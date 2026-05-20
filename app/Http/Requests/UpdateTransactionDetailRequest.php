<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Services\TransactionDetailsService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateTransactionDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $transaction = $this->route('transaction');
        $transactionDetail = $this->route('transactionDetail');

        return $transaction instanceof Transaction
            && $transactionDetail instanceof TransactionDetail
            && $transactionDetail->transaction_id === $transaction->getKey();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'in:sizes,kg,mt,pcs'],
            'quantity' => ['required', 'array:value,xxs,xs,s,m,l,xl'],
            'quantity.value' => [
                Rule::requiredIf(fn () => $this->input('unit') !== 'sizes'),
                'nullable',
                'numeric',
                'min:0.01',
            ],
            'quantity.xxs' => ['nullable', 'integer', 'min:0'],
            'quantity.xs' => ['nullable', 'integer', 'min:0'],
            'quantity.s' => ['nullable', 'integer', 'min:0'],
            'quantity.m' => ['nullable', 'integer', 'min:0'],
            'quantity.l' => ['nullable', 'integer', 'min:0'],
            'quantity.xl' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $transaction = $this->route('transaction');
                $transactionDetail = $this->route('transactionDetail');

                if (! $transaction instanceof Transaction || ! $transactionDetail instanceof TransactionDetail) {
                    return;
                }

                if ($this->input('unit') !== 'sizes') {
                    if ($transaction->type !== 'out') {
                        return;
                    }

                    $unit = (string) $this->input('unit');
                    $normalizedProductName = TransactionDetailsService::normalizeProductName((string) $this->input('product_name', ''));
                    $requestedQuantity = (float) data_get($this->input('quantity', []), 'value', 0);
                    $availableQuantity = (float) data_get(
                        TransactionDetailsService::getConfirmedAvailableQuantity($normalizedProductName, $unit),
                        'value',
                        0
                    );

                    $availableQuantity += $this->getCurrentAvailableValueOffset(
                        $transaction,
                        $transactionDetail,
                        $normalizedProductName,
                        $unit
                    );

                    if ($requestedQuantity > $availableQuantity) {
                        $validator->errors()->add('quantity.value', 'Quantita non disponibile in magazzino per questo prodotto.');
                    }

                    return;
                }

                $sizeKeys = ['xxs', 'xs', 's', 'm', 'l', 'xl'];
                $hasAtLeastOneSize = collect($sizeKeys)->contains(
                    fn (string $size): bool => (int) data_get($this->input('quantity', []), $size, 0) > 0
                );

                if (! $hasAtLeastOneSize) {
                    $validator->errors()->add('quantity_sizes', 'Inserisci almeno una quantita maggiore di zero per le taglie.');
                }

                if ($transaction->type !== 'out') {
                    return;
                }

                $normalizedProductName = TransactionDetailsService::normalizeProductName((string) $this->input('product_name', ''));
                $availableQuantitiesBySize = TransactionDetailsService::getConfirmedAvailableQuantity($normalizedProductName, 'sizes');
                $currentAvailableOffsetBySize = $this->getCurrentAvailableSizeOffset($transaction, $transactionDetail, $normalizedProductName);

                foreach ($sizeKeys as $size) {
                    $requestedQuantity = (int) data_get($this->input('quantity', []), $size, 0);
                    $availableQuantity = (int) data_get($availableQuantitiesBySize, $size, 0)
                        + (int) data_get($currentAvailableOffsetBySize, $size, 0);

                    if ($requestedQuantity > $availableQuantity) {
                        $validator->errors()->add('quantity_sizes', "Disponibilita insufficiente per la taglia {$size}.");
                        break;
                    }
                }
            },
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validatedPayload(): array
    {
        $validated = $this->validated();
        $validated['product_name'] = trim((string) $validated['product_name']);

        if ($validated['unit'] === 'sizes') {
            $sizeKeys = ['xxs', 'xs', 's', 'm', 'l', 'xl'];

            $validated['quantity'] = collect($sizeKeys)
                ->mapWithKeys(fn (string $size): array => [$size => (int) data_get($validated, "quantity.$size", 0)])
                ->all();

            return $validated;
        }

        $validated['quantity'] = [
            'value' => (float) data_get($validated, 'quantity.value'),
        ];

        return $validated;
    }

    private function getCurrentAvailableValueOffset(
        Transaction $transaction,
        TransactionDetail $transactionDetail,
        string $normalizedProductName,
        string $unit
    ): float {
        if (! $this->shouldRestoreCurrentDetailStock($transaction, $transactionDetail, $normalizedProductName, $unit)) {
            return 0.0;
        }

        return (float) data_get($transactionDetail->quantity, 'value', 0);
    }

    /**
     * @return array{xxs: int, xs: int, s: int, m: int, l: int, xl: int}
     */
    private function getCurrentAvailableSizeOffset(
        Transaction $transaction,
        TransactionDetail $transactionDetail,
        string $normalizedProductName
    ): array {
        $emptyOffset = ['xxs' => 0, 'xs' => 0, 's' => 0, 'm' => 0, 'l' => 0, 'xl' => 0];

        if (! $this->shouldRestoreCurrentDetailStock($transaction, $transactionDetail, $normalizedProductName, 'sizes')) {
            return $emptyOffset;
        }

        return collect(array_keys($emptyOffset))
            ->mapWithKeys(fn (string $size): array => [$size => (int) data_get($transactionDetail->quantity, $size, 0)])
            ->all();
    }

    private function shouldRestoreCurrentDetailStock(
        Transaction $transaction,
        TransactionDetail $transactionDetail,
        string $normalizedProductName,
        string $unit
    ): bool {
        return $transaction->type === 'out'
            && $transaction->status === 'confirmed'
            && $transactionDetail->unit === $unit
            && TransactionDetailsService::normalizeProductName((string) $transactionDetail->product_name) === $normalizedProductName;
    }
}

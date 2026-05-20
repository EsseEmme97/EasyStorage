<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use App\Services\TransactionDetailsService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTransactionDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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

                if (! $transaction instanceof Transaction) {
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

                foreach ($sizeKeys as $size) {
                    $requestedQuantity = (int) data_get($this->input('quantity', []), $size, 0);
                    $availableQuantity = (int) data_get($availableQuantitiesBySize, $size, 0);

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
}

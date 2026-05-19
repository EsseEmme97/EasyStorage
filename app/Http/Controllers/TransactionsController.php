<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TransactionsController extends Controller
{
    public function index()
    {
        return view('transactions', [
            'suppliers' => Supplier::all(),
            'transactions' => Transaction::with('supplier')->latest()->get(),
        ]);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'description' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:in,out'],
            'status' => ['required', 'in:draft,confirmed,canceled'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
        ]);

        Transaction::create($validated);

        return redirect()
            ->route('transactions')
            ->with('success', 'Transazione creata con successo.');
    }

    public function showDetails(Transaction $transaction)
    {
        $transaction->load(['supplier', 'details']);

        $productSuggestionsByUnit = $this->getProductSuggestionsByUnit($transaction->type);

        return view('transactions-details', [
            'transaction' => $transaction,
            'productSuggestionsByUnit' => $productSuggestionsByUnit,
        ]);
    }

    public function storeDetails(Request $request, Transaction $transaction)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'in:sizes,kg,mt,pcs'],
            'quantity' => ['required', 'array:value,xxs,xs,s,m,l,xl'],
            'quantity.value' => [
                Rule::requiredIf(fn () => $request->input('unit') !== 'sizes'),
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
        ]);

        $validator->after(function ($validator) use ($request, $transaction) {
            if ($request->input('unit') !== 'sizes') {
                if ($transaction->type !== 'out') {
                    return;
                }

                $normalizedProductName = $this->normalizeProductName((string) $request->input('product_name', ''));
                $requestedQuantity = (float) data_get($request->input('quantity', []), 'value', 0);
                $availableQuantity = (float) data_get($this->getConfirmedAvailableQuantity($normalizedProductName, 'pcs'), 'value', 0);

                if ($request->input('unit') === 'kg') {
                    $availableQuantity = (float) data_get($this->getConfirmedAvailableQuantity($normalizedProductName, 'kg'), 'value', 0);
                }

                if ($request->input('unit') === 'mt') {
                    $availableQuantity = (float) data_get($this->getConfirmedAvailableQuantity($normalizedProductName, 'mt'), 'value', 0);
                }

                if ($request->input('unit') === 'pcs') {
                    $availableQuantity = (float) data_get($this->getConfirmedAvailableQuantity($normalizedProductName, 'pcs'), 'value', 0);
                }

                if ($requestedQuantity > $availableQuantity) {
                    $validator->errors()->add('quantity.value', 'Quantita non disponibile in magazzino per questo prodotto.');
                }

                return;
            }

            $sizeKeys = ['xxs', 'xs', 's', 'm', 'l', 'xl'];
            $hasAtLeastOneSize = collect($sizeKeys)->contains(function ($size) use ($request) {
                return (int) data_get($request->input('quantity', []), $size, 0) > 0;
            });

            if (! $hasAtLeastOneSize) {
                $validator->errors()->add('quantity_sizes', 'Inserisci almeno una quantita maggiore di zero per le taglie.');
            }

            if ($transaction->type !== 'out') {
                return;
            }

            $normalizedProductName = $this->normalizeProductName((string) $request->input('product_name', ''));
            $availableQuantitiesBySize = $this->getConfirmedAvailableQuantity($normalizedProductName, 'sizes');

            foreach ($sizeKeys as $size) {
                $requestedQuantity = (int) data_get($request->input('quantity', []), $size, 0);
                $availableQuantity = (int) data_get($availableQuantitiesBySize, $size, 0);

                if ($requestedQuantity > $availableQuantity) {
                    $validator->errors()->add('quantity_sizes', "Disponibilita insufficiente per la taglia {$size}.");
                    break;
                }
            }
        });

        $validated = $validator->validate();
        $validated['product_name'] = trim($validated['product_name']);

        if ($validated['unit'] === 'sizes') {
            $sizeKeys = ['xxs', 'xs', 's', 'm', 'l', 'xl'];

            $validated['quantity'] = collect($sizeKeys)
                ->mapWithKeys(function ($size) use ($validated) {
                    return [$size => (int) data_get($validated, "quantity.$size", 0)];
                })
                ->all();
        } else {
            $validated['quantity'] = [
                'value' => (float) data_get($validated, 'quantity.value'),
            ];
        }

        $transaction->details()->create($validated);

        return redirect()
            ->route('transactions.show', $transaction)
            ->with('success', 'Dettaglio transazione aggiunto con successo.');
    }

    private function getProductSuggestionsByUnit(string $transactionType): array
    {
        $units = ['sizes', 'kg', 'mt', 'pcs'];
        $suggestions = collect($units)->mapWithKeys(fn (string $unit) => [$unit => []])->all();

        if ($transactionType === 'out') {
            return $this->getAvailableProductSuggestionsByUnit();
        }

        $seen = [];

        TransactionDetail::query()
            ->select(['product_name', 'unit'])
            ->where('product_name', '!=', '')
            ->latest('id')
            ->get()
            ->each(function (TransactionDetail $detail) use (&$suggestions, &$seen) {
                $unit = $detail->unit;

                if (! array_key_exists($unit, $suggestions)) {
                    return;
                }

                $normalizedName = $this->normalizeProductName((string) $detail->product_name);

                if ($normalizedName === '' || isset($seen[$unit][$normalizedName])) {
                    return;
                }

                $suggestions[$unit][] = trim((string) $detail->product_name);
                $seen[$unit][$normalizedName] = true;
            });

        foreach ($units as $unit) {
            sort($suggestions[$unit], SORT_NATURAL | SORT_FLAG_CASE);
        }

        return $suggestions;
    }

    private function getAvailableProductSuggestionsByUnit(): array
    {
        $units = ['sizes', 'kg', 'mt', 'pcs'];
        $availableByUnit = collect($units)->mapWithKeys(fn (string $unit) => [$unit => []])->all();
        $displayNames = [];

        $confirmedDetails = TransactionDetail::query()
            ->with(['transaction:id,type,status'])
            ->whereHas('transaction', fn ($query) => $query->where('status', 'confirmed'))
            ->get();

        foreach ($confirmedDetails as $detail) {
            $unit = $detail->unit;

            if (! array_key_exists($unit, $availableByUnit)) {
                continue;
            }

            $normalizedName = $this->normalizeProductName((string) $detail->product_name);

            if ($normalizedName === '') {
                continue;
            }

            $displayNames[$unit][$normalizedName] ??= trim((string) $detail->product_name);
            $delta = $detail->transaction?->type === 'in' ? 1 : -1;

            if ($unit === 'sizes') {
                foreach (['xxs', 'xs', 's', 'm', 'l', 'xl'] as $size) {
                    $availableByUnit[$unit][$normalizedName][$size] =
                        ($availableByUnit[$unit][$normalizedName][$size] ?? 0)
                        + ($delta * (int) data_get($detail->quantity, $size, 0));
                }

                continue;
            }

            $availableByUnit[$unit][$normalizedName]['value'] =
                ($availableByUnit[$unit][$normalizedName]['value'] ?? 0)
                + ($delta * (float) data_get($detail->quantity, 'value', 0));
        }

        $suggestions = collect($units)->mapWithKeys(fn (string $unit) => [$unit => []])->all();

        foreach ($availableByUnit as $unit => $products) {
            foreach ($products as $normalizedName => $quantityByUnit) {
                $hasStock = $unit === 'sizes'
                    ? collect(['xxs', 'xs', 's', 'm', 'l', 'xl'])->contains(fn (string $size) => (int) data_get($quantityByUnit, $size, 0) > 0)
                    : (float) data_get($quantityByUnit, 'value', 0) > 0;

                if (! $hasStock) {
                    continue;
                }

                $suggestions[$unit][] = $displayNames[$unit][$normalizedName] ?? $normalizedName;
            }

            sort($suggestions[$unit], SORT_NATURAL | SORT_FLAG_CASE);
        }

        return $suggestions;
    }

    private function getConfirmedAvailableQuantity(string $normalizedProductName, string $unit): array
    {
        $defaults = $unit === 'sizes'
            ? ['xxs' => 0, 'xs' => 0, 's' => 0, 'm' => 0, 'l' => 0, 'xl' => 0]
            : ['value' => 0.0];

        if ($normalizedProductName === '') {
            return $defaults;
        }

        $confirmedDetails = TransactionDetail::query()
            ->with(['transaction:id,type,status'])
            ->where('unit', $unit)
            ->whereRaw('LOWER(TRIM(product_name)) = ?', [$normalizedProductName])
            ->whereHas('transaction', fn ($query) => $query->where('status', 'confirmed'))
            ->get();

        if ($unit === 'sizes') {
            $stockBySize = $defaults;

            foreach ($confirmedDetails as $detail) {
                $delta = $detail->transaction?->type === 'in' ? 1 : -1;

                foreach (array_keys($stockBySize) as $size) {
                    $stockBySize[$size] += $delta * (int) data_get($detail->quantity, $size, 0);
                }
            }

            return $stockBySize;
        }

        $value = 0.0;

        foreach ($confirmedDetails as $detail) {
            $delta = $detail->transaction?->type === 'in' ? 1 : -1;
            $value += $delta * (float) data_get($detail->quantity, 'value', 0);
        }

        return ['value' => $value];
    }

    private function normalizeProductName(string $productName): string
    {
        return mb_strtolower(trim($productName));
    }
}

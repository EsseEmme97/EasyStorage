<?php

namespace App\Services;

use App\Models\TransactionDetail;

class TransactionDetailsService
{
    public function getProductSuggestionsByUnit(string $transactionType): array
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

    public function getConfirmedAvailableQuantity(string $normalizedProductName, string $unit): array
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

    public function normalizeProductName(string $productName): string
    {
        return mb_strtolower(trim($productName));
    }
}

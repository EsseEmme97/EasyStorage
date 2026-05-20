<?php

namespace App\Services;

use App\Models\TransactionDetail;

class TransactionDetailsService
{
    public static function getAvailableProducts(bool $inOut): array
    {
        $availableItems = TransactionDetail::query()
            ->whereHas('transaction', fn ($query) => $query->where('type', $inOut ? 'in' : 'out'))
            ->get()
            ->pluck('product_name')
            ->unique()
            ->values()
            ->all();

        sort($availableItems, SORT_NATURAL | SORT_FLAG_CASE);

        return $availableItems;
    }

    public static function getConfirmedAvailableQuantity(string $normalizedProductName, string $unit): array
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

    public static function normalizeProductName(string $productName): string
    {
        return mb_strtolower(trim($productName));
    }
}

<?php

namespace App\Services;

use App\Models\TransactionDetail;
use Illuminate\Support\Collection;

class TransactionDetailsService
{
    /**
     * @return array<int, array{name: string, unit: string, quantity: array<string, int|float>}>
     */
    public static function getAvailableProductSuggestions(bool $inOut): array
    {
        $transactionType = $inOut ? 'in' : 'out';

        $groupedDetails = TransactionDetail::query()
            ->with('transaction:id,type,status')
            ->whereHas('transaction', function ($query) use ($transactionType): void {
                $query
                    ->where('type', $transactionType)
                    ->where('status', 'confirmed');
            })
            ->latest('id')
            ->get()
            ->groupBy(fn (TransactionDetail $detail): string => self::normalizeProductName((string) $detail->product_name));

        $suggestions = $groupedDetails
            ->map(function (Collection $details): ?array {
                $latestDetail = $details->first();

                if (! $latestDetail instanceof TransactionDetail) {
                    return null;
                }

                $unit = (string) $latestDetail->unit;
                $quantity = $unit === 'sizes'
                    ? self::sumSizesQuantities($details)
                    : ['value' => self::sumValueQuantities($details)];

                return [
                    'name' => (string) $latestDetail->product_name,
                    'unit' => $unit,
                    'quantity' => $quantity,
                ];
            })
            ->filter()
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        /** @var array<int, array{name: string, unit: string, quantity: array<string, int|float>}> $suggestions */
        return $suggestions;
    }

    public static function getAvailableProducts(bool $inOut): array
    {
        $availableItems = collect(self::getAvailableProductSuggestions($inOut))
            ->pluck('name')
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

    /**
     * @param  Collection<int, TransactionDetail>  $details
     * @return array{xxs: int, xs: int, s: int, m: int, l: int, xl: int}
     */
    private static function sumSizesQuantities(Collection $details): array
    {
        $sizeKeys = ['xxs', 'xs', 's', 'm', 'l', 'xl'];
        $totals = array_fill_keys($sizeKeys, 0);

        foreach ($details as $detail) {
            foreach ($sizeKeys as $size) {
                $totals[$size] += (int) data_get($detail->quantity, $size, 0);
            }
        }

        return $totals;
    }

    /**
     * @param  Collection<int, TransactionDetail>  $details
     */
    private static function sumValueQuantities(Collection $details): float
    {
        return (float) $details->sum(fn (TransactionDetail $detail): float => (float) data_get($detail->quantity, 'value', 0));
    }
}

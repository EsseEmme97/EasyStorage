<?php

namespace App\Http\Controllers;

use App\Models\TransactionDetail;
use Illuminate\View\View;

class InventoryDashboardController extends Controller
{
    public function index(): View
    {
        $products = [];
        $sizeKeys = ['xxs', 'xs', 's', 'm', 'l', 'xl'];

        $confirmedDetails = TransactionDetail::query()
            ->with(['transaction:id,type,status'])
            ->whereHas('transaction', fn ($query) => $query->where('status', 'confirmed'))
            ->get();

        foreach ($confirmedDetails as $detail) {
            $unit = $detail->unit;
            $normalizedName = $this->normalizeProductName((string) $detail->product_name);

            if ($normalizedName === '') {
                continue;
            }

            $productKey = $unit.'::'.$normalizedName;

            if (! isset($products[$productKey])) {
                $products[$productKey] = $this->initialDashboardRow(trim((string) $detail->product_name), $unit, $sizeKeys);
            }

            $isInbound = $detail->transaction?->type === 'in';

            if ($unit === 'sizes') {
                foreach ($sizeKeys as $size) {
                    $quantity = (int) data_get($detail->quantity, $size, 0);

                    if ($isInbound) {
                        $products[$productKey]['in'][$size] += $quantity;
                    } else {
                        $products[$productKey]['out'][$size] += $quantity;
                    }
                }

                continue;
            }

            $quantity = (float) data_get($detail->quantity, 'value', 0);

            if ($isInbound) {
                $products[$productKey]['in']['value'] += $quantity;
            } else {
                $products[$productKey]['out']['value'] += $quantity;
            }
        }

        $dashboardRows = collect($products)
            ->map(function (array $row) use ($sizeKeys) {
                if ($row['unit'] === 'sizes') {
                    foreach ($sizeKeys as $size) {
                        $inQuantity = $row['in'][$size];
                        $outQuantity = $row['out'][$size];

                        $row['in_storage'][$size] = max($inQuantity - $outQuantity, 0);
                        $row['outside_storage'][$size] = $outQuantity;
                        $row['available'][$size] = $row['in_storage'][$size];
                    }

                    $row['totals'] = [
                        'in' => collect($sizeKeys)->sum(fn (string $size) => $row['in'][$size]),
                        'out' => collect($sizeKeys)->sum(fn (string $size) => $row['out'][$size]),
                        'in_storage' => collect($sizeKeys)->sum(fn (string $size) => $row['in_storage'][$size]),
                        'outside_storage' => collect($sizeKeys)->sum(fn (string $size) => $row['outside_storage'][$size]),
                    ];

                    return $row;
                }

                $inValue = (float) data_get($row, 'in.value', 0);
                $outValue = (float) data_get($row, 'out.value', 0);

                $row['in_storage']['value'] = max($inValue - $outValue, 0);
                $row['outside_storage']['value'] = $outValue;
                $row['available']['value'] = $row['in_storage']['value'];

                return $row;
            })
            ->sortBy(fn (array $row) => mb_strtolower($row['product_name'].' '.$row['unit']))
            ->values();

        $summary = [
            'products_count' => $dashboardRows->count(),
            'products_in_storage' => $dashboardRows->filter(function (array $row) {
                if ($row['unit'] === 'sizes') {
                    return (int) data_get($row, 'totals.in_storage', 0) > 0;
                }

                return (float) data_get($row, 'in_storage.value', 0) > 0;
            })->count(),
            'products_outside' => $dashboardRows->filter(function (array $row) {
                if ($row['unit'] === 'sizes') {
                    return (int) data_get($row, 'totals.outside_storage', 0) > 0;
                }

                return (float) data_get($row, 'outside_storage.value', 0) > 0;
            })->count(),
        ];

        return view('inventory-dashboard', [
            'rows' => $dashboardRows,
            'summary' => $summary,
            'sizeKeys' => $sizeKeys,
        ]);
    }

    private function initialDashboardRow(string $productName, string $unit, array $sizeKeys): array
    {
        if ($unit === 'sizes') {
            $emptySizeSet = collect($sizeKeys)->mapWithKeys(fn (string $size) => [$size => 0])->all();

            return [
                'product_name' => $productName,
                'unit' => $unit,
                'in' => $emptySizeSet,
                'out' => $emptySizeSet,
                'in_storage' => $emptySizeSet,
                'outside_storage' => $emptySizeSet,
                'available' => $emptySizeSet,
                'totals' => [
                    'in' => 0,
                    'out' => 0,
                    'in_storage' => 0,
                    'outside_storage' => 0,
                ],
            ];
        }

        return [
            'product_name' => $productName,
            'unit' => $unit,
            'in' => ['value' => 0.0],
            'out' => ['value' => 0.0],
            'in_storage' => ['value' => 0.0],
            'outside_storage' => ['value' => 0.0],
            'available' => ['value' => 0.0],
            'totals' => [
                'in' => 0.0,
                'out' => 0.0,
                'in_storage' => 0.0,
                'outside_storage' => 0.0,
            ],
        ];
    }

    private function normalizeProductName(string $productName): string
    {
        return mb_strtolower(trim($productName));
    }
}

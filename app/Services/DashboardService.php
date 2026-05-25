<?php

namespace App\Services;

use App\Models\TransactionDetail;

class DashboardService
{
    public function buildDashboard(bool $showOutsideSuppliers): array
    {
        $products = [];
        $outsideBalances = [];
        $outsideSupplierLabels = [];
        $sizeKeys = ['xxs', 'xs', 's', 'm', 'l', 'xl'];

        $confirmedDetails = TransactionDetail::query()
            ->with(['transaction:id,type,status,supplier_id,created_at', 'transaction.supplier:id,name'])
            ->whereHas('transaction', fn ($query) => $query->where('status', 'confirmed'))
            ->get()
            ->sortBy(function (TransactionDetail $detail): string {
                $transactionTimestamp = $detail->transaction?->created_at?->getTimestamp() ?? 0;
                $transactionId = $detail->transaction_id ?? 0;

                return sprintf('%015d-%015d-%015d', $transactionTimestamp, $transactionId, $detail->id);
            })
            ->values();

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
            $supplierKey = (string) ($detail->transaction?->supplier_id ?? 0);
            $outsideSupplierLabels[$supplierKey] = trim((string) ($detail->transaction?->supplier?->name ?? 'Senza fornitore'));

            if ($unit === 'sizes') {
                foreach ($sizeKeys as $size) {
                    $quantity = (int) data_get($detail->quantity, $size, 0);

                    if ($isInbound) {
                        $products[$productKey]['in'][$size] += $quantity;

                        $outsideBalances[$productKey][$supplierKey][$size] = max(
                            ((int) data_get($outsideBalances, $productKey.'.'.$supplierKey.'.'.$size, 0)) - $quantity,
                            0
                        );
                    } else {
                        $products[$productKey]['out'][$size] += $quantity;
                        $outsideBalances[$productKey][$supplierKey][$size] =
                            ((int) data_get($outsideBalances, $productKey.'.'.$supplierKey.'.'.$size, 0)) + $quantity;
                    }
                }

                continue;
            }

            $quantity = (float) data_get($detail->quantity, 'value', 0);

            if ($isInbound) {
                $products[$productKey]['in']['value'] += $quantity;

                $outsideBalances[$productKey][$supplierKey]['value'] = max(
                    ((float) data_get($outsideBalances, $productKey.'.'.$supplierKey.'.value', 0)) - $quantity,
                    0
                );
            } else {
                $products[$productKey]['out']['value'] += $quantity;
                $outsideBalances[$productKey][$supplierKey]['value'] =
                    ((float) data_get($outsideBalances, $productKey.'.'.$supplierKey.'.value', 0)) + $quantity;
            }
        }

        $dashboardRows = collect($products)
            ->map(function (array $row, string $productKey) use ($outsideBalances, $outsideSupplierLabels, $sizeKeys) {
                if ($row['unit'] === 'sizes') {
                    $outsideBySupplier = collect(data_get($outsideBalances, $productKey, []))
                        ->map(function (array $supplierOutside, string $supplierKey) use ($outsideSupplierLabels, $sizeKeys): array {
                            $sizes = [];
                            $total = 0;

                            foreach ($sizeKeys as $size) {
                                $sizes[$size] = (int) data_get($supplierOutside, $size, 0);
                                $total += $sizes[$size];
                            }

                            return [
                                'supplier' => (string) data_get($outsideSupplierLabels, $supplierKey, 'Senza fornitore'),
                                'sizes' => $sizes,
                                'total' => $total,
                            ];
                        })
                        ->filter(fn (array $item): bool => $item['total'] > 0)
                        ->sortBy('supplier', SORT_NATURAL | SORT_FLAG_CASE)
                        ->values()
                        ->all();

                    $outsideBySize = collect($outsideBySupplier)
                        ->reduce(function (array $carry, array $supplierOutside) use ($sizeKeys): array {
                            foreach ($sizeKeys as $size) {
                                $carry[$size] += (int) data_get($supplierOutside, 'sizes.'.$size, 0);
                            }

                            return $carry;
                        }, array_fill_keys($sizeKeys, 0));

                    foreach ($sizeKeys as $size) {
                        $inQuantity = $row['in'][$size];
                        $outQuantity = $row['out'][$size];

                        $row['in_storage'][$size] = max($inQuantity - $outQuantity, 0);
                        $row['outside_storage'][$size] = (int) data_get($outsideBySize, $size, 0);
                        $row['available'][$size] = $row['in_storage'][$size];
                    }

                    $row['totals'] = [
                        'in' => collect($sizeKeys)->sum(fn (string $size) => $row['in'][$size]),
                        'out' => collect($sizeKeys)->sum(fn (string $size) => $row['out'][$size]),
                        'in_storage' => collect($sizeKeys)->sum(fn (string $size) => $row['in_storage'][$size]),
                        'outside_storage' => collect($sizeKeys)->sum(fn (string $size) => $row['outside_storage'][$size]),
                    ];
                    $row['outside_by_supplier'] = $outsideBySupplier;

                    return $row;
                }

                $inValue = (float) data_get($row, 'in.value', 0);
                $outValue = (float) data_get($row, 'out.value', 0);
                $outsideValue = (float) collect(data_get($outsideBalances, $productKey, []))
                    ->sum(fn (array $supplierOutside): float => (float) data_get($supplierOutside, 'value', 0));
                $outsideBySupplier = collect(data_get($outsideBalances, $productKey, []))
                    ->map(function (array $supplierOutside, string $supplierKey) use ($outsideSupplierLabels): array {
                        return [
                            'supplier' => (string) data_get($outsideSupplierLabels, $supplierKey, 'Senza fornitore'),
                            'value' => (float) data_get($supplierOutside, 'value', 0),
                        ];
                    })
                    ->filter(fn (array $item): bool => $item['value'] > 0)
                    ->sortBy('supplier', SORT_NATURAL | SORT_FLAG_CASE)
                    ->values()
                    ->all();

                $row['in_storage']['value'] = max($inValue - $outValue, 0);
                $row['outside_storage']['value'] = $outsideValue;
                $row['available']['value'] = $row['in_storage']['value'];
                $row['outside_by_supplier'] = $outsideBySupplier;

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

        return [
            'rows' => $dashboardRows,
            'summary' => $summary,
            'sizeKeys' => $sizeKeys,
            'showOutsideSuppliers' => $showOutsideSuppliers,
        ];
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
                'outside_by_supplier' => [],
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
            'outside_by_supplier' => [],
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

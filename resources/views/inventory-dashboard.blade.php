<x-home-layout>
    <x-slot:title>
        Easy Storage - Dashboard Inventario
    </x-slot:title>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <h1 class="text-3xl font-bold tracking-tight">Dashboard Inventario</h1>
                <p class="text-muted-foreground">Vista real-time applicativa basata sulle sole transazioni confermate.</p>
            </div>
            <a
                href="{{ route('transactions') }}"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3"
            >
                Vai a Transazioni
            </a>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-md border p-4">
                <p class="text-sm text-muted-foreground">Prodotti tracciati</p>
                <p class="mt-1 text-2xl font-semibold">{{ $summary['products_count'] }}</p>
            </div>
            <div class="rounded-md border p-4">
                <p class="text-sm text-muted-foreground">Prodotti in magazzino</p>
                <p class="mt-1 text-2xl font-semibold">{{ $summary['products_in_storage'] }}</p>
            </div>
            <div class="rounded-md border p-4">
                <p class="text-sm text-muted-foreground">Prodotti fuori magazzino</p>
                <p class="mt-1 text-2xl font-semibold">{{ $summary['products_outside'] }}</p>
            </div>
        </div>

        <div class="rounded-md border">
            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b">
                        <tr class="border-b transition-colors hover:bg-muted/50">
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Prodotto</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Unita</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Totale IN</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Totale OUT</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">In Storage</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Outside Storage</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Stato</th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0">
                        @forelse ($rows as $row)
                            <tr class="border-b transition-colors hover:bg-muted/50 align-top">
                                <td class="h-12 px-4 align-middle font-medium">{{ $row['product_name'] }}</td>
                                <td class="h-12 px-4 align-middle uppercase">{{ $row['unit'] }}</td>
                                <td class="h-12 px-4 align-middle">
                                    @if ($row['unit'] === 'sizes')
                                        <div class="space-y-2">
                                            <span class="inline-flex items-center rounded-md border bg-muted/40 px-2 py-1 text-xs font-semibold">
                                                Totale: {{ $row['totals']['in'] }}
                                            </span>
                                            <div class="overflow-x-auto rounded-md border">
                                                <table class="min-w-90 text-xs">
                                                    <thead class="bg-muted/50">
                                                        <tr>
                                                            @foreach ($sizeKeys as $size)
                                                                <th class="px-2 py-1 text-left font-medium text-muted-foreground uppercase">{{ $size }}</th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr class="border-t">
                                                            @foreach ($sizeKeys as $size)
                                                                <td class="px-2 py-1">{{ $row['in'][$size] }}</td>
                                                            @endforeach
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @else
                                        {{ number_format($row['in']['value'], 2, ',', '.') }}
                                    @endif
                                </td>
                                <td class="h-12 px-4 align-middle">
                                    @if ($row['unit'] === 'sizes')
                                        <div class="space-y-2">
                                            <span class="inline-flex items-center rounded-md border bg-muted/40 px-2 py-1 text-xs font-semibold">
                                                Totale: {{ $row['totals']['out'] }}
                                            </span>
                                            <div class="overflow-x-auto rounded-md border">
                                                <table class="min-w-90 text-xs">
                                                    <thead class="bg-muted/50">
                                                        <tr>
                                                            @foreach ($sizeKeys as $size)
                                                                <th class="px-2 py-1 text-left font-medium text-muted-foreground uppercase">{{ $size }}</th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr class="border-t">
                                                            @foreach ($sizeKeys as $size)
                                                                <td class="px-2 py-1">{{ $row['out'][$size] }}</td>
                                                            @endforeach
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @else
                                        {{ number_format($row['out']['value'], 2, ',', '.') }}
                                    @endif
                                </td>
                                <td class="h-12 px-4 align-middle font-semibold">
                                    @if ($row['unit'] === 'sizes')
                                        {{ $row['totals']['in_storage'] }}
                                    @else
                                        {{ number_format($row['in_storage']['value'], 2, ',', '.') }}
                                    @endif
                                </td>
                                <td class="h-12 px-4 align-middle font-semibold">
                                    @if ($row['unit'] === 'sizes')
                                        {{ $row['totals']['outside_storage'] }}
                                    @else
                                        {{ number_format($row['outside_storage']['value'], 2, ',', '.') }}
                                    @endif
                                </td>
                                <td class="h-12 px-4 align-middle">
                                    @php
                                        $hasInStorage = $row['unit'] === 'sizes'
                                            ? $row['totals']['in_storage'] > 0
                                            : $row['in_storage']['value'] > 0;
                                        $hasOutside = $row['unit'] === 'sizes'
                                            ? $row['totals']['outside_storage'] > 0
                                            : $row['outside_storage']['value'] > 0;
                                    @endphp

                                    @if ($hasInStorage && $hasOutside)
                                        <span class="inline-flex items-center rounded-md border border-amber-300 bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-800">
                                            Distribuito (in + out)
                                        </span>
                                    @elseif ($hasInStorage)
                                        <span class="inline-flex items-center rounded-md border border-green-300 bg-green-50 px-2 py-1 text-xs font-semibold text-green-800">
                                            In magazzino
                                        </span>
                                    @elseif ($hasOutside)
                                        <span class="inline-flex items-center rounded-md border border-blue-300 bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-800">
                                            Fuori magazzino
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-md border border-zinc-300 bg-zinc-50 px-2 py-1 text-xs font-semibold text-zinc-700">
                                            Nessuna disponibilita
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="h-12 px-4 text-center text-muted-foreground font-bold">
                                    Nessun prodotto disponibile. Conferma almeno una transazione per popolare la dashboard.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-home-layout>

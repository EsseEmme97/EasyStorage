<x-home-layout>
    <x-slot:title>
        Easy Storage - Dettaglio Transazione
    </x-slot:title>
	<x-slot:additionalAsset>
		resources/js/transactionsDetails.js
	</x-slot:additionalAsset>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <h1 class="text-3xl font-bold tracking-tight">Dettaglio Transazione</h1>
                <p class="text-muted-foreground">Aggiungi prodotti e quantita legate alla transazione selezionata.</p>
            </div>
            <a
                href="{{ route('transactions') }}"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3"
            >
                Torna alle Transazioni
            </a>
        </div>

        @if(session('success'))
            <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-md border p-6 space-y-6">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">ID</p>
                    <p class="mt-1 text-lg font-semibold">#TRX-{{ str_pad((string) $transaction->id, 3, '0', STR_PAD_LEFT) }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Descrizione</p>
                    <p class="mt-1">{{ $transaction->description ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Fornitore</p>
                    <p class="mt-1">{{ $transaction->supplier?->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Tipo / Stato</p>
                    <p class="mt-1">{{ ucfirst($transaction->type) }} / {{ ucfirst($transaction->status) }}</p>
                </div>
            </div>

            <div class="border-t pt-6">
                <h2 class="text-lg font-semibold">Aggiungi Dettaglio</h2>
                <form action="{{ route('transactions.details.store', $transaction) }}" method="POST" class="mt-4 grid gap-4 md:grid-cols-4">
                    @csrf

                    <div class="md:col-span-2">
                        <label for="product_name" class="block text-sm font-medium">Nome Prodotto</label>
                        <input
                            id="product_name"
                            name="product_name"
                            type="text"
                            value="{{ old('product_name') }}"
                            required
                            class="mt-1 w-full rounded-md border px-3 py-2"
                            placeholder="Es. Kick pant"
                        >
                        @error('product_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="unit" class="block text-sm font-medium">Unita</label>
                        <select id="unit" name="unit" required class="mt-1 w-full rounded-md border px-3 py-2">
                            <option value="pcs" {{ old('unit', 'pcs') === 'pcs' ? 'selected' : '' }}>Pezzi</option>
                            <option value="kg" {{ old('unit') === 'kg' ? 'selected' : '' }}>Kg</option>
                            <option value="mt" {{ old('unit') === 'mt' ? 'selected' : '' }}>Metri</option>
                            <option value="sizes" {{ old('unit') === 'sizes' ? 'selected' : '' }}>Taglie</option>
                        </select>
                        @error('unit')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="quantity_value" class="block text-sm font-medium">Quantita</label>
                        <div id="quantity-value-wrapper">
                            <input
                                id="quantity_value"
                                name="quantity[value]"
                                type="number"
                                step="0.01"
                                min="0.01"
                                value="{{ old('quantity.value') }}"
                                {{ old('unit', 'pcs') === 'sizes' ? '' : 'required' }}
                                class="mt-1 w-full rounded-md border px-3 py-2"
                                placeholder="0.00"
                            >
                            @error('quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('quantity.value')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div id="sizes-wrapper" class="md:col-span-4 {{ old('unit') === 'sizes' ? '' : 'hidden' }}">
                        <p class="mb-2 text-sm font-medium">Quantita per Taglia</p>
                        <div class="overflow-x-auto rounded-md border">
                            <table class="w-full text-sm">
                                <thead class="bg-muted/50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-muted-foreground">XXS</th>
                                        <th class="px-3 py-2 text-left font-medium text-muted-foreground">XS</th>
                                        <th class="px-3 py-2 text-left font-medium text-muted-foreground">S</th>
                                        <th class="px-3 py-2 text-left font-medium text-muted-foreground">M</th>
                                        <th class="px-3 py-2 text-left font-medium text-muted-foreground">L</th>
                                        <th class="px-3 py-2 text-left font-medium text-muted-foreground">XL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-t">
                                        <td class="px-3 py-2">
                                            <input type="number" min="0" step="1" inputmode="numeric" name="quantity[xxs]" value="{{ old('quantity.xxs', 0) }}" class="w-full rounded-md border px-3 py-2">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" min="0" step="1" inputmode="numeric" name="quantity[xs]" value="{{ old('quantity.xs', 0) }}" class="w-full rounded-md border px-3 py-2">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" min="0" step="1" inputmode="numeric" name="quantity[s]" value="{{ old('quantity.s', 0) }}" class="w-full rounded-md border px-3 py-2">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" min="0" step="1" inputmode="numeric" name="quantity[m]" value="{{ old('quantity.m', 0) }}" class="w-full rounded-md border px-3 py-2">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" min="0" step="1" inputmode="numeric" name="quantity[l]" value="{{ old('quantity.l', 0) }}" class="w-full rounded-md border px-3 py-2">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" min="0" step="1" inputmode="numeric" name="quantity[xl]" value="{{ old('quantity.xl', 0) }}" class="w-full rounded-md border px-3 py-2">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        @error('quantity_sizes')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('quantity.xxs')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-4 flex justify-end">
                        <button type="submit" class="action-button">Aggiungi Dettaglio</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="rounded-md border">
            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b">
                        <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">ID</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Prodotto</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Unita</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Quantita</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Data</th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0">
                        @forelse ($transaction->details as $detail)
                            <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                                <td class="h-12 px-4 align-middle">{{ $detail->id }}</td>
                                <td class="h-12 px-4 align-middle">{{ $detail->product_name }}</td>
                                <td class="h-12 px-4 align-middle">{{ strtoupper($detail->unit) }}</td>
                                <td class="h-12 px-4 align-middle">
                                    @if ($detail->unit === 'sizes')
                                        <div class="overflow-x-auto rounded-md border">
                                            <table class="min-w-90 text-xs">
                                                <thead class="bg-muted/50">
                                                    <tr>
                                                        <th class="px-2 py-1 text-left font-medium text-muted-foreground">XXS</th>
                                                        <th class="px-2 py-1 text-left font-medium text-muted-foreground">XS</th>
                                                        <th class="px-2 py-1 text-left font-medium text-muted-foreground">S</th>
                                                        <th class="px-2 py-1 text-left font-medium text-muted-foreground">M</th>
                                                        <th class="px-2 py-1 text-left font-medium text-muted-foreground">L</th>
                                                        <th class="px-2 py-1 text-left font-medium text-muted-foreground">XL</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="border-t">
                                                        <td class="px-2 py-1">{{ data_get($detail->quantity, 'xxs', 0) }}</td>
                                                        <td class="px-2 py-1">{{ data_get($detail->quantity, 'xs', 0) }}</td>
                                                        <td class="px-2 py-1">{{ data_get($detail->quantity, 's', 0) }}</td>
                                                        <td class="px-2 py-1">{{ data_get($detail->quantity, 'm', 0) }}</td>
                                                        <td class="px-2 py-1">{{ data_get($detail->quantity, 'l', 0) }}</td>
                                                        <td class="px-2 py-1">{{ data_get($detail->quantity, 'xl', 0) }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        {{ data_get($detail->quantity, 'value', '-') }}
                                    @endif
                                </td>
                                <td class="h-12 px-4 align-middle">{{ $detail->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="h-12 px-4 text-center text-muted-foreground font-bold">Nessun dettaglio presente per questa transazione.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-home-layout>

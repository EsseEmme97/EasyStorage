<x-home-layout>
    <x-slot:title>
        Easy Storage - Transazioni
    </x-slot>
    <x-slot:additionalAsset>
        resources/js/manageTransactions.js
    </x-slot:additionalAsset>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <h2 class="text-3xl font-bold tracking-tight">Transazioni</h2>
                <p class="text-muted-foreground">Monitora e gestisci i movimenti del tuo magazzino.</p>
            </div>
            <div class="flex items-center space-x-2">
                <button class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
                    Esporta
                </button>
                <button id="toggle-transaction-form" type="button" class="action-button">
                    Nuova Transazione
                </button>
            </div>
        </div>

        <div id="transaction-form-wrapper" class="{{ old('description') || old('type') || old('status') || old('supplier_id') || $errors->any() ? '' : 'hidden' }} rounded-md border p-4 space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold">Nuova Transazione</h3>
                <svg id="closingButton" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 hover:text-slate-500 transition-colors">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <form action="{{ route('transactions.create') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="description" class="block text-sm font-medium">Descrizione</label>
                    <input
                        id="description"
                        name="description"
                        type="text"
                        value="{{ old('description') }}"
                        class="mt-1 w-full rounded-md border px-3 py-2"
                        placeholder="Descrizione transazione"
                    >
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label for="type" class="block text-sm font-medium">Tipo</label>
                        <select id="type" name="type" required class="mt-1 w-full rounded-md border px-3 py-2">
                            <option value="in" {{ old('type', 'in') === 'in' ? 'selected' : '' }}>Entrata</option>
                            <option value="out" {{ old('type') === 'out' ? 'selected' : '' }}>Uscita</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium">Stato</label>
                        <select id="status" name="status" required class="mt-1 w-full rounded-md border px-3 py-2">
                            <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Bozza</option>
                            <option value="confirmed" {{ old('status') === 'confirmed' ? 'selected' : '' }}>Confermata</option>
                            <option value="canceled" {{ old('status') === 'canceled' ? 'selected' : '' }}>Annullata</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="supplier_id" class="block text-sm font-medium">ID Fornitore</label>
                        <div class="flex items-center space-x-2">
                            <select
                                id="supplier_id"
                                name="supplier_id"
                                class="mt-1 w-full rounded-md border px-3 py-2"
                            >
                                @forelse($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ (string) old('supplier_id') === (string) $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @empty
                                    <option value="">Nessun fornitore disponibile</option>
                                @endforelse
                            </select>
                            <a
                                id="add-supplier-link"
                                data-base-url="{{ route('suppliers') }}"
                                class="rounded-md border px-3 py-1"
                                href="{{ route('suppliers', [
                                    'from' => 'transactions',
                                    'description' => old('description'),
                                    'type' => old('type', 'in'),
                                    'status' => old('status', 'draft'),
                                    'supplier_id' => old('supplier_id'),
                                ]) }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </a>
                        </div>
                        @error('supplier_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="action-button">Salva Transazione</button>
                </div>
            </form>
        </div>

        <div class="rounded-md border">
            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b">
                        <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">ID</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Descrizione</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Fornitore</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Tipo</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Data</th>
                            <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0">
                        {{-- Mock data rows --}}
                        <tr class="border-b transition-colors hover:bg-muted/50">
                            <td class="p-4 align-middle">#TR-001</td>
                            <td class="p-4 align-middle font-medium">Scaffale Industriale</td>
                            <td class="p-4 align-middle">Fornitore 1</td>
                            <td class="p-4 align-middle">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100">Entrata</span>
                            </td>
                            <td class="p-4 align-middle">08/05/2026</td>
                            <td class="p-4 align-middle text-right text-muted-foreground cursor-pointer hover:text-primary">Dettagli</td>
                        </tr>
                        <tr class="border-b transition-colors hover:bg-muted/50">
                            <td class="p-4 align-middle">#TR-002</td>
                            <td class="p-4 align-middle font-medium">Cassetta Plastica R32</td>
                            <td class="p-4 align-middle">Fornitore 2   </td>
                            <td class="p-4 align-middle">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100">Uscita</span>
                            </td>
                            <td class="p-4 align-middle">07/05/2026</td>
                            <td class="p-4 align-middle text-right text-muted-foreground cursor-pointer hover:text-primary">Dettagli</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-home-layout>
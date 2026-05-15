<x-home-layout>
    <x-slot:title>
        Easy Storage - Suppliers
    </x-slot:title>

    <x-slot:additionalAsset>
        resources/js/manageSuppliers.js
    </x-slot:additionalAsset>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <h1 class="text-3xl font-bold tracking-tight">Fornitori</h1>
                <p class="text-muted-foreground">Aggiungi o rimuovi fornitori dal tuo magazzino.</p>
            </div>
            <div class="flex items-center space-x-2">
                @if($returnToTransactions)
                    <a
                        href="{{ route('transactions') }}"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3"
                    >
                        Torna a Transazioni
                    </a>
                @endif
                <button id="toggle-supplier-form" type="button" class="action-button">
                    Nuovo Fornitore
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-semibold">Correggi i seguenti errori:</p>
                <ul class="mt-2 list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div id="supplier-form-wrapper" class="{{ $errors->any() ? '' : 'hidden' }} rounded-md border p-4 space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold">Nuovo Fornitore</h3>
                <svg id="closingSupplierButton" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 hover:text-slate-500 transition-colors cursor-pointer">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>

            <form action="{{ route('suppliers.create') }}" method="POST" class="space-y-4">
                @csrf

                @if($returnToTransactions)
                    <input type="hidden" name="return_to_transactions" value="1">
                    <input type="hidden" name="description" value="{{ $transactionInput['description'] ?? '' }}">
                    <input type="hidden" name="type" value="{{ $transactionInput['type'] ?? '' }}">
                    <input type="hidden" name="status" value="{{ $transactionInput['status'] ?? '' }}">
                    <input type="hidden" name="supplier_id" value="{{ $transactionInput['supplier_id'] ?? '' }}">
                @endif

                <div>
                    <label for="name" class="block text-sm font-medium">Nome Fornitore</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        required
                        maxlength="255"
                        class="mt-1 w-full rounded-md border px-3 py-2"
                        placeholder="Inserisci il nome del fornitore"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="action-button">Salva Fornitore</button>
                </div>
            </form>
        </div>

        <div class="rounded-md border">
            <div class="border-b p-4">
                <label for="suppliers-search" class="block text-sm font-medium">Cerca Fornitore</label>
                <div class="flex items-center gap-4">
                    <input
                        id="suppliers-search"
                        type="text"
                        class="mt-1 w-full rounded-md border px-3 py-2"
                        placeholder="Cerca per nome o codice"
                    >
                    <svg id="resetResearch" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 hover:text-slate-500 transition-colors">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b">
                        <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">ID</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Nome</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Creato il</th>
                            <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground">Azioni</th>
                        </tr>
                    </thead>
                    <tbody id="suppliers-table-body" class="[&_tr:last-child]:border-0">
                        @forelse($suppliers as $supplier)
                            <tr class="border-b transition-colors hover:bg-muted/50 supplier-row" data-search="SUP-{{ str_pad((string) $supplier->id, 3, '0', STR_PAD_LEFT) }} {{ strtolower($supplier->name) }}">
                                <td class="p-4 align-middle font-medium">#SUP-{{ str_pad((string) $supplier->id, 3, '0', STR_PAD_LEFT) }}</td>
                                <td class="p-4 align-middle">{{ $supplier->name }}</td>
                                <td class="p-4 align-middle">{{ $supplier->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td class="p-4 align-middle">
                                    <div class="flex justify-end items-center space-x-2">
                                        <a
                                            href="{{ route('suppliers.show', $supplier) }}"
                                            class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 px-3"
                                        >
                                            Dettagli
                                        </a>
                                        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare questo fornitore? Le transazioni resteranno, ma senza fornitore associato.');">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-red-300 text-red-700 hover:bg-red-50 h-8 px-3"
                                            >
                                                Elimina
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="no-suppliers-row">
                                <td colspan="4" class="p-6 text-center text-muted-foreground">
                                    Nessun fornitore disponibile. Clicca su "Nuovo Fornitore" per aggiungerne uno.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="no-search-results-row" class="hidden">
                            <td colspan="4" class="p-6 text-center text-muted-foreground">
                                Nessun risultato per la ricerca inserita.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-home-layout>
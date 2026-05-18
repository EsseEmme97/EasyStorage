<x-home-layout>
    <x-slot:title>
        Easy Storage - Dettaglio Fornitore
    </x-slot:title>
    <x-slot:additionalAsset>
        resources/js/supplierDetails.js
    </x-slot:additionalAsset> 

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <h1 class="text-3xl font-bold tracking-tight">Dettaglio Fornitore</h1>
                <p class="text-muted-foreground">Informazioni complete del fornitore selezionato.</p>
            </div>
            <a
                href="{{ route('suppliers') }}"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3"
            >
                Torna ai Fornitori
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
                    <p class="mt-1 text-lg font-semibold">#SUP-{{ str_pad((string) $supplier->id, 3, '0', STR_PAD_LEFT) }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Nome</p>
                    <div class="flex items-center gap-4">
                        <input form="update-supplier-form" type="text" name="name" value="{{ old('name', $supplier->name) }}" readonly class="mt-2 rounded-md border px-3 py-2 font-bold" placeholder="Nome Fornitore">
                        <svg id="editName" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 hover:text-slate-500 transition-colors cursor-pointer" onclick="document.querySelector('input[name=name]').removeAttribute('readonly')">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Creato il</p>
                    <p class="mt-1">{{ $supplier->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Ultimo aggiornamento</p>
                    <p class="mt-1">{{ $supplier->updated_at?->format('d/m/Y H:i') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Transazioni collegate</p>
                    <p class="mt-1">{{ $supplier->transactions_count }}</p>
                </div>
            </div>

            <div class="flex justify-end">
                <form id="update-supplier-form" action="{{ route('suppliers.update', $supplier) }}" method="post">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="action-button mr-2">
                        Aggiorna Fornitore
                    </button>
                </form>
                <form id="delete-supplier-form" action="{{ route('suppliers.destroy', $supplier) }}" method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare questo fornitore? Le transazioni resteranno, ma senza fornitore associato.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors h-9 px-3 border border-red-300 text-red-700 hover:bg-red-50">
                        Elimina Fornitore
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-home-layout>

<x-home-layout>
    <x-slot:title>
        Easy Storage - Dettaglio Fornitore
    </x-slot:title>

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
                    <p class="mt-1 text-lg font-semibold">{{ $supplier->name }}</p>
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
                <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare questo fornitore? Le transazioni resteranno, ma senza fornitore associato.');">
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

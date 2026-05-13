<x-home-layout>
    <x-slot:title>
        Easy Storage - Transazioni
    </x-slot>
	<main>
		
	</main>
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
                <button class="action-button">
                    Nuova Transazione
                </button>
            </div>
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
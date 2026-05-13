<x-home-layout>
    <x-slot:title>
        Easy Storage - Home
    </x-slot>

    <div class="flex flex-col items-center justify-center space-y-8 py-12">
        <div class="text-center space-y-2">
            <h1 class="text-4xl font-extrabold tracking-tight lg:text-5xl animate-bounce">
                Welcome to <span class="text-primary">easyStorage</span>
            </h1>
            <p class="text-xl text-muted-foreground">
                Gestisci il tuo inventario in modo semplice e veloce.
            </p>
        </div>

        <div class="w-full max-w-md p-6 border rounded-xl bg-card shadow-sm">
            <div class="flex flex-col space-y-1.5 mb-6">
                <h3 class="text-2xl font-semibold leading-none tracking-tight text-center">Registrati</h3>
                <p class="text-sm text-center text-muted-foreground">Crea un nuovo account per iniziare.</p>
            </div>
            
            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-2">
                    <label for="username" class="text-sm font-medium leading-none">Username</label>
                    <input type="text" name="name" id="username" placeholder="Inserisci il tuo username" 
                           class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                    @error('name')
                        <p class="text-destructive text-xs font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium leading-none">Email</label>
                    <input type="email" name="email" id="email" placeholder="nome@esempio.it" 
                           class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                    @error('email')
                        <p class="text-destructive text-xs font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium leading-none">Password</label>
                    <input type="password" name="password" id="password" placeholder="••••••••" 
                           class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                    @error('password')
                        <p class="text-destructive text-xs font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <button class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full" type="submit">
                    Crea Account
                </button>
            </form>
        </div>
		<div class="flex items-center gap-3 w-md">
			<span class="bg-muted-foreground h-px w-1/2"></span>
			<span class="text-muted-foreground">o</span>
			<span class="bg-muted-foreground h-px w-1/2"></span>
		</div>
		<div>
			<a href="{{ route('login') }}" class="action-button">Effettua il login</a>
		</div>
    </div>
</x-home-layout>
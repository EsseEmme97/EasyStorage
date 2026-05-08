<x-home-layout>
	<x-slot:title>
		Easy Storage
	</x-slot>
	<h1 class="text-3xl text-center">Welcome to easyStorage</h1>
	<form action="{{ route('register') }}" method="POST">
		@csrf
		<input type="text" name="username" placeholder="Username">
		@error('username')
			<p class="text-red-500 text-xs mt-1">{{ $message }}</p>
		@enderror
		<input type="text" name="email" placeholder="Email">
		@error('email')
			<p class="text-red-500 text-xs mt-1">{{ $message }}</p>
		@enderror
		<input type="password" name="password" placeholder="Password">
		@error('password')
			<p class="text-red-500 text-xs mt-1">{{ $message }}</p>
		@enderror
		<button class="border p-2 rounded" type="submit" >Register</button>
	</form>
</x-home-layout>
<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;


Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'index'])->name('home');
    Route::post('register', [AuthController::class, 'register'])->name('register');
});

Route::get('/transazioni', function() {
    return view('transazioni');
})->name('transazioni')->middleware('auth');
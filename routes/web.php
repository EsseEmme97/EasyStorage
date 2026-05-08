<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'index'])->name('login');
Route::post('register', [AuthController::class, 'register'])->name('register');

Route::get('/transazioni', function() {
    return view('transazioni');
})->name('transazioni')->middleware('auth');
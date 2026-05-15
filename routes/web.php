<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showRegistrationForm'])->name('home');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/transactions', [TransactionsController::class, 'index'])
    ->middleware('auth')
    ->name('transactions');

Route::post('/transactions/create', [TransactionsController::class, 'create'])
    ->middleware('auth')
    ->name('transactions.create');

Route::get('/suppliers', [SupplierController::class, 'index'])
    ->middleware('auth')
    ->name('suppliers');

Route::post('/suppliers/create', [SupplierController::class, 'create'])
    ->middleware('auth')
    ->name('suppliers.create');

Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])
    ->middleware('auth')
    ->name('suppliers.show');

Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])
    ->middleware('auth')
    ->name('suppliers.destroy');
<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\InventoryDashboardController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionsController;
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

Route::get('/transactions/{transaction}', [TransactionsController::class, 'showDetails'])
    ->middleware('auth')
    ->name('transactions.show');

Route::post('/transactions/{transaction}/details', [TransactionsController::class, 'storeDetails'])
    ->middleware('auth')
    ->name('transactions.details.store');

Route::put('/transactions/{transaction}/details/{transactionDetail}', [TransactionsController::class, 'updateDetails'])
    ->middleware('auth')
    ->name('transactions.details.update');

Route::delete('/transactions/{transaction}/details/{transactionDetail}', [TransactionsController::class, 'destroyDetails'])
    ->middleware('auth')
    ->name('transactions.details.destroy');

Route::get('/inventory-dashboard', [InventoryDashboardController::class, 'index'])
    ->middleware('auth')
    ->name('inventory.dashboard');

Route::get('/suppliers', [SupplierController::class, 'index'])
    ->middleware('auth')
    ->name('suppliers');

Route::post('/suppliers/create', [SupplierController::class, 'create'])
    ->middleware('auth')
    ->name('suppliers.create');

Route::get('/suppliers/{supplier}', [SupplierController::class, 'showDetails'])
    ->middleware('auth')
    ->name('suppliers.show');

Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])
    ->middleware('auth')
    ->name('suppliers.destroy');

Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])
    ->middleware('auth')
    ->name('suppliers.update');

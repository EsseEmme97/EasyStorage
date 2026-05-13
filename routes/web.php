<?php

use App\Http\Controllers\Auth\AuthController;
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
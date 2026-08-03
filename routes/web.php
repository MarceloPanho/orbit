<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'))->name('home');
Route::get('/finance/expense', fn () => view('expense'))->name('expense');
Route::get('/finance/expense-category', fn () => view('expense-category'))->name('expense-category');
Route::get('/finance/payment-method', fn () => view('payment-method'))->name('payment-method');
Route::get('/settings', fn () => view('settings'))->name('settings');

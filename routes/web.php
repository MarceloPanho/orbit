<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'))->name('home');
Route::get('/finance/expense', fn () => view('expense'))->name('expense');
Route::get('/finance/expense-category', fn () => view('expense-category'))->name('expense-category');
Route::get('/finance/payment-method', fn () => view('payment-method'))->name('payment-method');
Route::get('/finance/income', fn () => view('income'))->name('income');
Route::get('/finance/income-category', fn () => view('income-category'))->name('income-category');
Route::get('/settings', fn () => view('settings'))->name('settings');

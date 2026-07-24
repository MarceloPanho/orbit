<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'))->name('home');
Route::get('/finance/expense', fn () => view('expense'))->name('expense');
Route::get('/settings', fn () => view('settings'))->name('settings');

<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::get('/l/{listing}', fn () => abort(404))->name('listings.show');

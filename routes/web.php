<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes([
    'register' => false, // Disables registration route
]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

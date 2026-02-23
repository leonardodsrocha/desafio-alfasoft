<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Public routes – anyone can view contacts list and detail
Route::get('/', [ContactController::class, 'index'])->name('contacts.index');

// Protected routes – authenticated users only
// NOTE: contacts.create must be defined BEFORE contacts.show/{contact}
// to prevent "create" being captured as a route model binding slug.
Route::middleware('auth')->group(function () {
    Route::get('/contacts/create', [ContactController::class, 'create'])->name('contacts.create');
    Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::get('/contacts/{contact}/edit', [ContactController::class, 'edit'])->name('contacts.edit');
    Route::put('/contacts/{contact}', [ContactController::class, 'update'])->name('contacts.update');
    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');
});

// Public detail page (defined after /contacts/create to avoid conflict)
Route::get('/contacts/{contact}', [ContactController::class, 'show'])->name('contacts.show');

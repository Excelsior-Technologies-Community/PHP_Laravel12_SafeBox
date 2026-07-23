<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SafeBoxController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return redirect('/safebox');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // SafeBox
    Route::get('/safebox', [SafeBoxController::class, 'index'])->name('safebox.index');

    Route::post('/safebox', [SafeBoxController::class, 'store'])
        ->name('safebox.store');

    Route::delete('/safebox/{id}', [SafeBoxController::class, 'destroy'])
        ->name('safebox.destroy');

    // Trash
    Route::get('/safebox-trash', [SafeBoxController::class, 'trash'])
        ->name('safebox.trash');

    Route::put('/safebox/{id}/restore', [SafeBoxController::class, 'restore'])
        ->name('safebox.restore');

    Route::delete('/safebox/{id}/force-delete', [SafeBoxController::class, 'forceDelete'])
        ->name('safebox.forceDelete');
});

require __DIR__ . '/auth.php';

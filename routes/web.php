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

    // SafeBox Vault PIN and Auto-Lock
    Route::post('/safebox/verify-pin', [SafeBoxController::class, 'verifyPin'])->name('safebox.verify-pin');
    Route::post('/safebox/set-pin', [SafeBoxController::class, 'setPin'])->name('safebox.set-pin');
    Route::post('/safebox/lock-vault', [SafeBoxController::class, 'lockVault'])->name('safebox.lock-vault');

    // SafeBox CRUD & Features
    Route::get('/safebox', [SafeBoxController::class, 'index'])->name('safebox.index');
    Route::post('/safebox', [SafeBoxController::class, 'store'])->name('safebox.store');
    Route::put('/safebox/{id}', [SafeBoxController::class, 'update'])->name('safebox.update');
    Route::delete('/safebox/{id}', [SafeBoxController::class, 'destroy'])->name('safebox.destroy');
    Route::get('/safebox/{id}/versions', [SafeBoxController::class, 'versions'])->name('safebox.versions');
    Route::get('/safebox-export', [SafeBoxController::class, 'export'])->name('safebox.export');

    // Trash
    Route::get('/safebox-trash', [SafeBoxController::class, 'trash'])->name('safebox.trash');
    Route::put('/safebox/{id}/restore', [SafeBoxController::class, 'restore'])->name('safebox.restore');
    Route::delete('/safebox/{id}/force-delete', [SafeBoxController::class, 'forceDelete'])->name('safebox.forceDelete');
});

require __DIR__ . '/auth.php';

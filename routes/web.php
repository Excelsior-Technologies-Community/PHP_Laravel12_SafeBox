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

    Route::get('/safebox', [SafeBoxController::class, 'index'])->name('safebox.index');
    Route::post('/safebox', [SafeBoxController::class, 'store']);
    Route::delete('/safebox/{id}', [SafeBoxController::class, 'destroy']);
});

require __DIR__ . '/auth.php';

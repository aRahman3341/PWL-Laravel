<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'beranda'])->name('beranda');
Route::get('/profil', [PageController::class, 'profil'])->name('profil');
Route::get('/layanan', [PageController::class, 'layanan'])->name('layanan');
Route::get('/pengalaman', [PageController::class, 'pengalaman'])->name('pengalaman');
Route::get('/kontak', [PageController::class, 'kontak'])->name('kontak');
Route::post('/kontak', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('kontak.kirim');

require __DIR__ . '/admin.php';
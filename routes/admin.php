<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CodeController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DownloadController as AdminDownloadController;
use App\Http\Controllers\Admin\FileController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\PhotoController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ConfidentialController;
use App\Http\Controllers\DownloadController;
use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\AdminRole;
use Illuminate\Support\Facades\Route;

// ---------- Dokumen lama: sekarang semua lewat tombol Download ----------
Route::get('/dokumen-rahasia', fn () => redirect(route('beranda') . '#dokumen'))->name('rahasia');

// ---------- Download dengan persetujuan / kode manual (publik) ----------
Route::get('/dl/daftar', [DownloadController::class, 'files'])->name('dl.daftar');
Route::post('/dl/minta', [DownloadController::class, 'request'])->middleware('throttle:6,1,dlminta')->name('dl.minta');
Route::post('/dl/manual', [DownloadController::class, 'manual'])->middleware('throttle:10,1,dlmanual')->name('dl.manual');
Route::get('/dl/status', [DownloadController::class, 'status'])->middleware('throttle:60,1,dlstatus')->name('dl.status');
Route::post('/dl/verifikasi', [DownloadController::class, 'verify'])->middleware('throttle:12,1,dlverif')->name('dl.verifikasi');
Route::post('/dl/batal', [DownloadController::class, 'cancel'])->middleware('throttle:20,1,dlbatal')->name('dl.batal');
Route::post('/dl/selesai', [DownloadController::class, 'finish'])->middleware('throttle:20,1,dlselesai')->name('dl.selesai');
Route::get('/dl/unduh/{id}/{file}', [DownloadController::class, 'download'])->name('dl.unduh');
// Tautan dari email (bertanda tangan): tinjau lalu setujui / tolak
Route::get('/dl/tinjau/{id}', [DownloadController::class, 'review'])->middleware('signed')->name('dl.review');
Route::post('/dl/tinjau/{id}/{act}', [DownloadController::class, 'decide'])->middleware('signed')->name('dl.decide');

// ---------- Panel admin (khusus pegawai) ----------
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'form'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1,adminlogin')->name('login.post');

    Route::middleware(AdminAuth::class)->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');

        // Konten publik: semua role (admin, super_admin, management)
        Route::get('company', [CompanyController::class, 'edit'])->name('company');
        Route::put('company', [CompanyController::class, 'update'])->name('company.update');

        Route::get('photos', [PhotoController::class, 'index'])->name('photos');
        Route::post('photos/{slot}', [PhotoController::class, 'store'])->where('slot', '[a-z_]+')->name('photos.store');
        Route::delete('photos/{slot}', [PhotoController::class, 'destroy'])->where('slot', '[a-z_]+')->name('photos.destroy');

        Route::get('messages', [MessageController::class, 'index'])->name('messages');
        Route::put('messages/{id}', [MessageController::class, 'update'])->name('messages.update');

        Route::prefix('content/{type}')->where(['type' => 'services|projects|management|legal'])
            ->name('content.')->group(function () {
                Route::get('/', [ContentController::class, 'index'])->name('index');
                Route::get('create', [ContentController::class, 'create'])->name('create');
                Route::post('/', [ContentController::class, 'store'])->name('store');
                Route::get('{id}/edit', [ContentController::class, 'edit'])->name('edit');
                Route::put('{id}', [ContentController::class, 'update'])->name('update');
                Route::put('{id}/toggle', [ContentController::class, 'toggle'])->name('toggle');
                Route::delete('{id}', [ContentController::class, 'destroy'])->name('destroy');
            });

        // Akun, kode akses, dokumen rahasia: super_admin dan management saja
        Route::middleware(AdminRole::class . ':super_admin,management')->group(function () {
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::put('users/{id}', [UserController::class, 'update'])->name('users.update');
            Route::delete('users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

            Route::get('codes', [CodeController::class, 'index'])->name('codes.index');
            Route::post('codes', [CodeController::class, 'store'])->name('codes.store');
            Route::put('codes/{id}', [CodeController::class, 'toggle'])->name('codes.toggle');
            Route::delete('codes/{id}', [CodeController::class, 'destroy'])->name('codes.destroy');

            Route::get('downloads', [AdminDownloadController::class, 'index'])->name('downloads');
            Route::post('downloads/{id}/{act}', [AdminDownloadController::class, 'decide'])->name('downloads.decide');

            Route::get('settings', [SettingController::class, 'edit'])->name('settings');
            Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
            Route::post('settings/test-mail', [SettingController::class, 'testMail'])->name('settings.testmail');

            Route::get('files', [FileController::class, 'index'])->name('files.index');
            Route::post('files', [FileController::class, 'store'])->name('files.store');
            Route::put('files/{id}/toggle', [FileController::class, 'toggle'])->name('files.toggle');
            Route::delete('files/{id}', [FileController::class, 'destroy'])->name('files.destroy');
        });

        // Log aktivitas: management saja
        Route::get('audit', [DashboardController::class, 'audit'])
            ->middleware(AdminRole::class . ':management')->name('audit');
    });
});

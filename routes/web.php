<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Kemiskinan\DokumenKeluargaController;
use App\Http\Controllers\Kemiskinan\KeluargaController;
use App\Http\Controllers\Kemiskinan\VerifikasiKemiskinanController;
use App\Http\Controllers\Master\DesaKelurahanController;
use App\Http\Controllers\Master\KecamatanController;
use App\Http\Controllers\Master\PetugasController;
use App\Http\Controllers\Master\ReferensiController;
use App\Http\Controllers\Master\WilayahController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Stunting\AnakController;
use App\Http\Controllers\Stunting\DokumenAnakController;
use App\Http\Controllers\Stunting\PengukuranController;
use App\Http\Controllers\Stunting\VerifikasiStuntingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->prefix('kemiskinan')->name('kemiskinan.')->group(function () {
    Route::get('/data', [KeluargaController::class, 'index'])->name('index');
    Route::get('/create', [KeluargaController::class, 'create'])->name('create');
    Route::post('/', [KeluargaController::class, 'store'])->name('store');

    Route::get('/verifikasi', [VerifikasiKemiskinanController::class, 'index'])->name('verifikasi.index');

    Route::get('/keluarga/{keluarga}', [KeluargaController::class, 'show'])->name('show');
    Route::get('/keluarga/{keluarga}/edit', [KeluargaController::class, 'edit'])->name('edit');
    Route::put('/keluarga/{keluarga}', [KeluargaController::class, 'update'])->name('update');
    Route::delete('/keluarga/{keluarga}', [KeluargaController::class, 'destroy'])->name('destroy');
    Route::post('/keluarga/{keluarga}/kirim', [KeluargaController::class, 'kirim'])->name('kirim');
    Route::post('/keluarga/{keluarga}/verifikasi', [VerifikasiKemiskinanController::class, 'store'])->name('verifikasi.store');

    Route::post('/keluarga/{keluarga}/dokumen', [DokumenKeluargaController::class, 'store'])->name('dokumen.store');
    Route::delete('/keluarga/{keluarga}/dokumen/{dokumen}', [DokumenKeluargaController::class, 'destroy'])->name('dokumen.destroy');
});

Route::middleware(['auth', 'verified'])->prefix('stunting')->name('stunting.')->group(function () {
    Route::get('/data', [AnakController::class, 'index'])->name('index');
    Route::get('/create', [AnakController::class, 'create'])->name('create');
    Route::post('/', [AnakController::class, 'store'])->name('store');

    Route::get('/verifikasi', [VerifikasiStuntingController::class, 'index'])->name('verifikasi.index');

    Route::get('/anak/{anak}', [AnakController::class, 'show'])->name('show');
    Route::get('/anak/{anak}/edit', [AnakController::class, 'edit'])->name('edit');
    Route::put('/anak/{anak}', [AnakController::class, 'update'])->name('update');
    Route::delete('/anak/{anak}', [AnakController::class, 'destroy'])->name('destroy');
    Route::post('/anak/{anak}/kirim', [AnakController::class, 'kirim'])->name('kirim');
    Route::post('/anak/{anak}/verifikasi', [VerifikasiStuntingController::class, 'store'])->name('verifikasi.store');

    Route::post('/anak/{anak}/dokumen', [DokumenAnakController::class, 'store'])->name('dokumen.store');
    Route::delete('/anak/{anak}/dokumen/{dokumen}', [DokumenAnakController::class, 'destroy'])->name('dokumen.destroy');

    Route::post('/anak/{anak}/pengukuran', [PengukuranController::class, 'store'])->name('pengukuran.store');
    Route::delete('/anak/{anak}/pengukuran/{pengukuran}', [PengukuranController::class, 'destroy'])->name('pengukuran.destroy');
});

Route::middleware(['auth', 'verified'])->prefix('master')->name('master.')->group(function () {
    Route::get('/wilayah', [WilayahController::class, 'index'])->name('wilayah.index');
    Route::post('/wilayah/kecamatan', [KecamatanController::class, 'store'])->name('wilayah.kecamatan.store');
    Route::put('/wilayah/kecamatan/{kecamatan}', [KecamatanController::class, 'update'])->name('wilayah.kecamatan.update');
    Route::delete('/wilayah/kecamatan/{kecamatan}', [KecamatanController::class, 'destroy'])->name('wilayah.kecamatan.destroy');
    Route::post('/wilayah/desa-kelurahan', [DesaKelurahanController::class, 'store'])->name('wilayah.desa-kelurahan.store');
    Route::put('/wilayah/desa-kelurahan/{desaKelurahan}', [DesaKelurahanController::class, 'update'])->name('wilayah.desa-kelurahan.update');
    Route::delete('/wilayah/desa-kelurahan/{desaKelurahan}', [DesaKelurahanController::class, 'destroy'])->name('wilayah.desa-kelurahan.destroy');

    Route::get('/referensi', [ReferensiController::class, 'index'])->name('referensi.index');
    Route::post('/referensi', [ReferensiController::class, 'store'])->name('referensi.store');
    Route::put('/referensi/{referensi}', [ReferensiController::class, 'update'])->name('referensi.update');
    Route::delete('/referensi/{referensi}', [ReferensiController::class, 'destroy'])->name('referensi.destroy');

    Route::get('/petugas', [PetugasController::class, 'index'])->name('petugas.index');
    Route::get('/petugas/create', [PetugasController::class, 'create'])->name('petugas.create');
    Route::post('/petugas', [PetugasController::class, 'store'])->name('petugas.store');
    Route::get('/petugas/{petugas}/edit', [PetugasController::class, 'edit'])->name('petugas.edit');
    Route::put('/petugas/{petugas}', [PetugasController::class, 'update'])->name('petugas.update');
    Route::patch('/petugas/{petugas}/status', [PetugasController::class, 'toggleStatus'])->name('petugas.toggle-status');
    Route::post('/petugas/{petugas}/reset-password', [PetugasController::class, 'resetPassword'])->name('petugas.reset-password');
});

require __DIR__.'/auth.php';

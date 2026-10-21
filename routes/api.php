<?php

use App\Http\Controllers\ContractController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\TaxParameterController;
use App\Http\Controllers\TerminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Modul D-2 (Kontrak Komersial, Termin, & Invoice)
|--------------------------------------------------------------------------
| Kelompok: 2 / D (PT Anaheim Nimbus Universal ERP)
| Berdasarkan Spesifikasi OpenAPI v0.1.0 (Pertemuan 3)
*/

Route::prefix('v1')->group(function () {

    // Rute Operasi Keuangan (Wajib Peran: petugas_keuangan)
    Route::middleware(['auth.d2:petugas_keuangan'])->group(function () {

        // API-K02-02: Menampilkan daftar termin siap ditagih beserta status PO dari D-1
        Route::get('/termins', [TerminController::class, 'index']);

        // API-K02-03: Menerbitkan invoice baru dari termin siap ditagih
        Route::post('/invoices', [InvoiceController::class, 'store']);

        // API-K02-05: Detail lengkap invoice, status, keterkaitan, dan riwayat audit
        Route::get('/invoices/{invoiceId}', [InvoiceController::class, 'show']);

        // API-K02-04: Parameter pajak yang berlaku
        Route::get('/tax-parameters/applicable', [TaxParameterController::class, 'applicable']);
    });

    // Rute Operasi Kontrak Komersial (Peran: admin, pm, am_pm, atau petugas_keuangan)
    Route::middleware(['auth.d2:admin|pm|am_pm|petugas_keuangan'])->group(function () {

        // API-K02-01: Mencatat kontrak komersial baru yang terikat ke proyek D-1
        Route::post('/contracts', [ContractController::class, 'store']);
    });
});

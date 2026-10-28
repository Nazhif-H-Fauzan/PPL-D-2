<?php

use Illuminate\Support\Facades\Route;

$dashboard = function (string $heading = 'Overview') {
    return view('welcome', [
        'title' => $heading . ' | InvoiceKu',
        'pageHeading' => $heading,
    ]);
};

Route::get('/', $dashboard)->name('dashboard');
Route::get('/dashboard', $dashboard)->name('dashboard.index');

Route::get('/projects', fn () => $dashboard('Proyek'))->name('projects');
Route::get('/contracts', fn () => $dashboard('Kontrak'))->name('contracts');
Route::get('/termins', fn () => view('termins.index', [
    'title' => 'Daftar Termin | InvoiceKu',
    'pageHeading' => 'Termin siap ditagih',
]))->name('termins');
Route::get('/invoices', fn () => view('invoices.create', [
    'title' => 'Buat Invoice | InvoiceKu',
    'pageHeading' => 'Buat invoice',
]))->name('invoices');
Route::get('/invoices/{invoiceId}', fn (string $invoiceId) => view('invoices.show', [
    'title' => 'Detail Invoice | InvoiceKu',
    'pageHeading' => 'Detail invoice',
    'invoiceId' => $invoiceId,
]))->name('invoices.show');

# Modul D-2: Kontrak Komersial, Termin, dan Invoice
## Enterprise Resource Planning (ERP) PT Anaheim Nimbus Universal

Berkas ini memuat panduan cara membuka spesifikasi OpenAPI dan menjalankan mock endpoint API untuk Modul D-2 (Kelompok 2 / Kelas D).

---

### 1. Berkas Spesifikasi OpenAPI
* **YAML**: [openapi.yaml](file:///c:/Users/ayupu/laravel-app/openapi.yaml) (atau `docs/openapi.yaml`)
* **JSON**: [openapi.json](file:///c:/Users/ayupu/laravel-app/openapi.json) (atau `docs/openapi.json`)
* **Dokumen Tugas Pertemuan 3**: [TUGAS_PERTEMUAN_3_OPENAPI.md](file:///c:/Users/ayupu/laravel-app/TUGAS_PERTEMUAN_3_OPENAPI.md)

### 2. Cara Membuka Spesifikasi OpenAPI
Anda dapat membuka dan memvisualisasikan berkas `openapi.yaml` dengan beberapa cara:
1. **Online Swagger Editor**:
   - Buka [https://editor.swagger.io/](https://editor.swagger.io/)
   - Salin isi dari `openapi.yaml` atau klik *File -> Import File* lalu pilih `openapi.yaml`.
2. **Online Redocly Viewer**:
   - Buka [https://redocly.github.io/redoc/](https://redocly.github.io/redoc/) atau VS Code Extension *OpenAPI (Swagger) Editor* / *Redoc Viewer*.
3. **Swagger UI Lokal / Scalar**:
   - Berkas YAML dan JSON telah disiapkan di root proyek dan folder `docs/`.

### 3. Folder Contoh JSON (`examples/`)
Tersedia contoh respons fiktif untuk skenario pengujian:
* `examples/invoice_create_success.json` : Contoh respons HTTP 201 Created saat penerbitan invoice berhasil.
* `examples/invoice_create_error_po_blocked.json` : Contoh respons HTTP 422 Unprocessable Entity saat penagihan diblokir karena ketiadaan PO di D-1.
* `examples/error_validation_400.json` : Contoh respons HTTP 400 Bad Request saat validasi input gagal.
* `examples/termins_get_success.json` : Contoh respons HTTP 200 OK untuk daftar termin siap ditagih (UI-K02-01).
* `examples/invoice_get_detail_success.json` : Contoh respons HTTP 200 OK untuk detail invoice lengkap (UI-K02-03).
* `examples/error_403_forbidden.json` : Contoh respons HTTP 403 Forbidden saat hak akses bukan Petugas Keuangan.

### 4. Menjalankan Mock Endpoint pada Laravel
Endpoint dasar telah diintegrasikan pada berkas `routes/api.php` di dalam proyek Laravel ini.
Untuk menjalankan server secara lokal:
```bash
php artisan serve
```
Server akan aktif di `http://localhost:8000`. Anda dapat menguji endpoint berikut:
* `GET  /api/v1/termins` (Daftar termin siap ditagih)
* `POST /api/v1/invoices` (Penerbitan invoice baru)
* `GET  /api/v1/invoices/{invoiceId}` (Detail invoice)
* `GET  /api/v1/tax-parameters/applicable` (Tarif pajak berlaku)

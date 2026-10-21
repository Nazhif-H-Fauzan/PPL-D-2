# Modul D-2: Kontrak Komersial, Termin, dan Invoice
## Enterprise Resource Planning (ERP) PT Anaheim Nimbus Universal

Berkas ini memuat dokumentasi API, panduan membuka dan menguji spesifikasi OpenAPI menggunakan **Thunder Client (VS Code)**, serta petunjuk menjalankan backend server Modul D-2 (Kelompok 2 / Kelas D).

---

### 1. Berkas Spesifikasi OpenAPI
* **YAML**: [openapi.yaml](file:///c:/Users/ayupu/laravel-app/openapi.yaml) (atau `docs/openapi.yaml`)
* **JSON**: [openapi.json](file:///c:/Users/ayupu/laravel-app/openapi.json) (atau `docs/openapi.json`)
* **Koleksi Siap Pakai Thunder Client**: [thunder-collection_modul_d2.json](file:///c:/Users/ayupu/laravel-app/thunder-collection_modul_d2.json)

---

### 2. Cara Membuka & Menguji Spesifikasi OpenAPI Menggunakan Thunder Client

Pengujian API dilakukan secara langsung di dalam Visual Studio Code menggunakan ekstensi **Thunder Client**.

#### Langkah 1: Pasang Ekstensi Thunder Client di VS Code
1. Buka menu **Extensions** di VS Code (`Ctrl + Shift + X`).
2. Cari `Thunder Client` (oleh *Ranga Vadhineni*), lalu klik **Install**.
3. Ikon petir (**Thunder Client**) akan muncul pada bilah aktivitas (*Activity Bar*) sebelah kiri VS Code.

---

#### Langkah 2: Membuka Spesifikasi ke Thunder Client (Dua Pilihan Cara)

##### Pilihan A: Impor Berkas Koleksi Siap Pakai (Sangat Direkomendasikan)
Koleksi lengkap beserta contoh body dan header otentikasi telah disiapkan di berkas `thunder-collection_modul_d2.json`:
1. Klik ikon **Thunder Client** (⚡) pada bilah kiri VS Code.
2. Buka tab **Collections**.
3. Klik ikon menu opsi **`...`** (di sebelah kanan tulisan *Collections*).
4. Pilih **Import**.
5. Pilih berkas [thunder-collection_modul_d2.json](file:///c:/Users/ayupu/laravel-app/thunder-collection_modul_d2.json) dari root proyek.
6. Koleksi **"Modul D-2 ERP (Kontrak, Termin, Invoice)"** akan langsung muncul lengkap dengan 8 skenario pengujian (sukses 200/201, validasi 400, izin 403, penolakan PO 422, dsb.).

##### Pilihan B: Impor Langsung dari Berkas OpenAPI (`openapi.yaml` / `openapi.json`)
1. Klik ikon **Thunder Client** (⚡) -> tab **Collections**.
2. Klik ikon menu opsi **`...`** -> pilih **Import**.
3. Pada jendela yang muncul, pilih opsi/tab **OpenAPI**.
4. Arahkan dan pilih berkas [openapi.yaml](file:///c:/Users/ayupu/laravel-app/openapi.yaml) atau [openapi.json](file:///c:/Users/ayupu/laravel-app/openapi.json).
5. Klik **Import**. Seluruh folder dan endpoint dari OpenAPI akan otomatis di-*generate* menjadi request Thunder Client.

---

#### Langkah 3: Menjalankan Request Pengujian di Thunder Client
1. Pastikan server lokal backend Laravel sedang aktif:
   ```bash
   php artisan serve
   ```
   *(Server aktif di `http://127.0.0.1:8000`)*
2. Buka salah satu request di tab **Thunder Client** -> **Collections**, contoh:
   * **`1. GET Daftar Termin Siap Ditagih (200 OK)`**
   * **`3. POST Terbitkan Invoice Baru - Sukses (201 Created)`**
   * **`4. POST Invoice - Penolakan PO Belum Tersedia (422 PO Blocked)`**
3. Periksa tab **Headers**, pastikan header berikut terpasang:
   * `Authorization`: `Bearer emp-demo-012`
   * `Accept`: `application/json`
4. Klik tombol biru **Send**. Respon status (200, 201, 400, 422) dan data JSON akan langsung tampil di panel sebelah kanan.

---

### 3. Folder Contoh JSON (`examples/`)
Tersedia contoh payload permintaan dan respons bawaan OpenAPI untuk skenario pengujian:
* `examples/invoice_create_success.json` : Contoh respons HTTP 201 Created saat penerbitan invoice berhasil.
* `examples/invoice_create_error_po_blocked.json` : Contoh respons HTTP 422 Unprocessable Entity saat penagihan diblokir karena ketiadaan PO di D-1.
* `examples/error_validation_400.json` : Contoh respons HTTP 400 Bad Request saat validasi input gagal.
* `examples/termins_get_success.json` : Contoh respons HTTP 200 OK untuk daftar termin siap ditagih (UI-K02-01).
* `examples/invoice_get_detail_success.json` : Contoh respons HTTP 200 OK untuk detail invoice lengkap (UI-K02-03).
* `examples/error_403_forbidden.json` : Contoh respons HTTP 403 Forbidden saat hak akses bukan Petugas Keuangan.

---

### 4. Menjalankan Backend API pada Laravel

#### A. Migrasi & Seed Database Bawaan
Untuk menyiapkan database SQLite lokal beserta data contoh OpenAPI:
```bash
php artisan migrate:fresh --seed
```

#### B. Menjalankan Automated Feature Test
Untuk memverifikasi seluruh aturan bisnis dan status kode HTTP:
```bash
php artisan test --filter=ModulD2ApiTest
```

#### C. Menjalankan Server Lokal
```bash
php artisan serve
```
Server akan aktif di `http://127.0.0.1:8000`. Anda dapat menguji endpoint berikut:
* `GET  /api/v1/termins` (Daftar termin siap ditagih & status PO D-1)
* `POST /api/v1/invoices` (Penerbitan invoice baru per termin)
* `GET  /api/v1/invoices/{invoiceId}` (Detail lengkap invoice & riwayat)
* `GET  /api/v1/tax-parameters/applicable` (Tarif pajak berlaku)
* `POST /api/v1/contracts` (Pencatatan kontrak komersial)

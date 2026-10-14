# Proyek Perangkat Lunak | Template Tugas Pertemuan 3
# Template Tugas OpenAPI Versi Awal

Gunakan dokumen ini untuk menjelaskan kontrak API yang akan dipakai kelompok lain. Berkas OpenAPI (YAML atau JSON) adalah artefak utama; isian di sini membantu dosen dan kelompok konsumen memeriksa keputusan serta contoh responsnya.

---

### Informasi Dokumen
* **Kelompok dan kelas**: 2 / D
* **Modul atau layanan**: Kontrak komersial, termin, dan invoice (D-2) — *Enterprise Resource Planning (ERP) Software untuk PT Anaheim Nimbus Universal*
* **Penyusun dan reviewer internal**: Rudol Prasetyo Sinurat (233040152) & Roy Martin Silaban (233040130) / Wanda Gusdya Purnama, ST., MT.
* **Tanggal dan versi kontrak**: 2026-10-14 / v0.1
* **Tautan repositori dan lokasi OpenAPI**: `[URL repo GitHub]` / `openapi.yaml` (serta `openapi.json`)

---

## 1. Berkas yang Dikumpulkan

| Artefak | Isi yang Dapat Diperiksa |
| :--- | :--- |
| `openapi.yaml` atau `openapi.json` | Versi OpenAPI 3.0.3, info, servers, paths (`/api/v1/termins`, `/api/v1/invoices`, `/api/v1/invoices/{invoiceId}`, `/api/v1/tax-parameters/applicable`, `/api/v1/contracts`), skema komponen, autentikasi Bearer JWT, serta contoh respons lengkap. |
| `examples/` | Berkas JSON fiktif untuk keberhasilan dan kegagalan:<br>• `examples/invoice_create_success.json` (201 Created)<br>• `examples/invoice_create_error_po_blocked.json` (422 PO belum tersedia)<br>• `examples/error_validation_400.json` (400 Input tidak valid)<br>• `examples/termins_get_success.json` (200 OK daftar termin)<br>• `examples/invoice_get_detail_success.json` (200 OK detail invoice)<br>• `examples/error_403_forbidden.json` (403 Akses ditolak) |
| Dokumen ini | Cakupan layanan, keputusan lintas layanan, inventaris operasi, rincian operasi awal, batas data, catatan review produsen-konsumen, dan pertanyaan terbuka. |
| README / tautan repo | Petunjuk membuka spesifikasi melalui Swagger Editor/Redoc, struktur repositori, data fiktif, serta cara menjalankan mock endpoint dasar berbasis Laravel. |

---

## 2. Keputusan Lintas Layanan

Salin hasil kesepakatan pertemuan 2. Format dan konvensi diselaraskan antar-kelompok pengembang ERP.

| Topik | Keputusan yang Dipakai pada Kontrak | Sumber / Status |
| :--- | :--- | :--- |
| **employeeId / clientId / projectId** | • **`projectId`**: Dimiliki dan diterbitkan kelompok **D-1** (Manajemen Proyek). Bertipe `string` (UUID v7 D-1, contoh: `"prj-demo-001"`). Modul D-2 hanya menyimpan ID proyek sebagai referensi (`ref D-1`) pada entitas Kontrak (`ENT-K02-02`) tanpa menyalin datanya.<br>• **`employeeId`**: Dimiliki kelompok **F-2** (SDM/Karyawan). Bertipe `string` (contoh: `"emp-demo-007"`, `"SDM-0001"`). Digunakan untuk validasi pembuat kontrak dan invoice (`createdBy`).<br>• **`clientId`**: Dimiliki kelompok **D-1 / F-6**. D-2 tidak menyimpan `clientId` secara langsung. Status klien sementara (`isTemporaryClient`) dibaca dari D-1 via `projectId`. Jika proyek masih berklien sementara (`isTemporaryClient = true`), kontrak/termin dapat dibuat namun penerbitan invoice diblokir (Q-D2-07).<br>• **ID Entitas D-2** (`contractId`, `terminId`, `invoiceId`, `taxParamId`, `receiptId`): Dibuat sistem D-2 dengan tipe `string` UUID (contoh: `"ktr-demo-001"`, `"trm-demo-001"`, `"inv-demo-001"`). | Pertemuan 2 (Dokumen Skema Data & Kontrak API) / Disepakati |
| **Tanggal dan waktu** | • **Tanggal (`date`)**: Format ISO 8601 `YYYY-MM-DD` (contoh: `"2026-11-01"`). Digunakan pada `invoiceDate`, `dueDate`, `receiptDate`, `effectiveFrom`, `effectiveTo`.<br>• **Waktu (`datetime`)**: Format ISO 8601 UTC dengan akhiran `Z` (`YYYY-MM-DDTHH:mm:ssZ`, contoh: `"2026-11-01T02:00:00Z"`). Pada tampilan antarmuka (UI) dikonversi dan ditampilkan dalam WIB (Asia/Jakarta, UTC+7).<br>• **Nominal Finansial Rupiah**: Dikirim sebagai **`string desimal`** dengan 2 digit presisi di belakang koma (contoh: `"100000000.00"` / format `decimal(18,2)`) untuk mencegah kehilangan akurasi floating point. | Pertemuan 2 (Dokumen Kesepakatan Lintas Layanan) / Disepakati |
| **Status proses** | • Standar penulisan enum: Huruf besar (**UPPERCASE**) dengan pemisah garis bawah.<br>• **Termin (`ENT-K02-03`)**: `BELUM_SIAP` → `SIAP_DITAGIH` (saat syarat jatuh tempo/milestone terpenuhi) → `SUDAH_DITAGIHKAN` (berubah otomatis saat invoice berhasil dibuat).<br>• **Invoice (`ENT-K02-04`)**: `TERTAGIH` (status awal saat dibuat) → `TERBAYAR` (berubah otomatis bila total penerimaan bank `ENT-K02-06` sama dengan `totalAmount`).<br>• **Status PO (`poStatus` dari D-1)**: `PO_ADA` / `PO_TIDAK_ADA` (merefleksikan boolean `poReceived: true/false` di D-1). Ketiadaan PO memblokir penerbitan invoice. | Pertemuan 2 (Dokumen Fitur Minimum & Skema Data) / Disepakati |
| **Autentikasi** | • **Skema Token**: HTTP Bearer Token (JSON Web Token - JWT) yang diterbitkan oleh modul Akun & Autentikasi (**F-1**), dikirimkan melalui header `Authorization: Bearer <token>`.<br>• **Otorisasi Server**: Modul D-2 memvalidasi token dan hak akses peran di sisi server. Hak membuat dan melihat invoice dibatasi khusus peran **Petugas Keuangan** (`petugas_keuangan`). Peran lain ditolak dengan respons HTTP `403 Forbidden`.<br>• **Audit Trail**: Identitas pembuat transaksi (`createdBy`) diambil langsung dari klaim `employeeId` di dalam token yang valid, bukan dari input body client. | Pertemuan 2 (Daftar Kontrak API) / Disepakati bersama F-1 |

---

## 3. Inventaris Operasi API

Daftar operasi yang diterbitkan kelompok 2 (D-2) untuk fitur minimum:

| ID | Metode dan Path | Tujuan / Konsumen | Hak Akses | Status |
| :--- | :--- | :--- | :--- | :--- |
| **API-K02-01** | `POST /api/v1/contracts` | Mencatat kontrak komersial baru yang terkait ke `projectId` kanonis D-1 (FTR-K02-02, ENT-K02-02). | Petugas Berwenang (Admin/PM) | Draf (Internal / Antar-modul) |
| **API-K02-02** | `GET /api/v1/termins` | Menampilkan daftar termin siap ditagih beserta status ketersediaan PO dari D-1 untuk layar UI-K02-01, dilengkapi filter `projectId` dan `status`. | Petugas Keuangan | **Siap Direview** |
| **API-K02-03** | `POST /api/v1/invoices` | Menerbitkan invoice per termin siap ditagih; sistem menghitung DPP, PPN, PPh, total tagihan, memvalidasi PO D-1, dan memperbarui status termin (UI-K02-02, FTR-K02-04). | Petugas Keuangan | **Siap Direview (Operasi Inti)** |
| **API-K02-04** | `GET /api/v1/tax-parameters/applicable` | Mengambil tarif parameter PPN dan PPh yang berlaku untuk pratinjau kalkulasi tagihan di form UI-K02-02. | Petugas Keuangan | Draf (Internal) |
| **API-K02-05** | `GET /api/v1/invoices/{invoiceId}` | Menampilkan detail lengkap invoice, status penagihan, riwayat audit, dan keterkaitan telusur proyek/kontrak/termin/PO untuk UI-K02-03 dan ringkasan modul lain. | Petugas Keuangan / Modul Konsumen | **Siap Direview** |

---

## 4. Rincian Satu Operasi

### Operasi Utama: API-K02-03 (Pembuatan Invoice per Termin)

* **ID operasi, fitur, dan sumber kebutuhan**: `API-K02-03` (`createInvoice`) / `FTR-K02-04` (Pembuatan invoice per termin dengan DPP, PPN, PPh) / Peta Aktor D-2 Langkah 4, Backlog BL-03, BL-04, Dokumen Rancangan Antarmuka UI-K02-02.
* **Pemilik API dan konsumen**: Kelompok 2 / D (Penyedia D-2) / Konsumen: Antarmuka Internal D-2 (Petugas Keuangan) dan Modul Konsumen Terkait.
* **Metode, path, dan tujuan**: `POST /api/v1/invoices` — Menerbitkan invoice komersial dari termin yang telah siap ditagih, melakukan kalkulasi otomatis PPN (11%) dan PPh (2%), memeriksa ketiadaan blokir PO dan status klien resmi ke Modul D-1, mengubah status termin menjadi `SUDAH_DITAGIHKAN`, serta mengembalikan data invoice dengan status awal `TERTAGIH`.
* **Autentikasi dan otorisasi**: Bearer Token JWT pada header `Authorization`, peran pengguna harus `petugas_keuangan`. Field `createdBy` diekstrak dari klaim token (`employeeId`). Pengguna tanpa hak akses ditolak dengan HTTP `403 Forbidden`.

#### Masukan (Request)

| Masukan | Lokasi / Tipe / Wajib | Aturan dan Contoh Fiktif |
| :--- | :--- | :--- |
| `Authorization` | header / string / ya | Skema Bearer Token JWT dari modul F-1. Contoh: `Bearer eyJhbGciOi...` |
| `terminId` | body / string (UUID) / ya | Harus merupakan termin terdaftar, berstatus `SIAP_DITAGIH`, dan belum pernah diterbitkan invoice. Contoh: `"trm-demo-001"` |
| `invoiceDate` | body / date / ya | Format ISO 8601 `YYYY-MM-DD`. Pesan jika kosong: *"Tanggal invoice wajib diisi"*. Contoh: `"2026-11-01"` |
| `dppAmount` | body / string desimal / ya | Nilai Dasar Pengenaan Pajak bertipe numerik desimal > 0 (bawaan dari nilai termin). Pesan validasi: *"DPP harus lebih dari 0"*. Contoh: `"100000000.00"` |
| `ppnAmount, pphAmount, totalAmount` | body / tidak diterima | **Tidak diterima dari body request client**; dihitung secara otomatis dan aman di sisi server berdasarkan parameter pajak aktif. Nilai dari pemanggil diabaikan. |

#### Respons

| Respons | Skema / Field Penting | Contoh dan Dampak bagi Konsumen |
| :--- | :--- | :--- |
| **`201 Created`** | Skema `CreateInvoiceResponse`: `invoiceId`, `invoiceNumber`, `terminId`, `invoiceDate`, `dppAmount`, `ppnAmount`, `pphAmount`, `totalAmount`, `taxParamId`, `status: "TERTAGIH"`, `createdBy`, `createdAt`. | `examples/invoice_create_success.json`; UI menampilkan banner sukses hijau, memperbarui status termin menjadi `SUDAH_DITAGIHKAN` sehingga tidak muncul lagi di daftar siap ditagih, lalu mengarahkan ke halaman detail invoice (UI-K02-03). |
| **`400 Bad Request`** | Skema `ErrorResponse`: `code: "VALIDATION_ERROR"`, `errors: { field: [pesan] }`. | `examples/error_validation_400.json`; Form UI menandai kolom bermasalah dengan warna merah dan pesan spesifik tanpa menghapus isian pengguna. |
| **`401 Unauthorized`** | Skema `ErrorResponse`: `code: "UNAUTHORIZED"`, `message: "Token tidak valid atau telah kadaluarsa"`. | Sesi habis atau belum login; antarmuka mengarahkan pengguna ke form login F-1. |
| **`403 Forbidden`** | Skema `ErrorResponse`: `code: "FORBIDDEN"`, `message: "Akses ditolak: Anda tidak memiliki kewenangan sebagai Petugas Keuangan"`. | `examples/error_403_forbidden.json`; UI menampilkan peringatan hak akses ditolak sesuai batasan peran. |
| **`404 Not Found`** | Skema `ErrorResponse`: `code: "TERMIN_NOT_FOUND"`, `message: "Termin tidak ditemukan"`. | `terminId` tidak terdaftar dalam basis data D-2. |
| **`409 Conflict`** | Skema `ErrorResponse`: `code: "TERMIN_ALREADY_INVOICED"`, `message: "Termin ini sudah memiliki invoice atau belum berstatus siap ditagih"`. | Mencegah penagihan berganda; UI memperbarui tampilan data terbaru. |
| **`422 Unprocessable Entity`** | Skema `ErrorResponse`: `code: "PO_NOT_FOUND_OR_BLOCKED"`, pesan: *"Penagihan diblokir: PO belum tersedia"* atau `code: "TEMPORARY_CLIENT_BLOCKED"`, pesan: *"Penagihan diblokir: Klien proyek masih berstatus sementara"*. | `examples/invoice_create_error_po_blocked.json`; Tombol Simpan dinonaktifkan dan muncul banner peringatan bahwa PO resmi dari D-1 diperlukan sebelum penagihan dapat dilanjutkan. |
| **`503 Service Unavailable`**| Skema `ErrorResponse`: `code: "DEPENDENCY_UNAVAILABLE"`, pesan: *"Layanan D-1 tidak dapat dihubungi"*. | UI menampilkan pesan bahwa layanan proyek D-1 sedang gangguan; data form isian tetap tersimpan di layar tanpa hilang. |

* **Aturan paging/filter/urut (jika perlu)**: Tidak berlaku untuk operasi pembuatan resource (`POST`).
* **Idempotensi atau pengulangan request (jika relevan)**: Relasi antara `Termin` dan `Invoice` diatur dengan relasi unik `1:1`. Basis data menerapkan *unique constraint* pada kolom `terminId` di tabel `invoices`. Apabila terjadi pengiriman ulang request (misalnya klik ganda atau latensi jaringan), sistem akan menolak request kedua dengan respons `409 Conflict`, sehingga dipastikan tidak akan pernah terbentuk invoice ganda untuk termin yang sama.

---

### Operasi Pendukung: API-K02-02 (Daftar Termin Siap Ditagih)

* **ID operasi, fitur, dan sumber kebutuhan**: `API-K02-02` (`getTermins`) / `FTR-K02-03`, `FTR-K02-04` / Peta Aktor D-2 Langkah 1 & 4, UI-K02-01.
* **Metode, path, dan tujuan**: `GET /api/v1/termins` — Mengambil daftar termin dengan filter status dan ID proyek, diperkaya dengan status ketersediaan PO dari D-1.
* **Masukan**:
  - `projectId` (query / string / opsional): ID filter proyek kanonis D-1 (contoh: `"prj-demo-001"`).
  - `status` (query / enum / opsional): `BELUM_SIAP`, `SIAP_DITAGIH`, `SUDAH_DITAGIHKAN` (default: `SIAP_DITAGIH`).
  - `page` (query / integer / opsional, default: 1).
  - `pageSize` (query / integer / opsional, default: 20, max: 100).
* **Respons**: `200 OK` dengan skema `TerminListResponse` (`items[]`, `page`, `pageSize`, `totalItems`).
* **Aturan paging/filter/urut**: Pagination berbasis `page` dan `pageSize`. Default filter menampilkan termin berstatus `SIAP_DITAGIH`. Urutan data secara bawaan adalah urut nomor termin dalam kontrak (`terminNumber ASC`).

---

## 5. Contoh Respons dan Batas Data

* **Respons berhasil**:
  - Berkas: `examples/invoice_create_success.json`
  - Status HTTP: `201 Created`
  - Skema: `CreateInvoiceResponse`
* **Respons gagal**:
  - Berkas: `examples/invoice_create_error_po_blocked.json`
  - Status HTTP: `422 Unprocessable Entity`
  - Kode error: `PO_NOT_FOUND_OR_BLOCKED`
* **Alasan memilih kasus gagal**:
  Kasus ketiadaan Purchase Order (PO) dipilih karena merupakan **aturan bisnis penagihan terpenting** yang mengikat modul D-1 dan D-2. Sesuai keputusan mitra D-1 dan perancangan alur ERP, termin penagihan yang telah jatuh tempo tidak boleh diterbitkan invoicenya apabila pihak klien belum menyerahkan dokumen PO resmi yang tercatat di sistem D-1. Pengujian kasus gagal ini membuktikan bahwa validasi koordinasi antar-layanan berjalan efektif dalam mencegah penerbitan tagihan ilegal/prematur yang rawan sengketa.
* **Field yang sengaja tidak dikirim**:
  1. *Nomor Rekening Bank Perusahaan & Kredensial Finansial*: Tidak diekspos melalui endpoint transaksi invoice untuk menjaga kerahasiaan dan keamanan perbankan internal.
  2. *Data Pribadi / NPWP Individual Karyawan*: Tidak dikirimkan dalam payload publik karena hak kelola privasi data personil berada di modul F-2 (SDM).
  3. *Detail Profil Lengkap Klien*: D-2 hanya mereferensikan proyek D-1 dan tidak menduplikasi rincian alamat/kontak klien yang merupakan wewenang D-1 / F-6.

---

## 6. Review Produsen dan Konsumen

| Reviewer / Kelompok | Temuan pada Kontrak | Keputusan dan Pemilik Tindak Lanjut |
| :--- | :--- | :--- |
| **Kelompok D-1**<br>*(Manajemen Proyek)* | • Perlunya kejelasan mekanisme pengecekan status PO (`poReceived`) dan penanganan proyek yang masih memakai klien sementara (`isTemporaryClient: true`, `clientId: null`). Apakah D-2 perlu query berkali-kali ke D-1? | **Disepakati**: Kelompok D-1 menyediakan endpoint ringkas `GET /api/v1/projects/{projectId}` yang sekaligus mengembalikan boolean `poReceived`, `isTemporaryClient`, dan `clientId`. D-2 memblokir pembuatan invoice dengan HTTP 422 bila `poReceived == false` atau `isTemporaryClient == true`.<br>**Pemilik**: Rudol Prasetyo Sinurat (D-2) & Tim D-1 (Tenggat: Pertemuan 4). |
| **Kelompok F-1**<br>*(Akun & Autentikasi)* | • Format isi klaim Bearer Token JWT untuk otorisasi peran Petugas Keuangan dan keterkaitan akun login dengan `employeeId` pencatat transaksi. | **Disepakati**: F-1 akan menyertakan klaim `employeeId` dan array `roles: ["petugas_keuangan", ...]` secara langsung dalam payload JWT, sehingga modul D-2 dapat memeriksa kewenangan secara stateless dan cepat tanpa membebani server F-1.<br>**Pemilik**: Roy Martin Silaban (D-2) & Tim F-1 (Tenggat: Pertemuan 4). |

---

## 7. Pertanyaan Terbuka

| ID | Keputusan yang Belum Tersedia | Pemilik Keputusan dan Batas Waktu |
| :--- | :--- | :--- |
| **OPEN-01** | **Parameter & Rumus Pajak Final (Q-D2-03)**: Kepastian tarif PPN apakah tetap 11% atau dinamis sesuai PMK terbaru, serta perlakuan PPh 23 (apakah memotong total invoice atau dipungut terpisah sebagai bukti potong). Asumsi sementara: PPN 11%, PPh 2%, `total = DPP + PPN - PPh`. | Tim D-2 & Dosen / Mitra Keuangan<br>Batas waktu: 2026-10-21 (Pertemuan 4) |
| **OPEN-02** | **Format Penomoran Invoice Resmi (Q-D2-11)**: Pola penomoran resmi dokumen invoice perusahaan (misalnya `INV/{YYYY}/{MM}/{ROMAN}/{XXXX}`) dan apakah nomor di-reset per tahun kalender. Asumsi sementara: `INV-YYYY-XXXX`. | Tim D-2 & Mitra Keuangan<br>Batas waktu: 2026-10-21 (Pertemuan 4) |
| **OPEN-03** | **Kriteria Kondisi Termin "Siap Ditagih" (Q-D2-04)**: Apakah murni otomatis berdasarkan tanggal jatuh tempo tercapai (`currentDate >= dueDate`), atau memerlukan input Berita Acara Serah Terima (BAST) / Milestone dari PM di modul D-1. Asumsi sementara: jatuh tempo tercapai dan PO tersedia. | Tim D-2 & Kelompok D-1<br>Batas waktu: 2026-10-21 (Pertemuan 4) |
| **OPEN-04** | **Aturan Pencatatan Penerimaan Bank (Q-D2-08)**: Apakah pembayaran invoice boleh dicicil bertahap (pembayaran parsial 1:N), dan bagaimana perlakuan status bila terjadi lebih bayar atau kurang bayar. Asumsi sementara: mendukung cicilan, status menjadi `TERBAYAR` saat akumulasi penerimaan = total tagihan. | Tim D-2<br>Batas waktu: 2026-10-28 (Pertemuan 5) |

---

## 8. Checklist Sebelum Pengumpulan

- [x] **OpenAPI dapat dibuka dan valid sebagai YAML/JSON**: Berkas `openapi.yaml` dan `openapi.json` telah disusun memenuhi standar OpenAPI 3.0.3 lengkap dengan blok `info`, `servers`, `paths`, `components/schemas`, dan `components/securitySchemes`.
- [x] **Setiap operasi terpilih memiliki metadata lengkap**: Setiap endpoint memiliki `operationId` unik (`getTermins`, `createInvoice`, `getInvoiceDetail`, dll.), metode HTTP yang tepat, spesifikasi parameter/body, serta respons berhasil (`200`/`201`) dan ragam kode kegagalan (`400`, `401`, `403`, `404`, `409`, `422`, `503`).
- [x] **Konsistensi data**: Format tanggal (`YYYY-MM-DD`), waktu UTC (`ISO 8601`), status enum (`UPPERCASE`), nominal rupiah desimal (`decimal(18,2)` string), dan skema autentikasi Bearer JWT konsisten pada seluruh skema dan contoh.
- [x] **Perlindungan data sensitif**: Payload respons untuk konsumen tidak membocorkan data sensitif di luar kebutuhan (nomor rekening bank dan data pribadi SDM tidak diekspos).
- [x] **Review konsumen tercatat**: Catatan hasil review bersama Kelompok D-1 (Proyek) dan Kelompok F-1 (Autentikasi) telah terdokumentasi dengan keputusan dan pemilik tindak lanjut yang jelas.
- [x] **Repositori dan mock berjalan**: Repositori memuat `README.md`, folder `examples/` berisi data fiktif lengkap, serta implementasi endpoint mock dasar yang aktif pada framework Laravel (`routes/api.php`).

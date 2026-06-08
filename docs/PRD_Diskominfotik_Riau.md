# Product Requirements Document (PRD)
## Sistem Informasi Manajemen Aset Berbasis Web
### SIMA — Diskominfotik Provinsi Riau

---

| Field | Detail |
|---|---|
| **Nama Penulis** | Fandy Alfarizi Syafril |
| **NIM** | 2255301053 |
| **Pembimbing** | Puja Hanifah, S.S.T., M.MSI. |
| **Program Studi** | Teknik Informatika |
| **Institusi** | Politeknik Caltex Riau |
| **Studi Kasus** | Dinas Komunikasi, Informatika dan Statistik Provinsi Riau |
| **Metode Pengembangan** | Rapid Application Development (RAD) |
| **Versi Dokumen** | 1.0 — Juni 2026 |
| **Status** | ✅ Aktif — Dalam Pengembangan |

---

## Daftar Isi

1. [Ringkasan Eksekutif](#1-ringkasan-eksekutif)
2. [Stakeholder dan Pengguna](#2-stakeholder-dan-pengguna)
3. [Kebutuhan Fungsional](#3-kebutuhan-fungsional)
4. [Kebutuhan Non-Fungsional](#4-kebutuhan-non-fungsional)
5. [Arsitektur dan Teknologi](#5-arsitektur-dan-teknologi)
6. [Use Case Utama](#6-use-case-utama)
7. [Daftar Halaman Antarmuka (UI)](#7-daftar-halaman-antarmuka-ui)
8. [Rencana Pengujian Sistem](#8-rencana-pengujian-sistem)
9. [Jadwal Pengembangan](#9-jadwal-pengembangan)
10. [Risiko dan Mitigasi](#10-risiko-dan-mitigasi)
11. [Kriteria Penerimaan](#11-kriteria-penerimaan)

---

## 1. Ringkasan Eksekutif

### 1.1 Latar Belakang Produk

Dinas Komunikasi, Informatika dan Statistik (Diskominfotik) Provinsi Riau mengelola berbagai aset infrastruktur teknologi, perangkat telekomunikasi, dan peralatan teknis yang kompleks. Namun, seluruh proses pencatatan aset masih dilakukan secara manual menggunakan spreadsheet (Microsoft Excel) yang terpisah di setiap bidang, tanpa integrasi antar unit kerja.

Kondisi ini menimbulkan sejumlah masalah operasional kritis:

- **Redundansi data** akibat pencatatan yang tidak terpusat di setiap bidang.
- **Kesulitan sinkronisasi informasi** antar unit karena pengelola aset harus mendatangi setiap bidang secara manual setiap bulan.
- **Siklus hidup aset tidak terdokumentasi** — inventaris, pemeliharaan, penyusutan, penghapusan, dan pelaporan tidak berjalan sistematis.
- **Risiko ketidaksesuaian** antara data administrasi dengan kondisi fisik aset di lapangan, yang berdampak pada temuan audit.
- **Jejak mutasi aset hilang** — perpindahan aset antar bidang tidak tercatat dengan valid.

### 1.2 Solusi yang Diusulkan

Sistem Informasi Manajemen Aset (SIMA) berbasis web yang terintegrasi, dibangun menggunakan metode **Rapid Application Development (RAD)** dengan stack teknologi:

- **Back-end:** Laravel 11 (PHP)
- **Front-end:** Bootstrap / Tailwind CSS
- **Database:** MySQL
- **Fitur kunci:** Generator QR Code sebagai identitas digital setiap aset fisik

### 1.3 Tujuan Produk

- Membangun sistem terpusat untuk dokumentasi dan pengelolaan data aset di Diskominfotik Provinsi Riau.
- Mengimplementasikan **Generator QR Code** sebagai identitas digital aset fisik untuk mempermudah tracking dan pemantauan.
- Meningkatkan efisiensi operasional, meminimalisir kehilangan data, dan mendukung transparansi audit pemerintah.
- Menyediakan **visualisasi data dan laporan aset** yang akurat dan real-time bagi pimpinan.

### 1.4 Ruang Lingkup

Sistem diperuntukkan bagi pegawai di lingkungan Diskominfotik Provinsi Riau dan mencakup jenis aset berikut:

- Aset tetap (Register dan SMKI) di Diskominfotik Provinsi Riau
- Aset kendaraan dinas
- Aset infrastruktur dan peralatan teknis
- Sarana dan prasarana kantor

**Proses yang dicakup:** inventarisasi aset, pemeliharaan aset, penyusutan aset, penghapusan aset, manajemen peminjaman, mutasi aset antar bidang, dan pelaporan aset.

---

## 2. Stakeholder dan Pengguna

### 2.1 Identifikasi Aktor Sistem

| No | Aktor | Role | Kewenangan Utama |
|---|---|---|---|
| 1 | **Super Admin** (Pengelola Aset) | Administrator Penuh | Mengelola seluruh data aset, memverifikasi input dari admin perbidang, membuat label QR Code, mengelola akun pengguna, melihat dashboard keseluruhan. |
| 2 | **Admin Perbidang** (Pegawai) | Operator per Bidang | Menginput data aset baru pada bidang masing-masing, memperbarui kondisi fisik aset (baik/rusak ringan/rusak berat), mengajukan mutasi dan peminjaman aset. |
| 3 | **Pimpinan / Kepala Dinas** | Viewer Eksekutif | Memantau seluruh informasi aset melalui visualisasi dashboard, melihat rekapitulasi nilai dan kondisi aset, mengunduh laporan. |

### 2.2 Kebutuhan Pengguna

| Pengguna | Kebutuhan |
|---|---|
| **Super Admin** | Mengelola akun pengguna (Admin perbidang), mengelola data aset seluruh Diskominfotik, menerima dan memverifikasi laporan dari setiap bidang. |
| **Admin Perbidang** | Melihat semua informasi aset di Diskominfotik, membuat laporan kondisi aset masing-masing bidang, mengajukan peminjaman dan mutasi aset. |
| **Pimpinan / Kepala Dinas** | Melihat semua informasi aset yang dikelola pengelola aset (Super Admin) melalui dashboard visual yang informatif. |

---

## 3. Kebutuhan Fungsional

### 3.1 Matriks Prioritas Fitur (Eisenhower Matrix)

Seluruh fitur diprioritaskan menggunakan Eisenhower Matrix dan dikembangkan dalam 3 iterasi RAD:

| Kuadran | Status | Fitur | Iterasi RAD |
|---|---|---|---|
| **I** | 🔴 Penting & Mendesak | Login & autentikasi, manajemen pengguna & role, inventarisasi aset, verifikasi & validasi data aset, generate QR Code, pembaruan kondisi aset | Iterasi 1 |
| **II** | 🟡 Penting & Tidak Mendesak | Mutasi aset antar bidang, riwayat pemeliharaan, penyusutan aset, penghapusan aset, pelaporan aset, manajemen peminjaman, dashboard visualisasi pimpinan | Iterasi 2 |
| **III** | 🟢 Tidak Penting & Mendesak | Pencarian & filter data aset, ekspor laporan PDF/Excel, notifikasi konfirmasi penerimaan aset | Iterasi 3 |
| **IV** | ⚪ Tidak Penting & Tidak Mendesak | Kustomisasi antarmuka, integrasi sistem eksternal, log aktivitas (audit trail), dukungan multi-bahasa | Di luar scope |

---

### 3.2 Fitur Detail per Modul

#### Modul 1 — Autentikasi & Manajemen Pengguna

| ID | Nama Fitur | Deskripsi | Aktor | Prioritas |
|---|---|---|---|---|
| F-01 | Login Sistem | Pengguna masuk menggunakan NIP dan password. Sistem memvalidasi kredensial dan mengarahkan ke dashboard sesuai role. | Semua Aktor | 🔴 Tinggi |
| F-02 | Kelola Akun Pengguna | Super Admin dapat mendaftarkan, mengedit, dan menghapus akun Admin perbidang beserta informasi role, email, dan bidang. | Super Admin | 🔴 Tinggi |

#### Modul 2 — Inventarisasi Aset

| ID | Nama Fitur | Deskripsi | Aktor | Prioritas |
|---|---|---|---|---|
| F-03 | Input Data Aset (Register) | Admin perbidang menginput data aset fisik/register baru ke sistem. Data disimpan dengan status "Perlu Verifikasi" hingga disetujui Super Admin. | Admin Perbidang | 🔴 Tinggi |
| F-04 | Input Data Aset (SMKI) | Admin perbidang menginput data aset SMKI (Software, Media, Knowledge, Information). Data disimpan dengan status "Perlu Verifikasi". | Admin Perbidang | 🔴 Tinggi |
| F-05 | Kelola Kategori Aset | Super Admin menambah, mengedit, dan menghapus kategori aset (Register/SMKI). Sistem memvalidasi agar tidak ada duplikasi kategori. | Super Admin | 🔴 Tinggi |
| F-06 | Verifikasi & Validasi Aset | Super Admin meninjau dan memverifikasi data aset yang diajukan Admin perbidang. Setelah diverifikasi, sistem mengaktifkan fitur cetak QR Code. | Super Admin | 🔴 Tinggi |

#### Modul 3 — QR Code

| ID | Nama Fitur | Deskripsi | Aktor | Prioritas |
|---|---|---|---|---|
| F-07 | Generate QR Code | Sistem membuat QR Code unik berbasis ID Aset setelah aset terverifikasi. QR Code berfungsi sebagai identitas digital aset fisik. | Super Admin | 🔴 Tinggi |
| F-08 | Cetak / Download Label QR | Super Admin mencetak atau mengunduh label QR Code untuk ditempelkan pada aset fisik. Scan QR Code menampilkan detail aset. | Super Admin | 🔴 Tinggi |

#### Modul 4 — Pemeliharaan Aset

| ID | Nama Fitur | Deskripsi | Aktor | Prioritas |
|---|---|---|---|---|
| F-09 | Update Kondisi Aset | Admin perbidang memperbarui kondisi fisik aset (Baik / Rusak Ringan / Rusak Berat) disertai foto fisik aset. Sistem menyimpan riwayat perubahan kondisi. | Admin Perbidang | 🔴 Tinggi |

#### Modul 5 — Mutasi Aset

| ID | Nama Fitur | Deskripsi | Aktor | Prioritas |
|---|---|---|---|---|
| F-10 | Pengajuan Mutasi Aset | Admin perbidang mengajukan mutasi (perpindahan lokasi) aset ke bidang lain melalui form. Pengajuan dikirim ke Super Admin untuk verifikasi. | Admin Perbidang | 🟡 Sedang |
| F-11 | Verifikasi Mutasi Aset | Super Admin memverifikasi pengajuan mutasi. Setelah disetujui, status dan lokasi aset diperbarui secara otomatis. | Super Admin | 🟡 Sedang |
| F-12 | Riwayat Mutasi Aset | Sistem menampilkan histori seluruh perpindahan aset beserta detail tanggal, bidang asal, dan bidang tujuan. | Semua Aktor | 🟡 Sedang |

#### Modul 6 — Peminjaman Aset

| ID | Nama Fitur | Deskripsi | Aktor | Prioritas |
|---|---|---|---|---|
| F-13 | Pengajuan Peminjaman | Admin perbidang mengajukan peminjaman aset melalui form. Pengajuan dikirim ke Super Admin untuk verifikasi ketersediaan aset. | Admin Perbidang | 🟡 Sedang |
| F-14 | Verifikasi Peminjaman | Super Admin memverifikasi pengajuan peminjaman. Jika disetujui, status aset berubah menjadi "Dipinjam". | Super Admin | 🟡 Sedang |
| F-15 | Pengembalian Aset | Admin perbidang mencatat pengembalian aset. Status aset diperbarui menjadi "Tersedia" dan riwayat pengembalian dicatat. | Admin Perbidang | 🟡 Sedang |

#### Modul 7 — Penyusutan & Penghapusan Aset

| ID | Nama Fitur | Deskripsi | Aktor | Prioritas |
|---|---|---|---|---|
| F-16 | Kalkulasi Penyusutan Aset | Sistem menghitung penurunan nilai ekonomis aset secara berkala sesuai metode yang berlaku untuk keperluan laporan kekayaan daerah dan audit. | Super Admin | 🟡 Sedang |
| F-17 | Penghapusan Aset | Super Admin menonaktifkan dan menghapus aset dari inventaris aktif ketika aset tidak layak pakai, didukung data pemeliharaan dan nilai penyusutan yang valid. | Super Admin | 🟡 Sedang |

#### Modul 8 — Dashboard & Pelaporan

| ID | Nama Fitur | Deskripsi | Aktor | Prioritas |
|---|---|---|---|---|
| F-18 | Dashboard Super Admin | Menampilkan visualisasi data aset keseluruhan (grafik, statistik) dengan filter berdasarkan bidang, kategori, dan kondisi aset secara real-time. | Super Admin | 🔴 Tinggi |
| F-19 | Dashboard Admin Perbidang | Menampilkan ringkasan data aset per bidang masing-masing dengan filter kategori dan kondisi aset. | Admin Perbidang | 🔴 Tinggi |
| F-20 | Dashboard Pimpinan | Menampilkan visualisasi data aset keseluruhan Diskominfotik termasuk nilai aset, penyusutan, kondisi, dan statistik per bidang untuk mendukung keputusan strategis. | Pimpinan | 🟡 Sedang |
| F-21 | Laporan Aset | Sistem menghasilkan rekapitulasi data aset periodik dengan filter periode, bidang, dan kategori. Laporan dapat diekspor dalam format PDF/Excel. | Semua Aktor | 🟡 Sedang |
| F-22 | Pencarian & Filter Data | Pengguna dapat mencari aset berdasarkan nama, kategori, kondisi, bidang, atau status. Hasil ditampilkan secara real-time. | Semua Aktor | 🟢 Rendah |

---

## 4. Kebutuhan Non-Fungsional

| Kategori | Kebutuhan | Detail |
|---|---|---|
| **Kegunaan (Usability)** | Kemudahan penggunaan | Sistem dapat digunakan tanpa pelatihan khusus. Target skor SUS (System Usability Scale) ≥ 70 (Good). |
| **Performa** | Responsivitas sistem | Halaman dashboard dan daftar aset tampil dalam waktu < 3 detik pada koneksi standar kantor. |
| **Kompatibilitas** | Multi-platform & browser | Sistem dapat diakses melalui browser modern (Chrome, Firefox, Edge) di perangkat komputer maupun smartphone. |
| **Keamanan** | Autentikasi & otorisasi | Setiap pengguna hanya dapat mengakses fitur sesuai role-nya. Password dienkripsi. Sesi otomatis berakhir setelah tidak aktif. |
| **Skalabilitas** | Kapasitas data | Sistem mampu menangani seluruh data aset dari semua bidang Diskominfotik tanpa degradasi performa signifikan. |
| **Ketersediaan** | Uptime | Sistem tersedia selama jam kerja operasional kantor Diskominfotik Provinsi Riau. |
| **Pemeliharaan** | Kemudahan maintenance | Kode dikembangkan dengan framework Laravel 11 yang modular sehingga mudah dipelihara dan dikembangkan lebih lanjut. |
| **Standar Audit** | Kepatuhan regulasi | Laporan penyusutan dan pengelolaan aset sesuai dengan standar akuntansi pemerintah daerah. |

---

## 5. Arsitektur dan Teknologi

### 5.1 Stack Teknologi

| Lapisan | Teknologi | Keterangan |
|---|---|---|
| **Back-End** | Laravel 11 (PHP) | Framework PHP dengan arsitektur MVC, menggunakan Blade template engine, routing, dan migration untuk manajemen database. |
| **Front-End** | Bootstrap / Tailwind CSS | Menghasilkan antarmuka responsif yang dapat diakses dari komputer maupun smartphone. |
| **Database** | MySQL | Sistem manajemen basis data relasional (RDBMS) open source untuk menyimpan seluruh data aset, pengguna, dan transaksi. |
| **QR Code** | Library QR Generator | Menghasilkan kode QR unik berbasis ID aset yang dapat dipindai untuk melihat detail aset fisik. |

### 5.2 Alur Arsitektur Sistem (MVC)

```
Client (Browser / Smartphone)
        │
        ▼  HTTP Request
 ┌─────────────────┐
 │   Controller    │  ← Menerima request, memproses logika bisnis
 └────────┬────────┘
          │
          ▼
 ┌─────────────────┐
 │     Model       │  ← Berkomunikasi dengan database MySQL (CRUD)
 └────────┬────────┘
          │
          ▼
 ┌─────────────────┐
 │  MySQL Database │  ← Menyimpan data aset, pengguna, transaksi
 └─────────────────┘
          │
          ▼
 ┌─────────────────────────────┐
 │  View (Blade + Bootstrap /  │  ← Merender tampilan responsif
 │        Tailwind CSS)        │
 └─────────────────────────────┘
          │
          ▼  HTTP Response
Client (Browser / Smartphone)

   [Modul QR Code: Generate server-side, unduh/cetak via browser]
```

### 5.3 Entity Relationship Diagram — Entitas Utama

| Entitas | Atribut Kunci | Relasi |
|---|---|---|
| **Users** | id, name, NIP, email, password, role, bidang_id | Berelasi dengan Bidang (many-to-one); melakukan transaksi aset. |
| **Aset** | id, nama_aset, kode_aset, kategori_id, bidang_id, kondisi, status, nilai, tahun_perolehan, qr_code | Berelasi dengan Kategori dan Bidang; memiliki riwayat kondisi, mutasi, dan peminjaman. |
| **Kategori Aset** | id, nama_kategori, tipe (Register/SMKI) | Berelasi dengan Aset (one-to-many). |
| **Bidang** | id, nama_bidang | Berelasi dengan Users dan Aset. |
| **Riwayat Kondisi** | id, aset_id, kondisi_baru, foto, user_id, tanggal | Berelasi dengan Aset dan Users. |
| **Mutasi Aset** | id, aset_id, bidang_asal_id, bidang_tujuan_id, status_verifikasi, user_id, tanggal | Berelasi dengan Aset, Bidang, dan Users. |
| **Peminjaman Aset** | id, aset_id, peminjam_id, tanggal_pinjam, tanggal_kembali, status | Berelasi dengan Aset dan Users. |

---

## 6. Use Case Utama

| Use Case | Aktor | Pre-Condition | Post-Condition | Alur Utama |
|---|---|---|---|---|
| **Login** | Semua Aktor | Akun sudah terdaftar | Aktor diarahkan ke dashboard sesuai role | Input NIP & password → validasi sistem → redirect ke dashboard role. |
| **Input Data Aset Baru** | Admin Perbidang | Login sebagai Admin Perbidang | Data aset tersimpan dengan status "Perlu Verifikasi", diteruskan ke Super Admin | Pilih kategori aset → isi form → simpan → notifikasi ke Super Admin. |
| **Verifikasi Aset** | Super Admin | Ada aset dengan status "Perlu Verifikasi" | Status aset berubah "Terverifikasi"; fitur cetak QR Code aktif | Buka menu verifikasi → tinjau detail → klik Verifikasi → sistem update status & aktifkan QR Code. |
| **Generate & Cetak QR Code** | Super Admin | Aset sudah terverifikasi | Label QR Code siap dicetak dan ditempel di aset fisik | Buka menu QR → input ID Aset → sistem generate QR Code → cetak/unduh label. |
| **Update Kondisi Aset** | Admin Perbidang | Aset terdaftar di sistem | Riwayat kondisi aset terbarui (Baik/Rusak Ringan/Rusak Berat) | Cari aset → pilih kondisi baru → unggah foto → klik Update → sistem simpan riwayat. |
| **Pengajuan Mutasi Aset** | Admin Perbidang | Aset terdaftar di bidang pengguna | Pengajuan mutasi dikirim ke Super Admin untuk verifikasi | Isi form mutasi (aset, bidang tujuan, tanggal) → simpan → notifikasi ke Super Admin. |
| **Lihat Dashboard & Laporan** | Semua Aktor | Login ke sistem | Visualisasi data aset tampil real-time sesuai role | Buka Dashboard → filter sesuai kebutuhan → lihat grafik/tabel → ekspor laporan (opsional). |

### 6.1 Skenario Use Case Detail

#### UC-01: Login

| | |
|---|---|
| **Nama** | Login |
| **Aktor** | Super Admin, Admin Perbidang, Pimpinan |
| **Pre-Condition** | Akun sudah terdaftar di sistem |
| **Post-Condition** | Aktor berhasil login dan diarahkan ke dashboard sesuai hak akses |

| Aksi Aktor | Respon Sistem |
|---|---|
| 1. Klik menu Login pada sistem | Sistem menampilkan halaman Login |
| 2. Menginputkan NIP dan Password | Sistem memvalidasi data; jika tidak valid menampilkan pesan "Lengkapi data dengan benar" |
| 3. Klik tombol Login | Sistem mengarahkan aktor ke halaman sesuai role |

#### UC-02: Input Data Aset Baru

| | |
|---|---|
| **Nama** | Menginput Data Aset (Register/SMKI) |
| **Aktor** | Admin Perbidang |
| **Pre-Condition** | Aktor berada di halaman admin sesuai bidang |
| **Post-Condition** | Data aset baru berhasil disimpan dengan status "Perlu Verifikasi" |

| Aksi Aktor | Respon Sistem |
|---|---|
| 1. Memilih menu Data Aset dan pilih kategori | Sistem menampilkan daftar aset yang sudah ada |
| 2. Klik tombol tambah aset | Sistem menampilkan form penginputan aset |
| 3. Mengisi form data aset | Sistem mengecek kelengkapan data yang diinput |
| 4. Klik tombol Simpan | Sistem menyimpan data aset dengan status "Perlu Verifikasi" dan meneruskan ke Super Admin |

#### UC-03: Verifikasi & Generate QR Code

| | |
|---|---|
| **Nama** | Verifikasi Aset & Membuat QR Code |
| **Aktor** | Super Admin |
| **Pre-Condition** | Ada data aset dengan status "Perlu Verifikasi" |
| **Post-Condition** | Status aset berubah "Terverifikasi" dan label QR Code siap dicetak |

| Aksi Aktor | Respon Sistem |
|---|---|
| 1. Memilih menu Verifikasi Aset | Sistem menampilkan daftar aset yang menunggu verifikasi |
| 2. Meninjau detail data aset | Sistem menampilkan detail lengkap aset |
| 3. Mengklik tombol Verifikasi | Sistem mengubah status aset dan mengaktifkan fitur cetak QR Code |
| 4. Memilih menu Cetak Label QR Code | Sistem menampilkan halaman generate QR berbasis ID Aset |
| 5. Klik Print/Download QR Code | Sistem mengunduh atau mencetak label QR Code aset |

---

## 7. Daftar Halaman Antarmuka (UI)

| No | Nama Halaman | Aktor | Fungsi Utama |
|---|---|---|---|
| 1 | Halaman Login | Semua Aktor | Autentikasi pengguna dengan NIP dan password |
| 2 | Dashboard Super Admin | Super Admin | Visualisasi data aset keseluruhan semua bidang dengan filter dan grafik |
| 3 | Manajemen Pengguna | Super Admin | Registrasi, edit, dan hapus akun Admin Perbidang |
| 4 | Kategori Aset | Super Admin | Kelola kategori aset (Register dan SMKI) di seluruh bidang |
| 5 | Verifikasi Aset — Input Baru | Super Admin | Meninjau dan memverifikasi aset baru yang diajukan Admin Perbidang |
| 6 | Verifikasi Aset — Peminjaman | Super Admin | Meninjau dan menyetujui/menolak pengajuan peminjaman aset |
| 7 | Verifikasi Aset — Mutasi | Super Admin | Meninjau dan menyetujui/menolak pengajuan mutasi aset |
| 8 | Registrasi QR Code Aset | Super Admin | Generate dan cetak/unduh label QR Code untuk aset fisik |
| 9 | Dashboard Admin Perbidang | Admin Perbidang | Ringkasan data aset per bidang dengan filter kategori dan kondisi |
| 10 | Data Aset SMKI | Admin Perbidang | Pantau dan tambah data aset SMKI di bidang masing-masing |
| 11 | Form Tambah Aset SMKI | Admin Perbidang | Form pengisian data aset SMKI baru untuk diverifikasi Super Admin |
| 12 | Data Aset Register | Admin Perbidang | Pantau dan tambah data aset register (fisik) di bidang masing-masing |
| 13 | Form Tambah Aset Register | Admin Perbidang | Form pengisian data aset register baru untuk diverifikasi Super Admin |
| 14 | Kondisi Aset | Admin Perbidang | Pantau dan update kondisi fisik aset di bidang masing-masing |
| 15 | Form Update Kondisi Aset | Admin Perbidang | Popup form update status kondisi aset + foto fisik |
| 16 | Mutasi Aset | Admin Perbidang | Lihat riwayat dan ajukan mutasi/perpindahan aset ke bidang lain |
| 17 | Form Pengajuan Mutasi | Admin Perbidang | Form pengisian data pengajuan mutasi aset |
| 18 | Peminjaman Aset | Admin Perbidang | Lihat daftar peminjaman aktif dan ajukan peminjaman baru |
| 19 | Form Pengajuan Peminjaman | Admin Perbidang | Form pengisian data pengajuan peminjaman aset |
| 20 | Dashboard Pimpinan | Pimpinan | Visualisasi komprehensif nilai aset, penyusutan, dan kondisi seluruh bidang |
| 21 | Laporan Aset | Semua Aktor | Rekapitulasi dan ekspor laporan aset berdasarkan filter periode, bidang, kategori |

---

## 8. Rencana Pengujian Sistem

### 8.1 Metode Pengujian

| Metode | Tujuan | Waktu Pelaksanaan | Target |
|---|---|---|---|
| **Black Box Testing** | Memvalidasi fungsionalitas setiap fitur sistem — kesesuaian input dan output dari sisi pengguna tanpa melihat kode internal. | 30 Juni – 10 Juli 2026 | Seluruh fitur pada 3 role: Super Admin, Admin Perbidang, Pimpinan |
| **Usability Testing (SUS)** | Mengevaluasi kepuasan dan kemudahan penggunaan sistem oleh pegawai Diskominfotik menggunakan kuesioner System Usability Scale (SUS). | 30 Juni – 10 Juli 2026 | Skor SUS ≥ 70 (kategori Good) |

### 8.2 Ruang Lingkup Black Box Testing

#### Role: Super Admin

| Kelas Uji | Skenario | Hasil yang Diharapkan |
|---|---|---|
| Halaman Login | Input NIP & password valid | Diarahkan ke Dashboard Super Admin |
| Halaman Login | Input NIP/password tidak valid | Pesan "NIP atau password salah" |
| Kelola Akun Pengguna | Tambah akun baru dengan data lengkap | Akun tersimpan dan pesan "Akun berhasil dibuat" |
| Kelola Akun Pengguna | Hapus akun pengguna | Konfirmasi hapus → akun terhapus dari sistem |
| Dashboard Utama | Filter berdasarkan bidang/kategori/kondisi | Dashboard diperbarui sesuai filter yang dipilih |
| Kategori Aset | Tambah/edit/hapus kategori | Operasi berhasil dengan validasi duplikasi |
| Verifikasi Aset | Klik Verifikasi pada aset yang diajukan | Status berubah "Terverifikasi"; fitur QR Code aktif |
| Generator QR Code | Input ID Aset → klik Print/Download | Label QR Code berhasil dicetak/diunduh |
| Laporan | Klik Export Laporan | File laporan berhasil diunduh |

#### Role: Admin Perbidang

| Kelas Uji | Skenario | Hasil yang Diharapkan |
|---|---|---|
| Halaman Login | Input NIP & password valid | Diarahkan ke Dashboard Admin Perbidang |
| Input Aset Baru | Isi form dengan data lengkap dan simpan | Data disimpan status "Perlu Verifikasi", diteruskan ke Super Admin |
| Input Aset Baru | Isi form tidak lengkap dan simpan | Pesan "Lengkapi data dengan benar", data tidak tersimpan |
| Update Kondisi Aset | Pilih kondisi baru + unggah foto → Update | Riwayat kondisi tersimpan, pesan berhasil |
| Mutasi Aset | Isi form mutasi dan simpan | Pengajuan mutasi dikirim ke Super Admin |
| Peminjaman Aset | Isi form peminjaman dan simpan | Status aset berubah "Dipinjam" |
| Pengembalian Aset | Catat pengembalian | Status aset berubah "Tersedia", riwayat pengembalian tercatat |

#### Role: Pimpinan / Kepala Dinas

| Kelas Uji | Skenario | Hasil yang Diharapkan |
|---|---|---|
| Halaman Login | Input NIP & password valid | Diarahkan ke Dashboard Pimpinan |
| Dashboard Pimpinan | Buka menu Dashboard | Visualisasi data aset keseluruhan (grafik, statistik, kondisi) |
| Dashboard Pimpinan | Filter berdasarkan bidang/kategori/kondisi | Dashboard diperbarui sesuai filter |
| Monitoring Aset | Lihat detail aset tertentu | Riwayat kondisi dan mutasi aset tampil lengkap |
| Laporan | Klik Export Laporan | File laporan berhasil diunduh |

### 8.3 Kuesioner Usability Testing (SUS)

| No | Dimensi | Pertanyaan |
|---|---|---|
| 1–3 | **Learnability** (Kemudahan) | Sistem berjalan sesuai harapan; mudah mendapatkan informasi aset; mudah digunakan tanpa pelatihan khusus. |
| 4–6 | **Efficiency** (Efisiensi) | Membantu penginputan aset baru; membantu pembuatan QR Code; membantu pengelolaan data aset. |
| 7–8 | **Memorability** (Mudah Diingat) | Navigasi dan struktur web mudah diingat setelah digunakan; sistem mudah dipelajari dalam waktu singkat. |
| 9–11 | **Errors** (Kesalahan) | Frekuensi menu tidak merespons; kesalahan saat memasukkan data; frekuensi fitur error. |
| 12–16 | **Satisfaction** (Kepuasan) | Kenyamanan menggunakan sistem; tampilan antarmuka; kepuasan fitur; informasi yang diperoleh; kualitas pengalaman pengguna. |

> **Skala penilaian:** SS (5) | S (4) | N (3) | TS (2) | STS (1)  
> **Formula SUS:** (Σ skor item ganjil − 5) + (25 − Σ skor item genap) × 2,5  
> **Target:** Skor rata-rata ≥ 70 (kategori *Good*)

---

## 9. Jadwal Pengembangan

| No | Fitur / Aktivitas | Rencana Mulai | Rencana Selesai | Durasi | Iterasi RAD |
|---|---|---|---|---|---|
| 1 | Pengelolaan akun pengguna (Admin perbidang) | 19/05/2026 | 21/05/2026 | 3 Hari | Iterasi 1 |
| 2 | Fitur manajemen user | 21/05/2026 | 23/05/2026 | 3 Hari | Iterasi 1 |
| 3 | Fitur login | 23/05/2026 | 26/05/2026 | 3 Hari | Iterasi 1 |
| 4 | Dashboard admin (Super Admin) | 26/05/2026 | 29/05/2026 | 4 Hari | Iterasi 1 |
| 5 | Kelola data aset (Register & SMKI) | 29/05/2026 | 31/05/2026 | 3 Hari | Iterasi 1 |
| 6 | Input, edit, dan hapus data aset | 31/05/2026 | 02/06/2026 | 3 Hari | Iterasi 1 |
| 7 | Kelola kategori aset | 02/06/2026 | 03/06/2026 | 2 Hari | Iterasi 1 |
| 8 | Tambah, edit, hapus kategori aset | 03/06/2026 | 05/06/2026 | 3 Hari | Iterasi 1 |
| 9 | Verifikasi dan validasi aset | 05/06/2026 | 07/06/2026 | 3 Hari | Iterasi 1 |
| 10 | Verifikasi data aset oleh Super Admin | 07/06/2026 | 09/06/2026 | 3 Hari | Iterasi 1 |
| 11 | Dashboard Admin Perbidang | 09/06/2026 | 11/06/2026 | 3 Hari | Iterasi 2 |
| 12 | Input aset baru oleh Admin perbidang | 11/06/2026 | 13/06/2026 | 3 Hari | Iterasi 2 |
| 13 | Update kondisi aset | 13/06/2026 | 15/06/2026 | 3 Hari | Iterasi 2 |
| 14 | Dashboard Pimpinan/Kepala Dinas | 15/06/2026 | 18/06/2026 | 4 Hari | Iterasi 2 |
| 15 | Generator QR Code aset | 18/06/2026 | 20/06/2026 | 3 Hari | Iterasi 2 |
| 16 | Cetak/download label QR Code | 20/06/2026 | 23/06/2026 | 4 Hari | Iterasi 2 |
| 17 | Mutasi/perpindahan lokasi aset | 23/06/2026 | 25/06/2026 | 3 Hari | Iterasi 2 |
| 18 | Riwayat mutasi aset | 25/06/2026 | 27/06/2026 | 3 Hari | Iterasi 2 |
| 19 | Fitur peminjaman aset | 27/06/2026 | 29/06/2026 | 3 Hari | Iterasi 3 |
| 20 | Laporan dan visualisasi dashboard | 29/06/2026 | 02/07/2026 | 4 Hari | Iterasi 3 |
| — | **Pengujian Sistem** | 30/06/2026 | 10/07/2026 | ~10 Hari | — |

> **Total Durasi Pengembangan: 63 Hari Kerja (19 Mei — 2 Juli 2026)**

---

## 10. Risiko dan Mitigasi

| Risiko | Probabilitas | Dampak | Strategi Mitigasi |
|---|---|---|---|
| Perubahan kebutuhan pengguna di tengah pengembangan | 🟡 Sedang | 🔴 Tinggi | Metode RAD dengan iterasi memungkinkan penyesuaian kebutuhan secara berkala dan fleksibel. |
| Ketidaklengkapan data aset existing dari Diskominfotik | 🔴 Tinggi | 🟡 Sedang | Koordinasi intensif dengan pegawai untuk migrasi data awal sebelum go-live. |
| Resistensi pengguna terhadap sistem baru | 🟡 Sedang | 🟡 Sedang | Melibatkan pengguna aktif selama proses RAD sehingga sistem sesuai kebiasaan kerja mereka. |
| Validasi QR Code di lapangan tidak berjalan optimal | 🟢 Rendah | 🔴 Tinggi | Uji coba scanning di berbagai kondisi cahaya dan jarak sebelum implementasi massal. |
| Skor Usability Testing tidak mencapai target | 🟢 Rendah | 🟡 Sedang | Evaluasi dan perbaikan UI iteratif berdasarkan feedback pengguna selama fase RAD. |
| Keterlambatan jadwal pengembangan | 🟡 Sedang | 🟡 Sedang | Eisenhower Matrix memastikan fitur prioritas tinggi selesai lebih dulu; fitur tambahan dapat ditunda. |

---

## 11. Kriteria Penerimaan

Sistem dinyatakan **berhasil dan diterima** apabila memenuhi seluruh kriteria berikut:

| No | Kriteria | Indikator Keberhasilan |
|---|---|---|
| 1 | ✅ Fungsionalitas Lengkap | Seluruh 22 fitur (F-01 s.d. F-22) berfungsi sesuai deskripsi dan use case scenario. |
| 2 | ✅ Black Box Testing Lulus | Seluruh skenario uji pada ketiga role menghasilkan output sesuai yang diharapkan. |
| 3 | ✅ Skor SUS Memadai | Hasil Usability Testing menghasilkan skor SUS rata-rata ≥ 70 (kategori Good). |
| 4 | ✅ QR Code Berfungsi | QR Code dapat dipindai menggunakan kamera smartphone dan menampilkan detail aset dengan akurat. |
| 5 | ✅ Integrasi Antar Bidang | Data aset yang diinput Admin Perbidang dapat terlihat dan dikelola Super Admin secara real-time. |
| 6 | ✅ Laporan Dapat Diekspor | Laporan aset dapat diekspor dalam format PDF atau Excel dengan data yang akurat dan terstruktur. |
| 7 | ✅ Responsif di Mobile | Tampilan sistem dapat diakses dan digunakan dengan baik di perangkat smartphone. |
| 8 | ✅ Standar Audit Terpenuhi | Modul penyusutan menghasilkan laporan nilai aset yang sesuai dengan standar akuntansi pemerintah daerah. |

---

*PRD v1.0 — Fandy Alfarizi Syafril — Politeknik Caltex Riau — 2026*

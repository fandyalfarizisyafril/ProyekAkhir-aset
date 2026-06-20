# Task List Implementasi PRD SIMA Diskominfotik Riau

Terakhir diperbarui: 2026-06-20

Dokumen acuan: `docs/PRD_Diskominfotik_Riau.md`

## Legenda Status

- `[x]` Selesai: route/controller/view/alur utama sudah tersedia dan sesuai PRD.
- `[ ]` Belum selesai: belum ada implementasi utama.
- `Parsial`: fondasi sudah ada, tetapi belum memenuhi alur PRD secara lengkap.

## Ringkasan Audit Kode Saat Ini

- Stack project sudah sesuai arah PRD: Laravel 11, Blade, Tailwind CSS, database relational melalui migration.
- Role utama sudah ada: `Super Admin`, `Admin Perbidang`, `Kepala Dinas`, dan fallback `User`.
- Model/migration aset Register, aset SMKI, bidang, mutasi, peminjaman, penyusutan, riwayat kondisi, dan laporan sudah tersedia.
- Modul yang sudah berjalan paling utuh: login/role, manajemen pengguna, input aset Register/SMKI oleh Admin Perbidang, verifikasi aset oleh Super Admin, QR Code/label aset, update kondisi aset dengan riwayat dan foto, pengajuan mutasi aset oleh Admin Perbidang, verifikasi mutasi aset oleh Super Admin, riwayat mutasi lintas aktor, pengajuan peminjaman aset oleh Admin Perbidang, verifikasi peminjaman oleh Super Admin, pengembalian aset oleh Admin Perbidang, penyusutan, penghapusan aset, dashboard Super Admin, dashboard Admin Perbidang, dan dashboard Pimpinan/Kepala Dinas.
- Modul yang masih berupa menu/model/fondasi: laporan ekspor.

## Iterasi 1 - Penting dan Mendesak

- [x] F-01 Login Sistem
  - Status: Selesai.
  - Bukti implementasi: `LoginRequest`, `AuthenticatedSessionController`, `auth.login`, redirect dashboard berdasarkan role.
  - Update 2026-06-08: login disesuaikan dari email menjadi NIP + password sesuai PRD.

- [x] F-02 Kelola Akun Pengguna
  - Status: Selesai.
  - Bukti implementasi: route resource `super-admin/pengguna`, `KelolaPenggunaController`, form create/edit/index, validasi `StoreUserRequest` dan `UpdateUserRequest`.

- [x] F-03 Input Data Aset Register
  - Status: Selesai untuk alur input Admin Perbidang.
  - Bukti implementasi: `DataAsetRegisterController`, request validasi, view index/create/edit, filter dan pagination.
  - Update 2026-06-08: aset baru otomatis masuk `status_verifikasi = Perlu Verifikasi`.
  - Catatan: verifikasi oleh Super Admin dilacak di F-06.

- [x] F-04 Input Data Aset SMKI
  - Status: Selesai untuk alur input Admin Perbidang.
  - Bukti implementasi: `DataAsetSMKIController`, request validasi, view index/create/edit, filter dan pagination.
  - Update 2026-06-08: aset baru otomatis masuk `status_verifikasi = Perlu Verifikasi`; Admin Perbidang tidak lagi mengisi status verifikasi manual.
  - Catatan: verifikasi oleh Super Admin dilacak di F-06.

- [x] F-05 Kelola Kategori Aset
  - Status: Selesai.
  - Bukti implementasi: tabel/model `kategori_aset`, route resource `super-admin/kategori-aset`, `KategoriAsetController`, view daftar/tambah/edit kategori, menu sidebar `KATEGORI ASET`, validasi duplikasi kategori per tipe, sinkronisasi kategori dari input aset, dan proteksi hapus/ubah nama kategori yang sudah digunakan aset.
  - Update 2026-06-12: Super Admin dapat menambah, mengedit, menghapus, mencari, dan memfilter kategori aset tipe `Register` atau `SMKI`.
  - Update 2026-06-15: Form input/edit aset Register dan SMKI menggunakan input teks dengan saran kategori; kategori baru dari `kode_barang` Register atau `jenis_barang` SMKI dapat diketik Admin Perbidang tanpa harus dibuat manual lebih dulu.
  - Update 2026-06-15: Halaman kategori aset milik Super Admin ikut mengambil kategori dari data aset Register/SMKI terverifikasi yang sudah ada, sehingga data input admin perbidang dapat dikelola setelah lolos verifikasi.
  - Update 2026-06-15: Kategori yang berasal dari input Admin Perbidang hanya disinkronkan dari aset berstatus `Terverifikasi`; deskripsi kategori diambil dari `keterangan` aset dan tabel kategori menampilkan kolom bidang asal.
  - Update 2026-06-15: Filter daftar kategori ditambah dropdown `Semua Bidang` agar Super Admin dapat memfilter kategori berdasarkan bidang asal aset.

- [x] F-06 Verifikasi dan Validasi Aset
  - Status: Selesai.
  - Bukti implementasi: route `super-admin/verifikasi-aset`, `VerifikasiAsetController`, view daftar/detail verifikasi, aksi approve/reject, filter jenis aset/bidang/status, pencarian aset.
  - Update 2026-06-08: Super Admin dapat meninjau aset Register dan SMKI berstatus `Perlu Verifikasi`, lalu mengubah status menjadi `Terverifikasi` atau `Ditolak`.
  - Update 2026-06-08: aset SMKI ditambahkan kolom `diverifikasi_oleh` agar pencatatan verifier konsisten dengan aset Register.

- [x] F-07 Generate QR Code
  - Status: Selesai.
  - Bukti implementasi: dependency `bacon/bacon-qr-code`, route `super-admin/qr-code`, `QrCodeController`, penyimpanan QR SVG ke disk `public`, dan kolom `qr_code_path` pada aset Register/SMKI.
  - Update 2026-06-08: Super Admin dapat generate QR Code untuk aset Register dan SMKI yang sudah `Terverifikasi`.
  - Update 2026-06-08: scan QR membuka route `qr/aset/{type}/{id}` untuk menampilkan detail aset.

- [x] F-08 Cetak / Download Label QR
  - Status: Selesai.
  - Bukti implementasi: halaman label `super-admin/qr-code/{type}/{id}/label`, tombol print browser, download SVG QR, dan menu sidebar `REGISTRASI QR` sudah mengarah ke route aktif.
  - Update 2026-06-08: `php artisan storage:link` sudah dijalankan agar file QR di `storage/app/public` dapat ditampilkan melalui `/storage`.

- [x] F-09 Update Kondisi Aset
  - Status: Selesai.
  - Bukti implementasi: `KondisiAsetController`, `StoreKondisiAsetRequest`, `UpdateKondisiAsetRequest`, tabel `riwayat_kondisi_register`, tabel `riwayat_kondisi_smki`, upload foto ke disk `public`.

- [x] F-18 Dashboard Super Admin
  - Status: Selesai.
  - Bukti implementasi: `SuperAdmin\DashboardController` menghitung ringkasan aset Register/SMKI lintas bidang dari database, view dashboard menampilkan kartu ringkasan, filter bidang/kategori/kondisi, sebaran aset per bidang, kondisi fisik, tipe aset, status verifikasi, status peminjaman, dan nilai aset Register.
  - Update 2026-06-15: Data dashboard tidak lagi statis; filter bidang, kategori, dan kondisi memproses query database serta dilindungi test feature.

- [x] F-19 Dashboard Admin Perbidang
  - Status: Selesai.
  - Bukti implementasi: `AdminPerbidang\DashboardController` sudah menghitung ringkasan aset Register/SMKI berdasarkan `bidang_id` user login, view dashboard menampilkan kartu ringkasan, filter kategori/kondisi, sebaran kategori, kondisi fisik, tipe aset, status verifikasi, status peminjaman, dan nilai aset Register.
  - Update 2026-06-11: Filter kategori menggabungkan `kode_barang` aset Register dan `jenis_barang` aset SMKI; filter kondisi menggabungkan `kondisi` Register dan `keadaan_barang` SMKI.
  - Update 2026-06-11: Data dashboard dipastikan hanya menampilkan aset dari bidang Admin Perbidang terkait dan sudah dilindungi oleh test feature.

## Iterasi 2 - Penting dan Tidak Mendesak

- [x] F-10 Pengajuan Mutasi Aset
  - Status: Selesai.
  - Bukti implementasi: route resource `admin-perbidang/mutasi-aset`, `MutasiAsetController`, `StoreMutasiAsetRequest`, view daftar/form/detail pengajuan, dan menu sidebar `MUTASI ASET` sudah aktif.
  - Update 2026-06-08: Admin Perbidang dapat memilih aset Register/SMKI terverifikasi dari bidangnya, memilih bidang tujuan, mengisi tanggal dan alasan mutasi, lalu mengirim pengajuan dengan status `Menunggu Verifikasi`.
  - Catatan: perpindahan bidang/lokasi aset otomatis setelah disetujui sudah dilengkapi pada F-11.

- [x] F-11 Verifikasi Mutasi Aset
  - Status: Selesai.
  - Bukti implementasi: route `super-admin/verifikasi-mutasi`, `VerifikasiMutasiAsetController`, view daftar/detail verifikasi, menu sidebar `VERIFIKASI MUTASI`, aksi approve/reject, filter jenis/status/bidang, dan pencarian aset/pemohon.
  - Update 2026-06-10: Super Admin dapat menyetujui pengajuan mutasi berstatus `Menunggu Verifikasi`; sistem otomatis memperbarui status mutasi menjadi `Disetujui`, mencatat `disetujui_oleh`, memindahkan `bidang_id` aset ke bidang tujuan, dan memperbarui lokasi aset (`lokasi_aset` Register atau `ruangan` SMKI) mengikuti ruangan bidang tujuan.
  - Update 2026-06-10: Super Admin dapat menolak pengajuan mutasi; status mutasi menjadi `Ditolak`, verifier tercatat, dan aset tetap berada di bidang/lokasi asal.

- [x] F-12 Riwayat Mutasi Aset
  - Status: Selesai.
  - Bukti implementasi: route shared `riwayat-mutasi-aset`, `RiwayatMutasiAsetController`, view daftar/detail riwayat, menu sidebar `RIWAYAT MUTASI` untuk semua role, filter jenis/status/bidang/tanggal, dan pencarian aset/bidang/pemohon.
  - Update 2026-06-10: Sistem menampilkan histori perpindahan aset beserta detail tanggal mutasi, bidang asal, bidang tujuan, pemohon, verifier, alasan, dan status verifikasi.
  - Update 2026-06-10: Scope akses diterapkan per aktor: Super Admin dan Kepala Dinas melihat semua riwayat, Admin Perbidang melihat mutasi yang melibatkan bidangnya, dan User umum melihat riwayat yang sudah `Disetujui`.

- [x] F-13 Pengajuan Peminjaman
  - Status: Selesai.
  - Bukti implementasi: route resource `admin-perbidang/peminjaman-aset`, `PeminjamanAsetController`, `StorePeminjamanAsetRequest`, view daftar/form/detail pengajuan, dan menu sidebar `PEMINJAMAN ASET` sudah aktif.
  - Update 2026-06-11: Admin Perbidang dapat memilih aset Register/SMKI terverifikasi yang belum memiliki pengajuan/peminjaman aktif, mengisi tanggal pinjam, rencana kembali, keperluan, dan catatan, lalu mengirim pengajuan dengan status `Menunggu Verifikasi`.
  - Update 2026-06-11: Form peminjaman ditambah dropdown `Lokasi Asal Aset (Bidang)` untuk memfilter aset berdasarkan bidang asal dan input teks `Nama Peminjam`; data bidang asal tersimpan pada `bidang_asal_id` dan nama peminjam tersimpan pada `nama_peminjam`.
  - Catatan: perubahan status aset menjadi `Dipinjam` setelah disetujui dilacak pada F-14.

- [x] F-14 Verifikasi Peminjaman
  - Status: Selesai.
  - Bukti implementasi: route `super-admin/verifikasi-peminjaman`, `VerifikasiPeminjamanAsetController`, view daftar/detail verifikasi, menu sidebar `VERIFIKASI PEMINJAMAN`, aksi approve/reject, filter jenis/status/bidang, dan pencarian aset/peminjam.
  - Update 2026-06-11: Super Admin dapat menyetujui pengajuan peminjaman berstatus `Menunggu Verifikasi`; sistem otomatis mengubah status pengajuan menjadi `Disetujui`, mencatat `disetujui_oleh`, dan mengubah status aset menjadi `Dipinjam`.
  - Update 2026-06-11: Super Admin dapat menolak pengajuan peminjaman; status pengajuan menjadi `Ditolak`, verifier tercatat, dan status aset tidak berubah.
  - Update 2026-06-11: Aset SMKI ditambahkan kolom `status` agar status `Dipinjam` dapat dicatat konsisten seperti aset Register.

- [x] F-15 Pengembalian Aset
  - Status: Selesai.
  - Bukti implementasi: route `admin-perbidang/peminjaman-aset/{peminjaman_aset}/return`, aksi `returnAsset` pada `PeminjamanAsetController`, form pengembalian di detail peminjaman, filter/status `Dikembalikan`, dan kartu ringkasan pengembalian.
  - Update 2026-06-11: Admin Perbidang dapat mencatat pengembalian untuk peminjaman berstatus `Disetujui`; sistem mengisi `tanggal_kembali`, mengubah status peminjaman menjadi `Dikembalikan`, menambahkan catatan riwayat pengembalian, dan mengubah status aset Register/SMKI menjadi `Tersedia`.
  - Update 2026-06-11: Aksi pengembalian hanya bisa dilakukan oleh peminjam terkait dan tidak bisa dijalankan pada pengajuan yang belum disetujui atau sudah ditutup.

- [x] F-16 Kalkulasi Penyusutan Aset
  - Status: Selesai.
  - Bukti implementasi: route `super-admin/penyusutan-aset`, `PenyusutanAsetController`, view daftar penyusutan, filter tahun/bidang/pencarian aset, hitung penyusutan per aset, hitung massal, dan penyimpanan snapshot penyusutan per aset Register per tahun.
  - Update 2026-06-16: Sistem menghitung penyusutan aset Register terverifikasi menggunakan metode garis lurus berdasarkan nilai perolehan, umur manfaat, nilai residu, tahun perolehan, beban penyusutan, dan nilai buku akhir tahun.
  - Update 2026-06-16: Tabel `penyusutan_aset` ditambah `umur_manfaat_tahun`, `nilai_residu`, dan unique key aset+tahun agar perhitungan ulang memperbarui snapshot yang sama, bukan membuat data dobel.

- [x] F-17 Penghapusan Aset
  - Status: Selesai.
  - Bukti implementasi: route `super-admin/penghapusan-aset`, `PenghapusanAsetController`, tabel `penghapusan_aset`, model `PenghapusanAset`, menu Super Admin, halaman daftar aset aktif, filter jenis/bidang/pencarian, form penghapusan per aset, dan tabel riwayat penghapusan terbaru.
  - Update 2026-06-16: Super Admin dapat menonaktifkan aset Register/SMKI terverifikasi dari inventaris aktif dengan mengubah status aset menjadi `Dihapus`, menyimpan alasan, tanggal, metode, user penghapus, bidang, kode/nama aset, dan status sebelumnya sebagai audit trail.
  - Update 2026-06-16: Nilai buku aset Register diambil dari penyusutan terakhir jika tersedia, atau nilai perolehan jika belum ada penyusutan; aset SMKI tetap dapat dihapus tanpa nilai buku. Aset yang masih memiliki peminjaman aktif atau menunggu verifikasi tidak dapat dihapus.
  - Update 2026-06-16: Aksi hapus permanen pada Data Aset Register dan Data Aset SMKI milik Admin Perbidang dinonaktifkan; penghapusan aset hanya dapat dilakukan oleh Super Admin melalui modul Penghapusan Aset.

- [x] F-20 Dashboard Pimpinan
  - Status: Selesai.
  - Bukti implementasi: route `kepala-dinas/dashboard`, `KepalaDinas\DashboardController`, view dashboard pimpinan real-time, filter tahun/bidang/kategori/kondisi, ringkasan total aset aktif terverifikasi, nilai aset Register, beban penyusutan tahun terpilih, nilai buku, aset rusak/perbaikan, aset dihapus, sebaran aset per bidang, kondisi fisik, tipe aset, dan daftar aset Register bernilai tertinggi.
  - Update 2026-06-20: Dashboard Kepala Dinas tidak lagi memakai data statis; seluruh angka utama dihitung dari database dengan scope aset `Terverifikasi` dan belum `Dihapus`, serta dilindungi test feature.

- [ ] F-21 Laporan Aset
  - Status: Belum selesai.
  - Catatan: model dan migration `laporan` sudah ada, beberapa tombol ekspor tampil di UI, tetapi belum ada controller ekspor PDF/Excel dan filter laporan.

## Iterasi 3 - Pendukung

- [ ] F-22 Pencarian dan Filter Data
  - Status: Parsial.
  - Catatan: pencarian/filter sudah tersedia pada manajemen pengguna, aset Register, aset SMKI, dan kondisi aset untuk Admin Perbidang. Belum tersedia lintas semua aktor dan belum real-time.

- [ ] Ekspor Laporan PDF/Excel
  - Status: Belum selesai.
  - Catatan: belum ada dependency/export service untuk PDF/Excel.

- [ ] Notifikasi Konfirmasi Penerimaan Aset
  - Status: Belum selesai.
  - Catatan: belum ada notification/event/mail/database notification.

## Kebutuhan Non-Fungsional

- [x] Autentikasi dan otorisasi role dasar
  - Status: Selesai.
  - Bukti implementasi: middleware `role`, route group per role, password hashed.

- [ ] Sesi otomatis berakhir setelah tidak aktif
  - Status: Parsial.
  - Catatan: Laravel session tersedia, tetapi belum ada konfigurasi eksplisit sesuai kebijakan kantor.

- [x] Responsif mobile
  - Status: Selesai tahap global.
  - Update 2026-06-11: Layout utama dibuat responsif dengan sidebar drawer di mobile, tombol menu pada header, konten utama diberi batas lebar aman, footer dibuat wrap, kartu statistik dipadatkan di layar kecil, dan seluruh wrapper tabel utama memakai utilitas `responsive-table` agar scroll horizontal tetap berada di dalam card.
  - Verifikasi: `npm run build` berhasil dan `php artisan test` tetap lulus.

- [ ] Performa dashboard < 3 detik
  - Status: Belum diverifikasi.

- [ ] Standar audit penyusutan pemerintah daerah
  - Status: Belum selesai.

## Prioritas Berikutnya

1. Lengkapi F-21 laporan aset dan ekspor PDF/Excel setelah alur utama aset, mutasi, peminjaman, penyusutan, penghapusan, dan dashboard role stabil.
2. Perluas pencarian/filter lintas semua aktor setelah dashboard dan laporan utama selesai.
3. Verifikasi performa dashboard < 3 detik dengan data aset yang lebih besar.
4. Evaluasi standar audit penyusutan pemerintah daerah sebelum laporan akhir dipakai.

# Peminatan per Siswa

## Intent

Memisahkan rombel fisik dari peminatan akademik tanpa menggandakan siswa. Rombel tetap ditentukan oleh `kelas_siswa`, sedangkan `master_siswa` menyimpan peminatan dan program MAK. Bank soal dan seluruh endpoint ujian wajib menegakkan ketiga dimensi peserta: rombel, peminatan, dan program MAK.

## Goals

- Satu siswa memiliki paling banyak satu rombel aktif untuk setiap tahun pelajaran dan semester.
- Bank umum dapat dipakai lintas rombel tanpa memandang peminatan.
- Bank IPA, IPS, MAK, Tahfidz, dan Kitab hanya dapat diakses siswa yang cocok.
- Siswa lama tetap dapat mengikuti bank umum; siswa tanpa peminatan ditolak dari bank berpeminatan.
- Master siswa dapat dibuat tanpa rombel; seluruh penempatan aktif dikelola terpisah dan transaksional.
- Data lama, akun, nilai, dan jadwal tidak dihapus atau diubah di luar backfill yang disebutkan.

## Non-goals

- Tidak menghapus `master_kelas.jurusan_id`; kolom itu dipertahankan untuk kompatibilitas kode lama, tetapi tidak menjadi sumber otorisasi ujian siswa.
- Tidak membuat tabel peminatan atau program baru.
- Tidak mengubah username/password, nilai, jawaban, atau peserta ujian lama.
- Tidak mendesain ulang seluruh modul siswa/CBT atau menghilangkan obfuscation pada file lama.

## Chosen approach

Pendekatan terpilih adalah kompatibilitas ketat:

- `bank_jurusan_id` 0/NULL berarti tanpa filter peminatan.
- Bank berpeminatan hanya menerima siswa dengan `peminatan_id` non-NULL yang sama.
- `bank_program_mak` NULL berarti semua program MAK; nilai `TAHFIDZ`/`KITAB` harus sama dengan `program_mak` siswa.
- `peminatan_id` siswa NULL hanya dapat mengakses bank umum.

Alternatif yang ditolak adalah membolehkan siswa NULL melewati bank berpeminatan. Alternatif itu mempermudah kompatibilitas awal, tetapi membuka akses IPA dan IPS sekaligus bagi siswa XI yang belum diklasifikasikan.

## Data design and migration

Buat migration CodeIgniter berikutnya setelah `20261111000400` dan naikkan `migration_version`.

1. Preflight mendeteksi duplikasi `kelas_siswa` untuk `(id_tp,id_smt,id_siswa)`. Migration berhenti dengan pesan yang dapat ditindaklanjuti sebelum DDL jika duplikasi ada; migration tidak menghapus data untuk memaksakan unique key.
2. Pastikan master peminatan aktif dengan kode unik secara logis: `UM`, `IPA`, `IPS`, dan `MAK`. Record ID 6 `NON JURUSAN` dinormalisasi menjadi nama `UMUM`, kode `UM`, dengan ID yang sama agar referensi lama tetap valid. MAK ditambahkan hanya jika kode MAK belum ada.
3. Tambahkan secara idempotent:
   - `master_siswa.peminatan_id INT NULL`
   - `master_siswa.program_mak VARCHAR(20) NULL`
   - `cbt_bank_soal.bank_program_mak VARCHAR(20) NULL`
4. Jadikan `kelas_siswa.id_kelas_siswa` auto-increment bila struktur aktif belum demikian, lalu tambahkan unique key `(id_tp,id_smt,id_siswa)` setelah preflight lulus.
5. Backfill hanya `peminatan_id IS NULL`, berdasarkan nama rombel yang dinormalisasi secara konservatif:
   - kelas level/nama X -> UMUM;
   - XII-A -> IPA;
   - XII-B -> IPS;
   - XII-C dan XII-D -> MAK;
   - XI tidak diisi otomatis.
6. Backfill `program_mak IS NULL` secara eksplisit: XII-C -> `TAHFIDZ`, XII-D -> `KITAB`. XI tidak ditebak.
7. `down()` hanya menghapus kolom/index yang dibuat migration dan memuat komentar bahwa backfill nilai peminatan tidak dapat dipulihkan otomatis. Master UM/MAK yang mungkin dipakai data tidak dihapus.

Nilai program divalidasi aplikasi sebagai `NULL`, `TAHFIDZ`, atau `KITAB`. Migration tidak mengandalkan `CHECK` karena dukungan/penegakannya berbeda antarversi MySQL/MariaDB.

Migration utama tetap berupa migration PHP CI3 sesuai runner repositori, tetapi DDL/DML di dalamnya idempotent dan berbasis SQL/DB Forge. Companion SQL hanya ditambahkan bila deployment repo memang menjalankan migration manual; jangan membuat jalur schema kedua yang dapat menyimpang.

## Student write and placement model

Sumber kebenaran rombel aktif adalah `kelas_siswa`; `kelas_awal` tetap berarti tingkat saat diterima dan tidak diubah ketika siswa naik/pindah kelas.

- Tambah siswa: validasi identitas, peminatan, dan program; dalam satu transaksi hanya insert `master_siswa` dan `buku_induk`. Siswa baru berstatus Tanpa Kelas.
- Edit siswa: update profil/peminatan dalam transaksi. Jika peminatan bukan MAK, backend selalu menulis `program_mak = NULL`.
- Menu `Rombel / Penempatan Siswa` menjadi satu-satunya jalur menempatkan, memindahkan, atau mengeluarkan siswa. Pindah kelas meng-update baris periode aktif tanpa mengubah `kelas_awal`; unique key mencegah duplikat.
- Hapus siswa: pertahankan aturan aman yang sudah ada dan bungkus seluruh tabel terkait yang memang sudah disentuh alur lama dalam transaksi; jangan memperluas cascade.
- Impor Excel: kenali kolom peminatan/program serta rombel opsional, gunakan transaksi per batch, dan laporkan jumlah Tanpa Kelas.
- Tambah/edit rombel hanya menulis `master_kelas` dan boleh kosong. Penghapusan rombel ditolak selama `kelas_siswa` masih merujuk ke rombel tersebut.
- Semua kegagalan transaksi mengembalikan JSON konsisten dengan pesan yang ditampilkan UI.

## Authorization design

Tambahkan satu helper/model method terpusat yang menguji kelayakan siswa terhadap bank/jadwal:

1. Ambil rombel siswa dari `kelas_siswa` untuk tahun dan semester bank/jadwal.
2. Parse `bank_kelas` memakai format serialisasi yang sudah digunakan aplikasi dan cocokkan `id_kelas`.
3. Terapkan kondisi:
   - kelas cocok;
   - bank umum, atau `master_siswa.peminatan_id = bank_jurusan_id` dengan peminatan siswa non-NULL;
   - `bank_program_mak IS NULL`, atau sama dengan `master_siswa.program_mak`.
4. Daftar jadwal siswa menggunakan filter yang sama.
5. Panggil pemeriksaan yang sama sebelum konfirmasi, validasi token/mulai, pembuatan soal siswa, pemuatan soal, penyimpanan jawaban, dan melanjutkan attempt. Akses langsung yang gagal mengembalikan 403/JSON error dan tidak membuat attempt/nilai/soal.

Parsing `bank_kelas` yang gagal atau format lama yang tidak dikenali harus menolak akses secara default. Guard juga dipanggil sebelum timer, log, selesai, mengulang, resume, dan hasil yang menerima identitas jadwal/bank. Menyembunyikan jadwal di daftar tidak menggantikan guard endpoint.

Validasi program bank juga menolak program jika peminatan bukan MAK. Backend menormalisasi string kosong menjadi NULL dan program ke uppercase.

## UI

### Siswa

- Tambah/edit: dropdown `Peminatan`; dropdown `Program MAK` tampil/aktif hanya untuk MAK.
- Rombel tidak ditampilkan pada form master siswa; status Tanpa Kelas berasal dari tidak adanya `kelas_siswa` pada periode aktif.
- JavaScript membantu tampilan, tetapi backend mengosongkan program non-MAK.
- Daftar siswa dan detail kelas menampilkan nama peminatan dan program bila ada.
- Filter peminatan ditambahkan terpisah dan dikombinasikan dengan filter kelas/status serta pencarian yang sudah ada.
- Detail kelas mengambil peminatan/program dari `master_siswa`, bukan `master_kelas.jurusan_id`.

### Bank soal

- Label jurusan menjadi `Peminatan Peserta`.
- Kelas fisik tetap multi-select.
- Tambahkan `Program MAK`: Semua program MAK (NULL), Tahfidz, Kitab.
- Field program hanya aktif untuk peminatan MAK; backend tetap memvalidasi semua kombinasi.

## Tests

Tambahkan test terfokus yang dapat berjalan terhadap database staging:

- Migration `up()` dipanggil dua kali langsung pada schema staging (runner CI3 hanya memanggil versi sekali) dan struktur/indeks tetap benar; down didokumentasikan dan diuji sebatas struktur aman.
- Tambah siswa menghasilkan satu master dan satu buku induk tanpa penempatan aktif.
- Edit non-MAK menghapus program; pindah kelas tidak menggandakan siswa; kegagalan menulis rollback seluruh transaksi.
- Impor tidak menggandakan penempatan dan tidak mengubah kredensial lama.
- Fixture A/B/C/D menjalankan enam skenario wajib: umum, IPA, IPS, MAK semua, MAK Tahfidz, serta penolakan akses URL langsung.
- Bank umum dengan XI-A dan XI-B menerima A/B/C/D.
- Siswa IPA ditolak dari IPS dan sebaliknya.

## Verification and rollout

1. PHP lint semua file yang berubah.
2. PHPUnit test terfokus lalu suite relevan.
3. Salin database staging dari schema/fixture lokal; jangan gunakan database produksi.
4. Jalankan migration staging dan verifikasi ulang struktur serta backfill.
5. Jalankan tambah/edit/pindah/impor pada fixture.
6. Uji empat akun A/B/C/D melalui daftar dan URL langsung.
7. Catat file, migration, akar bug, perbaikan, hasil test, dan pemeriksaan produksi yang masih diperlukan.

## Safety constraints

- Tidak menjalankan migration terhadap koneksi produksi.
- Tidak menghapus duplikasi lama secara otomatis; migration gagal aman agar operator dapat meninjau data.
- Semua query baru memakai Query Builder/binding.
- Perubahan pada file obfuscated dibuat sesempit mungkin dan diverifikasi lint serta test sumber/perilaku.


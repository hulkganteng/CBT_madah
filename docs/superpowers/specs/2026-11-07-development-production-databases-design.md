# Pemisahan Database Development dan Production

## Tujuan

Memisahkan data pengembangan dari data produksi agar pengujian dapat memakai data dummy tanpa berisiko mencemari data operasional. Database lama `garuda_cbt` tetap utuh sebagai sumber struktur, akun admin, dan profil sekolah.

## Hasil yang diinginkan

- `garuda_cbt_development` mempunyai struktur terbaru serta data dummy untuk guru, siswa, kelas, mata pelajaran, akun login, dan bank soal.
- `garuda_cbt_production` mempunyai struktur terbaru tetapi tidak mempunyai data operasional guru, siswa, kelas, soal, ujian, atau hasil ujian.
- Production hanya menyimpan akun admin, profil sekolah, serta data referensi bawaan yang diperlukan agar aplikasi dan login bekerja. Profil admin hanya disalin jika memang ada pada sumber.
- Aplikasi memilih database berdasarkan `CI_ENV`: `development` dan `testing` menuju development, sedangkan `production` menuju production.

## Bukan tujuan

- Menghapus atau mengganti database lama `garuda_cbt`.
- Menambahkan panel untuk berpindah database dari antarmuka web.
- Menyalin data guru, siswa, soal, atau hasil ujian lama ke production.
- Mengubah kredensial admin atau isi profil sekolah.

## Pendekatan

### Dipilih: struktur dari database aktif, isi berdasarkan daftar aman

Struktur seluruh tabel disalin dari `garuda_cbt` tanpa baris data. Data referensi bawaan disalin melalui daftar yang eksplisit. Akun admin dan profil sekolah disalin secara terpisah. Development kemudian diisi dengan seed `tools/seed-data-umum.php` yang sudah digunakan proyek.

Pendekatan ini dipilih karena struktur database aktif sudah berada pada migrasi terbaru, tidak membawa data operasional lama, dan memakai seed yang sudah ada.

### Tidak dipilih: menggandakan database lama lalu membersihkannya

Lebih cepat, tetapi berisiko menyisakan data nyata pada salah satu tabel yang terlewat saat pembersihan.

### Tidak dipilih: membangun dari `assets/app/db/master.sql`

Data awalnya bersih, tetapi skemanya perlu dinaikkan melalui seluruh migrasi dan lebih mudah berbeda dari struktur database aktif.

## Isi database

Data referensi yang boleh ada di kedua database adalah data bawaan aplikasi dari tabel `bulan`, `cbt_jenis`, `cbt_ruang`, `cbt_sesi`, `cbt_token`, `groups`, `hari`, `level_guru`, `level_kelas`, `master_ekstra`, `master_jurusan`, `master_kelompok_mapel`, `master_mapel`, `master_smt`, dan `master_tp`. Data ini dianggap konfigurasi sistem, bukan data operasional sekolah.

Untuk admin, hanya baris admin yang sedang aktif dari `users`, relasinya di `users_groups`, dan profilnya di `users_profile` jika tersedia yang disalin. Sumber saat ini tidak mempunyai `users_profile` untuk admin, sehingga setup tidak membuat profil baru secara diam-diam. Baris tunggal `setting` disalin sebagai profil sekolah.

Development menerima tambahan data dummy dari seed yang sudah ada: 6 guru, 30 siswa, 6 kelas, 10 mata pelajaran tambahan, 4 ekstrakurikuler, dan 2 bank soal latihan. Production tidak menerima seed tersebut.

## Perubahan kode dan data

- Ubah `application/config/database.php` agar nama database mengikuti `ENVIRONMENT`.
- Buat dua database baru dan salin struktur semua tabel beserta foreign key dari `garuda_cbt` memakai `mysqldump --no-data`.
- Salin hanya data referensi, admin, dan profil sekolah ke kedua database.
- Isi tabel `migrations` pada setiap target dengan tepat satu baris versi terbaru dari `application/config/migration.php`; jangan menyalin tiga baris duplikat milik sumber.
- Jalankan seed umum hanya terhadap development.
- Arahkan `tools/test-mysql-concurrency.php` ke konfigurasi development, bukan nama database lama yang ditulis langsung.
- Installer lama tidak dipakai untuk membuat ulang setup dua database ini karena templatenya hanya mendukung satu nama database.
- Jangan mengubah database lama atau perubahan fitur import soal yang sudah ada di working tree.

## Pengamanan

- Sebelum membuat database, pastikan nama target persis `garuda_cbt_development` dan `garuda_cbt_production`.
- Jika salah satu target sudah ada, hentikan proses; jangan menimpa tanpa pemeriksaan ulang.
- Gunakan transaksi untuk penyalinan data pada setiap target.
- Anggap pembuatan database dan tabel sebagai DDL yang tidak dapat di-rollback; kegagalan harus menghentikan setup dan target yang belum lengkap harus dilaporkan.
- Verifikasi production tidak mempunyai akun selain admin dan seluruh tabel operasional utama bernilai nol.

## Verifikasi

- Kedua database mempunyai jumlah tabel yang sama dengan `garuda_cbt`.
- Development mempunyai tepat data dummy hasil seed dan login dummy dapat dikenali.
- Production mempunyai satu akun admin, relasi grup admin, satu profil sekolah, tidak menyintesis profil admin yang tidak ada di sumber, serta nol guru, siswa, kelas, bank soal, soal, ujian, dan hasil ujian operasional.
- Memuat konfigurasi dengan `CI_ENV=development`, `testing`, dan `production` menghasilkan nama database yang benar.
- Web tanpa `CI_ENV` tetap memakai production sesuai perilaku aman `index.php`; penggunaan dummy melalui web lokal harus mengatur `CI_ENV=development` pada Apache/vhost.
- Setiap target mempunyai tepat satu versi migrasi terbaru.
- Database lama `garuda_cbt` mempunyai jumlah tabel dan baris kunci yang tidak berubah.

## Risiko

Data referensi wajib tetap terisi di production walaupun data operasional kosong. Tanpa data tersebut, menu, hak akses, tahun ajaran, semester, dan login dapat gagal bekerja. Kesalahan pengaturan `CI_ENV` juga dapat menyebabkan pengujian lokal menulis ke production, sehingga mode aktif harus ditampilkan dan diperiksa saat verifikasi.

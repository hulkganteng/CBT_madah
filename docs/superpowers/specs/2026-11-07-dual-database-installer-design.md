# Installer Dua Database

## Tujuan

Mempermudah pengembangan dengan membuat database development dan production secara otomatis dari satu nama dasar, tanpa membawa data dummy ke production atau menimpa database yang sudah ada.

## Perilaku installer

- Kolom "Nama Database" menjadi "Nama Dasar Database".
- Nilai `garuda_cbt` menghasilkan `garuda_cbt_development` dan `garuda_cbt_production`.
- Nama dasar hanya boleh berisi huruf, angka, dan garis bawah, serta tidak boleh sudah berakhiran `_development` atau `_production`.
- Installer berhenti jika salah satu database target sudah ada; tidak ada mode overwrite.
- Kedua target menerima skema terbaru dan data referensi sistem yang sama.
- Kedua database mula-mula dibuat tanpa pengguna. Development menerima data dummy hanya setelah onboarding admin dan profil sekolah berhasil; production tidak menerima data dummy.
- Installer menulis konfigurasi dua database dan membuat `.env` lokal dengan `CI_ENV=development`.

## Isi template

`assets/app/db/master.sql` menjadi sumber skema terbaru: 79 tabel, 10 foreign key, tepat satu versi migrasi `20261103000100`, dan data referensi sistem, tanpa akun admin dan profil sekolah. `assets/app/db/database.json` juga dibuat ulang dari skema yang sama agar jalur `/update` tidak mengembalikan kolom ke definisi lama. File tambahan `assets/app/db/development-seed.sql` berisi hanya data dummy development yang saat ini dibuat oleh `tools/seed-data-umum.php`: guru, siswa, kelas, mata pelajaran tambahan, ekstrakurikuler tambahan, akun dummy, dan bank soal latihan.

Kredensial dummy tetap:

- Guru `umum.guru1` sampai `umum.guru6`: `Guru#2026`.
- Siswa `umum.siswa01` sampai `umum.siswa30`: `Siswa#2026`.

Bank soal dummy tetap kosong; template tidak membuat soal atau jadwal ujian.

## Admin dan profil sekolah

Onboarding aplikasi selalu membuka koneksi grup development secara eksplisit; ia tidak bergantung pada `.env` atau override `CI_ENV` milik Apache. Setelah admin dan profil sekolah berhasil dibuat, finalisasi dalam request `createApp` yang sama menyalin hanya admin berdasarkan ID hasil `ion_auth->register()`, relasi `group_id=1`, profil admin jika tersedia, dan baris `setting` ke production. Setelah itu, seed dummy dimasukkan ke development sebelum respons sukses dikirim. Data dummy atau data operasional lain tidak ikut disalin ke production.

Finalisasi memakai satu transaksi MySQL lintas dua schema karena kedua database berada pada server yang sama. Jika sinkronisasi atau seed gagal, transaksi dibatalkan dan admin/setting development yang baru dibuat dibersihkan secara kompensasi, sehingga onboarding dapat diulang dan hook keamanan tidak mengunci instalasi setengah jadi. Sinkronisasi ini hanya berjalan selama onboarding pertama dan gagal jika production sudah memiliki data pengguna atau profil sekolah. Perubahan admin/profil setelah instalasi tidak disinkronkan otomatis.

## Bagian yang diubah

- Satu helper installer bersama dipakai oleh kedua jalur pembuatan database: standalone `installer/install.php` dan endpoint `Install::checkDatabase/createDb`. Helper menangani validasi nama, pemeriksaan target, pembuatan dua database, serta impor master ke keduanya. Seed belum dimasukkan pada tahap ini agar akun dummy tidak mengunci onboarding.
- `installer/includes/databaseLibrary.php`: delegasi ke helper installer bersama.
- `installer/includes/taskCoreClass.php`: menghasilkan konfigurasi database dua-environment dan `.env` development.
- `installer/install.php`: menjalankan proses dua target dan menampilkan kegagalan spesifik.
- `installer/index.php`: memperjelas nama dasar dan dua nama target pada form/petunjuk; label lama `INSTALL / UPDATE` diganti menjadi instalasi baru dan pembaruan diarahkan ke `/update`.
- `assets/app/db/database.php`: template konfigurasi memilih database berdasarkan `ENVIRONMENT`.
- `assets/app/db/master.sql`: skema terbaru dan baseline bersih.
- `assets/app/db/database.json`: kontrak skema terbaru yang sama dengan `master.sql`.
- `assets/app/db/development-seed.sql`: data dummy development.
- `application/controllers/Install.php`: kedua endpoint database memakai helper bersama, koneksi onboarding dipaksa ke grup development, dan titik selesai `createApp` memanggil finalisasi.
- `application/models/Install_model.php`: finalisasi transaksional admin/profil sekolah dan seed development.
- Tes installer kecil untuk validasi nama, target yang sudah ada, pemetaan nama, isi development, dan production yang bersih.

## Pengamanan dan kegagalan

- Nama database selalu divalidasi sebelum dipakai sebagai identifier SQL.
- `CREATE DATABASE IF NOT EXISTS` tidak digunakan karena dapat menimpa/mencampur instalasi lama secara diam-diam.
- Jika pembuatan target kedua atau impor gagal, installer melaporkan database mana yang belum lengkap. Database lama tidak pernah dihapus otomatis.
- Pada kegagalan pembuatan/import, hanya database target yang baru dibuat oleh request tersebut yang dibersihkan; database yang sudah ada tidak pernah dihapus.
- Semua hasil `multi_query` diperiksa; kegagalan statement tidak boleh dilaporkan sebagai sukses.
- Kredensial database tetap berasal dari form installer dan tidak ditulis ke `.env`; `.env` hanya menyimpan `CI_ENV`. Nilai hostname, username, dan password ditulis ke template PHP memakai literal `var_export()`, bukan substitusi mentah.
- Endpoint onboarding tetap dikunci oleh pengamanan yang sudah ada setelah admin pertama berhasil dibuat.
- Jalur `/update` tetap bekerja pada database environment yang sedang aktif. Karena `database.json` diperbarui bersama template, instalasi baru tidak membutuhkan update awal; pembaruan masa depan dijalankan sekali untuk development dan sekali untuk production dengan mengganti `CI_ENV`.

## Verifikasi

- Instalasi uji dengan nama dasar acak menghasilkan tepat dua database dengan jumlah tabel dan foreign key yang sama.
- Sebelum onboarding, kedua database tidak mempunyai pengguna sehingga halaman pemasangan tetap terbuka walaupun Apache menetapkan `CI_ENV=production`.
- Setelah onboarding, development mempunyai 6 guru, 30 siswa, 6 kelas, dan 2 bank soal dummy; production bernilai nol untuk tabel operasional tersebut.
- Setelah onboarding, hash admin dan profil sekolah sama di kedua database, tetapi akun dummy hanya ada di development.
- `.env` memilih development dan override server `CI_ENV=production` memilih production.
- Nama tidak valid dan target yang sudah ada ditolak tanpa mengubah database tersebut.
- Kegagalan terkontrol di tengah pembuatan, sinkronisasi, dan seed dapat dicoba ulang tanpa instalasi terkunci.
- Kedua jalur pembuat database menghasilkan hasil yang sama.
- Database uji dihapus setelah nama targetnya diverifikasi secara eksplisit; database `garuda_cbt`, `garuda_cbt_development`, dan `garuda_cbt_production` yang sekarang tidak disentuh.

## Non-tujuan

- Sinkronisasi perubahan data harian dari development ke production.
- Menyalin soal, ujian, guru, siswa, atau hasil ujian ke production.
- Menambahkan tombol promosi data ke production.

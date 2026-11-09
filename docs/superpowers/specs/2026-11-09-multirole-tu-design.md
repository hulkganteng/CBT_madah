# Desain Multi-role dan Role Tata Usaha

## Intent

GarudaCBT harus mendukung beberapa role pada satu akun staf. Kebutuhan utama adalah guru yang juga bertugas sebagai Tata Usaha (TU) dapat tetap memakai fitur Guru sekaligus mengelola dan mengimpor data siswa. Role TU juga harus dapat dipakai oleh pegawai yang bukan guru. Role Siswa tetap terpisah agar portal dan alur ujian peserta tidak bercampur dengan akses staf.

## Tujuan

- Admin dapat menetapkan satu atau beberapa role kepada akun melalui halaman manajemen user.
- Hak akses merupakan gabungan seluruh role akun.
- Tampilan utama mengikuti prioritas `admin`, lalu `guru`, lalu `tu`, lalu `siswa`.
- Akun Guru+TU memakai dashboard Guru dengan tambahan menu Data Siswa.
- Akun TU tanpa role Guru memakai layout administrasi terbatas dengan menu Data Siswa.
- Role TU dapat melihat, menambah, mengimpor, memperbarui, mengaktifkan, dan mereset login siswa.
- Pemeriksaan hak dilakukan di server untuk halaman dan endpoint AJAX, bukan hanya melalui menu.
- Kombinasi Siswa dengan role staf ditolak.

## Bukan Tujuan

- Membuat sistem izin per aksi yang dapat dirancang bebas oleh Admin.
- Memberi TU akses ke konfigurasi CBT, manajemen Admin, data Guru, bank soal, jadwal, nilai, atau penghapusan siswa.
- Membuat pemilih mode role setelah login.
- Mengubah kepemilikan bank soal atau perilaku Guru yang sudah ada.

## Batasan dan Keamanan

- Proyek memakai Ion Auth dan tabel relasi `users_groups`, yang sudah dapat menyimpan lebih dari satu grup per user.
- Banyak controller lama terobfuscation dan sebagian masih mengambil grup pertama. Perubahan harus dipusatkan sebisa mungkin pada Ion Auth, hook keamanan, routing dashboard, dan form pengelolaan user.
- Role `siswa` bersifat eksklusif. Validasi dilakukan saat penyimpanan role dan pemeriksaan portal login tetap menjadi pertahanan tambahan.
- TU tidak boleh menghapus siswa. Penghapusan dan perubahan konfigurasi tetap hanya untuk Admin.
- Semua endpoint mutasi siswa yang diberikan kepada TU harus menggunakan validasi, CSRF, dan aturan unggah yang sudah ada.

## Pendekatan yang Dipertimbangkan

### 1. Role kumulatif dengan prioritas tampilan — dipilih

Setiap akun dapat memiliki beberapa baris di `users_groups`. `in_group()` menentukan izin, sedangkan prioritas role menentukan template dashboard. Pendekatan ini memakai kemampuan Ion Auth yang sudah tersedia dan membutuhkan perubahan paling kecil.

### 2. Pemilih role aktif setelah login

Akun memilih mode Guru atau TU. Pendekatan ini menambah state sesi dan berisiko membuat izin server berbeda dari menu yang sedang tampil. Pengguna juga tidak menginginkan perpindahan mode.

### 3. Izin per fitur

Setiap aksi disimpan sebagai permission terpisah. Ini lebih fleksibel, tetapi memerlukan skema, UI, dan audit seluruh controller. Lingkupnya terlalu besar untuk kebutuhan Guru+TU.

## Desain Perilaku

### Daftar role

Migrasi menambahkan grup `tu` dengan deskripsi `Tata Usaha` apabila grup tersebut belum ada. Migrasi harus idempoten dan tidak mengubah ID grup lama. Kode menggunakan nama grup, bukan mengasumsikan ID TU tertentu.

### Pengaturan role oleh Admin

Halaman edit user menampilkan pilihan role jamak. Role yang dimiliki user ditandai dari seluruh hasil `get_users_groups()->result()`, bukan elemen pertama.

Saat disimpan, server:

1. memastikan pemanggil adalah Admin;
2. menerima array ID role dan menormalkan nilai duplikat;
3. memastikan minimal satu role dipilih dan seluruh ID tersedia pada tabel `groups`;
4. menolak `siswa` apabila dipilih bersama role lain;
5. menyinkronkan relasi `users_groups` dalam transaksi, dengan API Ion Auth untuk menambah dan menghapus grup;
6. mengembalikan kegagalan tanpa meninggalkan role setengah tersimpan.

### Prioritas tampilan

- Memiliki `admin`: dashboard dan template Admin.
- Tidak memiliki Admin tetapi memiliki `guru`: dashboard dan template Guru.
- Hanya role staf `tu`: template administrasi terbatas.
- Memiliki `siswa`: portal Siswa; kombinasi dengan role lain tidak valid.

Prioritas hanya menentukan tampilan. Otorisasi tetap memeriksa seluruh role.

### Akses TU

Role TU memperoleh akses server-side ke daftar dan detail siswa, formulir tambah, preview impor, commit impor, pembaruan data, unduh format/data, aktivasi akun siswa, dan reset login siswa. Daftar method konkret dibuat sebagai allowlist pada hook keamanan agar method baru di controller siswa tidak otomatis terbuka.

Method penghapusan siswa, penghapusan berkas, pengaturan sistem, manajemen user staf, serta fitur CBT lain tetap ditolak untuk TU.

### Menu

- Guru+TU: sidebar Guru mendapat menu `Data Siswa` yang menuju alur siswa yang sama dengan Admin, tetapi tombol yang tidak diizinkan TU disembunyikan.
- TU mandiri: memakai header/sidebar administrasi dengan hanya Beranda, Data Siswa, profil/ganti password, dan logout.
- Admin: menu tetap seperti sekarang.

Menu mengikuti izin untuk menghindari tautan buntu, sedangkan hook keamanan tetap menjadi sumber keputusan akses.

### Login dan dashboard

Respons login mengenali role berdasarkan prioritas yang sama dan mengarahkan semua staf ke `/dashboard`. Dashboard memilih template berdasarkan role. Akun TU tidak lagi jatuh ke halaman login dinonaktifkan.

### Kompatibilitas data

Akun lama tetap memiliki role dan perilaku yang sama. Migrasi hanya menambah grup TU. Form baru membaca semua relasi yang sudah ada, sehingga kombinasi role yang sebelumnya dimasukkan manual akan tampil dan dapat disimpan dengan benar.

## Validasi

- Uji sinkronisasi multi-role: Guru+TU, Admin+Guru, pengurangan salah satu role, ID role tidak valid, dan penyimpanan tanpa role.
- Uji aturan eksklusif Siswa.
- Uji routing dashboard untuk Admin, Guru, TU, Guru+TU, dan Siswa.
- Uji TU dapat membuka dan menjalankan endpoint impor/pembaruan siswa yang diizinkan.
- Uji TU menerima 403 untuk hapus siswa, pengaturan sistem, manajemen staf, dan fitur CBT di luar haknya.
- Uji regresi akses Admin, Guru, dan Siswa yang sudah ada.

## Keputusan Implementasi yang Tersisa

Builder perlu memetakan method pada `Datasiswa`, `Datasiswaform`, dan `Usersiswa` ke allowlist TU secara eksplisit. Jika sebuah method mencampur operasi yang diizinkan dan terlarang, operasi tersebut dipisahkan atau diperiksa berdasarkan method tanpa memperluas hak TU.

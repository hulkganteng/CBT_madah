# Referensi aplikasi

Referensi ini merangkum persyaratan, peran, lokasi konfigurasi, status ujian, endpoint keamanan, dan data audit. Gunakan halaman ini saat mengoperasikan atau mendiagnosis instalasi.

## Persyaratan server

Komponen berikut dibaca dari konfigurasi dan dependensi proyek.

| Komponen | Persyaratan |
|---|---|
| Web server | Apache atau server kompatibel dengan rewrite CodeIgniter |
| PHP | 7.0 atau lebih baru |
| Database | MySQL/MariaDB melalui ekstensi `mysqli` |
| Ekstensi PHP | DOM, MySQLi, Fileinfo, ZIP, JSON, GD, Iconv, Calendar |
| Zona waktu | `Asia/Jakarta` |
| HTTPS | Wajib disarankan untuk produksi dan SEB |

## Lokasi konfigurasi

Gunakan berkas berikut untuk konfigurasi server aplikasi.

| Berkas | Fungsi |
|---|---|
| `application/config/config.php` | Base URL, session, cookie, CSRF, encryption key |
| `application/config/database.php` | Koneksi database |
| `application/config/migration.php` | Versi dan perilaku migrasi |
| `application/config/hooks.php` | Aktivasi lapisan keamanan ujian |
| `application/config/exam_baku.php` | Angka baku ujian: 25 (Eksata) / 30 (Non-eksata) soal, 60 menit, 25 peserta per sesi |
| `.htaccess` | Rewrite URL dan aturan Apache |
| `assets/app/db/master.sql` | Skema dasar untuk instalasi manual |

## Modul yang dinonaktifkan

Versi ini CBT murni. Daftar route berikut diblok server-side oleh `Exam_security_hook` (respons `403`), bukan hanya disembunyikan dari menu:

- Rapor, Leger Rapor, Buku Induk, Data Alumni: `rapor`, `bukurapor`, `bukuinduk`, `dataalumni`
- e-Learning dan KBM: `kelasmateri`, `kelasmaterijadwal`, `kelascatatan`, `kelasjadwal`, `kelasstatus`, `kelasnilai`, `kelasabsensiharian`, `kelasabsensibulanan`, `walicatatan`
- Controller legacy tanpa berkas pendukung: `users`, `jurusanmapel`, `hasilujian`
- **Data Manager (`dbclear`)**: menjalankan `drop_table` pada **semua** tabel lewat `GET`. Jangan diaktifkan kembali. Gunakan **Backup → Pemulihan Database** (`dbpemulihan`).
- `dbmanager/truncate` juga ditolak; backup dan hapus berkas backup tetap tersedia.

Halaman siswa e-Learning (`materi`, `tugas`, `bukamateri`, `bukatugas`, `kehadiran`, `jadwalpelajaran`, `catatan`, `seminggu`, dan endpoint log terkait) juga diblok di `Siswa`.

## Alamat hybrid (Online + LAN)

`base_url` dibangun dari host yang sedang dipakai pengakses, jadi aplikasi tidak terkunci ke satu alamat:

```php
$scheme = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
$host   = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ...;
$config['base_url'] = $scheme . '://' . $host . preg_replace('@/+$@', '', dirname($scriptName)) . '/';
```

Konsekuensinya: pindah jalur Online ke LAN mengharuskan login ulang (sesi browser baru), tetapi token, jawaban tersimpan, dan sisa waktu tetap sama karena satu database. Lihat [Laboratorium Hybrid](lab-hybrid.md).

## Peran pengguna

Garuda CBT versi ini adalah CBT murni. Modul Rapor, Buku Induk, dan e-Learning sudah dihapus dari navigasi dan diblok server-side.

| Peran | Area utama |
|---|---|
| Administrator | Data master, CBT, pengguna, backup database |
| Guru | Bank soal, jadwal, hasil, analisis soal sesuai penugasan |
| Proktor/pengawas | Token, aktivitas siswa, izin, perpanjangan waktu |
| Siswa | Dashboard ujian dan CBT miliknya |

## Status attempt ujian

Tabel `cbt_exam_attempt` menyimpan satu attempt untuk kombinasi siswa dan jadwal.

| Status | Arti |
|---|---|
| `IN_PROGRESS` | Ujian sedang berlangsung dan jawaban dapat diterima sebelum deadline |
| `SUBMITTED` | Siswa berhasil menyelesaikan ujian |
| `EXPIRED` | Deadline server telah terlewati |

## Event keamanan

Tabel `cbt_exam_event` menyimpan event append-only yang terkait dengan attempt.

| Event | Arti |
|---|---|
| `PAGE_HIDDEN` / `PAGE_VISIBLE` | Visibilitas halaman berubah |
| `FULLSCREEN_EXIT` / `FULLSCREEN_ENTER` | Status fullscreen berubah |
| `OFFLINE` / `ONLINE` | Status jaringan browser berubah |
| `HEARTBEAT_MISSED` | Heartbeat tidak diterima atau event tidak dikenal |
| `LEAVE_ATTEMPT` | Siswa meninggalkan alur ujian |
| `SEB_UNVERIFIED` | Validasi SEB tidak berhasil |

Metadata menyimpan konteks terbatas. Alamat IP dan user agent disimpan sebagai hash pada tabel audit baru.

## Endpoint keamanan ujian

Endpoint ini digunakan oleh halaman siswa dan tetap memerlukan sesi siswa serta CSRF yang valid.

| Endpoint | Metode | Fungsi |
|---|---|---|
| `examguard/status/{jadwal}` | POST | Memeriksa mode dan validasi SEB |
| `examguard/heartbeat` | POST | Memperbarui waktu heartbeat attempt |
| `examguard/event` | POST | Mencatat event browser dan jaringan |
| `examguard/ragu` | POST | Menandai soal ragu-ragu sebagai event |

## Endpoint pemantauan proktor

| Endpoint | Metode | Fungsi |
|---|---|---|
| `examactivity/data/{jadwal}` | GET | Status per peserta, sisa waktu, detik sejak heartbeat, hitungan pelanggaran |
| `examactivity/detail/{attempt}` | GET | Riwayat event satu attempt |
| `examactivity/kick` | POST | Keluarkan peserta (`kicked_at`) |
| `examactivity/unkick` | POST | Izinkan masuk kembali dan kosongkan `last_heartbeat_at` |
| `examactivity/extend` | POST | Tambah waktu dengan alasan wajib |

## Kolom SEB pada jadwal

Migrasi keamanan menambahkan kolom berikut ke `cbt_jadwal`.

| Kolom | Isi |
|---|---|
| `seb_mode` | `OFF`, `OPTIONAL`, atau `REQUIRED` |
| `seb_config_key` | Config Key 64 karakter heksadesimal atau `NULL` |
| `seb_browser_exam_keys` | Satu atau lebih Browser Exam Key, satu per baris |

## Konfigurasi session

Konfigurasi saat ini membatasi session menjadi empat jam dan meregenerasi ID session setiap lima menit.

| Opsi | Nilai |
|---|---|
| `sess_expiration` | `14400` detik |
| `sess_time_to_update` | `300` detik |
| `sess_regenerate_destroy` | `TRUE` |
| `cookie_httponly` | `TRUE` |
| `cookie_secure` | Aktif saat aplikasi memakai HTTPS |

## Migrasi keamanan

Versi migrasi keamanan adalah `20260812000100`. Migrasi CBT murni berikutnya:

| Versi | Isi |
|---|---|
| `20261028000100` | `master_mapel.kelompok_ujian` (Eksata / Non-eksata) dan kolom baku bank soal |
| `20261028000200` | Tabel `cbt_exam_attempt`, `cbt_exam_event`, kolom SEB pada `cbt_jadwal`, `cbt_nilai.bs_nilai` |

Jalankan migrasi dari root aplikasi:

```powershell
C:\xampp\php\php.exe index.php migrate CreateMigration
```

Tabel `migrations` pada paket ini hanya memiliki kolom `version`. Untuk instalasi ulang, periksa:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "SELECT * FROM garuda_cbt.migrations;"
```


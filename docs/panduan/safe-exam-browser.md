# Menggunakan Safe Exam Browser

Panduan ini membantu administrator dan proktor menghubungkan jadwal GarudaCBT dengan Safe Exam Browser (SEB). Integrasi memvalidasi Config Key atau Browser Exam Key melalui header HTTP maupun JavaScript API resmi SEB.

## Kebijakan SEB global

Semua jadwal memakai satu kebijakan SEB global dan satu file `.seb` resmi sekolah.

| Kebijakan | Perilaku |
|---|---|
| **Wajib** (`REQUIRED`) | Portal siswa dan ujian ditolak bila browser atau key SEB tidak cocok |

## Buat konfigurasi SEB

Gunakan SEB Config Tool pada platform yang dipakai sekolah.

1. Atur Start URL ke halaman aplikasi melalui HTTPS, misalnya `https://ujian.sekolah.sch.id/`.
2. Batasi navigasi, aplikasi lain, clipboard, tangkapan layar, dan keluar dari SEB sesuai kebijakan sekolah.
3. Aktifkan **Use Browser & Config Keys (send in HTTP header)** untuk kompatibilitas browser engine klasik.
4. Simpan konfigurasi sebagai berkas `.seb` dan lindungi perubahan konfigurasi dengan kata sandi admin.
5. Salin **Config Key** dari SEB Config Tool.
6. Jika memakai Browser Exam Key, salin key untuk setiap versi dan platform yang diizinkan.

Config Key lebih mudah dikelola karena tetap sama lintas platform selama isi konfigurasi tidak berubah. Browser Exam Key dapat berubah menurut versi SEB.

## Satu berkas .seb untuk semua jalur

Setiap PC client memakai satu berkas `.seb` dengan Start URL global yang sama untuk Online dan LAN:

| Berkas | Start URL | Dipakai shortcut |
|---|---|---|
| `GarudaCBT.seb` | `https://ujian.sekolah.sch.id/` | **Semua akses** |

Pastikan DNS/routing sekolah membuat URL tersebut dapat dijangkau dari kedua jaringan. Simpan berkas di `C:\CBT\` pada setiap PC client.

### Membuat kedua berkas dari server

Kedua berkas dapat dibuat ulang tanpa membuka SEB Config Tool:

```powershell
cd C:\xampp\htdocs\garudacbt
C:\xampp\php\php.exe tools\buat-seb.php online C:\CBT\GarudaCBT.seb
```

Perintah ini mencetak **Config Key** tiap berkas. Keluaran bersifat deterministik: menjalankan ulang perintah yang sama menghasilkan Config Key yang sama selama isi konfigurasi tidak berubah.

Kata sandi administrator dan kata sandi keluar SEB diambil dari variabel lingkungan, sehingga tidak tersimpan di dalam repo:

```powershell
$env:SEB_ADMIN_PASSWORD = 'kata-sandi-admin-sekolah'
$env:SEB_QUIT_PASSWORD  = 'kata-sandi-keluar'
C:\xampp\php\php.exe tools\buat-seb.php online C:\CBT\SEB-Online.seb
```

Tanpa `SEB_QUIT_PASSWORD`, peserta dapat keluar dari SEB tanpa kata sandi. Isi keduanya untuk pelaksanaan sesungguhnya.

Berkas dibuat dengan prefiks `plnd` (tidak dienkripsi, hanya dikompresi ganda) sesuai spesifikasi berkas SEB. Artinya peserta yang memahami format dapat membaca pengaturan di dalamnya. Yang menjaga ujian tetap terkunci adalah Config Key global pada server, bukan kerahasiaan berkas. Bila sekolah memerlukan berkas terenkripsi kata sandi, buka berkas hasil alat ini di SEB Config Tool, lalu simpan ulang dengan kata sandi; Config Key akan berubah dan harus diperbarui pada environment server.


## Hubungkan key global ke server

Pasang key hasil pembuatan file `.seb` pada environment server. Operator tidak mengisi key pada jadwal.

```powershell
$env:GARUDACBT_SEB_START_URL = 'https://ujian.sekolah.sch.id/'
$env:GARUDACBT_SEB_CONFIG_KEY = '<64 karakter Config Key>'
$env:GARUDACBT_SEB_BROWSER_EXAM_KEYS = '<opsional, satu key per baris>'
```

Setelah itu semua jadwal otomatis memakai kebijakan SEB global.

## Uji konfigurasi

Gunakan akun siswa uji dan perangkat yang sama dengan pelaksanaan sebenarnya.

1. Buka berkas `.seb`.
2. Masuk sebagai siswa uji.
3. Buka jadwal ujian.
4. Pastikan halaman konfirmasi menampilkan **Safe Exam Browser terverifikasi**.
5. Mulai ujian dan tunggu lebih dari 30 detik untuk menguji heartbeat.
6. Uji muat ulang halaman, putus-sambung jaringan, dan penyelesaian ujian.
7. Ulangi pada setiap platform dan versi SEB yang diizinkan.

Jangan memakai akun atau jadwal produksi untuk simulasi jika hasilnya akan masuk ke rekap nilai resmi.

## Rotasi konfigurasi

Perubahan apa pun pada berkas `.seb` dapat mengubah Config Key.

1. Simpan konfigurasi SEB yang baru.
2. Salin Config Key baru.
3. Perbarui `GARUDACBT_SEB_CONFIG_KEY` pada environment server.
4. Uji kembali dengan akun siswa uji.
5. Distribusikan berkas `.seb` baru dan tarik versi lama.

## Cara validasi bekerja

SEB mengirim hash yang bergantung pada URL halaman. Server menghitung hash yang sama dari URL absolut dan key yang disimpan, lalu membandingkannya secara aman.

Implementasi mendukung dua jalur resmi:

- `X-SafeExamBrowser-ConfigKeyHash` dan `X-SafeExamBrowser-RequestHash` untuk engine yang mengirim header.
- `SafeExamBrowser.security.configKey` dan `browserExamKey` untuk JavaScript API, termasuk WKWebView modern.

Lihat [dokumentasi Config Key SEB](https://safeexambrowser.org/developer/seb-config-key.html) dan [spesifikasi Browser Exam Key](https://safeexambrowser.org/developer/documents/SEB-Specification-BrowserExamKey.pdf) untuk rumus dan kompatibilitas resmi.

## Langkah berikutnya

Gunakan dokumen berikut untuk pelaksanaan aman.

- [Laboratorium Hybrid](lab-hybrid.md) untuk memasang dua shortcut dan berkas `.seb` per jalur.
- [Operasional ujian](operasional-ujian.md) untuk simulasi dan daftar periksa proktor.
- [Cara kerja keamanan ujian](keamanan-ujian.md) untuk memahami batas perlindungan SEB.
- [Pemecahan masalah](troubleshooting.md) untuk key tidak cocok atau tombol mulai nonaktif.

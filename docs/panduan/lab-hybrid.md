# Laboratorium Hybrid: Online dan LAN

Panduan ini menyiapkan satu PC server dan 25 PC client agar Garuda CBT dapat dibuka dari **dua jalur** dengan data yang sama: domain HTTPS (Cloudflare Tunnel) dan alamat IP LAN.

Kedua jalur bukan dua sistem. Keduanya membuka aplikasi dan database yang persis sama di PC server yang sama. Yang berbeda hanya alamat yang diketik di browser.

```text
JALUR A - ONLINE                              JALUR B - LAN
peserta -> internet -> Cloudflare Edge        PC client 1-25 -> switch lab
  -> Cloudflare Tunnel (cloudflared)            -> PC server langsung (tanpa internet)
  -> PC server https://cbt.bhydev.my.id         http://192.168.1.10/garudacbt
```

## 1. Siapkan PC server

| Item | Ketentuan |
|---|---|
| Stack | Windows + XAMPP, PHP 7.4-8.3, Apache dan MySQL berjalan otomatis saat PC menyala |
| IP | IP statis di LAN lab, tidak berubah selama periode ujian (contoh `192.168.1.10`) |
| Daya | Restart otomatis Windows Update dinonaktifkan pada hari ujian; PC server tidak dipakai peserta |
| Folder | Aplikasi di `htdocs/garudacbt`, satu database `garuda_cbt` untuk kedua jalur |

Tetapkan IP statis lewat **Control Panel > Network > Adapter > IPv4 > Use the following IP address**, lalu catat di berita acara teknis.

## 2. Pasang Cloudflare Tunnel sebagai service

`cloudflared` wajib hidup otomatis saat PC menyala, karena ia komponen jalur utama Online.

```powershell
# 1) Unduh cloudflared (winget atau MSI resmi Cloudflare)
winget install --id Cloudflare.cloudflared

# 2) Login ke akun Cloudflare sekolah (hanya sekali, membuka browser)
cloudflared tunnel login

# 3) Buat tunnel dan arahkan ke Apache lokal
cloudflared tunnel create garudacbt
cloudflared tunnel route dns garudacbt cbt.bhydev.my.id
```

Buat berkas `C:\ProgramData\cloudflared\config.yml`:

```yaml
tunnel: garudacbt
credentials-file: C:\Users\<user>\.cloudflared\<tunnel-id>.json
ingress:
  - hostname: cbt.bhydev.my.id
    service: http://localhost:80
  - service: http_status:404
```

Pasang sebagai service supaya tidak bergantung pada sesi login:

```powershell
cloudflared --config C:\ProgramData\cloudflared\config.yml service install
Start-Service cloudflared
Get-Service cloudflared          # harus Running
```

Periksa **sebelum sesi pertama**: buka `https://cbt.bhydev.my.id/` dari luar lab. Bila gagal, jalur Online belum siap dan sesi harus dijalankan lewat LAN.

> Keamanan: halaman admin kini terekspos internet. Ganti password admin bawaan, pakai password kuat, dan pertimbangkan **Cloudflare Access** untuk membatasi `/auth` dan menu pengaturan.

## 3. `base_url` dinamis

Aplikasi tidak boleh mengunci alamat. `application/config/config.php` membangun `base_url` dari host yang sedang dipakai, sehingga aset, form login, dan pengumpulan jawaban mengikuti jalur yang aktif.

Bukti cepat dari PC server:

```powershell
(Invoke-WebRequest 'http://localhost/garudacbt/auth' -UseBasicParsing).Content -match 'http://localhost/garudacbt/'
(Invoke-WebRequest 'http://127.0.0.1/garudacbt/auth' -UseBasicParsing).Content -match 'http://127.0.0.1/garudacbt/'
```

Keduanya harus `True`. Bila salah satu `False`, `base_url` dikunci ke satu alamat dan peserta akan terlempar ke jalur yang salah.

## 4. Siapkan 25 PC client

Setiap PC client punya **dua shortcut** di desktop dengan nama persis:

1. **CBT Online** - domain HTTPS resmi.
2. **CBT Lokal (Darurat)** - alamat LAN server.

Nama tidak boleh diubah agar instruksi proktor tidak ambigu. Buat keduanya dengan skrip yang disertakan:

```powershell
powershell -ExecutionPolicy Bypass -File C:\CBT\Buat-Shortcut-CBT.ps1 `
  -OnlineUrl "https://cbt.bhydev.my.id/" `
  -LanUrl    "http://192.168.1.10/garudacbt/" `
  -SebOnline "C:\CBT\SEB-Online.seb" `
  -SebLokal  "C:\CBT\SEB-Lokal.seb"
```

Bila berkas `.seb` tersedia, shortcut menjalankan Safe Exam Browser. Bila belum, skrip jatuh ke Chrome/Edge mode kiosk supaya PC tetap terpakai saat simulasi. Lihat [Safe Exam Browser](safe-exam-browser.md) untuk membuat berkas `.seb` dan Config Key.

Untuk 25 PC sekaligus, jalankan skrip itu dari PC proktor lewat **Veyon > Execute command on selected computers**.

## 5. Pantau dan alihkan lewat Veyon

Veyon dipakai proktor untuk menyalakan, memantau, dan mengalihkan client secara massal.

1. Pasang Veyon Master di PC proktor dan Veyon Service di setiap client.
2. Beri nama client sesuai nomor PC (`PC-01` ... `PC-25`) agar cocok dengan daftar hadir.
3. Sebelum sesi: **Start all computers**, lalu buka semua layar untuk memastikan tidak ada yang tertinggal di desktop.
4. Saat pindah jalur: pilih semua client, jalankan perintah
   `"C:\Users\Public\Desktop\CBT Lokal (Darurat).lnk"`
   atau klik kanan shortcut lewat remote control.
5. Setelah sesi: matikan client dari Veyon setelah semua peserta berstatus selesai.

## 6. Prosedur pindah jalur di tengah ujian

Wajib dilatih, bukan hanya ditulis.

1. Pastikan gangguan memang pada jalur Online (internet / tunnel), bukan pada PC server. Bila server yang bermasalah, pindah jalur tidak menolong.
2. Umumkan **satu** instruksi yang sama ke seluruh peserta, lalu alihkan client secara massal lewat Veyon ke **CBT Lokal (Darurat)**.
3. Peserta login ulang di alamat LAN dengan akun dan token sesi yang sama. Pindah alamat berarti sesi browser baru, jadi login ulang memang diperlukan; sampaikan agar tidak dianggap error.
4. Peserta melanjutkan dari jawaban terakhir yang tersimpan dengan sisa waktu yang sama. Proktor memeriksa **Aktivitas Siswa**: seluruh peserta harus kembali berstatus *sedang mengerjakan* sebelum ujian dilanjutkan.
5. Catat perpindahan jalur, jam, dan penyebabnya di berita acara sesi.

## 7. Periksa sebelum sesi

- [ ] `Get-Service cloudflared` = `Running`.
- [ ] Login terbuka dari **kedua** alamat; jalur sesi ini ditetapkan dan diumumkan.
- [ ] `base_url` dinamis terbukti (langkah 3).
- [ ] Kedua shortcut ada di desktop **semua** 25 client.
- [ ] Veyon menampilkan 25 layar.
- [ ] Backup database sebelum sesi pertama dibuat.
- [ ] Token sesi aktif; daftar hadir dan kartu peserta cadangan tercetak.
- [ ] Dashboard **Aktivitas Siswa** terbuka di PC proktor.

## Langkah berikutnya

- [Operasional ujian](operasional-ujian.md) untuk urutan pelaksanaan dan penutupan sesi.
- [Pemeliharaan](pemeliharaan.md) untuk backup sebelum dan sesudah hari ujian.
- [Pemecahan masalah](troubleshooting.md) bila salah satu jalur gagal.

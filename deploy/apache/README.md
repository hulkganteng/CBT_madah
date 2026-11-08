# Konfigurasi Domain cbtmadah.web.id

Domain belum aktif, jadi berkas ini disiapkan agar tinggal dipasang saat DNS
sudah mengarah ke server ini.

## Isi

- `cbt-sekolah.conf` — VirtualHost Apache untuk port 80 (HTTP) dan 443 (HTTPS).
  DocumentRoot `/var/www/cbt`, `AllowOverride All` agar `.htaccess` aplikasi
  berlaku, dan folder internal (`system`, `application`, `vendor`, `backups`,
  `installer/includes`) ditolak akses langsung.
- Saat memasang besok, tambahkan baris berikut ke `/var/www/cbt/.env` supaya
  Host header domain ini diterima untuk base_url dan hash Safe Exam Browser:

  ```
  APP_ALLOWED_HOSTS=cbtmadah.web.id,www.cbtmadah.web.id
  ```

  Jangan diisi sebelum domain aktif: selama allowlist terpasang, akses lewat
  localhost/IP akan dialihkan ke domain tersebut.

## Langkah pasang (butuh sudo)

```bash
# 1. Pasang virtual host
sudo cp /var/www/cbt/deploy/apache/cbt-sekolah.conf /etc/apache2/sites-available/
sudo a2ensite cbt-sekolah

# 2. Aktifkan modul SSL bila belum
sudo a2enmod ssl
sudo systemctl restart apache2

# 3. Uji konfigurasi lalu muat ulang
sudo apache2ctl configtest
sudo systemctl reload apache2
```

## Uji lokal tanpa DNS publik

Tambahkan ke `/etc/hosts` di komputer yang dipakai mengakses:

```
127.0.0.1 cbtmadah.web.id www.cbtmadah.web.id
```

Lalu buka `http://cbtmadah.web.id/`.

## Ganti sertifikat HTTPS

Saat domain sudah aktif dan mengarah ke server, terbitkan sertifikat nyata:

```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d cbtmadah.web.id -d www.cbtmadah.web.id
```

Setelah itu buka komentar baris `Redirect permanent / https://...` pada blok
port 80 agar semua akses dialihkan ke HTTPS.

## Catatan

- Domain final sudah disetel ke `cbtmadah.web.id` dan `www.cbtmadah.web.id`.
- Virtual host lama (`cbt-madah.conf`) tetap ada; nonaktifkan dengan
  `sudo a2dissite cbt-madah` bila ingin domain baru menjadi satu-satunya entri.
- Installer otomatis terkunci selama database bisa dihubungi (lihat
  `installer/guard.php`), jadi tidak perlu khawatir terbuka ke publik.

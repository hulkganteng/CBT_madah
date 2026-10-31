# Implementation Notes

## Deviations

- Rencana semula memakai environment sebagai sumber utama. Agar instalasi XAMPP lokal tetap bisa memakai satu kebijakan global tanpa konfigurasi Apache tambahan, ditambahkan fallback terlindungi `application/config/.seb_global.php`.
- Audit pasca-implementasi menemukan blok konfigurasi SEB global duplikat yang menimpa fallback lokal dengan environment kosong. Blok duplikat dihapus; ini adalah penyebab penolakan SEB pada instalasi saat ini.

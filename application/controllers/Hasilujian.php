<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller legacy: bergantung pada Ujian_model yang tidak ada di paket ini.
 * URL /hasilujian dibiarkan hidup agar pengguna mendapat pesan 403 yang jelas,
 * bukan jatuh ke 404_override (auth) yang melempar kembali ke dashboard.
 * Hasil ujian CBT dilayani cbtnilai, cbtanalisis, dan cbtrekap.
 */
class HasilUjian extends CI_Controller
{
    public function index()
    {
        show_error('Modul ini tidak termasuk dalam lingkup CBT. Gunakan menu Hasil Ujian, Analisis Soal, atau Rekap Nilai.', 403, 'Modul Dinonaktifkan');
    }

    public function _remap($method = 'index')
    {
        $this->index();
    }
}

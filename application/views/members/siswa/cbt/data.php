<?php
/**
 * Created by IntelliJ IDEA.
 * User: multazam
 * Date: 07/08/20
 * Time: 22:29
 */

$arrGuru = [];
foreach ($guru as $g) {
    $arrGuru[$g->id_guru] = $g->nama_guru;
}
$this->load->library(['Student_exam_eligibility', 'Exam_schedule_slots', 'Exam_attempt_service']);
$expiredSessions = $this->exam_attempt_service->expiredSessionAttempts($siswa->id_siswa);

$slotService = $this->exam_schedule_slots;
$plan = $slotService->periodSlots($tp_active->id_tp, $smt_active->id_smt);
$studentSlots = [];
if ($plan) {
    $scheduled = array_fill_keys(array_column($plan, 'id_jadwal'), TRUE);
    foreach ($cbt_jadwal as $date => $day) {
        $cbt_jadwal[$date] = array_filter($day, static function ($schedule) use ($scheduled) {
            return !isset($scheduled[$schedule->id_jadwal]);
        });
        if (!$cbt_jadwal[$date]) {
            unset($cbt_jadwal[$date]);
        }
    }
    $nowWib = Exam_schedule_slots::now();
    $todayWib = $nowWib->format('Y-m-d');
    $context = $slotService->studentContext($siswa->id_siswa, $tp_active->id_tp, $smt_active->id_smt);
    $assigned = $slotService->assignedSlots($plan, $context);
    $todayAttempts = [];
    if (!empty($assigned[$todayWib])) {
        $attempts = $this->db->where('id_siswa', $siswa->id_siswa)
            ->where_in('id_jadwal', array_keys($assigned[$todayWib]))->get('cbt_exam_attempt')->result();
        $todayAttempts = array_column($attempts, NULL, 'id_jadwal');
    }
    foreach ($assigned as $date => $day) {
        uasort($day, static function ($left, $right) {
            return [$left['urutan'], $left['jam_buka']] <=> [$right['urutan'], $right['jam_buka']];
        });
        foreach ($day as $id => $slot) {
            if (!$slotService->bankAvailable($slot, $siswa)
                || !$this->student_exam_eligibility->isEligible($siswa->id_siswa, $id)) {
                continue;
            }
            $slot['can_resume'] = FALSE;
            $slot['block_reason'] = NULL;
            if ($date === $todayWib) {
                $attempt = $todayAttempts[$id] ?? NULL;
                if ($attempt) {
                    $attempt = $this->exam_attempt_service->applySessionDeadline($attempt);
                    list($slot['can_resume'], $slot['block_reason']) = $this->exam_attempt_service->validateActiveAttempt($attempt);
                } else {
                    $slot['block_reason'] = $slotService->startBlock($slot, $day, $siswa, $nowWib);
                }
            }
            $studentSlots[$date][$id] = $slot;
        }
    }
}

$jam_pertama = null;
$jadwal_selesai = [];
?>
<div class="content-wrapper" style="margin-top: -1px;">
    <div class="sticky">
    </div>
    <section class="content overlap p-4">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <?php
                    $cbt_setting = [];
                    $this->load->view('members/siswa/templates/top'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card my-shadow">
                        <div class="card-header">
                            <h2 class="h5 text-center mb-0">Informasi ujian</h2>
                        </div>
                        <div class="card-header border-bottom-0">
                            <div class="text-center">
                                <a href="<?= base_url('ujicoba') ?>" class="btn btn-success btn-block">
                                    <i class="fas fa-play mr-1"></i> Simulasi / Ujicoba Latihan Soal
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <?php
                                    //echo '<pre>';
                                    //var_dump($elapsed);
                                    //var_dump(strtotime($durasiMulai->format('H:i')));
                                    //echo '</pre>';
                                    if ($cbt_info == null) : ?>
                                        <div class="alert alert-default-warning">
                                            <div class="text-center">Tidak ada jadwal penilaian</div>
                                        </div>
                                    <?php else: ?>
                                        <div class="card border">
                                            <div class="card-header border-bottom-0">
                                                <h4 class="card-title mt-1 text-wrap"><?= $siswa->nama ?></h4>
                                            </div>
                                            <div class="card-body pt-0">
                                                <ul class="list-group list-group-unbordered">
                                                    <?php
                                                    $arrTitle = ['No. Peserta', 'Ruang', 'Sesi', 'Dari', 'Sampai'];
                                                    $currentSlots = $studentSlots[$todayWib ?? ''] ?? [];
                                                    $currentSlot = reset($currentSlots);
                                                    if ($currentSlot && (int) $currentSlot['id_sesi'] !== (int) $cbt_info->id_sesi) {
                                                        $arrTitle = ['No. Peserta', 'Ruang semester', 'Sesi semester', 'Dari (semester)', 'Sampai (semester)'];
                                                    }
                                                    $arrSub = [$cbt_info->no_peserta->nomor_peserta ?? NULL, $cbt_info->nama_ruang, $cbt_info->nama_sesi, substr((string) $cbt_info->waktu_mulai, 0, -3), substr((string) $cbt_info->waktu_akhir, 0, -3)];
                                                    foreach ($arrTitle as $key => $title) :
                                                        if ($arrSub[$key] == null) array_push($cbt_setting, $title)
                                                        ?>
                                                        <li class="list-group-item p-1">
                                                            <?= $title ?>
                                                            <span class="float-right"><b><?= $arrSub[$key] ?></b></span>
                                                        </li>
                                                    <?php
                                                    endforeach; ?>
                                                    <?php if ($currentSlot && (int) $currentSlot['id_sesi'] !== (int) $cbt_info->id_sesi) : ?>
                                                        <li class="list-group-item p-1">
                                                            Sesi ujian hari ini
                                                            <span class="float-right"><b><?= html_escape($currentSlot['nama_sesi']) ?></b></span>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-8">
                                    <div class="alert alert-default-danger">
                                        <div class="text-center"><b>Peraturan ujian</b></div>
                                        Selama melaksanakan ULANGAN/UJIAN <b>Siswa DILARANG:</b>
                                        <ul>
                                            <li>
                                                Meninggalkan ruang ujian tanpa izin pengawas
                                            </li>
                                            <li>
                                                Logout/Keluar dari aplikasi tanpa izin dari pengawas
                                            </li>
                                            <li>
                                                Saling memberitahukan jawaban sesama peserta
                                            </li>
                                            <li>
                                                Membawa makanan dan minuman
                                            </li>
                                            <li>
                                                Membawa handphone ke ruangan ujian
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($expiredSessions) : ?>
                    <div class="col-12">
                        <div class="card my-shadow">
                            <div class="card-header"><h5 class="mb-0">Ujian belum terkumpul</h5></div>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($expiredSessions as $expiredSession) : ?>
                                    <li class="list-group-item d-flex flex-wrap align-items-center justify-content-between">
                                        <span><?= html_escape($expiredSession->nama_mapel ?: $expiredSession->bank_kode ?: 'Ujian CBT') ?></span>
                                        <?= form_open('siswa/selesaiujian', [], [
                                            'jadwal' => (int) $expiredSession->id_jadwal,
                                            'waktu_habis' => '1',
                                            'kembali_cbt' => '1',
                                        ]) ?>
                                        <button type="submit" class="btn btn-primary btn-sm">Kumpulkan jawaban tersimpan</button>
                                        <?= form_close() ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($studentSlots) {
                    $this->load->view('members/siswa/cbt/_session_slots', ['studentSlots' => $studentSlots, 'todayWib' => $todayWib]);
                } ?>
                <?php if (!$studentSlots || isset($cbt_jadwal[date('Y-m-d')])) : ?>
                <div class="col-12">
                    <div class="card my-shadow">
                        <div class="card-header">
                            <h5 class="text-center">
                                Jadwal ujian hari ini<br/><?= buat_tanggal(date('D, d M Y')) ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row" id="jadwal-content">
                                <?php
                                if ($cbt_info == null || count($cbt_setting) > 0) : ?>
                                    <div class="col-12 alert alert-default-warning">
                                        <p class="text-center mb-0">Jadwal atau data peserta belum tersedia. Minta pengawas memeriksa ruang, sesi, dan nomor peserta Anda.</p>
                                    </div>
                                <?php else:
                                    $jamSesi = $cbt_info == null ? '0' : (isset($cbt_info->sesi_id) ? $cbt_info->sesi_id : $cbt_info->id_sesi);
                                    if (isset($cbt_jadwal[date('Y-m-d')]) && count($cbt_jadwal[date('Y-m-d')]) > 0) :
                                        foreach ($cbt_jadwal[date('Y-m-d')] as $key => $jadwal)  :
                                            if (!$this->student_exam_eligibility->isEligible($siswa->id_siswa, $jadwal->id_jadwal)) continue;
                                            $kk = unserialize($jadwal->bank_kelas ?? '');
                                            $arrKelasCbt = [];
                                            foreach ($kk as $k) {
                                                array_push($arrKelasCbt, $k['kelas_id']);
                                            }

                                            $startDay = strtotime($jadwal->tgl_mulai);
                                            $endDay = strtotime($jadwal->tgl_selesai);
                                            $today = strtotime(date('Y-m-d'));

                                            //echo 'skrg='.$today . ' start=' . $startDay . ' end=' . $endDay;

                                            $hariMulai = new DateTime($jadwal->tgl_mulai);
                                            $hariSampai = new DateTime($jadwal->tgl_selesai);

                                            $sesiMulai = new DateTime($sesi[$jamSesi]['mulai']);
                                            $sesiSampai = new DateTime($sesi[$jamSesi]['akhir']);
                                            $now = strtotime(date('H:i'));

                                            $durasi = $elapsed[$jadwal->id_jadwal] ?? null;
                                            $mapelPerJam[$jadwal->tgl_mulai][$jadwal->jam_ke] = $jadwal->nama_mapel;
                                            $jadwal_selesai[$jadwal->tgl_mulai][$jadwal->jam_ke] = $durasi != null
                                                ? $durasi->status == '2'
                                                : false;

                                            if ($durasi != null) {
                                                $selesai = $durasi->selesai != null;
                                                $lanjutkan = $durasi->lama_ujian != null;
                                                $reset = $durasi->reset;
                                                if ($lanjutkan != null && !$selesai) $bg = 'bg-gradient-warning';
                                                elseif ($selesai) $bg = 'bg-gradient-success';
                                                else {
                                                    $bg = 'bg-gradient-danger';
                                                }
                                            } else {
                                                $selesai = false;
                                                $lanjutkan = false;
                                                $reset = 0;
                                                $bg = 'bg-gradient-danger';
                                            }
                                            $jam_ke = $jadwal->jam_ke == '0' ? '1' : $jadwal->jam_ke;
                                            ?>
                                            <div class="jadwal-cbt col-md-6 col-lg-4">
                                                <div class="card border">
                                                    <div class="card-header">
                                                        <div class="card-title">
                                                            <b>Jam ke: <?= $jam_ke ?></b>
                                                        </div>
                                                        <div class="card-tools">
                                                            <b><i class="fa fa-clock-o text-gray mr-1"></i><?= $jadwal->durasi_ujian ?>
                                                                mnt</b>
                                                        </div>
                                                    </div>
                                                    <div class="card-body p-0">
                                                        <div class="small-box <?= $bg ?> mb-0">
                                                            <div class="inner">
                                                                <h6 class="crop-text-1">
                                                                    <b><?= $jadwal->nama_mapel ?></b></h6>
                                                                <h5><?= $jadwal->nama_jenis ?></h5>
                                                            </div>
                                                            <div class="icon">
                                                                <i class="fas fa-book-open"></i>
                                                            </div>
                                                            <hr style="margin-top:0; margin-bottom: 0">
                                                            <?php
                                                            if (!$lanjutkan && $reset == 0 && !$selesai) : ?>
                                                                <?php if ($today < $startDay) : ?>
                                                                    <div id="<?= $jadwal->id_jadwal ?>"
                                                                         class="status small-box-footer p-2"
                                                                         data-tgl="<?= $jadwal->tgl_mulai ?>"
                                                                         data-jamke="<?= $jadwal->jam_ke ?>">
                                                                        <b>BELUM DIMULAI</b>
                                                                    </div>
                                                                <?php elseif ($today > $endDay) : ?>
                                                                    <div id="<?= $jadwal->id_jadwal ?>"
                                                                         class="status small-box-footer p-2"
                                                                         data-tgl="<?= $jadwal->tgl_mulai ?>"
                                                                         data-jamke="<?= $jadwal->jam_ke ?>">
                                                                        <b>SUDAH BERAKHIR</b>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <?php if ($now < strtotime($sesiMulai->format('H:i'))) : ?>
                                                                        <div id="<?= $jadwal->id_jadwal ?>"
                                                                             class="status small-box-footer p-2"
                                                                             data-tgl="<?= $jadwal->tgl_mulai ?>"
                                                                             data-jamke="<?= $jadwal->jam_ke ?>">
                                                                            <b>Sesi belum dimulai</b>
                                                                            <div>Mulai pukul <?= $sesiMulai->format('H:i') ?></div>
                                                                        </div>
                                                                    <?php elseif ($now > strtotime($sesiSampai->format('H:i'))) : ?>
                                                                        <div id="<?= $jadwal->id_jadwal ?>"
                                                                             class="status small-box-footer p-2"
                                                                             data-tgl="<?= $jadwal->tgl_mulai ?>"
                                                                             data-jamke="<?= $jadwal->jam_ke ?>">
                                                                            <b><?= strtoupper($cbt_info->nama_sesi) ?>
                                                                                SUDAH BERAKHIR</b>
                                                                        </div>
                                                                    <?php else : ?>
                                                                        <?php if (isset($jadwal_selesai[$jadwal->tgl_mulai][$jadwal->jam_ke - 1]) && $jadwal_selesai[$jadwal->tgl_mulai][$jadwal->jam_ke - 1] == false) : ?>
                                                                            <div class="small-box-footer p-2">
                                                                                <b>Menunggu ujian sebelumnya</b>
                                                                                <div>Selesaikan <?= html_escape($mapelPerJam[$jadwal->tgl_mulai][$jadwal->jam_ke - 1]) ?> terlebih dahulu.</div>
                                                                            </div>
                                                                        <?php else : ?>
                                                                            <button id="<?= $jadwal->id_jadwal ?>"
                                                                                    onclick="location.href='<?= base_url('siswa/konfirmasi/' . $jadwal->id_jadwal) ?>'"
                                                                                    class="btn btn-block status text-white small-box-footer p-2"
                                                                                    data-tgl="<?= $jadwal->tgl_mulai ?>"
                                                                                    data-jamke="<?= $jadwal->jam_ke ?>">
                                                                                <b>KERJAKAN</b><i
                                                                                        class="fas fa-arrow-circle-right ml-3"></i>
                                                                            </button>
                                                                        <?php endif; endif; endif; ?>
                                                            <?php elseif ($lanjutkan && !$selesai) : ?>
                                                                <button id="<?= $jadwal->id_jadwal ?>"
                                                                        class="btn-block btn status small-box-footer p-2 text-white"
                                                                        onclick="location.href='<?= base_url('siswa/konfirmasi/' . $jadwal->id_jadwal) ?>'"
                                                                        data-tgl="<?= $jadwal->tgl_mulai ?>"
                                                                        data-jamke="<?= $jadwal->jam_ke ?>">
                                                                    <b>LANJUTKAN</b><i
                                                                            class="fas fa-arrow-circle-right ml-3"></i>
                                                                </button>
                                                            <?php else : ?>
                                                                <div id="<?= $jadwal->id_jadwal ?>"
                                                                     class="btn status small-box-footer p-2"
                                                                     data-tgl="<?= $jadwal->tgl_mulai ?>"
                                                                     data-jamke="<?= $jadwal->jam_ke ?>">
                                                                    <b>SUDAH SELESAI</b>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        endforeach;
                                    else: ?>
                                        <div class="col-12 alert alert-default-warning">
                                            <div class="text-center">Tidak ada jadwal penilaian hari ini.</div>
                                        </div>
                                    <?php
                                    endif;
                                endif;
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php endif; ?>

                <?php if (!$studentSlots || array_diff(array_keys($cbt_jadwal), [date('Y-m-d')])) : ?>
                <div class="col-12">
                    <div class="card my-shadow">
                        <div class="card-header">
                            <h5 class="text-center">
                                JADWAL PENILAIAN SEBELUMNYA
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row table-responsive">
                                <table class="table">
                                    <?php
                                    foreach ($cbt_jadwal as $tgl => $jadwals)  :
                                        if ($tgl != date('Y-m-d')) :?>
                                            <tr>
                                                <td colspan="4" class="tgl-ujian text-center bg-secondary"
                                                    data-tgl="<?= $tgl ?>">
                                                    <?= buat_tanggal(date('D, d M Y', strtotime($tgl))) ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="text-center">Jam ke</th>
                                                <th class="text-center align-middle">Mapel</th>
                                                <th class="align-middle d-none d-md-block">Jenis Penilaian</th>
                                                <th class="align-middle">Status</th>
                                            </tr>
                                            <?php
                                            foreach ($jadwals as $key => $jadwal)  :
                                                if (!$this->student_exam_eligibility->isEligible($siswa->id_siswa, $jadwal->id_jadwal)) continue;
                                                $jam_ke = $jadwal->jam_ke == '0' ? '1' : $jadwal->jam_ke;
                                                $kk = unserialize($jadwal->bank_kelas ?? '');
                                                $arrKelasCbt = [];
                                                foreach ($kk as $k) {
                                                    array_push($arrKelasCbt, $k['kelas_id']);
                                                }

                                                $startDay = strtotime($jadwal->tgl_mulai);
                                                $endDay = strtotime($jadwal->tgl_selesai);
                                                $today = strtotime(date('Y-m-d'));

                                                $hariMulai = new DateTime($jadwal->tgl_mulai);
                                                $hariSampai = new DateTime($jadwal->tgl_selesai);

                                                $sesiMulai = new DateTime($sesi[$jamSesi]['mulai']);
                                                $sesiSampai = new DateTime($sesi[$jamSesi]['akhir']);
                                                $now = strtotime(date('H:i'));

                                                $durasi = $elapsed[$jadwal->id_jadwal] ?? null;
                                                $mapelPerJam[$jadwal->tgl_mulai][$jadwal->jam_ke] = $jadwal->nama_mapel;
                                                $jadwal_selesai[$jadwal->tgl_mulai][$jadwal->jam_ke] = $durasi != null
                                                    ? $durasi->status == '2'
                                                    : false;

                                                if ($durasi != null) {
                                                    $selesai = $durasi->selesai != null;
                                                    $lanjutkan = $durasi->lama_ujian != null;
                                                    $reset = $durasi->reset;
                                                    if ($lanjutkan != null && !$selesai) $bg = 'btn-warning';
                                                    elseif ($selesai) $bg = 'btn-success';
                                                    else {
                                                        $bg = 'btn-danger';
                                                    }
                                                } else {
                                                    $selesai = false;
                                                    $lanjutkan = false;
                                                    $reset = 0;
                                                    $bg = 'btn-danger';
                                                }

                                                $status = '';
                                                if (!$lanjutkan && $reset == 0 && !$selesai) {
                                                    if ($today < $startDay) {
                                                        $status = '<button id="' . $jadwal->id_jadwal . '" class="status-table btn btn-disabled ' . $bg . '"'
                                                            . ' data-tgl="' . $jadwal->tgl_mulai . '" data-jamke="' . $jadwal->jam_ke . '">'
                                                            . ' <b>BELUM DIMULAI</b></button>';
                                                    } elseif ($today > $endDay) {
                                                        $status = '<button id="' . $jadwal->id_jadwal . '" class="status-table btn btn-disabled ' . $bg . '"'
                                                            . ' data-tgl="' . $jadwal->tgl_mulai . '" data-jamke="' . $jadwal->jam_ke . '">'
                                                            . ' <b>SUDAH BERAKHIR</b></button>';
                                                    } else {
                                                        if ($now < strtotime($sesiMulai->format('H:i'))) {
                                                            $status = '<button id="' . $jadwal->id_jadwal . '" class="status-table btn btn-disabled ' . $bg . '"'
                                                                . ' data-tgl="' . $jadwal->tgl_mulai . '" data-jamke="' . $jadwal->jam_ke . '">'
                                                                . ' <b>Sesi mulai pukul ' . $sesiMulai->format('H:i') . '</b></button>';
                                                        } elseif ($now > strtotime($sesiSampai->format('H:i'))) {
                                                            $status = '<button id="' . $jadwal->id_jadwal . '" class="status-table btn btn-disabled ' . $bg . '"'
                                                                . ' data-tgl="' . $jadwal->tgl_mulai . '" data-jamke="' . $jadwal->jam_ke . '">'
                                                                . '<b>' . strtoupper($cbt_info->nama_sesi) . ' SUDAH BERAKHIR</b></button>';
                                                        } else {
                                                            if (isset($jadwal_selesai[$jadwal->tgl_mulai][$jadwal->jam_ke - 1]) && $jadwal_selesai[$jadwal->tgl_mulai][$jadwal->jam_ke - 1] == false) {
                                                                $previous = $mapelPerJam[$jadwal->tgl_mulai][$jadwal->jam_ke - 1];
                                                                $status = '<span>Selesaikan ' . html_escape($previous) . ' terlebih dahulu.</span>';
                                                            } else {
                                                                $status = '<button id="' . $jadwal->id_jadwal . '"'
                                                                    . ' onclick="location.href=\'' . base_url() . 'siswa/konfirmasi/' . $jadwal->id_jadwal . '\'"'
                                                                    . ' class="status-table btn ' . $bg . '"'
                                                                    . ' data-tgl="' . $jadwal->tgl_mulai . '" data-jamke="' . $jadwal->jam_ke . '">'
                                                                    . ' <b>KERJAKAN</b></button>';
                                                            }
                                                        }
                                                    }
                                                } elseif ($lanjutkan && !$selesai) {
                                                    $status = '<button id="' . $jadwal->id_jadwal . '" class="status-table btn ' . $bg . '"'
                                                        . ' onclick="location.href=\'' . base_url() . 'siswa/konfirmasi/' . $jadwal->id_jadwal . '\'"'
                                                        . ' data-tgl="' . $jadwal->tgl_mulai . '" data-jamke="' . $jadwal->jam_ke . '">'
                                                        . ' <b>LANJUTKAN</b></button>';
                                                } else {
                                                    $status = '<button id="' . $jadwal->id_jadwal . '" class="status-table btn btn-disabled ' . $bg . '"'
                                                        . ' data-tgl="' . $jadwal->tgl_mulai . '" data-jamke="' . $jadwal->jam_ke . '">'
                                                        . ' <b>SUDAH SELESAI</b></button>';
                                                } ?>
                                                <tr>
                                                    <td class="text-center"><?= $jam_ke ?>
                                                        <br><?= $jadwal->durasi_ujian ?> mnt
                                                    </td>
                                                    <td class="text-center"><?= $jadwal->nama_mapel ?><br>
                                                        <small class="d-block d-md-none"><?= $jadwal->nama_jenis ?></small>
                                                    </td>
                                                    <td class="d-none d-md-block"><?= $jadwal->nama_jenis ?></td>
                                                    <td><?= $status ?></td>
                                                </tr>
                                            <?php
                                            endforeach;
                                        endif;
                                    endforeach; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

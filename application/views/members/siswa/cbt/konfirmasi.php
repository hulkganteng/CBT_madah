<?php
/**
 * Created by IntelliJ IDEA.
 * User: multazam
 * Date: 23/08/20
 * Time: 23:18
 */
?>
<div class="content-wrapper" style="margin-top: -1px;">
    <div class="sticky">
    </div>
    <section class="content overlap p-4">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <?php $this->load->view('members/siswa/templates/top'); ?>
                </div>
            </div>

            <div class="container-fluid h-100">
                <div class="row h-100 justify-content-center">
                    <div class="col-md-8 col-lg-6">
                        <div class="card my-shadow">
                            <div class="card-body">
                                <?php
                                //var_dump($pengawas);
                                if ($support && $valid) : ?>
                                    <h3 class="text-center">KONFIRMASI</h3>
                                    <h5 class="text-center">
                                        <b><?= $bank->kode_jenis . ' | ' . $bank->tahun . ' | ' . $bank->smt ?></b>
                                    </h5>
                                    <br>
                                    <?php
                                    $jk = json_decode(json_encode($bank->bank_kelas));
                                    $jumlahKelas = @unserialize(is_string($jk) ? $jk : '');
                                    if (!is_array($jumlahKelas)) {
                                        $jumlahKelas = [];
                                    }
                                    $kelas = is_array($kelas ?? null) ? $kelas : [];

                                    $kelasbank = '';
                                    $no = 1;
                                    foreach ($jumlahKelas as $j) {
                                        foreach ($kelas as $k) {
                                            if ($j->kelas_id === $k->id_kelas) {
                                                if ($no > 1) {
                                                    $kelasbank .= ', ';
                                                }
                                                $kelasbank .= $k->nama_kelas;
                                                $no++;
                                            }
                                        }
                                    }

                                    ?>
                                    <?= form_open('', array('id' => 'konfir')) ?>
                                    <input type="hidden" name="siswa" value="<?= $siswa->id_siswa ?>">
                                    <input type="hidden" name="jadwal" value="<?= $bank->id_jadwal ?>">
                                    <input type="hidden" name="bank" value="<?= $bank->id_bank ?>">
                                    <ul class="list-group list-group-unbordered">
                                        <li class="list-group-item p-1"> Mata Pelajaran
                                            <span class="float-right"><b><?= $bank->nama_mapel ?></b></span>
                                        </li>
                                        <!--
                                        <li class="list-group-item p-1"> Guru
                                            <span class="float-right"><b><?= $bank->nama_guru ?></b></span>
                                        </li>
                                        -->
                                        <li class="list-group-item p-1"> Kelas
                                            <span class="float-right"><b><?= $kelasbank ?></b></span>
                                        </li>
                                        <li class="list-group-item p-1"> Durasi Waktu
                                            <span class="float-right"><b><?= $bank->durasi_ujian ?> Menit</b></span>
                                        </li>
                                        <!--
									<li class="list-group-item p-1"> Status
										<span class="float-right">
											<b><?= ($bank->status === '0') ? 'Non Aktif' : 'Aktif' ?></b>
										</span>
									</li>
									-->
                                        <li class="list-group-item p-1"> Jumlah Soal
                                            <span class="float-right">
											<b><?= $bank->tampil_pg + $bank->tampil_kompleks + (int) ($bank->tampil_bs ?? 0) ?></b>
										</span>
                                        </li>
                                        <?php if ($bank->token === '1') : ?>
                                            <li class="list-group-item p-1"><span
                                                        class="text-danger"><b>Token</b></span>
                                                <div class="float-right" style="width: 100px">
                                                    <input type='text' id="input-token" class="form-control form-control-sm text-center" name='token'
                                                           placeholder="Token"/>
                                                </div>
                                            </li>
                                        <?php endif; ?>
                                    </ul>

                                    <div class="alert alert-default-info">
                                        <h5>Pengawas Ujian:</h5>
                                        <ul>
                                            <?php
                                            foreach ($pengawas as $pws) :?>
                                                <li><?= $pws->nama_guru ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <br>
                                    <div id="seb-status" class="alert alert-light py-2" role="status">
                                        Memeriksa keamanan browser ujian...
                                    </div>
                                    <span class="float-right" data-toggle="tooltip" title="MULAI">
                                        <button id="load-soal" type="submit" class="btn btn-success" disabled>MULAI</button>
                                    </span>
                                    <?= form_close(); ?>

                                <?php elseif (!$valid) : ?>
                                    <div class="alert alert-default-danger text-center p-2">
                                        <h3><i class="icon fas fa-ban"></i> WARNING..!!</h3>
                                        <div class="text-lg">
                                            Ujian tidak bisa dilanjutkan
                                            <br>
                                            hubungi proktor
                                        </div>
                                    </div>
                                <small>Refresh halaman ini jika sudah diizinkan</small>
                                <?php elseif (!$support): ?>
                                    <div class="alert alert-default-danger text-center p-2">
                                        <h3><i class="icon fas fa-ban"></i> WARNING..!!</h3>
                                        <div class="text-lg">
                                            Browser yang digunakan tidak mendukung
                                            <br>
                                            silahkan gunakan browser lain dengan versi terbaru
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="<?= base_url() ?>/assets/app/js/redirect.js"></script>
<script src="<?= base_url() ?>/assets/app/js/exam-guard.js"></script>
<script>
    var examGuardReady = false;
    var confirmationSubmitting = false;
    ExamGuard.init({scheduleId: <?= (int) $bank->id_jadwal ?>, active: false, startButton: '#load-soal'});
    $(document).on('examguard:status', function (event, result) {
        var box = $('#seb-status');
        examGuardReady = !!result.status;
        if (result.status) {
            var label = result.seb_verified ? 'Safe Exam Browser terverifikasi.' :
                (result.seb_mode === 'OPTIONAL' ? 'SEB opsional; browser belum terverifikasi.' : 'Pemeriksaan browser selesai.');
            box.removeClass('alert-light alert-danger').addClass(result.seb_verified ? 'alert-success' : 'alert-warning').text(label);
        } else {
            box.removeClass('alert-light alert-success').addClass('alert-danger').text(result.message || 'Gunakan Safe Exam Browser yang dikonfigurasi untuk ujian ini.');
        }
    });

    $('#konfir').submit(function (e) {
        e.stopPropagation();
        e.preventDefault();
        if (confirmationSubmitting || !examGuardReady) return;
        confirmationSubmitting = true;
        $('#load-soal').prop('disabled', true).attr('aria-busy', 'true');

        swal.fire({
            title: "Membuka Soal",
            text: "Silahkan tunggu....",
            button: false,
            closeOnClickOutside: false,
            closeOnEsc: false,
            allowEscapeKey: false,
            allowOutsideClick: false,
            onOpen: () => {
                swal.showLoading();
            }
        });
        console.log($(this).serialize());
        var jadwal = $(this).find('input[name="jadwal"]').val();
        $.ajax({
            type: 'POST',
            url: base_url + 'siswa/validasisiswa',
            data: $(this).serialize(),
            success: function (data) {
                console.log(data);
                // jika menggunakan token, cek token
                if (data.token === true) {
                    // token ok
                    // cek browser dulu
                    if (data.support === false) {
                        // browser tidak support
                        // siswa stop disini
                        swal.fire({
                            "title": "Error",
                            "html": "Browser tidak mendukung!<br>Gunakan browser Chrome, atau Mozilla<br>005",
                            "icon": "error"
                        });
                    } else {
                        // browser OK
                        // cek izin ujian
                        if (data.izinkan === true) {
                            // diizinkan
                            // cek sisa waktu
                            if (data.ada_waktu === true) {
                                // masih ada waktu
                                // cek apakah ada soal?
                                if (data.jml_soal > 0) {
                                    // ada soal
                                    // siswa masuk halaman ujian
                                    window.location.href = base_url + 'siswa/penilaian/' + jadwal;
                                } else {
                                    // soal belum dibuat
                                    swal.fire({
                                        "title": "Error",
                                        "html": "Tidak ada soal ujian<br>Hubungi proktor<br>004",
                                        "icon": "error"
                                    });
                                }
                            } else {
                                // siswa logout ditengah ujian dan tidak melanjutkan sampai waktu ujian habis
                                // admin harus reset waktu
                                swal.fire({
                                    "title": "Error",
                                    "html": data.warn.msg + "<br>Hubungi proktor<br>003",
                                    "icon": "error"
                                });
                            }
                        } else {
                            // ditengah ujian, siswa ganti hape/komputer
                            // siswa tidak diizinkan ujian
                            // admin perlu reset izin
                            swal.fire({
                                "title": "Error",
                                "html": "Anda sedang mengerjakan ujian di perangkat lain<br>Hubungi proktor<br>002",
                                "icon": "error"
                            });
                        }
                    }
                } else {
                    // token salah, atau token tidak dibuat oleh admin
                    swal.fire({
                        "title": "Error",
                        "html": "TOKEN salah!<br>Hubungi proktor<br>001",
                        "icon": "error"
                    });
                }
            }, error: function (xhr, error, status) {
                swal.fire({
                    "title": "Error",
                    "html": "Coba kembali ke beranda, lalu ulangi lagi<br>006",
                    "icon": "error"
                });
                console.log(xhr.responseText);
            }, complete: function () {
                confirmationSubmitting = false;
                $('#load-soal').prop('disabled', !examGuardReady).attr('aria-busy', 'false');
            }
        });
    });

    console.log('mnt', getMinutes('2023-01-30 11:30:30'));

    function getMinutes(d) {
        var startTime = new Date(d);
        var endTime = new Date();
        endTime.setHours(endTime.getHours() - startTime.getHours());
        endTime.setMinutes(endTime.getMinutes() - startTime.getMinutes());
        endTime.setSeconds(endTime.getSeconds() - startTime.getSeconds());

        return {h: endTime.getHours(), m: endTime.getMinutes(), s: endTime.getSeconds()}
    }

</script>

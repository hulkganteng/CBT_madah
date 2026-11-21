<?php
/**
 * Created by IntelliJ IDEA.
 * User: multazam
 * Date: 14/07/20
 * Time: 17:46
 */

$savedClasses = @unserialize($bank->bank_kelas ?? '', ['allowed_classes' => ['stdClass']]);
$selectedClassIds = array_column((array) $savedClasses, 'kelas_id');
?>

<div class="content-wrapper bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-6">
                    <h1><?= $judul ?></h1>
                </div>
                <div class="col-6">
                    <button onclick="window.history.back();" type="button" class="btn btn-sm btn-danger float-right">
                        <i class="fas fa-arrow-circle-left"></i><span
                                class="d-none d-sm-inline-block ml-1">Kembali</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?= form_open('create', array('id' => 'create')) ?>
            <div class="card my-shadow">
                <div class="card-header">
                    <div class="card-title">
                        <h6><?= $subjudul . ' - ' . $bank->bank_kode ?></h6>
                    </div>
                    <div class="card-tools">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus mr-1"></i>Simpan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        echo form_hidden('id_bank', $bank->id_bank) ?>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="">Kode Bank Soal</label>
                                <input type="text" class="form-control form-control-sm" name="kode" maxlength="20"
                                       placeholder="Masukan Kode Bank Soal" value="<?= $bank->bank_kode ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Mata Pelajaran</label>
                                <?php echo form_dropdown('mapel', $mapel, $bank->bank_mapel_id, 'id="select-mapel" class="select2 form-control form-control-sm" required'); ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Guru Pengampu</label>
                                <?php echo form_dropdown('guru', $gurus, $bank->bank_guru_id, 'id="select-guru" class="select2 form-control form-control-sm" required'); ?>
                            </div>
                        </div>
                        <div class="col-md-1 col-3">
                            <div class="form-group">
                                <label>Level</label><br>
                                <?php
                                if ($setting->jenjang == "1") {
                                    for ($i = 1; $i < 7; $i++) {
                                        $arrLevel[$i] = $i;
                                    }
                                } else if ($setting->jenjang == "2") {
                                    for ($j = 7; $j < 10; $j++) {
                                        $arrLevel[$j] = $j;
                                    }
                                } else {
                                    for ($k = 10; $k < 13; $k++) {
                                        $arrLevel[$k] = $k;
                                    }
                                }

                                echo form_dropdown('level', $arrLevel, $bank->bank_level, 'id="select-level" class="form-control form-control-sm" data-placeholder="Pilih Level Kelas" required'); ?>
                            </div>
                        </div>
                        <div class="col-md-3 col-9">
                            <div class="form-group">
                                <label>Pilih Kelas</label><br>
                                <select name="kelas[]" id="select-kelas"
                                        class="select2 form-control form-control-sm" multiple="multiple"
                                        required=""></select>
                            </div>
                        </div>
                        <?php
                        $peminatan = [0 => 'Semua Peminatan'];
                        foreach ($this->db->order_by('nama_jurusan')->get_where('master_jurusan', ['status' => 1])->result() as $major) {
                            $peminatan[$major->id_jurusan] = $major->nama_jurusan;
                        }
                        ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Peminatan Peserta</label>
                                <?= form_dropdown('peminatan', $peminatan, $bank->bank_jurusan_id ?? 0, 'id="select-peminatan" class="select2 form-control form-control-sm"') ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Program MAK</label>
                                <?= form_dropdown('program_mak', ['' => 'Semua program MAK', 'TAHFIDZ' => 'Tahfidz', 'KITAB' => 'Kitab'], $bank->bank_program_mak ?? '', 'id="select-program-mak" class="select2 form-control form-control-sm"') ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-7">
                            <div class="card alert-default-primary">
                                <div class="card-body p-2">
                                    <span><b>Soal Pilihan Ganda</b></span>
                                    <div class="row">
                                        <div class="col-4">
                                            <div class="form-group">
                                                <label>Jml. Soal</label>
                                                <input id="jml-pg" type='number' name='tampil_pg'
                                                       class='form-control form-control-sm'
                                                       value="<?= $bank->tampil_pg ?>" required/>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="form-group">
                                                <label>Bobot %</label>
                                                <input id="bobot-pg" type='number' name='bobot_pg'
                                                       class='form-control form-control-sm'
                                                       value="<?= $bank->bobot_pg ?>"
                                                       required/>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="form-group">
                                                <label>Opsi Jawaban</label>
                                                <?php
                                                $opsis [''] = 'Pilih Jml Opsi :';
                                                $opsis ['3'] = '3 (A, B, C)';
                                                $opsis ['4'] = '4 (A, B, C, D)';
                                                $opsis ['5'] = '5 (A, B, C, D, E)';

                                                echo form_dropdown('opsi', $opsis, $bank->opsi, 'class="form-control form-control-sm" required'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-5">
                            <div class="card alert-default-warning">
                                <div class="card-body p-2">
                                    <span><b>Soal Ganda Kompleks</b></span>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label>Jml. Soal</label>
                                                <input id="jml-pg2" type='number' name='tampil_kompleks'
                                                       class='form-control form-control-sm'
                                                       value="<?= $bank->tampil_kompleks ?>" required/>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label>Bobot %</label>
                                                <input id="bobot-pg2" type='number' name='bobot_kompleks'
                                                       class='form-control form-control-sm'
                                                       value="<?= $bank->bobot_kompleks ?>"
                                                       required/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PRD FR-08: soal Benar/Salah (satu pernyataan, kunci B atau S). -->
                    <div class="row">
                        <div class="col-12 col-md-5">
                            <div class="card alert-default-success">
                                <div class="card-body p-2">
                                    <span><b>Soal Benar / Salah</b></span>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label>Jml. Soal</label>
                                                <input id="jml-bs" type='number' name='tampil_bs'
                                                       class='form-control form-control-sm'
                                                       value="<?= (int) ($bank->tampil_bs ?? 0) ?>" required/>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label>Bobot %</label>
                                                <input id="bobot-bs" type='number' name='bobot_bs'
                                                       class='form-control form-control-sm'
                                                       value="<?= (int) ($bank->bobot_bs ?? 0) ?>" required/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 col-6">
                            <div class="card">
                                <div class="card-body">
                                    <table class="w-100">
                                        <tr class="text-center">
                                            <th>Total Soal</th>
                                            <th>Total Bobot</th>
                                        </tr>
                                        <tr class="text-center text-bold text-lg">
                                            <td id="total-soal">0</td>
                                            <td id="total-bobot">0</td>
                                        </tr>
                                    </table>
                                    <small></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="form-group">
                                <label>Mapel Agama</label>
                                <?php
                                echo form_dropdown('soal_agama', $mapel_agama, $bank->soal_agama, 'class="form-control form-control-sm" required'); ?>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="form-group">
                                <label>Status Bank Soal</label>
                                <?php
                                $aktifs [''] = 'Pilih Status :';
                                $aktifs ['1'] = 'Aktif';
                                $aktifs ['0'] = 'Non Aktif';

                                echo form_dropdown('status', $aktifs, $bank->status, 'class="form-control form-control-sm" required'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?= form_close() ?>
        </div>
    </section>
</div>

<script>
    const isAdmin = <?= json_encode((bool) $this->ion_auth->is_admin()) ?>;
    let selectedClassIds = <?= json_encode($selectedClassIds) ?>.map(String);

    $(document).ready(function () {
        ajaxcsrf();
        var selLevel = $('#select-level');
        var selKelas = $('#select-kelas');
        var selMapel = $('#select-mapel');
        var selGuru = $('#select-guru');

        selMapel.select2();
        selKelas.select2();
        selGuru.select2();

        const peminatan = $('#select-peminatan');
        const programMak = $('#select-program-mak');
        function toggleProgramMak() {
            const kode = peminatan.find(':selected').text().trim().toUpperCase();
            const isMak = kode === 'MAK';
            programMak.prop('disabled', !isMak);
            if (!isMak) programMak.val('').trigger('change');
        }
        peminatan.on('change', toggleProgramMak);
        toggleProgramMak();

        let teacherRequest;
        let classRequest;

        if (isAdmin) {
            getGuruMapel();
        } else {
            getKelasLevel();
        }

        $('#create').submit('click', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            const $form = $(this);
            if ($form.data('saving')) return;
            const payload = $form.serialize();
            $form.data('saving', true).find(':submit').prop('disabled', true);
            swal.fire({
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
            $.ajax({
                url: base_url + "cbtbanksoal/saveBank",
                type: "POST",
                dataType: "JSON",
                data: payload,
                success: function (data) {
                    if (!data.status) {
                        swal.fire('Gagal', data.message || 'Bank soal gagal disimpan.', 'error');
                        return;
                    }
                    swal.fire({
                        title: "Sukses",
                        html: 'Bank soal berhasil disimpan',
                        icon: "success",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "OK"
                    }).then(result => {
                        if (result.value) {
                            window.history.back();
                        }
                    });
                }, error: function (xhr, status, error) {
                    console.log("error", xhr.responseText);
                    let message = 'Bank soal gagal disimpan. Periksa data dan coba lagi.';
                    try {
                        const err = JSON.parse(xhr.responseText);
                        message = err.message || err.error || err.Message || message;
                    } catch (e) {
                        console.error('Respons error bukan JSON yang valid', e);
                    }
                    swal.fire({
                        title: "Error",
                        text: message,
                        icon: "error"
                    });
                }, complete: function () {
                    $form.data('saving', false).find(':submit').prop('disabled', false);
                }
            });
        });

        function getGuruMapel() {
            const selectedTeacher = selGuru.val();
            if (teacherRequest) teacherRequest.abort();
            if (classRequest) classRequest.abort();
            selGuru.empty().trigger('change.select2');
            selKelas.empty().trigger('change');

            teacherRequest = $.ajax({
                url: base_url + "cbtbanksoal/getgurumapel",
                data: {id_mapel: selMapel.val()},
                dataType: 'json',
                success: function (teachers) {
                    $.each(teachers, function (id, name) {
                        selGuru.append(new Option(name, id));
                    });
                    if (Object.prototype.hasOwnProperty.call(teachers, selectedTeacher)) {
                        selGuru.val(selectedTeacher);
                    }
                    selGuru.trigger('change');
                },
                error: function (xhr, status) {
                    if (status !== 'abort') {
                        swal.fire('Gagal', 'Daftar guru pengampu gagal dimuat.', 'error');
                    }
                }
            });
        }

        function getKelasLevel() {
            if (classRequest) classRequest.abort();
            selKelas.empty().trigger('change');
            const level = selLevel.val();
            const mapel = selMapel.val();
            const teacher = selGuru.val();
            if (!level || !mapel || !teacher) return;

            classRequest = $.ajax({
                url: base_url + "cbtbanksoal/getkelaslevel",
                data: {level: level, id_guru: teacher, mapel: mapel},
                dataType: 'json',
                success: function (data) {
                    data.kelas.forEach(function (rombel) {
                        const selected = selectedClassIds.includes(String(rombel.id_kelas));
                        selKelas.append(new Option(rombel.nama_kelas, rombel.id_kelas, false, selected));
                    });
                    selectedClassIds = selKelas.val() || [];
                    selKelas.trigger('change');
                },
                error: function (xhr, status) {
                    if (status !== 'abort') {
                        swal.fire('Gagal', 'Daftar rombel gagal dimuat.', 'error');
                    }
                }
            });
        }

        selKelas.on('select2:select select2:unselect', function () {
            selectedClassIds = selKelas.val() || [];
        });
        selGuru.add(selLevel).on('change', getKelasLevel);
        selMapel.on('change', isAdmin ? getGuruMapel : getKelasLevel);

        var valBobotPg = $('#bobot-pg');
        var valBobotPg2 = $('#bobot-pg2');
        var valBobotBs = $('#bobot-bs');
        var totalBobot = $('#total-bobot');
        onChangeValueBobot();

        var valSoalPg = $('#jml-pg');
        var valSoalPg2 = $('#jml-pg2');
        var valSoalBs = $('#jml-bs');
        var totalSoal = $('#total-soal');
        onChangeValueJumlah();

        function onChangeValueBobot() {
            const bobotpg = valBobotPg.val() === "" ? 0 : parseInt(valBobotPg.val());
            const bobotpp2 = valBobotPg2.val() === "" ? 0 : parseInt(valBobotPg2.val());
            const bobotbs = valBobotBs.val() === "" ? 0 : parseInt(valBobotBs.val());

            totalBobot.text((bobotpg + bobotpp2 + bobotbs) + '');
        }

        function onChangeValueJumlah() {
            const jmlpg = valSoalPg.val() === "" ? 0 : parseInt(valSoalPg.val());
            const jmlpp2 = valSoalPg2.val() === "" ? 0 : parseInt(valSoalPg2.val());
            const jmlbs = valSoalBs.val() === "" ? 0 : parseInt(valSoalBs.val());

            totalSoal.text((jmlpg + jmlpp2 + jmlbs) + '');
        }

        valBobotPg.on('change keyup', function () {
            onChangeValueBobot();
        });
        valBobotPg2.on('change keyup', function () {
            onChangeValueBobot();
        });
        valBobotBs.on('change keyup', function () {
            onChangeValueBobot();
        });

        valSoalPg.on('change keyup', function () {
            onChangeValueJumlah();
        });
        valSoalPg2.on('change keyup', function () {
            onChangeValueJumlah();
        });
        valSoalBs.on('change keyup', function () {
            onChangeValueJumlah();
        });
    });

</script>

<?php
/**
 * Halaman mengerjakan simulasi/ujicoba siswa
 * Umpan balik: hanya nilai akhir (tanpa kunci jawaban).
 */
?>
<div class="content-wrapper" style="margin-top: -1px;">
    <div class="sticky"></div>
    <section class="content overlap pt-4">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <div class="card my-shadow">
                        <div class="card-header p-3">
                            <div class="card-title">
                                <b>Simulasi: <?= $bank->nama_mapel ?? $bank->bank_nama ?></b>
                            </div>
                            <div class="card-tools">
                                <span class="badge badge-primary">Soal <b id="nomor-soal"></b> dari <b
                                            id="total-soal"></b></span>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div id="konten-soal" class="mb-3"></div>
                            <hr>
                            <div id="konten-jawaban"></div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between bd-highlight">
                                <button class="btn btn-primary btn-oval-sm" id="prev" onclick="prevSoal()">
                                    <i class="fa fa-arrow-circle-left"></i>
                                    <span class="ml-2 d-none d-md-inline-block"><b>Sebelumnya</b></span>
                                </button>
                                <button class="btn btn-success btn-oval-sm" id="next" onclick="nextSoal()">
                                    <span class="mr-2 d-none d-md-inline-block" id="text-next"><b>Berikutnya</b></span>
                                    <i class="fa fa-arrow-circle-right"></i>
                                </button>
                            </div>
                        </div>
                        <div class="overlay d-none" id="loading">
                            <div class="spinner-grow"></div>
                            <div class="pl-3">MEMUAT SOAL</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?= form_open('', array('id' => 'up')) ?>
<input type="hidden" name="bank" value="<?= $bank->id_bank ?>">
<?= form_close() ?>

<script>
    var idBank = '<?= $bank->id_bank ?>';
    var nomor = 0;
    var total = 0;
    var idSoalAktif = null;
    var answers = {};

    $(document).ready(function () {
        ajaxcsrf();
        loadSoalNomor(1);
    });

    function loadSoalNomor(n) {
        if (n < 1) return;
        $('#loading').removeClass('d-none');
        $.ajax({
            type: 'POST',
            url: base_url + 'ujicoba/loadsoal',
            data: $('#up').serialize() + '&nomor=' + n,
            success: function (data) {
                $('#loading').addClass('d-none');
                if (data.error) {
                    showDangerToast('Soal tidak tersedia.');
                    return;
                }
                setKonten(data);
            },
            error: function (xhr) {
                $('#loading').addClass('d-none');
                showDangerToast('ERROR!');
                console.log(xhr.responseText);
            }
        });
    }

    function setKonten(data) {
        nomor = parseInt(data.nomor);
        total = parseInt(data.total);
        idSoalAktif = data.soal_id;

        $('#nomor-soal').text(nomor);
        $('#total-soal').text(total);
        $('#konten-soal').html(data.soal);
        $('#konten-jawaban').html(data.konten_jawaban);

        if (nomor === 1) {
            $('#prev').attr('disabled', 'disabled');
        } else {
            $('#prev').removeAttr('disabled');
        }

        if (nomor === total) {
            $('#text-next').html('<b>Selesai</b>');
            $('#next').addClass('btn-success');
        } else {
            $('#text-next').html('<b>Berikutnya</b>');
            $('#next').removeClass('btn-success');
        }

        restoreJawaban(data.soal_id);
        bindJawaban(data.soal_id);
    }

    function restoreJawaban(soalId) {
        var val = answers[soalId];
        if (val === undefined || val === null) return;

        if ($.isArray(val)) {
            $('#konten-jawaban input[type="checkbox"]').each(function () {
                if (val.indexOf($(this).val()) > -1) {
                    $(this).prop('checked', true);
                }
            });
        } else if (typeof val === 'string') {
            var radio = $('#konten-jawaban input[type="radio"][value="' + val + '"]');
            if (radio.length) {
                radio.prop('checked', true);
            }
            $('#jawaban-essai').val(val);
        }
    }

    function bindJawaban(soalId) {
        $('#konten-jawaban input[type="radio"]').on('change', function () {
            answers[soalId] = $(this).val();
        });

        $('#konten-jawaban input[type="checkbox"]').on('change', function () {
            var selected = [];
            $('#konten-jawaban input[type="checkbox"]:checked').each(function () {
                selected.push($(this).val());
            });
            answers[soalId] = selected;
        });

        $('#jawaban-essai').on('input change', function () {
            answers[soalId] = $(this).val();
        });
    }

    function nextSoal() {
        if (nomor < total) {
            loadSoalNomor(nomor + 1);
        } else {
            selesai();
        }
    }

    function prevSoal() {
        loadSoalNomor(nomor - 1);
    }

    function selesai() {
        swal.fire({
            title: 'Selesai Latihan?',
            text: 'Pastikan semua jawaban sudah diisi.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Selesai!'
        }).then(result => {
            if (result.value) {
                kirimSelesai();
            }
        });
    }

    function kirimSelesai() {
        $.ajax({
            url: base_url + 'ujicoba/selesai',
            method: 'POST',
            data: $('#up').serialize() + '&answers=' + JSON.stringify(answers),
            success: function (respon) {
                if (respon.status) {
                    swal.fire({
                        title: 'Nilai Akhir',
                        html: '<h3 class="mb-3">' + respon.nilai + '</h3>' +
                            '<p>Benar: <b>' + respon.benar + '</b> &nbsp;|&nbsp; Salah: <b>' + respon.salah +
                            '</b><br>Total dinilai: <b>' + respon.total + '</b></p>',
                        icon: 'info',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Selesai'
                    }).then(() => {
                        window.location.href = base_url + 'ujicoba';
                    });
                } else {
                    showDangerToast('Gagal menghitung nilai.');
                }
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                showDangerToast('ERROR!');
            }
        });
    }
</script>

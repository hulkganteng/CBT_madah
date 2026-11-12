<?php
/**
 * Created by IntelliJ IDEA.
 * User: multazam
 * Date: 23/08/20
 * Time: 23:18
 */
?>
<style>
    body.cbt-exam-mode { background: #f3f6fb; }
    body.cbt-exam-mode .main-sidebar,
    body.cbt-exam-mode .main-header,
    body.cbt-exam-mode .content-header,
    body.cbt-exam-mode .main-footer,
    body.cbt-exam-mode .breadcrumb { display: none !important; }
    body.cbt-exam-mode .content-wrapper { margin-left: 0 !important; margin-top: 0 !important; min-height: 100vh; }
    .cbt-exam-shell { color: #233044; font-family: Arial, sans-serif; }
    .cbt-exam-header { position: sticky; top: 0; z-index: 20; background: #fff; border-bottom: 1px solid rgba(35,48,68,.1); }
    .cbt-exam-header-inner { max-width: 1440px; margin: 0 auto; padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; gap: 20px; }
    .cbt-brand, .cbt-student, .cbt-timer { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .cbt-brand img { width: 42px; height: 42px; object-fit: contain; }
    .cbt-brand strong { display: block; font-size: 18px; line-height: 1.2; color: #183b67; }
    .cbt-brand small, .cbt-student small, .cbt-subject small { color: #68788d; font-size: 12px; }
    .cbt-subject { flex: 1; text-align: center; min-width: 0; }
    .cbt-subject strong { display: block; font-size: 16px; line-height: 1.3; }
    .cbt-student { justify-content: flex-end; }
    .cbt-student div { text-align: right; line-height: 1.3; }
    .cbt-student img { width: 34px; height: 34px; object-fit: contain; }
    .cbt-timer { padding: 8px 12px; border: 1px solid #e4a11b; border-radius: 8px; color: #8b5b00; background: #fffaf0; white-space: nowrap; }
    .cbt-timer #timer { font-size: 20px; font-weight: 600; font-variant-numeric: tabular-nums; }
    .cbt-exam-main { max-width: 1440px; margin: 0 auto; padding: 20px 24px 28px; }
    .exam-layout { display: grid; grid-template-columns: minmax(0, 1fr) 296px; gap: 20px; align-items: start; }
    .exam-main-card, .exam-sidebar-card { background: #fff; border: 1px solid rgba(35,48,68,.1); border-radius: 12px; box-shadow: 0 4px 12px rgba(35,48,68,.06); }
    .exam-main-card .card-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; border-bottom: 1px solid rgba(35,48,68,.08); }
    .exam-main-card .card-title { margin: 0; font-size: 15px; font-weight: 600; }
    .exam-main-card .card-body { padding: 20px; }
    .exam-main-card .konten-soal-jawab { border: 0 !important; }
    #konten-stimulus { font-size: 16px; line-height: 1.65; }
    #konten-soal { font-size: 18px; line-height: 1.65; }
    #konten-jawaban { font-size: 16px; }
    .container-jawaban.exam-option-card { display: block; position: relative; min-height: 52px; margin-bottom: 10px; padding: 14px 16px 14px 58px; border: 1px solid rgba(35,48,68,.14); border-radius: 9px; background: #fff; cursor: pointer; line-height: 1.5; transition: border-color .15s ease, background-color .15s ease; }
    .container-jawaban.exam-option-card:hover { border-color: #3779bd; background: #f7fbff; }
    .container-jawaban.exam-option-card input:checked ~ .checkmark { background: #3779bd; border-color: #3779bd; color: #fff; }
    .container-jawaban.exam-option-card:has(input:checked) { border-color: #3779bd; background: #eef6ff; }
    .exam-option-card .checkmark { left: 16px; top: 50%; transform: translateY(-50%); width: 30px; height: 30px; line-height: 28px; border: 1px solid #b9c8d9; border-radius: 50%; background: #fff; }
    #konten-jawaban .custom-control { min-height: 52px; margin-bottom: 10px; padding: 14px 16px 14px 42px; border: 1px solid rgba(35,48,68,.14); border-radius: 9px; }
    #konten-jawaban .custom-control:has(input:checked) { border-color: #3779bd; background: #eef6ff; }
    #konten-jawaban .custom-control-label { line-height: 1.5; cursor: pointer; }
    .exam-sidebar { position: sticky; top: 84px; }
    .exam-sidebar-card { padding: 18px; }
    .exam-sidebar-title { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; font-size: 16px; font-weight: 600; }
    #exam-navigator-content { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 8px; }
    #exam-navigator-content > * { margin: 0 !important; }
    #exam-navigator-content .grid-nomor-pg { display: grid !important; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 8px; width: 100%; }
    #exam-navigator-content .grid-nomor-pg > * { margin: 0 !important; }
    #exam-navigator-content .btn { position: relative; width: 100%; min-width: 44px; min-height: 44px; padding: 7px 4px; border-radius: 7px; font-size: 14px; }
    #konten-modal .btn { min-width: 44px; min-height: 44px; }
    .exam-legend { display: grid; gap: 8px; margin-top: 18px; padding-top: 14px; border-top: 1px solid rgba(35,48,68,.1); color: #68788d; font-size: 13px; }
    .exam-legend span { display: flex; align-items: center; gap: 8px; }
    .exam-legend i { width: 12px; height: 12px; display: inline-block; border-radius: 50%; border: 1px solid #b9c8d9; background: #fff; }
    .exam-legend .answered { background: #28a745; border-color: #28a745; }
    .exam-legend .flagged { background: #f6c343; border-color: #f6c343; }
    .exam-footer { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 20px; border-top: 1px solid rgba(35,48,68,.08); }
    #save-state { color: #68788d; font-size: 13px; }
    #save-state.is-saved { color: #218739; } #save-state.is-error { color: #b42318; }
    @media (max-width: 1023px) {
        .cbt-exam-header-inner { padding: 12px 16px; flex-wrap: wrap; gap: 10px; }
        .cbt-subject { order: 3; flex-basis: 100%; text-align: left; }
        .cbt-student { margin-left: auto; }
        .cbt-exam-main { padding: 14px 12px 24px; }
        .exam-layout { grid-template-columns: 1fr; }
        .exam-sidebar { position: static; }
        .exam-sidebar-card { padding: 14px; }
    }
    @media (max-width: 575px) {
        .cbt-student div { display: none; }
        .cbt-brand strong { font-size: 16px; }
        .cbt-timer { padding: 7px 9px; }
        .cbt-timer #timer { font-size: 17px; }
        .exam-main-card .card-header { align-items: flex-start; flex-direction: column; }
        .exam-main-card .card-tools { width: 100%; display: flex; flex-wrap: wrap; gap: 6px; }
        .exam-main-card .card-tools > button { margin: 0 !important; }
        .exam-footer { padding: 12px; }
        .exam-footer .btn { min-height: 44px; }
    }
</style>
<div class="content-wrapper cbt-exam-shell" style="margin-top: -1px;">
    <script>document.body.classList.add('cbt-exam-mode');</script>
    <section class="cbt-exam-header" aria-label="Informasi ujian">
        <div class="cbt-exam-header-inner">
            <div class="cbt-brand">
                <img src="<?= base_url() ?>/assets/img/garuda_circle.png" alt="GarudaCBT">
                <div><strong>GarudaCBT</strong><small>Ujian berbasis komputer</small></div>
            </div>
            <div class="cbt-subject">
                <strong><?= html_escape(isset($jadwal->nama_mapel) ? $jadwal->nama_mapel : 'Ujian CBT') ?></strong>
                <small><?= html_escape(isset($jadwal->nama_bank) ? $jadwal->nama_bank : 'Sesi ujian') ?></small>
            </div>
            <div class="cbt-student">
                <div><strong><?= html_escape($siswa->nama) ?></strong><small><?= html_escape($siswa->nis) ?> · <?= html_escape($siswa->nama_kelas) ?></small></div>
                <img src="<?= base_url() ?>/assets/app/img/ic_graduate.png" alt="Peserta">
            </div>
            <div class="cbt-timer" aria-live="polite"><span class="mr-2"><b>Sisa waktu</b></span><span id="timer">00:00:00</span></div>
        </div>
    </section>
    <main class="cbt-exam-main" style="-moz-user-select: none; -webkit-user-select: none; -ms-user-select:none; user-select:none;-o-user-select:none;" unselectable="on">
        <div class="exam-layout">
            <div class="exam-main-card card my-shadow" id="exam-card" aria-busy="true">
                <div class="card-header p-4">
                    <div class="card-title">Soal <span id="nomor-soal" class="btn bg-primary no-hover ml-2 text-lg"></span></div>
                    <div class="card-tools">
                        <button type="button" data-toggle="modal" data-target="#daftarModal" class="btn btn-primary btn-oval-sm"><span class="mr-2"><b>Daftar Soal</b></span><i class="fa fa-th"></i></button>
                        <button type="button" class="btn btn-outline-warning btn-oval-sm" id="btn-ragu" title="Tandai ragu-ragu" aria-pressed="false"><span class="mr-2"><b>Ragu-ragu</b></span><i class="fa fa-flag"></i></button>
                    </div>
                </div>
                        <div class="card-body p-3">
                            <div class="resize-text mb-3">
                                <button type="button" class="btn btn-outline-primary btn-oval-sm no-hover" id="minus"
                                        aria-label="Perkecil teks soal"><i
                                             class="fa fa-minus"></i></button>
                                <button type="button" class="btn btn-outline-primary btn-oval-sm no-hover" id="plus"
                                        aria-label="Perbesar teks soal"><i
                                             class="fa fa-plus"></i></button>
                            </div>
                            <?php
                            //echo '<pre>';
                            //var_dump($konten_opsi);
                            //echo '<br>';
                            //echo '<br>';
                            //var_dump($soal);
                            //echo '</pre>';
                            ?>
                            <div style="border: 1px solid; border-color: #D3D3D3">
                                <div class="konten-soal-jawab">
                                    <div class="row p-2 mb-4 ml-1">
                                        <div id="konten-stimulus" class="col-12 mb-2"></div>
                                        <div id="konten-soal" class="table-responsive"></div>
                                    </div>
                                    <?= form_open('jawab', array('id' => 'jawab')) ?>
                                    <input type="hidden" name="siswa" value="<?= $siswa->id_siswa ?>">
                                    <input type="hidden" name="jadwal" value="<?= $jadwal->id_jadwal ?>">
                                    <input type="hidden" name="bank" value="<?= $jadwal->id_bank ?>">
                                    <div class="row p-3">
                                        <div id="konten-jawaban" class="col-12">
                                        </div>
                                    </div>
                                    <?= form_close() ?>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer exam-footer">
                            <span id="save-state" aria-live="polite">Siap mengerjakan</span>
                            <div class="d-flex justify-content-between bd-highlight flex-grow-1">
                                <div class="bd-highlight">
                                    <button type="button" class="btn btn-primary btn-oval-sm" id="prev" onclick="prevSoal()">
                                        <i class="fa fa-arrow-circle-left"></i>
                                        <span class="ml-2 d-none d-md-inline-block"><b>Soal Sebelumnya</b></span>
                                    </button>
                                </div>
                                <div class="bd-highlight">
                                    <button type="button" class="btn btn-oval-sm btn-danger btn-disabled d-none" id="timer-selesai" disabled></button>
                                    <button type="button" class="btn btn-oval-sm" id="next" onclick="nextSoal()">
                                        <span id="text-next" class="mr-2 d-none d-md-inline-block"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="overlay" id="loading">
                            <div class="spinner-grow"></div>
                            <div class="pl-3">Memuat soal...</div>
                        </div>
                    </div>
            </div>
            <aside class="exam-sidebar" aria-label="Navigasi soal">
                <div class="exam-sidebar-card">
                    <div class="exam-sidebar-title"><span>Daftar soal</span><small class="text-muted">Klik nomor</small></div>
                    <div id="exam-navigator-content" aria-live="polite"></div>
                    <div class="exam-legend" aria-label="Keterangan status soal">
                        <span><i class="answered"></i> Terjawab</span>
                        <span><i></i> Belum dijawab</span>
                        <span><i class="flagged"></i> Ragu-ragu</span>
                    </div>
                </div>
            </aside>
        </div>
    </main>
</div>

<div class="modal fade" id="daftarModal" tabindex="-1" role="dialog" aria-labelledby="daftarLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="daftarLabel">Daftar Nomor Soal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid" id="konten-modal">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">TUTUP</button>
            </div>
        </div>
    </div>
</div>

<?= form_open('', array('id' => 'up')) ?>
<input type="hidden" name="siswa" value="<?= $siswa->id_siswa ?>">
<input type="hidden" name="jadwal" value="<?= $jadwal->id_jadwal ?>">
<input type="hidden" name="bank" value="<?= $jadwal->id_bank ?>">
<?= form_close() ?>

<script src="<?= base_url() ?>/assets/app/js/redirect.js"></script>
<script src="<?= base_url() ?>/assets/app/js/linker-list.js"></script>
<script src="<?= base_url() ?>/assets/plugins/element-queries/ElementQueries.js"></script>
<script src="<?= base_url() ?>/assets/plugins/element-queries/ResizeSensor.js"></script>

<script>
    window.CBT_CSRF = {
        name: <?= json_encode($this->security->get_csrf_token_name()) ?>,
        hash: <?= json_encode($this->security->get_csrf_hash()) ?>
    };
</script>
<script src="<?= base_url() ?>/assets/app/js/exam-guard.js"></script>
<script>
    ExamGuard.init({scheduleId: <?= (int) $jadwal->id_jadwal ?>, active: true});
    var elem = document.documentElement;
    history.pushState(null, null, '<?php echo $_SERVER["REQUEST_URI"]; ?>');
    window.addEventListener('popstate', function (event) {
        loadSoalNomor(1);
    });
    const infoJadwal = JSON.parse(JSON.stringify(<?= json_encode($jadwal) ?>));
    let nomorSoal = 0;
    let idSoal, idSoalSiswa, jenisSoal, modelSoal, typeSoal;
    let raguSoal = false;
    let jawabanSiswa, jawabanBaru = null, jsonJawaban;
    let nav = 0;
    let soalTerjawab = 0, soalTotal = 0;
    let timerOut;
    let timerSelesai;
    let questionRequest = null;
    let questionRequestSequence = 0;
    let answerRequest = null;
    let answerSaving = false;
    let answerRevision = 0;
    let savedAnswerRevision = 0;
    let autosaveTimer = null;
    let saveIsForNavigation = false;
    let pendingNavigation = null;
    let raguSaving = false;
    let examExpired = false;
    let timeoutSubmissionInFlight = false;
    let heartbeatWarningShown = false;
    //const durasi = JSON.parse(JSON.stringify(<?= json_encode($elapsed) ?>));
    let elapsed = '0';
    let h, m, s;
    var message = "Jangan menggunakan klik kanan!";

    let tick = 0;
    const _second = 1000,
        _minute = _second * 60,
        _hour = _minute * 60,
        _day = _hour * 24;
    const durasiUjian = Number(infoJadwal.durasi_ujian);
    let dif;
    let fieldLinks;
    let zoomClicked = 3;
    var arrSize = [];

    function setExamBusy(isBusy) {
        $('#exam-card').attr('aria-busy', isBusy ? 'true' : 'false');
        $('#loading').toggleClass('d-none', !isBusy);
        $('#next, #prev, #btn-ragu, [data-target="#daftarModal"], #exam-navigator-content .btn')
            .prop('disabled', isBusy || examExpired);
        $('#konten-jawaban :input').prop('disabled', isBusy || examExpired);
        if (!isBusy && !examExpired && nomorSoal <= 1) {
            $('#prev').prop('disabled', true);
        }
    }

    function recoverExamUi() {
        setExamBusy(false);
    }

    function showRequestRetry(title, message, retry) {
        swal.fire({
            title: title,
            text: message,
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Coba lagi',
            cancelButtonText: 'Tetap di soal ini',
            allowOutsideClick: false
        }).then(function (result) {
            if (result.value) retry();
        });
    }

    function hasUnsavedAnswer() {
        return answerRevision > savedAnswerRevision;
    }

    function scheduleAutosave(delay) {
        if (autosaveTimer) clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(function () {
            autosaveTimer = null;
            saveCurrentAnswer(null, false);
        }, typeof delay === 'number' ? delay : 700);
    }

    function markAnswerDirty() {
        answerRevision++;
        $('#save-state').removeClass('is-saved is-error').text('Belum tersimpan');
        scheduleAutosave(700);
    }

    function saveCurrentAnswer(destination, forNavigation) {
        if (examExpired || !jsonJawaban || !jsonJawaban.jenis) return;
        if (destination !== null && destination !== undefined) pendingNavigation = destination;
        saveIsForNavigation = saveIsForNavigation || !!forNavigation;
        if (autosaveTimer) {
            clearTimeout(autosaveTimer);
            autosaveTimer = null;
        }
        if (saveIsForNavigation) setExamBusy(true);
        if (answerSaving) return;

        if (!hasUnsavedAnswer()) {
            if (pendingNavigation !== null) {
                var cleanDestination = pendingNavigation;
                pendingNavigation = null;
                saveIsForNavigation = false;
                loadSoalNomor(cleanDestination);
            }
            return;
        }

        var siswa = $('#up').find('input[name="siswa"]').val();
        var bank = $('#up').find('input[name="bank"]').val();
        var savingRevision = answerRevision;
        var savedValue = JSON.parse(JSON.stringify(jawabanBaru));
        var formData = new FormData($('#jawab')[0]);
        formData.append('siswa', siswa);
        formData.append('bank', bank);
        formData.append('data', JSON.stringify(jsonJawaban));

        answerSaving = true;
        $('#save-state').removeClass('is-saved is-error').text('Menyimpan...');
        answerRequest = $.ajax({
            url: base_url + 'siswa/savejawaban',
            method: 'POST',
            processData: false,
            contentType: false,
            data: formData
        }).done(function (response) {
            soalTerjawab = response.soal_terjawab;
            savedAnswerRevision = Math.max(savedAnswerRevision, savingRevision);
            jawabanSiswa = savedValue;
            answerSaving = false;
            $('#save-state').removeClass('is-error').addClass('is-saved').text('✓ Tersimpan');
            if (examExpired) return;
            if (hasUnsavedAnswer()) {
                saveCurrentAnswer(pendingNavigation, saveIsForNavigation);
                return;
            }
            if (pendingNavigation !== null) {
                var nextDestination = pendingNavigation;
                pendingNavigation = null;
                saveIsForNavigation = false;
                loadSoalNomor(nextDestination);
            } else {
                saveIsForNavigation = false;
            }
        }).fail(function (xhr) {
            answerSaving = false;
            if (examExpired) return;
            var blockedNavigation = saveIsForNavigation;
            recoverExamUi();
            $('#save-state').removeClass('is-saved').addClass('is-error').text('Jawaban belum tersimpan');
            console.warn('Gagal menyimpan jawaban:', xhr.status);
            if (blockedNavigation) {
                var retryDestination = pendingNavigation;
                pendingNavigation = null;
                saveIsForNavigation = false;
                showRequestRetry(
                    'Jawaban belum tersimpan',
                    'Periksa koneksi, lalu coba lagi. Jawaban Anda masih ada di layar ini.',
                    function () { saveCurrentAnswer(retryDestination, true); }
                );
            } else {
                scheduleAutosave(3000);
            }
        }).always(function () {
            answerRequest = null;
        });
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    $(document).ready(function () {
        $(document).keydown(function (event) {
            //console.log('press', event.keyCode);
            var charCode = event.charCode || event.keyCode || event.which;
            if (charCode == 27 || charCode == 91 || charCode == 92) {
                return false;
            }
        });

        document.onmousedown = rtclickcheck;
        swal.fire({
            title: 'Peraturan Ujian',
            html: 'Kerjakan soal dengan serius,<br>jangan nyontek!',
            // showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Iya',
            allowOutsideClick: false
        }).then((result) => {
            if (result.value) {
                openFullscreen();
            }
        });

        $('#jawab').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            saveCurrentAnswer(nav > 0 ? nav : null, nav > 0);
        });

        $("#plus").click(function () {
            if (zoomClicked > 20) return;
            $(".konten-soal-jawab").find('*').not('.img-fluid').each(function (idx, v) {
                console.log(idx, parseInt($(v).css("font-size")));
                var size = parseInt($(this).css("font-size"));
                size = (size + 1) + "px";
                $(this).css({'font-size': size});
            });
            $('.img-fluid').each(function () {
                var currWidth = parseInt($(this).css('width'));
                $(this).css('width', (currWidth + 20) + 'px');
            });
            zoomClicked +=1;
        });

        $("#minus").click(function () {
            if (zoomClicked <= 0) return;
            $(".konten-soal-jawab").find('*').not('.img-fluid').each(function () {
                var size = parseInt($(this).css("font-size"));
                size = (size - 1) + "px";
                $(this).css({'font-size': size});
            });
            $('.img-fluid').each(function () {
                var currWidth = parseInt($(this).css('width'));
                $(this).css('width', (currWidth - 20) + 'px');
            });
            zoomClicked -=1;
        });

        loadSoalNomor(1);
    });

    function loadSoalNomor(nomor) {
        nomor = parseInt(nomor, 10);
        if (!nomor || nomor < 1) {
            recoverExamUi();
            return;
        }
        if (nomor == nomorSoal) {
            if (questionRequest && questionRequest.readyState !== 4) {
                questionRequestSequence++;
                questionRequest.abort();
                questionRequest = null;
            }
            recoverExamUi();
            return;
        }
        var dataPost = $('#up').serialize() + '&nomor=' + nomor + '&timer=' + $('#timer').text() + '&elapsed=' + elapsed;
        if (soalTotal === 0 || nomor <= parseInt(soalTotal)) {
            if (questionRequest && questionRequest.readyState !== 4) {
                questionRequest.abort();
            }
            var requestSequence = ++questionRequestSequence;
            setExamBusy(true);
            questionRequest = $.ajax({
                type: 'POST',
                url: base_url + 'siswa/loadnomorsoal',
                data: dataPost
            }).done(function (data) {
                if (requestSequence !== questionRequestSequence) return;
                setKonten(data);
                recoverExamUi();
            }).fail(function (xhr, status) {
                if (status === 'abort' || requestSequence !== questionRequestSequence) return;
                recoverExamUi();
                console.warn('Gagal memuat soal:', xhr.status);
                showRequestRetry(
                    'Soal gagal dimuat',
                    'Koneksi ke server bermasalah. Coba muat soal ini kembali.',
                    function () { loadSoalNomor(nomor); }
                );
            }).always(function () {
                if (requestSequence === questionRequestSequence) questionRequest = null;
            });
        } else {
            selesai();
        }
    }

    function loadSoal(datas) {
        $('#daftarModal').modal('hide').data('bs.modal', null);
        $('#daftarModal').on('hidden', function () {
            $(this).data('modal', null);
        });

        nav = $(datas).data('nomorsoal');
        if (hasUnsavedAnswer() || answerSaving) {
            $('#jawab').submit();
        } else {
            loadSoalNomor(nav);
        }
    }

    function setKonten(data) {
        console.log('max_jawaban', data.max_jawaban);
        idSoal = data.soal_id;
        idSoalSiswa = data.soal_siswa_id;
        nomorSoal = parseInt(data.soal_nomor);
        jenisSoal = data.soal_jenis;
        soalTerjawab = data.soal_terjawab;
        soalTotal = data.soal_total;

        if (autosaveTimer) {
            clearTimeout(autosaveTimer);
            autosaveTimer = null;
        }
        answerRevision = 0;
        savedAnswerRevision = 0;
        saveIsForNavigation = false;
        jsonJawaban = {};
        jawabanBaru = null;
        jawabanSiswa = data.soal_jawaban_siswa != null ? data.soal_jawaban_siswa : '';
        if ($.isArray(jawabanSiswa)) jawabanSiswa.sort();
        $('#save-state').removeClass('is-error').addClass('is-saved').text('✓ Jawaban tersinkron');

        if (nomorSoal === 1) {
            $('#prev').attr('disabled', 'disabled');
        } else {
            $('#prev').removeAttr('disabled');
        }

        $('#nomor-soal').html(nomorSoal);
        $('#btn-ragu').removeClass('btn-warning').addClass('btn-outline-warning');
        raguSoal = parseInt(data.soal_ragu, 10) === 1;
        if (raguSoal) {
            $('#btn-ragu').removeClass('btn-outline-warning').addClass('btn-warning');
        }
        $('#btn-ragu').attr('aria-pressed', raguSoal ? 'true' : 'false');
        $('#konten-soal').html(data.soal_soal);
        var stimulus = data.soal_stimulus == null ? '' : $.trim(data.soal_stimulus);
        if (stimulus !== '') {
            $('#konten-stimulus').html('<div class="alert alert-default-secondary border border-secondary p-2">' + stimulus + '</div>').removeClass('d-none');
        } else {
            $('#konten-stimulus').html('').addClass('d-none');
        }
        var jenis = data.soal_jenis;
        var html = '';
        if (jenis == "1" || jenis == "6") {
            $.each(data.soal_opsi, function (key, opsis) {
                if (opsis.valAlias != "") {
                    html += '<label class="container-jawaban exam-option-card font-weight-normal">' + opsis.opsi +
                        '<input type="radio"' +
                        ' name="jawaban"' +
                        ' value="' + opsis.value.toUpperCase() + '"' +
                        ' data-jawabansiswa="' + opsis.value.toUpperCase() + '"' +
                        ' data-jawabanalias="' + opsis.valAlias.toUpperCase() + '"' +
                        ' onclick="submitJawaban(this)" ' + opsis.checked + '>' +
                        '<span class="checkmark shadow text-center align-middle">' + opsis.valAlias.toUpperCase() + '</span>' +
                        '</label>';
                }
            });
            $('#konten-jawaban').html(html);
        } else if (jenis == "2") {
            $.each(data.soal_opsi, function (key, opsis) {
                html += '<div class="custom-control custom-checkbox checkbox-xl">' +
                    '<input type="checkbox" class="check2 custom-control-input"' +
                    'id="check'+key+'"' +
                    ' name="jawaban"' +
                    ' value="' + opsis.value.toUpperCase() + '"' +
                    ' data-max="' + data.max_jawaban[0] + '"' +
                    ' data-jawabansiswa="' + opsis.value.toUpperCase() + '"' +
                    ' onclick="submitJawaban(this)" ' + opsis.checked + '>' +
                    '<label class="custom-control-label font-weight-normal" for="check'+key+'">'
                    + opsis.opsi +'</label>' +
                    '</div>'
            });
            $('#konten-jawaban').html(html);
        } else if (jenis == "3") {
            modelSoal = data.soal_opsi.model;
            typeSoal = data.soal_opsi.type;
            let konten = $('#konten-jawaban')
            konten.html('');

            const dataJawab = data.soal_opsi
            const copy = $.extend(true, {}, dataJawab);
            console.log('test', copy)

            let arrData = [copy.tabel[0]]
            if (Array.isArray(copy.tbody)) {
                for (let i = 0; i < copy.tbody.length; i++) {
                    let val = copy.tbody[i]
                    for (let j = 0; j < val.length; j++) {
                        if (j === 0) val[j] = copy.tabel[i+1][0]
                    }
                    arrData.push(val)
                }
            } else {
                for (let i = 0; i < copy.tabel.length; i++) {
                    let val = copy.tbody[i]
                    if (val) {
                        for (let j = 0; j < val.length; j++) {
                            if (j === 0) val[j] = copy.tabel[i][0]
                        }
                        arrData.push(val)
                    }
                }
            }

            let keys = 0
            let dataMax = {}
            $.each(data.max_jawaban, function (key, val) {
                dataMax[keys] = val
                keys ++
            })

            let objJawaban = {
                jawaban: arrData,
                max: dataMax,
                model: modelSoal,
                type: typeSoal,
            }
            console.log('obj', objJawaban)
            konten.linkerList({
                enableEditor: false,
                data: objJawaban,
                viewMode: '2',
                id: nomorSoal,
                callback: function (id, data, hasLinks, isOffset) {
                    if (isOffset !== '0') {
                        $.toast({
                            heading: 'Warning',
                            text: 'Maksimal <b>' + isOffset + ' jawaban',
                            showHideTransition: 'slide',
                            icon: 'error',
                            loaderBg: '#f2a654',
                            position: 'bottom-center'
                        })
                    } else {
                        submitJawaban(data)
                    }
                }
            });
        } else if (jenis == "4") {
            html += '<div class="pr-4">' +
                '<span class="">JAWABAN:</span><br>' +
                '<div class="row"><div class="col-12 col-sm-8 col-md-6 col-lg-4 col-xl-4">'+
                '<input id="jawaban-essai" class="pl-1 form-control" type="text"' +
                ' name="jawaban" value="' + escapeHtml(jawabanSiswa) + '"' +
                ' placeholder="Tulis jawaban disini"/><br>' +
                '</div></div>' +
                '</div>';
            $('#konten-jawaban').html(html);
        } else {
            html += '<div class="pr-4">' +
                '<label>JAWABAN:</label><br>' +
                '<textarea id="jawaban-essai" class="w-100 pl-1" type="text"' +
                ' name="jawaban" rows="4"' +
                ' placeholder="Tulis jawaban disini">' + escapeHtml(jawabanSiswa) + '</textarea><br>' +
                '</div>';
            $('#konten-jawaban').html(html);
        }

        var soalModal = String(data.soal_modal || '');
        $('#konten-modal').html(soalModal);
        var sidebarSoal = soalModal.replace(/id=(["'])(box|btn|badge)(\d+)\1/g, function (_, quote, prefix, number) {
            return 'id=' + quote + 'side-' + prefix + number + quote;
        });
        $('#exam-navigator-content').html(sidebarSoal);
        decorateNavigator($('#konten-modal'));
        decorateNavigator($('#exam-navigator-content'));

        var $imgs = $('.konten-soal-jawab').find('img');
        $.each($imgs, function () {
            if (!$(this).attr('alt')) $(this).attr('alt', 'Gambar pendukung soal');
            var curSrc = $(this).attr('src') || '';
            if (curSrc.indexOf('uploads') === -1) return;
            var newSrc = '';
            if (curSrc.indexOf("http") === -1 && curSrc.indexOf("data:image") === -1) {
                newSrc = base_url + curSrc;
                $(this).attr('src', newSrc);
            } else if (curSrc.indexOf(base_url) === -1) {
                var pathUpload = 'uploads';
                var forReplace = curSrc.split(pathUpload);
                newSrc = base_url + pathUpload + forReplace[1];
                $(this).attr('src', newSrc);
            }
            $(this).on('load', function () {
                if ($(this).height() > 50) {
                    $(this).addClass('img-fluid');
                }
            });
        });

        $('video').css({'width': '100%', 'max-height': '100%'});

        $('.check').change(function (e) {
            var row = $(e.target).closest('tr');
            var isChecked = $(row).find("input:checked");
            var max = $(e.target).data('max');
            if (isChecked.length > max) {
                $(e.target).prop('checked', !$(this).prop('checked'));
                $.toast({
                    heading: 'Warning',
                    text: 'Maksimal <b>' + max + ' jawaban',
                    showHideTransition: 'slide',
                    icon: 'error',
                    loaderBg: '#f2a654',
                    position: 'bottom-center'
                })
            } else {
                submitJawaban(null);
            }
        });

        $("#jawaban-essai").on('change keyup paste', function () {
            submitJawaban(null);
        });
        if (!data.durasi) {
            window.location.href = base_url + 'siswa/cbt';
        } else {
            setElapsed(data.durasi);

            if (timerSelesai) {
                clearTimeout(timerSelesai);
                timerSelesai = null;
            }

            var next = $('#next');
            next.removeAttr('disabled');
            var txtnext = $('#text-next');
            $('#ic-btn').remove();

            if (soalTotal === nomorSoal && data.durasi.mulai != null && data.durasi.mulai != '0') {
                next.removeClass('btn-primary');
                next.addClass('btn-success');
                next.append('<i id="ic-btn" class="fa fa-check-circle"></i>');
                txtnext.html('<b>Selesai</b>');
                setTimerSelesai(next, data.durasi);
            } else {
                $('#timer-selesai').addClass('d-none');
                next.removeClass('d-none');
                next.removeClass('btn-success');
                next.addClass('btn-primary');
                next.append('<i id="ic-btn" class="fa fa-arrow-circle-right"></i>');
                txtnext.html('<b>Soal Berikutnya</b>');
            }
        }

        arrSize = [];
    }

    function setElapsed(durasi) {
        elapsed = durasi.lama_ujian == null || durasi.lama_ujian == '0' ? "00:00:00" : durasi.lama_ujian;
        startExamCountdown(null);
    }

    function startExamCountdown(serverRemainingSeconds) {
        createTimerCountdown(durasiUjian, elapsed.split(':'), function (isOver, remaining, onGoing) {
            $('#timer').html(remaining);
            elapsed = onGoing;
            if (isOver) submitExpiredExam();
        }, serverRemainingSeconds);
    }

    function showExpiredRetry(message) {
        $('#loading').addClass('d-none');
        swal.fire({
            title: 'Ujian belum terkumpul',
            text: message,
            icon: 'error',
            confirmButtonText: 'Coba lagi',
            allowOutsideClick: false,
            allowEscapeKey: false,
            footer: 'Jika tetap gagal, segera hubungi proktor dan jangan tutup halaman ini.'
        }).then(function () {
            submitExpiredExam();
        });
    }

    function submitExpiredExam() {
        if (timeoutSubmissionInFlight) return;
        timeoutSubmissionInFlight = true;
        examExpired = true;
        if (questionRequest && questionRequest.readyState !== 4) {
            questionRequestSequence++;
            questionRequest.abort();
            questionRequest = null;
        }
        if (answerRequest && answerRequest.readyState !== 4) {
            answerRequest.abort();
        }
        setExamBusy(true);

        var siswa = $('#up').find('input[name="siswa"]').val();
        var bank = $('#up').find('input[name="bank"]').val();
        var jadwal = $('#up').find('input[name="jadwal"]').val();
        var saveData = $('#jawab').serialize() + '&jadwal=' + encodeURIComponent(jadwal) +
            '&siswa=' + encodeURIComponent(siswa) + '&bank=' + encodeURIComponent(bank) +
            '&waktu=' + encodeURIComponent($('#timer').text()) + '&elapsed=' + encodeURIComponent(elapsed) +
            '&data=' + encodeURIComponent(JSON.stringify(jsonJawaban || {}));

        var saveRequest = jsonJawaban && jsonJawaban.jenis ? $.ajax({
            url: base_url + 'siswa/savejawaban',
            method: 'POST',
            data: saveData
        }) : $.Deferred().resolve().promise();

        saveRequest.done(function () {
            /* Simpan jawaban terakhir lebih dahulu, lalu kumpulkan ujian. */
            $.post(base_url + 'siswa/selesaiujian', {
                siswa: siswa,
                bank: bank,
                jadwal: jadwal
            }, null, 'json').done(function (response) {
                timeoutSubmissionInFlight = false;
                if (response && response.status === false) {
                    showExpiredRetry(response.message || 'Server belum dapat menyelesaikan ujian.');
                    return;
                }
                $('.konten-soal-jawab').html('');
                dialogWaktu();
            }).fail(function (xhr) {
                timeoutSubmissionInFlight = false;
                console.warn('Gagal mengumpulkan ujian:', xhr.status);
                showExpiredRetry('Jawaban sudah disimpan, tetapi ujian belum berhasil dikumpulkan.');
            });
        }).fail(function (xhr) {
            timeoutSubmissionInFlight = false;
            console.warn('Gagal menyimpan jawaban terakhir:', xhr.status);
            showExpiredRetry('Jawaban terakhir belum tersimpan karena koneksi ke server bermasalah.');
        });
    }

    function nextSoal() {
        if (raguSaving || examExpired) return;
        setExamBusy(true);
        nav = (nomorSoal + 1);
        if (hasUnsavedAnswer() || answerSaving) {
            $('#jawab').submit();
        } else {
            loadSoalNomor(nav);
        }
    }

    function setTimerSelesai(next, durasi) {
        const perdetik = 1000;
        const permenit = 60 * perdetik;
        const perjam = 60 * permenit;
        const t_dur = Number(infoJadwal.jarak) * (1000 * 60);

        const elapsed = (durasi.lama_ujian == null || durasi.lama_ujian == '0' ? "00:00:00" : durasi.lama_ujian).split(':');
        let t_jam = Number(elapsed[0]);
        let t_mnt = Number(elapsed[1]);
        let t_dtk = Number(elapsed[2]);

        const btnTimer = $('#timer-selesai');
        setTimer();

        function setTimer() {
            if (timerSelesai) {
                clearTimeout(timerSelesai);
                timerSelesai = null;
            }

            const elapsedMicro = (t_jam * perjam) + (t_mnt * permenit) + (t_dtk * perdetik);
            const t_remaining = t_dur - elapsedMicro;
            if (t_remaining <= 0) {
                next.removeClass('d-none');
                btnTimer.addClass('d-none');
            } else {
                // elapsed
                t_dtk++;
                if (t_dtk > 59) {
                    t_dtk = 0;
                    t_mnt++;
                }
                if (t_mnt > 59) {
                    t_mnt = 0;
                    t_jam++;
                }

                // remaining
                const r_jam = Math.floor(t_remaining / perjam);
                const r_mnt = Math.floor((t_remaining % perjam) / permenit);
                const r_dtk = Math.floor((t_remaining % permenit) / perdetik);
                next.addClass('d-none');
                btnTimer.removeClass('d-none');
                btnTimer.html(zeroPad(r_jam) + ':' + zeroPad(r_mnt) + ':' + zeroPad(r_dtk));

                timerSelesai = setTimeout(setTimer, 1000);
            }
        }
    }

    function prevSoal() {
        if (raguSaving || examExpired) return;
        setExamBusy(true);
        nav = (nomorSoal - 1);
        if (hasUnsavedAnswer() || answerSaving) {
            $('#jawab').submit();
        } else {
            loadSoalNomor(nav);
        }
    }

    function decorateNavigator(root) {
        root.find('.btn').each(function () {
            var button = $(this);
            var idMatch = String(button.attr('id') || '').match(/btn(\d+)$/);
            var number = button.data('nomorsoal') || (idMatch ? idMatch[1] : $.trim(button.text()));
            var isFlagged = button.hasClass('btn-warning');
            var isAnswered = button.hasClass('btn-success') || button.closest('[id*="box"]').find('[id*="badge"]').length > 0;
            var status = isFlagged ? 'ragu-ragu' : (isAnswered ? 'terjawab' : 'belum dijawab');
            button.find('.navigator-status-icon').remove();
            if (isFlagged) {
                button.append('<i class="fas fa-flag navigator-status-icon ml-1" aria-hidden="true"></i>');
            }
            button.attr({
                'aria-label': 'Soal ' + number + ', ' + status,
                'aria-pressed': isFlagged ? 'true' : 'false'
            });
        });
    }

    function updateModal(jwb) {
        $('#konten-modal, #exam-navigator-content').each(function () {
            var root = $(this);
            var badges = root.find('[id$="badge' + nomorSoal + '"]');
            var btn = root.find('[id$="btn' + nomorSoal + '"]');
            btn.removeClass("btn-outline-secondary btn-light btn-primary btn-success btn-warning");
            btn.addClass(raguSoal ? "btn-warning" : "btn-success");
            if (jenisSoal == 1 || jenisSoal == 6) {
                if (badges.length) {
                    badges.text(jwb)
                } else {
                    var badgeId = root.attr('id') === 'exam-navigator-content' ? 'side-badge' : 'badge';
                    var boxId = root.attr('id') === 'exam-navigator-content' ? 'side-box' : 'box';
                    var badge = '<div id="' + badgeId + nomorSoal + '" class="badge badge-pill badge-success border border-dark text-yellow"' +
                        ' style="font-size:12pt; width: 30px; height: 30px; margin-top: -60px; margin-left: 30px;">' +
                        jwb +
                        '</div>';
                    root.find('#' + boxId + nomorSoal).append(badge);
                }
            } else if (!badges.length) {
                var badgeId = root.attr('id') === 'exam-navigator-content' ? 'side-badge' : 'badge';
                var boxId = root.attr('id') === 'exam-navigator-content' ? 'side-box' : 'box';
                var badge = '<div id="' + badgeId + nomorSoal + '" class="badge badge-pill badge-success border border-dark"' +
                    ' style="font-size:12pt; width: 30px; height: 30px; margin-top: -60px; margin-left: 30px;">&check;</div>';
                root.find('#' + boxId + nomorSoal).append(badge);
            }
            decorateNavigator(root);
        });
    }

    function submitJawaban(opsi) {
        var jawaban_Siswa = '', jawaban_Alias = '';
        if (jenisSoal == 1 || jenisSoal == 6) {
            jawaban_Siswa = $(opsi).data('jawabansiswa');
            jawaban_Alias = $(opsi).data('jawabanalias');
        } else if (jenisSoal == 2) {
            var isChecked = $('#konten-jawaban').find("input:checked");
            var max = $(opsi).data('max');
            console.log('max:'+max, 'checked:'+isChecked.length);
            if (isChecked.length > max) {
                $(opsi).prop('checked', !$(opsi).prop('checked'));
                $.toast({
                    heading: 'Warning',
                    text: 'Maksimal <b>' + max + ' jawaban',
                    showHideTransition: 'slide',
                    icon: 'error',
                    loaderBg: '#f2a654',
                    position: 'bottom-center'
                });
                return;
            } else {
                var selected = [];
                $('#konten-jawaban input:checked').each(function () {
                    selected.push($(this).val());
                });
                jawaban_Siswa = selected;
            }
        } else if (jenisSoal == 3) {
            jawaban_Siswa = opsi
            /*
            var jawaban_json = modelSoal == '1' ? convertListToTable() : getDataTable();
            console.log('click submit', jawaban_json);
            jawaban_Siswa = {};
            jawaban_Siswa['jawaban'] = jawaban_json;
            jawaban_Siswa['type'] = typeSoal;
            jawaban_Siswa['model'] = modelSoal;
             */
        } else {
            jawaban_Siswa = $('#jawaban-essai').val();
        }
        jawabanBaru = jawaban_Siswa;
        if (jenisSoal == 2) {
            if ($.isArray(jawabanBaru)) jawabanBaru.sort();
        }

        updateModal(jawaban_Alias);
        jsonJawaban = createJsonJawaban(jawaban_Alias, jawaban_Siswa);
        markAnswerDirty();
    }

    function createJsonJawaban(jawab_Alias, jawab_Siswa) {
        var siswa = $('#up').find('input[name="siswa"]').val();
        var jadwal = $('#up').find('input[name="jadwal"]').val();
        var bank = $('#up').find('input[name="bank"]').val();

        var item = {};
        item ["no_soal_alias"] = nomorSoal;
        item ["jawaban_alias"] = jawab_Alias;
        item ["jawaban_siswa"] = jawab_Siswa;
        item ["jenis"] = jenisSoal;
        item ["id_soal"] = idSoal;
        item ["id_soal_siswa"] = idSoalSiswa;
        item ["id_jadwal"] = jadwal;
        item ["id_bank"] = bank;
        item ["id_siswa"] = siswa;

        return item;
    }

    function getDataTable() {
        var tbl = $('#table-jodohkan tr').get().map(function (row) {
            var $tables = [];

            $(row).find('th').get().map(function (cell) {
                var klm = $(cell).text().trim();
                $tables.push(klm == "" ? "#" : encode(klm));
            });

            $(row).find('td').get().map(function (cell) {
                if ($(cell).children('input').length > 0) {
                    $tables.push($(cell).find('input').prop("checked") === true ? "1" : "0");
                } else {
                    $tables.push(encode($(cell).text().trim()))
                }
            });

            return $tables;
        });
        return tbl;
    }

    function convertTable(data) {
        const head = []
        const body = []
        $.each(data.tabel, function (idx, val) {
            if (idx === 0) {
                $.each(val, function (id, vl) {
                    if (vl !== "#") head.push(encode(vl))
                })
            } else {
                $.each(val, function (id, vl) {
                    if (id === 0) body.push(encode(vl))
                })
            }
        })
        var kanan = data.thead;
        var kiri = [];
        $.each(data.tbody, function (i, v) {
            kiri.push(encode(v.shift()));
        });
        kanan.shift();

        var linked = [];
        $.each(data.tbody, function (n, arv) {
            $.each(arv, function (t, v) {
                if (v == '1') {
                    var it = {};
                    it['from'] = encode(body[n]);
                    it['to'] = encode(head[t]);
                    linked.push(it);
                }
            });
        });
        var item = {};
        item['type'] = data.type;
        item['jawaban'] = [body, head];
        item['linked'] = linked;
        return item;
    }

    function convertTableToList(data) {
        var kanan = data.thead;
        //console.log('kanan', kanan);
        var kiri = [];
        $.each(data.tbody, function (i, v) {
            kiri.push(decode(v.shift()));
        });
        kanan.shift();
        //console.log('kiri', kiri);
        $.each(kanan, function (i, v) {
            kanan[i] = (decode(v));
        });

        var linked = [];
        $.each(data.tbody, function (n, arv) {
            $.each(arv, function (t, v) {
                if (v == '1') {
                    var it = {};
                    it['from'] = decode(kiri[n]);
                    it['to'] = decode(kanan[t]);
                    linked.push(it);
                }
            });
        });
        var item = {};
        item['type'] = data.type;
        item['jawaban'] = [kiri, kanan];
        item['linked'] = linked;
        //console.log('test', item);
        return item;
    }

    function getListData() {
        var kolom = [];
        var baris = [];
        $(".FL-left li").each(function () {
            baris.push(encode($(this).text()));
        });
        $(".FL-right li").each(function () {
            kolom.push(encode($(this).text()));
        });
        return [kolom, baris];
    }

    /*
    function convertListToTable() {
        var results = fieldLinks.fieldsLinker("getLinks");
        var links = results.links;
        //console.log('linked', links);

        var array = getListData();
        var kolom = array[0];
        //console.log('kolom', kolom);
        var arrayres = [];
        $.each(array[1], function (ind, val) {
            //console.log('kolom', kolom);
            var vv = [];
            for (let i = 0; i < kolom.length; i++) {
                var sv = '0';
                if (links.length > 0) {
                    $.each(links, function (p, isi) {
                        if (encode(isi.from) == encode(val)) {
                            if (encode(isi.to) == encode(kolom[i])) {
                                sv = '1';
                            }
                        }
                    });
                }
                vv.push(sv);
            }

            vv.unshift(val);
            arrayres.push(vv);
        });
        kolom.unshift('#');
        arrayres.unshift(kolom);
        console.log('aray', arrayres);
        return arrayres;
    }
     */

    function selesai() {
        if (soalTotal === soalTerjawab) {
            swal.fire({
                title: "Selesaikan ujian?",
                html: '<div class="text-left">' +
                    '<div class="d-flex justify-content-between"><span>Terjawab</span><b>' + soalTerjawab + '</b></div>' +
                    '<div class="d-flex justify-content-between"><span>Belum dijawab</span><b>' + Math.max(soalTotal - soalTerjawab, 0) + '</b></div>' +
                    '<div class="d-flex justify-content-between"><span>Ragu-ragu</span><b>' + $('#exam-navigator-content .btn-warning').length + '</b></div>' +
                    '</div>',
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Selesaikan!",
                allowOutsideClick: false
            }).then(result => {
                if (result.value) {
                    $.ajax({
                        url: base_url + 'siswa/selesaiujian',
                        method: "POST",
                        data: $('#up').serialize(),
                        success: function (respon) {
                            if (respon.status) {
                                window.location.href = base_url + 'siswa/cbt';
                            } else {
                                recoverExamUi();
                                swal.fire({
                                    title: "Gagal",
                                    text: respon.message || "Tidak bisa menyelesaikan ujian",
                                    icon: "error"
                                });
                            }
                        },
                        error: function (xhr) {
                            recoverExamUi();
                            console.warn('Gagal menyelesaikan ujian:', xhr.status);
                            showRequestRetry(
                                'Ujian belum terkumpul',
                                'Koneksi ke server bermasalah. Coba kumpulkan kembali.',
                                selesai
                            );
                        }
                    });
                } else {
                    recoverExamUi();
                }
            });
        } else {
            swal.fire({
                title: "BELUM SELESAI!",
                text: "Masih ada soal yang belum dikerjakan",
                icon: "error",
                confirmButtonColor: "#3085d6",
            }).then(() => {
                recoverExamUi();
            });
        }
    }

    function dialogWaktu() {
        swal.fire({
            title: "Sudah Habis",
            text: "Waktu Ujian sudah habis, hubungi proktor",
            icon: "warning",
            allowOutsideClick: false,
            confirmButtonColor: "#3085d6",
            confirmButtonText: "OK"
        }).then(result => {
            if (result.value) {
                window.location.href = base_url + 'siswa/cbt';
            }
        });
    }

    function rtclickcheck(keyp) {
        if (navigator.appName == "Netscape" && keyp.which == 3) {
            alert(message);
            return false;
        }

        if (navigator.appVersion.indexOf("MSIE") != -1 && event.button == 2) {
            alert(message);
            return false;
        }
    }

    function openFullscreen() {
        if (elem.requestFullscreen) {
            elem.requestFullscreen();
        } else if (elem.mozRequestFullScreen) {
            /* Firefox */
            elem.mozRequestFullScreen();
        } else if (elem.webkitRequestFullscreen) {
            /* Chrome, Safari & Opera */
            elem.webkitRequestFullscreen();
        } else if (elem.msRequestFullscreen) {
            /* IE/Edge */
            elem = window.top.document.body; //To break out of frame in IE
            elem.msRequestFullscreen();
        }
    }

    function getMinutes(startTime) {
        var endTime = new Date();
        endTime.setHours(endTime.getHours() - startTime.getHours());
        endTime.setMinutes(endTime.getMinutes() - startTime.getMinutes());
        endTime.setSeconds(endTime.getSeconds() - startTime.getSeconds());

        return {h: endTime.getHours(), m: endTime.getMinutes(), s: endTime.getSeconds()}
    }

    function createTimerCountdown(durasi, elapsedParts, func, serverRemainingSeconds) {
        const durationSeconds = Number(durasi) * 60;
        const elapsedAtStart = ((Number(elapsedParts[0]) || 0) * 3600) +
            ((Number(elapsedParts[1]) || 0) * 60) + (Number(elapsedParts[2]) || 0);
        const hasServerRemaining = serverRemainingSeconds !== null && serverRemainingSeconds !== undefined &&
            serverRemainingSeconds !== '' && Number.isFinite(Number(serverRemainingSeconds));
        const remainingAtStart = hasServerRemaining
            ? Math.max(0, Number(serverRemainingSeconds))
            : Math.max(0, durationSeconds - elapsedAtStart);
        const startedAt = Date.now();
        const deadlineAt = startedAt + (remainingAtStart * 1000);

        tickTimer();

        function tickTimer() {
            if (timerOut) {
                clearTimeout(timerOut);
                timerOut = null;
            }

            const remainingSeconds = Math.max(0, Math.ceil((deadlineAt - Date.now()) / 1000));
            const elapsedSeconds = Math.max(0, elapsedAtStart + Math.floor((Date.now() - startedAt) / 1000));
            const elapsedFormatted = secondsToClock(elapsedSeconds);
            if (remainingSeconds <= 0) {
                if (func && (typeof func == "function")) {
                    func(true, 'Waktu habis', elapsedFormatted);
                }
            } else {
                if (func && (typeof func == "function")) {
                    func(false, secondsToClock(remainingSeconds), elapsedFormatted);
                }
                timerOut = setTimeout(tickTimer, Math.min(1000, Math.max(100, deadlineAt - Date.now())));
            }
        }
    }

    function secondsToClock(totalSeconds) {
        totalSeconds = Math.max(0, Math.floor(Number(totalSeconds) || 0));
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        return zeroPad(hours) + ':' + zeroPad(minutes) + ':' + zeroPad(seconds);
    }

    function zeroPad(no) {
        return no < 10 ? '0' + no : no;
    }

    function encode(str) {
        var decoded = decodeURIComponent(str)
        var isEncoded = decoded !== str
        var encoded = encodeURIComponent(str)
        if (isEncoded) {
            return str
        } else {
            return encoded
        }
    }

    function decode(str) {
        var decoded = decodeURIComponent(str)
        var encoded = encodeURIComponent(decoded)
        var isEncoded = encoded === str
        if (isEncoded) {
            return decoded
        } else {
            return str
        }
    }

    $(document).on('examguard:heartbeat-failed', function (event, result) {
        if (result && result.message) {
            console.warn('Exam guard:', result.message);
        }
        if (!heartbeatWarningShown && !(result && result.message)) {
            heartbeatWarningShown = true;
            $.toast({
                heading: 'Koneksi terputus',
                text: 'Koneksi ke server ujian terputus. Jawaban akan tetap di layar; periksa jaringan Anda.',
                icon: 'warning',
                position: 'bottom-center',
                hideAfter: 8000
            });
        }
    });

    $(document).on('examguard:heartbeat-ok', function (event, result) {
        heartbeatWarningShown = false;
        if (result && result.sisa_detik !== null && result.sisa_detik !== undefined &&
            Number.isFinite(Number(result.sisa_detik))) {
            startExamCountdown(Number(result.sisa_detik));
        }
    });

    /* PRD FR-18: penanda ragu-ragu tersimpan di server, ikut saat pindah PC. */
    $(document).on('click', '#btn-ragu', function () {
        if (!idSoalSiswa || examExpired || raguSaving || answerSaving) return;
        var button = $(this);
        var target = !raguSoal;
        var questionNumber = nomorSoal;
        var questionStudentId = idSoalSiswa;
        raguSaving = true;
        setExamBusy(true);
        $.post(base_url + 'examguard/ragu', {
            jadwal: $('#jawab input[name="jadwal"]').val(),
            bank: $('#jawab input[name="bank"]').val(),
            id_soal_siswa: questionStudentId,
            ragu: target ? 1 : 0
        }, function (res) {
            if (!res.status) {
                showDangerToast(res.message || 'Penanda ragu-ragu gagal disimpan.');
                return;
            }
            var savedAsDoubtful = res.ragu === 1;
            if (idSoalSiswa === questionStudentId) {
                raguSoal = savedAsDoubtful;
                button.toggleClass('btn-warning', raguSoal)
                    .toggleClass('btn-outline-warning', !raguSoal)
                    .attr('aria-pressed', raguSoal ? 'true' : 'false');
            }
            $('#konten-modal, #exam-navigator-content').each(function () {
                var root = $(this);
                var targetBtn = root.find('[id$="btn' + questionNumber + '"]');
                var answered = root.find('[id$="badge' + questionNumber + '"]').length > 0;
                targetBtn.removeClass('btn-warning btn-success btn-light');
                targetBtn.addClass(savedAsDoubtful ? 'btn-warning' : (answered ? 'btn-success' : 'btn-light'));
                decorateNavigator(root);
            });
        }, 'json').fail(function (xhr) {
            console.warn('Gagal menyimpan ragu-ragu:', xhr.status);
            showDangerToast('Penanda ragu-ragu belum tersimpan. Periksa koneksi.');
        }).always(function () {
            raguSaving = false;
            recoverExamUi();
        });
    });
</script>

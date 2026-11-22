<div class="content-wrapper bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-6">
                    <h1><?= $judul ?></h1>
                </div>
                <div class="col-6">
                    <a href="<?= base_url('cbtcetak') ?>" type="button" class="btn btn-sm btn-danger float-right">
                        <i class="fas fa-arrow-circle-left"></i><span
                                class="d-none d-sm-inline-block ml-1">Kembali</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-5">
                    <div class="card my-shadow card-primary card-outline">
                        <div class="card-header">
                            <h6 class="card-title">Setting Kartu</h6>
                            <button class="card-tools btn btn-sm bg-primary text-white" onclick="submitKartu()">
                                <i class="fas fa-save mr-1"></i> Simpan
                            </button>
                        </div>
                        <div class="card-body">
                            <?= form_open('', array('id' => 'set-kartu')) ?>
                            <div class="form-group">
                                <label>Header 1</label>
                                <textarea id="header-1" class="form-control" name="header_1" rows="2"
                                          placeholder="Header baris 1"
                                          required><?= isset($kartu->header_1) ? $kartu->header_1 : '' ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Header 2</label>
                                <textarea id="header-2" class="form-control" name="header_2" rows="2"
                                          placeholder="Header baris 2"
                                          required><?= isset($kartu->header_2) ? $kartu->header_2 : '' ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Header 3</label>
                                <textarea id="header-3" class="form-control" name="header_3" rows="2"
                                          placeholder="Header baris 3"
                                          required><?= isset($kartu->header_3) ? $kartu->header_3 : '' ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Header 4</label>
                                <textarea id="header-4" class="form-control" name="header_4" rows="2"
                                          placeholder="Header baris 4"
                                          required><?= isset($kartu->header_4) ? $kartu->header_4 : '' ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Tanggal</label>
                                <input id="input-tanggal" class="form-control" name="tanggal" placeholder="Titimangsa"
                                       value="<?= isset($kartu->tanggal) ? $kartu->tanggal : '' ?>" required>
                            </div>
                            <?= form_close() ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="card my-shadow">
                        <div class="card-header">
                            <div class="card-title">
                                Preview
                            </div>
                            <!--
                            <div class="card-tools mr-2 mt-1">
                                <button class="btn btn-sm bg-blue text-white">
                                    <i class="fa fa-file-word-o"></i><span class="ml-1">Word</span>
                                </button>
                                <button class="btn btn-sm bg-red text-white">
                                    <i class="fa fa-file-pdf-o"></i><span class="ml-1">Pdf</span>
                                </button>
                                <button class="btn btn-sm bg-success text-white">
                                    <i class="fa fa-print"></i><span class="ml-1">Cetak</span>
                                </button>
                            </div>
                            -->
                        </div>
                        <div class="card-body pb-4">
                            <div style="display: flex; justify-content: center; align-items: center;">
                                <div style="width: 10cm">
                                    <table id="table-header"
                                           style="width: 100%; border-top: 1px solid black; border-bottom: 0;border-left: 1px solid black; border-right: 1px solid black">
                                        <tr>
                                            <td style="width:20%;">
                                                <img alt="Logo kiri" id="prev-logo-kiri"
                                                     src="<?= base_url() . $setting->logo_kiri ?>"
                                                     style="width:55px; height:55px; margin: 6px;">
                                            </td>
                                            <td style="padding: 0 4px;">
                                                <div id="prev-header-1" class="text-center"
                                                     style="line-height: 1.1; font-family: 'Times New Roman'; font-size: 9pt"><?= isset($kartu->header_1) ? $kartu->header_1 : '' ?></div>
                                                <div id="prev-header-2" class="text-center"
                                                     style="line-height: 1.1; font-family: 'Times New Roman'; font-size: 10pt">
                                                    <b><?= isset($kartu->header_2) ? $kartu->header_2 : '' ?></b></div>
                                                <div id="prev-header-3" class="text-center"
                                                     style="line-height: 1.2; font-family: 'Times New Roman'; font-size: 8pt"><?= isset($kartu->header_3) ? $kartu->header_3 : '' ?></div>
                                                <div id="prev-header-4" class="text-center"
                                                     style="line-height: 1.2; font-family: 'Times New Roman'; font-size: 8pt"><?= isset($kartu->header_4) ? $kartu->header_4 : '' ?></div>
                                            </td>
                                        </tr>
                                    </table>
                                    <table id="table-body" style="width:100%;border: 1px solid black">
                                        <tr style="line-height: 1; font-family: 'Times New Roman'; font-size: 9pt">
                                            <td style="padding-top:8px;padding-left:22px;width: 35%">Nomor Peserta</td>
                                            <td style="padding-top:8px;">:</td>
                                            <td style="padding-top:8px;width: 60%">0000.00.000</td>
                                        </tr>
                                        <tr style="line-height: 1; font-family: 'Times New Roman'; font-size: 9pt">
                                            <td style="padding-left:22px">Nama</td>
                                            <td>:</td>
                                            <td>Nama Siswa</td>
                                        </tr>
                                        <tr style="line-height: 1; font-family: 'Times New Roman'; font-size: 9pt">
                                            <td style="padding-left:22px;width: 35%">NIS/NISN</td>
                                            <td>:</td>
                                            <td>012334455</td>
                                        </tr>
                                        <tr style="line-height: 1; font-family: 'Times New Roman'; font-size: 9pt">
                                            <td style="padding-left:22px;width: 35%">Kelas</td>
                                            <td>:</td>
                                            <td>IXA</td>
                                        </tr>
                                        <tr style="line-height: 1; font-family: 'Times New Roman'; font-size: 9pt">
                                            <td style="padding-left:22px;width: 35%">Ruang/Sesi</td>
                                            <td>:</td>
                                            <td>1/2</td>
                                        </tr>
                                        <tr style="line-height: 1; font-family: 'Times New Roman'; font-size: 9pt">
                                            <td style="padding-left:22px;width: 35%">Username</td>
                                            <td>:</td>
                                            <td><b>umbk001</b></td>
                                        </tr>
                                        <tr style="line-height: 1; font-family: 'Times New Roman'; font-size: 9pt">
                                            <td style="padding-left:22px;width: 35%">Password</td>
                                            <td>:</td>
                                            <td><b>umbk001</b></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" style="padding-top: 6px; text-align: center;">
                                                <div id="prev-tandatangan"
                                                     style="width: 65%; margin-left: auto; font-family: 'Times New Roman'; font-size: 9pt; line-height: 1; background: url('<?= base_url() . $setting->tanda_tangan ?>') no-repeat center; background-size: 100px 60px">
                                                    <span id="prev-kota"><?= $setting->kota ?></span>, <span
                                                            id="prev-tanggal"><?= isset($kartu->tanggal) ? $kartu->tanggal : '' ?></span>
                                                    <br>
                                                    Kepala <?= $setting->satuan_pendidikan == '2' ? 'Madrasah' : 'Sekolah' ?>
                                                    <br>
                                                    <br>
                                                    <br>
                                                    <span id="prev-kepsek"><u><?= $setting->kepsek ?></u></span>
                                                    <p id="prev-nip" style="margin-top: 4px; margin-bottom: 4px">NIP: <?= $setting_rapor != null && $setting_rapor->nip_kepsek == '1' ? $setting->nip : '-' ?></p>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <br>
                        </div>
                    </div>
                    <div class="card my-shadow">
                        <div class="card-header">
                            <div class="card-title">
                                Cetak
                            </div>
                            <!--
							<div id="selector" class="card-tools btn-group">
								<button type="button" class="btn active btn-primary">By Kelas</button>
								<button type="button" class="btn btn-outline-primary">By Ruang</button>
							</div>
							-->
                        </div>
                        <div class="card-body pb-4">
                            <div class="row">
                                <div class="col-8">
                                    <div class="input-group">
                                        <?php
                                        echo form_dropdown(
                                            'kelas',
                                            $kelas,
                                            null,
                                            'id="kelas" class="form-control"'
                                        ); ?>
                                    </div>
                                    <div class="input-group">
                                        <?php
                                        echo form_dropdown(
                                            'ruang',
                                            $ruang,
                                            null,
                                            'id="ruang" class="form-control d-none"'
                                        ); ?>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <!--
                                    <button class="btn bg-blue text-white">
                                        <i class="fa fa-file-word-o"></i><span class="ml-1">Word</span>
                                    </button>
                                    <button class="btn bg-red text-white" id="btn-pdf">
                                        <i class="fa fa-file-pdf-o"></i><span class="ml-1">Pdf</span>
                                    </button>
                                    -->
                                    <button class="btn bg-success text-white" id="btn-print" disabled>
                                        <i class="fa fa-print"></i><span class="ml-1">Cetak</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card my-shadow">
                        <div class="card-header">
                            <div class="card-title">
                                Preview
                            </div>
                        </div>
                        <div class="card-body pb-4">
                            <div class="d-flex justify-content-center bg-gray-light" style="min-height: 300mm">
                                <div id="print-preview" class="m-2"></div>
                            </div>
                        </div>
                        <div id="loading-kartu" class="overlay d-none" role="status" aria-label="Memuat kartu peserta">
                            <div class="spinner-grow"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<!--
<script src="<?= base_url() ?>/assets/app/js/pdfobject.js"></script>
<script>PDFObject.embed("/pdf/sample-3pp.pdf", "#example1");</script>
-->
<script src="<?= base_url('assets/app/js/print-area.js?v=2') ?>"
        data-print-stylesheet="<?= base_url('assets/app/css/cbt-print.css') ?>"></script>
<script src="<?= base_url('assets/app/js/cbt-print-preview.js') ?>"></script>
<script>
    var oldVal1 = <?= json_encode((string) (isset($kartu->header_1) ? $kartu->header_1 : ""), JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE) ?>;
    var oldVal2 = <?= json_encode((string) (isset($kartu->header_2) ? $kartu->header_2 : ""), JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE) ?>;
    var oldVal3 = <?= json_encode((string) (isset($kartu->header_3) ? $kartu->header_3 : ""), JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE) ?>;
    var oldVal4 = <?= json_encode((string) (isset($kartu->header_4) ? $kartu->header_4 : ""), JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE) ?>;
    var oldTgl = <?= json_encode((string) (isset($kartu->tanggal) ? $kartu->tanggal : ""), JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE) ?>;
    var oldKota = <?= json_encode((string) ($setting->kota), JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE) ?>;
    var logoKiri = <?= json_encode((string) (base_url() . $setting->logo_kiri), JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE) ?>;
    var tandatangan = <?= json_encode((string) (base_url() . $setting->tanda_tangan), JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE) ?>;
    var kepsek = <?= json_encode((string) ($setting->kepsek), JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE) ?>;
    var nip = <?= json_encode((string) ($setting->nip), JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE) ?>;
    var satuanPend = <?= json_encode((string) ($setting->satuan_pendidikan), JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE) ?> === "2" ? "Madrasah" : "Sekolah";
    var printBy = 1;

    var raporSetting = <?= json_encode($setting_rapor, JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE) ?>;
    var nipKepsek = raporSetting != null && raporSetting.nip_kepsek === "1" ? nip : " -";

    function submitKartu() {
        $('#set-kartu').submit();
    }

    function createPrintPreview(data) {
        var pages = [];
        var cardsPerPage = 8;

        for (let start = 0; start < data.length; start += cardsPerPage) {
            var table = '<table class="cbt-print-cards">';
            var page = data.slice(start, start + cardsPerPage);
            page.forEach(function (siswa, i) {
                var ruang = siswa.kode_ruang || '-';
                var sesi = siswa.kode_sesi || '-';
                var td = '<div style="display: flex; justify-content: center; align-items: center;">' +
                    '<div class="cbt-print-card" style="width: 10cm">' +
                    '<table id="table-header-print" style="width: 100%; border-top: 1px solid black; border-bottom: 0;border-left: 1px solid black; border-right: 1px solid black">' +
                    '<tr>' +
                    '<td style="width:20%;">' +
                    '<img alt="Logo kiri" src="' + logoKiri + '" style="width:55px; height:55px; margin-left: 6px; margin-right: 6px; margin-top:4px;">' +
                    '</td>' +
                    '<td style="padding: 0 4px; text-align: center;">' +
                    '<div style="line-height: 1.1; font-family: \'Times New Roman\'; font-size: 8pt">' + oldVal1 + '</div>' +
                    '<div class="text-center" style="line-height: 1.1; font-family: \'Times New Roman\'; font-size: 9pt"><b>' + oldVal2 + '</b></div>' +
                    '<div class="text-center" style="line-height: 1.2; font-family: \'Times New Roman\'; font-size: 8pt">' + oldVal3 + '</div>' +
                    '<div class="text-center" style="line-height: 1.2; font-family: \'Times New Roman\'; font-size: 8pt">' + oldVal4 + '</div>' +
                    '</td>' +
                    '</tr>' +
                    '</table>' +
                    '<table id="table-body-print" style="width:100%;border: 1px solid black">' +
                    '<tr style="line-height: 1; font-family: \'Times New Roman\'; font-size: 10pt">' +
                    '<td style="padding-top:4px;padding-left:22px;width: 30%">No. Peserta</td>' +
                    '<td style="padding-top:4px;">:</td>' +
                    '<td style="padding-top:4px;width: 65%">' + (siswa.nomor_peserta || '-') + '</td>' +
                    '</tr>' +
                    '<tr style="line-height: 1; font-family: \'Times New Roman\'; font-size: 9pt">' +
                    '<td style="padding-left:22px;width: 30%">Nama</td>' +
                    '<td>:</td>' +
                    '<td>' + siswa.nama + '</td>' +
                    '</tr>' +
                    '<tr style="line-height: 1; font-family: \'Times New Roman\'; font-size: 9pt">' +
                    '<td style="padding-left:22px;width: 30%">NIS / NISN</td>' +
                    '<td>:</td>' +
                    '<td>' + siswa.nis + '/' + siswa.nisn + '</td>' +
                    '</tr>' +
                    '<tr style="line-height: 1; font-family: \'Times New Roman\'; font-size: 9pt">' +
                    '<td style="padding-left:22px;width: 30%">Kelas</td>' +
                    '<td>:</td>' +
                    '<td>' + siswa.nama_kelas + '</td>' +
                    '</tr>' +
                    '<tr style="line-height: 1; font-family: \'Times New Roman\'; font-size: 9pt">' +
                    '<td style="padding-left:22px;width: 30%">Ruang/Sesi</td>' +
                    '<td>:</td>' +
                    '<td>' + ruang + '/' + sesi + '</td>' +
                    '</tr>' +
                    '<tr style="line-height: 1; font-family: \'Times New Roman\'; font-size: 9pt">' +
                    '<td style="padding-left:22px;width: 30%">Username</td>' +
                    '<td>:</td>' +
                    '<td>' + siswa.username + '</td>' +
                    '</tr>' +
                    '<tr style="line-height: 1; font-family: \'Times New Roman\'; font-size: 9pt">' +
                    '<td style="padding-left:22px;width: 30%">Password</td>' +
                    '<td>:</td>' +
                    '<td>' + siswa.password + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td colspan="3" style="padding-top: 6px; text-align: center;">' +
                    '<div style="width: 65%; margin-left: auto; font-family: \'Times New Roman\'; font-size: 9pt; line-height: 1; background: url(' + tandatangan + ') no-repeat center; background-size: 100px 60px">' +
                    '<span>' + oldKota + '</span>, <span>' + oldTgl + '</span>' +
                    '<br>Kepala ' + satuanPend +
                    '<br>' +
                    '<br>' +
                    '<br>' +
                    '<span>' + kepsek + '</span>' +
                    '<p id="prev-nip" style="margin-top: 4px; margin-bottom: 4px">NIP: '+nipKepsek+'</p>' +
                    '</div>' +
                    '</td>' +
                    '</tr>' +
                    '</table>' +
                    '</div>' +
                    '</div>';
                if (i % 2 === 0) {
                    table += '<tr>';
                }
                table += '<td style="padding: 5px;">' + td + '</td>';
                if (i % 2 === 1) {
                    table += '</tr>';
                }
            });
            if (page.length % 2 === 1) {
                table += '<td></td></tr>';
            }
            pages.push('<div class="border my-shadow mb-3 pt-3 bg-white cbt-print-sheet">' +
                '<div class="pt-4 cbt-print-sheet-content" ' +
                'style="display:flex;justify-content:center;align-items:flex-start;background:white;width:210mm;height:297mm;padding:1mm">' +
                table + '</table></div></div>');
        }

        var preview = $("#print-preview");
        preview.html(pages.join('<div class="cbt-print-break" style="page-break-after: always"></div>'));
    }

    $(document).ready(function () {
        ajaxcsrf();

        var drEvent = $('.dropify').dropify({
            messages: {
                'default': 'Seret logo kesini atau klik',
                'replace': 'Seret atau klik<br>untuk mengganti logo',
                'remove': 'Hapus',
                'error': 'Ooops, ada kesalahan!!.'
            },
            error: {
                'fileSize': 'The file size is too big ({{ value }} max).',
                'minWidth': 'The image width is too small ({{ value }}}px min).',
                'maxWidth': 'The image width is too big ({{ value }}}px max).',
                'minHeight': 'The image height is too small ({{ value }}}px min).',
                'maxHeight': 'The image height is too big ({{ value }}px max).',
                'imageFormat': 'The image format is not allowed ({{ value }} only).'
            }
        });


        drEvent.on('dropify.beforeClear', function (event, element) {
            //return confirm("Hapus logo \"" + element.file.name + "\" ?");
        });

        drEvent.on('dropify.afterClear', function (event, element) {
            //console.log(element.element.id);
            if (element.element.id === 'logo-kiri') {
                logoKiri = '';
                $('#prev-logo-kiri').attr('src', '');
                /*
            } else if (element.element.id === 'tanda-tangan') {
                tandatangan = '';
                $('#prev-tandatangan').css(
                    {'background': 'url() no-repeat center'},
                    {'font-family': 'Times New Roman'},
                    {'font-size': '10pt'},
                    {'background-size': '100px 60px'}
                );
                */
            }
        });

        drEvent.on('dropify.errors', function (event, element) {
            console.log('Has Errors');
            $.toast({
                heading: "Error",
                text: "file rusak",
                icon: 'warning',
                showHideTransition: 'fade',
                allowToastClose: true,
                hideAfter: 5000,
                position: 'top-right'
            });
        });

        $("#logo-kiri").change(function () {
            var input = $(this)[0];
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#prev-logo-kiri').attr('src', e.target.result);
                };
                reader.readAsDataURL(input.files[0]);

                var form = new FormData($('#set-logo-kiri')[0]);
                uploadAttach(base_url + 'cbtcetak/uploadfile/logo_kiri', form);
            }
        });

        $("#header-1").on("change keyup paste", function () {
            var currentVal = $(this).val();
            if (currentVal === oldVal1) {
                return;
            }

            oldVal1 = currentVal;
            $('#prev-header-1').html(currentVal);
            //alert("changed!");
        });
        $("#header-2").on("change keyup paste", function () {
            var currentVal = $(this).val();
            if (currentVal === oldVal2) {
                return;
            }

            oldVal2 = currentVal;
            $('#prev-header-2').text(currentVal);
            //alert("changed!");
        });
        $("#header-3").on("change keyup paste", function () {
            var currentVal = $(this).val();
            if (currentVal === oldVal3) {
                return;
            }

            oldVal3 = currentVal;
            $('#prev-header-3').text(currentVal);
            //alert("changed!");
        });
        $("#header-4").on("change keyup paste", function () {
            var currentVal = $(this).val();
            if (currentVal === oldVal4) {
                return;
            }

            oldVal4 = currentVal;
            $('#prev-header-4').text(currentVal);
            //alert("changed!");
        });

        $('#input-tanggal').on('input', function (e) {
            var tgl = $(this).val();
            $('#prev-tanggal').text(tgl);
            oldTgl = tgl;
        });

        $('#set-kartu').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

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
                url: base_url + 'cbtcetak/savekartu',
                type: 'POST',
                data: $(this).serialize() + '&logo_kiri=' + logoKiri + '&tanda_tangan=' + tandatangan,
                success: function (response) {
                    console.log(response);
                    swal.fire({
                        title: 'Sukses',
                        text: "Header kartu berhasil disimpan",
                        icon: 'success',
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                    }).then(result => {
                        if (result.value) {
                            window.location.href = base_url + 'cbtcetak/kartupeserta';
                        }
                    });
                },
                error: function (xhr, error, status) {
                    console.log(xhr.responseText);
                    const err = JSON.parse(xhr.responseText)
                    swal.fire({
                        title: "Error",
                        text: err.Message,
                        icon: "error"
                    });
                }
            });
        });

        function uploadAttach(action, data) {
            $.ajax({
                type: "POST",
                enctype: 'multipart/form-data',
                url: action,
                data: data,
                processData: false,
                contentType: false,
                cache: false,
                timeout: 600000,
                success: function (data) {
                    if (data.src.includes('kiri')) {
                        logoKiri = data.src;
                        //console.log('kiri', data.src);
                    } else if (data.src.includes('tanda')) {
                        tandatangan = data.src;
                        //console.log('tandatangan', data.src);
                    }
                },
                error: function (xhr, error, status) {
                    console.log("error", xhr.responseText);
                    const err = JSON.parse(xhr.responseText)
                    swal.fire({
                        title: "Error",
                        text: err.Message,
                        icon: "error"
                    });
                }
            });
        }

        function deleteImage(src) {
            $.ajax({
                data: {src: src},
                type: "POST",
                url: base_url + "cbtcetak/deletefile",
                cache: false,
                success: function (response) {
                    console.log(response);
                }
            });
        }

        var preview = $('#print-preview');
        var kelas = $('#kelas');
        var ruang = $('#ruang');
        var loadPreview = createCbtPrintPreview($('#btn-print'), $('#loading-kartu'));
        var endpointKelas = <?= json_encode($this->ion_auth->is_admin() ? 'getsiswakartu' : 'getsiswakelas') ?>;

        function refreshPreview() {
            var byClass = printBy === 1;
            var group = (byClass ? kelas : ruang).val();
            preview.empty();
            if (!group) {
                loadPreview();
                return;
            }

            var filters = {};
            filters[byClass ? 'kelas' : 'ruang'] = group;
            loadPreview(byClass ? endpointKelas : 'getsiswaruang', filters, function (response) {
                if (!response || !Array.isArray(response.siswa) || !response.siswa.length) {
                    preview.text('Tidak ada data siswa.');
                    return false;
                }

                createPrintPreview(response.siswa);
                return true;
            });
        }

        kelas.prepend("<option value='' selected='selected' disabled>Pilih Kelas</option>");
        ruang.prepend("<option value='' selected='selected' disabled>Pilih Ruang</option>");
        kelas.add(ruang).change(refreshPreview);
        kelas.select2({theme: 'bootstrap4'});

        $("#btn-print").click(function () {
            preview.print(<?= json_encode($judul, JSON_HEX_TAG) ?>);
        });

        $('#selector button').click(function () {
            $(this).addClass('active').siblings().addClass('btn-outline-primary').removeClass('active btn-primary');

            if (!$('#kelas').is(':hidden')) {
                $('#kelas').addClass('d-none');
                $('#ruang').removeClass('d-none');
                printBy = 2;
            } else {
                $('#kelas').removeClass('d-none');
                $('#ruang').addClass('d-none');
                printBy = 1;
            }
            refreshPreview();
        });
    })

</script>

var jenisSoal;
var fieldLinks;

let dataJodohkan;
let adaJawaban = false;
let saveAndAddAnother = false;
let uploadsPending = 0;
let questionSaving = false;
let questionLoading = false;
let questionRequest;

$(document).ready(function() {
    ajaxcsrf();
    $('.textsoal').summernote({
        placeholder: 'Tulis Soal disini',
        tabsize: 2,
        minHeight: 100,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'formula']],
            ['view', ['fullscreen', 'codeview', 'help']],
            ['cleaner',['cleaner']],
        ],
        buttons: {formula: questionFormulaButton},
        callbacks: {
            onImageUpload: function (images) {
                uploadEditorImages(images, $(this));
            }
        },
    });

    $('.textstimulus').summernote({
        placeholder: 'Tulis stimulus / wacana disini (opsional)',
        tabsize: 2,
        minHeight: 80,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'formula']],
            ['view', ['codeview']],
        ],
        buttons: {formula: questionFormulaButton},
        callbacks: {
            onImageUpload: function (images) {
                uploadEditorImages(images, $(this));
            }
        },
    });

    $('.textjawaban').summernote({
        placeholder: 'Tulis Jawaban disini',
        tabsize: 2,
        minHeight: 50,
        toolbar: [
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontsize', ['fontsize']],
            //['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['view', ['fullscreen', 'codeview', 'help']],
            ['insert', ['picture', 'formula']],
        ],
        buttons: {formula: questionFormulaButton},
        callbacks: {
            onImageUpload: function (images) {
                uploadEditorImages(images, $(this));
            }
        },
    });

    $('.textjawaban-essai').summernote({
        placeholder: 'Tulis Jawaban disini',
        tabsize: 2,
        toolbar: [
            //['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            //['fontname', ['fontname']],
            //['fontsize', ['fontsize']],
            //['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['picture', 'formula']],
            //['view', ['fullscreen', 'codeview', 'help']],
            //['cleaner',['cleaner']],
        ],
        buttons: {formula: questionFormulaButton},
        callbacks: {
            onImageUpload: function (images) {
                uploadEditorImages(images, $(this));
            }
        },
    });

    $("#picupload").on('change', function () {
        if (!idSoal || questionLoading) {
            this.value = '';
            showDangerToast('Simpan soal sebelum menambahkan lampiran.');
            return;
        }
        var form = new FormData($("#addfile")[0]);
        //console.log('nama file', names_files);
        //bank_id: id_bank, nomor: number, jenis: jenis_soal
        uploadAttach(base_url + 'cbtbanksoal/uploadfile?id_soal='+idSoal, form);
    });

    $('#save-question').on('click', function () { saveAndAddAnother = false; });
    $('#save-add-another').on('click', function () { saveAndAddAnother = true; });

    $('#create').submit('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        if (questionLoading || questionSaving || uploadsPending) return;
        jenis = $('#jenis-id').val();
        $(this).find('.textsoal, .textstimulus, .textjawaban, .textjawaban2, .textjawaban-essai')
            .filter(':enabled').each(function () {
                const $editor = $(this);
                if ($editor.summernote('codeview.isActivated')) {
                    $editor.summernote('codeview.deactivate');
                }
                $editor.val($editor.summernote('isEmpty') ? '' : $editor.summernote('code'));
            });

        let formData = new FormData($('#create')[0]);
        formData.append('nomor_soal', nomor_soal)

        if (jenisSoal == '3') {
            if (!adaJawaban) {
                swal.fire({
                    title: "ERROR",
                    text: "JAWABAN tidak boleh kosong",
                    icon: "error",
                    showCancelButton: false
                });
                return
            }
            $.each(dataJodohkan, function( key, value ) {
                if (key === "jawaban") {
                    $.each(value, function (ks, vs) {
                        $.each(vs, function (k, v) {
                            formData.append('jawaban['+ks+']['+k+']',  v)
                        })
                    })
                } else {
                    formData.append(key, value)
                }
            });
        }

        if (inputKosong()) {
            swal.fire({
                title: "ERROR",
                text: "SOAL atau JAWABAN tidak boleh kosong",
                icon: "error",
                showCancelButton: false
            });
        } else {
            questionSaving = true;
            updateSaveButtons();
            $('#loading').removeClass('d-none');
            $.ajax({
                url: base_url + "cbtbanksoal/saveSoal",
                type: "POST",
                dataType: 'json',
                processData: false,
                contentType: false,
                data: formData,
                success: function (data) {
                    $('#loading').addClass('d-none');
                    console.log(data);
                    if (data.status === 'error') {
                        swal.fire({
                            title: "ERROR",
                            text: data.error || "Soal gagal disimpan ke database.",
                            icon: "warning",
                            showCancelButton: false
                        });
                    } else {
                        if (data.soal_id) {
                            idSoal = data.soal_id;
                            $('#soal-id').val(data.soal_id);
                            $('#method').val('edit');
                        }
                        showSuccessToast("Soal berhasil disimpan.");
                        if (jenis==='1' || jenis==='2') {
                            $('#btn-'+jenis+nomor_soal).removeClass('btn-outline-danger');
                            $('#btn-'+jenis+nomor_soal).addClass('btn-success');
                        } else {
                            $('#btn-'+jenis+nomor_soal).removeClass('btn-outline-danger');
                            $('#btn-'+jenis+nomor_soal).addClass('btn-primary');
                        }
                        if (saveAndAddAnother) {
                            questionSaving = false;
                            var $addButton = $('[data-bank="' + idBank + '"][data-nomor]:visible').first();
                            var nextNumber = Math.max(parseInt($addButton.data('nomor'), 10) || 0, parseInt(nomor_soal, 10) + 1);
                            $addButton.data('nomor', nextNumber + 1).attr('data-nomor', nextNumber + 1);
                            getSoalById(idBank, nextNumber, jenis + '' + nextNumber, jenis);
                        }
                    }
                }, error: function (xhr, status, error) {
                    $('#loading').addClass('d-none');
                    console.log("error", xhr.responseText);
                    var response = xhr.responseJSON || {};
                    showDangerToast(response.error || 'Soal gagal disimpan ke database.');
                }, complete: function () {
                    questionSaving = false;
                    updateSaveButtons();
                }
            });
        }
    });

    $('#tambah-jawaban-pg2').on('click', function () {
        const $options = $('#opsi-pg2');
        const keys = $options.find('.check-pg2').map(function () { return this.value; }).get();
        const key = Array.from('abcdefghijklmnopqrst').find(function (letter) {
            return !keys.includes(letter);
        });
        if (!key) {
            showDangerToast('Pilihan ganda kompleks maksimal 20 opsi.');
            return;
        }
        appendComplexOption($options, key, '', false);
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var tab = $(e.target);
        var target = tab.attr("href");
        var id, jenis;
        if (target == '#ganda') {
            id = '11';
            jenis = '1';
        } else if (target == '#kompleks') {
            id = '21';
            jenis = '2';
        } else if (target == '#jodoh') {
            id = '31';
            jenis = '3';
        } else if (target == '#isian') {
            id = '41';
            jenis = '4';
        } else if (target == '#essai') {
            id = '51';
            jenis = '5';
        } else if (target == '#benarsalah') {
            id = '61';
            jenis = '6';
        } else {
            console.log('id not defined');
        }

        getSoalById(idBank, 1, id, jenis);
    });

    var arrJenis = ['', 'PG', 'PG Kompleks', 'Menjodohkan', 'Isian Singkat', 'Uraian/essai', 'Benar/Salah'];
    $('#hapus-soal').on('click', function () {
        swal.fire({
            title: "HAPUS ?",
            html: "Soal berikut akan dihapus<br>Nomor: <b>" +nomor_soal+"</b><br>Jenis: <b>"+arrJenis[jenis]+"</b>",
            icon: "error",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Hapus!"
        }).then(result => {
            if(result.value){
                console.log($('#create').serialize());
                $.ajax({
                    url: base_url + "cbtbanksoal/hapussoal",
                    method: "POST",
                    data: $('#create').serialize(),
                    success: function (result) {
                        console.log("result", result);
                        var tit = result ? 'BERHASIL' : 'GAGAL';
                        var msg = result ? 'berhasil' : 'gagal';
                        var ic = result ? 'success' : 'error';
                        swal.fire({
                            title: tit,
                            text: "Soal " + msg + " dihapus",
                            icon: ic,
                            showCancelButton: false,
                            confirmButtonColor: "#3085d6",
                        }).then(result => {
                            if (result.value) {
                                window.location.reload();
                            }
                        });
                    }, error: function (xhr, status, error) {
                        console.log("error", xhr.responseText);
                        showDangerToast('ERROR!!');
                    }
                });
            }
        })

    });

    if (inArray(1, adaPg)) {

    }
    getSoalById(idBank, nomor_soal, jenis+''+nomor_soal, jenis);
});

function appendComplexOption($options, key, html, checked) {
    const $option = $('<div class="pg-kompleks mb-4 ml-3">' +
        '<div class="row mb-2"><div class="col-6">' +
        '<button type="button" class="btn btn-sm btn-default mr-4" onclick="deleteOpsiPg2(this)" aria-label="Hapus opsi">' +
        '<i class="fa fa-trash"></i></button>' +
        '<span class="abjad-pg2"><b></b></span></div>' +
        '<div class="col-6 text-right d-flex justify-content-end"><b>Jawaban benar</b>' +
        '<input class="check-pg2 ml-2" type="checkbox" style="width:24px;height:24px" name="jawaban_benar_pg2[]"></div></div>' +
        '<textarea class="textjawaban2" placeholder="Tulis jawaban" style="width:100%"></textarea></div>');
    $option.find('.abjad-pg2 b').text('Jawaban ' + key.toUpperCase());
    $option.find('.check-pg2').val(key).prop('checked', checked);
    const $editor = $option.find('.textjawaban2').attr({id: 'textjawaban2_' + key, name: 'jawaban2_' + key});
    $options.append($option);
    $editor.summernote({
        placeholder: 'Tulis Jawaban disini',
        tabsize: 2,
        minHeight: 50,
        toolbar: [
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontsize', ['fontsize']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['picture', 'formula']],
        ],
        buttons: {formula: questionFormulaButton},
        callbacks: {
            onImageUpload: function (files) {
                uploadEditorImages(files, $(this));
            }
        }
    });
    $editor.summernote('code', questionEditorHtml(html));
}

function updateSaveButtons() {
    $('#save-question, #save-add-another').prop('disabled', questionLoading || questionSaving || uploadsPending > 0);
}

async function uploadEditorImages(files, $editor) {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const $question = $('#textsoal');
    uploadsPending++;
    updateSaveButtons();
    try {
        for (const file of Array.from(files)) {
            if (file.type && !allowedTypes.includes(file.type)) {
                showDangerToast('Format gambar harus JPG, PNG, GIF, atau WebP');
                continue;
            }
            if (file.size > 4 * 1024 * 1024) {
                showDangerToast('Ukuran gambar maksimal 4 MB');
                continue;
            }
            const data = new FormData();
            data.append('file', file);
            data.append('bank_id', $('#bank-id').val());
            data.append($question.attr('data-name'), $question.attr('data-id'));

            try {
                const response = await $.ajax({
                    url: base_url + 'soalmedia/upload',
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    contentType: false,
                    processData: false
                });
                if (!response.status) {
                    showDangerToast(response.message || 'Gambar gagal diunggah');
                    continue;
                }
                if ($editor[0].isConnected) {
                    const image = new Image();
                    image.src = base_url + response.filename;
                    image.alt = file.name.replace(/\.[^.]+$/, '');
                    image.style.maxWidth = '100%';
                    image.style.height = 'auto';
                    $editor.summernote('editor.insertNode', image);
                }
            } catch (xhr) {
                showDangerToast((xhr.responseJSON || {}).message || 'Gambar gagal diunggah');
            }
        }
    } finally {
        uploadsPending--;
        updateSaveButtons();
    }
}

function questionEditorHtml(html) {
    const fragment = document.createElement('template');
    fragment.innerHTML = html || '';
    for (const script of fragment.content.querySelectorAll('script')) script.remove();
    resolveQuestionMedia(fragment.content, base_url);
    return fragment.innerHTML;
}

function inputKosong() {
    if ($('.textsoal').summernote('isEmpty')) return true;
    var kosong = true;
    if (jenis === '1') {
        var jumlahOpsiTerisi = 0;
        $('.textjawaban').each(function () {
            if (!$(this).summernote('isEmpty')) {
                jumlahOpsiTerisi++;
            }
        });

        var jawabanBenar = $('[name="jawaban_pg"]:checked').val();
        kosong = jumlahOpsiTerisi < 2 || !jawabanBenar;
        if (!kosong) {
            kosong = $('#textjawaban_' + jawabanBenar).summernote('isEmpty');
        }
    } else if (jenis === '2') {
        const $choices = $('.textjawaban2');
        const $checked = $('.check-pg2:checked');
        kosong = $choices.length < 2 || !$checked.length || $choices.toArray().some(function (choice) {
            return $(choice).summernote('isEmpty');
        });
    } else if (jenis === '3') {
        kosong = false;
    } else if (jenis === '4') {
        kosong = $('#jawaban-isian').val().trim() == "";
    } else if (jenis === '6') {
        kosong = $('#jawaban-bs').val() == null || $('#jawaban-bs').val() === '';
    } else {
        kosong = $('.textjawaban-essai').summernote('isEmpty');
    }
    return kosong;
}

function inArray(val, array) {
    var found = $.inArray(val, array);
    return found >= 0;
}

var old_id = 11;
function getSoalById(id_bank, number, id, jenis_soal) {
    if (questionSaving || uploadsPending) return;
    if (questionRequest) questionRequest.abort();
    jenisSoal = jenis_soal;
    $('#empty-soal').addClass('d-none');
    $('#not-empty-soal').addClass('d-none');

    $('#btn-'+old_id).removeClass('active');
	$('#btn-'+id).addClass('active');
	$('#jenis-id').val(jenis_soal);

	// Benar/Salah default: sembunyikan, cabang jenis '6' yang menampilkan
	$('#root-jawaban-bs').addClass('d-none');
	$('#jawaban-bs').attr('disabled', 'disabled').prop('required', false);

	if (jenis_soal === '1') {
        $('#nomor-soal').html('<b>Soal PG Nomor: ' + number + '</b>');

        $('#soal-area').removeClass('col-md-12');
        $('#soal-area').addClass('col-md-6');
        $('#jawaban-area').removeClass('col-md-12');
        $('#jawaban-area').addClass('col-md-6');

        // 1 enable
        $('#root-opsi-pg').removeClass('d-none');
        $('.textjawaban').removeAttr('disabled', 'disabled');
        $('#jawaban-benar').removeClass('d-none');
        $('#root-jawaban-pg').removeClass('d-none');
        $('[name="jawaban_pg"]').prop('required', true).prop('disabled', false);

        // 2 disable
        $('#root-opsi-pg2').addClass('d-none');
        $('.textjawaban2').attr('disabled', 'disabled');
        $('.check-pg2').attr('disabled', 'disabled');

        // 3 disable
        $('#root-opsi-jodohkan').addClass('d-none');
        $('#model-opsi').attr('disabled', 'disabled');
        $('#type-opsi').attr('disabled', 'disabled');

        // 4 disable
        $('#root-jawaban-isian').addClass('d-none');
        $('#jawaban-isian').attr('disabled', 'disabled');

        // 5 disable
        $('#root-jawaban-essai').addClass('d-none');
        $('#jawaban-essai').attr('disabled', 'disabled');
    } else if (jenis_soal === '2') {
        $('#nomor-soal').html('<b>Soal Kompleks Nomor: '+number + '</b>');

        $('#soal-area').removeClass('col-md-12');
        $('#soal-area').addClass('col-md-6');
        $('#jawaban-area').removeClass('col-md-12');
        $('#jawaban-area').addClass('col-md-6');

        // 1 disable
        $('#root-opsi-pg').addClass('d-none');
        $('.textjawaban').attr('disabled', 'disabled');
        $('#jawaban-benar').addClass('d-none');
        $('#root-jawaban-pg').addClass('d-none');
        $('[name="jawaban_pg"]').prop('required', false).prop('disabled', true);

        // 2 enable
        $('#root-opsi-pg2').removeClass('d-none');
        $('.textjawaban2').removeAttr('disabled');
        $('.check-pg2').removeAttr('disabled');

        // 3 disable
        $('#root-opsi-jodohkan').addClass('d-none');
        $('#model-opsi').attr('disabled', 'disabled');
        $('#type-opsi').attr('disabled', 'disabled');

        // 4 disable
        $('#root-jawaban-isian').addClass('d-none');
        $('#jawaban-isian').attr('disabled', 'disabled');

        // 5 disable
        $('#root-jawaban-essai').addClass('d-none');
        $('#jawaban-essai').attr('disabled', 'disabled');
    } else if (jenis_soal === '3') {
        $('#nomor-soal').html('<b>Soal Menjodohkan Nomor: '+number + '</b>');

        $('#soal-area').removeClass('col-md-6');
        $('#soal-area').addClass('col-md-12');
        $('#jawaban-area').removeClass('col-md-6');
        $('#jawaban-area').addClass('col-md-12');

        // 1 disable
        $('#root-opsi-pg').addClass('d-none');
        $('.textjawaban').attr('disabled', 'disabled');
        $('#root-jawaban-pg').addClass('d-none');
        $('[name="jawaban_pg"]').prop('required', false).prop('disabled', true);

        // 2 disable
        $('#root-opsi-pg2').addClass('d-none');
        $('.textjawaban2').attr('disabled', 'disabled');
        $('.check-pg2').attr('disabled', 'disabled');

        // 3 enable
        $('#root-opsi-jodohkan').removeClass('d-none');
        $('#model-opsi').removeAttr('disabled');
        $('#type-opsi').removeAttr('disabled');

        // 4 disable
        $('#jawaban-benar').addClass('d-none');
        $('#root-jawaban-isian').addClass('d-none');
        $('#jawaban-isian').attr('disabled', 'disabled');

        // 5 disable
        $('#root-jawaban-essai').addClass('d-none');
        $('#jawaban-essai').attr('disabled', 'disabled');
    } else if (jenis_soal === '4') {
        $('#nomor-soal').html('<b>Soal Isian Nomor: '+number + '</b>');

        $('#soal-area').removeClass('col-md-12');
        $('#soal-area').addClass('col-md-6');
        $('#jawaban-area').removeClass('col-md-12');
        $('#jawaban-area').addClass('col-md-6');

        // 1 disable
        $('#root-opsi-pg').addClass('d-none');
        $('.textjawaban').attr('disabled', 'disabled');
        $('#root-jawaban-pg').addClass('d-none');
        $('[name="jawaban_pg"]').prop('required', false).prop('disabled', true);

        // 2 disable
        $('#root-opsi-pg2').addClass('d-none');
        $('.textjawaban2').attr('disabled', 'disabled');
        $('.check-pg2').attr('disabled', 'disabled');

        // 3 disable
        $('#root-opsi-jodohkan').addClass('d-none');
        $('#model-opsi').attr('disabled', 'disabled');
        $('#type-opsi').attr('disabled', 'disabled');

        // 4 enable
        $('#jawaban-benar').removeClass('d-none');
        $('#root-jawaban-isian').removeClass('d-none');
        $('#jawaban-isian').removeAttr('disabled');

        // 5 disable
        $('#root-jawaban-essai').addClass('d-none');
        $('#jawaban-essai').attr('disabled', 'disabled');
        	} else if (jenis_soal === '6') {
        $('#nomor-soal').html('<b>Soal Benar/Salah Nomor: ' + number + '</b>');

        $('#soal-area').removeClass('col-md-6');
        $('#soal-area').addClass('col-md-12');
        $('#jawaban-area').removeClass('col-md-6');
        $('#jawaban-area').addClass('col-md-12');

        // 1 disable
        $('#root-opsi-pg').addClass('d-none');
        $('.textjawaban').attr('disabled', 'disabled');
        $('#root-jawaban-pg').addClass('d-none');
        $('[name="jawaban_pg"]').prop('required', false).prop('disabled', true);

        // 2 disable
        $('#root-opsi-pg2').addClass('d-none');
        $('.textjawaban2').attr('disabled', 'disabled');
        $('.check-pg2').attr('disabled', 'disabled');

        // 3 disable
        $('#root-opsi-jodohkan').addClass('d-none');
        $('#model-opsi').attr('disabled', 'disabled');
        $('#type-opsi').attr('disabled', 'disabled');

        // 4 disable
        $('#root-jawaban-isian').addClass('d-none');
        $('#jawaban-isian').attr('disabled', 'disabled');

        // 5 disable
        $('#root-jawaban-essai').addClass('d-none');
        $('#jawaban-essai').attr('disabled', 'disabled');

        // Benar/Salah enable
        $('#jawaban-benar').removeClass('d-none');
        $('#root-jawaban-bs').removeClass('d-none');
        $('#jawaban-bs').removeAttr('disabled').prop('required', true);
	} else {
		$('#nomor-soal').html('<b>Soal Essai Nomor: '+number + '</b>');

        $('#soal-area').removeClass('col-md-12');
        $('#soal-area').addClass('col-md-6');
        $('#jawaban-area').removeClass('col-md-12');
        $('#jawaban-area').addClass('col-md-6');

        // 1 disable
		$('#root-opsi-pg').addClass('d-none');
        $('.textjawaban').attr('disabled', 'disabled');
        $('#root-jawaban-pg').addClass('d-none');
        $('[name="jawaban_pg"]').prop('required', false).prop('disabled', true);

        // 2 disable
        $('#root-opsi-pg2').addClass('d-none');
        $('.textjawaban2').attr('disabled', 'disabled');
        $('.check-pg2').attr('disabled', 'disabled');

        // 3 disable
        $('#root-opsi-jodohkan').addClass('d-none');

        // 4 disable
        $('#root-jawaban-isian').addClass('d-none');
        $('#jawaban-isian').attr('disabled', 'disabled');

        // 5 enable
        $('#jawaban-benar').removeClass('d-none');
		$('#root-jawaban-essai').removeClass('d-none');
        $('#jawaban-essai').removeAttr('disabled');
	}

	$('#loading').removeClass('d-none');
    questionLoading = true;
    updateSaveButtons();
    old_id = id;
    nomor_soal = number;
    jenis = jenis_soal;
    questionRequest = $.ajax({
        type: 'GET',
        url: base_url + 'cbtbanksoal/getSoalByNomor',
        dataType: 'json',
        data: {bank_id: id_bank, nomor: number, jenis: jenis_soal},
        success: function (data) {
            const question = data || {};
            idSoal = question.id_soal || 0;
            dataFiles = Array.isArray(question.file) ? question.file : [];
            $('#soal-id').val(idSoal);
            $('#method').val(idSoal ? 'edit' : 'add');
            $('#textsoal').summernote('code', questionEditorHtml(question.soal));
            $('#textstimulus').summernote('code', questionEditorHtml(question.deskripsi));

            if (jenis_soal === '1') {
                $('.textjawaban').each(function () {
                    const key = this.name.slice(-1);
                    $(this).summernote('code', questionEditorHtml(question['opsi_' + key]));
                });
                const answer = String(question.jawaban || '').trim().toLowerCase();
                $('[name="jawaban_pg"]').each(function () {
                    this.checked = this.value === answer;
                });
            } else if (jenis_soal === '2') {
                const $options = $('#opsi-pg2').empty();
                const answers = Array.isArray(question.jawaban) ? question.jawaban : [];
                $.each(question.opsi_a || {a: '', b: ''}, function (key, html) {
                    appendComplexOption($options, key, html, answers.includes(key));
                });
            } else if (jenis_soal === '3') {
                adaJawaban = false;
                dataJodohkan = question.jawaban || {jawaban: [], model: '1', type: '2'};
                $('#jawaban-jodohkan').empty().linkerList({
                    data: dataJodohkan,
                    id: number,
                    viewMode: '3',
                    callback: function (id, data, hasLinks) {
                        dataJodohkan = data;
                        adaJawaban = hasLinks;
                    }
                });
            } else if (jenis_soal === '4') {
                $('#jawaban-isian').val($.trim(question.jawaban));
            } else if (jenis_soal === '6') {
                $('#jawaban-bs').val(String(question.jawaban || '').trim().toLowerCase()).change();
            } else {
                $('#jawaban-essai').summernote('code', questionEditorHtml(question.jawaban));
            }

            $('#audios, #videos').empty();
            for (const file of dataFiles) {
                const isAudio = ['audio/mpeg', 'audio/ogg'].includes(file.type);
                const $media = isAudio ? $('<audio id="audio-att">') : $('<video id="video-att" width="100" controls>');
                $media.append($('<source>').attr({src: base_url + file.src, type: file.type}));
                (isAudio ? $('#audios') : $('#videos')).append($media, $('<span>').text(file.file_name || file.src));
                $media.stylise({enableRestart: false});
            }

            $('#loading').addClass('d-none');
            $('#not-empty-soal').removeClass('d-none');
            $('[data-toggle="tooltip"]').tooltip();
        },
        error: function (xhr, status) {
            if (status !== 'abort') {
                $('#loading').addClass('d-none');
                showDangerToast((xhr.responseJSON || {}).message || 'Soal gagal dimuat.');
            }
        },
        complete: function () {
            questionLoading = false;
            updateSaveButtons();
        }
    });
}

function tambahSoalPg(e) {
    getSoalById($(e).data('bank'), $(e).data('nomor'), '1' + $(e).data('nomor'), '1');
}

function tambahSoalPg2(e) {
    getSoalById($(e).data('bank'), $(e).data('nomor'), '2' + $(e).data('nomor'), '2');
}

function tambahSoalBs(e) {
    getSoalById($(e).data('bank'), $(e).data('nomor'), '6' + $(e).data('nomor'), '6');
}

function tambahSoalJodohkan(e) {
    var nomor = $(e).data('nomor');
    var bank = $(e).data('bank');
    var mapel = $(e).data('mapel');

    $.ajax({
        url: base_url + "cbtbanksoal/tambahSoal",
        type: "POST",
        dataType: "JSON",
        data: {
            nomor: nomor,
            jenis: 3,
            bank: bank
        },
        success: function (data) {
            console.log(data);
            window.location.href = base_url + 'cbtbanksoal/buatsoal/'+bank+'?tab=3';
        }, error: function (xhr, status, error) {
            console.log("error", xhr.responseText);
            showDangerToast('Gagal menambah soal');
        }
    });
}

function tambahSoalIsian(e) {
    var nomor = $(e).data('nomor');
    var bank = $(e).data('bank');
    var mapel = $(e).data('mapel');

    $.ajax({
        url: base_url + "cbtbanksoal/tambahSoal",
        type: "POST",
        dataType: "JSON",
        data: {
            nomor: nomor,
            jenis: 4,
            bank: bank
        },
        success: function (data) {
            console.log(data);
            window.location.href = base_url + 'cbtbanksoal/buatsoal/'+bank+'?tab=4';
        }, error: function (xhr, status, error) {
            console.log("error", xhr.responseText);
            showDangerToast('Gagal menambah soal');
        }
    });
}

function tambahSoalEssai(e) {
    var nomor = $(e).data('nomor');
    var bank = $(e).data('bank');
    var mapel = $(e).data('mapel');

    $.ajax({
        url: base_url + "cbtbanksoal/tambahSoal",
        type: "POST",
        dataType: "JSON",
        data: {
            nomor: nomor,
            jenis: 5,
            bank: bank
        },
        success: function (data) {
            console.log(data);
            window.location.href = base_url + 'cbtbanksoal/buatsoal/'+bank+'?tab=5';
        }, error: function (xhr, status, error) {
            console.log("error", xhr.responseText);
            showDangerToast('Gagal menambah soal');
        }
    });
}

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
            console.log('result', data);
            if (data.status) {
                dataFiles = data.files;
                /*
                var item = {};
                item ['size'] = data.size;
                item ["type"] = data.type;
                item ["src"] = data.src;
                item ["name"] = data.filename;
                dataFiles.push(item);
                console.log(data.type);
                */
                createPreviewFile();
			} else {
                swal.fire({
                    title: "ERROR",
                    html: data.src,
                    icon: "error",
                    showCancelButton: false
                });
			}
        },
        error: function (e) {
            console.log("error", e.responseText);
            showDangerToast("file tidak terbaca");}
    });
}

function createPreviewFile(/*elem, event*/) {
    //var files = event.target.files;
    for (var j = 0; j < dataFiles.length; j++) {
        let file = dataFiles[j];
        //names_files.push(elem.get(0).files[j].name);
        if (file.file_name !== "") {

            var div = document.createElement("li");
            div.setAttribute("id", "f-" + file.alias);
            if (!$("#f-" + file.alias).length) {
                if (file.type.match('image')) {
                    div.innerHTML = "<img src='" + base_url + file.src + "'/>" +
                        "<div  class='post-thumb'>" +
                        "<div class='inner-post-thumb'>" +
                        "<a href='javascript:void(0);' data-id='" + file.alias + "' class='remove-pic'>" +
                        "<i class='fa fa-times' aria-hidden='true'></i></a>" +
                        "<div>" +
                        "</div>";
                    $("#media-list").prepend(div);
                } else if (file.type.match('video')) {
                    div.innerHTML = "<video src='" + file.src + "'></video>" +
                        "<div class='post-thumb'>" +
                        "<div  class='inner-post-thumb'>" +
                        "<a href='javascript:void(0);' data-id='" + file.alias + "' class='remove-pic'>" +
                        "<i class='fa fa-times' aria-hidden='true'></i></a>" +
                        "<div>" +
                        "</div>";
                    $("#media-list").prepend(div);
                } else {
                    div.innerHTML = "<img src='" + base_url + "/assets/app/img/document_file.png'>" +
                        "<div class='post-thumb'>" +
                        "<div  class='inner-post-thumb'>" +
                        "<a href='javascript:void(0);' data-id='" + file.alias + "' class='remove-pic'>" +
                        "<i class='fa fa-times' aria-hidden='true'></i></a>" +
                        "<div>" +
                        "</div>";
                    $("#media-list").prepend(div);
                }
            }
        }
    }
    console.log(dataFiles);
}

$('body').on('click', '.remove-pic', function() {
    $(this).parent().parent().parent().remove();
    var removeItem = $(this).attr('data-id');

    for (var i = 0; i < dataFiles.length; i++) {
        var cur = dataFiles[i];
        if (cur.alias === removeItem) {
            dataFiles.splice(i, 1);
            deleteImage(cur.src);
            break;
        }
    }
    console.log(dataFiles);
});

function deleteImage(src) {
    $.ajax({
        data: {src: src, soal_id: idSoal},
        type: "POST",
        url: base_url + "cbtbanksoal/deletefile",
        cache: false,
        success: function (response) {
            console.log(response);
        }
    });
}

function HasArabicCharacters(text){
    var arregex = /[\u0600-\u06FF]/;
    return arregex.test(text);
}

function deleteOpsiPg2(e) {
    const $option = $(e).closest('.pg-kompleks');
    $option.find('.textjawaban2').summernote('destroy');
    $option.remove();

    var opsi2 = $('#opsi-pg2');
    const $abjads = opsi2.find('.abjad-pg2')

    $abjads.each(function (index, sp) {
        $(sp).html('<b>Jawaban '+(index + 10).toString(36).toUpperCase()+'</b>')
    })

    const $teks = opsi2.find('.textjawaban2');
    $teks.each(function (index, area) {
        const abc = (index + 10).toString(36).toLowerCase();
        $(area).prop('id', 'textjawaban2_' + abc).prop('name', 'jawaban2_' + abc);
        $(area).closest('.pg-kompleks').find('.check-pg2').val(abc);
    })
}

function showEditDialog(el) {
    valSelected = $(el).closest('.float-right').prev();
    let dialog = $('#noteModal');
    let editorCode = $('#edit-dialog');
    dialog.modal('show')
    editorCode.summernote('code', valSelected.html())
}

function simpanValue() {
    let editorCode = $('#edit-dialog');
    valSelected.html(editorCode.summernote('code'));
    $('#noteModal').modal('hide');
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

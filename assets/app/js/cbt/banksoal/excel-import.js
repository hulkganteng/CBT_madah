(function ($) {
    'use strict';

    var $form = $('#excel-question-form');
    if (!$form.length) return;

    var $file = $('#excel-question-file');
    var $result = $('#excel-import-result');
    var $errors = $('#excel-import-errors');
    var $preview = $('#excel-import-preview');
    var $submit = $('#excel-import-submit');
    var isValid = false;

    $file.on('change', function () {
        var name = this.files.length ? this.files[0].name : 'Pilih file .xlsx atau .xls';
        $(this).next('.custom-file-label').text(name);
        resetPreview();
    });

    $('#excel-import-mode').on('change', resetPreview);

    $form.on('submit', function (event) {
        event.preventDefault();
        request('preview');
    });

    $submit.on('click', function () {
        if (!isValid) return;
        var replace = $('#excel-import-mode').val() === 'replace';
        var message = replace
            ? 'Seluruh soal lama pada bank ini akan dihapus dan diganti. Lanjutkan?'
            : 'Tambahkan semua soal valid dari file ini?';
        swal.fire({
            title: 'Konfirmasi import',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, import',
            cancelButtonText: 'Batal'
        }).then(function (choice) {
            if (choice.value) request('upload');
        });
    });

    function request(action) {
        if (!$file[0].files.length) {
            showError(['Pilih file Excel terlebih dahulu.']);
            return;
        }
        swal.fire({
            title: action === 'preview' ? 'Memeriksa file' : 'Mengimpor soal',
            text: 'Silakan tunggu...',
            allowOutsideClick: false,
            onOpen: function () { swal.showLoading(); }
        });

        $.ajax({
            url: base_url + 'excelsoal/' + action,
            method: 'POST',
            data: new FormData($form[0]),
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function (response) {
            swal.close();
            if (action === 'preview') {
                renderPreview(response);
                return;
            }
            swal.fire('Berhasil', response.message, 'success').then(function () {
                window.location.href = base_url + 'cbtbanksoal/detail/' + $form.find('[name="bank_id"]').val();
            });
        }).fail(function (xhr) {
            swal.close();
            var response = xhr.responseJSON || {};
            var messages = response.errors || [response.message || 'Import soal gagal diproses.'];
            showError(messages);
        });
    }

    function renderPreview(response) {
        $result.removeClass('d-none');
        $preview.empty();
        (response.preview || []).forEach(function (row) {
            $('<tr>')
                .append($('<td>').text(row.no))
                .append($('<td>').text(typeLabel(row.jenis)))
                .append($('<td>').text(row.soal))
                .append($('<td>').text(row.tampilkan === '1' ? 'Tampil' : 'Cadangan'))
                .appendTo($preview);
        });
        if (response.total > 50) {
            $('<tr>').append($('<td colspan="4" class="text-center text-muted">').text('Menampilkan 50 dari ' + response.total + ' soal.')).appendTo($preview);
        }
        showError(response.errors || []);
        isValid = response.valid === true;
        $submit.prop('disabled', !isValid);
    }

    function showError(messages) {
        $result.removeClass('d-none');
        $errors.empty();
        if (!messages.length) return;
        var $alert = $('<div class="alert alert-danger">');
        $('<strong>').text('Perbaiki data berikut:').appendTo($alert);
        var $list = $('<ul class="mb-0 pl-3">').appendTo($alert);
        messages.slice(0, 20).forEach(function (message) { $('<li>').text(message).appendTo($list); });
        if (messages.length > 20) $('<li>').text((messages.length - 20) + ' kesalahan lain tidak ditampilkan.').appendTo($list);
        $errors.append($alert);
        isValid = false;
        $submit.prop('disabled', true);
    }

    function resetPreview() {
        isValid = false;
        $result.addClass('d-none');
        $submit.prop('disabled', true);
    }

    function typeLabel(type) {
        return ({'1': 'Pilihan Ganda', '2': 'Pilihan Kompleks', '6': 'Benar/Salah'})[type] || type;
    }
})(jQuery);

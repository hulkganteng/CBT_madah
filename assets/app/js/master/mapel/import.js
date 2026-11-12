$(document).ready(function () {
    ajaxcsrf();
    // Basic
    $('.dropify').dropify({
            tpl: {
                wrap: '<div class="dropify-wrapper"></div>',
                loader: '<div class="dropify-loader"></div>',
                message: '<div class="dropify-message"><span class="file-icon" /> <p>{{ default }}</p></div>',
                preview: '<div class="dropify-preview"><span class="dropify-render"></span><div class="dropify-infos"><div class="dropify-infos-inner"><p class="dropify-infos-message">{{ replace }}</p></div></div></div>',
                filename: '<p class="dropify-filename"><span class="file-icon"></span> <span class="dropify-filename-inner"></span></p>',
                clearButton: '<button type="button" class="dropify-clear" onclick="onRemoved()">{{ remove }}</button>',
                errorLine: '<p class="dropify-error">{{ error }}</p>',
                errorsContainer: '<div class="dropify-errors-container"><ul></ul></div>'
            }
        },
        {
            error: {
                'fileSize': 'The file size is too big ({{ value }} max).',
                'minWidth': 'The image width is too small ({{ value }}}px min).',
                'maxWidth': 'The image width is too big ({{ value }}}px max).',
                'minHeight': 'The image height is too small ({{ value }}}px min).',
                'maxHeight': 'The image height is too big ({{ value }}px max).',
                'imageFormat': 'The image format is not allowed ({{ value }} only).'
            }
        });

    var drDestroy = $('#input-file-to-destroy').dropify();
    drDestroy = drDestroy.data('dropify');

    $('#toggleDropify').on('click', function (e) {
        e.preventDefault();
        if (drDestroy.isDropified()) {
            drDestroy.destroy();
        } else {
            drDestroy.init();
        }
    });

    // Used events
    var drEvente = $('#input-file-events-excel').dropify();
    var drEventw = $('#input-file-events-word').dropify();

    drEvente.on('dropify.beforeClear', function (event, element) {
        return confirm("Do you really want to delete \"" + element.file.name + "\" ?");
    });

    drEvente.on('dropify.afterClear', function (event, element) {
        //todo
    });

    drEvente.on('dropify.errors', function (event, element) {
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

    $('#input-file-events-excel').on('change', function () {
        previewSelectedFile(this, '#formPreviewExcel', base_url + 'datamapel/previewExcel');
    });

    $('#input-file-events-word').on('change', function () {
        previewSelectedFile(this, '#formPreviewWord', base_url + 'datamapel/previewWord');
    });

    $('#formUpload').submit('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        var rows = $('#tablePrev').tableToJSON();
        if (!rows.length) {
            showImportError('Tidak ada data mata pelajaran yang dapat diunggah.');
            return false;
        }

        var button = $('#btn-upload-mapel');
        button.prop('disabled', true).attr('aria-busy', 'true');
        var dataPost = $(this).serialize() + "&mapel=" + encodeURIComponent(JSON.stringify(rows));

        $.ajax({
            url: base_url + "datamapel/do_import",
            type: "POST",
            dataType: "JSON",
            data: dataPost,
            success: function (data) {
                if (data && data.status) {
                    window.location.href = base_url + 'datamapel';
                    return;
                }
                showImportError(data && data.message ? data.message : 'Data mata pelajaran gagal disimpan.');
            }, error: function (xhr, status, error) {
                var response = xhr.responseJSON;
                showImportError(response && response.message ? response.message : 'Data mata pelajaran gagal disimpan.');
            }, complete: function () {
                button.prop('disabled', $('#tablePrev tbody tr').length === 0).attr('aria-busy', 'false');
            }
        });
        return false;
    });
});

function previewSelectedFile(input, formSelector, action) {
    if (!input.files || !input.files.length) {
        clearPreview();
        return;
    }
    preview(action, new FormData($(formSelector)[0]), input.files[0].name);
}

function preview(action, data, filename) {
    $('#btn-upload-mapel').prop('disabled', true);
    $('#file-preview').html('<span class="text-muted"><i class="fas fa-spinner fa-spin mr-2"></i>Membaca file...</span>');
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
            var html = '';
            var i;
            var no = 1;

            if (filename == "") {
                html = '<span class="text-center">Pastikan anda telah mengisi format yang telah disediakan.</span>';
            } else {
                var obj;
                try {
                    obj = typeof data === 'string' ? JSON.parse(data) : data;
                } catch (error) {
                    showImportError('Respons preview tidak valid. Pastikan file memakai template mapel.');
                    return;
                }
                if (!Array.isArray(obj)) {
                    showImportError(obj && obj.message ? obj.message : 'File tidak dapat dibaca.');
                    return;
                }
                html = '<table id="tablePrev" class="table table-bordered">' +
                    '        <thead>' +
                    '        <tr>' +
                    '        <td>No</td>' +
                    '        <td>nama</td>' +
                    '        <td>kode</td>' +
                    '        </tr>' +
                    '        </thead>' +
                    '        <tbody>';
                for (i = 0; i < obj.length; i++) {
                    var nama = String(obj[i].nama || '').trim();
                    var kode = String(obj[i].kode || '').trim();
                    if (nama !== '' && kode !== '') {
                        html +=
                            '<tr>' +
                            '<td>' + no++ + '</td>' +
                            '<td>' + escapeHtml(nama) + '</td>' +
                            '<td>' + escapeHtml(kode) + '</td>' +
                            '</tr>';
                    }
                }
                html +=  '</tbody></table>';
                if (no === 1) {
                    showImportError('Tidak ada baris valid. Nama dan kode mata pelajaran wajib diisi.');
                    return;
                }
            }

            $('#file-preview').html(html);
            $('#btn-upload-mapel').prop('disabled', false);
        },
        error: function (xhr) {
            var message = xhr.responseText ? $('<div>').html(xhr.responseText).text().trim() : '';
            showImportError(message || 'File tidak terbaca. Gunakan template dan format yang didukung.');
        }
    });
}

function escapeHtml(value) {
    return $('<div>').text(value).html();
}

function showImportError(message) {
    $('#file-preview').html('<div class="alert alert-danger mb-0" role="alert"></div>');
    $('#file-preview .alert').text(message);
    $('#btn-upload-mapel').prop('disabled', true);
    $.toast({
        heading: 'Import gagal',
        text: message,
        icon: 'error',
        showHideTransition: 'fade',
        allowToastClose: true,
        hideAfter: 5000,
        position: 'top-right'
    });
}

function clearPreview() {
    $('#file-preview').html('<span class="text-center">Pastikan anda telah mengisi format yang telah disediakan.</span>');
    $('#btn-upload-mapel').prop('disabled', true);
}

function onRemoved() {
    $(".dropify-filename-inner").text("");
    clearPreview();
}

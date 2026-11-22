function initMasterImport(controller, excelMethod, columns) {
    const form = $('#formUpload');
    const input = $('#formInput');
    const button = form.find('button[type="submit"]');
    const preview = $('#file-preview');
    const files = $('#input-file-events-excel, #input-file-events-word');
    let requestVersion = 0;

    function clearPreview(message) {
        requestVersion++;
        input.val('');
        button.prop('disabled', true);
        preview.text(message);
    }

    clearPreview('Pilih file yang telah diisi menggunakan template.');
    files.dropify();
    files.on('dropify.afterClear dropify.errors', function () {
        clearPreview('Pilih file Excel atau Word untuk melihat pratinjau.');
    });

    files.on('change', function () {
        if (!this.files.length) {
            return;
        }
        clearPreview('Membaca file...');
        const version = requestVersion;
        const method = this.id === 'input-file-events-excel' ? excelMethod : 'previewWord';
        const upload = new FormData(this.form);
        $.ajax({
            url: base_url + controller + '/' + method,
            type: 'POST',
            dataType: 'json',
            data: upload,
            contentType: false,
            processData: false
        }).done(function (result) {
            if (version !== requestVersion) {
                return;
            }
            if (!result.status || !Array.isArray(result.rows) || !result.rows.length) {
                clearPreview(result.message || 'File tidak berisi data.');
                return;
            }
            const table = $('<table>', {class: 'table table-bordered'});
            const heading = $('<tr>').append($('<th>').text('No.'));
            columns.forEach(function (column) {
                heading.append($('<th>').text(column[1]));
            });
            table.append($('<thead>').append(heading));
            const body = $('<tbody>');
            result.rows.forEach(function (record, index) {
                const row = $('<tr>').append($('<td>').text(index + 1));
                columns.forEach(function (column) {
                    row.append($('<td>').text(record[column[0]] ?? ''));
                });
                body.append(row);
            });
            preview.empty().append(table.append(body));
            input.val(JSON.stringify(result.rows));
            button.prop('disabled', false);
        }).fail(function () {
            if (version === requestVersion) {
                clearPreview('File gagal dibaca. Periksa format file lalu coba lagi.');
            }
        });
    });

    form.on('submit', function (event) {
        event.preventDefault();
        if (button.prop('disabled') || !input.val()) {
            return;
        }
        button.prop('disabled', true);
        files.prop('disabled', true);
        $.ajax({
            url: base_url + controller + '/do_import',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json'
        }).done(function (result) {
            if (result.status) {
                window.location.href = base_url + controller;
                return;
            }
            showDangerToast(result.message || 'Gagal menyimpan data impor.');
            button.prop('disabled', false);
            files.prop('disabled', false);
        }).fail(function () {
            showDangerToast('Gagal menyimpan data impor.');
            button.prop('disabled', false);
            files.prop('disabled', false);
        });
    });
}

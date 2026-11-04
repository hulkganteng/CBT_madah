(function ($) {
    'use strict';

    var $form = $('#excel-question-form');
    if (!$form.length) return;

    var importId = null;
    var page = 1;
    var pages = 1;
    var filter = 'all';
    var items = {};

    $.getJSON(base_url + 'excelsoal/latest', {bank_id: $('#excel-import-card').data('bank-id')}).done(function (response) {
        if (response.import_id) {
            importId = response.import_id;
            loadDraft();
        }
    });

    $('#excel-question-file').on('change', function () {
        $(this).next('.custom-file-label').text(this.files.length ? this.files[0].name : 'Belum ada file dipilih');
    });

    $form.on('submit', function (event) {
        event.preventDefault();
        if (!$('#excel-question-file')[0].files.length) {
            showAlert('Pilih file Excel terlebih dahulu.', 'danger');
            return;
        }
        busy('Membaca file', 'Soal sedang diperiksa...');
        $.ajax({
            url: base_url + 'excelsoal/create',
            method: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function (response) {
            swal.close();
            importId = response.import_id;
            page = 1;
            loadDraft();
        }).fail(handleFailure);
    });

    $('#import-filters').on('click', 'button', function () {
        filter = $(this).data('filter');
        page = 1;
        $('#import-filters button').removeClass('active');
        $(this).addClass('active');
        loadDraft();
    });

    $('#page-prev').on('click', function () { if (page > 1) { page--; loadDraft(); } });
    $('#page-next').on('click', function () { if (page < pages) { page++; loadDraft(); } });

    $('#import-items').on('click', '.edit-import-item', function () {
        var item = items[$(this).data('id')];
        if (!item) return;
        var question = item.question;
        var $edit = $('#edit-import-form');
        $edit.find('[name="import_id"]').val(importId);
        $edit.find('[name="item_id"]').val(item.id);
        $edit.find('[name="type"]').val(question.type);
        $edit.find('[name="question"]').val(question.question);
        $edit.find('[name="explanation"]').val(question.explanation || '');
        ['A', 'B', 'C', 'D', 'E'].forEach(function (key) {
            $edit.find('[name="option_' + key.toLowerCase() + '"]').val(question.options[key] || '');
        });
        renderAnswerEditor(question.type, question.correct_answers || []);
        toggleOptions(question.type);
        $('#edit-import-item').modal('show');
    });

    $('#edit-import-form [name="type"]').on('change', function () {
        toggleOptions(this.value);
        renderAnswerEditor(this.value, []);
    });

    $('#edit-import-form').on('submit', function (event) {
        event.preventDefault();
        busy('Menyimpan perbaikan', 'Soal sedang diperiksa ulang...');
        $.ajax({
            url: base_url + 'excelsoal/updateItem',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json'
        }).done(function (response) {
            swal.close();
            $('#edit-import-item').modal('hide');
            showAlert(response.message, response.item.is_valid ? 'success' : 'warning');
            loadDraft();
        }).fail(handleFailure);
    });

    $('#commit-import').on('click', function () {
        var valid = parseInt($('#count-valid').text(), 10) || 0;
        var invalid = parseInt($('#count-invalid').text(), 10) || 0;
        if (!valid) {
            showAlert('Belum ada soal yang siap diimport.', 'warning');
            return;
        }
        var text = valid + ' soal siap diimport.' + (invalid ? ' ' + invalid + ' soal bermasalah tidak akan disimpan dan tetap tersedia pada draft.' : '');
        swal.fire({
            title: 'Import ' + valid + ' Soal?',
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Import ' + valid + ' Soal',
            cancelButtonText: 'Batal'
        }).then(function (choice) {
            if (!choice.value) return;
            postAction('commit', 'Menyimpan soal', function (response) {
                swal.fire('Berhasil', response.message, 'success').then(function () {
                    if (response.remaining) loadDraft();
                    else window.location.href = base_url + 'cbtbanksoal/detail/' + $('#excel-import-card').data('bank-id');
                });
            });
        });
    });

    $('#cancel-import').on('click', function () {
        swal.fire({
            title: 'Batalkan Import?',
            text: 'Draft tidak akan dimasukkan ke bank soal.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Batalkan Import',
            cancelButtonText: 'Kembali'
        }).then(function (choice) {
            if (!choice.value) return;
            postAction('cancel', 'Membatalkan draft', function (response) {
                swal.fire('Dibatalkan', response.message, 'success').then(function () {
                    window.location.href = base_url + 'cbtbanksoal/detail/' + $('#excel-import-card').data('bank-id');
                });
            });
        });
    });

    function loadDraft() {
        if (!importId) return;
        $.getJSON(base_url + 'excelsoal/session', {import_id: importId, page: page, per_page: 20, filter: filter})
            .done(function (response) {
                $('#import-draft').removeClass('d-none');
                $('#import-filename').text(response.import.original_filename || 'Draft Import');
                $('#count-total').text(response.import.total_items);
                $('#count-valid').text(response.import.valid_items);
                $('#count-invalid').text(response.import.invalid_items);
                pages = response.pagination.pages;
                page = response.pagination.page;
                $('#page-info').text('Halaman ' + page + ' dari ' + pages);
                $('#page-prev').prop('disabled', page <= 1);
                $('#page-next').prop('disabled', page >= pages);
                renderItems(response.items);
                $('#commit-import').prop('disabled', parseInt(response.import.valid_items, 10) === 0);
            }).fail(handleFailure);
    }

    function renderItems(rows) {
        var $root = $('#import-items').empty();
        items = {};
        if (!rows.length) {
            $root.append($('<div class="alert alert-light border">').text('Tidak ada soal pada filter ini.'));
            return;
        }
        rows.forEach(function (item) {
            items[item.id] = item;
            var q = item.question;
            var $card = $('<div class="card border mb-3">');
            var $body = $('<div class="card-body">').appendTo($card);
            var statusClass = item.is_valid ? 'badge-success' : 'badge-warning';
            var statusText = item.is_valid ? 'Siap' : 'Perlu Diperbaiki';
            $('<div class="d-flex justify-content-between mb-2">')
                .append($('<strong>').text('Soal dari baris ' + item.row_number + ' · ' + typeLabel(q.type)))
                .append($('<span class="badge p-2">').addClass(statusClass).text(statusText))
                .appendTo($body);
            $('<div class="mb-3">').text(plain(q.question)).appendTo($body);
            if (q.type !== 'true_false') {
                var $options = $('<div class="mb-3">').appendTo($body);
                Object.keys(q.options || {}).forEach(function (key) {
                    $('<div>').text(key + '. ' + plain(q.options[key])).appendTo($options);
                });
            }
            var answers = (q.correct_answers || []).map(function (key) {
                return q.type === 'true_false' ? (key === 'B' ? 'Benar' : 'Salah') : key + '. ' + plain((q.options || {})[key] || '');
            });
            $('<div class="mb-2">').append($('<strong>').text('Jawaban benar: ')).append(document.createTextNode(answers.join(', ') || 'Belum dipilih')).appendTo($body);
            renderMessages($body, item.errors, 'danger');
            renderMessages($body, item.warnings, 'warning');
            if (!item.imported) {
                $('<button type="button" class="btn btn-sm btn-outline-primary edit-import-item">')
                    .attr('data-id', item.id).text('Perbaiki').appendTo($body);
            }
            $root.append($card);
        });
    }

    function renderMessages($root, messages, type) {
        (messages || []).forEach(function (message) {
            $('<div class="alert py-2 mb-2">').addClass('alert-' + type).text(message).appendTo($root);
        });
    }

    function renderAnswerEditor(type, selected) {
        var $root = $('#edit-answers').empty();
        if (type === 'true_false') {
            addAnswer($root, 'radio', 'B', 'Benar', selected);
            addAnswer($root, 'radio', 'S', 'Salah', selected);
            return;
        }
        ['A', 'B', 'C', 'D', 'E'].forEach(function (key) {
            addAnswer($root, type === 'pg' ? 'radio' : 'checkbox', key, key, selected);
        });
    }

    function addAnswer($root, inputType, value, label, selected) {
        var id = 'draft-answer-' + value;
        var $wrap = $('<div class="custom-control mb-1">').addClass(inputType === 'radio' ? 'custom-radio' : 'custom-checkbox');
        $('<input class="custom-control-input" name="correct_answers[]">')
            .attr({type: inputType, id: id, value: value})
            .prop('checked', selected.indexOf(value) !== -1).appendTo($wrap);
        $('<label class="custom-control-label">').attr('for', id).text(label).appendTo($wrap);
        $root.append($wrap);
    }

    function toggleOptions(type) {
        $('#edit-options').toggleClass('d-none', type === 'true_false');
    }

    function postAction(action, title, done) {
        busy(title, 'Silakan tunggu...');
        var csrf = $form.find('input[type="hidden"][name!="bank_id"]').first();
        var data = {import_id: importId};
        if (csrf.length) data[csrf.attr('name')] = csrf.val();
        $.post(base_url + 'excelsoal/' + action, data, null, 'json').done(done).fail(handleFailure);
    }

    function busy(title, text) {
        swal.fire({title: title, text: text, allowOutsideClick: false, onOpen: function () { swal.showLoading(); }});
    }

    function handleFailure(xhr) {
        swal.close();
        var response = xhr.responseJSON || {};
        var message = response.message || 'Proses gagal. Silakan coba lagi.';
        if (response.errors && response.errors.length) message += '\n' + response.errors.join('\n');
        showAlert(message, 'danger');
    }

    function showAlert(message, type) {
        $('#import-alert').empty().append($('<div class="alert">').addClass('alert-' + type).text(message));
    }

    function typeLabel(type) {
        return {pg: 'Pilihan Ganda', pg_complex: 'Pilihan Ganda Kompleks', true_false: 'Benar/Salah'}[type] || 'Jenis belum dikenali';
    }

    function plain(value) {
        return $('<div>').html(value || '').text().replace(/\s+/g, ' ').trim();
    }
})(jQuery);

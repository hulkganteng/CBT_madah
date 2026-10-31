(function ($) {
    'use strict';

    var $guide = $('#admin-first-exam-guide');
    if (!$guide.length) return;

    var storageKey = 'garudacbt.adminFirstExamGuide';
    var saved = {};
    try {
        saved = JSON.parse(localStorage.getItem(storageKey) || '{}');
    } catch (e) {
        saved = {};
    }

    function sync() {
        var $steps = $guide.find('[data-guide-step]');
        var total = $steps.length;
        var done = 0;
        var nextHref = null;
        var nextLabel = null;

        $steps.each(function () {
            var $step = $(this);
            var key = $step.data('guide-step');
            var status = $step.data('guide-status');
            var complete = status === 'done' || (status === 'manual' && saved[key] === true);
            var $check = $step.find('[data-guide-check]');

            $step.toggleClass('is-done', complete).toggleClass('is-manual', status === 'manual');
            if ($check.length) $check.prop('checked', complete);
            $step.find('[data-guide-status-label]').text(complete ? 'Selesai' : (status === 'manual' ? 'Periksa' : 'Perlu perhatian'));
            if (complete) done++;
            if (!complete && !nextHref) {
                nextHref = $step.find('a.btn-primary').attr('href');
                nextLabel = $step.find('h3').text();
            }
        });

        var percent = total ? Math.round(done / total * 100) : 0;
        $('#admin-guide-progress-bar').css('width', percent + '%').attr('aria-valuenow', percent);
        $('#admin-guide-progress-percent').text(percent + '%');
        $('#admin-guide-progress-label').text(done === total ? 'Semua langkah sudah diperiksa' : done + ' dari ' + total + ' langkah selesai');
        if (nextHref) {
            $('#admin-guide-next').attr('href', nextHref).html('Lanjutkan: ' + nextLabel + ' <i class="fas fa-arrow-right ml-1" aria-hidden="true"></i>');
        } else {
            $('#admin-guide-next').addClass('d-none');
        }
    }

    $('#admin-guide-toggle').on('click', function () {
        var expanded = $(this).attr('aria-expanded') === 'true';
        $(this).attr('aria-expanded', String(!expanded)).text(expanded ? 'Buka' : 'Ciutkan');
        $guide.toggleClass('is-collapsed', expanded);
    });

    $('#admin-guide-dismiss').on('click', function () {
        $guide.addClass('d-none');
    });

    $guide.on('change', '[data-guide-check]', function () {
        saved[$(this).data('guide-check')] = $(this).is(':checked');
        try { localStorage.setItem(storageKey, JSON.stringify(saved)); } catch (e) {}
        sync();
    });

    sync();
}(jQuery));

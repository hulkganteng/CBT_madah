(function (window, document, $) {
    'use strict';

    var state = {
        scheduleId: null,
        active: false,
        pageUrl: window.location.href.split('#')[0],
        configKeyHash: '',
        requestHash: '',
        sebVersion: '',
        heartbeatTimer: null
    };

    function readSebKeys(done) {
        var seb = window.SafeExamBrowser;
        if (!seb || !seb.security) {
            done();
            return;
        }
        state.sebVersion = seb.version || '';
        var capture = function () {
            state.configKeyHash = seb.security.configKey || '';
            state.requestHash = seb.security.browserExamKey || '';
            done();
        };
        if (typeof seb.security.updateKeys === 'function') {
            var completed = false;
            var finish = function () {
                if (completed) return;
                completed = true;
                capture();
            };
            try {
                seb.security.updateKeys(finish);
                window.setTimeout(finish, 1500);
            } catch (e) {
                finish();
            }
        } else {
            capture();
        }
    }

    function guardData(options) {
        if (!state.scheduleId || !options.url || options.url.indexOf(base_url) !== 0) return;
        var values = {
            seb_page_url: state.pageUrl,
            seb_config_key_hash: state.configKeyHash,
            seb_request_hash: state.requestHash,
            seb_version: state.sebVersion
        };
        if (window.CBT_CSRF && window.CBT_CSRF.name) {
            values[window.CBT_CSRF.name] = window.CBT_CSRF.hash;
        }
        if (window.FormData && options.data instanceof FormData) {
            Object.keys(values).forEach(function (key) { options.data.set(key, values[key]); });
            return;
        }
        if ($.isPlainObject(options.data)) {
            $.extend(options.data, values);
            return;
        }
        var encoded = $.param(values);
        options.data = typeof options.data === 'string' && options.data.length ? options.data + '&' + encoded : encoded;
    }

    function status(startButton) {
        $.ajax({
            url: base_url + 'examguard/status/' + state.scheduleId,
            method: 'POST',
            dataType: 'json'
        }).done(function (result) {
            if (startButton) {
                $(startButton).prop('disabled', !result.status);
            }
            $(document).trigger('examguard:status', [result]);
        }).fail(function (xhr) {
            var result = xhr.responseJSON || {status: false, message: 'Verifikasi keamanan ujian gagal.'};
            if (startButton) $(startButton).prop('disabled', true);
            $(document).trigger('examguard:status', [result]);
        });
    }

    function event(type, metadata) {
        if (!state.active) return;
        $.ajax({
            url: base_url + 'examguard/event',
            method: 'POST',
            data: {jadwal: state.scheduleId, event_type: type, metadata: JSON.stringify(metadata || {})}
        });
    }

    function heartbeat() {
        $.ajax({
            url: base_url + 'examguard/heartbeat',
            method: 'POST',
            dataType: 'json',
            data: {jadwal: state.scheduleId}
        }).done(function (result) {
            $(document).trigger('examguard:heartbeat-ok', [result]);
        }).fail(function (xhr) {
            var result = xhr.responseJSON || {};
            $(document).trigger('examguard:heartbeat-failed', [result]);
            /* PRD Bab 7 protokol TKA: server memutuskan, client mematuhi.
               Dihentikan proktor / waktu habis / sesi diambil PC lain
               -> keluar dari halaman ujian, bukan sekadar menampilkan pesan. */
            if (xhr.status === 403 || xhr.status === 409) {
                if (state.heartbeatTimer) {
                    window.clearInterval(state.heartbeatTimer);
                    state.heartbeatTimer = null;
                }
                state.active = false;
                window.alert(result.message || 'Sesi ujian Anda telah dihentikan.');
                window.location.href = base_url + 'siswa/cbt';
            }
        });
    }

    function bindSignals() {
        document.addEventListener('visibilitychange', function () {
            event(document.hidden ? 'PAGE_HIDDEN' : 'PAGE_VISIBLE');
        });
        document.addEventListener('fullscreenchange', function () {
            event(document.fullscreenElement ? 'FULLSCREEN_ENTER' : 'FULLSCREEN_EXIT');
        });
        window.addEventListener('offline', function () { event('OFFLINE'); });
        window.addEventListener('online', function () { event('ONLINE'); });
        document.addEventListener('paste', function (e) {
            event('PASTE_ATTEMPT', {target: (e.target && e.target.tagName) || ''});
        });
        document.addEventListener('copy', function () {
            event('COPY_ATTEMPT');
        });
        document.addEventListener('contextmenu', function () {
            event('CONTEXT_MENU');
        });
    }

    window.ExamGuard = {
        init: function (options) {
            state.scheduleId = parseInt(options.scheduleId, 10);
            state.active = !!options.active;
            $.ajaxPrefilter(guardData);
            readSebKeys(function () {
                status(options.startButton || null);
                if (state.active) {
                    heartbeat();
                    state.heartbeatTimer = window.setInterval(heartbeat, 30000);
                }
            });
            bindSignals();
        },
        event: event
    };
})(window, document, window.jQuery);

(function (window, document, $) {
    'use strict';

    var jadwalId = 0;
    var timer = null;
    var INTERVAL = 10000;

    function pad(n) {
        return ('0' + n).slice(-2);
    }

    function sisaWaktu(detik) {
        if (detik === null || detik === undefined) return '-';
        var h = Math.floor(detik / 3600);
        var m = Math.floor((detik % 3600) / 60);
        var s = detik % 60;
        return pad(h) + ':' + pad(m) + ':' + pad(s);
    }

    function labelStatus(row) {
        if (row.kicked) return '<span class="badge badge-danger">Terkunci—perlu reset</span>';
        if (row.status === 'SUBMITTED') return '<span class="badge badge-success">Selesai</span>';
        if (row.status === 'EXPIRED') return '<span class="badge badge-secondary">Waktu habis</span>';
        if (row.status !== 'IN_PROGRESS') return '<span class="badge badge-secondary">' + row.status + '</span>';
        if (!row.online) return '<span class="badge badge-warning">Terputus</span>';
        return '<span class="badge badge-info">Mengerjakan</span>';
    }

    function labelKoneksi(row) {
        if (row.detik_sejak_heartbeat === null) return '<span class="text-muted">-</span>';
        var s = row.detik_sejak_heartbeat;
        if (s <= 90) return '<span class="text-success">' + s + 's lalu</span>';
        return '<span class="text-danger">' + s + 's lalu</span>';
    }

    function badgeJml(n, warna) {
        return n > 0 ? '<span class="badge badge-' + warna + '">' + n + '</span>' : '<span class="text-muted">0</span>';
    }

    function barisAksi(row) {
        var html = '';
        if (row.status === 'IN_PROGRESS') {
            html += '<button type="button" class="btn btn-xs btn-warning mr-1 btn-waktu" data-id="' + row.id_siswa +
                '" data-nama="' + encodeURIComponent(row.nama || '') + '" title="Tambah Waktu"><i class="fa fa-clock"></i></button>';
            if (row.kicked) {
                html += '<button type="button" class="btn btn-xs btn-success mr-1 btn-unkick" data-id="' + row.id_siswa + '" title="Reset Login"><i class="fa fa-unlock"></i> Reset Login</button>';
            } else {
                html += '<button type="button" class="btn btn-xs btn-danger mr-1 btn-kick" data-id="' + row.id_siswa + '" title="Hentikan Ujian"><i class="fa fa-ban"></i></button>';
            }
        }
        html += '<button type="button" class="btn btn-xs btn-info btn-detail" data-attempt="' + row.attempt_id + '"><i class="fa fa-list"></i></button>';
        return html;
    }

    function render(peserta) {
        var online = 0, selesai = 0, pelanggaran = 0;
        var html = '';
        if (!peserta.length) {
            html = '<tr><td colspan="10" class="text-center text-muted">Belum ada peserta pada jadwal ini.</td></tr>';
        }
        peserta.forEach(function (row, i) {
            if (row.online) online++;
            if (row.status === 'SUBMITTED') selesai++;
            pelanggaran += row.total_pelanggaran;
            var tambahan = row.extra_minutes > 0
                ? '<span class="badge badge-primary">+' + row.extra_minutes + 'm</span>'
                : '<span class="text-muted">0</span>';
            html += '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + (row.nis || '-') + '</td>' +
                '<td>' + (row.nama || '-') + '</td>' +
                '<td class="text-center">' + labelStatus(row) + '</td>' +
                '<td class="text-center">' + sisaWaktu(row.sisa_detik) + '</td>' +
                '<td class="text-center">' + labelKoneksi(row) + '</td>' +
                '<td class="text-center">' + badgeJml(row.pelanggaran_tab, 'warning') + '</td>' +
                '<td class="text-center">' + badgeJml(row.pelanggaran_fullscreen, 'danger') + '</td>' +
                '<td class="text-center">' + tambahan + '</td>' +
                '<td class="text-center text-nowrap">' + barisAksi(row) + '</td>' +
                '</tr>';
        });
        $('#isi-aktivitas').html(html);
        $('#stat-total').text(peserta.length);
        $('#stat-online').text(online);
        $('#stat-pelanggaran').text(pelanggaran);
        $('#stat-selesai').text(selesai);
    }

    function muat() {
        if (!jadwalId) return;
        $.getJSON(base_url + 'examactivity/data/' + jadwalId, function (res) {
            if (!res.status) return;
            render(res.peserta || []);
            $('#info-refresh').text('diperbarui ' + new Date().toLocaleTimeString());
            var batas = res.jadwal && res.jadwal.tgl_selesai ? res.jadwal.tgl_selesai : '';
            $('#peringatan-batas-jadwal')
                .toggleClass('d-none', batas === '')
                .text(batas === '' ? '' : 'Jadwal ini ditutup pada ' + batas + '. Setelah tanggal itu peserta tidak bisa melanjutkan.');
        });
    }

    function mulai() {
        if (timer) return;
        timer = window.setInterval(muat, INTERVAL);
        $('#toggle-auto').removeClass('btn-success').addClass('btn-secondary')
            .find('span').text('Auto refresh');
    }

    function stop() {
        if (timer) {
            window.clearInterval(timer);
            timer = null;
        }
        $('#toggle-auto').removeClass('btn-secondary').addClass('btn-success')
            .find('span').text('Auto refresh');
    }

    function aksi(url, data, sukses) {
        $.post(base_url + url, data, function (res) {
            if (res.status) {
                sukses(res);
            } else {
                window.alert(res.message || 'Aksi gagal.');
            }
        }, 'json').fail(function (xhr) {
            var res = xhr.responseJSON || {};
            window.alert(res.message || 'Aksi gagal.');
        });
    }

    $(function () {
        $('#pilih-jadwal').on('change', function () {
            jadwalId = parseInt($(this).val(), 10) || 0;
            stop();
            if (jadwalId) {
                muat();
                mulai();
            } else {
                $('#isi-aktivitas').html('<tr><td colspan="10" class="text-center text-muted">Belum ada peserta.</td></tr>');
            }
        });

        $('#toggle-auto').on('click', function () {
            if (timer) { stop(); } else { mulai(); }
        });

        $('#refresh-now').on('click', muat);

        $(document).on('click', '.btn-kick', function () {
            var id = $(this).data('id');
            if (!window.confirm('Hentikan ujian peserta ini?')) return;
            aksi('examactivity/kick', {jadwal: jadwalId, siswa: id, alasan: 'Dihentikan proktor'}, muat);
        });

        $(document).on('click', '.btn-unkick', function () {
            var id = $(this).data('id');
            var alasan = window.prompt('Masukkan alasan Reset Login / Buka Kunci:');
            if (alasan === null) return;
            alasan = $.trim(alasan);
            if (alasan === '') {
                window.alert('Alasan Reset Login wajib diisi.');
                return;
            }
            aksi('examactivity/unkick', {jadwal: jadwalId, siswa: id, alasan: alasan}, muat);
        });

        $(document).on('click', '.btn-waktu', function () {
            $('#waktu-siswa').val($(this).data('id'));
            $('#waktu-nama').text(decodeURIComponent($(this).data('nama')));
            $('#waktu-menit').val(5);
            $('#waktu-alasan').val('');
            $('#modal-waktu').modal('show');
        });

        $('#simpan-waktu').on('click', function () {
            var alasan = $.trim($('#waktu-alasan').val());
            if (alasan === '') {
                window.alert('Alasan penambahan waktu wajib diisi.');
                return;
            }
            aksi('examactivity/extend', {
                jadwal: jadwalId,
                siswa: $('#waktu-siswa').val(),
                menit: $('#waktu-menit').val(),
                alasan: alasan
            }, function (res) {
                $('#modal-waktu').modal('hide');
                window.alert(res.message);
                muat();
            });
        });

        $(document).on('click', '.btn-detail', function () {
            var id = $(this).data('attempt');
            $.getJSON(base_url + 'examactivity/detail/' + id, function (res) {
                if (!res.status) return;
                var teks = (res.events || []).map(function (ev) {
                    return ev.event_time + '  ' + ev.event_type;
                }).join('\n');
                window.alert(teks === '' ? 'Belum ada aktivitas tercatat.' : teks);
            });
        });
    });
})(window, document, window.jQuery);

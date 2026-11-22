//var dataUpload;
$(document).ready(function () {
    ajaxcsrf();

    $('#formguru').on('submit', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        console.log('klik submit');
        var btn = $('#submit');

        btn.attr('disabled', 'disabled').text('Wait...');
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
            url: base_url + "dataguru/create",
            data: $(this).serialize(),
            dataType: "JSON",
            type: 'POST',
            success: function (response) {
                btn.removeAttr('disabled').text('Simpan');
                if (response.status) {
                    swal.fire('Sukses', 'Data Berhasil disimpan', 'success')
                        .then((result) => {
                            if (result.value) {
                                $("#nama_guru").val("");
                                $("#nip").val("");
                                $("#username").val("");
                                $('#password').val("");
                                //window.location.href = base_url+'dataguru';
                            }
                        });
                } else {
                    let errs = '';
                    $.each(response.errors || {}, function (key, val) {
                        errs += val
                        $('[name="' + key + '"]').closest('.form-group').addClass('has-error');
                        $('[name="' + key + '"]').nextAll('.help-block').eq(0).text(val);
                        if (val === '') {
                            $('[name="' + key + '"]').closest('.form-group').removeClass('has-error').addClass('has-success');
                            $('[name="' + key + '"]').nextAll('.help-block').eq(0).text('');
                        }
                    });
                    swal.fire({
                        title: "Gagal",
                        html: errs || 'Gagal menyimpan data guru.',
                        icon: "error"
                    });
                }
            },error: function (xhr, status, error) {
                btn.removeAttr('disabled').text('Simpan');
                swal.fire({
                    title: "Gagal",
                    html: 'Gagal menyimpan data',
                    icon: "error"
                });
            }
        })
    });

    initMasterImport('dataguru', 'previewExcel', [
        ['nama', 'Nama'],
        ['nip', 'NIP/NUPTK'],
        ['kode', 'Kode'],
        ['username', 'Username']
    ]);
});

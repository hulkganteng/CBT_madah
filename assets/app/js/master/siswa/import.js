var dataSiswa;
$(document).ready(function () {
    ajaxcsrf();

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
        //dataUpload = '';
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

    $('#input-file-events-excel').on('change', function (e) {
        var file = this.files[0];
        if (!file) return;
        dataSiswa = [];
        $('#submit-excel').attr('disabled', 'disabled');
        $('#file-preview').html('<span class="text-muted"><i class="fas fa-spinner fa-spin mr-2"></i>Membaca file...</span>');
        var form = new FormData($("#formPreviewExcel")[0]);
        preview(base_url + 'datasiswa/previewExcel', form, file.name);
    });

    $('#formUpload').on('submit', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        if (!dataSiswa || !dataSiswa.length) {
            $.toast({
                heading: "Perhatian",
                text: "Pilih file dan tunggu preview muncul sebelum upload.",
                icon: 'warning',
                showHideTransition: 'fade',
                allowToastClose: true,
                hideAfter: 5000,
                position: 'top-right'
            });
            return false;
        }

		var dataPost = $(this).serialize() + "&siswa=" + JSON.stringify(dataSiswa);
		console.log("data:", dataPost);

		var url = typeImport === "add" ? "datasiswa/do_import" : "datasiswa/updateall";
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
            url: base_url + url,
            type: "POST",
            data: dataPost,
            success: function (data) {
				console.log("response:", data);
                try {
                    data = typeof data === 'string' ? JSON.parse(data) : data;
                } catch (ex) {
                    $('#file-preview').html(data);
                    swal.fire({ title: "Gagal", html: 'Respons server tidak valid', icon: "error" });
                    return;
                }
                //window.history.back();
                if (data.status) {
                    swal.fire({
                        title: "Berhasil",
                        text: "Data siswa berhasil diimpor",
                        icon: "success"
                    }).then(result => {
                        if (result.value) {
                            window.history.back();
                        }
                    });
                } else {
                    let arrNisnDup = [];
                    let arrNisDup = [];
                    let arrUserDup = [];
                    $.each(data.errors, function (idx, o) {
                        console.log(idx, o);
                        if (o.nisn !== "") arrNisnDup.push("<br />" + idx + ": " +o.nisn);
                        if (o.nis !== "") arrNisDup.push("<br />" + idx + ": " +o.nis);
                        if (o.username !== "") arrUserDup.push("<br />" + idx + ": " +o.username);
                        });
                    let err = arrNisnDup.length ? `<b>${arrNisnDup.join("' ")}</b>` : '';
                    err += arrNisDup.length ? `<br /><b>${arrNisDup.join("' ")}</b>` : '';
                    err += arrUserDup.length ? `<br /><b>${arrUserDup.join("' ")}</b>` : '';
                    swal.fire({
                        title: "Gagal",
                        html: 'cek kembali siswa berikut: <br />'+err+'',
                        icon: "error"
                    }).then(result => {});
                }
            }, error: function (xhr, status, error) {
				$('#file-preview').html(xhr.responseText);
				console.log("error:", xhr);
                swal.fire({
                    title: "Gagal",
                    html: 'Gagal menyimpan data',
                    icon: "error"
                });
            }
        });
        return false;
    });

    $.fn.toJson = function(){
        if(!this.is('table')){
            return;
        }

        var results = [];

        this.find('table tr').each(function(indx, obj){
            var tds = $(obj).children('td');
            results.push(tds);
        });

        return results;
    }
});

function preview(action, data, filename) {
    console.log(filename);
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

            let arrNisn = [];
            let arrNis = [];
            let arrUser = [];
            let arrNisnDup = [];
            let arrNisDup = [];
            let arrUserDup = [];
            let arrUserEmpty = [];
            let arrSiswaDup = [];
            if (filename === "") {
                html = '<span class="text-center">Pastikan anda telah mengisi format yang telah disediakan.</span>';
            } else {
				try {
                    var dataResult = typeof data === 'string' ? JSON.parse(data) : data;

                    if (!dataResult.status) {
                        dataSiswa = [];
                        $('#submit-excel').attr('disabled', 'disabled');
                        $('#file-preview').html(
                            '<div class="alert alert-default-danger mb-0" role="alert">' +
                            (dataResult.message || 'File Excel tidak dapat dibaca.') +
                            '</div>'
                        );
                        return;
                    }

					dataSiswa = normalizeRows(dataResult.siswa);
                    var dataExists = normalizeRows(dataResult.exist);
                    if (!dataSiswa.length) {
                        throw new Error('Respons server tidak berisi data siswa.');
                    }
                    console.log("data", dataResult);
					html = '<table id="tableprev" class="table table-sm table-striped table-bordered nowrap w-100">' +
						'        <thead>' +
						'        <tr>' +
						'        <td class="text-center">No</td>' +
						'        <td>Nama</td>' +
						'        <td>NIS</td>' +
						'        <td>NISN</td>' +
						'        <td>Jenis_Kelamin</td>' +
						'        <td>Username</td>' +
						'        <td>Password</td>' +
						'        </tr>' +
						'        </thead>' +
						'        <tbody>';
					for (i = 0; i < dataSiswa.length; i++) {
                        const siswa = dataSiswa[i];
                        if (dataExists.length) {
                            var siswaDup = findDuplicateStudent(dataExists, siswa);
                            if (siswaDup) arrSiswaDup.push(siswaDup.nama);
                        }
                        var nisn = String(siswa.nisn || '').trim();
                        var nis = String(siswa.nis || '').trim();
                        var username = String(siswa.username || '').trim();

					    if (nisn && arrNisn.indexOf(nisn) !== -1) {
                            arrNisnDup.push(siswa.nisn)
                        } else if (nisn) {
                            arrNisn.push(nisn)
                        }
                        if (nis && arrNis.indexOf(nis) !== -1) {
                            arrNisDup.push(siswa.nis)
                        } else if (nis) {
                            arrNis.push(nis)
                        }
                        if (!username) {
                            arrUserEmpty.push((i + 1) + '. ' + (siswa.nama || '(nama kosong)'));
                        } else if (arrUser.indexOf(username) !== -1) {
                            arrUserDup.push(username)
                        } else {
                            arrUser.push(username)
                        }

						html +=
							'<tr>' +
							'<td class="text-center">' + no++ + '</td>' +
							'<td>' + siswa.nama + '</td>' +
							'<td>' + siswa.nis + '</td>' +
							'<td>' + siswa.nisn + '</td>' +
							'<td>' + siswa.jenis_kelamin + '</td>' +
							'<td>' + siswa.username + '</td>' +
							'<td>' + siswa.password + '</td>' +
							'</tr>';
					}
					html +=  '</tbody></table>';
				} catch (e) {
                    console.log('error', e)
					dataSiswa = [];
                    $('#submit-excel').attr('disabled', 'disabled');
                    var renderMessage = e && e.message
                        ? e.message
                        : 'Gagal menampilkan data, periksa format file yang diimpor.';
                    $('#file-preview').html('<div class="alert alert-default-danger mb-0" role="alert">' + escapeHtml(renderMessage) + '</div>');
					showDangerToast(renderMessage);
                    return;
				}
            }
            //console.log("duplikatNisn", arrNisnDup);
            //console.log("duplikatNis", arrNisDup);
            //console.log("duplikatUsername", arrUserDup);
            let htmlError = $('<ul><b>DUPLIKAT TEMPLATE</b></ul>');
            let invalidTemp = false;
            if (arrNisnDup.length) {
                invalidTemp = true;
                htmlError.append($(`<li>Ada ${arrNisnDup.length} duplikat NISN, silakan cek kembali NISN berikut: <br /><b>${arrNisnDup.join(", ")}</b></li>`))
            }
            if (arrNisDup.length) {
                invalidTemp = true;
                htmlError.append($(`<li>Ada ${arrNisDup.length} duplikat NIS, silakan cek kembali NIS berikut: <br /><b>${arrNisDup.join(", ")}</b></li>`))
            }
            if (arrUserDup.length) {
                invalidTemp = true;
                htmlError.append($(`<li>Ada ${arrUserDup.length} duplikat USERNAME, silakan cek kembali USERNAME berikut: <br /><b>${arrUserDup.join(", ")}</b></li>`))
            }
            if (arrUserEmpty.length) {
                invalidTemp = true;
                htmlError.append($(`<li>Ada ${arrUserEmpty.length} siswa dengan USERNAME kosong. Kolom USERNAME wajib diisi dan harus berbeda untuk setiap siswa:<br /><b>${formatPreviewList(arrUserEmpty)}</b></li>`))
            }

            let invalidDb = false;
            let dbError = $('<ul><b>DUPLIKAT DATABASE</b></ul>');
            if (arrSiswaDup.length > 0) {
                invalidDb = true;
                dbError.append($(`<li><b>${arrSiswaDup.length}</b> Siswa menggunakan NISN/NIS/USERNAME yang sudah digunakan dalam DATABASE, silakan cek kembali Siswa berikut: <br /><b>${arrSiswaDup.join(", ")}</b></li>`))
            }

            let alert = $('<div class="alert alert-default-danger align-content-center" role="alert"></div>');
            if (invalidTemp) alert.append(htmlError)
            if (invalidDb) alert.append(dbError)

            const content = $('#file-preview')
            content.html('')
            if ((invalidTemp || invalidDb) && typeImport === "add") {
                content.append(alert)
            }
            content.append(html)

            //var attrId = document.getElementById("formInput");
            //attrId.setAttribute("value", data);

			$('#tableprev').DataTable({
				"paging":   false,
				"ordering": false,
				"info":     false,
				"scrollX": 	true
			});
			if (typeImport === "add" && (invalidTemp || invalidDb)) {
                $('#submit-excel').attr('disabled', 'disabled');
            } else {
                $('#submit-excel').removeAttr('disabled');
            }
		},
        error: function (e) {
            console.log("error", e.responseText);
            dataSiswa = [];
            $('#submit-excel').attr('disabled', 'disabled');
            var message = e.responseJSON && e.responseJSON.message
                ? e.responseJSON.message
                : 'Preview gagal diproses oleh server.';
            $('#file-preview').html('<div class="alert alert-default-danger mb-0" role="alert">' + message + '</div>');
            showDangerToast(message);
        }
    });
}

function normalizeRows(rows) {
    if (Array.isArray(rows)) return rows;
    if (!rows || typeof rows !== 'object') return [];

    return Object.keys(rows).map(function (key) {
        return rows[key];
    });
}

function findDuplicateStudent(rows, siswa) {
    var nisn = String(siswa.nisn || '').trim();
    var nis = String(siswa.nis || '').trim();
    var username = String(siswa.username || '').trim();

    for (var i = 0; i < rows.length; i++) {
        var row = rows[i] || {};
        if ((nisn && String(row.nisn || '').trim() === nisn) ||
            (nis && String(row.nis || '').trim() === nis) ||
            (username && String(row.username || '').trim() === username)) {
            return row;
        }
    }
    return null;
}

function escapeHtml(value) {
    return $('<div>').text(value == null ? '' : String(value)).html();
}

function formatPreviewList(items) {
    var visible = items.slice(0, 20).map(escapeHtml);
    if (items.length > 20) {
        visible.push('... dan ' + (items.length - 20) + ' siswa lainnya');
    }
    return visible.join('<br>');
}

function parse(data) {

    $.htmlParser(data, {
        start: function () {
            // 'this' is a jQuery object representing the current node
            console.log('Start tag: <' + this.prop('tagName') + '>');
        },
        end: function () {
            console.log('End tag: </' + this.prop('tagName') + '>');
        },
        text: function () {
            console.log('Text: ' + this.text());
        },
        comment: function (text) {
            console.log('Comment: ' + this.text());
        }
    });
}

/*
function onFileChange(input){
    require("docx2html")(input.files[0]).then(function(converted){
        console.log(converted.toString())
    })
}*/

function onRemoved() {
    $(".dropify-filename-inner").text("");
}

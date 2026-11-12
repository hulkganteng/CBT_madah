var table;

$(document).ready(function() {
    ajaxcsrf();

    table = $("#users").DataTable({
        initComplete: function() {
            var api = this.api();
            $("#users_filter input")
                .off(".DT")
                .on("keyup.DT", function(e) {
                    api.search(this.value).draw();
                });
        },
        oLanguage: {
            sProcessing: "loading..."
        },
        processing: true,
        serverSide: true,
        ajax: {
            url: base_url + "usertu/data",
            type: "POST"
        },
        columns: [
            {
                data: "id",
                orderable: false,
                searchable: false
            },
            { data: "first_name" },
            { data: "last_name" },
            { data: "username" },
            { data: "email" },
            { data: "level" },
            { data: "created_on" }
        ],
        columnDefs: [
            {
                targets: 5,
                data: "level",
                render: function(data, type, row, meta) {
                    return `<div class="text-center">
                            <span class="badge badge-info">${data}</span>
                        </div>`;
                }
            },
            {
                targets: 7,
                orderable: false,
                searchable: false,
                title: "Status",
                data: "active",
                render: function(data, type, row, meta) {
                    if (data === "1") {
                        return `<div class="text-center">
                                <span class="badge bg-green">Active</span>
                            </div>`;
                    } else {
                        return `<div class="text-center">
                                <span class="badge bg-red">Not Active</span>
                            </div>`;
                    }
                }
            },
            {
                targets: 8,
                data: "id",
                render: function(data, type, row, meta) {
                    return `<div class="text-center">
                        <a type="button" class="btn btn-xs btn-warning" href="${base_url}usertu/edit/${data}">
                            <i class="fa fa-pencil-alt text-white"></i>
                        </a>
                        <button type="button" class="btn btn-xs btn-danger" onclick="hapus(${data})">
                            <i class="fa fa-trash text-white"></i>
                        </button>
                    </div>`;
                }
            }
        ],
        order: [[1, "asc"]],
        rowId: function(a) {
            return a;
        },
        rowCallback: function(row, data, iDisplayIndex) {
            var info = this.fnPagingInfo();
            var page = info.iPage;
            var length = info.iLength;
            var index = page * length + (iDisplayIndex + 1);
            $("td:eq(0)", row).html(index);
        }
    });

    table
        .buttons()
        .container()
        .appendTo("#users_wrapper .col-md-6:eq(0)");

    $('#create').on('submit', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        $.ajax({
            url: base_url + "usertu/create",
            type: "POST",
            dataType: "JSON",
            data: $(this).serialize(),
            success: function (data) {
                $('#createTuModal').modal('hide').data('bs.modal', null);
                if (data.status) {
                    showSuccessToast('Data TU berhasil disimpan.');
                    table.ajax.reload();
                } else {
                    let msg = 'Gagal menyimpan data TU.';
                    if (data.errors) {
                        msg = Object.values(data.errors).filter(Boolean).join('<br>');
                    }
                    showDangerToast(msg);
                }
            },
            error: function (xhr, status, error) {
                $('#createTuModal').modal('hide').data('bs.modal', null);
                showDangerToast(status);
            }
        });
        return false;
    });
});

function reload_ajax() {
    table.ajax.reload();
}

function hapus(id) {
    swal.fire({
        title: "Anda yakin?",
        text: "Data akan dihapus.",
        type: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Hapus!"
    }).then(result => {
        if (result.value) {
            $.getJSON(base_url + "usertu/delete/" + id, function(data) {
                swal.fire({
                    title: data.status ? "Berhasil" : "Gagal",
                    text: data.status
                        ? "User berhasil dihapus"
                        : (data.message || "User gagal dihapus"),
                    type: data.status ? "success" : "error"
                });
                reload_ajax();
            });
        }
    });
}

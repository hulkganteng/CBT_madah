// A print button becomes available only when the current filters have a printable preview.
function createCbtPrintPreview(button, loading) {
    let request;
    button.prop('disabled', true);

    return function (endpoint, filters, render) {
        if (request) {
            const previousRequest = request;
            request = null;
            previousRequest.abort();
        }
        button.prop('disabled', true);
        loading.toggleClass('d-none', !endpoint);
        if (!endpoint) {
            return;
        }

        const currentRequest = $.ajax({
            url: base_url + 'cbtcetak/' + endpoint,
            data: filters,
            dataType: 'json'
        });
        request = currentRequest;

        currentRequest.done(function (response) {
            if (request === currentRequest) {
                button.prop('disabled', !render(response));
            }
        }).fail(function (xhr, status) {
            if (request === currentRequest && status !== 'abort') {
                Swal.fire({text: 'Data cetak gagal dimuat.', icon: 'error'});
            }
        }).always(function () {
            if (request === currentRequest) {
                loading.addClass('d-none');
                request = null;
            }
        });
    };
}

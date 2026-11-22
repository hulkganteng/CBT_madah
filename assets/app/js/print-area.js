const printAreaStylesheet = document.currentScript.dataset.printStylesheet;

// Print a snapshot of the selected content in an isolated document.
jQuery.fn.print = function (docTitle) {
    const source = this[0];
    if (!source || (!source.children.length && !source.textContent.trim())) {
        return this;
    }

    const frame = document.createElement('iframe');
    frame.title = 'Pratinjau cetak';
    frame.setAttribute('aria-hidden', 'true');
    frame.style.cssText = 'position:absolute;left:-10000px;width:210mm;height:1px;border:0';
    document.body.appendChild(frame);

    const printWindow = frame.contentWindow;
    const printDocument = frame.contentDocument;
    printDocument.open();
    printDocument.write('<!doctype html><html><head></head><body></body></html>');
    printDocument.close();
    printDocument.title = docTitle || document.title;
    printDocument.documentElement.lang = document.documentElement.lang;

    const base = printDocument.createElement('base');
    base.href = document.baseURI;
    printDocument.head.appendChild(base);

    function resourceReady(element) {
        return new Promise(function (resolve) {
            element.addEventListener('load', resolve, {once: true});
            element.addEventListener('error', resolve, {once: true});
        });
    }

    const resources = [];
    document.querySelectorAll('style').forEach(function (style) {
        printDocument.head.appendChild(printDocument.importNode(style, true));
    });
    if (printAreaStylesheet) {
        const stylesheet = printDocument.createElement('link');
        stylesheet.rel = 'stylesheet';
        stylesheet.href = printAreaStylesheet;
        resources.push(resourceReady(stylesheet));
        printDocument.head.appendChild(stylesheet);
    }

    printDocument.body.className = 'print-area';
    printDocument.body.innerHTML = source.innerHTML;
    printDocument.querySelectorAll('img').forEach(function (img) {
        img.loading = 'eager';
        if (!img.complete) {
            resources.push(resourceReady(img));
        }
    });
    printDocument.querySelectorAll('[style*="background"]').forEach(function (element) {
        const url = element.style.backgroundImage.match(/^url\("(.*)"\)$/);
        if (url && url[1]) {
            const image = new printWindow.Image();
            resources.push(resourceReady(image));
            image.src = url[1];
        }
    });

    const ready = Promise.all(resources).then(function () {
        return printDocument.fonts && printDocument.fonts.ready;
    });
    let resourceTimeout;
    const deadline = new Promise(function (resolve) {
        resourceTimeout = setTimeout(resolve, 10000);
    });

    Promise.race([ready, deadline]).then(function () {
        clearTimeout(resourceTimeout);
        printWindow.addEventListener('afterprint', function () {
            setTimeout(function () { frame.remove(); }, 0);
        }, {once: true});
        printWindow.focus();
        printWindow.print();
    }).catch(function (error) {
        frame.remove();
        console.error('Gagal membuka dialog cetak', error);
    });

    return this;
};

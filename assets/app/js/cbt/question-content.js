function resolveQuestionMedia(container, baseUrl) {
    const root = baseUrl.replace(/\/+$/, '') + '/';
    const media = container.querySelectorAll('img[src], audio[src], video[src], audio source[src], video source[src]');
    for (const item of media) {
        const src = item.getAttribute('src').trim();
        if (!src || /^(?:data|blob):/i.test(src)) continue;

        // Older banks store both relative paths and URLs from previous installations.
        const uploadedPath = src.match(/(?:^|\/)(uploads\/bank_soal\/[^?#]+)([?#].*)?$/i);
        if (uploadedPath) {
            const resolved = root + uploadedPath[1] + (uploadedPath[2] || '');
            if (resolved !== src) item.setAttribute('src', resolved);
        }
    }
}

function makeQuestionTablesScrollable(container) {
    for (const table of container.querySelectorAll('table')) {
        if (table.parentElement.closest('.table-responsive')) continue;
        const wrapper = document.createElement('div');
        wrapper.className = 'table-responsive';
        table.before(wrapper);
        wrapper.append(table);
    }
}

async function embedQuestionImages(container) {
    const downloads = new Map();
    const images = container.querySelectorAll('img[src]');
    const results = await Promise.all(Array.from(images, async function (image) {
        const src = image.getAttribute('src');
        if (!src || /^data:/i.test(src)) return true;

        if (!downloads.has(image.src)) {
            downloads.set(image.src, fetch(image.src).then(function (response) {
                if (!response.ok) throw new Error('Image download failed');
                return response.blob();
            }).then(function (blob) {
                if (!blob.type.startsWith('image/')) throw new Error('Invalid image response');
                return new Promise(function (resolve, reject) {
                    const reader = new FileReader();
                    reader.onload = function () { resolve(reader.result); };
                    reader.onerror = function () { reject(reader.error); };
                    reader.readAsDataURL(blob);
                });
            }));
        }
        try {
            image.setAttribute('src', await downloads.get(image.src));
            return true;
        } catch (error) {
            return false;
        }
    }));
    return results.filter(function (success) { return !success; }).length;
}

(function () {
    const limits = {trust: false, maxExpand: 300, maxSize: 30};
    const delimiters = [
        {left: '\\(', right: '\\)', display: false},
        {left: '\\[', right: '\\]', display: true},
        {left: '$$', right: '$$', display: true},
    ];

    window.renderQuestionMath = function (element) {
        if (!element || typeof window.renderMathInElement !== 'function') return;

        window.renderMathInElement(element, {
            ...limits,
            delimiters: delimiters,
            ignoredClasses: ['katex', 'katex-display', 'katex-error'],
            throwOnError: false,
        });
    };

    window.questionFormulaButton = function (context) {
        return $.summernote.ui.button({
            contents: 'ƒx',
            tooltip: 'Sisipkan rumus LaTeX',
            click: function () {
                context.invoke('editor.saveRange');
                swal.fire({
                    title: 'Sisipkan rumus LaTeX',
                    input: 'textarea',
                    inputPlaceholder: '\\frac{1}{2}',
                    inputAttributes: {'aria-label': 'Kode LaTeX tanpa pembatas', spellcheck: 'false'},
                    html: '<p class="small">Tulis kode LaTeX tanpa pembatas. Rumus disisipkan di dalam baris.</p>' +
                        '<div class="question-formula-preview" aria-live="polite"></div>',
                    confirmButtonText: 'Sisipkan',
                    cancelButtonText: 'Batal',
                    showCancelButton: true,
                    onOpen: function (popup) {
                        const input = swal.getInput();
                        const preview = popup.querySelector('.question-formula-preview');
                        input.addEventListener('input', function () {
                            preview.textContent = '';
                            if (!input.value.trim()) return;
                            try {
                                window.katex.render(input.value, preview, limits);
                            } catch (error) {
                                preview.textContent = 'Periksa kembali sintaks rumus.';
                            }
                        });
                    },
                    inputValidator: function (value) {
                        if (!value.trim()) return 'Isi rumus LaTeX.';
                        try {
                            window.katex.renderToString(value, limits);
                        } catch (error) {
                            return 'Sintaks LaTeX belum valid.';
                        }
                    },
                }).then(function (result) {
                    if (!result.value) return;
                    context.invoke('editor.restoreRange');
                    context.invoke('editor.insertText', '\\(' + result.value.trim() + '\\)');
                });
            },
        }).render();
    };
}());

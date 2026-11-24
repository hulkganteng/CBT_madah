(function () {
    const limits = {trust: false, maxExpand: 300, maxSize: 30};
    const delimiters = [
        {left: '\\(', right: '\\)', display: false},
        {left: '\\[', right: '\\]', display: true},
        {left: '$$', right: '$$', display: true},
    ];

    const ignoredTags = ['script', 'noscript', 'style', 'textarea', 'pre', 'code', 'option'];
    const ignoredClasses = ['katex', 'katex-display', 'katex-error', 'question-inline-math'];
    const ignoredSelector = [...ignoredTags, ...ignoredClasses.map(name => '.' + name)].join(',');
    const commandArguments = {
        frac: 2, dfrac: 2, tfrac: 2, binom: 2, sqrt: 1, vec: 1, overline: 1,
        hat: 1, mathbf: 1, mathrm: 1, log: 0, ln: 0, sin: 0, cos: 0, tan: 0,
        sum: 0, prod: 0, int: 0, lim: 0, alpha: 0, beta: 0, gamma: 0, theta: 0,
        lambda: 0, pi: 0, sigma: 0, omega: 0, infty: 0, times: 0, div: 0, pm: 0,
        le: 0, leq: 0, ge: 0, geq: 0, neq: 0, cdot: 0,
    };
    const scriptArgument = String.raw`(?:\{[^{}\r\n]{1,80}\}|[+-]?\d{1,20}|[A-Za-z])`;
    const scripts = String.raw`(?:[\^_]${scriptArgument}){1,2}`;
    const base = String.raw`(?:\d{0,20}[A-Za-z]{1,3}(?=\^)|[A-Za-z]|\d{1,20}|\([^()\r\n]{1,40}\))`;
    const trailingScripts = new RegExp('^' + scripts);
    const implicitPattern = [
        String.raw`(?:https?://|www\.)[^\s<>]+`,
        String.raw`(?<![\\$\p{L}\p{N}])\$(?<dollar>[^$\r\n]{1,500})\$(?![$\p{L}\p{N}])`,
        String.raw`(?<![\\\p{L}\p{N}_^])(?<shorthand>(?:${base}${scripts}){1,10})(?![\p{L}\p{N}_^])`,
        String.raw`(?<!\\)\\(?<command>${Object.keys(commandArguments).join('|')})(?![A-Za-z])`,
    ].join('|');

    function isMathematics(subject) {
        return /\bmatematika\b/i.test(subject || '');
    }

    function groupEnd(source, start, open, close) {
        if (source[start] !== open) return start;
        let depth = 1;
        for (let index = start + 1; index < source.length && index - start <= 500; index++) {
            if (source[index] === '\\') index++;
            else if (source[index] === open) depth++;
            else if (source[index] === close && --depth === 0) return index + 1;
        }
        return start;
    }

    function commandEnd(source, end, command) {
        if (command === 'sqrt') end = groupEnd(source, end, '[', ']');
        for (let argument = 0; argument < commandArguments[command]; argument++) {
            while (source[end] === ' ' || source[end] === '\t') end++;
            const next = groupEnd(source, end, '{', '}');
            if (next === end) return null;
            end = next;
        }
        const suffix = source.slice(end, end + 175).match(trailingScripts);
        return end + (suffix ? suffix[0].length : 0);
    }

    function renderImplicitMath(element) {
        const doc = element.ownerDocument;
        const walker = doc.createTreeWalker(element, NodeFilter.SHOW_TEXT, node =>
            node.parentElement.closest(ignoredSelector) ? NodeFilter.FILTER_REJECT : NodeFilter.FILTER_ACCEPT
        );
        const textNodes = [];
        while (walker.nextNode()) textNodes.push(walker.currentNode);

        const pattern = new RegExp(implicitPattern, 'gu');
        for (const node of textNodes) {
            const source = node.nodeValue;
            const output = doc.createDocumentFragment();
            let start = 0;
            let match;
            while ((match = pattern.exec(source))) {
                const {dollar, shorthand, command} = match.groups;
                if (dollar === undefined && !shorthand && !command) continue;
                let tex = dollar;
                let end = pattern.lastIndex;
                if (dollar !== undefined) {
                    tex = dollar.trim();
                    if (!/\\[A-Za-z]|[\^_+*=<>/−-]/.test(tex) && !/^(?:[A-Za-z]|\d+(?:[.,]\d+)?)$/.test(tex)) continue;
                } else {
                    if (command) {
                        end = commandEnd(source, end, command);
                        if (end === null) continue;
                        pattern.lastIndex = end;
                    }
                    tex = (shorthand || source.slice(match.index, end)).replace(/([\^_])([+-]?\d+|[A-Za-z])/g, '$1{$2}');
                }
                const formula = doc.createElement('span');
                formula.className = 'question-inline-math';
                try {
                    window.katex.render(tex, formula, limits);
                } catch (error) {
                    continue;
                }
                output.append(source.slice(start, match.index), formula);
                start = end;
            }
            if (start > 0) {
                output.append(source.slice(start));
                node.replaceWith(output);
            }
        }
    }

    window.renderQuestionMath = function (element, subject) {
        if (!element || element.closest(ignoredSelector) || typeof window.renderMathInElement !== 'function') return;

        window.renderMathInElement(element, {
            ...limits,
            delimiters: delimiters,
            ignoredTags: ignoredTags,
            ignoredClasses: ignoredClasses,
            throwOnError: false,
        });
        if (isMathematics(subject) && window.katex) renderImplicitMath(element);
    };

    function bindQuestionMathPreview($form) {
        const subject = $form.attr('data-question-subject');
        if (!isMathematics(subject)) return;

        const pending = new WeakMap();
        $form.on('summernote.init.questionMath summernote.change.questionMath', 'textarea', function () {
            const editor = this;
            clearTimeout(pending.get(editor));
            pending.set(editor, setTimeout(function () {
                const layout = editor.nextElementSibling;
                const editable = layout && layout.querySelector('.note-editable');
                if (!editable || !editor.isConnected) return;

                let preview = layout.nextElementSibling;
                if (!preview || !preview.classList.contains('question-math-preview')) preview = null;
                const rendered = document.createElement('div');
                rendered.className = 'text-break';
                rendered.style.whiteSpace = 'pre-line';
                rendered.textContent = editable.innerText;
                window.renderQuestionMath(rendered, subject);
                if (!rendered.querySelector('.katex')) {
                    if (preview) preview.remove();
                    return;
                }
                if (!preview) {
                    preview = document.createElement('div');
                    preview.className = 'question-math-preview bg-light border rounded p-2 mt-2 mb-3 overflow-auto';
                    const label = document.createElement('div');
                    label.className = 'small text-muted mb-1';
                    label.textContent = 'Pratinjau rumus';
                    preview.append(label, rendered);
                    layout.after(preview);
                } else {
                    preview.lastElementChild.replaceWith(rendered);
                }
            }, 200));
        });
    }

    function initializeBankMath() {
        for (const detail of document.querySelectorAll('[data-question-math-detail]')) {
            for (const question of detail.querySelectorAll('.question-preview')) {
                window.renderQuestionMath(question, detail.dataset.questionSubject);
            }
        }
        if (window.jQuery) {
            window.jQuery('[data-question-math-editor]').each(function () {
                bindQuestionMathPreview(window.jQuery(this));
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeBankMath);
    } else {
        initializeBankMath();
    }

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

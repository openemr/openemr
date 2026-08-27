(function () {
    const modalEl = document.getElementById('ruleModal');
    if (!modalEl || typeof $ === 'undefined' || !$.fn.modal) {
        return;
    }

    let bandRules = [];
    try {
        bandRules = JSON.parse(modalEl.getAttribute('data-band-rules') || '[]');
    } catch (e) {
        bandRules = [];
    }
    const overlapMsg = {
        inverted: modalEl.getAttribute('data-msg-inverted') || 'Minimum must be less than or equal to maximum.',
        overlap: modalEl.getAttribute('data-msg-overlap') || 'This numeric range overlaps another band on the same field.'
    };

    function setValue(id, value) {
        const el = document.getElementById(id);
        if (!el) {
            return;
        }
        if (el.type === 'checkbox') {
            el.checked = !!value;
            return;
        }
        el.value = value == null ? '' : String(value);
    }

    function syncOp() {
        const op = (document.getElementById('op') || {}).value || 'band';
        const band = document.getElementById('band-fields');
        const token = document.getElementById('token-fields');
        const source2 = document.getElementById('source2-wrap');
        const helpBand = document.getElementById('source2-help-band');
        const helpRatio = document.getElementById('source2-help-ratio');
        const isBand = op === 'band';
        const isRatio = op === 'ratio_lt' || op === 'ratio_gt';
        const isToken = op === 'parse_severity';
        if (band) {
            band.classList.toggle('d-none', !isBand && !isRatio);
        }
        if (token) {
            token.classList.toggle('d-none', !isToken);
        }
        if (source2) {
            source2.classList.toggle('d-none', isToken);
        }
        if (helpBand) {
            helpBand.classList.toggle('d-none', !isBand);
        }
        if (helpRatio) {
            helpRatio.classList.toggle('d-none', !isRatio);
        }
    }

    function showOverlap(text) {
        const box = document.getElementById('rule-overlap-msg');
        if (!box) {
            return;
        }
        if (!text) {
            box.classList.add('d-none');
            box.textContent = '';
            return;
        }
        box.textContent = text;
        box.classList.remove('d-none');
    }

    function numOrNull(raw) {
        if (raw === null || raw === undefined || String(raw).trim() === '') {
            return null;
        }
        const n = Number(raw);
        return Number.isFinite(n) ? n : null;
    }

    function rangesOverlap(a, b) {
        const aMin = a.min == null ? Number.NEGATIVE_INFINITY : a.min;
        const aMax = a.max == null ? Number.POSITIVE_INFINITY : a.max;
        const bMin = b.min == null ? Number.NEGATIVE_INFINITY : b.min;
        const bMax = b.max == null ? Number.POSITIVE_INFINITY : b.max;
        if (aMax < bMin) {
            return false;
        }
        if (aMax === bMin && (!a.maxInc || !b.minInc)) {
            return false;
        }
        if (bMax < aMin) {
            return false;
        }
        if (bMax === aMin && (!b.maxInc || !a.minInc)) {
            return false;
        }
        return true;
    }

    function readDraft() {
        return {
            id: document.getElementById('rule_id').value,
            source_field_id: document.getElementById('source_field_id').value,
            source_field_id_2: document.getElementById('source_field_id_2').value,
            op: document.getElementById('op').value,
            min: numOrNull(document.getElementById('min_value').value),
            max: numOrNull(document.getElementById('max_value').value),
            minInc: document.getElementById('min_inclusive').checked,
            maxInc: document.getElementById('max_inclusive').checked,
            enabled: document.getElementById('enabled').checked
        };
    }

    function draftOverlaps() {
        const draft = readDraft();
        if (draft.op !== 'band' || !draft.enabled) {
            return '';
        }
        if (draft.min != null && draft.max != null && draft.min > draft.max) {
            return overlapMsg.inverted;
        }
        const selfId = String(draft.id || '');
        for (let i = 0; i < bandRules.length; i++) {
            const other = bandRules[i];
            if (String(other.id) === selfId) {
                continue;
            }
            if (Number(other.enabled) !== 1) {
                continue;
            }
            if (other.source_field_id !== draft.source_field_id) {
                continue;
            }
            if (String(other.source_field_id_2 || '') !== String(draft.source_field_id_2 || '')) {
                continue;
            }
            const b = {
                min: numOrNull(other.min_value),
                max: numOrNull(other.max_value),
                minInc: Number(other.min_inclusive) === 1,
                maxInc: Number(other.max_inclusive) === 1
            };
            if (rangesOverlap(draft, b)) {
                return overlapMsg.overlap;
            }
        }
        return '';
    }

    function fill(rule) {
        showOverlap('');
        setValue('rule_id', rule.id || '');
        setValue('source_field_id', rule.source_field_id || '');
        setValue('source_field_id_2', rule.source_field_id_2 || '');
        setValue('op', rule.op || 'band');
        setValue('min_value', rule.min_value || '');
        setValue('max_value', rule.max_value || '');
        setValue('min_inclusive', rule.min_inclusive === undefined ? true : Number(rule.min_inclusive) === 1);
        setValue('max_inclusive', rule.max_inclusive === undefined ? true : Number(rule.max_inclusive) === 1);
        setValue('seq', rule.seq || '0');
        setValue('match_token', rule.match_token || '');
        setValue('statement_text', rule.statement_text || '');
        setValue('enabled', rule.enabled === undefined ? true : Number(rule.enabled) === 1);
        const title = document.getElementById('ruleModalLabel');
        if (title) {
            title.textContent = rule.id ? title.getAttribute('data-edit') : title.getAttribute('data-add');
        }
        syncOp();
    }

    document.querySelectorAll('[data-rule-edit]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            fill(JSON.parse(btn.getAttribute('data-rule-edit')));
            $(modalEl).modal('show');
        });
    });

    const addBtn = document.getElementById('add-rule');
    if (addBtn) {
        addBtn.addEventListener('click', function () {
            fill({
                seq: addBtn.getAttribute('data-next-seq') || '10',
                enabled: 1,
                min_inclusive: 1,
                max_inclusive: 1,
                op: 'band'
            });
            $(modalEl).modal('show');
        });
    }

    const opEl = document.getElementById('op');
    if (opEl) {
        opEl.addEventListener('change', syncOp);
    }

    const form = modalEl.querySelector('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            const why = draftOverlaps();
            if (why) {
                e.preventDefault();
                showOverlap(why);
            }
        });
    }

    const reopen = modalEl.getAttribute('data-reopen');
    if (reopen) {
        try {
            fill(JSON.parse(reopen));
            const pageErr = document.querySelector('.alert-danger');
            if (pageErr && pageErr.textContent) {
                showOverlap(pageErr.textContent.trim());
            }
            $(modalEl).modal('show');
        } catch (e) {
            // leave closed
        }
    }
})();

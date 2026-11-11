/**
 * Statement rule editor for a layout form.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

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
        overlap: modalEl.getAttribute('data-msg-overlap') || 'This numeric range overlaps another band on the same field.',
        number: modalEl.getAttribute('data-msg-number') || 'Enter a number, or leave the bound empty.',
        ratio: modalEl.getAttribute('data-msg-ratio') || 'A ratio rule needs a second field.'
    };

    /**
     * Set an input or checkbox. Numeric 0 is kept; null becomes empty.
     * @param {string} id
     * @param {*} value
     */
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

    /**
     * Show band, ratio, or token fields for the selected operator.
     */
    function syncOp() {
        const op = (document.getElementById('op') || {}).value || 'band';
        const band = document.getElementById('band-fields');
        const token = document.getElementById('token-fields');
        const source2 = document.getElementById('source2-wrap');
        const helpBand = document.getElementById('source2-help-band');
        const helpRatio = document.getElementById('source2-help-ratio');
        const tokenLabelText = document.getElementById('token-label-text');
        const tokenLabelSex = document.getElementById('token-label-sex');
        const tokenHelpText = document.getElementById('token-help-text');
        const tokenHelpSex = document.getElementById('token-help-sex');
        const isBand = op === 'band';
        const isRatio = op === 'ratio_lt' || op === 'ratio_gt';
        const isToken = op === 'parse_severity';
        if (band) {
            band.classList.toggle('d-none', !isBand && !isRatio);
        }
        if (token) {
            token.classList.toggle('d-none', !isToken && !isBand);
        }
        if (tokenLabelText) {
            tokenLabelText.classList.toggle('d-none', !isToken);
        }
        if (tokenLabelSex) {
            tokenLabelSex.classList.toggle('d-none', !isBand);
        }
        if (tokenHelpText) {
            tokenHelpText.classList.toggle('d-none', !isToken);
        }
        if (tokenHelpSex) {
            tokenHelpSex.classList.toggle('d-none', !isBand);
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

    /**
     * Show or hide the overlap warning under the rule form.
     * @param {string} text
     */
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

    /**
     * Parse a numeric field, or null when blank.
     * @param {*} raw
     * @returns {number|null}
     */
    /**
     * True when a bound was typed and is not a finite number.
     * @param {*} raw
     * @returns {boolean}
     */
    function badNumber(raw) {
        if (raw === null || raw === undefined || String(raw).trim() === '') {
            return false;
        }
        return !Number.isFinite(Number(raw));
    }

    /**
     * Message when a bound is not blank and not numeric.
     * @returns {string}
     */
    function invalidBound() {
        const minEl = document.getElementById('min_value');
        const maxEl = document.getElementById('max_value');
        if ((minEl && badNumber(minEl.value)) || (maxEl && badNumber(maxEl.value))) {
            return overlapMsg.number;
        }
        return '';
    }

    function numOrNull(raw) {
        if (raw === null || raw === undefined || String(raw).trim() === '') {
            return null;
        }
        const n = Number(raw);
        return Number.isFinite(n) ? n : null;
    }

    /**
     * True when two inclusive/exclusive numeric ranges overlap.
     * @param {{min:number|null,max:number|null,minInc:boolean,maxInc:boolean}} a
     * @param {{min:number|null,max:number|null,minInc:boolean,maxInc:boolean}} b
     * @returns {boolean}
     */
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

    /**
     * Current rule-editor values used for client-side overlap checks.
     * @returns {object}
     */
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

    /**
     * Warning text when the draft band is inverted or overlaps another rule.
     * @returns {string}
     */
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

    /**
     * Load one rule into the editor. A numeric 0 bound is not treated as empty.
     * @param {object} rule
     */
    function fill(rule) {
        showOverlap('');
        setValue('rule_id', rule.id || '');
        setValue('source_field_id', rule.source_field_id || '');
        setValue('source_field_id_2', rule.source_field_id_2 || '');
        setValue('op', rule.op || 'band');
        setValue('min_value', rule.min_value);
        setValue('max_value', rule.max_value);
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
            const bound = invalidBound();
            if (bound) {
                e.preventDefault();
                showOverlap(bound);
                return;
            }
            const draft = readDraft();
            const ratio = draft.op === 'ratio_lt' || draft.op === 'ratio_gt';
            if (ratio && String(draft.source_field_id_2 || '').trim() === '') {
                e.preventDefault();
                showOverlap(overlapMsg.ratio);
                return;
            }
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

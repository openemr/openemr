/**
 * careplan.js - Care Plan form JavaScript using template cloning.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jacob T Paul <jacob@zhservices.com>
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2018-2025 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (C) 2025 Open Plan IT Ltd. <support@openplanit.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
(function (window, oeUI) {

    let webroot = '';
    let translations = {};

    const DATEPICKER_OPTIONS = {
        timepicker: true,
        showSeconds: false,
        formatInput: false
    };

    /**
     * @param {string} wrValue         - The webroot URL
     * @param {string|null} reasonCodeTypes - Code types offered by the reason code picker
     * @param {Object} i18n            - Pre-translated strings from the server
     */
    function init(wrValue, reasonCodeTypes, i18n) {
        webroot = wrValue;
        translations = i18n || {};

        // Setup reason code widgets
        if (oeUI.reasonCodeWidget) {
            oeUI.reasonCodeWidget.init(webroot, reasonCodeTypes);
        } else {
            console.error("Missing required dependency reasonCodeWidget");
        }

        // Datepickers are bound on hover rather than up front so that rows added after
        // load pick one up too. mouseover fires repeatedly as the pointer moves, so each
        // field is marked once initialized to avoid re-initializing it on every pass.
        $(document).on('mouseover', '.datepicker', function () {
            if (this.dataset.careplanDatepickerBound === '1') {
                return;
            }
            this.dataset.careplanDatepickerBound = '1';
            datetimepickerTranslated(this, DATEPICKER_OPTIONS);
        });

        // Bind event listeners via delegation
        var container = document.getElementById('care-plan-rows-container');
        if (container) {
            container.addEventListener('click', handleContainerClick);
        }

        // Set initial delete button state
        updateDeleteButtons();
    }

    function handleContainerClick(event) {
        // Handle code input clicks for code picker
        if (event.target.classList && event.target.classList.contains('js-careplan-code')) {
            var tbRow = event.target.closest('.tb_row');
            if (tbRow) {
                sel_code(webroot, tbRow.id);
            }
            return;
        }

        var target = event.target.closest('button');
        if (!target) {
            return;
        }

        var buttonRow = target.closest('.tb_row');
        if (!buttonRow) {
            return;
        }

        if (target.classList.contains('js-careplan-add')) {
            event.preventDefault();
            addRow(buttonRow);
        } else if (target.classList.contains('js-careplan-delete')) {
            event.preventDefault();
            confirmDeleteRow(buttonRow);
        }
    }

    function addRow(afterElement) {
        var template = document.getElementById('care-plan-row-template');
        var templateRow = template.content.querySelector('.tb_row');
        var newRow = templateRow.cloneNode(true);

        var nextIndex = getNextRowIndex();
        updateRowIndex(newRow, nextIndex);
        clearRowValues(newRow);

        afterElement.after(newRow);

        bindTemplateEditor(newRow);
        reindexAllRows();
        updateDeleteButtons();

        // Reload reason code widget to pick up new elements
        if (oeUI.reasonCodeWidget) {
            oeUI.reasonCodeWidget.reload();
        }
    }

    /**
     * CustomTemplateApi binds the description dblclick handler once at window load, so
     * rows added afterwards have to be wired up by hand or they silently lose the
     * custom template editor.
     */
    function bindTemplateEditor(row) {
        if (typeof doTemplateEditor === 'undefined') {
            return;
        }
        var textarea = row.querySelector('textarea.description');
        if (!textarea) {
            return;
        }
        textarea.addEventListener('dblclick', function (event) {
            doTemplateEditor(this, event, event.target.dataset.textcontext);
        });
    }

    function confirmDeleteRow(rowElement) {
        if (countRows() <= 1) {
            return;
        }
        var message = translations.confirmDelete || 'Are you sure you want to delete this entry?';
        if (!confirm(message)) {
            return;
        }
        deleteRow(rowElement);
    }

    function deleteRow(rowElement) {
        rowElement.remove();
        reindexAllRows();
        updateDeleteButtons();

        if (oeUI.reasonCodeWidget) {
            oeUI.reasonCodeWidget.reload();
        }
    }

    function countRows() {
        var container = document.getElementById('care-plan-rows-container');
        if (!container) {
            return 0;
        }
        return container.querySelectorAll('.tb_row').length;
    }

    /**
     * Enable or disable delete buttons based on the number of rows.
     * When there is only one row, the delete button is disabled.
     */
    function updateDeleteButtons() {
        var container = document.getElementById('care-plan-rows-container');
        if (!container) {
            return;
        }
        var rows = container.querySelectorAll('.tb_row');
        var isSingleRow = rows.length <= 1;

        rows.forEach(function (row) {
            var deleteBtn = row.querySelector('.js-careplan-delete');
            if (deleteBtn) {
                deleteBtn.disabled = isSingleRow;
            }
        });
    }

    function getNextRowIndex() {
        return countRows() + 1;
    }

    function reindexAllRows() {
        var container = document.getElementById('care-plan-rows-container');
        var rows = container.querySelectorAll('.tb_row');

        rows.forEach(function (row, i) {
            updateRowIndex(row, i + 1);
        });
    }

    function updateRowIndex(row, index) {
        row.id = 'tb_row_' + index;

        // Update IDs for elements with known class-based ID patterns
        var idMappings = [
            { cls: 'code', prefix: 'code_' },
            { cls: 'codetext', prefix: 'codetext_' },
            { cls: 'displaytext', prefix: 'displaytext_' },
            { cls: 'code_date', prefix: 'code_date_' },
            { cls: 'care_plan_type', prefix: 'care_plan_type_' },
            { cls: 'user', prefix: 'user_' },
            { cls: 'count', prefix: 'count_' },
            { cls: 'proposed_date', prefix: 'proposed_date_' },
            { cls: 'end_date', prefix: 'end_date_' },
            { cls: 'plan_status', prefix: 'status_' },
            { cls: 'plan_engagement_category', prefix: 'engagement_category_' },
            { cls: 'description', prefix: 'description_' },
            // Reason fields carry ids so their <label for> stays associated. They have to
            // reindex with the row or the labels end up pointing at another row's ids.
            // Note the reason-code *input* is reason_code_input_N -- reason_code_N is the
            // wrapper div, reindexed separately below as the toggle target.
            { cls: 'reason_code_input', prefix: 'reason_code_input_' },
            { cls: 'reason_status', prefix: 'reason_status_' },
            { cls: 'reason_start_date', prefix: 'reason_start_date_' },
            { cls: 'reason_end_date', prefix: 'reason_end_date_' }
        ];

        idMappings.forEach(function (mapping) {
            var el = row.querySelector('.' + mapping.cls);
            if (el) {
                el.id = mapping.prefix + index;
            }
        });

        // Update count hidden input value
        var countInput = row.querySelector('.count');
        if (countInput) {
            countInput.value = index;
        }

        // Update reason code container ID and toggle-container data attribute
        var reasonContainer = row.querySelector('.reasonCodeContainer');
        if (reasonContainer) {
            reasonContainer.id = 'reason_code_' + index;
        }

        var reasonBtn = row.querySelector('[data-toggle-container]');
        if (reasonBtn) {
            reasonBtn.dataset.toggleContainer = 'reason_code_' + index;
        }

        // Update label for attributes
        var labels = row.querySelectorAll('label[for]');
        labels.forEach(function (label) {
            var forAttr = label.getAttribute('for');
            // Replace trailing _N or _0 with _index
            var newFor = forAttr.replace(/_\d+$/, '_' + index);
            label.setAttribute('for', newFor);
        });
    }

    function clearRowValues(row) {
        // Clear text inputs
        var textInputs = row.querySelectorAll('input[type="text"]');
        textInputs.forEach(function (input) {
            input.value = '';
        });

        // Clear hidden inputs (codetext, user, reasonCodeText, count). The user column
        // is intentionally cleared -- the server falls back to the logged in user.
        var hiddenInputs = row.querySelectorAll('input[type="hidden"]');
        hiddenInputs.forEach(function (input) {
            input.value = '';
        });

        // Clear textareas
        var textareas = row.querySelectorAll('textarea');
        textareas.forEach(function (textarea) {
            textarea.value = '';
            textarea.dataset.textcontext = '';
        });

        // Reset selects to first option
        var selects = row.querySelectorAll('select');
        selects.forEach(function (select) {
            select.selectedIndex = 0;
        });

        // Clear display text spans
        var displayTexts = row.querySelectorAll('.displaytext');
        displayTexts.forEach(function (span) {
            span.textContent = '';
        });

        // Hide reason code container
        var reasonContainer = row.querySelector('.reasonCodeContainer');
        if (reasonContainer) {
            reasonContainer.classList.add('d-none');
        }

        // Hide reason code text display
        var reasonTextDisplay = row.querySelector('.code-selector-text-display');
        if (reasonTextDisplay) {
            reasonTextDisplay.classList.add('d-none');
            reasonTextDisplay.textContent = '';
        }
    }

    function sel_code(wrValue, rowId) {
        var parts = rowId.split('tb_row_');
        var checkId = '_' + (parts[1] || '1');
        document.getElementById('clickId').value = checkId;
        window.top.restoreSession();
        dlgopen(wrValue + '/interface/patient_file/encounter/find_code_popup.php?default=SNOMED-CT', '_blank', 700, 400);
    }

    function set_related(codetype, code, selector, codedesc) {
        var checkId = document.getElementById('clickId').value;
        if (codetype !== '') {
            document.getElementById('code' + checkId).value = codetype + ':' + code;
        } else {
            document.getElementById('code' + checkId).value = '';
        }
        document.getElementById('codetext' + checkId).value = codedesc;
        document.getElementById('displaytext' + checkId).textContent = codedesc;
    }

    // Expose public API
    window.careplanForm = {
        init: init
    };

    // set_related is called by find_code_popup.php via opener.set_related and is also
    // temporarily swapped out by reasonCodeWidget, so it must live on window.
    // sel_code is exported for backwards compatibility with downstream overrides.
    window.sel_code = sel_code;
    window.set_related = set_related;

})(window, window.oeUI || {});

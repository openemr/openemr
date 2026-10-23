/* Eye Form document-note dialog. Replaces the sparse read-only pnotes summary. */
(function () {
    var strings = window.eyeMagDocumentNotesStrings || {};
    function docT(key, fallback) {
        return typeof strings[key] === 'string' && strings[key] !== '' ? strings[key] : fallback;
    }

    function enableMaterialsPrint() {
        function openDocumentPage(url, label) {
            if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') {
                top.restoreSession();
            }
            if (typeof parent !== 'undefined' && typeof parent.twAddFrameTab === 'function') {
                parent.twAddFrameTab('enctabs', label, url);
                return;
            }
            window.open(url, '_blank', 'noopener');
        }

        var button = document.getElementById('PRINT_MATERIALS');
        var materials = document.getElementById('MATERIALS_TO_PRINT');
        var textIncluded = document.getElementById('MATERIALS_TEXT_INCLUDED');
        var addedText = document.getElementById('MATERIALS_ADDED_TEXT');
        var addedTextSummary = document.getElementById('MATERIALS_ADDED_TEXT_SUMMARY');
        var removeText = document.getElementById('REMOVE_MATERIALS_TEXT');

        var materialsSaving = false;
        var materialsSaveAgain = false;
        function saveMaterialsSelection() {
            if (typeof submit_form !== 'function') return;
            if (materialsSaving) {
                materialsSaveAgain = true;
                return;
            }
            materialsSaving = true;
            var request = submit_form();
            function finished() {
                materialsSaving = false;
                if (materialsSaveAgain) {
                    materialsSaveAgain = false;
                    saveMaterialsSelection();
                }
            }
            if (request && typeof request.always === 'function') request.always(finished);
            else finished();
        }

        function summarizeText(value) {
            var summary = String(value || '').replace(/\s+/g, ' ').trim();
            return summary.length > 90 ? summary.slice(0, 87) + '...' : summary;
        }

        if (button && materials) {
            button.addEventListener('click', function () {
                if (!materials.value.trim()) {
                    window.alert(docT('enterPatientMaterial', 'Enter patient material text before adding it.'));
                    return;
                }
                if (textIncluded) textIncluded.value = '1';
                if (addedTextSummary) addedTextSummary.textContent = summarizeText(materials.value);
                if (addedText) addedText.style.display = 'flex';
                saveMaterialsSelection();

                var printWindow = window.open('', 'eye_materials_print', 'width=850,height=700');
                if (!printWindow) return;
                printWindow.document.title = 'Materials to Print';
                var heading = printWindow.document.createElement('h2');
                heading.textContent = 'Materials to Print';
                var content = printWindow.document.createElement('div');
                content.style.whiteSpace = 'pre-wrap';
                content.style.font = '12pt/1.5 sans-serif';
                content.textContent = materials.value;
                printWindow.document.body.appendChild(heading);
                printWindow.document.body.appendChild(content);
                printWindow.focus();
                printWindow.print();
            });
        }
        if (removeText) {
            removeText.addEventListener('click', function () {
                if (textIncluded) textIncluded.value = '0';
                if (addedText) addedText.style.display = 'none';
                saveMaterialsSelection();
            });
        }

        var addHandout = document.getElementById('ADD_MATERIALS_HANDOUT');
        if (addHandout) {
            addHandout.addEventListener('click', function () {
                openDocumentPage(addHandout.getAttribute('data-upload-url'), 'Add Practice PDF Handout');
            });
        }

        var handout = document.getElementById('MATERIALS_HANDOUT_PICKER');
        var includedHandouts = document.getElementById('MATERIALS_HANDOUT_IDS');
        var openHandout = document.getElementById('OPEN_MATERIALS_HANDOUT');
        var addedHandouts = document.getElementById('MATERIALS_ADDED_PDFS');
        var handoutTemplate = document.getElementById('MATERIALS_PDF_ROW');
        if (!handout || !includedHandouts || !openHandout || !addedHandouts || !handoutTemplate) return;
        var selectedIds;
        try { selectedIds = JSON.parse(includedHandouts.value); } catch (error) { selectedIds = []; }
        selectedIds = Array.isArray(selectedIds) ? selectedIds.map(String).filter(function (id, index, ids) {
            return /^[1-9][0-9]*$/.test(id) && ids.indexOf(id) === index;
        }) : [];

        function renderHandouts() {
            addedHandouts.replaceChildren();
            selectedIds.forEach(function (id) {
                var option = Array.from(handout.options).find(function (item) { return item.value === id; });
                var row = handoutTemplate.content.firstElementChild.cloneNode(true);
                row.setAttribute('data-document-id', id);
                row.querySelector('.materials-pdf-name').textContent = option ? option.textContent.trim()
                    : docT('unavailableHandout', 'Unavailable PDF') + ' #' + id;
                var open = row.querySelector('[data-action="open"]');
                var print = row.querySelector('[data-action="print"]');
                open.disabled = print.disabled = !option;
                var patientId = option ? option.getAttribute('data-patient-id') : '0';
                open.addEventListener('click', function () { openPdf(id, patientId); });
                print.addEventListener('click', function () { printPdf(id, patientId); });
                row.querySelector('[data-action="remove"]').addEventListener('click', function () {
                    selectedIds = selectedIds.filter(function (selectedId) { return selectedId !== id; });
                    renderHandouts();
                    saveMaterialsSelection();
                });
                addedHandouts.appendChild(row);
            });
            includedHandouts.value = JSON.stringify(selectedIds);
        }

        function openPdf(documentId, patientId) {
            if (!documentId) return;
            patientId = Math.max(0, parseInt(patientId || '0', 10) || 0);
            openDocumentPage(base + '/controller.php?document&retrieve&patient_id=' + encodeURIComponent(patientId) +
                '&document_id=' + encodeURIComponent(documentId) + '&as_file=false&original_file=true&disable_exit=false&show_original=true', 'Patient Education Handout');
        }

        function printPdf(documentId, patientId) {
            if (!documentId) return;
            if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') {
                top.restoreSession();
            }
            var printWindow = window.open('', '_blank', 'width=900,height=750');
            if (!printWindow) {
                window.alert(docT('allowPopups', 'Allow pop-ups to print the PDF handout.'));
                return;
            }
            printWindow.opener = null;
            printWindow.document.title = 'Print PDF Handout';
            printWindow.document.body.style.cssText = 'margin:0;height:100vh;display:flex;flex-direction:column';
            var help = printWindow.document.createElement('p');
            help.textContent = 'If the print dialog does not open, use the PDF viewer’s Print button.';
            help.style.cssText = 'font:14px sans-serif;margin:10px';
            var frame = printWindow.document.createElement('iframe');
            frame.title = 'PDF handout to print';
            frame.style.cssText = 'width:100%;flex:1;border:0';
            frame.addEventListener('load', function () {
                try {
                    frame.contentWindow.focus();
                    frame.contentWindow.print();
                } catch (error) {
                    // Some browsers isolate their PDF viewer; its own print
                    // control remains available in this visible window.
                }
            }, { once: true });
            patientId = Math.max(0, parseInt(patientId || '0', 10) || 0);
            frame.src = base + '/controller.php?document&retrieve&patient_id=' + encodeURIComponent(patientId) +
                '&document_id=' + encodeURIComponent(documentId) + '&as_file=false&original_file=true&disable_exit=false&show_original=true';
            printWindow.document.body.appendChild(help);
            printWindow.document.body.appendChild(frame);
        }

        function updateHandoutButton() {
            // Keep the action available whenever the Handouts folder contains
            // a PDF. With one PDF, the click handler can select it directly;
            // with several, it can explain that a selection is required.
            openHandout.disabled = handout.querySelectorAll('option[value]:not([value=""])').length === 0;
        }

        handout.addEventListener('change', function () {
            updateHandoutButton();
        });
        openHandout.addEventListener('click', function () {
            if (!handout.value) {
                var available = handout.querySelectorAll('option[value]:not([value=""])');
                if (available.length === 1) {
                    handout.value = available[0].value;
                } else if (available.length > 1) {
                    window.alert(docT('selectHandout', 'Select a PDF handout first.'));
                    return;
                } else {
                    window.alert(docT('noPdfs', 'No PDFs are available in Patient Education / Handouts.'));
                    return;
                }
            }
            if (selectedIds.indexOf(handout.value) !== -1) return;
            selectedIds.push(handout.value);
            renderHandouts();
            handout.value = '';
            saveMaterialsSelection();
        });
        renderHandouts();
        updateHandoutButton();
    }

    window.showpnotes = function (docid) {
        if (!docid) {
            window.alert(docT('uploadDocument', 'Upload a document before adding notes.'));
            return false;
        }
        var url = base + '/interface/forms/eye_mag/document_notes.php?docid=' + encodeURIComponent(docid);
        dlgopen(url, 'eye_document_notes', 'modal-xl', 650, '', '', {
            buttons: [{text: docT('done', 'Done'), close: true, style: 'default btn-sm'}],
            sizeHeight: 'auto',
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe'
        });
        return false;
    };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', enableMaterialsPrint);
    else enableMaterialsPrint();
})();

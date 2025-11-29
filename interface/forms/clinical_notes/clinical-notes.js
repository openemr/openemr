/**
 * Javascript library for the clinical note form.
 *
 * @package OpenEMR
 * @subpackage Forms
 * @link   http://www.open-emr.org
 * @author Jacob T Paul <jacob@zhservices.com>
 * @author Vinish K <vinish@zhservices.com>
 * @author Brady Miller <brady.g.miller@gmail.com>
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @author Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (C) 2025 Open Plan IT Ltd. <support@openplanit.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
(function() {
    let codeArray = [];
    let defaultType = '';
    let defaultCategory = '';
    let patientUuid = ''; // Global patient UUID for API calls
    let csrfToken = ''; // Global CSRF token for API calls

    function duplicateRow(event) {
        event.preventDefault();
        let btn = event.currentTarget;
        let oldId = btn.id;
        if (!oldId) {
            console.error("No row id found for button ", btn);
            return;
        } else {
            oldId = 'tb_row_' + oldId.split('btn-add_')[1];
        }
        let dupRow = document.getElementById(oldId);
        let newRow = dupRow.cloneNode(true);
        let $newRow = $(newRow);

        // Rebind existing events
        $newRow.find(".btn-add").click(duplicateRow);
        $newRow.find(".btn-delete").click(deleteRow);
        $newRow.find(".clinical_notes_type").change(typeChange);

        // Note: New linking events are handled via event delegation, no need to rebind

        dupRow.parentNode.insertBefore(newRow, dupRow.nextSibling);

        // Update IDs for all elements including new linking elements
        changeIds('tb_row');
        changeIds('description');
        changeIds('code');
        changeIds('codetext');
        changeIds('code_date');
        changeIds('clinical_notes_type');
        changeIds('clinical_notes_category');
        changeIds('count');
        changeIds('btn-add');
        changeIds('btn-delete');
        changeIds('id');

        // New linking element IDs
        changeIds('linked_documents');
        changeIds('linked_documents_data');
        changeIds('linked_results');
        changeIds('linked_results_data');
        changeIds('btn-add-documents');
        changeIds('btn-add-results');

        $newRow.find('.hide-author').addClass('d-none')
        removeVal(newRow.id);
        updateDefaults(newRow.id);
        clearLinkingData(newRow.id);
    }

    function updateDefaults(rowid) {
        let rowid1 = rowid.split('tb_row_');
        let typeEl = document.getElementById("clinical_notes_type_" + rowid1[1]);
        let categoryEl = document.getElementById("clinical_notes_category_" + rowid1[1]);
        let codeEl = document.getElementById("code_" + rowid1[1]);
        let codeTextEl = document.getElementById("codetext_" + rowid1[1]);
        let codeContext = document.getElementById("description_" + rowid1[1]);
        // note these two elements could be missing if there are no active list ids enabled for type and category
        if (typeEl) {
            typeEl.value = defaultType;
        }
        if (categoryEl) {
            categoryEl.value = defaultCategory;
        }
        codeEl.value = '';
        codeTextEl.value = '';
        codeContext.dataset.textcontext = '';
    }

    function removeVal(rowid) {
        rowid1 = rowid.split('tb_row_');
        let elements = ['description', 'code', 'codetext', 'code_date', 'clinical_notes_type', 'clinical_notes_category', 'id',
            'linked_documents_data', 'linked_results_data'];
        for (let i = 0; i < elements.length; i++) {
            let el = document.getElementById(elements[i] + "_" + rowid1[1]);
            if (el) {
                el.value = '';
            }
        }
        // this is an external function defined in CustomTemplateApi.js
        if (typeof doTemplateEditor !== 'undefined') {
            document.getElementById("description_" + rowid1[1]).addEventListener('dblclick', event => {
                doTemplateEditor(this, event, event.target.dataset.textcontext);
            })
        }
    }

    function clearLinkingData(rowid) {
        let rowid1 = rowid.split('tb_row_');
        let index = rowid1[1];

        // Clear linked documents display and data
        let documentsContainer = document.getElementById("linked_documents_" + index);
        if (documentsContainer) {
            // Clear existing content
            documentsContainer.replaceChildren();

            // Add empty state using template
            const emptyTemplate = document.getElementById('empty-state-template');
            if (emptyTemplate) {
                const emptyState = emptyTemplate.content.cloneNode(true);
                const textElement = emptyState.querySelector('.empty-state-text');
                if (textElement) {
                    textElement.textContent = jsText(xl('No documents linked'));
                }
                documentsContainer.appendChild(emptyState);
            }
        }

        // Clear linked results display and data
        let resultsContainer = document.getElementById("linked_results_" + index);
        if (resultsContainer) {
            // Clear existing content
            resultsContainer.replaceChildren();

            // Add empty state using template
            const emptyTemplate = document.getElementById('empty-state-template');
            if (emptyTemplate) {
                const emptyState = emptyTemplate.content.cloneNode(true);
                const textElement = emptyState.querySelector('.empty-state-text');
                if (textElement) {
                    textElement.textContent = jsText(xl('No results linked'));
                }
                resultsContainer.appendChild(emptyState);
            }
        }

        // Clear hidden input values (already handled by removeVal, but explicit for clarity)
        let documentsData = document.getElementById("linked_documents_data_" + index);
        if (documentsData) {
            documentsData.value = '[]';
        }

        let resultsData = document.getElementById("linked_results_data_" + index);
        if (resultsData) {
            resultsData.value = '[]';
        }
    }

    function changeIds(class_val) {
        var elem = document.getElementsByClassName(class_val);
        for (let i = 0; i < elem.length; i++) {
            if (elem[i].id) {
                index = i + 1;
                elem[i].id = class_val + "_" + index;
            }
            if (class_val == 'count') {
                elem[i].value = index;
            }
        }
    }

    function deleteRow(event) {
        event.preventDefault();
        let btn = event.currentTarget;
        let rowid = btn.id;
        if (!rowid) {
            console.error("No row id found for button ", btn);
            return;
        } else {
            rowid = 'tb_row_' + rowid.split('btn-delete_')[1];
        }

        // check to make sure there are other rows before deleting the last one
        if (document.getElementsByClassName('tb_row').length <= 1) {
            alert(window.top.xl('You must have at least one clinical note.'));
            return;
        }
        if (rowid) {
            let elem = document.getElementById(rowid);
            elem.parentNode.removeChild(elem);
        }
    }

    function typeChange(event) {
        try {
            let othis = event.currentTarget;
            let rowid = othis.id.split('clinical_notes_type_');
            let oId = rowid[1];
            let codeEl = document.getElementById("code_" + oId);
            let codeTextEl = document.getElementById("codetext_" + oId);
            let codeContext = document.getElementById("description_" + oId);
            let type = othis.options[othis.selectedIndex].value;
            let i = codeArray.findIndex((v, idx) => codeArray[idx].value === type);
            if (i >= 0)
            {
                codeEl.value = jsText(codeArray[i].code);
                codeTextEl.value = jsText(codeArray[i].title);
                codeContext.dataset.textcontext = jsText(codeArray[i].title);
            } else {
                console.error("Code not found in array for selected element ", codeEl);
                // they are clearing out the value so we are going to empty everything out.
                codeEl.value = "";
                codeTextEl.value = "";
                codeContext.vlaue = "";
            }

        } catch (e) {
            alert(jsText(e));
        }
    }

    // ==============================================
    // Document and Procedure Results Linking Functions
    // ==============================================

    function initLinkingEventHandlers() {
        // Use event delegation for dynamically added elements
        $(document).on('click', '.btn-add-documents', function(e) {
            e.preventDefault();
            let noteIndex = $(this).data('note-index');
            openDocumentSearchDialog(noteIndex);
        });

        $(document).on('click', '.btn-close-document', function(e) {
            e.preventDefault();
            let noteIndex = $(this).data('note-index');
            let uuid = $(this).data('uuid');
            removeDocumentFromNote(noteIndex, uuid);
        });

        $(document).on('click', '.btn-add-results', function(e) {
            e.preventDefault();
            let noteIndex = $(this).data('note-index');
            openResultsSearchDialog(noteIndex);
        });

        $(document).on('click', '.btn-close-result', function(e) {
            e.preventDefault();
            let noteIndex = $(this).data('note-index');
            let uuid = $(this).data('uuid');
            removeResultFromNote(noteIndex, uuid);
        });
    }

    function openDocumentSearchDialog(noteIndex) {
        if (!patientUuid) {
            alert(jsText(xl('Patient UUID not available')));
            return;
        }

        // Clone the document search template
        const dialogContent = document.getElementById('document-search-template').content.cloneNode(true);

        dlgopen('', 'document-search', 'modal-lg', 600, '',
            jsText(xl('Search Documents')), {
                type: 'Alert',
                html: dialogContent,
                buttons: [
                    {text: jsText(xl('Add Selected')), id: 'select-documents', style: 'primary', disabled: true},
                    {text: jsText(xl('Cancel')), close: true, style: 'secondary'}
                ],
                resolvePromiseOn: 'shown',
                allowResize: true,
                onClosed: false
            }).then(function(dialog) {
            setupDocumentDialogEvents(dialog, noteIndex);
            loadDocuments('', '', ''); // Initial load
        });
    }

    function openResultsSearchDialog(noteIndex) {
        if (!patientUuid) {
            alert(jsText(xl('Patient UUID not available')));
            return;
        }

        // Clone the results search template
        const dialogContent = document.getElementById('results-search-template').content.cloneNode(true);

        dlgopen('', 'results-search', 'modal-lg', 600, '',
            jsText(xl('Search Procedure Results')), {
                type: 'Alert',
                html: dialogContent,
                buttons: [
                    {text: jsText(xl('Add Selected')), id: 'select-results', style: 'info', disabled: true},
                    {text: jsText(xl('Cancel')), close: true, style: 'secondary'}
                ],
                resolvePromiseOn: 'shown',
                allowResize: true,
                onClosed: false
            }).then(function(dialog) {
            setupResultsDialogEvents(dialog, noteIndex);
            loadProcedureResults('', 'laboratory', ''); // Initial load
        });
    }

    function addDocumentsToNote(noteIndex, documents) {
        if (!documents || documents.length === 0) {
            return;
        }

        let container = document.getElementById("linked_documents_" + noteIndex);
        let hiddenInput = document.getElementById("linked_documents_data_" + noteIndex);

        if (!container || !hiddenInput) {
            console.error('Document container or hidden input not found for index:', noteIndex);
            return;
        }

        // Get existing documents
        let existingDocs = [];
        try {
            existingDocs = JSON.parse(hiddenInput.value || '[]');
        } catch (e) {
            console.warn('Error parsing existing documents JSON:', e);
            existingDocs = [];
        }

        // Add new documents (avoid duplicates)
        let addedCount = 0;
        documents.forEach(doc => {
            if (!existingDocs.find(existing => existing.uuid === doc.uuid)) {
                existingDocs.push(doc);
                addedCount++;

                // Add to UI
                addDocumentToUI(container, doc, noteIndex);
            }
        });

        if (addedCount > 0) {
            // Remove "no documents" message if it exists
            let noDocsMsg = container.querySelector('.text-muted');
            if (noDocsMsg && noDocsMsg.textContent.includes('No documents')) {
                noDocsMsg.remove();
            }

            // Update hidden input
            hiddenInput.value = JSON.stringify(existingDocs);
        }
    }

    function addDocumentToUI(container, doc, noteIndex) {
        const badgeTemplate = document.getElementById('document-badge-template');
        if (!badgeTemplate) {
            console.error('Document badge template not found');
            return;
        }

        const docElement = badgeTemplate.content.cloneNode(true);
        const badgeDiv = docElement.querySelector('.document-item');
        const textSpan = docElement.querySelector('.document-badge-text');
        const closeButton = docElement.querySelector('.btn-close-document');

        if (badgeDiv) {
            badgeDiv.setAttribute('data-uuid', doc.uuid);
        }

        if (textSpan) {
            const displayText = doc.name + ' (' + doc.type + ')';
            textSpan.textContent = displayText;
            textSpan.setAttribute('title', displayText);
        }

        if (closeButton) {
            closeButton.setAttribute('data-note-index', noteIndex);
            closeButton.setAttribute('data-uuid', doc.uuid);
        }

        container.appendChild(docElement);
    }

    function removeDocumentFromNote(noteIndex, documentUuid) {
        let container = document.getElementById("linked_documents_" + noteIndex);
        let hiddenInput = document.getElementById("linked_documents_data_" + noteIndex);

        if (!container || !hiddenInput) {
            console.error('Document container or hidden input not found for index:', noteIndex);
            return;
        }

        // Remove from UI
        let docElement = container.querySelector(`[data-uuid="${documentUuid}"]`);
        if (docElement) {
            docElement.remove();
        }

        // Remove from data
        try {
            let existingDocs = JSON.parse(hiddenInput.value || '[]');
            existingDocs = existingDocs.filter(doc => doc.uuid !== documentUuid);
            hiddenInput.value = JSON.stringify(existingDocs);

            // Add "no documents" message if empty
            if (existingDocs.length === 0 && container.children.length === 0) {
                const emptyTemplate = document.getElementById('empty-state-template');
                if (emptyTemplate) {
                    const emptyState = emptyTemplate.content.cloneNode(true);
                    const textElement = emptyState.querySelector('.empty-state-text');
                    if (textElement) {
                        textElement.textContent = jsText(xl('No documents linked'));
                    }
                    container.appendChild(emptyState);
                }
            }
        } catch (e) {
            console.error('Error updating documents data:', e);
        }
    }

    function addResultsToNote(noteIndex, results) {
        if (!results || results.length === 0) {
            return;
        }

        let container = document.getElementById("linked_results_" + noteIndex);
        let hiddenInput = document.getElementById("linked_results_data_" + noteIndex);

        if (!container || !hiddenInput) {
            console.error('Results container or hidden input not found for index:', noteIndex);
            return;
        }

        // Get existing results
        let existingResults = [];
        try {
            existingResults = JSON.parse(hiddenInput.value || '[]');
        } catch (e) {
            console.warn('Error parsing existing results JSON:', e);
            existingResults = [];
        }

        // Add new results (avoid duplicates)
        let addedCount = 0;
        results.forEach(result => {
            if (!existingResults.find(existing => existing.uuid === result.uuid)) {
                existingResults.push(result);
                addedCount++;

                // Add to UI
                addResultToUI(container, result, noteIndex);
            }
        });

        if (addedCount > 0) {
            // Remove "no results" message if it exists
            let noResultsMsg = container.querySelector('.text-muted');
            if (noResultsMsg && noResultsMsg.textContent.includes('No results')) {
                noResultsMsg.remove();
            }

            // Update hidden input
            hiddenInput.value = JSON.stringify(existingResults);
        }
    }

    function addResultToUI(container, result, noteIndex) {
        const badgeTemplate = document.getElementById('result-badge-template');
        if (!badgeTemplate) {
            console.error('Result badge template not found');
            return;
        }

        const resultElement = badgeTemplate.content.cloneNode(true);
        const badgeDiv = resultElement.querySelector('.result-item');
        const textSpan = resultElement.querySelector('.result-badge-text');
        const closeButton = resultElement.querySelector('.btn-close-result');

        if (badgeDiv) {
            badgeDiv.setAttribute('data-uuid', result.uuid);
        }

        if (textSpan) {
            let displayText = result.procedure_name;
            if (result.result_value) {
                displayText += ': ' + result.result_value;
            }
            textSpan.textContent = displayText;

            const tooltipText = displayText + ' (' + result.date + ')';
            textSpan.setAttribute('title', tooltipText);
        }

        if (closeButton) {
            closeButton.setAttribute('data-note-index', noteIndex);
            closeButton.setAttribute('data-uuid', result.uuid);
        }

        container.appendChild(resultElement);
    }

    function removeResultFromNote(noteIndex, resultUuid) {
        let container = document.getElementById("linked_results_" + noteIndex);
        let hiddenInput = document.getElementById("linked_results_data_" + noteIndex);

        if (!container || !hiddenInput) {
            console.error('Results container or hidden input not found for index:', noteIndex);
            return;
        }

        // Remove from UI
        let resultElement = container.querySelector(`[data-uuid="${resultUuid}"]`);
        if (resultElement) {
            resultElement.remove();
        }

        // Remove from data
        try {
            let existingResults = JSON.parse(hiddenInput.value || '[]');
            existingResults = existingResults.filter(result => result.uuid !== resultUuid);
            hiddenInput.value = JSON.stringify(existingResults);

            // Add "no results" message if empty
            if (existingResults.length === 0 && container.children.length === 0) {
                const emptyTemplate = document.getElementById('empty-state-template');
                if (emptyTemplate) {
                    const emptyState = emptyTemplate.content.cloneNode(true);
                    const textElement = emptyState.querySelector('.empty-state-text');
                    if (textElement) {
                        textElement.textContent = jsText(xl('No results linked'));
                    }
                    container.appendChild(emptyState);
                }
            }
        } catch (e) {
            console.error('Error updating results data:', e);
        }
    }

    // Dialog setup and FHIR API functions
    function setupDocumentDialogEvents(dialog, noteIndex) {
        let selectedDocuments = [];

        // Search functionality
        $('#doc-search-term', dialog).on('input', function() {
            debounceDocumentSearch();
        });

        $('#doc-content-type, #doc-date-from', dialog).on('change', function() {
            const searchTerm = $('#doc-search-term', dialog).val() || '';
            const contentType = $('#doc-content-type', dialog).val() || '';
            const dateFrom = $('#doc-date-from', dialog).val() || '';
            loadDocuments(searchTerm, contentType, dateFrom);
        });

        $('#search-documents', dialog).on('click', function() {
            const searchTerm = $('#doc-search-term', dialog).val() || '';
            const contentType = $('#doc-content-type', dialog).val() || '';
            const dateFrom = $('#doc-date-from', dialog).val() || '';
            loadDocuments(searchTerm, contentType, dateFrom);
        });

        // Document selection
        $(dialog).on('click', '.document-item', function() {
            const uuid = $(this).data('uuid');
            const documentData = $(this).data('document');

            if ($(this).hasClass('selected')) {
                // Deselect
                $(this).removeClass('selected');
                selectedDocuments = selectedDocuments.filter(doc => doc.uuid !== uuid);
            } else {
                // Select
                $(this).addClass('selected');
                selectedDocuments.push(documentData);
            }

            updateDocumentSelectedCount(selectedDocuments.length);
        });

        // Add selected documents button
        $('#select-documents', dialog).on('click', function() {
            if (selectedDocuments.length > 0) {
                addDocumentsToNote(noteIndex, selectedDocuments);
                dlgclose();
            }
        });
    }

    function setupResultsDialogEvents(dialog, noteIndex) {
        let selectedResults = [];

        // Search functionality
        $('#result-search-term', dialog).on('input', function() {
            debounceResultsSearch();
        });

        $('#result-category, #result-date-from', dialog).on('change', function() {
            const searchTerm = $('#result-search-term', dialog).val() || '';
            const category = $('#result-category', dialog).val() || '';
            const dateFrom = $('#result-date-from', dialog).val() || '';
            loadProcedureResults(searchTerm, category, dateFrom);
        });

        $('#search-results', dialog).on('click', function() {
            const searchTerm = $('#result-search-term', dialog).val() || '';
            const category = $('#result-category', dialog).val() || '';
            const dateFrom = $('#result-date-from', dialog).val() || '';
            loadProcedureResults(searchTerm, category, dateFrom);
        });

        // Result selection
        $(dialog).on('click', '.result-item', function() {
            const uuid = $(this).data('uuid');
            const resultData = $(this).data('result');

            if ($(this).hasClass('selected')) {
                // Deselect
                $(this).removeClass('selected');
                selectedResults = selectedResults.filter(result => result.uuid !== uuid);
            } else {
                // Select
                $(this).addClass('selected');
                selectedResults.push(resultData);
            }

            updateResultsSelectedCount(selectedResults.length);
        });

        // Add selected results button
        $('#select-results', dialog).on('click', function() {
            if (selectedResults.length > 0) {
                addResultsToNote(noteIndex, selectedResults);
                dlgclose();
            }
        });
    }

    let documentSearchTimeout;
    function debounceDocumentSearch() {
        clearTimeout(documentSearchTimeout);
        documentSearchTimeout = setTimeout(function() {
            const searchTerm = $('#doc-search-term').val() || '';
            const contentType = $('#doc-content-type').val() || '';
            const dateFrom = $('#doc-date-from').val() || '';
            loadDocuments(searchTerm, contentType, dateFrom);
        }, 300);
    }

    let resultsSearchTimeout;
    function debounceResultsSearch() {
        clearTimeout(resultsSearchTimeout);
        resultsSearchTimeout = setTimeout(function() {
            const searchTerm = $('#result-search-term').val() || '';
            const category = $('#result-category').val() || '';
            const dateFrom = $('#result-date-from').val() || '';
            loadProcedureResults(searchTerm, category, dateFrom);
        }, 300);
    }

    function loadDocuments(searchTerm, contentType, dateFrom) {
        // Build FHIR query parameters
        let queryParams = new URLSearchParams({
            'patient': patientUuid,
            '_sort': '-_lastUpdated',
            '_count': '50'
        });

        if (contentType) {
            if (contentType == 'image') {
                contentType = 'image/png,image/jpeg,image/jpg,image/gif';
            }
            if (contentType == 'video') {
                contentType = 'video/mp4,video/avi,video/mov';
            }
            queryParams.append('content-type', contentType);
        }

        if (dateFrom) {
            queryParams.append('_lastUpdated', `ge${dateFrom}`);
        }

        const url = `${top.webroot_url}/apis/default/fhir/Media?${queryParams}`;

        // Show loading state
        showDocumentLoading(true);

        fetch(url, {
            method: 'GET',
            headers: {
                'APICSRFTOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => renderDocumentResults(data, searchTerm))
            .catch(error => handleDocumentError(error));
    }

    function loadProcedureResults(searchTerm, category, dateFrom) {
        // Build FHIR query parameters
        let queryParams = new URLSearchParams({
            'patient': patientUuid,
            '_sort': '-date',
            '_count': '50'
        });

        if (category) {
            queryParams.append('category', category);
        }

        if (dateFrom) {
            queryParams.append('date', `ge${dateFrom}`);
        }

        const url = `${top.webroot_url}/apis/default/fhir/Observation?${queryParams}`;

        // Show loading state
        showResultsLoading(true);

        fetch(url, {
            method: 'GET',
            headers: {
                'APICSRFTOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => renderProcedureResults(data, searchTerm))
            .catch(error => handleResultsError(error));
    }

    function renderDocumentResults(fhirBundle, searchTerm) {
        showDocumentLoading(false);

        const resultsContainer = document.getElementById('document-results');
        const noResultsContainer = document.getElementById('document-no-results');

        if (!fhirBundle.entry || fhirBundle.entry.length === 0) {
            resultsContainer.classList.add('d-none');
            noResultsContainer.classList.remove('d-none');
            return;
        }

        resultsContainer.classList.remove('d-none');
        noResultsContainer.classList.add('d-none');
        resultsContainer.replaceChildren(); // Clear existing content safely

        fhirBundle.entry.forEach(entry => {
            const media = entry.resource;

            // Filter by search term if provided
            if (searchTerm && !matchesDocumentSearchTerm(media, searchTerm)) {
                return;
            }

            const documentItem = createDocumentItem(media);
            resultsContainer.appendChild(documentItem);
        });

        updateDocumentSelectedCount(0);
    }

    function renderProcedureResults(fhirBundle, searchTerm) {
        showResultsLoading(false);

        const resultsContainer = document.getElementById('procedure-results');
        const noResultsContainer = document.getElementById('results-no-results');

        if (!fhirBundle.entry || fhirBundle.entry.length === 0) {
            resultsContainer.classList.add('d-none');
            noResultsContainer.classList.remove('d-none');
            return;
        }

        resultsContainer.classList.remove('d-none');
        noResultsContainer.classList.add('d-none');
        resultsContainer.replaceChildren(); // Clear existing content safely

        fhirBundle.entry.forEach(entry => {
            const observation = entry.resource;

            // Filter by search term if provided
            if (searchTerm && !matchesResultSearchTerm(observation, searchTerm)) {
                return;
            }

            const resultItem = createResultItem(observation);
            resultsContainer.appendChild(resultItem);
        });

        updateResultsSelectedCount(0);
    }

    function createDocumentItem(media) {
        const template = document.getElementById('document-item-template');
        if (!template) {
            console.error('Document item template not found');
            return document.createElement('div');
        }

        const item = template.content.cloneNode(true);
        const itemDiv = item.querySelector('.document-item');
        const nameDiv = item.querySelector('.document-name');
        const typeSpan = item.querySelector('.document-type-badge');
        const sizeSpan = item.querySelector('.document-size');
        const dateSpan = item.querySelector('.document-date');

        const uuid = media.id || '';
        const name = getDocumentName(media);
        const type = getDocumentType(media);
        const date = getDocumentDate(media);
        const size = getDocumentSize(media);

        const documentData = {
            uuid: uuid,
            name: name,
            type: type,
            date: date,
            size: size
        };

        if (itemDiv) {
            itemDiv.setAttribute('data-uuid', uuid);
            itemDiv.setAttribute('data-document', JSON.stringify(documentData));
        }

        if (nameDiv) {
            nameDiv.textContent = name;
        }

        if (typeSpan) {
            typeSpan.textContent = type;
        }

        if (sizeSpan) {
            if (size) {
                sizeSpan.textContent = formatFileSize(size);
            } else {
                sizeSpan.style.display = 'none';
            }
        }

        if (dateSpan) {
            if (date) {
                dateSpan.textContent = formatDate(date);
            } else {
                dateSpan.style.display = 'none';
            }
        }

        return item;
    }

    function createResultItem(observation) {
        const template = document.getElementById('result-item-template');
        if (!template) {
            console.error('Result item template not found');
            return document.createElement('div');
        }

        const item = template.content.cloneNode(true);
        const itemDiv = item.querySelector('.result-item');
        const nameDiv = item.querySelector('.procedure-name');
        const statusDiv = item.querySelector('.result-status');
        const valueDiv = item.querySelector('.result-value');
        const dateSpan = item.querySelector('.result-date');

        const uuid = observation.id || '';
        const procedureName = getObservationCode(observation);
        const resultValue = getObservationValue(observation);
        const date = getObservationDate(observation);
        const status = observation.status || 'unknown';

        const resultData = {
            uuid: uuid,
            procedure_name: procedureName,
            result_value: resultValue,
            date: date,
            status: status
        };

        if (itemDiv) {
            itemDiv.setAttribute('data-uuid', uuid);
            itemDiv.setAttribute('data-result', JSON.stringify(resultData));
        }

        if (nameDiv) {
            nameDiv.textContent = procedureName;
        }

        if (statusDiv) {
            statusDiv.textContent = status;
            statusDiv.classList.add(getStatusClass(status));
        }

        if (valueDiv) {
            valueDiv.textContent = resultValue;
        }

        if (dateSpan) {
            if (date) {
                dateSpan.textContent = formatDate(date);
            } else {
                dateSpan.style.display = 'none';
            }
        }

        return item;
    }

    function updateDocumentSelectedCount(count) {
        const button = document.getElementById('select-documents');
        if (button) {
            button.disabled = count === 0;
            const text = button.textContent.replace(/\(\d+\)/, '').trim();
            button.textContent = count > 0 ? `${text} (${count})` : text;
        }
    }

    function updateResultsSelectedCount(count) {
        const button = document.getElementById('select-results');
        if (button) {
            button.disabled = count === 0;
            const text = button.textContent.replace(/\(\d+\)/, '').trim();
            button.textContent = count > 0 ? `${text} (${count})` : text;
        }
    }

    function showDocumentLoading(show) {
        const loading = document.getElementById('document-loading');
        const results = document.getElementById('document-results');
        const noResults = document.getElementById('document-no-results');

        if (show) {
            loading.classList.remove('d-none');
            results.classList.add('d-none');
            noResults.classList.add('d-none');
        } else {
            loading.classList.add('d-none');
        }
    }

    function showResultsLoading(show) {
        const loading = document.getElementById('results-loading');
        const results = document.getElementById('procedure-results');
        const noResults = document.getElementById('results-no-results');

        if (show) {
            loading.classList.remove('d-none');
            results.classList.add('d-none');
            noResults.classList.add('d-none');
        } else {
            loading.classList.add('d-none');
        }
    }

    function handleDocumentError(error) {
        console.error('Document search error:', error);
        showDocumentLoading(false);

        const errorContainer = document.getElementById('document-error');
        if (errorContainer) {
            errorContainer.textContent = jsText(xl('Error loading documents. Please try again.'));
            errorContainer.classList.remove('d-none');
        }
    }

    function handleResultsError(error) {
        console.error('Results search error:', error);
        showResultsLoading(false);

        const errorContainer = document.getElementById('results-error');
        if (errorContainer) {
            errorContainer.textContent = jsText(xl('Error loading procedure results. Please try again.'));
            errorContainer.classList.remove('d-none');
        }
    }

    // Helper functions for FHIR data extraction
    function getDocumentName(media) {
        console.log(media);
        if (media.content && media.content && media.content.title) {
            return media.content.title;
        }
        return media.identifier && media.identifier[0] ? media.identifier[0].value : 'Unnamed Document';
    }

    function getDocumentType(media) {
        if (media.content && media.content && media.content.contentType) {
            return media.content.contentType;
        }
        return 'unknown';
    }

    function getDocumentDate(media) {
        return media.createdDateTime || media.meta?.lastUpdated || null;
    }

    function getDocumentSize(media) {
        if (media.content && media.content[0] && media.content[0].attachment && media.content[0].attachment.size) {
            return parseInt(media.content[0].attachment.size);
        }
        return null;
    }

    function getObservationCode(observation) {
        if (observation.code && observation.code.coding && observation.code.coding[0]) {
            return observation.code.coding[0].display || observation.code.coding[0].code || 'Unknown Procedure';
        }
        return observation.code?.text || 'Unknown Procedure';
    }

    function getObservationValue(observation) {
        if (observation.valueQuantity) {
            const value = observation.valueQuantity.value || '';
            const unit = observation.valueQuantity.unit || observation.valueQuantity.code || '';
            return `${value} ${unit}`.trim();
        }
        if (observation.valueString) {
            return observation.valueString;
        }
        if (observation.valueCodeableConcept) {
            return observation.valueCodeableConcept.text ||
                (observation.valueCodeableConcept.coding && observation.valueCodeableConcept.coding[0] ?
                    observation.valueCodeableConcept.coding[0].display : '');
        }
        return 'No value';
    }

    function getObservationDate(observation) {
        return observation.effectiveDateTime || observation.issued || null;
    }

    function matchesDocumentSearchTerm(media, searchTerm) {
        const name = getDocumentName(media).toLowerCase();
        const type = getDocumentType(media).toLowerCase();
        const term = searchTerm.toLowerCase();

        return name.includes(term) || type.includes(term);
    }

    function matchesResultSearchTerm(observation, searchTerm) {
        const procedureName = getObservationCode(observation).toLowerCase();
        const resultValue = getObservationValue(observation).toLowerCase();
        const term = searchTerm.toLowerCase();

        return procedureName.includes(term) || resultValue.includes(term);
    }

    function getStatusClass(status) {
        switch (status?.toLowerCase()) {
            case 'final':
                return 'badge-success';
            case 'preliminary':
                return 'badge-warning';
            case 'corrected':
                return 'badge-info';
            default:
                return 'badge-secondary';
        }
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    function formatDate(dateString) {
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString();
        } catch (e) {
            return dateString;
        }
    }

    function init(config) {
        codeArray = config.codeArray;
        defaultType = config.defaultType || '';
        defaultCategory = config.defaultCategory || '';
        patientUuid = config.patientUuid || '';
        csrfToken = config.csrfToken || '';

        // Initialize linking event handlers
        initLinkingEventHandlers();

        // Initialize other components if needed
        $(function () {
            // special case to deal with static and dynamic datepicker items
            $(document).on('mouseover', '.datepicker', function () {
                datetimepickerTranslated('.datepicker', {
                    timepicker: false
                    , showSeconds: false
                    , formatInput: false
                });
            });

            // initialize
            $(".clinical_notes_type").change(typeChange);

            // init code values by triggering the change in case
            // there are any default values set in the template
            $(".clinical_notes_type").trigger("change");
            $(".btn-add").click(duplicateRow);
            $(".btn-delete").click(deleteRow);
            if (typeof config.alertMessage !== 'undefined' && config.alertMessage != '') {
                alert(config.alertMessage);
            }
        });
    }
    window.oeFormsClinicalNotes = {
        init: init
    };
})(window);

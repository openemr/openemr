/**
 * observation.js - Enhanced with QuestionnaireResponse linking functionality
 * AI Generated: Extended from existing observation.js to support FHIR QuestionnaireResponse dialog
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// AI Generated: Global observation form object with QuestionnaireResponse functionality
window.observationForm = {
    webroot: null,
    reasonCodeTypes: null,
    translations: {},
    fhirConfig: null,
    csrfToken: null,

    // AI Generated: Initialize observation form with enhanced functionality
    init: function(webroot, reasonCodeTypes, translations, fhirConfig, csrfToken) {
        this.webroot = webroot;
        this.reasonCodeTypes = reasonCodeTypes;
        this.translations = translations;
        this.fhirConfig = fhirConfig;
        this.csrfToken = csrfToken;

        this.initializeFormHandlers();
        this.initializeSubObservations();
        this.initializeQuestionnaireHandlers();
        this.initializeFormValidation();
    },



    // AI Generated: Initialize questionnaire linking functionality
    initializeQuestionnaireHandlers: function() {
        const self = this;

        // Link questionnaire button
        $(document).on('click', '.questionnaire-link', function() {
            self.openQuestionnaireDialog();
        });

        // Change questionnaire button
        $(document).on('click', '.questionnaire-change', function() {
            self.openQuestionnaireDialog();
        });

        // Remove questionnaire button
        $(document).on('click', '.questionnaire-remove', function() {
            if (confirm(self.getTranslation('CONFIRM_QUESTIONNAIRE_REMOVE'))) {
                self.removeQuestionnaireLink();
            }
        });
    },

    // AI Generated: Open questionnaire selection dialog
    openQuestionnaireDialog: function() {
        const self = this;

        if (!this.fhirConfig || !this.fhirConfig.patient_uuid) {
            alert(this.getTranslation('QUESTIONNAIRE_LOAD_ERROR'));
            return;
        }

        const dialogContent = document.getElementById('questionnaire-dialog-template').content.cloneNode(true);

        dlgopen('', 'questionnaire-selector', 'modal-lg', 600, '',
            this.getTranslation('QUESTIONNAIRE_DIALOG_TITLE'), {
                type: 'alert',
                html: dialogContent,
                buttons: [
                    {text: this.getTranslation('QUESTIONNAIRE_DIALOG_CANCEL'), close: true, style: 'secondary'}
                ],
                resolvePromiseOn: 'shown',
                allowResize: true,
                onClosed: false
            }).then(function(dialog) {
            self.setupDialogEvents(dialog);
            self.loadInitialQuestionnaireData();
        });
    },

    // AI Generated: Setup event handlers within the dialog
    setupDialogEvents: function(dialog) {
        const self = this;

        // Search functionality
        $('#questionnaire-search', dialog).on('input', function() {
            self.debounceSearch();
        });

        $('#date-from, #date-to', dialog).on('change', function() {
            self.loadQuestionnaireData();
        });

        $('#search-questionnaires', dialog).on('click', function() {
            self.loadQuestionnaireData();
        });

        $('#clear-search', dialog).on('click', function() {
            $('#questionnaire-search', dialog).val('');
            $('#date-from', dialog).val('');
            $('#date-to', dialog).val('');
            self.loadQuestionnaireData();
        });

        // Result item interactions
        $(dialog).on('click', '.select-questionnaire', function() {
            const item = $(this).closest('.questionnaire-item');
            self.selectQuestionnaireResponse(item);
        });

        $(dialog).on('click', '.toggle-summary', function() {
            const summary = $(this).siblings('.questionnaire-summary');
            const icon = $(this).find('i');

            summary.collapse('toggle');

            summary.on('shown.bs.collapse', function() {
                icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                $(this).siblings('.toggle-summary').html('<i class="fa fa-chevron-up"></i> ' + this.getTranslation('QUESTIONNAIRE_ITEM_SUMMARY_DETAILS_HIDE'));
            });

            summary.on('hidden.bs.collapse', function() {
                icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                $(this).siblings('.toggle-summary').html('<i class="fa fa-chevron-down"></i> ' + this.getTranslation('QUESTIONNAIRE_ITEM_SUMMARY_DETAILS_SHOW'));
            });
        });
    },

    // AI Generated: Load initial questionnaire data
    loadInitialQuestionnaireData: function() {
        this.loadQuestionnaireData();
    },

    // AI Generated: Load questionnaire responses from FHIR API
    loadQuestionnaireData: function(searchTerm = '', dateFrom = '', dateTo = '') {
        // Get search parameters from dialog if not provided
        if (!searchTerm) searchTerm = $('#questionnaire-search').val() || '';
        if (!dateFrom) dateFrom = $('#date-from').val() || '';
        if (!dateTo) dateTo = $('#date-to').val() || '';

        // Build FHIR query parameters
        let queryParams = new URLSearchParams({
            'patient': this.fhirConfig.patient_uuid,
            '_sort': '-authored',
            '_count': '20'
        });

        if (dateFrom) queryParams.append('authored', `ge${dateFrom}`);
        if (dateTo) queryParams.append('authored', `le${dateTo}`);

        const url = `${this.fhirConfig.base_url}/QuestionnaireResponse?${queryParams}`;

        // Show loading state
        const resultsContainerLoading = document.getElementById('questionnaire-results-loading');
        if (resultsContainerLoading.classList.contains('d-none')) {
            resultsContainerLoading.classList.remove('d-none');
        }

        console.debug("Attempting to fetch data from ", url);
        fetch(url, {
            method: 'GET',
            headers: {
                'APICSRFTOKEN': this.csrfToken,
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
            .then(data => this.renderQuestionnaireResults(data, searchTerm))
            .catch(error => this.handleQuestionnaireError(error));
    },

    // AI Generated: Render questionnaire results in dialog
    renderQuestionnaireResults: function(fhirBundle, searchTerm) {
        document.querySelectorAll('.questionnaire-results').forEach(container => container.classList.add('d-none'));

        const resultsContainer = document.getElementById('questionnaire-results');
        const resultsContainerNoResults = document.getElementById('questionnaire-results-no-results');
        const resultsContainerLoading = document.getElementById('questionnaire-results-loading');

        resultsContainerLoading.classList.add('d-none'); // hide the loading
        if (!fhirBundle.entry || fhirBundle.entry.length === 0) {
            resultsContainerNoResults.classList.remove('d-none');
            return;
        }
        resultsContainer.classList.remove('d-none');
        resultsContainer.replaceChildren([]);

        fhirBundle.entry.forEach(entry => {
            const template = document.getElementById('questionnaire-result-template').content.cloneNode(true);
            const response = entry.resource;

            // Filter by search term if provided
            if (searchTerm && !this.matchesSearchTerm(response, searchTerm)) {
                return;
            }

            template.querySelector('.questionnaire-item').setAttribute('data-fhir-id', response.id);
            template.querySelector('.questionnaire-item').setAttribute('data-response-id', response.id);

            // Set questionnaire name
            const questionnaireName = this.getQuestionnaireName(response);
            let titleNode = template.querySelector('.questionnaire-title');
            titleNode.textContent = questionnaireName;

            // Set date
            template.querySelector('.date-text').innerText = this.formatFhirDate(response.authored);

            // Set status
            const statusBadge = template.querySelector('.questionnaire-status');
            statusBadge.textContent = response.status;
            statusBadge.classList += `badge-${this.getStatusClass(response.status)}`;

            // Set response ID
            template.querySelector('.response-id').textContent = `ID: ${response.id}`;

            // Set summary content
            this.generateResponseSummary(template, response);
            resultsContainer.appendChild(template);
        });
    },

    // AI Generated: Check if questionnaire response matches search term
    matchesSearchTerm: function(response, searchTerm) {
        if (!searchTerm) return true;

        const questionnaireName = this.getQuestionnaireName(response).toLowerCase();
        const searchLower = searchTerm.toLowerCase();

        return questionnaireName.includes(searchLower) ||
            response.id.toLowerCase().includes(searchLower) ||
            (response.status && response.status.toLowerCase().includes(searchLower));
    },

    // AI Generated: Extract questionnaire name from FHIR response
    getQuestionnaireName: function(response) {
        if (response._questionnaire) {
            // Check for display extension first
            if (response._questionnaire.extension) {
                const displayExt = response._questionnaire.extension.find(ext =>
                    ext.url === 'http://hl7.org/fhir/StructureDefinition/display');
                if (displayExt && displayExt.valueString) {
                    return displayExt.valueString;
                }
            }
        }
        if (response.questionnaire) {
            // Fall back to reference
            let parts = response.questionnaire.split("Questionnaire/");
            if (parts.length) {
                return parts[parts.length - 1]; // find the end
            }
        }

        return 'Unknown Questionnaire';
    },

    // AI Generated: Format FHIR date for display
    formatFhirDate: function(fhirDate) {
        if (!fhirDate) return 'Unknown Date';

        try {
            const date = new Date(fhirDate);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        } catch (e) {
            return fhirDate;
        }
    },

    // AI Generated: Get CSS class for status badge
    getStatusClass: function(status) {
        const statusMap = {
            'completed': 'success',
            'in-progress': 'warning',
            'stopped': 'danger',
            'entered-in-error': 'danger',
            'amended': 'info'
        };
        return statusMap[status] || 'secondary';
    },

    // AI Generated: Generate summary of questionnaire responses
    generateResponseSummary: function(templateNode, response) {
        if (!response.item || response.item.length === 0) {
            templateNode.querySelector('.response-no-summary').classList.remove('d-none');
            return;
        } else {
            templateNode.querySelector('.response-summary').classList.remove('d-none');
        }

        // let summary = '<div class="response-summary">';
        //
        let rowTemplateNode = document.getElementById('response-summary-row');
        response.item.forEach((item, index) => {
            if (index >= 30) {
                return;
            }
            let row = rowTemplateNode.content.cloneNode(true);
            if (item.answer && item.answer.length > 0) {
                const firstAnswer = item.answer[0];
                if (firstAnswer.valueString) answer = firstAnswer.valueString;
                else if (firstAnswer.valueInteger) answer = firstAnswer.valueInteger.toString();
                else if (firstAnswer.valueDecimal) answer = firstAnswer.valueDecimal.toString();
                else if (firstAnswer.valueBoolean !== undefined) answer = firstAnswer.valueBoolean ? this.getTranslation('RESPONSE_ANSWER_YES') : this.getTranslation('RESPONSE_ANSWER_NO');
                else if (firstAnswer.valueCoding) answer = firstAnswer.valueCoding.display || firstAnswer.valueCoding.code;
            } else {
                answer = this.getTranslation('RESPONSE_ANSWER_MISSING');
            }
            row.querySelector('.response-summary-question').textContent = item.text || item.linkId || this.getTranslation('RESPONSE_QUESTION_MISSING');
            row.querySelector('.response-summary-answer').textContent = answer;
            templateNode.querySelector('.response-summary').appendChild(row);
        });
        if (response.item.length > 30) {
            templateNode.querySelector('.response-summary-more').classList.remove('d-none');
        }
    },

    // AI Generated: Handle questionnaire selection
    selectQuestionnaireResponse: function(item) {
        const fhirId = item.data('fhir-id');
        const questionnaireName = item.find('.questionnaire-title').text();
        const responseId = item.find('.response-id').text();
        const date = item.find('.date-text').text();
        const status = item.find('.questionnaire-status').text();

        if (!fhirId) {
            alert(this.getTranslation('QUESTIONNAIRE_SELECT_ERROR'));
            return;
        }

        // Update hidden form field
        $('#questionnaire_response_fhir_id').val(fhirId);

        // Update display
        this.updateQuestionnaireDisplay(questionnaireName, responseId, date, status);

        // Close dialog
        $('.modal').modal('hide');
    },

    // AI Generated: Update questionnaire display on main form
    updateQuestionnaireDisplay: function(name, responseId, date, status) {
        const section = $('#questionnaire-section');
        const linkedTemplate = document.getElementById('questionnaire-linked-template').content.cloneNode(true);

        section.html(linkedTemplate);
        section.find('.questionnaire-name').text(name);
        section.find('.response-id').text(responseId);
        section.find('.response-date').text(date);
        section.find('.response-status').text(status);
    },

    // AI Generated: Remove questionnaire link
    removeQuestionnaireLink: function() {
        const section = $('#questionnaire-section');
        const unlinkedTemplate = document.getElementById('questionnaire-unlinked-template').content.cloneNode(true);

        section.html(unlinkedTemplate);
        $('#questionnaire_response_fhir_id').val('');
    },

    // AI Generated: Handle FHIR API errors
    handleQuestionnaireError: function(error) {
        console.error('QuestionnaireResponse API Error:', error);
        let resultContainers = document.querySelector('.questionnaire-results');
        resultContainers.forEach(container => container.classList.add('d-none'));
        document.getElementById('questionnaire-results-error').classList.remove('d-none');

        document.querySelector("#questionnaire-results-error .questionnaire-error-details").textContent = error.message;
        $('#questionnaire-results-error .btn').off('click').on('click', () => {
            this.loadQuestionnaireData();
        });
    },

    // AI Generated: Debounced search functionality
    debounceSearch: function() {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            this.loadQuestionnaireData();
        }, 500);
    },

    // AI Generated: Utility function to escape HTML
    escapeHtml: function(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    initializeFormHandlers: function() {
        if (oeUI.reasonCodeWidget) {
            oeUI.reasonCodeWidget.init(this.webroot, this.reasonCodeTypes);
        } else {
            console.error("Missing required dependency reasonCodeWidget");
            return;
        }


        const self = this;
        // Initialize datepickers
        $(function () {
            datetimepickerTranslated('.datepicker', {
                timepicker: true,
                showSecond: false,
                formatInput: true,
            });
            // setup cancel buttons
            $("#observation_form .btn-cancel").on('click', function() {
                self.closeTab();
            });
        });

        this.initializeCodePickerHandler(this.webroot);

        // Initialize category-based field visibility
        this.initializeCategoryHandlers();
    },

    closeTab: function() {
        if (window.parent.closeTab) {
            window.parent.closeTab(window.name, true);
        } else {
            alert(this.getTranslation('CLOSE_TAB_ERROR'));
        }
    },

    initializeCodePickerHandler: function (webroot) {
        const self = this;
        const $code = $('.code');
        $code.off('click');
        $code.on('click', function() {
            window.set_related = self.set_related.bind(self, this); // Make function globally accessible as required by popup, bind this, and send in the codeElement as first param
            dlgopen(webroot + '/interface/patient_file/encounter/find_code_popup.php', '_blank', 700, 400);
        });
    },

    set_related: function(codeElement, codetype, code, selector, codedesc) {
        console.log(codeElement);
        const $codeElement = $(codeElement);
        $codeElement.val(codetype + ':' + code);
        console.log($codeElement.data());
        if ($codeElement.data('description-target')) {
            $($codeElement.data('description-target')).val(codedesc);
        }
        if ($codeElement.data('display-text-target')) {
            // Update display text, this originally was HTML, but we DO NOT want HTML here
            $($codeElement.data('display-text-target')).text(codedesc);
        }
        if ($codeElement.data('code-type-target')) {
            $($codeElement.data('code-type-target')).text(codetype);
        }
    },
    // AI Generated: Category-based field visibility
    initializeCategoryHandlers: function () {
        const self = this;
        const categorySelects = document.querySelectorAll('select[name*="category"]');
        categorySelects.forEach(function(select) {
            select.addEventListener('change', function() {
                self.handleCategoryChange(this.value, this.closest('.card'));
            });

            // Initialize on page load
            if (select.value) {
                self.handleCategoryChange(select.value, select.closest('.card'));
            }
        });
    },

    getTranslation: function(key) {
        return this.translations[key] || key;
    },

    // AI Generated: Initialize sub-observations functionality
    initializeSubObservations: function() {
        const self = this;
        $(".btn-remove-sub-observation").off('click').on('click', function(e) {
            e.preventDefault();
            self.removeSubObservation(this);
        });

        $(".btn-add-sub-observation").off('click').on('click', function(e) {
            e.preventDefault();
            self.addSubObservation();
        });
    },

    // AI Generated: Initialize form validation
    initializeFormValidation: function() {
        const self = this;
        const form = document.querySelector('form[name="observation_form"]');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!self.validateObservationForm()) {
                    e.preventDefault();
                    return false;
                }
            });
        }
    },
    // AI Generated: Enhanced sub-observation management
    addSubObservation: function () {
        const container = document.getElementById('sub-observations-container');
        if (!container) return;

        const emptyState = container.querySelector('.empty-sub-observations');

        if (emptyState) {
            emptyState.remove();
        }

        // Clone the template from the DOM
        const template = document.getElementById('sub-observation-template');
        if (!template) {
            console.error('Sub-observation template not found');
            return;
        }

        const newSubObs = template.cloneNode(true);

        // Remove template ID and update numbering
        newSubObs.removeAttribute('id');
        this.updateSubObservationNumber(newSubObs, container);

        // Add event listener for remove button
        const self = this;
        const removeBtn = newSubObs.querySelector('.sub-obs-remove');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                self.removeSubObservation(this);
            });
        }

        container.appendChild(newSubObs);

        // Update all numbering
        this.updateAllSubObservationNumbers();

        // setup event handlers for remove buttons
        this.initializeSubObservations();

        // setup code pickers
        this.initializeCodePickerHandler(this.webroot);
    },

    removeSubObservation: function(button) {
        const subObs = button.closest('.sub-observation');
        const container = document.getElementById('sub-observations-container');

        if (subObs && container) {
            if (!confirm(this.getTranslation('CONFIRM_SUB_OBSERVATION_DELETE'))) {
                return;
            }

            subObs.remove();

            // Update numbering
            this.updateAllSubObservationNumbers();

            // If no sub-observations remain, show empty state using template
            if (container.querySelectorAll('.sub-observation').length === 0) {
                const emptyTemplate = document.getElementById('empty-sub-observations-template');
                if (emptyTemplate) {
                    const emptyState = emptyTemplate.cloneNode(true);
                    emptyState.removeAttribute('id');

                    // Add event listener for add first button
                    const addFirstBtn = emptyState.querySelector('.add-first-sub-obs');
                    if (addFirstBtn) {
                        addFirstBtn.addEventListener('click', this.addSubObservation.bind(this));
                    }

                    container.appendChild(emptyState);
                }
            }
        } else {
            console.error('Failed to remove sub-observation: element not found');
        }
    },

    // AI Generated: Update sub-observation numbering
    updateSubObservationNumber: function (subObsElement, container) {
        if (!container) {
            container = document.getElementById('sub-observations-container');
        }

        const allSubObs = container.querySelectorAll('.sub-observation');
        const number = allSubObs.length;

        const numberSpan = subObsElement.querySelector('.sub-obs-number');
        if (numberSpan) {
            numberSpan.textContent = number;
        }
    },
    updateAllSubObservationNumbers: function() {
        const container = document.getElementById('sub-observations-container');
        if (!container) return;

        const allSubObs = container.querySelectorAll('.sub-observation');

        allSubObs.forEach(function(subObs, index) {
            subObs.id = 'sub-observation-' + (index + 1).toString();
            let $code = $(subObs).find('input[name="sub_ob_code[]"]');
            $code.attr('data-description-target', '#sub-description-' + (index + 1).toString());
            $code.attr('data-code-type-target', '#sub-code-type-' + (index + 1).toString());
            $(subObs).find('input[name="sub_description[]"]').attr('id', 'sub-description-' + (index + 1).toString());
            $(subObs).find('input[name="sub_code_type[]"]').attr('id', 'sub-code-type-' + (index + 1).toString());

            const numberSpan = subObs.querySelector('.sub-obs-number');
            if (numberSpan) {
                numberSpan.textContent = (index + 1).toString();
            }
        });
    },
    // AI Generated: Category-based field handling
    handleCategoryChange: function (category, card) {
        if (!card) return;

        const typeSelect = card.querySelector('select[name*="ob_type"]');

        // Update type options based on category
        if (typeSelect) {
            this.updateTypeOptionsForCategory(typeSelect, category);
        }

        // Show/hide category-specific fields
        this.toggleCategorySpecificFields(card, category);
    },

    updateTypeOptionsForCategory: function (typeSelect, category) {
        const categoryTypes = {
            'sdoh': [
                'Housing Status',
                'Food Security',
                'Transportation',
                'Social Support',
                'Employment Status'
            ],
            'functional': [
                'Activities of Daily Living',
                'Instrumental ADL',
                'Mobility Assessment',
                'Functional Capacity'
            ],
            'cognitive': [
                'Mini-Mental State',
                'Clock Drawing Test',
                'Memory Assessment',
                'Executive Function'
            ],
            'physical': [
                'Vital Signs',
                'Physical Examination',
                'Pain Assessment',
                'Mobility'
            ]
        };

        // Clear existing options except first
        while (typeSelect.children.length > 1) {
            typeSelect.removeChild(typeSelect.lastChild);
        }

        // Add category-specific options
        if (categoryTypes[category]) {
            categoryTypes[category].forEach(function(type) {
                const option = document.createElement('option');
                option.value = type.toLowerCase().replace(/\s+/g, '-');
                option.textContent = type;
                typeSelect.appendChild(option);
            });
        }
    },

    toggleCategorySpecificFields: function(card, category) {
        // This could be used to show/hide specific fields based on category
        // For example, SDOH observations might need different fields than vital signs

        const specificFieldsContainer = card.querySelector('.category-specific-fields');
        if (specificFieldsContainer) {
            specificFieldsContainer.className = 'category-specific-fields ' + (category || '');
        }
    },

// AI Generated: Form validation
        validateObservationForm: function () {
        let isValid = true;

        // Clear previous validation messages
        document.querySelectorAll('.validation-error').forEach(el => el.remove());

        // Validate main observation fields
        const codeInput = document.querySelector('input[name="code"]');
        const descriptionInput = document.querySelector('input[name="description"]');
        const dateInput = document.querySelector('input[name="date"]');

        if (codeInput && !codeInput.value.trim()) {
            this.showValidationError(codeInput, this.getTranslation('VALIDATION_CODE_REQUIRED'));
            isValid = false;
        }

        if (descriptionInput && !descriptionInput.value.trim()) {
            this.showValidationError(descriptionInput, this.getTranslation('VALIDATION_DESCRIPTION_REQUIRED'));
            isValid = false;
        }

        if (dateInput && !dateInput.value.trim()) {
            this.showValidationError(dateInput, this.getTranslation('VALIDATION_DATE_REQUIRED'));
            isValid = false;
        }

        // Validate sub-observations if any exist
        const subObservations = document.querySelectorAll('.sub-observation');
        const self = this;
        subObservations.forEach(function(subObs) {
            const subCode = subObs.querySelector('input[name="sub_ob_code[]"]');
            const subValue = subObs.querySelector('input[name="sub_ob_value[]"]');
            const subDescription = subObs.querySelector('input[name="sub_description[]"]');

            if (subCode && subCode.value.trim() && !subValue.value.trim()) {
                self.showValidationError(subValue, self.getTranslation('VALIDATION_SUB_VALUE_REQUIRED'));
                isValid = false;
            }
            if (subValue && subValue.value.trim() && !subDescription.value.trim()) {
                self.showValidationError(subDescription, self.getTranslation('VALIDATION_SUB_DESCRIPTION_REQUIRED'));
                isValid = false;
            }
        });

        if (!isValid) {
            // Scroll to first error
            const firstError = document.querySelector('.validation-error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        return isValid;
    },
    showValidationError: function(field, message) {
        field.classList.add('is-invalid');

        const errorDiv = document.createElement('div');
        errorDiv.className = 'validation-error text-danger small mt-1';
        errorDiv.textContent = message;

        field.parentNode.appendChild(errorDiv);
    }
};

// AI Generated: Ensure xl function is available for translations
if (typeof xl === 'undefined') {
    window.xl = function(text) {
        return text; // Fallback if translation function not available
    };
}

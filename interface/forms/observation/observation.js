// AI Generated Note: Enhanced from original observation.js to support Design 1 card-based layout
(function (window, oeUI) {

    /**
     *
     * @type {string|null} The web root URL for constructing links
     */
    let urlWebRoot = null;

    /**
     * Translated message strings for UI prompts and confirmations
     * @type {{}}
     */
    let translations = {};

    function init(webroot, reasonCodeTypes, translationStrings) {
        urlWebRoot = webroot;
        translations = translationStrings || {};
        // Initialize reason code widgets
        if (oeUI.reasonCodeWidget) {
            oeUI.reasonCodeWidget.init(webroot, reasonCodeTypes);
        } else {
            console.error("Missing required dependency reasonCodeWidget");
            return;
        }


        // Initialize datepickers
        $(function () {
            datetimepickerTranslated('.datepicker', {
                timepicker: true,
                showSecond: false,
                formatInput: false,
            });
        });

        initEditObservationFormFeatures(webroot);
        initializeFormValidation();
        // initializeQuestionnaireFeatures();
    }

    function getTranslation(key) {
        return translations[key] || key;
    }

    // AI Generated: Design 1 specific initialization
    function initEditObservationFormFeatures(webroot) {
        initializeCodePickerHandler(webroot);
        // Initialize sub-observation management
        initializeSubObservationHandlers();

        // Initialize questionnaire linking
        initializeQuestionnaireToggles();

        // Initialize category-based field visibility
        initializeCategoryHandlers();
    }

    function initializeCodePickerHandler(webroot) {
        $('.code').off('click');
        $('.code').on('click', function(e) {
            window.set_related = set_related.bind(this, this); // Make function globally accessible as required by popup, bind this, and send in the codeElement as first param
            dlgopen(webroot + '/interface/patient_file/encounter/find_code_popup.php', '_blank', 700, 400);
        });
    }

    function set_related(codeElement, codetype, code, selector, codedesc) {
        console.log(codeElement);
        $codeElement = $(codeElement);
        $codeElement.val(codetype + ':' + code);
        console.log($codeElement.data());
        if ($codeElement.data('description-target')) {
            $('#' + $codeElement.data('description-target')).val(codedesc);
        }
        if ($codeElement.data('display-text-target')) {
            // Update display text, this originally was HTML, but we DO NOT want HTML here
            $('#' + $codeElement.data('display-text-target')).text(codedesc);
        }
        if ($codeElement.data('code-type-target')) {
            $('#' + $codeElement.data('code-type-target')).text(codetype);
        }
    }

    function initializeSubObservationHandlers() {
        $(".btn-remove-sub-observation").off('click').on('click', function(e) {
            e.preventDefault();
            removeSubObservation(this);
        });

        $(".btn-add-sub-observation").off('click').on('click', function(e) {
            e.preventDefault();
            addSubObservation();
        });
    }

    // AI Generated: Questionnaire linking functionality
    function initializeQuestionnaireToggles() {
        // Link questionnaire button
        document.addEventListener('click', function(e) {
            if (e.target.matches('[onclick*="linkQuestionnaire"]') ||
                e.target.textContent.includes('Link Questionnaire')) {
                e.preventDefault();
                showQuestionnaireSelector();
            }
        });

        // Remove questionnaire link button
        document.addEventListener('click', function(e) {
            if (e.target.textContent.includes('Remove Link')) {
                e.preventDefault();
                removeQuestionnaireLink();
            }
        });
    }

    // AI Generated: Category-based field visibility
    function initializeCategoryHandlers() {
        const categorySelects = document.querySelectorAll('select[name*="category"]');
        categorySelects.forEach(function(select) {
            select.addEventListener('change', function() {
                handleCategoryChange(this.value, this.closest('.card'));
            });

            // Initialize on page load
            if (select.value) {
                handleCategoryChange(select.value, select.closest('.card'));
            }
        });
    }

    // AI Generated: Form validation for Design 1
    function initializeFormValidation() {
        const form = document.querySelector('form[name="observation_form"]');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!validateObservationForm()) {
                    e.preventDefault();
                    return false;
                }
            });
        }
    }


// AI Generated: Enhanced sub-observation management
    function addSubObservation() {
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
        updateSubObservationNumber(newSubObs, container);

        // Add event listener for remove button
        const removeBtn = newSubObs.querySelector('.sub-obs-remove');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                removeSubObservation(this);
            });
        }

        container.appendChild(newSubObs);

        // Update all numbering
        updateAllSubObservationNumbers();

        // setup event handlers for remove buttons
        initializeSubObservationHandlers();

        // setup code pickers
        initializeCodePickerHandler(urlWebRoot);
    }

    function removeSubObservation(button) {
        const subObs = button.closest('.sub-observation');
        const container = document.getElementById('sub-observations-container');

        if (subObs && container) {
            if (!confirm(getTranslation('CONFIRM_SUB_OBSERVATION_DELETE'))) {
                return;
            }

            subObs.remove();

            // Update numbering
            updateAllSubObservationNumbers();

            // If no sub-observations remain, show empty state using template
            if (container.querySelectorAll('.sub-observation').length === 0) {
                const emptyTemplate = document.getElementById('empty-sub-observations-template');
                if (emptyTemplate) {
                    const emptyState = emptyTemplate.cloneNode(true);
                    emptyState.removeAttribute('id');

                    // Add event listener for add first button
                    const addFirstBtn = emptyState.querySelector('.add-first-sub-obs');
                    if (addFirstBtn) {
                        addFirstBtn.addEventListener('click', addSubObservation);
                    }

                    container.appendChild(emptyState);
                }
            }
        } else {
            console.error('Failed to remove sub-observation: element not found');
        }
    }

// AI Generated: Update sub-observation numbering
    function updateSubObservationNumber(subObsElement, container) {
        if (!container) {
            container = document.getElementById('sub-observations-container');
        }

        const allSubObs = container.querySelectorAll('.sub-observation');
        const number = allSubObs.length;

        const numberSpan = subObsElement.querySelector('.sub-obs-number');
        if (numberSpan) {
            numberSpan.textContent = number;
        }
    }

    function updateAllSubObservationNumbers() {
        const container = document.getElementById('sub-observations-container');
        if (!container) return;

        const allSubObs = container.querySelectorAll('.sub-observation');

        allSubObs.forEach(function(subObs, index) {
            subObs.id = 'sub-observation-' + (index + 1).toString();
            let $code = $(subObs).find('input[name="sub_ob_code[]"]');
            $code.attr('data-description-target', 'sub-description-' + (index + 1).toString());
            $code.attr('data-code-type-target', 'sub-code-type-' + (index + 1).toString());
            $(subObs).find('input[name="sub_description[]"]').attr('id', 'sub-description-' + (index + 1).toString());
            $(subObs).find('input[name="sub_code_type[]"]').attr('id', 'sub-code-type-' + (index + 1).toString());

            const numberSpan = subObs.querySelector('.sub-obs-number');
            if (numberSpan) {
                numberSpan.textContent = (index + 1).toString();
            }
        });
    }

// AI Generated: Questionnaire management functions
    function showQuestionnaireSelector() {
        // In a real implementation, this would open a modal or dropdown
        // with available questionnaires
        const questionnaires = [
            'PHQ-9 Depression Screening',
            'GAD-7 Anxiety Assessment',
            'MMSE Cognitive Assessment',
            'Social Determinants Assessment'
        ];

        const selection = prompt('Available Questionnaires:\n' +
            questionnaires.map((q, i) => `${i + 1}. ${q}`).join('\n') +
            '\n\nEnter the number of the questionnaire to link:');

        if (selection && !isNaN(selection) && selection > 0 && selection <= questionnaires.length) {
            linkQuestionnaire(questionnaires[selection - 1], Date.now());
        }
    }

    function linkQuestionnaire(questionnaireName, responseId) {
        const questionnaireSection = document.querySelector('.questionnaire-section');
        if (questionnaireSection) {
            questionnaireSection.outerHTML = `
            <div class="questionnaire-linked bg-light-green border border-success rounded p-3 text-center">
                <h5 class="text-success mb-2">Linked: ${questionnaireName}</h5>
                <p class="text-muted mb-3">Response ID: ${responseId}</p>
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary btn-sm">View Response</button>
                    <button type="button" class="btn btn-secondary btn-sm">Change</button>
                    <button type="button" class="btn btn-danger btn-sm">Remove Link</button>
                </div>
                <input type="hidden" name="questionnaire_response_id[]" value="${responseId}">
            </div>
        `;
        }
    }

    function removeQuestionnaireLink() {
        if (confirm('Are you sure you want to remove the questionnaire link?')) {
            const questionnaireLinked = document.querySelector('.questionnaire-linked');
            if (questionnaireLinked) {
                questionnaireLinked.outerHTML = `
                <div class="questionnaire-section bg-light border-2 border-dashed rounded p-4 text-center">
                    <h5 class="text-muted mb-2">No Questionnaire Linked</h5>
                    <p class="text-muted mb-3">Link a questionnaire response to provide additional context for this observation</p>
                    <button type="button" class="btn btn-primary" onclick="showQuestionnaireSelector()">Link Questionnaire</button>
                </div>
            `;
            }
        }
    }

// AI Generated: Category-based field handling
    function handleCategoryChange(category, card) {
        if (!card) return;

        const typeSelect = card.querySelector('select[name*="ob_type"]');

        // Update type options based on category
        if (typeSelect) {
            updateTypeOptionsForCategory(typeSelect, category);
        }

        // Show/hide category-specific fields
        toggleCategorySpecificFields(card, category);
    }

    function updateTypeOptionsForCategory(typeSelect, category) {
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
    }

    function toggleCategorySpecificFields(card, category) {
        // This could be used to show/hide specific fields based on category
        // For example, SDOH observations might need different fields than vital signs

        const specificFieldsContainer = card.querySelector('.category-specific-fields');
        if (specificFieldsContainer) {
            specificFieldsContainer.className = 'category-specific-fields ' + (category || '');
        }
    }

// AI Generated: Form validation
    function validateObservationForm() {
        let isValid = true;
        const errors = [];

        // Clear previous validation messages
        document.querySelectorAll('.validation-error').forEach(el => el.remove());

        // Validate main observation fields
        const codeInput = document.querySelector('input[name="code[]"]');
        const descriptionInput = document.querySelector('input[name="description[]"]');
        const dateInput = document.querySelector('input[name="code_date[]"]');

        // TODO: @adunsulag need to handle translation of validation errors
        if (codeInput && !codeInput.value.trim()) {
            showValidationError(codeInput, getTranslation('VALIDATION_CODE_REQUIRED'));
            isValid = false;
        }

        if (descriptionInput && !descriptionInput.value.trim()) {
            showValidationError(descriptionInput, getTranslation('VALIDATION_DESCRIPTION_REQUIRED'));
            isValid = false;
        }

        if (dateInput && !dateInput.value.trim()) {
            showValidationError(dateInput, getTranslation('VALIDATION_DATE_REQUIRED'));
            isValid = false;
        }

        // Validate sub-observations if any exist
        const subObservations = document.querySelectorAll('.sub-observation');
        subObservations.forEach(function(subObs, index) {
            const subCode = subObs.querySelector('input[name="sub_ob_code[]"]');
            const subValue = subObs.querySelector('input[name="sub_ob_value[]"]');
            const subDescription = subObs.querySelector('input[name="sub_description[]"]');

            if (subCode && subCode.value.trim() && !subValue.value.trim()) {
                showValidationError(subValue, getTranslation('VALIDATION_SUB_VALUE_REQUIRED'));
                isValid = false;
            }
            if (subValue && subValue.value.trim() && !subDescription.value.trim()) {
                showValidationError(subDescription, getTranslation('VALIDATION_SUB_DESCRIPTION_REQUIRED'));
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
    }

    function showValidationError(field, message) {
        field.classList.add('is-invalid');

        const errorDiv = document.createElement('div');
        errorDiv.className = 'validation-error text-danger small mt-1';
        errorDiv.textContent = message;

        field.parentNode.appendChild(errorDiv);
    }


    let form = {
        "init": init
    };
    window.observationForm = form;

})(window, window.oeUI || {});

// Legacy functions maintained for backward compatibility
function clearReasonCode(newRow) {
    // Make sure we clear everything out
    let inputs = newRow.querySelectorAll(".reasonCodeContainer input");
    inputs.forEach(function (input) {
        input.value = "";
    });
    // Make sure we are hiding the thing
    let container = newRow.querySelector(".reasonCodeContainer");
    if (container) {
        container.classList.add("d-none");
    }
}

function removeVal(rowid) {
    rowid1 = rowid.split('tb_row_');
    if (rowid1.length > 1) {
        const id = rowid1[1];
        ['comments', 'code', 'description', 'code_date', 'code_type', 'table_code',
            'ob_value', 'ob_unit', 'ob_value_phin', 'code_date_end'].forEach(function(field) {
            const element = document.getElementById(field + "_" + id);
            if (element) {
                if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                    element.value = '';
                } else {
                    element.innerHTML = '';
                }
            }
        });
    }
}

function changeDatasetIds(propertySelector, dataSetProperty, keyPrefix) {
    var elements = document.querySelectorAll('[data-' + propertySelector + ']');
    if (elements) {
        elements.forEach(function (element, index) {
            element.dataset[dataSetProperty] = keyPrefix + "_" + (index + 1);
        });
    }
}

function changeIds(class_val) {
    var elem = document.getElementsByClassName(class_val);
    for (let i = 0; i < elem.length; i++) {
        if (elem[i].id) {
            index = i + 1;
            elem[i].id = class_val + "_" + index;
        }
    }
}

function deleteRow(event, rowId, rowCount) {
    if (rowCount > 1) {
        let elem = document.getElementById(rowId);
        if (elem && elem.parentNode) {
            elem.parentNode.removeChild(elem);
        }
    }
    if (window.oeUI && window.oeUI.reasonCodeWidget) {
        window.oeUI.reasonCodeWidget.reload();
    }
}

/**
 * This class is responsible for rendering the patient edit insurance policy screen and handling the events for it.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
let {InsurancePolicyService} = await import("./InsurancePolicyService.js?v=" + window.top.jsGlobals.assetVersion);
let {InsurancePolicyModel} = await import("./InsurancePolicyModel.js?v=" + window.top.jsGlobals.assetVersion);
let {ValidationFieldError, ValidationError} = await import("../Error/ValidationError.js?v=" + window.top.jsGlobals.assetVersion);

export class EditPolicyScreenController
{
    __types = null;

    /**
     * @type {InsurancePolicyModel}
     */
    selectedInsurance = null;

    __insuranceProviderList = null;

    __insurancesByType = {};

    __screenName = 'edit-policy-screen';

    /**
     * @type {InsurancePolicyService}
     * @private
     */
    __insurancePolicyService = null;

    __selectedInsuranceTypeTab = null;

    // this is used to keep track of any events as part of the render process that we bind to the DOM so we can clean them up when we are done
    #boundEvents = [];

    // these are events we bind at the beginning during the setup process
    #boundSetupEvents = [];
    __validationErrors = [];

    /**
     * Boolean flag that controls messages / controls to indicate saving progress to the user.
     * @type {boolean}
     * @private
     */
    __isSaving = false;

    __policySaved = false;

    __csrfToken = null;

    __currentFocusedInputName = null;

    __updatingSubscriberSelfRelationship = false;

    /**
     *
     * @param insurancePolicyService InsurancePolicyService
     */
    constructor(csrfToken, insurancePolicyService) {
        this.__csrfToken = csrfToken;
        this.__insuranceProviderList = insurancePolicyService.getInsuranceProvidersList();
        this.__types = insurancePolicyService.getInsuranceCategories();
        this.__insurancesByType = insurancePolicyService.getInsurancesByType();
        this.__insurancePolicyService = insurancePolicyService;
    }

    hasDataToSave() {
        if (this.selectedInsurance && this.selectedInsurance.hasChanged()) {
            return true;
        }
        return false;
    }
    resetSaveData() {
        if (this.selectedInsurance) {
            let insurancesByType = this.__insurancesByType[this.__selectedInsuranceTypeTab];
            let resetInsurance = insurancesByType.find(insurance => insurance.id == this.selectedInsurance.id);
            if (resetInsurance) {
                this.#updateSelectedInsuranceObject(resetInsurance);
            }
        }
    }

    addCustomEventCleanupAction(evtObj, callback) {
        this.#boundEvents.push({customCallback: callback, event: evtObj});
    }
    addEvent(node, event, callback) {
        node.addEventListener(event, callback);
        this.#boundEvents.push({node: node, event: event, callback: callback});
    }

    clearEvents() {
        this.#boundEvents.forEach(event => {
            if (typeof event.customCallback == 'function') {
                event.customCallback(event.event);
            } else {
                event.node.removeEventListener(event.event, event.callback);
            }
        });
        this.#boundEvents = [];
    }

    clearSetupEvents() {
        this.#boundSetupEvents.forEach(event => {
            if (event.node == window) {
                window.removeEventListener(event.event, event.callback);
            } else {
                event.node.removeEventListener(event.event, event.callback);
            }
        });
        this.#boundSetupEvents = [];
    }

    /**
     *
     * @param InsurancePolicyModel newPolicyData
     */
    setupNewPolicyEdit(newPolicyData) {
        this.#updateSelectedInsuranceObject(newPolicyData);
        this.__selectedInsuranceTypeTab = newPolicyData.type;
        this.__insurancesByType = this.__insurancePolicyService.getInsurancesByType();
        this.setup();
    }

    setup() {
        this.toggleDisplay(true); // make sure we are on.
        this.#setupInsuranceTypeNavigation();
        this.#setupSavePolicyButton();
        this.#setupWindowEvents();
        this.#populateSelectDropdowns();
        // this is where we would populate the most current insurance
        this.setupInitialInsurance();
        this.render();
    }

    #setupWindowEvents() {
        let focusInHandler = (evt) => {
            if (evt.target.nodeName == 'INPUT' || evt.target.nodeName == 'SELECT') {
                this.__currentFocusedInputName = evt.target.name;
            }
        };
        window.addEventListener('focusin', focusInHandler);
        this.#boundSetupEvents.push({node: window, event: 'focusin', callback: focusInHandler});
        // by doing things this way we abstract the communication from the patient selection piece to this controller
        // we also are safer by grabbing the patient uuid and insurance uuid and refetching just to make sure we have
        // the right data.
        let messageHandler = (evt) => {
            if (event.origin !== window.location.origin) {
                return; // we only receive events from our same domain
            }
            if (evt.data && (typeof evt.data.action == 'string')) {
                if (evt.data.action == 'insurance-patient-browser-selected') {
                    let patientUuid = evt.data.patientUuid || null;
                    let insuranceUuid = evt.data.insuranceUuid || null;
                    if (!patientUuid || !insuranceUuid) {
                        alert(window.top.xl("No patient was selected to copy values from."));
                        return;
                    }
                    // this.__openedSubWindows.forEach(w => w.close()); // close all of the sub windows, not sure why the window.close doesn't work from inside the sub window but we'll do it this way.
                    let patientInsuranceObject = this.__insurancePolicyService.getPatientInsuranceData(patientUuid, insuranceUuid)
                        .then(result => {
                            // copy that data into the currently selected insurance
                            // make sure we don't overwrite the data that is specific to this insurance
                            // TODO: perhaps make a populateProtectedFields method that will skip over protected data fields
                            result.id = this.selectedInsurance.id;
                            result.uuid = this.selectedInsurance.uuid;
                            result.type = this.selectedInsurance.type;
                            result.pid = this.selectedInsurance.pid;
                            result.puuid = this.selectedInsurance.puuid;
                            this.selectedInsurance.populate(result);
                            this.#updateSelectedInsuranceObject(this.selectedInsurance);
                            this.render();
                        })
                        .catch(error => {
                            console.error(error);
                            alert(window.top.xl("An error occurred while loading the insurance policy. Please try again or contact your system administrator."));
                        });
                    this.render();
                }
                else if (evt.data.action == 'insurance-search-set-insurance') {
                    let insuranceCompanyId = evt.data.insuranceId || null;
                    let insuranceCompanyName = evt.data.insuranceName || null;
                    if (!insuranceCompanyId || !insuranceCompanyName) {
                        alert(window.top.xl("An error occurred while loading the insurance company information."));
                        console.error("Failed to find insurance company id or name in event data, this should not happen and is a bug. Event: ", evt);
                        return;
                    }
                    this.selectedInsurance.setProperty('provider', insuranceCompanyId);
                    this.__insurancePolicyService.addInsuranceProviderToList(insuranceCompanyId, insuranceCompanyName);
                    this.__insuranceProviderList = this.__insurancePolicyService.getInsuranceProvidersList();
                    this.render();
                }
            }
        };
        window.addEventListener("message", messageHandler);
        this.#boundSetupEvents.push({node: window, event: 'message', callback: messageHandler});
    }
    #setupSavePolicyButton() {
        // setup event listener for btn-save-policy to save the policy
        let btnSavePolicy = document.querySelectorAll('.btn-save-policy');

        if (btnSavePolicy) {
            let savePolicyHandler = (evt) => {
                evt.preventDefault();
                evt.stopPropagation();
                this.savePolicy();
            }
            btnSavePolicy.forEach(b => {
                b.addEventListener('click', savePolicyHandler);
                this.#boundSetupEvents.push({node: b, event: 'click', callback: savePolicyHandler});
            });
        }
    }
    #updateSubscriberRelationshipWithPatientData() {
        this.__updatingSubscriberSelfRelationship = true;
        this.render();
        this.__insurancePolicyService.getCurrentPatientData()
        .then(result => {
            this.__updatingSubscriberSelfRelationship = false;
            if (this.selectedInsurance) {
                this.selectedInsurance.populateWithPatientData(result);
            }
            this.render();
        })
        .catch(error => {
            console.error(error);
            this.__updatingSubscriberSelfRelationship = false;
            this.render();
            window.top.xl("Could not retrieve patient data for subscriber relationship.");
        });
    }
    #setupModelSyncBinding(selectedInsurance) {
        let type = selectedInsurance.type;
        let insuranceInfoContainer = document.getElementById('insurance-info-type-' + type);

        let keys = Object.keys(selectedInsurance);
        keys.forEach(key => {
            let input = insuranceInfoContainer.querySelector('[name="form_' + key + '"]');
            if (input) {
                if (input.classList.contains("datepicker")) {
                    return; // we don't want to do anything with datepickers as they are handled by the plugin
                }
                else if (input.nodeName !== "SELECT") {
                    this.addEvent(input, 'change', (evt) => {
                        let input = evt.target;
                        let key = input.name.replace('form_', '');
                        let value = input.value;
                        this.selectedInsurance.setProperty(key,value);
                        this.render(); // re-render the screen to update the display
                    });
                } else {
                    // capture the events for the select2 events and update the selectedInsurance
                    $(input).on('select2:open', (evt) => {
                        this.__currentFocusedInputName = evt.target.name;
                    });
                    $(input).on('select2:select', (evt) => {
                    // this.addEvent(input, 'select2:select', (evt) => {
                        let input = evt.target;
                        let key = input.name.replace('form_', '');
                        let value = input.value;
                        this.selectedInsurance.setProperty(key, value);
                        if (key == 'subscriber_relationship' && value == 'self') {
                            // set our flag for updatingSubscriberRelationship
                            this.#updateSubscriberRelationshipWithPatientData();
                        } else {
                            this.render(); // re-render the screen to update the display
                        }
                    });
                }
            }
        });

        // setup the event listeners for the capitalize me fields
        insuranceInfoContainer.querySelectorAll('.js-capitalize-me').forEach(input => {
            this.addEvent(input, 'change', this.capitalizeMe);
        });

        // setup the event listeners for the policykeyup fields
        if (typeof window.policykeyup == 'function') {
            // comes from common.js
            insuranceInfoContainer.querySelectorAll('.js-policykeyup').forEach(input => {
                // need to bind up the input to the function argument due to the way the function is written
                this.addEvent(input, 'keyup', function () {
                    window.policykeyup(this); // this is the input element
                });
            });
        }
        // let's destroy any existing date pickers and destroy them
        // $(insuranceInfoContainer).find('.datepicker').datetimepicker('destroy');
        // setup the event listeners for the date picker fields
        datetimepickerTranslated("#" + insuranceInfoContainer.id + " .datepicker", {
            timepicker: false
            ,showSeconds: false
            ,formatInput: true
            ,minDate: false
            ,maxDate: false
            ,onClose: (selectedDateTime, $input) => {
            //,onSelectDate: (selectedDateTime, $input) => {
                let input = $input[0];
                let key = input.name.replace('form_', '');
                let value = input.value;
                // datetime selected
                this.selectedInsurance.setProperty(key, selectedDateTime);
            }
        });

        // let's make sure when we clean our events up that we destroy any existing date pickers
        this.addCustomEventCleanupAction({selector: "#" + insuranceInfoContainer.id + " .datepicker"}, (evt) => {
            let datepickers = $(evt.selector);
            datepickers.datetimepicker('destroy');
        });

        // setup the bootstrap select fields
        select2Translated("#" + insuranceInfoContainer.id + " .sel2", undefined, {
            theme: "bootstrap4"
            ,dropdownAutoWidth: true
            ,width: "resolve"
        });

        this.addCustomEventCleanupAction({selector: "#" + insuranceInfoContainer.id + " .sel2"}, (evt) => {
            let selects = $(evt.selector);
            selects.off('select2:open');
            selects.off('select2:select'); // make sure we remove the event listeners
            selects.select2('destroy');
        });

        // setup the dialog popup
        insuranceInfoContainer.querySelector('.subscriber-relationship-open-finder').addEventListener('click', (evt) => {
                let input = evt.target;
                let url = new URL(input.href);
                // url.searchParams.append('csrf_token_form', this.__csrfToken);
                let dlgUrl = url.pathname + url.search;
                evt.preventDefault();
                window.top.restoreSession();
                let id = (new Date()).getTime();
                window.open(dlgUrl, id, 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=400,height=450,left = 440,top = 362');
        });

        insuranceInfoContainer.querySelector('.insurance-search-open-finder').addEventListener('click', (evt) => {
            let input = evt.target;
            let url = new URL(input.href);
            if (this.selectedInsurance && this.selectedInsurance.provider) {
                url.searchParams.set('ins', this.selectedInsurance.provider);
            }
            let relativeUrl = url.pathname + url.search;
            evt.preventDefault();
            dlgopen('', '', 700, 600, '', input.dataset['modalTitle'], {
                buttons: [
                    {text: window.top.xl('Close'), close: true, style: 'default btn-sm'}
                ],
                allowResize: true,
                allowDrag: true,
                dialogId: '',
                type: 'iframe',
                url: relativeUrl
            });
        });
    }

    #setupAddressValidation(selectedInsurance) {
        let type = selectedInsurance.type;
        let insuranceInfoContainer = document.getElementById('insurance-info-type-' + type);
        if (!insuranceInfoContainer) {
            throw new Error("Failed to find insurance info container for type: " + type);
        }
        // setup the js-verify-address fields
        let template = document.getElementById('insurance-verify-address-template');
        // we only bind the usps address verify events if the template is in the DOM (which it will be only if the event
        // is included
        if (template) {
            insuranceInfoContainer.querySelectorAll('.js-verify-address').forEach(input => {
                let clone = document.importNode(template.content, true);
                input.parentNode.insertBefore(clone, input.nextSibling);
                let topNode = input.parentNode;
                let btn = topNode.querySelector('.btn');
                btn.addEventListener('click', (evt) => {
                    let address;
                    if (input.name.match(/subscriber_employer/)) {
                        address = this.selectedInsurance.getEmployerAddress();
                    } else {
                        address = this.selectedInsurance.getSubscriberAddress();
                    }
                    this.#verifyAddress(evt, address);
                });
            });
        }
    }

    #verifyAddress(evt, address) {
        window.top.restoreSession();
        dlgopen('../../practice/address_verify.php?address1=' + encodeURIComponent(address.street) +
            '&address2=' + encodeURIComponent(address.street_line_2) +
            '&city=' + encodeURIComponent(address.city) +
            '&state=' + encodeURIComponent(address.state) +
            '&zip5=' + encodeURIComponent(address.postal_code.substring(0,5)) +
            '&zip4=' + encodeURIComponent(address.postal_code.substring(5,9))
            , '_blank', 400, 150, '', xl('Address Verify'));

        return false;
    }

    savePolicy() {
        // need to grab the selected insurance and save it
        this.__validationErrors = []; // reset our errors
        this.__isSaving = true;
        this.__policySaved = false;
        this.render(); // re-render the screen to update the display
        this.__insurancePolicyService.saveInsurance(this.selectedInsurance)
            .then(result => {
                this.#updateSelectedInsuranceObject(result);
                this.__insurancesByType = this.__insurancePolicyService.getInsurancesByType();
                this.__policySaved = true;
            })
            .catch(error => {
                if (error instanceof ValidationError) {
                    this.__validationErrors = error.validationErrors;
                    console.error(error);
                } else {
                    console.error(error);
                    alert("An error occurred while saving the insurance policy.  Please try again or contact your system administrator.");
                }
            })
            .finally(() => {
                this.__isSaving = false;
                this.render(); // re-render the screen to update the display
            });
    }

    setupInitialInsurance() {
        if (this.selectedInsurance === null) {
            let defaultType = this.__types[0];
            this.__selectedInsuranceTypeTab = defaultType;
            let insurances = this.__insurancesByType[defaultType];
            this.#updateSelectedInsuranceObject(insurances[0] || null);
        }

    }
    hide() {
        this.toggleDisplay(false);
    }
    show() {
        this.toggleDisplay(true);
        this.render(); // make sure to update ourselves
    }
    toggleDisplay(display) {
        let policyScreen = document.getElementById(this.__screenName);
        if (display) {
            policyScreen.classList.remove("d-none");
        } else {
            policyScreen.classList.add("d-none");
        }
    }
    destroy() {
        this.clearSetupEvents();
    }


    #populateInsuranceProviderListForNode(node, insuranceProviderList) {
        let select = node.querySelector('[name="form_provider"]');
        if (select) {
            let ids = Object.keys(insuranceProviderList);
            // need to sort the names but retain the ids
            ids.sort((a, b) => {
                let nameA = insuranceProviderList[a];
                let nameB = insuranceProviderList[b];
                return nameA < nameB ? -1 : 1;
            });
            ids.forEach(providerId => {
                let option = document.createElement('option');
                option.value = providerId;
                // name of the insurance provider is in the list
                option.innerText = insuranceProviderList[providerId];
                select.appendChild(option);
            });
        }
    }


    #setupInsuranceTypeNavigation() {
        // grab nav-link-insurance-type elements and setup click handlers for them
        // when the user clicks on one of them we need to hide all the tabs and show the one they clicked on
        // we also should populate the insurance information for the first insurance in the list for that insurance type
        let navLinks = document.querySelectorAll('.nav-link-insurance-type');
        if (navLinks) {
            navLinks.forEach(link => {
                link.addEventListener('click', (evt) => {
                    // cancel the evt so we don't navigate to a new page
                    evt.preventDefault();
                    evt.stopPropagation();

                    this.__selectedInsuranceTypeTab = evt.target.dataset.type;
                    // need to default to the top insurance in the list
                    if (!this.__insurancesByType[this.__selectedInsuranceTypeTab].length) {
                        console.error("Failed to find insurance for type: " + this.__selectedInsuranceTypeTab);
                    }
                    this.#updateSelectedInsuranceObject(this.__insurancesByType[this.__selectedInsuranceTypeTab][0]);
                    this.render();
                });
            });
        }
    }

    #updateSelectedInsuranceObject(selectedInsurance) {
        // we want to work with a copy of the insurance object so we don't modify the original until we save
        let newInsurance = new InsurancePolicyModel();
        let markAsUnchanged = selectedInsurance.hasChanged() ? false : true;
        newInsurance.populate(selectedInsurance, markAsUnchanged);
        this.selectedInsurance = newInsurance;
        this.__policySaved = false;
        this.__isSaving = false;
        this.__validationErrors = []; // clear out any existing errors
    }

    render() {
        let type = this.__selectedInsuranceTypeTab;
        let selectedInsuranceTypeAnchor = document.querySelector('.nav-link-insurance-type[data-type="' + type + '"]');
        let selectedInsuranceTypeTab = selectedInsuranceTypeAnchor.closest('.nav-link-insurance-type-container');
        // need to remove the current class from all of the links and mark the clicked link as current
        document.querySelectorAll('.nav-link-insurance-type').forEach(l => {
            l.closest('.nav-link-insurance-type-container').classList.remove('current');
        });
        selectedInsuranceTypeTab.classList.add('current');

        // need to hide all of the insurance-type-no-policies elements and show the one for the selected type
        document.querySelectorAll('.insurance-type-no-policies').forEach(el => {
            el.classList.add('d-none');
        });

        let noPolicies = document.querySelector('.insurance-type-no-policies-' + type);
        let typeHasNoExistingPolicy = this.__insurancesByType[type].find(insurance => insurance.id != null) === undefined;
        let selectorRow = document.querySelector('.insurance-type-selector-row-' + type);
        if (noPolicies && typeHasNoExistingPolicy) {
            noPolicies.classList.remove('d-none');
            // grab the selector row .insurance-type-selector-row-<type> and hide it

            if (selectorRow) {
                selectorRow.classList.add('d-none');
            }
        } else {
            selectorRow.classList.remove("d-none");
        }

        let tabContainer = document.querySelector('.tabContainer');
        if (tabContainer) {
            let tabs = tabContainer.querySelectorAll('.tab');
            if (tabs) {
                tabs.forEach(tab => {
                    if (tab.dataset.type == type) {
                        tab.classList.add('current');
                    } else {
                        tab.classList.remove('current');
                    }
                });
            }
        }
        let select = document.getElementById('insurance-type-' + type);
        if (select) {
            // let's empty out the select
            select.innerHTML = "";
            let insurances = this.__insurancesByType[type];
            let displayFormatSettingYMD = 0;
            insurances.forEach(insurance => {
                let option = document.createElement('option');
                option.value = insurance.id === null ? "" : insurance.id;
                option.innerText = insurance.toString();
                if (Object.prototype.hasOwnProperty.call(insurance, 'end_date') && insurance.end_date !== null) {
                    option.innerText += " - " + window.top.xl("End Date") + ": "
                        + window.top.oeFormatters.I18NDateFormat(insurance.end_date, displayFormatSettingYMD);
                }
                select.appendChild(option);
            });
            if (this.selectedInsurance) {
                let selectedOptionInsuranceId = this.selectedInsurance.id === null ? "" : this.selectedInsurance.id;
                let option = select.querySelector('[value="' + selectedOptionInsuranceId + '"]');
                if (option) {
                    option.selected = true;
                }
            }
            select.classList.remove("d-none");
        }

        let saveBtn = document.querySelector('.btn-save-policy');
        // set our default selected if we have an initially selected insurance we are editing
        if (this.selectedInsurance) {
            this.#populateInsuranceInformationForSelectedInsurance(this.selectedInsurance);
            saveBtn.disabled = false;
        } else {
            console.error("Failed to render selectedInsurance as none is set");
            saveBtn.disabled = true;
        }
        // render the validation error messages
        this.#renderValidationErrorMessages(this.selectedInsurance);
        let insuranceInfoContainer = document.getElementById('insurance-info-type-' + type);
        if (!insuranceInfoContainer) {
            return; // can't do anything if the container isn't there
        }
        let buttonTabContainer = document.querySelector('.tabContainer .tab.current');
        if (buttonTabContainer) {
            if (this.__isSaving) {
                buttonTabContainer.querySelector('.btn-saving').classList.remove("d-none");
                buttonTabContainer.querySelector('.btn-save-policy').classList.add("d-none");
                // show the saving progress indicator
                // hide the save button and display the saving progress indicator
            } else {
                // hide the saving progress indicator
                // show the save button
                buttonTabContainer.querySelector('.btn-saving').classList.add("d-none");
                buttonTabContainer.querySelector('.btn-save-policy').classList.remove("d-none");
            }

            if (this.__policySaved) {
                buttonTabContainer.querySelector('.insurance-save-success').classList.remove("d-none");
            } else {
                buttonTabContainer.querySelector('.insurance-save-success').classList.add("d-none");
            }
        }

        // need to handle the case where we are re-rendering the screen and we had a previously focused input
        // we want to restore focus to that input and then tab to the next input
        if (this.__currentFocusedInputName) {
            let currentTab = tabContainer.querySelector('.tab.current');
            let input = currentTab.querySelector('[name="' + this.__currentFocusedInputName + '"]');
            this.__currentFocusedInputName = null;
            if (input) {
                let tabbableInputs = currentTab.querySelectorAll('a[href], button, input, select, textarea');
                let currentInputIndex = Array.prototype.indexOf.call(tabbableInputs, input);
                let nextTabElement = tabbableInputs[0];
                if (tabbableInputs[currentInputIndex + 1]) {
                    nextTabElement = tabbableInputs[currentInputIndex + 1];
                } else {
                    nextTabElement = tabbableInputs[0];
                }
                nextTabElement.focus();
            }
        }
    }

    #renderValidationErrorMessages(selectedInsurance) {
        let type = selectedInsurance.type;
        let insuranceInfoContainer = document.getElementById('insurance-info-type-' + type);
        if (!insuranceInfoContainer) {
            return; // can't do anything if the container isn't there
        }
        let container = insuranceInfoContainer.querySelector('.validation-errors');
        // remove the validation template if its there
        if (container) {
            container.remove();
        }
        // clear any marked errors
        let markedFields = insuranceInfoContainer.querySelectorAll('.invalid-input.error');
        markedFields.forEach(field => {
            let errorNode = field;
            if (errorNode && errorNode.classList.contains('sel2')) {
                // need to handle them differently
                errorNode = errorNode.nextElementSibling ? errorNode.nextElementSibling.querySelector('.select2-selection') : null;
            }
            if (errorNode) {
                errorNode.classList.remove('invalid-input');
                errorNode.classList.remove('error');
                errorNode.classList.remove('error-border');
            }
        });
        if (!this.__validationErrors.length) {
            return; // nothing to do
        } else {
            let templateNode = document.getElementById('insurance-validation-error-template');
            if (!templateNode) {
                throw new Error("Failed to find insurance validation error template");
            }
            let template = document.importNode(templateNode.content, true);

            insuranceInfoContainer.prepend(template);
            let container = insuranceInfoContainer.querySelector('.validation-errors');
            if (!container) {
                throw new Error("Failed to find validation error container");
            }

            let detailsList = container.querySelector('.insurance-validation-details-list');
            if (!detailsList) {
                throw new Error("Failed to find validation details list");
            }

            this.__validationErrors.forEach(error => {
                let field = error.field;
                for (let key in error.validationErrors) {
                    let message = error.validationErrors[key];
                    let fieldContainer = container.querySelector('[data-error-id="' + key + '"]');
                    if (!fieldContainer) {
                        // grab the unknown field container as a backup
                        fieldContainer = container.querySelector('[data-error-id="Unknown::UNKNOWN"]');
                        if (!fieldContainer) {
                            throw new Error("Failed to find validation container for field: " + key);
                        }
                    }
                    let clonedFieldContainer = fieldContainer.cloneNode(true);
                    clonedFieldContainer.classList.remove('d-none');
                    let validationFieldName = clonedFieldContainer.querySelector('.validation-field-name');
                    if (!validationFieldName) {
                        throw new Error("Failed to find validation field name for validation field with key " + key);
                    }
                    let validationFieldLabel = insuranceInfoContainer.querySelector('[data-validation-field="' + field + '"');
                    if (validationFieldLabel) {
                        validationFieldName.innerText = validationFieldLabel.innerText;
                    } else {
                        validationFieldName.innerText = field;
                    }
                    detailsList.appendChild(clonedFieldContainer);
                    let fieldInput = insuranceInfoContainer.querySelector('[name="form_' + field + '"]');
                    let errorNode = fieldInput;
                    if (errorNode && errorNode.classList.contains('sel2')) {
                        // need to handle them differently
                        errorNode = errorNode.nextElementSibling ? errorNode.nextElementSibling.querySelector('.select2-selection') : null;
                    }
                    if (errorNode) {
                        errorNode.classList.add('invalid-input');
                        errorNode.classList.add('error');
                        errorNode.classList.add('error-border');
                    }
                }
            });
        }
    }

    #populateSelectDropdowns() {
        this.__types.forEach(type => {
            let select = document.getElementById('insurance-type-' + type);
            if (select) {
                select.addEventListener("change", (evt) => {
                    let selectedId = evt.target.value;
                    let type = evt.target.dataset.type;
                    if (!selectedId) {
                        selectedId = null; // so we can find the blank policy
                    }
                    let updatedInsurance = this.__insurancesByType[type].find(insurance => insurance.id == selectedId);
                    this.#updateSelectedInsuranceObject(updatedInsurance);
                    this.render();
                });
            }
        });
    }

    #populateInsuranceInformationForSelectedInsurance(selectedInsurance) {
        if (selectedInsurance) {
            try {
                let type = selectedInsurance.type;
                let insuranceInfoContainer = document.getElementById('insurance-info-type-' + type);
                if (insuranceInfoContainer) {
                    this.clearEvents(); // clear any bound events that we've done here
                    insuranceInfoContainer.innerHTML = '';
                    let template = document.getElementById('insurance-edit-template');
                    if (!template) {
                        throw new Error("Failed to find insurance edit template");
                    }

                    let clone = document.importNode(template.content, true);
                    insuranceInfoContainer.appendChild(clone); // note clone nodes are now empty at this point
                    this.#populateInsuranceProviderListForNode(insuranceInfoContainer, this.__insuranceProviderList);
                    // the select2 setup needs to occur after the data elements have been populated.
                    this.#populateInsuranceModelDataElements(insuranceInfoContainer, selectedInsurance);
                    this.#setupModelSyncBinding(selectedInsurance);
                    this.#setupAddressValidation(selectedInsurance);
                    this.#setupInsuranceSwapButtons(selectedInsurance);
                }
            }
            catch (error) {
                console.error(error);
                alert(window.top.xl("An error occurred while rendering the insurance policy. Please try again or contact your system administrator."));
            }
        }
    }
    #setupInsuranceSwapButtons(selectedInsurance) {
        if (!selectedInsurance.id) {
            // we don't have an id so we can't swap the src insurance
            return;
        }
        let type = selectedInsurance.type;
        let template = document.getElementById('insurance-swap-template');
        if (!template || (type != 'secondary' && type != 'tertiary')) {
            // no template, nothing to do as the feature is turned off or we are not primary or secondary insurance
            return;
        }
        let insuranceInfoContainer = document.getElementById('insurance-info-type-' + type);
        if (!insuranceInfoContainer) {
            throw new Error("Failed to find insurance info container for type: " + type);
        }
        let fieldDestination = insuranceInfoContainer.querySelector('.js-swap-insurance');
        if (!fieldDestination) {
            throw new Error("Failed to find swap insurance field for type: " + type);
        }
        let clone = document.importNode(template.content, true);
        fieldDestination.parentNode.insertBefore(clone, fieldDestination.nextSibling);
        let swapButton = insuranceInfoContainer.querySelector('.btn-insurance-swap[data-type="'+ type + '"]');
        if (!swapButton) {
            throw new Error("Failed to find swap button for type: " + type);
        }
        swapButton.classList.remove("d-none");
        swapButton.addEventListener('click', (evt) => {
            let targetType = evt.target.dataset['targetType'];
            if (!targetType) {
                throw new Error("Failed to find target type for swap button");
            }
            evt.target.disabled = true;
            evt.target.querySelector('.wait').classList.remove('d-none');
            this.__isSaving = true;
            this.__policySaved = false;
            this.__insurancePolicyService.swapInsurance(this.selectedInsurance, targetType)
                .then(selectedInsurance => {
                    evt.target.disabled = false;
                    evt.target.querySelector('.wait').classList.add('d-none');
                    // we can use our setup function to restart everything with the swapped policies
                    this.setupNewPolicyEdit(selectedInsurance);
                    // these fields get reset by the setupNewPolicyEdit function... so we need to undo it
                    this.__isSaving = false;
                    this.__policySaved = true;
                    this.render();
                })
                .catch(error => {
                    this.__isSaving = false;
                    evt.target.disabled = false;
                    evt.target.querySelector('.wait').classList.add('d-none');
                    console.error(error);
                    // if we couldn't swap due to validation errors... we need to display those errors
                    if (error instanceof ValidationError) {
                        this.__validationErrors = error.validationErrors;
                        this.render(); // re-render the screen to update the display with the errors.
                    } else {
                        alert(window.top.xl("An error occurred while swapping the insurance policy. Please try again or contact your system administrator."));
                    }
                });
        });
    }

    #isIgnoredModelProperty(property) {
        return ['id', 'insureruuid', 'pid', 'puuid', 'type', 'uuid'].indexOf(property) < 0;
    }

    #populateInsuranceModelDataElements(insuranceInfoContainer, selectedInsurance) {

        let keys = Object.keys(selectedInsurance);
        keys.forEach(key => {
            let value = selectedInsurance[key];
            let input = insuranceInfoContainer.querySelector('[name="form_' + key + '"]');
            if (!input) {
                if (this.#isIgnoredModelProperty(key)) {
                    console.error("Failed to find insurance info input for key: " + key);
                }
                return;
            }
            // clear out the id field as we don't use it
            input.removeAttribute('id');
            if (input.classList.contains('datepicker')) {
                if (value) {
                    input.value = window.top.oeFormatters.I18NDateFormat(value);
                } else {
                    input.value = "";
                }
                // $(input).val(value); // use jquery to set the value so we trigger plugin formatting
                // input.value = window.top.oeFormatters.DateFormatRead('jquery-datetimepicker');
            }
            else if (input.nodeName != "SELECT") {
                input.value = value;
            } else {
                let option = input.querySelector('[value="' + value + '"]');
                if (option) {
                    option.selected = true;
                } else {
                    console.error("Failed to find select option value for key: " + key + " value: " + value);
                }
            }

            // turn off subscriber self relationships data entry if we are waiting for patient data to load
            if (this.__updatingSubscriberSelfRelationship && selectedInsurance.isPatientDataField(key)) {
                if (input.nodeName != "SELECT") {
                    input.value = window.top.xl("Loading...");
                }
                input.disabled = true;
            } else {
                input.disabled = false;
            }
        });

        let insuranceInfoType = insuranceInfoContainer.querySelector('.insurance-info-type');
        if (insuranceInfoType) {
            // note use of innerText here to prevent XSS, DO NOT USE innerHTML
            insuranceInfoType.innerText = type;
        }
    }

    // This capitalizes the first letter of each word in the passed input
    // element.  It also strips out extraneous spaces.
    capitalizeMe(event) {
        let elem = event.target;
        if (!elem) {
            console.error("Failed to find element for capitalizeMe");
            return;
        }
        var a = elem.value.split(' ');
        var s = '';
        for(var i = 0; i < a.length; ++i) {
            if (a[i].length > 0) {
                if (s.length > 0) s += ' ';
                s += a[i].charAt(0).toUpperCase() + a[i].substring(1);
            }
        }
        elem.value = s;
    }
}

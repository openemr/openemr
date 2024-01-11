/**
 * This class is responsible for rendering the patient edit insurance policy screen and handling the events for it.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
import {InsurancePolicyService} from "./InsurancePolicyService.js";

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

    #boundEvents = [];

    /**
     *
     * @param insurancePolicyService InsurancePolicyService
     */
    constructor(insurancePolicyService) {
        this.__insuranceProviderList = insurancePolicyService.getInsuranceProvidersList();
        this.__types = insurancePolicyService.getInsuranceCategories();
        this.__insurancesByType = insurancePolicyService.getInsurancesByType();
        this.__insurancePolicyService = insurancePolicyService;
    }

    addEvent(node, event, callback) {
        node.addEventListener(event, callback);
        this.#boundEvents.push({node: node, event: event, callback: callback});
    }

    clearEvents() {
        this.#boundEvents.forEach(event => {
            event.node.removeEventListener(event.event, event.callback);
        });
        this.#boundEvents = [];
    }

    /**
     *
     * @param InsurancePolicyModel newPolicyData
     */
    setupNewPolicyEdit(newPolicyData) {
        this.selectedInsurance = newPolicyData;
        this.__selectedInsuranceTypeTab = newPolicyData.type;
        this.__insurancesByType = this.__insurancePolicyService.getInsurancesByType();
        this.setup();
    }

    setup() {
        this.show();
        this.#setupInsuranceTypeNavigation();
        this.#setupSavePolicyButton();
        this.#populateSelectDropdowns();
        // this is where we would populate the most current insurance
        this.setupInitialInsurance();
        this.render();
    }
    #setupSavePolicyButton() {
        // setup event listener for btn-save-policy to save the policy
        let btnSavePolicy = document.querySelectorAll('.btn-save-policy');
        if (btnSavePolicy) {
            btnSavePolicy.forEach(b => b.addEventListener('click', (evt) => {
                evt.preventDefault();
                evt.stopPropagation();
                this.savePolicy();
            }));
        }
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
                        this.selectedInsurance[key] = value;
                        this.render(); // re-render the screen to update the display
                    });
                } else {
                    // capture the events for the select2 events and update the selectedInsurance
                    $(input).on('select2:select', (evt) => {
                    // this.addEvent(input, 'select2:select', (evt) => {
                        let input = evt.target;
                        let key = input.name.replace('form_', '');
                        let value = input.value;
                        this.selectedInsurance[key] = value;
                        this.render(); // re-render the screen to update the display
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
        $(insuranceInfoContainer).find('.datepicker').datetimepicker('destroy');
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
                console.log("onSelectDate: " + key + " value: " + value);
                console.log("ct is ", selectedDateTime);
                // datetime selected
                this.selectedInsurance[key] = selectedDateTime;
                // setTimeout(() => {
                //     // make this happen after we've finished our event handlers
                //     this.render(); // re-render the screen to update the display
                // }, 0);
            }
        });
        // setup the bootstrap select fields
        select2Translated("#" + insuranceInfoContainer.id + " .sel2", undefined, {
            theme: "bootstrap4"
            ,dropdownAutoWidth: true
            ,width: "resolve"
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
        // TODO: @adunsulag need to sync the form data with the selected insurance
        // this.syncFormDataWithInsurance(this.selectedInsurance);
        // TODO: @adunsulag need to validate the form
        // need to grab the selected insurance and save it
        this.__insurancePolicyService.saveInsurance(this.selectedInsurance)
            .then(result => {
                this.selectedInsurance = result;
                this.__insurancesByType = insurancePolicyService.getInsurancesByType();
            })
            .catch(error => {
                // TODO: @adunsulag what to do here if we fail?
                console.error(error);
            });
    }

    setupInitialInsurance() {
        if (this.selectedInsurance === null) {
            let defaultType = this.__types[0];
            this.__selectedInsuranceTypeTab = defaultType;
            let insurances = this.__insurancesByType[defaultType];
            this.selectedInsurance = insurances[0] || null;
        }

    }
    hide() {
        this.toggleDisplay(false);
    }
    show() {
        this.toggleDisplay(true);
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
                    this.selectedInsurance = this.__insurancesByType[this.__selectedInsuranceTypeTab][0];
                    this.render();
                });
            });
        }
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
                if (insurance.hasOwnProperty('end_date') && insurance.end_date !== null) {
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
                    this.selectedInsurance = this.__insurancesByType[type].find(insurance => insurance.id == selectedId);
                    this.render();
                });
            }
        });
    }

    #populateInsuranceInformationForSelectedInsurance(selectedInsurance) {
        console.log(selectedInsurance);
        if (selectedInsurance) {
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
            }
        }
    }

    #populateInsuranceModelDataElements(insuranceInfoContainer, selectedInsurance) {

        let keys = Object.keys(selectedInsurance);
        keys.forEach(key => {
            let value = selectedInsurance[key];
            let input = insuranceInfoContainer.querySelector('[name="form_' + key + '"]');
            if (!input) {
                console.info("Failed to find insurance info input for key: " + key);
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

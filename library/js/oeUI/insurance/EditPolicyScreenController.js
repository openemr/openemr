import {InsurancePolicyService} from "./InsurancePolicyService.js";

export class EditPolicyScreenController
{
    __types = null;

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
        this.setupInsuranceTypeNavigation();
        this.populateSelectDropdowns();
        // this is where we would populate the most current insurance
        this.setupInitialInsurance();
        this.render();
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


    populateInsuranceProviderListForNode(node, insuranceProviderList) {
        let select = node.querySelector('[name="provider"]');
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


    setupInsuranceTypeNavigation() {
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

                    // clear out any selected insurance so we go with whatever the default is.
                    this.selectedInsurance = null;
                    this.__selectedInsuranceTypeTab = evt.target.dataset.type;
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
        let insurances = this.__insurancesByType[type];
        if (insurances && insurances.length > 0) {
            this.populateInsuranceInformationForSelectedInsurance(insurances[0]);
        } else {
            // TODO: @adunsulag need to figure out what happens if we have no insurance for the patient...
        }

        let select = document.getElementById('insurance-type-' + type);
        if (select) {
            // let's empty out the select
            select.innerHTML = "";
            let insurances = this.__insurancesByType[type];
            insurances.forEach(insurance => {
                let option = document.createElement('option');
                option.value = insurance.id === null ? "" : insurance.id;
                option.innerText = insurance.toString();
                if (insurance.hasOwnProperty('end_date') && insurance.end_date !== null) {
                    option.innerText += " - " + window.top.xl("End Date") + ": " + insurance.end_date;
                }
                select.appendChild(option);
            });
            // set our default selected if we have an initially selected insurance we are editing
            if (this.selectedInsurance) {
                let selectedOptionInsuranceId = this.selectedInsurance.id === null ? "" : this.selectedInsurance.id;
                let option = select.querySelector('[value="' + selectedOptionInsuranceId + '"]');
                if (option) {
                    option.selected = true;
                }
            }
            select.classList.remove("d-none");
        }
    }

    populateSelectDropdowns() {
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

    populateInsuranceInformationForSelectedInsurance(selectedInsurance) {
        console.log(selectedInsurance);
        if (selectedInsurance) {
            let type = selectedInsurance.type;
            let insuranceInfoContainer = document.getElementById('insurance-info-type-' + type);
            if (insuranceInfoContainer) {
                insuranceInfoContainer.innerHTML = '';
                let template = document.getElementById('insurance-edit-template');
                if (template) {
                    let clone = document.importNode(template.content, true);
                    insuranceInfoContainer.appendChild(clone); // note clone nodes are now empty at this point
                    this.populateInsuranceProviderListForNode(insuranceInfoContainer, this.__insuranceProviderList);

                    let keys = Object.keys(selectedInsurance);
                    keys.forEach(key => {
                        let value = selectedInsurance[key];
                        let updatedValue = this.convertValueForInsuranceKey(key, value);
                        let input = insuranceInfoContainer.querySelector('[name="form_' + key + '"]');
                        if (!input) {
                            console.info("Failed to find insurance info input for key: " + key);
                            return;
                        }
                        // clear out the id field as we don't use it
                        input.removeAttribute('id');
                        if (input.nodeName != "SELECT") {
                            input.value = updatedValue;
                        } else {
                            let option = input.querySelector('[value="' + updatedValue + '"]');
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

                    // setup the event listeners for the capitalize me fields
                    insuranceInfoContainer.querySelectorAll('.js-capitalize-me').forEach(input => {
                        input.addEventListener('change', this.capitalizeMe);
                    });

                    // setup the event listeners for the policykeyup fields
                    if (typeof window.policykeyup == 'function') {
                        // comes from common.js
                        insuranceInfoContainer.querySelectorAll('.js-policykeyup').forEach(input => {
                            // need to bind up the input to the function argument due to the way the function is written
                            input.addEventListener('keyup', function() {
                                window.policykeyup(this); // this is the input element
                            });
                        });
                    }

                    // setup the event listeners for the date picker fields

                    // setup the bootstrap select fields
                    select2Translated("#" + insuranceInfoContainer.id + " .sel2");
                }
            }
        }
    }

    convertValueForInsuranceKey(key, value) {
        if (key == 'accept_assignment') {
            if (value == 'FALSE') {
                value = 'NO';
            } else {
                // default should go to yes
                value = 'YES';
            }
        }
        return value;
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

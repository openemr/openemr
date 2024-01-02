import {InsurancePolicyService} from "./InsurancePolicyService.js";

export class NewPolicyScreenController
{
    __types = null;
    __insurancesByType = {};
    __screenName = "new-policy-screen";

    __isCopyPolicy = false;
    __copyPolicyId = null;
    __setEffectiveEndDate = false;

    __selectedInsuranceType = null;
    __effectiveEndDate = (new Date()).toISOString().substring(0, 10); // grab the first 10 digits

    /**
     *
     * @param insurancePolicyService InsurancePolicyService
     */
    constructor(insurancePolicyService) {
        this.__types = insurancePolicyService.getInsuranceCategories();
        this.__insurancesByType = insurancePolicyService.getInsurancesByType();
        this.__isCopyPolicy = false;
        this.__setEffectiveEndDate = false;
        this.__copyPolicyId = null;
    }

    setup() {
        let newPolicyScreen = document.getElementById("new-policy-screen");
        newPolicyScreen.classList.remove("d-none");

        // grab the newInsuranceType radio value
        document.querySelectorAll('input[name="newInsuranceType"],input[name="createOption"]').forEach(radio => {
            radio.addEventListener('click', (event) => this.refreshNewPolicyScreen(event));
        });

        document.getElementById("setEndDateCheckbox").addEventListener('click', (event) => {
            this.toggleEffectiveEndDate(event);
            this.refreshNewPolicyScreen(event);
        });
        this.refreshNewPolicyScreen({});
    }

    toggleSetEffectiveEndDateRow(display) {
        let setEndDateRow = document.querySelector(".new-policy-set-end-date-row");
        if (display) {
            setEndDateRow.classList.remove("d-none");
        } else {
            setEndDateRow.classList.add("d-none");
        }
    }

    shouldDisplayEffectiveEndDate() {
        let insurances = this.__insurancesByType[this.__selectedInsuranceType] || [];
        if (insurances.length) {
            // check if there is any without an end date
            let currentInsurances = insurances.find(i => i.isCurrentPolicy());
            if (currentInsurances) {
                return true;
            }
        }
        return false;
    }

    displayErrorMessage(errorCode) {
        let errorRow = document.querySelector(".new-policy-error-row");
        errorRow.classList.remove("d-none");
        let errorMessage = document.querySelector(".new-policy-error-message-" + errorCode);
        if (errorMessage) {
            errorMessage.classList.remove("d-none");
        } else {
            console.error("Error message does not exist with class name: new-policy-error-message-" + errorCode);
        }
    }

    updateEffectiveEndDate() {
        // grab the effective end date from the form and update the effective end date
        // for the insurance policy
        let endDateInput = document.querySelector(".new-policy-effective-end-date");
        this.__effectiveEndDate = endDateInput.value;
    }

    getDataForNewPolicy() {
        let data = {
            type: this.__selectedInsuranceType
            ,endDate: this.__effectiveEndDate
            ,setEndDate: this.__setEffectiveEndDate
            ,copyPolicyId: this.__copyPolicyId
            ,isCopyPolicy: this.__isCopyPolicy
        };
        return data;
    }

    toggleEffectiveEndDate(event) {
        this.__setEffectiveEndDate = event.target.checked;
    }

    refreshNewPolicyScreen(event) {
        let newInsuranceType = document.querySelector('input[name="newInsuranceType"]:checked').value;

        // grab the radio selected value for the createOption and if it is 'blank' then
        let createOption = document.querySelector('input[name="createOption"]:checked').value;

        let copyPolicyRow = document.querySelector(".new-policy-copy-row");
        this.__isCopyPolicy = createOption === 'copy';
        if (!this.__isCopyPolicy) {
            this.__isCopyPolicy = false;
            this.__copyPolicyId = null;
            copyPolicyRow.classList.add("d-none");
            // display the blank-policy form
            // using the newInsuranceType.value grab the select with name
        } else {
            // show the copy policy row
            copyPolicyRow.classList.remove("d-none");
            this.createCopyPolicyDropdown(newInsuranceType);

            let copyNode = document.querySelector(".new-policy-copy-list");
            if (copyNode && copyNode.value) {
                this.__copyPolicyId = copyNode.value;
            } else {
                this.__copyPolicyId = null;
            }
        }
        this.__selectedInsuranceType = newInsuranceType;

        this.toggleSetEffectiveEndDateRow(this.shouldDisplayEffectiveEndDate());


        let effectiveEndDateRow = document.querySelector(".new-policy-effective-end-date-row");
        if (this.__setEffectiveEndDate) {
            effectiveEndDateRow.classList.remove("d-none");
            let effectiveEndDateInput = document.querySelector(".new-policy-effective-end-date");
            effectiveEndDateInput.value = this.__effectiveEndDate;
        } else {
            effectiveEndDateRow.classList.add("d-none");
        }
        console.log(this.getDataForNewPolicy());
        if (this.isValid()) {
            this.toggleNextButton(true);
        } else {
            this.toggleNextButton(false);
        }
    }

    isValid() {
        let valid = true;
        let errorRow = document.querySelector(".new-policy-error-row");
        errorRow.classList.add("d-none");
        // reset the messages
        document.querySelectorAll('.new-policy-error-message').forEach(error => {
            error.classList.add("d-none");
        });

        if (this.__copyPolicyId == null && this.__isCopyPolicy) {
            this.displayErrorMessage("copy-empty-policy");
            valid = false;
        }
        if (this.__setEffectiveEndDate && this.__effectiveEndDate === "") {
            this.displayErrorMessage("effective-end-date-empty");
            valid = false;
        }
        return valid;
    }

    toggleNextButton(enable) {
        let nextButton = document.querySelector(".btn-new-policy-next");
        if (enable) {
            nextButton.removeAttribute("disabled");
            nextButton.classList.remove("btn-disabled");
        } else {
            nextButton.setAttribute("disabled", "disabled");
            nextButton.classList.add("btn-disabled");
        }
    }

    createCopyPolicyDropdown(newInsuranceType) {
        let insurances = this.__insurancesByType[newInsuranceType].filter(insurance => insurance.id !== null);
        let emptyPolicyList = document.querySelector(".new-policy-list-empty");
        let select = document.querySelector(".new-policy-copy-list");

        select.innerHTML = ""; // remove all the children
        if (insurances.length === 0) {
            emptyPolicyList.classList.remove("d-none");
            select.classList.add("d-none");
            this.__copyPolicyId = null;
        } else {
            emptyPolicyList.classList.add("d-none");
            select.classList.remove("d-none");
            insurances.forEach(insurance => {
                let option = document.createElement('option');
                option.value = insurance.id;
                option.innerText = insurance.plan_name + " - " + window.top.xl("Effective Date") + ": " + insurance.date;
                if (insurance.hasOwnProperty('end_date') && insurance.end_date !== null) {
                    option.innerText += " - " + window.top.xl("End Date") + ": " + insurance.end_date;
                }
                if (insurance.id === this.__copyPolicyId) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
            this.__copyPolicyId = insurances[0].id;
        }
    }

    destroy() {
        // cleanup all events and remove the dialog from the screen.  Reload the insurance
        // edit screen.
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
}

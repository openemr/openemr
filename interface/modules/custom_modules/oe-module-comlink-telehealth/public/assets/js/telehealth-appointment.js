/**
 * Appointment TeleHealth javascript library for interacting with the appointment dialog window.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
(function(window, comlink) {

    /**
     * @type {string} The path of where the module is installed at.  In a multisite we pull this from the server configuration, otherwise we default here
     */
    let moduleLocation = comlink.settings.modulePath || '/interface/modules/custom_modules/oe-module-comlink-telehealth/';
    let csrfToken = comlink.settings.apiCSRFToken || "";

    let defaultTranslations = {
    };

    function getProviderSelectNode() {
        let node = window.document.querySelector("#provd");
        if (!node) {
            console.error("Failed to find node with selector #provd");
            return;
        }
        return node;
    }

    function hideInvalidTelehealthProviders(telehealthProviders) {
        telehealthProviders = telehealthProviders || [];
        let ids = telehealthProviders.map(p => +p).filter(p => !isNaN(p));
        ids.sort();

       let providerSelector = getProviderSelectNode();
        if (!providerSelector) {
            console.error("Failed to find provider select node");
            return;
        }

        let options = providerSelector.options;
        let selectedValues = [];
        for (var index = 0; index < options.length; index++) {
            let value = +(options[index].value || 0);
            console.log("options are ", options[index]);
            // we could do a binary search here if we need to for the amount of data... should be fairly small though for 3-4000 providers.
            if (ids.indexOf(value) === -1) {
                if (options[index].selected) {
                    selectedValues.push(options[index]);
                }
                options[index].selected = false;
                options[index].classList.add("d-none");
            } else {
                console.log("options are ", options[index]);
                options[index].classList.remove("d-none");
            }
        }
        if (selectedValues.length > 0) {
            providerSelector.selectedIndex = -1; // if nothing is visible is selected we should remove all selected items
        }
    }

    function displayAllProviders() {
        let providerSelector = getProviderSelectNode();
        if (!providerSelector) {
            console.error("Failed to find provider select node");
            return;
        }

        let options = providerSelector.options;
        for (var index = 0; index < options.length; index++) {
            options[index].classList.remove("d-none");
        }
    }

    function isCategoryTelehealth(category, telehealthCategories) {
        let value = +(category || 0);
        if (value > 0 && telehealthCategories.indexOf(value) !== -1) {
            return true;
        }
        else {
            return false;
        }
    }

    function getValidationDiv(form) {
        let validationDiv = form.querySelector(".patient-validation-div");
        return validationDiv;
    }

    function updateAppointmentScreenForCategory(category, telehealthProviders, telehealthCategories) {

        let node = window.document.querySelector("#form_category");
        if (!node) {
            console.error("Failed to find node with selector #form_category");
            return;
        }
        let form = window.document.querySelector("#theform");

        // setup the change order
        // if the current category has an id of one of the telehealth categories
        // grab the select options and set their display option to be hidden if the provider is not in the
        // telehealth provider lists
        // Note this will probably max out at a few thousand providers... so if an OpenEMR install is larger than that
        // this will need to be adjusted.
        if (isCategoryTelehealth(node.value, telehealthCategories)) {
            // now let's hide our providers
            hideInvalidTelehealthProviders(telehealthProviders);
            // if we have a patient, let's go ahead and validate.
            let formPid = form.form_pid.value || 0;
            if (formPid) {
                validatePatientForTelehealthForm(form, formPid, telehealthCategories);
            }
        } else {
            displayAllProviders();
            let validationDiv = getValidationDiv(form);
            validationDiv.classList.add('d-none');

        }
    }
    function validatePatientForTelehealthForm(form, patientId, telehealthCategories) {

        // now we need to check if the patient is setup for telehealth
        let url = moduleLocation + 'public/index.php?action=patient_validate_telehealth_ready&validatePid=' + encodeURIComponent(patientId);
        // first show a message saying validating patient for telehealth appointment
        let validationDiv = form.querySelector(".patient-validation-div");
        validationDiv.innerText = translations.PATIENT_SETUP_FOR_TELEHEALTH_VALIDATING || "Checking if patient is setup for telehealth appointment...";
        validationDiv.classList.remove('d-none');
        validationDiv.classList.remove("alert-info");
        validationDiv.classList.remove("alert-success");
        validationDiv.classList.remove("alert-danger");
        validationDiv.classList.add("alert-info");
        let headers = {
            'apicsrftoken': csrfToken
        };
        window.fetch(url, {
            method: 'GET'
            ,redirect: 'manual'
            ,headers: headers
        }).then(result => {
            if (result.status == 400 || result.status == 401 || (result.ok && result.status == 200)) {
                return result.json();
            } else {
                throw new Error("Failed to validate patient " + this.pid
                    + " for telehealth data");
            }
        })
            .then(data => {
                validationDiv.classList.remove("alert-info");
                if (data && data.success === true) {
                    validationDiv.classList.add("alert-success");
                    validationDiv.innerText = translations.PATIENT_SETUP_FOR_TELEHEALTH_SUCCESS || "Patient has portal credentials and is setup for telehealth sessions";
                } else {
                    validationDiv.classList.add("alert-danger");
                    // we need to display an error message to the user
                    validationDiv.innerText = translations.PATIENT_SETUP_FOR_TELEHEALTH_FAILED || "Failed to validate patient for telehealth appointment";
                }
            })
            .catch(error => {
                console.error(error);
                validationDiv.classList.remove("alert-info");
                validationDiv.classList.add("alert-danger");
                // we need to display an error message to the user
                validationDiv.innerText = translations.PATIENT_SETUP_FOR_TELEHEALTH_FAILED || "Failed to validate patient for telehealth appointment";
            });
    }
    function validatePatientForTelehealth(evt, telehealthCategories) {
        if (!evt.detail)
        {
            console.error("validatePatientForTelehealth() - Failed to find detail object on event");
            return;
        }
        let node = window.document.querySelector("#form_category");
        if (!node) {
            console.error("Failed to find node with selector #form_category");
            return;
        }
        if (!isCategoryTelehealth(node.value, telehealthCategories)) {
            console.log("validatePatientForTelehealth() - category is not a telehealth category, skipping validation");
            return;
        }

        let patientId = evt.detail.pid;
        let form = evt.detail.form;
        validatePatientForTelehealthForm(form, patientId, telehealthCategories);
    }

    function initAppointmentWithTelehealth(telehealthProviders, telehealthCategories, jsEventNames) {
        let node = window.document.querySelector("#form_category");
        if (!node) {
            console.error("Failed to find node with selector #form_category");
            return;
        }

        node.addEventListener('change', function(evt) {
            let select = evt.currentTarget;
            updateAppointmentScreenForCategory(select.value, telehealthProviders, telehealthCategories);
        });

        // go with the initial value
        updateAppointmentScreenForCategory(node.value, telehealthProviders, telehealthCategories);

        // now we need to handle when the patient is selected to do some backend validation and display messages
        // if the patient is not setup properly for a telehealth session
        let appointmentForm = document.getElementById('theform');
        if (appointmentForm) {
            appointmentForm.addEventListener(jsEventNames.appointmentSetEvent, function(evt) {
                validatePatientForTelehealth(evt, telehealthCategories);
            });
        }
    }

    comlink.initAppointmentWithTelehealth = initAppointmentWithTelehealth;

    let translations = comlink.translations || defaultTranslations;
    window.comlink = comlink;
})(window, window.comlink || {});

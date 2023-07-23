/**
 * trusted-messages.js handles all of the frontend validation and backend communications for sending messages using the
 * Direct protocol
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
(function() {
    function hideAlerts() {
        let alerts = document.querySelectorAll(('.alert'));
        let length = alerts.length;
        alerts.forEach(function(item) {
            item.classList.add('d-none');
        });
    }
    function showAlert(alertId) {
        hideAlerts();
        let alert = document.getElementById(alertId);
        if (!alert) {
            alert = document.getElementById('error-serverError');
            if (!alert) {
                console.error("HTML dom element for error-serverError is missing from system");
                return;
            }
        }

        if (alert) {
            alert.classList.remove("d-none");
        } else {

            console.error("Failed to find dom node alert with id " + alertId);
        }
    }

    function processTrustedMessageResponse(data) {
        hideAlerts();
        console.log(data);
        if (data.errorCode) {
            showAlert('error-' + data.errorCode);
            if (data.errorCode && data.errorMessage) {
                let errorMessage = document.getElementById('directErrorMessage');
                if (errorMessage) {
                    // we use innerText as we want don't want any HTML or script being executed
                    errorMessage.innerText = data.errorMessage;
                }
            }
            // we don't want to do anything else here
            return;
        }
        showAlert('success');
        // save off the csrf, reset the form and then reset it
        let form = window.document.forms[0];
        let csrfNode = document.querySelector("form input[name='csrf']");
        let csrfValue = "";
        if (csrfNode) {
            csrfValue = csrfNode.value;
        }
        form.reset();
        if (csrfNode) {
            csrfNode.value = csrfValue;
        }
    }
    function validateForm(formData) {
        if (!(formData.has('message') && formData.has('documentId')))
        {
            console.error("Failed to find message and documentId inputs in form");
            showAlert('error-serverError');
            return false;
        }

        let message = formData.get("message") || "";
        let documentId = +(formData.get("documentId") || 0);

        if (message.trim() == "" && (isNaN(documentId) || documentId < 1)) {
            showAlert("error-validation-failed");
            return false;
        }

        if (!formData.has('trusted_email'))
        {
            console.error("Failed to find trusted_email inputs in form");
            showAlert('error-serverError');
            return false;
        }
        let trusted_email = formData.get("trusted_email") || "";
        if (trusted_email.trim() == "") {
            showAlert("error-validation-failed");
            return false;
        }

        if (!formData.has('pid'))
        {
            console.error("Failed to find pid inputs in form");
            showAlert('error-serverError');
            return false;
        }

        let pid = +(formData.get("pid") || 0);

        if (isNaN(pid) || pid < 1) {
            showAlert("error-validation-failed");
            return false;
        }

        return true;
    }
    function sendTrustedMessage(event) {
        event.preventDefault();
        // reset any alerts back to what they were
        hideAlerts();
        const formData = new FormData(event.target);
        if (!validateForm(formData)) {
            return;
        }
        toggleSubmitSpinner(true);
        window.top.restoreSession();

        window.fetch("trusted-messages-ajax.php", {method: 'POST', body: formData})
            .then(result => {
                if (result.ok) {
                    return result.json();
                } else {
                    return Promise.reject();
                }
            })
            .then(json => {
                toggleSubmitSpinner(false);
                processTrustedMessageResponse(json);
            })
            .catch(error => {
                console.error(error);
                toggleSubmitSpinner(false);
                processTrustedMessageResponse({errorCode: "networkError"});
            });
        return false;
    }

    function toggleSubmitSpinner(display) {

        let spinner = document.getElementById('message-spinner');
        let submitButton = document.getElementById('message-submit');

        if (spinner) {
            if (display) {
                spinner.classList.remove('d-none');
            } else {
                spinner.classList.add('d-none');
            }
        }

        if (submitButton) {
            if (display) {
                submitButton.disabled = true;
            } else {
                submitButton.disabled = false;
            }
        }

    }

    // This is for callback by the find-patient popup.
    function setpatient(pid, lname, fname, dob) {
        var patientNameNode = document.getElementById("patientName");
        var pidNode = document.getElementById("patientPid");
        if (pidNode) {
            pidNode.value = pid;
        } else {
            console.error("Could not find pid node");
        }
        if (patientNameNode) {
            patientNameNode.value = fname + " " + lname;
        } else {
            console.error("Could not find patientName node");
        }
    }


    function selectPatient() {
        window.top.restoreSession();
        dlgopen('../../main/calendar/find_patient_popup.php', '_blank', 625, 400);
    }

    function selectDocument() {
        let pidInput = document.getElementById('patientPid');
        let csrfNode = document.querySelector("form input[name='csrf_token_form']");
        let csrfValue = "";
        if (csrfNode) {
            csrfValue = csrfNode.value;
        } else {
            console.error("Failed to find csrf node");
            return;
        }

        let url = "../../main/finder/document_select.php?csrf_token_form="
            + encodeURIComponent(csrfValue);
        if (pidInput) {
            url += "&pid=" + encodeURIComponent(pidInput.value);
        }
        window.top.restoreSession();
        window.dlgopen(url, '_blank', 700, 400);
    }

    function setSelectedDocument(did, docName, categoryName, date) {
        let documentIdInput = document.getElementById('documentId');
        let documentNameInput = document.getElementById('documentName');
        if (documentIdInput) {
            documentIdInput.value = did;
        }
        if (documentNameInput)
        {
            // TODO: do we want to put category here somewhere?
            documentNameInput.value = docName;
        }
    }

    function initDocument() {
        let form = document.getElementById('trustedForm');
        if (form) {
            form.addEventListener('submit', sendTrustedMessage);
        } else {
            console.error("Could not find form to submit");
        }
        var patientNameNode = document.getElementById("patientName");
        if (patientNameNode)
        {
            patientNameNode.addEventListener('click', selectPatient);
        }
        var documentNameNode = document.getElementById("documentName");
        if (documentNameNode) {
            documentNameNode.addEventListener('click', selectDocument);
        }
    }

    window.addEventListener('DOMContentLoaded', initDocument);

    // we add these to the global scope as the patient dialog popups need to be able to find them.
    window.setpatient = setpatient;
    window.setSelectedDocument = setSelectedDocument;
})(window);
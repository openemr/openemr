/**
 * Javascript Controller for the Add Patient Dialog window in the telehealth conference room.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2023 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

export class AddPatientDialog
{
    /**
     *
     * @type {bootstrap.Modal}
     */
    modal = null;

    /**
     *
     * @type HTMLElement
     */
    container = null;

    /**
     *
     * @type {number}
     */
    pc_eid = null;

    /**
     *
     * @type {string}
     */
    scriptLocation = null;

    /**
     * Address of the FHIR api endpoint
     * @type string
     */
    fhirLocation = null;

    /**
     *
     * @type {function}
     */
    closeCallback = null;

    /**
     * Key value dictionary of the language translations
     * @type {array}
     * @private
     */
    __translations = null;

    /**
     * Used for making unique modal ids
     * @type {number}
     * @private
     */
    __idx = 0;

    /**
     * Cross Site Request Forgery token used to communicate with the internal API
     * @type {null}
     * @private
     */
    __apiCSRFToken = null;

    /**
     * The current screen we are displaying inside the dialog
     * @type {string}
     * @private
     */
    __currentScreen = null;

    /**
     * The caller settings received from the server that should be used for the telehealth session call
     * @type {object}
     * @private
     */
    __updatedCallerSettings = null;

    /**
     * The list of participants that are in the call
     * @type {object}
     * @private
     */
    __participantList = null;

    /**
     * Boolean that the DOM interface needs to be updated with the participant list changes.
     * @type {boolean}
     * @private
     */
    __updateParticipants = false;

    constructor(apiCSRFToken, translations, pc_eid, scriptLocation, fhirLocation, participantList, closeCallback) {
        this.pc_eid = pc_eid;
        this.scriptLocation = scriptLocation;
        this.fhirLocation = fhirLocation;
        this.closeCallback = closeCallback;
        this.__translations = translations;
        this.__apiCSRFToken = apiCSRFToken;

        this.__participantList = (participantList || []).filter(pl => pl.role !== 'provider');
        this.__updateParticipants = false;
    }

    cancelDialog() {
        // just have us call the close dialog piece.
        this.closeDialogAndSendCallerSettings();

    }

    sendSaveParticipant(saveData) {
        let postData = Object.assign({eid: this.pc_eid}, saveData);
        let scriptLocation = this.scriptLocation + "?action=save_session_participant";

        window.top.restoreSession();
        return window.fetch(scriptLocation,
            {
                method: 'POST'
                ,headers: {
                    'Content-Type': 'application/json'
                }
                ,body: JSON.stringify(postData)
                ,redirect: 'manual'
            })
            .then(result => {
                if (result.status == 400 || (result.ok && result.status == 200)) {
                    return result.json();
                } else {
                    throw new Error("Failed to save participant in " + this.pc_eid + " with save data");
                }
            })
            .then(jsonResult => {
                if (jsonResult.error) {
                    this.handleSaveParticipantErrorResponse(jsonResult);
                    return;
                }

                let callerSettings = jsonResult.callerSettings || {};
                this.showActionAlert('success', this.__translations.PATIENT_INVITATION_SUCCESS);
                // let's show we were successful and close things up.
                this.__updatedCallerSettings = callerSettings;
                let participants = (callerSettings.participantList || []).filter(pl => pl.role != 'provider');
                this.__participantList = participants;
                this.__updateParticipants = true;
                this.updateParticipantControls();
                this.showPrimaryScreen();
                setTimeout(() => {
                    this.closeDialogAndSendCallerSettings();
                }, 1000);
            })
    }

    closeDialogAndSendCallerSettings() {
        jQuery(this.container).on("hidden.bs.modal", () => {
            try {
                jQuery(this.container).off("hidden.bs.modal");
                this.closeCallback(this.__updatedCallerSettings);
            }
            catch (error)
            {
                console.error(error);
            }
            try {
                // make sure we destruct even if the callback fails
                this.destruct();
            }
            catch (error) {
                this.destruct();
            }
        });
        this.modal.hide();
    }

    sendSearchResults(inputValues) {
        // example FHIR request
        // let url = '/apis/default/fhir/Patient/_search?given:contains=<fname>&family:contains=<lname>&birthDate=<dob>'
        let url = this.fhirLocation + '/Patient';
        let searchParams = [];

        if (inputValues.pid) {
            searchParams.push("identifier=" + encodeURIComponent(inputValues.pid));
        }
        if (inputValues.fname || inputValues.lname) {
            let values = [];
            if (inputValues.fname) {
                values.push(encodeURIComponent(inputValues.fname));
            }
            if (inputValues.lname) {
                values.push(encodeURIComponent(inputValues.lname));
            }
            searchParams.push("name:contains=" + values.join(","));
        }

        if (inputValues.DOB) {
            // birthdate needs to be in Y-m-d prefix
            searchParams.push("birthdate=" + encodeURIComponent(inputValues.DOB));
        }

        if (inputValues.email) {
            searchParams.push("email:contains=" + encodeURIComponent(inputValues.email));
        }

        if (!searchParams.length) {
            alert(this.__translations.SEARCH_REQUIRES_INPUT);
            throw new Error("Failed to perform search due to missing search operator");
        }

        url += "?" + searchParams.join("&");
        window.top.restoreSession();
        let headers = {
            'apicsrftoken': this.__apiCSRFToken
        };
        return window.fetch(url,
            {
                method: 'GET'
                ,redirect: 'manual'
                ,headers: headers
            })
            .then(result => {
                if (!(result.ok && result.status == 200))
                {
                    this.showActionAlert('danger', this.__translations.OPERATION_FAILED);
                    throw new Error("Failed to save participant in " + this.pc_eid + " with save data");
                } else {
                    return result.json();
                }
            })
            .then(result => {
                if (result && Object.prototype.hasOwnProperty.call(result, 'total') && result.total <= 0) {
                    this.showActionAlert('info', this.__translations.SEARCH_RESULTS_NOT_FOUND);
                    return [];
                } else {
                    this.clearActionAlerts();
                    // return the array of result entries.
                    return result.entry;
                }
            })
    }

    showPrimaryScreen() {
        let screens = this.container.querySelectorAll('.screen') || [];
        screens.forEach(i => {
            if (i.classList.contains('primary-screen')) {
                i.classList.remove('d-none');
            } else {
                i.classList.add('d-none');
            }
        });
        this.__currentScreen = 'primary-screen';
        this.updateParticipantControls();
    }

    updateParticipantControls() {

        // let's update if our pid is different
        if (this.__participantList.length <= 0) {
            this.container.querySelector('.no-third-party-patient-row').classList.remove('d-none');
            this.container.querySelectorAll('.third-party-patient-row').forEach(n => n.classList.add('d-none'));
            return;
        }
        this.container.querySelector('.no-third-party-patient-row').classList.add('d-none');

        this.container.querySelectorAll('.third-party-patient-row').forEach(n => n.classList.remove('d-none'));

        if (this.__updateParticipants) {
            let participantContainers = this.container.querySelectorAll('.patient-participant');
            if (!participantContainers.length) {
                console.error("Failed to find dom node with selector .patient-participant");
                return;
            }
            let templateNode = null;
            participantContainers.forEach(pc => {
                if (!pc.classList.contains('template')) {
                    pc.remove();
                } else {
                    templateNode = pc;
                }
            });
            if (!templateNode) {
                console.error("Failed to find dom node with selector .patient-participant.template");
                return;
            }
            // now we clone for each participant we have
            if (this.__participantList.length) {
                this.__participantList.forEach(p => {
                    let clonedNode = templateNode.cloneNode(true);
                    // note setting the innerText on these nodes already handles the escaping
                    this.setNodeInnerText(clonedNode, '.patient-name', p.callerName);
                    this.setNodeInnerText(clonedNode, '.patient-email', p.email);
                    clonedNode.classList.remove('template');
                    clonedNode.classList.remove('d-none');

                    let invitation = p.invitation || {};
                    let btnInvitationCopy = clonedNode.querySelector('.btn-invitation-copy');
                    let btnLinkCopy = clonedNode.querySelector('.btn-link-copy');
                    let btnGenerateLink = clonedNode.querySelector('.btn-generate-link');

                    if (invitation) {
                        if (btnGenerateLink) {
                            // setup our link generation process since the user has enabled the onetime
                            // note the server will check the onetime setting and reject requests if its not enabled
                            btnGenerateLink.addEventListener('click', this.generatePatientLink.bind(this, p.id));
                        }
                        btnInvitationCopy.addEventListener('click', this.copyPatientInvitationToClipboard.bind(this));
                        btnLinkCopy.addEventListener('click', this.copyPatientLinkToClipboard.bind(this));
                        btnLinkCopy.dataset['inviteId'] = p.id;

                        if (invitation.generated) {
                            // show our buttons, we lave the generate link button open again in case they need to generate a new link.
                            btnLinkCopy.classList.remove('d-none');
                            btnInvitationCopy.classList.remove('d-none');
                        }

                        // invitation text is escaped from innerText
                        this.setNodeInnerText(clonedNode, '.patient-invitation-text', invitation.text || "")
                    } else {
                        console.error("Failed to find invitation data for patient ", p);
                        btnInvitationCopy.classList.add('d-none');
                        btnLinkCopy.classList.add('d-none');
                    }

                    templateNode.parentNode.appendChild(clonedNode);
                });
            }
            this.__updateParticipants = false;
        }
    }

    showNewPatientScreen() {
        this.showSecondaryScreen('create-patient');
    }

    showSearchPatientScreen() {
        this.showSecondaryScreen('search-patient');
    }

    showSecondaryScreen(screenName) {
        let selector = '.' + screenName;
        let primaryScreen = this.container.querySelector('.primary-screen');
        if (!primaryScreen) {
            console.error("Failed to find primary-screen selector for add-patient-dialog container");
            return;
        }

        primaryScreen.classList.add('d-none');

        let screen = this.container.querySelector(selector);
        if (screen) {
            screen.classList.remove('d-none');
        } else {
            console.error("Failed to find selector for add-patient-dialog container " + selector);
        }
        this.__currentScreen = screenName;
    }


    getInputValues(screen, inputs) {
        let inputValues = {};
        inputs.forEach((i) => {
            let node = this.container.querySelector("." + screen + ' input[name="' + i + '"]');
            if (node) {
                inputValues[i] = node.value;
            } else {
                console.error("Failed to find input node with name " + i);
                inputValues[i] = null;
            }
        });
        return inputValues;
    }

    inviteSearchResultPatient(pid) {
        // hide the search results
        this.displaySearchList(false);
        this.showActionAlert('info', this.__translations.PATIENT_INVITATION_PROCESSING);
        this.sendSaveParticipant({pid: pid})
        .catch(error => {
            console.error(error);
            this.showActionAlert('danger', this.__translations.OPERATION_FAILED);
            // show the search results
            this.displaySearchList(true);
        })
    }

    displaySearchList(shouldDisplay) {
        let selector = ".search-patient .search-patient-list";
        let patientList = this.container.querySelector(selector);
        if (!patientList) {
            console.error("Failed to find ",selector);
            return;
        }
        if (shouldDisplay) {
            patientList.classList.remove('d-none');
        } else {
            patientList.classList.add('d-none');
        }
    }

    populateSearchResults(result) {
        console.log("populatingSearchResults with result ", result);
        let selector = ".search-patient .search-patient-list";
        let patientList = this.container.querySelector(selector);
        if (!patientList) {
            console.error("Failed to find ",selector);
            return;
        }

        patientList.classList.remove('d-none');
        let row = patientList.querySelector('.duplicate-match-row-template');

        // clear out the table rows
        let parentNode = row.parentNode;
        parentNode.replaceChildren();
        parentNode.appendChild(row);

        // need to loop on the result and populate per row
        result.forEach(r => {
            let resource = r.resource;
            let clonedNode = row.cloneNode(true);
            clonedNode.classList.remove('duplicate-match-row-template');
            parentNode.appendChild(clonedNode);

            let pid = (resource.identifier || []).find(i => i.type.coding.find(cd => cd.code == "PT") !== undefined);
            let pidValue = pid.value || "";
            this.setNodeInnerText(clonedNode, '.pid', pidValue);

            let birthDate = resource.birthDate;
            if (birthDate) {
                this.setNodeInnerText(clonedNode, '.dob', birthDate);
            }

            let name = (resource.name || []).find(n => n.use == 'official');
            if (name) {
                this.setNodeInnerText(clonedNode, '.fname', name.given.join(" "));
                this.setNodeInnerText(clonedNode, '.lname', name.family);
            }

            let email = (resource.telecom || []).find(t => t.system == 'email');
            if (email) {
                this.setNodeInnerText(clonedNode, '.email', email.value);
            } else {
                clonedNode.classList.add('missing-email');
            }

            if (pidValue) {
                clonedNode.querySelector('.btn-select-patient').addEventListener('click', () => {
                    this.inviteSearchResultPatient(pidValue);
                });
            }
            clonedNode.classList.remove('d-none');
        });
    }

    setNodeInnerText(node, selector, value) {
        let subNode = node.querySelector(selector);
        if (!subNode) {
            console.error("Failed to find node with selector " + selector);
            return;
        }
        subNode.textContent = value;
    }

    searchParticipantsAction()
    {
        let inputs = ['fname', 'lname', 'DOB', 'email', 'pid'];
        let inputValues = this.getInputValues('search-patient', inputs);
        // form validation happens server side.
        this.toggleActionButton(false);
        this.sendSearchResults(inputValues)
            .then(result => {
                this.toggleActionButton(true);
                if (result.length) {
                    this.populateSearchResults(result);
                }
                else {
                    this.populateSearchResults([]);
                    let resultMessage = this.__translations.SEARCH_RESULTS_NOT_FOUND;
                }
            })
            .catch(error => {
                this.toggleActionButton(true);
                console.error(error);
            });
    }

    createPatientAction() {
        let inputs = ['fname', 'lname', 'DOB', 'email'];
        let inputValues = this.getInputValues('create-patient', inputs);
        // for now we don't do the searching but we will do the invitation here...
        this.clearActionAlerts();
        this.showActionAlert('info', this.__translations.PATIENT_INVITATION_PROCESSING);
        this.toggleActionButton(false);
        this.sendSaveParticipant(inputValues)
        .then(() => {
            this.toggleActionButton(true);
        })
        .catch(error => {
            this.toggleActionButton(true);
            console.error(error);
            this.showActionAlert('danger', this.__translations.OPERATION_FAILED);
        });
    }

    toggleActionButton(enabled) {
        let btns = this.container.querySelectorAll('.btn-create-patient,.btn-invite-search');
        btns.forEach(b => b.disabled = !enabled);
    }

    handleSaveParticipantErrorResponse(json) {
        // need to display the error message.
        let message = [];
        if (json.fields) {
            if (json.fields.DOB) {
                message.push(this.__translations.PATIENT_CREATE_INVALID_DOB);
            }
            if (json.fields.email) {
                message.push(this.__translations.PATIENT_CREATE_INVALID_EMAIL);
            }
            if (json.fields.fname || json.fields.lname) {
                message.push(this.__translations.PATIENT_CREATE_INVALID_NAME);
            }
            this.showActionAlert('danger', message.join(". "));
        } else {
            this.showActionAlert('danger', this.__translations.OPERATION_FAILED);
        }
    }

    clearActionAlerts() {
        let alerts = this.container.querySelectorAll('.alert');
        alerts.forEach(a => {
            if (a.classList.contains('alert-template')) {
                a.classList.add('d-none');
            } else {
                a.parentNode.removeChild(a);
            }
        });
    }

    showActionAlert(type, message) {
        this.clearActionAlerts(); // for now we will just remove these, we could animate & stack if we wanted.
        let template = this.container.querySelector('.alert.alert-template');
        let alert = template.cloneNode(true);
        alert.innerText = message;
        alert.classList.remove('alert-template');
        alert.classList.remove('d-none');
        alert.classList.add('alert-' + type);
        template.parentNode.insertBefore(alert, template);
        alert.scrollIntoView();
    }

    setupModal() {
        // see templates/comlink/conference-room.twig
        let id = 'telehealth-container-invite-patient';
        // let bootstrapModalTemplate = window.document.createElement('div');
        // we use min-height 90vh until we get the bootstrap full screen modal in bootstrap 5
        let node = document.getElementById(id);
        // we are going to clone the node and add an index to this...
        let clonedNode = node.cloneNode(true);
        clonedNode.id = id + "-" + this.__idx++;
        node.parentNode.appendChild(clonedNode);
        this.container = clonedNode;

        this.modal = new bootstrap.Modal(this.container, {keyboard: false, focus: true, backdrop: 'static'});

        this.addActionToButton('.btn-telehealth-confirm-cancel', this.cancelDialog.bind(this));
        this.addActionToButton('.btn-show-new-patient-screen', this.showNewPatientScreen.bind(this));
        this.addActionToButton('.btn-show-search-patient-screen', this.showSearchPatientScreen.bind(this));
        this.addActionToButton('.btn-cancel-screen-action', this.showPrimaryScreen.bind(this));
        this.addActionToButton('.btn-create-patient', this.createPatientAction.bind(this));
        this.addActionToButton('.btn-invite-search', this.searchParticipantsAction.bind(this));

        // we update the participant list as it may have changed from when the DOM originally sent it down.
        this.__updateParticipants = true;
        this.updateParticipantControls();
    }

    copyPatientLinkToClipboard(evt) {
        let target = evt.currentTarget;
        if (!target) {
            console.error("Failed to get a dom node cannot proceed with copy");
            return;
        }

        let id = target.dataset['inviteId'];
        if (!id) {
            // no link just ignoring
            console.error("Failed to find patient id to copy link for patient");
            this.showActionAlert('danger', this.__translations.CLIPBOARD_COPY_FAILURE);
            return;
        }
        let participant = this.__participantList.find(pl => pl.id == id);
        if (participant) {
            let invitation = participant.invitation || {};
            this.copyTextToClipboard(invitation.link || "");
        } else {
            this.showActionAlert('danger', this.__translations.CLIPBOARD_COPY_FAILURE);
        }
    }

    sendGeneratePatientLink(patientId) {
        let postData = {pc_eid: this.pc_eid, pid: patientId};
        let scriptLocation = this.scriptLocation + "?action=generate_participant_link";

        window.top.restoreSession();
        return window.fetch(scriptLocation,
            {
                method: 'POST'
                ,headers: {
                    'Content-Type': 'application/json'
                }
                ,body: JSON.stringify(postData)
                ,redirect: 'manual'
            })
            .then(result => {
                if (result.status == 400 || result.status == 401 || (result.ok && result.status == 200)) {
                    return result.json();
                } else {
                    throw new Error("Failed to generate participant link for participant " + this.pid
                        + " with save data for session " + this.pc_eid);
                }
            });
    }

    generatePatientLink(patientId, evt) {
        // let's do a post to generate the link to OpenEMR
        let target = evt.currentTarget;

        // once we have the link we will update the button to copy the link.
        // we will also insert the invitation text into the patient invitation text div.
        // then we will show the copy button and the copy invitation button.
        this.clearActionAlerts();
        this.showActionAlert('info', this.__translations.PATIENT_INVITATION_PROCESSING);
        this.toggleActionButton(false);
        this.sendGeneratePatientLink(patientId)
            .then(result => {
                this.toggleActionButton(true);
                this.clearActionAlerts();
                if (result.success) {
                    let invitation = result.invitation;
                    let patient = this.__participantList.find(p => p.id == patientId);
                    if (patient) {
                        patient.invitation = invitation;
                        this.__updateParticipants = true;
                        this.updateParticipantControls();
                        this.showActionAlert('success', this.__translations.PATIENT_INVITATION_GENERATED);
                    } else {
                        this.showActionAlert('danger', this.__translations.PATIENT_INVITATION_FAILURE);
                    }
                } else {
                    this.showActionAlert('danger', this.__translations.PATIENT_INVITATION_FAILURE);
                }
            })
            .catch(error => {
                console.error(error);
                this.showActionAlert('danger', this.__translations.PATIENT_INVITATION_FAILURE);
            });
    }

    copyPatientInvitationToClipboard(evt) {
        let target = evt.target;
        if (!target) {
            console.error("Failed to get a dom node cannot proceed with copy");
            return;
        }

        let closest = target.closest(".patient-participant[data-pid]");
        if (!closest) {
            this.showActionAlert('danger', this.__translations.CLIPBOARD_COPY_FAILURE);
            throw new Error("Failed to find patient to copy invitation");
        }
        let invitation = closest.querySelector(".patient-invitation-text");
        if (!invitation) {
            this.showActionAlert('danger', this.__translations.CLIPBOARD_COPY_FAILURE);
            throw new Error("Failed to find invitation text with selector .patient-invitation-text");
        }
        let text = invitation.textContent;
        this.copyTextToClipboard(text);
    }

    copyTextToClipboard(text) {

        // this is getting deprecated
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => {
                this.showActionAlert('success', this.__translations.CLIPBOARD_COPY_SUCCESS);
            })
                .catch(error => {
                    console.error(error);
                    this.showActionAlert('danger', this.__translations.CLIPBOARD_COPY_FAILURE);
                })
        } else {
            console.error("clipboard.writeText does not exist");
            this.showActionAlert('danger', this.__translations.CLIPBOARD_COPY_FAILURE);
        }
    }

    addActionToButton(selector, action) {
        let btns = this.container.querySelectorAll(selector);
        for (var i = 0; i < btns.length; i++)
        {
            btns[i].addEventListener('click', action);
        }
    }

    show() {
        if (!this.modal) {
            this.setupModal();
        }

        this.modal.show();
    }

    destruct() {
        this.modal = null;
        // we clean everything up by removing the node which also removes the event listeners.
        this.container.parentNode.removeChild(this.container);
        this.container = null;
    }
}

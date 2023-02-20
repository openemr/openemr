
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
     *
     * @type {function}
     */
    closeCallback = null;

    __translations = null;

    __idx = 0;

    __apiCSRFToken = null;

    __currentScreen = null;

    __updatedCallerSettings = null;

    __currentThirdParty = null;

    constructor(apiCSRFToken, translations, pc_eid, scriptLocation, currentThirdParty, closeCallback) {
        this.pc_eid = pc_eid;
        this.scriptLocation = scriptLocation;
        this.closeCallback = closeCallback;
        this.__translations = translations;
        this.__apiCSRFToken = apiCSRFToken;
        this.__currentThirdParty = currentThirdParty;
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
                if (!(result.ok && result.status == 200))
                {
                    throw new Error("Failed to save participant in " + this.pc_eid + " with save data");
                } else {
                    return result.json();
                }
            })
            .then(jsonResult => {
                let callerSettings = jsonResult.callerSettings || {};
                this.showActionAlert('success', this.__translations.PATIENT_INVITATION_SUCCESS);
                // let's show we were successful and close things up.
                this.__updatedCallerSettings = callerSettings;
                this.__currentThirdParty = callerSettings.thirdPartyPatient;
                this.updateThirdPartyControls();
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
        // let url = '/apis/default/fhir/Patient/_search?given:contains=<fname>&family:contains=<lname>&birthDate=<dob>'
        // TODO: @adunsulag note the local api SKIPS the site parameter.  I don't really like that but for now we will
        // ignore the site parameter as well
        // TODO: @adunsulag need to make sure we get the FQDN url here.
        let url = '/apis/fhir/Patient';
        let searchParams = [];

        if (inputValues.pid) {
            searchParams.push("identifier=" + inputValues.pid);
        }
        if (inputValues.fname || inputValues.lname) {
            let values = [];
            if (inputValues.fname) {
                values.push(inputValues.fname);
            }
            if (inputValues.lname) {
                values.push(inputValues.lname);
            }
            searchParams.push("name:contains=" + values.join(","));
        }

        if (inputValues.DOB) {
            // birthdate needs to be in Y-m-d prefix
            searchParams.push("birthdate=" + inputValues.DOB);
        }

        if (inputValues.email) {
            searchParams.push("email:contains=" + inputValues.email);
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
                    // TODO: @adunsulag update the session title here...
                    this.showActionAlert('danger', this.__translations.OPERATION_FAILED);
                    throw new Error("Failed to save participant in " + this.pc_eid + " with save data");
                } else {
                    return result.json();
                }
            })
            .then(result => {
                if (result.total == 0) {
                    this.showActionAlert('info', this.__translations.SEARCH_RESULTS_NOT_FOUND);
                    return [];
                } else {
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
        this.updateThirdPartyControls();
    }

    updateThirdPartyControls() {

        // let's update if our pid is different
        if (!this.__currentThirdParty) {
            this.container.querySelector('.no-third-party-patient-row').classList.remove('d-none');
            this.container.querySelectorAll('.third-party-patient-row').forEach(n => n.classList.add('d-none'));
            return;
        }
        this.container.querySelector('.no-third-party-patient-row').classList.add('d-none');
        this.container.querySelectorAll('.third-party-patient-row').forEach(n => n.classList.remove('d-none'));
        // now we need to update our participant screen if the pid has changed
        let thirdPartyRow = this.container.querySelector('.patient-thirdparty');
        if (!thirdPartyRow) {
            console.error("Failed to find dom node with selector .patient-thirdparty");
            return;
        }

        if (thirdPartyRow.dataset['pid'] != this.__currentThirdParty.pid) {
            // time to do some update magic
            thirdPartyRow.dataset['pid'] = this.__currentThirdParty.pid;
            let name = (this.__currentThirdParty.fname || "") + " " + (this.__currentThirdParty.lname || "");
            this.setNodeInnerText(thirdPartyRow, '.patient-name', name);
            this.setNodeInnerText(thirdPartyRow, '.patient-dob', this.__currentThirdParty.DOB);
            this.setNodeInnerText(thirdPartyRow, '.patient-email', this.__currentThirdParty.email);
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

            let pid = resource.identifier.find(i => i.type.coding.find(cd => cd.code == "PT") !== undefined);
            let pidValue = pid.value || "";
            this.setNodeInnerText(clonedNode, '.pid', pidValue);

            let birthDate = resource.birthDate;
            if (birthDate) {
                this.setNodeInnerText(clonedNode, '.dob', birthDate);
            }

            let name = resource.name.find(n => n.use == 'official');
            if (name) {
                this.setNodeInnerText(clonedNode, '.fname', name.given.join(" "));
                this.setNodeInnerText(clonedNode, '.lname', name.family);
            }

            let email = resource.telecom.find(t => t.system == 'email');
            if (email) {
                this.setNodeInnerText(clonedNode, '.email', email.value);
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
        // TODO: @adunsulag need to do form validation checking...
        this.sendSearchResults(inputValues)
            .then(result => {
                if (result.length) {
                    this.populateSearchResults(result);
                }
                else {
                    // TODO: @adunsulag change this.
                    this.populateSearchResults([]);
                    let resultMessage = this.__translations.SEARCH_RESULTS_NOT_FOUND;
                    setTimeout(function() {
                        alert(resultMessage);
                    }, 0);
                }
            })
            .catch(error => {
                console.error(error);
            });
    }

    createPatientAction() {
        let inputs = ['fname', 'lname', 'DOB', 'email'];
        let inputValues = this.getInputValues('create-patient', inputs);
        // for now we don't do the searching but we will do the invitation here...
        this.clearActionAlerts();
        this.showActionAlert('info', this.__translations.PATIENT_INVITATION_PROCESSING);
        // TODO: need to disable the save button during the save.
        this.sendSaveParticipant(inputValues)
        .catch(error => {
            this.showActionAlert('danger', this.__translations.OPERATION_FAILED);
            // TODO: @adunsulag need to handle the errors here.
            console.error(error);
        });
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
        this.addActionToButton('.btn-invitation-copy', this.copyPatientInvitationToClipboard.bind(this));
        this.addActionToButton('.btn-link-copy', this.copyPatientLinkToClipboard.bind(this))

        let actionButton = this.container.querySelector('.btn-invite-search');
        if (actionButton)
        {
            actionButton.addEventListener('click', this.__searchOrAddParticipant);
        } else {
            console.error("Could not find selector with .btn-telehealth-confirm-yes");
        }
        this.updateThirdPartyControls();
    }

    copyPatientLinkToClipboard(evt) {
        let target = evt.currentTarget;
        if (!target) {
            console.error("Failed to get a dom node cannot proceed with copy");
            return;
        }

        let link = target.dataset['inviteLink'];
        if (!link) {
            // no link just ignoring
            console.error("Failed to find link for patient");
            this.showActionAlert('danger', this.__translations.CLIPBOARD_COPY_FAILURE);
            return;
        }
        this.copyTextToClipboard(link);
    }

    copyPatientInvitationToClipboard(evt) {
        let target = evt.target;
        if (!target) {
            console.error("Failed to get a dom node cannot proceed with copy");
            return;
        }

        let closest = target.closest(".patient-thirdparty[data-pid]");
        if (!closest) {
            this.showActionAlert('danger', this.__translations.CLIPBOARD_COPY_FAILURE);
            throw new Error("Failed to find patient to copy invitation");
        }
        let invitation = closest.querySelector(".thirdparty-invitation-text");
        if (!invitation) {
            this.showActionAlert('danger', this.__translations.CLIPBOARD_COPY_FAILURE);
            throw new Error("Failed to find invitation text with selector .thirdparty-invitation-text");
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

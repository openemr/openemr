
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

    /**
     * Because javascript is asinine in treating 'this' with bound functions we have these pointers.
     * Modern JS allows us to 'overload' the 'get' parameter to handle this better but its still dumb.
     * Makes me almost tempted to go back to jquery's on/off functionality.
     * @type {function}
     * @private
     */
    __cancelDialog = null;
    __searchOrAddParticipant = null;

    __translations = null;

    __idx = 0;

    constructor(translations, pc_eid, scriptLocation, closeCallback) {
        this.pc_eid = pc_eid;
        this.scriptLocation = scriptLocation;
        this.closeCallback = closeCallback;
        this.__cancelDialog = this.cancelDialog.bind(this);
        this.__searchOrAddParticipant = this.searchOrAddParticipant.bind(this);
        this.__translations = translations;
    }

    cancelDialog() {
        let modal = this.modal;
        try {
            modal.hide();
        }
        catch (error) {
            console.error((error));
        }
        this.destruct();

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
                    // TODO: @adunsulag update the session title here...
                    alert(this.__translations.OPERATION_FAILED);
                    throw new Error("Failed to save participant in " + this.pc_eid + " with save data");
                } else {
                    return result.json();
                }
            })
            .then(jsonResult => {
                let callerSettings = jsonResult.callerSettings || {};
                this.closeDialogAndSendCallerSettings(callerSettings);
            })
    }
    closeDialogAndSendCallerSettings(callerSettings) {
        jQuery(this.container).on("hidden.bs.modal", () => {
            try {
                jQuery(this.container).off("hidden.bs.modal");
                this.closeCallback(callerSettings);
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
        let url = '/apis/default/fhir/Patient/_search?given:contains=<fname>&family:contains=<lname>&birthDate=<dob>'
        let searchParams = "";
        if (inputValues.fname || inputValues.lname) {
            let values = [];
            if (inputValues.fname) {
                values.push(inputValues.fname);
            }
            if (inputValues.lname) {
                values.push(inputValues.lname);
            }
            searchParams += "&name:contains=" + value.join(",");
        }

        if (inputValues.dob) {
            // birthdate needs to be in Y-m-d prefix
            searchParams += "birthdate=" + inputValues.dob;
        }
        url += "?" + searchParams;
    }

    searchOrAddParticipant()
    {
        let inputs = ['fname', 'lname', 'DOB', 'email', 'pid'];
        let inputValues = {};
        inputs.forEach((i) => {
            let node = this.container.querySelector('input[name="' + i + '"]');
            if (node) {
                inputValues[i] = node.value;
            } else {
                console.error("Failed to find input node with name " + i);
                inputValues[i] = null;
            }
        });
        // TODO: @adunsulag need to do form validation checking...

        // let's search for the patient and present our existing patients if we have some

        this.sendSearchResults(inputValues)
        .then(result => {
            if (result.length) {
                // populate our search piece
                return Promise.resolve([]);
            }
            else {
                // for now we don't do the searching but we will do the invitation here...
                return this.sendSaveParticipant(inputValues)
            }
        })
        .catch(error => {
            console.error(error);
        });
    }

    setupModal() {
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

        let btns = this.container.querySelectorAll('.btn-telehealth-confirm-cancel');
        for (var i = 0; i < btns.length; i++)
        {
            btns[i].addEventListener('click', this.__cancelDialog);
        }
        let actionButton = this.container.querySelector('.btn-invite-search');
        if (actionButton)
        {
            actionButton.addEventListener('click', this.__searchOrAddParticipant);
        } else {
            console.error("Could not find selector with .btn-telehealth-confirm-yes");
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

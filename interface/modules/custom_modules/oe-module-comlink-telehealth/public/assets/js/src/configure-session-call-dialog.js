
export class ConfigureSessionCallDialog
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
    __updateSettingsAndCloseDialog = null;

    constructor(pc_eid, scriptLocation, closeCallback) {
        this.pc_eid = pc_eid;
        this.scriptLocation = scriptLocation;
        this.closeCallback = closeCallback;
        this.__cancelDialog = this.cancelDialog.bind(this);
        this.__updateSettingsAndCloseDialog = this.updateSettingsAndCloseDialog.bind(this);
    }

    cancelDialog() {
        let modal = this.modal;
        this.destruct();
        modal.hide();
    }

    processConfirmYesAction(evt) {
        this.container.querySelector('.row-confirm').classList.add('d-none');
        this.container.querySelector('.row-update-status').classList.remove('d-none');
    }

    sendSettingsSave(settings)
    {
        console.log("Saving session settings to ", settings);
        let postData = Object.assign({pc_eid: this.pc_eid}, settings);
        let scriptLocation = this.scriptLocation + "action=save_session_settings";

        window.top.restoreSession();
        window.fetch(scriptLocation,
            {
                method: 'POST'
                ,headers: {
                    'Content-Type': 'application/json'
                }
                ,body: postData
                ,redirect: 'manual'
            })
            .then(result => {
                if (!(result.ok && result.status == 200))
                {
                    alert(translations.APPOINTMENT_STATUS_UPDATE_FAILED);
                    console.error("Failed to update session " + this.pc_eid + " with settings " + settings);
                }
            });
    };

    getSettings() {
        let thirdPartyEnabledNode = document.querySelector("input[name='enable-participant-invite']");
        let checked = thirdPartyEnabledNode && thirdPartyEnabledNode.checked;
        let settings = {
            "enableThirdParty": checked == true
        };
        return settings;
    }
    updateSettingsAndCloseDialog()
    {

        jQuery(this.container).on("hidden.bs.modal", () => {
            try {
                jQuery(this.container).off("hidden.bs.modal");
                // settings saved and being returned to the callback.
                this.closeCallback(this.getSettings());
            }
            catch (error)
            {
                console.error(error);
            }
            try {
                this.sendSettingsSave(this.getSettings());
            }
            catch (updateError)
            {
                console.error(updateError);
            }
        });
        this.modal.hide();
    }

    setupModal() {
        let id = 'telehealth-container-configure-session';
        // let bootstrapModalTemplate = window.document.createElement('div');
        // we use min-height 90vh until we get the bootstrap full screen modal in bootstrap 5
        this.container = document.getElementById(id);
        this.modal = new bootstrap.Modal(this.container, {keyboard: false, focus: true, backdrop: 'static'});

        let btns = this.container.querySelectorAll('.btn-telehealth-confirm-cancel');
        for (var i = 0; i < btns.length; i++)
        {
            btns[i].addEventListener('click', this.__cancelDialog);
        }
        let updateSettings = this.container.querySelector('.btn-telehealth-session-update');
        if (updateSettings)
        {
            updateSettings.addEventListener('click', this.__updateSettingsAndCloseDialog);
        } else {
            console.error("Could not find selector with .btn-telehealth-confirm-yes");
        }
    }

    resetModal() {
    }

    show() {
        if (!this.modal) {
            this.setupModal();
        }

        this.modal.show();
    }

    destruct() {
        let btns = this.container.querySelectorAll('.btn-telehealth-confirm-cancel');
        for (var i = 0; i < btns.length; i++)
        {
            btns[i].removeEventListener('click', this.__cancelDialog);
        }
        let updateSettings = this.container.querySelector('.btn-telehealth-session-update');
        if (updateSettings)
        {
            updateSettings.removeEventListener('click', this.__updateSettingsAndCloseDialog);
        } else {
            console.error("Could not find selector with .btn-telehealth-confirm-yes");
        }
    }
}

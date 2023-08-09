/**
 * Javascript Controller for the session close dialog window.  It handles the updating of the appointment status
 * and any other final actions that need to occur when a provider ends a session.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2023 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
export function ConfirmSessionCloseDialog(translations, pc_eid, scriptLocation, closeCallback)
{
    let dialog = this;
    let modal = null;
    let container = null;

    this.cancelDialog = function()
    {
        // reset everything here.
        let sections = container.querySelectorAll(".hangup-section");
        let startSection = container.querySelector('.hangup-section.hangup-section-start');
        if (sections && sections.length)
        {
            for (let i =0; i < sections.length; i++)
            {
                sections[i].classList.add("d-none");
            }
        }
        if (startSection)
        {
            startSection.classList.remove("d-none");
        }

        modal.hide();
    };

    this.processConfirmYesAction = function(evt) {
        container.querySelector('.row-confirm').classList.add('d-none');
        container.querySelector('.row-update-status').classList.remove('d-none');
    };

    this.sendAppointmentStatusUpdate = function(status)
    {
        console.log("Setting appointment to status ", status);
        let postData = "action=set_appointment_status&pc_eid=" + encodeURIComponent(pc_eid)
            + "&status=" + encodeURIComponent(status);
        window.top.restoreSession();
        window.fetch(scriptLocation,
            {
                method: 'POST'
                ,headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
                ,body: postData
                ,redirect: 'manual'
            })
            .then(result => {
                if (!(result.ok && result.status == 200))
                {
                    alert(translations.APPOINTMENT_STATUS_UPDATE_FAILED);
                    console.error("Failed to update appointment " + pc_eid + " to status " + status);
                }
            });
    };

    this.updateAppointmentStatusAndClose = function(status)
    {

        jQuery(container).on("hidden.bs.modal", function () {
            try {
                jQuery(container).off("hidden.bs.modal");
                closeCallback();
            }
            catch (error)
            {
                console.error(error);
            }
            try {
                if (status != 'CloseWithoutUpdating')
                {
                    dialog.sendAppointmentStatusUpdate(status);
                }
            }
            catch (updateError)
            {
                console.error(updateError);
            }
        });
        modal.hide();
    };

    this.processHangupSetting = function(evt)
    {
        let target = evt.currentTarget;
        let status = target.dataset['status'] || 'CloseWithoutUpdating'; // - means none
        dialog.updateAppointmentStatusAndClose(status);
    };

    this.processSetStatusFromSelector = function(evt)
    {
        let selector = container.querySelector('.appointment-status-update');
        if (selector && selector.value)
        {
            dialog.updateAppointmentStatusAndClose(selector.value);
        } else {
            console.error("Failed to find selector .appointment-status-update node or value is not defined for node");
        }
    };

    this.show = function() {
        let id = 'telehealth-container-hangup-confirm';
        // let bootstrapModalTemplate = window.document.createElement('div');
        // we use min-height 90vh until we get the bootstrap full screen modal in bootstrap 5
        container = document.getElementById(id);
        modal = new bootstrap.Modal(container, {keyboard: false, focus: true, backdrop: 'static'});

        let btns = container.querySelectorAll('.btn-telehealth-confirm-cancel');
        for (var i = 0; i < btns.length; i++)
        {
            btns[i].addEventListener('click', dialog.cancelDialog);
        }
        let confirmYes = container.querySelector('.btn-telehealth-confirm-yes');
        if (confirmYes)
        {
            confirmYes.addEventListener('click', dialog.processConfirmYesAction);
        } else {
            console.error("Could not find selector with .btn-telehealth-confirm-yes");
        }

        let statusOtherUpdateBtn = container.querySelector('.btn-telehealth-session-select-update');
        if (statusOtherUpdateBtn)
        {
            statusOtherUpdateBtn.addEventListener('click', dialog.processSetStatusFromSelector);
        }

        btns = container.querySelectorAll('.btn-telehealth-session-close');
        for (var i = 0; i < btns.length; i++)
        {
            btns[i].addEventListener('click', dialog.processHangupSetting);
        }
        modal.show();
    }
}

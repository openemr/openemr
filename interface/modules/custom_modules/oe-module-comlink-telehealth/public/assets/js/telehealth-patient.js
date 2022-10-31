/**
 * Patient TeleHealth methods for launching telehealth sessions from the patient portal.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
(function(window, comlink) {
    let telehealth = comlink.telehealth || {};
    let translations = comlink.translations || {};

    function launchDialog(evt)
    {
        let target = evt.currentTarget;
        try {
            var appointmentEventId = target.dataset['pc_eid'] || null;
            if (!appointmentEventId) {
                throw new Error("No appointmentEventId id found, cannot start session");
            }
            if (telehealth.showPatientPortalDialog)
            {
                window.comlink.telehealth.showPatientPortalDialog(appointmentEventId)
            } else {
                throw new Error("Could not launch session, missing library");
            }
        }
        catch (error) {
            alert(translations.SESSION_LAUNCH_FAILED);
            console.error(error);
        }
    }

    function init() {
        let launchButtons = document.querySelectorAll(".btn-comlink-telehealth-launch");
        for (let i = 0; i < launchButtons.length; i++)
        {
            launchButtons[i].addEventListener('click', launchDialog);
        }
        telehealth.launchRegistrationChecker(true);
    }

    if (telehealth && telehealth.launchRegistrationChecker)
    {
        window.addEventListener('load', init);
    }
})(window, window.comlink || {});
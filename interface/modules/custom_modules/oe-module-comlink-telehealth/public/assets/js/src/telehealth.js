import {RegistrationChecker} from "./registration-checker.js";
import {ConferenceRoom} from "./conference-room.js";
import {PatientConferenceRoom} from "./patient-conference-room.js";


/**
 * Core TeleHealth javascript library for communicating with OpenEMR to start and stop TeleHealth sessions.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

(function(window, comlink, bootstrap, jQuery) {

    /**
     *
     * @type {ConferenceRoom}
     */
    let conferenceRoom = false;

    /**
     * @type {string} The path of where the module is installed at.  In a multisite we pull this from the server configuration, otherwise we default here
     */
    let moduleLocation = comlink.settings.modulePath || '/interface/modules/custom_modules/oe-module-comlink-telehealth/';

    /**
     * @var VideoBar
     */
    let videoBar = null;

    /**
     * Handler from setInterval used as polling handle that checks to see if the provider has entered into the
     * conference room and is ready to chat with the patient.
     * @type number
     */
    let checkProviderReadyForPatientInterval = null;

    /**
     *
     * @type {RegistrationChecker}
     */
    let checker = null;

    let telehealthRegistrationInterval = null;

    let registrationSettings;

    let defaultTranslations = {
        'CALL_CONNECT_FAILED': "Failed to connect the call.",
        'SESSION_LAUNCH_FAILED': "There was an error in launching your telehealth session.  Please try again or contact support",
        'APPOINTMENT_STATUS_UPDATE_FAILED': 'There was an error in saving the telehealth appointment status.  Please contact support or update the appointment manually in the calendar',
        'DUPLICATE_SESSION': "You are already in a conference session.  Please hangup the current call to start a new telehealth session",
        'HOST_LEFT': "Host left the call",
        "CONFIRM_SESSION_CLOSE": "Are you sure you want to close this session?",
        "TELEHEALTH_MODAL_TITLE": "TeleHealth Session",
        "TELEHEALTH_MODAL_CONFIRM_TITLE": "Confirm Session Close",
        "UPDATE_APPOINTMENT_STATUS" : "Update appointment status",
        "STATUS_NO_SHOW" : "No Show",
        "STATUS_CANCELED" : "Canceled",
        "STATUS_CHECKED_OUT" : "Checked Out",
        "STATUS_SKIP_UPDATE": "Skip Update",
        "CONFIRM" : "Confirm",
        "STATUS_NO_UPDATE": "No Change",
        "STATUS_OTHER": "Other"
    };
    let translations = comlink.translations || defaultTranslations;

    /**
     * Returns the API endpoint to call for a patient or a provider for telehealth communication
     * @param forPatient
     */
    function getTeleHealthScriptLocation(forPatient)
    {
        if (forPatient === true)
        {
            return moduleLocation + 'public/index-portal.php';
        } else {
            return moduleLocation + 'public/index.php';
        }
    }

    // TODO: @adunsulag eventually we can merge this into our conference room app.
    function ATApp(conferenceRoom, params)
    {

        return this;
    }

    function launchProviderVideoMessage(data)
    {
        if (conferenceRoom)
        {
            if (conferenceRoom.inSession) {
                alert(translations.DUPLICATE_SESSION);
                return;
            }
            else
            {
                // destroy the session.
                conferenceRoom.destruct();
                conferenceRoom = null;
            }
        }
        conferenceRoom = new ConferenceRoom(translations, getTeleHealthScriptLocation(false));
        conferenceRoom.init(data);
    }


    function showPatientPortalDialog(appointmentEventId) {
            let telehealthSessionData = {
                pc_eid: appointmentEventId
            };
        conferenceRoom = new PatientConferenceRoom(translations, getTeleHealthScriptLocation(true));
        conferenceRoom.init(telehealthSessionData);
    }

    function launchRegistrationChecker(isPatient)
    {
        checker = new RegistrationChecker(getTeleHealthScriptLocation(isPatient));
        checker.checkRegistration();
    }

    // now to export our object here
    comlink.telehealth = {
        showPatientPortalDialog: showPatientPortalDialog,
        launchProviderVideoMessage: launchProviderVideoMessage,
        launchRegistrationChecker: launchRegistrationChecker,
    };
    // now reassign our comlink object or create it new if there are no other comlink extensions.
    window.comlink = comlink;
})(window, window.comlink || {}, bootstrap, $, window.dlgopen || function() {});

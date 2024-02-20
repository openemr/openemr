/**
 * Calendar TeleHealth javascript library for interacting with the OpenEMR calendar tab.
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

    let defaultTranslations = {
        'SESSION_LAUNCH_FAILED': "There was an error in launching your telehealth session.  Please try again or contact support",
        'PROVIDER_SESSION_START_PROMPT': "Would you like to start a telehealth session with this patient (This will create an encounter if one does not exist)?",
        "PROVIDER_SESSION_CLONE_START_PROMPT": "This appointment belongs to a different provider. Would you still like to start a telehealth session (this will copy the appointment to your calendar and create an encounter if needed)?",
        "PROVIDER_SESSION_TELEHEALTH_UNENROLLED": "This is a Telehealth session appointment.  If you would like to provide telehealth sessions to your clients contact your administrator to enroll today.",
        "CALENDAR_EVENT_DISABLED": "TeleHealth Sessions can only be launched within two hours of the current appointment time.",
        "CALENDAR_EVENT_COMPLETE": "This TeleHealth appointment has been completed."
    };
    let translations = comlink.translations || defaultTranslations;

    function closeDialogAndLaunchTelehealthSession(evt)
    {
        sendToEncounter(evt)
        .then(() => {
            if (window.dlgclose) {
                window.dlgclose();
            }
        })
        .catch(error => {
            console.error(error);
            alert(translations.SESSION_LAUNCH_FAILED);
        });
    }
    function setCurrentEncounterForAppointment(pid, appointmentId)
    {
        window.top.restoreSession();
        return window.fetch(moduleLocation + 'public/index.php?action=set_current_appt_encounter&pc_eid=' + encodeURIComponent(appointmentId), {redirect: "manual"})
            .then(response => {
                if (!response.ok)
                {
                    throw new Error("Failed to retrieve encounter settings");
                }
                return response.json();
            });
    }

    function apptCompleteAlert(evt)
    {
        evt.stopPropagation();
        evt.preventDefault();
        alert(translations.CALENDAR_EVENT_COMPLETE);
    }

    function apptTelehealthUnenrolled(evt)
    {
        evt.stopPropagation();
        evt.preventDefault();
        alert(translations.PROVIDER_SESSION_TELEHEALTH_UNENROLLED);
    }

    function apptDisabledAlert(evt)
    {
        evt.stopPropagation();
        evt.preventDefault();
        alert(translations.CALENDAR_EVENT_DISABLED);
    }

    function switchProvidersAndSendToTeleHealthSession(evt)
    {
        if (!evt.target)
        {
            console.error("target invalid");
            return;
        }
        let pid = evt.target.dataset["pid"];
        let pc_eid = evt.target.dataset["eid"];
        if (confirm(translations.PROVIDER_SESSION_CLONE_START_PROMPT))
        {
            console.log("Starting telehealth session");
            // need to grab the encounter (// create the encounter as needed
            return setCurrentEncounterForAppointment(pid, pc_eid)
                .then(encounterData => {
                    loadEncounterFromEncounterData(encounterData, pid, pc_eid);
                    // the ChangeProviders function comes from the internal OpenEMR calendar view
                    if (window.ChangeProviders && encounterData.user && encounterData.user.username)
                    {
                        // set our encounter
                        // we need to clear off every option
                        let calendarUsername = window.document.querySelector("#pc_username");
                        if (calendarUsername && calendarUsername.options)
                        {
                            calendarUsername.selectedIndex = -1;
                            for (let i = 0;i < calendarUsername.options.length; i++)
                            {
                                if (calendarUsername.options[i].value == encounterData.user.username)
                                {
                                    calendarUsername.selectedIndex = i;
                                    break;
                                }
                            }
                        }
                        window.ChangeProviders();
                    }
                })
                .catch(error => {
                    alert(translations.SESSION_LAUNCH_FAILED);
                    console.error(error);
                });
        }
    }

    function sendToEncounter(evt)
    {
        if (!evt.target)
        {
            console.error("target invalid");
            return;
        }
        let pid = evt.target.dataset["pid"];
        let pc_eid = evt.target.dataset["eid"];

        evt.stopPropagation();
        evt.preventDefault();
        if (confirm(translations.PROVIDER_SESSION_START_PROMPT))
        {
            console.log("Starting telehealth session");
            // need to grab the encounter (// create the encounter as needed
            return setCurrentEncounterForAppointment(pid, pc_eid)
            .then(encounterData => {
                loadEncounterFromEncounterData(encounterData, pid, pc_eid);
            })
            .catch(error => {
                alert(translations.SESSION_LAUNCH_FAILED);
                console.error(error);
            });
        }
    }

    function loadEncounterFromEncounterData(encounterData, pid, pc_eid)
    {
        if (!(encounterData.encounterList && encounterData.patient && encounterData.selectedEncounter))
        {
            throw new Error("Missing encounter information in order to start telehealth appointment");
        }
        console.log("grabbed encounter ", encounterData);
        window.top.restoreSession();
        // the order here is pretty critical.  First we have to setPatient to populate the initial patient
        // then we populate the patient's encounter lists with setPatientEncounter
        // and finally we set the currently open / selected encounter w/ setEncounter
        window.top.left_nav.setPatient(encounterData.patient.fullName,encounterData.patient.pid,encounterData.patient.pubpid,'',encounterData.patient.dob_str);
        /**
         * If we've created a new encounter than we need to populate the encounter list array for the top navigation
         */
        window.top.window.parent.left_nav.setPatientEncounter(encounterData.encounterList.ids, encounterData.encounterList.dates, encounterData.encounterList.categories);
        window.top.left_nav.setEncounter(encounterData.selectedEncounter.dateStr, encounterData.selectedEncounter.id, "");
        window.top.RTop.location = '../../patient_file/encounter/encounter_top.php?set_pid=' + encodeURIComponent(pid)
            + '&set_encounter=' + encodeURIComponent(encounterData.selectedEncounter.id) + '&launch_telehealth=1';
        if (window.top.comlink && window.top.comlink.telehealth && window.top.comlink.telehealth.launchProviderVideoMessage) {
            window.top.comlink.telehealth.launchProviderVideoMessage({
                pc_eid: pc_eid
            });
        } else {
            console.error("launchProviderVideoMessage was not found in top window object");
            alert(translations.SESSION_LAUNCH_FAILED);
        }
    }

    function init()
    {
        // now we have the dom we can add our event listeners to everything
        var telehealthNodes = document.querySelectorAll(".event_telehealth");
        if (telehealthNodes && telehealthNodes.length)
        {
            var count = telehealthNodes.length;
            for (let i = 0; i < count; i++)
            {
                if (telehealthNodes[i].clientHeight <= 20)
                {
                    // we need to shrink the button even more
                    telehealthNodes[i].classList.add("event_condensed");
                }
                let linkTitle = telehealthNodes[i].querySelector('.link_title');
                var btn = window.document.createElement("i");
                btn.className = "fa fa-video btn-telehealth-calendar-launch btn btn-sm mr-1 ml-1";
                btn.dataset["pid"] = linkTitle.dataset['pid'];
                btn.dataset['eid'] = telehealthNodes[i].dataset['eid'];
                if (telehealthNodes[i].classList.contains('event_telehealth_active'))
                {
                    if (telehealthNodes[i].classList.contains('event_user_different'))
                    {
                        btn.dataset['providerDifferent'] = 1;
                        btn.addEventListener('click', switchProvidersAndSendToTeleHealthSession);
                    }
                    else
                    {
                        btn.dataset['providerDifferent'] = 0;
                        btn.addEventListener('click', sendToEncounter);
                    }
                    btn.classList.add('btn-primary');

                } else if (telehealthNodes[i].classList.contains('event_telehealth_completed')) {
                    btn.addEventListener('click', apptCompleteAlert);
                    btn.classList.add('btn-success');
                    btn.disabled = true;
                } else if (telehealthNodes[i].classList.contains('event_telehealth_unenrolled')) {
                    btn.addEventListener('click', apptTelehealthUnenrolled);
                    btn.classList.add('btn-warning');
                    btn.disabled = true;
                } else {
                    btn.addEventListener('click', apptDisabledAlert);
                    btn.classList.add('btn-dark');
                    btn.disabled = true;
                }
                // img is used on monthly, .fas.fa-user on weekly & daily
                let userPictureIcon = linkTitle.querySelector('.fas.fa-user,img');
                if (userPictureIcon)
                {
                    userPictureIcon.parentNode.replaceChild(btn, userPictureIcon);
                }
                else {
                    // if we can't find the icon we will put the btn here instead.
                    linkTitle.parentNode.appendChild(btn);
                }
            }
        }

        // if we are on the add-edit-event form we can take action on initiating our appointment work
        if (document.querySelector('body.add-edit-event'))
        {
            // we need to hide our launch button if they change the patient...
            var oldSetPatient = window.setpatient || function() {};
            var btn = document.querySelector('.btn-add-edit-appointment-launch-telehealth');
            window.setpatient = function(pid, lname, fname, dob) {

                if (btn)
                {
                    btn.classList.add('d-none');
                }
                oldSetPatient(pid, lname, fname, dob);
            };
            if (btn) {
                // now let's add our event listener here
                btn.addEventListener('click', closeDialogAndLaunchTelehealthSession);
            }
        }


    }

    window.addEventListener('DOMContentLoaded', init);
    // if we are in an outer window we use that one, otherwise we grab the topmost comlink for our translation files
})(window, window.comlink || window.top.comlink || {});
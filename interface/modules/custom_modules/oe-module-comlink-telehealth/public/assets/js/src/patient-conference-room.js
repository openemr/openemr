import {ConferenceRoom} from "./conference-room.js";

export function PatientConferenceRoom(translations,scriptLocation) {
    let patientConferenceRoom = new ConferenceRoom(translations, scriptLocation);
    let parentDestruct = patientConferenceRoom.destruct;
    let checkProviderReadyForPatientInterval = null;
    let providerIsReady = false;

    function checkProviderReadyForPatient()
    {
        let pc_eid = patientConferenceRoom.telehealthSessionData.pc_eid;
        window.top.restoreSession();
        window.fetch(scriptLocation + '?action=patient_appointment_ready&eid=' + encodeURIComponent(pc_eid), {redirect: "manual"})
            .then(result => {
                if (result.ok) {
                    return result.json();
                } else {
                    throw new Error("Failed to get response back from server")
                }
            })
            .then(apptReadyData => {
                if (apptReadyData.session)
                {
                    // provider being ready is just one test, local camera permissions also must be checked
                    providerIsReady = apptReadyData.session.providerReady === "1";

                    // we update the calleeUuid here in case another provider takes over the appointment session of
                    // the patient.  This occurs if a provider is out of town and the patient is currently waiting
                    // for the session to start.

                    if (apptReadyData.session.participantList) {
                        patientConferenceRoom.callerSettings.participantList = apptReadyData.session.participantList;
                        let provider = patientConferenceRoom.callerSettings.participantList.find(p => p.role == 'provider');
                        if (!provider) {
                            throw new Error("No provider in participant list.  Provider is not ready and cannot continue");
                        }
                        patientConferenceRoom.callerSettings.calleeUuid = provider.uuid;
                        patientConferenceRoom.setCurrentCallerFocusId(provider.uuid);
                    }

                    patientConferenceRoom.toggleJoinButton(patientConferenceRoom.shouldEnableJoinButton());
                }
            })
            .catch(error => {
                let errorMessage = document.querySelector('.waiting-room-server-communication');
                if (errorMessage) {
                    errorMessage.classList.remove(('d-none'));
                }
                console.error(error);
            });
    }


    patientConferenceRoom.getFullConferenceVideoBarSettings = function()
    {
        let settings = patientConferenceRoom.getDefaultVideoBarSettings();
        settings.hangupCallback = patientConferenceRoom.handleCallHangup.bind(patientConferenceRoom);
        settings.expand = false;
        settings.notes = false; // patient doesn't get notes.
        settings.configure = false; // patient doesn't get configure at least for now.
        settings.hangup = true;
        patientConferenceRoom.addSettingsForScreenshare(settings);
        return settings;
    };

    patientConferenceRoom.startConferenceRoom = function()
    {
        patientConferenceRoom.stopProviderReadyCheck();
        patientConferenceRoom.startProviderConferenceRoom(); // not sure there is much difference here

        // we need to make a call to all the other participants...
        let participantList = patientConferenceRoom.getRemoteParticipantList();
        if (participantList) {
            // if the participant is in the room, let's make a call to that user.
            participantList.forEach(pl => {
                if (pl.inRoom == 'Y') {
                    if (pl.role == 'provider') {
                        // TODO: here is where we say we want this person to be the focus screen.
                    }
                    patientConferenceRoom.makeCall(pl.uuid);
                }
            });
        }
        // patientConferenceRoom.makeCall(patientConferenceRoom.callerSettings.calleeUuid);

        // patientConferenceRoom.makeScreenshareCall(patientConferenceRoom.callerSettings.calleeUuid);
    };

    patientConferenceRoom.canReceiveCall = function(call) {
        let callerId = call.getRemotePartyId();
        console.log("Received call ", call);
        // only allow calls from one of the participants allowed in the room...
        let canCall = patientConferenceRoom.isAuthorizedParticipant(callerId);
        return Promise.resolve({call: call, canCall: canCall});
    };

    patientConferenceRoom.handleCallEndedEvent = function(call)
    {
        let detachedCallRemoteUserId = null;
        // if the user data is allocated then this is an existing call
        if (call.getUserData() != null) {

            /**
             *
             * @type {CallerSlot|null}
             */
            let callerSlot = call.getUserData();
            detachedCallRemoteUserId = callerSlot.getRemotePartyId();
            patientConferenceRoom.removeCallFromConference(call);
        }

        // we only fall back to the waiting room if we aren't in the middle of a session destruction.
        if (patientConferenceRoom.inSession && !patientConferenceRoom.hasProviderParticipant()) {

            // we shouldn't ever have the case where there are no participants but the provider is still here...
            // for safety reasons though we want to put this in
            alert(translations.HOST_LEFT);
            // for patient conference if the provider leaves the call we need to send them back to the waiting room
            patientConferenceRoom.replaceConferenceRoomWithWaitingRoom();
            // cancel our session update sequence that happens during the conference room.
            patientConferenceRoom.cancelUpdateConferenceRoomSession();
        }
    };

    patientConferenceRoom.hasProviderParticipant = function() {
        // TODO: @adunsulag when we switch to 3rd party we can return the provider caller uuid
        return patientConferenceRoom.findCallerSlotWithId(patientConferenceRoom.callerSettings.calleeUuid) !== undefined;
    };

    patientConferenceRoom.replaceConferenceRoomWithWaitingRoom = function()
    {
        let telehealthSessionData = patientConferenceRoom.telehealthSessionData;
        let modalDialog = patientConferenceRoom.waitingRoomModal;
        var container = document.getElementById('telehealth-container');
        var body = container.querySelector('.modal-body');
        var video = document.getElementById('local-video');

        patientConferenceRoom.videoBar.destruct();
        body.innerHTML = patientConferenceRoom.waitingRoomTemplate;
        var replaceVideo = document.getElementById('local-video');
        if (replaceVideo) {
            replaceVideo.parentNode.replaceChild(video, replaceVideo);
        } else {
            console.error("Failed to find child video to replace");
            return;
        }
        providerIsReady = false;
        patientConferenceRoom.initWaitingRoomEvents(container);
        patientConferenceRoom.startProviderReadyCheck();
    };

    patientConferenceRoom.handleCallHangup = function()
    {
        if (patientConferenceRoom.room == patientConferenceRoom.ROOM_TYPE_WAITING)
        {
            patientConferenceRoom.sessionClose();
        } else {
            // since we are already tied to the hidden event we can just hide the modal and it will destroy everything
            // for the patient side of things.
            if (confirm(translations.CONFIRM_SESSION_CLOSE)) {
                patientConferenceRoom.sessionClose();
            }
        }
    };

    patientConferenceRoom.setWaitingRoomModal = function(waitingRoomModal)
    {
        patientConferenceRoom.waitingRoomModal = waitingRoomModal;
    };

    patientConferenceRoom.setupWaitingRoom = function()
    {
        // do the provider work and let's enable our join button only if the provider is ready
        patientConferenceRoom.setupProviderWaitingRoom();
        patientConferenceRoom.startProviderReadyCheck();
    };
    patientConferenceRoom.destruct = function()
    {
        patientConferenceRoom.stopProviderReadyCheck();
        // TODO: look at merging the two dialogs from patient versus provider
        if (window.dlgclose) {
            window.dlgclose();
        }
        parentDestruct.bind(patientConferenceRoom)();
    };
    patientConferenceRoom.stopProviderReadyCheck = function()
    {
        if (checkProviderReadyForPatientInterval) {
            clearInterval(checkProviderReadyForPatientInterval);
            checkProviderReadyForPatientInterval = null;
        }
    };
    patientConferenceRoom.startProviderReadyCheck = function()
    {
        checkProviderReadyForPatientInterval = setInterval(checkProviderReadyForPatient, 2000);
    };
    patientConferenceRoom.shouldEnableJoinButton = function()
    {
        return providerIsReady && patientConferenceRoom.hasLocalPermissionsEnabled();
    };
    return patientConferenceRoom;
}
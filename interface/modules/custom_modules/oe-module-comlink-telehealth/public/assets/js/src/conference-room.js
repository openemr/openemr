import {ConfirmSessionCloseDialog} from "./confirm-session-close-dialog.js";
import {VideoBar} from "./video-bar.js";
import {CallerSlot} from "./caller-slot.js";
import {TelehealthBridge} from "./telehealth-bridge.js";
import {PresentationScreen} from "./presentation-screen";
import * as cvb from "./cvb.min.js";

// TODO: @adunsulag convert this to class nomenclature
export function ConferenceRoom(translations, scriptLocation)
{
    let conf = this;

    this.waitingRoomTemplate = null;
    this.conferenceRoomTemplate = null;
    this.callerSettings = null;
    this.telehealthSessionData = null;
    this.videoBar = null;
    this.ROOM_TYPE_WAITING = 'waiting';
    this.ROOM_TYPE_CONFERENCE = 'conference';

    this.buttonSettings = {
        microphoneEnabled: true
        ,cameraEnabled: true
        ,screensharingEnabled: false
    };

    /**
     * Milliseconds to update the session information
     * @type {number}
     */
    this.sessionUpdatePollingTime = 5000;

    /**
     * What type of room this conference room is
     * @see ROOM_TYPE_WAITING
     * @see ROOM_TYPE_CONFERENCE
     * @type string
     */
    this.room = null;
    // TODO: @adunsulag rename this variable to be roomModal
    this.waitingRoomModal = null;

    /**
     * Are we currently in the conference room session
     * @type {boolean}
     */
    this.inSession = false;

    /**
     * Is the conference room currently  minimized
     * @type {boolean}
     */
    this.isMinimized = false;

    /**
     * Tracking interval for the conference room session polling
     * @type {number}
     */
    this.sessionUpdateInterval = null;

    this.__hasLocalPermisions = false;
    /** @private */
    this.__bridge = null;

    /** @private */
    this.__localVideoElement = null;

    /**
     * @type CallerSlot[]
     * @private
     * */
    this.__slots = [];

    /**
     *
     * @type CallerSlot
     * @private
     */
    this.__localCaller = null;

    /**
     *
     * @type CallerSlot
     * @private
     */
    this.__remoteCaller = null;

    this.__remoteCallSlot = null;

    this.__isShutdown = true;

    this.__callerIdx = 0;

    // let's allow for our max slots for now
    this.__maxSlots = 3;

    /**
     *
     * @type PresentationScreen
     * @private
     */
    this.__presentationScreen = null;

    /**
     *
     * @type cvb.VideoCall
     * @private
     */
    this.__localScreenshareCall = null;

    this.allocateSlot = function(call, stream) {
        // as we can get multiple events we only want to allocate on the call if we don't have any stream data setup.
        if (!call.getUserData()) {
            // in the event of a network hiccup we go through and allocate the slot
            let needNewSlot = true;
            for (var i = 0; i < this.__slots.length; i++) {
                if (this.__slots[i].isAvailableForCall(call)) {
                    this.__slots[i].attach(call, stream);
                    needNewSlot = false;
                    break;
                }
            }
            if (needNewSlot && this.__slots.length <= this.__maxSlots) {
                let newCaller = new CallerSlot('participant-list-container', this.__callerIdx++);
                this.__slots.push(newCaller);
                newCaller.attach(call, stream);
                if (this.__slots.length <= 2) {
                    this.__presentationScreen.attach(newCaller);
                    // TODO: @adunsulag fix this when we work with 3 callers
                    newCaller.hide();
                }
            }
            this.updateParticipantDisplays();
        }
    };

    this.updateParticipantDisplays = function() {
        // based on the mode of the conference room we will update the presentation screen and the participant list
        let hasPresentationScreen = false;
        for (var i = 0; i < this.__slots.length; i++) {
            if (this.__slots[i].getRemotePartyId() == this.getCurrentCallerFocusId()) {
                hasPresentationScreen = true;
                this.__presentationScreen.attach(this.__slots[i]);
                this.__slots[i].hide(); // hide the speaker from the participant list
            } else {
                // make sure we are showing all of our participants
                this.__slots[i].show();
            }
        }
        // if we get here we are going to just detatch
        if (!hasPresentationScreen) {
            this.__presentationScreen.detach();
        }

        if (this.__slots.length) {
            this.buttonSettings.screensharingEnabled = true;
        } else {
            this.buttonSettings.screensharingEnabled = false;
        }
        this.resetConferenceVideoBar();
    };

    this.isAuthorizedParticipant = function(callerId) {
        let participantList = this.callerSettings.participantList || [];
        let participant = participantList.find((p => p.uuid == callerId));
        return participant !== undefined;
    };

    this.getRemoteParticipantList = function() {
        let participantList = this.callerSettings.participantList || [];
        let participant = participantList.find((p => p.uuid == callerId));
        return participantList.filter(p => p.username !== conf.callerSettings.callerUuid);
    };

    /**
     * Returns the caller slot with the caller id that is passed in.
     * @param id
     * @returns {CallerSlot|undefined}
     */
    this.findCallerSlotWithId = function(id) {
        return this.__slots.find(s => s.getRemotePartyId() === id);
    };

    this.getCurrentCallerFocusId = function() {
        // if we use audio monitoring we can switch, or if the user pins a speaker we can this this
        // however, for now we focus on the callee for now
        return this.callerSettings.calleeUuid;
    };

    this.freeSlot = function(slot) {
        if (slot !== null && !slot.isAvailable() == null)
        {
            console.error("freeSlot called with null slot ", {slot: slot});
            return;
        }
        slot.__call.stop();
        slot.detach();
        // trying to leave code open in case we add more callers... but we add this in to clear our variables
        if (slot == conf.__remoteCallSlot)
        {
            conf.__remoteCallSlot = null;
        }
    };

    this.restartMediaStream = function() {
        if (conf.__localVideoElement.srcObject) {
            conf.__bridge.closeLocalMediaStream();
        }

        return conf.__bridge.getLocalMediaStream()
            .then(stream => conf.handleLocalMediaStreamStarted(stream))
            .catch(error => conf.handleLocalMediaStreamFailed(error));
    };
    this.handleLocalMediaStreamStarted = function(stream) {
        conf.__localVideoElement.srcObject = stream;
        conf.__localVideoElement.play();
        conf.__hasLocalPermisions = true;
        conf.toggleJoinButton(conf.shouldEnableJoinButton());
        conf.togglePermissionBox(false);
        return conf.__hasLocalPermisions;
    };
    this.handleLocalMediaStreamFailed = function(error) {
        console.error(error);
        conf.__hasLocalPermisions = false;
        conf.toggleJoinButton(conf.shouldEnableJoinButton());
        conf.togglePermissionBox(true);
        return conf.__hasLocalPermisions;
    };

    this.startBridge = function() {
        conf.__localVideoElement = document.getElementById('local-video');
        conf.__localVideoElement.muted = true;

        conf.__bridge = new TelehealthBridge(conf.callerSettings.callerUuid, conf.callerSettings.apiKey
            , conf.callerSettings.serviceUrl);

        let bridgeHandlers = {
            onincomingcall: conf.handleIncomingCall.bind(conf)
            ,onbridgeactive: (bridge) => {
                conf.restartMediaStream();
            }
            ,onbridgeinactive: (bridge) => {
                if (conf.__localVideoElement && conf.__localVideoElement.stop) {
                    conf.__localVideoElement.stop();
                    conf.__localVideoElement.srcObject = null;
                }
            }
            ,onbridgefailure: (bridge) => {
                if (conf.__localVideoElement && conf.__localVideoElement.stop) {
                    conf.__localVideoElement.stop();
                }
                conf.__localVideoElement.srcObject = null;
                alert(translations.BRIDGE_FAILED);
            }
        };

        conf.__bridge.startBridge(bridgeHandlers);
        conf.__isShutdown = false;
    };

    conf.shutdown = function()
    {
        if (conf.__isShutdown)
        {
            return;
        }

        conf.__bridge.shutdown();
        conf.__localVideoElement.srcObject = null;
        console.log("Shutting down " +  conf.callerSettings.callerUuid)
        conf.__isShutdown = true;
    };

    this.setCallHandlers = function(call) {
        // Callback: streams updated
        //
        call.oncallstarted = (call) => {
            conf.handleCallStartedEvent(call);
        };
        // Once we have the remote stream we'll allocate a video slot.
        call.onstreamupdated =
            (call, stream) => {
                console.log("onstreamupdated " + conf.callerSettings.calleeUuid);
                conf.allocateSlot(call, stream);
            };

        // Callback: call ended
        //
        // When the call ends we free the video slot.
        call.oncallended = (call) => {
            conf.handleCallEndedEvent(call);
        };
    };

    /**
     * Used to make a video call to the bridge
     * @param calleeId
     */
    this.makeCall = function(calleeId) {
        const call = conf.__bridge.createVideoCall(calleeId);

        conf.setCallHandlers(call);

        // Callback: outbound call rejected
        //
        // If the call is rejected we do nothing. We know that in that case the
        // stream will not have been created and, consequently, we will not have
        // created the video element.
        call.oncallrejected = (call) => {
            console.log(call);
            console.log("oncallrejected " + conf.callerSettings.calleeUuid);
            // if the call is rejected the provider dropped off before we connected so we go back to the conference
            // room
            conf.handleCallEndedEvent(call);
        };

        // Finally, start the call
        call.start().catch((e) => {
            alert(translations.CALL_CONNECT_FAILED);
            console.log("call exception " + conf.callerSettings.calleeUuid);
            console.error(e);
            conf.handleCallEndedEvent(call);
        });
        return call;
    };

    this.makeScreenshareCall = function(calleeId) {
        const call = conf.__bridge.createScreenSharingCall(calleeId);
        // conf.setCallHandlers(call);
        // for now we duplicate this as we play around to see how this works.
        call.start().catch((e) => {
            alert(translations.CALL_CONNECT_FAILED);
            console.log("call exception " + conf.callerSettings.calleeUuid);
            console.error(e);
            // conf.handleCallEndedEvent(call);
            this.__localScreenshareCall = null;
        });
        call.oncallended = (call) => {
            this.__localScreenshareCall = null;
        };
        this.__localScreenshareCall = call;
    };

    this.enableMicrophone = function(flag) {
        conf.__bridge.enableMicrophone(flag);
    };

    this.enableCamera = function(flag) {
        conf.__bridge.enableCamera(flag);
    };

    this.hasLocalPermissionsEnabled = function()
    {
        return conf.__hasLocalPermisions;
    };


    /**
     * Checks to make sure that the user with the given calleeId is allowed to talk to the current conference room caller
     * @param call The call that we are going to verify
     * @returns {Promise<boolean>}
     */
    this.canReceiveCall = function(call)
    {
        let callerId = call.getRemotePartyId();
        // TODO: @adunsulag check to make sure we don't need to hit the server... we already have the calleeUuid which we got
        // from the server... we can double check again by hitting the server and verifying the session has started and accept
        // the call, but that seems pretty paranoid to double check that...
        if (this.isAuthorizedParticipant(callerId))
        {
            return Promise.resolve({call: call, canCall: true});
        }
        else
        {
            return Promise.resolve({call: call, canCall: false});
        }
    };

    this.getRemoteScriptLocation = function()
    {
        return scriptLocation;
    };

    this.getTelehealthLaunchData = function(data)
    {
        var scriptLocation = this.getRemoteScriptLocation() + '?action=get_telehealth_launch_data&eid=' + encodeURIComponent(data.pc_eid);
        window.top.restoreSession();
        return window.fetch(scriptLocation, {redirect: "manual"});
    };

    this.setupProviderWaitingRoom = function()
    {
        let modal = this.createModalWithContent(conf.waitingRoomTemplate);
        // // now we will attach all of our event listeners here onto the document content.
        let container = document.getElementById('telehealth-container');
        conf.initWaitingRoomEvents(container);
        conf.waitingRoomModal = modal;
        conf.waitingRoomModal.show();
    };

    this.setupWaitingRoom = function()
    {
        conf.setupProviderWaitingRoom();
    };

    this.handleIncomingCall = function(call)
    {

        // we will hit the server to verify that the caller can actually make this call
        this.canReceiveCall(call)
            .then((result) => {
                if (!result.canCall) {
                    console.log("Call from " + result.call.getRemotePartyId() + " not authorized by server for "  + conf.callerSettings.callerUuid);
                    result.call.reject();
                    return;
                }

                conf.setCallHandlers(result.call);
                result.call.accept();
                console.log("Call accepted from " + result.call.getRemotePartyId() + " for "  + conf.callerSettings.callerUuid);
                // NOTE that result.call.accept does NOT fire the oncallstarted event for the call stream... so we have to
                // toggle our remote video here instead.
                conf.toggleRemoteVideo(true);
            })
            .catch(error => {
                call.reject();
            });
    };

    this.init = function(data)
    {
        if (!data.pc_eid)
        {
            alert(translations.SESSION_LAUNCH_FAILED);
            return;
        }

        conf.telehealthSessionData = data;
        // we grab everything up front as anything dealing with video playback needs to be triggered by a button on iOS devices
        // so we have to make sure we grab everything here.

        // we could also make sure we create users when we update... that would resolve the issue as well.
        let getLaunchData = conf.getTelehealthLaunchData(data);

        getLaunchData
            .then(function(result) {
                if (!(result.ok && result.status == 200)) {
                    throw new Error("Failed to fetch data");
                }

                return result.json();
            })
            .then(launchData => {
                conf.inSession = true;
                conf.callerSettings = launchData.callerSettings;
                conf.waitingRoomTemplate = launchData.waitingRoom;
                conf.conferenceRoomTemplate = launchData.conferenceRoom;
                conf.setupWaitingRoom();
                conf.startBridge();
                conf.room = conf.ROOM_TYPE_WAITING;
            })
            .catch(function(error) {
                console.error(error);
                // null out our values if we never started our session.
                if (!conf.__bridge) {
                    alert(translations.SESSION_LAUNCH_FAILED);
                    conf.destruct(); // shut things down if we don't have a valid session.
                }
            });
    };

    this.destruct = function()
    {
        // TODO: @adunsulag remove this debug
        console.log("conference-room.destruct() called");
        conf.inSession = false;
        conf.waitingRoomTemplate = null;
        conf.conferenceRoomTemplate = null;
        if (conf.videoBar) {
            conf.videoBar.destruct();
            conf.videoBar = null;
        }
        if (conf.room == conf.ROOM_TYPE_WAITING)
        {
            // TODO: do any cleanup needed here
        }
        else if (conf.room == conf.ROOM_TYPE_CONFERENCE)
        {
            // TODO: do any cleanup needed here.
        }
        if (conf.sessionUpdateInterval !== null)
        {
            clearInterval(conf.sessionUpdateInterval);
            conf.sessionUpdateInterval = null;
        }
        let container = document.getElementById('telehealth-container');

        // if (conf.__localScreenshareCall && conf.__localScreenshareCall.stop) {
        //     // stop the screensharing s
        //     // TODO: @adunsulag if a later version of the api fixes this, let's clean this up.
        //     conf.__localScreenshareCall.stop();
        // }

        if (conf.__bridge && conf.__bridge.shutdown)
        {
            // catch any problems from the library so we can still clean up.
            try {
                conf.__bridge.shutdown();
            }
            catch (error)
            {
                console.error(error);
            }
        }
        if (container && container.parentNode)
        {
            container.parentNode.removeChild(container);
        }

        if (conf.isMinimized)
        {
            // clean up minimized elements now too
            let minimizedContainer = document.getElementById('minimized-telehealth-video');
            if (minimizedContainer && minimizedContainer.parentNode)
            {
                minimizedContainer.parentNode.removeChild(minimizedContainer);
            }
            conf.isMinimized = false;
        }
        conf.callerSettings = null;
        conf.telehealthSessionData = null;
    };

    this.initModalEvents = function(container)
    {
        var elements = document.getElementsByClassName('btn-telehealth-provider-close');
        for (var i = 0; i < elements.length; i++)
        {
            elements[i].addEventListener('click', conf.handleCallHangup);
        }
    };

    this.initWaitingRoomEvents = function(container)
    {
        conf.videoBar = new VideoBar(container, conf.getWaitingRoomVideoBarSettings());
        conf.toggleJoinButton(conf.shouldEnableJoinButton());
    };

    this.togglePermissionBox = function(enabled)
    {
        let box = document.querySelector('#telehealth-container .permissions-box');
        let boxRestartBtn = document.querySelector('#telehealth-container .permissions-box .restart-media-btn');
        if (box && boxRestartBtn)
        {
            if (enabled) {
                boxRestartBtn.addEventListener('click', conf.recaptureLocalMedia);
                box.classList.remove('d-none');
            } else {
                box.classList.add('d-none');
                boxRestartBtn.removeEventListener('click', conf.recaptureLocalMedia);
            }
        } else {
            console.error("Could not find permissions box dom nodes");
        }
    };

    this.recaptureLocalMedia = function()
    {
        if (conf.__bridge && !conf.__bridge.isShutdown)
        {
            conf.__bridge.shutdown();
        }
        conf.startBridge(); // start the bridge over again since we couldn't capture the local media the first time.
    };

    this.shouldEnableJoinButton = function()
    {
        return conf.hasLocalPermissionsEnabled();
    };

    this.toggleJoinButton = function(enabled)
    {
        let btnJoin = document.querySelectorAll('.btn-comlink-conference-join');
        if (btnJoin && btnJoin.length) {
            for (let i = 0; i < btnJoin.length; i++) {
                if (enabled) {
                    btnJoin[i].addEventListener('click', conf.startConferenceRoom);
                    btnJoin[i].classList.remove('disabled');
                    btnJoin[i].disabled = false;
                } else {
                    btnJoin[i].removeEventListener('click', conf.startConferenceRoom);
                    btnJoin[i].classList.add('disabled');
                    btnJoin[i].disabled = true;
                }
            }
        }
    };

    this.sessionClose = function()
    {
        let conf = this;
        if (conf.isMinimized)
        {
            conf.destruct();
        }
        else {
            // have to use jquery to grab the jquery event
            jQuery("#telehealth-container").on("hidden.bs.modal", function () {
                jQuery("#telehealth-container").off("hidden.bs.modal");
                conf.destruct()
            });
            conf.waitingRoomModal.hide();
        }
    };

    this.confirmSessionClose = function()
    {
        if (conf.room == conf.ROOM_TYPE_WAITING)
        {
            conf.sessionClose();
        }
        else {
            let dialog = new ConfirmSessionCloseDialog(conf.telehealthSessionData.pc_eid, scriptLocation, function() {
                conf.sessionClose();
            });
            dialog.show();
        }
    };

    this.shutdownProviderWaitingRoom =  function()
    {
        let container = document.getElementById('telehealth-container');
        if (conf.__bridge && conf.__bridge.shutdown)
        {
            // catch any problems from the library so we can still clean up.
            try {
                conf.__bridge.shutdown();
            }
            catch (error)
            {
                console.error(error);
            }
        }
        if (container && container.parentNode)
        {
            container.parentNode.removeChild(container);
        }
        conf.callerSettings = null;
        conf.telehealthSessionData = null;
    };

    this.createModalWithContent = function(content)
    {
        let bootstrapModalTemplate = window.document.createElement('div');
        // we use min-height 90vh until we get the bootstrap full screen modal in bootstrap 5
        // TODO: @adunsulag now that both patient & portal are using the same dialog we can probably move this server side
        // into the waiting room template.
        bootstrapModalTemplate.innerHTML = `<div class="modal fade" id="telehealth-container" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog mw-100 ml-2 mr-2">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">` + jsText(translations.TELEHEALTH_MODAL_TITLE) + `</h5>
                    <button type="button" class="close btn-telehealth-provider-close" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body d-flex">
                  ${content}
                  </div>
                </div>
              </div>
            </div>`;
        window.document.body.appendChild(bootstrapModalTemplate.firstElementChild);
        var container = document.getElementById('telehealth-container');
        conf.waitingRoomModal = new bootstrap.Modal(container, {keyboard: false, focus: true, backdrop: 'static'});
        conf.initModalEvents(container);
        return conf.waitingRoomModal;
    };

    this.startConferenceRoom = function()
    {
        conf.startProviderConferenceRoom();
    };

    this.startProviderConferenceRoom = function()
    {
        let container = document.getElementById('telehealth-container');
        // now grab our video container in our modal
        let video = document.getElementById("local-video");

        conf.videoBar.destruct();

        // now we are going to replace the modal content
        let modalBody = container.querySelector('.modal-body');
        // clear out the modal body
        do {
            modalBody.removeChild(modalBody.firstChild);
        } while (modalBody.childNodes.length);
        modalBody.innerHTML = conf.conferenceRoomTemplate;

        // now let's replace our template video container with our original video container from the waiting room.
        let conferenceVideo = document.getElementById('local-video');
        if (conferenceVideo && conferenceVideo.parentNode) {
            conferenceVideo.parentNode.replaceChild(video, conferenceVideo);
        }
        let conferenceVideoBar = modalBody.querySelector(".telehealth-button-bar");
        conf.videoBar = new VideoBar(conferenceVideoBar, conf.getFullConferenceVideoBarSettings());
        conf.inConferenceRoom = true;
        conf.room = conf.ROOM_TYPE_CONFERENCE;

        this.sessionUpdateInterval = setInterval(conf.updateConferenceRoomSession.bind(conf), conf.sessionUpdatePollingTime);
        this.__presentationScreen = new PresentationScreen('presentation-screen');
        conf.updateConferenceRoomSession();
    };

    this.updateConferenceRoomSession = function() {
        let appt = conf.callerSettings.appointment || {};
        let eid = appt.eid || {};
        // TODO: if we ever need to take action on the session update we would do that here.
        // TODO: @adunsulag change eid here to be pc_eid
        window.top.restoreSession();
        window.fetch(conf.getRemoteScriptLocation() + '?action=conference_session_update&eid=' + encodeURIComponent(eid), {redirect: "manual"})
            .then((request) => {
                if (request.ok) {
                    return request.json();
                } else {
                    throw new Error("Failed to update session");
                }
            })
            .catch(error => console.error("Conference session update ", error));
    };

    this.cancelUpdateConferenceRoomSession = function()
    {
        if (conf.sessionUpdateInterval)
        {
            window.clearInterval(conf.sessionUpdateInterval);
        }
    };

    this.getDefaultVideoBarSettings = function()
    {
        var noop = function() {};

        return {
            notes: false
            ,notesCallback: noop
            ,microphone: conf.buttonSettings.microphoneEnabled
            ,microphoneCallback: conf.toggleMicrophone.bind(conf)
            ,video: conf.buttonSettings.cameraEnabled
            ,videoCallback: conf.toggleVideo.bind(conf)
            ,expand: false
            ,expandCallback: noop
            ,hangup: false
            ,hangupCallback: noop
            ,screenshare: conf.buttonSettings.screensharingEnabled
            ,screenshareCallback: conf.toggleScreenSharing.bind(conf)
        };
    };

    this.getWaitingRoomVideoBarSettings = function()
    {
        return conf.getDefaultVideoBarSettings();
    };

    this.getMinimizedConferenceVideoBarSettings = function()
    {
        let settings = conf.getDefaultVideoBarSettings();
        settings.expandCallback = conf.maximizeProviderConferenceCall.bind(conf);
        settings.hangupCallback = conf.handleCallHangup.bind(conf);
        settings.expand = true;
        settings.notes = false;
        settings.hangup = true;
        settings.screenshare = false;
        return settings;
    };

    this.addSettingsForScreenshare = function(settings) {
        if (this.__slots.length) {
            settings.screenshare = true;
        } else {
            settings.screenshare = false;
        }
    };

    this.getFullConferenceVideoBarSettings = function()
    {
        let settings = conf.getDefaultVideoBarSettings();
        settings.hangupCallback = conf.handleCallHangup.bind(conf);
        settings.notesCallback = conf.minimizeProviderConferenceCall.bind(conf);
        settings.expand = false;
        settings.notes = true;
        settings.hangup = true;
        conf.addSettingsForScreenshare(settings);
        return settings;
    };

    this.handleCallStartedEvent = function(call)
    {
        conf.toggleRemoteVideo(true);
    };

    this.resetConferenceVideoBar = function() {
        if (conf.videoBar) {
            conf.videoBar.destruct();
        }
        var container = document.getElementById('telehealth-container');
        let conferenceVideoBar = container.querySelector(".telehealth-button-bar");
        conf.videoBar = new VideoBar(conferenceVideoBar, conf.getFullConferenceVideoBarSettings());
    };

    this.removeCallFromConference = function(call) {
        if (call.getUserData() != null) {
            /**
             *
             * @type {CallerSlot|null}
             */
            let callerSlot = call.getUserData();
            if (call.isScreenSharing()) {
                callerSlot.detachScreenshare();
            }
            else {
                this.removeSlotForCall(call);
            }
            this.updateParticipantDisplays();
        }
    };

    // todo: look at removing this
    this.removeSlotForCall = function(call) {
        // if the user data is allocated then this is an existing call
        if (call.getUserData() != null) {
            let callerSlot = call.getUserData();
            callerSlot.destruct(); // remove everything
            // cleanup the slots
            this.__slots = this.__slots.filter(s => s !== callerSlot);
        }
    };

    this.hasRemoteParticipants = function() {
        let hasParticipants = this.__slots.length > 0;
        return hasParticipants;
    }

    // for the provider conference we end the call and we then show our waiting message
    this.handleCallEndedEvent = function(call)
    {
        conf.removeCallFromConference(call);
        // if we don't have any more slots available
        if (!conf.hasRemoteParticipants()) {
            if (!conf.__isShutdown) {
                conf.toggleRemoteVideo(false);
            }
        }
    };

    this.handleCallHangup = function()
    {
        // if we hangup the call we maximize the window since the confirm dialog is embedded inside the
        // main window.
        if (conf.isMinimized) {
            conf.maximizeProviderConferenceCall({});
        }
        conf.confirmSessionClose();
    };

    this.minimizeProviderConferenceCall = function()
    {
        // grab every dom node in the waiting room that is not the patient video
        // grab the video and shrink it to bottom left window
        // shrink container to be the size of the video
        var container = document.getElementById('telehealth-container');

        var template = document.createElement('div');
        template.id = "minimized-telehealth-video";
        template.className = "col-lg-2 col-md-3 col-sm-4 col-6 drag-action";

        window.document.body.appendChild(template);
        template.appendChild(this.__presentationScreen.getVideoElement());

        var oldButtonBar = container.querySelector('.telehealth-button-bar');
        var clonedButtonBar = oldButtonBar.cloneNode(true);

        template.appendChild(clonedButtonBar);

        // now destruct the old button
        conf.videoBar.destruct();
        conf.videoBar = new VideoBar(clonedButtonBar, conf.getMinimizedConferenceVideoBarSettings());

        conf.waitingRoomModal.hide();

        // now make the video container draggable
        if (window.initDragResize)
        {
            // let's initialize our drag action here.
            window.initDragResize();
        }

        conf.isMinimized = true;
    };

    this.maximizeProviderConferenceCall = function(evt)
    {
        // remove the event listener
        var remoteVideoContainer = document.querySelector('.remote-video-container');
        var remoteVideo = this.__presentationScreen.getVideoElement();

        // now let's move the video and cleanup the old container here
        if (remoteVideo && remoteVideoContainer) {

            var oldContainer = remoteVideo.parentNode;
            var oldButtonBar = oldContainer.querySelector('.telehealth-button-bar');
            if (oldButtonBar) {
                conf.videoBar.destruct();
                oldButtonBar.parentNode.removeChild(oldButtonBar);
            }


            if (remoteVideoContainer) {
                var newButtonBar = remoteVideoContainer.querySelector('.telehealth-button-bar');
                if (newButtonBar) {
                    conf.videoBar = new VideoBar(newButtonBar, conf.getFullConferenceVideoBarSettings());
                } else {
                    console.error("Failed to find #remote-video-container .telehealth-button-bar");
                }
            } else {
                console.error("Failed to find #remote-video-container");
            }
            remoteVideoContainer.prepend(remoteVideo);

            // need to clean up the original minimize container we created here
            if (oldContainer && oldContainer.parentNode)
            {
                oldContainer.parentNode.removeChild(oldContainer);
            }
        } else {
            console.error("Failed to find remote video or remote video container");
        }

        // everything's moved we can now display the larger video modal.
        if (conf.waitingRoomModal) {
            conf.waitingRoomModal.show();
            conf.isMinimized = false;
        } else {
            console.error("Failed to find waitingRoomModal");
        }
    };


    this.toggleMicrophone = function(event)
    {
        let node = event.target;
        if (!node.classList.contains('fa'))
        {
            node = node.querySelector('.fa');
        }
        let toggle = !conf.buttonSettings.microphoneEnabled;
        conf.buttonSettings.microphoneEnabled = toggle;
        node.dataset.enabled = toggle;
        if (conf.__bridge && conf.__bridge.enableMicrophone) {
            conf.__bridge.enableMicrophone(toggle);
        } else {
            console.error("__bridge is not initalized and cannot toggle microphone");
        }
        toggleClass(node, toggle, 'fa-microphone','fa-microphone-slash');
    };

    this.toggleVideo = function(event)
    {
        let node = event.target;
        if (!node.classList.contains('fa'))
        {
            node = node.querySelector('.fa');
        }
        let toggle = !conf.buttonSettings.cameraEnabled;
        conf.buttonSettings.cameraEnabled = toggle;
        // TODO: @adunsulag remove this reliance on the node.dataset here.
        node.dataset.enabled = toggle;
        if (conf.__bridge && conf.__bridge.enableCamera) {
            conf.__bridge.enableCamera(toggle);
        } else {
            console.error("app is not initalized and cannot toggle microphone");
        }
        toggleClass(node, toggle, 'fa-video','fa-video-slash');
    };

    this.toggleRemoteVideo = function(display)
    {
        var container = document.getElementById('telehealth-container');
        var waitingContainer = container.querySelector('.waiting-container');
        var remoteVideo = container.querySelector('.remote-video');

        if (display)
        {
            waitingContainer.classList.add('d-none');
            remoteVideo.classList.remove('d-none');
        } else {
            waitingContainer.classList.remove('d-none');
            remoteVideo.classList.add('d-none');
        }
    };

    this.toggleScreenSharing = function(evt) {
        this.makeScreenshareCall(conf.callerSettings.calleeUuid);
    };


    // don't really need any class member variables here so we will let JS hoist this up.
    function toggleClass(node, toggle, onClass, offClass)
    {
        if (toggle) {
            node.classList.add(onClass);
            node.classList.remove(offClass);
        } else {
            node.classList.add(offClass);
            node.classList.remove(onClass);
        }
    }

}
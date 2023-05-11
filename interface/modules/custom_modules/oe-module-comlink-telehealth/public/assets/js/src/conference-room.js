/**
 * Javascript Controller for the entire conference room.  It handles both the waiting room and session conference room
 * controls and interactions.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2023 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

import {ConfirmSessionCloseDialog} from "./confirm-session-close-dialog.js";
import {ConfigureSessionCallDialog} from "./configure-session-call-dialog.js";
import {VideoBar} from "./video-bar.js";
import {CallerSlot} from "./caller-slot.js";
import {TelehealthBridge} from "./telehealth-bridge.js";
import {PresentationScreen} from "./presentation-screen";
import * as cvb from "./cvb.min.js";
import {AddPatientDialog} from "./add-patient-dialog";
import {MinimizedConferenceRoom} from "./minimized-conference-room";
import {LocalCallerSlot} from "./local-caller-slot";

// TODO: @adunsulag convert this to class nomenclature (big project for future)
export function ConferenceRoom(apiCSRFToken, enabledFeatures, translations, scriptLocation)
{
    let conf = this;

    this.apiCSRFToken = apiCSRFToken;
    this.waitingRoomTemplate = null;
    this.conferenceRoomTemplate = null;
    this.callerSettings = null;
    this.features = enabledFeatures; // the features that are enabled from the server.
    this.telehealthSessionData = null;
    this.videoBar = null;
    this.ROOM_TYPE_WAITING = 'waiting';
    this.ROOM_TYPE_CONFERENCE = 'conference';

    // decibel threshold that determines that there is audio activity detected by a remote party
    this.dbThresholdSetting = -45;

    this.buttonSettings = {
        microphoneEnabled: true
        ,cameraEnabled: true
        ,screensharingEnabled: false
    };

    /**
     * The container node of the room
     * @type {HTMLElement}
     */
    this.roomNode = null;

    /**
     * Milliseconds to update the session information
     * @type {number}
     */
    this.sessionUpdatePollingTime = 5000;

    /**
     * Milliseconds to check if we should hide the conference room session controls or not
     * @type {number}
     */
    this.sessionControlsActiveTime = 500;

    /**
     * Milliseconds that a session is determined to be idle (so controls and other stuff can be hidden)
     * @type {number}
     */
    this.sessionControlsIdleTime = 5000;

    /**
     * The date timestamp that the local user was active from a visual control perspective (mouse move, click presentation screen, etc)
     * @type {Date|null}
     */
    this.lastActiveDate = null;

    /**
     * What type of room this conference room is
     * @see ROOM_TYPE_WAITING
     * @see ROOM_TYPE_CONFERENCE
     * @type string
     */
    this.room = null;

    /**
     * DOM Node for the modal that is popped up on the screen holding the waiting room / conference room modal
     * @type HTMLElement
     */
    this.roomModal = null;

    /**
     * Are we currently in the conference room session
     * @type {boolean}
     */
    this.inSession = false;

    /**
     * The minimized conference room handler
     * @type {MinimizedConferenceRoom}
     * @private
     */
    this.__minimizedConferenceRoom = null;

    /**
     * Tracking interval for the conference room session polling
     * @type {number}
     */
    this.sessionUpdateInterval = null;

    /**
     * Tracking interval for the conference room user activity polling
     * @type {number}
     */
    this.sessionControlsActiveInterval = null;

    this.__hasLocalPermisions = false;
    /** @private */
    this.__bridge = null;

    /**
     *
     * @type LocalCallerSlot
     * @private
     */
    this.__localCallSlot = null;

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
     * @type string
     * @private
     */
    this.__focusCallerUuid = null;

    /**
     *
     * @type string
     * @private
     */
    this.__pinnedCallerUuid = null;


    /**
     * Is the conference room currently  minimized
     * @type {boolean}
     */
    this.isMinimized = function() {
        if (this.__minimizedConferenceRoom) {
            return this.__minimizedConferenceRoom.isMinimized();
        }
        return false;
    };


    this.allocateSlot = function(call, stream) {
        // as we can get multiple events we only want to allocate on the call if we don't have any stream data setup.
        if (!call.getUserData()) {
            // in the event of a network hiccup we go through and allocate the slot
            let needNewSlot = true;
            let slotsLength = this.__slots.length;
            for (var i = 0; i < slotsLength; i++) {
                if (this.__slots[i].isAvailableForCall(call)) {
                    this.__slots[i].attach(call, stream, this.getParticipantForCall(call));
                    needNewSlot = false;
                    break;
                }
            }
            if (needNewSlot && slotsLength <= this.__maxSlots) {
                let newCaller = new CallerSlot('participant-list-container', this.__callerIdx++);
                newCaller.addCallerSelectListener(function(callerSlot) {
                    conf.togglePinnedCallerId(callerSlot.getRemotePartyId());
                    conf.updateParticipantDisplays();
                });
                this.__slots.push(newCaller);
                slotsLength++;
                newCaller.attach(call, stream, this.getParticipantForCall(call));
            }
            // now we need to update our focus caller and participant list if we have it
            if (slotsLength == 1) {
                // if we only have one person on the call they need to be the focus of the video
                this.setCurrentCallerFocusId(call.getRemotePartyId());
            }
            this.updateParticipantDisplays();
        }
    };

    this.updateParticipantDisplays = function() {
        if (this.__isShutdown) {
            // nothing to do in the loop so let's just exit
            return;
        }
        // TODO: @adunsulag based on performance we may want an isDirty flag here so we don't update the display
        // unless something has changed

        // based on the mode of the conference room we will update the presentation screen and the participant list
        let hasPresentationScreen = false;
        // if we only have one other participant... or the screen is minimized we want to hide the local speaker.
        let twoWayCall = this.__slots.length <= 1;
        let isMinimized = this.isMinimized();
        // we override the call with the pinned caller, then our currently speaking caller, finally we just have null
        let presentationScreenRemotePartyId = this.__pinnedCallerUuid || this.__focusCallerUuid || null;
        for (var i = 0; i < this.__slots.length; i++) {
            if (this.__slots[i].getRemotePartyId() == presentationScreenRemotePartyId) {
                hasPresentationScreen = true;
                this.__presentationScreen.attach(this.__slots[i]);
                if (twoWayCall && !isMinimized) {
                    // hide the remote caller in a two way call in the participant list ONLY if we are not minimized
                    // as they show up in the presentation screen.
                    this.__slots[i].hide();
                } else {
                    this.__slots[i].show();
                }
                // this condition never executes for now as we don't keep a local CallerSlot
                // if we do, we will leave this in for now
            } else if (isMinimized && this.__slots[i].getRemotePartyId() == this.getLocalPartyId()) {
                this.__slots[i].hide(); // hide the local caller from the participant list when we are minimized
            } else {
                // make sure we are showing everyone, including ourselves if we don't hide the local speaker
                this.__slots[i].show();
            }
        }
        // only hide if we are minimized
        if (this.__localCallSlot) {
            if (isMinimized) {
                this.__localCallSlot.hide();
            } else {
                this.__localCallSlot.show();
            }
        }

        // if we get here we are going to just detatch
        if (!hasPresentationScreen) {
            // check to see if our pinned slot is the local caller
            if (this.__localCallSlot.getRemotePartyId() == presentationScreenRemotePartyId) {
                this.__presentationScreen.attach(this.__localCallSlot);
            } else {
                this.__presentationScreen.detach();
            }
        }

        if (!this.hasRemoteParticipants()) {
            if (!this.__isShutdown) {
                this.toggleRemoteVideo(false);
            }
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
        return participantList.filter(p => p.uuid !== conf.callerSettings.callerUuid);
    };

    this.getLocalParticipant = function() {
        return this.findParticipantForId(conf.callerSettings.callerUuid);
    };

    this.getParticipantForCall = function(call) {
        return this.findParticipantForId(call.getRemotePartyId());
    };

    this.findParticipantForId = function(callerId) {
        let participantList = this.callerSettings.participantList || [];
        let participant = participantList.find((p => p.uuid == callerId));
        return participant;
    };

    this.setParticipantInCallRoomStatus = function(callerId, status) {
        if (this.__isShutdown) {
            // if we are shut down no point in updating the call room status as we are just dealing
            // with the after effects of callback triggers
            return;
        }
        status = status == 'Y' ? 'Y' : 'N';
        // if we have been destroyed and are still processing
        let callerSettings = this.callerSettings || {};
        let participantList = callerSettings.participantList || [];
        let participant = participantList.find((p => p.uuid == callerId));
        if (participant) {
            participant.inRoom = status;
        } else {
            console.error("participant was not found in participant list for callerId " + callerId);
        }
    };

    this.getLocalPartyId = function() {
        return conf.callerSettings.callerUuid;
    };

    /**
     * Returns the caller slot with the caller id that is passed in.
     * @param id
     * @returns {CallerSlot|undefined}
     */
    this.findCallerSlotWithId = function(id) {
        if (id == this.callerSettings.callerUuid) {
            return this.__localCallSlot;
        }

        return this.__slots.find(s => s.getRemotePartyId() === id);
    };

    this.togglePinnedCallerId = function(uuid) {
        if (this.__pinnedCallerUuid) {
            // need to unset it if its the same
            let callerSlot = this.findCallerSlotWithId(this.__pinnedCallerUuid);
            // the slot may have been removed so ok if we don't find it.
            if (callerSlot && callerSlot.isPinned()) {
                callerSlot.setPinnedStatus(false);
            }
        }
        if (uuid != this.__pinnedCallerUuid) {
            let callerSlot = this.findCallerSlotWithId(uuid);
            if (callerSlot) {
                this.__pinnedCallerUuid = uuid;
                callerSlot.setPinnedStatus(true);
                this.setCurrentCallerFocusId(uuid);
            } else {
                console.error("Failed to find pinned caller slot with uuid " + uuid);
            }
        } else {
            this.__pinnedCallerUuid = null;
        }

    };

    this.setCurrentCallerFocusId = function(uuid) {
        this.__focusCallerUuid = uuid;
    };

    this.getCurrentCallerFocusId = function() {
        // if we use audio monitoring we can switch, or if the user pins a speaker we can this this
        // however, for now we focus on the callee for now
        return this.__focusCallerUuid;
    };

    this.restartMediaStream = function() {
        if (conf.__localCallSlot.hasMediaStream()) {
            conf.__bridge.closeLocalMediaStream();
        }

        return conf.__bridge.getLocalMediaStream()
            .then(stream => conf.handleLocalMediaStreamStarted(stream))
            .catch(error => conf.handleLocalMediaStreamFailed(error));
    };
    this.handleLocalMediaStreamStarted = function(stream) {
        conf.__localCallSlot.attach(stream);
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
        conf.__localCallSlot = new LocalCallerSlot(document.getElementById('local-video-container'), this.getLocalParticipant());
        // conf.__localCallSlot = document.getElementById('local-video');
        conf.__localCallSlot.setMuted(true);
        // conf.__localCallSlot.muted = true;

        conf.__bridge = new TelehealthBridge(conf.callerSettings.callerUuid, conf.callerSettings.apiKey
            , conf.callerSettings.serviceUrl);

        let bridgeHandlers = {
            onincomingcall: conf.handleIncomingCall.bind(conf)
            ,onbridgeactive: (bridge) => {
                conf.restartMediaStream();
            }
            ,onbridgeinactive: (bridge) => {
                if (conf.__localCallSlot) {
                    conf.__localCallSlot.stop();
                    // conf.__localCallSlot.srcObject = null;
                }
            }
            ,onbridgefailure: (bridge) => {
                if (conf.__localCallSlot) {
                    conf.__localCallSlot.stop();
                }
                alert(translations.BRIDGE_FAILED);
            }
        };

        conf.__bridge.startBridge(bridgeHandlers);
        conf.__isShutdown = false;
    };
    //
    // conf.shutdown = function()
    // {
    //     if (conf.__isShutdown)
    //     {
    //         return;
    //     }
    //
    //     conf.__bridge.shutdown();
    //     conf.__localCallSlot.stop();
    //     console.log("Shutting down " +  conf.callerSettings.callerUuid)
    //     conf.__isShutdown = true;
    // };

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
        // we don't have an audio stream when working with screen sharing.
        if (!call.isScreenSharing()) {
            call.attachActivityMonitor(conf.dbThresholdSetting, (call) => {
                conf.handleActivityEvent(call);
            });
        }
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

    this.makeScreenshareCall = function(callees) {
        conf.__bridge.createScreenSharingCall(callees)
            .catch(e => {
                alert(translations.CALL_CONNECT_FAILED);
                console.log("call exception " + conf.callerSettings.calleeUuid);
                console.error(e);
            });
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
        // we could double check again by hitting the server and verifying the session has started and accept
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
        conf.roomModal = modal;
        conf.roomModal.show();
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

                // mark the participant as being in the room
                conf.setParticipantInCallRoomStatus(result.call.getRemotePartyId(), 'Y');
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

        // add our vh resize calculator so we can make sure our viewpoint is constant.
        conf.updateTelehealthFullVH(); // first set our values here
        window.addEventListener('resize', conf.updateTelehealthFullVH);

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
                conf.setCurrentCallerFocusId(launchData.callerSettings.calleeUuid);
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

    this.cleanupSlots = function() {
        this.__slots.forEach(s => {
            try {
                // don't let a slot cleanup stop everything
                s.destruct();
            } catch (error) {
                console.error("Failed to cleanup slot ", error);
            }
        });
        this.__slots = [];
    };

    this.destruct = function()
    {
        // shouldn't
        if (this.__isShutdown) {
            console.log("destruct called multiple times while application has already shutdown.  Ignoring");
            return;
        }

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

        if (conf.sessionControlsActiveInterval !== null) {
            clearInterval(conf.sessionControlsActiveInterval);
            conf.sessionControlsActiveInterval = null;
        }
        if (conf.roomNode) {
            conf.cleanupParticipantActiveEvents();
            conf.roomNode = null;
        }
        let container = document.getElementById('telehealth-container');

        // cleanup any outstanding slots we have.
        conf.cleanupSlots();

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

        if (conf.isMinimized())
        {
            this.__minimizedConferenceRoom.destruct();
            this.__minimizedConferenceRoom = null;
        }

        // we are going to remove our resize calculator for our vh unit
        window.removeEventListener('resize', conf.updateTelehealthFullVH);
        conf.callerSettings = null;
        conf.telehealthSessionData = null;
        conf.__isShutdown = true;
    };

    this.updateTelehealthFullVH = function() {
        // this functionality comes from https://stackoverflow.com/a/53883824 by manuel-84
        // adapted from the answer on March 9th 2022 at 15:30.

        let vh = window.innerHeight - 1;
        document.documentElement.style.setProperty('--telehealth-full-vh', `${vh}px`);
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
                    btnJoin[i].classList.remove('btn-primary');
                    btnJoin[i].classList.add('btn-success');
                    btnJoin[i].disabled = false;
                } else {
                    btnJoin[i].removeEventListener('click', conf.startConferenceRoom);
                    btnJoin[i].classList.add('disabled');
                    btnJoin[i].classList.remove('btn-success');
                    btnJoin[i].classList.add('btn-primary');
                    btnJoin[i].disabled = true;
                }
            }
        }
    };

    this.sessionClose = function()
    {
        let conf = this;
        if (conf.isMinimized())
        {
            conf.destruct();
        }
        else {
            // have to use jquery to grab the jquery event
            jQuery("#telehealth-container").on("hidden.bs.modal", function () {
                jQuery("#telehealth-container").off("hidden.bs.modal");
                conf.destruct()
            });
            conf.roomModal.hide();
        }
    };

    this.confirmSessionClose = function()
    {
        if (conf.room == conf.ROOM_TYPE_WAITING)
        {
            conf.sessionClose();
        }
        else {
            let dialog = new ConfirmSessionCloseDialog(translations, conf.telehealthSessionData.pc_eid, conf.getRemoteScriptLocation(), function() {
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
        bootstrapModalTemplate.innerHTML = `<div class="modal fade pl-0" id="telehealth-container" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog mw-100 ml-2 mr-2 mt-0 mt-md-1 mt-lg-2 mb-0">
                <div class="modal-content">
                  <div class="modal-header pt-2 pt-md-3 pb-2 pb-md-3">
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
        conf.roomModal = new bootstrap.Modal(container, {keyboard: false, focus: true, backdrop: 'static'});
        conf.initModalEvents(container);
        return conf.roomModal;
    };

    this.startConferenceRoom = function()
    {
        conf.startProviderConferenceRoom();
    };

    this.startProviderConferenceRoom = function()
    {
        let container = document.getElementById('telehealth-container');
        // now grab our video container in our modal


        // let video = document.getElementById("local-video");

        conf.videoBar.destruct();

        // now we are going to replace the modal content
        let modalBody = container.querySelector('.modal-body');
        // clear out the modal body
        do {
            modalBody.removeChild(modalBody.firstChild);
        } while (modalBody.childNodes.length);
        modalBody.innerHTML = conf.conferenceRoomTemplate;
        conf.roomNode = modalBody.querySelector('.conference-room');
        if (!conf.roomNode) {
            console.error("Failed to find node with selector .conference-room from received data");
            alert(translations.SESSION_LAUNCH_FAILED);
            conf.sessionClose();
            return;
        }
        conf.roomNode.classList.add('active');

        // now let's replace our template video container with our original video container from the waiting room.
        let newLocalCallSlot = new LocalCallerSlot(container.querySelector('.local-participant'), this.getLocalParticipant());
        newLocalCallSlot.attach(this.__localCallSlot.getCurrentCallStream());
        newLocalCallSlot.addCallerSelectListener((callerSlot) => {
            conf.togglePinnedCallerId(callerSlot.getRemotePartyId());
            conf.updateParticipantDisplays();
        })
        this.__localCallSlot = newLocalCallSlot;
        //
        // let conferenceVideo = document.getElementById('local-video');
        // if (conferenceVideo && conferenceVideo.parentNode) {
        //     conferenceVideo.parentNode.replaceChild(video, conferenceVideo);
        // }
        conf.videoBar = new VideoBar(this.getVideoBarContainer(), conf.getFullConferenceVideoBarSettings());
        conf.inConferenceRoom = true;
        conf.room = conf.ROOM_TYPE_CONFERENCE;

        this.sessionUpdateInterval = setInterval(conf.updateConferenceRoomSession.bind(conf), conf.sessionUpdatePollingTime);
        this.__presentationScreen = new PresentationScreen('presentation-screen');

        this.sessionControlsActiveInterval = setInterval(conf.updateSessionControlsActive.bind(conf), conf.sessionControlsActiveTime);

        // setup our minimize controls
        conf.setupParticipantMinimizeControls(container);

        conf.setupParticipantActiveEvents(container);

        conf.updateConferenceRoomSession();
    };

    conf.updateSessionControlsActive = function() {
        // if the interval has shutdown then we don't want to update anything anymore.
        if (!conf.sessionControlsActiveInterval || conf.__isShutdown) {
            return;
        }

        let date = new Date();
        let lastActive = conf.lastActiveDate;
        if (lastActive && (date.getTime() - lastActive.getTime() > conf.sessionControlsIdleTime)) {
            conf.roomNode.classList.remove('active');
            conf.roomNode.classList.add('idle');
            conf.lastActive = null;
        }
    };

    conf.setupParticipantActiveEvents = function() {
        conf.lastActiveDate = new Date(); // set our initial active date
        conf.roomNode.addEventListener('mousemove', conf.updateActive);
        let presentationScreenContainer = conf.roomNode.querySelector('.remote-video-container');
        if (!presentationScreenContainer) {
            console.error("Failed to find node with selector '.remote-video-container'");
            return;
        }
        presentationScreenContainer.addEventListener('click', conf.updateActive);
        // this.__presentationScreen.addSelectHandler(conf.updateActive);
    };

    conf.cleanupParticipantActiveEvents = function() {
        if (conf.roomNode) {
            conf.roomNode.removeEventListener('mousemove', conf.updateActive);
        }
    };

    conf.updateActive = function() {
        conf.lastActiveDate = new Date();
        if (conf.roomNode) {
            conf.roomNode.classList.remove('idle');
            conf.roomNode.classList.add('active');
        }
    };

    conf.toggleSidebar = function() {
        if (conf.roomNode) {
            let sidebar = conf.roomNode.querySelector('.sidebar')
            if (sidebar.classList.contains('minimized')) {
                sidebar.classList.remove('minimized');
                sidebar.classList.add('maximized');
            } else {
                sidebar.classList.add('minimized');
                sidebar.classList.remove('maximized');
            }
        }
    };

    conf.setupParticipantMinimizeControls = function(container) {
        let btn = container.querySelector('.btn-list-minimize');
        if (!btn) {
            console.error("Failed to find minimize button with selector .btn-list-minimize");
            return;
        }
        btn.addEventListener('click', conf.toggleSidebar);

        // now we need to do a media query check and initially minimize if we are at the point that we only
        // have 400px of real estate on mobile...
        var x = window.matchMedia("(max-width: 400px)");
        if (x.matches) {
            conf.toggleSidebar();
        }
    };

    this.updateLocalParticipantList = function() {
        let eid = conf.telehealthSessionData.pc_eid;
        window.top.restoreSession();
        window.fetch(conf.getRemoteScriptLocation() + '?action=get_participant_list&pc_eid=' + encodeURIComponent(eid), {redirect: "manual"})
            .then((request) => {
                if (request.ok) {
                    return request.json();
                } else {
                    throw new Error("Failed to receive valid server response from get_participant_list");
                }
            })
            .then(result => {
                this.callerSettings.participantList = result.participantList;0
            })
            .catch(error => console.error("Failed to update local participant list. Third party invitations may not be up to date", error));
    };

    this.updateConferenceRoomSession = function() {
        let appt = conf.callerSettings.appointment || {};
        let pc_eid = appt.eid || {};
        window.top.restoreSession();
        window.fetch(conf.getRemoteScriptLocation() + '?action=conference_session_update&pc_eid=' + encodeURIComponent(pc_eid), {redirect: "manual"})
            .then((request) => {
                if (request.ok) {
                    return request.json();
                } else {
                    throw new Error("Failed to update session");
                }
            })
            .then(result => {
                if (!conf.sessionUpdateInterval) {
                    return; // we've been shut down and should just cancel at this point.
                }
               if (result.status !== 'success') {
                   throw new Error("Failed to update session");
               } else {
                   if (conf.__isShutdown) {
                       // we've shutdown so ignore any updates here.
                   }
                   // grab our participant list

                   // algorithm will be
                   // 1. get the participant list
                   // 2. check participant list that we have against the server participant list
                   // 3. if participants are not the same
                   //       at least one participant in our list of server participants is not in our local list
                   // 4. call server for update list
                   // 5. grab updated list and populate participants.
                   let participants = result.participantList || [];
                   let needsUpdate = participants.some(p => {
                       let participant = this.callerSettings.participantList.find(cpl => {
                           // don't think we need to update if we are in the room.
                           return cpl.role == p.role && cpl.id == p.id; // && cpl.inRoom == p.inRoom
                       });
                       // server has participant that we don't have.
                       return participant === undefined;
                   });
                   if (needsUpdate) {
                       this.updateLocalParticipantList();
                   }
               }
            })
            .catch(error => console.error("Conference session update ", error));
    };

    this.cancelConferenceRoomSessionUpdateInterval = function()
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
        conf.addSettingsForScreenshare(settings);
        return settings;
    };

    this.addSettingsForScreenshare = function(settings) {
        settings.screenshare = false;
        if (this.__slots.length) {
            // only show the button if we actually support the screenshare
            if (window && window.navigator && window.navigator.mediaDevices
                && window.navigator.mediaDevices.getDisplayMedia) {
                settings.screenshare = true;
            }
        }
    };

    this.addSettingsForThirdPartyInvitations = function(settings) {
        if (conf.features && conf.features.thirdPartyInvitations)
        {
            settings.invite = true;
            settings.inviteCallback = conf.handleInviteCallback.bind(conf);
        } else {
            settings.invite = false;
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
        conf.addSettingsForThirdPartyInvitations(settings);
        conf.addSettingsForScreenshare(settings);
        return settings;
    };

    this.handleInviteCallback = function() {
        let dialog = new AddPatientDialog(this.apiCSRFToken, translations, conf.telehealthSessionData.pc_eid
            , conf.getRemoteScriptLocation(), comlink.settings.fhirPath, conf.callerSettings.participantList
            , function(callerSettings) {
            // make suer we update our caller settings with the newly allowed patient so the provider can receive the call
            if (callerSettings) {
                conf.callerSettings = callerSettings;
            }
            });
        dialog.show();
    };

    this.handleCallStartedEvent = function(call)
    {
        conf.toggleRemoteVideo(true);
    };

    this.resetConferenceVideoBar = function() {
        if (conf.videoBar) {
            conf.videoBar.destruct();
        }
        if (conf.__isShutdown) {
            return; // nothing to do here as we are shutting down.
        }
        if (this.isMinimized()) {
            conf.videoBar = this.__minimizedConferenceRoom.resetConferenceVideoBar(conf.getMinimizedConferenceVideoBarSettings());
        } else {
            conf.videoBar = new VideoBar(this.getVideoBarContainer(), conf.getFullConferenceVideoBarSettings());
        }
    };

    this.getVideoBarContainer = function() {
        var container = document.getElementById('telehealth-container');
        return container.querySelector('.conference-room .telehealth-button-bar');
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
                // when person is removed we can remove them from the call.
                this.removeSlotForCall(call);
                conf.setParticipantInCallRoomStatus(call.getRemotePartyId(), 'N');
            }

            this.updateParticipantDisplays();
        }
    };

    // todo: look at removing this
    this.removeSlotForCall = function(call) {
        // if the user data is allocated then this is an existing call
        if (call.getUserData() != null) {
            let callerSlot = call.getUserData();
            let remotePartyId = callerSlot.getRemotePartyId();
            callerSlot.destruct(); // remove everything
            // cleanup the slots
            this.__slots = this.__slots.filter(s => s !== callerSlot);
            // if the caller has left then we have nothing to pin here.
            if (remotePartyId == this.__pinnedCallerUuid) {
                this.togglePinnedCallerId(remotePartyId);
            }
            if (this.__slots.length && this.getCurrentCallerFocusId() == remotePartyId) {
                // need to set our current caller focus id
                // for now we will just set it to the first one on the call instead of trying to figure out
                // the last known audio chatting
                this.setCurrentCallerFocusId(this.__slots[0].getRemotePartyId());
            }
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
    };

    this.handleActivityEvent = function(call) {
        // first we only do this on a multi-party call where we have not pinned the caller
        this.setCurrentCallerFocusId(call.getRemotePartyId());
        this.updateParticipantDisplays();
    };

    this.handleCallHangup = function()
    {
        // if we hangup the call we maximize the window since the confirm dialog is embedded inside the
        // main window.
        if (conf.isMinimized()) {
            conf.maximizeProviderConferenceCall({});
        }
        conf.confirmSessionClose();
    };

    this.minimizeProviderConferenceCall = function()
    {
        let container = document.getElementById('telehealth-container');
        let defaultSettings = this.features.minimizeWindow || {enabled: true, defaultPosition: 'bottom-left'};
        this.__minimizedConferenceRoom = new MinimizedConferenceRoom(container, defaultSettings);
        this.__minimizedConferenceRoom.minimizeConferenceRoom(this.getMinimizedConferenceVideoBarSettings(), );
        this.resetConferenceVideoBar(); // make sure we reset our controls here before we continue
        conf.roomModal.hide();
        this.updateParticipantDisplays();
    };

    this.maximizeProviderConferenceCall = function(evt)
    {
        if (this.isMinimized()) {
            // save off our offset so we can restore it later
            if (this.features.minimizeWindow) {
                this.features.minimizeWindow.offset = this.__minimizedConferenceRoom.getCurrentOffset();
            }
            this.__minimizedConferenceRoom.maximizeConferenceRoom();
            this.__minimizedConferenceRoom.destruct();
            this.__minimizedConferenceRoom = null;


            // everything's moved we can now display the larger video modal.
            if (conf.roomModal) {
                conf.roomModal.show();
            } else {
                console.error("Failed to find roomModal");
            }
        }
        this.updateParticipantDisplays();
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
        // TODO: @adunsulag should we remove this reliance on the node.dataset here?
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

        if (display)
        {
            waitingContainer.classList.add('d-none');
            this.__presentationScreen.show();
        } else {
            waitingContainer.classList.remove('d-none');
            this.__presentationScreen.hide();
        }
    };

    this.toggleScreenSharing = function(evt) {
        let participantList = this.getRemoteParticipantList();
        let screenShareCallers = participantList.filter(p => p.inRoom == 'Y').map(p => p.uuid);
        this.makeScreenshareCall(screenShareCallers);
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

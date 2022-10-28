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



    let cvb = null;
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

    function RegistrationChecker(isPatient)
    {
        var checker = this;
        var timeoutId;
        var settings;
        var currentCheckCount = 0;
        var maxCheck = 10;

        this.checkRegistration = function()
        {
            if (currentCheckCount++ > maxCheck)
            {
                console.error("Failed to get a valid telehealth registration for user");
                return;
            }

            let location = getTeleHealthScriptLocation(isPatient) + '?action=check_registration';

            window.top.restoreSession();
            window.fetch(location)
            .then(result => {
                if (!result.ok)
                {
                    throw new Error("Registration check failed");
                }
                return result.json();
            })
            .then(registrationSettings => {
                if (registrationSettings && registrationSettings.hasOwnProperty('errorCode')) {
                    if (registrationSettings.errorCode == 402) {
                        // user is not enrolled and so we will skip trying to register the user
                        checker.settings = {};
                    }
                }
                checker.settings = registrationSettings;
            })
            .catch(error => {
                console.error("Failed to execute check_registration action", error);
                timeoutId = setTimeout(checker.checkRegistration.bind(checker), 2000);
            });
        };
        return this;
    }

    // only 91% browser support for import... asian browsers like Baidu don't support this... according to conversations
    // with Comlink, modern Chrome, Firefox, and Safari browsers are expected support.
    import ('./cvb.min.js').then((module) => {
        cvb = module;
    });

    function ATSlot(index, onhangup) {
        /** @private */
        this.__call = null;

        /** @private */
        this.__video = document.getElementById('remote-video');
        if (!this.__video)
        {
            throw new Error("Failed to find #remote-video element");
        }

        this.isAvailable = function() {
            return this.__call == null;
        };


        this.attach = function(call, stream) {
            this.__call = call;
            if (call == null || stream == null)
            {
                console.error("Call or stream were null.  Cannot proceed", {call: call, stream: stream});
                throw new Error("call and stream cannot be null");
            }
            this.__call.setUserData(this);
            this.__video.srcObject = stream;
            this.__video.play();
        };

        this.detach = function() {
            this.__call.setUserData(null);
            this.__call = null;
            this.__video.srcObject = null;
        };
    }

    // TODO: @adunsulag eventually we can merge this into our conference room app.
    function ATApp(conferenceRoom, params)
    {
        this.__hasLocalPermisions = false;
        /** @private */
        this.__bridge = null;

        /** @pprivate */
        this.__userId = params.userId;

        /** @private */
        this.__passwordHash = params.password;

        /** @private */
        this.__localVideoElement = document.getElementById('local-video');
        this.__localVideoElement.muted = true;

        /** @private */
        this.__slots = [];

        /**
         * @private
         */
        this.__serviceUrl = params.serviceUrl;

        this.__remoteCallSlot = null;

        this.__isShutdown = true;

        this.allocateSlot = function(call, stream) {
            // as we can get multiple events we only want to allocate on the call if we don't have any stream data setup.
            if (!call.getUserData()) {
                // we are leaving this in place in case we want to extend things and add additional callers to the call
                // in the future
                if (this.__remoteCallSlot) {
                    // detatch the old call and initiate the new one
                    this.freeSlot(this.__remoteCallSlot);
                }
                this.__remoteCallSlot = new ATSlot(0, function () {
                });
                this.__remoteCallSlot.attach(call, stream);
            }
        };

        this.freeSlot = function(slot) {
            if (slot !== null && slot.__call == null)
            {
                console.error("freeSlot called with null slot ", {slot: slot});
                return;
            }
            slot.__call.stop();
            slot.detach();
            // trying to leave code open in case we add more callers... but we add this in to clear our variables
            if (slot == this.__remoteCallSlot)
            {
                this.__remoteCallSlot = null;
            }
        };

        this.restartMediaStream = function() {
            if (this.__localVideoElement.srcObject) {
                this.__bridge.closeLocalMediaStream();
            }

            return this.__bridge.getLocalMediaStream()
                .then(stream => this.handleLocalMediaStreamStarted(stream))
                .catch(error => this.handleLocalMediaStreamFailed(error));
        };
        this.handleLocalMediaStreamStarted = function(stream) {
            this.__localVideoElement.srcObject = stream;
            this.__localVideoElement.play();
            this.__hasLocalPermisions = true;
            conferenceRoom.toggleJoinButton(conferenceRoom.shouldEnableJoinButton());
            conferenceRoom.togglePermissionBox(false);
            return this.__hasLocalPermisions;
        };
        this.handleLocalMediaStreamFailed = function(error) {
            console.error(error);
            this.__hasLocalPermisions = false;
            conferenceRoom.toggleJoinButton(conferenceRoom.shouldEnableJoinButton());
            conferenceRoom.togglePermissionBox(true);
            return this.__hasLocalPermisions;
        };

        this.start = function() {
            // Create the bridge instance
            this.__bridge = new cvb.VideoBridge({
                userId: this.__userId,
                passwordHash: this.__passwordHash,
                type: 'normal',
                serviceUrl: this.__serviceUrl // 'https://sandbox.mvoipctsi.com:22528'
            });
            console.log("Instantiated bridge "  + this.__userId);

            // Callback: incoming call
            //
            // When a new call comes in we'll ask the user if they want to accept or
            // reject it.
            this.__bridge.onincomingcall = (call) => {
                console.log("Receiving call from " + call.getRemotePartyId() + " for "  + this.__userId);

                conferenceRoom.handleIncomingCall(call);
            };

            // Callback: bridge active
            //
            // When the bridge becomes active we'll ask it for the local media stream
            // which we will then play in the local video element.
            this.__bridge.onbridgeactive = (bridge) => {
                this.__bridge.getLocalMediaStream()
                    .then(stream => this.handleLocalMediaStreamStarted(stream))
                    .catch(error => this.handleLocalMediaStreamFailed(error));

                console.log("The bridge is active " + this.__userId);
            };

            // Callback: bridge inactive
            //
            // When the bridge becomes inactive we'll stop the local stream video
            // element.
            this.__bridge.onbridgeinactive = (bridge) => {
                if (this.__localVideoElement && this.__localVideoElement.stop) {
                    this.__localVideoElement.stop();
                    this.__localVideoElement.srcObject = null;
                }

                console.log("The bridge is inactive " + this.__userId);
            };

            // Callback: bridge failure
            //
            // Similarly, if the bridge suffers a catastrophic failure we'll stop the
            // local stream video element.
            this.__bridge.onbridgefailure = (bridge) => {
                this.__localVideoElement.stop();
                this.__localVideoElement.srcObject = null;

                console.error("The bridge failed " + this.__userId);
            };

            // Finally spin up the bridge.
            this.__bridge.start();
            console.log("Started bridge "  + this.__userId);
            this.__isShutdown = false;
        };

        this.shutdown = function()
        {
            if (this.__isShutdown)
            {
                return;
            }

            this.__bridge.shutdown();
            this.__localVideoElement.srcObject = null;
            console.log("Shutting down " +  this.__userId)
            this.__isShutdown = true;
        };

        this.setCallHandlers = function(call) {
            // Callback: streams updated
            //
            call.oncallstarted = (call) => {
                conferenceRoom.handleCallStartedEvent(call);
            };
            // Once we have the remote stream we'll allocate a video slot.
            call.onstreamupdated =
                (call, stream) => {
                    console.log("onstreamupdated " + this.__userId);
                    this.allocateSlot(call, stream);
                };

            // Callback: call ended
            //
            // When the call ends we free the video slot.
            call.oncallended = (call) => {
                this.freeSlot(call.getUserData());
                conferenceRoom.handleCallEndedEvent(call);
            };
        };

        this.makeCall = function(calleeId) {
            const call = this.__bridge.createVideoCall(calleeId);

            this.setCallHandlers(call);

            // Callback: outbound call rejected
            //
            // If the call is rejected we do nothing. We know that in that case the
            // stream will not have been created and, consequently, we will not have
            // created the video element.
            call.oncallrejected = (call) => {
                console.log(call);
                console.log("oncallrejected " + this.__userId);
                // if the call is rejected the provider dropped off before we connected so we go back to the conference
                // room
                conferenceRoom.handleCallEndedEvent(call);
            };

            // Finally, start the call
            call.start().catch((e) => {
                alert(translations.CALL_CONNECT_FAILED);
                console.log("call exception " + this.__userId);
                console.error(e);
                conferenceRoom.handleCallEndedEvent(call);
            });
        };

        this.enableMicrophone = function(flag) {
            this.__bridge.enableMicrophone(flag);
        };

        this.enableCamera = function(flag) {
            this.__bridge.enableCamera(flag);
        };

        this.hasLocalPermissionsEnabled = function()
        {
            return this.__hasLocalPermisions;
        };

        return this;
    }

    function ConferenceRoom()
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
            if (callerId === conf.callerSettings.calleeUuid)
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
            return getTeleHealthScriptLocation(false);
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
                    if (result.canCall)
                    {
                        conf.app.setCallHandlers(result.call);
                        result.call.accept();
                        console.log("Call accepted from " + result.call.getRemotePartyId() + " for "  + conf.callerSettings.callerUuid);
                        // NOTE that result.call.accept does NOT fire the oncallstarted event for the call stream... so we have to
                        // toggle our remote video here instead.
                        conf.toggleRemoteVideo(true);
                    }
                    else
                    {
                        result.call.reject();
                        console.log("Call from " + result.call.getRemotePartyId() + " not authorized by server for "  + conf.callerSettings.callerUuid);
                    }
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
                    conf.app = new ATApp(conf, {
                        userId: conf.callerSettings.callerUuid,
                        password: conf.callerSettings.apiKey,
                        serviceUrl: conf.callerSettings.serviceUrl
                    });
                    conf.app.start();
                    conf.room = conf.ROOM_TYPE_WAITING;
                })
                .catch(function(error) {
                    console.error(error);
                    // null out our values if we never started our session.
                    if (!conf.app) {
                        alert(translations.SESSION_LAUNCH_FAILED);
                        conf.destruct(); // shut things down if we don't have a valid session.
                    }
                });
        };

        this.destruct = function()
        {
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
            if (conf.app && conf.app.shutdown)
            {
                // catch any problems from the library so we can still clean up.
                try {
                    conf.app.shutdown();
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
            if (conf.app && conf.app.restartMediaStream) {
                // destroy the app and restart
                conf.app.restartMediaStream();
            }
        };

        this.hasLocalPermissionsEnabled = function()
        {
            if (conf.app) // && conf.app.isActive())
            {
                return conf.app.hasLocalPermissionsEnabled();
            }
            return false;
        };

        this.shouldEnableJoinButton = function()
        {
            return this.hasLocalPermissionsEnabled();
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

        this.startVideoStreams = function()
        {
            conf.app = new ATApp(conf, {
                userId: conf.callerSettings.callerUuid,
                password: conf.callerSettings.apiKey,
                serviceUrl: conf.callerSettings.serviceUrl
            });
            conf.app.start();
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

        function ConfirmSessionCloseDialog(pc_eid, scriptLocation)
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
                // conf.app.shutdown();
                container.querySelector('.row-confirm').classList.add('d-none');
                container.querySelector('.row-update-status').classList.remove('d-none');
            };

            this.sendAppointmentStatusUpdate = function(status)
            {
                console.log("Setting appointment to status ", status);
                let postData = "action=set_appointment_status&pc_eid=" + encodeURIComponent(pc_eid) + "&status=" + encodeURIComponent(status);
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
                        conf.sessionClose();
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

        this.confirmSessionClose = function()
        {
            if (conf.room == conf.ROOM_TYPE_WAITING)
            {
                conf.sessionClose();
            }
            else {
                let dialog = new ConfirmSessionCloseDialog(conf.telehealthSessionData.pc_eid, getTeleHealthScriptLocation(false));
                dialog.show();
            }
        };

        this.shutdownProviderWaitingRoom =  function()
        {
            let container = document.getElementById('telehealth-container');
            if (conf.app && conf.app.shutdown)
            {
                // catch any problems from the library so we can still clean up.
                try {
                    conf.app.shutdown();
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
            conf.updateConferenceRoomSession();
        };

        this.updateConferenceRoomSession = function() {
            let appt = conf.callerSettings.appointment || {};
            let eid = appt.eid || {};
            // TODO: if we ever need to take action on the session update we would do that here.
            // TODO: @adunsulag change eid here to be pc_eid
            window.top.restoreSession();
            window.fetch(conferenceRoom.getRemoteScriptLocation() + '?action=conference_session_update&eid=' + encodeURIComponent(eid), {redirect: "manual"})
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
            if (conferenceRoom.sessionUpdateInterval)
            {
                window.clearInterval(conferenceRoom.sessionUpdateInterval);
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
            return settings;
        };

        this.getFullConferenceVideoBarPatientSettings = function()
        {
            let settings = conf.getDefaultVideoBarSettings();
            settings.hangupCallback = conf.handleCallHangup.bind(conf);
            settings.expand = false;
            settings.notes = false; // patient doesn't get notes.
            settings.hangup = true;
            return settings;
        };

        this.getFullConferenceVideoBarSettings = function()
        {
            let settings = conf.getDefaultVideoBarSettings();
            settings.hangupCallback = conf.handleCallHangup.bind(conf);
            settings.notesCallback = conf.minimizeProviderConferenceCall.bind(conf);
            settings.expand = false;
            settings.notes = true;
            settings.hangup = true;
            return settings;
        };

        this.handleCallStartedEvent = function(call)
        {
            conferenceRoom.toggleRemoteVideo(true);
        };

        this.handleCallEndedEvent = function(call)
        {
            conferenceRoom.toggleRemoteVideo(false);
        };

        this.handleCallHangup = function()
        {
            // if we hangup the call we maximize the window since the confirm dialog is embedded inside the
            // main window.
            if (this.isMinimized) {
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
            var localVideo = document.getElementById('remote-video');

            var template = document.createElement('div');
            template.id = "minimized-telehealth-video";
            template.className = "col-lg-2 col-md-3 col-sm-4 col-6 drag-action";

            window.document.body.appendChild(template);
            template.appendChild(localVideo);

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

            this.isMinimized = true;
        };

        this.maximizeProviderConferenceCall = function(evt)
        {
            // remove the event listener
            var remoteVideoContainer = document.querySelector('.remote-video-container');
            var remoteVideo = document.getElementById('remote-video');

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
                this.isMinimized = false;
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
            if (conf.app && conf.app.enableMicrophone) {
                conf.app.enableMicrophone(toggle);
            } else {
                console.error("app is not initalized and cannot toggle microphone");
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
            if (conf.app && conf.app.enableCamera) {
                conf.app.enableCamera(toggle);
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

    function VideoBarButton(node, defaultValue, callback)
    {
        let btn = this;
        btn.node = node;
        btn.value = defaultValue;
        btn.callback = callback;
        btn.enabled = defaultValue === true;

        btn.init = function()
        {
            if (btn.enabled)
            {
                btn.attach();
            }
            else
            {
                btn.detatch();
            }
        };

        btn.attach = function() {
          if (this.node)
          {
              this.node.addEventListener('click', this.callback);
              this.node.classList.remove('d-none');
          }
        };

        btn.detatch = function()
        {
            if (this.node)
            {
                this.node.removeEventListener('click', this.callback);
                this.node.classList.add('d-none');
            }
        };
        btn.destruct = function()
        {
            // remove event handlers and cleanup memory.
            btn.detatch();
            btn.node = null;
            btn.callback = null;
        }
        btn.toggle = function()
        {
            btn.enabled = !btn.enabled;
            if (btn.enabled)
            {
                this.attach();
            }
            else
            {
                this.detatch();
            }
        };
        btn.init();
        return btn;
    }

    function VideoBar(container, options)
    {
        var bar = this;
        /**
         * @var VideoBarButton[]
         */
        bar.__buttons = {};

        /**
         * @var HTMLElement
         */
        bar.__container = container;

        function noop() {}

        function init() {
            options = options || {};
            setDefaultValue(options,'notes', false);
            setDefaultValue(options,'notesCallback', noop);

            setDefaultValue(options,'microphone', true);
            setDefaultValue(options,'microphoneCallback', noop);
            setDefaultValue(options,'video', true);
            setDefaultValue(options,'videoCallback', noop);
            setDefaultValue(options,'expand', false);
            setDefaultValue(options,'expandCallback', noop);
            setDefaultValue(options,'hangup', false);
            setDefaultValue(options,'hangupCallback', noop);

            let btns = ['notes', 'microphone', 'video', 'expand', 'hangup'];
            btns.forEach(btn => {
                let node = bar.__container.querySelector(".telehealth-btn-" + btn);
                let callback = options[btn + 'Callback'] || noop;

                if (!node)
                {
                    console.error("Failed to find node for telehealth-btn-" + btn);
                    return;
                }
                bar.__buttons[btn] = new VideoBarButton(node, options[btn], callback);
            });

            // we always make sure our microphone and video is displayed, but we swap the icons if they are disabled.
            if (!bar.__buttons['microphone'].enabled)
            {
                let btn = bar.__buttons['microphone'];
                let node = btn.node.querySelector('.fa');
                if (btn && node) {
                    btn.toggle();
                    node.classList.add('fa-microphone-slash');
                    node.classList.remove('fa-microphone');
                } else {
                    console.error("Failed to find microphone node and fa icon");
                }
            }
            if (!bar.__buttons['video'].enabled)
            {
                let btn = bar.__buttons['video'];
                let node = btn.node.querySelector('.fa');
                if (btn && node) {
                    btn.toggle();
                    node.classList.add('fa-video-slash');
                    node.classList.remove('fa-video');
                } else {
                    console.error("Failed to find video node and fa icon");
                }
            }
        }

        function destruct()
        {
            Object.values(bar.__buttons).forEach(button => button.detatch());
            bar.__container = null;
        }

        function setDefaultValue(obj, property, value)
        {
            if (!obj.hasOwnProperty(property))
            {
                obj[property] = value;
            }
        }

        function toggleButtons(toggleBtns)
        {
            toggleBtns.forEach(btn => {
                if (bar.__buttons[btn])
                {
                    bar.__buttons[btn].toggle();
                }
                else
                {
                    console.error('Failed to find button to toggle for button ' + btn);
                }

            });
        }


        bar.init = init;
        bar.destruct = destruct;
        bar.toggleButtons = toggleButtons;

        bar.init();
        return bar;
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
        conferenceRoom = new ConferenceRoom();
        conferenceRoom.init(data);
    }

    function PatientConferenceRoom() {
        let patientConferenceRoom = new ConferenceRoom();
        let parentDestruct = patientConferenceRoom.destruct;
        let checkProviderReadyForPatientInterval = null;
        let providerIsReady = false;

        function checkProviderReadyForPatient()
        {
            let pc_eid = patientConferenceRoom.telehealthSessionData.pc_eid;
            window.top.restoreSession();
            window.fetch(moduleLocation + 'public/index-portal.php?action=patient_appointment_ready&eid=' + encodeURIComponent(pc_eid), {redirect: "manual"})
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
                    providerIsReady = apptReadyData.session.providerReady === true;

                    // we update the calleeUuid here in case another provider takes over the appointment session of
                    // the patient.  This occurs if a provider is out of town and the patient is currently waiting
                    // for the session to start.
                    if (apptReadyData.session.calleeUuid)
                    {
                        patientConferenceRoom.callerSettings.calleeUuid = apptReadyData.session.calleeUuid;
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

        patientConferenceRoom.getRemoteScriptLocation = function() {
            return getTeleHealthScriptLocation(true);
        };

        patientConferenceRoom.getFullConferenceVideoBarSettings = patientConferenceRoom.getFullConferenceVideoBarPatientSettings;

        patientConferenceRoom.startConferenceRoom = function()
        {
            patientConferenceRoom.stopProviderReadyCheck();
            patientConferenceRoom.startProviderConferenceRoom(); // not sure there is much difference here
            patientConferenceRoom.app.makeCall(patientConferenceRoom.callerSettings.calleeUuid);
        };

        patientConferenceRoom.handleIncomingCall = function()
        {
            // patient shouldn't get provider initiated call... so we will skip this for now.
        };

        patientConferenceRoom.handleCallEndedEvent = function(call)
        {
            alert(translations.HOST_LEFT);
            // for patient conference if the provider leaves the call we need to send them back to the waiting room
            patientConferenceRoom.replaceConferenceRoomWithWaitingRoom();
            // cancel our session update sequence that happens during the conference room.
            patientConferenceRoom.cancelUpdateConferenceRoomSession();
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

    function showPatientPortalDialog(appointmentEventId, translations) {
            let telehealthSessionData = {
                pc_eid: appointmentEventId
            };
        conferenceRoom = new PatientConferenceRoom();
        conferenceRoom.init(telehealthSessionData);
    }

    function launchRegistrationChecker(isPatient)
    {
        checker = new RegistrationChecker(isPatient);
        checker.checkRegistration();
    }

    // now to export our object here
    comlink.telehealth = {
        showPatientPortalDialog: showPatientPortalDialog,
        launchProviderVideoMessage: launchProviderVideoMessage,
        launchRegistrationChecker: launchRegistrationChecker
    };
    // now reassign our comlink object or create it new if there are no other comlink extensions.
    window.comlink = comlink;
})(window, window.comlink || {}, bootstrap, $, window.dlgopen || function() {});

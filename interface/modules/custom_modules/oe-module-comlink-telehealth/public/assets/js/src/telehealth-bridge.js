/**
 * Javascript Wrapper around the comlink bridge object for handling telehealth bridge interactions in the way OpenEMR
 * needs it to.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2023 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
import {VideoBridge, VideoCall} from "./cvb.min";

export class TelehealthBridge
{
    isShutdown = false;
    userId = null;
    passwordHash = null;
    serviceUrl = null;
    /**
     *
     * @type VideoBridge
     */
    bridge;

    /**
     *
     * @type VideoCall
     */
    currentLocalScreenShareCall;

    constructor(userId, passwordHash, serviceUrl) {
        this.userId = userId;
        this.passwordHash = passwordHash;
        this.serviceUrl = serviceUrl;
        this.isRunning = false;
    }

    noop() {
    }

    startBridge(handlers) {
        handlers = handlers || {};
        handlers.onincomingcall = handlers.onincomingcall || this.noop;
        handlers.onbridgeactive = handlers.onbridgeactive || this.noop;
        handlers.onbridgeinactive = handlers.onbridgeinactive || this.noop;
        handlers.onbridgefailure = handlers.onbridgefailure || this.noop;

        this.bridge = new VideoBridge({
            userId: this.userId,
            passwordHash: this.passwordHash,
            type: 'normal',
            serviceUrl: this.serviceUrl
        });
        console.log("Instantiated bridge "  + this.userId);


        // Callback: incoming call
        //
        // When a new call comes in we'll ask the user if they want to accept or
        // reject it.
        this.bridge.onincomingcall = (call) => {
            console.log("Receiving call from " + call.getRemotePartyId() + " for "  + this.userId);
            handlers.onincomingcall(call);
        };

        // Callback: bridge active
        //
        // When the bridge becomes active we'll ask it for the local media stream
        // which we will then play in the local video element.
        this.bridge.onbridgeactive = (bridge) => {
            console.log("The bridge is active " + this.userId);
            handlers.onbridgeactive(bridge);
        };

        // Callback: bridge inactive
        //
        // When the bridge becomes inactive we'll stop the local stream video
        // element.
        this.bridge.onbridgeinactive = (bridge) => {
            console.log("The bridge is inactive " + this.userId);
            handlers.onbridgeinactive(bridge);
        };

        // Callback: bridge failure
        //
        // Similarly, if the bridge suffers a catastrophic failure we'll stop the
        // local stream video element.
        this.bridge.onbridgefailure = (bridge) => {
            console.error("The bridge failed " + this.userId);
            if (!this.isShutdown) {
                try {
                    // we only call this once
                    handlers.onbridgefailure(bridge);
                }
                catch (e) {
                    console.error("Failure occurred in bridge shutdown handler", e);
                }
            }
            this.isShutdown = true;
            this.shutdown();
        };

        // Finally spin up the bridge.
        this.bridge.start();
        console.log("Started bridge "  + this.userId);
        this.isShutdown = false;
    }

    hasLocalPermissionsEnabled() {
        if (!this.bridge) {
            return false;
        }

        return this.bridge.hasLocalPermissionsEnabled();
    }

    shutdown() {
        // if we are already shut down, don't need to do anything here.
        if (!this.isShutdown) {
            try {
                this.bridge.shutdown();
            }
            catch (e) {
                console.log("Error shutting down bridge", e);
            }
            this.isShutdown = true;
        }
    }

    restartMediaStream() {
        if (this.isShutdown) {
            console.error("Bridge is shutdown.  Cannot restart media stream");
        }
    }

    getLocalMediaStream() {
        return this.bridge.getLocalMediaStream();
    }

    enableMicrophone(toggle) {
        return this.bridge.enableMicrophone(toggle);
    }

    enableCamera(toggle) {
        return this.bridge.enableCamera(toggle);
    }

    createScreenSharingCall(calleeIds) {
        // make the first call then execute the subsequent calls
        let call = this.bridge.createScreenSharingCall(calleeIds[0]);
        calleeIds.shift();

        let promise = call.start();
        if (calleeIds.length <= 0) {
            return promise.then(() => {
                this.currentLocalScreenShareCall = call;
                this.setScreenShareCallHandlers(call);
                return call;
            })
        }

        // multiparty call so we handle this differently
        return promise.then(() => {
            this.currentLocalScreenShareCall = call;
            this.setScreenShareCallHandlers(call);

            return Promise.all(calleeIds.map(id => {
                let basedOnCall = this.bridge.createScreenSharingCall(id, this.currentLocalScreenShareCall);
                this.setScreenShareCallHandlers(basedOnCall);
                return basedOnCall.start();
            }))
            .then(localCalls => {
                return this.currentLocalScreenShareCall;
            })
            .catch(e => {
                if (this.currentLocalScreenShareCall) {
                    console.log("multiparty screenshare failed ")
                    this.currentLocalScreenShareCall.stop();
                    this.currentLocalScreenShareCall = null;
                }
                throw e;
            });
        });
    }

    createVideoCall(calleeUuid) {
        return this.bridge.createVideoCall(calleeUuid);
    }

    setCallHandlers(call) {
        return this.bridge.setCallHandlers(call);
    }

    setScreenShareCallHandlers(call) {
        this.currentLocalScreenShareCall.oncallended = () => {
            this.currentLocalScreenShareCall = null;
        };
    }
}

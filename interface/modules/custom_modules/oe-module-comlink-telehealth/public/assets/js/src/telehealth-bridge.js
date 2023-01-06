import * as cvb from "./cvb.min";

export class TelehealthBridge
{
    isShutdown = false;
    userId = null;
    passwordHash = null;
    serviceUrl = null;
    bridge = null;

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

        this.bridge = new cvb.VideoBridge({
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
            this.bridge.shutdown();
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

    makeScreenshareCall(calleeUuid) {
        return this.bridge.makeScreenshareCall(calleeUuid);
    }

    setCallHandlers(call) {
        return this.bridge.setCallHandlers(call);
    }
}
import {ATSlot} from "./at-slot.js";

export function CallerSlot(videoId, screenshareId) {
    this.__videoSlot = new ATSlot(videoId);
    this.__screenshareSlot = new ATSlot(screenshareId);

    this.isAvailable = function() {
        return this.__videoSlot.isAvailable();
    };

    this.isAvailableForCall = function(call) {
        // we say things are available if the remote id is the same as the caller remote party id
        // or if the video slot has no call
        if (this.__videoSlot.isAvailable() || this.__videoSlot.getRemotePartyId() == call.getRemotePartyId())
        {
            return true;
        }
        return false;
    };

    this.attach = function(call, stream) {
        if (call == null || stream == null)
        {
            console.error("Call or stream were null.  Cannot proceed", {call: call, stream: stream});
            throw new Error("call and stream cannot be null");
        }
        // let's us cleanup screensharing and video slots if we already have it allocated.
        // this only happens if the same user calls into the call
        if (call.isScreenSharing()) {
            if (!this.__screenshareSlot.isAvailable()) {
                this.__screenshareSlot.detach();
                this.__screenshareSlot = new ATSlot(screenshareId);
            }
            this.__screenshareSlot.attach(call, stream);
            this.showScreenshare();
        } else {
            if (!this.__videoSlot.isAvailable()) {
                this.__videoSlot.detach();
                this.__videoSlot = new ATSlot(videoId);
            }
            this.__videoSlot.attach(call, stream);
            this.showVideo();
        }
    };

    this.showScreenshare = function() {
        this.__videoSlot.hide();
        this.__screenshareSlot.show();
    };
    this.showVideo = function() {
        this.__videoSlot.show();
        this.__screenshareSlot.hide();
    };
    this.detachScreenshare = function() {
        this.__screenshareSlot.detach();
        this.__screenshareSlot = null;
        this.showVideo();
    };
    this.detatchVideo = function() {
        this.__videoSlot.detach();
    };
    this.detach = function() {
        this.detachScreenshare();
        this.detatchVideo();
    };
}
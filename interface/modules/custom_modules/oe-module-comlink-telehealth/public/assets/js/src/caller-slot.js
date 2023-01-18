import {ATSlot} from "./at-slot.js";

export class CallerSlot {

    /**
     *
     * @type HTMLMediaElement
     * @private
     */
    __videoNode = null;

    /**
     *
     * @type HTMLVideoElement
     * @private
     */
    __screenshareNode = null;

    /**
     * @type ATSlot
     * @private
     */
    __videoSlot = null;

    /**
     *
     * @type ATSlot
     * @private
     */
    __screenshareSlot = null;

    /**
     *
     * @type ATSlot
     * @private
     */
    __currentSlot = null;

    __containerId = null;

    constructor(containerId, index) {
        let domNode = document.getElementById(containerId);
        let templateNode = document.getElementById('participant-list-template-node');

        if (!domNode) {
            throw new Error("Failed to find container id with " + containerId);
        }
        if (!templateNode) {
            throw new Error("Failed to find container id with " + 'participant-list-template-node');
        }
        let videoId = 'participant-video-' + index;
        let screenshareId = 'participant-screenshare-' + index;
        let videoNode = templateNode.cloneNode();
        videoNode.id = videoId;
        domNode.appendChild(videoNode);
        let screenshareNode = templateNode.cloneNode();
        screenshareNode.id = screenshareId;
        domNode.appendChild(screenshareNode);

        this.__videoSlot = new ATSlot(videoId);
        this.__videoNode = videoNode;
        this.__screenshareSlot = new ATSlot(screenshareId);
        this.__screenshareNode = screenshareNode;
        this.__currentSlot = this.__videoSlot;
    }

    getRemotePartyId() {
        return this.__currentSlot.getRemotePartyId();
    }

    hide() {
        if (this.__currentSlot) {
            this.__currentSlot.hide();
        }
    }
    show() {
        if (this.__currentSlot) {
            this.__currentSlot.show();
        }
    }

    isAvailable() {
        return this.__videoSlot.isAvailable();
    }

    isAvailableForCall(call) {
        // we say things are available if the remote id is the same as the caller remote party id
        // or if the video slot has no call
        if (this.__videoSlot.isAvailable() || this.__videoSlot.getRemotePartyId() == call.getRemotePartyId())
        {
            return true;
        }
        return false;
    }

    getCurrentCallStream() {
        if (this.__currentSlot ) {
            return this.__currentSlot .getVideoStream();
        }
        return null;
    }

    attach(call, stream) {
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
            }
            this.__screenshareSlot.attach(call, stream);
            this.showScreenshare();
        } else {
            if (!this.__videoSlot.isAvailable()) {
                this.__videoSlot.detach();
            }
            this.__videoSlot.attach(call, stream);
            this.showVideo();
        }
        // set ourselves up as the user data
        // TODO: @adunsulag I don't like how I setUserData here but clear it in the ATSlot object.
        call.setUserData(this);
    }

    showScreenshare() {
        this.__videoSlot.hide();
        this.__screenshareSlot.show();
        this.__currentSlot = this.__screenshareSlot;
    }

    showVideo() {
        this.__videoSlot.show();
        this.__screenshareSlot.hide();
        this.__currentSlot = this.__videoSlot;
    }

    detachScreenshare() {
        this.__screenshareSlot.detach();
        if (this.__videoSlot) {
            this.__videoSlot.show();
            this.__currentSlot = this.__videoSlot;
        }
    }

    detatchVideo() {
        this.__videoSlot.detach();
        this.__currentSlot = null;
    }

    detach() {
        this.detachScreenshare();
        this.detatchVideo();
    }

    destruct() {
        // do we really need a detach at this point?  We might as well just remove all of the nodes...
        // TODO: @adunsulag look at refactoring this so we only have to call destruct
        this.detach();
        if (this.__screenshareNode) {
            this.__screenshareNode.parentNode.removeChild(this.__screenshareNode);
            this.__screenshareNode = null;
        }
        if (this.__videoNode) {
            this.__videoNode.parentNode.removeChild(this.__videoNode);
            this.__videoNode = null;
        }
    }
}
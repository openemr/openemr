export class PresentationScreen
{
    /**
     *
     * @type HTMLVideoElement
     */
    videoElement = null;

    /**
     *
     * @type CallerSlot
     */
    callerSlot = null;

    constructor(domNodeId)
    {
        /**
         *
         * @type HTMLVideoElement
         */
        this.videoElement = document.getElementById(domNodeId);
        if (!this.videoElement) {
            throw new Error("Failed to find presentation screen dom node with id " + domNodeId);
        }
    }

    updateCallerSlotScreen() {
        if (this.callerSlot && this.callerSlot.getCurrentCallStream() != null) {
            this.videoElement.srcObject = this.callerSlot.getCurrentCallStream();
            this.videoElement.play(); // TODO: do we need this?
        }
    }

    attach(callerSlot) {
        if (this.callerSlot != null) {
            // nothing to do here, just return
            if (this.callerSlot === callerSlot
                || this.callerSlot.getRemotePartyId() == callerSlot.getRemotePartyId()) {
                return;
            }
        }
        // if we have something let's remove it.
        if (this.callerSlot) {
            this.detach();
        }

        if (callerSlot && callerSlot.getCurrentCallStream() != null) {
            let displayTitle = callerSlot.getParticipant() ? callerSlot.getParticipant().callerName : "";
            this.videoElement.srcObject = callerSlot.getCurrentCallStream();
            this.videoElement.play();
            this.videoElement.title = displayTitle;
            this.callerSlot = callerSlot;
        }
    }

    getVideoElement() {
        return this.videoElement;
    }

    detach() {
        this.callerSlot = null;
        this.videoElement.srcObject = null;
    }
}
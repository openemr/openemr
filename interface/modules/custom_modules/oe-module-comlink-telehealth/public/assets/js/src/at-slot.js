export function ATSlot(videoId) {
    /** @private */
    this.__call = null;

    /** @private */
    this.__container = document.getElementById(videoId);
    this.__video = this.__container.querySelector('.remote-video');
    if (!this.__video)
    {
        throw new Error("Failed to find #" + videoId + " element");
    }

    this.hasVideo = function() {
        return !this.isAvailable();
    };

    this.getVideoStream = function() {
        return this.__video.srcObject;
    };

    this.isAvailable = function() {
        return this.__call == null;
    };

    this.getRemotePartyId = function() {
        if (this.__call) {
            return this.__call.getRemotePartyId();
        }
        return null;
    };


    this.attach = function(call, stream, displayTitle) {
        this.__call = call;
        if (call == null || stream == null)
        {
            console.error("Call or stream were null.  Cannot proceed", {call: call, stream: stream});
            throw new Error("call and stream cannot be null");
        }
        // this.__call.setUserData(this);
        this.__video.srcObject = stream;
        this.__video.play();
        // for now we will just have the hover title be the display title for now.
        // eventually we could have this be a full DOM Node that is always displayed.
        let label = this.__container.querySelector('.participant-label');
        if (label) {
            label.textContent = displayTitle;
        }

        this.__video.title = displayTitle;
    };

    this.detach = function() {
        if (this.__call) {
            this.__call.setUserData(null);
        }
        this.__call = null;
        this.__video.srcObject = null;
    };

    this.hide = function() {
        if (this.__container) {
            this.__container.classList.add('d-none');
        }
    };
    this.show = function() {
        if (this.__container) {
            this.__container.classList.remove('d-none');
        }
    };

    this.setPinnedStatus = function(status) {
        if (this.__container) {
            if (status) {
                this.__container.classList.add('pinned');
            } else {
                this.__container.classList.remove('pinned');
            }
        }
    };

    this.destruct = function() {
        this.__container.re
    }
}
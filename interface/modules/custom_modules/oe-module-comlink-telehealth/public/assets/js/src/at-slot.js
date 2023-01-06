export function ATSlot(videoId) {
    /** @private */
    this.__call = null;

    /** @private */
    this.__video = document.getElementById(videoId);
    if (!this.__video)
    {
        throw new Error("Failed to find #" + videoId + " element");
    }

    this.isAvailable = function() {
        return this.__call == null;
    };

    this.getRemotePartyId = function() {
        if (this.__call) {
            return this.__call.remotePartyId();
        }
        return null;
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

    this.hide = function() {
        this.__video.classList.add('d-none');
    };
    this.show = function() {
        if (this.__video) {
            this.__video.classList.remove('d-none');
        }
    };
}
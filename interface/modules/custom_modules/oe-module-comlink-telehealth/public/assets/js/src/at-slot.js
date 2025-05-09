/**
 * Represents a single video stream slot in the DOM.  This can be a shared screen, or camera screen in the telehealth
 * session.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2023 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
export class ATSlot {

    /**
     *
     * @type CallerSlot
     * @private
     */
    __call = null;

    /**
     *
     * @type HTMLElement
     * @private
     */
    __container = null;

    /**
     *
     * @type HTMLMediaElement
     * @private
     */
    __video = null;

    constructor(videoId) {
        this.__call = null;
        this.__container = document.getElementById(videoId);
        this.__video = this.__container.querySelector('.participant-video');
        if (!this.__video)
        {
            throw new Error("Failed to find #" + videoId + " element");
        }
    }


    hasVideo() {
        return !this.isAvailable();
    }

    getVideoStream() {
        return this.__video.srcObject;
    }

    isAvailable() {
        return this.__call == null;
    }

    getRemotePartyId() {
        if (this.__call) {
            return this.__call.getRemotePartyId();
        }
        return null;
    }


    attach(call, stream, displayTitle) {
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
            label.textContent = jsText(displayTitle);
        }

        this.__video.title = jsText(displayTitle);
    }

    detach() {
        if (this.__call) {
            this.__call.setUserData(null);
        }
        this.__call = null;
        this.__video.srcObject = null;
    }

    hide() {
        if (this.__container) {
            this.__container.classList.add('d-none');
        }
    }
    show() {
        if (this.__container) {
            this.__container.classList.remove('d-none');
        }
    }

    setPinnedStatus(status) {
        if (this.__container) {
            if (status) {
                this.__container.classList.add('pinned');
            } else {
                this.__container.classList.remove('pinned');
            }
        }
    }
}
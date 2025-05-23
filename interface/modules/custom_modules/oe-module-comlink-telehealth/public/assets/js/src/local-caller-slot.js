/**
 * Javascript Controller for the local (current) user in the video session.  It imitates a regular caller slot so that
 * actions can be applied to both objects.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2023 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
export class LocalCallerSlot {

    /**
     *
     * @type HTMLElement
     * @private
     */
    __container = null;

    /**
     *
     * @type HTMLVideoElement
     * @private
     */
    __localVideoElement = null;

    /**
     *
     * @type {Array}
     * @private
     */
    __selectCallbacks = [];

    /**
     * The participant object
     * @type object
     * @private
     */
    __localParticipant = null;

    /**
     * True if the video is pinned
     * @type {boolean}
     * @private
     */
    __isPinned = false;

    constructor(container, participant) {
        this.__container = container;
        this.__localVideoElement = container.querySelector('.local-video');
        this.__selectCallbacks = [];
        this.__isPinned = false;
        this.__localParticipant = participant;

        container.addEventListener('click', this.handleSelectEvent.bind(this));
    }

    attach(stream) {
        this.__localVideoElement.srcObject = stream;
        this.__localVideoElement.play().catch(e => {
            console.error("Failed to play local video error object ", e);
        })
    }

    getRemotePartyId() {
        if (this.__localParticipant) {
            return this.__localParticipant.uuid;
        }
        return null;
    }

    getParticipant() {
        return this.__localParticipant;
    }

    getCurrentCallStream() {
        if (this.__localVideoElement) {
            return this.__localVideoElement.srcObject;
        }
        return null;
    }


    setMuted(isMuted) {
        this.__localVideoElement.muted = true;
    }

    hasMediaStream() {
        return this.__localVideoElement && this.__localVideoElement.srcObject != null;
    }

    stop() {
        if (this.__localVideoElement) {
            this.__localVideoElement.pause();
            this.__localVideoElement.srcObject = null;
        }
    }

    hide() {
        this.__container.classList.add('d-none');
    }

    show() {
        this.__container.classList.remove('d-none');
    }


    handleSelectEvent(evt) {
        this.__selectCallbacks.forEach(cb => cb(this));
    }

    addCallerSelectListener(callback) {
        this.__selectCallbacks.push(callback);
    }


    setPinnedStatus(status) {
        this.__isPinned = status;
        if (status) {
            this.__container.classList.add('pinned');
        } else {
            this.__container.classList.remove('pinned');
        }
    }

    isPinned() {
        return this.__isPinned;
    }
}
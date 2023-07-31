/**
 * Javascript Controller for the minimized session window.  It handles both minimizing and maximizing of a conference
 * session room.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2023 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
import {VideoBar} from "./video-bar.js";

export class MinimizedConferenceRoom {
    __isMinimized = false;
    container = null;

    /**
     *
     * @type {HTMLElement}
     */
    participantList = null;

    /**
     *
     * @type {VideoBar}
     */
    videoBar = null;

    /**
     *
     * @type HTMLElement
     */
    minimizedConferenceNode = null;

    /**
     *
     * @type 'top-left'|'bottom-left'|'top-right'|'bottom-right'
     */
    defaultPosition = 'bottom-right';

    initialOffset = null;

    constructor(container, defaultSettings) {
        this.container = container;
        this.defaultPosition = defaultSettings.defaultPosition || 'bottom-right';
        if (defaultSettings.offset) {
            this.initialOffset = defaultSettings.offset;
        }
    }

    isMinimized() {
        return this.__isMinimized;
    }

    resetConferenceVideoBar(minimizedSettings) {
        if (this.videoBar) {
            this.videoBar.destruct();
            this.videoBar = null;
        }
        let conferenceVideoBar = this.minimizedConferenceNode.querySelector(".telehealth-button-bar");
        this.videoBar = new VideoBar(conferenceVideoBar, minimizedSettings);
        return this.videoBar;
    }

    minimizeConferenceRoom(minimizedVideoButtonSettings) {

        let className = "minimized-telehealth-video-template";
        let templateNode = this.container.querySelector("." + className);
        if (!templateNode) {
            throw new Error("Failed to find template node with class ." + className);
        }
        let template = templateNode.cloneNode(true);

        // grab every dom node in the waiting room that is not the patient video
        // grab the video and shrink it to bottom left window
        // shrink container to be the size of the video
        template.id = "minimized-telehealth-video";
        template.classList.remove('d-none');
        template.classList.remove(className);
        template.classList.add(this.defaultPosition);

        window.document.body.appendChild(template);
        this.minimizedConferenceNode = template;

        // let's grab the participant list and append it
        this.participantList = this.container.querySelector('.participant-list-container');
        template.prepend(this.participantList);
        if (this.participantList.dataset['classMinimize']) {
            this.participantList.className = this.participantList.dataset['classMinimize'];
        }

        if (this.initialOffset && window.setInteractorPosition) {
            // this is what the drag drop library does...
            window.setInteractorPosition(this.initialOffset.top, this.initialOffset.left, this.minimizedConferenceNode);
        }

        // now make the video container draggable
        if (window.initDragResize)
        {
            // let's initialize our drag action here.
            window.initDragResize();
        }

        this.__isMinimized = true;
    }

    getCurrentOffset() {
        let top = 0;
        let left = 0;
        top = this.minimizedConferenceNode.dataset.x;
        left = this.minimizedConferenceNode.dataset.y;
        return {
            top: top
            ,left: left
        };
    }

    maximizeConferenceRoom() {
        // remove the event listener
        var remoteVideoContainer = this.container.querySelector('.sidebar');

        // now let's move the video and cleanup the old container here
        // if (remoteVideo && remoteVideoContainer) {
        if (this.participantList && remoteVideoContainer) {

            // var oldContainer = remoteVideo.parentNode;
            var oldContainer = this.minimizedConferenceNode;
            this.videoBar.destruct();
            // remoteVideoContainer.prepend(remoteVideo);
            remoteVideoContainer.appendChild(this.participantList);
            if (this.participantList.dataset['classMaximize']) {
                this.participantList.className = this.participantList.dataset['classMaximize'];
            }

            // need to clean up the original minimize container we created here
            if (oldContainer && oldContainer.parentNode)
            {
                oldContainer.parentNode.removeChild(oldContainer);
            }
            this.minimizedConferenceNode = null;
        } else {
            console.error("Failed to find remote video or remote video container");
        }
        this.__isMinimized = false;
    }

    destruct() {
        // clean up our node pointers
        this.participantList = null;
        this.container = null;

        // clean up minimized elements now too if we have them.
        let minimizedContainer = document.getElementById('minimized-telehealth-video');
        if (minimizedContainer && minimizedContainer.parentNode)
        {
            minimizedContainer.parentNode.removeChild(minimizedContainer);
        }
        this.__isMinimized = false;
    }
}

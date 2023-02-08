import {VideoBar} from "./video-bar.js";

export class MinimizedConferenceRoom {
    __isMinimized = false;
    container = null;
    constructor(container) {
        this.container = container;
    }

    isMinimized() {
        return this.__isMinimized;
    }

    minimizeConferenceRoom(conf, videoElement) {

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

        window.document.body.appendChild(template);
        template.appendChild(videoElement);

        var oldButtonBar = this.container.querySelector('.telehealth-button-bar');
        var clonedButtonBar = oldButtonBar.cloneNode(true);

        template.appendChild(clonedButtonBar);

        // now destruct the old button
        conf.videoBar.destruct();
        conf.videoBar = new VideoBar(clonedButtonBar, conf.getMinimizedConferenceVideoBarSettings());

        conf.waitingRoomModal.hide();

        // now make the video container draggable
        if (window.initDragResize)
        {
            // let's initialize our drag action here.
            window.initDragResize();
        }

        this.__isMinimized = true;
    }

    maximizeConferenceRoom(conf, videoElement) {
        // remove the event listener
        var remoteVideoContainer = document.querySelector('.remote-video-container');
        var remoteVideo = videoElement;

        // now let's move the video and cleanup the old container here
        if (remoteVideo && remoteVideoContainer) {

            var oldContainer = remoteVideo.parentNode;
            var oldButtonBar = oldContainer.querySelector('.telehealth-button-bar');
            if (oldButtonBar) {
                conf.videoBar.destruct();
                oldButtonBar.parentNode.removeChild(oldButtonBar);
            }


            if (remoteVideoContainer) {
                var newButtonBar = remoteVideoContainer.querySelector('.telehealth-button-bar');
                if (newButtonBar) {
                    conf.videoBar = new VideoBar(newButtonBar, conf.getFullConferenceVideoBarSettings());
                } else {
                    console.error("Failed to find #remote-video-container .telehealth-button-bar");
                }
            } else {
                console.error("Failed to find #remote-video-container");
            }
            remoteVideoContainer.prepend(remoteVideo);

            // need to clean up the original minimize container we created here
            if (oldContainer && oldContainer.parentNode)
            {
                oldContainer.parentNode.removeChild(oldContainer);
            }
        } else {
            console.error("Failed to find remote video or remote video container");
        }
    }

    destruct() {

        // clean up minimized elements now too if we have them.
        let minimizedContainer = document.getElementById('minimized-telehealth-video');
        if (minimizedContainer && minimizedContainer.parentNode)
        {
            minimizedContainer.parentNode.removeChild(minimizedContainer);
        }
        this.__isMinimized = false;
    }
}
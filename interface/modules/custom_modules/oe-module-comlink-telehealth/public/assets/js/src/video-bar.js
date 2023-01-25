import {VideoBarButton} from "./video-bar-button.js";

export function VideoBar(container, options)
{
    var bar = this;
    /**
     * @var VideoBarButton[]
     */
    bar.__buttons = {};

    /**
     * @var HTMLElement
     */
    bar.__container = container;

    function noop() {}

    function init() {
        options = options || {};
        setDefaultValue(options,'notes', false);
        setDefaultValue(options,'notesCallback', noop);

        setDefaultValue(options,'microphone', true);
        setDefaultValue(options,'microphoneCallback', noop);
        setDefaultValue(options,'video', true);
        setDefaultValue(options,'videoCallback', noop);
        setDefaultValue(options,'expand', false);
        setDefaultValue(options,'expandCallback', noop);
        setDefaultValue(options,'hangup', false);
        setDefaultValue(options,'hangupCallback', noop);
        setDefaultValue(options,'screenshare', true);
        setDefaultValue(options,'screenshareCallback', noop);
        setDefaultValue(options,'configure', false);
        setDefaultValue(options,'configureCallback', noop);


        let btns = ['notes', 'microphone', 'video', 'expand', 'hangup', 'screenshare', 'configure'];
        btns.forEach(btn => {
            let node = bar.__container.querySelector(".telehealth-btn-" + btn);
            let callback = options[btn + 'Callback'] || noop;

            if (!node)
            {
                console.error("Failed to find node for telehealth-btn-" + btn);
                return;
            }
            bar.__buttons[btn] = new VideoBarButton(node, options[btn], callback);
        });

        // we always make sure our microphone and video is displayed, but we swap the icons if they are disabled.
        if (!bar.__buttons['microphone'].enabled)
        {
            let btn = bar.__buttons['microphone'];
            let node = btn.node.querySelector('.fa');
            if (btn && node) {
                btn.toggle();
                node.classList.add('fa-microphone-slash');
                node.classList.remove('fa-microphone');
            } else {
                console.error("Failed to find microphone node and fa icon");
            }
        }
        if (!bar.__buttons['video'].enabled)
        {
            let btn = bar.__buttons['video'];
            let node = btn.node.querySelector('.fa');
            if (btn && node) {
                btn.toggle();
                node.classList.add('fa-video-slash');
                node.classList.remove('fa-video');
            } else {
                console.error("Failed to find video node and fa icon");
            }
        }
    }

    function destruct()
    {
        Object.values(bar.__buttons).forEach(button => button.detatch());
        bar.__container = null;
    }

    function setDefaultValue(obj, property, value)
    {
        if (!obj.hasOwnProperty(property))
        {
            obj[property] = value;
        }
    }

    function toggleButtons(toggleBtns)
    {
        toggleBtns.forEach(btn => {
            if (bar.__buttons[btn])
            {
                bar.__buttons[btn].toggle();
            }
            else
            {
                console.error('Failed to find button to toggle for button ' + btn);
            }

        });
    }


    bar.init = init;
    bar.destruct = destruct;
    bar.toggleButtons = toggleButtons;

    bar.init();
    return bar;
}
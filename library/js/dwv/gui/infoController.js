/* eslint-disable no-use-before-define */
/* eslint-disable no-var */

// namespaces
var dwv = dwv || {};
var dwvOemr = dwvOemr || {};
dwvOemr.gui = dwvOemr.gui || {};
dwvOemr.gui.info = dwvOemr.gui.info || {};

/**
 * DICOM Header overlay info controller.
 * @constructor
 * @param {Object} app The assciated app.
 * @param {String} containerDivId The id of the container div.
 */
dwvOemr.gui.info.Controller = function (app, containerDivId) {
    // Info layer overlay guis
    const overlayGuis = [];
    // flag to tell if guis have been created
    let guisCreated = false;
    // flag to tell if data was all laoded
    let loadEnd = false;

    // overlay data
    let overlayData = [];

    // flag to know if the info layer is listening on the image.
    let isInfoLayerListening = false;

    /**
     * Handle a new loaded item event.
     * @param {Object} event The load-item event.
     */
    this.onLoadItem = function (event) {
        // reset
        if (loadEnd) {
            overlayData = [];
            guisCreated = false;
            loadEnd = false;
        }
        // create and store overlay data
        const { data } = event;
        let dataUid = 0;
        // check if dicom data (x00020010: transfer syntax)
        if (typeof data.x00020010 !== 'undefined') {
            if (typeof data.x00080018 !== 'undefined') {
                // SOP instance UID
                dataUid = dwv.dicom.cleanString(data.x00080018.value[0]);
            } else {
                dataUid = overlayData.length;
            }
            overlayData[dataUid] = dwvOemr.gui.info.createOverlayData(
                new dwv.dicom.DicomElementsWrapper(data),
            );
        } else {
            // image file case
            dataUid = data[5].value;
            overlayData[dataUid] = dwvOemr.gui.info.createOverlayDataForDom(data);
        }

        for (let i = 0; i < overlayGuis.length; i += 1) {
            overlayGuis[i].setOverlayData(overlayData[dataUid]);
        }

        // create overlay guis if not done
        // TODO The first gui is maybe not the one disaplyed...
        if (!guisCreated) {
            for (let j = 0; j < overlayGuis.length; j += 1) {
                overlayGuis[j].create();
            }
            guisCreated = true;
        }
    };

    /**
     * Handle a load end event.
     * @param {Object} event The load-end event.
     */
    this.onLoadEnd = function () {
        loadEnd = true;
    };

    /**
     * Handle a changed slice event.
     * @param {Object} event The slice-change event.
     */
    function onSliceChange(event) {
        // change the overlay data to the one of the new slice
        const dataUid = event.data.imageUid;
        for (let i = 0; i < overlayGuis.length; i += 1) {
            overlayGuis[i].setOverlayData(overlayData[dataUid]);
        }
    }

    /**
     * Toggle info listeners.
     */
    this.toggleListeners = function () {
        if (overlayGuis.length === 0) {
            return;
        }

        if (isInfoLayerListening) {
            for (let n = 0; n < overlayGuis.length; n += 1) {
                app.removeEventListener('zoom-change', overlayGuis[n].update);
                app.removeEventListener('wl-width-change', overlayGuis[n].update);
                app.removeEventListener('wl-center-change', overlayGuis[n].update);
                app.removeEventListener('position-change', overlayGuis[n].update);
                app.removeEventListener('frame-change', overlayGuis[n].update);
            }
        } else {
            for (let n = 0; n < overlayGuis.length; n += 1) {
                app.addEventListener('zoom-change', overlayGuis[n].update);
                app.addEventListener('wl-width-change', overlayGuis[n].update);
                app.addEventListener('wl-center-change', overlayGuis[n].update);
                app.addEventListener('position-change', overlayGuis[n].update);
                app.addEventListener('frame-change', overlayGuis[n].update);
            }
        }
        // update flag
        isInfoLayerListening = !isInfoLayerListening;
    };

    /**
     * Get a HTML element associated to the application.
     * @param name The name or id to find.
     * @return The found element or null.
     */
    function getElement(name) {
        return dwvOemr.gui.getElement(containerDivId, name);
    }

    /**
     * Create the different info elements.
     */
    this.init = function () {
        // create overlay info at each corner
        const pos_list = ['tl', 'tc', 'tr', 'cl', 'cr', 'bl', 'bc', 'br'];

        for (let n = 0; n < pos_list.length; n += 1) {
            const pos = pos_list[n];
            const infoElement = getElement(`info${pos}`);
            if (infoElement) {
                overlayGuis.push(new dwvOemr.gui.info.Overlay(infoElement, pos));
            }
        }

        // listen to update data
        app.addEventListener('slice-change', onSliceChange);
        // first toggle: set to listening
        this.toggleListeners();
    };
}; // class dwvOemr.gui.info.Controller

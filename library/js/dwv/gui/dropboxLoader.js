// namespaces
var dwvOemr = dwvOemr || {};
dwvOemr.gui = dwvOemr.gui || {};

/**
 * Dropbox loader.
 * Listens to drag events on the layer container and
 * uses a drop box element as first display.
 * @constructor
 * @param {Object} app The associated application.
 */
dwvOemr.gui.DropboxLoader = function (app)
{
    // closure to self
    var self = this;

    // drop box class name
    var dropboxClassName = "dropBox";
    var borderClassName = "dropBoxBorder";
    var hoverClassName = "hover";

    // size of the drop box
    var dropBoxSize = 0;

    /**
     * Initialise the drop box.
     */
    this.init = function () {
        // start listening to drag events on the layerContainer
        var layerDiv = app.getElement("layerContainer");
        if (layerDiv) {
            layerDiv.addEventListener("dragover", onDragOver);
            layerDiv.addEventListener("dragleave", onDragLeave);
            layerDiv.addEventListener("drop", onDrop);
        }
        // set the initial drop box size
        var box = app.getElement(dropboxClassName);
        if (box) {
            var size = app.getLayerContainerSize();
            dropBoxSize = 2 * size.height / 3;
            box.setAttribute(
                "style",
                "width:" + dropBoxSize + "px;height:" + dropBoxSize + "px"
            );
        }
    };

    /**
     * Hide the drop box gui.
     */
    this.hideDropboxElement = function () {
        var box = app.getElement(dropboxClassName);
        if (box) {
            // remove size
            box.removeAttribute("style");
            // remove border
            box.className = box.className.replace(" " + borderClassName, "");
            box.className = box.className.replace(" " + hoverClassName, "");
        }
    };

    /**
     * Show the drop box gui.
     */
    this.showDropboxElement = function () {
        var box = app.getElement(dropboxClassName);
        if (box) {
            // set size
            box.setAttribute(
                "style",
                "width:" + dropBoxSize + "px;height:" + dropBoxSize + "px"
            );
            // add border
            box.className += " " + borderClassName;
        }
    };

    /**
     * Handle a drag over.
     * @private
     * @param {Object} event The event to handle.
     */
    function onDragOver(event) {
        // prevent default handling
        event.stopPropagation();
        event.preventDefault();
        // update box border
        var box = app.getElement(borderClassName);
        if (box && box.className.indexOf(hoverClassName) === -1) {
            box.className += " " + hoverClassName;
        }
    }

    /**
     * Handle a drag leave.
     * @private
     * @param {Object} event The event to handle.
     */
    function onDragLeave(event) {
        // prevent default handling
        event.stopPropagation();
        event.preventDefault();
        // update box border
        var box = app.getElement(borderClassName);
        if (box && box.className.indexOf(hoverClassName) !== -1) {
            box.className = box.className.replace(" " + hoverClassName, "");
        }
    }

    /**
     * Handle a drop event.
     * @private
     * @param {Object} event The event to handle.
     */
    function onDrop(event) {
        // prevent default handling
        event.stopPropagation();
        event.preventDefault();
        // load files
        app.loadFiles(event.dataTransfer.files);
        // hide drop box
        self.hideDropboxElement();
    }

}; // dwvOemr.gui.dropboxLoader

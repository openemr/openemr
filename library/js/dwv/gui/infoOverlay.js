// namespaces
var dwv = dwv || {};
var dwvOemr = dwvOemr || {};
dwvOemr.gui = dwvOemr.gui || {};
dwvOemr.gui.info = dwvOemr.gui.info || {};

/**
 * DICOM Header overlay info layer.
 * @constructor
 * @param {Object} div The HTML element to add Header overlay info to.
 * @param {String} pos The string to specify the corner position. (tl,tc,tr,cl,cr,bl,bc,br)
 */
dwvOemr.gui.info.Overlay = function ( div, pos )
{
    var overlayData = null;

    /**
     * Set the overlay data.
     * @param {Object} data The overlay data for all positions.
     */
    this.setOverlayData = function (data) {
        overlayData = data[pos];
    };

    /**
     * Create the overlay info div.
     */
    this.create = function ()
    {
        // remove all <ul> elements from div
        dwvOemr.html.cleanNode(div);

        if (!overlayData) {
            return;
        }

        if (pos === "bc" || pos === "tc" ||
            pos === "cr" || pos === "cl") {
            div.textContent = overlayData[0].value;
        } else {
            // create <ul> element
            var ul = document.createElement("ul");

            var li;
            var value;
            for (var n=0; n < overlayData.length; ++n) {
                value = overlayData[n].value;
                if (value === "window-center") {
                    li = document.createElement("li");
                    li.className = "info-" + pos + "-window-center";
                    ul.appendChild(li);
                } else if (value === "window-width") {
                    li = document.createElement("li");
                    li.className = "info-" + pos + "-window-width";
                    ul.appendChild(li);
                } else if (value === "zoom") {
                    li = document.createElement("li");
                    li.className = "info-" + pos + "-zoom";
                    ul.appendChild(li);
                } else if (value === "offset") {
                    li = document.createElement("li");
                    li.className = "info-" + pos + "-offset";
                    ul.appendChild(li);
                } else if (value === "value") {
                    li = document.createElement("li");
                    li.className = "info-" + pos + "-value";
                    ul.appendChild(li);
                } else if (value === "position") {
                    li = document.createElement("li");
                    li.className = "info-" + pos + "-position";
                    ul.appendChild(li);
                } else if (value === "frame") {
                    li = document.createElement("li");
                    li.className = "info-" + pos + "-frame";
                    ul.appendChild(li);
                } else {
                    li = document.createElement("li");
                    li.className = "info-" + pos + "-" + n;
                    li.appendChild( document.createTextNode( value ) );
                    ul.appendChild(li);
                }
            }

            // append <ul> element before color map
            div.appendChild(ul);
        }
    };

    /**
     * Update the overlay info div.
     * @param {Object} event A change event.
     */
    this.update = function ( event )
    {
        if (!overlayData) {
            return;
        }

        if (pos === "bc" || pos === "tc" ||
            pos === "cr" || pos === "cl") {
            div.textContent = overlayData[0].value;
        } else {
            var li;
            var value;
            var format;
            for (var n=0; n < overlayData.length; ++n) {
                value = overlayData[n].value;
                format = overlayData[n].format;
                if (value === "window-center") {
                    if (event.type === "wl-center-change") {
                        li = div.getElementsByClassName("info-" + pos + "-window-center")[0];
                        dwvOemr.html.cleanNode(li);
                        var wcStr = dwv.utils.replaceFlags2( format, [Math.round(event.wc)] );
                        if (li) {
                            li.appendChild( document.createTextNode(wcStr) );
                        }
                    }
                } else if (value === "window-width") {
                    if (event.type === "wl-width-change") {
                        li = div.getElementsByClassName("info-" + pos + "-window-width")[0];
                        dwvOemr.html.cleanNode(li);
                        var wwStr = dwv.utils.replaceFlags2( format, [Math.round(event.ww)] );
                        if (li) {
                            li.appendChild( document.createTextNode(wwStr) );
                        }
                    }
                } else if (value === "zoom") {
                    if (event.type === "zoom-change") {
                        li = div.getElementsByClassName("info-" + pos + "-zoom")[0];
                        dwvOemr.html.cleanNode(li);
                        var zoom = Number(event.scale).toPrecision(3);
                        var zoomStr = dwv.utils.replaceFlags2( format, [zoom] );
                        if (li) {
                            li.appendChild( document.createTextNode( zoomStr ) );
                        }
                    }
                } else if (value === "offset") {
                    if (event.type === "zoom-change") {
                        li = div.getElementsByClassName("info-" + pos + "-offset")[0];
                        dwvOemr.html.cleanNode(li);
                        var offset = [ Number(event.cx).toPrecision(3),
                            Number(event.cy).toPrecision(3)];
                        var offStr = dwv.utils.replaceFlags2( format, offset );
                        if (li) {
                            li.appendChild( document.createTextNode( offStr ) );
                        }
                    }
                } else if (value === "value") {
                    if (event.type === "position-change") {
                        li = div.getElementsByClassName("info-" + pos + "-value")[0];
                        dwvOemr.html.cleanNode(li);
                        var valueStr = dwv.utils.replaceFlags2( format, [event.value] );
                        if (li) {
                            li.appendChild( document.createTextNode( valueStr ) );
                        }
                    }
                } else if (value === "position") {
                    if (event.type === "position-change") {
                        li = div.getElementsByClassName("info-" + pos + "-position")[0];
                        dwvOemr.html.cleanNode(li);
                        var posStr = dwv.utils.replaceFlags2( format, [event.i, event.j, event.k] );
                        if (li) {
                            li.appendChild( document.createTextNode( posStr ) );
                        }
                    }
                } else if (value === "frame") {
                    if (event.type === "frame-change") {
                        li = div.getElementsByClassName("info-" + pos + "-frame")[0];
                        dwvOemr.html.cleanNode(li);
                        var frameStr = dwv.utils.replaceFlags2( format, [event.frame] );
                        if (li) {
                            li.appendChild( document.createTextNode( frameStr ) );
                        }
                    }
                } else {
                    if (event.type === "position-change") {
                        li = div.getElementsByClassName("info-" + pos + "-" + n)[0];
                        dwvOemr.html.cleanNode(li);
                        if (li) {
                            li.appendChild( document.createTextNode( value ) );
                        }
                    }
                }
            }
        }
    };
}; // class dwvOemr.gui.info.Overlay

/**
 * Create overlay string array of the image in each corner
 * @param {Object} dicomElements DICOM elements of the image
 * @return {Array} Array of string to be shown in each corner
 */
dwvOemr.gui.info.createOverlayData = function (dicomElements)
{
    var overlays = {};
    var modality = dicomElements.getFromKey("x00080060");
    if (!modality){
        return overlays;
    }

    var omaps = dwvOemr.gui.info.overlayMaps;
    if (!omaps){
        return overlays;
    }
    var omap = omaps[modality] || omaps['*'];

    for (var n=0; omap[n]; n++){
        var value = omap[n].value;
        var tags = omap[n].tags;
        var format = omap[n].format;
        var pos = omap[n].pos;

        if (typeof tags !== "undefined" && tags.length !== 0) {
            // get values
            var values = [];
            for ( var i = 0; i < tags.length; ++i ) {
                values.push( dicomElements.getElementValueAsStringFromKey( tags[i] ) );
            }
            // format
            if (typeof format === "undefined" || format === null) {
                format = dwv.utils.createDefaultReplaceFormat( values );
            }
            value = dwv.utils.replaceFlags2( format, values );
        }

        if (!value || value.length === 0){
            continue;
        }

        // add value to overlays
        if (!overlays[pos]) {
            overlays[pos] = [];
        }
        overlays[pos].push({'value': value.trim(), 'format': format});
    }

    // (0020,0020) Patient Orientation
    var valuePO = dicomElements.getFromKey("x00200020");
    if (typeof valuePO !== "undefined" && valuePO !== null && valuePO.length == 2){
        var po0 = dwv.dicom.cleanString(valuePO[0]);
        var po1 = dwv.dicom.cleanString(valuePO[1]);
        overlays.cr = [{'value': po0}];
        overlays.cl = [{'value': dwv.dicom.getReverseOrientation(po0)}];
        overlays.bc = [{'value': po1}];
        overlays.tc = [{'value': dwv.dicom.getReverseOrientation(po1)}];
    }

    return overlays;
};

/**
 * Create overlay string array of the image in each corner
 * @param {Object} dicomElements DICOM elements of the image
 * @return {Array} Array of string to be shown in each corner
 */
dwvOemr.gui.info.createOverlayDataForDom = function (info)
{
    var overlays = {};
    var omaps = dwvOemr.gui.info.overlayMaps;
    if (!omaps){
        return overlays;
    }
    var omap = omaps.DOM;
    if (!omap){
        return overlays;
    }

    for (var n=0; omap[n]; n++){
        var value = omap[n].value;
        var tags = omap[n].tags;
        var format = omap[n].format;
        var pos = omap[n].pos;

        if (typeof tags !== "undefined" && tags.length !== 0) {
            // get values
            var values = [];
            for ( var i = 0; i < tags.length; ++i ) {
                for ( var j = 0; j < info.length; ++j ) {
                    if (tags[i] === info[j].name) {
                        values.push( info[j].value );
                    }
                }
            }
            // format
            if (typeof format === "undefined" || format === null) {
                format = dwv.utils.createDefaultReplaceFormat( values );
            }
            value = dwv.utils.replaceFlags2( format, values );
        }

        if (!value || value.length === 0){
            continue;
        }

        // add value to overlays
        if (!overlays[pos]) {
            overlays[pos] = [];
        }
        overlays[pos].push({'value': value.trim(), 'format': format});
    }

    return overlays;
};

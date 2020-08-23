// namespaces
var dwvOemr = dwvOemr || {};
dwvOemr.gui = dwvOemr.gui || {};
dwvOemr.gui.info = dwvOemr.gui.info || {};

/**
 * MiniColourMap info layer.
 * @constructor
 * @param {Object} div The HTML element to add colourMap info to.
 * @param {Object} app The associated application.
 */
dwvOemr.gui.info.MiniColourMap = function (div, app) {
    /**
     * Create the mini colour map info div.
     */
    this.create = function () {
        // clean div
        const elems = div.getElementsByClassName('colour-map-info');
        if (elems.length !== 0) {
            dwvOemr.html.removeNodes(elems);
        }
        // colour map
        const canvas = document.createElement('canvas');
        canvas.className = 'colour-map-info';
        canvas.width = 98;
        canvas.height = 10;
        // add canvas to div
        div.appendChild(canvas);
    };

    /**
     * Update the mini colour map info div.
     * @param {Object} event The windowing change event containing the new values.
     * Warning: expects the mini colour map div to exist (use after createMiniColourMap).
     */
    this.update = function (event) {
        const windowCenter = event.wc;
        const windowWidth = event.ww;
        // retrieve canvas and context
        const canvas = div.getElementsByClassName('colour-map-info')[0];
        const context = canvas.getContext('2d');
        // fill in the image data
        const colourMap = app.getViewController().getColourMap();
        const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
        // histogram sampling
        let c = 0;
        const minInt = app.getImage().getRescaledDataRange().min;
        const range = app.getImage().getRescaledDataRange().max - minInt;
        const incrC = range / canvas.width;
        // Y scale
        let y = 0;
        const yMax = 255;
        const yMin = 0;
        // X scale
        const xMin = windowCenter - 0.5 - (windowWidth - 1) / 2;
        const xMax = windowCenter - 0.5 + (windowWidth - 1) / 2;
        // loop through values
        let index;
        for (let j = 0; j < canvas.height; j += 1) {
            c = minInt;
            for (let i = 0; i < canvas.width; i += 1) {
                if (c <= xMin) {
                    y = yMin;
                } else if (c > xMax) {
                    y = yMax;
                } else {
                    y = ((c - (windowCenter - 0.5)) / (windowWidth - 1) + 0.5)
                        * (yMax - yMin) + yMin;
                    y = parseInt(y, 10);
                }
                index = (i + j * canvas.width) * 4;
                imageData.data[index] = colourMap.red[y];
                imageData.data[index + 1] = colourMap.green[y];
                imageData.data[index + 2] = colourMap.blue[y];
                imageData.data[index + 3] = 0xff;
                c += incrC;
            }
        }
        // put the image data in the context
        context.putImageData(imageData, 0, 0);
    };
}; // class dwvOemr.gui.info.MiniColourMap

// Inspired from umdjs
// See https://github.com/umdjs/umd/blob/master/templates/returnExports.js
(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define([
            'modernizr',
            'i18next',
            'i18nextXHRBackend',
            'i18nextBrowserLanguageDetector',
            'jszip',
            'konva',
            ''
        ], factory);
    } else if (typeof module === 'object' && module.exports) {
        // Node. Does not work with strict CommonJS, but
        // only CommonJS-like environments that support module.exports,
        // like Node.

        // i18next-xhr-backend: requires XMlHttpRequest
        // Konva: requires 'canvas' -> deactivated for now...
        // MagicWand: no package -> deactivated

        module.exports = factory(
            require('modernizr'),
            require('i18next'),
            require('i18next-xhr-backend'),
            require('i18next-browser-languagedetector'),
            require('jszip'),
            null,
            null
        );
    } else {
        // Browser globals (root is window)
        root.dwv = factory(
            root.Modernizr,
            root.i18next,
            root.i18nextXHRBackend,
            root.i18nextBrowserLanguageDetector,
            root.JSZip,
            root.Konva,
            root.MagicWand
        );
    }
}(this, function (
    Modernizr,
    i18next,
    i18nextXHRBackend,
    i18nextBrowserLanguageDetector,
    JSZip,
    Konva,
    MagicWand) {

    // similar to what browserify does but reversed
    //https://www.contentful.com/blog/2017/01/17/the-global-object-in-javascript/
    var window = typeof window !== 'undefined' ?
        window : typeof self !== 'undefined' ?
        self : typeof global !== 'undefined' ?
        global : {};

/** @namespace */
var dwv = dwv || {};

/**
 * Main application class.
 * @constructor
 */
dwv.App = function ()
{
    // Local object
    var self = this;

    // Image
    var image = null;
    // Original image
    var originalImage = null;
    // Image data array
    var imageData = null;
    // Image data width
    var dataWidth = 0;
    // Image data height
    var dataHeight = 0;
    // Is the data mono-slice?
    var isMonoSliceData = 0;

    // Default character set
    var defaultCharacterSet;

    // Container div id
    var containerDivId = null;
    // Display window scale
    var windowScale = 1;
    // Fit display to window flag
    var fitToWindow = false;
    // main scale
    var scale = 1;
    // zoom center
    var scaleCenter = {"x": 0, "y": 0};
    // translation
    var translation = {"x": 0, "y": 0};

    // View
    var view = null;
    // View controller
    var viewController = null;

    // Info layer controller
    var infoController = null;

    // Dicom tags gui
    var tagsGui = null;

    // Drawing list gui
    var drawListGui = null;

    // Image layer
    var imageLayer = null;

    // Draw controller
    var drawController = null;

    // Generic style
    var style = new dwv.html.Style();

    // Toolbox controller
    var toolboxController = null;

    // Loadbox
    var loadbox = null;
    // UndoStack
    var undoStack = null;

    // listeners
    var listeners = {};

    /**
     * Get the version of the application.
     * @return {String} The version of the application.
     */
    this.getVersion = function () { return "v0.21.0"; };

    /**
     * Get the image.
     * @return {Image} The associated image.
     */
    this.getImage = function () { return image; };
    /**
     * Set the view.
     * @param {Image} img The associated image.
     */
    this.setImage = function (img)
    {
        image = img;
        view.setImage(img);
    };
    /**
     * Restore the original image.
     */
    this.restoreOriginalImage = function ()
    {
        image = originalImage;
        view.setImage(originalImage);
    };
    /**
     * Get the image data array.
     * @return {Array} The image data array.
     */
    this.getImageData = function () { return imageData; };
    /**
     * Is the data mono-slice?
     * @return {Boolean} True if the data is mono-slice.
     */
    this.isMonoSliceData = function () { return isMonoSliceData; };

    /**
     * Get the main scale.
     * @return {Number} The main scale.
     */
    this.getScale = function () { return scale / windowScale; };

    /**
     * Get the window scale.
     * @return {Number} The window scale.
     */
    this.getWindowScale = function () { return windowScale; };

    /**
     * Get the scale center.
     * @return {Object} The coordinates of the scale center.
     */
    this.getScaleCenter = function () { return scaleCenter; };

    /**
     * Get the translation.
     * @return {Object} The translation.
     */
    this.getTranslation = function () { return translation; };

    /**
     * Get the view controller.
     * @return {Object} The controller.
     */
    this.getViewController = function () { return viewController; };

    /**
     * Get the image layer.
     * @return {Object} The image layer.
     */
    this.getImageLayer = function () { return imageLayer; };
    /**
     * Get the current draw layer.
     * @return {Object} The draw layer.
     */
    this.getCurrentDrawLayer = function () {
        return drawController.getCurrentDrawLayer();
    };
    /**
     * Get the draw stage.
     * @return {Object} The draw stage.
     */
    this.getDrawStage = function () {
        return drawController.getDrawStage();
     };

    /**
     * Get the app style.
     * @return {Object} The app style.
     */
    this.getStyle = function () { return style; };

    /**
     * Add a command to the undo stack.
     * @param {Object} The command to add.
     */
    this.addToUndoStack = function (cmd) {
        if ( undoStack !== null ) {
            undoStack.add(cmd);
        }
    };

    /**
     * Initialise the HTML for the application.
     */
    this.init = function ( config ) {
        containerDivId = config.containerDivId;
        // tools
        if ( config.tools && config.tools.length !== 0 ) {
            // setup the tool list
            var toolList = {};
            for ( var t = 0; t < config.tools.length; ++t ) {
                var toolName = config.tools[t];
                if ( toolName === "Draw" ) {
                    if ( config.shapes !== 0 ) {
                        // setup the shape list
                        var shapeList = {};
                        for ( var s = 0; s < config.shapes.length; ++s ) {
                            var shapeName = config.shapes[s];
                            var shapeFactoryClass = shapeName+"Factory";
                            if (typeof dwv.tool[shapeFactoryClass] !== "undefined") {
                                shapeList[shapeName] = dwv.tool[shapeFactoryClass];
                            }
                            else {
                                console.warn("Could not initialise unknown shape: "+shapeName);
                            }
                        }
                        toolList.Draw = new dwv.tool.Draw(this, shapeList);
                        toolList.Draw.addEventListener("draw-create", fireEvent);
                        toolList.Draw.addEventListener("draw-change", fireEvent);
                        toolList.Draw.addEventListener("draw-move", fireEvent);
                        toolList.Draw.addEventListener("draw-delete", fireEvent);
                    }
                }
                else if ( toolName === "Filter" ) {
                    if ( config.filters.length !== 0 ) {
                        // setup the filter list
                        var filterList = {};
                        for ( var f = 0; f < config.filters.length; ++f ) {
                            var filterName = config.filters[f];
                            if (typeof dwv.tool.filter[filterName] !== "undefined") {
                                filterList[filterName] = new dwv.tool.filter[filterName](this);
                            }
                            else {
                                console.warn("Could not initialise unknown filter: "+filterName);
                            }
                        }
                        toolList.Filter = new dwv.tool.Filter(filterList, this);
                        toolList.Filter.addEventListener("filter-run", fireEvent);
                        toolList.Filter.addEventListener("filter-undo", fireEvent);
                    }
                }
                else {
                    // default: find the tool in the dwv.tool namespace
                    var toolClass = toolName;
                    if (typeof dwv.tool[toolClass] !== "undefined") {
                        toolList[toolClass] = new dwv.tool[toolClass](this);
                        if (typeof toolList[toolClass].addEventListener !== "undefined") {
                            toolList[toolClass].addEventListener(fireEvent);
                        }
                    }
                    else {
                        console.warn("Could not initialise unknown tool: "+toolName);
                    }
                }
            }
            toolboxController = new dwv.ToolboxController();
            toolboxController.create(toolList, this);
        }
        // gui
        if ( config.gui ) {
            // tools
            if ( config.gui.indexOf("tool") !== -1 && toolboxController) {
                toolboxController.setup();
            }
            // load
            if ( config.gui.indexOf("load") !== -1 ) {
                var loaderList = {};
                for ( var l = 0; l < config.loaders.length; ++l ) {
                    var loaderName = config.loaders[l];
                    var loaderClass = loaderName + "Load";
                    // default: find the loader in the dwv.gui namespace
                    if (typeof dwv.gui[loaderClass] !== "undefined") {
                        loaderList[loaderName] = new dwv.gui[loaderClass](this);
                    }
                    else {
                        console.warn("Could not initialise unknown loader: "+loaderName);
                    }
                }
                loadbox = new dwv.gui.Loadbox(this, loaderList);
                loadbox.setup();
                var loaderKeys = Object.keys(loaderList);
                for ( var lk = 0; lk < loaderKeys.length; ++lk ) {
                    loaderList[loaderKeys[lk]].setup();
                }
                loadbox.displayLoader(loaderKeys[0]);
            }
            // undo
            if ( config.gui.indexOf("undo") !== -1 ) {
                undoStack = new dwv.tool.UndoStack(this);
                undoStack.setup();
            }
            // DICOM Tags
            if ( config.gui.indexOf("tags") !== -1 ) {
                tagsGui = new dwv.gui.DicomTags(this);
            }
            // Draw list
            if ( config.gui.indexOf("drawList") !== -1 ) {
                drawListGui = new dwv.gui.DrawList(this);
                // update list on draw events
                this.addEventListener("draw-create", drawListGui.update);
                this.addEventListener("draw-change", drawListGui.update);
                this.addEventListener("draw-delete", drawListGui.update);
            }
            // version number
            if ( config.gui.indexOf("version") !== -1 ) {
                dwv.gui.appendVersionHtml(this.getVersion());
            }
            // help
            if ( config.gui.indexOf("help") !== -1 ) {
                var isMobile = true;
                if ( config.isMobile !== "undefined" ) {
                    isMobile = config.isMobile;
                }
                dwv.gui.appendHelpHtml( toolboxController.getToolList(), isMobile, this );
            }
        }

        // listen to drag&drop
        var box = this.getElement("dropBox");
        if ( box ) {
            box.addEventListener("dragover", onDragOver);
            box.addEventListener("dragleave", onDragLeave);
            box.addEventListener("drop", onDrop);
            // initial size
            var size = dwv.gui.getWindowSize();
            var dropBoxSize = 2 * size.height / 3;
            box.setAttribute("style","width:"+dropBoxSize+"px;height:"+dropBoxSize+"px");
        }

        // possible load from URL
        if ( typeof config.skipLoadUrl === "undefined" ) {
            var query = dwv.utils.getUriQuery(window.location.href);
            // check query
            if ( query && typeof query.input !== "undefined" ) {
                dwv.utils.decodeQuery(query, this.onInputURLs);
                // optional display state
                if ( typeof query.state !== "undefined" ) {
                    var onLoadEnd = function (/*event*/) {
                        loadStateUrl(query.state);
                    };
                    this.addEventListener( "load-end", onLoadEnd );
                }
            }
        }
        else{
            console.log("Not loading url from address since skipLoadUrl is defined.");
        }

        // align layers when the window is resized
        if ( config.fitToWindow ) {
            fitToWindow = true;
            window.onresize = this.onResize;
        }

        // default character set
        if ( typeof config.defaultCharacterSet !== "undefined" ) {
            defaultCharacterSet = config.defaultCharacterSet;
        }
    };

    /**
     * Get a HTML element associated to the application.
     * @param name The name or id to find.
     * @return The found element or null.
     */
     this.getElement = function (name)
     {
         return dwv.gui.getElement(containerDivId, name);
     };

    /**
     * Reset the application.
     */
    this.reset = function ()
    {
        // clear tools
        if ( toolboxController ) {
            toolboxController.reset();
        }
        // clear draw
        if ( drawController ) {
            drawController.reset();
        }
        // clear objects
        image = null;
        view = null;
        isMonoSliceData = false;
        // reset undo/redo
        if ( undoStack ) {
            undoStack = new dwv.tool.UndoStack(this);
            undoStack.initialise();
        }
    };

    /**
     * Reset the layout of the application.
     */
    this.resetLayout = function () {
        var previousScale = scale;
        var previousSC = scaleCenter;
        var previousTrans = translation;
        // reset values
        scale = windowScale;
        scaleCenter = {"x": 0, "y": 0};
        translation = {"x": 0, "y": 0};
        // apply new values
        if ( imageLayer ) {
            imageLayer.resetLayout(windowScale);
            imageLayer.draw();
        }
        if ( drawController ) {
            drawController.resetStage(windowScale);
        }
        // fire events
        if (previousScale != scale) {
            fireEvent({"type": "zoom-change", "scale": scale, "cx": scaleCenter.x, "cy": scaleCenter.y });
        }
        if ( (previousSC.x !== scaleCenter.x || previousSC.y !== scaleCenter.y) ||
             (previousTrans.x !== translation.x || previousTrans.y !== translation.y)) {
            fireEvent({"type": "offset-change", "scale": scale, "cx": scaleCenter.x, "cy": scaleCenter.y });
        }
    };

    /**
     * Add an event listener on the app.
     * @param {String} type The event type.
     * @param {Object} listener The method associated with the provided event type.
     */
    this.addEventListener = function (type, listener)
    {
        if ( typeof listeners[type] === "undefined" ) {
            listeners[type] = [];
        }
        listeners[type].push(listener);
    };

    /**
     * Remove an event listener from the app.
     * @param {String} type The event type.
     * @param {Object} listener The method associated with the provided event type.
     */
    this.removeEventListener = function (type, listener)
    {
        if( typeof listeners[type] === "undefined" ) {
            return;
        }
        for ( var i = 0; i < listeners[type].length; ++i )
        {
            if ( listeners[type][i] === listener ) {
                listeners[type].splice(i,1);
            }
        }
    };

    /**
     * Load a list of files. Can be image files or a state file.
     * @param {Array} files The list of files to load.
     */
    this.loadFiles = function (files)
    {
        // has been checked for emptiness.
        var ext = files[0].name.split('.').pop().toLowerCase();
        if ( ext === "json" ) {
            loadStateFile(files[0]);
        }
        else {
            loadImageFiles(files);
        }
    };

    /**
     * Load a list of image files.
     * @private
     * @param {Array} files The list of image files to load.
     */
    function loadImageFiles(files)
    {
        // create IO
        var fileIO = new dwv.io.FilesLoader();
        // load data
        loadImageData(files, fileIO);
    }

    /**
     * Load a State file.
     * @private
     * @param {String} file The state file to load.
     */
    function loadStateFile(file)
    {
        // create IO
        var fileIO = new dwv.io.FilesLoader();
        // load data
        loadStateData([file], fileIO);
    }

    /**
     * Load a list of URLs. Can be image files or a state file.
     * @param {Array} urls The list of urls to load.
     * @param {Array} requestHeaders An array of {name, value} to use as request headers.
     */
    this.loadURLs = function (urls, requestHeaders)
    {
        // has been checked for emptiness.
        var ext = urls[0].split('.').pop().toLowerCase();
        if ( ext === "json" ) {
            loadStateUrl(urls[0], requestHeaders);
        }
        else {
            loadImageUrls(urls, requestHeaders);
        }
    };

    /**
     * Load a list of image URLs.
     * @private
     * @param {Array} urls The list of urls to load.
     * @param {Array} requestHeaders An array of {name, value} to use as request headers.
     */
    function loadImageUrls(urls, requestHeaders)
    {
        // create IO
        var urlIO = new dwv.io.UrlsLoader();
        // create options
        var options = {'requestHeaders': requestHeaders};
        // load data
        loadImageData(urls, urlIO, options);
    }

    /**
     * Load a State url.
     * @private
     * @param {String} url The state url to load.
     * @param {Array} requestHeaders An array of {name, value} to use as request headers.
     */
    function loadStateUrl(url, requestHeaders)
    {
        // create IO
        var urlIO = new dwv.io.UrlsLoader();
        // create options
        var options = {'requestHeaders': requestHeaders};
        // load data
        loadStateData([url], urlIO, options);
    }

    /**
     * Load a list of image data.
     * @private
     * @param {Array} data Array of data to load.
     * @param {Object} loader The data loader.
     * @param {Object} options Options passed to the final loader.
     */
    function loadImageData(data, loader, options)
    {
        // clear variables
        self.reset();
        // first data name
        var firstName = "";
        if (typeof data[0].name !== "undefined") {
            firstName = data[0].name;
        } else {
            firstName = data[0];
        }
        // flag used by scroll to decide wether to activate or not
        // TODO: supposing multi-slice for zip files, could not be...
        isMonoSliceData = (data.length === 1 &&
            firstName.split('.').pop().toLowerCase() !== "zip");
        // set IO
        loader.setDefaultCharacterSet(defaultCharacterSet);
        loader.onload = function (data) {
            if ( image ) {
                view.append( data.view );
                if ( drawController ) {
                    drawController.appendDrawLayer(image.getNumberOfFrames());
                }
            }
            postLoadInit(data);
        };
        loader.onerror = function (error) { handleError(error); };
        loader.onloadend = function (/*event*/) {
            if ( drawController ) {
                drawController.activateDrawLayer(viewController);
            }
            fireEvent({type: "load-progress", lengthComputable: true,
                loaded: 100, total: 100});
            fireEvent({ 'type': 'load-end' });
        };
        loader.onprogress = onLoadProgress;
        // main load (asynchronous)
        fireEvent({ 'type': 'load-start' });
        loader.load(data, options);
    }

    /**
     * Load a State data.
     * @private
     * @param {Array} data Array of data to load.
     * @param {Object} loader The data loader.
     * @param {Object} options Options passed to the final loader.
     */
    function loadStateData(data, loader, options)
    {
        // set IO
        loader.onload = function (data) {
            // load state
            var state = new dwv.State(self);
            state.fromJSON(data);
        };
        loader.onerror = function (error) { handleError(error); };
        // main load (asynchronous)
        loader.load(data, options);
    }

    /**
     * Fit the display to the given size. To be called once the image is loaded.
     */
    this.fitToSize = function (size)
    {
        // previous width
        var oldWidth = parseInt(windowScale*dataWidth, 10);
        // find new best fit
        windowScale = Math.min( (size.width / dataWidth), (size.height / dataHeight) );
        // new sizes
        var newWidth = parseInt(windowScale*dataWidth, 10);
        var newHeight = parseInt(windowScale*dataHeight, 10);
        // ratio previous/new to add to zoom
        var mul = newWidth / oldWidth;
        scale *= mul;

        // update style
        style.setScale(windowScale);

        // resize container
        var container = this.getElement("layerContainer");
        container.setAttribute("style","width:"+newWidth+"px;height:"+newHeight+"px");
        // resize image layer
        if ( imageLayer ) {
            imageLayer.setWidth(newWidth);
            imageLayer.setHeight(newHeight);
            imageLayer.zoom(scale, scale, 0, 0);
            imageLayer.draw();
        }
        // resize draw stage
        if ( drawController ) {
            drawController.resizeStage(newWidth, newHeight, scale);
        }
    };

    /**
     * Toggle the display of the information layer.
     */
    this.toggleInfoLayerDisplay = function ()
    {
        // toggle html
        var infoLayer = self.getElement("infoLayer");
        dwv.html.toggleDisplay(infoLayer);
        // toggle listeners
        infoController.toggleListeners(self, view);
    };

    /**
     * Init the Window/Level display
     */
    this.initWLDisplay = function ()
    {
        // set window/level to first preset
        viewController.setWindowLevelPresetById(0);
        // default position
        viewController.setCurrentPosition2D(0,0);
        // default frame
        viewController.setCurrentFrame(0);
    };

    /**
     * Add canvas mouse and touch listeners.
     * @param {Object} canvas The canvas to listen to.
     */
    this.addToolCanvasListeners = function (layer)
    {
        toolboxController.addCanvasListeners(layer);
    };

    /**
     * Remove layer mouse and touch listeners.
     * @param {Object} canvas The canvas to stop listening to.
     */
    this.removeToolCanvasListeners = function (layer)
    {
        toolboxController.removeCanvasListeners(layer);
    };

    /**
     * Render the current image.
     */
    this.render = function ()
    {
        generateAndDrawImage();
    };

    /**
     * Zoom to the layers.
     * @param {Number} zoom The zoom to apply.
     * @param {Number} cx The zoom center X coordinate.
     * @param {Number} cy The zoom center Y coordinate.
     */
    this.zoom = function (zoom, cx, cy) {
        scale = zoom * windowScale;
        if ( scale <= 0.1 ) {
            scale = 0.1;
        }
        scaleCenter = {"x": cx, "y": cy};
        zoomLayers();
    };

    /**
     * Add a step to the layers zoom.
     * @param {Number} step The zoom step increment. A good step is of 0.1.
     * @param {Number} cx The zoom center X coordinate.
     * @param {Number} cy The zoom center Y coordinate.
     */
    this.stepZoom = function (step, cx, cy) {
        scale += step;
        if ( scale <= 0.1 ) {
            scale = 0.1;
        }
        scaleCenter = {"x": cx, "y": cy};
        zoomLayers();
    };

    /**
     * Apply a translation to the layers.
     * @param {Number} tx The translation along X.
     * @param {Number} ty The translation along Y.
     */
    this.translate = function (tx, ty)
    {
        translation = {"x": tx, "y": ty};
        translateLayers();
    };

    /**
     * Add a translation to the layers.
     * @param {Number} tx The step translation along X.
     * @param {Number} ty The step translation along Y.
     */
    this.stepTranslate = function (tx, ty)
    {
        var txx = translation.x + tx / scale;
        var tyy = translation.y + ty / scale;
        translation = {"x": txx, "y": tyy};
        translateLayers();
    };

    /**
     * Get the list of drawing display details.
     * @return {Object} The list of draw details including id, slice, frame...
     */
    this.getDrawDisplayDetails = function ()
    {
        return drawController.getDrawDisplayDetails();
    };
    /**
     * Get the list of drawings.
     * @return {Object} The list of drawings.
     */
    this.getDraws = function ()
    {
        return drawController.getDraws();
    };
    /**
     * Get a list of drawing store details.
     * @return {Object} A list of draw details including id, text, quant...
     */
    this.getDrawStoreDetails = function ()
    {
        return drawController.getDrawStoreDetails();
    };
    /**
     * Set the drawings on the current stage.
     * @param {Array} drawings An array of drawings.
     * @param {Array} drawingsDetails An array of drawings details.
     */
    this.setDrawings = function (drawings, drawingsDetails)
    {
        drawController.setDrawings(drawings, drawingsDetails, fireEvent, this.addToUndoStack);
    };
    /**
     * Update a drawing from its details.
     * @param {Object} drawDetails Details of the drawing to update.
     */
    this.updateDraw = function (drawDetails)
    {
        drawController.updateDraw(drawDetails);
    };
    /**
     * Delete all Draws from all layers.
    */
    this.deleteDraws = function () {
        drawController.deleteDraws(fireEvent, this.addToUndoStack);
    };
    /**
     * Check the visibility of a given group.
     * @param {Object} drawDetails Details of the drawing to check.
     */
    this.isGroupVisible = function (drawDetails)
    {
        return drawController.isGroupVisible(drawDetails);
    };
    /**
     * Toggle group visibility.
     * @param {Object} drawDetails Details of the drawing to update.
     */
    this.toogleGroupVisibility = function (drawDetails)
    {
        drawController.toogleGroupVisibility(drawDetails);
    };

    // Handler Methods -----------------------------------------------------------

    /**
     * Handle window/level change.
     * @param {Object} event The event fired when changing the window/level.
     */
    this.onWLChange = function (event)
    {
        // generate and draw if no skip flag
        if (typeof event.skipGenerate === "undefined" ||
            event.skipGenerate === false) {
            generateAndDrawImage();
        }
    };

    /**
     * Handle colour map change.
     * @param {Object} event The event fired when changing the colour map.
     */
    this.onColourChange = function (/*event*/)
    {
        generateAndDrawImage();
    };

    /**
     * Handle frame change.
     * @param {Object} event The event fired when changing the frame.
     */
    this.onFrameChange = function (/*event*/)
    {
        generateAndDrawImage();
        if ( drawController ) {
            drawController.activateDrawLayer(viewController);
        }
    };

    /**
     * Handle slice change.
     * @param {Object} event The event fired when changing the slice.
     */
    this.onSliceChange = function (/*event*/)
    {
        generateAndDrawImage();
        if ( drawController ) {
            drawController.activateDrawLayer(viewController);
        }
    };

    /**
     * Handle key down event.
     * - CRTL-Z: undo
     * - CRTL-Y: redo
     * - CRTL-ARROW_LEFT: next frame
     * - CRTL-ARROW_UP: next slice
     * - CRTL-ARROW_RIGHT: previous frame
     * - CRTL-ARROW_DOWN: previous slice
     * Default behavior. Usually used in tools.
     * @param {Object} event The key down event.
     */
    this.onKeydown = function (event)
    {
        if (event.ctrlKey) {
            if ( event.keyCode === 37 ) // crtl-arrow-left
            {
                event.preventDefault();
                self.getViewController().decrementFrameNb();
            }
            else if ( event.keyCode === 38 ) // crtl-arrow-up
            {
                event.preventDefault();
                self.getViewController().incrementSliceNb();
            }
            else if ( event.keyCode === 39 ) // crtl-arrow-right
            {
                event.preventDefault();
                self.getViewController().incrementFrameNb();
            }
            else if ( event.keyCode === 40 ) // crtl-arrow-down
            {
                event.preventDefault();
                self.getViewController().decrementSliceNb();
            }
            else if ( event.keyCode === 89 ) // crtl-y
            {
                undoStack.redo();
            }
            else if ( event.keyCode === 90 ) // crtl-z
            {
                undoStack.undo();
            }
        }
    };

    /**
     * Handle resize.
     * Fit the display to the window. To be called once the image is loaded.
     * @param {Object} event The change event.
     */
    this.onResize = function (/*event*/)
    {
        self.fitToSize(dwv.gui.getWindowSize());
    };

    /**
     * Handle zoom reset.
     * @param {Object} event The change event.
     */
    this.onZoomReset = function (/*event*/)
    {
        self.resetLayout();
    };

    /**
     * Handle loader change.
     * @param {Object} event The change event.
     */
    this.onChangeLoader = function (/*event*/)
    {
        // called from an HTML select, use its value
        loadbox.displayLoader( this.value );
    };

    /**
     * Reset the load box to its original state.
     */
    this.resetLoadbox = function ()
    {
        loadbox.reset();
    };

    /**
     * Handle change url event.
     * @param {Object} event The event fired when changing the url field.
     */
    this.onChangeURL = function (event)
    {
        self.loadURLs([event.target.value]);
    };

    /**
     * Handle input urls.
     * @param {Array} urls The list of input urls.
     * @param {Array} requestHeaders An array of {name, value} to use as request headers.
     */
    this.onInputURLs = function (urls, requestHeaders)
    {
        self.loadURLs(urls, requestHeaders);
    };

    /**
     * Handle change files event.
     * @param {Object} event The event fired when changing the file field.
     */
    this.onChangeFiles = function (event)
    {
        var files = event.target.files;
        if ( files.length !== 0 ) {
            self.loadFiles(files);
        }
    };

    /**
     * Handle state save event.
     * @param {Object} event The event fired when changing the state save field.
     */
    this.onStateSave = function (/*event*/)
    {
        var state = new dwv.State(self);
        // add href to link (html5)
        var element = self.getElement("download-state");
        element.href = "data:application/json," + state.toJSON();
    };

    /**
     * Handle colour map change.
     * @param {Object} event The change event.
     */
    this.onChangeColourMap = function (/*event*/)
    {
        // called from an HTML select, use its value
        viewController.setColourMapFromName(this.value);
    };

    /**
     * Handle window/level preset change.
     * @param {Object} event The change event.
     */
    this.onChangeWindowLevelPreset = function (/*event*/)
    {
        // value should be the name of the preset
        viewController.setWindowLevelPreset( this.value );
    };

    /**
     * Handle tool change.
     * @param {Object} event The change event.
     */
    this.onChangeTool = function (/*event*/)
    {
        // called from an HTML select, use its value
        toolboxController.setSelectedTool(this.value);
    };

    /**
     * Handle shape change.
     * @param {Object} event The change event.
     */
    this.onChangeShape = function (/*event*/)
    {
        // called from an HTML select, use its value
        toolboxController.setSelectedShape(this.value);
    };

    /**
     * Handle filter change.
     * @param {Object} event The change event.
     */
    this.onChangeFilter = function (/*event*/)
    {
        // called from an HTML select, use its value
        toolboxController.setSelectedFilter(this.value);
    };

    /**
     * Handle filter run.
     * @param {Object} event The run event.
     */
    this.onRunFilter = function (/*event*/)
    {
        toolboxController.runSelectedFilter();
    };

    /**
     * Handle line colour change.
     * @param {Object} event The change event.
     */
    this.onChangeLineColour = function (/*event*/)
    {
        // called from an HTML select, use its value
        toolboxController.setLineColour(this.value);
    };

    /**
     * Handle min/max slider change.
     * @param {Object} range The new range of the data.
     */
    this.onChangeMinMax = function (range)
    {
        toolboxController.setRange(range);
    };

    /**
     * Handle undo.
     * @param {Object} event The associated event.
     */
    this.onUndo = function (/*event*/)
    {
        undoStack.undo();
    };

    /**
     * Handle redo.
     * @param {Object} event The associated event.
     */
    this.onRedo = function (/*event*/)
    {
        undoStack.redo();
    };

    /**
     * Handle toggle of info layer.
     * @param {Object} event The associated event.
     */
    this.onToggleInfoLayer = function (/*event*/)
    {
        self.toggleInfoLayerDisplay();
    };

    /**
     * Handle display reset.
     * @param {Object} event The change event.
     */
    this.onDisplayReset = function (/*event*/)
    {
        self.resetLayout();
        self.initWLDisplay();
        // update preset select
        var select = self.getElement("presetSelect");
        if (select) {
            select.selectedIndex = 0;
            dwv.gui.refreshElement(select);
        }
    };


    // Private Methods -----------------------------------------------------------

    /**
     * Fire an event: call all associated listeners.
     * @param {Object} event The event to fire.
     */
    function fireEvent (event)
    {
        if ( typeof listeners[event.type] === "undefined" ) {
            return;
        }
        for ( var i = 0; i < listeners[event.type].length; ++i )
        {
            listeners[event.type][i](event);
        }
    }

    /**
     * Generate the image data and draw it.
     */
    function generateAndDrawImage()
    {
        // generate image data from DICOM
        view.generateImageData(imageData);
        // set the image data of the layer
        imageLayer.setImageData(imageData);
        // draw the image
        imageLayer.draw();
    }

    /**
     * Apply the stored zoom to the layers.
     */
    function zoomLayers()
    {
        // image layer
        if( imageLayer ) {
            imageLayer.zoom(scale, scale, scaleCenter.x, scaleCenter.y);
            imageLayer.draw();
        }
        // draw layer
        if( drawController ) {
            drawController.zoomStage(scale, scaleCenter);
        }
        // fire event
        fireEvent({"type": "zoom-change", "scale": scale, "cx": scaleCenter.x, "cy": scaleCenter.y });
    }

    /**
     * Apply the stored translation to the layers.
     */
    function translateLayers()
    {
        // image layer
        if( imageLayer ) {
            imageLayer.translate(translation.x, translation.y);
            imageLayer.draw();
            // draw layer
            if( drawController ) {
                var ox = - imageLayer.getOrigin().x / scale - translation.x;
                var oy = - imageLayer.getOrigin().y / scale - translation.y;
                drawController.translateStage(ox, oy);
            }
            // fire event
            fireEvent({"type": "offset-change", "scale": scale,
                "cx": imageLayer.getTrans().x, "cy": imageLayer.getTrans().y });
        }
    }

    /**
     * Handle a drag over.
     * @private
     * @param {Object} event The event to handle.
     */
    function onDragOver(event)
    {
        // prevent default handling
        event.stopPropagation();
        event.preventDefault();
        // update box
        var box = self.getElement("dropBox");
        if ( box ) {
            box.className = 'dropBox hover';
        }
    }

    /**
     * Handle a drag leave.
     * @private
     * @param {Object} event The event to handle.
     */
    function onDragLeave(event)
    {
        // prevent default handling
        event.stopPropagation();
        event.preventDefault();
        // update box
        var box = self.getElement("dropBox hover");
        if ( box ) {
            box.className = 'dropBox';
        }
    }

    /**
     * Handle a drop event.
     * @private
     * @param {Object} event The event to handle.
     */
    function onDrop(event)
    {
        // prevent default handling
        event.stopPropagation();
        event.preventDefault();
        // load files
        self.loadFiles(event.dataTransfer.files);
    }

    /**
     * Handle an error: display it to the user.
     * @private
     * @param {Object} error The error to handle.
     */
    function handleError(error)
    {
        // alert window
        if ( error.name && error.message) {
            alert(error.name+": "+error.message+".");
        }
        else {
            alert("Error: "+error+".");
        }
        // log
        if ( error.stack ) {
            console.error(error.stack);
        }
        // stop progress
        dwv.gui.displayProgress(100);
    }

    /**
     * Handle a load progress.
     * @private
     * @param {Object} event The event to handle.
     */
    function onLoadProgress(event)
    {
        fireEvent(event);
        if( event.lengthComputable )
        {
            var percent = Math.ceil((event.loaded / event.total) * 100);
            dwv.gui.displayProgress(percent);
        }
    }

    /**
     * Create the application layers.
     * @private
     * @param {Number} dataWidth The width of the input data.
     * @param {Number} dataHeight The height of the input data.
     */
    function createLayers(dataWidth, dataHeight)
    {
        // image layer
        var canImgLay = self.getElement("imageLayer");
        imageLayer = new dwv.html.Layer(canImgLay);
        imageLayer.initialise(dataWidth, dataHeight);
        imageLayer.fillContext();
        imageLayer.setStyleDisplay(true);
        // draw layer
        var drawDiv = self.getElement("drawDiv");
        if ( drawDiv ) {
            drawController = new dwv.DrawController(drawDiv);
            drawController.create(dataWidth, dataHeight);
        }
        // resize app
        if ( fitToWindow ) {
            self.fitToSize( dwv.gui.getWindowSize() );
        }
        else {
            self.fitToSize( {
                'width': self.getElement("layerContainer").offsetWidth,
                'height': self.getElement("layerContainer").offsetHeight } );
        }
        self.resetLayout();
    }

    /**
     * Post load application initialisation. To be called once the DICOM has been parsed.
     * @private
     * @param {Object} data The data to display.
     */
    function postLoadInit(data)
    {
        // only initialise the first time
        if ( view ) {
            return;
        }

        // get the view from the loaded data
        view = data.view;
        viewController = new dwv.ViewController(view);

        // append the DICOM tags table
        if ( tagsGui ) {
            tagsGui.update(data.info);
        }
        // store image
        originalImage = view.getImage();
        image = originalImage;

        // layout
        var size = image.getGeometry().getSize();
        dataWidth = size.getNumberOfColumns();
        dataHeight = size.getNumberOfRows();
        createLayers(dataWidth, dataHeight);

        // get the image data from the image layer
        imageData = imageLayer.getContext().createImageData(
                dataWidth, dataHeight);

        // image listeners
        view.addEventListener("wl-width-change", self.onWLChange);
        view.addEventListener("wl-center-change", self.onWLChange);
        view.addEventListener("colour-change", self.onColourChange);
        view.addEventListener("slice-change", self.onSliceChange);
        view.addEventListener("frame-change", self.onFrameChange);

        // connect with local listeners
        view.addEventListener("wl-width-change", fireEvent);
        view.addEventListener("wl-center-change", fireEvent);
        view.addEventListener("colour-change", fireEvent);
        view.addEventListener("position-change", fireEvent);
        view.addEventListener("slice-change", fireEvent);
        view.addEventListener("frame-change", fireEvent);

        // append draw layers (before initialising the toolbox)
        if ( drawController ) {
            drawController.appendDrawLayer(image.getNumberOfFrames());
        }

        // initialise the toolbox
        if ( toolboxController ) {
            toolboxController.initAndDisplay( imageLayer );
        }

        // stop box listening to drag (after first drag)
        var box = self.getElement("dropBox");
        if ( box ) {
            box.removeEventListener("dragover", onDragOver);
            box.removeEventListener("dragleave", onDragLeave);
            box.removeEventListener("drop", onDrop);
            dwv.html.removeNode(box);
            // switch listening to layerContainer
            var div = self.getElement("layerContainer");
            div.addEventListener("dragover", onDragOver);
            div.addEventListener("dragleave", onDragLeave);
            div.addEventListener("drop", onDrop);
        }

        // info layer
        var infoLayer = self.getElement("infoLayer");
        if ( infoLayer ) {
            infoController = new dwv.InfoController(containerDivId);
            infoController.create(self);
            infoController.toggleListeners(self, view);
        }

        // init W/L display
        self.initWLDisplay();
        // generate first image
        generateAndDrawImage();
    }

};

// namespaces
var dwv = dwv || {};
// external
var Konva = Konva || {};

/**
 * Draw controller.
 * @constructor
 * @param {Object} drawDiv The HTML div used to store the drawings.
 * @external Konva
 */
dwv.DrawController = function (drawDiv)
{

    // Draw stage
    var drawStage = null;
    // Draw layers: 2 dimension array: [slice][frame]
    var drawLayers = [];

    // current slice position
    var currentSlice = 0;
    // current frame position
    var currentFrame = 0;

    /**
     * Create the controller: sets up the draw stage.
     * @param {Number} width The width of the stage.
     * @param {Number} height The height of the stage.
     */
    this.create = function (width, height) {
        // create stage
        drawStage = new Konva.Stage({
            'container': drawDiv,
            'width': width,
            'height': height,
            'listening': false
        });
        // reset style
        // (avoids a not needed vertical scrollbar)
        drawStage.getContent().setAttribute("style", "");
    };

    /**
     * Get the current draw layer.
     * @return {Object} The draw layer.
     */
    this.getCurrentDrawLayer = function () {
        //return this.getDrawLayer(currentSlice, currentFrame);
        return drawLayers[currentSlice][currentFrame];
    };

    /**
     * Reset: clear the layers array.
     */
    this.reset = function () {
        drawLayers = [];
    };

    /**
     * Get the draw stage.
     * @return {Object} The draw layer.
     */
    this.getDrawStage = function () {
        return drawStage;
    };

    /**
     * Activate the current draw layer.
     * @param {Object} viewController The associated view controller.
     */
    this.activateDrawLayer = function (viewController)
    {
        // hide all draw layers
        for ( var k = 0, lenk = drawLayers.length; k < lenk; ++k ) {
            for ( var f = 0, lenf = drawLayers[k].length; f < lenf; ++f ) {
                drawLayers[k][f].visible( false );
            }
        }
        // set current position
        currentSlice = viewController.getCurrentPosition().k;
        currentFrame = viewController.getCurrentFrame();
        // show current draw layer
        var currentLayer = this.getCurrentDrawLayer();
        currentLayer.visible( true );
        currentLayer.draw();
    };

    /**
     * Reset the stage with a new window scale.
     * @param {Number} windowScale The window scale.
     */
    this.resetStage = function (windowScale) {
        drawStage.offset( {'x': 0, 'y': 0} );
        drawStage.scale( {'x': windowScale, 'y': windowScale} );
        drawStage.draw();
    };

    /**
     * Resize the current stage.
     * @param {Number} width the stage width.
     * @param {Number} height the stage height.
     * @param {Number} scale the stage scale.
     */
    this.resizeStage = function (width, height, scale) {
        // resize div
        drawDiv.setAttribute("style","width:"+width+"px;height:"+height+"px");
        // resize stage
        drawStage.setWidth(width);
        drawStage.setHeight(height);
        drawStage.scale( {'x': scale, 'y': scale} );
        drawStage.draw();
    };

    /**
     * Zoom the stage.
     * @param {Number} scale The scale factor.
     * @param {Object} scaleCenter The scale center point.
     */
    this.zoomStage = function (scale, scaleCenter) {
        // zoom
        var newScale = {'x': scale, 'y': scale};
        // offset
        // TODO different from the imageLayer offset?
        var oldScale = drawStage.scale();
        var oldOffset = drawStage.offset();
        var newOffsetX = (scaleCenter.x / oldScale.x) +
            oldOffset.x - (scaleCenter.x / newScale.x);
        var newOffsetY = (scaleCenter.y / oldScale.y) +
            oldOffset.y - (scaleCenter.y / newScale.y);
        var newOffset = {'x': newOffsetX, 'y': newOffsetY};
        // store
        drawStage.offset( newOffset );
        drawStage.scale( newScale );
        drawStage.draw();
    };

    /**
     * Translate the stage.
     * @param {Number} tx The X translation.
     * @param {Number} ty The Y translation.
     */
    this.translateStage = function (tx, ty) {
        drawStage.offset( {'x': tx, 'y': ty} );
        drawStage.draw();
    };

    /**
     * Append a new draw layer list to the list.
     * @param {Number} nLayers The size of the layers array to append to the current one.
     */
    this.appendDrawLayer = function (nLayers) {
        // add a new dimension
        drawLayers.push([]);
        // fill it
        for (var i = 0; i < nLayers; ++i) {
            // create draw layer
            var drawLayer = new Konva.Layer({
                'listening': false,
                'hitGraphEnabled': false,
                'visible': false
            });
            drawLayers[drawLayers.length - 1].push(drawLayer);
            // add the layer to the stage
            drawStage.add(drawLayer);
        }
    };

    /**
     * Get a list of drawing display details.
     * @return {Object} A list of draw details including id, slice, frame...
     */
    this.getDrawDisplayDetails = function ()
    {
        var list = [];
        for ( var k = 0, lenk = drawLayers.length; k < lenk; ++k ) {
            for ( var f = 0, lenf = drawLayers[k].length; f < lenf; ++f ) {
                var collec = drawLayers[k][f].getChildren();
                for ( var i = 0, leni = collec.length; i < leni; ++i ) {
                    var shape = collec[i].getChildren( isNodeNameShape )[0];
                    var label = collec[i].getChildren( isNodeNameLabel )[0];
                    var text = label.getChildren()[0];
                    var type = shape.className;
                    if (type === "Line") {
                        var shapeExtrakids = collec[i].getChildren( isNodeNameShapeExtra );
                        if (shape.closed()) {
                            type = "Roi";
                        } else if (shapeExtrakids.length !== 0) {
                            if ( shapeExtrakids[0].name().indexOf("triangle") !== -1 ) {
                                type = "Arrow";
                            }
                            else {
                                type = "Ruler";
                            }
                        }
                    }
                    if (type === "Rect") {
                        type = "Rectangle";
                    }
                    list.push( {
                        "id": collec[i].id(),
                        "slice": k,
                        "frame": f,
                        "type": type,
                        "color": shape.stroke(),
                        "label": text.textExpr,
                        "description": text.longText
                    });
                }
            }
        }
        // return
        return list;
    };

    /**
     * Get all the draws of the stage.
     */
    this.getDraws = function ()
    {
        var drawGroups = [];
        for ( var k = 0, lenk = drawLayers.length; k < lenk; ++k ) {
            drawGroups[k] = [];
            for ( var f = 0, lenf = drawLayers[k].length; f < lenf; ++f ) {
                // getChildren always return, so drawings will have the good size
                var groups = drawLayers[k][f].getChildren();
                drawGroups[k].push(groups);
            }
        }
        return drawGroups;
    };

    /**
     * Get a list of drawing store details.
     * @return {Object} A list of draw details including id, text, quant...
     * TODO Unify with getDrawDisplayDetails?
     */
    this.getDrawStoreDetails = function ()
    {
        var drawingsDetails = [];
        for ( var k = 0, lenk = drawLayers.length; k < lenk; ++k ) {
            drawingsDetails[k] = [];
            for ( var f = 0, lenf = drawLayers[k].length; f < lenf; ++f ) {
                // getChildren always return, so drawings will have the good size
                var groups = drawLayers[k][f].getChildren();
                var details = [];
                for ( var i = 0, leni = groups.length; i < leni; ++i ) {
                    // remove anchors
                    var anchors = groups[i].find(".anchor");
                    for ( var a = 0; a < anchors.length; ++a ) {
                        anchors[a].remove();
                    }
                    // get text
                    var texts = groups[i].find(".text");
                    if ( texts.length !== 1 ) {
                        console.warn("There should not be more than one text per shape.");
                    }
                    // get details (non konva vars)
                    details.push({
                        "id": groups[i].id(),
                        "textExpr": encodeURIComponent(texts[0].textExpr),
                        "longText": encodeURIComponent(texts[0].longText),
                        "quant": texts[0].quant
                    });
                }
                drawingsDetails[k].push(details);
            }
        }
        return drawingsDetails;
    };

    /**
     * Set the drawings on the current stage.
     * @param {Array} drawings An array of drawings.
     * @param {Array} drawingsDetails An array of drawings details.
     * @param {Object} cmdCallback The DrawCommand callback.
     * @param {Object} exeCallback The callback to call once the DrawCommand has been executed.
     */
    this.setDrawings = function (drawings, drawingsDetails, cmdCallback, exeCallback)
    {
        // loop through layers
        for ( var k = 0, lenk = drawLayers.length; k < lenk; ++k ) {
            for ( var f = 0, lenf = drawLayers[k].length; f < lenf; ++f ) {
                for ( var i = 0, leni = drawings[k][f].length; i < leni; ++i ) {
                    // create the group
                    var group = Konva.Node.create(drawings[k][f][i]);
                    var shape = group.getChildren( isNodeNameShape )[0];
                    // create the draw command
                    var cmd = new dwv.tool.DrawGroupCommand(
                        group, shape.className,
                        drawLayers[k][f] );
                    // draw command callbacks
                    cmd.onExecute = cmdCallback;
                    cmd.onUndo = cmdCallback;
                    // text (new in v0.2)
                    // TODO Verify ID?
                    if (drawingsDetails) {
                        var details = drawingsDetails[k][f][i];
                        var label = group.getChildren( isNodeNameLabel )[0];
                        var text = label.getText();
                        // store details
                        text.textExpr = details.textExpr;
                        text.longText = details.longText;
                        text.quant = details.quant;
                        // reset text (it was not encoded)
                        text.setText(dwv.utils.replaceFlags(text.textExpr, text.quant));
                    }
                    // execute
                    cmd.execute();
                    exeCallback(cmd);
                }
            }
        }
    };

    /**
     * Update a drawing from its details.
     * @param {Object} drawDetails Details of the drawing to update.
     */
    this.updateDraw = function (drawDetails)
    {
        // get the group
        var group = getDrawGroup(drawDetails.slice, drawDetails.frame, drawDetails.id);
        // shape
        var shapes = group.getChildren( isNodeNameShape );
        for (var i = 0; i < shapes.length; ++i ) {
            shapes[i].stroke(drawDetails.color);
        }
        // shape extra
        var shapesExtra = group.getChildren( isNodeNameShapeExtra );
        for (var j = 0; j < shapesExtra.length; ++j ) {
            if (typeof shapesExtra[j].stroke() !== "undefined") {
                shapesExtra[j].stroke(drawDetails.color);
            }
            else if (typeof shapesExtra[j].fill() !== "undefined") {
                shapesExtra[j].fill(drawDetails.color);
            }
        }
        // label
        var label = group.getChildren( isNodeNameLabel )[0];
        var text = label.getChildren()[0];
        text.fill(drawDetails.color);
        text.textExpr = drawDetails.label;
        text.longText = drawDetails.description;
        text.setText(dwv.utils.replaceFlags(text.textExpr, text.quant));

        // udpate current layer
        this.getCurrentDrawLayer().draw();
    };

    /**
     * Check the visibility of a given group.
     * @param {Object} drawDetails Details of the group to check.
     */
    this.isGroupVisible = function (drawDetails) {
        // get the group
        var group = getDrawGroup(drawDetails.slice, drawDetails.frame, drawDetails.id);
        // get visibility
        return group.isVisible();
    };

    /**
     * Toggle the visibility of a given group.
     * @param {Object} drawDetails Details of the group to update.
     */
    this.toogleGroupVisibility = function (drawDetails) {
        // get the group
        var group = getDrawGroup(drawDetails.slice, drawDetails.frame, drawDetails.id);
        // toggle visible
        group.visible(!group.isVisible());

        // udpate current layer
        this.getCurrentDrawLayer().draw();
    };

    /**
     * Delete all Draws from the stage.
     * @param {Object} cmdCallback The DeleteCommand callback.
     * @param {Object} exeCallback The callback to call once the DeleteCommand has been executed.
     */
    this.deleteDraws = function (cmdCallback, exeCallback) {
        var delcmd, layer, groups;
        for ( var k = 0, lenk = drawLayers.length; k < lenk; ++k ) {
            for ( var f = 0, lenf = drawLayers[k].length; f < lenf; ++f ) {
                layer = drawLayers[k][f];
                groups = layer.getChildren();
                while (groups.length) {
                    var shape = groups[0].getChildren( isNodeNameShape )[0];
                    delcmd = new dwv.tool.DeleteGroupCommand( groups[0],
                        dwv.tool.GetShapeDisplayName(shape), layer);
                    delcmd.onExecute = cmdCallback;
                    delcmd.onUndo = cmdCallback;
                    delcmd.execute();
                    exeCallback(delcmd);
                }
            }
        }
    };

    /**
     * Get a draw group.
     * @param {Number} slice The slice position.
     * @param {Number} frame The frame position.
     * @param {Number} id The group id.
     */
    function getDrawGroup(slice, frame, id) {
        var layer = drawLayers[slice][frame];
        //var collec = layer.getChildren()[drawDetails.id];
        var collec = layer.getChildren( function (node) {
            return node.id() === id;
        });

        var res = null;
        if (collec.length !== 0) {
            res = collec[0];
        }
        else {
            console.warn("Could not find draw group for slice='" +
                slice + "', frame='" + frame + "', id='" + id + "'.");
        }
        return res;
    }

    /**
     * Is an input node's name 'shape'.
     * @param {Object} node A Konva node.
     */
    function isNodeNameShape( node ) {
        return node.name() === "shape";
    }

    /**
     * Is a node an extra shape associated with a main one.
     * @param {Object} node A Konva node.
     */
    function isNodeNameShapeExtra( node ) {
        return node.name().startsWith("shape-");
    }

    /**
     * Is an input node's name 'label'.
     * @param {Object} node A Konva node.
     */
    function isNodeNameLabel( node ) {
        return node.name() === "label";
    }

}; // class dwv.DrawController

// namespaces
var dwv = dwv || {};

/**
 * Info controller.
 * @constructor
 */
dwv.InfoController = function (containerDivId)
{

    // Info layer plot gui
    var plotInfo = null;
    // Info layer colour map gui
    var miniColourMap = null;
	// Info layer overlay
	var overlayInfos = [];
    // flag to know if the info layer is listening on the image.
    var isInfoLayerListening = false;

    /**
     * Create the different info elements.
     * TODO Get rid of the app input arg...
     */
    this.create = function (app)
    {
        var infocm = getElement("infocm");
        if (infocm) {
            miniColourMap = new dwv.gui.info.MiniColourMap(infocm, app);
            miniColourMap.create();
        }

		// create overlay info at each corner
		var pos_list = [
			"tl", "tc", "tr",
			"cl",       "cr",
			"bl", "bc", "br" ];

		var num = 0;
		for (var n=0; n<pos_list.length; n++){
			var pos = pos_list[n];
			var info = getElement("info" + pos);
			if (info) {
				overlayInfos[num] = new dwv.gui.info.Overlay(info, pos, app);
				overlayInfos[num].create();
				num++;
			}
		}

        var plot = getElement("plot");
        if (plot) {
            plotInfo = new dwv.gui.info.Plot(plot, app);
            plotInfo.create();
        }
    };

    /**
     * Toggle info listeners to the app and the view.
     * @param {Object} app The app to listen or not to.
     * @param {Object} view The view to listen or not to.
     */
    this.toggleListeners = function (app, view)
    {
        if (isInfoLayerListening) {
            removeListeners(app, view);
        }
        else {
            addListeners(app, view);
        }
    };

    /**
     * Get a HTML element associated to the application.
     * @param name The name or id to find.
     * @return The found element or null.
     */
    function getElement(name)
    {
        return dwv.gui.getElement(containerDivId, name);
    }

    /**
     * Add info listeners to the view.
     * @param {Object} app The app to listen to.
     * @param {Object} view The view to listen to.
     */
    function addListeners(app, view)
    {
        if (plotInfo) {
            view.addEventListener("wl-width-change", plotInfo.update);
            view.addEventListener("wl-center-change", plotInfo.update);
        }
        if (miniColourMap) {
            view.addEventListener("wl-width-change", miniColourMap.update);
            view.addEventListener("wl-center-change", miniColourMap.update);
            view.addEventListener("colour-change", miniColourMap.update);
        }
		if (overlayInfos.length > 0){
			for (var n=0; n<overlayInfos.length; n++){
				app.addEventListener("zoom-change", overlayInfos[n].update);
				view.addEventListener("wl-width-change", overlayInfos[n].update);
                view.addEventListener("wl-center-change", overlayInfos[n].update);
				view.addEventListener("position-change", overlayInfos[n].update);
				view.addEventListener("frame-change", overlayInfos[n].update);
			}
		}
        // udpate listening flag
        isInfoLayerListening = true;
    }

    /**
     * Remove info listeners to the view.
     * @param {Object} app The app to stop listening to.
     * @param {Object} view The view to stop listening to.
     */
    function removeListeners(app, view)
    {
        if (plotInfo) {
            view.removeEventListener("wl-width-change", plotInfo.update);
            view.removeEventListener("wl-center-change", plotInfo.update);
        }
        if (miniColourMap) {
            view.removeEventListener("wl-width-change", miniColourMap.update);
            view.removeEventListener("wl-center-change", miniColourMap.update);
            view.removeEventListener("colour-change", miniColourMap.update);
        }
		if (overlayInfos.length > 0){
			for (var n=0; n<overlayInfos.length; n++){
				app.removeEventListener("zoom-change", overlayInfos[n].update);
                view.removeEventListener("wl-width-change", overlayInfos[n].update);
				view.removeEventListener("wl-center-change", overlayInfos[n].update);
				view.removeEventListener("position-change", overlayInfos[n].update);
				view.removeEventListener("frame-change", overlayInfos[n].update);
			}
		}
        // udpate listening flag
        isInfoLayerListening = false;
    }

}; // class dwv.InfoController

// namespaces
var dwv = dwv || {};

/**
 * State class.
 * Saves: data url/path, display info.
 * @constructor
 * @param {Object} app The associated application.
 */
dwv.State = function (app)
{
    /**
     * Save the application state as JSON.
     */
    this.toJSON = function () {
        // store each slice drawings group
        var drawings = app.getDraws();
        var drawingsDetails = app.getDrawStoreDetails();
        // return a JSON string
        return JSON.stringify( {
            "version": "0.2",
            "window-center": app.getViewController().getWindowLevel().center,
            "window-width": app.getViewController().getWindowLevel().width,
            "position": app.getViewController().getCurrentPosition(),
            "scale": app.getScale(),
            "scaleCenter": app.getScaleCenter(),
            "translation": app.getTranslation(),
            "drawings": drawings,
            // new in v0.2
            "drawingsDetails": drawingsDetails
        } );
    };
    /**
     * Load an application state from JSON.
     * @param {String} json The JSON representation of the state.
     */
    this.fromJSON = function (json) {
        var data = JSON.parse(json);
        if (data.version === "0.1") {
            readV01(data);
        }
        else if (data.version === "0.2") {
            readV02(data);
        }
        else {
            throw new Error("Unknown state file format version: '"+data.version+"'.");
        }
    };
    /**
     * Read an application state from an Object in v0.1 format.
     * @param {Object} data The Object representation of the state.
     */
    function readV01(data) {
        // display
        app.getViewController().setWindowLevel( data["window-center"], data["window-width"] );
        app.getViewController().setCurrentPosition( data.position );
        app.zoom( data.scale, data.scaleCenter.x, data.scaleCenter.y );
        app.translate( data.translation.x, data.translation.y );
        // drawings
        app.setDrawings( data.drawings, null );
    }
    /**
     * Read an application state from an Object in v0.2 format.
     * @param {Object} data The Object representation of the state.
     */
    function readV02(data) {
        // display
        app.getViewController().setWindowLevel( data["window-center"], data["window-width"] );
        app.getViewController().setCurrentPosition( data.position );
        app.zoom( data.scale, data.scaleCenter.x, data.scaleCenter.y );
        app.translate( data.translation.x, data.translation.y );
        // drawings
        app.setDrawings( data.drawings, data.drawingsDetails );
    }
}; // State class

// namespaces
var dwv = dwv || {};

/**
 * Toolbox controller.
 * @constructor
 */
dwv.ToolboxController = function ()
{
    // internal toolbox
    var toolbox = null;
    // point converter function
    var displayToIndexConverter = null;

    /**
     * Create the internal toolbox.
     * @param {Array} toolList The list of tools instances.
     * @param {Object} app The associated app.
     */
    this.create = function (toolList, app) {
        toolbox = new dwv.tool.Toolbox(toolList, app);
    };

    /**
     * Setup the internal toolbox.
     */
    this.setup = function () {
        toolbox.setup();
    };

    /**
     * Reset the internal toolbox.
     */
    this.reset = function () {
        toolbox.reset();
    };

    /**
     * Initialise and display the internal toolbox.
     */
    this.initAndDisplay = function (layer) {
        // initialise
        toolbox.init();
        // display
        toolbox.display(true);
        // TODO Would prefer to have this done in the addLayerListeners
        displayToIndexConverter = layer.displayToIndex;
        // add layer listeners
        this.addCanvasListeners(layer.getCanvas());
        // keydown listener
        window.addEventListener("keydown", onMouch, true);
    };

    /**
     * Get the tool list.
     */
    this.getToolList = function () {
        return toolbox.getToolList();
    };

    /**
     * Get the selected tool event handler.
     * @param {String} eventType The event type, for example mousedown, touchstart...
     */
    this.getSelectedToolEventHandler = function (eventType)
    {
        return toolbox.getSelectedTool()[eventType];
    };

    /**
     * Set the selected tool.
     * @param {String} name The name of the tool.
     */
    this.setSelectedTool = function (name)
    {
        toolbox.setSelectedTool(name);
    };

    /**
     * Set the selected shape.
     * @param {String} name The name of the shape.
     */
    this.setSelectedShape = function (name)
    {
        toolbox.getSelectedTool().setShapeName(name);
    };

    /**
     * Set the selected filter.
     * @param {String} name The name of the filter.
     */
    this.setSelectedFilter = function (name)
    {
        toolbox.getSelectedTool().setSelectedFilter(name);
    };

    /**
     * Run the selected filter.
     */
    this.runSelectedFilter = function ()
    {
        toolbox.getSelectedTool().getSelectedFilter().run();
    };

    /**
     * Set the tool line colour.
     * @param {String} colour The colour.
     */
    this.setLineColour = function (colour)
    {
        toolbox.getSelectedTool().setLineColour(colour);
    };

    /**
     * Set the tool range.
     * @param {Object} range The new range of the data.
     */
    this.setRange = function (range)
    {
        // seems like jquery is checking if the method exists before it
        // is used...
        if ( toolbox && toolbox.getSelectedTool() &&
                toolbox.getSelectedTool().getSelectedFilter() ) {
            toolbox.getSelectedTool().getSelectedFilter().run(range);
        }
    };

    /**
     * Add canvas mouse and touch listeners.
     * @param {Object} canvas The canvas to listen to.
     */
    this.addCanvasListeners = function (canvas)
    {
        // allow pointer events
        canvas.setAttribute("style", "pointer-events: auto;");
        // mouse listeners
        canvas.addEventListener("mousedown", onMouch);
        canvas.addEventListener("mousemove", onMouch);
        canvas.addEventListener("mouseup", onMouch);
        canvas.addEventListener("mouseout", onMouch);
        canvas.addEventListener("mousewheel", onMouch);
        canvas.addEventListener("DOMMouseScroll", onMouch);
        canvas.addEventListener("dblclick", onMouch);
        // touch listeners
        canvas.addEventListener("touchstart", onMouch);
        canvas.addEventListener("touchmove", onMouch);
        canvas.addEventListener("touchend", onMouch);
    };

    /**
     * Remove canvas mouse and touch listeners.
     * @param {Object} canvas The canvas to stop listening to.
     */
    this.removeCanvasListeners = function (canvas)
    {
        // disable pointer events
        canvas.setAttribute("style", "pointer-events: none;");
        // mouse listeners
        canvas.removeEventListener("mousedown", onMouch);
        canvas.removeEventListener("mousemove", onMouch);
        canvas.removeEventListener("mouseup", onMouch);
        canvas.removeEventListener("mouseout", onMouch);
        canvas.removeEventListener("mousewheel", onMouch);
        canvas.removeEventListener("DOMMouseScroll", onMouch);
        canvas.removeEventListener("dblclick", onMouch);
        // touch listeners
        canvas.removeEventListener("touchstart", onMouch);
        canvas.removeEventListener("touchmove", onMouch);
        canvas.removeEventListener("touchend", onMouch);
    };

    /**
     * Mou(se) and (T)ouch event handler. This function just determines the mouse/touch
     * position relative to the canvas element. It then passes it to the current tool.
     * @private
     * @param {Object} event The event to handle.
     */
    function onMouch(event)
    {
        // flag not to get confused between touch and mouse
        var handled = false;
        // Store the event position relative to the image canvas
        // in an extra member of the event:
        // event._x and event._y.
        var offsets = null;
        var position = null;
        if ( event.type === "touchstart" ||
            event.type === "touchmove")
        {
            // event offset(s)
            offsets = dwv.html.getEventOffset(event);
            // should have at least one offset
            event._xs = offsets[0].x;
            event._ys = offsets[0].y;
            position = displayToIndexConverter( offsets[0] );
            event._x = parseInt( position.x, 10 );
            event._y = parseInt( position.y, 10 );
            // possible second
            if ( offsets.length === 2 ) {
                event._x1s = offsets[1].x;
                event._y1s = offsets[1].y;
                position = displayToIndexConverter( offsets[1] );
                event._x1 = parseInt( position.x, 10 );
                event._y1 = parseInt( position.y, 10 );
            }
            // set handle event flag
            handled = true;
        }
        else if ( event.type === "mousemove" ||
            event.type === "mousedown" ||
            event.type === "mouseup" ||
            event.type === "mouseout" ||
            event.type === "mousewheel" ||
            event.type === "dblclick" ||
            event.type === "DOMMouseScroll" )
        {
            offsets = dwv.html.getEventOffset(event);
            event._xs = offsets[0].x;
            event._ys = offsets[0].y;
            position = displayToIndexConverter( offsets[0] );
            event._x = parseInt( position.x, 10 );
            event._y = parseInt( position.y, 10 );
            // set handle event flag
            handled = true;
        }
        else if ( event.type === "keydown" ||
                event.type === "touchend")
        {
            handled = true;
        }

        // Call the event handler of the curently selected tool.
        if ( handled )
        {
            if ( event.type !== "keydown" ) {
                event.preventDefault();
            }
            var func = toolbox.getSelectedTool()[event.type];
            if ( func )
            {
                func(event);
            }
        }
    }

}; // class dwv.ToolboxController

// namespaces
var dwv = dwv || {};

/**
 * View controller.
 * @constructor
 */
dwv.ViewController = function ( view )
{
    // closure to self
    var self = this;
    // Slice/frame player ID (created by setInterval)
    var playerID = null;

    /**
     * Get the window/level presets names.
     * @return {Array} The presets names.
     */
    this.getWindowLevelPresetsNames = function ()
    {
        return view.getWindowPresetsNames();
    };

    /**
     * Add window/level presets to the view.
     * @return {Object} The list of presets.
     */
    this.addWindowLevelPresets = function (presets)
    {
        return view.addWindowPresets(presets);
    };

    /**
     * Set the window level to the preset with the input name.
     * @param {String} name The name of the preset to activate.
     */
    this.setWindowLevelPreset = function (name)
    {
        view.setWindowLevelPreset(name);
    };

    /**
     * Set the window level to the preset with the input id.
     * @param {Number} id The id of the preset to activate.
     */
    this.setWindowLevelPresetById = function (id)
    {
        view.setWindowLevelPresetById(id);
    };

    /**
     * Check if the controller is playing.
     * @return {Boolean} True is the controler is playing slices/frames.
     */
    this.isPlaying = function () { return (playerID !== null); };

    /**
     * Get the current position.
     * @return {Object} The position.
      */
    this.getCurrentPosition = function ()
    {
        return view.getCurrentPosition();
    };

    /**
     * Set the current position.
     * @param {Object} pos The position.
     * @return {Boolean} False if not in bounds.
      */
    this.setCurrentPosition = function (pos)
    {
        return view.setCurrentPosition(pos);
    };

    /**
     * Set the current 2D (i,j) position.
     * @param {Number} i The column index.
     * @param {Number} j The row index.
     * @return {Boolean} False if not in bounds.
      */
    this.setCurrentPosition2D = function (i, j)
    {
        return view.setCurrentPosition({
            "i": i,
            "j": j,
            "k": view.getCurrentPosition().k
        });
    };

    /**
     * Set the current slice position.
     * @param {Number} k The slice index.
     * @return {Boolean} False if not in bounds.
      */
    this.setCurrentSlice = function (k)
    {
        return view.setCurrentPosition({
            "i": view.getCurrentPosition().i,
            "j": view.getCurrentPosition().j,
            "k": k
        });
    };

    /**
     * Increment the current slice number.
     * @return {Boolean} False if not in bounds.
     */
    this.incrementSliceNb = function ()
    {
        return self.setCurrentSlice( view.getCurrentPosition().k + 1 );
    };

    /**
     * Decrement the current slice number.
     * @return {Boolean} False if not in bounds.
     */
    this.decrementSliceNb = function ()
    {
        return self.setCurrentSlice( view.getCurrentPosition().k - 1 );
    };

    /**
     * Get the current frame.
     * @return {Number} The frame number.
      */
    this.getCurrentFrame = function ()
    {
        return view.getCurrentFrame();
    };

    /**
     * Set the current frame.
     * @param {Number} number The frame number.
     * @return {Boolean} False if not in bounds.
      */
    this.setCurrentFrame = function (number)
    {
        return view.setCurrentFrame(number);
    };

    /**
     * Increment the current frame.
     * @return {Boolean} False if not in bounds.
     */
    this.incrementFrameNb = function ()
    {
        return view.setCurrentFrame( view.getCurrentFrame() + 1 );
    };

    /**
     * Decrement the current frame.
     * @return {Boolean} False if not in bounds.
     */
    this.decrementFrameNb = function ()
    {
        return view.setCurrentFrame( view.getCurrentFrame() - 1 );
    };

    /**
     * Go to first slice .
     * @return {Boolean} False if not in bounds.
     * @deprecated Use the setCurrentSlice function.
     */
    this.goFirstSlice = function()
    {
        return view.setCurrentPosition({
            "i": view.getCurrentPosition().i,
            "j": view.getCurrentPosition().j,
            "k": 0
        });
    };

    /**
     *
     */
     this.play = function ()
     {
         if ( playerID === null ) {
             var nSlices = view.getImage().getGeometry().getSize().getNumberOfSlices();
             var nFrames = view.getImage().getNumberOfFrames();

             playerID = setInterval( function () {
                 if ( nSlices !== 1 ) {
                     if ( !self.incrementSliceNb() ) {
                         self.setCurrentSlice(0);
                     }
                 } else if ( nFrames !== 1 ) {
                     if ( !self.incrementFrameNb() ) {
                         self.setCurrentFrame(0);
                     }
                 }

             }, 300);
         } else {
             this.stop();
         }
     };

     /**
      *
      */
      this.stop = function ()
      {
          if ( playerID !== null ) {
              clearInterval(playerID);
              playerID = null;
          }
      };

    /**
     * Get the window/level.
     * @return {Object} The window center and width.
     */
    this.getWindowLevel = function ()
    {
        return {
            "width": view.getCurrentWindowLut().getWindowLevel().getWidth(),
            "center": view.getCurrentWindowLut().getWindowLevel().getCenter()
        };
    };

    /**
     * Set the window/level.
     * @param {Number} wc The window center.
     * @param {Number} ww The window width.
     */
    this.setWindowLevel = function (wc, ww)
    {
        view.setWindowLevel(wc,ww);
    };

    /**
     * Get the colour map.
     * @return {Object} The colour map.
     */
    this.getColourMap = function ()
    {
        return view.getColourMap();
    };

    /**
     * Set the colour map.
     * @param {Object} colourMap The colour map.
     */
    this.setColourMap = function (colourMap)
    {
        view.setColourMap(colourMap);
    };

    /**
     * Set the colour map from a name.
     * @param {String} name The name of the colour map to set.
     */
    this.setColourMapFromName = function (name)
    {
        // check if we have it
        if ( !dwv.tool.colourMaps[name] ) {
            throw new Error("Unknown colour map: '" + name + "'");
        }
        // enable it
        this.setColourMap( dwv.tool.colourMaps[name] );
    };

}; // class dwv.ViewController

// namespaces
var dwv = dwv || {};
/** @namespace */
dwv.dicom = dwv.dicom || {};

/**
 * Clean string: trim and remove ending.
 * @param {String} inputStr The string to clean.
 * @return {String} The cleaned string.
 */
dwv.dicom.cleanString = function (inputStr)
{
    var res = inputStr;
    if ( inputStr ) {
        // trim spaces
        res = inputStr.trim();
        // get rid of ending zero-width space (u200B)
        if ( res[res.length-1] === String.fromCharCode("u200B") ) {
            res = res.substring(0, res.length-1);
        }
    }
    return res;
};

/**
 * Is the Native endianness Little Endian.
 * @type Boolean
 */
dwv.dicom.isNativeLittleEndian = function ()
{
    return new Int8Array(new Int16Array([1]).buffer)[0] > 0;
};

/**
 * Get the utfLabel (used by the TextDecoder) from a character set term
 * References:
 * - DICOM [Value Encoding]{@link http://dicom.nema.org/dicom/2013/output/chtml/part05/chapter_6.html}
 * - DICOM [Specific Character Set]{@link http://dicom.nema.org/dicom/2013/output/chtml/part03/sect_C.12.html#sect_C.12.1.1.2}
 * - [TextDecoder#Parameters]{@link https://developer.mozilla.org/en-US/docs/Web/API/TextDecoder/TextDecoder#Parameters}
 */
dwv.dicom.getUtfLabel = function (charSetTerm)
{
    var label = "utf-8";
    if (charSetTerm === "ISO_IR 100" ) {
        label = "iso-8859-1";
    }
    else if (charSetTerm === "ISO_IR 101" ) {
        label = "iso-8859-2";
    }
    else if (charSetTerm === "ISO_IR 109" ) {
        label = "iso-8859-3";
    }
    else if (charSetTerm === "ISO_IR 110" ) {
        label = "iso-8859-4";
    }
    else if (charSetTerm === "ISO_IR 144" ) {
        label = "iso-8859-5";
    }
    else if (charSetTerm === "ISO_IR 127" ) {
        label = "iso-8859-6";
    }
    else if (charSetTerm === "ISO_IR 126" ) {
        label = "iso-8859-7";
    }
    else if (charSetTerm === "ISO_IR 138" ) {
        label = "iso-8859-8";
    }
    else if (charSetTerm === "ISO_IR 148" ) {
        label = "iso-8859-9";
    }
    else if (charSetTerm === "ISO_IR 13" ) {
        label = "shift-jis";
    }
    else if (charSetTerm === "ISO_IR 166" ) {
        label = "iso-8859-11";
    }
    else if (charSetTerm === "ISO 2022 IR 87" ) {
        label = "iso-2022-jp";
    }
    else if (charSetTerm === "ISO 2022 IR 149" ) {
        // not supported by TextDecoder when it says it should...
        //label = "iso-2022-kr";
    }
    else if (charSetTerm === "ISO 2022 IR 58") {
        // not supported by TextDecoder...
        //label = "iso-2022-cn";
    }
    else if (charSetTerm === "ISO_IR 192" ) {
        label = "utf-8";
    }
    else if (charSetTerm === "GB18030" ) {
        label = "gb18030";
    }
    else if (charSetTerm === "GB2312" ) {
        label = "gb2312";
    }
    else if (charSetTerm === "GBK" ) {
        label = "chinese";
    }
    return label;
};

/**
 * Data reader.
 * @constructor
 * @param {Array} buffer The input array buffer.
 * @param {Boolean} isLittleEndian Flag to tell if the data is little or big endian.
 */
dwv.dicom.DataReader = function (buffer, isLittleEndian)
{
    // Set endian flag if not defined.
    if ( typeof isLittleEndian === 'undefined' ) {
        isLittleEndian = true;
    }

    // Default text decoder
    var defaultTextDecoder = {};
    defaultTextDecoder.decode = function (buffer) {
        var result = "";
        for ( var i = 0, leni = buffer.length; i < leni; ++i ) {
            result += String.fromCharCode( buffer[ i ] );
        }
        return result;
    };
    // Text decoder
    var textDecoder = defaultTextDecoder;
    if (typeof window.TextDecoder !== "undefined") {
        textDecoder = new TextDecoder("iso-8859-1");
    }

    /**
     * Set the utfLabel used to construct the TextDecoder.
     * @param {String} label The encoding label.
     */
    this.setUtfLabel = function (label) {
        if (typeof window.TextDecoder !== "undefined") {
            textDecoder = new TextDecoder(label);
        }
    };

    /**
     * Is the Native endianness Little Endian.
     * @private
     * @type Boolean
     */
    var isNativeLittleEndian = dwv.dicom.isNativeLittleEndian();

    /**
     * Flag to know if the TypedArray data needs flipping.
     * @private
     * @type Boolean
     */
    var needFlip = (isLittleEndian !== isNativeLittleEndian);

    /**
     * The main data view.
     * @private
     * @type DataView
     */
    var view = new DataView(buffer);

    /**
     * Flip an array's endianness.
     * Inspired from [DataStream.js]{@link https://github.com/kig/DataStream.js}.
     * @param {Object} array The array to flip (modified).
     */
    this.flipArrayEndianness = function (array) {
       var blen = array.byteLength;
       var u8 = new Uint8Array(array.buffer, array.byteOffset, blen);
       var bpel = array.BYTES_PER_ELEMENT;
       var tmp;
       for ( var i = 0; i < blen; i += bpel ) {
         for ( var j = i + bpel - 1, k = i; j > k; j--, k++ ) {
           tmp = u8[k];
           u8[k] = u8[j];
           u8[j] = tmp;
         }
       }
    };

    /**
     * Read Uint16 (2 bytes) data.
     * @param {Number} byteOffset The offset to start reading from.
     * @return {Number} The read data.
     */
    this.readUint16 = function (byteOffset) {
        return view.getUint16(byteOffset, isLittleEndian);
    };
    /**
     * Read Uint32 (4 bytes) data.
     * @param {Number} byteOffset The offset to start reading from.
     * @return {Number} The read data.
     */
    this.readUint32 = function (byteOffset) {
        return view.getUint32(byteOffset, isLittleEndian);
    };
    /**
     * Read Int32 (4 bytes) data.
     * @param {Number} byteOffset The offset to start reading from.
     * @return {Number} The read data.
     */
    this.readInt32 = function (byteOffset) {
        return view.getInt32(byteOffset, isLittleEndian);
    };
    /**
     * Read Uint8 array.
     * @param {Number} byteOffset The offset to start reading from.
     * @param {Number} size The size of the array.
     * @return {Array} The read data.
     */
    this.readUint8Array = function (byteOffset, size) {
        return new Uint8Array(buffer, byteOffset, size);
    };
    /**
     * Read Int8 array.
     * @param {Number} byteOffset The offset to start reading from.
     * @param {Number} size The size of the array.
     * @return {Array} The read data.
     */
    this.readInt8Array = function (byteOffset, size) {
        return new Int8Array(buffer, byteOffset, size);
    };
    /**
     * Read Uint16 array.
     * @param {Number} byteOffset The offset to start reading from.
     * @param {Number} size The size of the array.
     * @return {Array} The read data.
     */
    this.readUint16Array = function (byteOffset, size) {
        var arraySize = size / Uint16Array.BYTES_PER_ELEMENT;
        var data = null;
        // byteOffset should be a multiple of Uint16Array.BYTES_PER_ELEMENT (=2)
        if ( (byteOffset % Uint16Array.BYTES_PER_ELEMENT) === 0 ) {
            data = new Uint16Array(buffer, byteOffset, arraySize);
            if ( needFlip ) {
                this.flipArrayEndianness(data);
            }
        }
        else {
            data = new Uint16Array(arraySize);
            for ( var i = 0; i < arraySize; ++i ) {
                data[i] = view.getInt16( (byteOffset +
                    Uint16Array.BYTES_PER_ELEMENT * i),
                    isLittleEndian);
            }
        }
        return data;
    };
    /**
     * Read Int16 array.
     * @param {Number} byteOffset The offset to start reading from.
     * @param {Number} size The size of the array.
     * @return {Array} The read data.
     */
    this.readInt16Array = function (byteOffset, size) {
        var arraySize = size / Int16Array.BYTES_PER_ELEMENT;
        var data = null;
        // byteOffset should be a multiple of Int16Array.BYTES_PER_ELEMENT (=2)
        if ( (byteOffset % Int16Array.BYTES_PER_ELEMENT) === 0 ) {
            data = new Int16Array(buffer, byteOffset, arraySize);
            if ( needFlip ) {
                this.flipArrayEndianness(data);
            }
        }
        else {
            data = new Int16Array(arraySize);
            for ( var i = 0; i < arraySize; ++i ) {
                data[i] = view.getInt16( (byteOffset +
                    Int16Array.BYTES_PER_ELEMENT * i),
                    isLittleEndian);
            }
        }
        return data;
    };
    /**
     * Read Uint32 array.
     * @param {Number} byteOffset The offset to start reading from.
     * @param {Number} size The size of the array.
     * @return {Array} The read data.
     */
    this.readUint32Array = function (byteOffset, size) {
        var arraySize = size / Uint32Array.BYTES_PER_ELEMENT;
        var data = null;
        // byteOffset should be a multiple of Uint32Array.BYTES_PER_ELEMENT (=4)
        if ( (byteOffset % Uint32Array.BYTES_PER_ELEMENT) === 0 ) {
            data = new Uint32Array(buffer, byteOffset, arraySize);
            if ( needFlip ) {
                this.flipArrayEndianness(data);
            }
        }
        else {
            data = new Uint32Array(arraySize);
            for ( var i = 0; i < arraySize; ++i ) {
                data[i] = view.getUint32( (byteOffset +
                    Uint32Array.BYTES_PER_ELEMENT * i),
                    isLittleEndian);
            }
        }
        return data;
    };
    /**
     * Read Int32 array.
     * @param {Number} byteOffset The offset to start reading from.
     * @param {Number} size The size of the array.
     * @return {Array} The read data.
     */
    this.readInt32Array = function (byteOffset, size) {
        var arraySize = size / Int32Array.BYTES_PER_ELEMENT;
        var data = null;
        // byteOffset should be a multiple of Int32Array.BYTES_PER_ELEMENT (=4)
        if ( (byteOffset % Int32Array.BYTES_PER_ELEMENT) === 0 ) {
            data = new Int32Array(buffer, byteOffset, arraySize);
            if ( needFlip ) {
                this.flipArrayEndianness(data);
            }
        }
        else {
            data = new Int32Array(arraySize);
            for ( var i = 0; i < arraySize; ++i ) {
                data[i] = view.getInt32( (byteOffset +
                    Int32Array.BYTES_PER_ELEMENT * i),
                    isLittleEndian);
            }
        }
        return data;
    };
    /**
     * Read Float32 array.
     * @param {Number} byteOffset The offset to start reading from.
     * @param {Number} size The size of the array.
     * @return {Array} The read data.
     */
    this.readFloat32Array = function (byteOffset, size) {
        var arraySize = size / Float32Array.BYTES_PER_ELEMENT;
        var data = null;
        // byteOffset should be a multiple of Float32Array.BYTES_PER_ELEMENT (=4)
        if ( (byteOffset % Float32Array.BYTES_PER_ELEMENT) === 0 ) {
            data = new Float32Array(buffer, byteOffset, arraySize);
            if ( needFlip ) {
                this.flipArrayEndianness(data);
            }
        }
        else {
            data = new Float32Array(arraySize);
            for ( var i = 0; i < arraySize; ++i ) {
                data[i] = view.getFloat32( (byteOffset +
                    Float32Array.BYTES_PER_ELEMENT * i),
                    isLittleEndian);
            }
        }
        return data;
    };
    /**
     * Read Float64 array.
     * @param {Number} byteOffset The offset to start reading from.
     * @param {Number} size The size of the array.
     * @return {Array} The read data.
     */
    this.readFloat64Array = function (byteOffset, size) {
        var arraySize = size / Float64Array.BYTES_PER_ELEMENT;
        var data = null;
        // byteOffset should be a multiple of Float64Array.BYTES_PER_ELEMENT (=8)
        if ( (byteOffset % Float64Array.BYTES_PER_ELEMENT) === 0 ) {
            data = new Float64Array(buffer, byteOffset, arraySize);
            if ( needFlip ) {
                this.flipArrayEndianness(data);
            }
        }
        else {
            data = new Float64Array(arraySize);
            for ( var i = 0; i < arraySize; ++i ) {
                data[i] = view.getFloat64( (byteOffset +
                    Float64Array.BYTES_PER_ELEMENT*i),
                    isLittleEndian);
            }
        }
        return data;
    };
    /**
     * Read data as an hexadecimal string.
     * @param {Number} byteOffset The offset to start reading from.
     * @return {Array} The read data.
     */
    this.readHex = function (byteOffset) {
        // read and convert to hex string
        var str = this.readUint16(byteOffset).toString(16);
        // return padded
        return "0x0000".substr(0, 6 - str.length) + str.toUpperCase();
    };

    /**
     * Read data as a string.
     * @param {Number} byteOffset The offset to start reading from.
     * @param {Number} nChars The number of characters to read.
     * @return {String} The read data.
     */
    this.readString = function (byteOffset, nChars) {
        var data = this.readUint8Array(byteOffset, nChars);
        return defaultTextDecoder.decode(data);
    };

    /**
     * Read data as a 'special' string, decoding it if the TextDecoder is available.
     * @param {Number} byteOffset The offset to start reading from.
     * @param {Number} nChars The number of characters to read.
     * @return {String} The read data.
     */
    this.readSpecialString = function (byteOffset, nChars) {
        var data = this.readUint8Array(byteOffset, nChars);
        return textDecoder.decode(data);
    };

};

/**
 * Get the group-element key used to store DICOM elements.
 * @param {Number} group The DICOM group.
 * @param {Number} element The DICOM element.
 * @return {String} The key.
 */
dwv.dicom.getGroupElementKey = function (group, element)
{
    return 'x' + group.substr(2,6) + element.substr(2,6);
};

/**
 * Split a group-element key used to store DICOM elements.
 * @param {String} key The key in form "x00280102.
 * @return {Object} The DICOM group and element.
 */
dwv.dicom.splitGroupElementKey = function (key)
{
    return {'group': key.substr(1,4), 'element': key.substr(5,8) };
};

/**
 * Get patient orientation label in the reverse direction.
 * @param {String} ori Patient Orientation value.
 * @return {String} Reverse Orientation Label.
 */
dwv.dicom.getReverseOrientation = function (ori)
{
    if (!ori) {
        return null;
    }
    // reverse labels
    var rlabels = {
        "L": "R",
        "R": "L",
        "A": "P",
        "P": "A",
        "H": "F",
        "F": "H"
    };

    var rori = "";
    for (var n=0; n<ori.length; n++) {
        var o = ori.substr(n,1);
        var r = rlabels[o];
        if (r){
            rori += r;
        }
    }
    // return
    return rori;
};

/**
 * Tell if a given syntax is an implicit one (element with no VR).
 * @param {String} syntax The transfer syntax to test.
 * @return {Boolean} True if an implicit syntax.
 */
dwv.dicom.isImplicitTransferSyntax = function (syntax)
{
    return syntax === "1.2.840.10008.1.2";
};

/**
 * Tell if a given syntax is a big endian syntax.
 * @param {String} syntax The transfer syntax to test.
 * @return {Boolean} True if a big endian syntax.
 */
dwv.dicom.isBigEndianTransferSyntax = function (syntax)
{
    return syntax === "1.2.840.10008.1.2.2";
};

/**
 * Tell if a given syntax is a JPEG baseline one.
 * @param {String} syntax The transfer syntax to test.
 * @return {Boolean} True if a jpeg baseline syntax.
 */
dwv.dicom.isJpegBaselineTransferSyntax = function (syntax)
{
    return syntax === "1.2.840.10008.1.2.4.50" ||
        syntax === "1.2.840.10008.1.2.4.51";
};

/**
 * Tell if a given syntax is a retired JPEG one.
 * @param {String} syntax The transfer syntax to test.
 * @return {Boolean} True if a retired jpeg syntax.
 */
dwv.dicom.isJpegRetiredTransferSyntax = function (syntax)
{
    return ( syntax.match(/1.2.840.10008.1.2.4.5/) !== null &&
        !dwv.dicom.isJpegBaselineTransferSyntax() &&
        !dwv.dicom.isJpegLosslessTransferSyntax() ) ||
        syntax.match(/1.2.840.10008.1.2.4.6/) !== null;
};

/**
 * Tell if a given syntax is a JPEG Lossless one.
 * @param {String} syntax The transfer syntax to test.
 * @return {Boolean} True if a jpeg lossless syntax.
 */
dwv.dicom.isJpegLosslessTransferSyntax = function (syntax)
{
    return syntax === "1.2.840.10008.1.2.4.57" ||
        syntax === "1.2.840.10008.1.2.4.70";
};

/**
 * Tell if a given syntax is a JPEG-LS one.
 * @param {String} syntax The transfer syntax to test.
 * @return {Boolean} True if a jpeg-ls syntax.
 */
dwv.dicom.isJpeglsTransferSyntax = function (syntax)
{
    return syntax.match(/1.2.840.10008.1.2.4.8/) !== null;
};

/**
 * Tell if a given syntax is a JPEG 2000 one.
 * @param {String} syntax The transfer syntax to test.
 * @return {Boolean} True if a jpeg 2000 syntax.
 */
dwv.dicom.isJpeg2000TransferSyntax = function (syntax)
{
    return syntax.match(/1.2.840.10008.1.2.4.9/) !== null;
};

/**
 * Tell if a given syntax needs decompression.
 * @param {String} syntax The transfer syntax to test.
 * @return {String} The name of the decompression algorithm.
 */
dwv.dicom.getSyntaxDecompressionName = function (syntax)
{
    var algo = null;
    if ( dwv.dicom.isJpeg2000TransferSyntax(syntax) ) {
        algo = "jpeg2000";
    }
    else if ( dwv.dicom.isJpegBaselineTransferSyntax(syntax) ) {
        algo = "jpeg-baseline";
    }
    else if ( dwv.dicom.isJpegLosslessTransferSyntax(syntax) ) {
        algo = "jpeg-lossless";
    }
    return algo;
};

/**
 * Tell if a given syntax is supported for reading.
 * @param {String} syntax The transfer syntax to test.
 * @return {Boolean} True if a supported syntax.
 */
dwv.dicom.isReadSupportedTransferSyntax = function (syntax) {

    // Unsupported:
    // "1.2.840.10008.1.2.1.99": Deflated Explicit VR - Little Endian
    // "1.2.840.10008.1.2.4.100": MPEG2 Image Compression
    // dwv.dicom.isJpegRetiredTransferSyntax(syntax): non supported JPEG
    // dwv.dicom.isJpeglsTransferSyntax(syntax): JPEG-LS
    // "1.2.840.10008.1.2.5": RLE (lossless)

    return( syntax === "1.2.840.10008.1.2" || // Implicit VR - Little Endian
        syntax === "1.2.840.10008.1.2.1" || // Explicit VR - Little Endian
        syntax === "1.2.840.10008.1.2.2" || // Explicit VR - Big Endian
        dwv.dicom.isJpegBaselineTransferSyntax(syntax) || // JPEG baseline
        dwv.dicom.isJpegLosslessTransferSyntax(syntax) || // JPEG Lossless
        dwv.dicom.isJpeg2000TransferSyntax(syntax) ); // JPEG 2000
};

/**
 * Get the transfer syntax name.
 * Reference: [UID Values]{@link http://dicom.nema.org/dicom/2013/output/chtml/part06/chapter_A.html}.
 * @param {String} syntax The transfer syntax.
 * @return {String} The name of the transfer syntax.
 */
dwv.dicom.getTransferSyntaxName = function (syntax)
{
    var name = "Unknown";
    // Implicit VR - Little Endian
    if( syntax === "1.2.840.10008.1.2" ) {
        name = "Little Endian Implicit";
    }
    // Explicit VR - Little Endian
    else if( syntax === "1.2.840.10008.1.2.1" ) {
        name = "Little Endian Explicit";
    }
    // Deflated Explicit VR - Little Endian
    else if( syntax === "1.2.840.10008.1.2.1.99" ) {
        name = "Little Endian Deflated Explicit";
    }
    // Explicit VR - Big Endian
    else if( syntax === "1.2.840.10008.1.2.2" ) {
        name = "Big Endian Explicit";
    }
    // JPEG baseline
    else if( dwv.dicom.isJpegBaselineTransferSyntax(syntax) ) {
        if ( syntax === "1.2.840.10008.1.2.4.50" ) {
            name = "JPEG Baseline";
        }
        else { // *.51
            name = "JPEG Extended, Process 2+4";
        }
    }
    // JPEG Lossless
    else if( dwv.dicom.isJpegLosslessTransferSyntax(syntax) ) {
        if ( syntax === "1.2.840.10008.1.2.4.57" ) {
            name = "JPEG Lossless, Nonhierarchical (Processes 14)";
        }
        else { // *.70
            name = "JPEG Lossless, Non-hierarchical, 1st Order Prediction";
        }
    }
    // Retired JPEG
    else if( dwv.dicom.isJpegRetiredTransferSyntax(syntax) ) {
        name = "Retired JPEG";
    }
    // JPEG-LS
    else if( dwv.dicom.isJpeglsTransferSyntax(syntax) ) {
        name = "JPEG-LS";
    }
    // JPEG 2000
    else if( dwv.dicom.isJpeg2000TransferSyntax(syntax) ) {
        if ( syntax === "1.2.840.10008.1.2.4.91" ) {
            name = "JPEG 2000 (Lossless or Lossy)";
        }
        else { // *.90
            name = "JPEG 2000 (Lossless only)";
        }
    }
    // MPEG2 Image Compression
    else if( syntax === "1.2.840.10008.1.2.4.100" ) {
        name = "MPEG2";
    }
    // RLE (lossless)
    else if( syntax === "1.2.840.10008.1.2.5" ) {
        name = "RLE";
    }
    // return
    return name;
};

/**
 * Get the appropriate TypedArray in function of arguments.
 * @param {Number} bitsAllocated The number of bites used to store the data: [8, 16, 32].
 * @param {Number} pixelRepresentation The pixel representation, 0:unsigned;1:signed.
 * @param {Size} size The size of the new array.
 * @return The good typed array.
 */
dwv.dicom.getTypedArray = function (bitsAllocated, pixelRepresentation, size)
{
    var res = null;
    if (bitsAllocated === 8) {
        if (pixelRepresentation === 0) {
            res = new Uint8Array(size);
        }
        else {
            res = new Int8Array(size);
        }
    }
    else if (bitsAllocated === 16) {
        if (pixelRepresentation === 0) {
            res = new Uint16Array(size);
        }
        else {
            res = new Int16Array(size);
        }
    }
    else if (bitsAllocated === 32) {
        if (pixelRepresentation === 0) {
            res = new Uint32Array(size);
        }
        else {
            res = new Int32Array(size);
        }
    }
    return res;
};

/**
 * Does this Value Representation (VR) have a 32bit Value Length (VL).
 * Ref: [Data Element explicit]{@link http://dicom.nema.org/dicom/2013/output/chtml/part05/chapter_7.html#table_7.1-1}.
 * @param {String} vr The data Value Representation (VR).
 * @returns {Boolean} True if this VR has a 32-bit VL.
 */
dwv.dicom.is32bitVLVR = function (vr)
{
    // added locally used 'ox'
    return ( vr === "OB" || vr === "OW" || vr === "OF" || vr === "ox" ||  vr === "UT" ||
    vr === "SQ" || vr === "UN" );
};

/**
 * Does this tag have a VR.
 * Basically the Item, ItemDelimitationItem and SequenceDelimitationItem tags.
 * @param {String} group The tag group.
 * @param {String} element The tag element.
 * @returns {Boolean} True if this tar has a VR.
 */
dwv.dicom.isTagWithVR = function (group, element) {
    return !(group === "0xFFFE" &&
            (element === "0xE000" || element === "0xE00D" || element === "0xE0DD" ));
};


/**
 * Get the number of bytes occupied by a data element prefix, i.e. without its value.
 * WARNING: this is valid for tags with a VR, if not sure use the 'isTagWithVR' function first.
 * Reference:
 * - [Data Element explicit]{@link http://dicom.nema.org/dicom/2013/output/chtml/part05/chapter_7.html#table_7.1-1},
 * - [Data Element implicit]{@link http://dicom.nema.org/dicom/2013/output/chtml/part05/sect_7.5.html#table_7.5-1}.
 *
 * | Tag | VR  | VL | Value |
 * | 4   | 2   | 2  | X     | -> regular explicit: 8 + X
 * | 4   | 2+2 | 4  | X     | -> 32bit VL: 12 + X
 *
 * | Tag | VL | Value |
 * | 4   | 4  | X     | -> implicit (32bit VL): 8 + X
 *
 * | Tag | Len | Value |
 * | 4   | 4   | X     | -> item: 8 + X
 */
dwv.dicom.getDataElementPrefixByteSize = function (vr) {
    return dwv.dicom.is32bitVLVR(vr) ? 12 : 8;
};

/**
 * DicomParser class.
 * @constructor
 */
dwv.dicom.DicomParser = function ()
{
    /**
     * The list of DICOM elements.
     * @type Array
     */
    this.dicomElements = {};

    /**
     * Default character set (optional).
     * @private
     * @type String
    */
    var defaultCharacterSet;
    /**
     * Get the default character set.
     * @return {String} The default character set.
     */
    this.getDefaultCharacterSet = function () {
        return defaultCharacterSet;
    };
    /**
     * Set the default character set.
     * param {String} The character set.
     */
    this.setDefaultCharacterSet = function (characterSet) {
        defaultCharacterSet = characterSet;
    };
};

/**
 * Get the raw DICOM data elements.
 * @return {Object} The raw DICOM elements.
 */
dwv.dicom.DicomParser.prototype.getRawDicomElements = function ()
{
    return this.dicomElements;
};

/**
 * Get the DICOM data elements.
 * @return {Object} The DICOM elements.
 */
dwv.dicom.DicomParser.prototype.getDicomElements = function ()
{
    return new dwv.dicom.DicomElementsWrapper(this.dicomElements);
};

/**
 * Read a DICOM tag.
 * @param reader The raw data reader.
 * @param offset The offset where to start to read.
 * @return An object containing the tags 'group', 'element' and 'name'.
 */
dwv.dicom.DicomParser.prototype.readTag = function (reader, offset)
{
    // group
    var group = reader.readHex(offset);
    offset += Uint16Array.BYTES_PER_ELEMENT;
    // element
    var element = reader.readHex(offset);
    offset += Uint16Array.BYTES_PER_ELEMENT;
    // name
    var name = dwv.dicom.getGroupElementKey(group, element);
    // return
    return {
        'group': group,
        'element': element,
        'name': name,
        'endOffset': offset };
};

/**
 * Read an item data element.
 * @param {Object} reader The raw data reader.
 * @param {Number} offset The offset where to start to read.
 * @param {Boolean} implicit Is the DICOM VR implicit?
 * @returns {Object} The item data as a list of data elements.
 */
dwv.dicom.DicomParser.prototype.readItemDataElement = function (reader, offset, implicit)
{
    var itemData = {};

    // read the first item
    var item = this.readDataElement(reader, offset, implicit);
    offset = item.endOffset;

    // exit if it is a sequence delimitation item
    var isSeqDelim = ( item.tag.name === "xFFFEE0DD" );
    if (isSeqDelim) {
        return {
            data: itemData,
            endOffset: item.endOffset,
            isSeqDelim: isSeqDelim };
    }

    // store it
    itemData[item.tag.name] = item;

    // explicit VR items
    if (item.vl !== "u/l") {
        // not empty
        if (item.vl !== 0) {
            // read until the end offset
            var endOffset = offset;
            offset -= item.vl;
            while (offset < endOffset) {
                item = this.readDataElement(reader, offset, implicit);
                offset = item.endOffset;
                itemData[item.tag.name] = item;
            }
        }
    }
    // implicit VR items
    else {
        // read until the item delimitation item
        var isItemDelim = false;
        while (!isItemDelim) {
            item = this.readDataElement(reader, offset, implicit);
            offset = item.endOffset;
            isItemDelim = ( item.tag.name === "xFFFEE00D" );
            if (!isItemDelim) {
                itemData[item.tag.name] = item;
            }
        }
    }

    return {
        'data': itemData,
        'endOffset': offset,
        'isSeqDelim': false };
};

/**
 * Read the pixel item data element.
 * Ref: [Single frame fragments]{@link http://dicom.nema.org/dicom/2013/output/chtml/part05/sect_A.4.html#table_A.4-1}.
 * @param {Object} reader The raw data reader.
 * @param {Number} offset The offset where to start to read.
 * @param {Boolean} implicit Is the DICOM VR implicit?
 * @returns {Array} The item data as an array of data elements.
 */
dwv.dicom.DicomParser.prototype.readPixelItemDataElement = function (reader, offset, implicit)
{
    var itemData = [];

    // first item: basic offset table
    var item = this.readDataElement(reader, offset, implicit);
    var offsetTableVl = item.vl;
    offset = item.endOffset;

    // read until the sequence delimitation item
    var isSeqDelim = false;
    while (!isSeqDelim) {
        item = this.readDataElement(reader, offset, implicit);
        offset = item.endOffset;
        isSeqDelim = ( item.tag.name === "xFFFEE0DD" );
        if (!isSeqDelim) {
            itemData.push(item.value);
        }
    }

    return {
        'data': itemData,
        'endOffset': offset,
        'offsetTableVl': offsetTableVl };
};

/**
 * Read a DICOM data element.
 * Reference: [DICOM VRs]{@link http://dicom.nema.org/dicom/2013/output/chtml/part05/sect_6.2.html#table_6.2-1}.
 * @param {Object} reader The raw data reader.
 * @param {Number} offset The offset where to start to read.
 * @param {Boolean} implicit Is the DICOM VR implicit?
 * @return {Object} An object containing the element 'tag', 'vl', 'vr', 'data' and 'endOffset'.
 */
dwv.dicom.DicomParser.prototype.readDataElement = function (reader, offset, implicit)
{
    // Tag: group, element
    var tag = this.readTag(reader, offset);
    offset = tag.endOffset;

    // Value Representation (VR)
    var vr = null;
    var is32bitVLVR = false;
    if (dwv.dicom.isTagWithVR(tag.group, tag.element)) {
        // implicit VR
        if (implicit) {
            vr = "UN";
            var dict = dwv.dicom.dictionary;
            if ( typeof dict[tag.group] !== "undefined" &&
                    typeof dict[tag.group][tag.element] !== "undefined" ) {
                vr = dwv.dicom.dictionary[tag.group][tag.element][0];
            }
            is32bitVLVR = true;
        }
        else {
            vr = reader.readString( offset, 2 );
            offset += 2 * Uint8Array.BYTES_PER_ELEMENT;
            is32bitVLVR = dwv.dicom.is32bitVLVR(vr);
            // reserved 2 bytes
            if ( is32bitVLVR ) {
                offset += 2 * Uint8Array.BYTES_PER_ELEMENT;
            }
        }
    }
    else {
        vr = "UN";
        is32bitVLVR = true;
    }

    // Value Length (VL)
    var vl = 0;
    if ( is32bitVLVR ) {
        vl = reader.readUint32( offset );
        offset += Uint32Array.BYTES_PER_ELEMENT;
    }
    else {
        vl = reader.readUint16( offset );
        offset += Uint16Array.BYTES_PER_ELEMENT;
    }

    // check the value of VL
    var vlString = vl;
    if( vl === 0xffffffff ) {
        vlString = "u/l";
        vl = 0;
    }

    var startOffset = offset;

    // data
    var data = null;
    var isPixelData = (tag.name === "x7FE00010");
    // pixel data sequence (implicit)
    if (isPixelData && vlString === "u/l")
    {
        var pixItemData = this.readPixelItemDataElement(reader, offset, implicit);
        offset = pixItemData.endOffset;
        startOffset += pixItemData.offsetTableVl;
        data = pixItemData.data;
    }
    else if (isPixelData && (vr === "OB" || vr === "OW" || vr === "OF" || vr === "ox")) {
        // BitsAllocated
        var bitsAllocated = 16;
        if ( typeof this.dicomElements.x00280100 !== 'undefined' ) {
            bitsAllocated = this.dicomElements.x00280100.value[0];
        } else {
            console.warn("Reading DICOM pixel data with default bitsAllocated.");
        }
        if (bitsAllocated === 8 && vr === "OW") {
            console.warn("Reading DICOM pixel data with vr=OW and bitsAllocated=8 (should be 16).");
        }
        if (bitsAllocated === 16 && vr === "OB") {
            console.warn("Reading DICOM pixel data with vr=OB and bitsAllocated=16 (should be 8).");
        }
        // PixelRepresentation 0->unsigned, 1->signed
        var pixelRepresentation = 0;
        if ( typeof this.dicomElements.x00280103 !== 'undefined' ) {
            pixelRepresentation = this.dicomElements.x00280103.value[0];
        }
        // read
        if ( bitsAllocated === 8 ) {
            if (pixelRepresentation === 0) {
                data = reader.readUint8Array( offset, vl );
            }
            else {
                data = reader.readInt8Array( offset, vl );
            }
        }
        else if ( bitsAllocated === 16 ) {
            if (pixelRepresentation === 0) {
                data = reader.readUint16Array( offset, vl );
            }
            else {
                data = reader.readInt16Array( offset, vl );
            }
        }
        else if ( bitsAllocated === 32 ) {
            if (pixelRepresentation === 0) {
                data = reader.readUint32Array( offset, vl );
            }
            else {
                data = reader.readInt32Array( offset, vl );
            }
        }
        else if ( bitsAllocated === 64 ) {
            if (pixelRepresentation === 0) {
                data = reader.readUint64Array( offset, vl );
            }
            else {
                data = reader.readInt64Array( offset, vl );
            }
        }
        offset += vl;
    }
    // others
    else if ( vr === "OB" )
    {
        data = reader.readInt8Array( offset, vl );
        offset += vl;
    }
    else if ( vr === "OW" )
    {
        data = reader.readInt16Array( offset, vl );
        offset += vl;
    }
    else if ( vr === "OF" )
    {
        data = reader.readInt32Array( offset, vl );
        offset += vl;
    }
    else if ( vr === "OD" )
    {
        data = reader.readInt64Array( offset, vl );
        offset += vl;
    }
    // numbers
    else if( vr === "US")
    {
        data = reader.readUint16Array( offset, vl );
        offset += vl;
    }
    else if( vr === "UL")
    {
        data = reader.readUint32Array( offset, vl );
        offset += vl;
    }
    else if( vr === "SS")
    {
        data = reader.readInt16Array( offset, vl );
        offset += vl;
    }
    else if( vr === "SL")
    {
        data = reader.readInt32Array( offset, vl );
        offset += vl;
    }
    else if( vr === "FL")
    {
        data = reader.readFloat32Array( offset, vl );
        offset += vl;
    }
    else if( vr === "FD")
    {
        data = reader.readFloat64Array( offset, vl );
        offset += vl;
    }
    // attribute
    else if( vr === "AT")
    {
        var raw = reader.readUint16Array( offset, vl );
        offset += vl;
        data = [];
        for ( var i = 0, leni = raw.length; i < leni; i+=2 ) {
            var stri = raw[i].toString(16);
            var stri1 = raw[i+1].toString(16);
            var str = "(";
            str += "0000".substr(0, 4 - stri.length) + stri.toUpperCase();
            str += ",";
            str += "0000".substr(0, 4 - stri1.length) + stri1.toUpperCase();
            str += ")";
            data.push(str);
        }
    }
    // not available
    else if( vr === "UN")
    {
        data = reader.readUint8Array( offset, vl );
        offset += vl;
    }
    // sequence
    else if (vr === "SQ")
    {
        data = [];
        var itemData;
        // explicit VR sequence
        if (vlString !== "u/l") {
            // not empty
            if (vl !== 0) {
                var sqEndOffset = offset + vl;
                while (offset < sqEndOffset) {
                     itemData = this.readItemDataElement(reader, offset, implicit);
                     data.push( itemData.data );
                     offset = itemData.endOffset;
                }
            }
        }
        // implicit VR sequence
        else {
            // read until the sequence delimitation item
            var isSeqDelim = false;
            while (!isSeqDelim) {
                itemData = this.readItemDataElement(reader, offset, implicit);
                isSeqDelim = itemData.isSeqDelim;
                offset = itemData.endOffset;
                // do not store the delimitation item
                if (!isSeqDelim) {
                    data.push( itemData.data );
                }
            }
        }
    }
    // raw
    else
    {
        if ( vr === "SH" || vr === "LO" || vr === "ST" ||
            vr === "PN" || vr === "LT" || vr === "UT" ) {
            data = reader.readSpecialString( offset, vl );
        } else {
            data = reader.readString( offset, vl );
        }
        offset += vl;
        data = data.split("\\");
    }

    // return
    return {
        'tag': tag,
        'vr': vr,
        'vl': vlString,
        'value': data,
        'startOffset': startOffset,
        'endOffset': offset
    };
};

/**
 * Parse the complete DICOM file (given as input to the class).
 * Fills in the member object 'dicomElements'.
 * @param buffer The input array buffer.
 */
dwv.dicom.DicomParser.prototype.parse = function (buffer)
{
    var offset = 0;
    var implicit = false;
    // default readers
    var metaReader = new dwv.dicom.DataReader(buffer);
    var dataReader = new dwv.dicom.DataReader(buffer);

    // 128 -> 132: magic word
    offset = 128;
    var magicword = metaReader.readString( offset, 4 );
    offset += 4 * Uint8Array.BYTES_PER_ELEMENT;
    if(magicword !== "DICM")
    {
        throw new Error("Not a valid DICOM file (no magic DICM word found)");
    }

    // 0x0002, 0x0000: FileMetaInformationGroupLength
    var dataElement = this.readDataElement(metaReader, offset);
    offset = dataElement.endOffset;
    // store the data element
    this.dicomElements[dataElement.tag.name] = dataElement;
    // get meta length
    var metaLength = parseInt(dataElement.value[0], 10);

    // meta elements
    var metaEnd = offset + metaLength;
    while( offset < metaEnd )
    {
        // get the data element
        dataElement = this.readDataElement(metaReader, offset, false);
        offset = dataElement.endOffset;
        // store the data element
        this.dicomElements[dataElement.tag.name] = dataElement;
    }

    // check the TransferSyntaxUID (has to be there!)
    if (typeof this.dicomElements.x00020010 === "undefined")
    {
        throw new Error("Not a valid DICOM file (no TransferSyntaxUID found)");
    }
    var syntax = dwv.dicom.cleanString(this.dicomElements.x00020010.value[0]);

    // check support
    if (!dwv.dicom.isReadSupportedTransferSyntax(syntax)) {
        throw new Error("Unsupported DICOM transfer syntax: '"+syntax+
            "' ("+dwv.dicom.getTransferSyntaxName(syntax)+")");
    }

    // Implicit VR
    if (dwv.dicom.isImplicitTransferSyntax(syntax)) {
        implicit = true;
    }

    // Big Endian
    if (dwv.dicom.isBigEndianTransferSyntax(syntax)) {
        dataReader = new dwv.dicom.DataReader(buffer,false);
    }

    // default character set
    if (typeof this.getDefaultCharacterSet() !== "undefined") {
        dataReader.setUtfLabel(this.getDefaultCharacterSet());
    }

    // DICOM data elements
    while ( offset < buffer.byteLength )
    {
        // get the data element
        dataElement = this.readDataElement(dataReader, offset, implicit);
        // check character set
        if (dataElement.tag.name === "x00080005") {
            var charSetTerm;
            if (dataElement.value.length === 1) {
                charSetTerm = dwv.dicom.cleanString(dataElement.value[0]);
            }
            else {
                charSetTerm = dwv.dicom.cleanString(dataElement.value[1]);
                console.warn("Unsupported character set with code extensions: '"+charSetTerm+"'.");
            }
            dataReader.setUtfLabel(dwv.dicom.getUtfLabel(charSetTerm));
        }
        // increment offset
        offset = dataElement.endOffset;
        // store the data element
        this.dicomElements[dataElement.tag.name] = dataElement;
    }

    // safety check...
    if (buffer.byteLength !== offset) {
        console.warn("Did not reach the end of the buffer: "+
            offset+" != "+buffer.byteLength);
    }

    // pixel buffer
    if (typeof this.dicomElements.x7FE00010 !== "undefined") {

        var numberOfFrames = 1;
        if (typeof this.dicomElements.x00280008 !== "undefined") {
            numberOfFrames = this.dicomElements.x00280008.value[0];
        }

        if (this.dicomElements.x7FE00010.vl !== "u/l") {
            // compressed should be encapsulated...
            if (dwv.dicom.isJpeg2000TransferSyntax( syntax ) ||
                dwv.dicom.isJpegBaselineTransferSyntax( syntax ) ||
                dwv.dicom.isJpegLosslessTransferSyntax( syntax ) ) {
                console.warn("Compressed but no items...");
            }

            // calculate the slice size
            var pixData = this.dicomElements.x7FE00010.value;
            var columns = this.dicomElements.x00280011.value[0];
            var rows = this.dicomElements.x00280010.value[0];
            var samplesPerPixel = this.dicomElements.x00280002.value[0];
            var sliceSize = columns * rows * samplesPerPixel;
            // slice data in an array of frames
            var newPixData = [];
            var frameOffset = 0;
            for (var g = 0; g < numberOfFrames; ++g) {
                newPixData[g] = pixData.slice(frameOffset, frameOffset+sliceSize);
                frameOffset += sliceSize;
            }
            // store as pixel data
            this.dicomElements.x7FE00010.value = newPixData;
        }
        else {
            // handle fragmented pixel buffer
            // Reference: http://dicom.nema.org/dicom/2013/output/chtml/part05/sect_8.2.html
            // (third note, "Depending on the transfer syntax...")
            var pixItems = this.dicomElements.x7FE00010.value;
            if (pixItems.length > 1 && pixItems.length > numberOfFrames ) {

                var bitsAllocated = this.dicomElements.x00280100.value[0];
                var pixelRepresentation = this.dicomElements.x00280103.value[0];

                // concatenate pixel data items
                // concat does not work on typed arrays
                //this.pixelBuffer = this.pixelBuffer.concat( dataElement.data );
                // manual concat...
                var nItemPerFrame = pixItems.length / numberOfFrames;
                var newPixItems = [];
                var index = 0;
                for (var f = 0; f < numberOfFrames; ++f) {
                    index = f * nItemPerFrame;
                    // calculate the size of a frame
                    var size = 0;
                    for (var i = 0; i < nItemPerFrame; ++i) {
                        size += pixItems[index + i].length;
                    }
                    // create new buffer
                    var newBuffer = dwv.dicom.getTypedArray(bitsAllocated, pixelRepresentation, size);
                    // fill new buffer
                    var fragOffset = 0;
                    for (var j = 0; j < nItemPerFrame; ++j) {
                        newBuffer.set( pixItems[index + j], fragOffset );
                        fragOffset += pixItems[index + j].length;
                    }
                    newPixItems[f] = newBuffer;
                }
                // store as pixel data
                this.dicomElements.x7FE00010.value = newPixItems;
            }
        }
    }
};

/**
 * DicomElements wrapper.
 * @constructor
 * @param {Array} dicomElements The elements to wrap.
 */
dwv.dicom.DicomElementsWrapper = function (dicomElements) {

    /**
    * Get a DICOM Element value from a group/element key.
    * @param {String} groupElementKey The key to retrieve.
    * @return {Object} The DICOM element.
    */
    this.getDEFromKey = function ( groupElementKey ) {
        return dicomElements[groupElementKey];
    };

    /**
    * Get a DICOM Element value from a group/element key.
    * @param {String} groupElementKey The key to retrieve.
    * @param {Boolean} asArray Get the value as an Array.
    * @return {Object} The DICOM element value.
    */
    this.getFromKey = function ( groupElementKey, asArray ) {
        // default
        if ( typeof asArray === "undefined" ) {
            asArray = false;
        }
        var value = null;
        var dElement = dicomElements[groupElementKey];
        if ( typeof dElement !== "undefined" ) {
            // raw value if only one
            if ( dElement.value.length === 1 && asArray === false) {
                value = dElement.value[0];
            }
            else {
                value = dElement.value;
            }
        }
        return value;
    };

    /**
     * Dump the DICOM tags to an array.
     * @return {Array}
     */
    this.dumpToTable = function () {
        var keys = Object.keys(dicomElements);
        var dict = dwv.dicom.dictionary;
        var table = [];
        var dicomElement = null;
        var dictElement = null;
        var row = null;
        for ( var i = 0, leni = keys.length; i < leni; ++i ) {
            dicomElement = dicomElements[keys[i]];
            row = {};
            // dictionnary entry (to get name)
            dictElement = null;
            if ( typeof dict[dicomElement.tag.group] !== "undefined" &&
                    typeof dict[dicomElement.tag.group][dicomElement.tag.element] !== "undefined") {
                dictElement = dict[dicomElement.tag.group][dicomElement.tag.element];
            }
            // name
            if ( dictElement !== null ) {
                row.name = dictElement[2];
            }
            else {
                row.name = "Unknown Tag & Data";
            }
            // value
            row.value = this.getElementValueAsString(dicomElement);
            // others
            row.group = dicomElement.tag.group;
            row.element = dicomElement.tag.element;
            row.vr = dicomElement.vr;
            row.vl = dicomElement.vl;

            table.push( row );
        }
        return table;
    };

    /**
     * Dump the DICOM tags to a string.
     * @return {String} The dumped file.
     */
    this.dump = function () {
        var keys = Object.keys(dicomElements);
        var result = "\n";
        result += "# Dicom-File-Format\n";
        result += "\n";
        result += "# Dicom-Meta-Information-Header\n";
        result += "# Used TransferSyntax: ";
        if ( dwv.dicom.isNativeLittleEndian() ) {
            result += "Little Endian Explicit\n";
        }
        else {
            result += "NOT Little Endian Explicit\n";
        }
        var dicomElement = null;
        var checkHeader = true;
        for ( var i = 0, leni = keys.length; i < leni; ++i ) {
            dicomElement = dicomElements[keys[i]];
            if ( checkHeader && dicomElement.tag.group !== "0x0002" ) {
                result += "\n";
                result += "# Dicom-Data-Set\n";
                result += "# Used TransferSyntax: ";
                var syntax = dwv.dicom.cleanString(dicomElements.x00020010.value[0]);
                result += dwv.dicom.getTransferSyntaxName(syntax);
                result += "\n";
                checkHeader = false;
            }
            result += this.getElementAsString(dicomElement) + "\n";
        }
        return result;
    };

};

/**
 * Get a data element value as a string.
 * @param {Object} dicomElement The DICOM element.
 * @param {Boolean} pretty When set to true, returns a 'pretified' content.
 * @return {String} A string representation of the DICOM element.
 */
dwv.dicom.DicomElementsWrapper.prototype.getElementValueAsString = function ( dicomElement, pretty )
{
    var str = "";
    var strLenLimit = 65;

    // dafault to pretty output
    if ( typeof pretty === "undefined" ) {
        pretty = true;
    }
    // check dicom element input
    if ( typeof dicomElement === "undefined" || dicomElement === null ) {
        return str;
    }

    // Polyfill for Number.isInteger.
    var isInteger = Number.isInteger || function (value) {
      return typeof value === 'number' &&
        isFinite(value) &&
        Math.floor(value) === value;
    };

    // TODO Support sequences.

    if ( dicomElement.vr !== "SQ" &&
        dicomElement.value.length === 1 && dicomElement.value[0] === "" ) {
        str += "(no value available)";
    } else if ( dicomElement.tag.group === '0x7FE0' &&
        dicomElement.tag.element === '0x0010' &&
        dicomElement.vl === 'u/l' ) {
        str = "(PixelSequence)";
    } else if ( dicomElement.vr === "DA" && pretty ) {
        var daValue = dicomElement.value[0];
        var daYear = parseInt( daValue.substr(0,4), 10 );
        var daMonth = parseInt( daValue.substr(4,2), 10 ) - 1; // 0-11
        var daDay = parseInt( daValue.substr(6,2), 10 );
        var da = new Date(daYear, daMonth, daDay);
        str = da.toLocaleDateString();
    } else if ( dicomElement.vr === "TM"  && pretty ) {
        var tmValue = dicomElement.value[0];
        var tmHour = tmValue.substr(0,2);
        var tmMinute = tmValue.length >= 4 ? tmValue.substr(2,2) : "00";
        var tmSeconds = tmValue.length >= 6 ? tmValue.substr(4,2) : "00";
        str = tmHour + ':' + tmMinute + ':' + tmSeconds;
    } else {
        var isOtherVR = ( dicomElement.vr[0].toUpperCase() === "O" );
        var isFloatNumberVR = ( dicomElement.vr === "FL" ||
            dicomElement.vr === "FD" ||
            dicomElement.vr === "DS");
        var valueStr = "";
        for ( var k = 0, lenk = dicomElement.value.length; k < lenk; ++k ) {
            valueStr = "";
            if ( k !== 0 ) {
                valueStr += "\\";
            }
            if ( isFloatNumberVR ) {
                var val = dicomElement.value[k];
                if (typeof val === "string") {
                    val = dwv.dicom.cleanString(val);
                }
                var num = Number( val );
                if ( !isInteger( num ) && pretty ) {
                    valueStr += num.toPrecision(4);
                } else {
                    valueStr += num.toString();
                }
            } else if ( isOtherVR ) {
                var tmp = dicomElement.value[k].toString(16);
                if ( dicomElement.vr === "OB" ) {
                    tmp = "00".substr(0, 2 - tmp.length) + tmp;
                }
                else {
                    tmp = "0000".substr(0, 4 - tmp.length) + tmp;
                }
                valueStr += tmp;
            } else if ( typeof dicomElement.value[k] === "string" ) {
                valueStr += dwv.dicom.cleanString(dicomElement.value[k]);
            } else {
                valueStr += dicomElement.value[k];
            }
            // check length
            if ( str.length + valueStr.length <= strLenLimit ) {
                str += valueStr;
            } else {
                str += "...";
                break;
            }
        }
    }
    return str;
};

/**
 * Get a data element value as a string.
 * @param {String} groupElementKey The key to retrieve.
 */
dwv.dicom.DicomElementsWrapper.prototype.getElementValueAsStringFromKey = function ( groupElementKey )
{
    return this.getElementValueAsString( this.getDEFromKey(groupElementKey) );
};

/**
 * Get a data element as a string.
 * @param {Object} dicomElement The DICOM element.
 * @param {String} prefix A string to prepend this one.
 */
dwv.dicom.DicomElementsWrapper.prototype.getElementAsString = function ( dicomElement, prefix )
{
    // default prefix
    prefix = prefix || "";

    // get element from dictionary
    var dict = dwv.dicom.dictionary;
    var dictElement = null;
    if ( typeof dict[dicomElement.tag.group] !== "undefined" &&
            typeof dict[dicomElement.tag.group][dicomElement.tag.element] !== "undefined") {
        dictElement = dict[dicomElement.tag.group][dicomElement.tag.element];
    }

    var deSize = dicomElement.value.length;
    var isOtherVR = ( dicomElement.vr[0].toUpperCase() === "O" );

    // no size for delimitations
    if ( dicomElement.tag.group === "0xFFFE" && (
            dicomElement.tag.element === "0xE00D" ||
            dicomElement.tag.element === "0xE0DD" ) ) {
        deSize = 0;
    }
    else if ( isOtherVR ) {
        deSize = 1;
    }

    var isPixSequence = (dicomElement.tag.group === '0x7FE0' &&
        dicomElement.tag.element === '0x0010' &&
        dicomElement.vl === 'u/l');

    var line = null;

    // (group,element)
    line = "(";
    line += dicomElement.tag.group.substr(2,5).toLowerCase();
    line += ",";
    line += dicomElement.tag.element.substr(2,5).toLowerCase();
    line += ") ";
    // value representation
    line += dicomElement.vr;
    // value
    if ( dicomElement.vr !== "SQ" && dicomElement.value.length === 1 && dicomElement.value[0] === "" ) {
        line += " (no value available)";
        deSize = 0;
    }
    else {
        // simple number display
        if ( dicomElement.vr === "na" ) {
            line += " ";
            line += dicomElement.value[0];
        }
        // pixel sequence
        else if ( isPixSequence ) {
            line += " (PixelSequence #=" + deSize + ")";
        }
        else if ( dicomElement.vr === 'SQ' ) {
            line += " (Sequence with";
            if ( dicomElement.vl === "u/l" ) {
                line += " undefined";
            }
            else {
                line += " explicit";
            }
            line += " length #=";
            line += dicomElement.value.length;
            line += ")";
        }
        // 'O'ther array, limited display length
        else if ( isOtherVR ||
                dicomElement.vr === 'pi' ||
                dicomElement.vr === "UL" ||
                dicomElement.vr === "US" ||
                dicomElement.vr === "SL" ||
                dicomElement.vr === "SS" ||
                dicomElement.vr === "FL" ||
                dicomElement.vr === "FD" ||
                dicomElement.vr === "AT" ) {
            line += " ";
            line += this.getElementValueAsString(dicomElement, false);
        }
        // default
        else {
            line += " [";
            line += this.getElementValueAsString(dicomElement, false);
            line += "]";
        }
    }

    // align #
    var nSpaces = 55 - line.length;
    if ( nSpaces > 0 ) {
        for ( var s = 0; s < nSpaces; ++s ) {
            line += " ";
        }
    }
    line += " # ";
    if ( dicomElement.vl < 100 ) {
        line += " ";
    }
    if ( dicomElement.vl < 10 ) {
        line += " ";
    }
    line += dicomElement.vl;
    line += ", ";
    line += deSize; //dictElement[1];
    line += " ";
    if ( dictElement !== null ) {
        line += dictElement[2];
    }
    else {
        line += "Unknown Tag & Data";
    }

    var message = null;

    // continue for sequence
    if ( dicomElement.vr === 'SQ' ) {
        var item = null;
        for ( var l = 0, lenl = dicomElement.value.length; l < lenl; ++l ) {
            item = dicomElement.value[l];
            var itemKeys = Object.keys(item);
            if ( itemKeys.length === 0 ) {
                continue;
            }

            // get the item element
            var itemElement = item.xFFFEE000;
            message = "(Item with";
            if ( itemElement.vl === "u/l" ) {
                message += " undefined";
            }
            else {
                message += " explicit";
            }
            message += " length #="+(itemKeys.length - 1)+")";
            itemElement.value = [message];
            itemElement.vr = "na";

            line += "\n";
            line += this.getElementAsString(itemElement, prefix + "  ");

            for ( var m = 0, lenm = itemKeys.length; m < lenm; ++m ) {
                if ( itemKeys[m] !== "xFFFEE000" ) {
                    line += "\n";
                    line += this.getElementAsString(item[itemKeys[m]], prefix + "    ");
                }
            }

            message = "(ItemDelimitationItem";
            if ( itemElement.vl !== "u/l" ) {
                message += " for re-encoding";
            }
            message += ")";
            var itemDelimElement = {
                    "tag": { "group": "0xFFFE", "element": "0xE00D" },
                    "vr": "na",
                    "vl": "0",
                    "value": [message]
                };
            line += "\n";
            line += this.getElementAsString(itemDelimElement, prefix + "  ");

        }

        message = "(SequenceDelimitationItem";
        if ( dicomElement.vl !== "u/l" ) {
            message += " for re-encod.";
        }
        message += ")";
        var sqDelimElement = {
                "tag": { "group": "0xFFFE", "element": "0xE0DD" },
                "vr": "na",
                "vl": "0",
                "value": [message]
            };
        line += "\n";
        line += this.getElementAsString(sqDelimElement, prefix);
    }
    // pixel sequence
    else if ( isPixSequence ) {
        var pixItem = null;
        for ( var n = 0, lenn = dicomElement.value.length; n < lenn; ++n ) {
            pixItem = dicomElement.value[n];
            line += "\n";
            pixItem.vr = 'pi';
            line += this.getElementAsString(pixItem, prefix + "  ");
        }

        var pixDelimElement = {
                "tag": { "group": "0xFFFE", "element": "0xE0DD" },
                "vr": "na",
                "vl": "0",
                "value": ["(SequenceDelimitationItem)"]
            };
        line += "\n";
        line += this.getElementAsString(pixDelimElement, prefix);
    }

    return prefix + line;
};

/**
 * Get a DICOM Element value from a group and an element.
 * @param {Number} group The group.
 * @param {Number} element The element.
 * @return {Object} The DICOM element value.
 */
dwv.dicom.DicomElementsWrapper.prototype.getFromGroupElement = function (
    group, element )
{
   return this.getFromKey(
       dwv.dicom.getGroupElementKey(group, element) );
};

/**
 * Get a DICOM Element value from a tag name.
 * Uses the DICOM dictionary.
 * @param {String} name The tag name.
 * @return {Object} The DICOM element value.
 */
dwv.dicom.DicomElementsWrapper.prototype.getFromName = function ( name )
{
   var group = null;
   var element = null;
   var dict = dwv.dicom.dictionary;
   var keys0 = Object.keys(dict);
   var keys1 = null;
   var k0 = 0;
   var lenk0 = 0;
   var k1 = 0;
   var lenk1 = 0;
   // label for nested loop break
   outLabel:
   // search through dictionary
   for ( k0 = 0, lenk0 = keys0.length; k0 < lenk0; ++k0 ) {
       group = keys0[k0];
       keys1 = Object.keys( dict[group] );
       for ( k1 = 0, lenk1 = keys1.length; k1 < lenk1; ++k1 ) {
           element = keys1[k1];
           if ( dict[group][element][2] === name ) {
               break outLabel;
           }
       }
   }
   var dicomElement = null;
   // check that we are not at the end of the dictionary
   if ( k0 !== keys0.length && k1 !== keys1.length ) {
       dicomElement = this.getFromKey(dwv.dicom.getGroupElementKey(group, element));
   }
   return dicomElement;
};

// namespaces
var dwv = dwv || {};
dwv.dicom = dwv.dicom || {};

/**
 * Data writer.
 *
 * Example usage:
 *   var parser = new dwv.dicom.DicomParser();
 *   parser.parse(this.response);
 *
 *   var writer = new dwv.dicom.DicomWriter(parser.getRawDicomElements());
 *   var blob = new Blob([writer.getBuffer()], {type: 'application/dicom'});
 *
 *   var element = document.getElementById("download");
 *   element.href = URL.createObjectURL(blob);
 *   element.download = "anonym.dcm";
 *
 * @constructor
 * @param {Array} buffer The input array buffer.
 */
dwv.dicom.DataWriter = function (buffer)
{
    // private DataView
    var view = new DataView(buffer);
    // endianness flag
    var isLittleEndian = true;

    /**
     * Write Uint8 data.
     * @param {Number} byteOffset The offset to start writing from.
     * @param {Number} value The data to write.
     * @returns {Number} The new offset position.
     */
    this.writeUint8 = function (byteOffset, value) {
        view.setUint8(byteOffset, value);
        return byteOffset + Uint8Array.BYTES_PER_ELEMENT;
    };

    /**
     * Write Int8 data.
     * @param {Number} byteOffset The offset to start writing from.
     * @param {Number} value The data to write.
     * @returns {Number} The new offset position.
     */
    this.writeInt8 = function (byteOffset, value) {
        view.setInt8(byteOffset, value);
        return byteOffset + Int8Array.BYTES_PER_ELEMENT;
    };

    /**
     * Write Uint16 data.
     * @param {Number} byteOffset The offset to start writing from.
     * @param {Number} value The data to write.
     * @returns {Number} The new offset position.
     */
    this.writeUint16 = function (byteOffset, value) {
        view.setUint16(byteOffset, value, isLittleEndian);
        return byteOffset + Uint16Array.BYTES_PER_ELEMENT;
    };

    /**
     * Write Int16 data.
     * @param {Number} byteOffset The offset to start writing from.
     * @param {Number} value The data to write.
     * @returns {Number} The new offset position.
     */
    this.writeInt16 = function (byteOffset, value) {
        view.setInt16(byteOffset, value, isLittleEndian);
        return byteOffset + Int16Array.BYTES_PER_ELEMENT;
    };

    /**
     * Write Uint32 data.
     * @param {Number} byteOffset The offset to start writing from.
     * @param {Number} value The data to write.
     * @returns {Number} The new offset position.
     */
    this.writeUint32 = function (byteOffset, value) {
        view.setUint32(byteOffset, value, isLittleEndian);
        return byteOffset + Uint32Array.BYTES_PER_ELEMENT;
    };

    /**
     * Write Int32 data.
     * @param {Number} byteOffset The offset to start writing from.
     * @param {Number} value The data to write.
     * @returns {Number} The new offset position.
     */
    this.writeInt32 = function (byteOffset, value) {
        view.setInt32(byteOffset, value, isLittleEndian);
        return byteOffset + Int32Array.BYTES_PER_ELEMENT;
    };

    /**
     * Write Float32 data.
     * @param {Number} byteOffset The offset to start writing from.
     * @param {Number} value The data to write.
     * @returns {Number} The new offset position.
     */
    this.writeFloat32 = function (byteOffset, value) {
        view.setFloat32(byteOffset, value, isLittleEndian);
        return byteOffset + Float32Array.BYTES_PER_ELEMENT;
    };

    /**
     * Write Float64 data.
     * @param {Number} byteOffset The offset to start writing from.
     * @param {Number} value The data to write.
     * @returns {Number} The new offset position.
     */
    this.writeFloat64 = function (byteOffset, value) {
        view.setFloat64(byteOffset, value, isLittleEndian);
        return byteOffset + Float64Array.BYTES_PER_ELEMENT;
    };

    /**
     * Write string data as hexadecimal.
     * @param {Number} byteOffset The offset to start writing from.
     * @param {Number} str The padded hexadecimal string to write ('0x####').
     * @returns {Number} The new offset position.
     */
    this.writeHex = function (byteOffset, str) {
        // remove first two chars and parse
        var value = parseInt(str.substr(2), 16);
        view.setUint16(byteOffset, value, isLittleEndian);
        return byteOffset + Uint16Array.BYTES_PER_ELEMENT;
    };

    /**
     * Write string data.
     * @param {Number} byteOffset The offset to start writing from.
     * @param {Number} str The data to write.
     * @returns {Number} The new offset position.
     */
    this.writeString = function (byteOffset, str) {
        for ( var i = 0, len = str.length; i < len; ++i ) {
            view.setUint8(byteOffset, str.charCodeAt(i));
            byteOffset += Uint8Array.BYTES_PER_ELEMENT;
        }
        return byteOffset;
    };

};

/**
 * Write Uint8 array.
 * @param {Number} byteOffset The offset to start writing from.
 * @param {Array} array The array to write.
 * @returns {Number} The new offset position.
 */
dwv.dicom.DataWriter.prototype.writeUint8Array = function (byteOffset, array) {
    for ( var i = 0, len = array.length; i < len; ++i ) {
        byteOffset = this.writeUint8(byteOffset, array[i]);
    }
    return byteOffset;
};

/**
 * Write Int8 array.
 * @param {Number} byteOffset The offset to start writing from.
 * @param {Array} array The array to write.
 * @returns {Number} The new offset position.
 */
dwv.dicom.DataWriter.prototype.writeInt8Array = function (byteOffset, array) {
    for ( var i = 0, len = array.length; i < len; ++i ) {
        byteOffset = this.writeInt8(byteOffset, array[i]);
    }
    return byteOffset;
};

/**
 * Write Uint16 array.
 * @param {Number} byteOffset The offset to start writing from.
 * @param {Array} array The array to write.
 * @returns {Number} The new offset position.
 */
dwv.dicom.DataWriter.prototype.writeUint16Array = function (byteOffset, array) {
    for ( var i = 0, len = array.length; i < len; ++i ) {
        byteOffset = this.writeUint16(byteOffset, array[i]);
    }
    return byteOffset;
};

/**
 * Write Int16 array.
 * @param {Number} byteOffset The offset to start writing from.
 * @param {Array} array The array to write.
 * @returns {Number} The new offset position.
 */
dwv.dicom.DataWriter.prototype.writeInt16Array = function (byteOffset, array) {
    for ( var i = 0, len = array.length; i < len; ++i ) {
        byteOffset = this.writeInt16(byteOffset, array[i]);
    }
    return byteOffset;
};

/**
 * Write Uint32 array.
 * @param {Number} byteOffset The offset to start writing from.
 * @param {Array} array The array to write.
 * @returns {Number} The new offset position.
 */
dwv.dicom.DataWriter.prototype.writeUint32Array = function (byteOffset, array) {
    for ( var i = 0, len = array.length; i < len; ++i ) {
        byteOffset = this.writeUint32(byteOffset, array[i]);
    }
    return byteOffset;
};

/**
 * Write Int32 array.
 * @param {Number} byteOffset The offset to start writing from.
 * @param {Array} array The array to write.
 * @returns {Number} The new offset position.
 */
dwv.dicom.DataWriter.prototype.writeInt32Array = function (byteOffset, array) {
    for ( var i = 0, len = array.length; i < len; ++i ) {
        byteOffset = this.writeInt32(byteOffset, array[i]);
    }
    return byteOffset;
};

/**
 * Write Float32 array.
 * @param {Number} byteOffset The offset to start writing from.
 * @param {Array} array The array to write.
 * @returns {Number} The new offset position.
 */
dwv.dicom.DataWriter.prototype.writeFloat32Array = function (byteOffset, array) {
    for ( var i = 0, len = array.length; i < len; ++i ) {
        byteOffset = this.writeFloat32(byteOffset, array[i]);
    }
    return byteOffset;
};

/**
 * Write Float64 array.
 * @param {Number} byteOffset The offset to start writing from.
 * @param {Array} array The array to write.
 * @returns {Number} The new offset position.
 */
dwv.dicom.DataWriter.prototype.writeFloat64Array = function (byteOffset, array) {
    for ( var i = 0, len = array.length; i < len; ++i ) {
        byteOffset = this.writeFloat64(byteOffset, array[i]);
    }
    return byteOffset;
};

/**
 * Write string array.
 * @param {Number} byteOffset The offset to start writing from.
 * @param {Array} array The array to write.
 * @returns {Number} The new offset position.
 */
dwv.dicom.DataWriter.prototype.writeStringArray = function (byteOffset, array) {
    for ( var i = 0, len = array.length; i < len; ++i ) {
        // separator
        if ( i !== 0 ) {
            byteOffset = this.writeString(byteOffset, "\\");
        }
        // value
        byteOffset = this.writeString(byteOffset, array[i].toString());
    }
    return byteOffset;
};

/**
 * Write a list of items.
 * @param {Number} byteOffset The offset to start writing from.
 * @param {Array} items The list of items to write.
 * @returns {Number} The new offset position.
 */
dwv.dicom.DataWriter.prototype.writeDataElementItems = function (byteOffset, items) {
    var item = null;
    for ( var i = 0; i < items.length; ++i ) {
        item = items[i];
        var itemKeys = Object.keys(item);
        if ( itemKeys.length === 0 ) {
            continue;
        }
        // write item
        var itemElement = item.xFFFEE000;
        itemElement.value = [];
        byteOffset = this.writeDataElement(itemElement, byteOffset);
        // write rest
        for ( var m = 0; m < itemKeys.length; ++m ) {
            if ( itemKeys[m] !== "xFFFEE000" && itemKeys[m] !== "xFFFEE00D") {
                byteOffset = this.writeDataElement(item[itemKeys[m]], byteOffset);
            }
        }
        // item delimitation
        if (itemElement.vl === "u/l") {
            var itemDelimElement = {
                'tag': { group: "0xFFFE",
                    element: "0xE00D",
                    name: "ItemDelimitationItem" },
                'vr': "NONE",
                'vl': 0,
                'value': []
            };
            byteOffset = this.writeDataElement(itemDelimElement, byteOffset);
        }
    }

    // return new offset
    return byteOffset;
};

/**
 * Write data with a specific Value Representation (VR).
 * @param {String} vr The data Value Representation (VR).
 * @param {Number} byteOffset The offset to start writing from.
 * @param {Array} value The array to write.
 * @returns {Number} The new offset position.
 */
dwv.dicom.DataWriter.prototype.writeDataElementValue = function (vr, byteOffset, value) {
    // switch according to VR
    if ( vr === "OB" || vr === "UN") {
        byteOffset = this.writeUint8Array(byteOffset, value);
    }
    else if ( vr === "US") {
        byteOffset = this.writeUint16Array(byteOffset, value);
    }
    else if (vr === "OW") {
        if (value.BYTES_PER_ELEMENT === 1) {
            byteOffset = this.writeUint8Array(byteOffset, value);
        } else {
            byteOffset = this.writeUint16Array(byteOffset, value);
        }
    }
    else if ( vr === "SS") {
        byteOffset = this.writeInt16Array(byteOffset, value);
    }
    else if ( vr === "UL") {
        byteOffset = this.writeUint32Array(byteOffset, value);
    }
    else if ( vr === "SL") {
        byteOffset = this.writeInt32Array(byteOffset, value);
    }
    else if ( vr === "FL") {
        byteOffset = this.writeFloat32Array(byteOffset, value);
    }
    else if ( vr === "FD") {
        byteOffset = this.writeFloat64Array(byteOffset, value);
    }
    else if ( vr === "SQ") {
        byteOffset = this.writeDataElementItems(byteOffset, value);
    }
    else if ( vr === "AT") {
        var hexString = value + '';
        var hexString1 = hexString.substring(1, 5);
        var hexString2 = hexString.substring(6, 10);
        var dec1 = parseInt(hexString1, 16);
        var dec2 = parseInt(hexString2, 16);
        value = new Uint16Array([dec1, dec2]);
        byteOffset = this.writeUint16Array(byteOffset, value);
    }
    else {
        byteOffset = this.writeStringArray(byteOffset, value);
    }

    // return new offset
    return byteOffset;
};

/**
 * Write a pixel data element.
 * @param {String} vr The data Value Representation (VR).
 * @param {String} vl The data Value Length (VL).
 * @param {Number} byteOffset The offset to start writing from.
 * @param {Array} value The array to write.
 * @returns {Number} The new offset position.
 */
dwv.dicom.DataWriter.prototype.writePixelDataElementValue = function (vr, vl, byteOffset, value) {
    // explicit length
    if (vl !== "u/l") {
        var finalValue = value[0];
        // flatten multi frame
        if (value.length > 1) {
            finalValue = dwv.dicom.flattenArrayOfTypedArrays(value);
        }
        // write
        byteOffset = this.writeDataElementValue(vr, byteOffset, finalValue);
    } else {
        // pixel data as sequence
        var item = {};
        // first item: basic offset table
        item.xFFFEE000 = {
            'tag': { group: "0xFFFE",
                element: "0xE000",
                name: "xFFFEE000" },
            'vr': "UN",
            'vl': 0,
            'value': []
        };
        // data
        for (var i = 0; i < value.length; ++i) {
            item[i] = {
                'tag': { group: "0xFFFE",
                    element: "0xE000",
                    name: "xFFFEE000" },
                'vr': vr,
                'vl': value[i].length,
                'value': value[i]
            };
        }
        // sequence delimitation item
        item.end = {
            'tag': { group: "0xFFFE",
                element: "0xE0DD",
                name: "xFFFEE0DD" },
            'vr': "UN",
            'vl': 0,
            'value': []
        };
        // write
        byteOffset = this.writeDataElementItems(byteOffset, [item]);
    }

    // return new offset
    return byteOffset;
};

/**
 * Write a data element.
 * @param {Object} element The DICOM data element to write.
 * @param {Number} byteOffset The offset to start writing from.
 * @returns {Number} The new offset position.
 */
dwv.dicom.DataWriter.prototype.writeDataElement = function (element, byteOffset) {
    var isTagWithVR = dwv.dicom.isTagWithVR(element.tag.group, element.tag.element);
    var is32bitVLVR = dwv.dicom.is32bitVLVR(element.vr);
    // group
    byteOffset = this.writeHex(byteOffset, element.tag.group);
    // element
    byteOffset = this.writeHex(byteOffset, element.tag.element);
    // VR
    if ( isTagWithVR ) {
        byteOffset = this.writeString(byteOffset, element.vr);
        // reserved 2 bytes for 32bit VL
        if ( is32bitVLVR ) {
            byteOffset += 2;
        }
    }

    // update vl for sequence or item with implicit length
    var vl = element.vl;
    if ( dwv.dicom.isImplicitLengthSequence(element) ||
        dwv.dicom.isImplicitLengthItem(element) ||
        dwv.dicom.isImplicitLengthPixels(element) ) {
        vl = 0xffffffff;
    }
    // VL
    if ( is32bitVLVR || !isTagWithVR ) {
        byteOffset = this.writeUint32(byteOffset, vl);
    }
    else {
        byteOffset = this.writeUint16(byteOffset, vl);
    }

    // value
    var value = element.value;
    // check value
    if (typeof value === 'undefined') {
        value = [];
    }
    // write
    if (element.tag.name === "x7FE00010") {
        byteOffset = this.writePixelDataElementValue(element.vr, element.vl, byteOffset, value);
    } else {
        byteOffset = this.writeDataElementValue(element.vr, byteOffset, value);
    }

    // sequence delimitation item for sequence with implicit length
    if ( dwv.dicom.isImplicitLengthSequence(element) ) {
        var seqDelimElement = {
            'tag': { group: "0xFFFE",
                element: "0xE0DD",
                name: "SequenceDelimitationItem" },
            'vr': "NONE",
            'vl': 0,
            'value': []
        };
        byteOffset = this.writeDataElement(seqDelimElement, byteOffset);
    }

    // return new offset
    return byteOffset;
};


/**
 * Tell if a given syntax is supported for writing.
 * @param {String} syntax The transfer syntax to test.
 * @return {Boolean} True if a supported syntax.
 */
dwv.dicom.isWriteSupportedTransferSyntax = function (syntax) {
    return syntax === "1.2.840.10008.1.2.1"; // Explicit VR - Little Endian
};

/**
 * Is this element an implicit length sequence?
 * @param {Object} element The element to check.
 * @returns {Boolean} True if it is.
 */
dwv.dicom.isImplicitLengthSequence = function (element) {
    // sequence with no length
    return (element.vr === "SQ") &&
        (element.vl === "u/l");
};

/**
 * Is this element an implicit length item?
 * @param {Object} element The element to check.
 * @returns {Boolean} True if it is.
 */
dwv.dicom.isImplicitLengthItem = function (element) {
    // item with no length
    return (element.tag.name === "xFFFEE000") &&
        (element.vl === "u/l");
};

/**
 * Is this element an implicit length pixel data?
 * @param {Object} element The element to check.
 * @returns {Boolean} True if it is.
 */
dwv.dicom.isImplicitLengthPixels = function (element) {
    // pixel data with no length
    return (element.tag.name === "x7FE00010") &&
        (element.vl === "u/l");
};

/**
 * Helper method to flatten an array of typed arrays to 2D typed array
 * @param {Array} array of typed arrays
 * @returns {Object} a typed array containing all values
 */
dwv.dicom.flattenArrayOfTypedArrays = function(initialArray) {
    var initialArrayLength = initialArray.length;
    var arrayLength = initialArray[0].length;
    var flattenendArrayLength = initialArrayLength * arrayLength;

    var flattenedArray = new initialArray[0].constructor(flattenendArrayLength);

    for (var i = 0; i < initialArrayLength; i++) {
        var indexFlattenedArray = i * arrayLength;
        flattenedArray.set(initialArray[i], indexFlattenedArray);
    }
    return flattenedArray;
};

/**
 * DICOM writer.
 * @constructor
 */
dwv.dicom.DicomWriter = function () {

    // possible tag actions
    var actions = {
        'copy': function (item) { return item; },
        'remove': function () { return null; },
        'clear': function (item) {
            item.value[0] = "";
            item.vl = 0;
            item.endOffset = item.startOffset;
            return item;
        },
        'replace': function (item, value) {
            item.value[0] = value;
            item.vl = value.length;
            item.endOffset = item.startOffset + value.length;
            return item;
        }
    };

    // default rules: just copy
    var defaultRules = {
        'default': {action: 'copy', value: null }
    };

    /**
     * Public (modifiable) rules.
     * Set of objects as:
     *   name : { action: 'actionName', value: 'optionalValue }
     * The names are either 'default', tagName or groupName.
     * Each DICOM element will be checked to see if a rule is applicable.
     * First checked by tagName and then by groupName,
     * if nothing is found the default rule is applied.
     */
    this.rules = defaultRules;

    /**
     * Example anonymisation rules.
     */
    this.anonymisationRules = {
        'default': {action: 'remove', value: null },
        'PatientName': {action: 'replace', value: 'Anonymized'}, // tag
        'Meta Element' : {action: 'copy', value: null }, // group 'x0002'
        'Acquisition' : {action: 'copy', value: null }, // group 'x0018'
        'Image Presentation' : {action: 'copy', value: null }, // group 'x0028'
        'Procedure' : {action: 'copy', value: null }, // group 'x0040'
        'Pixel Data' : {action: 'copy', value: null } // group 'x7fe0'
    };

    /**
     * Get the element to write according to the class rules.
     * Priority order: tagName, groupName, default.
     * @param {Object} element The element to check
     * @return {Object} The element to write, can be null.
     */
    this.getElementToWrite = function (element) {
        // get group and tag string name
        var tagName = null;
        var dict = dwv.dicom.dictionary;
        var group = element.tag.group;
        var groupName = dwv.dicom.TagGroups[group.substr(1)]; // remove first 0

        if ( typeof dict[group] !== 'undefined' && typeof dict[group][element.tag.element] !== 'undefined') {
            tagName = dict[group][element.tag.element][2];
        }
        // apply rules:
        var rule;
        // 1. tag itself
        if (typeof this.rules[element.tag.name] !== 'undefined') {
        	rule = this.rules[element.tag.name];
        }
        // 2. tag name
        else if ( tagName !== null && typeof this.rules[tagName] !== 'undefined' ) {
            rule = this.rules[tagName];
        }
        // 3. group name
        else if ( typeof this.rules[groupName] !== 'undefined' ) {
            rule = this.rules[groupName];
        }
        // 4. default
        else {
            rule = this.rules['default'];
        }
        // apply action on element and return
        return actions[rule.action](element, rule.value);
    };
};

/**
 * Get the ArrayBuffer corresponding to input DICOM elements.
 * @param {Array} dicomElements The wrapped elements to write.
 * @returns {ArrayBuffer} The elements as a buffer.
 */
dwv.dicom.DicomWriter.prototype.getBuffer = function (dicomElements) {
    // array keys
    var keys = Object.keys(dicomElements);

    // transfer syntax
    var syntax = dwv.dicom.cleanString(dicomElements.x00020010.value[0]);

    // check support
    if (!dwv.dicom.isWriteSupportedTransferSyntax(syntax)) {
        throw new Error("Unsupported DICOM transfer syntax: '"+syntax+
            "' ("+dwv.dicom.getTransferSyntaxName(syntax)+")");
    }
    
    // calculate buffer size and split elements (meta and non meta)
    var size = 128 + 4; // DICM
    var metaElements = [];
    var rawElements = [];
    var element;
    var groupName;
    for ( var i = 0, leni = keys.length; i < leni; ++i ) {
        element = this.getElementToWrite(dicomElements[keys[i]]);
        if ( element !== null ) {

            // size
            size += dwv.dicom.getDataElementPrefixByteSize(element.vr);
            var realVl = element.endOffset - element.startOffset;
            size += parseInt(realVl, 10);

            // add size of sequence delimitation item
            if ( dwv.dicom.isImplicitLengthSequence(element) ) {
                size += dwv.dicom.getDataElementPrefixByteSize("NONE");
            }

            // sort element
            groupName = dwv.dicom.TagGroups[element.tag.group.substr(1)]; // remove first 0
            if ( groupName === 'Meta Element' ) {
                metaElements.push(element);
            }
            else {
                rawElements.push(element);
            }
        }
    }

    // create buffer
    var buffer = new ArrayBuffer(size);
    var writer = new dwv.dicom.DataWriter(buffer);
    var offset = 128;
    // DICM
    offset = writer.writeString(offset, "DICM");
    // write meta
    for ( var j = 0, lenj = metaElements.length; j < lenj; ++j ) {
        offset = writer.writeDataElement(metaElements[j], offset);
    }
    // write non meta
    for ( var k = 0, lenk = rawElements.length; k < lenk; ++k ) {
        offset = writer.writeDataElement(rawElements[k], offset);
    }

    // return
    return buffer;
};

// namespaces
var dwv = dwv || {};
dwv.dicom = dwv.dicom || {};

/**
 * DICOM tag dictionary.
 * Generated using xml standard conversion
 *  from {@link https://github.com/ivmartel/dcmbench/tree/master/view/part06}
 *  with {@link http://medical.nema.org/medical/dicom/current/source/docbook/part06/part06.xml}
 * Conversion changes:
 * - (vr) "See Note" -> "NONE", "OB or OW" -> "ox", "US or SS" -> "xs"
 * - added "GenericGroupLength" element to each group
 * Local changes:
 * - tag numbers with 'xx' were replaced with '00', 'xxx' with '001' and 'xxxx' with '0004'
 */
dwv.dicom.dictionary = {
    '0x0000': {
        '0x0000': ['UL', '1', 'GroupLength'],
        '0x0001': ['UL', '1', 'CommandLengthToEnd'],
        '0x0002': ['UI', '1', 'AffectedSOPClassUID'],
        '0x0003': ['UI', '1', 'RequestedSOPClassUID'],
        '0x0010': ['CS', '1', 'CommandRecognitionCode'],
        '0x0100': ['US', '1', 'CommandField'],
        '0x0110': ['US', '1', 'MessageID'],
        '0x0120': ['US', '1', 'MessageIDBeingRespondedTo'],
        '0x0200': ['AE', '1', 'Initiator'],
        '0x0300': ['AE', '1', 'Receiver'],
        '0x0400': ['AE', '1', 'FindLocation'],
        '0x0600': ['AE', '1', 'MoveDestination'],
        '0x0700': ['US', '1', 'Priority'],
        '0x0800': ['US', '1', 'DataSetType'],
        '0x0850': ['US', '1', 'NumberOfMatches'],
        '0x0860': ['US', '1', 'ResponseSequenceNumber'],
        '0x0900': ['US', '1', 'Status'],
        '0x0901': ['AT', '1-n', 'OffendingElement'],
        '0x0902': ['LO', '1', 'ErrorComment'],
        '0x0903': ['US', '1', 'ErrorID'],
        '0x0904': ['OT', '1-n', 'ErrorInformation'],
        '0x1000': ['UI', '1', 'AffectedSOPInstanceUID'],
        '0x1001': ['UI', '1', 'RequestedSOPInstanceUID'],
        '0x1002': ['US', '1', 'EventTypeID'],
        '0x1003': ['OT', '1-n', 'EventInformation'],
        '0x1005': ['AT', '1-n', 'AttributeIdentifierList'],
        '0x1007': ['AT', '1-n', 'ModificationList'],
        '0x1008': ['US', '1', 'ActionTypeID'],
        '0x1009': ['OT', '1-n', 'ActionInformation'],
        '0x1013': ['UI', '1-n', 'SuccessfulSOPInstanceUIDList'],
        '0x1014': ['UI', '1-n', 'FailedSOPInstanceUIDList'],
        '0x1015': ['UI', '1-n', 'WarningSOPInstanceUIDList'],
        '0x1020': ['US', '1', 'NumberOfRemainingSuboperations'],
        '0x1021': ['US', '1', 'NumberOfCompletedSuboperations'],
        '0x1022': ['US', '1', 'NumberOfFailedSuboperations'],
        '0x1023': ['US', '1', 'NumberOfWarningSuboperations'],
        '0x1030': ['AE', '1', 'MoveOriginatorApplicationEntityTitle'],
        '0x1031': ['US', '1', 'MoveOriginatorMessageID'],
        '0x4000': ['AT', '1', 'DialogReceiver'],
        '0x4010': ['AT', '1', 'TerminalType'],
        '0x5010': ['SH', '1', 'MessageSetID'],
        '0x5020': ['SH', '1', 'EndMessageSet'],
        '0x5110': ['AT', '1', 'DisplayFormat'],
        '0x5120': ['AT', '1', 'PagePositionID'],
        '0x5130': ['CS', '1', 'TextFormatID'],
        '0x5140': ['CS', '1', 'NormalReverse'],
        '0x5150': ['CS', '1', 'AddGrayScale'],
        '0x5160': ['CS', '1', 'Borders'],
        '0x5170': ['IS', '1', 'Copies'],
        '0x5180': ['CS', '1', 'OldMagnificationType'],
        '0x5190': ['CS', '1', 'Erase'],
        '0x51A0': ['CS', '1', 'Print'],
        '0x51B0': ['US', '1-n', 'Overlays']
    },
    '0x0002': {
        '0x0000': ['UL', '1', 'FileMetaInformationGroupLength'],
        '0x0001': ['OB', '1', 'FileMetaInformationVersion'],
        '0x0002': ['UI', '1', 'MediaStorageSOPClassUID'],
        '0x0003': ['UI', '1', 'MediaStorageSOPInstanceUID'],
        '0x0010': ['UI', '1', 'TransferSyntaxUID'],
        '0x0012': ['UI', '1', 'ImplementationClassUID'],
        '0x0013': ['SH', '1', 'ImplementationVersionName'],
        '0x0016': ['AE', '1', 'SourceApplicationEntityTitle'],
        '0x0017': ['AE', '1', 'SendingApplicationEntityTitle'],
        '0x0018': ['AE', '1', 'ReceivingApplicationEntityTitle'],
        '0x0100': ['UI', '1', 'PrivateInformationCreatorUID'],
        '0x0102': ['OB', '1', 'PrivateInformation']
    },
    '0x0004': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x1130': ['CS', '1', 'FileSetID'],
        '0x1141': ['CS', '1-8', 'FileSetDescriptorFileID'],
        '0x1142': ['CS', '1', 'SpecificCharacterSetOfFileSetDescriptorFile'],
        '0x1200': ['UL', '1', 'OffsetOfTheFirstDirectoryRecordOfTheRootDirectoryEntity'],
        '0x1202': ['UL', '1', 'OffsetOfTheLastDirectoryRecordOfTheRootDirectoryEntity'],
        '0x1212': ['US', '1', 'FileSetConsistencyFlag'],
        '0x1220': ['SQ', '1', 'DirectoryRecordSequence'],
        '0x1400': ['UL', '1', 'OffsetOfTheNextDirectoryRecord'],
        '0x1410': ['US', '1', 'RecordInUseFlag'],
        '0x1420': ['UL', '1', 'OffsetOfReferencedLowerLevelDirectoryEntity'],
        '0x1430': ['CS', '1', 'DirectoryRecordType'],
        '0x1432': ['UI', '1', 'PrivateRecordUID'],
        '0x1500': ['CS', '1-8', 'ReferencedFileID'],
        '0x1504': ['UL', '1', 'MRDRDirectoryRecordOffset'],
        '0x1510': ['UI', '1', 'ReferencedSOPClassUIDInFile'],
        '0x1511': ['UI', '1', 'ReferencedSOPInstanceUIDInFile'],
        '0x1512': ['UI', '1', 'ReferencedTransferSyntaxUIDInFile'],
        '0x151A': ['UI', '1-n', 'ReferencedRelatedGeneralSOPClassUIDInFile'],
        '0x1600': ['UL', '1', 'NumberOfReferences']
    },
    '0x0008': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0001': ['UL', '1', 'LengthToEnd'],
        '0x0005': ['CS', '1-n', 'SpecificCharacterSet'],
        '0x0006': ['SQ', '1', 'LanguageCodeSequence'],
        '0x0008': ['CS', '2-n', 'ImageType'],
        '0x0010': ['SH', '1', 'RecognitionCode'],
        '0x0012': ['DA', '1', 'InstanceCreationDate'],
        '0x0013': ['TM', '1', 'InstanceCreationTime'],
        '0x0014': ['UI', '1', 'InstanceCreatorUID'],
        '0x0015': ['DT', '1', 'InstanceCoercionDateTime'],
        '0x0016': ['UI', '1', 'SOPClassUID'],
        '0x0018': ['UI', '1', 'SOPInstanceUID'],
        '0x001A': ['UI', '1-n', 'RelatedGeneralSOPClassUID'],
        '0x001B': ['UI', '1', 'OriginalSpecializedSOPClassUID'],
        '0x0020': ['DA', '1', 'StudyDate'],
        '0x0021': ['DA', '1', 'SeriesDate'],
        '0x0022': ['DA', '1', 'AcquisitionDate'],
        '0x0023': ['DA', '1', 'ContentDate'],
        '0x0024': ['DA', '1', 'OverlayDate'],
        '0x0025': ['DA', '1', 'CurveDate'],
        '0x002A': ['DT', '1', 'AcquisitionDateTime'],
        '0x0030': ['TM', '1', 'StudyTime'],
        '0x0031': ['TM', '1', 'SeriesTime'],
        '0x0032': ['TM', '1', 'AcquisitionTime'],
        '0x0033': ['TM', '1', 'ContentTime'],
        '0x0034': ['TM', '1', 'OverlayTime'],
        '0x0035': ['TM', '1', 'CurveTime'],
        '0x0040': ['US', '1', 'DataSetType'],
        '0x0041': ['LO', '1', 'DataSetSubtype'],
        '0x0042': ['CS', '1', 'NuclearMedicineSeriesType'],
        '0x0050': ['SH', '1', 'AccessionNumber'],
        '0x0051': ['SQ', '1', 'IssuerOfAccessionNumberSequence'],
        '0x0052': ['CS', '1', 'QueryRetrieveLevel'],
        '0x0053': ['CS', '1', 'QueryRetrieveView'],
        '0x0054': ['AE', '1-n', 'RetrieveAETitle'],
        '0x0056': ['CS', '1', 'InstanceAvailability'],
        '0x0058': ['UI', '1-n', 'FailedSOPInstanceUIDList'],
        '0x0060': ['CS', '1', 'Modality'],
        '0x0061': ['CS', '1-n', 'ModalitiesInStudy'],
        '0x0062': ['UI', '1-n', 'SOPClassesInStudy'],
        '0x0064': ['CS', '1', 'ConversionType'],
        '0x0068': ['CS', '1', 'PresentationIntentType'],
        '0x0070': ['LO', '1', 'Manufacturer'],
        '0x0080': ['LO', '1', 'InstitutionName'],
        '0x0081': ['ST', '1', 'InstitutionAddress'],
        '0x0082': ['SQ', '1', 'InstitutionCodeSequence'],
        '0x0090': ['PN', '1', 'ReferringPhysicianName'],
        '0x0092': ['ST', '1', 'ReferringPhysicianAddress'],
        '0x0094': ['SH', '1-n', 'ReferringPhysicianTelephoneNumbers'],
        '0x0096': ['SQ', '1', 'ReferringPhysicianIdentificationSequence'],
        '0x009C': ['PN', '1-n', 'ConsultingPhysicianName'],
        '0x009D': ['SQ', '1', 'ConsultingPhysicianIdentificationSequence'],
        '0x0100': ['SH', '1', 'CodeValue'],
        '0x0101': ['LO', '1', 'ExtendedCodeValue'],
        '0x0102': ['SH', '1', 'CodingSchemeDesignator'],
        '0x0103': ['SH', '1', 'CodingSchemeVersion'],
        '0x0104': ['LO', '1', 'CodeMeaning'],
        '0x0105': ['CS', '1', 'MappingResource'],
        '0x0106': ['DT', '1', 'ContextGroupVersion'],
        '0x0107': ['DT', '1', 'ContextGroupLocalVersion'],
        '0x0108': ['LT', '1', 'ExtendedCodeMeaning'],
        '0x010B': ['CS', '1', 'ContextGroupExtensionFlag'],
        '0x010C': ['UI', '1', 'CodingSchemeUID'],
        '0x010D': ['UI', '1', 'ContextGroupExtensionCreatorUID'],
        '0x010F': ['CS', '1', 'ContextIdentifier'],
        '0x0110': ['SQ', '1', 'CodingSchemeIdentificationSequence'],
        '0x0112': ['LO', '1', 'CodingSchemeRegistry'],
        '0x0114': ['ST', '1', 'CodingSchemeExternalID'],
        '0x0115': ['ST', '1', 'CodingSchemeName'],
        '0x0116': ['ST', '1', 'CodingSchemeResponsibleOrganization'],
        '0x0117': ['UI', '1', 'ContextUID'],
        '0x0118': ['UI', '1', 'MappingResourceUID'],
        '0x0119': ['UC', '1', 'LongCodeValue'],
        '0x0120': ['UR', '1', 'URNCodeValue'],
        '0x0121': ['SQ', '1', 'EquivalentCodeSequence'],
        '0x0201': ['SH', '1', 'TimezoneOffsetFromUTC'],
        '0x0300': ['SQ', '1', 'PrivateDataElementCharacteristicsSequence'],
        '0x0301': ['US', '1', 'PrivateGroupReference'],
        '0x0302': ['LO', '1', 'PrivateCreatorReference'],
        '0x0303': ['CS', '1', 'BlockIdentifyingInformationStatus'],
        '0x0304': ['US', '1-n', 'NonidentifyingPrivateElements'],
        '0x0306': ['US', '1-n', 'IdentifyingPrivateElements'],
        '0x0305': ['SQ', '1', 'DeidentificationActionSequence'],
        '0x0307': ['CS', '1', 'DeidentificationAction'],
        '0x1000': ['AE', '1', 'NetworkID'],
        '0x1010': ['SH', '1', 'StationName'],
        '0x1030': ['LO', '1', 'StudyDescription'],
        '0x1032': ['SQ', '1', 'ProcedureCodeSequence'],
        '0x103E': ['LO', '1', 'SeriesDescription'],
        '0x103F': ['SQ', '1', 'SeriesDescriptionCodeSequence'],
        '0x1040': ['LO', '1', 'InstitutionalDepartmentName'],
        '0x1048': ['PN', '1-n', 'PhysiciansOfRecord'],
        '0x1049': ['SQ', '1', 'PhysiciansOfRecordIdentificationSequence'],
        '0x1050': ['PN', '1-n', 'PerformingPhysicianName'],
        '0x1052': ['SQ', '1', 'PerformingPhysicianIdentificationSequence'],
        '0x1060': ['PN', '1-n', 'NameOfPhysiciansReadingStudy'],
        '0x1062': ['SQ', '1', 'PhysiciansReadingStudyIdentificationSequence'],
        '0x1070': ['PN', '1-n', 'OperatorsName'],
        '0x1072': ['SQ', '1', 'OperatorIdentificationSequence'],
        '0x1080': ['LO', '1-n', 'AdmittingDiagnosesDescription'],
        '0x1084': ['SQ', '1', 'AdmittingDiagnosesCodeSequence'],
        '0x1090': ['LO', '1', 'ManufacturerModelName'],
        '0x1100': ['SQ', '1', 'ReferencedResultsSequence'],
        '0x1110': ['SQ', '1', 'ReferencedStudySequence'],
        '0x1111': ['SQ', '1', 'ReferencedPerformedProcedureStepSequence'],
        '0x1115': ['SQ', '1', 'ReferencedSeriesSequence'],
        '0x1120': ['SQ', '1', 'ReferencedPatientSequence'],
        '0x1125': ['SQ', '1', 'ReferencedVisitSequence'],
        '0x1130': ['SQ', '1', 'ReferencedOverlaySequence'],
        '0x1134': ['SQ', '1', 'ReferencedStereometricInstanceSequence'],
        '0x113A': ['SQ', '1', 'ReferencedWaveformSequence'],
        '0x1140': ['SQ', '1', 'ReferencedImageSequence'],
        '0x1145': ['SQ', '1', 'ReferencedCurveSequence'],
        '0x114A': ['SQ', '1', 'ReferencedInstanceSequence'],
        '0x114B': ['SQ', '1', 'ReferencedRealWorldValueMappingInstanceSequence'],
        '0x1150': ['UI', '1', 'ReferencedSOPClassUID'],
        '0x1155': ['UI', '1', 'ReferencedSOPInstanceUID'],
        '0x115A': ['UI', '1-n', 'SOPClassesSupported'],
        '0x1160': ['IS', '1-n', 'ReferencedFrameNumber'],
        '0x1161': ['UL', '1-n', 'SimpleFrameList'],
        '0x1162': ['UL', '3-3n', 'CalculatedFrameList'],
        '0x1163': ['FD', '2', 'TimeRange'],
        '0x1164': ['SQ', '1', 'FrameExtractionSequence'],
        '0x1167': ['UI', '1', 'MultiFrameSourceSOPInstanceUID'],
        '0x1190': ['UR', '1', 'RetrieveURL'],
        '0x1195': ['UI', '1', 'TransactionUID'],
        '0x1196': ['US', '1', 'WarningReason'],
        '0x1197': ['US', '1', 'FailureReason'],
        '0x1198': ['SQ', '1', 'FailedSOPSequence'],
        '0x1199': ['SQ', '1', 'ReferencedSOPSequence'],
        '0x1200': ['SQ', '1', 'StudiesContainingOtherReferencedInstancesSequence'],
        '0x1250': ['SQ', '1', 'RelatedSeriesSequence'],
        '0x2110': ['CS', '1', 'LossyImageCompressionRetired'],
        '0x2111': ['ST', '1', 'DerivationDescription'],
        '0x2112': ['SQ', '1', 'SourceImageSequence'],
        '0x2120': ['SH', '1', 'StageName'],
        '0x2122': ['IS', '1', 'StageNumber'],
        '0x2124': ['IS', '1', 'NumberOfStages'],
        '0x2127': ['SH', '1', 'ViewName'],
        '0x2128': ['IS', '1', 'ViewNumber'],
        '0x2129': ['IS', '1', 'NumberOfEventTimers'],
        '0x212A': ['IS', '1', 'NumberOfViewsInStage'],
        '0x2130': ['DS', '1-n', 'EventElapsedTimes'],
        '0x2132': ['LO', '1-n', 'EventTimerNames'],
        '0x2133': ['SQ', '1', 'EventTimerSequence'],
        '0x2134': ['FD', '1', 'EventTimeOffset'],
        '0x2135': ['SQ', '1', 'EventCodeSequence'],
        '0x2142': ['IS', '1', 'StartTrim'],
        '0x2143': ['IS', '1', 'StopTrim'],
        '0x2144': ['IS', '1', 'RecommendedDisplayFrameRate'],
        '0x2200': ['CS', '1', 'TransducerPosition'],
        '0x2204': ['CS', '1', 'TransducerOrientation'],
        '0x2208': ['CS', '1', 'AnatomicStructure'],
        '0x2218': ['SQ', '1', 'AnatomicRegionSequence'],
        '0x2220': ['SQ', '1', 'AnatomicRegionModifierSequence'],
        '0x2228': ['SQ', '1', 'PrimaryAnatomicStructureSequence'],
        '0x2229': ['SQ', '1', 'AnatomicStructureSpaceOrRegionSequence'],
        '0x2230': ['SQ', '1', 'PrimaryAnatomicStructureModifierSequence'],
        '0x2240': ['SQ', '1', 'TransducerPositionSequence'],
        '0x2242': ['SQ', '1', 'TransducerPositionModifierSequence'],
        '0x2244': ['SQ', '1', 'TransducerOrientationSequence'],
        '0x2246': ['SQ', '1', 'TransducerOrientationModifierSequence'],
        '0x2251': ['SQ', '1', 'AnatomicStructureSpaceOrRegionCodeSequenceTrial'],
        '0x2253': ['SQ', '1', 'AnatomicPortalOfEntranceCodeSequenceTrial'],
        '0x2255': ['SQ', '1', 'AnatomicApproachDirectionCodeSequenceTrial'],
        '0x2256': ['ST', '1', 'AnatomicPerspectiveDescriptionTrial'],
        '0x2257': ['SQ', '1', 'AnatomicPerspectiveCodeSequenceTrial'],
        '0x2258': ['ST', '1', 'AnatomicLocationOfExaminingInstrumentDescriptionTrial'],
        '0x2259': ['SQ', '1', 'AnatomicLocationOfExaminingInstrumentCodeSequenceTrial'],
        '0x225A': ['SQ', '1', 'AnatomicStructureSpaceOrRegionModifierCodeSequenceTrial'],
        '0x225C': ['SQ', '1', 'OnAxisBackgroundAnatomicStructureCodeSequenceTrial'],
        '0x3001': ['SQ', '1', 'AlternateRepresentationSequence'],
        '0x3010': ['UI', '1-n', 'IrradiationEventUID'],
        '0x3011': ['SQ', '1', 'SourceIrradiationEventSequence'],
        '0x3012': ['UI', '1', 'RadiopharmaceuticalAdministrationEventUID'],
        '0x4000': ['LT', '1', 'IdentifyingComments'],
        '0x9007': ['CS', '4', 'FrameType'],
        '0x9092': ['SQ', '1', 'ReferencedImageEvidenceSequence'],
        '0x9121': ['SQ', '1', 'ReferencedRawDataSequence'],
        '0x9123': ['UI', '1', 'CreatorVersionUID'],
        '0x9124': ['SQ', '1', 'DerivationImageSequence'],
        '0x9154': ['SQ', '1', 'SourceImageEvidenceSequence'],
        '0x9205': ['CS', '1', 'PixelPresentation'],
        '0x9206': ['CS', '1', 'VolumetricProperties'],
        '0x9207': ['CS', '1', 'VolumeBasedCalculationTechnique'],
        '0x9208': ['CS', '1', 'ComplexImageComponent'],
        '0x9209': ['CS', '1', 'AcquisitionContrast'],
        '0x9215': ['SQ', '1', 'DerivationCodeSequence'],
        '0x9237': ['SQ', '1', 'ReferencedPresentationStateSequence'],
        '0x9410': ['SQ', '1', 'ReferencedOtherPlaneSequence'],
        '0x9458': ['SQ', '1', 'FrameDisplaySequence'],
        '0x9459': ['FL', '1', 'RecommendedDisplayFrameRateInFloat'],
        '0x9460': ['CS', '1', 'SkipFrameRangeFlag']
    },
    '0x0010': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0010': ['PN', '1', 'PatientName'],
        '0x0020': ['LO', '1', 'PatientID'],
        '0x0021': ['LO', '1', 'IssuerOfPatientID'],
        '0x0022': ['CS', '1', 'TypeOfPatientID'],
        '0x0024': ['SQ', '1', 'IssuerOfPatientIDQualifiersSequence'],
        '0x0030': ['DA', '1', 'PatientBirthDate'],
        '0x0032': ['TM', '1', 'PatientBirthTime'],
        '0x0040': ['CS', '1', 'PatientSex'],
        '0x0050': ['SQ', '1', 'PatientInsurancePlanCodeSequence'],
        '0x0101': ['SQ', '1', 'PatientPrimaryLanguageCodeSequence'],
        '0x0102': ['SQ', '1', 'PatientPrimaryLanguageModifierCodeSequence'],
        '0x0200': ['CS', '1', 'QualityControlSubject'],
        '0x0201': ['SQ', '1', 'QualityControlSubjectTypeCodeSequence'],
        '0x1000': ['LO', '1-n', 'OtherPatientIDs'],
        '0x1001': ['PN', '1-n', 'OtherPatientNames'],
        '0x1002': ['SQ', '1', 'OtherPatientIDsSequence'],
        '0x1005': ['PN', '1', 'PatientBirthName'],
        '0x1010': ['AS', '1', 'PatientAge'],
        '0x1020': ['DS', '1', 'PatientSize'],
        '0x1021': ['SQ', '1', 'PatientSizeCodeSequence'],
        '0x1030': ['DS', '1', 'PatientWeight'],
        '0x1040': ['LO', '1', 'PatientAddress'],
        '0x1050': ['LO', '1-n', 'InsurancePlanIdentification'],
        '0x1060': ['PN', '1', 'PatientMotherBirthName'],
        '0x1080': ['LO', '1', 'MilitaryRank'],
        '0x1081': ['LO', '1', 'BranchOfService'],
        '0x1090': ['LO', '1', 'MedicalRecordLocator'],
        '0x1100': ['SQ', '1', 'ReferencedPatientPhotoSequence'],
        '0x2000': ['LO', '1-n', 'MedicalAlerts'],
        '0x2110': ['LO', '1-n', 'Allergies'],
        '0x2150': ['LO', '1', 'CountryOfResidence'],
        '0x2152': ['LO', '1', 'RegionOfResidence'],
        '0x2154': ['SH', '1-n', 'PatientTelephoneNumbers'],
        '0x2155': ['LT', '1', 'PatientTelecomInformation'],
        '0x2160': ['SH', '1', 'EthnicGroup'],
        '0x2180': ['SH', '1', 'Occupation'],
        '0x21A0': ['CS', '1', 'SmokingStatus'],
        '0x21B0': ['LT', '1', 'AdditionalPatientHistory'],
        '0x21C0': ['US', '1', 'PregnancyStatus'],
        '0x21D0': ['DA', '1', 'LastMenstrualDate'],
        '0x21F0': ['LO', '1', 'PatientReligiousPreference'],
        '0x2201': ['LO', '1', 'PatientSpeciesDescription'],
        '0x2202': ['SQ', '1', 'PatientSpeciesCodeSequence'],
        '0x2203': ['CS', '1', 'PatientSexNeutered'],
        '0x2210': ['CS', '1', 'AnatomicalOrientationType'],
        '0x2292': ['LO', '1', 'PatientBreedDescription'],
        '0x2293': ['SQ', '1', 'PatientBreedCodeSequence'],
        '0x2294': ['SQ', '1', 'BreedRegistrationSequence'],
        '0x2295': ['LO', '1', 'BreedRegistrationNumber'],
        '0x2296': ['SQ', '1', 'BreedRegistryCodeSequence'],
        '0x2297': ['PN', '1', 'ResponsiblePerson'],
        '0x2298': ['CS', '1', 'ResponsiblePersonRole'],
        '0x2299': ['LO', '1', 'ResponsibleOrganization'],
        '0x4000': ['LT', '1', 'PatientComments'],
        '0x9431': ['FL', '1', 'ExaminedBodyThickness']
    },
    '0x0012': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0010': ['LO', '1', 'ClinicalTrialSponsorName'],
        '0x0020': ['LO', '1', 'ClinicalTrialProtocolID'],
        '0x0021': ['LO', '1', 'ClinicalTrialProtocolName'],
        '0x0030': ['LO', '1', 'ClinicalTrialSiteID'],
        '0x0031': ['LO', '1', 'ClinicalTrialSiteName'],
        '0x0040': ['LO', '1', 'ClinicalTrialSubjectID'],
        '0x0042': ['LO', '1', 'ClinicalTrialSubjectReadingID'],
        '0x0050': ['LO', '1', 'ClinicalTrialTimePointID'],
        '0x0051': ['ST', '1', 'ClinicalTrialTimePointDescription'],
        '0x0060': ['LO', '1', 'ClinicalTrialCoordinatingCenterName'],
        '0x0062': ['CS', '1', 'PatientIdentityRemoved'],
        '0x0063': ['LO', '1-n', 'DeidentificationMethod'],
        '0x0064': ['SQ', '1', 'DeidentificationMethodCodeSequence'],
        '0x0071': ['LO', '1', 'ClinicalTrialSeriesID'],
        '0x0072': ['LO', '1', 'ClinicalTrialSeriesDescription'],
        '0x0081': ['LO', '1', 'ClinicalTrialProtocolEthicsCommitteeName'],
        '0x0082': ['LO', '1', 'ClinicalTrialProtocolEthicsCommitteeApprovalNumber'],
        '0x0083': ['SQ', '1', 'ConsentForClinicalTrialUseSequence'],
        '0x0084': ['CS', '1', 'DistributionType'],
        '0x0085': ['CS', '1', 'ConsentForDistributionFlag']
    },
    '0x0014': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0023': ['ST', '1-n', 'CADFileFormat'],
        '0x0024': ['ST', '1-n', 'ComponentReferenceSystem'],
        '0x0025': ['ST', '1-n', 'ComponentManufacturingProcedure'],
        '0x0028': ['ST', '1-n', 'ComponentManufacturer'],
        '0x0030': ['DS', '1-n', 'MaterialThickness'],
        '0x0032': ['DS', '1-n', 'MaterialPipeDiameter'],
        '0x0034': ['DS', '1-n', 'MaterialIsolationDiameter'],
        '0x0042': ['ST', '1-n', 'MaterialGrade'],
        '0x0044': ['ST', '1-n', 'MaterialPropertiesDescription'],
        '0x0045': ['ST', '1-n', 'MaterialPropertiesFileFormatRetired'],
        '0x0046': ['LT', '1', 'MaterialNotes'],
        '0x0050': ['CS', '1', 'ComponentShape'],
        '0x0052': ['CS', '1', 'CurvatureType'],
        '0x0054': ['DS', '1', 'OuterDiameter'],
        '0x0056': ['DS', '1', 'InnerDiameter'],
        '0x1010': ['ST', '1', 'ActualEnvironmentalConditions'],
        '0x1020': ['DA', '1', 'ExpiryDate'],
        '0x1040': ['ST', '1', 'EnvironmentalConditions'],
        '0x2002': ['SQ', '1', 'EvaluatorSequence'],
        '0x2004': ['IS', '1', 'EvaluatorNumber'],
        '0x2006': ['PN', '1', 'EvaluatorName'],
        '0x2008': ['IS', '1', 'EvaluationAttempt'],
        '0x2012': ['SQ', '1', 'IndicationSequence'],
        '0x2014': ['IS', '1', 'IndicationNumber'],
        '0x2016': ['SH', '1', 'IndicationLabel'],
        '0x2018': ['ST', '1', 'IndicationDescription'],
        '0x201A': ['CS', '1-n', 'IndicationType'],
        '0x201C': ['CS', '1', 'IndicationDisposition'],
        '0x201E': ['SQ', '1', 'IndicationROISequence'],
        '0x2030': ['SQ', '1', 'IndicationPhysicalPropertySequence'],
        '0x2032': ['SH', '1', 'PropertyLabel'],
        '0x2202': ['IS', '1', 'CoordinateSystemNumberOfAxes'],
        '0x2204': ['SQ', '1', 'CoordinateSystemAxesSequence'],
        '0x2206': ['ST', '1', 'CoordinateSystemAxisDescription'],
        '0x2208': ['CS', '1', 'CoordinateSystemDataSetMapping'],
        '0x220A': ['IS', '1', 'CoordinateSystemAxisNumber'],
        '0x220C': ['CS', '1', 'CoordinateSystemAxisType'],
        '0x220E': ['CS', '1', 'CoordinateSystemAxisUnits'],
        '0x2210': ['OB', '1', 'CoordinateSystemAxisValues'],
        '0x2220': ['SQ', '1', 'CoordinateSystemTransformSequence'],
        '0x2222': ['ST', '1', 'TransformDescription'],
        '0x2224': ['IS', '1', 'TransformNumberOfAxes'],
        '0x2226': ['IS', '1-n', 'TransformOrderOfAxes'],
        '0x2228': ['CS', '1', 'TransformedAxisUnits'],
        '0x222A': ['DS', '1-n', 'CoordinateSystemTransformRotationAndScaleMatrix'],
        '0x222C': ['DS', '1-n', 'CoordinateSystemTransformTranslationMatrix'],
        '0x3011': ['DS', '1', 'InternalDetectorFrameTime'],
        '0x3012': ['DS', '1', 'NumberOfFramesIntegrated'],
        '0x3020': ['SQ', '1', 'DetectorTemperatureSequence'],
        '0x3022': ['ST', '1', 'SensorName'],
        '0x3024': ['DS', '1', 'HorizontalOffsetOfSensor'],
        '0x3026': ['DS', '1', 'VerticalOffsetOfSensor'],
        '0x3028': ['DS', '1', 'SensorTemperature'],
        '0x3040': ['SQ', '1', 'DarkCurrentSequence'],
        '0x3050': ['ox', '1', 'DarkCurrentCounts'],
        '0x3060': ['SQ', '1', 'GainCorrectionReferenceSequence'],
        '0x3070': ['ox', '1', 'AirCounts'],
        '0x3071': ['DS', '1', 'KVUsedInGainCalibration'],
        '0x3072': ['DS', '1', 'MAUsedInGainCalibration'],
        '0x3073': ['DS', '1', 'NumberOfFramesUsedForIntegration'],
        '0x3074': ['LO', '1', 'FilterMaterialUsedInGainCalibration'],
        '0x3075': ['DS', '1', 'FilterThicknessUsedInGainCalibration'],
        '0x3076': ['DA', '1', 'DateOfGainCalibration'],
        '0x3077': ['TM', '1', 'TimeOfGainCalibration'],
        '0x3080': ['OB', '1', 'BadPixelImage'],
        '0x3099': ['LT', '1', 'CalibrationNotes'],
        '0x4002': ['SQ', '1', 'PulserEquipmentSequence'],
        '0x4004': ['CS', '1', 'PulserType'],
        '0x4006': ['LT', '1', 'PulserNotes'],
        '0x4008': ['SQ', '1', 'ReceiverEquipmentSequence'],
        '0x400A': ['CS', '1', 'AmplifierType'],
        '0x400C': ['LT', '1', 'ReceiverNotes'],
        '0x400E': ['SQ', '1', 'PreAmplifierEquipmentSequence'],
        '0x400F': ['LT', '1', 'PreAmplifierNotes'],
        '0x4010': ['SQ', '1', 'TransmitTransducerSequence'],
        '0x4011': ['SQ', '1', 'ReceiveTransducerSequence'],
        '0x4012': ['US', '1', 'NumberOfElements'],
        '0x4013': ['CS', '1', 'ElementShape'],
        '0x4014': ['DS', '1', 'ElementDimensionA'],
        '0x4015': ['DS', '1', 'ElementDimensionB'],
        '0x4016': ['DS', '1', 'ElementPitchA'],
        '0x4017': ['DS', '1', 'MeasuredBeamDimensionA'],
        '0x4018': ['DS', '1', 'MeasuredBeamDimensionB'],
        '0x4019': ['DS', '1', 'LocationOfMeasuredBeamDiameter'],
        '0x401A': ['DS', '1', 'NominalFrequency'],
        '0x401B': ['DS', '1', 'MeasuredCenterFrequency'],
        '0x401C': ['DS', '1', 'MeasuredBandwidth'],
        '0x401D': ['DS', '1', 'ElementPitchB'],
        '0x4020': ['SQ', '1', 'PulserSettingsSequence'],
        '0x4022': ['DS', '1', 'PulseWidth'],
        '0x4024': ['DS', '1', 'ExcitationFrequency'],
        '0x4026': ['CS', '1', 'ModulationType'],
        '0x4028': ['DS', '1', 'Damping'],
        '0x4030': ['SQ', '1', 'ReceiverSettingsSequence'],
        '0x4031': ['DS', '1', 'AcquiredSoundpathLength'],
        '0x4032': ['CS', '1', 'AcquisitionCompressionType'],
        '0x4033': ['IS', '1', 'AcquisitionSampleSize'],
        '0x4034': ['DS', '1', 'RectifierSmoothing'],
        '0x4035': ['SQ', '1', 'DACSequence'],
        '0x4036': ['CS', '1', 'DACType'],
        '0x4038': ['DS', '1-n', 'DACGainPoints'],
        '0x403A': ['DS', '1-n', 'DACTimePoints'],
        '0x403C': ['DS', '1-n', 'DACAmplitude'],
        '0x4040': ['SQ', '1', 'PreAmplifierSettingsSequence'],
        '0x4050': ['SQ', '1', 'TransmitTransducerSettingsSequence'],
        '0x4051': ['SQ', '1', 'ReceiveTransducerSettingsSequence'],
        '0x4052': ['DS', '1', 'IncidentAngle'],
        '0x4054': ['ST', '1', 'CouplingTechnique'],
        '0x4056': ['ST', '1', 'CouplingMedium'],
        '0x4057': ['DS', '1', 'CouplingVelocity'],
        '0x4058': ['DS', '1', 'ProbeCenterLocationX'],
        '0x4059': ['DS', '1', 'ProbeCenterLocationZ'],
        '0x405A': ['DS', '1', 'SoundPathLength'],
        '0x405C': ['ST', '1', 'DelayLawIdentifier'],
        '0x4060': ['SQ', '1', 'GateSettingsSequence'],
        '0x4062': ['DS', '1', 'GateThreshold'],
        '0x4064': ['DS', '1', 'VelocityOfSound'],
        '0x4070': ['SQ', '1', 'CalibrationSettingsSequence'],
        '0x4072': ['ST', '1', 'CalibrationProcedure'],
        '0x4074': ['SH', '1', 'ProcedureVersion'],
        '0x4076': ['DA', '1', 'ProcedureCreationDate'],
        '0x4078': ['DA', '1', 'ProcedureExpirationDate'],
        '0x407A': ['DA', '1', 'ProcedureLastModifiedDate'],
        '0x407C': ['TM', '1-n', 'CalibrationTime'],
        '0x407E': ['DA', '1-n', 'CalibrationDate'],
        '0x4080': ['SQ', '1', 'ProbeDriveEquipmentSequence'],
        '0x4081': ['CS', '1', 'DriveType'],
        '0x4082': ['LT', '1', 'ProbeDriveNotes'],
        '0x4083': ['SQ', '1', 'DriveProbeSequence'],
        '0x4084': ['DS', '1', 'ProbeInductance'],
        '0x4085': ['DS', '1', 'ProbeResistance'],
        '0x4086': ['SQ', '1', 'ReceiveProbeSequence'],
        '0x4087': ['SQ', '1', 'ProbeDriveSettingsSequence'],
        '0x4088': ['DS', '1', 'BridgeResistors'],
        '0x4089': ['DS', '1', 'ProbeOrientationAngle'],
        '0x408B': ['DS', '1', 'UserSelectedGainY'],
        '0x408C': ['DS', '1', 'UserSelectedPhase'],
        '0x408D': ['DS', '1', 'UserSelectedOffsetX'],
        '0x408E': ['DS', '1', 'UserSelectedOffsetY'],
        '0x4091': ['SQ', '1', 'ChannelSettingsSequence'],
        '0x4092': ['DS', '1', 'ChannelThreshold'],
        '0x409A': ['SQ', '1', 'ScannerSettingsSequence'],
        '0x409B': ['ST', '1', 'ScanProcedure'],
        '0x409C': ['DS', '1', 'TranslationRateX'],
        '0x409D': ['DS', '1', 'TranslationRateY'],
        '0x409F': ['DS', '1', 'ChannelOverlap'],
        '0x40A0': ['LO', '1', 'ImageQualityIndicatorType'],
        '0x40A1': ['LO', '1', 'ImageQualityIndicatorMaterial'],
        '0x40A2': ['LO', '1', 'ImageQualityIndicatorSize'],
        '0x5002': ['IS', '1', 'LINACEnergy'],
        '0x5004': ['IS', '1', 'LINACOutput'],
        '0x5100': ['US', '1', 'ActiveAperture'],
        '0x5101': ['DS', '1', 'TotalAperture'],
        '0x5102': ['DS', '1', 'ApertureElevation'],
        '0x5103': ['DS', '1', 'MainLobeAngle'],
        '0x5104': ['DS', '1', 'MainRoofAngle'],
        '0x5105': ['CS', '1', 'ConnectorType'],
        '0x5106': ['SH', '1', 'WedgeModelNumber'],
        '0x5107': ['DS', '1', 'WedgeAngleFloat'],
        '0x5108': ['DS', '1', 'WedgeRoofAngle'],
        '0x5109': ['CS', '1', 'WedgeElement1Position'],
        '0x510A': ['DS', '1', 'WedgeMaterialVelocity'],
        '0x510B': ['SH', '1', 'WedgeMaterial'],
        '0x510C': ['DS', '1', 'WedgeOffsetZ'],
        '0x510D': ['DS', '1', 'WedgeOriginOffsetX'],
        '0x510E': ['DS', '1', 'WedgeTimeDelay'],
        '0x510F': ['SH', '1', 'WedgeName'],
        '0x5110': ['SH', '1', 'WedgeManufacturerName'],
        '0x5111': ['LO', '1', 'WedgeDescription'],
        '0x5112': ['DS', '1', 'NominalBeamAngle'],
        '0x5113': ['DS', '1', 'WedgeOffsetX'],
        '0x5114': ['DS', '1', 'WedgeOffsetY'],
        '0x5115': ['DS', '1', 'WedgeTotalLength'],
        '0x5116': ['DS', '1', 'WedgeInContactLength'],
        '0x5117': ['DS', '1', 'WedgeFrontGap'],
        '0x5118': ['DS', '1', 'WedgeTotalHeight'],
        '0x5119': ['DS', '1', 'WedgeFrontHeight'],
        '0x511A': ['DS', '1', 'WedgeRearHeight'],
        '0x511B': ['DS', '1', 'WedgeTotalWidth'],
        '0x511C': ['DS', '1', 'WedgeInContactWidth'],
        '0x511D': ['DS', '1', 'WedgeChamferHeight'],
        '0x511E': ['CS', '1', 'WedgeCurve'],
        '0x511F': ['DS', '1', 'RadiusAlongWedge']
    },
    '0x0018': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0010': ['LO', '1', 'ContrastBolusAgent'],
        '0x0012': ['SQ', '1', 'ContrastBolusAgentSequence'],
        '0x0013': ['FL', '1', 'ContrastBolusT1Relaxivity'],
        '0x0014': ['SQ', '1', 'ContrastBolusAdministrationRouteSequence'],
        '0x0015': ['CS', '1', 'BodyPartExamined'],
        '0x0020': ['CS', '1-n', 'ScanningSequence'],
        '0x0021': ['CS', '1-n', 'SequenceVariant'],
        '0x0022': ['CS', '1-n', 'ScanOptions'],
        '0x0023': ['CS', '1', 'MRAcquisitionType'],
        '0x0024': ['SH', '1', 'SequenceName'],
        '0x0025': ['CS', '1', 'AngioFlag'],
        '0x0026': ['SQ', '1', 'InterventionDrugInformationSequence'],
        '0x0027': ['TM', '1', 'InterventionDrugStopTime'],
        '0x0028': ['DS', '1', 'InterventionDrugDose'],
        '0x0029': ['SQ', '1', 'InterventionDrugCodeSequence'],
        '0x002A': ['SQ', '1', 'AdditionalDrugSequence'],
        '0x0030': ['LO', '1-n', 'Radionuclide'],
        '0x0031': ['LO', '1', 'Radiopharmaceutical'],
        '0x0032': ['DS', '1', 'EnergyWindowCenterline'],
        '0x0033': ['DS', '1-n', 'EnergyWindowTotalWidth'],
        '0x0034': ['LO', '1', 'InterventionDrugName'],
        '0x0035': ['TM', '1', 'InterventionDrugStartTime'],
        '0x0036': ['SQ', '1', 'InterventionSequence'],
        '0x0037': ['CS', '1', 'TherapyType'],
        '0x0038': ['CS', '1', 'InterventionStatus'],
        '0x0039': ['CS', '1', 'TherapyDescription'],
        '0x003A': ['ST', '1', 'InterventionDescription'],
        '0x0040': ['IS', '1', 'CineRate'],
        '0x0042': ['CS', '1', 'InitialCineRunState'],
        '0x0050': ['DS', '1', 'SliceThickness'],
        '0x0060': ['DS', '1', 'KVP'],
        '0x0070': ['IS', '1', 'CountsAccumulated'],
        '0x0071': ['CS', '1', 'AcquisitionTerminationCondition'],
        '0x0072': ['DS', '1', 'EffectiveDuration'],
        '0x0073': ['CS', '1', 'AcquisitionStartCondition'],
        '0x0074': ['IS', '1', 'AcquisitionStartConditionData'],
        '0x0075': ['IS', '1', 'AcquisitionTerminationConditionData'],
        '0x0080': ['DS', '1', 'RepetitionTime'],
        '0x0081': ['DS', '1', 'EchoTime'],
        '0x0082': ['DS', '1', 'InversionTime'],
        '0x0083': ['DS', '1', 'NumberOfAverages'],
        '0x0084': ['DS', '1', 'ImagingFrequency'],
        '0x0085': ['SH', '1', 'ImagedNucleus'],
        '0x0086': ['IS', '1-n', 'EchoNumbers'],
        '0x0087': ['DS', '1', 'MagneticFieldStrength'],
        '0x0088': ['DS', '1', 'SpacingBetweenSlices'],
        '0x0089': ['IS', '1', 'NumberOfPhaseEncodingSteps'],
        '0x0090': ['DS', '1', 'DataCollectionDiameter'],
        '0x0091': ['IS', '1', 'EchoTrainLength'],
        '0x0093': ['DS', '1', 'PercentSampling'],
        '0x0094': ['DS', '1', 'PercentPhaseFieldOfView'],
        '0x0095': ['DS', '1', 'PixelBandwidth'],
        '0x1000': ['LO', '1', 'DeviceSerialNumber'],
        '0x1002': ['UI', '1', 'DeviceUID'],
        '0x1003': ['LO', '1', 'DeviceID'],
        '0x1004': ['LO', '1', 'PlateID'],
        '0x1005': ['LO', '1', 'GeneratorID'],
        '0x1006': ['LO', '1', 'GridID'],
        '0x1007': ['LO', '1', 'CassetteID'],
        '0x1008': ['LO', '1', 'GantryID'],
        '0x1010': ['LO', '1', 'SecondaryCaptureDeviceID'],
        '0x1011': ['LO', '1', 'HardcopyCreationDeviceID'],
        '0x1012': ['DA', '1', 'DateOfSecondaryCapture'],
        '0x1014': ['TM', '1', 'TimeOfSecondaryCapture'],
        '0x1016': ['LO', '1', 'SecondaryCaptureDeviceManufacturer'],
        '0x1017': ['LO', '1', 'HardcopyDeviceManufacturer'],
        '0x1018': ['LO', '1', 'SecondaryCaptureDeviceManufacturerModelName'],
        '0x1019': ['LO', '1-n', 'SecondaryCaptureDeviceSoftwareVersions'],
        '0x101A': ['LO', '1-n', 'HardcopyDeviceSoftwareVersion'],
        '0x101B': ['LO', '1', 'HardcopyDeviceManufacturerModelName'],
        '0x1020': ['LO', '1-n', 'SoftwareVersions'],
        '0x1022': ['SH', '1', 'VideoImageFormatAcquired'],
        '0x1023': ['LO', '1', 'DigitalImageFormatAcquired'],
        '0x1030': ['LO', '1', 'ProtocolName'],
        '0x1040': ['LO', '1', 'ContrastBolusRoute'],
        '0x1041': ['DS', '1', 'ContrastBolusVolume'],
        '0x1042': ['TM', '1', 'ContrastBolusStartTime'],
        '0x1043': ['TM', '1', 'ContrastBolusStopTime'],
        '0x1044': ['DS', '1', 'ContrastBolusTotalDose'],
        '0x1045': ['IS', '1', 'SyringeCounts'],
        '0x1046': ['DS', '1-n', 'ContrastFlowRate'],
        '0x1047': ['DS', '1-n', 'ContrastFlowDuration'],
        '0x1048': ['CS', '1', 'ContrastBolusIngredient'],
        '0x1049': ['DS', '1', 'ContrastBolusIngredientConcentration'],
        '0x1050': ['DS', '1', 'SpatialResolution'],
        '0x1060': ['DS', '1', 'TriggerTime'],
        '0x1061': ['LO', '1', 'TriggerSourceOrType'],
        '0x1062': ['IS', '1', 'NominalInterval'],
        '0x1063': ['DS', '1', 'FrameTime'],
        '0x1064': ['LO', '1', 'CardiacFramingType'],
        '0x1065': ['DS', '1-n', 'FrameTimeVector'],
        '0x1066': ['DS', '1', 'FrameDelay'],
        '0x1067': ['DS', '1', 'ImageTriggerDelay'],
        '0x1068': ['DS', '1', 'MultiplexGroupTimeOffset'],
        '0x1069': ['DS', '1', 'TriggerTimeOffset'],
        '0x106A': ['CS', '1', 'SynchronizationTrigger'],
        '0x106C': ['US', '2', 'SynchronizationChannel'],
        '0x106E': ['UL', '1', 'TriggerSamplePosition'],
        '0x1070': ['LO', '1', 'RadiopharmaceuticalRoute'],
        '0x1071': ['DS', '1', 'RadiopharmaceuticalVolume'],
        '0x1072': ['TM', '1', 'RadiopharmaceuticalStartTime'],
        '0x1073': ['TM', '1', 'RadiopharmaceuticalStopTime'],
        '0x1074': ['DS', '1', 'RadionuclideTotalDose'],
        '0x1075': ['DS', '1', 'RadionuclideHalfLife'],
        '0x1076': ['DS', '1', 'RadionuclidePositronFraction'],
        '0x1077': ['DS', '1', 'RadiopharmaceuticalSpecificActivity'],
        '0x1078': ['DT', '1', 'RadiopharmaceuticalStartDateTime'],
        '0x1079': ['DT', '1', 'RadiopharmaceuticalStopDateTime'],
        '0x1080': ['CS', '1', 'BeatRejectionFlag'],
        '0x1081': ['IS', '1', 'LowRRValue'],
        '0x1082': ['IS', '1', 'HighRRValue'],
        '0x1083': ['IS', '1', 'IntervalsAcquired'],
        '0x1084': ['IS', '1', 'IntervalsRejected'],
        '0x1085': ['LO', '1', 'PVCRejection'],
        '0x1086': ['IS', '1', 'SkipBeats'],
        '0x1088': ['IS', '1', 'HeartRate'],
        '0x1090': ['IS', '1', 'CardiacNumberOfImages'],
        '0x1094': ['IS', '1', 'TriggerWindow'],
        '0x1100': ['DS', '1', 'ReconstructionDiameter'],
        '0x1110': ['DS', '1', 'DistanceSourceToDetector'],
        '0x1111': ['DS', '1', 'DistanceSourceToPatient'],
        '0x1114': ['DS', '1', 'EstimatedRadiographicMagnificationFactor'],
        '0x1120': ['DS', '1', 'GantryDetectorTilt'],
        '0x1121': ['DS', '1', 'GantryDetectorSlew'],
        '0x1130': ['DS', '1', 'TableHeight'],
        '0x1131': ['DS', '1', 'TableTraverse'],
        '0x1134': ['CS', '1', 'TableMotion'],
        '0x1135': ['DS', '1-n', 'TableVerticalIncrement'],
        '0x1136': ['DS', '1-n', 'TableLateralIncrement'],
        '0x1137': ['DS', '1-n', 'TableLongitudinalIncrement'],
        '0x1138': ['DS', '1', 'TableAngle'],
        '0x113A': ['CS', '1', 'TableType'],
        '0x1140': ['CS', '1', 'RotationDirection'],
        '0x1141': ['DS', '1', 'AngularPosition'],
        '0x1142': ['DS', '1-n', 'RadialPosition'],
        '0x1143': ['DS', '1', 'ScanArc'],
        '0x1144': ['DS', '1', 'AngularStep'],
        '0x1145': ['DS', '1', 'CenterOfRotationOffset'],
        '0x1146': ['DS', '1-n', 'RotationOffset'],
        '0x1147': ['CS', '1', 'FieldOfViewShape'],
        '0x1149': ['IS', '1-2', 'FieldOfViewDimensions'],
        '0x1150': ['IS', '1', 'ExposureTime'],
        '0x1151': ['IS', '1', 'XRayTubeCurrent'],
        '0x1152': ['IS', '1', 'Exposure'],
        '0x1153': ['IS', '1', 'ExposureInuAs'],
        '0x1154': ['DS', '1', 'AveragePulseWidth'],
        '0x1155': ['CS', '1', 'RadiationSetting'],
        '0x1156': ['CS', '1', 'RectificationType'],
        '0x115A': ['CS', '1', 'RadiationMode'],
        '0x115E': ['DS', '1', 'ImageAndFluoroscopyAreaDoseProduct'],
        '0x1160': ['SH', '1', 'FilterType'],
        '0x1161': ['LO', '1-n', 'TypeOfFilters'],
        '0x1162': ['DS', '1', 'IntensifierSize'],
        '0x1164': ['DS', '2', 'ImagerPixelSpacing'],
        '0x1166': ['CS', '1-n', 'Grid'],
        '0x1170': ['IS', '1', 'GeneratorPower'],
        '0x1180': ['SH', '1', 'CollimatorGridName'],
        '0x1181': ['CS', '1', 'CollimatorType'],
        '0x1182': ['IS', '1-2', 'FocalDistance'],
        '0x1183': ['DS', '1-2', 'XFocusCenter'],
        '0x1184': ['DS', '1-2', 'YFocusCenter'],
        '0x1190': ['DS', '1-n', 'FocalSpots'],
        '0x1191': ['CS', '1', 'AnodeTargetMaterial'],
        '0x11A0': ['DS', '1', 'BodyPartThickness'],
        '0x11A2': ['DS', '1', 'CompressionForce'],
        '0x11A4': ['LO', '1', 'PaddleDescription'],
        '0x1200': ['DA', '1-n', 'DateOfLastCalibration'],
        '0x1201': ['TM', '1-n', 'TimeOfLastCalibration'],
        '0x1202': ['DT', '1', 'DateTimeOfLastCalibration'],
        '0x1210': ['SH', '1-n', 'ConvolutionKernel'],
        '0x1240': ['IS', '1-n', 'UpperLowerPixelValues'],
        '0x1242': ['IS', '1', 'ActualFrameDuration'],
        '0x1243': ['IS', '1', 'CountRate'],
        '0x1244': ['US', '1', 'PreferredPlaybackSequencing'],
        '0x1250': ['SH', '1', 'ReceiveCoilName'],
        '0x1251': ['SH', '1', 'TransmitCoilName'],
        '0x1260': ['SH', '1', 'PlateType'],
        '0x1261': ['LO', '1', 'PhosphorType'],
        '0x1300': ['DS', '1', 'ScanVelocity'],
        '0x1301': ['CS', '1-n', 'WholeBodyTechnique'],
        '0x1302': ['IS', '1', 'ScanLength'],
        '0x1310': ['US', '4', 'AcquisitionMatrix'],
        '0x1312': ['CS', '1', 'InPlanePhaseEncodingDirection'],
        '0x1314': ['DS', '1', 'FlipAngle'],
        '0x1315': ['CS', '1', 'VariableFlipAngleFlag'],
        '0x1316': ['DS', '1', 'SAR'],
        '0x1318': ['DS', '1', 'dBdt'],
        '0x1400': ['LO', '1', 'AcquisitionDeviceProcessingDescription'],
        '0x1401': ['LO', '1', 'AcquisitionDeviceProcessingCode'],
        '0x1402': ['CS', '1', 'CassetteOrientation'],
        '0x1403': ['CS', '1', 'CassetteSize'],
        '0x1404': ['US', '1', 'ExposuresOnPlate'],
        '0x1405': ['IS', '1', 'RelativeXRayExposure'],
        '0x1411': ['DS', '1', 'ExposureIndex'],
        '0x1412': ['DS', '1', 'TargetExposureIndex'],
        '0x1413': ['DS', '1', 'DeviationIndex'],
        '0x1450': ['DS', '1', 'ColumnAngulation'],
        '0x1460': ['DS', '1', 'TomoLayerHeight'],
        '0x1470': ['DS', '1', 'TomoAngle'],
        '0x1480': ['DS', '1', 'TomoTime'],
        '0x1490': ['CS', '1', 'TomoType'],
        '0x1491': ['CS', '1', 'TomoClass'],
        '0x1495': ['IS', '1', 'NumberOfTomosynthesisSourceImages'],
        '0x1500': ['CS', '1', 'PositionerMotion'],
        '0x1508': ['CS', '1', 'PositionerType'],
        '0x1510': ['DS', '1', 'PositionerPrimaryAngle'],
        '0x1511': ['DS', '1', 'PositionerSecondaryAngle'],
        '0x1520': ['DS', '1-n', 'PositionerPrimaryAngleIncrement'],
        '0x1521': ['DS', '1-n', 'PositionerSecondaryAngleIncrement'],
        '0x1530': ['DS', '1', 'DetectorPrimaryAngle'],
        '0x1531': ['DS', '1', 'DetectorSecondaryAngle'],
        '0x1600': ['CS', '1-3', 'ShutterShape'],
        '0x1602': ['IS', '1', 'ShutterLeftVerticalEdge'],
        '0x1604': ['IS', '1', 'ShutterRightVerticalEdge'],
        '0x1606': ['IS', '1', 'ShutterUpperHorizontalEdge'],
        '0x1608': ['IS', '1', 'ShutterLowerHorizontalEdge'],
        '0x1610': ['IS', '2', 'CenterOfCircularShutter'],
        '0x1612': ['IS', '1', 'RadiusOfCircularShutter'],
        '0x1620': ['IS', '2-2n', 'VerticesOfThePolygonalShutter'],
        '0x1622': ['US', '1', 'ShutterPresentationValue'],
        '0x1623': ['US', '1', 'ShutterOverlayGroup'],
        '0x1624': ['US', '3', 'ShutterPresentationColorCIELabValue'],
        '0x1700': ['CS', '1-3', 'CollimatorShape'],
        '0x1702': ['IS', '1', 'CollimatorLeftVerticalEdge'],
        '0x1704': ['IS', '1', 'CollimatorRightVerticalEdge'],
        '0x1706': ['IS', '1', 'CollimatorUpperHorizontalEdge'],
        '0x1708': ['IS', '1', 'CollimatorLowerHorizontalEdge'],
        '0x1710': ['IS', '2', 'CenterOfCircularCollimator'],
        '0x1712': ['IS', '1', 'RadiusOfCircularCollimator'],
        '0x1720': ['IS', '2-2n', 'VerticesOfThePolygonalCollimator'],
        '0x1800': ['CS', '1', 'AcquisitionTimeSynchronized'],
        '0x1801': ['SH', '1', 'TimeSource'],
        '0x1802': ['CS', '1', 'TimeDistributionProtocol'],
        '0x1803': ['LO', '1', 'NTPSourceAddress'],
        '0x2001': ['IS', '1-n', 'PageNumberVector'],
        '0x2002': ['SH', '1-n', 'FrameLabelVector'],
        '0x2003': ['DS', '1-n', 'FramePrimaryAngleVector'],
        '0x2004': ['DS', '1-n', 'FrameSecondaryAngleVector'],
        '0x2005': ['DS', '1-n', 'SliceLocationVector'],
        '0x2006': ['SH', '1-n', 'DisplayWindowLabelVector'],
        '0x2010': ['DS', '2', 'NominalScannedPixelSpacing'],
        '0x2020': ['CS', '1', 'DigitizingDeviceTransportDirection'],
        '0x2030': ['DS', '1', 'RotationOfScannedFilm'],
        '0x2041': ['SQ', '1', 'BiopsyTargetSequence'],
        '0x2042': ['UI', '1', 'TargetUID'],
        '0x2043': ['FL', '2', 'LocalizingCursorPosition'],
        '0x2044': ['FL', '3', 'CalculatedTargetPosition'],
        '0x2045': ['SH', '1', 'TargetLabel'],
        '0x2046': ['FL', '1', 'DisplayedZValue'],
        '0x3100': ['CS', '1', 'IVUSAcquisition'],
        '0x3101': ['DS', '1', 'IVUSPullbackRate'],
        '0x3102': ['DS', '1', 'IVUSGatedRate'],
        '0x3103': ['IS', '1', 'IVUSPullbackStartFrameNumber'],
        '0x3104': ['IS', '1', 'IVUSPullbackStopFrameNumber'],
        '0x3105': ['IS', '1-n', 'LesionNumber'],
        '0x4000': ['LT', '1', 'AcquisitionComments'],
        '0x5000': ['SH', '1-n', 'OutputPower'],
        '0x5010': ['LO', '1-n', 'TransducerData'],
        '0x5012': ['DS', '1', 'FocusDepth'],
        '0x5020': ['LO', '1', 'ProcessingFunction'],
        '0x5021': ['LO', '1', 'PostprocessingFunction'],
        '0x5022': ['DS', '1', 'MechanicalIndex'],
        '0x5024': ['DS', '1', 'BoneThermalIndex'],
        '0x5026': ['DS', '1', 'CranialThermalIndex'],
        '0x5027': ['DS', '1', 'SoftTissueThermalIndex'],
        '0x5028': ['DS', '1', 'SoftTissueFocusThermalIndex'],
        '0x5029': ['DS', '1', 'SoftTissueSurfaceThermalIndex'],
        '0x5030': ['DS', '1', 'DynamicRange'],
        '0x5040': ['DS', '1', 'TotalGain'],
        '0x5050': ['IS', '1', 'DepthOfScanField'],
        '0x5100': ['CS', '1', 'PatientPosition'],
        '0x5101': ['CS', '1', 'ViewPosition'],
        '0x5104': ['SQ', '1', 'ProjectionEponymousNameCodeSequence'],
        '0x5210': ['DS', '6', 'ImageTransformationMatrix'],
        '0x5212': ['DS', '3', 'ImageTranslationVector'],
        '0x6000': ['DS', '1', 'Sensitivity'],
        '0x6011': ['SQ', '1', 'SequenceOfUltrasoundRegions'],
        '0x6012': ['US', '1', 'RegionSpatialFormat'],
        '0x6014': ['US', '1', 'RegionDataType'],
        '0x6016': ['UL', '1', 'RegionFlags'],
        '0x6018': ['UL', '1', 'RegionLocationMinX0'],
        '0x601A': ['UL', '1', 'RegionLocationMinY0'],
        '0x601C': ['UL', '1', 'RegionLocationMaxX1'],
        '0x601E': ['UL', '1', 'RegionLocationMaxY1'],
        '0x6020': ['SL', '1', 'ReferencePixelX0'],
        '0x6022': ['SL', '1', 'ReferencePixelY0'],
        '0x6024': ['US', '1', 'PhysicalUnitsXDirection'],
        '0x6026': ['US', '1', 'PhysicalUnitsYDirection'],
        '0x6028': ['FD', '1', 'ReferencePixelPhysicalValueX'],
        '0x602A': ['FD', '1', 'ReferencePixelPhysicalValueY'],
        '0x602C': ['FD', '1', 'PhysicalDeltaX'],
        '0x602E': ['FD', '1', 'PhysicalDeltaY'],
        '0x6030': ['UL', '1', 'TransducerFrequency'],
        '0x6031': ['CS', '1', 'TransducerType'],
        '0x6032': ['UL', '1', 'PulseRepetitionFrequency'],
        '0x6034': ['FD', '1', 'DopplerCorrectionAngle'],
        '0x6036': ['FD', '1', 'SteeringAngle'],
        '0x6038': ['UL', '1', 'DopplerSampleVolumeXPositionRetired'],
        '0x6039': ['SL', '1', 'DopplerSampleVolumeXPosition'],
        '0x603A': ['UL', '1', 'DopplerSampleVolumeYPositionRetired'],
        '0x603B': ['SL', '1', 'DopplerSampleVolumeYPosition'],
        '0x603C': ['UL', '1', 'TMLinePositionX0Retired'],
        '0x603D': ['SL', '1', 'TMLinePositionX0'],
        '0x603E': ['UL', '1', 'TMLinePositionY0Retired'],
        '0x603F': ['SL', '1', 'TMLinePositionY0'],
        '0x6040': ['UL', '1', 'TMLinePositionX1Retired'],
        '0x6041': ['SL', '1', 'TMLinePositionX1'],
        '0x6042': ['UL', '1', 'TMLinePositionY1Retired'],
        '0x6043': ['SL', '1', 'TMLinePositionY1'],
        '0x6044': ['US', '1', 'PixelComponentOrganization'],
        '0x6046': ['UL', '1', 'PixelComponentMask'],
        '0x6048': ['UL', '1', 'PixelComponentRangeStart'],
        '0x604A': ['UL', '1', 'PixelComponentRangeStop'],
        '0x604C': ['US', '1', 'PixelComponentPhysicalUnits'],
        '0x604E': ['US', '1', 'PixelComponentDataType'],
        '0x6050': ['UL', '1', 'NumberOfTableBreakPoints'],
        '0x6052': ['UL', '1-n', 'TableOfXBreakPoints'],
        '0x6054': ['FD', '1-n', 'TableOfYBreakPoints'],
        '0x6056': ['UL', '1', 'NumberOfTableEntries'],
        '0x6058': ['UL', '1-n', 'TableOfPixelValues'],
        '0x605A': ['FL', '1-n', 'TableOfParameterValues'],
        '0x6060': ['FL', '1-n', 'RWaveTimeVector'],
        '0x7000': ['CS', '1', 'DetectorConditionsNominalFlag'],
        '0x7001': ['DS', '1', 'DetectorTemperature'],
        '0x7004': ['CS', '1', 'DetectorType'],
        '0x7005': ['CS', '1', 'DetectorConfiguration'],
        '0x7006': ['LT', '1', 'DetectorDescription'],
        '0x7008': ['LT', '1', 'DetectorMode'],
        '0x700A': ['SH', '1', 'DetectorID'],
        '0x700C': ['DA', '1', 'DateOfLastDetectorCalibration'],
        '0x700E': ['TM', '1', 'TimeOfLastDetectorCalibration'],
        '0x7010': ['IS', '1', 'ExposuresOnDetectorSinceLastCalibration'],
        '0x7011': ['IS', '1', 'ExposuresOnDetectorSinceManufactured'],
        '0x7012': ['DS', '1', 'DetectorTimeSinceLastExposure'],
        '0x7014': ['DS', '1', 'DetectorActiveTime'],
        '0x7016': ['DS', '1', 'DetectorActivationOffsetFromExposure'],
        '0x701A': ['DS', '2', 'DetectorBinning'],
        '0x7020': ['DS', '2', 'DetectorElementPhysicalSize'],
        '0x7022': ['DS', '2', 'DetectorElementSpacing'],
        '0x7024': ['CS', '1', 'DetectorActiveShape'],
        '0x7026': ['DS', '1-2', 'DetectorActiveDimensions'],
        '0x7028': ['DS', '2', 'DetectorActiveOrigin'],
        '0x702A': ['LO', '1', 'DetectorManufacturerName'],
        '0x702B': ['LO', '1', 'DetectorManufacturerModelName'],
        '0x7030': ['DS', '2', 'FieldOfViewOrigin'],
        '0x7032': ['DS', '1', 'FieldOfViewRotation'],
        '0x7034': ['CS', '1', 'FieldOfViewHorizontalFlip'],
        '0x7036': ['FL', '2', 'PixelDataAreaOriginRelativeToFOV'],
        '0x7038': ['FL', '1', 'PixelDataAreaRotationAngleRelativeToFOV'],
        '0x7040': ['LT', '1', 'GridAbsorbingMaterial'],
        '0x7041': ['LT', '1', 'GridSpacingMaterial'],
        '0x7042': ['DS', '1', 'GridThickness'],
        '0x7044': ['DS', '1', 'GridPitch'],
        '0x7046': ['IS', '2', 'GridAspectRatio'],
        '0x7048': ['DS', '1', 'GridPeriod'],
        '0x704C': ['DS', '1', 'GridFocalDistance'],
        '0x7050': ['CS', '1-n', 'FilterMaterial'],
        '0x7052': ['DS', '1-n', 'FilterThicknessMinimum'],
        '0x7054': ['DS', '1-n', 'FilterThicknessMaximum'],
        '0x7056': ['FL', '1-n', 'FilterBeamPathLengthMinimum'],
        '0x7058': ['FL', '1-n', 'FilterBeamPathLengthMaximum'],
        '0x7060': ['CS', '1', 'ExposureControlMode'],
        '0x7062': ['LT', '1', 'ExposureControlModeDescription'],
        '0x7064': ['CS', '1', 'ExposureStatus'],
        '0x7065': ['DS', '1', 'PhototimerSetting'],
        '0x8150': ['DS', '1', 'ExposureTimeInuS'],
        '0x8151': ['DS', '1', 'XRayTubeCurrentInuA'],
        '0x9004': ['CS', '1', 'ContentQualification'],
        '0x9005': ['SH', '1', 'PulseSequenceName'],
        '0x9006': ['SQ', '1', 'MRImagingModifierSequence'],
        '0x9008': ['CS', '1', 'EchoPulseSequence'],
        '0x9009': ['CS', '1', 'InversionRecovery'],
        '0x9010': ['CS', '1', 'FlowCompensation'],
        '0x9011': ['CS', '1', 'MultipleSpinEcho'],
        '0x9012': ['CS', '1', 'MultiPlanarExcitation'],
        '0x9014': ['CS', '1', 'PhaseContrast'],
        '0x9015': ['CS', '1', 'TimeOfFlightContrast'],
        '0x9016': ['CS', '1', 'Spoiling'],
        '0x9017': ['CS', '1', 'SteadyStatePulseSequence'],
        '0x9018': ['CS', '1', 'EchoPlanarPulseSequence'],
        '0x9019': ['FD', '1', 'TagAngleFirstAxis'],
        '0x9020': ['CS', '1', 'MagnetizationTransfer'],
        '0x9021': ['CS', '1', 'T2Preparation'],
        '0x9022': ['CS', '1', 'BloodSignalNulling'],
        '0x9024': ['CS', '1', 'SaturationRecovery'],
        '0x9025': ['CS', '1', 'SpectrallySelectedSuppression'],
        '0x9026': ['CS', '1', 'SpectrallySelectedExcitation'],
        '0x9027': ['CS', '1', 'SpatialPresaturation'],
        '0x9028': ['CS', '1', 'Tagging'],
        '0x9029': ['CS', '1', 'OversamplingPhase'],
        '0x9030': ['FD', '1', 'TagSpacingFirstDimension'],
        '0x9032': ['CS', '1', 'GeometryOfKSpaceTraversal'],
        '0x9033': ['CS', '1', 'SegmentedKSpaceTraversal'],
        '0x9034': ['CS', '1', 'RectilinearPhaseEncodeReordering'],
        '0x9035': ['FD', '1', 'TagThickness'],
        '0x9036': ['CS', '1', 'PartialFourierDirection'],
        '0x9037': ['CS', '1', 'CardiacSynchronizationTechnique'],
        '0x9041': ['LO', '1', 'ReceiveCoilManufacturerName'],
        '0x9042': ['SQ', '1', 'MRReceiveCoilSequence'],
        '0x9043': ['CS', '1', 'ReceiveCoilType'],
        '0x9044': ['CS', '1', 'QuadratureReceiveCoil'],
        '0x9045': ['SQ', '1', 'MultiCoilDefinitionSequence'],
        '0x9046': ['LO', '1', 'MultiCoilConfiguration'],
        '0x9047': ['SH', '1', 'MultiCoilElementName'],
        '0x9048': ['CS', '1', 'MultiCoilElementUsed'],
        '0x9049': ['SQ', '1', 'MRTransmitCoilSequence'],
        '0x9050': ['LO', '1', 'TransmitCoilManufacturerName'],
        '0x9051': ['CS', '1', 'TransmitCoilType'],
        '0x9052': ['FD', '1-2', 'SpectralWidth'],
        '0x9053': ['FD', '1-2', 'ChemicalShiftReference'],
        '0x9054': ['CS', '1', 'VolumeLocalizationTechnique'],
        '0x9058': ['US', '1', 'MRAcquisitionFrequencyEncodingSteps'],
        '0x9059': ['CS', '1', 'Decoupling'],
        '0x9060': ['CS', '1-2', 'DecoupledNucleus'],
        '0x9061': ['FD', '1-2', 'DecouplingFrequency'],
        '0x9062': ['CS', '1', 'DecouplingMethod'],
        '0x9063': ['FD', '1-2', 'DecouplingChemicalShiftReference'],
        '0x9064': ['CS', '1', 'KSpaceFiltering'],
        '0x9065': ['CS', '1-2', 'TimeDomainFiltering'],
        '0x9066': ['US', '1-2', 'NumberOfZeroFills'],
        '0x9067': ['CS', '1', 'BaselineCorrection'],
        '0x9069': ['FD', '1', 'ParallelReductionFactorInPlane'],
        '0x9070': ['FD', '1', 'CardiacRRIntervalSpecified'],
        '0x9073': ['FD', '1', 'AcquisitionDuration'],
        '0x9074': ['DT', '1', 'FrameAcquisitionDateTime'],
        '0x9075': ['CS', '1', 'DiffusionDirectionality'],
        '0x9076': ['SQ', '1', 'DiffusionGradientDirectionSequence'],
        '0x9077': ['CS', '1', 'ParallelAcquisition'],
        '0x9078': ['CS', '1', 'ParallelAcquisitionTechnique'],
        '0x9079': ['FD', '1-n', 'InversionTimes'],
        '0x9080': ['ST', '1', 'MetaboliteMapDescription'],
        '0x9081': ['CS', '1', 'PartialFourier'],
        '0x9082': ['FD', '1', 'EffectiveEchoTime'],
        '0x9083': ['SQ', '1', 'MetaboliteMapCodeSequence'],
        '0x9084': ['SQ', '1', 'ChemicalShiftSequence'],
        '0x9085': ['CS', '1', 'CardiacSignalSource'],
        '0x9087': ['FD', '1', 'DiffusionBValue'],
        '0x9089': ['FD', '3', 'DiffusionGradientOrientation'],
        '0x9090': ['FD', '3', 'VelocityEncodingDirection'],
        '0x9091': ['FD', '1', 'VelocityEncodingMinimumValue'],
        '0x9092': ['SQ', '1', 'VelocityEncodingAcquisitionSequence'],
        '0x9093': ['US', '1', 'NumberOfKSpaceTrajectories'],
        '0x9094': ['CS', '1', 'CoverageOfKSpace'],
        '0x9095': ['UL', '1', 'SpectroscopyAcquisitionPhaseRows'],
        '0x9096': ['FD', '1', 'ParallelReductionFactorInPlaneRetired'],
        '0x9098': ['FD', '1-2', 'TransmitterFrequency'],
        '0x9100': ['CS', '1-2', 'ResonantNucleus'],
        '0x9101': ['CS', '1', 'FrequencyCorrection'],
        '0x9103': ['SQ', '1', 'MRSpectroscopyFOVGeometrySequence'],
        '0x9104': ['FD', '1', 'SlabThickness'],
        '0x9105': ['FD', '3', 'SlabOrientation'],
        '0x9106': ['FD', '3', 'MidSlabPosition'],
        '0x9107': ['SQ', '1', 'MRSpatialSaturationSequence'],
        '0x9112': ['SQ', '1', 'MRTimingAndRelatedParametersSequence'],
        '0x9114': ['SQ', '1', 'MREchoSequence'],
        '0x9115': ['SQ', '1', 'MRModifierSequence'],
        '0x9117': ['SQ', '1', 'MRDiffusionSequence'],
        '0x9118': ['SQ', '1', 'CardiacSynchronizationSequence'],
        '0x9119': ['SQ', '1', 'MRAveragesSequence'],
        '0x9125': ['SQ', '1', 'MRFOVGeometrySequence'],
        '0x9126': ['SQ', '1', 'VolumeLocalizationSequence'],
        '0x9127': ['UL', '1', 'SpectroscopyAcquisitionDataColumns'],
        '0x9147': ['CS', '1', 'DiffusionAnisotropyType'],
        '0x9151': ['DT', '1', 'FrameReferenceDateTime'],
        '0x9152': ['SQ', '1', 'MRMetaboliteMapSequence'],
        '0x9155': ['FD', '1', 'ParallelReductionFactorOutOfPlane'],
        '0x9159': ['UL', '1', 'SpectroscopyAcquisitionOutOfPlanePhaseSteps'],
        '0x9166': ['CS', '1', 'BulkMotionStatus'],
        '0x9168': ['FD', '1', 'ParallelReductionFactorSecondInPlane'],
        '0x9169': ['CS', '1', 'CardiacBeatRejectionTechnique'],
        '0x9170': ['CS', '1', 'RespiratoryMotionCompensationTechnique'],
        '0x9171': ['CS', '1', 'RespiratorySignalSource'],
        '0x9172': ['CS', '1', 'BulkMotionCompensationTechnique'],
        '0x9173': ['CS', '1', 'BulkMotionSignalSource'],
        '0x9174': ['CS', '1', 'ApplicableSafetyStandardAgency'],
        '0x9175': ['LO', '1', 'ApplicableSafetyStandardDescription'],
        '0x9176': ['SQ', '1', 'OperatingModeSequence'],
        '0x9177': ['CS', '1', 'OperatingModeType'],
        '0x9178': ['CS', '1', 'OperatingMode'],
        '0x9179': ['CS', '1', 'SpecificAbsorptionRateDefinition'],
        '0x9180': ['CS', '1', 'GradientOutputType'],
        '0x9181': ['FD', '1', 'SpecificAbsorptionRateValue'],
        '0x9182': ['FD', '1', 'GradientOutput'],
        '0x9183': ['CS', '1', 'FlowCompensationDirection'],
        '0x9184': ['FD', '1', 'TaggingDelay'],
        '0x9185': ['ST', '1', 'RespiratoryMotionCompensationTechniqueDescription'],
        '0x9186': ['SH', '1', 'RespiratorySignalSourceID'],
        '0x9195': ['FD', '1', 'ChemicalShiftMinimumIntegrationLimitInHz'],
        '0x9196': ['FD', '1', 'ChemicalShiftMaximumIntegrationLimitInHz'],
        '0x9197': ['SQ', '1', 'MRVelocityEncodingSequence'],
        '0x9198': ['CS', '1', 'FirstOrderPhaseCorrection'],
        '0x9199': ['CS', '1', 'WaterReferencedPhaseCorrection'],
        '0x9200': ['CS', '1', 'MRSpectroscopyAcquisitionType'],
        '0x9214': ['CS', '1', 'RespiratoryCyclePosition'],
        '0x9217': ['FD', '1', 'VelocityEncodingMaximumValue'],
        '0x9218': ['FD', '1', 'TagSpacingSecondDimension'],
        '0x9219': ['SS', '1', 'TagAngleSecondAxis'],
        '0x9220': ['FD', '1', 'FrameAcquisitionDuration'],
        '0x9226': ['SQ', '1', 'MRImageFrameTypeSequence'],
        '0x9227': ['SQ', '1', 'MRSpectroscopyFrameTypeSequence'],
        '0x9231': ['US', '1', 'MRAcquisitionPhaseEncodingStepsInPlane'],
        '0x9232': ['US', '1', 'MRAcquisitionPhaseEncodingStepsOutOfPlane'],
        '0x9234': ['UL', '1', 'SpectroscopyAcquisitionPhaseColumns'],
        '0x9236': ['CS', '1', 'CardiacCyclePosition'],
        '0x9239': ['SQ', '1', 'SpecificAbsorptionRateSequence'],
        '0x9240': ['US', '1', 'RFEchoTrainLength'],
        '0x9241': ['US', '1', 'GradientEchoTrainLength'],
        '0x9250': ['CS', '1', 'ArterialSpinLabelingContrast'],
        '0x9251': ['SQ', '1', 'MRArterialSpinLabelingSequence'],
        '0x9252': ['LO', '1', 'ASLTechniqueDescription'],
        '0x9253': ['US', '1', 'ASLSlabNumber'],
        '0x9254': ['FD', '1', 'ASLSlabThickness'],
        '0x9255': ['FD', '3', 'ASLSlabOrientation'],
        '0x9256': ['FD', '3', 'ASLMidSlabPosition'],
        '0x9257': ['CS', '1', 'ASLContext'],
        '0x9258': ['UL', '1', 'ASLPulseTrainDuration'],
        '0x9259': ['CS', '1', 'ASLCrusherFlag'],
        '0x925A': ['FD', '1', 'ASLCrusherFlowLimit'],
        '0x925B': ['LO', '1', 'ASLCrusherDescription'],
        '0x925C': ['CS', '1', 'ASLBolusCutoffFlag'],
        '0x925D': ['SQ', '1', 'ASLBolusCutoffTimingSequence'],
        '0x925E': ['LO', '1', 'ASLBolusCutoffTechnique'],
        '0x925F': ['UL', '1', 'ASLBolusCutoffDelayTime'],
        '0x9260': ['SQ', '1', 'ASLSlabSequence'],
        '0x9295': ['FD', '1', 'ChemicalShiftMinimumIntegrationLimitInppm'],
        '0x9296': ['FD', '1', 'ChemicalShiftMaximumIntegrationLimitInppm'],
        '0x9297': ['CS', '1', 'WaterReferenceAcquisition'],
        '0x9298': ['IS', '1', 'EchoPeakPosition'],
        '0x9301': ['SQ', '1', 'CTAcquisitionTypeSequence'],
        '0x9302': ['CS', '1', 'AcquisitionType'],
        '0x9303': ['FD', '1', 'TubeAngle'],
        '0x9304': ['SQ', '1', 'CTAcquisitionDetailsSequence'],
        '0x9305': ['FD', '1', 'RevolutionTime'],
        '0x9306': ['FD', '1', 'SingleCollimationWidth'],
        '0x9307': ['FD', '1', 'TotalCollimationWidth'],
        '0x9308': ['SQ', '1', 'CTTableDynamicsSequence'],
        '0x9309': ['FD', '1', 'TableSpeed'],
        '0x9310': ['FD', '1', 'TableFeedPerRotation'],
        '0x9311': ['FD', '1', 'SpiralPitchFactor'],
        '0x9312': ['SQ', '1', 'CTGeometrySequence'],
        '0x9313': ['FD', '3', 'DataCollectionCenterPatient'],
        '0x9314': ['SQ', '1', 'CTReconstructionSequence'],
        '0x9315': ['CS', '1', 'ReconstructionAlgorithm'],
        '0x9316': ['CS', '1', 'ConvolutionKernelGroup'],
        '0x9317': ['FD', '2', 'ReconstructionFieldOfView'],
        '0x9318': ['FD', '3', 'ReconstructionTargetCenterPatient'],
        '0x9319': ['FD', '1', 'ReconstructionAngle'],
        '0x9320': ['SH', '1', 'ImageFilter'],
        '0x9321': ['SQ', '1', 'CTExposureSequence'],
        '0x9322': ['FD', '2', 'ReconstructionPixelSpacing'],
        '0x9323': ['CS', '1', 'ExposureModulationType'],
        '0x9324': ['FD', '1', 'EstimatedDoseSaving'],
        '0x9325': ['SQ', '1', 'CTXRayDetailsSequence'],
        '0x9326': ['SQ', '1', 'CTPositionSequence'],
        '0x9327': ['FD', '1', 'TablePosition'],
        '0x9328': ['FD', '1', 'ExposureTimeInms'],
        '0x9329': ['SQ', '1', 'CTImageFrameTypeSequence'],
        '0x9330': ['FD', '1', 'XRayTubeCurrentInmA'],
        '0x9332': ['FD', '1', 'ExposureInmAs'],
        '0x9333': ['CS', '1', 'ConstantVolumeFlag'],
        '0x9334': ['CS', '1', 'FluoroscopyFlag'],
        '0x9335': ['FD', '1', 'DistanceSourceToDataCollectionCenter'],
        '0x9337': ['US', '1', 'ContrastBolusAgentNumber'],
        '0x9338': ['SQ', '1', 'ContrastBolusIngredientCodeSequence'],
        '0x9340': ['SQ', '1', 'ContrastAdministrationProfileSequence'],
        '0x9341': ['SQ', '1', 'ContrastBolusUsageSequence'],
        '0x9342': ['CS', '1', 'ContrastBolusAgentAdministered'],
        '0x9343': ['CS', '1', 'ContrastBolusAgentDetected'],
        '0x9344': ['CS', '1', 'ContrastBolusAgentPhase'],
        '0x9345': ['FD', '1', 'CTDIvol'],
        '0x9346': ['SQ', '1', 'CTDIPhantomTypeCodeSequence'],
        '0x9351': ['FL', '1', 'CalciumScoringMassFactorPatient'],
        '0x9352': ['FL', '3', 'CalciumScoringMassFactorDevice'],
        '0x9353': ['FL', '1', 'EnergyWeightingFactor'],
        '0x9360': ['SQ', '1', 'CTAdditionalXRaySourceSequence'],
        '0x9401': ['SQ', '1', 'ProjectionPixelCalibrationSequence'],
        '0x9402': ['FL', '1', 'DistanceSourceToIsocenter'],
        '0x9403': ['FL', '1', 'DistanceObjectToTableTop'],
        '0x9404': ['FL', '2', 'ObjectPixelSpacingInCenterOfBeam'],
        '0x9405': ['SQ', '1', 'PositionerPositionSequence'],
        '0x9406': ['SQ', '1', 'TablePositionSequence'],
        '0x9407': ['SQ', '1', 'CollimatorShapeSequence'],
        '0x9410': ['CS', '1', 'PlanesInAcquisition'],
        '0x9412': ['SQ', '1', 'XAXRFFrameCharacteristicsSequence'],
        '0x9417': ['SQ', '1', 'FrameAcquisitionSequence'],
        '0x9420': ['CS', '1', 'XRayReceptorType'],
        '0x9423': ['LO', '1', 'AcquisitionProtocolName'],
        '0x9424': ['LT', '1', 'AcquisitionProtocolDescription'],
        '0x9425': ['CS', '1', 'ContrastBolusIngredientOpaque'],
        '0x9426': ['FL', '1', 'DistanceReceptorPlaneToDetectorHousing'],
        '0x9427': ['CS', '1', 'IntensifierActiveShape'],
        '0x9428': ['FL', '1-2', 'IntensifierActiveDimensions'],
        '0x9429': ['FL', '2', 'PhysicalDetectorSize'],
        '0x9430': ['FL', '2', 'PositionOfIsocenterProjection'],
        '0x9432': ['SQ', '1', 'FieldOfViewSequence'],
        '0x9433': ['LO', '1', 'FieldOfViewDescription'],
        '0x9434': ['SQ', '1', 'ExposureControlSensingRegionsSequence'],
        '0x9435': ['CS', '1', 'ExposureControlSensingRegionShape'],
        '0x9436': ['SS', '1', 'ExposureControlSensingRegionLeftVerticalEdge'],
        '0x9437': ['SS', '1', 'ExposureControlSensingRegionRightVerticalEdge'],
        '0x9438': ['SS', '1', 'ExposureControlSensingRegionUpperHorizontalEdge'],
        '0x9439': ['SS', '1', 'ExposureControlSensingRegionLowerHorizontalEdge'],
        '0x9440': ['SS', '2', 'CenterOfCircularExposureControlSensingRegion'],
        '0x9441': ['US', '1', 'RadiusOfCircularExposureControlSensingRegion'],
        '0x9442': ['SS', '2-n', 'VerticesOfThePolygonalExposureControlSensingRegion'],
        '0x9445': ['', '', ''],
        '0x9447': ['FL', '1', 'ColumnAngulationPatient'],
        '0x9449': ['FL', '1', 'BeamAngle'],
        '0x9451': ['SQ', '1', 'FrameDetectorParametersSequence'],
        '0x9452': ['FL', '1', 'CalculatedAnatomyThickness'],
        '0x9455': ['SQ', '1', 'CalibrationSequence'],
        '0x9456': ['SQ', '1', 'ObjectThicknessSequence'],
        '0x9457': ['CS', '1', 'PlaneIdentification'],
        '0x9461': ['FL', '1-2', 'FieldOfViewDimensionsInFloat'],
        '0x9462': ['SQ', '1', 'IsocenterReferenceSystemSequence'],
        '0x9463': ['FL', '1', 'PositionerIsocenterPrimaryAngle'],
        '0x9464': ['FL', '1', 'PositionerIsocenterSecondaryAngle'],
        '0x9465': ['FL', '1', 'PositionerIsocenterDetectorRotationAngle'],
        '0x9466': ['FL', '1', 'TableXPositionToIsocenter'],
        '0x9467': ['FL', '1', 'TableYPositionToIsocenter'],
        '0x9468': ['FL', '1', 'TableZPositionToIsocenter'],
        '0x9469': ['FL', '1', 'TableHorizontalRotationAngle'],
        '0x9470': ['FL', '1', 'TableHeadTiltAngle'],
        '0x9471': ['FL', '1', 'TableCradleTiltAngle'],
        '0x9472': ['SQ', '1', 'FrameDisplayShutterSequence'],
        '0x9473': ['FL', '1', 'AcquiredImageAreaDoseProduct'],
        '0x9474': ['CS', '1', 'CArmPositionerTabletopRelationship'],
        '0x9476': ['SQ', '1', 'XRayGeometrySequence'],
        '0x9477': ['SQ', '1', 'IrradiationEventIdentificationSequence'],
        '0x9504': ['SQ', '1', 'XRay3DFrameTypeSequence'],
        '0x9506': ['SQ', '1', 'ContributingSourcesSequence'],
        '0x9507': ['SQ', '1', 'XRay3DAcquisitionSequence'],
        '0x9508': ['FL', '1', 'PrimaryPositionerScanArc'],
        '0x9509': ['FL', '1', 'SecondaryPositionerScanArc'],
        '0x9510': ['FL', '1', 'PrimaryPositionerScanStartAngle'],
        '0x9511': ['FL', '1', 'SecondaryPositionerScanStartAngle'],
        '0x9514': ['FL', '1', 'PrimaryPositionerIncrement'],
        '0x9515': ['FL', '1', 'SecondaryPositionerIncrement'],
        '0x9516': ['DT', '1', 'StartAcquisitionDateTime'],
        '0x9517': ['DT', '1', 'EndAcquisitionDateTime'],
        '0x9518': ['SS', '1', 'PrimaryPositionerIncrementSign'],
        '0x9519': ['SS', '1', 'SecondaryPositionerIncrementSign'],
        '0x9524': ['LO', '1', 'ApplicationName'],
        '0x9525': ['LO', '1', 'ApplicationVersion'],
        '0x9526': ['LO', '1', 'ApplicationManufacturer'],
        '0x9527': ['CS', '1', 'AlgorithmType'],
        '0x9528': ['LO', '1', 'AlgorithmDescription'],
        '0x9530': ['SQ', '1', 'XRay3DReconstructionSequence'],
        '0x9531': ['LO', '1', 'ReconstructionDescription'],
        '0x9538': ['SQ', '1', 'PerProjectionAcquisitionSequence'],
        '0x9541': ['SQ', '1', 'DetectorPositionSequence'],
        '0x9542': ['SQ', '1', 'XRayAcquisitionDoseSequence'],
        '0x9543': ['FD', '1', 'XRaySourceIsocenterPrimaryAngle'],
        '0x9544': ['FD', '1', 'XRaySourceIsocenterSecondaryAngle'],
        '0x9545': ['FD', '1', 'BreastSupportIsocenterPrimaryAngle'],
        '0x9546': ['FD', '1', 'BreastSupportIsocenterSecondaryAngle'],
        '0x9547': ['FD', '1', 'BreastSupportXPositionToIsocenter'],
        '0x9548': ['FD', '1', 'BreastSupportYPositionToIsocenter'],
        '0x9549': ['FD', '1', 'BreastSupportZPositionToIsocenter'],
        '0x9550': ['FD', '1', 'DetectorIsocenterPrimaryAngle'],
        '0x9551': ['FD', '1', 'DetectorIsocenterSecondaryAngle'],
        '0x9552': ['FD', '1', 'DetectorXPositionToIsocenter'],
        '0x9553': ['FD', '1', 'DetectorYPositionToIsocenter'],
        '0x9554': ['FD', '1', 'DetectorZPositionToIsocenter'],
        '0x9555': ['SQ', '1', 'XRayGridSequence'],
        '0x9556': ['SQ', '1', 'XRayFilterSequence'],
        '0x9557': ['FD', '3', 'DetectorActiveAreaTLHCPosition'],
        '0x9558': ['FD', '6', 'DetectorActiveAreaOrientation'],
        '0x9559': ['CS', '1', 'PositionerPrimaryAngleDirection'],
        '0x9601': ['SQ', '1', 'DiffusionBMatrixSequence'],
        '0x9602': ['FD', '1', 'DiffusionBValueXX'],
        '0x9603': ['FD', '1', 'DiffusionBValueXY'],
        '0x9604': ['FD', '1', 'DiffusionBValueXZ'],
        '0x9605': ['FD', '1', 'DiffusionBValueYY'],
        '0x9606': ['FD', '1', 'DiffusionBValueYZ'],
        '0x9607': ['FD', '1', 'DiffusionBValueZZ'],
        '0x9701': ['DT', '1', 'DecayCorrectionDateTime'],
        '0x9715': ['FD', '1', 'StartDensityThreshold'],
        '0x9716': ['FD', '1', 'StartRelativeDensityDifferenceThreshold'],
        '0x9717': ['FD', '1', 'StartCardiacTriggerCountThreshold'],
        '0x9718': ['FD', '1', 'StartRespiratoryTriggerCountThreshold'],
        '0x9719': ['FD', '1', 'TerminationCountsThreshold'],
        '0x9720': ['FD', '1', 'TerminationDensityThreshold'],
        '0x9721': ['FD', '1', 'TerminationRelativeDensityThreshold'],
        '0x9722': ['FD', '1', 'TerminationTimeThreshold'],
        '0x9723': ['FD', '1', 'TerminationCardiacTriggerCountThreshold'],
        '0x9724': ['FD', '1', 'TerminationRespiratoryTriggerCountThreshold'],
        '0x9725': ['CS', '1', 'DetectorGeometry'],
        '0x9726': ['FD', '1', 'TransverseDetectorSeparation'],
        '0x9727': ['FD', '1', 'AxialDetectorDimension'],
        '0x9729': ['US', '1', 'RadiopharmaceuticalAgentNumber'],
        '0x9732': ['SQ', '1', 'PETFrameAcquisitionSequence'],
        '0x9733': ['SQ', '1', 'PETDetectorMotionDetailsSequence'],
        '0x9734': ['SQ', '1', 'PETTableDynamicsSequence'],
        '0x9735': ['SQ', '1', 'PETPositionSequence'],
        '0x9736': ['SQ', '1', 'PETFrameCorrectionFactorsSequence'],
        '0x9737': ['SQ', '1', 'RadiopharmaceuticalUsageSequence'],
        '0x9738': ['CS', '1', 'AttenuationCorrectionSource'],
        '0x9739': ['US', '1', 'NumberOfIterations'],
        '0x9740': ['US', '1', 'NumberOfSubsets'],
        '0x9749': ['SQ', '1', 'PETReconstructionSequence'],
        '0x9751': ['SQ', '1', 'PETFrameTypeSequence'],
        '0x9755': ['CS', '1', 'TimeOfFlightInformationUsed'],
        '0x9756': ['CS', '1', 'ReconstructionType'],
        '0x9758': ['CS', '1', 'DecayCorrected'],
        '0x9759': ['CS', '1', 'AttenuationCorrected'],
        '0x9760': ['CS', '1', 'ScatterCorrected'],
        '0x9761': ['CS', '1', 'DeadTimeCorrected'],
        '0x9762': ['CS', '1', 'GantryMotionCorrected'],
        '0x9763': ['CS', '1', 'PatientMotionCorrected'],
        '0x9764': ['CS', '1', 'CountLossNormalizationCorrected'],
        '0x9765': ['CS', '1', 'RandomsCorrected'],
        '0x9766': ['CS', '1', 'NonUniformRadialSamplingCorrected'],
        '0x9767': ['CS', '1', 'SensitivityCalibrated'],
        '0x9768': ['CS', '1', 'DetectorNormalizationCorrection'],
        '0x9769': ['CS', '1', 'IterativeReconstructionMethod'],
        '0x9770': ['CS', '1', 'AttenuationCorrectionTemporalRelationship'],
        '0x9771': ['SQ', '1', 'PatientPhysiologicalStateSequence'],
        '0x9772': ['SQ', '1', 'PatientPhysiologicalStateCodeSequence'],
        '0x9801': ['FD', '1-n', 'DepthsOfFocus'],
        '0x9803': ['SQ', '1', 'ExcludedIntervalsSequence'],
        '0x9804': ['DT', '1', 'ExclusionStartDateTime'],
        '0x9805': ['FD', '1', 'ExclusionDuration'],
        '0x9806': ['SQ', '1', 'USImageDescriptionSequence'],
        '0x9807': ['SQ', '1', 'ImageDataTypeSequence'],
        '0x9808': ['CS', '1', 'DataType'],
        '0x9809': ['SQ', '1', 'TransducerScanPatternCodeSequence'],
        '0x980B': ['CS', '1', 'AliasedDataType'],
        '0x980C': ['CS', '1', 'PositionMeasuringDeviceUsed'],
        '0x980D': ['SQ', '1', 'TransducerGeometryCodeSequence'],
        '0x980E': ['SQ', '1', 'TransducerBeamSteeringCodeSequence'],
        '0x980F': ['SQ', '1', 'TransducerApplicationCodeSequence'],
        '0x9810': ['xs', '1', 'ZeroVelocityPixelValue'],
        '0xA001': ['SQ', '1', 'ContributingEquipmentSequence'],
        '0xA002': ['DT', '1', 'ContributionDateTime'],
        '0xA003': ['ST', '1', 'ContributionDescription']
    },
    '0x0020': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x000D': ['UI', '1', 'StudyInstanceUID'],
        '0x000E': ['UI', '1', 'SeriesInstanceUID'],
        '0x0010': ['SH', '1', 'StudyID'],
        '0x0011': ['IS', '1', 'SeriesNumber'],
        '0x0012': ['IS', '1', 'AcquisitionNumber'],
        '0x0013': ['IS', '1', 'InstanceNumber'],
        '0x0014': ['IS', '1', 'IsotopeNumber'],
        '0x0015': ['IS', '1', 'PhaseNumber'],
        '0x0016': ['IS', '1', 'IntervalNumber'],
        '0x0017': ['IS', '1', 'TimeSlotNumber'],
        '0x0018': ['IS', '1', 'AngleNumber'],
        '0x0019': ['IS', '1', 'ItemNumber'],
        '0x0020': ['CS', '2', 'PatientOrientation'],
        '0x0022': ['IS', '1', 'OverlayNumber'],
        '0x0024': ['IS', '1', 'CurveNumber'],
        '0x0026': ['IS', '1', 'LUTNumber'],
        '0x0030': ['DS', '3', 'ImagePosition'],
        '0x0032': ['DS', '3', 'ImagePositionPatient'],
        '0x0035': ['DS', '6', 'ImageOrientation'],
        '0x0037': ['DS', '6', 'ImageOrientationPatient'],
        '0x0050': ['DS', '1', 'Location'],
        '0x0052': ['UI', '1', 'FrameOfReferenceUID'],
        '0x0060': ['CS', '1', 'Laterality'],
        '0x0062': ['CS', '1', 'ImageLaterality'],
        '0x0070': ['LO', '1', 'ImageGeometryType'],
        '0x0080': ['CS', '1-n', 'MaskingImage'],
        '0x00AA': ['IS', '1', 'ReportNumber'],
        '0x0100': ['IS', '1', 'TemporalPositionIdentifier'],
        '0x0105': ['IS', '1', 'NumberOfTemporalPositions'],
        '0x0110': ['DS', '1', 'TemporalResolution'],
        '0x0200': ['UI', '1', 'SynchronizationFrameOfReferenceUID'],
        '0x0242': ['UI', '1', 'SOPInstanceUIDOfConcatenationSource'],
        '0x1000': ['IS', '1', 'SeriesInStudy'],
        '0x1001': ['IS', '1', 'AcquisitionsInSeries'],
        '0x1002': ['IS', '1', 'ImagesInAcquisition'],
        '0x1003': ['IS', '1', 'ImagesInSeries'],
        '0x1004': ['IS', '1', 'AcquisitionsInStudy'],
        '0x1005': ['IS', '1', 'ImagesInStudy'],
        '0x1020': ['LO', '1-n', 'Reference'],
        '0x1040': ['LO', '1', 'PositionReferenceIndicator'],
        '0x1041': ['DS', '1', 'SliceLocation'],
        '0x1070': ['IS', '1-n', 'OtherStudyNumbers'],
        '0x1200': ['IS', '1', 'NumberOfPatientRelatedStudies'],
        '0x1202': ['IS', '1', 'NumberOfPatientRelatedSeries'],
        '0x1204': ['IS', '1', 'NumberOfPatientRelatedInstances'],
        '0x1206': ['IS', '1', 'NumberOfStudyRelatedSeries'],
        '0x1208': ['IS', '1', 'NumberOfStudyRelatedInstances'],
        '0x1209': ['IS', '1', 'NumberOfSeriesRelatedInstances'],
        '0x3100': ['CS', '1-n', 'SourceImageIDs'],
        '0x3401': ['CS', '1', 'ModifyingDeviceID'],
        '0x3402': ['CS', '1', 'ModifiedImageID'],
        '0x3403': ['DA', '1', 'ModifiedImageDate'],
        '0x3404': ['LO', '1', 'ModifyingDeviceManufacturer'],
        '0x3405': ['TM', '1', 'ModifiedImageTime'],
        '0x3406': ['LO', '1', 'ModifiedImageDescription'],
        '0x4000': ['LT', '1', 'ImageComments'],
        '0x5000': ['AT', '1-n', 'OriginalImageIdentification'],
        '0x5002': ['LO', '1-n', 'OriginalImageIdentificationNomenclature'],
        '0x9056': ['SH', '1', 'StackID'],
        '0x9057': ['UL', '1', 'InStackPositionNumber'],
        '0x9071': ['SQ', '1', 'FrameAnatomySequence'],
        '0x9072': ['CS', '1', 'FrameLaterality'],
        '0x9111': ['SQ', '1', 'FrameContentSequence'],
        '0x9113': ['SQ', '1', 'PlanePositionSequence'],
        '0x9116': ['SQ', '1', 'PlaneOrientationSequence'],
        '0x9128': ['UL', '1', 'TemporalPositionIndex'],
        '0x9153': ['FD', '1', 'NominalCardiacTriggerDelayTime'],
        '0x9154': ['FL', '1', 'NominalCardiacTriggerTimePriorToRPeak'],
        '0x9155': ['FL', '1', 'ActualCardiacTriggerTimePriorToRPeak'],
        '0x9156': ['US', '1', 'FrameAcquisitionNumber'],
        '0x9157': ['UL', '1-n', 'DimensionIndexValues'],
        '0x9158': ['LT', '1', 'FrameComments'],
        '0x9161': ['UI', '1', 'ConcatenationUID'],
        '0x9162': ['US', '1', 'InConcatenationNumber'],
        '0x9163': ['US', '1', 'InConcatenationTotalNumber'],
        '0x9164': ['UI', '1', 'DimensionOrganizationUID'],
        '0x9165': ['AT', '1', 'DimensionIndexPointer'],
        '0x9167': ['AT', '1', 'FunctionalGroupPointer'],
        '0x9170': ['SQ', '1', 'UnassignedSharedConvertedAttributesSequence'],
        '0x9171': ['SQ', '1', 'UnassignedPerFrameConvertedAttributesSequence'],
        '0x9172': ['SQ', '1', 'ConversionSourceAttributesSequence'],
        '0x9213': ['LO', '1', 'DimensionIndexPrivateCreator'],
        '0x9221': ['SQ', '1', 'DimensionOrganizationSequence'],
        '0x9222': ['SQ', '1', 'DimensionIndexSequence'],
        '0x9228': ['UL', '1', 'ConcatenationFrameOffsetNumber'],
        '0x9238': ['LO', '1', 'FunctionalGroupPrivateCreator'],
        '0x9241': ['FL', '1', 'NominalPercentageOfCardiacPhase'],
        '0x9245': ['FL', '1', 'NominalPercentageOfRespiratoryPhase'],
        '0x9246': ['FL', '1', 'StartingRespiratoryAmplitude'],
        '0x9247': ['CS', '1', 'StartingRespiratoryPhase'],
        '0x9248': ['FL', '1', 'EndingRespiratoryAmplitude'],
        '0x9249': ['CS', '1', 'EndingRespiratoryPhase'],
        '0x9250': ['CS', '1', 'RespiratoryTriggerType'],
        '0x9251': ['FD', '1', 'RRIntervalTimeNominal'],
        '0x9252': ['FD', '1', 'ActualCardiacTriggerDelayTime'],
        '0x9253': ['SQ', '1', 'RespiratorySynchronizationSequence'],
        '0x9254': ['FD', '1', 'RespiratoryIntervalTime'],
        '0x9255': ['FD', '1', 'NominalRespiratoryTriggerDelayTime'],
        '0x9256': ['FD', '1', 'RespiratoryTriggerDelayThreshold'],
        '0x9257': ['FD', '1', 'ActualRespiratoryTriggerDelayTime'],
        '0x9301': ['FD', '3', 'ImagePositionVolume'],
        '0x9302': ['FD', '6', 'ImageOrientationVolume'],
        '0x9307': ['CS', '1', 'UltrasoundAcquisitionGeometry'],
        '0x9308': ['FD', '3', 'ApexPosition'],
        '0x9309': ['FD', '16', 'VolumeToTransducerMappingMatrix'],
        '0x930A': ['FD', '16', 'VolumeToTableMappingMatrix'],
        '0x930B': ['CS', '1', 'VolumeToTransducerRelationship'],
        '0x930C': ['CS', '1', 'PatientFrameOfReferenceSource'],
        '0x930D': ['FD', '1', 'TemporalPositionTimeOffset'],
        '0x930E': ['SQ', '1', 'PlanePositionVolumeSequence'],
        '0x930F': ['SQ', '1', 'PlaneOrientationVolumeSequence'],
        '0x9310': ['SQ', '1', 'TemporalPositionSequence'],
        '0x9311': ['CS', '1', 'DimensionOrganizationType'],
        '0x9312': ['UI', '1', 'VolumeFrameOfReferenceUID'],
        '0x9313': ['UI', '1', 'TableFrameOfReferenceUID'],
        '0x9421': ['LO', '1', 'DimensionDescriptionLabel'],
        '0x9450': ['SQ', '1', 'PatientOrientationInFrameSequence'],
        '0x9453': ['LO', '1', 'FrameLabel'],
        '0x9518': ['US', '1-n', 'AcquisitionIndex'],
        '0x9529': ['SQ', '1', 'ContributingSOPInstancesReferenceSequence'],
        '0x9536': ['US', '1', 'ReconstructionIndex']
    },
    '0x0022': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0001': ['US', '1', 'LightPathFilterPassThroughWavelength'],
        '0x0002': ['US', '2', 'LightPathFilterPassBand'],
        '0x0003': ['US', '1', 'ImagePathFilterPassThroughWavelength'],
        '0x0004': ['US', '2', 'ImagePathFilterPassBand'],
        '0x0005': ['CS', '1', 'PatientEyeMovementCommanded'],
        '0x0006': ['SQ', '1', 'PatientEyeMovementCommandCodeSequence'],
        '0x0007': ['FL', '1', 'SphericalLensPower'],
        '0x0008': ['FL', '1', 'CylinderLensPower'],
        '0x0009': ['FL', '1', 'CylinderAxis'],
        '0x000A': ['FL', '1', 'EmmetropicMagnification'],
        '0x000B': ['FL', '1', 'IntraOcularPressure'],
        '0x000C': ['FL', '1', 'HorizontalFieldOfView'],
        '0x000D': ['CS', '1', 'PupilDilated'],
        '0x000E': ['FL', '1', 'DegreeOfDilation'],
        '0x0010': ['FL', '1', 'StereoBaselineAngle'],
        '0x0011': ['FL', '1', 'StereoBaselineDisplacement'],
        '0x0012': ['FL', '1', 'StereoHorizontalPixelOffset'],
        '0x0013': ['FL', '1', 'StereoVerticalPixelOffset'],
        '0x0014': ['FL', '1', 'StereoRotation'],
        '0x0015': ['SQ', '1', 'AcquisitionDeviceTypeCodeSequence'],
        '0x0016': ['SQ', '1', 'IlluminationTypeCodeSequence'],
        '0x0017': ['SQ', '1', 'LightPathFilterTypeStackCodeSequence'],
        '0x0018': ['SQ', '1', 'ImagePathFilterTypeStackCodeSequence'],
        '0x0019': ['SQ', '1', 'LensesCodeSequence'],
        '0x001A': ['SQ', '1', 'ChannelDescriptionCodeSequence'],
        '0x001B': ['SQ', '1', 'RefractiveStateSequence'],
        '0x001C': ['SQ', '1', 'MydriaticAgentCodeSequence'],
        '0x001D': ['SQ', '1', 'RelativeImagePositionCodeSequence'],
        '0x001E': ['FL', '1', 'CameraAngleOfView'],
        '0x0020': ['SQ', '1', 'StereoPairsSequence'],
        '0x0021': ['SQ', '1', 'LeftImageSequence'],
        '0x0022': ['SQ', '1', 'RightImageSequence'],
        '0x0028': ['CS', '1', 'StereoPairsPresent'],
        '0x0030': ['FL', '1', 'AxialLengthOfTheEye'],
        '0x0031': ['SQ', '1', 'OphthalmicFrameLocationSequence'],
        '0x0032': ['FL', '2-2n', 'ReferenceCoordinates'],
        '0x0035': ['FL', '1', 'DepthSpatialResolution'],
        '0x0036': ['FL', '1', 'MaximumDepthDistortion'],
        '0x0037': ['FL', '1', 'AlongScanSpatialResolution'],
        '0x0038': ['FL', '1', 'MaximumAlongScanDistortion'],
        '0x0039': ['CS', '1', 'OphthalmicImageOrientation'],
        '0x0041': ['FL', '1', 'DepthOfTransverseImage'],
        '0x0042': ['SQ', '1', 'MydriaticAgentConcentrationUnitsSequence'],
        '0x0048': ['FL', '1', 'AcrossScanSpatialResolution'],
        '0x0049': ['FL', '1', 'MaximumAcrossScanDistortion'],
        '0x004E': ['DS', '1', 'MydriaticAgentConcentration'],
        '0x0055': ['FL', '1', 'IlluminationWaveLength'],
        '0x0056': ['FL', '1', 'IlluminationPower'],
        '0x0057': ['FL', '1', 'IlluminationBandwidth'],
        '0x0058': ['SQ', '1', 'MydriaticAgentSequence'],
        '0x1007': ['SQ', '1', 'OphthalmicAxialMeasurementsRightEyeSequence'],
        '0x1008': ['SQ', '1', 'OphthalmicAxialMeasurementsLeftEyeSequence'],
        '0x1009': ['CS', '1', 'OphthalmicAxialMeasurementsDeviceType'],
        '0x1010': ['CS', '1', 'OphthalmicAxialLengthMeasurementsType'],
        '0x1012': ['SQ', '1', 'OphthalmicAxialLengthSequence'],
        '0x1019': ['FL', '1', 'OphthalmicAxialLength'],
        '0x1024': ['SQ', '1', 'LensStatusCodeSequence'],
        '0x1025': ['SQ', '1', 'VitreousStatusCodeSequence'],
        '0x1028': ['SQ', '1', 'IOLFormulaCodeSequence'],
        '0x1029': ['LO', '1', 'IOLFormulaDetail'],
        '0x1033': ['FL', '1', 'KeratometerIndex'],
        '0x1035': ['SQ', '1', 'SourceOfOphthalmicAxialLengthCodeSequence'],
        '0x1037': ['FL', '1', 'TargetRefraction'],
        '0x1039': ['CS', '1', 'RefractiveProcedureOccurred'],
        '0x1040': ['SQ', '1', 'RefractiveSurgeryTypeCodeSequence'],
        '0x1044': ['SQ', '1', 'OphthalmicUltrasoundMethodCodeSequence'],
        '0x1050': ['SQ', '1', 'OphthalmicAxialLengthMeasurementsSequence'],
        '0x1053': ['FL', '1', 'IOLPower'],
        '0x1054': ['FL', '1', 'PredictedRefractiveError'],
        '0x1059': ['FL', '1', 'OphthalmicAxialLengthVelocity'],
        '0x1065': ['LO', '1', 'LensStatusDescription'],
        '0x1066': ['LO', '1', 'VitreousStatusDescription'],
        '0x1090': ['SQ', '1', 'IOLPowerSequence'],
        '0x1092': ['SQ', '1', 'LensConstantSequence'],
        '0x1093': ['LO', '1', 'IOLManufacturer'],
        '0x1094': ['LO', '1', 'LensConstantDescription'],
        '0x1095': ['LO', '1', 'ImplantName'],
        '0x1096': ['SQ', '1', 'KeratometryMeasurementTypeCodeSequence'],
        '0x1097': ['LO', '1', 'ImplantPartNumber'],
        '0x1100': ['SQ', '1', 'ReferencedOphthalmicAxialMeasurementsSequence'],
        '0x1101': ['SQ', '1', 'OphthalmicAxialLengthMeasurementsSegmentNameCodeSequence'],
        '0x1103': ['SQ', '1', 'RefractiveErrorBeforeRefractiveSurgeryCodeSequence'],
        '0x1121': ['FL', '1', 'IOLPowerForExactEmmetropia'],
        '0x1122': ['FL', '1', 'IOLPowerForExactTargetRefraction'],
        '0x1125': ['SQ', '1', 'AnteriorChamberDepthDefinitionCodeSequence'],
        '0x1127': ['SQ', '1', 'LensThicknessSequence'],
        '0x1128': ['SQ', '1', 'AnteriorChamberDepthSequence'],
        '0x1130': ['FL', '1', 'LensThickness'],
        '0x1131': ['FL', '1', 'AnteriorChamberDepth'],
        '0x1132': ['SQ', '1', 'SourceOfLensThicknessDataCodeSequence'],
        '0x1133': ['SQ', '1', 'SourceOfAnteriorChamberDepthDataCodeSequence'],
        '0x1134': ['SQ', '1', 'SourceOfRefractiveMeasurementsSequence'],
        '0x1135': ['SQ', '1', 'SourceOfRefractiveMeasurementsCodeSequence'],
        '0x1140': ['CS', '1', 'OphthalmicAxialLengthMeasurementModified'],
        '0x1150': ['SQ', '1', 'OphthalmicAxialLengthDataSourceCodeSequence'],
        '0x1153': ['SQ', '1', 'OphthalmicAxialLengthAcquisitionMethodCodeSequence'],
        '0x1155': ['FL', '1', 'SignalToNoiseRatio'],
        '0x1159': ['LO', '1', 'OphthalmicAxialLengthDataSourceDescription'],
        '0x1210': ['SQ', '1', 'OphthalmicAxialLengthMeasurementsTotalLengthSequence'],
        '0x1211': ['SQ', '1', 'OphthalmicAxialLengthMeasurementsSegmentalLengthSequence'],
        '0x1212': ['SQ', '1', 'OphthalmicAxialLengthMeasurementsLengthSummationSequence'],
        '0x1220': ['SQ', '1', 'UltrasoundOphthalmicAxialLengthMeasurementsSequence'],
        '0x1225': ['SQ', '1', 'OpticalOphthalmicAxialLengthMeasurementsSequence'],
        '0x1230': ['SQ', '1', 'UltrasoundSelectedOphthalmicAxialLengthSequence'],
        '0x1250': ['SQ', '1', 'OphthalmicAxialLengthSelectionMethodCodeSequence'],
        '0x1255': ['SQ', '1', 'OpticalSelectedOphthalmicAxialLengthSequence'],
        '0x1257': ['SQ', '1', 'SelectedSegmentalOphthalmicAxialLengthSequence'],
        '0x1260': ['SQ', '1', 'SelectedTotalOphthalmicAxialLengthSequence'],
        '0x1262': ['SQ', '1', 'OphthalmicAxialLengthQualityMetricSequence'],
        '0x1265': ['SQ', '1', 'OphthalmicAxialLengthQualityMetricTypeCodeSequence'],
        '0x1273': ['LO', '1', 'OphthalmicAxialLengthQualityMetricTypeDescription'],
        '0x1300': ['SQ', '1', 'IntraocularLensCalculationsRightEyeSequence'],
        '0x1310': ['SQ', '1', 'IntraocularLensCalculationsLeftEyeSequence'],
        '0x1330': ['SQ', '1', 'ReferencedOphthalmicAxialLengthMeasurementQCImageSequence'],
        '0x1415': ['CS', '1', 'OphthalmicMappingDeviceType'],
        '0x1420': ['SQ', '1', 'AcquisitionMethodCodeSequence'],
        '0x1423': ['SQ', '1', 'AcquisitionMethodAlgorithmSequence'],
        '0x1436': ['SQ', '1', 'OphthalmicThicknessMapTypeCodeSequence'],
        '0x1443': ['SQ', '1', 'OphthalmicThicknessMappingNormalsSequence'],
        '0x1445': ['SQ', '1', 'RetinalThicknessDefinitionCodeSequence'],
        '0x1450': ['SQ', '1', 'PixelValueMappingToCodedConceptSequence'],
        '0x1452': ['xs', '1', 'MappedPixelValue'],
        '0x1454': ['LO', '1', 'PixelValueMappingExplanation'],
        '0x1458': ['SQ', '1', 'OphthalmicThicknessMapQualityThresholdSequence'],
        '0x1460': ['FL', '1', 'OphthalmicThicknessMapThresholdQualityRating'],
        '0x1463': ['FL', '2', 'AnatomicStructureReferencePoint'],
        '0x1465': ['SQ', '1', 'RegistrationToLocalizerSequence'],
        '0x1466': ['CS', '1', 'RegisteredLocalizerUnits'],
        '0x1467': ['FL', '2', 'RegisteredLocalizerTopLeftHandCorner'],
        '0x1468': ['FL', '2', 'RegisteredLocalizerBottomRightHandCorner'],
        '0x1470': ['SQ', '1', 'OphthalmicThicknessMapQualityRatingSequence'],
        '0x1472': ['SQ', '1', 'RelevantOPTAttributesSequence'],
        '0x1512': ['SQ', '1', 'TransformationMethodCodeSequence'],
        '0x1513': ['SQ', '1', 'TransformationAlgorithmSequence'],
        '0x1515': ['CS', '1', 'OphthalmicAxialLengthMethod'],
        '0x1517': ['FL', '1', 'OphthalmicFOV'],
        '0x1518': ['SQ', '1', 'TwoDimensionalToThreeDimensionalMapSequence'],
        '0x1525': ['SQ', '1', 'WideFieldOphthalmicPhotographyQualityRatingSequence'],
        '0x1526': ['SQ', '1', 'WideFieldOphthalmicPhotographyQualityThresholdSequence'],
        '0x1527': ['FL', '1', 'WideFieldOphthalmicPhotographyThresholdQualityRating'],
        '0x1528': ['FL', '1', 'XCoordinatesCenterPixelViewAngle'],
        '0x1529': ['FL', '1', 'YCoordinatesCenterPixelViewAngle'],
        '0x1530': ['UL', '1', 'NumberOfMapPoints'],
        '0x1531': ['OF', '1', 'TwoDimensionalToThreeDimensionalMapData']
    },
    '0x0024': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0010': ['FL', '1', 'VisualFieldHorizontalExtent'],
        '0x0011': ['FL', '1', 'VisualFieldVerticalExtent'],
        '0x0012': ['CS', '1', 'VisualFieldShape'],
        '0x0016': ['SQ', '1', 'ScreeningTestModeCodeSequence'],
        '0x0018': ['FL', '1', 'MaximumStimulusLuminance'],
        '0x0020': ['FL', '1', 'BackgroundLuminance'],
        '0x0021': ['SQ', '1', 'StimulusColorCodeSequence'],
        '0x0024': ['SQ', '1', 'BackgroundIlluminationColorCodeSequence'],
        '0x0025': ['FL', '1', 'StimulusArea'],
        '0x0028': ['FL', '1', 'StimulusPresentationTime'],
        '0x0032': ['SQ', '1', 'FixationSequence'],
        '0x0033': ['SQ', '1', 'FixationMonitoringCodeSequence'],
        '0x0034': ['SQ', '1', 'VisualFieldCatchTrialSequence'],
        '0x0035': ['US', '1', 'FixationCheckedQuantity'],
        '0x0036': ['US', '1', 'PatientNotProperlyFixatedQuantity'],
        '0x0037': ['CS', '1', 'PresentedVisualStimuliDataFlag'],
        '0x0038': ['US', '1', 'NumberOfVisualStimuli'],
        '0x0039': ['CS', '1', 'ExcessiveFixationLossesDataFlag'],
        '0x0040': ['CS', '1', 'ExcessiveFixationLosses'],
        '0x0042': ['US', '1', 'StimuliRetestingQuantity'],
        '0x0044': ['LT', '1', 'CommentsOnPatientPerformanceOfVisualField'],
        '0x0045': ['CS', '1', 'FalseNegativesEstimateFlag'],
        '0x0046': ['FL', '1', 'FalseNegativesEstimate'],
        '0x0048': ['US', '1', 'NegativeCatchTrialsQuantity'],
        '0x0050': ['US', '1', 'FalseNegativesQuantity'],
        '0x0051': ['CS', '1', 'ExcessiveFalseNegativesDataFlag'],
        '0x0052': ['CS', '1', 'ExcessiveFalseNegatives'],
        '0x0053': ['CS', '1', 'FalsePositivesEstimateFlag'],
        '0x0054': ['FL', '1', 'FalsePositivesEstimate'],
        '0x0055': ['CS', '1', 'CatchTrialsDataFlag'],
        '0x0056': ['US', '1', 'PositiveCatchTrialsQuantity'],
        '0x0057': ['CS', '1', 'TestPointNormalsDataFlag'],
        '0x0058': ['SQ', '1', 'TestPointNormalsSequence'],
        '0x0059': ['CS', '1', 'GlobalDeviationProbabilityNormalsFlag'],
        '0x0060': ['US', '1', 'FalsePositivesQuantity'],
        '0x0061': ['CS', '1', 'ExcessiveFalsePositivesDataFlag'],
        '0x0062': ['CS', '1', 'ExcessiveFalsePositives'],
        '0x0063': ['CS', '1', 'VisualFieldTestNormalsFlag'],
        '0x0064': ['SQ', '1', 'ResultsNormalsSequence'],
        '0x0065': ['SQ', '1', 'AgeCorrectedSensitivityDeviationAlgorithmSequence'],
        '0x0066': ['FL', '1', 'GlobalDeviationFromNormal'],
        '0x0067': ['SQ', '1', 'GeneralizedDefectSensitivityDeviationAlgorithmSequence'],
        '0x0068': ['FL', '1', 'LocalizedDeviationFromNormal'],
        '0x0069': ['LO', '1', 'PatientReliabilityIndicator'],
        '0x0070': ['FL', '1', 'VisualFieldMeanSensitivity'],
        '0x0071': ['FL', '1', 'GlobalDeviationProbability'],
        '0x0072': ['CS', '1', 'LocalDeviationProbabilityNormalsFlag'],
        '0x0073': ['FL', '1', 'LocalizedDeviationProbability'],
        '0x0074': ['CS', '1', 'ShortTermFluctuationCalculated'],
        '0x0075': ['FL', '1', 'ShortTermFluctuation'],
        '0x0076': ['CS', '1', 'ShortTermFluctuationProbabilityCalculated'],
        '0x0077': ['FL', '1', 'ShortTermFluctuationProbability'],
        '0x0078': ['CS', '1', 'CorrectedLocalizedDeviationFromNormalCalculated'],
        '0x0079': ['FL', '1', 'CorrectedLocalizedDeviationFromNormal'],
        '0x0080': ['CS', '1', 'CorrectedLocalizedDeviationFromNormalProbabilityCalculated'],
        '0x0081': ['FL', '1', 'CorrectedLocalizedDeviationFromNormalProbability'],
        '0x0083': ['SQ', '1', 'GlobalDeviationProbabilitySequence'],
        '0x0085': ['SQ', '1', 'LocalizedDeviationProbabilitySequence'],
        '0x0086': ['CS', '1', 'FovealSensitivityMeasured'],
        '0x0087': ['FL', '1', 'FovealSensitivity'],
        '0x0088': ['FL', '1', 'VisualFieldTestDuration'],
        '0x0089': ['SQ', '1', 'VisualFieldTestPointSequence'],
        '0x0090': ['FL', '1', 'VisualFieldTestPointXCoordinate'],
        '0x0091': ['FL', '1', 'VisualFieldTestPointYCoordinate'],
        '0x0092': ['FL', '1', 'AgeCorrectedSensitivityDeviationValue'],
        '0x0093': ['CS', '1', 'StimulusResults'],
        '0x0094': ['FL', '1', 'SensitivityValue'],
        '0x0095': ['CS', '1', 'RetestStimulusSeen'],
        '0x0096': ['FL', '1', 'RetestSensitivityValue'],
        '0x0097': ['SQ', '1', 'VisualFieldTestPointNormalsSequence'],
        '0x0098': ['FL', '1', 'QuantifiedDefect'],
        '0x0100': ['FL', '1', 'AgeCorrectedSensitivityDeviationProbabilityValue'],
        '0x0102': ['CS', '1', 'GeneralizedDefectCorrectedSensitivityDeviationFlag'],
        '0x0103': ['FL', '1', 'GeneralizedDefectCorrectedSensitivityDeviationValue'],
        '0x0104': ['FL', '1', 'GeneralizedDefectCorrectedSensitivityDeviationProbabilityValue'],
        '0x0105': ['FL', '1', 'MinimumSensitivityValue'],
        '0x0106': ['CS', '1', 'BlindSpotLocalized'],
        '0x0107': ['FL', '1', 'BlindSpotXCoordinate'],
        '0x0108': ['FL', '1', 'BlindSpotYCoordinate'],
        '0x0110': ['SQ', '1', 'VisualAcuityMeasurementSequence'],
        '0x0112': ['SQ', '1', 'RefractiveParametersUsedOnPatientSequence'],
        '0x0113': ['CS', '1', 'MeasurementLaterality'],
        '0x0114': ['SQ', '1', 'OphthalmicPatientClinicalInformationLeftEyeSequence'],
        '0x0115': ['SQ', '1', 'OphthalmicPatientClinicalInformationRightEyeSequence'],
        '0x0117': ['CS', '1', 'FovealPointNormativeDataFlag'],
        '0x0118': ['FL', '1', 'FovealPointProbabilityValue'],
        '0x0120': ['CS', '1', 'ScreeningBaselineMeasured'],
        '0x0122': ['SQ', '1', 'ScreeningBaselineMeasuredSequence'],
        '0x0124': ['CS', '1', 'ScreeningBaselineType'],
        '0x0126': ['FL', '1', 'ScreeningBaselineValue'],
        '0x0202': ['LO', '1', 'AlgorithmSource'],
        '0x0306': ['LO', '1', 'DataSetName'],
        '0x0307': ['LO', '1', 'DataSetVersion'],
        '0x0308': ['LO', '1', 'DataSetSource'],
        '0x0309': ['LO', '1', 'DataSetDescription'],
        '0x0317': ['SQ', '1', 'VisualFieldTestReliabilityGlobalIndexSequence'],
        '0x0320': ['SQ', '1', 'VisualFieldGlobalResultsIndexSequence'],
        '0x0325': ['SQ', '1', 'DataObservationSequence'],
        '0x0338': ['CS', '1', 'IndexNormalsFlag'],
        '0x0341': ['FL', '1', 'IndexProbability'],
        '0x0344': ['SQ', '1', 'IndexProbabilitySequence']
    },
    '0x0028': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0002': ['US', '1', 'SamplesPerPixel'],
        '0x0003': ['US', '1', 'SamplesPerPixelUsed'],
        '0x0004': ['CS', '1', 'PhotometricInterpretation'],
        '0x0005': ['US', '1', 'ImageDimensions'],
        '0x0006': ['US', '1', 'PlanarConfiguration'],
        '0x0008': ['IS', '1', 'NumberOfFrames'],
        '0x0009': ['AT', '1-n', 'FrameIncrementPointer'],
        '0x000A': ['AT', '1-n', 'FrameDimensionPointer'],
        '0x0010': ['US', '1', 'Rows'],
        '0x0011': ['US', '1', 'Columns'],
        '0x0012': ['US', '1', 'Planes'],
        '0x0014': ['US', '1', 'UltrasoundColorDataPresent'],
        '0x0020': ['', '', ''],
        '0x0030': ['DS', '2', 'PixelSpacing'],
        '0x0031': ['DS', '2', 'ZoomFactor'],
        '0x0032': ['DS', '2', 'ZoomCenter'],
        '0x0034': ['IS', '2', 'PixelAspectRatio'],
        '0x0040': ['CS', '1', 'ImageFormat'],
        '0x0050': ['LO', '1-n', 'ManipulatedImage'],
        '0x0051': ['CS', '1-n', 'CorrectedImage'],
        '0x005F': ['LO', '1', 'CompressionRecognitionCode'],
        '0x0060': ['CS', '1', 'CompressionCode'],
        '0x0061': ['SH', '1', 'CompressionOriginator'],
        '0x0062': ['LO', '1', 'CompressionLabel'],
        '0x0063': ['SH', '1', 'CompressionDescription'],
        '0x0065': ['CS', '1-n', 'CompressionSequence'],
        '0x0066': ['AT', '1-n', 'CompressionStepPointers'],
        '0x0068': ['US', '1', 'RepeatInterval'],
        '0x0069': ['US', '1', 'BitsGrouped'],
        '0x0070': ['US', '1-n', 'PerimeterTable'],
        '0x0071': ['xs', '1', 'PerimeterValue'],
        '0x0080': ['US', '1', 'PredictorRows'],
        '0x0081': ['US', '1', 'PredictorColumns'],
        '0x0082': ['US', '1-n', 'PredictorConstants'],
        '0x0090': ['CS', '1', 'BlockedPixels'],
        '0x0091': ['US', '1', 'BlockRows'],
        '0x0092': ['US', '1', 'BlockColumns'],
        '0x0093': ['US', '1', 'RowOverlap'],
        '0x0094': ['US', '1', 'ColumnOverlap'],
        '0x0100': ['US', '1', 'BitsAllocated'],
        '0x0101': ['US', '1', 'BitsStored'],
        '0x0102': ['US', '1', 'HighBit'],
        '0x0103': ['US', '1', 'PixelRepresentation'],
        '0x0104': ['xs', '1', 'SmallestValidPixelValue'],
        '0x0105': ['xs', '1', 'LargestValidPixelValue'],
        '0x0106': ['xs', '1', 'SmallestImagePixelValue'],
        '0x0107': ['xs', '1', 'LargestImagePixelValue'],
        '0x0108': ['xs', '1', 'SmallestPixelValueInSeries'],
        '0x0109': ['xs', '1', 'LargestPixelValueInSeries'],
        '0x0110': ['xs', '1', 'SmallestImagePixelValueInPlane'],
        '0x0111': ['xs', '1', 'LargestImagePixelValueInPlane'],
        '0x0120': ['xs', '1', 'PixelPaddingValue'],
        '0x0121': ['xs', '1', 'PixelPaddingRangeLimit'],
        '0x0122': ['FL', '1', 'FloatPixelPaddingValue'],
        '0x0123': ['FD', '1', 'DoubleFloatPixelPaddingValue'],
        '0x0124': ['FL', '1', 'FloatPixelPaddingRangeLimit'],
        '0x0125': ['FD', '1', 'DoubleFloatPixelPaddingRangeLimit'],
        '0x0200': ['US', '1', 'ImageLocation'],
        '0x0300': ['CS', '1', 'QualityControlImage'],
        '0x0301': ['CS', '1', 'BurnedInAnnotation'],
        '0x0302': ['CS', '1', 'RecognizableVisualFeatures'],
        '0x0303': ['CS', '1', 'LongitudinalTemporalInformationModified'],
        '0x0304': ['UI', '1', 'ReferencedColorPaletteInstanceUID'],
        '0x0400': ['LO', '1', 'TransformLabel'],
        '0x0401': ['LO', '1', 'TransformVersionNumber'],
        '0x0402': ['US', '1', 'NumberOfTransformSteps'],
        '0x0403': ['LO', '1-n', 'SequenceOfCompressedData'],
        '0x0404': ['AT', '1-n', 'DetailsOfCoefficients'],
        '0x04x0': ['US', '1', 'RowsForNthOrderCoefficients'],
        '0x04x1': ['US', '1', 'ColumnsForNthOrderCoefficients'],
        '0x04x2': ['LO', '1-n', 'CoefficientCoding'],
        '0x04x3': ['AT', '1-n', 'CoefficientCodingPointers'],
        '0x0700': ['LO', '1', 'DCTLabel'],
        '0x0701': ['CS', '1-n', 'DataBlockDescription'],
        '0x0702': ['AT', '1-n', 'DataBlock'],
        '0x0710': ['US', '1', 'NormalizationFactorFormat'],
        '0x0720': ['US', '1', 'ZonalMapNumberFormat'],
        '0x0721': ['AT', '1-n', 'ZonalMapLocation'],
        '0x0722': ['US', '1', 'ZonalMapFormat'],
        '0x0730': ['US', '1', 'AdaptiveMapFormat'],
        '0x0740': ['US', '1', 'CodeNumberFormat'],
        '0x08x0': ['CS', '1-n', 'CodeLabel'],
        '0x08x2': ['US', '1', 'NumberOfTables'],
        '0x08x3': ['AT', '1-n', 'CodeTableLocation'],
        '0x08x4': ['US', '1', 'BitsForCodeWord'],
        '0x08x8': ['AT', '1-n', 'ImageDataLocation'],
        '0x0A02': ['CS', '1', 'PixelSpacingCalibrationType'],
        '0x0A04': ['LO', '1', 'PixelSpacingCalibrationDescription'],
        '0x1040': ['CS', '1', 'PixelIntensityRelationship'],
        '0x1041': ['SS', '1', 'PixelIntensityRelationshipSign'],
        '0x1050': ['DS', '1-n', 'WindowCenter'],
        '0x1051': ['DS', '1-n', 'WindowWidth'],
        '0x1052': ['DS', '1', 'RescaleIntercept'],
        '0x1053': ['DS', '1', 'RescaleSlope'],
        '0x1054': ['LO', '1', 'RescaleType'],
        '0x1055': ['LO', '1-n', 'WindowCenterWidthExplanation'],
        '0x1056': ['CS', '1', 'VOILUTFunction'],
        '0x1080': ['CS', '1', 'GrayScale'],
        '0x1090': ['CS', '1', 'RecommendedViewingMode'],
        '0x1100': ['xs', '3', 'GrayLookupTableDescriptor'],
        '0x1101': ['xs', '3', 'RedPaletteColorLookupTableDescriptor'],
        '0x1102': ['xs', '3', 'GreenPaletteColorLookupTableDescriptor'],
        '0x1103': ['xs', '3', 'BluePaletteColorLookupTableDescriptor'],
        '0x1104': ['US', '3', 'AlphaPaletteColorLookupTableDescriptor'],
        '0x1111': ['xs', '4', 'LargeRedPaletteColorLookupTableDescriptor'],
        '0x1112': ['xs', '4', 'LargeGreenPaletteColorLookupTableDescriptor'],
        '0x1113': ['xs', '4', 'LargeBluePaletteColorLookupTableDescriptor'],
        '0x1199': ['UI', '1', 'PaletteColorLookupTableUID'],
        '0x1200': ['US or SS or OW', '1-n or 1', 'GrayLookupTableData'],
        '0x1201': ['OW', '1', 'RedPaletteColorLookupTableData'],
        '0x1202': ['OW', '1', 'GreenPaletteColorLookupTableData'],
        '0x1203': ['OW', '1', 'BluePaletteColorLookupTableData'],
        '0x1204': ['OW', '1', 'AlphaPaletteColorLookupTableData'],
        '0x1211': ['OW', '1', 'LargeRedPaletteColorLookupTableData'],
        '0x1212': ['OW', '1', 'LargeGreenPaletteColorLookupTableData'],
        '0x1213': ['OW', '1', 'LargeBluePaletteColorLookupTableData'],
        '0x1214': ['UI', '1', 'LargePaletteColorLookupTableUID'],
        '0x1221': ['OW', '1', 'SegmentedRedPaletteColorLookupTableData'],
        '0x1222': ['OW', '1', 'SegmentedGreenPaletteColorLookupTableData'],
        '0x1223': ['OW', '1', 'SegmentedBluePaletteColorLookupTableData'],
        '0x1300': ['CS', '1', 'BreastImplantPresent'],
        '0x1350': ['CS', '1', 'PartialView'],
        '0x1351': ['ST', '1', 'PartialViewDescription'],
        '0x1352': ['SQ', '1', 'PartialViewCodeSequence'],
        '0x135A': ['CS', '1', 'SpatialLocationsPreserved'],
        '0x1401': ['SQ', '1', 'DataFrameAssignmentSequence'],
        '0x1402': ['CS', '1', 'DataPathAssignment'],
        '0x1403': ['US', '1', 'BitsMappedToColorLookupTable'],
        '0x1404': ['SQ', '1', 'BlendingLUT1Sequence'],
        '0x1405': ['CS', '1', 'BlendingLUT1TransferFunction'],
        '0x1406': ['FD', '1', 'BlendingWeightConstant'],
        '0x1407': ['US', '3', 'BlendingLookupTableDescriptor'],
        '0x1408': ['OW', '1', 'BlendingLookupTableData'],
        '0x140B': ['SQ', '1', 'EnhancedPaletteColorLookupTableSequence'],
        '0x140C': ['SQ', '1', 'BlendingLUT2Sequence'],
        '0x140D': ['CS', '1', 'BlendingLUT2TransferFunction'],
        '0x140E': ['CS', '1', 'DataPathID'],
        '0x140F': ['CS', '1', 'RGBLUTTransferFunction'],
        '0x1410': ['CS', '1', 'AlphaLUTTransferFunction'],
        '0x2000': ['OB', '1', 'ICCProfile'],
        '0x2110': ['CS', '1', 'LossyImageCompression'],
        '0x2112': ['DS', '1-n', 'LossyImageCompressionRatio'],
        '0x2114': ['CS', '1-n', 'LossyImageCompressionMethod'],
        '0x3000': ['SQ', '1', 'ModalityLUTSequence'],
        '0x3002': ['xs', '3', 'LUTDescriptor'],
        '0x3003': ['LO', '1', 'LUTExplanation'],
        '0x3004': ['LO', '1', 'ModalityLUTType'],
        '0x3006': ['US or OW', '1-n or 1', 'LUTData'],
        '0x3010': ['SQ', '1', 'VOILUTSequence'],
        '0x3110': ['SQ', '1', 'SoftcopyVOILUTSequence'],
        '0x4000': ['LT', '1', 'ImagePresentationComments'],
        '0x5000': ['SQ', '1', 'BiPlaneAcquisitionSequence'],
        '0x6010': ['US', '1', 'RepresentativeFrameNumber'],
        '0x6020': ['US', '1-n', 'FrameNumbersOfInterest'],
        '0x6022': ['LO', '1-n', 'FrameOfInterestDescription'],
        '0x6023': ['CS', '1-n', 'FrameOfInterestType'],
        '0x6030': ['US', '1-n', 'MaskPointers'],
        '0x6040': ['US', '1-n', 'RWavePointer'],
        '0x6100': ['SQ', '1', 'MaskSubtractionSequence'],
        '0x6101': ['CS', '1', 'MaskOperation'],
        '0x6102': ['US', '2-2n', 'ApplicableFrameRange'],
        '0x6110': ['US', '1-n', 'MaskFrameNumbers'],
        '0x6112': ['US', '1', 'ContrastFrameAveraging'],
        '0x6114': ['FL', '2', 'MaskSubPixelShift'],
        '0x6120': ['SS', '1', 'TIDOffset'],
        '0x6190': ['ST', '1', 'MaskOperationExplanation'],
        '0x7000': ['SQ', '1', 'EquipmentAdministratorSequence'],
        '0x7001': ['US', '1', 'NumberOfDisplaySubsystems'],
        '0x7002': ['US', '1', 'CurrentConfigurationID'],
        '0x7003': ['US', '1', 'DisplaySubsystemID'],
        '0x7004': ['SH', '1', 'DisplaySubsystemName'],
        '0x7005': ['LO', '1', 'DisplaySubsystemDescription'],
        '0x7006': ['CS', '1', 'SystemStatus'],
        '0x7007': ['LO', '1', 'SystemStatusComment'],
        '0x7008': ['SQ', '1', 'TargetLuminanceCharacteristicsSequence'],
        '0x7009': ['US', '1', 'LuminanceCharacteristicsID'],
        '0x700A': ['SQ', '1', 'DisplaySubsystemConfigurationSequence'],
        '0x700B': ['US', '1', 'ConfigurationID'],
        '0x700C': ['SH', '1', 'ConfigurationName'],
        '0x700D': ['LO', '1', 'ConfigurationDescription'],
        '0x700E': ['US', '1', 'ReferencedTargetLuminanceCharacteristicsID'],
        '0x700F': ['SQ', '1', 'QAResultsSequence'],
        '0x7010': ['SQ', '1', 'DisplaySubsystemQAResultsSequence'],
        '0x7011': ['SQ', '1', 'ConfigurationQAResultsSequence'],
        '0x7012': ['SQ', '1', 'MeasurementEquipmentSequence'],
        '0x7013': ['CS', '1-n', 'MeasurementFunctions'],
        '0x7014': ['CS', '1', 'MeasurementEquipmentType'],
        '0x7015': ['SQ', '1', 'VisualEvaluationResultSequence'],
        '0x7016': ['SQ', '1', 'DisplayCalibrationResultSequence'],
        '0x7017': ['US', '1', 'DDLValue'],
        '0x7018': ['FL', '2', 'CIExyWhitePoint'],
        '0x7019': ['CS', '1', 'DisplayFunctionType'],
        '0x701A': ['FL', '1', 'GammaValue'],
        '0x701B': ['US', '1', 'NumberOfLuminancePoints'],
        '0x701C': ['SQ', '1', 'LuminanceResponseSequence'],
        '0x701D': ['FL', '1', 'TargetMinimumLuminance'],
        '0x701E': ['FL', '1', 'TargetMaximumLuminance'],
        '0x701F': ['FL', '1', 'LuminanceValue'],
        '0x7020': ['LO', '1', 'LuminanceResponseDescription'],
        '0x7021': ['CS', '1', 'WhitePointFlag'],
        '0x7022': ['SQ', '1', 'DisplayDeviceTypeCodeSequence'],
        '0x7023': ['SQ', '1', 'DisplaySubsystemSequence'],
        '0x7024': ['SQ', '1', 'LuminanceResultSequence'],
        '0x7025': ['CS', '1', 'AmbientLightValueSource'],
        '0x7026': ['CS', '1-n', 'MeasuredCharacteristics'],
        '0x7027': ['SQ', '1', 'LuminanceUniformityResultSequence'],
        '0x7028': ['SQ', '1', 'VisualEvaluationTestSequence'],
        '0x7029': ['CS', '1', 'TestResult'],
        '0x702A': ['LO', '1', 'TestResultComment'],
        '0x702B': ['CS', '1', 'TestImageValidation'],
        '0x702C': ['SQ', '1', 'TestPatternCodeSequence'],
        '0x702D': ['SQ', '1', 'MeasurementPatternCodeSequence'],
        '0x702E': ['SQ', '1', 'VisualEvaluationMethodCodeSequence'],
        '0x7FE0': ['UR', '1', 'PixelDataProviderURL'],
        '0x9001': ['UL', '1', 'DataPointRows'],
        '0x9002': ['UL', '1', 'DataPointColumns'],
        '0x9003': ['CS', '1', 'SignalDomainColumns'],
        '0x9099': ['US', '1', 'LargestMonochromePixelValue'],
        '0x9108': ['CS', '1', 'DataRepresentation'],
        '0x9110': ['SQ', '1', 'PixelMeasuresSequence'],
        '0x9132': ['SQ', '1', 'FrameVOILUTSequence'],
        '0x9145': ['SQ', '1', 'PixelValueTransformationSequence'],
        '0x9235': ['CS', '1', 'SignalDomainRows'],
        '0x9411': ['FL', '1', 'DisplayFilterPercentage'],
        '0x9415': ['SQ', '1', 'FramePixelShiftSequence'],
        '0x9416': ['US', '1', 'SubtractionItemID'],
        '0x9422': ['SQ', '1', 'PixelIntensityRelationshipLUTSequence'],
        '0x9443': ['SQ', '1', 'FramePixelDataPropertiesSequence'],
        '0x9444': ['CS', '1', 'GeometricalProperties'],
        '0x9445': ['FL', '1', 'GeometricMaximumDistortion'],
        '0x9446': ['CS', '1-n', 'ImageProcessingApplied'],
        '0x9454': ['CS', '1', 'MaskSelectionMode'],
        '0x9474': ['CS', '1', 'LUTFunction'],
        '0x9478': ['FL', '1', 'MaskVisibilityPercentage'],
        '0x9501': ['SQ', '1', 'PixelShiftSequence'],
        '0x9502': ['SQ', '1', 'RegionPixelShiftSequence'],
        '0x9503': ['SS', '2-2n', 'VerticesOfTheRegion'],
        '0x9505': ['SQ', '1', 'MultiFramePresentationSequence'],
        '0x9506': ['US', '2-2n', 'PixelShiftFrameRange'],
        '0x9507': ['US', '2-2n', 'LUTFrameRange'],
        '0x9520': ['DS', '16', 'ImageToEquipmentMappingMatrix'],
        '0x9537': ['CS', '1', 'EquipmentCoordinateSystemIdentification']
    },
    '0x0032': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x000A': ['CS', '1', 'StudyStatusID'],
        '0x000C': ['CS', '1', 'StudyPriorityID'],
        '0x0012': ['LO', '1', 'StudyIDIssuer'],
        '0x0032': ['DA', '1', 'StudyVerifiedDate'],
        '0x0033': ['TM', '1', 'StudyVerifiedTime'],
        '0x0034': ['DA', '1', 'StudyReadDate'],
        '0x0035': ['TM', '1', 'StudyReadTime'],
        '0x1000': ['DA', '1', 'ScheduledStudyStartDate'],
        '0x1001': ['TM', '1', 'ScheduledStudyStartTime'],
        '0x1010': ['DA', '1', 'ScheduledStudyStopDate'],
        '0x1011': ['TM', '1', 'ScheduledStudyStopTime'],
        '0x1020': ['LO', '1', 'ScheduledStudyLocation'],
        '0x1021': ['AE', '1-n', 'ScheduledStudyLocationAETitle'],
        '0x1030': ['LO', '1', 'ReasonForStudy'],
        '0x1031': ['SQ', '1', 'RequestingPhysicianIdentificationSequence'],
        '0x1032': ['PN', '1', 'RequestingPhysician'],
        '0x1033': ['LO', '1', 'RequestingService'],
        '0x1034': ['SQ', '1', 'RequestingServiceCodeSequence'],
        '0x1040': ['DA', '1', 'StudyArrivalDate'],
        '0x1041': ['TM', '1', 'StudyArrivalTime'],
        '0x1050': ['DA', '1', 'StudyCompletionDate'],
        '0x1051': ['TM', '1', 'StudyCompletionTime'],
        '0x1055': ['CS', '1', 'StudyComponentStatusID'],
        '0x1060': ['LO', '1', 'RequestedProcedureDescription'],
        '0x1064': ['SQ', '1', 'RequestedProcedureCodeSequence'],
        '0x1070': ['LO', '1', 'RequestedContrastAgent'],
        '0x4000': ['LT', '1', 'StudyComments']
    },
    '0x0038': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0004': ['SQ', '1', 'ReferencedPatientAliasSequence'],
        '0x0008': ['CS', '1', 'VisitStatusID'],
        '0x0010': ['LO', '1', 'AdmissionID'],
        '0x0011': ['LO', '1', 'IssuerOfAdmissionID'],
        '0x0014': ['SQ', '1', 'IssuerOfAdmissionIDSequence'],
        '0x0016': ['LO', '1', 'RouteOfAdmissions'],
        '0x001A': ['DA', '1', 'ScheduledAdmissionDate'],
        '0x001B': ['TM', '1', 'ScheduledAdmissionTime'],
        '0x001C': ['DA', '1', 'ScheduledDischargeDate'],
        '0x001D': ['TM', '1', 'ScheduledDischargeTime'],
        '0x001E': ['LO', '1', 'ScheduledPatientInstitutionResidence'],
        '0x0020': ['DA', '1', 'AdmittingDate'],
        '0x0021': ['TM', '1', 'AdmittingTime'],
        '0x0030': ['DA', '1', 'DischargeDate'],
        '0x0032': ['TM', '1', 'DischargeTime'],
        '0x0040': ['LO', '1', 'DischargeDiagnosisDescription'],
        '0x0044': ['SQ', '1', 'DischargeDiagnosisCodeSequence'],
        '0x0050': ['LO', '1', 'SpecialNeeds'],
        '0x0060': ['LO', '1', 'ServiceEpisodeID'],
        '0x0061': ['LO', '1', 'IssuerOfServiceEpisodeID'],
        '0x0062': ['LO', '1', 'ServiceEpisodeDescription'],
        '0x0064': ['SQ', '1', 'IssuerOfServiceEpisodeIDSequence'],
        '0x0100': ['SQ', '1', 'PertinentDocumentsSequence'],
        '0x0101': ['SQ', '1', 'PertinentResourcesSequence'],
        '0x0102': ['LO', '1', 'ResourceDescription'],
        '0x0300': ['LO', '1', 'CurrentPatientLocation'],
        '0x0400': ['LO', '1', 'PatientInstitutionResidence'],
        '0x0500': ['LO', '1', 'PatientState'],
        '0x0502': ['SQ', '1', 'PatientClinicalTrialParticipationSequence'],
        '0x4000': ['LT', '1', 'VisitComments']
    },
    '0x003A': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0004': ['CS', '1', 'WaveformOriginality'],
        '0x0005': ['US', '1', 'NumberOfWaveformChannels'],
        '0x0010': ['UL', '1', 'NumberOfWaveformSamples'],
        '0x001A': ['DS', '1', 'SamplingFrequency'],
        '0x0020': ['SH', '1', 'MultiplexGroupLabel'],
        '0x0200': ['SQ', '1', 'ChannelDefinitionSequence'],
        '0x0202': ['IS', '1', 'WaveformChannelNumber'],
        '0x0203': ['SH', '1', 'ChannelLabel'],
        '0x0205': ['CS', '1-n', 'ChannelStatus'],
        '0x0208': ['SQ', '1', 'ChannelSourceSequence'],
        '0x0209': ['SQ', '1', 'ChannelSourceModifiersSequence'],
        '0x020A': ['SQ', '1', 'SourceWaveformSequence'],
        '0x020C': ['LO', '1', 'ChannelDerivationDescription'],
        '0x0210': ['DS', '1', 'ChannelSensitivity'],
        '0x0211': ['SQ', '1', 'ChannelSensitivityUnitsSequence'],
        '0x0212': ['DS', '1', 'ChannelSensitivityCorrectionFactor'],
        '0x0213': ['DS', '1', 'ChannelBaseline'],
        '0x0214': ['DS', '1', 'ChannelTimeSkew'],
        '0x0215': ['DS', '1', 'ChannelSampleSkew'],
        '0x0218': ['DS', '1', 'ChannelOffset'],
        '0x021A': ['US', '1', 'WaveformBitsStored'],
        '0x0220': ['DS', '1', 'FilterLowFrequency'],
        '0x0221': ['DS', '1', 'FilterHighFrequency'],
        '0x0222': ['DS', '1', 'NotchFilterFrequency'],
        '0x0223': ['DS', '1', 'NotchFilterBandwidth'],
        '0x0230': ['FL', '1', 'WaveformDataDisplayScale'],
        '0x0231': ['US', '3', 'WaveformDisplayBackgroundCIELabValue'],
        '0x0240': ['SQ', '1', 'WaveformPresentationGroupSequence'],
        '0x0241': ['US', '1', 'PresentationGroupNumber'],
        '0x0242': ['SQ', '1', 'ChannelDisplaySequence'],
        '0x0244': ['US', '3', 'ChannelRecommendedDisplayCIELabValue'],
        '0x0245': ['FL', '1', 'ChannelPosition'],
        '0x0246': ['CS', '1', 'DisplayShadingFlag'],
        '0x0247': ['FL', '1', 'FractionalChannelDisplayScale'],
        '0x0248': ['FL', '1', 'AbsoluteChannelDisplayScale'],
        '0x0300': ['SQ', '1', 'MultiplexedAudioChannelsDescriptionCodeSequence'],
        '0x0301': ['IS', '1', 'ChannelIdentificationCode'],
        '0x0302': ['CS', '1', 'ChannelMode']
    },
    '0x0040': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0001': ['AE', '1-n', 'ScheduledStationAETitle'],
        '0x0002': ['DA', '1', 'ScheduledProcedureStepStartDate'],
        '0x0003': ['TM', '1', 'ScheduledProcedureStepStartTime'],
        '0x0004': ['DA', '1', 'ScheduledProcedureStepEndDate'],
        '0x0005': ['TM', '1', 'ScheduledProcedureStepEndTime'],
        '0x0006': ['PN', '1', 'ScheduledPerformingPhysicianName'],
        '0x0007': ['LO', '1', 'ScheduledProcedureStepDescription'],
        '0x0008': ['SQ', '1', 'ScheduledProtocolCodeSequence'],
        '0x0009': ['SH', '1', 'ScheduledProcedureStepID'],
        '0x000A': ['SQ', '1', 'StageCodeSequence'],
        '0x000B': ['SQ', '1', 'ScheduledPerformingPhysicianIdentificationSequence'],
        '0x0010': ['SH', '1-n', 'ScheduledStationName'],
        '0x0011': ['SH', '1', 'ScheduledProcedureStepLocation'],
        '0x0012': ['LO', '1', 'PreMedication'],
        '0x0020': ['CS', '1', 'ScheduledProcedureStepStatus'],
        '0x0026': ['SQ', '1', 'OrderPlacerIdentifierSequence'],
        '0x0027': ['SQ', '1', 'OrderFillerIdentifierSequence'],
        '0x0031': ['UT', '1', 'LocalNamespaceEntityID'],
        '0x0032': ['UT', '1', 'UniversalEntityID'],
        '0x0033': ['CS', '1', 'UniversalEntityIDType'],
        '0x0035': ['CS', '1', 'IdentifierTypeCode'],
        '0x0036': ['SQ', '1', 'AssigningFacilitySequence'],
        '0x0039': ['SQ', '1', 'AssigningJurisdictionCodeSequence'],
        '0x003A': ['SQ', '1', 'AssigningAgencyOrDepartmentCodeSequence'],
        '0x0100': ['SQ', '1', 'ScheduledProcedureStepSequence'],
        '0x0220': ['SQ', '1', 'ReferencedNonImageCompositeSOPInstanceSequence'],
        '0x0241': ['AE', '1', 'PerformedStationAETitle'],
        '0x0242': ['SH', '1', 'PerformedStationName'],
        '0x0243': ['SH', '1', 'PerformedLocation'],
        '0x0244': ['DA', '1', 'PerformedProcedureStepStartDate'],
        '0x0245': ['TM', '1', 'PerformedProcedureStepStartTime'],
        '0x0250': ['DA', '1', 'PerformedProcedureStepEndDate'],
        '0x0251': ['TM', '1', 'PerformedProcedureStepEndTime'],
        '0x0252': ['CS', '1', 'PerformedProcedureStepStatus'],
        '0x0253': ['SH', '1', 'PerformedProcedureStepID'],
        '0x0254': ['LO', '1', 'PerformedProcedureStepDescription'],
        '0x0255': ['LO', '1', 'PerformedProcedureTypeDescription'],
        '0x0260': ['SQ', '1', 'PerformedProtocolCodeSequence'],
        '0x0261': ['CS', '1', 'PerformedProtocolType'],
        '0x0270': ['SQ', '1', 'ScheduledStepAttributesSequence'],
        '0x0275': ['SQ', '1', 'RequestAttributesSequence'],
        '0x0280': ['ST', '1', 'CommentsOnThePerformedProcedureStep'],
        '0x0281': ['SQ', '1', 'PerformedProcedureStepDiscontinuationReasonCodeSequence'],
        '0x0293': ['SQ', '1', 'QuantitySequence'],
        '0x0294': ['DS', '1', 'Quantity'],
        '0x0295': ['SQ', '1', 'MeasuringUnitsSequence'],
        '0x0296': ['SQ', '1', 'BillingItemSequence'],
        '0x0300': ['US', '1', 'TotalTimeOfFluoroscopy'],
        '0x0301': ['US', '1', 'TotalNumberOfExposures'],
        '0x0302': ['US', '1', 'EntranceDose'],
        '0x0303': ['US', '1-2', 'ExposedArea'],
        '0x0306': ['DS', '1', 'DistanceSourceToEntrance'],
        '0x0307': ['DS', '1', 'DistanceSourceToSupport'],
        '0x030E': ['SQ', '1', 'ExposureDoseSequence'],
        '0x0310': ['ST', '1', 'CommentsOnRadiationDose'],
        '0x0312': ['DS', '1', 'XRayOutput'],
        '0x0314': ['DS', '1', 'HalfValueLayer'],
        '0x0316': ['DS', '1', 'OrganDose'],
        '0x0318': ['CS', '1', 'OrganExposed'],
        '0x0320': ['SQ', '1', 'BillingProcedureStepSequence'],
        '0x0321': ['SQ', '1', 'FilmConsumptionSequence'],
        '0x0324': ['SQ', '1', 'BillingSuppliesAndDevicesSequence'],
        '0x0330': ['SQ', '1', 'ReferencedProcedureStepSequence'],
        '0x0340': ['SQ', '1', 'PerformedSeriesSequence'],
        '0x0400': ['LT', '1', 'CommentsOnTheScheduledProcedureStep'],
        '0x0440': ['SQ', '1', 'ProtocolContextSequence'],
        '0x0441': ['SQ', '1', 'ContentItemModifierSequence'],
        '0x0500': ['SQ', '1', 'ScheduledSpecimenSequence'],
        '0x050A': ['LO', '1', 'SpecimenAccessionNumber'],
        '0x0512': ['LO', '1', 'ContainerIdentifier'],
        '0x0513': ['SQ', '1', 'IssuerOfTheContainerIdentifierSequence'],
        '0x0515': ['SQ', '1', 'AlternateContainerIdentifierSequence'],
        '0x0518': ['SQ', '1', 'ContainerTypeCodeSequence'],
        '0x051A': ['LO', '1', 'ContainerDescription'],
        '0x0520': ['SQ', '1', 'ContainerComponentSequence'],
        '0x0550': ['SQ', '1', 'SpecimenSequence'],
        '0x0551': ['LO', '1', 'SpecimenIdentifier'],
        '0x0552': ['SQ', '1', 'SpecimenDescriptionSequenceTrial'],
        '0x0553': ['ST', '1', 'SpecimenDescriptionTrial'],
        '0x0554': ['UI', '1', 'SpecimenUID'],
        '0x0555': ['SQ', '1', 'AcquisitionContextSequence'],
        '0x0556': ['ST', '1', 'AcquisitionContextDescription'],
        '0x059A': ['SQ', '1', 'SpecimenTypeCodeSequence'],
        '0x0560': ['SQ', '1', 'SpecimenDescriptionSequence'],
        '0x0562': ['SQ', '1', 'IssuerOfTheSpecimenIdentifierSequence'],
        '0x0600': ['LO', '1', 'SpecimenShortDescription'],
        '0x0602': ['UT', '1', 'SpecimenDetailedDescription'],
        '0x0610': ['SQ', '1', 'SpecimenPreparationSequence'],
        '0x0612': ['SQ', '1', 'SpecimenPreparationStepContentItemSequence'],
        '0x0620': ['SQ', '1', 'SpecimenLocalizationContentItemSequence'],
        '0x06FA': ['LO', '1', 'SlideIdentifier'],
        '0x071A': ['SQ', '1', 'ImageCenterPointCoordinatesSequence'],
        '0x072A': ['DS', '1', 'XOffsetInSlideCoordinateSystem'],
        '0x073A': ['DS', '1', 'YOffsetInSlideCoordinateSystem'],
        '0x074A': ['DS', '1', 'ZOffsetInSlideCoordinateSystem'],
        '0x08D8': ['SQ', '1', 'PixelSpacingSequence'],
        '0x08DA': ['SQ', '1', 'CoordinateSystemAxisCodeSequence'],
        '0x08EA': ['SQ', '1', 'MeasurementUnitsCodeSequence'],
        '0x09F8': ['SQ', '1', 'VitalStainCodeSequenceTrial'],
        '0x1001': ['SH', '1', 'RequestedProcedureID'],
        '0x1002': ['LO', '1', 'ReasonForTheRequestedProcedure'],
        '0x1003': ['SH', '1', 'RequestedProcedurePriority'],
        '0x1004': ['LO', '1', 'PatientTransportArrangements'],
        '0x1005': ['LO', '1', 'RequestedProcedureLocation'],
        '0x1006': ['SH', '1', 'PlacerOrderNumberProcedure'],
        '0x1007': ['SH', '1', 'FillerOrderNumberProcedure'],
        '0x1008': ['LO', '1', 'ConfidentialityCode'],
        '0x1009': ['SH', '1', 'ReportingPriority'],
        '0x100A': ['SQ', '1', 'ReasonForRequestedProcedureCodeSequence'],
        '0x1010': ['PN', '1-n', 'NamesOfIntendedRecipientsOfResults'],
        '0x1011': ['SQ', '1', 'IntendedRecipientsOfResultsIdentificationSequence'],
        '0x1012': ['SQ', '1', 'ReasonForPerformedProcedureCodeSequence'],
        '0x1060': ['LO', '1', 'RequestedProcedureDescriptionTrial'],
        '0x1101': ['SQ', '1', 'PersonIdentificationCodeSequence'],
        '0x1102': ['ST', '1', 'PersonAddress'],
        '0x1103': ['LO', '1-n', 'PersonTelephoneNumbers'],
        '0x1104': ['LT', '1', 'PersonTelecomInformation'],
        '0x1400': ['LT', '1', 'RequestedProcedureComments'],
        '0x2001': ['LO', '1', 'ReasonForTheImagingServiceRequest'],
        '0x2004': ['DA', '1', 'IssueDateOfImagingServiceRequest'],
        '0x2005': ['TM', '1', 'IssueTimeOfImagingServiceRequest'],
        '0x2006': ['SH', '1', 'PlacerOrderNumberImagingServiceRequestRetired'],
        '0x2007': ['SH', '1', 'FillerOrderNumberImagingServiceRequestRetired'],
        '0x2008': ['PN', '1', 'OrderEnteredBy'],
        '0x2009': ['SH', '1', 'OrderEntererLocation'],
        '0x2010': ['SH', '1', 'OrderCallbackPhoneNumber'],
        '0x2011': ['LT', '1', 'OrderCallbackTelecomInformation'],
        '0x2016': ['LO', '1', 'PlacerOrderNumberImagingServiceRequest'],
        '0x2017': ['LO', '1', 'FillerOrderNumberImagingServiceRequest'],
        '0x2400': ['LT', '1', 'ImagingServiceRequestComments'],
        '0x3001': ['LO', '1', 'ConfidentialityConstraintOnPatientDataDescription'],
        '0x4001': ['CS', '1', 'GeneralPurposeScheduledProcedureStepStatus'],
        '0x4002': ['CS', '1', 'GeneralPurposePerformedProcedureStepStatus'],
        '0x4003': ['CS', '1', 'GeneralPurposeScheduledProcedureStepPriority'],
        '0x4004': ['SQ', '1', 'ScheduledProcessingApplicationsCodeSequence'],
        '0x4005': ['DT', '1', 'ScheduledProcedureStepStartDateTime'],
        '0x4006': ['CS', '1', 'MultipleCopiesFlag'],
        '0x4007': ['SQ', '1', 'PerformedProcessingApplicationsCodeSequence'],
        '0x4009': ['SQ', '1', 'HumanPerformerCodeSequence'],
        '0x4010': ['DT', '1', 'ScheduledProcedureStepModificationDateTime'],
        '0x4011': ['DT', '1', 'ExpectedCompletionDateTime'],
        '0x4015': ['SQ', '1', 'ResultingGeneralPurposePerformedProcedureStepsSequence'],
        '0x4016': ['SQ', '1', 'ReferencedGeneralPurposeScheduledProcedureStepSequence'],
        '0x4018': ['SQ', '1', 'ScheduledWorkitemCodeSequence'],
        '0x4019': ['SQ', '1', 'PerformedWorkitemCodeSequence'],
        '0x4020': ['CS', '1', 'InputAvailabilityFlag'],
        '0x4021': ['SQ', '1', 'InputInformationSequence'],
        '0x4022': ['SQ', '1', 'RelevantInformationSequence'],
        '0x4023': ['UI', '1', 'ReferencedGeneralPurposeScheduledProcedureStepTransactionUID'],
        '0x4025': ['SQ', '1', 'ScheduledStationNameCodeSequence'],
        '0x4026': ['SQ', '1', 'ScheduledStationClassCodeSequence'],
        '0x4027': ['SQ', '1', 'ScheduledStationGeographicLocationCodeSequence'],
        '0x4028': ['SQ', '1', 'PerformedStationNameCodeSequence'],
        '0x4029': ['SQ', '1', 'PerformedStationClassCodeSequence'],
        '0x4030': ['SQ', '1', 'PerformedStationGeographicLocationCodeSequence'],
        '0x4031': ['SQ', '1', 'RequestedSubsequentWorkitemCodeSequence'],
        '0x4032': ['SQ', '1', 'NonDICOMOutputCodeSequence'],
        '0x4033': ['SQ', '1', 'OutputInformationSequence'],
        '0x4034': ['SQ', '1', 'ScheduledHumanPerformersSequence'],
        '0x4035': ['SQ', '1', 'ActualHumanPerformersSequence'],
        '0x4036': ['LO', '1', 'HumanPerformerOrganization'],
        '0x4037': ['PN', '1', 'HumanPerformerName'],
        '0x4040': ['CS', '1', 'RawDataHandling'],
        '0x4041': ['CS', '1', 'InputReadinessState'],
        '0x4050': ['DT', '1', 'PerformedProcedureStepStartDateTime'],
        '0x4051': ['DT', '1', 'PerformedProcedureStepEndDateTime'],
        '0x4052': ['DT', '1', 'ProcedureStepCancellationDateTime'],
        '0x8302': ['DS', '1', 'EntranceDoseInmGy'],
        '0x9092': ['SQ', '1', 'ParametricMapFrameTypeSequence'],
        '0x9094': ['SQ', '1', 'ReferencedImageRealWorldValueMappingSequence'],
        '0x9096': ['SQ', '1', 'RealWorldValueMappingSequence'],
        '0x9098': ['SQ', '1', 'PixelValueMappingCodeSequence'],
        '0x9210': ['SH', '1', 'LUTLabel'],
        '0x9211': ['xs', '1', 'RealWorldValueLastValueMapped'],
        '0x9212': ['FD', '1-n', 'RealWorldValueLUTData'],
        '0x9216': ['xs', '1', 'RealWorldValueFirstValueMapped'],
        '0x9220': ['SQ', '1', 'QuantityDefinitionSequence'],
        '0x9224': ['FD', '1', 'RealWorldValueIntercept'],
        '0x9225': ['FD', '1', 'RealWorldValueSlope'],
        '0xA007': ['CS', '1', 'FindingsFlagTrial'],
        '0xA010': ['CS', '1', 'RelationshipType'],
        '0xA020': ['SQ', '1', 'FindingsSequenceTrial'],
        '0xA021': ['UI', '1', 'FindingsGroupUIDTrial'],
        '0xA022': ['UI', '1', 'ReferencedFindingsGroupUIDTrial'],
        '0xA023': ['DA', '1', 'FindingsGroupRecordingDateTrial'],
        '0xA024': ['TM', '1', 'FindingsGroupRecordingTimeTrial'],
        '0xA026': ['SQ', '1', 'FindingsSourceCategoryCodeSequenceTrial'],
        '0xA027': ['LO', '1', 'VerifyingOrganization'],
        '0xA028': ['SQ', '1', 'DocumentingOrganizationIdentifierCodeSequenceTrial'],
        '0xA030': ['DT', '1', 'VerificationDateTime'],
        '0xA032': ['DT', '1', 'ObservationDateTime'],
        '0xA040': ['CS', '1', 'ValueType'],
        '0xA043': ['SQ', '1', 'ConceptNameCodeSequence'],
        '0xA047': ['LO', '1', 'MeasurementPrecisionDescriptionTrial'],
        '0xA050': ['CS', '1', 'ContinuityOfContent'],
        '0xA057': ['CS', '1-n', 'UrgencyOrPriorityAlertsTrial'],
        '0xA060': ['LO', '1', 'SequencingIndicatorTrial'],
        '0xA066': ['SQ', '1', 'DocumentIdentifierCodeSequenceTrial'],
        '0xA067': ['PN', '1', 'DocumentAuthorTrial'],
        '0xA068': ['SQ', '1', 'DocumentAuthorIdentifierCodeSequenceTrial'],
        '0xA070': ['SQ', '1', 'IdentifierCodeSequenceTrial'],
        '0xA073': ['SQ', '1', 'VerifyingObserverSequence'],
        '0xA074': ['OB', '1', 'ObjectBinaryIdentifierTrial'],
        '0xA075': ['PN', '1', 'VerifyingObserverName'],
        '0xA076': ['SQ', '1', 'DocumentingObserverIdentifierCodeSequenceTrial'],
        '0xA078': ['SQ', '1', 'AuthorObserverSequence'],
        '0xA07A': ['SQ', '1', 'ParticipantSequence'],
        '0xA07C': ['SQ', '1', 'CustodialOrganizationSequence'],
        '0xA080': ['CS', '1', 'ParticipationType'],
        '0xA082': ['DT', '1', 'ParticipationDateTime'],
        '0xA084': ['CS', '1', 'ObserverType'],
        '0xA085': ['SQ', '1', 'ProcedureIdentifierCodeSequenceTrial'],
        '0xA088': ['SQ', '1', 'VerifyingObserverIdentificationCodeSequence'],
        '0xA089': ['OB', '1', 'ObjectDirectoryBinaryIdentifierTrial'],
        '0xA090': ['SQ', '1', 'EquivalentCDADocumentSequence'],
        '0xA0B0': ['US', '2-2n', 'ReferencedWaveformChannels'],
        '0xA110': ['DA', '1', 'DateOfDocumentOrVerbalTransactionTrial'],
        '0xA112': ['TM', '1', 'TimeOfDocumentCreationOrVerbalTransactionTrial'],
        '0xA120': ['DT', '1', 'DateTime'],
        '0xA121': ['DA', '1', 'Date'],
        '0xA122': ['TM', '1', 'Time'],
        '0xA123': ['PN', '1', 'PersonName'],
        '0xA124': ['UI', '1', 'UID'],
        '0xA125': ['CS', '2', 'ReportStatusIDTrial'],
        '0xA130': ['CS', '1', 'TemporalRangeType'],
        '0xA132': ['UL', '1-n', 'ReferencedSamplePositions'],
        '0xA136': ['US', '1-n', 'ReferencedFrameNumbers'],
        '0xA138': ['DS', '1-n', 'ReferencedTimeOffsets'],
        '0xA13A': ['DT', '1-n', 'ReferencedDateTime'],
        '0xA160': ['UT', '1', 'TextValue'],
        '0xA161': ['FD', '1-n', 'FloatingPointValue'],
        '0xA162': ['SL', '1-n', 'RationalNumeratorValue'],
        '0xA163': ['UL', '1-n', 'RationalDenominatorValue'],
        '0xA167': ['SQ', '1', 'ObservationCategoryCodeSequenceTrial'],
        '0xA168': ['SQ', '1', 'ConceptCodeSequence'],
        '0xA16A': ['ST', '1', 'BibliographicCitationTrial'],
        '0xA170': ['SQ', '1', 'PurposeOfReferenceCodeSequence'],
        '0xA171': ['UI', '1', 'ObservationUID'],
        '0xA172': ['UI', '1', 'ReferencedObservationUIDTrial'],
        '0xA173': ['CS', '1', 'ReferencedObservationClassTrial'],
        '0xA174': ['CS', '1', 'ReferencedObjectObservationClassTrial'],
        '0xA180': ['US', '1', 'AnnotationGroupNumber'],
        '0xA192': ['DA', '1', 'ObservationDateTrial'],
        '0xA193': ['TM', '1', 'ObservationTimeTrial'],
        '0xA194': ['CS', '1', 'MeasurementAutomationTrial'],
        '0xA195': ['SQ', '1', 'ModifierCodeSequence'],
        '0xA224': ['ST', '1', 'IdentificationDescriptionTrial'],
        '0xA290': ['CS', '1', 'CoordinatesSetGeometricTypeTrial'],
        '0xA296': ['SQ', '1', 'AlgorithmCodeSequenceTrial'],
        '0xA297': ['ST', '1', 'AlgorithmDescriptionTrial'],
        '0xA29A': ['SL', '2-2n', 'PixelCoordinatesSetTrial'],
        '0xA300': ['SQ', '1', 'MeasuredValueSequence'],
        '0xA301': ['SQ', '1', 'NumericValueQualifierCodeSequence'],
        '0xA307': ['PN', '1', 'CurrentObserverTrial'],
        '0xA30A': ['DS', '1-n', 'NumericValue'],
        '0xA313': ['SQ', '1', 'ReferencedAccessionSequenceTrial'],
        '0xA33A': ['ST', '1', 'ReportStatusCommentTrial'],
        '0xA340': ['SQ', '1', 'ProcedureContextSequenceTrial'],
        '0xA352': ['PN', '1', 'VerbalSourceTrial'],
        '0xA353': ['ST', '1', 'AddressTrial'],
        '0xA354': ['LO', '1', 'TelephoneNumberTrial'],
        '0xA358': ['SQ', '1', 'VerbalSourceIdentifierCodeSequenceTrial'],
        '0xA360': ['SQ', '1', 'PredecessorDocumentsSequence'],
        '0xA370': ['SQ', '1', 'ReferencedRequestSequence'],
        '0xA372': ['SQ', '1', 'PerformedProcedureCodeSequence'],
        '0xA375': ['SQ', '1', 'CurrentRequestedProcedureEvidenceSequence'],
        '0xA380': ['SQ', '1', 'ReportDetailSequenceTrial'],
        '0xA385': ['SQ', '1', 'PertinentOtherEvidenceSequence'],
        '0xA390': ['SQ', '1', 'HL7StructuredDocumentReferenceSequence'],
        '0xA402': ['UI', '1', 'ObservationSubjectUIDTrial'],
        '0xA403': ['CS', '1', 'ObservationSubjectClassTrial'],
        '0xA404': ['SQ', '1', 'ObservationSubjectTypeCodeSequenceTrial'],
        '0xA491': ['CS', '1', 'CompletionFlag'],
        '0xA492': ['LO', '1', 'CompletionFlagDescription'],
        '0xA493': ['CS', '1', 'VerificationFlag'],
        '0xA494': ['CS', '1', 'ArchiveRequested'],
        '0xA496': ['CS', '1', 'PreliminaryFlag'],
        '0xA504': ['SQ', '1', 'ContentTemplateSequence'],
        '0xA525': ['SQ', '1', 'IdenticalDocumentsSequence'],
        '0xA600': ['CS', '1', 'ObservationSubjectContextFlagTrial'],
        '0xA601': ['CS', '1', 'ObserverContextFlagTrial'],
        '0xA603': ['CS', '1', 'ProcedureContextFlagTrial'],
        '0xA730': ['SQ', '1', 'ContentSequence'],
        '0xA731': ['SQ', '1', 'RelationshipSequenceTrial'],
        '0xA732': ['SQ', '1', 'RelationshipTypeCodeSequenceTrial'],
        '0xA744': ['SQ', '1', 'LanguageCodeSequenceTrial'],
        '0xA992': ['ST', '1', 'UniformResourceLocatorTrial'],
        '0xB020': ['SQ', '1', 'WaveformAnnotationSequence'],
        '0xDB00': ['CS', '1', 'TemplateIdentifier'],
        '0xDB06': ['DT', '1', 'TemplateVersion'],
        '0xDB07': ['DT', '1', 'TemplateLocalVersion'],
        '0xDB0B': ['CS', '1', 'TemplateExtensionFlag'],
        '0xDB0C': ['UI', '1', 'TemplateExtensionOrganizationUID'],
        '0xDB0D': ['UI', '1', 'TemplateExtensionCreatorUID'],
        '0xDB73': ['UL', '1-n', 'ReferencedContentItemIdentifier'],
        '0xE001': ['ST', '1', 'HL7InstanceIdentifier'],
        '0xE004': ['DT', '1', 'HL7DocumentEffectiveTime'],
        '0xE006': ['SQ', '1', 'HL7DocumentTypeCodeSequence'],
        '0xE008': ['SQ', '1', 'DocumentClassCodeSequence'],
        '0xE010': ['UR', '1', 'RetrieveURI'],
        '0xE011': ['UI', '1', 'RetrieveLocationUID'],
        '0xE020': ['CS', '1', 'TypeOfInstances'],
        '0xE021': ['SQ', '1', 'DICOMRetrievalSequence'],
        '0xE022': ['SQ', '1', 'DICOMMediaRetrievalSequence'],
        '0xE023': ['SQ', '1', 'WADORetrievalSequence'],
        '0xE024': ['SQ', '1', 'XDSRetrievalSequence'],
        '0xE025': ['SQ', '1', 'WADORSRetrievalSequence'],
        '0xE030': ['UI', '1', 'RepositoryUniqueID'],
        '0xE031': ['UI', '1', 'HomeCommunityID']
    },
    '0x0042': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0010': ['ST', '1', 'DocumentTitle'],
        '0x0011': ['OB', '1', 'EncapsulatedDocument'],
        '0x0012': ['LO', '1', 'MIMETypeOfEncapsulatedDocument'],
        '0x0013': ['SQ', '1', 'SourceInstanceSequence'],
        '0x0014': ['LO', '1-n', 'ListOfMIMETypes']
    },
    '0x0044': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0001': ['ST', '1', 'ProductPackageIdentifier'],
        '0x0002': ['CS', '1', 'SubstanceAdministrationApproval'],
        '0x0003': ['LT', '1', 'ApprovalStatusFurtherDescription'],
        '0x0004': ['DT', '1', 'ApprovalStatusDateTime'],
        '0x0007': ['SQ', '1', 'ProductTypeCodeSequence'],
        '0x0008': ['LO', '1-n', 'ProductName'],
        '0x0009': ['LT', '1', 'ProductDescription'],
        '0x000A': ['LO', '1', 'ProductLotIdentifier'],
        '0x000B': ['DT', '1', 'ProductExpirationDateTime'],
        '0x0010': ['DT', '1', 'SubstanceAdministrationDateTime'],
        '0x0011': ['LO', '1', 'SubstanceAdministrationNotes'],
        '0x0012': ['LO', '1', 'SubstanceAdministrationDeviceID'],
        '0x0013': ['SQ', '1', 'ProductParameterSequence'],
        '0x0019': ['SQ', '1', 'SubstanceAdministrationParameterSequence']
    },
    '0x0046': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0012': ['LO', '1', 'LensDescription'],
        '0x0014': ['SQ', '1', 'RightLensSequence'],
        '0x0015': ['SQ', '1', 'LeftLensSequence'],
        '0x0016': ['SQ', '1', 'UnspecifiedLateralityLensSequence'],
        '0x0018': ['SQ', '1', 'CylinderSequence'],
        '0x0028': ['SQ', '1', 'PrismSequence'],
        '0x0030': ['FD', '1', 'HorizontalPrismPower'],
        '0x0032': ['CS', '1', 'HorizontalPrismBase'],
        '0x0034': ['FD', '1', 'VerticalPrismPower'],
        '0x0036': ['CS', '1', 'VerticalPrismBase'],
        '0x0038': ['CS', '1', 'LensSegmentType'],
        '0x0040': ['FD', '1', 'OpticalTransmittance'],
        '0x0042': ['FD', '1', 'ChannelWidth'],
        '0x0044': ['FD', '1', 'PupilSize'],
        '0x0046': ['FD', '1', 'CornealSize'],
        '0x0050': ['SQ', '1', 'AutorefractionRightEyeSequence'],
        '0x0052': ['SQ', '1', 'AutorefractionLeftEyeSequence'],
        '0x0060': ['FD', '1', 'DistancePupillaryDistance'],
        '0x0062': ['FD', '1', 'NearPupillaryDistance'],
        '0x0063': ['FD', '1', 'IntermediatePupillaryDistance'],
        '0x0064': ['FD', '1', 'OtherPupillaryDistance'],
        '0x0070': ['SQ', '1', 'KeratometryRightEyeSequence'],
        '0x0071': ['SQ', '1', 'KeratometryLeftEyeSequence'],
        '0x0074': ['SQ', '1', 'SteepKeratometricAxisSequence'],
        '0x0075': ['FD', '1', 'RadiusOfCurvature'],
        '0x0076': ['FD', '1', 'KeratometricPower'],
        '0x0077': ['FD', '1', 'KeratometricAxis'],
        '0x0080': ['SQ', '1', 'FlatKeratometricAxisSequence'],
        '0x0092': ['CS', '1', 'BackgroundColor'],
        '0x0094': ['CS', '1', 'Optotype'],
        '0x0095': ['CS', '1', 'OptotypePresentation'],
        '0x0097': ['SQ', '1', 'SubjectiveRefractionRightEyeSequence'],
        '0x0098': ['SQ', '1', 'SubjectiveRefractionLeftEyeSequence'],
        '0x0100': ['SQ', '1', 'AddNearSequence'],
        '0x0101': ['SQ', '1', 'AddIntermediateSequence'],
        '0x0102': ['SQ', '1', 'AddOtherSequence'],
        '0x0104': ['FD', '1', 'AddPower'],
        '0x0106': ['FD', '1', 'ViewingDistance'],
        '0x0121': ['SQ', '1', 'VisualAcuityTypeCodeSequence'],
        '0x0122': ['SQ', '1', 'VisualAcuityRightEyeSequence'],
        '0x0123': ['SQ', '1', 'VisualAcuityLeftEyeSequence'],
        '0x0124': ['SQ', '1', 'VisualAcuityBothEyesOpenSequence'],
        '0x0125': ['CS', '1', 'ViewingDistanceType'],
        '0x0135': ['SS', '2', 'VisualAcuityModifiers'],
        '0x0137': ['FD', '1', 'DecimalVisualAcuity'],
        '0x0139': ['LO', '1', 'OptotypeDetailedDefinition'],
        '0x0145': ['SQ', '1', 'ReferencedRefractiveMeasurementsSequence'],
        '0x0146': ['FD', '1', 'SpherePower'],
        '0x0147': ['FD', '1', 'CylinderPower'],
        '0x0201': ['CS', '1', 'CornealTopographySurface'],
        '0x0202': ['FL', '2', 'CornealVertexLocation'],
        '0x0203': ['FL', '1', 'PupilCentroidXCoordinate'],
        '0x0204': ['FL', '1', 'PupilCentroidYCoordinate'],
        '0x0205': ['FL', '1', 'EquivalentPupilRadius'],
        '0x0207': ['SQ', '1', 'CornealTopographyMapTypeCodeSequence'],
        '0x0208': ['IS', '2-2n', 'VerticesOfTheOutlineOfPupil'],
        '0x0210': ['SQ', '1', 'CornealTopographyMappingNormalsSequence'],
        '0x0211': ['SQ', '1', 'MaximumCornealCurvatureSequence'],
        '0x0212': ['FL', '1', 'MaximumCornealCurvature'],
        '0x0213': ['FL', '2', 'MaximumCornealCurvatureLocation'],
        '0x0215': ['SQ', '1', 'MinimumKeratometricSequence'],
        '0x0218': ['SQ', '1', 'SimulatedKeratometricCylinderSequence'],
        '0x0220': ['FL', '1', 'AverageCornealPower'],
        '0x0224': ['FL', '1', 'CornealISValue'],
        '0x0227': ['FL', '1', 'AnalyzedArea'],
        '0x0230': ['FL', '1', 'SurfaceRegularityIndex'],
        '0x0232': ['FL', '1', 'SurfaceAsymmetryIndex'],
        '0x0234': ['FL', '1', 'CornealEccentricityIndex'],
        '0x0236': ['FL', '1', 'KeratoconusPredictionIndex'],
        '0x0238': ['FL', '1', 'DecimalPotentialVisualAcuity'],
        '0x0242': ['CS', '1', 'CornealTopographyMapQualityEvaluation'],
        '0x0244': ['SQ', '1', 'SourceImageCornealProcessedDataSequence'],
        '0x0247': ['FL', '3', 'CornealPointLocation'],
        '0x0248': ['CS', '1', 'CornealPointEstimated'],
        '0x0249': ['FL', '1', 'AxialPower'],
        '0x0250': ['FL', '1', 'TangentialPower'],
        '0x0251': ['FL', '1', 'RefractivePower'],
        '0x0252': ['FL', '1', 'RelativeElevation'],
        '0x0253': ['FL', '1', 'CornealWavefront']
    },
    '0x0048': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0001': ['FL', '1', 'ImagedVolumeWidth'],
        '0x0002': ['FL', '1', 'ImagedVolumeHeight'],
        '0x0003': ['FL', '1', 'ImagedVolumeDepth'],
        '0x0006': ['UL', '1', 'TotalPixelMatrixColumns'],
        '0x0007': ['UL', '1', 'TotalPixelMatrixRows'],
        '0x0008': ['SQ', '1', 'TotalPixelMatrixOriginSequence'],
        '0x0010': ['CS', '1', 'SpecimenLabelInImage'],
        '0x0011': ['CS', '1', 'FocusMethod'],
        '0x0012': ['CS', '1', 'ExtendedDepthOfField'],
        '0x0013': ['US', '1', 'NumberOfFocalPlanes'],
        '0x0014': ['FL', '1', 'DistanceBetweenFocalPlanes'],
        '0x0015': ['US', '3', 'RecommendedAbsentPixelCIELabValue'],
        '0x0100': ['SQ', '1', 'IlluminatorTypeCodeSequence'],
        '0x0102': ['DS', '6', 'ImageOrientationSlide'],
        '0x0105': ['SQ', '1', 'OpticalPathSequence'],
        '0x0106': ['SH', '1', 'OpticalPathIdentifier'],
        '0x0107': ['ST', '1', 'OpticalPathDescription'],
        '0x0108': ['SQ', '1', 'IlluminationColorCodeSequence'],
        '0x0110': ['SQ', '1', 'SpecimenReferenceSequence'],
        '0x0111': ['DS', '1', 'CondenserLensPower'],
        '0x0112': ['DS', '1', 'ObjectiveLensPower'],
        '0x0113': ['DS', '1', 'ObjectiveLensNumericalAperture'],
        '0x0120': ['SQ', '1', 'PaletteColorLookupTableSequence'],
        '0x0200': ['SQ', '1', 'ReferencedImageNavigationSequence'],
        '0x0201': ['US', '2', 'TopLeftHandCornerOfLocalizerArea'],
        '0x0202': ['US', '2', 'BottomRightHandCornerOfLocalizerArea'],
        '0x0207': ['SQ', '1', 'OpticalPathIdentificationSequence'],
        '0x021A': ['SQ', '1', 'PlanePositionSlideSequence'],
        '0x021E': ['SL', '1', 'ColumnPositionInTotalImagePixelMatrix'],
        '0x021F': ['SL', '1', 'RowPositionInTotalImagePixelMatrix'],
        '0x0301': ['CS', '1', 'PixelOriginInterpretation']
    },
    '0x0050': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0004': ['CS', '1', 'CalibrationImage'],
        '0x0010': ['SQ', '1', 'DeviceSequence'],
        '0x0012': ['SQ', '1', 'ContainerComponentTypeCodeSequence'],
        '0x0013': ['FD', '1', 'ContainerComponentThickness'],
        '0x0014': ['DS', '1', 'DeviceLength'],
        '0x0015': ['FD', '1', 'ContainerComponentWidth'],
        '0x0016': ['DS', '1', 'DeviceDiameter'],
        '0x0017': ['CS', '1', 'DeviceDiameterUnits'],
        '0x0018': ['DS', '1', 'DeviceVolume'],
        '0x0019': ['DS', '1', 'InterMarkerDistance'],
        '0x001A': ['CS', '1', 'ContainerComponentMaterial'],
        '0x001B': ['LO', '1', 'ContainerComponentID'],
        '0x001C': ['FD', '1', 'ContainerComponentLength'],
        '0x001D': ['FD', '1', 'ContainerComponentDiameter'],
        '0x001E': ['LO', '1', 'ContainerComponentDescription'],
        '0x0020': ['LO', '1', 'DeviceDescription']
    },
    '0x0052': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0001': ['FL', '1', 'ContrastBolusIngredientPercentByVolume'],
        '0x0002': ['FD', '1', 'OCTFocalDistance'],
        '0x0003': ['FD', '1', 'BeamSpotSize'],
        '0x0004': ['FD', '1', 'EffectiveRefractiveIndex'],
        '0x0006': ['CS', '1', 'OCTAcquisitionDomain'],
        '0x0007': ['FD', '1', 'OCTOpticalCenterWavelength'],
        '0x0008': ['FD', '1', 'AxialResolution'],
        '0x0009': ['FD', '1', 'RangingDepth'],
        '0x0011': ['FD', '1', 'ALineRate'],
        '0x0012': ['US', '1', 'ALinesPerFrame'],
        '0x0013': ['FD', '1', 'CatheterRotationalRate'],
        '0x0014': ['FD', '1', 'ALinePixelSpacing'],
        '0x0016': ['SQ', '1', 'ModeOfPercutaneousAccessSequence'],
        '0x0025': ['SQ', '1', 'IntravascularOCTFrameTypeSequence'],
        '0x0026': ['CS', '1', 'OCTZOffsetApplied'],
        '0x0027': ['SQ', '1', 'IntravascularFrameContentSequence'],
        '0x0028': ['FD', '1', 'IntravascularLongitudinalDistance'],
        '0x0029': ['SQ', '1', 'IntravascularOCTFrameContentSequence'],
        '0x0030': ['SS', '1', 'OCTZOffsetCorrection'],
        '0x0031': ['CS', '1', 'CatheterDirectionOfRotation'],
        '0x0033': ['FD', '1', 'SeamLineLocation'],
        '0x0034': ['FD', '1', 'FirstALineLocation'],
        '0x0036': ['US', '1', 'SeamLineIndex'],
        '0x0038': ['US', '1', 'NumberOfPaddedALines'],
        '0x0039': ['CS', '1', 'InterpolationType'],
        '0x003A': ['CS', '1', 'RefractiveIndexApplied']
    },
    '0x0054': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0010': ['US', '1-n', 'EnergyWindowVector'],
        '0x0011': ['US', '1', 'NumberOfEnergyWindows'],
        '0x0012': ['SQ', '1', 'EnergyWindowInformationSequence'],
        '0x0013': ['SQ', '1', 'EnergyWindowRangeSequence'],
        '0x0014': ['DS', '1', 'EnergyWindowLowerLimit'],
        '0x0015': ['DS', '1', 'EnergyWindowUpperLimit'],
        '0x0016': ['SQ', '1', 'RadiopharmaceuticalInformationSequence'],
        '0x0017': ['IS', '1', 'ResidualSyringeCounts'],
        '0x0018': ['SH', '1', 'EnergyWindowName'],
        '0x0020': ['US', '1-n', 'DetectorVector'],
        '0x0021': ['US', '1', 'NumberOfDetectors'],
        '0x0022': ['SQ', '1', 'DetectorInformationSequence'],
        '0x0030': ['US', '1-n', 'PhaseVector'],
        '0x0031': ['US', '1', 'NumberOfPhases'],
        '0x0032': ['SQ', '1', 'PhaseInformationSequence'],
        '0x0033': ['US', '1', 'NumberOfFramesInPhase'],
        '0x0036': ['IS', '1', 'PhaseDelay'],
        '0x0038': ['IS', '1', 'PauseBetweenFrames'],
        '0x0039': ['CS', '1', 'PhaseDescription'],
        '0x0050': ['US', '1-n', 'RotationVector'],
        '0x0051': ['US', '1', 'NumberOfRotations'],
        '0x0052': ['SQ', '1', 'RotationInformationSequence'],
        '0x0053': ['US', '1', 'NumberOfFramesInRotation'],
        '0x0060': ['US', '1-n', 'RRIntervalVector'],
        '0x0061': ['US', '1', 'NumberOfRRIntervals'],
        '0x0062': ['SQ', '1', 'GatedInformationSequence'],
        '0x0063': ['SQ', '1', 'DataInformationSequence'],
        '0x0070': ['US', '1-n', 'TimeSlotVector'],
        '0x0071': ['US', '1', 'NumberOfTimeSlots'],
        '0x0072': ['SQ', '1', 'TimeSlotInformationSequence'],
        '0x0073': ['DS', '1', 'TimeSlotTime'],
        '0x0080': ['US', '1-n', 'SliceVector'],
        '0x0081': ['US', '1', 'NumberOfSlices'],
        '0x0090': ['US', '1-n', 'AngularViewVector'],
        '0x0100': ['US', '1-n', 'TimeSliceVector'],
        '0x0101': ['US', '1', 'NumberOfTimeSlices'],
        '0x0200': ['DS', '1', 'StartAngle'],
        '0x0202': ['CS', '1', 'TypeOfDetectorMotion'],
        '0x0210': ['IS', '1-n', 'TriggerVector'],
        '0x0211': ['US', '1', 'NumberOfTriggersInPhase'],
        '0x0220': ['SQ', '1', 'ViewCodeSequence'],
        '0x0222': ['SQ', '1', 'ViewModifierCodeSequence'],
        '0x0300': ['SQ', '1', 'RadionuclideCodeSequence'],
        '0x0302': ['SQ', '1', 'AdministrationRouteCodeSequence'],
        '0x0304': ['SQ', '1', 'RadiopharmaceuticalCodeSequence'],
        '0x0306': ['SQ', '1', 'CalibrationDataSequence'],
        '0x0308': ['US', '1', 'EnergyWindowNumber'],
        '0x0400': ['SH', '1', 'ImageID'],
        '0x0410': ['SQ', '1', 'PatientOrientationCodeSequence'],
        '0x0412': ['SQ', '1', 'PatientOrientationModifierCodeSequence'],
        '0x0414': ['SQ', '1', 'PatientGantryRelationshipCodeSequence'],
        '0x0500': ['CS', '1', 'SliceProgressionDirection'],
        '0x0501': ['CS', '1', 'ScanProgressionDirection'],
        '0x1000': ['CS', '2', 'SeriesType'],
        '0x1001': ['CS', '1', 'Units'],
        '0x1002': ['CS', '1', 'CountsSource'],
        '0x1004': ['CS', '1', 'ReprojectionMethod'],
        '0x1006': ['CS', '1', 'SUVType'],
        '0x1100': ['CS', '1', 'RandomsCorrectionMethod'],
        '0x1101': ['LO', '1', 'AttenuationCorrectionMethod'],
        '0x1102': ['CS', '1', 'DecayCorrection'],
        '0x1103': ['LO', '1', 'ReconstructionMethod'],
        '0x1104': ['LO', '1', 'DetectorLinesOfResponseUsed'],
        '0x1105': ['LO', '1', 'ScatterCorrectionMethod'],
        '0x1200': ['DS', '1', 'AxialAcceptance'],
        '0x1201': ['IS', '2', 'AxialMash'],
        '0x1202': ['IS', '1', 'TransverseMash'],
        '0x1203': ['DS', '2', 'DetectorElementSize'],
        '0x1210': ['DS', '1', 'CoincidenceWindowWidth'],
        '0x1220': ['CS', '1-n', 'SecondaryCountsType'],
        '0x1300': ['DS', '1', 'FrameReferenceTime'],
        '0x1310': ['IS', '1', 'PrimaryPromptsCountsAccumulated'],
        '0x1311': ['IS', '1-n', 'SecondaryCountsAccumulated'],
        '0x1320': ['DS', '1', 'SliceSensitivityFactor'],
        '0x1321': ['DS', '1', 'DecayFactor'],
        '0x1322': ['DS', '1', 'DoseCalibrationFactor'],
        '0x1323': ['DS', '1', 'ScatterFractionFactor'],
        '0x1324': ['DS', '1', 'DeadTimeFactor'],
        '0x1330': ['US', '1', 'ImageIndex'],
        '0x1400': ['CS', '1-n', 'CountsIncluded'],
        '0x1401': ['CS', '1', 'DeadTimeCorrectionFlag']
    },
    '0x0060': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x3000': ['SQ', '1', 'HistogramSequence'],
        '0x3002': ['US', '1', 'HistogramNumberOfBins'],
        '0x3004': ['xs', '1', 'HistogramFirstBinValue'],
        '0x3006': ['xs', '1', 'HistogramLastBinValue'],
        '0x3008': ['US', '1', 'HistogramBinWidth'],
        '0x3010': ['LO', '1', 'HistogramExplanation'],
        '0x3020': ['UL', '1-n', 'HistogramData']
    },
    '0x0062': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0001': ['CS', '1', 'SegmentationType'],
        '0x0002': ['SQ', '1', 'SegmentSequence'],
        '0x0003': ['SQ', '1', 'SegmentedPropertyCategoryCodeSequence'],
        '0x0004': ['US', '1', 'SegmentNumber'],
        '0x0005': ['LO', '1', 'SegmentLabel'],
        '0x0006': ['ST', '1', 'SegmentDescription'],
        '0x0008': ['CS', '1', 'SegmentAlgorithmType'],
        '0x0009': ['LO', '1', 'SegmentAlgorithmName'],
        '0x000A': ['SQ', '1', 'SegmentIdentificationSequence'],
        '0x000B': ['US', '1-n', 'ReferencedSegmentNumber'],
        '0x000C': ['US', '1', 'RecommendedDisplayGrayscaleValue'],
        '0x000D': ['US', '3', 'RecommendedDisplayCIELabValue'],
        '0x000E': ['US', '1', 'MaximumFractionalValue'],
        '0x000F': ['SQ', '1', 'SegmentedPropertyTypeCodeSequence'],
        '0x0010': ['CS', '1', 'SegmentationFractionalType'],
        '0x0011': ['SQ', '1', 'SegmentedPropertyTypeModifierCodeSequence'],
        '0x0012': ['SQ', '1', 'UsedSegmentsSequence']
    },
    '0x0064': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0002': ['SQ', '1', 'DeformableRegistrationSequence'],
        '0x0003': ['UI', '1', 'SourceFrameOfReferenceUID'],
        '0x0005': ['SQ', '1', 'DeformableRegistrationGridSequence'],
        '0x0007': ['UL', '3', 'GridDimensions'],
        '0x0008': ['FD', '3', 'GridResolution'],
        '0x0009': ['OF', '1', 'VectorGridData'],
        '0x000F': ['SQ', '1', 'PreDeformationMatrixRegistrationSequence'],
        '0x0010': ['SQ', '1', 'PostDeformationMatrixRegistrationSequence']
    },
    '0x0066': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0001': ['UL', '1', 'NumberOfSurfaces'],
        '0x0002': ['SQ', '1', 'SurfaceSequence'],
        '0x0003': ['UL', '1', 'SurfaceNumber'],
        '0x0004': ['LT', '1', 'SurfaceComments'],
        '0x0009': ['CS', '1', 'SurfaceProcessing'],
        '0x000A': ['FL', '1', 'SurfaceProcessingRatio'],
        '0x000B': ['LO', '1', 'SurfaceProcessingDescription'],
        '0x000C': ['FL', '1', 'RecommendedPresentationOpacity'],
        '0x000D': ['CS', '1', 'RecommendedPresentationType'],
        '0x000E': ['CS', '1', 'FiniteVolume'],
        '0x0010': ['CS', '1', 'Manifold'],
        '0x0011': ['SQ', '1', 'SurfacePointsSequence'],
        '0x0012': ['SQ', '1', 'SurfacePointsNormalsSequence'],
        '0x0013': ['SQ', '1', 'SurfaceMeshPrimitivesSequence'],
        '0x0015': ['UL', '1', 'NumberOfSurfacePoints'],
        '0x0016': ['OF', '1', 'PointCoordinatesData'],
        '0x0017': ['FL', '3', 'PointPositionAccuracy'],
        '0x0018': ['FL', '1', 'MeanPointDistance'],
        '0x0019': ['FL', '1', 'MaximumPointDistance'],
        '0x001A': ['FL', '6', 'PointsBoundingBoxCoordinates'],
        '0x001B': ['FL', '3', 'AxisOfRotation'],
        '0x001C': ['FL', '3', 'CenterOfRotation'],
        '0x001E': ['UL', '1', 'NumberOfVectors'],
        '0x001F': ['US', '1', 'VectorDimensionality'],
        '0x0020': ['FL', '1-n', 'VectorAccuracy'],
        '0x0021': ['OF', '1', 'VectorCoordinateData'],
        '0x0023': ['OW', '1', 'TrianglePointIndexList'],
        '0x0024': ['OW', '1', 'EdgePointIndexList'],
        '0x0025': ['OW', '1', 'VertexPointIndexList'],
        '0x0026': ['SQ', '1', 'TriangleStripSequence'],
        '0x0027': ['SQ', '1', 'TriangleFanSequence'],
        '0x0028': ['SQ', '1', 'LineSequence'],
        '0x0029': ['OW', '1', 'PrimitivePointIndexList'],
        '0x002A': ['UL', '1', 'SurfaceCount'],
        '0x002B': ['SQ', '1', 'ReferencedSurfaceSequence'],
        '0x002C': ['UL', '1', 'ReferencedSurfaceNumber'],
        '0x002D': ['SQ', '1', 'SegmentSurfaceGenerationAlgorithmIdentificationSequence'],
        '0x002E': ['SQ', '1', 'SegmentSurfaceSourceInstanceSequence'],
        '0x002F': ['SQ', '1', 'AlgorithmFamilyCodeSequence'],
        '0x0030': ['SQ', '1', 'AlgorithmNameCodeSequence'],
        '0x0031': ['LO', '1', 'AlgorithmVersion'],
        '0x0032': ['LT', '1', 'AlgorithmParameters'],
        '0x0034': ['SQ', '1', 'FacetSequence'],
        '0x0035': ['SQ', '1', 'SurfaceProcessingAlgorithmIdentificationSequence'],
        '0x0036': ['LO', '1', 'AlgorithmName'],
        '0x0037': ['FL', '1', 'RecommendedPointRadius'],
        '0x0038': ['FL', '1', 'RecommendedLineThickness'],
        '0x0040': ['UL', '1-n', 'LongPrimitivePointIndexList'],
        '0x0041': ['UL', '3-3n', 'LongTrianglePointIndexList'],
        '0x0042': ['UL', '2-2n', 'LongEdgePointIndexList'],
        '0x0043': ['UL', '1-n', 'LongVertexPointIndexList']
    },
    '0x0068': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x6210': ['LO', '1', 'ImplantSize'],
        '0x6221': ['LO', '1', 'ImplantTemplateVersion'],
        '0x6222': ['SQ', '1', 'ReplacedImplantTemplateSequence'],
        '0x6223': ['CS', '1', 'ImplantType'],
        '0x6224': ['SQ', '1', 'DerivationImplantTemplateSequence'],
        '0x6225': ['SQ', '1', 'OriginalImplantTemplateSequence'],
        '0x6226': ['DT', '1', 'EffectiveDateTime'],
        '0x6230': ['SQ', '1', 'ImplantTargetAnatomySequence'],
        '0x6260': ['SQ', '1', 'InformationFromManufacturerSequence'],
        '0x6265': ['SQ', '1', 'NotificationFromManufacturerSequence'],
        '0x6270': ['DT', '1', 'InformationIssueDateTime'],
        '0x6280': ['ST', '1', 'InformationSummary'],
        '0x62A0': ['SQ', '1', 'ImplantRegulatoryDisapprovalCodeSequence'],
        '0x62A5': ['FD', '1', 'OverallTemplateSpatialTolerance'],
        '0x62C0': ['SQ', '1', 'HPGLDocumentSequence'],
        '0x62D0': ['US', '1', 'HPGLDocumentID'],
        '0x62D5': ['LO', '1', 'HPGLDocumentLabel'],
        '0x62E0': ['SQ', '1', 'ViewOrientationCodeSequence'],
        '0x62F0': ['FD', '9', 'ViewOrientationModifier'],
        '0x62F2': ['FD', '1', 'HPGLDocumentScaling'],
        '0x6300': ['OB', '1', 'HPGLDocument'],
        '0x6310': ['US', '1', 'HPGLContourPenNumber'],
        '0x6320': ['SQ', '1', 'HPGLPenSequence'],
        '0x6330': ['US', '1', 'HPGLPenNumber'],
        '0x6340': ['LO', '1', 'HPGLPenLabel'],
        '0x6345': ['ST', '1', 'HPGLPenDescription'],
        '0x6346': ['FD', '2', 'RecommendedRotationPoint'],
        '0x6347': ['FD', '4', 'BoundingRectangle'],
        '0x6350': ['US', '1-n', 'ImplantTemplate3DModelSurfaceNumber'],
        '0x6360': ['SQ', '1', 'SurfaceModelDescriptionSequence'],
        '0x6380': ['LO', '1', 'SurfaceModelLabel'],
        '0x6390': ['FD', '1', 'SurfaceModelScalingFactor'],
        '0x63A0': ['SQ', '1', 'MaterialsCodeSequence'],
        '0x63A4': ['SQ', '1', 'CoatingMaterialsCodeSequence'],
        '0x63A8': ['SQ', '1', 'ImplantTypeCodeSequence'],
        '0x63AC': ['SQ', '1', 'FixationMethodCodeSequence'],
        '0x63B0': ['SQ', '1', 'MatingFeatureSetsSequence'],
        '0x63C0': ['US', '1', 'MatingFeatureSetID'],
        '0x63D0': ['LO', '1', 'MatingFeatureSetLabel'],
        '0x63E0': ['SQ', '1', 'MatingFeatureSequence'],
        '0x63F0': ['US', '1', 'MatingFeatureID'],
        '0x6400': ['SQ', '1', 'MatingFeatureDegreeOfFreedomSequence'],
        '0x6410': ['US', '1', 'DegreeOfFreedomID'],
        '0x6420': ['CS', '1', 'DegreeOfFreedomType'],
        '0x6430': ['SQ', '1', 'TwoDMatingFeatureCoordinatesSequence'],
        '0x6440': ['US', '1', 'ReferencedHPGLDocumentID'],
        '0x6450': ['FD', '2', 'TwoDMatingPoint'],
        '0x6460': ['FD', '4', 'TwoDMatingAxes'],
        '0x6470': ['SQ', '1', 'TwoDDegreeOfFreedomSequence'],
        '0x6490': ['FD', '3', 'ThreeDDegreeOfFreedomAxis'],
        '0x64A0': ['FD', '2', 'RangeOfFreedom'],
        '0x64C0': ['FD', '3', 'ThreeDMatingPoint'],
        '0x64D0': ['FD', '9', 'ThreeDMatingAxes'],
        '0x64F0': ['FD', '3', 'TwoDDegreeOfFreedomAxis'],
        '0x6500': ['SQ', '1', 'PlanningLandmarkPointSequence'],
        '0x6510': ['SQ', '1', 'PlanningLandmarkLineSequence'],
        '0x6520': ['SQ', '1', 'PlanningLandmarkPlaneSequence'],
        '0x6530': ['US', '1', 'PlanningLandmarkID'],
        '0x6540': ['LO', '1', 'PlanningLandmarkDescription'],
        '0x6545': ['SQ', '1', 'PlanningLandmarkIdentificationCodeSequence'],
        '0x6550': ['SQ', '1', 'TwoDPointCoordinatesSequence'],
        '0x6560': ['FD', '2', 'TwoDPointCoordinates'],
        '0x6590': ['FD', '3', 'ThreeDPointCoordinates'],
        '0x65A0': ['SQ', '1', 'TwoDLineCoordinatesSequence'],
        '0x65B0': ['FD', '4', 'TwoDLineCoordinates'],
        '0x65D0': ['FD', '6', 'ThreeDLineCoordinates'],
        '0x65E0': ['SQ', '1', 'TwoDPlaneCoordinatesSequence'],
        '0x65F0': ['FD', '4', 'TwoDPlaneIntersection'],
        '0x6610': ['FD', '3', 'ThreeDPlaneOrigin'],
        '0x6620': ['FD', '3', 'ThreeDPlaneNormal']
    },
    '0x0070': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0001': ['SQ', '1', 'GraphicAnnotationSequence'],
        '0x0002': ['CS', '1', 'GraphicLayer'],
        '0x0003': ['CS', '1', 'BoundingBoxAnnotationUnits'],
        '0x0004': ['CS', '1', 'AnchorPointAnnotationUnits'],
        '0x0005': ['CS', '1', 'GraphicAnnotationUnits'],
        '0x0006': ['ST', '1', 'UnformattedTextValue'],
        '0x0008': ['SQ', '1', 'TextObjectSequence'],
        '0x0009': ['SQ', '1', 'GraphicObjectSequence'],
        '0x0010': ['FL', '2', 'BoundingBoxTopLeftHandCorner'],
        '0x0011': ['FL', '2', 'BoundingBoxBottomRightHandCorner'],
        '0x0012': ['CS', '1', 'BoundingBoxTextHorizontalJustification'],
        '0x0014': ['FL', '2', 'AnchorPoint'],
        '0x0015': ['CS', '1', 'AnchorPointVisibility'],
        '0x0020': ['US', '1', 'GraphicDimensions'],
        '0x0021': ['US', '1', 'NumberOfGraphicPoints'],
        '0x0022': ['FL', '2-n', 'GraphicData'],
        '0x0023': ['CS', '1', 'GraphicType'],
        '0x0024': ['CS', '1', 'GraphicFilled'],
        '0x0040': ['IS', '1', 'ImageRotationRetired'],
        '0x0041': ['CS', '1', 'ImageHorizontalFlip'],
        '0x0042': ['US', '1', 'ImageRotation'],
        '0x0050': ['US', '2', 'DisplayedAreaTopLeftHandCornerTrial'],
        '0x0051': ['US', '2', 'DisplayedAreaBottomRightHandCornerTrial'],
        '0x0052': ['SL', '2', 'DisplayedAreaTopLeftHandCorner'],
        '0x0053': ['SL', '2', 'DisplayedAreaBottomRightHandCorner'],
        '0x005A': ['SQ', '1', 'DisplayedAreaSelectionSequence'],
        '0x0060': ['SQ', '1', 'GraphicLayerSequence'],
        '0x0062': ['IS', '1', 'GraphicLayerOrder'],
        '0x0066': ['US', '1', 'GraphicLayerRecommendedDisplayGrayscaleValue'],
        '0x0067': ['US', '3', 'GraphicLayerRecommendedDisplayRGBValue'],
        '0x0068': ['LO', '1', 'GraphicLayerDescription'],
        '0x0080': ['CS', '1', 'ContentLabel'],
        '0x0081': ['LO', '1', 'ContentDescription'],
        '0x0082': ['DA', '1', 'PresentationCreationDate'],
        '0x0083': ['TM', '1', 'PresentationCreationTime'],
        '0x0084': ['PN', '1', 'ContentCreatorName'],
        '0x0086': ['SQ', '1', 'ContentCreatorIdentificationCodeSequence'],
        '0x0087': ['SQ', '1', 'AlternateContentDescriptionSequence'],
        '0x0100': ['CS', '1', 'PresentationSizeMode'],
        '0x0101': ['DS', '2', 'PresentationPixelSpacing'],
        '0x0102': ['IS', '2', 'PresentationPixelAspectRatio'],
        '0x0103': ['FL', '1', 'PresentationPixelMagnificationRatio'],
        '0x0207': ['LO', '1', 'GraphicGroupLabel'],
        '0x0208': ['ST', '1', 'GraphicGroupDescription'],
        '0x0209': ['SQ', '1', 'CompoundGraphicSequence'],
        '0x0226': ['UL', '1', 'CompoundGraphicInstanceID'],
        '0x0227': ['LO', '1', 'FontName'],
        '0x0228': ['CS', '1', 'FontNameType'],
        '0x0229': ['LO', '1', 'CSSFontName'],
        '0x0230': ['FD', '1', 'RotationAngle'],
        '0x0231': ['SQ', '1', 'TextStyleSequence'],
        '0x0232': ['SQ', '1', 'LineStyleSequence'],
        '0x0233': ['SQ', '1', 'FillStyleSequence'],
        '0x0234': ['SQ', '1', 'GraphicGroupSequence'],
        '0x0241': ['US', '3', 'TextColorCIELabValue'],
        '0x0242': ['CS', '1', 'HorizontalAlignment'],
        '0x0243': ['CS', '1', 'VerticalAlignment'],
        '0x0244': ['CS', '1', 'ShadowStyle'],
        '0x0245': ['FL', '1', 'ShadowOffsetX'],
        '0x0246': ['FL', '1', 'ShadowOffsetY'],
        '0x0247': ['US', '3', 'ShadowColorCIELabValue'],
        '0x0248': ['CS', '1', 'Underlined'],
        '0x0249': ['CS', '1', 'Bold'],
        '0x0250': ['CS', '1', 'Italic'],
        '0x0251': ['US', '3', 'PatternOnColorCIELabValue'],
        '0x0252': ['US', '3', 'PatternOffColorCIELabValue'],
        '0x0253': ['FL', '1', 'LineThickness'],
        '0x0254': ['CS', '1', 'LineDashingStyle'],
        '0x0255': ['UL', '1', 'LinePattern'],
        '0x0256': ['OB', '1', 'FillPattern'],
        '0x0257': ['CS', '1', 'FillMode'],
        '0x0258': ['FL', '1', 'ShadowOpacity'],
        '0x0261': ['FL', '1', 'GapLength'],
        '0x0262': ['FL', '1', 'DiameterOfVisibility'],
        '0x0273': ['FL', '2', 'RotationPoint'],
        '0x0274': ['CS', '1', 'TickAlignment'],
        '0x0278': ['CS', '1', 'ShowTickLabel'],
        '0x0279': ['CS', '1', 'TickLabelAlignment'],
        '0x0282': ['CS', '1', 'CompoundGraphicUnits'],
        '0x0284': ['FL', '1', 'PatternOnOpacity'],
        '0x0285': ['FL', '1', 'PatternOffOpacity'],
        '0x0287': ['SQ', '1', 'MajorTicksSequence'],
        '0x0288': ['FL', '1', 'TickPosition'],
        '0x0289': ['SH', '1', 'TickLabel'],
        '0x0294': ['CS', '1', 'CompoundGraphicType'],
        '0x0295': ['UL', '1', 'GraphicGroupID'],
        '0x0306': ['CS', '1', 'ShapeType'],
        '0x0308': ['SQ', '1', 'RegistrationSequence'],
        '0x0309': ['SQ', '1', 'MatrixRegistrationSequence'],
        '0x030A': ['SQ', '1', 'MatrixSequence'],
        '0x030C': ['CS', '1', 'FrameOfReferenceTransformationMatrixType'],
        '0x030D': ['SQ', '1', 'RegistrationTypeCodeSequence'],
        '0x030F': ['ST', '1', 'FiducialDescription'],
        '0x0310': ['SH', '1', 'FiducialIdentifier'],
        '0x0311': ['SQ', '1', 'FiducialIdentifierCodeSequence'],
        '0x0312': ['FD', '1', 'ContourUncertaintyRadius'],
        '0x0314': ['SQ', '1', 'UsedFiducialsSequence'],
        '0x0318': ['SQ', '1', 'GraphicCoordinatesDataSequence'],
        '0x031A': ['UI', '1', 'FiducialUID'],
        '0x031C': ['SQ', '1', 'FiducialSetSequence'],
        '0x031E': ['SQ', '1', 'FiducialSequence'],
        '0x0401': ['US', '3', 'GraphicLayerRecommendedDisplayCIELabValue'],
        '0x0402': ['SQ', '1', 'BlendingSequence'],
        '0x0403': ['FL', '1', 'RelativeOpacity'],
        '0x0404': ['SQ', '1', 'ReferencedSpatialRegistrationSequence'],
        '0x0405': ['CS', '1', 'BlendingPosition']
    },
    '0x0072': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0002': ['SH', '1', 'HangingProtocolName'],
        '0x0004': ['LO', '1', 'HangingProtocolDescription'],
        '0x0006': ['CS', '1', 'HangingProtocolLevel'],
        '0x0008': ['LO', '1', 'HangingProtocolCreator'],
        '0x000A': ['DT', '1', 'HangingProtocolCreationDateTime'],
        '0x000C': ['SQ', '1', 'HangingProtocolDefinitionSequence'],
        '0x000E': ['SQ', '1', 'HangingProtocolUserIdentificationCodeSequence'],
        '0x0010': ['LO', '1', 'HangingProtocolUserGroupName'],
        '0x0012': ['SQ', '1', 'SourceHangingProtocolSequence'],
        '0x0014': ['US', '1', 'NumberOfPriorsReferenced'],
        '0x0020': ['SQ', '1', 'ImageSetsSequence'],
        '0x0022': ['SQ', '1', 'ImageSetSelectorSequence'],
        '0x0024': ['CS', '1', 'ImageSetSelectorUsageFlag'],
        '0x0026': ['AT', '1', 'SelectorAttribute'],
        '0x0028': ['US', '1', 'SelectorValueNumber'],
        '0x0030': ['SQ', '1', 'TimeBasedImageSetsSequence'],
        '0x0032': ['US', '1', 'ImageSetNumber'],
        '0x0034': ['CS', '1', 'ImageSetSelectorCategory'],
        '0x0038': ['US', '2', 'RelativeTime'],
        '0x003A': ['CS', '1', 'RelativeTimeUnits'],
        '0x003C': ['SS', '2', 'AbstractPriorValue'],
        '0x003E': ['SQ', '1', 'AbstractPriorCodeSequence'],
        '0x0040': ['LO', '1', 'ImageSetLabel'],
        '0x0050': ['CS', '1', 'SelectorAttributeVR'],
        '0x0052': ['AT', '1-n', 'SelectorSequencePointer'],
        '0x0054': ['LO', '1-n', 'SelectorSequencePointerPrivateCreator'],
        '0x0056': ['LO', '1', 'SelectorAttributePrivateCreator'],
        '0x0060': ['AT', '1-n', 'SelectorATValue'],
        '0x0062': ['CS', '1-n', 'SelectorCSValue'],
        '0x0064': ['IS', '1-n', 'SelectorISValue'],
        '0x0066': ['LO', '1-n', 'SelectorLOValue'],
        '0x0068': ['LT', '1', 'SelectorLTValue'],
        '0x006A': ['PN', '1-n', 'SelectorPNValue'],
        '0x006C': ['SH', '1-n', 'SelectorSHValue'],
        '0x006E': ['ST', '1', 'SelectorSTValue'],
        '0x0070': ['UT', '1', 'SelectorUTValue'],
        '0x0072': ['DS', '1-n', 'SelectorDSValue'],
        '0x0074': ['FD', '1-n', 'SelectorFDValue'],
        '0x0076': ['FL', '1-n', 'SelectorFLValue'],
        '0x0078': ['UL', '1-n', 'SelectorULValue'],
        '0x007A': ['US', '1-n', 'SelectorUSValue'],
        '0x007C': ['SL', '1-n', 'SelectorSLValue'],
        '0x007E': ['SS', '1-n', 'SelectorSSValue'],
        '0x007F': ['UI', '1-n', 'SelectorUIValue'],
        '0x0080': ['SQ', '1', 'SelectorCodeSequenceValue'],
        '0x0100': ['US', '1', 'NumberOfScreens'],
        '0x0102': ['SQ', '1', 'NominalScreenDefinitionSequence'],
        '0x0104': ['US', '1', 'NumberOfVerticalPixels'],
        '0x0106': ['US', '1', 'NumberOfHorizontalPixels'],
        '0x0108': ['FD', '4', 'DisplayEnvironmentSpatialPosition'],
        '0x010A': ['US', '1', 'ScreenMinimumGrayscaleBitDepth'],
        '0x010C': ['US', '1', 'ScreenMinimumColorBitDepth'],
        '0x010E': ['US', '1', 'ApplicationMaximumRepaintTime'],
        '0x0200': ['SQ', '1', 'DisplaySetsSequence'],
        '0x0202': ['US', '1', 'DisplaySetNumber'],
        '0x0203': ['LO', '1', 'DisplaySetLabel'],
        '0x0204': ['US', '1', 'DisplaySetPresentationGroup'],
        '0x0206': ['LO', '1', 'DisplaySetPresentationGroupDescription'],
        '0x0208': ['CS', '1', 'PartialDataDisplayHandling'],
        '0x0210': ['SQ', '1', 'SynchronizedScrollingSequence'],
        '0x0212': ['US', '2-n', 'DisplaySetScrollingGroup'],
        '0x0214': ['SQ', '1', 'NavigationIndicatorSequence'],
        '0x0216': ['US', '1', 'NavigationDisplaySet'],
        '0x0218': ['US', '1-n', 'ReferenceDisplaySets'],
        '0x0300': ['SQ', '1', 'ImageBoxesSequence'],
        '0x0302': ['US', '1', 'ImageBoxNumber'],
        '0x0304': ['CS', '1', 'ImageBoxLayoutType'],
        '0x0306': ['US', '1', 'ImageBoxTileHorizontalDimension'],
        '0x0308': ['US', '1', 'ImageBoxTileVerticalDimension'],
        '0x0310': ['CS', '1', 'ImageBoxScrollDirection'],
        '0x0312': ['CS', '1', 'ImageBoxSmallScrollType'],
        '0x0314': ['US', '1', 'ImageBoxSmallScrollAmount'],
        '0x0316': ['CS', '1', 'ImageBoxLargeScrollType'],
        '0x0318': ['US', '1', 'ImageBoxLargeScrollAmount'],
        '0x0320': ['US', '1', 'ImageBoxOverlapPriority'],
        '0x0330': ['FD', '1', 'CineRelativeToRealTime'],
        '0x0400': ['SQ', '1', 'FilterOperationsSequence'],
        '0x0402': ['CS', '1', 'FilterByCategory'],
        '0x0404': ['CS', '1', 'FilterByAttributePresence'],
        '0x0406': ['CS', '1', 'FilterByOperator'],
        '0x0420': ['US', '3', 'StructuredDisplayBackgroundCIELabValue'],
        '0x0421': ['US', '3', 'EmptyImageBoxCIELabValue'],
        '0x0422': ['SQ', '1', 'StructuredDisplayImageBoxSequence'],
        '0x0424': ['SQ', '1', 'StructuredDisplayTextBoxSequence'],
        '0x0427': ['SQ', '1', 'ReferencedFirstFrameSequence'],
        '0x0430': ['SQ', '1', 'ImageBoxSynchronizationSequence'],
        '0x0432': ['US', '2-n', 'SynchronizedImageBoxList'],
        '0x0434': ['CS', '1', 'TypeOfSynchronization'],
        '0x0500': ['CS', '1', 'BlendingOperationType'],
        '0x0510': ['CS', '1', 'ReformattingOperationType'],
        '0x0512': ['FD', '1', 'ReformattingThickness'],
        '0x0514': ['FD', '1', 'ReformattingInterval'],
        '0x0516': ['CS', '1', 'ReformattingOperationInitialViewDirection'],
        '0x0520': ['CS', '1-n', 'ThreeDRenderingType'],
        '0x0600': ['SQ', '1', 'SortingOperationsSequence'],
        '0x0602': ['CS', '1', 'SortByCategory'],
        '0x0604': ['CS', '1', 'SortingDirection'],
        '0x0700': ['CS', '2', 'DisplaySetPatientOrientation'],
        '0x0702': ['CS', '1', 'VOIType'],
        '0x0704': ['CS', '1', 'PseudoColorType'],
        '0x0705': ['SQ', '1', 'PseudoColorPaletteInstanceReferenceSequence'],
        '0x0706': ['CS', '1', 'ShowGrayscaleInverted'],
        '0x0710': ['CS', '1', 'ShowImageTrueSizeFlag'],
        '0x0712': ['CS', '1', 'ShowGraphicAnnotationFlag'],
        '0x0714': ['CS', '1', 'ShowPatientDemographicsFlag'],
        '0x0716': ['CS', '1', 'ShowAcquisitionTechniquesFlag'],
        '0x0717': ['CS', '1', 'DisplaySetHorizontalJustification'],
        '0x0718': ['CS', '1', 'DisplaySetVerticalJustification']
    },
    '0x0074': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0120': ['FD', '1', 'ContinuationStartMeterset'],
        '0x0121': ['FD', '1', 'ContinuationEndMeterset'],
        '0x1000': ['CS', '1', 'ProcedureStepState'],
        '0x1002': ['SQ', '1', 'ProcedureStepProgressInformationSequence'],
        '0x1004': ['DS', '1', 'ProcedureStepProgress'],
        '0x1006': ['ST', '1', 'ProcedureStepProgressDescription'],
        '0x1008': ['SQ', '1', 'ProcedureStepCommunicationsURISequence'],
        '0x100A': ['UR', '1', 'ContactURI'],
        '0x100C': ['LO', '1', 'ContactDisplayName'],
        '0x100E': ['SQ', '1', 'ProcedureStepDiscontinuationReasonCodeSequence'],
        '0x1020': ['SQ', '1', 'BeamTaskSequence'],
        '0x1022': ['CS', '1', 'BeamTaskType'],
        '0x1024': ['IS', '1', 'BeamOrderIndexTrial'],
        '0x1025': ['CS', '1', 'AutosequenceFlag'],
        '0x1026': ['FD', '1', 'TableTopVerticalAdjustedPosition'],
        '0x1027': ['FD', '1', 'TableTopLongitudinalAdjustedPosition'],
        '0x1028': ['FD', '1', 'TableTopLateralAdjustedPosition'],
        '0x102A': ['FD', '1', 'PatientSupportAdjustedAngle'],
        '0x102B': ['FD', '1', 'TableTopEccentricAdjustedAngle'],
        '0x102C': ['FD', '1', 'TableTopPitchAdjustedAngle'],
        '0x102D': ['FD', '1', 'TableTopRollAdjustedAngle'],
        '0x1030': ['SQ', '1', 'DeliveryVerificationImageSequence'],
        '0x1032': ['CS', '1', 'VerificationImageTiming'],
        '0x1034': ['CS', '1', 'DoubleExposureFlag'],
        '0x1036': ['CS', '1', 'DoubleExposureOrdering'],
        '0x1038': ['DS', '1', 'DoubleExposureMetersetTrial'],
        '0x103A': ['DS', '4', 'DoubleExposureFieldDeltaTrial'],
        '0x1040': ['SQ', '1', 'RelatedReferenceRTImageSequence'],
        '0x1042': ['SQ', '1', 'GeneralMachineVerificationSequence'],
        '0x1044': ['SQ', '1', 'ConventionalMachineVerificationSequence'],
        '0x1046': ['SQ', '1', 'IonMachineVerificationSequence'],
        '0x1048': ['SQ', '1', 'FailedAttributesSequence'],
        '0x104A': ['SQ', '1', 'OverriddenAttributesSequence'],
        '0x104C': ['SQ', '1', 'ConventionalControlPointVerificationSequence'],
        '0x104E': ['SQ', '1', 'IonControlPointVerificationSequence'],
        '0x1050': ['SQ', '1', 'AttributeOccurrenceSequence'],
        '0x1052': ['AT', '1', 'AttributeOccurrencePointer'],
        '0x1054': ['UL', '1', 'AttributeItemSelector'],
        '0x1056': ['LO', '1', 'AttributeOccurrencePrivateCreator'],
        '0x1057': ['IS', '1-n', 'SelectorSequencePointerItems'],
        '0x1200': ['CS', '1', 'ScheduledProcedureStepPriority'],
        '0x1202': ['LO', '1', 'WorklistLabel'],
        '0x1204': ['LO', '1', 'ProcedureStepLabel'],
        '0x1210': ['SQ', '1', 'ScheduledProcessingParametersSequence'],
        '0x1212': ['SQ', '1', 'PerformedProcessingParametersSequence'],
        '0x1216': ['SQ', '1', 'UnifiedProcedureStepPerformedProcedureSequence'],
        '0x1220': ['SQ', '1', 'RelatedProcedureStepSequence'],
        '0x1222': ['LO', '1', 'ProcedureStepRelationshipType'],
        '0x1224': ['SQ', '1', 'ReplacedProcedureStepSequence'],
        '0x1230': ['LO', '1', 'DeletionLock'],
        '0x1234': ['AE', '1', 'ReceivingAE'],
        '0x1236': ['AE', '1', 'RequestingAE'],
        '0x1238': ['LT', '1', 'ReasonForCancellation'],
        '0x1242': ['CS', '1', 'SCPStatus'],
        '0x1244': ['CS', '1', 'SubscriptionListStatus'],
        '0x1246': ['CS', '1', 'UnifiedProcedureStepListStatus'],
        '0x1324': ['UL', '1', 'BeamOrderIndex'],
        '0x1338': ['FD', '1', 'DoubleExposureMeterset'],
        '0x133A': ['FD', '4', 'DoubleExposureFieldDelta']
    },
    '0x0076': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0001': ['LO', '1', 'ImplantAssemblyTemplateName'],
        '0x0003': ['LO', '1', 'ImplantAssemblyTemplateIssuer'],
        '0x0006': ['LO', '1', 'ImplantAssemblyTemplateVersion'],
        '0x0008': ['SQ', '1', 'ReplacedImplantAssemblyTemplateSequence'],
        '0x000A': ['CS', '1', 'ImplantAssemblyTemplateType'],
        '0x000C': ['SQ', '1', 'OriginalImplantAssemblyTemplateSequence'],
        '0x000E': ['SQ', '1', 'DerivationImplantAssemblyTemplateSequence'],
        '0x0010': ['SQ', '1', 'ImplantAssemblyTemplateTargetAnatomySequence'],
        '0x0020': ['SQ', '1', 'ProcedureTypeCodeSequence'],
        '0x0030': ['LO', '1', 'SurgicalTechnique'],
        '0x0032': ['SQ', '1', 'ComponentTypesSequence'],
        '0x0034': ['CS', '1', 'ComponentTypeCodeSequence'],
        '0x0036': ['CS', '1', 'ExclusiveComponentType'],
        '0x0038': ['CS', '1', 'MandatoryComponentType'],
        '0x0040': ['SQ', '1', 'ComponentSequence'],
        '0x0055': ['US', '1', 'ComponentID'],
        '0x0060': ['SQ', '1', 'ComponentAssemblySequence'],
        '0x0070': ['US', '1', 'Component1ReferencedID'],
        '0x0080': ['US', '1', 'Component1ReferencedMatingFeatureSetID'],
        '0x0090': ['US', '1', 'Component1ReferencedMatingFeatureID'],
        '0x00A0': ['US', '1', 'Component2ReferencedID'],
        '0x00B0': ['US', '1', 'Component2ReferencedMatingFeatureSetID'],
        '0x00C0': ['US', '1', 'Component2ReferencedMatingFeatureID']
    },
    '0x0078': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0001': ['LO', '1', 'ImplantTemplateGroupName'],
        '0x0010': ['ST', '1', 'ImplantTemplateGroupDescription'],
        '0x0020': ['LO', '1', 'ImplantTemplateGroupIssuer'],
        '0x0024': ['LO', '1', 'ImplantTemplateGroupVersion'],
        '0x0026': ['SQ', '1', 'ReplacedImplantTemplateGroupSequence'],
        '0x0028': ['SQ', '1', 'ImplantTemplateGroupTargetAnatomySequence'],
        '0x002A': ['SQ', '1', 'ImplantTemplateGroupMembersSequence'],
        '0x002E': ['US', '1', 'ImplantTemplateGroupMemberID'],
        '0x0050': ['FD', '3', 'ThreeDImplantTemplateGroupMemberMatchingPoint'],
        '0x0060': ['FD', '9', 'ThreeDImplantTemplateGroupMemberMatchingAxes'],
        '0x0070': ['SQ', '1', 'ImplantTemplateGroupMemberMatching2DCoordinatesSequence'],
        '0x0090': ['FD', '2', 'TwoDImplantTemplateGroupMemberMatchingPoint'],
        '0x00A0': ['FD', '4', 'TwoDImplantTemplateGroupMemberMatchingAxes'],
        '0x00B0': ['SQ', '1', 'ImplantTemplateGroupVariationDimensionSequence'],
        '0x00B2': ['LO', '1', 'ImplantTemplateGroupVariationDimensionName'],
        '0x00B4': ['SQ', '1', 'ImplantTemplateGroupVariationDimensionRankSequence'],
        '0x00B6': ['US', '1', 'ReferencedImplantTemplateGroupMemberID'],
        '0x00B8': ['US', '1', 'ImplantTemplateGroupVariationDimensionRank']
    },
    '0x0080': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0001': ['SQ', '1', 'SurfaceScanAcquisitionTypeCodeSequence'],
        '0x0002': ['SQ', '1', 'SurfaceScanModeCodeSequence'],
        '0x0003': ['SQ', '1', 'RegistrationMethodCodeSequence'],
        '0x0004': ['FD', '1', 'ShotDurationTime'],
        '0x0005': ['FD', '1', 'ShotOffsetTime'],
        '0x0006': ['US', '1-n', 'SurfacePointPresentationValueData'],
        '0x0007': ['US', '3-3n', 'SurfacePointColorCIELabValueData'],
        '0x0008': ['SQ', '1', 'UVMappingSequence'],
        '0x0009': ['SH', '1', 'TextureLabel'],
        '0x0010': ['OF', '1-n', 'UValueData'],
        '0x0011': ['OF', '1-n', 'VValueData'],
        '0x0012': ['SQ', '1', 'ReferencedTextureSequence'],
        '0x0013': ['SQ', '1', 'ReferencedSurfaceDataSequence']
    },
    '0x0088': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0130': ['SH', '1', 'StorageMediaFileSetID'],
        '0x0140': ['UI', '1', 'StorageMediaFileSetUID'],
        '0x0200': ['SQ', '1', 'IconImageSequence'],
        '0x0904': ['LO', '1', 'TopicTitle'],
        '0x0906': ['ST', '1', 'TopicSubject'],
        '0x0910': ['LO', '1', 'TopicAuthor'],
        '0x0912': ['LO', '1-32', 'TopicKeywords']
    },
    '0x0100': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0410': ['CS', '1', 'SOPInstanceStatus'],
        '0x0420': ['DT', '1', 'SOPAuthorizationDateTime'],
        '0x0424': ['LT', '1', 'SOPAuthorizationComment'],
        '0x0426': ['LO', '1', 'AuthorizationEquipmentCertificationNumber']
    },
    '0x0400': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0005': ['US', '1', 'MACIDNumber'],
        '0x0010': ['UI', '1', 'MACCalculationTransferSyntaxUID'],
        '0x0015': ['CS', '1', 'MACAlgorithm'],
        '0x0020': ['AT', '1-n', 'DataElementsSigned'],
        '0x0100': ['UI', '1', 'DigitalSignatureUID'],
        '0x0105': ['DT', '1', 'DigitalSignatureDateTime'],
        '0x0110': ['CS', '1', 'CertificateType'],
        '0x0115': ['OB', '1', 'CertificateOfSigner'],
        '0x0120': ['OB', '1', 'Signature'],
        '0x0305': ['CS', '1', 'CertifiedTimestampType'],
        '0x0310': ['OB', '1', 'CertifiedTimestamp'],
        '0x0401': ['SQ', '1', 'DigitalSignaturePurposeCodeSequence'],
        '0x0402': ['SQ', '1', 'ReferencedDigitalSignatureSequence'],
        '0x0403': ['SQ', '1', 'ReferencedSOPInstanceMACSequence'],
        '0x0404': ['OB', '1', 'MAC'],
        '0x0500': ['SQ', '1', 'EncryptedAttributesSequence'],
        '0x0510': ['UI', '1', 'EncryptedContentTransferSyntaxUID'],
        '0x0520': ['OB', '1', 'EncryptedContent'],
        '0x0550': ['SQ', '1', 'ModifiedAttributesSequence'],
        '0x0561': ['SQ', '1', 'OriginalAttributesSequence'],
        '0x0562': ['DT', '1', 'AttributeModificationDateTime'],
        '0x0563': ['LO', '1', 'ModifyingSystem'],
        '0x0564': ['LO', '1', 'SourceOfPreviousValues'],
        '0x0565': ['CS', '1', 'ReasonForTheAttributeModification']
    },
    '0x1000': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0010': ['US', '3', 'EscapeTriplet'],
        '0x0011': ['US', '3', 'RunLengthTriplet'],
        '0x0012': ['US', '1', 'HuffmanTableSize'],
        '0x0013': ['US', '3', 'HuffmanTableTriplet'],
        '0x0014': ['US', '1', 'ShiftTableSize'],
        '0x0015': ['US', '3', 'ShiftTableTriplet']
    },
    '0x1010': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0004': ['US', '1-n', 'ZonalMap']
    },
    '0x2000': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0010': ['IS', '1', 'NumberOfCopies'],
        '0x001E': ['SQ', '1', 'PrinterConfigurationSequence'],
        '0x0020': ['CS', '1', 'PrintPriority'],
        '0x0030': ['CS', '1', 'MediumType'],
        '0x0040': ['CS', '1', 'FilmDestination'],
        '0x0050': ['LO', '1', 'FilmSessionLabel'],
        '0x0060': ['IS', '1', 'MemoryAllocation'],
        '0x0061': ['IS', '1', 'MaximumMemoryAllocation'],
        '0x0062': ['CS', '1', 'ColorImagePrintingFlag'],
        '0x0063': ['CS', '1', 'CollationFlag'],
        '0x0065': ['CS', '1', 'AnnotationFlag'],
        '0x0067': ['CS', '1', 'ImageOverlayFlag'],
        '0x0069': ['CS', '1', 'PresentationLUTFlag'],
        '0x006A': ['CS', '1', 'ImageBoxPresentationLUTFlag'],
        '0x00A0': ['US', '1', 'MemoryBitDepth'],
        '0x00A1': ['US', '1', 'PrintingBitDepth'],
        '0x00A2': ['SQ', '1', 'MediaInstalledSequence'],
        '0x00A4': ['SQ', '1', 'OtherMediaAvailableSequence'],
        '0x00A8': ['SQ', '1', 'SupportedImageDisplayFormatsSequence'],
        '0x0500': ['SQ', '1', 'ReferencedFilmBoxSequence'],
        '0x0510': ['SQ', '1', 'ReferencedStoredPrintSequence']
    },
    '0x2010': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0010': ['ST', '1', 'ImageDisplayFormat'],
        '0x0030': ['CS', '1', 'AnnotationDisplayFormatID'],
        '0x0040': ['CS', '1', 'FilmOrientation'],
        '0x0050': ['CS', '1', 'FilmSizeID'],
        '0x0052': ['CS', '1', 'PrinterResolutionID'],
        '0x0054': ['CS', '1', 'DefaultPrinterResolutionID'],
        '0x0060': ['CS', '1', 'MagnificationType'],
        '0x0080': ['CS', '1', 'SmoothingType'],
        '0x00A6': ['CS', '1', 'DefaultMagnificationType'],
        '0x00A7': ['CS', '1-n', 'OtherMagnificationTypesAvailable'],
        '0x00A8': ['CS', '1', 'DefaultSmoothingType'],
        '0x00A9': ['CS', '1-n', 'OtherSmoothingTypesAvailable'],
        '0x0100': ['CS', '1', 'BorderDensity'],
        '0x0110': ['CS', '1', 'EmptyImageDensity'],
        '0x0120': ['US', '1', 'MinDensity'],
        '0x0130': ['US', '1', 'MaxDensity'],
        '0x0140': ['CS', '1', 'Trim'],
        '0x0150': ['ST', '1', 'ConfigurationInformation'],
        '0x0152': ['LT', '1', 'ConfigurationInformationDescription'],
        '0x0154': ['IS', '1', 'MaximumCollatedFilms'],
        '0x015E': ['US', '1', 'Illumination'],
        '0x0160': ['US', '1', 'ReflectedAmbientLight'],
        '0x0376': ['DS', '2', 'PrinterPixelSpacing'],
        '0x0500': ['SQ', '1', 'ReferencedFilmSessionSequence'],
        '0x0510': ['SQ', '1', 'ReferencedImageBoxSequence'],
        '0x0520': ['SQ', '1', 'ReferencedBasicAnnotationBoxSequence']
    },
    '0x2020': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0010': ['US', '1', 'ImageBoxPosition'],
        '0x0020': ['CS', '1', 'Polarity'],
        '0x0030': ['DS', '1', 'RequestedImageSize'],
        '0x0040': ['CS', '1', 'RequestedDecimateCropBehavior'],
        '0x0050': ['CS', '1', 'RequestedResolutionID'],
        '0x00A0': ['CS', '1', 'RequestedImageSizeFlag'],
        '0x00A2': ['CS', '1', 'DecimateCropResult'],
        '0x0110': ['SQ', '1', 'BasicGrayscaleImageSequence'],
        '0x0111': ['SQ', '1', 'BasicColorImageSequence'],
        '0x0130': ['SQ', '1', 'ReferencedImageOverlayBoxSequence'],
        '0x0140': ['SQ', '1', 'ReferencedVOILUTBoxSequence']
    },
    '0x2030': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0010': ['US', '1', 'AnnotationPosition'],
        '0x0020': ['LO', '1', 'TextString']
    },
    '0x2040': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0010': ['SQ', '1', 'ReferencedOverlayPlaneSequence'],
        '0x0011': ['US', '1-99', 'ReferencedOverlayPlaneGroups'],
        '0x0020': ['SQ', '1', 'OverlayPixelDataSequence'],
        '0x0060': ['CS', '1', 'OverlayMagnificationType'],
        '0x0070': ['CS', '1', 'OverlaySmoothingType'],
        '0x0072': ['CS', '1', 'OverlayOrImageMagnification'],
        '0x0074': ['US', '1', 'MagnifyToNumberOfColumns'],
        '0x0080': ['CS', '1', 'OverlayForegroundDensity'],
        '0x0082': ['CS', '1', 'OverlayBackgroundDensity'],
        '0x0090': ['CS', '1', 'OverlayMode'],
        '0x0100': ['CS', '1', 'ThresholdDensity'],
        '0x0500': ['SQ', '1', 'ReferencedImageBoxSequenceRetired']
    },
    '0x2050': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0010': ['SQ', '1', 'PresentationLUTSequence'],
        '0x0020': ['CS', '1', 'PresentationLUTShape'],
        '0x0500': ['SQ', '1', 'ReferencedPresentationLUTSequence']
    },
    '0x2100': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0010': ['SH', '1', 'PrintJobID'],
        '0x0020': ['CS', '1', 'ExecutionStatus'],
        '0x0030': ['CS', '1', 'ExecutionStatusInfo'],
        '0x0040': ['DA', '1', 'CreationDate'],
        '0x0050': ['TM', '1', 'CreationTime'],
        '0x0070': ['AE', '1', 'Originator'],
        '0x0140': ['AE', '1', 'DestinationAE'],
        '0x0160': ['SH', '1', 'OwnerID'],
        '0x0170': ['IS', '1', 'NumberOfFilms'],
        '0x0500': ['SQ', '1', 'ReferencedPrintJobSequencePullStoredPrint']
    },
    '0x2110': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0010': ['CS', '1', 'PrinterStatus'],
        '0x0020': ['CS', '1', 'PrinterStatusInfo'],
        '0x0030': ['LO', '1', 'PrinterName'],
        '0x0099': ['SH', '1', 'PrintQueueID']
    },
    '0x2120': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0010': ['CS', '1', 'QueueStatus'],
        '0x0050': ['SQ', '1', 'PrintJobDescriptionSequence'],
        '0x0070': ['SQ', '1', 'ReferencedPrintJobSequence']
    },
    '0x2130': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0010': ['SQ', '1', 'PrintManagementCapabilitiesSequence'],
        '0x0015': ['SQ', '1', 'PrinterCharacteristicsSequence'],
        '0x0030': ['SQ', '1', 'FilmBoxContentSequence'],
        '0x0040': ['SQ', '1', 'ImageBoxContentSequence'],
        '0x0050': ['SQ', '1', 'AnnotationContentSequence'],
        '0x0060': ['SQ', '1', 'ImageOverlayBoxContentSequence'],
        '0x0080': ['SQ', '1', 'PresentationLUTContentSequence'],
        '0x00A0': ['SQ', '1', 'ProposedStudySequence'],
        '0x00C0': ['SQ', '1', 'OriginalImageSequence']
    },
    '0x2200': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0001': ['CS', '1', 'LabelUsingInformationExtractedFromInstances'],
        '0x0002': ['UT', '1', 'LabelText'],
        '0x0003': ['CS', '1', 'LabelStyleSelection'],
        '0x0004': ['LT', '1', 'MediaDisposition'],
        '0x0005': ['LT', '1', 'BarcodeValue'],
        '0x0006': ['CS', '1', 'BarcodeSymbology'],
        '0x0007': ['CS', '1', 'AllowMediaSplitting'],
        '0x0008': ['CS', '1', 'IncludeNonDICOMObjects'],
        '0x0009': ['CS', '1', 'IncludeDisplayApplication'],
        '0x000A': ['CS', '1', 'PreserveCompositeInstancesAfterMediaCreation'],
        '0x000B': ['US', '1', 'TotalNumberOfPiecesOfMediaCreated'],
        '0x000C': ['LO', '1', 'RequestedMediaApplicationProfile'],
        '0x000D': ['SQ', '1', 'ReferencedStorageMediaSequence'],
        '0x000E': ['AT', '1-n', 'FailureAttributes'],
        '0x000F': ['CS', '1', 'AllowLossyCompression'],
        '0x0020': ['CS', '1', 'RequestPriority']
    },
    '0x3002': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0002': ['SH', '1', 'RTImageLabel'],
        '0x0003': ['LO', '1', 'RTImageName'],
        '0x0004': ['ST', '1', 'RTImageDescription'],
        '0x000A': ['CS', '1', 'ReportedValuesOrigin'],
        '0x000C': ['CS', '1', 'RTImagePlane'],
        '0x000D': ['DS', '3', 'XRayImageReceptorTranslation'],
        '0x000E': ['DS', '1', 'XRayImageReceptorAngle'],
        '0x0010': ['DS', '6', 'RTImageOrientation'],
        '0x0011': ['DS', '2', 'ImagePlanePixelSpacing'],
        '0x0012': ['DS', '2', 'RTImagePosition'],
        '0x0020': ['SH', '1', 'RadiationMachineName'],
        '0x0022': ['DS', '1', 'RadiationMachineSAD'],
        '0x0024': ['DS', '1', 'RadiationMachineSSD'],
        '0x0026': ['DS', '1', 'RTImageSID'],
        '0x0028': ['DS', '1', 'SourceToReferenceObjectDistance'],
        '0x0029': ['IS', '1', 'FractionNumber'],
        '0x0030': ['SQ', '1', 'ExposureSequence'],
        '0x0032': ['DS', '1', 'MetersetExposure'],
        '0x0034': ['DS', '4', 'DiaphragmPosition'],
        '0x0040': ['SQ', '1', 'FluenceMapSequence'],
        '0x0041': ['CS', '1', 'FluenceDataSource'],
        '0x0042': ['DS', '1', 'FluenceDataScale'],
        '0x0050': ['SQ', '1', 'PrimaryFluenceModeSequence'],
        '0x0051': ['CS', '1', 'FluenceMode'],
        '0x0052': ['SH', '1', 'FluenceModeID']
    },
    '0x3004': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0001': ['CS', '1', 'DVHType'],
        '0x0002': ['CS', '1', 'DoseUnits'],
        '0x0004': ['CS', '1', 'DoseType'],
        '0x0005': ['CS', '1', 'SpatialTransformOfDose'],
        '0x0006': ['LO', '1', 'DoseComment'],
        '0x0008': ['DS', '3', 'NormalizationPoint'],
        '0x000A': ['CS', '1', 'DoseSummationType'],
        '0x000C': ['DS', '2-n', 'GridFrameOffsetVector'],
        '0x000E': ['DS', '1', 'DoseGridScaling'],
        '0x0010': ['SQ', '1', 'RTDoseROISequence'],
        '0x0012': ['DS', '1', 'DoseValue'],
        '0x0014': ['CS', '1-3', 'TissueHeterogeneityCorrection'],
        '0x0040': ['DS', '3', 'DVHNormalizationPoint'],
        '0x0042': ['DS', '1', 'DVHNormalizationDoseValue'],
        '0x0050': ['SQ', '1', 'DVHSequence'],
        '0x0052': ['DS', '1', 'DVHDoseScaling'],
        '0x0054': ['CS', '1', 'DVHVolumeUnits'],
        '0x0056': ['IS', '1', 'DVHNumberOfBins'],
        '0x0058': ['DS', '2-2n', 'DVHData'],
        '0x0060': ['SQ', '1', 'DVHReferencedROISequence'],
        '0x0062': ['CS', '1', 'DVHROIContributionType'],
        '0x0070': ['DS', '1', 'DVHMinimumDose'],
        '0x0072': ['DS', '1', 'DVHMaximumDose'],
        '0x0074': ['DS', '1', 'DVHMeanDose']
    },
    '0x3006': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0002': ['SH', '1', 'StructureSetLabel'],
        '0x0004': ['LO', '1', 'StructureSetName'],
        '0x0006': ['ST', '1', 'StructureSetDescription'],
        '0x0008': ['DA', '1', 'StructureSetDate'],
        '0x0009': ['TM', '1', 'StructureSetTime'],
        '0x0010': ['SQ', '1', 'ReferencedFrameOfReferenceSequence'],
        '0x0012': ['SQ', '1', 'RTReferencedStudySequence'],
        '0x0014': ['SQ', '1', 'RTReferencedSeriesSequence'],
        '0x0016': ['SQ', '1', 'ContourImageSequence'],
        '0x0018': ['SQ', '1', 'PredecessorStructureSetSequence'],
        '0x0020': ['SQ', '1', 'StructureSetROISequence'],
        '0x0022': ['IS', '1', 'ROINumber'],
        '0x0024': ['UI', '1', 'ReferencedFrameOfReferenceUID'],
        '0x0026': ['LO', '1', 'ROIName'],
        '0x0028': ['ST', '1', 'ROIDescription'],
        '0x002A': ['IS', '3', 'ROIDisplayColor'],
        '0x002C': ['DS', '1', 'ROIVolume'],
        '0x0030': ['SQ', '1', 'RTRelatedROISequence'],
        '0x0033': ['CS', '1', 'RTROIRelationship'],
        '0x0036': ['CS', '1', 'ROIGenerationAlgorithm'],
        '0x0038': ['LO', '1', 'ROIGenerationDescription'],
        '0x0039': ['SQ', '1', 'ROIContourSequence'],
        '0x0040': ['SQ', '1', 'ContourSequence'],
        '0x0042': ['CS', '1', 'ContourGeometricType'],
        '0x0044': ['DS', '1', 'ContourSlabThickness'],
        '0x0045': ['DS', '3', 'ContourOffsetVector'],
        '0x0046': ['IS', '1', 'NumberOfContourPoints'],
        '0x0048': ['IS', '1', 'ContourNumber'],
        '0x0049': ['IS', '1-n', 'AttachedContours'],
        '0x0050': ['DS', '3-3n', 'ContourData'],
        '0x0080': ['SQ', '1', 'RTROIObservationsSequence'],
        '0x0082': ['IS', '1', 'ObservationNumber'],
        '0x0084': ['IS', '1', 'ReferencedROINumber'],
        '0x0085': ['SH', '1', 'ROIObservationLabel'],
        '0x0086': ['SQ', '1', 'RTROIIdentificationCodeSequence'],
        '0x0088': ['ST', '1', 'ROIObservationDescription'],
        '0x00A0': ['SQ', '1', 'RelatedRTROIObservationsSequence'],
        '0x00A4': ['CS', '1', 'RTROIInterpretedType'],
        '0x00A6': ['PN', '1', 'ROIInterpreter'],
        '0x00B0': ['SQ', '1', 'ROIPhysicalPropertiesSequence'],
        '0x00B2': ['CS', '1', 'ROIPhysicalProperty'],
        '0x00B4': ['DS', '1', 'ROIPhysicalPropertyValue'],
        '0x00B6': ['SQ', '1', 'ROIElementalCompositionSequence'],
        '0x00B7': ['US', '1', 'ROIElementalCompositionAtomicNumber'],
        '0x00B8': ['FL', '1', 'ROIElementalCompositionAtomicMassFraction'],
        '0x00B9': ['SQ', '1', 'AdditionalRTROIIdentificationCodeSequence'],
        '0x00C0': ['SQ', '1', 'FrameOfReferenceRelationshipSequence'],
        '0x00C2': ['UI', '1', 'RelatedFrameOfReferenceUID'],
        '0x00C4': ['CS', '1', 'FrameOfReferenceTransformationType'],
        '0x00C6': ['DS', '16', 'FrameOfReferenceTransformationMatrix'],
        '0x00C8': ['LO', '1', 'FrameOfReferenceTransformationComment']
    },
    '0x3008': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0010': ['SQ', '1', 'MeasuredDoseReferenceSequence'],
        '0x0012': ['ST', '1', 'MeasuredDoseDescription'],
        '0x0014': ['CS', '1', 'MeasuredDoseType'],
        '0x0016': ['DS', '1', 'MeasuredDoseValue'],
        '0x0020': ['SQ', '1', 'TreatmentSessionBeamSequence'],
        '0x0021': ['SQ', '1', 'TreatmentSessionIonBeamSequence'],
        '0x0022': ['IS', '1', 'CurrentFractionNumber'],
        '0x0024': ['DA', '1', 'TreatmentControlPointDate'],
        '0x0025': ['TM', '1', 'TreatmentControlPointTime'],
        '0x002A': ['CS', '1', 'TreatmentTerminationStatus'],
        '0x002B': ['SH', '1', 'TreatmentTerminationCode'],
        '0x002C': ['CS', '1', 'TreatmentVerificationStatus'],
        '0x0030': ['SQ', '1', 'ReferencedTreatmentRecordSequence'],
        '0x0032': ['DS', '1', 'SpecifiedPrimaryMeterset'],
        '0x0033': ['DS', '1', 'SpecifiedSecondaryMeterset'],
        '0x0036': ['DS', '1', 'DeliveredPrimaryMeterset'],
        '0x0037': ['DS', '1', 'DeliveredSecondaryMeterset'],
        '0x003A': ['DS', '1', 'SpecifiedTreatmentTime'],
        '0x003B': ['DS', '1', 'DeliveredTreatmentTime'],
        '0x0040': ['SQ', '1', 'ControlPointDeliverySequence'],
        '0x0041': ['SQ', '1', 'IonControlPointDeliverySequence'],
        '0x0042': ['DS', '1', 'SpecifiedMeterset'],
        '0x0044': ['DS', '1', 'DeliveredMeterset'],
        '0x0045': ['FL', '1', 'MetersetRateSet'],
        '0x0046': ['FL', '1', 'MetersetRateDelivered'],
        '0x0047': ['FL', '1-n', 'ScanSpotMetersetsDelivered'],
        '0x0048': ['DS', '1', 'DoseRateDelivered'],
        '0x0050': ['SQ', '1', 'TreatmentSummaryCalculatedDoseReferenceSequence'],
        '0x0052': ['DS', '1', 'CumulativeDoseToDoseReference'],
        '0x0054': ['DA', '1', 'FirstTreatmentDate'],
        '0x0056': ['DA', '1', 'MostRecentTreatmentDate'],
        '0x005A': ['IS', '1', 'NumberOfFractionsDelivered'],
        '0x0060': ['SQ', '1', 'OverrideSequence'],
        '0x0061': ['AT', '1', 'ParameterSequencePointer'],
        '0x0062': ['AT', '1', 'OverrideParameterPointer'],
        '0x0063': ['IS', '1', 'ParameterItemIndex'],
        '0x0064': ['IS', '1', 'MeasuredDoseReferenceNumber'],
        '0x0065': ['AT', '1', 'ParameterPointer'],
        '0x0066': ['ST', '1', 'OverrideReason'],
        '0x0068': ['SQ', '1', 'CorrectedParameterSequence'],
        '0x006A': ['FL', '1', 'CorrectionValue'],
        '0x0070': ['SQ', '1', 'CalculatedDoseReferenceSequence'],
        '0x0072': ['IS', '1', 'CalculatedDoseReferenceNumber'],
        '0x0074': ['ST', '1', 'CalculatedDoseReferenceDescription'],
        '0x0076': ['DS', '1', 'CalculatedDoseReferenceDoseValue'],
        '0x0078': ['DS', '1', 'StartMeterset'],
        '0x007A': ['DS', '1', 'EndMeterset'],
        '0x0080': ['SQ', '1', 'ReferencedMeasuredDoseReferenceSequence'],
        '0x0082': ['IS', '1', 'ReferencedMeasuredDoseReferenceNumber'],
        '0x0090': ['SQ', '1', 'ReferencedCalculatedDoseReferenceSequence'],
        '0x0092': ['IS', '1', 'ReferencedCalculatedDoseReferenceNumber'],
        '0x00A0': ['SQ', '1', 'BeamLimitingDeviceLeafPairsSequence'],
        '0x00B0': ['SQ', '1', 'RecordedWedgeSequence'],
        '0x00C0': ['SQ', '1', 'RecordedCompensatorSequence'],
        '0x00D0': ['SQ', '1', 'RecordedBlockSequence'],
        '0x00E0': ['SQ', '1', 'TreatmentSummaryMeasuredDoseReferenceSequence'],
        '0x00F0': ['SQ', '1', 'RecordedSnoutSequence'],
        '0x00F2': ['SQ', '1', 'RecordedRangeShifterSequence'],
        '0x00F4': ['SQ', '1', 'RecordedLateralSpreadingDeviceSequence'],
        '0x00F6': ['SQ', '1', 'RecordedRangeModulatorSequence'],
        '0x0100': ['SQ', '1', 'RecordedSourceSequence'],
        '0x0105': ['LO', '1', 'SourceSerialNumber'],
        '0x0110': ['SQ', '1', 'TreatmentSessionApplicationSetupSequence'],
        '0x0116': ['CS', '1', 'ApplicationSetupCheck'],
        '0x0120': ['SQ', '1', 'RecordedBrachyAccessoryDeviceSequence'],
        '0x0122': ['IS', '1', 'ReferencedBrachyAccessoryDeviceNumber'],
        '0x0130': ['SQ', '1', 'RecordedChannelSequence'],
        '0x0132': ['DS', '1', 'SpecifiedChannelTotalTime'],
        '0x0134': ['DS', '1', 'DeliveredChannelTotalTime'],
        '0x0136': ['IS', '1', 'SpecifiedNumberOfPulses'],
        '0x0138': ['IS', '1', 'DeliveredNumberOfPulses'],
        '0x013A': ['DS', '1', 'SpecifiedPulseRepetitionInterval'],
        '0x013C': ['DS', '1', 'DeliveredPulseRepetitionInterval'],
        '0x0140': ['SQ', '1', 'RecordedSourceApplicatorSequence'],
        '0x0142': ['IS', '1', 'ReferencedSourceApplicatorNumber'],
        '0x0150': ['SQ', '1', 'RecordedChannelShieldSequence'],
        '0x0152': ['IS', '1', 'ReferencedChannelShieldNumber'],
        '0x0160': ['SQ', '1', 'BrachyControlPointDeliveredSequence'],
        '0x0162': ['DA', '1', 'SafePositionExitDate'],
        '0x0164': ['TM', '1', 'SafePositionExitTime'],
        '0x0166': ['DA', '1', 'SafePositionReturnDate'],
        '0x0168': ['TM', '1', 'SafePositionReturnTime'],
        '0x0171': ['SQ', '1', 'PulseSpecificBrachyControlPointDeliveredSequence'],
        '0x0172': ['US', '1', 'PulseNumber'],
        '0x0173': ['SQ', '1', 'BrachyPulseControlPointDeliveredSequence'],
        '0x0200': ['CS', '1', 'CurrentTreatmentStatus'],
        '0x0202': ['ST', '1', 'TreatmentStatusComment'],
        '0x0220': ['SQ', '1', 'FractionGroupSummarySequence'],
        '0x0223': ['IS', '1', 'ReferencedFractionNumber'],
        '0x0224': ['CS', '1', 'FractionGroupType'],
        '0x0230': ['CS', '1', 'BeamStopperPosition'],
        '0x0240': ['SQ', '1', 'FractionStatusSummarySequence'],
        '0x0250': ['DA', '1', 'TreatmentDate'],
        '0x0251': ['TM', '1', 'TreatmentTime']
    },
    '0x300A': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0002': ['SH', '1', 'RTPlanLabel'],
        '0x0003': ['LO', '1', 'RTPlanName'],
        '0x0004': ['ST', '1', 'RTPlanDescription'],
        '0x0006': ['DA', '1', 'RTPlanDate'],
        '0x0007': ['TM', '1', 'RTPlanTime'],
        '0x0009': ['LO', '1-n', 'TreatmentProtocols'],
        '0x000A': ['CS', '1', 'PlanIntent'],
        '0x000B': ['LO', '1-n', 'TreatmentSites'],
        '0x000C': ['CS', '1', 'RTPlanGeometry'],
        '0x000E': ['ST', '1', 'PrescriptionDescription'],
        '0x0010': ['SQ', '1', 'DoseReferenceSequence'],
        '0x0012': ['IS', '1', 'DoseReferenceNumber'],
        '0x0013': ['UI', '1', 'DoseReferenceUID'],
        '0x0014': ['CS', '1', 'DoseReferenceStructureType'],
        '0x0015': ['CS', '1', 'NominalBeamEnergyUnit'],
        '0x0016': ['LO', '1', 'DoseReferenceDescription'],
        '0x0018': ['DS', '3', 'DoseReferencePointCoordinates'],
        '0x001A': ['DS', '1', 'NominalPriorDose'],
        '0x0020': ['CS', '1', 'DoseReferenceType'],
        '0x0021': ['DS', '1', 'ConstraintWeight'],
        '0x0022': ['DS', '1', 'DeliveryWarningDose'],
        '0x0023': ['DS', '1', 'DeliveryMaximumDose'],
        '0x0025': ['DS', '1', 'TargetMinimumDose'],
        '0x0026': ['DS', '1', 'TargetPrescriptionDose'],
        '0x0027': ['DS', '1', 'TargetMaximumDose'],
        '0x0028': ['DS', '1', 'TargetUnderdoseVolumeFraction'],
        '0x002A': ['DS', '1', 'OrganAtRiskFullVolumeDose'],
        '0x002B': ['DS', '1', 'OrganAtRiskLimitDose'],
        '0x002C': ['DS', '1', 'OrganAtRiskMaximumDose'],
        '0x002D': ['DS', '1', 'OrganAtRiskOverdoseVolumeFraction'],
        '0x0040': ['SQ', '1', 'ToleranceTableSequence'],
        '0x0042': ['IS', '1', 'ToleranceTableNumber'],
        '0x0043': ['SH', '1', 'ToleranceTableLabel'],
        '0x0044': ['DS', '1', 'GantryAngleTolerance'],
        '0x0046': ['DS', '1', 'BeamLimitingDeviceAngleTolerance'],
        '0x0048': ['SQ', '1', 'BeamLimitingDeviceToleranceSequence'],
        '0x004A': ['DS', '1', 'BeamLimitingDevicePositionTolerance'],
        '0x004B': ['FL', '1', 'SnoutPositionTolerance'],
        '0x004C': ['DS', '1', 'PatientSupportAngleTolerance'],
        '0x004E': ['DS', '1', 'TableTopEccentricAngleTolerance'],
        '0x004F': ['FL', '1', 'TableTopPitchAngleTolerance'],
        '0x0050': ['FL', '1', 'TableTopRollAngleTolerance'],
        '0x0051': ['DS', '1', 'TableTopVerticalPositionTolerance'],
        '0x0052': ['DS', '1', 'TableTopLongitudinalPositionTolerance'],
        '0x0053': ['DS', '1', 'TableTopLateralPositionTolerance'],
        '0x0055': ['CS', '1', 'RTPlanRelationship'],
        '0x0070': ['SQ', '1', 'FractionGroupSequence'],
        '0x0071': ['IS', '1', 'FractionGroupNumber'],
        '0x0072': ['LO', '1', 'FractionGroupDescription'],
        '0x0078': ['IS', '1', 'NumberOfFractionsPlanned'],
        '0x0079': ['IS', '1', 'NumberOfFractionPatternDigitsPerDay'],
        '0x007A': ['IS', '1', 'RepeatFractionCycleLength'],
        '0x007B': ['LT', '1', 'FractionPattern'],
        '0x0080': ['IS', '1', 'NumberOfBeams'],
        '0x0082': ['DS', '3', 'BeamDoseSpecificationPoint'],
        '0x0084': ['DS', '1', 'BeamDose'],
        '0x0086': ['DS', '1', 'BeamMeterset'],
        '0x0088': ['FL', '1', 'BeamDosePointDepth'],
        '0x0089': ['FL', '1', 'BeamDosePointEquivalentDepth'],
        '0x008A': ['FL', '1', 'BeamDosePointSSD'],
        '0x008B': ['CS', '1', 'BeamDoseMeaning'],
        '0x008C': ['SQ', '1', 'BeamDoseVerificationControlPointSequence'],
        '0x008D': ['FL', '1', 'AverageBeamDosePointDepth'],
        '0x008E': ['FL', '1', 'AverageBeamDosePointEquivalentDepth'],
        '0x008F': ['FL', '1', 'AverageBeamDosePointSSD'],
        '0x00A0': ['IS', '1', 'NumberOfBrachyApplicationSetups'],
        '0x00A2': ['DS', '3', 'BrachyApplicationSetupDoseSpecificationPoint'],
        '0x00A4': ['DS', '1', 'BrachyApplicationSetupDose'],
        '0x00B0': ['SQ', '1', 'BeamSequence'],
        '0x00B2': ['SH', '1', 'TreatmentMachineName'],
        '0x00B3': ['CS', '1', 'PrimaryDosimeterUnit'],
        '0x00B4': ['DS', '1', 'SourceAxisDistance'],
        '0x00B6': ['SQ', '1', 'BeamLimitingDeviceSequence'],
        '0x00B8': ['CS', '1', 'RTBeamLimitingDeviceType'],
        '0x00BA': ['DS', '1', 'SourceToBeamLimitingDeviceDistance'],
        '0x00BB': ['FL', '1', 'IsocenterToBeamLimitingDeviceDistance'],
        '0x00BC': ['IS', '1', 'NumberOfLeafJawPairs'],
        '0x00BE': ['DS', '3-n', 'LeafPositionBoundaries'],
        '0x00C0': ['IS', '1', 'BeamNumber'],
        '0x00C2': ['LO', '1', 'BeamName'],
        '0x00C3': ['ST', '1', 'BeamDescription'],
        '0x00C4': ['CS', '1', 'BeamType'],
        '0x00C5': ['FD', '1', 'BeamDeliveryDurationLimit'],
        '0x00C6': ['CS', '1', 'RadiationType'],
        '0x00C7': ['CS', '1', 'HighDoseTechniqueType'],
        '0x00C8': ['IS', '1', 'ReferenceImageNumber'],
        '0x00CA': ['SQ', '1', 'PlannedVerificationImageSequence'],
        '0x00CC': ['LO', '1-n', 'ImagingDeviceSpecificAcquisitionParameters'],
        '0x00CE': ['CS', '1', 'TreatmentDeliveryType'],
        '0x00D0': ['IS', '1', 'NumberOfWedges'],
        '0x00D1': ['SQ', '1', 'WedgeSequence'],
        '0x00D2': ['IS', '1', 'WedgeNumber'],
        '0x00D3': ['CS', '1', 'WedgeType'],
        '0x00D4': ['SH', '1', 'WedgeID'],
        '0x00D5': ['IS', '1', 'WedgeAngle'],
        '0x00D6': ['DS', '1', 'WedgeFactor'],
        '0x00D7': ['FL', '1', 'TotalWedgeTrayWaterEquivalentThickness'],
        '0x00D8': ['DS', '1', 'WedgeOrientation'],
        '0x00D9': ['FL', '1', 'IsocenterToWedgeTrayDistance'],
        '0x00DA': ['DS', '1', 'SourceToWedgeTrayDistance'],
        '0x00DB': ['FL', '1', 'WedgeThinEdgePosition'],
        '0x00DC': ['SH', '1', 'BolusID'],
        '0x00DD': ['ST', '1', 'BolusDescription'],
        '0x00DE': ['DS', '1', 'EffectiveWedgeAngle'],
        '0x00E0': ['IS', '1', 'NumberOfCompensators'],
        '0x00E1': ['SH', '1', 'MaterialID'],
        '0x00E2': ['DS', '1', 'TotalCompensatorTrayFactor'],
        '0x00E3': ['SQ', '1', 'CompensatorSequence'],
        '0x00E4': ['IS', '1', 'CompensatorNumber'],
        '0x00E5': ['SH', '1', 'CompensatorID'],
        '0x00E6': ['DS', '1', 'SourceToCompensatorTrayDistance'],
        '0x00E7': ['IS', '1', 'CompensatorRows'],
        '0x00E8': ['IS', '1', 'CompensatorColumns'],
        '0x00E9': ['DS', '2', 'CompensatorPixelSpacing'],
        '0x00EA': ['DS', '2', 'CompensatorPosition'],
        '0x00EB': ['DS', '1-n', 'CompensatorTransmissionData'],
        '0x00EC': ['DS', '1-n', 'CompensatorThicknessData'],
        '0x00ED': ['IS', '1', 'NumberOfBoli'],
        '0x00EE': ['CS', '1', 'CompensatorType'],
        '0x00EF': ['SH', '1', 'CompensatorTrayID'],
        '0x00F0': ['IS', '1', 'NumberOfBlocks'],
        '0x00F2': ['DS', '1', 'TotalBlockTrayFactor'],
        '0x00F3': ['FL', '1', 'TotalBlockTrayWaterEquivalentThickness'],
        '0x00F4': ['SQ', '1', 'BlockSequence'],
        '0x00F5': ['SH', '1', 'BlockTrayID'],
        '0x00F6': ['DS', '1', 'SourceToBlockTrayDistance'],
        '0x00F7': ['FL', '1', 'IsocenterToBlockTrayDistance'],
        '0x00F8': ['CS', '1', 'BlockType'],
        '0x00F9': ['LO', '1', 'AccessoryCode'],
        '0x00FA': ['CS', '1', 'BlockDivergence'],
        '0x00FB': ['CS', '1', 'BlockMountingPosition'],
        '0x00FC': ['IS', '1', 'BlockNumber'],
        '0x00FE': ['LO', '1', 'BlockName'],
        '0x0100': ['DS', '1', 'BlockThickness'],
        '0x0102': ['DS', '1', 'BlockTransmission'],
        '0x0104': ['IS', '1', 'BlockNumberOfPoints'],
        '0x0106': ['DS', '2-2n', 'BlockData'],
        '0x0107': ['SQ', '1', 'ApplicatorSequence'],
        '0x0108': ['SH', '1', 'ApplicatorID'],
        '0x0109': ['CS', '1', 'ApplicatorType'],
        '0x010A': ['LO', '1', 'ApplicatorDescription'],
        '0x010C': ['DS', '1', 'CumulativeDoseReferenceCoefficient'],
        '0x010E': ['DS', '1', 'FinalCumulativeMetersetWeight'],
        '0x0110': ['IS', '1', 'NumberOfControlPoints'],
        '0x0111': ['SQ', '1', 'ControlPointSequence'],
        '0x0112': ['IS', '1', 'ControlPointIndex'],
        '0x0114': ['DS', '1', 'NominalBeamEnergy'],
        '0x0115': ['DS', '1', 'DoseRateSet'],
        '0x0116': ['SQ', '1', 'WedgePositionSequence'],
        '0x0118': ['CS', '1', 'WedgePosition'],
        '0x011A': ['SQ', '1', 'BeamLimitingDevicePositionSequence'],
        '0x011C': ['DS', '2-2n', 'LeafJawPositions'],
        '0x011E': ['DS', '1', 'GantryAngle'],
        '0x011F': ['CS', '1', 'GantryRotationDirection'],
        '0x0120': ['DS', '1', 'BeamLimitingDeviceAngle'],
        '0x0121': ['CS', '1', 'BeamLimitingDeviceRotationDirection'],
        '0x0122': ['DS', '1', 'PatientSupportAngle'],
        '0x0123': ['CS', '1', 'PatientSupportRotationDirection'],
        '0x0124': ['DS', '1', 'TableTopEccentricAxisDistance'],
        '0x0125': ['DS', '1', 'TableTopEccentricAngle'],
        '0x0126': ['CS', '1', 'TableTopEccentricRotationDirection'],
        '0x0128': ['DS', '1', 'TableTopVerticalPosition'],
        '0x0129': ['DS', '1', 'TableTopLongitudinalPosition'],
        '0x012A': ['DS', '1', 'TableTopLateralPosition'],
        '0x012C': ['DS', '3', 'IsocenterPosition'],
        '0x012E': ['DS', '3', 'SurfaceEntryPoint'],
        '0x0130': ['DS', '1', 'SourceToSurfaceDistance'],
        '0x0131': ['FL', '1', 'AverageBeamDosePointSourceToExternalContourSurfaceDistance'],
        '0x0132': ['FL', '1', 'SourceToExternalContourDistance'],
        '0x0133': ['FL', '3', 'ExternalContourEntryPoint'],
        '0x0134': ['DS', '1', 'CumulativeMetersetWeight'],
        '0x0140': ['FL', '1', 'TableTopPitchAngle'],
        '0x0142': ['CS', '1', 'TableTopPitchRotationDirection'],
        '0x0144': ['FL', '1', 'TableTopRollAngle'],
        '0x0146': ['CS', '1', 'TableTopRollRotationDirection'],
        '0x0148': ['FL', '1', 'HeadFixationAngle'],
        '0x014A': ['FL', '1', 'GantryPitchAngle'],
        '0x014C': ['CS', '1', 'GantryPitchRotationDirection'],
        '0x014E': ['FL', '1', 'GantryPitchAngleTolerance'],
        '0x0180': ['SQ', '1', 'PatientSetupSequence'],
        '0x0182': ['IS', '1', 'PatientSetupNumber'],
        '0x0183': ['LO', '1', 'PatientSetupLabel'],
        '0x0184': ['LO', '1', 'PatientAdditionalPosition'],
        '0x0190': ['SQ', '1', 'FixationDeviceSequence'],
        '0x0192': ['CS', '1', 'FixationDeviceType'],
        '0x0194': ['SH', '1', 'FixationDeviceLabel'],
        '0x0196': ['ST', '1', 'FixationDeviceDescription'],
        '0x0198': ['SH', '1', 'FixationDevicePosition'],
        '0x0199': ['FL', '1', 'FixationDevicePitchAngle'],
        '0x019A': ['FL', '1', 'FixationDeviceRollAngle'],
        '0x01A0': ['SQ', '1', 'ShieldingDeviceSequence'],
        '0x01A2': ['CS', '1', 'ShieldingDeviceType'],
        '0x01A4': ['SH', '1', 'ShieldingDeviceLabel'],
        '0x01A6': ['ST', '1', 'ShieldingDeviceDescription'],
        '0x01A8': ['SH', '1', 'ShieldingDevicePosition'],
        '0x01B0': ['CS', '1', 'SetupTechnique'],
        '0x01B2': ['ST', '1', 'SetupTechniqueDescription'],
        '0x01B4': ['SQ', '1', 'SetupDeviceSequence'],
        '0x01B6': ['CS', '1', 'SetupDeviceType'],
        '0x01B8': ['SH', '1', 'SetupDeviceLabel'],
        '0x01BA': ['ST', '1', 'SetupDeviceDescription'],
        '0x01BC': ['DS', '1', 'SetupDeviceParameter'],
        '0x01D0': ['ST', '1', 'SetupReferenceDescription'],
        '0x01D2': ['DS', '1', 'TableTopVerticalSetupDisplacement'],
        '0x01D4': ['DS', '1', 'TableTopLongitudinalSetupDisplacement'],
        '0x01D6': ['DS', '1', 'TableTopLateralSetupDisplacement'],
        '0x0200': ['CS', '1', 'BrachyTreatmentTechnique'],
        '0x0202': ['CS', '1', 'BrachyTreatmentType'],
        '0x0206': ['SQ', '1', 'TreatmentMachineSequence'],
        '0x0210': ['SQ', '1', 'SourceSequence'],
        '0x0212': ['IS', '1', 'SourceNumber'],
        '0x0214': ['CS', '1', 'SourceType'],
        '0x0216': ['LO', '1', 'SourceManufacturer'],
        '0x0218': ['DS', '1', 'ActiveSourceDiameter'],
        '0x021A': ['DS', '1', 'ActiveSourceLength'],
        '0x021B': ['SH', '1', 'SourceModelID'],
        '0x021C': ['LO', '1', 'SourceDescription'],
        '0x0222': ['DS', '1', 'SourceEncapsulationNominalThickness'],
        '0x0224': ['DS', '1', 'SourceEncapsulationNominalTransmission'],
        '0x0226': ['LO', '1', 'SourceIsotopeName'],
        '0x0228': ['DS', '1', 'SourceIsotopeHalfLife'],
        '0x0229': ['CS', '1', 'SourceStrengthUnits'],
        '0x022A': ['DS', '1', 'ReferenceAirKermaRate'],
        '0x022B': ['DS', '1', 'SourceStrength'],
        '0x022C': ['DA', '1', 'SourceStrengthReferenceDate'],
        '0x022E': ['TM', '1', 'SourceStrengthReferenceTime'],
        '0x0230': ['SQ', '1', 'ApplicationSetupSequence'],
        '0x0232': ['CS', '1', 'ApplicationSetupType'],
        '0x0234': ['IS', '1', 'ApplicationSetupNumber'],
        '0x0236': ['LO', '1', 'ApplicationSetupName'],
        '0x0238': ['LO', '1', 'ApplicationSetupManufacturer'],
        '0x0240': ['IS', '1', 'TemplateNumber'],
        '0x0242': ['SH', '1', 'TemplateType'],
        '0x0244': ['LO', '1', 'TemplateName'],
        '0x0250': ['DS', '1', 'TotalReferenceAirKerma'],
        '0x0260': ['SQ', '1', 'BrachyAccessoryDeviceSequence'],
        '0x0262': ['IS', '1', 'BrachyAccessoryDeviceNumber'],
        '0x0263': ['SH', '1', 'BrachyAccessoryDeviceID'],
        '0x0264': ['CS', '1', 'BrachyAccessoryDeviceType'],
        '0x0266': ['LO', '1', 'BrachyAccessoryDeviceName'],
        '0x026A': ['DS', '1', 'BrachyAccessoryDeviceNominalThickness'],
        '0x026C': ['DS', '1', 'BrachyAccessoryDeviceNominalTransmission'],
        '0x0280': ['SQ', '1', 'ChannelSequence'],
        '0x0282': ['IS', '1', 'ChannelNumber'],
        '0x0284': ['DS', '1', 'ChannelLength'],
        '0x0286': ['DS', '1', 'ChannelTotalTime'],
        '0x0288': ['CS', '1', 'SourceMovementType'],
        '0x028A': ['IS', '1', 'NumberOfPulses'],
        '0x028C': ['DS', '1', 'PulseRepetitionInterval'],
        '0x0290': ['IS', '1', 'SourceApplicatorNumber'],
        '0x0291': ['SH', '1', 'SourceApplicatorID'],
        '0x0292': ['CS', '1', 'SourceApplicatorType'],
        '0x0294': ['LO', '1', 'SourceApplicatorName'],
        '0x0296': ['DS', '1', 'SourceApplicatorLength'],
        '0x0298': ['LO', '1', 'SourceApplicatorManufacturer'],
        '0x029C': ['DS', '1', 'SourceApplicatorWallNominalThickness'],
        '0x029E': ['DS', '1', 'SourceApplicatorWallNominalTransmission'],
        '0x02A0': ['DS', '1', 'SourceApplicatorStepSize'],
        '0x02A2': ['IS', '1', 'TransferTubeNumber'],
        '0x02A4': ['DS', '1', 'TransferTubeLength'],
        '0x02B0': ['SQ', '1', 'ChannelShieldSequence'],
        '0x02B2': ['IS', '1', 'ChannelShieldNumber'],
        '0x02B3': ['SH', '1', 'ChannelShieldID'],
        '0x02B4': ['LO', '1', 'ChannelShieldName'],
        '0x02B8': ['DS', '1', 'ChannelShieldNominalThickness'],
        '0x02BA': ['DS', '1', 'ChannelShieldNominalTransmission'],
        '0x02C8': ['DS', '1', 'FinalCumulativeTimeWeight'],
        '0x02D0': ['SQ', '1', 'BrachyControlPointSequence'],
        '0x02D2': ['DS', '1', 'ControlPointRelativePosition'],
        '0x02D4': ['DS', '3', 'ControlPoint3DPosition'],
        '0x02D6': ['DS', '1', 'CumulativeTimeWeight'],
        '0x02E0': ['CS', '1', 'CompensatorDivergence'],
        '0x02E1': ['CS', '1', 'CompensatorMountingPosition'],
        '0x02E2': ['DS', '1-n', 'SourceToCompensatorDistance'],
        '0x02E3': ['FL', '1', 'TotalCompensatorTrayWaterEquivalentThickness'],
        '0x02E4': ['FL', '1', 'IsocenterToCompensatorTrayDistance'],
        '0x02E5': ['FL', '1', 'CompensatorColumnOffset'],
        '0x02E6': ['FL', '1-n', 'IsocenterToCompensatorDistances'],
        '0x02E7': ['FL', '1', 'CompensatorRelativeStoppingPowerRatio'],
        '0x02E8': ['FL', '1', 'CompensatorMillingToolDiameter'],
        '0x02EA': ['SQ', '1', 'IonRangeCompensatorSequence'],
        '0x02EB': ['LT', '1', 'CompensatorDescription'],
        '0x0302': ['IS', '1', 'RadiationMassNumber'],
        '0x0304': ['IS', '1', 'RadiationAtomicNumber'],
        '0x0306': ['SS', '1', 'RadiationChargeState'],
        '0x0308': ['CS', '1', 'ScanMode'],
        '0x030A': ['FL', '2', 'VirtualSourceAxisDistances'],
        '0x030C': ['SQ', '1', 'SnoutSequence'],
        '0x030D': ['FL', '1', 'SnoutPosition'],
        '0x030F': ['SH', '1', 'SnoutID'],
        '0x0312': ['IS', '1', 'NumberOfRangeShifters'],
        '0x0314': ['SQ', '1', 'RangeShifterSequence'],
        '0x0316': ['IS', '1', 'RangeShifterNumber'],
        '0x0318': ['SH', '1', 'RangeShifterID'],
        '0x0320': ['CS', '1', 'RangeShifterType'],
        '0x0322': ['LO', '1', 'RangeShifterDescription'],
        '0x0330': ['IS', '1', 'NumberOfLateralSpreadingDevices'],
        '0x0332': ['SQ', '1', 'LateralSpreadingDeviceSequence'],
        '0x0334': ['IS', '1', 'LateralSpreadingDeviceNumber'],
        '0x0336': ['SH', '1', 'LateralSpreadingDeviceID'],
        '0x0338': ['CS', '1', 'LateralSpreadingDeviceType'],
        '0x033A': ['LO', '1', 'LateralSpreadingDeviceDescription'],
        '0x033C': ['FL', '1', 'LateralSpreadingDeviceWaterEquivalentThickness'],
        '0x0340': ['IS', '1', 'NumberOfRangeModulators'],
        '0x0342': ['SQ', '1', 'RangeModulatorSequence'],
        '0x0344': ['IS', '1', 'RangeModulatorNumber'],
        '0x0346': ['SH', '1', 'RangeModulatorID'],
        '0x0348': ['CS', '1', 'RangeModulatorType'],
        '0x034A': ['LO', '1', 'RangeModulatorDescription'],
        '0x034C': ['SH', '1', 'BeamCurrentModulationID'],
        '0x0350': ['CS', '1', 'PatientSupportType'],
        '0x0352': ['SH', '1', 'PatientSupportID'],
        '0x0354': ['LO', '1', 'PatientSupportAccessoryCode'],
        '0x0356': ['FL', '1', 'FixationLightAzimuthalAngle'],
        '0x0358': ['FL', '1', 'FixationLightPolarAngle'],
        '0x035A': ['FL', '1', 'MetersetRate'],
        '0x0360': ['SQ', '1', 'RangeShifterSettingsSequence'],
        '0x0362': ['LO', '1', 'RangeShifterSetting'],
        '0x0364': ['FL', '1', 'IsocenterToRangeShifterDistance'],
        '0x0366': ['FL', '1', 'RangeShifterWaterEquivalentThickness'],
        '0x0370': ['SQ', '1', 'LateralSpreadingDeviceSettingsSequence'],
        '0x0372': ['LO', '1', 'LateralSpreadingDeviceSetting'],
        '0x0374': ['FL', '1', 'IsocenterToLateralSpreadingDeviceDistance'],
        '0x0380': ['SQ', '1', 'RangeModulatorSettingsSequence'],
        '0x0382': ['FL', '1', 'RangeModulatorGatingStartValue'],
        '0x0384': ['FL', '1', 'RangeModulatorGatingStopValue'],
        '0x0386': ['FL', '1', 'RangeModulatorGatingStartWaterEquivalentThickness'],
        '0x0388': ['FL', '1', 'RangeModulatorGatingStopWaterEquivalentThickness'],
        '0x038A': ['FL', '1', 'IsocenterToRangeModulatorDistance'],
        '0x0390': ['SH', '1', 'ScanSpotTuneID'],
        '0x0392': ['IS', '1', 'NumberOfScanSpotPositions'],
        '0x0394': ['FL', '1-n', 'ScanSpotPositionMap'],
        '0x0396': ['FL', '1-n', 'ScanSpotMetersetWeights'],
        '0x0398': ['FL', '2', 'ScanningSpotSize'],
        '0x039A': ['IS', '1', 'NumberOfPaintings'],
        '0x03A0': ['SQ', '1', 'IonToleranceTableSequence'],
        '0x03A2': ['SQ', '1', 'IonBeamSequence'],
        '0x03A4': ['SQ', '1', 'IonBeamLimitingDeviceSequence'],
        '0x03A6': ['SQ', '1', 'IonBlockSequence'],
        '0x03A8': ['SQ', '1', 'IonControlPointSequence'],
        '0x03AA': ['SQ', '1', 'IonWedgeSequence'],
        '0x03AC': ['SQ', '1', 'IonWedgePositionSequence'],
        '0x0401': ['SQ', '1', 'ReferencedSetupImageSequence'],
        '0x0402': ['ST', '1', 'SetupImageComment'],
        '0x0410': ['SQ', '1', 'MotionSynchronizationSequence'],
        '0x0412': ['FL', '3', 'ControlPointOrientation'],
        '0x0420': ['SQ', '1', 'GeneralAccessorySequence'],
        '0x0421': ['SH', '1', 'GeneralAccessoryID'],
        '0x0422': ['ST', '1', 'GeneralAccessoryDescription'],
        '0x0423': ['CS', '1', 'GeneralAccessoryType'],
        '0x0424': ['IS', '1', 'GeneralAccessoryNumber'],
        '0x0425': ['FL', '1', 'SourceToGeneralAccessoryDistance'],
        '0x0431': ['SQ', '1', 'ApplicatorGeometrySequence'],
        '0x0432': ['CS', '1', 'ApplicatorApertureShape'],
        '0x0433': ['FL', '1', 'ApplicatorOpening'],
        '0x0434': ['FL', '1', 'ApplicatorOpeningX'],
        '0x0435': ['FL', '1', 'ApplicatorOpeningY'],
        '0x0436': ['FL', '1', 'SourceToApplicatorMountingPositionDistance'],
        '0x0440': ['IS', '1', 'NumberOfBlockSlabItems'],
        '0x0441': ['SQ', '1', 'BlockSlabSequence'],
        '0x0442': ['DS', '1', 'BlockSlabThickness'],
        '0x0443': ['US', '1', 'BlockSlabNumber'],
        '0x0450': ['SQ', '1', 'DeviceMotionControlSequence'],
        '0x0451': ['CS', '1', 'DeviceMotionExecutionMode'],
        '0x0452': ['CS', '1', 'DeviceMotionObservationMode'],
        '0x0453': ['SQ', '1', 'DeviceMotionParameterCodeSequence']
    },
    '0x300C': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0002': ['SQ', '1', 'ReferencedRTPlanSequence'],
        '0x0004': ['SQ', '1', 'ReferencedBeamSequence'],
        '0x0006': ['IS', '1', 'ReferencedBeamNumber'],
        '0x0007': ['IS', '1', 'ReferencedReferenceImageNumber'],
        '0x0008': ['DS', '1', 'StartCumulativeMetersetWeight'],
        '0x0009': ['DS', '1', 'EndCumulativeMetersetWeight'],
        '0x000A': ['SQ', '1', 'ReferencedBrachyApplicationSetupSequence'],
        '0x000C': ['IS', '1', 'ReferencedBrachyApplicationSetupNumber'],
        '0x000E': ['IS', '1', 'ReferencedSourceNumber'],
        '0x0020': ['SQ', '1', 'ReferencedFractionGroupSequence'],
        '0x0022': ['IS', '1', 'ReferencedFractionGroupNumber'],
        '0x0040': ['SQ', '1', 'ReferencedVerificationImageSequence'],
        '0x0042': ['SQ', '1', 'ReferencedReferenceImageSequence'],
        '0x0050': ['SQ', '1', 'ReferencedDoseReferenceSequence'],
        '0x0051': ['IS', '1', 'ReferencedDoseReferenceNumber'],
        '0x0055': ['SQ', '1', 'BrachyReferencedDoseReferenceSequence'],
        '0x0060': ['SQ', '1', 'ReferencedStructureSetSequence'],
        '0x006A': ['IS', '1', 'ReferencedPatientSetupNumber'],
        '0x0080': ['SQ', '1', 'ReferencedDoseSequence'],
        '0x00A0': ['IS', '1', 'ReferencedToleranceTableNumber'],
        '0x00B0': ['SQ', '1', 'ReferencedBolusSequence'],
        '0x00C0': ['IS', '1', 'ReferencedWedgeNumber'],
        '0x00D0': ['IS', '1', 'ReferencedCompensatorNumber'],
        '0x00E0': ['IS', '1', 'ReferencedBlockNumber'],
        '0x00F0': ['IS', '1', 'ReferencedControlPointIndex'],
        '0x00F2': ['SQ', '1', 'ReferencedControlPointSequence'],
        '0x00F4': ['IS', '1', 'ReferencedStartControlPointIndex'],
        '0x00F6': ['IS', '1', 'ReferencedStopControlPointIndex'],
        '0x0100': ['IS', '1', 'ReferencedRangeShifterNumber'],
        '0x0102': ['IS', '1', 'ReferencedLateralSpreadingDeviceNumber'],
        '0x0104': ['IS', '1', 'ReferencedRangeModulatorNumber'],
        '0x0111': ['SQ', '1', 'OmittedBeamTaskSequence'],
        '0x0112': ['CS', '1', 'ReasonForOmission'],
        '0x0113': ['LO', '1', 'ReasonForOmissionDescription']
    },
    '0x300E': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0002': ['CS', '1', 'ApprovalStatus'],
        '0x0004': ['DA', '1', 'ReviewDate'],
        '0x0005': ['TM', '1', 'ReviewTime'],
        '0x0008': ['PN', '1', 'ReviewerName']
    },
    '0x4000': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0010': ['LT', '1', 'Arbitrary'],
        '0x4000': ['LT', '1', 'TextComments']
    },
    '0x4008': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0040': ['SH', '1', 'ResultsID'],
        '0x0042': ['LO', '1', 'ResultsIDIssuer'],
        '0x0050': ['SQ', '1', 'ReferencedInterpretationSequence'],
        '0x00FF': ['CS', '1', 'ReportProductionStatusTrial'],
        '0x0100': ['DA', '1', 'InterpretationRecordedDate'],
        '0x0101': ['TM', '1', 'InterpretationRecordedTime'],
        '0x0102': ['PN', '1', 'InterpretationRecorder'],
        '0x0103': ['LO', '1', 'ReferenceToRecordedSound'],
        '0x0108': ['DA', '1', 'InterpretationTranscriptionDate'],
        '0x0109': ['TM', '1', 'InterpretationTranscriptionTime'],
        '0x010A': ['PN', '1', 'InterpretationTranscriber'],
        '0x010B': ['ST', '1', 'InterpretationText'],
        '0x010C': ['PN', '1', 'InterpretationAuthor'],
        '0x0111': ['SQ', '1', 'InterpretationApproverSequence'],
        '0x0112': ['DA', '1', 'InterpretationApprovalDate'],
        '0x0113': ['TM', '1', 'InterpretationApprovalTime'],
        '0x0114': ['PN', '1', 'PhysicianApprovingInterpretation'],
        '0x0115': ['LT', '1', 'InterpretationDiagnosisDescription'],
        '0x0117': ['SQ', '1', 'InterpretationDiagnosisCodeSequence'],
        '0x0118': ['SQ', '1', 'ResultsDistributionListSequence'],
        '0x0119': ['PN', '1', 'DistributionName'],
        '0x011A': ['LO', '1', 'DistributionAddress'],
        '0x0200': ['SH', '1', 'InterpretationID'],
        '0x0202': ['LO', '1', 'InterpretationIDIssuer'],
        '0x0210': ['CS', '1', 'InterpretationTypeID'],
        '0x0212': ['CS', '1', 'InterpretationStatusID'],
        '0x0300': ['ST', '1', 'Impressions'],
        '0x4000': ['ST', '1', 'ResultsComments']
    },
    '0x4010': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0001': ['CS', '1', 'LowEnergyDetectors'],
        '0x0002': ['CS', '1', 'HighEnergyDetectors'],
        '0x0004': ['SQ', '1', 'DetectorGeometrySequence'],
        '0x1001': ['SQ', '1', 'ThreatROIVoxelSequence'],
        '0x1004': ['FL', '3', 'ThreatROIBase'],
        '0x1005': ['FL', '3', 'ThreatROIExtents'],
        '0x1006': ['OB', '1', 'ThreatROIBitmap'],
        '0x1007': ['SH', '1', 'RouteSegmentID'],
        '0x1008': ['CS', '1', 'GantryType'],
        '0x1009': ['CS', '1', 'OOIOwnerType'],
        '0x100A': ['SQ', '1', 'RouteSegmentSequence'],
        '0x1010': ['US', '1', 'PotentialThreatObjectID'],
        '0x1011': ['SQ', '1', 'ThreatSequence'],
        '0x1012': ['CS', '1', 'ThreatCategory'],
        '0x1013': ['LT', '1', 'ThreatCategoryDescription'],
        '0x1014': ['CS', '1', 'ATDAbilityAssessment'],
        '0x1015': ['CS', '1', 'ATDAssessmentFlag'],
        '0x1016': ['FL', '1', 'ATDAssessmentProbability'],
        '0x1017': ['FL', '1', 'Mass'],
        '0x1018': ['FL', '1', 'Density'],
        '0x1019': ['FL', '1', 'ZEffective'],
        '0x101A': ['SH', '1', 'BoardingPassID'],
        '0x101B': ['FL', '3', 'CenterOfMass'],
        '0x101C': ['FL', '3', 'CenterOfPTO'],
        '0x101D': ['FL', '6-n', 'BoundingPolygon'],
        '0x101E': ['SH', '1', 'RouteSegmentStartLocationID'],
        '0x101F': ['SH', '1', 'RouteSegmentEndLocationID'],
        '0x1020': ['CS', '1', 'RouteSegmentLocationIDType'],
        '0x1021': ['CS', '1-n', 'AbortReason'],
        '0x1023': ['FL', '1', 'VolumeOfPTO'],
        '0x1024': ['CS', '1', 'AbortFlag'],
        '0x1025': ['DT', '1', 'RouteSegmentStartTime'],
        '0x1026': ['DT', '1', 'RouteSegmentEndTime'],
        '0x1027': ['CS', '1', 'TDRType'],
        '0x1028': ['CS', '1', 'InternationalRouteSegment'],
        '0x1029': ['LO', '1-n', 'ThreatDetectionAlgorithmandVersion'],
        '0x102A': ['SH', '1', 'AssignedLocation'],
        '0x102B': ['DT', '1', 'AlarmDecisionTime'],
        '0x1031': ['CS', '1', 'AlarmDecision'],
        '0x1033': ['US', '1', 'NumberOfTotalObjects'],
        '0x1034': ['US', '1', 'NumberOfAlarmObjects'],
        '0x1037': ['SQ', '1', 'PTORepresentationSequence'],
        '0x1038': ['SQ', '1', 'ATDAssessmentSequence'],
        '0x1039': ['CS', '1', 'TIPType'],
        '0x103A': ['CS', '1', 'DICOSVersion'],
        '0x1041': ['DT', '1', 'OOIOwnerCreationTime'],
        '0x1042': ['CS', '1', 'OOIType'],
        '0x1043': ['FL', '3', 'OOISize'],
        '0x1044': ['CS', '1', 'AcquisitionStatus'],
        '0x1045': ['SQ', '1', 'BasisMaterialsCodeSequence'],
        '0x1046': ['CS', '1', 'PhantomType'],
        '0x1047': ['SQ', '1', 'OOIOwnerSequence'],
        '0x1048': ['CS', '1', 'ScanType'],
        '0x1051': ['LO', '1', 'ItineraryID'],
        '0x1052': ['SH', '1', 'ItineraryIDType'],
        '0x1053': ['LO', '1', 'ItineraryIDAssigningAuthority'],
        '0x1054': ['SH', '1', 'RouteID'],
        '0x1055': ['SH', '1', 'RouteIDAssigningAuthority'],
        '0x1056': ['CS', '1', 'InboundArrivalType'],
        '0x1058': ['SH', '1', 'CarrierID'],
        '0x1059': ['CS', '1', 'CarrierIDAssigningAuthority'],
        '0x1060': ['FL', '3', 'SourceOrientation'],
        '0x1061': ['FL', '3', 'SourcePosition'],
        '0x1062': ['FL', '1', 'BeltHeight'],
        '0x1064': ['SQ', '1', 'AlgorithmRoutingCodeSequence'],
        '0x1067': ['CS', '1', 'TransportClassification'],
        '0x1068': ['LT', '1', 'OOITypeDescriptor'],
        '0x1069': ['FL', '1', 'TotalProcessingTime'],
        '0x106C': ['OB', '1', 'DetectorCalibrationData'],
        '0x106D': ['CS', '1', 'AdditionalScreeningPerformed'],
        '0x106E': ['CS', '1', 'AdditionalInspectionSelectionCriteria'],
        '0x106F': ['SQ', '1', 'AdditionalInspectionMethodSequence'],
        '0x1070': ['CS', '1', 'AITDeviceType'],
        '0x1071': ['SQ', '1', 'QRMeasurementsSequence'],
        '0x1072': ['SQ', '1', 'TargetMaterialSequence'],
        '0x1073': ['FD', '1', 'SNRThreshold'],
        '0x1075': ['DS', '1', 'ImageScaleRepresentation'],
        '0x1076': ['SQ', '1', 'ReferencedPTOSequence'],
        '0x1077': ['SQ', '1', 'ReferencedTDRInstanceSequence'],
        '0x1078': ['ST', '1', 'PTOLocationDescription'],
        '0x1079': ['SQ', '1', 'AnomalyLocatorIndicatorSequence'],
        '0x107A': ['FL', '3', 'AnomalyLocatorIndicator'],
        '0x107B': ['SQ', '1', 'PTORegionSequence'],
        '0x107C': ['CS', '1', 'InspectionSelectionCriteria'],
        '0x107D': ['SQ', '1', 'SecondaryInspectionMethodSequence'],
        '0x107E': ['DS', '6', 'PRCSToRCSOrientation']
    },
    '0x4FFE': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0001': ['SQ', '1', 'MACParametersSequence']
    },
    '0x5000': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0005': ['US', '1', 'CurveDimensions'],
        '0x0010': ['US', '1', 'NumberOfPoints'],
        '0x0020': ['CS', '1', 'TypeOfData'],
        '0x0022': ['LO', '1', 'CurveDescription'],
        '0x0030': ['SH', '1-n', 'AxisUnits'],
        '0x0040': ['SH', '1-n', 'AxisLabels'],
        '0x0103': ['US', '1', 'DataValueRepresentation'],
        '0x0104': ['US', '1-n', 'MinimumCoordinateValue'],
        '0x0105': ['US', '1-n', 'MaximumCoordinateValue'],
        '0x0106': ['SH', '1-n', 'CurveRange'],
        '0x0110': ['US', '1-n', 'CurveDataDescriptor'],
        '0x0112': ['US', '1-n', 'CoordinateStartValue'],
        '0x0114': ['US', '1-n', 'CoordinateStepValue'],
        '0x1001': ['CS', '1', 'CurveActivationLayer'],
        '0x2000': ['US', '1', 'AudioType'],
        '0x2002': ['US', '1', 'AudioSampleFormat'],
        '0x2004': ['US', '1', 'NumberOfChannels'],
        '0x2006': ['UL', '1', 'NumberOfSamples'],
        '0x2008': ['UL', '1', 'SampleRate'],
        '0x200A': ['UL', '1', 'TotalTime'],
        '0x200C': ['ox', '1', 'AudioSampleData'],
        '0x200E': ['LT', '1', 'AudioComments'],
        '0x2500': ['LO', '1', 'CurveLabel'],
        '0x2600': ['SQ', '1', 'CurveReferencedOverlaySequence'],
        '0x2610': ['US', '1', 'CurveReferencedOverlayGroup'],
        '0x3000': ['ox', '1', 'CurveData']
    },
    '0x5200': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x9229': ['SQ', '1', 'SharedFunctionalGroupsSequence'],
        '0x9230': ['SQ', '1', 'PerFrameFunctionalGroupsSequence']
    },
    '0x5400': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0100': ['SQ', '1', 'WaveformSequence'],
        '0x0110': ['ox', '1', 'ChannelMinimumValue'],
        '0x0112': ['ox', '1', 'ChannelMaximumValue'],
        '0x1004': ['US', '1', 'WaveformBitsAllocated'],
        '0x1006': ['CS', '1', 'WaveformSampleInterpretation'],
        '0x100A': ['ox', '1', 'WaveformPaddingValue'],
        '0x1010': ['ox', '1', 'WaveformData']
    },
    '0x5600': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0010': ['OF', '1', 'FirstOrderPhaseCorrectionAngle'],
        '0x0020': ['OF', '1', 'SpectroscopyData']
    },
    '0x6000': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0010': ['US', '1', 'OverlayRows'],
        '0x0011': ['US', '1', 'OverlayColumns'],
        '0x0012': ['US', '1', 'OverlayPlanes'],
        '0x0015': ['IS', '1', 'NumberOfFramesInOverlay'],
        '0x0022': ['LO', '1', 'OverlayDescription'],
        '0x0040': ['CS', '1', 'OverlayType'],
        '0x0045': ['LO', '1', 'OverlaySubtype'],
        '0x0050': ['SS', '2', 'OverlayOrigin'],
        '0x0051': ['US', '1', 'ImageFrameOrigin'],
        '0x0052': ['US', '1', 'OverlayPlaneOrigin'],
        '0x0060': ['CS', '1', 'OverlayCompressionCode'],
        '0x0061': ['SH', '1', 'OverlayCompressionOriginator'],
        '0x0062': ['SH', '1', 'OverlayCompressionLabel'],
        '0x0063': ['CS', '1', 'OverlayCompressionDescription'],
        '0x0066': ['AT', '1-n', 'OverlayCompressionStepPointers'],
        '0x0068': ['US', '1', 'OverlayRepeatInterval'],
        '0x0069': ['US', '1', 'OverlayBitsGrouped'],
        '0x0100': ['US', '1', 'OverlayBitsAllocated'],
        '0x0102': ['US', '1', 'OverlayBitPosition'],
        '0x0110': ['CS', '1', 'OverlayFormat'],
        '0x0200': ['US', '1', 'OverlayLocation'],
        '0x0800': ['CS', '1-n', 'OverlayCodeLabel'],
        '0x0802': ['US', '1', 'OverlayNumberOfTables'],
        '0x0803': ['AT', '1-n', 'OverlayCodeTableLocation'],
        '0x0804': ['US', '1', 'OverlayBitsForCodeWord'],
        '0x1001': ['CS', '1', 'OverlayActivationLayer'],
        '0x1100': ['US', '1', 'OverlayDescriptorGray'],
        '0x1101': ['US', '1', 'OverlayDescriptorRed'],
        '0x1102': ['US', '1', 'OverlayDescriptorGreen'],
        '0x1103': ['US', '1', 'OverlayDescriptorBlue'],
        '0x1200': ['US', '1-n', 'OverlaysGray'],
        '0x1201': ['US', '1-n', 'OverlaysRed'],
        '0x1202': ['US', '1-n', 'OverlaysGreen'],
        '0x1203': ['US', '1-n', 'OverlaysBlue'],
        '0x1301': ['IS', '1', 'ROIArea'],
        '0x1302': ['DS', '1', 'ROIMean'],
        '0x1303': ['DS', '1', 'ROIStandardDeviation'],
        '0x1500': ['LO', '1', 'OverlayLabel'],
        '0x3000': ['ox', '1', 'OverlayData'],
        '0x4000': ['LT', '1', 'OverlayComments']
    },
    '0x7FE0': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0008': ['OF', '1', 'FloatPixelData'],
        '0x0009': ['OD', '1', 'DoubleFloatPixelData'],
        '0x0010': ['ox', '1', 'PixelData'],
        '0x0020': ['OW', '1', 'CoefficientsSDVN'],
        '0x0030': ['OW', '1', 'CoefficientsSDHN'],
        '0x0040': ['OW', '1', 'CoefficientsSDDN']
    },
    '0x7F00': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0x0010': ['ox', '1', 'VariablePixelData'],
        '0x0011': ['US', '1', 'VariableNextDataGroup'],
        '0x0020': ['OW', '1', 'VariableCoefficientsSDVN'],
        '0x0030': ['OW', '1', 'VariableCoefficientsSDHN'],
        '0x0040': ['OW', '1', 'VariableCoefficientsSDDN']
    },
    '0xFFFA': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0xFFFA': ['SQ', '1', 'DigitalSignaturesSequence']
    },
    '0xFFFC': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0xFFFC': ['OB', '1', 'DataSetTrailingPadding']
    },
    '0xFFFE': {
        '0x0000': ['UL', '1', 'GenericGroupLength'],
        '0xE000': ['NONE', '1', 'Item'],
        '0xE00D': ['NONE', '1', 'ItemDelimitationItem'],
        '0xE0DD': ['NONE', '1', 'SequenceDelimitationItem']
    }
}; // dwv.dicom.Dictionnary

// taken from gdcm-2.6.1\Source\DataDictionary\GroupName.dic
// -> removed duplicates (commented)
dwv.dicom.TagGroups = {
  'x0000' :'Command',
  'x0002': 'Meta Element',
  'x0004': 'File Set',
  //'x0004': 'Directory',
  'x0008': 'Identifying',
  'x0009': 'SPI Identifying',
  'x0010': 'Patient',
  'x0012': 'Clinical Trial',
  'x0018': 'Acquisition',
  'x0019': 'SPI Acquisition',
  'x0020': 'Image',
  'x0021': 'SPI Image',
  'x0022': 'Ophtalmology',
  'x0028': 'Image Presentation',
  'x0032': 'Study',
  'x0038': 'Visit',
  'x003A': 'Waveform',
  'x0040': 'Procedure',
  //'x0040': ''Modality Worklist',
  'x0042': 'Encapsulated Document',
  'x0050': 'Device Informations',
  //'x0050': 'XRay Angio Device',
  'x0054': 'Nuclear Medicine',
  'x0060': 'Histogram',
  'x0070': 'Presentation State',
  'x0072': 'Hanging Protocol',
  'x0088': 'Storage',
  //'x0088': 'Medicine',
  'x0100': 'Authorization',
  'x0400': 'Digital Signature',
  'x1000': 'Code Table',
  'x1010': 'Zonal Map',
  'x2000': 'Film Session',
  'x2010': 'Film Box',
  'x2020': 'Image Box',
  'x2030': 'Annotation',
  'x2040': 'Overlay Box',
  'x2050': 'Presentation LUT',
  'x2100': 'Print Job',
  'x2110': 'Printer',
  'x2120': 'Queue',
  'x2130': 'Print Content',
  'x2200': 'Media Creation',
  'x3002': 'RT Image',
  'x3004': 'RT Dose',
  'x3006': 'RT StructureSet',
  'x3008': 'RT Treatment',
  'x300A': 'RT Plan',
  'x300C': 'RT Relationship',
  'x300E': 'RT Approval',
  'x4000': 'Text',
  'x4008': 'Results',
  'x4FFE': 'MAC Parameters',
  'x5000': 'Curve',
  'x5002': 'Curve',
  'x5004': 'Curve',
  'x5006': 'Curve',
  'x5008': 'Curve',
  'x500A': 'Curve',
  'x500C': 'Curve',
  'x500E': 'Curve',
  'x5400': 'Waveform Data',
  'x6000': 'Overlays',
  'x6002': 'Overlays',
  'x6004': 'Overlays',
  'x6008': 'Overlays',
  'x600A': 'Overlays',
  'x600C': 'Overlays',
  'x600E': 'Overlays',
  'xFFFC': 'Generic',
  'x7FE0': 'Pixel Data',
  'xFFFF': 'Unknown'
  };

// namespaces
var dwv = dwv || {};
/** @namespace */
dwv.gui = dwv.gui || {};
/** @namespace */
dwv.gui.base = dwv.gui.base || {};
/** @namespace */
dwv.gui.filter = dwv.gui.filter || {};
/** @namespace */
dwv.gui.filter.base = dwv.gui.filter.base || {};

/**
 * Filter tool base gui.
 * @constructor
 */
dwv.gui.base.Filter = function (app)
{
    /**
     * Setup the filter tool HTML.
     */
    this.setup = function (list)
    {
        // filter select
        var filterSelector = dwv.html.createHtmlSelect("filterSelect", list, "filter");
        filterSelector.onchange = app.onChangeFilter;

        // filter list element
        var filterLi = dwv.html.createHiddenElement("li", "filterLi");
        filterLi.className += " ui-block-b";
        filterLi.appendChild(filterSelector);

        // append element
        var node = app.getElement("toolList").getElementsByTagName("ul")[0];
        dwv.html.appendElement(node, filterLi);
    };

    /**
     * Display the tool HTML.
     * @param {Boolean} flag True to display, false to hide.
     */
    this.display = function (flag)
    {
        var node = app.getElement("filterLi");
        dwv.html.displayElement(node, flag);
    };

    /**
     * Initialise the tool HTML.
     */
    this.initialise = function ()
    {
        // filter select: reset selected options
        var filterSelector = app.getElement("filterSelect");
        filterSelector.selectedIndex = 0;
        // refresh
        dwv.gui.refreshElement(filterSelector);
    };

}; // class dwv.gui.base.Filter

/**
 * Threshold filter base gui.
 * @constructor
 */
dwv.gui.base.Threshold = function (app)
{
    /**
     * Threshold slider.
     * @private
     * @type Object
     */
    var slider = new dwv.gui.Slider(app);

    /**
     * Setup the threshold filter HTML.
     */
    this.setup = function ()
    {
        // threshold list element
        var thresholdLi = dwv.html.createHiddenElement("li", "thresholdLi");
        thresholdLi.className += " ui-block-c";

        // node
        var node = app.getElement("toolList").getElementsByTagName("ul")[0];
        // append threshold
        node.appendChild(thresholdLi);
        // threshold slider
        slider.append();
        // refresh
        dwv.gui.refreshElement(node);
    };

    /**
     * Clear the threshold filter HTML.
     * @param {Boolean} flag True to display, false to hide.
     */
    this.display = function (flag)
    {
        // only initialise at display time
        // (avoids min/max calculation at startup)
        if (flag) {
            slider.initialise();
        }
        
        var node = app.getElement("thresholdLi");
        dwv.html.displayElement(node, flag);
    };

    /**
     * Initialise the threshold filter HTML.
     */
    this.initialise = function ()
    {
        // nothing to do
    };

}; // class dwv.gui.base.Threshold

/**
 * Create the apply filter button.
 */
dwv.gui.filter.base.createFilterApplyButton = function (app)
{
    var button = document.createElement("button");
    button.id = "runFilterButton";
    button.onclick = app.onRunFilter;
    button.setAttribute("style","width:100%; margin-top:0.5em;");
    button.setAttribute("class","ui-btn ui-btn-b");
    button.appendChild(document.createTextNode(dwv.i18n("basics.apply")));
    return button;
};

/**
 * Sharpen filter base gui.
 * @constructor
 */
dwv.gui.base.Sharpen = function (app)
{
    /**
     * Setup the sharpen filter HTML.
     */
    this.setup = function ()
    {
        // sharpen list element
        var sharpenLi = dwv.html.createHiddenElement("li", "sharpenLi");
        sharpenLi.className += " ui-block-c";
        sharpenLi.appendChild( dwv.gui.filter.base.createFilterApplyButton(app) );
        // append element
        var node = app.getElement("toolList").getElementsByTagName("ul")[0];
        dwv.html.appendElement(node, sharpenLi);
    };

    /**
     * Display the sharpen filter HTML.
     * @param {Boolean} flag True to display, false to hide.
     */
    this.display = function (flag)
    {
        var node = app.getElement("sharpenLi");
        dwv.html.displayElement(node, flag);
    };

}; // class dwv.gui.base.Sharpen

/**
 * Sobel filter base gui.
 * @constructor
 */
dwv.gui.base.Sobel = function (app)
{
    /**
     * Setup the sobel filter HTML.
     */
    this.setup = function ()
    {
        // sobel list element
        var sobelLi = dwv.html.createHiddenElement("li", "sobelLi");
        sobelLi.className += " ui-block-c";
        sobelLi.appendChild( dwv.gui.filter.base.createFilterApplyButton(app) );
        // append element
        var node = app.getElement("toolList").getElementsByTagName("ul")[0];
        dwv.html.appendElement(node, sobelLi);
    };

    /**
     * Display the sobel filter HTML.
     * @param {Boolean} flag True to display, false to hide.
     */
    this.display = function (flag)
    {
        var node = app.getElement("sobelLi");
        dwv.html.displayElement(node, flag);
    };

}; // class dwv.gui.base.Sobel

// namespaces
var dwv = dwv || {};
dwv.gui = dwv.gui || {};
dwv.gui.base = dwv.gui.base || {};

/**
 * Get the size of the image display window.
 */
dwv.gui.base.getWindowSize = function ()
{
    return { 'width': window.innerWidth, 'height': window.innerHeight - 147 };
};

/**
 * Ask some text to the user.
 * @param {String} message Text to display to the user.
 * @param {String} defaultText Default value displayed in the text input field.
 * @return {String} Text entered by the user.
 */
dwv.gui.base.prompt = function (message, defaultText)
{
    return prompt(message, defaultText);
};

/**
 * Display a progress value.
 * @param {Number} percent The progress percentage.
 */
dwv.gui.base.displayProgress = function (/*percent*/)
{
    // default does nothing...
};

/**
 * Focus the view on the image.
 */
dwv.gui.base.focusImage = function ()
{
    // default does nothing...
};

/**
 * Post process a HTML table.
 * @param {Object} table The HTML table to process.
 * @return The processed HTML table.
 */
dwv.gui.base.postProcessTable = function (/*table*/)
{
    // default does nothing...
};

/**
 * Get a HTML element associated to a container div.
 * @param {Number} containerDivId The id of the container div.
 * @param {String} name The name or id to find.
 * @return {Object} The found element or null.
 */
dwv.gui.base.getElement = function (containerDivId, name)
{
    // get by class in the container div
    var parent = document.getElementById(containerDivId);
    var elements = parent.getElementsByClassName(name);
    // getting the last element since some libraries (ie jquery-mobile) creates
    // span in front of regular tags (such as select)...
    var element = elements[elements.length-1];
    // if not found get by id with 'containerDivId-className'
    if ( typeof element === "undefined" ) {
        element = document.getElementById(containerDivId + '-' + name);
    }
    return element;
 };

 /**
 * Refresh a HTML element. Mainly for jquery-mobile.
 * @param {String} element The HTML element to refresh.
 */
dwv.gui.base.refreshElement = function (/*element*/)
{
    // base does nothing...
};

/**
 * Set the selected item of a HTML select.
 * @param {String} element The HTML select element.
 * @param {String} value The value of the option to mark as selected.
 */
dwv.gui.setSelected = function (element, value)
{
    if ( element ) {
        var index = 0;
        for( index in element.options){
            if( element.options[index].value === value ) {
                break;
            }
        }
        element.selectedIndex = index;
        dwv.gui.refreshElement(element);
    }
};

/**
 * Slider base gui.
 * @constructor
 */
dwv.gui.base.Slider = function (app)
{
    /**
     * Append the slider HTML.
     */
    this.append = function ()
    {
        // default values
        var min = 0;
        var max = 1;

        // jquery-mobile range slider
        // minimum input
        var inputMin = document.createElement("input");
        inputMin.id = "threshold-min";
        inputMin.type = "range";
        inputMin.max = max;
        inputMin.min = min;
        inputMin.value = min;
        // maximum input
        var inputMax = document.createElement("input");
        inputMax.id = "threshold-max";
        inputMax.type = "range";
        inputMax.max = max;
        inputMax.min = min;
        inputMax.value = max;
        // slicer div
        var div = document.createElement("div");
        div.id = "threshold-div";
        div.setAttribute("data-role", "rangeslider");
        div.appendChild(inputMin);
        div.appendChild(inputMax);
        div.setAttribute("data-mini", "true");
        // append to document
        app.getElement("thresholdLi").appendChild(div);
        // bind change
        $("#threshold-div").on("change",
                function(/*event*/) {
                    app.onChangeMinMax(
                        { "min":$("#threshold-min").val(),
                          "max":$("#threshold-max").val() } );
                }
            );
        // refresh
        dwv.gui.refreshElement(app.getElement("toolList"));
    };

    /**
     * Initialise the slider HTML.
     */
    this.initialise = function ()
    {
        var min = app.getImage().getDataRange().min;
        var max = app.getImage().getDataRange().max;

        // minimum input
        var inputMin = document.getElementById("threshold-min");
        inputMin.max = max;
        inputMin.min = min;
        inputMin.value = min;
        // maximum input
        var inputMax = document.getElementById("threshold-max");
        inputMax.max = max;
        inputMax.min = min;
        inputMax.value = max;
        // refresh
        dwv.gui.refreshElement(app.getElement("toolList"));
    };

}; // class dwv.gui.base.Slider

/**
 * DICOM tags base gui.
 * @param {Object} app The associated application.
 * @constructor
 */
dwv.gui.base.DicomTags = function (app)
{
    /**
     * Update the DICOM tags table with the input info.
     * @param {Object} dataInfo The data information.
     */
    this.update = function (dataInfo)
    {
        // HTML node
        var node = app.getElement("tags");
        if( node === null ) {
            console.warn("Cannot find a node to append the DICOM tags.");
            return;
        }
        // remove possible previous
        while (node.hasChildNodes()) {
            node.removeChild(node.firstChild);
        }

        // exit if no tags
        if (dataInfo.length === 0) {
            console.warn("No DICOM tags to show.");
            return;
        }

        // tags HTML table
        var table = dwv.html.toTable(dataInfo);
        table.className = "tagsTable";

        // optional gui specific table post process
        dwv.gui.postProcessTable(table);

        // check processed table
        if (table.rows.length === 0) {
            console.warn("The processed table does not contain data.");
            return;
        }
        
        // translate first row
        dwv.html.translateTableRow(table.rows.item(0));

        // append search form
        node.appendChild(dwv.html.getHtmlSearchForm(table));
        // append tags table
        node.appendChild(table);

        // refresh
        dwv.gui.refreshElement(node);
    };

}; // class dwv.gui.base.DicomTags

/**
 * Drawing list base gui.
 * @param {Object} app The associated application.
 * @constructor
 */
dwv.gui.base.DrawList = function (app)
{
    /**
     * Closure to self.
     */
    var self = this;

    /**
     * Update the draw list html element
     * @param {Object} event A change event, decides if the table is editable or not.
     */
    this.update = function (event)
    {
        var isEditable = false;
        if (typeof event.editable !== "undefined") {
            isEditable = event.editable;
        }

        // HTML node
        var node = app.getElement("drawList");
        if( node === null ) {
            console.warn("Cannot find a node to append the drawing list.");
            return;
        }
        // remove possible previous
        while (node.hasChildNodes()) {
            node.removeChild(node.firstChild);
        }

        // drawing details
        var drawDisplayDetails = app.getDrawDisplayDetails();

        // exit if no details
        if (drawDisplayDetails.length === 0) {
            return;
        }

        // tags HTML table
        var table = dwv.html.toTable(drawDisplayDetails);
        table.className = "drawsTable";

        // optional gui specific table post process
        dwv.gui.postProcessTable(table);

        // check processed table
        if (table.rows.length === 0) {
            console.warn("The processed table does not contain data.");
            return;
        }

        // translate first row
        dwv.html.translateTableRow(table.rows.item(0));

        // translate shape names
        dwv.html.translateTableColumn(table, 3, "shape", "name");

        // create a color onkeyup handler
        var createColorOnKeyUp = function (details) {
            return function () {
                details.color = this.value;
                app.updateDraw(details);
            };
        };
        // create a text onkeyup handler
        var createTextOnKeyUp = function (details) {
            return function () {
                details.label = this.value;
                app.updateDraw(details);
            };
        };
        // create a long text onkeyup handler
        var createLongTextOnKeyUp = function (details) {
            return function () {
                details.description = this.value;
                app.updateDraw(details);
            };
        };
        // create a row onclick handler
        var createRowOnClick = function (slice, frame) {
            return function () {
                // update slice
                var pos = app.getViewController().getCurrentPosition();
                pos.k = slice;
                app.getViewController().setCurrentPosition(pos);
                // update frame
                app.getViewController().setCurrentFrame(frame);
                // focus on the image
                dwv.gui.focusImage();
            };
        };
        // create visibility handler
        var createVisibleOnClick = function (details) {
            return function () {
                app.toogleGroupVisibility(details);
            };
        };

        // append visible column to the header row
        var row0 = table.rows.item(0);
        var cell00 = row0.insertCell(0);
        cell00.outerHTML = "<th>" + dwv.i18n("basics.visible") + "</th>";

        // loop through rows
        for (var r = 1; r < table.rows.length; ++r) {
            var drawId = r - 1;
            var drawDetails = drawDisplayDetails[drawId];
            var row = table.rows.item(r);
            var cells = row.cells;

            // loop through cells
            for (var c = 0; c < cells.length; ++c) {
                // show short ID
                if (c === 0) {
                    cells[c].firstChild.data = cells[c].firstChild.data.substring(0, 5);
                }

                if (isEditable) {
                    // color
                    if (c === 4) {
                        dwv.html.makeCellEditable(cells[c], createColorOnKeyUp(drawDetails), "color");
                    }
                    // text
                    else if (c === 5) {
                        dwv.html.makeCellEditable(cells[c], createTextOnKeyUp(drawDetails));
                    }
                    // long text
                    else if (c === 6) {
                        dwv.html.makeCellEditable(cells[c], createLongTextOnKeyUp(drawDetails));
                    }
                }
                else {
                    // id: link to image
                    cells[0].onclick = createRowOnClick(
                        cells[1].firstChild.data,
                        cells[2].firstChild.data);
                    cells[0].onmouseover = dwv.html.setCursorToPointer;
                    cells[0].onmouseout = dwv.html.setCursorToDefault;
                    // color: just display the input color with no callback
                    if (c === 4) {
                        dwv.html.makeCellEditable(cells[c], null, "color");
                    }
                }
            }

            // append visible column
            var cell0 = row.insertCell(0);
            var input = document.createElement("input");
            input.setAttribute("type", "checkbox");
            input.checked = app.isGroupVisible(drawDetails);
            input.onclick = createVisibleOnClick(drawDetails);
            cell0.appendChild(input);
        }

        // editable checkbox
        var tickBox = document.createElement("input");
        tickBox.setAttribute("type", "checkbox");
        tickBox.id = "checkbox-editable";
        tickBox.checked = isEditable;
        tickBox.onclick = function () { self.update({"editable": this.checked}); };
        // checkbox label
        var tickLabel = document.createElement("label");
        tickLabel.setAttribute( "for", tickBox.id );
        tickLabel.setAttribute( "class", "inline" );
        tickLabel.appendChild( document.createTextNode( dwv.i18n("basics.editMode") ) );
        // checkbox div
        var tickDiv = document.createElement("div");
        tickDiv.appendChild(tickLabel);
        tickDiv.appendChild(tickBox);

        // search form
        node.appendChild(dwv.html.getHtmlSearchForm(table));
        // tick form
        node.appendChild(tickDiv);

        // draw list table
        node.appendChild(table);

        // delete draw button
        var deleteButton = document.createElement("button");
        deleteButton.onclick = function () { app.deleteDraws(); };
        deleteButton.setAttribute( "class", "ui-btn ui-btn-inline" );
        deleteButton.appendChild( document.createTextNode( dwv.i18n("basics.deleteDraws") ) );
        if (!isEditable) {
            deleteButton.style.display = "none";
        }
        node.appendChild(deleteButton);

        // refresh
        dwv.gui.refreshElement(node);
    };

}; // class dwv.gui.base.DrawList

// namespaces
var dwv = dwv || {};
dwv.gui = dwv.gui || {};
dwv.gui.base = dwv.gui.base || {};

/**
 * Append the version HTML.
 */
dwv.gui.base.appendVersionHtml = function (version)
{
    var nodes = document.getElementsByClassName("dwv-version");
    if ( nodes ) {
        for( var i = 0; i < nodes.length; ++i ){
            nodes[i].appendChild( document.createTextNode(version) );
        }
    }
};

/**
 * Build the help HTML.
 * @param {Boolean} mobile Flag for mobile or not environement.
 */
dwv.gui.base.appendHelpHtml = function(toolList, mobile, app)
{
    var actionType = "mouse";
    if( mobile ) {
        actionType = "touch";
    }

    var toolHelpDiv = document.createElement("div");

    // current location
    var loc = window.location.pathname;
    var dir = loc.substring(0, loc.lastIndexOf('/'));

    var tool = null;
    var tkeys = Object.keys(toolList);
    for ( var t=0; t < tkeys.length; ++t )
    {
        tool = toolList[tkeys[t]];
        // title
        var title = document.createElement("h3");
        title.appendChild(document.createTextNode(tool.getHelp().title));
        // doc div
        var docDiv = document.createElement("div");
        // brief
        var brief = document.createElement("p");
        brief.appendChild(document.createTextNode(tool.getHelp().brief));
        docDiv.appendChild(brief);
        // details
        if( tool.getHelp()[actionType] ) {
            var keys = Object.keys(tool.getHelp()[actionType]);
            for( var i=0; i<keys.length; ++i )
            {
                var action = tool.getHelp()[actionType][keys[i]];

                var img = document.createElement("img");
                img.src = dir + "/../../resources/help/"+keys[i]+".png";
                img.style.float = "left";
                img.style.margin = "0px 15px 15px 0px";

                var br = document.createElement("br");
                br.style.clear = "both";

                var para = document.createElement("p");
                para.appendChild(img);
                para.appendChild(document.createTextNode(action));
                para.appendChild(br);
                docDiv.appendChild(para);
            }
        }

        // different div structure for mobile or static
        if( mobile )
        {
            var toolDiv = document.createElement("div");
            toolDiv.setAttribute("data-role", "collapsible");
            toolDiv.appendChild(title);
            toolDiv.appendChild(docDiv);
            toolHelpDiv.appendChild(toolDiv);
        }
        else
        {
            toolHelpDiv.id = "accordion";
            toolHelpDiv.appendChild(title);
            toolHelpDiv.appendChild(docDiv);
        }
    }

    var helpNode = app.getElement("help");

    var headPara = document.createElement("p");
    headPara.appendChild(document.createTextNode(dwv.i18n("help.intro.p0")));
    helpNode.appendChild(headPara);

    var secondPara = document.createElement("p");
    secondPara.appendChild(document.createTextNode(dwv.i18n("help.intro.p1")));
    helpNode.appendChild(secondPara);

    var toolPara = document.createElement("p");
    toolPara.appendChild(document.createTextNode(dwv.i18n("help.tool_intro")));
    helpNode.appendChild(toolPara);
    helpNode.appendChild(toolHelpDiv);
};

// namespaces
var dwv = dwv || {};
/** @namespace */
dwv.html = dwv.html || {};

/**
 * Append a cell to a given row.
 * @param {Object} row The row to append the cell to.
 * @param {Object} content The content of the cell.
 */
dwv.html.appendCell = function (row, content)
{
    var cell = row.insertCell(-1);
    var str = content;
    // special care for arrays
    if ( content instanceof Array ||
            content instanceof Uint8Array || content instanceof Int8Array ||
            content instanceof Uint16Array || content instanceof Int16Array ||
            content instanceof Uint32Array ) {
        if ( content.length > 10 ) {
            content = Array.prototype.slice.call( content, 0, 10 );
            content[10] = "...";
        }
        str = Array.prototype.join.call( content, ', ' );
    }
    // append
    cell.appendChild(document.createTextNode(str));
};

/**
 * Append a header cell to a given row.
 * @param {Object} row The row to append the header cell to.
 * @param {String} text The text of the header cell.
 */
dwv.html.appendHCell = function (row, text)
{
    var cell = document.createElement("th");
    cell.appendChild(document.createTextNode(text));
    row.appendChild(cell);
};

/**
 * Append a row to an array.
 * @param {Object} table The HTML table to append a row to.
 * @param {Array} input The input row array.
 * @param {Number} level The depth level of the input array.
 * @param {Number} maxLevel The maximum depth level.
 * @param {String} rowHeader The content of the first cell of a row (mainly for objects).
 */
dwv.html.appendRowForArray = function (table, input, level, maxLevel, rowHeader)
{
    var row = null;
    // loop through
    for ( var i=0; i<input.length; ++i ) {
        var value = input[i];
        // last level
        if ( typeof value === 'number' ||
                typeof value === 'string' ||
                value === null ||
                value === undefined ||
                level >= maxLevel ) {
            if ( !row ) {
                row = table.insertRow(-1);
            }
            dwv.html.appendCell(row, value);
        }
        // more to come
        else {
            dwv.html.appendRow(table, value, level+i, maxLevel, rowHeader);
        }
    }
};

/**
 * Append a row to an object.
 * @param {Object} table The HTML table to append a row to.
 * @param {Array} input The input row array.
 * @param {Number} level The depth level of the input array.
 * @param {Number} maxLevel The maximum depth level.
 * @param {String} rowHeader The content of the first cell of a row (mainly for objects).
 */
dwv.html.appendRowForObject = function (table, input, level, maxLevel, rowHeader)
{
    var keys = Object.keys(input);
    var row = null;
    for ( var o=0; o<keys.length; ++o ) {
        var value = input[keys[o]];
        // last level
        if ( typeof value === 'number' ||
                typeof value === 'string' ||
                value === null ||
                value === undefined ||
                level >= maxLevel ) {
            if ( !row ) {
                row = table.insertRow(-1);
            }
            if ( o === 0 && rowHeader) {
                dwv.html.appendCell(row, rowHeader);
            }
            dwv.html.appendCell(row, value);
        }
        // more to come
        else {
            dwv.html.appendRow(table, value, level+o, maxLevel, keys[o]);
        }
    }
    // header row
    // warn: need to create the header after the rest
    // otherwise the data will inserted in the thead...
    if ( level === 2 ) {
        var header = table.createTHead();
        var th = header.insertRow(-1);
        if ( rowHeader ) {
            dwv.html.appendHCell(th, "");
        }
        for ( var k=0; k<keys.length; ++k ) {
            dwv.html.appendHCell(th, keys[k]);
        }
    }
};

/**
 * Append a row to an object or an array.
 * @param {Object} table The HTML table to append a row to.
 * @param {Array} input The input row array.
 * @param {Number} level The depth level of the input array.
 * @param {Number} maxLevel The maximum depth level.
 * @param {String} rowHeader The content of the first cell of a row (mainly for objects).
 */
dwv.html.appendRow = function (table, input, level, maxLevel, rowHeader)
{
    // array
    if ( input instanceof Array ) {
        dwv.html.appendRowForArray(table, input, level+1, maxLevel, rowHeader);
    }
    // object
    else if ( typeof input === 'object') {
        dwv.html.appendRowForObject(table, input, level+1, maxLevel, rowHeader);
    }
    else {
        throw new Error("Unsupported input data type.");
    }
};

/**
 * Converts the input to an HTML table.
 * @input {Mixed} input Allowed types are: array, array of object, object.
 * @return {Object} The created HTML table or null if the input is empty.
 * @warning Null is interpreted differently in browsers, firefox will not display it.
 */
dwv.html.toTable = function (input)
{
    // check content
    if (input.length === 0) {
        return null;
    }

    var table = document.createElement('table');
    dwv.html.appendRow(table, input, 0, 2);
    return table;
};

/**
 * Get an HTML search form.
 * @param {Object} htmlTableToSearch The table to do the search on.
 * @return {Object} The HTML search form.
 */
dwv.html.getHtmlSearchForm = function (htmlTableToSearch)
{
    // input
    var input = document.createElement("input");
    input.id = "table-search";
    // TODO Use new html5 search type
    //input.setAttribute("type", "search");
    input.onkeyup = function () {
        dwv.html.filterTable(input, htmlTableToSearch);
    };
    // label
    var label = document.createElement("label");
    label.setAttribute("for", input.id);
    label.appendChild(document.createTextNode(dwv.i18n("basics.search") + ": "));
    // form
    var form = document.createElement("form");
    form.setAttribute("class", "filter");
    form.onsubmit = function (event) {
        event.preventDefault();
    };
    form.appendChild(label);
    form.appendChild(input);
    // return
    return form;
};

/**
 * Filter a table with a given parameter: sets the display css of rows to
 * true or false if it contains the term.
 * @param {String} term The term to filter the table with.
 * @param {Object} table The table to filter.
 */
dwv.html.filterTable = function (term, table) {
    // de-highlight
    dwv.html.dehighlight(table);
    // split search terms
    var terms = term.value.toLowerCase().split(" ");

    // search
    var text = 0;
    var display = 0;
    for (var r = 1; r < table.rows.length; ++r) {
        display = '';
        for (var i = 0; i < terms.length; ++i) {
            text = table.rows[r].innerHTML.replace(/<[^>]+>/g, "").toLowerCase();
            if (text.indexOf(terms[i]) < 0) {
                display = 'none';
            } else {
                if (terms[i].length) {
                    dwv.html.highlight(terms[i], table.rows[r]);
                }
            }
            table.rows[r].style.display = display;
        }
    }
};

/**
 * Transform back each
 * 'preText <span class="highlighted">term</span> postText'
 * into its original 'preText term postText'.
 * @param {Object} container The container to de-highlight.
 */
dwv.html.dehighlight = function (container) {
    for (var i = 0; i < container.childNodes.length; i++) {
        var node = container.childNodes[i];

        if (node.attributes &&
                node.attributes['class'] &&
                node.attributes['class'].value === 'highlighted') {
            node.parentNode.parentNode.replaceChild(
                    document.createTextNode(
                        node.parentNode.innerHTML.replace(/<[^>]+>/g, "")),
                    node.parentNode);
            // Stop here and process next parent
            return;
        } else if (node.nodeType !== 3) {
            // Keep going onto other elements
            dwv.html.dehighlight(node);
        }
    }
};

/**
 * Create a
 * 'preText <span class="highlighted">term</span> postText'
 * around each search term.
 * @param {String} term The term to highlight.
 * @param {Object} container The container where to highlight the term.
 */
dwv.html.highlight = function (term, container) {
    for (var i = 0; i < container.childNodes.length; i++) {
        var node = container.childNodes[i];

        if (node.nodeType === 3) {
            // Text node
            var data = node.data;
            var data_low = data.toLowerCase();
            if (data_low.indexOf(term) >= 0) {
                //term found!
                var new_node = document.createElement('span');
                node.parentNode.replaceChild(new_node, node);

                var result;
                while ((result = data_low.indexOf(term)) !== -1) {
                    // before term
                    new_node.appendChild(document.createTextNode(
                                data.substr(0, result)));
                    // term
                    new_node.appendChild(dwv.html.createHighlightNode(
                                document.createTextNode(data.substr(
                                        result, term.length))));
                    // reduce search string
                    data = data.substr(result + term.length);
                    data_low = data_low.substr(result + term.length);
                }
                new_node.appendChild(document.createTextNode(data));
            }
        } else {
            // Keep going onto other elements
            dwv.html.highlight(term, node);
        }
    }
};

/**
 * Highlight a HTML node.
 * @param {Object} child The child to highlight.
 * @return {Object} The created HTML node.
 */
dwv.html.createHighlightNode = function (child) {
    var node = document.createElement('span');
    node.setAttribute('class', 'highlighted');
    node.attributes['class'].value = 'highlighted';
    node.appendChild(child);
    return node;
};

/**
 * Remove all children of a HTML node.
 * @param {Object} node The node to remove kids.
 */
dwv.html.cleanNode = function (node) {
    // remove its children if node exists
    if ( !node ) {
        return;
    }
    while (node.hasChildNodes()) {
        node.removeChild(node.firstChild);
    }
};

/**
 * Remove a HTML node and all its children.
 * @param {String} nodeId The string id of the node to delete.
 */
dwv.html.removeNode = function (node) {
    // check node
    if ( !node ) {
        return;
    }
    // remove its children
    dwv.html.cleanNode(node);
    // remove it from its parent
    var top = node.parentNode;
    top.removeChild(node);
};

/**
 * Remove a list of HTML nodes and all their children.
 * @param {Array} nodes The list of nodes to delete.
 */
dwv.html.removeNodes = function (nodes) {
    for ( var i = 0; i < nodes.length; ++i ) {
        dwv.html.removeNode(nodes[i]);
    }
};

/**
 * Translate the content of an HTML row.
 * @param {Object} row The HTML row to parse.
 * @param {String} i18nPrefix The i18n prefix to use to find the translation.
 */
dwv.html.translateTableRow = function (row, i18nPrefix) {
    var prefix = (typeof i18nPrefix === "undefined") ? "basics" : i18nPrefix;
    if (prefix.length !== 0) {
        prefix += ".";
    }
    var cells = row.cells;
    for (var c = 0; c < cells.length; ++c) {
        var text = cells[c].firstChild.data;
        cells[c].firstChild.data = dwv.i18n( prefix + text );
    }
};

/**
 * Translate the content of an HTML column.
 * @param {Object} table The HTML table to parse.
 * @param {Number} columnNumber The number of the column to translate.
 * @param {String} i18nPrefix The i18n prefix to use to find the translation.
 * @param {String} i18nSuffix The i18n suffix to use to find the translation.
 */
dwv.html.translateTableColumn = function (table, columnNumber, i18nPrefix, i18nSuffix) {
    var prefix = (typeof i18nPrefix === "undefined") ? "basics" : i18nPrefix;
    if (prefix.length !== 0) {
        prefix += ".";
    }
    var suffix = (typeof i18nSuffix === "undefined") ? "" : i18nSuffix;
    if (suffix.length !== 0) {
        suffix = "." + suffix;
    }
    if (table.rows.length !== 0) {
        for (var r = 1; r < table.rows.length; ++r) {
            var cells = table.rows.item(r).cells;
            if (cells.length >= columnNumber) {
                var text = cells[columnNumber].firstChild.data;
                cells[columnNumber].firstChild.data = dwv.i18n( prefix + text + suffix );
            }
        }
    }
};

/**
 * Make a HTML table cell editable by putting its content inside an input element.
 * @param {Object} cell The cell to make editable.
 * @param {Function} onchange The callback to call when cell's content is changed.
 *    if set to null, the HTML input will be disabled.
 * @param {String} inputType The type of the HTML input, default to 'text'.
 */
dwv.html.makeCellEditable = function (cell, onchange, inputType) {
    // check event
    if (typeof cell === "undefined" ) {
        console.warn("Cannot create input for non existing cell.");
        return;
    }
    // HTML input
    var input = document.createElement("input");
    // handle change
    if (onchange) {
        input.onchange = onchange;
    }
    else {
        input.disabled = true;
    }
    // set input value
    input.value = cell.firstChild.data;
    // input type
    if (typeof inputType === "undefined" ||
        (inputType === "color" && !dwv.browser.hasInputColor() ) ) {
        input.type = "text";
    }
    else {
        input.type = inputType;
    }

    // clean cell
    dwv.html.cleanNode(cell);

    // HTML form
    var form = document.createElement("form");
    form.onsubmit = function (event) {
        event.preventDefault();
    };
    form.appendChild(input);
    // add form to cell
    cell.appendChild(form);
};

/**
 * Set the document cursor to 'pointer'.
 */
dwv.html.setCursorToPointer = function () {
    document.body.style.cursor = 'pointer';
};

/**
 * Set the document cursor to 'default'.
 */
dwv.html.setCursorToDefault = function () {
    document.body.style.cursor = 'default';
};


/**
 * Create a HTML select from an input array of options.
 * The values of the options are the name of the option made lower case.
 * It is left to the user to set the 'onchange' method of the select.
 * @param {String} name The name of the HTML select.
 * @param {Mixed} list The list of options of the HTML select.
 * @param {String} i18nPrefix An optional namespace prefix to find the translation values.
 * @param {Bool} i18nSafe An optional flag to check translation existence.
 * @return {Object} The created HTML select.
 */
dwv.html.createHtmlSelect = function (name, list, i18nPrefix, i18nSafe) {
    // select
    var select = document.createElement("select");
    //select.name = name;
    select.className = name;
    var prefix = (typeof i18nPrefix === "undefined") ? "" : i18nPrefix + ".";
    var safe = (typeof i18nSafe === "undefined") ? false : true;
    var getText = function(value) {
        var key = prefix + value + ".name";
        var text = "";
        if (safe) {
            if (dwv.i18nExists(key)) {
                text = dwv.i18n(key);
            }
            else {
                text = value;
            }
        }
        else {
            text = dwv.i18n(key);
        }
        return text;
    };
    // options
    var option;
    if ( list instanceof Array )
    {
        for ( var i in list )
        {
            option = document.createElement("option");
            option.value = list[i];
            option.appendChild(document.createTextNode(getText(list[i])));
            select.appendChild(option);
        }
    }
    else if ( typeof list === 'object')
    {
        for ( var item in list )
        {
            option = document.createElement("option");
            option.value = item;
            option.appendChild(document.createTextNode(getText(item)));
            select.appendChild(option);
        }
    }
    else
    {
        throw new Error("Unsupported input list type.");
    }
    return select;
};

/**
 * Display or not an element.
 * @param {Object} element The HTML element to display.
 * @param {Boolean} flag True to display the element.
 */
dwv.html.displayElement = function (element, flag)
{
    element.style.display = flag ? "" : "none";
};

/**
 * Toggle the display of an element.
 * @param {Object} element The HTML element to display.
 */
dwv.html.toggleDisplay = function (element)
{
    if ( element.style.display === "none" ) {
        element.style.display = '';
    }
    else {
        element.style.display = "none";
    }
};

/**
 * Append an element.
 * @param {Object} parent The HTML element to append to.
 * @param {Object} element The HTML element to append.
 */
dwv.html.appendElement = function (parent, element)
{
    // append
    parent.appendChild(element);
    // refresh
    dwv.gui.refreshElement(parent);
};

/**
 * Create an element.
 * @param {String} type The type of the elemnt.
 * @param {String} className The className of the element.
 */
dwv.html.createHiddenElement = function (type, className)
{
    var element = document.createElement(type);
    element.className = className;
    // hide by default
    element.style.display = "none";
    // return
    return element;
};

// namespaces
var dwv = dwv || {};
dwv.gui = dwv.gui || {};
dwv.gui.base = dwv.gui.base || {};
dwv.gui.info = dwv.gui.info || {};

/**
 * Plot some data in a given div.
 * @param {Object} div The HTML element to add WindowLevel info to.
 * @param {Array} data The data array to plot.
 * @param {Object} options Plot options.
 */
dwv.gui.base.plot = function (/*div, data, options*/)
{
    // default does nothing...
};

/**
 * MiniColourMap info layer.
 * @constructor
 * @param {Object} div The HTML element to add colourMap info to.
 * @param {Object} app The associated application.
 */
dwv.gui.info.MiniColourMap = function ( div, app )
{
    /**
     * Create the mini colour map info div.
     */
    this.create = function ()
    {
        // clean div
        var elems = div.getElementsByClassName("colour-map-info");
        if ( elems.length !== 0 ) {
            dwv.html.removeNodes(elems);
        }
        // colour map
        var canvas = document.createElement("canvas");
        canvas.className = "colour-map-info";
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
    this.update = function (event)
    {
        var windowCenter = event.wc;
        var windowWidth = event.ww;
        // retrieve canvas and context
        var canvas = div.getElementsByClassName("colour-map-info")[0];
        var context = canvas.getContext('2d');
        // fill in the image data
        var colourMap = app.getViewController().getColourMap();
        var imageData = context.getImageData(0,0,canvas.width, canvas.height);
        // histogram sampling
        var c = 0;
        var minInt = app.getImage().getRescaledDataRange().min;
        var range = app.getImage().getRescaledDataRange().max - minInt;
        var incrC = range / canvas.width;
        // Y scale
        var y = 0;
        var yMax = 255;
        var yMin = 0;
        // X scale
        var xMin = windowCenter - 0.5 - (windowWidth-1) / 2;
        var xMax = windowCenter - 0.5 + (windowWidth-1) / 2;
        // loop through values
        var index;
        for ( var j = 0; j < canvas.height; ++j ) {
            c = minInt;
            for ( var i = 0; i < canvas.width; ++i ) {
                if ( c <= xMin ) {
                    y = yMin;
                }
                else if ( c > xMax ) {
                    y = yMax;
                }
                else {
                    y = ( (c - (windowCenter-0.5) ) / (windowWidth-1) + 0.5 ) *
                        (yMax-yMin) + yMin;
                    y = parseInt(y,10);
                }
                index = (i + j * canvas.width) * 4;
                imageData.data[index] = colourMap.red[y];
                imageData.data[index+1] = colourMap.green[y];
                imageData.data[index+2] = colourMap.blue[y];
                imageData.data[index+3] = 0xff;
                c += incrC;
            }
        }
        // put the image data in the context
        context.putImageData(imageData, 0, 0);
    };
}; // class dwv.gui.info.MiniColourMap


/**
 * Plot info layer.
 * @constructor
 * @param {Object} div The HTML element to add colourMap info to.
 * @param {Object} app The associated application.
 */
dwv.gui.info.Plot = function (div, app)
{
    /**
     * Create the plot info.
     */
    this.create = function()
    {
        // clean div
        if ( div ) {
            dwv.html.cleanNode(div);
        }
        // plot
        dwv.gui.plot(div, app.getImage().getHistogram());
    };

    /**
     * Update plot.
     * @param {Object} event The windowing change event containing the new values.
     * Warning: expects the plot to exist (use after createPlot).
     */
    this.update = function (event)
    {
        var wc = event.wc;
        var ww = event.ww;

        var half = parseInt( (ww-1) / 2, 10 );
        var center = parseInt( (wc-0.5), 10 );
        var min = center - half;
        var max = center + half;

        var markings = [
            { "color": "#faa", "lineWidth": 1, "xaxis": { "from": min, "to": min } },
            { "color": "#aaf", "lineWidth": 1, "xaxis": { "from": max, "to": max } }
        ];

        // plot
        dwv.gui.plot(div, app.getImage().getHistogram(), {markings: markings});
    };

}; // class dwv.gui.info.Plot

/**
 * DICOM Header overlay info layer.
 * @constructor
 * @param {Object} div The HTML element to add Header overlay info to.
 * @param {String} pos The string to specify the corner position. (tl,tc,tr,cl,cr,bl,bc,br)
 */
dwv.gui.info.Overlay = function ( div, pos, app )
{
    // closure to self
    var self = this;

    /**
     * Get the overlay array of the current position.
     * @return {Array} The overlay information.
     */
    this.getOverlays = function ()
    {
        var image = app.getImage();
        if (!image) {
            return;
        }
        var allOverlays = image.getOverlays();
        if (!allOverlays) {
            return;
        }
        var position = app.getViewController().getCurrentPosition();
        var sliceOverlays = allOverlays[position.k];
        if (!sliceOverlays) {
            return;
        }
        return sliceOverlays[pos];
    };

    /**
     * Create the overlay info div.
     */
    this.create = function ()
    {
        // remove all <ul> elements from div
        dwv.html.cleanNode(div);

        // get overlay string array of the current position
        var overlays = self.getOverlays();
        if (!overlays) {
            return;
        }

        if (pos === "bc" || pos === "tc" ||
            pos === "cr" || pos === "cl") {
            div.textContent = overlays[0].value;
        } else {
            // create <ul> element
            var ul = document.createElement("ul");

            for (var n=0; overlays[n]; n++){
                var li;
                if (overlays[n].value === "window-center") {
                    li = document.createElement("li");
                    li.className = "info-" + pos + "-window-center";
                    ul.appendChild(li);
                } else if (overlays[n].value === "window-width") {
                    li = document.createElement("li");
                    li.className = "info-" + pos + "-window-width";
                    ul.appendChild(li);
                } else if (overlays[n].value === "zoom") {
                    li = document.createElement("li");
                    li.className = "info-" + pos + "-zoom";
                    ul.appendChild(li);
                } else if (overlays[n].value === "offset") {
                    li = document.createElement("li");
                    li.className = "info-" + pos + "-offset";
                    ul.appendChild(li);
                } else if (overlays[n].value === "value") {
                    li = document.createElement("li");
                    li.className = "info-" + pos + "-value";
                    ul.appendChild(li);
                } else if (overlays[n].value === "position") {
                    li = document.createElement("li");
                    li.className = "info-" + pos + "-position";
                    ul.appendChild(li);
                } else if (overlays[n].value === "frame") {
                    li = document.createElement("li");
                    li.className = "info-" + pos + "-frame";
                    ul.appendChild(li);
                } else {
                    li = document.createElement("li");
                    li.className = "info-" + pos + "-" + n;
                    li.appendChild( document.createTextNode( overlays[n].value ) );
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
        // get overlay string array of the current position
        var overlays = self.getOverlays();
        if (!overlays) {
            return;
        }

        if (pos === "bc" || pos === "tc" ||
            pos === "cr" || pos === "cl") {
            div.textContent = overlays[0].value;
        } else {
            var li;
            var n;

            for (n=0; overlays[n]; n++) {
                if (overlays[n].value === "window-center") {
                    if (event.type === "wl-center-change") {
                        li = div.getElementsByClassName("info-" + pos + "-window-center")[0];
                        dwv.html.cleanNode(li);
                        var wcStr = dwv.utils.replaceFlags2( overlays[n].format, [Math.round(event.wc)] );
                        if (li) {
                            li.appendChild( document.createTextNode(wcStr) );
                        }
                    }
                } else if (overlays[n].value === "window-width") {
                    if (event.type === "wl-width-change") {
                        li = div.getElementsByClassName("info-" + pos + "-window-width")[0];
                        dwv.html.cleanNode(li);
                        var wwStr = dwv.utils.replaceFlags2( overlays[n].format, [Math.round(event.ww)] );
                        if (li) {
                            li.appendChild( document.createTextNode(wwStr) );
                        }
                    }
                } else if (overlays[n].value === "zoom") {
                    if (event.type === "zoom-change") {
                        li = div.getElementsByClassName("info-" + pos + "-zoom")[0];
                        dwv.html.cleanNode(li);
                        var zoom = Number(event.scale).toPrecision(3);
                        var zoomStr = dwv.utils.replaceFlags2( overlays[n].format, [zoom] );
                        if (li) {
                            li.appendChild( document.createTextNode( zoomStr ) );
                        }
                    }
                } else if (overlays[n].value === "offset") {
                    if (event.type === "zoom-change") {
                        li = div.getElementsByClassName("info-" + pos + "-offset")[0];
                        dwv.html.cleanNode(li);
                        var offset = [ Number(event.cx).toPrecision(3),
                            Number(event.cy).toPrecision(3)];
                        var offStr = dwv.utils.replaceFlags2( overlays[n].format, offset );
                        if (li) {
                            li.appendChild( document.createTextNode( offStr ) );
                        }
                    }
                } else if (overlays[n].value === "value") {
                    if (event.type === "position-change") {
                        li = div.getElementsByClassName("info-" + pos + "-value")[0];
                        dwv.html.cleanNode(li);
                        var valueStr = dwv.utils.replaceFlags2( overlays[n].format, [event.value] );
                        if (li) {
                            li.appendChild( document.createTextNode( valueStr ) );
                        }
                    }
                } else if (overlays[n].value === "position") {
                    if (event.type === "position-change") {
                        li = div.getElementsByClassName("info-" + pos + "-position")[0];
                        dwv.html.cleanNode(li);
                        var posStr = dwv.utils.replaceFlags2( overlays[n].format, [event.i, event.j, event.k] );
                        if (li) {
                            li.appendChild( document.createTextNode( posStr ) );
                        }
                    }
                } else if (overlays[n].value === "frame") {
                    if (event.type === "frame-change") {
                        li = div.getElementsByClassName("info-" + pos + "-frame")[0];
                        dwv.html.cleanNode(li);
                        var frameStr = dwv.utils.replaceFlags2( overlays[n].format, [event.frame] );
                        if (li) {
                            li.appendChild( document.createTextNode( frameStr ) );
                        }
                    }
                } else {
                    if (event.type === "position-change") {
                        li = div.getElementsByClassName("info-" + pos + "-" + n)[0];
                        dwv.html.cleanNode(li);
                        if (li) {
                            li.appendChild( document.createTextNode( overlays[n].value ) );
                        }
                    }
                }
            }
        }
    };
}; // class dwv.gui.info.Overlay

/**
 * Create overlay string array of the image in each corner
 * @param {Object} dicomElements DICOM elements of the image
 * @return {Array} Array of string to be shown in each corner
 */
dwv.gui.info.createOverlays = function (dicomElements)
{
    var overlays = {};
    var modality = dicomElements.getFromKey("x00080060");
    if (!modality){
        return overlays;
    }

    var omaps = dwv.gui.info.overlayMaps;
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
dwv.gui.info.createOverlaysForDom = function (info)
{
    var overlays = {};
    var omaps = dwv.gui.info.overlayMaps;
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

// namespaces
var dwv = dwv || {};
dwv.html = dwv.html || {};

/**
 * Window layer.
 * @constructor
 * @param {String} name The name of the layer.
 */
dwv.html.Layer = function(canvas)
{
    /**
     * A cache of the initial canvas.
     * @private
     * @type Object
     */
    var cacheCanvas = null;
    /**
     * The associated CanvasRenderingContext2D.
     * @private
     * @type Object
     */
    var context = null;

    /**
     * Get the layer canvas.
     * @return {Object} The layer canvas.
     */
    this.getCanvas = function() { return canvas; };
    /**
     * Get the layer context.
     * @return {Object} The layer context.
     */
    this.getContext = function() { return context; };
    /**
     * Get the layer offset on page.
     * @return {Number} The layer offset on page.
     */
    this.getOffset = function() { return canvas.offset(); };

    /**
     * The image data array.
     * @private
     * @type Array
     */
    var imageData = null;

    /**
     * The layer origin.
     * @private
     * @type {Object}
     */
    var origin = {'x': 0, 'y': 0};
    /**
     * Get the layer origin.
     * @return {Object} The layer origin as {'x','y'}.
     */
    this.getOrigin = function () {
        return origin;
    };
    /**
     * The layer zoom.
     * @private
     * @type {Object}
     */
    var zoom = {'x': 1, 'y': 1};
    /**
     * Get the layer zoom.
     * @return {Object} The layer zoom as {'x','y'}.
     */
    this.getZoom = function () {
        return zoom;
    };

    /**
     * The layer translation.
     * @private
     * @type {Object}
     */
    var trans = {'x': 0, 'y': 0};
    /**
     * Get the layer translation.
     * @return {Object} The layer translation as {'x','y'}.
     */
    this.getTrans = function () {
        return trans;
    };

    /**
     * Set the canvas width.
     * @param {Number} width The new width.
     */
    this.setWidth = function ( width ) {
        canvas.width = width;
    };
    /**
     * Set the canvas height.
     * @param {Number} height The new height.
     */
    this.setHeight = function ( height ) {
        canvas.height = height;
    };

    /**
     * Set the layer zoom.
     * @param {Number} newZoomX The zoom in the X direction.
     * @param {Number} newZoomY The zoom in the Y direction.
     * @param {Number} centerX The zoom center in the X direction.
     * @param {Number} centerY The zoom center in the Y direction.
     */
    this.zoom = function(newZoomX,newZoomY,centerX,centerY)
    {
        // The zoom is the ratio between the differences from the center
        // to the origins:
        // centerX - originX = ( centerX - originX0 ) * zoomX
        // (center in ~world coordinate system)
        //originX = (centerX / zoomX) + originX - (centerX / newZoomX);
        //originY = (centerY / zoomY) + originY - (centerY / newZoomY);

        // center in image coordinate system
        origin.x = centerX - (centerX - origin.x) * (newZoomX / zoom.x);
        origin.y = centerY - (centerY - origin.y) * (newZoomY / zoom.y);

        // save zoom
        zoom.x = newZoomX;
        zoom.y = newZoomY;
    };

    /**
     * Set the layer translation.
     * Translation is according to the last one.
     * @param {Number} tx The translation in the X direction.
     * @param {Number} ty The translation in the Y direction.
     */
    this.translate = function(tx,ty)
    {
        trans.x = tx;
        trans.y = ty;
    };

    /**
     * Set the image data array.
     * @param {Array} data The data array.
     */
    this.setImageData = function(data)
    {
        imageData = data;
        // update the cached canvas
        cacheCanvas.getContext("2d").putImageData(imageData, 0, 0);
    };

    /**
     * Reset the layout.
     */
    this.resetLayout = function(izoom)
    {
        origin.x = 0;
        origin.y = 0;
        zoom.x = izoom;
        zoom.y = izoom;
        trans.x = 0;
        trans.y = 0;
    };

    /**
     * Transform a display position to an index.
     */
    this.displayToIndex = function ( point2D ) {
        return {'x': ( (point2D.x - origin.x) / zoom.x ) - trans.x,
            'y': ( (point2D.y - origin.y) / zoom.y ) - trans.y};
    };

    /**
     * Draw the content (imageData) of the layer.
     * The imageData variable needs to be set
     */
    this.draw = function ()
    {
        // clear the context: reset the transform first
        // store the current transformation matrix
        context.save();
        // use the identity matrix while clearing the canvas
        context.setTransform( 1, 0, 0, 1, 0, 0 );
        context.clearRect( 0, 0, canvas.width, canvas.height );
        // restore the transform
        context.restore();

        // draw the cached canvas on the context
        // transform takes as input a, b, c, d, e, f to create
        // the transform matrix (column-major order):
        // [ a c e ]
        // [ b d f ]
        // [ 0 0 1 ]
        context.setTransform( zoom.x, 0, 0, zoom.y,
            origin.x + (trans.x * zoom.x),
            origin.y + (trans.y * zoom.y) );
        context.drawImage( cacheCanvas, 0, 0 );
    };

    /**
     * Initialise the layer: set the canvas and context
     * @input {Number} inputWidth The width of the canvas.
     * @input {Number} inputHeight The height of the canvas.
     */
    this.initialise = function(inputWidth, inputHeight)
    {
        // find the canvas element
        //canvas = document.getElementById(name);
        //if (!canvas)
        //{
        //    alert("Error: cannot find the canvas element for '" + name + "'.");
        //    return;
        //}
        // check that the getContext method exists
        if (!canvas.getContext)
        {
            alert("Error: no canvas.getContext method.");
            return;
        }
        // get the 2D context
        context = canvas.getContext('2d');
        if (!context)
        {
            alert("Error: failed to get the 2D context.");
            return;
        }
        // canvas sizes
        canvas.width = inputWidth;
        canvas.height = inputHeight;
        // original empty image data array
        context.clearRect (0, 0, canvas.width, canvas.height);
        imageData = context.getImageData(0, 0, canvas.width, canvas.height);
        // cached canvas
        cacheCanvas = document.createElement("canvas");
        cacheCanvas.width = inputWidth;
        cacheCanvas.height = inputHeight;
    };

    /**
     * Fill the full context with the current style.
     */
    this.fillContext = function()
    {
        context.fillRect( 0, 0, canvas.width, canvas.height );
    };

    /**
     * Clear the context and reset the image data.
     */
    this.clear = function()
    {
        context.clearRect(0, 0, canvas.width, canvas.height);
        imageData = context.getImageData(0, 0, canvas.width, canvas.height);
        this.resetLayout();
    };

    /**
     * Merge two layers.
     * @input {Layer} layerToMerge The layer to merge. It will also be emptied.
     */
    this.merge = function(layerToMerge)
    {
        // basic resampling of the merge data to put it at zoom 1:1
        var mergeImageData = layerToMerge.getContext().getImageData(
            0, 0, canvas.width, canvas.height);
        var offMerge = 0;
        var offMergeJ = 0;
        var offThis = 0;
        var offThisJ = 0;
        var alpha = 0;
        for( var j=0; j < canvas.height; ++j ) {
            offMergeJ = parseInt( (origin.y + j * zoom.y), 10 ) * canvas.width;
            offThisJ = j * canvas.width;
            for( var i=0; i < canvas.width; ++i ) {
                // 4 component data: RGB + alpha
                offMerge = 4 * ( parseInt( (origin.x + i * zoom.x), 10 ) + offMergeJ );
                offThis = 4 * ( i + offThisJ );
                // merge non transparent
                alpha = mergeImageData.data[offMerge+3];
                if( alpha !== 0 ) {
                    imageData.data[offThis] = mergeImageData.data[offMerge];
                    imageData.data[offThis+1] = mergeImageData.data[offMerge+1];
                    imageData.data[offThis+2] = mergeImageData.data[offMerge+2];
                    imageData.data[offThis+3] = alpha;
                }
            }
        }
        // empty and reset merged layer
        layerToMerge.clear();
        // draw the layer
        this.draw();
    };

    /**
     * Set the line colour for the layer.
     * @input {String} colour The line colour.
     */
    this.setLineColour = function(colour)
    {
        context.fillStyle = colour;
        context.strokeStyle = colour;
    };

    /**
     * Display the layer.
     * @input {Boolean} val Whether to display the layer or not.
     */
    this.setStyleDisplay = function(val)
    {
        if( val === true )
        {
            canvas.style.display = '';
        }
        else
        {
            canvas.style.display = "none";
        }
    };

    /**
     * Check if the layer is visible.
     * @return {Boolean} True if the layer is visible.
     */
    this.isVisible = function()
    {
        if( canvas.style.display === "none" ) {
            return false;
        }
        else {
            return true;
        }
    };

    /**
     * Align on another layer.
     * @param {Layer} rhs The layer to align on.
     */
    this.align = function(rhs)
    {
        canvas.style.top = rhs.getCanvas().offsetTop;
        canvas.style.left = rhs.getCanvas().offsetLeft;
    };
}; // Layer class

/**
 * Get the offset of an input event.
 * @param {Object} event The event to get the offset from.
 * @return {Array} The array of offsets.
 */
dwv.html.getEventOffset = function (event) {
    var positions = [];
    var ex = 0;
    var ey = 0;
    if ( event.targetTouches ) {
        // get the touch offset from all its parents
        var offsetLeft = 0;
        var offsetTop = 0;
        var offsetParent = event.targetTouches[0].target.offsetParent;
        while ( offsetParent ) {
            if (!isNaN(offsetParent.offsetLeft)) {
                offsetLeft += offsetParent.offsetLeft;
            }
            if (!isNaN(offsetParent.offsetTop)) {
                offsetTop += offsetParent.offsetTop;
            }
            offsetParent = offsetParent.offsetParent;
        }
        // set its position
        var touch = null;
        for ( var i = 0 ; i < event.targetTouches.length; ++i ) {
            touch = event.targetTouches[i];
            ex = touch.pageX - offsetLeft;
            ey = touch.pageY - offsetTop;
            positions.push({'x': ex, 'y': ey});
        }
    }
    else {
        // layerX is used by Firefox
        ex = event.offsetX === undefined ? event.layerX : event.offsetX;
        ey = event.offsetY === undefined ? event.layerY : event.offsetY;
        positions.push({'x': ex, 'y': ey});
    }
    return positions;
};

// namespaces
var dwv = dwv || {};
dwv.gui = dwv.gui || {};
dwv.gui.base = dwv.gui.base || {};

/**
 * Loadbox base gui.
 * @constructor
 */
dwv.gui.base.Loadbox = function (app, loaders)
{
    /**
     * Loader HTML select.
     * @private
     */
    var loaderSelector = null;

    /**
     * Setup the loadbox HTML.
     */
    this.setup = function ()
    {
        // loader select
        loaderSelector = dwv.html.createHtmlSelect("loaderSelect", loaders, "io");
        loaderSelector.onchange = app.onChangeLoader;

        // node
        var node = app.getElement("loaderlist");
        // clear it
        while(node.hasChildNodes()) {
            node.removeChild(node.firstChild);
        }
        // append
        node.appendChild(loaderSelector);
        // refresh
        dwv.gui.refreshElement(node);
    };

    /**
     * Display a loader.
     * @param {String} name The name of the loader to show.
     */
    this.displayLoader = function (name)
    {
        var keys = Object.keys(loaders);
        for ( var i = 0; i < keys.length; ++i ) {
            if ( keys[i] === name ) {
                loaders[keys[i]].display(true);
            }
            else {
                loaders[keys[i]].display(false);
            }
        }
    };

    /**
     * Reset to its original state.
     */
    this.reset = function ()
    {
        // display first loader
        var keys = Object.keys(loaders);
        this.displayLoader(keys[0]);
        // reset HTML select
        if (loaderSelector) {
            loaderSelector.selectedIndex = 0;
        }
    };

}; // class dwv.gui.base.Loadbox

/**
 * FileLoad base gui.
 * @constructor
 */
dwv.gui.base.FileLoad = function (app)
{
    // closure to self
    var self = this;

    /**
     * Internal file input change handler.
     * @param {Object} event The change event.
     */
    function onchangeinternal(event) {
        if (typeof self.onchange === "function") {
            self.onchange(event);
        }
        app.onChangeFiles(event);
    }

    /**
     * Setup the file load HTML to the page.
     */
    this.setup = function()
    {
        // input
        var fileLoadInput = document.createElement("input");
        fileLoadInput.onchange = onchangeinternal;
        fileLoadInput.type = "file";
        fileLoadInput.multiple = true;
        fileLoadInput.className = "imagefiles";
        fileLoadInput.setAttribute("data-clear-btn","true");
        fileLoadInput.setAttribute("data-mini","true");

        // associated div
        var fileLoadDiv = document.createElement("div");
        fileLoadDiv.className = "imagefilesdiv";
        fileLoadDiv.style.display = "none";
        fileLoadDiv.appendChild(fileLoadInput);

        // node
        var node = app.getElement("loaderlist");
        // append
        node.appendChild(fileLoadDiv);
        // refresh
        dwv.gui.refreshElement(node);
    };

    /**
     * Display the file load HTML.
     * @param {Boolean} bool True to display, false to hide.
     */
    this.display = function (bool)
    {
        // file div element
        var node = app.getElement("loaderlist");
        var filediv = node.getElementsByClassName("imagefilesdiv")[0];
        filediv.style.display = bool ? "" : "none";
    };

}; // class dwv.gui.base.FileLoad

/**
 * UrlLoad base gui.
 * @constructor
 */
dwv.gui.base.UrlLoad = function (app)
{
    // closure to self
    var self = this;

    /**
     * Internal url input change handler.
     * @param {Object} event The change event.
     */
    function onchangeinternal(event) {
        if (typeof self.onchange === "function") {
            self.onchange(event);
        }
        app.onChangeURL(event);
    }

    /**
     * Setup the url load HTML to the page.
     */
    this.setup = function ()
    {
        // input
        var urlLoadInput = document.createElement("input");
        urlLoadInput.onchange = onchangeinternal;
        urlLoadInput.type = "url";
        urlLoadInput.className = "imageurl";
        urlLoadInput.setAttribute("data-clear-btn","true");
        urlLoadInput.setAttribute("data-mini","true");

        // associated div
        var urlLoadDiv = document.createElement("div");
        urlLoadDiv.className = "imageurldiv";
        urlLoadDiv.style.display = "none";
        urlLoadDiv.appendChild(urlLoadInput);

        // node
        var node = app.getElement("loaderlist");
        // append
        node.appendChild(urlLoadDiv);
        // refresh
        dwv.gui.refreshElement(node);
    };

    /**
     * Display the url load HTML.
     * @param {Boolean} bool True to display, false to hide.
     */
    this.display = function (bool)
    {
        // url div element
        var node = app.getElement("loaderlist");
        var urldiv = node.getElementsByClassName("imageurldiv")[0];
        urldiv.style.display = bool ? "" : "none";
    };

}; // class dwv.gui.base.UrlLoad

// namespaces
var dwv = dwv || {};
dwv.html = dwv.html || {};

/**
 * Style class.
 * @constructor
 */
dwv.html.Style = function ()
{
    /**
     * Font size.
     * @private
     * @type Number
     */
    var fontSize = 12;
    /**
     * Font family.
     * @private
     * @type String
     */
    var fontFamily = "Verdana";
    /**
     * Text colour.
     * @private
     * @type String
     */
    var textColour = "#fff";
    /**
     * Line colour.
     * @private
     * @type String
     */
    var lineColour = "#ffff80";
    /**
     * Display scale.
     * @private
     * @type Number
     */
    var displayScale = 1;
    /**
     * Stroke width.
     * @private
     * @type Number
     */
    var strokeWidth = 2;

    /**
     * Get the font family.
     * @return {String} The font family.
     */
    this.getFontFamily = function () { return fontFamily; };

    /**
     * Get the font size.
     * @return {Number} The font size.
     */
    this.getFontSize = function () { return fontSize; };

    /**
     * Get the stroke width.
     * @return {Number} The stroke width.
     */
    this.getStrokeWidth = function () { return strokeWidth; };

    /**
     * Get the text colour.
     * @return {String} The text colour.
     */
    this.getTextColour = function () { return textColour; };

    /**
     * Get the line colour.
     * @return {String} The line colour.
     */
    this.getLineColour = function () { return lineColour; };

    /**
     * Set the line colour.
     * @param {String} colour The line colour.
     */
    this.setLineColour = function (colour) { lineColour = colour; };

    /**
     * Set the display scale.
     * @param {String} scale The display scale.
     */
    this.setScale = function (scale) { displayScale = scale; };

    /**
     * Scale an input value.
     * @param {Number} value The value to scale.
     */
    this.scale = function (value) { return value / displayScale; };
};

/**
 * Get the font definition string.
 * @return {String} The font definition string.
 */
dwv.html.Style.prototype.getFontStr = function ()
{
    return ("normal " + this.getFontSize() + "px sans-serif");
};

/**
 * Get the line height.
 * @return {Number} The line height.
 */
dwv.html.Style.prototype.getLineHeight = function ()
{
    return ( this.getFontSize() + this.getFontSize() / 5 );
};

/**
 * Get the font size scaled to the display.
 * @return {Number} The scaled font size.
 */
dwv.html.Style.prototype.getScaledFontSize = function ()
{
    return this.scale( this.getFontSize() );
};

/**
 * Get the stroke width scaled to the display.
 * @return {Number} The scaled stroke width.
 */
dwv.html.Style.prototype.getScaledStrokeWidth = function ()
{
    return this.scale( this.getStrokeWidth() );
};

// namespaces
var dwv = dwv || {};
dwv.gui = dwv.gui || {};
dwv.gui.base = dwv.gui.base || {};

/**
 * Toolbox base gui.
 * @constructor
 */
dwv.gui.base.Toolbox = function (app)
{
    /**
     * Setup the toolbox HTML.
     */
    this.setup = function (list)
    {
        // tool select
        var toolSelector = dwv.html.createHtmlSelect("toolSelect", list, "tool");
        toolSelector.onchange = app.onChangeTool;

        // tool list element
        var toolLi = document.createElement("li");
        toolLi.className = "toolLi ui-block-a";
        toolLi.style.display = "none";
        toolLi.appendChild(toolSelector);

        // tool ul
        var toolUl = document.createElement("ul");
        toolUl.appendChild(toolLi);
        toolUl.className = "ui-grid-b";

        // node
        var node = app.getElement("toolList");
        // append
        node.appendChild(toolUl);
        // refresh
        dwv.gui.refreshElement(node);
    };

    /**
     * Display the toolbox HTML.
     * @param {Boolean} bool True to display, false to hide.
     */
    this.display = function (bool)
    {
        // tool list element
        var node = app.getElement("toolLi");
        dwv.html.displayElement(node, bool);
    };

    /**
     * Initialise the toolbox HTML.
     */
    this.initialise = function (displays)
    {
        // tool select: reset selected option
        var toolSelector = app.getElement("toolSelect");

        // update list
        var options = toolSelector.options;
        var selectedIndex = -1;
        for ( var i = 0; i < options.length; ++i ) {
            if ( !displays[i] ) {
                options[i].style.display = "none";
            }
            else {
                if ( selectedIndex === -1 ) {
                    selectedIndex = i;
                }
                options[i].style.display = "";
            }
        }
        toolSelector.selectedIndex = selectedIndex;

        // refresh
        dwv.gui.refreshElement(toolSelector);
    };

}; // dwv.gui.base.Toolbox

/**
 * WindowLevel tool base gui.
 * @constructor
 */
dwv.gui.base.WindowLevel = function (app)
{
    /**
     * Setup the tool HTML.
     */
    this.setup = function ()
    {
        // preset select
        var wlSelector = dwv.html.createHtmlSelect("presetSelect", []);
        wlSelector.onchange = app.onChangeWindowLevelPreset;
        // colour map select
        var cmSelector = dwv.html.createHtmlSelect("colourMapSelect", dwv.tool.colourMaps, "colourmap");
        cmSelector.onchange = app.onChangeColourMap;

        // preset list element
        var wlLi = document.createElement("li");
        wlLi.className = "wlLi ui-block-b";
        //wlLi.className = "wlLi";
        wlLi.style.display = "none";
        wlLi.appendChild(wlSelector);
        // colour map list element
        var cmLi = document.createElement("li");
        cmLi.className = "cmLi ui-block-c";
        //cmLi.className = "cmLi";
        cmLi.style.display = "none";
        cmLi.appendChild(cmSelector);

        // node
        var node = app.getElement("toolList").getElementsByTagName("ul")[0];
        // append preset
        node.appendChild(wlLi);
        // append colour map
        node.appendChild(cmLi);
        // refresh
        dwv.gui.refreshElement(node);
    };

    /**
     * Display the tool HTML.
     * @param {Boolean} bool True to display, false to hide.
     */
    this.display = function (bool)
    {
        // presets list element
        var node = app.getElement("wlLi");
        dwv.html.displayElement(node, bool);
        // colour map list element
        node = app.getElement("cmLi");
        dwv.html.displayElement(node, bool);
    };

    /**
     * Initialise the tool HTML.
     */
    this.initialise = function ()
    {
        // create new preset select
        var wlSelector = dwv.html.createHtmlSelect("presetSelect",
            app.getViewController().getWindowLevelPresetsNames(), "wl.presets", true);
        wlSelector.onchange = app.onChangeWindowLevelPreset;
        wlSelector.title = "Select w/l preset.";

        // copy html list
        var wlLi = app.getElement("wlLi");
        // clear node
        dwv.html.cleanNode(wlLi);
        // add children
        wlLi.appendChild(wlSelector);
        // refresh
        dwv.gui.refreshElement(wlLi);

        // colour map select
        var cmSelector = app.getElement("colourMapSelect");
        cmSelector.selectedIndex = 0;
        // special monochrome1 case
        if( app.getImage().getPhotometricInterpretation() === "MONOCHROME1" )
        {
            cmSelector.selectedIndex = 1;
        }
        // refresh
        dwv.gui.refreshElement(cmSelector);
    };

}; // class dwv.gui.base.WindowLevel

/**
 * Draw tool base gui.
 * @constructor
 */
dwv.gui.base.Draw = function (app)
{
    // default colours
    var colours = [
       "Yellow", "Red", "White", "Green", "Blue", "Lime", "Fuchsia", "Black"
    ];
    /**
     * Get the default colour.
     */
    this.getDefaultColour = function () {
        if ( dwv.browser.hasInputColor() ) {
            return "#FFFF80";
        }
        else {
            return colours[0];
        }
    };

    /**
     * Setup the tool HTML.
     */
    this.setup = function (shapeList)
    {
        // shape select
        var shapeSelector = dwv.html.createHtmlSelect("shapeSelect", shapeList, "shape");
        shapeSelector.onchange = app.onChangeShape;
        // colour select
        var colourSelector = null;
        if ( dwv.browser.hasInputColor() ) {
            colourSelector = document.createElement("input");
            colourSelector.className = "colourSelect";
            colourSelector.type = "color";
            colourSelector.value = "#FFFF80";
        }
        else {
            colourSelector = dwv.html.createHtmlSelect("colourSelect", colours, "colour");
        }
        colourSelector.onchange = app.onChangeLineColour;

        // shape list element
        var shapeLi = document.createElement("li");
        shapeLi.className = "shapeLi ui-block-c";
        shapeLi.style.display = "none";
        shapeLi.appendChild(shapeSelector);
        //shapeLi.setAttribute("class","ui-block-c");
        // colour list element
        var colourLi = document.createElement("li");
        colourLi.className = "colourLi ui-block-b";
        colourLi.style.display = "none";
        colourLi.appendChild(colourSelector);
        //colourLi.setAttribute("class","ui-block-b");

        // node
        var node = app.getElement("toolList").getElementsByTagName("ul")[0];
        // apend shape
        node.appendChild(shapeLi);
        // append colour
        node.appendChild(colourLi);
        // refresh
        dwv.gui.refreshElement(node);
    };

    /**
     * Display the tool HTML.
     * @param {Boolean} bool True to display, false to hide.
     */
    this.display = function (bool)
    {
        // colour list element
        var node = app.getElement("colourLi");
        dwv.html.displayElement(node, bool);
        // shape list element
        node = app.getElement("shapeLi");
        dwv.html.displayElement(node, bool);
    };

    /**
     * Initialise the tool HTML.
     */
    this.initialise = function ()
    {
        // shape select: reset selected option
        var shapeSelector = app.getElement("shapeSelect");
        shapeSelector.selectedIndex = 0;
        // refresh
        dwv.gui.refreshElement(shapeSelector);

        // colour select: reset selected option
        var colourSelector = app.getElement("colourSelect");
        if ( !dwv.browser.hasInputColor() ) {
            colourSelector.selectedIndex = 0;
        }
        // refresh
        dwv.gui.refreshElement(colourSelector);
    };

}; // class dwv.gui.base.Draw

/**
 * Base gui for a tool with a colour setting.
 * @constructor
 */
dwv.gui.base.ColourTool = function (app, prefix)
{
    // default colours
    var colours = [
       "Yellow", "Red", "White", "Green", "Blue", "Lime", "Fuchsia", "Black"
    ];
    // colour selector class
    var colourSelectClassName = prefix + "ColourSelect";
    // colour selector class
    var colourLiClassName = prefix + "ColourLi";

    /**
     * Get the default colour.
     */
    this.getDefaultColour = function () {
        if ( dwv.browser.hasInputColor() ) {
            return "#FFFF80";
        }
        else {
            return colours[0];
        }
    };

    /**
     * Setup the tool HTML.
     */
    this.setup = function ()
    {
        // colour select
        var colourSelector = null;
        if ( dwv.browser.hasInputColor() ) {
            colourSelector = document.createElement("input");
            colourSelector.className = colourSelectClassName;
            colourSelector.type = "color";
            colourSelector.value = "#FFFF80";
        }
        else {
            colourSelector = dwv.html.createHtmlSelect(colourSelectClassName, colours, "colour");
        }
        colourSelector.onchange = app.onChangeLineColour;

        // colour list element
        var colourLi = document.createElement("li");
        colourLi.className = colourLiClassName + " ui-block-b";
        colourLi.style.display = "none";
        //colourLi.setAttribute("class","ui-block-b");
        colourLi.appendChild(colourSelector);

        // node
        var node = app.getElement("toolList").getElementsByTagName("ul")[0];
        // apend colour
        node.appendChild(colourLi);
        // refresh
        dwv.gui.refreshElement(node);
    };

    /**
     * Display the tool HTML.
     * @param {Boolean} bool True to display, false to hide.
     */
    this.display = function (bool)
    {
        // colour list
        var node = app.getElement(colourLiClassName);
        dwv.html.displayElement(node, bool);
    };

    /**
     * Initialise the tool HTML.
     */
    this.initialise = function ()
    {
        var colourSelector = app.getElement(colourSelectClassName);
        if ( !dwv.browser.hasInputColor() ) {
            colourSelector.selectedIndex = 0;
        }
        dwv.gui.refreshElement(colourSelector);
    };

}; // class dwv.gui.base.ColourTool

/**
 * ZoomAndPan tool base gui.
 * @constructor
 */
dwv.gui.base.ZoomAndPan = function (app)
{
    /**
     * Setup the tool HTML.
     */
    this.setup = function()
    {
        // reset button
        var button = document.createElement("button");
        button.className = "zoomResetButton";
        button.name = "zoomResetButton";
        button.onclick = app.onZoomReset;
        button.setAttribute("style","width:100%; margin-top:0.5em;");
        button.setAttribute("class","ui-btn ui-btn-b");
        var text = document.createTextNode(dwv.i18n("basics.reset"));
        button.appendChild(text);

        // list element
        var liElement = document.createElement("li");
        liElement.className = "zoomLi ui-block-c";
        liElement.style.display = "none";
        //liElement.setAttribute("class","ui-block-c");
        liElement.appendChild(button);

        // node
        var node = app.getElement("toolList").getElementsByTagName("ul")[0];
        // append element
        node.appendChild(liElement);
        // refresh
        dwv.gui.refreshElement(node);
    };

    /**
     * Display the tool HTML.
     * @param {Boolean} bool True to display, false to hide.
     */
    this.display = function(bool)
    {
        // display list element
        var node = app.getElement("zoomLi");
        dwv.html.displayElement(node, bool);
    };

}; // class dwv.gui.base.ZoomAndPan

/**
 * Scroll tool base gui.
 * @constructor
 */
dwv.gui.base.Scroll = function (app)
{
    /**
     * Setup the tool HTML.
     */
    this.setup = function()
    {
        // list element
        var liElement = document.createElement("li");
        liElement.className = "scrollLi ui-block-c";
        liElement.style.display = "none";

        // node
        var node = app.getElement("toolList").getElementsByTagName("ul")[0];
        // append element
        node.appendChild(liElement);
        // refresh
        dwv.gui.refreshElement(node);
    };

    /**
     * Display the tool HTML.
     * @param {Boolean} bool True to display, false to hide.
     */
    this.display = function(bool)
    {
        // display list element
        var node = app.getElement("scrollLi");
        dwv.html.displayElement(node, bool);
    };

}; // class dwv.gui.base.Scroll

// namespaces
var dwv = dwv || {};
dwv.gui = dwv.gui || {};
dwv.gui.base = dwv.gui.base || {};

/**
 * Undo base gui.
 * @constructor
 */
dwv.gui.base.Undo = function (app)
{
    /**
     * Setup the undo HTML.
     */
    this.setup = function ()
    {
        var paragraph = document.createElement("p");
        paragraph.appendChild(document.createTextNode("History:"));
        paragraph.appendChild(document.createElement("br"));

        var select = document.createElement("select");
        select.className = "history_list";
        select.name = "history_list";
        select.multiple = "multiple";
        paragraph.appendChild(select);

        // node
        var node = app.getElement("history");
        // clear it
        while(node.hasChildNodes()) {
            node.removeChild(node.firstChild);
        }
        // append
        node.appendChild(paragraph);
        // refresh
        dwv.gui.refreshElement(node);
    };

    /**
     * Clear the command list of the undo HTML.
     */
    this.initialise = function ()
    {
        var select = app.getElement("history_list");
        if ( select && select.length !== 0 ) {
            for( var i = select.length - 1; i >= 0; --i)
            {
                select.remove(i);
            }
        }
        // refresh
        dwv.gui.refreshElement(select);
    };

    /**
     * Add a command to the undo HTML.
     * @param {String} commandName The name of the command to add.
     */
    this.addCommandToUndoHtml = function (commandName)
    {
        var select = app.getElement("history_list");
        // remove undone commands
        var count = select.length - (select.selectedIndex+1);
        if( count > 0 )
        {
            for( var i = 0; i < count; ++i)
            {
                select.remove(select.length-1);
            }
        }
        // add new option
        var option = document.createElement("option");
        option.text = commandName;
        option.value = commandName;
        select.add(option);
        // increment selected index
        select.selectedIndex++;
        // refresh
        dwv.gui.refreshElement(select);
    };

    /**
     * Enable the last command of the undo HTML.
     * @param {Boolean} enable Flag to enable or disable the command.
     */
    this.enableInUndoHtml = function (enable)
    {
        var select = app.getElement("history_list");
        // enable or not (order is important)
        var option;
        if( enable )
        {
            // increment selected index
            select.selectedIndex++;
            // enable option
            option = select.options[select.selectedIndex];
            option.disabled = false;
        }
        else
        {
            // disable option
            option = select.options[select.selectedIndex];
            option.disabled = true;
            // decrement selected index
            select.selectedIndex--;
        }
        // refresh
        dwv.gui.refreshElement(select);
    };

}; // class dwv.gui.base.Undo

// namespaces
var dwv = dwv || {};
dwv.image = dwv.image || {};

// JPEG Baseline
var hasJpegBaselineDecoder = (typeof JpegImage !== "undefined");
var JpegImage = JpegImage || {};
// JPEG Lossless
var hasJpegLosslessDecoder = (typeof jpeg !== "undefined") &&
    (typeof jpeg.lossless !== "undefined");
var jpeg = jpeg || {};
jpeg.lossless = jpeg.lossless || {};
// JPEG 2000
var hasJpeg2000Decoder = (typeof JpxImage !== "undefined");
var JpxImage = JpxImage || {};

/**
 * Asynchronous pixel buffer decoder.
 * @param {String} script The path to the decoder script to be used by the web worker.
 */
dwv.image.AsynchPixelBufferDecoder = function (script)
{
    // initialise the thread pool
    var pool = new dwv.utils.ThreadPool(15);
    pool.init();

    /**
     * Decode a pixel buffer.
     * @param {Array} pixelBuffer The pixel buffer.
     * @param {Number} bitsAllocated The bits allocated per element in the buffer.
     * @param {Boolean} isSigned Is the data signed.
     * @param {Function} callback Callback function to handle decoded data.
     */
    this.decode = function (pixelBuffer, bitsAllocated, isSigned, callback) {
        // (re)set event handler
        pool.onpoolworkend = this.ondecodeend;
        pool.onworkerend = this.ondecoded;
        // create worker task
        var workerTask = new dwv.utils.WorkerTask(script, callback, {
            'buffer': pixelBuffer,
            'bitsAllocated': bitsAllocated,
            'isSigned': isSigned } );
        // add it the queue and run it
        pool.addWorkerTask(workerTask);
    };
};

/**
 * Handle a decode end event.
 */
dwv.image.AsynchPixelBufferDecoder.prototype.ondecodeend = function ()
{
    // default does nothing.
};

/**
 * Handle a decode event.
 */
dwv.image.AsynchPixelBufferDecoder.prototype.ondecoded = function ()
{
    // default does nothing.
};

/**
 * Synchronous pixel buffer decoder.
 * @param {String} algoName The decompression algorithm name.
 */
dwv.image.SynchPixelBufferDecoder = function (algoName)
{
    /**
     * Decode a pixel buffer.
     * @param {Array} pixelBuffer The pixel buffer.
     * @param {Number} bitsAllocated The bits allocated per element in the buffer.
     * @param {Boolean} isSigned Is the data signed.
     * @return {Array} The decoded pixel buffer.
     * @external jpeg
     * @external JpegImage
     * @external JpxImage
     */
    this.decode = function (pixelBuffer, bitsAllocated, isSigned) {
        var decoder = null;
        var decodedBuffer = null;
        if( algoName === "jpeg-lossless" ) {
            if ( !hasJpegLosslessDecoder ) {
                throw new Error("No JPEG Lossless decoder provided");
            }
            // bytes per element
            var bpe = bitsAllocated / 8;
            var buf = new Uint8Array( pixelBuffer );
            decoder = new jpeg.lossless.Decoder();
            var decoded = decoder.decode(buf.buffer, 0, buf.buffer.byteLength, bpe);
            if (bitsAllocated === 8) {
                if (isSigned) {
                    decodedBuffer = new Int8Array(decoded.buffer);
                }
                else {
                    decodedBuffer = new Uint8Array(decoded.buffer);
                }
            }
            else if (bitsAllocated === 16) {
                if (isSigned) {
                    decodedBuffer = new Int16Array(decoded.buffer);
                }
                else {
                    decodedBuffer = new Uint16Array(decoded.buffer);
                }
            }
        }
        else if ( algoName === "jpeg-baseline" ) {
            if ( !hasJpegBaselineDecoder ) {
                throw new Error("No JPEG Baseline decoder provided");
            }
            decoder = new JpegImage();
            decoder.parse( pixelBuffer );
            decodedBuffer = decoder.getData(decoder.width,decoder.height);
        }
        else if( algoName === "jpeg2000" ) {
            if ( !hasJpeg2000Decoder ) {
                throw new Error("No JPEG 2000 decoder provided");
            }
            // decompress pixel buffer into Int16 image
            decoder = new JpxImage();
            decoder.parse( pixelBuffer );
            // set the pixel buffer
            decodedBuffer = decoder.tiles[0].items;
        }
        // send events
        this.ondecoded();
        this.ondecodeend();
        // return result as array
        return [decodedBuffer];
    };
};

/**
 * Handle a decode end event.
 */
dwv.image.SynchPixelBufferDecoder.prototype.ondecodeend = function ()
{
    // default does nothing.
};

/**
 * Handle a decode event.
 */
dwv.image.SynchPixelBufferDecoder.prototype.ondecoded = function ()
{
    // default does nothing.
};

/**
 * Decode a pixel buffer.
 * @constructor
 * @param {String} algoName The decompression algorithm name.
 * If the 'dwv.image.decoderScripts' variable does not contain the desired algorythm,
 * the decoder will switch to the synchronous mode.
 */
dwv.image.PixelBufferDecoder = function (algoName, asynch)
{
    /**
     * Asynchronous decoder.
     * Defined only once.
     * @private
     * @type Object
     */
    var asynchDecoder = null;

    // initialise the asynch decoder (if possible)
    if (typeof dwv.image.decoderScripts !== "undefined" &&
            typeof dwv.image.decoderScripts[algoName] !== "undefined") {
        asynchDecoder = new dwv.image.AsynchPixelBufferDecoder(dwv.image.decoderScripts[algoName]);
    }

    /**
     * Get data from an input buffer using a DICOM parser.
     * @param {Array} pixelBuffer The input data buffer.
     * @param {Number} bitsAllocated The bits allocated per element in the buffer.
     * @param {Boolean} isSigned Is the data signed.
     * @param {Object} callback The callback on the conversion.
     * @param {Boolean} asynch Should the decoder run asynchronously, default to true.
     */
    this.decode = function (pixelBuffer, bitsAllocated, isSigned, callback)
    {
        // default to asynch
        asynch = (typeof asynch === 'undefined') ? true : asynch;

        // run asynchronous if asked and we have scripts
        if (asynch && asynchDecoder !== null) {
            // (re)set event handler
            asynchDecoder.ondecodeend = this.ondecodeend;
            asynchDecoder.ondecoded = this.ondecoded;
            // decode and call the callback
            asynchDecoder.decode(pixelBuffer, bitsAllocated, isSigned, callback);
        }
        else {
            // create the decoder
            var synchDecoder = new dwv.image.SynchPixelBufferDecoder(algoName);
            synchDecoder.ondecodeend = this.ondecodeend;
            synchDecoder.ondecoded = this.ondecoded;
            // decode
            var decodedBuffer = synchDecoder.decode(pixelBuffer, bitsAllocated, isSigned);
            // call the callback
            callback({data: decodedBuffer});
        }
    };
};

/**
 * Handle a decode end event.
 */
dwv.image.PixelBufferDecoder.prototype.ondecodeend = function ()
{
    // default does nothing.
};

/**
 * Handle a decode end event.
 */
dwv.image.PixelBufferDecoder.prototype.ondecoded = function ()
{
    // default does nothing.
};

// namespaces
var dwv = dwv || {};
dwv.image = dwv.image || {};

/**
 * Create a dwv.image.View from a DICOM buffer.
 * @constructor
 */
dwv.image.DicomBufferToView = function ()
{
    // closure to self
    var self = this;

    /**
     * The default character set (optional).
     * @private
     * @type String
     */
    var defaultCharacterSet;

    /**
     * Set the default character set.
     * param {String} The character set.
     */
    this.setDefaultCharacterSet = function (characterSet) {
        defaultCharacterSet = characterSet;
    };

    /**
     * Pixel buffer decoder.
     * Define only once to allow optional asynchronous mode.
     * @private
     * @type Object
     */
    var pixelDecoder = null;

    /**
     * Get data from an input buffer using a DICOM parser.
     * @param {Array} buffer The input data buffer.
     * @param {Number} dataIndex The data index.
     */
    this.convert = function (buffer, dataIndex)
    {
        // DICOM parser
        var dicomParser = new dwv.dicom.DicomParser();
        dicomParser.setDefaultCharacterSet(defaultCharacterSet);
        // parse the buffer
        dicomParser.parse(buffer);

        var pixelBuffer = dicomParser.getRawDicomElements().x7FE00010.value;
        var syntax = dwv.dicom.cleanString(dicomParser.getRawDicomElements().x00020010.value[0]);
        var algoName = dwv.dicom.getSyntaxDecompressionName(syntax);
        var needDecompression = (algoName !== null);

        // worker callback
        var onDecodedFirstFrame = function (/*event*/) {
            // create the image
            var imageFactory = new dwv.image.ImageFactory();
            var image = imageFactory.create( dicomParser.getDicomElements(), pixelBuffer );
            // create the view
            var viewFactory = new dwv.image.ViewFactory();
            var view = viewFactory.create( dicomParser.getDicomElements(), image );
            // return
            self.onload({"view": view, "info": dicomParser.getDicomElements().dumpToTable()});
        };

        if ( needDecompression ) {
            var bitsAllocated = dicomParser.getRawDicomElements().x00280100.value[0];
            var pixelRepresentation = dicomParser.getRawDicomElements().x00280103.value[0];
            var isSigned = (pixelRepresentation === 1);
            var nFrames = pixelBuffer.length;

            if (!pixelDecoder){
                pixelDecoder = new dwv.image.PixelBufferDecoder(algoName);
            }

            // loadend event
            pixelDecoder.ondecodeend = function () {
                self.onloadend();
            };

            // send an onload event for mono frame
            if ( nFrames === 1 ) {
                pixelDecoder.ondecoded = function () {
                    self.onloadend();
                };
            }

            // decoder callback
            var countDecodedFrames = 0;
            var onDecodedFrame = function (frame) {
                return function (event) {
                    // send progress
                    ++countDecodedFrames;
                    var ev = {'type': "load-progress", 'lengthComputable': true,
                        'loaded': (countDecodedFrames * 100 / nFrames), 'total': 100};
                    if ( typeof dataIndex !== "undefined") {
                        ev.index = dataIndex;
                    }
                    self.onprogress(ev);
                    // store data
                    pixelBuffer[frame] = event.data[0];
                    // create image for first frame
                    if ( frame === 0 ) {
                        onDecodedFirstFrame();
                    }
                };
            };

            // decompress synchronously the first frame to create the image
            pixelDecoder.decode(pixelBuffer[0],
                bitsAllocated, isSigned, onDecodedFrame(0), false);

            // decompress the possible other frames
            if ( nFrames !== 1 ) {
                // decode (asynchronously if possible)
                for (var f = 1; f < nFrames; ++f) {
                    pixelDecoder.decode(pixelBuffer[f],
                        bitsAllocated, isSigned, onDecodedFrame(f));
                }
            }
        }
        // no decompression
        else {
            // send progress
            var evnodec = {'type': 'load-progress', 'lengthComputable': true,
                'loaded': 100, 'total': 100};
            if ( typeof dataIndex !== "undefined") {
                evnodec.index = dataIndex;
            }
            self.onprogress(evnodec);
            // create image
            onDecodedFirstFrame();
            // send load events
            self.onloadend();
        }
    };
};

/**
 * Handle a load end event.
 * @param {Object} event The load end event.
 * Default does nothing.
 */
dwv.image.DicomBufferToView.prototype.onloadend = function (/*event*/) {};
/**
 * Handle a load event.
 * @param {Object} event The load event.
 * Default does nothing.
 */
dwv.image.DicomBufferToView.prototype.onload = function  (/*event*/) {};
/**
 * Handle a load progress event.
 * @param {Object} event The progress event.
 * Default does nothing.
 */
dwv.image.DicomBufferToView.prototype.onprogress = function  (/*event*/) {};

// namespaces
var dwv = dwv || {};
dwv.image = dwv.image || {};

/**
 * Create a simple array buffer from an ImageData buffer.
 * @param {Object} imageData The ImageData taken from a context.
 * @return {Array} The image buffer.
 */
dwv.image.imageDataToBuffer = function (imageData) {
    // remove alpha
    // TODO support passing the full image data
    var dataLen = imageData.data.length;
    var buffer = new Uint8Array( (dataLen / 4) * 3);
    var j = 0;
    for( var i = 0; i < dataLen; i+=4 ) {
        buffer[j] = imageData.data[i];
        buffer[j+1] = imageData.data[i+1];
        buffer[j+2] = imageData.data[i+2];
        j+=3;
    }
    return buffer;
};

/**
 * Get data from an input context imageData.
 * @param {Number} width The width of the coresponding image.
 * @param {Number} height The height of the coresponding image.
 * @param {Number} sliceIndex The slice index of the imageData.
 * @param {Object} imageBuffer The image buffer.
 * @param {Number} numberOfFrames The final number of frames.
 * @return {Object} The corresponding view.
 */
dwv.image.getDefaultView = function (
    width, height, sliceIndex,
    imageBuffer, numberOfFrames, info) {
    // image size
    var imageSize = new dwv.image.Size(width, height);
    // default spacing
    // TODO: misleading...
    var imageSpacing = new dwv.image.Spacing(1,1);
    // default origin
    var origin = new dwv.math.Point3D(0,0,sliceIndex);
    // create image
    var geometry = new dwv.image.Geometry(origin, imageSize, imageSpacing );
    var image = new dwv.image.Image( geometry, imageBuffer, numberOfFrames );
    image.setPhotometricInterpretation("RGB");
    // meta information
    var meta = {};
    meta.BitsStored = 8;
    image.setMeta(meta);
    // overlay
    image.setFirstOverlay( dwv.gui.info.createOverlaysForDom(info) );
    // view
    var view = new dwv.image.View(image);
    // defaut preset
    view.setWindowLevelMinMax();
    // return
    return view;
};

/**
 * Get data from an input image using a canvas.
 * @param {Object} image The DOM Image.
 * @return {Mixed} The corresponding view and info.
 */
dwv.image.getViewFromDOMImage = function (image)
{
    // image size
    var width = image.width;
    var height = image.height;

    // draw the image in the canvas in order to get its data
    var canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    var ctx = canvas.getContext('2d');
    ctx.drawImage(image, 0, 0);
    // get the image data
    var imageData = ctx.getImageData(0, 0, width, height);

    // image properties
    var info = [];
    if ( typeof image.origin === "string" ) {
        info.push({ "name": "origin", "value": image.origin });
    } else {
        info.push({ "name": "fileName", "value": image.origin.name });
        info.push({ "name": "fileType", "value": image.origin.type });
        info.push({ "name": "fileLastModifiedDate", "value": image.origin.lastModifiedDate });
    }
    info.push({ "name": "imageWidth", "value": width });
    info.push({ "name": "imageHeight", "value": height });

    // create view
    var sliceIndex = image.index ? image.index : 0;
    var imageBuffer = dwv.image.imageDataToBuffer(imageData);
    var view = dwv.image.getDefaultView(
        width, height, sliceIndex, [imageBuffer], 1, info);

    // return
    return {"view": view, "info": info};
};

/**
 * Get data from an input image using a canvas.
 * @param {Object} video The DOM Video.
 * @param {Object} callback The function to call once the data is loaded.
 * @param {Object} cbprogress The function to call to report progress.
 * @param {Object} cbonloadend The function to call to report load end.
 * @param {Number} dataindex The data index.
 */
dwv.image.getViewFromDOMVideo = function (video, callback, cbprogress, cbonloadend, dataIndex)
{
    // video size
    var width = video.videoWidth;
    var height = video.videoHeight;

    // default frame rate...
    var frameRate = 30;
    // number of frames
    var numberOfFrames = Math.floor(video.duration * frameRate);

    // video properties
    var info = [];
    if( video.file )
    {
        info.push({ "name": "fileName", "value": video.file.name });
        info.push({ "name": "fileType", "value": video.file.type });
        info.push({ "name": "fileLastModifiedDate", "value": video.file.lastModifiedDate });
    }
    info.push({ "name": "imageWidth", "value": width });
    info.push({ "name": "imageHeight", "value": height });
    info.push({ "name": "numberOfFrames", "value": numberOfFrames });

    // draw the image in the canvas in order to get its data
    var canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    var ctx = canvas.getContext('2d');

    // using seeked to loop through all video frames
    video.addEventListener('seeked', onseeked, false);

    // current frame index
    var frameIndex = 0;
    // video view
    var view = null;

    // draw the context and store it as a frame
    function storeFrame() {
        // send progress
        var evprog = {'type': 'load-progress', 'lengthComputable': true,
            'loaded': frameIndex, 'total': numberOfFrames};
        if (typeof dataIndex !== "undefined") {
            evprog.index = dataIndex;
        }
        cbprogress(evprog);
        // draw image
        ctx.drawImage(video, 0, 0);
        // context to image buffer
        var imgBuffer = dwv.image.imageDataToBuffer(
            ctx.getImageData(0, 0, width, height) );
        if (frameIndex === 0) {
            // create view
            view = dwv.image.getDefaultView(
                width, height, 1, [imgBuffer], numberOfFrames, info);
            // call callback
            callback( {"view": view, "info": info } );
        } else {
            view.appendFrameBuffer(imgBuffer);
        }
    }

    // handle seeked event
    function onseeked(/*event*/) {
        // store
        storeFrame();
        // increment index
        ++frameIndex;
        // set the next time
        // (not using currentTime, it seems to get offseted)
        var nextTime = frameIndex / frameRate;
        if (nextTime <= this.duration) {
            this.currentTime = nextTime;
        } else {
            cbonloadend();
            // stop listening
            video.removeEventListener('seeked', onseeked);
        }
    }

    // trigger the first seeked
    video.currentTime = 0;
};

// namespaces
var dwv = dwv || {};
dwv.image = dwv.image || {};
/** @namespace */
dwv.image.filter = dwv.image.filter || {};

/**
 * Threshold an image between an input minimum and maximum.
 * @constructor
 */
dwv.image.filter.Threshold = function()
{
    /**
     * Threshold minimum.
     * @private
     * @type Number
     */
    var min = 0;
    /**
     * Threshold maximum.
     * @private
     * @type Number
     */
    var max = 0;

    /**
     * Get the threshold minimum.
     * @return {Number} The threshold minimum.
     */
    this.getMin = function() { return min; };
    /**
     * Set the threshold minimum.
     * @param {Number} val The threshold minimum.
     */
    this.setMin = function(val) { min = val; };
    /**
     * Get the threshold maximum.
     * @return {Number} The threshold maximum.
     */
    this.getMax = function() { return max; };
    /**
     * Set the threshold maximum.
     * @param {Number} val The threshold maximum.
     */
    this.setMax = function(val) { max = val; };
    /**
     * Get the name of the filter.
     * @return {String} The name of the filter.
     */
    this.getName = function() { return "Threshold"; };

    /**
     * Original image.
     * @private
     * @type Object
     */
    var originalImage = null;
    /**
     * Set the original image.
     * @param {Object} image The original image.
     */
    this.setOriginalImage = function (image) { originalImage = image; };
    /**
     * Get the original image.
     * @return {Object} image The original image.
     */
    this.getOriginalImage = function () { return originalImage; };
};

/**
 * Transform the main image using this filter.
 * @return {Object} The transformed image.
 */
dwv.image.filter.Threshold.prototype.update = function ()
{
    var image = this.getOriginalImage();
    var imageMin = image.getDataRange().min;
    var self = this;
    var threshFunction = function (value) {
        if ( value < self.getMin() || value > self.getMax() ) {
            return imageMin;
        }
        else {
            return value;
        }
    };
    return image.transform( threshFunction );
};

/**
 * Sharpen an image using a sharpen convolution matrix.
 * @constructor
 */
dwv.image.filter.Sharpen = function()
{
    /**
     * Get the name of the filter.
     * @return {String} The name of the filter.
     */
    this.getName = function() { return "Sharpen"; };
    /**
     * Original image.
     * @private
     * @type Object
     */
    var originalImage = null;
    /**
     * Set the original image.
     * @param {Object} image The original image.
     */
    this.setOriginalImage = function (image) { originalImage = image; };
    /**
     * Get the original image.
     * @return {Object} image The original image.
     */
    this.getOriginalImage = function () { return originalImage; };
};

/**
 * Transform the main image using this filter.
 * @return {Object} The transformed image.
 */
dwv.image.filter.Sharpen.prototype.update = function()
{
    var image = this.getOriginalImage();

    return image.convolute2D(
        [  0, -1,  0,
          -1,  5, -1,
           0, -1,  0 ] );
};

/**
 * Apply a Sobel filter to an image.
 * @constructor
 */
dwv.image.filter.Sobel = function()
{
    /**
     * Get the name of the filter.
     * @return {String} The name of the filter.
     */
    this.getName = function() { return "Sobel"; };
    /**
     * Original image.
     * @private
     * @type Object
     */
    var originalImage = null;
    /**
     * Set the original image.
     * @param {Object} image The original image.
     */
    this.setOriginalImage = function (image) { originalImage = image; };
    /**
     * Get the original image.
     * @return {Object} image The original image.
     */
    this.getOriginalImage = function () { return originalImage; };
};

/**
 * Transform the main image using this filter.
 * @return {Object} The transformed image.
 */
dwv.image.filter.Sobel.prototype.update = function()
{
    var image = this.getOriginalImage();

    var gradX = image.convolute2D(
        [ 1,  0,  -1,
          2,  0,  -2,
          1,  0,  -1 ] );

    var gradY = image.convolute2D(
        [  1,  2,  1,
           0,  0,  0,
          -1, -2, -1 ] );

    return gradX.compose( gradY, function (x,y) { return Math.sqrt(x*x+y*y); } );
};

// namespaces
var dwv = dwv || {};
dwv.image = dwv.image || {};

/**
 * 2D/3D Size class.
 * @constructor
 * @param {Number} numberOfColumns The number of columns.
 * @param {Number} numberOfRows The number of rows.
 * @param {Number} numberOfSlices The number of slices.
*/
dwv.image.Size = function ( numberOfColumns, numberOfRows, numberOfSlices )
{
    /**
     * Get the number of columns.
     * @return {Number} The number of columns.
     */
    this.getNumberOfColumns = function () { return numberOfColumns; };
    /**
     * Get the number of rows.
     * @return {Number} The number of rows.
     */
    this.getNumberOfRows = function () { return numberOfRows; };
    /**
     * Get the number of slices.
     * @return {Number} The number of slices.
     */
    this.getNumberOfSlices = function () { return (numberOfSlices || 1.0); };
};

/**
 * Get the size of a slice.
 * @return {Number} The size of a slice.
 */
dwv.image.Size.prototype.getSliceSize = function () {
    return this.getNumberOfColumns() * this.getNumberOfRows();
};

/**
 * Get the total size.
 * @return {Number} The total size.
 */
dwv.image.Size.prototype.getTotalSize = function () {
    return this.getSliceSize() * this.getNumberOfSlices();
};

/**
 * Check for equality.
 * @param {Size} rhs The object to compare to.
 * @return {Boolean} True if both objects are equal.
 */
dwv.image.Size.prototype.equals = function (rhs) {
    return rhs !== null &&
        this.getNumberOfColumns() === rhs.getNumberOfColumns() &&
        this.getNumberOfRows() === rhs.getNumberOfRows() &&
        this.getNumberOfSlices() === rhs.getNumberOfSlices();
};

/**
 * Check that coordinates are within bounds.
 * @param {Number} i The column coordinate.
 * @param {Number} j The row coordinate.
 * @param {Number} k The slice coordinate.
 * @return {Boolean} True if the given coordinates are within bounds.
 */
dwv.image.Size.prototype.isInBounds = function ( i, j, k ) {
    if( i < 0 || i > this.getNumberOfColumns() - 1 ||
        j < 0 || j > this.getNumberOfRows() - 1 ||
        k < 0 || k > this.getNumberOfSlices() - 1 ) {
        return false;
    }
    return true;
};

/**
 * Get a string representation of the Vector3D.
 * @return {String} The vector as a string.
 */
dwv.image.Size.prototype.toString = function () {
    return "(" + this.getNumberOfColumns() +
        ", " + this.getNumberOfRows() +
        ", " + this.getNumberOfSlices() + ")";
};

/**
 * 2D/3D Spacing class.
 * @constructor
 * @param {Number} columnSpacing The column spacing.
 * @param {Number} rowSpacing The row spacing.
 * @param {Number} sliceSpacing The slice spacing.
 */
dwv.image.Spacing = function ( columnSpacing, rowSpacing, sliceSpacing )
{
    /**
     * Get the column spacing.
     * @return {Number} The column spacing.
     */
    this.getColumnSpacing = function () { return columnSpacing; };
    /**
     * Get the row spacing.
     * @return {Number} The row spacing.
     */
    this.getRowSpacing = function () { return rowSpacing; };
    /**
     * Get the slice spacing.
     * @return {Number} The slice spacing.
     */
    this.getSliceSpacing = function () { return (sliceSpacing || 1.0); };
};

/**
 * Check for equality.
 * @param {Spacing} rhs The object to compare to.
 * @return {Boolean} True if both objects are equal.
 */
dwv.image.Spacing.prototype.equals = function (rhs) {
    return rhs !== null &&
        this.getColumnSpacing() === rhs.getColumnSpacing() &&
        this.getRowSpacing() === rhs.getRowSpacing() &&
        this.getSliceSpacing() === rhs.getSliceSpacing();
};

/**
 * Get a string representation of the Vector3D.
 * @return {String} The vector as a string.
 */
dwv.image.Spacing.prototype.toString = function () {
    return "(" + this.getColumnSpacing() +
        ", " + this.getRowSpacing() +
        ", " + this.getSliceSpacing() + ")";
};


/**
 * 2D/3D Geometry class.
 * @constructor
 * @param {Object} origin The object origin (a 3D point).
 * @param {Object} size The object size.
 * @param {Object} spacing The object spacing.
 * @param {Object} orientation The object orientation (3*3 matrix, default to 3*3 identity).
 */
dwv.image.Geometry = function ( origin, size, spacing, orientation )
{
    // check input origin
    if( typeof origin === 'undefined' ) {
        origin = new dwv.math.Point3D(0,0,0);
    }
    var origins = [origin];
    // check input orientation
    if( typeof orientation === 'undefined' ) {
        orientation = new dwv.math.getIdentityMat33();
    }

    /**
     * Get the object first origin.
     * @return {Object} The object first origin.
     */
    this.getOrigin = function () { return origin; };
    /**
     * Get the object origins.
     * @return {Array} The object origins.
     */
    this.getOrigins = function () { return origins; };
    /**
     * Get the object size.
     * @return {Object} The object size.
     */
    this.getSize = function () { return size; };
    /**
     * Get the object spacing.
     * @return {Object} The object spacing.
     */
    this.getSpacing = function () { return spacing; };
    /**
     * Get the object orientation.
     * @return {Object} The object orientation.
     */
    this.getOrientation = function () { return orientation; };

    /**
     * Get the slice position of a point in the current slice layout.
     * @param {Object} point The point to evaluate.
     */
    this.getSliceIndex = function (point)
    {
        // cannot use this.worldToIndex(point).getK() since
        // we cannot guaranty consecutive slices...

        // find the closest index
        var closestSliceIndex = 0;
        var minDist = point.getDistance(origins[0]);
        var dist = 0;
        for( var i = 0; i < origins.length; ++i )
        {
            dist = point.getDistance(origins[i]);
            if( dist < minDist )
            {
                minDist = dist;
                closestSliceIndex = i;
            }
        }
        // we have the closest point, are we before or after
        var normal = new dwv.math.Vector3D(
            orientation.get(2,0), orientation.get(2,1), orientation.get(2,2));
        var dotProd = normal.dotProduct( point.minus(origins[closestSliceIndex]) );
        var sliceIndex = ( dotProd > 0 ) ? closestSliceIndex + 1 : closestSliceIndex;
        return sliceIndex;
    };

    /**
     * Append an origin to the geometry.
     * @param {Object} origin The origin to append.
     * @param {Number} index The index at which to append.
     */
    this.appendOrigin = function (origin, index)
    {
        // add in origin array
        origins.splice(index, 0, origin);
        // increment slice number
        size = new dwv.image.Size(
            size.getNumberOfColumns(),
            size.getNumberOfRows(),
            size.getNumberOfSlices() + 1);
    };

};

/**
 * Check for equality.
 * @param {Geometry} rhs The object to compare to.
 * @return {Boolean} True if both objects are equal.
 */
dwv.image.Geometry.prototype.equals = function (rhs) {
    return rhs !== null &&
        this.getOrigin() === rhs.getOrigin() &&
        this.getSize() === rhs.getSize() &&
        this.getSpacing() === rhs.getSpacing();
};

/**
 * Convert an index to an offset in memory.
 * @param {Object} index The index to convert.
 */
dwv.image.Geometry.prototype.indexToOffset = function (index) {
    var size = this.getSize();
    return index.getI() +
       index.getJ() * size.getNumberOfColumns() +
       index.getK() * size.getSliceSize();
};

/**
 * Convert an index into world coordinates.
 * @param {Object} index The index to convert.
 */
dwv.image.Geometry.prototype.indexToWorld = function (index) {
    var origin = this.getOrigin();
    var spacing = this.getSpacing();
    return new dwv.math.Point3D(
        origin.getX() + index.getI() * spacing.getColumnSpacing(),
        origin.getY() + index.getJ() * spacing.getRowSpacing(),
        origin.getZ() + index.getK() * spacing.getSliceSpacing() );
};

/**
 * Convert world coordinates into an index.
 * @param {Object} THe point to convert.
 */
dwv.image.Geometry.prototype.worldToIndex = function (point) {
    var origin = this.getOrigin();
    var spacing = this.getSpacing();
    return new dwv.math.Point3D(
        point.getX() / spacing.getColumnSpacing() - origin.getX(),
        point.getY() / spacing.getRowSpacing() - origin.getY(),
        point.getZ() / spacing.getSliceSpacing() - origin.getZ() );
};

// namespaces
var dwv = dwv || {};
/** @namespace */
dwv.image = dwv.image || {};

/**
 * Rescale Slope and Intercept
 * @constructor
 * @param slope
 * @param intercept
 */
dwv.image.RescaleSlopeAndIntercept = function (slope, intercept)
{
    /*// Check the rescale slope.
    if(typeof(slope) === 'undefined') {
        slope = 1;
    }
    // Check the rescale intercept.
    if(typeof(intercept) === 'undefined') {
        intercept = 0;
    }*/

    /**
     * Get the slope of the RSI.
     * @return {Number} The slope of the RSI.
     */
    this.getSlope = function ()
    {
        return slope;
    };
    /**
     * Get the intercept of the RSI.
     * @return {Number} The intercept of the RSI.
     */
    this.getIntercept = function ()
    {
        return intercept;
    };
    /**
     * Apply the RSI on an input value.
     * @return {Number} The value to rescale.
     */
    this.apply = function (value)
    {
        return value * slope + intercept;
    };
};

/**
 * Check for RSI equality.
 * @param {Object} rhs The other RSI to compare to.
 * @return {Boolean} True if both RSI are equal.
 */
dwv.image.RescaleSlopeAndIntercept.prototype.equals = function (rhs) {
    return rhs !== null &&
        this.getSlope() === rhs.getSlope() &&
        this.getIntercept() === rhs.getIntercept();
};

/**
 * Get a string representation of the RSI.
 * @return {String} The RSI as a string.
 */
dwv.image.RescaleSlopeAndIntercept.prototype.toString = function () {
    return (this.getSlope() + ", " + this.getIntercept());
};

/**
 * Is this RSI an ID RSI.
 * @return {Boolean} True if the RSI has a slope of 1 and no intercept.
 */
dwv.image.RescaleSlopeAndIntercept.prototype.isID = function () {
    return (this.getSlope() === 1 && this.getIntercept() === 0);
};

/**
 * Image class.
 * Usable once created, optional are:
 * - rescale slope and intercept (default 1:0),
 * - photometric interpretation (default MONOCHROME2),
 * - planar configuration (default RGBRGB...).
 * @constructor
 * @param {Object} geometry The geometry of the image.
 * @param {Array} buffer The image data as an array of frame buffers.
 * @param {Number} numberOfFrames The number of frames (optional, can be used
     to anticipate the final number after appends).
 */
dwv.image.Image = function(geometry, buffer, numberOfFrames)
{
    // use buffer length in not specified
    if (typeof numberOfFrames === "undefined") {
        numberOfFrames = buffer.length;
    }

    /**
     * Get the number of frames.
     * @returns {Number} The number of frames.
     */
    this.getNumberOfFrames = function () {
        return numberOfFrames;
    };

    /**
     * Rescale slope and intercept.
     * @private
     * @type Number
     */
    var rsis = [];
    // initialise RSIs
    for ( var s = 0, nslices = geometry.getSize().getNumberOfSlices(); s < nslices; ++s ) {
        rsis.push( new dwv.image.RescaleSlopeAndIntercept( 1, 0 ) );
    }
    /**
     * Flag to know if the RSIs are all identity (1,0).
     * @private
     * @type Boolean
     */
    var isIdentityRSI = true;
    /**
     * Flag to know if the RSIs are all equals.
     * @private
     * @type Boolean
     */
    var isConstantRSI = true;
    /**
     * Photometric interpretation (MONOCHROME, RGB...).
     * @private
     * @type String
     */
    var photometricInterpretation = "MONOCHROME2";
    /**
     * Planar configuration for RGB data (0:RGBRGBRGBRGB... or 1:RRR...GGG...BBB...).
     * @private
     * @type Number
     */
    var planarConfiguration = 0;
    /**
     * Number of components.
     * @private
     * @type Number
     */
    var numberOfComponents = buffer[0].length / (
        geometry.getSize().getTotalSize() );
    /**
     * Meta information.
     * @private
     * @type Object
     */
    var meta = {};

    /**
     * Data range.
     * @private
     * @type Object
     */
    var dataRange = null;
    /**
     * Rescaled data range.
     * @private
     * @type Object
     */
    var rescaledDataRange = null;
    /**
     * Histogram.
     * @private
     * @type Array
     */
    var histogram = null;

	/**
	 * Overlay.
     * @private
     * @type Array
     */
	var overlays = [];

    /**
     * Set the first overlay.
     * @param {Array} over The first overlay.
     */
    this.setFirstOverlay = function (over) { overlays[0] = over; };

    /**
     * Get the overlays.
     * @return {Array} The overlays array.
     */
    this.getOverlays = function () { return overlays; };

    /**
     * Get the geometry of the image.
     * @return {Object} The size of the image.
     */
    this.getGeometry = function() { return geometry; };

    /**
     * Get the data buffer of the image.
     * @todo dangerous...
     * @return {Array} The data buffer of the image.
     */
    this.getBuffer = function() { return buffer; };
    /**
     * Get the data buffer of the image.
     * @todo dangerous...
     * @return {Array} The data buffer of the frame.
     */
    this.getFrame = function (frame) { return buffer[frame]; };

    /**
     * Get the rescale slope and intercept.
     * @param {Number} k The slice index.
     * @return {Object} The rescale slope and intercept.
     */
    this.getRescaleSlopeAndIntercept = function(k) { return rsis[k]; };
    /**
     * Set the rescale slope and intercept.
     * @param {Array} inRsi The input rescale slope and intercept.
     * @param {Number} k The slice index (optional).
     */
    this.setRescaleSlopeAndIntercept = function(inRsi, k) {
        if ( typeof k === 'undefined' ) {
            k = 0;
        }
        rsis[k] = inRsi;

        // update RSI flags
        isIdentityRSI = true;
        isConstantRSI = true;
        for ( var s = 0, lens = rsis.length; s < lens; ++s ) {
            if (!rsis[s].isID()) {
                isIdentityRSI = false;
            }
            if (s > 0 && !rsis[s].equals(rsis[s-1])) {
                isConstantRSI = false;
            }
        }
    };
    /**
     * Are all the RSIs identity (1,0).
     * @return {Boolean} True if they are.
     */
    this.isIdentityRSI = function () { return isIdentityRSI; };
    /**
     * Are all the RSIs equal.
     * @return {Boolean} True if they are.
     */
    this.isConstantRSI = function () { return isConstantRSI; };
    /**
     * Get the photometricInterpretation of the image.
     * @return {String} The photometricInterpretation of the image.
     */
    this.getPhotometricInterpretation = function() { return photometricInterpretation; };
    /**
     * Set the photometricInterpretation of the image.
     * @pqrqm {String} interp The photometricInterpretation of the image.
     */
    this.setPhotometricInterpretation = function(interp) { photometricInterpretation = interp; };
    /**
     * Get the planarConfiguration of the image.
     * @return {Number} The planarConfiguration of the image.
     */
    this.getPlanarConfiguration = function() { return planarConfiguration; };
    /**
     * Set the planarConfiguration of the image.
     * @param {Number} config The planarConfiguration of the image.
     */
    this.setPlanarConfiguration = function(config) { planarConfiguration = config; };
    /**
     * Get the numberOfComponents of the image.
     * @return {Number} The numberOfComponents of the image.
     */
    this.getNumberOfComponents = function() { return numberOfComponents; };

    /**
     * Get the meta information of the image.
     * @return {Object} The meta information of the image.
     */
    this.getMeta = function() { return meta; };
    /**
     * Set the meta information of the image.
     * @param {Object} rhs The meta information of the image.
     */
    this.setMeta = function(rhs) { meta = rhs; };

    /**
     * Get value at offset. Warning: No size check...
     * @param {Number} offset The desired offset.
     * @param {Number} frame The desired frame.
     * @return {Number} The value at offset.
     */
    this.getValueAtOffset = function (offset, frame) {
        return buffer[frame][offset];
    };

    /**
     * Clone the image.
     * @return {Image} A clone of this image.
     */
    this.clone = function()
    {
        // clone the image buffer
        var clonedBuffer = [];
        for (var f = 0, lenf = this.getNumberOfFrames(); f < lenf; ++f) {
            clonedBuffer[f] = buffer[f].slice(0);
        }
        // create the image copy
        var copy = new dwv.image.Image(this.getGeometry(), clonedBuffer);
        // copy the RSIs
        var nslices = this.getGeometry().getSize().getNumberOfSlices();
        for ( var k = 0; k < nslices; ++k ) {
            copy.setRescaleSlopeAndIntercept(this.getRescaleSlopeAndIntercept(k), k);
        }
        // copy extras
        copy.setPhotometricInterpretation(this.getPhotometricInterpretation());
        copy.setPlanarConfiguration(this.getPlanarConfiguration());
        copy.setMeta(this.getMeta());
        // return
        return copy;
    };

    /**
     * Append a slice to the image.
     * @param {Image} The slice to append.
     * @return {Number} The number of the inserted slice.
     */
    this.appendSlice = function (rhs, frame)
    {
        // check input
        if( rhs === null ) {
            throw new Error("Cannot append null slice");
        }
        var rhsSize = rhs.getGeometry().getSize();
        var size = geometry.getSize();
        if( rhsSize.getNumberOfSlices() !== 1 ) {
            throw new Error("Cannot append more than one slice");
        }
        if( size.getNumberOfColumns() !== rhsSize.getNumberOfColumns() ) {
            throw new Error("Cannot append a slice with different number of columns");
        }
        if( size.getNumberOfRows() !== rhsSize.getNumberOfRows() ) {
            throw new Error("Cannot append a slice with different number of rows");
        }
        if( photometricInterpretation !== rhs.getPhotometricInterpretation() ) {
            throw new Error("Cannot append a slice with different photometric interpretation");
        }
        // all meta should be equal
        for( var key in meta ) {
            if( meta[key] !== rhs.getMeta()[key] ) {
                throw new Error("Cannot append a slice with different "+key);
            }
        }

        var f = (typeof frame === "undefined") ? 0 : frame;

        // calculate slice size
        var mul = 1;
        if( photometricInterpretation === "RGB" || photometricInterpretation === "YBR_FULL_422") {
            mul = 3;
        }
        var sliceSize = mul * size.getSliceSize();

        // create the new buffer
        var newBuffer = dwv.dicom.getTypedArray(
            buffer[f].BYTES_PER_ELEMENT * 8,
            meta.IsSigned ? 1 : 0,
            sliceSize * (size.getNumberOfSlices() + 1) );

        // append slice at new position
        var newSliceNb = geometry.getSliceIndex( rhs.getGeometry().getOrigin() );
        if( newSliceNb === 0 )
        {
            newBuffer.set(rhs.getFrame(f));
            newBuffer.set(buffer[f], sliceSize);
        }
        else if( newSliceNb === size.getNumberOfSlices() )
        {
            newBuffer.set(buffer[f]);
            newBuffer.set(rhs.getFrame(f), size.getNumberOfSlices() * sliceSize);
        }
        else
        {
            var offset = newSliceNb * sliceSize;
            newBuffer.set(buffer[f].subarray(0, offset - 1));
            newBuffer.set(rhs.getFrame(f), offset);
            newBuffer.set(buffer[f].subarray(offset), offset + sliceSize);
        }

        // update geometry
        geometry.appendOrigin( rhs.getGeometry().getOrigin(), newSliceNb );
        // update rsi
        rsis.splice(newSliceNb, 0, rhs.getRescaleSlopeAndIntercept(0));

        // copy to class variables
        buffer[f] = newBuffer;

		// insert overlay information of the slice to the image
		overlays.splice(newSliceNb, 0, rhs.getOverlays()[0]);

        // return the appended slice number
        return newSliceNb;
    };

    /**
     * Append a frame buffer to the image.
     * @param {Object} frameBuffer The frame buffer to append.
     */
    this.appendFrameBuffer = function (frameBuffer)
    {
        buffer.push(frameBuffer);
    };

    /**
     * Get the data range.
     * @return {Object} The data range.
     */
    this.getDataRange = function() {
        if( !dataRange ) {
            dataRange = this.calculateDataRange();
        }
        return dataRange;
    };

    /**
     * Get the rescaled data range.
     * @return {Object} The rescaled data range.
     */
    this.getRescaledDataRange = function() {
        if( !rescaledDataRange ) {
            rescaledDataRange = this.calculateRescaledDataRange();
        }
        return rescaledDataRange;
    };

    /**
     * Get the histogram.
     * @return {Array} The histogram.
     */
    this.getHistogram = function() {
        if( !histogram ) {
            var res = this.calculateHistogram();
            dataRange = res.dataRange;
            rescaledDataRange = res.rescaledDataRange;
            histogram = res.histogram;
        }
        return histogram;
    };
};

/**
 * Get the value of the image at a specific coordinate.
 * @param {Number} i The X index.
 * @param {Number} j The Y index.
 * @param {Number} k The Z index.
 * @param {Number} f The frame number.
 * @return {Number} The value at the desired position.
 * Warning: No size check...
 */
dwv.image.Image.prototype.getValue = function( i, j, k, f )
{
    var frame = (f || 0);
    var index = new dwv.math.Index3D(i,j,k);
    return this.getValueAtOffset( this.getGeometry().indexToOffset(index), frame );
};

/**
 * Get the rescaled value of the image at a specific coordinate.
 * @param {Number} i The X index.
 * @param {Number} j The Y index.
 * @param {Number} k The Z index.
 * @param {Number} f The frame number.
 * @return {Number} The rescaled value at the desired position.
 * Warning: No size check...
 */
dwv.image.Image.prototype.getRescaledValue = function( i, j, k, f )
{
    var frame = (f || 0);
    var val = this.getValue(i,j,k,frame);
    if (!this.isIdentityRSI()) {
        val = this.getRescaleSlopeAndIntercept(k).apply(val);
    }
    return val;
};

/**
 * Calculate the data range of the image.
 * WARNING: for speed reasons, only calculated on the first frame...
 * @return {Object} The range {min, max}.
 */
dwv.image.Image.prototype.calculateDataRange = function ()
{
    var size = this.getGeometry().getSize().getTotalSize();
    var nFrames = 1; //this.getNumberOfFrames();
    var min = this.getValueAtOffset(0,0);
    var max = min;
    var value = 0;
    for ( var f = 0; f < nFrames; ++f ) {
        for ( var i = 0; i < size; ++i ) {
            value = this.getValueAtOffset(i,f);
            if( value > max ) { max = value; }
            if( value < min ) { min = value; }
        }
    }
    // return
    return { "min": min, "max": max };
};

/**
 * Calculate the rescaled data range of the image.
 * WARNING: for speed reasons, only calculated on the first frame...
 * @return {Object} The range {min, max}.
 */
dwv.image.Image.prototype.calculateRescaledDataRange = function ()
{
    if (this.isIdentityRSI()) {
        return this.getDataRange();
    }
    else if (this.isConstantRSI()) {
        var range = this.getDataRange();
        var resmin = this.getRescaleSlopeAndIntercept(0).apply(range.min);
        var resmax = this.getRescaleSlopeAndIntercept(0).apply(range.max);
        return {
            "min": ((resmin < resmax) ? resmin : resmax),
            "max": ((resmin > resmax) ? resmin : resmax)
        };
    }
    else {
        var size = this.getGeometry().getSize();
        var nFrames = 1; //this.getNumberOfFrames();
        var rmin = this.getRescaledValue(0,0,0);
        var rmax = rmin;
        var rvalue = 0;
        for ( var f = 0, nframes = nFrames; f < nframes; ++f ) {
            for ( var k = 0, nslices = size.getNumberOfSlices(); k < nslices; ++k ) {
                for ( var j = 0, nrows = size.getNumberOfRows(); j < nrows; ++j ) {
                    for ( var i = 0, ncols = size.getNumberOfColumns(); i < ncols; ++i ) {
                        rvalue = this.getRescaledValue(i,j,k,f);
                        if( rvalue > rmax ) { rmax = rvalue; }
                        if( rvalue < rmin ) { rmin = rvalue; }
                    }
                }
            }
        }
        // return
        return { "min": rmin, "max": rmax };
    }
};

/**
 * Calculate the histogram of the image.
 * @return {Object} The histogram, data range and rescaled data range.
 */
dwv.image.Image.prototype.calculateHistogram = function ()
{
    var size = this.getGeometry().getSize();
    var histo = [];
    var min = this.getValue(0,0,0);
    var max = min;
    var value = 0;
    var rmin = this.getRescaledValue(0,0,0);
    var rmax = rmin;
    var rvalue = 0;
    for ( var f = 0, nframes = this.getNumberOfFrames(); f < nframes; ++f ) {
        for ( var k = 0, nslices = size.getNumberOfSlices(); k < nslices; ++k ) {
            for ( var j = 0, nrows = size.getNumberOfRows(); j < nrows; ++j ) {
                for ( var i = 0, ncols = size.getNumberOfColumns(); i < ncols; ++i ) {
                    value = this.getValue(i,j,k,f);
                    if( value > max ) { max = value; }
                    if( value < min ) { min = value; }
                    rvalue = this.getRescaleSlopeAndIntercept(k).apply(value);
                    if( rvalue > rmax ) { rmax = rvalue; }
                    if( rvalue < rmin ) { rmin = rvalue; }
                    histo[rvalue] = ( histo[rvalue] || 0 ) + 1;
                }
            }
        }
    }
    // set data range
    var dataRange = { "min": min, "max": max };
    var rescaledDataRange = { "min": rmin, "max": rmax };
    // generate data for plotting
    var histogram = [];
    for ( var b = rmin; b <= rmax; ++b ) {
        histogram.push([b, ( histo[b] || 0 ) ]);
    }
    // return
    return { 'dataRange': dataRange, 'rescaledDataRange': rescaledDataRange,
        'histogram': histogram };
};

/**
 * Convolute the image with a given 2D kernel.
 * @param {Array} weights The weights of the 2D kernel as a 3x3 matrix.
 * @return {Image} The convoluted image.
 * Note: Uses the raw buffer values.
 */
dwv.image.Image.prototype.convolute2D = function(weights)
{
    if(weights.length !== 9) {
        throw new Error("The convolution matrix does not have a length of 9; it has "+weights.length);
    }

    var newImage = this.clone();
    var newBuffer = newImage.getBuffer();

    var imgSize = this.getGeometry().getSize();
    var ncols = imgSize.getNumberOfColumns();
    var nrows = imgSize.getNumberOfRows();
    var nslices = imgSize.getNumberOfSlices();
    var nframes = this.getNumberOfFrames();
    var ncomp = this.getNumberOfComponents();

    // adapt to number of component and planar configuration
    var factor = 1;
    var componentOffset = 1;
    var frameOffset = imgSize.getTotalSize();
    if( ncomp === 3 )
    {
        frameOffset *= 3;
        if( this.getPlanarConfiguration() === 0 )
        {
            factor = 3;
        }
        else
        {
            componentOffset = imgSize.getTotalSize();
        }
    }

    // allow special indent for matrices
    /*jshint indent:false */

    // default weight offset matrix
    var wOff = [];
    wOff[0] = (-ncols-1) * factor; wOff[1] = (-ncols) * factor; wOff[2] = (-ncols+1) * factor;
    wOff[3] = -factor; wOff[4] = 0; wOff[5] = 1 * factor;
    wOff[6] = (ncols-1) * factor; wOff[7] = (ncols) * factor; wOff[8] = (ncols+1) * factor;

    // border weight offset matrices
    // borders are extended (see http://en.wikipedia.org/wiki/Kernel_%28image_processing%29)

    // i=0, j=0
    var wOff00 = [];
    wOff00[0] = wOff[4]; wOff00[1] = wOff[4]; wOff00[2] = wOff[5];
    wOff00[3] = wOff[4]; wOff00[4] = wOff[4]; wOff00[5] = wOff[5];
    wOff00[6] = wOff[7]; wOff00[7] = wOff[7]; wOff00[8] = wOff[8];
    // i=0, j=*
    var wOff0x = [];
    wOff0x[0] = wOff[1]; wOff0x[1] = wOff[1]; wOff0x[2] = wOff[2];
    wOff0x[3] = wOff[4]; wOff0x[4] = wOff[4]; wOff0x[5] = wOff[5];
    wOff0x[6] = wOff[7]; wOff0x[7] = wOff[7]; wOff0x[8] = wOff[8];
    // i=0, j=nrows
    var wOff0n = [];
    wOff0n[0] = wOff[1]; wOff0n[1] = wOff[1]; wOff0n[2] = wOff[2];
    wOff0n[3] = wOff[4]; wOff0n[4] = wOff[4]; wOff0n[5] = wOff[5];
    wOff0n[6] = wOff[4]; wOff0n[7] = wOff[4]; wOff0n[8] = wOff[5];

    // i=*, j=0
    var wOffx0 = [];
    wOffx0[0] = wOff[3]; wOffx0[1] = wOff[4]; wOffx0[2] = wOff[5];
    wOffx0[3] = wOff[3]; wOffx0[4] = wOff[4]; wOffx0[5] = wOff[5];
    wOffx0[6] = wOff[6]; wOffx0[7] = wOff[7]; wOffx0[8] = wOff[8];
    // i=*, j=* -> wOff
    // i=*, j=nrows
    var wOffxn = [];
    wOffxn[0] = wOff[0]; wOffxn[1] = wOff[1]; wOffxn[2] = wOff[2];
    wOffxn[3] = wOff[3]; wOffxn[4] = wOff[4]; wOffxn[5] = wOff[5];
    wOffxn[6] = wOff[3]; wOffxn[7] = wOff[4]; wOffxn[8] = wOff[5];

    // i=ncols, j=0
    var wOffn0 = [];
    wOffn0[0] = wOff[3]; wOffn0[1] = wOff[4]; wOffn0[2] = wOff[4];
    wOffn0[3] = wOff[3]; wOffn0[4] = wOff[4]; wOffn0[5] = wOff[4];
    wOffn0[6] = wOff[6]; wOffn0[7] = wOff[7]; wOffn0[8] = wOff[7];
    // i=ncols, j=*
    var wOffnx = [];
    wOffnx[0] = wOff[0]; wOffnx[1] = wOff[1]; wOffnx[2] = wOff[1];
    wOffnx[3] = wOff[3]; wOffnx[4] = wOff[4]; wOffnx[5] = wOff[4];
    wOffnx[6] = wOff[6]; wOffnx[7] = wOff[7]; wOffnx[8] = wOff[7];
    // i=ncols, j=nrows
    var wOffnn = [];
    wOffnn[0] = wOff[0]; wOffnn[1] = wOff[1]; wOffnn[2] = wOff[1];
    wOffnn[3] = wOff[3]; wOffnn[4] = wOff[4]; wOffnn[5] = wOff[4];
    wOffnn[6] = wOff[3]; wOffnn[7] = wOff[4]; wOffnn[8] = wOff[4];

    // restore indent for rest of method
    /*jshint indent:4 */

    // loop vars
    var pixelOffset = 0;
    var newValue = 0;
    var wOffFinal = [];
    // go through the destination image pixels
    for (var f=0; f<nframes; f++) {
        pixelOffset = f * frameOffset;
        for (var c=0; c<ncomp; c++) {
            // special component offset
            pixelOffset += c * componentOffset;
            for (var k=0; k<nslices; k++) {
                for (var j=0; j<nrows; j++) {
                    for (var i=0; i<ncols; i++) {
                        wOffFinal = wOff;
                        // special border cases
                        if( i === 0 && j === 0 ) {
                            wOffFinal = wOff00;
                        }
                        else if( i === 0 && j === (nrows-1)  ) {
                            wOffFinal = wOff0n;
                        }
                        else if( i === (ncols-1) && j === 0 ) {
                            wOffFinal = wOffn0;
                        }
                        else if( i === (ncols-1) && j === (nrows-1) ) {
                            wOffFinal = wOffnn;
                        }
                        else if( i === 0 && j !== (nrows-1) && j !== 0 ) {
                            wOffFinal = wOff0x;
                        }
                        else if( i === (ncols-1) && j !== (nrows-1) && j !== 0 ) {
                            wOffFinal = wOffnx;
                        }
                        else if( i !== 0 && i !== (ncols-1) && j === 0 ) {
                            wOffFinal = wOffx0;
                        }
                        else if( i !== 0 && i !== (ncols-1) && j === (nrows-1) ) {
                            wOffFinal = wOffxn;
                        }

                        // calculate the weighed sum of the source image pixels that
                        // fall under the convolution matrix
                        newValue = 0;
                        for( var wi=0; wi<9; ++wi )
                        {
                            newValue += this.getValueAtOffset(pixelOffset + wOffFinal[wi], f) * weights[wi];
                        }
                        newBuffer[f][pixelOffset] = newValue;
                        // increment pixel offset
                        pixelOffset += factor;
                    }
                }
            }
        }
    }
    return newImage;
};

/**
 * Transform an image using a specific operator.
 * WARNING: no size check!
 * @param {Function} operator The operator to use when transforming.
 * @return {Image} The transformed image.
 * Note: Uses the raw buffer values.
 */
dwv.image.Image.prototype.transform = function(operator)
{
    var newImage = this.clone();
    var newBuffer = newImage.getBuffer();
    for ( var f = 0, lenf = this.getNumberOfFrames(); f < lenf; ++f )
    {
        for( var i = 0, leni = newBuffer[f].length; i < leni; ++i )
        {
            newBuffer[f][i] = operator( newImage.getValueAtOffset(i,f) );
        }
    }
    return newImage;
};

/**
 * Compose this image with another one and using a specific operator.
 * WARNING: no size check!
 * @param {Image} rhs The image to compose with.
 * @param {Function} operator The operator to use when composing.
 * @return {Image} The composed image.
 * Note: Uses the raw buffer values.
 */
dwv.image.Image.prototype.compose = function(rhs, operator)
{
    var newImage = this.clone();
    var newBuffer = newImage.getBuffer();
    for ( var f = 0, lenf = this.getNumberOfFrames(); f < lenf; ++f )
    {
        for( var i = 0, leni = newBuffer[f].length; i < leni; ++i )
        {
            // using the operator on the local buffer, i.e. the latest (not original) data
            newBuffer[f][i] = Math.floor( operator( this.getValueAtOffset(i,f), rhs.getValueAtOffset(i,f) ) );
        }
    }
    return newImage;
};

/**
 * Quantify a line according to image information.
 * @param {Object} line The line to quantify.
 * @return {Object} A quantification object.
 */
dwv.image.Image.prototype.quantifyLine = function(line)
{
    var quant = {};
    // length
    var spacing = this.getGeometry().getSpacing();
    var length = line.getWorldLength( spacing.getColumnSpacing(),
            spacing.getRowSpacing() );
    if (length !== null) {
        quant.length = {"value": length, "unit": dwv.i18n("unit.mm")};
    }
    // return
    return quant;
};

/**
 * Quantify a rectangle according to image information.
 * @param {Object} rect The rectangle to quantify.
 * @return {Object} A quantification object.
 */
dwv.image.Image.prototype.quantifyRect = function(rect)
{
    var quant = {};
    // surface
    var spacing = this.getGeometry().getSpacing();
    var surface = rect.getWorldSurface( spacing.getColumnSpacing(),
            spacing.getRowSpacing());
    if (surface !== null) {
        quant.surface = {"value": surface/100, "unit": dwv.i18n("unit.cm2")};
    }
    // stats
    var subBuffer = [];
    var minJ = parseInt(rect.getBegin().getY(), 10);
    var maxJ = parseInt(rect.getEnd().getY(), 10);
    var minI = parseInt(rect.getBegin().getX(), 10);
    var maxI = parseInt(rect.getEnd().getX(), 10);
    for ( var j = minJ; j < maxJ; ++j ) {
        for ( var i = minI; i < maxI; ++i ) {
            subBuffer.push( this.getValue(i,j,0) );
        }
    }
    var quantif = dwv.math.getStats( subBuffer );
    quant.min = {"value": quantif.min, "unit": ""};
    quant.max = {"value": quantif.max, "unit": ""};
    quant.mean = {"value": quantif.mean, "unit": ""};
    quant.stdDev = {"value": quantif.stdDev, "unit": ""};
    // return
    return quant;
};

/**
 * Quantify an ellipse according to image information.
 * @param {Object} ellipse The ellipse to quantify.
 * @return {Object} A quantification object.
 */
dwv.image.Image.prototype.quantifyEllipse = function(ellipse)
{
    var quant = {};
    // surface
    var spacing = this.getGeometry().getSpacing();
    var surface = ellipse.getWorldSurface( spacing.getColumnSpacing(),
            spacing.getRowSpacing());
    if (surface !== null) {
        quant.surface = {"value": surface/100, "unit": dwv.i18n("unit.cm2")};
    }
    // return
    return quant;
};

/**
 * {@link dwv.image.Image} factory.
 * @constructor
 */
dwv.image.ImageFactory = function () {};

/**
 * Get an {@link dwv.image.Image} object from the read DICOM file.
 * @param {Object} dicomElements The DICOM tags.
 * @param {Array} pixelBuffer The pixel buffer.
 * @return {View} A new Image.
 */
dwv.image.ImageFactory.prototype.create = function (dicomElements, pixelBuffer)
{
    // columns
    var columns = dicomElements.getFromKey("x00280011");
    if ( !columns ) {
        throw new Error("Missing or empty DICOM image number of columns");
    }
    // rows
    var rows = dicomElements.getFromKey("x00280010");
    if ( !rows ) {
        throw new Error("Missing or empty DICOM image number of rows");
    }
    // image size
    var size = new dwv.image.Size( columns, rows );

    // spacing
    var rowSpacing = null;
    var columnSpacing = null;
    // PixelSpacing
    var pixelSpacing = dicomElements.getFromKey("x00280030");
    // ImagerPixelSpacing
    var imagerPixelSpacing = dicomElements.getFromKey("x00181164");
    if ( pixelSpacing && pixelSpacing[0] && pixelSpacing[1] ) {
        rowSpacing = parseFloat( pixelSpacing[0] );
        columnSpacing = parseFloat( pixelSpacing[1] );
    }
    else if ( imagerPixelSpacing && imagerPixelSpacing[0] && imagerPixelSpacing[1] ) {
        rowSpacing = parseFloat( imagerPixelSpacing[0] );
        columnSpacing = parseFloat( imagerPixelSpacing[1] );
    }
    // image spacing
    var spacing = new dwv.image.Spacing( columnSpacing, rowSpacing );

    // TransferSyntaxUID
    var transferSyntaxUID = dicomElements.getFromKey("x00020010");
    var syntax = dwv.dicom.cleanString( transferSyntaxUID );
    var jpeg2000 = dwv.dicom.isJpeg2000TransferSyntax( syntax );
    var jpegBase = dwv.dicom.isJpegBaselineTransferSyntax( syntax );
    var jpegLoss = dwv.dicom.isJpegLosslessTransferSyntax( syntax );

    // slice position
    var slicePosition = new Array(0,0,0);
    // ImagePositionPatient
    var imagePositionPatient = dicomElements.getFromKey("x00200032");
    if ( imagePositionPatient ) {
        slicePosition = [ parseFloat( imagePositionPatient[0] ),
            parseFloat( imagePositionPatient[1] ),
            parseFloat( imagePositionPatient[2] ) ];
    }

    // slice orientation
    var imageOrientationPatient = dicomElements.getFromKey("x00200037");
    var orientationMatrix;
    if ( imageOrientationPatient ) {
        var rowCosines = new dwv.math.Vector3D( parseFloat( imageOrientationPatient[0] ),
            parseFloat( imageOrientationPatient[1] ),
            parseFloat( imageOrientationPatient[2] ) );
        var colCosines = new dwv.math.Vector3D( parseFloat( imageOrientationPatient[3] ),
            parseFloat( imageOrientationPatient[4] ),
            parseFloat( imageOrientationPatient[5] ) );
        var normal = rowCosines.crossProduct(colCosines);
        orientationMatrix = new dwv.math.Matrix33(
            rowCosines.getX(), rowCosines.getY(), rowCosines.getZ(),
            colCosines.getX(), colCosines.getY(), colCosines.getZ(),
            normal.getX(), normal.getY(), normal.getZ() );
    }

    // geometry
    var origin = new dwv.math.Point3D(slicePosition[0], slicePosition[1], slicePosition[2]);
    var geometry = new dwv.image.Geometry( origin, size, spacing, orientationMatrix );

    // image
    var image = new dwv.image.Image( geometry, pixelBuffer );
    // PhotometricInterpretation
    var photometricInterpretation = dicomElements.getFromKey("x00280004");
    if ( photometricInterpretation ) {
        var photo = dwv.dicom.cleanString(photometricInterpretation).toUpperCase();
        // jpeg decoders output RGB data
        if ( (jpeg2000 || jpegBase || jpegLoss) &&
        	(photo !== "MONOCHROME1" && photo !== "MONOCHROME2") ) {
            photo = "RGB";
        }
        image.setPhotometricInterpretation( photo );
    }
    // PlanarConfiguration
    var planarConfiguration = dicomElements.getFromKey("x00280006");
    if ( planarConfiguration ) {
        image.setPlanarConfiguration( planarConfiguration );
    }

    // rescale slope and intercept
    var slope = 1;
    // RescaleSlope
    var rescaleSlope = dicomElements.getFromKey("x00281053");
    if ( rescaleSlope ) {
        slope = parseFloat(rescaleSlope);
    }
    var intercept = 0;
    // RescaleIntercept
    var rescaleIntercept = dicomElements.getFromKey("x00281052");
    if ( rescaleIntercept ) {
        intercept = parseFloat(rescaleIntercept);
    }
    var rsi = new dwv.image.RescaleSlopeAndIntercept(slope, intercept);
    image.setRescaleSlopeAndIntercept( rsi );

    // meta information
    var meta = {};
    // Modality
    var modality = dicomElements.getFromKey("x00080060");
    if ( modality ) {
        meta.Modality = modality;
    }
    // StudyInstanceUID
    var studyInstanceUID = dicomElements.getFromKey("x0020000D");
    if ( studyInstanceUID ) {
        meta.StudyInstanceUID = studyInstanceUID;
    }
    // SeriesInstanceUID
    var seriesInstanceUID = dicomElements.getFromKey("x0020000E");
    if ( seriesInstanceUID ) {
        meta.SeriesInstanceUID = seriesInstanceUID;
    }
    // BitsStored
    var bitsStored = dicomElements.getFromKey("x00280101");
    if ( bitsStored ) {
        meta.BitsStored = parseInt(bitsStored, 10);
    }
    // PixelRepresentation -> is signed
    var pixelRepresentation = dicomElements.getFromKey("x00280103");
    meta.IsSigned = false;
    if ( pixelRepresentation ) {
        meta.IsSigned = (pixelRepresentation === 1);
    }
    image.setMeta(meta);

    // overlay
    image.setFirstOverlay( dwv.gui.info.createOverlays(dicomElements) );

    return image;
};

// namespaces
var dwv = dwv || {};
dwv.image = dwv.image || {};
/** @namespace */
dwv.image.lut = dwv.image.lut || {};

/**
 * Rescale LUT class.
 * @constructor
 * @param {Object} rsi The rescale slope and intercept.
 * @param {Number} bitsStored The number of bits used to store the data.
 */
dwv.image.lut.Rescale = function (rsi, bitsStored)
{
    /**
     * The internal array.
     * @private
     * @type Array
     */
    var lut = null;

    /**
     * Flag to know if the lut is ready or not.
     * @private
     * @type Boolean
     */
    var isReady = false;

    /**
     * The size of the LUT array.
     * @private
     * @type Number
     */
    var length = Math.pow(2, bitsStored);

    /**
     * Get the Rescale Slope and Intercept (RSI).
     * @return {Object} The rescale slope and intercept.
     */
    this.getRSI = function () { return rsi; };

    /**
     * Is the lut ready to use or not? If not, the user must
     * call 'initialise'.
     * @return {Boolean} True if the lut is ready to use.
     */
    this.isReady = function () { return isReady; };

    /**
     * Initialise the LUT.
     */
    this.initialise = function ()
    {
        // check if already initialised
        if (isReady) {
            return;
        }
        // create lut and fill it
        lut = new Float32Array(length);
        for ( var i = 0; i < length; ++i ) {
            lut[i] = rsi.apply(i);
        }
        // update ready flag
        isReady = true;
    };

    /**
     * Get the length of the LUT array.
     * @return {Number} The length of the LUT array.
     */
    this.getLength = function () { return length; };

    /**
     * Get the value of the LUT at the given offset.
     * @return {Number} The value of the LUT at the given offset.
     */
    this.getValue = function (offset)
    {
        return lut[ offset ];
    };
};

/**
 * Window LUT class.
 * @constructor
 * @param {Number} rescaleLut The associated rescale LUT.
 * @param {Boolean} isSigned Flag to know if the data is signed or not.
 */
dwv.image.lut.Window = function (rescaleLut, isSigned)
{
    /**
     * The internal array: Uint8ClampedArray clamps between 0 and 255.
     * @private
     * @type Array
     */
    var lut = null;

    /**
     * The window level.
     * @private
     * @type {Object}
     */
    var windowLevel = null;

    /**
     * Flag to know if the lut is ready or not.
     * @private
     * @type Boolean
     */
    var isReady = false;

    /**
     * Shift for signed data.
     * @private
     * @type Number
     */
    var signedShift = 0;

    /**
     * Get the window / level.
     * @return {Object} The window / level.
     */
    this.getWindowLevel = function () { return windowLevel; };
    /**
     * Get the signed flag.
     * @return {Boolean} The signed flag.
     */
    this.isSigned = function () { return isSigned; };
    /**
     * Get the rescale lut.
     * @return {Object} The rescale lut.
     */
    this.getRescaleLut = function () { return rescaleLut; };

    /**
     * Is the lut ready to use or not? If not, the user must
     * call 'update'.
     * @return {Boolean} True if the lut is ready to use.
     */
    this.isReady = function () { return isReady; };

    /**
     * Set the window center and width.
     * @param {Object} wl The window level.
     */
    this.setWindowLevel = function (wl)
    {
        // store the window values
        windowLevel = wl;
        // possible signed shift
        signedShift = 0;
        windowLevel.setSignedOffset(0);
        if ( isSigned ) {
            var size = rescaleLut.getLength();
            signedShift = size / 2;
            windowLevel.setSignedOffset(rescaleLut.getRSI().getSlope() * signedShift);
        }
        // update ready flag
        isReady = false;
    };

    /**
     * Update the lut if needed..
     */
    this.update = function ()
    {
        // check if we need to update
        if ( isReady ) {
            return;
        }

        // check rescale lut
        if (!rescaleLut.isReady()) {
            rescaleLut.initialise();
        }
        // create window lut
        var size = rescaleLut.getLength();
        if (!lut) {
            // use clamped array (polyfilled in browser.js)
            lut = new Uint8ClampedArray(size);
        }
        // by default WindowLevel returns a value in the [0,255] range
        // this is ok with regular Arrays and ClampedArray.
        for ( var i = 0; i < size; ++i )
        {
            lut[i] = windowLevel.apply( rescaleLut.getValue(i) );
        }

        // update ready flag
        isReady = true;
    };

    /**
     * Get the length of the LUT array.
     * @return {Number} The length of the LUT array.
     */
    this.getLength = function () { return lut.length; };

    /**
     * Get the value of the LUT at the given offset.
     * @return {Number} The value of the LUT at the given offset.
     */
    this.getValue = function (offset)
    {
        return lut[ offset + signedShift ];
    };
};

/**
* Lookup tables for image colour display.
*/

dwv.image.lut.range_max = 256;

dwv.image.lut.buildLut = function(func)
{
    var lut = [];
    for( var i=0; i<dwv.image.lut.range_max; ++i ) {
        lut.push(func(i));
    }
    return lut;
};

dwv.image.lut.max = function(/*i*/)
{
    return dwv.image.lut.range_max-1;
};

dwv.image.lut.maxFirstThird = function(i)
{
    if( i < dwv.image.lut.range_max/3 ) {
        return dwv.image.lut.range_max-1;
    }
    return 0;
};

dwv.image.lut.maxSecondThird = function(i)
{
    var third = dwv.image.lut.range_max/3;
    if( i >= third && i < 2*third ) {
        return dwv.image.lut.range_max-1;
    }
    return 0;
};

dwv.image.lut.maxThirdThird = function(i)
{
    if( i >= 2*dwv.image.lut.range_max/3 ) {
        return dwv.image.lut.range_max-1;
    }
    return 0;
};

dwv.image.lut.toMaxFirstThird = function(i)
{
    var val = i * 3;
    if( val > dwv.image.lut.range_max-1 ) {
        return dwv.image.lut.range_max-1;
    }
    return val;
};

dwv.image.lut.toMaxSecondThird = function(i)
{
    var third = dwv.image.lut.range_max/3;
    var val = 0;
    if( i >= third ) {
        val = (i-third) * 3;
        if( val > dwv.image.lut.range_max-1 ) {
            return dwv.image.lut.range_max-1;
        }
    }
    return val;
};

dwv.image.lut.toMaxThirdThird = function(i)
{
    var third = dwv.image.lut.range_max/3;
    var val = 0;
    if( i >= 2*third ) {
        val = (i-2*third) * 3;
        if( val > dwv.image.lut.range_max-1 ) {
            return dwv.image.lut.range_max-1;
        }
    }
    return val;
};

dwv.image.lut.zero = function(/*i*/)
{
    return 0;
};

dwv.image.lut.id = function(i)
{
    return i;
};

dwv.image.lut.invId = function(i)
{
    return (dwv.image.lut.range_max-1)-i;
};

// plain
dwv.image.lut.plain = {
    "red":   dwv.image.lut.buildLut(dwv.image.lut.id),
    "green": dwv.image.lut.buildLut(dwv.image.lut.id),
    "blue":  dwv.image.lut.buildLut(dwv.image.lut.id)
};

// inverse plain
dwv.image.lut.invPlain = {
    "red":   dwv.image.lut.buildLut(dwv.image.lut.invId),
    "green": dwv.image.lut.buildLut(dwv.image.lut.invId),
    "blue":  dwv.image.lut.buildLut(dwv.image.lut.invId)
};

//rainbow
dwv.image.lut.rainbow = {
    "blue":  [0, 4, 8, 12, 16, 20, 24, 28, 32, 36, 40, 44, 48, 52, 56, 60, 64, 68, 72, 76, 80, 84, 88, 92, 96, 100, 104, 108, 112, 116, 120, 124, 128, 132, 136, 140, 144, 148, 152, 156, 160, 164, 168, 172, 176, 180, 184, 188, 192, 196, 200, 204, 208, 212, 216, 220, 224, 228, 232, 236, 240, 244, 248, 252, 255, 247, 239, 231, 223, 215, 207, 199, 191, 183, 175, 167, 159, 151, 143, 135, 127, 119, 111, 103, 95, 87, 79, 71, 63, 55, 47, 39, 31, 23, 15, 7, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
    "green": [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 8, 16, 24, 32, 40, 48, 56, 64, 72, 80, 88, 96, 104, 112, 120, 128, 136, 144, 152, 160, 168, 176, 184, 192, 200, 208, 216, 224, 232, 240, 248, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 253, 251, 249, 247, 245, 243, 241, 239, 237, 235, 233, 231, 229, 227, 225, 223, 221, 219, 217, 215, 213, 211, 209, 207, 205, 203, 201, 199, 197, 195, 193, 192, 189, 186, 183, 180, 177, 174, 171, 168, 165, 162, 159, 156, 153, 150, 147, 144, 141, 138, 135, 132, 129, 126, 123, 120, 117, 114, 111, 108, 105, 102, 99, 96, 93, 90, 87, 84, 81, 78, 75, 72, 69, 66, 63, 60, 57, 54, 51, 48, 45, 42, 39, 36, 33, 30, 27, 24, 21, 18, 15, 12, 9, 6, 3],
    "red":   [0, 2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 34, 36, 38, 40, 42, 44, 46, 48, 50, 52, 54, 56, 58, 60, 62, 64, 62, 60, 58, 56, 54, 52, 50, 48, 46, 44, 42, 40, 38, 36, 34, 32, 30, 28, 26, 24, 22, 20, 18, 16, 14, 12, 10, 8, 6, 4, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 4, 8, 12, 16, 20, 24, 28, 32, 36, 40, 44, 48, 52, 56, 60, 64, 68, 72, 76, 80, 84, 88, 92, 96, 100, 104, 108, 112, 116, 120, 124, 128, 132, 136, 140, 144, 148, 152, 156, 160, 164, 168, 172, 176, 180, 184, 188, 192, 196, 200, 204, 208, 212, 216, 220, 224, 228, 232, 236, 240, 244, 248, 252, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255]
};

// hot
dwv.image.lut.hot = {
    "red":   dwv.image.lut.buildLut(dwv.image.lut.toMaxFirstThird),
    "green": dwv.image.lut.buildLut(dwv.image.lut.toMaxSecondThird),
    "blue":  dwv.image.lut.buildLut(dwv.image.lut.toMaxThirdThird)
};

// hot iron
dwv.image.lut.hot_iron = {
    "red":   [0, 2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 34, 36, 38, 40, 42, 44, 46, 48, 50, 52, 54, 56, 58, 60, 62, 64, 66, 68, 70, 72, 74, 76, 78, 80, 82, 84, 86, 88, 90, 92, 94, 96, 98, 100, 102, 104, 106, 108, 110, 112, 114, 116, 118, 120, 122, 124, 126, 128, 130, 132, 134, 136, 138, 140, 142, 144, 146, 148, 150, 152, 154, 156, 158, 160, 162, 164, 166, 168, 170, 172, 174, 176, 178, 180, 182, 184, 186, 188, 190, 192, 194, 196, 198, 200, 202, 204, 206, 208, 210, 212, 214, 216, 218, 220, 222, 224, 226, 228, 230, 232, 234, 236, 238, 240, 242, 244, 246, 248, 250, 252, 254, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255],
    "green": [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 34, 36, 38, 40, 42, 44, 46, 48, 50, 52, 54, 56, 58, 60, 62, 64, 66, 68, 70, 72, 74, 76, 78, 80, 82, 84, 86, 88, 90, 92, 94, 96, 98, 100, 102, 104, 106, 108, 110, 112, 114, 116, 118, 120, 122, 124, 126, 128, 130, 132, 134, 136, 138, 140, 142, 144, 146, 148, 150, 152, 154, 156, 158, 160, 162, 164, 166, 168, 170, 172, 174, 176, 178, 180, 182, 184, 186, 188, 190, 192, 194, 196, 198, 200, 202, 204, 206, 208, 210, 212, 214, 216, 218, 220, 222, 224, 226, 228, 230, 232, 234, 236, 238, 240, 242, 244, 246, 248, 250, 252, 255],
    "blue":  [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 4, 8, 12, 16, 20, 24, 28, 32, 36, 40, 44, 48, 52, 56, 60, 64, 68, 72, 76, 80, 84, 88, 92, 96, 100, 104, 108, 112, 116, 120, 124, 128, 132, 136, 140, 144, 148, 152, 156, 160, 164, 168, 172, 176, 180, 184, 188, 192, 196, 200, 204, 208, 212, 216, 220, 224, 228, 232, 236, 240, 244, 248, 252, 255]
};

// pet
dwv.image.lut.pet = {
    "red":   [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25, 27, 29, 31, 33, 35, 37, 39, 41, 43, 45, 47, 49, 51, 53, 55, 57, 59, 61, 63, 65, 67, 69, 71, 73, 75, 77, 79, 81, 83, 85, 86, 88, 90, 92, 94, 96, 98, 100, 102, 104, 106, 108, 110, 112, 114, 116, 118, 120, 122, 124, 126, 128, 130, 132, 134, 136, 138, 140, 142, 144, 146, 148, 150, 152, 154, 156, 158, 160, 162, 164, 166, 168, 170, 171, 173, 175, 177, 179, 181, 183, 185, 187, 189, 191, 193, 195, 197, 199, 201, 203, 205, 207, 209, 211, 213, 215, 217, 219, 221, 223, 225, 227, 229, 231, 233, 235, 237, 239, 241, 243, 245, 247, 249, 251, 253, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255],
    "green": [0, 2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 34, 36, 38, 40, 42, 44, 46, 48, 50, 52, 54, 56, 58, 60, 62, 65, 67, 69, 71, 73, 75, 77, 79, 81, 83, 85, 87, 89, 91, 93, 95, 97, 99, 101, 103, 105, 107, 109, 111, 113, 115, 117, 119, 121, 123, 125, 128, 126, 124, 122, 120, 118, 116, 114, 112, 110, 108, 106, 104, 102, 100, 98, 96, 94, 92, 90, 88, 86, 84, 82, 80, 78, 76, 74, 72, 70, 68, 66, 64, 63, 61, 59, 57, 55, 53, 51, 49, 47, 45, 43, 41, 39, 37, 35, 33, 31, 29, 27, 25, 23, 21, 19, 17, 15, 13, 11, 9, 7, 5, 3, 1, 0, 2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 34, 36, 38, 40, 42, 44, 46, 48, 50, 52, 54, 56, 58, 60, 62, 64, 66, 68, 70, 72, 74, 76, 78, 80, 82, 84, 86, 88, 90, 92, 94, 96, 98, 100, 102, 104, 106, 108, 110, 112, 114, 116, 118, 120, 122, 124, 126, 128, 130, 132, 134, 136, 138, 140, 142, 144, 146, 148, 150, 152, 154, 156, 158, 160, 162, 164, 166, 168, 170, 172, 174, 176, 178, 180, 182, 184, 186, 188, 190, 192, 194, 196, 198, 200, 202, 204, 206, 208, 210, 212, 214, 216, 218, 220, 222, 224, 226, 228, 230, 232, 234, 236, 238, 240, 242, 244, 246, 248, 250, 252, 255],
    "blue":  [0, 1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25, 27, 29, 31, 33, 35, 37, 39, 41, 43, 45, 47, 49, 51, 53, 55, 57, 59, 61, 63, 65, 67, 69, 71, 73, 75, 77, 79, 81, 83, 85, 87, 89, 91, 93, 95, 97, 99, 101, 103, 105, 107, 109, 111, 113, 115, 117, 119, 121, 123, 125, 127, 129, 131, 133, 135, 137, 139, 141, 143, 145, 147, 149, 151, 153, 155, 157, 159, 161, 163, 165, 167, 169, 171, 173, 175, 177, 179, 181, 183, 185, 187, 189, 191, 193, 195, 197, 199, 201, 203, 205, 207, 209, 211, 213, 215, 217, 219, 221, 223, 225, 227, 229, 231, 233, 235, 237, 239, 241, 243, 245, 247, 249, 251, 253, 255, 252, 248, 244, 240, 236, 232, 228, 224, 220, 216, 212, 208, 204, 200, 196, 192, 188, 184, 180, 176, 172, 168, 164, 160, 156, 152, 148, 144, 140, 136, 132, 128, 124, 120, 116, 112, 108, 104, 100, 96, 92, 88, 84, 80, 76, 72, 68, 64, 60, 56, 52, 48, 44, 40, 36, 32, 28, 24, 20, 16, 12, 8, 4, 0, 4, 8, 12, 16, 20, 24, 28, 32, 36, 40, 44, 48, 52, 56, 60, 64, 68, 72, 76, 80, 85, 89, 93, 97, 101, 105, 109, 113, 117, 121, 125, 129, 133, 137, 141, 145, 149, 153, 157, 161, 165, 170, 174, 178, 182, 186, 190, 194, 198, 202, 206, 210, 214, 218, 222, 226, 230, 234, 238, 242, 246, 250, 255]
};

// hot metal blue
dwv.image.lut.hot_metal_blue = {
    "red":   [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3, 6, 9, 12, 15, 18, 21, 24, 26, 29, 32, 35, 38, 41, 44, 47, 50, 52, 55, 57, 59, 62, 64, 66, 69, 71, 74, 76, 78, 81, 83, 85, 88, 90, 93, 96, 99, 102, 105, 108, 111, 114, 116, 119, 122, 125, 128, 131, 134, 137, 140, 143, 146, 149, 152, 155, 158, 161, 164, 166, 169, 172, 175, 178, 181, 184, 187, 190, 194, 198, 201, 205, 209, 213, 217, 221, 224, 228, 232, 236, 240, 244, 247, 251, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255],
    "green": [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 4, 6, 8, 9, 11, 13, 15, 17, 19, 21, 23, 24, 26, 28, 30, 32, 34, 36, 38, 40, 41, 43, 45, 47, 49, 51, 53, 55, 56, 58, 60, 62, 64, 66, 68, 70, 72, 73, 75, 77, 79, 81, 83, 85, 87, 88, 90, 92, 94, 96, 98, 100, 102, 104, 105, 107, 109, 111, 113, 115, 117, 119, 120, 122, 124, 126, 128, 130, 132, 134, 136, 137, 139, 141, 143, 145, 147, 149, 151, 152, 154, 156, 158, 160, 162, 164, 166, 168, 169, 171, 173, 175, 177, 179, 181, 183, 184, 186, 188, 190, 192, 194, 196, 198, 200, 201, 203, 205, 207, 209, 211, 213, 215, 216, 218, 220, 222, 224, 226, 228, 229, 231, 233, 235, 237, 239, 240, 242, 244, 246, 248, 250, 251, 253, 255],
    "blue":  [0, 2, 4, 6, 8, 10, 12, 14, 16, 17, 19, 21, 23, 25, 27, 29, 31, 33, 35, 37, 39, 41, 43, 45, 47, 49, 51, 53, 55, 57, 59, 61, 63, 65, 67, 69, 71, 73, 75, 77, 79, 81, 83, 84, 86, 88, 90, 92, 94, 96, 98, 100, 102, 104, 106, 108, 110, 112, 114, 116, 117, 119, 121, 123, 125, 127, 129, 131, 133, 135, 137, 139, 141, 143, 145, 147, 149, 151, 153, 155, 157, 159, 161, 163, 165, 167, 169, 171, 173, 175, 177, 179, 181, 183, 184, 186, 188, 190, 192, 194, 196, 198, 200, 197, 194, 191, 188, 185, 182, 179, 176, 174, 171, 168, 165, 162, 159, 156, 153, 150, 144, 138, 132, 126, 121, 115, 109, 103, 97, 91, 85, 79, 74, 68, 62, 56, 50, 47, 44, 41, 38, 35, 32, 29, 26, 24, 21, 18, 15, 12, 9, 6, 3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3, 6, 9, 12, 15, 18, 21, 24, 26, 29, 32, 35, 38, 41, 44, 47, 50, 53, 56, 59, 62, 65, 68, 71, 74, 76, 79, 82, 85, 88, 91, 94, 97, 100, 103, 106, 109, 112, 115, 118, 121, 124, 126, 129, 132, 135, 138, 141, 144, 147, 150, 153, 156, 159, 162, 165, 168, 171, 174, 176, 179, 182, 185, 188, 191, 194, 197, 200, 203, 206, 210, 213, 216, 219, 223, 226, 229, 232, 236, 239, 242, 245, 249, 252, 255]
};

// pet 20 step
dwv.image.lut.pet_20step = {
    "red":   [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 112, 112, 112, 112, 112, 112, 112, 112, 112, 112, 112, 112, 112, 128, 128, 128, 128, 128, 128, 128, 128, 128, 128, 128, 128, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 192, 192, 192, 192, 192, 192, 192, 192, 192, 192, 192, 192, 192, 176, 176, 176, 176, 176, 176, 176, 176, 176, 176, 176, 176, 176, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255],
    "green": [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 112, 112, 112, 112, 112, 112, 112, 112, 112, 112, 112, 112, 112, 128, 128, 128, 128, 128, 128, 128, 128, 128, 128, 128, 128, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 144, 144, 144, 144, 144, 144, 144, 144, 144, 144, 144, 144, 144, 192, 192, 192, 192, 192, 192, 192, 192, 192, 192, 192, 192, 192, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 208, 176, 176, 176, 176, 176, 176, 176, 176, 176, 176, 176, 176, 176, 144, 144, 144, 144, 144, 144, 144, 144, 144, 144, 144, 144, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255],
    "blue":  [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 112, 112, 112, 112, 112, 112, 112, 112, 112, 112, 112, 112, 128, 128, 128, 128, 128, 128, 128, 128, 128, 128, 128, 128, 128, 176, 176, 176, 176, 176, 176, 176, 176, 176, 176, 176, 176, 176, 192, 192, 192, 192, 192, 192, 192, 192, 192, 192, 192, 192, 192, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 224, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 48, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 96, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255, 255]
};

// test
dwv.image.lut.test = {
    "red":   dwv.image.lut.buildLut(dwv.image.lut.id),
    "green": dwv.image.lut.buildLut(dwv.image.lut.zero),
    "blue":  dwv.image.lut.buildLut(dwv.image.lut.zero)
};

//red
/*dwv.image.lut.red = {
   "red":   dwv.image.lut.buildLut(dwv.image.lut.max),
   "green": dwv.image.lut.buildLut(dwv.image.lut.id),
   "blue":  dwv.image.lut.buildLut(dwv.image.lut.id)
};*/

// namespaces
var dwv = dwv || {};
dwv.image = dwv.image || {};

/**
 * WindowLevel class.
 * References:
 * - DICOM [Window Center and Window Width]{@link http://dicom.nema.org/dicom/2013/output/chtml/part03/sect_C.11.html#sect_C.11.2.1.2}
 * Pseudo-code:
 *  if (x <= c - 0.5 - (w-1)/2), then y = ymin
 *  else if (x > c - 0.5 + (w-1)/2), then y = ymax,
 *  else y = ((x - (c - 0.5)) / (w-1) + 0.5) * (ymax - ymin) + ymin
 */
dwv.image.WindowLevel = function (center, width)
{
    // avoid zero width
    if ( width === 0 ) {
        throw new Error("A window level with a width of zero is not possible.");
    }

    /**
     * Signed data offset.
     * @private
     * @type Number
     */
    var signedOffset = 0;
    /**
     * Output value minimum.
     * @private
     * @type Number
     */
    var ymin = 0;
    /**
     * Output value maximum.
     * @private
     * @type Number
     */
    var ymax = 255;

    /**
     * Input value minimum (calculated).
     * @private
     * @type Number
     */
    var xmin = null;
    /**
     * Input value maximum (calculated).
     * @private
     * @type Number
     */
    var xmax = null;
    /**
     * Window level equation slope (calculated).
     * @private
     * @type Number
     */
    var slope = null;
    /**
     * Window level equation intercept (calculated).
     * @private
     * @type Number
     */
    var inter = null;

    /**
     * Initialise members.
     */
    function init() {
        var c = center + signedOffset;
        // from the standard
        xmin = c - 0.5 - ( (width-1) / 2 );
        xmax = c - 0.5 + ( (width-1) / 2 );
        // develop the equation:
        // y = ( ( x - (c - 0.5) ) / (w-1) + 0.5 ) * (ymax - ymin) + ymin
        // y = ( x / (w-1) ) * (ymax - ymin) + ( -(c - 0.5) / (w-1) + 0.5 ) * (ymax - ymin) + ymin
        slope = (ymax - ymin) / (width-1);
        inter = ( -(c - 0.5) / (width-1) + 0.5 ) * (ymax - ymin) + ymin;
    }

    // call init
    init();

    /**
     * Get the window center.
     * @return {Number} The window center.
     */
    this.getCenter = function () { return center; };
    /**
     * Get the window width.
     * @return {Number} The window width.
     */
    this.getWidth = function () { return width; };

    /**
     * Set the output value range.
     * @param {Number} min The output value minimum.
     * @param {Number} max The output value maximum.
     */
    this.setRange = function (min, max) {
        ymin = parseInt( min, 10 );
        ymax = parseInt( max, 10 ) ;
        // re-initialise
        init();
    };
    /**
     * Set the signed offset.
     * @param {Number} The signed data offset, typically: slope * ( size / 2).
     */
    this.setSignedOffset = function (offset) {
        signedOffset = offset;
        // re-initialise
        init();
    };

    /**
     * Apply the window level on an input value.
     * @param {Number} The value to rescale as an integer.
     * @return {Number} The leveled value, in the
     *  [ymin, ymax] range (default [0,255]).
     */
    this.apply = function (value)
    {
        if ( value <= xmin ) {
            return ymin;
        } else if ( value > xmax ) {
            return ymax;
        } else {
            return parseInt( ((value * slope) + inter), 10);
        }
    };

};

/**
 * Check for window level equality.
 * @param {Object} rhs The other window level to compare to.
 * @return {Boolean} True if both window level are equal.
 */
dwv.image.WindowLevel.prototype.equals = function (rhs) {
    return rhs !== null &&
        this.getCenter() === rhs.getCenter() &&
        this.getWidth() === rhs.getWidth();
};

/**
 * Get a string representation of the window level.
 * @return {String} The window level as a string.
 */
dwv.image.WindowLevel.prototype.toString = function () {
    return (this.getCenter() + ", " + this.getWidth());
};

/**
 * View class.
 * @constructor
 * @param {Image} image The associated image.
 * Need to set the window lookup table once created
 * (either directly or with helper methods).
 */
dwv.image.View = function (image)
{
    /**
     * Window lookup tables, indexed per Rescale Slope and Intercept (RSI).
     * @private
     * @type Window
     */
    var windowLuts = {};

    /**
     * Window presets.
     * Minmax will be filled at first use (see view.setWindowLevelPreset).
     * @private
     * @type Object
     */
    var windowPresets = { "minmax": {"name": "minmax"} };

    /**
     * Current window preset name.
     * @private
     * @type String
     */
    var currentPresetName = null;

    /**
     * colour map.
     * @private
     * @type Object
     */
    var colourMap = dwv.image.lut.plain;
    /**
     * Current position.
     * @private
     * @type Object
     */
    var currentPosition = {"i":0,"j":0,"k":0};
    /**
     * Current frame. Zero based.
     * @private
     * @type Number
     */
    var currentFrame = null;

    /**
     * Get the associated image.
     * @return {Image} The associated image.
     */
    this.getImage = function() { return image; };
    /**
     * Set the associated image.
     * @param {Image} inImage The associated image.
     */
    this.setImage = function(inImage) { image = inImage; };

    /**
     * Get the window LUT of the image.
     * Warning: can be undefined in no window/level was set.
     * @param {Object} rsi Optional image rsi, will take the one of the current slice otherwise.
     * @return {Window} The window LUT of the image.
     */
    this.getCurrentWindowLut = function (rsi) {
        var sliceNumber = this.getCurrentPosition().k;
        // use current rsi if not provided
        if ( typeof rsi === "undefined" ) {
            rsi = image.getRescaleSlopeAndIntercept(sliceNumber);
        }
        // get the lut
        var wlut = windowLuts[ rsi.toString() ];

        // special case for 'perslice' presets
        if (currentPresetName &&
            typeof windowPresets[currentPresetName] !== "undefined" &&
            typeof windowPresets[currentPresetName].perslice !== "undefined" &&
            windowPresets[currentPresetName].perslice === true ) {
            // get the preset for this slice
            var wl = windowPresets[currentPresetName].wl[sliceNumber];
            // apply it if different from previous
            if (!wlut.getWindowLevel().equals(wl)) {
                // previous values
                var previousWidth = wlut.getWindowLevel().getWidth();
                var previousCenter = wlut.getWindowLevel().getCenter();
                // set slice window level
                wlut.setWindowLevel(wl);
                // fire event
                if ( previousWidth !== wl.getWidth() ) {
                    this.fireEvent({"type": "wl-width-change",
                        "wc": wl.getCenter(), "ww": wl.getWidth(),
                        "skipGenerate": true});
                }
                if ( previousCenter !== wl.getCenter() ) {
                    this.fireEvent({"type": "wl-center-change",
                        "wc": wl.getCenter(), "ww": wl.getWidth(),
                        "skipGenerate": true});
                }
            }
        }

        // update in case of wl change
        // TODO: should not be run in a getter...
        wlut.update();

        // return
        return wlut;
    };
    /**
     * Add the window LUT to the list.
     * @param {Window} wlut The window LUT of the image.
     */
    this.addWindowLut = function (wlut)
    {
        var rsi = wlut.getRescaleLut().getRSI();
        windowLuts[rsi.toString()] = wlut;
    };

    /**
     * Get the window presets.
     * @return {Object} The window presets.
     */
    this.getWindowPresets = function () {
        return windowPresets;
    };

    /**
     * Get the window presets names.
     * @return {Object} The list of window presets names.
     */
    this.getWindowPresetsNames = function () {
        return Object.keys(windowPresets);
    };

    /**
     * Set the window presets.
     * @param {Object} presets The window presets.
     */
    this.setWindowPresets = function (presets) {
        windowPresets = presets;
    };
    /**
     * Add window presets to the existing ones.
     * @param {Object} presets The window presets.
     * @param {Number} k The slice the preset belong to.
     */
    this.addWindowPresets = function (presets, k) {
        var keys = Object.keys(presets);
        var key = null;
        for (var i = 0; i < keys.length; ++i) {
            key = keys[i];
            if (typeof windowPresets[key] !== "undefined") {
                if (typeof windowPresets[key].perslice !== "undefined" &&
                    windowPresets[key].perslice === true) {
                    // use first new preset wl...
                    windowPresets[key].wl.splice(k, 0, presets[key].wl[0]);
                } else {
                    windowPresets[key] = presets[key];
                }
            } else {
                // add new
                windowPresets[key] = presets[key];
            }
        }
    };

    /**
     * Get the colour map of the image.
     * @return {Object} The colour map of the image.
     */
    this.getColourMap = function() { return colourMap; };
    /**
     * Set the colour map of the image.
     * @param {Object} map The colour map of the image.
     */
    this.setColourMap = function(map) {
        colourMap = map;
        // TODO Better handle this...
        if( this.getImage().getPhotometricInterpretation() === "MONOCHROME1") {
            colourMap = dwv.image.lut.invPlain;
        }
        this.fireEvent({"type": "colour-change",
           "wc": this.getCurrentWindowLut().getWindowLevel().getCenter(),
           "ww": this.getCurrentWindowLut().getWindowLevel().getWidth() });
    };

    /**
     * Get the current position.
     * @return {Object} The current position.
     */
    this.getCurrentPosition = function() {
        // return a clone to avoid reference problems
        return {"i": currentPosition.i, "j": currentPosition.j, "k": currentPosition.k};
    };
    /**
     * Set the current position.
     * @param {Object} pos The current position.
     * @param {Boolean} silent If true, does not fire a slice-change event.
     * @return {Boolean} False if not in bounds
     */
    this.setCurrentPosition = function(pos, silent) {
        // default silent flag to false
        if ( typeof silent === "undefined" ) {
            silent = false;
        }
        // check if possible
        if( !image.getGeometry().getSize().isInBounds(pos.i,pos.j,pos.k) ) {
            return false;
        }
        var oldPosition = currentPosition;
        currentPosition = pos;

        // fire a 'position-change' event
        if( image.getPhotometricInterpretation().match(/MONOCHROME/) !== null )
        {
            this.fireEvent({"type": "position-change",
                "i": pos.i, "j": pos.j, "k": pos.k,
                "value": image.getRescaledValue(pos.i,pos.j,pos.k, this.getCurrentFrame())});
        }
        else
        {
            this.fireEvent({"type": "position-change",
                "i": pos.i, "j": pos.j, "k": pos.k});
        }

        // fire a slice change event (used to trigger redraw)
        if ( !silent ) {
          if( oldPosition.k !== currentPosition.k ) {
              this.fireEvent({"type": "slice-change"});
          }
        }

        // all good
        return true;
    };

    /**
     * Get the current frame number.
     * @return {Number} The current frame number.
     */
    this.getCurrentFrame = function() {
        return currentFrame;
    };

    /**
     * Set the current frame number.
     * @param {Number} The current frame number.
     * @return {Boolean} False if not in bounds
     */
    this.setCurrentFrame = function (frame) {
        // check if possible
        if( frame < 0 || frame >= image.getNumberOfFrames() ) {
            return false;
        }
        // assign
        var oldFrame = currentFrame;
        currentFrame = frame;
        // fire event
        if( oldFrame !== currentFrame && image.getNumberOfFrames() !== 1 ) {
            this.fireEvent({"type": "frame-change", "frame": currentFrame});
            // silent set current position to update info text
            this.setCurrentPosition(this.getCurrentPosition(),true);
        }
        // all good
        return true;
    };

    /**
     * Append another view to this one.
     * @param {Object} rhs The view to append.
     */
    this.append = function( rhs )
    {
       // append images
       var newSliceNumber = this.getImage().appendSlice( rhs.getImage() );
       // update position if a slice was appended before
       if ( newSliceNumber <= this.getCurrentPosition().k ) {
           this.setCurrentPosition(
             {"i": this.getCurrentPosition().i,
             "j": this.getCurrentPosition().j,
             "k": this.getCurrentPosition().k + 1}, true );
       }
       // add window presets
       this.addWindowPresets( rhs.getWindowPresets(), newSliceNumber );
    };

    /**
     * Append a frame buffer to the included image.
     * @param {Object} frameBuffer The frame buffer to append.
     */
    this.appendFrameBuffer = function (frameBuffer)
    {
        this.getImage().appendFrameBuffer(frameBuffer);
    };

    /**
     * Set the view window/level.
     * @param {Number} center The window center.
     * @param {Number} width The window width.
     * @param {String} name Associated preset name, defaults to 'manual'.
     * Warning: uses the latest set rescale LUT or the default linear one.
     */
    this.setWindowLevel = function ( center, width, name )
    {
        // window width shall be >= 1 (see https://www.dabsoft.ch/dicom/3/C.11.2.1.2/)
        if ( width >= 1 ) {

            // get current window/level (before updating name)
            var sliceNumber = this.getCurrentPosition().k;
            var currentWl = null;
            var rsi = image.getRescaleSlopeAndIntercept(sliceNumber);
            if ( rsi && typeof rsi !== "undefined" ) {
                var currentLut = windowLuts[ rsi.toString() ];
                if ( currentLut && typeof currentLut !== "undefined") {
                    currentWl = currentLut.getWindowLevel();
                }
            }

            if ( typeof name === "undefined" ) {
                name = "manual";
            }
            // update current preset name
            currentPresetName = name;

            var wl = new dwv.image.WindowLevel(center, width);
            var keys = Object.keys(windowLuts);

            // create the first lut if none exists
            if (keys.length === 0) {
                // create the rescale lookup table
                var rescaleLut = new dwv.image.lut.Rescale(
                    image.getRescaleSlopeAndIntercept(0), image.getMeta().BitsStored );
                // create the window lookup table
                var windowLut = new dwv.image.lut.Window(rescaleLut, image.getMeta().IsSigned);
                this.addWindowLut(windowLut);
            }

            // set window level on luts
            for ( var key in windowLuts ) {
                windowLuts[key].setWindowLevel(wl);
            }

            // fire window level change event
            if (currentWl && typeof currentWl !== "undefined") {
                if (currentWl.getWidth() !== width) {
                    this.fireEvent({"type": "wl-width-change", "wc": center, "ww": width });
                }
                if (currentWl.getCenter() !== center) {
                    this.fireEvent({"type": "wl-center-change", "wc": center, "ww": width });
                }
            } else {
                this.fireEvent({"type": "wl-width-change", "wc": center, "ww": width });
                this.fireEvent({"type": "wl-center-change", "wc": center, "ww": width });
            }
        }
    };

    /**
     * Set the window level to the preset with the input name.
     * @param {String} name The name of the preset to activate.
     */
    this.setWindowLevelPreset = function (name) {
        var preset = this.getWindowPresets()[name];
        if ( typeof preset === "undefined" ) {
            throw new Error("Unknown window level preset: '" + name + "'");
        }
        // special min/max
        if (name === "minmax" && typeof preset.wl === "undefined") {
            preset.wl = this.getWindowLevelMinMax();
        }
        // special 'perslice' case
        if (typeof preset.perslice !== "undefined" &&
            preset.perslice === true) {
            preset = { "wl": preset.wl[this.getCurrentPosition().k] };
        }
        // set w/l
        this.setWindowLevel( preset.wl.getCenter(), preset.wl.getWidth(), name );
    };

    /**
     * Set the window level to the preset with the input id.
     * @param {Number} id The id of the preset to activate.
     */
    this.setWindowLevelPresetById = function (id) {
        var keys = Object.keys(this.getWindowPresets());
        this.setWindowLevelPreset( keys[id] );
    };

    /**
     * Clone the image using all meta data and the original data buffer.
     * @return {View} A full copy of this {dwv.image.View}.
     */
    this.clone = function ()
    {
        var copy = new dwv.image.View(this.getImage());
        for ( var key in windowLuts ) {
            copy.addWindowLut(windowLuts[key]);
        }
        copy.setListeners(this.getListeners());
        return copy;
    };

    /**
     * View listeners
     * @private
     * @type Object
     */
    var listeners = {};
    /**
     * Get the view listeners.
     * @return {Object} The view listeners.
     */
    this.getListeners = function() { return listeners; };
    /**
     * Set the view listeners.
     * @param {Object} list The view listeners.
     */
    this.setListeners = function(list) { listeners = list; };
};

/**
 * Get the image window/level that covers the full data range.
 * Warning: uses the latest set rescale LUT or the default linear one.
 */
dwv.image.View.prototype.getWindowLevelMinMax = function ()
{
    var range = this.getImage().getRescaledDataRange();
    var min = range.min;
    var max = range.max;
    var width = max - min;
    var center = min + width/2;
    return new dwv.image.WindowLevel(center, width);
};

/**
 * Set the image window/level to cover the full data range.
 * Warning: uses the latest set rescale LUT or the default linear one.
 */
dwv.image.View.prototype.setWindowLevelMinMax = function()
{
    // calculate center and width
    var wl = this.getWindowLevelMinMax();
    // set window level
    this.setWindowLevel(wl.getCenter(), wl.getWidth(), "minmax");
};

/**
 * Generate display image data to be given to a canvas.
 * @param {Array} array The array to fill in.
 */
dwv.image.View.prototype.generateImageData = function( array )
{
    var windowLut = this.getCurrentWindowLut();

    var image = this.getImage();
    var sliceSize = image.getGeometry().getSize().getSliceSize();
    var sliceOffset = sliceSize * this.getCurrentPosition().k;
    var frame = (this.getCurrentFrame()) ? this.getCurrentFrame() : 0;

    var index = 0;
    var pxValue = 0;
    var stepPos = 0;

    var photoInterpretation = image.getPhotometricInterpretation();
    switch (photoInterpretation)
    {
    case "MONOCHROME1":
    case "MONOCHROME2":
        var colourMap = this.getColourMap();
        var iMax = sliceOffset + sliceSize;
        for(var i=sliceOffset; i < iMax; ++i)
        {
            pxValue = parseInt( windowLut.getValue(
                    image.getValueAtOffset(i, frame) ), 10 );
            array.data[index] = colourMap.red[pxValue];
            array.data[index+1] = colourMap.green[pxValue];
            array.data[index+2] = colourMap.blue[pxValue];
            array.data[index+3] = 0xff;
            index += 4;
        }
        break;

    case "RGB":
        // 3 times bigger...
        sliceOffset *= 3;
        // the planar configuration defines the memory layout
        var planarConfig = image.getPlanarConfiguration();
        if( planarConfig !== 0 && planarConfig !== 1 ) {
            throw new Error("Unsupported planar configuration: "+planarConfig);
        }
        // default: RGBRGBRGBRGB...
        var posR = sliceOffset;
        var posG = sliceOffset + 1;
        var posB = sliceOffset + 2;
        stepPos = 3;
        // RRRR...GGGG...BBBB...
        if (planarConfig === 1) {
            posR = sliceOffset;
            posG = sliceOffset + sliceSize;
            posB = sliceOffset + 2 * sliceSize;
            stepPos = 1;
        }

        for(var j=0; j < sliceSize; ++j)
        {
            array.data[index] = parseInt( windowLut.getValue(
                    image.getValueAtOffset(posR, frame) ), 10 );
            array.data[index+1] = parseInt( windowLut.getValue(
                    image.getValueAtOffset(posG, frame) ), 10 );
            array.data[index+2] = parseInt( windowLut.getValue(
                    image.getValueAtOffset(posB, frame) ), 10 );
            array.data[index+3] = 0xff;
            index += 4;

            posR += stepPos;
            posG += stepPos;
            posB += stepPos;
        }
        break;

    case "YBR_FULL_422":
        // theory:
        // http://dicom.nema.org/dicom/2013/output/chtml/part03/sect_C.7.html#sect_C.7.6.3.1.2
        // reverse equation:
        // https://en.wikipedia.org/wiki/YCbCr#JPEG_conversion

        // 3 times bigger...
        sliceOffset *= 3;
        // the planar configuration defines the memory layout
        var planarConfigYBR = image.getPlanarConfiguration();
        if( planarConfigYBR !== 0 && planarConfigYBR !== 1 ) {
            throw new Error("Unsupported planar configuration: "+planarConfigYBR);
        }
        // default: YBRYBRYBR...
        var posY = sliceOffset;
        var posCB = sliceOffset + 1;
        var posCR = sliceOffset + 2;
        stepPos = 3;
        // YYYY...BBBB...RRRR...
        if (planarConfigYBR === 1) {
            posY = sliceOffset;
            posCB = sliceOffset + sliceSize;
            posCR = sliceOffset + 2 * sliceSize;
            stepPos = 1;
        }

        var y, cb, cr;
        var r, g, b;
        for (var k=0; k < sliceSize; ++k)
        {
            y = image.getValueAtOffset(posY, frame);
            cb = image.getValueAtOffset(posCB, frame);
            cr = image.getValueAtOffset(posCR, frame);

            r = y + 1.402 * (cr - 128);
            g = y - 0.34414 * (cb - 128) - 0.71414 * (cr - 128);
            b = y + 1.772 * (cb - 128);

            array.data[index] = parseInt( windowLut.getValue(r), 10 );
            array.data[index+1] = parseInt( windowLut.getValue(g), 10 );
            array.data[index+2] = parseInt( windowLut.getValue(b), 10 );
            array.data[index+3] = 0xff;
            index += 4;

            posY += stepPos;
            posCB += stepPos;
            posCR += stepPos;
        }
        break;

    default:
        throw new Error("Unsupported photometric interpretation: "+photoInterpretation);
    }
};

/**
 * Add an event listener on the view.
 * @param {String} type The event type.
 * @param {Object} listener The method associated with the provided event type.
 */
dwv.image.View.prototype.addEventListener = function(type, listener)
{
    var listeners = this.getListeners();
    if( !listeners[type] ) {
        listeners[type] = [];
    }
    listeners[type].push(listener);
};

/**
 * Remove an event listener on the view.
 * @param {String} type The event type.
 * @param {Object} listener The method associated with the provided event type.
 */
dwv.image.View.prototype.removeEventListener = function(type, listener)
{
    var listeners = this.getListeners();
    if( !listeners[type] ) {
        return;
    }
    for(var i=0; i < listeners[type].length; ++i)
    {
        if( listeners[type][i] === listener ) {
            listeners[type].splice(i,1);
        }
    }
};

/**
 * Fire an event: call all associated listeners.
 * @param {Object} event The event to fire.
 */
dwv.image.View.prototype.fireEvent = function(event)
{
    var listeners = this.getListeners();
    if( !listeners[event.type] ) {
        return;
    }
    for(var i=0; i < listeners[event.type].length; ++i)
    {
        listeners[event.type][i](event);
    }
};

/**
 * View factory.
 * @constructor
 */
dwv.image.ViewFactory = function () {};

/**
 * Get an View object from the read DICOM file.
 * @param {Object} dicomElements The DICOM tags.
 * @param {Object} image The associated image.
 * @return {View} The new View.
 */
dwv.image.ViewFactory.prototype.create = function (dicomElements, image)
{
    // view
    var view = new dwv.image.View(image);

    // presets
    var windowPresets = {};

    // DICOM presets
    var windowCenter = dicomElements.getFromKey("x00281050", true);
    var windowWidth = dicomElements.getFromKey("x00281051", true);
    var windowCWExplanation = dicomElements.getFromKey("x00281055", true);
    if ( windowCenter && windowWidth ) {
        var name;
        for ( var j = 0; j < windowCenter.length; ++j) {
            var center = parseFloat( windowCenter[j], 10 );
            var width = parseFloat( windowWidth[j], 10 );
            if ( center && width ) {
                name = "";
                if ( windowCWExplanation ) {
                    name = dwv.dicom.cleanString(windowCWExplanation[j]);
                }
                if (name === "") {
                    name = "Default"+j;
                }
                windowPresets[name] = {
                    "wl": [new dwv.image.WindowLevel(center, width)],
                    "name": name,
                    "perslice": true};
            }
        }
    }

    // min/max
    // Not filled yet since it is stil too costly to calculate min/max
    // for each slice... It will be filled at first use (see view.setWindowLevelPreset).
    // Order is important, if no wl from DICOM, this will be the default.
    windowPresets.minmax = { "name": "minmax" };

    // optional modality presets
    if ( typeof dwv.tool.defaultpresets !== "undefined" ) {
        var modality = image.getMeta().Modality;
        for( var key in dwv.tool.defaultpresets[modality] ) {
            var preset = dwv.tool.defaultpresets[modality][key];
            windowPresets[key] = {
                "wl": new dwv.image.WindowLevel(preset.center, preset.width),
                "name": key};
        }
    }

    // store
    view.setWindowPresets( windowPresets );

    return view;
};

// namespaces
var dwv = dwv || {};
dwv.io = dwv.io || {};

/**
 * DICOM data loader.
 */
dwv.io.DicomDataLoader = function ()
{
    // closure to self
    var self = this;

    /**
     * Loader options.
     * @private
     * @type Object
     */
    var options = {};

    /**
     * Set the loader options.
     * @param {Object} opt The input options.
     */
    this.setOptions = function (opt) {
        options = opt;
    };

    /**
     * DICOM buffer to dwv.image.View (asynchronous)
     */
    var db2v = new dwv.image.DicomBufferToView();

    /**
     * Load data.
     * @param {Object} buffer The DICOM buffer.
     * @param {String} origin The data origin.
     * @param {Number} index The data index.
     */
    this.load = function (buffer, origin, index) {
        // set character set
        if (typeof options.defaultCharacterSet !== "undefined") {
            db2v.setDefaultCharacterSet(options.defaultCharacterSet);
        }
        // connect handlers
        db2v.onload = self.onload;
        db2v.onloadend = self.onloadend;
        db2v.onprogress = self.onprogress;
        // convert
        try {
            db2v.convert( buffer, index );
        } catch (error) {
            self.onerror(error);
        }
    };

    /**
     * Get a file load handler.
     * @param {Object} file The file to load.
     * @param {Number} index The index 'id' of the file.
     * @return {Function} A file load handler.
     */
    this.getFileLoadHandler = function (file, index) {
        return function (event) {
            self.load(event.target.result, file, index);
        };
    };

    /**
     * Get a url load handler.
     * @param {String} url The url to load.
     * @param {Number} index The index 'id' of the url.
     * @return {Function} A url load handler.
     */
    this.getUrlLoadHandler = function (url, index) {
        return function (/*event*/) {
            // check response status
            // https://developer.mozilla.org/en-US/docs/Web/HTTP/Response_codes
            // status 200: "OK"; status 0: "debug"
            if (this.status !== 200 && this.status !== 0) {
                self.onerror({'name': "RequestError",
                    'message': "Error status: " + this.status +
                    " while loading '" + url + "' [DicomDataLoader]" });
                return;
            }
            // load
            self.load(this.response, url, index);
        };
    };

    /**
     * Get an error handler.
     * @param {String} origin The file.name/url at the origin of the error.
     * @return {Function} An error handler.
     */
    this.getErrorHandler = function (origin) {
        return function (event) {
            var message = "";
            if (typeof event.getMessage !== "undefined") {
                message = event.getMessage();
            } else if (typeof this.status !== "undefined") {
                message = "http status: " + this.status;
            }
            self.onerror( {'name': "RequestError",
                'message': "An error occurred while reading '" + origin +
                "' (" + message + ") [DicomDataLoader]" } );
        };
    };

}; // class DicomDataLoader

/**
 * Check if the loader can load the provided file.
 * @param {Object} file The file to check.
 * @return True if the file can be loaded.
 */
dwv.io.DicomDataLoader.prototype.canLoadFile = function (file) {
    var split = file.name.split('.');
    var ext = "";
    if (split.length !== 1) {
        ext = split.pop().toLowerCase();
    }
    var hasExt = (ext.length !== 0);
    return !hasExt || (ext === "dcm");
};

/**
 * Check if the loader can load the provided url.
 * @param {String} url The url to check.
 * @return True if the url can be loaded.
 */
dwv.io.DicomDataLoader.prototype.canLoadUrl = function (url) {
    var split = url.split('.');
    var ext = "";
    if (split.length !== 1) {
        ext = split.pop().toLowerCase();
    }
    var hasExt = (ext.length !== 0) && (ext.length < 5);
    // wado url
    var isDicomContentType = (url.indexOf("contentType=application/dicom") !== -1);

    return isDicomContentType || (ext === "dcm") || !hasExt;
};

/**
 * Get the file content type needed by the loader.
 * @return One of the 'dwv.io.fileContentTypes'.
 */
dwv.io.DicomDataLoader.prototype.loadFileAs = function () {
    return dwv.io.fileContentTypes.ArrayBuffer;
};

/**
 * Get the url content type needed by the loader.
 * @return One of the 'dwv.io.urlContentTypes'.
 */
dwv.io.DicomDataLoader.prototype.loadUrlAs = function () {
    return dwv.io.urlContentTypes.ArrayBuffer;
};

/**
 * Handle a load event.
 * @param {Object} event The load event, 'event.target'
 *  should be the loaded data.
 * Default does nothing.
 */
dwv.io.DicomDataLoader.prototype.onload = function (/*event*/) {};
/**
 * Handle an load end event.
 * Default does nothing.
 */
dwv.io.DicomDataLoader.prototype.onloadend = function () {};
/**
 * Handle an error event.
 * @param {Object} event The error event, 'event.message'
 *  should be the error message.
 * Default does nothing.
 */
dwv.io.DicomDataLoader.prototype.onerror = function (/*event*/) {};
/**
 * Handle a progress event.
 * @param {Object} event The progress event.
 * Default does nothing.
 */
dwv.io.DicomDataLoader.prototype.onprogress = function (/*event*/) {};

/**
 * Add to Loader list.
 */
dwv.io.loaderList = dwv.io.loaderList || [];
dwv.io.loaderList.push( "DicomDataLoader" );

// namespaces
var dwv = dwv || {};
/** @namespace */
dwv.io = dwv.io || {};

// file content types
dwv.io.fileContentTypes = {
    'Text': 0,
    'ArrayBuffer': 1,
    'DataURL': 2
};

/**
 * Files loader.
 * @constructor
 */
dwv.io.FilesLoader = function ()
{
    /**
     * Closure to self.
     * @private
     * @type Object
     */
    var self = this;

    /**
     * Number of data to load.
     * @private
     * @type Number
     */
    var nToLoad = 0;
    /**
     * Number of loaded data.
     * @private
     * @type Number
     */
    var nLoaded = 0;

    /**
     * The default character set (optional).
     * @private
     * @type String
     */
    var defaultCharacterSet;

    /**
     * Get the default character set.
     * @return {String} The default character set.
     */
    this.getDefaultCharacterSet = function () {
        return defaultCharacterSet;
    };

    /**
     * Set the default character set.
     * @param {String} characterSet The character set.
     */
    this.setDefaultCharacterSet = function (characterSet) {
        defaultCharacterSet = characterSet;
    };

    /**
     * Set the number of data to load.
     * @param {Number} n The number of data to load.
     */
    this.setNToLoad = function (n) {
        nToLoad = n;
    };

    /**
     * Increment the number of loaded data
     * and call onloadend if loaded all data.
     */
    this.addLoaded = function () {
        nLoaded++;
        if ( nLoaded === nToLoad ) {
            self.onloadend();
        }
    };

}; // class File

/**
 * Handle a load event.
 * @param {Object} event The load event, 'event.target'
 *  should be the loaded data.
 * Default does nothing.
 */
dwv.io.FilesLoader.prototype.onload = function (/*event*/) {};
/**
 * Handle a load end event.
 * Default does nothing.
 */
dwv.io.FilesLoader.prototype.onloadend = function () {};
/**
 * Handle a progress event.
 * @param {Object} event The progress event.
 * Default does nothing.
 */
dwv.io.FilesLoader.prototype.onprogress = function (/*event*/) {};
/**
 * Handle an error event.
 * @param {Object} event The error event, 'event.message'
 *  should be the error message.
 * Default does nothing.
 */
dwv.io.FilesLoader.prototype.onerror = function (/*event*/) {};

/**
 * Load a list of files.
 * @param {Array} ioArray The list of files to load.
 * @external FileReader
 */
dwv.io.FilesLoader.prototype.load = function (ioArray)
{
    // closure to self for handlers
    var self = this;
    // set the number of data to load
    this.setNToLoad( ioArray.length );

    var mproghandler = new dwv.utils.MultiProgressHandler(self.onprogress);
    mproghandler.setNToLoad( ioArray.length );

    // get loaders
    var loaders = [];
    for (var m = 0; m < dwv.io.loaderList.length; ++m) {
        loaders.push( new dwv.io[dwv.io.loaderList[m]]() );
    }

    // set loaders callbacks
    var loader = null;
    for (var k = 0; k < loaders.length; ++k) {
        loader = loaders[k];
        loader.onload = self.onload;
        loader.onloadend = self.addLoaded;
        loader.onerror = self.onerror;
        loader.setOptions({
            'defaultCharacterSet': this.getDefaultCharacterSet()
        });
        loader.onprogress = mproghandler.getUndefinedMonoProgressHandler(1);
    }

    // loop on I/O elements
    for (var i = 0; i < ioArray.length; ++i)
    {
        var file = ioArray[i];
        var reader = new FileReader();

        // bind reader progress
        reader.onprogress = mproghandler.getMonoProgressHandler(i, 0);

        // find a loader
        var foundLoader = false;
        for (var l = 0; l < loaders.length; ++l) {
            loader = loaders[l];
            if (loader.canLoadFile(file)) {
                foundLoader = true;
                // set reader callbacks
                reader.onload = loader.getFileLoadHandler(file, i);
                reader.onerror = loader.getErrorHandler(file.name);
                // read
                if (loader.loadFileAs() === dwv.io.fileContentTypes.Text) {
                    reader.readAsText(file);
                } else if (loader.loadFileAs() === dwv.io.fileContentTypes.DataURL) {
                    reader.readAsDataURL(file);
                } else if (loader.loadFileAs() === dwv.io.fileContentTypes.ArrayBuffer) {
                    reader.readAsArrayBuffer(file);
                }
                // next file
                break;
            }
        }
        // TODO: throw?
        if (!foundLoader) {
            throw new Error("No loader found for file: "+file);
        }
    }
};

// namespaces
var dwv = dwv || {};
dwv.io = dwv.io || {};

/**
 * JSON text loader.
 */
dwv.io.JSONTextLoader = function ()
{
    // closure to self
    var self = this;

    /**
     * Set the loader options.
     * @param {Object} opt The input options.
     */
    this.setOptions = function () {
        // does nothing
    };

    /**
     * Load data.
     * @param {Object} text The input text.
     * @param {String} origin The data origin.
     * @param {Number} index The data index.
     */
    this.load = function (text, origin, index) {
        try {
            self.onload( text );
            self.onloadend();
        } catch (error) {
            self.onerror(error);
        }
        self.onprogress({'type': 'read-progress', 'lengthComputable': true,
            'loaded': 100, 'total': 100, 'index': index});
    };

    /**
     * Get a file load handler.
     * @param {Object} file The file to load.
     * @param {Number} index The index 'id' of the file.
     * @return {Function} A file load handler.
     */
    this.getFileLoadHandler = function (file, index) {
        return function (event) {
            self.load(event.target.result, file, index);
        };
    };

    /**
     * Get a url load handler.
     * @param {String} url The url to load.
     * @param {Number} index The index 'id' of the url.
     * @return {Function} A url load handler.
     */
    this.getUrlLoadHandler = function (url, index) {
        return function (/*event*/) {
            // check response status
            // https://developer.mozilla.org/en-US/docs/Web/HTTP/Response_codes
            // status 200: "OK"; status 0: "debug"
            if (this.status !== 200 && this.status !== 0) {
                self.onerror({'name': "RequestError",
                    'message': "Error status: " + this.status +
                    " while loading '" + url + "' [JSONTextLoader]" });
                return;
            }
            // load
            self.load(this.responseText, url, index);
        };
    };

    /**
     * Get an error handler.
     * @param {String} origin The file.name/url at the origin of the error.
     * @return {Function} An error handler.
     */
    this.getErrorHandler = function (origin) {
        return function (event) {
            var message = "";
            if (typeof event.getMessage !== "undefined") {
                message = event.getMessage();
            } else if (typeof this.status !== "undefined") {
                message = "http status: " + this.status;
            }
            self.onerror( {'name': "RequestError",
                'message': "An error occurred while reading '" + origin +
                "' (" + message + ") [JSONTextLoader]" } );
        };
    };

}; // class JSONTextLoader

/**
 * Check if the loader can load the provided file.
 * @param {Object} file The file to check.
 * @return True if the file can be loaded.
 */
dwv.io.JSONTextLoader.prototype.canLoadFile = function (file) {
    var ext = file.name.split('.').pop().toLowerCase();
    return (ext === "json");
};

/**
 * Check if the loader can load the provided url.
 * @param {String} url The url to check.
 * @return True if the url can be loaded.
 */
dwv.io.JSONTextLoader.prototype.canLoadUrl = function (url) {
    var ext = url.split('.').pop().toLowerCase();
    return (ext === "json");
};

/**
 * Get the file content type needed by the loader.
 * @return One of the 'dwv.io.fileContentTypes'.
 */
dwv.io.JSONTextLoader.prototype.loadFileAs = function () {
    return dwv.io.fileContentTypes.Text;
};

/**
 * Get the url content type needed by the loader.
 * @return One of the 'dwv.io.urlContentTypes'.
 */
dwv.io.JSONTextLoader.prototype.loadUrlAs = function () {
    return dwv.io.urlContentTypes.Text;
};

/**
 * Handle a load event.
 * @param {Object} event The load event, 'event.target'
 *  should be the loaded data.
 * Default does nothing.
 */
dwv.io.JSONTextLoader.prototype.onload = function (/*event*/) {};
/**
 * Handle an load end event.
 * Default does nothing.
 */
dwv.io.JSONTextLoader.prototype.onloadend = function () {};
/**
 * Handle an error event.
 * @param {Object} event The error event, 'event.message'
 *  should be the error message.
 * Default does nothing.
 */
dwv.io.JSONTextLoader.prototype.onerror = function (/*event*/) {};
/**
 * Handle a progress event.
 * @param {Object} event The progress event.
 * Default does nothing.
 */
dwv.io.JSONTextLoader.prototype.onprogress = function (/*event*/) {};

/**
 * Add to Loader list.
 */
dwv.io.loaderList = dwv.io.loaderList || [];
dwv.io.loaderList.push( "JSONTextLoader" );

// namespaces
var dwv = dwv || {};
dwv.io = dwv.io || {};

/**
 * Memory loader.
 * @constructor
 */
dwv.io.MemoryLoader = function ()
{
    /**
     * Closure to self.
     * @private
     * @type Object
     */
    var self = this;

    /**
     * Number of data to load.
     * @private
     * @type Number
     */
    var nToLoad = 0;
    /**
     * Number of loaded data.
     * @private
     * @type Number
     */
    var nLoaded = 0;

    /**
     * The default character set (optional).
     * @private
     * @type String
     */
    var defaultCharacterSet;

    /**
     * Get the default character set.
     * @return {String} The default character set.
     */
    this.getDefaultCharacterSet = function () {
        return defaultCharacterSet;
    };

    /**
     * Set the default character set.
     * @param {String} characterSet The character set.
     */
    this.setDefaultCharacterSet = function (characterSet) {
        defaultCharacterSet = characterSet;
    };

    /**
     * Set the number of data to load.
     * @param {Number} n The number of data to load.
     */
    this.setNToLoad = function (n) {
        nToLoad = n;
    };

    /**
     * Increment the number of loaded data
     * and call onloadend if loaded all data.
     */
    this.addLoaded = function () {
        nLoaded++;
        if ( nLoaded === nToLoad ) {
            self.onloadend();
        }
    };

}; // class Memory

/**
 * Handle a load event.
 * @param {Object} event The load event, 'event.target'
 *  should be the loaded data.
 * Default does nothing.
 */
dwv.io.MemoryLoader.prototype.onload = function (/*event*/) {};
/**
 * Handle a load end event.
 * Default does nothing.
 */
dwv.io.MemoryLoader.prototype.onloadend = function () {};
/**
 * Handle a progress event.
 * @param {Object} event The progress event.
 * Default does nothing.
 */
dwv.io.MemoryLoader.prototype.onprogress = function (/*event*/) {};
/**
 * Handle an error event.
 * @param {Object} event The error event, 'event.message'
 *  should be the error message.
 * Default does nothing.
 */
dwv.io.MemoryLoader.prototype.onerror = function (/*event*/) {};

/**
 * Load a list of buffers.
 * @param {Array} ioArray The list of buffers to load.
 */
dwv.io.MemoryLoader.prototype.load = function (ioArray)
{
    // closure to self for handlers
    var self = this;
    // set the number of data to load
    this.setNToLoad( ioArray.length );

    var mproghandler = new dwv.utils.MultiProgressHandler(self.onprogress);
    mproghandler.setNToLoad( ioArray.length );

    // get loaders
    var loaders = [];
    for (var m = 0; m < dwv.io.loaderList.length; ++m) {
        loaders.push( new dwv.io[dwv.io.loaderList[m]]() );
    }

    // set loaders callbacks
    var loader = null;
    for (var k = 0; k < loaders.length; ++k) {
        loader = loaders[k];
        loader.onload = self.onload;
        loader.onloadend = self.addLoaded;
        loader.onerror = self.onerror;
        loader.setOptions({
            'defaultCharacterSet': this.getDefaultCharacterSet()
        });
        loader.onprogress = mproghandler.getUndefinedMonoProgressHandler(1);
    }

    // loop on I/O elements
    for (var i = 0; i < ioArray.length; ++i)
    {
        var iodata = ioArray[i];

        // find a loader
        var foundLoader = false;
        for (var l = 0; l < loaders.length; ++l) {
            loader = loaders[l];
            if (loader.canLoadUrl(iodata.filename)) {
                foundLoader = true;
                // read
                loader.load(iodata.data, iodata.filename, i);
                // next file
                break;
            }
        }
        // TODO: throw?
        if (!foundLoader) {
            throw new Error("No loader found for file: "+iodata.filename);
        }
    }
};

// namespaces
var dwv = dwv || {};
dwv.io = dwv.io || {};

/**
 * Raw image loader.
 */
dwv.io.RawImageLoader = function ()
{
    // closure to self
    var self = this;

    /**
     * Set the loader options.
     * @param {Object} opt The input options.
     */
    this.setOptions = function () {
        // does nothing
    };

    /**
     * Create a Data URI from an HTTP request response.
     * @param {Object} response The HTTP request response.
     * @param {String} dataType The data type.
     */
    function createDataUri(response, dataType) {
        // image data as string
        var bytes = new Uint8Array(response);
        var imageDataStr = '';
        for( var i = 0; i < bytes.byteLength; ++i ) {
            imageDataStr += String.fromCharCode(bytes[i]);
        }
        // image type
        var imageType = dataType;
        if (imageType === "jpg") {
            imageType = "jpeg";
        }
        // create uri
        var uri = "data:image/" + imageType + ";base64," + window.btoa(imageDataStr);
        return uri;
    }

    /**
     * Load data.
     * @param {Object} dataUri The data URI.
     * @param {String} origin The data origin.
     * @param {Number} index The data index.
     */
    this.load = function ( dataUri, origin, index ) {
        // create a DOM image
        var image = new Image();
        image.src = dataUri;
        // storing values to pass them on
        image.origin = origin;
        image.index = index;
        // triggered by ctx.drawImage
        image.onload = function (/*event*/) {
            try {
                self.onload( dwv.image.getViewFromDOMImage(this) );
                self.onloadend();
            } catch (error) {
                self.onerror(error);
            }
            self.onprogress({'type': 'read-progress', 'lengthComputable': true,
                'loaded': 100, 'total': 100, 'index': index});
        };
    };

    /**
     * Get a file load handler.
     * @param {Object} file The file to load.
     * @param {Number} index The index 'id' of the file.
     * @return {Function} A file load handler.
     */
    this.getFileLoadHandler = function (file, index) {
        return function (event) {
            self.load(event.target.result, file, index);
        };
    };

    /**
     * Get a url load handler.
     * @param {String} url The url to load.
     * @param {Number} index The index 'id' of the url.
     * @return {Function} A url load handler.
     */
    this.getUrlLoadHandler = function (url, index) {
        return function (/*event*/) {
            // check response status
            // https://developer.mozilla.org/en-US/docs/Web/HTTP/Response_codes
            // status 200: "OK"; status 0: "debug"
            if (this.status !== 200 && this.status !== 0) {
                self.onerror({'name': "RequestError",
                    'message': "Error status: " + this.status +
                    " while loading '" + url + "' [RawImageLoader]" });
                return;
            }
            // load
            var ext = url.split('.').pop().toLowerCase();
            self.load(createDataUri(this.response, ext), url, index);
        };
    };

    /**
     * Get an error handler.
     * @param {String} origin The file.name/url at the origin of the error.
     * @return {Function} An error handler.
     */
    this.getErrorHandler = function (origin) {
        return function (event) {
            var message = "";
            if (typeof event.getMessage !== "undefined") {
                message = event.getMessage();
            } else if (typeof this.status !== "undefined") {
                message = "http status: " + this.status;
            }
            self.onerror( {'name': "RequestError",
                'message': "An error occurred while reading '" + origin +
                "' (" + message + ") [RawImageLoader]" } );
        };
    };

}; // class RawImageLoader

/**
 * Check if the loader can load the provided file.
 * @param {Object} file The file to check.
 * @return True if the file can be loaded.
 */
dwv.io.RawImageLoader.prototype.canLoadFile = function (file) {
    return file.type.match("image.*");
};

/**
 * Check if the loader can load the provided url.
 * @param {String} url The url to check.
 * @return True if the url can be loaded.
 */
dwv.io.RawImageLoader.prototype.canLoadUrl = function (url) {
    var ext = url.split('.').pop().toLowerCase();
    var hasImageExt = (ext === "jpeg") || (ext === "jpg") ||
            (ext === "png") || (ext === "gif");
    // wado url
    var isImageContentType = (url.indexOf("contentType=image/jpeg") !== -1) ||
        (url.indexOf("contentType=image/png") !== -1) ||
        (url.indexOf("contentType=image/gif") !== -1);

    return isImageContentType || hasImageExt;
};

/**
 * Get the file content type needed by the loader.
 * @return One of the 'dwv.io.fileContentTypes'.
 */
dwv.io.RawImageLoader.prototype.loadFileAs = function () {
    return dwv.io.fileContentTypes.DataURL;
};

/**
 * Get the url content type needed by the loader.
 * @return One of the 'dwv.io.urlContentTypes'.
 */
dwv.io.RawImageLoader.prototype.loadUrlAs = function () {
    return dwv.io.urlContentTypes.ArrayBuffer;
};

/**
 * Handle a load event.
 * @param {Object} event The load event, 'event.target'
 *  should be the loaded data.
 * Default does nothing.
 */
dwv.io.RawImageLoader.prototype.onload = function (/*event*/) {};
/**
 * Handle an load end event.
 * Default does nothing.
 */
dwv.io.RawImageLoader.prototype.onloadend = function () {};
/**
 * Handle an error event.
 * @param {Object} event The error event, 'event.message'
 *  should be the error message.
 * Default does nothing.
 */
dwv.io.RawImageLoader.prototype.onerror = function (/*event*/) {};
/**
 * Handle a progress event.
 * @param {Object} event The progress event.
 * Default does nothing.
 */
dwv.io.RawImageLoader.prototype.onprogress = function (/*event*/) {};

/**
 * Add to Loader list.
 */
dwv.io.loaderList = dwv.io.loaderList || [];
dwv.io.loaderList.push( "RawImageLoader" );

// namespaces
var dwv = dwv || {};
dwv.io = dwv.io || {};

/**
 * Raw video loader.
 * url example (cors enabled):
 *   https://raw.githubusercontent.com/clappr/clappr/master/test/fixtures/SampleVideo_360x240_1mb.mp4
 */
dwv.io.RawVideoLoader = function ()
{
    // closure to self
    var self = this;

    /**
     * Set the loader options.
     * @param {Object} opt The input options.
     */
    this.setOptions = function () {
        // does nothing
    };

    /**
     * Create a Data URI from an HTTP request response.
     * @param {Object} response The HTTP request response.
     * @param {String} dataType The data type.
     */
    function createDataUri(response, dataType) {
        // image data as string
        var bytes = new Uint8Array(response);
        var videoDataStr = '';
        for( var i = 0; i < bytes.byteLength; ++i ) {
            videoDataStr += String.fromCharCode(bytes[i]);
        }
        // create uri
        var uri = "data:video/" + dataType + ";base64," + window.btoa(videoDataStr);
        return uri;
    }

    /**
     * Internal Data URI load.
     * @param {Object} dataUri The data URI.
     * @param {String} origin The data origin.
     * @param {Number} index The data index.
     */
    this.load = function ( dataUri, origin, index ) {
        // create a DOM video
        var video = document.createElement('video');
        video.src = dataUri;
        // storing values to pass them on
        video.file = origin;
        video.index = index;
        // onload handler
        video.onloadedmetadata = function (/*event*/) {
            try {
                dwv.image.getViewFromDOMVideo(this,
                    self.onload, self.onprogress, self.onloadend, index);
            } catch (error) {
                self.onerror(error);
            }
        };
    };

    /**
     * Get a file load handler.
     * @param {Object} file The file to load.
     * @param {Number} index The index 'id' of the file.
     * @return {Function} A file load handler.
     */
    this.getFileLoadHandler = function (file, index) {
        return function (event) {
            self.load(event.target.result, file, index);
        };
    };

    /**
     * Get a url load handler.
     * @param {String} url The url to load.
     * @param {Number} index The index 'id' of the url.
     * @return {Function} A url load handler.
     */
    this.getUrlLoadHandler = function (url, index) {
        return function (/*event*/) {
            // check response status
            // https://developer.mozilla.org/en-US/docs/Web/HTTP/Response_codes
            // status 200: "OK"; status 0: "debug"
            if (this.status !== 200 && this.status !== 0) {
                self.onerror({'name': "RequestError",
                    'message': "Error status: " + this.status +
                    " while loading '" + url + "' [RawVideoLoader]" });
                return;
            }
            // load
            var ext = url.split('.').pop().toLowerCase();
            self.load(createDataUri(this.response, ext), url, index);
        };
    };

    /**
     * Get an error handler.
     * @param {String} origin The file.name/url at the origin of the error.
     * @return {Function} An error handler.
     */
    this.getErrorHandler = function (origin) {
        return function (event) {
            var message = "";
            if (typeof event.getMessage !== "undefined") {
                message = event.getMessage();
            } else if (typeof this.status !== "undefined") {
                message = "http status: " + this.status;
            }
            self.onerror( {'name': "RequestError",
                'message': "An error occurred while reading '" + origin +
                "' (" + message + ") [RawVideoLoader]" } );
        };
    };

}; // class RawVideoLoader

/**
 * Check if the loader can load the provided file.
 * @param {Object} file The file to check.
 * @return True if the file can be loaded.
 */
dwv.io.RawVideoLoader.prototype.canLoadFile = function (file) {
    return file.type.match("video.*");
};

/**
 * Check if the loader can load the provided url.
 * @param {String} url The url to check.
 * @return True if the url can be loaded.
 */
dwv.io.RawVideoLoader.prototype.canLoadUrl = function (url) {
    var ext = url.split('.').pop().toLowerCase();
    return (ext === "mp4") || (ext === "ogg") ||
            (ext === "webm");
};

/**
 * Get the file content type needed by the loader.
 * @return One of the 'dwv.io.fileContentTypes'.
 */
dwv.io.RawVideoLoader.prototype.loadFileAs = function () {
    return dwv.io.fileContentTypes.DataURL;
};

/**
 * Get the url content type needed by the loader.
 * @return One of the 'dwv.io.urlContentTypes'.
 */
dwv.io.RawVideoLoader.prototype.loadUrlAs = function () {
    return dwv.io.urlContentTypes.ArrayBuffer;
};

/**
 * Handle a load event.
 * @param {Object} event The load event, 'event.target'
 *  should be the loaded data.
 * Default does nothing.
 */
dwv.io.RawVideoLoader.prototype.onload = function (/*event*/) {};
/**
 * Handle an load end event.
 * Default does nothing.
 */
dwv.io.RawVideoLoader.prototype.onloadend = function () {};
/**
 * Handle an error event.
 * @param {Object} event The error event, 'event.message'
 *  should be the error message.
 * Default does nothing.
 */
dwv.io.RawVideoLoader.prototype.onerror = function (/*event*/) {};
/**
 * Handle a progress event.
 * @param {Object} event The progress event.
 * Default does nothing.
 */
dwv.io.RawVideoLoader.prototype.onprogress = function (/*event*/) {};

/**
 * Add to Loader list.
 */
dwv.io.loaderList = dwv.io.loaderList || [];
dwv.io.loaderList.push( "RawVideoLoader" );

// namespaces
var dwv = dwv || {};
dwv.io = dwv.io || {};

// url content types
dwv.io.urlContentTypes = {
    'Text': 0,
    'ArrayBuffer': 1,
    'oups': 2
};

/**
 * Urls loader.
 * @constructor
 */
dwv.io.UrlsLoader = function ()
{
    /**
     * Closure to self.
     * @private
     * @type Object
     */
    var self = this;

    /**
     * Number of data to load.
     * @private
     * @type Number
     */
    var nToLoad = 0;
    /**
     * Number of loaded data.
     * @private
     * @type Number
     */
    var nLoaded = 0;

    /**
     * The default character set (optional).
     * @private
     * @type String
     */
    var defaultCharacterSet;

    /**
     * Get the default character set.
     * @return {String} The default character set.
     */
    this.getDefaultCharacterSet = function () {
        return defaultCharacterSet;
    };

    /**
     * Set the default character set.
     * @param {String} characterSet The character set.
     */
    this.setDefaultCharacterSet = function (characterSet) {
        defaultCharacterSet = characterSet;
    };

    /**
     * Set the number of data to load.
     * @param {Number} n The number of data to load.
     */
    this.setNToLoad = function (n) {
        nToLoad = n;
    };

    /**
     * Increment the number of loaded data
     * and call onloadend if loaded all data.
     */
    this.addLoaded = function () {
        nLoaded++;
        if ( nLoaded === nToLoad ) {
            self.onloadend();
        }
    };

}; // class Url

/**
 * Handle a load event.
 * @param {Object} event The load event, 'event.target'
 *  should be the loaded data.
 * Default does nothing.
 */
dwv.io.UrlsLoader.prototype.onload = function (/*event*/) {};
/**
 * Handle a load end event.
 * Default does nothing.
 */
dwv.io.UrlsLoader.prototype.onloadend = function () {};
/**
 * Handle a progress event.
 * @param {Object} event The progress event.
 * Default does nothing.
 */
dwv.io.UrlsLoader.prototype.onprogress = function (/*event*/) {};
/**
 * Handle an error event.
 * @param {Object} event The error event, 'event.message'
 *  should be the error message.
 * Default does nothing.
 */
dwv.io.UrlsLoader.prototype.onerror = function (/*event*/) {};

/**
 * Load a list of URLs.
 * @param {Array} ioArray The list of urls to load.
 * @param {Object} options Load options.
 * @external XMLHttpRequest
 */
dwv.io.UrlsLoader.prototype.load = function (ioArray, options)
{
    // closure to self for handlers
    var self = this;
    // set the number of data to load
    this.setNToLoad( ioArray.length );

    var mproghandler = new dwv.utils.MultiProgressHandler(self.onprogress);
    mproghandler.setNToLoad( ioArray.length );

    // get loaders
    var loaders = [];
    for (var m = 0; m < dwv.io.loaderList.length; ++m) {
        loaders.push( new dwv.io[dwv.io.loaderList[m]]() );
    }

    // set loaders callbacks
    var loader = null;
    for (var k = 0; k < loaders.length; ++k) {
        loader = loaders[k];
        loader.onload = self.onload;
        loader.onloadend = self.addLoaded;
        loader.onerror = self.onerror;
        loader.setOptions({
            'defaultCharacterSet': this.getDefaultCharacterSet()
        });
        loader.onprogress = mproghandler.getUndefinedMonoProgressHandler(1);
    }

    // loop on I/O elements
    for (var i = 0; i < ioArray.length; ++i)
    {
        var url = ioArray[i];
        var request = new XMLHttpRequest();
        request.open('GET', url, true);

        // optional request headers
        if ( typeof options.requestHeaders !== "undefined" ) {
            var requestHeaders = options.requestHeaders;
            for (var j = 0; j < requestHeaders.length; ++j) {
                if ( typeof requestHeaders[j].name !== "undefined" &&
                    typeof requestHeaders[j].value !== "undefined" ) {
                    request.setRequestHeader(requestHeaders[j].name, requestHeaders[j].value);
                }
            }
        }

        // bind reader progress
        request.onprogress = mproghandler.getMonoProgressHandler(i, 0);
        request.onloadend = mproghandler.getMonoOnLoadEndHandler(i, 0);

        // find a loader
        var foundLoader = false;
        for (var l = 0; l < loaders.length; ++l) {
            loader = loaders[l];
            if (loader.canLoadUrl(url)) {
                foundLoader = true;
                // set reader callbacks
                request.onload = loader.getUrlLoadHandler(url, i);
                request.onerror = loader.getErrorHandler(url);
                // response type (default is 'text')
                if (loader.loadUrlAs() === dwv.io.urlContentTypes.ArrayBuffer) {
                    request.responseType = "arraybuffer";
                }
                // read
                request.send(null);
                // next file
                break;
            }
        }
        // TODO: throw?
        if (!foundLoader) {
            throw new Error("No loader found for url: "+url);
        }
    }
};

// namespaces
var dwv = dwv || {};
dwv.io = dwv.io || {};
// external
var JSZip = JSZip || {};

/**
 * ZIP data loader.
 */
dwv.io.ZipLoader = function ()
{
    // closure to self
    var self = this;

    /**
     * Loader options.
     * @private
     * @type Object
     */
    var options = {};

    /**
     * Set the loader options.
     * @param {Object} opt The input options.
     */
    this.setOptions = function (opt) {
        options = opt;
    };

    var filename = "";
    var files = [];
    var zobjs = null;

    /**
     * JSZip.async callback
     * @param {ArrayBuffer} content unzipped file image
     * @return {}
     */
    function zipAsyncCallback(content)
    {
    	files.push({"filename": filename, "data": content});

    	if (files.length < zobjs.length){
    		var num = files.length;
    		filename = zobjs[num].name;
    		zobjs[num].async("arrayBuffer").then(zipAsyncCallback);
    	}
    	else {
            var memoryIO = new dwv.io.MemoryLoader();
            memoryIO.onload = self.onload;
            memoryIO.onloadend = self.onloadend;
            memoryIO.onerror = self.onerror;
            memoryIO.onprogress = self.onprogress;

            memoryIO.load(files);
        }
    }

    /**
     * Load data.
     * @param {Object} buffer The DICOM buffer.
     * @param {String} origin The data origin.
     * @param {Number} index The data index.
     */
    this.load = function (buffer/*, origin, index*/) {
        JSZip.loadAsync(buffer).then( function(zip) {
            files = [];
        	zobjs = zip.file(/.*\.dcm/);
            // recursively load zip files into the files array
        	var num = files.length;
        	filename = zobjs[num].name;
        	zobjs[num].async("arrayBuffer").then(zipAsyncCallback);
        });
    };

    /**
     * Get a file load handler.
     * @param {Object} file The file to load.
     * @param {Number} index The index 'id' of the file.
     * @return {Function} A file load handler.
     */
    this.getFileLoadHandler = function (file, index) {
        return function (event) {
            self.load(event.target.result, file, index);
        };
    };

    /**
     * Get a url load handler.
     * @param {String} url The url to load.
     * @param {Number} index The index 'id' of the url.
     * @return {Function} A url load handler.
     */
    this.getUrlLoadHandler = function (url, index) {
        return function (/*event*/) {
            // check response status
            // https://developer.mozilla.org/en-US/docs/Web/HTTP/Response_codes
            // status 200: "OK"; status 0: "debug"
            if (this.status !== 200 && this.status !== 0) {
                self.onerror({'name': "RequestError",
                    'message': "Error status: " + this.status +
                    " while loading '" + url + "' [ZipLoader]" });
                return;
            }
            // load
            self.load(this.response, url, index);
        };
    };

    /**
     * Get an error handler.
     * @param {String} origin The file.name/url at the origin of the error.
     * @return {Function} An error handler.
     */
    this.getErrorHandler = function (origin) {
        return function (event) {
            var message = "";
            if (typeof event.getMessage !== "undefined") {
                message = event.getMessage();
            } else if (typeof this.status !== "undefined") {
                message = "http status: " + this.status;
            }
            self.onerror( {'name': "RequestError",
                'message': "An error occurred while reading '" + origin +
                "' (" + message + ") [ZipLoader]" } );
        };
    };

}; // class DicomDataLoader

/**
 * Check if the loader can load the provided file.
 * @param {Object} file The file to check.
 * @return True if the file can be loaded.
 */
dwv.io.ZipLoader.prototype.canLoadFile = function (file) {
    var ext = file.name.split('.').pop().toLowerCase();
    return (ext === "zip");
};

/**
 * Check if the loader can load the provided url.
 * @param {String} url The url to check.
 * @return True if the url can be loaded.
 */
dwv.io.ZipLoader.prototype.canLoadUrl = function (url) {
    var ext = url.split('.').pop().toLowerCase();
    return (ext === "zip");
};

/**
 * Get the file content type needed by the loader.
 * @return One of the 'dwv.io.fileContentTypes'.
 */
dwv.io.ZipLoader.prototype.loadFileAs = function () {
    return dwv.io.fileContentTypes.ArrayBuffer;
};

/**
 * Get the url content type needed by the loader.
 * @return One of the 'dwv.io.urlContentTypes'.
 */
dwv.io.ZipLoader.prototype.loadUrlAs = function () {
    return dwv.io.urlContentTypes.ArrayBuffer;
};

/**
 * Handle a load event.
 * @param {Object} event The load event, 'event.target'
 *  should be the loaded data.
 * Default does nothing.
 */
dwv.io.ZipLoader.prototype.onload = function (/*event*/) {};
/**
 * Handle an load end event.
 * Default does nothing.
 */
dwv.io.ZipLoader.prototype.onloadend = function () {};
/**
 * Handle an error event.
 * @param {Object} event The error event, 'event.message'
 *  should be the error message.
 * Default does nothing.
 */
dwv.io.ZipLoader.prototype.onerror = function (/*event*/) {};
/**
 * Handle a progress event.
 * @param {Object} event The progress event.
 * Default does nothing.
 */
dwv.io.ZipLoader.prototype.onprogress = function (/*event*/) {};

/**
 * Add to Loader list.
 */
dwv.io.loaderList = dwv.io.loaderList || [];
dwv.io.loaderList.push( "ZipLoader" );

// namespaces
var dwv = dwv || {};
/** @namespace */
dwv.math = dwv.math || {};

/**
 * Circular Bucket Queue.
 *
 * Returns input'd points in sorted order. All operations run in roughly O(1)
 * time (for input with small cost values), but it has a strict requirement:
 *
 * If the most recent point had a cost of c, any points added should have a cost
 * c' in the range c <= c' <= c + (capacity - 1).
 *
 * @constructor
 * @input bits
 * @input cost_functor
 */
dwv.math.BucketQueue = function(bits, cost_functor)
{
    this.bucketCount = 1 << bits; // # of buckets = 2^bits
    this.mask = this.bucketCount - 1; // 2^bits - 1 = index mask
    this.size = 0;

    this.loc = 0; // Current index in bucket list

    // Cost defaults to item value
    this.cost = (typeof(cost_functor) !== 'undefined') ? cost_functor : function(item) {
        return item;
    };

    this.buckets = this.buildArray(this.bucketCount);
};

dwv.math.BucketQueue.prototype.push = function(item) {
    // Prepend item to the list in the appropriate bucket
    var bucket = this.getBucket(item);
    item.next = this.buckets[bucket];
    this.buckets[bucket] = item;

    this.size++;
};

dwv.math.BucketQueue.prototype.pop = function() {
    if ( this.size === 0 ) {
        throw new Error("Cannot pop, bucketQueue is empty.");
    }

    // Find first empty bucket
    while ( this.buckets[this.loc] === null ) {
        this.loc = (this.loc + 1) % this.bucketCount;
    }

    // All items in bucket have same cost, return the first one
    var ret = this.buckets[this.loc];
    this.buckets[this.loc] = ret.next;
    ret.next = null;

    this.size--;
    return ret;
};

dwv.math.BucketQueue.prototype.remove = function(item) {
    // Tries to remove item from queue. Returns true on success, false otherwise
    if ( !item ) {
        return false;
    }

    // To find node, go to bucket and search through unsorted list.
    var bucket = this.getBucket(item);
    var node = this.buckets[bucket];

    while ( node !== null && !item.equals(node.next) ) {
        node = node.next;
    }

    if ( node === null ) {
        // Item not in list, ergo item not in queue
        return false;
    }
    else {
        // Found item, do standard list node deletion
        node.next = node.next.next;

        this.size--;
        return true;
    }
};

dwv.math.BucketQueue.prototype.isEmpty = function() {
    return this.size === 0;
};

dwv.math.BucketQueue.prototype.getBucket = function(item) {
    // Bucket index is the masked cost
    return this.cost(item) & this.mask;
};

dwv.math.BucketQueue.prototype.buildArray = function(newSize) {
    // Create array and initialze pointers to null
    var buckets = new Array(newSize);

    for ( var i = 0; i < buckets.length; i++ ) {
        buckets[i] = null;
    }

    return buckets;
};

// namespaces
var dwv = dwv || {};
dwv.math = dwv.math || {};

/**
 * Immutable 3x3 Matrix.
 * @constructor
 */
dwv.math.Matrix33 = function (
    m00, m01, m02,
    m10, m11, m12,
    m20, m21, m22 )
{
    // row-major order
    var mat = new Float32Array(9);
    mat[0] = m00; mat[1] = m01; mat[2] = m02;
    mat[3] = m10; mat[4] = m11; mat[5] = m12;
    mat[6] = m20; mat[7] = m21; mat[8] = m22;

    /**
     * Get a value of the matrix.
     * @param {Number} row The row at wich to get the value.
     * @param {Number} col The column at wich to get the value.
     */
    this.get = function (row, col) {
        return mat[row*3 + col];
    };
}; // Matrix33

/**
 * Check for Matrix33 equality.
 * @param {Object} rhs The other matrix to compare to.
 * @return {Boolean} True if both matrices are equal.
 */
dwv.math.Matrix33.prototype.equals = function (rhs) {
    return this.get(0,0) === rhs.get(0,0) && this.get(0,1) === rhs.get(0,1) &&
        this.get(0,2) === rhs.get(0,2) && this.get(1,0) === rhs.get(1,0) &&
        this.get(1,1) === rhs.get(1,1) && this.get(1,2) === rhs.get(1,2) &&
        this.get(2,0) === rhs.get(2,0) && this.get(2,1) === rhs.get(2,1) &&
        this.get(2,2) === rhs.get(2,2);
};

/**
 * Get a string representation of the Matrix33.
 * @return {String} The matrix as a string.
 */
dwv.math.Matrix33.prototype.toString = function () {
    return "[" + this.get(0,0) + ", " + this.get(0,1) + ", " + this.get(0,2) +
        "\n " + this.get(1,0) + ", " + this.get(1,1) + ", " + this.get(1,2) +
        "\n " + this.get(2,0) + ", " + this.get(2,1) + ", " + this.get(2,2) + "]";
};

/**
 * Multiply this matrix by a 3D vector.
 * @param {Object} vector3D The input 3D vector
 * @return {Object} The result 3D vector
 */
dwv.math.Matrix33.multiplyVector3D = function (vector3D) {
    // cache matrix values
    var m00 = this.get(0,0); var m01 = this.get(0,1); var m02 = this.get(0,2);
    var m10 = this.get(1,0); var m11 = this.get(1,1); var m12 = this.get(1,2);
    var m20 = this.get(2,0); var m21 = this.get(2,1); var m22 = this.get(2,2);
    // cache vector values
    var vx = vector3D.getX();
    var vy = vector3D.getY();
    var vz = vector3D.getZ();
    // calculate
    return new dwv.math.Vector3D(
        (m00 * vx) + (m01 * vy) + (m02 * vz),
        (m10 * vx) + (m11 * vy) + (m12 * vz),
        (m20 * vx) + (m21 * vy) + (m22 * vz) );
};

/**
 * Create a 3x3 identity matrix.
 * @return {Object} The identity matrix.
 */
dwv.math.getIdentityMat33= function () {
    return new dwv.math.Matrix33(
        1, 0, 0,
        0, 1, 0,
        0, 0, 1 );
};

// namespaces
var dwv = dwv || {};
dwv.math = dwv.math || {};

/**
 * Immutable 2D point.
 * @constructor
 * @param {Number} x The X coordinate for the point.
 * @param {Number} y The Y coordinate for the point.
 */
dwv.math.Point2D = function (x,y)
{
    /**
     * Get the X position of the point.
     * @return {Number} The X position of the point.
     */
    this.getX = function () { return x; };
    /**
     * Get the Y position of the point.
     * @return {Number} The Y position of the point.
     */
    this.getY = function () { return y; };
}; // Point2D class

/**
 * Check for Point2D equality.
 * @param {Object} rhs The other point to compare to.
 * @return {Boolean} True if both points are equal.
 */
dwv.math.Point2D.prototype.equals = function (rhs) {
    return rhs !== null &&
        this.getX() === rhs.getX() &&
        this.getY() === rhs.getY();
};

/**
 * Get a string representation of the Point2D.
 * @return {String} The point as a string.
 */
dwv.math.Point2D.prototype.toString = function () {
    return "(" + this.getX() + ", " + this.getY() + ")";
};

/**
 * Get the distance to another Point2D.
 * @param {Object} point2D The input point.
 */
dwv.math.Point2D.prototype.getDistance = function (point2D) {
    return Math.sqrt(
        (this.getX() - point2D.getX()) * (this.getX() - point2D.getX()) +
        (this.getY() - point2D.getY()) * (this.getY() - point2D.getY()) );
};

/**
 * Mutable 2D point.
 * @constructor
 * @param {Number} x The X coordinate for the point.
 * @param {Number} y The Y coordinate for the point.
 */
dwv.math.FastPoint2D = function (x,y)
{
    this.x = x;
    this.y = y;
}; // FastPoint2D class

/**
 * Check for FastPoint2D equality.
 * @param {Object} other The other point to compare to.
 * @return {Boolean} True if both points are equal.
 */
dwv.math.FastPoint2D.prototype.equals = function (rhs) {
    return rhs !== null &&
        this.x === rhs.x &&
        this.y === rhs.y;
};

/**
 * Get a string representation of the FastPoint2D.
 * @return {String} The point as a string.
 */
dwv.math.FastPoint2D.prototype.toString = function () {
    return "(" + this.x + ", " + this.y + ")";
};

/**
 * Immutable 3D point.
 * @constructor
 * @param {Number} x The X coordinate for the point.
 * @param {Number} y The Y coordinate for the point.
 * @param {Number} z The Z coordinate for the point.
 */
dwv.math.Point3D = function (x,y,z)
{
    /**
     * Get the X position of the point.
     * @return {Number} The X position of the point.
     */
    this.getX = function () { return x; };
    /**
     * Get the Y position of the point.
     * @return {Number} The Y position of the point.
     */
    this.getY = function () { return y; };
    /**
     * Get the Z position of the point.
     * @return {Number} The Z position of the point.
     */
    this.getZ = function () { return z; };
}; // Point3D class

/**
 * Check for Point3D equality.
 * @param {Object} rhs The other point to compare to.
 * @return {Boolean} True if both points are equal.
 */
dwv.math.Point3D.prototype.equals = function (rhs) {
    return rhs !== null &&
        this.getX() === rhs.getX() &&
        this.getY() === rhs.getY() &&
        this.getZ() === rhs.getZ();
};

/**
 * Get a string representation of the Point3D.
 * @return {String} The point as a string.
 */
dwv.math.Point3D.prototype.toString = function () {
    return "(" + this.getX() +
        ", " + this.getY() +
        ", " + this.getZ() + ")";
};

/**
 * Get the distance to another Point3D.
 * @param {Object} point3D The input point.
 */
dwv.math.Point3D.prototype.getDistance = function (point3D) {
    return Math.sqrt(
        (this.getX() - point3D.getX()) * (this.getX() - point3D.getX()) +
        (this.getY() - point3D.getY()) * (this.getY() - point3D.getY()) +
        (this.getZ() - point3D.getZ()) * (this.getZ() - point3D.getZ()) );
};

/**
 * Get the difference to another Point3D.
 * @param {Object} point3D The input point.
 * @return {Object} The 3D vector from the input point to this one.
 */
dwv.math.Point3D.prototype.minus = function (point3D) {
    return new dwv.math.Vector3D(
        (this.getX() - point3D.getX()),
        (this.getY() - point3D.getY()),
        (this.getZ() - point3D.getZ()) );
};

/**
 * Immutable 3D index.
 * @constructor
 * @param {Number} i The column index.
 * @param {Number} j The row index.
 * @param {Number} k The slice index.
 */
dwv.math.Index3D = function (i,j,k)
{
    /**
     * Get the column index.
     * @return {Number} The column index.
     */
    this.getI = function () { return i; };
    /**
     * Get the row index.
     * @return {Number} The row index.
     */
    this.getJ = function () { return j; };
    /**
     * Get the slice index.
     * @return {Number} The slice index.
     */
    this.getK = function () { return k; };
}; // Index3D class

/**
 * Check for Index3D equality.
 * @param {Object} rhs The other index to compare to.
 * @return {Boolean} True if both indices are equal.
 */
dwv.math.Index3D.prototype.equals = function (rhs) {
    return rhs !== null &&
        this.getI() === rhs.getI() &&
        this.getJ() === rhs.getJ() &&
        this.getK() === rhs.getK();
};

/**
 * Get a string representation of the Index3D.
 * @return {String} The Index3D as a string.
 */
dwv.math.Index3D.prototype.toString = function () {
    return "(" + this.getI() +
        ", " + this.getJ() +
        ", " + this.getK() + ")";
};

// namespaces
var dwv = dwv || {};
dwv.math = dwv.math || {};

// Pre-created to reduce allocation in inner loops
var __twothirdpi = ( 2 / (3 * Math.PI) );

/**
 *
 */
dwv.math.computeGreyscale = function(data, width, height) {
    // Returns 2D augmented array containing greyscale data
    // Greyscale values found by averaging colour channels
    // Input should be in a flat RGBA array, with values between 0 and 255
    var greyscale = [];

    // Compute actual values
    for (var y = 0; y < height; y++) {
        greyscale[y] = [];

        for (var x = 0; x < width; x++) {
            var p = (y*width + x)*4;
            greyscale[y][x] = (data[p] + data[p+1] + data[p+2]) / (3*255);
        }
    }

    // Augment with convenience functions
    greyscale.dx = function(x,y) {
        if ( x+1 === this[y].length ) {
            // If we're at the end, back up one
            x--;
        }
        return this[y][x+1] - this[y][x];
    };

    greyscale.dy = function(x,y) {
        if ( y+1 === this.length ) {
            // If we're at the end, back up one
            y--;
        }
        return this[y][x] - this[y+1][x];
    };

    greyscale.gradMagnitude = function(x,y) {
        var dx = this.dx(x,y);
        var dy = this.dy(x,y);
        return Math.sqrt(dx*dx + dy*dy);
    };

    greyscale.laplace = function(x,y) {
        // Laplacian of Gaussian
        var lap = -16 * this[y][x];
        lap += this[y-2][x];
        lap += this[y-1][x-1] + 2*this[y-1][x] + this[y-1][x+1];
        lap += this[y][x-2]   + 2*this[y][x-1] + 2*this[y][x+1] + this[y][x+2];
        lap += this[y+1][x-1] + 2*this[y+1][x] + this[y+1][x+1];
        lap += this[y+2][x];

        return lap;
    };

    return greyscale;
};

/**
 *
 */
dwv.math.computeGradient = function(greyscale) {
    // Returns a 2D array of gradient magnitude values for greyscale. The values
    // are scaled between 0 and 1, and then flipped, so that it works as a cost
    // function.
    var gradient = [];

    var max = 0; // Maximum gradient found, for scaling purposes

    var x = 0;
    var y = 0;

    for (y = 0; y < greyscale.length-1; y++) {
        gradient[y] = [];

        for (x = 0; x < greyscale[y].length-1; x++) {
            gradient[y][x] = greyscale.gradMagnitude(x,y);
            max = Math.max(gradient[y][x], max);
        }

        gradient[y][greyscale[y].length-1] = gradient[y][greyscale.length-2];
    }

    gradient[greyscale.length-1] = [];
    for (var i = 0; i < gradient[0].length; i++) {
        gradient[greyscale.length-1][i] = gradient[greyscale.length-2][i];
    }

    // Flip and scale.
    for (y = 0; y < gradient.length; y++) {
        for (x = 0; x < gradient[y].length; x++) {
            gradient[y][x] = 1 - (gradient[y][x] / max);
        }
    }

    return gradient;
};

/**
 *
 */
dwv.math.computeLaplace = function(greyscale) {
    // Returns a 2D array of Laplacian of Gaussian values
    var laplace = [];

    // Make the edges low cost here.

    laplace[0] = [];
    laplace[1] = [];
    for (var i = 1; i < greyscale.length; i++) {
        // Pad top, since we can't compute Laplacian
        laplace[0][i] = 1;
        laplace[1][i] = 1;
    }

    for (var y = 2; y < greyscale.length-2; y++) {
        laplace[y] = [];
        // Pad left, ditto
        laplace[y][0] = 1;
        laplace[y][1] = 1;

        for (var x = 2; x < greyscale[y].length-2; x++) {
            // Threshold needed to get rid of clutter.
            laplace[y][x] = (greyscale.laplace(x,y) > 0.33) ? 0 : 1;
        }

        // Pad right, ditto
        laplace[y][greyscale[y].length-2] = 1;
        laplace[y][greyscale[y].length-1] = 1;
    }

    laplace[greyscale.length-2] = [];
    laplace[greyscale.length-1] = [];
    for (var j = 1; j < greyscale.length; j++) {
        // Pad bottom, ditto
        laplace[greyscale.length-2][j] = 1;
        laplace[greyscale.length-1][j] = 1;
    }

    return laplace;
};

dwv.math.computeGradX = function(greyscale) {
    // Returns 2D array of x-gradient values for greyscale
    var gradX = [];

    for ( var y = 0; y < greyscale.length; y++ ) {
        gradX[y] = [];

        for ( var x = 0; x < greyscale[y].length-1; x++ ) {
            gradX[y][x] = greyscale.dx(x,y);
        }

        gradX[y][greyscale[y].length-1] = gradX[y][greyscale[y].length-2];
    }

    return gradX;
};

dwv.math.computeGradY = function(greyscale) {
    // Returns 2D array of y-gradient values for greyscale
    var gradY = [];

    for (var y = 0; y < greyscale.length-1; y++) {
        gradY[y] = [];

        for ( var x = 0; x < greyscale[y].length; x++ ) {
            gradY[y][x] = greyscale.dy(x,y);
        }
    }

    gradY[greyscale.length-1] = [];
    for ( var i = 0; i < greyscale[0].length; i++ ) {
        gradY[greyscale.length-1][i] = gradY[greyscale.length-2][i];
    }

    return gradY;
};

dwv.math.gradUnitVector = function(gradX, gradY, px, py, out) {
    // Returns the gradient vector at (px,py), scaled to a magnitude of 1
    var ox = gradX[py][px];
    var oy = gradY[py][px];

    var gvm = Math.sqrt(ox*ox + oy*oy);
    gvm = Math.max(gvm, 1e-100); // To avoid possible divide-by-0 errors

    out.x = ox / gvm;
    out.y = oy / gvm;
};

dwv.math.gradDirection = function(gradX, gradY, px, py, qx, qy) {
    var __dgpuv = new dwv.math.FastPoint2D(-1, -1);
    var __gdquv = new dwv.math.FastPoint2D(-1, -1);
    // Compute the gradiant direction, in radians, between to points
    dwv.math.gradUnitVector(gradX, gradY, px, py, __dgpuv);
    dwv.math.gradUnitVector(gradX, gradY, qx, qy, __gdquv);

    var dp = __dgpuv.y * (qx - px) - __dgpuv.x * (qy - py);
    var dq = __gdquv.y * (qx - px) - __gdquv.x * (qy - py);

    // Make sure dp is positive, to keep things consistant
    if (dp < 0) {
        dp = -dp;
        dq = -dq;
    }

    if ( px !== qx && py !== qy ) {
        // We're going diagonally between pixels
        dp *= Math.SQRT1_2;
        dq *= Math.SQRT1_2;
    }

    return __twothirdpi * (Math.acos(dp) + Math.acos(dq));
};

dwv.math.computeSides = function(dist, gradX, gradY, greyscale) {
    // Returns 2 2D arrays, containing inside and outside greyscale values.
    // These greyscale values are the intensity just a little bit along the
    // gradient vector, in either direction, from the supplied point. These
    // values are used when using active-learning Intelligent Scissors

    var sides = {};
    sides.inside = [];
    sides.outside = [];

    var guv = new dwv.math.FastPoint2D(-1, -1); // Current gradient unit vector

    for ( var y = 0; y < gradX.length; y++ ) {
        sides.inside[y] = [];
        sides.outside[y] = [];

        for ( var x = 0; x < gradX[y].length; x++ ) {
            dwv.math.gradUnitVector(gradX, gradY, x, y, guv);

            //(x, y) rotated 90 = (y, -x)

            var ix = Math.round(x + dist*guv.y);
            var iy = Math.round(y - dist*guv.x);
            var ox = Math.round(x - dist*guv.y);
            var oy = Math.round(y + dist*guv.x);

            ix = Math.max(Math.min(ix, gradX[y].length-1), 0);
            ox = Math.max(Math.min(ox, gradX[y].length-1), 0);
            iy = Math.max(Math.min(iy, gradX.length-1), 0);
            oy = Math.max(Math.min(oy, gradX.length-1), 0);

            sides.inside[y][x] = greyscale[iy][ix];
            sides.outside[y][x] = greyscale[oy][ox];
        }
    }

    return sides;
};

dwv.math.gaussianBlur = function(buffer, out) {
    // Smooth values over to fill in gaps in the mapping
    out[0] = 0.4*buffer[0] + 0.5*buffer[1] + 0.1*buffer[1];
    out[1] = 0.25*buffer[0] + 0.4*buffer[1] + 0.25*buffer[2] + 0.1*buffer[3];

    for ( var i = 2; i < buffer.length-2; i++ ) {
        out[i] = 0.05*buffer[i-2] + 0.25*buffer[i-1] + 0.4*buffer[i] + 0.25*buffer[i+1] + 0.05*buffer[i+2];
    }

    var len = buffer.length;
    out[len-2] = 0.25*buffer[len-1] + 0.4*buffer[len-2] + 0.25*buffer[len-3] + 0.1*buffer[len-4];
    out[len-1] = 0.4*buffer[len-1] + 0.5*buffer[len-2] + 0.1*buffer[len-3];
};


/**
 * Scissors
 *
 * Ref: Eric N. Mortensen, William A. Barrett, Interactive Segmentation with
 *   Intelligent Scissors, Graphical Models and Image Processing, Volume 60,
 *   Issue 5, September 1998, Pages 349-384, ISSN 1077-3169,
 *   DOI: 10.1006/gmip.1998.0480.
 *
 * {@link http://www.sciencedirect.com/science/article/B6WG4-45JB8WN-9/2/6fe59d8089fd1892c2bfb82283065579}
 *
 * Highly inspired from {@link http://code.google.com/p/livewire-javascript/}
 * @constructor
 */
dwv.math.Scissors = function()
{
    this.width = -1;
    this.height = -1;

    this.curPoint = null; // Corrent point we're searching on.
    this.searchGranBits = 8; // Bits of resolution for BucketQueue.
    this.searchGran = 1 << this.earchGranBits; //bits.
    this.pointsPerPost = 500;

    // Precomputed image data. All in ranges 0 >= x >= 1 and all inverted (1 - x).
    this.greyscale = null; // Greyscale of image
    this.laplace = null; // Laplace zero-crossings (either 0 or 1).
    this.gradient = null; // Gradient magnitudes.
    this.gradX = null; // X-differences.
    this.gradY = null; // Y-differences.

    this.parents = null; // Matrix mapping point => parent along shortest-path to root.

    this.working = false; // Currently computing shortest paths?

    // Begin Training:
    this.trained = false;
    this.trainingPoints = null;

    this.edgeWidth = 2;
    this.trainingLength = 32;

    this.edgeGran = 256;
    this.edgeTraining = null;

    this.gradPointsNeeded = 32;
    this.gradGran = 1024;
    this.gradTraining = null;

    this.insideGran = 256;
    this.insideTraining = null;

    this.outsideGran = 256;
    this.outsideTraining = null;
    // End Training
}; // Scissors class

// Begin training methods //
dwv.math.Scissors.prototype.getTrainingIdx = function(granularity, value) {
    return Math.round((granularity - 1) * value);
};

dwv.math.Scissors.prototype.getTrainedEdge = function(edge) {
    return this.edgeTraining[this.getTrainingIdx(this.edgeGran, edge)];
};

dwv.math.Scissors.prototype.getTrainedGrad = function(grad) {
    return this.gradTraining[this.getTrainingIdx(this.gradGran, grad)];
};

dwv.math.Scissors.prototype.getTrainedInside = function(inside) {
    return this.insideTraining[this.getTrainingIdx(this.insideGran, inside)];
};

dwv.math.Scissors.prototype.getTrainedOutside = function(outside) {
    return this.outsideTraining[this.getTrainingIdx(this.outsideGran, outside)];
};
// End training methods //

dwv.math.Scissors.prototype.setWorking = function(working) {
    // Sets working flag
    this.working = working;
};

dwv.math.Scissors.prototype.setDimensions = function(width, height) {
    this.width = width;
    this.height = height;
};

dwv.math.Scissors.prototype.setData = function(data) {
    if ( this.width === -1 || this.height === -1 ) {
        // The width and height should have already been set
        throw new Error("Dimensions have not been set.");
    }

    this.greyscale = dwv.math.computeGreyscale(data, this.width, this.height);
    this.laplace = dwv.math.computeLaplace(this.greyscale);
    this.gradient = dwv.math.computeGradient(this.greyscale);
    this.gradX = dwv.math.computeGradX(this.greyscale);
    this.gradY = dwv.math.computeGradY(this.greyscale);

    var sides = dwv.math.computeSides(this.edgeWidth, this.gradX, this.gradY, this.greyscale);
    this.inside = sides.inside;
    this.outside = sides.outside;
    this.edgeTraining = [];
    this.gradTraining = [];
    this.insideTraining = [];
    this.outsideTraining = [];
};

dwv.math.Scissors.prototype.findTrainingPoints = function(p) {
    // Grab the last handful of points for training
    var points = [];

    if ( this.parents !== null ) {
        for ( var i = 0; i < this.trainingLength && p; i++ ) {
            points.push(p);
            p = this.parents[p.y][p.x];
        }
    }

    return points;
};

dwv.math.Scissors.prototype.resetTraining = function() {
    this.trained = false; // Training is ignored with this flag set
};

dwv.math.Scissors.prototype.doTraining = function(p) {
    // Compute training weights and measures
    this.trainingPoints = this.findTrainingPoints(p);

    if ( this.trainingPoints.length < 8 ) {
        return; // Not enough points, I think. It might crash if length = 0.
    }

    var buffer = [];
    this.calculateTraining(buffer, this.edgeGran, this.greyscale, this.edgeTraining);
    this.calculateTraining(buffer, this.gradGran, this.gradient, this.gradTraining);
    this.calculateTraining(buffer, this.insideGran, this.inside, this.insideTraining);
    this.calculateTraining(buffer, this.outsideGran, this.outside, this.outsideTraining);

    if ( this.trainingPoints.length < this.gradPointsNeeded ) {
        // If we have two few training points, the gradient weight map might not
        // be smooth enough, so average with normal weights.
        this.addInStaticGrad(this.trainingPoints.length, this.gradPointsNeeded);
    }

    this.trained = true;
};

dwv.math.Scissors.prototype.calculateTraining = function(buffer, granularity, input, output) {
    var i = 0;
    // Build a map of raw-weights to trained-weights by favoring input values
    buffer.length = granularity;
    for ( i = 0; i < granularity; i++ ) {
        buffer[i] = 0;
    }

    var maxVal = 1;
    for ( i = 0; i < this.trainingPoints.length; i++ ) {
        var p = this.trainingPoints[i];
        var idx = this.getTrainingIdx(granularity, input[p.y][p.x]);
        buffer[idx] += 1;

        maxVal = Math.max(maxVal, buffer[idx]);
    }

    // Invert and scale.
    for ( i = 0; i < granularity; i++ ) {
        buffer[i] = 1 - buffer[i] / maxVal;
    }

    // Blur it, as suggested. Gets rid of static.
    dwv.math.gaussianBlur(buffer, output);
};

dwv.math.Scissors.prototype.addInStaticGrad = function(have, need) {
    // Average gradient raw-weights to trained-weights map with standard weight
    // map so that we don't end up with something to spiky
    for ( var i = 0; i < this.gradGran; i++ ) {
        this.gradTraining[i] = Math.min(this.gradTraining[i],  1 - i*(need - have)/(need*this.gradGran));
    }
};

dwv.math.Scissors.prototype.gradDirection = function(px, py, qx, qy) {
    return dwv.math.gradDirection(this.gradX, this.gradY, px, py, qx, qy);
};

dwv.math.Scissors.prototype.dist = function(px, py, qx, qy) {
    // The grand culmunation of most of the code: the weighted distance function
    var grad =  this.gradient[qy][qx];

    if ( px === qx || py === qy ) {
        // The distance is Euclidean-ish; non-diagonal edges should be shorter
        grad *= Math.SQRT1_2;
    }

    var lap = this.laplace[qy][qx];
    var dir = this.gradDirection(px, py, qx, qy);

    if ( this.trained ) {
        // Apply training magic
        var gradT = this.getTrainedGrad(grad);
        var edgeT = this.getTrainedEdge(this.greyscale[py][px]);
        var insideT = this.getTrainedInside(this.inside[py][px]);
        var outsideT = this.getTrainedOutside(this.outside[py][px]);

        return 0.3*gradT + 0.3*lap + 0.1*(dir + edgeT + insideT + outsideT);
    } else {
        // Normal weights
        return 0.43*grad + 0.43*lap + 0.11*dir;
    }
};

dwv.math.Scissors.prototype.adj = function(p) {
    var list = [];

    var sx = Math.max(p.x-1, 0);
    var sy = Math.max(p.y-1, 0);
    var ex = Math.min(p.x+1, this.greyscale[0].length-1);
    var ey = Math.min(p.y+1, this.greyscale.length-1);

    var idx = 0;
    for ( var y = sy; y <= ey; y++ ) {
        for ( var x = sx; x <= ex; x++ ) {
            if ( x !== p.x || y !== p.y ) {
                list[idx++] = new dwv.math.FastPoint2D(x,y);
            }
        }
    }

    return list;
};

dwv.math.Scissors.prototype.setPoint = function(sp) {
    this.setWorking(true);

    this.curPoint = sp;

    var x = 0;
    var y = 0;

    this.visited = [];
    for ( y = 0; y < this.height; y++ ) {
        this.visited[y] = [];
        for ( x = 0; x < this.width; x++ ) {
            this.visited[y][x] = false;
        }
    }

    this.parents = [];
    for ( y = 0; y < this.height; y++ ) {
        this.parents[y] = [];
    }

    this.cost = [];
    for ( y = 0; y < this.height; y++ ) {
        this.cost[y] = [];
        for ( x = 0; x < this.width; x++ ) {
            this.cost[y][x] = Number.MAX_VALUE;
        }
    }

    this.pq = new dwv.math.BucketQueue(this.searchGranBits, function(p) {
        return Math.round(this.searchGran * this.costArr[p.y][p.x]);
    });
    this.pq.searchGran = this.searchGran;
    this.pq.costArr = this.cost;

    this.pq.push(sp);
    this.cost[sp.y][sp.x] = 0;
};

dwv.math.Scissors.prototype.doWork = function() {
    if ( !this.working ) {
        return;
    }

    this.timeout = null;

    var pointCount = 0;
    var newPoints = [];
    while ( !this.pq.isEmpty() && pointCount < this.pointsPerPost ) {
        var p = this.pq.pop();
        newPoints.push(p);
        newPoints.push(this.parents[p.y][p.x]);

        this.visited[p.y][p.x] = true;

        var adjList = this.adj(p);
        for ( var i = 0; i < adjList.length; i++) {
            var q = adjList[i];

            var pqCost = this.cost[p.y][p.x] + this.dist(p.x, p.y, q.x, q.y);

            if ( pqCost < this.cost[q.y][q.x] ) {
                if ( this.cost[q.y][q.x] !== Number.MAX_VALUE ) {
                    // Already in PQ, must remove it so we can re-add it.
                    this.pq.remove(q);
                }

                this.cost[q.y][q.x] = pqCost;
                this.parents[q.y][q.x] = p;
                this.pq.push(q);
            }
        }

        pointCount++;
    }

    return newPoints;
};

// namespaces
var dwv = dwv || {};
dwv.math = dwv.math || {};

/**
 * Mulitply the three inputs if the last two are not null.
 * @param {Number} a The first input.
 * @param {Number} b The second input.
 * @param {Number} c The third input.
 * @return {Number} The multiplication of the three inputs or
 *  null if one of the last two is null.
 */
function mulABC( a, b, c) {
    var res = null;
    if (b !== null && c !== null) {
        res = a * b * c;
    }
    return res;
}

/**
 * Circle shape.
 * @constructor
 * @param {Object} centre A Point2D representing the centre of the circle.
 * @param {Number} radius The radius of the circle.
 */
dwv.math.Circle = function(centre, radius)
{
    /**
     * Circle surface.
     * @private
     * @type Number
     */
    var surface = Math.PI*radius*radius;

    /**
     * Get the centre (point) of the circle.
     * @return {Object} The center (point) of the circle.
     */
    this.getCenter = function() { return centre; };
    /**
     * Get the radius of the circle.
     * @return {Number} The radius of the circle.
     */
    this.getRadius = function() { return radius; };
    /**
     * Get the surface of the circle.
     * @return {Number} The surface of the circle.
     */
    this.getSurface = function() { return surface; };
    /**
     * Get the surface of the circle according to a spacing.
     * @param {Number} spacingX The X spacing.
     * @param {Number} spacingY The Y spacing.
     * @return {Number} The surface of the circle multiplied by the given
     *  spacing or null for null spacings.
     */
    this.getWorldSurface = function(spacingX, spacingY)
    {
        return mulABC(surface, spacingX, spacingY);
    };
}; // Circle class

/**
 * Ellipse shape.
 * @constructor
 * @param {Object} centre A Point2D representing the centre of the ellipse.
 * @param {Number} a The radius of the ellipse on the horizontal axe.
 * @param {Number} b The radius of the ellipse on the vertical axe.
 */
dwv.math.Ellipse = function(centre, a, b)
{
    /**
     * Circle surface.
     * @private
     * @type Number
     */
    var surface = Math.PI*a*b;

    /**
     * Get the centre (point) of the ellipse.
     * @return {Object} The center (point) of the ellipse.
     */
    this.getCenter = function() { return centre; };
    /**
     * Get the radius of the ellipse on the horizontal axe.
     * @return {Number} The radius of the ellipse on the horizontal axe.
     */
    this.getA = function() { return a; };
    /**
     * Get the radius of the ellipse on the vertical axe.
     * @return {Number} The radius of the ellipse on the vertical axe.
     */
    this.getB = function() { return b; };
    /**
     * Get the surface of the ellipse.
     * @return {Number} The surface of the ellipse.
     */
    this.getSurface = function() { return surface; };
    /**
     * Get the surface of the ellipse according to a spacing.
     * @param {Number} spacingX The X spacing.
     * @param {Number} spacingY The Y spacing.
     * @return {Number} The surface of the ellipse multiplied by the given
     *  spacing or null for null spacings.
     */
    this.getWorldSurface = function(spacingX, spacingY)
    {
        return mulABC(surface, spacingX, spacingY);
    };
}; // Circle class

/**
 * Line shape.
 * @constructor
 * @param {Object} begin A Point2D representing the beginning of the line.
 * @param {Object} end A Point2D representing the end of the line.
 */
dwv.math.Line = function(begin, end)
{
    /**
     * Line delta in the X direction.
     * @private
     * @type Number
     */
    var dx = end.getX() - begin.getX();
    /**
     * Line delta in the Y direction.
     * @private
     * @type Number
     */
    var dy = end.getY() - begin.getY();
    /**
     * Line length.
     * @private
     * @type Number
     */
    var length = Math.sqrt( dx * dx + dy * dy );

    /**
     * Get the begin point of the line.
     * @return {Object} The beginning point of the line.
     */
    this.getBegin = function() { return begin; };
    /**
     * Get the end point of the line.
     * @return {Object} The ending point of the line.
     */
    this.getEnd = function() { return end; };
    /**
     * Get the line delta in the X direction.
     * @return {Number} The delta in the X direction.
     */
    this.getDeltaX = function() { return dx; };
    /**
     * Get the line delta in the Y direction.
     * @return {Number} The delta in the Y direction.
     */
    this.getDeltaY = function() { return dy; };
    /**
     * Get the length of the line.
     * @return {Number} The length of the line.
     */
    this.getLength = function() { return length; };
    /**
     * Get the length of the line according to a  spacing.
     * @param {Number} spacingX The X spacing.
     * @param {Number} spacingY The Y spacing.
     * @return {Number} The length of the line with spacing
     *  or null for null spacings.
     */
    this.getWorldLength = function(spacingX, spacingY)
    {
        var wlen = null;
        if (spacingX !== null && spacingY !== null) {
            var dxs = dx * spacingX;
            var dys = dy * spacingY;
            wlen = Math.sqrt( dxs * dxs + dys * dys );
        }
        return wlen;
    };
    /**
     * Get the mid point of the line.
     * @return {Object} The mid point of the line.
     */
    this.getMidpoint = function()
    {
        return new dwv.math.Point2D(
            parseInt( (begin.getX()+end.getX()) / 2, 10 ),
            parseInt( (begin.getY()+end.getY()) / 2, 10 ) );
    };
    /**
     * Get the slope of the line.
     * @return {Number} The slope of the line.
     */
    this.getSlope = function()
    {
        return dy / dx;
    };
    /**
     * Get the intercept of the line.
     * @return {Number} The slope of the line.
     */
    this.getIntercept = function()
    {
        return (end.getX() * begin.getY() - begin.getX() * end.getY()) / dx;
    };
    /**
     * Get the inclination of the line.
     * @return {Number} The inclination of the line.
     */
    this.getInclination = function()
    {
        // tan(theta) = slope
        var angle = Math.atan2( dy, dx ) * 180 / Math.PI;
        // shift?
        return 180 - angle;
    };
}; // Line class

/**
 * Get the angle between two lines.
 * @param line0 The first line.
 * @param line1 The second line.
 */
dwv.math.getAngle = function (line0, line1)
{
    var dx0 = line0.getDeltaX();
    var dy0 = line0.getDeltaY();
    var dx1 = line1.getDeltaX();
    var dy1 = line1.getDeltaY();
    // dot = ||a||*||b||*cos(theta)
    var dot = dx0 * dx1 + dy0 * dy1;
    // cross = ||a||*||b||*sin(theta)
    var det = dx0 * dy1 - dy0 * dx1;
    // tan = sin / cos
    var angle = Math.atan2( det, dot ) * 180 / Math.PI;
    // complementary angle
    // shift?
    return 360 - (180 - angle);
};

/**
 * Get a perpendicular line to an input one.
 * @param {Object} line The line to be perpendicular to.
 * @param {Object} point The middle point of the perpendicular line.
 * @param {Number} length The length of the perpendicular line.
 */
dwv.math.getPerpendicularLine = function (line, point, length)
{
    // begin point
    var beginX = 0;
    var beginY = 0;
    // end point
    var endX = 0;
    var endY = 0;

    // check slope:
    // 0 -> horizontal
    // Infinite -> vertical (a/Infinite = 0)
    if ( line.getSlope() !== 0 ) {
        // a0 * a1 = -1
        var slope = -1 / line.getSlope();
        // y0 = a1*x0 + b1 -> b1 = y0 - a1*x0
        var intercept = point.getY() - slope * point.getX();

        // 1. (x - x0)^2 + (y - y0)^2 = d^2
        // 2. a = (y - y0) / (x - x0) -> y = a*(x - x0) + y0
        // ->  (x - x0)^2 + m^2 * (x - x0)^2 = d^2
        // -> x = x0 +- d / sqrt(1+m^2)

        // length is the distance between begin and end,
        // point is half way between both -> d = length / 2
        var dx = length / ( 2 * Math.sqrt( 1 + slope * slope ) );

        // begin point
        beginX = point.getX() - dx;
        beginY = slope * beginX + intercept;
        // end point
        endX = point.getX() + dx;
        endY = slope * endX + intercept;
    }
    else {
      // horizontal input line -> perpendicular is vertical!
      // begin point
      beginX = point.getX();
      beginY = point.getY() - length / 2;
      // end point
      endX = point.getX();
      endY = point.getY() + length / 2;
    }
    // perpendicalar line
    return new dwv.math.Line(
        new dwv.math.Point2D(beginX, beginY),
        new dwv.math.Point2D(endX, endY) );
};

/**
 * Rectangle shape.
 * @constructor
 * @param {Object} begin A Point2D representing the beginning of the rectangle.
 * @param {Object} end A Point2D representing the end of the rectangle.
 */
dwv.math.Rectangle = function(begin, end)
{
    if ( end.getX() < begin.getX() ) {
        var tmpX = begin.getX();
        begin = new dwv.math.Point2D( end.getX(), begin.getY() );
        end = new dwv.math.Point2D( tmpX, end.getY() );
    }
    if ( end.getY() < begin.getY() ) {
        var tmpY = begin.getY();
        begin = new dwv.math.Point2D( begin.getX(), end.getY() );
        end = new dwv.math.Point2D( end.getX(), tmpY );
    }

    /**
     * Rectangle surface.
     * @private
     * @type Number
     */
    var surface = Math.abs(end.getX() - begin.getX()) * Math.abs(end.getY() - begin.getY() );

    /**
     * Get the begin point of the rectangle.
     * @return {Object} The begin point of the rectangle
     */
    this.getBegin = function() { return begin; };
    /**
     * Get the end point of the rectangle.
     * @return {Object} The end point of the rectangle
     */
    this.getEnd = function() { return end; };
    /**
     * Get the real width of the rectangle.
     * @return {Number} The real width of the rectangle.
     */
    this.getRealWidth = function() { return end.getX() - begin.getX(); };
    /**
     * Get the real height of the rectangle.
     * @return {Number} The real height of the rectangle.
     */
    this.getRealHeight = function() { return end.getY() - begin.getY(); };
    /**
     * Get the width of the rectangle.
     * @return {Number} The width of the rectangle.
     */
    this.getWidth = function() { return Math.abs(this.getRealWidth()); };
    /**
     * Get the height of the rectangle.
     * @return {Number} The height of the rectangle.
     */
    this.getHeight = function() { return Math.abs(this.getRealHeight()); };
    /**
     * Get the surface of the rectangle.
     * @return {Number} The surface of the rectangle.
     */
    this.getSurface = function() { return surface; };
    /**
     * Get the surface of the circle according to a spacing.
     * @param {Number} spacingX The X spacing.
     * @param {Number} spacingY The Y spacing.
     * @return {Number} The surface of the rectangle multiplied by the given
     *  spacing or null for null spacings.
     */
    this.getWorldSurface = function(spacingX, spacingY)
    {
        return mulABC(surface, spacingX, spacingY);
    };
}; // Rectangle class

/**
 * Region Of Interest shape.
 * Note: should be a closed path.
 * @constructor
 */
dwv.math.ROI = function()
{
    /**
     * List of points.
     * @private
     * @type Array
     */
    var points = [];

    /**
     * Get a point of the list at a given index.
     * @param {Number} index The index of the point to get (beware, no size check).
     * @return {Object} The Point2D at the given index.
     */
    this.getPoint = function(index) { return points[index]; };
    /**
     * Get the length of the point list.
     * @return {Number} The length of the point list.
     */
    this.getLength = function() { return points.length; };
    /**
     * Add a point to the ROI.
     * @param {Object} point The Point2D to add.
     */
    this.addPoint = function(point) { points.push(point); };
    /**
     * Add points to the ROI.
     * @param {Array} rhs The array of POints2D to add.
     */
    this.addPoints = function(rhs) { points=points.concat(rhs);};
}; // ROI class

/**
 * Path shape.
 * @constructor
 * @param {Array} inputPointArray The list of Point2D that make the path (optional).
 * @param {Array} inputControlPointIndexArray The list of control point of path,
 *  as indexes (optional).
 * Note: first and last point do not need to be equal.
 */
dwv.math.Path = function(inputPointArray, inputControlPointIndexArray)
{
    /**
     * List of points.
     * @type Array
     */
    this.pointArray = inputPointArray ? inputPointArray.slice() : [];
    /**
     * List of control points.
     * @type Array
     */
    this.controlPointIndexArray = inputControlPointIndexArray ?
        inputControlPointIndexArray.slice() : [];
}; // Path class

/**
 * Get a point of the list.
 * @param {Number} index The index of the point to get (beware, no size check).
 * @return {Object} The Point2D at the given index.
 */
dwv.math.Path.prototype.getPoint = function(index) {
    return this.pointArray[index];
};

/**
 * Is the given point a control point.
 * @param {Object} point The Point2D to check.
 * @return {Boolean} True if a control point.
 */
dwv.math.Path.prototype.isControlPoint = function(point) {
    var index = this.pointArray.indexOf(point);
    if( index !== -1 ) {
        return this.controlPointIndexArray.indexOf(index) !== -1;
    }
    else {
        throw new Error("Error: isControlPoint called with not in list point.");
    }
};

/**
 * Get the length of the path.
 * @return {Number} The length of the path.
 */
dwv.math.Path.prototype.getLength = function() {
    return this.pointArray.length;
};

/**
 * Add a point to the path.
 * @param {Object} point The Point2D to add.
 */
dwv.math.Path.prototype.addPoint = function(point) {
    this.pointArray.push(point);
};

/**
 * Add a control point to the path.
 * @param {Object} point The Point2D to make a control point.
 */
dwv.math.Path.prototype.addControlPoint = function(point) {
    var index = this.pointArray.indexOf(point);
    if( index !== -1 ) {
        this.controlPointIndexArray.push(index);
    }
    else {
        throw new Error("Error: addControlPoint called with no point in list point.");
    }
};

/**
 * Add points to the path.
 * @param {Array} points The list of Point2D to add.
 */
dwv.math.Path.prototype.addPoints = function(newPointArray) {
    this.pointArray = this.pointArray.concat(newPointArray);
};

/**
 * Append a Path to this one.
 * @param {Path} other The Path to append.
 */
dwv.math.Path.prototype.appenPath = function(other) {
    var oldSize = this.pointArray.length;
    this.pointArray = this.pointArray.concat(other.pointArray);
    var indexArray = [];
    for( var i=0; i < other.controlPointIndexArray.length; ++i ) {
        indexArray[i] = other.controlPointIndexArray[i] + oldSize;
    }
    this.controlPointIndexArray = this.controlPointIndexArray.concat(indexArray);
};

// namespaces
var dwv = dwv || {};
dwv.math = dwv.math || {};

/**
 * Get the minimum, maximum, mean and standard deviation
 * of an array of values.
 * Note: could use {@link https://github.com/tmcw/simple-statistics}.
 */
dwv.math.getStats = function (array)
{
    var min = array[0];
    var max = min;
    var mean = 0;
    var sum = 0;
    var sumSqr = 0;
    var stdDev = 0;
    var variance = 0;

    var val = 0;
    for ( var i = 0; i < array.length; ++i ) {
        val = array[i];
        if ( val < min ) {
            min = val;
        }
        else if ( val > max ) {
            max = val;
        }
        sum += val;
        sumSqr += val * val;
    }

    mean = sum / array.length;
    // see http://en.wikipedia.org/wiki/Algorithms_for_calculating_variance
    variance = sumSqr / array.length - mean * mean;
    stdDev = Math.sqrt(variance);

    return { 'min': min, 'max': max, 'mean': mean, 'stdDev': stdDev };
};

/**
 * Unique ID generator.
 * See {@link http://stackoverflow.com/questions/105034/create-guid-uuid-in-javascript}
 * and this {@link http://stackoverflow.com/a/13403498 answer}.
 */
dwv.math.guid = function ()
{
    return Math.random().toString(36).substring(2, 15);
};

// namespaces
var dwv = dwv || {};
dwv.math = dwv.math || {};

/**
 * Immutable 3D vector.
 * @constructor
 * @param {Number} x The X component of the vector.
 * @param {Number} y The Y component of the vector.
 * @param {Number} z The Z component of the vector.
 */
dwv.math.Vector3D = function (x,y,z)
{
    /**
     * Get the X component of the vector.
     * @return {Number} The X component of the vector.
     */
    this.getX = function () { return x; };
    /**
     * Get the Y component of the vector.
     * @return {Number} The Y component of the vector.
     */
    this.getY = function () { return y; };
    /**
     * Get the Z component of the vector.
     * @return {Number} The Z component of the vector.
     */
    this.getZ = function () { return z; };
}; // Vector3D class

/**
 * Check for Vector3D equality.
 * @param {Object} rhs The other vector to compare to.
 * @return {Boolean} True if both vectors are equal.
 */
dwv.math.Vector3D.prototype.equals = function (rhs) {
    return rhs !== null &&
        this.getX() === rhs.getX() &&
        this.getY() === rhs.getY() &&
        this.getZ() === rhs.getZ();
};

/**
 * Get a string representation of the Vector3D.
 * @return {String} The vector as a string.
 */
dwv.math.Vector3D.prototype.toString = function () {
    return "(" + this.getX() +
        ", " + this.getY() +
        ", " + this.getZ() + ")";
};

/**
 * Get the norm of the vector.
  * @return {Number} The norm.
 */
dwv.math.Vector3D.prototype.norm = function () {
    return Math.sqrt( (this.getX() * this.getX()) +
        (this.getY() * this.getY()) +
        (this.getZ() * this.getZ()) );
};

/**
 * Get the cross product with another Vector3D, ie the
 * vector that is perpendicular to both a and b.
 * If both vectors are parallel, the cross product is a zero vector.
 * @param {Object} vector3D The input vector.
  * @return {Object} The result vector.
 */
dwv.math.Vector3D.prototype.crossProduct = function (vector3D) {
    return new dwv.math.Vector3D(
        (this.getY() * vector3D.getZ()) - (vector3D.getY() * this.getZ()),
        (this.getZ() * vector3D.getX()) - (vector3D.getZ() * this.getX()),
        (this.getX() * vector3D.getY()) - (vector3D.getX() * this.getY()) );
};

/**
 * Get the dot product with another Vector3D.
 * @param {Object} vector3D The input vector.
 * @return {Number} The dot product.
 */
dwv.math.Vector3D.prototype.dotProduct = function (vector3D) {
    return (this.getX() * vector3D.getX()) +
        (this.getY() * vector3D.getY()) +
        (this.getZ() * vector3D.getZ());
};

// namespaces
var dwv = dwv || {};
dwv.tool = dwv.tool || {};
// external
var Konva = Konva || {};

/**
 * Arrow factory.
 * @constructor
 * @external Konva
 */
dwv.tool.ArrowFactory = function ()
{
    /**
     * Get the number of points needed to build the shape.
     * @return {Number} The number of points.
     */
    this.getNPoints = function () { return 2; };
    /**
     * Get the timeout between point storage.
     * @return {Number} The timeout in milliseconds.
     */
    this.getTimeout = function () { return 0; };
};

/**
 * Create an arrow shape to be displayed.
 * @param {Array} points The points from which to extract the line.
 * @param {Object} style The drawing style.
 * @param {Object} image The associated image.
 */
dwv.tool.ArrowFactory.prototype.create = function (points, style/*, image*/)
{
    // physical shape
    var line = new dwv.math.Line(points[0], points[1]);
    // draw shape
    var kshape = new Konva.Line({
        points: [line.getBegin().getX(), line.getBegin().getY(),
                 line.getEnd().getX(), line.getEnd().getY() ],
        stroke: style.getLineColour(),
        strokeWidth: style.getScaledStrokeWidth(),
        name: "shape"
    });
    // triangle
    var beginTy = new dwv.math.Point2D(line.getBegin().getX(), line.getBegin().getY() - 10);
    var verticalLine = new dwv.math.Line(line.getBegin(), beginTy);
    var angle = dwv.math.getAngle(line, verticalLine);
    var angleRad = angle * Math.PI / 180;
    var radius = 5;
    var kpoly = new Konva.RegularPolygon({
        x: line.getBegin().getX() + radius * Math.sin(angleRad),
        y: line.getBegin().getY() + radius * Math.cos(angleRad),
        sides: 3,
        radius: radius,
        rotation: -angle,
        fill: style.getLineColour(),
        strokeWidth: style.getScaledStrokeWidth(),
        name: "shape-triangle"
    });
    // quantification
    var ktext = new Konva.Text({
        fontSize: style.getScaledFontSize(),
        fontFamily: style.getFontFamily(),
        fill: style.getLineColour(),
        name: "text"
    });
    ktext.textExpr = "";
    ktext.longText = "";
    ktext.quant = null;
    ktext.setText(ktext.textExpr);
    // label
    var dX = line.getBegin().getX() > line.getEnd().getX() ? 0 : -1;
    var dY = line.getBegin().getY() > line.getEnd().getY() ? -1 : 0.5;
    var klabel = new Konva.Label({
        x: line.getEnd().getX() + dX * 25,
        y: line.getEnd().getY() + dY * 15,
        name: "label"
    });
    klabel.add(ktext);
    klabel.add(new Konva.Tag());

    // return group
    var group = new Konva.Group();
    group.name("line-group");
    group.add(kshape);
    group.add(kpoly);
    group.add(klabel);
    group.visible(true); // dont inherit
    return group;
};

/**
 * Update an arrow shape.
 * @param {Object} anchor The active anchor.
 * @param {Object} image The associated image.
 */
dwv.tool.UpdateArrow = function (anchor/*, image*/)
{
    // parent group
    var group = anchor.getParent();
    // associated shape
    var kline = group.getChildren( function (node) {
        return node.name() === 'shape';
    })[0];
    // associated triangle shape
    var ktriangle = group.getChildren( function (node) {
        return node.name() === 'shape-triangle';
    })[0];
    // associated label
    var klabel = group.getChildren( function (node) {
        return node.name() === 'label';
    })[0];
    // find special points
    var begin = group.getChildren( function (node) {
        return node.id() === 'begin';
    })[0];
    var end = group.getChildren( function (node) {
        return node.id() === 'end';
    })[0];
    // update special points
    switch ( anchor.id() ) {
    case 'begin':
        begin.x( anchor.x() );
        begin.y( anchor.y() );
        break;
    case 'end':
        end.x( anchor.x() );
        end.y( anchor.y() );
        break;
    }
    // update shape and compensate for possible drag
    // note: shape.position() and shape.size() won't work...
    var bx = begin.x() - kline.x();
    var by = begin.y() - kline.y();
    var ex = end.x() - kline.x();
    var ey = end.y() - kline.y();
    kline.points( [bx,by,ex,ey] );
    // new line
    var p2d0 = new dwv.math.Point2D(begin.x(), begin.y());
    var p2d1 = new dwv.math.Point2D(end.x(), end.y());
    var line = new dwv.math.Line(p2d0, p2d1);
    // udate triangle
    var beginTy = new dwv.math.Point2D(line.getBegin().getX(), line.getBegin().getY() - 10);
    var verticalLine = new dwv.math.Line(line.getBegin(), beginTy);
    var angle = dwv.math.getAngle(line, verticalLine);
    var angleRad = angle * Math.PI / 180;
    ktriangle.x(line.getBegin().getX() + ktriangle.radius() * Math.sin(angleRad));
    ktriangle.y(line.getBegin().getY() + ktriangle.radius() * Math.cos(angleRad));
    ktriangle.rotation(-angle);
    // update text
    var ktext = klabel.getText();
    ktext.quant = null;
    ktext.setText(ktext.textExpr);
    // update position
    var dX = line.getBegin().getX() > line.getEnd().getX() ? 0 : -1;
    var dY = line.getBegin().getY() > line.getEnd().getY() ? -1 : 0.5;
    var textPos = {
        'x': line.getEnd().getX() + dX * 25,
        'y': line.getEnd().getY() + dY * 15 };
    klabel.position( textPos );
};

// namespaces
var dwv = dwv || {};
/** @namespace */
dwv.tool = dwv.tool || {};
// external
var Konva = Konva || {};

/**
 * Drawing tool.
 * @constructor
 * @param {Object} app The associated application.
 * @external Konva
 */
dwv.tool.Draw = function (app, shapeFactoryList)
{
    /**
     * Closure to self: to be used by event handlers.
     * @private
     * @type WindowLevel
     */
    var self = this;
    /**
     * Draw GUI.
     * @type Object
     */
    var gui = null;
    /**
     * Interaction start flag.
     * @private
     * @type Boolean
     */
    var started = false;

    /**
     * Shape factory list
     * @type Object
     */
    this.shapeFactoryList = shapeFactoryList;
    /**
     * Draw command.
     * @private
     * @type Object
     */
    var command = null;
    /**
     * Current shape group.
     * @private
     * @type Object
     */
    var shapeGroup = null;

    /**
     * Shape name.
     * @type String
     */
    this.shapeName = 0;

    /**
     * List of points.
     * @private
     * @type Array
     */
    var points = [];

    /**
     * Last selected point.
     * @private
     * @type Object
     */
    var lastPoint = null;

    /**
     * Shape editor.
     * @private
     * @type Object
     */
    var shapeEditor = new dwv.tool.ShapeEditor(app);

    // associate the event listeners of the editor
    //  with those of the draw tool
    shapeEditor.setDrawEventCallback(fireEvent);

    /**
     * Trash draw: a cross.
     * @private
     * @type Object
     */
    var trash = new Konva.Group();

    // first line of the cross
    var trashLine1 = new Konva.Line({
        points: [-10, -10, 10, 10 ],
        stroke: 'red'
    });
    // second line of the cross
    var trashLine2 = new Konva.Line({
        points: [10, -10, -10, 10 ],
        stroke: 'red'
    });
    trash.add(trashLine1);
    trash.add(trashLine2);

    /**
     * Drawing style.
     * @type Style
     */
    this.style = new dwv.html.Style();

    /**
     * Event listeners.
     * @private
     */
    var listeners = {};

    /**
     * The associated draw layer.
     * @private
     * @type Object
     */
    var drawLayer = null;

    /**
     * Handle mouse down event.
     * @param {Object} event The mouse down event.
     */
    this.mousedown = function(event){
        // determine if the click happened in an existing shape
        var stage = app.getDrawStage();
        var kshape = stage.getIntersection({
            x: event._xs,
            y: event._ys
        });

        if ( kshape ) {
            var group = kshape.getParent();
            var selectedShape = group.find(".shape")[0];
            // reset editor if click on other shape
            // (and avoid anchors mouse down)
            if ( selectedShape && selectedShape !== shapeEditor.getShape() ) {
                shapeEditor.disable();
                shapeEditor.setShape(selectedShape);
                shapeEditor.setImage(app.getImage());
                shapeEditor.enable();
            }
        }
        else {
            // disable edition
            shapeEditor.disable();
            shapeEditor.setShape(null);
            shapeEditor.setImage(null);
            // start storing points
            started = true;
            // clear array
            points = [];
            // store point
            lastPoint = new dwv.math.Point2D(event._x, event._y);
            points.push(lastPoint);
        }
    };

    /**
     * Handle mouse move event.
     * @param {Object} event The mouse move event.
     */
    this.mousemove = function(event){
        if (!started)
        {
            return;
        }
        if ( Math.abs( event._x - lastPoint.getX() ) > 0 ||
                Math.abs( event._y - lastPoint.getY() ) > 0 )
        {
            // current point
            lastPoint = new dwv.math.Point2D(event._x, event._y);
            // clear last added point from the list (but not the first one)
            if ( points.length != 1 ) {
                points.pop();
            }
            // add current one to the list
            points.push( lastPoint );
            // allow for anchor points
            var factory = new self.shapeFactoryList[self.shapeName]();
            if( points.length < factory.getNPoints() ) {
                clearTimeout(this.timer);
                this.timer = setTimeout( function () {
                    points.push( lastPoint );
                }, factory.getTimeout() );
            }
            // remove previous draw
            if ( shapeGroup ) {
                shapeGroup.destroy();
            }
            // create shape group
            shapeGroup = factory.create(points, self.style, app.getImage());
            // do not listen during creation
            var shape = shapeGroup.getChildren( function (node) {
                return node.name() === 'shape';
            })[0];
            shape.listening(false);
            drawLayer.hitGraphEnabled(false);
            // draw shape command
            command = new dwv.tool.DrawGroupCommand(shapeGroup, self.shapeName, drawLayer, true);
            // draw
            command.execute();
        }
    };

    /**
     * Handle mouse up event.
     * @param {Object} event The mouse up event.
     */
    this.mouseup = function (/*event*/){
        if (started && points.length > 1 )
        {
            // reset shape group
            if ( shapeGroup ) {
                shapeGroup.destroy();
            }
            // create final shape
            var factory = new self.shapeFactoryList[self.shapeName]();
            var group = factory.create(points, self.style, app.getImage());
            group.id( dwv.math.guid() );
            // re-activate layer
            drawLayer.hitGraphEnabled(true);
            // draw shape command
            command = new dwv.tool.DrawGroupCommand(group, self.shapeName, drawLayer);
            command.onExecute = fireEvent;
            command.onUndo = fireEvent;
            // execute it
            command.execute();
            // save it in undo stack
            app.addToUndoStack(command);

            // set shape on
            var shape = group.getChildren( function (node) {
                return node.name() === 'shape';
            })[0];
            self.setShapeOn( shape );
        }
        // reset flag
        started = false;
    };

    /**
     * Handle mouse out event.
     * @param {Object} event The mouse out event.
     */
    this.mouseout = function(event){
        self.mouseup(event);
    };

    /**
     * Handle touch start event.
     * @param {Object} event The touch start event.
     */
    this.touchstart = function(event){
        self.mousedown(event);
    };

    /**
     * Handle touch move event.
     * @param {Object} event The touch move event.
     */
    this.touchmove = function(event){
        self.mousemove(event);
    };

    /**
     * Handle touch end event.
     * @param {Object} event The touch end event.
     */
    this.touchend = function(event){
        self.mouseup(event);
    };

    /**
     * Handle key down event.
     * @param {Object} event The key down event.
     */
    this.keydown = function(event){
        app.onKeydown(event);
    };

    /**
     * Setup the tool GUI.
     */
    this.setup = function ()
    {
        gui = new dwv.gui.Draw(app);
        gui.setup(this.shapeFactoryList);
    };

    /**
     * Enable the tool.
     * @param {Boolean} flag The flag to enable or not.
     */
    this.display = function ( flag ){
        if ( gui ) {
            gui.display( flag );
        }
        // reset shape display properties
        shapeEditor.disable();
        shapeEditor.setShape(null);
        shapeEditor.setImage(null);
        document.body.style.cursor = 'default';
        // make layer listen or not to events
        app.getDrawStage().listening( flag );
        // get the current draw layer
        drawLayer = app.getCurrentDrawLayer();
        renderDrawLayer(flag);
        // listen to app change to update the draw layer
        if (flag) {
            app.addEventListener("slice-change", updateDrawLayer);
            app.addEventListener("frame-change", updateDrawLayer);
        }
        else {
            app.removeEventListener("slice-change", updateDrawLayer);
            app.removeEventListener("frame-change", updateDrawLayer);
        }
    };

    /**
     * Get the current app draw layer.
     */
    function updateDrawLayer() {
        // deactivate the old draw layer
        renderDrawLayer(false);
        // get the current draw layer
        drawLayer = app.getCurrentDrawLayer();
        // activate the new draw layer
        renderDrawLayer(true);
    }

    /**
     * Render (or not) the draw layer.
     * @param {Boolean} visible Set the draw layer visible or not.
     */
    function renderDrawLayer(visible) {
        drawLayer.listening( visible );
        drawLayer.hitGraphEnabled( visible );
        // get the list of shapes
        var groups = drawLayer.getChildren();
        var shapes = [];
        var fshape = function (node) {
            return node.name() === 'shape';
        };
        for ( var i = 0; i < groups.length; ++i ) {
            // should only be one shape per group
            shapes.push( groups[i].getChildren(fshape)[0] );
        }
        // set shape display properties
        if ( visible ) {
            app.addToolCanvasListeners( app.getDrawStage().getContent() );
            shapes.forEach( function (shape){ self.setShapeOn( shape ); });
        }
        else {
            app.removeToolCanvasListeners( app.getDrawStage().getContent() );
            shapes.forEach( function (shape){ setShapeOff( shape ); });
        }
        // draw
        drawLayer.draw();
    }

    /**
     * Set shape off properties.
     * @param {Object} shape The shape to set off.
     */
    function setShapeOff( shape ) {
        // mouse styling
        shape.off('mouseover');
        shape.off('mouseout');
        // drag
        shape.draggable(false);
        shape.off('dragstart');
        shape.off('dragmove');
        shape.off('dragend');
        shape.off('dblclick');
    }

    /**
     * Get the real position from an event.
     */
    function getRealPosition( index ) {
        var stage = app.getDrawStage();
        return { 'x': stage.offset().x + index.x / stage.scale().x,
            'y': stage.offset().y + index.y / stage.scale().y };
    }

    /**
     * Set shape on properties.
     * @param {Object} shape The shape to set on.
     */
    this.setShapeOn = function ( shape ) {
        // mouse over styling
        shape.on('mouseover', function () {
            document.body.style.cursor = 'pointer';
        });
        // mouse out styling
        shape.on('mouseout', function () {
            document.body.style.cursor = 'default';
        });

        // make it draggable
        shape.draggable(true);
        var dragStartPos = null;
        var dragLastPos = null;

        // command name based on shape type
        var shapeDisplayName = dwv.tool.GetShapeDisplayName(shape);

        // store original colour
        var colour = null;

        // drag start event handling
        shape.on('dragstart', function (event) {
            // save start position
            var offset = dwv.html.getEventOffset( event.evt )[0];
            dragStartPos = getRealPosition( offset );
            // colour
            colour = shape.stroke();
            // display trash
            var stage = app.getDrawStage();
            var scale = stage.scale();
            var invscale = {'x': 1/scale.x, 'y': 1/scale.y};
            trash.x( stage.offset().x + ( 256 / scale.x ) );
            trash.y( stage.offset().y + ( 20 / scale.y ) );
            trash.scale( invscale );
            drawLayer.add( trash );
            // deactivate anchors to avoid events on null shape
            shapeEditor.setAnchorsActive(false);
            // draw
            drawLayer.draw();
        });
        // drag move event handling
        shape.on('dragmove', function (event) {
            var offset = dwv.html.getEventOffset( event.evt )[0];
            var pos = getRealPosition( offset );
            var translation;
            if ( dragLastPos ) {
                translation = {'x': pos.x - dragLastPos.x,
                    'y': pos.y - dragLastPos.y};
            }
            else {
                translation = {'x': pos.x - dragStartPos.x,
                        'y': pos.y - dragStartPos.y};
            }
            dragLastPos = pos;
            // highlight trash when on it
            if ( Math.abs( pos.x - trash.x() ) < 10 &&
                    Math.abs( pos.y - trash.y() ) < 10   ) {
                trash.getChildren().each( function (tshape){ tshape.stroke('orange'); });
                shape.stroke('red');
            }
            else {
                trash.getChildren().each( function (tshape){ tshape.stroke('red'); });
                shape.stroke(colour);
            }
            // update group but not 'this' shape
            var group = this.getParent();
            group.getChildren().each( function (shape) {
                if ( shape == this ) {
                    return;
                }
                shape.x( shape.x() + translation.x );
                shape.y( shape.y() + translation.y );
            });
            // reset anchors
            shapeEditor.resetAnchors();
            // draw
            drawLayer.draw();
        });
        // drag end event handling
        shape.on('dragend', function (/*event*/) {
            var pos = dragLastPos;
            dragLastPos = null;
            // remove trash
            trash.remove();
            // delete case
            if ( Math.abs( pos.x - trash.x() ) < 10 &&
                    Math.abs( pos.y - trash.y() ) < 10   ) {
                // compensate for the drag translation
                var delTranslation = {'x': pos.x - dragStartPos.x,
                        'y': pos.y - dragStartPos.y};
                var group = this.getParent();
                group.getChildren().each( function (shape) {
                    shape.x( shape.x() - delTranslation.x );
                    shape.y( shape.y() - delTranslation.y );
                });
                // disable editor
                shapeEditor.disable();
                shapeEditor.setShape(null);
                shapeEditor.setImage(null);
                // reset
                shape.stroke(colour);
                document.body.style.cursor = 'default';
                // delete command
                var delcmd = new dwv.tool.DeleteGroupCommand(this.getParent(),
                    shapeDisplayName, drawLayer);
                delcmd.onExecute = fireEvent;
                delcmd.onUndo = fireEvent;
                delcmd.execute();
                app.addToUndoStack(delcmd);
            }
            else {
                // save drag move
                var translation = {'x': pos.x - dragStartPos.x,
                        'y': pos.y - dragStartPos.y};
                if ( translation.x !== 0 || translation.y !== 0 ) {
                    var mvcmd = new dwv.tool.MoveGroupCommand(this.getParent(),
                        shapeDisplayName, translation, drawLayer);
                    mvcmd.onExecute = fireEvent;
                    mvcmd.onUndo = fireEvent;
                    app.addToUndoStack(mvcmd);
                    // the move is handled by Konva, trigger an event manually
                    fireEvent({'type': 'draw-move'});
                }
                // reset anchors
                shapeEditor.setAnchorsActive(true);
                shapeEditor.resetAnchors();
            }
            // draw
            drawLayer.draw();
        });
        // double click handling: update label
        shape.on('dblclick', function () {

            // get the label object for this shape
            var group = this.getParent();
            var labels = group.find('Label');
            // should just be one
            if (labels.length !== 1) {
                throw new Error("Could not find the shape label.");
            }
            var ktext = labels[0].getText();

            // ask user for new label
            var labelText = dwv.gui.prompt("Shape label", ktext.textExpr);

            // if press cancel do nothing
            if (labelText === null) {
                return;
            }
            else if (labelText === ktext.textExpr) {
                return;
            }
            // update text expression and set text
            ktext.textExpr = labelText;
            ktext.setText(dwv.utils.replaceFlags(ktext.textExpr, ktext.quant));

            // trigger event
            fireEvent({'type': 'draw-change'});

            // draw
            drawLayer.draw();
        });
    };

    /**
     * Initialise the tool.
     */
    this.init = function() {
        // set the default to the first in the list
        var shapeName = 0;
        for( var key in this.shapeFactoryList ){
            shapeName = key;
            break;
        }
        this.setShapeName(shapeName);
        // init gui
        if ( gui ) {
            // init with the app window scale
            this.style.setScale(app.getWindowScale());
            // same for colour
            this.setLineColour(this.style.getLineColour());
            // init html
            gui.initialise();
        }
        return true;
    };

    /**
     * Add an event listener on the app.
     * @param {String} type The event type.
     * @param {Object} listener The method associated with the provided event type.
     */
    this.addEventListener = function (type, listener)
    {
        if ( typeof listeners[type] === "undefined" ) {
            listeners[type] = [];
        }
        listeners[type].push(listener);
    };

    /**
     * Remove an event listener from the app.
     * @param {String} type The event type.
     * @param {Object} listener The method associated with the provided event type.
     */
    this.removeEventListener = function (type, listener)
    {
        if( typeof listeners[type] === "undefined" ) {
            return;
        }
        for ( var i = 0; i < listeners[type].length; ++i )
        {
            if ( listeners[type][i] === listener ) {
                listeners[type].splice(i,1);
            }
        }
    };

    /**
     * Set the line colour of the drawing.
     * @param {String} colour The colour to set.
     */
    this.setLineColour = function (colour)
    {
        this.style.setLineColour(colour);
    };

    // Private Methods -----------------------------------------------------------

    /**
     * Fire an event: call all associated listeners.
     * @param {Object} event The event to fire.
     */
    function fireEvent (event)
    {
        if ( typeof listeners[event.type] === "undefined" ) {
            return;
        }
        for ( var i=0; i < listeners[event.type].length; ++i )
        {
            listeners[event.type][i](event);
        }
    }

}; // Draw class

/**
 * Help for this tool.
 * @return {Object} The help content.
 */
dwv.tool.Draw.prototype.getHelp = function()
{
    return {
        "title": dwv.i18n("tool.Draw.name"),
        "brief": dwv.i18n("tool.Draw.brief"),
        "mouse": {
            "mouse_drag": dwv.i18n("tool.Draw.mouse_drag")
        },
        "touch": {
            "touch_drag": dwv.i18n("tool.Draw.touch_drag")
        }
    };
};

/**
 * Set the shape name of the drawing.
 * @param {String} name The name of the shape.
 */
dwv.tool.Draw.prototype.setShapeName = function(name)
{
    // check if we have it
    if( !this.hasShape(name) )
    {
        throw new Error("Unknown shape: '" + name + "'");
    }
    this.shapeName = name;
};

/**
 * Check if the shape is in the shape list.
 * @param {String} name The name of the shape.
 */
dwv.tool.Draw.prototype.hasShape = function(name) {
    return this.shapeFactoryList[name];
};

// namespaces
var dwv = dwv || {};
/** @namespace */
dwv.tool = dwv.tool || {};
// external
var Konva = Konva || {};

/**
 * Get the display name of the input shape.
 * @param {Object} shape The Konva shape.
 * @return {String} The display name.
 * @external Konva
 */
dwv.tool.GetShapeDisplayName = function (shape)
{
    var displayName = "shape";
    if ( shape instanceof Konva.Line ) {
        if ( shape.points().length === 4 ) {
            displayName = "line";
        }
        else if ( shape.points().length === 6 ) {
            displayName = "protractor";
        }
        else {
            displayName = "roi";
        }
    }
    else if ( shape instanceof Konva.Rect ) {
        displayName = "rectangle";
    }
    else if ( shape instanceof Konva.Ellipse ) {
        displayName = "ellipse";
    }
    // return
    return displayName;
};

/**
 * Draw group command.
 * @param {Object} group The group draw.
 * @param {String} name The shape display name.
 * @param {Object} layer The layer where to draw the group.
 * @param {Object} silent Whether to send a creation event or not.
 * @constructor
 */
dwv.tool.DrawGroupCommand = function (group, name, layer, silent)
{
    var isSilent = (typeof silent === "undefined") ? false : true;

    /**
     * Get the command name.
     * @return {String} The command name.
     */
    this.getName = function () { return "Draw-"+name; };
    /**
     * Execute the command.
     */
    this.execute = function () {
        // add the group to the layer
        layer.add(group);
        // draw
        layer.draw();
        // callback
        if (!isSilent) {
            this.onExecute({'type': 'draw-create', 'id': group.id()});
        }
    };
    /**
     * Undo the command.
     */
    this.undo = function () {
        // remove the group from the parent layer
        group.remove();
        // draw
        layer.draw();
        // callback
        this.onUndo({'type': 'draw-delete', 'id': group.id()});
    };
}; // DrawGroupCommand class

/**
 * Handle an execute event.
 * @param {Object} event The execute event with type and id.
 */
dwv.tool.DrawGroupCommand.prototype.onExecute = function (/*event*/)
{
    // default does nothing.
};
/**
 * Handle an undo event.
 * @param {Object} event The undo event with type and id.
 */
dwv.tool.DrawGroupCommand.prototype.onUndo = function (/*event*/)
{
    // default does nothing.
};

/**
 * Move group command.
 * @param {Object} group The group draw.
 * @param {String} name The shape display name.
 * @param {Object} translation A 2D translation to move the group by.
 * @param {Object} layer The layer where to move the group.
 * @constructor
 */
dwv.tool.MoveGroupCommand = function (group, name, translation, layer)
{
    /**
     * Get the command name.
     * @return {String} The command name.
     */
    this.getName = function () { return "Move-"+name; };

    /**
     * Execute the command.
     */
    this.execute = function () {
        // translate all children of group
        group.getChildren().each( function (shape) {
            shape.x( shape.x() + translation.x );
            shape.y( shape.y() + translation.y );
        });
        // draw
        layer.draw();
        // callback
        this.onExecute({'type': 'draw-move', 'id': group.id()});
    };
    /**
     * Undo the command.
     */
    this.undo = function () {
        // invert translate all children of group
        group.getChildren().each( function (shape) {
            shape.x( shape.x() - translation.x );
            shape.y( shape.y() - translation.y );
        });
        // draw
        layer.draw();
        // callback
        this.onUndo({'type': 'draw-move', 'id': group.id()});
    };
}; // MoveGroupCommand class

/**
 * Handle an execute event.
 * @param {Object} event The execute event with type and id.
 */
dwv.tool.MoveGroupCommand.prototype.onExecute = function (/*event*/)
{
    // default does nothing.
};
/**
 * Handle an undo event.
 * @param {Object} event The undo event with type and id.
 */
dwv.tool.MoveGroupCommand.prototype.onUndo = function (/*event*/)
{
    // default does nothing.
};

/**
 * Change group command.
 * @param {String} name The shape display name.
 * @param {Object} func The change function.
 * @param {Object} startAnchor The anchor that starts the change.
 * @param {Object} endAnchor The anchor that ends the change.
 * @param {Object} layer The layer where to change the group.
 * @param {Object} image The associated image.
 * @constructor
 */
dwv.tool.ChangeGroupCommand = function (name, func, startAnchor, endAnchor, layer, image)
{
    /**
     * Get the command name.
     * @return {String} The command name.
     */
    this.getName = function () { return "Change-"+name; };

    /**
     * Execute the command.
     */
    this.execute = function () {
        // change shape
        func( endAnchor, image );
        // draw
        layer.draw();
        // callback
        this.onExecute({'type': 'draw-change'});
    };
    /**
     * Undo the command.
     */
    this.undo = function () {
        // invert change shape
        func( startAnchor, image );
        // draw
        layer.draw();
        // callback
        this.onUndo({'type': 'draw-change'});
    };
}; // ChangeGroupCommand class

/**
 * Handle an execute event.
 * @param {Object} event The execute event with type and id.
 */
dwv.tool.ChangeGroupCommand.prototype.onExecute = function (/*event*/)
{
    // default does nothing.
};
/**
 * Handle an undo event.
 * @param {Object} event The undo event with type and id.
 */
dwv.tool.ChangeGroupCommand.prototype.onUndo = function (/*event*/)
{
    // default does nothing.
};

/**
 * Delete group command.
 * @param {Object} group The group draw.
 * @param {String} name The shape display name.
 * @param {Object} layer The layer where to delete the group.
 * @constructor
 */
dwv.tool.DeleteGroupCommand = function (group, name, layer)
{
    /**
     * Get the command name.
     * @return {String} The command name.
     */
    this.getName = function () { return "Delete-"+name; };
    /**
     * Execute the command.
     */
    this.execute = function () {
        // remove the group from the parent layer
        group.remove();
        // draw
        layer.draw();
        // callback
        this.onExecute({'type': 'draw-delete', 'id': group.id()});
    };
    /**
     * Undo the command.
     */
    this.undo = function () {
        // add the group to the layer
        layer.add(group);
        // draw
        layer.draw();
        // callback
        this.onUndo({'type': 'draw-create', 'id': group.id()});
    };
}; // DeleteGroupCommand class

/**
 * Handle an execute event.
 * @param {Object} event The execute event with type and id.
 */
dwv.tool.DeleteGroupCommand.prototype.onExecute = function (/*event*/)
{
    // default does nothing.
};
/**
 * Handle an undo event.
 * @param {Object} event The undo event with type and id.
 */
dwv.tool.DeleteGroupCommand.prototype.onUndo = function (/*event*/)
{
    // default does nothing.
};

// namespaces
var dwv = dwv || {};
dwv.tool = dwv.tool || {};
// external
var Konva = Konva || {};

/**
 * Shape editor.
 * @constructor
 * @external Konva
 */
dwv.tool.ShapeEditor = function (app)
{
    /**
     * Edited shape.
     * @private
     * @type Object
     */
    var shape = null;
    /**
     * Edited image. Used for quantification update.
     * @private
     * @type Object
     */
    var image = null;
    /**
     * Active flag.
     * @private
     * @type Boolean
     */
    var isActive = false;
    /**
     * Update function used by anchors to update the shape.
     * @private
     * @type Function
     */
    var updateFunction = null;
    /**
     * Draw event callback.
     * @private
     * @type Function
     */
    var drawEventCallback = null;

    /**
     * Set the shape to edit.
     * @param {Object} inshape The shape to edit.
     */
    this.setShape = function ( inshape ) {
        shape = inshape;
        // reset anchors
        if ( shape ) {
            removeAnchors();
            addAnchors();
        }
    };

    /**
     * Set the associated image.
     * @param {Object} img The associated image.
     */
    this.setImage = function ( img ) {
        image = img;
    };

    /**
     * Get the edited shape.
     * @return {Object} The edited shape.
     */
    this.getShape = function () {
        return shape;
    };

    /**
     * Get the active flag.
     * @return {Boolean} The active flag.
     */
    this.isActive = function () {
        return isActive;
    };

    /**
     * Set the draw event callback.
     * @param {Object} callback The callback.
     */
    this.setDrawEventCallback = function ( callback ) {
        drawEventCallback = callback;
    };

    /**
     * Enable the editor. Redraws the layer.
     */
    this.enable = function () {
        isActive = true;
        if ( shape ) {
            setAnchorsVisible( true );
            if ( shape.getLayer() ) {
                shape.getLayer().draw();
            }
        }
    };

    /**
     * Disable the editor. Redraws the layer.
     */
    this.disable = function () {
        isActive = false;
        if ( shape ) {
            setAnchorsVisible( false );
            if ( shape.getLayer() ) {
                shape.getLayer().draw();
            }
        }
    };

    /**
     * Reset the anchors.
     */
    this.resetAnchors = function () {
        // remove previous controls
        removeAnchors();
        // add anchors
        addAnchors();
        // set them visible
        setAnchorsVisible( true );
    };

    /**
     * Apply a function on all anchors.
     * @param {Object} func A f(shape) function.
     */
    function applyFuncToAnchors( func ) {
        if ( shape && shape.getParent() ) {
            var anchors = shape.getParent().find('.anchor');
            anchors.each( func );
        }
    }

    /**
     * Set anchors visibility.
     * @param {Boolean} flag The visible flag.
     */
    function setAnchorsVisible( flag ) {
        applyFuncToAnchors( function (anchor) {
            anchor.visible( flag );
        });
    }

    /**
     * Set anchors active.
     * @param {Boolean} flag The active (on/off) flag.
     */
    this.setAnchorsActive = function ( flag ) {
        var func = null;
        if ( flag ) {
            func = function (anchor) {
                setAnchorOn( anchor );
            };
        }
        else {
            func = function (anchor) {
                setAnchorOff( anchor );
            };
        }
        applyFuncToAnchors( func );
    };

    /**
     * Remove anchors.
     */
    function removeAnchors() {
        applyFuncToAnchors( function (anchor) {
            anchor.remove();
        });
    }

    /**
     * Add the shape anchors.
     */
    function addAnchors() {
        // exit if no shape or no layer
        if ( !shape || !shape.getLayer() ) {
            return;
        }
        // get shape group
        var group = shape.getParent();
        // add shape specific anchors to the shape group
        if ( shape instanceof Konva.Line ) {
            var points = shape.points();
            if ( points.length === 4 || points.length === 6) {
                // add shape offset
                var p0x = points[0] + shape.x();
                var p0y = points[1] + shape.y();
                var p1x = points[2] + shape.x();
                var p1y = points[3] + shape.y();
                addAnchor(group, p0x, p0y, 'begin');
                if ( points.length === 4 ) {
                    var shapekids = group.getChildren( function ( node ) {
                        return node.name().startsWith("shape-");
                    });
                    if (shapekids.length === 2) {
                        updateFunction = dwv.tool.UpdateRuler;
                    } else {
                        updateFunction = dwv.tool.UpdateArrow;
                    }
                    addAnchor(group, p1x, p1y, 'end');
                }
                else {
                    updateFunction = dwv.tool.UpdateProtractor;
                    addAnchor(group, p1x, p1y, 'mid');
                    var p2x = points[4] + shape.x();
                    var p2y = points[5] + shape.y();
                    addAnchor(group, p2x, p2y, 'end');
                }
            }
            else {
                updateFunction = dwv.tool.UpdateRoi;
                var px = 0;
                var py = 0;
                for ( var i = 0; i < points.length; i=i+2 ) {
                    px = points[i] + shape.x();
                    py = points[i+1] + shape.y();
                    addAnchor(group, px, py, i);
                }
            }
        }
        else if ( shape instanceof Konva.Rect ) {
            updateFunction = dwv.tool.UpdateRect;
            var rectX = shape.x();
            var rectY = shape.y();
            var rectWidth = shape.width();
            var rectHeight = shape.height();
            addAnchor(group, rectX, rectY, 'topLeft');
            addAnchor(group, rectX+rectWidth, rectY, 'topRight');
            addAnchor(group, rectX+rectWidth, rectY+rectHeight, 'bottomRight');
            addAnchor(group, rectX, rectY+rectHeight, 'bottomLeft');
        }
        else if ( shape instanceof Konva.Ellipse ) {
            updateFunction = dwv.tool.UpdateEllipse;
            var ellipseX = shape.x();
            var ellipseY = shape.y();
            var radius = shape.radius();
            addAnchor(group, ellipseX-radius.x, ellipseY-radius.y, 'topLeft');
            addAnchor(group, ellipseX+radius.x, ellipseY-radius.y, 'topRight');
            addAnchor(group, ellipseX+radius.x, ellipseY+radius.y, 'bottomRight');
            addAnchor(group, ellipseX-radius.x, ellipseY+radius.y, 'bottomLeft');
        }
        // add group to layer
        shape.getLayer().add( group );
    }

    /**
     * Create shape editor controls, i.e. the anchors.
     * @param {Object} group The group associated with this anchor.
     * @param {Number} x The X position of the anchor.
     * @param {Number} y The Y position of the anchor.
     * @param {Number} id The id of the anchor.
     */
    function addAnchor(group, x, y, id) {
        // anchor shape
        var anchor = new Konva.Circle({
            x: x,
            y: y,
            stroke: '#999',
            fill: 'rgba(100,100,100,0.7',
            strokeWidth: app.getStyle().getScaledStrokeWidth() / app.getScale(),
            radius: app.getStyle().scale(6) / app.getScale(),
            name: 'anchor',
            id: id,
            dragOnTop: false,
            draggable: true,
            visible: false
        });
        // set anchor on
        setAnchorOn( anchor );
        // add the anchor to the group
        group.add(anchor);
    }

    /**
     * Get a simple clone of the input anchor.
     * @param {Object} anchor The anchor to clone.
     */
    function getClone( anchor ) {
        // create closure to properties
        var parent = anchor.getParent();
        var id = anchor.id();
        var x = anchor.x();
        var y = anchor.y();
        // create clone object
        var clone = {};
        clone.getParent = function () {
            return parent;
        };
        clone.id = function () {
            return id;
        };
        clone.x = function () {
            return x;
        };
        clone.y = function () {
            return y;
        };
        return clone;
    }

    /**
     * Set the anchor on listeners.
     * @param {Object} anchor The anchor to set on.
     */
    function setAnchorOn( anchor ) {
        var startAnchor = null;

        // command name based on shape type
        var shapeDisplayName = dwv.tool.GetShapeDisplayName(shape);

        // drag start listener
        anchor.on('dragstart', function () {
            startAnchor = getClone(this);
        });
        // drag move listener
        anchor.on('dragmove', function () {
            if ( updateFunction ) {
                updateFunction(this, image);
            }
            if ( this.getLayer() ) {
                this.getLayer().draw();
            }
            else {
                console.warn("No layer to draw the anchor!");
            }
        });
        // drag end listener
        anchor.on('dragend', function () {
            var endAnchor = getClone(this);
            // store the change command
            var chgcmd = new dwv.tool.ChangeGroupCommand(
                    shapeDisplayName, updateFunction, startAnchor, endAnchor, this.getLayer(), image);
            chgcmd.onExecute = drawEventCallback;
            chgcmd.onUndo = drawEventCallback;
            chgcmd.execute();
            app.addToUndoStack(chgcmd);
            // reset start anchor
            startAnchor = endAnchor;
        });
        // mouse down listener
        anchor.on('mousedown touchstart', function () {
            this.moveToTop();
        });
        // mouse over styling
        anchor.on('mouseover', function () {
            document.body.style.cursor = 'pointer';
            this.stroke('#ddd');
            if ( this.getLayer() ) {
                this.getLayer().draw();
            }
            else {
                console.warn("No layer to draw the anchor!");
            }
        });
        // mouse out styling
        anchor.on('mouseout', function () {
            document.body.style.cursor = 'default';
            this.stroke('#999');
            if ( this.getLayer() ) {
                this.getLayer().draw();
            }
            else {
                console.warn("No layer to draw the anchor!");
            }
        });
    }

    /**
     * Set the anchor off listeners.
     * @param {Object} anchor The anchor to set off.
     */
    function setAnchorOff( anchor ) {
        anchor.off('dragstart');
        anchor.off('dragmove');
        anchor.off('dragend');
        anchor.off('mousedown touchstart');
        anchor.off('mouseover');
        anchor.off('mouseout');
    }
};

// namespaces
var dwv = dwv || {};
dwv.tool = dwv.tool || {};
// external
var Konva = Konva || {};

/**
 * Ellipse factory.
 * @constructor
 * @external Konva
 */
dwv.tool.EllipseFactory = function ()
{
    /**
     * Get the number of points needed to build the shape.
     * @return {Number} The number of points.
     */
    this.getNPoints = function () { return 2; };
    /**
     * Get the timeout between point storage.
     * @return {Number} The timeout in milliseconds.
     */
    this.getTimeout = function () { return 0; };
};

/**
 * Create an ellipse shape to be displayed.
 * @param {Array} points The points from which to extract the ellipse.
 * @param {Object} style The drawing style.
 * @param {Object} image The associated image.
 */
dwv.tool.EllipseFactory.prototype.create = function (points, style, image)
{
    // calculate radius
    var a = Math.abs(points[0].getX() - points[1].getX());
    var b = Math.abs(points[0].getY() - points[1].getY());
    // physical shape
    var ellipse = new dwv.math.Ellipse(points[0], a, b);
    // draw shape
    var kshape = new Konva.Ellipse({
        x: ellipse.getCenter().getX(),
        y: ellipse.getCenter().getY(),
        radius: { x: ellipse.getA(), y: ellipse.getB() },
        stroke: style.getLineColour(),
        strokeWidth: style.getScaledStrokeWidth(),
        name: "shape"
    });
    // quantification
    var quant = image.quantifyEllipse( ellipse );
    var ktext = new Konva.Text({
        fontSize: style.getScaledFontSize(),
        fontFamily: style.getFontFamily(),
        fill: style.getLineColour(),
        name: "text"
    });
    ktext.textExpr = "{surface}";
    ktext.longText = "";
    ktext.quant = quant;
    ktext.setText(dwv.utils.replaceFlags(ktext.textExpr, ktext.quant));
    // label
    var klabel = new Konva.Label({
        x: ellipse.getCenter().getX(),
        y: ellipse.getCenter().getY(),
        name: "label"
    });
    klabel.add(ktext);
    klabel.add(new Konva.Tag());

    // return group
    var group = new Konva.Group();
    group.name("ellipse-group");
    group.add(kshape);
    group.add(klabel);
    group.visible(true); // dont inherit
    return group;
};

/**
 * Update an ellipse shape.
 * @param {Object} anchor The active anchor.
 * @param {Object} image The associated image.
 */
dwv.tool.UpdateEllipse = function (anchor, image)
{
    // parent group
    var group = anchor.getParent();
    // associated shape
    var kellipse = group.getChildren( function (node) {
        return node.name() === 'shape';
    })[0];
    // associated label
    var klabel = group.getChildren( function (node) {
        return node.name() === 'label';
    })[0];
    // find special points
    var topLeft = group.getChildren( function (node) {
        return node.id() === 'topLeft';
    })[0];
    var topRight = group.getChildren( function (node) {
        return node.id() === 'topRight';
    })[0];
    var bottomRight = group.getChildren( function (node) {
        return node.id() === 'bottomRight';
    })[0];
    var bottomLeft = group.getChildren( function (node) {
        return node.id() === 'bottomLeft';
    })[0];
    // update 'self' (undo case) and special points
    switch ( anchor.id() ) {
    case 'topLeft':
        topLeft.x( anchor.x() );
        topLeft.y( anchor.y() );
        topRight.y( anchor.y() );
        bottomLeft.x( anchor.x() );
        break;
    case 'topRight':
        topRight.x( anchor.x() );
        topRight.y( anchor.y() );
        topLeft.y( anchor.y() );
        bottomRight.x( anchor.x() );
        break;
    case 'bottomRight':
        bottomRight.x( anchor.x() );
        bottomRight.y( anchor.y() );
        bottomLeft.y( anchor.y() );
        topRight.x( anchor.x() );
        break;
    case 'bottomLeft':
        bottomLeft.x( anchor.x() );
        bottomLeft.y( anchor.y() );
        bottomRight.y( anchor.y() );
        topLeft.x( anchor.x() );
        break;
    default :
        console.error('Unhandled anchor id: '+anchor.id());
        break;
    }
    // update shape
    var radiusX = ( topRight.x() - topLeft.x() ) / 2;
    var radiusY = ( bottomRight.y() - topRight.y() ) / 2;
    var center = { 'x': topLeft.x() + radiusX, 'y': topRight.y() + radiusY };
    kellipse.position( center );
    var radiusAbs = { 'x': Math.abs(radiusX), 'y': Math.abs(radiusY) };
    if ( radiusAbs ) {
        kellipse.radius( radiusAbs );
    }
    // new ellipse
    var ellipse = new dwv.math.Ellipse(center, radiusAbs.x, radiusAbs.y);
    // update text
    var quant = image.quantifyEllipse( ellipse );
    var ktext = klabel.getText();
    ktext.quant = quant;
    ktext.setText(dwv.utils.replaceFlags(ktext.textExpr, ktext.quant));
    // update position
    var textPos = { 'x': center.x, 'y': center.y };
    klabel.position( textPos );
};

// namespaces
var dwv = dwv || {};
dwv.tool = dwv.tool || {};
/** @namespace */
dwv.tool.filter = dwv.tool.filter || {};

/**
 * Filter tool.
 * @constructor
 * @param {Array} filterList The list of filter objects.
 * @param {Object} app The associated app.
 */
dwv.tool.Filter = function ( filterList, app )
{
    /**
     * Filter GUI.
     * @type Object
     */
    var gui = null;
    /**
     * Filter list
     * @type Object
     */
    this.filterList = filterList;
    /**
     * Selected filter.
     * @type Object
     */
    this.selectedFilter = 0;
    /**
     * Default filter name.
     * @type String
     */
    this.defaultFilterName = 0;
    /**
     * Display Flag.
     * @type Boolean
     */
    this.displayed = false;
    /**
     * Listener handler.
     * @type Object
     */
    var listenerHandler = new dwv.utils.ListenerHandler();

    /**
     * Setup the filter GUI. Called at app startup.
     */
    this.setup = function ()
    {
        if ( Object.keys(this.filterList).length !== 0 ) {
            gui = new dwv.gui.Filter(app);
            gui.setup(this.filterList);
            for( var key in this.filterList ){
                this.filterList[key].setup();
                this.filterList[key].addEventListener("filter-run", fireEvent);
                this.filterList[key].addEventListener("filter-undo", fireEvent);
            }
        }
    };

    /**
     * Display the tool.
     * @param {Boolean} bool Flag to enable or not.
     */
    this.display = function (bool)
    {
        if ( gui ) {
            gui.display(bool);
        }
        this.displayed = bool;
        // display the selected filter
        this.selectedFilter.display(bool);
    };

    /**
     * Initialise the filter. Called once the image is loaded.
     */
    this.init = function ()
    {
        // set the default to the first in the list
        for( var key in this.filterList ){
            this.defaultFilterName = key;
            break;
        }
        this.setSelectedFilter(this.defaultFilterName);
        // init all filters
        for( key in this.filterList ) {
            this.filterList[key].init();
        }
        // init html
        if ( gui ) {
            gui.initialise();
        }
        return true;
    };

    /**
     * Handle keydown event.
     * @param {Object} event The keydown event.
     */
    this.keydown = function (event)
    {
        app.onKeydown(event);
    };

    /**
     * Add an event listener to this class.
     * @param {String} type The event type.
     * @param {Object} callback The method associated with the provided event type,
     *    will be called with the fired event.
     */
    this.addEventListener = function (type, callback) {
        listenerHandler.add(type, callback);
    };
    /**
     * Remove an event listener from this class.
     * @param {String} type The event type.
     * @param {Object} callback The method associated with the provided event type.
     */
    this.removeEventListener = function (type, callback) {
        listenerHandler.remove(type, callback);
    };
    /**
     * Fire an event: call all associated listeners with the input event object.
     * @param {Object} event The event to fire.
     * @private
     */
    function fireEvent (event) {
        listenerHandler.fireEvent(event);
    }

}; // class dwv.tool.Filter

/**
 * Help for this tool.
 * @return {Object} The help content.
 */
dwv.tool.Filter.prototype.getHelp = function ()
{
    return {
        "title": dwv.i18n("tool.Filter.name"),
        "brief": dwv.i18n("tool.Filter.brief")
    };
};

/**
 * Get the selected filter.
 * @return {Object} The selected filter.
 */
dwv.tool.Filter.prototype.getSelectedFilter = function ()
{
    return this.selectedFilter;
};

/**
 * Set the selected filter.
 * @return {String} The name of the filter to select.
 */
dwv.tool.Filter.prototype.setSelectedFilter = function (name)
{
    // check if we have it
    if ( !this.hasFilter(name) )
    {
        throw new Error("Unknown filter: '" + name + "'");
    }
    // hide last selected
    if ( this.displayed )
    {
        this.selectedFilter.display(false);
    }
    // enable new one
    this.selectedFilter = this.filterList[name];
    // display the selected filter
    if ( this.displayed )
    {
        this.selectedFilter.display(true);
    }
};

/**
 * Get the list of filters.
 * @return {Array} The list of filter objects.
 */
dwv.tool.Filter.prototype.getFilterList = function ()
{
    return this.filterList;
};

/**
 * Check if a filter is in the filter list.
 * @param {String} name The name to check.
 * @return {String} The filter list element for the given name.
 */
dwv.tool.Filter.prototype.hasFilter = function (name)
{
    return this.filterList[name];
};

/**
 * Threshold filter tool.
 * @constructor
 * @param {Object} app The associated application.
 */
dwv.tool.filter.Threshold = function ( app )
{
    /**
     * Associated filter.
     * @type Object
     */
    var filter = new dwv.image.filter.Threshold();
    /**
     * Filter GUI.
     * @type Object
     */
    var gui = new dwv.gui.Threshold(app);
    /**
     * Flag to know wether to reset the image or not.
     * @type Boolean
     */
    var resetImage = true;
    /**
     * Listener handler.
     * @type Object
     */
    var listenerHandler = new dwv.utils.ListenerHandler();

    /**
     * Setup the filter GUI. Called at app startup.
     */
    this.setup = function ()
    {
        gui.setup();
    };

    /**
     * Display the filter.
     * @param {Boolean} bool Flag to display or not.
     */
    this.display = function (bool)
    {
        gui.display(bool);
        // reset the image when the tool is displayed
        if ( bool ) {
            resetImage = true;
        }
    };

    /**
     * Initialise the filter. Called once the image is loaded.
     */
    this.init = function ()
    {
        gui.initialise();
    };

    /**
     * Run the filter.
     * @param {Mixed} args The filter arguments.
     */
    this.run = function (args)
    {
        filter.setMin(args.min);
        filter.setMax(args.max);
        // reset the image if asked
        if ( resetImage ) {
            filter.setOriginalImage(app.getImage());
            resetImage = false;
        }
        var command = new dwv.tool.RunFilterCommand(filter, app);
        command.onExecute = fireEvent;
        command.onUndo = fireEvent;
        command.execute();
        // save command in undo stack
        app.addToUndoStack(command);
    };

    /**
     * Add an event listener to this class.
     * @param {String} type The event type.
     * @param {Object} callback The method associated with the provided event type,
     *    will be called with the fired event.
     */
    this.addEventListener = function (type, callback) {
        listenerHandler.add(type, callback);
    };
    /**
     * Remove an event listener from this class.
     * @param {String} type The event type.
     * @param {Object} callback The method associated with the provided event type.
     */
    this.removeEventListener = function (type, callback) {
        listenerHandler.remove(type, callback);
    };
    /**
     * Fire an event: call all associated listeners with the input event object.
     * @param {Object} event The event to fire.
     * @private
     */
    function fireEvent (event) {
        listenerHandler.fireEvent(event);
    }

}; // class dwv.tool.filter.Threshold


/**
 * Sharpen filter tool.
 * @constructor
 * @param {Object} app The associated application.
 */
dwv.tool.filter.Sharpen = function ( app )
{
    /**
     * Filter GUI.
     * @type Object
     */
    var gui = new dwv.gui.Sharpen(app);
    /**
     * Listener handler.
     * @type Object
     */
    var listenerHandler = new dwv.utils.ListenerHandler();

    /**
     * Setup the filter GUI. Called at app startup.
     */
    this.setup = function ()
    {
        gui.setup();
    };

    /**
     * Display the filter.
     * @param {Boolean} bool Flag to enable or not.
     */
    this.display = function (bool)
    {
        gui.display(bool);
    };

    /**
     * Initialise the filter. Called once the image is loaded.
     */
    this.init = function ()
    {
        // nothing to do...
    };

    /**
     * Run the filter.
     * @param {Mixed} args The filter arguments.
     */
    this.run = function (/*args*/)
    {
        var filter = new dwv.image.filter.Sharpen();
        filter.setOriginalImage(app.getImage());
        var command = new dwv.tool.RunFilterCommand(filter, app);
        command.onExecute = fireEvent;
        command.onUndo = fireEvent;
        command.execute();
        // save command in undo stack
        app.addToUndoStack(command);
    };

    /**
     * Add an event listener to this class.
     * @param {String} type The event type.
     * @param {Object} callback The method associated with the provided event type,
     *    will be called with the fired event.
     */
    this.addEventListener = function (type, callback) {
        listenerHandler.add(type, callback);
    };
    /**
     * Remove an event listener from this class.
     * @param {String} type The event type.
     * @param {Object} callback The method associated with the provided event type.
     */
    this.removeEventListener = function (type, callback) {
        listenerHandler.remove(type, callback);
    };
    /**
     * Fire an event: call all associated listeners with the input event object.
     * @param {Object} event The event to fire.
     * @private
     */
    function fireEvent (event) {
        listenerHandler.fireEvent(event);
    }

}; // dwv.tool.filter.Sharpen

/**
 * Sobel filter tool.
 * @constructor
 * @param {Object} app The associated application.
 */
dwv.tool.filter.Sobel = function ( app )
{
    /**
     * Filter GUI.
     * @type Object
     */
    var gui = new dwv.gui.Sobel(app);
    /**
     * Listener handler.
     * @type Object
     */
    var listenerHandler = new dwv.utils.ListenerHandler();

    /**
     * Setup the filter GUI. Called at app startup.
     */
    this.setup = function ()
    {
        gui.setup();
    };

    /**
     * Enable the filter.
     * @param {Boolean} bool Flag to enable or not.
     */
    this.display = function (bool)
    {
        gui.display(bool);
    };

    /**
     * Initialise the filter. Called once the image is loaded.
     */
    this.init = function ()
    {
        // nothing to do...
    };

    /**
     * Run the filter.
     * @param {Mixed} args The filter arguments.
     */
    dwv.tool.filter.Sobel.prototype.run = function (/*args*/)
    {
        var filter = new dwv.image.filter.Sobel();
        filter.setOriginalImage(app.getImage());
        var command = new dwv.tool.RunFilterCommand(filter, app);
        command.onExecute = fireEvent;
        command.onUndo = fireEvent;
        command.execute();
        // save command in undo stack
        app.addToUndoStack(command);
    };

    /**
     * Add an event listener to this class.
     * @param {String} type The event type.
     * @param {Object} callback The method associated with the provided event type,
     *    will be called with the fired event.
     */
    this.addEventListener = function (type, callback) {
        listenerHandler.add(type, callback);
    };
    /**
     * Remove an event listener from this class.
     * @param {String} type The event type.
     * @param {Object} callback The method associated with the provided event type.
     */
    this.removeEventListener = function (type, callback) {
        listenerHandler.remove(type, callback);
    };
    /**
     * Fire an event: call all associated listeners with the input event object.
     * @param {Object} event The event to fire.
     * @private
     */
    function fireEvent (event) {
        listenerHandler.fireEvent(event);
    }

}; // class dwv.tool.filter.Sobel

/**
 * Run filter command.
 * @constructor
 * @param {Object} filter The filter to run.
 * @param {Object} app The associated application.
 */
dwv.tool.RunFilterCommand = function (filter, app) {

    /**
     * Get the command name.
     * @return {String} The command name.
     */
    this.getName = function () { return "Filter-" + filter.getName(); };

    /**
     * Execute the command.
     */
    this.execute = function ()
    {
        // run filter and set app image
        app.setImage(filter.update());
        // update display
        app.render();
        // callback
        this.onExecute({'type': 'filter-run', 'id': this.getName()});
    };

    /**
     * Undo the command.
     */
    this.undo = function () {
        // reset the image
        app.setImage(filter.getOriginalImage());
        // update display
        app.render();
        // callback
        this.onUndo({'type': 'filter-undo', 'id': this.getName()});
    };

}; // RunFilterCommand class

/**
 * Handle an execute event.
 * @param {Object} event The execute event with type and id.
 */
dwv.tool.RunFilterCommand.prototype.onExecute = function (/*event*/)
{
    // default does nothing.
};
/**
 * Handle an undo event.
 * @param {Object} event The undo event with type and id.
 */
dwv.tool.RunFilterCommand.prototype.onUndo = function (/*event*/)
{
    // default does nothing.
};

// namespaces
var dwv = dwv || {};
dwv.tool = dwv.tool || {};
// external
var MagicWand = MagicWand || {};

/**
 * Floodfill painting tool.
 * @constructor
 * @param {Object} app The associated application.
 * @external MagicWand
 * @see {@link  https://github.com/Tamersoul/magic-wand-js}
 */
dwv.tool.Floodfill = function(app)
{
    /**
     * Original variables from external library. Used as in the lib example.
     * @private
     * @type Number
     */
    var blurRadius = 5;
    /**
     * Original variables from external library. Used as in the lib example.
     * @private
     * @type Number
     */
    var simplifyTolerant = 0;
    /**
     * Original variables from external library. Used as in the lib example.
     * @private
     * @type Number
     */
    var simplifyCount = 2000;
    /**
     * Canvas info
     * @private
     * @type Object
     */
    var imageInfo = null;
    /**
     * Object created by MagicWand lib containing border points
     * @private
     * @type Object
     */
    var mask = null;
    /**
     * threshold default tolerance of the tool border
     * @private
     * @type Number
     */
    var initialthreshold = 10;
    /**
     * threshold tolerance of the tool border
     * @private
     * @type Number
     */
    var currentthreshold = null;
    /**
     * Closure to self: to be used by event handlers.
     * @private
     * @type WindowLevel
     */
    var self = this;
    /**
     * Interaction start flag.
     * @type Boolean
     */
    this.started = false;
    /**
     * Livewire GUI.
     * @type Object
     */
    var gui = null;
    /**
     * Draw command.
     * @private
     * @type Object
     */
    var command = null;
    /**
     * Current shape group.
     * @private
     * @type Object
     */
    var shapeGroup = null;
    /**
     * Coordinates of the fist mousedown event.
     * @private
     * @type Object
     */
    var initialpoint;
    /**
     * Floodfill border.
     * @private
     * @type Object
     */
    var border = null;
    /**
     * List of parent points.
     * @private
     * @type Array
     */
    var parentPoints = [];
    /**
     * Assistant variable to paint border on all slices.
     * @private
     * @type Boolean
     */
    var extender = false;
    /**
     * Timeout for painting on mousemove.
     * @private
     */
    var painterTimeout;
    /**
     * Drawing style.
     * @type Style
     */
    this.style = new dwv.html.Style();

    /**
     * Event listeners.
     * @private
     */
    var listeners = [];

    /**
     * Set extend option for painting border on all slices.
     * @param {Boolean} The option to set
     */
    this.setExtend = function(Bool){
        extender = Bool;
    };

    /**
     * Get extend option for painting border on all slices.
     * @return {Boolean} The actual value of of the variable to use Floodfill on museup.
     */
    this.getExtend = function(){
        return extender;
    };

    /**
     * Get (x, y) coordinates referenced to the canvas
     * @param {Object} event The original event.
     */
    var getCoord = function(event){
        return { x: event._x, y: event._y };
    };

    /**
     * Calculate border.
     * @private
     * @param {Object} Start point.
     * @param {Number} Threshold tolerance.
     */
    var calcBorder = function(points, threshold, simple){

        parentPoints = [];
        var image = {
            data: imageInfo.data,
            width: imageInfo.width,
            height: imageInfo.height,
            bytes: 4
        };

        // var p = new dwv.math.FastPoint2D(points.x, points.y);
        mask = MagicWand.floodFill(image, points.x, points.y, threshold);
        mask = MagicWand.gaussBlurOnlyBorder(mask, blurRadius);

        var cs = MagicWand.traceContours(mask);
        cs = MagicWand.simplifyContours(cs, simplifyTolerant, simplifyCount);

        if(cs.length > 0 && cs[0].points[0].x){
            if(simple){
                return cs[0].points;
            }
            for(var j=0, icsl=cs[0].points.length; j<icsl; j++){
                parentPoints.push(new dwv.math.Point2D(cs[0].points[j].x, cs[0].points[j].y));
            }
            return parentPoints;
        }
        else{
            return false;
        }
    };

    /**
     * Paint Floodfill.
     * @private
     * @param {Object} Start point.
     * @param {Number} Threshold tolerance.
     */
    var paintBorder = function(point, threshold){
        // Calculate the border
        border = calcBorder(point, threshold);
        // Paint the border
        if(border){
            var factory = new dwv.tool.RoiFactory();
            shapeGroup = factory.create(border, self.style);
            shapeGroup.id( dwv.math.guid() );
            // draw shape command
            command = new dwv.tool.DrawGroupCommand(shapeGroup, "floodfill", app.getCurrentDrawLayer());
            command.onExecute = fireEvent;
            command.onUndo = fireEvent;
            // // draw
            command.execute();
            // save it in undo stack
            app.addToUndoStack(command);

            return true;
        }
        else{
            return false;
        }
    };

    /**
     * Create Floodfill in all the prev and next slices while border is found
     */
    this.extend = function(ini, end){
        //avoid errors
        if(!initialpoint){
            throw "'initialpoint' not found. User must click before use extend!";
        }
        // remove previous draw
        if ( shapeGroup ) {
            shapeGroup.destroy();
        }

        var pos = app.getViewController().getCurrentPosition();
        var threshold = currentthreshold || initialthreshold;

        // Iterate over the next images and paint border on each slice.
        for(var i=pos.k, len = end ? end : app.getImage().getGeometry().getSize().getNumberOfSlices(); i<len ; i++){
            if(!paintBorder(initialpoint, threshold)){
                break;
            }
            app.getViewController().incrementSliceNb();
        }
        app.getViewController().setCurrentPosition(pos);

        // Iterate over the prev images and paint border on each slice.
        for(var j=pos.k, jl = ini ? ini : 0 ; j>jl ; j--){
            if(!paintBorder(initialpoint, threshold)){
                break;
            }
            app.getViewController().decrementSliceNb();
        }
        app.getViewController().setCurrentPosition(pos);
    };

    /**
     * Modify tolerance threshold and redraw ROI.
     * @param {Number} New threshold.
     */
    this.modifyThreshold = function(modifyThreshold, shape){

        if(!shape && shapeGroup){
            shape = shapeGroup.getChildren( function (node) {
                return node.name() === 'shape';
            })[0];
        }
        else{
            throw 'No shape found';
        }

        clearTimeout(painterTimeout);
        painterTimeout = setTimeout( function () {
            border = calcBorder(initialpoint, modifyThreshold, true);
            if(!border){
                return false;
            }
            var arr = [];
            for( var i = 0, bl = border.length; i < bl ; ++i )
            {
                arr.push( border[i].x );
                arr.push( border[i].y );
            }
            shape.setPoints(arr);
            var shapeLayer = shape.getLayer();
            shapeLayer.draw();
            self.onThresholdChange(modifyThreshold);
        }, 100);
    };

    /**
     * Event fired when threshold change
     * @param {Number} Current threshold
     */
    this.onThresholdChange = function(/*value*/){
        // Defaults do nothing
    };

    /**
     * Handle mouse down event.
     * @param {Object} event The mouse down event.
     */
    this.mousedown = function(event){
        imageInfo = app.getImageData();
        if (!imageInfo){ return console.error('No image found');}

        self.started = true;
        initialpoint = getCoord(event);
        paintBorder(initialpoint, initialthreshold);
        self.onThresholdChange(initialthreshold);
    };

    /**
     * Handle mouse move event.
     * @param {Object} event The mouse move event.
     */
    this.mousemove = function(event){
        if (!self.started)
        {
            return;
        }
        var movedpoint   = getCoord(event);
        currentthreshold = Math.round(Math.sqrt( Math.pow((initialpoint.x-movedpoint.x), 2) + Math.pow((initialpoint.y-movedpoint.y), 2) )/2);
        currentthreshold = currentthreshold < initialthreshold ? initialthreshold : currentthreshold - initialthreshold;
        self.modifyThreshold(currentthreshold);
    };

    /**
     * Handle mouse up event.
     * @param {Object} event The mouse up event.
     */
    this.mouseup = function(/*event*/){
        self.started = false;
        if(extender){
            self.extend();
        }
    };

    /**
     * Handle mouse out event.
     * @param {Object} event The mouse out event.
     */
    this.mouseout = function(/*event*/){
        self.mouseup(/*event*/);
    };

    /**
     * Handle touch start event.
     * @param {Object} event The touch start event.
     */
    this.touchstart = function(event){
        // treat as mouse down
        self.mousedown(event);
    };

    /**
     * Handle touch move event.
     * @param {Object} event The touch move event.
     */
    this.touchmove = function(event){
        // treat as mouse move
        self.mousemove(event);
    };

    /**
     * Handle touch end event.
     * @param {Object} event The touch end event.
     */
    this.touchend = function(/*event*/){
        // treat as mouse up
        self.mouseup(/*event*/);
    };

    /**
     * Handle key down event.
     * @param {Object} event The key down event.
     */
    this.keydown = function(event){
        app.onKeydown(event);
    };

    /**
     * Setup the tool GUI.
     */
    this.setup = function ()
    {
        gui = new dwv.gui.ColourTool(app, "ff");
        gui.setup();
    };

    /**
     * Enable the tool.
     * @param {Boolean} bool The flag to enable or not.
     */
    this.display = function(bool){
        if ( gui ) {
            gui.display(bool);
        }
        // TODO why twice?
        this.init();
    };

    /**
     * Initialise the tool.
     */
    this.init = function()
    {
        if ( gui ) {
            // init with the app window scale
            this.style.setScale(app.getWindowScale());
            // set the default to the first in the list
            this.setLineColour(this.style.getLineColour());
            // init html
            gui.initialise();
        }

        return true;
    };

    /**
     * Add an event listener on the app.
     * @param {Object} listener The method associated with the provided event type.
     */
    this.addEventListener = function (listener)
    {
        listeners.push(listener);
    };

    /**
     * Remove an event listener from the app.
     * @param {Object} listener The method associated with the provided event type.
     */
    this.removeEventListener = function (listener)
    {
        for ( var i = 0; i < listeners.length; ++i )
        {
            if ( listeners[i] === listener ) {
                listeners.splice(i,1);
            }
        }
    };

    // Private Methods -----------------------------------------------------------

    /**
     * Fire an event: call all associated listeners.
     * @param {Object} event The event to fire.
     */
    function fireEvent (event)
    {
        for ( var i=0; i < listeners.length; ++i )
        {
            listeners[i](event);
        }
    }

}; // Floodfill class

/**
 * Help for this tool.
 * @return {Object} The help content.
 */
dwv.tool.Floodfill.prototype.getHelp = function()
{
    return {
        'title': dwv.i18n("tool.Floodfill.name"),
        'brief': dwv.i18n("tool.Floodfill.brief"),
        "mouse": {
            "click": dwv.i18n("tool.Floodfill.click")
        },
        "touch": {
            "tap": dwv.i18n("tool.Floodfill.tap")
        }
    };
};

/**
 * Set the line colour of the drawing.
 * @param {String} colour The colour to set.
 */
dwv.tool.Floodfill.prototype.setLineColour = function(colour)
{
    // set style var
    this.style.setLineColour(colour);
};

// namespaces
var dwv = dwv || {};
dwv.tool = dwv.tool || {};
// external
var Konva = Konva || {};

/**
 * FreeHand factory.
 * @constructor
 * @external Konva
 */
dwv.tool.FreeHandFactory = function ()
{
    /**
     * Get the number of points needed to build the shape.
     * @return {Number} The number of points.
     */
    this.getNPoints = function () { return 1000; };
    /**
     * Get the timeout between point storage.
     * @return {Number} The timeout in milliseconds.
     */
    this.getTimeout = function () { return 25; };
};

/**
 * Create a roi shape to be displayed.
 * @param {Array} points The points from which to extract the line.
 * @param {Object} style The drawing style.
 * @param {Object} image The associated image.
 */
dwv.tool.FreeHandFactory.prototype.create = function (points, style /*, image*/)
{
    // points stored the Konvajs way
    var arr = [];
    for( var i = 0; i < points.length; ++i )
    {
        arr.push( points[i].getX() );
        arr.push( points[i].getY() );
    }
    // draw shape
    var kshape = new Konva.Line({
        points: arr,
        stroke: style.getLineColour(),
        strokeWidth: style.getScaledStrokeWidth(),
        name: "shape",
        tension: 0.5
    });

    // text
    var ktext = new Konva.Text({
        fontSize: style.getScaledFontSize(),
        fontFamily: style.getFontFamily(),
        fill: style.getLineColour(),
        name: "text"
    });
    ktext.textExpr = "";
    ktext.longText = "";
    ktext.quant = null;
    ktext.setText(ktext.textExpr);

    // label
    var klabel = new Konva.Label({
        x: points[0].getX(),
        y: points[0].getY() + 10,
        name: "label"
    });
    klabel.add(ktext);
    klabel.add(new Konva.Tag());

    // return group
    var group = new Konva.Group();
    group.name("freeHand-group");
    group.add(kshape);
    group.add(klabel);
    group.visible(true); // dont inherit
    return group;
};

/**
 * Update a FreeHand shape.
 * @param {Object} anchor The active anchor.
 * @param {Object} image The associated image.
 */
dwv.tool.UpdateFreeHand = function (anchor /*, image*/)
{
    // parent group
    var group = anchor.getParent();
    // associated shape
    var kline = group.getChildren( function (node) {
        return node.name() === 'shape';
    })[0];
    // associated label
    var klabel = group.getChildren( function (node) {
        return node.name() === 'label';
    })[0];

    // update self
    var point = group.getChildren( function (node) {
        return node.id() === anchor.id();
    })[0];
    point.x( anchor.x() );
    point.y( anchor.y() );
    // update the roi point and compensate for possible drag
    // (the anchor id is the index of the point in the list)
    var points = kline.points();
    points[anchor.id()] = anchor.x() - kline.x();
    points[anchor.id()+1] = anchor.y() - kline.y();
    kline.points( points );

    // update text
    var ktext = klabel.getText();
    ktext.quant = null;
    ktext.setText(dwv.utils.replaceFlags(ktext.textExpr, ktext.quant));
    // update position
    var textPos = { 'x': points[0] + kline.x(), 'y': points[1] +  kline.y() + 10 };
    klabel.position( textPos );
};

// namespaces
var dwv = dwv || {};
dwv.tool = dwv.tool || {};

/**
 * Livewire painting tool.
 * @constructor
 * @param {Object} app The associated application.
 */
dwv.tool.Livewire = function(app)
{
    /**
     * Closure to self: to be used by event handlers.
     * @private
     * @type WindowLevel
     */
    var self = this;
    /**
     * Livewire GUI.
     * @type Object
     */
    var gui = null;
    /**
     * Interaction start flag.
     * @type Boolean
     */
    this.started = false;

    /**
     * Draw command.
     * @private
     * @type Object
     */
    var command = null;
    /**
     * Current shape group.
     * @private
     * @type Object
     */
    var shapeGroup = null;
    /**
     * Drawing style.
     * @type Style
     */
    this.style = new dwv.html.Style();
    // init with the app window scale
    this.style.setScale(app.getWindowScale());

    /**
     * Path storage. Paths are stored in reverse order.
     * @private
     * @type Path
     */
    var path = new dwv.math.Path();
    /**
     * Current path storage. Paths are stored in reverse order.
     * @private
     * @type Path
     */
    var currentPath = new dwv.math.Path();
    /**
     * List of parent points.
     * @private
     * @type Array
     */
    var parentPoints = [];
    /**
     * Tolerance.
     * @private
     * @type Number
     */
    var tolerance = 5;

    /**
     * Event listeners.
     * @private
     */
    var listeners = [];

    /**
     * Clear the parent points list.
     * @private
     */
    function clearParentPoints() {
        var nrows = app.getImage().getGeometry().getSize().getNumberOfRows();
        for( var i = 0; i < nrows; ++i ) {
            parentPoints[i] = [];
        }
    }

    /**
     * Clear the stored paths.
     * @private
     */
    function clearPaths() {
        path = new dwv.math.Path();
        currentPath = new dwv.math.Path();
    }

    /**
     * Scissor representation.
     * @private
     * @type Scissors
     */
    var scissors = new dwv.math.Scissors();

    /**
     * Handle mouse down event.
     * @param {Object} event The mouse down event.
     */
    this.mousedown = function(event){
        // first time
        if( !self.started ) {
            self.started = true;
            self.x0 = event._x;
            self.y0 = event._y;
            // clear vars
            clearPaths();
            clearParentPoints();
            // do the training from the first point
            var p = new dwv.math.FastPoint2D(event._x, event._y);
            scissors.doTraining(p);
            // add the initial point to the path
            var p0 = new dwv.math.Point2D(event._x, event._y);
            path.addPoint(p0);
            path.addControlPoint(p0);
        }
        else {
            // final point: at 'tolerance' of the initial point
            if( (Math.abs(event._x - self.x0) < tolerance) && (Math.abs(event._y - self.y0) < tolerance) ) {
                // draw
                self.mousemove(event);
                // listen
                command.onExecute = fireEvent;
                command.onUndo = fireEvent;
                // debug
                console.log("Done.");
                // save command in undo stack
                app.addToUndoStack(command);
                // set flag
                self.started = false;
            }
            // anchor point
            else {
                path = currentPath;
                clearParentPoints();
                var pn = new dwv.math.FastPoint2D(event._x, event._y);
                scissors.doTraining(pn);
                path.addControlPoint(currentPath.getPoint(0));
            }
        }
    };

    /**
     * Handle mouse move event.
     * @param {Object} event The mouse move event.
     */
    this.mousemove = function(event){
        if (!self.started)
        {
            return;
        }
        // set the point to find the path to
        var p = new dwv.math.FastPoint2D(event._x, event._y);
        scissors.setPoint(p);
        // do the work
        var results = 0;
        var stop = false;
        while( !parentPoints[p.y][p.x] && !stop)
        {
            console.log("Getting ready...");
            results = scissors.doWork();

            if( results.length === 0 ) {
                stop = true;
            }
            else {
                // fill parents
                for( var i = 0; i < results.length-1; i+=2 ) {
                    var _p = results[i];
                    var _q = results[i+1];
                    parentPoints[_p.y][_p.x] = _q;
                }
            }
        }
        console.log("Ready!");

        // get the path
        currentPath = new dwv.math.Path();
        stop = false;
        while (p && !stop) {
            currentPath.addPoint(new dwv.math.Point2D(p.x, p.y));
            if(!parentPoints[p.y]) {
                stop = true;
            }
            else {
                if(!parentPoints[p.y][p.x]) {
                    stop = true;
                }
                else {
                    p = parentPoints[p.y][p.x];
                }
            }
        }
        currentPath.appenPath(path);

        // remove previous draw
        if ( shapeGroup ) {
            shapeGroup.destroy();
        }
        // create shape
        var factory = new dwv.tool.RoiFactory();
        shapeGroup = factory.create(currentPath.pointArray, self.style);
        shapeGroup.id( dwv.math.guid() );
        // draw shape command
        command = new dwv.tool.DrawGroupCommand(shapeGroup, "livewire", app.getCurrentDrawLayer());
        // draw
        command.execute();
    };

    /**
     * Handle mouse up event.
     * @param {Object} event The mouse up event.
     */
    this.mouseup = function(/*event*/){
        // nothing to do
    };

    /**
     * Handle mouse out event.
     * @param {Object} event The mouse out event.
     */
    this.mouseout = function(event){
        // treat as mouse up
        self.mouseup(event);
    };

    /**
     * Handle double click event.
     * @param {Object} event The double click event.
     */
    this.dblclick = function(/*event*/){
        console.log("dblclick");
        // save command in undo stack
        app.addToUndoStack(command);
        // set flag
        self.started = false;
    };

    /**
     * Handle touch start event.
     * @param {Object} event The touch start event.
     */
    this.touchstart = function(event){
        // treat as mouse down
        self.mousedown(event);
    };

    /**
     * Handle touch move event.
     * @param {Object} event The touch move event.
     */
    this.touchmove = function(event){
        // treat as mouse move
        self.mousemove(event);
    };

    /**
     * Handle touch end event.
     * @param {Object} event The touch end event.
     */
    this.touchend = function(event){
        // treat as mouse up
        self.mouseup(event);
    };

    /**
     * Handle key down event.
     * @param {Object} event The key down event.
     */
    this.keydown = function(event){
        app.onKeydown(event);
    };

    /**
     * Setup the tool GUI.
     */
    this.setup = function ()
    {
        gui = new dwv.gui.ColourTool(app, "lw");
        gui.setup();
    };

    /**
     * Enable the tool.
     * @param {Boolean} bool The flag to enable or not.
     */
    this.display = function(bool){
        if ( gui ) {
            gui.display(bool);
        }
        // start scissors if displayed
        if (bool) {
            //scissors = new dwv.math.Scissors();
            var size = app.getImage().getGeometry().getSize();
            scissors.setDimensions(
                    size.getNumberOfColumns(),
                    size.getNumberOfRows() );
            scissors.setData(app.getImageData().data);
        }
    };

    /**
     * Initialise the tool.
     */
    this.init = function()
    {
        if ( gui ) {
            // init with the app window scale
            this.style.setScale(app.getWindowScale());
            // set the default to the first in the list
            this.setLineColour(this.style.getLineColour());
            // init html
            gui.initialise();
        }

        return true;
    };

    /**
     * Add an event listener on the app.
     * @param {Object} listener The method associated with the provided event type.
     */
    this.addEventListener = function (listener)
    {
        listeners.push(listener);
    };

    /**
     * Remove an event listener from the app.
     * @param {Object} listener The method associated with the provided event type.
     */
    this.removeEventListener = function (listener)
    {
        for ( var i = 0; i < listeners.length; ++i )
        {
            if ( listeners[i] === listener ) {
                listeners.splice(i,1);
            }
        }
    };

    // Private Methods -----------------------------------------------------------

    /**
     * Fire an event: call all associated listeners.
     * @param {Object} event The event to fire.
     */
    function fireEvent (event)
    {
        for ( var i=0; i < listeners.length; ++i )
        {
            listeners[i](event);
        }
    }

}; // Livewire class

/**
 * Help for this tool.
 * @return {Object} The help content.
 */
dwv.tool.Livewire.prototype.getHelp = function()
{
    return {
        "title": dwv.i18n("tool.Livewire.name"),
        "brief": dwv.i18n("tool.Livewire.brief")
    };
};

/**
 * Set the line colour of the drawing.
 * @param {String} colour The colour to set.
 */
dwv.tool.Livewire.prototype.setLineColour = function(colour)
{
    // set style var
    this.style.setLineColour(colour);
};

// namespaces
var dwv = dwv || {};
dwv.tool = dwv.tool || {};
// external
var Konva = Konva || {};

/**
 * Protractor factory.
 * @constructor
 * @external Konva
 */
dwv.tool.ProtractorFactory = function ()
{
    /**
     * Get the number of points needed to build the shape.
     * @return {Number} The number of points.
     */
    this.getNPoints = function () { return 3; };
    /**
     * Get the timeout between point storage.
     * @return {Number} The timeout in milliseconds.
     */
    this.getTimeout = function () { return 500; };
};

/**
 * Create a protractor shape to be displayed.
 * @param {Array} points The points from which to extract the protractor.
 * @param {Object} style The drawing style.
 * @param {Object} image The associated image.
 */
dwv.tool.ProtractorFactory.prototype.create = function (points, style/*, image*/)
{
    // physical shape
    var line0 = new dwv.math.Line(points[0], points[1]);
    // points stored the Konvajs way
    var pointsArray = [];
    for( var i = 0; i < points.length; ++i )
    {
        pointsArray.push( points[i].getX() );
        pointsArray.push( points[i].getY() );
    }
    // draw shape
    var kshape = new Konva.Line({
        points: pointsArray,
        stroke: style.getLineColour(),
        strokeWidth: style.getScaledStrokeWidth(),
        name: "shape"
    });
    var group = new Konva.Group();
    group.name("protractor-group");
    group.add(kshape);
    group.visible(true); // dont inherit
    // text and decoration
    if ( points.length === 3 ) {
        var line1 = new dwv.math.Line(points[1], points[2]);
        // quantification
        var angle = dwv.math.getAngle(line0, line1);
        var inclination = line0.getInclination();
        if ( angle > 180 ) {
            angle = 360 - angle;
            inclination += angle;
        }

        // quantification
        var quant = { "angle": { "value": angle, "unit": dwv.i18n("unit.degree")} };
        var ktext = new Konva.Text({
            fontSize: style.getScaledFontSize(),
            fontFamily: style.getFontFamily(),
            fill: style.getLineColour(),
            name: "text"
        });
        ktext.textExpr = "{angle}";
        ktext.longText = "";
        ktext.quant = quant;
        ktext.setText(dwv.utils.replaceFlags(ktext.textExpr, ktext.quant));

        // label
        var midX = ( line0.getMidpoint().getX() + line1.getMidpoint().getX() ) / 2;
        var midY = ( line0.getMidpoint().getY() + line1.getMidpoint().getY() ) / 2;
        var klabel = new Konva.Label({
            x: midX,
            y: midY - 15,
            name: "label"
        });
        klabel.add(ktext);
        klabel.add(new Konva.Tag());

        // arc
        var radius = Math.min(line0.getLength(), line1.getLength()) * 33 / 100;
        var karc = new Konva.Arc({
            innerRadius: radius,
            outerRadius: radius,
            stroke: style.getLineColour(),
            strokeWidth: style.getScaledStrokeWidth(),
            angle: angle,
            rotation: -inclination,
            x: points[1].getX(),
            y: points[1].getY(),
            name: "shape-arc"
         });
        // add to group
        group.add(klabel);
        group.add(karc);
    }
    // return group
    return group;
};

/**
 * Update a protractor shape.
 * @param {Object} anchor The active anchor.
 * @param {Object} image The associated image.
 */
dwv.tool.UpdateProtractor = function (anchor/*, image*/)
{
    // parent group
    var group = anchor.getParent();
    // associated shape
    var kline = group.getChildren( function (node) {
        return node.name() === 'shape';
    })[0];
    // associated label
    var klabel = group.getChildren( function (node) {
        return node.name() === 'label';
    })[0];
    // associated arc
    var karc = group.getChildren( function (node) {
        return node.name() === 'shape-arc';
    })[0];
    // find special points
    var begin = group.getChildren( function (node) {
        return node.id() === 'begin';
    })[0];
    var mid = group.getChildren( function (node) {
        return node.id() === 'mid';
    })[0];
    var end = group.getChildren( function (node) {
        return node.id() === 'end';
    })[0];
    // update special points
    switch ( anchor.id() ) {
    case 'begin':
        begin.x( anchor.x() );
        begin.y( anchor.y() );
        break;
    case 'mid':
        mid.x( anchor.x() );
        mid.y( anchor.y() );
        break;
    case 'end':
        end.x( anchor.x() );
        end.y( anchor.y() );
        break;
    }
    // update shape and compensate for possible drag
    // note: shape.position() and shape.size() won't work...
    var bx = begin.x() - kline.x();
    var by = begin.y() - kline.y();
    var mx = mid.x() - kline.x();
    var my = mid.y() - kline.y();
    var ex = end.x() - kline.x();
    var ey = end.y() - kline.y();
    kline.points( [bx,by,mx,my,ex,ey] );
    // update text
    var p2d0 = new dwv.math.Point2D(begin.x(), begin.y());
    var p2d1 = new dwv.math.Point2D(mid.x(), mid.y());
    var p2d2 = new dwv.math.Point2D(end.x(), end.y());
    var line0 = new dwv.math.Line(p2d0, p2d1);
    var line1 = new dwv.math.Line(p2d1, p2d2);
    var angle = dwv.math.getAngle(line0, line1);
    var inclination = line0.getInclination();
    if ( angle > 180 ) {
        angle = 360 - angle;
        inclination += angle;
    }

    // update text
    var quant = { "angle": { "value": angle, "unit": dwv.i18n("unit.degree")} };
    var ktext = klabel.getText();
    ktext.quant = quant;
    ktext.setText(dwv.utils.replaceFlags(ktext.textExpr, ktext.quant));
    // update position
    var midX = ( line0.getMidpoint().getX() + line1.getMidpoint().getX() ) / 2;
    var midY = ( line0.getMidpoint().getY() + line1.getMidpoint().getY() ) / 2;
    var textPos = { 'x': midX, 'y': midY - 15 };
    klabel.position( textPos );

    // arc
    var radius = Math.min(line0.getLength(), line1.getLength()) * 33 / 100;
    karc.innerRadius(radius);
    karc.outerRadius(radius);
    karc.angle(angle);
    karc.rotation(-inclination);
    var arcPos = { 'x': mid.x(), 'y': mid.y() };
    karc.position(arcPos);
};

// namespaces
var dwv = dwv || {};
dwv.tool = dwv.tool || {};
// external
var Konva = Konva || {};

/**
 * Rectangle factory.
 * @constructor
 * @external Konva
 */
dwv.tool.RectangleFactory = function ()
{
    /**
     * Get the number of points needed to build the shape.
     * @return {Number} The number of points.
     */
    this.getNPoints = function () { return 2; };
    /**
     * Get the timeout between point storage.
     * @return {Number} The timeout in milliseconds.
     */
    this.getTimeout = function () { return 0; };
};

/**
 * Create a rectangle shape to be displayed.
 * @param {Array} points The points from which to extract the rectangle.
 * @param {Object} style The drawing style.
 * @param {Object} image The associated image.
 */
dwv.tool.RectangleFactory.prototype.create = function (points, style, image)
{
    // physical shape
    var rectangle = new dwv.math.Rectangle(points[0], points[1]);
    // draw shape
    var kshape = new Konva.Rect({
        x: rectangle.getBegin().getX(),
        y: rectangle.getBegin().getY(),
        width: rectangle.getWidth(),
        height: rectangle.getHeight(),
        stroke: style.getLineColour(),
        strokeWidth: style.getScaledStrokeWidth(),
        name: "shape"
    });
    // quantification
    var quant = image.quantifyRect( rectangle );
    var ktext = new Konva.Text({
        fontSize: style.getScaledFontSize(),
        fontFamily: style.getFontFamily(),
        fill: style.getLineColour(),
        name: "text"
    });
    ktext.textExpr = "{surface}";
    ktext.longText = "";
    ktext.quant = quant;
    ktext.setText(dwv.utils.replaceFlags(ktext.textExpr, ktext.quant));

    // label
    var klabel = new Konva.Label({
        x: rectangle.getBegin().getX(),
        y: rectangle.getEnd().getY() + 10,
        name: "label"
    });
    klabel.add(ktext);
    klabel.add(new Konva.Tag());

    // return group
    var group = new Konva.Group();
    group.name("rectangle-group");
    group.add(kshape);
    group.add(klabel);
    group.visible(true); // dont inherit
    return group;
};

/**
 * Update a rectangle shape.
 * @param {Object} anchor The active anchor.
 * @param {Object} image The associated image.
 */
dwv.tool.UpdateRect = function (anchor, image)
{
    // parent group
    var group = anchor.getParent();
    // associated shape
    var krect = group.getChildren( function (node) {
        return node.name() === 'shape';
    })[0];
    // associated label
    var klabel = group.getChildren( function (node) {
        return node.name() === 'label';
    })[0];
    // find special points
    var topLeft = group.getChildren( function (node) {
        return node.id() === 'topLeft';
    })[0];
    var topRight = group.getChildren( function (node) {
        return node.id() === 'topRight';
    })[0];
    var bottomRight = group.getChildren( function (node) {
        return node.id() === 'bottomRight';
    })[0];
    var bottomLeft = group.getChildren( function (node) {
        return node.id() === 'bottomLeft';
    })[0];
    // update 'self' (undo case) and special points
    switch ( anchor.id() ) {
    case 'topLeft':
        topLeft.x( anchor.x() );
        topLeft.y( anchor.y() );
        topRight.y( anchor.y() );
        bottomLeft.x( anchor.x() );
        break;
    case 'topRight':
        topRight.x( anchor.x() );
        topRight.y( anchor.y() );
        topLeft.y( anchor.y() );
        bottomRight.x( anchor.x() );
        break;
    case 'bottomRight':
        bottomRight.x( anchor.x() );
        bottomRight.y( anchor.y() );
        bottomLeft.y( anchor.y() );
        topRight.x( anchor.x() );
        break;
    case 'bottomLeft':
        bottomLeft.x( anchor.x() );
        bottomLeft.y( anchor.y() );
        bottomRight.y( anchor.y() );
        topLeft.x( anchor.x() );
        break;
    default :
        console.error('Unhandled anchor id: '+anchor.id());
        break;
    }
    // update shape
    krect.position(topLeft.position());
    var width = topRight.x() - topLeft.x();
    var height = bottomLeft.y() - topLeft.y();
    if ( width && height ) {
        krect.size({'width': width, 'height': height});
    }
    // new rect
    var p2d0 = new dwv.math.Point2D(topLeft.x(), topLeft.y());
    var p2d1 = new dwv.math.Point2D(bottomRight.x(), bottomRight.y());
    var rect = new dwv.math.Rectangle(p2d0, p2d1);
    // update text
    var quant = image.quantifyRect( rect );
    var ktext = klabel.getText();
    ktext.quant = quant;
    ktext.setText(dwv.utils.replaceFlags(ktext.textExpr, ktext.quant));
    // update position
    var textPos = { 'x': rect.getBegin().getX(), 'y': rect.getEnd().getY() + 10 };
    klabel.position( textPos );
};

// namespaces
var dwv = dwv || {};
dwv.tool = dwv.tool || {};
// external
var Konva = Konva || {};

/**
 * ROI factory.
 * @constructor
 * @external Konva
 */
dwv.tool.RoiFactory = function ()
{
    /**
     * Get the number of points needed to build the shape.
     * @return {Number} The number of points.
     */
    this.getNPoints = function () { return 50; };
    /**
     * Get the timeout between point storage.
     * @return {Number} The timeout in milliseconds.
     */
    this.getTimeout = function () { return 100; };
};

/**
 * Create a roi shape to be displayed.
 * @param {Array} points The points from which to extract the line.
 * @param {Object} style The drawing style.
 * @param {Object} image The associated image.
 */
dwv.tool.RoiFactory.prototype.create = function (points, style /*, image*/)
{
    // physical shape
    var roi = new dwv.math.ROI();
    // add input points to the ROI
    roi.addPoints(points);
    // points stored the Konvajs way
    var arr = [];
    for( var i = 0; i < roi.getLength(); ++i )
    {
        arr.push( roi.getPoint(i).getX() );
        arr.push( roi.getPoint(i).getY() );
    }
    // draw shape
    var kshape = new Konva.Line({
        points: arr,
        stroke: style.getLineColour(),
        strokeWidth: style.getScaledStrokeWidth(),
        name: "shape",
        closed: true
    });

    // text
    var ktext = new Konva.Text({
        fontSize: style.getScaledFontSize(),
        fontFamily: style.getFontFamily(),
        fill: style.getLineColour(),
        name: "text"
    });
    ktext.textExpr = "";
    ktext.longText = "";
    ktext.quant = null;
    ktext.setText(dwv.utils.replaceFlags(ktext.textExpr, ktext.quant));

    // label
    var klabel = new Konva.Label({
        x: roi.getPoint(0).getX(),
        y: roi.getPoint(0).getY() + 10,
        name: "label"
    });
    klabel.add(ktext);
    klabel.add(new Konva.Tag());

    // return group
    var group = new Konva.Group();
    group.name("roi-group");
    group.add(kshape);
    group.add(klabel);
    group.visible(true); // dont inherit
    return group;
};

/**
 * Update a roi shape.
 * @param {Object} anchor The active anchor.
 * @param {Object} image The associated image.
 */
dwv.tool.UpdateRoi = function (anchor /*, image*/)
{
    // parent group
    var group = anchor.getParent();
    // associated shape
    var kroi = group.getChildren( function (node) {
        return node.name() === 'shape';
    })[0];
    // associated label
    var klabel = group.getChildren( function (node) {
        return node.name() === 'label';
    })[0];

    // update self
    var point = group.getChildren( function (node) {
        return node.id() === anchor.id();
    })[0];
    point.x( anchor.x() );
    point.y( anchor.y() );
    // update the roi point and compensate for possible drag
    // (the anchor id is the index of the point in the list)
    var points = kroi.points();
    points[anchor.id()] = anchor.x() - kroi.x();
    points[anchor.id()+1] = anchor.y() - kroi.y();
    kroi.points( points );

    // update text
    var ktext = klabel.getText();
    ktext.quant = null;
    ktext.setText(dwv.utils.replaceFlags(ktext.textExpr, ktext.quant));
    // update position
    var textPos = { 'x': points[0] + kroi.x(), 'y': points[1] +  kroi.y() + 10 };
    klabel.position( textPos );

};

// namespaces
var dwv = dwv || {};
dwv.tool = dwv.tool || {};
// external
var Konva = Konva || {};

/**
 * Ruler factory.
 * @constructor
 * @external Konva
 */
dwv.tool.RulerFactory = function ()
{
    /**
     * Get the number of points needed to build the shape.
     * @return {Number} The number of points.
     */
    this.getNPoints = function () { return 2; };
    /**
     * Get the timeout between point storage.
     * @return {Number} The timeout in milliseconds.
     */
    this.getTimeout = function () { return 0; };
};

/**
 * Create a ruler shape to be displayed.
 * @param {Array} points The points from which to extract the line.
 * @param {Object} style The drawing style.
 * @param {Object} image The associated image.
 */
dwv.tool.RulerFactory.prototype.create = function (points, style, image)
{
    // physical shape
    var line = new dwv.math.Line(points[0], points[1]);
    // draw shape
    var kshape = new Konva.Line({
        points: [line.getBegin().getX(), line.getBegin().getY(),
                 line.getEnd().getX(), line.getEnd().getY() ],
        stroke: style.getLineColour(),
        strokeWidth: style.getScaledStrokeWidth(),
        name: "shape"
    });

    // tick begin
    var linePerp0 = dwv.math.getPerpendicularLine( line, points[0], 10 );
    var ktick0 = new Konva.Line({
        points: [linePerp0.getBegin().getX(), linePerp0.getBegin().getY(),
                 linePerp0.getEnd().getX(), linePerp0.getEnd().getY() ],
        stroke: style.getLineColour(),
        strokeWidth: style.getScaledStrokeWidth(),
        name: "shape-tick0"
    });

    // tick end
    var linePerp1 = dwv.math.getPerpendicularLine( line, points[1], 10 );
    var ktick1 = new Konva.Line({
        points: [linePerp1.getBegin().getX(), linePerp1.getBegin().getY(),
                 linePerp1.getEnd().getX(), linePerp1.getEnd().getY() ],
        stroke: style.getLineColour(),
        strokeWidth: style.getScaledStrokeWidth(),
        name: "shape-tick1"
    });

    // quantification
    var quant = image.quantifyLine( line );
    var ktext = new Konva.Text({
        fontSize: style.getScaledFontSize(),
        fontFamily: style.getFontFamily(),
        fill: style.getLineColour(),
        name: "text"
    });
    ktext.textExpr = "{length}";
    ktext.longText = "";
    ktext.quant = quant;
    ktext.setText(dwv.utils.replaceFlags(ktext.textExpr, ktext.quant));
    // label
    var dX = line.getBegin().getX() > line.getEnd().getX() ? 0 : -1;
    var dY = line.getBegin().getY() > line.getEnd().getY() ? -1 : 0.5;
    var klabel = new Konva.Label({
        x: line.getEnd().getX() + dX * 25,
        y: line.getEnd().getY() + dY * 15,
        name: "label"
    });
    klabel.add(ktext);
    klabel.add(new Konva.Tag());

    // return group
    var group = new Konva.Group();
    group.name("ruler-group");
    group.add(kshape);
    group.add(ktick0);
    group.add(ktick1);
    group.add(klabel);
    group.visible(true); // dont inherit
    return group;
};

/**
 * Update a ruler shape.
 * @param {Object} anchor The active anchor.
 * @param {Object} image The associated image.
 */
dwv.tool.UpdateRuler = function (anchor, image)
{
    // parent group
    var group = anchor.getParent();
    // associated shape
    var kline = group.getChildren( function (node) {
        return node.name() === 'shape';
    })[0];
    // associated tick0
    var ktick0 = group.getChildren( function (node) {
        return node.name() === 'shape-tick0';
    })[0];
    // associated tick1
    var ktick1 = group.getChildren( function (node) {
        return node.name() === 'shape-tick1';
    })[0];
    // associated label
    var klabel = group.getChildren( function (node) {
        return node.name() === 'label';
    })[0];
    // find special points
    var begin = group.getChildren( function (node) {
        return node.id() === 'begin';
    })[0];
    var end = group.getChildren( function (node) {
        return node.id() === 'end';
    })[0];
    // update special points
    switch ( anchor.id() ) {
    case 'begin':
        begin.x( anchor.x() );
        begin.y( anchor.y() );
        break;
    case 'end':
        end.x( anchor.x() );
        end.y( anchor.y() );
        break;
    }
    // update shape and compensate for possible drag
    // note: shape.position() and shape.size() won't work...
    var bx = begin.x() - kline.x();
    var by = begin.y() - kline.y();
    var ex = end.x() - kline.x();
    var ey = end.y() - kline.y();
    kline.points( [bx,by,ex,ey] );
    // new line
    var p2d0 = new dwv.math.Point2D(begin.x(), begin.y());
    var p2d1 = new dwv.math.Point2D(end.x(), end.y());
    var line = new dwv.math.Line(p2d0, p2d1);
    // tick
    var p2b = new dwv.math.Point2D(bx, by);
    var p2e = new dwv.math.Point2D(ex, ey);
    var linePerp0 = dwv.math.getPerpendicularLine( line, p2b, 10 );
    ktick0.points( [linePerp0.getBegin().getX(), linePerp0.getBegin().getY(),
        linePerp0.getEnd().getX(), linePerp0.getEnd().getY()] );
    var linePerp1 = dwv.math.getPerpendicularLine( line, p2e, 10 );
    ktick1.points( [linePerp1.getBegin().getX(), linePerp1.getBegin().getY(),
        linePerp1.getEnd().getX(), linePerp1.getEnd().getY()] );
    // update text
    var quant = image.quantifyLine( line );
    var ktext = klabel.getText();
    ktext.quant = quant;
    ktext.setText(dwv.utils.replaceFlags(ktext.textExpr, ktext.quant));
    // update position
    var dX = line.getBegin().getX() > line.getEnd().getX() ? 0 : -1;
    var dY = line.getBegin().getY() > line.getEnd().getY() ? -1 : 0.5;
    var textPos = {
        'x': line.getEnd().getX() + dX * 25,
        'y': line.getEnd().getY() + dY * 15 };
    klabel.position( textPos );
};

// namespaces
var dwv = dwv || {};
dwv.tool = dwv.tool || {};

/**
 * Scroll class.
 * @constructor
 * @param {Object} app The associated application.
 */
dwv.tool.Scroll = function(app)
{
    /**
     * Closure to self: to be used by event handlers.
     * @private
     * @type WindowLevel
     */
    var self = this;
    /**
     * Scroll GUI.
     * @type Object
     */
    var gui = null;
    /**
     * Interaction start flag.
     * @type Boolean
     */
    this.started = false;
    // touch timer ID (created by setTimeout)
    var touchTimerID = null;

    /**
     * Handle mouse down event.
     * @param {Object} event The mouse down event.
     */
    this.mousedown = function(event){
        // stop viewer if playing
        if ( app.getViewController().isPlaying() ) {
            app.getViewController().stop();
        }
        // start flag
        self.started = true;
        // first position
        self.x0 = event._x;
        self.y0 = event._y;
    };

    /**
     * Handle mouse move event.
     * @param {Object} event The mouse move event.
     */
    this.mousemove = function(event){
        if (!self.started) {
            return;
        }

        // difference to last Y position
        var diffY = event._y - self.y0;
        var yMove = (Math.abs(diffY) > 15);
        // do not trigger for small moves
        if( yMove ) {
            // update GUI
            if( diffY > 0 ) {
                app.getViewController().incrementSliceNb();
            }
            else {
                app.getViewController().decrementSliceNb();
            }
        }

        // difference to last X position
        var diffX = event._x - self.x0;
        var xMove = (Math.abs(diffX) > 15);
        // do not trigger for small moves
        if( xMove ) {
            // update GUI
            if( diffX > 0 ) {
                app.getViewController().incrementFrameNb();
            }
            else {
                app.getViewController().decrementFrameNb();
            }
        }

        // reset origin point
        if (xMove) {
            self.x0 = event._x;
        }
        if (yMove) {
            self.y0 = event._y;
        }
    };

    /**
     * Handle mouse up event.
     * @param {Object} event The mouse up event.
     */
    this.mouseup = function(/*event*/){
        if (self.started)
        {
            // stop recording
            self.started = false;
        }
    };

    /**
     * Handle mouse out event.
     * @param {Object} event The mouse out event.
     */
    this.mouseout = function(event){
        self.mouseup(event);
    };

    /**
     * Handle touch start event.
     * @param {Object} event The touch start event.
     */
    this.touchstart = function(event){
        // long touch triggers the dblclick
        touchTimerID = setTimeout(self.dblclick, 500);
        // call mouse equivalent
        self.mousedown(event);
    };

    /**
     * Handle touch move event.
     * @param {Object} event The touch move event.
     */
    this.touchmove = function(event){
        // abort timer if move
        if (touchTimerID !== null) {
            clearTimeout(touchTimerID);
            touchTimerID = null;
        }
        // call mouse equivalent
        self.mousemove(event);
    };

    /**
     * Handle touch end event.
     * @param {Object} event The touch end event.
     */
    this.touchend = function(event){
        // abort timer
        if (touchTimerID !== null) {
            clearTimeout(touchTimerID);
            touchTimerID = null;
        }
        // call mouse equivalent
        self.mouseup(event);
    };

    /**
     * Handle mouse scroll event (fired by Firefox).
     * @param {Object} event The mouse scroll event.
     */
    this.DOMMouseScroll = function (event) {
        // ev.detail on firefox is 3
        if ( event.detail < 0 ) {
            mouseScroll(true);
        } else {
            mouseScroll(false);
        }
    };

    /**
     * Handle mouse wheel event.
     * @param {Object} event The mouse wheel event.
     */
    this.mousewheel = function (event) {
        // ev.wheelDelta on chrome is 120
        if ( event.wheelDelta > 0 ) {
            mouseScroll(true);
        } else {
            mouseScroll(false);
        }
    };

    /**
     * Mouse scroll action.
     * @param {Boolean} up True to increment, false to decrement.
     */
    function mouseScroll (up) {
        var hasSlices = (app.getImage().getGeometry().getSize().getNumberOfSlices() !== 1);
        var hasFrames = (app.getImage().getNumberOfFrames() !== 1);
        if ( up ) {
            if (hasSlices) {
                app.getViewController().incrementSliceNb();
            } else if (hasFrames) {
                app.getViewController().incrementFrameNb();
            }
        } else {
            if (hasSlices) {
                app.getViewController().decrementSliceNb();
            } else if (hasFrames) {
                app.getViewController().decrementFrameNb();
            }
        }
    }

    /**
     * Handle key down event.
     * @param {Object} event The key down event.
     */
    this.keydown = function(event){
        app.onKeydown(event);
    };
    /**
     * Handle double click.
     * @param {Object} event The key down event.
     */
     this.dblclick = function (/*event*/) {
         app.getViewController().play();
     };

    /**
     * Setup the tool GUI.
     */
    this.setup = function ()
    {
        gui = new dwv.gui.Scroll(app);
        gui.setup();
    };

    /**
     * Enable the tool.
     * @param {Boolean} bool The flag to enable or not.
     */
    this.display = function(bool){
        if ( gui ) {
            gui.display(bool);
        }
    };

    /**
     * Initialise the tool.
     */
    this.init = function() {
        if ( app.isMonoSliceData() && app.getImage().getNumberOfFrames() === 1 ) {
            return false;
        }
        return true;
    };

}; // Scroll class

/**
 * Help for this tool.
 * @return {Object} The help content.
 */
dwv.tool.Scroll.prototype.getHelp = function()
{
    return {
        "title": dwv.i18n("tool.Scroll.name"),
        "brief": dwv.i18n("tool.Scroll.brief"),
        "mouse": {
            "mouse_drag": dwv.i18n("tool.Scroll.mouse_drag"),
            "double_click": dwv.i18n("tool.Scroll.double_click")
        },
        "touch": {
            'touch_drag': dwv.i18n("tool.Scroll.touch_drag"),
            'tap_and_hold': dwv.i18n("tool.Scroll.tap_and_hold")
        }
    };
};

// namespaces
var dwv = dwv || {};
dwv.tool = dwv.tool || {};

/**
 * Tool box.
 * @constructor
 * @param {Array} toolList The list of tool objects.
 * @param {Object} gui The associated gui.
 */
dwv.tool.Toolbox = function( toolList, app )
{
    /**
     * Toolbox GUI.
     * @type Object
     */
    var gui = null;
    /**
     * Selected tool.
     * @type Object
     */
    var selectedTool = null;
    /**
     * Default tool name.
     * @type String
     */
    var defaultToolName = null;

    /**
     * Get the list of tools.
     * @return {Array} The list of tool objects.
     */
    this.getToolList = function ()
    {
        return toolList;
    };

    /**
     * Get the selected tool.
     * @return {Object} The selected tool.
     */
    this.getSelectedTool = function ()
    {
        return selectedTool;
    };

    /**
     * Setup the toolbox GUI.
     */
    this.setup = function ()
    {
        if ( Object.keys(toolList).length !== 0 ) {
            gui = new dwv.gui.Toolbox(app);
            gui.setup(toolList);

            for( var key in toolList ) {
                toolList[key].setup();
            }
        }
    };

    /**
     * Display the toolbox.
     * @param {Boolean} bool Flag to display or not.
     */
    this.display = function (bool)
    {
        if ( Object.keys(toolList).length !== 0 && gui ) {
            gui.display(bool);
        }
    };

    /**
     * Initialise the tool box.
     */
    this.init = function ()
    {
        var keys = Object.keys(toolList);
        // check if we have tools
        if ( keys.length === 0 ) {
            return;
        }
        // init all tools
        defaultToolName = "";
        var displays = [];
        var display = null;
        for( var key in toolList ) {
            display = toolList[key].init();
            if ( display && defaultToolName === "" ) {
                defaultToolName = key;
            }
            displays.push(display);
        }
        this.setSelectedTool(defaultToolName);
        // init html
        if ( gui ) {
            gui.initialise(displays);
        }
    };

    /**
     * Set the selected tool.
     * @return {String} The name of the tool to select.
     */
    this.setSelectedTool = function (name)
    {
        // check if we have it
        if( !this.hasTool(name) )
        {
            throw new Error("Unknown tool: '" + name + "'");
        }
        // hide last selected
        if( selectedTool )
        {
            selectedTool.display(false);
        }
        // enable new one
        selectedTool = toolList[name];
        // display it
        selectedTool.display(true);
    };

    /**
     * Reset the tool box.
     */
    this.reset = function ()
    {
        // hide last selected
        if ( selectedTool ) {
            selectedTool.display(false);
        }
        selectedTool = null;
        defaultToolName = null;
    };
};

/**
 * Check if a tool is in the tool list.
 * @param {String} name The name to check.
 * @return {String} The tool list element for the given name.
 */
dwv.tool.Toolbox.prototype.hasTool = function (name)
{
    return this.getToolList()[name];
};

// namespaces
var dwv = dwv || {};
dwv.tool = dwv.tool || {};

/**
 * UndoStack class.
 * @constructor
 */
dwv.tool.UndoStack = function (app)
{
    /**
     * Undo GUI.
     * @type Object
     */
    var gui = new dwv.gui.Undo(app);
    /**
     * Array of commands.
     * @private
     * @type Array
     */
    var stack = [];

    /**
     * Get the stack.
     * @return {Array} The list of stored commands.
     */
    this.getStack = function () { return stack; };

    /**
     * Current command index.
     * @private
     * @type Number
     */
    var curCmdIndex = 0;

    /**
     * Add a command to the stack.
     * @param {Object} cmd The command to add.
     */
    this.add = function(cmd)
    {
        // clear commands after current index
        stack = stack.slice(0,curCmdIndex);
        // store command
        stack.push(cmd);
        //stack[curCmdIndex] = cmd;
        // increment index
        ++curCmdIndex;
        // add command to display history
        gui.addCommandToUndoHtml(cmd.getName());
    };

    /**
     * Undo the last command.
     */
    this.undo = function()
    {
        // a bit inefficient...
        if( curCmdIndex > 0 )
        {
            // decrement command index
            --curCmdIndex;
            // undo last command
            stack[curCmdIndex].undo();
            // disable last in display history
            gui.enableInUndoHtml(false);
        }
    };

    /**
     * Redo the last command.
     */
    this.redo = function()
    {
        if( curCmdIndex < stack.length )
        {
            // run last command
            stack[curCmdIndex].execute();
            // increment command index
            ++curCmdIndex;
            // enable next in display history
            gui.enableInUndoHtml(true);
        }
    };

    /**
     * Setup the tool GUI.
     */
    this.setup = function ()
    {
        gui.setup();
    };

    /**
     * Initialise the tool GUI.
     */
    this.initialise = function ()
    {
        gui.initialise();
    };

}; // UndoStack class

// namespaces
var dwv = dwv || {};
dwv.tool = dwv.tool || {};

/**
 * WindowLevel tool: handle window/level related events.
 * @constructor
 * @param {Object} app The associated application.
 */
dwv.tool.WindowLevel = function(app)
{
    /**
     * Closure to self: to be used by event handlers.
     * @private
     * @type WindowLevel
     */
    var self = this;
    /**
     * WindowLevel GUI.
     * @type Object
     */
    var gui = null;
    /**
     * Interaction start flag.
     * @type Boolean
     */
    this.started = false;

    /**
     * Handle mouse down event.
     * @param {Object} event The mouse down event.
     */
    this.mousedown = function(event){
        // set start flag
        self.started = true;
        // store initial position
        self.x0 = event._x;
        self.y0 = event._y;
        // update GUI
        app.getViewController().setCurrentPosition2D(event._x, event._y);
    };

    /**
     * Handle mouse move event.
     * @param {Object} event The mouse move event.
     */
    this.mousemove = function(event){
        // check start flag
        if( !self.started ) {
            return;
        }
        // difference to last position
        var diffX = event._x - self.x0;
        var diffY = self.y0 - event._y;
        // calculate new window level
        var windowCenter = parseInt(app.getViewController().getWindowLevel().center, 10) + diffY;
        var windowWidth = parseInt(app.getViewController().getWindowLevel().width, 10) + diffX;

        // add the manual preset to the view
        app.getViewController().addWindowLevelPresets( { "manual": {
            "wl": new dwv.image.WindowLevel(windowCenter, windowWidth),
            "name": "manual"} } );
        app.getViewController().setWindowLevelPreset("manual");

        // update gui
        if ( gui ) {
            // initialise to add the manual preset
            gui.initialise();
            // set selected preset
            dwv.gui.setSelected(app.getElement("presetSelect"), "manual");
        }

        // store position
        self.x0 = event._x;
        self.y0 = event._y;
    };

    /**
     * Handle mouse up event.
     * @param {Object} event The mouse up event.
     */
    this.mouseup = function(/*event*/){
        // set start flag
        if( self.started ) {
            self.started = false;
        }
    };

    /**
     * Handle mouse out event.
     * @param {Object} event The mouse out event.
     */
    this.mouseout = function(event){
        // treat as mouse up
        self.mouseup(event);
    };

    /**
     * Handle touch start event.
     * @param {Object} event The touch start event.
     */
    this.touchstart = function(event){
        self.mousedown(event);
    };

    /**
     * Handle touch move event.
     * @param {Object} event The touch move event.
     */
    this.touchmove = function(event){
        self.mousemove(event);
    };

    /**
     * Handle touch end event.
     * @param {Object} event The touch end event.
     */
    this.touchend = function(event){
        self.mouseup(event);
    };

    /**
     * Handle double click event.
     * @param {Object} event The double click event.
     */
    this.dblclick = function(event){
        // update GUI
        app.getViewController().setWindowLevel(
            parseInt(app.getImage().getRescaledValue(
                event._x, event._y, app.getViewController().getCurrentPosition().k), 10),
            parseInt(app.getViewController().getWindowLevel().width, 10) );
    };

    /**
     * Handle key down event.
     * @param {Object} event The key down event.
     */
    this.keydown = function(event){
        // let the app handle it
        app.onKeydown(event);
    };

    /**
     * Setup the tool GUI.
     */
    this.setup = function ()
    {
        gui = new dwv.gui.WindowLevel(app);
        gui.setup();
    };

    /**
     * Display the tool.
     * @param {Boolean} bool The flag to display or not.
     */
    this.display = function (bool)
    {
        if ( gui )
        {
            if( app.getImage().getPhotometricInterpretation().match(/MONOCHROME/) !== null ) {
                gui.display(bool);
            }
            else {
                gui.display(false);
            }
        }
    };

    /**
     * Initialise the tool.
     */
    this.init = function() {
        if ( gui ) {
            gui.initialise();
        }
        return true;
    };
}; // WindowLevel class

/**
 * Help for this tool.
 * @return {Object} The help content.
 */
dwv.tool.WindowLevel.prototype.getHelp = function()
{
    return {
        "title": dwv.i18n("tool.WindowLevel.name"),
        "brief": dwv.i18n("tool.WindowLevel.brief"),
        "mouse": {
            "mouse_drag": dwv.i18n("tool.WindowLevel.mouse_drag"),
            "double_click": dwv.i18n("tool.WindowLevel.double_click")
        },
        "touch": {
            "touch_drag": dwv.i18n("tool.WindowLevel.touch_drag")
        }
    };
};

// namespaces
var dwv = dwv || {};
dwv.tool = dwv.tool || {};

/**
 * ZoomAndPan class.
 * @constructor
 * @param {Object} app The associated application.
 */
dwv.tool.ZoomAndPan = function(app)
{
    /**
     * Closure to self: to be used by event handlers.
     * @private
     * @type Object
     */
    var self = this;
    /**
     * ZoomAndPan GUI.
     * @type Object
     */
    var gui = null;
    /**
     * Interaction start flag.
     * @type Boolean
     */
    this.started = false;

    /**
     * Handle mouse down event.
     * @param {Object} event The mouse down event.
     */
    this.mousedown = function(event){
        self.started = true;
        // first position
        self.x0 = event._xs;
        self.y0 = event._ys;
    };

    /**
     * Handle two touch down event.
     * @param {Object} event The touch down event.
     */
    this.twotouchdown = function(event){
        self.started = true;
        // store first point
        self.x0 = event._x;
        self.y0 = event._y;
        // first line
        var point0 = new dwv.math.Point2D(event._x, event._y);
        var point1 = new dwv.math.Point2D(event._x1, event._y1);
        self.line0 = new dwv.math.Line(point0, point1);
        self.midPoint = self.line0.getMidpoint();
    };

    /**
     * Handle mouse move event.
     * @param {Object} event The mouse move event.
     */
    this.mousemove = function(event){
        if (!self.started)
        {
            return;
        }
        // calculate translation
        var tx = event._xs - self.x0;
        var ty = event._ys - self.y0;
        // apply translation
        //app.translate(tx, ty);
        app.stepTranslate(tx, ty);
        // reset origin point
        self.x0 = event._xs;
        self.y0 = event._ys;
    };

    /**
     * Handle two touch move event.
     * @param {Object} event The touch move event.
     */
    this.twotouchmove = function(event){
        if (!self.started)
        {
            return;
        }
        var point0 = new dwv.math.Point2D(event._x, event._y);
        var point1 = new dwv.math.Point2D(event._x1, event._y1);
        var newLine = new dwv.math.Line(point0, point1);
        var lineRatio = newLine.getLength() / self.line0.getLength();

        if( lineRatio === 1 )
        {
            // scroll mode
            // difference  to last position
            var diffY = event._y - self.y0;
            // do not trigger for small moves
            if( Math.abs(diffY) < 15 ) {
                return;
            }
            // update GUI
            if( diffY > 0 ) {
                app.getViewController().incrementSliceNb();
            }
            else {
                app.getViewController().decrementSliceNb();
            }
        }
        else
        {
            // zoom mode
            var zoom = (lineRatio - 1) / 2;
            if( Math.abs(zoom) % 0.1 <= 0.05 ) {
                app.stepZoom(zoom, event._xs, event._ys);
            }
        }
    };

    /**
     * Handle mouse up event.
     * @param {Object} event The mouse up event.
     */
    this.mouseup = function(/*event*/){
        if (self.started)
        {
            // stop recording
            self.started = false;
        }
    };

    /**
     * Handle mouse out event.
     * @param {Object} event The mouse out event.
     */
    this.mouseout = function(event){
        self.mouseup(event);
    };

    /**
     * Handle touch start event.
     * @param {Object} event The touch start event.
     */
    this.touchstart = function(event){
        var touches = event.targetTouches;
        if( touches.length === 1 ){
            self.mousedown(event);
        }
        else if( touches.length === 2 ){
            self.twotouchdown(event);
        }
    };

    /**
     * Handle touch move event.
     * @param {Object} event The touch move event.
     */
    this.touchmove = function(event){
        var touches = event.targetTouches;
        if( touches.length === 1 ){
            self.mousemove(event);
        }
        else if( touches.length === 2 ){
            self.twotouchmove(event);
        }
    };

    /**
     * Handle touch end event.
     * @param {Object} event The touch end event.
     */
    this.touchend = function(event){
        self.mouseup(event);
    };

    /**
     * Handle mouse scroll event (fired by Firefox).
     * @param {Object} event The mouse scroll event.
     */
    this.DOMMouseScroll = function(event){
        // ev.detail on firefox is 3
        var step = - event.detail / 30;
        app.stepZoom(step, event._xs, event._ys);
    };

    /**
     * Handle mouse wheel event.
     * @param {Object} event The mouse wheel event.
     */
    this.mousewheel = function(event){
        // ev.wheelDelta on chrome is 120
        var step = event.wheelDelta / 1200;
        app.stepZoom(step, event._xs, event._ys);
    };

    /**
     * Handle key down event.
     * @param {Object} event The key down event.
     */
    this.keydown = function(event){
        app.onKeydown(event);
    };

    /**
     * Setup the tool GUI.
     */
    this.setup = function ()
    {
        gui = new dwv.gui.ZoomAndPan(app);
        gui.setup();
    };

    /**
     * Enable the tool.
     * @param {Boolean} bool The flag to enable or not.
     */
    this.display = function(bool){
        if ( gui ) {
            gui.display(bool);
        }
    };

}; // ZoomAndPan class

/**
 * Help for this tool.
 * @return {Object} The help content.
 */
dwv.tool.ZoomAndPan.prototype.getHelp = function()
{
    return {
        "title": dwv.i18n("tool.ZoomAndPan.name"),
        "brief": dwv.i18n("tool.ZoomAndPan.brief"),
        "mouse": {
            "mouse_wheel": dwv.i18n("tool.ZoomAndPan.mouse_wheel"),
            "mouse_drag": dwv.i18n("tool.ZoomAndPan.mouse_drag")
        },
        "touch": {
            'twotouch_pinch': dwv.i18n("tool.ZoomAndPan.twotouch_pinch"),
            'touch_drag': dwv.i18n("tool.ZoomAndPan.touch_drag")
        }
    };
};

/**
 * Initialise the tool.
 */
dwv.tool.ZoomAndPan.prototype.init = function() {
    return true;
};

// namespaces
var dwv = dwv || {};
/** @namespace */
dwv.browser = dwv.browser || {};
// external
var Modernizr = Modernizr || {};

/**
 * Browser check for the FileAPI.
 * Assume support for Safari5.
 */
dwv.browser.hasFileApi = function()
{
    // regular test does not work on Safari 5
    var isSafari5 = (navigator.appVersion.indexOf("Safari") !== -1) &&
        (navigator.appVersion.indexOf("Chrome") === -1) &&
        ( (navigator.appVersion.indexOf("5.0.") !== -1) ||
          (navigator.appVersion.indexOf("5.1.") !== -1) );
    if( isSafari5 )
    {
        console.warn("Assuming FileAPI support for Safari5...");
        return true;
    }
    // regular test
    return Modernizr.filereader;
};

/**
 * Browser check for the XMLHttpRequest.
 */
dwv.browser.hasXmlHttpRequest = function()
{
    return Modernizr.xhrresponsetype &&
        Modernizr.xhrresponsetypearraybuffer && Modernizr.xhrresponsetypetext &&
        "XMLHttpRequest" in window && "withCredentials" in new XMLHttpRequest();
};

/**
 * Browser check for typed array.
 */
dwv.browser.hasTypedArray = function()
{
    return Modernizr.dataview && Modernizr.typedarrays;
};

/**
 * Browser check for input with type='color'.
 * Missing in IE and Safari.
 */
dwv.browser.hasInputColor = function()
{
    return Modernizr.inputtypes.color;
};

//only check at startup (since we propose a replacement)
dwv.browser._hasTypedArraySlice = (typeof Uint8Array.prototype.slice !== "undefined");

/**
 * Browser check for typed array slice method.
 * Missing in Internet Explorer 11.
 */
dwv.browser.hasTypedArraySlice = function()
{
    return dwv.browser._hasTypedArraySlice;
};

// only check at startup (since we propose a replacement)
dwv.browser._hasFloat64Array = ("Float64Array" in window);

/**
 * Browser check for Float64Array array.
 * Missing in PhantomJS 1.9.20 (on Travis).
 */
dwv.browser.hasFloat64Array = function()
{
    return dwv.browser._hasFloat64Array;
};

//only check at startup (since we propose a replacement)
dwv.browser._hasClampedArray = ("Uint8ClampedArray" in window);

/**
 * Browser check for clamped array.
 * Missing in:
 * - Safari 5.1.7 for Windows
 * - PhantomJS 1.9.20 (on Travis).
 */
dwv.browser.hasClampedArray = function()
{
    return dwv.browser._hasClampedArray;
};

/**
 * Browser checks to see if it can run dwv. Throws an error if not.
 * Silently replaces basic functions.
 */
dwv.browser.check = function()
{

    // Required --------------

    var appnorun = "The application cannot be run.";
    var message = "";
    // Check for the File API support
    if( !dwv.browser.hasFileApi() ) {
        message = "The File APIs are not supported in this browser. ";
        alert(message+appnorun);
        throw new Error(message);
    }
    // Check for XMLHttpRequest
    if( !dwv.browser.hasXmlHttpRequest() ) {
        message = "The XMLHttpRequest is not supported in this browser. ";
        alert(message+appnorun);
        throw new Error(message);
    }
    // Check typed array
    if( !dwv.browser.hasTypedArray() ) {
        message = "The Typed arrays are not supported in this browser. ";
        alert(message+appnorun);
        throw new Error(message);
    }

    // Replaced if not present ------------

    // Check typed array slice
    if( !dwv.browser.hasTypedArraySlice() ) {
        // silent fail with warning
        console.warn("The TypedArray.slice method is not supported in this browser. This may impair performance. ");
        // basic Uint16Array implementation
        Uint16Array.prototype.slice = function (begin, end) {
            var size = end - begin;
            var cloned = new Uint16Array(size);
            for (var i = 0; i < size; i++) {
                cloned[i] = this[begin + i];
            }
            return cloned;
        };
        // basic Int16Array implementation
        Int16Array.prototype.slice = function (begin, end) {
            var size = end - begin;
            var cloned = new Int16Array(size);
            for (var i = 0; i < size; i++) {
                cloned[i] = this[begin + i];
            }
            return cloned;
        };
        // basic Uint8Array implementation
        Uint8Array.prototype.slice = function (begin, end) {
            var size = end - begin;
            var cloned = new Uint8Array(size);
            for (var i = 0; i < size; i++) {
                cloned[i] = this[begin + i];
            }
            return cloned;
        };
        // basic Int8Array implementation
        Int8Array.prototype.slice = function (begin, end) {
            var size = end - begin;
            var cloned = new Int8Array(size);
            for (var i = 0; i < size; i++) {
                cloned[i] = this[begin + i];
            }
            return cloned;
        };
    }
    // check clamped array
    if( !dwv.browser.hasClampedArray() ) {
        // silent fail with warning
        console.warn("The Uint8ClampedArray is not supported in this browser. This may impair performance. ");
        // Use Uint8Array instead... Not good
        // TODO Find better replacement!
        window.Uint8ClampedArray = window.Uint8Array;
    }
    // check Float64 array
    if( !dwv.browser.hasFloat64Array() ) {
        // silent fail with warning
        console.warn("The Float64Array is not supported in this browser. This may impair performance. ");
        // Use Float32Array instead... Not good
        // TODO Find better replacement!
        window.Float64Array = window.Float32Array;
    }
};

// namespaces
var dwv = dwv || {};
// external
var i18next = i18next || {};
var i18nextXHRBackend = i18nextXHRBackend || {};
var i18nextBrowserLanguageDetector = i18nextBrowserLanguageDetector || {};

// This is mainly a wrapper around the i18next object.
// see its API: http://i18next.com/docs/api/

// global locales path
dwv.i18nLocalesPath = null;

/**
 * Initialise i18n.
 * @param {String} language The language to translate to. Defaults to 'auto' and
 *   gets the language from the browser.
 * @param {String} localesPath Path to the locales directory.
 * @external i18next
 * @external i18nextXHRBackend
 * @external i18nextBrowserLanguageDetector
 */
dwv.i18nInitialise = function (language, localesPath)
{
    var lng = (typeof language === "undefined") ? "auto" : language;
    var lpath = (typeof localesPath === "undefined") ? "../.." : localesPath;
    // store as global
    dwv.i18nLocalesPath = lpath;
    // i18n options: default 'en' language and
    //  only load language, not specialised (for ex en-GB)
    var options = {
        fallbackLng: "en",
        load: "languageOnly",
        backend: { loadPath: lpath + "/locales/{{lng}}/{{ns}}.json" }
    };
    // use the XHR backend to get translation files
    var i18n = i18next.use(i18nextXHRBackend);
    // use browser language or the specified one
    if (lng === "auto") {
        i18n.use(i18nextBrowserLanguageDetector);
    }
    else {
        options.lng = lng;
    }
    // init i18n: will be ready when the 'loaded' event is fired
    i18n.init(options);
};

/**
 * Handle i18n load event.
 * @param {Object} callback The callback function to call when i18n is loaded.
 *  It can take one argument that will be replaced with the loaded languages.
 * @external i18next
 */
dwv.i18nOnLoaded = function (callback) {
    i18next.on('loaded', callback);
};

/**
 * Stop handling i18n load event.
 * @external i18next
 */
dwv.i18nOffLoaded = function () {
    i18next.off('loaded');
};

/**
 * Handle i18n failed load event.
 * @param {Object} callback The callback function to call when i18n is loaded.
 *  It can take three arguments: lng, ns and msg.
 * @external i18next
 */
dwv.i18nOnFailedLoad = function (callback) {
    i18next.on('failedLoading', callback);
};

/**
 * Stop handling i18n failed load event.
 * @external i18next
 */
dwv.i18nOffFailedLoad = function () {
    i18next.off('failedLoading');
};

/**
 * Get the translated text.
 * @param {String} key The key to the text entry.
 * @param {Object} options The translation options such as plural, context...
 * @external i18next
 */
dwv.i18n = function (key, options) {
    return i18next.t(key, options);
};

/**
 * Check the existence of a translation.
 * @param {String} key The key to the text entry.
 * @param {Object} options The translation options such as plural, context...
 * @external i18next
 */
dwv.i18nExists = function (key, options) {
    return i18next.exists(key, options);
};

/**
 * Translate all data-i18n tags in the current html page. If an html tag defines the
 * data-i18n attribute, its value will be used as key to find its corresponding text
 * and will replace the content of the html tag.
 */
dwv.i18nPage = function () {
    // get all elements
    var elements = document.getElementsByTagName("*");
    // if the element defines data-i18n, replace its content with the tranlation
    for (var i = 0; i < elements.length; ++i) {
        if (typeof elements[i].dataset.i18n !== "undefined") {
            elements[i].innerHTML = dwv.i18n(elements[i].dataset.i18n);
        }
    }
};

/**
 * Get the current locale resource path.
 * Warning: to be used once i18next is initialised.
 * @return {String} The path to the locale resource.
 */
dwv.i18nGetLocalePath = function (filename) {
    var lng = i18next.language.substr(0, 2);
    return dwv.i18nLocalesPath +
        "/locales/" + lng + "/" + filename;
};

/**
 * Get the current locale resource path.
 * Warning: to be used once i18next is initialised.
 * @return {String} The path to the locale resource.
 */
dwv.i18nGetFallbackLocalePath = function (filename) {
    var lng = i18next.languages[i18next.languages.length-1].substr(0, 2);
    return dwv.i18nLocalesPath +
        "/locales/" + lng + "/" + filename;
};

// namespaces
var dwv = dwv || {};
dwv.utils = dwv.utils || {};

/**
  * ListenerHandler class: handles add/removing and firing listeners.
  * @constructor
 */
dwv.utils.ListenerHandler = function ()
{
    /**
     * listeners.
     * @private
     */
    var listeners = {};

    /**
     * Add an event listener.
     * @param {String} type The event type.
     * @param {Object} callback The method associated with the provided event type,
     *    will be called with the fired event.
     */
    this.add = function (type, callback)
    {
        // create array if not present
        if ( typeof listeners[type] === "undefined" ) {
            listeners[type] = [];
        }
        // add callback to listeners array
        listeners[type].push(callback);
    };

    /**
     * Remove an event listener.
     * @param {String} type The event type.
     * @param {Object} callback The method associated with the provided event type.
     */
    this.remove = function (type, callback)
    {
        // check if the type is present
        if( typeof listeners[type] === "undefined" ) {
            return;
        }
        // remove from listeners array
        for ( var i = 0; i < listeners[type].length; ++i ) {
            if ( listeners[type][i] === callback ) {
                listeners[type].splice(i,1);
            }
        }
    };

    /**
     * Fire an event: call all associated listeners with the input event object.
     * @param {Object} event The event to fire.
     */
    this.fireEvent = function (event)
    {
        // check if they are listeners for the event type
        if ( typeof listeners[event.type] === "undefined" ) {
            return;
        }
        // fire events
        for ( var i=0; i < listeners[event.type].length; ++i ) {
            listeners[event.type][i](event);
        }
    };
};

// namespaces
var dwv = dwv || {};
dwv.utils = dwv.utils || {};

/**
 * Multiple progresses handler.
 * Stores a multi dimensional list of progresses to allow to
 * calculate a global progress.
 * @param {Function} callback The function to pass the global progress to.
 */
dwv.utils.MultiProgressHandler = function (callback)
{
    // closure to self
    var self = this;

    /**
     * List of progresses.
     * @private
     * @type Array
     */
    var progresses = [];

    /**
     * Number of dimensions.
     * @private
     * @type Number
     */
    var numberOfDimensions = 2;

    /**
     * Set the number of dimensions.
     * @param {Number} num The number.
     */
    this.setNumberOfDimensions = function (num) {
        numberOfDimensions = num;
    };

    /**
     * Set the number of data to load.
     * @param {Number} n The number of data to load.
     */
    this.setNToLoad = function (n) {
        for ( var i = 0; i < n; ++i ) {
            progresses[i] = [];
            for ( var j = 0; j < numberOfDimensions; ++j ) {
                progresses[i][j] = 0;
            }
        }
    };

    /**
     * Handle a load progress.
     * @param {Object} event The progress event.
     */
    this.onprogress = function (event) {
        // check event
        if ( !event.lengthComputable ) {
            return;
        }
        if ( typeof event.subindex === "undefined" ) {
            return;
        }
        if ( typeof event.index === "undefined" ) {
            return;
        }
        // calculate percent
        var percent = (event.loaded * 100) / event.total;
        // set percent for index
        progresses[event.index][event.subindex] = percent;

        // call callback
        callback({'type': event.type, 'lengthComputable': true,
            'loaded': getGlobalPercent(), 'total': 100});
    };

    /**
     * Get the global load percent including the provided one.
     * @return {Number} The accumulated percentage.
     */
    function getGlobalPercent() {
        var sum = 0;
        var lenprog = progresses.length;
        for ( var i = 0; i < lenprog; ++i ) {
            for ( var j = 0; j < numberOfDimensions; ++j ) {
                sum += progresses[i][j];
            }
        }
        return Math.round( sum / (lenprog * numberOfDimensions) );
    }

    /**
     * Create a mono progress event handler.
     * @param {Number} index The index of the data.
     * @param {Number} subindex The sub-index of the data.
     */
    this.getMonoProgressHandler = function (index, subindex) {
        return function (event) {
            event.index = index;
            event.subindex = subindex;
            self.onprogress(event);
        };
    };

    /**
     * Create a mono loadend event handler: sends a 100% progress.
     * @param {Number} index The index of the data.
     * @param {Number} subindex The sub-index of the data.
     */
    this.getMonoOnLoadEndHandler = function (index, subindex) {
        return function () {
            self.onprogress({'type': 'load-progress', 'lengthComputable': true,
                'loaded': 100, 'total': 100,
                'index': index, 'subindex': subindex});
        };
    };

    /**
     * Create a mono progress event handler with an undefined index.
     * Warning: The caller handles the progress index.
     * @param {Number} subindex The sub-index of the data.
     */
    this.getUndefinedMonoProgressHandler = function (subindex) {
        return function (event) {
            event.subindex = subindex;
            self.onprogress(event);
        };
    };
};

// namespaces
var dwv = dwv || {};
/** @namespace */
dwv.utils = dwv.utils || {};

/**
 * Capitalise the first letter of a string.
 * @param {String} string The string to capitalise the first letter.
 * @return {String} The new string.
 */
dwv.utils.capitaliseFirstLetter = function (string)
{
    var res = string;
    if ( string ) {
        res = string.charAt(0).toUpperCase() + string.slice(1);
    }
    return res;
};

/**
 * Split key/value string:
 *  key0=val00&key0=val01&key1=val10 returns
 *  { key0 : [val00, val01], key1 : val1 }
 * @param {String} inputStr The string to split.
 * @return {Object} The split string.
 */
dwv.utils.splitKeyValueString = function (inputStr)
{
    // result
    var result = {};
    // check input string
    if ( inputStr ) {
         // split key/value pairs
        var pairs = inputStr.split('&');
        for ( var i = 0; i < pairs.length; ++i )
        {
            var pair = pairs[i].split('=');
            // if the key does not exist, create it
            if ( !result[pair[0]] )
            {
                result[pair[0]] = pair[1];
            }
            else
            {
                // make it an array
                if ( !( result[pair[0]] instanceof Array ) ) {
                    result[pair[0]] = [result[pair[0]]];
                }
                result[pair[0]].push(pair[1]);
            }
        }
    }
    return result;
};

/**
 * Replace flags in a input string. Flags are keywords surrounded with curly
 * braces.
 * @param {String} inputStr The input string.
 * @param {Object} values A object of {value, unit}.
 * @example
 *    var values = {"length": { "value": 33, "unit": "cm" } };
 *    var str = "The length is: {length}.";
 *    var res = dwv.utils.replaceFlags(str, values); // "The length is: 33 cm."
 * @return {String} The result string.
 */
dwv.utils.replaceFlags = function (inputStr, values)
{
    var res = "";
    // check input string
    if (inputStr === null || typeof inputStr === "undefined") {
        return res;
    }
    res = inputStr;
    // check values
    if (values === null || typeof values === "undefined") {
        return res;
    }
    // loop through values keys
    var keys = Object.keys(values);
    for (var i = 0; i < keys.length; ++i) {
        var valueObj = values[keys[i]];
        if ( valueObj !== null && typeof valueObj !== "undefined" &&
            valueObj.value !== null && typeof valueObj.value !== "undefined") {
            // value string
            var valueStr = valueObj.value.toPrecision(4);
            // add unit if available
            // space or no space? Yes apart from degree...
            // check: https://en.wikipedia.org/wiki/Space_(punctuation)#Spaces_and_unit_symbols
            if (valueObj.unit !== null && typeof valueObj.unit !== "undefined" &&
                valueObj.unit.length !== 0) {
                if (valueObj.unit !== "degree") {
                    valueStr += " ";
                }
                valueStr += valueObj.unit;
            }
            // flag to replace
            var flag = '{' + keys[i] + '}';
            // replace
            res = res.replace(flag, valueStr);
        }
    }
    // return
    return res;
};

/**
 * Replace flags in a input string. Flags are keywords surrounded with curly
 * braces.
 * @param {String} inputStr The input string.
 * @param {Array} values An array of strings.
 * @example
 *    var values = ["a", "b"];
 *    var str = "The length is: {v0}. The size is: {v1}";
 *    var res = dwv.utils.replaceFlags2(str, values); // "The length is: a. The size is: b"
 * @return {String} The result string.
 */
dwv.utils.replaceFlags2 = function (inputStr, values)
{
    var res = inputStr;
    for ( var j = 0; j < values.length; ++j ) {
        res = res.replace("{v"+j+"}", values[j]);
    }
    return res;
};

dwv.utils.createDefaultReplaceFormat = function (values)
{
    var res = "";
    for ( var j = 0; j < values.length; ++j ) {
        if ( j !== 0 ) {
            res += ", ";
        }
        res += "{v"+j+"}";
    }
    return res;
};

// namespaces
var dwv = dwv || {};
dwv.utils = dwv.utils || {};

/**
 * Thread Pool.
 * Highly inspired from {@link http://www.smartjava.org/content/html5-easily-parallelize-jobs-using-web-workers-and-threadpool}.
 * @constructor
 * @param {Number} size The size of the pool.
 */
dwv.utils.ThreadPool = function (size) {
    // closure to self
    var self = this;
    // task queue
    this.taskQueue = [];
    // worker queue
    this.workerQueue = [];
    // pool size
    this.poolSize = size;

    /**
     * Initialise.
     */
    this.init = function () {
        // create 'size' number of worker threads
        for (var i = 0; i < size; ++i) {
            self.workerQueue.push(new dwv.utils.WorkerThread(self));
        }
    };

    /**
     * Add a worker task to the queue.
     * Will be run when a thread is made available.
     * @return {Object} workerTask The task to add.
     */
    this.addWorkerTask = function (workerTask) {
        if (self.workerQueue.length > 0) {
            // get the worker thread from the front of the queue
            var workerThread = self.workerQueue.shift();
            workerThread.run(workerTask);
        } else {
            // no free workers, add to queue
            self.taskQueue.push(workerTask);
        }
    };

    /**
     * Free a worker thread.
     * @param {Object} workerThread The thread to free.
     */
    this.freeWorkerThread = function (workerThread) {
        self.onworkerend();
        if (self.taskQueue.length > 0) {
            // don't put back in queue, but execute next task
            var workerTask = self.taskQueue.shift();
            workerThread.run(workerTask);
        } else {
            // no task to run, add to queue
            self.workerQueue.push(workerThread);
            // the work is done when the queue is back to its initial size
            if ( self.workerQueue.length === size ) {
                self.onpoolworkend();
            }
        }
    };
};

/**
 * Handle a pool work end event.
 */
dwv.utils.ThreadPool.prototype.onpoolworkend = function ()
{
    // default does nothing.
};

/**
 * Handle a pool worker end event.
 */
dwv.utils.ThreadPool.prototype.onworkerend = function ()
{
    // default does nothing.
};

/**
 * Worker thread.
 * @external Worker
 * @constructor
 * @param {Object} parentPool The parent pool.
 */
dwv.utils.WorkerThread = function (parentPool) {
    // closure to self
    var self = this;
    // parent pool
    this.parentPool = parentPool;
    // associated task
    this.workerTask = {};
    // associated web worker
    var worker;

    /**
     * Run a worker task
     * @param {Object} workerTask The task to run.
     */
    this.run = function (workerTask) {
        // closure to task
        this.workerTask = workerTask;
        // create a new web worker
        if (this.workerTask.script !== null) {
            worker = new Worker(workerTask.script);
            worker.addEventListener('message', ontaskend, false);
            // launch the worker
            worker.postMessage(workerTask.startMessage);
        }
    };

    /**
     * Handle once the task is done.
     * For now assume we only get a single callback from a worker
     * which also indicates the end of this worker.
     * @param {Object} event The callback event.
     */
    function ontaskend(event) {
        // pass to original callback
        self.workerTask.callback(event);
        // stop the worker
        worker.terminate();
        // tell the parent pool this thread is free
        self.parentPool.freeWorkerThread(self);
    }

};

/**
 * Worker task.
 * @constructor
 * @param {String} script The worker script.
 * @param {Function} parentPool The worker callback.
 * @param {Object} message The data to pass to the worker.
 */
dwv.utils.WorkerTask = function (script, callback, message) {
    // worker script
    this.script = script;
    // worker callback
    this.callback = callback;
    // worker start message
    this.startMessage = message;
};

// namespaces
var dwv = dwv || {};
dwv.utils = dwv.utils || {};
/** @namespace */
dwv.utils.base = dwv.utils.base || {};

/**
 * Split an input URI:
 *  'root?key0=val00&key0=val01&key1=val10' returns
 *  { base : root, query : [ key0 : [val00, val01], key1 : val1 ] }
 * Returns an empty object if the input string is not correct (null, empty...)
 *  or if it is not a query string (no question mark).
 * @param {String} inputStr The string to split.
 * @return {Object} The split string.
 */
dwv.utils.splitUri = function (uri)
{
    // result
    var result = {};
    // check if query string
    var sepIndex = null;
    if ( uri && (sepIndex = uri.indexOf('?')) !== -1 ) {
        // base: before the '?'
        result.base = uri.substr(0, sepIndex);
        // query : after the '?' and until possible '#'
        var hashIndex = uri.indexOf('#');
        if ( hashIndex === -1 ) {
            hashIndex = uri.length;
        }
        var query = uri.substr(sepIndex + 1, (hashIndex - 1 - sepIndex));
        // split key/value pairs of the query
        result.query = dwv.utils.splitKeyValueString(query);
    }
    // return
    return result;
};

/**
 * Get the query part, split into an array, of an input URI.
 * The URI scheme is: 'base?query#fragment'
 * @param {String } uri The input URI.
 * @return {Object} The query part, split into an array, of the input URI.
 */
dwv.utils.getUriQuery = function (uri)
{
    // split
    var parts = dwv.utils.splitUri(uri);
    // check not empty
    if ( Object.keys(parts).length === 0 ) {
        return null;
    }
    // return query
    return parts.query;
};

/**
 * Generic URI query decoder.
 * Supports manifest:
 *   [dwv root]?input=encodeURIComponent('[manifest file]')&type=manifest
 * or encoded URI with base and key value/pairs:
 *   [dwv root]?input=encodeURIComponent([root]?key0=value0&key1=value1)
 *  @param {String} query The query part to the input URI.
 *  @param {Function} callback The function to call with the decoded file urls.
 */
dwv.utils.base.decodeQuery = function (query, callback)
{
    // manifest
    if ( query.type && query.type === "manifest" ) {
        dwv.utils.decodeManifestQuery( query, callback );
    }
    // default case: encoded URI with base and key/value pairs
    else {
        callback( dwv.utils.decodeKeyValueUri( query.input, query.dwvReplaceMode ) );
    }
};

/**
 * Decode a Key/Value pair URI. If a key is repeated, the result
 * be an array of base + each key.
 * @param {String} uri The URI to decode.
 * @param {String} replaceMode The key replace more.
 *   replaceMode can be:
 *   - key (default): keep the key
 *   - other than key: do not use the key
 *   'file' is a special case where the '?' of the query is not kept.
 * @return The list of input file urls.
 */
dwv.utils.decodeKeyValueUri = function (uri, replaceMode)
{
    var result = [];

    // repeat key replace mode (default to keep key)
    var repeatKeyReplaceMode = "key";
    if ( replaceMode ) {
        repeatKeyReplaceMode = replaceMode;
    }

    // decode input URI
    var queryUri = decodeURIComponent(uri);
    // get key/value pairs from input URI
    var inputQueryPairs = dwv.utils.splitUri(queryUri);
    if ( Object.keys(inputQueryPairs).length === 0 )
    {
        result.push(queryUri);
    }
    else
    {
        var keys = Object.keys(inputQueryPairs.query);
        // find repeat key
        var repeatKey = null;
        for ( var i = 0; i < keys.length; ++i )
        {
            if ( inputQueryPairs.query[keys[i]] instanceof Array )
            {
                repeatKey = keys[i];
                break;
            }
        }

        if ( !repeatKey )
        {
            result.push(queryUri);
        }
        else
        {
            var repeatList = inputQueryPairs.query[repeatKey];
            // build base uri
            var baseUrl = inputQueryPairs.base;
            // do not add '?' when the repeatKey is 'file'
            // root/path/to/?file=0.jpg&file=1.jpg
            if ( repeatKey !== "file" ) {
                baseUrl += "?";
            }
            var gotOneArg = false;
            for ( var j = 0; j < keys.length; ++j )
            {
                if ( keys[j] !== repeatKey ) {
                    if ( gotOneArg ) {
                        baseUrl += "&";
                    }
                    baseUrl += keys[j] + "=" + inputQueryPairs.query[keys[j]];
                    gotOneArg = true;
                }
            }
            // append built urls to result
            var url;
            for ( var k = 0; k < repeatList.length; ++k )
            {
                url = baseUrl;
                if ( gotOneArg ) {
                    url += "&";
                }
                if ( repeatKeyReplaceMode === "key" ) {
                    url += repeatKey + "=";
                }
                // other than 'key' mode: do nothing
                url += repeatList[k];
                result.push(url);
            }
        }
    }
    // return
    return result;
};

/**
 * Decode a manifest query.
 * @external XMLHttpRequest
 * @param {Object} query The manifest query: {input, nslices},
 *   with input the input URI and nslices the number of slices.
 * @param {Function} The function to call with the decoded urls.
 */
dwv.utils.decodeManifestQuery = function (query, callback)
{
    var uri = "";
    if ( query.input[0] === '/' ) {
        uri = window.location.protocol + "//" + window.location.host;
    }
    // TODO: needs to be decoded (decodeURIComponent?
    uri += query.input;

    // handle error
    var onError = function (/*event*/)
    {
        console.warn( "RequestError while receiving manifest: "+this.status );
    };

    // handle load
    var onLoad = function (/*event*/)
    {
        callback( dwv.utils.decodeManifest(this.responseXML, query.nslices) );
    };

    var request = new XMLHttpRequest();
    request.open('GET', decodeURIComponent(uri), true);
    request.responseType = "document";
    request.onload = onLoad;
    request.onerror = onError;
    request.send(null);
};

/**
 * Decode an XML manifest.
 * @param {Object} manifest The manifest to decode.
 * @param {Number} nslices The number of slices to load.
 */
dwv.utils.decodeManifest = function (manifest, nslices)
{
    var result = [];
    // wado url
    var wadoElement = manifest.getElementsByTagName("wado_query");
    var wadoURL = wadoElement[0].getAttribute("wadoURL");
    var rootURL = wadoURL + "?requestType=WADO&contentType=application/dicom&";
    // patient list
    var patientList = manifest.getElementsByTagName("Patient");
    if ( patientList.length > 1 ) {
        console.warn("More than one patient, loading first one.");
    }
    // study list
    var studyList = patientList[0].getElementsByTagName("Study");
    if ( studyList.length > 1 ) {
        console.warn("More than one study, loading first one.");
    }
    var studyUID = studyList[0].getAttribute("StudyInstanceUID");
    // series list
    var seriesList = studyList[0].getElementsByTagName("Series");
    if ( seriesList.length > 1 ) {
        console.warn("More than one series, loading first one.");
    }
    var seriesUID = seriesList[0].getAttribute("SeriesInstanceUID");
    // instance list
    var instanceList = seriesList[0].getElementsByTagName("Instance");
    // loop on instances and push links
    var max = instanceList.length;
    if ( nslices < max ) {
        max = nslices;
    }
    for ( var i = 0; i < max; ++i ) {
        var sopInstanceUID = instanceList[i].getAttribute("SOPInstanceUID");
        var link = rootURL +
        "&studyUID=" + studyUID +
        "&seriesUID=" + seriesUID +
        "&objectUID=" + sopInstanceUID;
        result.push( link );
    }
    // return
    return result;
};

    return dwv;
}));

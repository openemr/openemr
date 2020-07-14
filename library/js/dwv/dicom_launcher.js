/**
 * DWV (Dicom Web Viewer) application launcher
 * OpenEMR v5.0.2
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Victor Kofia <victor.kofia@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com> (complete rework 7/1/2020)
 * @copyright Copyright (c) 2016 Victor Kofia <victor.kofia@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * Application launcher.
 */
// namespaces
var dwvOemr = dwvOemr || {};

/**
 * Application launcher.
 */

// start app function
function startApp() {
    // gui setup
    dwvOemr.gui.setup();

    // show dwv version
    dwvOemr.gui.appendVersionHtml(dwv.getVersion());

    // initialise the application
    const loaderList = [
        "File",
        "Url"
    ];

    const filterList = [
        "Threshold",
        "Sharpen",
        "Sobel"
    ];

    const shapeList = [
        "Arrow",
        "Ruler",
        "Protractor",
        "Rectangle",
        "Roi",
        "Ellipse"
    ];

    const toolList = {
        "Scroll": {},
        "WindowLevel": {},
        "ZoomAndPan": {},
        "Draw": {
            options: shapeList,
            type: "factory",
            events: ["draw-create", "draw-change", "draw-move", "draw-delete"]
        },
        "Livewire": {
            events: ["draw-create", "draw-change", "draw-move", "draw-delete"]
        },
        "Filter": {
            options: filterList,
            type: "instance",
            events: ["filter-run", "filter-undo"]
        },
        "Floodfill": {
            events: ["draw-create", "draw-change", "draw-move", "draw-delete"]
        }
    };

    // initialise the application
    const options = {
        "containerDivId": "dwv",
        "gui": ["help", "undo"],
        "loaders": loaderList,
        "tools": toolList
    };
    if (dwv.env.hasInputDirectory()) {
        options.loaders.splice(1, 0, "Folder");
    }

    // main application
    const oemrApp = new dwv.App();
    oemrApp.init(options);

    // show help
    const isMobile = false;
    dwvOemr.gui.appendHelpHtml(
        oemrApp.getToolboxController().getToolList(),
        isMobile,
        oemrApp,
        "js/dwv/gui/resources/help");

    // setup the undo gui
    const undoGui = new dwvOemr.gui.Undo(oemrApp);
    undoGui.setup();

    // setup the dropbox loader
    const dropBoxLoader = new dwvOemr.gui.DropboxLoader(oemrApp);
    dropBoxLoader.init();

    // setup the loadbox gui
    const loadboxGui = new dwvOemr.gui.Loadbox(oemrApp);
    loadboxGui.setup(loaderList);

    // info layer
    const infoController = new dwvOemr.gui.info.Controller(oemrApp, "dwv");
    infoController.init();

    // setup the tool gui
    const toolboxGui = new dwvOemr.gui.ToolboxContainer(oemrApp, infoController);
    toolboxGui.setup(toolList);

    // setup the meta data gui
    const metaDataGui = new dwvOemr.gui.MetaData(oemrApp);

    // setup the draw list gui
    const drawListGui = new dwvOemr.gui.DrawList(oemrApp);
    drawListGui.init();

    // colour map
    const infocm = dwvOemr.gui.getElement("dwv", "infocm");
    var miniColourMap = null;
    if (infocm) {
        miniColourMap = new dwvOemr.gui.info.MiniColourMap(infocm, oemrApp);
    }

    // intensities plot
    const plot = dwvOemr.gui.getElement("dwv", "plot");
    var plotInfo = null;
    if (plot) {
        plotInfo = new dwvOemr.gui.info.Plot(plot, oemrApp);
    }

    // loading time listener
    const loadTimerListener = function (event) {
        if (event.type === "load-start") {
            console.time("load-data");
        } else if (event.type === "load-end") {
            console.timeEnd("load-data");
        }
    };
    // abort shortcut listener
    const abortOnCrtlX = function (event) {
        if (event.ctrlKey && event.keyCode === 88) { // crtl-x
            console.log("Abort load received from user (crtl-x).");
            oemrApp.abortLoad();
        }
    };

    // handle load events
    var nReceivedLoadItem = null;
    var nReceivedError = null;
    var nReceivedAbort = null;
    oemrApp.addEventListener("load-start", function (event) {
        loadTimerListener(event);
        // reset counts
        nReceivedLoadItem = 0;
        nReceivedError = 0;
        nReceivedAbort = 0;
        // reset progress bar
        dwvOemr.gui.displayProgress(0);
        // allow to cancel via crtl-x
        window.addEventListener("keydown", abortOnCrtlX);
    });
    oemrApp.addEventListener("load-progress", function (event) {
        let percent = Math.ceil((event.loaded / event.total) * 100);
        dwvOemr.gui.displayProgress(percent);
    });
    oemrApp.addEventListener('load-item', function (event) {
        ++nReceivedLoadItem;
        // add new meta data to the info controller
        if (event.loadtype === "image") {
            infoController.onLoadItem(event);
        }
        // hide drop box (for url load)
        dropBoxLoader.hideDropboxElement();
        // initialise and display the toolbox
        toolboxGui.initialise();
        toolboxGui.display(true);
    });
    oemrApp.addEventListener('load', function (event) {
        // update info controller
        if (event.loadtype === "image") {
            infoController.onLoadEnd();
        }
        // initialise undo gui
        undoGui.setup();
        // update meta data table
        metaDataGui.update(oemrApp.getMetaData());

        // create colour map (if present)
        if (miniColourMap) {
            miniColourMap.create();
        }
        // create plot info (if present)
        if (plotInfo) {
            plotInfo.create();
        }
    });
    oemrApp.addEventListener("error", function (event) {
        console.error("load error", event);
        console.error(event.error);
        ++nReceivedError;
    });
    oemrApp.addEventListener("abort", function () {
        ++nReceivedAbort;
    });
    oemrApp.addEventListener("load-end", function (event) {
        loadTimerListener(event);
        // show the drop box if no item were received
        if (nReceivedLoadItem === 0) {
            dropBoxLoader.showDropboxElement();
        }
        // show alert for errors
        if (nReceivedError !== 0) {
            var message = "A load error has ";
            if (nReceivedError > 1) {
                message = nReceivedError + " load errors have ";
            }
            message += "occured. See log for details.";
            alert(message);
        }
        // console warn for aborts
        if (nReceivedAbort !== 0) {
            console.warn("Data load was aborted.");
        }
        // stop listening for crtl-x
        window.removeEventListener("keydown", abortOnCrtlX);
        // hide the progress bar
        dwvOemr.gui.displayProgress(100);
        toggle('loaderlist');
    });

    // handle undo/redo
    oemrApp.addEventListener("undo-add", function (event) {
        undoGui.addCommandToUndoHtml(event.command);
    });
    oemrApp.addEventListener("undo", function () {
        undoGui.enableLastInUndoHtml(false);
    });
    oemrApp.addEventListener("redo", function () {
        undoGui.enableLastInUndoHtml(true);
    });

    // handle key events
    oemrApp.addEventListener("keydown", function (event) {
        oemrApp.defaultOnKeydown(event);
    });

    // handle window resize
    // WARNING: will fail if the resize happens and the image is not shown
    // (for example resizing while viewing the meta data table)

    // @todo resize not work correctly. really should be able to resize canvas from UI.
    //window.addEventListener('resize', oemrApp.onResize);

    if (miniColourMap) {
        oemrApp.addEventListener('wl-width-change', miniColourMap.update);
        oemrApp.addEventListener('wl-center-change', miniColourMap.update);
        oemrApp.addEventListener('colour-change', miniColourMap.update);
    }
    if (plotInfo) {
        oemrApp.addEventListener('wl-width-change', plotInfo.update);
        oemrApp.addEventListener('wl-center-change', plotInfo.update);
    }

    // if controller.php url for doc fetch
    if (dwvOemr.oemrDocumentUrl) {
        toggle('loaderlist'); // shows progress of file load.
        oemrApp.loadURLs([dwvOemr.oemrDocumentUrl]);
    } else {
        // possible load from location
        dwv.utils.loadFromUri(window.location.href, oemrApp);
    }
}

// Image decoders (for web workers)
dwv.image.decoderScripts = {
    "jpeg2000": "./../public/assets/dwv/decoders/pdfjs/decode-jpeg2000.js",
    "jpeg-lossless": "./../public/assets/dwv/decoders/rii-mango/decode-jpegloss.js",
    "jpeg-baseline": "./../public/assets/dwv/decoders/pdfjs/decode-jpegbaseline.js",
    "rle": "./../public/assets/dwv/decoders/dwv/decode-rle.js"
};

// status flags
var domContentLoaded = false;
var i18nInitialised = false;

// launch when both DOM and i18n are ready
function launchApp() {
    if (domContentLoaded && i18nInitialised) {
        startApp();
    }
}

// i18n ready?
dwv.i18nOnInitialised(function () {
    // call next once the overlays are loaded
    const onLoaded = function (data) {
        dwvOemr.gui.info.overlayMaps = data;
        i18nInitialised = true;
        launchApp();
    };
    // load overlay map info
    $.getJSON(dwv.i18nGetLocalePath("overlays.json"), onLoaded).fail(function () {
        console.log("Using fallback overlays.");
        $.getJSON(dwv.i18nGetFallbackLocalePath("overlays.json"), onLoaded);
    });
});

// check environment support
dwv.env.check();
// initialise i18n
dwv.i18nInitialise();

// DOM ready?
document.addEventListener("DOMContentLoaded", function () {
    domContentLoaded = true;
    launchApp();
});

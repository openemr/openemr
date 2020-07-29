/**
 * DWV (Dicom Web Viewer) application launcher
 * OpenEMR v5.0.2
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com> (complete rework 7/1/2020)
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function startApp() {
    // gui setup
    if ($("#dwv").attr("src")) {
        dwv.utils.getUrlFromUri = dwv.utils.getUrlFromUri;
    }
    // initialise the application
    const loaderList = [
        "File",
        "Url"
    ];

    // initialise the application
    var options = {
        "containerDivId": "dwv",
        "gui": ["tool", "load", "help", "tags", "drawList", "undo"],
        "loaders": loaderList,
        "tools": ["Scroll", "WindowLevel", "ZoomAndPan", "Draw", "Livewire", "Filter", "Floodfill"],
        "filters": ["Threshold", "Sharpen", "Sobel"],
        "shapes": ["Arrow", "Ruler", "Protractor", "Rectangle", "Roi", "Ellipse", "FreeHand"],
        "fitToWindow": true,
        "helpResourcesPath": "js/dwv/gui/resources/help"
    };
    if (dwv.browser.hasInputDirectory()) {
        options.loaders.splice(1, 0, "Folder");
    }

    // main application
    const oemrApp = new dwv.App();
    oemrApp.init(options);

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
        dwv.gui.displayProgress(0);
        // allow to cancel via crtl-x
        window.addEventListener("keydown", abortOnCrtlX);
    });
    oemrApp.addEventListener("load-progress", function (event) {
        let percent = Math.ceil((event.loaded / event.total) * 100);
        dwv.gui.displayProgress(percent);
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
            // dropBoxLoader.showDropboxElement();
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
        dwv.gui.displayProgress(100);
        toggle('loaderlist');
    });

    // handle key events
    oemrApp.addEventListener("keydown", function (event) {
        oemrApp.defaultOnKeydown(event);
    });

    // handle window resize;
    /*window.addEventListener('resize', (event) => {
        //
    });*/

    // if controller.php url for doc fetch
    if (dwv.oemrDocumentUrl) {
        toggle('loaderlist'); // shows progress of file load.
        oemrApp.loadURLs([dwv.oemrDocumentUrl]);
    } else {
        // possible load from location
    }
    dwv.gui.appendResetHtml(oemrApp);
}

// Image decoders (for web workers)
dwv.image.decoderScripts = {
    "jpeg2000": "./js/dwv/assets/dwv/decoders/pdfjs/decode-jpeg2000.js",
    "jpeg-lossless": "./js/dwv/assets/dwv/decoders/rii-mango/decode-jpegloss.js",
    "jpeg-baseline": "./js/dwv/assets/dwv/decoders/pdfjs/decode-jpegbaseline.js",
    "rle": "./js/dwv/assets/dwv/decoders/dwv/decode-rle.js"
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
        dwv.gui.info.overlayMaps = data;
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
dwv.browser.check();
// initialise i18n
dwv.i18nInitialise();

// DOM ready?
document.addEventListener("DOMContentLoaded", function () {
    domContentLoaded = true;
    launchApp();
});

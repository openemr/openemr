/**
 * DWV (Dicom Web Viewer) application launcher
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Victor Kofia <victor.kofia@gmail.com>
 * @copyright Copyright (c) 2016 Victor Kofia <victor.kofia@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * Application launcher.
 */

// start app function
function startApp() {
    // main application
    var myapp = new dwv.App();
    // initialise the application
    myapp.init({
        "containerDivId": "dwv",
        "fitToWindow": true,
        "gui": ["tool"],
        "tools": ["ZoomAndPan", "WindowLevel", "Scroll"],
        "isMobile": false
    });
    var base_url = $("#dwv").attr("src");
    myapp.loadURLs([base_url]);
    dwv.gui.appendResetHtml(myapp);
}

// Image decoders (for web workers)
dwv.image.decoderScripts = {
    "jpeg2000": "./../public/assets/dwv-0-21-0/decoders/pdfjs/decode-jpeg2000.js",
    "jpeg-lossless": "./../public/assets/dwv-0-21-0/decoders/rii-mango/decode-jpegloss.js",
    "jpeg-baseline": "./../public/assets/dwv-0-21-0/decoders/pdfjs/decode-jpegbaseline.js"
};

// status flags
var domContentLoaded = false;
var i18nInitialised = false;
// launch when both DOM and i18n are ready
function launchApp() {
    if ( domContentLoaded && i18nInitialised ) {
        startApp();
    }
}
// i18n ready?
dwv.i18nOnInitialised( function () {
    i18nInitialised = true;
    launchApp();
});

// check browser support
dwv.browser.check();
// initialise i18n
dwv.i18nInitialise();

// DOM ready?
document.addEventListener("DOMContentLoaded", function (/*event*/) {
    domContentLoaded = true;
    launchApp();
});

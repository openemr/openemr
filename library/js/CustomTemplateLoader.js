/**
 * custom templates dynamic support loader
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019-2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Target dom of active page
window.onload = function (event) {
    const isAny = document.querySelectorAll("textarea, input[type='text']");
    if (isAny === null || isAny === 'undefined') {
        // no reason to load
        console.log("Templates Api Support not required for: ['" + location + "']");
        return false;
    }

    // we'll always be assured that bootstrap and jquery exist.
    if (typeof dlgopen === 'undefined' || typeof dlgopen !== 'function') {
        const script = document.createElement('script');
        script.onload = function () {
            console.log("Needed to load dialog.js support for: ['" + location + "']");
        };
        script.src = top.webroot_url + "/library/dialog.js";
        document.head.appendChild(script);
    }

    if (typeof bindTextArea === 'undefined') {
        const script = document.createElement('script');
        script.onload = function () {
            bindTextArea();
        };
        script.src = top.webroot_url + "/library/js/CustomTemplateApi.js";
        document.head.appendChild(script);
    }
};

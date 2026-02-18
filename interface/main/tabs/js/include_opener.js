/**
 * This is code needed to connect the iframe for a dialog back to the window which makes the call.
 * It is necessary to include this script at the "top" of any php file that is used as a dialog.
 * It was not possible to inject this code at "document ready" because sometimes the opened dialog
 * has a redirect or a close before the document ever becomes ready.
 *
 * Reworked to be used in both frames and tabs u.i.. sjp 12/01/17
 * Removed legacy dialog support. sjp 12/16/17
 * All window.close() should be removed from scripts and replaced with dlgclose() where possible
 * usually anywhere dlgopen() is used. Also, top.dlgclose and parent.dlgclose() is available.
 *
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

if (!opener) {
    /* eslint-disable-next-line no-global-assign */
    opener = top.get_opener(window.name);
}

window.close =
    function (call, args) {
        var frameName = window.name;
        var wframe = top;
        var dialogModal = top.$('div#' + frameName);

        var removeFrame = dialogModal.find("iframe[name='" + frameName + "']");
        if (removeFrame.length > 0) {
            removeFrame.remove();
        }

        if (dialogModal.length > 0) {
            if(call){
                wframe.setCallBack(call, args);
            }
            var bsRef = (typeof top !== 'undefined' && top.bootstrap && top.bootstrap.Modal) ? top.bootstrap : (typeof bootstrap !== 'undefined' ? bootstrap : null);
            if (bsRef && bsRef.Modal && typeof bsRef.Modal.getInstance === 'function') {
                var modalInstance = bsRef.Modal.getInstance(dialogModal[0]);
                if (modalInstance) { modalInstance.hide(); }
            }
        }

    };

var dlgclose =
    function (call, args) {
        var frameName = window.name;
        var wframe = top;
        var dialogModal = top.$('div#' + frameName);

        var removeFrame = dialogModal.find("iframe[name='" + frameName + "']");
        if (removeFrame.length > 0) {
            removeFrame.remove();
        }

        if (dialogModal.length > 0) {
            if(call){
                wframe.setCallBack(call, args);
            }
            var bsRef = (typeof top !== 'undefined' && top.bootstrap && top.bootstrap.Modal) ? top.bootstrap : (typeof bootstrap !== 'undefined' ? bootstrap : null);
            if (bsRef && bsRef.Modal && typeof bsRef.Modal.getInstance === 'function') {
                var modalInstance = bsRef.Modal.getInstance(dialogModal[0]);
                if (modalInstance) { modalInstance.hide(); }
            }
        }

    };

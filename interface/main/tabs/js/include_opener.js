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
 * @link      https://www.open-emr.org
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
            dialogModal.modal('hide');
        }

    };

var dlgclose =
    function (call, args) {
        var frameName = window.name;
        var wframe = top;
        var dialogModal = top.$('div#' + frameName);

        if (dialogModal.length === 0) {
            return;
        }

        if (call) {
            wframe.setCallBack(call, args);
        }

        // The original implementation removed the iframe up-front and then
        // called .modal('hide'). Two bugs there when dlgclose fires from
        // an inline script inside the modal's own iframe (e.g.
        // find_appt_popup's "provider unavailable, use anyway" submit
        // branch, which runs before the modal's show transition even
        // finishes):
        //
        //   1. Removing the iframe synchronously kills the calling script
        //      before .modal('hide') runs — the modal never hides.
        //   2. Bootstrap 4's Modal.hide() is a no-op while _isTransitioning
        //      is true, and every dlgopen enters show-transition
        //      immediately, so a fast dlgclose call lands during that
        //      window and is silently dropped.
        //
        // Fix both: defer the iframe removal into a top-scoped
        // 'hidden.bs.modal' handler (so it happens after the fade and
        // after our calling script has returned), and if the modal is
        // still transitioning, wait for 'shown.bs.modal' before firing
        // hide.
        dialogModal.one('hidden.bs.modal', function () {
            dialogModal.find("iframe[name='" + frameName + "']").remove();
        });
        var bs = dialogModal.data('bs.modal');
        if (bs && bs._isTransitioning) {
            dialogModal.one('shown.bs.modal', function () {
                dialogModal.modal('hide');
            });
        } else {
            dialogModal.modal('hide');
        }
    };

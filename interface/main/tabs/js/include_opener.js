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
 * Bootstrap 5 Migration Note:
 * BS5 removed jQuery dependency. Modal events (hidden.bs.modal) don't propagate reliably
 * across iframes, so we must manually handle cleanup and callback execution.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Get the dialog opener window (the window that called dlgopen)
// This is used to properly execute callbacks like 'reload' in cross-frame scenarios
function getDialogOpener() {
    // First try the OpenEMR opener list (set by dialog.js)
    if (typeof top !== 'undefined' && typeof top.get_opener === 'function') {
        var dlgOpener = top.get_opener(window.name);
        if (dlgOpener) {
            return dlgOpener;
        }
    }
    // Fall back to browser opener
    if (typeof opener !== 'undefined' && opener) {
        return opener;
    }
    return null;
}

// Set opener for backward compatibility (some code may reference this global)
if (!opener) {
    /* eslint-disable-next-line no-global-assign */
    opener = getDialogOpener();
}

/**
 * BS5 cross-frame modal close handler.
 * Handles modal cleanup and callback execution since hidden.bs.modal events
 * don't fire reliably across iframe boundaries.
 *
 * @param {string} call - Callback name or 'reload' to reload the opener window
 * @param {*} args - Arguments to pass to the callback
 * @param {jQuery} dialogModal - The modal container element
 * @param {Window} wframe - The target window (usually top)
 */
function closeModalAndExecuteCallback(call, args, dialogModal, wframe) {
    // Get the actual opener window for callback execution BEFORE any DOM changes
    var openerWindow = getDialogOpener();
    var modalEl = dialogModal[0];

    // Set callback for dialog.js (in case its event handler fires)
    if (call) {
        wframe.setCallBack(call, args);
    }

    // Try to hide using BS5 Modal API
    var bsRef = (typeof top !== 'undefined' && top.bootstrap && top.bootstrap.Modal)
        ? top.bootstrap
        : (typeof bootstrap !== 'undefined' ? bootstrap : null);

    var modalInstance = null;
    if (bsRef && bsRef.Modal && typeof bsRef.Modal.getInstance === 'function') {
        modalInstance = bsRef.Modal.getInstance(modalEl);
    }

    if (modalInstance) {
        modalInstance.hide();
    }

    // For 'reload' callback: execute immediately since cross-frame events are unreliable
    // Calling reload() on an already-reloading window is harmless
    if (call === 'reload' && openerWindow && openerWindow.location) {
        // Small delay to let modal start hiding, then reload
        setTimeout(function() {
            if (typeof openerWindow.location.reload === 'function') {
                openerWindow.location.reload();
            }
        }, 50);
    }

    // Cleanup modal after animation (300ms) completes
    // For 'reload', the opener window will be reloading so this cleanup is for other windows
    setTimeout(function() {
        // Always cleanup backdrop and body (BS5 cross-frame issue)
        top.jQuery('.modal-backdrop').remove();
        top.jQuery('body').removeClass('modal-open').css({'padding-right': '', 'overflow': ''});
        dialogModal.remove();

        // For non-reload callbacks, execute them after cleanup
        // (reload was already handled above)
        if (call && call !== 'reload') {
            if (openerWindow && typeof openerWindow[call] === 'function') {
                openerWindow[call](args);
            } else if (wframe && typeof wframe[call] === 'function') {
                wframe[call](args);
            }
        }
    }, 350);
}

window.close = function (call, args) {
    var frameName = window.name;
    var wframe = top;
    var dialogModal = top.$('div#' + frameName);

    // Note: Do NOT remove the iframe here - it destroys this script's execution context
    // The iframe will be removed along with dialogModal in closeModalAndExecuteCallback

    if (dialogModal.length > 0) {
        closeModalAndExecuteCallback(call, args, dialogModal, wframe);
    }
};

var dlgclose = function (call, args) {
    var frameName = window.name;
    var wframe = top;
    var dialogModal = top.$('div#' + frameName);

    // Note: Do NOT remove the iframe here - it destroys this script's execution context
    // The iframe will be removed along with dialogModal in closeModalAndExecuteCallback

    if (dialogModal.length > 0) {
        closeModalAndExecuteCallback(call, args, dialogModal, wframe);
    }
};

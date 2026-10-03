/**
 * Availability-popup helper for auto-submitting the opener event form.
 *
 * Empty form_action means the add-event dialog is not ready to save.
 * Closing without submit avoids a blank POST that leaves the save modal stuck.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/* global dlgclose */

function openerEventFormHasAction(form) {
    if (!form) {
        return false;
    }
    var action = form.form_action;
    if (!action && form.elements && typeof form.elements.namedItem === 'function') {
        action = form.elements.namedItem('form_action');
    }
    return !!(action && String(action.value).length);
}

function submitOpenerEventForm() {
    if (typeof opener === 'undefined' || !opener || opener.closed || !opener.document || !opener.document.forms[0]) {
        return false;
    }
    var f = opener.document.forms[0];
    if (!openerEventFormHasAction(f)) {
        if (typeof dlgclose === 'function') {
            dlgclose();
        }
        return false;
    }
    if (opener.top && typeof opener.top.restoreSession === 'function') {
        opener.top.restoreSession();
    }
    f.submit();
    if (typeof dlgclose === 'function') {
        dlgclose();
    }
    return true;
}

window.openerEventFormHasAction = openerEventFormHasAction;
window.submitOpenerEventForm = submitOpenerEventForm;

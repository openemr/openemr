/**
 * Calendar Visit Lists, appointment editor: before a Visit List block with patients is
 * deleted or given another category, say how many patients are scheduled in it and that
 * they will stay on the schedule as timed appointments, and let the user cancel.
 *
 * The server sends, for an existing block with patients from today on (else nothing):
 * { categoryId, messages: { current, future, all }, deleteText }, where a repeat scope with
 * no such patients is left out of messages. A block that doesn't repeat uses "all".
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/* exported pcVisitListBlockAsksLater, pcVisitListBlockConfirm */

function pcVisitListBlockHasPatients(config) {
    return !!(config && config.messages && Object.keys(config.messages).length > 0);
}

/**
 * Whether the delete button can skip its usual "are you sure": the block has patients, so
 * pcVisitListBlockConfirm() asks once the repeat scope is known.
 */
function pcVisitListBlockAsksLater(config) {
    return pcVisitListBlockHasPatients(config);
}

/**
 * Asks before the form is submitted, when the submit deletes the block or saves it with
 * another category and patients are in the scope it affects. Returns whether to go on. On cancel
 * the form is set back as it was before the save or delete was started.
 */
function pcVisitListBlockConfirm(config, form) {
    if (!pcVisitListBlockHasPatients(config)) {
        return true;
    }
    var fields = form.elements;
    var action = fields.form_action.value;
    if (action !== 'save' && action !== 'delete') {
        // A duplicate is a new event: the block and its patients stay as they are.
        return true;
    }
    var deleting = action === 'delete';
    if (!deleting && String(fields.form_category.value) === String(config.categoryId)) {
        return true;
    }

    var scope = fields.recurr_affect.value || 'all';
    var message = config.messages[scope];
    if (message === undefined) {
        // No patients on the dates this affects: a delete still asks as it always has.
        if (!deleting || window.confirm(config.deleteText)) {
            return true;
        }
    } else if (window.confirm(message)) {
        return true;
    }

    fields.recurr_affect.value = '';
    fields.form_action.value = '';
    if (typeof window.EnableForm === 'function') {
        window.EnableForm();
    }
    if (typeof window.HideRecurrPopup === 'function') {
        window.HideRecurrPopup();
    }
    if (fields.form_save) {
        fields.form_save.disabled = false;
    }
    return false;
}

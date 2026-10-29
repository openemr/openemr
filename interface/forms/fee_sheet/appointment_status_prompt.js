/**
 * After Save on the fee sheet: ask whether to set the visit's appointment status, and on
 * Yes set it and open the calendar on the visit date. Then close the form as Save does.
 * The page (templates/forms/fee_sheet/appointment_status_prompt.html.twig) provides
 * the #appt-status-prompt form.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

(function () {
    'use strict';

    const form = document.getElementById('appt-status-prompt');
    // The main window outlives this form's frame, which closes before the calendar loads.
    const mainWindow = top;
    const closeForm = function () {
        parent.closeTab(window.name, true);
    };
    if (!form || !confirm(form.dataset.question)) {
        closeForm();
        return;
    }

    mainWindow.restoreSession();
    fetch(form.action, {method: 'POST', body: new FormData(form), credentials: 'same-origin'})
        .then(function (response) {
            return response.json();
        })
        .then(function (result) {
            if (!result.ok) {
                throw new Error('status not set');
            }
            mainWindow.navigateTab(form.dataset.calendarUrl, 'cal', function () {
                mainWindow.activateTabByName('cal', true);
            });
        })
        .catch(function () {
            alert(form.dataset.failed);
        })
        .finally(closeForm);
})();

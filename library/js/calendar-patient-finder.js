/**
 * Navigation helpers for the calendar patient finder.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
(function (window) {
    'use strict';

    function findApplicationWindow(context) {
        if (context.top && typeof context.top.navigateTab === 'function') {
            return context.top;
        }

        // A patient finder opened as a native window may still have the main
        // application as the top-level window of its appointment opener.
        if (context.opener && !context.opener.closed && context.opener.top &&
            typeof context.opener.top.navigateTab === 'function') {
            return context.opener.top;
        }

        // When the appointment itself is a native window, its finder is an
        // iframe whose top-level opener is the main application.
        if (context.top && context.top.opener && !context.top.opener.closed &&
            context.top.opener.top && typeof context.top.opener.top.navigateTab === 'function') {
            return context.top.opener.top;
        }

        return null;
    }

    function openAddPatient(context, url, closeFinder) {
        const applicationWindow = findApplicationWindow(context);

        if (applicationWindow) {
            if (typeof applicationWindow.restoreSession === 'function') {
                applicationWindow.restoreSession();
            }
            applicationWindow.navigateTab(url, 'pat', function () {
                if (typeof applicationWindow.activateTabByName === 'function') {
                    applicationWindow.activateTabByName('pat', true);
                }
            });
        } else {
            if (typeof context.restoreSession === 'function') {
                context.restoreSession();
            }
            // Keep the appointment alive and reuse the current supported
            // dialog/window when the tabs shell is unavailable.
            context.location.assign(url);
            return;
        }

        closeFinder();
    }

    window.OpenEMRCalendarPatientFinder = {
        findApplicationWindow: findApplicationWindow,
        openAddPatient: openAddPatient
    };
})(window);

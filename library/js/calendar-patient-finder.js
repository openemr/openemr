/**
 * Navigation helpers for the calendar patient finder.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
(function (window) {
    'use strict';

    function usableApplicationWindow(candidate) {
        try {
            return candidate && typeof candidate.navigateTab === 'function' ? candidate : null;
        } catch {
            return null;
        }
    }

    function findApplicationWindow(context) {
        try {
            const applicationWindow = usableApplicationWindow(context.top);
            if (applicationWindow) {
                return applicationWindow;
            }
        } catch {
            // Cross-origin and closing WindowProxy properties can throw. Try
            // the remaining supported application-window relationships.
        }

        // A patient finder opened as a native window may still have the main
        // application as the top-level window of its appointment opener.
        try {
            const opener = context.opener;
            const applicationWindow = opener && !opener.closed ? usableApplicationWindow(opener.top) : null;
            if (applicationWindow) {
                return applicationWindow;
            }
        } catch {
            // Continue when the opener is inaccessible or was severed.
        }

        // When the appointment itself is a native window, its finder is an
        // iframe whose top-level opener is the main application.
        try {
            const topWindow = context.top;
            const opener = topWindow && topWindow.opener;
            const applicationWindow = opener && !opener.closed ? usableApplicationWindow(opener.top) : null;
            if (applicationWindow) {
                return applicationWindow;
            }
        } catch {
            // The current-context fallback remains available below.
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

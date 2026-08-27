(function () {
    const input = document.getElementById('patient_display');
    const finderUrl = (input && input.getAttribute('data-finder-url')) || '';

    window.sel_patient = function () {
        if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') {
            top.restoreSession();
        }
        if (typeof dlgopen === 'function') {
            dlgopen(finderUrl, '_blank', 700, 550);
            return;
        }
        window.open(finderUrl, 'findPatient', 'width=700,height=550,scrollbars=yes');
    };

    function tabsWindow() {
        try {
            if (window.top && window.top !== window && typeof window.top.navigateTab === 'function') {
                return window.top;
            }
        } catch (e) {
            /* cross-origin */
        }
        return null;
    }

    // Same path as Patient Finder (dynamic_finder.php): open demographics in
    // the Patient tab. demographics.php?set_encounterid= then loadFrame('enc').
    function openPatientTab(shell, url) {
        if (typeof shell.restoreSession === 'function') {
            shell.restoreSession();
        }
        if (typeof shell.navigateTab === 'function') {
            shell.navigateTab(url, 'pat', function () {
                if (typeof shell.activateTabByName === 'function') {
                    shell.activateTabByName('pat', true);
                }
            });
            return;
        }
        if (shell.RTop) {
            shell.RTop.location = url;
        }
    }

    window.openEncounterTab = function (ev) {
        if (ev) {
            if (ev.preventDefault) {
                ev.preventDefault();
            }
            if (ev.stopPropagation) {
                ev.stopPropagation();
            }
        }
        const el = (ev && ev.currentTarget)
            ? ev.currentTarget
            : document.getElementById('lbf-stmt-open-form');
        if (!el) {
            return false;
        }
        const tabsUrl = el.getAttribute('data-tabs-url');
        const href = el.getAttribute('data-fallback-url') || el.getAttribute('href');
        const shell = tabsWindow();
        if (shell !== null && tabsUrl) {
            try {
                openPatientTab(shell, tabsUrl);
            } catch (e) {
                if (window.console && typeof console.error === 'function') {
                    console.error('openEncounterTab', e);
                }
            }
            return false;
        }
        // Unframed (no OpenEMR tab shell): follow the encounter view URL.
        if (shell === null && href && href !== '#') {
            window.location.href = href;
        }
        return false;
    };

    window.setpatient = function (pid, lname, fname) {
        const pidEl = document.getElementById('pid');
        const form = document.getElementById('lbf-stmt-pick');
        if (!pidEl || !form) {
            return;
        }
        pidEl.value = String(pid);
        if (input) {
            input.value = lname + ', ' + fname;
        }
        form.submit();
    };
})();

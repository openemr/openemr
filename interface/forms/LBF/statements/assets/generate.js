(function () {
    const input = document.getElementById('patient_display');
    const finderUrl = (input && input.getAttribute('data-finder-url')) || '';

    /**
     * Open the patient finder dialog.
     */
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

    /**
     * Parent OpenEMR window that owns navigateTab, or null when unframed.
     * @returns {Window|null}
     */
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
    /**
     * Open demographics in the Patient tab of the OpenEMR shell.
     * @param {Window} shell
     * @param {string} url
     */
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

    /**
     * Open the encounter in a tab when the shell is present; otherwise follow the link.
     * @param {Event} ev
     * @returns {boolean}
     */
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
        const href = el.getAttribute('data-fallback-url') || openFormUrl(el);
        const shell = tabsWindow();
        if (shell !== null && tabsUrl) {
            try {
                openPatientTab(shell, tabsUrl);
                return false;
            } catch (e) {
                if (window.console && typeof console.error === 'function') {
                    console.error('openEncounterTab', e);
                }
            }
        }
        if (href && href !== '#') {
            window.location.href = href;
        }
        return false;
    };

    /**
     * Selected layout form_id from the generate form.
     * @returns {string}
     */
    function pickFormId() {
        const el = document.getElementById('form_id');
        return el ? String(el.value || '') : '';
    }

    /**
     * Selected patient id from the generate form.
     * @returns {string}
     */
    function pickPid() {
        const el = document.getElementById('pid');
        return el ? String(el.value || '') : '';
    }

    /**
     * Generate-screen URL for one form instance.
     * @param {string|number} instanceId
     * @returns {string}
     */
    function instanceIndexUrl(instanceId) {
        return 'index.php?form_id=' + encodeURIComponent(pickFormId())
            + '&pid=' + encodeURIComponent(pickPid())
            + '&instance_id=' + encodeURIComponent(String(instanceId || ''));
    }

    /**
     * CSRF-protected open_form.php URL for print or encounter view.
     * @param {Element} el
     * @returns {string}
     */
    function openFormUrl(el) {
        if (!el) {
            return 'open_form.php';
        }
        const dest = el.getAttribute('data-dest') || 'form';
        return 'open_form.php?form_id=' + encodeURIComponent(el.getAttribute('data-form-id') || '')
            + '&pid=' + encodeURIComponent(el.getAttribute('data-pid') || '')
            + '&instance_id=' + encodeURIComponent(el.getAttribute('data-instance-id') || '')
            + '&dest=' + encodeURIComponent(dest)
            + '&csrf_token_form=' + encodeURIComponent(el.getAttribute('data-csrf') || '');
    }

    document.querySelectorAll('.lbf-stmt-pick-inst').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') {
                top.restoreSession();
            }
            window.location.href = instanceIndexUrl(a.getAttribute('data-instance-id'));
        });
    });

    const printBtn = document.getElementById('lbf-stmt-print');
    if (printBtn) {
        printBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') {
                top.restoreSession();
            }
            window.open(openFormUrl(printBtn), '_blank');
        });
    }

    /**
     * Patient-finder callback: store pid and reload the generate screen.
     * @param {string|number} pid
     * @param {string} lname
     * @param {string} fname
     */
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

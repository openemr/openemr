// Only one prescription sync runs from this page at a time. After returning
// from e-Prescribe the widget starts one on its own (sync_report). A Sync click
// while that was running started a second sync on the server; both write the
// same report file, so the click failed. The Sync button is disabled while a
// sync runs, and the server answers 409 if a sync from elsewhere (another tab,
// the background service) is already running.
let wenoSyncRunning = false;

function wenoSyncText(text) {
    return typeof xl === 'function' ? xl(text) : text;
}

function setWenoSyncBusy(busy) {
    wenoSyncRunning = busy;
    const button = document.getElementById("sync-button");
    const icon = document.getElementById("sync-icon");
    if (button) {
        button.setAttribute("aria-disabled", busy ? "true" : "false");
        button.classList.toggle("disabled", busy);
        button.style.pointerEvents = busy ? "none" : "";
        button.style.opacity = busy ? "0.5" : "";
        button.title = busy ? wenoSyncText("A prescription sync is running.") : "";
    }
    if (icon) {
        icon.classList.toggle("fa-spin", busy);
    }
}

function postWenoSync() {
    top.restoreSession();
    const formData = new FormData();
    formData.append("key", "sync");
    return fetch('../../modules/custom_modules/oe-module-weno/templates/synch.php', {
        method: 'POST',
        body: formData
    });
}

function showWenoSyncInfo(element, msg) {
    if (!element) {
        return;
    }
    element.classList.remove("d-none", "alert-success", "alert-danger");
    element.classList.add("alert", "alert-info");
    element.textContent = msg;
    setTimeout(function () {
        element.classList.add("d-none");
        element.classList.remove("alert", "alert-info");
        element.textContent = "";
    }, 5000);
}

// Unlike the info notice, an error stays on screen until the page reloads.
function showWenoSyncError(element, msg) {
    if (!element) {
        return;
    }
    element.classList.remove("d-none", "alert-success", "alert-info");
    element.classList.add("alert", "alert-danger");
    element.textContent = msg;
}

function sync_weno() {
    if (wenoSyncRunning) {
        return;
    }
    const syncIcon = document.getElementById("sync-icon");
    const syncAlert = document.getElementById("sync-alert");
    setWenoSyncBusy(true);

    postWenoSync().then(response => {
        if (response.status === 409) {
            setWenoSyncBusy(false);
            showWenoSyncInfo(syncAlert, wenoSyncText("A prescription sync is already running. Please try again shortly."));
            return;
        }
        if (!response.ok) {
            throw new Error('Server responded with an error status: ' + response.status);
        }
        // Both alert paths reload the page, so the button stays disabled.
        wenoAlertManager("success", syncAlert, syncIcon);
        refreshDemographics();
    }).catch(error => {
        console.log(error.message);
        wenoAlertManager("failed", syncAlert, syncIcon);
    });
}

function sync_report(pid = '') {
    if (wenoSyncRunning) {
        return;
    }
    setWenoSyncBusy(true);

    postWenoSync().then(response => {
        if (response.status === 409) {
            // Another sync is already importing; say so on the
            // "Checking Sync Report" notice instead of hiding it.
            showWenoSyncInfo(
                document.getElementById("resync-alert"),
                wenoSyncText("A prescription sync is already running. Please try again shortly.")
            );
            setWenoSyncBusy(false);
            return;
        }
        if (!response.ok) {
            throw new Error('Server responded with an error status: ' + response.status);
        }
        refreshDemographics();
    }).catch(error => {
        console.log(error.message);
        showWenoSyncError(
            document.getElementById("resync-alert"),
            wenoSyncText("The prescription report could not be refreshed. The prescriptions shown may be out of date.")
        );
        setWenoSyncBusy(false);
    });
}

function wenoAlertManager(option, element, spinElement, msg = "Successfully updated") {
    top.restoreSession();
    spinElement.classList.remove("fa-spin");
    if (option === "success") {
        element.classList.remove("d-none");
        element.classList.add("alert", "alert-success");
        element.innerHTML = msg;
        setTimeout(
            function () {
                element.classList.add("d-none");
                element.classList.remove("alert", "alert-success");
                element.innerHTML = "";
                window.location.replace(window.location.href);
            }, 3000
        );

    } else {
        setTimeout(function () {
            element.classList.add("d-none");
            element.classList.remove("alert", "alert-danger");
            element.innerHTML = "";
            window.location.replace(window.location.href);
        }, 5000);
        element.classList.remove("d-none");
        element.classList.add("alert", "alert-danger");
        element.innerHTML = "An error occurred possibly credentials are wrong. Please check the credentials and try again.";
    }
}

function renderDialog(action, uid, event) {
    event.preventDefault();
    // Trim action URL
    action = action.trim();
    // Get CSRF token
    const csrf = document.getElementById("csrf_token_form").value || '';
    // Map URLs
    const urls = {
        'demographics': '/interface/patient_file/summary/demographics_full.php',
        'user_settings': '/interface/super/edit_globals.php?mode=user',
        'weno_manage': '/interface/modules/custom_modules/oe-module-weno/templates/weno_setup.php',
        'users': '/interface/usergroup/user_admin.php'
    };
    if (urls[action] === undefined) {
        console.error('Invalid action URL');
        alert(action.toUpperCase() + " " + xl('Direct action not implemented yet.'));
        return;
    }
    // Construct action URL
    const urlPart = urls[action].includes('?') ? '&' : '?';
    const actionUrl = `${urls[action]}${urlPart}id=${encodeURIComponent(uid)}&csrf_token_form=${encodeURIComponent(csrf)}`;

    // Open modal dialog
    dlgopen('', 'dialog-mod', 'modal-lg', 'full', '', '', {
        buttons: [
            {
                text: jsText('Return to eRx Widget'),
                close: true,
                style: 'primary'
            }
        ],
        allowResize: true,
        allowDrag: true,
        onClosed: 'reload',
        dialogId: 'error-dialog',
        type: 'iframe',
        resolvePromiseOn: 'close',
        sizeHeight: 'full',
        url: top.webroot_url + actionUrl
    }).then(function (dialog) {
        top.restoreSession();
    });
}

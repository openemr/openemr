function sync_weno() {
    top.restoreSession();
    const syncIcon = document.getElementById("sync-icon");
    const syncAlert = document.getElementById("sync-alert");
    const url = '../../modules/custom_modules/oe-module-weno/templates/synch.php';

    syncIcon.classList.add("fa-spin");

    let formData = new FormData();
    formData.append("key", "sync");

    fetch(url, {
        method: 'POST',
        body: formData
    }).then(response => {
        if (!response.ok) {
            // If the response status code is not in the 200-299 range, reject the promise
            throw new Error('Server responded with an error status: ' + response.status);
        } else {
            // setting alert details
            wenoAlertManager("success", syncAlert, syncIcon);
        }
    }).catch(error => {
        console.log(error.message)
        wenoAlertManager("failed", syncAlert, syncIcon);
    });
}

function wenoAlertManager(option, element, spinElement) {
    top.restoreSession();
    spinElement.classList.remove("fa-spin");
    if (option === "success") {
        element.classList.remove("d-none");
        element.classList.add("alert", "alert-success");
        element.innerHTML = "Successfully updated";
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

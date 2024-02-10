function sync_weno() {
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
                window.location.reload();
            }, 3000
        );

    } else {
        setTimeout(function () {
            element.classList.add("d-none");
            element.classList.remove("alert", "alert-danger");
            element.innerHTML = "";
        }, 3000);
        element.classList.remove("d-none");
        element.classList.add("alert", "alert-danger");
        element.innerHTML = "An error occurred";
    }
}

function renderDialogAlert(message) {
    const dialogAlert = document.getElementById("dialog-alert");
    const dialogContent = document.getElementById("dialog-content");
    dialogAlert.classList.remove("d-none");
    dialogContent.innerHTML = message;
    setTimeout(function () {
        dialogAlert.classList.add("d-none");
    }, 20000);
}

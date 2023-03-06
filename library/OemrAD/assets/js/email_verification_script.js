/*
Author: Hardik Khatri
Description: Init Email Verification Elements ("options.inc.php")
*/
function setEmailVerificationData(val, ele) {
    if(val.trim() == "") {
        ele.querySelector('button.btn_verify_email').disabled = true;
        //$('#DEM #hidden_verification_status').addClass('disabledItem');
    } else {
        ele.querySelector('button.btn_verify_email').disabled = false;
        //$('#DEM #hidden_verification_status').removeClass('disabledItem');
    }
}

function initEmailVerificationElement(ele) {
    var emvElementor = ele;
    if(emvElementor && emvElementor != null) {
        let initStatus = emvElementor.dataset.initstatus;
        let inputElement = emvElementor.querySelector('input[type="text"]');

        setEmailVerificationData(inputElement.value, ele);
    }
}

function initEmailVerificationElements() {
    document.querySelectorAll('.emv-input-group-container').forEach(function(container) {
        initEmailVerificationElement(container);
    });
}

/*Set Status Value ("options.inc.php")*/
function setEmailVerificationStatusValue(status, ele) {
    if(status == true) {
        ele.querySelector('.status-icon-container').innerHTML  = "<i class='fa fa-check-circle email-verification-icon-successful' aria-hidden='true'></i>";
        ele.querySelector('.hidden_verification_status').value = "1";
    } else if(status == false) {
        ele.querySelector('.status-icon-container').innerHTML  = "<i class='fa fa-times-circle email-verification-icon-failed' aria-hidden='true'></i>";
        ele.querySelector('.hidden_verification_status').value = "0";
    }
}

/*Set Button Status ("options.inc.php")*/
function emvSetLoadingValue(status) {
    if(status == true) {
        $('#DEM #btn_verify_email').attr("disabled", "disabled").html('Verifying...');
    } else if(status == false) {
        $('#DEM #btn_verify_email').removeAttr("disabled", "disabled").html('Verify Email');
    }
}

/*Email Verification Service ("options.inc.php")*/
async function callEmailVerificationService(val) {
    let result;
    let ajaxurl = top.webroot_url + '/interface/email_verification/ajax_email_verification.php?email='+val;

    if(val && val != "") {
        try {
            result = await $.ajax({
                url: ajaxurl,
                type: 'GET',
                timeout: 30000
            });
            return JSON.parse(result);
        } catch (error) {
            if(error.statusText == "timeout") {
                alert('Request Timeout');
            } else {
                alert('Something went wrong');
            }
        }
    }
    return null;
}

/*Handle email verification ("options.inc.php")*/
async function handleEmailVerification(val, ele) {
    emvSetLoadingValue(true);
    var reponceData = await callEmailVerificationService(val);
    emvSetLoadingValue(false);

    if(reponceData != null) {
        var reponce = JSON.parse(reponceData);
        if(reponce.success == "true") {
            if(reponce.result == "valid" && reponce.disposable == "false" && reponce.accept_all == "false") {
                setEmailVerificationStatusValue(true, ele);
            } else {
                setEmailVerificationStatusValue(false, ele);
            }
        } else if(reponce.success == "false"){
            alert(reponce.message);
        }
    }
}
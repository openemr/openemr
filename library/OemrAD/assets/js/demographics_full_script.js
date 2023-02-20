// Confirmbox for demographics ("demographics_full.php")
async function handleConfimBox_DemographicsFull() {
    var statusVal = $('#DEM #form_email_direct_hidden_verification_status').val();
  
    if(statusVal && statusVal == "0") {
        //return confirm("Do you want to continue with unverified email?");
        let verificationConfirmation = await confirmBoxModal({
            type: 'confirm',
            title: "Confirm",
            html: "Do you want to continue with unverified email?"
        });

        return verificationConfirmation;
    }

    return true;
}

function checkSecondoryEmail(element) {
  var eleId = '#'+element;
  var directEmailVal = $(eleId+' #form_email_direct').val();
  var secondaryEmailVal = $(eleId+' #form_secondary_email').val();
  var expSecondaryEmailVal = secondaryEmailVal.split(',');
  var finalList = [];

  $.each(expSecondaryEmailVal, function(index, item) {
    if(!finalList.includes($.trim(item)) && $.trim(directEmailVal) != $.trim(item)) {
      finalList.push($.trim(item));
    }
  });

  $(eleId+' #form_secondary_email').text(finalList.join());
}

function phoneContains(a, obj) {
    var objitemVal = obj.replace(/[^0-9]/gi, '');
    for (var i = 0; i < a.length; i++) {
        var tmpitemVal = a[i].replace(/[^0-9]/gi, '');
        if (tmpitemVal === objitemVal) {
            return true;
        }
    }
    return false;
}

function checkSecondoryPhone(element) {
  var eleId = '#'+element;
  var phoneCellVal = $(eleId+' #form_phone_cell').val();
  var tmpphoneCellVal = phoneCellVal.replace(/[^0-9]/gi, '');

  var secondaryPhoneCellVal = $(eleId+' #form_secondary_phone_cell').val();
  var expSecondaryPhoneCellVal = secondaryPhoneCellVal.split(',');
  var finalList = [];

  $.each(expSecondaryPhoneCellVal, function(index, item) {
    var tmpitemVal = item.replace(/[^0-9]/gi, '');
    if(phoneContains(finalList, $.trim(item)) == false && $.trim(tmpphoneCellVal) != $.trim(tmpitemVal)) {
      finalList.push($.trim(item));
    }
  });

  $(eleId+' #form_secondary_phone_cell').val(finalList.join());
}

// Handle submit for demographics ("demographics_full.php")
async function handleOnSubmit_DemographicsFull(validationStatus, element, event, eleId) {
    event.preventDefault();

    if(validationStatus === false) return false;
    
    const optionStatus = await validateOptions(eleId);
    if(optionStatus === false) {
        return false;
    }

    //Check Values
    checkSecondoryEmail(eleId);
    checkSecondoryPhone(eleId);

    var data = await handleConfimBox_DemographicsFull();
    if(data == true) {
        element.submit();
        //submitme(validate, event, element, constraints);
    }
    
    return data;
}

// This invokes the find-addressbook popup.
function open_notes_log(pid) {
    var url = top.webroot_url + '/library/OemrAD/interface/patient_file/summary/dem_view_logs.php'+'?pid='+pid;
    let title = 'Logs';
    dlgopen(url, 'dem-alert-log', 1000, 400, '', title, {
        allowDrag: false,
        allowResize: false
    });
}

$(function() {
    var alertEles = document.querySelectorAll("#form_alert_info");
    alertEles.forEach(function (alertElement, index) {
        var alert_val = alertElement.value;
        document.querySelector('#form_current_alert_info').value = alert_val;
    });
});
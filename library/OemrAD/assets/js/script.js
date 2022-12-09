// Check is patient exitsts ("new_comprehensive.php")
async function isPatientExists(){
    var firstName = $('#DEM #form_fname').val(); 
    var lastName = $('#DEM #form_lname').val();
    var dob = $('#DEM #form_DOB').val();

    //Call webservice
    var responce = await callPatientVerificationService(firstName, lastName, dob);

    if (
        responce != null &&
        responce["isExists"] != undefined &&
        responce["isExists"] != "null"
    ) {
        var isExistsResponce = responce["isExists"];

        if (isExistsResponce === true) {
            //return confirm("*****WARNING****** - You are about to create a duplicate chart!!!!!! There is already a patient with the same first name, last name & birthdate.  Press CANCEL to abort..");
            return await confirmBoxModal({
                type: "confirm",
                title: "Confirm",
                html: "*****WARNING****** - You are about to create a duplicate chart!!!!!! There is already a patient with the same first name, last name & birthdate.  Press CANCEL to abort..",
            });
        }
    }

    return true;
}

// Call Patient Verification Service ("new_comprehensive.php")
async function callPatientVerificationService(firstName, lastName, dob) {
    let result;
    let ajaxurl = top.webroot_url + '/library/OemrAD/interface/new/ajax/ajax_patient_verification.php';

    if(firstName == "" && lastName == "" && dob == "") {
        return null;
    }

    try {
        result = await $.ajax({
            url: ajaxurl,
            type: 'GET',
            data: {"firstName": firstName, "lastName" : lastName, "dob" : dob},
        });
        return JSON.parse(result);
    } catch (error) {
        //alert('Something went wrong');
        await confirmBoxModal({
            type: 'alert',
            title: "Alert",
            html: "Something went wrong."
        });
    }

    return null;
}

// Confirmbox for unverified email ("new_comprehensive.php")
async function handleConfimBox_NewComprehensive() {
    var submitElement = $('#DEM').parent().parent().parent().parent().find('#create');
    $(submitElement).prop('disabled', true);

    //Check Is PatientExists
    var isPatientExistsResponce = await isPatientExists();

    if(isPatientExistsResponce === false) {
      $(submitElement).prop('disabled', false);
      return isPatientExistsResponce;
    }

    var statusVal = $('#DEM #form_email_direct_hidden_verification_status').val();
    var emailDirectVal = $('#DEM #form_email_direct').val();
    if(statusVal == "0" && emailDirectVal != "") {
        //return confirm("Do you want to continue with unverified email?");
        let verificationConfirmation = await confirmBoxModal({
            type: 'confirm',
            title: "Confirm",
            html: "Do you want to continue with unverified email?"
        });
        $(submitElement).prop('disabled', false);
    
        return verificationConfirmation;
    }

    $(submitElement).prop('disabled', false);

    return true;
}

// Handle Confirmbox on submit ("new_comprehensive.php")
async function handleOnSubmit_NewComprehensive(validationStatus, element, event, eleId) {
    event.preventDefault();

    if(validationStatus === false) return false;

    var data = handleBeforeSubmit_NewComprehensive(eleId);
    if(data == true) {
        element.submit();
        //submitme(validate, event, element, constraints);
    }

    return data;
}

// Process logic before form submit. ("new_comprehensive.php")
async function handleBeforeSubmit_NewComprehensive(eleId) {
    const optionStatus = await validateOptions(eleId);
    if(optionStatus === false) {
        return false;
    }

    //Check Values
    checkSecondoryEmail(eleId);
    checkSecondoryPhone(eleId);

    var data = handleConfimBox_NewComprehensive();
    return data;
}

/*-------------------(Demographics Full)----------------*/

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
async function open_notes_log(pid) {
    var url = top.webroot_url + '/library/OemrAD/interface/patient_file/summary/dem_view_logs.php'+'?pid='+pid;
    let title = 'Logs';
    let dialogObj = await dlgopen(url, 'dem-alert-log', 'modal-mlg', '', '', title, {
        allowDrag: false,
        allowResize: false,
        sizeHeight: 'full'
    });

    dialogLoader(dialogObj.modalwin);
}

$(function() {
    var alertEles = document.querySelectorAll("#form_alert_info");
    alertEles.forEach(function (alertElement, index) {
        var alert_val = alertElement.value;
        document.querySelector('#form_current_alert_info').value = alert_val;
    });

    // if(alert_ele.length > 0) {
    //     var alert_val = alert_ele.val();
    //     $('#form_current_alert_info').val(alert_val);
    // }
});

/*-------------------End----------------*/


/*-------------------(Email Verification Process)----------------*/

// Init Email Verification Elements ("options.inc.php")
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

// Set Status Value ("options.inc.php")
function setEmailVerificationStatusValue(status, ele) {
    if(status == true) {
        ele.querySelector('.status-icon-container').innerHTML  = "<i class='fa fa-check-circle email-verification-icon-successful' aria-hidden='true'></i>";
        ele.querySelector('.hidden_verification_status').value = "1";
    } else if(status == false) {
        ele.querySelector('.status-icon-container').innerHTML  = "<i class='fa fa-times-circle email-verification-icon-failed' aria-hidden='true'></i>";
        ele.querySelector('.hidden_verification_status').value = "0";
    }
}

// Set Button Status ("options.inc.php")
function emvSetLoadingValue(status) {
    if(status == true) {
        $('#DEM #btn_verify_email').attr("disabled", "disabled").html('Verifying...');
    } else if(status == false) {
        $('#DEM #btn_verify_email').removeAttr("disabled", "disabled").html('Verify Email');
    }
}

// Email Verification Service ("options.inc.php")
async function callEmailVerificationService(val) {
    let result;
    let ajaxurl = top.webroot_url + '/library/OemrAD/interface/email_verification/ajax_email_verification.php?email='+val;

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

// Handle email verification ("options.inc.php")
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

/*-------------------END----------------*/

/*-------------------(Phone number validation)----------------*/

// Prepare Multi Input Field values before submit
function prepareMiValues() {
  var miContainer = document.querySelectorAll(".mti-container");

  miContainer.forEach(function (containerElement, index) {
    var dataId = containerElement.getAttribute('data-id');
    var inputElements = containerElement.querySelectorAll('.mti-inputcontainer .mti-form-control[data-id="'+dataId+'"]');
    
    var valList = [];
    inputElements.forEach(function (inputElement, index) {
      var eleVal = inputElement.value;
      valList.push(eleVal);
    });

    containerElement.querySelector('#form_' + dataId).value = valList.join(); 
  });
}

// Prepare Error Msg
function prepareErrorMsg(errors) {
  var errorList = {}

  if(errors) {
    for(var eKey in errors) {
    //errors.forEach(function (error, eIndex) {
      var errorMsg = "";
      var errorField = [];
      
      for(var eiKey in errors[eKey]) {
        var errorItem = errors[eKey][eiKey];
        if(errorItem.hasOwnProperty('field_id')) {
          errorMsg = errorItem['message'];
          errorField.push(errorItem['field_title']);
        }
      }

      if(errorMsg != "" && errorField.length > 0) {
        errorMsg = errorMsg.replace('%s', errorField.join(', '));
        errorList[eKey] = errorMsg;
      }
    }
  }

  return errorList;
}


// Validations
function validatePhoneNumber(ele, e) {
  var result = {
    'error' : false,
    'message' : 'Phone numbers must have 10 numbers, example (555) 555-5555.  Please update [%s]. Press CANCEL to fix phone number.  Press OK to bypass error and save improperly formatted phone number'
  };

  if(ele) {
    var eleVal = ele.value;
    var eleId = ele.getAttribute('data-id');
    var eleTitle = ele.getAttribute('data-title');

    result['field_id'] = eleId ? eleId : '';
    result['field_title'] = eleTitle ? eleTitle : '';

    if(eleVal != '') {
      //"\([0-9][0-9][0-9]\)\s[0-9][0-9][0-9][-][0-9][0-9][0-9][0-9]\b";
      //let pattern = /\(\d{3}\)\s\d{3}[-]\d{4}\b/;
      //result['error'] = eleVal.match(pattern) ? false : true;
      var trimValue = eleVal.replace(/[\s\(\)\-]/g, "");
      let isnum = /^\d+$/.test(trimValue);

      if(trimValue.length === 10 && isnum === true) {
        result['error'] = false;
      } else {
        result['error'] = true;
      }
      
    }
  }

  return result;
}

// Check Validation for Field
async function validateOptions(form_id) {
  
  //Prepare Mi Values
  prepareMiValues();

  var form = document.querySelector("form#"+form_id);

  var inputEles = form.querySelectorAll('input[type="text"][data-validate]:not(.mi_input_control)');
  var miInputEles = form.querySelectorAll('.mi_inputcontainer input[type="text"][data-validate]');
  var errorList = {};

  var finalEList = Array.prototype.slice.call(inputEles).concat(Array.prototype.slice.call(miInputEles));

  finalEList.forEach(function (miInputEle, miIndex) {
      var eleValidate = miInputEle.getAttribute('data-validate');
      var eleValidations = eleValidate.split(';');

      eleValidations.forEach(function (eleValidation, evIndex) {
        if(eleValidation && eleValidation != '') {
          var errorStatus = eval(eleValidation)(miInputEle, e);
          if(errorStatus && errorStatus['error'] === true) {

            if(!errorList.hasOwnProperty(eleValidation)) {
              errorList[eleValidation] = {};
            }

            if(errorStatus.hasOwnProperty('field_id')) {
              errorList[eleValidation][errorStatus['field_id']] = errorStatus; 
            }
          }
        }
      });
  });
  
  var preErrorList = prepareErrorMsg(errorList);
  let returnStatus = true;

  for(var eKey in preErrorList) {
    if(preErrorList[eKey] != "") {
      //alert(preErrorList[eKey]);
      //returnStatus = confirm(preErrorList[eKey]);
        returnStatus = await confirmBoxModal({
            type: 'confirm',
            title: "Confirm",
            html: preErrorList[eKey]
        });
    }
  }

  return returnStatus;
}

/*-------------------END----------------*/


/*-------------------(Phone number mask)----------------*/

// Mask phone value on load ("options.inc.php")
function maskPhoneOnLoad(elem) {
  var inputValue = elem.value;
  //var trimValue = inputValue.replace(/[\s\(\)\-]/g, "");
  var trimValue = inputValue.replace(/[\s\(\)\-]/g, "");
  var curchr = trimValue.length;
  let isnum = /^\d+$/.test(trimValue);

  if(curchr === 10 && isnum === true) {
    inputValue = String(trimValue.replace(/(\d{3})(\d{3})(\d{4})/, "($1) $2-$3"));
  }
  
  elem.value = inputValue;
}

// Mask phone value ("options.inc.php")
function maskPhone(elem, paste = false) {
  e = window.event;
  
  if(paste === false) {
    var key = e.which || e.keyCode; // keyCode detection
    var ctrl = e.ctrlKey || e.metaKey || key === 17; // ctrl detection
    if (key == 86 && ctrl) { // Ctrl + V Pressed !

    } else if (key == 67 && ctrl) { // Ctrl + C Pressed !

    } else if (key == 88 && ctrl) { //Ctrl + x Pressed

    } else if (key == 65 && ctrl) { //Ctrl + a Pressed !
      //maskPhoneOnLoad(elem);
    } else if (key != 9 && e.which != 8 && e.which != 0 && !(e.keyCode >= 96 && e.keyCode <= 105) && !(e.keyCode >= 48 && e.keyCode <= 57)) {
        return false;
    }

    //var curval = $(elem).val();
    var curval = elem.value;
    var trimValue = curval.replace(/[\s\(\)\-]/g, "");
    var curchr = trimValue.length;
    let isnum = /^\d+$/.test(trimValue);

    if(curchr === 10 && isnum === true) {
      elem.value = String(trimValue.replace(/(\d{3})(\d{3})(\d{4})/, "($1) $2-$3"));
    }

    // if (curchr == 3 && e.which != 8 && e.which != 0) {
    //     //$(elem).val('(' + curval + ')' + " ");
    //     elem.value = '(' + curval + ')' + " ";
    // } else if (curchr == 9 && e.which != 8 && e.which != 0) {
    //     //$(elem).val(curval + "-");
    //     elem.value = curval + "-";
    // }

    //$(elem).attr('maxlength', '20');
    elem.setAttribute('maxlength', '20');
  } else {
    maskPhoneOnLoad(elem);
  }
}

// Init Mask Phone ("options.inc.php")
function initMaskPhone() {
  const collection = document.getElementsByClassName("maskPhone");
  for (let i = 0; i < collection.length; i++) {
    maskPhone(collection[i], true);
  }
}

/*-------------------END----------------*/

/*-------------------(Multi Text Input)----------------*/

// Add more input
function addMoreInput(elem) {
  var dataId = elem.getAttribute('data-id');

  if(dataId != '') {
    var cloneElement = document.getElementById('clone-container_'+dataId).children[0];
    var inputContainerEle = document.querySelectorAll("#mti-container-" + dataId + " .mti-inputcontainer");

    inputContainerEle[0].appendChild(cloneElement.cloneNode(true));
  }
}

// Remove more input
function removeMoreInput(elem) {
    var currentInputContainer = elem.parentElement.parentElement;

    if(currentInputContainer.parentElement.children.length > 1) {
        if(currentInputContainer) {
            currentInputContainer.remove();
        }
    } else {
        currentInputContainer.querySelector('input[type="text"]').value = "";
    }
}

/*-------------------End----------------*/

// Page load functions ("options.inc.php")
$(function() {
    initEmailVerificationElements();

    /*On value change enable/disable verification element.*/
    $('.emv-input-group-container input[type="text"]').keyup(function() {
        setEmailVerificationData($(this).val(), $(this).parent().parent()[0]);
    });

    /*On change check email validation ("options.inc.php")*/
    $('.emv-input-group-container').on('input', 'input[type="text"]', function() {
        let inputVal = $(this).val();
        let emvContainer = $(this).parent().parent()[0];
        let initVStatus = emvContainer.dataset.initstatus;
        let initVEmail = emvContainer.dataset.initemail;

        if(inputVal == initVEmail && initVStatus == '1') {
            setEmailVerificationStatusValue(true, emvContainer);
        } else {
            setEmailVerificationStatusValue(false, emvContainer);
        }
    });

    /*On click check email validation ("options.inc.php")*/
    $('.emv-input-group-container').on('click', 'button.btn_verify_email', async function() {
        var isDisable = $(this).is(':disabled');
        var inputVal = $('#form_email_direct').val();
        var innerHtmlVal = $(this).html();

        //Set loader
        $(this).html('<div class="spinner-border btn-loader"></div>');

        if(isDisable == false) {
            await handleEmailVerification(inputVal, $(this).parent().parent().parent()[0]);
        }

        //Unset loader
        $(this).html(innerHtmlVal);
    });


    /*On keyup check ans mask phone value ("options.inc.php")*/
    $(document).on('keyup', 'input[type="text"].maskPhone', function() {
        maskPhone($(this)[0]);
    });

    /*On focusout check ans mask phone value ("options.inc.php")*/
    $(document).on('focusout', 'input[type="text"].maskPhone', function() {
        maskPhone($(this)[0], true);
    });

    /*Init phone mask when page load.*/
    initMaskPhone();
});

/*------------------- (Encounter Form) ----------------*/

async function handleConfimBox_feeCodeLinked(encounter, pid) {
    var bodyObj = { encounter :  encounter, pid : pid };
    const result = await $.ajax({
        type: "POST",
        url:  top.webroot_url + '/library/OemrAD/interface/forms/fee_sheet/ajax/get_feesheet_code_status.php',
        datatype: "json",
        data: bodyObj
    });

    if(result != '') {
        var resultObj = JSON.parse(result);
        if(resultObj && resultObj['feesheet_code_status'] === false) {
            /*if(!confirm("Warning - At least one CPT/HCPCS is not linked to an ICD in the fee sheet.  Press \"Cancel\" to back and justify all CPT/HCPCS codes or Press \"Ok\" to sign the encounter")) {
                return false;
            } else {
                return true;
            }*/
            returnStatus = await confirmBoxModal({
                type: 'confirm',
                title: "Confirm",
                html: "Warning - At least one CPT/HCPCS is not linked to an ICD in the fee sheet.  Press \"Cancel\" to back and justify all CPT/HCPCS codes or Press \"Ok\" to sign the encounter"
            });

            return returnStatus;
        } else {
            return true;
        }
    }

    return true;
}

// Handle care team provider.
async function handleCareTeamProvider(encounter, pid) {
    var bodyObj = { encounter :  encounter, pid : pid};
    const cs_result = await $.ajax({
        type: "POST",
        url:  top.webroot_url + '/library/OemrAD/interface/patient_file/encounter/ajax/check_cs_rp.php',
        datatype: "json",
        data: bodyObj
    });

    if(cs_result != '') {
        var csResultObj = JSON.parse(cs_result);
        if(csResultObj && csResultObj['status'] !== false) {
            var rp_url = top.webroot_url + '/library/OemrAD/interface/patient_file/encounter/case_rp_view.php?pid=' + pid + '&encounter=' + encounter;
            let rp_title = 'Care Team Providers';
            let dialogObj = await dlgopen(rp_url, 'case_rp_view', 'modal-mlg', '', '', rp_title, {
                sizeHeight: 'full'
            });
            
            dialogLoader(dialogObj.modalwin);
        }
    }
}

// Check is encounter authorizedEncounter.
async function authorizedEncounter(encounter = '', case_id = '', start_date = '') {
    if(case_id != '') {
        var responce = await $.post({
            type: "POST",
            url: top.webroot_url + '/library/OemrAD/interface/main/calendar/ajax/authorized_case.php',
            datatype: "json",
            data: { "type" : "encounter", "case_id" : case_id, "start_date" : start_date, "encounter" : encounter }
        });

        var responceJSON = JSON.parse(responce);

        if(responceJSON['status'] === false) {
            //alert(responceJSON['message'].join('\n\n'));
            await confirmBoxModal({
                type: 'alert',
                title: "Alert",
                html: responceJSON['message'].join('\n\n')
            });
        }
    }
}

/*-------------------End----------------*/

/*------------------- (messages.php) ----------------*/

function MessageLib() {
    'use strict';

    let props = {
        attachClassObject: null,
        handleSelectEncounters: async function() {
            let pid = $("#reply_to").val();

            if(pid == "") {
                //alert("Please select patient");
                await confirmBoxModal({
                    type: 'alert',
                    title: "Alert",
                    html: "Please select patient"
                });
                return false;
            }

            //Handle Encounter
            this.attachClassObject.handleEncounter(pid);
        },
        handleDocuments: async function() {
            let pid = $("#reply_to").val();

            if(pid == "") {
                //alert("Please select patient");
                await confirmBoxModal({
                    type: 'alert',
                    title: "Alert",
                    html: "Please select patient"
                });
                return false;
            }


            //Handle Document
            this.attachClassObject.handleDocument(pid);
        },
        handleMessages: async function(opts = {}) {
            let pid = $("#reply_to").val();
            let assigned_to = opts['assigned_to'] ? opts['assigned_to'] : "";

            if(pid == "") {
                //alert("Please select patient");
                await confirmBoxModal({
                    type: 'alert',
                    title: "Alert",
                    html: "Please select patient"
                });
                return false;
            }

            //Handle Message
            this.attachClassObject.handleMessage(pid, { assigned_to: assigned_to});
        },
        handleOrders: async function() {
            let pid = $("#reply_to").val();

            if(pid == "") {
                //alert("Please select patient");
                await confirmBoxModal({
                    type: 'alert',
                    title: "Alert",
                    html: "Please select patient"
                });
                return false;
            }

            //Handle Order
            this.attachClassObject.handleOrder(pid);
        },
        onPrepareFiles: function(items) {
            let finalList = {
                encounters : items['encounters'] ? items['encounters'] : {},
                documents : items['documents'] ? items['documents'] : {},
                messages : items['messages'] ? items['messages'] : {},
                orders : items['orders'] ? items['orders'] : {},
            };
            let newFinalList = {};
            let mappingList = {
                "encounters" : "encounter_id",
                "documents" : "doc_id",
                "messages" : "message_id",
                "orders" : "order_id"
            };

            $.each(finalList, function(iType, items) {
                if(Array.isArray(items)) {
                    let preparedData = [];
                    items.forEach(function (itemData, itemIndex) {
                        let mappingField = mappingList[iType] ? mappingList[iType] : "";
                        if(mappingField != "") {
                            preparedData.push({
                                "id" : itemData[mappingField] ? itemData[mappingField] : ""
                            })
                        }
                    });

                    newFinalList[iType] = preparedData;
                }
            });

            let finalListJSONStr = JSON.stringify(newFinalList);

            $('#filesDocList').val(finalListJSONStr);
        },
        init: function() {
        }
    }

    // On page load
    $(document).ready(function(){
        props.attachClassObject = $('#itemsContainer').attachment({
            empty_title: "No items",
            onPrepareFiles: props.onPrepareFiles,
            clickable_link: true
        });

        $('.usersSelectList').on("change", function (e) {
            let select_val = $(this).val();
            isGroupUserExists(select_val);             
        });
    });

    return props;
}

/*------------ End -----------------*/

/*------------------- (Dialog) ----------------*/
    
function dialogLoader(modalwin) {
    if(modalwin) {
        let modalBodyElement = $(modalwin).find('.modal-content .modal-body');
        modalBodyElement.addClass("loading-dialog");
        modalBodyElement.append("<div class=\"loaderContainer\" style=\"width: 100%;height: 100%;display: grid;justify-content: center;align-items: center;\"><div class=\"spinner-border\"></div></div>");
        //modalBodyElement.find('iframe').hide();
        modalBodyElement.find('iframe').attr('style', 'position: absolute; top:0; left:0; visibility: hidden;');

        //On load page.
        modalwin.on('load', function (e) {
            //modalBodyElement.find('iframe').show();
            modalBodyElement.find('.loaderContainer').remove();
            modalBodyElement.find('iframe').attr('style', '');
        });
    }
}

// Handle ConfirmBoxModal
async function confirmBoxModal(dopt = {}) {
    var opt = {
        allowDrag: false,
        allowResize: false,
        sizeHeight: 'auto',
        ...dopt
    }
    let dlgopenResponce;

    let promise = new Promise(async(resolve, reject) => {
        var buttons = [];
        let topWindow = opt['topWindow'] ? opt['topWindow'] : true;

        if(topWindow === true) {
            if(opt.type == "confirm") {
                buttons = [
                    {text: 'Ok', close: true, id: 'confirmYes1', style: 'primary', click: () => {
                        dlgopenResponce = true;
                        setTimeout(() => {
                            resolve(true);
                        }, "500");
                    }},
                    {text: 'Cancel', close: true, id: 'confirmNo1', style: 'secondary', click: () => {
                        dlgopenResponce = false;
                        setTimeout(() => {
                            resolve(false);
                        }, "500");
                    }}
                ];
            } else if(opt.type == "alert") {
                buttons = [
                    {text: 'Ok', close: true, id: 'confirmYes1', style: 'primary', click: () => {
                        dlgopenResponce = true;
                        setTimeout(() => {
                            resolve(true);
                        }, "500");
                    }}
                ];
            }

            await dlgopen(opt?.url,'confirmationbox', 400, 0, '', opt?.title, {
                buttons: buttons,
                type: 'confirm',
                html: opt?.html,
                resolvePromiseOn: 'confirm',
                allowDrag: opt?.allowDrag,
                allowResize: opt?.allowResize,
                sizeHeight: opt?.sizeHeight,
                topWindow: topWindow,
                callBack: {
                    call :() => {if(typeof dlgopenResponce === undefined || dlgopenResponce == undefined) {resolve(false);}}
                }
            });
        } else {
            if(opt.type == "confirm") {
                buttons = [
                    {text: 'Yes', close: true, id: 'confirmYes', style: 'primary'},
                    {text: 'No', close: true, id: 'confirmNo', style: 'secondary'}
                ];
            } else if(opt.type == "alert") {
                buttons = [
                    {text: 'Ok', close: true, id: 'confirmYes', style: 'primary'}
                ];
            }

            dlgopenResponce = await dlgopen(opt?.url,'confirmationbox', 400, 0, '', opt?.title, {
                buttons: buttons,
                type: 'confirm',
                html: opt?.html,
                resolvePromiseOn: 'confirm',
                allowDrag: opt?.allowDrag,
                allowResize: opt?.allowResize,
                sizeHeight: opt?.sizeHeight,
                callBack: {
                    call :() => {if(typeof dlgopenResponce === undefined || dlgopenResponce == undefined) {resolve(false);}}
                }
            });

            setTimeout(() => {
              resolve(dlgopenResponce ? dlgopenResponce : false);
            }, "500");
        }
    });

    let result = await promise; // wait until the promise resolves (*)
    return result;
}

/*------------ End -----------------*/


// Multi Element JS
$.fn.multielement = function (opts = {}) {
    let mainChilds = $(this).children()[0];
    let elementsWrapper = $(this).find('.m-elements-wrapper');
    let elementWrapper = $(this).find('.m-elements-wrapper .m-element-wrapper').eq(0);
    let rawClone = elementWrapper ? $(elementWrapper) : null;
    let addEle = $(this).find('.m-btn-add').eq(0);
    let removeEle = $(this).find('.m-btn-remove').eq(0);
    let self = this;
    let eCount = elementsWrapper ? elementsWrapper.children().length : 0;
    let fValues = opts.values ? opts.values : [];

    // Add elements
    this.addElement = function(fieldVals =  {}) {
        if(rawClone) {
            // Element Clone
            let eleClone = rawClone.eq(0).clone();

            if(Object.keys(fieldVals).length > 0) {
                for (let fieldName in fieldVals) {
                    let fVal = fieldVals[fieldName] ? fieldVals[fieldName] : '';
                    let ce = eleClone.find('[data-field-id="'+fieldName+'"]').eq(0);

                    if(fVal != "") {
                        ce.val(fVal);
                    } else {
                        ce.val('');
                    }
                }
            } else {
                // Set Value
                $(eleClone).find('input:text').val('');
                $(eleClone).find('select').val('');

                $(eleClone).find('.c-text-info').html('');
            }

            // Append Value
            $(elementsWrapper.eq(0)).append(eleClone);

            eCount++;
        }
    }

    // Remove elements
    this.removeElement = function(event) {
        let targetElement = event.target || event.srcElement;
        let cElement = $(targetElement).closest('.m-element-wrapper').eq(0);

        if(eCount > 1) {
            $(cElement).remove();
        } else {
            $(cElement).find('input:text').val('');
            $(cElement).find('select').val('');

            $(cElement).find('.c-text-info').html('');
        }

        eCount--;
    }

    $(this).on('click', '.m-btn-add', function() {
        self.addElement();
    });

    $(this).on('click', '.m-btn-remove', function(event) {
        self.removeElement(event);
    });

    // Set inti values
    fValues.forEach((s,i) => {
       self.addElement(s);
    });
}

/*------------------- (Message Attachment) ----------------*/

// File Attachments
$.fn.attachment = function (opts = {}) {
    if(this[0] == undefined) return false;

    this.docsList = opts.docsList ? opts.docsList : [];
    this.attachments = opts.attachments ? opts.attachments : [];
    this.clickable_link = opts.clickable_link ? opts.clickable_link : false;

    this.selectedFileList = [];
    this.demoins_inc_demographic = null;
    
    let fileIdCounter = 0;
    const attachmentEvent = new Event('change');

    this.fileUploader = function(evt) {
        var output = [];
        for (var i = 0; i < evt.target.files.length; i++) {
            fileIdCounter++;
            var file = evt.target.files[i];
            var fileId = 'file_' + fileIdCounter;

            this.selectedFileList.push({
                type: 'files',
                id: fileId,
                file: file
            });
        }

        evt.target.value = null;

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    };

    this.fileUploaderRemoveFile = function(itemId) {
        let tempThis = this;
        this.selectedFileList.forEach(function (item, index) {
            if(item.type) {
                if(item.id == itemId) {
                    tempThis.selectedFileList.splice(index, 1);
                }
            }
        });

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.fileUploaderPrepareFile = function() {
        let itemList = [];
        let tempThis = this;

        let ulClass = opts['ulClass'] == undefined ? "list-group" : opts['ulClass'];
        let liClass = opts['liClass'] == undefined ? "list-group-item list-group-item-primary px-3 py-1" : opts['liClass'];
        let defaultliClass = opts['defaultLiClass'] == undefined ? "list-group-item list-group-item-primary px-4 py-2.5" : opts['defaultLiClass'];
        let typeClass = opts['typeClass'] == undefined ? "badge badge-dark" : opts['typeClass'];

        //Replace exsting content;
        tempThis[0].innerHTML = "";

        //Ul Item
        let ulItem = document.createElement('ul');
        ulItem.className = ulClass;

        this.selectedFileList.forEach(function (item, index) {
            // Skip iteration 
            if(item['hidden'] != undefined && item['hidden'] === true) {
                return;
            }

            if(item.type == "files") {
                //Item remove link.
                let removeLink = document.createElement('a');
                removeLink.innerHTML = 'Remove';
                removeLink.href = "javascript:void(0);";
                removeLink.onclick = () => {
                    tempThis.fileUploaderRemoveFile(item.id);
                }

                //Li items.
                let liItem = document.createElement('li');
                liItem.className = liClass;
                liItem.innerHTML = "<span><span>" + escape(item.file.name) + "</span> - <span>" + item.file.size + " bytes.&nbsp;<span class=\"" + typeClass + "\">File</span>&nbsp;</span> - </span>";
                liItem.appendChild(removeLink);

                ulItem.appendChild(liItem);
            } if(item.type == "local_files") {
                let itemData = item.item ? item.item : {};
                let iData = itemData['data'] ? itemData['data'] : {};

                // Item remove link.
                let removeLink = document.createElement('a');
                removeLink.innerHTML = 'Remove';
                removeLink.href = "javascript:void(0);";
                removeLink.onclick = () => {
                    tempThis.fileLocalFileRemoveFile(item.id);
                }

                //Li items.
                let liItem = document.createElement('li');
                liItem.className = liClass;
                liItem.innerHTML = "<span><span>" + escape(iData.file_name) + "</span> <span class=\"" + typeClass + "\">File</span>&nbsp - </span>";
                liItem.appendChild(removeLink);

                ulItem.appendChild(liItem);
            } else if(item.type == "documents") {
                let itemData = item.item ? item.item : {};

                //Item remove link.
                let removeLink = document.createElement('a');
                removeLink.innerHTML = 'Remove';
                removeLink.href = "javascript:void(0);";
                removeLink.onclick = () => {
                    tempThis.removeDocument(item.id);
                }

                //Li items.
                let liItem = document.createElement('li');
                liItem.className = liClass;
                let clickFun = tempThis.clickable_link === true ? "gotoReport('"+itemData.data['doc_id']+"','"+itemData['pid']+"')" : "";

                liItem.innerHTML = "<span><a href=\"javascript:void(0);\" onClick=\""+clickFun+"\">" + itemData['text_title'] + "</a>&nbsp;<span class=\"" + typeClass + "\">Document</span>&nbsp;- </span>";
                liItem.appendChild(removeLink);

                ulItem.appendChild(liItem);
            } else if(item.type == "encounters") {
                let itemData = item.item ? item.item : {};

                //Item remove link.
                let removeLink = document.createElement('a');
                removeLink.innerHTML = 'Remove';
                removeLink.href = "javascript:void(0);";
                removeLink.onclick = () => {
                    tempThis.removeEncounter(item.id);
                }

                //Li items.
                let liItem = document.createElement('li');
                liItem.className = liClass;

                let clickFun = tempThis.clickable_link === true ? "handleGoToEncounter('"+itemData.data['encounter_id']+"','"+itemData['pid']+"')" : "";
                
                liItem.innerHTML = "<span><a href=\"javascript:void(0);\" onClick=\""+clickFun+"\">" + removeBackslash(itemData['text_title']) + "</a>&nbsp;<span class=\"" + typeClass + "\">Encounter</span>&nbsp;- </span>";
                liItem.appendChild(removeLink);

                ulItem.appendChild(liItem);
            } else if(item.type == "encounter_forms") {
                let itemData = item.item ? item.item : {};
                let formid = itemData['id'] ? itemData['id'] : '';

                if(itemData['parentId'] == undefined) {
                    //Item remove link.
                    let removeLink = document.createElement('a');
                    removeLink.innerHTML = 'Remove';
                    removeLink.href = "javascript:void(0);";
                    removeLink.onclick = () => {
                        tempThis.removeEncounterForm(item.id);
                    }

                    //Li items.
                    let liItem = document.createElement('li');
                    liItem.className = liClass;

                    let clickFun = tempThis.clickable_link === true ? "handleGoToEncounter('"+itemData.data['formid']+"','"+itemData['pid']+"')" : "";

                    liItem.innerHTML = "<span><a href=\"javascript:void(0);\">" + removeBackslash(itemData['text_title']) + "</a>&nbsp;<span class=\"" + typeClass + "\">Encounter Form</span>&nbsp;- </span>";

                    //Generate Child

                    //Child Ul Item
                    let isChildExists = false;
                    let culItem = document.createElement('ul');
                    //culItem.className = ulClass;

                    tempThis.selectedFileList.forEach(function (cItem, cIndex) {
                        if(cItem.type == "encounter_forms") {
                            let cItemData = cItem.item ? cItem.item : {};
                            if(cItemData['parentId'] == formid) {
                                //Child Item remove link.
                                let cremoveLink = document.createElement('a');
                                cremoveLink.innerHTML = 'Remove';
                                cremoveLink.href = "javascript:void(0);";
                                cremoveLink.onclick = () => {
                                    tempThis.removeEncounterForm(cItem.id);
                                }

                                //Child Li items.
                                let cliItem = document.createElement('li');
                                //cliItem.className = liClass;
                                cliItem.innerHTML = "<span><a href=\"javascript:void(0);\">" + removeBackslash(cItemData['text_title']) + "</a>&nbsp;<span class=\"" + typeClass + "\">Encounter Form</span>&nbsp;- </span>";
                                cliItem.appendChild(cremoveLink);
                                culItem.appendChild(cliItem);

                                isChildExists = true;
                            }
                        }
                    });

                    liItem.appendChild(removeLink);

                    //Add Child
                    if(isChildExists === true) {
                        liItem.appendChild(culItem);
                    }

                    ulItem.appendChild(liItem);
                }
            } else if(item.type == "messages") {
                let itemData = item.item ? item.item : {};

                //Item remove link.
                let removeLink = document.createElement('a');
                removeLink.innerHTML = 'Remove';
                removeLink.href = "javascript:void(0);";
                removeLink.onclick = () => {
                    tempThis.removeMessage(item.id);
                }

                //Li items.
                let liItem = document.createElement('li');
                liItem.className = liClass;

                let clickFun = tempThis.clickable_link === true ? "handleGoToMessage('"+itemData.data['message_id']+"','"+itemData['pid']+"')" : "";

                liItem.innerHTML = "<span><a href=\"javascript:void(0);\" onClick=\""+clickFun+"\">" + removeBackslash(itemData['text_title']) + "</a>&nbsp;<span class=\"" + typeClass + "\">Message</span>&nbsp;- </span>";
                liItem.appendChild(removeLink);

                ulItem.appendChild(liItem);
            } else if(item.type == "orders") {
                let itemData = item.item ? item.item : {};

                //Item remove link.
                let removeLink = document.createElement('a');
                removeLink.innerHTML = 'Remove';
                removeLink.href = "javascript:void(0);";
                removeLink.onclick = () => {
                    tempThis.removeOrder(item.id);
                }

                //Li items.
                let liItem = document.createElement('li');
                liItem.className = liClass;

                let clickFun = tempThis.clickable_link === true ? "handleGoToOrder('"+itemData.data['order_id']+"','"+itemData['pid']+"')" : "";

                liItem.innerHTML = "<span><a href=\"javascript:void(0);\" onClick=\""+clickFun+"\">" + removeBackslash(itemData['text_title']) + "</a>&nbsp;<span class=\"" + typeClass + "\">Order</span>&nbsp;- </span>";
                liItem.appendChild(removeLink);

                ulItem.appendChild(liItem);
            } else if(item.type == "demos_insurances") {
                let itemData = item.item ? item.item : {};

                //Item remove link.
                let removeLink = document.createElement('a');
                removeLink.innerHTML = 'Remove';
                removeLink.href = "javascript:void(0);";
                removeLink.onclick = () => {
                    tempThis.removeDemosIns(item.id);
                }

                //Li items.
                let liItem = document.createElement('li');
                liItem.className = liClass;
                let clickFun = "";
                liItem.innerHTML = "<span><a href=\"javascript:void(0);\" onClick=\""+clickFun+"\">" + removeBackslash(itemData['text_title']) + "</a>&nbsp;<span class=\"" + typeClass + "\">Demos & Ins</span>&nbsp;- </span>";
                liItem.appendChild(removeLink);

                //Generate Child

                //Child Ul Item
                let isChildExists = false;
                let culItem = document.createElement('ul');
                
                if(itemData['childs']) {
                    let iChilds = itemData['childs'];
                    Object.keys(iChilds).forEach(function(key) {
                        let cItemData = iChilds[key];
                        
                        //Child Item remove link.
                        let cremoveLink = document.createElement('a');
                        cremoveLink.innerHTML = 'Remove';
                        cremoveLink.href = "javascript:void(0);";
                        cremoveLink.onclick = () => {
                            tempThis.removeDemosIns(item.id, key);
                        }

                        //Child Li items.
                        let cliItem = document.createElement('li');
                        //cliItem.className = liClass;
                        cliItem.innerHTML = "<span><a href=\"javascript:void(0);\">" + removeBackslash(cItemData['text_title']) + "</a>&nbsp;<span class=\"" + typeClass + "\">Demos & Ins</span>&nbsp;- </span>";
                        cliItem.appendChild(cremoveLink);
                        culItem.appendChild(cliItem);

                        isChildExists = true;
                    });
                }

                //Add Child
                if(isChildExists === true) {
                    liItem.appendChild(culItem);
                }

                ulItem.appendChild(liItem);
            }
        });
    
        // Filter hidden items
        let tSelectedFileList = this.selectedFileList.filter((item) => {
            if(item['hidden'] == undefined || item['hidden'] === false) {
                return item;
            }
        });

        if(tSelectedFileList.length > 0) {
            tempThis[0].appendChild(ulItem);
        } else {
            if(opts.empty_title != "") {
                tempThis[0].innerHTML = "<ul class=\"" + ulClass + "\"><li class=\"" + defaultliClass + "\">" + opts.empty_title + "</li></ul>"
            }
        }
    };

    this.handleDocument = async function(pid) {
        let url = top.webroot_url + "/library/OemrAD/interface/main/messages/msg_select_document.php?pid="+pid;
        let dialogObj = "";

        dialogObj = await dlgopen(url,'selectDocPop', 'modal-mlg', '', '', 'Documents', {
            buttons: [
                {text: 'Submit', close: false, click: () => { this.handleDocumentCallBack(dialogObj) }, style: 'primary documentsaveBtn btn-sm'},
                {text: 'Close', close: true, style: 'secondary btn-sm'}
            ],
            sizeHeight: 'full',
            onClosed: '',
            type: 'iframe',
            callBack: {call : '', args : pid}
        });

        //Set Values
        this.setValues(dialogObj.modalwin, this.getItemsList('documents'));

        dialogLoader(dialogObj.modalwin);
    }

    this.handleDocumentCallBack = function(dialogObj) {
        if(dialogObj.modalwin) {
            let type = 'documents';
            let iframeContent = this.getIframeContentWindow(dialogObj.modalwin);
            let documentList = iframeContent.getSelectedDocumentList();
            let tempThis = this;

            this.removeAllItems(type);

            documentList.forEach(function (item, idx) {
                fileIdCounter++;
                let fileId = type + '_' + fileIdCounter;

                tempThis.selectedFileList.push({
                    type: type,
                    id: fileId,
                    item: item,
                    row_item: [item]
                });
            });

            dialogObj.dlgContainer.modal('hide');
        }

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.removeDocument = function(itemId) {
        let tempThis = this;
        this.selectedFileList.forEach(function (item, index) {
            if(item.type) {
                if(item.id == itemId) {
                    tempThis.selectedFileList.splice(index, 1);
                }
            }
        });

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.handleEncounter = async function(pid) {
        let url = top.webroot_url + "/library/OemrAD/interface/main/messages/msg_select_encounter.php?pid="+pid;
        let dialogObj = "";

        dialogObj = await dlgopen(url,'selectEncounterPop', 'modal-mlg', '', '', 'Encounter', {
            buttons: [
                {text: 'Submit', close: false, click: () => { this.handleEncounterCallBack(dialogObj) }, style: 'primary documentsaveBtn btn-sm'},
                {text: 'Close', close: true, style: 'secondary btn-sm'}
            ],
            sizeHeight: 'full',
            onClosed: '',
            type: 'iframe',
            callBack: {call : '', args : pid}
        });

        //Set Values
        this.setValues(dialogObj.modalwin, this.getItemsList('encounters'));

        dialogLoader(dialogObj.modalwin);
    }

    this.handleEncounterCallBack = function(dialogObj) {
        if(dialogObj.modalwin) {
            let type = 'encounters';
            let iframeContent = this.getIframeContentWindow(dialogObj.modalwin);
            let encounterList = iframeContent.getSelectedEncounterList();
            let tempThis = this;

            this.removeAllItems(type);

            encounterList.forEach(function (item, idx) {
                fileIdCounter++;
                let fileId = type + '_' + fileIdCounter;

                tempThis.selectedFileList.push({
                    type: type,
                    id: fileId,
                    item: item,
                    row_item: [item]
                });
            });

            dialogObj.dlgContainer.modal('hide');
        }

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.removeEncounter = function(itemId) {
        let tempThis = this;
        this.selectedFileList.forEach(function (item, index) {
            if(item.type) {
                if(item.id == itemId) {
                    tempThis.selectedFileList.splice(index, 1);
                }
            }
        });

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.handleMessage = async function(pid, opts = {}) {
        let url = top.webroot_url + "/library/OemrAD/interface/main/messages/msg_select_messages.php?pid="+pid+"&assigned_to="+opts?.assigned_to;
        let dialogObj = "";

        dialogObj = await dlgopen(url,'selectMsgPop', 'modal-mlg', '', '', 'Message', {
            buttons: [
                {text: 'Submit', close: false, click: () => { this.handleMessageCallBack(dialogObj) }, style: 'primary documentsaveBtn btn-sm'},
                {text: 'Close', close: true, style: 'secondary btn-sm'}
            ],
            sizeHeight: 'full',
            onClosed: '',
            type: 'iframe',
            callBack: {call : '', args : pid}
        });

        //Set Values
        this.setValues(dialogObj.modalwin, this.getItemsList('messages'));

        dialogLoader(dialogObj.modalwin);
    }

    this.handleMessageCallBack = function(dialogObj) {
        if(dialogObj.modalwin) {
            let type = 'messages';
            let iframeContent = this.getIframeContentWindow(dialogObj.modalwin);
            let messageList = iframeContent.getSelectedMessageList();
            let tempThis = this;

            this.removeAllItems(type);

            messageList.forEach(function (item, idx) {
                fileIdCounter++;
                let fileId = type + '_' + fileIdCounter;

                tempThis.selectedFileList.push({
                    type: type,
                    id: fileId,
                    item: item,
                    row_item: [item]
                });
            });

            dialogObj.dlgContainer.modal('hide');
        }

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.removeMessage = function(itemId) {
        let tempThis = this;
        this.selectedFileList.forEach(function (item, index) {
            if(item.type) {
                if(item.id == itemId) {
                    tempThis.selectedFileList.splice(index, 1);
                }
            }
        });

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.handleOrder = async function(pid) {
        let url = top.webroot_url + "/library/OemrAD/interface/main/messages/msg_select_order.php?pid="+pid;
        let dialogObj = "";

        dialogObj = await dlgopen(url,'selectOrderPop', 'modal-mlg', '', '', 'Order', {
            buttons: [
                {text: 'Submit', close: false, click: () => { this.handleOrderCallBack(dialogObj) }, style: 'primary documentsaveBtn btn-sm'},
                {text: 'Close', close: true, style: 'secondary btn-sm'}
            ],
            sizeHeight: 'full',
            onClosed: '',
            type: 'iframe',
            callBack: {call : '', args : pid}
        });

        //Set Values
        this.setValues(dialogObj.modalwin, this.getItemsList('orders'));

        dialogLoader(dialogObj.modalwin);
    }

    this.handleOrderCallBack = function(dialogObj) {
        if(dialogObj.modalwin) {
            let type = 'orders';
            let iframeContent = this.getIframeContentWindow(dialogObj.modalwin);
            let orderList = iframeContent.getSelectedOrderList();
            let tempThis = this;

            this.removeAllItems(type);

            orderList.forEach(function (item, idx) {
                fileIdCounter++;
                let fileId = type + '_' + fileIdCounter;

                tempThis.selectedFileList.push({
                    type: type,
                    id: fileId,
                    item: item,
                    row_item: [item]
                });
            });

            dialogObj.dlgContainer.modal('hide');
        }

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.removeOrder = function(itemId) {
        let tempThis = this;
        this.selectedFileList.forEach(function (item, index) {
            if(item.type) {
                if(item.id == itemId) {
                    tempThis.selectedFileList.splice(index, 1);
                }
            }
        });

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.handleEncounterForm = async function(pid) {
        let url = top.webroot_url + "/library/OemrAD/interface/main/messages/msg_select_encounter_form.php?pid="+pid;
        let dialogObj = "";

        dialogObj = await dlgopen(url,'selectEncounterFormPop', 'modal-mlg', '', '', 'Encounters & Forms', {
            buttons: [
                {text: 'Submit', close: false, click: () => { this.handleEncounterFormCallBack(dialogObj) }, style: 'primary documentsaveBtn btn-sm'},
                {text: 'Close', close: true, style: 'secondary btn-sm'}
            ],
            sizeHeight: 'full',
            onClosed: '',
            type: 'iframe',
            callBack: {call : '', args : pid}
        });

        //Set Values
        this.setValues(dialogObj.modalwin, this.getItemsList('encounter_forms'));

        dialogLoader(dialogObj.modalwin);
    }

    this.handleEncounterFormCallBack = function(dialogObj) {
        if(dialogObj.modalwin) {
            let type = 'encounter_forms';
            let iframeContent = this.getIframeContentWindow(dialogObj.modalwin);
            let encounterFormList = iframeContent.getSelectedEncounterFormList();
            let tempThis = this;

            this.removeAllItems(type);

            encounterFormList.forEach(function (item, idx) {
                fileIdCounter++;
                let fileId = type + '_' + fileIdCounter;

                tempThis.selectedFileList.push({
                    type: type,
                    id: fileId,
                    item: item,
                    row_item: [item]
                });
            });

            dialogObj.dlgContainer.modal('hide');
        }

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.removeEncounterForm = function(itemId) {
        let tempThis = this;
        let deleteItems = [];

        this.selectedFileList.forEach(function (item, index) {
            if(item.type) {
                if(item.id == itemId) {
                    let itemData = item.item ? item.item : {};
                    let formid = itemData['id'] ? itemData['id'] : '';

                    deleteItems.push(index);

                    if(itemData['parentId'] == undefined && formid != '') {
                        tempThis.selectedFileList.forEach(function (citem, cindex) {
                            if(citem.type == "encounter_forms") {
                                let cItemData = citem.item ? citem.item : {};
                                if(cItemData['parentId'] == formid) {
                                    deleteItems.push(cindex);
                                }
                            }
                        });
                    }
                }
            }
        });

        for (var i = deleteItems.length -1; i >= 0; i--) {
            tempThis.selectedFileList.splice(deleteItems[i], 1);
        }

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.handleDemosIns = async function(pid) {
        let url = top.webroot_url + "/library/OemrAD/interface/main/messages/msg_select_demos_ins.php?pid="+pid;
        let dialogObj = "";

        dialogObj = await dlgopen(url,'selectDemosInsPop', 'modal-mlg', '', '', 'Demos & Insurances', {
            buttons: [
                {text: 'Submit', close: false, click: () => { this.handleDemosInsCallBack(dialogObj) }, style: 'primary documentsaveBtn btn-sm'},
                {text: 'Close', close: true, style: 'secondary btn-sm'}
            ],
            sizeHeight: 'full',
            onClosed: '',
            type: 'iframe',
            callBack: {call : '', args : pid}
        });

        //Set Values
        this.setValues(dialogObj.modalwin, this.getItemsList('demos_insurances'));

        if(dialogObj.modalwin && this.demoins_inc_demographic != null) {
            $(dialogObj.modalwin).find('iframe')[0].contentWindow.demoins_inc_demographic = this.demoins_inc_demographic;
        }

        dialogLoader(dialogObj.modalwin);
    }

    this.handleDemosInsCallBack = function(dialogObj) {
        if(dialogObj.modalwin) {
            let type = 'demos_insurances';
            let iframeContent = this.getIframeContentWindow(dialogObj.modalwin);
            let demoInsList = iframeContent.getSelectedDemoInsList();
            let tempThis = this;

            this.demoins_inc_demographic = iframeContent.getNeedToIncludeDemographic();

            this.removeAllItems(type);

            demoInsList.forEach(function (item, idx) {
                fileIdCounter++;
                let fileId = type + '_' + fileIdCounter;

                tempThis.selectedFileList.push({
                    type: type,
                    id: fileId,
                    item: item,
                    row_item: [item]
                });
            });

            dialogObj.dlgContainer.modal('hide');
        }

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.removeDemosIns = function(itemId, cItemId = '') {
        let tempThis = this;
        this.selectedFileList.forEach(function (item, index) {
            if(item.type) {
                if(item.id == itemId) {
                    if(itemId != '' && cItemId != '') {
                        let itemData = item['item'] ? item['item'] : {};
                        if(itemData['childs']) {
                            let iChilds = itemData['childs'];
                            Object.keys(iChilds).forEach(function(key) {
                                let cItemData = iChilds[key];

                                if(key == cItemId) {
                                    delete tempThis.selectedFileList[index]['item']['childs'][cItemId];
                                    delete tempThis.selectedFileList[index]['row_item'][0]['childs'][cItemId];
                                }
                            });

                            if(Object.keys(tempThis.selectedFileList[index]['item']['childs']).length === 0) {
                                tempThis.selectedFileList.splice(index, 1);
                            }
                        }
                    } else {
                        tempThis.selectedFileList.splice(index, 1);
                    }
                }
            }
        });

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.fileLocalFileRemoveFile = function(itemId) {
        let tempThis = this;
        this.selectedFileList.forEach(function (item, index) {
            if(item.type) {
                if(item.id == itemId) {
                    tempThis.selectedFileList.splice(index, 1);
                }
            }
        });

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    //Remove all items by type.
    this.removeAllItems = function(type) {
        let tempThis = this;

        //Remove all.
        const items = this.selectedFileList.filter(function (item) {
            if(item.type != type) { 
                return item;
            }
        });

        this.selectedFileList = items;
    }

    // Get items list by type.
    this.getItemsList = function(type) {
        if(["documents", "encounters", "messages", "orders", "encounter_forms", "demos_insurances", "local_files"].includes(type)) {
            let items = [];
            this.selectedFileList.forEach(function (item, index) {
                if(item.type == type) {
                    if(Array.isArray(item['row_item'])) {
                        items = items.concat(item.row_item);
                    }
                }
            });
            return items;
        } else {
            let items = {};
            this.selectedFileList.forEach(function (item, index) {
                if(item.type == type) {
                    if(item['row_item'] != '') {
                        items = {...items, ...item.row_item};
                    }
                }
            });
            return items;
        }
    }

    // Get items list by type.
    this.getItemsDataList = function(type) {
        if(type == "files") {
            let items = [];
            this.selectedFileList.forEach(function (item, index) {
                if(item.type == type) {
                    //if(Array.isArray(item['file'])) {
                        items = items.concat([{file: item.file}]);
                    //}
                }
            });
            return items;
        } if(type == "demos_insurances") {
            let items = [];
            this.selectedFileList.forEach(function (item, index) {
                if(item.type == type) {
                    if(Array.isArray(item['row_item']) && item['row_item'][0]['data']) {
                        let nData = item.row_item[0].data;
                        let nChildsData = item.row_item[0].childs;

                        if(nData) {
                            nData['childs'] = {};
                            Object.keys(nChildsData).forEach(function(key) {
                                nData['childs'][key] = nChildsData[key].data;
                            });
                        }
                        items = items.concat([nData]);
                    }
                }
            });
            return items;
        } if(["documents", "encounters", "messages", "orders", "encounter_forms", "local_files"].includes(type)) {
            let items = [];
            this.selectedFileList.forEach(function (item, index) {
                if(item.type == type) {
                    if(Array.isArray(item['row_item']) && item['row_item'][0]['data']) {
                        items = items.concat([item.row_item[0].data]);
                    }
                }
            });
            return items;
        } else {
            let items = {};
            this.selectedFileList.forEach(function (item, index) {
                if(item.type == type) {
                    if(item['row_item'] != '' && item['row_item'][0]['data'] != '') {
                        items = {...items, ...item.row_item[0].data};
                    }
                }
            });
            return items;
        }
    }

    //Set items list by type.
    this.setItemsList = function(itemList, hidden = false) {
        let tempThis = this;

        jQuery.each(itemList, function(type, items) {
            // If it is array
            if(Array.isArray(items)) {
                items.forEach(function (item, itemIndex) {
                    fileIdCounter++;
                    let fileId = type + '_' + fileIdCounter;

                    tempThis.selectedFileList.push({
                        type: type,
                        id: fileId,
                        item: item,
                        hidden: hidden,
                        row_item: [item]
                    });
                });
            } else {
                if(type == "demoins_inc_demographic") {
                    tempThis.demoins_inc_demographic = items;
                }
            }
        });

        // Dispatch the event.
        this[0].dispatchEvent(attachmentEvent);
    }

    this.prepareFiles = function() {
        this.fileUploaderPrepareFile();

        if (opts.onPrepareFiles) {
            let callParam = {
                orders: this.getItemsDataList('orders'),
                messages: this.getItemsDataList('messages'),
                encounters: this.getItemsDataList('encounters'),
                encounter_forms: this.getItemsDataList('encounter_forms'),
                documents: this.getItemsDataList('documents'),
                files: this.getItemsDataList('files'),
                local_files: this.getItemsDataList('local_files'),
                demos_insurances: this.getItemsDataList('demos_insurances')
            }

            if (typeof opts.onPrepareFiles == 'string') {
                window[opts.onPrepareFiles](callParam);
            } else {
                opts.onPrepareFiles.call(this, callParam);
            }
        }
    }

    this.getIframeContentWindow = function(modalwin) {
        return $(modalwin).find('iframe')[0].contentWindow;
    }

    this.setValues = function(modalwin, values) {
        let tempThis = this;
        if(modalwin) {
            $(modalwin).find('iframe')[0].contentWindow.items = values;
        }
    }

    this.appendDataToForm = function(formData) {
        //Organize the file data
        let fileItems = this.getItemsDataList('files');
        formData.append("files_length", fileItems.length);
        for (var i = 0; i < fileItems.length; i++) {
            formData.append("files["+i+"]", fileItems[i].file);
        }

        //organize the document data
        let documentItems = this.getItemsDataList('documents');
        if(documentItems && documentItems.length > 0) {
            formData.append("documents", JSON.stringify(documentItems));
        }

        //organize the document data
        let noteItems = this.getItemsDataList('notes');
        if(noteItems && noteItems.length > 0) {
            formData.append("notes", JSON.stringify(noteItems));
        }

        //organize the order data
        let orderItems = this.getItemsDataList('orders');
        if(orderItems && orderItems.length > 0) {
            formData.append("orders", JSON.stringify(orderItems));
        }

        // organize the encounter data
        let encounterFormItems = this.getItemsDataList('encounter_forms');
        if(encounterFormItems && encounterFormItems.length > 0) {
            /*var tempencounters = {};
            jQuery.each(encounterFormItems, function(i, n){
                let record = { text_title : n['title'], form_id: n['value'], id : i, pid : n['pid'] };
                if(n['parentId'] == undefined) {
                    tempencounters[i] = record;
                    tempencounters[i]['child'] = [];
                } else {
                    if(tempencounters[n['parentId']] != undefined) {
                        tempencounters[n['parentId']]['child'].push(record);
                    } else {
                        tempencounters[i] = record;
                    }
                }
            });*/
            formData.append("encounter_forms", JSON.stringify(encounterFormItems));
        }

        // organize the encounter data
        let demoInsItems = this.getItemsDataList('demos_insurances');
        if(demoInsItems && demoInsItems.length > 0) {
            formData.append("demos_insurances", JSON.stringify(demoInsItems));
        }

        // organize the local file
        let localfileItems = this.getItemsDataList('local_files');
        if(localfileItems) {
            formData.append("local_files", JSON.stringify(localfileItems));
        }

        // organize the local file
        let attachItems = this.getItemsDataList('attachment_files');
        if(attachItems) {
            formData.append("attachment_files", JSON.stringify(attachItems));
        }

        //formData.append("isCheckEncounterDemo", this.checkEncounterDemo);
        formData.append("demoins_inc_demographic", this.demoins_inc_demographic);
    }

    //Intial
    this.prepareFiles();

    // Listen for the event.
    this[0].addEventListener('change', (e) => {
        this.prepareFiles();
    }, false);

    return this;
}

function removeBackslash(b) {
    var str = b.replace(/\\/g, '');
    return str;
}

async function handleSetPatient(pid) {
    var bodyObj = { set_pid : pid};
    const result = await $.ajax({
        type: "GET",
        url: top.webroot_url + "/library/OemrAD/interface/new/ajax/set_patient.php",
        datatype: "json",
        data: bodyObj
    });

    return true;
}

function handleSetPatientData(pid, pubpid, pname, dobstr) {
    parent.left_nav.setPatient(pname, pid, pubpid, '',dobstr);
}

// used to display the patient demographic and encounter screens
async function handleGoToMessage(id, pid, pubpid, pname, dobstr) {
    await handleSetPatient(pid);
    handleSetPatientData(pid, pubpid, pname, dobstr);
    top.RTop.location = top.webroot_url + "/interface/main/messages/messages.php?task=edit&noteid="+id;
}

async function handleGoToOrder(id, pid, pubpid, pname, dobstr) {
    await handleSetPatient(pid);
    handleSetPatientData(pid, pubpid, pname, dobstr);
    top.RTop.location = top.webroot_url + "/interface/forms/rto1/new.php?pop=db&id="+id;
}

function handleGoToEncounter(pid, pubpid, pname, enc, dobstr) {
    top.restoreSession();
    handleLoadpatient(pid,enc);
}

// used to display the patient demographic and encounter screens
function handleLoadpatient(newpid, enc) {
    if ($('#setting_new_window').val() === 'checked') {
        document.fnew.patientID.value = newpid;
        document.fnew.encounterID.value = enc;
        document.fnew.submit();
    }
    else {
        if (enc > 0) {
            top.RTop.location = top.webroot_url + "/interface/patient_file/summary/demographics.php?set_pid=" + newpid + "&set_encounterid=" + enc;
        }
        else {
            top.RTop.location = top.webroot_url + "/interface/patient_file/summary/demographics.php?set_pid=" + newpid;
        }
    }
}

// To view message.
function setMessage(type, message_id, pid) {
    loadMessage(type, message_id, pid)
}

// To view messages by message type.
function loadMessage(type, id, pid) {
    var url = top.webroot_url + "/interface/main/messages/portal_message.php?pid="+pid+"&id=" + id;
    if (type == 'PHONE') url = top.webroot_url + "/interface/main/messages/phone_call.php?pid="+pid+"&id=" + id;
    if (type == 'SMS') url = top.webroot_url + "/interface/main/messages/sms_message.php?pid="+pid+"&id=" + id + "&onlymsg=1";
    if (type == 'EMAIL') url = top.webroot_url + "/interface/main/messages/email_message.php?pid="+pid+"&id=" + id + "&enable_btn=reply";
    if (type == 'FAX') url = top.webroot_url + "/interface/main/messages/fax_message.php?pid="+pid+"&id=" + id;
    if (type == 'P_LETTER') url = top.webroot_url + "/interface/main/messages/postal_letter.php?pid="+pid+"&id=" + id;
    dlgopen(url, 'view_msg', 700, 500);
}

function handleDataTable(tableEle = null, noFooter = false) {
    if(tableEle) {
        var tableId = $(tableEle).attr('id');
        var tableWrapper = $(tableEle).closest('#' + tableId + '_wrapper');
        
        if(noFooter === false) {
            $(tableWrapper).find('div.row:last-child').addClass('footer-container border-bottom border-top');
        } else {
            $(tableWrapper).find('div.row:last-child').addClass('no-footer-container border-top');
        }
    }
}

/*------------------- End ----------------*/


/*------------------- (Others) ----------------*/

async function isGroupUserExists(userVal) {
    var select_val = userVal;

    const result = await $.ajax({
        type: "GET",
        url: (top.webroot_url + "/library/OemrAD/interface/main/messages/ajax/check_group_user_exists.php?user="+select_val),
        datatype: "json",
    });

    if(result != '') {
        var resultObj = JSON.parse(result);
        if(resultObj && resultObj['status'] == true && resultObj['isGroup'] == true) {
            if(resultObj['data'] && Number(resultObj['data']) == 0) {
                //alert("Selected group doesn't have a valid member.")
                await confirmBoxModal({
                    type: 'alert',
                    title: "Alert",
                    html: "Selected group doesn't have a valid member."
                });
            }
        }
    }
}

/*------------------- End ----------------*/

/* Utiliy */

function isJson(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }

    return true;
}

function getJson(str) {
    try {
        return JSON.parse(str);
    } catch (e) {
        return str;
    }
}

/* End */ 

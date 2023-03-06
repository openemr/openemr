// Check is patient exitsts ("new_comprehensive.php")
async function isPatientExists(){
    var firstName = $('#DEM #form_fname').val(); 
    var lastName = $('#DEM #form_lname').val();
    var dob = $('#DEM #form_DOB').val();

    //Call webservice
    var responce = callPatientVerificationService(firstName, lastName, dob);

    if (
        responce != null &&
        responce["isExists"] != undefined &&
        responce["isExists"] != "null"
    ) {
        var isExistsResponce = responce["isExists"];

        if (isExistsResponce === true) {
            return confirm("*****WARNING****** - You are about to create a duplicate chart!!!!!! There is already a patient with the same first name, last name & birthdate.  Press CANCEL to abort..");
            // return await confirmBoxModal({
            //     type: "confirm",
            //     title: "Confirm",
            //     html: "*****WARNING****** - You are about to create a duplicate chart!!!!!! There is already a patient with the same first name, last name & birthdate.  Press CANCEL to abort..",
            // });
        }
    }

    return true;
}

// Call Patient Verification Service ("new_comprehensive.php")
async function callPatientVerificationService(firstName, lastName, dob) {
    let result;
    let ajaxurl = top.webroot_url + '/interface/new/ajax/ajax_patient_verification.php';

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
        alert('Something went wrong');
        // await confirmBoxModal({
        //     type: 'alert',
        //     title: "Alert",
        //     html: "Something went wrong."
        // });
    }

    return null;
}

// Confirmbox for unverified email ("new_comprehensive.php")
function handleConfimBox_NewComprehensive() {
    var submitElement = $('#DEM').parent().parent().parent().parent().find('#create');
    $(submitElement).prop('disabled', true);

    //Check Is PatientExists
    var isPatientExistsResponce = isPatientExists();

    if(isPatientExistsResponce === false) {
      $(submitElement).prop('disabled', false);
      return isPatientExistsResponce;
    }

    var statusVal = $('#DEM #form_email_direct_hidden_verification_status').val();
    var emailDirectVal = $('#DEM #form_email_direct').val();
    if(statusVal == "0" && emailDirectVal != "") {
        return confirm("Do you want to continue with unverified email?");
        // let verificationConfirmation = await confirmBoxModal({
        //     type: 'confirm',
        //     title: "Confirm",
        //     html: "Do you want to continue with unverified email?"
        // });
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
function handleBeforeSubmit_NewComprehensive(eleId) {
    const optionStatus = validateOptions(eleId);
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
function handleConfimBox_DemographicsFull() {
    var statusVal = $('#DEM #form_email_direct_hidden_verification_status').val();
  
    if(statusVal && statusVal == "0") {
        return confirm("Do you want to continue with unverified email?");
        // let verificationConfirmation = await confirmBoxModal({
        //     type: 'confirm',
        //     title: "Confirm",
        //     html: "Do you want to continue with unverified email?"
        // });

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
function handleOnSubmit_DemographicsFull(validationStatus, element, event, eleId) {
    event.preventDefault();

    if(validationStatus === false) return false;
    
    const optionStatus = validateOptions(eleId);
    if(optionStatus === false) {
        return false;
    }

    //Check Values
    checkSecondoryEmail(eleId);
    checkSecondoryPhone(eleId);

    var data = handleConfimBox_DemographicsFull();
    if(data == true) {
        element.submit();
        //submitme(validate, event, element, constraints);
    }
    
    return data;
}

// This invokes the find-addressbook popup.
async function open_notes_log(pid) {
    var url = top.webroot_url + '/interface/patient_file/summary/ajax/dem_view_logs.php'+'?pid='+pid;
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
        let alertInfoEle = document.querySelector('#form_current_alert_info');

        if(alertInfoEle != undefined) {
            alertInfoEle.value = alert_val;
        }
    });
});

/*-------------------End----------------*/


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
function validatePhoneNumber(ele, e = '') {
  var result = {
    'error' : false,
    'message' : 'Phone numbers must have 10 numbers, example 555-555-5555.  Please update [%s]. Press CANCEL to fix phone number.  Press OK to bypass error and save improperly formatted phone number'
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
function validateOptions(form_id) {
  
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
          var errorStatus = eval(eleValidation)(miInputEle);
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
      returnStatus = confirm(preErrorList[eKey]);
        // returnStatus = await confirmBoxModal({
        //     type: 'confirm',
        //     title: "Confirm",
        //     html: preErrorList[eKey]
        // });
    }
  }

  return returnStatus;
}

/*-------------------END----------------*/

/*------------------- (Encounter Form) ----------------*/

/*
async function handleConfimBox_feeCodeLinked(encounter, pid) {
    var bodyObj = { encounter :  encounter, pid : pid };
    const result = await $.ajax({
        type: "POST",
        url:  top.webroot_url + '/interface/forms/fee_sheet/ajax/get_feesheet_code_status.php',
        datatype: "json",
        data: bodyObj
    });

    if(result != '') {
        var resultObj = JSON.parse(result);
        if(resultObj && resultObj['feesheet_code_status'] === false) {
            if(!confirm("Warning - At least one CPT/HCPCS is not linked to an ICD in the fee sheet.  Press \"Cancel\" to back and justify all CPT/HCPCS codes or Press \"Ok\" to sign the encounter")) {
                return false;
            } else {
                return true;
            }
            // returnStatus = await confirmBoxModal({
            //     type: 'confirm',
            //     title: "Confirm",
            //     html: "Warning - At least one CPT/HCPCS is not linked to an ICD in the fee sheet.  Press \"Cancel\" to back and justify all CPT/HCPCS codes or Press \"Ok\" to sign the encounter"
            // });

            return returnStatus;
        } else {
            return true;
        }
    }

    return true;
}*/

/*
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
}*/

/*
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
            alert(responceJSON['message'].join('\n\n'));
            // await confirmBoxModal({
            //     type: 'alert',
            //     title: "Alert",
            //     html: responceJSON['message'].join('\n\n')
            // });
        }
    }
}*/

/*-------------------End----------------*/

/*------------------- (messages.php) ----------------*/

function MessageLib() {
    'use strict';

    let props = {
        attachClassObject: null,
        handleSelectEncounters: function() {
            let pid = $("#reply_to").val();

            if(pid == "") {
                alert("Please select patient");
                // await confirmBoxModal({
                //     type: 'alert',
                //     title: "Alert",
                //     html: "Please select patient"
                // });
                return false;
            }

            //Handle Encounter
            this.attachClassObject.handleEncounter(pid);
        },
        handleDocuments: function() {
            let pid = $("#reply_to").val();

            if(pid == "") {
                alert("Please select patient");
                // await confirmBoxModal({
                //     type: 'alert',
                //     title: "Alert",
                //     html: "Please select patient"
                // });
                return false;
            }


            //Handle Document
            this.attachClassObject.handleDocument(pid);
        },
        handleMessages: function(opts = {}) {
            let pid = $("#reply_to").val();
            let assigned_to = opts['assigned_to'] ? opts['assigned_to'] : "";

            if(pid == "") {
                alert("Please select patient");
                // await confirmBoxModal({
                //     type: 'alert',
                //     title: "Alert",
                //     html: "Please select patient"
                // });
                return false;
            }

            //Handle Message
            this.attachClassObject.handleMessage(pid, { assigned_to: assigned_to});
        },
        handleOrders: function() {
            let pid = $("#reply_to").val();

            if(pid == "") {
                alert("Please select patient");
                // await confirmBoxModal({
                //     type: 'alert',
                //     title: "Alert",
                //     html: "Please select patient"
                // });
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

function removeBackslash(b) {
    var str = b.replace(/\\/g, '');
    return str;
}

async function handleSetPatient(pid) {
    var bodyObj = { set_pid : pid};
    const result = await $.ajax({
        type: "GET",
        url: top.webroot_url + "/interface/new/ajax/set_patient.php",
        datatype: "json",
        data: bodyObj
    });

    if(result) {
        return JSON.parse(result);
    }

    return true;
}

function handleSetPatientData(pid, pubpid = '', pname = '', dobstr = '') {
    //parent.left_nav.setPatient(pname, pid, pubpid, '',dobstr);
    top.RTop.location = top.webroot_url + "/interface/patient_file/summary/demographics.php?set_pid=" + pid;
}

async function handlegotoCase(case_id, pid) {
    const pData = await handleSetPatient(pid);
    if(pData !== false && pData['data']) {
        handleSetPatientData(pid, pData['data']['pubpid'], pData['data']['pname'], pData['data']['pdob']);
    }

    parent.left_nav.loadFrame('RTop', 'RTop', '/forms/cases/view.php?id='+case_id+'&pid='+pid+'&list_mode=list&list_popup=&popup=no&caller=patient');
}

async function handlegotoReport(doc_id, pid) {
    const pData = await handleSetPatient(pid);
    if(pData !== false && pData['data']) {
        handleSetPatientData(pid, pData['data']['pubpid'], pData['data']['pname'], pData['data']['pdob']);
    }
    var docurl = '../controller.php?document&view' + "&patient_id=" + pid + "&document_id=" + doc_id + "&";
    parent.left_nav.loadFrame('RTop', 'RTop', docurl);
    //top.activateTabByName('enc', true);
}

// used to display the patient demographic and encounter screens
async function handleGoToMessage(id, pid, pubpid = '', pname = '', dobstr = '') {
    const pData = await handleSetPatient(pid);
    if(pData !== false && pData['data']) {
        handleSetPatientData(pid, pData['data']['pubpid'], pData['data']['pname'], pData['data']['pdob']);
    }

    parent.left_nav.loadFrame('RTop', 'RTop', top.webroot_url + "/interface/main/messages/messages.php?task=edit&noteid="+id);
}

async function handleGoToOrder(id, pid, pubpid = '', pname = '', dobstr = '') {
    const pData = await handleSetPatient(pid);
    if(pData !== false && pData['data']) {
        handleSetPatientData(pid, pData['data']['pubpid'], pData['data']['pname'], pData['data']['pdob']);
    }

    parent.left_nav.loadFrame('RTop', 'RTop', top.webroot_url + "/interface/forms/rto1/new.php?pop=db&id="+id);
    //top.RTop.location = top.webroot_url + "/interface/forms/rto1/new.php?pop=db&id="+id;
}

function handleGoToEncounter(pid, pubpid = '', pname = '', enc = '', dobstr = '') {
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

/*------------------- (Message Board) ----------------*/

/*Fetch eligibility verification data by calling service*/
async function fetchLinkCount(direction, msg_from, msg_to, id, action) {
    const result = await $.ajax({
        type: "POST",
        url: top.webroot_url + "/library/OemrAD/interface/main/messages/fetch_message_count.php",
        datatype: "json",
        data: {
            direction: direction,
            msg_from: msg_from,
            msg_to: msg_to,
            id: id,
            action: action
        }
    });

    if(result) {
        return JSON.parse(result);
    }

    return false;
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
                alert("Selected group doesn't have a valid member.")
                // await confirmBoxModal({
                //     type: 'alert',
                //     title: "Alert",
                //     html: "Selected group doesn't have a valid member."
                // });
            }
        }
    }
}

/*------------------- End ----------------*/

/* add_edit_calender */



/* End */

/* CaseLib */

async function checkRecentInactive(pid = '', case_id = '') {
    var bodyObj = { pid : pid, case_id : case_id };
    const result = await $.ajax({
        type: "GET",
        url: top.webroot_url + "/interface/forms/cases/ajax/check_recent_case.php",
        datatype: "json",
        data: bodyObj
    });

    if(result != '') {
        var resultObj = JSON.parse(result);
        if(resultObj && resultObj['status'] == true) {
            return true;
        }
    }

    return false;
}

async function activateCase(pid = '', case_id = '') {
    var bodyObj = { pid : pid, case_id : case_id };
    const result = await $.ajax({
        type: "GET",
        url: top.webroot_url + "/interface/forms/cases/ajax/activate_case.php",
        datatype: "json",
        data: bodyObj
    });

    if(result != '') {
        var resultObj = JSON.parse(result);
        if(resultObj && resultObj['status'] == true) {
            return true;
        }
    }

    return false;
}

async function caseCount(pid = '') {
    var bodyObj = { pid : pid };
    var cCount = 0;

    const result = await $.ajax({
        type: "GET",
        url: top.webroot_url + "/interface/forms/cases/ajax/get_case_count.php",
        datatype: "json",
        data: bodyObj
    });

    if(result != '') {
        var resultObj = JSON.parse(result);
        if(resultObj && resultObj['status'] == true) {
            cCount = resultObj['count'];
        }
    }

    return cCount;
}

/* End */ 

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

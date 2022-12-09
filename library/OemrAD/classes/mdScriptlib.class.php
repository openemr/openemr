<?php

namespace OpenEMR\OemrAd;

class Scriptlib {
	public static function calender_script() {
		global $eid;

		$selectTitle = xlt('Select');
		$caseListTitle = xlt('Case List');
		$closeTitle = xla('Close');

		return <<<EOF
			<script type="text/javascript">
				function validateHhMm(value) {
				    var isValid = /^([0-0]?[0-9]|1[0-2]):([0-5][0-9])(:[0-5][0-9])?$/.test(value);
				    return isValid;
				}

				// This invokes popup to send zoom details.
				function sel_communication_type(eid, pid) {
				    var url = '{$GLOBALS['webroot']}/library/OemrAD/interface/main/calendar/zoom_popup.php?eid="; ?>'+ eid + "&pid=" + pid;
				    let title = '{$selectTitle}';
				    dlgopen(url, 'selectCommunicationType', 400, 200, '', title);
				}

				async function setCommunicationType(obj) {
				    if(obj && obj.eid) {
				        var data = {};

				        data['eid'] = obj.eid;
				        data['selectedType'] = [];
				        
				        if(obj.email && obj.email == 1) {
				            data['selectedType'].push('email');
				        }

				        if(obj.sms && obj.sms == 1) {
				            data['selectedType'].push('sms');
				        }

				        sendJoinUrlDetails(data);
				    }
				}

				async function sendJoinUrlDetails(data) {
				    $('#form_send_join_url').attr("disabled", true);
				    $('#form_send_join_url').val("Send Meeting Details...");

				    const result = await $.ajax({
				        type: "POST",
				        url: "{$GLOBALS['webroot']}/library/OemrAD/interface/main/calendar/ajax/send_zoom_details.php",
				        datatype: "json",
				        data: data
				    });

				    if(result) {
				        resultObj = JSON.parse(result);

				        if(resultObj['message']) {
				            alert(resultObj['message']);
				        }
				    }

				    $('#form_send_join_url').val("Send Meeting Details");
				    $('#form_send_join_url').attr("disabled", false);
				}

				async function sendJoinUrlEvent() {
				    $('#form_action').val("send_join_url");
				    var f = document.forms[0];
				    var eid = '{$eid}';
				    var pid = f.form_pid.value;

				    if(pid != "" && eid != "") {
				        sel_communication_type(eid, pid);
				    }
				}

				async function recreateZoomMeeting() {
				    var data = {
				        'appt_id' : '{$eid}',
				        'appt_provider': $('select[name="form_provider"]').val(),
				        'appt_date': $('input[name="form_date"]').val(),
				        'appt_hour': $('input[name="form_hour"]').val(),
				        'appt_minute': $('input[name="form_minute"]').val(),
				        'appt_ampm': $('select[name="form_ampm"]').val(),
				        'appt_duration': $('input[name="form_duration"]').val(),
				        'appt_facility': $('select[name="facility"]').val(),
				        'appt_category': $('select[name="form_category"]').val(),
				    }

				    const result = await $.ajax({
				        type: "POST",
				        url: "{$GLOBALS['webroot']}/library/OemrAD/interface/main/calendar/ajax/ajax_check_zoom_meeting.php?mode=recreate",
				        datatype: "json",
				        data: data
				    });

				    if(result) {
				        resultObj = JSON.parse(result);

				        if(resultObj['message']) {
				            alert(resultObj['message']);
				        }

				        if(resultObj['status'] === true) {
				            location.reload();
				        }
				    }
				}

				async function deleteZoomMeeting() {
				    var data = {
				        'appt_id' : '{$eid}',
				        'appt_provider': $('select[name="form_provider"]').val(),
				        'appt_date': $('input[name="form_date"]').val(),
				        'appt_hour': $('input[name="form_hour"]').val(),
				        'appt_minute': $('input[name="form_minute"]').val(),
				        'appt_ampm': $('select[name="form_ampm"]').val(),
				        'appt_duration': $('input[name="form_duration"]').val(),
				        'appt_facility': $('select[name="facility"]').val(),
				        'appt_category': $('select[name="form_category"]').val(),
				    }

				    const result = await $.ajax({
				        type: "POST",
				        url: "{$GLOBALS['webroot']}/library/OemrAD/interface/main/calendar/ajax/ajax_check_zoom_meeting.php?mode=delete",
				        datatype: "json",
				        data: data
				    });

				    if(result) {
				        resultObj = JSON.parse(result);

				        if(resultObj['message']) {
				            alert(resultObj['message']);
				        }

				        if(resultObj['status'] === true) {
				            location.reload();
				        }
				    }
				}

				async function isMeetingExists() {
				    var data = {
				        'appt_id' : '{$eid}',
				        'appt_provider': $('select[name="form_provider"]').val(),
				        'appt_date': $('input[name="form_date"]').val(),
				        'appt_hour': $('input[name="form_hour"]').val(),
				        'appt_minute': $('input[name="form_minute"]').val(),
				        'appt_ampm': $('select[name="form_ampm"]').val(),
				        'appt_duration': $('input[name="form_duration"]').val(),
				        'appt_facility': $('select[name="facility"]').val(),
				        'appt_category': $('select[name="form_category"]').val(),
				    }

				    const result = await $.ajax({
				        type: "POST",
				        url: "{$GLOBALS['webroot']}/library/OemrAD/interface/main/calendar/ajax/ajax_check_zoom_meeting.php?mode=check",
				        datatype: "json",
				        data: data
				    });

				    if(result) {
				        resultObj = JSON.parse(result);

				        if(resultObj['message']) {
				            alert(resultObj['message']);
				            $('#form_save').attr('disabled', false);
				            return true;
				        }
				    }

				    return false;
				}

				async function todaysEncounterIf() {
				    var f = document.forms[0];
				    var responce = await $.ajax({
				        url: '{$GLOBALS['webroot']}/library/OemrAD/interface/main/calendar/ajax/add_edit_event_ajax.php?type=check&eid={$eid}',
				        type: 'POST',
				        data: $(f).serialize()
				    });

				    var resObj = JSON.parse(responce);
				    if(resObj.encounter == true) {
				        if (confirm("An encounter already exists for this patient for this day, do you still want to create a new encounter?")) {
				            $('#form_todaysEncounterIf').val("1");
				        } else {
				            $('#form_todaysEncounterIf').val("0");
				        }
				    }

				    if(resObj.encounter_group == true) {
				        if (confirm("An encounter group already exists for this patient for this day, do you still want to create a new group encounter?")) {
				            $('#form_todaysTherapyGroupEncounterIf').val("1");
				        } else {
				            $('#form_todaysTherapyGroupEncounterIf').val("0");
				        }
				    }
				}

				//Get Total Cancelled PatientAppt
				async function getTotalCancelledPatientAppt(pid = '', caseId = '', totalCount = 1, futureAppt = 1, rehabProgress = 1) {
				    if(pid && pid != '') {
				        var responce = await $.ajax({
				            url: '{$GLOBALS['webroot']}/library/OemrAD/interface/main/calendar/ajax/total_cancelled_patient_appt.php?pid=' + pid + '&caseId=' + caseId + '&totalCount=' + totalCount + '&futureAppt=' + futureAppt + '&rehabProgress=' + rehabProgress,
				            type: 'GET'
				        });

				        var responceJSON = JSON.parse(responce);
				        var labelContent = "";

				        if(totalCount === 1) {
				            if(responceJSON && responceJSON['total_count'] && responceJSON['total_count'] > 0) {
				                labelContent = responceJSON['total_count'] + " Cancellation Violations in past 6 months";
				            }

				            document.getElementById('cancellation_info').innerHTML = labelContent;
				        }

				        if(futureAppt === 1) {
				            $('#future_appt_info').hide();

				            if(responceJSON && responceJSON['future_appt_list']) {
				                var fapptList = [];
				                var fapptFullList = [];
				                var futurApptFullHtml = "";
				                var futurApptHtml = "";

				                responceJSON['future_appt_list'].forEach((item, i) => {
				                    if(i < 2) {
				                        fapptList.push("<span>"+item+"</span>");
				                    }

				                    fapptFullList.push("<li><span>"+item+"</span></li>");
				                });

				                if(fapptFullList.length > 0) {
				                    futurApptFullHtml = "<ul class='futureApptUlList'>" + fapptFullList.join("") + "</ul>";
				                }

				                if(fapptList.length > 0) {
				                    futurApptHtml = fapptList.join("<br/>");
				                    document.querySelector('#future_appt_info .future_appt_info_inner .content').innerHTML = futurApptHtml;
				                    $('#future_appt_info').show();
				                }

				                if(responceJSON['future_appt_list'].length > 2) {
				                    document.querySelector('#future_appt_info .future_appt_info_inner .more_content .hidden_content').innerHTML = futurApptFullHtml;
				                    $('#future_appt_info .future_appt_info_inner .more_content').show();
				                    //fapptList.push("<span><div data-toggle='tooltip' title='fff'>...</div></span>");
				                }
				            }
				        }

				        if(rehabProgress == '1' && caseId != '') {
				            $('#rehab_progress_info').hide();

				            if(responceJSON && responceJSON['rehab_progress'] && responceJSON['rehab_progress'] != "") {
				                document.querySelector('#rehab_progress_info .content').innerHTML = "<b>Rehab Progress: </b>" + responceJSON['rehab_progress'];
				                $('#rehab_progress_info').show();
				            }
				        }
				    }
				}

				async function authorizedCase(case_id = '', start_date = '', pid = '', provider = '') {
				    let appt_case_id = case_id;
				    let appt_start_date = start_date;
				    let appt_pid = pid;
				    let appt_provider = provider;

				    if(appt_case_id == '') {
				        appt_case_id = document.getElementById('form_case').value;
				    }

				    if(appt_start_date == '') {
				        appt_start_date = document.getElementById('form_date').value;
				    }

				    if(appt_pid == '') {
				        appt_pid = document.querySelector('input[name="form_pid"]').value;
				    }

				    if(appt_pid == '') {
				        appt_pid = document.querySelector('input[name="form_pid"]').value;
				    }

				    if(appt_provider == '') {
				        appt_provider = document.querySelector('select[name="form_provider"]').value;
				    }

				    if(appt_case_id != '') {
				        var responce = await $.ajax({
				            type: "POST",
				            url: "{$GLOBALS['webroot']}/library/OemrAD/interface/main/calendar/ajax/authorized_case.php",
				            datatype: "json",
				            data: { "type" : "appt", "case_id" : appt_case_id, "start_date" : appt_start_date, "pid" : appt_pid, "provider" : appt_provider }
				        });

				        var responceJSON = JSON.parse(responce);

				        if(responceJSON['status'] === false) {
				            alert(responceJSON['message'].join('\\n\\n'));
				        }
				    }
				}

				function setCase(case_id, case_dt, desc) {
				  var decodedDesc = '';
				  if(desc) decodedDesc = window.atob(desc);
				  var desc = case_dt + '   [' + decodedDesc + ']';
					var target = document.getElementById('case_desc');
					while(target.firstChild) {
						target.removeChild( target.firstChild );
					}
					target.appendChild( document.createTextNode(desc));
					document.getElementById('form_case').value = case_id;

				    //Call Authorized Case
				    //authorizedCase(case_id);
				    
				    var pid = document.forms[0].form_pid.value;
				    getTotalCancelledPatientAppt(pid, case_id, 0, 0, 1);
				}

				function sel_case() {
					var pid = document.forms[0].form_pid.value;
					if(!pid) {
						alert('You must select a patient first');
						return false;
					}
				  var href = "../../forms/cases/case_list.php?mode=choose&popup=pop&pid=" + pid;
				  dlgopen(href, 'findCase', 'modal-xl', 'modal-xl', '', '{$caseListTitle}');
				}

				function openAlertInfoPopup(message) {
				      top.restoreSession()
				      dlgopen('{$GLOBALS['webroot']}/library/OemrAD/interface/main/calendar/alert_popup.php?message='+encodeURIComponent(message), 'alert_info_popup_add_edit_event', 500, 250, '', '', {
				          buttons: [
				              {text: '{$closeTitle}', close: true, style: 'default btn-sm'}
				          ],
				          allowResize: true,
				          allowDrag: true,
				          dialogId: '',
				          type: 'iframe'
				      });
				}

			</script>
EOF;
	}
}
<?php

/*Get email verification*/
function getEmailVerificationResults($user_id, $field_name) {
	if($user_id) {
		$sql = sqlStatement("SELECT * FROM email_verifications WHERE user_id = $user_id AND field_name = '$field_name' ");
		$records = sqlFetchArray($sql);
		if($records) {
			return $records;
		}
	}
	return false;
}

/*Update or Save email verification*/
function updateEmailVerification($user_id, $field_name, $data) {
	$form_email_direct = attr($data['form_email_direct']);
	$hidden_verification_status = attr($data['hidden_verification_status']);

	if($field_name && $form_email_direct != "") {
		$isRecordExists = getEmailVerificationResults($user_id, $field_name);

		if($isRecordExists) {
			sqlQuery("UPDATE email_verifications SET field_value = ?, verification_status = ? WHERE user_id = ? AND field_name = ?", array($form_email_direct, $hidden_verification_status, $user_id, $field_name));
		} else {
			$query = "INSERT INTO email_verifications ( user_id, field_name, field_value, verification_status) VALUES ( ?,?,?,? )";
	        sqlStatement($query, array($user_id, $field_name, $form_email_direct, $hidden_verification_status));
		}
	}
}

function emailVerificationContent($path, $result) {
	$id = attr(isset($result['id']) ? $result['id'] : "");
	$pid = attr(isset($result['pid']) ? $result['pid'] : "");
	$vfield_value = '';
	$vStatus = 0;
	$vStatusFlag = 0;

	$correctSVG = '<svg height="19pt" viewBox="0 0 512 512" width="19pt" xmlns="http://www.w3.org/2000/svg"><path d="m256 0c-141.164062 0-256 114.835938-256 256s114.835938 256 256 256 256-114.835938 256-256-114.835938-256-256-256zm0 0" fill="#4caf50"/><path d="m385.75 201.75-138.667969 138.664062c-4.160156 4.160157-9.621093 6.253907-15.082031 6.253907s-10.921875-2.09375-15.082031-6.253907l-69.332031-69.332031c-8.34375-8.339843-8.34375-21.824219 0-30.164062 8.339843-8.34375 21.820312-8.34375 30.164062 0l54.25 54.25 123.585938-123.582031c8.339843-8.34375 21.820312-8.34375 30.164062 0 8.339844 8.339843 8.339844 21.820312 0 30.164062zm0 0" fill="#fafafa"/></svg>';

	$invalidSVG = '<svg height="19pt" viewBox="0 0 512 512" width="19pt" xmlns="http://www.w3.org/2000/svg"><path d="m256 0c-141.164062 0-256 114.835938-256 256s114.835938 256 256 256 256-114.835938 256-256-114.835938-256-256-256zm0 0" fill="#f44336"/><path d="m350.273438 320.105469c8.339843 8.34375 8.339843 21.824219 0 30.167969-4.160157 4.160156-9.621094 6.25-15.085938 6.25-5.460938 0-10.921875-2.089844-15.082031-6.25l-64.105469-64.109376-64.105469 64.109376c-4.160156 4.160156-9.621093 6.25-15.082031 6.25-5.464844 0-10.925781-2.089844-15.085938-6.25-8.339843-8.34375-8.339843-21.824219 0-30.167969l64.109376-64.105469-64.109376-64.105469c-8.339843-8.34375-8.339843-21.824219 0-30.167969 8.34375-8.339843 21.824219-8.339843 30.167969 0l64.105469 64.109376 64.105469-64.109376c8.34375-8.339843 21.824219-8.339843 30.167969 0 8.339843 8.34375 8.339843 21.824219 0 30.167969l-64.109376 64.105469zm0 0" fill="#fafafa"/></svg>';

	if($pid) {
		$form_email_direct = attr($result['email_direct']);
		$records = getEmailVerificationResults($pid, "email_direct");

		if($records != false && is_array($records)) {
			$vfield_value = attr($records['field_value']);
			$vStatus = attr($records['verification_status']);

			if($vfield_value == $form_email_direct && $vStatus == "1") {
				$vStatusFlag = 1;
			}
		}
	}


	if($vStatusFlag == 1) {
		$verification_status = $correctSVG;
	} else if($vStatusFlag == 0){
		$verification_status = $invalidSVG;
	}

	return <<<EOF
	<style type="text/css">
		.tab.current#tab_Contact {
			width: 900px!important;
		}

		.verification_content {
			display: inline-block!important;
			vertical-align: bottom;
			margin-bottom: 4px;
		}

		.verification_content .verification_status_text {
			display: inline-block!important;
		}

		.verification_content .btn_container {
			display: inline-block!important;
			margin-left: 6px;
		}

	</style>
	<script type="text/javascript">
		var initVEmail = '{$vfield_value}';
		var initVStatus = '{$vStatus}';

		/*Set Button Status*/
		function setLoadingValue(status) {
			if(status == true) {
				$('#DEM #btn_verify_email').attr("disabled", "disabled").html('Verifying...');
			} else if(status == false) {
				$('#DEM #btn_verify_email').removeAttr("disabled", "disabled").html('Verify Email');
			}
		}

		/*Set Status Value*/
		function setStatusValue(status) {
			if(status == true) {
				$('#DEM #verification_status_text').html('{$correctSVG}');
				$('#DEM #hidden_verification_status').val("1");
			} else if(status == false) {
				$('#DEM #verification_status_text').html('{$invalidSVG}');
				$('#DEM #hidden_verification_status').val("0");
			}
		}

		/*Email Verification Service*/
		async function callEmailVerificationService(val) {
		    let result;
		    let ajaxurl = '{$path}email_verification/ajax_email_verification.php?email='+val;

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

		async function handleVerification(val) {
			setLoadingValue(true);
			var reponceData = await callEmailVerificationService(val);
			setLoadingValue(false);

			if(reponceData != null) {
				var reponce = JSON.parse(reponceData);
				if(reponce.success == "true") {
					if(reponce.result == "valid" && reponce.disposable == "false" && reponce.accept_all == "false") {
						setStatusValue(true);
					} else {
						setStatusValue(false);
					}
				} else if(reponce.success == "false"){
					alert(reponce.message);
				}
			}
		}

		function handleVerificationContainer(val) {
			if(val.trim() == "") {
				$("#DEM #value_id_text_email_direct .verification_content").attr('style', 'display: none !important');
				$('#DEM #hidden_verification_status').addClass('disabledItem');
			} else {
				$("#DEM #value_id_text_email_direct .verification_content").attr('style', 'display: inline-block !important');
				$('#DEM #hidden_verification_status').removeClass('disabledItem');
			}
		}

		$(document).ready(function(){

			/*Email Validation content*/
			$("#DEM #value_id_text_email_direct, #DEM #text_email_direct").append('<div class="verification_content"><input type="hidden" name="hidden_verification_status" value="{$vStatusFlag}" id="hidden_verification_status" /><div class="verification_status_text" id="verification_status_text">{$verification_status}</div><div class="btn_container"><button type="button" id="btn_verify_email">Verify Email</button></div></div>');

			handleVerificationContainer($('#DEM #form_email_direct').val());

			$('#DEM #form_email_direct').keyup(function() {
				handleVerificationContainer($(this).val());
			});

			/*On change check email validation*/
			$('#DEM').on('input', '#form_email_direct', function() {
				var inputVal = $(this).val();
				if(inputVal == initVEmail && initVStatus == '1') {
					setStatusValue(true);
				} else {
					setStatusValue(false);
				}
			});

			$('#DEM').on('click', '#btn_verify_email', function() {
				var isDisable = $(this).is(':disabled');
				var inputVal = $('#form_email_direct').val(); 

				if(isDisable == false) {
					handleVerification(inputVal);
				}
			});
		});
	</script>
EOF;
}
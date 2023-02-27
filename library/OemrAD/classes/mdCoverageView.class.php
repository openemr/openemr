<?php

namespace OpenEMR\OemrAd;

include_once(__DIR__ . "/mdCoverageCheck.class.php");

use OpenEMR\OemrAd\CoverageCheck;


class CoverageView {

	/*Constructor*/
	public function __construct() {
	}

	/*Eligibility Javascript function to perform operations*/
	public static function getEligibilityScript($pid, $dt) {
		//global $correctSVG, $invalidSVG;

		?>
		<style type="text/css">
			/*.eligibilityElementContainer {
				display: grid;
			    grid-template-columns: 1fr auto auto;
			    align-items: center;
			    grid-column-gap: 10px;
			    max-width: 530px;
			}*/

			/*.eligibilityElementContainer .payerIdContainer {
				min-width: 90px;
			}*/

			/*.eligibilityElementContainer .statusContainer {
				display: inline-grid;
			    grid-template-columns: auto 1fr;
			    grid-gap: 5px;
			    align-items: center;
			}
			.handleHistoryBtn {
				max-width: 160px;
			}*/
		</style>
		<script type="text/javascript">
			// var pid = '<?php //echo $pid; ?>';

			// function isJson(str) {
			//     try {
			//         JSON.parse(str);
			//     } catch (e) {
			//         return false;
			//     }

			//     return true;
			// }

			// function getJson(str) {
			//     try {
			//         return JSON.parse(str);
			//     } catch (e) {
			//         return str;
			//     }
			// }

			// /*Fetch eligibility verification data by calling service*/
			// async function fetchEligibilityVerification(cnt, pid, case_id, ins_id, provider_id) {
			// 	const result = await $.ajax({
			// 		type: "POST",
			// 		url: "<?php //echo $GLOBALS['webroot'].'/library/OemrAD/interface/forms/cases/ajax/coverage_verification.php'; ?>",
			// 		datatype: "json",
			// 		data: {
			// 			case_id: case_id,
			// 			cnt: cnt,
			// 			ins_id: ins_id,
			// 			provider_id: provider_id,
			// 			pid: pid
			// 		}
			// 	});
			// 	return result;
			// }

			// /*Set Button Status*/
			// function setLoadingValue(ele, status) {
			// 	if(status == true) {
			// 		ele.disabled = true;
			// 		ele.innerHTML = 'Eligibility Verifying...';
			// 	} else if(status == false) {
			// 		ele.disabled = false;
			// 		ele.innerHTML = 'Eligibility Verification';
			// 	}
			// }

			// function setFieldsValue(cnt, responceData, update_val = "1") {
			// 	$('#ins_raw_data'+cnt).html(responceData ? JSON.stringify(responceData) : '');

			// 	if(responceData.eligibility_status == "eligible") {
			// 		$('#statusText'+cnt).html('<?php //echo CoverageCheck::getValidIcon(); ?>');
			// 	} else {
			// 		$('#statusText'+cnt).html('<?php //echo CoverageCheck::getInValidIcon(); ?>');
			// 	}

			// 	var statusMsg = responceData.statusMsg ? responceData.statusMsg : false;
			// 	if(statusMsg != false) {
			// 		alert(statusMsg);
			// 	}
			// }

			// /*Handle eligibility verification */
			// async function handleEligibilityVerification(ele, cnt, pid, case_id, ins_id, provider_id) {
				
			// 	if(ele.disabled == false) {
			// 		var provider_value = $("#provider_id").val();

			// 		setLoadingValue(ele, true);

			// 		/*Fetch eligibility verification*/
			// 		var responce = await fetchEligibilityVerification(cnt, pid, case_id, ins_id, provider_value);

			// 		/*Log Info*/
			// 		//console.info(getJson(responce));
					
			// 		if(isJson(responce)) {
			// 			var responceData = getJson(responce);

			// 			if(responceData.action == 1) {
			// 				setFieldsValue(cnt, responceData);
			// 			} else {
			// 				await handleEligibilityContent(cnt, pid, case_id, ins_id, provider_id);
			// 			}
						
			// 			/*Process responce Data*/
			// 			if(responceData.success == 0) {
			// 				alert(responceData.error);
			// 			} else {
			// 				if(responceData.userMessage) {
			// 					alert(responceData.userMessage);
			// 				}
			// 			}
			// 		} else {
			// 			alert('Something wrong');
			// 		}

			// 		setLoadingValue(ele, false);
			// 	}
			// }

			// function handleHistory(pid, case_id, cnt) {
			// 	var url = '<?php //echo $GLOBALS['webroot'] ?>'+'/library/OemrAD/interface/forms/cases/ajax/coverage_history.php?pid='+pid+'&case_id='+case_id+'&cnt='+cnt;
			// 	dlgopen(url,'_blank', 1200, 500);
			// }

			// /*Fetch eligibility content by calling service based on rule*/
			// async function fetchEligibilityContent(cnt, pid, case_id, ins_id, provider_id) {
			// 	const result = await $.ajax({
			// 		type: "POST",
			// 		url: "<?php //echo $GLOBALS['webroot'].'/library/OemrAD/interface/forms/cases/ajax/coverage_view.php'; ?>",
			// 		datatype: "json",
			// 		data: {
			// 			case_id: case_id,
			// 			cnt: cnt,
			// 			ins_id: ins_id,
			// 			provider_id: provider_id,
			// 			pid: pid
			// 		}
			// 	});
			// 	return result;
			// }

			// /*Handle content on change of incurence company value*/
			// async function handleEligibilityContent(cnt = '', pid ='', case_id = '', ins_id = '', provider_id) {
			// 	var responce = '';
			// 	if(pid != '' && ins_id != '') {
			// 		responce = await fetchEligibilityContent(cnt, pid, case_id, ins_id, provider_id);
			// 	}
			// 	$('#verification_contaner_'+cnt).html(responce);
			// }


			// $(document).ready(function(){
			// 	$( ".ins-dropdown" ).change(function() {
			// 		var cnt = $(this).data( "id" );
			// 		var ins_id = $(this).val();
			// 		var provider_id = $("#provider_id").val();
			// 		var case_id = $("#case_id").val();

			// 		handleEligibilityContent(cnt, pid, case_id, ins_id, provider_id);
			// 	});
			// });
		</script>
		<?php
	}

	/*Get content*/
	public static function getEligibilityContent($pid, $dt, $cnt) {

		/*Get Provider Id*/
		$providerId = CoverageCheck::getProviderId($dt['provider_id'], attr($dt['ins_data_id'.$cnt]), $pid);

		/*Get InsuranceData based on insurence copmany details*/
		$returnData = CoverageCheck::getInsuranceDataById(attr($dt['pid']), attr($dt['ins_data_id'.$cnt]), $providerId);
		?>
		<div id="<?php echo 'verification_contaner_'.$cnt; ?>">
			<?php
			if($returnData && is_array($returnData) && count($returnData) > 0) {
				if(!empty($returnData[0]['policy_number'])) {
					
					/*Get Html content on page render*/
					echo CoverageCheck::getHtmlContent($pid, attr($dt['case_id']), $cnt, attr($dt['ins_data_id'.$cnt]), $providerId, $returnData[0]);
				}
			}
			?>
		</div>
		<?php
	}
}
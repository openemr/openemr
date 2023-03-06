<?php

namespace OpenEMR\OemrAd;

use OpenEMR\Common\Acl\AclMain;

class Caselib {
	
	function __construct(){
	}

	public static function manageInsData($pid) {
		global $dt, $field_prefix;

		$ids = array();
		for ($i=1; $i <= 3 ; $i++) { 
			$ids[] = $dt['ins_data_id'.$i];
		}

		return self::checkIsExists($ids, $pid);
	}

	public static function checkIsExists($ids, $pid) {
		$types = array('20', '21', '16');

		$status = false;
		foreach ($ids as $i => $id) {
			if(isset($id) && $id != 0) {
				$ins_data = self::getInsuranceCompaniesData($id, $pid);

				if(isset($ins_data) && !empty($ins_data)) {
					if(in_array($ins_data['ins_type_code'], $types)) {
						$status = true;
					}
				}
			}
		}

		return $status;
	}

	public static function isInsLiableForPiCase($pid) {
		global $dt, $field_prefix;

		$ids = array();
		for ($i=1; $i <= 3 ; $i++) { 
			$ids[] = $dt['ins_data_id'.$i];
		}

		return self::checkIsLiableForPiCase($ids, $pid);
	}

	public static function checkIsLiableForPiCase($ids, $pid) {
		$types = array('20', '21', '16');

		$status = false;
		foreach ($ids as $i => $id) {
			if(isset($id) && $id != 0) {
				$ins_data = self::getInsuranceCompaniesData($id, $pid);

				if(isset($ins_data) && !empty($ins_data)) {
					if(in_array($ins_data['ins_type_code'], $types)) {
						$status = true;
					}
				}
			}
		}

		return $status;
	} 

	public static function isLiablePiCaseByCase($case_id, $pid, $caseData = array()) {
		$insIdList = array();

		if(empty($caseData)) {
			$caseData = self::getCaseData($case_id);
		}

		for ($ins_i=1; $ins_i <= 3; $ins_i++) { 
			if(isset($caseData['ins_data_id'.$ins_i]) && $caseData['ins_data_id'.$ins_i] != "") {
				$insIdList[] = $caseData['ins_data_id'.$ins_i];
			}
		}

		return self::checkIsLiableForPiCase($insIdList, $pid);
	}

	public static function getLiableInsData($ids, $pid) {
		$types = array('20', '21', '16');
		$insList = array();

		foreach ($ids as $i => $id) {
			if(isset($id) && $id != 0) {
				$ins_data = self::getInsuranceCompaniesData($id, $pid);

				if(isset($ins_data) && !empty($ins_data)) {
					if(in_array($ins_data['ins_type_code'], $types)) {
						$insList[] = $ins_data;
					}
				}
			}
		}

		return $insList;
	}

	public static function getFutureAppt($case_id, $pid, $date = '') {
		$resultItems = array();

		if(empty($case_id) || empty($pid)) {
			return $resultItems;
		}

		$whereSql = "ope.pc_pid = '$pid' and ope.pc_case = '$case_id' and ope.pc_apptstatus not in ('?','x','%') ";
		//$bind = array($pid, $case_id);

		if(!empty($date)) {
			$whereSql .= "and TIMESTAMP(ope.pc_eventDate, ope.pc_startTime) > '".$date."' ";
			//$bind = array($date);
		} else {
			$whereSql .= "and TIMESTAMP(ope.pc_eventDate, ope.pc_startTime) > now() ";
		}

		$sql = "SELECT ope.*, opc.pc_catname, u.fname as provider_fname, u.mname as provider_mname, u.lname as provider_lname, TIMESTAMP(pc_eventDate, pc_startTime) as event_date_time from openemr_postcalendar_events ope left join users u on u.id is not null and u.id = ope.pc_aid left join openemr_postcalendar_categories opc on opc.pc_catid = ope.pc_catid where ".$whereSql." order by event_date_time asc ;";
		$result = sqlStatementNoLog($sql);

		while ($result_data = sqlFetchArray($result)) {
			$result_data['provider_name'] = ucfirst(substr($result_data['provider_fname'], 0, 1)) . $result_data['provider_lname'];
			$result_data['event_date1'] = isset($result_data['event_date_time']) ? date('m/d/Y',strtotime($result_data['event_date_time'])) : "";
			$result_data['event_time1'] = isset($result_data['event_date_time']) ? date('h:iA',strtotime($result_data['event_date_time'])) : "";

			$resultItems[] = $result_data;
		}

		return $resultItems;
	}


	public static function ListLook($thisData, $thisList) {
		if($thisList == 'occurrence') {
			if(!$thisData || $thisData == '') return 'Unknown or N/A'; 
		}
		if(!$thisData || $thisData == '') return ''; 
		$fres=sqlStatement("SELECT * FROM list_options WHERE list_id='".$thisList."' AND option_id='".$thisData."'");
		if($fres) {
			$rret=sqlFetchArray($fres);
			$dispValue= $rret['title'];
			if($thisList == 'occurrence' && $dispValue == '') {
				$dispValue = 'Unknown or N/A';
    		}
		}
  		else {
   			$dispValue= '*Not Found*';
		}
  		return $dispValue;
	}

	public static function getPreviousCanceledAppt($case_id, $pid, $date = '') {
		$resultItems = array();

		if(empty($case_id) || empty($pid)) {
			return $resultItems;
		}

		$whereSql = "ope.pc_pid = '$pid' and ope.pc_case = '$case_id' and ope.pc_apptstatus in ('?','%', 'x') ";
		//$bind = array($pid, $case_id);

		if(!empty($date)) {
			$whereSql .= "and TIMESTAMP(ope.pc_eventDate, ope.pc_startTime) < '".$date."' ";
			//$bind = array($date);
		} else {
			$whereSql .= "and TIMESTAMP(ope.pc_eventDate, ope.pc_startTime) < now() ";
		}

		$sql = "SELECT ope.*, opc.pc_catname, u.fname as provider_fname, u.mname as provider_mname, u.lname as provider_lname, TIMESTAMP(pc_eventDate, pc_startTime) as event_date_time, ope.pc_apptstatus from openemr_postcalendar_events ope left join users u on u.id is not null and u.id = ope.pc_aid left join openemr_postcalendar_categories opc on opc.pc_catid = ope.pc_catid where ".$whereSql." order by event_date_time asc ;";
		$result = sqlStatementNoLog($sql);


		while ($result_data = sqlFetchArray($result)) {
			$result_data['provider_name'] = ucfirst(substr($result_data['provider_fname'], 0, 1)) . $result_data['provider_lname'];
			$result_data['event_date1'] = isset($result_data['event_date_time']) ? date('m/d/Y',strtotime($result_data['event_date_time'])) : "";
			$result_data['event_time1'] = isset($result_data['event_date_time']) ? date('h:iA',strtotime($result_data['event_date_time'])) : "";

			$resultItems[] = $result_data;
		}

		return $resultItems;
	}

	public static function getRehabPlanDataByCase($case_id, $caseMData = array(), $caseData = array()) {
		$caseManagerData = $caseMData;
		$returnData = array();

		if(empty($caseMData)) {
			$caseManagerData = self::piCaseManagerFormData($case_id, '');
		}

		$tmpRehabList = array();
		$rehabCatList = array(
			"PT" => "adjustment_with_therapy",
			"LD" => "spinal_decompression_(lumbar)",
			"CD" => "spinal_decompression_(cervical)",
			"DD" => "spinal_decompression_(lumbar_&_cervical)"
		);

		if(isset($caseManagerData['tmp_rehab_field_1']) && isset($caseManagerData['tmp_rehab_field_2'])) {
			$oldR1Field = $caseManagerData['tmp_rehab_field_1'];
			$oldR2Field = $caseManagerData['tmp_rehab_field_2'];

			for ($old_i=0; $old_i < count($oldR1Field); $old_i++) { 
				if(!isset($tmpRehabList[$oldR2Field[$old_i]])) {
					$tmpRehabList[$oldR2Field[$old_i]] = array();
				}
				$tmpRehabList[$oldR2Field[$old_i]][] = $oldR1Field[$old_i];
			}

			foreach ($tmpRehabList as $tmprk => $tmprItem) {
				if(isset($rehabCatList[$tmprk])) {
					$rehabApptCount = self::getRehabPlanByCase($case_id, $rehabCatList[$tmprk]);

					$rehabApptCount = isset($rehabApptCount['total_count']) ? $rehabApptCount['total_count'] : 0;
					$rehabValueSum = (isset($tmprItem) && is_array($tmprItem)) ? array_sum($tmprItem) : 0;

					$returnData[] = array('id' => $tmprk, 'appt_count' => $rehabApptCount, 'value_sum' => $rehabValueSum);
				}
			}
		}

		return $returnData;
	}

	public static function getRehabProgressLBFData($case_id = array()) {
		$dataSet = array();
		$case_id_str = "'".implode("','",$case_id)."'";

		if(!empty($case_id)) {
			$esql = sqlStatement("SELECT SUM(a1.pc) as pt, SUM(a1.ld) as ld, SUM(a1.cd) as cd, SUM(a1.dd) as dd, a1.encounter, a1.case_id FROM (SELECT MAX(a2.pc) as pc, MAX(a2.ld) as ld, MAX(a2.cd) as cd, MAX(a2.dd) as dd, a2.encounter, a2.case_id FROM (SELECT MAX(IF((lo.form_id in ('LBF_UErehab', 'LBF_elbow') or (lo.form_id = 'LBF_rehab' and lgp.grp_title in ('Lumbar Rehabilitation', 'Cervical Rehabilitation'))) and (ld.field_value is not null and ld.field_value !=''), '1', '0')) as pc, IF(MAX(IF((lo.form_id in ('LBF_rehab') and lgp.grp_title in ('Spinal Decompression') and lo.title in ('Lumbar SD Time', 'Lumbar SD Low Limit', 'Lumbar SD High Limit')) and (ld.field_value is not null and ld.field_value !=''), '1', '0')) > 0 and MAX(IF((lo.form_id in ('LBF_rehab') and lgp.grp_title in ('Spinal Decompression') and lo.title in ('Cervical SD Time', 'Cervical SD Low Limit', 'Cervical SD High Limit')) and (ld.field_value is not null and ld.field_value !=''), '1', '0')) <= 0, '1', '0') as ld, IF(MAX(IF((lo.form_id in ('LBF_rehab') and lgp.grp_title in ('Spinal Decompression') and lo.title in ('Cervical SD Time', 'Cervical SD Low Limit', 'Cervical SD High Limit')) and (ld.field_value is not null and ld.field_value !=''), '1', '0')) > 0 and MAX(IF((lo.form_id in ('LBF_rehab') and lgp.grp_title in ('Spinal Decompression') and lo.title in ('Lumbar SD Time', 'Lumbar SD Low Limit', 'Lumbar SD High Limit')) and (ld.field_value is not null and ld.field_value !=''), '1', '0')) <= 0, '1', '0') as cd, IF(MAX(IF((lo.form_id in ('LBF_rehab') and lgp.grp_title in ('Spinal Decompression') and lo.title in ('Cervical SD Time', 'Cervical SD Low Limit', 'Cervical SD High Limit')) and (ld.field_value is not null and ld.field_value !=''), '1', '0')) > 0 AND MAX(IF((lo.form_id in ('LBF_rehab') and lgp.grp_title in ('Spinal Decompression') and lo.title in ('Lumbar SD Time', 'Lumbar SD Low Limit', 'Lumbar SD High Limit')) and (ld.field_value is not null and ld.field_value !=''), '1', '0')) > 0, '1', '0') as dd, fe.encounter, cal.enc_case as case_id, lo.form_id as form_dir, lo.title as field_title, lgp.grp_title as grp_title from case_appointment_link cal,form_encounter fe, forms f, lbf_data ld, layout_options lo, layout_group_properties lgp WHERE fe.encounter = cal.encounter AND f.encounter = fe.encounter AND ld.form_id = f.form_id AND lo.field_id = ld.field_id AND lo.form_id = f.formdir AND lgp.grp_form_id = lo.form_id and lgp.grp_group_id = lo.group_id AND f.formdir IN ('LBF_rehab', 'LBF_UErehab', 'LBF_elbow') AND cal.enc_case IN (".$case_id_str.") AND f.deleted = 0 GROUP BY ld.form_id) AS a2 GROUP BY a2.encounter) AS a1 GROUP BY a1.case_id");

			while ($enrow = sqlFetchArray($esql)) {
				$dataSet['case_'.$enrow['case_id']] = $enrow;
			}
		}

		return $dataSet;
	}

	public static function getMedicalDataOfCase($case_id) {
		if($case_id != "") {
			$sql = "SELECT ope.*, u.fname as provider_fname, u.mname as provider_mname, u.lname as provider_lname, TIMESTAMP(pc_eventDate, pc_startTime) as event_date_time from openemr_postcalendar_events ope, users u where ope.pc_aid=u.id and ope.pc_apptstatus not in ('%', '?','x') and u.physician_type = 'general_physician' and ope.pc_case = $case_id"	;
			$result_data = sqlQuery($sql, array());

			if(!empty($result_data)) {
				$result_data['provider_name'] = ucfirst(substr($result_data['provider_fname'], 0, 1)) . $result_data['provider_lname'];
				$result_data['event_date1'] = isset($result_data['event_date_time']) ? date('m/d/Y',strtotime($result_data['event_date_time'])) : "";
				$result_data['event_time1'] = isset($result_data['event_date_time']) ? date('h:iA',strtotime($result_data['event_date_time'])) : "";
			}

			return $result_data;
		}

		return false;
	}

	function getXRayCountByCase($case_id) {
		if($case_id != "") {
			$sql = "SELECT count(cal.encounter) as total_count from case_appointment_link cal left join form_encounter fe on fe.encounter = cal.encounter left join billing b on b.encounter = fe.encounter where b.code_type = 'CPT4' and b.code like '7%' and cal.enc_case = $case_id"	;
			$frow = sqlQuery($sql, array());
			return $frow;
		}

		return false;
	}

	public static function getRehabPlanByCase($case_id, $cat_id) {
		if($case_id != "" && $cat_id != "") {
			$sql = "SELECT count(ope.pc_eid) as total_count from openemr_postcalendar_events ope left join openemr_postcalendar_categories opc on opc.pc_catid = ope.pc_catid where ope.pc_apptstatus not in ('-','+','?','x','%') and opc.pc_constant_id = '".$cat_id."' and ope.pc_case = $case_id"	;
			$frow = sqlQuery($sql, array());
			return $frow;
		}

		return false;
	}

	public static function getOrdesByCase($case_id) {
		$dataItems = array();

		if($case_id != "") {
			$result = sqlStatement("SELECT fr.*, lo.title as rto_action_title, lo1.title as rto_status_title from form_rto fr left join list_options lo on lo.option_id = fr.rto_action and lo.list_id = 'RTO_Action' left join list_options lo1 on lo1.option_id = fr.rto_status and lo1.list_id = 'RTO_Status' where fr.rto_case = ? order by fr.`date` asc", array($case_id));
			while ($row = sqlFetchArray($result)) {
				$dataItems[] = $row;
			}
		}

		return $dataItems;
	}

	function getTensCountByCase($case_id) {
		if($case_id != "") {
			$sql = "SELECT count(cal.encounter) as total_count from case_appointment_link cal left join form_encounter fe on fe.encounter = cal.encounter left join billing b on b.encounter = fe.encounter where b.code_type = 'HCPCS' and b.code = 'E0720' and cal.enc_case = $case_id"	;
			$frow = sqlQuery($sql, array());
			return $frow;
		}

		return false;
	}

	public static function getInsuranceCompaniesData($id, $pid) {
		$query = "SELECT ic.* FROM insurance_data AS ins LEFT JOIN insurance_companies AS ic ON ins.`provider` = ic.`id` WHERE ins.`id` = ? AND ins.`pid` = ? ";

		$fres = sqlStatement($query, array($id, $pid));
		$row = sqlFetchArray($fres);

		return $row;
	}

	public static function getInactiveCaseData() {
		$result = sqlStatement("SELECT * FROM form_cases WHERE closed = ? AND (lb_date IS NULL OR lb_date = '' OR lb_date = '0000-00-00') AND (lb_notes IS NULL OR lb_notes = '' ) ", array(1));
		$data = array();
		while ($row = sqlFetchArray($result)) {
			$data[] = $row;
		}
		return $data;
	}

	public static function updateCaseLiabilityData($id, $date, $note) {
		$sql = "UPDATE form_cases SET `lb_date` = ?, `lb_notes` = ? WHERE `id` = ?";
		return sqlStatement($sql, array($date, $note, $id));
	}

	public static function case_form_head($pid) {
		?>
		<style type="text/css">
			.trHide {
				display: none;
			}
			.hideElement {
				display: none !important;
			}
			.alert_log_table_container {
			    display: inline-block;
				vertical-align: top;
				margin-bottom: 5px;
			}

			.alert_log_table_container table tr td,
			.alert_log_table_container table tr th {
				padding: 5px;
			}

			.alert_log_table_container table tr:nth-child(even) {
				background: #EEEEEE !important;
			}
			/*.brDiv {
				border-top: 1px solid #000;
				width: 100%;
				height: 2px;
				margin-top: 15px;
    			margin-bottom: 15px;
			}*/
			.logContainer {
				display: grid;
    			grid-template-columns: 1fr auto;
    			padding-left: 10px;
			}
			/*.cs_rp_inputcontainer, 
			.u_inputcontainer {
				display: grid;
    			grid-template-columns: auto 1fr;
    			grid-column-gap: 10px;
    			margin-bottom: 5px;
			}
			.u_inputcontainer .actionsBtn {
				display: grid;
				grid-template-columns: auto auto;
				max-width: 130px;
			}*/
			/*.cs_referring,
			.ufield {
				height: 25px;
    			padding: 2px 12px 2px !important;
			    width: 100%;
			    min-width: 180px;
			    margin-top: 5px;
			}*/
			/*.cs_rp_addmore_btn, .cs_rp_remove_btn,
			.u_addmore_btn, .u_remove_btn {
				float: none !important;
    			justify-self: left !important;
			}
			.cs_rp_addmore_btn, .cs_rp_remove_btn,
			.u_addmore_btn, .u_remove_btn {
				margin-top: 5px;
				height: 25px;
    			padding: 0px 10px;
			}
			.rp_main_container {
				display: grid;
			    grid-template-columns: auto 1fr;
			    grid-column-gap: 20px;
			}
			.bic_container {
				display: grid;
				grid-column-gap: 15px;
    			grid-template-columns: auto auto auto 1fr;
			}
			.bcStatContainer {
				margin-top: 15px;
			}
			#bc_date {
				width: 100%;
			    min-width: 180px;
			}
			#bc_list_interim {
				width: 100%;
			    min-width: 200px;
			}
			#bc_notes {
				width: 100%;
			    min-width: 200px;
			}*/
			/*.case_manager_container {
				display: grid;
				grid-column-gap: 15px;
    			grid-template-columns: auto auto auto 1fr;
			}
			.case_manager {
				height: 25px;
    			padding: 2px 12px 2px !important;
			    width: 100%;
			    min-width: 180px;
			    margin-top: 5px;
			}
			.csmanager_field_container {
				display: grid;
				grid-template-columns: auto auto;
				grid-column-gap: 5px;
			}
			.csmanager_inputcontainer {
				display: grid;
    			grid-template-columns: 1fr auto;
    			grid-column-gap: 6px;
    			margin-bottom: 8px;
    			/*margin-bottom: 5px;*/
			}*/
			/*.rehab_field_1, .rehab_field_2 {
				height: 25px;
    			padding: 2px 12px 2px !important;
			    width: auto;
			    max-width: 180px;
			    margin-top: 5px;
			}*/
			/*.csmanager_addmore_btn, .csmanager_remove_btn, .csmanager_fremove_btn {
				float: none !important;
    			justify-self: left !important;
			}
			.csmanager_addmore_btn, .csmanager_remove_btn, .csmanager_fremove_btn {
				margin-top: 5px;
				height: 25px;
    			padding: 0px 10px;
			}*/
			/*.case_manager_container .btnContainer {
				display: grid;
				grid-template-columns: auto auto;
			}
			.csmanager_main {
				display: grid;
    			grid-template-columns: 1fr auto;
    			grid-gap: 8px;
    			align-items: start;
    			max-width: 350px;
			}*/

			/*.lpc_container {
				display: grid;
    			grid-template-columns: 1fr auto;
			}

			.search_user_btn {
				margin-top: 5px;
				margin-left: 5px;
				height: 25px;
    			padding: 3px 10px !important;
			}*/

			/*Additional Details Container*/
			/*.lpc_ele_container {
				margin-top: 10px;
			}
			.lpc_main_container .u_inputcontainer  {
				grid-template-columns: auto 128px 1fr !important;
			}
			.ipc_info_container{
				display: grid;
    			align-content: center;
			}*/
		</style>
		<script type="text/javascript">
			// //Set Lawyer/Paralegal Contacts
			// var curr_lpc_ele = null;

			// $(document).ready(function() {
			// 	$('.ins-dropdown').change(async function(){
			// 		var eles = $('.ins-dropdown');
			// 		var ids = [];

			// 		$.each( eles, function( index, ele ){
			// 		    var eleVal = $(ele).val();
			// 		    if(eleVal && eleVal != "") {
			// 		    	ids.push(eleVal);
			// 			}
			// 		});

			// 		var bodyObj = { ids :  ids, pid : '<?php //echo $pid ?>' };
			// 		const result = await $.ajax({
			// 			type: "POST",
			// 			url: "<?php //echo $GLOBALS['webroot'].'/library/OemrAD/interface/forms/cases/ajax/check_lb.php'; ?>",
			// 			datatype: "json",
			// 			data: bodyObj
			// 		});

			// 		if(result != '') {
			// 			var resultObj = JSON.parse(result);
			// 			if(resultObj && resultObj['status'] == true) {
			// 				$('.lb_row').removeClass('trHide').trigger('sectionClassChange');
							
			// 				toggleSubSection('show');
			// 			} else {
			// 				$('.lb_row').addClass('trHide').trigger('sectionClassChange');
			// 				resetValue();

			// 				toggleSubSection('hide');
			// 			}
			// 		}
			// 	});

			// 	//Reset
			// 	//resetBcSectionVal();
			// 	resetPISectionVal();

			// 	// $('#lb_row').on('sectionClassChange', function() {
			// 	// 	resetBcSectionVal();
			// 	// });

			// 	$('#pi_case_row').on('sectionClassChange', function() {
			// 		resetPISectionVal();
			// 	});

			// 	// function resetBcSectionVal() {
			// 	// 	var isEnable = $('#lb_row').hasClass("trHide");
			// 	// 	if(isEnable) {
			// 	// 		resetBillingSectionValue();
			// 	// 	}
			// 	// }

			// 	function resetPISectionVal() {
			// 		var isEnable = $('#pi_case_row').hasClass("trHide");
			// 		if(isEnable) {
			// 			resetPiSectionValue(true);
			// 		} else {
			// 			resetPiSectionValue(false);
			// 		}
			// 	}

			// 	function resetBillingSectionValue() {
			// 		$('#bc_date').val('');
			// 		$('#bc_notes').val('');
			// 		$('#bc_notes_dsc').val('');
			// 		$('#tmp_old_bc_value').val('');
			// 	}

			// 	function resetPiSectionValue(disable = true) {
			// 		if(disable === true) {
			// 			$('#case_header_case_manager').attr("disabled", "disabled");
			// 			$('.csmanager_field_container select').attr("disabled", "disabled");
			// 		} else {
			// 			$('#case_header_case_manager').removeAttr("disabled");
			// 			$('.csmanager_field_container select').removeAttr("disabled");
			// 		}
			// 	}

			// 	function toggleSubSection(type = 'show') {
			// 		var ele = $('.sec_row');

			// 		if(type == 'show' && ele) {
			// 			ele.find('.tmp_casemanager_hidden_sec').val('1');
			// 			ele.removeClass('trHide').trigger('sectionClassChange');
			// 		} else {
			// 			ele.find('.tmp_casemanager_hidden_sec').val('0');
			// 			ele.addClass('trHide').trigger('sectionClassChange');
			// 			//resetPICaseManagerValues(ele);
			// 		}
			// 	}

			// 	function resetValue() {
			// 		$('#lb_notes').val('');
			// 		$('#lb_date').val('');
			// 		$('#tmp_lb_notes').val('');
			// 	}

			// 	function resetPICaseManagerValues(ele) {
			// 		if(ele) {
			// 			ele.find('.case_manager').val('');
			// 			ele.find('.csmanager_container .csmanager_remove_btn').trigger( "click" );
			// 		}
			// 	}

			// 	$svgRemove = "<svg height='15pt' viewBox='0 0 512 512' width='15pt' xmlns='http://www.w3.org/2000/svg'><path d='m256 0c-141.164062 0-256 114.835938-256 256s114.835938 256 256 256 256-114.835938 256-256-114.835938-256-256-256zm0 0' fill='#2672ec'></path><path d='m350.273438 320.105469c8.339843 8.34375 8.339843 21.824219 0 30.167969-4.160157 4.160156-9.621094 6.25-15.085938 6.25-5.460938 0-10.921875-2.089844-15.082031-6.25l-64.105469-64.109376-64.105469 64.109376c-4.160156 4.160156-9.621093 6.25-15.082031 6.25-5.464844 0-10.925781-2.089844-15.085938-6.25-8.339843-8.34375-8.339843-21.824219 0-30.167969l64.109376-64.105469-64.109376-64.105469c-8.339843-8.34375-8.339843-21.824219 0-30.167969 8.34375-8.339843 21.824219-8.339843 30.167969 0l64.105469 64.109376 64.105469-64.109376c8.34375-8.339843 21.824219-8.339843 30.167969 0 8.339843 8.34375 8.339843 21.824219 0 30.167969l-64.109376 64.105469zm0 0' fill='#fafafa'></path></svg>";


			// 	// $('.cs_rp_container').on('click', '.cs_rp_addmore_btn', function(){
			// 	// 	addCsRP();
			// 	// });

			// 	// $('.cs_rp_container').on('click', '.cs_rp_remove_btn', function(){
			// 	// 	$(this).parent().remove();
			// 	// });

			// 	$('.u_container').on('change', '.lpcDropChange', function(){
			// 		var dropExtInfo = $(this).find(':selected').attr('data-extinfo');
			// 		dropExtInfo = atob(dropExtInfo);

			// 		if(dropExtInfo != '') {
			// 			$(this).parent().parent().find('.ipc_info_container').html(dropExtInfo);
			// 		}
			// 	});

			// 	$('.u_container').on('click', '.uAddmoreBtn1', function(){
			// 		adduEle1('', $(this).parent().parent().parent());
			// 	});

			// 	$('.u_container').on('click', '.uAddmoreBtn', function(){
			// 		adduEle('', $(this).parent().parent().parent());
			// 	});

			// 	$('.u_container').on('click', '.uRemoveBtn', function(){
			// 		//$(this).parent().parent().remove();
			// 		var csEle = $(this).parent().parent();
			// 		if(!csEle.hasClass('rawelements')) {
			// 			$(this).parent().parent().remove();
			// 		} else {
			// 			$(csEle).find('.ufield').val('');
			// 		}
			// 	});



			// 	//Case Manager
			// 	$('.csmanager_container').on('click', '.csmanager_addmore_btn', function(){
			// 		addCsManagerRehabPlanField();
			// 	});

			// 	$('.csmanager_container').on('click', '.csmanager_remove_btn', function(){
			// 		//$csEle = $('.csmanager_container .csmanager_inputcontainer').length;
			// 		var csEle = $(this).parent().parent();
			// 		if(!csEle.hasClass('rawelements')) {
			// 			$(this).parent().parent().remove();
			// 		} else {
			// 			removeFirstCsManagerRehabPlanField($(this));
			// 		}
			// 	});

			// 	$('.csmanager_container').on('click', '.csmanager_fremove_btn', function(){
			// 		removeFirstCsManagerRehabPlanField($(this));
			// 	});

			// 	$(".u_container").on('click', '.medium_modal', function(e) {
			// 		window.curr_lpc_ele = $(this).parent().parent();

			//         e.preventDefault();e.stopPropagation();
			//         dlgopen('', '', 700, 400, '', '', {
			//             buttons: [
			//                 {text: '<?php //echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
			//             ],
			//             //onClosed: 'refreshme',
			//             allowResize: false,
			//             allowDrag: true,
			//             dialogId: '',
			//             type: 'iframe',
			//             url: $(this).attr('href')
			//         });
			//     });
				
			// });

			// // This is for callback by the find-user popup.
			// function setuser(uid, uname, username, status) {
			// 	if(window.curr_lpc_ele && window.curr_lpc_ele != null) {
			// 		$(window.curr_lpc_ele).find('.uinner_container .ufield').eq(0).val(uid).trigger('change');
			// 	}
			// }

			// // function addCsRP(eVal = '') {
			// // 	$cs_rp_clone = $('.cs_rp_container .rawelements .cs_referring_container').clone();

			// // 	if(eVal && eVal != '') {
			// // 		$cs_rp_clone.find('.cs_referring').eq(0).val(eVal);
			// // 	}

			// // 	$cs_rp_html = $("<div class='cs_rp_inputcontainer'><div>").html($cs_rp_clone).append("<button type='button' class='cs_rp_remove_btn'>"+$svgRemove+"</button>");
			// // 	$('.cs_rp_container').append($cs_rp_html);
			// // }

			// function initCsRP($ids) {
			// 	$.each($ids, (index, item) => {
			// 		//var sEle = $('.cs_rp_container .cs_referring').eq(index);
			// 		var sEle = $('.cs_rp_container .ufield').eq(index);
			// 		if(sEle.length > 0) {
			// 			sEle.val(item);
			// 		} else {
			// 			//addCsRP(item);
			// 			adduEle(item, $('.cs_rp_container'));
			// 		}
			// 	});
			// }


			// //Add Init Lawyer/Paralegal Contacts
			// function initLPC(items) {
			// 	var itemId = JSON.parse(items);
			// 	$.each(itemId, (index, item) => {
			// 		//var sEle = $('.cs_rp_container .cs_referring').eq(index);
			// 		var sEle = $('.lpc_main_container .ufield').eq(index);
					
			// 		if(sEle.length > 0) {
			// 			sEle.val(item).trigger('change');
			// 		} else {
			// 			//addCsRP(item);
			// 			adduEle1(item, $('.lpc_main_container'));
			// 		}
			// 	});
			// }

			// function adduEle(eVal = '', e) {
			// 	$e_clone =  $(e).find('.rawelements .uinner_container').clone();

			// 	if(eVal && eVal != '') {
			// 		$e_clone.find('.ufield').eq(0).val(eVal);
			// 	}

			// 	$e_html = $("<div class='u_inputcontainer'><div>").html($e_clone).append("<div class='actionsBtn'><button type='button' class='u_remove_btn uRemoveBtn'>"+$svgRemove+"</button></div>");
			// 	$(e).append($e_html);
			// }

			// function adduEle1(eVal = '', e) {
			// 	$e_clone =  $(e).find('.rawelements .uinner_container').clone();
			// 	$e_html = $("<div class='u_inputcontainer'><div>").html($e_clone).append("<div class='actionsBtn'><button type='button' class='u_remove_btn uRemoveBtn'>"+$svgRemove+"</button></div><div class='ipc_info_container'></div>");
			// 	$(e).append($e_html);

			// 	if(eVal && eVal != '') {
			// 		$e_html.find('.ufield').eq(0).val(eVal).trigger('change');
			// 	}
			// }

			// function addCsManagerRehabPlanField(eVal = '', eVal1 = '') {
			// 	$csmanager_clone = $('.csmanager_container .rawelements .csmanager_field_container').clone();

			// 	if(eVal && eVal != '') {
			// 		$csmanager_clone.find('.rehab_field_1').eq(0).val(eVal);
			// 	} else {
			// 		$csmanager_clone.find('.rehab_field_1').eq(0).val('');
			// 	}

			// 	if(eVal1 && eVal1 != '') {
			// 		$csmanager_clone.find('.rehab_field_2').eq(0).val(eVal1);
			// 	} else {
			// 		$csmanager_clone.find('.rehab_field_2').eq(0).val('');
			// 	}

			// 	$csmanager_html = $("<div class='csmanager_inputcontainer'><div>").html($csmanager_clone).append("<div><button type='button' class='csmanager_remove_btn'>"+$svgRemove+"</button></div>");
			// 	$('.csmanager_inner_container').append($csmanager_html);
			// }

			// function removeFirstCsManagerRehabPlanField(ele) {
			// 	$csmanagerEle = ele.parent().parent();
			// 	if($csmanagerEle) {
			// 		$csmanagerEle.find('.csmanager_field_container .rehab_field_1').eq(0).val('');
			// 		$csmanagerEle.find('.csmanager_field_container .rehab_field_2').eq(0).val('');
			// 	}
			// }

			// // This invokes the find-addressbook popup.
			// function open_notes_log(pid, id) {
			// 	var url = '<?php //echo $GLOBALS['webroot']."/library/OemrAD/interface/forms/cases/case_view_logs.php?pid=". $pid; ?>'+'&id='+id;
			//   	let title = '<?php //echo xlt('Logs'); ?>';
			//   	dlgopen(url, 'notesLogs', 600, 400, '', title);
			// }

			// function open_field_log(pid, id, field_id = '', form_name = '') {
			// 	var url = '<?php //echo $GLOBALS['webroot']."/library/OemrAD/interface/forms/cases/view_logs.php?pid=". $pid; ?>'+'&form_id='+id+'&field_id='+field_id+'&form_name='+form_name;
			//   	let title = '<?php //echo xlt('Logs'); ?>';
			//   	dlgopen(url, 'viewfieldlog', 700, 400, '', title);
			// }

			// function updatenotes() {
			// 	var actionUrl = document.forms[0].action.split('?')[0];
			// 	const params = getParams(document.forms[0].action);
			// 	params['mode'] = 'updatenotes';

			// 	var newParams = Object.keys(params).map(function(k) {
			// 	    return encodeURIComponent(k) + '=' + encodeURIComponent(params[k])
			// 	}).join('&');

			// 	document.forms[0].action = actionUrl +'?'+newParams;
			// 	document.forms[0].submit();
			// }

			// function getParams(url) {
			// 	var params = {};
			// 	var parser = document.createElement('a');
			// 	parser.href = url;
			// 	var query = parser.search.substring(1);
			// 	var vars = query.split('&');
			// 	for (var i = 0; i < vars.length; i++) {
			// 		var pair = vars[i].split('=');
			// 		params[pair[0]] = decodeURIComponent(pair[1]);
			// 	}
			// 	return params;
			// }
		</script>
		<?php
	}

	function fetchAlertLogs($pid, $id, $limit = '', $frmn = 'form_cases') {
		$sql = "SELECT fl.*, u.username as user_name  FROM form_value_logs As fl LEFT JOIN users As u ON u.id = fl.username WHERE fl.field_id = ? AND fl.form_id = ? AND fl.form_name = ? AND fl.pid = ? ORDER BY date DESC ";

		if(!empty($limit)) {
			$sql .= ' LIMIT '.$limit;
		}

		$lres=sqlStatement($sql, array("lb_notes", $id, $frmn, $pid));
  		$result = array();

  		while ($lrow = sqlFetchArray($lres)) {
  			$result[] = $lrow;
  		}
  		return $result;
	}

	public static function fetchAlertLogsByParam($data = array(), $limit = '') {

		$whereParam = array();
		$bind = array();

		foreach ($data as $dk => $dItem) {
			if(!empty($dItem)) {
				$whereParam[] = $dk . " = ? ";  
				$bind[] = $dItem;
			}
		}

		$whereStr = "";
		if(!empty($whereParam)) {
			$whereStr = " WHERE " . implode(" AND ", $whereParam);
		}

		$sql = "SELECT fl.*, u.username as user_name FROM form_value_logs As fl LEFT JOIN users As u ON u.id = fl.username ".$whereStr." ORDER BY date DESC ";

		if(!empty($limit)) {
			$sql .= ' LIMIT '.$limit;
		}

		$lres=sqlStatement($sql, $bind);
  		$result = array();

  		while ($lrow = sqlFetchArray($lres)) {
  			$result[] = $lrow;
  		}
  		return $result;
	}

	public static function fetchCaseAlertMaxDAte($case_id) {
		$sql = "SELECT min(fl.created_date) as created_date, max(fl.created_date) as recent_date FROM case_form_value_logs As fl WHERE fl.case_id = ? ";

		$result=sqlQuery($sql, array($case_id));
  		return $result;
	}	

	public static function fetchCaseAlertLogs($case_id, $limit = '') {
		$sql = "SELECT fl.*, u.username as user_name  FROM case_form_value_logs As fl LEFT JOIN users As u ON u.id = fl.user WHERE fl.case_id = ? ORDER BY created_date DESC ";

		if(!empty($limit)) {
			$sql .= ' LIMIT '.$limit;
		}

		$lres=sqlStatement($sql, array($case_id));
  		$result = array();

  		while ($lrow = sqlFetchArray($lres)) {
  			$result[] = $lrow;
  		}
  		return $result;
	}

	public static function addScRcData($id, $value) {
		if(!empty($id)) {
			$sql = "UPDATE `form_cases` SET `sc_referring_id` = ? WHERE `id` = ?";
			sqlStatement($sql, array($value, $id));
		}
	}

	public static function updateRecentDate($id) {
		if(!empty($id)) {
			$logDate = self::fetchCaseAlertMaxDAte($id);
			$createdTime = isset($logDate['created_date']) ? $logDate['created_date'] : '';
			$updatedTime = isset($logDate['recent_date']) ? $logDate['recent_date'] : '';

			if(!empty($createdTime) && !empty($updatedTime)) {
				sqlStatement("UPDATE `form_cases` SET `bc_created_time` = ?, `bc_update_time` = ? WHERE `id` = ?", array($createdTime, $updatedTime, $id));
			}
		}
	}

	/*
	public static function case_save($pid, $isNoteUpdate = false) {
		global $frmn, $frmdir, $id, $modules, $field_prefix;

		$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : "";
		
		if($frmn == "form_cases" && $mode == "updatenotes") {
			// $field_prefix = '';

			// $form_lb_date = isset($_POST['tmp_lb_date']) && !empty($_POST['tmp_lb_date']) ? date("Y-m-d",strtotime(trim($_POST['tmp_lb_date'])))  : NULL;
			// $form_lb_notes = isset($_POST['tmp_lb_notes']) ? trim($_POST['tmp_lb_notes']) : "";
			// $form_lb_list_interim = "";
			// //$form_lb_list_interim = isset($_POST['tmp_lb_list_interim']) ? trim($_POST['tmp_lb_list_interim']) : "";

			// if(isset($_POST['tmp_lb_list_interim']) && !empty($_POST['tmp_lb_list_interim'])) {
			// 	$nq_filter = ' AND option_id = "'.$_POST['tmp_lb_list_interim'].'"';
			// 	$listOptions = LoadList('Case_Billing_Notes', 'active', 'seq', '', $nq_filter);

			// 	if(!empty($listOptions)) {
			// 		$form_lb_list_interim = $listOptions[0] && isset($listOptions[0]['title']) ? $listOptions[0]['title'] : "";
			// 	}
			// }

			// if(!empty($form_lb_list_interim)) {
			// 	if(!empty($form_lb_notes)) {
			// 		$form_lb_notes = $form_lb_list_interim . " - ".$form_lb_notes;
			// 	} else {
			// 		$form_lb_notes = $form_lb_list_interim;
			// 	}
			// }
			
			// if(!empty($form_lb_date) && !empty($form_lb_notes)) {
			// 	$sql = "INSERT INTO `case_form_value_logs` ( case_id, delivery_date, notes, user ) VALUES (?, ?, ?, ?) ";
			// 	sqlInsert($sql, array(
			// 		$id,
			// 		$form_lb_date,
			// 		$form_lb_notes,
			// 		$_SESSION['authUserID']
			// 	));
			// }
		}

		if($frmn == "form_cases" && ($mode == "save" || $mode == "updatenotes")) {
			foreach($modules as $module) {
				if($module['codes'] != '') $chp_options = explode('|', $module['codes']);
				$field_prefix = $chp_options[1];

				if($module['option_id'] == "case_header") {
					$sc_referring_id_tmp = isset($_REQUEST['tmp_' . $field_prefix . 'sc_referring_id']) ? $_REQUEST['tmp_' . $field_prefix . 'sc_referring_id'] : array();
					$sc_filter_referring_id = array();

					foreach($sc_referring_id_tmp as $key => $val) {
						if(!empty($val)) {
							$sc_filter_referring_id[] = $val;
						}
					}
					$sc_referring_id = implode("|",$sc_filter_referring_id);
					self::addScRcData($id, $sc_referring_id);
				}
			}
		}


		if($frmn == "form_cases" && ($mode == "save" || $mode == "updatenotes")) {
			$bc_date_value = isset($_POST['bc_date']) ? $_POST['bc_date'] : "";
			$bc_notes_value = isset($_POST['bc_notes']) ? $_POST['bc_notes'] : "";
			$bc_notes_dsc_value = isset($_POST['bc_notes_dsc']) ? $_POST['bc_notes_dsc'] : "";
			
			$bc_old_value = isset($_POST['tmp_old_bc_value']) ? $_POST['tmp_old_bc_value'] : "";
			$bc_new_value = $bc_date_value . $bc_notes_value . $bc_notes_dsc_value;

			if($bc_old_value !== $bc_new_value) {
				$form_lb_date = !empty($bc_date_value) ? date("Y-m-d",strtotime(trim($bc_date_value))) : NULL;
				$form_lb_list_interim = "";
				$form_lb_notes = $bc_notes_dsc_value;

				if(!empty($bc_notes_value)) {
					$nq_filter = ' AND option_id = "'.$bc_notes_value.'"';
					$listOptions = LoadList('Case_Billing_Notes', 'active', 'seq', '', $nq_filter);

					if(!empty($listOptions)) {
						$form_lb_list_interim = $listOptions[0] && isset($listOptions[0]['title']) ? $listOptions[0]['title'] : "";
					}
				}

				if(!empty($form_lb_list_interim)) {
					if(!empty($form_lb_notes)) {
						$form_lb_notes = $form_lb_list_interim . " - " . $form_lb_notes;
					} else {
						$form_lb_notes = $form_lb_list_interim;
					}
				}
				
				if(!empty($form_lb_date) || !empty($form_lb_notes)) {
					$sql = "INSERT INTO `case_form_value_logs` ( case_id, delivery_date, notes, user ) VALUES (?, ?, ?, ?) ";
					$sId = sqlInsert($sql, array(
						$id,
						$form_lb_date,
						$form_lb_notes,
						$_SESSION['authUserID']
					));

					if(!empty($id)) {
						self::updateRecentDate($id);
					}
				}
			}
		}

		if($frmn == "form_cases" && ($mode == "save" || $mode == "updatenotes")) {
			if(!empty($id)) {
				$fieldList = array('case_manager', 'rehab_field_1', 'rehab_field_2');
				foreach($modules as $module) {
					if($module['codes'] != '') $chp_options = explode('|', $module['codes']);
					$field_prefix = $chp_options[1];

					if($module['option_id'] == "case_header") {
						$data = array();
						$casemanager_hidden_sec = isset($_REQUEST['tmp_' . $field_prefix . 'casemanager_hidden_sec']) ? $_REQUEST['tmp_' . $field_prefix . 'casemanager_hidden_sec'] : 0;

						foreach ($fieldList as $fk => $fItem) {
							$data[$fItem] = isset($_REQUEST['tmp_' . $field_prefix . $fItem]) ? $_REQUEST['tmp_' . $field_prefix . $fItem] : "";
						}

						if($casemanager_hidden_sec === "1") {
							//Save PI Case Values
							$isNeedToUpdate = self::generateRehabLog($id, $data, $field_prefix);
							self::savePICaseManagmentDetails($id, $data);

							if($isNeedToUpdate !== false) {
								self::logFormFieldValues(array(
									'field_id' => 'rehab_field',
									'form_name' => $frmn,
									'form_id' => $id,
									'new_value' => $isNeedToUpdate['new_value'],
									'old_value' => $isNeedToUpdate['old_value'],
									'pid' => isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '',
									'username' => $_SESSION['authUserID']
								));
							}
						} else {
							self::savePICaseManagmentDetails($id, $data);
						}

						//Handle Lawyer/Paralegal Contacts
						$lpc_data = array();
						$lpc_fieldList = array('lp_contact');
						foreach ($lpc_fieldList as $lpc_k => $lpcItem) {
							$lpc_data[$lpcItem] = isset($_REQUEST['tmp_' . $field_prefix . $lpcItem]) ? $_REQUEST['tmp_' . $field_prefix . $lpcItem] : "";
						}
						if(!empty($lpc_data) && !empty($id)) {
							$c_notes = isset($_REQUEST[$field_prefix . 'notes']) ? $_REQUEST[$field_prefix . 'notes'] : "";
							$c_emails = array_filter(explode(",",$c_notes));
							$c_emails = array_map('trim',$c_emails);

							$t_emails = $c_emails;

							$lpContactData = self::getPICaseManageData($id, 'lp_contact');
							$lpList1 = array();
							$lpList2 = array();

							foreach ($lpContactData as $lpck => $lpcItem) {
								if(isset($lpcItem['field_value']) && !empty($lpcItem['field_value'])) {
									$lpList1[] = $lpcItem['field_value'];
								}
							}

							if(isset($lpc_data['lp_contact']) && !empty($lpc_data['lp_contact'])) {
								$lpList2 = $lpc_data['lp_contact'];
							}

							$diff1 = self::getArrayValDeff($lpList1, $lpList2);
							$diff2 = self::getArrayValDeff($lpList2, $lpList1);
							
							$diffa1 = self::getAbookData($diff1);
							$diffa2 = self::getAbookData($diff2);

							if(!empty($diff1) || !empty($diff2)) {
								foreach ($diff1 as $dak1 => $daI1) {
									if(isset($diffa1['id_'.$daI1]) && !empty($diffa1['id_'.$daI1])) {
										$daItem1 = $diffa1['id_'.$daI1];

										if(isset($daItem1['email']) && !empty($daItem1['email'])) {
											if (($ky1 = array_search($daItem1['email'], $t_emails)) !== false) {
												unset($t_emails[$ky1]);
											}
										}
									}
								}

								foreach ($diff2 as $dak2 => $daI2) {
									if(isset($diffa2['id_'.$daI2]) && !empty($diffa2['id_'.$daI2])) {
										$daItem2 = $diffa2['id_'.$daI2];

										if(isset($daItem2['email']) && !empty($daItem2['email'])) {
											$t_emails[] = $daItem2['email'];
										}
									}
								}

								if(isset($t_emails) && !empty($id)) {
									$t_emails_str = implode(", ", $t_emails);
									sqlStatement("UPDATE form_cases SET `notes` = ? WHERE `id` = ?", array($t_emails_str, $id));
								}
							}

							//Save PI Case Managment Data
							self::savePICaseManagmentDetails($id, $lpc_data);
						}
					}
				}
			}
		}
	}*/

	public static function getArrayValDeff($array1, $array2) {
		$diff = array_filter($array1, 
		  function ($val) use (&$array2) { 
		    $key = array_search($val, $array2);
		    if ( $key === false ) return true;
		    unset($array2[$key]);
		    return false;
		  }
		);

		return $diff;
	}

	public static function getAbookData($id = array()) {
		$resultItem = array();

		if(!empty($id)) {
			$idStr = implode("','", $id); 

			$result = sqlStatement("SELECT * from users u where id IN ('$idStr') ");
			while ($row = sqlFetchArray($result)) {
				$resultItem['id_'.$row['id']] = $row;
			}
		}

		return $resultItem;
	}

	public static function getUsersBy($thisField, $special_title='', $whereCon = array(), $display_extra = '', $allow_empty=true) {
		$whereStr = '';
		if(!empty($whereCon)) {
			if(is_array($whereCon)) {
				foreach ($whereCon as $wck => $wItem) {
					if(is_array($wItem)) {
						$whereStr .= "AND " . $wck . " IN('" . implode("','", $wItem) . "') ";
					} else {
						$whereStr .= "AND " . $wck . " = '" . $wItem . "' ";
					}
				}
			} else {
				$whereStr = $whereCon;
			}
		}
	  	$sql = "SELECT id, lname, fname, mname, specialty";
		if($display_extra) { $sql .= ", $display_extra"; }
		$sql .= " FROM users WHERE active=1 AND (lname != '' AND fname != '') ".
			" $whereStr ORDER BY lname";
			
	  	$rlist= sqlStatementNoLog($sql);

		if($allow_empty) {
	  		echo "<option value=''";
		  	if(!$thisField) echo " selected='selected'";
		  		echo ">&nbsp;</option>";
			}
		if($special_title) {
	  		echo "<option value='-1'";
	  	if($thisField == -1) echo " selected='selected'";
	  		echo ">$special_title</option>";
		}
	  	while ($rrow= sqlFetchArray($rlist)) {
	    	echo "<option value='" . $rrow['id'] . "'";
		    if($thisField == $rrow['id']) echo " selected='selected'";
		    echo ">" . $rrow['lname'].', '.$rrow['fname'].' '.$rrow['mname'];
				if($display_extra) {
					$keys = explode(',',$display_extra);
					foreach($keys as $extra) {
						$extra = trim($extra);
						if($extra) { echo ' - '.$rrow[$extra]; }
					}
				}
		    echo "</option>";
	  	}
	}

	public static function getExtraInfoOfLPC($lpcData = array()) {
		$lpctextData = array();

		if(!empty($lpcData)) {
			$lpctextData[] = $lpcData['lname'].', '.$lpcData['fname'];

			if(!empty($lpcData['organization'])) {
				$lpctextData[] = $lpcData['organization'];
			}

			if(!empty($lpcData['email'])) {
				$lpctextData[] = $lpcData['email'];
			}

			$lpc_addr_data = array();
			if(!empty($lpcData['street'])) {
				$lpc_addr_data[] = $lpcData['street'];
			}

			if(!empty($lpcData['streetb'])) {
				$lpc_addr_data[] = $lpcData['streetb'];
			}

			if(!empty($lpcData['city'])) {
				$lpc_addr_data[] = $lpcData['city'];
			}

			if(!empty($lpcData['state'])) {
				$lpc_addr_data[] = $lpcData['state'];
			}

			if(!empty($lpcData['zip'])) {
				$lpc_addr_data[] = $lpcData['zip'];
			}

			if(!empty($lpc_addr_data)) {
				$lpctextData[] = implode(", ", $lpc_addr_data);
			}
		}

		if(!empty($lpctextData)) {
			$lpctextData = implode(", ", $lpctextData);
		} else {
			$lpctextData = "";
		}

		return $lpctextData;
	}

	/*
	function piCaseManagementElements($pid) {
		global $dt, $field_prefix, $id;
		$rehabField2List = array(
			'PT' => 'PT',
			'LD' => 'LD',
			'CD' => 'CD',
			'DD' => 'DD'
		);

		$logsData = array();
		if(!empty($id)) {
			$logsData = self::fetchAlertLogsByParam(array(
				'field_id' => 'rehab_field',
				'form_name' => 'form_cases',
				'pid' => $pid,
				'form_id' => $id
			), 5);
		}

		$checkStatus = self::isInsLiableForPiCase($pid);
		$trClasss = !$checkStatus ? 'trHide' : '';

		// Rehab field
		$rehabField2List = array(
			'PT' => 'PT',
			'LD' => 'LD',
			'CD' => 'CD',
			'DD' => 'DD'
		);
		$rehab_field_1_val = isset($dt['tmp_'.$field_prefix.'rehab_field_1']) ? $dt['tmp_'.$field_prefix.'rehab_field_1'] : array();
		$rehab_field_2_val = isset($dt['tmp_'.$field_prefix.'rehab_field_2']) ? $dt['tmp_'.$field_prefix.'rehab_field_2'] : array();
		$fieldCount = (count($rehab_field_1_val) == count($rehab_field_2_val)) ? count($rehab_field_1_val) : 1;
		$fieldCount = ($fieldCount > 0) ? $fieldCount : 1;

		// Lawyer/Paralegal Contacts
		$lp_contact_val = isset($dt['tmp_'.$field_prefix.'lp_contact']) ? $dt['tmp_'.$field_prefix.'lp_contact'] : array();

		?>
		<!-- Pi Case Management Section -->
		<div id="pi_case_row" class="form-row mt-4 pi-case-management-container sec_row <?php echo $trClasss; ?>">
		    <div class="col-md-12">
		    	<div class="card">
		    		<div class="card-header">
				      <h6 class="mb-0 d-inline-block"><?php echo xl('PI Case Management'); ?></h6>
				    </div>
		    		<div class="card-body px-2 py-2">
		    			<!-- Case Manager -->
		    			<div class="form-row">
		    				<div class="col-lg-6">
		    						<!-- Case manager -->
		    						<div class="form-row">
		    							<div class="form-group col-lg-4">
									      <label for="case_id"><?php echo xl('Case Manager'); ?>:</label>
									      <!-- hidden input -->
									      <input type="hidden" name="<?php echo $field_prefix; ?>liability_payer_exists" class="liability_payer_exists" value="<?php echo $checkStatus === true ? 1 : 0 ?>">
										  <input type="hidden" name="tmp_<?php echo $field_prefix; ?>casemanager_hidden_sec" class="hidden_sec_input" value="<?php echo $checkStatus ? $checkStatus : 0 ?>">
										  <select name="tmp_<?php echo $field_prefix; ?>case_manager" class="case_manager form-control makedisable" id="<?php echo $field_prefix; ?>case_manager">
											<?php self::getUsersBy($dt['tmp_' . $field_prefix . 'case_manager'], '', array('physician_type' => array('chiropractor_physician', 'case_manager_232321'))); ?>
										  </select>
									    </div>
		    							<div class="col-lg-8">
		    								<label for="case_id"><?php echo xl('Rehab Plan'); ?>:</label>
		    								<div id="reahab_wrapper" class="d-flex align-items-start m-main-wrapper">
		    									<div class="m-elements-wrapper mr-2">
		    										<?php for ($fi=0; $fi < $fieldCount; $fi++) { ?>
		    										<!-- Input container -->
		    										<div class="m-element-wrapper mb-2">
		    											<!-- Field container -->
		    											<div class="input-group">
		    												<select name="tmp_<?php echo $field_prefix; ?>rehab_field_1[]" class="form-control makedisable" data-field-id="rehab_field_1" >
															    <option value=""></option>
																<?php
																	for ($i=1; $i <= 20 ; $i++) {
																		$isSelected = ($i == $rehab_field_1_val[$fi]) ? "selected" : ""; 
																		?>
																			<option value="<?php echo $i ?>" <?php echo $isSelected ?>><?php echo $i ?></option>
																		<?php
																	}
																?>
															  </select>
															  <select name="tmp_<?php echo $field_prefix; ?>rehab_field_2[]" class="form-control makedisable" data-field-id="rehab_field_2">
															    <option value=""></option>
																<?php
																	foreach ($rehabField2List as $rbk => $rbItem) {
																		$isSelected = ($rbk == $rehab_field_2_val[$fi]) ? "selected" : ""; 
																		?>
																			<option value="<?php echo $rbk ?>" <?php echo $isSelected ?>><?php echo $rbItem ?></option>
																		<?php
																	}
																?>
															  </select>
															  <div class="input-group-append">
															  	<button type="button" class="btn btn-primary m-btn-remove"><i class="fa fa-times" aria-hidden="true"></i></button>
																</div>
		    											</div>
		    											<!-- Remove Button -->
		    											<!-- <button type="button" class="btn btn-primary m-btn-remove"><i class="fa fa-times" aria-hidden="true"></i></button> -->
		    										</div>
		    										<?php } ?>
		    									</div>

		    									<!-- Add more item btn -->
		    									<button type="button" class="btn btn-primary m-btn-add"><i class="fa fa-plus" aria-hidden="true"></i> Add more</button>
		    								</div>

		    							</div>
		    						</div>
		    				</div>
		    				<div class="col-lg-6">
		    					<div class="alert_log_table_container">
									<table class="alert_log_table text">
										<tr class="showborder_head">
											<th>Sr.</th>
											<th>New Value</th>
											<th>Old Value</th>
											<th>Username</th>
											<th>DateTime</th>
										</tr>
										<?php
											$ci = 1;
											foreach ($logsData as $key => $item) {
												?>
												<tr>
													<td><?php echo $ci; ?></td>
													<td style="vertical-align: text-top;"><div style="white-space: pre;"><?php echo $item['new_value']; ?></div></td>
													<td style="vertical-align: text-top;"><div style="white-space: pre;"><?php echo $item['old_value']; ?></div></td>
													<td><?php echo $item['user_name']; ?></td>
													<td><?php echo date('d-m-Y h:i:s',strtotime($item['date'])); ?></td>
												</tr>
												<?php
												$ci++;
											}
										?>
									</table>
								</div>
								<br/>
								<?php if(isset($id) && !empty($id)) { ?>
								<a href="javascript:void(0)" onClick="caselibObj.open_field_log('<?php echo $pid ?>', '<?php echo $id ?>', 'rehab_field', 'form_cases')">View logs</a>
								<?php } ?>
				    		</div>
		    			</div>

		    			<!-- Lawyer/Paralegal Contacts -->
		    			<div class="form-row mt-2">
		    				<div class="col-lg-6">
		    					<label for="case_id"><?php echo xl('Lawyer/Paralegal Contacts'); ?>:</label>
									<div id="lpc_ele_container" class="d-flex align-items-start m-main-wrapper">
										<div class="m-elements-wrapper mr-2 w-100">
											<?php foreach ($lp_contact_val as $lpk => $lpItem) { ?>
											<!-- Input container -->
											<div class="m-element-wrapper jumbotron jumbotron-fluid px-2 py-2 mb-2 mb-2">
												<!-- Field container -->
												<div>
												<div class="input-group">
												  <select name="tmp_<?php echo $field_prefix; ?>lp_contact[]" class="form-control" data-field-id="lp_contact">
												    	<?php self::referringSelect($lpItem, '', '', array('Attorney'), '', true, true); ?>
												  </select>
												  <div class="input-group-append">
												  	<button type="button" class="btn btn-primary search_user_btn" href='<?php echo $GLOBALS['webroot']. '/interface/forms/cases/php/find_user_popup.php?abook_type=Attorney'; ?>'><i class="fa fa-search" aria-hidden="true"></i></button>
													</div>
												</div>
												<span class="field-text-info c-font-size-sm ipc_info_container c-text-info"></span>
											</div>
												<!-- Remove Button -->
												<button type="button" class="btn btn-primary m-btn-remove"><i class="fa fa-times" aria-hidden="true"></i></button>
											</div>
											<?php } ?>
										</div>

										<!-- Add more item btn -->
										<button type="button" class="btn btn-primary m-btn-add"><i class="fa fa-plus" aria-hidden="true"></i> <?php echo xl('Add more'); ?></button>
									</div>
		    				</div>
		    				<div class="col-lg-6">
		    				</div>
		    			</div>

		    			<!-- Email Addresses -->
		    			<div class="form-row mt-4">
		    				<div class="form-group col-lg-6">
		    					<label for="case_id"><?php echo xl('Email Addresses'); ?>: <i>**  <?php echo xl('Please use a comma to separate multiple addresses'); ?></i></label>
		    					<textarea name="<?php echo $field_prefix; ?>notes" id="<?php echo $field_prefix; ?>notes" class="form-control" rows="3" placeholder="Email Addresses" <?php echo AclMain::aclCheckCore('admin', 'super') === false ? "readonly" : ""; ?>><?php echo attr($dt[$field_prefix . 'notes']); ?></textarea>
		    				</div>
		    				<div class="col-lg-6">
		    				</div>
		    			</div>


		    		</div>
		    	</div>
		    </div>
		</div>
		<?php
	}

	public static function billingCollectionDeliveryElements($pid) {
		global $dt, $field_prefix, $id;

		$checkStatus = self::manageInsData($pid);
		//$trClasss = !$checkStatus ? 'trHide' : '';
		$trClasss = '';

		$logsData = self::fetchCaseAlertLogs($id, 5);
		
		?>
		<div id="lb_row" class="form-row mt-4 billing-delivery-status-container">
		    <div class="col-md-12">
		    	<div class="card">
		    		<div class="card-header">
				      <h6 class="mb-0 d-inline-block"><?php echo xl('Billing/Collection Delivery Status'); ?></h6>
				    </div>
		    		<div class="card-body px-2 py-2">
		    			<div class="form-row">
		    				<div class="col-lg-6">

		    					<div class="form-row">
								    <div class="form-group col-md-6">
								    	<label><?php echo xl('Delivery Date'); ?>:</label>
								      	<input type="text" class="form-control"name="bc_date" id="bc_date" placeholder="Delivery Date" value="<?php echo oeFormatShortDate($dt[$field_prefix . 'bc_date']); ?>">
								    </div>
								    <div class="form-group col-md-6">
								    	<label><?php echo xl('Billing Notes'); ?>:</label>
								    	<select name="bc_notes" id="bc_notes" class='form-control'>
								      		<?php ListSel($dt[$field_prefix . 'bc_notes'], 'Case_Billing_Notes'); ?>
								      	</select>
								    </div>
								  </div>

								  <div class="form-row">
								    <div class="form-group col-md-12">
								    	<label><?php echo xl('Notes'); ?>:</label>
								      	<textarea name="bc_notes_dsc" id="bc_notes_dsc" class="form-control" placeholder="Notes"><?php echo $dt[$field_prefix . 'bc_notes_dsc']; ?></textarea>
								      	<textarea type="text" name="tmp_old_bc_value" id="tmp_old_bc_value" class="form-control hideElement"><?php echo oeFormatShortDate($dt[$field_prefix . 'bc_date']) . $dt[$field_prefix . 'bc_notes'] . $dt[$field_prefix . 'bc_notes_dsc'] ?></textarea>
								    </div>
								  </div>

								  <div class="form-row">
								    <div class="form-group col-md-12">
								    	<div class="d-inline-block">
									    <div class="form-check">
									      <input class="form-check-input" type="checkbox" name="bc_stat" id="bc_stat" class="bc_stat" value="1" <?php echo $dt[$field_prefix . 'bc_stat'] ? 'checked' : ''; ?>>
									      <label class="form-check-label"><?php echo xl('Stat'); ?></label>
									    </div>
									  </div>
								    </div>
								  </div>

		    				</div>
		    				<div class="col-lg-6">

		    					<div class="logContainer">
									<div>
										<div class="alert_log_table_container">
											<table class="alert_log_table text">
												<tr class="showborder_head">
													<th>Sr.</th>
													<th>Delivery Date</th>
													<th>Notes</th>
													<th>Username</th>
													<th>Created Time</th>
												</tr>
												<?php
													$ci = 1;
													foreach ($logsData as $key => $item) {
														?>
														<tr>
															<td><?php echo $ci; ?></td>
															<td><?php echo $item['delivery_date']; ?></td>
															<td><?php echo $item['notes']; ?></td>
															<td><?php echo $item['user_name']; ?></td>
															<td><?php echo date('d-m-Y h:i:s',strtotime($item['created_date'])); ?></td>
														</tr>
														<?php
														$ci++;
													}
												?>
											</table>
										</div>
										<br/>
										<?php if(isset($id) && !empty($id)) { ?>
										<a href="javascript:void(0)" onClick="caselibObj.open_notes_log('<?php echo $pid ?>', '<?php echo $id ?>')">View logs</a>
										<?php } ?>
									</div>
								</div>

		    				</div>
		    			</div>
		    		</div>
		    	</div>
		    </div>
		</div>
		<?php
	}

	public static function careTeamProvidersElements($pid) {
		global $dt, $field_prefix, $id;

		$sc_referring_id_tmp = isset($dt['sc_referring_id']) ? $dt['sc_referring_id'] : "";
		$sc_referring_id_tmp = explode("|",$sc_referring_id_tmp);
		$sc_referring_id = json_encode($sc_referring_id_tmp);

		?>
		<!-- Care Team Providers -->
		<div class="form-row mt-4 care-team-providers-container">
		    <div class="col-md-12">
		    	<div class="card">
		    		<div class="card-header">
				      <h6 class="mb-0 d-inline-block"><?php echo xl('Care Team Providers'); ?></h6>
				    </div>
		    		<div class="card-body px-2 py-2">
		    			<div class="form-row">
						    <div class="col-lg-6">

						    	<div class="form-row">
								    <div class="form-group col-md-6">
								    	<label><?php echo xl('Referring Provider'); ?>:</label>
								      	<select class="form-control cs_referring" name="<?php echo $field_prefix; ?>referring_id" id="<?php echo $field_prefix; ?>referring_id">
										    <?php self::referringSelect($dt[$field_prefix . 'referring_id'], '', '', array('Referral Source', 'external_provider')); ?>
										</select>
								    </div>
								    <div class="form-group col-md-6">
								    	<label><?php echo xl('Additional Providers'); ?>:</label>

								      <div id="aprovider_wrapper" class="d-flex align-items-start m-main-wrapper">
		  									<div class="m-elements-wrapper mr-2 w-100">
		  										<!-- Input container -->
		  										<?php foreach ($sc_referring_id_tmp as $scrKey => $scrItem) { ?>
		  										<div class="m-element-wrapper mb-2">
		  											<!-- Field container -->
		  											<div class="input-group">
		  												<select class="form-control" data-field-id="sc_referring_id" name="tmp_<?php echo $field_prefix; ?>sc_referring_id[]">
														    <?php self::referringSelect($scrItem, '', '', array('Referral Source', 'external_provider')); ?>
														</select>
														<div class="input-group-append">
														  	<button type="button" class="btn btn-primary m-btn-remove"><i class="fa fa-times" aria-hidden="true"></i></button>
														</div>
		  											</div>
		  											<!-- Remove Button -->
		  											<!-- <button type="button" class="btn btn-primary m-btn-remove"><i class="fa fa-times" aria-hidden="true"></i></button> -->
		  										</div>
		  										<?php } ?>
		  									</div>

		  									<!-- Add more item btn -->
		  									<button type="button" class="btn btn-primary m-btn-add"><i class="fa fa-plus" aria-hidden="true"></i> <?php echo xl('Add more'); ?></button>
		  								</div>

								    </div>
								</div>

						    </div>
						    <div class="col-lg-6">
						    </div>
						</div>
		    		</div>
		    	</div>
		    </div>
		</div>
		<?php
	}*/

	/*
	public static function save_appt_script() {
		?>
		<script type="text/javascript">
			async function checkRecentInactive(pid = '', case_id = '') {
				var bodyObj = { pid : pid, case_id : case_id };
				const result = await $.ajax({
					type: "GET",
					url: "<?php echo $GLOBALS['webroot'].'/interface/forms/cases/ajax/check_recent_case.php'; ?>",
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
					url: "<?php echo $GLOBALS['webroot'].'/interface/forms/cases/ajax/activate_case.php'; ?>",
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

			async function caseCount(pid = '',) {
				var bodyObj = { pid : pid };
				var cCount = 0;

				const result = await $.ajax({
					type: "GET",
					url: "<?php echo $GLOBALS['webroot'].'/interface/forms/cases/ajax/get_case_count.php'; ?>",
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
		</script>
		<?php
	}
	*/

	/*
	public static function add_calender_script() {
		return <<<EOF
			<script type="text/javascript">
				async function checkRecentInactive(case_id = '') {
					var f = document.forms[0];
	    			var pid = f.form_pid.value;

					var bodyObj = { pid : pid, case_id : case_id };
					const result = await $.ajax({
						type: "GET",
						url: "{$GLOBALS['webroot']}/interface/forms/cases/ajax/check_recent_case.php",
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
				
				async function checkRecentInactive(case_id = '') {
					var f = document.forms[0];
	    			var pid = f.form_pid.value;

					var bodyObj = { pid : pid, case_id : case_id };
					const result = await $.ajax({
						type: "GET",
						url: "{$GLOBALS['webroot']}/interface/forms/cases/ajax/check_recent_case.php",
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

				async function activateCase(case_id = '') {
					var f = document.forms[0];
	    			var pid = f.form_pid.value;

					var bodyObj = { pid : pid, case_id : case_id };
					const result = await $.ajax({
						type: "GET",
						url: "{$GLOBALS['webroot']}/interface/forms/cases/ajax/activate_case.php",
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

				async function caseCount(p_id = '') {
					var f = document.forms[0];
	    			
	    			var pid = p_id;
	    			if(p_id == '') {
	    				pid = f.form_pid.value;
	    			}

					var bodyObj = { pid : pid };
					var cCount = 0;

					const result = await $.ajax({
						type: "GET",
						url: "{$GLOBALS['webroot']}/interface/forms/cases/ajax/get_case_count.php",
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
			</script>
EOF;
	}

	public static function caseForm_js() {
		?>
		<script type="text/javascript">
			// async function isCaseInsDataValid(ids = [], pid, employer = "") {
			// 	var status = true;

			// 	if(ids) {
			// 		var bodyObj = { ids :  ids, pid : pid, employer : employer };
			// 		const result = await $.ajax({
			// 			type: "POST",
			// 			url: "<?php //echo $GLOBALS['webroot'].'/library/OemrAD/interface/forms/cases/ajax/get_case_form_status.php'; ?>",
			// 			datatype: "json",
			// 			data: bodyObj
			// 		});

			// 		if(result != '') {
			// 			var resultObj = JSON.parse(result);
			// 			if(resultObj && resultObj['case_form_status'] === false) {
			// 				if(!confirm("Warning - all insurances must have subscribers listed, including PI.  Press \"Cancel\" to go back and set the subscriber or Press \"Ok\" to save and continue.")) {
			// 					status = false;
			// 				}
			// 			}

			// 			if(resultObj && resultObj['case_employer_status'] === false) {
			// 				if(!confirm("Warning - all workers compensation insurances require an employer to be listed.  Press \"Cancel\" to go back and set the employer or Press \"Ok\" to save and continue.")) {
			// 					status = false;
			// 				}
			// 			}
			// 		}
			// 	}

			// 	return status;
			// }

			// function checkAuthValidation() {
			// 	var numEle = document.querySelector('.auth_num_visit').value;
			// 	var isAuthChecked = document.querySelector('.auth_req').checked;
			// 	var numEle = document.querySelector('.auth_num_visit').value;
			// 	var authStartDate = document.querySelector('.auth_start_date').value;
			// 	var authEndDate = document.querySelector('.auth_end_date').value;

			// 	var validationStatus = true;
			// 	var errorMsg = [];

			// 	if(isAuthChecked == true) {
			// 		if((authStartDate == "" && authEndDate != "") || (authStartDate != "" && authEndDate == "") ) {
			// 			validationStatus = false;
			// 			errorMsg.push('Start Date and End Date are required if Authorization Dates are specified.');
			// 		}

			// 		if(authStartDate != "" && authEndDate != "") {
			// 			var authStartD = new Date(authStartDate);
			// 			var authEndD = new Date(authEndDate);

			// 			if(authStartD > authEndD) {
			// 				errorMsg.push('Authorization Details - the End Date must be equal or greater than the Start Date.');
			// 				validationStatus = false;
			// 			}
			// 		}

			// 		if(numEle != "" && isAuthChecked == true) {
			// 			if(!isNaN(numEle) && numEle >= 0 && numEle < 100) {
			// 				//validationStatus = true;
			// 			} else {
			// 				errorMsg.push('Authorized Number of Visits must be a number less than 100.');
			// 				validationStatus = false;
			// 			}
			// 		}
			// 	}

			// 	if(errorMsg.length > 0) {
			// 		alert(errorMsg.join('\n\n'));
			// 	}

			// 	return validationStatus;
			// }

			// function validateNoteEmails() {
			// 	let inValidEmailList = [];
			// 	let case_header_notes = document.getElementById('case_header_notes').value;
			// 	//let mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
			// 	let mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w+)+$/;

			// 	if(case_header_notes != "") {	
			// 		let em_list = case_header_notes.split(",").map(element => element.trim());
			// 		em_list.forEach((eItem, ei) => {
			// 			if(eItem != "" && !eItem.match(mailformat)) {
			// 				inValidEmailList.push(eItem);
			// 			}
			// 		});
			// 	}

			// 	if(inValidEmailList.length > 0) {
			// 		return "Invalid Email ('" + inValidEmailList.join("', '") + "')";
			// 	}

			// 	return true;	
			// }

			// function validateCaseForm() {
			// 	var tmp_casemanager_hidden_sec = document.getElementsByClassName('tmp_casemanager_hidden_sec')[0].value;
				
			// 	if(tmp_casemanager_hidden_sec != 1) {
			// 		return true;
			// 	}

			// 	var case_manager_val = document.getElementById('case_header_case_manager').value;
			// 	var errorList = [];
				
			// 	if(case_manager_val == "") {
			// 		errorList.push("Case manager field is required.");
			// 	}

			// 	let noteEmailStatus = validateNoteEmails();
			// 	if(noteEmailStatus !== true) {
			// 		//errorList.push(noteEmailStatus);
			// 		alert(noteEmailStatus);
			// 	}

			// 	var csFieldContainer = document.querySelectorAll(".csmanager_container .csmanager_inputcontainer .csmanager_field_container");
			// 	for(var i = 0; i < csFieldContainer.length; i++){
			// 		var r1 = csFieldContainer[i].getElementsByClassName("rehab_field_1")[0];
			// 		var r2 = csFieldContainer[i].getElementsByClassName("rehab_field_2")[0];
				   	
			// 	   	if(r1 && r2) {
			// 	   		if(r1.value.trim() == "" && r2.value.trim() != "") {
			// 	   			errorList.push("Rehab plan-"+(i+1)+" field 1 must not be empty.");
			// 	   		}

			// 	   		if(r1.value.trim() != "" && r2.value.trim() == "") {
			// 	   			errorList.push("Rehab plan-"+(i+1)+" field 2 must not be empty.");
			// 	   		}
			// 	   	}
			// 	}

			// 	if(errorList.length > 0) {
			// 		alert(errorList.join("\n"));
			// 		return false;
			// 	}

			// 	return true;
			// }
		</script>
		<?php
	}
	*/

	function case_header_authorization_elements($dt, $field_prefix) {
		?>
		<script type="text/javascript">let checked_mode = "";let unchecked_mode = "";</script>
		<label><input name="<?php echo $field_prefix; ?>auth_req" id="<?php echo $field_prefix; ?>auth_req" type="checkbox" class="auth_req" value="1" <?php echo $dt[$field_prefix . 'auth_req'] ? 'checked' : ''; ?> onchange="ToggleDivDisplay('case_header_auth_req_container', '<?php echo $field_prefix; ?>auth_req');" />&nbsp;&nbsp;<?php echo xl('Authorization Required'); ?></label>
		<?php
	}

	function authorizationRequestElements($pid, $field_prefix, $dt) {
		global $date_title_fmt;
		$showStyle = $dt[$field_prefix . 'auth_req'] ? "display:block;" : "display:none;";

		?>
		<!-- Authorization Details -->
		<div id="case_header_auth_req_container" class="form-row mt-4 authorization-container">
		    <div class="col-md-12">
		    	<div class="card">
		    		<div class="card-header">
				      <h6 class="mb-0 d-inline-block"><?php echo xl('Authorization Details'); ?></h6>
				    </div>
		    		<div class="card-body px-2 py-2">
		    			<div class="form-row">
						    <div class="form-group col-md-3">
						    	<label><?php echo xl('Start Date'); ?>:</label>
						     	<input type="text" name="<?php echo $field_prefix; ?>auth_start_date" id="<?php echo $field_prefix; ?>auth_start_date" class="form-control auth_date auth_start_date" placeholder="Start Date" value="<?php echo attr(oeFormatShortDate($dt[$field_prefix . 'auth_start_date'])); ?>" title="Enter as <?php echo $date_title_fmt; ?>">
						    </div>
						    <div class="form-group col-md-3">
						    	<label><?php echo xl('End Date'); ?></label>
						    	<input type="text" name="<?php echo $field_prefix; ?>auth_end_date" id="<?php echo $field_prefix; ?>auth_end_date" class="form-control auth_date auth_end_date" placeholder="End Date" value="<?php echo attr(oeFormatShortDate($dt[$field_prefix . 'auth_end_date'])); ?>" title="Enter as <?php echo $date_title_fmt; ?>">
						    </div>

						    <div class="form-group col-md-3">
						    	<label><?php echo xl('Authorized Number of Visits'); ?>:</label>
						    	<input type="number" min="1" max="999" name="<?php echo $field_prefix; ?>auth_num_visit" id="<?php echo $field_prefix; ?>auth_num_visit" class="form-control auth_num_visit" placeholder="0" value="<?php echo attr($dt[$field_prefix . 'auth_num_visit']); ?>">
						    </div>

						    <div class="form-group col-md-3">
						    	<label ><?php echo xl('Authorized Provider'); ?>:</label>
							    <select name="<?php echo $field_prefix; ?>auth_provider" id="<?php echo $field_prefix; ?>auth_provider" class="form-control auth_provider">
							    	<?php ProviderSelect($dt[$field_prefix . 'auth_provider']); ?>
								</select>
						    </div>
						  </div>

						  <div class="form-row">
						    <div class="form-group col-lg-6">
						    	<label for="validationCustom01"><?php echo xl('Authorization Notes'); ?>:</label>
								<textarea name="<?php echo $field_prefix; ?>auth_notes" id="<?php echo $field_prefix; ?>auth_notes" class="form-control" placeholder="Authorization Notes"><?php echo attr($dt[$field_prefix . 'auth_notes']); ?></textarea>
						    </div>
						    <div class="col-lg-6">
						    </div>
						  </div>
		    		</div>
		    	</div>
		    </div>
		</div>
		<?php
	}

	public static function mostRecentCase($pid, $case_id = '') {
		if(!$pid) return false;

		if(!empty($case_id)) {
			$sql= 'SELECT * FROM `form_cases` WHERE `pid` = ? AND id = ? ORDER BY `form_dt` DESC LIMIT 1';
			$bindArray = array($pid, $case_id);
		} else {
			$sql = 'SELECT * FROM `form_cases` WHERE `pid` = ? ORDER BY `form_dt` DESC LIMIT 1'	;
			$bindArray = array($pid);
		}
		
		$frow = sqlQuery($sql, $bindArray);
		return $frow;
	}

	public static function activateCase($pid, $case_id = '') {
		if(empty($case_id)) return false;
		
		$sql = "UPDATE form_cases SET `closed` = ? WHERE `id` = ?";
		return sqlStatement($sql, array(0, $case_id));
	}

	public static function getCaseCount($pid) {
		if(!$pid) return false;

		$sql = 'SELECT count(*) as count FROM `form_cases` WHERE `pid` = ? ORDER BY `form_dt` DESC LIMIT 1'	;
		$bindArray = array($pid);
		$frow = sqlQuery($sql, $bindArray);

		return $frow;
	}

	public static function getCsRpExists($encounter, $pid) {
		$status = false;

		if(!empty($encounter)) {
			$sql = "SELECT e.*, f.name AS facility_name, opc.pc_catname, cal.pc_eid, cal.enc_case FROM form_encounter AS e LEFT JOIN facility AS f ON f.id = e.facility_id LEFT JOIN openemr_postcalendar_categories AS opc USING(pc_catid) LEFT JOIN case_appointment_link AS cal ON cal.encounter = e.encounter WHERE e.pid=? AND e.encounter=? ";

			$res = sqlStatement($sql, array($pid, $encounter));
			while ($result = sqlFetchArray($res)) {
				$enc_case = isset($result['enc_case']) ? $result['enc_case'] : "";

				if(!empty($enc_case)) {
					$case_data = self::getCaseData($enc_case);
					$rp_list = self::getCaseReferringProviderList($case_data);

					if(!empty($rp_list)) {
						$status = $rp_list;
					}
				}
			}
		}

		return $status;
	}

	public static function getCaseData($case_id) {
		if(!empty($case_id)) {
			$case_data = sqlQuery("SELECT * FROM form_cases WHERE id = ? ", array($case_id));
			return $case_data;
		}

		return false;
	}

	public static function getCaseReferringProviderList($case_data) {
		$r_list = array();

		if(!empty($case_data)) {
			$referring_id_tmp = isset($case_data['referring_id']) ? $case_data['referring_id'] : "";
			$sc_referring_id_tmp = isset($case_data['sc_referring_id']) ? $case_data['sc_referring_id'] : "";
			$sc_referring_id_tmp = explode("|",$sc_referring_id_tmp);

			if(!empty($referring_id_tmp) && !in_array($referring_id_tmp, $r_list)) {
				$r_list[] = $referring_id_tmp;
			}

			if(!empty($sc_referring_id_tmp)) {
				foreach ($sc_referring_id_tmp as $rk => $rid) {
					if(!empty($rid) && !in_array($rid, $r_list)) {
						$r_list[] = $rid;
					}
				}
			}
		}

		return $r_list;
	}

	public static function getRpData($ids = array()) {
		$rp_data = array();
		if(!empty($ids) && $ids !== false) {
			$sql = "SELECT u.* FROM users AS u WHERE u.id IN(".implode(",",$ids).") ";
			$res = sqlStatement($sql, array());

			while ($result = sqlFetchArray($res)) {
				$rp_data[] = $result;
			}
		}

		return $rp_data;
	}

	public static function getCtNotificationCategories($encounter) {
		$ct_notification_categories = $GLOBALS['ct_notification_categories'];
		$ctn_cat_list = explode(",",$ct_notification_categories);

		$encounter_data = sqlQuery("SELECT * FROM form_encounter WHERE encounter = ? ", array($encounter));
		if(!empty($encounter_data) && isset($encounter_data['pc_catid']) && !empty($encounter_data['pc_catid'])) {
			if (in_array($encounter_data['pc_catid'], $ctn_cat_list)) {
				return true;
			}
		}

		return false;
	}

	public static function getLinkedApptByCase($pid, $case_id) {
		$results = array();

		$sql = "SELECT fc.id as case_id, ope.pc_eid, ope.pc_case as pc_case from form_cases fc left join openemr_postcalendar_events ope on ope.pc_case = fc.id and ope.pc_pid = fc.pid where fc.id = '".$case_id."' and fc.pid = ".$pid." and ope.pc_apptstatus not in ('x', '%', '?')";

		if(!empty($pid) && !empty($case_id)) {
			$result = sqlStatement($sql);
			while ($row = sqlFetchArray($result)) {
				$results[] = $row;
			}
		}

		return $results;
	}

	public static function getLinkedEncounterByCase($case_id) {
		$results = array();

		$sql = "SELECT fc.id, fc.auth_req, fc.auth_num_visit, fc.auth_start_date, fc.auth_end_date, fe.encounter from form_cases fc left join case_appointment_link cal on cal.enc_case = fc.id left join form_encounter fe on fe.encounter = cal.encounter where fc.id = ? and (fe.encounter != '' or fe.encounter is not null)";

		if(!empty($case_id)) {
			$result = sqlStatement($sql, array($case_id));
			while ($row = sqlFetchArray($result)) {
				$results[] = $row;
			}
		}

		return $results;
	}

	public static function getLinkedCaseByEncounter($enc_id) {
		$sql = "SELECT fe.encounter, fc.id as case_id from form_encounter fe left join case_appointment_link cal on cal.encounter = fe.encounter left join form_cases fc on fc.id = cal.enc_case where fe.encounter = ? ";

		$case_data = sqlQuery($sql, array($enc_id));
		return $case_data;
	}

	public static function getEncounterById($encounter_id) {
		$enc_data = array();

		if(!empty($encounter_id)) {
			$sql = "SELECT * from form_encounter fe where fe.encounter = ? ";
			$enc_data = sqlQuery($sql, array($encounter_id));
		}

		return $enc_data;
	}

	public static function getEncounterSignData($encounter) {
		$results = array();

		$sql = "SELECT fe.encounter, es.id, es.tid from form_encounter fe left join esign_signatures es on es.tid = fe.encounter and es.`table` = 'form_encounter' where fe.encounter = ? and es.id != ''";

		if(!empty($encounter)) {
			$result = sqlStatement($sql, array($encounter));
			while ($row = sqlFetchArray($result)) {
				$results[] = $row;
			}
		}

		return $results;
	}

	public static function orderCaseEle($cnt, $rto) {
		global $pid, $newordermode;
		$cFieldId = "rto_case_".$cnt;

		if($newordermode !== true) {
			return;
		}

		if(isset($rto['rto_case']) && !empty($rto['rto_case'])) {
			$caseData = self::getCaseData($rto['rto_case']);
			$caseTitle =  "";
			if(isset($caseData) && isset($caseData['case_description']) && !empty($caseData['case_description'])) {
				//$caseTitle .= " - ".$caseData['case_description'];
				$caseTitle .= $caseData['case_description'];
			}

			?>
			<div class="caseContainer">
				<div>
					<input name="rto_case_<?php echo $cnt; ?>" id="rto_case_<?php echo $cnt; ?>" type="text" value="<?php echo $rto['rto_case']; ?>" style="width: auto" class="wmtInput wmtFInput" onclick="sel_case('<?php echo $pid; ?>', '<?php echo $cnt; ?>');" title="Click to select or add a case for this appointment" />
				</div>
				<div class="caseDescription" style="font-size:13px;">			
					<span id="<?php echo "case_description_title_".$cnt; ?>" ><i><?php echo $caseTitle ?></i></span>
				</div>
			</div>
			<?php

		} else { 
		?>
			<div class="caseContainer">
				<div>
					<input name="rto_case_<?php echo $cnt; ?>" id="rto_case_<?php echo $cnt; ?>" type="text" value="<?php echo $rto['rto_case']; ?>" class="wmtInput wmtFInput" onclick="sel_case('<?php echo $pid; ?>', '<?php echo $cnt; ?>');" title="Click to select or add a case for this appointment" />
				</div>
				<div class="caseDescription" style="font-size:13px;">			
					<span id="<?php echo "case_description_title_".$cnt; ?>" ><i><?php echo $caseTitle ?></i></span>
				</div>
			</div>
		<?php
		}
	}

	public static function afterFormSuccess_js($pid, $encounter) {
		$isNotificationEnable = self::getCtNotificationCategories($encounter);

		if($isNotificationEnable === true) {
		?>
		var bodyObj = { encounter :  '<?php echo $encounter ?>', pid : '<?php echo $pid ?>' };
		const cs_result = await $.ajax({
			type: "POST",
			url: "<?php echo $GLOBALS['webroot'].'/library/OemrAD/interface/patient_file/encounter/ajax/check_cs_rp.php'; ?>",
			datatype: "json",
			data: bodyObj
		});

		if(cs_result != '') {
			var csResultObj = JSON.parse(cs_result);
			if(csResultObj && csResultObj['status'] !== false) {
				var rp_url = '<?php echo $GLOBALS['webroot']."/library/OemrAD/interface/patient_file/encounter/case_rp_view.php?pid=". $pid . "&encounter=" . $encounter; ?>';
			  	let rp_title = '<?php echo xlt('Care Team Providers'); ?>';
			  	dlgopen(rp_url, 'case_rp_view', 700, 400, '', rp_title);
			  	
			}
		}

		<?php
		}
	}

	function getAuthorizedCaseId($pid, $encounter, $encounter_date) {
		if(!empty($encounter)) {
			$encounterSignData = self::getEncounterSignData($encounter);

			if(count($encounterSignData) === 0) {
				$case_data = self::getLinkedCaseByEncounter($encounter);
				$case_id = isset($case_data['case_id']) ? $case_data['case_id'] : "";

				if(!empty($case_id)) {
					return $case_id;
				}
			}
		}

		return false;
	}

	public static function init_case_rp_Js() {
		global $pid;
		?>
		<script type="text/javascript">
			//Init
			//var attachClassObject = new attachClass('attachClassObject', 'attachment_container', 'filesDoc');
			//$(document).ready(function(){
				//attachClassObject.getSelectEncountersElement('<?php //echo $pid; ?>');
				//attachClassObject.getSelectDocumentElement('<?php //echo $pid; ?>');
			//});
		</script>
		<?php
	}

	/*public static function generateRehabFields($dt = array(), $field_prefix = '') {
		if(!empty($dt)) {
			$rehabField2List = array(
				'PT' => 'PT',
				'LD' => 'LD',
				'CD' => 'CD',
				'DD' => 'DD'
			);
			$rehab_field_1_val = isset($dt['tmp_'.$field_prefix.'rehab_field_1']) ? $dt['tmp_'.$field_prefix.'rehab_field_1'] : array();
			$rehab_field_2_val = isset($dt['tmp_'.$field_prefix.'rehab_field_2']) ? $dt['tmp_'.$field_prefix.'rehab_field_2'] : array();
			$fieldCount = (count($rehab_field_1_val) == count($rehab_field_2_val)) ? count($rehab_field_1_val) : 1;
			$fieldCount = ($fieldCount > 0) ? $fieldCount : 1;

			?>
			<div class="csmanager_container csmanager_main">
				<div class="csmanager_inner_container">
					<?php
						for ($fi=0; $fi < $fieldCount; $fi++) { 
							?>
							<div class="csmanager_inputcontainer <?php echo $fi === 0 ? 'rawelements' : '' ?>">
								<div class="csmanager_field_container">
									<select name="tmp_<?php echo $field_prefix; ?>rehab_field_1[]" class="rehab_field_1">
										<option value=""></option>
										<?php
											for ($i=1; $i <= 20 ; $i++) {
												$isSelected = ($i == $rehab_field_1_val[$fi]) ? "selected" : ""; 
												?>
													<option value="<?php echo $i ?>" <?php echo $isSelected ?>><?php echo $i ?></option>
												<?php
											}
										?>
									</select>
									<select name="tmp_<?php echo $field_prefix; ?>rehab_field_2[]" class="rehab_field_2">
										<option value=""></option>
										<?php
											foreach ($rehabField2List as $rbk => $rbItem) {
												$isSelected = ($rbk == $rehab_field_2_val[$fi]) ? "selected" : ""; 
												?>
													<option value="<?php echo $rbk ?>" <?php echo $isSelected ?>><?php echo $rbItem ?></option>
												<?php
											}
										?>
									</select>
								</div>

								<div>
									<button type='button' class='csmanager_remove_btn'><svg height='15pt' viewBox='0 0 512 512' width='15pt' xmlns='http://www.w3.org/2000/svg'><path d='m256 0c-141.164062 0-256 114.835938-256 256s114.835938 256 256 256 256-114.835938 256-256-114.835938-256-256-256zm0 0' fill='#2672ec'></path><path d='m350.273438 320.105469c8.339843 8.34375 8.339843 21.824219 0 30.167969-4.160157 4.160156-9.621094 6.25-15.085938 6.25-5.460938 0-10.921875-2.089844-15.082031-6.25l-64.105469-64.109376-64.105469 64.109376c-4.160156 4.160156-9.621093 6.25-15.082031 6.25-5.464844 0-10.925781-2.089844-15.085938-6.25-8.339843-8.34375-8.339843-21.824219 0-30.167969l64.109376-64.105469-64.109376-64.105469c-8.339843-8.34375-8.339843-21.824219 0-30.167969 8.34375-8.339843 21.824219-8.339843 30.167969 0l64.105469 64.109376 64.105469-64.109376c8.34375-8.339843 21.824219-8.339843 30.167969 0 8.339843 8.34375 8.339843 21.824219 0 30.167969l-64.109376 64.105469zm0 0' fill='#fafafa'></path></svg></button>
								</div>
							</div>
							<?php
						}
					?>
				</div>
				<button type="button" class="csmanager_addmore_btn">Add more</svg></button>
			</div>
			<?php
		}
	}*/

	public static function getPICaseManageData($case_id, $field_val = '') {
		$resultItem = array();
		$binds = array();
		$whereStr = '';

		if(!empty($case_id)) {
			$binds = array($case_id);

			if(!empty($field_val)) {
				$whereStr .= ' AND field_name = ? ';
				$binds[] = $field_val;
			}

			$result = sqlStatement("SELECT * FROM vh_pi_case_management_details WHERE case_id = ? $whereStr ", $binds);

			while ($row = sqlFetchArray($result)) {
				$resultItem[] = $row;
			}
		}

		return $resultItem;
	}

	public static function piCaseManagerFormData($case_id, $field_prefix = '') {
		$resultItem = array();

		if(!empty($case_id)) {
			$caseManageData = self::getPICaseManageData($case_id);
			foreach ($caseManageData as $rk => $row) {
				if(isset($row['field_name'])) {
					$field_name = "tmp_" . $field_prefix . $row['field_name'];
					$field_value = isset($row['field_value']) ? $row['field_value'] : "";

					if(!isset($resultItem[$field_name])) {
						$resultItem[$field_name] = array();
					}

					if($row['field_name'] == "case_manager") {
						$resultItem[$field_name] = $field_value;
					} else {
						$resultItem[$field_name][] = $field_value;
					}
				}
			}	
		}

		return $resultItem;
	}

	public static function savePICaseManagmentDetails($case_id = '', $data = array()) {

		if(!empty($case_id) && !empty($data)) {
			foreach ($data as $dk => $dItem) {
				if(!empty($dk)) {

					//Delete Record
					sqlStatement("DELETE FROM `vh_pi_case_management_details` WHERE case_id = ? AND field_name = ? ", array($case_id, $dk));

					if(is_array($dItem)) {
						foreach ($dItem as $diK => $dsItem) {
							if(!empty($dsItem)) {
								//Insert Items
								$insertSql = "INSERT INTO `vh_pi_case_management_details` (case_id, field_name, field_index, field_value) VALUES (?, ?, ?, ?) ";
								sqlInsert($insertSql, array(
									$case_id,
									$dk,
									$diK,
									$dsItem
								));
							}
						}
					} else {

						//Insert Items
						$insertSql = "INSERT INTO `vh_pi_case_management_details` (case_id, field_name, field_index, field_value) VALUES (?, ?, ?, ?) ";
						sqlInsert($insertSql, array(
							$case_id,
							$dk,
							0,
							$dItem
						));
					}
				}	
			}
		}
	}

	public static function logFormFieldValues($data = array()) {
		if(!empty($data)) {
			extract($data);

			$sql = "INSERT INTO `form_value_logs` ( field_id, form_name, form_id, new_value, old_value, pid, username ) VALUES (?, ?, ?, ?, ?, ?, ?) ";
			return sqlInsert($sql, array(
				$field_id,
				$form_name,
				$form_id,
				$new_value,
				$old_value,
				$pid,
				$username
			));
		}

		return false;
	}

	public static function generateRehabLog($case_id = '', $data = array(), $field_prefix = '') {
		if(!empty($case_id)) {
			$fieldList = array('rehab_field_1', 'rehab_field_2');
			$caseManagerData = self::piCaseManagerFormData($case_id, '');
			$oldFieldValue = array();
			$newFieldValue = array();

			if(isset($caseManagerData['tmp_rehab_field_1']) && isset($caseManagerData['tmp_rehab_field_2'])) {
				$oldR1Field = $caseManagerData['tmp_rehab_field_1'];
				$oldR2Field = $caseManagerData['tmp_rehab_field_2'];

				for ($old_i=0; $old_i < count($oldR1Field); $old_i++) { 
					if(empty($oldR1Field[$old_i]) || empty($oldR2Field[$old_i])) {
						continue;
					}
					$oldFieldValue[] = $oldR1Field[$old_i] ."-". $oldR2Field[$old_i];
				}
			}

			if(isset($data['rehab_field_1']) && isset($data['rehab_field_2'])) {
				$newR1Field = $data['rehab_field_1'];
				$newR2Field = $data['rehab_field_2'];

				for ($new_i=0; $new_i < count($newR1Field); $new_i++) {
					if(empty($newR1Field[$new_i]) || empty($newR2Field[$new_i])) {
						continue;
					}
					$newFieldValue[] = $newR1Field[$new_i] ."-". $newR2Field[$new_i];
				}
			}

			$diffValArray1 = array_diff($newFieldValue, $oldFieldValue);
			$diffValArray2 = array_diff($oldFieldValue, $newFieldValue);

			$isNeedToUpdate = false;
			if($diffValArray1 !== $diffValArray2) {
				$isNeedToUpdate = true;
			}

			if($isNeedToUpdate === true) {
				return array(
					'old_value' => implode(", ", $oldFieldValue),
					'new_value' => implode(", ", $newFieldValue)
				);
			}
		}

		return false;
	}

	public static function referringSelect($thisField, $special_title='', $specialty='', $abook_type = array(), $display_extra = '', $allow_empty=true, $extInfo = false) {
		if($specialty) {
			$specialty = "AND UPPER(specialty) LIKE UPPER('%$specialty%')";
		}

		if(!empty($abook_type)) {
			$abook_type = "AND abook_type IN ('". implode("','", $abook_type) ."')";
		}

	  $sql = "SELECT *";
		if($display_extra) { $sql .= ", $display_extra"; }
		$sql .= " FROM users WHERE active=1 ".
			" $specialty $abook_type ORDER BY lname";
	  $rlist= sqlStatementNoLog($sql);
		if($allow_empty) {
	  	echo "<option value=''";
	  	if(!$thisField) echo " selected='selected'";
	  	echo ">&nbsp;</option>";
		}
		if($special_title) {
	  	echo "<option value='-1'";
	  	if($thisField == -1) echo " selected='selected'";
	  	echo ">$special_title</option>";
		}
	  	while ($rrow= sqlFetchArray($rlist)) {
	  		$extInfoTxt = '';

	  		if($extInfo === true) {
	  			$extInfoTxt = self::getExtraInfoOfLPC($rrow);
	  			if(!empty($extInfoTxt)) {
	  				$extInfoTxt = base64_encode($extInfoTxt);
	  				$extInfoTxt = "data-extinfo='$extInfoTxt'";
	  			}
	  		}

		    echo "<option $extInfoTxt value='" . $rrow['id'] . "'";
		    if($thisField == $rrow['id']) echo " selected='selected'";
		    echo ">" . $rrow['lname'].', '.$rrow['fname'].' '.$rrow['mname'];
				if($display_extra) {
					$keys = explode(',',$display_extra);
					foreach($keys as $extra) {
						$extra = trim($extra);
						if($extra) { echo ' - '.$rrow[$extra]; }
					}
				}
		    echo "</option>";
		 }
	}

	public static function get15kThresholdData($case_id = '') {
		$resultData = array();
		if(!empty($case_id)) {
			$sql = "select vtd.*, date(vtd.reported_datetime_whencrossed15k) as reported_date_whencrossed15k from vh_15000threshold_data vtd where vtd.case_id = ?";
			return sqlQuery($sql, array($case_id));
		}

		return $resultData;
	}
}
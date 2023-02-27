<?php

require_once("../../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/wmt-v2/wmtstandard.inc");
require_once("$srcdir/wmt-v2/wmt.msg.inc");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\Core\Header;
use OpenEMR\OemrAd\Caselib;

$page_action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = $_POST['search']['value']; // Search value

$filterVal = isset($_POST['filterVal']) ? $_POST['filterVal'] : array(); // Filter value
$colList = isset($_POST['columnList']) ? $_POST['columnList'] : array(); // Column List value

$searchArray = array();
$columnList = array(
	array(
		"name" => "dt_control",
		"title" => "dt_control",
		"data" => array(
            "className" => 'dt-control-all dt-control',
            "orderable" => false,
            "data" => '',
            "defaultContent" => '',
            "width" => "25"
		) 
	),
	array(
		"name" => "appt_datetime",
		"title" => "Time",
		"data" => array(
			"width" => "180"
		)
	),
	array(
		"name" => "patient_name",
		"title" => "Patient Name",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "250"
		)
	),
	array(
		"name" => "appt_type",
		"title" => "Appointment Type",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "180",
            "orderable" => false,
		)
	),
	array(
		"name" => "appt_provider",
		"title" => "Provider",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "250"
		)
	),
	array(
		"name" => "date_of_injury",
		"title" => "Date of Injury",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "orderable" => false,
		)
	),
	array(
		"name" => "appt_comments",
		"title" => "Appointment Comments",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "visible" => false
		)
	),
	array(
		"name" => "ins_data",
		"title" => "Insurances",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "visible" => false,
            "width" => "0"
		)
	),
	array(
		"name" => "rehab_progress",
		"title" => "Rehab Progress",
		"data" => array(
			"visible" => false,
			"defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
			"orderable" => false,
			"width" => "0"
		)
	),
	array(
		"name" => "areas_of_treatment",
		"title" => "Areas of treatment",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "visible" => false,
            "width" => "0"
		)
	),
	array(
		"name" => "lsd_weights",
		"title" => "Last Spinal Decompression weights",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "visible" => false,
            "width" => "0"
		)
	),
	array(
		"name" => "orders",
		"title" => "Orders",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "visible" => false,
            "width" => "0"
		)
	),
	array(
		"name" => "authorization_info",
		"title" => "Authorization Info",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "visible" => false,
            "width" => "0"
		)
	),
	array(
		"name" => "auth_start_date",
		"title" => "auth_start_date",
		"data" => array(
            "visible" => false,
            "width" => "0"
		)
	),
	array(
		"name" => "auth_end_date",
		"title" => "auth_end_date",
		"data" => array(
            "visible" => false,
            "width" => "0"
		)
	),
	array(
		"name" => "auth_num_visit",
		"title" => "auth_num_visit",
		"data" => array(
            "visible" => false,
            "width" => "0"
		)
	),
	array(
		"name" => "auth_provider",
		"title" => "auth_provider",
		"data" => array(
            "visible" => false,
            "width" => "0"
		)
	),
	array(
		"name" => "auth_notes",
		"title" => "auth_notes",
		"data" => array(
            "visible" => false,
            "width" => "0"
		)
	),
	array(
		"name" => "pc_eid",
		"title" => "pc_eid",
		"data" => array(
            "visible" => false,
            "width" => "0"
		)
	)
);

function getOrdesByCase($case_id = '') {
	$dataItems = array();

	$whereStr = '';
	$binds = array();
	if(is_array($case_id)) {
		$whereStr = "fr.rto_case IN ('". implode("','", $case_id) ."') ";
	} else {
		$whereStr = "fr.rto_case = ?";
		$binds[] = $case_id;
	}

	if($case_id != "") {
		$result = sqlStatement("SELECT fr.*, lo.title as rto_action_title, lo1.title as rto_status_title from form_rto fr left join list_options lo on lo.option_id = fr.rto_action and lo.list_id = 'RTO_Action' left join list_options lo1 on lo1.option_id = fr.rto_status and lo1.list_id = 'RTO_Status' where $whereStr order by fr.`date` asc", $binds);
		while ($row = sqlFetchArray($result)) {
			$dataItems[] = $row;
		}
	}

	return $dataItems;
}

function getEncounterData($case_id = '', $formdir = '') {
	$dataSet = array();

	$whereStr = array();
	$binds = array();
	if(is_array($case_id)) {
		$whereStr[] = "cal.enc_case IN ('". implode("','", $case_id) ."') ";
	} else {
		$whereStr[] = "cal.enc_case = ?";
		$binds[] = $case_id;
	}

	if(is_array($formdir)) {
		$whereStr[] = "f.formdir IN ('". implode("','", $formdir) ."') ";
	} else {
		$whereStr[] = "f.formdir = ?";
		$binds[] = $formdir;
	}

	if(!empty($whereStr)) {
		$whereStr = implode(" AND ", $whereStr);
	} else {
		$whereStr = '';
	}

	if(!empty($case_id)) {
		// $esql = sqlStatement("SELECT cal.enc_case as case_id, fe.id as enc_id, fe.encounter, f.pid, f.form_name, f.form_id, f.formdir FROM case_appointment_link cal LEFT JOIN form_encounter fe ON fe.encounter = cal.encounter JOIN forms f ON f.encounter = fe.encounter WHERE $whereStr AND fe.`date` <= now()", $binds);
		$esql = sqlStatement("SELECT cal.enc_case as case_id, fe.id as enc_id, fe.encounter, f.pid, f.form_name, f.form_id, f.formdir, ld.field_id, ld.field_value, lgp.grp_title FROM case_appointment_link cal, form_encounter fe, forms f, lbf_data ld, layout_group_properties lgp WHERE f.encounter = fe.encounter and fe.encounter = cal.encounter AND $whereStr AND fe.`date` <= now() AND ld.form_id = f.form_id AND lgp.grp_form_id = f.formdir AND (ld.field_value IS NOT NULL AND ld.field_value != '') group by lgp.grp_title;", $binds);

		while ($enrow = sqlFetchArray($esql)) {
			$dataSet[] = $enrow;
		}
	}

	return $dataSet;
}

function getEncounterData2($case_id = '', $formdir = '') {
	$dataSet = array();

	$whereStr = array();
	$binds = array();
	if(is_array($case_id)) {
		$whereStr[] = "cal.enc_case IN ('". implode("','", $case_id) ."') ";
	} else {
		$whereStr[] = "cal.enc_case = ?";
		$binds[] = $case_id;
	}

	if(is_array($formdir)) {
		$whereStr[] = "f.formdir IN ('". implode("','", $formdir) ."') ";
	} else {
		$whereStr[] = "f.formdir = ?";
		$binds[] = $formdir;
	}

	if(!empty($whereStr)) {
		$whereStr = implode(" AND ", $whereStr);
	} else {
		$whereStr = '';
	}

	if(!empty($case_id)) {
		// $esql = sqlStatement("SELECT cal.enc_case as case_id, fe.id as enc_id, fe.encounter, f.pid, f.form_name, f.form_id, f.formdir FROM case_appointment_link cal LEFT JOIN form_encounter fe ON fe.encounter = cal.encounter JOIN forms f ON f.encounter = fe.encounter WHERE $whereStr AND fe.`date` <= now()", $binds);
		$esql = sqlStatement("SELECT cal.enc_case as case_id, fe.id as enc_id, fe.encounter, f.pid, f.form_name, f.form_id, f.formdir from case_appointment_link cal, form_encounter fe,forms f WHERE f.encounter = fe.encounter and fe.encounter = cal.encounter AND $whereStr AND fe.`date` <= now()", $binds);

		while ($enrow = sqlFetchArray($esql)) {
			$dataSet[] = $enrow;
		}
	}

	return $dataSet;
}

function getEncounterData1($case_id) {
	$dataSet = array();

	if(!empty($case_id)) {
		// $esql = sqlStatement("SELECT fe.id as enc_id, fe.encounter, f.pid, f.form_name, f.form_id, f.formdir FROM case_appointment_link cal LEFT JOIN form_encounter fe ON fe.encounter = cal.encounter JOIN forms f ON f.encounter = fe.encounter WHERE cal.enc_case = ? AND f.formdir IN ('LBF_rehab') order by fe.`date` desc", array($case_id));
		$esql = sqlStatement("SELECT fe.id as enc_id, fe.encounter, f.pid, f.form_name, f.form_id, f.formdir FROM case_appointment_link cal,form_encounter fe ,forms f WHERE fe.encounter = cal.encounter and f.encounter = fe.encounter and cal.enc_case = ? AND f.formdir IN ('LBF_rehab') order by fe.`date` desc", array($case_id));
		while ($enrow = sqlFetchArray($esql)) {
			$dataSet[] = $enrow;
		}
	}

	return $dataSet;
}

function getLayoutOptionsData($formId = '', $formdir = '') {
	$dataSet = array();

	$whereStr = '';
	$binds = array($formId);

	if(is_array($formdir)) {
		$whereStr = "lo.form_id IN ('". implode("','", $formdir) ."') ";
	} else {
		$whereStr = "lo.form_id = ?";
		$binds[] = $formdir;
	}

	$esql = sqlStatement("SELECT ld.field_value, lo.*, lgp.grp_title FROM layout_options lo LEFT JOIN layout_group_properties lgp on lgp.grp_form_id = lo.form_id AND lgp.grp_group_id = lo.group_id LEFT JOIN lbf_data ld on ld.field_id = lo.field_id AND ld.form_id = ? WHERE $whereStr AND lo.uor > 0 ORDER BY lo.group_id, lo.seq", $binds);

	while ($enrow = sqlFetchArray($esql)) {
		$dataSet[] = $enrow;
	}

	return $dataSet;
}

function getLBFFieldValue($form_id, $field_id = array()) {
	$dataSet = array();

	$whereStr = array();
	$binds = array();

	if(!empty($form_id)) {
		$whereStr[] = "form_id = ?";
		$binds[] = $form_id;
	}

	if(is_array($field_id)) {
		$whereStr[] = "field_id IN ('". implode("','", $field_id) ."') ";
	}

	if(!empty($whereStr)) {
		$whereStr = implode(" AND ", $whereStr);
	} else {
		$whereStr = '';
	}

	$esql = sqlStatement("SELECT field_id, field_value FROM lbf_data WHERE $whereStr ", $binds);

	while ($enrow = sqlFetchArray($esql)) {
		$dataSet[$enrow['field_id']] = $enrow['field_value'];
	}

	return $dataSet;
}

function getLsdWeights($formid) {
	$fres = sqlStatement("SELECT ld.*, lo.title as field_title, lo2.title as op_title FROM lbf_data ld JOIN layout_options lo left join list_options lo2 on lo2.option_id = ld.field_value WHERE ld.form_id = ? AND lo.field_id = ld.field_id AND lo.title IN ('Cervical SD Low Limit', 'Cervical SD High Limit', 'Lumbar SD Low Limit', 'Lumbar SD High Limit')", array($formid));

	$fieldList = array(
		'Cervical SD Low Limit' => 'c_low',
		'Cervical SD High Limit' => 'c_high',
		'Lumbar SD Low Limit' => 'l_low',
		'Lumbar SD High Limit' => 'l_high'
	);
	$dataSet = array(
		'c_low' => '',
		'c_high' => '',
		'l_low' => '',
		'l_high' => '',
	);

	while ($frow1 = sqlFetchArray($fres)) {
		$field_id = $frow1['field_id'];
		$field_title = $frow1['field_title'];
		$currvalue = $frow1['op_title'];

		if(isset($fieldList[$field_title])) {
			$optv = isset($fieldList[$field_title]) ? $fieldList[$field_title] : '';
			if(!empty($optv)) {
				$dataSet[$optv] = $currvalue;
			}
		}
	}

	return $dataSet;
}

function getLsdWeights1($formname, $formid, $pid) {
	$fres = sqlStatement("SELECT lo.*, lgp.grp_title FROM layout_options lo LEFT JOIN layout_group_properties lgp on lgp.grp_form_id = lo.form_id AND lgp.grp_group_id = lo.group_id " .
                "WHERE lo.form_id = ? AND lo.uor > 0 " .
                "ORDER BY lo.group_id, lo.seq", array($formname));
	$fieldList = array(
		'Cervical SD Low Limit' => 'c_low',
		'Cervical SD High Limit' => 'c_high',
		'Lumbar SD Low Limit' => 'l_low',
		'Lumbar SD High Limit' => 'l_high'
	);
	$dataSet = array();

	while ($frow1 = sqlFetchArray($fres)) {
		$field_id = $frow1['field_id'];
		$field_title = $frow1['title'];
		$source = $frow1['source'];
		$edit_options = $frow1['edit_options'];

		if(isset($fieldList[$field_title])) {
			$currvalue = lbf_current_value($frow1, $formid, $encounter);

			if(!empty($currvalue)) {
				$loData = sqlQuery("SELECT lo.* FROM list_options lo where lo.option_id = ?",array($currvalue));

				if(!empty($loData) && isset($loData['title'])) {
					$dataSet[$fieldList[$field_title]] = $loData['title'];
				}
			}
		}
	}

	return $dataSet;
}

function getHtmlString($text) {
	return addslashes(htmlspecialchars($text));
}

//Filter Query Data
function generateFilterQuery($filterData = array()) {
	$filterQryList = array();
	$filterQry = "";

	if(!empty($filterData)) {
		if(isset($filterData['appt_status']) && !empty($filterData['appt_status'])) {
			$filterQryList[] = "ope.pc_apptstatus IN('" . implode("','", $filterData['appt_status']) . "')";
		}

		if(isset($filterData['appt_category']) && !empty($filterData['appt_category'])) {
			$filterQryList[] = "ope.pc_catid IN('" . implode("','", $filterData['appt_category']) . "')";
		}

		if(isset($filterData['appt_facility']) && !empty($filterData['appt_facility'])) {
			$filterQryList[] = "ope.pc_facility = ".$filterData['appt_facility']."";
		}

		if(isset($filterData['appt_provider']) && !empty($filterData['appt_provider'])) {
			$filterQryList[] = "ope.pc_aid = ".$filterData['appt_provider']."";
		}

		if(isset($filterData['appt_date_from']) && !empty($filterData['appt_date_from']) && isset($filterData['appt_date_to']) && !empty($filterData['appt_date_to'])) {
			$filterData['appt_date_from'] = date('Y/m/d', strtotime($filterData['appt_date_from']));
			$filterData['appt_date_to'] = date('Y/m/d', strtotime($filterData['appt_date_to']));
			
			$filterQryList[] = "(ope.pc_eventDate IS NOT null and ope.pc_eventDate != '' and date(ope.pc_eventDate) between '".$filterData['appt_date_from']."' and '".$filterData['appt_date_to']."')";
		}

		if(!empty($filterQryList)) {
			$filterQry = implode(" and ", $filterQryList);
		}
	}

	return $filterQry;
}

//Generate Query
function generateSqlQuery($data = array(), $isSearch = false) {
	$select_qry = isset($data['select']) ? $data['select'] : "*";
	$where_qry = isset($data['where']) ? $data['where'] : "";
	$order_qry = isset($data['order']) ? $data['order'] : "ope.pc_eid"; 
	$order_type_qry = isset($data['order_type']) ? $data['order_type'] : "desc";

	if($order_qry == "appt_datetime") {
		$order_qry = "cast(concat(ope.pc_eventDate, ' ', ope.pc_startTime) as datetime)";
	} else if($order_qry == "patient_name") {
		$order_qry = "CONCAT(CONCAT_WS(' ', IF(LENGTH(pd.fname),pd.fname,NULL), IF(LENGTH(pd.lname),pd.lname,NULL)), ' (', pd.pubpid ,')')";
	} else if($order_qry == "appt_provider") {
		$order_qry = "concat(u.lname, ', ', u.fname)";
	}

	$limit_qry = isset($data['limit']) ? $data['limit'] : ""; 
	$offset_qry = isset($data['offset']) ? $data['offset'] : "asc";

	$sql = "SELECT $select_qry from openemr_postcalendar_events ope";

	$patient_data_join = " left join patient_data pd on pd.pid = ope.pc_pid";
	$provider_data_join = " left join users u on u.id = ope.pc_aid";

	if(isset($data['filter_data'])) {
	 	$filter_data = isset($data['filter_data']) ? $data['filter_data'] : array();

		if((isset($filter_data['law_firm']) && !empty($filter_data['law_firm'])) || ($filter_data['case_manager'] == 'blank')) {
			//$sql .= $ins_data_join;
		}

	} else {
		if($isSearch === false) {
			if(!empty($select_qry) && $select_qry != "*") {
				$sql .= $patient_data_join . $provider_data_join . " ";
			}
		}
	}

	if(!empty($where_qry)) {
		$sql .= " WHERE $where_qry";
	}

	if(!empty($order_qry)) {
		$sql .= " ORDER BY $order_qry $order_type_qry";
	}

	if($limit_qry != '' && $offset_qry != '') {
		$sql .= " LIMIT $limit_qry , $offset_qry";
	}

	return $sql;
}

//Prepare Data Table Data
function prepareDataTableData($row_item = array(), $columns = array(), $rowDataSet = array()) {
	$rowData = array();
	$apptTypeList = array();

	$caseData = Caselib::getCaseData($row_item['case_id']);
	$caseManagerData = Caselib::piCaseManagerFormData($row_item['case_id'], '');
	$isPiCaseLiable = Caselib::isLiablePiCaseByCase($row_item['case_id'], $row_item['pid'], $caseData);

	$cres = sqlStatement("SELECT pc_catid, pc_cattype, pc_catname, pc_recurrtype, pc_duration, pc_end_all_day FROM openemr_postcalendar_categories where pc_active = 1 ORDER BY pc_seq");
	while ($crow = sqlFetchArray($cres)) {
		$apptTypeList[$crow['pc_catid']] = array(
			'pc_cattype' => $crow['pc_cattype'],
			'pc_catname' => $crow['pc_catname']
		);
	}

	foreach ($columns as $clk => $cItem) {
		if(isset($cItem['name'])) {
			if($cItem['name'] == "appt_datetime") {
				$fieldHtml = "<a href=\"#!\" onclick=\"oldEvt('".$row_item['pc_eid']."');\">". $row_item[$cItem['name']] ."</a>";

				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "patient_name") {
				$fieldHtml = "<a href=\"#!\" onclick=\"goParentPid('".$row_item['pid']."');\">". $row_item[$cItem['name']] . "</a>";
				
				$rowData[$cItem['name']] = (isset($row_item[$cItem['name']]) && !empty($row_item[$cItem['name']])) ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "appt_type") {
				$fieldHtml = '';
				if(isset($row_item['pc_catid']) && !empty($row_item['pc_catid'])) {
					if(isset($apptTypeList[$row_item['pc_catid']])) {
						$fieldHtml = $apptTypeList[$row_item['pc_catid']]['pc_catname'];
					}
					
				}
				
				$rowData[$cItem['name']] = (isset($fieldHtml) && !empty($fieldHtml)) ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "appt_provider") {
				$fieldHtml = isset($row_item['provider_name']) ? $row_item['provider_name'] : "";
				$rowData[$cItem['name']] = (isset($fieldHtml) && !empty($fieldHtml)) ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "appt_comments") {
				$fieldHtml = isset($row_item['pc_hometext']) ? $row_item['pc_hometext'] : "";
				$rowData[$cItem['name']] = (isset($fieldHtml) && !empty($fieldHtml)) ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "ins_data") {
				$ins_data = array();
				$fieldHtml = array();

				for ($ins_i=1; $ins_i <= 3; $ins_i++) { 
					if(isset($caseData['ins_data_id'.$ins_i]) && $caseData['ins_data_id'.$ins_i] != "") {
						$cid = $caseData['ins_data_id'.$ins_i];

						if(isset($cid) && $cid != 0) {
							$ins_data[] = Caselib::getInsuranceCompaniesData($cid, $row_item['pid']);
						}
					}
				}

				if(isset($ins_data)) {
					foreach ($ins_data as $lk => $lItem) {
						$fieldHtml[] = "<span>".$lItem['name']."</span>";
					}
				}

				if(!empty($fieldHtml)) {
					$fieldHtml = implode(", ", $fieldHtml);
				} else {
					$fieldHtml = "";
				}

				$rowData[$cItem['name']] = (isset($fieldHtml) && !empty($fieldHtml)) ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "date_of_injury") {
				$fieldHtml = isset($caseData['injury_date']) ? $caseData['injury_date'] : "";
				$rowData[$cItem['name']] = (isset($fieldHtml) && !empty($fieldHtml)) ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "rehab_plan") {
				$fieldHtml = "";

				if($caseManagerData && $isPiCaseLiable === true) {
					//$caseManagerData = $_EXTCORE->ExtCase->piCaseManagerFormData($row_item['case_id'], '');
					$oldFieldValue = array();

					if(isset($caseManagerData['tmp_rehab_field_1']) && isset($caseManagerData['tmp_rehab_field_2'])) {
						$oldR1Field = $caseManagerData['tmp_rehab_field_1'];
						$oldR2Field = $caseManagerData['tmp_rehab_field_2'];

						for ($old_i=0; $old_i < count($oldR1Field); $old_i++) { 
							$oldFieldValue[] = $oldR1Field[$old_i] ."". $oldR2Field[$old_i];
						}
					}
					$fieldHtml = !empty($oldFieldValue) ? getHtmlString(implode(", ", $oldFieldValue)) : "";
				}
				
				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? $fieldHtml : "";
				continue;
			} else if($cItem['name'] == "rehab_progress") { 
				$fieldHtml = array();
				$finalDataSet = array();
				$lbfFormData = isset($row_item['lbf_data']) ? $row_item['lbf_data'] : array();
				if(!empty($lbfFormData)) {
					$finalDataSet = array(
						"PT" => isset($lbfFormData['pt']) ? $lbfFormData['pt'] : 0,
						"LD" => isset($lbfFormData['ld']) ? $lbfFormData['ld'] : 0,
						"CD" => isset($lbfFormData['cd']) ? $lbfFormData['cd'] : 0,
						"DD" => isset($lbfFormData['dd']) ? $lbfFormData['dd'] : 0
					);
				}

				if($isPiCaseLiable === true) {
					$rehabPlanData = Caselib::getRehabPlanDataByCase($row_item['case_id'], $caseManagerData);

					foreach ($rehabPlanData as $rpd => $rpdItem) {
						//$fieldHtml[] = $rpdItem['id'] . " " . $rpdItem['appt_count'] . "/" . $rpdItem['value_sum'];
						$apptCount = isset($finalDataSet[$rpdItem['id']]) ? $finalDataSet[$rpdItem['id']] : 0;
						$fieldHtml[] = $rpdItem['id'] . " " . $apptCount . "/" . $rpdItem['value_sum'];
					}
				}

				if(!empty($fieldHtml)) {
					$fieldHtml = implode(", ", $fieldHtml);
				} else {
					$fieldHtml = "";
				}

				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? $fieldHtml : "";
				continue;
			} else if($cItem['name'] == "orders") {
				$fieldHtml = array();
				$ordersData = Caselib::getOrdesByCase($row_item['case_id']);

				$orderStatusListClass = array(
					'p' => 'text_red',
					'misinfo' => 'text_red',
					'pendins' => 'text_red',
					'pendpi' => 'text_red',
					'UCRijw$' => 'text_red',
					'ssss' => 'text_green',
					'ssss142' => 'text_green',
					's' => 'text_green',
					'c' => 'text_green',
					'deny' => 'text_black',
					'x' => 'text_black',
					'Patdec' => 'text_black',
					'PU88935' => 'text_black',
				);

				if(isset($ordersData)) {
					foreach ($ordersData as $odk => $odItem) {
						$rto_action_title = isset($odItem['rto_action_title']) ? $odItem['rto_action_title'] : "";
						$rto_date = (isset($odItem['date']) && !empty($odItem['date'])) ? date('m/d/Y', strtotime($odItem['date'])) : "";
						$rto_status = isset($odItem['rto_status']) ? $odItem['rto_status'] : "";
						$rto_class = (isset($orderStatusListClass[$rto_status])) ? $orderStatusListClass[$rto_status] : '';
						$rto_status_title = isset($odItem['rto_status_title']) ? $odItem['rto_status_title'] : "";
						$tooltip_html = "";

						$patientData = getPatientData($odItem['pid'], "fname, mname, lname, pubpid, billing_note, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
					    $patientName = addslashes(htmlspecialchars($patientData['fname'] . ' ' . $patientData['lname']));
					    $patientDOB = xl('DOB') . ': ' . addslashes(oeFormatShortDate($patientData['DOB_YMD'])) . ' ' . xl('Age') . ': ' . getPatientAge($patientData['DOB_YMD']);
					    $patientPubpid = $patientData['pubpid'];

						if($rto_status_title != "") {
							$tooltip_html .= "<div><span><b>Status</b>: ".$rto_status_title."</span></div>";
						}

						ob_start();
						getRTOSummary($odItem{'id'}, $odItem{'pid'}, $odItem);
						$orderSummaryHtml = ob_get_clean();

						if($orderSummaryHtml != "") {
							$tooltip_html .= "<div><b>Summary</b>: ".$orderSummaryHtml."</div>";
						}

						$fieldHtml[] = "<a href=\"#!\" onclick=\"handleGoToOrder('".$odItem['id']."','".$odItem['pid']."','".$patientPubpid."','".$patientName."','".$patientDOB."')\"><span data-toggle='tooltip' class='$rto_class tooltip_text' title=''>".$rto_action_title." ".$rto_date."<div class='hidden_content'style='display:none;'>".$tooltip_html."</div></span></a>";
					}
					
				}

				if(!empty($fieldHtml)) {
					$fieldHtml = implode(", ", $fieldHtml);
				} else {
					$fieldHtml = "";
				}

				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "areas_of_treatment") {
				$labelList = array(
					'Cervical Rehabilitation' => 'Cervical',
					'Lumbar Rehabilitation' => 'Lumbar',
					'Shoulder' => 'Shoulder',
					'Hand and Wrist' => 'Hand/Wrist',
					'Elbow' => 'Elbow',
					'Knee' => 'Knee',
					'Hip' => 'Hip',
					'Ankle' => 'Ankle'
				);
				$fieldHtml = array();

				//$encData = isset($encDataSet['c'.$row_item['case_id']]) ? $encDataSet['c'.$row_item['case_id']] : array();
				$encData = getEncounterData($row_item['case_id'], array(
								'LBF_rehab',
								'LBF_UErehab',
								'LBF_elbow'
							));

				if(!empty($encData)) {
					foreach ($encData as $enck => $encItem) {
						$grpTitle = isset($encItem['grp_title']) ? $encItem['grp_title'] : '';
						if(isset($labelList[$grpTitle])) {
							if(!in_array($labelList[$grpTitle], $fieldHtml)) {
								$fieldHtml[] = $labelList[$grpTitle];
							}
						}
					}
				}

				if(!empty($fieldHtml)) {
					$fieldHtml = implode(", ", $fieldHtml);
				} else {
					$fieldHtml = "";
				}

				$rowData[$cItem['name']] = (isset($fieldHtml) && !empty($fieldHtml)) ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "lsd_weights") {
				$fieldHtml = array();

				$encData = getEncounterData1($row_item['case_id']);
				if(!empty($encData)) {
					foreach ($encData as $enck => $encItem) {
						$lbfRehabData = getLsdWeights($encItem['form_id']);	
						//$lbfRehabData = getLsdWeights($encItem['formdir'], $encItem['form_id'], $encItem['pid']);

						if(!empty($lbfRehabData)) {
							if(!empty($lbfRehabData['c_low']) || !empty($lbfRehabData['c_high'])) {
								$cervicalLData = isset($lbfRehabData['c_low']) ? $lbfRehabData['c_low'] : '-';
								$cervicalHData = isset($lbfRehabData['c_high']) ? $lbfRehabData['c_high'] : '-';

								$fieldHtml[] = "C$cervicalLData/$cervicalHData";
							}

							if(!empty($lbfRehabData['l_low']) || !empty($lbfRehabData['l_high'])) {
								$cervicalLData = isset($lbfRehabData['l_low']) ? $lbfRehabData['l_low'] : '-';
								$cervicalHData = isset($lbfRehabData['l_high']) ? $lbfRehabData['l_high'] : '-';

								$fieldHtml[] = "L$cervicalLData/$cervicalHData";
							}

							if(!empty($fieldHtml)) {
								break;
							}
						}
					}
				}

				if(!empty($fieldHtml)) {
					$fieldHtml = implode(", ", $fieldHtml);
				} else {
					$fieldHtml = "";
				}

				$rowData[$cItem['name']] = (isset($fieldHtml) && !empty($fieldHtml)) ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "auth_start_date") {
				$fieldHtml = isset($caseData['auth_start_date']) ? $caseData['auth_start_date'] : "";
				$rowData[$cItem['name']] = (isset($fieldHtml) && !empty($fieldHtml)) ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "auth_end_date") {
				$fieldHtml = isset($caseData['auth_end_date']) ? $caseData['auth_end_date'] : "";
				$rowData[$cItem['name']] = (isset($fieldHtml) && !empty($fieldHtml)) ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "auth_num_visit") {
				$fieldHtml = isset($caseData['auth_num_visit']) ? $caseData['auth_num_visit'] : "";
				$rowData[$cItem['name']] = (isset($fieldHtml) && !empty($fieldHtml)) ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "auth_provider") {
				$fieldHtml = "";

				if(isset($caseData['auth_provider'])) {
  					$aplist = sqlQuery("SELECT u.id, concat(u.lname, ', ', u.fname, ' ', u.mname) as full_name FROM users u WHERE id = ? ORDER BY lname", array($caseData['auth_provider']));

  					if(isset($aplist['full_name']) && !empty($aplist['full_name'])) {
  						$fieldHtml = $aplist['full_name'];
  					}
  				}

				$rowData[$cItem['name']] = (isset($fieldHtml) && !empty($fieldHtml)) ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "auth_notes") {
				$fieldHtml = isset($caseData['auth_notes']) ? $caseData['auth_notes'] : "";
				$rowData[$cItem['name']] = (isset($fieldHtml) && !empty($fieldHtml)) ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "authorization_info") {
				$fieldHtml = array();

	  			if(!empty($caseData)) {
	  				$tfieldHtml = [];
	  				if(isset($caseData['auth_start_date']) && !empty($caseData['auth_start_date'])) {
	  					$tfieldHtml[] = "<b>Start Date:</b> " . $caseData['auth_start_date'];
	  				}

	  				if(isset($caseData['auth_end_date']) && !empty($caseData['auth_end_date'])) {
	  					$tfieldHtml[] = "<b>End Date:</b> " . $caseData['auth_end_date'];
	  				}

	  				if(isset($caseData['auth_num_visit']) && !empty($caseData['auth_num_visit'])) {
	  					$tfieldHtml[] = "<b>Number of Visits:</b> " . $caseData['auth_num_visit'];
	  				}

	  				if(isset($caseData['auth_provider'])) {
	  					$aplist = sqlQuery("SELECT u.id, concat(u.lname, ', ', u.fname, ' ', u.mname) as full_name FROM users u WHERE id = ? ORDER BY lname", array($caseData['auth_provider']));

	  					if(isset($aplist['full_name']) && !empty($aplist['full_name'])) {
	  						$tfieldHtml[] = "<b>Authorized Provider:</b> " . $aplist['full_name'];
	  					}
	  				}

	  				if(!empty($tfieldHtml)) {
	  					$fieldHtml[] = implode("\t\t", $tfieldHtml);
	  				}

	  				if(isset($caseData['auth_notes']) && !empty($caseData['auth_notes'])) {
	  					$fieldHtml[] = "<b>Notes:</b> \n" . $caseData['auth_notes'];
	  				}
	  			}

	  			if(!empty($fieldHtml)) {
					$fieldHtml = implode("\n", $fieldHtml);
				} else {
					$fieldHtml = "";
				}

				$rowData[$cItem['name']] = (isset($fieldHtml) && !empty($fieldHtml)) ? getHtmlString($fieldHtml) : "";
				continue;
			}
			
			$rowData[$cItem['name']] = (isset($row_item[$cItem['name']]) && !empty($row_item[$cItem['name']])) ? getHtmlString($row_item[$cItem['name']]) : "";
		}
	}

	return $rowData;
}

//Get DataTable Data
function getDataTableData($data = array(), $columns = array(), $filterVal = array()) {
	extract($data);

	// Search 
	$searchQuery = "";
	if($searchValue != ''){
	   //$searchQuery = " AND (emp_name LIKE ? or email LIKE ? ) ";
	   // $searchArray = array( 
	   //      'emp_name'=>"%$searchValue%", 
	   //      'email'=>"%$searchValue%",
	   //      'city'=>"%$searchValue%"
	   // );
	}

	//Filter Value
	$filterQuery .= generateFilterQuery($filterVal);

	if(!empty($filterQuery)) {
		$searchQuery .= " " . $filterQuery;
	}

	//$sql_data_query = generateSqlQuery("COUNT(*) AS allcount");
	$bindArray = array();

	$records = sqlQuery(generateSqlQuery(array(
		"select" => "COUNT(*) AS allcount",
		"filter_data" => array()
	), true));

	$totalRecords = $records['allcount'];

	$records = sqlQuery(generateSqlQuery(array(
		"select" => "COUNT(*) AS allcount",
		"where" => $searchQuery,
		"filter_data" => $filterVal
	), true));

	$totalRecordwithFilter  = $records['allcount'];

	$result = sqlStatement(generateSqlQuery(array(
		"select" => "ope.pc_eid, cast(concat(ope.pc_eventDate, ' ', ope.pc_startTime) as datetime) as appt_datetime, CONCAT(CONCAT_WS(' ', IF(LENGTH(pd.fname),pd.fname,NULL), IF(LENGTH(pd.lname),pd.lname,NULL)), ' (', pd.pubpid ,')') as patient_name, pd.pid, ope.pc_catid, ope.pc_aid, concat(u.lname, ', ', u.fname) as provider_name, ope.pc_case as case_id, ope.pc_hometext ",
		"where" => $searchQuery,
		"order" => $columnName,
		"order_type" => $columnSortOrder,
		"limit" => $row,
		"offset" => $rowperpage
	)));

	$dataSet = array();
	$rowItems = array();
	$caseIds = array();

	// $rowDataSet = array(
	// 	'case_ids' => array(),
	// 	'enc_data' => array(),
	// 	'layout_option_data' => array(),
	// 	'items' => array()
	// );
	while ($row_item = sqlFetchArray($result)) {
		// if(!in_array($row_item['case_id'], $rowDataSet['case_ids'])) {
		// 	$rowDataSet['case_ids'][] = $row_item['case_id'];
		// }

		// $rowDataSet['items'][] = $row_item;
		//$dataSet[] = prepareDataTableData($row_item, $columns);
		if(!in_array($row_item['case_id'], $caseIds)) {
			$caseIds[] = $row_item['case_id'];
		}
		$rowItems[] = $row_item;
	}

	//Get LBF Data
	$lbfFormDataItems = Caselib::getRehabProgressLBFData($caseIds);

	foreach ($rowItems as $rik => $rItem) {
		if(isset($lbfFormDataItems['case_'.$rItem['case_id']])) {
			$rItem['lbf_data'] = $lbfFormDataItems['case_'.$rItem['case_id']];
		}

		$dataSet[] = prepareDataTableData($rItem, $columns);
	}

	// //$case_data = getOrdesByCase($rowDataSet['case_ids']);
	// $encData = getEncounterData($rowDataSet['case_ids'], array(
	// 	'LBF_rehab',
	// 	'LBF_UErehab',
	// 	'LBF_elbow'
	// ));

	// //Encounter Data
	// foreach ($encData as $encK => $encItem) {
	// 	$caseId = isset($encItem['case_id']) ? $encItem['case_id'] : '';
	// 	$encformdir = isset($encItem['formdir']) ? $encItem['formdir'] : '';
	// 	$encform_id = isset($encItem['form_id']) ? $encItem['form_id'] : '';

	// 	if(empty($caseId)) {
	// 		continue;
	// 	}

	// 	if(!isset($rowDataSet['enc_data']['c'.$caseId])) {
	// 		$rowDataSet['enc_data']['c'.$caseId] = array();
	// 	}

	// 	if(!empty($encformdir)) {
	// 		// if(isset($rowDataSet['layout_option_data'][$encformdir])) {
	// 		// 	$loData = $rowDataSet['layout_option_data'][$encformdir];
	// 		// } else {
	// 		 	$loData = getLayoutOptionsData($encform_id, $encItem['formdir']);
	// 		// }

	// 		// if(!empty($loData)) {
	// 		// 	$fieldIdList = array();

	// 		// 	foreach ($loData as $lokey => $loItem) {
	// 		// 		$fieldIdList[] = $loItem['field_id'];
	// 		// 	}

	// 		// 	$lbffieldsValue = getLBFFieldValue($encform_id, $fieldIdList);
	// 		// 	print_r($lbffieldsValue);
	// 		// }

	// 		$encItem['layout_option_data'] = $loData;	
	// 	}

	// 	$rowDataSet['enc_data']['c'.$caseId][] = $encItem;
	// }

	// foreach ($rowDataSet['items'] as $ik => $iItem) {
	// 	$dataSet[] = prepareDataTableData($iItem, $columns, $rowDataSet);
	// }

	return array(
		"recordsTotal" => $totalRecords,
		"recordsFiltered" => $totalRecordwithFilter,
		"data" => $dataSet
	);
}

if(!empty($page_action)) {
	$response_data = array();
	
	$datatableDataSet = getDataTableData(array(
		'searchValue' => $searchValue,
		'columnName' => $columnName,
		'columnSortOrder' => $columnSortOrder,
		'row' => $row,
		'rowperpage' => $rowperpage
	), $colList, $filterVal);

	$response_data = array(
		"draw" => intval($draw),
	  	"recordsTotal" => $datatableDataSet['recordsTotal'],
	  	"recordsFiltered" => $datatableDataSet['recordsFiltered'],
	  	"data" => $datatableDataSet['data']
	);

	echo json_encode($response_data);
	exit();
}

?>

<html>
<head>
    <title><?php echo xlt('Pre-Visit Planning'); ?></title>
	<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">

	<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui', 'jquery-ui-base', 'datatables', 'datatables-colreorder', 'datatables-bs', 'oemr_ad']); ?>
	<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

	<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js?v=41"></script>

	<style type="text/css">
		/*DataTable Style*/
		table.dataTable {
			font-size: 14px;
			width: calc(100% - 16px);
			position: relative;
		}
		.dataTables_processing {
	        z-index: 1 !important;
	    }
		.dataTable tbody td.no-padding {
			padding: 0;
		}
		.dataTable tr td, .dataTable tr th{
			text-align: left;
		}
        .dataTable .defaultValueText {
        	opacity: 0.3;
        }

        /*Row Details*/
        table.row_details_table {
			table-layout: fixed;
			width: 100%;
			font-size: 14px;
			padding: 8px 10px;
			background-color: #f9f9f9;
		}
		table.row_details_table .case_note_val_container {
			white-space: pre-wrap;
		}

		.datatable_container {
			margin-bottom: 60px;
		}

		.datatable_report .text_green{
			color: green !important;
		}
		.datatable_report .text_red{
			color: red !important;
		}
		.datatable_report .text_black{
			color: #000 !important;
		}

		/*Tooltip Text*/
		.tooltip_text {
			cursor: pointer;
			white-space: pre;
		}
		.uiTooltipContainer {
			opacity: 1 !important;
			font-family: inherit !important;
			font-size: 14px;
		}
		.uiTooltipContent {
		}
		.uiTooltipContent .summeryContainer table tr td {
			font-size: 14px;
			vertical-align: top;
		}

		/*Read More*/
		.textcontentbox {
			white-space: normal!important;
		}
		.textcontentbox input {
    		opacity: 0;
		    position: absolute;
		    pointer-events: none;
		}
		.textcontentbox .content {
		    display: -webkit-box;
		    -webkit-line-clamp: 3;
		    -webkit-box-orient: vertical;  
		    overflow: hidden;
		}
		.textcontentbox input:focus ~ label {
		    outline: -webkit-focus-ring-color auto 5px;
		}
		.textcontentbox input:checked + .content {
		    -webkit-line-clamp: unset;
		} 
		.textcontentbox input:checked ~ label.readmore, 
		.textcontentbox input:not(:checked) ~ label.lessmore {
			display: none;
		}
		.textcontentbox input:checked ~ label.lessmore,
		.textcontentbox input:not(:checked) ~ label.readmore {
			display: inline-block;
		}
		.textcontentbox .content:not(.truncated) ~ label{
		    display: none!important;
		}
		.textcontentbox .content {
		    margin: 0;
		}
		.textcontentbox label {
		    color: #2672ec !important;
		    outline: none !important;
		    cursor: pointer;
		}
		.textcontentbox label:focus {
			outline: none !important;
		}
		.textcontentbox .readmore,
		.textcontentbox .lessmore {
		}
		.checkboxContainer {
			display: grid;
			grid-template-columns: auto 1fr;
			align-items: center;
		}
		.billing_notes {
			height: 90px;
		}
		.text_green{
			color: green !important;
		}
		.text_red{
			color: red !important;
		}
		.text_black{
			color: #000 !important;
		}
	</style>

	<script type="text/javascript">
		$(document).ready(function(){
			$('.date_field').datetimepicker({
	      		<?php $datetimepicker_timepicker = false; ?>
	      		<?php $datetimepicker_showseconds = false; ?>
	      		<?php $datetimepicker_formatInput = true; ?>
	    		<?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
			});
		});
	</script>
</head>
<body class="body_top">
	<div class="page-title">
	    <h2>Pre-Visit Planning</h2>
	</div>

	<div class="dataTables_wrapper datatable_filter">
		<form id="page_report_filter">
			<div class="form-row">
				<div class="col">
					<div class="form-group">
						<label><?php echo xlt('Facility'); ?></label>
						<select name="appt_facility" class="form-control form-control-sm">
							<option value="">Please Select</option>
							<?php
								$qsql = sqlStatement("SELECT id, name FROM facility WHERE service_location != 0");
								while ($facrow = sqlFetchArray($qsql)) {
									echo "<option value='" . attr($facrow['id']) . "'>" . text($facrow['name']) . "</option>";
								}
							?>
						</select>
					</div>
				</div>
				<div class="col">
					<div class="form-group">
						<label><?php echo xlt('Provider'); ?></label>
						<select name="appt_provider" class="form-control form-control-sm">
							<option value="">Please Select</option>
							<?php Caselib::getUsersBy('', '', ' and authorized != 0 ', '', false); ?>
						</select>
					</div>
				</div>
				<div class="col">
					<div class="form-group">
						<label><?php echo xlt('Date'); ?></label>
						<div class="form-row">
			    			<div class="col">
			    				<input type="text" name="appt_date_from" class="date_field form-control form-control-sm" placeholder="From (MM/DD/YY)" value="<?php echo date('m/d/Y'); ?>">
			    			</div>
			    			<div class="col">
			    				<input type="text" name="appt_date_to" class="date_field form-control form-control-sm" placeholder="To (MM/DD/YY)" value="<?php echo date('m/d/Y'); ?>">
				    		</div>
				    	</div>
					</div>
				</div>
			</div>
			<div class="form-row">
				<div class="col">
					<div class="form-group">
						<label><?php echo xlt('Appt Status'); ?></label>
						<select name="appt_status" class="form-control form-control-sm" multiple>
							<option value="">Please Select</option>
							<?php
								$asres = sqlStatement("SELECT * from list_options lo where list_id = 'apptstat'");
								while ($asrow = sqlFetchArray($asres)) {
									echo "<option value='" . attr($asrow['option_id']) . "'>" . text($asrow['title']) . "</option>";
								}
							?>
						</select>
					</div>
				</div>
				<div class="col">
					<div class="form-group">
						<label><?php echo xlt('Appt Category'); ?></label>
						<select name="appt_category" class="form-control form-control-sm" multiple>
							<option value="">Please Select</option>
							<?php
								$cres = sqlStatement("SELECT pc_catid, pc_cattype, pc_catname, pc_recurrtype, pc_duration, pc_end_all_day FROM openemr_postcalendar_categories where pc_active = 1 ORDER BY pc_seq");
								while ($crow = sqlFetchArray($cres)) {
									echo "<option value='" . attr($crow['pc_catid']) . "'>" . text($crow['pc_catname']) . "</option>";
								}
							?>
						</select>
					</div>
				</div>
				<div class="col">
				</div>
			</div>

			<div class="form-row">
				<div class="col">
					<button type="submit" id="filter_submit" class="btn btn-secondary"><?php echo xlt('Submit'); ?></button>
				</div>
			</div>
		</form>
	</div>

	<div id="page_report_container" class="datatable_container table-responsive">
		<table id='page_report' class='text table table-sm datatable_report' style="width:100%;">
		  <thead class="thead-light">
		    <tr>
		      <?php
		      	foreach ($columnList as $clk => $cItem) {
		      		if($cItem["name"] == "dt_control") {
		      		?> <th></th> <?php
		      		} else {
		      		?> <th><?php echo $cItem["title"] ?></th> <?php
		      		}
		      	}
		      ?>
		    </tr>
		  </thead>
		</table>
	</div>

<script type='text/javascript'>
	<?php include($GLOBALS['srcdir'].'/wmt-v2/report_tools.inc.js'); ?>

	function oldEvt(eventid) {
        dlgopen('<?php echo $GLOBALS['webroot']; ?>/interface/main/calendar/add_edit_event.php?eid=' + eventid, 'blank', 775, 500);
    }
</script>

<script type="text/javascript">
	function decodeHtmlString(text) {
	    var map = {
	        '&amp;': '&',
	        '&#038;': "&",
	        '&lt;': '<',
	        '&gt;': '>',
	        '&quot;': '"',
	        '&#039;': "'",
	        '&#8217;': "’",
	        '&#8216;': "‘",
	        '&#8211;': "–",
	        '&#8212;': "—",
	        '&#8230;': "…",
	        '&#8221;': '”'
	    };

	    if(text != "" && text != null) {
	    	text = text.replace(/\\(.)/mg, "$1");
	    	text = text.replace(/\&[\w\d\#]{2,5}\;/g, function(m) { return map[m]; });
	    	return text;
		}

		return text;
	};

	function initTooltip() {
		$('[data-toggle="tooltip"]').tooltip({
        	classes: {
                "ui-tooltip": "ui-corner-all uiTooltipContainer",
                "ui-tooltip-content" : "ui-tooltip-content uiTooltipContent"
            },
            content: function(){
              var element = $( this );
              return element.find('.hidden_content').html();
            },
        	html: true,
        	track: true
        });
	}

	function format(d, columnList = []) {
		var defaultVal = '<i class="defaultValueText">Empty</i>';
		var pc_eid_val = decodeHtmlString(d.pc_eid);
		var appt_comments_val = decodeHtmlString(d.appt_comments);
		var appt_comments_val = decodeHtmlString(d.appt_comments);
		var ins_data_val = decodeHtmlString(d.ins_data);
		//var rehab_plan_val = decodeHtmlString(d.rehab_plan);
		var rehab_progress_val = decodeHtmlString(d.rehab_progress);
		var areas_of_treatment_val = decodeHtmlString(d.areas_of_treatment);
		var lsd_weights_val = decodeHtmlString(d.lsd_weights);
		var orders_val = decodeHtmlString(d.orders);
		var authorization_info_val = decodeHtmlString(d.authorization_info);

		var auth_start_date_val = decodeHtmlString(d.auth_start_date);
		var auth_end_date_val = decodeHtmlString(d.auth_end_date);
		var auth_num_visit_val = decodeHtmlString(d.auth_num_visit);
		var auth_provider_val = decodeHtmlString(d.auth_provider);
		var auth_notes_val = decodeHtmlString(d.auth_notes);

		var authInfoStr1 = '<tr>'+
								'<td width="180" height="10">'+
									'<span>Authorization Start Date :</span>'+
								'</td>'+
								'<td>'+
									'<div>'+(auth_start_date_val != "" ? auth_start_date_val : defaultVal)+'</div>'+
								'</td>'+
								'<td width="200" height="10">'+
									'<span>Authorized Number of Visits :</span>'+
								'</td>'+
								'<td>'+
									'<div>'+(auth_num_visit_val != "" ? auth_num_visit_val : defaultVal)+'</div>'+
								'</td>'+
							'</tr>'+
							'<tr>'+
								'<td width="180" height="10">'+
									'<span>Authorization End Date :</span>'+
								'</td>'+
								'<td>'+
									'<div>'+(auth_end_date_val != "" ? auth_end_date_val : defaultVal)+'</div>'+
								'</td>'+
								'<td width="200" height="10">'+
									'<span>Authorized Provider :</span>'+
								'</td>'+
								'<td>'+
									'<div>'+(auth_provider_val != "" ? auth_provider_val : defaultVal)+'</div>'+
								'</td>'+
							'</tr>'+
							'<tr>'+
								'<td width="180" height="10">'+
									'<span>Authorization Notes :</span>'+
								'</td>'+
								'<td colspan="3">'+
									'<div class="textcontentbox">'+
										'<input type="checkbox" id="expanded_nt_'+pc_eid_val+'">'+
										'<div class="content case_note_val_container">'+(auth_notes_val != "" ? auth_notes_val : defaultVal)+'</div>'+
										'<label for="expanded_nt_'+pc_eid_val+'" class="readmore" role="button">Read More</label>'+
										'<label for="expanded_nt_'+pc_eid_val+'" class="lessmore" role="button">Read Less</label>'+
									'</div>'+
								'</td>'+
								
							'</tr>';

		var authInfoStr2 = '<tr>'+
								'<td width="180" height="10">'+
									'<span>Authorization Info :</span>'+
								'</td>'+
								'<td colspan="3">'+
									'<div>'+defaultVal+'</div>'+
								'</td>'+
							'</tr>';							

		var authInfoHtml = authorization_info_val != "" ? authInfoStr1 : authInfoStr2;

		return '<div><table class="row_details_table text table table-sm table-borderless mb-0"><tbody>'+
					'<tr>'+
						'<td width="180" height="10">'+
							'<span>Insurances:</span>'+
						'</td>'+
						'<td>'+
							'<div>'+(ins_data_val != "" ? ins_data_val : defaultVal) +'</div>'+
						'</td>'+
						'<td width="200" height="10">'+
							'<span>Rehab Progress:</span>'+
						'</td>'+
						'<td>'+
							'<div>'+(rehab_progress_val != "" ? rehab_progress_val : defaultVal) +'</div>'+
						'</td>'+
					'</tr>'+
					'<tr>'+
						'<td width="180" height="10">'+
							'<span>Areas of Treatment:</span>'+
						'</td>'+
						'<td>'+
							'<div>'+(areas_of_treatment_val != "" ? areas_of_treatment_val : defaultVal) +'</div>'+
						'</td>'+
						'<td width="200" height="10">'+
							'<span>Last Spinal Decompression weights:</span>'+
						'</td>'+
						'<td>'+
							'<div>'+(lsd_weights_val != "" ? lsd_weights_val : defaultVal) +'</div>'+
						'</td>'+
					'</tr>'+
					'<tr>'+
						'<td width="180" height="10">'+
							'<span>Orders :</span>'+
						'</td>'+
						'<td>'+
							'<div>'+(orders_val != "" ? orders_val : defaultVal) +'</div>'+
						'</td>'+
						'<td width="200" height="10">'+
							'<span>Appointment Comments:</span>'+
						'</td>'+
						'<td>'+
							'<div>'+(appt_comments_val != "" ? appt_comments_val : defaultVal) +'</div>'+
						'</td>'+
					'</tr>'+
					authInfoHtml+
			    '</tbody></table></div>';
	}

	function initDataTable(id, ajax_url = '', data = {}, columnList = []) {
		var colummsData = JSON.parse(columnList);
		var columns = []; 
		colummsData.forEach((item, index) => {
			if(item["name"]) {
				var item_data = item["data"] ? item["data"] : {};

				if(item["name"] == "dt_control") { 
					columns.push({ 
						"data" : "",
						...item_data
					});
				} else {
					columns.push({ 
						"data" : item["name"],
						...item_data,
						"render" : function(data, type, row ) {
							var defaultVal = item_data['defaultValue'] ? decodeHtmlString(item_data['defaultValue']) : "";
							var colValue = decodeHtmlString(data);

							return (colValue && colValue != "") ? colValue : defaultVal;
						} 
					});
				}
			}
		});

		data["columnList"] = colummsData;

		if(id && id != "" && ajax_url != '' && data) {
			var dTable = $(id).DataTable({
					"processing": true,
			       	"serverSide": true,
			         "ajax":{
			             url: ajax_url, // json datasource
			             data: function(adata) {

			             		for (let key in data) {
			             			adata[key] = data[key];
			             		}

			             		//Append Filter Value
			             		adata['filterVal'] = getFilterValues(id + "_filter");
			             },
			             type: "POST",   // connection method (default: GET)
			             
			        },
			        "columns": columns,
			        "columnDefs": [
				        { 
				        	"targets": '_all', 
				        	"render" : function ( data, type, row ) {
				        		return data;
			                },
			                
				        },
				    ],
			        "searching" : false,
			        "order": [[ 1, "asc" ]],
			        "iDisplayLength" : 100,
			        "deferLoading" : 0,
			});

			$(id).on( 'processing.dt', function ( e, settings, processing ) {
				if(processing === true) {
					$('#filter_submit').prop('disabled', true);
				} else if(processing === false) {
					$('#filter_submit').prop('disabled', false);
				}
			});

			$(id).on('draw.dt', function () {
	            
	            //Init Tooltip
	            initTooltip();

	            //Expand Row Details
	            dTable.rows().every( function () {
	            	var tr = $(this.node());
	            	var row = dTable.row( tr );
	            	var childTrClass = tr.hasClass('even') ? 'even' : 'odd';
	            	row.child(format(row.data()), 'no-padding row-details-tr bg-light ').show();
		            tr.addClass('shown').trigger('classChange');
		            //$('.dt-control-all').closest('tr').addClass('shown');
		            initTooltip();
	            });

	            //Check Is There any Read More Content
	            const ps = document.querySelectorAll('.textcontentbox .content');
					ps.forEach(p => {
					  if(Math.ceil(p.scrollHeight) > Math.ceil(p.offsetHeight)) {
					  	p.classList.add("truncated");
					  } else {
					  	p.classList.remove("truncated");
					  }
					});
		        });

	        $(id+' tbody').on('classChange', function() {
			    var isShown = $(id+' tbody tr.shown').length;
			    var tr = $(id+' thead tr th.dt-control').closest('tr');

			    if(isShown > 0) {
			    	tr.addClass('shown');
			    } else {
			    	tr.removeClass('shown');
			    }
			});

	        // Add event listener for opening and closing details
		    $(id+' tbody').on('click', 'td.dt-control', function () {
		        var tr = $(this).closest('tr');
		        var row = dTable.row( tr );
		 
		        if ( row.child.isShown() ) {
		            // This row is already open - close it
		            row.child.hide();
		            tr.removeClass('shown').trigger('classChange');
		        }
		        else {
		            // Open this row
		            var childTrClass = tr.hasClass('even') ? 'even' : 'odd';
	            	row.child(format(row.data()), 'no-padding row-details-tr bg-light ').show();
		            tr.addClass('shown').trigger('classChange');
		            initTooltip();
		        }
		    });

		    $(id+' thead').on('click', 'th.dt-control', function () {
		    	var tr = $(this).closest('tr');

		    	if(tr.hasClass( "shown" )) {
		    		//UnExpand Row Details
		    		dTable.rows().every( function () {
		            	var tr = $(this.node());
		            	var row = dTable.row( tr );
		            	row.child.hide();
		            	tr.removeClass('shown').trigger('classChange');
		            });
		    	} else {
		    		//Expand Row Details
		    		dTable.rows().every( function () {
		            	var tr = $(this.node());
		            	var row = dTable.row( tr );
		            	var childTrClass = tr.hasClass('even') ? 'even' : 'odd';
		            	row.child(format(row.data()), 'no-padding row-details-tr bg-light ').show();
			            tr.addClass('shown').trigger('classChange');
			            //$('.dt-control-all').closest('tr').addClass('shown');
			            initTooltip();
		            });
		    	}
		    });

			return dTable;
		}

		return false;
	}

	function getFilterValues(id = '') {
		var form_val_array = {};

		if(id != '') {
			var unindexed_array = $(id).serializeArray();
			var indexed_array = {};
		    $.map(unindexed_array, function(n, i){
		        if((n['name'] == "appt_status" || n['name'] == "appt_category") && n['value'] != "") {
		        	if(!indexed_array[n['name']]) {
		        		indexed_array[n['name']] = [];
		        	}

		        	indexed_array[n['name']].push(n['value']);
		        } else {
		        	indexed_array[n['name']] = n['value'];
		    	}
		    });

		    $.map(indexed_array, function(ni, ii){
		    	if(ni != "") {
		    		form_val_array[ii] = ni;
		    	}
		    });
		}

		return form_val_array;
	}

	function validateForm() {
		var apptFacility = document.querySelector('select[name="appt_facility"]').value;
		var apptProvider = document.querySelector('select[name="appt_provider"]').value;

		if(apptFacility == '' && apptProvider == '') {
			alert('Please select facility or provider.');
			return false;
		}

		return true;
	}

	$(function () {
		var dataTableId = "#page_report";
		var dataTableFilterId = "#page_report_filter";

		//$('#filter_submit').prop('disabled', true);
		var dataTable = initDataTable(
			dataTableId, 
			'pre_visit_planning_report.php', 
			{ action: 'fetch_data' },
			'<?php echo json_encode($columnList); ?>'
		);

		$(dataTableFilterId).submit(function(e){
            e.preventDefault();

            var vStatus = validateForm();

            if(vStatus === true) {
            	dataTable.draw();
        	}
        });
	});
</script>

</body>
</html>
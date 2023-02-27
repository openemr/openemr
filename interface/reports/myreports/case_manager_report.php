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
		"name" => "case_id",
		"title" => "Case Number",
		"data" => array(
			"width" => "120"
		)
	),
	array(
		"name" => "first_visit_date",
		"title" => "Date 1st visit",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "120"
		)	
	),
	array(
		"name" => "injury_date",
		"title" => "Date of injury",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "120"
		)
	),
	array(
		"name" => "patient_name",
		"title" => "Patient Name",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
		)
	),
	array(
		"name" => "law_firm",
		"title" => "Law firm",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "visible" => false,
            "orderable" => false,
            "width" => "0"
		)
	),
	array(
		"name" => "next_appt",
		"title" => "Next Appts",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "visible" => false,
            "orderable" => false,
            "width" => "0"
		)
	),
	array(
		"name" => "prev_canceled_appt",
		"title" => "Canceled Appts",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "visible" => false,
            "orderable" => false,
            "width" => "0"
		)
	),
	array(
		"name" => "medical",
		"title" => "Medical?",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "orderable" => false,
            "width" => "100"
		)
	),
	array(
		"name" => "x_ray",
		"title" => "X-Ray?",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "orderable" => false,
            "width" => "60"
		)
	),
	array(
		"name" => "tens",
		"title" => "TENS?",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "orderable" => false,
            "width" => "60"
		)
	),
	array(
		"name" => "rehab_plan",
		"title" => "Rehab Plan",
		"data" => array(
			"visible" => false,
			"defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
			"orderable" => false,
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
		"name" => "orders",
		"title" => "Orders",
		"data" => array(
			"visible" => false,
			"defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
			"orderable" => false,
			"width" => "0"
		)
	),
	array(
		"name" => "case_note",
		"title" => "Case Note",
		"data" => array(
			"visible" => false,
			"defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
			"orderable" => false,
			"width" => "0"
		)
	),
	array(
		"name" => "threshold_15k",
		"title" => "$15k Threshold",
		"data" => array(
			"visible" => false,
			"defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
			"orderable" => false,
			"width" => "0"
		)
	)
);

function getHtmlString($text) {
	return addslashes(htmlspecialchars($text));
}

function getXRayCountByCase($case_id = array()) {
	$dataSet = array();
	$case_id_str = "'".implode("','",$case_id)."'";

	if(!empty($case_id)) {
		$esql = sqlStatement("SELECT count(cal.encounter) as total_count, cal.enc_case as case_id from case_appointment_link cal left join form_encounter fe on fe.encounter = cal.encounter left join billing b on b.encounter = fe.encounter where b.code_type = 'CPT4' and b.code like '7%' and cal.enc_case IN (".$case_id_str.") group by cal.enc_case");

		while ($enrow = sqlFetchArray($esql)) {
			$dataSet['case_'.$enrow['case_id']] = $enrow;
		}
	}

	return $dataSet;
}

function getCodeCountData($case_id = array()) {
	$dataSet = array();
	$case_id_str = "'".implode("','",$case_id)."'";

	if(!empty($case_id)) {
		$esql = sqlStatement("SELECT count(cal.encounter) as total_count, cal.enc_case as case_id, b.code_type from case_appointment_link cal left join form_encounter fe on fe.encounter = cal.encounter left join billing b on b.encounter = fe.encounter where ((b.code_type = 'CPT4' and b.code like '7%') or (b.code_type = 'HCPCS' and b.code = 'E0720')) and cal.enc_case IN (".$case_id_str.") and b.activity = 1 group by cal.enc_case, b.code_type");

		while ($enrow = sqlFetchArray($esql)) {
			if(!isset($dataSet['case_'.$enrow['case_id']])) {
				$dataSet['case_'.$enrow['case_id']] = array();
			}

			$dataSet['case_'.$enrow['case_id']][] = $enrow;
		}
	}

	return $dataSet;
}

function getEncounterLBFData1($case_id = array()) {
	$dataSet = array();
	$case_id_str = "'".implode("','",$case_id)."'";

	if(!empty($case_id)) {
		$esql = sqlStatement("SELECT CONCAT('{',GROUP_CONCAT('\"enc_', a1.encounter, '\": ',a1.json_data), '}') as json_data, a1.case_id, a1.encounter FROM (SELECT CONCAT('{',GROUP_CONCAT('\"form_', a2.form_id, '\": ' , CONCAT('[', a2.json_data), ']'), '}') as json_data, a2.case_id, a2.encounter FROM (SELECT GROUP_CONCAT(JSON_OBJECT('form_id', ld.form_id,'field_id', ld.field_id,'field_value', ld.field_value,'field_title', lo.title,'form_dir', lo.form_id,'grp_title', lgp.grp_title,'encounter', fe.encounter,'enc_case', cal.enc_case)) AS json_data, cal.enc_case AS case_id, fe.encounter, ld.form_id FROM case_appointment_link cal,form_encounter fe, forms f, lbf_data ld, layout_options lo, layout_group_properties lgp WHERE fe.encounter = cal.encounter AND f.encounter = fe.encounter AND ld.form_id = f.form_id AND lo.field_id = ld.field_id AND lo.form_id = f.formdir AND lgp.grp_form_id = lo.form_id and lgp.grp_group_id = lo.group_id AND f.formdir IN ('LBF_rehab', 'LBF_UErehab', 'LBF_elbow') AND cal.enc_case IN (".$case_id_str.") AND f.deleted = 0 GROUP BY ld.form_id, cal.enc_case) AS a2 GROUP BY a2.encounter) AS a1 GROUP BY a1.case_id");

		while ($enrow = sqlFetchArray($esql)) {
			if(!empty($enrow['json_data'])) {
				$enrow['json_data'] = json_decode($enrow['json_data'], true);
			}

			$dataSet['case_'.$enrow['case_id']] = $enrow;
		}
	}

	return $dataSet;
}

function getEncounterLBFData2($case_id = array()) {
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

function getEncounterLBFData($case_id) {
	$dataSet = array();

	if(!empty($case_id)) {
		$esql = sqlStatement("SELECT fe.id as enc_id, fe.encounter, f.pid, f.form_name, f.form_id, f.formdir FROM case_appointment_link cal,form_encounter fe ,forms f WHERE fe.encounter = cal.encounter and f.encounter = fe.encounter and cal.enc_case = ? AND f.formdir IN ('LBF_rehab', 'LBF_UErehab', 'LBF_elbow') and f.deleted = 0 order by fe.`date` desc", array($case_id));

		while ($enrow = sqlFetchArray($esql)) {
			$dataSet[] = $enrow;
		}
	}

	return $dataSet;
}

function getLBFRehabCount1($formItem) {

	$fieldList = array(
		'Lumbar SD Time',
		'Lumbar SD Low Limit',
		'Lumbar SD High Limit'
	);

	$fieldList1 = array(
		'Cervical SD Time',
		'Cervical SD Low Limit',
		'Cervical SD High Limit'
	);
	
	$dataSet = array(
		'PT' => 0,
		'LD' => 0,
		'CD' => 0,
		'DD' => 0
	);

	$cdStatus = false;
	$ldStatus = false;

	foreach ($formItem as $fkey => $frow1) {
		if(isset($frow1['field_value']) && !empty($frow1['field_value'])) {
			if($frow1['form_dir'] == "LBF_rehab") {
				if($frow1['grp_title'] == "Lumbar Rehabilitation" || $frow1['grp_title'] == "Cervical Rehabilitation") {
					$dataSet['PT'] = 1;
				}
			} 
			if($frow1['form_dir'] == "LBF_UErehab") {
				$dataSet['PT'] = 1;
			} 
			if($frow1['form_dir'] == "LBF_elbow") {
				$dataSet['PT'] = 1;
			}

			if($frow1['form_dir'] == "LBF_rehab") {
				if($frow1['grp_title'] == "Spinal Decompression") {
					if(isset($frow1['field_title']) && in_array($frow1['field_title'], $fieldList)) {
						$ldStatus = true;
					} else if(isset($frow1['field_title']) && in_array($frow1['field_title'], $fieldList1)) {
						$cdStatus = true;
					}
				}
			}
		}
	}

	if($cdStatus === true && $ldStatus === false) {
		$dataSet['CD'] = 1;
	} else if($cdStatus === false && $ldStatus === true) {
		$dataSet['LD'] = 1;
	} else if($cdStatus === true && $ldStatus === true) {
		$dataSet['DD'] = 1;
	}

	return $dataSet;
}

function getLBFRehabCount($formid) {
	$fres = sqlStatement("SELECT ld.*, lo.title as field_title, lo2.title as op_title, lo.form_id, lgp.grp_title FROM lbf_data ld JOIN layout_options lo JOIN layout_group_properties lgp LEFT join list_options lo2 on lo2.option_id = ld.field_value WHERE ld.form_id = ? AND lo.field_id = ld.field_id and lgp.grp_form_id = lo.form_id and lgp.grp_group_id = lo.group_id", array($formid));

	$fieldList = array(
		'Lumbar SD Time',
		'Lumbar SD Low Limit',
		'Lumbar SD High Limit'
	);

	$fieldList1 = array(
		'Cervical SD Time',
		'Cervical SD Low Limit',
		'Cervical SD High Limit'
	);
	
	$dataSet = array(
		'PT' => 0,
		'LD' => 0,
		'CD' => 0,
		'DD' => 0
	);

	$cdStatus = false;
	$ldStatus = false;

	while ($frow1 = sqlFetchArray($fres)) {
		if(isset($frow1['field_value']) && !empty($frow1['field_value'])) {
			if($frow1['form_id'] == "LBF_rehab") {
				if($frow1['grp_title'] == "Lumbar Rehabilitation" || $frow1['grp_title'] == "Cervical Rehabilitation") {
					$dataSet['PT'] = 1;
				}
			} 
			if($frow1['form_id'] == "LBF_UErehab") {
				$dataSet['PT'] = 1;
			} 
			if($frow1['form_id'] == "LBF_elbow") {
				$dataSet['PT'] = 1;
			}

			if($frow1['form_id'] == "LBF_rehab") {
				if($frow1['grp_title'] == "Spinal Decompression") {
					if(isset($frow1['field_title']) && in_array($frow1['field_title'], $fieldList)) {
						$ldStatus = true;
					} else if(isset($frow1['field_title']) && in_array($frow1['field_title'], $fieldList1)) {
						$cdStatus = true;
					}
				}
			}
		}
	}

	if($cdStatus === true && $ldStatus === false) {
		$dataSet['CD'] = 1;
	} else if($cdStatus === false && $ldStatus === true) {
		$dataSet['LD'] = 1;
	} else if($cdStatus === true && $ldStatus === true) {
		$dataSet['DD'] = 1;
	}

	return $dataSet;
}

//Filter Query Data
function generateFilterQuery($filterData = array()) {
	$filterQryList = array();
	$filterQry = "";

	if(!empty($filterData)) {
		if(isset($filterData['closed'])) {
			$filterQryList[] = "fc.closed = " . (intval($filterData['closed']) ? 0 : 1);
		}

		if(isset($filterData['case_manager']) && !empty($filterData['case_manager'])) {
			if($filterData['case_manager'] == 'blank') {
				//$filterQryList[] = "(fc.liability_payer_exists = 1 and length(trim(coalesce(vpcmd.field_value,'')))=0)";
				$filterQryList[] = "if(((ic1.ins_type_code in ('20', '21', '16') OR ic2.ins_type_code in ('20', '21', '16') OR ic3.ins_type_code in ('20', '21', '16')) and length(trim(coalesce(vpcmd.field_value,'')))=0), 1, 0) = 1";
			} else {
				$filterQryList[] = "vpcmd.field_value = '" . $filterData['case_manager'] . "'";
			}
		}

		if(isset($filterData['chart_number']) && !empty($filterData['chart_number'])) {
			$filterQryList[] = "pd.pubpid = '" . $filterData['chart_number'] . "'";
		}

		if(isset($filterData['law_firm']) && !empty($filterData['law_firm'])) {
			$filterQryList[] = "(id1.provider = '". $filterData['law_firm'] ."' or id2.provider = '". $filterData['law_firm'] ."' or id3.provider = '". $filterData['law_firm'] ."')";
		}

		if(isset($filterData['date_of_injury_from']) && !empty($filterData['date_of_injury_from']) && isset($filterData['date_of_injury_to']) && !empty($filterData['date_of_injury_to'])) {
			$filterData['date_of_injury_from'] = date('Y/m/d', strtotime($filterData['date_of_injury_from']));
			$filterData['date_of_injury_to'] = date('Y/m/d', strtotime($filterData['date_of_injury_to']));
			
			$filterQryList[] = "(fc.injury_date IS NOT null and fc.injury_date != '' and date(fc.injury_date) between '".$filterData['date_of_injury_from']."' and '".$filterData['date_of_injury_to']."')";
		}

		// if(isset($filterData['date_of_injury_from']) && !empty($filterData['date_of_injury_from']) && isset($filterData['date_of_injury_to']) && !empty($filterData['date_of_injury_to'])) {
		// 	$filterData['date_of_injury_from'] = date('Y/m/d', strtotime($filterData['date_of_injury_from']));
		// 	$filterData['date_of_injury_to'] = date('Y/m/d', strtotime($filterData['date_of_injury_to']));
			
		// 	$filterQryList[] = "(fc.injury_date IS NOT null and fc.injury_date != '' and date(fc.injury_date) between '".$filterData['date_of_injury_from']."' and '".$filterData['date_of_injury_to']."')";
		// }

		if(isset($filterData['date_of_first_visit_from']) && !empty($filterData['date_of_first_visit_from']) && isset($filterData['date_of_first_visit_to']) && !empty($filterData['date_of_first_visit_to'])) {
			$filterData['date_of_first_visit_from'] = date('Y/m/d', strtotime($filterData['date_of_first_visit_from']));
			$filterData['date_of_first_visit_to'] = date('Y/m/d', strtotime($filterData['date_of_first_visit_to']));
			
			$filterQryList[] = "(date((select min(ope.pc_eventDate) from openemr_postcalendar_events ope where ope.pc_case = fc.id and ope.pc_apptstatus not in ('-','+','?','x', '%') and ope.pc_pid = fc.pid)) between '".$filterData['date_of_first_visit_from']."' and '".$filterData['date_of_first_visit_to']."')";
		}

		if(isset($filterData['patient_with_no_future_app_from']) && $filterData['patient_with_no_future_app_from'] == '1') {
			$filterQryList[] = "if(exists(select 1 from openemr_postcalendar_events ope where ope.pc_pid = fc.pid and ope.pc_case = fc.id and ope.pc_apptstatus not in ('?','x','%') and TIMESTAMP(ope.pc_eventDate, ope.pc_startTime) > now() order by ope.pc_eid asc), 1, 0) = 0";
		}

		if(!empty($filterQryList)) {
			$filterQry = implode(" and ", $filterQryList);
		}
	}

	return $filterQry;
}

//Prepare Data Table Data
function prepareDataTableData($row_item = array(), $columns = array()) {
	$rowData = array();

	$caseManagerData = Caselib::piCaseManagerFormData($row_item['case_id'], '');
	$isPiCaseLiable = Caselib::isLiablePiCaseByCase($row_item['case_id'], $row_item['pid'], $row_item);

	foreach ($columns as $clk => $cItem) {
		if(isset($cItem['name'])) {
			if($cItem['name'] == "case_id") {
				$fieldHtml = "<a href=\"#!\" onclick=\"handlegotoCase('".$row_item['case_id']."','".$row_item['pid']."');\">". $row_item[$cItem['name']] . "</a>";
				$rowData[$cItem['name']] = (isset($row_item[$cItem['name']]) && !empty($row_item[$cItem['name']])) ? getHtmlString($fieldHtml) : "-";
				continue;
			} else if($cItem['name'] == "patient_name") {
				$bcNotesStatusListClass = array(
					'reque_22144' => 'text_red',
					'affys_only' => 'text_red',
					'stil_treat' => 'text_red',
					'mis_note' => 'text_red',
					'pend_pymt' => 'text_red',
					'in_audit' => 'text_red',
					'Sent113' => 'text_green',
					'Updated' => 'text_green',
					'Interim' => 'text_green'
				);

				$bc_notes_val = isset($row_item['bc_notes']) ? $row_item['bc_notes'] : "";
				$tooltip_html = "";
				$req_class = "text_blue";

				if(!empty($bc_notes_val)) {
					$nq_filter = ' AND option_id = "'.$bc_notes_val.'"';
					$listOptions = LoadList('Case_Billing_Notes', 'active', 'seq', '', $nq_filter);
					$req_class = (isset($bcNotesStatusListClass[$bc_notes_val])) ? $bcNotesStatusListClass[$bc_notes_val] : 'text_blue';

					if(!empty($listOptions)) {
						$bc_option_title = $listOptions[0] && isset($listOptions[0]['title']) ? $listOptions[0]['title'] : "";
						$tooltip_html = $bc_option_title;

						// if($bc_option_title == "Requested") {
						// 	$tooltip_html = "<div><span>Bills/Records Requested</span></div>";
						// }
					}
				}

				if(!empty($tooltip_html)) {
					$fieldHtml = "<a href=\"#!\" class='$req_class' onclick=\"goParentPid('".$row_item['pid']."');\"><span data-toggle='tooltip' class='tooltip_text $req_class' title=''>". $row_item[$cItem['name']] . "<div class='hidden_content'style='display:none;'>".$tooltip_html."</div></span></a>";
				} else {
					$fieldHtml = "<a href=\"#!\" class='linktext $req_class' onclick=\"goParentPid('".$row_item['pid']."');\">". $row_item[$cItem['name']] . "</a>";
				}
				
				$rowData[$cItem['name']] = (isset($row_item[$cItem['name']]) && !empty($row_item[$cItem['name']])) ? getHtmlString($fieldHtml) : "-";
				continue;
			} else if($cItem['name'] == "law_firm") {
				$insIds = array();
				$fieldHtml = array();

				for ($ins_i=1; $ins_i <= 3; $ins_i++) { 
					if(isset($row_item['ins_data_id'.$ins_i]) && $row_item['ins_data_id'.$ins_i] != "") {
						$insIds[] = $row_item['ins_data_id'.$ins_i];
					}
				}

				$liableInsList = Caselib::getLiableInsData($insIds, $row_item['pid']);

				if(isset($liableInsList)) {
					foreach ($liableInsList as $lk => $lItem) {
						$fieldHtml[] = "<span>".$lItem['name']."</span>";
					}
				}

				if(!empty($fieldHtml)) {
					//$fieldHtml = "<ul>" . $fieldHtml . "</ul>";
					$fieldHtml = implode(", ", $fieldHtml);
				} else {
					$fieldHtml = "";
				}

				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "next_appt") {
				$fieldHtml = array();

				$nextAppts = Caselib::getFutureAppt($row_item['case_id'], $row_item['pid']);

				if(isset($nextAppts)) {
					foreach ($nextAppts as $nak => $naItem) {
						$next_appt_time = isset($naItem['event_date_time']) ? date('m/d',strtotime($naItem['event_date_time'])) : "";
						$next_appt_provider_name = "";

						if(isset($naItem['provider_fname']) && !empty($naItem['provider_fname'])) {
							$next_appt_provider_name .= ucfirst(substr($naItem['provider_fname'], 0, 1));
						}

						if(isset($naItem['provider_mname']) && !empty($naItem['provider_mname'])) {
							$next_appt_provider_name .= ucfirst(substr($naItem['provider_mname'], 0, 1));
						}

						if(isset($naItem['provider_lname']) && !empty($naItem['provider_lname'])) {
							$next_appt_provider_name .= ucfirst(substr($naItem['provider_lname'], 0, 1));
						}

						$fieldHtml[] = "<a href=\"#!\" onclick=\"oldEvt('".$naItem['pc_eid']."');\">".$next_appt_provider_name. " " . $next_appt_time ."</a>";
					}
				}

				if(!empty($fieldHtml)) {
					$fieldHtml = implode(", ", $fieldHtml);
				} else {
					$fieldHtml = "";
				}

				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "prev_canceled_appt") {

				$fieldHtml = array();

				$prevCanceledAppts = Caselib::getPreviousCanceledAppt($row_item['case_id'], $row_item['pid']);

				if(isset($prevCanceledAppts)) {
					foreach ($prevCanceledAppts as $nak => $naItem) {
						$prev_appt_time = isset($naItem['event_date_time']) ? date('m/d',strtotime($naItem['event_date_time'])) : "";
						$prev_appt_provider_name = "";
						$tooltip_html = "";

						if(isset($naItem['provider_fname']) && !empty($naItem['provider_fname'])) {
							$prev_appt_provider_name .= ucfirst(substr($naItem['provider_fname'], 0, 1));
						}

						if(isset($naItem['provider_mname']) && !empty($naItem['provider_mname'])) {
							$prev_appt_provider_name .= ucfirst(substr($naItem['provider_mname'], 0, 1));
						}

						if(isset($naItem['provider_lname']) && !empty($naItem['provider_lname'])) {
							$prev_appt_provider_name .= ucfirst(substr($naItem['provider_lname'], 0, 1));
						}

						$apptStatus = "";
						if(isset($naItem['pc_apptstatus']) && !empty($naItem['pc_apptstatus'])) {
							$apptStatus = Caselib::ListLook($naItem['pc_apptstatus'],'apptstat');
						}

						$tooltip_html .= "<div><span><b>Status</b>: ".$apptStatus."</span></div>";

						$fieldHtml[] = "<a href=\"#!\" onclick=\"oldEvt('".$naItem['pc_eid']."');\"><span data-toggle='tooltip' class='tooltip_text' title=''>".$prev_appt_provider_name. " " . $prev_appt_time ."<div class='hidden_content'style='display:none;'>".$tooltip_html."</div></span></a>";
					}
				}

				if(!empty($fieldHtml)) {
					$fieldHtml = implode(", ", $fieldHtml);
				} else {
					$fieldHtml = "";
				}

				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "medical") {
				$fieldHtml = "";

				$medicalData = Caselib::getMedicalDataOfCase($row_item['case_id']);

				if(!empty($medicalData)) {
					$next_appt_time = isset($medicalData['event_date_time']) ? date('m/d',strtotime($medicalData['event_date_time'])) : "";
					$next_appt_provider_name = "";

					if(isset($medicalData['provider_fname']) && !empty($medicalData['provider_fname'])) {
						$next_appt_provider_name .= ucfirst(substr($medicalData['provider_fname'], 0, 1));
					}

					if(isset($medicalData['provider_mname']) && !empty($medicalData['provider_mname'])) {
						$next_appt_provider_name .= ucfirst(substr($medicalData['provider_mname'], 0, 1));
					}

					if(isset($medicalData['provider_lname']) && !empty($medicalData['provider_lname'])) {
						$next_appt_provider_name .= ucfirst(substr($medicalData['provider_lname'], 0, 1));
					}

					$fieldHtml = "<a href=\"#!\" onclick=\"oldEvt('".$medicalData['pc_eid']."');\">".$next_appt_provider_name. " " . $next_appt_time ."</a>";
				} else {
					$fieldHtml = 'N';
				}

				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "x_ray") {
				$fieldHtml = "";

				$isApptsAvailable = isset($row_item['xray_data']) ? $row_item['xray_data'] : array('total_count' => 0);
				//$isApptsAvailable = Caselib::getXRayCountByCase($row_item['case_id']);
				$fieldHtml = ($isApptsAvailable !== false && $isApptsAvailable['total_count'] > 0) ? 'Y' : 'N';
				
				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "tens") {
				$fieldHtml = "";

				$isApptsAvailable = isset($row_item['tens_data']) ? $row_item['tens_data'] : array('total_count' => 0);
				//$isApptsAvailable = Caselib::getTensCountByCase($row_item['case_id']);
				$fieldHtml = ($isApptsAvailable !== false && $isApptsAvailable['total_count'] > 0) ? 'Y' : 'N';
				
				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "rehab_plan") {
				$fieldHtml = "";

				if($caseManagerData && $isPiCaseLiable === true) {
					//$caseManagerData = Caselib::piCaseManagerFormData($row_item['case_id'], '');
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
			} else if($cItem['name'] == "case_note") { 
				$fieldHtml = isset($row_item['comments']) ? $row_item['comments'] : "";

				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? $fieldHtml : "";
				continue;
			} else if($cItem['name'] == "threshold_15k") { 
				$data15k = Caselib::get15kThresholdData($row_item['case_id']);
				$fieldHtml = isset($data15k['reported_date_whencrossed15k']) ? $data15k['reported_date_whencrossed15k'] : "";

				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? $fieldHtml : "";
				continue;
			}
			
			$rowData[$cItem['name']] = (isset($row_item[$cItem['name']]) && !empty($row_item[$cItem['name']])) ? getHtmlString($row_item[$cItem['name']]) : "";
		}
	}

	return $rowData;
}

//Generate Query
function generateCaseQuery($data = array(), $isSearch = false) {
	$select_qry = isset($data['select']) ? $data['select'] : "*";
	$where_qry = isset($data['where']) ? $data['where'] : "";
	$order_qry = isset($data['order']) ? $data['order'] : "fc.id"; 
	$order_type_qry = isset($data['order_type']) ? $data['order_type'] : "desc";

	$limit_qry = isset($data['limit']) ? $data['limit'] : ""; 
	$offset_qry = isset($data['offset']) ? $data['offset'] : "asc";

	$sql = "SELECT $select_qry from form_cases fc";

	$patient_data_join = " left join patient_data pd on pd.pid = fc.pid";
	$pi_case_join = " left join vh_pi_case_management_details vpcmd on vpcmd.case_id = fc.id and vpcmd.field_name = 'case_manager' and vpcmd.field_index = 0";
	$ins_data_join = " left join insurance_data id1 on id1.id = fc.ins_data_id1 left join insurance_data id2 on id2.id = fc.ins_data_id2 left join insurance_data id3 on id3.id = fc.ins_data_id3";
	$ins_company_data_join = " left join insurance_companies ic1 on ic1.id = id1.provider left join insurance_companies ic2 on ic2.id = id2.provider left join insurance_companies ic3 on ic3.id = id2.provider";

	if(isset($data['filter_data'])) {
		$filter_data = isset($data['filter_data']) ? $data['filter_data'] : array();

		if(isset($filter_data['case_manager']) && !empty($filter_data['case_manager'])) {
			$sql .= $pi_case_join;
		}

		if(isset($filter_data['chart_number']) && !empty($filter_data['chart_number'])) {
			$sql .= $patient_data_join;
		}

		if((isset($filter_data['law_firm']) && !empty($filter_data['law_firm'])) || ($filter_data['case_manager'] == 'blank')) {
			$sql .= $ins_data_join;
		}

		if(isset($filter_data['case_manager']) && $filter_data['case_manager'] == 'blank') {
				$sql .= $ins_company_data_join;
		}
	} else {
		if($isSearch === false) {
			if(!empty($select_qry) && $select_qry != "*") {
				$sql .= $patient_data_join . $pi_case_join . $ins_data_join . $ins_company_data_join . " ";
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

	//$sql_data_query = generateCaseQuery("COUNT(*) AS allcount");
	$bindArray = array();

	$records = sqlQuery(generateCaseQuery(array(
		"select" => "COUNT(*) AS allcount",
		"filter_data" => array()
	), true));
	$totalRecords = $records['allcount'];

	$records = sqlQuery(generateCaseQuery(array(
		"select" => "COUNT(*) AS allcount",
		"where" => $searchQuery,
		"filter_data" => $filterVal
	), true));

	$totalRecordwithFilter  = $records['allcount'];

	$result = sqlStatement(generateCaseQuery(array(
		"select" => "fc.id as case_id, (select min(ope.pc_eventDate) from openemr_postcalendar_events ope where ope.pc_case = fc.id and ope.pc_apptstatus not in ('-','+','?','x','%') and ope.pc_pid = fc.pid) as first_visit_date, fc.injury_date,  CONCAT(CONCAT_WS(' ', IF(LENGTH(pd.fname),pd.fname,NULL), IF(LENGTH(pd.lname),pd.lname,NULL)), ' (', pd.pubpid ,')') as patient_name, fc.ins_data_id1, fc.ins_data_id2, fc.ins_data_id3, fc.pid, fc.comments, fc.bc_notes ",
		"where" => $searchQuery,
		"order" => $columnName,
		"order_type" => $columnSortOrder,
		"limit" => $row,
		"offset" => $rowperpage
	)));

	$dataSet = array();
	$rowItems = array();
	$caseIds = array();
	while ($row_item = sqlFetchArray($result)) {
		$caseIds[] = $row_item['case_id'];
		$rowItems[] = $row_item;
		//$dataSet[] = prepareDataTableData($row_item, $columns);
	}

	//Get LBF Data
	$lbfFormDataItems = Caselib::getRehabProgressLBFData($caseIds);

	//Get Code Count DAta
	$codeCountDataItems = getCodeCountData($caseIds);

	//Get XRayData
	//$xRayDataItems = getTensCountByCase($caseIds);

	foreach ($rowItems as $rik => $rItem) {
		if(isset($lbfFormDataItems['case_'.$rItem['case_id']])) {
			$rItem['lbf_data'] = $lbfFormDataItems['case_'.$rItem['case_id']];
		}

		if(isset($codeCountDataItems['case_'.$rItem['case_id']])) {
			$codeCountDataCaseItems = $codeCountDataItems['case_'.$rItem['case_id']];

			foreach ($codeCountDataCaseItems as $cki => $codeCountDataItem) {
				if(isset($codeCountDataItem['code_type'])) {
					if($codeCountDataItem['code_type'] == "CPT4") {
						$rItem['xray_data'] = $codeCountDataItem;
					}

					if($codeCountDataItem['code_type'] == "HCPCS") {
						$rItem['tens_data'] = $codeCountDataItem;
					}
				}
			}
		}

		$dataSet[] = prepareDataTableData($rItem, $columns);
	}

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

//Get Insurance Provider
$insurancei = getInsuranceProvidersExtra();

?>

<html>
<head>
    <title><?php echo xlt('Case Manager'); ?></title>
	<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">

	<?php Header::setupHeader(['common', 'jquery', 'jquery-ui', 'jquery-ui-base', 'datetime-picker', 'datatables', 'datatables-colreorder', 'datatables-bs', 'oemr_ad']); ?>

	<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>
	<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js?v=41"></script>


	<style type="text/css">
		/*DataTable Style*/
		table.dataTable {
			font-size: 14px;
			width: calc(100% - 16px);
			position: relative;
		}
        .dataTable .defaultValueText {
        	opacity: 0.3;
        }

        /*Row Details*/
        table.row_details_table {
			table-layout: fixed;
			width: 100%;
			/*font-size: 14px;*/
			padding: 8px 10px !important;
			/*background-color: #f9f9f9;*/
		}
		table.row_details_table tr td {
			vertical-align: top;
			border: 0px solid #fff !important;
			padding: 0px;
		}
		table.row_details_table .case_note_val_container {
			white-space: pre-wrap;
		}

		#case_manager_report_container {
			margin-bottom: 60px;
		}

		#case_manager_report .text_green{
			color: green !important;
		}
		#case_manager_report .text_red{
			color: red !important;
		}
		#case_manager_report .text_black{
			color: #000 !important;
		}
		#case_manager_report .text_blue {
			color: #0000cc !important;
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

		.ins_dropdownfilter {
			display: grid;
		    grid-template-columns: 1fr auto;
		    align-items: center;
		    grid-column-gap: 3px;
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
<body class="body_top case_manager">

<div class="page-title">
    <h2>Case Manager</h2>
</div>

<div class="dataTables_wrapper datatable_filter mb-4">
	<form id="case_manager_report_filter">
		<div class="form-row">
			<div class="col">
				<div class="form-group">
					<label><?php echo xlt('Active'); ?></label>
					<select name="closed" class="form-control">
						<option value="">Please Select</option>
						<option value="0">No</option>
						<option value="1" selected>Yes</option>
					</select>
				</div>
			</div>
			<div class="col">
				<div class="form-group">
					<label><?php echo xlt('Case Manager'); ?></label>
					<select name="case_manager" class="form-control">
						<option value="">Please Select</option>
						<option value="blank">BLANK</option>
						<?php Caselib::getUsersBy('', '', array('physician_type' => array('chiropractor_physician', 'case_manager_232321')), '', false); ?>
					</select>
				</div>
			</div>
			<div class="col">
				<div class="form-group">
					<label><?php echo xlt('Chart Number'); ?></label>
					<input type="text" name="chart_number" class="input_field form-control" placeholder="Enter Chart Number">
				</div>
			</div>
			<div class="col">
				<div class="form-group">
					<label><?php echo xlt('Law firm'); ?></label>
					<div class="ins_dropdownfilter">
						<select name="law_firm" class="form-control">
							<option value="">Please Select</option>
							<?php
								foreach ($insurancei as $iid => $iname) {
				                    echo "<option value='" . attr($iid) . "'";
				                    if (strtolower($iid) == strtolower($result3{"provider"})) {
				                        echo " selected";
				                    }

				                    echo ">" . text($iname) . "</option>\n";
				                }
							?>
						</select>
						<a class='medium_modal btn btn-primary' href='<?php echo $GLOBALS['webroot']. '/interface/practice/ins_search.php'; ?>'><span> <?php echo xlt('Search'); ?></span></a>
					</div>
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="col">
				<div class="form-group">
					<label><?php echo xlt('Date of injury'); ?></label>
					<div class="form-row">
		    			<div class="col">
		    				<input type="text" name="date_of_injury_from" class="date_field form-control" placeholder="From (MM/DD/YY)">
		    			</div>
		    			<div class="col">
		    				<input type="text" name="date_of_injury_to" class="date_field form-control" placeholder="To (MM/DD/YY)">
			    		</div>
			    	</div>
				</div>
			</div>
			<div class="col">
				<div class="form-group">
					<label><?php echo xlt('Date of first visit'); ?></label>
					<div class="form-row">
		    			<div class="col">
		    				<input type="text" name="date_of_first_visit_from" class="date_field form-control" placeholder="From (MM/DD/YY)">
		    			</div>
		    			<div class="col">
		    				<input type="text" name="date_of_first_visit_to" class="date_field form-control" placeholder="To (MM/DD/YY)">
			    		</div>
			    	</div>
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="col">
				<div class="form-group">
					<label class="form-check-label">Show patients with no future appts:</label>
					<input type="checkbox" name="patient_with_no_future_app_from form-check-input" class="" value="1">
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="col">
				<button type="submit" id="filter_submit" class="btn btn-secondary"><?php echo xlt('Submit'); ?></button>
			</div>
		</div>
	</form>
</div>

<div id="case_manager_report_container" class="table-responsive">
<table id='case_manager_report' class='text table table-sm' style="width: 100%;">
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
		var law_firm_val = decodeHtmlString(d.law_firm);
		var next_appt_val = decodeHtmlString(d.next_appt);
		var prev_canceled_appt_val = decodeHtmlString(d.prev_canceled_appt);
		var rehab_plan_val = decodeHtmlString(d.rehab_plan);
		var rehab_progress_val = decodeHtmlString(d.rehab_progress);
		var orders_val = decodeHtmlString(d.orders);
		var case_note_val = decodeHtmlString(d.case_note);
		var threshold_15k_val = decodeHtmlString(d.threshold_15k);

		return '<div><table class="row_details_table text table table-sm table-borderless mb-0"><tbody>'+
					'<tr>'+
						'<td width="120">'+
							'<span>Law firm:</span>'+
						'</td>'+
						'<td>'+
							'<div>'+(law_firm_val != "" ? law_firm_val : defaultVal) +'</div>'+
						'</td>'+
						'<td width="120">'+
							'<span>Next Appts:</span>'+
						'</td>'+
						'<td>'+
							'<div>'+(next_appt_val != "" ? next_appt_val : defaultVal)+'</div>'+
						'</td>'+
					'</tr>'+
					'<tr>'+
						'<td width="120">'+
							'<span>Rehab Plan:</span>'+
						'</td>'+
						'<td>'+
							'<div>'+(rehab_plan_val != "" ? rehab_plan_val : defaultVal) +'</div>'+
						'</td>'+
						'<td width="120">'+
							'<span>Canceled Appts:</span>'+
						'</td>'+
						'<td>'+
							'<div>'+(prev_canceled_appt_val != "" ? prev_canceled_appt_val : defaultVal)+'</div>'+
						'</td>'+
					'</tr>'+
					'<tr>'+
						'<td width="120" height="10">'+
							'<span>Rehab Progress:</span>'+
						'</td>'+
						'<td>'+
							'<div>'+(rehab_progress_val != "" ? rehab_progress_val : defaultVal)+'</div>'+
						'</td>'+
						'<td width="120" rowspan="2">'+
							'<span>Case Note:</span>'+
						'</td>'+
						'<td rowspan="2">'+
							'<div>'+
								'<div class="textcontentbox">'+
									'<input type="checkbox" id="expanded_nt_'+d.case_id+'">'+
									'<div class="content case_note_val_container">'+(case_note_val != "" ? case_note_val : defaultVal)+'</div>'+
									'<label for="expanded_nt_'+d.case_id+'" class="readmore" role="button">Read More</label>'+
									'<label for="expanded_nt_'+d.case_id+'" class="lessmore" role="button">Read Less</label>'+
									'</div></div>'+
						'</td>'+
					'</tr>'+
					'<tr>'+
						'<td width="120">'+
							'<span>Orders:</span>'+
						'</td>'+
						'<td valign="top">'+
							'<div>'+(orders_val != "" ? orders_val : defaultVal)+'</div>'+
						'</td>'+
						
					'</tr>'+
					'<tr>'+
						'<td width="120">'+
						'</td>'+
						'<td valign="top">'+
						'</td>'+
						'<td width="120">'+
							'<span>$15k Threshold:</span>'+
						'</td>'+
						'<td valign="top">'+
							'<div>'+(threshold_15k_val != "" ? threshold_15k_val : defaultVal)+'</div>'+
						'</td>'+
					'</tr>'+
					
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
			        "order": [[ 1, "desc" ]],
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
	            	row.child(format(row.data()), 'no-padding row-details-tr p-3 mb-2 bg-light ').show();
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
	            	row.child(format(row.data()), 'no-padding row-details-tr p-3 mb-2 bg-light ').show();
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
		            	row.child(format(row.data()), 'no-padding row-details-tr p-3 mb-2 bg-light ').show();
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
	        indexed_array[n['name']] = n['value'];
	    });

	    $.map(indexed_array, function(ni, ii){
	    	if(ni != "") {
	    		if(ii == "date_of_injury_from" && indexed_array["date_of_injury_to"] == "") {
	    			alert("Please select to date of injury.");
	    			return false;
	    		} else if(ii == "date_of_injury_to" && indexed_array["date_of_injury_from"] == "") {
	    			alert("Please select from date of injury.");
	    			return false;
	    		}

	    		if(ii == "date_of_first_visit_from" && indexed_array["date_of_first_visit_to"] == "") {
	    			alert("Please select to date of first visit.");
	    			return false;
	    		}else if(ii == "date_of_first_visit_to" && indexed_array["date_of_first_visit_from"] == "") {
	    			alert("Please select from date of first visit.");
	    			return false;
	    		}

	    		form_val_array[ii] = ni;
	    	}
	    });
		}

		return form_val_array;
	}

	// The ins_search.php window calls this to set the selected insurance.
	function set_insurance(ins_id, ins_name) {
		var thesel = document.querySelector('#case_manager_report_filter select[name="law_firm"]');
		var theopts = thesel.options; // the array of Option objects
		 var i = 0;
		 for (; i < theopts.length; ++i) {
		  if (theopts[i].value == ins_id) {
		   theopts[i].selected = true;
		   return;
		  }
		 }
		// no matching option was found so create one, append it to the
		// end of the list, and select it.
		theopts[i] = new Option(ins_name, ins_id, false, true);
	}

	$(function () {
		var dataTableId = "#case_manager_report";
		var dataTableFilterId = "#case_manager_report_filter";
		var dataTable = initDataTable(
			dataTableId, 
			'case_manager_report.php', 
			{ action: 'fetch_data' },
			'<?php echo json_encode($columnList); ?>'
		);

		$(dataTableFilterId).submit(function(e){
            e.preventDefault();
            dataTable.draw();
        });

		$(".medium_modal").on('click', function(e) {
	        e.preventDefault();e.stopPropagation();
	        dlgopen('', '', 650, 460, '', '', {
	            buttons: [
	                {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
	            ],
	            //onClosed: 'refreshme',
	            allowResize: false,
	            allowDrag: true,
	            dialogId: '',
	            type: 'iframe',
	            url: $(this).attr('href')
	        });
	    });
	});
</script>

<script type="text/javascript">
	var curr_scrollYVal = 0;
	var prev_scrollYVal = 0;
	$(document).ready(function() {
		window.addEventListener("scroll", (event) => {
		  	curr_scrollYVal = $(window).scrollTop();
		});

		var observer = new MutationObserver(function(mutationsList, observer) {
		    for (var mutation of mutationsList){
		        if($(mutation.target).is(":visible")){
		        	if(prev_scrollYVal >= 0) {
		        		$(window).scrollTop(prev_scrollYVal);
		        	}
		        } else if(!$(mutation.target).is(":visible")){
		        	prev_scrollYVal = curr_scrollYVal
		        }
		    }
		});

		$('.frameDisplay iframe', parent.document).each(function(i, obj) {
			var cElement = $(obj).contents().find('body.case_manager');
			if(cElement.length > 0){
				observer.observe(obj.parentElement, { attributes: true});
			}
		});
	});
</script>
</body>
</html>

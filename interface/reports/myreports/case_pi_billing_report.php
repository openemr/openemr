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
		"name" => "bc_created_time",
		"title" => "Date Created",
		"data" => array(
			"width" => "180"
		)
	),
	array(
		"name" => "bc_update_time",
		"title" => "Date Updated",
		"data" => array(
			"width" => "180"
		)
	),
	array(
		"name" => "patient_name",
		"title" => "Patient Name",
		"data" => array(
			"orderable" => false,
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
		)
	),
	array(
		"name" => "billing_notes_status",
		"title" => "Billing Notes Status",
		"data" => array(
			"orderable" => false,
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
		)
	),
	array(
		"name" => "case_manager",
		"title" => "Case Manager",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "visible" => true,
            "orderable" => false,
            "width" => "250"
		)
	),
	array(
		"name" => "bc_stat",
		"title" => "Stat",
		"data" => array(
			"orderable" => true,
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
		)
	),
	array(
		"name" => "bi_note",
		"title" => "Billing Note",
		"data" => array(
			"visible" => false,
			"defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
			"orderable" => false,
			"width" => "0"
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
	)
);

function getHtmlString($text) {
	return addslashes(htmlspecialchars($text));
}

//Filter Query Data
function generateFilterQuery($filterData = array()) {
	$filterQryList = array();
	$filterQry = "";

	if(!empty($filterData)) {
		if(isset($filterData['billing_notes']) && !empty($filterData['billing_notes'])) {
			$filterQryList[] = "fc.bc_notes IN('" . implode("','", $filterData['billing_notes']) . "')";
		}

		if(isset($filterData['law_firm']) && !empty($filterData['law_firm'])) {
			$filterQryList[] = "(id1.provider = '". $filterData['law_firm'] ."' or id2.provider = '". $filterData['law_firm'] ."' or id3.provider = '". $filterData['law_firm'] ."')";
		}

		if(isset($filterData['delivery_date_from']) && !empty($filterData['delivery_date_from']) && isset($filterData['delivery_date_to']) && !empty($filterData['delivery_date_to'])) {
			$filterData['delivery_date_from'] = date('Y/m/d', strtotime($filterData['delivery_date_from']));
			$filterData['delivery_date_to'] = date('Y/m/d', strtotime($filterData['delivery_date_to']));
			
			$filterQryList[] = "(fc.bc_date IS NOT null and fc.bc_date != '' and date(fc.bc_date) between '".$filterData['delivery_date_from']."' and '".$filterData['delivery_date_to']."')";
		}

		if(isset($filterData['create_time_from']) && !empty($filterData['create_time_from']) && isset($filterData['create_time_to']) && !empty($filterData['create_time_to'])) {
			$filterData['create_time_from'] = date('Y/m/d', strtotime($filterData['create_time_from']));
			$filterData['create_time_to'] = date('Y/m/d', strtotime($filterData['create_time_to']));
			
			$filterQryList[] = "(fc.`date` IS NOT null and fc.`date` != '' and date(fc.`date`) between '".$filterData['create_time_from']."' and '".$filterData['create_time_to']."')";
		}

		if(!empty($filterQryList)) {
			$filterQry = implode(" and ", $filterQryList);
		}
	}

	return $filterQry;
}

//Generate Query
function generateCaseQuery($data = array(), $isSearch = false) {
	$select_qry = isset($data['select']) ? $data['select'] : "*";
	$where_qry = isset($data['where']) ? $data['where'] : "";
	$order_qry = isset($data['order']) ? $data['order'] : "fc.id"; 
	$order_type_qry = isset($data['order_type']) ? $data['order_type'] : "desc";

	if($order_qry == "patient_name") {
		$order_qry = "CONCAT(CONCAT_WS(' ', IF(LENGTH(pd.fname),pd.fname,NULL), IF(LENGTH(pd.lname),pd.lname,NULL)), ' (', pd.pubpid ,')')";
	}

	if($order_qry != "bc_stat") {
		$new_order_query = "bc_stat desc,";
	}

	$limit_qry = isset($data['limit']) ? $data['limit'] : ""; 
	$offset_qry = isset($data['offset']) ? $data['offset'] : "asc";

	$sql = "SELECT $select_qry from form_cases fc";

	$patient_data_join = " left join patient_data pd on pd.pid = fc.pid";
	$pi_case_join = " left join vh_pi_case_management_details vpcmd on vpcmd.case_id = fc.id and vpcmd.field_name = 'case_manager' and vpcmd.field_index = 0";
	$ins_data_join = " left join insurance_data id1 on id1.id = fc.ins_data_id1 left join insurance_data id2 on id2.id = fc.ins_data_id2 left join insurance_data id3 on id3.id = fc.ins_data_id3";
	//$ins_company_data_join = " left join insurance_companies ic1 on ic1.id = id1.provider left join insurance_companies ic2 on ic2.id = id2.provider left join insurance_companies ic3 on ic3.id = id2.provider";

	if(isset($data['filter_data'])) {
	 	$filter_data = isset($data['filter_data']) ? $data['filter_data'] : array();

		if((isset($filter_data['law_firm']) && !empty($filter_data['law_firm'])) || ($filter_data['case_manager'] == 'blank')) {
			$sql .= $ins_data_join;
		}

	} else {
		if($isSearch === false) {
			if(!empty($select_qry) && $select_qry != "*") {
				$sql .= $patient_data_join . $pi_case_join . $ins_data_join . " ";
			}
		}
	}

	if(!empty($where_qry)) {
		$sql .= " WHERE $where_qry";
	}

	if(!empty($order_qry)) {
		$sql .= " ORDER BY $new_order_query $order_qry $order_type_qry";
	}

	if($limit_qry != '' && $offset_qry != '') {
		$sql .= " LIMIT $limit_qry , $offset_qry";
	}

	return $sql;
}

//Prepare Data Table Data
function prepareDataTableData($row_item = array(), $columns = array()) {
	$rowData = array();

	foreach ($columns as $clk => $cItem) {
		if(isset($cItem['name'])) {
			if($cItem['name'] == "case_id") {
				$fieldHtml = "<a href=\"#!\" onclick=\"handlegotoCase('".$row_item['case_id']."','".$row_item['pid']."');\">". $row_item[$cItem['name']] . "</a>";
				$rowData[$cItem['name']] = (isset($row_item[$cItem['name']]) && !empty($row_item[$cItem['name']])) ? getHtmlString($fieldHtml) : "-";
				continue;
			} else if($cItem['name'] == "date") { 
				$createdDate = Caselib::fetchCaseAlertMaxDAte($row_item['case_id']);
				$rowData[$cItem['name']] = isset($createdDate['created_date']) ? getHtmlString($createdDate['created_date']) : "";
				continue;
			} else if($cItem['name'] == "patient_name") {
				$fieldHtml = "<a href=\"#!\" onclick=\"goParentPid('".$row_item['pid']."');\">". $row_item[$cItem['name']] . "</a>";
				$rowData[$cItem['name']] = (isset($row_item[$cItem['name']]) && !empty($row_item[$cItem['name']])) ? getHtmlString($fieldHtml) : "-";
				continue;
			} else if($cItem['name'] == "billing_notes_status") {
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
				$fieldHtml = "";
				
				if(!empty($bc_notes_val)) {
					$nq_filter = ' AND option_id = "'.$bc_notes_val.'"';
					$listOptions = LoadList('Case_Billing_Notes', 'active', 'seq', '', $nq_filter);
					$req_class = (isset($bcNotesStatusListClass[$bc_notes_val])) ? $bcNotesStatusListClass[$bc_notes_val] : '';

					if(!empty($listOptions)) {
						$fieldTitle = $listOptions[0] && isset($listOptions[0]['title']) ? $listOptions[0]['title'] : "";
						$tooltip_html = $fieldTitle;
						$fieldHtml = "<span data-toggle='tooltip' class='$req_class tooltip_text' title=''>".$fieldTitle."<div class='hidden_content'style='display:none;'>".$tooltip_html."</div></span>";
					}
				}

				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "bi_note") { 
				$fieldHtml = isset($row_item['bc_notes_dsc']) ? $row_item['bc_notes_dsc'] : "";

				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? $fieldHtml : "";
				continue;
			} else if($cItem['name'] == "bc_stat") { 
				$fieldHtml = isset($row_item['bc_stat']) && $row_item['bc_stat'] === "1" ? "Yes" : "No";

				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? $fieldHtml : "";
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
			} else if($cItem['name'] == "case_manager") {
				$caseManager = isset($row_item['case_manager']) ? $row_item['case_manager'] : "";
				$fieldHtml = "";

				if(!empty($caseManager)) {
					$u_results = sqlQuery("select lname, fname, mname from users where id=?", array($caseManager));
					$fieldHtml =  $u_results['lname'].', '.$u_results['fname'].' '.$u_results['mname'];
				}

				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? $fieldHtml : "";
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
		"select" => "fc.id as case_id, fc.pid, fc.date, CONCAT(CONCAT_WS(' ', IF(LENGTH(pd.fname),pd.fname,NULL), IF(LENGTH(pd.lname),pd.lname,NULL)), ' (', pd.pubpid ,')') as patient_name, fc.bc_date, fc.bc_notes, fc.bc_notes_dsc, fc.bc_stat, fc.ins_data_id1, fc.ins_data_id2, fc.ins_data_id3, fc.bc_created_time, fc.bc_update_time, vpcmd.field_value as case_manager ",
		"where" => $searchQuery,
		"order" => $columnName,
		"order_type" => $columnSortOrder,
		"limit" => $row,
		"offset" => $rowperpage
	)));

	$dataSet = array();
	while ($row_item = sqlFetchArray($result)) {
		$dataSet[] = prepareDataTableData($row_item, $columns);
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
    <title><?php echo xlt('PI Billing/Records'); ?></title>
    <?php //Header::setupHeader('datetime-picker'); ?>

	<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">

	<?php Header::setupHeader(['common', 'jquery', 'jquery-ui', 'jquery-ui-base', 'datetime-picker', 'datatables', 'datatables-colreorder', 'datatables-bs', 'oemr_ad']); ?>

	<style type="text/css">
        /*Row Details*/
        table.row_details_table {
			table-layout: fixed;
			width: 100%;
			font-size: 14px;
			padding: 8px 10px;
		}
		table.row_details_table tr td {
			vertical-align: top;
			border: 0px solid #fff !important;
			padding: 0px;
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
		.ins_dropdownfilter {
			display: grid;
		    grid-template-columns: 1fr auto;
		    align-items: center;
		    grid-column-gap: 3px;
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
	    <h2>PI Billing/Records</h2>
	</div>

	<div class="dataTables_wrapper datatable_filter mb-4">
		<form id="case_pi_billing_report_filter">
			<div class="form-row">
				<div class="col">
					<div class="form-group">
						<label><?php echo xlt('Billing Notes Status'); ?></label>
						<select name="billing_notes" class="billing_notes form-control" multiple>
							<?php ListSel('', 'Case_Billing_Notes', 'Please Select'); ?>
						</select>
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
							<a class='medium_modal css_button btn btn-primary' href='<?php echo $GLOBALS['webroot']. '/interface/practice/ins_search.php'; ?>'><span> <?php echo xlt('Search'); ?></span></a>
						</div>
					</div>
				</div>
			</div>
			<div class="form-row">
				<div class="col">
					<div class="form-group">
						<label><?php echo xlt('Delivery Date'); ?></label>
						<div class="form-row">
			    			<div class="col">
			    				<input type="text" name="delivery_date_from" class="date_field form-control" placeholder="From (MM/DD/YY)">
			    			</div>
			    			<div class="col">
			    				<input type="text" name="delivery_date_to" class="date_field form-control" placeholder="To (MM/DD/YY)">
				    		</div>
				    	</div>
					</div>
				</div>
				<div class="col">
					<div class="form-group">
						<label><?php echo xlt('Created Time'); ?></label>
						<div class="form-row">
			    			<div class="col">
			    				<input type="text" name="create_time_from" class="date_field form-control" placeholder="From (MM/DD/YY)">
			    			</div>
			    			<div class="col">
			    				<input type="text" name="create_time_to" class="date_field form-control" placeholder="To (MM/DD/YY)">
				    		</div>
				    	</div>
					</div>
				</div>
			</div>
			<div class="form-row">
				<div class="col">
					<button type="submit" id="filter_submit" class="btn btn-secondary">Submit</button>
				</div>
			</div>
		</form>
	</div>

	<div id="case_pi_billing_report_container" class="table-responsive">
		<table id='case_pi_billing_report' class='text table table-sm datatable_report'>
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
		var bi_note_val = decodeHtmlString(d.bi_note);
		var law_firm_val = decodeHtmlString(d.law_firm);
		var case_manager = decodeHtmlString(d.case_manager);
		var next_appt_val = decodeHtmlString(d.next_appt);

		return '<div><table class="row_details_table text table table-sm table-borderless mb-0"><tbody>'+
					'<tr>'+
						'<td width="120" height="10">'+
							'<span>Law firm:</span>'+
						'</td>'+
						'<td>'+
							'<div>'+(law_firm_val != "" ? law_firm_val : defaultVal) +'</div>'+
						'</td>'+
						'<td width="50" rowspan="2">'+
							'<span>Notes:</span>'+
						'</td>'+
						'<td rowspan="2">'+
							'<div>'+
								'<div class="textcontentbox">'+
									'<input type="checkbox" id="expanded_nt_'+d.case_id+'">'+
									'<div class="content case_note_val_container">'+(bi_note_val != "" ? bi_note_val : defaultVal)+'</div>'+
									'<label for="expanded_nt_'+d.case_id+'" class="readmore" role="button">Read More</label>'+
									'<label for="expanded_nt_'+d.case_id+'" class="lessmore" role="button">Read Less</label>'+
									'</div></div>'+
						'</td>'+
					'</tr>'+
					'<tr>'+
						'<td width="120" height="10">'+
							'<span>Next Appts:</span>'+
						'</td>'+
						'<td valign="top">'+
							'<div>'+(next_appt_val != "" ? next_appt_val : defaultVal)+'</div>'+
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
			        "order": [[ 2, "desc" ]],
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
	        if(n['name'] == "billing_notes" && n['value'] != "") {
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
	    		if(ii == "delivery_date_from" && indexed_array["delivery_date_to"] == "") {
	    			alert("Please select to delivery date.");
	    			return false;
	    		} else if(ii == "delivery_date_to" && indexed_array["delivery_date_from"] == "") {
	    			alert("Please select from delivery date.");
	    			return false;
	    		}

	    		if(ii == "create_time_from" && indexed_array["create_time_to"] == "") {
	    			alert("Please select to created time.");
	    			return false;
	    		}else if(ii == "create_time_to" && indexed_array["create_time_from"] == "") {
	    			alert("Please select from created time.");
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
		var thesel = document.querySelector('#case_pi_billing_report_filter select[name="law_firm"]');
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
		var dataTableId = "#case_pi_billing_report";
		var dataTableFilterId = "#case_pi_billing_report_filter";

		//$('#filter_submit').prop('disabled', true);
		var dataTable = initDataTable(
			dataTableId, 
			'case_pi_billing_report.php', 
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

</body>
</html>
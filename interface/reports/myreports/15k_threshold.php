<?php

require_once("../../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/wmt-v2/wmtstandard.inc");
require_once("$srcdir/wmt-v2/wmt.msg.inc");
require_once("$srcdir/OemrAD/oemrad.globals.php");
require_once($GLOBALS['OE_SITE_DIR'] . "/odbcconf.php");

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
		"name" => "reported_date_time",
		"title" => "Reported Date",
		"data" => array(
			"width" => "180"
		)
	),
	array(
		"name" => "patient_name",
		"title" => "Patient Name",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "250",
            "orderable" => false,
		)
	),
	array(
		"name" => "payer_name",
		"title" => "Payer Name",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "250",
            "orderable" => false,
		)
	),
	array(
		"name" => "case_id",
		"title" => "Case Number",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "250"
		)
	),
	array(
		"name" => "snapshot_balance",
		"title" => "Snapshot Balance",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "150",
            "orderable" => false,
		)
	),
	array(
		"name" => "next_appt",
		"title" => "Next Appts",
		"data" => array(
            "defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "250",
            "orderable" => false,
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
		if(isset($filterData['closed'])) {
			$filterQryList[] = "fc.closed = " . (intval($filterData['closed']) ? 0 : 1);
		}

		if(isset($filterData['case_manager']) && !empty($filterData['case_manager'])) {
			if($filterData['case_manager'] == 'blank' || (is_array($filterData['case_manager']) && in_array('blank', $filterData['case_manager']))) {
				$filterQryList[] = "if(((ic1.ins_type_code in ('20', '21', '16') OR ic2.ins_type_code in ('20', '21', '16') OR ic3.ins_type_code in ('20', '21', '16')) and length(trim(coalesce(vpcmd.field_value,'')))=0), 1, 0) = 1";
			} else {
				$filterQryList[] = "vpcmd.field_value IN('" . implode("','", $filterData['case_manager']) . "')";
			}
		}

		if(isset($filterData['date_from']) && !empty($filterData['date_from']) && isset($filterData['date_to']) && !empty($filterData['date_to'])) {
			$filterData['date_from'] = date('Y/m/d', strtotime($filterData['date_from']));
			$filterData['date_to'] = date('Y/m/d', strtotime($filterData['date_to']));
			
			$filterQryList[] = "(vtd.reported_datetime_whencrossed15k IS NOT null and vtd.reported_datetime_whencrossed15k != '' and date(vtd.reported_datetime_whencrossed15k) between '".$filterData['date_from']."' and '".$filterData['date_to']."')";
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
	$order_qry = isset($data['order']) ? $data['order'] : "tr.id"; 
	$order_type_qry = isset($data['order_type']) ? $data['order_type'] : "desc";

	if($order_qry == "id") {
		$order_qry = "tr.id";
	}

	$limit_qry = isset($data['limit']) ? $data['limit'] : ""; 
	$offset_qry = isset($data['offset']) ? $data['offset'] : "asc";

	$sql = "SELECT $select_qry from vh_15000threshold_report tr join form_cases fc on fc.id = tr.oemr_case_id";
	//$sql = "SELECT $select_qry from vh_15000threshold_report tr join form_cases fc on fc.id = 72014";

	$data_15k_join = " left join vh_15000threshold_data as vtd on vtd.case_id = tr.oemr_case_id";

	$patient_data_join = " left join patient_data as pd on pd.pid = fc.pid";
	$pi_case_join = " left join vh_pi_case_management_details vpcmd on vpcmd.case_id = fc.id and vpcmd.field_name = 'case_manager' and vpcmd.field_index = 0";
	$ins_data_join = " left join insurance_data id1 on id1.id = fc.ins_data_id1 left join insurance_data id2 on id2.id = fc.ins_data_id2 left join insurance_data id3 on id3.id = fc.ins_data_id3";
	$ins_company_data_join = " left join insurance_companies ic1 on ic1.id = id1.provider left join insurance_companies ic2 on ic2.id = id2.provider left join insurance_companies ic3 on ic3.id = id2.provider";

	if(isset($data['filter_data'])) {
		$sql .= $data_15k_join;

	 	$filter_data = isset($data['filter_data']) ? $data['filter_data'] : array();

		if(isset($filter_data['case_manager']) && !empty($filter_data['case_manager'])) {
			$sql .= $pi_case_join;
		}

		if(($filter_data['case_manager'] == 'blank' || (is_array($filter_data['case_manager']) && in_array('blank', $filter_data['case_manager'])))) {
			$sql .= $ins_data_join;
		}

		if(isset($filter_data['case_manager']) && $filter_data['case_manager'] == 'blank' || (is_array($filter_data['case_manager']) && in_array('blank', $filter_data['case_manager']))) {
			$sql .= $ins_company_data_join;
		}

	} else {
		if($isSearch === false) {
			if(!empty($select_qry) && $select_qry != "*") {
				$sql .= $data_15k_join . $patient_data_join . $pi_case_join . $ins_data_join . $ins_company_data_join . " ";
			}
		}
	}

	if(!empty($where_qry)) {
		$sql .= " WHERE vtd.reported_datetime_whencrossed15k IS NOT null AND $where_qry";
	} else {
		$sql .= " WHERE vtd.reported_datetime_whencrossed15k IS NOT null ";
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

	foreach ($columns as $clk => $cItem) {
		if(isset($cItem['name'])) {
			if($cItem['name'] == "reported_date_time") {
				$fieldHtml = "<span>".$row_item['reported_date_time']."</span>";
				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "patient_name") {
				$fieldHtml = "<a href=\"#!\" class='linktext $req_class' onclick=\"goParentPid('".$row_item['pid']."');\">". $row_item[$cItem['name']] . "</a>";
				$rowData[$cItem['name']] = (isset($row_item[$cItem['name']]) && !empty($row_item[$cItem['name']])) ? getHtmlString($fieldHtml) : "-";
				continue;
			} else if($cItem['name'] == "case_id") {
				$fieldHtml = "<a href=\"#!\" onclick=\"handlegotoCase('".$row_item['case_id']."','".$row_item['pid']."');\">". $row_item[$cItem['name']] . "</a>";
				$rowData[$cItem['name']] = (isset($row_item[$cItem['name']]) && !empty($row_item[$cItem['name']])) ? getHtmlString($fieldHtml) : "-";
				continue;
			} else if($cItem['name'] == "payer_name") {
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
			} else if($cItem['name'] == "snapshot_balance") {
				$fieldHtml = isset($row_item['snapshot_balance']) ? '$' . number_format($row_item['snapshot_balance']) : "";
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

	//Filter Value
	$filterQuery .= generateFilterQuery($filterVal);

	if(!empty($filterQuery)) {
		$searchQuery .= " " . $filterQuery;
	}

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
		"select" => "DATE(vtd.reported_datetime_whencrossed15k) as reported_date_time, tr.amount as snapshot_balance, fc.id as case_id, fc.pid, pd.pubpid, CONCAT(CONCAT_WS(' ', IF(LENGTH(pd.fname),pd.fname,NULL), IF(LENGTH(pd.lname),pd.lname,NULL)), ' (', pd.pubpid ,')') as patient_name, fc.ins_data_id1, fc.ins_data_id2, fc.ins_data_id3 ",
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
		if(!in_array($row_item['case_id'], $caseIds)) {
			$caseIds[] = $row_item['case_id'];
		}
		$rowItems[] = $row_item;
	}

	foreach ($rowItems as $rik => $rItem) {
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

?>
<html>
<head>
    <title><?php echo xlt('15k Threshold Report'); ?></title>

	<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
	<?php Header::setupHeader(['opener', 'jquery', 'jquery-ui-base', 'datetime-picker', 'datatables', 'datatables-colreorder', 'datatables-bs', 'oemr_ad']); ?>

	<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js?v=41"></script>

	<style type="text/css">
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
	    <h2>15k Threshold Report</h2>
	</div>

	<div class="datatable_filter">
		<form id="page_report_filter">
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

					<div class="form-group">
						<label><?php echo xlt('Date'); ?></label>
						<div class="form-row">
			    			<div class="col">
			    				<input type="text" name="date_from" class="date_field form-control inline-block" placeholder="From (MM/DD/YY)" value="<?php echo date('m/d/Y', time()) ?>">
			    			</div>
			    			<div class="col">
			    				<input type="text" name="date_to" class="date_field form-control inline-block" placeholder="To (MM/DD/YY)" value="<?php echo date('m/d/Y', time()) ?>">
			    			</div>
			    		</div>
					</div>
			    </div>
			    <div class="col">
			    	<div class="form-group">
						<label><?php echo xlt('Case Manager'); ?></label>
						<select name="case_manager" class="form-control" multiple>
							<option value="">Please Select</option>
							<option value="blank" selected>BLANK</option>
							<?php Caselib::getUsersBy('', '', array('physician_type' => array('chiropractor_physician', 'case_manager_232321')), '', false); ?>
						</select>
					</div>
			    </div>
			</div>
			<div class="form-row">
			    <div class="col">
			    	<button type="submit" class="btn btn-secondary" id="filter_submit"><?php echo xlt('Submit'); ?></button>
			    </div>
			</div>
		</form>
	</div>

	<div id="page_report_container" class="datatable_container table-responsive">
		<table id='page_report' class='text table table-sm datatable_report'>
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
		return '<h1>Heading</h1>';
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
			        "order": [[ 0, "asc" ]],
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
	            	row.child(format(row.data()), 'no-padding row-details-tr '+childTrClass).show();
		            tr.addClass('shown').trigger('classChange');
		            //initTooltip();
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
		            	row.child(format(row.data()), 'no-padding row-details-tr '+childTrClass).show();
			            tr.addClass('shown').trigger('classChange');
			            //$('.dt-control-all').closest('tr').addClass('shown');
			            //initTooltip();
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
		        if((n['name'] == "case_manager") && n['value'] != "") {
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
		var dateFrom = document.querySelector('input[name="date_from"]').value;
		var dateTo = document.querySelector('input[name="date_to"]').value;

		if(dateFrom != "" && dateTo == "") {
			alert("Please select \"to date\".");
			return false;
		}

		if(dateTo != "" && dateFrom == "") {
			alert("Please select \"from date\".");
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
			'15k_threshold.php', 
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
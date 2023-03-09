<?php

require_once("../globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\Core\Header;
use OpenEMR\OemrAd\CoverageCheck;

if(!isset($_GET{'cnt'})) $_GET['cnt'] = '';
if(!isset($_GET{'case_id'})) $_GET['case_id'] = '';
if(!isset($_GET{'pid'})) $_GET['pid'] = '';

$cnt = strip_tags($_GET['cnt']);
$case_id = strip_tags($_GET['case_id']);
$pid = strip_tags($_GET['pid']);


function generatePayerNotes($payerNotes) {
	$payernotesStr = '';

	if(isset($payerNotes)) {
		$payernotesStr .= '<ol class="payerNotesList">';
		foreach ($payerNotes as $pn => $payerNote) {
			$payernotesStr .= '<li>'.$payerNote.'</li>';
		}
		$payernotesStr .= '</ol>';
	}

	return $payernotesStr;
}

function generatePlanPayerNotes($payerNotes) {
	$payernotesStr = '';

	if(isset($payerNotes)) {
		$payernotesStr .= '<ol class="payerNotesList">';
		foreach ((array)$payerNotes as $pn => $payerNote) {
			$pNote = (array)$payerNote;
			$type = isset($pNote['type']) ? $pNote['type']." - "  : "";
			$typeCode = isset($pNote['typeCode']) ? $pNote['typeCode']." - "  : "";
			$message = isset($pNote['message']) ? $pNote['message']  : "";
			$payernotesStr .= '<li>'.$type.$typeCode.$message.'</li>';
		}
		$payernotesStr .= '</ol>';
	}

	return $payernotesStr;
}

function generateAmountsSub($amounts) {
	$htmlStr = '';

	foreach ($amounts as $ak => $obj) {
		$htmlStr .= '<tr>';
			$htmlStr .= '<td class="benefitText alignleft table-warning" colspan="3">'.ucfirst($ak).'</td>';
		$htmlStr .= '</tr>';
		foreach ((array)$obj as $okey => $oitem) {
			$ocounter = 0;
			foreach ((array)$oitem as $ofieldKey => $ofieldValue) {
				$fieldVal = '';

				if(is_array($ofieldValue)) {
					if($ofieldKey == 'payerNotes') {
						$fieldVal = generatePayerNotes($ofieldValue);
					} else {
						$fieldVal = json_encode($ofieldValue);
					}
				} else {
					$fieldVal = $ofieldValue;
				}

				$htmlStr .= '<tr>';
					if($ocounter == 0) {
						$htmlStr .= '<td width="20" class="srNo" rowspan="'.count((array)$oitem).'"><span class="t-bold">'.($okey+1).'</span></td>';
					}
					$htmlStr .= '<td><span class="t-bold">'.ucfirst($ofieldKey).'</span></td>';
					$htmlStr .= '<td width="100%">'.$fieldVal.'</td>';
				$htmlStr .= '</tr>';
				$ocounter++;
			}
		}
	}

	if($htmlStr == '') {
		$htmlStr .= '<tr>';
		$htmlStr .= '<td>'. xl('No Records') .'</td>';
		$htmlStr .= '</tr>';
	}

	return $htmlStr;
}

function generateAmounts($amounts) {
	$htmlStr = '';

	foreach ($amounts as $ak => $obj) {
		$htmlStr .= '<h6><span class="t-bold">Amount - '.ucfirst($ak).'</span></h6>';
		$htmlStr .= '<table class="statusTable table text table-sm table-bordered" width="100%">';
		$htmlStr .= '<tbody>';
		$htmlStr .= generateAmountsSub((array)$obj);
		$htmlStr .= '</tbody>';
		$htmlStr .= '</table>';
	}

	return $htmlStr;
}

function generateStatusNoNetwork($statusDetails) {
	$htmlStr = '';

	foreach ($statusDetails as $sdk => $obj) {
		$htmlStr .= '<tr>';
			$htmlStr .= '<td class="benefitText alignleft table-warning" colspan="3">'.ucfirst($sdk).'</td>';
		$htmlStr .= '</tr>';
		foreach ((array)$obj as $okey => $oitem) {
			$ocounter = 0;
			foreach ((array)$oitem as $ofieldKey => $ofieldValue) {
				$fieldVal = '';

				if(is_array($ofieldValue)) {
					if($ofieldKey == 'payerNotes') {
						$fieldVal = generatePayerNotes($ofieldValue);
					} else {
						$fieldVal = json_encode($ofieldValue);
					}
				} else {
					$fieldVal = $ofieldValue;
				}

				$htmlStr .= '<tr>';
					if($ocounter == 0) {
						$htmlStr .= '<td width="20" class="srNo" rowspan="'.count((array)$oitem).'"><span class="t-bold">'.($okey+1).'</span></td>';
					}
					$htmlStr .= '<td><span class="t-bold">'.ucfirst($ofieldKey).'</span></td>';
					$htmlStr .= '<td width="100%">'.$fieldVal.'</td>';
				$htmlStr .= '</tr>';
				$ocounter++;
			}
		}
	}

	if($htmlStr == ''){
		$htmlStr .= '<tr>';
		$htmlStr .= '<td colspan="5">'. xl('No Records') .'</td>';
		$htmlStr .= '</tr>';
	}

	return $htmlStr;
}

function isAssoc(array $arr){
    if (array() === $arr) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
}

function displayChildDetails($details, $level) {
	$htmlStr = '';

	if($level > 0) {
		$htmlStr = '';
	}

	$ocounter = 0;
	$level++;

	$htmlStr = '';

	foreach ($details as $key => $item) {
		if(is_array($item)) {
			if(!is_numeric($key) && $level == 1) {
				$htmlStr .= '<tr>';
				$htmlStr .= '<td class="benefitText alignleft table-warning" colspan="3">'.ucfirst($key).'</td>';
				$htmlStr .= '</tr>';
			}

			$htmlStr .= '<tr>';

			if(isAssoc($details) == true && $level > 1){ 
				$htmlStr .= '<td><span class="t-bold">'.ucfirst($key).'</span></td>';
			} else if(isAssoc($details) == false){
				$htmlStr .= '<td width="10" class="srNo"><span class="t-bold">'.($key+1).'</span></td>';
			}
			
			$htmlStr .= '<td class="alignleft" colspan="2" style="padding:0px!important;" width="100%">';
			$htmlStr .= displayChildDetails($item, $level);
			$htmlStr .= '</td>';
			$htmlStr .= '</tr>';
		} else {
			if(is_array($item)) {
				if($key == 'payerNotes') {
					$fieldVal = generatePayerNotes($item);
				} else {
					$fieldVal = json_encode($item);
				}
			} else {
				$fieldVal = $item;
			}

		 	$htmlStr .= '<tr>';
		 	if(!is_numeric($key)) {
				$htmlStr .= '<td><span class="t-bold">'.ucfirst($key).'</span></td>';
			}
			$htmlStr .= '<td width="100%" colspan="2">'.$fieldVal.'</td>';
			$htmlStr .= '</tr>';
		}

		$ocounter++;
	}

	if($htmlStr == '') {
		$htmlTableStr .= '<div class="empty-plans-records">';
			$htmlTableStr .= '<span colspan="4">'. xl('No Records') .'</span>';
		$htmlTableStr .= '</div>';
	} else {
		$htmlTableStr .= '<table class="statusTable n-bordered table text table-sm table-bordered" width="100%" style="border:0px;margin-bottom:0px!important;">';
		$htmlTableStr .= '<tbody>';
		$htmlTableStr .= $htmlStr;
		$htmlTableStr .= '</tbody>';
		$htmlTableStr .= '</table>';
	}

	return $htmlTableStr;
}

function displayDetails($benefitsObj) {
	$benefitsArray = json_decode(json_encode($benefitsObj), true);
	$ignoreList = array('statusDetails', 'amounts');
	$htmlStr = '';
	$level = 0;

	foreach ($benefitsArray as $key => $childItem) {
		if(!in_array($key, $ignoreList) && is_array($childItem)) {
			$htmlStr .= '<h6><span class="t-bold">'.ucfirst($key).'</span></h6>';
			$htmlStr .= '<table class="statusTable table text table-sm table-bordered" width="100%">';
			$htmlStr .= '<tbody>';
			$htmlStr .= '<tr><td style="padding:0px!important;">';
			$htmlStr .= displayChildDetails($childItem, $level);
			$htmlStr .= '</td></tr>';
			$htmlStr .= '</tbody>';
			$htmlStr .= '</table>';
		}
	}

	return $htmlStr;
}

function generateBenefits($benefits) {
	$htmlStr = '';

	if(isset($benefits) && isset($benefits->statusDetails)) {
		
		$htmlStr .= '<h6><span class="t-bold">StatusDetails</span></h6>';
		$htmlStr .= '<table class="statusTable table text table-sm table-bordered" width="100%">';
		$htmlStr .= '<tbody>';
		$htmlStr .= generateStatusNoNetwork($benefits->statusDetails);
		$htmlStr .= '</tbody>';
		$htmlStr .= '</table>';
	}

	if(isset($benefits) && isset($benefits->amounts)) {
		$htmlStr .= generateAmounts((array)$benefits->amounts);
	}

	$htmlStr .= displayDetails($benefits);

	if($htmlStr == '') {
		$htmlStr .= '<div class="empty-plans-records">';
			$htmlStr .= '<span colspan="4">'. xl('No Records') .'</span>';
		$htmlStr .= '</div>';
	}

	return $htmlStr;
}

/*Generate plan content for sub row*/
function generateChildRow($historyData) {
	$obj = array();
	if(isset($historyData) && is_array($historyData)) {
		foreach ($historyData as $i => $item) {
			if(isset($item['coverage_data_obj'])) {
				$coverageData = $item['coverage_data_obj'];
				
				$htmlStr = '';
				if(isset($coverageData['errors'])) {
					$htmlStr .= '<div class="error-records-container">';
						$htmlStr .= CoverageCheck::generateErrorString($coverageData, false);
					$htmlStr .= '</div>';
				} else if(isset($coverageData) && CoverageCheck::checkIsValidationErrors($coverageData) !== false) {
					$htmlStr .= '<div class="error-records-container">';
						$htmlStr .= CoverageCheck::generateErrorString($coverageData, false);
					$htmlStr .= '</div>';
				} else if(isset($coverageData['plans']) && is_array($coverageData['plans']) && count($coverageData['plans']) > 0) {
					$htmlStr .= '<h4>'. xl('Plans') .'</h4>';
					$htmlStr .= '<table class="childTable table text table-sm table-bordered" width="100%">';
					$htmlStr .= '<tbody>';
					foreach ($coverageData['plans'] as $pi => $plan) {
						$plan_start_date = isset($plan->planStartDate) ? $plan->planStartDate : '';
						$payerNotes = isset($plan->payerNotes) ? generatePlanPayerNotes($plan->payerNotes) : "";

						if($pi != 0) {
						$htmlStr .= '<tr>';
							$htmlStr .= '<td class="blankRow" colspan="5"></td>';
						$htmlStr .= '</tr>';
						}
						$htmlStr .= '<tr class="childHead table-active">';
							$htmlStr .= '<th></th>';
							$htmlStr .= '<th>'. xl('Insurance Type') .'</th>';
							$htmlStr .= '<th>'. xl('Insurance Code') .'</th>';
							$htmlStr .= '<th>'. xl('Status') .'</th>';
							$htmlStr .= '<th>'. xl('Plan Start Date') .'</th>';
							$htmlStr .= '<th class="payerNotes">'. xl('Payer Notes') .'</th>';
						$htmlStr .= '</tr>';
						$htmlStr .= '<tr>';
							$htmlStr .= '<td></td>';
							$htmlStr .= '<td>'.$plan->insuranceType.'</td>';
							$htmlStr .= '<td>'.$plan->insuranceTypeCode.'</td>';
							$htmlStr .= '<td>'.$plan->status.'</td>';
							$htmlStr .= '<td>'.$plan_start_date.'</td>';
							$htmlStr .= '<td class="payerNotes">'.$payerNotes.'</td>';
						$htmlStr .= '</tr>';

						$htmlStr .= '<tr>';
						$htmlStr .= '<td class="benefitText table-warning" colspan="6">'. xl('Benefits') .'</td>';
						$htmlStr .= '</tr>';
						$htmlStr .= '<tr class="benefitTextHead table-active">';
							$htmlStr .= '<th></th>';
							$htmlStr .= '<th colspan="3">'. xl('Name') .'</th>';
							$htmlStr .= '<th>'. xl('Type') .'</th>';
							$htmlStr .= '<th>'. xl('Status') .'</th>';
						$htmlStr .= '</tr>';

						if(isset($plan->benefits) && is_array($plan->benefits) && count($plan->benefits) > 0) {
							foreach ($plan->benefits as $bi => $benefit) {
								$benefit_name = isset($benefit->name) ? $benefit->name : "";
								$benefit_type = isset($benefit->type) ? $benefit->type : "";
								$benefit_status = isset($benefit->status) ? $benefit->status : "";

								$htmlStr .= '<tr>';
									$htmlStr .= '<td width="20" class="details-control1"></td>';
									$htmlStr .= '<td colspan="3">'.$benefit_name.'</td>';
									$htmlStr .= '<td>'.$benefit_type.'</td>';
									$htmlStr .= '<td>'.$benefit_status.'</td>';
								$htmlStr .= '</tr>';
								$htmlStr .= '<tr>';
									$htmlStr .= '<td class="rowDetails" style="display:none" colspan="6">'.generateBenefits($benefit).'</td>';
								$htmlStr .= '</tr>';

							}
						} else {
							$htmlStr .= '<tr>';
							$htmlStr .= '<td class="empty-benefit-records" colspan="6">'. xl('No Records') .'</td>';
							$htmlStr .= '</tr>';
						}
					}
					$htmlStr .= '</tbody>';
					$htmlStr .= '</table>';
					
				} else {
					$htmlStr .= '<div class="empty-plans-records">';
						$htmlStr .= '<span>'. xl('No Records') .'</span>';
					$htmlStr .= '</div>';
				}
				$obj[$i] = htmlspecialchars(str_replace('\\','\\\\"',$htmlStr), ENT_QUOTES);
			}
		}
	}

	return $obj;
}

if($cnt != "" && $case_id != "" && $pid != "") {
?>
<html>
<head>
	<title><?php echo xlt('Covrage History'); ?></title>

	<?php Header::setupHeader(['common', 'opener', 'jquery', 'jquery-ui', 'datatables', 'datatables-colreorder', 'datatables-bs', 'oemr_ad']); ?>

    <style type="text/css">
        td.details-control::after,
        td.details-control1::after {
        	font-family: "Font Awesome 6 Free";
		    content: "\f078";
		    display: inline-block;
		    vertical-align: middle;
		    font-weight: 900;
		    float: right;
		    padding-right: 3px;
		    vertical-align: middle;
        }

        tr.details td.details-control::after,
        tr.details td.details-control1::after {
            font-family: "Font Awesome 6 Free";
		    content: "\f077";
		    display: inline-block;
		    vertical-align: middle;
		    font-weight: 900;
		    float: right;
		    padding-right: 3px;
		    vertical-align: middle;
        }

        .childTable,
        .statusTable {
        	/*width: 100%;
        	border-collapse: collapse;
        	font-size: 14px!important;
        	margin-bottom: 20px;*/
        }

        .payerNotes {
        	width: auto;
        	max-width: 400px;
        }

        .alignleft {
        	text-align: left!important;
        }

		.srNo {
			text-align: center;
    		vertical-align: text-top;
		}

		.payerNotesList {
			padding-left: 15px;
		}

		.benefitText {
			/*background-color: lightyellow;*/
    		text-align: center;
		}

		/*.benefitTextHead {
			background-color: whitesmoke;
		}*/

		.blankRow {
			height: 26px!important;
		}

		.empty-plans-records {
			text-align: center;
    		/*padding: 4px 10px;*/
		}

		.error-records-container {
			padding: 4px 10px;
			color: red;
		}

		.error-records-container .errors-container {
			/*padding: 8px 0px;*/
		}

		table.childTable .empty-benefit-records {
			text-align: center;
			/*padding: 4px 10px;*/
		}

		.bordered, .n-bordered {
			border-width: 1px !important;
			border-style: solid !important;
			border-collapse: collapse !important;	
		}

		.n-bordered{
			border: 0px solid !important;
		}

		.n-bordered > tbody > tr > td {
			/*border: 1px solid black;*/
		}

		.n-bordered > tr:first-child > td,
		.n-bordered > tbody > tr:first-child > td {
			border-top: 0px solid !important;
		}

		.n-bordered > tbody > tr:last-child > td{
			border-bottom: 0px solid !important;
		}

		.n-bordered > tbody > tr > td:first-child{
			border-left: 0px solid !important;
		}

		.n-bordered  > tbody > tr > td:last-child{
			border-right: 0px solid !important;
		}

		.t-bold {
			font-weight: 500;
		}
    </style>

</head>
<body>
<?php
	$historyData = CoverageCheck::getCoverageEligibilityHistoryData($pid, $case_id, $cnt);
	$javascriptObj = generateChildRow($historyData);
?>
	<div class="coverage_history_container">
	<div class="table-responsive table-container datatable-container c-table-bordered o-overlay">
	<table id="coverage_history_result" class="coverage_history_result table text table-sm" cellspacing="0" width="100%">
		<thead class="thead-dark">
			<tr>
				<th></th>
				<th><span class="headTitle"><?php echo xl('Date & Time'); ?><span></th>
				<th><span class="headTitle"><?php echo xl('Run By'); ?><span></th>
				<th><span class="headTitle"><?php echo xl('Elibility Status'); ?><span></th>
				<th><span class="headTitle"><?php echo xl('Coverage Status'); ?><span></th>
				<th><span class="headTitle"><?php echo xl('Insurance Name'); ?><span></th>
				<th><span class="headTitle"><?php echo xl('Policy Number'); ?><span></th>
				<th><span class="headTitle"><?php echo xl('Group Number'); ?><span></th>
				<th><span class="headTitle"><?php echo xl('Provider'); ?><span></th>
				<th><span class="headTitle"><?php echo xl('Effective Date'); ?><span></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ($historyData as $key => $item) {
				$verificationStatus = CoverageCheck::initStatusVerification($item);
				$verificationIcon = CoverageCheck::getEligibilityText($verificationStatus);

				if($verificationStatus == "eligible") {
					$verificationText = "<div class='statusContainer'>".$verificationIcon." <span><i>(Eligible)</i></span></div>";
				} else {
					$verificationText = "<div class='statusContainer'>".$verificationIcon." <span><i>(Not Eligible)</i></span></div>";
				}

				$effective_date = isset($item['effective_date']) ? date('Y-m-d', strtotime($item['effective_date'])) : '';
				$pr_fname = isset($item['pr_fname']) ? $item['pr_fname'] : '';
				$pr_lname = isset($item['pr_lname']) ? $item['pr_lname'] : '';

				?>
				<tr data-index-value="<?php echo $key; ?>">
					<td class="details-control"></td>
					<td><span class="cellText"><?php echo $item['updated_at']; ?><span></td>
					<td><span class="cellText"><?php echo CoverageCheck::getRunByUserName($item['uid']); ?><span></td>
					<td><span class="cellText"><?php echo $verificationText; ?><span></td>
					<td><span class="cellText"><?php echo $item['coverage_data_obj']['status']; ?><span></td>
					<td><span class="cellText"><?php echo $item['ic_name']; ?><span></td>
					<td><span class="cellText"><?php echo $item['policy_number']; ?><span></td>
					<td><span class="cellText"><?php echo $item['group_number']; ?><span></td>
					<td><span class="cellText"><?php echo $pr_fname.' '.$pr_lname; ?><span></td>
					<td><span class="cellText"><?php echo $effective_date; ?><span></td>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>
	</div>
	</div>
<script type="text/javascript">
	const dataValue = JSON.parse('<?php echo json_encode($javascriptObj); ?>');

	function format(index) {
		var html = '';
		if(typeof dataValue[index] !== 'undefined') {
			datahtml = dataValue[index];
			var doc = new DOMParser().parseFromString(datahtml, "text/xml");
			var title = $('<textarea />').html(datahtml).text();
		    html = title;
		}
        return html;
    }

	var table = jQuery('#coverage_history_result').DataTable({
		'initComplete': function(settings){
			 //Handle Footer
			 handleDataTable(this);
	     },
		'searching': false,
		'aaSorting': [],
		'scrollY': '100vh',
        'scrollCollapse': true,
        'pageLength': 50,
       	'responsive': {
			details: false
		}
	});

	jQuery('table').on('click', '.childTable td.details-control1', function (event) {
		event.stopPropagation();
		var $target = $(event.target);
		var tr = $target.closest("tr");
		var nEle = tr.next().find(".rowDetails");
		
		if (nEle.is(":visible")) {
			nEle.hide();
			tr.removeClass('details');
		} else {
			nEle.show();
			tr.addClass('details');
		}
	});

	jQuery('#coverage_history_result').on('click', 'td.details-control', function () {
      var tr = jQuery(this).closest('tr');
      var row = table.row(tr);

      if (row.child.isShown()) {
          // This row is already open - close it
          row.child.hide();
          tr.removeClass('details');
      } else {
          // Open this row
          row.child(format(tr.data('index-value')), "row-details").show();
          tr.addClass('details');
      }
    });

</script>
</body>
</html>
<?php
}
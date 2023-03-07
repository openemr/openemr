<?php

use OpenEMR\Core\Header;

include_once("../../globals.php");
include_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\Caselib;

$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';

function getCaseData($pid) {
	$dataSet = array();

	if(empty($pid)) {
		return $dataSet;
	}

	$result = sqlStatement("SELECT pd.pubpid, fc.* FROM form_cases fc left join patient_data pd on pd.pid = fc.pid WHERE fc.pid  = ? and fc.closed = 0 order by fc.id desc", $pid);
	while ($row = sqlFetchArray($result)) {
		$dataSet[] = $row;
	}

	return $dataSet;
}

$piTypeCases = array();
$cases = getCaseData($pid);

foreach ($cases as $ck => $case) {
	$liableData = Caselib::isLiablePiCaseByCase($case['id'], $pid, $case);

	if($liableData === true) {
		$piTypeCases[] = $case;
	}
}

$jdataSet = [];

?>
<html>
<head>
	<title><?php echo htmlspecialchars( xl('Select Case'), ENT_NOQUOTES); ?></title>
	<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'jquery-ui-base', 'fontawesome', 'main-theme']); ?>
	</script>

	<style type="text/css">
		.layoutContainer {
			display: grid;
			grid-template-rows: 1fr auto;
			height: 100%;
		}

		.footerContainer {
			padding: 15px;
    		text-align: right;
    		border-top: 1px solid #e5e5e5;
		}

		.caseTable tr td, 
		.caseTable tr th {
			font-size: 13px;
		}
	</style>

	<script type="text/javascript">
		function selMultiCaseItem(caseList = []) {
		    if (opener.closed || ! opener.setMulticase)
		        alert("<?php echo xlt('The destination form was closed; I cannot act on your selection.'); ?>");
		    else
		        opener.setMulticase(caseList);
		        dlgclose();
		    return false;
		 }
	</script>
</head>
<body>
	<div class="layoutContainer">
		<div class="mainContainer">
			<?php if(!empty($piTypeCases)) { ?>
				<table class="caseTable dataTable display">
					<tr>
						<th width="10"><!-- <input type="checkbox" id="checkAll" value="1" /> --></th>
						<th>Case</th>
						<th>Email Addresses</th>
					</tr>

					<?php
						foreach ($piTypeCases as $pik => $piItem) {
							$jdataSet['case_'.$piItem['id']] = $piItem;
							?>
							<tr>
								<td><input type="checkbox" class="sel_check" name="case_<?php echo $piItem['id']; ?>" value="<?php echo $piItem['id']; ?>" /></td>
								<td><?php echo $piItem['id']; ?></td>
								<td><?php echo $piItem['notes']; ?></td>
							</tr>
							<?php
						}
					?>
				</table>
			<?php } else { ?>
				<h1>No records found.</h1>
			<?php } ?>
		</div>
		<div class="footerContainer modal-footer">
			<button type="button" class="btn btn-sm submitbtn" id="selectsubmitbtn">Submit</button>
			<button type="button" class="btn btn-default btn-sm" data-dismiss="modal" style="default btn-sm" onClick="dlgclose();">Close</button>
		</div>
	</div>

<script type="text/javascript">

	var jDataSet = <?php echo json_encode($jdataSet); ?>;
	
	$(document).ready(function(){
		$("#checkAll").click(function() {
			let isAllChecked = $(this).is(':checked');

			if(isAllChecked === true) {
				$('.sel_check').each(function() {
					$(this).prop('checked', true);
				});
			} else {
				$('.sel_check').each(function() {
					$(this).prop('checked', false);
				});
			}
		});

		$(document).on('click', '.sel_check', function() {      
    		$('.sel_check').not(this).prop('checked', false);      
		});

		//Handle Mulitcase select
	    $("#selectsubmitbtn").click(function() {
	        let selectedItemList = [];
	        
	        $(".sel_check:checked").each(function(){
	            let uId = $(this).val();
	            if(jDataSet.hasOwnProperty('case_'+uId)) {
	                selectedItemList.push(jDataSet['case_'+uId]);
	            }
	        });

	        if(selectedItemList.length <= 0) {
	            alert('Please select case from the list.');
	            return false;
	        }

	        selMultiCaseItem(selectedItemList);
	    });
	});
</script>
</body>
</html>
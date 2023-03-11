<?php

include_once("../../globals.php");
include_once("$srcdir/acl.inc");
include_once("$srcdir/options.inc.php");
include_once($GLOBALS['srcdir'].'/OemrAD/oemrad.globals.php');

use OpenEMR\OemrAd\Caselib;
use OpenEMR\Core\Header;

$form_act_val = isset($_REQUEST['form_act']) ? $_REQUEST['form_act'] : "";
$form_action_val = isset($_REQUEST['form_action']) ? $_REQUEST['form_action'] : '';
$form_replace_field_val = isset($_REQUEST['form_replace_field']) ? $_REQUEST['form_replace_field'] : '';
$form_replace_with_field_val = isset($_REQUEST['form_replace_with_field']) ? $_REQUEST['form_replace_with_field'] : '';
$errorList = array();

$formActionList = array(
	"replace" => "Replace",
	"remove" => "Remove"
);

function getPICaseManageData($case_id) {
	$resultItem = array();

	if(!empty($case_id)) {
		$result = sqlStatement("SELECT * FROM vh_pi_case_management_details WHERE case_id = ? AND field_name = 'lp_contact' ", array($case_id));

		while ($row = sqlFetchArray($result)) {
			$resultItem[] = $row;
		}
	}

	return $resultItem;
}

function getCaseData($email = '') {
	$dataSet = array();

	if(empty($email)) {
		return $dataSet;
	}

	$result = sqlStatement("SELECT pd.pubpid, fc.* FROM form_cases fc left join patient_data pd on pd.pid = fc.pid WHERE fc.notes  != '' AND fc.notes like '%".$email."%'");

	while ($row = sqlFetchArray($result)) {
		$dataSet[] = $row;
	}

	return $dataSet;
}

function getAbookData($email) {
	if(!empty($email)) {
		return sqlQuery("SELECT * from users u where email = ?", array($email));
	}

	return false;
}

function getNewNotes($action, $notes = '', $find_val = '', $replace_val = '') {
	$newNotes = $notes;

	if(!empty($action) && !empty($notes)) {
		if($action == "replace") {
			$newNotes = str_ireplace($find_val,$replace_val,$notes);
		} else if($action == "remove") {
			$c_emails = array_filter(explode(",",$notes));
			$c_emails = array_map('trim',$c_emails);

			$t_emails = $c_emails;

			$filteredArray = array_filter($t_emails, function($element) use($find_val){
  				return isset($element) && $element == $find_val;
			});

			foreach ($filteredArray as $fak => $faItem) {
				unset($t_emails[$fak]);
			}

			$newNotes = implode(", ", $t_emails);
		}
	}

	return $newNotes;
}

function validateEmail($email) {
    if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    }
    else {
        return false;
    }
}

function insertPICaseManagmentDetails($case_id = '', $data = array()) {
	if(!empty($case_id) && !empty($data)) {
		foreach ($data as $dk => $dItem) {
			if(!empty($dk)) {
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

function doProcessData($doCheck = true, $action_type = '', $find_val = '', $replace_val = '') {
	global $errorList;

	if(!empty($action_type)) {
		$cdList = array();
		$cabookData = array();
		$rabookData = array();
		$statusDataList = array();

		try {
			if(!empty($find_val)) {
				$cabookData = getAbookData($find_val);
				$cdList = getCaseData($find_val);
			}

			if(!empty($replace_val)) {
				$rabookData = getAbookData($replace_val);
				if($rabookData['abook_type'] !== "Attorney") {
					throw new \Exception('Replace user is not "Attorney" type.');
				}
			}

			foreach ($cdList as $cdk => $cdItem) {
				$ciNotes = isset($cdItem['notes']) ? $cdItem['notes'] : "";
				$ciid = isset($cdItem['id']) ? $cdItem['id'] : "";
				$cipubpid = isset($cdItem['pubpid']) ? $cdItem['pubpid'] : "";
				$cabookId = isset($cabookData['id']) ? $cabookData['id'] : "";
				$rabookId = isset($rabookData['id']) ? $rabookData['id'] : "";
				$nNotes = getNewNotes($action_type, $ciNotes, $find_val, $replace_val);

				if($doCheck === false) {
					if($action_type == "replace") {
						if(!empty($find_val) && !empty($replace_val)) {
							$nNotes = getNewNotes($action_type, $ciNotes, $find_val, $replace_val);

							if(!empty($ciid)) {
								//Replace Note Values
								sqlStatement("UPDATE form_cases SET `notes` = ? WHERE `id` = ?", array($nNotes, $ciid));
								
								//Replace Lawyer values
								if(!empty($cabookId) && !empty($rabookId)) {
									sqlStatement("UPDATE vh_pi_case_management_details SET field_value = ? WHERE field_name = 'lp_contact' AND field_value = ? AND case_id = ?", array($rabookId, $cabookId, $ciid));
								}


								$dataEmail = array('lp_contact' => array());
								$new_emails = array_filter(explode(",",$nNotes));
								$new_emails = array_map('trim',$new_emails);

								foreach ($new_emails as $cek => $ceItem) {
									if(validateEmail($ceItem)) {
										$ceAbookData = getAbookData($ceItem);
										if(!empty($ceAbookData)) {
											$dataEmail['lp_contact'][] = $ceAbookData['id'];
										}
									}
								}

								$lpContactData = Caselib::getPICaseManageData($ciid, 'lp_contact');
								$lpList1 = array();
								$lpList2 = array();

								foreach ($lpContactData as $lpck => $lpcItem) {
									if(isset($lpcItem['field_value']) && !empty($lpcItem['field_value'])) {
										$lpList1[] = $lpcItem['field_value'];
									}
								}

								if(isset($dataEmail['lp_contact']) && !empty($dataEmail['lp_contact'])) {
									$lpList2 = $dataEmail['lp_contact'];
								}

								$diff2 = Caselib::getArrayValDeff($lpList2, $lpList1);
								$diffa2 = Caselib::getAbookData($diff2);
								$t_emails = array('lp_contact' => array());
								$lastIndex = count($lpList1);

								foreach ($diff2 as $dak2 => $daI2) {
									if(isset($diffa2['id_'.$daI2]) && !empty($diffa2['id_'.$daI2])) {
										$daItem2 = $diffa2['id_'.$daI2];

										if(isset($daItem2['id']) && !empty($daItem2['email'])) {
											$t_emails['lp_contact'][$lastIndex] = $daItem2['id'];
											$lastIndex++;
										}
									}
								}

								if(!empty($t_emails) && !empty($ciid)) {
									//Add New Info
									insertPICaseManagmentDetails($ciid, $t_emails);
								}

								// //Replace Note Values
								// sqlStatement("UPDATE form_cases SET `notes` = ? WHERE `id` = ?", array($nNotes, $ciid));
								
								// //Replace Lawyer values
								// if(!empty($cabookId) && !empty($rabookId)) {
								// 	sqlStatement("UPDATE vh_pi_case_management_details SET field_value = ? WHERE field_name = 'lp_contact' AND field_value = ? AND case_id = ?", array($rabookId, $cabookId, $ciid));
								// }
							}
						}
					} else if($action_type == "remove") {
						if(!empty($find_val)) {
							$nNotes = getNewNotes($action_type, $ciNotes, $find_val, $replace_val);

							if(!empty($ciid)) {
								//Replace Note Values
								sqlStatement("UPDATE form_cases SET `notes` = ? WHERE `id` = ?", array($nNotes, $ciid));
								
								//Remove Lawyer values
								if(!empty($cabookId)) {
									sqlStatement("DELETE FROM vh_pi_case_management_details WHERE field_name = 'lp_contact' AND `field_value` = ? AND case_id = ? ", array($cabookId, $ciid));
								}
							}
						}
					}
				}

				$statusDataList[] = array(
					'case_id' => $ciid,
					'case_pub_pid' => $cipubpid,
					'case_notes' => $ciNotes,
					'case_new_notes' => $nNotes
				);
			}
		} catch (Exception $e) {
			$errorList[] = $e->getMessage();
		}
	}

	return $statusDataList;
}

$statusDataList = array();

if(isset($_REQUEST['form_act'])) {
	if($_REQUEST['form_act'] == "Submit") {
		$statusDataList = doProcessData(false, $form_action_val, $form_replace_field_val, $form_replace_with_field_val);
	} else if($_REQUEST['form_act'] == "Check") {
		$statusDataList = doProcessData(true, $form_action_val, $form_replace_field_val, $form_replace_with_field_val);
	}
}

$fieldc1 = "hide";
$fieldc2 = "hide";
$isReadOnly = false;

if($form_action_val == "replace") { $fieldc1 = ""; $fieldc2 = ""; }
if($form_action_val == "remove") { $fieldc1 = ""; }

if($form_act_val != "") {
	$isReadOnly = true;
}

?>
<html>
<head>
	<title><?php echo xlt('Attorney in Case'); ?></title>
	<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'datatables', 'datatables-colreorder', 'datatables-bs']); ?>

	<script type="text/javascript" src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js"></script>

	<style type="text/css">
		.page-title h2 {
			margin-top: 20px;
			margin-bottom: 10px;
			font-size: 30px;
			font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
			font-weight: normal;
		}

		/*DataTable Custom Filter*/
		.datatable_filter {
			margin-bottom: 40px;
		}
		.datatable_filter table {
			table-layout: fixed;
			width: 100%;
		}
		.datatable_filter table tr > td {
			padding: 4px 1px;
		}
		.datatable_filter label {
			font-size: 14px;
		}
		.datatable_filter .date_inline {
			display: grid;
			grid-template-columns: auto auto;
			grid-column-gap: 3px;
		}
		.datatable_filter select,
		.datatable_filter input[type="text"] {
			padding: 5px;
			margin: 3px 0px;
			width: 100%;
		}
		.datatable_filter select[readonly],
		.datatable_filter input[readonly] {
			pointer-events: none;
			opacity: 0.4;
		}
		.datatable_filter button[type="submit"] {
			cursor: pointer;
		}
		.datatable_filter button[type="submit"]:disabled {
			opacity: 0.4;
		}

		#theform {
			margin: 0px;
		}

		.innerformContainer {
			display: grid;
    		grid-template-rows: 1fr auto;
    		height: 100%;
		}

		.fieldLabel { 
			/*font-size:10pt; */
		}

		.form_select {
			background: rgb(255, 255, 255);
		    border: 1px solid rgb(68, 68, 68);
		    padding: 5px;
    		margin: 3px 0px;
    		width: 100%;
		}

		.form_input {
			background: rgb(255, 255, 255);
		    border: 1px solid rgb(68, 68, 68);
		    padding: 5px;
    		margin: 3px 0px;
    		width: 100%;
		}

		.tableContainer {
			margin-top: 10px;
			margin-bottom: 20px;
		}

		.hide {
			display: none;
		}

		#data_report_container {
			margin-bottom: 60px;
		}

		.fieldContainer {
			display: grid;
		    grid-template-columns: 1fr auto;
		    align-items: center;
		    grid-column-gap: 3px;
		}

		.fieldContainer .css_button {
			padding: 6px 12px 6px !important;
			border-radius: 0px !important; 
		}

	</style>
</head>
<body class="body_top">
	<div class="page-title">
    	<h2>Attorney in Case</h2>
	</div>

	<div class="dataTables_wrapper datatable_filter">
		<form method='post' name='theform' id="theform" action='replace_remove_attorney.php'>
			<input type='hidden' name='form_act' value='' />
			<table>
				<tr>
					<td width="400">
						<label class="txtTitle">
							Active:
							<select name="form_action" class="form_select actionSelect" <?php echo $isReadOnly === true ? "readonly" : ""; ?>>
					  		<option value="">Please Select</option>
						  		<?php
						  			foreach ($formActionList as $alk => $alItem) {
						  				$opSelected = ($alk == $form_action_val) ? 'selected' : '';
						  				?>
						  				<option value="<?php echo $alk; ?>" <?php echo $opSelected ?>><?php echo $alItem; ?></option>
						  				<?php
						  			}
						  		?>
						  	</select>
						</label>
					</td>
					<td class="replaceFieldContainer <?php echo $fieldc1; ?>">
						<span class="fieldLabel"><b><?php echo xlt('Find'); ?>:</b></span>
					  	<div class="fieldContainer">
					  		<input type="text" name="form_replace_field" class="form_input filterInput" value="<?php echo $form_replace_field_val ?>" <?php echo $isReadOnly === true ? "readonly" : ""; ?> />
					  		<?php if($isReadOnly === false) { ?>
				  				<a class='medium_modal css_button search_user_btn' href='<?php echo $GLOBALS['webroot']. '/interface/forms/cases/php/find_user_popup.php?abook_type=Attorney'; ?>'><span> <?php echo xlt('Search'); ?></span></a>
				  			<?php } ?>
				  		</div>
				  	</td>
				  	<td class="replacewithFieldContainer <?php echo $fieldc2; ?>">
						<span class="fieldLabel"><b><?php echo xlt('Replace'); ?>:</b></span>
					  	<div class="fieldContainer">
						  	<input type="text" name="form_replace_with_field" class="form_input filterInput" value="<?php echo $form_replace_with_field_val ?>" <?php echo $isReadOnly === true ? "readonly" : ""; ?> />
						  	<?php if($isReadOnly === false) { ?>
						  		<a class='medium_modal css_button search_user_btn' href='<?php echo $GLOBALS['webroot']. '/interface/forms/cases/php/find_user_popup.php?abook_type=Attorney'; ?>'><span> <?php echo xlt('Search'); ?></span></a>
						  	<?php } ?>
					  	</div>
				  	</td>
				</tr>
				<tr>
					<td>
						<?php if($form_act_val == "" && empty($statusDataList)) { ?>
							<input type='button' onclick="doSubmit('Check')" value='<?php echo xla('Validate'); ?>' />
						<?php } ?>

						<?php if($form_act_val === "Check" && !empty($statusDataList)) { ?>
							<input type='button' onclick="doSubmit('Submit')" value='<?php echo xla('Execute'); ?>' />
						<?php } ?>
						<input type='button' onclick="doSubmit('Cancel')" value='<?php echo xla('Cancel'); ?>' />
					</td>
				</tr>
			</table>
		</form>
	</div>
	<div id="data_report_container">
		<?php if(!empty($form_act_val)) { ?>
		<?php if(!empty($statusDataList)) { ?>

		<?php
			if(!empty($form_act_val == "Submit")) {
				?>
				<h3>Total Updated Records: <?php echo count($statusDataList); ?></h3>
				<?php
			}
		?>

		<div class="statusDataTableContainer">
			<table class="statusDataTable dataTable display ">
				<thead>
					<th width="50" align="left">Sr.</th>
					<th width="100" align="left">Case Id</th>
					<th width="100" align="left">Chart No</th>
					<th align="left">Notes</th>
					<th align="left">New Notes</th>
				</thead>
				<tbody>
					<?php
						foreach ($statusDataList as $sdl => $sdItem) {
							?>
							<tr>
								<td><?php echo ($sdl + 1) ?></td>
								<td><?php echo $sdItem['case_id'] ?></td>
								<td><?php echo $sdItem['case_pub_pid'] ?></td>
								<td><?php echo $sdItem['case_notes'] ?></td>
								<td><?php echo $sdItem['case_new_notes'] ?></td>
							</tr>
							<?php
						}
					?>
				</tbody>
			</table>
		</div>
		<?php } else { ?>
			<h3>No Results Found</h3>
		<?php } ?>
		<?php } ?>
	</div>

<script type="text/javascript">
	var currentEle = null;

	<?php if(!empty($errorList)) { ?>
		setTimeout(function() {
			alert('<?php echo implode("\n", $errorList) ?>');
		},100);
	<?php } ?>

	function doSubmit(type) {
		var form_action_val = $('select[name="form_action"]').val();
		var form_replace_field_val = $('input[name="form_replace_field"]').val();
		var form_replace_with_field_val = $('input[name="form_replace_with_field"]').val();

		$('input[name="form_act"]').val(type);

		if(type == "Cancel") {
			window.location = "<?php echo $GLOBALS['webroot']; ?>/interface/reports/myreports/replace_remove_attorney.php";
			return false;
		}

		if(form_action_val == "") {
			alert('Please select action.');
			return false;
		}

		if(form_action_val == "replace") {
			if(form_replace_field_val == "") {
				alert('Please enter "Find" value.');
				return false;
			}

			if(form_replace_with_field_val == "") {
				alert('Please enter "Replace" value.');
				return false;
			}
		}

		if(form_action_val == "remove") {
			if(form_replace_field_val == "") {
				alert('Please enter "Find" value.');
				return false;
			}
		}


		$('#theform').submit();
	}

	// This is for callback by the find-user popup.
	function setuser(uid, uname, fname, username, status, noteType, email = '') {
		if(window.currentEle && window.currentEle != null) {
			$(window.currentEle).find('.filterInput').eq(0).val(email).trigger('change');
		}
	}

	$(document).ready(function() {
		$(document).on('click', '.medium_modal', function(e) {
			window.currentEle = $(this).parent();
	        e.preventDefault();
	        e.stopPropagation();
	        dlgopen('', '', 700, 400, '', '', {
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

		$('.actionSelect').change(function(){
			var actionVal = $(this).val();

			$('.replaceFieldContainer').addClass('hide');
			$('.replacewithFieldContainer').addClass('hide');

			if(actionVal == "replace") {
				$('.replaceFieldContainer').removeClass('hide');
				$('.replacewithFieldContainer').removeClass('hide');
			}

			if(actionVal == "remove") {
				$('.replaceFieldContainer').removeClass('hide');
			}
		});
	});	
</script>

</body>
</html>
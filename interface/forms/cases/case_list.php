<?php

$fake_register_globals=false;
$sanitize_all_escapes=true;

include_once("../../globals.php");
include_once($GLOBALS['srcdir'].'/api.inc');
include_once($GLOBALS['srcdir'].'/forms.inc');
include_once($GLOBALS['srcdir'].'/calendar.inc');
include_once($GLOBALS['srcdir'].'/patient.inc');
include_once($GLOBALS['srcdir'].'/options.inc.php');
include_once($GLOBALS['srcdir'].'/translation.inc.php');
include_once($GLOBALS['srcdir'].'/formatting.inc.php');
include_once($GLOBALS['srcdir'].'/pnotes.inc');
include_once($GLOBALS['fileroot'].'/custom/code_types.inc.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/list_tools.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/billing_tools.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/approve.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtform.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtpatient.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/printvisit.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/dashboard.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/fyi.class.php');

// COMMENT OUT FOR VERSIONS BELOW 5.0.1
use OpenEMR\Core\Header;

$mode = 'list';
if(isset($_GET['mode'])) $mode = strip_tags($_GET['mode']);
$type = 'active';
if(isset($_GET['type'])) $type = strtolower(strip_tags($_GET['type']));

$eid = $case_id = $pid = $encounter = $pop_mode = $caller = '';
if(isset($_SESSION['pid'])) $pid = $_SESSION['pid'];
if(isset($_GET['pid'])) $pid = strip_tags($_GET['pid']);
if(isset($_GET['enc'])) $encounter = strip_tags($_GET['enc']);
if(isset($_GET['popup'])) $pop_mode = strip_tags($_GET['popup']);
if(isset($_GET['caller'])) $caller = strip_tags($_GET['caller']);
$cancel_href = 'href="'.$GLOBALS['form_exit_url'].'"';
if($pop_mode) {
	$cancel_href = 'href="javascript: window.close()"';
	if($v_major > 4 && ($v_minor || $v_patch)) {
		$cancel_href = 'href="javascript: dlgclose()"';
	}
}
if($caller == 'patient') $cancel_href = 'href="' . $GLOBALS['rootdir'] . 
	'/patient_file/summary/demographics.php"';

$qtrParams = '';
if(!empty($mode)) {
	$qtrParams .= '&mode='.$mode;
}

if(!empty($pop_mode)) {
	$qtrParams .= '&popup='.$pop_mode;
}

if(!$pid) die("No Patient ID Was Found");

$cases = array();
$sql = 'SELECT form_cases.*, (SELECT COUNT(*) FROM case_appointment_link AS ' .
	'ca LEFT JOIN openemr_postcalendar_events AS oe ON (ca.pc_eid = oe.pc_eid) ' .
	'WHERE pid = ? AND oe.pc_case = form_cases.id) AS enc_count FROM '.
	'form_cases WHERE pid = ? AND ';
if($type == 'active') $sql .= 'closed = 0 AND ';
$sql .= 'activity > 0 ORDER BY id DESC';
$res = sqlStatement($sql, array($pid, $pid));
while($row = sqlFetchArray($res)) {
	$cases[] = $row;
}
?>


<?php
$js_location = $GLOBALS['webroot'] . '/library/js';
if($v_major > 4) $js_location = $GLOBALS['assets_static_relative'];
?>
<html>
<head>
	<title><?php echo xl('Active Case List'); ?></title>
	<?php Header::setupHeader(['common', 'opener', 'jquery', 'jquery-ui', 'datatables', 'datatables-colreorder', 'datatables-bs', 'oemr_ad']); ?>
	<!-- <style type="text/css">@import url(<?php //echo $GLOBALS['webroot']; ?>/library/dynarch_calendar.css);</style> -->
	<link rel="stylesheet" href="<?php echo $GLOBALS['css_header'];?>" type="text/css">
	<link rel="stylesheet" href="<?php echo $GLOBALS['webroot'];?>/library/wmt/wmt.default.css" type="text/css">
	<script src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtpopup.js" type="text/javascript"></script>

	<style type="text/css">
		.case-page-container {
			width: 100%; 
			/*padding-bottom:20px !important;*/
		}

		.inner-page-container {
			display: grid;
		    height: 100%;
		    grid-template-rows: 1fr auto;
		    overflow: hidden;
		    position: relative;
		    height: 100vh;
		}

		.table-container {
			height: 100%;
			min-height: 220px;
		}
	</style>

	<script type="text/javascript">
		function set_case(id, dt, desc) {
			<?php if($v_major > 4 && ($v_minor || $v_patch)) { ?>
				if(typeof opener.setCase !== 'function') {
					alert('The case can not be set, something did not load properly');
				} else {
					opener.setCase(id, dt, desc);
					dlgclose();
				}
			<?php } else { ?>
				if(opener.closed || !opener.setCase) {
					alert('The destination form was closed, cannot select this case');
				} else {
					opener.setCase(id, dt, desc);
					window.close();
					return false;
				}
			<?php } ?>
		}

		$(document).ready(function() {
			table = jQuery('#case_list').DataTable({
				'initComplete': function(settings){
					//Handle Footer
					handleDataTable(this, true);
			    },
				'columnDefs': [
					 {
					    'targets': 0,
					    'checkboxes': {
					       'selectRow': true
					    }
					 }
				],
				'select': {
					'style': 'multi'
				},
				'order': [[1, 'asc']],
				'pageLength': -1,
				'bLengthChange': true,
				'ordering': false,
				'autoWidth':true,
				'scrollY': '100vh',
	    		'scrollCollapse': true,
	    		'responsive': {
				    details: false
				},
				'paging': false,
				'bFilter': false, 
				'bInfo': false
			});
		});
	</script>
</head>
<body>
	<div class="case-page-container">
		<div class="inner-page-container p-1 <?php echo $pop_mode !== "pop" ? "pb-3" : ""; ?>">
			<div class="table-responsive table-container datatable-container c-table-bordered footer-p o-overlay text">
				<table id="case_list" class="table table-striped text table-sm mt-0" class="border-0 display">
					<thead class="thead-dark">
					<tr>
						<th scope="col"><?php echo xl('Case Date'); ?></th>
						<th scope="col"><?php echo xl('Case Number'); ?></th>
						<th scope="col"><?php echo xl('Case Description'); ?></th>
						<th scope="col"><?php echo xl('Injury Date'); ?></th>
						<th scope="col"><?php echo xl('Empl'); ?></th>
						<th scope="col"><?php echo xl('Auto'); ?></th>
						<th scope="col"><?php echo xl('Cash'); ?></th>
						<th scope="col"><?php echo xl('# Encs'); ?></th>
						<th scope="col"><?php echo xl('Closed'); ?></th>
						<?php if($mode == 'choose') echo '<th scope="col" width="150">&nbsp;</th>'; ?>
					</tr>
					</thead>
					<tbody>
					<?php 
					$bgcolor = '#FFFFFF';
					if(count($cases) > 0) { 
						foreach($cases as $case) {
							$bgcolor = ($bgcolor == '#FFFFFF') ? '#E0E0E0' : '#FFFFFF';
							$href = FORMS_DIR_JS . 'cases/view.php?id=' . $case{'id'} . '&pid=' .
								$case{'pid'} . '&list_mode=' . $mode . '&list_popup=' . $pop_mode . 
								'&popup=no';
							if($caller) $href .= '&caller=' . $caller;
							$choose_href = 'onclick="set_case(\'' . $case['id'] . '\', \'' . oeFormatShortDate($case['case_dt']) . '\',\'' . base64_encode($case['case_description']) .'\');"';
							$onClickRow = ($mode != 'choose') ? "window.location = '".$href."';" : "";
					?>
							<tr style="" onclick="<?php echo $onClickRow; ?>">
								<td class="align-middle" <?php echo $mode == 'choose' ? $choose_href : ''; ?> >&nbsp;<?php echo oeFormatShortDate($case{'case_dt'}); ?></td>
								<td class="align-middle" <?php echo $mode == 'choose' ? $choose_href : ''; ?> >&nbsp;<?php echo htmlspecialchars($case{'id'}, ENT_QUOTES); ?></td>
								<td class="align-middle" <?php echo $mode == 'choose' ? $choose_href : ''; ?> >&nbsp;<?php echo htmlspecialchars($case{'case_description'}, ENT_QUOTES); ?></td>
								<td class="align-middle" <?php echo $mode == 'choose' ? $choose_href : ''; ?> >&nbsp;<?php echo oeFormatShortDate($case{'injury_date'}); ?></td>
								<td class="align-middle" <?php echo $mode == 'choose' ? $choose_href : ''; ?> >&nbsp;<?php echo $case{'employment_related'} ? 'Yes' : 'No'; ?></td>
								<td class="align-middle" <?php echo $mode == 'choose' ? $choose_href : ''; ?> >&nbsp;<?php echo $case{'auto_accident'} ? 'Yes' : 'No'; ?></td>
								<td class="align-middle" <?php echo $mode == 'choose' ? $choose_href : ''; ?> >&nbsp;<?php echo $case{'cash'} ? 'Yes' : 'No'; ?></td>
								<td class="align-middle" <?php echo $mode == 'choose' ? $choose_href : ''; ?> >&nbsp;<?php echo $case{'enc_count'}; ?></td>
								<td class="align-middle" <?php echo $mode == 'choose' ? $choose_href : ''; ?> >&nbsp;<?php echo $case{'closed'} ? 'Yes' : 'No'; ?></td>
								<?php if($mode == 'choose') { ?>
								<td class="text-right"><div><a href="javascript:;" class="btn btn-primary" onclick="window.location = '<?php echo $href; ?>';" ><span>View / Edit</span></a></div>
								</td>
								<?php // onclick="wmtOpen('$href;', 'Case Editing', '90%', '90%');"; ?>
								<?php } ?>
							</tr>
					<?php }
			  		} else {
						//echo '<tr><td colspan="10" class="label"><center>'. xl('No Active Cases On File'). '</center></td></tr>';
					}
					?>
					</tbody>
				</table>
			</div>
			<div class="d-flex px-0 pt-3">
				<div>
					<a href="javascript:;" class="btn btn-primary btn-sm" onclick="window.location='<?php echo FORMS_DIR_JS; ?>cases/new.php?pid=<?php echo $pid; ?>&list_mode=<?php echo $mode; ?>&list_popup=<?php echo $pop_mode; ?>&popup=no<?php echo $caller ? "&caller=$caller" : ""; ?>';"><span><?php echo xl('Add Another'); ?></span></a>
				</div>
				<?php if($type == 'active') { ?>
				<div>
					<a href="javascript:;" class="btn btn-primary btn-sm ml-2" onclick="window.location='<?php echo FORMS_DIR_JS; ?>cases/case_list.php?pid=<?php echo $pid; ?>&type=all<?php echo $qtrParams; ?>';"><span><?php echo xl('Show ALL Cases'); ?></span></a>
				</div>
				<?php } else { ?>
				<div>
					<a href="javascript:;" class="btn btn-primary btn-sm ml-2" onclick="window.location='<?php echo FORMS_DIR_JS; ?>cases/case_list.php?pid=<?php echo $pid.$qtrParams; ?>';"><span><?php echo xl('Only Active Cases'); ?></span></a>
				</div>
				<?php } ?>
				<div class="ml-auto">
			   		<a class="btn btn-secondary btn-sm" tabindex="-1" <?php echo $cancel_href; ?> ><span><?php echo xl('Cancel'); ?></span></a>
				</div>
			</div>
		</div>
	</div>
</body>
</html>

<?php
/** **************************************************************************
 *	QUEST/COMMON.PHP
 *
 *	Copyright (c)2014 - Williams Medical Technology, Inc.
 *
 *	This program is licensed software: licensee is granted a limited nonexclusive
 *  license to install this Software on more than one computer system, as long as all
 *  systems are used to support a single licensee. Licensor is and remains the owner
 *  of all titles, rights, and interests in program.
 *  
 *  Licensee will not make copies of this Software or allow copies of this Software 
 *  to be made by others, unless authorized by the licensor. Licensee may make copies 
 *  of the Software for backup purposes only.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 *  FOR A PARTICULAR PURPOSE. LICENSOR IS NOT LIABLE TO LICENSEE FOR ANY DAMAGES, 
 *  INCLUDING COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL 
 *  DAMAGES, CONNECTED WITH OR RESULTING FROM THIS LICENSE AGREEMENT OR LICENSEE'S 
 *  USE OF THIS SOFTWARE.
 *
 *  @package lablink
 *  @subpackage quest
 *  @version 2.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 * 
 *************************************************************************** */
require_once("../../globals.php");
require_once("{$GLOBALS['srcdir']}/options.inc.php");
require_once("{$GLOBALS['srcdir']}/lists.inc");
require_once("{$GLOBALS['srcdir']}/wmt/wmt.class.php");
require_once("{$GLOBALS['srcdir']}/wmt/wmt.include.php");

use OpenEMR\Core\Header;

// grab inportant stuff
$id = '';
$generated = false;
$print = $_REQUEST['print'];
if ($viewmode) $id = $_REQUEST['id'];
$popup = ($popup)? $popup : $_REQUEST['pop'];
if (! $pid) $pid = $_SESSION['pid'];
if (! $lab_id) {
	$lab = sqlQuery("SELECT ppid FROM procedure_providers WHERE npi = 'QUEST' LIMIT 1");
	$lab_id = $lab['ppid'];
}

$client_id = false;
$params = sqlQuery("SELECT setting_value FROM user_settings WHERE setting_label = ?",array("wmt::client_id"));
if ($params['setting_value']) $client_id = $params['setting_value'];

$form_name = 'quest';
$form_title = 'Quest Diagnostics';
$form_table = 'form_quest';
$order_table = 'procedure_order';
$item_table = 'procedure_order_code';
$aoe_table = 'procedure_answers';

$save_url = $rootdir.'/forms/'.$form_name.'/save.php';
$validate_url = $rootdir.'/forms/'.$form_name.'/validate.php';
$submit_url = $rootdir.'/forms/'.$form_name.'/submit.php';
$abort_url = $rootdir.'/patient_file/summary/demographics.php';
$reload_url = $rootdir.'/patient_file/encounter/view_form.php?formname='.$form_name.'&id=';
$cancel_url = $rootdir.'/patient_file/encounter/encounter_top.php';
$document_url = $GLOBALS['web_root'].'/controller.php?document&retrieve&patient_id='.$pid.'&document_id=';

// date fix
function goodDate($date) {
	if ($date == '') $date = FALSE;
	if ($date == 0) $date = FALSE;
	if (strtotime($date) === FALSE) $date = FALSE;
	if (strtotime($date) == 0) $date = FALSE;
	if (!strtotime($date)) $date = FALSE;
	if ($date == '000-00-00 00:00:00') $date = FALSE;
	return $date;
}

/* RETRIEVE FORM DATA */
try {
	$order_date = date('Y-m-d');
	$order_data = new wmtOrder($form_name, $id);
	if ($order_data->id && goodDate($order_data->order_datetime)) $order_date = date('Y-m-d',strtotime($order_data->order_datetime));
	if ($order_data->user == 'system') $generated = true;
	if ($order_data->patient_id) $pid = $order_data->patient_id;
	if (! $pid) die ("Missing patient identifier!!");
	if (! $encounter && $order_data->encounter_id) $encounter = $order_data->encounter_id;
	if (! $encounter) die ("Missing current encounter identifier!!");
	 
	$pat_data = wmtPatient::getPidPatient($pid);
	$enc_data = wmtEncounter::getEncounter($encounter);
	$ins_list = wmtInsurance::getPidInsDate($pid,$order_date);
	if ($GLOBALS['wmt::case_ins_pick']) {
		$case_data = sqlQueryNoLog("SELECT `enc_case` FROM `case_appointment_link` WHERE `encounter` = ?",array($encounter));
		if ($case_data) $ins_list = wmtInsurance::getPidInsCase($pid,$case_data['enc_case']);
	}
// if ($pid == '56557') var_dump($ins_list);	
	$lab_id = ($order_data->lab_id) ? $order_data->lab_id : $lab_id; // use order if available
	$lab_data = sqlQuery("SELECT * FROM procedure_providers WHERE ppid = ?",array($lab_id));

	// fetch order detail records
	$item_list = wmtOrderItem::fetchItemList($order_data->order_number, true);
}
catch (Exception $e) {
	die ("FATAL ERROR ENCOUNTERED: " . $e->getMessage());
	exit;
}

// get quest site id
$siteid = ListLook($enc_data->facility_id, 'Quest_Site_Identifiers');
if (!$siteid || $siteid == '* Not Found *') $siteid = $lab_data['send_fac_id'];

// set form status
$status = 'i'; // incomplete and pending
if ($order_data->id && $order_data->status)
	$status = $order_data->status;
if ($report_data->id && $report_data->status)
	$status = $report_data->status;

// VALIDATE INSTALL
$invalid = "";
if (!$GLOBALS["wmt_lab_enable"]) $invalid .= "LabLink Interface Not Enabled\n";
if (!$siteid) $invalid .= "No Sending Facility Identifier\n";
if (!$lab_data["recv_fac_id"]) $invalid .= "No Receiving Facility Identifier\n";
if (!$lab_data["recv_app_id"]) $invalid .= "No Receiving Application Identifier\n";
if (!$lab_data["login"]) $invalid .= "No Quest Username\n";
if (!$lab_data["password"]) $invalid .= "No Quest Password\n";
if (!$lab_data["orders_path"]) $invalid .= "No Quest Order Path\n";
if (!$lab_data["results_path"]) $invalid .= "No Quest Result Path\n";
if (!file_exists("{$GLOBALS["OE_SITE_DIR"]}/labs")) $invalid .= "No Lab Work Directory\n";
if (!file_exists("{$GLOBALS["srcdir"]}/wmt")) $invalid .= "Missing WMT Library\n";
if (!file_exists("{$GLOBALS["srcdir"]}/wmt/quest")) $invalid .= "Missing Quest Library\n";
if (!file_exists("{$GLOBALS["srcdir"]}/tcpdf")) $invalid .= "Missing TCPDF Library\n";
if (!extension_loaded("curl")) $invalid .= "CURL Module Not Enabled\n";
if (!extension_loaded("xml")) $invalid .= "XML Module Not Enabled\n";
if (!extension_loaded("sockets")) $invalid .= "SOCKETS Module Not Enabled\n";
if (!extension_loaded("soap")) $invalid .= "SOAP Module Not Enabled\n";
if (!extension_loaded("openssl")) $invalid .= "OPENSSL Module Not Enabled\n";

if ($invalid) { ?>
<h1>Quest Diagnostic Interface Not Available</h1>
The interface is not enabled, not properly configured, or required
components are missing!!
<br />
<br />
For assistance with implementing this service contact:
<br />
<br />
<a href="http://www.williamsmedtech.com/support" target="_blank"><b>Williams
		Medical Technologies Support</b></a>
<br />
<br />
<table style="border: 2px solid red; padding: 20px">
	<tr>
		<td style="white-space: pre; color: red"><h3>DEBUG OUTPUT</h3><?php echo $invalid ?></td>
	</tr>
</table>
<?php
exit; 
}

$dlist = array();

// active encounter diagnoses
$sql = "SELECT 'Active' AS title, CONCAT('ICD10:',formatted_dx_code) AS code, short_desc, long_desc FROM `issue_encounter` ie ";
$sql .= "LEFT JOIN `lists` ls ON ie.`list_id` = ls.`id` AND ie.`pid` = ls.`pid` AND ls.`activity` = '1' ";
$sql .= "LEFT JOIN `icd10_dx_order_code` oc ON oc.`formatted_dx_code` = SUBSTR(ls.`diagnosis` FROM 7) AND oc.`active` = '1' ";
$sql .= "WHERE ie.`pid` = ? AND ie.`encounter` = ? AND ie.`resolved` = 0 AND short_desc IS NOT NULL ";
$sql .= "ORDER BY oc.`short_desc`";
$result = sqlStatementNoLog($sql,array($pid,$encounter));

while ($data = sqlFetchArray($result)) {
	// create array ('tab title','icd9 code','short title','long title')
	$dlist[] = $data;
}

// retrieve diagnosis quick list
/* OLD VERSION !!!
$query = "SELECT title, CONCAT('ICD10:',formatted_dx_code) AS code, short_desc, long_desc FROM list_options l ";
$query .= "JOIN icd9_dx_code c ON c.formatted_dx_code = l.option_id AND c.active = 1 ";
$query .= "WHERE l.list_id LIKE 'Quest\_Diagnosis%' ";
$query .= "ORDER BY l.title, l.seq";
$result = sqlStatement($query);
*/
$query = "SELECT title, CONCAT('ICD10:',formatted_dx_code) AS code, short_desc, long_desc FROM list_options l ";
$query .= "JOIN icd10_dx_order_code c ON c.formatted_dx_code = l.option_id AND c.active = 1 ";
$query .= "WHERE l.list_id LIKE 'Lab\_ICD10%' ";
$query .= "ORDER BY l.title, l.seq";
$result = sqlStatement($query);

while ($data = sqlFetchArray($result)) {
	// create array ('tab title','icd code','short title','long title')
	$dlist[] = $data;
}

// Retrieve all of the lab favorites
$query = "SELECT ord.procedure_type_id AS id, ord.procedure_code AS code, fav.name AS title, ord.name, fav.description FROM procedure_type fav ";
$query .= "LEFT JOIN procedure_type ord ON ord.procedure_type_id = fav.parent ";
$query .= "WHERE fav.procedure_type = 'fav' AND ord.lab_id = ? ";
$query .= "ORDER BY fav.name, fav.seq";
$result = sqlStatementNoLog($query,array($lab_id));

if (sqlNumRows($result) == 0) {
	$query = "SELECT ls.`option_id` AS code, ls.`title`, pt.`name`, ls.`notes` AS description FROM `list_options` ls ";
	$query .= "LEFT JOIN `procedure_type` pt ON ls.`option_id` = pt.`procedure_code` AND pt.`lab_id` = ? AND ";
	$query .= "(pt.procedure_type LIKE 'pro' OR pt.procedure_type LIKE 'ord') ";
	$query .= "WHERE ls.`list_id` LIKE '" . $lab_data['npi'] . "\_Tests%' AND ls.`activity` = 1 ";
	$query .= "ORDER BY ls.`title`, ls.`seq`";
}

$result = sqlStatement($query,array($lab_id));

// Save favorites array (for each lab)
$olist = array();
while ($data = sqlFetchArray($result)) {
	// Save the favorites
	$olist[] = $data;
}	

if (!function_exists('UserIdLook')) {
	function UserIdLook($thisField) {
	  if(!$thisField) return '';
	  $ret = '';
	  $rlist= sqlStatement("SELECT * FROM users WHERE id='".$thisField."'");
	  $rrow= sqlFetchArray($rlist);
	  if($rrow) $ret = $rrow['lname'].', '.$rrow['fname'].' '.$rrow['mname'];
	  return $ret;
	}
}

function getLabelers($thisField) {
	$rlist= sqlStatement("SELECT * FROM list_options WHERE list_id = 'Quest_Label_Printers' ORDER BY seq, title");
	
	$active = '';
	$default = '';
	$labelers = array();
	while ($rrow= sqlFetchArray($rlist)) {
		if ($thisField == $rrow['option_id']) $active = $rrow['option_id'];
		if ($rrow['is_default']) $default = $rrow['option_id'];
		$labelers[] = $rrow; 
	}

	if (!$active) $active = $default;
	
	foreach ($labelers AS $rrow) {
		echo "<option value='" . $rrow['option_id'] . "'";
		if ($active == $rrow['option_id']) echo " selected='selected'";
		echo ">" . $rrow['title'];
		echo "</option>\n";
	}
}

// find account number
$account = false;
$data = sqlQuery("SELECT * FROM list_options WHERE list_id = 'Provider_Site_Identifier' AND title = ?",array($lab_id));
if ($data) $account = $data['option_id'];

// SPECIAL PROCESSING REQUIRED
$qba = false;
if ($lab_data['recv_fac_id'] == 'QBA') $qba = true;

?>
<!DOCTYPE HTML>
<html>
<head>
		<?php //html_header_show();?>
		<title><?php echo $form_title; ?></title>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css"
	href="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox-1.3.4/jquery.fancybox-1.3.4.css"
	media="screen" />
<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/wmt/wmt.default.css" media="screen" />

<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui']); ?>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>


<script><?php include_once("{$GLOBALS['srcdir']}/restoreSession.php"); ?></script>
<script type="text/javascript"
	src="<?php echo $GLOBALS['webroot'] ?>/library/js/common.js"></script>
<script type="text/javascript"
	src="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox-1.3.4/jquery.fancybox-1.3.4_patch.js"></script>
<script type="text/javascript"
	src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
<script type="text/javascript"
	src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>
<script type="text/javascript"
	src="<?php echo $GLOBALS['webroot'] ?>/library/wmt/wmtstandard.js"></script>

<style>
.calendar tbody .day {
	border: 1px solid inherit;
}

.calendar {
	z-index: 2000
}

.wmtMainContainer {
	min-width: 880px
}

.wmtMainContainer table {
	font-size: 12px;
	border-collapse: collapse;
}

.wmtMainContainer fieldset {
	margin-top: 0;
	border: 1px solid #aaa
}

/* COLOR STYLES FOR WMT SOFTWARE 
.wmtColorMain {
	background-color: #FFFFFF;
}

.wmtColorBar {
	background-color: #8497bf;
}

.wmtColorBox {
	background-color: #f0f0f0;
	border-color: #666666;
}

.wmtColorHeader {
	background-color: #004080;
	color: #FFFFFF;
}

.wmtColorMenu {
	background-color: #B6D2E0;
	border-color: #B6D2E0;
}

.wmtLabMenu 
	.ui-state-default {
	background-color: #8cacbb;
	border-color: #000000;
}

.wmtLabMenu 
	.ui-state-active {
	background-color: #f0f0f0;
	border-color: #000000;
}

.wmtInput, .wmtInput2, .wmtFullInput, .wmtFullInput2 {
	background-color: #ffffff;
}
*/
</style>

<script>
			var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

			// validate data and submit form
			function saveClicked() {
				var f = document.forms[0];
				var resp = true;
<?php if ($order_data->status == 'i') { // has not been submitted yet ?>
				resp = confirm("Your order will be saved but will NOT be submitted.\n\nClick 'OK' to save and exit.");
<?php } ?>
				if (resp) {
					restoreSession();
					f.submit();
				}
 			}

			function submitClicked() {
				// minimum validation
				notice = '';
				$('.aoe').each(function() {
					if (!$(this).val()) notice = "\n- All order questions must be answered."; 
				});
				if ($('.code').length < 1) notice += "\n- At least one diagnosis code required.";
				if ($('.test').length < 1) notice += "\n- At least one profile / test code required.";
				if ($('#specimen_draw').val() == '' && !$('#order_psc').is(':checked')) notice += "\n- Specimen collect by is required.";
				if ($('#provider_id').val() == '' || $('#provider_id').val() == '_blank') notice += "\n- An ordering physician is required.";
				if ($('#request_account').val() == '') notice += "\n- A billing account must be specified.";

				if (notice) {
					notice = "PLEASE CORRECT THE FOLLOWING:\n" + notice;
					alert(notice);
					return;
				}

				$.fancybox.showActivity();
				
				$('#process').val('1'); // flag doing submit
				
				$.ajax ({
					type: "POST",
					url: "<?php echo $save_url ?>",
					data: $("#<?php echo $form_name; ?>").serialize(),
					success: function(data) {
			            $.fancybox({
			                'content' 				: data,
							'overlayOpacity' 		: 0.6,
							'showCloseButton' 		: false,
							'width'					: 'auto',
							'height' 				: 'auto',
							'centerOnScroll' 		: false,
							'autoScale'				: false,
							'autoDimensions'		: true,
							'hideOnOverlayClick' 	: false
						});
					}
				});
			}

 			function printClicked() {
 	 			// do save before print
				var f = document.forms[0];
				$('#print').val('1'); // flag doing print
				var prnwin = window.open('','print','width=735px,height=575px,status=no,scrollbars=yes');
				prnwin.focus();
				$('#<?php echo $form_name ?>').attr('target','print');
				restoreSession();
				f.submit();
 			}

 			function messageClicked() {
 	 			var url = "../../main/messages/add_edit_message.php?mode=addnew&reply_to=<?php echo $order_data->pid ?>&document_id=<?php echo $order_data->result_doc_id ?>";
				var prnwin = window.open(url,'message','width=735px,height=575px,status=no,scrollbars=yes');
 			}

			function doClose() {
				parent.closeTab(window.name, false);
			}
			
			function doReturn(id) {
				parent.closeTab(window.name, false);
			}
			
 			 // define ajax error handler
			$(function() {
			    $.ajaxSetup({
			        error: function(jqXHR, exception) {
			            if (jqXHR.status === 0) {
			                alert('Not connected to network.');
			            } else if (jqXHR.status == 404) {
			                alert('Requested page not found. [404]');
			            } else if (jqXHR.status == 500) {
			                alert('Internal Server Error [500].');
			            } else if (exception === 'parsererror') {
			                alert('Requested JSON parse failed.');
			            } else if (exception === 'timeout') {
			                alert('Time out error.');
			            } else if (exception === 'abort') {
			                alert('Ajax request aborted.');
			            } else {
			                alert('Uncaught Error.\n' + jqXHR.responseText);
			            }
			        }
			    });

			    return false;
			});

			// search for the provided icd code
			function searchDiagnosis() {
				var output = '';
				var f = document.forms[0];
				var code = f.searchIcd.value;
				if ( code == '' ) { 
					alert('You must enter a diagnosis search code.');
					return;
				}
				
				// retrieve the diagnosis array
				$.ajax ({
					type: "POST",
					url: "<?php echo $GLOBALS['webroot'] ?>/library/wmt/quest/QuestAjax.php",
					dataType: "json",
					data: {
						type: 'icd9',
						code: code
					},
					success: function(data) {
				    	$.each(data, function(key, val) {
					    	id = val.code.replace('.','_');
					    	code = val.code.replace('ICD10:','');
				    		output += "<tr><td style='white-space:nowrap;width:60px''><input class='wmtCheck' type='checkbox' name='check_"+id+"' code='"+val.code+"' desc='"+val.long_desc+"'/> <b>"+val.code+"</b> - </td><td style='padding-top:3px'>"+val.short_desc+"<br/></td>\n";
						});
					},
					async:   false
				});

				if (output == '') {
					output = '<table><tr><td><h4>NO MATCHES</h4></td></tr></table>';
				}
				else{
					output = '<table>' + output + '</table>';
				}
				
				$('#dc_Search').html(output);
				$("#dc_tabs").tabs( "option", "active", 0 );	
				f.searchIcd.value = '';
			}

			function addCodes() {
				var count = 0;
				$('#dc_tabs').tabs('option','active');
				$("#dc_tabs div[aria-hidden='false'] input:checked").each(function() {
					success = addCodeRow($(this).attr('code'), $(this).attr('desc'));
					$(this).attr('checked',false);
					if (success) count++;
				});
			}
			
			function addCodeRow(code,text) {
				$('#codeEmptyRow').remove();

				id = code.replace('.','_');
				id = id.replace('ICD9:','');
				id = id.replace('ICD10:','');
				if ($('#code_'+id).length) {
					alert("Code "+code+" has already been added.");
					return false;
				}

				if ($('#codeTable tr').length > 10) {
					alert("Maximum number of diagnosis codes exceeded.");
					return false;
				}
				
				var newRow = "<tr id='code_" +id + "'>";
				newRow += "<td><input type='button' class='wmtButton' value='remove' style='width:60px' onclick=\"removeCodeRow('code_"+id+"')\" /></td>\n";
				newRow += "<td class='wmtLabel' style='width:120px'><input type='text' name='dx_code[]' class='wmtFullInput code' style='font-weight:bold' readonly value='";
				newRow += code;
				newRow += "'/></td><td class='wmtLabel'><input type='text' name='dx_text[]' class='wmtFullInput name' readonly value='";
				newRow += text;
				newRow += "'/></td></tr>\n";
				
				$('#codeTable').append(newRow);

				return true;
			}

			function removeCodeRow(id) {
				$('#'+id).remove();
				// there is always the header and the "empty" row
				if ($('#codeTable tr').length == 1) {
					$('#codeTable').append('<tr id="CodeEmptyRow"><td colspan="3"><b>NO DIAGNOSIS CODES SELECTED</b></td></tr>');
				}
			}

			// search for the provided test code
			function searchTest() {
				var output = '';
				var f = document.forms[0];
				var code = f.searchCode.value;
				if ( code == '' ) { 
					alert('You must enter a profile or lab test search code.');
					return;
				}
				
				// retrieve the test array
				$.ajax ({
					type: "POST",
					url: "<?php echo $GLOBALS['webroot'] ?>/library/wmt/quest/QuestAjax.php",
					dataType: "json",
					data: {
						type: 'code',
						code: code,
						lab_id: '<?php echo $lab_id ?>'
					},
					success: function(data) {
						// data = array('id','code','type','title','description','provider');
				    	$.each(data, function(key, val) {
					    	var id = val.code.replace('.','_');
							var text = val.description;
							if (!text) text = val.title;
				    		output += "<tr><td style='white-space:nowrap;width:60px'><input class='wmtCheck' type='checkbox' name='check_"+val.id+"' code='"+val.code+"' desc='"+text+"' prof='"+val.type+"' /> ";
				    		if (val.type == 'pro') {
					    		output += "<span style='font-weight:bold;color:#c00;vertical-align:middle'>"+val.code+"</span>";
				    		}
				    		else { 	
					    		output += "<span style='font-weight:bold;vertical-align:middle'>"+val.code+"</span>";
				    		}
				    		output += " - </td><td style='width:auto;text-align:left;padding-top:3px'>"+val.title+"<br/></td>\n";
				    	});
					},
					async:   false
				});

				if (output == '') {
					output = '<table><tr><td><h4>NO MATCHES</h4></td></tr></table>';
				}
				else{
					output = '<table>' + output + '</table>';
				}
				
				$('#oc_Search').html(output);
				$("#oc_tabs").tabs( "option", "active", 0 );	
				f.searchCode.value = '';
			}

			// search for the provided test code
			function fetchDetails(code) {
				var output = '';
				
				// retrieve the test details
				$.ajax ({
					type: "POST",
					url: "<?php echo $GLOBALS['webroot'] ?>/library/wmt/quest/QuestAjax.php",
					dataType: "json",
					data: {
						type: 'details',
						code: code,
						lab_id: '<?php echo $lab_id ?>'
					},
					success: function(data) {
						output = data; // process later
					},
					async:   false
				});

				return output;
			}

			function addTests() {
				var count = 0;
				var errors = 0;
				$('#oc_tabs').tabs('option','active');
				$("#oc_tabs div[aria-hidden='false'] input:checked").each(function() {
					success = addTestRow($(this).attr('code'),$(this).attr('desc'),$(this).attr('prof'));
					$(this).attr('checked',false);
					if (success) {
						count++;
					}
					else {
						errors++;
					}
				});
				if (count) {
					if (errors) {
						alert("Some items were not added to order.");
					}
				}
			}
			
			function addTestRow(code,text,flag) {
				$('#orderEmptyRow').remove();

				id = code.replace('.','_');
				if ($('#test_'+id).length) {
					alert("Test "+code+" has already been added.");
					return false;
				}

				if ($('#order_table tr').length > 35) {
					alert("Maximum number of profile/test requests exceeded.");
					return false;
				}

				var data = fetchDetails(code);
				var type = data.type; // json data from ajax
				var unit = data.unit; // json data from ajax
				var state = data.state; // json data from ajax
				var profile = data.profile; // json data fron ajax
				var aoe = data.aoe; // json data from ajax

				var current = $('#specimen_transport').val();
//WRONG!!!				var psc = $('#order_psc').val();
				var psc = false;
				if ( $('#order_psc').is(':checked') ) psc = true;

<?php if (!$GLOBALS['wmt_lab_psc']) { ?>

<?php if (!$qba) { ?>
				if (state != '') {
					if (current == '') {
						$('#specimen_transport').val(state);
					}
					else if (current != state && (current == 'PAP' || state == 'PAP') ) {
						if (code != '90521' && code != '90569') {
							alert("SPECIMEN PATHOLOGY MISMATCH: \n"+current+" and "+state+"\n\nAnatomic pathology test ["+code+"] requires different processing\nand must be entered on a separate request.");
							return false;
						}
					}
					else if (current != state && !psc) {
						alert("SPECIMEN TRANSPORT MISMATCH: \n"+current+" and "+state+"\n\nTest ["+code+"] requires a different specimen transport\ntype and must be entered on a separate request.");
						return false;
					}
				}
<?php } else { ?>
				if (state != '') {
					if (current == '') {
						$('#specimen_transport').val(state);
					}
					else if (current == 'Separate') {
						alert("SPECIMEN MISMATCH: \nThe current test ["+code+"] must be entered on a separate request.");
						return false;
					}
					else if (state == 'Separate') {
						alert("SPECIMEN MISMATCH: \nThe selected test ["+code+"] must be entered on a separate request.");
						return false;
					}
					else if (current != state && !psc) {
						alert("TRANSPORT MISMATCH: \n"+current+" and "+state+"\n\nTest ["+code+"] requires a different specimen transport\ntype and must be entered on a separate request.");
						return false;
					}
				}
<?php } ?>

<?php } ?>
				var success = true;
				$('.component').each(function() {
					if ($(this).attr('unit') == code && success) {
						alert("Test "+code+" has already been added as profile component.");
						success = false;
					} 					
				});

				if (!success) return false;

				var newRow = "<tr id='test_" +id + "'>";
				newRow += "<td style='vertical-align:top'><input type='button' class='wmtButton' value='remove' style='width:60px' onclick=\"removeTestRow('test_"+id+"')\" /> ";
				newRow += "<input type='button' class='wmtButton' value='details' style='width:60px' onclick=\"testOverview('"+id+"')\" /></td>\n";
				newRow += "<td class='wmtLabel' style='vertical-align:top;padding-top:5px;width:80px'><input type='text' name='test_code[]' class='wmtFullInput test' readonly value='"+code+"' ";
				if (flag == 'pro') { // profile test
					newRow += "style='font-weight:bold;color:#c00' /><input type='hidden' name='test_type[]' value='pro' />";
				}
				else {
					newRow += "style='font-weight:bold' /><input type='hidden' name='test_type[]' value='ord' />";
				}
 				newRow += "</td><td colspan='2' class='wmtLabel' style='text-align:right;vertical-align:top;padding-top:5px'><input type='text' name='test_text[]' class='wmtFullInput component' readonly unit='"+code+"' value='"+text+"'/>\n";
  				
				// add profile tests if necessary
				success = true;
				for (var key in profile) {
					var obj = profile[key];

					$('.component').each(function() {
						if ($(this).attr('unit') == obj.component) {
							alert("Component of test "+code+" has already been added.");
							success = false;
						} 					
					});
						
					if (obj.description)  newRow += "<input type='text' class='wmtFullInput component' style='margin-top:5px' readonly unit='"+obj.component+"' value='"+obj.component+" - "+obj.description+"'/>\n";
					
					// add component AOE questions if necessary
					var aoe2 = obj.aoe;
					for (var key2 in aoe2) {
						var obj2 = aoe2[key2];
					   
						var test_code = obj2.code;
						var test_unit = obj2.unit;
						var question = obj2.question.replace(':','');
						if (obj2.description) question = obj2.description.replace(':',''); // use longer if available
						var prompt = obj2.prompt;
						if (test_code) {
							newRow += '<input type="hidden" name="aoe'+id+'_label[]" value="'+question+'" />'+"\n";
							newRow += "<input type='hidden' name='aoe"+id+"_code[]' value='"+test_code+"' />\n";
					   		newRow += "<input type='hidden' name='aoe"+id+"_unit[]' value='"+test_unit+"' />\n";
					   		newRow += "<div style='margin-top:5px'>" + question + ": <input type='text' name='aoe"+id+"_text[]' title='" + test_code + ": " + prompt + "' class='wmtFullInput aoe' value='' style='width:300px' /></div>\n";
						}	
					}
				}

				if (!success) return false;
				
				// add order AOE questions if necessary
				for (var key in aoe) {
					var obj = aoe[key];
				   
					var test_code = obj.code;
					var question = obj.question.replace(':','');
					if (obj.description) question = obj.description.replace(':',''); // use longer if available
					var prompt = obj.prompt;
					if (test_code) {
						newRow += '<input type="hidden" name="aoe'+id+'_label[]" value="'+question+'" />'+"\n";
						newRow += "<input type='hidden' name='aoe"+id+"_code[]' value='"+test_code+"' />\n";
						newRow += "<div style='margin-top:5px'>" + question + ": <input type='text' name='aoe"+id+"_text[]' title='" + prompt + "' class='wmtFullInput aoe' value='' style='width:300px' /></div>\n";
					}	
				}

				newRow += "</td></tr>\n"; // finish up order row
				
				$('#order_table').append(newRow);

				return true;
			}

			function removeTestRow(id) {
				$('#'+id).remove();
				// there is always the header and the "empty" row
				if ($('#order_table tr').length == 1) {
					$('#specimen_type').val('');
					$('#specimen_transport').val('');
					$('#order_table').append('<tr id="orderEmptyRow"><td colspan="3"><b>NO PROFILES / TESTS SELECTED</b></td></tr>');
				}
			}

			// display test overview pop up
			function testOverview(code) {
				$.fancybox.showActivity();
				
				// retrieve the overview details
				$.ajax ({
					type: "POST",
					url: "<?php echo $GLOBALS['webroot'] ?>/library/wmt/quest/QuestAjax.php",
					dataType: "html",
					data: {
						type: 'overview',
						code: code
					},
					success: function(data) {
			            $.fancybox({
			                'content' 				: data,
							'overlayOpacity' 		: 0.6,
							'showCloseButton' 		: true,
							'width'					: '500',
							'height' 				: '400',
							'centerOnScroll' 		: false,
							'autoScale'				: false,
							'autoDimensions'		: false,
							'hideOnOverlayClick' 	: true,
							'scrolling'				: 'auto'
						});
										},
					async:   false
				});

				return;
			}

			
			// print labels
			function printLabels(item) {
				var f = document.forms[0];
				var fl = document.forms[item];
				var printer = fl.labeler.value;
				if ( printer == '' ) { 
					alert('Unable to determine default label printer.\nPlease select a label printer.');
					return;
				}

				var count = fl.count.value;
				var order = f.order_number.value;
				var patient = "<?php echo $pat_data->lname; ?>, <?php echo $pat_data->fname; ?> <?php echo $pat_data->mname; ?>";
				var pid = "<?php echo $pat_data->pid  ?>";
				
				// retrieve the label
				$.ajax ({
					type: "POST",
					url: "<?php echo $GLOBALS['webroot'] ?>/library/wmt/quest/QuestAjax.php",
					dataType: "text",
					data: {
						type: 'label',
						printer: printer,
						count: count,
						order: order,
						patient: patient,
						pid: pid,
						siteid: '<?php echo $siteid ?>'
					},
					success: function(data) {
						if (printer == 'file') {
							window.open(data,"_blank");
						}
						else {
							alert(data);
						}
					},
					async:   false
				});

			}

			// setup jquery processes
			$(document).ready(function(){
				$('#dc_tabs').tabs().addClass('ui-tabs-vertical ui-helper-clearfix');
				$('#oc_tabs').tabs().addClass('ui-tabs-vertical ui-helper-clearfix');

				$("#searchIcd").keyup(function(event){
				    if(event.keyCode == 13){
				        searchDiagnosis();
				    }
				});

				$("#searchCode").keyup(function(event){
				    if(event.keyCode == 13){
				        searchTest();
				    }
				});

				$("#order_psc").change(function(){
					$("#sample_data").show();
					$("#ship_data").show();
					$("#psc_data").hide();
					
				    if ($(this).attr("checked")) {
						$("#sample_data").hide();
						$("#ship_data").hide();
						$("#psc_data").show();
					}
				});
				
				$("#work_flag").change(function(){
					$("#work_data").hide();
				
				    if ($(this).attr("checked")) {
						$("#work_data").show();
				    }
				});

				// setup fancybox
				$(".inline").fancybox( {
					'overlayOpacity' : 0.0,
					'showCloseButton' : true,
					'autoDimensions' : false,
					'height' : 350,
					'width' : 650,
					'type' : 'inline'
				});

				$(".inline").click(function() {
					var key = $(this).attr('key');
					var code = $('#result_code_'+key).val();
					var text = $('#result_text_'+key).val();
					var notes = $('#result_notes_'+key).val();
					var status = $('#result_status_'+key).val();
					var date = $('#result_date_'+key).val();
					var clinician = $('#result_clinician_'+key).val();

					$('#edit_key').val(key);
					$('#edit_code').val(code);
					$('#edit_text').val(text);
					$('#edit_data').val(notes);
					$('#edit_status').val(status);
					$('#edit_date').val(date);
					$('#edit_clinician').val(clinician);
				});
				
<?php if ($status != 'i') { // disable if not incomplete ?> 
				$("#orderEntry :input").attr("disabled", true);
				$("#orderReview :input").attr("disabled", true);
				$("#orderSubmission :input").attr("disabled", true);
				$(".nolock").attr("disabled", false);
<?php } ?>
			});
				
 			function saveResult() {
				var key = $('#edit_key').val();
 				var code = $('#edit_code').val();
				var text = $('#edit_text').val();
				var notes = $('#edit_data').val();
				var status = $('#edit_status').val();
				var date = $('#edit_date').val();
				var clinician = $('#edit_clinician').val();
				var cname = $( "#edit_clinician option:selected" ).text();
				var sname = $( "#edit_status option:selected" ).text();
				
				$('#result_notes_'+key).val(notes);
				$('#result_status_'+key).val(status);
				$('#result_sname_'+key).val(sname);
				$('#result_date_'+key).val(date);
				$('#result_clinician_'+key).val(clinician);
				$('#result_cname_'+key).val(cname);
				$('#result_'+key).show();
			
				$.fancybox.close();
 			}

			
		</script>
</head>

<body>

	<!-- Required for the popup date selectors -->
	<div id="overDiv"
		style="position: absolute; visibility: hidden; z-index: 1000;"></div>

	<form method='post' action="<?php echo $save_url ?>"
		id='<?php echo $form_name; ?>' name='<?php echo $form_name; ?>' style='padding: 0px 10px;'>
		<input type='hidden' name='process' id='process' value='' /> <input
			type='hidden' name='print' id='print' value='' /> <input
			type='hidden' name='lab_quest_siteid' id='lab_quest_siteid'
			value='<?php echo $siteid ?>' /> <input type='hidden' name='pop'
			id='pop' value='<?php if ($popup) echo '1' ?>' /> <input
			type='hidden' name='patient_id' id='patient_id'
			value='<?php echo $pid ?>' /> <input type='hidden' name='facility_id'
			id='facility_id' value='<?php echo $enc_data->facility_id ?>' />
		<div class="wmtTitle">
<?php if ($viewmode) { ?>
				<input type=hidden name='mode' value='update' /> <input type=hidden
				name='id' value='<?php echo $_GET["id"] ?>' /> <span class=title><?php echo $form_title; ?> <?php echo (strtotime($order_data->date_transmitted))? '&amp; Results View': 'Update' ?></span>
<?php } else { ?>
				<input type='hidden' name='mode' value='new' /> <span class='title'>New <?php echo $form_title; ?></span>
<?php } ?>
			</div>

		<!-- BEGIN ORDER -->
<?php 
$info_border = "border-radius:5px";
$info_arrow = "$webroot/library/wmt/fill-270.png";
$info_hide = "display:none";
if ($client_id == "walsh" || $client_id == "uimda") {
	$info_border = "";
	$info_arrow = "$webroot/library/wmt/fill-090.png";
	$info_hide = "";
}
?>
			<!-- Client Information -->
		<div class="wmtMainContainer wmtColorMain" id="clientData"
			style="width: 100%">
			<div class="wmtCollapseBar wmtColorBar" id="ReviewCollapseBar" style="<?php echo $info_border ?>" onclick="togglePanel('ReviewBox','ReviewImageL','ReviewImageR','ReviewCollapseBar')">
				<table style="width: 100%">
					<tr>
						<td><img id="ReviewImageL" align="left"
							src="<?php echo $info_arrow;?>" border="0" alt="Show/Hide"
							title="Show/Hide" /></td>
						<td class="wmtChapter" style="text-align: center">Patient
							Information</td>
						<td style="text-align: right"><img id="ReviewImageR"
							src="<?php echo $info_arrow;?>" border="0" alt="Show/Hide"
							title="Show/Hide" /></td>
					</tr>
				</table>
			</div>

			<div class="wmtCollapseBox" id="ReviewBox" style="<?php echo $info_hide ?>">
				<table style="width: 100%">
					<tr>
						<!-- Left Side -->
						<td style="width: 50%" class="wmtInnerLeft">
							<table style="width: 100%">
								<tr>
									<td style="width: 20%" class="wmtLabel">Patient First <input
										name="pat_fname" type="text" class="wmtFullInput" readonly
										value="<?php echo ($completed)?$order_data->pat_fname:$pat_data->fname; ?>">
										<input name="pid" type="hidden"
										value="<?php echo $pat_data->pid; ?>"> <input name="pubpid"
										type="hidden" value="<?php echo $pat_data->pubpid; ?>"> <input
										name="encounter" type="hidden"
										value="<?php echo $encounter; ?>">
									</td>
									<td style="width: 10%" class="wmtLabel">Middle <input
										name="pat_mname" type="text" class="wmtFullInput" readonly
										value="<?php echo ($completed)?$order_data->pat_mname:$pat_data->mname; ?>">
									</td>
									<td class="wmtLabel">Last Name <input name="pat_lname"
										type="text" class="wmtFullInput" readonly
										value="<?php echo ($completed)?$order_data->pat_lname:$pat_data->lname; ?>">
									</td>
									<td style="width: 20%" class="wmtLabel">Patient Id <input
										name="pat_pubpid" type="text" class="wmtFullInput" readonly
										value="<?php echo ($completed)?$order_data->pat_pubpid:$pat_data->pubpid; ?>">
									</td>
									<td colspan="2" style="width: 20%" class="wmtLabel">Social
										Security <input name="pat_ss" type"text" class="wmtFullInput"
										readonly
										value="<?php echo ($completed)?$order_data->pat_ss:$pat_data->ss ?>">
									</td>
								</tr>

								<tr>
									<td colspan="3" class="wmtLabel">Email Address<input
										name="pat_email" type="text" class="wmtFullInput" readonly
										value="<?php echo ($completed)?$order_data->pat_email:$pat_data->email; ?>"></td>
									<td style="width: 20%" class="wmtLabel">Birth Date <input
										name="pat_DOB" type="text" class="wmtFullInput" readonly
										value="<?php echo $pat_data->birth_date; ?>">
									</td>
									<td style="width: 5%" class="wmtLabel">Age <input
										name="pat_age" type="text" class="wmtFullInput" readonly
										value="<?php echo $pat_data->age; ?>">
									</td>
									<td style="width: 15%" class="wmtLabel">Gender <input
										name="pat_sex" type="hidden"
										value="<?php echo $pat_data->sex ?>" /> <input type="text"
										class="wmtFullInput" readonly
										value="<?php echo ListLook($pat_data->sex, 'sex') ?>">
									</td>
								</tr>

								<tr>
									<td colspan="3" class="wmtLabel">Primary Address <input
										name="pat_street" type="text" class="wmtFullInput" readonly
										value="<?php echo $pat_data->street; ?>">
									</td>
									<td class="wmtLabel">Mobile Phone<input name="pat_mobile"
										id="ex_phone_mobile" type="text" class="wmtFullInput" readonly
										value="<?php echo $pat_data->phone_cell; ?>"></td>
									<td colspan="2" class="wmtLabel">Home Phone<input
										name="pat_phone" type="text" class="wmtFullInput" readonly
										value="<?php echo $pat_data->phone_home; ?>"></td>
								</tr>

								<tr>
									<td colspan="3" class="wmtLabel" style="width: 50%">City <input
										name="pat_city" type="text" class="wmtFullInput" readonly
										value="<?php echo $pat_data->city; ?>">
									</td>
									<td class="wmtLabel">State <input type="text"
										class="wmtFullInput" readonly
										value="<?php echo ListLook($pat_data->state, 'state'); ?>"> <input
										type="hidden" name="pat_state"
										value="<?php echo $pat_data->state ?>" />
									</td>
									<td colspan="2" class="wmtLabel">Postal Code <input
										name="pat_zip" type="text" class="wmtFullInput" readonly
										value="<?php echo $pat_data->postal_code; ?>">
									</td>
								</tr>
							</table>
						</td>

						<!-- Right Side -->
						<td style="width: 50%" class="wmtInnerRight">
							<table style="width: 100%">
								<tr>
									<td style="width: 20%" class="wmtLabel">Insured First <input
										name="ins_fname" type="text" class="wmtFullInput" readonly
										value="<?php echo $ins_list[0]->subscriber_fname; ?>">
									</td>
									<td style="width: 10%" class="wmtLabel">Middle <input
										name="ins_mname" type"text" class="wmtFullInput" readonly
										value="<?php echo $ins_list[0]->subscriber_mname; ?>">
									</td>
									<td class="wmtLabel">Last Name <input name="ins_lname"
										type"text" class="wmtFullInput" readonly
										value="<?php echo $ins_list[0]->subscriber_lname; ?>">
									</td>
									<td style="width: 20%" class="wmtLabel">Birth Date <input
										name="ins_DOB" type="text" class="wmtFullInput" readonly
										value="<?php echo $ins_list[0]->subscriber_birth_date; ?>">
									</td>
									<td style="width: 20%" class="wmtLabel">Relationship <input
										name="ins_relation" type="text" class="wmtFullInput" readonly
										value="<?php echo ListLook($ins_list[0]->subscriber_relationship, 'sub_relation'); ?>">
										<input name="ins_ss" type="hidden"
										value="<?php echo $ins_list[0]->subscriber_ss ?>" /> <input
										name="ins_sex" type="hidden"
										value="<?php echo $ins_list[0]->subscriber_sex ?>" />
									</td>
								</tr>
								<tr>
									<td colspan="3" class="wmtLabel">Primary Insurance <input
										type="text" class="wmtFullInput" readonly
										value="<?php echo ($ins_list[0]->company_name)?$ins_list[0]->company_name:'No Insurance'; ?>">
										<input id="ins_primary" name="ins_primary" type="hidden"
										value="<?php echo $ins_list[0]->id ?>" />
									</td>
									<td class="wmtLabel">Policy #<input name="ins_primary_policy"
										type="text" class="wmtFullInput" readonly
										value="<?php echo $ins_list[0]->policy_number; ?>"></td>
									<td class="wmtLabel">Group #<input name="ins_primary_group"
										type="text" class="wmtFullInput" readonly
										value="<?php echo $ins_list[0]->group_number; ?>"></td>
								</tr>
								<tr>
									<td colspan="3" class="wmtLabel">Secondary Insurance <input
										type="text" class="wmtFullInput" readonly
										value="<?php echo $ins_list[1]->company_name; ?>"> <input
										id="ins_secondard" name="ins_secondary" type="hidden"
										value="<?php echo $ins_list[1]->id ?>" />
									</td>
									<td class="wmtLabel">Policy #<input name="ins_secondary_policy"
										type="text" class="wmtFullInput" readonly
										value="<?php echo $ins_list[1]->policy_number; ?>"></td>
									<td class="wmtLabel">Group #<input name="ins_secondary_group"
										type="text" class="wmtFullInput" readonly
										value="<?php echo $ins_list[1]->group_number; ?>"></td>
								</tr>
								<tr>
									<td style="width: 20%" class="wmtLabel">Guarantor First <input
										name="guarantor_fname" type="text" class="wmtFullInput"
										readonly
										value="<?php echo ($ins_list[0]->subscriber_lname)?$ins_list[0]->subscriber_fname:$pat_data->fname; ?>">
										<input name="guarantor_phone" type="hidden"
										value="<?php echo ($ins_list[0]->subscriber_phone)?$ins_list[0]->subscriber_phone:$pat_data->phone_home ?>" />
										<input name="guarantor_street" type="hidden"
										value="<?php echo ($ins_list[0]->subscriber_street)?$ins_list[0]->subscriber_street:$pat_data->street ?>" />
										<input name="guarantor_city" type="hidden"
										value="<?php echo ($ins_list[0]->subscriber_street)?$ins_list[0]->subscriber_city:$pat_data->city ?>" />
										<input name="guarantor_state" type="hidden"
										value="<?php echo ($ins_list[0]->subscriber_street)?$ins_list[0]->subscriber_state:$pat_data->state ?>" />
										<input name="guarantor_zip" type="hidden"
										value="<?php echo ($ins_list[0]->subscriber_street)?$ins_list[0]->subscriber_postal_code:$pat_data->postal_code ?>" />
									</td>
									<td style="width: 10%" class="wmtLabel">Middle <input
										name="guarantor_mname" type="text" class="wmtFullInput"
										readonly
										value="<?php echo ($ins_list[0]->subscriber_lname)?$ins_list[0]->subscriber_mname:$pat_data->mname; ?>">
									</td>
									<td style="width: 20%" class="wmtLabel">Last Name <input
										name="guarantor_lname" type="text" class="wmtFullInput"
										readonly
										value="<?php echo ($ins_list[0]->subscriber_lname)?$ins_list[0]->subscriber_lname:$pat_data->lname; ?>">
									</td>
									<td class="wmtLabel">SS#<input name="guarantor_ss" type="text"
										class="wmtFullInput" readonly
										value="<?php echo ($ins_list[0]->subscriber_ss)?$ins_list[0]->subscriber_ss:$pat_data->ss; ?>"></td>
									<td class="wmtLabel">Relationship <input
										name="guarantor_relation" type="text" class="wmtFullInput"
										readonly
										value="<?php echo ($ins_list[0]->subscriber_relationship)?ListLook($ins_list[0]->subscriber_relationship, 'sub_relation'):'Self'; ?>">
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<!-- End Client Information -->

		<!--  Order Entry -->
		<div class="wmtMainContainer wmtColorMain" id="orderEntry" style="width:100%;<?php if ($status != 'i') echo 'display:none'; ?>">
			<div class="wmtCollapseBar wmtColorBar" id="EntryCollapseBar"
				onclick="togglePanel('EntryBox','EntryImageL','EntryImageR','EntryCollapseBar');">
				<table style="width: 100%">
					<tr>
						<td><img id="EntryImageL" align="left"
							src="<?php echo $webroot;?>/library/wmt/fill-090.png" border="0"
							alt="Show/Hide" title="Show/Hide" /></td>
						<td class="wmtChapter" style="text-align: center">Order Entry</td>
						<td style="text-align: right"><img id="EntryImageR"
							src="<?php echo $webroot;?>/library/wmt/fill-090.png" border="0"
							alt="Show/Hide" title="Show/Hide" /></td>
					</tr>
				</table>
			</div>

			<div class="wmtCollapseBox" id="EntryBox">
				<table style="width: 100%; height: 310px">
					<tr>
						<!-- Left Side -->
						<td class="wmtInnerLeft"
							style='width: 49%; padding-left: 5px; padding-right: 4px'>
							<table class="wmtLabBox wmtColorBox">
								<tr>
									<td class="wmtLabHeader wmtColorHeader">
										<div class="wmtLabTitle">CLINICAL DIAGNOSIS CODES&nbsp;</div>
										<div style="float: left; vertical-align: bottom;">
											<input class="wmtButton css_button" type="button"
												style="margin-top: 2px; vertical-align: top"
												onclick="addCodes()" value="add selected" />
										</div>
										<div style="float: right">
											<input class="wmtInput"
												style="background-color: white; vertical-align: top;height: 24px;width: auto !important;"
												type="text" name="searchIcd" id="searchIcd" /> <input
												class="wmtButton css_button" type="button" style="float: left; margin-right:2px;" value="search"
												onclick="searchDiagnosis()" />
										</div>
									</td>
								</tr>
								<tr>
									<td class="wmtLabBody">
										<div id="dc_tabs">
											<div class="wmtLabMenu wmtColorMenu">
												<ul style="margin: 0; padding: 0">
<?php 
$title = 'Search';
echo "<li><a href='#dc_Search'>Search</a></li>\n";
foreach ($dlist as $data) {
	if ($data['title'] != $title) {
		$title = $data['title']; // new tab
		$link = strtolower(str_replace(' ', '_', $title));
		echo "<li><a href='#dc_".$link."'>".$title."</a></li>\n";
	}
}
?>
													</ul>
											</div>
												
<?php 
$title = 'Search';
echo "<div class='wmtQuick' id='dc_Search' style='display:none'><table width='100%'><tr><td style='text-align:center;padding-top:30px'><h3>Select profile at left or<br/>search using search box at top.</h3></tr></td>\n";
foreach ($dlist as $data) {
	if ($data['title'] != $title) {
		if ($title) echo "</table></div>\n"; // end previous section
		$title = $data['title']; // new section
		$link = strtolower(str_replace(' ', '_', $title));
		echo "<div class='wmtQuick' id='dc_".$link."' style='display:none'><table>\n";
	}
	$text = ($data['notes']) ? $data['notes'] : $data['short_desc'];
	$code = str_replace('ICD9:', '', $data['code']);
	$code = str_replace('ICD10:', '', $code);
	$id = str_replace('.', '_', $code);
	echo "<tr><td style='white-space:nowrap;width:60px'><input class='wmtCheck' type='checkbox' id='check_".$id."' code='".$data['code']."' desc='".htmlspecialchars($text)."' > <b>".$code."</b></input> - </td><td style='padding-top:4px'>".$text."</td></tr>\n";
}
if ($title) echo "</table></div>\n"; // end if at least one section
?>
											</div>
									</td>
								</tr>
							</table>
						</td>

						<!-- Right Side -->
						<td class="wmtInnerRight"
							style='width: 49%; padding-left: 10px; padding-right: 3px'>
							<table class="wmtLabBox wmtColorBox">
								<tr>
									<td class="wmtLabHeader wmtColorHeader">
										<div class="wmtLabTitle">
												<?php echo strtoupper($lab_data['name'])?> CODES&nbsp;
											</div>
										<div style="float: left">
											<input class="wmtButton css_button" type="button"
												style='vertical-align: top' onclick="addTests()"
												value="add selected" />
										</div>
										<div style="float: right">
											<input class="wmtInput"
												style="background-color: white; vertical-align: top; height: 24px;width: auto !important;"
												type="text" name="searchCode" id="searchCode" /> <input
												class="wmtButton css_button" type="button" style='vertical-align: top; float: left; margin-right:2px;'
												value="search" onclick="searchTest()" />
										</div>
									</td>
								</tr>
								<tr>
									<td class="wmtLabBody">
										<div id="oc_tabs">
											<div class="wmtLabMenu wmtColorMenu">
												<ul style="margin: 0; padding: 0">
<?php 
$title = 'Search';
echo "<li><a href='#oc_Search'>Search</a></li>\n";
foreach ($olist as $data) {
	if ($data['title'] != $title) {
		$title = $data['title']; // new tab
		$link = strtolower(str_replace(' ', '_', $title));
		echo "<li><a href='#oc_".$link."'>".$title."</a></li>\n";
	}
}
?>
													</ul>
											</div>
												
<?php 
$title = 'Search';
echo "<div class='wmtQuick' id='oc_Search' style='display:none'><table width='100%'><tr><td style='text-align:center;padding-top:30px'><h3>Select profile at left or<br/>search using search box at top.</h3>\n";
foreach ($olist as $data) {
	if ($data['title'] != $title) {
		if ($title) echo "</table></div>\n"; // end previous section
		$title = $data['title']; // new section
		$link = strtolower(str_replace(' ', '_', $title));
		echo "<div class='wmtQuick' id='oc_".$link."' style='display:none'><table>\n";
	}
	$text = ($data['description']) ? $data['description'] : $data['name'];
	$id = str_replace('.', '_', $data['code']);
	echo "<tr><td style='white-space:nowrap;width:60px'><input class='wmtCheck' type='checkbox' id='mark_".$id."' code='".$data['code']."' desc='".htmlspecialchars($text)."' > <b>".$data['code']."</b></input> - </td><td style='padding-top:0'>".$text."</td></tr>\n";
}
if ($title) echo "</table></div>\n"; // end if at least one section
?>
											</div>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<!-- End Order Entry -->

		<!--  Order Review -->
		<div class="wmtMainContainer wmtColorMain" id="orderReview"
			style="width: 100%">
			<div class="wmtCollapseBar wmtColorBar" id="OrderCollapseBar" style="<?php if ($status != 'i') echo "border-radius:5px" ?>" onclick="togglePanel('OrderBox','OrderImageL','OrderImageR','OrderCollapseBar')">
				<table style="width: 100%">
					<tr>
						<td><img id="OrderImageL" align="left"
							src="<?php echo $webroot;?>/library/wmt/fill-<?php echo ($status != 'i')? "270" : "090" ?>.png"
							border="0" alt="Show/Hide" title="Show/Hide" /></td>
						<td class="wmtChapter" style="text-align: center">Order Review</td>
						<td style="text-align: right"><img id="OrderImageR"
							src="<?php echo $webroot;?>/library/wmt/fill-<?php echo ($status != 'i')? "270" : "090" ?>.png"
							border="0" alt="Show/Hide" title="Show/Hide" /></td>
					</tr>
				</table>
			</div>

			<div class="wmtCollapseBox" id="OrderBox" style="<?php if ($status != 'i') echo 'display:none' ?>">
				<table style="width: 100%">
					<tr>
						<td>
							<fieldset>
								<legend>Diagnosis Codes</legend>

								<table id="codeTable" style="width: 100%">
									<tr>
										<th class="wmtHeader" style="width: 60px">Action</th>
										<th class="wmtHeader" style="width: 120px">Diagnosis</th>
										<th class="wmtHeader">Description</th>
									</tr>

<?php 
// load the existing diagnosis codes
$newRow = '';
$diag_array = array();
if ($order_data->diagnoses)
	$diag_array = explode("|", $order_data->diagnoses); // code & text

foreach ($diag_array AS $diag) {
	list($code,$text) = explode("^", $diag);
	if (empty($code)) continue;
	if (strpos($code,":") !== false)	
		list($dx_type,$code) = explode(":", $code);

	if (!$dx_type) $dx_type = 'ICD9';
	$key = str_replace('.', '_', $code);
	$code = $dx_type.":".$code;
	
	// add new row
	$newRow .= "<tr id='code_".$key."'>";
	$newRow .= "<td><input type='button' class='wmtButton' value='remove' style='width:60px' onclick=\"removeCodeRow('code_".$key."')\" /></td>\n";
	$newRow .= "<td class='wmtLabel'><input type='text' name='dx_code[]' class='wmtFullInput code' style='font-weight:bold' readonly value='".$code."'/>\n";
	$newRow .= "</td><td class='wmtLabel'><input name='dx_text[]' type='text' class='wmtFullInput name' readonly value='".$text."'/>\n";
	$newRow .= "</td></tr>\n";
}

// anything found
if ($newRow) {
	echo $newRow;
}
else { // create empty row
?>
										<tr id="codeEmptyRow">
										<td colspan="3"><b>NO DIAGNOSIS CODES SELECTED</b></td>
									</tr>
<?php } ?>
									</table>
							</fieldset>

						</td>
					</tr>

<?php 
// create unique identifier for order number
if ($viewmode) {
	$ordnum = $order_data->order_number;
}
else {
	$ordnum = $GLOBALS['adodb']['db']->GenID('order_seq');
	
	// duplicate checking
	$dupchk = sqlQuery("SELECT procedure_order_id AS id FROM procedure_order WHERE procedure_order_id = ?",array($ordnum));
	while($dupchk['id']) {
		$ordnum = $GLOBALS['adodb']['db']->GenID('order_seq');
		$dupchk = sqlQuery("SELECT procedure_order_id AS id FROM procedure_order WHERE procedure_order_id = ?",array($ordnum));
	} 
}
?>
						<tr>
						<td>
							<fieldset>
								<legend>Order Requisition - <?php echo $ordnum ?></legend>
								<input type="hidden" name="order_number"
									value="<?php echo $ordnum ?>" />

								<table style="width: 100%; margin-bottom: 10px">
									<tr>
										<td class="wmtHeader">LABORATORY PROCESSOR</td>
									</tr>
									<tr>
										<td class="wmtOutput" style="font-weight: bold"><input
											type="hidden" name="lab_id" value="<?php echo $lab_id ?>" />
												<?php echo $lab_data['name'] ?>
											</td>
									</tr>
								</table>

								<hr style="border-color: #f0f0f0" />
									
<?php if ($ins_list[0]->plan_type == 2) { // medicare ?>
									<table style="margin-bottom: 10px">
									<tr>
										<td colspan="8"><input type="checkbox" class="wmtCheck"
											id="order_abn_signed" name="order_abn_signed" value="Y"
											<?php if ($order_data->order_abn_signed) echo "checked" ?> />
											<label class="wmtLabel" style="vertical-align: middle">ABN
												(Advanced Beneficiary Notice) Signed</label></td>
									</tr>
								</table>
<?php } ?>
									
									<table style="margin-bottom: 10px">
									<tr>
										<td colspan="10"><input type="checkbox" class="wmtCheck"
											id="order_psc" name="order_psc" value="1"
											<?php if ($order_data->order_psc || (!$viewmode && $GLOBALS['wmt_lab_psc'])) echo "checked" ?> />
											<label class="wmtLabel" style="vertical-align: middle">Specimen
												Not Collected [ PSC Hold Order ]</label></td>
									</tr>
									<tr id="ship_data" style="<?php if ($order_data->order_psc || (!$viewmode && $GLOBALS['wmt_lab_psc'])) echo "display:none" ?>">
										<td><label class="wmtLabel" style="vertical-align: middle">Transport:</label>
										</td>
										<td colspan="4"><input type="text" class="wmtInput"
											id="specimen_transport" name="specimen_transport" readonly
											style="width: 220px"
											value="<?php echo $order_data->specimen_transport ?>" /></td>
									</tr>
									<tr id="sample_data" style="<?php if ($order_data->order_psc || (!$viewmode && $GLOBALS['wmt_lab_psc'])) echo "display:none" ?>">
<?php if ($GLOBALS['wmt::auto_draw_bill']) { ?>
										<td style='min-width: 80px'><label class="wmtLabel">Collected
												By: </label></td>
										<td style="white-space: nowrap"><select id='specimen_draw'
											name='specimen_draw' class='wmtSelect'>
													<?php ListSel($order_data->specimen_draw,'Lab_Draw') ?>
												</select></td>
<?php } ?>
										<td style='min-width: 70px'><label class="wmtLabel">Collection Date:
										</label></td>
										<td style="white-space: nowrap"><input class="wmtInput"
											type='text' style='width: 100px !important' name='date_collected'
											id='date_collected'
											value='<?php echo $viewmode ? (!goodDate($order_data->date_collected))? '' : date('Y-m-d',strtotime($order_data->date_collected)) : date('Y-m-d'); ?>'
											title='<?php xl('yyyy-mm-dd Date sample taken','e'); ?>'
											onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
											<img src='../../pic/show_calendar.gif' align='absbottom'
											width='24' height='22' id='img_date_collected' border='0'
											alt='[?]' style='cursor: pointer; cursor: hand;'
											title='<?php xl('Click here to choose a date','e'); ?>'></td>
										<td
											style='text-align: right; min-width: 45px; white-space: nowrap'>
											<label class="wmtLabel">Time: </label>
										</td>
										<td style="white-space: nowrap"><input type="text"
											class="wmtInput" style="width: 50px !important" name='time_collected'
											id='time_collected'
											value='<?php echo $viewmode ? (!goodDate($order_data->date_collected))? '' : date('H:i',strtotime($order_data->date_collected)) : date('H:i'); ?>' />
											<small>( 24hr )</small></td>
<?php if ($lab_data['npi'] != 'QUEST' && $lab_data['recv_fac_id'] != 'QBA') { ?>
											<td style="text-align: right; min-width: 65px !important;"><label
											class="wmtLabel">Volume: </label></td>
										<td style="white-space: nowrap"><input type="text"
											class="wmtInput" style="width: 65px !important;" name='specimen_volume'
											id='specimen_volume'
											value='<?php echo $order_data->specimen_volume; ?>' /> <small>(
												ml )</small></td>
<?php } // END SPECIAL FOR QBA ?>
											<td style="text-align: right; min-width: 70px"><label
											class="wmtLabel" style="vertical-align: middle">Fasting:</label>
										</td>
										<td style="white-space: nowrap"><select
											name='specimen_fasting' class='wmtSelect' style='width: 80px !important;'>
													<?php ListSel($order_data->specimen_fasting,'yesno') ?>
												</select></td>
										<td style="width: 80%"></td>
									</tr>

									<tr id="psc_data" style="<?php if (!$order_data->order_psc && !$GLOBALS['wmt_lab_psc']) echo "display:none" ?>">
										<td style="min-width: 100px"><label class="wmtLabel">Scheduled
												Date: </label></td>
										<td style="width: 100%"><input class="wmtInput" type='text'
											style='width: 100px !important' name='date_pending' id='date_pending'
											value='<?php echo $viewmode ? (!goodDate($order_data->date_pending))? '' : date('Y-m-d',strtotime($order_data->date_pending)) : ''; ?>'
											title='<?php xl('yyyy-mm-dd Date sample scheduled','e'); ?>'
											onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
											<img src='../../pic/show_calendar.gif' align='absbottom'
											width='24' height='22' id='img_date_pending' border='0'
											alt='[?]' style='cursor: pointer; cursor: hand'
											title='<?php xl('Click here to choose a date','e'); ?>'></td>
										<!-- td style='width:40px;text-align:right'>
												<label class="wmtLabel">Time: </label>
											</td>
											</td><td>
												<input type="input" id="pending_time" name="pending_time" class="wmtInput" style="width:65px" 
												value='<?php echo $viewmode ? (strtotime($order_data->date_collected) === false)? '' : date('h:ia',strtotime($order_data->date_collected)) : ''; ?>' />
											</td -->
									</tr>
								</table>

								<hr style="border-color: #f0f0f0" />

								<table id="order_table" style="width: 100%; margin-bottom: 25px">
									<tr>
										<th class="wmtHeader" style="width: 125px; padding-left: 9px">Actions</th>
										<th class="wmtHeader" style="width: 100px">Profile / Test</th>
										<th class="wmtHeader">General Description</th>
										<!-- th class="wmtHeader" style="width:300px">Order Entry Questions</th -->
									</tr>
<?php 
// load the existing requisition codes
$newRow = '';
foreach ($item_list as $order_item) { // $item = array of objects
	if (!$order_item->procedure_code) continue;
	$key = str_replace('.', '_', $order_item->procedure_code);

	// generate test row
	$newRow .= "<tr id='test_".$key."'>\n";
	$newRow .= "<td style='vertical-align:top'>\n";
	$newRow .= "<input type='button' class='wmtButton' value='remove' style='width:60px' onclick=\"removeTestRow('test_".$key."')\" /> \n";
	$newRow .= "<input type='button' class='wmtButton' value='details' style='width:60px' onclick=\"testOverview('".$order_item->procedure_code."')\" /></td>\n";
	$newRow .= "</td>\n";
	$newRow .= "<td class='wmtLabel' style='vertical-align:top;padding-top:5px;font-weight:bold' />";
	$newRow .= "<input name='test_code[]' type='text' class='wmtFullInput test' readonly='readonly' value='".$order_item->procedure_code."' ";
	if ($order_item->procedure_type == 'pro') {
		$newRow .= "style='font-weight:bold;color:#c00' /><input type='hidden' name='test_profile[]' value='pro' />";
	}
	else {
		$newRow .= "style='font-weight:bold' /><input type='hidden' name='test_profile[]' value='ord' />";
	} 
 	$newRow .= "</td><td colspan='2' class='wmtLabel' style='text-align:right;vertical-align:top;padding-top:5px'><input name='test_text[]' type='text' class='wmtFullInput' readonly value='".$order_item->procedure_name."'/>\n";
			
	// add profile tests if necessary
	if ($order_item->procedure_type == 'pro') {
		// retrieve all component test if profile
		$codes = $comps = "";
		$profile = array();
		$record = sqlQuery("SELECT related_code AS components FROM procedure_type WHERE procedure_code = ? AND lab_id = ? AND procedure_type = 'pro' ",
				array($order_item->procedure_code, $lab_id));
		if ($record['components']) {
			$list = explode("^", $record['components']);
			if (!is_array($list)) $list = array($list); // convert to array if necessary
			foreach ($list AS $comp) $comps[$comp] = "'$comp'";
			$codes = implode(",", $comps);
		}
		
		// component codes found
		if ($codes) {
			$query = "SELECT procedure_type_id AS id, procedure_code AS component, description, name AS title FROM procedure_type ";
			$query .= "WHERE activity = 1 AND lab_id = ? AND procedure_type = 'ord' ";
			$query .= "AND procedure_code IN ( ".$codes." ) ";
			$query .= "GROUP BY procedure_code ORDER BY procedure_code ";
			$result = sqlStatement($query,array($lab_id));
		
			$aoe_count = 0;
			while ($profile = sqlFetchArray($result)) {
				$description = ($profile['description'])? $profile['description'] : $profile['title'];
				$newRow .= "<input type='text' class='wmtFullInput component' style='margin-top:5px' readonly unit='".$profile['component']."' value='".$profile['component']." - ".$description."'/>\n";
					
				// add component AOE questions if necessary
				$result2 = sqlStatement("SELECT aoe.procedure_code AS code, aoe.question_code, aoe.question_text, aoe.tips, answer FROM procedure_answers ans ".
					"LEFT JOIN procedure_questions aoe ON aoe.question_code = ans.question_code ".
					"WHERE aoe.lab_id = ? AND ans.procedure_order_id = ? AND ans.procedure_order_seq = ? AND aoe.activity = 1 ORDER BY ans.answer_seq",
						array($lab_id, $order_item->procedure_order_id, $order_item->procedure_order_seq));
		
				while ($aoe2 = sqlFetchArray($result2)) {
					$question = str_replace(':','',$aoe2['aoe_question_desc']);
					if ($aoe2['analyte_cd']) {
						$newRow .= "<input type='hidden' name='aoe".$aoe['code']."_label[]' value='".$question."' />\n";
						$newRow .= "<input type='hidden' name='aoe".$aoe['code']."_code[]' value='".$aoe2['analyte_cd']."' />\n";
						$newRow .= "<input type='hidden' name='aoe".$aoe['code']."_unit[]' value='".$aoe2['unit_cd']."' />\n";
						$newRow .= "<div style='margin-top:5px'>".$question.": <input type='text' name='aoe".$aoe2['code']."_text[]' title='".$aoe2['result_filter']."' class='wmtFullInput aoe' value='".$test["aoe{$aoe_count}_text"]."' style='width:300px' /></div>\n";
						$aoe_count++;
					}
				}
			}
		}
	}

	// add AOE questions if necessary
	$result = sqlStatement("SELECT aoe.procedure_code AS code, aoe.question_code, aoe.question_text, aoe.tips, answer FROM procedure_questions aoe ".
		"LEFT JOIN procedure_answers ans ON aoe.question_code = ans.question_code ".
		"WHERE aoe.lab_id = ? AND aoe.procedure_code = ? AND ans.procedure_order_id = ? AND ans.procedure_order_seq = ? AND aoe.activity = 1 ORDER BY ans.answer_seq",
			array($lab_id, $order_item->procedure_code, $order_item->procedure_order_id, $order_item->procedure_order_seq));
			
	$aoe_count = 0;
	while ($aoe = sqlFetchArray($result)) {
		$question = str_replace(':','',$aoe['question_text']);
		if ($aoe['code']) {
			$newRow .= "<input type='hidden' name='aoe".$aoe['code']."_label[]' value='".$question."' />\n";
			$newRow .= "<input type='hidden' name='aoe".$aoe['code']."_code[]' value='".$aoe['question_code']."' />\n";
			$newRow .= "<div style='margin-top:5px'>".$question.": <input type='text' name='aoe".$aoe['code']."_text[]' title='".$aoe['tips']."' class='wmtFullInput aoe' value='".$aoe['answer']."' style='width:300px' /></div>\n";
			$aoe_count++;
		}
	}
	
	$newRow .= "</td></tr>\n"; // finish up order row

}

// anything found
if ($newRow) {
	echo $newRow;
}
else { // create empty row
?>
										
										<tr id="orderEmptyRow">
										<td colspan="3"><b>NO PROFILES / TESTS SELECTED</b></td>
									</tr>
<?php } ?>
																			
									</table>

								<hr style="border-color: #f0f0f0" />

								<table style="width: 100%">
									<tr>
										<td><label class="wmtLabel">Order Comments:</label> <textarea
												id="clinical_hx" name="clinical_hx" rows="2"
												class="wmtFullInput"><?php echo htmlspecialchars($order_data->clinical_hx) ?></textarea>
										</td>
									</tr>
									<tr>
										<td><label class="wmtLabel">Patient Instructions:</label> <textarea
												id="patient_instructions" name="patient_instructions"
												rows="2" class="wmtFullInput"><?php echo htmlspecialchars($order_data->patient_instructions) ?></textarea>
										</td>
									</tr>
								</table>
							</fieldset>
						</td>
					</tr>

				</table>
			</div>
		</div>
		<!-- End Order Review -->

		<!-- Order Submission -->
		<div class="wmtMainContainer wmtColorMain" id="orderSubmission"
			style="width: 100%;">
			<div class="wmtCollapseBar wmtColorBar" id="InfoCollapseBar" style="<?php if ($status != 'i') echo "border-radius:5px" ?>" onclick="togglePanel('InfoBox','InfoImageL','InfoImageR','InfoCollapseBar')">
				<table style="width: 100%">
					<tr>
						<td style="text-align: left"><img id="InfoImageL"
							src="<?php echo $webroot;?>/library/wmt/fill-<?php echo ($status != 'i')? "270" : "090" ?>.png"
							border="0" alt="Show/Hide" title="Show/Hide" /></td>
						<td class="wmtChapter" style="text-align: center">Order Submission
						</td>
						<td style="text-align: right"><img id="InfoImageR"
							src="<?php echo $webroot;?>/library/wmt/fill-<?php echo ($status != 'i')? "270" : "090" ?>.png"
							border="0" alt="Show/Hide" title="Show/Hide" /></td>
					</tr>
				</table>
			</div>

			<div class="wmtCollapseBox" id="InfoBox" style="<?php if ($status != 'i') echo "display:none" ?>" >
				<table style="width: 100%">
					<tr>
						<td style="width: 50%">
							<table style="width: 100%">
								<tr>
									<td class="wmtLabel" nowrap style="text-align: right">Order
										Date:</td>
									<td nowrap><input class="wmtInput" type='text' size='10'
										name='date_ordered' id='date_ordered'
										value='<?php echo $viewmode ? (!goodDate($order_data->date_ordered))? '' : date('Y-m-d',strtotime($order_data->date_ordered)) : date('Y-m-d'); ?>'
										title='<?php xl('yyyy-mm-dd Date of order','e'); ?>'
										onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
										<img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
												id='img_date_ordered' border='0' alt='[?]' style='cursor:pointer;cursor:hand;<?php if ($status != 'i') echo "display:none" ?>'
												title='<?php xl('Click here to choose a date','e'); ?>'></td>

									<td class="wmtLabel" nowrap style="text-align: right">Physician:
									</td>
									<td><select class="wmtSelect" name='provider_id'
										id='provider_id' style="min-width: 150px; max-width: 200px">
											<option value=''>-- select --</option>
<?php 
	$rlist= sqlStatement("SELECT * FROM users WHERE authorized=1 AND active=1 AND npi != '' ORDER BY lname");
	while ($rrow= sqlFetchArray($rlist)) {
    	echo "<option value='" . $rrow['id'] . "'";
		if ($order_data->provider_id == $rrow['id']) echo " selected";
		if (!$order_data->provider_id && $_SESSION['authUserID'] == $rrow['id']) echo " selected";
		echo ">" . $rrow['lname'].', '.$rrow['fname'].' '.$rrow['mname'];
    	echo "</option>";
  	}
?>
											</select></td>
								</tr>

								<tr>
									<td class="wmtLabel" nowrap style="text-align: right">Order
										Status:</td>
									<td nowrap><input type='text' class="wmtInput" readonly
										style="width: 150px"
										value="<?php echo ListLook($status, 'Lab_Form_Status') ?>" />
									</td>

									<td class="wmtLabel" nowrap style="text-align: right">Process
										Date:</td>
									<td nowrap><input type='text' class="wmtInput" readonly
										style="width: 150px"
										value="<?php echo ($order_data->date_transmitted > 0)?date('Y-m-d H:i:s',strtotime($order_data->date_transmitted)):''?>" />
									</td>
								</tr>
<?php 
if ($GLOBALS['wmt::lab_ins_pick']) { // special processing for sfa ?>
									<tr>		
										<td class="wmtLabel" nowrap style="text-align:right">Bill To: </td>
										<td colspan="3" nowrap>
											<input type="hidden" id="request_handling" name="request_handling" value=""/>
											<select class="wmtSelect" name="request_billing" id="request_billing" style="width:140px">
<?php 
	if (!$order_data->request_billing) echo "<option value=''>--select--</option>";
		
	$rlist= sqlStatement("SELECT * FROM list_options WHERE list_id = 'Lab_Billing' ORDER BY seq");
	while ($rrow = sqlFetchArray($rlist)) {
		if ($rrow['option_id'] == 'T') continue; // third-party not an option here
		echo "<option value='" . $rrow['option_id'] . "'";
		if ($order_data->request_billing == $rrow['option_id']) echo " selected";
		echo ">" . $rrow['title'] . "</option>";
  	}
	if ($status == 'i') { // still incomplete so they can change billing
	  	foreach ($ins_list AS $ins) {
	  	//	if ($ins->type != 'primary' && $ins->type != 'secondary' && $ins_type != 'tertiary') continue;
	  		echo "<option value='" . $ins->id . "'";
	  		if ($order_data->request_billing == $ins->id) echo " selected";
	  		if (empty($order_data->request_billing) && $ins->type == 'primary') echo " selected";
  			echo ">" . $ins->company_name . "</option>";
	  	}
	} elseif ( is_numeric($order_data->request_billing) ) { // order submitted, no edits allowed

		$ins = new wmtInsurance($order_data->request_billing);
		echo "<option value='" . $order_data->request_billing . "' selected >";
		echo ($ins->company_name) ? $ins->company_name : "INSURANCE MISSING"; 
		echo "</option>";
	
	}
?>
											</select>
										</td>
									</tr>
<?php 
} else { ?>
								<tr>
									<td class="wmtLabel" nowrap style="text-align: right">Bill To: </td>
									<td nowrap><select class="wmtSelect" name="request_billing"
										id="request_billing" style="width: 105px">
<?php 
	$bill_option = "";
	if (($order_data->ins_primary && $order_data->ins_primary != 'No Insurance') || 
			($order_data->ins_secondary && $order_data->ins_secondary != 'No Insurance') || 
					$ins_list[0]->company_name || $ins_list[1]->company_name) { // insurance available
		$bill_option .= "<option value='T'";
		if ($order_data->request_billing == 'T') $bill_option .= " selected";
		$bill_option .= ">Third Party</option>\n";
	}
	$bill_option .= "<option value='P'";
	if ($order_data->request_billing == 'P') $bill_option .= " selected";
	$bill_option .= ">Patient Bill</option>\n";
	$bill_option .= "<option value='C'";
	if ($order_data->request_billing == 'C') $bill_option .= " selected";
	$bill_option .= ">Client Bill</option>\n";
	
	echo $bill_option;	
?>
											</select></td>

									<td class="wmtLabel" nowrap style="text-align: right">Account:
									</td>
									<td nowrap><select class="wmtInput" name="request_account"
										style="max-width: 200px" />
<?php 
	$rlist= sqlStatement("SELECT * FROM list_options WHERE list_id = 'Quest_Accounts' ORDER BY seq");
	while ($rrow= sqlFetchArray($rlist)) {
    	echo "<option value='" . $rrow['option_id'] . "'";
    	if ($order_data->request_account) {
			if ($order_data->request_account == $rrow['option_id']) echo " selected";
		}
		else {
			if ($siteid == $rrow['option_id']) echo " selected";
		}
		echo ">" . htmlentities($rrow['title'],ENT_QUOTES);
    	echo "</option>";
  	}
?>
											</select></td>
								</tr>
<?php } ?>

							</table>
						</td>

						<td>
							<table style="width: 100%">
								<tr>
									<td class="wmtLabel" colspan="3">Clinic Notes: <small
										style='font-weight: normal; padding-left: 20px'>[ Not sent to
											lab or printed on requisition ]</small> <textarea
											name="order_notes" id="order_notes" class="wmtFullInput"
											rows="4"><?php echo htmlspecialchars($order_data->order_notes) ?></textarea>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<!-- End Order Submission -->

		<!-- END OF ORDER -->


		<!-- START OF RESULTS -->

		<!--  External Results -->
		<div class="wmtMainContainer wmtColorMain" id="resultEntry" style="width:100%;<?php if ($status == 'i' || $lab_data['protocol'] == 'INT') echo "display:none" ?>">
			<div class="wmtCollapseBar wmtColorBar" id="ExternalCollapseBar"
				onclick="togglePanel('ExternalBox','ExternalImageL','ExternalImageR','ExternalCollapseBar')">
				<table style="width: 100%">
					<tr>
						<td><img id="ExternalImageL" align="left"
							src="<?php echo $webroot;?>/library/wmt/fill-090.png" border="0"
							alt="Show/Hide" title="Show/Hide" /></td>
						<td class="wmtChapter" style="text-align: center">External Results
						</td>
						<td style="text-align: right"><img id="ExternalImageR"
							src="<?php echo $webroot;?>/library/wmt/fill-090.png" border="0"
							alt="Show/Hide" title="Show/Hide" /></td>
					</tr>
				</table>
			</div>

			<div class="wmtCollapseBox" id="ExternalBox">
				<table style="width: 100%">
					<tr>
						<td>
							<fieldset>
								<legend>Observation Results - <?php echo $order_data->order_number ?></legend>
								<table style="width: 100%">
									<tr>
										<td class="wmtHeader">LABORATORY PROCESSOR</td>
									</tr>
									<tr>
										<td class="wmtOutput" style="font-weight: bold"><input
											type="hidden" name="lab_id" value="<?php echo $lab_id ?>" />
												<?php echo $lab_data['name'] ?>
											</td>
									</tr>
								</table>
<?php 
	if (strtotime($order_data->result_datetime)) { // results available
?>
									<hr
									style="border-color: #eee; margin-top: 15px; margin-bottom: 15px" />

								<table id="sample_table" border="0" cellspacing="0"
									cellpadding="2">
									<tr>
										<td colspan=7 class="wmtHeader" style="padding-bottom: 10px">
											OBSERVATION SUMMARY</td>
									</tr>
<?php 
	if ($order_data->control_id) {
?>
										<tr>
										<td style="padding-bottom: 10px"><label class="wmtLabel"
											style="vertical-align: middle">Accession Number:</label></td>
										<td style="padding-bottom: 10px" colspan='2'><input
											type="text" class="wmtInput" readonly style="width: 220px"
											value="<?php echo $order_data->control_id ?>" /></td>
									</tr>
<?php 
	}
?>										<tr>
										<td style='width: 100px'><label class="wmtLabel">Ordered Date:
										</label></td>
										<td><input class="wmtInput" type='text' size='10' readonly
											value='<?php echo ($order_data->date_transmitted == 0)? '' : date('Y-m-d',strtotime($order_data->date_transmitted)); ?>' />
										</td>
										<td style='text-align: right'><label class="wmtLabel">Time: </label>
										</td>
										<td><input type="text" class="wmtInput" style="width: 65px"
											readonly
											value='<?php echo ($order_data->date_transmitted == 0)? '' : date('h:ia',strtotime($order_data->date_transmitted)); ?>' />
										</td>
									</tr>
									<tr>
										<td style='width: 100px'><label class="wmtLabel">Collection
												Date: </label></td>
										<td><input class="wmtInput" type='text' size='10' readonly
											value='<?php echo ($order_data->date_collected == 0)? '' : date('Y-m-d',strtotime($order_data->date_collected)); ?>' />
										</td>
										<td style='text-align: right'><label class="wmtLabel">Time: </label>
										</td>
										<td><input type="text" class="wmtInput" style="width: 65px"
											readonly
											value='<?php echo ($order_data->date_collected == 0)? '' : date('h:ia',strtotime($order_data->date_collected)); ?>' />
										</td>
									</tr>
									<tr>
										<td style='width: 100px'><label class="wmtLabel">Reported
												Date: </label></td>
										<td><input class="wmtInput" type='text' size='10' readonly
											value='<?php echo ($order_data->result_datetime == 0)? '' : date('Y-m-d',strtotime($order_data->result_datetime)); ?>' />
										</td>
										<td style='text-align: right'><label class="wmtLabel">Time: </label>
										</td>
										<td><input type="text" class="wmtInput" style="width: 65px"
											readonly
											value='<?php echo ($order_data->result_datetime == 0)? '' : date('h:ia',strtotime($order_data->result_datetime)); ?>' />
										</td>
										<td style='text-align: right; width: 120px'><label
											class="wmtLabel">Status: </label></td>
										<td><input type="text" class="wmtInput" style="width: 150px"
											readonly
											value='<?php echo ListLook($order_data->status,'Lab_Form_Status'); ?>' />
										</td>
									</tr>
<?php if ($order_data->lab_notes) { ?>
										<tr>
										<td style='width: 100px; vertical-align: top'><label
											class="wmtLabel">Lab Comments: </label></td>
										<td colspan=5><textarea class="wmtInput" style="width: 100%"
												readonly rows=2><?php echo $order_data->lab_notes ?></textarea>
										</td>
									</tr>
<?php } ?>
									</table>

								<hr
									style="border-color: #eee; margin-top: 15px; margin-bottom: 15px" />

								<table id="result_table" style="min-width: 900px; width: 100%">
									<tr>
										<td colspan='10' class="wmtHeader">RESULT DETAIL INFORMATION</td>
									</tr>
<?php
		// loop through each ordered item
		$last_code = "FIRST";
		$facility_list = array();
		foreach ($item_list as $order_item) {
//			$report_data = wmtResult::fetchResult($order_item->procedure_order_id, $order_item->procedure_order_seq);
//			if (!$report_data) continue; // no results yet
			$key = $order_item->procedure_order_seq;
			$report_list = wmtResult::fetchResultList($order_item->procedure_order_id, $key);
			if (!$report_list) continue; // no results yet
?>
										<tr>
										<td colspan="10" class="wmtLabel"
											style="text-align: left; font-size: 1.1em">
												<?php if ($last_code != "FIRST") echo "<br/><br/>" ?>
												<?php echo $order_item->procedure_code ?> - <?php echo $order_item->procedure_name ?>
											</td>
									</tr>
			
<?php 
			$last_code = $order_item->procedure_code;
			
			// ADDED TO SUPPORT MULTIPLE RESULT FOR SINGLE TEST
			foreach ($report_list AS $report_data) {
			
			$result_date = (strtotime($report_data->date_report))? date('Y-m-d',strtotime($report_data->date_report)): '';
			$result_list = wmtResultItem::fetchItemList($report_data->procedure_report_id);
			if (!result_list) continue; // no details yet

			// process each observation
			$first = true;
			foreach ($result_list AS $result_data) {
				// collect facility information
				if ($result_data->facility && !$facility_list[$result_data->facility]) {
					$facility = sqlQuery("SELECT * FROM procedure_facility WHERE code = ?",array($result_data->facility));
					if ($facility) $facility_list[$facility['code']] = $facility;
				}
				
				// do we need a header?
				if ($first) { // changed test code
					$first = false;
?>
										<tr style="font-size: 9px; font-weight: bold">
										<td style="min-width: 20px; width: 20px">&nbsp;</td>
										<td style="width: 7%">RESULT</td>
										<td style="width: 25%">DESCRIPTION</td>
										<td style="width: 8%">VALUE</td>
										<td style="width: 11%">UNITS</td>
										<td style="padding-left: 10px; width: 11%">REFERENCE</td>
										<td style="text-align: center; width: 8%">FLAG</td>
										<td style="text-align: center; width: 16%">OBSERVATION</td>
										<td style="text-align: center; width: 7%">STATUS</td>
										<td style="text-align: center; width: 5%">FACILITY</td>
										<td></td>
									</tr>
<?php 
					$last_code = $result_data->result_code;
				}
	
				$abnormal = $result_data->abnormal; // in case they sneak in a new status
				if ($result_data->abnormal == 'H') $abnormal = 'High';
				if ($result_data->abnormal == 'L') $abnormal = 'Low';
				if ($result_data->abnormal == 'HH') $abnormal = 'Alert High';
				if ($result_data->abnormal == 'LL') $abnormal = 'Alert Low';
				if ($result_data->abnormal == '>') $abnormal = 'Panic High';
				if ($result_data->abnormal == '<') $abnormal = 'Panic Low';
				if ($result_data->abnormal == 'A') $abnormal = 'Abnormal';
				if ($result_data->abnormal == 'AA') $abnormal = 'Critical';
				if ($result_data->abnormal == 'S') $abnormal = 'Susceptible';
				if ($result_data->abnormal == 'R') $abnormal = 'Resistant';
				if ($result_data->abnormal == 'I') $abnormal = 'Intermediate';
				if ($result_data->abnormal == 'NEG') $abnormal = 'Negative';
				if ($result_data->abnormal == 'POS') $abnormal = 'Positive';
?>
										<tr
										<?php if ($abnormal) echo 'style="font-weight:bold;color:#bb0000"'?>>
										<td>&nbsp;</td>
										<td>
												<?php echo $result_data->result_code ?>
											</td>
										<td>
												<?php echo $result_data->result_text ?>
											</td>
<?php 
				if ($result_data->result_data_type) { // there is an observation
					if ($result_data->units || $result_data->range || $abnormal) {
?>
											<td style="font-family: monospace">
												<?php if ($result_data->result != ".") echo htmlspecialchars($result_data->result) ?>
											</td>
										<td style="font-family: monospace">
												<?php echo htmlspecialchars($result_data->units) ?>
											</td>
										<td style="font-family: monospace; padding-left: 10px">
												<?php echo htmlspecialchars($result_data->range) ?>
											</td>
										<td style="font-family: monospace; text-align: center">
												<?php echo $abnormal ?>
											</td>
<?php 
					} else {
?>
											<td colspan='4'
											style="font-family: monospace; text-align: left">
												<?php if ($result_data->result != ".") echo htmlspecialchars($result_data->result) ?>
											</td>
<?php 
					}
?>
											<td style="font-family: monospace; text-align: center">
												<?php echo (strtotime($result_data->date))? date('Y-m-d H:i',strtotime($result_data->date)): '' ?>
											</td>
										<td style="font-family: monospace; text-align: center">
												<?php echo htmlspecialchars($result_data->result_status) ?>
											</td>
										<td style="font-family: monospace; text-align: center">
												<?php echo htmlspecialchars($result_data->facility) ?>
											</td>
										<td></td>
									</tr>
<?php
					if ($result_data->comments) { // put comments below test line
?>
										<tr
										<?php if ($abnormal) echo 'style="font-weight:bold;color:#bb0000"'?>>
										<td colspan="1">&nbsp;</td>
										<td colspan="8"
											style="padding-left: 120px; text-align: left; font-family: monospace;">
												<?php echo nl2br($result_data->comments); ?>
											</td>
										<td></td>
									</tr>
<?php 
					} // end if comments
				} // end if obser value
				else { 
?>
											<td colspan="6"
										style="padding-left: 120px; text-align: left; font-family: monospace">
												<?php echo nl2br($result_data->comments); ?>
											</td>
									<td
										style="font-family: monospace; text-align: center; width: 10%">
												<?php echo htmlspecialchars($result_data->facility) ?>
											</td>
									<td></td>
									</tr>
<?php
				} // end if observ 
			} // end result foreach
			} // NEW MULTIPLE REPORT LOOP
		} // end foreach ordered item
		
		// do we need a facility box at all?
		if (count($facility_list) > 0) {
?>
										<tr>
										<td colspan="10" style="padding: 10px 0 0 0">
											<hr
												style="border-color: #eee; margin-top: 15px; margin-bottom: 15px" />
											<table style="width: 100%">
												<tr style="font-size: 9px; font-weight: bold">
													<td style="min-width: 20px; width: 20px">&nbsp;</td>
													<td style="text-align: left; width: 10%">FACILITY</td>
													<td style="width: 25%">FACILITY TITLE</td>
													<td style="width: 35%">CONTACT INFORMATION</td>
													<td style="width: 20%">FACILITY DIRECTOR</td>
													<td></td>
												</tr>
<?php 
				foreach ($facility_list AS $facility_data) {
					if ($facility['phone']) {
						$phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~', '($1) $2-$3', $facility['phone']);
					}
					
					$director = $facility['director'];
					if ($facility['npi']) $director .= "<br/>NPI: ".$facility['npi']; // identifier

					$address = '';
					if ($facility['street']) $address .= $facility['street']."<br/>";
					if ($facility['street2']) $address .= $facility['street2']."<br/>";
					if ($facility['city']) $address .= $facility['city'].", ";
					$address .= $facility['state']."&nbsp;&nbsp;";
					if ($facility['zip'] > 5) $address .= preg_replace('~.*(\d{5})(\d{4}).*~', '$1-$2', $facility['zip']);
					else $address .= $facility['zip'];
?>					
												<tr style="font-family: monospace; vertical-align: baseline">
													<td>&nbsp;</td>
													<td class="wmtOutput">
														<?php echo $facility['code'] ?>
													</td>
													<td class="wmtOutput">
														<?php echo $facility['name'] ?>
													</td>
													<td class="wmtOutput">
														<?php echo $address ?>
													</td>
													<td class="wmtOutput">
														<?php echo $director ?>
													</td>
												</tr>
												<tr>
													<td colspan="5">&nbsp;</td>
												</tr>	
<?php
			} // end facility foreach
 		} // end facilities
?>
						 					</table>
										</td>
									</tr>
<?php 		
	} // end if results
	else { 
?>
									<table>
										<tr>
											<td style="font-weight: bold"><br />NO RESULTS HAVE BEEN
												RECEIVED</td>
										</tr>
									</table>
<?php 
	} // end result else
?>
									</table>
							</fieldset>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<!-- End External Display -->

		<!--  Result Review -->
		<div class="wmtMainContainer wmtColorMain" id="resultReview" style="width:100%;<?php if ($status == 'i' || $status == 's') echo "display:none" ?>">
			<div class="wmtCollapseBar wmtColorBar" id="ResultCollapseBar"
				onclick="togglePanel('ResultBox','ResultImageL','ResultImageR','ResultCollapseBar')">
				<table style="width: 100%">
					<tr>
						<td style="text-align: left"><img id="ResultImageL"
							src="<?php echo $webroot;?>/library/wmt/fill-090.png" border="0"
							alt="Show/Hide" title="Show/Hide" /></td>
						<td class="wmtChapter" style="text-align: center">Review
							Information</td>
						<td style="text-align: right"><img id="ResultImageR"
							src="<?php echo $webroot;?>/library/wmt/fill-090.png" border="0"
							alt="Show/Hide" title="Show/Hide" /></td>
					</tr>
				</table>
			</div>

			<div class="wmtCollapseBox" id="ResultBox">
				<table style="width: 100%">
					<tr>
						<td style="width: 50%">
							<table style="width: 100%">
								<tr>
									<td class="wmtLabel" nowrap style="text-align: right">Reviewed
										By:</td>
									<td>
<?php if ($order_data->reviewed_id) { ?>
											<input type="hidden" name='reviewed_id'
										value="<?php echo $order_data->reviewed_id ?>" /> <input
										type="text" class="wmtInput nolock" style="min-width: 150px"
										readonly
										<?php 
	$rrow= sqlQuery("SELECT * FROM users WHERE id = ?",array($order_data->reviewed_id));
	if ($rrow['lname']) echo 'value="' . $rrow['lname'].', '.$rrow['fname'].' '.$rrow['mname'] . '"'
?> />
<?php } else { ?>
											<select class="wmtInput nolock" name='reviewed_id'
										id='reviewed_id' style="min-width: 150px"
										onchange="$('#date_reviewed').val('<?php echo date('Y-m-d H:i') ?>')">
											<option value='_blank'>-- select --</option>
<?php 
	$rlist= sqlStatement("SELECT * FROM users WHERE authorized=1 AND active=1 AND npi != '' ORDER BY lname");
	while ($rrow= sqlFetchArray($rlist)) {
    	echo "<option value='" . $rrow['id'] . "'";
		if ($order_data->reviewed_id == $rrow['id']) echo " selected";
		echo ">" . $rrow['lname'].', '.$rrow['fname'].' '.$rrow['mname'];
    	echo "</option>";
  	}
?>
											</select>
<?php } ?>
										</td>
									<td class="wmtLabel" nowrap style="text-align: right">Reviewed
										Date:</td>
									<td nowrap><input class="wmtInput nolock" type='text' size='16'
										name='reviewed_date' id='date_reviewed' readonly
										value='<?php echo (!goodDate($order_data->reviewed_datetime))? '' : date('Y-m-d H:i',strtotime($order_data->reviewed_datetime)); ?>' />
									</td>
								</tr>

								<tr>
									<td class="wmtLabel" nowrap style="text-align: right">Notified
										By:</td>
									<td>
<?php if ($order_data->notified_id) { ?>
											<input type="hidden" name='notified_id'
										value="<?php echo $order_data->notified_id ?>" /> <input
										type="text" class="wmtInput nolock" style="min-width: 150px"
										readonly
										<?php 
	$rrow= sqlQuery("SELECT * FROM users WHERE id = ?",array($order_data->notified_id));
	if ($rrow['lname']) echo 'value="' . $rrow['lname'].', '.$rrow['fname'].' '.$rrow['mname'] . '"'
?> />
<?php } else { ?>
											<select class="wmtInput nolock" name='notified_id'
										id='notified_id' style="min-width: 150px"
										onchange="$('#date_notified').val('<?php echo date('Y-m-d H:i') ?>')">
											<option value='_blank'>-- select --</option>
<?php 
	$rlist= sqlStatement("SELECT * FROM users WHERE active=1 AND facility_id > 0 ORDER BY lname");
	while ($rrow= sqlFetchArray($rlist)) {
    	echo "<option value='" . $rrow['id'] . "'";
		if ($order_data->notified_id == $rrow['id']) echo " selected";
		echo ">" . $rrow['lname'].', '.$rrow['fname'].' '.$rrow['mname'];
    	echo "</option>";
  	}
?>
											</select>
<?php } ?>
										</td>
									<td class="wmtLabel" nowrap style="text-align: right">Notified
										Date:</td>
									<td nowrap><input class="wmtInput nolock" type='text' size='16'
										name='notified_date' id='date_notified' readonly
										value='<?php echo (!goodDate($order_data->notified_datetime))? '' : date('Y-m-d H:i',strtotime($order_data->notified_datetime)); ?>' />
									</td>
								</tr>

								<tr>
									<td class="wmtLabel" nowrap style="text-align: right">Person
										Contacted:</td>
									<td><input type='text' id='notified_person'
										name='notified_person' class="wmtFullInput"
										value="<?php echo $order_data->notified_person ?>"
										<?php if ($order_data->notified_id) echo "readonly" ?> /></td>
									<td colspan="2" class="wmtLabel" nowrap
										style="text-align: center">
<?php if ($GLOBALS['wmt::portal_enable'] == 'true' && $pat_data->allow_patient_portal == 'YES') {?>										
											Release to Patient Portal:&nbsp;
											<input type='checkbox' id='portal_flag' name='portal_flag'
										class="wmtCheck" value="1"
										<?php if ($order_data->portal_flag) echo 'checked' ?> />
<?php } ?>
										</td>
								</tr>

							</table>
						</td>

						<td>
							<table style="width: 100%">
								<tr>
									<td class="wmtLabel" colspan="3">Review Notes: <textarea
											name="review_notes" id="review_notes"
											class="wmtFullInput nolock" rows="4"
											<?php if ($order_data->reviewed_id) echo "readonly" ?>><?php echo htmlspecialchars($order_data->review_notes) ?></textarea>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<!-- End Result Review -->

		<!-- END RESULTS -->

		<br />

		<!-- Start of Buttons -->
		<table width="100%" border="0">
<?php if ($viewmode && $order_data->status != 'i') { ?>
				<tr>
				<td class="wmtLabel" colspan="4"
					style="padding-bottom: 10px; padding-left: 8px">Label Printer: <select
					class="nolock" id="labeler" name="labeler"
					style="margin-right: 10px">
							<?php getLabelers($_SERVER['REMOTE_ADDR'])?>
						</select> Quantity: <select class="nolock" name="count"
					style="margin-right: 10px">
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4">4</option>
						<option value="5">5</option>
				</select> <input class="nolock" type="button" tabindex="-1"
					onclick="printLabels(0)" value="Print Labels" />

				</td>
			</tr>
<?php } ?>
				<tr>
				<td class="wmtLabel"
					style="vertical-align: top; float: left; width: 95px"><a
					class="css_button" tabindex="-1" href="javascript:saveClicked()"><span>Save
							Work</span></a></td>
<?php if ($status == 'i') { ?>	
					<td class="wmtLabel" style="vertical-align: top; float: left"><a
					class="css_button" tabindex="-1" href="javascript:submitClicked()"><span>Submit
							Order</span></a></td>
				
<?php } ?>
					<td class="wmtLabel"><a class="css_button" tabindex="-1"
					href="javascript:printClicked()"><span>Printable Form</span></a></td>
<?php if ($order_data->order_abn_id) { ?>
					<td class="wmtLabel"><a class="css_button" tabindex="-1"
					href="<?php echo $document_url . $order_data->order_abn_id ?>"><span>ABN
							Document</span></a></td>
<?php } if ($order_data->order_req_id) { ?>
					<td class="wmtLabel"><a class="css_button" tabindex="-1"
					href="<?php echo $document_url . $order_data->order_req_id ?>"><span>Order
							Document</span></a></td>
<?php } if ($order_data->result_doc_id) { ?>
					<td class="wmtLabel"><a class="css_button" tabindex="-1"
					href="<?php echo $document_url . $order_data->result_doc_id ?>"><span>Result
							Document</span></a></td>
				<td class="wmtLabel"><a class="css_button" tabindex="-1"
					href="javascript:messageClicked()"><span>Send Message</span></a></td>
<?php } ?>
					<td class="wmtLabel" style="vertical-align: top; float: right">
<?php if (!$locked) { ?>
						<a class="css_button" tabindex="-1" href="javascript:doClose()"><span>Don't
							Save</span></a>
<?php } else { ?>
						<a class="css_button" tabindex="-1" href="javascript:doClose()"><span>Cancel</span></a>
<?php } ?>
					</td>
			</tr>
		</table>
		<!-- End of Buttons -->

		<input type="hidden" name="status"
			value="<?php echo ($order_data->status)?$order_data->status:'i' ?>" />
		<input type="hidden" name="priority"
			value="<?php echo ($order_data->priority)?$order_data->priority:'n' ?>" />
	</form>

</body>

<script>
		/* required for popup calendar */
		Calendar.setup({inputField:"date_pending", ifFormat:"%Y-%m-%d", button:"img_date_pending"});
		Calendar.setup({inputField:"date_collected", ifFormat:"%Y-%m-%d", button:"img_date_collected"});
		Calendar.setup({inputField:"date_ordered", ifFormat:"%Y-%m-%d", button:"img_date_ordered"});
//		Calendar.setup({inputField:"work_date", ifFormat:"%Y-%m-%d", button:"img_work_date"});
	</script>

</html>

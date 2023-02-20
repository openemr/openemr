<?php
/** **************************************************************************
 *	WMT/EDIT_ISSUE.PHP
 *
 *	Copyright (c)2013 - Williams Medical Technology, Inc.
 *
 *	This program is free software: you can redistribute it and/or modify it 
 *	under the terms of the GNU General Public License as published by the Free 
 *	Software Foundation, either version 3 of the License, or (at your option) 
 *	any later version.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
 *	FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for 
 *	more details.
 *
 *	You should have received a copy of the GNU General Public License along with 
 *	this program.  If not, see <http://www.gnu.org/licenses/>.	This program is 
 *	free software; you can redistribute it and/or modify it under the terms of 
 *	the GNU Library General Public License as published by the Free Software 
 *	Foundation; either version 2 of the License, or (at your option) any 
 *	later version.
 *
 *  @package standard
 *  @subpackage library
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Ron Criswell <info@keyfocusmedia.com>
 * 
 *************************************************************************** */
require_once("../../interface/globals.php");
include_once("$srcdir/wmt/wmt.include.php");
include_once("$srcdir/wmt/wmt.class.php");

use OpenEMR\Core\Header;

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

// form information
$form_name = 'issues';
$form_title = 'Add/Edit Issues';

// grab inportant stuff
$type = $_REQUEST['type'];
$id = $_REQUEST['issue'];
$pid = ($_REQUEST['pid'])? $_REQUEST['pid']: $_SESSION['pid'];
$encounter = ($_REQUEST['enc'])? $_REQUEST['enc']: $_SESSION['encounter'];
$task = ($_REQUEST['task'])? $_REQUEST['task']: 'edit';
if ($_POST['form_store']) $task = 'store';

$form_data = new wmtIssue($id); 

// If we are saving, then save and close the window.
if ($task == 'edit' && $_POST['form_save']) {
	$form_begin = fixDate($_POST['form_begin'], '');
	$form_end   = fixDate($_POST['form_end'], '');

	$form_data->date = date('Y-m-d H:i:s');
	$form_data->type = 'medical_problem';
	$form_data->title = formData('form_title');
	$form_data->begdate = $form_begin;
	$form_data->enddate = $form_end;
	$form_data->occurrence = formData('form_occurrence');
	$form_data->referredby = formData('form_referredby');
	$form_data->extrainfo = formData('form_description');
	$form_data->diagnosis = formData('form_diagnosis');
	$form_data->activity = 1;
	$form_data->comments = formData('form_comments');
	$form_data->pid = $pid;
	$form_data->user = $_SESSION['authId'];
	$form_data->outcome = formData('form_outcome');
	$form_data->destination = formData('form_destination');
	 
	if ($form_data->id) {
		$form_data->update();
	}
	else {
		$id = wmtIssue::insert($form_data);
		wmtIssue::linkEncounter($pid, $encounter, $id);
	}

	// Close this window and redisplay the updated list of issues.
	echo "<html><body><script language='JavaScript'>\n";
	echo " var myboss = opener ? opener : parent;\n";
	echo " if (myboss.refreshIssue) myboss.refreshIssue($id,'$tmp_title');\n";
	echo " else if (myboss.reloadIssues) myboss.reloadIssues();\n";
	echo " else myboss.location.reload();\n";
	echo " if (parent.$ && parent.$.fancybox) parent.$.fancybox.close();\n";
	echo " else window.close();\n";
	echo "</script></body></html>\n";
	exit();
}

// If we are unlinking.
if ($task == 'unlink') {
	if ($form_data->id) {
		wmtIssue::unlinkEncounter($pid, $encounter, $id);
	}

	// Close this window and redisplay the updated list of issues.
	echo "<html><body><script language='JavaScript'>\n";
	echo " var myboss = opener ? opener : parent;\n";
	echo " if (myboss.refreshIssue) myboss.refreshIssue($id,'$tmp_title');\n";
	echo " else if (myboss.reloadIssues) myboss.reloadIssues();\n";
	echo " else myboss.location.reload();\n";
	echo " if (parent.$ && parent.$.fancybox) parent.$.fancybox.close();\n";
	echo " else window.close();\n";
	echo "</script></body></html>\n";
	exit();
}

// If we are unlinking.
if ($task == 'store') {
	$form_begin = fixDate($_POST['form_begin'], '');
	$form_end   = fixDate($_POST['form_end'], '');

	$form_data->date = date('Y-m-d H:i:s');
	$form_data->type = 'medical_problem';
	$formTitle = formData('form_title');
	$form_data->title = $formTitle;
	$form_data->begdate = $form_begin;
	$form_data->enddate = $form_end;
	$form_data->occurrence = formData('form_occurrence');
	$form_data->referredby = formData('form_referredby');
	$form_data->extrainfo = formData('form_description');
	$formCode = formData('form_diagnosis');
	$form_data->diagnosis = $formCode;
	$form_data->activity = 1;
	$formComments = $_POST['form_comments'];
	$form_data->comments = $formComments;
	$form_data->pid = $pid;
	$form_data->user = $_SESSION['authId'];
	$form_data->outcome = formData('form_outcome');
	$form_data->destination = formData('form_destination');
	 
	$query = "SELECT COUNT(*) AS dup FROM list_options ";
	$query .= "WHERE list_id = 'Common_Diagnosis' AND codes = ? AND title = ?";
	$result = sqlQuery($query,array($formCode,$formTitle));
	if ($result['dup'] > 0) {
		$msg = 'ERROR: template [ '.$formCode.' - '.$formTitle.' ] exists!!\nPlease choose a different title.';
	}
	else {	
		$result = sqlQuery("SELECT COUNT(*) AS tot FROM list_options WHERE list_id = 'Common_Diagnosis' AND option_id LIKE '".$formCode."%' ");
		$key = $result['tot'] + 1;
		$query = "INSERT INTO list_options SET ";
		$query .= "list_id = 'Common_Diagnosis', ";
		$query .= "option_id = '".$formCode."-".$key."', ";
		$query .= "title = '$formTitle', ";
		$query .= "codes = '$formCode', ";
		$query .= "notes = '".mysql_real_escape_string($formComments)."' ";
		sqlStatement($query);
		
		$msg = "Template [ $formCode - $formTitle ] stored!!";
	}
}

// retrieve common diagnosis
$common = array();
$common[] = array(); // blank selection
$query = "SELECT * FROM list_options lo ";
$query .= "LEFT JOIN icd9_dx_code dx ON lo.codes = concat('ICD9:',dx.dx_code) OR lo.codes = concat('ICD9:',dx.formatted_dx_code) ";
$query .= "WHERE list_id = 'Common_Diagnosis' ORDER BY seq,option_id";
$results = sqlStatement($query);
while ($data = sqlFetchArray($results)) $common[] = $data;

// retrieve data objects
$pat_data = wmtPatient::getPidPatient($pid);
//$ins_data = wmtInsurance::getPidInsurance($pid);
//$enc_data = wmtEncounter::getEncounter($encounter);

?>
<!DOCTYPE HTML>
<html>
	<head>
		<?php //html_header_show();?>
		<title><?php echo $form_title ?> for <?php echo $pat_data->format_name; ?> on <?php echo $form_data->date; ?></title>
		<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

		<?php Header::setupHeader(['dialog', 'jquery', 'jquery-ui', 'datetime-picker']); ?>

		<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/wmt/wmt.default.css" />

		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/wmt/wmtstandard.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>
		
		<script>
			// This is for callback by the find-code popup.
			function set_related(codetype, code, selector, codedesc) {
				var f = document.forms[0];
				var d = '';
				var s = '';
				if (code) {
					s = codetype + ':' + code;
					d = codedesc;
				} 
				else {
					s = '';
					d = '';
				}
				f.form_diagnosis.value = s;
				f.form_description.value = d;
			}

			// This invokes the find-code popup.
			function sel_diagnosis() {
				dlgopen('<?php echo $GLOBALS['webroot'] ?>/interface/patient_file/encounter/wmt_find_code.php?codetype=ICD9,ICD10', '_blank', 500, 400);
			}

			// Process click on Delete link.
			function deleteme() {
				dlgopen('<?php echo $GLOBALS['webroot'] ?>/interface/patient_file/deleter.php?issue=<?php echo attr($issue) ?>', '_blank', 500, 450);
			  return false;
			}

			// Process click on Delete link.
			function unlinkme() {
				location.href='edit_issue.php?task=unlink&issue=<?php echo attr($issue); ?>&pid=<?php echo attr($pid); ?>&enc=<?php echo attr($encounter) ?>';
			  return false;
			}

			// Called by the deleteme.php window on a successful delete.
			function imdeleted() {
				closeme();
			}

			function closeme() {
			    if (parent.$) {
				    parent.reloadIssues();
				    parent.$.fancybox.close();
			    }
			    window.close();
			 }

			// Process click on Delete link.
			function storeme() {
				var f = document.forms[0];
				if (f.common_diagnosis.value > 0) {
					if (! confirm("Replace the contents of the current template?\nClick 'OK' to proceed.")) {
						return;
					}
				}
				if (!f.form_title.value || !f.form_diagnosis.value) {
					alert("Please enter a title and diagnosis code!!");
					return false;
				}
				top.restoreSession();
				f.form_store.value = 1;
				f.submit();
			}

			 // Check for errors when the form is submitted.
			function validate() {
				var f = document.forms[0];
				if (!f.form_title.value || !f.form_diagnosis.value) {
					alert("<?php echo addslashes(xl('Please enter a title and diagnosis!!')); ?>");
					return false;
				}
				top.restoreSession();
				return true;
			}

			 $(document).ready(function(){

			    $('#common_diagnosis').change(function(){
					notes = $('option:selected',this).attr('notes');
					$('#form_comments').val(notes);
					codes = $('option:selected',this).attr('codes');
					$('#form_diagnosis').val(codes);
					titles = $('option:selected',this).attr('titles');
					$('#form_title').val(titles);
					short = $('option:selected',this).attr('short');
					$('#form_description').val(short);
					$('#form_begin').val('<?php echo date('Y-m-d') ?>');
			    });
<?php if ($msg) { ?>
				alert("<?php echo $msg ?>");
<?php } ?>
			});
		</script>
	</head>

	<body class="body_top" style="margin:auto;width:535px">
		<form method='post' name='theform'  style=""
			 action='edit_issue.php?issue=<?php echo attr($issue); ?>&pid=<?php echo attr($pid); ?>&enc=<?php echo attr($encounter); ?>'
			 onsubmit='return validate()'>
			<input type='hidden' id='form_store' name='form_store' value='' />
			<table style="margin:10px auto;width:100%">
				<tr>
					<td class="wmtLabel" colspan="3" style="font-size:16px;">
						<u>Add/Edit Diagnosis & Plan</u>
					</td>
					<td class='wmtLabel'>
<?php if ($_SESSION['userauthorized']) { ?>
						<a class="css_button" tabindex="-1" onClick="storeme()" href="#"><span>Store Template</span></a>
<!-- input type='button' value='<?php echo xla('Delete'); ?>' style='color:red' onclick='deleteme()' / -->
<?php } ?>
					</td>
				</tr>
				<tr style="height:10px"><td colspan="4"></td></tr>
				<tr>
					<td class="wmtLabel">Common:</td>
					<td class="wmtData" colspan="3">
						<select class="wmtFullInput" id="common_diagnosis">
<?php 
	foreach ($common AS $diag) {
		echo "<option titles=\"".$diag['title']."\" value=\"".$diag['option_id']."\" codes=\"".$diag['codes']."\" notes=\"".htmlspecialchars($diag['notes'])."\" short=\"".htmlspecialchars($diag['long_desc'])."\">";
		if ($diag['title']) echo htmlspecialchars($diag['codes']." - ".$diag['title']);
		echo "</option>\n";
	}

?>					
						</select>
					</td>
				</tr>
				<tr style="height:10px">
					<td></td>
					<td colspan="3" class="wmtTinyLabel" style="padding-left:70px;text-style:italic">Select a common diagnosis above or enter your information below.</td>
				</tr>
				<tr>
					<td class="wmtLabel">Title:</td>
					<td class="wmtData" colspan="3">
						<input class="wmtFullInput" type="text" name="form_title" id="form_title" value="<?php echo $form_data->title ?>" />
					</td>
				</tr>
				<tr style="height:10px"><td colspan="4"></td></tr>
				<tr>
					<td class="wmtLabel" style="width:70px">Diagnosis:</td>
					<td colspan="3">
						<input class="wmtInput" type="text" name="form_diagnosis" id="form_diagnosis" style="width:100px" onclick="sel_diagnosis()" value="<?php echo $form_data->diagnosis ?>"/>
					</td>
				</tr>
				<tr>
					<td class="wmtLabel">Description:</td>
					<td class="wmtData" colspan="3">
						<input class="wmtFullInput" type="text" name="form_description" id="form_description" value="<?php echo $form_data->extrainfo ?>" />
					</td>
				</tr>
				<tr style="height:10px"><td colspan="4"></td></tr>
				<tr>
					<td class="wmtLabel">Begin Date:</td>
					<td class="wmtData" style="white-space:nowrap">
						<!-- input class="wmtInput" type="text" style="width:70px" / -->
						<input class="wmtInput" type='text' size='10' name='form_begin' id='form_begin'
							value='<?php if ($form_data->begdate) echo date('Y-m-d',strtotime($form_data->begdate)) ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
							title='<?php echo xla('yyyy-mm-dd begin date or onset of problem'); ?>' />
						<img src='<?php echo $GLOBALS['webroot'] ?>/interface/pic/show_calendar.gif' align='absbottom' width='24' height='22'
							id='img_begin' border='0' alt='[?]' style='cursor:pointer'
    						title='<?php echo xla('Click here to choose a date'); ?>' />
					</td>
					<td class="wmtLabel" style="width:80px">Occurence:</td>
					<td class="wmtData" style="width:45%">
						<select class="wmtFullInput" name="form_occurrence">
							<?php echo ListSel($form_data->occurrence,'occurrence') ?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="wmtLabel">Referred By:</td>
					<td class="wmtData" colspan="3">
						<input class="wmtFullInput" type="text" name="form_referredby" value="<?php echo $form_data->referredby ?>" />
					</td>
				</tr>
				<tr style="height:10px"><td colspan="4"></td></tr>
				<tr>
					<td class="wmtLabel">End Date:</td>
					<td class="wmtData">
						<!-- input class="wmtInput" type="text"  style="width:70px" /-->
						<input class="wmtInput" type='text' size='10' name='form_end' id='form_end'
							value='<?php if ($form_data->enddate) echo date('Y-m-d',strtotime($form_data->enddate)) ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
							title='<?php echo xla('yyyy-mm-dd end date or date resolved'); ?>' />
						<img src='<?php echo $GLOBALS['webroot'] ?>/interface/pic/show_calendar.gif' align='absbottom' width='24' height='22'
							id='img_end' border='0' alt='[?]' style='cursor:pointer'
    						title='<?php echo xla('Click here to choose a date'); ?>' />
					</td>
					<td class="wmtLabel">Outcome:</td>
					<td class="wmtData">
						<select class="wmtFullInput" name="form_outcome">
							<?php echo ListSel($form_data->outcome,'outcome') ?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="wmtLabel">Destination:</td>
					<td class="wmtData" colspan="3">
						<input class="wmtFullInput" type="text" name="form_destination" value="<?php echo $form_data->destination ?>" />
					</td>
				</tr>
				<tr style="height:10px"><td colspan="4"></td></tr>
				<tr>
					<td class="wmtLabel" colspan="4">
						Plan Of Care:<br/>
						<textarea class="wmtFullInput" rows="7" name="form_comments" id="form_comments" style="resize:none"><?php echo $form_data->comments ?></textarea>
					</td>
				</tr>
			</table>
			<center>
				<?php if ($issue) { ?>
				<input type='submit' name='form_save' value='<?php echo xla('Update'); ?>' />
				&nbsp;
				<input type='button' value='<?php echo xla('Unlink'); ?>' onclick='unlinkme()' />
				<?php } else { ?>
				<input type='submit' name='form_save' value='<?php echo xla('Save'); ?>' />
				<?php } ?>
				<?php if ($issue && \OpenEMR\Common\Acl\AclMain::aclCheckCore('admin', 'super')) { ?>
					&nbsp;
					<input type='button' value='<?php echo xla('Delete'); ?>' style='color:red' onclick='deleteme()' />
				<?php } ?>
					&nbsp;
					<input type='button' value='<?php echo xla('Cancel'); ?>' onclick='closeme();' />
			</center>
		</form>
		
		<script language='JavaScript'>
			 Calendar.setup({inputField:"form_begin", ifFormat:"%Y-%m-%d", button:"img_begin"});
			 Calendar.setup({inputField:"form_end", ifFormat:"%Y-%m-%d", button:"img_end"});
		</script>
	</body>
</html>

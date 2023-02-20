<?php
/** **************************************************************************
 *	WMT/EDIT_IMMUNIZATION.PHP
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
$form_name = 'immunization';
$form_title = 'Add / Edit Immunization';


// grab inportant stuff
$type = $_REQUEST['type'];
$id = $_REQUEST['issue'];
$pid = ($_REQUEST['pid'])? $_REQUEST['pid']: $_SESSION['pid'];
$encounter = ($_REQUEST['enc'])? $_REQUEST['enc']: $_SESSION['encounter'];

$form_data = new wmtImmunization($id); 

// If we are saving, then save and close the window.
if ($_POST['form_save']) {
	$form_admin_date = fixDate($_POST['form_admin_date'], '');
	$form_edu_date   = fixDate($_POST['form_edu_date'], '');
	$form_vis_date   = fixDate($_POST['form_vis_date'], '');
	
	$form_data->patient_id = $pid;
	$form_data->administered_date = $form_admin_date;
	$form_data->immunization_id = formData('form_immun_id');
	$form_data->cvx_code = formData('form_cvx_code');
	$form_data->manufacturer = formData('form_company');
	$form_data->lot_number = formData('form_lot');
	$form_data->administered_by_id = formData('form_admin_by');
	$form_data->administered_by = UserNameFromID($form_data->administered_by_id);
	$form_data->education_date = $form_edu_date;
	$form_data->vis_date = $form_vis_date;
	$form_data->note = formData('form_comments');
	 
	if ($form_data->id) {
		$form_data->update_date = date('Y-m-d H:i:s');
		$form_data->updated_by = $_SESSION['authId'];
		$form_data->update();
	}
	else {
		$form_data->create_date = date('Y-m-d H:i:s');
		$form_data->created_by = $_SESSION['authId'];
		$id = wmtImmunization::insert($form_data);
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


// retrieve manufacturers
$companies = array();
$companies[] = array(''); // blank selection
$query = "SELECT * FROM list_options ";
$query .= "WHERE list_id = 'common_immunization' ORDER BY seq";
$results = sqlStatement($query);
while ($data = sqlFetchArray($results)) $common[] = $data;

?>
<!DOCTYPE HTML>
<html>
	<head>
		<?php //html_header_show();?>
		<title><?php echo $form_title ?> for <?php echo $pat_data->format_name; ?> on <?php echo $form_data->date; ?></title>
		<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
		<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/wmt/wmt.default.css" />

		<?php Header::setupHeader(['dialog', 'jquery', 'jquery-ui', 'datetime-picker']); ?>

		<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/wmt/wmtstandard.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>
		
		<script>

			// Process click on Delete link.
			function deleteme() {
				dlgopen('<?php echo $GLOBALS['webroot'] ?>/interface/patient_file/deleter.php?issue=<?php echo attr($issue) ?>', '_blank', 500, 450);
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

			// Check for errors when the form is submitted.
			function validate() {
				var f = document.forms[0];
				if (!f.form_immun_id.value) {
					alert("<?php echo addslashes(xl('Please enter an immunization!!')); ?>");
					return false;
				}
				top.restoreSession();
				return true;
			}

			$(document).ready(function(){
				$('#form_immun_id').change(function() {
					var cvx = $('option:selected',this).attr('cvx');
					$('#form_cvx_code').val(cvx);
				});
			});
			
		</script>
	</head>

	<body class="body_top" style="width:535px;margin:10px auto">
		<form method='post' name='theform'
			 action='edit_immunization.php?issue=<?php echo attr($id); ?>' onsubmit='return validate()'>

			<table style='width:100%;margin-top:50px'>
				<tr>
					<td class="wmtLabel" colspan="4" style="font-size:16px;">
						<u>Add/Edit Immunization</u>
					</td>
				</tr>
				<tr style="height:20px"><td colspan="4"></td></tr>
				<tr>
					<td class="wmtLabel">Immunization:</td>
					<td class="wmtData" colspan="3">
						<select class="wmtFullInput" id="form_immun_id" name="form_immun_id">
							<option value="">&nbsp;</option>
<?php 
	$result = sqlStatement("SELECT * FROM list_options WHERE list_id = 'Immunizations' ORDER BY seq");
	while ($drug = sqlFetchArray($result)) {
		echo "<option value='".$drug['option_id']."' cvx='".$drug['option_value']."' ";
		if ($form_data->immunization_id == $drug['option_id']) echo "selected ";
		echo ">".$drug['title'];
		echo "</option>\n";
	}
?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="wmtLabel">Manufacturer:</td>
					<td class="wmtData" colspan="3">
						<select class="wmtFullInput" name="form_company">
							<option value="">&nbsp;</option>
<?php 
	$result = sqlStatement("SELECT * FROM list_options WHERE list_id = 'Vaccine_Manufacturers' ORDER BY seq");
	while ($drug = sqlFetchArray($result)) {
		echo "<option value='".$drug['option_id']."' cvx='".$drug['option_value']."' ";
		if ($form_data->manufacturer == $drug['option_id']) echo "selected ";
		echo ">".$drug['title'];
		echo "</option>\n";
	}
?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="wmtLabel">Lot Number:</td>
					<td class="wmtData">
						<input class="wmtInput" name="form_lot" value="<?php echo $form_data->lot_number ?>" style="width:120px" />
					</td>
					<td class="wmtLabel">CVX Code:</td>
					<td class="wmtData">
						<input class="wmtInput" id="form_cvx_code" name="form_cvx_code" readonly style="width:40px" 
							value="<?php echo $form_data->cvx_code ?>" style="width:120px" />
					</td>
				</tr>
				<tr style="height:20px"><td colspan="4"></td></tr>
				<tr>
					<td class="wmtLabel">Date Administered:</td>
					<td class="wmtData" style="white-space:nowrap">
						<input class="wmtInput" type='text' size='10' name='form_admin_date' id='form_admin_date'
							value='<?php echo ($form_data->administered_date)? date('Y-m-d',strtotime($form_data->administered_date)): date('Y-m-d') ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
							title='<?php echo xla('yyyy-mm-dd date immunization given'); ?>' />
						<img src='<?php echo $GLOBALS['webroot'] ?>/interface/pic/show_calendar.gif' align='absbottom' width='24' height='22'
							id='img_admin_date' border='0' alt='[?]' style='cursor:pointer'
    						title='<?php echo xla('Click here to choose a date'); ?>' />
					</td>
					<td class="wmtLabel">Clinician:</td>
					<td class="wmtData">
						<select class="wmtFullInput" name="form_admin_by">
							<option value="">&nbsp;</option>
<?php 
	if (!$form_data->id) $form_data->administered_by = $_SESSION['authId']; // for new records
	$result = sqlStatement("SELECT * FROM users WHERE facility_id != '' ORDER BY lname, fname");
	while ($user = sqlFetchArray($result)) {
		echo "<option value='".$user['id']."' ";
		if ($form_data->administered_by_id == $user['id']) echo "selected ";
		echo ">".$user['lname'].", ".$user['fname'];
		echo "</option>\n";
	}
?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="wmtLabel">Education Date:</td>
					<td class="wmtData" style="white-space:nowrap">
						<input class="wmtInput" type='text' size='10' name='form_edu_date' id='form_edu_date'
							value='<?php echo ($form_data->education_date > 0)? date('Y-m-d',strtotime($form_data->education_date)): "" ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
							title='<?php echo xla('yyyy-mm-dd date vaccine education provided'); ?>' />
						<img src='<?php echo $GLOBALS['webroot'] ?>/interface/pic/show_calendar.gif' align='absbottom' width='24' height='22'
							id='img_edu_date' border='0' alt='[?]' style='cursor:pointer'
    						title='<?php echo xla('Click here to choose a date'); ?>' />
					</td>
					<td class="wmtLabel">VIS Date:</td>
					<td class="wmtData" style="white-space:nowrap">
						<input class="wmtInput" type='text' size='10' name='form_vis_date' id='form_vis_date'
							value='<?php echo ($form_data->vis_date > 0)? date('Y-m-d',strtotime($form_data->vis_date)): "" ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
							title='<?php echo xla('yyyy-mm-dd date vaccine information statement date'); ?>' />
						<img src='<?php echo $GLOBALS['webroot'] ?>/interface/pic/show_calendar.gif' align='absbottom' width='24' height='22'
							id='img_vis_date' border='0' alt='[?]' style='cursor:pointer'
    						title='<?php echo xla('Click here to choose a date'); ?>' />
					</td>
				</tr>
				<tr style="height:20px"><td colspan="4"></td></tr>
				<tr>
					<td class="wmtLabel" colspan="4">
						Additional Information:<br/>
						<textarea class="wmtFullInput" rows="2" name="form_comments" id="form_note" ><?php echo $form_data->note ?></textarea>
					</td>
				</tr>
			</table>
			<br/>
			<center>
				<input type='submit' name='form_save' value='<?php echo xla('Save'); ?>' />
				<?php if ($issue && \OpenEMR\Common\Acl\AclMain::aclCheckCore('admin', 'super')) { ?>
					&nbsp;
					<input type='button' value='<?php echo xla('Delete'); ?>' style='color:red' onclick='deleteme()' />
				<?php } ?>
					&nbsp;
					<input type='button' value='<?php echo xla('Cancel'); ?>' onclick='closeme();' />
			</center>
		</form>
		
		<script language='JavaScript'>
			 Calendar.setup({inputField:"form_admin_date", ifFormat:"%Y-%m-%d", button:"img_admin_date"});
			 Calendar.setup({inputField:"form_edu_date", ifFormat:"%Y-%m-%d", button:"img_edu_date"});
			 Calendar.setup({inputField:"form_vis_date", ifFormat:"%Y-%m-%d", button:"img_vis_date"});
		</script>
	</body>
</html>

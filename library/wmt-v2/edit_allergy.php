<?php
/** **************************************************************************
 *	WMT/EDIT_ALLERGY.PHP
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
$form_name = 'allergy';
$form_title = 'Add / Edit Allergy';


// grab inportant stuff
$type = $_REQUEST['type'];
$id = $_REQUEST['issue'];
$pid = ($_REQUEST['pid'])? $_REQUEST['pid']: $_SESSION['pid'];
$encounter = ($_REQUEST['enc'])? $_REQUEST['enc']: $_SESSION['encounter'];

$form_data = new wmtIssue($id); 

// If we are saving, then save and close the window.
if ($_POST['form_save']) {
	$form_begin = fixDate($_POST['form_begin'], '');
	$form_end   = fixDate($_POST['form_end'], '');

	$form_data->date = date('Y-m-d H:i:s');
	$form_data->type = 'allergy';
	$form_data->title = formData('form_title');
	$form_data->begdate = $form_begin;
	$form_data->enddate = $form_end;
	$form_data->reaction = formData('form_reaction');
	$form_data->activity = 1;
	$form_data->comments = formData('form_comments');
	$form_data->pid = $pid;
	$form_data->user = $_SESSION['authId'];
	 
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


// retrieve common allergies
$common = array();
$common[] = array(); // blank selection
$query = "SELECT * FROM list_options ";
$query .= "WHERE list_id = 'common_allergy' ORDER BY seq";
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
				if (!f.form_title.value) {
					alert("<?php echo addslashes(xl('Please enter a title!!')); ?>");
					return false;
				}
				top.restoreSession();
				return true;
			}

			 $(document).ready(function(){

			    $('#common_allergy').change(function(){
					notes = $('option:selected',this).attr('notes');
					$('#form_comments').val(notes);
					titles = $('option:selected',this).text();
					$('#form_title').val(titles);
					$('#form_begin').val('<?php echo date('Y-m-d') ?>');
			    });
			});
		</script>
	</head>

	<body class="body_top" style="width:535px;margin:10px auto">
		<form method='post' name='theform'
			 action='edit_allergy.php?issue=<?php echo attr($issue); ?>&pid=<?php echo attr($pid); ?>&enc=<?php echo attr($encounter); ?>'
			 onsubmit='return validate()'>

			<table style='width:100%;margin-top:20px'>
				<tr>
					<td class="wmtLabel" colspan="4" style="font-size:16px;">
						<u>Add/Edit Allergy</u>
					</td>
				</tr>
				<tr style="height:10px"><td colspan="4"></td></tr>
				<tr>
					<td class="wmtLabel">Common:</td>
					<td class="wmtData" colspan="3">
						<select class="wmtFullInput" id="common_allergy">
<?php 
	foreach ($common AS $allergy) {
		echo "<option value=\"".$allergy['option_id']."\" notes=\"".$allergy['notes']."\" >".$diag['title']."</option>\n";
	}

?>					
						</select>
					</td>
				</tr>
				<tr style="height:50px">
					<td></td>
					<td colspan="3" class="wmtTinyLabel" style="padding-left:70px;text-style:italic">Select a common allergy above or enter your information below.</td>
				</tr>
				<tr>
					<td class="wmtLabel">Title:</td>
					<td class="wmtData" colspan="3">
						<input class="wmtFullInput" type="text" name="form_title" id="form_title" value="<?php echo $form_data->title ?>" />
					</td>
				</tr>
				<tr>
					<td class="wmtLabel">Reaction:</td>
					<td class="wmtData" colspan="3">
						<input class="wmtFullInput" type="text" name="form_reaction" value="<?php echo $form_data->reaction ?>" />
					</td>
				</tr>
				<tr style="height:20px"><td colspan="4"></td></tr>
				<tr>
					<td class="wmtLabel">Begin Date:</td>
					<td class="wmtData" style="white-space:nowrap">
						<input class="wmtInput" type='text' size='10' name='form_begin' id='form_begin'
							value='<?php if ($form_data->begdate) echo date('Y-m-d',strtotime($form_data->begdate)) ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
							title='<?php echo xla('yyyy-mm-dd begin date or onset of problem'); ?>' />
						<img src='<?php echo $GLOBALS['webroot'] ?>/interface/pic/show_calendar.gif' align='absbottom' width='24' height='22'
							id='img_begin' border='0' alt='[?]' style='cursor:pointer'
    						title='<?php echo xla('Click here to choose a date'); ?>' />
					</td>
					<td class="wmtLabel" style="width:70px">End Date:</td>
					<td class="wmtData">
						<input class="wmtInput" type='text' size='10' name='form_end' id='form_end'
							value='<?php if ($form_data->enddate) echo date('Y-m-d',strtotime($form_data->enddate)) ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
							title='<?php echo xla('yyyy-mm-dd end date or date resolved'); ?>' />
						<img src='<?php echo $GLOBALS['webroot'] ?>/interface/pic/show_calendar.gif' align='absbottom' width='24' height='22'
							id='img_end' border='0' alt='[?]' style='cursor:pointer'
    						title='<?php echo xla('Click here to choose a date'); ?>' />
					</td>
				</tr>
				<tr style="height:20px"><td colspan="4"></td></tr>
				<tr>
					<td class="wmtLabel" colspan="4">
						Additional Information:<br/>
						<textarea class="wmtFullInput" rows="2" name="form_comments" id="form_comments" ><?php echo $form_data->comments ?></textarea>
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
			Calendar.setup({inputField:"form_begin", ifFormat:"%Y-%m-%d", button:"img_begin"});
			Calendar.setup({inputField:"form_end", ifFormat:"%Y-%m-%d", button:"img_end"});
		</script>
	</body>
</html>

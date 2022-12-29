<?php
/** **************************************************************************
 *	internal_note.php
 *
 *	Copyright (c)2019 - Medical Technology Services (MDTechSvcs.com)
 *
 *	This program is free software: you can redistribute it and/or modify it under the 
 *  terms of the GNU General Public License as published by the Free Software Foundation, 
 *  either version 3 of the License, or (at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT ANY
 *	WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 *  PARTICULAR PURPOSE. DISTRIBUTOR IS NOT LIABLE TO USER FOR ANY DAMAGES, INCLUDING 
 *  COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL DAMAGES, 
 *  CONNECTED WITH OR RESULTING FROM THIS AGREEMENT OR USE OF THIS SOFTWARE.
 *
 *	See the GNU General Public License <http://www.gnu.org/licenses/> for more details.
 *
 *  @package wmt
 *  @subpackage messages
 *  @version 1.0.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

// Sanitize escapes
$sanitize_all_escapes = true;

// Stop fake global registration
$fake_register_globals = false;

require_once("../../globals.php");
require_once($GLOBALS['srcdir']."/wmt-v3/wmt.globals.php");

require_once("$srcdir/pnotes.inc");
require_once("$srcdir/gprelations.inc.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/classes/Document.class.php");
require_once("$srcdir/gprelations.inc.php");
require_once("$srcdir/formatting.inc.php");
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmt.msg.inc');
include_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\Core\Header;
use OpenEMR\OemrAd\MessagesLib;
use OpenEMR\OemrAd\OrderLbfForm;

$delete_id = array();
$templates = array();

$use_alerts = globalKeyTest('wmt::use_message_status');

$mode = 'addnew';
$noteid = '';
$rowid = '';
$document_id = '';
$tabmode = false;
if(isset($_REQUEST['mode'])) $mode = strip_tags($_REQUEST['mode']);
if(isset($_REQUEST['noteid'])) $noteid = strip_tags($_REQUEST['noteid']);
if(isset($_REQUEST['rowid'])) $rowid = strip_tags($_REQUEST['rowid']);
if(isset($_REQUEST['document_id'])) $document_id  = strip_tags($_REQUEST['document_id']);
if(isset($_REQUEST['tabmode'])) $tabmode = strip_tags($_REQUEST['tabmode']);

$note = isset($_POST['note']) ? $_POST['note'] : '';
$form_note_type = 
		isset($_POST['form_note_type']) ? $_POST['form_note_type'] : '';
$assigned_to = 
		isset($_POST['assigned_to']) ? $_POST['assigned_to'] : '';
$form_message_status = 
		isset($_POST['form_message_status']) ? $_POST['form_message_status'] : '';
$reply_to = isset($_REQUEST['reply_to']) ? strip_tags($_REQUEST['reply_to']) : '';
if (empty($reply_to) && isset($_REQUEST['pid'])) $reply_to = strip_tags($_REQUEST['pid']);
	$title = isset($_POST['note_type']) ? $_POST['note_type'] : '';
//$assigned_to_list = explode(';',$assigned_to);
switch($mode) {
	case "status_update": {
		//	if ( empty($note) ) {
		//		updatePnoteMessageStatus($noteid, $form_message_status);
		//	} else {
				$newnoteid = '';
				if ($noteid) {
					$newnoteid = $noteid;
					updatePnote($noteid, $note, $form_note_type, $assigned_to, $form_message_status);
					$noteid = '';
				} else {
					error_log('No Note ID - Saving');
					$new_note_id = addPnote($reply_to, $note, $userauthorized, '1', $form_note_type, $assigned_to, '', $form_message_status);
					$noteid = $new_note_id;
					$newnoteid = $noteid;
					error_log("New Note ID ($noteid)");
				}

		    OrderLbfForm::add_internal_note($pid);

		//	}
		break;
	}
	case "save" : {
		//	if ( empty($note) ) {
		//		updatePnoteMessageStatus($noteid, $form_message_status);
		//	} else {
				$newnoteid = '';
				if ($noteid) {
					$newnoteid = $noteid;
					updatePnote($noteid, $note, $form_note_type, $assigned_to, $form_message_status);
					$noteid = '';
				} else {
					error_log('No Note ID - Saving');
					$new_note_id = addPnote($reply_to, $note, $userauthorized, '1', $form_note_type, $assigned_to, '', $form_message_status);
					$noteid = $new_note_id;
					$newnoteid = $noteid;
					error_log("New Note ID ($noteid)");
				}

				OrderLbfForm::add_internal_note($pid);

		//	}
		break;	
	}
	case "edit" : {
		// UPDATE THE MESSAGE IF IT ALREADY EXISTS, IT'S APPENDED TO AN EXISTING NOTE
		$result = getPnoteById($noteid);
		if ($result) {
			if($title == '') $title = $result['title'];
			if($assigned_to == '') $assigned_to = $result['assigned_to'];
			$body = $result['body'];
			if ($reply_to == '') $reply_to = $result['pid'];
			$form_message_status = $result['message_status'];
			if (!empty($result['title'])) $form_note_type = $result['title'];
		}
		break;
	}
	case "delete" : {
		deletePnote($noteid);
		newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "pnotes: id ".$noteid);
		break;
	}
}

if($mode == 'save' || $mode == 'status_update') {
	echo "<html>\n";
	echo "<head>\n";
	echo "<title>Redirecting.....</title>\n";
	Header::setupHeader(['opener', 'dialog']);
	echo "\n<script type='text/javascript'>\n";
	if($tabmode) {
		echo "window.location.assign('../../patient_file/summary/messages_full.php?mode=notes_update');\n";
	} else {
	echo "opener.doRefresh();\n";
	echo "dlgclose();\n";
	}
	echo "</script>\n";
	echo "</head>\n";
	echo "</html>\n";
}
?>

<html>
<head>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<?php Header::setupHeader(['opener', 'dialog', 'common', 'textformat', 'jquery', 'jquery-ui', 'bootstrap', 'oemr_ad']); ?>

<?php if($use_alerts) { ?>
<script type="text/javascript" src="../../../library/wmt-v2/wmt.msg.js"></script>
<?php } ?>

<script type="text/javascript" src="../../../library/wmt-v2/wmtpopup.js"></script>
<script type="text/javascript" src="../../../library/wmt-v2/wmtextras.js"></script>
<script type="text/javascript">
var basePath = '<?php echo $rootdir; ?>';
var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

function goParentPid(pid) {
	opener.document.location.href = '../../patient_file/summary/demographics.php?set_pid=' + pid;
}

function CheckTemplate(t) {
	var chc = t.selectedIndex;
	var title = t.options[chc].value;
	if(title == '<?php echo $templates[1]; ?>') {
  	var text = document.getElementById('note').value;
		if(text == '' || !text) {
			document.getElementById('note').value = "FBS\tPC\tAC\tPC\tAC\tHS\r";
		}
	}
	return true;
}

/* OEMRAD - Changes */
$(document).ready(function(){
	$('.usersSelectList').on("change", function (e) {
		var select_val = $(this).val();
		isGroupUserExists(select_val);				
	});

	$('#assigned_to').on("change", function (e) {
		var select_val = $(this).val();
		isGroupUserExists(select_val);				
	});
});
/* End */

</script>
</head>

<body class="body_top">
<?php
if($mode == 'addnew' || $mode == 'edit') {
?>
<!-- div class="title" style="display: inline; float: left;">
<?php // echo htmlspecialchars( xl('Messages'), ENT_NOQUOTES); ?>
</div -->
<form name="new_note" id="new_note" action="internal_note.php" method="post">
<input type="hidden" name="noteid" id="noteid" value="<?php echo htmlspecialchars( $noteid, ENT_QUOTES); ?>" />
<input type="hidden" name="rowid" id="rowid" value="<?php echo htmlspecialchars( $rowid, ENT_QUOTES); ?>" />
<input type="hidden" name="tabmode" id="tabmode" value="<?php echo htmlspecialchars( $tabmode, ENT_QUOTES); ?>" />
<input type="hidden" name="document_id" id="document_id" value="<?php echo attr( $document_id); ?>" />
<input type="hidden" name="mode" id="mode" value="addnew" />
<?php if($use_alerts) include_once($GLOBALS['srcdir'].'/wmt-v2/wmt.msg.php'); ?>
<div id="pnotes" style="max-width: 800px;">
	<div>
		<div class="form-row">
			<div class="form-group col-sm-6">
		    <label><?php echo xlt('Type'); ?></label>
		    <select name="form_note_type" id="form_note_type" class="form-control" onchange="CheckTemplate(this);">
		    	<?php
						$ures=sqlStatement("Select option_id, title, codes FROM list_options ".
								"WHERE list_id='note_type' ORDER BY seq");
						echo "		<option value=''";
				  	if ($form_note_type == '') echo " selected";
						echo "> - Choose A Type - </option>";
						while($urow=sqlFetchArray($ures)) {
				      echo "    <option value='" . htmlspecialchars( $urow['option_id'], ENT_QUOTES) . "'";
				  		if ($urow['option_id'] == $form_note_type) echo " selected";
				  		if ($urow['codes'] == 1) echo ' style="color: red;"';
				  		echo '>'. htmlspecialchars( $urow['title'], ENT_NOQUOTES);
				  		if ($urow['codes'] == 1) echo ' (Seen in the Portal)';
				  		echo "</option>\n";
						}
				  ?>
		    </select>
		  </div>

		  <div class="form-group col-sm-6">
		    <label><?php echo xlt('Status'); ?></label>
		    <select name='assigned_to' id='assigned_to' class="form-control">
					<?php 
					  echo "<option value='" . text('--') . "'";
					  echo ">" . text( 'Select User/Group' );
					  echo "</option>\n";

						MsgUserGroupSelect('', true, $use_alerts, false, $ustat);
					/*
					  echo "<option value='" . htmlspecialchars( '-patient-', ENT_QUOTES) . "'";
					  if ($assigned_to == '-patient-') echo " selected";
					  echo ">" . htmlspecialchars( '-Patient-', ENT_NOQUOTES);
					  echo "</option>\n";
					*/
					?>
	   		</select>
		  </div>
		</div>

		<div class="form-row">
		  <div class="form-group col-sm-6">
		    <label>
		    	<?php if ($mode != "addnew") { ?>
			     	<?php echo xlt('Patient'); ?>:&nbsp;
			   	<?php } else { ?>
			     	<b class="<?php echo ($mode == 'addnew' ? 'required' : '') ?>" style=''><?php echo xlt('Patient'); ?>:&nbsp;</b>
			   	<?php } ?>
		    </label>
		    <?php
			 	if ($reply_to) {
			  	$prow = sqlQuery("SELECT lname, fname FROM patient_data WHERE pid = ?", array($reply_to) );
			  	$patientname = $prow['lname'] . ", " . $prow['fname'];
			 	} else $patientname = '';
			 	if($patientname == '') $patientname = xl('Click to select');
			 	?>
			  	<input type='text' size='10' name='form_patient' class="form-control" style='<?php echo $mode == "addnew" ? "cursor:pointer;cursor:hand;" : ""; ?>' value='<?php echo attr($patientname); ?>' <?php echo $mode == "addnew" ? "onclick='sel_patient()' readonly" : "disabled"; ?> title='<?php echo $mode == "addnew" ? xlt('Click to select patient') : ""; ?>'  />
			  	<input type='hidden' name='reply_to' id='reply_to' value='<?php echo attr($reply_to) ?>' />
		  </div>

		  <div class="form-group col-sm-6">
		    <label><?php echo xlt('To'); ?></label>
		    <?php
			  if ($form_message_status == "") {
			      $form_message_status = 'New';
			  }
	    	generate_form_field(array('data_type'=>1,'field_id'=>'message_status','list_id'=>'message_status','empty_title'=>'SKIP','order_by'=>'title'), $form_message_status); ?>
		  </div>
		</div>

		<div class="form-group">
	    <?php
	    if ($noteid) {
  			$body = preg_replace('/(:\d{2}\s\()'.$result['pid'].'(\sto\s)/','${1}'.$patientname.'${2}',$body);
  			$body = nl2br( $body );
  			//echo "<div style='background-color:white; color: gray; border:1px solid #999; padding: 5px; width: 640px;'>".$body."</div>";
  			echo "<div class='form-control disabled mb-2' style='height:auto !important;'>".$body."</div>";
			}
			?>
 			<textarea name='note' id='note' rows='8' class="form-control"><?php echo text( $note ); ?></textarea>
	  </div>

	  <div class="form-group">
	  	<input type="button" id="newnote" class="btn btn-primary" value="<?php echo xla('Send message'); ?>">
    	<?php if ($noteid) { ?>
    	<input type="button" id="printnote" class="btn btn-primary" value="<?php echo xla('Print message'); ?>">
    	<?php } ?>
    	<input type="button" id="cancel" class="btn btn-secondary" value="<?php echo xla('Cancel'); ?>">
	  </div>
	</div>
</div>
</form>
</body>

<script type="text/javascript">

$(document).ready(function(){
  $("#newnote").click(function() { SaveNote(); });
  $("#printnote").click(function() { PrintNote(); });
	// obj = document.getElementById("form_message_status");
	// obj.onchange = function() { SaveNote(); };
  $("#cancel").click(function() { CancelNote(); });
  $("#note").focus();

  var SaveNote = function () {
    if (document.forms[0].reply_to.value.length == 0) {
    	alert('Please choose a patient');
    } else if (document.forms[0].assigned_to.value == '--' && 
					document.getElementById("form_message_status").value != 'Done') {
       alert('Recipient required unless status is Done');
    } else {
			if(document.getElementById("form_message_status").value != 'Done') {
      	$("#mode").val("save");
			} else {
      	$("#mode").val("status_update");
			}
			var tmp = document.getElementById('mode').value;
      $("#new_note").submit();
    }
  }

  var PrintNote = function () {
    window.open('../../patient_file/summary/pnotes_print.php?noteid=<?php echo htmlspecialchars( $noteid, ENT_QUOTES); ?>', '_blank', 'resizable=1,scrollbars=1,width=600,height=500');
  }

  var CancelNote = function () {
    $("#mode").val("status_update");
    $("#new_note").submit();
  }
});

function setpatient(pid, lname, fname, dob) {
	var f = document.forms[0];
	f.form_patient.value = lname + ', ' + fname;
	f.reply_to.value = pid;
}

 // This invokes the find-patient popup.
function sel_patient() {
	dlgopen('../../main/calendar/find_patient_popup.php', '_blank', 500, 400);
}
 
function addtolist(sel){
	var itemtext = document.getElementById('assigned_to_text');
	var item = document.getElementById('assigned_to');
	if(sel.value != '--') {
		if(item.value) {
			if(item.value.indexOf(sel.value) == -1) {
				<?php if($use_alerts) {
				echo "alertMsgStatus(sel, sel.value);";
				} ?>
				itemtext.value = itemtext.value +" ;\n"+ sel.options[sel.selectedIndex].text;
				item.value = item.value +';'+ sel.value;
			} else {
				// Second click on an item will remove it
				var opt_text = sel.options[sel.selectedIndex].text;
				if(itemtext.value.indexOf(opt_text+' ; ') != -1) {
					itemtext.value = itemtext.value.replace(opt_text+' ; ','');
				} else if(itemtext.value.indexOf(' ; '+opt_text) != -1) {
					itemtext.value = itemtext.value.replace(' ; '+opt_text,'');
				} else if(itemtext.value.indexOf(opt_text) != -1) {
					itemtext.value = itemtext.value.replace(opt_text,'');
				}
				
				if(item.value.indexOf(sel.value+';') != -1) {
					item.value = item.value.replace(sel.value+';','');
				} else if(item.value.indexOf(';'+sel.value) != -1) {
					item.value = item.value.replace(';'+sel.value,'');
				} else if(item.value.indexOf(sel.value) != -1) {
					item.value = item.value.replace(sel.value,'');
				}
			}
		} else {
			<?php if($use_alerts) {
			echo "alertMsgStatus(sel, sel.value);";
			} ?>
			itemtext.value = sel.options[sel.selectedIndex].text;
			item.value = sel.value;
		}
	}
	sel.selectedIndex = 0;
}
 
</script>
<?php } ?>

</html>

<?php
/**
 *
 * Patient disclosures main screen.
 *
 * Copyright (C) Visolve <vicareplus_engg@visolve.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Visolve <vicareplus_engg@visolve.com>
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../../globals.php");
require_once("$srcdir/sql.inc");
require_once("$srcdir/log.inc");

//retrieve the user name
$res = sqlQuery("select username from users where username=?", array($_SESSION{"authUser"}) );
$uname=$res{"username"};
//if the mode variable is set to disclosure, retrieve the values from 'disclosure_form ' in record_disclosure.php to store it in database.
if (isset($_POST["mode"]) and  $_POST["mode"] == "disclosure"){
	$dates=trim($_POST['dates']);
	$event=trim($_POST['form_disclosure_type']);
	$recipient_name=trim($_POST['recipient_name']);
	$disclosure_desc=trim($_POST['desc_disc']);
	$disclosure_id=trim($_POST['disclosure_id']);
	if (isset($_POST["updatemode"]) AND $_POST["updatemode"] == "disclosure_update")
	{
		//update the recorded disclosure in the extended_log table.
		updateRecordedDisclosure($dates,$event,$recipient_name,$disclosure_desc,$disclosure_id);
	}
	else
	{
		//insert the disclosure records in the extended_log table.
		 recordDisclosure($dates,$event,$pid,$recipient_name,$disclosure_desc,$uname);
	}
}
if (isset($_GET['deletelid']))
{
$deletelid=$_GET['deletelid'];
//function to delete the recorded disclosures  
deleteDisclosure($deletelid);
}
?>
<html>
<head>
<link rel='stylesheet' href="<?php echo $css_header;?>" type="text/css">
<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>
<link rel="stylesheet" type="text/css" href="../../../library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="../../../library/js/common.js"></script>
<script type="text/javascript" src="../../../library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
</head>
<body class="body_top">
<div>
	<span class="title"><?php echo xls('Disclosures'); ?></span>
</div>
<div style='float: left; margin-right: 10px'><?php echo xls('for'); ?>&nbsp;
	<span class="title"><a href="../summary/demographics.php" onclick="top.restoreSession()"><?php $pname = getPatientName($pid); echo text($pname); ?></a></span>
</div>
<div>
	<a href="record_disclosure.php" class="css_button iframe" onclick="top.restoreSession()"><span><?php echo xls('Record'); ?></span></a>
</div>
<div>
	<a href="demographics.php" <?php if (!$GLOBALS['concurrent_layout']) echo "target='Main'"; ?>
	class="css_button" onclick="top.restoreSession()"> <span><?php echo xls('View Patient') ?></span></a>
</div>
<br>
<br>
<?php
$N=15;
$offset = $_REQUEST['offset'];
if (!isset($offset)) $offset = 0;

$disclQry = " SELECT el.id, el.event, el.recipient, el.description, el.date, CONCAT(u.fname, ' ', u.lname) as user_fullname FROM extended_log el ".
		   " LEFT JOIN users u ON u.username = el.user ".
		   " WHERE el.patient_id=? AND el.event IN (SELECT option_id FROM list_options WHERE list_id='disclosure_type') ORDER BY el.date DESC ";
$r2= sqlStatement($disclQry, array($pid) );
$totalRecords=sqlNumRows($r2);

//echo "select id,event,recipient,description,date from extended_log where patient_id=$pid AND event in (select option_id from list_options where list_id='disclosure_type') order by date desc limit $offset ,$N";
//display all of the disclosures for the day, as well as others that are active from previous dates, up to a certain number, $N
$disclInnerQry = " SELECT el.id, el.event, el.recipient, el.description, el.date, CONCAT(u.fname, ' ', u.lname) as user_fullname FROM extended_log el ".
				" LEFT JOIN users u ON u.username = el.user ".
				" WHERE patient_id=? AND event IN (SELECT option_id FROM list_options WHERE list_id='disclosure_type') ORDER BY date DESC LIMIT $offset,$N";

$r1= sqlStatement($disclInnerQry, array($pid) );
$n=sqlNumRows($r1);
$noOfRecordsLeft=($totalRecords - $offset);
if ($n>0){?>
	<table border='0' class="text">
		<tr>
		<td colspan='5' style="padding: 5px;"><a href="disclosure_full.php" class="" id='Submit' onclick="top.restoreSession()"><span><?php echo xls('Refresh'); ?></span></a></td>
		</tr>
	</table>
<div id='pnotes'>	
	<table border='0' cellpadding="1" width='80%'>
		<tr class="showborder_head" align='left' height="22">
			<th style='width: 120px';>&nbsp;</th>
			<th style="border-style: 1px solid #000" width="140px"><?php echo xls('Recipient Name'); ?></th>
			<th style="border-style: 1px solid #000" width="140px"><?php echo xls('Disclosure Type'); ?></th>
			<th style="border-style: 1px solid #000"><?php echo xls('Description'); ?></th>
			<th style="border-style: 1px solid #000"><?php echo xls('Provider'); ?></th>
		</tr>
	<?php
	$result2 = array();
	for ($iter = 0;$frow = sqlFetchArray($r1);$iter++)
		$result2[$iter] = $frow;
	foreach($result2 as $iter)
	{
		$app_event=$iter{event};
		$event=split("-",$app_event);
		$description =nl2br(text($iter{description})); //for line break if there is any new lines in the input text area field.
		?>
		<!-- List the recipient name, description, date and edit and delete options-->
		<tr  class="noterow" height='25'>		
			<!--buttons for edit and delete.-->
			<td valign='top'><a href='record_disclosure.php?editlid=<?php echo text($iter{id}); ?>'
			class='css_button_small iframe' onclick='top.restoreSession()'><span><?php echo xls('Edit');?></span></a>
			<a href='#' class='deletenote css_button_small'
			id='<?php echo text($iter{id}); ?>' onclick='top.restoreSession()'><span><?php echo xls('Delete');?></span></a></td>
			<td class="text" valign='top'><?php echo text($iter{recipient});?>&nbsp;</td>
			<td class='text' valign='top'><?php if($event[1]=='healthcareoperations'){ echo xls('health care operations'); } else echo text($event[1]); ?>&nbsp;</td>
			<td class='text'><?php echo text($iter{date})." ".$description;?>&nbsp;</td>
			<td class='text'><?php echo text($iter{user_fullname});?></td>
		</tr>
		<?php
	}
}
else
{?>
	<br>
	<!-- Display None, if there is no disclosure -->
	<span class='text' colspan='3'><?php echo xls('None');?></span>
	<?php
}
?>
</table>
<table width='400' border='0' cellpadding='0' cellspacing='0'>
 <tr>
  <td>
<?php 
if ($offset > ($N-1) && $n!=0) {
  echo "   <a class='link' href='disclosure_full.php?active=" . $active .
    "&offset=" . ($offset-$N) . "' onclick='top.restoreSession()'>[" .
    xls('Previous') . "]</a>\n";
}
?>
  
<?php 

if ($n >= $N && $noOfRecordsLeft!=$N) {
  echo "&nbsp;&nbsp;   <a class='link' href='disclosure_full.php?active=" . $active. 
    "&offset=" . ($offset+$N)  ."&leftrecords=".$noOfRecordsLeft."' onclick='top.restoreSession()'>[" .
    xls('Next') . "]</a>\n";
}
?>
  </td>
 </tr>
</table>
</div>
</body>

<script type="text/javascript">
$(document).ready(function()
        {
/// todo, move this to a common library  
	//for row highlight.	
	 $(".noterow").mouseover(function() { $(this).toggleClass("highlight"); });
	 $(".noterow").mouseout(function() { $(this).toggleClass("highlight"); });
	 //fancy box  
    	enable_modals();
    	//for deleting the disclosures
    	$(".deletenote").click(function() { DeleteNote(this); });
	
      	var DeleteNote = function(logevent) 
		{
		if (confirm("<?php echo htmlspecialchars(xl('Are you sure you want to delete this disclosure?','','','\n ') . xl('This action CANNOT be undone.'),ENT_QUOTES); ?>")) 
			{
	                top.restoreSession();
                        window.location.replace("disclosure_full.php?deletelid="+logevent.id)                         
         		}
       		}	
       });
</script>
</html>



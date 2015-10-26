<?php
/**
 * delete tool, for logging and removing patient data.
 *
 * Called from many different pages.
 *
 *  Copyright (C) 2015 Sherwin Gaddis <sherwingaddis@gmail.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * @package OpenEMR
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @link    http://www.openmedpractice.com
 */

require_once('../globals.php');
require_once($GLOBALS['srcdir'].'/log.inc');
require_once($GLOBALS['srcdir'].'/acl.inc');
require_once($GLOBALS['srcdir'].'/sl_eob.inc.php');

$row         = $_REQUEST['row'];
$pid         = $_REQUEST['pid'];
$encounter   = $_REQUEST['encounter'];


     function delete_diag($row, $pid, $encounter) {
	
		 sqlStatement("UPDATE care_plan SET diag_$row = ''," . 
								   " risk_$row = ''," .
								   " assessment_$row = ''," . 
								   " goal_$row = '' WHERE pid = $pid AND encounter = $encounter");

        }
//If the delete is confirmed
if($_POST['form_submit']){

    $row = $_REQUEST['row'];
	$pid = $_REQUEST['pid'];
	$encounter = $_REQUEST['encounter'];
	
	//collect information about to be deleted
	$logstring = "care_plan";
	
	//log information deleted
	newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "$logstring");
	
	//delete specified row
	delete_diag($row, $pid, $encounter);
	
    //echo "Ready to delete! " . $row;
	
  if (! $info_msg) $info_msg = xl('Delete successful.');

  // Close this window and tell our opener that it's done.
  //
  echo "<script language='JavaScript'>\n";
  if ($info_msg) echo " alert('$info_msg');\n";
  if ($encounterid) //this code need to be same as 'parent.imdeleted($encounterid)' when the popup is div like
  {
    echo "window.opener.imdeleted($encounterid);\n";
  }
  else
  {
    echo " if (opener && opener.imdeleted) opener.imdeleted(); else parent.imdeleted();\n";
  }
  echo " window.close();\n";
  //function to refresh the parent window
  echo "window.onunload = function(){
        window.opener.location.reload();
      };";
  echo "</script></body></html>\n";
    exit;		
	}	
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Delete Patient, Encounter, Form, Issue, Document, Payment, Billing or Transaction','e'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style>

</style>

<script language="javascript">
function submit_form()
{
document.deletefrm.submit();
}
// Java script function for closing the popup
function popup_close() {
	if(parent.$==undefined) {
	  	window.close();
	 }
	 else {
	  	parent.$.fn.fancybox.close(); 
	 }	  
}
</script>
</head>

<body class="body_top">
<?php 
  // If the Delete is confirmed...
  //
  $post = filter_input(INPUT_POST,'form_submit');
  if (!empty($post)){
  
  
  
      echo "Going to delete now";
	  echo " if (opener && opener.imdeleted) opener.imdeleted(); else parent.imdeleted();\n";
	  echo "<script language='JavaScript'>\n";
	  echo " window.close();\n";
      echo "</script></body></html>\n";
	  exit;
  }
?>




<form method='post' name="deletefrm" action='careplan_deleter.php?row=<?php echo $row; ?>&pid=<?php echo $pid; ?>&encounter=<?php echo $encounter; ?>' onsubmit="javascript:alert('1');document.deleform.submit();" >

<p class="text">&nbsp;<br><?php xl('Do you really want to delete','e'); ?>

<?php
 if ($row) {
  echo xl('Diagnosis') . " " . $row . " " .$pid . " " . $encounter ;
 } 
?> <?php xl('and all subordinate data? <br>This action will be logged','e'); ?>!</p>

<center>

<p class="text">&nbsp;<br>
<a href="#" onclick="submit_form()" class="css_button"><span><?php xl('Yes, Delete and Log','e'); ?></span></a>
<input type='hidden' name='form_submit' value=<?php xl($row); ?>/>
<a href='#' class="css_button" onclick=popup_close();><span><?php echo xl('No, Cancel');?></span></a>
</p>


</body>
</html>
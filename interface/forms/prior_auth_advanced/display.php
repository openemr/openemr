<?php

// Copyright (C) 2016 by following authors:
//  Sherwin Gaddis <sherwingaddis@gmail.com>
//  
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

require_once("../../globals.php");
require_once("$srcdir/forms.inc");

global $pid;

$sql = "SELECT a.id, a.date, a.prior_auth_number, a.comments, a.desc, a.code1, a.code2, a.code3, a.code4, a.code5, a.code6, a.code7, b.id, b.encounter 
        FROM forms AS b LEFT JOIN form_prior_auth AS a ON a.id = b.form_id AND b.formdir LIKE 'prior_auth'  
		WHERE a.pid = ". $pid . " GROUP BY b.form_id";
$res = sqlStatement($sql);

?>
<html>
<title></title>
<head>
<?php html_header_show();?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/ajtooltip.js"></script>

<style>
.display p {
	text-align: right;
}
.display{
	text-align: right;
	position: relative;
	float: left;
}

</style>
<script language="JavaScript">

//function toencounter(enc, datestr) {
function toencounter(rawdata) {
    var parts = rawdata.split("~");
    var enc = parts[0];
    var datestr = parts[1];

    top.restoreSession();
<?php if ($GLOBALS['concurrent_layout']) { ?>
    parent.left_nav.setEncounter(datestr, enc, window.name);
    parent.left_nav.setRadio(window.name, 'enc');
    parent.left_nav.loadFrame('enc2', window.name, 'patient_file/encounter/encounter_top.php?set_encounter=' + enc);
<?php } else { ?>
    top.Title.location.href = '../encounter/encounter_title.php?set_encounter='   + enc;
    top.Main.location.href  = '../encounter/patient_encounter.php?set_encounter=' + enc;
<?php } ?>
}


	 // Helper function to set the contents of a div.
    function setDivContent(id, content) {
    $("#"+id).html(content);
   }
   
function pop(stuff){
	var things = stuff;
	alert("working" + things);
}
</script>
</head>

<body>
<h1>Prior Auth Info</h1>

<?php while($display = sqlFetchArray($res)){ ?>
	<div class="display">
		Date: <br>
		Prior Auth#:  <br>
		Date From:  To: <br>
		CPT's: <br>
		Desc: <br>
		Encounter:  <br>
	</div>
	<div class="info">
		<?php echo $display['date']; ?><br>
		<?php echo $display['prior_auth_number']; ?><br>
		<?php echo $display['auth_from']." &nbsp;&nbsp;&nbsp; ".$display['auth_to']; ?><br>
		<?php echo $display['code1']. " " . $display['code2']. " " .$display['code3']. " ". $display['code4']. " "
					   . $display['code5']. " ". $display['code6']. " ". $display['code7']; ?><br>
		<?php echo $display['desc']; ?><br>
		<?php echo $display['encounter']; ?><br>
	</div>
</br>
<?php if(getSupervisor($authUser) == "Supervisor"){ ?>
<a href="#" id="edit"  onclick="toencounter('<?php 
        $d = explode(" ", $display['date']);
    echo $display['encounter']."~". $d[0]?>')" ><button>Edit</button></a>
<?php } ?>

<hr>
<?php } ?>
</body>

<script language="javascript">
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".encrow").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".encrow").mouseout(function() { $(this).toggleClass("highlight"); });
    $(".encrow").click(function() { toencounter(this.id); }); 

});

</script>
</html>
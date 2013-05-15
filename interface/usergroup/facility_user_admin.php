<?php
/**
 * edit per-facility user information.
 *
 * Copyright (C) 2012 NP Clinics <info@npclinics.com.au>
 *
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 *
 * A copy of the GNU General Public License is included along with this program:
 * openemr/interface/login/GnuGPL.html
 * For more information write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * @package OpenEMR
 * @Author  Scott Wakefield <scott@npclinics.com.au>
 * @link    http://open-emr.org
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;


//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;


require_once("../globals.php");
require_once("$srcdir/sql.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/acl.inc");

// Ensure authorized
if (!acl_check('admin', 'users')) {
  die(xlt("Unauthorized"));
}

// Ensure variables exist
if (!isset($_GET["user_id"]) || !isset($_GET["fac_id"])) {
  die(xlt("Error"));
}

?>

<html>
<head>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css" href="../../library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="../../library/js/common.js"></script>
<script type="text/javascript" src="../../library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.easydrag.handler.beta2.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="JavaScript">

function submitform() {
	top.restoreSession();
	var flag=0;
	function trimAll(sString)
	{
		while (sString.substring(0,1) == ' ')
		{
			sString = sString.substring(1, sString.length);
		}
		while (sString.substring(sString.length-1, sString.length) == ' ')
		{
			sString = sString.substring(0,sString.length-1);
		}
		return sString;
	}
	if(flag == 0){
		document.forms[0].submit();
		parent.$.fn.fancybox.close(); 
	}
	
	
	
}

$(document).ready(function(){
    $("#cancel").click(function() {
		  parent.$.fn.fancybox.close();
	 });

});

</script>

</head>
<body class="body_top" style="width:450px;height:200px !important;">

<?php
// Collect user information
$user_info = sqlQuery("select * from `users` WHERE `id` = ?", array($_GET["user_id"]) );

// Collect facility information
$fac_info = sqlQuery("select * from `facility` where `id` = ?", array($_GET["fac_id"]) );

// Collect layout information and store them in an array
$l_res = sqlStatement("SELECT * FROM layout_options " .
                      "WHERE form_id = 'FACUSR' AND uor > 0 AND field_id != '' " .
                      "ORDER BY group_name, seq");
$l_arr = array();
for($i=0; $row=sqlFetchArray($l_res); $i++) {
  $l_arr[$i]=$row;
}
?>

<table>
    <tr>
        <td>
        <span class="title"><?php echo xlt('Edit Facility Specific User Information'); ?></span>&nbsp;&nbsp;&nbsp;</td><td>
        <a class="css_button large_button" name='form_save' id='form_save' onclick='submitform()' href='#' >
            <span class='css_button_span large_button_span'><?php echo xlt('Save');?></span>
        </a>
        <a class="css_button large_button" id='cancel' href='#'>
            <span class='css_button_span large_button_span'><?php echo xlt('Cancel');?></span>
        </a>
     </td>
  </tr>
</table>

<form name='medicare' method='post' action="facility_user.php" target="_parent">
    <input type=hidden name=mode value="facility_user_id">
    <input type=hidden name=user_id value="<?php echo attr($_GET["user_id"]);?>">
    <input type=hidden name=fac_id value="<?php echo attr($_GET["fac_id"]);?>">
    <?php $iter = sqlQuery("select * from facility_user_ids where id=?", array($my_id)); ?>

<table border=0 cellpadding=0 cellspacing=0>
<tr>
	<td>
		<span class=text><?php echo xlt('User'); ?>: </span>
	</td>
	<td>
		<span class=text><?php echo text($user_info['username']); ?> </span>
	</td>
</tr>

<tr>
	<td>
		<span class=text><?php echo xlt('Facility'); ?>: </span>
	</td>
	<td>
		<span class=text><?php echo text($fac_info['name']); ?> </span>
	</td>
</tr>

<?php foreach ($l_arr as $layout_entry) { ?>
  <tr>
	<td style="width:180px;">
		<span class=text><?php echo text(xl_layout_label($layout_entry['title'])) ?>: </span>
	</td>
	<td style="width:270px;">
                <?php
                $entry_data = sqlQuery("SELECT `field_value` FROM `facility_user_ids` " .
                                       "WHERE `uid` = ? AND `facility_id` = ? AND `field_id` = ?", array($user_info['id'],$fac_info['id'],$layout_entry['field_id']) );
                echo "<td><span class='text'>" . generate_form_field($layout_entry,$entry_data['field_value']) . "&nbsp;</td>";
                ?> 
	</td>
  </tr>
<?php } ?>

</table>
</form>

<!-- include support for the list-add selectbox feature -->
<?php include $GLOBALS['fileroot'] . "/library/options_listadd.inc"; ?>

<script language="JavaScript">
<?php echo $date_init; ?>
</script>

</body>
</html>


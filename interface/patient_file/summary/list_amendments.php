<?php
/**
 * List Amendments
 *
 * Copyright (C) 2014 Ensoftek
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Hema Bandaru <hemab@drcloudemr.com>
 * @link    http://www.open-emr.org
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

include_once("../../globals.php");
include_once("$srcdir/sql.inc");
include_once("$srcdir/options.inc.php");

?>

<html>
<head>
<?php html_header_show();?>

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>


<!-- page styles -->
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<style>
.highlight {
  color: green;
}
tr.selected {
  background-color: white;
}	
</style>
		
<!-- pop up calendar -->
<style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);</style>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>
<script type="text/javascript">
	function checkForAmendments() {
		var amendments = "";
		$("#list_amendments input:checkbox:checked").each(function() {
				amendments += $(this).val() + ",";
		});
		
		if ( amendments == '' ) {
			alert("<?php echo xls('Select amendments to print'); ?>");
			return;
		}
		
		// Call the function to print
		var url = "print_amendments.php?ids=" + amendments;
		window.open(url);
	}
	
	function checkUncheck(option) {
		$("input[name='check_list[]']").each( function () {
			var optionFlag = ( option ) ? true : false;
			$(this).attr('checked',optionFlag);
		});
	}
</script>
</head>

<body class="body_top">

<form action="list_amendments.php" name="list_amendments" id="list_amendments" method="post" onsubmit='return top.restoreSession()'>

<span class="title"><?php echo xlt('List'); ?></span>&nbsp;
<?php 
	$query = "SELECT * FROM amendments WHERE pid = ? ORDER BY amendment_date DESC";
	$resultSet = sqlStatement($query,array($pid));
	if ( sqlNumRows($resultSet)) { ?>
			<table cellspacing="0" cellpadding="0" style="width:100%">
				<tr>
					<td><a href="javascript:checkForAmendments();" class="css_button"><span><?php echo xlt("Print Amendments"); ?></span></a></td>
					<td align="right">
						<a href="#" class="small" onClick="checkUncheck(1);"><span><?php echo xlt('Check All');?></span></a> |
						<a href="#" class="small" onClick="checkUncheck(0);"><span><?php echo xlt('Clear All');?></span></a>
					</td>
				</tr>
			</table>
		<div id="patient_stats">
			<br>
		<table border=0 cellpadding=0 cellspacing=0 style="margin-bottom:1em;">

		<tr class='head'>
			<th style="width:5%"></th>
			<th style="width:15%" align="left"><?php echo  xlt('Requested Date'); ?></th>
			<th style="width:40%" align="left"><?php echo  xlt('Request Description'); ?></th>
			<th style="width:25%" align="left"><?php echo  xlt('Requested By'); ?></th>
			<th style="width:15%" align="left"><?php echo  xlt('Request Status'); ?></th>
		</tr>
	
		<?php while($row = sqlFetchArray($resultSet)) {
			$amendmentLink = "<a href=add_edit_amendments.php?id=" . attr($row['amendment_id']) . ">" . oeFormatShortDate($row['amendment_date']) . "</a>";
		?>
			<tr class="amendmentrow" id="<?php echo attr($row['amendment_id']); ?>">
				<td><input id="check_list[]" name="check_list[]" type="checkbox" value="<?php echo attr($row['amendment_id']); ?>"></td>
				<td class=text><?php echo $amendmentLink; ?> </td>
				<td class=text><?php echo text($row['amendment_desc']); ?> </td>
				<td class=text><?php echo generate_display_field(array('data_type'=>'1','list_id'=>'amendment_from'), $row['amendment_by']); ?> </td>
				<td class=text><?php echo generate_display_field(array('data_type'=>'1','list_id'=>'amendment_status'), $row['amendment_status']); ?> </td>
			</tr>
		<?php } ?>
		</table>
		</div>
	<?php } else { ?>
		<span style="color:red">
			<br>
			<?php echo xlt("No amendment requests available"); ?>
		</span>
	<?php } ?>
</form>
</body>

</html>
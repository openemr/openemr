<?php
// Copyright (C) 2009 Aron Racho <aron@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
//------------Forms generated from formsWiz
include_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");
function snellen_report( $pid, $encounter, $cols, $id) {
  $count = 0;
  $cols = 2;
  $data = formFetch("form_snellen", $id);
  $width = 100/$cols;
  if ($data) {
	?>

	<table class='text' border='0px' cellpadding='2px' cellspacing='0px'>
		<tr>
			<td width='80px'><b>&nbsp;</td>
			<td width='80px'><b>Without Correction</b></td>
			<td width='80px'><b>With Correction</b></td>
		</tr>
		<tr>
			<td>(L) Eye</td>
			<td>20/<?php echo $data['left_1'] ? $data['left_1'] : "__"; ?></td>
			<td>20/<?php echo $data['left_2'] ? $data['left_2'] : "__"; ?></td>
		</tr>	
		<tr>
			<td>(R) Eye</td>
			<td>20/<?php echo $data['right_1'] ? $data['right_1'] : "__"; ?></td>
			<td>20/<?php echo $data['right_2'] ? $data['right_2'] : "__"; ?></td>
		</tr>	

	</table>

	<?php if ($data['notes'] != '') {?>
	</p>
	<table border='0' cellpadding='0' cellspacing='0' class='text'>
		<tr class='text'>
			<td><b>NOTES</b></td>
		</tr>
		<tr class='text'>
			<td><p align='left'><?php echo $data['notes']?>&nbsp;</p></td>
		</tr>
	</table>
	<?php } ?>

	<?php

  }
}
?>

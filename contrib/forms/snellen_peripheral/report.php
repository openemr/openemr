<?php
// Copyright (C) 2009 Aron Racho <aron@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
//------------Forms generated from formsWiz
include_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");
function snellen_peripheral_report( $pid, $encounter, $cols, $id) {
  $count = 0;
  $cols = 2;
  $data = formFetch("form_snellen_peripheral", $id);
  $width = 100/$cols;
  if ($data) {
	?>

	<table class='text' border='0px' cellpadding='5px' cellspacing='2px'>
		<tr>
			<td width='80px'><b>&nbsp;</td>
			<td width='80px'><b>Without Correction</b></td>
			<td width='80px'><b>With Correction</b></td>
		</tr>
		<tr>
			<td>Left Eye</td>
			<td>20/<?php echo $data['left_1'] ? $data['left_1'] : "__"; ?></td>
			<td>20/<?php echo $data['left_2'] ? $data['left_2'] : "__"; ?></td>
		</tr>	
		<tr>
			<td>Right Eye</td>
			<td>20/<?php echo $data['right_1'] ? $data['right_1'] : "__"; ?></td>
			<td>20/<?php echo $data['right_2'] ? $data['right_2'] : "__"; ?></td>
		</tr>	
		<tr>
			<td>Both Eyes</td>
			<td>20/<?php echo $data['both_1'] ? $data['both_1'] : "__"; ?></td>
			<td>20/<?php echo $data['both_2'] ? $data['both_2'] : "__"; ?></td>
		</tr>	
		<tr>
			<td>Peripheral(L)</td>
			<td><?php echo $data['peripheral_l1'] ? $data['peripheral_l1'] : "__"; ?>&deg;</td>
			<td><?php echo $data['peripheral_l2'] ? $data['peripheral_l2'] : "__"; ?>&deg;</td>
		</tr>
		<tr>
			<td>Peripheral(R)</td>
			<td><?php echo $data['peripheral_r1'] ? $data['peripheral_r1'] : "__"; ?>&deg;</td>
			<td><?php echo $data['peripheral_r2'] ? $data['peripheral_r2'] : "__"; ?>&deg;</td>
		</tr>		
	</table>
	<table border='0' cellpadding='0' cellspacing='0' class='text'>
		<tr>
			<br><td><b>Able to distinguish between: Red, Green, Amber colors:</b> <?php echo $data['colors']?></td>
		</tr>
	</table>
	<table border='0' cellpadding='0' cellspacing='0' class='text'>
		<tr>
			<td><b>Monocular Vision:</b> <?php echo $data['monocular']?></td>
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

<?php
// Copyright (C) 2009 Aron Racho <aron@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
//------------Forms generated from formsWiz
include_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");
function leg_length_report( $pid, $encounter, $cols, $id) {
  $count = 0;
  $data = formFetch("form_leg_length", $id);
  $width = 100/$cols;
  if ($data) {
  ?>


<table border="1" bordercolor="#000000" cellpadding="7" cellspacing="0" class="text">
		<col>
		<col>
		<col>
		<tbody><tr valign="top">
			<td>
				<palign="justify">&nbsp;</p>

			</td>
			<td>
				<p<b>RIGHT</b></p>
			</td>
			<td>
				<p<b>LEFT</b></p>
			</td>
		</tr>

		<tr valign="top">
			<td>
				<p<b>AE</b></p>
			</td>
			<td>
				<p><?php echo $data["AE_left"]?>&nbsp;</p>
			</td>
			<td>
				<p><?php echo $data["AE_right"]?>&nbsp;</p>
			</td>
		</tr>
		<tr valign="top">
			<td>
				<p<b>BE</b></p>
			</td>
			<td>

				<p><?php echo $data["BE_left"]?>&nbsp;</p>
			</td>
			<td>
				<p><?php echo $data["BE_right"]?>&nbsp;</p>
			</td>
		</tr>
		<tr valign="top">
			<td width="40" height="3">

				<p<b>AK</b></p>
			</td>
			<td>
				<p><?php echo $data["AK_left"]?>&nbsp;</p>
			</td>
			<td>
				<p><?php echo $data["AK_right"]?>&nbsp;</p>

			</td>
		</tr>
		<tr valign="top">
			<td>
				<p<b>K</b></p>
			</td>
			<td>
				<p><?php echo $data["K_left"]?>&nbsp;</p>

			</td>
			<td>
				<p><?php echo $data["K_right"]?>&nbsp;</p>
			</td>
		</tr>
		<tr valign="top">
			<td>
				<p<b>BK</b></p>

			</td>
			<td>
				<p><?php echo $data["BK_left"]?>&nbsp;</p>
			</td>
			<td>
				<p><?php echo $data["BK_right"]?>&nbsp;</p>
			</td>
		</tr>

		<tr valign="top">
			<td>
				<p<b>ASIS</b></p>
			</td>
			<td>
				<p><?php echo $data["ASIS_left"]?>&nbsp;</p>
			</td>
			<td>

				<p><?php echo $data["ASIS_right"]?>&nbsp;</p>
			</td>
		</tr>
		<tr valign="top">
			<td width="40">
				<p<b>UMB</b></p>
			</td>
			<td>

				<p><?php echo $data["UMB_left"]?>&nbsp;</p>
			</td>
			<td>
				<p><?php echo $data["UMB_right"]?>&nbsp;</p>
			</td>
		</tr>
	</tbody>
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

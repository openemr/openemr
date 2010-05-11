<?php
// Copyright (C) 2009 Aron Racho <aron@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
//------------Forms generated from formsWiz
include_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");
function complaint_history_report( $pid, $encounter, $cols, $id) {
  $count = 0;
  $cols = 2;
  $data = formFetch("form_complaint_history", $id);
  if ($data) {
	?>

	<table class='text' border='0px' cellpadding='2px' cellspacing='0px'>
		<tr>
			<td><?php echo $data['complaint_history'] ? $data['complaint_history'] : "&nbsp;"; ?></td>
		</tr>	
	</table>
	<?php
  }
}
?>

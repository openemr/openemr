<?php
/*******************************************************************************\
 * Copyright (C) 2014 Joe Slam (joe@produnis.de)                                *
 *                                                                              *
 * This program is free software; you can redistribute it and/or                *
 * modify it under the terms of the GNU General Public License                  *
 * as published by the Free Software Foundation; either version 2               *
 * of the License, or (at your option) any later version.                       *
 *                                                                              *
 * This program is distributed in the hope that it will be useful,              *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of               *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                *
 * GNU General Public License for more details.                                 *
 *                                                                              *
 * You should have received a copy of the GNU General Public License            *
 * along with this program; if not,                                             *
 * see <http://opensource.org/licenses/gpl-license.php>                         *
 ********************************************************************************
 * @package OpenEMR
 * @author Joe Slam <joe@produnis.de>
 * @link http://www.open-emr.org
 * 
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../../globals.php");

?>
<div id='labdata' style='margin-top: 3px; margin-left: 10px; margin-right: 10px'><!--outer div-->
<br>
<?php
//retrieve tracks.
$spell = "SELECT form_name, MAX(form_track_anything_results.track_timestamp) as maxdate, form_id " .
			"FROM forms " . 
			"JOIN form_track_anything_results ON forms.form_id = form_track_anything_results.track_anything_id " . 
			"WHERE forms.pid = ? " . 
			"AND formdir = ? " .
			"GROUP BY form_name " .
			"ORDER BY maxdate DESC ";
$result = sqlQuery($spell, array($pid, 'track_anything'));
if ( !$result ) //If there are no disclosures recorded
{ ?>
  <span class='text'> <?php echo htmlspecialchars(xl("No tracks have been documented."),ENT_NOQUOTES); 
?>
  </span> 
<?php 
} else {  // We have some tracks here...
	echo "<span class='text'>";
	echo xlt('Available Tracks') . ":";
	echo $result;
	echo "<ul>";
	$result=sqlStatement($spell, array($pid, 'track_anything') );
	while($myrow = sqlFetchArray($result)){
		$formname = $myrow['form_name'];
		$thedate = $myrow['maxdate'];
		$formid = $myrow['form_id'];
		echo "<li><a href='../../forms/track_anything/history.php?formid=" . attr($formid) . "'>" . text($formname) . "</a></li> (" . text($thedate) . ")</li>";
	}
	echo "</ul>";
	echo "</span>";
} ?>
<br />
<br />
</div>

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
 * see <http://opensource.org/licenses/gpl-license.php>                          *
 ********************************************************************************
 * @package OpenEMR
 * @author Joe Slam <joe@produnis.de>
 * @link http://www.open-emr.org
 * */

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
//retrieve most recent set of labdata.
$spell = "SELECT procedure_report.date_collected AS thedate, " . 
			      "procedure_order_code.procedure_name AS theprocedure, " .
				  "procedure_order.encounter_id AS theencounter " . 
			"FROM procedure_report " . 
			"JOIN procedure_order ON  procedure_report.procedure_order_id = procedure_order.procedure_order_id " . 
			"JOIN procedure_order_code ON procedure_order.procedure_order_id = procedure_order_code.procedure_order_id " . 
			"WHERE procedure_order.patient_id = ? " . 
			"ORDER BY procedure_report.date_collected DESC ";
$result=sqlQuery($spell, array($pid) );
    
if ( !$result ) //If there are no lab data recorded
{ ?>
  <span class='text'> <?php echo htmlspecialchars(xl("No lab data documented."),ENT_NOQUOTES); 
?>
  </span> 
<?php 
} else
{
?> 
  <span class='text'><b>
  <?php echo htmlspecialchars(xl('Most recent lab data:'),ENT_NOQUOTES); ?>
  </b>
  <br />
  <?php 
  	echo xlt('Procedure') . ": " . text($result['theprocedure']) . " (" . text($result['thedate']) . ")<br>";
  	echo xlt('Encounter') . ": <a href='../../patient_file/encounter/encounter_top.php?set_encounter=" . attr($result['theencounter']) . "' target='RBot'>" . text($result['theencounter']) . "</a>";
  ?>
  <br />
  </span><span class='text'>
  <br />
  <a href='../summary/labdata.php' onclick='top.restoreSession()'><?php echo htmlspecialchars(xl('Click here to view and graph all labdata.'),ENT_NOQUOTES);?></a>
  </span><?php
} ?>
<br />
<br />
</div>

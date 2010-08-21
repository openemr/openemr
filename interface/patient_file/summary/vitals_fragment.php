<?php
/*******************************************************************************\
 * Copyright (C) Brady Miller (brady@sparmy.com)                                *
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
 * along with this program; if not, write to the Free Software                  *
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.  *
 ********************************************************************************/

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../../globals.php");

?>
<div id='vitals' style='margin-top: 3px; margin-left: 10px; margin-right: 10px'><!--outer div-->
<br>
<?php
//retrieve most recent set of vitals.
$result=sqlQuery("SELECT date, id FROM form_vitals WHERE pid=? ORDER BY date DESC", array($pid) );
    
if ( !$result ) //If there are no disclosures recorded
{ ?>
  <span class='text'> <?php echo htmlspecialchars(xl("No vitals have been documented."),ENT_NOQUOTES); 
?>
  </span> 
<?php 
} else
{
?> 
  <span class='text'><b>
  <?php echo htmlspecialchars(xl('Most recent vitals from:')." ".$result['date'],ENT_NOQUOTES); ?>
  </b></span>
  <br />
  <br />
  <?php include_once($GLOBALS['incdir'] . "/forms/vitals/report.php");
  call_user_func("vitals_report", '', '', 2, $result['id']);
  ?>  <span class='text'>
  <br />
  <a href='../encounter/trend_form.php?formname=vitals' onclick='top.restoreSession()'><?php echo htmlspecialchars(xl('Click here to view and graph all vitals.'),ENT_NOQUOTES);?></a>
  </span><?php
} ?>
<br />
<br />
</div>

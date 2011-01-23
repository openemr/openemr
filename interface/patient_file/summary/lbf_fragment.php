<?php
/*******************************************************************************\
 * Copyright 2010 Brady Miller <brady@sparmy.com>                               *
 * Copyright 2011 Rod Roark <rod@sunsetsystems.com>                             *
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
$sanitize_all_escapes = true;

//STOP FAKE REGISTER GLOBALS
$fake_register_globals = false;

require_once("../../globals.php");

$lbf_form_id = $_GET['formname'];
?>
<div id='<?php echo $lbf_form_id; ?>' style='margin-top: 3px; margin-left: 10px; margin-right: 10px'>
<br />
<?php
// Retrieve most recent instance of this form for this patient.
$result = sqlQuery("SELECT date, form_id, form_name FROM forms WHERE pid = ? " .
  "AND formdir = ? AND deleted = 0 ORDER BY date DESC LIMIT 1",
  array($pid, $lbf_form_id));
    
if (!$result) { //If there are none
?>
  <span class='text'> <?php echo htmlspecialchars(xl("None have been documented"), ENT_NOQUOTES); ?>
  </span> 
<?php } else { ?> 
  <span class='text'><b>
<?php
  echo htmlspecialchars(xl('Most recent from') . ": " .
    substr($result['date'], 0, 10), ENT_NOQUOTES);
?>
  </b></span>
  <br />
  <br />
<?php
  include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");
  call_user_func("lbf_report", '', '', 2, $result['form_id'], $lbf_form_id);
?>
  <span class='text'>
  <br />
  <a href='../encounter/trend_form.php?formname=<?php echo $lbf_form_id; ?>'
   onclick='top.restoreSession()'>
   <?php echo htmlspecialchars(xl('Click here to view and graph'),ENT_NOQUOTES);?>
  </a>
  </span>
<?php } ?>
<br />
<br />
</div>

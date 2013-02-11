<?php
/**
 * Report to view the Direct Message log.
 *
 * Copyright (C) 2013 Brady Miller <brady@sparmy.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../globals.php");
?>

<html>

<head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<title><?php echo xlt('Direct Message Log'); ?></title>

<script type="text/javascript" src="../../library/js/jquery-1.7.2.min.js"></script>

<style type="text/css">

/* specifically include & exclude from printing */
@media print {
    #report_parameters {
        visibility: hidden;
        display: none;
    }
    #report_parameters_daterange {
        visibility: visible;
        display: inline;
    }
    #report_results table {
       margin-top: 0px;
    }
}

/* specifically exclude some from the screen */
@media screen {
    #report_parameters_daterange {
        visibility: hidden;
        display: none;
    }
}

</style>
</head>

<body class="body_top">

<span class='title'><?php echo xlt('Direct Message Log'); ?></span>

<form method='post' name='theform' id='theform' action='direct_message_log.php' onsubmit='return top.restoreSession()'>

<div id="report_parameters">
<table>
 <tr>
  <td width='470px'>
	<div style='float:left'>

	<table class='text'>
             <div style='margin-left:15px'>
               <a id='refresh_button' href='#' class='css_button' onclick='top.restoreSession(); $("#theform").submit()'>
               <span>
               <?php echo xlt('Refresh'); ?>
               </span>
               </a>
             </div>
        </table>
  </td>
 </tr>
</table>
</div>  <!-- end of search parameters -->

<br>



<div id="report_results">
<table>

 <thead>

  <th align='center'>
   <?php echo xlt('ID'); ?>
  </th>

  <th align='center'>
   <?php echo xlt('Type'); ?>
  </th>

  <th align='center'>
   <?php echo xlt('Date Created'); ?>
  </th>

  <th align='center'>
   <?php echo xlt('Sender'); ?>
  </th>

  <th align='center'>
   <?php echo xlt('Recipient'); ?>
  </th>

  <th align='center'>
   <?php echo xlt('Status'); ?>
  </th>

  <th align='center'>
   <?php echo xlt('Date of Status Change'); ?>
  </th>

 </thead>
 <tbody>  <!-- added for better print-ability -->
<?php

 $res = sqlStatement("SELECT * FROM `direct_message_log` ORDER BY `create_ts` DESC");
 while ($row = sqlFetchArray($res)) {
?>
 <tr>
      <td align='center'><?php echo text($row['id']); ?></td>

      <?php if ($row['msg_type'] == "R") { ?>
          <td align='center'><?php echo xlt("Received") ?></td>
      <?php } else if ($row['msg_type'] == "S") { ?>
          <td align='center'><?php echo xlt("Sent") ?></td>
      <?php } else {?>
          <td align='center'>&nbsp;</td>
      <?php } ?>

      <td align='center'><?php echo text($row['create_ts']); ?></td>
      <td align='center'><?php echo text($row['sender']); ?></td>
      <td align='center'><?php echo text($row['recipient']); ?></td>

      <?php if ($row['status'] == "Q") { ?>
          <td align='center'><?php echo xlt("Queued") ?></td>
      <?php } else if ($row['status'] == "D") { ?>
          <td align='center'><?php echo xlt("Dispatched") ?></td>
      <?php } else if ($row['status'] == "R") { ?>
          <td align='center'><?php echo xlt("Received") ?></td>
      <?php } else if ($row['status'] == "F") { ?>
          <td align='center'><?php echo xlt("Failed") ?></td>
      <?php } else {?>
          <td align='center'>&nbsp;</td>
      <?php } ?>

      <td align='center'><?php echo text($row['status_ts']); ?></td>

 </tr>
<?php
 } // $row = sqlFetchArray($res) while
?>
</tbody>
</table>
</div>  <!-- end of search results -->

</form>

</body>
</html>


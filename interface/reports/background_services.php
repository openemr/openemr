<?php
/**
 * Report to view the background services.
 *
 * Copyright (C) 2013 Brady Miller <brady.g.miller@gmail.com>
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
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */


use OpenEMR\Core\Header;
require_once("../globals.php");
?>

<html>

<head>

<?php Header::setupHeader(); ?>

<title><?php echo xlt('Background Services'); ?></title>

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

<span class='title'><?php echo xlt('Background Services'); ?></span>

<form method='post' name='theform' id='theform' action='background_services.php' onsubmit='return top.restoreSession()'>

<div id="report_parameters">
    <table>
        <tr>
            <td width='470px'>
	              <div class="btn-group pull-left" role="group">
                    <a id='refresh_button' href='#' class='btn btn-default btn-refresh' onclick='top.restoreSession(); $("#theform").submit()'>
                        <?php echo xlt('Refresh'); ?>
                    </a>
                </div>
            </td>
        </tr>
    </table>
</div>  <!-- end of search parameters -->

<br>



<div id="report_results">
<table>

 <thead>
  <th align='center'>
   <?php echo xlt('Service Name'); ?>
  </th>

  <th align='center'>
   <?php echo xlt('Active'); ?>
  </th>

  <th align='center'>
   <?php echo xlt('Automatic'); ?>
  </th>

  <th align='center'>
   <?php echo xlt('Interval (minutes)'); ?>
  </th>

  <th align='center'>
   <?php echo xlt('Currently Busy'); ?>
  </th>

  <th align='center'>
   <?php echo xlt('Last Run Started At'); ?>
  </th>

  <th align='center'>
   <?php echo xlt('Next Scheduled Run'); ?>
  </th>

  <th align='center'>
   &nbsp;
  </th>

 </thead>
 <tbody>  <!-- added for better print-ability -->
<?php

 $res = sqlStatement("SELECT *, (`next_run` - INTERVAL `execute_interval` MINUTE) as `last_run_start`" .
	" FROM `background_services` ORDER BY `sort_order`");
 while ($row = sqlFetchArray($res)) {
?>
 <tr>
      <td align='center'><?php echo xlt($row['title']); ?></td>

      <td align='center'><?php echo ($row['active']) ? xlt("Yes") : xlt("No"); ?></td>

      <?php if ($row['active']) { ?>
          <td align='center'><?php echo ($row['execute_interval'] > 0) ? xlt("Yes") : xlt("No"); ?></td>
      <?php } else { ?>
          <td align='center'><?php echo xlt('Not Applicable'); ?></td>
      <?php } ?>

      <?php if ($row['active'] && ($row['execute_interval'] > 0)) { ?>
          <td align='center'><?php echo text($row['execute_interval']); ?></td>
      <?php } else { ?>
          <td align='center'><?php echo xlt('Not Applicable'); ?></td>
      <?php } ?>

          <td align='center'><?php echo ($row['running']>0) ? xlt("Yes") : xlt("No"); ?></td>

      <?php if ( $row['running'] > -1) { ?>
          <td align='center'><?php echo text($row['last_run_start']); ?></td>
      <?php } else { ?>
          <td align='center'><?php echo xlt('Never'); ?></td>
      <?php } ?>

      <?php if ( $row['active'] && ($row['execute_interval'] > 0) ) { ?>
          <td align='center'><?php echo text($row['next_run']); ?></td>
      <?php } else { ?>
          <td align='center'><?php echo xlt('Not Applicable'); ?></td>
      <?php } ?>

      <?php if ($row['name'] == "phimail") { ?>
         <td align='center'><a href='direct_message_log.php' onclick='top.restoreSession()'><?php echo xlt("View Log"); ?></a></td>
      <?php } else { ?>
         <td align='center'>&nbsp;</td>
      <?php } ?>

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


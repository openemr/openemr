<?php

/**
 * Report to view the background services.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2013-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Core\Header;
?>

<html>

<head>

<?php Header::setupHeader(); ?>

<title><?php echo xlt('Background Services'); ?></title>

<style>

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
                  <div class="btn-group float-left" role="group">
                    <a id='refresh_button' href='#' class='btn btn-secondary btn-refresh' onclick='top.restoreSession(); $("#theform").submit()'>
                        <?php echo xlt('Refresh'); ?>
                    </a>
                </div>
            </td>
        </tr>
    </table>
</div>  <!-- end of search parameters -->

<br />



<div id="report_results">
<table class='table'>

 <thead class='thead-light'>
  <th align='center'>
    <?php echo xlt('Service Name'); ?>
  </th>

  <th align='center'>
    <?php echo xlt('Active{{Service}}'); ?>
  </th>

  <th align='center'>
    <?php echo xlt('Automatic'); ?>
  </th>

  <th align='center'>
    <?php echo xlt('Interval (minutes)'); ?>
  </th>

  <th align='center'>
    <?php echo xlt('Currently Busy{{Service}}'); ?>
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

          <td align='center'><?php echo ($row['running'] > 0) ? xlt("Yes") : xlt("No"); ?></td>

        <?php if ($row['running'] > -1) { ?>
          <td align='center'><?php echo text(oeFormatDateTime($row['last_run_start'], "global", true)); ?></td>
        <?php } else { ?>
          <td align='center'><?php echo xlt('Never'); ?></td>
        <?php } ?>

        <?php if ($row['active'] && ($row['execute_interval'] > 0)) { ?>
          <td align='center'><?php echo text(oeFormatDateTime($row['next_run'], "global", true)); ?></td>
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


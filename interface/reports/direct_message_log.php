<?php
/**
 * Report to view the Direct Message log.
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
<?php
$logstart = (isset($_POST['logstart'])) ? $_POST['logstart'] : 0;
if (isset($_POST['lognext']) && $_POST['lognext']) {
    $logtop = $logstart + $_POST['lognext'];
} else {
    $logtop = 0;
}
?>

<?php Header::setupHeader(); ?>

<title><?php echo xlt('Direct Message Log'); ?></title>

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
<input type='hidden' name='lognext' id='lognext' value=''>

<div id="report_parameters">
    <table>
        <tr>
            <td width='470px'>
                <div class="btn-group pull-left" role="group">
                    <a id='refresh_button' href='#' class='btn btn-default btn-refresh' onclick='top.restoreSession(); $("#theform").submit()'>
                        <?php echo xlt('Refresh'); ?>
                    </a>
                    <a id='prev_button' href='#' class='btn btn-default btn-transmit' onclick='top.restoreSession(); $("#lognext").val(-100); $("#theform").submit()'>
                        <?php echo xlt('Older'); ?>
                    </a>
                    <a id='next_button' href='#' class='btn btn-default btn-transmit' onclick='top.restoreSession(); $("#lognext").val(100); $("#theform").submit()'>
                        <?php echo xlt('Newer'); ?>
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

if (!$logtop) {
    $res = sqlStatement("SELECT * FROM `direct_message_log` ORDER BY `id` DESC LIMIT 100");
} else {
    $res = sqlStatement(
        "SELECT * FROM `direct_message_log` WHERE `id` BETWEEN ? AND ? ORDER BY `id` DESC",
        array($logtop-99,$logtop)
    );
}

 $logstart = 0;
while ($row = sqlFetchArray($res)) {
    if (!$logstart) {
        $logstart = $row['id'];
    }
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

    <td align='center'><?php echo text(oeFormatDateTime($row['create_ts'], "global", true)); ?></td>
    <td align='center'><?php echo text($row['sender']); ?></td>
    <td align='center'><?php echo text($row['recipient']); ?></td>

    <?php if ($row['status'] == "Q") { ?>
          <td align='center'><?php echo xlt("Queued") ?></td>
    <?php } else if ($row['status'] == "S") { ?>
          <td align='center'><?php echo xlt("Sent") ?></td>
    <?php } else if ($row['status'] == "D") { ?>
          <td align='center'><?php echo xlt("Sent - Confirmed") ?></td>
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

<input type='hidden' name='logstart' id='logstart' value='<?php echo text($logstart); ?>'>
</form>

</body>
</html>


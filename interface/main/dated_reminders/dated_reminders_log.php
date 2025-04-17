<?php

/**
 * Used for adding dated reminders.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Craig Bezuidenhout <http://www.tajemo.co.za/>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012 tajemo.co.za <http://www.tajemo.co.za/>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 */

    require_once("../../globals.php");
    require_once("$srcdir/dated_reminder_functions.php");

    use OpenEMR\Common\Acl\AclMain;
    use OpenEMR\Common\Csrf\CsrfUtils;
    use OpenEMR\Core\Header;

    $isAdmin = AclMain::aclCheckCore('admin', 'users');
?>
<?php
  /*
    -------------------  HANDLE POST ---------------------
  */
if ($_GET) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    if (!$isAdmin) {
        if (empty($_GET['sentBy']) and empty($_GET['sentTo'])) {
            $_GET['sentTo'] = array(intval($_SESSION['authUserID']));
        }
    }

    echo '  <div class="col-12">
            <h4>' . xlt('Click and drag bottom right corner to resize this display') . '</h4>
            <table class="table table-bordered"  id="logTable">
                <thead>
                  <tr>
                    <th>' . xlt('ID') . '</th>
                    <th>' . xlt('Sent Date') . '</th>
                    <th>' . xlt('From') . '</th>
                    <th>' . xlt('To{{Destination}}') . '</th>
                    <th>' . xlt('Patient') . '</th>
                    <th>' . xlt('Message') . '</th>
                    <th>' . xlt('Due Date') . '</th>
                    <th>' . xlt('Processed Date') . '</th>
                    <th>' . xlt('Processed By') . '</th>
                  </tr>
                </thead>
                <tbody>';
    $remindersArray = array();
    $TempRemindersArray = logRemindersArray();
    foreach ($TempRemindersArray as $RA) {
        $remindersArray[$RA['messageID']]['messageID'] = $RA['messageID'];
        $remindersArray[$RA['messageID']]['ToName'] = ((!empty($remindersArray[$RA['messageID']]['ToName'])) ? $remindersArray[$RA['messageID']]['ToName'] . ', ' . ($RA['ToName'] ?? '') : ($RA['ToName'] ?? ''));
        $remindersArray[$RA['messageID']]['PatientName'] = $RA['PatientName'];
        $remindersArray[$RA['messageID']]['message'] = $RA['message'];
        $remindersArray[$RA['messageID']]['dDate'] = $RA['dDate'];
        $remindersArray[$RA['messageID']]['sDate'] = $RA['sDate'];
        $remindersArray[$RA['messageID']]['pDate'] = $RA['pDate'];
        $remindersArray[$RA['messageID']]['processedByName'] = $RA['processedByName'];
        $remindersArray[$RA['messageID']]['fromName'] = $RA['fromName'];
    }

    foreach ($remindersArray as $RA) {
        echo '<tr class="heading">
              <td>' . text($RA['messageID']) . '</td>
              <td>' . text(oeFormatDateTime($RA['sDate'])) . '</td>
              <td>' . text($RA['fromName']) . '</td>
              <td>' . text($RA['ToName']) . '</td>
              <td>' . text($RA['PatientName']) . '</td>
              <td>' . text($RA['message']) . '</td>
              <td>' . text(oeFormatShortDate($RA['dDate'])) . '</td>
              <td>' . text(oeFormatDateTime($RA['pDate'])) . '</td>
              <td>' . text($RA['processedByName']) . '</td>
            </tr>';
    }

    echo '</tbody></table></div>';

    die;
}
?>
<html>
  <head>
    <?php Header::setupHeader(['datetime-picker']); ?>

    <script>
      $(function () {
        $("#submitForm").click(function(){
          // top.restoreSession(); --> can't use this as it negates this ajax refresh
          $.get("dated_reminders_log.php?"+$("#logForm").serialize(),
               function(data) {
                  $("#resultsDiv").html(data);
                    <?php
                    if (!$isAdmin) {
                        echo '$("select option").removeAttr("selected");';
                    }
                    ?>
                    return false;
               }
             )
          return false;
        });

        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = true; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
      })
    </script>
</head>
<body>
    <div class="container">
    <!-- Required for the popup date selectors -->
        <div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
        <?php
        $allUsers = array();
        $uSQL = sqlStatement('SELECT id, fname,	mname, lname  FROM  `users` WHERE  `active` = 1 AND `facility_id` > 0 AND id != ?', array(intval($_SESSION['authUserID'])));
        for ($i = 0; $uRow = sqlFetchArray($uSQL); $i++) {
            $allUsers[] = $uRow;
        }
        ?>
        <div class="row">
            <div class="col-12">
                <h2><?php echo xlt('Dated Message Log');?> &nbsp;<i id="show_hide" class="fa fa-eye-slash fa-2x small" data-toggle="tooltip" data-placement="top" title="<?php echo xla('Click to Hide Filters'); ?>"></i></h2>
            </div>
            <div class="col-12 hideaway">
                <form method="get" id="logForm" onsubmit="return top.restoreSession()">
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <fieldset>
                        <legend><?php echo xlt('Filters') ?></legend>
                        <h5><?php echo xlt('Date The Message Was Sent');?></h5>
                        <div class="form-group row">
                            <div class="col-12 col-md-6">
                                <label class="col-form-label" for="sd"><?php echo xlt('Start Date') ?>:</label>
                                <input id="sd" type="text" class='form-control datepicker' name="sd" value="" title='<?php echo attr(DateFormatRead('validateJS')) ?>'>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="col-form-label" for="ed"><?php echo xlt('End Date') ?>:</label>
                                <input id="ed" type="text" class='form-control datepicker' name="ed" value="" title='<?php echo attr(DateFormatRead('validateJS')) ?>'>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-12 col-md-6">
                                <label class="col-form-label" for="sentBy"><?php echo xlt('Sent By, Leave Blank For All');?>:</label>
                                <select class="form-control" id="sentBy" name="sentBy[]" multiple="multiple">
                                    <option value="<?php echo attr(intval($_SESSION['authUserID'])); ?>"><?php echo xlt('Myself') ?></option>
                                    <?php
                                    if ($isAdmin) {
                                        foreach ($allUsers as $user) {
                                            echo '<option value="' . attr($user['id']) . '">' . text($user['fname'] . ' ' . $user['mname'] . ' ' . $user['lname']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="col-form-label" for="sentBy"><?php echo xlt('Sent To, Leave Blank For All') ?>:</label>
                                <select class="form-control" id="sentTo" name="sentTo[]" multiple="multiple">
                                    <option value="<?php echo attr(intval($_SESSION['authUserID'])); ?>"><?php echo xlt('Myself') ?></option>
                                    <?php
                                    if ($isAdmin) {
                                        foreach ($allUsers as $user) {
                                            echo '<option value="' . attr($user['id']) . '">' . text($user['fname'] . ' ' . $user['mname'] . ' ' . $user['lname']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="processed" id="processed"><?php echo xlt('Processed') ?>
                                </label>
                                <label>
                                    <input type="checkbox" name="pending" id="pending"><?php echo xlt('Pending') ?>
                                </label>
                            </div>
                        </div>
                    </fieldset>
                    <div class="form-group row">
                        <div class="col-sm-12 position-override">
                            <div class="btn-group form-group" role="group">
                                <button type="button" value="Refresh" id="submitForm" class="btn btn-secondary btn-refresh" ><?php echo xlt('Refresh') ?></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-12">
                <div id="resultsDiv"></div>
            </div>
        </div>
    </div><!--end of container div-->
    <script>
       $('#show_hide').click(function() {
            var elementTitle = $('#show_hide').prop('title');
            var hideTitle = '<?php echo xla('Click to Hide Filters'); ?>';
            var showTitle = '<?php echo xla('Click to Show Filters'); ?>';
            $('.hideaway').toggle('1000');
            $(this).toggleClass('fa-eye-slash fa-eye');
            if (elementTitle == hideTitle) {
                elementTitle = showTitle;
            } else if (elementTitle == showTitle) {
                elementTitle = hideTitle;
            }
            $('#show_hide').prop('title', elementTitle);
        });
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</body>
</html>

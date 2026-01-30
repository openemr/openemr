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
            $_GET['sentTo'] = [intval($_SESSION['authUserID'])];
        }
    }

    $remindersArray = [];
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

    echo '<div class="row">
            <div class="col-12 results-section mb-3">';

    if (empty($remindersArray)) {
        echo '<div class="alert alert-info text-center mt-3 mb-3">' . xlt('No Messages Found') . '</div>';
    } else {
        echo '<div class="card mt-3 mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">' . xlt('Message Results') . '</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="logTable">
                            <thead class="thead-light">
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

        foreach ($remindersArray as $RA) {
            echo '<tr>
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

        echo '</tbody>
            </table>
            </div>
        </div>
        </div>';
    }

    echo '</div>
        </div>';

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
        $allUsers = [];
        $uSQL = sqlStatement('SELECT id, fname, mname, lname FROM `users` WHERE `active` = 1 AND `facility_id` > 0 AND id != ?', [intval($_SESSION['authUserID'])]);
        for ($i = 0; $uRow = sqlFetchArray($uSQL); $i++) {
            $allUsers[] = $uRow;
        }
        ?>
        <div class="row">
            <div class="col-12 mb-2">
                <h2 class="title">
                    <?php echo xlt('Dated Message Log'); ?>
                    <i id="show_hide" class="fa fa-eye-slash ml-2" data-toggle="tooltip" data-placement="top" title="<?php echo xla('Click to Hide Filters'); ?>"></i>
                </h2>
            </div>
            <div class="col-12 filter-section mb-3">
                <form method="get" id="logForm" onsubmit="return top.restoreSession()">
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><?php echo xlt('Filters') ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="section-header mb-2">
                                <h6 class="text-muted"><?php echo xlt('Message Date Range');?></h6>
                            </div>
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

                            <div class="section-header mt-4 mb-2">
                                <h6 class="text-muted"><?php echo xlt('Message Participants');?></h6>
                            </div>
                            <div class="form-group row">
                                <div class="col-12 col-md-6">
                                    <label class="col-form-label" for="sentBy">
                                        <?php echo xlt('Sent By');?>:
                                        <small class="text-muted"><?php echo xlt('Leave blank for all'); ?></small>
                                    </label>
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
                                    <small class="form-text text-muted">
                                        <?php echo xlt('([ctrl] + click or [cmd] + click on Mac to select multiple)'); ?>
                                    </small>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="col-form-label" for="sentTo">
                                        <?php echo xlt('Sent To');?>:
                                        <small class="text-muted"><?php echo xlt('Leave blank for all'); ?></small>
                                    </label>
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
                                    <small class="form-text text-muted">
                                        <?php echo xlt('([ctrl] + click or [cmd] + click on Mac to select multiple)'); ?>
                                    </small>
                                </div>
                            </div>

                            <div class="section-header mt-4 mb-2">
                                <h6 class="text-muted"><?php echo xlt('Message Status');?></h6>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    <input type="checkbox" class="custom-control-input" name="processed" id="processed">
                                    <label class="custom-control-label" for="processed"><?php echo xlt('Processed') ?></label>
                                </div>
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    <input type="checkbox" class="custom-control-input" name="pending" id="pending">
                                    <label class="custom-control-label" for="pending"><?php echo xlt('Pending') ?></label>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="button" id="submitForm" class="btn btn-primary">
                                <i class="fa fa-refresh mr-1"></i><?php echo xlt('Apply Filters') ?>
                            </button>
                            <button type="reset" class="btn btn-secondary ml-1">
                                <i class="fa fa-eraser mr-1"></i><?php echo xlt('Reset') ?>
                            </button>
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

            $('.filter-section').toggle('1000');
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

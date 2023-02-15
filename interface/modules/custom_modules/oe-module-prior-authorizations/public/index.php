<?php

/*
 *  package OpenEMR
 *  link    https://www.open-emr.org
 *  author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  Copyright (c) 2022.
 *  All Rights Reserved
 */

require_once dirname(__FILE__, 5) . "/globals.php";
require_once dirname(__FILE__, 2) . '/vendor/autoload.php';

use Juggernaut\OpenEMR\Modules\PriorAuthModule\Controller\AuthorizationService;
use Juggernaut\OpenEMR\Modules\PriorAuthModule\Controller\ListAuthorizations;
use OpenEMR\Core\Header;
use OpenEMR\Common\Csrf\CsrfUtils;

$pid = $_SESSION['pid'];


function isValid($date, $format = 'Y-m-d')
{
    $dt = DateTime::createFromFormat($format, $date);
    return $dt && $dt->format($format) === $date;
}

if (!empty($_POST['token'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["token"])) {
        CsrfUtils::csrfNotVerified();
    }

    $postStartDate = DateToYYYYMMDD($_POST['start_date']);
    if (isValid($postStartDate) === true) {
        $startDate = $postStartDate ;
    } else {
        $startDate = $_POST['start_date'];
    }

    $postEndDate = DateToYYYYMMDD($_POST['end_date']);
    if (isValid($postEndDate) === true) {
        $endDate = $postEndDate;
    } else {
        $endDate = $_POST['end_date'];
    }

    $postData = new AuthorizationService();
    $postData->setId($_POST['id']);
    $postData->setPid($pid);
    $postData->setAuthNum($_POST['authorization']);
    $postData->setInitUnits($_POST['units']);
    $postData->setStartDate($startDate);
    $postData->setEndDate($endDate);
    $postData->setCpt($_POST['cpts']);
    $postData->storeAuthorizationInfo();
}

$listData = new ListAuthorizations();
$listData->setPid($pid);
$authList = $listData->getAllAuthorizations();

const TABLE_TD = "</td><td>";
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo xlt('Add Prior Auth'); ?></title>
    <?php Header::setupHeader(['common', 'datetime-picker'])?>

    <script>
        $(function() {
            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
        })

        function refreshme() {
            top.restoreSession();
            location.reload();
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="m-4">
                <span style="font-size: xx-large; padding-right: 20px"><?php echo xlt('Prior Authorization Manager'); ?></span>
                <a href="../../../../patient_file/summary/demographics.php" onclick="top.restoreSession()"
                   title="<?php echo xla('Go Back') ?>">
                    <i id="advanced-tooltip" class="fa fa-undo fa-2x small" aria-hidden="true"></i></a>

        </div>
        <div class="m-4">
            <?php if (empty($pid)) {
                echo xlt("You must be in a patients Chart to enter this information");
                die;
            } ?>
            <div class="m-3">
                <h3><?php echo xlt('Enter new authorization'); ?></h3>
            </div>
            <form id="theform" method="post" action="index.php" onsubmit="top.restoreSession()">
                <input type="hidden" name="token" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>">
                <input type="hidden" id="id" name="id" value="">
                <div class="form-row">
                    <div class="col">
                        <input class="form-control" id="authorization" name="authorization" value="" placeholder="
                        <?php echo xla('Authorization Number') ?>">
                    </div>
                    <div class="col">
                        <input class="form-control" id="units" name="units" value="" placeholder="<?php echo xla('Units') ?>">
                    </div>
                    <div class="col">
                        <input class="form-control datepicker" id="start_date" name="start_date" value="" placeholder="<?php echo xla('Start Date') ?>" readonly>
                    </div>
                    <div class="col">
                        <input class="form-control datepicker" id="end_date" name="end_date" value="" placeholder="<?php echo xla('End Date') ?>" readonly>
                    </div>
                </div>
                <div class="form-row">
                    <div class="col">
                        <input class="form-control" id="cpts" name="cpts" value="" placeholder="<?php echo xla('CPTs') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="col">
                        <input class="form-control btn btn-primary" type="submit" value="<?php echo xla('Save') ?>">
                    </div>
                </div>
            </form>
        </div>
        <div class="m-4">
            <table class="table table-striped">
                <caption><?php echo xla('Display of authorization code'); ?></caption>
                <tr>
                    <th scope="col"><?php echo xlt('Authorization Number'); ?></th>
                    <th scope="col"><?php echo xlt('Allocated Units'); ?></th>
                    <th scope="col"><?php echo xlt('Remaining Units'); ?></th>
                    <th scope="col"><?php echo xlt('Start Date'); ?></th>
                    <th scope="col"><?php echo xlt('End Date'); ?></th>
                    <th scope="col"><?php echo xlt('CPTs'); ?></th>
                    <th scope="col"></th>
                    <th scope="col"></th>
                </tr>
                <?php
                if (!empty($authList)) {
                    while ($iter = sqlFetchArray($authList)) {
                        $editData = json_encode($iter);
                        $used = AuthorizationService::getUnitsUsed($iter['auth_num']);
                        $remaining = $iter['init_units'] - $used['count'];
                        print "<tr><td>";
                        print text($iter['auth_num']);
                        print TABLE_TD . text($iter['init_units']);
                        print TABLE_TD . text($remaining);
                        print TABLE_TD . text($iter['start_date']);
                        if ($iter['end_date'] == '0000-00-00') {
                            print TABLE_TD;
                        } else {
                            print TABLE_TD . text($iter['end_date']);
                        }
                        print TABLE_TD . text($iter['cpt']);
                        print TABLE_TD . " <button class='btn btn-primary' onclick=getRowData(" . attr_js($iter['id']) . ")>" . xlt('Edit') . "</button>
                        <input type='hidden' id='" . attr_js($iter['id']) . "' value='" . attr($editData) . "' ></td>";
                        print "<td><a class='btn btn-danger' href='#' onclick=removeEntry(" . attr_js($iter['id']) . ")>" . xlt('Delete') . "</a></td>";

                        print "</tr>";
                    }
                }
                ?>
            </table>
        </div>
        &copy; <?php echo date('Y') . " Juggernaut Systems Express" ?>
    </div>
<script>
    function getRowData(jsonData) {
        let dataArray = document.getElementById(jsonData).value;
        const obj = JSON.parse(dataArray);

        document.getElementById('id').value = obj.id;
        document.getElementById('authorization').value = obj.auth_num;
        document.getElementById('start_date').value = obj.start_date;
        document.getElementById('end_date').value = obj.end_date;
        document.getElementById('cpts').value = obj.cpt;
        document.getElementById('units').value = obj.init_units;
    }

    function removeEntry(id) {
        let url = 'deleter.php?id=' + encodeURIComponent(id) + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>;;
        dlgopen(url, '_blank', 290, 290, '', 'Delete Entry', {
            buttons: [
                {text: <?php echo xlj('Done') ?>, style: 'danger btn-sm', close: true}
            ],
            onClosed: 'refreshme'
        })
    }
</script>

</body>
</html>

<?php

/*
 *  package OpenEMR
 *  link    https://www.open-emr.org
 *  author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  Copyright (c) 2022.
 *  All Rights Reserved
 */



require_once dirname(__FILE__, 6) . "/globals.php";
require_once dirname(__FILE__, 3) . '/vendor/autoload.php';

use Juggernaut\OpenEMR\Modules\PriorAuthModule\Controller\AuthorizationService;
use OpenEMR\Core\Header;

$data = new AuthorizationService();
$patients = $data->listPatientAuths();

?>
<!doctype html>
<html lang="en">
<head>
    <?php Header::setupHeader(['common', 'opener']) ?>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo xlt("List Exising Prior Auths Report"); ?></title>
    <script>
        // opens the demographic and encounter screens in a new window
        function openNewTopWindow(newpid) {
            top.restoreSession();
            top.RTop.location = "<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics.php?set_pid=" + encodeURIComponent(newpid);
        }
    </script>
</head>
<body>
    <div class="container-lg" style="padding-top: 6em">
        <h1><?php echo xlt("Prior Auths") ?></h1>
        <div class="table">
            <table class="table table-striped">
                <caption><?php echo xlt("Patients with prior auths"); ?></caption>
                <th scope="col"><?php echo xlt("MRN"); ?></th>
                <th scope="col"><?php echo xlt("Name"); ?></th>
                <th scope="col"><?php echo xlt("Ins"); ?></th>
                <th scope="col"><?php echo xlt("Auths"); ?></th>
                <th scope="col"><?php echo xlt("Start"); ?></th>
                <th scope="col"><?php echo xlt("End"); ?></th>
                <th scope="col">#<?php echo xlt("of Units"); ?></th>
                <th scope="col"><?php echo xlt("Remaining"); ?></th>

                <?php
                $count = 0;
                $name = '';
                while ($iter = sqlFetchArray($patients)) {
                    if (!empty($iter['pid'])) {
                        $pid = $iter['pid'];
                    } else {
                        $pid = $iter['mrn'];
                    }
                    $requireAuth = AuthorizationService::requiresAuthorization($iter['pid']);
                    $status = AuthorizationService::patientInactive($pid);

                    if ($iter['provider'] != 133 && ($requireAuth['field_value'] != 'YES')) {
                        continue;
                    }

                    if ($status['status'] == 'inactive') {
                        continue;
                    }

                    $numbers = AuthorizationService::countUsageOfAuthNumber($pid, $iter['auth_num']);
                    $insurance = AuthorizationService::insuranceName($pid);

                    if ($name !== $iter['fname'] . " " . $iter['lname']) {
                        print "<tr><td><a href='#' onclick='openNewTopWindow(" . attr_js($pid) . ")'>" . text($pid) . "</a></td>";
                        print "<td><strong>" . text($iter['lname']) . ", " . text($iter['fname']) . "</strong></td>";
                        print "<td style='max-width:75px;'>" . text($insurance['name']) . "</td>";
                    } else {
                        print "<td></td>";
                        print "<td></td>";
                        print "<td></td>";
                    }
                    print "<td>" . text($iter['auth_num']) . "</td>";
                    print "<td>" . text($iter['start_date']) . "</td>";
                    print "<td>" . text($iter['end_date']) . "</td>";
                    if (($iter['end_date'] < date('Y-m-d')) && ($iter['end_date'] !== '0000-00-00') && !empty($iter['auth_num'])) {
                        print "<td style='color: red'><strong>" . xlt('Expired') . "</strong></td>";
                        print "<td></td>";
                    } else {
                        print "<td>" . text($iter['init_units']) . "</td>";
                        $unitCount = $iter['init_units'] - $numbers['count'];
                        if ($unitCount > 0) {
                            print "<td>" . text($unitCount) . "</td>";
                        } else {
                            print "<td>&nbsp</td>";
                        }
                    }

                    print "</tr>";
                    $name = $iter['fname'] . " " . $iter['lname'];
                    $count++;
                }
                ?>
            </table>
            <table>
                <tr>Count <?php echo $count; ?></tr>
            </table>

        </div>
        &copy; <?php echo date('Y') . " Juggernaut Systems Express" ?>
    </div>

</body>
</html>

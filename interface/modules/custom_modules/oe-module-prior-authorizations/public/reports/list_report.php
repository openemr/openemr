<?php

/*
 *  package OpenEMR
 *  link    https://www.open-emr.org
 *  author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  Copyright (c) 2022.
 *  All Rights Reserved
 */

require_once dirname(__FILE__, 6) . "/globals.php";

use Juggernaut\OpenEMR\Modules\PriorAuthModule\Controller\AuthorizationService;
use OpenEMR\Core\Header;

$data = new AuthorizationService();
$patients = $data->listPatientAuths();

$hide_expired = ($_GET['hide_expired'] ?? '0') === '1';
$total_initial_units = 0;
$total_used_units = 0;

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
          <form method="get" action="">
              <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" name="hide_expired" value="1" id="hideExpiredCheck" <?php echo isset($_GET['hide_expired']) && $_GET['hide_expired'] == '1' ? 'checked' : ''; ?> onchange="this.form.submit()">
                  <label class="form-check-label" for="hideExpiredCheck">
                      <?php echo xlt("Hide Expired Authorizations"); ?>
                  </label>
              </div>
          </form>
        <div class="table">
          <div class="row mb-4">
              <div class="col-md-4 mx-auto">
                  <h2><?php echo xlt("Usage Summary"); ?></h2>
                  <div style="height: 300px;">
                      <canvas id="usageChart"></canvas>
                  </div>
              </div>
          </div>
            <table class="table table-striped">
                <caption><?php echo xlt("Patients with prior auths"); ?></caption>
                <th scope="col"><?php echo xlt("MRN"); ?></th>
                <th scope="col"><?php echo xlt("Name"); ?></th>
                <th scope="col"><?php echo xlt("Ins"); ?></th>
                <th scope="col"><?php echo xlt("Auths"); ?></th>
                <th scope="col"><?php echo xlt("CPT"); ?></th>
                <th scope="col"><?php echo xlt("Start"); ?></th>
                <th scope="col"><?php echo xlt("End"); ?></th>
                <th scope="col">#<?php echo xlt("of Units"); ?></th>
                <th scope="col"><?php echo xlt("Remaining"); ?></th>
                <th scope="col"><?php echo xlt("%"); ?></th>
  

                <?php
                $count = 0;
                $name = '';
                while ($iter = sqlFetchArray($patients)) {
                    if (!empty($iter['pid'])) {
                        $pid = $iter['pid'];
                    } else {
                        $pid = $iter['mrn'];
                    }
            // This part requires custom form and custom table to function
            /*
                    $requireAuth = AuthorizationService::requiresAuthorization($iter['pid']);
                    $status = AuthorizationService::patientInactive($pid);

                    if ($iter['provider'] != 133 && ($requireAuth['field_value'] != 'YES')) {
                        continue;
                    }

                    if ($status['status'] == 'inactive') {
                        continue;
                    }
            */

                    if ($hide_expired) {
                        if (!empty($iter['end_date']) && $iter['end_date'] !== '0000-00-00' && $iter['end_date'] < date('Y-m-d')) {
                            continue;
                        }
                    }
                    $numbers = AuthorizationService::countUsageOfAuthNumber(
                        $iter['auth_num'],
                        $pid,
                        $iter['cpt'],
                        $iter['start_date'],
                        $iter['end_date']
                    );

                    $insurance = AuthorizationService::insuranceName($pid);

                    if ($name !== $iter['fname'] . " " . $iter['lname']) {
                        print "<tr><td><a href='#' onclick='openNewTopWindow(" . attr_js($pid) . ")'>" . text($pid) . "</a></td>";
                        print "<td><strong>" . text($iter['lname']) . ", " . text($iter['fname']) . "</strong></td>";
                        print "<td style='max-width:75px;'>" . text($insurance) . "</td>";
                    } else {
                        print "<td></td>";
                        print "<td></td>";
                        print "<td></td>";
                    }
                    print "<td>" . text($iter['auth_num']) . "</td>";
                    print "<td>" . text($iter['cpt']) . "</td>";
                    print "<td>" . text($iter['start_date']) . "</td>";
                    print "<td>" . text($iter['end_date']) . "</td>";

                    if (($iter['end_date'] < date('Y-m-d')) && ($iter['end_date'] !== '0000-00-00') && (!empty($iter['auth_num']))) {
                        print "<td style='color: red'><strong>" . xlt('Expired') . "</strong></td>";
                        print "<td></td>";
                        print "<td></td>";
                    } else {
                        $initialUnits = (int)$iter['init_units'];
                        print "<td>" . text($initialUnits) . "</td>";

                        $usedUnits = $initialUnits - $numbers;
                        $unitCount = $usedUnits;
                        if (($iter['end_date'] >= date('Y-m-d') || $iter['end_date'] === '0000-00-00') && !empty($iter['auth_num'])) {
                            $total_initial_units += $initialUnits;
                            $total_used_units += $usedUnits;
                        }

                        if ($unitCount > 0) {
                            print "<td>" . text($unitCount) . "</td>";
                        } else {
                            print "<td>&nbsp</td>";
                        }

                        if ($initialUnits > 0) {
                            $percentRemaining = round(($usedUnits / $initialUnits) * 100);
                        } else {
                            $percentRemaining = 0;
                        }

                        $barColor = match (true) {
                            ($percentRemaining <= 33) => '#dc3545', // Red: Empty
                            ($percentRemaining <= 66) => '#ffc107', // Yellow: Getting low
                            default => '#4CAF50', // Green: Full/Plenty remaining
                        };

                        print "<td>";
                        print "<div style='background-color:#eee; height:20px; width:150px; border:1px solid #ccc; position:relative;'>";
                        print "<div style='background-color:{$barColor}; height:100%; width:{$percentRemaining}%; position:absolute;'></div>";
                        print "<div style='position:absolute; top:0; width:100%; text-align:center; line-height:20px; color:#000; font-weight:bold; font-size:12px;'>{$percentRemaining}%</div>";
                        print "</div>";
                        print "</td>";
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
   <script>
    const total_initial = <?php echo $total_initial_units; ?>;
    const total_used = <?php echo $total_used_units; ?>;
    const total_remaining = total_initial - total_used;

    const ctx = document.getElementById('usageChart');

    if (total_initial > 0) {
        new Chart(ctx, {
            type: 'doughnut', // circle chart
            data: {
                labels: ['<?php echo xlt("Units Used"); ?>', '<?php echo xlt("Units Remaining"); ?>'],
                datasets: [{
                    label: '<?php echo xlt("Unit Count"); ?>',
                    data: [total_used, total_remaining],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)', 
                        'rgba(255, 99, 132, 0.8)' 
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: '<?php echo xlt("Total Units: ") . $total_initial_units; ?>'
                    }
                }
            }
        });
    } else {
        ctx.style.display = 'none';
        const container = ctx.closest('.col-md-4');
        if (container) {
            container.innerHTML = '<p class="text-center"><?php echo xlt("No active authorizations with units found."); ?></p>';
        }
    }
    </script>
</body>
</html>

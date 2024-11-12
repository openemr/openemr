<?php

/*
 *   @package   OpenEMR
 *   @link      http://www.open-emr.org
 *
 *   @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   Copyright (c)  Juggernaut Systems Express
 *   @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
require_once dirname(__DIR__) . '/src/PaymentsAndAdjustments.php';
use OpenEMR\Reports\Productivity\PaymentsAndAdjustments;

function showTheMoney($pid, $encounter, $surplus): array
{
    $money = new PaymentsAndAdjustments();
    $money->pid = $pid;
    $money->encounter = $encounter;
    $money->name = $surplus;
    return $money->getPaymentsAdjustments();
}

global $reportData;

?>
    <div class="row">
        <div class="col-md-12 mt-5" id="printableReport">
            <table class="table table-striped" id="reportTable">
                <thead>
                    <tr>
                        <td><strong><?php echo xlt('Provider') ?></strong></td>
                        <td><strong><?php echo xlt('DOS') ?></strong></td>
                        <td><strong><?php echo xlt('Patient') ?></strong></td>
                        <td><strong><?php echo xlt('Code') ?></strong></td>
                        <td><strong><?php echo xlt('Description') ?></strong></td>
                        <td><strong><?php echo xlt('Insurance Company') ?></strong></td>
                        <td><strong><?php echo xlt('Total Charge') ?></strong></td>
                        <td><strong><?php echo xlt('Minutes') ?></strong></td>
                        <td><strong><?php echo xlt('Payment') ?></strong></td>
                        <td><strong><?php echo xlt('Adjustment') ?></strong></td>
                        <td><strong><?php echo xlt('Balance') ?></strong></td>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if (isset($reportData['message'])) {
                        echo '<tr><td colspan="11">' . $reportData['message'] . '</td></tr>';
                    } else {
                    $surplus = 0;
                    foreach ($reportData as $row):
                        $pid = (int) $row['pid'] ?? '';

                        if ($pname != $row['patient_name']) {
                            $surplus = 0;
                        }
                        if (!empty($row['pid'])) {
                            $cash = showTheMoney($pid, $row['encounter'], $surplus);
                        }
                        if ($row['fee'] == '0.00') {
                            continue;
                        }
                        if ($pname == $row['patient_name'] && $text == $row['code_text']) {
                            continue;
                        }

                        ?>
                        <tr>
                            <td width="120px"><?php echo $row['provider'] ?></td>
                            <td><?php echo substr($row['date'], 0, -9) ?></td>
                            <td><?php echo $row['patient_name'] ?></td>
                            <td><?php echo $row['code']?></td>
                            <td width="520"><?php echo $row['code_text'] ?></td>
                            <td><?php echo $row['insurance_company_name'] ?></td>
                            <td><?php echo $row['fee'] ?></td>
                            <td><?php echo $row['units'] ?></td>
                            <td><?php echo $cash['payments']; ?></td>
                            <td><?php echo $cash['adjustments'] ?></td>
                            <td><?php
                                //the surplus has to change when the patient changes. It is the remainder of the fee after the payments and adjustments
                                    if (!empty($row['fee'])) {
                                        $preBalance = ($cash['payments'] + $cash['adjustments']);

                                        echo oeFormatMoney($row['fee'] - $preBalance);
                                    }

                                $pname = $row['patient_name'];
                                    $text = $row['code_text'];
                                    $surplus++;
                                ?></td>
                    </tr>
                <?php endforeach; }?>
                </tbody>
            </table>
        </div>
    </div>


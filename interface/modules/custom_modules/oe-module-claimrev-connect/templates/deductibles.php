<?php

/**
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use OpenEMR\Modules\ClaimRevConnector\PrintProperty;

/** @var \stdClass $eligibilityData */

$str = static function (object $o, string $prop): string {
    if (!property_exists($o, $prop)) {
        return '';
    }
    $v = $o->$prop;
    return is_string($v) ? $v : '';
};

foreach (
    [
        'deductibleReleaseReason' => 'Deductible Release Reason:',
        'deductible' => 'Deductible:',
        'deductibleRemaining' => 'Deductible Remaining:',
        'outOfPocket' => 'Out Of Pocket:',
        'outOfPocketRemaining' => 'Out Of Pocket Remaining:',
        'lifetimeLimit' => 'Lifetime Limit:',
        'lifetimeLimitRemaining' => 'Lifetime Limit Remaining:',
        'spendDownAmount' => 'Spend Down Amount:',
        'deductibleManagementPending' => 'Management Pending:',
    ] as $field => $label
) {
    if (property_exists($eligibilityData, $field)) {
        PrintProperty::displayProperty($label, $eligibilityData->$field);
    }
}

$deductibles = property_exists($eligibilityData, 'deductibles') && is_iterable($eligibilityData->deductibles) ? $eligibilityData->deductibles : null;
if ($deductibles !== null) {
    ?>
        <table class="table">
            <thead>
                <th scope="col"><?php echo xlt("Service Type"); ?></th>
                <th scope="col"><?php echo xlt("Coverage Level"); ?></th>
                <th scope="col"><?php echo xlt("Insurance Type"); ?></th>
                <th scope="col"><?php echo xlt("In Plan Network"); ?></th>
                <th scope="col"><?php echo xlt("Annual Amt"); ?></th>
                <th scope="col"><?php echo xlt("Episode Amt"); ?></th>
                <th scope="col"><?php echo xlt("Remaining Amt"); ?></th>
                <th scope="col"><?php echo xlt("Plan Name"); ?></th>
            </thead>
            <tbody>
            <?php
            foreach ($deductibles as $deductible) {
                if (!is_object($deductible)) {
                    continue;
                }
                ?>
                        <tr>
                            <td> <?php echo text($str($deductible, 'serviceTypeDescription')); ?> (<?php echo text($str($deductible, 'serviceTypeCode')); ?>)</td>
                            <td> <?php echo text($str($deductible, 'coverageLevelDescription')); ?> (<?php echo text($str($deductible, 'coverageLevelCode')); ?>)</td>
                            <td> <?php echo text($str($deductible, 'insuranceTypeDescription')); ?> (<?php echo text($str($deductible, 'insuranceTypeCode')); ?>)</td>
                            <td> <?php echo text($str($deductible, 'inPlanNetwork')); ?></td>
                            <td> <?php echo text($str($deductible, 'annualAmount')); ?></td>
                            <td> <?php echo text($str($deductible, 'episodeAmount')); ?></td>
                            <td> <?php echo text($str($deductible, 'remainingAmount')); ?></td>
                            <td> <?php echo text($str($deductible, 'planName')); ?></td>
                        </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    <?php
}

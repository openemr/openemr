<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Modules\ClaimRevConnector\PrintProperty;

if (property_exists($eligibilityData, 'deductibleReleaseReason')) {
    PrintProperty::displayProperty("Deductible Release Reason:", $eligibilityData->deductibleReleaseReason);
}
if (property_exists($eligibilityData, 'deductible')) {
    PrintProperty::displayProperty("Deductible:", $eligibilityData->deductible);
}
if (property_exists($eligibilityData, 'deductibleRemaining')) {
    PrintProperty::displayProperty("Deductible Remaining:", $eligibilityData->deductibleRemaining);
}
if (property_exists($eligibilityData, 'outOfPocket')) {
    PrintProperty::displayProperty("Out Of Pocket:", $eligibilityData->outOfPocket);
}
if (property_exists($eligibilityData, 'outOfPocketRemaining')) {
    PrintProperty::displayProperty("Out Of Pocket Remaining:", $eligibilityData->outOfPocketRemaining);
}
if (property_exists($eligibilityData, 'lifetimeLimit')) {
    PrintProperty::displayProperty("Lifetime Limit:", $eligibilityData->lifetimeLimit);
}
if (property_exists($eligibilityData, 'lifetimeLimitRemaining')) {
    PrintProperty::displayProperty("Lifetime Limit Remaining:", $eligibilityData->lifetimeLimitRemaining);
}
if (property_exists($eligibilityData, 'spendDownAmount')) {
    PrintProperty::displayProperty("Spend Down Amount:", $eligibilityData->spendDownAmount);
}
if (property_exists($eligibilityData, 'deductibleManagementPending')) {
    PrintProperty::displayProperty("Management Pending:", $eligibilityData->deductibleManagementPending);
}

if (property_exists($eligibilityData, 'deductibles')) {
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
            foreach ($eligibilityData->deductibles as $deductible) {
                ?>
                        <tr>
                            <td> <?php echo text($deductible->serviceTypeDescription) ?> (<?php echo text($deductible->serviceTypeCode) ?>)</td>
                            <td> <?php echo text($deductible->coverageLevelDescription) ?> (<?php echo text($deductible->coverageLevelCode) ?>)</td>
                            <td> <?php echo text($deductible->insuranceTypeDescription) ?> (<?php echo text($deductible->insuranceTypeCode) ?>)</td>
                            <td> <?php echo text($deductible->inPlanNetwork) ?></td>
                            <td> <?php echo text($deductible->annualAmount) ?></td>
                            <td> <?php echo text($deductible->episodeAmount) ?></td>
                            <td> <?php echo text($deductible->remainingAmount) ?></td>
                            <td> <?php echo text($deductible->planName) ?></td>
                        </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    <?php
}
?>

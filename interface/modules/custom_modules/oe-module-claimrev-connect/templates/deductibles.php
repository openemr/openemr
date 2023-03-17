<?php
/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
    use OpenEMR\Modules\ClaimRevConnector\PrintProperty;

    if (property_exists($eligibilityData, 'deductibleReleaseReason'))
    {
        PrintProperty::DisplayProperty("Deductible Release Reason:",$eligibilityData->deductibleReleaseReason);
    }
    if (property_exists($eligibilityData, 'deductible'))
    {
        PrintProperty::DisplayProperty("Deductible:",$eligibilityData->deductible);
    }
    if (property_exists($eligibilityData, 'deductibleRemaining'))
    {
        PrintProperty::DisplayProperty("Deductible Remaining:",$eligibilityData->deductibleRemaining);
    }
    if (property_exists($eligibilityData, 'outOfPocket'))
    {
        PrintProperty::DisplayProperty("Out Of Pocket:",$eligibilityData->outOfPocket);
    }
    if (property_exists($eligibilityData, 'outOfPocketRemaining'))
    {
        PrintProperty::DisplayProperty("Out Of Pocket Remaining:",$eligibilityData->outOfPocketRemaining);
    }
    if (property_exists($eligibilityData, 'lifetimeLimit'))
    {
        PrintProperty::DisplayProperty("Lifetime Limit:",$eligibilityData->lifetimeLimit);
    }
    if (property_exists($eligibilityData, 'lifetimeLimitRemaining'))
    {
        PrintProperty::DisplayProperty("Lifetime Limit Remaining:",$eligibilityData->lifetimeLimitRemaining);
    }
    if (property_exists($eligibilityData, 'spendDownAmount'))
    {
        PrintProperty::DisplayProperty("Spend Down Amount:",$eligibilityData->spendDownAmount);
    }
    if (property_exists($eligibilityData, 'deductibleManagementPending'))
    {
        PrintProperty::DisplayProperty("Management Pending:",$eligibilityData->deductibleManagementPending);
    }

    if (property_exists($eligibilityData, 'deductibles'))
    {
?>
        <table class="table">
            <thead>
                <th scope="col">Service Type</th>
                <th scope="col">Coverage Level</th>   
                <th scope="col">Insurance Type</th>
                <th scope="col">In Plan Network</th>  
                <th scope="col">Annual Amt</th>    
                <th scope="col">Episode Amt</th>  
                <th scope="col">Remaining Amt</th>  
                <th scope="col">Plan Name</th>  
            </thead>
            <tbody>
                <?php
                    foreach($eligibilityData->deductibles as $deductible)
                    {
                ?>
                        <tr>
                            <td> <?php echo($deductible->serviceTypeDescription) ?> (<?php echo($deductible->serviceTypeCode) ?>)</td>
                            <td> <?php echo($deductible->coverageLevelDescription) ?> (<?php echo($deductible->coverageLevelCode) ?>)</td>
                            <td> <?php echo($deductible->insuranceTypeDescription) ?> (<?php echo($deductible->insuranceTypeCode) ?>)</td>
                            <td> <?php echo($deductible->inPlanNetwork) ?></td>
                            <td> <?php echo($deductible->annualAmount) ?></td>
                            <td> <?php echo($deductible->episodeAmount) ?></td>
                            <td> <?php echo($deductible->remainingAmount) ?></td>
                            <td> <?php echo($deductible->planName) ?></td>
                        </tr>
                <?php
                    }
                ?>
            </tbody>
        </table>
<?php
    }
?>
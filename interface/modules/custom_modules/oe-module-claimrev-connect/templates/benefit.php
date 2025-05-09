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

$benefitPatResponse = ["B","C","G","J","Y"];

foreach ($benefits as $benefit) {
    ?>
<div class="card">
    <div class="card-body">
        <h5 class="card-title"> <?php echo xlt("Benefit"); ?> - <?php echo text($benefit->benefitInformationDesc); ?> </h5>
        
        <div class="row"> 
            <div class="col">
                <strong> <?php echo xlt("Service Type");?></strong>                
            </div>
            <div class="col">
                <ul>
                    <?php
                    foreach ($benefit->serviceTypes as $st) {
                        ?>
                        <li><?php echo text($st->serviceTypeDesc) ?></li>
                        <?php
                    }
                    ?>
                </ul>                
            </div>
            
        </div>
    <?php
            PrintProperty::displayProperty("Coverage Level", $benefit->coverageLevel);
            PrintProperty::displayProperty("Insurance Type", $benefit->insuranceTypeCodeDesc);
            PrintProperty::displayProperty("Coverage Description", $benefit->planCoverageDescription);
            PrintProperty::displayProperty("Time Period", $benefit->timePeriodQualifierDesc);
    if (in_array($benefit->benefitInformation, $benefitPatResponse)) {
        PrintProperty::displayProperty("Patient Responsibility", $benefit->benefitAmount, "$");
    } else {
        PrintProperty::displayProperty("Amount", $benefit->benefitAmount, "$");
    }
    if ($benefit->benefitInformation == 'A') {
        PrintProperty::displayProperty("Patient Responsibility", $benefit->benefitPercent, "", "%");
    } else {
        PrintProperty::displayProperty("Benefit Percent", $benefit->benefitPercent, "", "%");
    }
            PrintProperty::displayProperty("Benefit Quantity", $benefit->benefitQuantity, "", " - " . $benefit->quantityQualifierDesc);
            PrintProperty::displayProperty("Authorization/Certification Indicator", $benefit->certificationIndicator);
            PrintProperty::displayProperty("In Plan Network", $benefit->inPlanNetworkIndicator);
            include 'service_delivery.php';
            include 'procedure_info.php';
            include 'date_information.php';
            include 'identifier_info.php';
            include 'additional_info.php';
            include 'related_entity.php';
            include 'messages.php';
    ?>

     

    </div>
</div>
    <?php
}
?>



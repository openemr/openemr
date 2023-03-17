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
$benefitPatResponse = ["B","C","G","J","Y"];
function displayProperty($title, $propertyValue, $qualifier = "", $ending = "")
{
    if($propertyValue != '')
    {
?>
    <div class="row">
        <div class="col">
                <strong> <?php echo($title); ?> </strong>
                
            </div>
        <div class="col">
            <div>                
                <?php
                    if($ending == "%")
                    {
                        $propertyValue = $propertyValue * 100;
                    } 
                    echo($qualifier . $propertyValue . $ending); 
                ?>
            </div>
        </div>
    </div>

<?php

    }
}


foreach( $benefits as $benefit )
{
?>
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Benefit - <?php echo($benefit->benefitInformationDesc); ?> </h5>
        
        <div class="row"> 
            <div class="col">
                <strong>Service Type</strong>                
            </div>
            <div class="col">
                <ul>
                    <?php 
                        foreach($benefit->serviceTypes as $st)
                        {
?>
                        <li><?php echo($st->serviceTypeDesc) ?></li>
<?php 
                        }
?>
                </ul>                
            </div>
            
        </div>
<?php 
            displayProperty("Coverage Level", $benefit->coverageLevel);
            displayProperty("Insurance Type", $benefit->insuranceTypeCodeDesc);
            displayProperty("Coverage Description", $benefit->planCoverageDescription);
            displayProperty("Time Period", $benefit->timePeriodQualifierDesc);
            if(in_array($benefit->benefitInformation,$benefitPatResponse))
            {
                displayProperty("Patient Responsibility", $benefit->benefitAmount, "$");
            }
            else
            {
                displayProperty("Amount", $benefit->benefitAmount, "$");
            }
            if($benefit->benefitInformation == 'A')
            {
                displayProperty("Patient Responsibility", $benefit->benefitPercent, "","%");
            }
            else
            {
                displayProperty("Benefit Percent", $benefit->benefitPercent, "","%");
            }
            displayProperty("Benefit Quantity", $benefit->benefitQuantity, "", " - " . $benefit->quantityQualifierDesc );
            displayProperty("Authorization/Certification Indicator", $benefit->certificationIndicator);
            displayProperty("In Plan Network", $benefit->inPlanNetworkIndicator);
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



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

if ($benefit->serviceDeliveries != null && $benefit->serviceDeliveries) {
    ?>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h6><?php echo xlt("Services Delivery");?></h6>
                    <ul>
    <?php
    foreach ($benefit->serviceDeliveries as $serviceDelivery) {
        if ($serviceDelivery->benefitQuantity != "") {
            ?>
                                    <li>
            <?php
            if ($serviceDelivery->quantityQualifier != '') {
                ?>
                                            <span> <?php echo text($serviceDelivery->benefitQuantity); ?></span> <span><?php echo text($serviceDelivery->quantityQualifierDesc);  ?></span>
                <?php
            }
            if ($serviceDelivery->sampleSelectionModulus != '') {
                ?>
                                        <?php echo text($serviceDelivery->sampleSelectionModulus); ?>
                                            <span> <?php echo text($serviceDelivery->quantityQualifierDesc); ?></span> <?php echo xlt("per") ?> <span><?php  echo text($serviceDelivery->measurementCodeDesc);  ?></span>
                                    <?php
            }
                                                            echo text($serviceDelivery->periodCount); ?> <?php echo text($serviceDelivery->timePeriodDesc);
if ($serviceDelivery->frequencyCode != '') {
    if ($serviceDelivery->FrequencyCodeDesc != '') {
        ?>
                                                <span> <?php echo text($serviceDelivery->FrequencyCodeDesc); ?></span> <span><?php echo text($serviceDelivery->patternTimeCodeDesc); ?> </span>
        <?php
    }
}
?>      
                                    </li>
            <?php
        }

        ?>
                               
        <?php
    }
    ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>

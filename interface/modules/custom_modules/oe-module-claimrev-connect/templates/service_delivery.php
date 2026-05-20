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

/** @var \stdClass $benefit */

declare(strict_types=1);

$serviceDeliveries = property_exists($benefit, 'serviceDeliveries') && is_iterable($benefit->serviceDeliveries) ? $benefit->serviceDeliveries : null;
if ($serviceDeliveries === null) {
    return;
}

$str = static function (object $o, string $prop): string {
    if (!property_exists($o, $prop)) {
        return '';
    }
    $v = $o->$prop;
    return is_string($v) ? $v : '';
};
?>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h6><?php echo xlt("Services Delivery");?></h6>
                    <ul>
    <?php
    foreach ($serviceDeliveries as $serviceDelivery) {
        if (!is_object($serviceDelivery)) {
            continue;
        }
        $benefitQuantity = $str($serviceDelivery, 'benefitQuantity');
        if ($benefitQuantity === '') {
            continue;
        }
        $quantityQualifier = $str($serviceDelivery, 'quantityQualifier');
        $quantityQualifierDesc = $str($serviceDelivery, 'quantityQualifierDesc');
        $sampleSelectionModulus = $str($serviceDelivery, 'sampleSelectionModulus');
        $measurementCodeDesc = $str($serviceDelivery, 'measurementCodeDesc');
        $periodCount = $str($serviceDelivery, 'periodCount');
        $timePeriodDesc = $str($serviceDelivery, 'timePeriodDesc');
        $frequencyCode = $str($serviceDelivery, 'frequencyCode');
        $frequencyCodeDesc = $str($serviceDelivery, 'FrequencyCodeDesc');
        $patternTimeCodeDesc = $str($serviceDelivery, 'patternTimeCodeDesc');
        ?>
                                    <li>
        <?php
        if ($quantityQualifier !== '') {
            ?>
                                            <span> <?php echo text($benefitQuantity); ?></span> <span><?php echo text($quantityQualifierDesc); ?></span>
            <?php
        }
        if ($sampleSelectionModulus !== '') {
            ?>
                                        <?php echo text($sampleSelectionModulus); ?>
                                            <span> <?php echo text($quantityQualifierDesc); ?></span> <?php echo xlt("per") ?> <span><?php echo text($measurementCodeDesc); ?></span>
                                    <?php
        }
        echo text($periodCount); ?> <?php echo text($timePeriodDesc);
if ($frequencyCode !== '' && $frequencyCodeDesc !== '') { ?>
                                                <span> <?php echo text($frequencyCodeDesc); ?></span> <span><?php echo text($patternTimeCodeDesc); ?> </span>
        <?php } ?>
                                    </li>
        <?php
    }
    ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

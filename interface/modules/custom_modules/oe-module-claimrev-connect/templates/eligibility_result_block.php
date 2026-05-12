<?php

/**
 * Renders one or more eligibility-style result blocks (each with Quick Info,
 * Deductibles, Benefits, Medicare, and Validations sub-tabs).
 *
 * Used for both the Eligibility tab and the Coverage Discovery tab — the
 * ClaimRev API returns the same SharpRevenueEligibilityResponse shape for both.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

/**
 * Required variables in scope:
 *   @var iterable<\stdClass> $results     SharpRevenueEligibilityResponse[]
 *   @var string              $prKey       payer-responsibility key (e.g. "primary")
 *   @var string              $tabPrefix   unique prefix for tab DOM ids (e.g. "elig", "cd")
 *   @var string              $path        templates directory
 */

$blockIndex = 0;
foreach ($results as $result) {
    $blockIndex++;
    $eligibilityData = $result;
    $benefits = null;
    $subscriberPatient = null;
    $data = null;
    if (property_exists($eligibilityData, 'mapped271')) {
        $data = $eligibilityData->mapped271;
    }

    if (is_object($data) && property_exists($data, 'dependent')) {
        $dependent = $data->dependent;
        if (is_object($dependent) && property_exists($dependent, 'benefits')) {
            $benefits = $dependent->benefits;
            $subscriberPatient = $dependent;
        }
    }

    if (is_object($data) && property_exists($data, 'subscriber')) {
        $subscriber = $data->subscriber;
        if (is_object($subscriber) && property_exists($subscriber, 'benefits')) {
            $benefits = $subscriber->benefits;
            $subscriberPatient = $subscriber;
        }
    }

    $idSuffix = $tabPrefix . '-' . $prKey . '-' . attr((string) $blockIndex);
    ?>
    <ul class="nav nav-tabs nav-tabs-sm mb-2 mt-2">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#<?php echo $idSuffix; ?>-quick"><?php echo xlt("Quick Info"); ?></a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#<?php echo $idSuffix; ?>-deductibles"><?php echo xlt("Deductibles"); ?></a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#<?php echo $idSuffix; ?>-benefits"><?php echo xlt("Benefits"); ?></a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#<?php echo $idSuffix; ?>-medicare"><?php echo xlt("Medicare"); ?></a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#<?php echo $idSuffix; ?>-validations"><?php echo xlt("Validations"); ?></a></li>
    </ul>
    <div class="tab-content">
        <div id="<?php echo $idSuffix; ?>-quick" class="tab-pane active">
            <?php include $path . '/quick_info.php'; ?>
        </div>
        <div id="<?php echo $idSuffix; ?>-deductibles" class="tab-pane">
            <?php include $path . '/deductibles.php'; ?>
        </div>
        <div id="<?php echo $idSuffix; ?>-benefits" class="tab-pane">
            <?php
            if (is_object($data)) {
                $source = property_exists($data, 'informationSourceName') ? $data->informationSourceName : '';
                include $path . '/source.php';
                $receiver = property_exists($data, 'receiver') ? $data->receiver : null;
                include $path . '/receiver.php';
            }
            if ($benefits != null) {
                include $path . '/subscriber_patient.php';
                include $path . '/benefit.php';
            }
            ?>
        </div>
        <div id="<?php echo $idSuffix; ?>-medicare" class="tab-pane">
            <?php include $path . '/medicare_info.php'; ?>
        </div>
        <div id="<?php echo $idSuffix; ?>-validations" class="tab-pane">
            <?php include $path . '/validation.php'; ?>
        </div>
    </div>
<?php } //end foreach result

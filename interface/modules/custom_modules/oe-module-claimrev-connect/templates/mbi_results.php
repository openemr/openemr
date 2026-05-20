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

/** @var \stdClass|null $mbiResults set by the caller (individual->mbiFinderResults) */

declare(strict_types=1);

if ($mbiResults === null) {
    echo xlt("No MBI results");
    return;
}

$mbiStatus = property_exists($mbiResults, 'mbiFinderStatus') && is_string($mbiResults->mbiFinderStatus) ? $mbiResults->mbiFinderStatus : '';
$foundMbi = property_exists($mbiResults, 'foundMbi') && is_string($mbiResults->foundMbi) ? $mbiResults->foundMbi : '';
$mbiError = property_exists($mbiResults, 'mbiFinderErrorMessage') && is_string($mbiResults->mbiFinderErrorMessage) ? $mbiResults->mbiFinderErrorMessage : '';
?>
<div class="card mb-2">
    <div class="card-header"><?php echo xlt("MBI Finder Results"); ?></div>
    <div class="card-body">
        <?php if ($mbiStatus !== '') { ?>
            <div class="row mb-1">
                <div class="col-3 font-weight-bold"><?php echo xlt("Status"); ?>:</div>
                <div class="col"><?php echo text($mbiStatus); ?></div>
            </div>
        <?php } ?>
        <?php if ($foundMbi !== '') { ?>
            <div class="row mb-1">
                <div class="col-3 font-weight-bold"><?php echo xlt("MBI Number"); ?>:</div>
                <div class="col">
                    <span class="font-weight-bold text-success" style="font-size: 1.1em;"><?php echo text($foundMbi); ?></span>
                </div>
            </div>
        <?php } ?>
        <?php if ($mbiError !== '') { ?>
            <div class="row mb-1">
                <div class="col-3 font-weight-bold text-danger"><?php echo xlt("Error"); ?>:</div>
                <div class="col text-danger"><?php echo text($mbiError); ?></div>
            </div>
        <?php } ?>
    </div>
</div>

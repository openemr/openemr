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

/** @var \stdClass $subscriberPatient */

declare(strict_types=1);

$str = static function (object $o, string $prop): string {
    if (!property_exists($o, $prop)) {
        return '';
    }
    $v = $o->$prop;
    return is_string($v) ? $v : '';
};
?>
<div class="card">
    <div class="card-body">
        <h5 class="card-title"><?php echo xlt("Subscriber/Patient Information"); ?></h5>
        <div class="row">
            <div class="col">
                <strong><?php echo xlt("Name"); ?></strong>
            </div>
            <div class="col">
                <?php echo text($str($subscriberPatient, 'firstName')); ?> <?php echo text($str($subscriberPatient, 'middleName')); ?> <?php echo text($str($subscriberPatient, 'lastOrganizationName')); ?> <?php echo text($str($subscriberPatient, 'suffix')); ?>
            </div>
            <div class="col">
                <strong><?php echo xlt("Member ID"); ?></strong>
            </div>
            <div class="col">
                <?php echo text($str($subscriberPatient, 'identifier')); ?>
            </div>
        </div>
    </div>

</div>

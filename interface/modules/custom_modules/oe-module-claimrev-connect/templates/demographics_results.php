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

/** @var \stdClass|null $demographicInfo set by the caller (individual->demographicInfo) */

declare(strict_types=1);

if ($demographicInfo === null) {
    echo xlt("No demographic results");
    return;
}

/**
 * @return string The string-typed property value or '' if missing.
 */
$str = static function (object $o, string $prop): string {
    if (!property_exists($o, $prop)) {
        return '';
    }
    $v = $o->$prop;
    return is_string($v) ? $v : '';
};
?>
<div class="card mb-2">
    <div class="card-header"><?php echo xlt("Demographics Results"); ?></div>
    <div class="card-body">
        <?php $status = $str($demographicInfo, 'status'); ?>
        <?php if ($status !== '') { ?>
            <div class="row mb-1">
                <div class="col-3 font-weight-bold"><?php echo xlt("Status"); ?>:</div>
                <div class="col"><?php echo text($status); ?></div>
            </div>
        <?php } ?>
        <?php $confidenceScore = property_exists($demographicInfo, 'confidenceScore') ? $demographicInfo->confidenceScore : null; ?>
        <?php if ($confidenceScore !== null && $confidenceScore !== '' && $confidenceScore !== 0) { ?>
            <div class="row mb-1">
                <div class="col-3 font-weight-bold"><?php echo xlt("Confidence Score"); ?>:</div>
                <div class="col"><?php echo text(\OpenEMR\Modules\ClaimRevConnector\TypeCoerce::asString($confidenceScore)); ?></div>
            </div>
        <?php } ?>

        <?php $person = property_exists($demographicInfo, 'correctedPerson') && is_object($demographicInfo->correctedPerson) ? $demographicInfo->correctedPerson : null; ?>
        <?php if ($person !== null) { ?>
            <h6 class="mt-3"><?php echo xlt("Verified Information"); ?></h6>
            <div class="row mb-1">
                <div class="col-3 font-weight-bold"><?php echo xlt("Name"); ?>:</div>
                <div class="col">
                    <?php echo text($str($person, 'firstName')); ?> <?php echo text($str($person, 'middleName')); ?> <?php echo text($str($person, 'lastName')); ?> <?php echo text($str($person, 'suffix')); ?>
                </div>
            </div>
            <?php $gender = $str($person, 'gender'); ?>
            <?php if ($gender !== '') { ?>
                <div class="row mb-1">
                    <div class="col-3 font-weight-bold"><?php echo xlt("Gender"); ?>:</div>
                    <div class="col"><?php echo text($gender); ?></div>
                </div>
            <?php } ?>
            <?php $dob = $str($person, 'dob'); ?>
            <?php if ($dob !== '') { ?>
                <div class="row mb-1">
                    <div class="col-3 font-weight-bold"><?php echo xlt("DOB"); ?>:</div>
                    <div class="col"><?php echo text(substr($dob, 0, 10)); ?></div>
                </div>
            <?php } ?>
            <?php $ssn = $str($person, 'ssn'); ?>
            <?php if ($ssn !== '') { ?>
                <div class="row mb-1">
                    <div class="col-3 font-weight-bold"><?php echo xlt("SSN"); ?>:</div>
                    <div class="col"><?php echo text($ssn); ?></div>
                </div>
            <?php } ?>
            <?php $address1 = $str($person, 'address1'); ?>
            <?php if ($address1 !== '') {
                $address2 = $str($person, 'address2');
                ?>
                <div class="row mb-1">
                    <div class="col-3 font-weight-bold"><?php echo xlt("Address"); ?>:</div>
                    <div class="col">
                        <?php echo text($address1); ?>
                        <?php if ($address2 !== '') {
                            echo ", " . text($address2);
                        } ?>
                        <br/><?php echo text($str($person, 'city')); ?>, <?php echo text($str($person, 'state')); ?> <?php echo text($str($person, 'zip')); ?>
                    </div>
                </div>
            <?php } ?>
            <?php $phoneNumber = $str($person, 'phoneNumber'); ?>
            <?php if ($phoneNumber !== '') { ?>
                <div class="row mb-1">
                    <div class="col-3 font-weight-bold"><?php echo xlt("Phone"); ?> (<?php echo text($str($person, 'phoneNumberType')); ?>):</div>
                    <div class="col"><?php echo text($phoneNumber); ?></div>
                </div>
            <?php } ?>
            <?php $deceased = property_exists($person, 'deceased') ? $person->deceased : false; ?>
            <?php if ($deceased) { ?>
                <div class="row mb-1">
                    <div class="col-3 font-weight-bold text-danger"><?php echo xlt("Deceased"); ?>:</div>
                    <div class="col text-danger"><?php echo xlt("Yes"); ?></div>
                </div>
            <?php } ?>
        <?php } ?>

        <?php $additionalAddresses = property_exists($demographicInfo, 'additionalAddresses') && is_iterable($demographicInfo->additionalAddresses) ? $demographicInfo->additionalAddresses : null; ?>
        <?php if ($additionalAddresses !== null) { ?>
            <h6 class="mt-3"><?php echo xlt("Additional Addresses"); ?></h6>
            <?php foreach ($additionalAddresses as $addr) {
                if (!is_object($addr)) {
                    continue;
                }
                $address2 = $str($addr, 'address2');
                $addressDateReported = $str($addr, 'addressDateReported');
                ?>
                <div class="row mb-1">
                    <div class="col">
                        <?php echo text($str($addr, 'address1')); ?>
                        <?php if ($address2 !== '') {
                            echo ", " . text($address2);
                        } ?>
                        , <?php echo text($str($addr, 'city')); ?>, <?php echo text($str($addr, 'state')); ?> <?php echo text($str($addr, 'zip')); ?>
                        <?php if ($addressDateReported !== '') { ?>
                            <small class="text-muted">(<?php echo text(substr($addressDateReported, 0, 10)); ?>)</small>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>

        <?php $warnings = property_exists($demographicInfo, 'warnings') && is_iterable($demographicInfo->warnings) ? $demographicInfo->warnings : null; ?>
        <?php if ($warnings !== null) { ?>
            <h6 class="mt-3 text-warning"><?php echo xlt("Warnings"); ?></h6>
            <ul class="mb-0">
            <?php foreach ($warnings as $warning) { ?>
                <li class="text-warning"><?php echo text(is_string($warning) ? $warning : ''); ?></li>
            <?php } ?>
            </ul>
        <?php } ?>

        <?php $redFlags = property_exists($demographicInfo, 'redFlags') && is_iterable($demographicInfo->redFlags) ? $demographicInfo->redFlags : null; ?>
        <?php if ($redFlags !== null) { ?>
            <h6 class="mt-3 text-danger"><?php echo xlt("Red Flags"); ?></h6>
            <ul class="mb-0">
            <?php foreach ($redFlags as $flag) { ?>
                <li class="text-danger"><?php echo text(is_string($flag) ? $flag : ''); ?></li>
            <?php } ?>
            </ul>
        <?php } ?>
    </div>
</div>

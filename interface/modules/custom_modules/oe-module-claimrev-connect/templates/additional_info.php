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

$additionalInfos = property_exists($benefit, 'benefitAdditionalInfos') && is_iterable($benefit->benefitAdditionalInfos) ? $benefit->benefitAdditionalInfos : null;
if ($additionalInfos === null) {
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
                    <h6><?php echo xlt("Eligibility or Benefit Additional Information"); ?></h6>
    <?php
    foreach ($additionalInfos as $ba) {
        if (!is_object($ba)) {
            continue;
        }
        $codeListQualifier = $str($ba, 'codeListQualifier');
        $messageText = $str($ba, 'messageText');
        if ($codeListQualifier !== '') {
            ?>
                            <dl class="row">
                                <dt class="col">
                    <?php echo xlt("Codes"); ?>

                                </dt>
                                <dd class="col">
                    <?php echo text($codeListQualifier); ?> <?php echo text($str($ba, 'industryCode')); ?> <?php echo text($str($ba, 'categoryCode')); ?>
                                </dd>
                            <dl>
            <?php
        }
        if ($messageText !== '') {
            ?>
                            <dl class="row">
                            <dt class="col">
                <?php echo xlt("Message"); ?>
                            </dt>
                            <dd class="col">
                <?php echo text($messageText); ?>
                            </dd>
                        <dl>
            <?php
        }
    }


    ?>
                </div>
            </div>
        </div>
    </div>

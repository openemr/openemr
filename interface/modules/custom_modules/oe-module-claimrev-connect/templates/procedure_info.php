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

$procedureInfo = property_exists($benefit, 'procedureInfo') && is_object($benefit->procedureInfo) ? $benefit->procedureInfo : null;
if ($procedureInfo === null) {
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
                    <h6><?php echo xlt("Procedure Information"); ?></h6>
                    <div class="row">
                        <div class="col">
                            <?php echo text($str($procedureInfo, 'serviceIdQualifier')); ?> : <?php echo text($str($procedureInfo, 'procedureCode')); ?>
                        </div>
                        <div class="col">
                            <ol>
                                <?php
                                foreach (['modifier1', 'modifier2', 'modifier3', 'modifier4'] as $field) {
                                    $v = $str($procedureInfo, $field);
                                    if ($v !== '') {
                                        echo "<li>" . text($v) . "</li>";
                                    }
                                }
                                ?>
                            </ol>
                        </div>
                        <div class="col">
                            <ol>
                                <?php
                                foreach (['pointer1', 'pointer2', 'pointer3', 'pointer4'] as $field) {
                                    $v = $str($procedureInfo, $field);
                                    if ($v !== '') {
                                        echo "<li>" . text($v) . "</li>";
                                    }
                                }
                                ?>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

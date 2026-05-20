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

$identifiers = property_exists($benefit, 'identifiers') && is_iterable($benefit->identifiers) ? $benefit->identifiers : null;
if ($identifiers === null) {
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
                    <h6><?php echo xlt("Identifiers"); ?></h6>
                    <div class="row">
                        <div class="col">
                            <ul>
                                <li>
                                    <?php
                                    foreach ($identifiers as $ident) {
                                        if (!is_object($ident)) {
                                            continue;
                                        }
                                        ?>
                                            <div class="row">
                                                <div class="col">
                                                <?php echo text($str($ident, 'referenceQualifierDesc')); ?>
                                                </div>
                                                <div class="col">
                                                <?php echo text($str($ident, 'referenceValue')); ?>
                                                </div>
                                                <div class="col">
                                                <?php echo text($str($ident, 'referenceDesc')); ?>
                                                </div>
                                            </div>

                                        <?php
                                    }
                                    ?>
                                </li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

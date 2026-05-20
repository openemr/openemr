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

$dates = property_exists($benefit, 'dates') && is_iterable($benefit->dates) ? $benefit->dates : null;
if ($dates === null) {
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
                        <h6> <?php echo xlt("Dates"); ?></h6>
                        <div class="row">
                            <div class="col">
                                <ul>
                                    <li>
    <?php
    foreach ($dates as $dtp) {
        if (!is_object($dtp)) {
            continue;
        }
        ?>
                                            <div class="row">
                                                <div class="col">
        <?php echo text($str($dtp, 'dateDescription')); ?>
                                                </div>
                                                <div class="col">
        <?php echo xlt("Start"); ?>: <?php echo text(substr($str($dtp, 'startDate'), 0, 10)); ?> <?php echo xlt("End"); ?>: <?php echo text(substr($str($dtp, 'endDate'), 0, 10)); ?>
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

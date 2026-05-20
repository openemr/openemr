<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/** @var \stdClass|null $source */

declare(strict_types=1);

if ($source === null) {
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
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"> <?php echo xlt("Payer Information"); ?></h5>
                <div class="row">
                    <div class="col">
                    <?php echo xlt("Payer Name"); ?>
                    </div>
                    <div class="col">
                    <?php echo text($str($source, 'lastOrganizationName')); ?>
                    </div>
                    <div class="col">
                    <?php echo xlt("Payer ID"); ?>
                    </div>
                    <div class="col">
                    <?php echo text($str($source, 'identifier')); ?>
                    </div>
                </div>
            </div>
        </div>

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

$messages = property_exists($benefit, 'messages') && is_iterable($benefit->messages) ? $benefit->messages : null;
if ($messages === null) {
    return;
}
?>
    <div class="row">
        <div class="col">
            <?php echo xlt("Messages"); ?>
        </div>
        <div class="col">
    <?php
    foreach ($messages as $message) {
        ?>
                <div class="row">
                    <div class="col">
                <?php echo text(is_string($message) ? $message : ''); ?>
                    </div>
                </div>
        <?php
    }
    ?>
        </div>
    </div>

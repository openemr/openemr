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

if ($benefit->messages != null && $benefit->messages) {
    ?>
    <div class="row">
        <div class="col">
            <?php echo xlt("Messages");?>            
        </div>
        <div class="col">
    <?php
    foreach ($benefit->messages as $message) {
        ?>
                <div class="row">
                    <div class="col">
                <?php echo text($message); ?>
                    </div>
                </div>
        <?php
    }
    ?>
        </div>
    </div>
    <?php
}
?>

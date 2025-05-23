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

if ($benefit->dates != null && $benefit->dates) {
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
    foreach ($benefit->dates as $dtp) {
        ?>
                                            <div class="row">
                                                <div class="col">
        <?php echo text($dtp->dateDescription) ?>
                                                </div>
                                                <div class="col">                        
        <?php echo xlt("Start"); ?>: <?php echo text(substr($dtp->startDate, 0, 10));  ?> <?php echo xlt("End"); ?>: <?php echo text(substr($dtp->endDate, 0, 10)); ?>
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
    <?php
}
?>

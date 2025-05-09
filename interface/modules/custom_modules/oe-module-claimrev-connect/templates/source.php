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

if ($source != null) {
    ?>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"> <?php echo xlt("Payer Information"); ?></h5>
                <div class="row"> 
                    <div class="col">
                    <?php echo xlt("Payer Name"); ?>                         
                    </div>
                    <div class="col">
                    <?php echo text($source->lastOrganizationName) ?>
                    </div>
                    <div class="col">
                    <?php echo xlt("Payer ID"); ?>
                    </div>
                    <div class="col">
                    <?php echo text($source->identifier) ?>
                    </div>
                </div>
            </div>
                
        </div>

    <?php
}
?>



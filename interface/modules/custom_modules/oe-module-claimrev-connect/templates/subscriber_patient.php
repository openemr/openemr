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

?>
<div class="card">
    <div class="card-body">
        <h5 class="card-title"><?php echo xlt("Subscriber/Patient Information"); ?></h5>
        <div class="row"> 
            <div class="col">
                <strong><?php echo xlt("Name"); ?></strong>
            </div>
            <div class="col">
                <?php echo text($subscriberPatient->firstName) ?> <?php echo text($subscriberPatient->middleName) ?> <?php echo text($subscriberPatient->lastOrganizationName) ?> <?php echo text($subscriberPatient->suffix) ?>
            </div>
            <div class="col">
                <strong><?php echo xlt("Member ID"); ?></strong>
            </div>
            <div class="col">
                <?php echo text($subscriberPatient->identifier) ?>
            </div>
        </div>
    </div>
        
</div>

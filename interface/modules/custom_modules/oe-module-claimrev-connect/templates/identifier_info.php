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

if ($benefit->identifiers != null && $benefit->identifiers) {
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
                                    foreach ($benefit->identifiers as $ident) {
                                        ?>
                                            <div class="row">
                                                <div class="col">
                                                <?php echo text($ident->referenceQualifierDesc) ?>
                                                </div>
                                                <div class="col">                                                
                                                <?php echo text($ident->referenceValue) ?>
                                                </div>
                                                <div class="col">                                                
                                                <?php echo text($ident->referenceDesc) ?>
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

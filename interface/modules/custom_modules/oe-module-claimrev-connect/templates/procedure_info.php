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

if ($benefit->procedureInfo != null) {
    $procedureInfo = $benefit->procedureInfo;
    ?>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h6><?php echo xlt("Procedure Information"); ?></h6>
                    <div class="row">
                        <div class="col">
                            <?php echo text($procedureInfo->serviceIdQualifier); ?> : <?php echo text($procedureInfo->procedureCode); ?>
                        </div>
                        <div class="col">
                            <ol>
                                <?php
                                if ($procedureInfo->modifier1 != "") {
                                    echo("<li>" . text($procedureInfo->modifier1) . "</li>");
                                }
                                if ($procedureInfo->modifier2 != "") {
                                    echo("<li>" . text($procedureInfo->modifier2) . "</li>");
                                }
                                if ($procedureInfo->modifier3 != "") {
                                    echo("<li>" . text($procedureInfo->modifier3) . "</li>");
                                }
                                if ($procedureInfo->modifier4 != "") {
                                    echo("<li>" . text($procedureInfo->modifier4) . "</li>");
                                }
                                ?>
                            </ol>
                        </div>
                        <div class="col">
                            <ol>
                                <?php
                                if ($procedureInfo->pointer1 != "") {
                                    echo("<li>" . text($procedureInfo->pointer1) . "</li>");
                                }
                                if ($procedureInfo->pointer2 != "") {
                                    echo("<li>" . text($procedureInfo->pointer2) . "</li>");
                                }
                                if ($procedureInfo->pointer3 != "") {
                                    echo("<li>" . text($procedureInfo->pointer3) . "</li>");
                                }
                                if ($procedureInfo->pointer4 != "") {
                                    echo("<li>" . text($procedureInfo->pointer4) . "</li>");
                                }
                                ?>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>

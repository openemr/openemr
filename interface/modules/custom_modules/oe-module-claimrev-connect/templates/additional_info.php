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

if ($benefit->benefitAdditionalInfos != null && $benefit->benefitAdditionalInfos) {
    ?>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h6><?php echo xlt("Eligibility or Benefit Additional Information"); ?></h6>
    <?php
    foreach ($benefit->benefitAdditionalInfos as $ba) {
        if ($ba->codeListQualifier != "") {
            ?>
                            <dl class="row">
                                <dt class="col">
                    <?php echo xlt("Codes"); ?>
                                    
                                </dt>
                                <dd class="col">
                    <?php echo text($ba->codeListQualifier);?> <?php echo text($ba->industryCode);?> <?php echo text($ba->categoryCode); ?>
                                </dd>
                            <dl>
            <?php
        }
        if ($ba->messageText != "") {
            ?>
                            <dl class="row">
                            <dt class="col">
                <?php echo xlt("Message"); ?>                                
                            </dt>
                            <dd class="col">
                <?php echo text($ba->messageText);?>                                              
                            </dd>
                        <dl>
            <?php
        }
    }


    ?>
                </div>
            </div>
        </div>
    </div>

    <?php
}


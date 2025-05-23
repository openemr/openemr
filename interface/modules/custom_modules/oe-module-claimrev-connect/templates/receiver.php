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

if ($receiver != null) {
     $companyProviderCaption = "Company Name";
     $companyProviderName = $receiver->lastOrganizationName;
    if ($receiver->entityIdentifierCodeQualifier == "1") {
         $companyProviderCaption = "Provider Name";
         $companyProviderName = $receiver->firstName . " " . $receiver->middleName . " " .  $receiver->lastOrganizationName . " " . $receiver->suffix;
    }
    ?>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><?php echo xlt("Receiver Information"); ?></h5>
            <div class="row"> 
                <div class="col">
                    <?php echo xlt($companyProviderCaption); ?>                        
                </div>
                <div class="col">
                    <?php echo text($companyProviderName) ?>
                </div>
                <div class="col">
                    <?php echo xlt("ID"); ?>                        
                </div>
                <div class="col">
                    <?php echo text($receiver->identifier) ?>
                </div>
            </div>
        </div>
    </div>
     <?php
}
?>
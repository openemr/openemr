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

if ($benefit->relatedEntities != null && $benefit->relatedEntities) {
    ?>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h6> <?php echo xlt("Related Entity"); ?></h6>
    <?php
    foreach ($benefit->relatedEntities as $relatedEntity) {
        if ($relatedEntity->entityIdentifierCodeQualifier == "2") {
            ?>
                            <dl class="row">
                                <dt class="col">
                    <?php echo xlt("Organization Name"); ?>
                                </dt>
                                <dd class="col">
                    <?php echo text($relatedEntity->lastOrganizationName);?>
                                </dd>
                            <dl>
            <?php
        }
        if ($relatedEntity->entityIdentifierCodeQualifier == "1") {
            ?>
                            <dl class="row">
                                <dt class="col">
                    <?php echo xlt("Name"); ?>                                  
                                </dt>
                                <dd class="col">
                    <?php echo text($relatedEntity->firstName);?> <?php echo text($relatedEntity->middleName);?> <?php echo text($relatedEntity->lastOrganizationName);?> <?php echo text($relatedEntity->suffix);?>                                     
                                </dd>
                            <dl>
            <?php
        }
        if ($relatedEntity->entityIdentifierCodeQualifier == "1") {
            ?>
                            <dl class="row">
                                <dt class="col">
                                    
                                </dt>
                                <dd class="col">
                    <?php echo text($relatedEntity->identifier);?>                                     
                                </dd>
                            <dl>
            <?php
        }
        ?>
                    <dl class="row">
                        <dt class="col">
            <?php echo xlt("Address"); ?>                                
                        </dt>
                        <dd class="col">
                            <div class="row">
                                <div class="col">
                    <?php echo text($relatedEntity->address->address1);?>  
                                </div>
                            </div> 
                            <div class="row">
                                <div class="col">
                    <?php echo text($relatedEntity->address->address2);?>  
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                    <?php echo text($relatedEntity->address->city);?>  
                                </div>
                                <div class="col">
                    <?php echo text($relatedEntity->address->state);?>  
                                </div>
                                <div class="col">
                    <?php echo text($relatedEntity->address->zip);?>  
                                </div>
                            </div>
                        </dd>
                    </dl>
        <?php
        if ($relatedEntity->taxonomyCode != "") {
            ?>
                            <dl class="row">
                                <dt class="col">
                    <?php echo xlt("Taxonomy Code"); ?>  
                                    
                                </dt>
                                <dd class="col">
                                    (<?php echo text($relatedEntity->taxonomyProviderCode);?>) <?php echo text($relatedEntity->taxonomyCode);?>                                        
                                </dd>
                            <dl>
            <?php
        }
        if ($relatedEntity->contacts != null && $relatedEntity->contacts) {
            foreach ($relatedEntity->contacts as $c) {
                ?>
                                <dl class="row">                                    
                                    <dt class="col">
                        <?php echo xlt("Contact Name"); ?>                                           
                                    </dt>
                                    <dt class="col">
                        <?php echo text($c->contactName); ?>
                <?php
                foreach ($c->contactMethods as $m) {
                    ?>
                                                <dl class="row">
                                                    <dt class="col">
                                <?php echo text($c->contactType); ?>
                                                    </dt>
                                                    <dt class="col">
                                <?php echo text($c->contactValue); ?>
                                                    </dt>
                                                </dl>  
                    <?php
                }
                ?>
  
                                    
                                    </dt>
                                </dl>
                <?php
            }
        }
    }//end foreach


    ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>

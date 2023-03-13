<?php
if($benefit->relatedEntities != null && $benefit->relatedEntities )
{
?>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h6>Related Entity</h6>
<?php
                    foreach($benefit->relatedEntities as $relatedEntity)
                    {
                        if($relatedEntity->entityIdentifierCodeQualifier == "2")
                        {
?>
                            <dl class="row">
                                <dt class="col">
                                    Organization Name
                                </dt>
                                <dd class="col">
                                    <?php echo($relatedEntity->lastOrganizationName);?>                                             
                                </dd>
                            <dl>
<?php
                        }
                        if($relatedEntity->entityIdentifierCodeQualifier == "1")
                        {
?>
                            <dl class="row">
                                <dt class="col">
                                Name
                                </dt>
                                <dd class="col">
                                    <?php echo($relatedEntity->firstName);?> <?php echo($relatedEntity->middleName);?> <?php echo($relatedEntity->lastOrganizationName);?> <?php echo($relatedEntity->suffix);?>                                     
                                </dd>
                            <dl>
<?php
                        }
                        if($relatedEntity->entityIdentifierCodeQualifier == "1")
                        {
?>
                            <dl class="row">
                                <dt class="col">
                                    
                                </dt>
                                <dd class="col">
                                    <?php echo($relatedEntity->identifier);?>                                     
                                </dd>
                            <dl>
<?php
                        }
?>
                    <dl class="row">
                        <dt class="col">
                            Address
                        </dt>
                        <dd class="col">
                            <div class="row">
                                <div class="col">
                                    <?php echo($relatedEntity->address->address1);?>  
                                </div>
                            </div> 
                            <div class="row">
                                <div class="col">
                                    <?php echo($relatedEntity->address->address2);?>  
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <?php echo($relatedEntity->address->city);?>  
                                </div>
                                <div class="col">
                                    <?php echo($relatedEntity->address->state);?>  
                                </div>
                                <div class="col">
                                    <?php echo($relatedEntity->address->zip);?>  
                                </div>
                            </div>
                        </dd>
                    </dl>
<?php
                        if($relatedEntity->taxonomyCode != "")
                        {
?>
                            <dl class="row">
                                <dt class="col">
                                    Taxonomy Code
                                </dt>
                                <dd class="col">
                                    (<?php echo($relatedEntity->taxonomyProviderCode);?>) <?php echo($relatedEntity->taxonomyCode);?>                                        
                                </dd>
                            <dl>
<?php
                        }
                        if($relatedEntity->contacts != null && $relatedEntity->contacts )
                        {
                            foreach($relatedEntity->contacts as $c)
                            {
?>
                                <dl class="row">                                    
                                    <dt class="col">
                                        Contact Name
                                    </dt>
                                    <dt class="col">
                                        <?php echo($c->contactName); ?>
<?php 
                                            foreach($c->contactMethods as $m)
                                            {
?>
                                                <dl class="row">
                                                    <dt class="col">
                                                        <?php echo($c->contactType); ?>
                                                    </dt>
                                                    <dt class="col">
                                                        <?php echo($c->contactValue); ?>
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
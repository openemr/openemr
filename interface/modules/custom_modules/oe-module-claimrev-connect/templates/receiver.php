       
<?php
    
if($receiver != null)
{
    if($receiver->entityIdentifierCodeQualifier == "2")
    {
?>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Receiver Information</h5>
                <div class="row"> 
                    <div class="col">
                        Company Name
                    </div>
                    <div class="col">
                        <?php echo($receiver->lastOrganizationName) ?>
                    </div>
                    <div class="col">
                        ID
                    </div>
                    <div class="col">
                        <?php echo($receiver->identifier) ?>
                    </div>
                </div>
            </div>
        </div>
<?php
    }
    else if ($receiver->entityIdentifierCodeQualifier == "1")
    {
?>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Receiver Information</h5>
                <div class="row"> 
                    <div class="col">
                        Provider Name
                    </div>
                    <div class="col">
                        <?php echo($receiver->firstName) ?> <?php echo($receiver->middleName) ?> <?php echo($receiver->lastOrganizationName) ?> <?php echo($receiver->suffix) ?>
                    </div>
                    <div class="col">
                        ID
                    </div>
                    <div class="col">
                        <?php echo($receiver->identifier) ?>
                    </div>
                </div>
            </div>
        </div>
<?php
    }
}
?>

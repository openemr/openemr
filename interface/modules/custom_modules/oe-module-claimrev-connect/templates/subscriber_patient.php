<div class="card">
    <div class="card-body">
        <h5 class="card-title">Subscriber/Patient Information</h5>
        <div class="row"> 
            <div class="col">
                <strong>Name</strong>
            </div>
            <div class="col">
            <?php echo($subscriberPatient->firstName) ?> <?php echo($subscriberPatient->middleName) ?> <?php echo($subscriberPatient->lastOrganizationName) ?> <?php echo($subscriberPatient->suffix) ?>
            </div>
            <div class="col">
                <strong>Member ID</strong>
                
            </div>
            <div class="col">
                <?php echo($subscriberPatient->identifier) ?>
            </div>
        </div>
    </div>
        
</div>
<?php
if($benefit->benefitAdditionalInfos != null && $benefit->benefitAdditionalInfos )
{
?>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h6>Eligibility or Benefit Additional Information</h6>
                <?php
                    foreach($benefit->benefitAdditionalInfos as $ba)
                    {
                        if($ba->codeListQualifier != "")
                        {
                ?>
                            <dl class="row">
                                <dt class="col">
                                    Codes
                                </dt>
                                <dd class="col">
                                    <?php echo($ba->codeListQualifier);?> <?php echo($ba->industryCode);?> <?php echo($ba->categoryCode); ?>                                                
                                </dd>
                            <dl>
                <?php
                        }
                        if($ba->messageText != "")
                        {
                ?>
                            <dl class="row">
                            <dt class="col">
                                Message
                            </dt>
                            <dd class="col">
                                <?php echo($ba->messageText);?>                                              
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
?>
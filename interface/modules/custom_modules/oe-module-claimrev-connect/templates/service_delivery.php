<?php 
if($benefit->serviceDeliveries != null && $benefit->serviceDeliveries )
{
    ?>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h6>Services Delivery</h6>
                    <ul>
                        <?php
                            foreach($benefit->serviceDeliveries as $serviceDelivery)
                            {
                                if($serviceDelivery->benefitQuantity != "")
                                {
                        ?>
                                    <li>
                                <?php  if($serviceDelivery->quantityQualifier != '')
                                        {
                                ?>
                                            <span> <?php echo($serviceDelivery->benefitQuantity); ?></span>  <span><?php  echo($serviceDelivery->quantityQualifierDesc);  ?></span>
                                <?php  }
                                        if($serviceDelivery->sampleSelectionModulus != '')
                                        {
                                ?>
                                            <?php echo($serviceDelivery->sampleSelectionModulus); ?>
                                            <span> <?php echo($serviceDelivery->quantityQualifierDesc); ?></span> per <span><?php  echo($serviceDelivery->measurementCodeDesc);  ?></span>
                                <?php   }
                                         echo($serviceDelivery->periodCount); ?> <?php echo($serviceDelivery->timePeriodDesc); 
                                        if($serviceDelivery->frequencyCode != '')
                                        {
                                            if($serviceDelivery->FrequencyCodeDesc != '')
                                            {
                                ?>
                                                <span> <?php echo($serviceDelivery->FrequencyCodeDesc); ?></span> <span><?php  echo($serviceDelivery->patternTimeCodeDesc); ?> </span>
                                <?php       }                                           
                                        }                                  
                                ?>
                                        
                                    </li>
                            <?php
                                }
                            
                        ?>
                               
                        <?php
                            }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>
<?php
$validations = null;
$noValidations = true;
 if (property_exists($data, 'requestValidations'))
 {                                                
    $validations = $data->requestValidations;
    printValidation("Primary Validations",$validations );
 }  
 if (property_exists($data, 'informationSourceName'))
 {    
    $informationSourceName = $data->informationSourceName;    

    if ($informationSourceName != null && property_exists($informationSourceName, 'requestValidations'))
    {                                                
       $validations = $informationSourceName->requestValidations;
       printValidation("information Source Validations",$validations );
    } 
 }  

 if (property_exists($data, 'receiver'))
 {    
    $receiver = $data->receiver;                                            
    if ($receiver != null && property_exists($receiver, 'requestValidations'))
    {                                                
       $validations = $receiver->requestValidations;
       printValidation("Receiver Validations",$validations );
    } 
 }  

 if (property_exists($data, 'subscriber'))
 {    
    $subscriber = $data->subscriber;                                            
    if ($subscriber != null && property_exists($subscriber, 'requestValidations'))
    {                                                
       $validations = $subscriber->requestValidations;
       printValidation("Receiver Validations",$validations );
    } 
 }  
        
    
    if($noValidations == true) 
    {
?>
    <div class="row">
        <div class="col">
            No Validations
        </div>
    </div>
<?php
    }
?>


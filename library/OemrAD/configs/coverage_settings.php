<?php

//module configurations

//If in following array then pass provider tax id as a additional parameter.
//ex. array('12345','334455');
$GLOBALS['req_taxidforinsurance'] = array();

//If Insurance is found in this array use the provider mentioned in the combination. 
//First part is payerid & second part is provider id. (user id)
//ex. array('XYZ' => '5');
$GLOBALS['req_provider_payer'] = array();
<?php

require_once("../globals.php");

use OpenEMR\USPS\USPSAddress;
use OpenEMR\USPS\USPSAddressVerify;

// Initiate and set the username provided from usps
$verify = new USPSAddressVerify($GLOBALS['usps_webtools_username']);

// Create new address object and assign the properties
// apparently the order you assign them is important so make sure
// to set them as the example below
$address = new USPSAddress();
//$address->setFirmName('Apartment');
$address->setApt($_GET['address2']);
$address->setAddress($_GET['address1']);
$address->setCity($_GET['city']);
$address->setState($_GET['state']);
$address->setZip5($_GET['zip5']);
$address->setZip4($_GET['zip4']);

//print_r($address);

// Add the address object to the address verify class
$verify->addAddress($address);

// Perform the request and return resultFset
//print_r($verify->verify());
$verify->verify();

$response_array = $verify->getArrayResponse();

//var_dump($verify->isError());

// See if it was successful
if($verify->isSuccess()) {
    $output = '<!DOCTYPE html>
    <html>
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    $address_details = $response_array['AddressValidateResponse']['Address'];
    // remove attributes array at end of response address array
    $removed = array_pop($address_details);
    foreach ($address_details as $key => $value) {
        $output .= text($key) . ": " . text($value) . "</br>";
    }
    //$output = var_dump($response_array);
} else {
  $output = 'Error: ' . $verify->getErrorMessage();
}

echo $output;
<?php

/*
 * Used in demographics edit to check address with USPS Web API
 * originally under MIT License
 * https://packagist.org/packages/binarydata/usps-php-api
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Vincent Gabriel
 * @author    stephen waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2012 Vincent Gabriel
 * @copyright Copyright (c) 2022 stephen waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Core\Header;
use OpenEMR\USPS\USPSAddress;
use OpenEMR\USPS\USPSAddressVerify;

// set up USPS API (v3 if client credentials exist, otherwise legacy)
$verify = new USPSAddressVerify(
    $GLOBALS['usps_webtools_username'] ?? '',
    $GLOBALS['usps_webtools_client_id'] ?? '',
    $GLOBALS['usps_webtools_client_secret'] ?? ''
);

// Create new address object and assign the properties
// apparently the order you assign them is important so make sure
// to set them as the example below
$address = new USPSAddress();
//$address->setFirmName('Apartment');
$address->setApt($_GET['address1']);
$address->setAddress($_GET['address2']);
$address->setCity($_GET['city']);
$address->setState($_GET['state']);
$address->setZip5($_GET['zip5']);
$address->setZip4($_GET['zip4']);

//print_r($address);

// Add the address object to the address verify class
$verify->addAddress($address);

$output = '<!DOCTYPE html><html>';
$output .= Header::setupHeader([], false);
$output .= "<body class='text-left'>
   <div class='container'>
       <p>";

try {
    // Perform the request
    $verify->verify();
    $response_array = $verify->getArrayResponse();

    if ($verify->isSuccess()) {
        // check response format (v3 JSON vs legacy XML)
        if (isset($response_array['address'])) {
            // v3 format
            $address_data = $response_array['address'];
            $address_array = [
                'Address1' => $address_data['secondaryAddress'] ?? '',
                'Address2' => $address_data['streetAddress'] ?? '',
                'City' => $address_data['city'] ?? '',
                'State' => $address_data['state'] ?? '',
                'Zip5' => $address_data['ZIPCode'] ?? '',
                'Zip4' => $address_data['ZIPPlus4'] ?? '',
            ];
        } else {
            // legacy format
            $address_array = $response_array['AddressValidateResponse']['Address'];

            // remove attributes array at end of response address array
            $removed = array_pop($address_array);

            // usps Address1 is for apt/suite so need to handle their special response
            if (!array_key_exists('Address1', $address_array)) {
                $address_array['Address1'] = $address_array['Address2'];
                $address_array['Address2'] = '';
            }
        }

        ksort($address_array);

        foreach ($address_array as $key => $value) {
            if (($_GET[strtolower((string) $key)] ?? null) != $value) {
                $output .= "<div class='text-danger'>";
            } else {
                $output .= "<div class='text-success'>";
            }
            $output .= text($key) . ": " . text($value) . "</div>";
        }
        //$output = var_dump($response_array);
    } else {
        $output .= "<div class='text-danger'>";
        $output .= 'Error: ' . text($verify->getErrorMessage())  . "</div>";
    }
} catch (\Exception $e) {
    $output .= "<div class='text-danger'>";
    $output .= 'Error: ' . text($e->getMessage()) . "</div>";
}

$output .= "</div></body></html>";

echo $output;

<?php

/**
 * Used in demographics edit to check address with USPS Web API v3
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Vincent Gabriel
 * @author    stephen waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2012 Vincent Gabriel
 * @copyright Copyright (c) 2022-2025 stephen waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Core\Header;
use OpenEMR\USPS\USPSAddressVerifyV3;

$verify = new USPSAddressVerifyV3();

$output = '<!DOCTYPE html><html>';
$output .= Header::setupHeader([], false);
$output .= "<body class='text-left'>
   <div class='container'>
       <p>";

if (!$verify->isConfigured()) {
    $output .= "<div class='text-danger'>";
    $output .= text("USPS API v3 credentials not configured. Please set Client ID and Client Secret in Administration > Globals > Connectors.");
    $output .= "</div>";
} else {
    // address1 = primary street, address2 = apt/suite (from JS form mapping)
    $success = $verify->verify(
        $_GET['address1'] ?? '',      // streetAddress (primary)
        $_GET['address2'] ?? '',      // secondaryAddress (apt/suite)
        $_GET['city'] ?? '',
        $_GET['state'] ?? '',
        $_GET['zip5'] ?? '',
        $_GET['zip4'] ?? ''
    );

    if ($success) {
        $address = $verify->getAddress();

        if ($address) {
            // Map input to v3 response field names for comparison
            $inputMap = [
                'streetAddress' => $_GET['address1'] ?? '',
                'secondaryAddress' => $_GET['address2'] ?? '',
                'city' => $_GET['city'] ?? '',
                'state' => $_GET['state'] ?? '',
                'ZIPCode' => $_GET['zip5'] ?? '',
                'ZIPPlus4' => $_GET['zip4'] ?? ''
            ];

            foreach ($address as $key => $value) {
                $inputValue = $inputMap[$key] ?? '';
                if (strcasecmp((string) $inputValue, (string) $value) !== 0) {
                    $output .= "<div class='text-danger'>";
                } else {
                    $output .= "<div class='text-success'>";
                }
                $output .= text($key) . ": " . text($value) . "</div>";
            }
        }
    } else {
        $output .= "<div class='text-danger'>";
        $output .= 'Error: ' . text($verify->getErrorMessage()) . "</div>";
    }
}

$output .= "</div></body></html>";

echo $output;

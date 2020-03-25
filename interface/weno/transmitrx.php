<?php
/**
 * File use to transmit Rx
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2019 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
require_once "../globals.php";

use OpenEMR\Rx\Weno\NewRx;

if (isset($_GET['scripts'])) {
    $list = filter_input(INPUT_GET, 'scripts', FILTER_VALIDATE_INT);
} else {
    echo xlt("Error: No prescriptions sent");
    exit;
}

$meds = explode(",", $list);

/**
 * This look is to package the XML and bundle the prescriptions togther
 */
foreach ($meds as $med) {
    $sendScript = new NewRx();
    $payloads = '';
    //Returns XML message to be transmitted to gateway
    $payload = $sendScript->creatOrderXMLBody($med);
    //These colons were added to have a way to separate the presciptions on the endpoint server
    $payloads .= $payload.":::";
}

/**
 * determine which gateway to transmit script
 */
if ($GLOBALS['weno_rx_testing'] == 1) {
    $request_url = 'https://apa.openmedpractice.com/apa/interface/weno/receivingrx_test_v2';
} else {
    $request_url = 'https://apa.openmedpractice.com/apa/interface/weno/receivingrx_v2';
}

$response = get_url($request_url, $payloads);

//Transmit RX payload to gateway

function get_url($request_url, $payloads)
{
    $headers = [
        "Access-Control-Allow-Origin: *",
        "Content-type: text/xml",
    ];
    //I used this during the build process. It can be turned into a log system
    //file_put_contents("payload/".rand()."-payload.xml", $payloads);
    $data = ['xml' => $payloads];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $request_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch) or die(curl_error($ch));

    if (curl_errno($ch)) {
        print curl_error($ch);
    } else {
        curl_close($ch);
    }

    return $response;
}

<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 3/4/2019
 * Time: 1:42 PM
 */

require_once ("../../../interface/globals.php");
use OpenEMR\Common\Http\oeHttp;
$npi = 1760767016;

$headers = array(
    'Content-Type' => "application/json"
);
$query = [
    'number' => $npi,
    'enumeration_type' => '',
    'taxonomy_description' => '',
    'first_name' => '',
    'last_name' => '',
    'organization_name'  => '',
    'address_purpose' => '',
    'city' => '',
    'state' => '',
    'postal_code' => '',
    'country_code' => '',
    'limit' => '',
    'skip' => '',
    'version' => '2.0',
    ];
$response = oeHttp::bodyFormat('body')->get('https://npiregistry.cms.hhs.gov/api/', $query);

$body = $response->body(); // already should be json.

$validated = json_decode($body);

echo $validated->result_count;

var_dump($validated);


$npi = 1760767016;
$url = "https://npiregistry.cms.hhs.gov/api/?number=$npi&enumeration_type=&taxonomy_description=&first_name=&last_name=&organization_name=&address_purpose=&city=&state=&postal_code=&country_code=&limit=&skip=&version=2.0";
$json = file_get_contents($url);
$response = json_decode($json);

echo $response->result_count;
var_dump($response);
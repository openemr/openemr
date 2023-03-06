<?php

require_once("../globals.php");

$getUrl = "http://api.quickemailverification.com/v1/verify?email=".$_GET['email']."&apikey=".$GLOBALS['email_verification_api'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_URL, $getUrl);
curl_setopt($ch, CURLOPT_TIMEOUT, 80);
 
$response = curl_exec($ch);
 
if(curl_error($ch)){
	echo json_encode(array(
		"success" =>"false",
    	"message" => "Something went wrong"
	));
} else {
	echo json_encode($response, true);
}
 
curl_close($ch);
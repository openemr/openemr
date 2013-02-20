<?php

header("Content-Type:text/xml");
require_once 'includes/class.database.php';
require_once 'includes/functions.php';
require_once 'includes/class.arraytoxml.php';

$xml_array = array();

$token = $_GET['token'];

if ($id = validateToken($token)) {
    $query = "SELECT * FROM `medmasterusers` WHERE `id` = {$id}";
    $db->query($query);

    $xml_array['user'] = get_object_vars($db->last_result[0]);
    
    unset ($xml_array['user']['password']);
    unset ($xml_array['user']['pin']);
    
    $xml_array['status'] = 1;
} else {

    $xml_array['status'] = 1;
    $xml_array['reason'] = 'Invalid Token';
}

$xml = ArrayToXML::toXml($xml_array, 'MedMasterUser');

echo $xml;
?>
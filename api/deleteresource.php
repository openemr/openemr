<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$xml_string = "";
$xml_string = "<Resource>";

$token = $_POST['token'];
$option_id = $_POST['option_id'];
$list_id = 'ExternalResources';

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('admin', 'super', $user);
    if ($acl_allow) {


        $strQuery1 = "SELECT notes
                    FROM `list_options`
                    WHERE `list_id` LIKE '{$list_id}' AND 
                    `option_id` LIKE '{$option_id}'";
        $result1 = $db->get_results($strQuery1);

        $file_path = $result1[0]->notes;

        $temp_path = explode("userdata", $file_path);
        $relative_path = $sitesDir . "{$site}/documents/userdata" . $temp_path[1];


        if (file_exists($relative_path)) {
            unlink($relative_path);
        }

        $thumb_name = end(explode("/", $temp_path[1]));
        $relative_path_thumb = $sitesDir . "{$site}/documents/userdata/images/thumb/" . $thumb_name;

        if (file_exists($relative_path_thumb)) {
            unlink($relative_path_thumb);
        }

        $strQuery = "DELETE FROM `list_options` WHERE `list_id` LIKE '{$list_id}' AND 
                    `option_id` LIKE '{$option_id}'";


        $result = $db->query($strQuery);



        if ($result) {
            newEvent($event = 'resourse-record-select', $user, $groupname = 'Default', $success = '1', $comments = $strQuery1);
            newEvent($event = 'resourse-record-delete', $user, $groupname = 'Default', $success = '1', $comments = $strQuery);

            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Resource has been deleted</reason>";
        } else {
            $xml_string .= "<status>-1</status>";
            $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>";
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</Resource>";
echo $xml_string;
?>
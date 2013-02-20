<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$xml_string = "";
$xml_string = "<Message>";

$token = $_POST['token'];
$patientId = $_POST['patientId'];
$authorized = $_POST['authorized'] ? $_POST['authorized'] : 1;
$activity = $_POST['activity'] ? $_POST['activity'] : 1;
$title = $_POST['title'];
$newtext = $_POST['newtext'];
$assigned_to = $_POST['assigned_to'];
$message_status = $_POST['message_status'];

$message_id = isset($_POST['message_id']) && !empty($_POST['message_id']) ? $_POST['message_id'] : '';


if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('patients', 'notes', $user);

    if ($acl_allow) {
    $provider_id = getUserProviderId($userId);

    $assigned_to_array = explode(',', $assigned_to);
    $_SESSION['authUser'] = $user;
    $_SESSION['authProvider'] = 'Default';

    foreach ($assigned_to_array as $assignee) {
        if ($message_status == 'Done' && !empty($message_id)) {
            updatePnoteMessageStatus($message_id, $message_status);
            $result = 1;
            break;
        } else {
            $result = addPnote($patientId, $newtext, $authorized, $activity, $title, $assignee, $datetime = '', $message_status);
            $device_token_badge = getDeviceTokenBadge($assignee,'message');
            $badge = $device_token_badge ['badge'];
            $deviceToken = $device_token_badge ['device_token'];
            if ($deviceToken) {
                $notification_res = notification($deviceToken, $badge, $msg_count = 0, $apt_count = 0, $message = 'New Message Notification!');
            }
        }
    }
    if ($result) {
        $xml_string .= "<status>0</status>";
        $xml_string .= "<reason>Message send successfully</reason>";
        if ($notification_res) {
            $xml_string .= "<notification>Notification({$notification_res}) Sent.</notification>";
        } else {
            $xml_string .= "<notification>Notification Failed.</notification>";
        }
    } else {
        $xml_string .= "<status>-1</status>";
        $xml_string .= "<reason>Could not send message</reason>";
    }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</Message>";
echo $xml_string;
?>
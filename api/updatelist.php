<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once ("classes.php");

$token = $_POST['token'];

$id = $_POST['id'];

$title = isset($_POST['title']) ? $_POST['title'] : '';
$begdate = isset($_POST['begdate']) ? $_POST['begdate'] : '';
$enddate = isset($_POST['enddate']) ? $_POST['enddate'] : '';
$returndate = isset($_POST['returndate']) ? $_POST['returndate'] : '';
$occurrence = isset($_POST['occurrence']) ? $_POST['occurrence'] : '';
$classification = isset($_POST['classification']) ? $_POST['classification'] : '0';
$referredby = isset($_POST['referredby']) ? $_POST['referredby'] : '';
$extrainfo = isset($_POST['extrainfo']) ? $_POST['extrainfo'] : '';
$diagnosis = isset($_POST['diagnosis']) ? $_POST['diagnosis'] : '';
$activity = isset($_POST['activity']) ? $_POST['activity'] : '1';
$comments = isset($_POST['comments']) ? $_POST['comments'] : '';
$pid = $_POST['pid'];
$user = '';
$groupname = isset($_POST['groupname']) ? $_POST['groupname'] : '';

$outcome = $_POST['outcome'];
$destination = $_POST['destination'];

$reinjury_id = isset($_POST['reinjury_id']) ? $_POST['reinjury_id'] : '0';
$injury_part = isset($_POST['injury_part']) ? $_POST['injury_part'] : '';
$injury_type = isset($_POST['injury_type']) ? $_POST['injury_type'] : '';
$injury_grade = isset($_POST['injury_grade']) ? $_POST['injury_grade'] : '';
$reaction = isset($_POST['reaction']) ? $_POST['reaction'] : '';
$external_allergyid = isset($_POST['external_allergyid']) ? $_POST['external_allergyid'] : '';
$erx_source = isset($_POST['erx_source']) ? $_POST['erx_source'] : 0;
$erx_uploaded = isset($_POST['erx_uploaded']) ? $_POST['erx_uploaded'] : 0;

$xml_string = "";
$xml_string = "<list>";

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('patients', 'med', $user);

    $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;
    $_SESSION['pid'] = $pid;
    
    if ($acl_allow) {
        $strQuery = "UPDATE `lists` SET 
                                `title`='{$title}',
                                `begdate`='{$begdate}',
                                `enddate`='{$enddate}',
                                `returndate`='{$returndate}',
                                `occurrence`='{$occurrence}',
                                `classification`='{$classification}',
                                `referredby`='{$classification}',
                                `extrainfo`='{$extrainfo}',
                                `diagnosis`='{$diagnosis}',
                                `activity`='{$activity}',
                                `comments`='{$comments}',
                                `user`='{$user}',
                                `groupname`='{$groupname}',
                                `outcome`='{$outcome}',
                                `destination`='{$destination}',
                                `reinjury_id`='{$reinjury_id}',
                                `injury_part`='{$injury_part}',
                                `injury_type`='{$injury_type}',
                                `injury_grade`='{$injury_grade}',
                                `reaction`='{$reaction}',
                                `external_allergyid`='{$external_allergyid}',
                                `erx_source`='{$erx_source}',
                                `erx_uploaded`='{$error_string}' 
                       WHERE id = " . $id;

        $result = sqlStatement($strQuery);

        if ($result) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The {$type} has been update</reason>";
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

$xml_string .= "</list>";
echo $xml_string;
?>
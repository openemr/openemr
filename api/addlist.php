<?php
/**
 * api/addlist.php add patient's problem.
 *
 * Api add's patient problem such as allergies,medical problem, Medicaions,
 * Dental, Surgery
 * 
 * Copyright (C) 2012 Karl Englund <karl@mastermobileproducts.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-3.0.html>;.
 *
 * @package OpenEMR
 * @author  Karl Englund <karl@mastermobileproducts.com>
 * @link    http://www.open-emr.org
 */
header("Content-Type:text/xml");
$ignoreAuth = true;
require_once ("classes.php");

$token = $_POST['token'];
$date = 'NOW()';
$type = $_POST['type']; // medical_problem
$visit_id = $_POST['visit_id'];

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
$modifydate = 'NOW()';
$pid = $_POST['pid'];
$user = '';
$groupname = isset($_POST['groupname']) ? $_POST['groupname'] : 'Default';

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

    if ($acl_allow) {
        setListTouch($pid, $type);

        $strQuery = "INSERT INTO `lists`(`date`, `type`, `title`, `begdate`, `enddate`, `returndate`, `occurrence`, `classification`, `referredby`, `extrainfo`, `diagnosis`, `activity`, `comments`, `pid`, `user`, `groupname`, `outcome`, `destination`, `reinjury_id`, `injury_part`, `injury_type`, `injury_grade`, `reaction`, `external_allergyid`, `erx_source`, `modifydate`, `erx_uploaded`) 
                                        VALUES (
                                                '" . add_escape_custom($date) . "',
                                                '" . add_escape_custom($type) . "',
                                                '" . add_escape_custom($title) . "',
                                                '" . add_escape_custom($begdate) . "',
                                                '" . add_escape_custom($enddate) . "',
                                                '" . add_escape_custom($returndate) . "',
                                                '" . add_escape_custom($occurrence) . "',
                                                '" . add_escape_custom($classification) . "',
                                                '" . add_escape_custom($referredby) . "',
                                                '" . add_escape_custom($extrainfo) . "',
                                                '" . add_escape_custom($diagnosis) . "',
                                                '" . add_escape_custom($activity) . "',
                                                '" . add_escape_custom($comments) . "',
                                                '" . add_escape_custom($pid) . "',
                                                '" . add_escape_custom($user) . "',
                                                '" . add_escape_custom($groupname) . "',
                                                '" . add_escape_custom($outcome) . "',
                                                '" . add_escape_custom($destination) . "',
                                                '" . add_escape_custom($reinjury_id) . "',
                                                '" . add_escape_custom($injury_part) . "',
                                                '" . add_escape_custom($injury_type) . "',
                                                '" . add_escape_custom($injury_grade) . "',
                                                '" . add_escape_custom($reaction) . "',
                                                '" . add_escape_custom($external_allergyid) . "',
                                                '" . add_escape_custom($erx_source) . "',
                                                '" . $modifydate . "',
                                                '" . add_escape_custom($erx_uploaded) . "')";
        $result = sqlInsert($strQuery);

        $last_inseted_id = $result;

        $result1 = 1;
        if ($visit_id) {
            $sql_list_query = "INSERT INTO `issue_encounter`(`pid`, `list_id`, `encounter`, `resolved`) 
                            VALUES (
                                '".add_escape_custom($pid)."',
                                '".add_escape_custom($last_inseted_id)."',
                                '".add_escape_custom($visit_id)."',
                                0)";
            $result1 = sqlInsert($sql_list_query);
        }
        if ($result && $result1) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The {$type} has been added</reason>";
            $xml_string .= "<id>{$last_inseted_id}</id>";
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
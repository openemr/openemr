<?php
/**
 * api/getlist.php retrieve all patients.
 *
 * API fetch all patient problems e.g allergies, medication, medical problem etc.
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
require_once('classes.php');
require_once("$srcdir/lists.inc");

$xml_string = "";
$xml_string .= "<Listitems>\n";

$token = $_POST['token'];
$pid = $_POST['patientId'];
$type = $_POST['type'];
$visit_id = isset($_POST['visit_id']) && !empty($_POST['visit_id']) ? $_POST['visit_id'] : '';

if ($userId = validateToken($token)) {
    $user_data = getUserData($userId);
    $user = $user_data['user'];
    $emr = $user_data['emr'];
    $username = $user_data['username'];
    $password = $user_data['password'];

    $acl_allow = acl_check('patients', 'med', $username);

    if ($acl_allow) {
        if (!empty($type)) {
            $list = getListByType($pid, $type, $cols = "*", $active = "all", $limit = "all", $offset = "0");
            if ($list) {
                $xml_string .= "<status>0</status>\n";
                $xml_string .= "<reason>Success processing patient records</reason>\n";

                for ($i = 0; $i < count($list); $i++) {
                    $xml_string .= "<listitem>\n";

                    foreach ($list[$i] as $fieldName => $fieldValue) {
                        $rowValue = xmlsafestring($fieldValue);
                        $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                    }

                    $xml_string .= "</listitem>\n";
                }
            } else {
                $xml_string .= "<status>-1</status>\n";
                $xml_string .= "<reason>Cound not find results</reason>\n";
            }
        } else {
            // for all types
            $list_medical_problem = getListByType($pid, 'medical_problem', $cols = "*", $active = "all", $limit = "all", $offset = "0");
            $list_allergy = getListByType($pid, 'allergy', $cols = "*", $active = "all", $limit = "all", $offset = "0");
            $list_medication = getListByType($pid, 'medication', $cols = "*", $active = "all", $limit = "all", $offset = "0");
            $list_surgery = getListByType($pid, 'surgery', $cols = "*", $active = "all", $limit = "all", $offset = "0");
            $list_dental = getListByType($pid, 'dental', $cols = "*", $active = "all", $limit = "all", $offset = "0");

            $all_lists = array(
                'medical_problem' => $list_medical_problem,
                'allergy' => $list_allergy,
                'medication' => $list_medication,
                'surgery' => $list_surgery,
                'dental' => $list_dental
            );

            if ($list_medical_problem || $list_allergy || $list_medication || $list_surgery || $list_dental) {
                $xml_string .= "<status>0</status>\n";
                $xml_string .= "<reason>The Patient Record has been fetched</reason>\n";

                foreach ($all_lists AS $listtype => $list) {


                    for ($i = 0; $i < count($list); $i++) {

                        if ($visit_id || $list[$i]['encounter'] != $visit_id) {
                            continue;
                        }

                        $xml_string .= "<listitem>\n";
                        foreach ($list[$i] as $fieldName => $fieldValue) {
                            $rowValue = xmlsafestring($fieldValue);
                            $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                        }

                        $xml_string .= "</listitem>\n";
                    }
                }
            } else {
                $xml_string .= "<status>-1</status>\n";
                $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>\n";
            }
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>\n";
    $xml_string .= "<reason>Invalid Token</reason>\n";
}
$xml_string .= "</Listitems>\n";
echo $xml_string;
?>
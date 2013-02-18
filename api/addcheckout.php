<?php
/**
 * api/addcheckout.php Add patient checkout.
 *
 * API is allowed to add patient billed ammount, discount and payment 
 * method(cash, check).
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
require_once 'classes.php';

$xml_string = "";
$xml_string = "<checkout>";

//Post by form:-
$token = $_POST['token'];
$patientId = $_POST['patientId'];
$visit_id = $_POST['visit_id'];
$payment_method = $_POST['payment_method'];
$check_ref_number = $_POST['check_ref_number'];
$discountAmount = $_POST['discountAmount'];
$billing_id = $_POST['billing_id'];

//Post by getfeesheet web serivece for insertion
$feeSum = $_POST['feeSum'];
$fee = -$amount_paid;
$amount_paid = $feeSum - $discountAmount;

//Post by getfeesheet web serivece for view Only
$itemFee = $_POST['itemFee'];
$date = $_POST['date'];
$units = add_escape_custom($_POST['units']);

$code_type = 'COPAY';
$auth = "1";

if ($userId = validateToken($token)) {
    $user = getUsername($userId);




    $acl_allow = acl_check('acct', 'bill', $user);

    $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;
    $_SESSION['pid'] = $patientId;

    if ($acl_allow) {

        addBilling($visit_id, $code_type, $code, $code_text, $patientId, $auth, $provider = "0", $modifier = "0", $units, $fee, $ndc_info = '', $justify = '', $billed = "1", $notecodes = '');

        $strQuery1 = "UPDATE `billing` SET";
        $strQuery1 .= " activity  = 0";
        $strQuery1 .= " WHERE encounter = " . add_escape_custom($visit_id) . " AND pid = " . add_escape_custom($patientId);

        $result1 = sqlStatement($strQuery1);

        $strQuery2 = 'UPDATE `billing` SET';
        $strQuery2 .= ' fee  = "' . add_escape_custom($feeSum) . '",';
        $strQuery2 .= ' bill_date  = "' . date('Y-m-d H:i:s') . '",';
        $strQuery2 .= ' billed  = 1';
        $strQuery2 .= ' WHERE id = ' . add_escape_custom($billing_id);

        $result2 = sqlStatement($strQuery2);

        $strQuery3 = "INSERT INTO ar_activity ( pid, encounter, code, modifier, payer_type, post_user, post_time, session_id, memo, adj_amount ) 
                                            VALUES ( '" . add_escape_custom($patientId) . "',
                                                    '" . add_escape_custom($visit_id) . "',
                                                    '',
                                                    '',
                                                    '0',
                                                    '" . $userId . "',
                                                    '" . date('Y-m-d H:i:s') . "',\
                                                    '0',
                                                    'Discount',
                                                    '" . add_escape_custom($discountAmount) . "'
                                                        )";

        $result3 = sqlStatement($strQuery3);

        if ($result1 && $result2 && $result3) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Checkout has been added.</reason>";
        } else {
            $xml_string .= "<status>-1</status>";
            $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>";
        }
    } else {
        $xml_array['status'] = -2;
        $xml_array['reason'] = 'You are not Authorized to perform this action';
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</checkout>";
echo $xml_string;?>
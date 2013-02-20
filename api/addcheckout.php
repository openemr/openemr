<?php

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
$units = $_POST['units'];

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
        $strQuery1 .= " WHERE encounter = " . $visit_id . " AND pid = " . $patientId;

        $result1 = sqlStatement($strQuery1);

        $strQuery2 = 'UPDATE `billing` SET';
        $strQuery2 .= ' fee  = "' . $feeSum . '",';
        $strQuery2 .= ' bill_date  = "' . date('Y-m-d H:i:s') . '",';
        $strQuery2 .= ' billed  = 1';
        $strQuery2 .= ' WHERE id = ' . $billing_id;

        $result2 = sqlStatement($strQuery2);

        $strQuery3 = "INSERT INTO ar_activity ( pid, encounter, code, modifier, payer_type, post_user, post_time, session_id, memo, adj_amount ) VALUES ( '" . $patientId . "', '" . $visit_id . "', '', '', '0', '" . $userId . "', '" . date('Y-m-d H:i:s') . "', '0', 'Discount', '" . $discountAmount . "' )";

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
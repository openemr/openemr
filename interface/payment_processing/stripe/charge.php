<?php

/**
 * To collect credit card payments in openEMR without assigning to an encounter
 * using stripe.com
 *
 * Receives post data from confirmation.php, charges credit card using the secure token
 * received from confirmation.php and writes to table cc_ledger1
 * If credit card payment is a test transaction 'T' gets written as transaction_type
 * if it is a live transaction 'L' gets written as transaction_type
 * Presents a receipt button, which posts to receipt.php
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 * 
 * @package OpenEMR
 * @author Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author Ranganath Pathak <pathak01@hotmail.com>
 * @copyright Copyright (c) 2016, 2017 Sherwin Gaddis, Ranganath Pathak
 * @version 3.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link http://www.open-emr.org 
 */
use OpenEMR\Core\Header;

require_once ("../../globals.php");
require_once ('config.php'); //loads the server side stripe classes
require_once ("../scripts/php/sanitize.php");
require_once ("../scripts/php/genRandomString.php"); // needed to generate addon charge number


// all strings that need to be translated

$lang = array(
    'cc_successfully_charged' => htmlspecialchars(xl('CC Successfully Charged'), ENT_NOQUOTES),
    'patient_id' => htmlspecialchars(xl('Patient ID'), ENT_NOQUOTES),
    'successfully_charged' => htmlspecialchars(xl('Successfully Charged'), ENT_NOQUOTES),
    'pt_name' => htmlspecialchars(xl('Pt Name'), ENT_NOQUOTES),
    'amount_charged' => htmlspecialchars(xl('Amount Charged'), ENT_NOQUOTES),
    'currency' => htmlspecialchars(xl('$'), ENT_NOQUOTES),
    'charged_by' => htmlspecialchars(xl('Charged by'), ENT_NOQUOTES),
    'receipt' => htmlspecialchars(xl('Receipt'), ENT_NOQUOTES)
);

/* @var $_POST type */
$token = filter_input(INPUT_POST, 'stripeToken');
$email = filter_input(INPUT_POST, 'stripeEmail');
$amount = filter_input(INPUT_POST, 'chargeAmount');
$account_code = filter_input(INPUT_POST, 'account_code');
$comment = filter_input(INPUT_POST, 'comment');
$currency = filter_input(INPUT_POST, 'currency');
$decimal = substr($GLOBALS['stripe_currency'],4); //gets the decimal type ie two decimals or zero decimals
if (isset($_POST['patient_id'])) {
    $patient_id = check_input($_POST['patient_id']);
} // to check for pid of 0
$customer = \Stripe\Customer::create(array(
    'email' => $email,
    //'card' => $token
    'source'  => $token
));
try { // cc is being charged here using secure token returned by checkout.js accessed in confirmation.php no credit card number being sent or stored
    $charge = \Stripe\Charge::create(array(
        'customer' => $customer->id,
        'amount' => $amount,
        'currency' => $currency
    
    ));
} catch (\Stripe\Error\Card $e) {
    echo "<fieldset style='border:1px solid #ff5d5a; background-color: #ff5d5a; color: #fff; font-weight: bold; font-family:sans-serif; border-radius:5px; padding:20px 5px;'>";
    //echo "Error: " . $e;
    $error = 'There was a problem charging your card: '.$e->getMessage();
    echo $error . "<br>";
    // $body = $e->getJsonBody();
    // $err  = $body['error'];

    // print('Status is:' . $e->getHttpStatus() . "\n");
    // print('Type is:' . $err['type'] . "\n");
    // print('Code is:' . $err['code'] . "\n");
    // // param is '' in this case
    // print('Param is:' . $err['param'] . "\n");
    // print('Message is:' . $err['message'] . "\n");
    
    echo "</fieldset>";
    exit();
} catch (\Stripe\Error\RateLimit $e) {
  // Too many requests made to the API too quickly
  $e->getMessage();
  exit();
} catch (\Stripe\Error\InvalidRequest $e) {
  // Invalid parameters were supplied to Stripe's API
  $e->getMessage();
  exit();
} catch (\Stripe\Error\Authentication $e) {
  // Authentication with Stripe's API failed
  // (maybe you changed API keys recently)
  $e->getMessage();
  exit();
} catch (\Stripe\Error\ApiConnection $e) {
  // Network communication with Stripe failed
  $e->getMessage();
  exit();
} catch (\Stripe\Error\Base $e) {
  // Display a very generic error to the user, and maybe send
  // yourself an email
  $e->getMessage();
  exit();
} catch (Exception $e) {
  // Something else happened, completely unrelated to 
  $e->getMessage();
  exit();
}

$charge_id = $charge->id;
if ($decimal == "TD"){ // for currencies with 2 decimals
    $amt = sprintf('%.2f', $charge['amount'] / 100);
} elseif ($decimal == "ZD") { // for currencies with zero decimals
    $amt = $charge['amount'];
}

$pid = $patient_id;
$user = $_SESSION['authUser'];
$date = date("Y-m-d");
$pk_key_stripe = $GLOBALS['pk_key_stripe'];

if (strpos($pk_key_stripe, 'live') !== false) {
    $transaction_type = "L";
} elseif (strpos($pk_key_stripe, 'test') !== false) {
    $transaction_type = "T";
} else {
    $transaction_type = "N";
}

If ($pid == 0) {
    $mrn = "<span class = 'warning' > {$lang['patient_id']}: 0 !! WARNING!!</span>";
} else {
    $mrn = "{$lang['patient_id']}: <strong>$pid</strong>";
}

$date1 = date_create();
$chrg_date = date_format($date1, 'Y-m-d H:i:s');
$post_user = intval($_SESSION['authUserID']);

// Getting patient name, user name to be used in display
$sql = "SELECT fname, 
                lname,
                mname
            FROM patient_data as pd 
            WHERE 
                pd.pid = ? ";
$variables = array(
    $pid
);
$result = sqlQuery($sql, $variables);

$pt_lname = $result['lname'];
$pt_fname = $result['fname'];
$pt_mname = $result['mname'];

$sql = "SELECT  fname, 
                lname,
                mname
        FROM users as u 
        WHERE u.id = ? ";

$result = sqlQuery($sql, array(
    $post_user
));

$usr_fname = $result['fname'];
$usr_lname = $result['lname'];
$usr_mname = $result['mname'];

// sanitize data before insert double

$charge_id = check_input($charge_id);
$currency_code = check_input($currency);
$amt = check_input($amt);
$account_code = check_input($account_code);
$pid = check_input($pid);
$post_user = check_input($post_user);
$chrg_date = check_input($chrg_date);
$transaction_type = check_input($transaction_type);
$comment = check_input($comment);

// Insert data into table cc_ledger1

$sql = "INSERT INTO cc_ledger1 SET trans_id = ?,
                          currency_code = ?,
                          pay_amount = ?,
                          account_code = ?,
                          pid = ?,
                          post_user = ?,
                          chrg_date = ?,
                          transaction_type = ?,
                          comment = ? ";

$variables = array(
    $charge_id,
    $currency_code,
    $amt,
    $account_code,
    $pid,
    $post_user,
    $chrg_date,
    $transaction_type,
    $comment
);

sqlInsert($sql, $variables);

$currency = strtoupper($currency);
$currency_hex_rst = sqlStatement("SELECT currency_hex
        FROM currencies
        WHERE currency_code LIKE ?", array($currency));
$currency_hex_array =  sqlFetchArray($currency_hex_rst);
$currency_hex = $currency_hex_array['currency_hex'];
// generate appropriate currency symbol
if(!empty($currency_hex)) { 
    $arr_currency_hex = explode(",", $currency_hex);
    foreach ($arr_currency_hex as $value) {
        $currency_symbol .= "&#x".trim($value);
    }
    //currency code to be added to the amount if symbol is dollar #x0024 or pound #x00a3 and 
    //currency is not USD or GBP to indicate that amount id not US  dollar or GB Pound
    if(($currency_hex == '24' && $currency != 'USD')||($currency_hex == 'a3' && $currency != 'GBP')){
        $curr_code = "(".$currency.")";
    } else{
         $curr_code = "";
    }
} else {
    $currency_symbol = ''; 
}
//$currency_symbol = check_input($currency_symbol);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<?php Header::setupHeader();?>
<!--[if lt IE 9]>
      <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
      <![endif]-->
<style>
    form {
        margin-top: 30px;
    }
</style>
<title><?php echo $lang['cc_successfully_charged']; ?></title>  
</head>
<body>
    
<?php
$div = <<<EOF
            <div id = 'cc-container' name = 'cc-container' class = 'container'>
                <div id='form-div'>
                    <form name = 'charged-form' id =  'charged-form' method = 'post' action= 'receipt.php'>
                        <div class='col-xs-6 col-xs-offset-3'>
                            <fieldset>
                                <legend>{$lang['successfully_charged']}</legend>
                                <div id = 'success' name = 'success' class = 'success'>
                                    &nbsp;&nbsp;{$lang['pt_name']}: <strong>$pt_fname $pt_mname $pt_lname</strong><br>
                                    <br>
                                    &nbsp;&nbsp;$mrn<br>
                                    <br>
                                    &nbsp;&nbsp;{$lang['amount_charged']}:<strong> {$currency_symbol} $amt ({$currency})</strong><br>
                                    <br>
                                    &nbsp;&nbsp;{$lang['charged_by']}: <strong>$usr_fname $usr_lname</strong>
                                </div>
                            </fieldset>
                        </div>
                        <div  class='col-xs-6 col-xs-offset-3'>
                            <?php //can change position of buttons by creating a class 'position-override' and adding rule text-align:center or right as the case may be in individual stylesheets ?>
                            <div class="form-group clearfix">
                                <div class="col-sm-12 text-left position-override">
                                    <div class="btn-group" role="group">
                                        <button type='submit' id='button-blue' class="btn btn-default btn-print" value='{$lang["receipt"]}'onclick ='return top.restoreSession()'>{$lang["receipt"]}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                            <input type = 'hidden' name = 'trans_id' id = 'trans_id' value = '$charge_id'>
                            <input type = 'hidden' name = 'transaction_type' id = 'transaction_type' value = '$transaction_type' >
                            <input type='hidden' value='$patient_id' name='patient_id'>
                        </form>  
                </div>
            </div>
EOF;
echo $div;
?>                        
    <script>
        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
    </script>
</body>
</html>
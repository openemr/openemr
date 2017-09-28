<?php

/**
 * To collect credit card payments in openEMR without assigning to an encounter
 * using stripe.com
 *
 * Receives POST data containing patient and payment details but not credit card number 
 * from index.php and summarises it , when submitted acceses https://checkout.stripe.com/v2/checkout.js
 * this opens an iframe to stripe.com via https where credit card number etc is entered,
 * card is charged at stripe.com and a unique token is returned along with confirmation 
 * Posts data to charge.php containing these details, where it gets written to table cc_ledger1
 * NO CREDIT CARD DETAIL IS ENTERED IN OPENEMR, ENTERED ONLY IN THE IFRAME, AND NO CREDIT CARD DETAIL 
 * IS STORED IN OPENEMR, ONLY THE RETURNED TOKEN
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
require_once ('config.php');
require_once ("../scripts/php/sanitize.php");
if (isset($_POST['full_name'])) {
    $full_name = check_input($_POST['full_name']);
}
if (isset($_POST['patient_id'])) {
    $patient_id = check_input($_POST['patient_id']);
}
if (isset($_POST['amount'])) {
    $a = check_input($_POST['amount']);
} else {
    $a = "0.00";
}
if (isset($_POST['account_code'])) {
    $account_code = check_input(implode(", ", $_POST['account_code']));
}
if (isset($_POST['comment'])) {
    $comment = check_input($_POST['comment']);
}

$amount = str_replace(".", "", $a);
$currency = substr($GLOBALS['stripe_currency'], 0, 3);
$currency_lower = strtolower($currency);
$sql = "SELECT name FROM facility ";
$fn = sqlStatement($sql);
$fna = sqlFetchArray($fn);

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
    $currency_symbol = $currency_code; // displays 3 letter currency code if no symbol
}
//currency code to be added to the amount if symbol is dollar #x0024 or pound #x00a3


// all strings that need to be translated

$lang = array(
    'cc_confirmation' => htmlspecialchars(xl('Credit Card Confirmation'), ENT_NOQUOTES),
    'patient_payment' => htmlspecialchars(xl('Patient Payment'), ENT_NOQUOTES),
    'confirm_and_proceed' => htmlspecialchars(xl('Confirm and Proceed'), ENT_NOQUOTES),
    //'currency' => htmlspecialchars(xl('$'), ENT_NOQUOTES),
    'is_being_charged_to_a_credit_card_for_patient' => htmlspecialchars(xl('is being charged to a credit card for patient'), ENT_NOQUOTES),
    'do_you_wish_to_proceed' => htmlspecialchars(xl('Do you wish to proceed'), ENT_NOQUOTES),
    'proceed' => htmlspecialchars(xl('Proceed'), ENT_NOQUOTES), // no access from stripe cannot translate
    'cancel' => htmlspecialchars(xl('Cancel'), ENT_NOQUOTES),
    'charge_card' => htmlspecialchars(xl('Charge Card'), ENT_NOQUOTES)
);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<?php Header::setupHeader();?>
<style>
   form {
        margin-top: 30px;
    }
</style>
<title>CC Confirmation</title>

<!--[if lt IE 9]>
      <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
      <![endif]-->
</head>
<body class="body_top">
    <script src="https://checkout.stripe.com/checkout.js"></script>
   <?php
$div = <<<EOF
        <div id = 'cc-container' name = 'cc-container' class = 'container'>
            <div id='form-div'>
                <form id='chargeForm' action='charge.php' method='post' onsubmit ='return top.restoreSession()'>
                    <div class='col-xs-6 col-xs-offset-3'>
                        <fieldset>
                            <legend>{$lang['confirm_and_proceed']}</legend>
                            <p>&nbsp;&nbsp;<strong> $currency_symbol $a $curr_code</strong> {$lang['is_being_charged_to_a_credit_card_for_patient']} <span class= 'highlight'> $full_name</span>
                            <p>&nbsp;&nbsp;{$lang['do_you_wish_to_proceed']} ?
                        </fieldset>
                         
                    </div>
                    <div  class='col-xs-6 col-xs-offset-3'>
                        <?php //can change position of buttons by creating a class 'position-override' and adding rule text-align:center or right as the case may be in individual stylesheets ?>
                        <div class="form-group clearfix">
                            <div class="col-sm-12 text-left position-override">
                                <div class="btn-group" role="group">
                                    <button id="customButton" class="btn btn-default btn-save" >{$lang['charge_card']}</button>
                                    <button id = 'back-btn' class="btn btn-link btn-cancel btn-separate-left" onclick='top.restoreSession(); window.location.href="index.php"'><span>{$lang['cancel']}</span></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type='hidden' value='$amount' name='chargeAmount'>
                    <input type='hidden' value='$account_code' name='account_code'>
                    <input type='hidden' value='$comment' name='comment'>
                    <input type='hidden' value='$patient_id' name='patient_id'>
                    <input type="hidden" name="stripeToken" id="stripeToken" value="">
                    <input type="hidden" name ="stripeEmail" id="stripeEmail" value="">
                    <input type='hidden' value='$currency_lower' name='currency'>
                </form>        
            </div>
        </div>
EOF;
echo $div;
?>    
    <script>
        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
    </script>
   
    <script>
        var handler = StripeCheckout.configure({
          key: '<?php echo $stripe['publishable_key']; ?>',
          image: '../img/Menu-logo-t.png',
          locale: 'auto',
          token: function(token) {
            document.getElementById("stripeToken").value = token.id;
            document.getElementById("stripeEmail").value = token.email;
            document.getElementById("chargeForm").submit();
            
          }
        });

        document.getElementById('customButton').addEventListener('click', function(e) {
          // Open Checkout with further options:
          handler.open({
            name: '<?php echo $fna['name']; ?>',
            description: '<?php echo $lang['patient_payment']; ?>',
            zipCode: true,
            amount: <?php echo $amount ?>,
            currency: '<?php echo $currency_lower ?>'
                   
          });
          e.preventDefault();
        });

        // Close Checkout on page navigation:
        window.addEventListener('popstate', function() {
          handler.close();
        });
    </script>
       
</body>
</html>
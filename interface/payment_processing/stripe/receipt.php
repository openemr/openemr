<?php
/**
 * To collect credit card payments in openEMR without assigning to an encounter
 * using stripe.com
 *
 * Receives post data from charge.php and displays a receipt
 * Presents a print icon, which lets you print the receipt
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
 * @ @package OpenEMR
 * @author Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author Ranganath Pathak <pathak01@hotmail.com>
 * @copyright Copyright (c) 2016, 2017 Sherwin Gaddis, Ranganath Pathak
 * @version 3.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link http://www.open-emr.org 
 */
use OpenEMR\Core\Header;

require_once ("../../globals.php");
require_once ("../scripts/php/sanitize.php");

if (isset($_POST['patient_id'])) {
    $patient_id = check_input($_POST['patient_id']);
} // to check for pid of 0
if (isset($_POST['trans_id'])) {
    $trans_id = check_input($_POST['trans_id']);
}

$pid = $patient_id;

$practice = $GLOBALS['receipt_company'];

$sql = "SELECT     CL.id,
        CL.trans_id,
        CL.currency_code,
        CL.pay_amount,
        DATE_FORMAT(CL.chrg_date, '%m/%d/%Y') AS charge_date,
        PD.lname AS pt_lname,
        PD.fname AS pt_fname,
        PD.mname AS pt_mname,
        PD.street,
        PD.city,
        PD.state,
        PD.postal_code,
        U.lname AS usr_lname,
        U.fname AS usr_fname
FROM cc_ledger1 as CL
INNER JOIN patient_data as PD 
            ON PD.pid = CL.pid
INNER JOIN users as U
            ON U.id = CL.post_user
WHERE CL.trans_id = ? ";

// returns a single row
$result = sqlQuery($sql, array(
    $trans_id
));

$cl_id = $result['id'];
$trans_id = $result['trans_id'];
$currency_code =  strtoupper($result['currency_code']);
$pay_amount = $result['pay_amount'];
$charge_date = $result['charge_date']; // remember this is formatted in SQL
$pt_lname = $result['pt_lname'];
$pt_fname = $result['pt_fname'];
$pt_mname = $result['pt_mname'];
$pt_fullname = $pt_fname . " " . trim($pt_mname . " " . $pt_lname);
$street = $result['street'];
$city = $result['city'];
$state = $result['state'];
$postal_code = $result['postal_code'];
$usr_lname = $result['usr_lname'];
$usr_fname = $result['usr_fname'];

// all strings that need to be translated

$lang = array(
    'cash' => htmlspecialchars(xl('cash'), ENT_NOQUOTES),
    'check' => htmlspecialchars(xl('check'), ENT_NOQUOTES),
    'check_no' => htmlspecialchars(xl('check no'), ENT_NOQUOTES),
    'print' => htmlspecialchars(xl('Print'), ENT_NOQUOTES),
    'receipt' => htmlspecialchars(xl('Receipt'), ENT_NOQUOTES),
    'date' => htmlspecialchars(xl('Date'), ENT_NOQUOTES),
    'receipt_no' => htmlspecialchars(xl('Receipt No'), ENT_NOQUOTES),
    'on_behalf_of' => htmlspecialchars(xl('On behalf of'), ENT_NOQUOTES),
    'received' => htmlspecialchars(xl('received'), ENT_NOQUOTES),
    'as_a_credit-card_payment_for' => htmlspecialchars(xl('as a credit-card payment for'), ENT_NOQUOTES),
    'the_unique_id_of_this_transaction_is' => htmlspecialchars(xl('The unique ID of this transaction is'), ENT_NOQUOTES),
    'this_charge_will_appear_on_your_credit_card_statement_under' => htmlspecialchars(xl('This charge will appear on your credit card statement under'), ENT_NOQUOTES),
    'if_you_have_any_questions_regarding_this_payment_please_quote_the_unique_id_in_your_correspondence' => htmlspecialchars(xl('If you have any questions regarding this payment please quote the unique id in your correspondence'), ENT_NOQUOTES),
    'card_charged_by' => htmlspecialchars(xl('Card charged by'), ENT_NOQUOTES)
);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<?php Header::setupHeader();?>
<link rel="stylesheet" href="../css/styles.css" media="print">
<!--[if lt IE 9]>
      <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
      <![endif]-->
<title><?php echo $lang{['receipt']} ?></title>
</head>
<body>
    <?php
    $body = <<<EOF
        <div id = 'receipt-container' name = 'receipt-container' class = 'container' style = 'width:80%'>
            <div id = 'print' name = 'print' class = 'right-float noprint'>
                <a href='#' onclick='window.print(); top.restoreSession()'><img src='../img/printer.png' title = '{$lang['print']}'></a>
            </div> 
            <div class = 'both-clear'></div>
        
            <div id = 'address' name = 'address' class = ''>
                <h3 class = 'center-text'>$practice</h3>
            </div>
            <div id = 'receipt-label' name = 'receipt-label' class = ''>
                <h4 class = 'center-text'>{$lang['receipt']} </h4>
            </div>
            <div id = 'date-receipt' name = 'date-receipt'>    
                    <p  class = 'left-float'>{$lang['date']}: $charge_date</p>
                    <p class= 'right-float'>{$lang['receipt_no']}: $cl_id</p>
            </div>
            <div class = 'both-clear'></div>        
            <div id = 'receipt-body' name = 'receipt-body' class = ''>
                <p>{$lang['on_behalf_of']} $practice {$lang['received']} <span style = 'font-weight:bold'> $pay_amount({$currency_code})</span> {$lang['as_a_credit-card_payment_for']} <span style = 'font-weight:bold'>$pt_fullname</span>.  
                {$lang['the_unique_id_of_this_transaction_is']} $trans_id.</p>
                <p>{$lang['this_charge_will_appear_on_your_credit_card_statement_under']} $practice.</p>
                <p>{$lang['if_you_have_any_questions_regarding_this_payment_please_quote_the_unique_id_in_your_correspondence']}.</p>
            </div>
            <br />
            <div id = 'charged-by' name = 'charged-by' class = ''>
                <p>{$lang['card_charged_by']}: $usr_fname</p>
            </div>
        </div>
EOF;
    echo $body;
    ?>
        </div>
	<script>
        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
      </script>
</body>
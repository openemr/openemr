<?php

/**
 * To collect credit card payments in openEMR without assigning to an encounter
 * using stripe.com
 *
 * Posts data to confirmation.php with patient and payment details but not credit card number
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
require_once ("../scripts/php/sanitize.php");

$pid = $GLOBALS['pid'];
if ($pid == 0) {
    echo "Please select a patient first.";
    exit();
}

// Getting patient name, user name to be used in display
$sql = "SELECT fname, 
               lname,
               mname
        FROM patient_data as pd 
        WHERE pd.pid = ? ";
$variables = array(
    $pid
);
$result = sqlQuery($sql, $variables);

$pt_lname = $result['lname'];
$pt_fname = $result['fname'];
$pt_mname = $result['mname'];
$pt_fullname = $pt_fname . " " . trim($pt_mname . " " . $pt_lname);

$decimal = substr($GLOBALS['stripe_currency'],4); //gets the decimal type ie two decimals or zero decimals

?>

<!DOCTYPE html>

<html>
<head>
<meta charset="utf-8">
<?php Header::setupHeader();?>

<!--<link rel="stylesheet" href="../css/styles.css">-->


<style rel="stylesheet">
/*

button-blue {
    padding-top: 3px;
}*/
select[multiple], select[size]{
    height:auto !Important
}
@media only screen and (max-width: 1024px) {
            [class*="col-"] {
              width: 100%;
              text-align:left!Important;
            }
        }
</style>
<!--[if lt IE 9]>
      <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
      <![endif]-->
      <?php
      
      
      
// all strings that need to be translated

$lang = array(
    'cc_processing' =>  htmlspecialchars(xl('Credit Card Processing'), ENT_NOQUOTES),
    'credit_card_payment' => htmlspecialchars(xl('Credit Card Payment'), ENT_NOQUOTES),
    'payment_details' =>  htmlspecialchars(xl('Payment Details'), ENT_NOQUOTES),
    'payment_for' => htmlspecialchars(xl('Payment For'), ENT_NOQUOTES),
    'payment_method' => htmlspecialchars(xl('Payment Method'), ENT_NOQUOTES),
    'payment_type' => htmlspecialchars(xl('Payment Type'), ENT_NOQUOTES),
    'ctrl' => htmlspecialchars(xl("Hold 'Ctrl' to choose multiple"), ENT_NOQUOTES),
    'copay' => htmlspecialchars(xl('Copay'), ENT_NOQUOTES),
    'payment_on_account' => htmlspecialchars(xl('Payment on Account'), ENT_NOQUOTES),
    'patient_payment' => htmlspecialchars(xl('Patient Payment'), ENT_NOQUOTES),
    'pre_pay' => htmlspecialchars(xl('Pre Pay'), ENT_NOQUOTES),
    'payment_amount' => htmlspecialchars(xl('Payment Amount'), ENT_NOQUOTES),
    'with_two_decimals' => htmlspecialchars(xl('with two decimals'), ENT_NOQUOTES),
    'with_no_decimals' => htmlspecialchars(xl('with no decimals'), ENT_NOQUOTES),
    'comment' => htmlspecialchars(xl('Comment'), ENT_NOQUOTES),
    'next' => htmlspecialchars(xl('Next'), ENT_NOQUOTES),
    'close' => htmlspecialchars(xl('Close'), ENT_NOQUOTES)
);

if($decimal =='TD'){
    $decimal_message = $lang['with_two_decimals'];
} else if ($decimal =='ZD'){
    $decimal_message = $lang['with_no_decimals'];
}



?>
    <title><?php echo  $lang['credit_card_payment']; ?></title>
</head>
<body class="body_top">
<?php 
$div = <<<EOF
        <div id = 'cc-container' name = 'cc-container' class = 'container'>
            <div class='row'>
                <div class='col-xs-12'>
                    <div class='page-header clearfix'>
                       <h2 id='header_title' class='clearfix'><span id='header_text'>{$lang['credit_card_payment']} - $pt_fullname ($pid)</span><a class='pull-right' data-target='#myModal' data-toggle='modal' href='#' id='help-href' name='help-href' style='color:#000000'><i class='fa fa-question-circle' aria-hidden='true'></i></a></h2>
                    </div>
                </div>
            </div>
            <div id='form-div' class='row'>
                
                <form name = 'form-amount' id = 'form-amount' method = 'post' action='confirmation.php' onSubmit = 'return(validateAmount());' >
                    <div class='col-xs-12'>
                        <fieldset>
                            <legend>{$lang['payment_details']}</legend>
                            <div class='col-xs-4'>
                            
                                <label class='control-label for='account_code_select'>{$lang['payment_type']} <span id = 'span-legend'> ({$lang['ctrl']})</span></label>
                                    <select name='account_code[]' id='account_code_select' class='form-control' size='4' multiple='multiple'>
                                        <option value='PCP' selected>{$lang['copay']}</option>
                                        <option value='POA'>{$lang['payment_on_account']}</option>
                                        <option value='PP'>{$lang['patient_payment']}</option>
                                        <option value='PRP'>{$lang['pre_pay']}</option>
                                    </select>
                           </div>
                            <div class='col-xs-4'>
                            
                                <label class='control-label for='amount'>{$lang['payment_amount']} <span id = 'span-legend'> ({$decimal_message})</span></label>
                                <input type='text'  name='amount'  id='amount' class='form-control' autocomplete='off' onBlur = 'roundAmount();' onClick= 'this.style.backgroundColor = "#FFFFFF"' >
                           
                            </div>
                            <div class='col-xs-4'>
                            
                                <label class='control-label for='comment'>{$lang['comment']}</label>
                                <textarea id='comment' name='comment' class='form-control' maxlength = '65' ></textarea>
                            </div>
                        </fieldset>
                            
                            <input type = 'hidden' name = 'full_name' id = 'full_name' value = '$pt_fullname'>
                            <input type = 'hidden' name = 'patient_id' id = 'patient_id' value = '$pid'> <!--to prevent patient pid 0 being written to database-->
                    </div>
                    <div class="col-xs-12">
                        
                        <div class="form-group clearfix">
                            <div class="col-sm-12 text-left position-override">
                                <div class="btn-group" role="group">
                                    <button type="submit" class="btn btn-default btn-save" name='form_refresh' value='{$lang['next']}' onclick ='return top.restoreSession()'>{$lang['next']}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div><!--End of container div-->
        <div class="row">
            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content oe-modal-content">
                        <div class="modal-header clearfix"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" style="color:#000000; font-size:1.5em;">×</span></button></div>
                        <div class="modal-body">
                            <iframe src="" id="targetiframe" style="height:650px; width:100%; overflow-x: hidden; border:none" allowtransparency="true"></iframe>  
                        </div>
                        <div class="modal-footer" style="margin-top:0px;">
                           <button class="btn btn-link btn-cancel pull-right" data-dismiss="modal" type="button">{$lang['close']}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
       
EOF;

echo $div;
require_once ("../scripts/php/validateAmount.php"); // echos out a js function, xl function used
$decimal = substr($GLOBALS['stripe_currency'],4); //gets the decimal type ie two decimals or zero decimals
?>

    <script>
        $( document ).ready(function() {
            $('#help-href').click (function(){
                document.getElementById('targetiframe').src ='stripe_help.php';
                //document.getElementById('targetiframe').src ='HOWTO setup a Stripe account.pdf';
            })
       
        });
    </script>
    <script >
        function roundAmount(){
			var decimal = '<?php echo $decimal; ?>';
			var chargeAmount  = document.getElementById("amount").value;
			if(chargeAmount > 0 && !isNaN(chargeAmount)){
                // to support zero decimal currencies - prevent adding zeros after decimal
				if (decimal == "TD"){
                    document.getElementById('amount').value = Number(chargeAmount).toFixed(2);
				} else if (decimal == "ZD") {
                    document.getElementById('amount').value = Number(chargeAmount).toFixed(0);
                }
			}
		}
    </script>
    <script>
        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
    </script>
</body>
</html>
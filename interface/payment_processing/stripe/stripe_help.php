<?php
 /**
 * Help file To collect credit card payments in openEMR without assigning to an encounter
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
?>
<!DOCTYPE HTML>
<html>
    <head>
    <?php Header::setupHeader();?>
    <title><?php echo xlt("EOB Posting - Instructions");?></title>
    <style>
        .oe-help-heading{
            color:#337ab7;
            background-color: #d9edf7;
            border-color: #bce8f1;
            padding: 10px 5px;
            border-radius: 5px;
        }
        .oe-help-redirect{
            color:#337ab7;
        }
        @media only screen and (max-width: 768px) {
           [class*="col-"] {
           width: 100%;
           text-align:left!Important;
            }
        }
        @media only screen and (max-width: 1004px) and (min-width: 641px)  {
            .oe-large {
                display: none;
            }
            .oe-small {
                display: inline-block;
            }
        }
    </style>
    </head>
    <body>
        <div class="container">
            <div>
                <center><h2><a name = 'entire_doc'><?php echo xlt("Setup and Use Stripe to Charge Credit Cards");?></a></h2></center>
            </div>
            <div class= "row">
                <p><?php echo xlt("The Stripe module allows charging credit cards from inside openEMR. ");?>
                
                <p><?php echo xlt("In order to begin using this module we need to go through these setps");?>
                <ul>
                    <li><a href="#account_setup"><?php echo xlt("Setup a free account with Stripe");?></a></li>
                    <li><a href="#configure_openemr"><?php echo xlt("Configure openEMR to start using the Stripe Credit Card Charger");?></a></li>
                    <li><a href="#test_payments"><?php echo xlt("Make some test charges from openEMR");?></a></li>
                    <li><a href="#view_payments"><?php echo xlt("View payments report in openEMR");?></a></li>
                    <li><a href="#assign_payments"><?php echo xlt("'Assign' payments in openEMR to specific encounters");?></a></li>
                    <li><a href="#activate_stripe"><?php echo xlt("Activate your Stripe account - real banking info, tax_id, EIN  etc. has to be provided");?></a></li>
                    <li><a href="#live_payment"><?php echo xlt("Go Live and make a small payment");?></a></li>
                    
                </ul>
            </div>
             <div class= "row" id="account_setup" name="account_setup">
                <h4 class="oe-help-heading"><?php echo xlt("Setting up an account with Stripe"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("Go to ");?><a href="https://stripe.com" target="_blank">https://stripe.com</a>, <?php echo xlt("Click on 'Create Account'.");?>
                
                <p><?php echo xlt("Enter email and password and click 'Create your Stripe Account'."); ?>
                
                <p><?php echo xlt("You will land on the Dashboard page."); ?>
                
                <p><?php echo xlt("You may choose to click on 'Read the Basics before staring on Stripe documentation'. If not close this by clicking  on the READ LATER  X button on the top right.."); ?>
                
                <p><?php echo xlt("Click on 'Payments' in the left menu."); ?>
                
                <p><?php echo xlt("Click on 'Create your first payment'."); ?>
                
                <p><?php echo xlt("Enter details -"); ?>
                
                <ul>
                    <li><?php echo xlt("Any random, syntactically valid email address (the more random, the better)");?></li>
                    <li><?php echo xlt("Stripe's test card numbers, such as 4242 4242 424 4242, more test numbers are available at ");?><a target="_blank"  href="https://stripe.com/docs/testing#cards"><?php echo xlt("More Stripe Test Card numbers");?></a></li>
                    <li><?php echo xlt("Any three-digit CVC code");?></li>
                    <li><?php echo xlt("Any expiration date in the future");?></li>
                    <li><?php echo xlt("Any billing ZIP code, such as 12345");?></li>
                </ul>
                
                <p><?php echo xlt("Click 'Create payment'."); ?>
                
                <p><?php echo xlt("If successful you will see the payment details. This means the fake charge has gone through in Test mode."); ?>
                
                <p><?php echo xlt("Click on 'API' in the left menu."); ?>
                
                <p><?php echo xlt("Copy the two test keys, Publishable and Secret, to a text file."); ?>
                
           </div>
            <div class= "row" id="configure_openemr" name="configure_openemr">
                <h4 class="oe-help-heading"><?php echo xlt("Configure openEMR to start using the Stripe Credit Card Charger"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("In openEMR go to Administration > Globals > CC gateway."); ?>
                
                <p><?php echo xlt("Check 'Enable Stripe CC Processing' box if not aleady enabled."); ?>
                
                <p><?php echo xlt("Copy the existing key values to a text file, replace them with these two test keys from your Stripe account."); ?>
                
                <p><?php echo xlt("'Currency' lets you choose the currency in which the card will be charged."); ?>
                
                <p><?php echo xlt("'Receipt on Behalf of' - Enter relevant info that will appear in the receipt generated to give to the patient."); ?>
                
                <p><?php echo xlt("Click 'Save'."); ?>
            </div>
            <div class= "row" id="test_payments" name="test_payments">
                <h4 class="oe-help-heading"><?php echo xlt("Make some test charges from openEMR"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("In openEMR Select any patient and enter their chart."); ?>
                
                <p><?php echo xlt("Go to Fees > Stripe Credit Card."); ?>
                
                <p><?php echo xlt("Enter details and click 'Next'."); ?>
                
                <p><?php echo xlt("It will take you to the Confirm and Proceed page. Click 'Charge Card'."); ?>
                
                <p><?php echo xlt("It will open a Stripe generated secure form. Enter details (fake ones like above). Click 'Pay'."); ?>
                
                <p><?php echo xlt("If successful it will go to the 'Successfully Charged' page."); ?>
                
                <p><?php echo xlt("Click 'Receipt' button. Note the unique ID number on the receipt generated. You can print this receipt using the printer icon"); ?>
                
                <p><?php echo xlt("Go to the Stripe dashboard and refresh it."); ?>
                
                <p><?php echo xlt("Click on 'Payments' in the left navigation menu."); ?>
                
                <p><?php echo xlt("You will now see the charge details. The Unique Id in the receipt should match the number in the description columnh."); ?>
                
                <p><?php echo xlt("You have now successfully charged a test credit card from within openEMR."); ?>
            </div>
            <div class= "row" id="view_payments" name="view_payments">
                <h4 class="oe-help-heading"><?php echo xlt("View payments report in openEMR"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("In openEMR go to Fees > Just View Cash/Check/Credit."); ?>
                
                <p><?php echo xlt("This will list all the successful charges."); ?>
                
                <p><?php echo xlt("For those with administrator or accounting privileges Just Assign/Cash/Check/Credit  will also be displayed that will let you 'assign' the charges."); ?>
                
                <p><?php echo xlt("The difference between these reports is that the assign report has an Assign icon that the View report lacks."); ?>
                
                <p><?php echo xlt("On both these reports if you hover over the 'Charged By' or 'Pmt Method' rows the mouse cursor icon will change to a dollar sign and document."); ?><img src="../img/dollar-doc.jpg"/>
                
                <p><?php echo xlt("Clicking will bring up a modal that will summarize the collections by either the person collecting the payment or by the payment method."); ?>
                
                <div><img src="../img/charged_by_modal.jpg" width="80%"/></div>
                <br>
                <p><?php echo xlt("The modal that is shown by clicking of the 'Pmt Method' row will show the 'unassigned' and 'assigned' values. The goal would be to 'assign' all payments and reduce the 'unassigned' value to zero."); ?>
                
                <div><img src="../img/pmt_method_modal.jpg" width="80%"/></div>
                 <br>
            </div>
            <div class= "row" id="assign_payments" name="assign_payments">
                <h4 class="oe-help-heading"><?php echo xlt("'Assign' payments in openEMR to specific encounters"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("Those with adminstrator or accounting privileges allowed to assign charges."); ?>
                
                <p><?php echo xlt("In openEMR go to Fees > Just Assign Cash/Check/Credit."); ?>
                
                <p><?php echo xlt("Click on the curved arrow in the 'Assign' column."); ?>
                <div><img src="../img/payment_processing2.jpg" width="100%"/></div>
                <br>
                
                <p><?php echo xlt("It will take you to the 'Paymemt Processing' page."); ?>
                <div><img src="../img/payment_processing1.jpg" width="80%"/></div>
                <br>
                
                <p><?php echo xlt("Enter details and click 'Assign'."); ?>
                
                <p><?php echo xlt("This will take you back to the Just Assign Cash/Check/Credit page . Note the curved arrow has been replaced by a green tick mark."); ?>
                <div><img src="../img/payment_processing3.jpg" width="100%"/></div>
                <br>
                
                <p><i class="fa fa-exclamation-triangle" style="color:red" aria-hidden="true"></i> <strong><?php echo xlt("Please be aware that these charges have to be manually entered into the regular workflow via Fees > Payment."); ?></strong>
                
                <p><?php echo xlt("To 'Refund' Click on the green tick mark of an assigned charge."); ?>
                
                <p><?php echo xlt("Click 'Refund'."); ?> <i class="fa fa-exclamation-circle" style="color:orange" aria-hidden="true"></i> <?php echo xlt("Only full 'refunds' are possible."); ?>
                
                <p><?php echo xlt("This will take you back to the Just Assign Cash/Check/Credit page . Note charge amount is now zero and the comment indicates a refund."); ?>
                <div><img src="../img/payment_processing4.jpg" width="100%"/></div>
                <br>
                
                <p><i class="fa fa-exclamation-triangle" style="color:red" aria-hidden="true"></i> <strong><?php echo xlt("Please be aware that the actual refund has to be done on the Stripe Dashboard and manually entered into the regular workflow via Fees > Posting."); ?></strong>
            </div>
            <div class= "row" id="activate_stripe" name="activate_stripe">
                <h4 class="oe-help-heading"><?php echo xlt("Activate your Stripe account"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("On the Stripe dashboard click on 'Activate your account' in the left menu. It will ask you to verify your email. Click 'Resend verification email'."); ?>
                
                <p><?php echo xlt("Open the email and click 'Confirm email address'."); ?>
                
                <p><?php echo xlt("On the dashboard once again click on 'Activate your account' in the left menu."); ?>
                
                <p><?php echo xlt("Fill out the Account Application form and click the 'Activate account' button at the very bottom."); ?>
                
                <p><?php echo xlt("Once account is activated, Click on 'API' in the left menu. Note now live keys are also available. Copy the live keys to a text file."); ?>
                
                <p><?php echo xlt("On an activated account you can switch between Live and Test data by using the 'View test data' slider switch in the left menu."); ?>
                
                <p><i class="fa fa-exclamation-circle" style="color:orange" aria-hidden="true"></i> <strong><?php echo xlt("By having a separate set of keys for live and test inadvertent charges to the live account is prevented."); ?></strong>
                
                <p><?php echo xlt("Click on 'Payouts' in the left menu. Go to 'Settings' and check to make sure there that at least one bank account is present and a payout schedule is selected. This is essential in order for the amounts that were charged and collected by Stripe to be transferred to your chosen bank account."); ?>
            </div>
            <div class= "row" id="live_payment" name="live_payment">
                <h4 class="oe-help-heading"><?php echo xlt("Go Live and make a small payment"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("In openEMR go to Administration > Globals > CC gateway."); ?>

                <p><?php echo xlt("Paste the live keys and click 'Save'."); ?>

                <p><?php echo xlt("Now you will be able to make an actual charge on the live site."); ?>

                <p><?php echo xlt("Repeat the steps in "); ?><strong><a href="#test_payments" class="oe-help-redirect"><?php echo xlt("Make some test charges from openEMR"); ?></a></strong>

                <p><?php echo xlt("Check on the Stripe Dashboard to cofirm that the charge has gone through and payment was received."); ?>

                <p><?php echo xlt("You should now be able to use Stripe to charge credit cards from inside openEMR."); ?>
            </div>
           
        </div><!--end of container div-->
    </body>
</html>

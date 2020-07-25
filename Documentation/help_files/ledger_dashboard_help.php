<?php

/**
 * Ledger Dashboard Help.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Header;

require_once("../../interface/globals.php");
?>
<!DOCTYPE html>
<html>
    <head>
    <?php Header::setupHeader();?>
    <title><?php echo xlt("Patient Ledger Help");?></title>
    </head>
    <body>
        <div class="container oe-help-container">
            <div>
                <h2 class="text-center"><a name='entire_doc'><?php echo xlt("Patient Ledger Help");?></a></h2>
            </div>
            <div class= "row">
                <div class="col-sm-12">
                    <p><?php echo xlt("Is a record of all financial transactions between the patient and the practice");?>.</p>

                    <p><?php echo xlt("Click on the Ledger menu item in the navigation bar to to enter the Patient Ledger page");?>.</p>

                    <p><?php echo xlt("Select a date range and click Submit");?>.
                        <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("Submit"); ?></button>
                    </p>

                    <p><?php echo xlt("Financial transactions corresponding to the date range, if any, will be listed in the table");?>.</p>

                    <p><?php echo xlt("They are listed in ascending order by encounter date");?>.</p>

                    <p><?php echo xlt("The first line will show the Encounter date and Reason as well as the Provider for that encounter");?>.</p>

                    <p><?php echo xlt("The next few lines will list all the E/M and CPT codes that were billed, one line per code");?>.</p>

                    <p><?php echo xlt("For those lines the Description will indicate the details of the code");?>.</p>

                    <p><?php echo xlt("The Billed Date/Payor column will show the date the line item was billed and the Payor - either insurance or patient");?>.</p>

                    <p><?php echo xlt("The Type will be blank");?>.</p>

                    <p><?php echo xlt("The Units will be 1 as each code is billed only once for the encounter");?>.</p>

                    <p><?php echo xlt("The Charge will be the actual amount being charged for each line item");?>.</p>

                    <p><?php echo xlt("The Payment, Adjustment and Balance will be blank as these lines reflect only the charges being made");?>.</p>

                    <p><?php echo xlt("The subsequent lines will indicate the details of the payments received, adjustments made and the balance due");?>.</p>

                    <p><?php echo xlt("On these lines the Code column will be blank");?>.</p>

                    <p><?php echo xlt("The Description will detail the type of payment or adjustment - Cash, Check, Electronic etc ");?>.</p>

                    <p><?php echo xlt("The Billed Date/Payor column will show the date the line item payment was received and the Payor name - either insurance or patient");?>.</p>

                    <p><?php echo xlt("The Type will Insurance or Patient");?>.</p>

                    <p><?php echo xlt("The Unit column would be blank");?>.</p>

                    <p><?php echo xlt("The Charge column would be blank");?>.</p>

                    <p><?php echo xlt("The amount will be displayed in either the Payment or Adjustment column as the case may be");?>.</p>

                    <p><?php echo xlt("The Balance column would be empty");?>.</p>

                    <p><?php echo xlt("The last line of this encounter block will give the encounter totals of Charges, Payments, Adjustments and the Balance");?>.</p>

                    <p><?php echo xlt("The very last line of this report will give the Grand Total of Charges, Payments, Adjustments and the Balance");?>.</p>
                </div>
            </div>
        </div><!--end of container div-->
    </body>
</html>

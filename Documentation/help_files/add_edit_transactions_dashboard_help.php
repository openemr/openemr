<?php

/**
 * Add Edit Transaction Dashboard Help.
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
    <title><?php echo xlt("Add Edit Transactions Help");?></title>
    </head>
    <body>
        <div class="container oe-help-container">
            <div>
               <h2 class="text-center"><a name='entire_doc'><?php echo xlt("Add Edit Transactions Help");?></a></h2>
            </div>
            <div class= "row">
                <div class="col-sm-12">
                    <p><?php echo xlt("Currently there are 5 types of transactions in OpenEMR");?>:</p>
                        <ul>
                            <li><?php echo xlt("Referral"); ?></li>
                            <li><?php echo xlt("Patient Request"); ?></li>
                            <li><?php echo xlt("Physician Request"); ?></li>
                            <li><?php echo xlt("Legal"); ?></li>
                            <li><?php echo xlt("Billing"); ?></li>
                        </ul>

                    <p><?php echo xlt("The first step is to select a Transaction type");?>.</p>

                    <p><strong><?php echo xlt("CREATE A REFERRAL"); ?> :</strong></p>

                    <p><?php echo xlt("A Referral is the most common type of transaction");?>.</p>

                    <p><?php echo xlt("Select Referral from the Transaction type drop-down box");?>.</p>

                    <p><?php echo xlt("A two tabbed form will be revealed");?>.</p>

                    <p><?php echo xlt("The first tab is the referral tab that constitutes the referral being made");?>.</p>

                    <p><?php echo xlt("The second tab, is somewhat ambiguously named as Counter-Referral, is the place to document the received reply to the referral being made");?>.</p>

                    <p><?php echo xlt("There are various selections to be filled, the minimum data needed to successfully save the referral is Referral Date, Refer To and Reason");?>.</p>

                    <p><?php echo xlt("Filling in the other fields will help provide additional details pertinent to the referral");?>.</p>

                    <p><?php echo xlt("To fulfill Meaningful use requirements and help track its use you can check on the Sent Summary of Care check-box and the Sent Summary of Care Electronically check-box if appropriate");?>.</p>

                    <p><?php echo xlt("Note this refers to the Continuity of Care Record (CCR) or Continuity of Care Document (CCD) as the case may be");?>.</p>

                    <p><?php echo xlt("It does not refer to the actual referral that has been created");?>.</p>

                    <p><?php echo xlt("Click Save");?>.
                        <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("Save"); ?></button>
                    </p>

                    <p><strong><?php echo xlt("CREATE A COUNTER-REFERRAL"); ?> :</strong></p>

                    <p><?php echo xlt("The somewhat ambiguously named Counter-Referral is the space where the reply to the referral being made can be documented");?>.</p>

                    <p><?php echo xlt("This lets you manually enter the reply to the referral as structured data");?>.</p>

                    <p><?php echo xlt("Fill in the details as required and click Save");?>.
                        <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("Save"); ?></button>
                    </p>

                    <p><?php echo xlt("It therefore stands to reason that you cannot create a Counter-Referral by itself");?>.</p>

                    <p><strong><?php echo xlt("CREATE A SIMPLE TRANSACTION TYPE"); ?> :</strong></p>

                    <p><?php echo xlt("The other 4 transaction types are simple transaction types - Patient Request, Physician Request, Legal and Billing");?>.</p>

                    <p><?php echo xlt("Select as appropriate and fill in the text box and click Save");?>.
                        <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("Save"); ?></button>
                    </p>

                    <p><?php echo xlt("Once a Transaction is saved it will appear on the Transaction page");?>.</p>

                    <p><?php echo xlt("These Transactions are generally used to document events that have already occurred");?>.</p>
                </div>
            </div>
        </div><!--end of container div-->
    </body>
</html>

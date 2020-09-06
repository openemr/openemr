<?php

/**
 * Transactions Dashboard Help.
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
    <title><?php echo xlt("Patient Transactions Help");?></title>
    </head>
    <body>
        <div class="container oe-help-container">
            <div>
                <h2 class="text-center"><a name='entire_doc'><?php echo xlt("Patient Transactions Help");?></a></h2>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <p><?php echo xlt("Transactions are for events or information not necessarily tied to one specific visit or encounter");?>.</p>

                    <p><?php echo xlt("Most activities in relation to a patient are based on an encounter");?>.</p>

                    <p><?php echo xlt("Transactions provides a mechanism to link an activity to patient that is not encounter based");?>.</p>

                    <p><?php echo xlt("Upon entering the page there are two buttons visible below the navigation bar");?>.</p>
                    <ul>
                        <li><?php echo xlt("Create New Transaction"); ?> <button type="button" class="btn btn-secondary btn-sm btn-add oe-no-float"><?php echo xlt("Create New Transaction"); ?></button></li>
                        <li><?php echo xlt("View/Print Blank Referral Form - that can be filled by hand"); ?>  <button type="button" class="btn btn-secondary btn-sm btn-print oe-no-float"><?php echo xlt("View/Print Blank Referral Form"); ?></button></li>
                    </ul>

                    <p><?php echo xlt("Below this is a table that contains the existing transactions, if any, pertaining to the current patient");?>.</p>

                    <p><?php echo xlt("There are three sets of actions that can be performed on this page, in addition the form can be customized");?>.</p>

                    <ul>
                        <li><a href="#section1"><?php echo xlt("Create New Transaction");?></a></li>
                        <li><a href="#section2"><?php echo xlt("Interact with created transactions");?></a></li>
                        <li><a href="#section3"><?php echo xlt("View/Print Blank Referral Form");?></a></li>
                        <li><a href="#section4"><?php echo xlt("Customize the Transaction Form");?></a></li>
                    </ul>
                </div>
            </div>
            <div class= "row" id="section1">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Create New Transaction"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("Click on Create New Transaction to open the Add/Edit Patient Transaction page");?>.</p>

                    <p><?php echo xlt("This is where the referrals and various other simple transactions are created");?>.</p>

                    <p><?php echo xlt("Use the help file on that page for further help");?>.</p>
                </div>
            </div>
            <div class= "row" id="section2">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Interact with created transactions"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("All Transactions for the patient will appear on the Transactions page in descending order of its date of creation");?>.</p>

                    <p><?php echo xlt("Each Transaction is listed on a separate line");?>.</p>

                    <p><?php echo xlt("Depending on the level of access you can View/Edit the Transaction");?>.</p>

                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <?php echo xlt("Those with adequate privilege would be able to able to Delete the transaction");?>.</p>

                    <p><?php echo xlt("These two actions are available for all transactions");?>.</p>

                    <p><?php echo xlt("A Referral has an additional action - to print the referral or save it as a pdf file");?>.</p>
                </div>
            </div>
            <div class= "row" id="section3">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("View/Print Blank Referral Form"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("In addition to creating a Referral the system also allows you to print a blank referral form that can be manually filled to generate a Referral");?>.</p>

                    <p><?php echo xlt("This method will however result in the data becoming non-structured and one would loose the ability to document the reply in an electronic format");?>.</p>
                </div>
            </div>
            <div class= "row" id="section4">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Customize the Transaction Form"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("The default form can be customized by editing it in Administration > Layouts");?>.</p>

                    <p><i class="fa fa-exclamation-triangle oe-text-red"  aria-hidden="true"></i> <strong><?php echo xlt("You will need Administrator privileges to edit this form");?>.</strong></p>

                    <p><?php echo xlt("There are 3 forms in the Core category - Demographics, Facility Specific User Information and History and all 5 forms in Transactions that can be edited ");?>.</p>

                    <p><?php echo xlt("More information on how to edit this form and other such forms can be found here");?>. &nbsp; <a href="https://www.open-emr.org/wiki/index.php/LBV_Forms" rel="noopener" target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title=""></i></a>&nbsp;
                        <a href="https://www.open-emr.org/wiki/index.php/Sample_Layout_Based_Visit_Form" rel="noopener" target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                    </p>
                </div>
            </div>
        </div><!--end of container div-->

    </body>
</html>

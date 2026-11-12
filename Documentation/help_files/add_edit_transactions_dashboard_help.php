<?php

/**
 * Add Edit Transaction Dashboard Help.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
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

                    <h3><?php echo xlt("CREATE A REFERRAL"); ?> :</h3>
                    <hr />

                    <p><?php echo xlt("A Referral is the most common type of transaction");?>.</p>

                    <p><?php echo xlt("Select Referral from the Transaction type drop-down box");?>.</p>

                    <p><?php echo xlt("A two tabbed form will be revealed");?>.</p>

                    <p><?php echo xlt("The first tab is the referral tab that constitutes the referral being made");?>.</p>

                    <p><?php echo xlt("The second tab, is somewhat ambiguously named as Counter-Referral, is the place to document the received reply to the referral being made");?>.</p>

                    <p><?php echo xlt("There are various selections to be filled, the minimum data needed to successfully save the referral is Referral Date, Refer To and Reason");?>.</p>

                    <p><?php echo xlt("Filling in the other fields will help provide additional details pertinent to the referral");?>.</p>

                    <div class="alert alert-info">
                    <h4><?php echo xlt("Meaningful Use Requirements"); ?></h4>
                    <p><?php echo xlt("To fulfill Meaningful use requirements and help track its use you can check on the Sent Summary of Care check-box and the Sent Summary of Care Electronically check-box if appropriate");?>.</p>

                    <p><?php echo xlt("Note this refers to the Continuity of Care Record (CCR) or Continuity of Care Document (CCD) as the case may be");?>.</p>
                    <p><?php echo xlt("It does not refer to the actual referral that has been created");?>.</p>
                    <p><?php echo xlt("If you use the Carecoordination module to generate this document for this patient on the same day after creating this referral these checkboxes will be checked automatically");?>.</p>
                    <p><?php echo xlt("A MIPS eligible clinician must verify that the fields for current problem list, current medication list, and current medication allergy list are not blank and include the most recent information known by the MIPS eligible clinician as of the time of generating the summary of care document or include a notation of no current problem, medication and/or medication allergies");?>.</p>


                    <p><?php echo xlt("A MIPS eligible clinician must have reasonable certainty of receipt of the summary of care document by the receiving clinician");?>.</p>
                    <p><?php echo xlt("If a CEHRT software other than EMR Direct was used to transfer the summary of care you can check the Confirmed Recipient Received Summary of Care check-box upon confirmation of receipt if appropriate");?>.</p>

                    </div>
                    <p><?php echo xlt("Click Save");?>.
                        <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("Save"); ?></button>
                    </p>

                    <h3><?php echo xlt("TRACK A REFERRAL"); ?> :</h3>
                    <hr />

                    <p><?php echo xlt("A saved referral shows where it stands on this page, on the Transactions list and in the Referrals report");?>.</p>
                    <p><?php echo xlt("Open means no reply has been recorded");?>.</p>
                    <p><?php echo xlt("Replied means a Reply Date has been recorded. Completed means the recipient's task is completed, which a recipient can do through the API without sending a reply, so Completed alone does not mean a consult note is on file");?>.</p>
                    <p><?php echo xlt("Overdue is added to an open referral once its Referral Date is at least the number of days set in Referrals Overdue After (Days) in the Report settings ago. Setting that to 0 turns Overdue off");?>.</p>

                    <div class="alert alert-info">
                    <h4><?php echo xlt("Referral Tracking With Tasks"); ?></h4>
                    <p><?php echo xlt("When Track Referrals with FHIR Tasks is turned on in the Connectors settings, saving a referral that has no Reply Date creates a task for the Refer To recipient");?>.</p>
                    <p><?php echo xlt("The recipient must have an NPI, or be an External Organization in the Address Book");?>.</p>
                    <p><?php echo xlt("An External Organization needs an Organization name or a contact name to receive a task. If the name is missing, add it in the Address Book and save the referral again");?>.</p>
                    <p><?php echo xlt("The referral then shows the status of that task: Draft, Requested, Received, Accepted, Ready, In Progress, On Hold, Completed, Rejected, Failed, Cancelled or Entered in Error");?>.</p>
                    <p><?php echo xlt("A recipient connected through the API changes this status, and the Refer By provider gets a message when the referral is accepted, rejected, completed, failed or cancelled");?>.</p>
                    <p><?php echo xlt("Changing Refer To and saving cancels the first recipient's task if it is still open. The new recipient gets a task only when the referral has no Reply Date and the new recipient has an NPI or is an External Organization");?>.</p>
                    </div>

                    <p><?php echo xlt("Re-send appears when the recipient rejected, failed or cancelled the referral and no reply is recorded");?>.</p>
                    <p><?php echo xlt("It sends the referral to the same recipient again and reopens it");?>.</p>
                    <p><?php echo xlt("Saving the referral does not re-send it. The one exception is clearing a Reply Date that had completed the task: that save gives the same recipient a new task and, with notices turned on, tells them again");?>.</p>
                    <p><?php echo xlt("When Notify Referral Recipients is also turned on, the recipient is told about a new referral: by Direct message if their Address Book entry has a Trusted Email (Direct address), otherwise by a plain email with no patient details");?>.</p>
                    <p><?php echo xlt("This page shows how the recipient was told, or that they could not be told");?>.</p>

                    <h3><?php echo xlt("CREATE A COUNTER-REFERRAL"); ?> :</h3>
                    <hr />

                    <p><?php echo xlt("The somewhat ambiguously named Counter-Referral is the space where the reply to the referral being made can be documented");?>.</p>

                    <p><?php echo xlt("This lets you manually enter the reply to the referral as structured data");?>.</p>

                    <p><?php echo xlt("Fill in the details as required and click Save");?>.
                        <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("Save"); ?></button>
                    </p>

                    <p><?php echo xlt("It therefore stands to reason that you cannot create a Counter-Referral by itself");?>.</p>

                    <p><?php echo xlt("With referral tracking turned on, saving a Reply Date while the recipient's task is still open files the reply in the patient's documents as a consult note and completes the task. If the recipient had already completed, rejected, failed or cancelled the task, or there is no task, the reply is kept on this page only and no document is filed");?>.</p>
                    <p><?php echo xlt("Clearing a Reply Date that was saved by mistake reopens the referral when that reply is what completed it, and the recipient gets a new task and a new notice. The consult note that was filed for the mistaken reply is not removed: it stays in the patient's documents, so review it there and delete it if it should not be kept. A referral the recipient had already completed, rejected, failed or cancelled keeps showing that status");?>.</p>
                    <p><?php echo xlt("A reply received by Direct message is listed on this page only when it answers a Direct message that was sent with this referral. Its Confirm it completes the referral button appears only when referral tracking is on, the recipient's task is still open, the referral has no Reply Date and you may edit transactions");?>.</p>
                    <p><?php echo xlt("Read the reply first. Confirm only when it answers the referral: confirming records the Reply Date and completes the task, which takes the referral off the open list. Do not confirm a question or a refusal");?>.</p>

                    <h3><?php echo xlt("CREATE A SIMPLE TRANSACTION TYPE"); ?> :</h3>
                    <hr />

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

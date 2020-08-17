<?php

/**
 * Message Center Help.
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
    <title><?php echo xlt("Message Center Help");?></title>

    <style>
        @media only screen and (max-width: 768px) {
           [class*="col-"] {
           width: 100%;
           text-align:left!important;
            }
        }
    </style>
    </head>
    <body>
        <div class="container oe-help-container">
            <div>
                <h2 class="text-center"><a name='entire_doc'><?php echo xlt("Messages, Reminders, Recalls");?></a></h2>
            </div>
            <div class= "row">
                <p><?php echo xlt("The messaging center conveniently consolidates communications options in one place");?>.</p>

                <p><?php echo xlt("It lets you send messages and dated reminders to staff members, add patient to the recall list and send SMS text messages to patients");?>.</p>

                <p><?php echo xlt("The default messaging center is divided into three sections - Messages, Reminders and Recalls");?>.</p>

                <p><?php echo xlt("Additional functionality including the ability to send SMS text messages, automated phone dialing, emails etc. can be accessed by enabling the optional MedEx Communication Service");?>.</p>

                <ul>
                    <li><a href="#messages"><?php echo xlt("Messages");?></a></li>
                    <li><a href="#reminders"><?php echo xlt("Reminders");?></a></li>
                    <li><a href="#recalls"><?php echo xlt("Recalls");?></a></li>
                    <li><a href="#medex_communication_service"><?php echo xlt("MedEx Communication Service");?></a></li>
                </ul>
            </div>
            <div class= "row" id="messages">
                <h4 class="oe-help-heading"><?php echo xlt("Messages"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("Lets you send messages to staff members about patient related matters");?>.</p>

                <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <strong><?php echo xlt("It is a part of the medical record"); ?>.</strong></p>

                <p><?php echo xlt("Upon logging in to openEMR a small envelope icon is visible on the top right. It shows the number of pending messages and dated reminders"); ?>.
                    <button type="button" class="btn btn-secondary btn-sm oe-no-float"><i class="fa fa-envelope"></i></button>
                </p>

                <p><?php echo xlt("You can click on the envelope icon to access the messaging center"); ?>.</p>

                <p><?php echo xlt("Alternatively it can be accessed by clicking in the Messages menu item in the top navigation bar"); ?>.</p>

                <p><?php echo xlt("The messaging center will open with the Messages tab activated and will display the logged in user's messages as indicated by the caption My Messages"); ?>.</p>

                <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <strong><?php echo xlt("For those with administrative privileges an icon is displayed next to the My Messages caption") ?>.</strong></p>

                <p><?php echo xlt("Clicking on the icon next to the caption will display everyone's messages and the caption will change to All Messages") ?>.</p>

                <p><?php echo xlt("By default Active Messages are displayed as indicated in the section below the Messages caption"); ?>.</p>

                <p><?php echo xlt("Clicking on the Show All or Show Inactive buttons will show the appropriate messages"); ?>.</p>

                <p><strong><?php echo xlt("CREATING A NEW MESSAGE"); ?> :</strong></p>

                <p><?php echo xlt("To create a new message click on the Add New button at the bottom"); ?>.
                    <button type="button" class="btn btn-secondary btn-add btn-sm oe-no-float"><?php echo xlt("Add New"); ?></button>
                </p>

                <p><?php echo xlt("It will open the Create New Message section"); ?>.</p>

                <p><?php echo xlt("Select the message type to more accurately reflect the type of message you are sending. You may choose to leave it as unassigned"); ?>.</p>

                <p><?php echo xlt("The message status will be New as it a new message that is being created"); ?>.</p>

                <p><?php echo xlt("Select a patient by clicking on the patient input box"); ?>.</p>

                <p><?php echo xlt("It will bring up the Multi patient finder  popup"); ?>.&nbsp;</p>
                <i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <strong><?php echo xlt("New to openEMR 5.0.2"); ?></strong>

                <p><?php echo xlt("Clicking on either the Enter Name or Enter ID tab will bring up the search box which you can use to select a patient"); ?>.</p>

                <p><?php echo xlt("Click on Add to List button to add to the bottom section"); ?>.
                    <button class="btn btn-secondary btn-sm oe-no-float"><?php echo xlt("Add to list"); ?></button>
                </p>

                <p><?php echo xlt("You can add multiple patients to the list"); ?>.</p>

                <p><?php echo xlt("Click OK to import these patients into the patient input box"); ?>.</p>

                <p><?php echo xlt("Click the Clear button to clear the imported patient names and start afresh"); ?>.
                    <button type="button" class="btn btn-secondary btn-undo btn-sm oe-no-float"><?php echo xlt("Clear"); ?></button>
                </p>

                <p><?php echo xlt("Use the Select User dropdown box to select the user(s) to whom the message will be sent"); ?>.</p>

                <p><?php echo xlt("You can add multiple users by selecting them one at a time"); ?>.</p>

                <p><?php echo xlt("Click the Clear button to start afresh"); ?>.
                    <button type="button" class="btn btn-secondary btn-undo btn-sm oe-no-float"><?php echo xlt("Clear"); ?></button>
                </p>

                <p><?php echo xlt("Type the message and click Send Message "); ?>.
                    <button type="button" class="btn btn-secondary btn-send-msg btn-sm oe-no-float"><?php echo xlt("Send Message"); ?></button>
                </p>

                <p><?php echo xlt("A new message can thus be sent to a single or multiple users and/or can be about a single or multiple patients"); ?>.</p>

                <p><strong><?php echo xlt("ADD TO EXISTING MESSAGE"); ?> :</strong></p>

                <p><?php echo xlt("All pending messages are displayed on the MY MESSAGES pane"); ?>.</p>

                <p><?php echo xlt("You can sort the pending messages in ascending or descending order by clicking on the arrowhead in each cell of the table header"); ?>.</p>

                <p><?php echo xlt("Clicking on the patient's name will reveal the message"); ?>.</p>

                <p><?php echo xlt("You can only add to the existing message and cannot edit previously entered data"); ?>.</p>

                <p><?php echo xlt("You can change the type or leave it as it is"); ?>.</p>

                <p><?php echo xlt("There are four message statuses - New, Forwarded, Read and Done"); ?>.</p>

                <p><?php echo xlt("Changing the status to Done will remove the message from the active message list"); ?>.</p>

                <p><?php echo xlt("Changing the status to Read would leave it as a read message in the active messages list of the current user"); ?>.</p>

                <p><?php echo xlt("If the message is forwarded it will show up in the active messages list of the user to whom the message was forwarded and disappear from the current user's active messages list"); ?>.</p>

                <p><?php echo xlt("Changing the status to New will achieve the same result"); ?>.</p>

                <p><?php echo xlt("If desired the current message can be printed"); ?>.</p>

                <p><?php echo xlt("Clicking on the Delete button will delete the message"); ?>.
                    <button type="button" class="btn btn-secondary btn-delete btn-sm oe-no-float"><?php echo xlt("Delete"); ?></button>
                </p>
            </div>
            <div class= "row" id="reminders">
                <h4 class="oe-help-heading"><?php echo xlt("Reminders"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("Otherwise known as Dated Reminders are short messages of up to 160 characters"); ?>.</p>

                <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <strong><?php echo xlt("Unlike a message sent in Messages the dated reminder is not a part of the medical record"); ?>.</strong></p>

                <p><?php echo xlt("It can be either linked to a patient or just be a message addressed to another user(s)"); ?>.</p>

                <p><?php echo xlt("There are three actions that are possible - Create a New Dated Reminder, Forward and Set as Completed"); ?>.</p>

                <p><strong><?php echo xlt("CREATING A NEW DATED REMINDER"); ?> :</strong></p>

                <p><?php echo xlt("Clicking on the Create A Dated Reminder button will bring up the Send a Reminder popup"); ?>.
                    <button type="button" class="btn btn-secondary btn-add btn-sm oe-no-float"><?php echo xlt("Create A Dated Reminder"); ?></button>
                </p>

                <p><?php echo xlt("The first section lets you either link this message to a patient if so desired."); ?>.</p>

                <p><?php echo xlt("The second section lets you choose to whom you wish to send the message"); ?>.</p>

                <p><?php echo xlt("You can click on the Select All button to choose all authorized users"); ?>.
                    <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("Select All"); ?></button>
                </p>

                <p><?php echo xlt("If the checkbox is checked and the message is sent to multiple users then each user has to sign off on the message by clicking the Set As Completed Button for that message"); ?>.</p>

                <p><?php echo xlt("A due date can be specified or a specific time period can be chosen for the dated reminder to to show up"); ?>.</p>

                <p><?php echo xlt("Set the priority level"); ?>.</p>

                <p><?php echo xlt("Type a brief message and hit Send Message "); ?>.
                    <button type="button" class="btn btn-secondary btn-send-msg btn-sm oe-no-float"><?php echo xlt("Send Message"); ?></button>
                </p>

                <p><?php echo xlt("The messages that were sent by the user on that day will be displayed in the table below"); ?>.</p>

                <p><?php echo xlt("The dated reminder will start to appear 5 days before the reminder date under Dated Reminders "); ?>.</p>

                <p><?php echo xlt("The upcoming reminders have a green exclamation icon"); ?>.&nbsp;
                <i class="fa fa-exclamation-circle oe-text-green" aria-hidden="true"></i>

                <p><?php echo xlt("The reminder for the current day will have an orange exclamation icon"); ?>.</p>
                <i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i>

                <p><?php echo xlt("The reminders that are past the due date will have a red exclamation triangle icon"); ?>.</p>
                <i class="fa fa-exclamation-triangle oe-text-red" aria-hidden="true"></i>

                <p><?php echo xlt("By default only five dated reminders are shown at a time"); ?>.</p>

                <p><?php echo xlt("Increase the limit to a larger number say 100 so that you can decide on which ones to act upon"); ?>.</p>

                <p><i class="fa fa-exclamation-triangle oe-text-red" aria-hidden="true"></i> <strong><?php echo xlt("You need administrator privileges to increase the number of reminders to show"); ?>.</strong></p>

                <p><?php echo xlt("Go to Administration > Globals > CDR > Dated reminders maximum alerts to show and change the value to a higher number"); ?>.</p>

                <p><?php echo xlt("A dated reminder message can be either forwarded by clicking the Forward button or removed from the dated reminders list by clicking on the Set As Completed button"); ?>.
                    <button type="button" class="btn btn-secondary btn-send-msg btn-sm oe-no-float"><?php echo xlt("Forward"); ?></button>
                    <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("Set As Completed"); ?></button>
                </p>

                <p><strong><?php echo xlt("FORWARD A DATED REMINDER"); ?> :</strong></p>

                <p><?php echo xlt("The process for forwarding the message is quite similar to that of creating a new message"); ?>.</p>

                <p><?php echo xlt("While forwarding a dated reminder the old message is displayed, it can be overwritten"); ?>.</p>

                <p><?php echo xlt("The previous message can however be viewed by clicking on the View Log button"); ?>.
                    <button type="button" class="btn btn-secondary btn-save btn-show btn-sm oe-no-float"><?php echo xlt("View Log"); ?></button>
                </p>

                <p><?php echo xlt("Clicking on the View Log button will bring up the Dated Message Log popup"); ?>.</p>

                <p><?php echo xlt("Lets you filter the results as per the chosen criteria"); ?>.</p>

                <p><?php echo xlt("If you hit the refresh button without setting any filters then all dated reminders for the user will appear below"); ?>.
                    <button type="button" class="btn btn-secondary btn-sm btn-refresh oe-no-float"><?php echo xlt("Refresh"); ?></button>
                </p>

                <p><?php echo xlt("The log table will show a list of both messages and dated reminders if any"); ?>.</p>

                <p><?php echo xlt("The display window can be resized by clicking and dragging the bottom right corner of the window"); ?>.</p>

                <p><?php echo xlt("Clicking on the slashed eye icon will hide the filters"); ?>.</p>
                <i class="fa fa-eye-slash text-warning" aria-hidden="true"></i>

                <p><?php echo xlt("To reveal the filters click on the eye icon"); ?>.
                    <i class="fa fa-eye text-warning" aria-hidden="true"></i>
                </p>

                <p><strong><?php echo xlt("SET AS COMPLETED"); ?> :</strong></p>

                <p><?php echo xlt("Clicking on Set As Completed will remove the reminder from the active display"); ?>.
                    <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("Set As Completed"); ?></button>
                </p>
            </div>
            <div class= "row" id="recalls">
                <h4 class="oe-help-heading"><?php echo xlt("Recalls"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("This feature is used to add patients to a recall list, i.e those that need an appointment at a future date but the appointment has not yet been scheduled"); ?>.</p>

                <p><?php echo xlt("Once an appointment is scheduled the name automatically drops off the recall board"); ?>.</p>

                <p><?php echo xlt("The Recalls tab has two buttons New Recall and Recall Board"); ?>.
                    <button type="button" class="btn btn-secondary btn-sm btn-add oe-no-float"><?php echo xlt("New Recall"); ?></button>
                    <button type="button" class="btn btn-secondary btn-sm btn-transmit oe-no-float"><?php echo xlt("Recall Board"); ?></button>
                </p>

                <p><strong><?php echo xlt("ADD A NEW RECALL"); ?> :</strong></p>

                <p><?php echo xlt("Click on the New Recall button to open a new recall scheduling page"); ?>.</p>

                <p><?php echo xlt("It contains a left and right section"); ?>.</p>

                <p><?php echo xlt("Click on the Name text box on the left section to bring up the patient finder pop-up"); ?>.</p>

                <p><?php echo xlt("Search and select a patient and click OK"); ?>.</p>

                <p><?php echo xlt("If the patient has any demographic data entered in openEMR it will automatically populate the relevant fields on the right sections"); ?>.</p>

                <p><?php echo xlt("Fill in any missing details or edit existing information. This will be saved in the patient's demographics page in openEMR"); ?>.</p>

                <p><?php echo xlt("The last visit date will automatically be filled"); ?>.</p>

                <p><?php echo xlt("If the patient is being added to the recall list on the day of the visit it would reflect the current date"); ?>.</p>

                <p><?php echo xlt("If the patient is calling back to be added to the list the last visit may be in the remote past. Be aware that the 1,2,3 plus years are calculated and displayed on the Date box at the bottom of this section"); ?>.</p>

                <p><?php echo xlt("You could manually edit this date should you choose to or an entirely different date"); ?>.</p>

                <p><?php echo xlt("Fill in the reason and select a provider and clinic and click the Add Recall button to complete the process"); ?>.
                    <button type="button" class="btn btn-secondary btn-sm btn-add oe-no-float"><?php echo xlt("Add Recall"); ?></button>
                </p>

                <p><strong><?php echo xlt("RECALL BOARD - Default - without MedEx Communication Service"); ?> :</strong></p>

                <p><?php echo xlt("This fully functional Recall Board included in the default install can be used to add patients to the recall list"); ?>.</p>

                <p><?php echo xlt("To access it click on the Recall Board menu item in the top navigation bar or click on the Recall Board button in the Message Center"); ?>.
                    <button type="button" class="btn btn-secondary btn-sm btn-transmit oe-no-float"><?php echo xlt("Recall Board"); ?></button>
                </p>

                <p><?php echo xlt("The top portion of the Recall Board allows the setting of filters to display specified data"); ?>.</p>

                <p><?php echo xlt("Enter or select the options in the various boxes to filter the results according to need and press Filter"); ?>.
                    <button type="button" class="btn btn-secondary btn-sm btn-filter oe-no-float"><?php echo xlt("Filter"); ?></button>
                </p>

                <p><?php echo xlt("The filtered results will show up in the section below it"); ?>.</p>

                <p><?php echo xlt("The filter section can be hidden or revealed by clicking the arrowhead  on the bottom right "); ?>.</p>
                <span class="text-right fa-stack fa-lg pull_right small oe-text-black"style="position:relative;right:0;top:0;">
                    <i class="fa fa-square-o fa-stack-2x"></i>
                    <i id="print_caret" class="fa fa-caret-up fa-stack-1x"></i>
                </span>

                <p><?php echo xlt("The Name cell contains the name, date of birth, patient ID and date of last visit"); ?>.</p>

                <p><?php echo xlt("You can click on the patient name to quickly access the patient's chart"); ?>.</p>

                <p><?php echo xlt("The Recall cell has the date of recall and the reason for recall"); ?>.</p>

                <p><?php echo xlt("The Contacts cell has various bits of contact information like phone numbers, email etc"); ?>.</p>

                <p><?php echo xlt("The next three cells are the action cells that let you perform some recall activity"); ?>.</p>

                <p><?php echo xlt("If you check the check box in any of these cells it will generate an entry in the last cell called Progress"); ?>.</p>

                <p><?php echo xlt("Checking the Postcards check box will let you print a postcard that can be mailed to a patient"); ?>.</p>

                <p><?php echo xlt("Labels can be printed either for one patient or for all the selected patients"); ?>.</p>

                <p><?php echo xlt("The Office: Phone cell lets you indicate a phone call was made"); ?>.</p>

                <p><i class="fa fa-exclamation-circle oe-text-orange" aria-hidden="true"></i> <strong></p>
                <?php echo xlt("The default installation will not make the phone call automatically"); ?>.</strong>

                <p><?php echo xlt("An appointment can be scheduled by clicking on the calendar icon in that cell"); ?>.</p>
                <i class="fa fa-calendar-check-o fa-fw oe-text-black" aria-hidden="true"></i>

                <p><?php echo xlt("Once an appointment is scheduled the recall will drop off the Recall Board"); ?>.</p>

                <p><?php echo xlt("The Notes cell lets you add a note to an action that you take in the 3 action cells or just a note without any association to the action"); ?>.</p>

                <p><?php echo xlt("The Progress cell lists all the actions along with any associated notes if any"); ?>.</p>

                <p><?php echo xlt("You can delete the recall by clicking on the X button on the top right corner of the cell"); ?>.</p>

                <p><?php echo xlt("If so desired the default board can be disabled by going to Administration > Calendar > Recall Board: Disable and checking the checkbox and click Save"); ?>.</p>

                <p><i class="fa fa-exclamation-triangle oe-text-red" aria-hidden="true"></i> <strong><?php echo xlt("You need administrator privileges to disable the default Recall Board"); ?>.</strong></p>
            </div>
            <div class= "row" id="medex_communication_service">
                <h4 class="oe-help-heading"><?php echo xlt("MedEx Communication Service"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><i class="fa fa-exclamation-circle oe-text-orange" aria-hidden="true"></i> <strong><?php echo xlt("It is a commercial messaging module available to openEMR practices utilizing version 5.0.1 and higher"); ?>.</strong></p>

                <p><?php echo xlt("MedEx automates Appointment Reminders and Recalls using phone calls, text messages and e-mails"); ?>.</p>

                <p><?php echo xlt("Replies from patients are displayed directly in your EHR"); ?>.</p>

                <p><i class="fa fa-exclamation-triangle  oe-text-red" aria-hidden="true"></i> <strong><?php echo xlt("You need administrator privileges to enable the MedEx Communication Service"); ?>.</strong></p>

                <p><?php echo xlt("Go to Administration > Globals > Connectors and check the Enable MedEx Communication Service check box and click Save"); ?>.
                    <button type="button" class="btn btn-secondary btn-sm btn-save oe-no-float"><?php echo xlt("Save"); ?></button>
                </p>

                <p><?php echo xlt("Refresh the Message Center or just reopen it"); ?>.</p>

                <p><?php echo xlt("A new tab sub menu will be visible at the top of the Message center page"); ?>.</p>

                <p><?php echo xlt("Click on the File menu item on the top left of the Message center page ansd select Setup MedEx"); ?>.</p>

                <p><?php echo xlt("It will take you to the MedEx sign-up page"); ?>.</p>

                <p><?php echo xlt("More information available at the openEMR MedEx wiki page"); ?>.
                    <a href="https://www.open-emr.org/wiki/index.php/MedEx" rel="noopener" target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                </p>

                <p><?php echo xlt("The features of a MedEx enabled installation are explained in this video"); ?>.
                    <a href="https://www.youtube.com/watch?v=4lbJCpfotAo" rel="noopener" target="_blank"><i class="fa fa-video-camera text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                </p>

                <p><?php echo xlt("SMS zone - This section lets use send and receive SMS texts"); ?>.</p>

                <p><?php echo xlt("It is also a subscription based service"); ?>.</p>
            </div>
        </div><!--end of container div-->
    </body>
</html>

<?php

/**
 * Issues Dashboard Help.
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
    <title><?php echo xlt("Issues Help");?></title>
    </head>
    <body>
        <div class="container oe-help-container">
            <div>
                <h2 class="text-center"><a name='entire_doc'><?php echo xlt("Issues Help");?></a></h2>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <p><?php echo xlt("An Issue concerns matters relating to the patient's health");?>.</p>

                    <p><?php echo xlt("The default installation lists 5 types of issues that can be documented");?>:</p>
                    <ul>
                        <li><a href="#section1"><?php echo xlt("Medical Problems");?></a></li>
                        <li><a href="#section2"><?php echo xlt("Allergies");?></a></li>
                        <li><a href="#section3"><?php echo xlt("Medications");?></a></li>
                        <li><a href="#section4"><?php echo xlt("Surgeries");?></a></li>
                        <li><a href="#section5"><?php echo xlt("Dental Issues");?></a></li>
                    </ul>

                    <p><?php echo xlt("Issues can also be linked to zero or more encounters");?>.</p>

                    <p><?php echo xlt("The ability to link individual issues to patient encounters will let the user quickly determine the outcome of the issue over time");?>.</p>

                    <p><?php echo xlt("The Issues can be entered in one of two ways");?>:</p>
                    <ul>
                        <li><?php echo xlt("From the Patient dashboard via the individual Issues widgets on the right hand column"); ?></li>
                        <li><?php echo xlt("From the Issues Menu item on the Nav Bar"); ?></li>
                    </ul>

                    <p><?php echo xlt("Either way data entered by any method will feature in both locations");?>.</p>

                    <p><?php echo xlt("Click on the Issues menu item to bring up the Issues page");?>.</p>

                    <p><?php echo xlt("You can enter data into all 5 categories from this page");?>.</p>

                    <p><i class="fa fa-lightbulb-o fa-lg  oe-text-green" aria-hidden="true"></i>&nbsp <?php echo xlt("To use the help page as an instruction manual it is suggested that you reduce the size of the browser to cover half the viewport, resize the help pop-up by clicking and dragging the bottom right corner of the pop-up. Open another instance of the browser and resize it to cover the other half of the viewport, login to openEMR ");?>.</p>

                </div>
            </div>
            <div class= "row" id="section1">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Medical Problems"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("Click on Add to bring up the Add/Edit Issue pop-up page");?>.
                        <button type="button" class="btn btn-secondary btn-sm oe-no-float"><?php echo xlt("Add"); ?></button>
                    </p>

                    <p><?php echo xlt("The Type would be Problem indicating a medical problem");?>.</p>

                    <p><?php echo xlt("Underneath it will be some common problems");?>.</p>

                    <p><?php echo xlt("Choose one if appropriate");?>.</p>

                    <p><?php echo xlt("This will fill in the Title text-box as well as ICD10 code in the Coding text-box");?>.</p>

                    <p><?php echo xlt("If the medical problem is not listed in the drop-down box click on Coding text-box to bring up the Select Codes pop-up page");?>.</p>

                    <p><?php echo xlt("Select only one code per problem");?>.</p>

                    <p><?php echo xlt("If the displayed table says No matching record found it means that the ICD10 code set is not installed");?>.</p>

                    <p><?php echo xlt("Install it by going to Administration > Other > External Data Loads > ICD10 > Staged Releases and click Install");?>.</p>

                    <p><i class="fa fa-exclamation-triangle  oe-text-red" aria-hidden="true"></i> <strong><?php echo xlt("You will need Administrator Privileges to install the ICD10 code set");?>.</strong></p>

                    <p><?php echo xlt("Now when you bring up the Select Codes pop-up you will see all the ICD10 codes listed");?>.</p>

                    <p><?php echo xlt("You can search for and select the codes by clicking once on each line containing the code");?>.</p>

                    <p><?php echo xlt("Click Close to exit the Select Codes pop-up page");?>.</p>

                    <p><?php echo xlt("The selected code will be added to the coding text-box and the Description on the code will be added to the Title text-box");?>.</p>

                    <p><?php echo xlt("You can edit the title if you so choose");?>.</p>

                    <p><?php echo xlt("If a wrong code was selected click on the Coding text-box to to bring up the Select Codes pop-up page");?>.</p>

                    <p><?php echo xlt("Listing an ICD10 code will let you search for educational material for that particular code ");?>.</p>

                    <p><?php echo xlt("It is also necessary to list an ICD10 code in order for the problem to be listed in Continuity of Care Report (CCR) and Continuity of Care Document (CCD)");?>.</p>

                    <p><?php echo xlt("Enter a Begin Date");?>.</p>

                    <p><?php echo xlt("Enter an End Date if the problem has been resolved");?>.</p>

                    <p><?php echo xlt("Leave it blank if the problem is still active");?>.</p>

                    <p><?php echo xlt("Select the type of Occurrence of the problem from the drop-down box, the choices are Unknown or N/A, First, Early Recurrence, Late Recurrence, Delayed Recurrence, Chronic/Recurrent and Acute on Chronic");?>.</p>

                    <p><?php echo xlt("You may type in a name of the person referring or leave blank");?>.</p>

                    <p><?php echo xlt("An Outcome for the problem - Resolved, Improved, Status Quo, Worse, Pending Followup or leave as Unassigned");?>.</p>

                    <p><?php echo xlt("Leave Destination blank");?>.</p>

                    <p><?php echo xlt("Click Save to save this problem");?>.
                        <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("Save"); ?></button>
                    </p>

                    <p><?php echo xlt("The saved Medical Problem will now be listed as a single line in the main Issues page under Medical Problems");?>.</p>

                    <p><?php echo xlt("If it it still active i.e. End Date is not entered it will be displayed in red, if the problem has an end Date then it will be displayed in black");?>.</p>

                    <p><?php echo xlt("Clicking on the first cell under Title will bring up the Add/Edit Issue pop-up page that will let you edit this entry");?>.</p>

                    <p><?php echo xlt("Clicking on the Coding cell will bring up the Educational materials pop-up that will let you search by ICD10 code");?>.</p>

                    <p><?php echo xlt("There are two sources that can be searched - MedlinePlus Connect or Local Content");?>.</p>

                    <p><?php echo xlt("You can upload content and label them by ICD10 code enable you to search and download the selected material");?>.</p>

                    <p><?php echo xlt("The last cell on the line in Encounter");?>.</p>

                    <p><?php echo xlt("If the issue has not been linked to an encounter it will display 0");?>.</p>

                    <p><?php echo xlt("To be able to link this issue to an encounter click on the button to bring up the Issues and Encounters pop-up page");?>.</p>

                    <p><?php echo xlt("It will have two sections - the Issues Section and the Encounters Section");?>.</p>

                    <p><?php echo xlt("The Issues section will display all the issues entered for the patient and will display the type of issue, its title and a description");?>.</p>

                    <p><?php echo xlt("The Encounters Section will display the date of encounter as well as the presenting complaint");?>.</p>

                    <p><?php echo xlt("To link the Issues to an encounter first select the issue, it will be highlighted yellow, then click on one or more encounters to link them, the selected encounters will be highlighted in purple");?>.</p>

                    <p><?php echo xlt("To delink just click on the item to delink and it will get delinked and will no longer be highlighted");?>.</p>

                    <p><?php echo xlt("Click Save");?>.</p>

                    <p><?php echo xlt("You can also add an Issue from this page by clicking on the Add Issue button to bring up the Add issue pop-up page");?>.</p>

                    <p><?php echo xlt("You can then proceed to link the newly added issue to encounters");?>.</p>
                </div>
            </div>
            <div class= "row" id="section2">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Allergies"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("Depending on whether or not NewCrop eRx module is enabled the method of entry will vary");?>.</p>

                    <p><?php echo xlt("In the default installation click on Add to bring up the Add/Edit Issue pop-up page");?>.
                        <button type="button" class="btn btn-secondary btn-sm oe-no-float"><?php echo xlt("Add"); ?></button>
                    </p>

                    <p><?php echo xlt("The Type would be Allergy");?>.</p>

                    <p><?php echo xlt("Underneath it will be some common drugs");?>.</p>

                    <p><?php echo xlt("Choose one if appropriate");?>.</p>

                    <p><?php echo xlt("If not listed you can add the drug to the Title text-box");?>.</p>

                    <p><?php echo xlt("Leave Coding Blank");?>.</p>

                    <p><?php echo xlt("Begin date if known");?>.</p>

                    <p><?php echo xlt("End Date leave blank if ongoing");?>.</p>

                    <p><?php echo xlt("Enter Occurrence if appropriate");?>.</p>

                    <p><?php echo xlt("Enter the Severity of the allergy");?>.</p>

                    <p><?php echo xlt("Reaction - hives, nausea, shortness of breath or unassigned");?>.</p>

                    <p><?php echo xlt("Referred By, Outcome and Destination can be left blank");?>.</p>

                    <p><?php echo xlt("Click Save");?>.
                        <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("Save"); ?></button>
                    </p>

                    <p><?php echo xlt("If you have NewCrop eRx module enabled then the allergies have to be entered on the NewCrop MedEntry page");?>.</p>

                    <p><?php echo xlt("Click on the Allergy/Intolerance button");?>.</p>

                    <p><?php echo xlt("Search for the drug by typing its name in the Search for Allergy text-box");?>.</p>

                    <p><?php echo xlt("Click the Search for Allergy button");?>.</p>

                    <p><?php echo xlt("Select from the displayed list");?>.</p>

                    <p><?php echo xlt("Assign Severity and Save");?>.</p>

                    <p><?php echo xlt("It will then appear in the Allergies widget on the Patient Dashboard as well as under allergies in the Issues page");?>.</p>

                    <p><?php echo xlt("You can link the allergy to one or more encounters if appropriate");?>.</p>
                </div>
            </div>
            <div class= "row" id="section3">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Medications"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("Depending on whether or not NewCrop eRx module is enabled the method of entry will vary");?>.</p>

                    <p><?php echo xlt("In the default installation click on Add to bring up the Add/Edit Issue pop-up page");?>.
                        <button type="button" class="btn btn-secondary btn-sm oe-no-float"><?php echo xlt("Add"); ?></button>
                    </p>

                    <p><?php echo xlt("The Type would be Medication");?>.</p>

                    <p><?php echo xlt("Fill in the details in a fashion similar to that used for allergies");?>.</p>

                    <p><?php echo xlt("Click Save");?>.
                        <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("Save"); ?></button>
                    </p>

                    <p><?php echo xlt("If you have NewCrop eRx module enabled then the allergies have to be entered on the NewCrop MedEntry page");?>.</p>

                    <p><?php echo xlt("Type the name of the drug in the Drug Search text-box");?>.</p>

                    <p><?php echo xlt("Select from the displayed list");?>.</p>

                    <p><?php echo xlt("Assign the dosage values etc. and click Save");?>.</p>

                    <p><?php echo xlt("It will then appear in the Medications widget on the Patient Dashboard as well as under Medications in the Issues page");?>.</p>

                    <p><?php echo xlt("You can link the medication to one or more encounters if appropriate");?>.</p>
                </div>
            </div>
            <div class= "row" id="section4">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Surgeries"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("Click on Add to bring up the Add/Edit Issue pop-up page");?>.
                        <button type="button" class="btn btn-secondary btn-sm oe-no-float"><?php echo xlt("Add"); ?></button>
                    </p>

                    <p><?php echo xlt("The Type would be Surgery");?>.</p>

                    <p><?php echo xlt("Select a surgery if it is listed in the drop-down box or enter a new surgery name under Title");?>.</p>

                    <p><?php echo xlt("Leave coding blank as currently you can only enter ICD10 codes");?>.</p>

                    <p><?php echo xlt("Begin and End date if information is available");?>.</p>

                    <p><?php echo xlt("The rest may be left blank if so preferred");?>.</p>

                    <p><?php echo xlt("Click Save");?>.
                        <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("Save"); ?></button>
                    </p>
                </div>
            </div>
            <div class= "row" id="section5">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Dental Issues"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("Click on Add to bring up the Add/Edit Issue pop-up page");?>.
                        <button type="button" class="btn btn-secondary btn-sm oe-no-float"><?php echo xlt("Add"); ?></button>
                    </p>

                    <p><?php echo xlt("The Type would be Dental Issues");?>.</p>

                    <p><?php echo xlt("Fill in the necessary details in a fashion similar to that for Medical Problems");?>.</p>

                    <p><?php echo xlt("Click Save");?>.
                        <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("Save"); ?></button>
                    </p>

                    <p><?php echo xlt("More information about Issues can be found here");?>.
                        <a href="https://www.open-emr.org/wiki/index.php/Issues_&_Immunizations" rel="noopener" target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                    </p>
                </div>
            </div>

        </div><!--end of container div-->
    </body>
</html>

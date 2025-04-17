<?php

/**
 * Medical Dashboard Help.
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
    <title><?php echo xlt("Medical Dashboard Help");?></title>
    </head>
    <body>
        <div class="container oe-help-container">
            <div>
                <h2 class="text-center"><a name='entire_doc'><?php echo xlt("Medical Dashboard Help");?></a></h2>
            </div>
            <div class= "row">
                <div class="col-sm-12">
                    <p><?php echo xlt("The dashboard is the central location for convenient access the patient's medical record");?>.</p>

                    <p><i class="fa fa-lightbulb-o fa-lg  oe-text-green" aria-hidden="true"></i>&nbsp <?php echo xlt("To help familiarize you with the various components of the Dashboard page it is suggested that you reduce the size of the browser to cover half the viewport, resize the help pop-up by clicking and dragging the bottom right corner of the pop-up. Open another instance of the browser and resize it to cover the other half of the viewport, login to openEMR");?>.</p>

                    <p><?php echo xlt("The Dashboard page is divided into three sections");?>:</p>

                    <ul>
                        <li><a href="#section1"><?php echo xlt("Header");?></a></li>
                        <li><a href="#section2"><?php echo xlt("Nav Bar");?></a></li>
                        <li><a href="#section3"><?php echo xlt("Data Section");?></a></li>
                    </ul>
                </div>
            </div>
            <div class= "row" id="section1">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Header"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("The header section will reveal patient specific information across most pages related to the patient's medical record");?>.</p>

                    <p><strong><?php echo xlt("E-PRESCRIBING"); ?> :</strong></p>

                    <p><?php echo xlt("If NewCrop eRx - the electronic prescription module, is enabled the NewCrop MedEntry and NewCrop Account Status buttons will be appear here");?>.
                        <button type="button" class="btn btn-secondary btn-add btn-sm oe-no-float"><?php echo xlt("NewCrop MedEntry"); ?></button>
                        <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("NewCrop Account Status"); ?></button>
                    </p>

                    <p><i class="fa fa-exclamation-triangle oe-text-red"  aria-hidden="true"></i> <strong><?php echo xlt("You will need Administrator privileges to setup the NewCrop service and has to be setup in conjunction with technical support from the NewCrop eRx service");?>.</strong></p>

                    <p><?php echo xlt("This module is subscription based and needs to be enabled from Administration > Globals > Connectors > Enable NewCrop eRx Service");?>.</p>

                    <p><?php echo xlt("The NewCrop eRx Partner Name, NewCrop eRx Name and NewCrop eRx Password will be provided by the vendor");?>.</p>

                    <p><?php echo xlt("The rest of the boxes related to the NewCrop eRx service can be left at default values");?>.</p>

                    <p><?php echo xlt("This module is well integrated with openEMR, there are however two non-subscription based alternatives, Weno and Allscripts that can be used instead");?>.</p>

                    <p><?php echo xlt("The Weno Exchange is well integrated with openEMR and is not subscription based");?>.</p>

                    <p><?php echo xlt("The Allscripts solution integrates the Allscripts ePrescribe web site with openEMR");?>.</p>

                    <p><?php echo xlt("Further information regarding using the e-prescribing modules can be found by clicking this link");?>
                        <a href="https://www.open-emr.org/wiki/index.php/OpenEMR_ePrescribe" rel="noopener" target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                    .</p>

                    <p><?php echo xlt("At present e-prescribing from openEMR is possible only in the United States");?>.</p>

                    <p><strong><?php echo xlt("PATIENT PORTAL"); ?> :</strong></p>

                    <p><?php echo xlt("Information regarding the Patient Portal is also shown in the header section");?>.</p>

                    <p><?php echo xlt("There are multiple options regarding patient portals and information on how to setup the patient portal is available here");?>
                        <a href="https://www.open-emr.org/wiki/index.php/Patient_Portal" rel="noopener" target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                    .</p>

                    <p><?php echo xlt("To enable Patient Portal go to Administration > Portal > Enable Version 2 Onsite Patient Portal, Enable Offsite Patient Portal or Enable CMS Portal");?>.</p>

                    <p><?php echo xlt("Enable only one type of portal by checking the relevant check-box");?>.</p>

                    <p><i class="fa fa-exclamation-triangle oe-text-red"  aria-hidden="true"></i> <strong><?php echo xlt("You will need Administrator privileges to enable the patient portal");?>.</strong></p>

                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <?php echo xlt("If the patient has not authorized patient portal then a Patient has not authorized the Patient Portal message will be shown here");?>.</p>

                    <p><?php echo xlt("To authorize the patient portal for the patient go to Dashboard > Demographics > Edit > Choices and select Yes in Allow Patient Portal drop-down box and Save");?>.
                        <button type="button" class="btn btn-secondary btn-sm oe-no-float"><?php echo xlt("Edit"); ?></button>
                        <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("Save"); ?></button>
                    </p>

                    <p><?php echo xlt("If the Online Patient portal is enabled there will be either a button that says Create Online Portal Credentials provided the patient has given permission to access the online patient portal or a message that says Patient has not authorized the Patient Portal");?>.
                        <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("Create Online Portal Credentials"); ?></button>
                    </p>

                    <p><?php echo xlt("If the Offsite Patient portal is enabled there will be either a button that says Create Offsite Portal Credentials provided the patient has given permission to access the online patient portal or a message that says Patient has not authorized the Patient Portal");?>.
                        <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("Create Offsite Portal Credentials"); ?></button>
                    </p>

                    <p><?php echo xlt("Clicking on the Create Online/Offsite Portal Credentials button will generate a username and password for the patient that has to be given to the patient");?>.</p>

                    <p><?php echo xlt("These credentials will be used by the patient to login to the patient portal for the first time");?>.</p>

                    <p><?php echo xlt("The patient will have to change their credentials at the first login");?>.</p>

                    <p><?php echo xlt("If the Online/Offsite Portal Credentials has already been set the button will change to");?>.
                        <button type="button" class="btn btn-secondary btn-undo btn-sm oe-no-float"><?php echo xlt("Reset Online Portal Credentials"); ?></button>
                        <button type="button" class="btn btn-secondary btn-undo btn-sm oe-no-float"><?php echo xlt("Reset Offsite Portal Credentials"); ?></button>
                    </p>

                    <p><strong><?php echo xlt("DECEASED NOTIFICATION"); ?> :</strong></p>

                    <p><?php echo xlt("If the patient is deceased then the deceased notification will appear in red in this section");?>.</p>

                    <p><?php echo xlt("For the deceased notification to appear the date of death must be noted under Medical Dashboard > Edit Demographics > Misc");?>.</p>

                    <p><?php echo xlt("The help icon will let you access context sensitive help for each of the pages accessed");?>. <i class="fa fa-question-circle fa-lg oe-help-redirect" aria-hidden="true"></i></p>

            </div>
            </div>
            <div class= "row" id="section2">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Nav Bar"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("The Nav Bar allows one to quickly navigate to various parts of the patient's medical record");?>.</p>

                    <p><?php echo xlt("The default installation has the following items");?>:</p>

                        <ul>
                            <li><?php echo xlt("Dashboard"); ?></li>
                            <li><?php echo xlt("History"); ?></li>
                            <li><?php echo xlt("Report"); ?></li>
                            <li><?php echo xlt("Documents"); ?></li>
                            <li><?php echo xlt("Transactions"); ?></li>
                            <li><?php echo xlt("Issues"); ?></li>
                            <li><?php echo xlt("Ledger"); ?></li>
                            <li><?php echo xlt("External Data"); ?></li>
                        </ul>

                    <p><?php echo xlt("Dashboard - summarizes all patient related information");?>.</p>

                    <p><?php echo xlt("History - patient's past medical history, family history, personal history");?>.</p>

                    <p><?php echo xlt("Report - Generates and downloads the patient's Continuity of Care Record (CCR), Continuity of Care Document (CCD) and Patient Report");?>.</p>

                    <p><?php echo xlt("Documents - a repository of the patient's scanned/faxed paper documents. It also the place to download patient specific templates");?>.</p>

                    <p><?php echo xlt("Transactions - lists various notes about happenings in a patient's chart with respect to billing, legal, patient request, physician request and also generates a patient referral or counter-referral");?>.</p>

                    <p><?php echo xlt("Issues - summarizes the patient's medical problems, allergies, medications, surgeries and dental issues");?>.</p>

                    <p><?php echo xlt("Ledger - Summarizes and tabulates all the charges, payments, adjustments and balances for all encounters pertaining to the patient");?>.</p>

                    <p><?php echo xlt("External Data - any external data linked to either encounters or procedures");?>.</p>

                    <p><?php echo xlt("Additional information about the individual pages can be found in their respective help files");?>.</p>
                </div>
            </div>
            <div class= "row" id="section3">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Data Section"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("The data section of the dashboard page lists all pertinent items related to a patient");?>.</p>

                    <p><?php echo xlt("These items can be edited if the user has sufficient privilege");?>.</p>

                    <p><?php echo xlt("Some of these data sections can be turned off if not being utilized by the clinic. Go to Admin, Config, Appearance to hide the cards to streamline the dashboard"); ?></p>

                    <p><?php echo xlt("Billing - provides a summary of the balances - Patient Balance Due, Insurance Balance Due, Total Balance Due and lists the name of the Primary Insurance along with its effective date");?>.</p>

                    <p><?php echo xlt("Demographics - patient demographics and insurance information");?>.</p>

                    <p><?php echo xlt("Patient Reminders - a list reminders for preventive or follow-up care according to patient preferences based on demographic data, specific conditions, and/or medication list as well as the status of the notification");?>
                        <a href="https://www.open-emr.org/wiki/index.php/Patient_Reminders" rel="noopener" target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                    .</p>

                    <p><?php echo xlt("Disclosures - Record disclosures made for treatment, payment, and health care operations with date, time, patient identification (name or number), user identification (name or number), and a description of the disclosure");?>
                        <a href="https://www.open-emr.org/wiki/index.php/7._Recording_Disclosure" rel="noopener" target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                    .</p>

                    <p><?php echo xlt("Amendments - Enable a user to electronically select the record affected by a patient’s request for amendment and either append the amendment to the affected record or include a link that indicates the amendment’s location");?>
                        <a href="https://www.open-emr.org/wiki/index.php/Amendments_(MU2)" rel="noopener" target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                    .</p>

                    <p><?php echo xlt("ID Card/Photos - will display any ID Card or patient photo that has been uploaded to Documents > Patient Information > Patient ID Card and Patient Photograph folders");?>.</p>

                    <p><i class="fa fa-exclamation-triangle oe-text-red"  aria-hidden="true"></i> <strong><?php echo xlt("Please ensure that there is only one image file - jpeg, png or bmp in the Patient Photograph folder");?>.</strong></p>

                    <p><?php echo xlt("Clinical Reminders - is a widget that displays the Passive Alerts for a Clinical Decision Rule");?>.</p>

                    <p><?php echo xlt("A Clinical Decision Rule is patient specific information that is filtered and presented at appropriate times to enhance health and health care");?>.</p>

                    <p><?php echo xlt("A detailed guide on how to enable and setup a Clinical Decision rule is found here");?>
                        <a href="https://open-emr.org/wiki/images/c/ca/Clinical_Decision_Rules_Manual.pdf" rel="noopener" target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                    .</p>

                    <p><?php echo xlt("Once a rule is setup it can be enabled for a particular patient");?>.</p>

                    <p><?php echo xlt("Upon reaching a predetermined point, either a date or value, the rule will trigger one or more events");?>:</p>
                        <ul>
                            <li><?php echo xlt("Active Alert - that presents as a pop-up notification when a patient's chart is entered"); ?></li>
                            <li><?php echo xlt("Passive Alert - that will be displayed in the Clinical Reminders widget section"); ?></li>
                            <li><?php echo xlt("Patient Reminder - that is used to communicate relevant information pertaining to that particular Clinical Decision Rule and is shown in the Patient Reminders widget as Well as under Administration > Patient Reminders"); ?></li>
                         </ul>

                    <p><?php echo xlt("More information about Clinical Decision Rule can be found here");?>
                        <a href="https://www.open-emr.org/wiki/index.php/CDR_User_Manual" rel="noopener" target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                    .</p>

                    <p><?php echo xlt("Appointments - shows all future appointments as well as Recalls");?>.</p>

                    <p><?php echo xlt("Recurrent Appointments - shows all recurring appointments");?>.</p>

                    <p><?php echo xlt("Past Appointments - will show all past appointments");?>.</p>

                    <p><?php echo xlt("Medical Problems - will show the patient's medical issues, Issues > Medical Problems");?>.</p>

                    <p><?php echo xlt("Allergies - will show the allergies listed under Issues > Allergies. If eRx is enabled the allergy list has to be entered on the eRx page");?>.</p>

                    <p><?php echo xlt("Medications - lists the medications under Issues > Medications. If eRx is enabled the medication list has to be entered on the eRx page");?>.</p>

                    <p><?php echo xlt("Immunizations - lists immunization history and allows for adding new entries or editing existing ones");?>.</p>

                    <p><?php echo xlt("Prescription - lists the prescriptions of the current patient");?>.</p>

                    <p><?php echo xlt("Tracks - if the Track Anything feature is enabled it will display a list of values that can be tracked and graphed");?>
                        <a href="https://www.open-emr.org/wiki/index.php/Track_Anything_Form" rel="noopener" target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                    .</p>
                </div>
            </div>

        </div><!--end of container div-->
        <script>
           $('#show_hide').click(function() {
                var elementTitle = $('#show_hide').prop('title');
                var hideTitle = '<?php echo xla('Click to Hide'); ?>';
                var showTitle = '<?php echo xla('Click to Show'); ?>';
                $('.hideaway').toggle('1000');
                $(this).toggleClass('fa-eye-slash fa-eye');
                if (elementTitle == hideTitle) {
                    elementTitle = showTitle;
                } else if (elementTitle == showTitle) {
                    elementTitle = hideTitle;
                }
                $('#show_hide').prop('title', elementTitle);
            });
        </script>
    </body>
</html>

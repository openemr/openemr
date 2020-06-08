<?php

/**
 * Report Dashboard Help.
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
    <title><?php echo xlt("Patient Reports Help");?></title>
    <style>
        .oe-help-add-info{
            padding:15px;
            border:6px solid;
            font-style: italic;
        }
    </style>
    </head>
    <body>
        <div class="container oe-help-container">
            <div>
                <h2 class="text-center"><a name='entire_doc'><?php echo xlt("Patient Reports Help");?></a></h2>
            </div>
            <div class= "row">
                <div class="col-sm-12">
                    <p><?php echo xlt("Reports consisting of various portions of the patient's medical record can be created here");?>.</p>

                    <p><?php echo xlt("There are three main types of reports that can be created, two pertain to continuity of ongoing care - Continuity of Care Record (CCR) and Continuity of Care Document (CCD) and the third - Patient Report that creates a document containing various sections of the patient's medical record including demographics, medical issues, procedures and encounters. It also has the ability to include all or any of the scanned documents in the patient's chart");?>.</p>

                    <p><?php echo xlt("This help file is divided into four sections");?>:</p>

                    <ul>
                        <li><a href="#section1"><?php echo xlt("Continuity of Care Record (CCR)");?></a></li>
                        <li><a href="#section2"><?php echo xlt("Continuity of Care Document (CCD)");?></a></li>
                        <li><a href="#section3"><?php echo xlt("Patient Report");?></a></li>
                        <li><a href="#section4"><?php echo xlt("Enabling EMR Direct phiMail");?></a></li>
                    </ul>

                    <p><i class="fa fa-lightbulb-o fa-lg  oe-text-green" aria-hidden="true"></i>&nbsp <?php echo xlt("To help explore the various components of the Report page especially if you want to use it as an instruction manual it is suggested that you reduce the size of the browser to cover half the viewport, resize the help pop-up by clicking and dragging the bottom right corner of the pop-up. Open another instance of the browser and resize it to cover the other half of the viewport, login to openEMR ");?>.</p>
                </div>
            </div>
            <div class= "row" id="section1">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Continuity of Care Record (CCR)"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("The Continuity of Care Record (CCR) is the clinical record of the patient’s current and historical health care status");?>.</p>

                    <p><?php echo xlt("Basic patient information is included, such as patient and provider information, insurance, patient health status, recent care provided, care plan information, and reason for referral or transfer");?>.</p>

                    <p><?php echo xlt("It is intended to include only the information that is critical to effectively continue care");?>.</p>

                    <p><?php echo xlt("For those interested in details of what constitutes a Continuity of Care Record (CCR) click on the eye icon");?> &nbsp;<i class="show_hide fa fa-eye fa-lg small" title="<?php echo xla('Click to Show'); ?>"></i></p>

                    <div id="ccr_details" class='hideaway oe-help-heading oe-help-add-info' style='display: none;'>
                        <p><?php echo xlt("The Continuity of Care Record (CCR) has been developed jointly by ASTM International - an organization that is involved in the development and delivery of voluntary consensus, the Massachusetts Medical Society (MMS), the Health Information Management and Systems Society (HIMSS), the American Academy of Family Physicians (AAFP), and the American Academy of Pediatrics");?>.</p>

                        <p><?php echo xlt("The basis for CCR is a Patient Care Referral Form developed by the Massachusetts Department of Public Health");?>.</p>

                        <p><?php echo xlt("In total, there are seventeen sections that can be included within a Continuity of Care Record (CCR)");?>.</p>

                        <p id = "my_hide"><?php echo xlt("All sections are not required, rather, they are available based on the information a practitioner believes is critical data for the patient at any given moment in time");?> &nbsp <i class="show_hide fa fa-eye fa-lg small" title="<?php echo xla('Click to Show'); ?>"></i></p>

                            <div class='hideaway' id='ccr_sections'style='display: none;'>
                                <ul>
                                    <li><?php echo xlt("Patient Demographics"); ?></li>
                                    <li><?php echo xlt("Immunizations"); ?></li>
                                    <li><?php echo xlt("Vital Signs"); ?></li>
                                    <li><?php echo xlt("Problems & Diagnoses"); ?></li>
                                    <li><?php echo xlt("Insurance Information"); ?></li>
                                    <li><?php echo xlt("Health Care Providers"); ?></li>
                                    <li><?php echo xlt("Encounter Information"); ?></li>
                                    <li><?php echo xlt("Allergies/Alerting Data"); ?></li>
                                    <li><?php echo xlt("Appropriate Results"); ?></li>
                                    <li><?php echo xlt("Medication"); ?></li>
                                    <li><?php echo xlt("Procedures"); ?></li>
                                    <li><?php echo xlt("Results"); ?></li>
                                    <li><?php echo xlt("Necessary Medical Equipment"); ?></li>
                                    <li><?php echo xlt("Social History"); ?></li>
                                    <li><?php echo xlt("Statistics"); ?></li>
                                    <li><?php echo xlt("Family History"); ?></li>
                                    <li><?php echo xlt("Care Plan"); ?></li>
                                </ul>
                            </div>
                        <div>
                        <p><?php echo xlt("The core dataset consists of");?>: &nbsp;<i class="show_hide fa fa-eye fa-lg small" title="<?php echo xla('Click to Show'); ?>"></i></p>

                        <div class='hideaway' id='ccr_core'style='display: none;'>

                            <ul>
                                <li><?php echo xlt('Document identifying information');?>*</li>
                                <li><?php echo xlt('Patient identifying information');?>*</li>
                                <li><?php echo xlt('Patient insurance/financial information');?>*</li>
                                <li><?php echo xlt('Advance Directives');?></li>
                                <li><?php echo xlt('Patient’s health status');?>*</li>
                                <li><?php echo xlt('Care documentation');?>*</li>
                                <li><?php echo xlt('Care plan recommendations');?>*</li>
                                <li><?php echo xlt('List of health care practitioners');?></li>
                            </ul>
                            <p><?php echo xlt("The items that have asterisks after them are mandated");?>.
                        </div>
                        </div>
                    </div>

                    <p><?php echo xlt("In openEMR the Continuity of Care Record (CCR) reports to be generated can be limited to a specific date range by checking the Use Date Range check-box and entering the desired date range");?>.</p>

                    <p><?php echo xlt("There are three options available when you create a Continuity of Care Record (CCR)");?>:</p>
                        <ul>
                            <li><?php echo xlt("Generate Report - that opens the CCR in a separate tab on the browser"); ?> <button type="button" class="btn btn-secondary btn-sm btn-save oe-no-float"><?php echo xlt("Generate Report"); ?></button></li>
                            <li><?php echo xlt("Download - the created CCR is downloaded as a pdf file to the downloads from the browser"); ?> <button type="button" class="btn btn-secondary btn-sm btn-download oe-no-float"><?php echo xlt("Download"); ?></button></li>
                            <li><?php echo xlt("Transmit - securely transmit the CCR using phiMail Direct Messaging"); ?> <button type="button" class="btn btn-secondary btn-sm btn-transmit oe-no-float"><?php echo xlt("Transmit"); ?></button></li>
                        </ul>

                    <p><?php echo xlt("Clicking the Transmit button will open up a text box where the Direct address of the recipient needs to be entered");?>.</p>

                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <strong><?php echo xlt("Note: this is not an email address");?>.</strong></p>

                    <p><?php echo xlt("More information about what phiMail is and how to set it up is available here");?>. <a href="#section4"><?php echo xlt("Enabling EMR Direct phiMail");?></a></p>
                </div>
            </div>
            <div class= "row" id="section2">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Continuity of Care Document (CCD)"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("Continuity of Care Document (CCD) is an electronic document containing patient specific information that aids in continuity of care");?>.</p>

                    <p><?php echo xlt("It contains structured data that is included in the Continuity of Care Record (CCR) set to the the Clinical Document Architecture standard");?>.</p>

                    <p><?php echo xlt("Click to learn more about the Clinical Document Architecture (CDA) and Consolidated CDA (C-CDA)");?>  &nbsp;<i class="show_hide fa fa-eye fa-lg small" title="<?php echo xla('Click to Show'); ?>"></i></p>

                    <div id="ccd_details" class='hideaway oe-help-heading oe-help-add-info' style='display: none;'>

                        <p><?php echo xlt("CDA or Clinical Document Architecture is a document standard developed by the HL7 organization");?>.</p>

                        <p><?php echo xlt("CDA defines building blocks which can be used to contain healthcare data elements that can be captured, stored, accessed, displayed and transmitted electronically for use and reuse in many formats");?>.</p>

                        <p><?php echo xlt("Clinical documents are produced by arranging or limiting CDA elements in defined ways using templates and Implementation Guides (IG)");?>.</p>

                        <p><?php echo xlt("The CDA is a plain text document that is coded using the Extensible Markup Language (XML) and contains a Header and Body");?>.</p>

                        <p><?php echo xlt("The Header defines the context for the clinical document as a whole");?>.</p>

                        <p><?php echo xlt("The Body contains the clinical data that may be structured and organized as one or more sections or may be an unstructured blob of data");?>.</p>

                        <p><?php echo xlt("Each Section has one Narrative Block and zero to many coded Entries");?>.</p>

                        <p><?php echo xlt("The Narrative Block contains information that is rendered as readable text");?>.</p>

                        <p><?php echo xlt("The Entries contain data used for further computer processing");?>.</p>

                        <p><?php echo xlt("Examples of Sections are - Medications, Allergies, Vital Signs");?>.</p>

                        <p><?php echo xlt("The initial efforts led to the creation of duplicative and conflicting Implementation Guides (IGs) published by different standards organizations");?>.</p>

                        <p><?php echo xlt("The end result was a confusing collection of documents containing ambiguous and/or conflicting information");?>.</p>

                        <p><?php echo xlt("To help simplify implementations, commonly used templates were harmonized from existing CDA implementation guides and consolidated into a single implementation guide – the C-CDA Implementation Guide (IG) (07/2012)");?>.</p>

                        <p><?php echo xlt("There are nine C-CDA templates that are currently defined");?>:  &nbsp;<i class="show_hide fa fa-eye fa-lg small" title="<?php echo xla('Click to Show'); ?>"></i></p>

                            <div class='hideaway' id='c-cda_sections'style='display: none;'>
                                <ul>
                                    <li><?php echo xlt("Continuity of Care Document"); ?></li>
                                    <li><?php echo xlt("Consultation Notes - 2008"); ?></li>
                                    <li><?php echo xlt("Discharge Summary - 2009"); ?></li>
                                    <li><?php echo xlt("Imaging Integration, and DICOM Diagnostic Imaging Reports - 2009"); ?></li>
                                    <li><?php echo xlt("History and Physical 2008"); ?></li>
                                    <li><?php echo xlt("Operative Note - 2009"); ?></li>
                                    <li><?php echo xlt("Progress Note - 2010"); ?></li>
                                    <li><?php echo xlt("Procedure Note - 2010"); ?></li>
                                    <li><?php echo xlt("Unstructured Documents - 2010"); ?></li>
                                </ul>
                            </div>
                        <p><?php echo xlt("The Continuity of Care Document (CCD) and Continuity of Care Record (CCR) were both selected as acceptable formats to extract information for clinical care summaries as a part of Meaningful Use Stage 1");?>.</p>

                        <p><?php echo xlt("In the second stage of Meaningful Use, the CCD, but not the CCR, was included as part of the standard for clinical document exchange");?>.</p>
                    </div>

                    <p><?php echo xlt("Continuity of Care Document (CCD) is meant to be created at the conclusion of an encounter prior to transfer of care to enable the next provider to easily access such information");?>.</p>

                    <p><?php echo xlt("It serves as a necessary bridge to a different environment, often with new clinicians who know nothing about the patient, enabling next provider to easily");?></p>

                        <ul>
                            <li><?php echo xlt("Access core data set of patient information at the beginning of an encounter"); ?></li>
                            <li><?php echo xlt("Update information when the patient goes to another provider, to support safety, quality, and continuity of patient care"); ?></li>
                        </ul>

                    <p><?php echo xlt("There are three options available when you create a Continuity of Care Document (CCD)");?>:</p>
                        <ul>
                            <li><?php echo xlt("Generate Report - that opens the CCD in a separate tab on the browser"); ?> <button type="button" class="btn btn-secondary btn-sm btn-save oe-no-float"><?php echo xlt("Generate Report"); ?></button></li>
                            <li><?php echo xlt("Download - the created CCD is downloaded as a pdf file to the downloads from the browser"); ?> <button type="button" class="btn btn-secondary btn-sm btn-download oe-no-float"><?php echo xlt("Download"); ?></button></li>
                            <li><?php echo xlt("Transmit - securely transmit the CCD using phiMail Direct Messaging"); ?> <button type="button" class="btn btn-secondary btn-sm btn-transmit oe-no-float"><?php echo xlt("Transmit"); ?></button></li>
                        </ul>

                    <p><?php echo xlt("Clicking the Transmit button will open up a text box where the Direct address of the recipient needs to be entered");?>.</p>

                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <strong><?php echo xlt("Note: this is not an email address");?>.</strong></p>

                    <p><?php echo xlt("More information about what phiMail is and how to set it up is available here");?>. <a href="#section4"><?php echo xlt("Enabling EMR Direct phiMail");?></a></p>
                </div>
            </div>
            <div class= "row" id="section3">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Patient Report"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("Creates a report that contains various sections of the patient's medical record");?>.</p>

                    <p><?php echo xlt("The created report can contain as much or as little information based on the need");?>.</p>

                    <p><?php echo xlt("By default only Demographics and Billing information is selected");?>.</p>

                    <p><?php echo xlt("There are two buttons that enable you to Check or Clear all available check-boxes");?>.
                        <button type="button" class="btn btn-secondary btn-sm btn-save oe-no-float"><?php echo xlt("Check All"); ?></button>
                        <button type="button" class="btn btn-secondary btn-sm btn-undo oe-no-float"><?php echo xlt("Clear All"); ?></button>
                    </p>

                    <p><?php echo xlt("Alternatively you could clear all selections and select only the items that you want to be a part of the report");?>.</p>

                    <p><?php echo xlt("To include the scanned documents that are a part of the patient's record select the desired records by check the relevant check-boxes");?>.</p>

                    <p><?php echo xlt("There are two options for the records that will be created");?>:</p>
                        <ul>
                            <li><?php echo xlt("Generate Report that creates a report and displays it in a separate tab on the browser "); ?> <button type="button" class="btn btn-secondary btn-sm btn-save oe-no-float"><?php echo xlt("Generate Report"); ?></button></li>
                            <li><?php echo xlt("Download report as a pdf file into the browser's download folder"); ?> <button type="button" class="btn btn-secondary btn-sm btn-download oe-no-float"><?php echo xlt("Download PDF"); ?></button></li>
                        </ul>

                    <p><?php echo xlt("When the generated report is displayed in a separate tab there is an option that lets you view a Printable Version that can be printed");?>.</p>

                    <p><?php echo xlt("If the report is sent electronically all steps needed for safe and secure transmission of Protected Health Information (PHI) must be followed");?>.</p>

                <div>
            </div>
            <div class= "row" id="section4">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Enabling EMR Direct phiMail"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("EMR Direct phiMail is a secure, scalable, standards-based way for participants to send authenticated, encrypted health information directly to known, trusted recipients over the Internet");?>.</p>

                    <p><i class="fa fa-exclamation-triangle oe-text-red"  aria-hidden="true"></i> <strong><?php echo xlt("You will need Administrator privileges to setup phiMail Direct Messaging");?>.</strong></p>

                    <p><?php echo xlt("The first step is signing up for a production Direct messaging account with EMR Direct by registering on their website");?>.
                        <a href="https://www.emrdirect.com/subscribe" rel="noopener" target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                    </p>

                    <p><?php echo xlt("Subscribers will receive the username, password, and server address information with which to configure OpenEMR");?>.</p>

                    <p><?php echo xlt("Go to Administration > Globals > Connectors");?>.</p>

                    <p><?php echo xlt("Check the Enable phiMail Direct Messaging Service check-box");?>.</p>

                    <p><?php echo xlt("Enter the Server Address, Username, and Password provided to you");?>.</p>

                    <p><?php echo xlt("The server address will be of the form ");?> <b>ssl://servername.example.com:32541</b></p>

                    <p><?php echo xlt("Replace the hostname and port with the values provided to you by EMR Direct");?>.</p>

                    <p><?php echo xlt("The Username is your Direct Address");?>.</p>

                    <p><?php echo xlt("Do not enter the server URL into your browser address bar, as this will not work");?>.</p>

                    <p><?php echo xlt("Specify the OpenEMR user who will receive notification of new incoming Direct messages");?>.</p>

                    <p><?php echo xlt("Enter their OpenEMR username in the notification user field");?>.</p>

                    <p><?php echo xlt("Specify the interval for automatic message checking, 5 or 10 minutes as a starting point");?>.</p>

                    <p><?php echo xlt("Installations processing a large number of Direct messages may want a shorter interval");?>.</p>

                    <p><?php echo xlt("To disable automatic message checking go to Administration > Globals > Connectors  and set phiMail Message Check Interval to 0 (zero) ");?>.</p>

                    <p><?php echo xlt("Disabling automatic checking would be appropriate if message checking is managed through another mechanism, such as a system cron job");?>.</p>

                    <p><?php echo xlt("In order to send Continuity of Care Record (CCR) and /or Continuity of Care Document (CCD) optionally check phiMail Allow CCD Send and/or phiMail Allow CCR Send to enable the Transmit feature for these data types");?>.</p>

                    <p><?php echo xlt("If you do not select at least one of these, OpenEMR will operate in a receive-only mode");?>.</p>

                    <p><?php echo xlt("Click the Save button");?>.
                        <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("Save"); ?></button>
                    </p>

                    <p><?php echo xlt("To receive error notifications from the Direct Messaging service a valid Notification Email Address needs to be entered for the user named in Administration > Globals > Connectors > phiMail notification user");?>.</p>

                    <p><?php echo xlt("Install the EMR Direct trust anchor certificate");?>.</p>

                    <p><strong><?php echo xlt("SENDING A MESSAGE"); ?>:</strong></p>

                    <p><?php echo xlt("At present only Continuity of Care Record (CCR) Continuity of Care Document (CCD) can be sent using phiMail");?>.</p>

                    <p><?php echo xlt("Click the Transmit button to reveal the Direct address box");?>.
                        <button type="button" class="btn btn-secondary btn-transmit btn-sm oe-no-float"><?php echo xlt("Transmit"); ?></button>
                    </p>

                    <p><?php echo xlt("Enter the Direct address and click Send CCR/Send CCD as the case may be");?>.
                        <button type="button" class="btn btn-secondary btn-send-msg btn-sm oe-no-float"><?php echo xlt("Send CCR"); ?></button>
                        <button type="button" class="btn btn-secondary btn-send-msg btn-sm oe-no-float"><?php echo xlt("Send CCD"); ?></button>
                    </p>

                    <p><strong><?php echo xlt("RECEIVING A MESSAGE"); ?> :</strong></p>

                    <p><?php echo xlt("Received messages are processed and a new Patient Note is delivered to a specified user and appears in that user's Message Center");?>.</p>

                    <p><?php echo xlt("These Patient notes are sent without an assigned patient");?>.</p>

                    <p><?php echo xlt("Open and review the message content and any attachments");?>.</p>

                    <p><?php echo xlt("Assign the message to the correct patient by clicking Patient: Click to select, assign a Type and forward the message on to the correct clinician or staff member");?>.</p>

                    <p><?php echo xlt("More information on the use of phiMail messages is available here");?>.
                        <a href="https://www.open-emr.org/wiki/index.php/Direct" rel="noopener" target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                    </p>

                    <p><?php echo xlt("Detailed information on how to setup and troubleshoot phiMail messages is available here");?>.
                        <a href="https://raw.githubusercontent.com/openemr/openemr/master/Documentation/Direct_Messaging_README.txt" rel="noopener" target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                    </p>
                </div>
            </div>
        </div><!--end of container div-->
        <script>
           $('.show_hide').click(function() {
                var elementTitle = $(this).prop('title');
                var hideTitle = '<?php echo xla('Click to Hide'); ?>';
                var showTitle = '<?php echo xla('Click to Show'); ?>';
                //$('.hideaway').toggle('1000');
                $(this).parent().parent().closest('div').children('.hideaway').toggle('1000');
                if (elementTitle == hideTitle) {
                    elementTitle = showTitle;
                    $(this).toggleClass('fa-eye-slash fa-eye');
                } else if (elementTitle == showTitle) {
                    elementTitle = hideTitle;
                    $(this).toggleClass('fa-eye fa-eye-slash');
                }
                $(this).prop('title', elementTitle);
            });
        </script>
    </body>
</html>

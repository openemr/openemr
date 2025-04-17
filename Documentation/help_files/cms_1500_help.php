<?php

/**
 * CMS 1500 Help.
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
    <title><?php echo xlt("Health Insurance Claim Form");?></title>
    <style>
        @media only screen and (max-width: 768px) {
           [class*="col-"] {
           width: 100%;
           text-align:left!Important;
            }
        }
    </style>
    </head>
    <body>
        <div class="container oe-help-container">
            <div>
                <h2 class="text-center"><a name='entire_doc'><?php echo xlt("Additional information to process paper claims");?></a></h2>
            </div>
            <div class="row">
                <p><?php echo xlt("The information entered in this form will be used to complete a paper claim");?>.</p>

                <p><?php echo xlt("Relevant for insurance claim submission in the United States");?>.</p>

                <p><?php echo xlt("A standardized form called the Health Insurance Claim Form is used to submit claims");?>.</p>

                <p><?php echo xlt("The Health Insurance Claim Form is often referred to by its form number CMS 1500 (HCFA 1500)");?>.</p>

                <p><?php echo xlt("Although the HCFA-1500 originally was developed for submitting Medicare claims, it eventually was accepted by all commercial/ private insurance carriers to facilitate the standardization of the claims process");?>.</p>

                <p><?php echo xlt("It is the basic paper claim form prescribed by many payers for claims submitted by individual doctors & practices, nurses, and professionals, including therapists, chiropractors, and out-patient clinics, and in some cases, for ambulance services");?>.</p>

                <p><?php echo xlt("The 1500 Claim Form may also be used to report patient encounter data to federal, state, and/or other public health agencies");?>.</p>

                <p><?php echo xlt("HCFA 1500 or CMS 1500 - both refer to the same form. Prior to 2001 the federal agency within the United States Department of Health and Human Services (HHS) that administers the Medicare program was called Health Care Financing Administration (HCFA) since then it is called Centers for Medicare & Medicaid Services (CMS)");?>.</p>

                <p><?php echo xlt("CMS 1500 was revised on 2012-02-01 and is hence called Form 1500 (02-12) and is set to expire on 2020-03-31. As of April 1, 2014 only the revised, 02-12 version is accepted.");?>.</p>

                <p><?php echo xlt("The UB-04 (CMS 1450) is a claim form used by hospitals, nursing facilities, in-patient, and other facility providers");?>.</p>

                <p><?php echo xlt("The 837P (Professional) is the standard format used by health care professionals and suppliers to transmit health care claims electronically");?>.</p>

                <p><?php echo xlt("The National Uniform Claim Committee (NUCC) is responsible for the maintenance of the 1500 Claim Form");?>.</p>

                <p><?php echo xlt("The Instruction manual on how to fill the CMS 1500 form can be found here");?>.<a href="http://www.nucc.org" rel="noopener" target="_blank">&nbsp;<i id="show_hide" class="fa fa-external-link-alt fa-lg small" title=<?php echo xlt("Click to Show");?>></i></a></p>

                <p><?php echo xlt("The CMS 1500 form has 33 boxes and is divided into two sections");?>.&nbsp;<a href="https://www.cms.gov/Medicare/CMS-Forms/CMS-Forms/Downloads/CMS1500.pdf" rel="noopener" target="_blank"><i id="show_hide" class="fa fa-eye fa-lg small" title=<?php echo xlt("Click to Show");?>></i></a></p>

                <p><?php echo xlt("Patient and Insured Information - Boxes 1 - 13 and Physician or Supplier Information - Boxes 14 - 33");?>.</p>

                <p><?php echo xlt("The information provided in the New Encounter Form and Fee Sheet in openEMR is used to generate the paper claim as well as electronic claims");?>.</p>

                <p><?php echo xlt("Some global parameters on the setup and printing of the generated claims can be set in Adminstration > Globals > Billing");?>.</p>

                <p><i class="fa fa-exclamation-triangle oe-text-red" aria-hidden="true"></i> <strong><?php echo xlt("You need administrator privileges to perform the setup"); ?>.</strong></p>

                <p><?php echo xlt("The miscellaneous billing options for HCFA-1500 form is used to provide additional information for an individual claim");?>.</p>

                <p><?php echo xlt("It is divided into two sections");?>:</p>

                <ul>
                    <li><a href="#select_option"><?php echo xlt("Select Options for Current Encounter");?></a></li>
                    <li><a href="#add_notes"><?php echo xlt("Additional Notes");?></a></li>
                </ul>
            </div>
            <div class= "row" id="select_option">
                <h4 class="oe-help-heading"><?php echo xlt("Select Options for Current Encounter"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("Enter needed information to help process this particular claim");?>.</p>

                <p><?php echo xlt("Checking any of the check boxes will result in a Yes on the CMS 1500 form");?>.</p>

                <p><?php echo xlt("Box 10 - This information indicates whether the patient’s illness or injury is related to employment, auto accident, or other accident");?>.</p>

                <p><?php echo xlt("Box 10 D - Is used to submit the Early and Periodic Screening, Diagnosis and Treatment (EPSDT) Referral Code");?>.</p>

                <p><?php echo xlt("Box 14 - It identifies the first date of onset of illness, the actual date of injury, or the LMP for pregnancy. Enter the date in the first box and select either Onset of Current Symptoms or Illness or LMP from the drop-down box");?>.</p>

                <p><?php echo xlt("Box 15 - The Other Date identifies additional date information about the patient’s condition or treatment. Enter the date in the first box and select a qualifier from the drop-down box");?>.</p>

                <p><?php echo xlt("Box 16 - Dates Patient Unable to Work in Current Occupation - is the time span the patient is or was unable to work");?>.</p>

                <p><?php echo xlt("Box 17 - The name entered is the referring provider, ordering provider or supervising provider who referred, ordered, or supervised the service(s) or supply(ies) on the claim. If multiple providers are involved, enter one provider using the following priority order: 1. Referring Provider, 2. Ordering Provider, 3. Supervising Provider.");?>.</p>

                <p><?php echo xlt("Box 18 - The “Hospitalization Dates Related to Current Services” would refer to an inpatient stay and indicates the admission and discharge dates associated with the service(s) on the claim");?>.</p>

                <p><?php echo xlt("Box 20 - Indicates that services have been rendered by an independent provider as indicated in Box 32 (Service Facility Location Information) and the related costs");?>.</p>

                <p><?php echo xlt("Box 22 - Medicaid Resubmission Code and Original Reference Number is the code and original reference number assigned by the destination payer or receiver to indicate a previously submitted claim or encounter");?>.</p>

                <p><?php echo xlt("Box 23 - Prior Authorization Number is the payer assigned number authorizing the service(s)");?>.</p>

                <p><?php echo xlt("X12 only: Replacement Claim, X12 only ICN resubmission No is used for electronic resubmission of claims. Requirement varies by insurer");?>.</p>
            </div>
            <div class= "row" id="add_notes">
                <h4 class="oe-help-heading"><?php echo xlt("Additional Notes"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("Additional Notes field is for local use in openEMR");?>.</p>

                <p><?php echo xlt("Used to enter information that pertains to this particular claim in order to facilitate processing and submitting this claim");?>.</p>

                <p><i class="fa fa-exclamation-circle oe-text-orange" aria-hidden="true"></i> <strong><?php echo xlt("It is not submitted with the claim");?>.</strong></p>
            </div>
        </div><!--end of container div-->
    </body>
</html>

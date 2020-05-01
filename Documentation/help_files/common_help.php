<?php

/**
 * New Encounter Help.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Header;

require_once("../../interface/globals.php");
?>
<!DOCTYPE html>
<html>
    <head>
    <?php Header::setupHeader();?>.
    <title><?php echo xlt("Creating and Managing Encounters");?>.</title>
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
                <h2 class="text-center"><a name='entire_doc'><?php echo xlt("Creating and Managing Encounters");?>.</a></h2>
            </div>
            <div class="row">
                <p><?php echo xlt("All patient interactions in OpenEMR are linked to encounters");?>.</p>

                <p><?php echo xlt("Clicking on the + button on the patient header panel opens the New Encounter Form");?>.</p>

                <p><?php echo xlt("This form allows you to enter details of a visit or patient interaction");?>.</p>

                <p><?php echo xlt("The information entered here is used to display a summary of the encounter. It is also used for billing purposes");?>.</p>

                <p><?php echo xlt("It consists of three sections ");?>:</p>
                <ul>
                    <li><a href="#visit_details"><?php echo xlt("Visit Details");?>.</a></li>
                    <li><a href="#visit_reason"><?php echo xlt("Reason for visit");?>.</a></li>
                    <li><a href="#link_issue"><?php echo xlt("Link/Add Issues (Injuries/Medical/Allergy) to Current Visit");?>.</a></li>
                </ul>
            </div>
            <div class= "row" id="visit_details">
                <h4 class="oe-help-heading"><?php echo xlt("Visit Details");?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("The minimum information required to create a new encounter is to select a visit category");?>.</p>

                <p><?php echo xlt("Date of Service is today's date by default. If needed it can be changed to any valid date");?>.</p>

                <p><?php echo xlt("Facility reflects the facility in which the visit occurred");?>.</p>

                <p><?php echo xlt("Billing Facility lets you choose the facility used for billing. To create and edit the billing facility go to Administration  > Facilities");?>.</p>

                <p><?php echo xlt("Sensitivity - lets you limit the information in this encounter to those with appropriate privileges");?>.</p>

                <p><?php echo xlt("The default is Normal, i.e. it is visible to everyone. Selecting High will restrict it to users belonging to the Physicians and Administrators groups by default");?>.</p>

                <p><i class="fa fa-exclamation-triangle oe-text-red" aria-hidden="true"></i> <strong><?php echo xlt("You need administrator privileges to give access to other groups");?>.</strong></p>

                <p><?php echo xlt("Access can be given to other groups by editing User Memberships or Groups and Access Controls in Administration > ACL");?>.</p>

                <p><?php echo xlt("Onset/hosp. date - is used for billing hospital encounters");?>.</p>

                <p><i class="fa fa-exclamation-triangle oe-text-red" aria-hidden="true"></i> <strong><?php echo xlt("You need administrator privileges to perform the changes to the values in the drop-down boxes.");?>.</strong></p>
            </div>
            <div class="row" id="visit_reason">
                <h4 class="oe-help-heading"><?php echo xlt("Reason for visit");?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("A few words to indicate the reason for the visit");?>.</p>

                <p><?php echo xlt("It will be displayed in the visit summary");?>.</p>

                <p><?php echo xlt("Also provides a short description of the visit in Patient Reports");?>.</p>
            </div>
            <div class="row" id="link_issue">
                <h4 class="oe-help-heading"><?php echo xlt("Link/Add Issues (Injuries/Medical/Allergy) to Current Visit");?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("An issue is a medical problem, allergy, medication, surgery or dental issue");?>.</p>

                <p><?php echo xlt("It is used to link problems with encounters and is also used to see which encounters are associated to a particular issue");?>.</p>

                <p><?php echo xlt("Clicking on the Add Issue button brings up the add issue pop-up box");?>.
                    <a class="btn btn-primary btn-sm oe-no-float oe-inline" style="color:white !Important"><?php echo xlt("Add Issue");?></a>
                </p>

                <p><?php echo xlt("The default issue type is set to Problem");?>.</p>

                <p><?php echo xlt("For each issue type there is a list of commonly selected titles which are customizable for your clinic's specific needs");?>.</p>

                <p><?php echo xlt("If the desired title is not included, it can be typed into the text box below the list");?>.</p>

                <p><?php echo xlt("Select an appropriate ICD/CPT code. This helps search for education material to give to the patient for that particular problem");?>.</p>

                <p><?php echo xlt("To link this encounter/consult to an existing issue, click the desired issue above to highlight it and then click Save");?>.
                    <button type="button" class="btn btn-secondary btn-sm btn-save oe-inline oe-no-float"><?php echo xlt("Save");?></button>
                </p>
                
                <p><?php echo xlt("Hold down Ctrl button to select multiple issues");?>.</p>

                <p><?php echo xlt("For more information on Issues see the ");?>. <strong><a href="https://www.open-emr.org/wiki/index.php/Issues_%26_Immunizations" rel="noopener" target="_blank" class="oe-help-redirect"><?php echo xlt("Issues and Immunizations wiki page");?></a></strong></p>
            </div>
        </div><!--end of container div-->
    </body>
</html>

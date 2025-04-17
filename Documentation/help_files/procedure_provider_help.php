<?php

/**
 * Message Center Help.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
 * @version 1.0.0
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
    </head>
    <body>
        <div class="container oe-help-container">
            <div>
                <h2 class="text-center"><a name='entire_doc'><?php echo xlt("Procedure Provider Help");?></a></h2>
            </div>
            <div class= "row">
                <div class="col-sm-12">
                    <p><?php echo xlt("The Procedures module is used to place and review orders");?>.</p>

                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <strong><?php echo xlt("In order to fulfill Meaningful Use requirements for Computerized Provider Order Entry (CPOE) for lab and radiology tests this module needs to be used");?>.</strong></p>

                    <p><?php echo xlt("While it is intuitive to think of this module as the place where lab tests and imaging studies can be ordered and reviewed this module can also be used to track other procedures performed in office");?>.</p>

                    <p><?php echo xlt("The Procedure Order page lists the following procedure types"); ?>:</p>
                        <ul>
                            <li><?php echo xlt("Procedure"); ?></li>
                            <li><?php echo xlt("Intervention"); ?></li>
                            <li><?php echo xlt("Laboratory Test"); ?></li>
                            <li><?php echo xlt("Physical Exam"); ?></li>
                            <li><?php echo xlt("Risk Category Assessment"); ?></li>
                            <li><?php echo xlt("Patient Characteristics"); ?></li>
                            <li><?php echo xlt("Imaging"); ?></li>
                            <li><?php echo xlt("Encounter Checkup Procedure"); ?></li>
                        </ul>

                    <p><?php echo xlt("The procedure request needs to be structured in a hierarchical manner and a unique identifying code assigned to it that will then be used to track and tabulate the result");?>.</p>

                    <p><?php echo xlt("It consists of two parts - defining a Provider and configuring the Orders and Results");?>.</p>

                    <p><?php echo xlt("While lab tests and radiological tests can be performed in the practice facility they are generally done at an external facility and the provider details for that entity will have to be entered in openEMR before the Procedure module setup can begin");?>.</p>

                    <p><?php echo xlt("For procedures performed in the practice, whether it is an office based procedure or a lab or radiological procedure done in the facility, the provider can be defined as Local Lab, Office Based or have any descriptive name");?>.</p>

                    <p><?php echo xlt("The setup for lab tests will be described in detail, minor modification to these steps will be needed to setup in-office procedures and other procedure types");?>.</p>

                    <p><?php echo xlt("Integrating lab results into a patient's chart in openEMR can be done manually i.e. both ordering tests and uploading the received results or electronically");?>.</p>

                    <p><?php echo xlt("Electronic results can be bidirectional - both order tests and receive results electronically or unidirectional - only receive the results electronically");?>.</p>

                    <p><?php echo xlt("A third alternative is to scan the results and save it as a document - TIFF, JPEG or PDF in the patient's chart under documents");?>.</p>

                    <p><?php echo xlt("The advantage of integrating the results with the patient's chart as structured data is the ability to manipulate it to see trends in one convenient location, to plot graphs with the data and use it in data analysis");?>.</p>

                    <p><?php echo xlt("The advantage of scanning the results into the chart is simplicity - no setup is required other than defining a directory/folder in the patient's chart under Documents where the result will be stored. Being unstructured data it does not have the above advantages and will not fulfill Meaningful Use criteria for Computerized Provider Order Entry (CPOE)");?>.</p>

                    <p><?php echo xlt("If you choose to integrate lab results with the patient's record then some preliminary setup has to be performed");?>.</p>

                    <ul id="top_section">
                        <li><a href="#section1"><?php echo xlt("Define lab service locations");?></a></li>
                        <li><a href="#section2"><?php echo xlt("Check and adjust the settings in the standard release");?></a></li>
                        <li><a href="#section3"><?php echo xlt("Configure the lab test structure");?></a></li>
                    </ul>

                    <p><?php echo xlt("These three steps are essential before orders can be placed and received results linked to a patient's chart");?>.</p>

                    <p><?php echo xlt("This is required for both manual lab result entry and for electronic ordering tests and receiving results");?>.</p>
                </div>
            </div>
            <div class= "row" id="section1">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Define lab service locations"); ?><a href="#top_section"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><i class="fa fa-exclamation-triangle  oe-text-red" aria-hidden="true"></i> <strong><?php echo xlt("You need administrator privileges to perform this action"); ?>.</strong></p>

                    <p><i class="fa fa-lightbulb-o fa-lg  oe-text-green" aria-hidden="true"></i>&nbsp <?php echo xlt("To use this help file as an instruction manual it is suggested that you reduce the size of the browser to cover half the viewport, resize the help pop-up by clicking and dragging the bottom right corner of the pop-up. Open another instance of the browser and resize it to cover the other half of the viewport, login to openEMR");?>.</p>

                    <p><?php echo xlt("Go to Administration > Address Book > Add New");?>.</p>

                    <p><?php echo xlt("Select Lab Service in the Type drop-down box and enter a name under organization");?>.</p>

                    <p><?php echo xlt("Check the CPOE (Computerized Provider Order Entry) check-box");?>.</p>

                    <p><?php echo xlt("For manual lab entry you can give the organization any name  - e.g Local Lab and click Save");?>.
                        <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("Save"); ?></button>
                    </p>

                    <p><?php echo xlt("If you are using Electronic lab entry then fill in the required details and click Save");?>.
                        <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("Save"); ?></button>
                    </p>

                    <p><?php echo xlt("If you are using multiple labs enter the details for each one of them");?>.</p>

                    <p><?php echo xlt("For this name to show in the drop-down box in the Procedures > Providers setup page select only Lab Service as the Type even if it represents a Radiological facility");?>.</p>

                    <p><?php echo xlt("These entries will be used to define the Providers in the Procedures module");?>.</p>

                    <p><?php echo xlt("Go to Procedures > Providers and click on the Add New button");?>.
                        <button type="button" class="btn btn-secondary btn-add btn-sm oe-no-float"><?php echo xlt("Add New"); ?></button>
                    </p>

                    <p><?php echo xlt("The Enter Provider Details pop-up will be visible");?>.</p>

                    <p><?php echo xlt('Additional help to fill out this form is available by hovering over labels of each box and clicking on the dark blue help ? icon that is revealed'); ?>.</p>

                    <p><?php echo xlt('On mobile devices tap once on the label to reveal the help icon and tap on the icon to show the help section'); ?>.</p>

                    <p><?php echo xlt("Select the name of the entity from the Name drop-down box");?>.</p>

                    <p><?php echo xlt("Fill in the required details especially for the external facilities and click Save");?>.
                        <button type="button" class="btn btn-secondary btn-sm oe-no-float"><?php echo xlt("Save"); ?></button>
                    </p>

                    <p><?php echo xlt("Add all needed providers in a similar manner");?>.</p>
                </div>
            </div>
            <div class= "row" id="section2">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Check and adjust the settings in the standard release"); ?><a href="#top_section"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("Before proceeding to configuring the tests themselves you would need to review the values included in the standard release and make changes according to need");?>.</p>

                    <p><?php echo xlt("The values listed here will show up in the drop-down boxes that will be used to process orders");?>.</p>

                    <p><?php echo xlt("There are 10 settings that are related to procedures included in the standard release of openEMR"); ?>.&nbsp <i id="show_hide" class="fa fa-eye fa-lg small" title="<?php echo xla('Click to Show'); ?>"></i></p>

                    <div id="proc_list" class='hideaway' style='display: none;'>
                        <ul>
                            <li><?php echo xlt('Procedure Body Sites');?></li>
                            <li><?php echo xlt('Procedure Boolean Results');?></li>
                            <li><?php echo xlt('Procedure Lateralities');?></li>
                            <li><?php echo xlt('Procedure Report Statuses');?></li>
                            <li><?php echo xlt('Procedure Report Abnormal');?></li>
                            <li><?php echo xlt('Procedure Result Statuses');?></li>
                            <li><?php echo xlt('Procedure Routes');?></li>
                            <li><?php echo xlt('Procedure Specimen Types');?></li>
                            <li><?php echo xlt('Procedure Types');?></li>
                            <li><?php echo xlt('Procedure Units');?></li>
                        </ul>
                    </div>

                    <p><i class="fa fa-exclamation-triangle  oe-text-red" aria-hidden="true"></i> <strong><?php echo xlt("You need administrator privileges to perform this action"); ?>.</strong></p>

                    <p><?php echo xlt("Go to Administration > Lists, select each one of the above lists from the drop-down box and make changes as needed");?>.</p>

                    <p><?php echo xlt("Upon selecting a list to edit the edit page with the list values will be displayed");?>.</p>

                    <p><?php echo xlt("For editing the values under Procedures you would need to modify");?>:</p>

                        <ul>
                            <li><?php echo xlt("ID - select a unique three letter id, all in lower case"); ?></li>
                            <li><?php echo xlt("Title - The value that will be displayed - Each word to begin with an uppercase letter"); ?></li>
                            <li><?php echo xlt("Order - The order in which the item will be displayed - incrementing by 10 will allow for up to 9 values to be inserted in between if so desired at a later date"); ?></li>
                            <li><?php echo xlt("Default - Check any check box and this value will show up in the displayed drop-down box as the default value"); ?></li>
                            <li><?php echo xlt("Active - By default any value added here will be set to Active, to prevent/remove an item from showing up in the drop-down list just uncheck and deactivate"); ?></li>
                            <li><?php echo xlt("Notes - A short description that will show up as a tooltip"); ?></li>
                            <li><?php echo xlt("Codes - Leave blank"); ?></li>
                        </ul>

                    <p><?php echo xlt("Procedure Body Sites - Arm, Buttock and Other - used for immunization - modify as needed");?>.

                    <p><?php echo xlt("Procedure Boolean Results - No and Yes");?>.

                    <p><?php echo xlt("Procedure Lateralities - Left, Right and Bilateral");?>.

                    <p><?php echo xlt("Procedure Report Statuses - Final, Reviewed, Preliminary, Canceled, Error and Corrected");?>.

                    <p><?php echo xlt("Procedure Report Abnormal - No, Yes, High, Low Above upper panic limit and Below lower panic limit");?>.

                    <p><?php echo xlt("Procedure Result Statuses - Final, Preliminary, Canceled, Error, Corrected and Incomplete");?>.

                    <p><?php echo xlt("Procedure Routes - Injection, Oral and Other");?>.

                    <p><?php echo xlt("Procedure Specimen Types - Blood, Saliva, Urine and Other");?>.

                    <p><i class="fa fa-exclamation-circle  oe-text-orange" aria-hidden="true"></i>&nbsp<?php echo xlt("Procedure Types - Group, Procedure Order, Discrete Result, Recommendation, Custom Favorite Group and Custom Favorite Item. Used in the next step - Configure the lab test structure");?>.</p>

                    <p><?php echo xlt("Custom Favorite Group and Custom Favorite Item is used to create customized groups of orders");?>. <i class="fa fa-exclamation-circle oe-text-orange" aria-hidden="true"></i>&nbsp;<strong><?php echo xlt("New in openEMR ver 5.0.2 "); ?></strong></li></p>

                    <p><?php echo xlt("Procedure Units - various units needed to define result values - may need to add to this list depending on the tests that are included, will vary according to need");?>.</p>

                    <p><?php echo xlt("Once this step is completed you can proceed to next step - to configure the tests that can be ordered from the system");?>.</p>
                </div>
            </div>
            <div class= "row" id="section3">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Configure the lab test structure"); ?><a href="#top_section"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("To configure the lab test go to Procedures > Configuration");?>.</p>

                    <p><?php echo xlt("The help file there will guide you on further steps in setting up the lab tests");?>.</p>
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

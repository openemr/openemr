<?php
/**
 * Fee Sheet Help.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2017 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
 
use OpenEMR\Core\Header;

require_once("../../globals.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
    <?php Header::setupHeader();?>
    <title><?php echo xlt("EOB Posting - Instructions");?></title>
    <style>
        .oe-help-heading{
            color:#676666;
            background-color: #E4E2E0;
            border-color: #DADADA;
            padding: 10px 5px;
            border-radius: 5px;
        }
        .oe-help-redirect{
            color:#676666;
        }
        a {
            text-decoration: none !important;
            color:#676666 !important;
            font-weight:700;
        }
        h2 > a {
            font-weight:500;
        }
        @media only screen and (max-width: 768px) {
           [class*="col-"] {
           width: 100%;
           text-align:left!Important;
            }
        }
        @media only screen and (max-width: 1004px) and (min-width: 641px)  {
            .oe-large {
                display: none;
            }
            .oe-small {
                display: inline-block;
            }
        }
    </style>
    </head>
    <body>
        <div class="container">
            <div>
                <center><h2><a name = 'entire_doc'><?php echo xlt("Using the Feesheet");?></a></h2></center>
            </div>
            <div class= "row">
                <p><?php echo xlt("Use the fee sheet to charge for services or products. ");?>
                
                <p><i class="fa fa-exclamation-circle" style="color:orange" aria-hidden="true"></i> <strong><?php echo xlt("Before beginning to use the Fee Sheet read through this help page and do some preliminary setup.");?></strong>
                
                <p><?php echo xlt("The default fee sheet can be modified and customized to make it more useful.");?>
                
                <p><?php echo xlt("A followup appointment can also be scheduled right from the fee sheet.");?>
                
                <p><?php echo xlt("The default fee sheet is divided into several sections and is designed to be filled from top to bottom with the save button at the very end.");?>
                
               
                <ul>
                    <li><a href="#price_level"><?php echo xlt("Set Price Level");?></a></li>
                    <li><a href="#select_code"><?php echo xlt("Select Code");?></a></li>
                    <li><a href="#search_additional"><?php echo xlt("Search for Additional Codes");?></a></li>
                    <li><a href="#selected_codes"><?php echo xlt("Selected Fee Sheet Codes and Charges for Current Encounter");?></a></li>
                    <li><a href="#select_providers"><?php echo xlt("Select Providers");?></a></li>
                    <li><a href="#setup"><?php echo xlt("Setup the Fee Sheet");?></a></li>
                    <li><a href="#customize"><?php echo xlt("Customize the Fee Sheet");?></a></li>
                </ul>
            </div>
            <div class= "row" id="price_level">
                <h4 class="oe-help-heading"><?php echo xlt("Set Price Level"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("This lets you set the price level for the charges and let you charge different rates for different modes of payment.");?>
                
                <p><?php echo xlt("The default is 'Standard' usually used to reflect rates charged for insurance billing. You can have a different amount charged for credit card payment and for cash payment."); ?>
                
                <p><?php echo xlt("By selecting a payment method the rates calculated for that payment method will be automatically displayed in 'Selected Fee Sheet Codes and Charges for Current Encounter' section."); ?>
                
                <p><?php echo xlt("To add more options to the dropdown menu go to Administration > Lists > Manage Lists and select 'Price Level' in the dropdown box and enter the types e.g: Credit Card, Cash etc.."); ?>
                
                <p><i class="fa fa-exclamation-circle" style="color:orange" aria-hidden="true"></i> <strong><?php echo xlt("The actual amounts that will be displayed must exist in the database."); ?></strong> <strong><a href="#setup" class="oe-help-redirect"><?php echo xlt("See Setup the Fee Sheet"); ?></a></strong>
            </div>
            <div class= "row" id="select_code">
                <h4 class="oe-help-heading"><?php echo xlt("Select Code"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("This section is where the codes use to charge for the visit are selected."); ?>
                
                <p><?php echo xlt("The default has two buttons that is set for 'New Patient' and 'Established' patient. Clicking the button will reveal a popup that will list the E/M codes, check the appropriate code and clik 'OK'."); ?>
                
                <p><?php echo xlt("The selected codes will then appear in the 'Selected Fee Sheet Codes and Charges for Current Encounter' section."); ?>
                
                <p><?php echo xlt("This section can be customized to group the codes into various categories."); ?> <strong><a href="#customize" class="oe-help-redirect"><?php echo xlt("See Customize the Fee Sheet"); ?></a></strong>
            </div>
            <div class= "row" id="search_additional">
                <h4 class="oe-help-heading"><?php echo xlt("Search for Additional Codes"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("This section lets you search for the needed code if it is not in the default or customized options in 'Select Code' section."); ?>
                
                <p><?php echo xlt("It lets you search by ICD 9 and ICD 10 diagnostic codes and CPT4 and HCPCS service/procedure codes."); ?>
                
                <p><i class="fa fa-exclamation-circle" style="color:orange" aria-hidden="true"></i> <strong><?php echo xlt("For a search to show results the codes must exist in the database.");?></strong>  <strong><a href="#setup" class="oe-help-redirect"><?php echo xlt("See Setup the Fee Sheet"); ?></a></strong>
                
                <p><?php echo xlt("Select the appropriate radio button. Enter the search term in the search box and click 'Search'. A popup box will alert you about success or failure."); ?>
                
                <p><?php echo xlt("If your search was successful the search results will be displayed in the search results box below."); ?>
                
                <p><?php echo xlt("To select a particular code identify it in the returned results and click on it to select."); ?>
                
                <p><?php echo xlt("The selected codes will then appear in the 'Selected Fee Sheet Codes and Charges for Current Encounter' section"); ?>
            </div>
            <div class= "row" id="selected_codes">
                <h4 class="oe-help-heading"><?php echo xlt("Selected Fee Sheet Codes and Charges for Current Encounter"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("This section will list all the successful charges one row at a time."); ?>
                
                <p><?php echo xlt("The rows containing the CPT4 codes has several boxes that need to be filled to ensure proper billing of claims."); ?>
                <ul>
                    <li><?php echo xlt("Modifiers"); ?> - <?php echo xlt("List modifiers, upto 4 can be listed, each separated by a space or a colon."); ?></li>
                    <li><?php echo xlt("Price"); ?> - <?php echo xlt("Price can be manually set or if already set can be altered here."); ?></li>
                    <li><?php echo xlt("Units"); ?> - <?php echo xlt("If a product is sold then change the default to reflect required number. Leave it at one for procedures"); ?></li>
                    <li><?php echo xlt("Justify"); ?> - <?php echo xlt("Justify each CPT code with one or more justifications using the dropdown box.These will reflect the diagnosis codes that was previously selected in 'Select Codes'"); ?></li>
                    <li><?php echo xlt("Provider/Warehouse"); ?> - <?php echo xlt("A provider or warehouse can be selected in the dropwdown box. For this option to be displayed 'Support provider in line item in fee sheet' box must be checked in Administration > Globals> Billing page"); ?></li>
                    <li><?php echo xlt("Note Codes"); ?> - <?php echo xlt("Add a note to the biller."); ?></li>
                </ul>
                
                <p><?php echo xlt("In addition there are 2 checkboxes."); ?>
                <ul>
                    <li><?php echo xlt("Auth"); ?> - <?php echo xlt("Authorize or not as needed."); ?></li>
                    <li><?php echo xlt("Delete"); ?> - <?php echo xlt("Lets you delete a line or row. Hit 'Refresh' and the line will have a strikethrough across it. To fully delete hit 'Save'."); ?></li>
                </ul>
                
                <p><?php echo xlt("To add a copay click the 'Add Copay' button. To display the copay amount it must have been entered in the patient's insurance under Edit > Demographics. "); ?>
                
                <p><?php echo xlt("Clicking on 'Review' will list all the codes entered for previous encounters. By default all codes are selected, uncheck the codes you do not want and click 'Add'. These codes will then be added to the current encounter."); ?>
                
            </div>
            <div class= "row" id="select_providers">
                <h4 class="oe-help-heading"><?php echo xlt("Select Providers"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("Both rendering and supervising providers can be set here."); ?>
                
                <p><?php echo xlt("The default values for the rendering providers can be set."); ?> <strong><a href="#customize" class="oe-help-redirect"><?php echo xlt("See Setup the Fee Sheet"); ?></a></strong>
                
                <p><?php echo xlt("To allow for each procedure line to have a separate provider you have to check the 'Support provider in line item in fee sheet' checkbox in Administarion > Globals > Billing. If not checked the rendering provider in this section will be used for all claims"); ?>
                
            </div>
            <div class= "row" id="setup">
                <h4 class="oe-help-heading"><?php echo xlt("Setup the Fee Sheet"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><i class="fa fa-exclamation-triangle" style="color:red" aria-hidden="true"></i> <strong><?php echo xlt("You need administrator privileges to perform the setup."); ?></strong>
                
                <p><?php echo xlt("The very first step would be install the ICD codes. Go to Administraion > Other > External Data Loads. Select the Code sets you want to install and click Install."); ?>
                
                <p><?php echo xlt("For full billing functionality in the United States CPT/HCPCS codes will then need to be installed."); ?>
                
                <p><?php echo xlt("Read this wiki page for more details."); ?> <a href="http://www.open-emr.org/wiki/index.php/Code_Types" target="_blank"><?php echo xlt("Installing codes in openEMR"); ?> </a>
                
                <p><?php echo xlt("Importing the entire CPT code set after you license it from the American Medical Association would get you all the relevant CPT codes in one fell swoop."); ?>
                
                <p><?php echo xlt("If you are only using a small subset of the CPT codes you can manually enter it in Administration > Codes."); ?>
                
                <p><?php echo xlt("If you are planning on entering different prices for different modalities of payment go to Administraion > Lists."); ?>
                
                <p><?php echo xlt("Select 'Price Levels' and enter Credit Card and Cash."); ?> <?php echo xlt("This will give you the option to set different price levels when you are manually entering CPT codes."); ?>
                
                <p><?php echo xlt("Create a spreadsheet in openoffice/LibreOffice, have 5 columns - CPT Code, Description, Standard, Credit Card and Cash. Fill in the values. From this spreadsheet manually copy and paste the values via Administration > Codes."); ?>
                
                <p><?php echo xlt("To change and activate the default parameters in the Fee Sheet go to Administration > Globals > Billing."); ?>
                    <ul>
                    <li><?php echo xlt("Default Search Code Type"); ?> - <?php echo xlt("Set which radio button is selected by default in 'Search for Additional Codes' section."); ?></li>
                    <li><?php echo xlt("Default Rendering Provider in Fee Sheet"); ?> - <?php echo xlt("Choose either current provider or current logged in provider."); ?></li>
                    <li><?php echo xlt("Support provider in line item in fee sheet"); ?> - <?php echo xlt(" Will add another dropdown menu in the CPT line to let you select a provider for that line item."); ?></li>
                    <li><?php echo xlt("Automatically replicate justification codes in Fee Sheet"); ?> - <?php echo xlt("Once you select a justification code for the first CPT line all subsequent CPT code lines will have the same justification. Can be manually altered later if so desired.."); ?></li>
                </ul>
                
                <p><?php echo xlt("This completes a basic setup and will let use use the Fee Sheet to document charges for billing purposes."); ?>
                
                <p><?php echo xlt("For added ease of use the Fee Sheet needs to be customized."); ?>
            </div>
            <div class= "row" id="customize">
                <h4 class="oe-help-heading"><?php echo xlt("Customize the Fee Sheet"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("There are several ways to customize the fee sheet. It depends on how comfortable you are in adding information to the database itself."); ?>
                
                <p><?php echo xlt("Read these two wiki articles for customizing the Fee Sheet."); ?> <strong><a href="http://www.open-emr.org/wiki/index.php/HOWTO:_Create_Multiple_Code_Fee_Sheet_List_Categories" target="_blank"><?php echo xlt("Article"); ?> 1 </a></strong> <strong><a href="http://openemr.sourceforge.net/wiki/index.php/Preparing_for_Billing_and_using_the_Fee_Sheet" target="_blank"><?php echo xlt("Article"); ?> 2</a></strong>
                
                <p><?php echo xlt("If you do not want to do so the following method involves adding data manually using the openEMR interface."); ?>
                
                <p><?php echo xlt("The following steps will help customize the Fee Sheet to mimic the old paper superbill with a list of CPT4 and ICD codes arranged in categories."); ?>
                
                <p><i class="fa fa-exclamation-triangle" style="color:red" aria-hidden="true"></i> <strong><?php echo xlt("Before you begin customization please ensure that all the ICD and CPT codes you will be needing have been entered into the database."); ?></strong> <strong><a href="#setup" class="oe-help-redirect"><?php echo xlt("See Setup the Fee Sheet"); ?></a></strong>
                
                <p><?php echo xlt("The first step is to plan what your Fee Sheet 'Select Codes' section should display."); ?>
                
                <p><?php echo xlt("If you have an old superbill you can use its grouping of codes as a template to start the customization."); ?>
                
                <p><?php echo xlt("Decide on the code categories and the order you want them to appear, these will be displayed on the buttons in the 'Select Codes' section."); ?>
                
                <p><?php echo xlt("Open a spreadsheet in openoffice/LibreOffice create 5 columns - three for E/M / CPT codes and two for the ICD code categories."); ?>
                
                <p><?php echo xlt("Alternatively download a sample spreadsheet."); ?> &nbsp <a href="fee_sheet_customization.ods" class= "btn btn-default btn-download" download="Fee Sheet Customization" target="_blank"> <?php echo xlt("Download"); ?></a>

                <p><?php echo xlt("Fill in the CPT codes using the displayed format. The common E/M codes are already filled in, add more as needed."); ?>
                
                <p><?php echo xlt("The first column contains the Group/Category name that will be displayed on the button."); ?>
                
                <p><?php echo xlt("The second column contains the options that will be displayed when the button is clicked."); ?>
                
                <p><?php echo xlt("The third column will contain the CPT code."); ?>
                
                <p><?php echo xlt("The first two numbers in the first and second colum will be used to determine the sort order of the buttons and the lists that are present in the popup."); ?>
                
                <p><?php echo xlt("Go to Administration > Lists. Select  'Fee Sheet'  from the dropdown box."); ?>
                
                <p><?php echo xlt("Replace the existing group and option values with these ones taking care to match the E/M codes."); ?>
                
                <p><?php echo xlt("Add the remaning codes, in order to do so the CPT code must already exist in the database."); ?>
                
                <p><?php echo xlt("After you filled in the spreadsheet with the relvant ICD categories and ICD codes go to Administration > Lists. Select  'Service Category'  from the dropdown box."); ?>
                
                <p><?php echo xlt("Enter the category data in the appropriate fields and click 'Save'. These will  be used to group the ICD codes and will be displayed on the buttons in the 'Select Codes' section."); ?>
                
                <p><i class="fa fa-exclamation-circle" style="color:orange" aria-hidden="true"></i> <strong><?php echo xlt("Go to Adminstration > Lists and select 'Code Types' from the dropdown box. Use either ICD 9 or ICD 10, inactivate ICD 9 and select 'No' in the last dropdown box under ICD 10."); ?></strong>
                
                <p><?php echo xlt("Go to Adminstration > Codes. Select ICD 10 under Type, enter ICD 10 Code, enter Description, under category select an appropriate category, this ICD 10 code will then appear when the button with that category is clicked."); ?>
                
                <p><?php echo xlt("Click 'Save'."); ?>
                
                <p><i class="fa fa-exclamation-circle" style="color:orange" aria-hidden="true"></i> <strong><?php echo xlt("After you have finished entering all the ICD 10 codes go to Adminstration > Lists and select 'Code Types' from the dropdown box. Select 'ICD10 Diagnosis' from the last dropdown box under ICD 10."); ?></strong>
                
                <p><?php echo xlt("Click 'Save'. Now you will be able to use the Search feature to search all ICD 10 codes."); ?>
                
                <p><?php echo xlt("The 'Select Codes' section will now have all the CPT and ICD codes that you entered grouped under the categories that you had decided upon."); ?>
                
                <p><?php echo xlt("The custmomized Fee Sheet can be used. If and when you come across a code that is not there in the custom 'Select Codes' section you can always use the 'Search for Additional Codes' section."); ?>
            </div>
        </div><!--end of container div-->
    </body>
</html>

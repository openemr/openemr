<?php
/**
 * Load Compendium - Help.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2017 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
use OpenEMR\Core\Header;

include_once("../globals.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
    <?php Header::setupHeader();?>
    <title><?php echo xlt("Load Compendium - Help");?></title>
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
                <center><h2><a name = 'entire_doc'><?php echo xlt("Load Compendium - Help");?></a></h2></center>
            </div>
            <div class= "row">
                <p><?php echo xlt("If the user partners with Diagnostic Pathology Medical Group, Pathgroup Labs LLC or Yosemite Pathology Medical Group, they can obtain from them a data set of diagnostic studies that can be uploaded directly into the Procedure module to avoid manual configuration.");?>
                
                <p><?php echo xlt("The 'Select Compendium' box has three options - Vendor, Action and Container Group Name.");?>
            </div>
            <div class= "row">
                <h4 class="oe-help-heading"><?php echo xlt("Vendor"); ?><a name = 'vendor' href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("Only the above mentioned three vendors are currently supported."); ?> 
                
                <p><?php echo xlt("To be able to select one option an entry for that vendor should exist in Administration >  Address book.")?>
                
            </div>
            <div class= "row">
                <h4 class="oe-help-heading"><?php echo xlt("Action"); ?><a name = 'action' href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("In order to fully load the data the three actions must be performed on the dataset.");?>

                <p><?php echo xlt("Load Order Definitions - Fill in the Blanks.");?>

                <p><?php echo xlt("Load Order Entry Questions  - Fill in the Blanks. ");?>

                <p><?php echo xlt("Load OE Question Options - Fill in the Blanks.");?>

            </div>
            <div class= "row">
                <h4 class="oe-help-heading"><?php echo xlt("Container Group Name"); ?><a name = 'container_group_name' href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("Fill in the Blanks.");?>

                <p><?php echo xlt("Fill in the Blanks.");?>

                <p><?php echo xlt("Fill in the Blanks. ");?>

            </div>
           
        </div><!--end of container div-->
    </body>
</html>

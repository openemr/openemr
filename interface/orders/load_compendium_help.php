<?php
 // Copyright (C) 2005-2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
use OpenEMR\Core\Header;

include_once("../globals.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
    <?php Header::setupHeader();?>
    <title><?php echo xlt("EOB Posting - Instructions");?></title>
    <style>
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
                <a name = 'vendor'> <h4><?php echo xlt("Vendor"); ?></h4></a>
                <p><?php echo xlt("Only the above mentioned three vendors are currently supported."); ?> 
                
                <p><?php echo xlt("To be able to select one option an entry for that vendor should exist in Administration >  Address book.")?>
                
            </div>
            <div class= "row">
                <a name = 'action'> <h4><?php echo xlt("Action"); ?></h4></a>
                <p><?php echo xlt("In order to fully load the data the three actions must be performed on the dataset.");?>

                <p><?php echo xlt("Load Order Definitions - Fill in the Blanks.");?>

                <p><?php echo xlt("Load Order Entry Questions  - Fill in the Blanks. ");?>

                <p><?php echo xlt("Load OE Question Options - Fill in the Blanks.");?>

            </div>
            <div class= "row">
                <a name = 'container_group_name'> <h4><?php echo xlt("Container Group Name"); ?></h4></a>
                <p><?php echo xlt("Fill in the Blanks.");?>

                <p><?php echo xlt("Fill in the Blanks.");?>

                <p><?php echo xlt("Fill in the Blanks. ");?>

            </div>
           
        </div><!--end of container div-->
    </body>
</html>

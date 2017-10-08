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
                <center><h2><a name = 'entire_doc'><?php echo xlt("Ordering Procedures, Entering and Viewing Results");?></a></h2></center>
            </div>
            <div class= "row">
                <p><?php echo xlt("Before ordering procedures the Procedures module has to be setup either for manual entry or for automated import of lab results.");?>
                
                <p><?php echo xlt("In order for a test to appear on the 'pending review' tab, the tests ordered must be correctly configured - otherwise it fails silently.
                each 'procedure order' must have a 'discrete result' under it.");?>
            </div>
             <div class= "row">
                <p><?php echo xlt("Use the Procedures > Providers, Procedures > Configuration or Procedures > Load Compendium to set up this module.");?>
                
                <p><?php echo xlt("The first step is to place an order, this is done by selecting a patient. Then either create a new encounter or select a previous encounter, then go to Encounter > Adminstrative > Procedure Order. In case you need to enter the results for tests not ordered by the practice - create an encounter for the date the lab was ordered and create an order for that procedure/test by going to Encounter > Adminstrative > Procedure Order."); ?> 
            </div>
            <div class= "row">
                <a name = 'pending_review'> <h4><?php echo xlt("Pending Review"); ?></h4></a>
                <p><?php echo xlt("In order to manually enter the test results for a particular patient, select the patient , go to Procedures > Pending Review."); ?> 
                
                <p><?php echo xlt("To bring up the studies for result entry, login as Physician. Other staff, including Administrator, will not have the ability to perform this task. Generally this privilege should be reserved for the Physician. The Front Desk should not be permitted to sign off on diagnostic studies.")?>
                
                <p><?php echo xlt("In unusual situations wherein the Physician wears two hats and has the responsibility of the Administrator as well, this can be changed in Administration/ACL by granting the Access Control Object, 'Sign Lab Results', to the Administrator.")?>

                <p><?php echo xlt("Add the Reported date, assign a Status and enter the results before clicking the 'Sign Results' button.")?>
            </div>
            <div class= "row">
                <a name = 'patient_results'> <h4><?php echo xlt("Patient Results"); ?></h4></a>
                <p><?php echo xlt("After signing the results, the data will appear in Patient Results.");?>

                <p><?php echo xlt("This page like the Procedure Results - Review page has a table that displays the data in tabular form.");?>

                <p><?php echo xlt("The data is divided into three sections - Order, Report and Results and Recommendations. ");?>

                <p><?php echo xlt("The 'Order' section contains the actual procedure or test tha was ordered using Encounter > Procedure Order and contains the details of the order, namely Date and Procedure Name.");?>

                <p><?php echo xlt("The 'Report' section contains the Reported time and status of the result. These are manually entered in preceding Procedures > Procedure Results - Review page.");?>

                <p><?php echo xlt("The 'Results and Recommendations' section contains the actual values of the individual results along with the normal range and recommendations if any.");?>
            </div>
           
        </div><!--end of container div-->
    </body>
</html>

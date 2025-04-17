<?php

/**
 * Access Control List Help.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ranganath Pathak <pathak01@hotmail.com>
  * @version 1.0.0
 * @copyright Copyright (c) 2017 Ranganath Pathak <pathak01@hotmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Header;

require_once("../../interface/globals.php");
?>
<!DOCTYPE html>
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
    </style>

    </head>
    <body>
        <div class="container oe-help-container" id="home-div">
            <div>
                <h2 class="text-center"><a name='entire_doc'><?php echo xlt("EOB Data Entry");?></a></h2>
            </div>
            <div class="row">
                <p><?php echo xlt("This module promotes efficient entry of EOB data.");?></p>

                <p><?php echo xlt("There are two ways this can be accomplished, either by the manual method, by searching and entering data for individual invoices or by uploading an entire electronic remittance advice ERA file.");?></p>

                <p><?php echo xlt("The initial screen lets you select a method by offering you these two options.");?></p>
            </div>
            <div class= "row">
                <form>
                <fieldset>
                    <legend>
                        &nbsp;<span><?php echo xlt("Select Method");?></span>
                         <div class="float-right oe-legend-radio">
                            <label class="radio-inline">
                                <input type="radio" id="invoice_search_help" name="radio-search" onclick="" value="inv-search-help"><?php echo xlt("Invoice Search"); ?>
                            </label>
                            <label class="radio-inline">
                                <input type="radio" id="era_upload" name="radio-search" onclick=""  value="era-upld-help"><?php echo xlt("ERA Upload"); ?>
                            </label>
                        </div>

                    </legend>
                </fieldset >
                </form>
            </div>
            <div class= "row">
            <br />
                <?php echo xlt("To get started you choose one of the two radio buttons. 'Invoice Search' or 'ERA Upload' "); ?>

                <ul>
                    <li><a href="#invoice-search-div"><?php echo xlt("Invoice Search");?></a></li>
                    <li><a href="#era-upload-div"><?php echo xlt("ERA Upload");?></a></li>
                </ul>
            </div>
            <div class= "row" id="invoice-search-div">
                <h4 class="oe-help-heading"><?php echo xlt("Invoice Search"); ?><a name = 'invoice_search' href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("If you choose to do a manual entry click the 'Invoice Search' radio button. It displays two sections 'Post Item' and 'Invoice Search'"); ?></p>
                 <form>

                        <fieldset>
                            <legend>
                                &nbsp;<span><?php echo xlt("Post Item");?></span>
                            </legend>
                        </fieldset>
                        <fieldset>
                            <legend>
                                &nbsp;<span><?php echo xlt("Invoice Search");?></span>
                                <div class="float-right oe-legend-radio">
                                        <label class="radio-inline">
                                          <input type="radio" id="invoice_search_help" name="radio-search" onclick="" value="inv-search-help"><?php echo xlt("Invoice Search"); ?>
                                        </label>
                                        <label class="radio-inline">
                                          <input type="radio" id="era_upload_help" name="radio-search" onclick=""  value="era-upld-help"><?php echo xlt("ERA Upload"); ?>
                                        </label>
                                </div>
                            </legend>
                        </fieldset>

                </form>
                <br />
                <p><?php echo xlt("In the 'Post Item' section that is displayed at the top you may enter a source (e.g. check number), pay date and check amount. The reason for the source and pay date is so that you don\'t have to enter them over and over again for each claim. The amount that you enter will be decreased for each invoice that is given part of the payment, and hopefully will end at zero when you are done.")?></p>

                <p><?php echo xlt("The section labeled 'Invoice Search' is where you put in your search parameters. You can search by patient name, chart number, encounter number or date of service, or any combination of these. You may also select whether you want to see all invoices, open invoices, or only invoices that are due (by the patient). Click the 'Search' button to perform the search.")?></p>

                <p><?php echo xlt("The Search results are displayed in the section 'Search Results'.")?></p>
                 <form>
                        <fieldset>
                            <legend>
                                &nbsp;<span><?php echo xlt("Search Results");?></span>
                            </legend>
                        </fieldset>
                </form>
                <br />
                <p><?php echo xlt("Upon a successful search you are presented with a list of invoices. You may click on one of the invoice numbers to open a second window, which is the data entry page for manual posting. You may also click on a patient name if you want to enter a note that the front office staff will see when the patient checks in, and you may select invoices to appear on patient statements and print those statements.");?></p>

                <p><?php echo xlt("Upon clicking an invoice number the 'manual posting window' appears. Here you can change the due date and notes for the invoice, select the party for whom you are posting, and select the insurances for which all expected payments have been received. Most importantly, for each billing code for which an amount was charged, you can enter payment and adjustment information.");?></p>

                <p><?php echo xlt("The Source and Date columns are copied from the first page, so normally you will not need to touch those. You can put a payment amount in the Pay column, an adjustment amount in the Adjust column, or both. You can also click the 'W' on the right to automatically compute an adjustment value that writes off the remainder of the charge for that line item.");?></p>

                <p><?php echo xlt("Pay attention to the 'Done with' checkboxes. After the insurances are marked complete then we will start asking the patient to pay the remaining balance; if you fail to mark all of the insurances complete then the remaining amount will not be collected! Also if there is a balance that the patient should pay, then set the due date appropriately, as this will affect the language that appears on patient statements.");?></p>

                <p><?php echo xlt("After the information is correctly entered, click the Save button.");?></p>

                <p><?php echo xlt("Another thing you can do in the posting window is request secondary billing. If you select this checkbox before saving, then the original claim will be re-opened and queued on the Billing page, and will be processed during the next billing run.");?></p>
            </div>
            <div class= "row" id="era-upload-div">
                <h4 class="oe-help-heading"><?php echo xlt("ERA Upload"); ?><a id = 'electronic_remits' name = 'electonic_remits' href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("Alternatively, you may choose to upload an electronic remittance (X12 835) file that you have obtained from your payer or clearinghouse. You can do this by first selecting the 'ERA upload' option in the initial 'Select Method' section. This brings up the 'ERA Upload' Section.")?></p>
                <form>
                    <fieldset>
                        <legend>
                            &nbsp;<span><?php echo xlt("Select Method");?></span>
                            <div class="float-right oe-legend-radio">
                                <label class="radio-inline">
                                    <input type="radio" id="invoice_search_help" name="radio-search" onclick="" value="inv-search-help"><?php echo xlt("Invoice Search"); ?>
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" id="era_upload_help" name="radio-search" onclick=""  value="era-upld-help"><?php echo xlt("ERA Upload"); ?>
                                </label>
                            </div>
                        </legend>
                    </fieldset>
                    <fieldset>
                        <legend>
                            &nbsp;<span><?php echo xlt("ERA Upload");?></span>
                        </legend>
                    </fieldset>
                </form>
                <br />
                <p><?php echo xlt("Start by clicking the Browse button and selecting the file to upload, and then clicking 'Upload' to perform the upload and display the corresponding invoices. In this case the other parameters mentioned above do not apply and will be ignored. Uploading saves the file but does not yet process its contents -- that is done separately as described below.")?></p>

                <p><?php echo xlt("If you have chosen to upload electronic remittances, then the search window redisplays itself with the matching invoices from the X12 file. You may click on any of these invoice numbers (as described above) if you wish to make any corrections before the remittance information is applied. To apply the changes, click the 'Process ERA File' button at the bottom of the page. This will produce a new window with a detailed report."); ?></p>

                <p> <strong style="color:blue"><?php echo xlt("Blue lines in this report are informational.");?></strong></p>
                <p><strong style="color:black"><?php echo xlt("Black lines show previously existing information.");?></strong></p>
                <p><strong style="color:green"><?php echo xlt("Green lines show changes that were successfully applied.");?> </strong></p>
                <p><strong style="color:red"><?php echo xlt("Red lines  indicate errors, or changes that were not applied; these must be processed manually. Currently denied claims and payment reversals are not handled automatically and so will appear in red.");?></strong></p>

                <p><?php echo xlt("If you have entered a Pay Date in the search page, this will override the posting date of payments and adjustments that are otherwise taken from the X12 file. This may be useful for reporting purposes, if you want your receipts reporting to use your posting date rather than the insurance company\'s processing date. Note that this will also affect dates of prior payments and adjustments that are put into secondary claims."); ?></p>

                <p><?php echo xlt("The X12 files as well as the resulting HTML output reports are archived in the 'era' subdirectory of the main OpenEMR installation directory. You will want to refer to these archives from time to time."); ?>
                    <span><strong>
                    <?php
                    $url = (!empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) ? 'https' : 'http';
                    $url .= "://" . $_SERVER['HTTP_HOST'] . "$web_root/sites/" . $_SESSION['site_id'] . "/documents/era/";
                    echo "$url";
                    ?>
                    </strong></span>
                </p>
            </div>
        </div><!--end of container div-->
    </body>
</html>

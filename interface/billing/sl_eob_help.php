<?php
 // Copyright (C) 2005-2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 include_once("../globals.php");
?>
<html>
<head>
<? html_header_show();?>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<title><?php xl('EOB Posting - Instructions','e')?></title>
</head>
<body>
<center><h2><?php xl('EOB Data Entry','e')?></h2></center>

<p><?php xl('This module promotes efficient entry of EOB data.','e')?>

<p><b><?php xl('Input Fields','e') ?></b>

<p><?php xl('The initial window is the invoice search page.  At the top you may enter
a source (e.g. check number), pay date and check amount.  The reason for the
source and pay date is so that you don\'t have to enter them over and over
again for each claim.  The amount that you enter will be decreased for each
invoice that is given part of the payment, and hopefully will end at zero
when you are done.','e')?>

<p><?php xl('Just below the check information is a blue area where you put in your
search parameters.  You can search by patient name, chart number, encounter
number or date of service, or any combination of these.  You may also select
whether you want to see all invoices, open invoices, or only invoices that
are due (by the patient).  Click the Search button to perform the search.','e')?>

<p><b><?php xl('Electronic Remits','e') ?></b>

<p><?php xl('Alternatively, you may use the search page to upload an electronic
remittance (X12 835) file that you have obtained from your payer or
clearinghouse.  You can do this by clicking the Browse button and selecting
the file to upload, and then clicking Search to perform the upload and display
the corresponding invoices.  In this case the other parameters mentioned above
do not apply and will be ignored.  Uploading saves the file but does not yet
process its contents -- that is done separately as described below.','e')?>

<p><?php xl('If you have chosen to upload electronic remittances, then the search
window redisplays itself with the matching invoices from the X12 file.  You
may click on any of these invoice numbers (as described below) if you wish to
make any corrections before the remittance information is applied.
To apply the changes, click the Process ERA File button at the bottom of the
page.  This will produce a new window with a detailed report.','e') ?>

<p><?php xl('Blue lines in this report are informational. Black lines
show previously existing information.  Green lines show changes
that were successfully applied.  Red lines indicate errors, or changes that
were not applied; these must be processed manually.  Currently denied claims and
payment reversals are not handled automatically and so will appear in red.','e')?>

<p><?php xl('If you have entered a Pay Date in the search page, this will
override the posting date of payments and adjustments that are otherwise
taken from the X12 file.  This may be useful for reporting purposes, if
you want your receipts reporting to use your posting date rather than the
insurance company\'s processing date.  Note that this will also affect
dates of prior payments and adjustments that are put into secondary
claims.','e') ?>

<p><?php xl('The X12 files as well as the resulting HTML output reports are archived
in the "era" subdirectory of the main OpenEMR installation directory.  You will
want to refer to these archives from time to time.  The URL is ','e') ?>
<?php
$url = ($_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
$url .= "://" . $_SERVER['HTTP_HOST'] . "$web_root/sites/" . $_SESSION['site_id'] . "/era/";
echo "<a href='$url'>$url</a>.\n";
?>

<p><b><?php xl('Manual Posting','e') ?></b>

<p><?php xl('The remaining information below applies only if you are posting
manually, or if you are doing manual corrections.','e')?>

<p><?php xl('Upon searching you are presented with a list of invoices.  You may click
on one of the invoice numbers to open a second window, which is the data entry
page for manual posting.  You may also click on a patient name if you want to
enter a note that the front office staff will see when the patient checks in, and
you may select invoices to appear on patient statements and print those
statements.','e')?>

<p><?php xl('Upon clicking an invoice number the "manual posting window" appears.
Here you can change the due date and notes for the invoice, select the party
for whom you are posting, and select the insurances for which all expected
paymants have been received.  Most importantly, for each billing code for which
an amount was charged, you can enter payment and adjustment information.','e')?>

<p><?php xl('The Source and Date columns are copied from the first page, so normally
you will not need to touch those.  You can put a payment amount in the Pay
column, an adjustment amount in the Adjust column, or both.  You can also click
the "W" on the right to automatically compute an adjustment value that
writes off the remainder of the charge for that line item.','e')?>

<p><?php xl('Pay attention to the "Done with" checkboxes.  After the insurances are
marked complete then we will start asking the patient to pay the remaining
balance; if you fail to mark all of the insurances complete then the remaining
amount will not be collected!  Also if there is a balance that the patient
should pay, then set the due date appropriately, as this will affect the
language that appears on patient statements.','e')?>

<p><?php xl('After the information is correctly entered, click the Save button.','e')?>

<p><?php xl('Another thing you can do in the posting window is request secondary billing.
If you select this checkbox before saving, then the original claim will be
re-opened and queued on the Billing page, and will be processed during the next
billing run.','e')?>
</body>
</html>

<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<title><?xl('EOB Posting - Instructions','e')?></title>
</head>
<body>
<center><h2><?xl('EOB Data Entry','e')?></h2></center>

<p><?xl('This module consists of two windows (not including this help page)
and promotes efficient entry of EOB data.','e')?>

<p><?xl('The first window is the invoice search page.  At the top you should
enter a source (e.g. check number), pay date and check amount.  The reason for the
source and pay date is so that you don\'t have to enter them over and over
again for each claim.  The amount that you enter will be decreased for each
invoice that is given part of the payment, and hopefully will end at zero
when you are done.','e')?>

<p><?xl('Just below the check information is a blue area where you put in your
search parameters.  You can search by patient name, chart number, encounter
number or date of service, or any combination of these.  You may also select
whether you want to see all invoices, open invoices, or only invoices that
are due (by the patient).  Click the Search button to perform the search.','e')?>

<p><?xl('Upon searching you are presented with a list of invoices.  Click on one
of the invoice numbers to open the second window, which is the data entry
page.  You may also click on a patient name if you want to enter a note that
the front office staff will see when the patient checks in, and you may
select invoices to appear on patient statements and print those statements.','e')?>

<p><?xl('Upon clicking an invoice number a "posting window" appears.  Here you can
change the due date and notes for the invoice, select the party for whom you
are posting, and select the insurances for which all expected paymants have
been received.  Most importantly, for each billing code for which an amount
was charged, you can enter payment and adjustment information.','e')?>

<p><?xl('The Source and Date columns are copied from the first page, so normally
you will not need to touch those.  You can put a payment amount in the Pay
column, an adjustment amount in the Adjust column, or both.  You can also click
the "W" on the right to automatically compute an adjustment value that
writes off the remainder of the charge for that line item.','e')?>

<p><?xl('Pay attention to the "Done with" checkboxes.  After the insurances are
marked complete then we will start asking the patient to pay the remaining
balance; if you fail to mark all of the insurances complete then the remaining
amount will not be collected!  Also if there is a balance that the patient
should pay, then set the due date appropriately, as this will affect the
language that appears on patient statements.','e')?>

<p><?xl('After the information is correctly entered, click the Save button.','e')?>

<p><?xl('Another thing you can do in the posting window is request secondary billing.
If you select this checkbox before saving, then the original claim will be
re-opened on the Billing page, and you will be able to select the new insurance
to be billed during the next billing run.','e')?>
</body>
</html>

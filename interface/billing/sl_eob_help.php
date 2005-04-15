<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<title>EOB Posting - Instructions</title>
</head>
<body>
<center><h2>EOB Data Entry</h2></center>

<p>This module consists of two windows (not including this help page)
and promotes efficient entry of EOB data.

<p>The first window is the invoice search page.  At the top you should
enter a check number, pay date and check amount.  The reason for the check
number and pay date is so that you don't have to enter them over and over
again for each claim.  The amount that you enter will be decreased for each
invoice that is given part of the payment, and hopefully will end at zero
when you are done.

<p>Just below the check information is a blue area where you put in your
search parameters.  You can search by patient name, chart number, encounter
number or date of service, or any combination of these.  The "Closed" checkbox,
if checked, will include closed invoices in the search.  Click the Search
button to perform the search.

<p>Upon searching you are presented with a list of invoices.  Click on one
of the invoice numbers to open the second window, which is the data entry
page.

<p>In the data entry page you can change the due date and notes for the invoice.
More importantly, for each billing code for which an amount was charged, you
can enter payment and adjustment information.

<p>The Source and Date columns are copied from the first page, so normally
you will not need to touch those.  You can put a payment amount in the Pay
column, an adjustment amount in the Adjust column, or both.  You can also click
the "W" on the right to automatically compute an adjustment value that
writes off the remainder of the charge for that line item.

<p>Pay attention to the due date.  This is the date when we will start asking
the patient to pay the remaining balance.  If there is a balance
that the patient should pay, then set the due date to the current date.  If
you will be submitting a secondary claim then set the due date about 40 days
into the future, or as otherwise prescribed by your site's policies.

<p>After the information is correctly entered, click the Save button.
</body>
</html>

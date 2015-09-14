Server Integration Method
=========================

Basic Overview
--------------

The Authorize.Net PHP SDK includes classes that can speed up implementing
a Server Integration Method solution.


Hosted Order/Receipt Page
-------------------------

The AuthorizeNetSIM_Form class aims to make it easier to setup the hidden
fields necessary for creating a SIM experience. While it is not necessary
to use the AuthorizeNetSIM_Form class to implement SIM, it may be handy for
reference.

The code below will generate a buy now button that leads to a hosted order page:

<form method="post" action="https://test.authorize.net/gateway/transact.dll">
<?php
$amount = "9.99";
$fp_sequence = "123";
$time = time();

$fingerprint = AuthorizeNetSIM_Form::getFingerprint($api_login_id, $transaction_key, $amount, $fp_sequence, $time);
$sim = new AuthorizeNetSIM_Form(
    array(
    'x_amount'        => $amount,
    'x_fp_sequence'   => $fp_sequence,
    'x_fp_hash'       => $fingerprint,
    'x_fp_timestamp'  => $time,
    'x_relay_response'=> "FALSE",
    'x_login'         => $api_login_id,
    )
);
echo $sim->getHiddenFieldString();?>
<input type="submit" value="Buy Now">
</form>

Fingerprint Generation
----------------------

To generate the fingerprint needed for a SIM transaction call the getFingerprint method:

$fingerprint = AuthorizeNetSIM_Form::getFingerprint($api_login_id, $transaction_key, $amount, $fp_sequence, $fp_timestamp);


Relay Response
--------------

The PHP SDK includes a AuthorizeNetSIM class for handling a relay response from
Authorize.Net.

To receive a relay response from Authorize.Net you can either configure the
url in the Merchant Interface or specify the url when submitting a transaction
with SIM using the "x_relay_url" field.

When a transaction occurs, Authorize.Net will post the transaction details to
this url. You can then craete a page on your server at a url such as
http://yourdomain.com/response_handler.php and execute any logic you want
when a transaction occurs. The AuthorizeNetSIM class makes it easy to verify
the transaction came from Authorize.Net and parse the response:

$response = new AuthorizeNetSIM;
if ($response->isAuthorizeNet())
{
  if ($response->approved)
  {
    // Activate magazine subscription
    magazine_subscription_activate($response->cust_id);
  }
}

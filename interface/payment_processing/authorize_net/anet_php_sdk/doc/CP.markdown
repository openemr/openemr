Card Present API
================

Basic Overview
--------------

The AuthorizeNetCP class creates a request object for submitting transactions
to the AuthorizeNetCP API. The AuthorizeNetCP class extends the AuthorizeNetAIM
class. See the AIM.markdown for help with the basics. This document contains
information regarding the special features of the AuthorizeNetCP class.


Merchant Credentials
--------------------

Please note that if you are using both the CNP and CP APIs your merchant
credentials will be different.

Setting Track Data
------------------

To set Track 1 and/or Track 2 data, use the respective methods like so:

$sale = new AuthorizeNetCP(CP_API_LOGIN_ID, CP_TRANSACTION_KEY);
$sale->setFields(
    array(
    'amount' => rand(1, 1000),
    'device_type' => '4',
    )
);
$sale->setTrack1Data('%B4111111111111111^CARDUSER/JOHN^1803101000000000020000831000000?');
$response = $sale->authorizeAndCapture();

$sale = new AuthorizeNetCP(CP_API_LOGIN_ID, CP_TRANSACTION_KEY);
$sale->setFields(
    array(
    'amount' => rand(1, 1000),
    'device_type' => '4',
    )
);
$sale->setTrack2Data('4111111111111111=1803101000020000831?');
$response = $sale->authorizeAndCapture();


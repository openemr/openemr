Advanced Integration Method
===========================

Basic Overview
--------------

The AuthorizeNetAIM class creates a request object for submitting transactions
to the AuthorizeNetAIM API. To use, create an instance of the class, set the fields
for your transaction, call the method you want to use (Authorize Only, Authorize & 
Capture, etc.) and you'll receive an AuthorizeNetAIM response object providing easy access
to the results of the transaction.

Including the SDK
-----------------

require_once 'anet_php_sdk/AuthorizeNet.php'; 

Setting Merchant Credentials
----------------------------
The easiest way to set credentials is to define constants which the SDK uses:
define("AUTHORIZENET_API_LOGIN_ID", "YOURLOGIN");
define("AUTHORIZENET_TRANSACTION_KEY", "YOURKEY");

You can also set credentials manually per request like so:

$sale = new AuthorizeNetAIM("YOUR_API_LOGIN_ID","YOUR_TRANSACTION_KEY");


Setting the Transaction Post Location
-------------------------------------

To post transactions to the live Authorize.Net gateway:
define("AUTHORIZENET_SANDBOX", false);

To post transactions to the Authorize.Net test server:
define("AUTHORIZENET_SANDBOX", true);

You can also set the location manually per request:
$sale->setSandbox(false);


Setting Fields
--------------

An Authorize.Net AIM request is simply a set of name/value pairs. The PHP SDK
allows you to set these fields in a few different ways depending on your
preference.

Note: to make things easier on the developer, the "x_" prefix attached to each
field in the AIM API has been removed. Thus, instead of setting $sale->x_card_num,
set $sale->card_num instead.

1.) By Setting Fields Directly:
$sale = new AuthorizeNetAIM;
$sale->amount = "1999.99";
$sale->card_num = '6011000000000012';
$sale->exp_date = '04/15';
$response = $sale->authorizeAndCapture();

2.) By Setting Multiple Fields at Once:
$sale = new AuthorizeNetAIM;
$sale->setFields(
    array(
    'amount' => rand(1, 1000),
    'card_num' => '6011000000000012',
    'exp_date' => '0415'
    )
);

3.) By Setting Special Items

To add line items or set custom fields use the respective functions:

Line Items:
$sale->addLineItem(
  'item1', // Item Id
  'Golf tees', // Item Name
  'Blue tees', // Item Description
  '2', // Item Quantity
  '5.00', // Item Unit Price
  'N' // Item taxable
  );

Custom Fields:
$sale->setCustomField("coupon_code", "SAVE2011");

4.) By Passing in Objects

Each property will be copied from the object to the AIM request.

$sale = new AuthorizeNetAIM;
$customer = (object)array();
$customer->first_name = "Jane";
$customer->last_name = "Smith";
$customer->company = "Jane Smith Enterprises Inc.";
$customer->address = "20 Main Street";
$customer->city = "San Francisco";
$customer->state = "CA";
$customer->zip = "94110";
$customer->country = "US";
$customer->phone = "415-555-5557";
$customer->fax = "415-555-5556";
$customer->email = "foo@example.com";
$customer->cust_id = "55";
$customer->customer_ip = "98.5.5.5";
$sale->setFields($customer);

Submitting Transactions
-----------------------
To submit a transaction call one of the 7 methods: 

-authorizeAndCapture()
-authorizeOnly()
-priorAuthCapture()
-void()
-captureOnly()
-credit()

Each method has optional parameters which highlight the fields required by the
Authorize.Net API for that transaction type.


eCheck
------
To submit an electronic check transaction you can set the required fields individually
or simply use the setECheck method:

$sale = new AuthorizeNetAIM;
$sale->amount = "45.00";
$sale->setECheck(
  '121042882', // bank_aba_code
  '123456789123', // bank_acct_num
  'CHECKING', // bank_acct_type
  'Bank of Earth', // bank_name
  'Jane Doe', // bank_acct_name
  'WEB' // echeck_type
);
$response  = $sale->authorizeAndCapture();


Partial Authorization Transactions
----------------------------------
To enable partial authorization transactions set the partial_auth flag
to true:

$sale->allow_partial_auth = true;

You should receive a split tender id in the response if a partial auth
is made:

$split_tender_id = $response->split_tender_id;


Itemized Order Information
--------------------------
To add itemized order information use the addLineItem method:

$auth->addLineItem(
  'item1', // Item Id
  'Golf tees', // Item Name
  'Blue tees', // Item Description
  '2', // Item Quantity
  '5.00', // Item Unit Price
  'N' // Item taxable
  );


Merchant Defined Fields
-----------------------
You can use the setCustomField method to set any custom merchant defined field(s):

$sale->setCustomField("entrance_source", "Search Engine");
$sale->setCustomField("coupon_code", "SAVE2011");


Transaction Response
--------------------
When you submit an AIM transaction you receive an AuthorizeNetAIM_Response
object in return. You can access each name/value pair in the response as
you would normally expect:

$response = $sale->authorizeAndCapture();
$response->response_code;
$response->response_subcode;
$response->response_reason_code;
$response->transaction_id;


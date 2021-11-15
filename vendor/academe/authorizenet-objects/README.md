# PHP Value Objects for Authorize.Net

[![Build Status](https://travis-ci.org/academe/authorizenet-objects.svg?branch=master)](https://travis-ci.org/academe/authorizenet-objects)
[![Latest Stable Version](https://poser.pugx.org/academe/authorizenet-objects/v/stable)](https://packagist.org/packages/academe/authorizenet-objects)
[![Total Downloads](https://poser.pugx.org/academe/authorizenet-objects/downloads)](https://packagist.org/packages/academe/authorizenet-objects)
[![Latest Unstable Version](https://poser.pugx.org/academe/authorizenet-objects/v/unstable)](https://packagist.org/packages/academe/authorizenet-objects)
[![License](https://poser.pugx.org/academe/authorizenet-objects/license)](https://packagist.org/packages/academe/authorizenet-objects)

The aim of this pacjage is to model, as value objects, all the data structures
defined here:
http://developer.authorize.net/api/reference/

The data structure for the API messages is defined in the
[XSD DTD](https://api.authorize.net/xml/v1/schema/AnetApiSchema.xsd).
Although that is for the XSL API, the JSON API mirrors it closely.
This DTD has been parsed into
[this documentation page](https://academe.github.io/authorizenet-objects/AnetApiSchema.html)
using [xs3p.xsl](https://github.com/bitfehler/xs3p), which can be a little easier
to read, and contains fewer errors than the official HTML documentation.
Practically, use the two together.

## General Project Stucture

The request messages are under `Academe\AuthorizeNet\Request`.
All these requests take a `Academe\AuthorizeNet\Auth\MerchantAuthentication` object
to provide authentication and one or more additional objects to carry the main
message detail.

The `Academe\AuthorizeNet\Request\CreateTransaction` message takes a transaction
object from under `Academe\AuthorizeNet\Request\Transaction`.
The `Transaction` object is built mainly from scalar data and other objects under
`Academe\AuthorizeNet\Request\Model`.

There are collections under `Academe\AuthorizeNet\Collections` used to group some
of the `Model` objects, for example `LineItems` or `UserFields`.
These `Collections` may be moved to `Academe\AuthorizeNet\Request` at some point,
so be ready for that.

Note: this documentation is sparse at the moment, but the code covers much of the API.

## Examples

One package that uses this package is the
[Authorize.Net API](https://github.com/academe/omnipay-authorizenetapi)
driver for the [Omnipay](https://omnipay.thephpleague.com/) project.

Further libraries can then wrap these objects into a gateway driver.
Any request object, once constructed, can be serialised to JSON (`json_encode()`)
to provide the body of the request that the API will expect.

The response from Authorize.Net will be a JSON structure, which can be parsed
by the application. The intention is also to create a factory in this package
that will parse the response into value objects and collections.
Many of the response value objects have been created, but there is no factory
yet to fill them up with data.

```php
<?php

use Academe\AuthorizeNet;
use Academe\AuthorizeNet\Auth;
use Academe\AuthorizeNet\Amount;
use Academe\AuthorizeNet\Payment;
use Academe\AuthorizeNet\Request;
use Academe\AuthorizeNet\Collections;

// Composer: moneyphp/money
use Money\Money;

require 'vendor/autoload.php';

echo "<pre>";

// Set up authorisation details.
$auth = new Auth\MerchantAuthentication('xxx', 'yyy');

// Create a credit card.
$credit_card = new Payment\CreditCard('4000123412341234', '1220', '123');

// Or create a magnetic stripe track (only one can be used).
$track1 = new Payment\Track1('TTTTTTTTTTTTTTTT11111');
$track2 = new Payment\Track2('TTTTTTTT2222222222222222');

// Create order details.
$order = new Request\Model\Order('orderx', 'ordery');

// Create customer detals.
$customer = new Request\Model\Customer('business', 'Customer ID', 'customer@example.com');

// Create retail flags
$retail = new Request\Model\Retail(2, 3, 'HJSHDJSDJKSD');

// Create a billing name and address.
$billTo = new Request\Model\NameAddress('BFirstname', 'BLastname', null, 'Address Line');
$billTo = $billTo->withCompany('My Billing Company Ltd')->withZip('ZippyZipBill');

// Create a shipping name and address.
$shipTo = new Request\Model\NameAddress();
// A single with() can set multiple values at once.
$shipTo = $shipTo->with([
    'firstName' => 'Firstname',
    'lastName' => 'Lastname',
    'city' => 'My City',
    'country' => 'United Kingdom',
]);

// Create a total amount of £24.99
$amount = new Amount\Amount('GBP', 2499);
// Or maybe just £19.99
$amount = $amount->withMajorUnit(19.99);

// Better still, use moneyphp/money to set the amount to £15.49
$money_php_object = Money::GBP(1549);
$amount = new Amount\MoneyPhp($money_php_object);

// Create tax amount.
// All parameters have a with*() method available.
$tax = new Request\Model\ExtendedAmount();
$tax = $tax->withName('Tax Name')->withDescription('Tax Description');
$tax = $tax->withAmount(new Amount\MoneyPhp(Money::GBP(99));

// Set up some line items.
// Note these collections are not value objects. Should they be?
$lineItems = new Collections\LineItems();
$lineItems->push(new Request\Model\LineItem(1, 'Item Name 1', 'Item Desc 1', 1.5, new Amount\MoneyPhp(Money::GBP(49)), false));
$lineItems->push(new Request\Model\LineItem(2, 'Item Name 2', 'Item Desc 2', 2, new Amount\MoneyPhp(Money::GBP(97)), true));

// Set up some transaction settings.
$transactionSettings = new Collections\TransactionSettings();
$transactionSettings->push(new Request\Model\Setting('Foo', 'Bar'));

// And add some user fields.
$userFields = new Collections\UserFields();
$userFields->push(new Request\Model\UserField('UserFoo', 'UserBar'));

// Now put most of these into an Auth Capture transaction.
$auth_capture_transaction = new Request\Transaction\AuthCapture($amount);
$auth_capture_transaction = $auth_capture_transaction->withPayment(/*$track1*/ $credit_card);
$auth_capture_transaction = $auth_capture_transaction->withCreateProfile(false);
$auth_capture_transaction = $auth_capture_transaction->withTaxExempt(true);
$auth_capture_transaction = $auth_capture_transaction->withLineItems($lineItems);
$auth_capture_transaction = $auth_capture_transaction->withTransactionSettings($transactionSettings);
$auth_capture_transaction = $auth_capture_transaction->withUserFields($userFields);
$auth_capture_transaction = $auth_capture_transaction->withSolutionId('SOLLLL');
$auth_capture_transaction = $auth_capture_transaction->withTax($tax)->withDuty($tax)->withShipping($tax);
$auth_capture_transaction = $auth_capture_transaction->withPoNumber('myPoNumber');
$auth_capture_transaction = $auth_capture_transaction->withCustomer($customer);
$auth_capture_transaction = $auth_capture_transaction->withRetail($retail);

// You can set multiple items using an array:

$auth_capture_transaction = $auth_capture_transaction->with([
    'order' => $order,
    'shipTo' => $shipTo,
    'billTo' => $billTo,
    'employeeId' => '1234',
    'customerIp' => '1.2.3.4',
]);


// Add the auth capture transaction to the transaction request, along with the auth details.
$transaction_request = new Request\CreateTransaction($auth, $auth_capture_transaction);

// Display the resulting JSON request message.
echo '<p>' . $transaction_request->getObjectName() . ': </p>';
echo "<textarea style='width:100%;height: 12em'>" . json_encode($transaction_request) . "</textarea>";

/*
{
   "createTransactionRequest":{
      "merchantAuthentication":{
         "name":"xxx",
         "transactionKey":"yyy"
      },
      "refId":"REFREFREF",
      "transactionRequest":{
         "transactionType":"authCaptureTransaction",
         "amount":"9.99",
         "currencyCode":"GBP",
         "payment":{
            "creditCard":{
               "cardNumber":"4000123412341234",
               "expirationDate":"1220",
               "cardCode":"123"
            }
         },
         "profile":{
            "createProfile":false
         },
         "solution":{
            "id":"SOLLLL"
         },
         "order":{
            "invoiceNumber":"orderx",
            "description":"ordery"
         },
         "lineItems":{
            "lineItem":[
               {
                  "itemId":1,
                  "name":"Item Name 1",
                  "description":"Item Desc 1",
                  "quantity":1.5,
                  "unitPrice":"0.49",
                  "taxable":false
               },
               {
                  "itemId":2,
                  "name":"Item Name 2",
                  "description":"Item Desc 2",
                  "quantity":2,
                  "unitPrice":"0.97",
                  "taxable":true
               }
            ]
         },
         "tax":{
            "amount":"9.99",
            "name":"Tax Name",
            "description":"Tax Description"
         },
         "duty":{
            "amount":"9.99",
            "name":"Tax Name",
            "description":"Tax Description"
         },
         "shipping":{
            "amount":"9.99",
            "name":"Tax Name",
            "description":"Tax Description"
         },
         "taxExempt":true,
         "poNumber":"myPoNumber",
         "customer":{
            "type":"business",
            "id":"Customer ID",
            "email":"customer@example.com"
         },
         "billTo":{
            "firstName":"BFirstname",
            "lastName":"BLastname",
            "company":"My Billing Company Ltd",
            "address":"Address Line",
            "zip":"ZippyZipBill"
         },
         "shipTo":{
            "firstName":"Firstname",
            "lastName":"Lastname",
            "city":"My City",
            "country":"United Kingdom"
         },
         "customerIP":"1.2.3.4",
         "cardholderAuthentication":{
            "authenticationIndicator":"AAAA"
         },
         "retail":{
            "marketType":2,
            "deviceType":3,
            "customerSignature":"HJSHDJSDJKSD"
         },
         "employeeId":"1234",
         "transactionSettings":{
            "setting":[
               {
                  "settingName":"Foo",
                  "settingValue":"Bar"
               }
            ]
         },
         "userFields":{
            "userField":[
               {
                  "name":"UserFoo",
                  "value":"UserBar"
               }
            ]
         }
      }
   }
}
*/
```

This can be sent to the gateway simply (and naively, just for demonstration) using Guzzle:

```php
// "guzzlehttp/guzzle": "~6.0"
use GuzzleHttp\Client;

$client = new GuzzleHttp\Client();

// sandbox endpoint
$endpoint = 'https://apitest.authorize.net/xml/v1/request.api';

$response = $client->request('POST', $endpoint, [
    // Just pass the object and Guzzle will cast it to JSON.
    'json' => $transaction_request,
]);

var_dump((string)$response->getBody());

/*
{
   "transactionResponse":{
      "responseCode":"1",
      "authCode":"59WHY9",
      "avsResultCode":"Y",
      "cvvResultCode":"P",
      "transId":"60020301993",
      "refTransID":"",
      "transHash":"2EA6BB2D8D88C587EA16C77C44C7B8B2",
      "testRequest":"0",
      "accountNumber":"XXXX1234",
      "entryMode":"Keyed",
      "accountType":"Visa",
      "messages":[
         {
            "code":"1",
            "description":"This transaction has been approved."
         }
      ],
      "userFields":[
         {
            "name":"UserFoo",
            "value":"UserBar"
         }
      ],
      "transHashSha2":""
   },
   "refId":"5678917213",
   "messages":{
      "resultCode":"Ok",
      "message":[
         {
            "code":"I00001",
            "text":"Successful."
         }
      ]
   }
}
*/
```

This response data can then be parsed into nested objects:

```php
$responseObject = new \Academe\AuthorizeNet\Response\Response(json_decode((string)$response->getBody(), true));
```


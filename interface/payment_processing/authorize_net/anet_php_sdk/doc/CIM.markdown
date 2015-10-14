CIM API
=======

Basic Overview
--------------

The AuthorizeNetCIM class creates a request object for submitting transactions
to the Authorize.Net CIM API.


Creating a Customer Profile
---------------------------

To create a new cusomter profile, first create a new AuthorizeNetCustomer
object.

$customerProfile = new AuthorizeNetCustomer;
$customerProfile->description = "Description of customer";
$customerProfile->merchantCustomerId = 123;
$customerProfile->email = "user@domain.com";

You can then create an add payment profiles and addresses to this
customer object.

// Add payment profile.
$paymentProfile = new AuthorizeNetPaymentProfile;
$paymentProfile->customerType = "individual";
$paymentProfile->payment->creditCard->cardNumber = "4111111111111111";
$paymentProfile->payment->creditCard->expirationDate = "2015-10";
$customerProfile->paymentProfiles[] = $paymentProfile;

// Add another payment profile.
$paymentProfile2 = new AuthorizeNetPaymentProfile;
$paymentProfile2->customerType = "business";
$paymentProfile2->payment->bankAccount->accountType = "businessChecking";
$paymentProfile2->payment->bankAccount->routingNumber = "121042882";
$paymentProfile2->payment->bankAccount->accountNumber = "123456789123";
$paymentProfile2->payment->bankAccount->nameOnAccount = "Jane Doe";
$paymentProfile2->payment->bankAccount->echeckType = "WEB";
$paymentProfile2->payment->bankAccount->bankName = "Pandora Bank";
$customerProfile->paymentProfiles[] = $paymentProfile2;


// Add shipping address.
$address = new AuthorizeNetAddress;
$address->firstName = "john";
$address->lastName = "Doe";
$address->company = "John Doe Company";
$address->address = "1 Main Street";
$address->city = "Boston";
$address->state = "MA";
$address->zip = "02412";
$address->country = "USA";
$address->phoneNumber = "555-555-5555";
$address->faxNumber = "555-555-5556";
$customerProfile->shipToList[] = $address;

// Add another shipping address.
$address2 = new AuthorizeNetAddress;
$address2->firstName = "jane";
$address2->lastName = "Doe";
$address2->address = "11 Main Street";
$address2->city = "Boston";
$address2->state = "MA";
$address2->zip = "02412";
$address2->country = "USA";
$address2->phoneNumber = "555-512-5555";
$address2->faxNumber = "555-523-5556";
$customerProfile->shipToList[] = $address2;


Next, create an AuthorizeNetCIM object:

$request = new AuthorizeNetCIM;

Finally, call the createCustomerProfile method and pass in your
customer object:

$response = $request->createCustomerProfile($customerProfile);

The response object provides some helper methods for easy access to the
results of the transaction:

$new_customer_id = $response->getCustomerProfileId();

The response object also stores the XML response as a SimpleXml element
which you can access like so:

$new_customer_id = $response->xml->customerProfileId
  
You can also run xpath queries against the result:

$array = $response->xpath('customerProfileId');
$new_customer_id = $array[0];


Deleting a Customer Profile
---------------------------

To delete a customer profile first create a new AuthorizeNetCIM object:

$request = new AuthorizeNetCIM;

Then call the deleteCustomerProfile method:

request->deleteCustomerProfile($customer_id);


Retrieving a Customer Profile
-----------------------------

To retrieve a customer profile call the getCustomerProfile method:

$response = $request->getCustomerProfile($customerProfileId);


Validation Mode
---------------

Validation mode allows you to generate a test transaction at the time you create a customer profile. In Test Mode, only field validation is performed. In Live Mode, a transaction is generated and submitted to the processor with the amount of $0.00 or $0.01. If successful, the transaction is immediately voided.

To create a customer profile with Validation mode, simply pass in the
a value for the validation mode parameter on the createCustomerProfile method:

$response = $request->createCustomerProfile($customerProfile, "testMode");

You can access the validation response for each payment profile via xpath,
the SimpleXML element or the getValidationResponses method:

$validationResponses = $response->getValidationResponses();
  foreach ($validationResponses as $vr) {
    echo $vr->approved;
}


Updating a Customer Profile
---------------------------

Call the updateCustomerProfile method with the customerProfileId and customerProfile
parameters:

$response = $request->updateCustomerProfile($customerProfileId, $customerProfile);


Adding a Payment Profile
------------------------


$paymentProfile = new AuthorizeNetPaymentProfile;
$paymentProfile->customerType = "individual";
$paymentProfile->payment->creditCard->cardNumber = "4111111111111111";
$paymentProfile->payment->creditCard->expirationDate = "2015-10";
$response = $request->createCustomerPaymentProfile($customerProfileId, $paymentProfile);


Updating a Payment Profile
--------------------------

$paymentProfile->payment->creditCard->cardNumber = "4111111111111111";
$paymentProfile->payment->creditCard->expirationDate = "2017-11";
$response = $request->updateCustomerPaymentProfile($customerProfileId,$paymentProfileId, $paymentProfile);

Adding a Shipping Address
-------------------------



$address = new AuthorizeNetAddress;
$address->firstName = "john";
$address->lastName = "Doe";
$address->company = "John Doe Company";
$address->address = "1 Main Street";
$address->city = "Boston";
$address->state = "MA";
$address->zip = "02412";
$address->country = "USA";
$address->phoneNumber = "555-555-5555";
$address->faxNumber = "555-555-5556";
$response = $request->createCustomerShippingAddress($customerProfileId, $address);
$customerAddressId = $response->getCustomerAddressId();

Updating a Shipping Address
---------------------------

// Update shipping address.
$address->address = "2 First Street";
$response = $request->updateCustomerShippingAddress($customerProfileId, $customerAddressId, $address);


Creating Transactions
---------------------
    
// Create Auth & Capture Transaction
$transaction = new AuthorizeNetTransaction;
$transaction->amount = "9.79";
$transaction->customerProfileId = $customerProfileId;
$transaction->customerPaymentProfileId = $paymentProfileId;
$transaction->customerShippingAddressId = $customerAddressId;
    
$lineItem              = new AuthorizeNetLineItem;
$lineItem->itemId      = "4";
$lineItem->name        = "Cookies";
$lineItem->description = "Chocolate Chip";
$lineItem->quantity    = "4";
$lineItem->unitPrice   = "1.00";
$lineItem->taxable     = "true";

$lineItem2             = new AuthorizeNetLineItem;
$lineItem2->itemId     = "4";
$lineItem2->name       = "Cookies";
$lineItem2->description= "Peanut Butter";
$lineItem2->quantity   = "4";
$lineItem2->unitPrice  = "1.00";
$lineItem2->taxable    = "true";

$transaction->lineItems[] = $lineItem;
$transaction->lineItems[] = $lineItem2;
    
    
$response = $request->createCustomerProfileTransaction("AuthCapture", $transaction);
$transactionResponse = $response->getTransactionResponse();
$transactionId = $transactionResponse->transaction_id;
    
    
Voiding a Transaction
---------------------

$transaction = new AuthorizeNetTransaction;
$transaction->transId = $transactionId;
$response = $request->createCustomerProfileTransaction("Void", $transaction);


Deleting a Shipping Address
---------------------------

$response = $request->deleteCustomerShippingAddress($customerProfileId, $customerAddressId);


Deleting a Payment Profile
--------------------------

$response = $request->deleteCustomerPaymentProfile($customerProfileId, $paymentProfileId);
  


Getting Customer Profile IDs
----------------------------

$response = $request->getCustomerProfileIds();
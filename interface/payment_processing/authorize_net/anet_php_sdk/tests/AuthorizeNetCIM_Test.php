<?php

require_once 'AuthorizeNet_Test_Config.php';


class AuthorizeNetCIM_Test extends PHPUnit_Framework_TestCase
{
    
  public function testDeleteAllCustomerProfiles()
  {
    $request = new AuthorizeNetCIM;
    $response = $request->getCustomerProfileIds();
    $customers = $response->getCustomerProfileIds();
       
    foreach ($customers as $customer) {
      $response = $request->deleteCustomerProfile($customer);
      $this->assertTrue($response->isOk());
    }
       

  }
  
  public function testXPath()
  {
  
    // Create new customer profile
    $request = new AuthorizeNetCIM;
    $customerProfile = new AuthorizeNetCustomer;
    $customerProfile->description = $description = "Some Description of customer 2";
    $customerProfile->merchantCustomerId = $merchantCustomerId = time().rand(1,150);
    $customerProfile->email = $email = "test2@domain.com";

    // Add payment profile.
    $paymentProfile = new AuthorizeNetPaymentProfile;
    $paymentProfile->customerType = "individual";
    $paymentProfile->payment->creditCard->cardNumber = "4111111111111111";
    $paymentProfile->payment->creditCard->expirationDate = "2021-04";
    $customerProfile->paymentProfiles[] = $paymentProfile;

    $response = $request->createCustomerProfile($customerProfile);
    $this->assertTrue($response->isOk());
    $array = $response->xpath('customerProfileId');
    $this->assertEquals($response->getCustomerProfileId(), "{$array[0]}");
    
    $profile = $request->getCustomerProfile($response->getCustomerProfileId());
    
    $this->assertEquals($email, (string)array_pop($profile->xpath('profile/email')));
    $this->assertEquals($email, (string)array_pop($profile->xpath('profile/email')));
    $this->assertEquals($description, (string)array_pop($profile->xpath('profile/description')));
    $this->assertEquals($merchantCustomerId, (string)array_pop($profile->xpath('profile/merchantCustomerId')));
    
  }


 
  public function testCreateCustomerProfile()
  {
    // Create new customer profile
    $request = new AuthorizeNetCIM;
    $customerProfile = new AuthorizeNetCustomer;
    $customerProfile->description = "Description of customer";
    $customerProfile->merchantCustomerId = time().rand(1,100);
    $customerProfile->email = "blahbla@domain.com";
    
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
    
    $response = $request->createCustomerProfile($customerProfile);
    $this->assertTrue($response->isOk());
    $this->assertEquals(2, count($response->getCustomerShippingAddressIds()));
    $this->assertEquals(2, count($response->getCustomerPaymentProfileIds()));
    $customerProfileId = $response->getCustomerProfileId();
    
    $this->assertEquals($response->getCustomerProfileId(), "{$response->xml->customerProfileId}");
    
    
    
    $response = $request->getCustomerProfile($customerProfileId);
    $this->assertEquals($customerProfile->description, (string)$response->xml->profile->description);
    $this->assertEquals($customerProfile->merchantCustomerId, (string)$response->xml->profile->merchantCustomerId);
    $this->assertEquals($customerProfile->email, (string)$response->xml->profile->email);
    $this->assertEquals(substr($customerProfile->paymentProfiles[0]->payment->creditCard->cardNumber, -4, 4), substr((string)$response->xml->profile->paymentProfiles->payment->creditCard->cardNumber, -4, 4));
    
    
    
    $this->assertTrue($response->isOk());
  
  
  }
  
  public function testGetCustomerProfile()
  {
    // Create new customer profile
    $request = new AuthorizeNetCIM;
    $customerProfile = new AuthorizeNetCustomer;
    $customerProfile->description = "Description of customer";
    $customerProfile->merchantCustomerId = time().rand(1,10);
    $customerProfile->email = "blahlah@domain.com";
    $response = $request->createCustomerProfile($customerProfile);
    $this->assertTrue($response->isOk());
    $customerProfileId = $response->getCustomerProfileId();
    
    $response = $request->getCustomerProfile($customerProfileId);

    $this->assertTrue($response->isOk());
    
  }
  
  public function testCreateCustomerProfileWithValidationMode()
  {
      // Create new customer profile
     $request = new AuthorizeNetCIM;
     $customerProfile = new AuthorizeNetCustomer;
     $customerProfile->description = "Some Description of customer 2";
     $customerProfile->merchantCustomerId = time().rand(1,10);
     $customerProfile->email = "test2@domain.com";
     
     // Add payment profile.
     $paymentProfile = new AuthorizeNetPaymentProfile;
     $paymentProfile->customerType = "individual";
     $paymentProfile->payment->creditCard->cardNumber = "4111111111111111";
     $paymentProfile->payment->creditCard->expirationDate = "2015-04";
     $customerProfile->paymentProfiles[] = $paymentProfile;
     
     // Add another payment profile.
     $paymentProfile2 = new AuthorizeNetPaymentProfile;
     $paymentProfile2->customerType = "individual";
     $paymentProfile2->payment->creditCard->cardNumber = "4222222222222";
     $paymentProfile2->payment->creditCard->expirationDate = "2019-10";
     $customerProfile->paymentProfiles[] = $paymentProfile2;
     
     $response = $request->createCustomerProfile($customerProfile, "testMode");

     $this->assertTrue($response->isOk());
     
     $validationResponses = $response->getValidationResponses();
     foreach ($validationResponses as $vr) {
         $this->assertTrue($vr->approved);
     }
      
  }
  
  public function testUpdateSplitTenderGroup()
  {
      // Create a partial auth test transaction
      $amount = 4.92;
      
      $sale = new AuthorizeNetAIM;
      $sale->amount = $amount;
      $sale->card_num = '4222222222222';
      $sale->zip = "46225";
      $sale->exp_date = '04/24';
      $sale->allow_partial_auth = true;
      $response = $sale->authorizeAndCapture();
      $this->assertTrue($response->held);
      $this->assertEquals("1.23", $response->amount);
      $this->assertEquals($amount, $response->requested_amount);
      $split_tender_id = $response->split_tender_id;
      
      // Charge a bit more
      $sale = new AuthorizeNetAIM;
      $sale->amount = 1.23;
      $sale->card_num = '6011000000000012';
      $sale->exp_date = '04/26';
      $sale->split_tender_id = $split_tender_id;
      $sale->allow_partial_auth = true;
      $response = $sale->authorizeAndCapture();
      $this->assertTrue($response->approved);
      
      // Void the group of partial auths.
      
      $request = new AuthorizeNetCIM;
      $response = $request->updateSplitTenderGroup($split_tender_id, "voided");
      $this->assertTrue($response->isOk());
  }

  public function testAll()
  {
    // Create new customer profile
    $request = new AuthorizeNetCIM;
    $customerProfile = new AuthorizeNetCustomer;
    $customerProfile->description = "Description of customer";
    $customerProfile->merchantCustomerId = time().rand(1,10);
    $customerProfile->email = "blahblahblah@domain.com";
    $response = $request->createCustomerProfile($customerProfile);
    $this->assertTrue($response->isOk());
    $customerProfileId = $response->getCustomerProfileId();
    
    // Update customer profile
    $customerProfile->description = "New description";
    $customerProfile->email = "newemail@domain.com";
    $response = $request->updateCustomerProfile($customerProfileId, $customerProfile);
    $this->assertTrue($response->isOk());
    
    // Add payment profile.
    $paymentProfile = new AuthorizeNetPaymentProfile;
    $paymentProfile->customerType = "individual";
    $paymentProfile->payment->creditCard->cardNumber = "4111111111111111";
    $paymentProfile->payment->creditCard->expirationDate = "2015-10";
    $response = $request->createCustomerPaymentProfile($customerProfileId, $paymentProfile);
    $this->assertTrue($response->isOk());
    $paymentProfileId = $response->getPaymentProfileId();
    
    // Update payment profile.
    $paymentProfile->payment->creditCard->cardNumber = "4111111111111111";
    $paymentProfile->payment->creditCard->expirationDate = "2017-11";
    $response = $request->updateCustomerPaymentProfile($customerProfileId,$paymentProfileId, $paymentProfile);
    $this->assertTrue($response->isOk());
    
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
    $response = $request->createCustomerShippingAddress($customerProfileId, $address);
    $this->assertTrue($response->isOk());
    $customerAddressId = $response->getCustomerAddressId();
    
    // Update shipping address.
    $address->address = "2 First Street";
    $response = $request->updateCustomerShippingAddress($customerProfileId, $customerAddressId, $address);
    $this->assertTrue($response->isOk());
    
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
    $this->assertTrue($response->isOk());
    $transactionResponse = $response->getTransactionResponse();
    $this->assertTrue($transactionResponse->approved);
    $transactionId = $transactionResponse->transaction_id;
    
    // Void the transaction
    $transaction = new AuthorizeNetTransaction;
    $transaction->transId = $transactionId;
    $response = $request->createCustomerProfileTransaction("Void", $transaction);
    $this->assertTrue($response->isOk());
    $transactionResponse = $response->getTransactionResponse();
    $this->assertTrue($transactionResponse->approved);
    
        
    // Delete Shipping Address
    $response = $request->deleteCustomerShippingAddress($customerProfileId, $customerAddressId);
    $this->assertTrue($response->isOk());
    
    // Delete payment profile.
    $response = $request->deleteCustomerPaymentProfile($customerProfileId, $paymentProfileId);
    $this->assertTrue($response->isOk());
    
    
    // Delete the profile id for future testing.
    $response = $request->deleteCustomerProfile($customerProfileId);
    $this->assertTrue($response->isOk());
  }
  

  

  public function testGetCustomerProfileIds()
  {
    // Create new customer profile
    $request = new AuthorizeNetCIM;
    $customerProfile = new AuthorizeNetCustomer;
    $customerProfile->description = "Description of customer";
    $customerProfile->merchantCustomerId = time().rand(1,10);
    $customerProfile->email = "blahblahblah@domain.com";
    $response = $request->createCustomerProfile($customerProfile);
    $this->assertTrue($response->isOk());
    $customerProfileId = $response->getCustomerProfileId();
    
    $response = $request->getCustomerProfileIds();
    $this->assertTrue($response->isOk());
    $this->assertTrue(in_array($customerProfileId, $response->getCustomerProfileIds()));
    
    
  }
 
  
  

}
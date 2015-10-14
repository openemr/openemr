<?php

require_once 'AuthorizeNet_Test_Config.php';

class AuthorizeNetARB_Test extends PHPUnit_Framework_TestCase
{

    public function testAllMethods()
    {
        // Set the subscription fields.
        $subscription = new AuthorizeNet_Subscription;
        $subscription->name = "Short subscription";
        $subscription->intervalLength = "1";
        $subscription->intervalUnit = "months";
        $subscription->startDate = "2011-03-12";
        $subscription->totalOccurrences = "14";
        $subscription->amount = rand(1,100);
        $subscription->creditCardCardNumber = "6011000000000012";
        $subscription->creditCardExpirationDate = "2018-10";
        $subscription->creditCardCardCode = "123";
        $subscription->billToFirstName = "john";
        $subscription->billToLastName = "doe";
        
        // Create the subscription.
        $request = new AuthorizeNetARB;
        $response = $request->createSubscription($subscription);
        $this->assertTrue($response->isOk());
        $subscription_id = $response->getSubscriptionId();
        
        // Get the subscription status
        $status_request = new AuthorizeNetARB;
        $status_response = $status_request->getSubscriptionStatus($subscription_id);
        $this->assertEquals("active",$status_response->getSubscriptionStatus());
        
        // Update the subscription
        $update_request = new AuthorizeNetARB;
        $updated_subscription_info = new AuthorizeNet_Subscription;
        $updated_subscription_info->billToFirstName = "jane";
        $updated_subscription_info->billToLastName = "smith";
        $updated_subscription_info->creditCardCardNumber = "6011000000000012";
        $updated_subscription_info->creditCardExpirationDate = "2019-10";
        $updated_subscription_info->creditCardCardCode = "423";
        $update_response = $update_request->updateSubscription($subscription_id, $updated_subscription_info);
        $this->assertTrue($update_response->isOk());
        
        // Cancel the subscription
        $cancellation = new AuthorizeNetARB;
        $cancel_response = $cancellation->cancelSubscription($subscription_id);
        $this->assertTrue($cancel_response->isOk());
        
        // Get the subscription status
        $status_request = new AuthorizeNetARB;
        $status_response = $status_request->getSubscriptionStatus($subscription_id);
        $this->assertEquals("canceled", $status_response->getSubscriptionStatus());
        
    }


    public function testCreateSubscriptionLong()
    {
        
        $subscription = new AuthorizeNet_Subscription;
        $subscription->name = "test subscription";
        $subscription->intervalLength = "1";
        $subscription->intervalUnit = "months";
        $subscription->startDate = "2015-03-12";
        $subscription->totalOccurrences = "14";
        $subscription->trialOccurrences = "";
        $subscription->amount = "6.99";
        $subscription->trialAmount = "";
        $subscription->creditCardCardNumber = "6011000000000012";
        $subscription->creditCardExpirationDate = "2018-10";
        $subscription->creditCardCardCode = "123";
        $subscription->bankAccountAccountType = "";
        $subscription->bankAccountRoutingNumber = "";
        $subscription->bankAccountAccountNumber = "";
        $subscription->bankAccountNameOnAccount = "";
        $subscription->bankAccountEcheckType = "";
        $subscription->bankAccountBankName = "";
        $subscription->orderInvoiceNumber = "";
        $subscription->orderDescription = "";
        $subscription->customerId = "12";
        $subscription->customerEmail = "foo@domain.com";
        $subscription->customerPhoneNumber = "";
        $subscription->customerFaxNumber = "";
        $subscription->billToFirstName = "john";
        $subscription->billToLastName = "doe";
        $subscription->billToCompany = "";
        $subscription->billToAddress = "";
        $subscription->billToCity = "";
        $subscription->billToState = "";
        $subscription->billToZip = "";
        $subscription->billToCountry = "";
        $subscription->shipToFirstName = "";
        $subscription->shipToLastName = "";
        $subscription->shipToCompany = "";
        $subscription->shipToAddress = "";
        $subscription->shipToCity = "";
        $subscription->shipToState = "";
        $subscription->shipToZip = "";
        $subscription->shipToCountry = "";
        
        $refId = "ref" . time();
        
        // Create the request and send it.
        $request = new AuthorizeNetARB;
        $request->setRefId($refId);
        $response = $request->createSubscription($subscription);
        
        
        // Handle the response.
        
        $this->assertTrue($response->isOk());
        $this->assertEquals($response->getMessageCode(), "I00001");
        $this->assertEquals($response->getMessageText(), "Successful.");
        $this->assertEquals($response->getRefId(), $refId);
        $this->assertEquals($response->getResultCode(), "Ok");
        
        // Cancel the subscription to avoid duplicate errors in future
        
        $cancellation = new AuthorizeNetARB;
        $cancellation->setRefId($refId);
        $cancel_response = $cancellation->cancelSubscription($response->getSubscriptionId());
        
        
        
        $this->assertTrue($cancel_response->isOk());
        
    }
    
    public function testCreateSubscriptionECheck()
    {
        
        $subscription = new AuthorizeNet_Subscription;
        $subscription->name = "my test echeck subscription";
        $subscription->intervalLength = "1";
        $subscription->intervalUnit = "months";
        $subscription->startDate = "2015-04-12";
        $subscription->totalOccurrences = "2";
        $subscription->trialOccurrences = "";
        $subscription->amount = "11.99";
        $subscription->trialAmount = "";
        $subscription->bankAccountAccountType = "checking";
        $subscription->bankAccountRoutingNumber = "121000248";
        $subscription->bankAccountAccountNumber = "12345678";
        $subscription->bankAccountNameOnAccount = "John Doe";
        $subscription->bankAccountEcheckType = "WEB";
        $subscription->bankAccountBankName = "Bank of Earth";
        $subscription->orderInvoiceNumber = "";
        $subscription->orderDescription = "";
        $subscription->customerId = "12";
        $subscription->customerEmail = "foo@domain.com";
        $subscription->customerPhoneNumber = "";
        $subscription->customerFaxNumber = "";
        $subscription->billToFirstName = "john";
        $subscription->billToLastName = "doe";
        $subscription->billToCompany = "";
        $subscription->billToAddress = "";
        $subscription->billToCity = "";
        $subscription->billToState = "";
        $subscription->billToZip = "";
        $subscription->billToCountry = "";
        $subscription->shipToFirstName = "";
        $subscription->shipToLastName = "";
        $subscription->shipToCompany = "";
        $subscription->shipToAddress = "";
        $subscription->shipToCity = "";
        $subscription->shipToState = "";
        $subscription->shipToZip = "";
        $subscription->shipToCountry = "";
        
        $refId = "ref" . time();
        
        // Create the request and send it.
        $request = new AuthorizeNetARB;
        $request->setRefId($refId);
        
        $response = $request->createSubscription($subscription);
        
        
        // Handle the response.
        
        $this->assertTrue($response->isOk());
        $this->assertEquals($response->getMessageCode(), "I00001");
        $this->assertEquals($response->getMessageText(), "Successful.");
        $this->assertEquals($response->getRefId(), $refId);
        $this->assertEquals($response->getResultCode(), "Ok");
        
        
        // Cancel the subscription to avoid duplicate errors in future
        
        
        $cancellation = new AuthorizeNetARB;
        $cancellation->setRefId($refId);
        $cancel_response = $cancellation->cancelSubscription($response->getSubscriptionId());
        
        $this->assertTrue($cancel_response->isOk());
        
    }

}
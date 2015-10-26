<?php

require_once 'AuthorizeNet_Test_Config.php';

class AuthorizeNetAIM_Sandbox_Test extends PHPUnit_Framework_TestCase
{
    
    public function testAuthCapture()
    {
        $sale = new AuthorizeNetAIM;
        $sale->setFields(
            array(
            'amount' => rand(1, 1000),
            'card_num' => '4111111111111111',
            'exp_date' => '0415'
            )
        );
        $response = $sale->authorizeAndCapture();
        $this->assertTrue($response->approved);
    }
    
    public function testAuthCaptureSingleDigitMonth()
    {
        $sale = new AuthorizeNetAIM;
        $sale->setFields(
            array(
            'amount' => rand(1, 1000),
            'card_num' => '4111111111111111',
            'exp_date' => '415'
            )
        );
        $response = $sale->authorizeAndCapture();
        $this->assertTrue($response->approved);
    }
    
    public function testAuthCaptureSingleDigitMonthWithSlash()
    {
        $sale = new AuthorizeNetAIM;
        $sale->setFields(
            array(
            'amount' => rand(1, 1000),
            'card_num' => '4111111111111111',
            'exp_date' => '4/15'
            )
        );
        $response = $sale->authorizeAndCapture();
        $this->assertTrue($response->approved);
    }
    
    public function testAuthCaptureTwoDigitMonthWithSlash()
    {
        $sale = new AuthorizeNetAIM;
        $sale->setFields(
            array(
            'amount' => rand(1, 1000),
            'card_num' => '4111111111111111',
            'exp_date' => '04/15'
            )
        );
        $response = $sale->authorizeAndCapture();
        $this->assertTrue($response->approved);
    }
    
    public function testAuthCaptureAlternate()
    {
        $sale = new AuthorizeNetAIM;
        $sale->amount = rand(1, 10000);
        $sale->card_num = '6011000000000012';
        $sale->exp_date = '04/15';
        $response = $sale->authorizeAndCapture();
        $this->assertTrue($response->approved);
    }
    
    public function testAuthCaptureShort()
    {
        $sale = new AuthorizeNetAIM;
        $response = $sale->authorizeAndCapture(rand(1, 100), '6011000000000012', '04/16');
        $this->assertTrue($response->approved);
    }
    
    public function testAuthCapturePartial()
    {
        $amount = 3.69;
        
        $sale = new AuthorizeNetAIM;
        $sale->amount = $amount;
        $sale->card_num = '4222222222222';
        $sale->zip = "46225";
        $sale->exp_date = '04/15';
        $sale->allow_partial_auth = true;
        $response = $sale->authorizeAndCapture();
        $this->assertTrue($response->held);
        $this->assertEquals("1.23", $response->amount);
        $this->assertEquals($amount, $response->requested_amount);
        $split_tender_id = $response->split_tender_id;
        
        // Pay the balance with a different card
        $sale = new AuthorizeNetAIM;
        $sale->amount = $amount - $response->amount;
        $sale->card_num = '6011000000000012';
        $sale->exp_date = '04/20';
        $sale->split_tender_id = $split_tender_id;
        $sale->allow_partial_auth = true;
        $response = $sale->authorizeAndCapture();
        $this->assertTrue($response->approved);
        
        
    }
    
    public function testAuthCaptureShortNoVerify()
    {
        $sale = new AuthorizeNetAIM;
        $sale->VERIFY_PEER = false;
        $response = $sale->authorizeAndCapture(rand(1, 100), '6011000000000012', '04/19');
        $this->assertTrue($response->approved);
    }
    
    // public function testVisaVerify()
    // {
    //     return;  // Remove to enable test
    //     $verify = new AuthorizeNetAIM;
    //     $verify->amount = "0.00";
    //     $verify->card_num = '4012888818888';
    //     $verify->exp_date = "0517";
    //     $verify->address = "123 Main Street";
    //     $verify->zip = "94110";
    //     $verify->authentication_indicator = "5";
    //     $verify->cardholder_authentication_value = "512";
    //     $response = $verify->authorizeOnly();
    //     $this->assertTrue($response->approved);
    // }
    // 
    // public function testVisaVerifyFail()
    // {
    //     return;  // Remove to enable test
    //     $verify = new AuthorizeNetAIM;
    //     $verify->amount = "0.00";
    //     $verify->card_num = '4012888818888';
    //     $verify->exp_date = "0517";
    //     $verify->address = "123 Main Street";
    //     $verify->zip = "94110";
    //     $verify->authentication_indicator = "5";
    //     $verify->cardholder_authentication_value = "";
    //     $response = $verify->authorizeOnly();
    //     $this->assertTrue($response->declined);
    // }
    // 
    // public function testMastercardVerify()
    // {
    //     return;  // Remove to enable test
    //     $verify = new AuthorizeNetAIM;
    //     $verify->amount = "0.00";
    //     $verify->card_num = '5424000000000015';
    //     $verify->exp_date = "0517";
    //     $verify->address = "123 Main Street";
    //     $verify->zip = "94110";
    //     $verify->authentication_indicator = "2";
    //     $verify->cardholder_authentication_value = "512";
    //     $response = $verify->authorizeOnly();
    //     $this->assertTrue($response->approved);
    // }
    // 
    // public function testMastercardVerifyFail()
    // {
    //     return;  // Remove to enable test
    //     $verify = new AuthorizeNetAIM;
    //     $verify->amount = "0.00";
    //     $verify->card_num = '5424000000000015';
    //     $verify->exp_date = "0517";
    //     $verify->address = "123 Main Street";
    //     $verify->zip = "94110";
    //     $verify->authentication_indicator = "2";
    //     $verify->cardholder_authentication_value = "";
    //     $response = $verify->authorizeOnly();
    //     $this->assertTrue($response->declined);
    // }
    
    public function testAimResponseFields()
    {
        
        $sale = new AuthorizeNetAIM;
        $sale->card_num           = '4111111111111111';
        $sale->exp_date           = '04/16';
        $sale->amount             = $amount = rand(1,99);
        $sale->description        = $description = "Sale description";
        $sale->first_name         = $first_name = "Jane";
        $sale->last_name          = $last_name = "Smith";
        $sale->company            = $company = "Jane Smith Enterprises Inc.";
        $sale->address            = $address = "20 Main Street";
        $sale->city               = $city = "San Francisco";
        $sale->state              = $state = "CA";
        $sale->zip                = $zip = "94110";
        $sale->country            = $country = "US";
        $sale->phone              = $phone = "415-555-5557";
        $sale->fax                = $fax = "415-555-5556";
        $sale->email              = $email = "foo@example.com";
        $sale->cust_id            = $customer_id = "55";
        $sale->customer_ip        = "98.5.5.5";
        $sale->invoice_num        = $invoice_number = "123";
        $sale->ship_to_first_name = $ship_to_first_name = "John";
        $sale->ship_to_last_name  = $ship_to_last_name = "Smith";
        $sale->ship_to_company    = $ship_to_company = "Smith Enterprises Inc.";
        $sale->ship_to_address    = $ship_to_address = "10 Main Street";
        $sale->ship_to_city       = $ship_to_city = "San Francisco";
        $sale->ship_to_state      = $ship_to_state = "CA";
        $sale->ship_to_zip        = $ship_to_zip_code = "94110";
        $sale->ship_to_country    = $ship_to_country = "US";
        $sale->tax                = $tax = "0.00";
        $sale->freight            = $freight = "Freight<|>ground overnight<|>12.95";
        $sale->duty               = $duty = "Duty1<|>export<|>15.00";
        $sale->tax_exempt         = $tax_exempt = "FALSE";
        $sale->po_num             = $po_num = "12";

        
        
        $response = $sale->authorizeAndCapture();
        $this->assertTrue($response->approved);
        $this->assertEquals("1", $response->response_code);
        $this->assertEquals("1", $response->response_subcode);
        $this->assertEquals("1", $response->response_reason_code);
        $this->assertEquals("This transaction has been approved.", $response->response_reason_text);
        $this->assertGreaterThan(1, strlen($response->authorization_code));
        $this->assertEquals("Y", $response->avs_response);
        $this->assertGreaterThan(1, strlen($response->transaction_id));
        $this->assertEquals($invoice_number, $response->invoice_number);
        $this->assertEquals($description, $response->description);
        $this->assertEquals($amount, $response->amount);
        $this->assertEquals("CC", $response->method);
        $this->assertEquals("auth_capture", $response->transaction_type);
        $this->assertEquals($customer_id, $response->customer_id);
        $this->assertEquals($first_name, $response->first_name);
        $this->assertEquals($last_name, $response->last_name);
        $this->assertEquals($company, $response->company);
        $this->assertEquals($address, $response->address);
        $this->assertEquals($city, $response->city);
        $this->assertEquals($state, $response->state);
        $this->assertEquals($zip, $response->zip_code);
        $this->assertEquals($country, $response->country);
        $this->assertEquals($phone, $response->phone);
        $this->assertEquals($fax, $response->fax);
        $this->assertEquals($email, $response->email_address);
        $this->assertEquals($ship_to_first_name, $response->ship_to_first_name);
        $this->assertEquals($ship_to_last_name, $response->ship_to_last_name);
        $this->assertEquals($ship_to_company, $response->ship_to_company);
        $this->assertEquals($ship_to_address, $response->ship_to_address);
        $this->assertEquals($ship_to_city, $response->ship_to_city);
        $this->assertEquals($ship_to_state, $response->ship_to_state);
        $this->assertEquals($ship_to_zip_code, $response->ship_to_zip_code);
        $this->assertEquals($ship_to_country, $response->ship_to_country);
        $this->assertEquals($tax, $response->tax);
        $this->assertEquals("15.00", $response->duty);
        $this->assertEquals("12.95", $response->freight);
        $this->assertEquals($tax_exempt, $response->tax_exempt);
        $this->assertEquals($po_num, $response->purchase_order_number);
        $this->assertGreaterThan(1, strlen($response->md5_hash));
        $this->assertEquals("", $response->card_code_response);
        $this->assertEquals("2", $response->cavv_response);
        $this->assertEquals("XXXX1111", $response->account_number);
        $this->assertEquals("Visa", $response->card_type);
        $this->assertEquals("", $response->split_tender_id);
        $this->assertEquals("", $response->requested_amount);
        $this->assertEquals("", $response->balance_on_card);
        
        
    }
    
 
    public function testVoid()
    {
        // First create transaction to void.
        $amount = rand(1, 1000);
        $sale = new AuthorizeNetAIM;
        $sale->setFields(
            array(
            'amount' => $amount,
            'card_num' => '6011000000000012',
            'exp_date' => '0415'
            )
        );
        $response = $sale->authorizeAndCapture();
        $this->assertTrue($response->approved);
        
        $void = new AuthorizeNetAIM;
        $void->setFields(
            array(
            'amount' => $amount,
            'card_num' => '6011000000000012',
            'trans_id' => $response->transaction_id,
            )
        );
        $void_response = $void->Void();
        $this->assertTrue($void_response->approved);
    }
    
    public function testVoidShort()
    {
        // First create transaction to void.
        $amount = rand(1, 1000);
        $card_num = '6011000000000012';
        $exp_date = '0415';
        $sale = new AuthorizeNetAIM;
        $response = $sale->authorizeAndCapture($amount, $card_num, $exp_date);
        $this->assertTrue($response->approved);
        
        $void = new AuthorizeNetAIM;
        $void_response = $void->void($response->transaction_id);
        $this->assertTrue($void_response->approved);
    }

    public function testAuthCaptureECheckSandbox()
    {
        $sale = new AuthorizeNetAIM;
        $sale->setFields(
            array(
            'amount' => rand(1, 1000),
            'method' => 'echeck',
            'bank_aba_code' => '121042882',
            'bank_acct_num' => '123456789123',
            'bank_acct_type' => 'CHECKING',
            'bank_name' => 'Bank of Earth',
            'bank_acct_name' => 'Jane Doe',
            'echeck_type' => 'WEB',
            )
        );
        $response = $sale->authorizeAndCapture();
        $this->assertEquals("ECHECK", $response->method);
        $this->assertTrue($response->approved);
        
    }
   
    public function testAmex()
    {
        $sale = new AuthorizeNetAIM;
        $response = $sale->authorizeAndCapture(rand(1, 100), '370000000000002', '04/16');
        $this->assertTrue($response->approved);
    }
    
    public function testDiscover()
    {
        $sale = new AuthorizeNetAIM;
        $response = $sale->authorizeAndCapture(rand(1, 100), '6011000000000012', '04/16');
        $this->assertTrue($response->approved);
    }
    
    public function testVisa()
    {
        $sale = new AuthorizeNetAIM;
        $response = $sale->authorizeAndCapture(rand(1, 100), '4012888818888', '04/16');
        $this->assertTrue($response->approved);
    }
    
    // public function testJCB()
    // {
    //     return; // Remove to enable test
    //     $sale = new AuthorizeNetAIM;
    //     $response = $sale->authorizeAndCapture(rand(1, 100), '3088000000000017', '0905');
    //     $this->assertTrue($response->approved);
    // }
    // 
    // public function testDinersClub()
    // {
    //     return; // Remove to enable test
    //     $sale = new AuthorizeNetAIM;
    //     $response = $sale->authorizeAndCapture(rand(1, 100), '38000000000006', '0905');
    //     $this->assertTrue($response->approved);
    // }
    
    public function testAuthOnly()
    {
        $auth = new AuthorizeNetAIM;
        $auth->setFields(
            array(
            'amount' => rand(1, 1000),
            'card_num' => '6011000000000012',
            'exp_date' => '0415'
            )
        );
        $response = $auth->authorizeOnly();
        $this->assertTrue($response->approved);
    }
    
    public function testAuthCaptureVoid()
    {
        $amount = rand(1, 1000);
        $auth = new AuthorizeNetAIM;
        $auth->setFields(
            array(
            'amount' => $amount,
            'card_num' => '6011000000000012',
            'exp_date' => '0415'
            )
        );
        $auth_response = $auth->authorizeOnly();
        $this->assertTrue($auth_response->approved);
        
        // Now capture.
        $capture = new AuthorizeNetAIM;
        $capture->setFields(
            array(
                'amount' => $amount,
                'card_num' => '6011000000000012',
                'exp_date' => '0415',
                'trans_id' => $auth_response->transaction_id,
                )
        );
        $capture_response = $capture->priorAuthCapture();
        $this->assertTrue($capture_response->approved);
        
        // Now void
        $void = new AuthorizeNetAIM;
        $void->setFields(
            array(
            'amount' => $amount,
            'card_num' => '0012',
            'trans_id' => $auth_response->transaction_id,
            )
        );
        $void_response = $void->void();
        $this->assertTrue($void_response->approved);
    }
    
    // public function testCredit()
    // {
    //     
    // }
    // 
    // public function testPriorAuthCapture()
    // {
    //     
    // }
    // 
    // public function testCaptureOnly()
    // {
    //     
    // }
    
    public function testAdvancedAIM()
    {
        $auth = new AuthorizeNetAIM;
        $auth->amount = "45.00";
        
        // Use eCheck:
        $auth->setECheck(
            '121042882',
            '123456789123',
            'CHECKING',
            'Bank of Earth',
            'Jane Doe',
            'WEB'
        );
        
        // Set multiple line items:
        $auth->addLineItem('item1', 'Golf tees', 'Blue tees', '2', '5.00', 'N');
        $auth->addLineItem('item2', 'Golf shirt', 'XL', '1', '40.00', 'N');
        
        // Set Invoice Number:
        $auth->invoice_num = time();
        
        // Set a Merchant Defined Field:
        $auth->setCustomField("entrance_source", "Search Engine");
        
        // Authorize Only:
        $response  = $auth->authorizeOnly();
        $this->assertTrue($response->approved);
        if ($response->approved) {
            $auth_code = $response->transaction_id;
            
            // Now capture:
            $capture = new AuthorizeNetAIM;
            $capture_response = $capture->priorAuthCapture($auth_code);
            $this->assertTrue($capture_response->approved);
            
            // Now void:
            $void = new AuthorizeNetAIM;
            $void_response = $void->void($capture_response->transaction_id);
            $this->assertTrue($void_response->approved);
        }
    }
    
    public function testAuthCaptureCustomFields()
    {
        $sale = new AuthorizeNetAIM;
        $sale->setFields(
            array(
            'amount' => rand(1, 1000),
            'card_num' => '6011000000000012',
            'exp_date' => '0415'
            )
        );
        $sale->setCustomField("foo", "bar");
        $sale->setCustomField("foo2", "bar2");
        $sale->setCustomField("foo3", "bar3");
        $sale->setCustomField("foo4", "bar4");
        $sale->setCustomField("foo5", "bar5");
        $sale->setCustomField("My_MerchantField6", "My Merchant Value6");
        $sale->setCustomField("foo7", "bar7");
        $response = $sale->authorizeAndCapture();
        $this->assertTrue($response->approved);
        $this->assertEquals("bar", $response->foo);
        $this->assertEquals("bar2", $response->foo2);
    }

    public function testEncapCharacter()
    {
        $description = "john doe's present, with comma";
        $amount = rand(1, 1000);
        $sale = new AuthorizeNetAIM;
        $sale->setFields(
            array(
            'amount' => $amount,
            'card_num' => '6011000000000012',
            'exp_date' => '0415',
            'encap_char' => '$',
            'description' => $description,
            )
        );
        $response = $sale->authorizeAndCapture();
        $this->assertTrue($response->approved);
        $this->assertEquals($amount, $response->amount);
        $this->assertEquals($description, $response->description);
    }

    public function testAuthCaptureSetMultipleCustomFields()
    {
        $sale = new AuthorizeNetAIM;
        $sale->setFields(
            array(
            'amount' => rand(1, 1000),
            'card_num' => '6011000000000012',
            'exp_date' => '0415'
            )
        );
        $sale->setCustomFields(array("foo" => "bar", "foo2" => "bar2"));
        $response = $sale->authorizeAndCapture();
        $this->assertTrue($response->approved);
        $this->assertEquals("bar", $response->foo);
        $this->assertEquals("bar2", $response->foo2);
    }
    
    public function testInvalidMerchantCredentials()
    {
        $auth = new AuthorizeNetAIM('d', 'd');
        $response = $auth->AuthorizeOnly();
        $this->assertTrue($response->error);
        $this->assertEquals($response->response_subcode, 2);
        $this->assertEquals($response->response_reason_code, 13);
    }
    
    public function testInvalidCreditCard()
    {
        $sale = new AuthorizeNetAIM;
        $sale->setFields(
            array(
            'amount' => rand(1, 1000),
            'card_num' => '123',
            'exp_date' => '0415'
            )
        );
        $response = $sale->authorizeAndCapture();
        $this->assertFalse($response->approved);
        $this->assertTrue($response->error);
    }

    public function testError()
    {
        $sale = new AuthorizeNetAIM;
        $sale->unsetField("login");
        $sale->unsetField("tran_key");
        $sale->unsetField("delim_data");
        
        $sale->unsetField("version");
        $sale->unsetField("relay_response");
        
        $response = $sale->authorizeAndCapture();
        // An exception should have been thrown.
        $this->assertFalse($response->approved);
        $this->assertTrue($response->error);
        
    }
    
    public function testMultipleLineItems()
    {
        $merchant = (object)array();
        $merchant->login = AUTHORIZENET_API_LOGIN_ID;
        $merchant->tran_key = AUTHORIZENET_TRANSACTION_KEY;
        $merchant->allow_partial_auth = "false";

        $creditCard = array(
            'exp_date' => '02/2012',
            'card_num' => '6011000000000012',
            'card_code' => '452',
            );

        $transaction = array(
        'amount' => rand(100, 1000),
        'duplicate_window' => '10',
        // 'email_customer' => 'true',
        'footer_email_receipt' => 'thank you for your business!',
        'header_email_receipt' => 'a copy of your receipt is below',
        );
            
        $order = array(
            'description' => 'Johns Bday Gift',
            'invoice_num' => '3123',
            'line_item' => 'item1<|>golf balls<|><|>2<|>18.95<|>Y',
            );

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

        $shipping_info = (object)array();
        $shipping_info->ship_to_first_name = "John";
        $shipping_info->ship_to_last_name = "Smith";
        $shipping_info->ship_to_company = "Smith Enterprises Inc.";
        $shipping_info->ship_to_address = "10 Main Street";
        $shipping_info->ship_to_city = "San Francisco";
        $shipping_info->ship_to_state = "CA";
        $shipping_info->ship_to_zip = "94110";
        $shipping_info->ship_to_country = "US";
        $shipping_info->tax = "CA";
        $shipping_info->freight = "Freight<|>ground overnight<|>12.95";
        $shipping_info->duty = "Duty1<|>export<|>15.00";
        $shipping_info->tax_exempt = "false";
        $shipping_info->po_num = "12";

        $sale = new AuthorizeNetAIM;
        $sale->setFields($creditCard);
        $sale->setFields($shipping_info);
        $sale->setFields($customer);
        $sale->setFields($order);
        $sale->setFields($merchant);
        $sale->setFields($transaction);
        
        $sale->addLineItem('item2', 'golf tees', 'titanium tees', '2', '2.95', 'Y');
        $sale->addLineItem('item3', 'golf shirt', 'red, large', '2', '3.95', 'Y');
        
        $response = $sale->authorizeAndCapture();

        $this->assertTrue($response->approved);
    }
    
    public function testAllFieldsLongMethod()
    {
        $merchant = (object)array();
        $merchant->login = AUTHORIZENET_API_LOGIN_ID;
        $merchant->tran_key = AUTHORIZENET_TRANSACTION_KEY;
        $merchant->allow_partial_auth = "false";

        $creditCard = array(
            'exp_date' => '02/2012',
            'card_num' => '6011000000000012',
            'card_code' => '452',
            );

        $transaction = array(
        'amount' => rand(100, 1000),
        'duplicate_window' => '10',
        // 'email_customer' => 'true',
        'footer_email_receipt' => 'thank you for your business!',
        'header_email_receipt' => 'a copy of your receipt is below',
        );
            
        $order = array(
            'description' => 'Johns Bday Gift',
            'invoice_num' => '3123',
            'line_item' => 'item1<|>golf balls<|><|>2<|>18.95<|>Y',
            );

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

        $shipping_info = (object)array();
        $shipping_info->ship_to_first_name = "John";
        $shipping_info->ship_to_last_name = "Smith";
        $shipping_info->ship_to_company = "Smith Enterprises Inc.";
        $shipping_info->ship_to_address = "10 Main Street";
        $shipping_info->ship_to_city = "San Francisco";
        $shipping_info->ship_to_state = "CA";
        $shipping_info->ship_to_zip = "94110";
        $shipping_info->ship_to_country = "US";
        $shipping_info->tax = "CA";
        $shipping_info->freight = "Freight<|>ground overnight<|>12.95";
        $shipping_info->duty = "Duty1<|>export<|>15.00";
        $shipping_info->tax_exempt = "false";
        $shipping_info->po_num = "12";

        $sale = new AuthorizeNetAIM;
        $sale->setFields($creditCard);
        $sale->setFields($shipping_info);
        $sale->setFields($customer);
        $sale->setFields($order);
        $sale->setFields($merchant);
        $sale->setFields($transaction);
        $response = $sale->authorizeAndCapture();
        
        $this->assertTrue($response->approved);
    }

    public function testResponseMethods()
    {
        $amount = rand(1, 1000);
        $zipcode = "02301";
        
        $sale = new AuthorizeNetAIM;
        $sale->setFields(
            array(
            'amount' => $amount,
            'card_num' => '6011000000000012',
            'exp_date' => '0415',
            'zip' => $zipcode,
            )
        );
        
        $sale->setCustomField("custom1", "custom1value");
        $sale->setCustomField("custom2", "custom2value");
        $result = $sale->authorizeAndCapture();
        $this->assertTrue($result->approved);
        
        $this->assertEquals("custom2value", $result->custom2);
        $this->assertEquals($amount, $result->amount);
        $this->assertEquals("CC", $result->method);
        $this->assertEquals("auth_capture", $result->transaction_type);
        $this->assertEquals("Discover", $result->card_type);
        $this->assertEquals($zipcode, $result->zip_code);
    }
    
    public function testSetBadField()
    {
        try {
            $amount = rand(1, 1000);
            $zipcode = "02301";
            
            $sale = new AuthorizeNetAIM;
            $sale->setFields(
                array(
                'amount' => $amount,
                'card_num' => '6011000000000012',
                'exp_date' => '0415',
                'zipcode' => $zipcode, // Should actually be just "zip"
                )
            );

            $result = $sale->authorizeAndCapture();
            $this->assertTrue($result->approved);
            // should have thrown an exception by now
            $this->assertFalse(true);
        }
        catch (AuthorizeNetException $e){
            $this->assertTrue(true);
        
        }
    }

}


class AuthorizeNetAIM_Live_Test extends PHPUnit_Framework_TestCase
{

    public function testAuthCaptureSetECheckMethod()
    {
        if (MERCHANT_LIVE_API_LOGIN_ID) {
            // $this->markTestIncomplete('Depends on whether eChecks is enabled');
            $sale = new AuthorizeNetAIM(MERCHANT_LIVE_API_LOGIN_ID,MERCHANT_LIVE_TRANSACTION_KEY);
            $sale->setSandbox(false);
            $sale->test_request = 'TRUE';
            $sale->amount = "4.99";
            $sale->setECheck(
                '121042882',
                '123456789123',
                'CHECKING',
                'Bank of Earth',
                'Jane Doe',
                'WEB'
            );
            $response = $sale->authorizeAndCapture();
            $this->assertEquals("ECHECK", $response->method);
            $this->assertEquals("18", $response->response_reason_code);
            // $this->assertTrue($response->approved);
        }
    }
    
    public function testAuthCaptureECheck()
    {
        if (MERCHANT_LIVE_API_LOGIN_ID) {
            // $this->markTestIncomplete('Depends on whether eChecks is enabled');
            $sale = new AuthorizeNetAIM(MERCHANT_LIVE_API_LOGIN_ID,MERCHANT_LIVE_TRANSACTION_KEY);
            $sale->setSandbox(false);
            $sale->test_request = 'TRUE';
            $sale->setFields(
                array(
                'amount' => rand(1, 1000),
                'method' => 'echeck',
                'bank_aba_code' => '121042882',
                'bank_acct_num' => '123456789123',
                'bank_acct_type' => 'CHECKING',
                'bank_name' => 'Bank of Earth',
                'bank_acct_name' => 'Jane Doe',
                'echeck_type' => 'WEB',
                )
            );
            $response = $sale->authorizeAndCapture();
            $this->assertEquals("ECHECK", $response->method);
            $this->assertEquals("18", $response->response_reason_code);
            // $this->assertTrue($response->approved);
        }
    }

    public function testAuthCaptureLiveServerTestRequest()
    {
        if (MERCHANT_LIVE_API_LOGIN_ID) {
            $sale = new AuthorizeNetAIM(MERCHANT_LIVE_API_LOGIN_ID,MERCHANT_LIVE_TRANSACTION_KEY);
            $sale->setSandbox(false);
            $sale->setFields(
                array(
                'amount' => rand(1, 1000),
                'card_num' => '6011000000000012',
                'exp_date' => '0415'
                )
            );
            $sale->setField('test_request', 'TRUE');
            $response = $sale->authorizeAndCapture();
            $this->assertTrue($response->approved);
        }
    }

    public function testAuthCaptureLiveServer()
    {
        if (MERCHANT_LIVE_API_LOGIN_ID) {
            $sale = new AuthorizeNetAIM(MERCHANT_LIVE_API_LOGIN_ID,MERCHANT_LIVE_TRANSACTION_KEY);
            $sale->setSandbox(false);
            $sale->test_request = 'TRUE';
            $sale->setFields(
                array(
                'amount' => rand(1, 1000),
                'card_num' => '6011000000000012',
                'exp_date' => '0415'
                )
            );
            $response = $sale->authorizeAndCapture();
            $this->assertTrue($response->approved);
        }
    }

    public function testInvalidCredentials()
    {
        if (MERCHANT_LIVE_API_LOGIN_ID) {
            // Post a response to live server using invalid credentials.
            $sale = new AuthorizeNetAIM('a','a');
            $sale->setSandbox(false);
            $sale->setFields(
                array(
                'amount' => rand(1, 1000),
                'card_num' => '6011000000000012',
                'exp_date' => '0415'
                )
            );
            $response = $sale->authorizeAndCapture();
            $this->assertTrue($response->error);
            $this->assertEquals("13", $response->response_reason_code);
        }
    }

}
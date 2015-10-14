<?php

require_once 'AuthorizeNet_Test_Config.php';

class AuthorizeNetCP_Test extends PHPUnit_Framework_TestCase
{
    
    public function testAuthCapture()
    {
        $sale = new AuthorizeNetCP(CP_API_LOGIN_ID, CP_TRANSACTION_KEY);
        $sale->setFields(
            array(
            'amount' => rand(1, 1000),
            'card_num' => '4111111111111111',
            'exp_date' => '0415',
            'device_type' => '4',
            )
        );
        $response = $sale->authorizeAndCapture();
        $this->assertTrue($response->approved);
    }
    
    // public function testMd5()
    // {
    //     return;
    //     $sale = new AuthorizeNetCP(CP_API_LOGIN_ID, CP_TRANSACTION_KEY);
    //     $sale->setFields(
    //         array(
    //         'amount' => rand(1, 1000),
    //         'card_num' => '4111111111111111',
    //         'exp_date' => '0415',
    //         'device_type' => '4',
    //         )
    //     );
    //     $response = $sale->authorizeAndCapture();
    //     $this->assertTrue($response->approved);
    //     $this->assertTrue($response->isAuthorizeNet(CP_API_LOGIN_ID));
    // }
    
    public function testAuthCaptureTrack1()
    {
        $sale = new AuthorizeNetCP(CP_API_LOGIN_ID, CP_TRANSACTION_KEY);
        $sale->setFields(
            array(
            'amount' => rand(1, 1000),
            'device_type' => '4',
            )
        );
        $sale->setTrack1Data('%B4111111111111111^CARDUSER/JOHN^1803101000000000020000831000000?');
        $response = $sale->authorizeAndCapture();
        $this->assertTrue($response->approved);
    }
    
    public function testAuthCaptureTrack2()
    {
        $sale = new AuthorizeNetCP(CP_API_LOGIN_ID, CP_TRANSACTION_KEY);
        $sale->setFields(
            array(
            'amount' => rand(1, 1000),
            'device_type' => '4',
            )
        );
        $sale->setTrack2Data('4111111111111111=1803101000020000831?');
        $response = $sale->authorizeAndCapture();
        $this->assertTrue($response->approved);
    }
    
    public function testAuthCaptureTrack2Error()
    {
        $sale = new AuthorizeNetCP(CP_API_LOGIN_ID, CP_TRANSACTION_KEY);
        $sale->setFields(
            array(
            'amount' => rand(1, 1000),
            'device_type' => '4',
            )
        );
        $sale->setTrack2Data('4411111111111111=1803101000020000831?');
        $response = $sale->authorizeAndCapture();
        $this->assertFalse($response->approved);
        $this->assertTrue($response->error);
        $this->assertEquals(6, $response->response_reason_code);
    }
    
    public function testResponseFields()
    {
        $sale = new AuthorizeNetCP(CP_API_LOGIN_ID, CP_TRANSACTION_KEY);
        $sale->setFields(
            array(
            'amount' => rand(1, 1000),
            'device_type' => '4',
            )
        );
        $sale->user_ref = $user_ref = "someCustomVariable123";
        $sale->setTrack1Data('%B4111111111111111^CARDUSER/JOHN^1803101000000000020000831000000?');
        $response = $sale->authorizeAndCapture();
        
        
        $this->assertTrue($response->approved);
        $this->assertEquals('1.0',$response->version);
        $this->assertEquals('1',$response->response_code);
        $this->assertEquals('1',$response->response_reason_code);
        $this->assertEquals('(TESTMODE) This transaction has been approved.',$response->response_reason_text);
        $this->assertEquals('000000',$response->authorization_code);
        $this->assertEquals('P',$response->avs_code);
        $this->assertEquals('',$response->card_code_response);
        $this->assertEquals('0',$response->transaction_id);
        $this->assertStringMatchesFormat('%x',$response->md5_hash);
        $this->assertEquals($user_ref, $response->user_ref);
        $this->assertEquals('XXXX1111',$response->card_num);
        $this->assertEquals('Visa',$response->card_type);


    }
    
    public function testXmlResponse()
    {
        $sale = new AuthorizeNetCP(CP_API_LOGIN_ID, CP_TRANSACTION_KEY);
        $sale->setFields(
            array(
            'amount' => rand(1, 1000),
            'device_type' => '4',
            'response_format' => '0',
            )
        );
        $sale->user_ref = $user_ref = "dummyvalue323";
        $sale->setTrack1Data('%B4111111111111111^CARDUSER/JOHN^1803101000000000020000831000000?');
        $response = $sale->authorizeAndCapture();
        
        
        $this->assertTrue($response->approved);
        $this->assertEquals('1.0',$response->version);
        $this->assertEquals('1',$response->response_code);
        $this->assertEquals('1',$response->response_reason_code);
        $this->assertEquals('This transaction has been approved.',$response->response_reason_text);
        $this->assertEquals('000000',$response->authorization_code);
        $this->assertEquals('P',$response->avs_code);
        $this->assertEquals('',$response->card_code_response);
        $this->assertEquals('0',$response->transaction_id);
        $this->assertStringMatchesFormat('%x',$response->md5_hash);
        $this->assertEquals($user_ref, $response->user_ref);
        $this->assertEquals('XXXX1111',$response->card_num);
        $this->assertEquals('Visa',$response->card_type);


    }
    
    public function testXmlResponseFailure()
    {
        $sale = new AuthorizeNetCP(CP_API_LOGIN_ID, CP_TRANSACTION_KEY);
        $sale->setFields(
            array(
            'amount' => rand(1, 1000),
            'device_type' => '4',
            'response_format' => '0',
            )
        );
        $sale->user_ref = $user_ref = "dummyvalue323";
        $sale->setTrack1Data('%B4111111111111^CARDUSER/JOHN^1803101000000000020000831000000?');
        $response = $sale->authorizeAndCapture();
        
        
        $this->assertTrue($response->error);
        $this->assertEquals('1.0',$response->version);
        $this->assertEquals('3',$response->response_code);
        $this->assertEquals('6',$response->response_reason_code);
        $this->assertEquals('The credit card number is invalid.',$response->response_reason_text);
        $this->assertEquals('000000',$response->authorization_code);
        $this->assertEquals('P',$response->avs_code);
        $this->assertEquals('',$response->card_code_response);
        $this->assertEquals('0',$response->transaction_id);
        $this->assertStringMatchesFormat('%x',$response->md5_hash);
        $this->assertEquals($user_ref, $response->user_ref);
        $this->assertEquals('XXXX1111',$response->card_num);


    }


}
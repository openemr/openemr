<?php

require_once 'AuthorizeNet_Test_Config.php';

class AuthorizeNetSIM_Test extends PHPUnit_Framework_TestCase
{
    
    public function testGenerateHash()
    {
        $_POST['x_amount'] = "4.12";
        $_POST['x_trans_id'] = "123";
        $message = new AuthorizeNetSIM("528udYYwz","test");
        $this->assertEquals("8FC33C32ABB3EDD8BBC4BE3E904CB47E",$message->generateHash());
    }
    
    public function testAmount()
    {
        $_POST['x_amount'] = "4.12";
        $_POST['x_response_code'] = "1";
        $message = new AuthorizeNetSIM("528udYYwz","test");
        $this->assertEquals("4.12",$message->amount);
        $this->assertTrue($message->approved);
    }
    
    public function testIsAuthNet()
    {
        $_POST['x_amount'] = "4.12";
        $_POST['x_trans_id'] = "123";
        $_POST['x_MD5_Hash'] = "8FC33C32ABB3EDD8BBC4BE3E904CB47E";
        $message = new AuthorizeNetSIM("528udYYwz","test");
        $this->assertTrue($message->isAuthorizeNet());
        
        
        $_POST['x_amount'] = "4.12";
        $_POST['x_trans_id'] = "123";
        $_POST['x_MD5_Hash'] = "8FC33C32BB3EDD8BBC4BE3E904CB47E";
        $message = new AuthorizeNetSIM("528udYYwz","test");
        $this->assertFalse($message->isAuthorizeNet());
    }
    
     public function testIsError()
    {
        $_POST['x_amount'] = "4.12";
        $_POST['x_response_code'] = "3";
        $_POST['x_ship_to_state'] = "CA";
        $message = new AuthorizeNetSIM("528udYYwz","test");
        $this->assertEquals("3",$message->response_code);
        $this->assertTrue($message->error);
        $this->assertFalse($message->approved);
        $this->assertEquals("CA",$message->ship_to_state);
    }
    
    
    

}
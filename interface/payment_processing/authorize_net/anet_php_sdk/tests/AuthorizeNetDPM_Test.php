<?php

require_once 'AuthorizeNet_Test_Config.php';

class AuthorizeNetDPM_Test extends PHPUnit_Framework_TestCase
{ 
    public function testGenerateFingerprint()
    {
        $this->assertEquals("db88bbebb8f699acdbe70daad897a68a",AuthorizeNetDPM::getFingerprint("123","123","123","123","123"));
    }
    
    public function testGetCreditCardForm()
    {
        $fp_sequence = "12345";
        $this->assertContains('<input type="hidden" name="x_fp_sequence" value="'.$fp_sequence.'">',AuthorizeNetDPM::getCreditCardForm('2', $fp_sequence, 'ht', '2', '1', true));
    }
    
    public function testRelayResponseUrl()
    {
        $return_url = 'http://yourdomain.com';
        
        $this->assertContains('window.location="'.$return_url.'";', AuthorizeNetDPM::getRelayResponseSnippet($return_url));
    }

}
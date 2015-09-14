<?php

require_once 'AuthorizeNet_Test_Config.php';

class AuthorizeNet_SOAP_Test extends PHPUnit_Framework_TestCase
{
    
    public function testSaveSoapDoc ()
    {
        $filepath = dirname(__FILE__) . "/soap_doc.php";
        $client = new AuthorizeNetSOAP;
        $this->assertTrue($client->saveSoapDocumentation($filepath) > 1);
        unlink($filepath);
    }
    
    public function testGetCustomerIds ()
    {
        $client = new AuthorizeNetSOAP;
        $result = $client->GetCustomerProfileIds(
            array(
                'merchantAuthentication' => array(
                    'name' => AUTHORIZENET_API_LOGIN_ID,
                    'transactionKey' => AUTHORIZENET_TRANSACTION_KEY,
                ),
            )
        );
        $customer_ids = $result->GetCustomerProfileIdsResult->ids->long;
        $this->assertTrue(is_array($customer_ids));
    }

}
<?php

require_once 'AuthorizeNet_Test_Config.php';


class AuthorizeNetTD_Test extends PHPUnit_Framework_TestCase
{


    public function testGetSettledBatchList()
    {
        $request = new AuthorizeNetTD;
        $response = $request->getSettledBatchList();
        $this->assertTrue($response->isOk());
        $this->assertEquals("I00001",(string)array_pop($response->xpath("messages/message/code")));
    }
    
    public function testGetSettledBatchListIncludeStatistics()
    {
        $request = new AuthorizeNetTD;
        $response = $request->getSettledBatchList(true);
        $this->assertTrue($response->isOk());
    }
    
    public function testGetSettledBatchListForMonth()
    {
        $request = new AuthorizeNetTD;
        $response = $request->getSettledBatchListForMonth();
        $this->assertTrue($response->isOk());
    }
    
    public function testGetTransactionsForDay()
    {
        $request = new AuthorizeNetTD;
        $transactions = $request->getTransactionsForDay(12, 8, 2010);
        $this->assertTrue(is_array($transactions));
    }
    
    public function testGetTransactionList()
    {
        $request = new AuthorizeNetTD;
        $response = $request->getSettledBatchList();
        $this->assertTrue($response->isOk());
        $batches = $response->xpath("batchList/batch");
        $batch_id = (string)$batches[0]->batchId;
        $response = $request->getTransactionList($batch_id);
        $this->assertTrue($response->isOk());
    }
    
    public function testGetTransactionDetails()
    {
        $sale = new AuthorizeNetAIM;
        $amount = rand(1, 100);
        $response = $sale->authorizeAndCapture($amount, '4012888818888', '04/17');
        $this->assertTrue($response->approved);
        
        $transId = $response->transaction_id;
        
        $request = new AuthorizeNetTD;
        $response = $request->getTransactionDetails($transId);
        $this->assertTrue($response->isOk());
        
        $this->assertEquals($transId, (string)$response->xml->transaction->transId);
        $this->assertEquals($amount, (string)$response->xml->transaction->authAmount);
        $this->assertEquals("Visa", (string)$response->xml->transaction->payment->creditCard->cardType);
        
    }
    
    public function testGetUnsettledTransactionList()
    {
        $sale = new AuthorizeNetAIM;
        $amount = rand(1, 100);
        $response = $sale->authorizeAndCapture($amount, '4012888818888', '04/17');
        $this->assertTrue($response->approved);
        
        $request = new AuthorizeNetTD;
        $response = $request->getUnsettledTransactionList();
        $this->assertTrue($response->isOk());
        $this->assertTrue($response->xml->transactions->count() >= 1);
    }
    
    public function testGetBatchStatistics()
    {
        $request = new AuthorizeNetTD;
        $response = $request->getSettledBatchList();
        $this->assertTrue($response->isOk());
        $this->assertTrue($response->xml->batchList->count() >= 1);
        $batchId = $response->xml->batchList->batch[0]->batchId;
        
        $request = new AuthorizeNetTD;
        $response = $request->getBatchStatistics($batchId);
        $this->assertTrue($response->isOk());
    }
  

}
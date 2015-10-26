<?php
/**
 * Easily interact with the Authorize.Net Transaction Details XML API.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetTD
 * @link       http://www.authorize.net/support/ReportingGuide_XML.pdf Transaction Details XML Guide
 */


/**
 * A class to send a request to the Transaction Details XML API.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetTD
 */ 
class AuthorizeNetTD extends AuthorizeNetRequest
{

    const LIVE_URL = "https://api.authorize.net/xml/v1/request.api";
    const SANDBOX_URL = "https://apitest.authorize.net/xml/v1/request.api";
    
    private $_xml;
    
    /**
     * This function returns information about a settled batch: Batch ID, Settlement Time, & 
     * Settlement State. If you specify includeStatistics, you also receive batch statistics 
     * by payment type.
     *
     *
     * The detault date range is one day (the previous 24 hour period). The maximum date range is 31 
     * days. The merchant time zone is taken into consideration when calculating the batch date range, 
     * unless the Z is specified in the first and last settlement date
     *
     * @param bool   $includeStatistics
     * @param string $firstSettlementDate //  yyyy-mmddTHH:MM:SS
     * @param string $lastSettlementDate  //  yyyy-mmddTHH:MM:SS
     * @param bool   $utc                 //  Use UTC instead of merchant time zone setting
     *
     * @return AuthorizeNetTD_Response
     */
    public function getSettledBatchList($includeStatistics = false, $firstSettlementDate = false, $lastSettlementDate = false, $utc = true)
    {
        $utc = ($utc ? "Z" : "");
        $this->_constructXml("getSettledBatchListRequest");
        ($includeStatistics ?
        $this->_xml->addChild("includeStatistics", $includeStatistics) : null);
        ($firstSettlementDate ?
        $this->_xml->addChild("firstSettlementDate", $firstSettlementDate . $utc) : null);
        ($lastSettlementDate ?
        $this->_xml->addChild("lastSettlementDate", $lastSettlementDate . $utc) : null);
        return $this->_sendRequest();
    }
    
    /**
     * Return all settled batches for a certain month.
     *
     * @param int $month
     * @param int $year
     *
     * @return AuthorizeNetTD_Response
     */
    public function getSettledBatchListForMonth($month = false, $year = false)
    {
        $month = ($month ? $month : date('m'));
        $year = ($year ? $year : date('Y'));
        $firstSettlementDate = substr(date('c',mktime(0, 0, 0, $month, 1, $year)),0,-6);
        $lastSettlementDate  = substr(date('c',mktime(0, 0, 0, $month+1, 0, $year)),0,-6);
        return $this->getSettledBatchList(true, $firstSettlementDate, $lastSettlementDate);
    }

    /**
     * This function returns limited transaction details for a specified batch ID
     *
     * @param int $batchId
     *
     * @return AuthorizeNetTD_Response
     */
    public function getTransactionList($batchId)
    {
        $this->_constructXml("getTransactionListRequest");
        $this->_xml->addChild("batchId", $batchId);
        return $this->_sendRequest();
    }
    
    /**
     * Return all transactions for a certain day.
     *
     * @param int $month
     * @param int $day
     * @param int $year
     *
     * @return array Array of SimpleXMLElments
     */
    public function getTransactionsForDay($month = false, $day = false, $year = false)
    {
        $transactions = array();
        $month = ($month ? $month : date('m'));
        $day = ($day ? $day : date('d'));
        $year = ($year ? $year : date('Y'));
        $firstSettlementDate = substr(date('c',mktime(0, 0, 0, (int)$month, (int)$day, (int)$year)),0,-6);
        $lastSettlementDate  = substr(date('c',mktime(0, 0, 0, (int)$month, (int)$day, (int)$year)),0,-6);
        $response = $this->getSettledBatchList(true, $firstSettlementDate, $lastSettlementDate);
        $batches = $response->xpath("batchList/batch");
        foreach ($batches as $batch) {
            $batch_id = (string)$batch->batchId;
            $request = new AuthorizeNetTD;
            $tran_list = $request->getTransactionList($batch_id);
            $transactions = array_merge($transactions, $tran_list->xpath("transactions/transaction"));
        }
        return $transactions;
    }

    /**
     * This function returns full transaction details for a specified transaction ID.
     *
     * @param int $transId
     *
     * @return AuthorizeNetTD_Response
     */    
    public function getTransactionDetails($transId)
    {
        $this->_constructXml("getTransactionDetailsRequest");
        $this->_xml->addChild("transId", $transId);
        return $this->_sendRequest();
    }
    
    /**
     * This function returns statistics about the settled batch specified by $batchId.
     *
     * @param int $batchId
     *
     * @return AuthorizeNetTD_Response
     */
    public function getBatchStatistics($batchId)
    {
        $this->_constructXml("getBatchStatisticsRequest");
        $this->_xml->addChild("batchId", $batchId);
        return $this->_sendRequest();
    }
    
    /**
     * This function returns the last 1000 unsettled transactions.
     *
     *
     * @return AuthorizeNetTD_Response
     */
    public function getUnsettledTransactionList()
    {
        $this->_constructXml("getUnsettledTransactionListRequest");
        return $this->_sendRequest();
    }
    
    /**
     * @return string
     */
    protected function _getPostUrl()
    {
        return ($this->_sandbox ? self::SANDBOX_URL : self::LIVE_URL);
    }
    
    /**
     *
     *
     * @param string $response
     * 
     * @return AuthorizeNetTransactionDetails_Response
     */
    protected function _handleResponse($response)
    {
        return new AuthorizeNetTD_Response($response);
    }
    
    /**
     * Prepare the XML post string.
     */
    protected function _setPostString()
    {
        $this->_post_string = $this->_xml->asXML();
        
    }
    
    /**
     * Start the SimpleXMLElement that will be posted.
     *
     * @param string $request_type The action to be performed.
     */
    private function _constructXml($request_type)
    {
        $string = '<?xml version="1.0" encoding="utf-8"?><'.$request_type.' xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"></'.$request_type.'>';
        $this->_xml = @new SimpleXMLElement($string);
        $merchant = $this->_xml->addChild('merchantAuthentication');
        $merchant->addChild('name',$this->_api_login);
        $merchant->addChild('transactionKey',$this->_transaction_key);
    }
    
}

/**
 * A class to parse a response from the Transaction Details XML API.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetTD
 */
class AuthorizeNetTD_Response extends AuthorizeNetXMLResponse
{
    

}

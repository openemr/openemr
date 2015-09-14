<?php
/**
 * Easily interact with the Authorize.Net ARB XML API.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetARB
 * @link       http://www.authorize.net/support/ARB_guide.pdf ARB Guide
 */


/**
 * A class to send a request to the ARB XML API.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetARB
 */
class AuthorizeNetARB extends AuthorizeNetRequest
{

    const LIVE_URL = "https://api.authorize.net/xml/v1/request.api";
    const SANDBOX_URL = "https://apitest.authorize.net/xml/v1/request.api";

    private $_request_type;
    private $_request_payload;
    
    /**
     * Optional. Used if the merchant wants to set a reference ID.
     *
     * @param string $refId
     */
    public function setRefId($refId)
    {
        $this->_request_payload = ($refId ? "<refId>$refId</refId>" : "");
    }
    
    /**
     * Create an ARB subscription
     *
     * @param AuthorizeNet_Subscription $subscription
     *
     * @return AuthorizeNetARB_Response
     */
    public function createSubscription(AuthorizeNet_Subscription $subscription)
    {
        $this->_request_type = "CreateSubscriptionRequest";
        $this->_request_payload .= $subscription->getXml();
        return $this->_sendRequest();
    }
    
    /**
     * Update an ARB subscription
     *
     * @param int                       $subscriptionId
     * @param AuthorizeNet_Subscription $subscription
     *
     * @return AuthorizeNetARB_Response
     */
    public function updateSubscription($subscriptionId, AuthorizeNet_Subscription $subscription)
    {
        $this->_request_type = "UpdateSubscriptionRequest";
        $this->_request_payload .= "<subscriptionId>$subscriptionId</subscriptionId>";
        $this->_request_payload .= $subscription->getXml();
        return $this->_sendRequest();
    }

    /**
     * Get status of a subscription
     *
     * @param int $subscriptionId
     *
     * @return AuthorizeNetARB_Response
     */
    public function getSubscriptionStatus($subscriptionId)
    {
        $this->_request_type = "GetSubscriptionStatusRequest";
        $this->_request_payload .= "<subscriptionId>$subscriptionId</subscriptionId>";
        return $this->_sendRequest();
    }

    /**
     * Cancel a subscription
     *
     * @param int $subscriptionId
     *
     * @return AuthorizeNetARB_Response
     */
    public function cancelSubscription($subscriptionId)
    {
        $this->_request_type = "CancelSubscriptionRequest";
        $this->_request_payload .= "<subscriptionId>$subscriptionId</subscriptionId>";
        return $this->_sendRequest();
    }
    
     /**
     *
     *
     * @param string $response
     * 
     * @return AuthorizeNetARB_Response
     */
    protected function _handleResponse($response)
    {
        return new AuthorizeNetARB_Response($response);
    }
    
    /**
     * @return string
     */
    protected function _getPostUrl()
    {
        return ($this->_sandbox ? self::SANDBOX_URL : self::LIVE_URL);
    }
    
    /**
     * Prepare the XML document for posting.
     */
    protected function _setPostString()
    {
        $this->_post_string =<<<XML
<?xml version="1.0" encoding="utf-8"?>
<ARB{$this->_request_type} xmlns= "AnetApi/xml/v1/schema/AnetApiSchema.xsd">
    <merchantAuthentication>
        <name>{$this->_api_login}</name>
        <transactionKey>{$this->_transaction_key}</transactionKey>
    </merchantAuthentication>
    {$this->_request_payload}
</ARB{$this->_request_type}>
XML;
    }
    
}


/**
 * A class to parse a response from the ARB XML API.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetARB
 */
class AuthorizeNetARB_Response extends AuthorizeNetXMLResponse
{

    /**
     * @return int
     */
    public function getSubscriptionId()
    {
        return $this->_getElementContents("subscriptionId");
    }
    
    /**
     * @return string
     */
    public function getSubscriptionStatus()
    {
        return $this->_getElementContents("Status");
    }

}
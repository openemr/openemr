<?php
/**
 * Easily interact with the Authorize.Net CIM XML API.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetCIM
 * @link       http://www.authorize.net/support/CIM_XML_guide.pdf CIM XML Guide
 */



/**
 * A class to send a request to the CIM XML API.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetCIM
 */ 
class AuthorizeNetCIM extends AuthorizeNetRequest
{

    const LIVE_URL = "https://api.authorize.net/xml/v1/request.api";
    const SANDBOX_URL = "https://apitest.authorize.net/xml/v1/request.api";

    
    private $_xml;
    private $_refId = false;
    private $_validationMode = "none"; // "none","testMode","liveMode"
    private $_extraOptions;
    private $_transactionTypes = array(
        'AuthOnly',
        'AuthCapture',
        'CaptureOnly',
        'PriorAuthCapture',
        'Refund',
        'Void',
    );
    
    /**
     * Optional. Used if the merchant wants to set a reference ID.
     *
     * @param string $refId
     */
    public function setRefId($refId)
    {
        $this->_refId = $refId;
    }
    
    /**
     * Create a customer profile.
     *
     * @param AuthorizeNetCustomer $customerProfile
     * @param string               $validationMode
     *
     * @return AuthorizeNetCIM_Response
     */
    public function createCustomerProfile($customerProfile, $validationMode = "none")
    {
        $this->_validationMode = $validationMode;
        $this->_constructXml("createCustomerProfileRequest");
        $profile = $this->_xml->addChild("profile");
        $this->_addObject($profile, $customerProfile);
        return $this->_sendRequest();
    }
    
    /**
     * Create a customer payment profile.
     *
     * @param int                        $customerProfileId
     * @param AuthorizeNetPaymentProfile $paymentProfile
     * @param string                     $validationMode
     *
     * @return AuthorizeNetCIM_Response
     */
    public function createCustomerPaymentProfile($customerProfileId, $paymentProfile, $validationMode = "none")
    {
        $this->_validationMode = $validationMode;
        $this->_constructXml("createCustomerPaymentProfileRequest");
        $this->_xml->addChild("customerProfileId", $customerProfileId);
        $profile = $this->_xml->addChild("paymentProfile");
        $this->_addObject($profile, $paymentProfile);
        return $this->_sendRequest();
    }
    
    /**
     * Create a shipping address.
     *
     * @param int                        $customerProfileId
     * @param AuthorizeNetAddress        $shippingAddress
     *
     * @return AuthorizeNetCIM_Response
     */
    public function createCustomerShippingAddress($customerProfileId, $shippingAddress)
    {
        $this->_constructXml("createCustomerShippingAddressRequest");
        $this->_xml->addChild("customerProfileId", $customerProfileId);
        $address = $this->_xml->addChild("address");
        $this->_addObject($address, $shippingAddress);
        return $this->_sendRequest();
    }
    
    /**
     * Create a transaction.
     *
     * @param string                     $transactionType
     * @param AuthorizeNetTransaction    $transaction
     * @param string                     $extraOptionsString
     *
     * @return AuthorizeNetCIM_Response
     */
    public function createCustomerProfileTransaction($transactionType, $transaction, $extraOptionsString = "")
    {
        $this->_constructXml("createCustomerProfileTransactionRequest");
        $transactionParent = $this->_xml->addChild("transaction");
        $transactionChild = $transactionParent->addChild("profileTrans" . $transactionType);
        $this->_addObject($transactionChild, $transaction);
        $this->_extraOptions = $extraOptionsString;
        return $this->_sendRequest();
    }
    
    /**
     * Delete a customer profile.
     *
     * @param int $customerProfileId
     *
     * @return AuthorizeNetCIM_Response
     */
    public function deleteCustomerProfile($customerProfileId)
    {
        $this->_constructXml("deleteCustomerProfileRequest");
        $this->_xml->addChild("customerProfileId", $customerProfileId);
        return $this->_sendRequest();
    }
    
    /**
     * Delete a payment profile.
     *
     * @param int $customerProfileId
     * @param int $customerPaymentProfileId
     *
     * @return AuthorizeNetCIM_Response
     */
    public function deleteCustomerPaymentProfile($customerProfileId, $customerPaymentProfileId)
    {
        $this->_constructXml("deleteCustomerPaymentProfileRequest");
        $this->_xml->addChild("customerProfileId", $customerProfileId);
        $this->_xml->addChild("customerPaymentProfileId", $customerPaymentProfileId);
        return $this->_sendRequest();
    }
    
    /**
     * Delete a shipping address.
     *
     * @param int $customerProfileId
     * @param int $customerAddressId
     *
     * @return AuthorizeNetCIM_Response
     */
    public function deleteCustomerShippingAddress($customerProfileId, $customerAddressId)
    {
        $this->_constructXml("deleteCustomerShippingAddressRequest");
        $this->_xml->addChild("customerProfileId", $customerProfileId);
        $this->_xml->addChild("customerAddressId", $customerAddressId);
        return $this->_sendRequest();
    }
    
    /**
     * Get all customer profile ids.
     *
     * @return AuthorizeNetCIM_Response
     */
    public function getCustomerProfileIds()
    {
        $this->_constructXml("getCustomerProfileIdsRequest");
        return $this->_sendRequest();
    }
    
    /**
     * Get a customer profile.
     *
     * @param int $customerProfileId
     *
     * @return AuthorizeNetCIM_Response
     */
    public function getCustomerProfile($customerProfileId)
    {
        $this->_constructXml("getCustomerProfileRequest");
        $this->_xml->addChild("customerProfileId", $customerProfileId);
        return $this->_sendRequest();
    }
    
    /**
     * Get a payment profile.
     *
     * @param int $customerProfileId
     * @param int $customerPaymentProfileId
     *
     * @return AuthorizeNetCIM_Response
     */
    public function getCustomerPaymentProfile($customerProfileId, $customerPaymentProfileId)
    {
        $this->_constructXml("getCustomerPaymentProfileRequest");
        $this->_xml->addChild("customerProfileId", $customerProfileId);
        $this->_xml->addChild("customerPaymentProfileId", $customerPaymentProfileId);
        return $this->_sendRequest();
    }
    
    /**
     * Get a shipping address.
     *
     * @param int $customerProfileId
     * @param int $customerAddressId
     *
     * @return AuthorizeNetCIM_Response
     */
    public function getCustomerShippingAddress($customerProfileId, $customerAddressId)
    {
        $this->_constructXml("getCustomerShippingAddressRequest");
        $this->_xml->addChild("customerProfileId", $customerProfileId);
        $this->_xml->addChild("customerAddressId", $customerAddressId);
        return $this->_sendRequest();
    }
    
    /**
     * Update a profile.
     *
     * @param int                        $customerProfileId
     * @param AuthorizeNetCustomer       $customerProfile
     *
     * @return AuthorizeNetCIM_Response
     */
    public function updateCustomerProfile($customerProfileId, $customerProfile)
    {
        $this->_constructXml("updateCustomerProfileRequest");
        $customerProfile->customerProfileId = $customerProfileId;
        $profile = $this->_xml->addChild("profile");
        $this->_addObject($profile, $customerProfile);
        return $this->_sendRequest();
    }
    
    /**
     * Update a payment profile.
     *
     * @param int                        $customerProfileId
     * @param int                        $customerPaymentProfileId
     * @param AuthorizeNetPaymentProfile $paymentProfile
     * @param string                     $validationMode
     *
     * @return AuthorizeNetCIM_Response
     */
    public function updateCustomerPaymentProfile($customerProfileId, $customerPaymentProfileId, $paymentProfile, $validationMode = "none")
    {
        $this->_validationMode = $validationMode;
        $this->_constructXml("updateCustomerPaymentProfileRequest");
        $this->_xml->addChild("customerProfileId", $customerProfileId);
        $paymentProfile->customerPaymentProfileId = $customerPaymentProfileId;
        $profile = $this->_xml->addChild("paymentProfile");
        $this->_addObject($profile, $paymentProfile);
        return $this->_sendRequest();
    }
    
    /**
     * Update a shipping address.
     *
     * @param int                        $customerProfileId
     * @param int                        $customerShippingAddressId
     * @param AuthorizeNetAddress        $shippingAddress
     *
     * @return AuthorizeNetCIM_Response
     */
    public function updateCustomerShippingAddress($customerProfileId, $customerShippingAddressId, $shippingAddress)
    {
        
        $this->_constructXml("updateCustomerShippingAddressRequest");
        $this->_xml->addChild("customerProfileId", $customerProfileId);
        $shippingAddress->customerAddressId = $customerShippingAddressId;
        $sa = $this->_xml->addChild("address");
        $this->_addObject($sa, $shippingAddress);
        return $this->_sendRequest();
    }
    
    /**
     * Update the status of an existing order that contains multiple transactions with the same splitTenderId.
     *
     * @param int                        $splitTenderId
     * @param string                     $splitTenderStatus
     *
     * @return AuthorizeNetCIM_Response
     */
    public function updateSplitTenderGroup($splitTenderId, $splitTenderStatus)
    {
        $this->_constructXml("updateSplitTenderGroupRequest");
        $this->_xml->addChild("splitTenderId", $splitTenderId);
        $this->_xml->addChild("splitTenderStatus", $splitTenderStatus);
        return $this->_sendRequest();
    }
    
    /**
     * Validate a customer payment profile.
     *
     * @param int                        $customerProfileId
     * @param int                        $customerPaymentProfileId
     * @param int                        $customerShippingAddressId
     * @param int                        $cardCode
     * @param string                     $validationMode
     *
     * @return AuthorizeNetCIM_Response
     */
    public function validateCustomerPaymentProfile($customerProfileId, $customerPaymentProfileId, $customerShippingAddressId, $cardCode, $validationMode = "testMode")
    {
        $this->_validationMode = $validationMode;
        $this->_constructXml("validateCustomerPaymentProfileRequest");
        $this->_xml->addChild("customerProfileId",$customerProfileId);
        $this->_xml->addChild("customerPaymentProfileId",$customerPaymentProfileId);
        $this->_xml->addChild("customerShippingAddressId",$customerShippingAddressId);
        $this->_xml->addChild("cardCode",$cardCode);
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
     * @return AuthorizeNetCIM_Response
     */
    protected function _handleResponse($response)
    {
        return new AuthorizeNetCIM_Response($response);
    }
    
    /**
     * Prepare the XML post string.
     */
    protected function _setPostString()
    {
        ($this->_validationMode != "none" ? $this->_xml->addChild('validationMode',$this->_validationMode) : "");
        $this->_post_string = $this->_xml->asXML();
        
        // Add extraOptions CDATA
        if ($this->_extraOptions) {
            $this->_xml->addChild("extraOptions");
            $this->_post_string = str_replace("<extraOptions></extraOptions>",'<extraOptions><![CDATA[' . $this->_extraOptions . ']]></extraOptions>', $this->_xml->asXML());
            $this->_extraOptions = false;
        }
        // Blank out our validation mode, so that we don't include it in calls that
        // don't use it.
        $this->_validationMode = "none";
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
        ($this->_refId ? $this->_xml->addChild('refId',$this->_refId) : "");
    }
    
    /**
     * Add an object to an SimpleXMLElement parent element.
     *
     * @param SimpleXMLElement $destination The parent element.
     * @param Object           $object      An object, array or value.  
     */
    private function _addObject($destination, $object)
    {
        $array = (array)$object;
        foreach ($array as $key => $value) {
            if ($value && !is_object($value)) {
                if (is_array($value) && count($value)) {
                    foreach ($value as $index => $item) {
                        $items = $destination->addChild($key);
                        $this->_addObject($items, $item);
                    }
                } else {
                    $destination->addChild($key,$value);
                }
            } elseif (is_object($value) && self::_notEmpty($value)) {
                $dest = $destination->addChild($key);
                $this->_addObject($dest, $value);
            }
        }
    }
    
    /**
     * Checks whether an array or object contains any values.
     *
     * @param Object $object
     *
     * @return bool
     */
    private static function _notEmpty($object)
    {
        $array = (array)$object;
        foreach ($array as $key => $value) {
            if ($value && !is_object($value)) {
                return true;
            } elseif (is_object($value)) {
                if (self::_notEmpty($value)) {
                    return true;
                }
            }
        }
        return false;
    }
    
}

/**
 * A class to parse a response from the CIM XML API.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetCIM
 */
class AuthorizeNetCIM_Response extends AuthorizeNetXMLResponse
{
    /**
     * @return AuthorizeNetAIM_Response
     */
    public function getTransactionResponse()
    {
        return new AuthorizeNetAIM_Response($this->_getElementContents("directResponse"), ",", "", array());
    }
    
    /**
     * @return array Array of AuthorizeNetAIM_Response objects for each payment profile.
     */
    public function getValidationResponses()
    {
        $responses = (array)$this->xml->validationDirectResponseList;
        $return = array();
        foreach ((array)$responses["string"] as $response) {
            $return[] = new AuthorizeNetAIM_Response($response, ",", "", array());
        }
        return $return;
    }
    
    /**
     * @return AuthorizeNetAIM_Response
     */
    public function getValidationResponse()
    {
        return new AuthorizeNetAIM_Response($this->_getElementContents("validationDirectResponse"), ",", "", array());
    }
    
    /**
     * @return array
     */
    public function getCustomerProfileIds()
    {
        $ids = (array)$this->xml->ids;
        return $ids["numericString"];
    }
    
    /**
     * @return array
     */
    public function getCustomerPaymentProfileIds()
    {
        $ids = (array)$this->xml->customerPaymentProfileIdList;
        return $ids["numericString"];
    }
    
    /**
     * @return array
     */
    public function getCustomerShippingAddressIds()
    {
        $ids = (array)$this->xml->customerShippingAddressIdList;
        return $ids["numericString"];
    }
    
    /**
     * @return string
     */
    public function getCustomerAddressId()
    {
        return $this->_getElementContents("customerAddressId");
    }
    
    /**
     * @return string
     */
    public function getCustomerProfileId()
    {
        return $this->_getElementContents("customerProfileId");
    }
    
    /**
     * @return string
     */
    public function getPaymentProfileId()
    {
        return $this->_getElementContents("customerPaymentProfileId");
    }

}

<?php
/**
 * Base class for the AuthorizeNet ARB & CIM Responses.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetXML
 */

/**
 * Base class for the AuthorizeNet ARB & CIM Responses.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetXML
 */
class AuthorizeNetXMLResponse
{

    public $xml; // Holds a SimpleXML Element with response.

    /**
     * Constructor. Parses the AuthorizeNet response string.
     *
     * @param string $response The response from the AuthNet server.
     */
    public function __construct($response)
    {
        $this->response = $response;
        if ($response) {
            $this->xml = @simplexml_load_string($response);
            
            // Remove namespaces for use with XPath.
            $this->xpath_xml = @simplexml_load_string(preg_replace('/ xmlns:xsi[^>]+/','',$response));
        }
    }
    
    /**
     * Was the transaction successful?
     *
     * @return bool
     */
    public function isOk()
    {
        return ($this->getResultCode() == "Ok");
    }
    
    /**
     * Run an xpath query on the cleaned XML response
     *
     * @param  string $path
     * @return array  Returns an array of SimpleXMLElement objects or FALSE in case of an error.
     */
    public function xpath($path)
    {
        return $this->xpath_xml->xpath($path);
    }
    
    /**
     * Was there an error?
     *
     * @return bool
     */
    public function isError()
    {
        return ($this->getResultCode() == "Error");
    }

    /**
     * @return string
     */    
    public function getErrorMessage()
    {
        return "Error: {$this->getResultCode()} 
        Message: {$this->getMessageText()}
        {$this->getMessageCode()}";    
    }
    
    /**
     * @return string
     */
    public function getRefID()
    {
        return $this->_getElementContents("refId");
    }
    
    /**
     * @return string
     */
    public function getResultCode()
    {
        return $this->_getElementContents("resultCode");
    }
    
    /**
     * @return string
     */
    public function getMessageCode()
    {
        return $this->_getElementContents("code");
    }
    
    /**
     * @return string
     */
    public function getMessageText()
    {
        return $this->_getElementContents("text");
    }
    
    /**
     * Grabs the contents of a unique element.
     *
     * @param  string
     * @return string
     */
    protected function _getElementContents($elementName) 
    {
        $start = "<$elementName>";
        $end = "</$elementName>";
        if (strpos($this->response,$start) === false || strpos($this->response,$end) === false) {
            return false;
        } else {
            $start_position = strpos($this->response, $start)+strlen($start);
            $end_position = strpos($this->response, $end);
            return substr($this->response, $start_position, $end_position-$start_position);
        }
    }

}
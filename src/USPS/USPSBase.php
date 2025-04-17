<?php

/*
 * Base class for USPS Web API Tools
 * originally under MIT License
 * https://packagist.org/packages/binarydata/usps-php-api
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Vincent Gabriel
 * @author    stephen waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2012 Vincent Gabriel
 * @copyright Copyright (c) 2022 stephen waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\USPS;

use LaLit\Array2XML;
use LaLit\XML2Array;

/**
 * USPS Base class
 * used to perform the actual api calls
 * @since 1.0
 * @author Vincent Gabriel
 */
class USPSBase
{
    const LIVE_API_URL = 'https://secure.shippingapis.com/ShippingAPI.dll';

  /**
   * @var string - the usps username provided by the usps website
   */
    protected $username = '';
  /**
   *  the error code if one exists
   * @var integer
   */
    protected $errorCode = 0;
  /**
   * the error message if one exists
   * @var string
   */
    protected $errorMessage = '';
  /**
   *  the response message
   * @var string
   */
    protected $response = '';
  /**
   *  the headers returned from the call made
   * @var array
   */
    protected $headers = '';
  /**
   * The response represented as an array
   * @var array
   */
    protected $arrayResponse = array();
  /**
   * All the post fields we will add to the call
   * @var array
   */
    protected $postFields = array();
  /**
   * The api type we are about to call
   * @var string
   */
    protected $apiVersion = '';
  /**
   * @var boolean - set whether we are in a test mode or not
   */
    public static $testMode = false;
  /**
   * @var array - different kind of supported api calls by this wrapper
   */
    protected $apiCodes = array(
    'RateV2' => 'RateV2Request',
    'RateV4' => 'RateV4Request',
    'IntlRateV2' => 'IntlRateV2Request',
    'Verify' => 'AddressValidateRequest',
    'ZipCodeLookup' => 'ZipCodeLookupRequest',
    'CityStateLookup' => 'CityStateLookupRequest',
    'TrackV2' => 'TrackFieldRequest',
    'FirstClassMail' => 'FirstClassMailRequest',
    'SDCGetLocations' => 'SDCGetLocationsRequest',
    'ExpressMailLabel' => 'ExpressMailLabelRequest',
    'PriorityMail' => 'PriorityMailRequest',
    'OpenDistributePriorityV2' => 'OpenDistributePriorityV2.0Request',
    'OpenDistributePriorityV2Certify' => 'OpenDistributePriorityV2.0CertifyRequest',
    'ExpressMailIntl' => 'ExpressMailIntlRequest',
    'PriorityMailIntl' => 'PriorityMailIntlRequest',
    'FirstClassMailIntl' => 'FirstClassMailIntlRequest',
    );
  /**
   * Default options for curl.
     */
    public static $CURL_OPTS = array(
    CURLOPT_CONNECTTIMEOUT => 30,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 60,
    CURLOPT_FRESH_CONNECT  => 1,
    CURLOPT_PORT       => 443,
    CURLOPT_USERAGENT      => 'usps-php',
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_RETURNTRANSFER => true,
    );
  /**
   * Constructor
   * @param string $username - the usps api username
   */
    public function __construct($username = '')
    {
        $this->username = $username;
    }
  /**
   * set the usps api username we are going to user
   * @param string $username - the usps api username
   */
    public function setUsername($username)
    {
        $this->username = $username;
    }
  /**
   * Return the post data fields as an array
   * @return array
   */
    public function getPostData()
    {
        $fields = array('API' => $this->apiVersion, 'XML' => $this->getXMLString());
        return $fields;
    }
  /**
   * Set the api version we are going to use
   * @param string $version the new api version
   * @return void
   */
    public function setApiVersion($version)
    {
        $this->apiVersion = $version;
    }
  /**
   * Set whether we are in a test mode or not
   * @param boolean $value
   * @return void
   */
    public function setTestMode($value)
    {
        self::$testMode = (bool) $value;
    }
  /**
   * Response api name
   * @return string
   */
    public function getResponseApiName()
    {
        return str_replace('Request', 'Response', $this->apiCodes[$this->apiVersion]);
    }
  /**
   * Makes an HTTP request. This method can be overriden by subclasses if
   * developers want to do fancier things or use something other than curl to
   * make the request.
   *
   * @param CurlHandler optional initialized curl handle
   * @return String the response text
   */
    protected function doRequest($ch = null)
    {
        if (!$ch) {
            $ch = curl_init();
        }

        $opts = self::$CURL_OPTS;
        $opts[CURLOPT_POSTFIELDS] = http_build_query($this->getPostData(), null, '&');
        $opts[CURLOPT_URL] = $this->getEndpoint();

      // Replace 443 with 80 if it's not secured
        if (strpos($opts[CURLOPT_URL], 'https://') === false) {
            $opts[CURLOPT_PORT] = 80;
        }

      // set options
        curl_setopt_array($ch, $opts);

      // execute
        $this->setResponse(curl_exec($ch));
        $this->setHeaders(curl_getinfo($ch));

      // fetch errors
        $this->setErrorCode(curl_errno($ch));
        $this->setErrorMessage(curl_error($ch));

      // Convert response to array
        $this->convertResponseToArray();

      // If it failed then set error code and message
        if ($this->isError()) {
            $arrayResponse = $this->getArrayResponse();

          // Find the error number
            $errorInfo = $this->getValueByKey($arrayResponse, 'Error');

            if ($errorInfo) {
                $this->setErrorCode($errorInfo['Number']);
                $this->setErrorMessage($errorInfo['Description']);
            }
        }

      // close
        curl_close($ch);

        return $this->getResponse();
    }

    public function getEndpoint()
    {
        return self::$testMode ? self::TEST_API_URL : self::LIVE_API_URL;
    }

  /**
   * Return the xml string built that we are about to send over to the api
   * @return string
   */
    protected function getXMLString()
    {
      // Add in the defaults
        $postFields = array(
        '@attributes' => array('USERID' => $this->username),
        );

      // Add in the sub class data
        $postFields = array_merge($postFields, $this->getPostFields());

        $xml = Array2XML::createXML($this->apiCodes[$this->apiVersion], $postFields);
        return $xml->saveXML();
    }
  /**
   * Did we encounter an error?
   * @return boolean
   */
    public function isError()
    {
        $headers = $this->getHeaders();
        $response = $this->getArrayResponse();
      // First make sure we got a valid response
        if ($headers['http_code'] != 200) {
            return true;
        }

      // Make sure the response does not have error in it
        if (isset($response['Error'])) {
            return true;
        }

      // Check to see if we have the Error word in the response
        if (strpos($this->getResponse(), '<Error>') !== false) {
            return true;
        }

      // No error
        return false;
    }
  /**
   * Was the last call successful
   * @return boolean
   */
    public function isSuccess()
    {
        return !$this->isError() ? true : false;
    }
  /**
   * Return the response represented as string
   * @return array
   */
    public function convertResponseToArray()
    {
        if ($this->getResponse()) {
            $this->setArrayResponse(XML2Array::createArray($this->getResponse()));
        }

        return $this->getArrayResponse();
    }
  /**
   * Set the array response value
   * @param array $value
   * @return void
   */
    public function setArrayResponse($value)
    {
        $this->arrayResponse = $value;
    }
  /**
   * Return the array representation of the last response
   * @return array
   */
    public function getArrayResponse()
    {
        return $this->arrayResponse;
    }
  /**
   * Set the response
   *
   * @param mixed the response returned from the call
   * @return facebookLib object
   */
    public function setResponse($response = '')
    {
        $this->response = $response;
        return $this;
    }
  /**
   * Get the response data
   *
   * @return mixed the response data
   */
    public function getResponse()
    {
        return $this->response;
    }

  /**
   * Set the headers
   *
   * @param string $headers
   *
   * @internal param \USPS\the $array headers array
   * @return facebookLib object
   */
    public function setHeaders($headers = '')
    {
        $this->headers = $headers;
        return $this;
    }
  /**
   * Get the headers
   *
   * @return array the headers returned from the call
   */
    public function getHeaders()
    {
        return $this->headers;
    }
  /**
   * Set the error code number
   *
   * @param integer the error code number
   * @return facebookLib object
   */
    public function setErrorCode($code = 0)
    {
        $this->errorCode = $code;
        return $this;
    }
  /**
   * Get the error code number
   *
   * @return integer error code number
   */
    public function getErrorCode()
    {
        return $this->errorCode;
    }
  /**
   * Set the error message
   *
   * @param string the error message
   * @return facebookLib object
   */
    public function setErrorMessage($message = '')
    {
        $this->errorMessage = $message;
        return $this;
    }
  /**
   * Get the error code message
   *
   * @return string error code message
   */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
  /**
   * Find a key inside a multi dim. array
   * @param array $array
   * @param string $key
   * @return mixed
   */
    protected function getValueByKey($array, $key)
    {
        foreach ($array as $k => $each) {
            if ($k == $key) {
                return $each;
            }

            if (is_array($each)) {
                if ($return = $this->getValueByKey($each, $key)) {
                    return $return;
                }
            }
        }

      // Nothing matched
        return null;
    }
}

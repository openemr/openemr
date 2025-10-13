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
    const LIVE_API_V3_URL = 'https://api.usps.com/addresses/v3';
    const OAUTH_TOKEN_URL = 'https://api.usps.com/oauth2/v3/token';
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
    protected $arrayResponse = [];
  /**
   * All the post fields we will add to the call
   * @var array
   */
    protected $postFields = [];
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
   * @var string - access token for v3 API
   */
    protected $accessToken = '';
  /**
   * @var int - token expiration time
   */
    protected $tokenExpiry = 0;
  /**
   * @var boolean - use v3 API or legacy
   */
    protected $useV3 = false;
  /**
   * @var array - different kind of supported api calls by this wrapper
   */
    protected $apiCodes = [
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
    ];
  /**
   * Default options for curl.
     */
    public static $CURL_OPTS = [
    CURLOPT_CONNECTTIMEOUT => 30,
    CURLOPT_TIMEOUT        => 60,
    CURLOPT_FRESH_CONNECT  => 1,
    CURLOPT_PORT       => 443,
    CURLOPT_USERAGENT      => 'usps-php',
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_RETURNTRANSFER => true,
    ];
  /**
   * Constructor
   * @param string $username - legacy API username
   * @param string $clientId - v3 API client ID
   * @param string $clientSecret - v3 API client secret
   */
    public function __construct(protected $username = '', protected $clientId = '', protected $clientSecret = '')
    {
        // use v3 if we have client credentials, otherwise legacy
        $this->useV3 = !empty($this->clientId) && !empty($this->clientSecret);
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
        $fields = ['API' => $this->apiVersion, 'XML' => $this->getXMLString()];
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
        if (!str_contains($opts[CURLOPT_URL], 'https://')) {
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
        if ($this->useV3) {
            return self::LIVE_API_V3_URL;
        }
        return self::$testMode ? self::TEST_API_URL : self::LIVE_API_URL;
    }

  /**
   * Get access token (fetches new one if expired)
   * @return string|false
   */
    protected function getAccessToken()
    {
        if (!empty($this->accessToken) && time() < $this->tokenExpiry) {
            return $this->accessToken;
        }

        return $this->fetchAccessToken();
    }

  /**
   * Fetch new access token
   * @return string|false
   */
    protected function fetchAccessToken()
    {
        $ch = curl_init(self::OAUTH_TOKEN_URL);

        $postData = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => 'addresses'
        ];

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            $this->setErrorCode($httpCode);
            $this->setErrorMessage('Failed to get access token');
            return false;
        }

        $tokenData = json_decode($response, true);

        if (isset($tokenData['access_token'])) {
            $this->accessToken = $tokenData['access_token'];
            // cache with 60sec buffer
            $expiresIn = $tokenData['expires_in'] ?? 3600;
            $this->tokenExpiry = time() + $expiresIn - 60;
            return $this->accessToken;
        }

        $this->setErrorMessage('Invalid token response');
        return false;
    }

  /**
   * Make v3 API request
   * @param string $endpoint
   * @param array $params
   * @return string
   */
    protected function doRequestV3($endpoint, $params = [])
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return '';
        }

        $url = $this->getEndpoint() . $endpoint;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $this->setHeaders(curl_getinfo($ch));
        $this->setErrorCode(curl_errno($ch));
        $this->setErrorMessage(curl_error($ch));

        $this->setResponse($response);

        if ($response) {
            $jsonData = json_decode($response, true);
            if ($jsonData !== null) {
                $this->setArrayResponse($jsonData);
            } else {
                $this->setErrorMessage('Failed to parse JSON: ' . json_last_error_msg());
            }
        } else {
            $this->setErrorMessage('Empty response');
        }

        if ($this->isError()) {
            $arrayResponse = $this->getArrayResponse();
            if (isset($arrayResponse['error'])) {
                $this->setErrorCode($arrayResponse['error']['code'] ?? 0);
                $this->setErrorMessage($arrayResponse['error']['message'] ?? 'Unknown error');
            }
        }

        curl_close($ch);

        return $this->getResponse();
    }

  /**
   * Return the xml string built that we are about to send over to the api
   * @return string
   */
    protected function getXMLString()
    {
      // Add in the defaults
        $postFields = [
        '@attributes' => ['USERID' => $this->username],
        ];

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
        if (is_array($headers) && isset($headers['http_code']) && $headers['http_code'] != 200) {
            return true;
        }

      // Check for errors in array response
        if (is_array($response)) {
            // legacy XML format
            if (isset($response['Error'])) {
                return true;
            }

            // v3 JSON format
            if (isset($response['error'])) {
                return true;
            }
        }

      // Check to see if we have the Error word in the response string
        $responseText = $this->getResponse();
        if (is_string($responseText) && str_contains($responseText, '<Error>')) {
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

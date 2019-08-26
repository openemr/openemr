<?php

namespace RingCentral\SDK\Platform;

use Exception;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use RingCentral\SDK\Http\ApiException;
use RingCentral\SDK\Http\ApiResponse;
use RingCentral\SDK\Http\Client;
use RingCentral\SDK\SDK;

class Platform
{
    const ACCESS_TOKEN_TTL = 3600; // 60 minutes
    const REFRESH_TOKEN_TTL = 604800; // 1 week
    const TOKEN_ENDPOINT = '/restapi/oauth/token';
    const REVOKE_ENDPOINT = '/restapi/oauth/revoke';
    const AUTHORIZE_ENDPOINT = '/restapi/oauth/authorize';
    const API_VERSION = 'v1.0';
    const URL_PREFIX = '/restapi';

    /** @var string */
    protected $_server;

    /** @var string */
    protected $_clientId;

    /** @var string */
    protected $_clientSecret;

    /** @var string */
    protected $_appName;

    /** @var string */
    protected $_appVersion;

    /** @var string */
    protected $_userAgent;

    /** @var Auth */
    protected $_auth;

    /** @var Client */
    protected $_client;

    /**
     * Platform constructor.
     *
     * @param Client $client
     * @param string $clientId
     * @param string $clientSecret
     * @param string $server
     * @param string $appName
     * @param string $appVersion
     */
    public function __construct(Client $client, $clientId, $clientSecret, $server, $appName = '', $appVersion = '')
    {

        $this->_clientId = $clientId;
        $this->_clientSecret = $clientSecret;
        $this->_appName = empty($appName) ? 'Unnamed' : $appName;
        $this->_appVersion = empty($appVersion) ? '0.0.0' : $appVersion;

        $this->_server = $server;

        $this->_auth = new Auth();
        $this->_client = $client;

        $this->_userAgent = (!empty($this->_appName) ? ($this->_appName . (!empty($this->_appVersion) ? '/' . $this->_appVersion : '') . ' ') : '') .
                            php_uname('s') . '/' . php_uname('r') . ' ' .
                            'PHP/' . phpversion() . ' ' .
                            'RCPHPSDK/' . SDK::VERSION;

    }

    /**
     * @return Auth
     */
    public function auth()
    {
        return $this->_auth;
    }

    /**
     * @param string $path
     * @param array  $options
     *
     * @return string
     */
    public function createUrl($path = '', $options = array())
    {

        $builtUrl = '';
        $hasHttp = stristr($path, 'http://') || stristr($path, 'https://');

        if (!empty($options['addServer']) && !$hasHttp) {
            $builtUrl .= $this->_server;
        }

        if (!stristr($path, self::URL_PREFIX) && !$hasHttp) {
            $builtUrl .= self::URL_PREFIX . '/' . self::API_VERSION;
        }

        $builtUrl .= $path;

        if (!empty($options['addMethod']) || !empty($options['addToken'])) {
            $builtUrl .= (stristr($path, '?') ? '&' : '?');
        }

        if (!empty($options['addMethod'])) {
            $builtUrl .= '_method=' . $options['addMethod'];
        }
        if (!empty($options['addToken'])) {
            $builtUrl .= ($options['addMethod'] ? '&' : '') . 'access_token=' . $this->_auth->accessToken();
        }

        return $builtUrl;

    }

    /**
     * This function has mixed purposes. On the face of it, it can used to return a boolean value which represents
     * whether or not the platform has been configured with valid authentication tokens.
     *
     * However, it also does much more than that. If the access token is expired BUT the refresh token is valid, then
     * this function takes it upon itself to use that refresh token to automatically request brand new tokens, which it
     * then sets and uses.
     *
     * @return bool True if the access token is value OR it is able to request new tokens successfully, otherwise false
     */
    public function loggedIn()
    {
        try {
            return $this->_auth->accessTokenValid() || $this->refresh();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Create and return a URL that can be used for authenticating/logging in to RingCentral.
     *
     * @param array $options An array containing information that will be added to the generated URL.
     *                       $options = [
     *                           'redirectUri' => (string) The callback URI to use once authentication is complete.
     *                           'state'       => (string)
     *                           'brandId'     => (string)
     *                           'display'     => (string)
     *                           'prompt'      => (string)
     *                       ]
     *
     * @return string
     */
    public function authUrl($options)
    {

        return $this->createUrl(self::AUTHORIZE_ENDPOINT . '?' . http_build_query(
            array (
            'response_type' => 'code',
            'redirect_uri'  => $options['redirectUri'] ? $options['redirectUri'] : null,
            'client_id'     => $this->_clientId,
            'state'         => $options['state'] ? $options['state'] : null,
            'brand_id'      => $options['brandId'] ? $options['brandId'] : null,
            'display'       => $options['display'] ? $options['display'] : null,
            'prompt'        => $options['prompt'] ? $options['prompt'] : null
        )), array(
            'addServer'     => 'true'
        ));
    }

    /**
     * @param string $url
     *
     * @return array
     */
    public function parseAuthRedirectUrl($url)
    {

        parse_str($url,$qsArray);
        return array(
                'code' => $qsArray['code']
        );
    }

    /**
     * @param string $username
     * @param string $extension
     * @param string $password
     *
     * @throws ApiException If there is an issue with the token request.
     * @throws Exception    If it fails to retrieve/parse JSON data from he response.
     *
     * @return ApiResponse
     */
    public function login($options)
    {
        if (is_string($options)) {
           $options = array(
                            'username'  => func_get_arg(0),
                            'extension' => func_get_arg(1) ? func_get_arg(1) : null,
                            'password'  => func_get_arg(2)
                            );
        }

        $response = !empty($options['code']) ? $this->requestToken(self::TOKEN_ENDPOINT, array(

            'grant_type'        => 'authorization_code',
            'code'              => $options['code'],
            'redirect_uri'      => $options['redirectUri'],
            'access_token_ttl'  => self::ACCESS_TOKEN_TTL,
            'refresh_token_ttl' => self::REFRESH_TOKEN_TTL

        )) :$this->requestToken(self::TOKEN_ENDPOINT, array(

            'grant_type'        => 'password',
            'username'          => $options['username'],
            'extension'         => $options['extension'] ? $options['extension'] : null,
            'password'          => $options["password"],
            'access_token_ttl'  => self::ACCESS_TOKEN_TTL,
            'refresh_token_ttl' => self::REFRESH_TOKEN_TTL

        ));

        $this->_auth->setData($response->jsonArray());

        return $response;

    }

    /**
     * Attempt to request new access and refresh tokens using the existing refresh token.
     *
     * @throws ApiException If the existing refresh token is invalid or there is an issue with the request.
     * @throws Exception    If it fails to retrieve/parse JSON data from he response.
     *
     * @return ApiResponse
     */
    public function refresh()
    {

        if (!$this->_auth->refreshTokenValid()) {
            throw new ApiException(null, new Exception('Refresh token has expired'));
        }

        // Synchronous
        $response = $this->requestToken(self::TOKEN_ENDPOINT, array(
            "grant_type"        => "refresh_token",
            "refresh_token"     => $this->_auth->refreshToken(),
            "access_token_ttl"  => self::ACCESS_TOKEN_TTL,
            "refresh_token_ttl" => self::REFRESH_TOKEN_TTL
        ));

        $this->_auth->setData($response->jsonArray());

        return $response;

    }

    /**
     * @throws ApiException If an error occurs making the request.
     * @throws Exception    If an error occurs parsing the response from the request.
     *
     * @return ApiResponse
     */
    public function logout()
    {

        $response = $this->requestToken(self::REVOKE_ENDPOINT, array(
            'token' => $this->_auth->accessToken()
        ));

        $this->_auth->reset();

        return $response;

    }

    /**
     * Convenience helper used for processing requests (even externally created).
     * Performs access token refresh if needed.
     * Then adds Authorization header and API server to URI
     *
     * @param RequestInterface $request
     * @param array            $options
     *
     * @throws ApiException If an error occurs making the refresh request.
     * @throws Exception    If an error occurs parsing the response from the refresh request.
     *
     * @return RequestInterface
     */
    public function inflateRequest(RequestInterface $request, $options = array())
    {

        if (empty($options['skipAuthCheck'])) {

            $this->ensureAuthentication();

            /** @var RequestInterface $request */
            $request = $request->withHeader('Authorization', $this->authHeader());

        }

        /** @var RequestInterface $request */
        $request = $request->withAddedHeader('User-Agent', $this->_userAgent)
                           ->withAddedHeader('RC-User-Agent', $this->_userAgent);

        $uri = new Uri($this->createUrl((string)$request->getUri(), array('addServer' => true)));

        return $request->withUri($uri);

    }

    /**
     * Method sends the request (even externally created) to API server using client
     *
     * @param RequestInterface $request
     * @param array            $options
     *
     * @throws ApiException If an error occurs making the refresh request as part of inflateRequest()
     * @throws Exception    If an error occurs parsing the response from the refresh request as part of inflateRequest()
     *
     * @return ApiResponse

     */
    public function sendRequest(RequestInterface $request, $options = array())
    {

        return $this->_client->send($this->inflateRequest($request, $options));

    }

    /**
     * @param string $url
     * @param array  $queryParameters
     * @param array  $headers
     * @param array  $options
     *
     * @throws ApiException If an error occurs making the request.
     * @throws Exception    If an error occurs parsing the response from the request.
     *
     * @return ApiResponse
     */
    public function get($url = '', $queryParameters = array(), array $headers = array(), $options = array())
    {
        return $this->sendRequest(
            $this->_client->createRequest('GET', $url, $queryParameters, null, $headers),
            $options
        );
    }

    /**
     * @param string $url
     * @param array  $body
     * @param array  $queryParameters
     * @param array  $headers
     * @param array  $options
     *
     * @throws ApiException If an error occurs making the request.
     * @throws Exception    If an error occurs parsing the response from the request.
     *
     * @return ApiResponse
     */
    public function post(
        $url = '',
        $body = null,
        $queryParameters = array(),
        array $headers = array(),
        $options = array()
    ) {
        return $this->sendRequest(
            $this->_client->createRequest('POST', $url, $queryParameters, $body, $headers),
            $options
        );
    }

    /**
     * @param string $url
     * @param array  $body
     * @param array  $queryParameters
     * @param array  $headers
     * @param array  $options
     *
     * @throws ApiException If an error occurs making the request.
     * @throws Exception    If an error occurs parsing the response from the request.
     *
     * @return ApiResponse
     */
    public function patch(
        $url = '',
        $body = null,
        $queryParameters = array(),
        array $headers = array(),
        $options = array()
    ) {
        return $this->sendRequest(
            $this->_client->createRequest('PATCH', $url, $queryParameters, $body, $headers),
            $options
        );
    }    
    
    /**
     * @param string $url
     * @param array  $body
     * @param array  $queryParameters
     * @param array  $headers
     * @param array  $options
     *
     * @throws ApiException If an error occurs making the request.
     * @throws Exception    If an error occurs parsing the response from the request.
     *
     * @return ApiResponse
     */
    public function put(
        $url = '',
        $body = null,
        $queryParameters = array(),
        array $headers = array(),
        $options = array()
    ) {
        return $this->sendRequest(
            $this->_client->createRequest('PUT', $url, $queryParameters, $body, $headers),
            $options
        );
    }

    /**
     * @param string $url
     * @param array  $queryParameters
     * @param array  $headers
     * @param array  $options
     *
     * @throws ApiException If an error occurs making the request.
     * @throws Exception    If an error occurs parsing the response from the request.
     *
     * @return ApiResponse
     */
    public function delete($url = '', $queryParameters = array(), array $headers = array(), $options = array())
    {
        return $this->sendRequest(
            $this->_client->createRequest('DELETE', $url, $queryParameters, null, $headers),
            $options
        );
    }

    /**
     * @param string $path
     * @param array  $body
     *
     * @throws ApiException If an error occurs making the request.
     * @throws Exception    If an error occurs parsing the response from the request.
     *
     * @return ApiResponse
     */
    protected function requestToken($path = '', $body = array())
    {

        $headers = array(
            'Authorization' => 'Basic ' . $this->apiKey(),
            'Content-Type'  => 'application/x-www-form-urlencoded'
        );

        $request = $this->_client->createRequest('POST', $path, null, $body, $headers);

        return $this->sendRequest($request, array('skipAuthCheck' => true));

    }

    /**
     * @return string
     */
    protected function apiKey()
    {
        return base64_encode($this->_clientId . ':' . $this->_clientSecret);
    }

    /**
     * @return string
     */
    protected function authHeader()
    {
        return $this->_auth->tokenType() . ' ' . $this->_auth->accessToken();
    }

    /**
     * @throws ApiException If an error occurs making the refresh request.
     * @throws Exception    If an error occurs parsing the response from the refresh request.
     *
     * @return void
     */
    protected function ensureAuthentication()
    {
        if (!$this->_auth->accessTokenValid()) {
            $this->refresh();
        }
    }

}

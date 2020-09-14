<?php

/**
 * Http Rest and OAuth 2 Clients
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Http;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use kamermans\OAuth2\OAuth2Middleware;
use kamermans\OAuth2\GrantType\PasswordCredentials;
use kamermans\OAuth2\Persistence\FileTokenPersistence;

//use kamermans\OAuth2\GrantType\RefreshToken;

/**
 * Class oeOAuth
 * @package OpenEMR\Common\Http
 */
// @TODO handle a refresh token returned instead of getting new token on expiry
class oeOAuth
{
    protected $auth_client;
    protected $auth_config = [];
    protected $auth_options = [];
    protected $httpCache;
    protected $grant_type;
    protected $oauth;
    protected $token_storage;
    protected $stack;
    public $apiOAuth = false;
    public $DEBUG_MODE = false;
    public $useProxy = false;

    public function __construct()
    {
        $this->auth_options = [
            'base_uri' => '',
            'http_errors' => false,
            'verify' => false,
        ];
        $this->auth_config = [
            "username" => "dummy",
            "password" => "dummy",
            "client_id" => "",
            "scope" => ""
        ];
    }

    public function initOAuthClient()
    {
        $this->apiOAuth = true;

        // auth endpoint debug
        if ($this->DEBUG_MODE) {
            $this->usingAuthHeaders(['Cookie' => 'XDEBUG_SESSION=PHPSTORM']);
            if ($this->useProxy) {
                $this->setAuthOptions(['proxy' => 'localhost:5000']);
            }
        }
        // maintain an oauth client. oeOAuth has it's own guzzle client to manage tokens
        // whereas a static oeHttp client instantiates an oeHttpRequest which wraps a guzzle request.
        //
        $this->auth_client = new Client($this->auth_options);

        // Use php file to persist a safe token storage.
        //
        $token_path = $GLOBALS['OE_SITE_DIR'] . '/documents/logs_and_misc/methods/_cache';
        $this->token_storage = new FileTokenPersistence($token_path);
        $this->grant_type = new PasswordCredentials($this->auth_client, $this->auth_config);
        $this->oauth = new OAuth2Middleware($this->grant_type);
        $this->oauth->setTokenPersistence($this->token_storage);

        // Setup stack for later adding the oauth handler to http client.
        // Implemented late in request send.
        //
        $this->stack = HandlerStack::create();
        $this->stack->push($this->oauth);
    }

    public function usingAuthHeaders($headers)
    {
        return $this->tap($this, function ($request) use ($headers) {
            return $this->auth_options = array_merge_recursive($this->auth_options, [
                'headers' => $headers
            ]);
        });
    }

    public function usingAuthEndpoint($baseUri)
    {
        return $this->tap($this, function ($request) use ($baseUri) {
            return $this->auth_options = array_merge($this->auth_options, [
                'base_uri' => $baseUri,
            ]);
        });
    }

    public function setAuthOptions($options)
    {
        return $this->tap($this, function ($request) use ($options) {
            return $this->auth_options = array_merge_recursive($this->auth_options, $options);
        });
    }

    protected function tap($value, $callback)
    {
        $callback($value);
        return $value;
    }
}

/**
 * Class oeHttp
 * extends oeOAuth
 *
 * @package OpenEMR\Common\Http
 */
class oeHttp extends oeOAuth
{
    public static $client;

    public static function __callStatic($method, $args)
    {
        return oeHttpRequest::newArgs(static::client())->{$method}(...$args);
    }

    public static function client()
    {
        return static::$client ?: static::$client = new Client();
    }
}

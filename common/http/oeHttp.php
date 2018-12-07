<?php
/**
 * Http Rest and OAuth 2 Clients
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Http;

use Doctrine\Common\Cache\PhpFileCache;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use kamermans\OAuth2\OAuth2Middleware;
use kamermans\OAuth2\GrantType\PasswordCredentials;
use kamermans\OAuth2\Persistence\DoctrineCacheTokenPersistence;

//use kamermans\OAuth2\Persistence\FileTokenPersistence;
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
    protected static $grant_type;
    protected static $oauth;
    protected static $token_storage;
    protected static $stack;
    // defaults to normally using as rest client for calling rest controllers and services.
    // current session is discretely passed and maintained in this mode otherwise, sessions are
    // destroyed after each api call by rest dispatcher.
    public $apiNative = true;
    public $apiOAuth = false;
    public $DEBUG_MODE = false;
    public $useProxy = false;

    public function initOAuthClient()
    {
        $endpoint = trim($GLOBALS['fhir_base_url']); // default
        $endpoint = substr($endpoint, -1) == '/' ? $endpoint : $endpoint . '/auth';
        $this->auth_options = [
            'base_uri' => $endpoint,
            'http_errors' => false,
            'verify' => false,
        ];
        // auth endpoint debug
        if ($this->DEBUG_MODE) {
            $this->usingAuthHeaders(['Cookie' => 'XDEBUG_SESSION=PHPSTORM']);
            if ($this->useProxy) {
                $this->setAuthOptions(['proxy' => 'localhost:5000']);
            }
        }
        // maintain an oauth client. oeHttpRequest has it's own static client
        // whereas oeHttpRequest is an instance of guzzle request.
        //
        $this->auth_client = new Client($this->auth_options);

        // Use doctrine and php file cache to persist a safe token storage.
        //
        $this->httpCache = new PhpFileCache($GLOBALS['temporary_files_dir'], '');
        static::$token_storage = new DoctrineCacheTokenPersistence($this->httpCache);

        static::$grant_type = new PasswordCredentials($this->auth_client, $this->auth_config);
        static::$oauth = new OAuth2Middleware(static::$grant_type);
        static::$oauth->setTokenPersistence(static::$token_storage);

        // Setup stack for later adding the oauth handler to http client.
        // Implemented late in request send.
        //
        static::$stack = HandlerStack::create();
        static::$stack->push(static::$oauth);

        // Set some flags to ensure we implement this grant.
        $this->apiNative = false;
        $this->apiOAuth = true;
    }

    public function withCredentials($username, $password, $client_id)
    {
        if (empty($client_id)) {
            $client_id = $_SESSION['site_id'];
        }
        $options = [
            "client_id" => $client_id,
            "username" => $username,
            "password" => $password
        ];
        return $this->tap($this, function ($request) use ($options) {
            $this->auth_config = array_merge($this->auth_config, $options);
            return $this->initOAuthClient();
        });
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
        return oeHttpRequest::new(static::client())->{$method}(...$args);
    }

    public static function client()
    {
        return static::$client ?: static::$client = new Client;
    }
}

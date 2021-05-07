<?php

/**
 * Http Rest Requests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Http;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use kamermans\OAuth2\GrantType\AuthorizationCode;
use kamermans\OAuth2\GrantType\RefreshToken;
use kamermans\OAuth2\OAuth2Middleware;
use kamermans\OAuth2\Persistence\FileTokenPersistence;

/**
 * Class oeOAuth
 *
 * @package OpenEMR\Common\Http
 */
class oeOAuth
{
    public $token_storage;
    public $apiOAuth = false;
    public $DEBUG_MODE = false;
    public $useProxy = false;
    protected $auth_client;
    protected $auth_config = [];
    protected $auth_options = [];
    protected $token_config = [];
    protected $password_config = [];
    protected $auth_code;
    protected $redirect_uri;
    protected $httpCache;
    protected $grant_type;
    protected $refresh_grant_type;
    protected $oauth;
    protected $stack;

    public function __construct()
    {
        // for refresh/accecc token client.
        $this->auth_options = [
            'base_uri' => '',
            'http_errors' => false,
            'verify' => false,
        ];
        $this->password_config = [
            "username" => "dummy",
            "password" => "dummy",
            "client_id" => "",
            "user_role" => "",
            "scope" => ""
        ];
        $this->token_config = [
            'code' => '',
            'client_id' => '',
            'client_secret' => '',
            'redirect_uri' => '',
        ];
        $this->auth_config = [
            'client_id' => '',
            'redirect_uri' => '',
            'response_type' => 'code',
            'scope' => '',
            'state' => '',
            'prompt' => 'select_account',
        ];
    }

    public function initOAuthClient(): void
    {
        $this->apiOAuth = true;

        // auth endpoint debug
        if ($this->DEBUG_MODE) {
            $this->usingAuthHeaders(['Cookie' => 'XDEBUG_SESSION=PHPSTORM']);
            if ($this->useProxy) {
                $this->setAuthOptions(['proxy' => 'localhost:' . 8888]);
            }
        }
        /*
         * Maintain an oauth client. oeOAuth has it's own guzzle client to manage tokens
         * whereas a static oeHttp client instantiates an oeHttpRequest which wraps a guzzle request.
         */
        $this->auth_client = new Client($this->auth_options);

        /* Use php file to persist a safe token storage.
         *  Uniqueness is by client_id and logged in username.
         */
        $token_path = $GLOBALS['OE_SITE_DIR'] . '/documents/logs_and_misc/methods/' .
            $this->token_config["client_id"] . '_cache_' . $_SESSION['authUser'];
        // init cache
        $this->token_storage = new FileTokenPersistence($token_path);
        // Test if valid token. If not then do the flows consent and get code.
        if (!$this->isCachedTokenValid($token_path) && empty($this->token_config["code"])) {
            $auth_url = $this->auth_options["authorize_uri"] . "?" .
                http_build_query([
                    'client_id' => $this->token_config["client_id"],
                    'redirect_uri' => $this->token_config["redirect_uri"],
                    'response_type' => 'code',
                    'scope' => $this->token_config["scope"],
                    'state' => $this->token_config["state"],
                    'prompt' => 'select_account',
                ]);
            // redirect for new token consent.
            header("Location: $auth_url");
            exit;
        }
        // Else continue flow setup.
        $this->grant_type = new AuthorizationCode($this->auth_client, $this->token_config);
        $this->refresh_grant_type = new RefreshToken($this->auth_client, $this->token_config);

        $this->oauth = new OAuth2Middleware($this->grant_type, $this->refresh_grant_type);
        // token is not saved here.
        $this->oauth->setTokenPersistence($this->token_storage);

        // Setup stack for later adding the oauth handler to http client.
        // Implemented late in request send.
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

    protected function tap($value, $callback)
    {
        $callback($value);
        return $value;
    }

    public function setAuthOptions($options)
    {
        return $this->tap($this, function ($request) use ($options) {
            return $this->auth_options = array_merge_recursive($this->auth_options, $options);
        });
    }

    private function isCachedTokenValid($file): bool
    {
        if ($this->token_storage->hasToken()) {
            $token = @json_decode(@file_get_contents($file), true);
            if (empty($token['access_token'])) {
                // somehow corrupt token
                $this->token_storage->deleteToken();
                return false;
            }
        }

        return true;
    }

    public function usingAuthEndpoint($baseUri)
    {
        return $this->tap($this, function ($request) use ($baseUri) {
            return $this->auth_options = array_merge($this->auth_options, [
                'base_uri' => $baseUri,
            ]);
        });
    }

    public function setAuthBase($uri)
    {
        return $this->tap($this, function ($request) use ($uri) {
            return $this->auth_options = array_merge($this->auth_options, [
                'base_uri' => $uri,
            ]);
        });
    }

    public function setRedirect($uri)
    {
        return $this->tap($this, function ($request) use ($uri) {
            return $this->token_config = array_merge($this->token_config, [
                'redirect_uri' => $uri,
            ]);
        });
    }
}

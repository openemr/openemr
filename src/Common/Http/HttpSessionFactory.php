<?php

namespace OpenEMR\Common\Http;

use OpenEMR\Common\Session\SessionUtil;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionFactory;
use Symfony\Component\HttpFoundation\Session\SessionFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorageFactory;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorageFactory;

class HttpSessionFactory implements SessionFactoryInterface
{
    public const SESSION_TYPE_OAUTH = 'oauth';
    public const SESSION_TYPE_API = 'api';

    public const SESSION_TYPE_CORE = 'core';
    public const DEFAULT_SESSION_TYPE = self::SESSION_TYPE_OAUTH;

    /**
     * @var string The type of session to create.
     */
    private string $sessionType;

    private HttpRestRequest $request;

    private string $web_root;

    private bool $readOnly;

    private bool $useBridge = false;

    public function __construct(HttpRestRequest $request, string $web_root = "", $sessionType = self::DEFAULT_SESSION_TYPE, bool $readOnly = false)
    {
        $this->readOnly = $readOnly;
        $this->web_root = $web_root;
        $this->request = $request;
        if (!in_array($sessionType, [self::SESSION_TYPE_OAUTH, self::SESSION_TYPE_API, self::SESSION_TYPE_CORE])) {
            throw new \InvalidArgumentException("Invalid session type: $sessionType");
        }
        $this->sessionType = $sessionType;
    }

    /**
     * Set whether to use an existing session bridge where a session already exists and was created in globals.php.
     * @param bool $useBridge
     * @return void
     */
    public function setUseExistingSessionBridge(bool $useBridge): void
    {
        $this->useBridge = $useBridge;
    }
    public function createSession(): SessionInterface
    {
        $sessionKey = SessionUtil::CORE_SESSION_ID;
        $settings = [];
        if ($this->sessionType == self::SESSION_TYPE_OAUTH) {
            $sessionKey = SessionUtil::OAUTH_SESSION_ID;
            $settings = [
                'cookie_samesite' => "None",
                'cookie_secure' => true,
                'name' => SessionUtil::OAUTH_SESSION_ID,
                'cookie_httponly' => true,
                'cookie_path' => ((!empty($this->web_root)) ? $this->web_root . SessionUtil::OAUTH_WEBROOT : SessionUtil::OAUTH_WEBROOT),
                'gc_maxlifetime' => 14400, // 4 hours
                'use_strict_mode' => true,
                'use_cookies' => true,
                'use_only_cookies' => true
            ];
        } else if ($this->sessionType == self::SESSION_TYPE_API) {
            $settings = [
                'cookie_samesite' => "Strict",
                'cookie_secure' => true,
                'name' => SessionUtil::API_SESSION_ID,
                'cookie_httponly' => true,
                'cookie_path' => ((!empty($this->web_root)) ? $this->web_root . SessionUtil::API_WEBROOT : SessionUtil::API_WEBROOT),
                'gc_maxlifetime' => 14400, // 4 hours
                'use_strict_mode' => true,
                'use_cookies' => true,
                'use_only_cookies' => true
            ];
        } else if ($this->sessionType == self::SESSION_TYPE_CORE) {
            $sessionKey = SessionUtil::CORE_SESSION_ID;
            $settings = [
                'read_and_close' => $this->readOnly,
                'cookie_samesite' => "Strict",
                'cookie_secure' => false,
                'name' => SessionUtil::CORE_SESSION_ID,
                'cookie_httponly' => false,
                'cookie_path' => ((!empty($this->web_root)) ? $this->web_root . '/' : '/'),
                'gc_maxlifetime' => 14400, // 4 hours
                'use_strict_mode' => true,
                'use_cookies' => true,
                'use_only_cookies' => true
            ];
        }

        // PHP 8.4 and higher does not support sid_bits_per_character and sid_length
        // (ie. will remove below code block when PHP 8.4 is the minimum requirement)
        if (version_compare(phpversion(), '8.4.0', '<')) {
            // Code to run on PHP < 8.4
            $settings = array_merge([
                'sid_bits_per_character' => 6,
                'sid_length' => 48
            ], $settings);
        }
        if ($this->useBridge) {
            // Use the existing session bridge if it exists
            $sessionStorageFactory = new PhpBridgeSessionStorageFactory();
        } else {
            $sessionStorageFactory = new NativeSessionStorageFactory($settings);
        }
        $storage = $sessionStorageFactory->createStorage($this->request);
        $session = new Session($storage, new AttributeBag($sessionKey));
        $session->start();
        $this->populateSessionFromGlobals($session);
        return $session;
    }

    private function populateSessionFromGlobals(SessionInterface $session): void
    {
        // Populate session from global $_SESSION if it exists
        // we don't right now support multiple session bags so we can handle this backwards compatibility
        // while we migrate the sessions to testable objects.
        if (!empty($_SESSION)) {
            foreach ($_SESSION as $key => $value) {
                if ($key !== $session->getName()) { // Avoid overwriting session name
                    $session->set($key, $value);
                }
            }
        }
    }
}

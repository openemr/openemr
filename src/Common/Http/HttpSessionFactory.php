<?php

namespace OpenEMR\Common\Http;

use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Common\Session\Predis\SentinelUtil;
use OpenEMR\Common\Session\SessionConfigurationBuilder;
use OpenEMR\Common\Session\SessionUtil;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorageFactory;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorageFactory;

class HttpSessionFactory implements SessionFactoryInterface
{
    use SystemLoggerAwareTrait;

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
        $settings = $this->getSessionSettings();
        $sessionKey = $this->getSessionKey();
        $sessionHandler = $this->getSessionHandlerInterface($settings);
        if ($this->useBridge) {
            // Use the existing session bridge if it exists
            $sessionStorageFactory = new PhpBridgeSessionStorageFactory($sessionHandler);
        } else {
            $sessionStorageFactory = new NativeSessionStorageFactory($settings, $sessionHandler);
        }
        $storage = $sessionStorageFactory->createStorage($this->request);
        $session = new Session($storage, new AttributeBag($sessionKey));
        $session->start();
        $this->populateSessionFromGlobals($session);
        return $session;
    }
    private function getSessionSettings(): array
    {
        switch ($this->sessionType) {
            case self::SESSION_TYPE_OAUTH:
                return SessionConfigurationBuilder::forOAuth($this->web_root);

            case self::SESSION_TYPE_API:
                return SessionConfigurationBuilder::forApi($this->web_root);

            case self::SESSION_TYPE_CORE:
                return SessionConfigurationBuilder::forCore($this->web_root, $this->readOnly);

            default:
                throw new \InvalidArgumentException("Unknown session type: {$this->sessionType}");
        }
    }

    private function getSessionKey(): string
    {
        switch ($this->sessionType) {
            case self::SESSION_TYPE_OAUTH:
                return SessionUtil::OAUTH_SESSION_ID;
            case self::SESSION_TYPE_API:
                return SessionUtil::API_SESSION_ID;
            case self::SESSION_TYPE_CORE:
            default:
                return SessionUtil::CORE_SESSION_ID;
        }
    }
    private function getSessionHandlerInterface(array $settings): ?\SessionHandlerInterface
    {
        $sessionHandler = null;
        if (!empty(getenv('SESSION_STORAGE_MODE', true))  && getenv('SESSION_STORAGE_MODE', true) === "predis-sentinel") {
            $this->getSystemLogger()->debug("SessionUtil: using predis sentinel session storage mode");
            try {
                $sessionHandler = (new SentinelUtil($settings['gc_maxlifetime']))->configure();
            } catch (\Exception $e) {
                // we want to log the error and throw a runtime exception, since we don't want to fail silently when sessions are not working
                $this->getSystemLogger()->error(
                    "SessionUtil: failed to configure predis sentinel session storage: " . $e->getMessage(),
                    ['trace' => $e->getTraceAsString()]
                );
                throw new \RuntimeException("Failed to configure predis sentinel session storage: " . $e->getMessage());
            }
        }
        return $sessionHandler;
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

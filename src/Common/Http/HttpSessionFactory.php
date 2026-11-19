<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Http;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Session\Predis\SentinelUtil;
use OpenEMR\Common\Session\SessionConfiguration;
use OpenEMR\Common\Session\SessionConfigurationBuilder;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\Storage\Handler\StoreFailureStrictSessionHandler;
use OpenEMR\Common\Session\Storage\ReadAndCloseNativeSessionStorage;
use OpenEMR\Common\Session\WriteThroughSession;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\SessionFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

class HttpSessionFactory implements SessionFactoryInterface
{
    public const SESSION_TYPE_OAUTH = 'oauth';
    public const SESSION_TYPE_API = 'api';

    public const SESSION_TYPE_CORE = 'core';
    public const SESSION_TYPE_PORTAL = 'portal';
    public const DEFAULT_SESSION_TYPE = self::SESSION_TYPE_OAUTH;

    /**
     * @var string The type of session to create.
     */
    private readonly string $sessionType;

    private ?SessionStorageInterface $lastCreatedStorage = null;

    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly HttpRestRequest $request,
        private readonly string $web_root = "",
        string $sessionType = self::DEFAULT_SESSION_TYPE,
        private readonly bool $readOnly = false,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? ServiceContainer::getLogger();
        if (!in_array($sessionType, [self::SESSION_TYPE_OAUTH, self::SESSION_TYPE_API, self::SESSION_TYPE_CORE, self::SESSION_TYPE_PORTAL])) {
            throw new \InvalidArgumentException("Invalid session type: $sessionType");
        }
        $this->sessionType = $sessionType;
    }

    public function createSession(): SessionInterface
    {
        $settings = $this->getSessionSettings();
        $sessionKey = $this->getSessionKey();
        $sessionHandler = $this->getSessionHandlerInterface($settings);

        $storage = new ReadAndCloseNativeSessionStorage($settings, $sessionHandler);
        $this->lastCreatedStorage = $storage;

        $session = new WriteThroughSession($storage, new AttributeBag($sessionKey));
        return $session;
    }

    public function getRequest(): HttpRestRequest
    {
        return $this->request;
    }

    public function getLastCreatedStorage(): ?SessionStorageInterface
    {
        return $this->lastCreatedStorage;
    }

    private function getSessionSettings(): SessionConfiguration
    {
        return match ($this->sessionType) {
            self::SESSION_TYPE_OAUTH => SessionConfigurationBuilder::forOAuth($this->web_root),
            self::SESSION_TYPE_API => SessionConfigurationBuilder::forApi($this->web_root),
            self::SESSION_TYPE_CORE => SessionConfigurationBuilder::forCore($this->web_root, $this->readOnly),
            self::SESSION_TYPE_PORTAL => SessionConfigurationBuilder::forPortal($this->web_root, $this->readOnly),
        };
    }

    private function getSessionKey(): string
    {
        return match ($this->sessionType) {
            self::SESSION_TYPE_OAUTH => SessionUtil::OAUTH_SESSION_ID,
            self::SESSION_TYPE_API => SessionUtil::API_SESSION_ID,
            self::SESSION_TYPE_PORTAL => SessionUtil::PORTAL_SESSION_ID,
            default => SessionUtil::CORE_SESSION_ID,
        };
    }

    private function getSessionHandlerInterface(SessionConfiguration $settings): \SessionHandlerInterface
    {
        if (getenv('SESSION_STORAGE_MODE', true) !== 'predis-sentinel') {
            return new StoreFailureStrictSessionHandler(new \SessionHandler());
        }

        $this->logger->debug('SessionUtil: using predis sentinel session storage mode');
        try {
            return (new SentinelUtil())->configure($settings->gcMaxLifetime);
        } catch (\Throwable $e) {
            // Log and rethrow: sessions must not fail silently
            $this->logger->error('SessionUtil: failed to configure predis sentinel session storage', ['exception' => $e]);
            throw new \RuntimeException('Failed to configure predis sentinel session storage', previous: $e);
        }
    }
}

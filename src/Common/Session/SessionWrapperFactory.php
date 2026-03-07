<?php

/**
 * SessionWrapperFactory is a singleton factory for session wrapper. Its purpose is to provide the appropriate session wrapper
 * for part of the application that is requesting. It does it based on the ` App ` cookie value, and it can distinguish between
 * the core and portal application requests. Additionally, it can distinguish if Redis is used for session storage and
 * cache the value for subsequent requests.
 * Once when the core is ported to the Symfony Session, we can remove this wrapper and use Symfony Session directly.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @author    Claude Code AI
 * @copyright Copyright (c) Milan Zivkovic
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Session;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\HttpSessionFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Core\Traits\SingletonTrait;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

class SessionWrapperFactory
{
    use SingletonTrait;

    private ?SessionInterface $activeSession = null;

    private ?SessionStorageInterface $activeStorage = null;

    private bool $readOnly = false;

    public function isSessionActive(): bool
    {
        return $this->activeSession !== null;
    }

    public function setSessionReadOnly(bool $readOnly): void
    {
        $this->readOnly = $readOnly;
    }

    public function setActiveSession(SessionInterface $session, ?SessionStorageInterface $storage = null): void
    {
        $this->activeSession = $session;
        if ($storage !== null) {
            $this->activeStorage = $storage;
        }
    }

    public function getActiveStorage(): ?SessionStorageInterface
    {
        return $this->activeStorage;
    }

    public function getActiveSession(): SessionInterface
    {
        if ($this->activeSession !== null) {
            return $this->activeSession;
        }

        $app = SessionUtil::getAppCookie();
        if ($app === SessionUtil::PORTAL_SESSION_ID) {
            return $this->getPortalSession();
        }
        return $this->getCoreSession();
    }

    public function getPortalSession(bool $reset = false): SessionInterface
    {
        if (!$this->activeSession || $reset) {
            $this->activeSession = $this->createPortalSession();
        }

        return $this->activeSession;
    }

    public function destroyPortalSession(): void
    {
        if ($this->activeSession !== null && session_status() === PHP_SESSION_ACTIVE && session_name() === SessionUtil::PORTAL_SESSION_ID) {
            $this->activeSession->invalidate();
            session_write_close();
            $this->activeSession = null;
            $this->activeStorage = null;
        }
    }

    public function destroySetupSession(): void
    {
        if ($this->activeSession !== null && session_status() === PHP_SESSION_ACTIVE && session_name() === SessionUtil::SETUP_SESSION_ID) {
            $this->activeSession->invalidate();
            session_write_close();
            $this->activeSession = null;
            $this->activeStorage = null;
        }
    }

    public function destroyCoreSession(): void
    {
        if ($this->activeSession !== null && session_status() === PHP_SESSION_ACTIVE && session_name() === SessionUtil::CORE_SESSION_ID) {
            $this->activeSession->invalidate();
            session_write_close();
            $this->activeSession = null;
            $this->activeStorage = null;
        }
    }

    public function getCoreSession(bool $reset = false): SessionInterface
    {
        if (!$this->activeSession || $reset) {
            $this->activeSession = $this->createCoreSession();
        }

        return $this->activeSession;
    }

    private function createPortalSession(): SessionInterface
    {
        $web_root = OEGlobalsBag::getInstance()->getString('web_root');
        $request = HttpRestRequest::createFromGlobals();
        if (!$request->hasSession()) {
            $sessionFactory = new HttpSessionFactory($request, $web_root, HttpSessionFactory::SESSION_TYPE_PORTAL);
            $this->activeSession = $sessionFactory->createSession();
            $this->activeStorage = $sessionFactory->getLastCreatedStorage();
        } else {
            $this->activeSession = $request->getSession();
        }
        return $this->activeSession;
    }

    private function createCoreSession(): SessionInterface
    {
        $web_root = OEGlobalsBag::getInstance()->getString('web_root');
        $request = HttpRestRequest::createFromGlobals();
        if (!$request->hasSession()) {
            $sessionFactory = new HttpSessionFactory($request, $web_root, HttpSessionFactory::SESSION_TYPE_CORE, $this->readOnly);
            $this->activeSession = $sessionFactory->createSession();
            $this->activeStorage = $sessionFactory->getLastCreatedStorage();
        } else {
            $this->activeSession = $request->getSession();
        }

        return $this->activeSession;
    }
}

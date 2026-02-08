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
 * @copyright Copyright (c) Milan Zivkovic
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Session;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\HttpSessionFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Core\Traits\SingletonTrait;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionWrapperFactory
{
    use SingletonTrait;

    private ?SessionInterface $portalSession = null;
    private ?SessionInterface $coreSession = null;

    private ?SessionInterface $activeSession = null;

    public function isSessionActive(): bool
    {
        return $this->portalSession !== null || $this->coreSession !== null;
    }

    public function getActiveSession(): SessionInterface
    {
        // TODO @zmilan: this is just for testing, see how to approach it differently
//        if ($this->isSessionActive()) {
//            return $this->activeSession;
//        }
//        TODO @zmilan: Let's hope that we do not need this
        $app = SessionUtil::getAppCookie();
        if ($app === SessionUtil::PORTAL_SESSION_ID) {
            return $this->getPortalSession();
        }
        return $this->getCoreSession();
    }

//    private function findSessionWrapper(array $initData = []): SessionWrapperInterface
//    {
//        $app = SessionUtil::getAppCookie();
//        // Use PHPSessionWrapper for non-portal requests, or if a session is already active
//        // (e.g., API/OAuth requests where SiteSetupListener has already started a Symfony session)
//        if ($app !== SessionUtil::PORTAL_SESSION_ID || session_status() === PHP_SESSION_ACTIVE) {
//            $session = new PHPSessionWrapper();
//        } else {
//            $session = new SymfonySessionWrapper(SessionUtil::portalSessionStart());
//        }
//
//        foreach ($initData as $name => $value) {
//            $session->set($name, $value);
//        }
//
//        return $session;
//    }

    public function getPortalSession(bool $reset = false): SessionInterface
    {
        if (!$this->portalSession || $reset) {
            $this->portalSession = $this->createPortalSession();
        }

        return $this->portalSession;
    }

    public function destroyPortalSession(): void
    {
        if ($this->portalSession !== null) {
            $this->portalSession->invalidate();
            if (session_status() === PHP_SESSION_ACTIVE && session_name() === SessionUtil::PORTAL_SESSION_ID) {
                session_write_close();
            }
            $this->portalSession = null;
            $this->activeSession = null; // TODO @zmilan: this can get messy a lot easily
        }
    }

    public function destroyCoreSession(): void
    {
        if ($this->coreSession !== null) {
            $this->coreSession->invalidate();
            if (session_status() === PHP_SESSION_ACTIVE && session_name() === SessionUtil::CORE_SESSION_ID) {
                session_write_close();
            }
            $this->coreSession = null;
            $this->activeSession = null;
        }
    }

    public function getCoreSession(bool $reset = false): SessionInterface
    {
        if (!$this->coreSession || $reset) {
            $this->coreSession = $this->createCoreSession();
        }

        return $this->coreSession;
    }

    private function createPortalSession(): SessionInterface
    {
        $web_root = OEGlobalsBag::getInstance()->getString('web_root');
        $request = HttpRestRequest::createFromGlobals();
        if (!$request->hasSession()) {
            $sessionFactory = new HttpSessionFactory($request, $web_root, HttpSessionFactory::SESSION_TYPE_PORTAL);
            if (session_status() === PHP_SESSION_ACTIVE) {
                $sessionFactory->setUseExistingSessionBridge(true);
            }
            $this->activeSession = $sessionFactory->createSession();
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
            $sessionFactory = new HttpSessionFactory($request, $web_root, HttpSessionFactory::SESSION_TYPE_CORE);
            if (session_status() === PHP_SESSION_ACTIVE) {
                $sessionFactory->setUseExistingSessionBridge(true);
            }
            $this->activeSession = $sessionFactory->createSession();
        } else {
            $this->activeSession = $request->getSession();
        }

        return $this->activeSession;
    }
}

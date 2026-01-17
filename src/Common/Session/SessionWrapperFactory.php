<?php

/**
 * SessionWrapperFactory is a singleton factory for session wrapper. Its purpose is to provide the appropriate session wrapper
 * for part of the application that is requesting. It does it based on the ` App ` cookie value, and it can distinguish between
 * the core and portal application requests. Additionally, it can distinguish if Redis is used for session storage and
 * cache the value for subsequent requests.
 * Once when the core is ported to the Symfony Session, we can remove this wrapper and use Symfony Session directly.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
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
        // TODO this is just for testing, see how to approach it differently
        if ($this->isSessionActive()) {
            return $this->activeSession;
        }
//        TODO Let's hope that we do not need this
//        $app = SessionUtil::getAppCookie();
//        if ($app === SessionUtil::PORTAL_SESSION_ID) {
//            return $this->createPortalSession();
//        }
        return $this->createCoreSession();
    }

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
            $this->portalSession = null;
        }
    }

    public function destroyCoreSession(): void
    {
        if ($this->coreSession !== null) {
            $this->coreSession->invalidate();
            $this->coreSession = null;
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
        $oeGlobals = OEGlobalsBag::getInstance();
        $request = HttpRestRequest::createFromGlobals();
        $sessionFactory = new HttpSessionFactory($request, $oeGlobals->getString('web_root'), HttpSessionFactory::SESSION_TYPE_PORTAL);
//        $sessionFactory->setUseExistingSessionBridge(true);
        $this->activeSession = $sessionFactory->createSession();
        return $this->activeSession;
    }

    private function createCoreSession(): SessionInterface
    {
        $oeGlobals = OEGlobalsBag::getInstance();
        $request = HttpRestRequest::createFromGlobals();
        $sessionFactory = new HttpSessionFactory($request, $oeGlobals->getString('web_root'), HttpSessionFactory::SESSION_TYPE_CORE);
//        $sessionFactory->setUseExistingSessionBridge(true);
        $this->activeSession = $sessionFactory->createSession();
        return $this->activeSession;
    }
}

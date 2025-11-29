<?php

namespace OpenEMR\Common\Session;

use Waryway\PhpTraitsLibrary\Singleton;

class SessionWrapperFactory
{
    use Singleton;

    private ?SessionWrapperInterface $sessionWrapper = null;

    public function getWrapper(): SessionWrapperInterface
    {
        if (!$this->sessionWrapper) {
            $this->sessionWrapper = $this->findSessionWrapper();
        }

        return $this->sessionWrapper;
    }

    private function findSessionWrapper(): SessionWrapperInterface
    {
        $app = SessionUtil::getAppCookie();
        if ($app !== SessionUtil::PORTAL_SESSION_ID) {
            return new PHPSessionWrapper();
        }

        if (SessionUtil::isPredisSession()) {
            SessionUtil::portalPredisSessionStart();
            return new PHPSessionWrapper();
        }

        return new SymfonySessionWrapper(SessionUtil::portalSessionStart());
    }

}

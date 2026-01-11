<?php

namespace OpenEMR\Common\Session;

use OpenEMR\Core\Traits\SingletonTrait;

class SessionWrapperFactory
{
    use SingletonTrait;

    private ?SessionWrapperInterface $sessionWrapper = null;

    public function getWrapper(array $initData = []): SessionWrapperInterface
    {
        if (!$this->sessionWrapper) {
            $this->sessionWrapper = $this->findSessionWrapper($initData);
        }

        return $this->sessionWrapper;
    }

    private function findSessionWrapper(array $initData = []): SessionWrapperInterface
    {
        $app = SessionUtil::getAppCookie();
        if ($app !== SessionUtil::PORTAL_SESSION_ID) {
            $session = new PHPSessionWrapper();
        } else if (SessionUtil::isPredisSession()) {
            SessionUtil::portalPredisSessionStart();
            $session = new PHPSessionWrapper();
        } else {
            $session = new SymfonySessionWrapper(SessionUtil::portalSessionStart());
        }

        foreach ($initData as $name => $value) {
            $session->set($name, $value);
        }

        return $session;
    }

}

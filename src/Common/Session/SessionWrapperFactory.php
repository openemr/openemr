<?php

namespace OpenEMR\Common\Session;


class SessionWrapperFactory
{
    private static ?SessionWrapperFactory $instance = null;

    private ?SessionWrapperInterface $sessionWrapper = null;

    private function __construct()
    {

    }
    public static function createSessionWrapper(): SessionWrapperInterface
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance->getSessionWrapper();
    }

    public function getSessionWrapper(): SessionWrapperInterface
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

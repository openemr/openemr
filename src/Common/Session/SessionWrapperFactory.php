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

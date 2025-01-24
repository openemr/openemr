<?php

/**
 * OpenEMR <https://open-emr.org>.
 * Code written by ChatGPTo4 <https://www.chatgpt.com/>
 * Code distributed under the GNU General Public License (GPL).
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Tabs;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TabIdentifierService
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;

        if (!$this->session->isStarted()) {
            $this->session->start();
        }
    }

    public function generateTabIdentifier(): string
    {
        $tabIdentifier = bin2hex(random_bytes(16));
        $this->session->set('tabContexts.' . $tabIdentifier, []);
        return $tabIdentifier;
    }

    public function getTabData(string $tabIdentifier): ?array
    {
        return $this->session->get('tabContexts.' . $tabIdentifier, null);
    }

    public function setTabData(string $tabIdentifier, array $data): void
    {
        $this->session->set('tabContexts.' . $tabIdentifier, $data);
    }
}

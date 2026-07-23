<?php

/**
 * Module bootstrap for the External Identity Provider module.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ExternalIdp;

use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Core\ExternalAuthenticationProviderEvent;
use OpenEMR\Modules\ExternalIdp\Repository\ProviderRepository;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class Bootstrap
{
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public function subscribeToEvents(): void
    {
        $this->eventDispatcher->addListener(
            ExternalAuthenticationProviderEvent::EVENT_NAME,
            $this->addLoginProvider(...),
        );
    }

    public function addLoginProvider(ExternalAuthenticationProviderEvent $event): void
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $siteId = (string) ($session->get('site_id') ?: 'default');
        $provider = (new ProviderRepository())->getEnabledForSite($siteId);
        if (empty($provider) || empty($provider['id']) || empty($provider['display_name'])) {
            return;
        }

        $startUrl = OEGlobalsBag::getInstance()->getWebRoot() . '/interface/modules/custom_modules/oe-module-external-idp/start.php?provider_id=' . rawurlencode((string) $provider['id']);
        $event->addProvider((string) $provider['id'], (string) $provider['display_name'], $startUrl);
    }
}

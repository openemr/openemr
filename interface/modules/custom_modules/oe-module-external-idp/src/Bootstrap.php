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
use OpenEMR\Menu\MenuEvent;
use OpenEMR\Modules\ExternalIdp\Repository\ProviderRepository;
use stdClass;
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
        $this->eventDispatcher->addListener(
            MenuEvent::MENU_UPDATE,
            $this->addAdminMenuItem(...),
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

    public function addAdminMenuItem(MenuEvent $event): MenuEvent
    {
        $menu = $event->getMenu();

        $menuItem = new stdClass();
        $menuItem->requirement = 0;
        $menuItem->target = 'adm0';
        $menuItem->menu_id = 'external_idp_config';
        $menuItem->label = xlt('External Identity Provider');
        $menuItem->url = '/interface/modules/custom_modules/oe-module-external-idp/moduleConfig.php';
        $menuItem->children = [];
        $menuItem->acl_req = ['admin', 'super'];
        $menuItem->global_req = [];

        foreach ($menu as $item) {
            if (is_object($item) && (($item->menu_id ?? '') === 'admimg' || ($item->label ?? '') === 'Admin')) {
                if (!isset($item->children) || !is_array($item->children)) {
                    $item->children = [];
                }
                $item->children[] = $menuItem;
                break;
            }
        }

        $event->setMenu($menu);
        return $event;
    }
}

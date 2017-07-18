<?php
/**
 * Created by PhpStorm.
 * User: rdown
 * Date: 2017-07-12
 * Time: 23:44
 */

namespace OpenEMR\Calendar\EventListener;

use OpenEMR\Admin\AdminEvents;
use OpenEMR\Admin\Event\MenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CalendarSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            AdminEvents::BUILD_MAIN_MENU => 'onAdminMenuBuild',
        ];
    }

    public function onAdminMenuBuild(MenuEvent $event)
    {
        error_log('onAdminMenuBuild was just executed');
        $event->addMenuItem('Calendar', 'value-of-link.html');
        return $event;
    }
}

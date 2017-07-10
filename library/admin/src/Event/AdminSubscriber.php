<?php
/**
 * Created by PhpStorm.
 * User: rdown
 * Date: 2017-07-09
 * Time: 22:49
 */

namespace OpenEMR\Admin\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use OpenEMR\Core\Event\HeaderLoadedEvent;

class AdminSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            HeaderLoadedEvent::NAME => 'onHeaderLoad',
        ];
    }

    public function onHeaderLoad(Event $e)
    {
        die('Event dispatched');
    }
}
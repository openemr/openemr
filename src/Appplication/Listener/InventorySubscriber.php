<?php
/*
 *   @package   OpenEMR
 *   @link      http://www.open-emr.org
 *   @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   @copyright Copyright (c )2021. Sherwin Gaddis <sherwingaddis@gmail.com>
 *   @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Application\Listener;

use OpenEMR\Events\Inventory\InventoryChangeEvent;
use Symfony\Component\EventSubscriber\EventSubscriberInterface;

class InventorySubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents(): array
    {
        return [
            InventoryChangeEvent::INVENTORY_DECREMENT => 'onDispensingItem',
            InventoryChangeEvent::INVENTORY_INCREMENT => 'onInventoryAdd'

        ];
    }

    public function onDispensingItem(InventoryChangeEvent $event)
    {
        echo "decreased inventory"; var_dump($event);
    }

    public function onInventoryAdd(InventoryChangeEvent $event)
    {
        echo "increased inventory"; var_dump($event);
    }

}
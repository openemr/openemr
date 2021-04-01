<?php
/*
 *   @package   OpenEMR
 *   @link      http://www.open-emr.org
 *   @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   @copyright Copyright (c )2021. Sherwin Gaddis <sherwingaddis@gmail.com>
 *   @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Application\Listener;

use OpenEMR\Inventory\InventoryDecrement;
use OpenEMR\Events\Inventory\InventoryDecrementEvent;

class InventorySubscriber implements EventSubscriberInterface
{
    public function __construct()
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            InventoryDecrementEvent::INVENTORY_UPDATE => 'onDispensingItem'
        ];
    }

    public function onDispensingItem(InventoryDecrementEvent $decreaseInventoriedItem)
    {
        $inventoryItem = $decreaseInventoriedItem->getInventory();
        $changeInventoryAmount = new InventoryDecrement();
        $changeInventoryAmount->decrementInventory($inventoryItem);

    }
}
<?php

/*
 *   @package   OpenEMR
 *   @link      http://www.open-emr.org
 *   @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   @copyright Copyright (c )2021. Sherwin Gaddis <sherwingaddis@gmail.com>
 *   @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Inventory;

use OpenEMR\Inventory\InventoryDecrement;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event object for decrementing inventory quantity on hand
 *
 * Class InventoryDecrementEvent
 * @package OpenEMR\Events\Inventory
 *
 */
class InventoryDecrementEvent extends Event
{
    /**
     * @var int
     */
    protected $itemToinventory;

    const INVENTORY_UPDATE = 'inventory.decrement';

    public function __construct(InventoryDecrement $inventory)
    {
        $this->itemToinventory = $inventory;
    }

    public function getInventory(): InventoryDecrement
    {
        return $this->itemToinventory;
    }
}
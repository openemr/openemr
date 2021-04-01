<?php
/*
 *   @package   OpenEMR
 *   @link      http://www.open-emr.org
 *   @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   @copyright Copyright (c )2021. Sherwin Gaddis <sherwingaddis@gmail.com>
 *   @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Inventory;


/**
 * Class InventoryDecrement
 * @package OpenEMR\Inventory
 */
class InventoryDecrement
{
    /**
     * @var void
     */
    private $currentCount;
    private $item;


    /**
     * InventoryDecrement constructor.
     */
    public function __construct()
    {
        $this->currentCount = $this->getInventoryOnHandCount();
    }

    /**
     * @param $inventoryItem
     */
    public function decrementInventory($inventoryItem)
    {
        $this->item = $inventoryItem;
        $this->newCount = $this->currentCount - 1;
        $this->updateOhHandInventory();
    }

    /**
     * get onhand count or item
     *
     */
    public function getInventoryOnHandCount()
    {
        $sql = "SELECT on_hand FROM drug_inventory WHERE lot_number = ?, [$this->item]";
        sqlQuery($sql);

    }

    public function updateOhHandInventory()
    {
        $sql = "UPDATE drug_inventory SET on_hand = ? WHERE lot_number = ?, [$this->newCount]";
        sqlStatement($sql);
    }
}

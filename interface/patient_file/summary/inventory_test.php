<?php

/*
 *   @package   OpenEMR
 *   @link      http://www.open-emr.org
 *   @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   @copyright Copyright (c )2021. Sherwin Gaddis <sherwingaddis@gmail.com>
 *   @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../../globals.php");

use OpenEMR\Events\Inventory\InventoryChangeEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Event\GenericEvent;

if ($GLOBALS['inhouse_pharmacy'] > 0) {
    $decreaseInventoriedItem = new InventoryChangeEvent();
    $decreaseInventoriedItem->itemToinventory = 8965742;
    $dispatcher = $GLOBALS['kernel']->getEventDispatcher();
    $thend = $dispatcher->dispatch(InventoryChangeEvent::INVENTORY_DECREMENT, $decreaseInventoriedItem, new GenericEvent());
}

echo "Dispatched!";
var_dump($thend);
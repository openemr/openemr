<?php

/*
 *   @package   OpenEMR
 *   @link      http://www.open-emr.org
 *   @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   @copyright Copyright (c )2021. Sherwin Gaddis <sherwingaddis@gmail.com>
 *   @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Application\Listener\InventorySubscriber;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Event;


/**
 * @var EventDispatcherInterface $eventDispatcher
 * Register subscribers to inventory events such as increase or decrease inventory
 */

$subscriber = new InventorySubscriber();
$eventDispatcher->addSubscriber($subscriber);

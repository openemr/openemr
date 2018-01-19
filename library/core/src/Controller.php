<?php
/**
 * Created by PhpStorm.
 * User: rdown
 * Date: 2017-07-12
 * Time: 22:05
 */

namespace OpenEMR\Core;

require_once("../../../../interface/globals.php");

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Controller
{

    /** @var Container */
    public $container;

    /** @var EventDispatcher */
    public $eventDispatcher;

    public function __construct()
    {
        $kernel = $GLOBALS['kernel'];
        /** @var Container $container */
        $container = $kernel->getContainer();
        $this->container = $container;
        $this->eventDispatcher = $container->get('event_dispatcher');
    }
}

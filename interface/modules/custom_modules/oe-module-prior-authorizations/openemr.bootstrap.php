<?php

/*
 *
 * @package      OpenEMR
 * @link               https://www.open-emr.org
 *
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2021 Sherwin Gaddis <sherwingaddis@gmail.com>
 * All Rights Reserved
 *
 */

/**
 * @global OpenEMR\Core\ModulesClassLoader $classLoader
 */

use Symfony\Component\EventDispatcher\EventDispatcher;
use Juggernaut\OpenEMR\Modules\PriorAuthModule\Bootstrap;

$classLoader->registerNamespaceIfNotExists('Juggernaut\\OpenEMR\Modules\\PriorAuthModule\\', __DIR__ . DIRECTORY_SEPARATOR . 'src');

/**
 * @global EventDispatcher $eventDispatcher Injected by the OpenEMR module loader;
 */
$bootstrap = new Bootstrap($eventDispatcher);
$bootstrap->subscribeToEvents();



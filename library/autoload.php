<?php
/**
* OpenEMR Autoloder
*
* Load up modules using spl_autoloader. This negates the need to use require
* statements everywhere and manage paths. 
* See /library/AutoLoader/src/Psr4Autoloader.php for more info
* 
* Copyright (C) 2015 Robert Down <robertdown@live.com>
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/


require_once $GLOBALS['srcdir'] . '/AutoLoader/src/Psr4Autoloader.php';

use OpenEMR\AutoLoader;

$loader = new \OpenEMR\AutoLoader\Psr4Autoloader;
$loader->register();

// Add the module name here (this is the folder directly below /library/)
// These modules have the vendor name OpenEMR
// @TODO Move this array to a config file (top level openemr.config.php maybe or globals?)
//       That would further separate config from logic, which is good
$internalModules = array(
    'Patient',
    'ViewHelper',
);

// Load all the internal modules defined above
// Time-saver by only having to throw the names of the module into an array
// and we do the auto-loading here. Inspired by the ZF2 Module management, but
// to say this is even a watered-down variation of that would not be suffice
foreach ($internalModules as $module)
{
    $namespace = 'OpenEMR\\' . $module;
    $path = $GLOBALS['srcdir'] . '/' . $module . '/src';
    $loader->addNamespace($namespace, $path);
}

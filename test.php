<?php

require_once 'interface/globals.php';

use OpenEMR\Core\Kernel;

$k = new Kernel();

var_dump($k->getContainer()->get('main_menu_role'));

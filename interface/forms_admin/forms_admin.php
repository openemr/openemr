<?php

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.




//INCLUDES, DO ANY ACTIONS, THEN GET OUR DATA
require_once("../globals.php");

use OpenEMR\Controllers\Forms\FormAdminController;

$controller = new FormAdminController();

if (!$controller->checkAccess()) {
    exit;
}

if ($controller->hasAction()) {
    $error = $controller->handleAction();
}
$controller->render("/interface/forms_admin/forms_admin.php", $error);

exit;

<?php
/**
 * Paytrace Gateway Member
 * link    http://www.open-emr.org
 * author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * Copyright (c) 2020. Sherwin Gaddis <sherwingaddis@gmail.com>
 * license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../../globals.php");

use OpenEMR\Modules\PaytraceGateway\Controllers\IndexController;

$storecard = new IndexController();

echo $storecard->registerCard();

echo "Do more stuff here";




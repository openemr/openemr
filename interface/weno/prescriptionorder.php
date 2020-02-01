<?php
/**
 * Prescription Order Screen
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2019 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
require_once "../globals.php";

use OpenEMR\Rx\Weno\NarcoticRxController;

$page = new NarcoticRxController();


echo $page->buildRx();


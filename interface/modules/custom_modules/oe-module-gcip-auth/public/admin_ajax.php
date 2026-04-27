<?php

/**
 * AJAX handler for GCIP module admin configuration.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Modules\GcipAuth\Controller\AdminController;

$controller = new AdminController();
$controller->handleAjax();

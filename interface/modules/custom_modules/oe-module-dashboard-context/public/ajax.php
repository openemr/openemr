<?php

/**
 * Dashboard Context Module - User AJAX Handler
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Adjust path based on module location
require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Modules\DashboardContext\Controller\UserContextController;

$controller = new UserContextController();
$controller->handleRequest();

<?php

/*
 *  package OpenEMR
 *  link    https://www.open-emr.org
 *  author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  Copyright (c) 2022-2024.
 *  All Rights Reserved
 */

require_once dirname(__FILE__, 6) . "/globals.php";

use Juggernaut\OpenEMR\Modules\PriorAuthModule\Controller\ReportsController;

// Create controller instance
$controller = new ReportsController();

// Render the list report view
try {
    echo $controller->listAction();
} catch (Exception $e) {
    error_log("Error rendering list report: " . $e->getMessage());
    // Fallback to simple error message
    echo "<!DOCTYPE html><html><head><title>Error</title></head><body>";
    echo "<div class='alert alert-danger'>";
    echo "An error occurred while loading the authorization report. Please try again later.";
    echo "</div></body></html>";
}

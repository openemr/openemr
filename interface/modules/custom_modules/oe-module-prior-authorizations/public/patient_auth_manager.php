<?php

/*
 *  package OpenEMR
 *  link    https://www.open-emr.org
 *  author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  Copyright (c) 2022-2024.
 *  All Rights Reserved
 */

require_once dirname(__FILE__, 5) . "/globals.php";

use Juggernaut\OpenEMR\Modules\PriorAuthModule\Controller\PatientAuthManagerController;
use Juggernaut\OpenEMR\Modules\PriorAuthModule\Controller\ListAuthorizations;

// Initialize auto-population of missing auths from forms
$listData = new ListAuthorizations();
$listData->insertMissingAuthsFromForm();

// Create controller instance
$controller = new PatientAuthManagerController();

// Process form submission if present
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->processForm();
    // Redirect to prevent duplicate submission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Render the view
try {
    echo $controller->view();
} catch (Exception $e) {
    error_log("Error rendering patient auth manager view: " . $e->getMessage());

    // Fallback to simple error message
    echo "<!DOCTYPE html><html><head><title>Error</title></head><body>";
    echo "<div class='alert alert-danger'>";
    echo "An error occurred while loading the authorization manager. Please try again later.";
    echo "</div></body></html>";
}

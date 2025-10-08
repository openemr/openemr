<?php

/*
 *  package OpenEMR
 *  link    https://www.open-emr.org
 *  author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  Copyright (c) 2022-2024.
 *  All Rights Reserved
 *  license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once dirname(__FILE__, 5) . '/globals.php';

use Juggernaut\OpenEMR\Modules\PriorAuthModule\Controller\DeleteController;

// Create controller instance
$controller = new DeleteController();

// Execute delete action and render response
try {
    echo $controller->deleteAction();
} catch (Exception $e) {
    error_log("Error in deleter.php: " . $e->getMessage());
    
    // Fallback error response for modal
    echo "<!DOCTYPE html><html><head><title>" . xlt('Error') . "</title></head><body>";
    echo "<div class='container-fluid text-center p-4'>";
    echo "<div class='alert alert-danger'>";
    echo "<i class='fas fa-exclamation-triangle fa-2x mb-3'></i>";
    echo "<h5>" . xlt('System Error') . "</h5>";
    echo "<p>" . xlt('An unexpected system error occurred. Please try again later.') . "</p>";
    echo "<p class='mt-3'><small class='text-muted'>" . xlt('Please click "Done" to close this window.') . "</small></p>";
    echo "</div></div></body></html>";
}

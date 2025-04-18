<?php

/**
 * ProductRegistrationController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @license     https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Services\ProductRegistrationService;
use OpenEMR\Services\VersionService;
use OpenEMR\Telemetry\BackgroundTaskManager;

require_once("../../interface/globals.php");

header("Content-Type: application/json");

// Determine request method
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Retrieve current registration status
    $sql = "SELECT * FROM product_registration LIMIT 1";
    $row = sqlQuery($sql);
    if ($row && $row['telemetry_disabled'] !== null) {
        echo json_encode($row); // Both registration and telemetry answered
    } else {
        echo json_encode(["statusAsString" => "UNREGISTERED"]);
    }
    exit;
}

// Process form submission
if ($method === 'POST') {
    // Retrieve email; if empty or "false", treat as opt-out.
    $opt_out = 0;
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    if (empty($email)) {
        $opt_out = 1;
    }
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid email"]);
        exit;
    }
    if (!empty($email)) {
        // send to product registration service
        $response = [];
        $productRegistrationService = new ProductRegistrationService();
        $registrationEmail = $productRegistrationService->registerProduct($email);
        $response['email'] = $registrationEmail;
        $status = 201;
    }
    // Check for telemetry opt-out
    $telemetry_disabled = 1;
    $selected_options = [];
    // Check for each checkbox input; expected values are 1 if checked.
    // Leaving open the opportunity to add new checkboxes/options.
    if ($_POST['allow_telemetry'] ?? null == 1) {
        $telemetry_disabled = 0;
        $selected_options[] = 'allow_telemetry';
    }
    $options = json_encode($selected_options);
    $auth_by_id = $_SESSION['authUserID'] ?? null;

    // Update the last ask date and version
    $last_ask_date = date("Y-m-d H:i:s");

    $versionService = new VersionService();
    $last_ask_version = $versionService->asString();

    $sql = "SELECT id FROM `product_registration` WHERE id > 0 LIMIT 1";
    $res = sqlQueryNoLog($sql);
    $id = $res['id'] ?? 0;
    if ($id > 0) {
        $sql = "UPDATE `product_registration` SET `email` = ?, `opt_out` = ?, `auth_by_id` = ?, `telemetry_disabled` = ?, `last_ask_date` = ?, `last_ask_version` = ?, `options` = ? WHERE `id` = ?";
        $result = sqlStatementNoLog($sql, [
            $email,
            $opt_out,
            $auth_by_id,
            $telemetry_disabled,
            $last_ask_date,
            $last_ask_version,
            $options,
            $id
        ]);
    } else {
        // Insert or update the registration record (assuming single-row record with id = 1)
        $sql = "INSERT INTO `product_registration` (email, opt_out, auth_by_id, telemetry_disabled, last_ask_date, last_ask_version, options) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $params = [$email, $opt_out, $auth_by_id, $telemetry_disabled, $last_ask_date, $last_ask_version, $options];
        $result = sqlStatementNoLog($sql, $params);
    }

    if ($result) {
        // Update the telemetry task if telemetry is enabled
        $backgroundTaskManager = new BackgroundTaskManager();
        if ($telemetry_disabled == 0) {
            $backgroundTaskManager->modifyTelemetryTask();
            $backgroundTaskManager->enableTelemetryTask();
        } else {
            $backgroundTaskManager->deleteTelemetryTask();
        }
        echo json_encode(["success" => true, "email" => $email]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to update registration"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}
exit;

<?php

/**
 * ProductRegistrationController
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package   OpenEMR
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 *
 * @link      http://www.open-emr.org
 */

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
} elseif ($method === 'POST') {
    // Process form submission
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
    $telemetry_disabled = 1;
    $selected_options = [];
    // Check for each checkbox input; expected values are 1 if checked.
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
        $sql = "UPDATE `product_registration` SET `email` = ?, `opt_out` = ?, `auth_by_id` = ?, `telemetry_disabled` = ?, `last_ask_date` = ?, `last_ask_version` = ?, `options` = ?
            WHERE `id` = ?";
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

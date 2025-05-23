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

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\ProductRegistrationService;
use OpenEMR\Services\VersionService;
use OpenEMR\Telemetry\BackgroundTaskManager;

require_once("../../interface/globals.php");

header("Content-Type: application/json");

// Determine request method
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // retrieve if allowRegisterDialog
    $allowRegisterDialog = (new ProductRegistrationService())->getProductDialogStatus()['allowRegisterDialog'] ?? 0;
    echo json_encode(["allowRegisterDialog" => $allowRegisterDialog]);
    exit;
}

// Process form submission
if ($method === 'POST') {
    // Figure out which elements are going to be updated
    $productRegistration = (new ProductRegistrationService())->getProductDialogStatus();
    $allowEmail = $productRegistration['allowEmail'] ?? null;
    $allowTelemetry = $productRegistration['allowTelemetry'] ?? null;

    if ($allowEmail) {
        $email = trim($_POST['email'] ?? '');

        // validate email address
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            http_response_code(400);
            echo json_encode(["message" => xlt("Invalid email")]);
            exit;
        }

        // send to product registration service
        try {
            $submitRegistration = (new ProductRegistrationService())->registerProduct($email);
        } catch (GenericProductRegistrationException $e) {
            // Handle the exception
            http_response_code(400);
            echo json_encode(["message" => xlt("An internal error occurred while processing your request.")]);
            (new SystemLogger())->error(
                "Product Email Registration Error, GenericProductRegistrationException occurred",
                ['trace' => $e->getTraceAsString()]
            );
            exit;
        } catch (Exception $e) {
            // Handle any other exceptions
            http_response_code(500);
            echo json_encode(["message" => xlt("An internal error occurred while processing your request.")]);
            (new SystemLogger())->error(
                "Product Email Registration Error, Exception occurred",
                ['trace' => $e->getTraceAsString()]
            );
            exit;
        }

        if (empty($email) && !is_null($submitRegistration)) {
            http_response_code(400);
            echo json_encode(["message" => xlt("An internal error occurred while processing your request.")]);
            (new SystemLogger())->error(
                "Product Email Registration Error, error occurred on submit empty email",
                ['email' => $email, 'submitRegistration' => $submitRegistration]
            );
            exit;
        }
        if (!empty($email) && ($submitRegistration != $email)) {
            http_response_code(400);
            echo json_encode(["message" => xlt("An internal error occurred while processing your request.")]);
            (new SystemLogger())->error(
                "Product Email Registration Error, error occurred on submit '" . $email . "' email",
                ['email' => $email, 'submitRegistration' => $submitRegistration]
            );
            exit;
        }

        if (!$allowTelemetry) {
            echo json_encode(["success" => true, "email" => $email]);
            exit;
        }
    }

    if ($allowTelemetry) {
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

        // Update the registration record
        //  (note that there will always be a existent record at this point and email registration has already been dealt with, if applicable)
        $res = sqlQueryNoLog("SELECT `id` FROM `product_registration` WHERE `id` > 0 LIMIT 1");
        $id = (int)($res['id'] ?? 0);
        if ($id > 0) {
            // Otherwise, we just update the telemetry status
            $sql = "UPDATE `product_registration` SET `auth_by_id` = ?, `telemetry_disabled` = ?, `last_ask_date` = ?, `last_ask_version` = ?, `options` = ? WHERE `id` = ?";
            $params = [$auth_by_id, $telemetry_disabled, $last_ask_date, $last_ask_version, $options, $id];
        } else {
            // Error, should never happen
            http_response_code(400);
            echo json_encode(["message" => xlt("An internal error occurred while processing your request.")]);
            (new SystemLogger())->error(
                "Product Telemetry Registration Error, missing entry",
                ['id' => $id, 'auth_by_id' => $auth_by_id, 'telemetry_disabled' => $telemetry_disabled, 'last_ask_date' => $last_ask_date, 'last_ask_version' => $last_ask_version, 'options' => $options]
            );
            exit;
        }
        $result = sqlStatementNoLog($sql, $params);

        if ($result) {
            // Update the telemetry task if telemetry is enabled
            $backgroundTaskManager = new BackgroundTaskManager();
            if ($telemetry_disabled == 0) {
                $backgroundTaskManager->modifyTelemetryTask();
                $backgroundTaskManager->enableTelemetryTask();
            } else {
                $backgroundTaskManager->deleteTelemetryTask();
            }
            if ($allowEmail) {
                echo json_encode(["success" => true, "email" => $email, "telemetry_enabled" => ($telemetry_disabled == 0)]);
            } else {
                echo json_encode(["success" => true, "telemetry_enabled" => ($telemetry_disabled == 0)]);
            }
        } else {
            http_response_code(500);
            echo json_encode(["message" => xlt("Failed to update registration")]);
            (new SystemLogger())->error(
                "Product Telemetry Registration Error, failed to update registration",
                ['id' => $id, 'auth_by_id' => $auth_by_id, 'telemetry_disabled' => $telemetry_disabled, 'last_ask_date' => $last_ask_date, 'last_ask_version' => $last_ask_version, 'options' => $options]
            );
        }
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
}
exit;

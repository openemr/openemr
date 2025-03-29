<?php

require_once("../../interface/globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Uuid\UniqueInstallationUuid;
use OpenEMR\Services\VersionService;

header("Content-Type: application/json");

// Read and decode the JSON payload.
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, true);

// Verify CSRF token.
if (!CsrfUtils::verifyCsrfToken($data["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
    exit;
}
if ($data['action'] === 'reportMenuClickData') {
    reportClicks();
    // $result = reportUsageData(); // todo: remove this line. for testing until background task is implemented.
} elseif ($data['action'] === 'reportUsageData') {
    $result = reportUsageData();
    echo json_encode($result);
} else {
    http_response_code(400);
    echo json_encode(array("error" => "Invalid action"));
}

function reportClicks()
{
    global $data;
    $eventType = $data['eventType'] ?? '';
    $eventLabel = $data['eventLabel'] ?? '';
    $eventUrl = $data['eventUrl'] ?? '';
    $eventTarget = $data['eventTarget'] ?? '';
    $currentTime = date("Y-m-d H:i:s");

    if (empty($eventType) || empty($eventLabel)) {
        http_response_code(400);
        echo json_encode(array("error" => "Missing required fields"));
        exit;
    }

    $sql = "INSERT INTO track_events (event_type, event_label, event_url, event_target, first_event, last_event, count)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
          event_url    = ?,
          event_target = ?,
          last_event   = ?,
          count        = count + 1";

    $params = array(
        $eventType,
        $eventLabel,
        $eventUrl,
        $eventTarget,
        $currentTime,
        $currentTime,
        1,
        // Update values:
        $eventUrl,
        $eventTarget,
        $currentTime
    );
    $result = sqlStatement($sql, $params);

    if ($result) {
        echo json_encode(array("success" => true));
    } else {
        http_response_code(500);
        echo json_encode(array("error" => "Database insertion/update failed"));
    }
}

// TODO needs work
// TODO call from a background task.
function reportUsageData()
{
    global $data;

    $site_uuid = UniqueInstallationUuid::getUniqueInstallationUuid() ?? '';
    if (empty($site_uuid)) {
        error_log("Site UUID not found.");
        return false;
    }

    $versionService = new VersionService();
    $endpoint = "https://reg.open-emr.org/api/usage?SiteID=" . urlencode($site_uuid);

    // Query the track_events table for usage data.
    $sql = "SELECT event_type, event_label, event_url, event_target, first_event, last_event, count FROM track_events";
    $result = sqlStatement($sql);
    $usageRecords = array();
    while ($row = sqlFetchArray($result)) {
        $usageRecords[] = $row;
    }
    // Collect instance data.
    $localeData = array(
        'site_uuid' => $site_uuid,
        'location_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'location_name' => $_SERVER['SERVER_NAME'] ?? '',
        'time_zone' => $GLOBALS['gbl_time_zone'] ?? '',
        'locale' => locale_get_default(),
        'version' => $versionService->asString(),
    );

    $payload_data = array(
        'usageRecords' => $usageRecords,
        'localeData' => $localeData
    );

    $payload = json_encode($payload_data);
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "Content-Length: " . strlen($payload)
    ));

    $response = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (curl_errno($ch)) {
        error_log("cURL error: " . curl_error($ch));
    }
    curl_close($ch);

    return array("httpStatus" => $httpStatus, "response" => $response);
}

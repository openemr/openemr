<?php

/**
 * MedEx SMS Bot - Message List View
 * Opens SMS Bot showing all recent conversations.
 * Uses MedExAPI (api/oemr/login) — the module's standard auth path.
 *
 * @package   MedEx
 * @link      https://www.medexbank.com
 * @author    MedEx <support@medexbank.com>
 * @copyright Copyright (c) 2017-2026 MedEx
 * @license   Proprietary
 */

require_once("../../../../globals.php");

require_once(__DIR__ . '/../src/MedExAPI.php');
require_once(__DIR__ . '/../src/MedExConfig.php');

use OpenEMR\Modules\MedEx\MedExAPI;
use OpenEMR\Modules\MedEx\MedExConfig;
use OpenEMR\Common\Database\QueryUtils;

// --- Phone style preference ---------------------------------------------------
$prefs = QueryUtils::querySingleRow(
    "SELECT sms_bot_phone_style FROM medex_prefs LIMIT 1",
    []
);
$phoneStyle = $prefs['sms_bot_phone_style'] ?? 'S8';
$allowedPhoneStyles = ['S8', 'iPhone14', 'iPhone4', 'Pixel8', 'minimal'];
if (!in_array($phoneStyle, $allowedPhoneStyles)) {
    $phoneStyle = 'S8';
}

// --- Authenticate via MedExAPI (api/oemr/login) --------------------------------
$medexApi = new MedExAPI();
if (!$medexApi->hasAnyServiceEntitlement(['appointment_reminders', 'medex_messages'])) {
    echo "<html><head><title>MedEx SMS Bot</title></head><body>";
    echo "<h3>MedEx Service Not Enabled</h3>";
    echo "<p>SMS Bot requires an active subscription.</p>";
    echo "</body></html>";
    exit;
}
try {
    $loginData  = $medexApi->login(true);           // force-refresh → fresh oc_api_session token
    $loginToken = $loginData['token'] ?? null;

    if (empty($loginToken)) {
        throw new \RuntimeException('MedEx login returned no token');
    }
} catch (\Exception $e) {
    echo "<html><head><title>MedEx SMS Bot</title></head><body>";
    echo "<h3>MedEx Connection Failed</h3>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your credentials in <a href='../admin/settings.php'>MedEx Settings</a>.</p>";
    echo "</body></html>";
    exit;
}

// --- Browser-facing base URL (public, not internal k8s DNS) --------------------
$publicUrl     = MedExConfig::publicBaseUrl();
$redirectServer = (strpos($publicUrl, '/cart/upload') !== false) ? $publicUrl : ($publicUrl . '/cart/upload');

// --- Provider UID (if logged-in user) ------------------------------------------
$providerUID = null;
$currentUser = $_SESSION['authUserID'] ?? null;
if ($currentUser) {
    $userInfo    = QueryUtils::querySingleRow("SELECT username FROM users WHERE id = ?", [$currentUser]);
    $providerUID = $userInfo['username'] ?? null;
}

// --- Patient sync (when opened for a specific patient) -------------------------
$pid = $_REQUEST['pid'] ?? null;
if ($pid) {
    // Fetch patient from OpenEMR and sync to MedEx so SMS Hub can find them
    $patient = QueryUtils::querySingleRow(
        "SELECT pid, fname, lname, mname, phone_cell, phone_home, email,
                street, city, state, postal_code, country_code,
                hipaa_allowsms, hipaa_allowemail, hipaa_voice,
                deceased_date, language, DOB, sex
         FROM patient_data WHERE pid = ?",
        [$pid]
    );
    if ($patient && !empty($patient['phone_cell'])) {
        try {
            $syncUrl = MedExConfig::baseUrl() . '/index.php?route=api/custom/syncPat&token=' . urlencode($loginToken);
            $syncHttp = \OpenEMR\Common\Http\oeHttp::setOptions([
                'timeout' => 5,
                'verify'  => false,
                'http_errors' => false,
            ]);
            $syncHttp->asFormParams()->post($syncUrl, [
                'pid'              => $patient['pid'],
                'fname'            => $patient['fname'] ?? '',
                'lname'            => $patient['lname'] ?? '',
                'mname'            => $patient['mname'] ?? '',
                'phone_cell'       => $patient['phone_cell'] ?? '',
                'phone_home'       => $patient['phone_home'] ?? '',
                'email'            => $patient['email'] ?? '',
                'street'           => $patient['street'] ?? '',
                'city'             => $patient['city'] ?? '',
                'state'            => $patient['state'] ?? '',
                'postal_code'      => $patient['postal_code'] ?? '',
                'country_code'     => $patient['country_code'] ?? '',
                'hipaa_allowsms'   => $patient['hipaa_allowsms'] ?? '',
                'hipaa_voice'      => $patient['hipaa_voice'] ?? '',
                'hipaa_allowemail' => $patient['hipaa_allowemail'] ?? '',
                'deceased_date'    => $patient['deceased_date'] ?? '',
                'language'         => $patient['language'] ?? '',
            ]);
            error_log("[MedEx] sms_bot_list: synced patient pid={$pid} to MedEx");
        } catch (\Exception $e) {
            error_log("[MedEx] sms_bot_list: patient sync failed for pid={$pid}: " . $e->getMessage());
        }
    }
}

// --- Build SMS Hub redirect URL ------------------------------------------------
$url = $redirectServer . '/index.php?route=information/SmsHub'
     . '&token='       . urlencode($loginToken)
     . '&dir=back&show=new';

if ($phoneStyle) {
    $url .= '&phone_style=' . urlencode($phoneStyle);
}
if ($providerUID) {
    $url .= '&P_UID=' . urlencode($providerUID);
}
if ($pid) {
    $url .= '&pid=' . urlencode($pid);
}

// Server-side redirect won't work here because globals.php already sent output
// (validation_script.js.php outputs <script> tags). Use JS redirect instead.
echo "<script>window.location.replace(" . json_encode($url) . ");</script>";
echo "<noscript><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "'></noscript>";
exit;

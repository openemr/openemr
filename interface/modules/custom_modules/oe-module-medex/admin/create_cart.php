<?php
/**
 * MedEx Module - Create Cart
 *
 * Sends selected services to MedEx API to create a cart/checkout session
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018-2025 MedEx
 * @license   Proprietary - All Rights Reserved
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

// Set JSON response header
header('Content-Type: application/json');

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

// Verify CSRF token
if (empty($session->get('csrf_private_key', null))) {
    CsrfUtils::setupCsrfKey($session);
}
$csrfToken = trim((string)($_POST['csrf_token_form'] ?? ''));
$csrfOk = false;
if ($csrfToken !== '') {
    try {
        if ($session instanceof \Symfony\Component\HttpFoundation\Session\SessionInterface) {
            $csrfOk = CsrfUtils::verifyCsrfToken(token: $csrfToken, session: $session, subject: 'default') ||
                CsrfUtils::verifyCsrfToken(token: $csrfToken, session: $session, subject: 'api');
        } else {
            $csrfOk = CsrfUtils::verifyCsrfToken($csrfToken, 'default') ||
                CsrfUtils::verifyCsrfToken($csrfToken, 'api');
        }
    } catch (\Throwable $e) {
        $csrfOk = CsrfUtils::verifyCsrfToken($csrfToken, 'default') ||
            CsrfUtils::verifyCsrfToken($csrfToken, 'api');
    }
}
if (!$csrfOk) {
    echo json_encode(['success' => false, 'error' => 'Invalid security token']);
    exit;
}

try {
    require_once(__DIR__ . '/../src/MedExAPI.php');
    $api = new \OpenEMR\Modules\MedEx\MedExAPI();

    // Build cart data from form submission
    $cartData = [
        'items' => []
    ];

    // Service: Reminders & Recalls
    if (isset($_POST['service_reminders']) && $_POST['service_reminders'] === 'on') {
        $providerCount = isset($_POST['reminders_providers']) ? count($_POST['reminders_providers']) : 0;
        if ($providerCount > 0) {
            $cartData['items'][] = [
                'service' => 'appointment_reminders',
                'quantity' => $providerCount,
                'providers' => $_POST['reminders_providers']
            ];
        }
    }

    // Service: Calendar & AI Rescheduler
    if (isset($_POST['service_calendar_ai']) && $_POST['service_calendar_ai'] === 'on') {
        $providerCount = isset($_POST['reminders_providers']) ? count($_POST['reminders_providers']) : 1;
        $cartData['items'][] = [
            'service' => 'calendar_ai',
            'quantity' => $providerCount
        ];
    }

    // Service: Calendar View & Export
    if (isset($_POST['service_calendar_view']) && $_POST['service_calendar_view'] === 'on') {
        $providerCount = isset($_POST['reminders_providers']) ? count($_POST['reminders_providers']) : 1;
        $cartData['items'][] = [
            'service' => 'calendar_view',
            'quantity' => $providerCount
        ];
    }

    // Service: Secure Chat (practice-wide)
    if (isset($_POST['service_chat']) && $_POST['service_chat'] === 'on') {
        $cartData['items'][] = [
            'service' => 'secure_chat',
            'quantity' => 1
        ];
    }

    // Service: PDF Form Management (practice-wide)
    if (isset($_POST['service_pdf']) && $_POST['service_pdf'] === 'on') {
        $cartData['items'][] = [
            'service' => 'pdf_management',
            'quantity' => 1
        ];
    }

    if (empty($cartData['items'])) {
        echo json_encode(['success' => false, 'error' => 'Select at least one service to continue.']);
        exit;
    }

    if (empty($cartData['items'])) {
        echo json_encode(['success' => false, 'error' => 'Select at least one service to continue.']);
        exit;
    }

    // Compatibility path: api/oemr/create_cart endpoint no longer exists.
    // Build a local cart summary for step 3 and complete activation via api/oemr/subscribe in process_payment.php.
    $pricing = $api->getPricing(true);
    $services = is_array($pricing['services'] ?? null) ? $pricing['services'] : [];
    $total = 0.0;

    foreach ($cartData['items'] as $item) {
        $serviceKey = (string)($item['service'] ?? '');
        $quantity = (int)($item['quantity'] ?? 1);
        if ($serviceKey === '' || !isset($services[$serviceKey])) {
            continue;
        }
        $svc = $services[$serviceKey];
        $price = (float)($svc['price'] ?? 0.0);
        $providerBased = !empty($svc['provider_based']);
        $total += $providerBased ? ($price * max($quantity, 1)) : $price;
    }

    $cartId = 'onb_' . bin2hex(random_bytes(8));

    $_SESSION['medex_cart_id'] = $cartId;
    $_SESSION['medex_cart_total'] = round($total, 2);
    $_SESSION['medex_cart_items'] = $cartData['items'];

    echo json_encode([
        'success' => true,
        'cart_id' => $cartId,
        'total' => round($total, 2)
    ]);
} catch (\Exception $e) {
    error_log('[MedEx] create_cart.php error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred: ' . $e->getMessage()
    ]);
}

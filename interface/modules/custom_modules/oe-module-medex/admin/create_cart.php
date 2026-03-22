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
if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '', $session)) {
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
    if (isset($_POST['service_calendar']) && $_POST['service_calendar'] === 'on') {
        $providerCount = isset($_POST['reminders_providers']) ? count($_POST['reminders_providers']) : 1;
        $cartData['items'][] = [
            'service' => 'calendar_ai',
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

    // Create cart with MedEx API
    $result = $api->createCart($cartData);

    if ($result['success']) {
        // Store cart_id in session for step 3
        $_SESSION['medex_cart_id'] = $result['cart_id'];
        $_SESSION['medex_cart_total'] = $result['total'];
        $_SESSION['medex_cart_items'] = $result['items'];
        $_SESSION['medex_braintree_token'] = $result['braintree_token'];

        echo json_encode([
            'success' => true,
            'cart_id' => $result['cart_id'],
            'total' => $result['total']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $result['error'] ?? 'Failed to create cart'
        ]);
    }
} catch (\Exception $e) {
    error_log('[MedEx] create_cart.php error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred: ' . $e->getMessage()
    ]);
}

<?php
/**
 * MedEx Module - Process Payment
 *
 * Handles payment processing via Braintree and activates subscription
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

    $cartId       = $_POST['cart_id']       ?? '';
    $paymentNonce = $_POST['payment_nonce'] ?? '';
    $cartTotal    = (float)($_SESSION['medex_cart_total'] ?? -1);

    // cart_id is always required
    if (empty($cartId)) {
        echo json_encode([
            'success' => false,
            'error'   => 'Missing cart information'
        ]);
        exit;
    }

    // payment_nonce is only required when the order total > $0.00
    // (DEMO / customer group 3 has 0.00 pricing — no Braintree needed)
    if (empty($paymentNonce) && $cartTotal > 0) {
        echo json_encode([
            'success' => false,
            'error'   => 'Missing required payment information'
        ]);
        exit;
    }

    // Process payment via MedEx API
    $result = $api->processPayment($cartId, $paymentNonce);

    if ($result['success']) {
        // Clear session cart data
        unset($_SESSION['medex_cart_id']);
        unset($_SESSION['medex_cart_total']);
        unset($_SESSION['medex_cart_items']);
        unset($_SESSION['medex_braintree_token']);

        // Bust DB + session-level services cache so the next getEnabledServices() call
        // re-fetches from medex_subscriptions (where checkout just inserted new rows).
        $api->bustServicesCache();

        // Pre-warm the cache immediately: login(forceRefresh=true) queries medex_subscriptions
        // so the admin menu reflects the newly activated service without a second page-load cycle.
        try {
            $freshServices = $api->getEnabledServices();
        } catch (\Exception $e) {
            error_log('[MedEx] post-checkout services refresh failed: ' . $e->getMessage());
            $freshServices = $result['services'] ?? [];
        }

        echo json_encode([
            'success'         => true,
            'subscription_id' => $result['subscription_id'] ?? null,
            'services'        => $freshServices,
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $result['error'] ?? 'Payment processing failed'
        ]);
    }
} catch (\Exception $e) {
    error_log('[MedEx] process_payment.php error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred: ' . $e->getMessage()
    ]);
}

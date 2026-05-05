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

// Verify CSRF token (compatible across OpenEMR versions)
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

    $cartId       = $_POST['cart_id']       ?? '';
    $paymentNonce = $_POST['payment_nonce'] ?? '';
    $cartTotal    = (float)($_SESSION['medex_cart_total'] ?? -1);
    $cartItems    = $_SESSION['medex_cart_items'] ?? [];
    $sessionCartId = (string)($_SESSION['medex_cart_id'] ?? '');

    // cart_id + local cart payload are required
    if (empty($cartId) || empty($sessionCartId) || $cartId !== $sessionCartId || !is_array($cartItems) || empty($cartItems)) {
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

    // Build subscribe payload from local onboarding cart and complete activation
    // via the active API endpoint: api/oemr/subscribe.
    $practiceId = null;
    $config = $api->getConfig();
    $practiceId = $config['practice_id'] ?? null;
    if (empty($practiceId)) {
        $prefs = \OpenEMR\Common\Database\QueryUtils::querySingleRow(
            "SELECT MedEx_id FROM medex_prefs ORDER BY MedEx_lastupdated DESC LIMIT 1",
            []
        );
        $practiceId = $prefs['MedEx_id'] ?? null;
    }
    if (empty($practiceId)) {
        throw new Exception('MedEx not configured. Practice ID missing.');
    }

    $facilityInfo = \OpenEMR\Common\Database\QueryUtils::querySingleRow(
        "SELECT name, phone, fax, street, city, state, postal_code, country_code, email
         FROM facility
         WHERE primary_business_entity = 1
         LIMIT 1",
        []
    );
    if (!$facilityInfo) {
        $facilityInfo = \OpenEMR\Common\Database\QueryUtils::querySingleRow(
            "SELECT name, phone, fax, street, city, state, postal_code, country_code, email
             FROM facility
             ORDER BY id
             LIMIT 1",
            []
        );
    }

    $add = [];
    foreach ($cartItems as $item) {
        if (!is_array($item)) {
            continue;
        }
        $service = (string)($item['service'] ?? '');
        if ($service === '') {
            continue;
        }
        $providers = isset($item['providers']) && is_array($item['providers']) ? $item['providers'] : [];
        $quantity = (int)($item['quantity'] ?? (count($providers) ?: 1));
        $add[] = [
            'service' => $service,
            'providers' => $providers,
            'provider_count' => max($quantity, 1),
            'quantity' => max($quantity, 1),
        ];
    }

    $subscriptionData = [
        'practice_id' => $practiceId,
        'add' => $add,
        'remove' => [],
        'payment_nonce' => $paymentNonce,
        'use_existing_payment' => false,
        'dev_bypass' => false,
        'practice_info' => $facilityInfo ? [
            'name' => $facilityInfo['name'] ?? 'Practice',
            'phone' => $facilityInfo['phone'] ?? '',
            'fax' => $facilityInfo['fax'] ?? '',
            'street' => $facilityInfo['street'] ?? '',
            'city' => $facilityInfo['city'] ?? '',
            'state' => $facilityInfo['state'] ?? '',
            'postal_code' => $facilityInfo['postal_code'] ?? '',
            'country_code' => $facilityInfo['country_code'] ?? 'US',
            'email' => $facilityInfo['email'] ?? '',
        ] : null,
    ];

    $result = $api->makeRequest('index.php?route=api/oemr/subscribe', $subscriptionData, 'POST');

    if (!empty($result['success'])) {
        // Clear session cart data
        unset($_SESSION['medex_cart_id']);
        unset($_SESSION['medex_cart_total']);
        unset($_SESSION['medex_cart_items']);

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

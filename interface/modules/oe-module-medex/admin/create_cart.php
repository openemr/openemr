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
use OpenEMR\Common\Session\SessionWrapperFactory;

function medexNormalizeServiceId(string $rawId, array $svc = []): string
{
    $id = strtolower(trim($rawId));
    $name = strtolower(trim((string)($svc['name'] ?? '')));
    if ($id === 'calendar service' || $id === 'calendar_service' || $name === 'calendar services') {
        return 'calendar_ai';
    }
    if ($id === 'calendar export' || $id === 'calendar_export') {
        return 'calendar_export';
    }
    return preg_replace('/[^a-z0-9_]/', '', str_replace(' ', '_', $id));
}

// Set JSON response header
header('Content-Type: application/json');

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

$session = null;
if (class_exists(SessionWrapperFactory::class)) {
    try {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
    } catch (\Throwable $e) {
        $session = null;
    }
}

// Verify CSRF token
if ($session) {
    if (empty($session->get('csrf_private_key', null))) {
        CsrfUtils::setupCsrfKey($session);
    }
} else {
    if (empty($_SESSION['csrf_private_key'] ?? null)) {
        CsrfUtils::setupCsrfKey();
    }
}
$csrfToken = trim((string)($_POST['csrf_token_form'] ?? ''));
$csrfOk = false;
if ($csrfToken !== '') {
    try {
        if ($session) {
            $csrfOk = CsrfUtils::verifyCsrfToken(token: $csrfToken, subject: 'default', session: $session) ||
                CsrfUtils::verifyCsrfToken(token: $csrfToken, subject: 'api', session: $session);
        } else {
            $csrfOk = CsrfUtils::verifyCsrfToken($csrfToken, 'default') ||
                CsrfUtils::verifyCsrfToken($csrfToken, 'api');
        }
    } catch (\Throwable $e) {
        $csrfOk = false;
    }
}
if (!$csrfOk) {
    $freshToken = '';
    try {
        if ($session) {
            $freshToken = (string) CsrfUtils::collectCsrfToken(subject: 'default', session: $session);
        } else {
            $freshToken = (string) CsrfUtils::collectCsrfToken('default');
        }
    } catch (\Throwable $e) {
        $freshToken = '';
    }
    echo json_encode(['success' => false, 'error' => 'Invalid security token', 'csrf_token' => $freshToken]);
    exit;
}

try {
    require_once(__DIR__ . '/../src/MedExAPI.php');
    $api = new \OpenEMR\Modules\MedEx\MedExAPI();

    // Build cart data from form submission
    $cartData = [
        'items' => []
    ];
    $selectedServiceIds = [];
    foreach ($_POST as $key => $value) {
        if (!is_string($key) || strpos($key, 'service_') !== 0) {
            continue;
        }
        if ((string)$value !== 'on') {
            continue;
        }
        $serviceId = substr($key, strlen('service_'));
        $serviceId = medexNormalizeServiceId((string)$serviceId);
        if ($serviceId !== '') {
            $selectedServiceIds[] = $serviceId;
        }
    }
    $selectedServiceIds = array_values(array_unique($selectedServiceIds));
    $selectedProviders = isset($_POST['reminders_providers']) && is_array($_POST['reminders_providers']) ? array_values($_POST['reminders_providers']) : [];
    $selectedProviderCount = count($selectedProviders);

    // Pull pricing metadata so provider-based services can use provider quantity.
    $pricing = $api->getPricing(true);
    $servicesRaw = is_array($pricing['services'] ?? null) ? $pricing['services'] : [];
    $services = [];
    foreach ($servicesRaw as $rawId => $meta) {
        $normalizedId = medexNormalizeServiceId((string)$rawId, is_array($meta) ? $meta : []);
        if ($normalizedId === '') {
            continue;
        }
        $services[$normalizedId] = is_array($meta) ? $meta : [];
    }
    foreach ($selectedServiceIds as $serviceId) {
        if (!isset($services[$serviceId])) {
            continue;
        }
        $serviceMeta = is_array($services[$serviceId]) ? $services[$serviceId] : [];
        $providerBased = !empty($serviceMeta['provider_based']);
        if ($providerBased && $selectedProviderCount < 1) {
            echo json_encode(['success' => false, 'error' => 'Select at least one provider for provider-based services.']);
            exit;
        }
        $item = [
            'service' => $serviceId,
            'quantity' => $providerBased ? $selectedProviderCount : 1,
        ];
        if ($providerBased) {
            $item['providers'] = $selectedProviders;
        }
        $cartData['items'][] = $item;
    }

    if (empty($cartData['items'])) {
        echo json_encode(['success' => false, 'error' => 'Select at least one service to continue.']);
        exit;
    }

    // Compatibility path: api/oemr/create_cart endpoint no longer exists.
    // Build a local cart summary for step 3 and complete activation via api/oemr/subscribe in process_payment.php.
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

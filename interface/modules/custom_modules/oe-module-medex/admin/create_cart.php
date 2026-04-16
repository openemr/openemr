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

// Set JSON response header
header('Content-Type: application/json');

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

// Verify CSRF token
$session = null;
if (class_exists(SessionWrapperFactory::class)) {
    try {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
    } catch (\Throwable $e) {
        $session = null;
    }
}
if ($session && empty($session->get('csrf_private_key', null))) {
    CsrfUtils::setupCsrfKey($session);
} elseif (!$session && empty($_SESSION['csrf_private_key'] ?? null)) {
    CsrfUtils::setupCsrfKey();
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
    $pricing = $api->getPricing(true);
    $rawServices = is_array($pricing['services'] ?? null) ? $pricing['services'] : [];
    $services = $rawServices;
    foreach ([
        'calendar_view' => ['calendar_view', 'calendar_export'],
        'calendar_ai' => ['calendar_ai', 'Calendar Service'],
        'calendar_full' => ['calendar_full', 'FullCalendar'],
    ] as $canonicalKey => $aliases) {
        foreach ($aliases as $aliasKey) {
            if (!array_key_exists($aliasKey, $rawServices)) {
                continue;
            }
            $services[$canonicalKey] = $rawServices[$aliasKey];
            break;
        }
    }
    $serviceIsPurchasable = static function (string $serviceKey) use ($services): bool {
        if (!array_key_exists($serviceKey, $services)) {
            return false;
        }
        $service = is_array($services[$serviceKey] ?? null) ? $services[$serviceKey] : [];
        return !array_key_exists('available', $service) || $service['available'] !== false;
    };

    // Build cart data from form submission
    $cartData = [
        'items' => []
    ];

    // Service: Reminders & Recalls
    if (isset($_POST['service_reminders']) && $_POST['service_reminders'] === 'on') {
        if (!$serviceIsPurchasable('appointment_reminders')) {
            echo json_encode(['success' => false, 'error' => 'Reminders & Recalls is not available for this account.']);
            exit;
        }
        $providerCount = isset($_POST['reminders_providers']) ? count($_POST['reminders_providers']) : 0;
        $facilityCount = isset($_POST['reminders_facilities']) ? count($_POST['reminders_facilities']) : 0;
        if ($providerCount > 0 && $facilityCount > 0) {
            $cartData['items'][] = [
                'service' => 'appointment_reminders',
                'quantity' => $providerCount,
                'providers' => $_POST['reminders_providers'],
                'facilities' => $_POST['reminders_facilities']
            ];
        } else {
            echo json_encode(['success' => false, 'error' => 'Select at least one provider and one facility for Reminders & Recalls.']);
            exit;
        }
    }

    // Service: Calendar & AI Rescheduler
    if (isset($_POST['service_calendar_ai']) && $_POST['service_calendar_ai'] === 'on') {
        if (!$serviceIsPurchasable('calendar_ai')) {
            echo json_encode(['success' => false, 'error' => 'Calendar & AI Rescheduler is not available for this account.']);
            exit;
        }
        $providerCount = isset($_POST['reminders_providers']) ? count($_POST['reminders_providers']) : 1;
        $cartData['items'][] = [
            'service' => 'calendar_ai',
            'quantity' => $providerCount
        ];
    }

    // Service: Calendar View & Export
    if (isset($_POST['service_calendar_view']) && $_POST['service_calendar_view'] === 'on') {
        if (!$serviceIsPurchasable('calendar_view')) {
            echo json_encode(['success' => false, 'error' => 'Calendar View & Export is not available for this account.']);
            exit;
        }
        $providerCount = isset($_POST['reminders_providers']) ? count($_POST['reminders_providers']) : 1;
        $cartData['items'][] = [
            'service' => 'calendar_export',
            'quantity' => $providerCount
        ];
    }

    // Service: Secure Chat (practice-wide)
    if (isset($_POST['service_chat']) && $_POST['service_chat'] === 'on') {
        if (!$serviceIsPurchasable('secure_chat')) {
            echo json_encode(['success' => false, 'error' => 'Secure Chat is not available for this account.']);
            exit;
        }
        $cartData['items'][] = [
            'service' => 'secure_chat',
            'quantity' => 1
        ];
    }

    // Service: PDF Form Management (practice-wide)
    if (isset($_POST['service_pdf']) && $_POST['service_pdf'] === 'on') {
        if (!$serviceIsPurchasable('pdf_management')) {
            echo json_encode(['success' => false, 'error' => 'PDF Form Management is not available for this account.']);
            exit;
        }
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

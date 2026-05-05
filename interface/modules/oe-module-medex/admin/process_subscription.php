<?php
/**
 * Process Subscription Changes
 *
 * Handles subscription additions, removals, and payment processing
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

error_log('[MedEx] process_subscription.php FILE LOADED - Request method: ' . ($_SERVER['REQUEST_METHOD'] ?? 'unknown'));

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

error_log('[MedEx] process_subscription.php - After globals.php loaded');

use OpenEMR\Common\Acl\AclMain;

// Set JSON response header
header('Content-Type: application/json');

error_log('[MedEx] process_subscription.php - Checking ACL');

// Check admin access (POST+ACL sufficient — no CSRF check needed)
if (!AclMain::aclCheckCore('admin', 'super')) {
    error_log('[MedEx] process_subscription.php - ACL CHECK FAILED');
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

error_log('[MedEx] process_subscription.php - ACL CHECK PASSED');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
    exit;
}

// Load MedEx API
require_once(__DIR__ . '/../src/MedExAPI.php');
$api = new \OpenEMR\Modules\MedEx\MedExAPI();

try {
    error_log('[MedEx] process_subscription.php STARTED - Input: ' . json_encode($input));

    // Validate input
    $addServices = $input['add'] ?? [];
    $removeServices = $input['remove'] ?? [];
    $paymentNonce = $input['payment_nonce'] ?? null;
    $useExistingPayment = $input['use_existing_payment'] ?? false;
    $providerSelections = $input['providers'] ?? [];
    $devBypass = $input['dev_bypass'] ?? false; // TODO: Remove for production

    // Calendar billing bundle normalization:
    // treat calendar_export as part of calendar_ai so only one service is billed.
    $normalizeServiceKey = static function ($key) {
        $k = trim((string)$key);
        if ($k === 'calendar_export') {
            return 'calendar_ai';
        }
        return $k;
    };

    if (is_array($providerSelections)) {
        $normalizedProviderSelections = [];
        foreach ($providerSelections as $rawKey => $providerIds) {
            $mappedKey = $normalizeServiceKey($rawKey);
            if ($mappedKey === '') {
                continue;
            }
            if (!isset($normalizedProviderSelections[$mappedKey])) {
                $normalizedProviderSelections[$mappedKey] = [];
            }
            if (is_array($providerIds)) {
                foreach ($providerIds as $pid) {
                    $pid = (int)$pid;
                    if ($pid > 0 && !in_array($pid, $normalizedProviderSelections[$mappedKey], true)) {
                        $normalizedProviderSelections[$mappedKey][] = $pid;
                    }
                }
            }
        }
        $providerSelections = $normalizedProviderSelections;
    }

    if (is_array($removeServices)) {
        $normalizedRemovals = [];
        foreach ($removeServices as $rawKey) {
            $mappedKey = $normalizeServiceKey($rawKey);
            if ($mappedKey !== '') {
                $normalizedRemovals[] = $mappedKey;
            }
        }
        $removeServices = array_values(array_unique($normalizedRemovals));
    }

    if (is_array($addServices)) {
        $normalizedAdds = [];
        foreach ($addServices as $item) {
            if (is_array($item)) {
                $rawServiceKey = $item['serviceId'] ?? $item['service'] ?? '';
                $mappedKey = $normalizeServiceKey($rawServiceKey);
                if ($mappedKey === '') {
                    continue;
                }
                $item['serviceId'] = $mappedKey;
                $item['service'] = $mappedKey;
                if (empty($item['providerIds']) && !empty($providerSelections[$mappedKey])) {
                    $item['providerIds'] = $providerSelections[$mappedKey];
                }
                $normalizedAdds[$mappedKey] = $item;
            } else {
                $mappedKey = $normalizeServiceKey($item);
                if ($mappedKey !== '') {
                    $normalizedAdds[$mappedKey] = $mappedKey;
                }
            }
        }
        $addServices = array_values($normalizedAdds);
    }

    error_log("[MedEx] Parsed input - addServices: " . json_encode($addServices) . ", removeServices: " . json_encode($removeServices));

    if (empty($addServices) && empty($removeServices)) {
        throw new Exception('No subscription changes specified');
    }

    // Calculate estimated total to determine if payment is needed
    $estimatedTotal = 0.0;
    if (!empty($addServices)) {
        // Need to fetch pricing to calculate total
        // Do a quick pricing fetch from MedEx API
        $pricingData = $api->makeRequest('index.php?route=api/oemr/pricing', [], 'GET');
        error_log('[MedEx] Pricing data for total calculation: ' . json_encode($pricingData));

        if ($pricingData && isset($pricingData['services'])) {
            foreach ($addServices as $item) {
                $serviceKey = is_array($item) ? ($item['serviceId'] ?? $item['service'] ?? '') : $item;
                $quantity = is_array($item) ? ($item['quantity'] ?? 1) : 1;

                if (!empty($pricingData['services'][$serviceKey])) {
                    $service = $pricingData['services'][$serviceKey];
                    $price = isset($service['price']) ? floatval($service['price']) : 0.0;
                    $isProviderBased = isset($service['provider_based']) ? $service['provider_based'] : false;

                    if ($isProviderBased) {
                        $estimatedTotal += ($price * $quantity);
                    } else {
                        $estimatedTotal += $price;
                    }
                    error_log("[MedEx] Service $serviceKey: price=$price, quantity=$quantity, providerBased=" . ($isProviderBased ? 'yes' : 'no') . ", subtotal added=" . ($isProviderBased ? ($price * $quantity) : $price));
                }
            }
        }
    }

    error_log("[MedEx] Estimated total for payment check: $estimatedTotal");

    // Check if this is a free/DEMO customer by reading the pricing_tier from the
    // cached pricing data in medex_prefs.status (populated by getEnabledServices,
    // no network call needed). If multiplier is 0 or customer_group_id is 3/7,
    // all services are free and no payment is required.
    $isFreeCustomer = false;
    try {
        $prefsRow = \OpenEMR\Common\Database\QueryUtils::querySingleRow(
            "SELECT status FROM medex_prefs ORDER BY MedEx_lastupdated DESC LIMIT 1",
            []
        );
        if ($prefsRow && !empty($prefsRow['status'])) {
            $statusData = json_decode($prefsRow['status'], true);
            // pricing_tier is nested inside pricing_cache
            $pricingCache = is_array($statusData['pricing_cache'] ?? null) ? $statusData['pricing_cache'] : [];
            $pricingTier  = is_array($pricingCache['pricing_tier'] ?? null) ? $pricingCache['pricing_tier'] : [];
            $cgid        = intval($pricingTier['customer_group_id'] ?? 1);
            $multiplier  = isset($pricingTier['multiplier']) ? floatval($pricingTier['multiplier']) : 1.0;
            $tierName    = $pricingTier['name'] ?? '';
            $isFreeCustomer = in_array($cgid, [3, 7]) || $multiplier == 0.0 || strtoupper($tierName) === 'DEMO';
            error_log("[MedEx] Pricing tier: cgid=$cgid, multiplier=$multiplier, tierName=$tierName, isFreeCustomer=" . ($isFreeCustomer ? 'true' : 'false'));
        }
    } catch (\Exception $e) {
        error_log("[MedEx] Could not determine free customer status: " . $e->getMessage());
    }

    // Payment nonce is only required for new customers without payment on file AND non-zero total
    // If use_existing_payment is true, we'll use the stored payment method at MedEx/Braintree
    // Server will validate if customer actually has a valid payment method
    // Skip validation in dev_bypass mode, total is $0.00, or customer is on a free/DEMO plan
    if (!empty($addServices) && $estimatedTotal > 0 && empty($paymentNonce) && !$useExistingPayment && !$devBypass && !$isFreeCustomer) {
        error_log("[MedEx] BLOCKING: Payment required because estimatedTotal=$estimatedTotal > 0 and not a free customer");
        throw new Exception('Payment information required for new subscriptions');
    }

    error_log("[MedEx] ALLOWING: No payment required (total=$estimatedTotal, paymentNonce=" . ($paymentNonce ? 'present' : 'absent') . ", useExisting=$useExistingPayment, devBypass=$devBypass)");

    // Get practice ID from MedEx config
    $config = $api->getConfig();
    error_log('[MedEx] getConfig() returned: ' . json_encode($config));
    $practiceId = $config['practice_id'] ?? null;

    // Fallback to database if not in config
    if (empty($practiceId)) {
        error_log('[MedEx] practice_id empty from config, trying database fallback');
        $prefs = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT MedEx_id FROM medex_prefs ORDER BY MedEx_lastupdated DESC LIMIT 1"
        , []);
        error_log('[MedEx] Database query returned: ' . json_encode($prefs));
        $practiceId = $prefs['MedEx_id'] ?? null;
    }

    // Also try globals table directly
    if (empty($practiceId)) {
        error_log('[MedEx] Still no practice_id, trying globals table');
        $globalPracticeId = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT gl_value FROM globals WHERE gl_name = 'medex_practice_id'"
        , []);
        error_log('[MedEx] Globals query returned: ' . json_encode($globalPracticeId));
        $practiceId = $globalPracticeId['gl_value'] ?? null;
    }

    if (empty($practiceId)) {
        throw new Exception('MedEx not configured. Please complete registration first. Config: ' . json_encode($config));
    }

    error_log('[MedEx] Final practice_id to use: ' . $practiceId);

    // Get practice/facility information from OpenEMR
    $facilityInfo = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT name, phone, fax, street, city, state, postal_code, country_code, email
         FROM facility
         WHERE primary_business_entity = 1
         LIMIT 1"
    , []);

    // Fallback to first facility if no primary
    if (!$facilityInfo) {
        $facilityInfo = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT name, phone, fax, street, city, state, postal_code, country_code, email
             FROM facility
             ORDER BY id
             LIMIT 1"
        , []);
    }

    // Build subscription change request
    $subscriptionData = [
        'practice_id' => $practiceId,
        'add' => [],
        'remove' => $removeServices,
        'payment_nonce' => $paymentNonce,
        'use_existing_payment' => $useExistingPayment,
        'dev_bypass' => $devBypass, // From input, controlled by JS devBypass flag
        'practice_info' => $facilityInfo ? [
            'name' => $facilityInfo['name'] ?? 'Practice',
            'phone' => $facilityInfo['phone'] ?? '',
            'fax' => $facilityInfo['fax'] ?? '',
            'street' => $facilityInfo['street'] ?? '',
            'city' => $facilityInfo['city'] ?? '',
            'state' => $facilityInfo['state'] ?? '',
            'postal_code' => $facilityInfo['postal_code'] ?? '',
            'country_code' => $facilityInfo['country_code'] ?? 'US',
            'email' => $facilityInfo['email'] ?? ''
        ] : null
    ];

    // Add services with provider details
    // Supports both old string format ["appointment_reminders"] and
    // new object format [{serviceId, quantity, providerIds}] from provider picker
    foreach ($addServices as $item) {
        if (is_array($item)) {
            // New object format from provider picker modal
            $serviceKey  = $item['serviceId'] ?? $item['service'] ?? '';
            $providerIds = $item['providerIds'] ?? [];
            $quantity    = $item['quantity'] ?? max(count($providerIds), 1);
        } else {
            // Legacy string format
            $serviceKey  = $item;
            $providerIds = isset($providerSelections[$serviceKey]) && is_array($providerSelections[$serviceKey])
                            ? $providerSelections[$serviceKey] : [];
            $quantity    = count($providerIds) ?: 1;
        }

        if (empty($serviceKey)) {
            continue;
        }

        $serviceData = [
            'service'        => $serviceKey,
            'providers'      => $providerIds,
            'provider_count' => $quantity,
            'quantity'       => $quantity,
        ];

        $subscriptionData['add'][] = $serviceData;
    }

    error_log('[MedEx OpenEMR] Sending subscription data: ' . json_encode($subscriptionData));

    // Send to MedEx API
    $response = $api->makeRequest('index.php?route=api/oemr/subscribe', $subscriptionData, 'POST');

    if ($response && isset($response['success']) && $response['success']) {
        // Success - trigger practice data sync to MedEx
        error_log('[MedEx] Subscription successful, triggering practice data sync...');

        $syncResult = ['success' => false, 'error' => 'not attempted'];
        try {
            require_once(__DIR__ . '/../src/Services/PracticeService.php');
            $practiceService = new \OpenEMR\Modules\MedEx\Services\PracticeService($api);
            $syncResult = $practiceService->performInitialSync();

            if (!$syncResult['success']) {
                error_log('[MedEx] Warning: Practice data sync failed after subscription: ' . ($syncResult['error'] ?? 'Unknown error'));
            } else {
                error_log('[MedEx] Practice data sync completed successfully');
            }
        } catch (\Throwable $syncEx) {
            error_log('[MedEx] Warning: Practice data sync threw: ' . $syncEx->getMessage());
            // Don't fail the subscription - just log the warning
        }

// Update local medex_prefs.status with new enabled_services.
        // IMPORTANT: merge into existing status JSON so pricing_cache and other fields are preserved.
        if (!empty($response['subscriptions'])) {
            $enabledServices = array_keys($response['subscriptions']);
            error_log('[MedEx] Updating local enabled_services: ' . json_encode($enabledServices));

            // Read current status, merge in enabled_services only, write back
            $currentStatusRow = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT status FROM medex_prefs WHERE MedEx_id = ? LIMIT 1",
                [$practiceId]
            );
            $currentStatus = [];
            if (!empty($currentStatusRow['status'])) {
                $currentStatus = json_decode($currentStatusRow['status'], true) ?? [];
            }
            $currentStatus['enabled_services'] = $enabledServices;
            \OpenEMR\Common\Database\QueryUtils::sqlStatementThrowException(
                "UPDATE medex_prefs SET status = ? WHERE MedEx_id = ?",
                [json_encode($currentStatus), $practiceId]
            );
        }

        // Bust the DB services cache so the next page load goes back to the server
        // and sees the newly activated services (resets the 6-hour throttle window).
        $api->bustServicesCache();

        // Also do a fresh login to refresh session data
        try {
            $api->login(true); // Force refresh
        } catch (\Exception $e) {
            error_log('[MedEx] Warning: Failed to refresh session after subscription: ' . $e->getMessage());
        }

        // Return order details
        echo json_encode([
            'success' => true,
            'message' => 'Subscription updated successfully',
            'order_id' => $response['order_id'] ?? null,
            'subscriptions' => $response['subscriptions'] ?? [],
            'sync_status' => $syncResult['success'] ? 'completed' : 'failed'
        ]);
    } else {
        $errMsg = $response['error'] ?? 'Failed to process subscription changes';
        if (is_array($errMsg)) { $errMsg = json_encode($errMsg); }
        throw new Exception($errMsg);
    }

} catch (\Throwable $e) {
    error_log('[MedEx] Subscription processing error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

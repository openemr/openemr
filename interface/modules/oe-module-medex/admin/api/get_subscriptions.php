<?php
/**
 * Get Subscriptions Tab Content
 *
 * Returns native HTML for subscriptions management (no iframe)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    http_response_code(403);
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> ' . xlt('Access denied') . '</div>';
    exit;
}

// Load MedEx API
require_once(__DIR__ . '/../../src/MedExAPI.php');
$api = new \OpenEMR\Modules\MedEx\MedExAPI();

// Get CSRF token in a way compatible across OpenEMR variants.
// Using token-name-only avoids session type mismatches between builds.
$csrfToken = '';
try {
    $csrfToken = (string) CsrfUtils::collectCsrfToken('csrf_token_form');
} catch (\Throwable $e) {
    try {
        $csrfToken = (string) CsrfUtils::collectCsrfToken();
    } catch (\Throwable $e2) {
        $csrfToken = '';
    }
}

// Check if configured
if (!$api->isConfigured()) {
    echo '<div class="panel">';
    echo '<h3><i class="fas fa-exclamation-circle"></i> ' . xlt('Setup Required') . '</h3>';
    echo '<p>' . xlt('MedEx is not configured yet. Please complete the registration process to manage subscriptions.') . '</p>';
    echo '<a href="splash.php" class="btn btn-primary"><i class="fas fa-rocket"></i> ' . xlt('Get Started') . '</a>';
    echo '</div>';
    exit;
}

// Force fresh login to get full data including customer_group_id
// Cached tokens don't include pricing tier information
try {
    $loginData = $api->login(true); // Force refresh to get customer_group_id
    error_log('[get_subscriptions.php] Login response keys: ' . implode(', ', array_keys($loginData)));
} catch (\Exception $e) {
    error_log('[get_subscriptions.php] Login error: ' . $e->getMessage());
    echo '<div class="panel"><h3><i class="fas fa-exclamation-circle"></i> ' . xlt('Connection Error') . '</h3>';
    echo '<p>' . xlt('Unable to connect to MedEx server.') . ' ' . text($e->getMessage()) . '</p></div>';
    exit;
}

// Get subscriptions - returns real data from OpenCart with pricing
$subscriptionsData = $api->getSubscriptions();
error_log('[get_subscriptions.php] Subscriptions data: ' . json_encode($subscriptionsData));

// Extract data from new structure
$currentSubscriptions = $subscriptionsData['subscriptions'] ?? [];
$activeServices = $subscriptionsData['active_services'] ?? [];
$apiPricing = $subscriptionsData['pricing'] ?? [];
$customerGroupId = $subscriptionsData['customer_group_id'] ?? ($loginData['customer_group_id'] ?? 1);

error_log('[get_subscriptions.php] Customer group ID: ' . $customerGroupId);
error_log('[get_subscriptions.php] Active services: ' . json_encode($activeServices));
error_log('[get_subscriptions.php] API pricing: ' . json_encode($apiPricing));

$braintreeToken = $loginData['braintree_token'] ?? null;

// A la carte credit balance + recharge settings
$creditBalance    = (float)($loginData['credit_balance'] ?? 0.00);
$recharge         = $loginData['recharge'] ?? [];
$rechargeOn       = (int)($recharge['recharge_on']    ?? 0);
$rechargePoint    = (float)($recharge['recharge_point'] ?? 20.00);
$rechargeAmt      = (int)($recharge['recharge_amt']   ?? 0);
$isDemoCustomer   = ($customerGroupId == 3);

// Customer group names for display
$groupNames = [
    1  => 'Standard',
    2  => 'Beta (50% off)',
    3  => 'Demo (Free)',
    4  => 'Discount (25% off)',
    5  => 'Standard',
    6  => 'Redox (20% off)',
    7  => 'TeleMedEx (Free)',
    8  => 'Standard',
    10 => 'AIM (10% off)',
    11 => 'Epic Sandbox (50% off)',
    12 => 'Epic (20% off)',
];

$pricingTierName = $groupNames[$customerGroupId] ?? 'Standard';

// Get pricing from API — force=true so admin always sees live prices from DB, not cached data.
// oc_product is the source of truth; pricing changes in OpenCart admin propagate immediately here.
$pricing = $api->getPricing(true);

// Get campaigns from API
$campaigns = $api->getCampaigns();

// Debug: Log subscription data structure
error_log('[get_subscriptions.php] Current subscriptions count: ' . count($currentSubscriptions));
error_log('[get_subscriptions.php] Pricing data: ' . json_encode($pricing));
error_log('[get_subscriptions.php] Campaigns data: ' . json_encode($campaigns));
error_log('[get_subscriptions.php] Number of campaigns: ' . count($campaigns));

// Get active providers
$providers = sqlStatement("SELECT id, fname, lname FROM users WHERE authorized=1 AND active=1 ORDER BY lname, fname");
$providerList = [];
while ($row = sqlFetchArray($providers)) {
    $providerList[] = $row;
}

// Get selected providers from prefs — same row selection logic as save_preferences.php
// Must use ORDER BY MedEx_lastupdated DESC to match the row that was last written.
// LIMIT 1 without ORDER BY risks returning a stale/empty row when multiple rows exist.
$prefs = sqlQuery("SELECT ME_providers, ME_facilities FROM medex_prefs WHERE ME_username IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1");
// Default to ALL providers/facilities when never configured — better UX than showing nothing.
$selectedProviders = !empty($prefs['ME_providers'])
    ? explode('|', trim($prefs['ME_providers'], '|'))
    : array_column($providerList, 'id');

// Get active facilities
$facilities = sqlStatement("SELECT id, name FROM facility ORDER BY name");
$facilityList = [];
while ($row = sqlFetchArray($facilities)) {
    $facilityList[] = $row;
}

// Get Appointment Categories for Scheduler
$categoriesRes = sqlStatement("SELECT pc_catid, pc_catname FROM openemr_postcalendar_categories ORDER BY pc_catname");
$apptCategories = [];
while ($row = sqlFetchArray($categoriesRes)) {
    $apptCategories[] = $row;
}

// Get selected facilities from prefs
// Default to ALL facilities when never configured.
$selectedFacilities = !empty($prefs['ME_facilities'])
    ? explode('|', trim($prefs['ME_facilities'], '|'))
    : array_column($facilityList, 'id');

// Define available services with descriptions and icons
// UI-only metadata: the three things OpenCart will never know about.
// icon        — Font Awesome 6 class string
// description — OpenEMR-context blurb (OpenCart name is the display name, not this)
// coming_soon — client-side hint; API available=true overrides it
//
// The API ($pricing) is the sole source of which services exist, their names,
// prices, and availability. Adding a new OpenCart product requires NO change here.
$serviceUiMeta = [
    'appointment_reminders' => [
        'icon'        => 'fas fa-calendar-check',
        'description' => 'Automated SMS, email, and voice reminders for appointments. Includes Recalls, Announcements, Clinical Reminders, Dial-0, and Surveys.',
    ],
    'secure_chat' => [
        'icon'        => 'fas fa-comments',
        'description' => 'HIPAA-compliant messaging with patients',
    ],
    'calendar_export' => [
        'icon'        => 'fas fa-file-export',
        'description' => 'Secure calendar export to iCal, Yahoo Calendar.<br>* excludes Google - Not HIPAA-Compliant',
    ],
    'calendar_full' => [
        'icon'        => 'fas fa-calendar-days',
        'description' => 'Advanced embedded calendar with FullCalendar integration and scheduling features',
    ],
    'calendar_ai' => [
        'icon'        => 'fas fa-robot',
        'description' => 'Calendar Services: category setup, calendar export, rescheduler options, and cancellation list rules.',
    ],
    'pdf_management' => [
        'icon'        => 'fas fa-file-pdf',
        'description' => 'Digital forms and document management',
    ],
    'surveys' => [
        'icon'        => 'fas fa-clipboard-list',
        'description' => 'Post-visit and satisfaction surveys sent automatically to patients.',
    ],
    'vfax' => [
        'icon'        => 'fas fa-fax',
        'description' => 'Virtual fax service for sending and receiving faxes',
        'coming_soon' => true,
    ],
    'whatsapp' => [
        'icon'        => 'fab fa-whatsapp',
        'description' => 'Communicate with patients via WhatsApp',
        'coming_soon' => true,
    ],
    'dedicated_number' => [
        'icon'        => 'fas fa-square-phone',
        'description' => 'Use your own dedicated phone number for outgoing messages (10-DLC compliant). Improves deliverability and brand recognition.',
        'coming_soon' => true,
    ],
    'telemedex' => [
        'icon'        => 'fas fa-video',
        'description' => 'Integrated telehealth video visits with HIPAA-compliant patient portal access.',
    ],
];

// scope/provider_based: billing structure metadata the API may also supply.
// appointment_reminders is the only provider-scoped service; all others are practice-wide.
$_serviceStructure = [
    'appointment_reminders' => ['scope' => 'provider', 'provider_based' => true],
];

// Build $serviceDefinitions from the API as the single source of truth for
// which services exist.  $serviceUiMeta is consulted only for icon/description/coming_soon.
// If the API is unreachable ($pricing empty), fall back to ui meta keys so the
// page doesn't go blank — prices will display as null ("—").
$pricingServices = [];
if (!empty($pricing) && is_array($pricing)) {
    $pricingServices = $pricing['services'] ?? $pricing;
}
$serviceSources = !empty($pricingServices)
    ? $pricingServices
    : array_fill_keys(array_keys($serviceUiMeta), []);

$serviceDefinitions = [];
foreach ($serviceSources as $svcId => $priceInfo) {
    if (!is_array($priceInfo)) {
        $priceInfo = ['price' => is_numeric($priceInfo) ? (float)$priceInfo : null];
    }
    $meta = $serviceUiMeta[$svcId] ?? [];

    $price = null;
    if (isset($priceInfo['price']) && $priceInfo['price'] !== null) {
        $price = (float)$priceInfo['price'];
    } elseif (isset($priceInfo['monthly_cost']) && $priceInfo['monthly_cost'] !== null) {
        $price = (float)$priceInfo['monthly_cost'];
    }

    $serviceName = $priceInfo['name'] ?? ucwords(str_replace('_', ' ', $svcId));
    if (is_string($serviceName)) {
        // Keep internal service ids out of end-user labels (e.g. "Full Calendar calendar_full").
        $serviceName = preg_replace('/[\(\[]?\s*' . preg_quote($svcId, '/') . '\s*[\)\]]?/i', ' ', $serviceName);
        $serviceName = trim(preg_replace('/\s+/', ' ', (string)$serviceName));
    }
    if (
        $svcId === 'appointment_reminders'
        && (
            !is_string($serviceName)
            || trim($serviceName) === ''
            || preg_match('/a\\s*la\\s*carte/i', $serviceName)
        )
    ) {
        // Legacy API payloads may still label this as "a la carte".
        $serviceName = 'Automated Reminders';
    }
    if ($svcId === 'calendar_ai') {
        // Force consistent left-menu label regardless of upstream OpenCart naming.
        $serviceName = 'Calendar';
    }
    if ($svcId === 'calendar_full') {
        $serviceName = 'Full Calendar';
    }

    $serviceDefinitions[$svcId] = [
        'name'           => $serviceName,
        'description'    => $meta['description'] ?? 'MedEx service — see hipaabank.net for details',
        'icon'           => $meta['icon'] ?? 'fas fa-gear',
        'price'          => $price,
        'base_price'     => $price,
        'unit'           => $priceInfo['unit'] ?? null,
        'scope'          => $priceInfo['scope'] ?? ($_serviceStructure[$svcId]['scope'] ?? 'practice'),
        'provider_based' => isset($priceInfo['provider_based'])
                                ? (bool)$priceInfo['provider_based']
                                : ($_serviceStructure[$svcId]['provider_based'] ?? false),
        'trial_days'     => isset($priceInfo['trial_days']) ? (int)$priceInfo['trial_days'] : 0,
        'available'      => isset($priceInfo['available']) ? (bool)$priceInfo['available'] : null,
        'coming_soon'    => $meta['coming_soon'] ?? false,
    ];
}

// Filter unavailable services for non-demo customers
// Demo group (3) and TeleMedEx (7) see all services including "coming soon"
// All others only see services where available=true OR coming_soon is not set
$isDemoCustomer = in_array($customerGroupId, [3, 7]); // Demo or TeleMedEx groups
if (!$isDemoCustomer) {
    foreach ($serviceDefinitions as $svcId => $svc) {
        // Hide if explicitly marked as unavailable by API (oc_product.status = 0)
        // OR if hardcoded as coming_soon and no override from API
        $isAvailableFromAPI = isset($svc['available']) ? $svc['available'] : null;
        $isComingSoon = isset($svc['coming_soon']) ? $svc['coming_soon'] : false;

        if ($isAvailableFromAPI === false || ($isComingSoon && $isAvailableFromAPI !== true)) {
            unset($serviceDefinitions[$svcId]);
            error_log('[get_subscriptions.php] Hiding unavailable service from non-demo customer: ' . $svcId);
        }
    }
}

// Calendar billing bundle: Calendar is the single billable service.
// Calendar Export remains functionally part of the Calendar service and
// should not appear as a separate subscription/billing line item.
if (isset($serviceDefinitions['calendar_ai']) && isset($serviceDefinitions['calendar_export'])) {
    unset($serviceDefinitions['calendar_export']);
}

// $apiPricing from getSubscriptions() never contains a 'pricing' key — removing dead override.
// Pricing is fully sourced from getPricing() (oc_product_recurring) merged above.
foreach ($serviceDefinitions as $svcId => &$svc) {
    // For free tiers (price exactly 0.00), enable all services including "coming soon"
    // null means API was unreachable — don't treat that as free
    if ($svc['price'] !== null && $svc['price'] == 0.00) {
        $svc['available'] = true;
        $svc['coming_soon'] = false;
    }
}
unset($svc);

// Calculate totals and active services
// Use the active_services array from the API (source of truth from OpenCart)
$currentTotal = 0;

// If we have active services from the API, use those
if (!empty($activeServices)) {
    foreach ($activeServices as $serviceKey) {
        if (isset($serviceDefinitions[$serviceKey])) {
            $currentTotal += (float)($serviceDefinitions[$serviceKey]['price'] ?? 0);
        }
    }
} else {
    // Fallback: parse currentSubscriptions if activeServices not available
    $activeServices = [];
    if (is_array($currentSubscriptions)) {
        foreach ($currentSubscriptions as $sub) {
            if (is_array($sub) && isset($sub['service_key']) && $sub['status'] === 'active') {
                $serviceKey = $sub['service_key'];
                $activeServices[] = $serviceKey;
                if (isset($serviceDefinitions[$serviceKey])) {
                    $currentTotal += (float)($serviceDefinitions[$serviceKey]['price'] ?? 0);
                }
            }
        }
    }
}

error_log('[get_subscriptions.php] Active services: ' . json_encode($activeServices));
error_log('[get_subscriptions.php] Current total: $' . $currentTotal);
error_log('[get_subscriptions.php] Service definitions: ' . json_encode(array_keys($serviceDefinitions)));

// Sort service definitions: subscribed first, then available, then coming soon
uksort($serviceDefinitions, function ($a, $b) use ($activeServices, $serviceDefinitions) {
    $getPriority = function ($svcId) use ($activeServices, $serviceDefinitions) {
        if (in_array($svcId, $activeServices)) {
            return 0; // subscribed
        }
        if (!empty($serviceDefinitions[$svcId]['coming_soon'])) {
            return 2; // coming soon
        }
        return 1; // available but not subscribed
    };
    $pa = $getPriority($a);
    $pb = $getPriority($b);
    return $pa <=> $pb;
});

?>

<style>
/* ============================================
   Breadcrumb Navigation System
   ============================================ */
.medex-breadcrumb {
    display: flex;
    align-items: center;
    gap: 0;
    padding: 12px 20px;
    background: linear-gradient(135deg, #f8fbff 0%, #eef0ff 100%);
    border-bottom: 2px solid #0f4b8f;
    border-radius: 8px 8px 0 0;
    font-size: 13px;
    margin: 0 0 16px 0;
    flex-wrap: wrap;
}
.medex-breadcrumb-item {
    display: flex;
    align-items: center;
    gap: 4px;
    color: #0f4b8f;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    transition: all 0.15s ease;
    font-weight: 500;
    white-space: nowrap;
}
.medex-breadcrumb-item:hover {
    background: rgba(102, 126, 234, 0.12);
    color: #4a5ec9;
    text-decoration: none;
}
.medex-breadcrumb-item i {
    font-size: 11px;
}
.medex-breadcrumb-sep {
    color: #b0b8d9;
    font-size: 11px;
    margin: 0 2px;
    user-select: none;
}
.medex-breadcrumb-current {
    display: flex;
    align-items: center;
    gap: 4px;
    color: #333;
    font-weight: 600;
    padding: 4px 8px;
    cursor: default;
    white-space: nowrap;
}
.medex-breadcrumb-current i {
    font-size: 11px;
    color: #0f4b8f;
}

/* ============================================
   Sub-tool Selector Cards (used by AI Scheduling)
   ============================================ */
.subtool-selector {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    padding: 20px 0;
}
.subtool-card {
    border: 2px solid #dbe5ee;
    border-radius: 12px;
    padding: 30px 24px;
    text-align: center;
    cursor: pointer;
    transition: all 0.25s ease;
    background: white;
    position: relative;
    overflow: hidden;
}
.subtool-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: #dbe5ee;
    transition: background 0.25s ease;
}
.subtool-card:hover {
    border-color: #0f4b8f;
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.18);
    transform: translateY(-3px);
}
.subtool-card:hover::before {
    background: linear-gradient(90deg, #0f4b8f, #9c27b0);
}
.subtool-card-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 26px;
}
.subtool-card-title {
    font-size: 18px;
    font-weight: 700;
    color: #333;
    margin-bottom: 8px;
}
.subtool-card-desc {
    font-size: 13px;
    color: #666;
    line-height: 1.5;
    margin-bottom: 16px;
}
.subtool-card-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 20px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    color: white;
    border: none;
    cursor: pointer;
    transition: all 0.15s ease;
}
.subtool-card-action:hover {
    filter: brightness(1.1);
}

/* Subscription-Specific Styles - Ensure they load with content */
#subscription-container {
    display: grid;
    gap: 20px;
}
#subscription-container.subscription-layout-full {
    grid-template-columns: 1fr;
}
#subscription-container.subscription-layout-two-col {
    grid-template-columns: 1fr 400px;
}
#cart-panel {
    display: none;
    align-self: start;
}
#subscription-container.subscription-layout-two-col #cart-panel {
    display: block;
}
#service-list {
    display: grid !important;
    grid-template-columns: repeat(4, 1fr) !important;
    gap: 12px !important;
}
@media (max-width: 1400px) {
    #service-list { grid-template-columns: repeat(3, 1fr) !important; }
}
@media (max-width: 1000px) {
    #service-list { grid-template-columns: repeat(2, 1fr) !important; }
}
@media (max-width: 600px) {
    #service-list { grid-template-columns: 1fr !important; }
}
.service-card {
    border: 2px solid #0f4b8f;
    border-radius: 8px;
    padding: 12px;
    transition: all 0.2s ease;
    position: relative;
    background: #f8fbff;
    display: flex;
    flex-direction: column;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}
.service-card.active {
    border-color: #b7bcd3 !important;
    background: #f0f1fb69 !important;
}
.service-card.pending-add {
    background: #fff8e1 !important;
    border-color: #ffc107 !important;
    border-style: dashed !important;
}
.service-card.selected {
    border-color: #28a745 !important;
    background: #f0f9f4 !important;
}
.service-card.removing {
    border-color: #dc3545 !important;
    background: #fff5f5 !important;
}
.service-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
.service-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 4px;
}
.btn-action-bottom {
    background: #e77681;
    color: white;
    border: none;
    padding: 4px 8px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 11px;
    font-weight: 500;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.btn-action-bottom:hover {
    background: #dc3545;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
.btn-action-bottom.btn-add {
    background: #28a745;
}
.btn-action-bottom.btn-add:hover {
    background: #218838;
}
/* Remove any ::before or ::after content from service list buttons */
#service-list .btn-action-bottom::before,
#service-list .btn-action-bottom::after {
    content: none;
    display: none;
}
.service-title {
    font-size: 13px;
    font-weight: 600;
    color: #333;
    margin-bottom: 2px;
    display: flex;
    align-items: center;
    gap: 5px;
}
.service-title i {
    color: #0f4b8f;
    font-size: 12px;
}
.service-status {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}
.service-status.current {
    background: #d4edda;
    color: #155724;
}
.service-status.trial {
    background: #fff3cd;
    color: #856404;
}
.service-desc {
    color: #666;
    font-size: 12px;
    line-height: 1.3;
    margin-bottom: 6px;
}
.service-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: auto;
    padding-top: 8px;
    border-top: 1px solid #dbe5ee;
}
.service-footer-left {
    display: flex;
    align-items: flex-end;
    gap: 12px;
}
.service-footer-right {
    display: flex;
    align-items: flex-end;
}
.coming-soon-badge {
    display: inline-block;
    padding: 6px 12px;
    background: #6c757d;
    color: white;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
}
.service-price {
    font-size: 12px;
    font-weight: 600;
    color: #333;
}
.service-trial {
    color: #28a745;
    font-weight: 600;
    font-size: 10px;
}
.provider-selector {
    margin-top: 15px;
    padding: 0;
    background: #f9f9f9;
    border-radius: 8px;
    max-height: 0;
    overflow: hidden;
    opacity: 0;
    transition: max-height 0.3s ease, padding 0.3s ease, margin-top 0.3s ease, opacity 0.3s ease;
}
.provider-selector:not(.collapsed) {
    max-height: 300px;
    opacity: 1;
    padding: 15px;
}
/* Button Styles */
.btn {
    padding: 10px 20px;
    border-radius: 6px;
    border: none;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}
.btn-sm {
    padding: 8px 16px;
    font-size: 13px;
}
.btn-success {
    background: #28a745;
    color: white;
}
.btn-success:hover {
    background: #218838;
}
.btn-danger {
    background: #dc3545;
    color: white;
}
.btn-danger:hover {
    background: #c82333;
}
.btn-edit-providers {
    background: #e6eafc87;
    color: black;
    border: 1px solid #a9b7f3;
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 4px;
    cursor: pointer;
    position: absolute;
    top: 15px;
    right: 15px;
}
.btn-edit-providers:hover {
    background: #d4dafb;
}
.service-card.expanded .btn-edit-providers {
    display: none;
}
.service-card.expanded .service-footer {
    display: none;
}
.service-card.expanded.active {
    border: none !important;
    background: white !important;
    box-shadow: 0 8px 32px rgba(0,0,0,0.2) !important;
}
.provider-controls {
    display: flex;
    gap: 8px;
    margin-bottom: 10px;
    transition: max-height 0.3s ease, opacity 0.3s ease;
}
.provider-controls.collapsed {
    max-height: 0;
    opacity: 0;
    overflow: hidden;
    margin-bottom: 0;
}
.provider-controls button {
    padding: 4px 10px;
    font-size: 11px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}
.provider-controls button:hover {
    background: #0f4b8f;
    color: white;
    border-color: #0f4b8f;
}
.provider-list {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 8px;
    background: white;
    transition: max-height 0.3s ease, opacity 0.3s ease;
}
.provider-list.collapsed {
    max-height: 0;
    opacity: 0;
    padding: 0;
    border: none;
    overflow: hidden;
}
.provider-item {
    padding: 6px 4px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.provider-item input[type="checkbox"] {
    cursor: pointer;
}
.provider-item label {
    cursor: pointer;
    margin: 0;
    user-select: none;
}
/* Expanded card view */
.service-card.expanded {
    position: fixed !important;
    top: 60px !important;
    left: 10px !important;
    right: 10px !important;
    bottom: 10px !important;
    z-index: 9999 !important;
    max-width: none !important;
    max-height: none !important;
    min-height: auto !important;
    width: auto !important;
    overflow-y: auto;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    justify-content: flex-start !important;
    align-items: stretch !important;
    display: block !important;
}
.service-card.expanded .service-desc,
.service-card.expanded .service-footer {
    display: none !important;
}
.service-card.expanded .expanded-selector-grid {
    display: grid !important;
    grid-template-columns: repeat(4, 1fr) !important;
    gap: 20px !important;
    margin-top: 10px !important;
}
.expanded-selector-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-top: 10px;
    align-content: start;
}
@media (max-width: 1600px) {
    .expanded-selector-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 1000px) {
    .expanded-selector-grid {
        grid-template-columns: 1fr;
    }
}
.expanded-selector-section {
    background: #f8fbff;
    border: 2px solid #0f4b8f;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.expanded-selector-section h4 {
    margin: 0 0 15px 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
}
.expanded-selector-section h4 i {
    color: #0f4b8f;
}
.expanded-provider-item,
.expanded-facility-item {
    padding: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    background: white;
    border: 1px solid #dbe5ee;
    border-radius: 6px;
    margin-bottom: 8px;
    transition: all 0.2s ease;
}
.expanded-provider-item:last-child,
.expanded-facility-item:last-child {
    margin-bottom: 0;
}
.expanded-provider-item:hover,
.expanded-facility-item:hover {
    background: #f8fbff;
    border-color: #0f4b8f;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
}
.expanded-provider-item label,
.expanded-facility-item label {
    cursor: pointer;
    margin: 0;
    flex: 1;
    font-size: 14px;
}
.expanded-campaign-item {
    padding: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    background: white;
    border: 1px solid #dbe5ee;
    border-radius: 6px;
    margin-bottom: 8px;
    font-size: 14px;
}
.expanded-campaign-item:last-child {
    margin-bottom: 0;
}
/* .btn-close-expanded removed — replaced by breadcrumb bar */
.btn-save-providers {
    margin-top: 20px;
    width: 100%;
    background: #28a745;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    font-size: 16px;
}
.btn-save-providers:hover {
    background: #218838;
}
</style>

<div id="subscription-container" class="subscription-layout-full">
<div class="panel">
    <h3><?php echo xlt('Available Services'); ?></h3>

            <?php if (empty($currentSubscriptions)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <div>
                    <strong><?php echo xlt('No Active Subscriptions'); ?></strong><br>
                    <?php echo xlt('Select services below to get started.'); ?>
                </div>
            </div>
            <?php endif; ?>

            <div id="service-list">
                <?php foreach ($serviceDefinitions as $serviceId => $service):
                    $isActive = in_array($serviceId, $activeServices);
                    $subscription = null;
                    if ($isActive) {
                        foreach ($currentSubscriptions as $subKey => $sub) {
                            // Check both structures
                            if ($subKey === $serviceId) {
                                // Structure: ['appointment_reminders' => [...]]
                                $subscription = $sub;
                                error_log('[get_subscriptions] Subscription for ' . $serviceId . ': providers=' . json_encode($sub['providers'] ?? null) . ', facilities=' . json_encode($sub['facilities'] ?? null));
                                break;
                            } elseif (is_array($sub) && isset($sub['service_id']) && $sub['service_id'] === $serviceId) {
                                // Structure: [['service_id' => 'appointment_reminders', ...]]
                                $subscription = $sub;
                                error_log('[get_subscriptions] Subscription (alt) for ' . $serviceId . ': providers=' . json_encode($sub['providers'] ?? null) . ', facilities=' . json_encode($sub['facilities'] ?? null));
                                break;
                            }
                        }
                    }
                ?>
                <!-- ABOUT TO RENDER CARD: <?php echo $serviceId; ?> -->
                <?php
                    // DEBUG: Check service data
                    echo '<!-- Service data: name=' . ($service['name'] ?? 'MISSING') . ', price=' . ($service['price'] ?? 'MISSING') . ', icon=' . ($service['icon'] ?? 'MISSING') . ' -->';
                ?>
                <div class="service-card <?php echo $isActive ? 'active' : ''; ?>" data-service="<?php echo attr($serviceId); ?>">
                    <!-- DEBUG: Card for <?php echo $serviceId; ?> -->
                    <div class="service-header">
                        <div>
                            <div class="service-title">
                                <i class="<?php echo attr($service['icon'] ?? 'fas fa-gear'); ?>"></i>
                                <?php echo text($service['name']); ?>
                            </div>
                            <?php if ($isActive): ?>
                                <?php if (!empty($subscription['status']) && $subscription['status'] === 'trial'): ?>
                                    <span class="service-status trial"><?php echo xlt('Trial'); ?></span>
                                <?php else: ?>
                                    <span class="service-status current"><?php echo xlt('Active'); ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($isActive): ?>
                            <?php
                                $editLabel = xlt('Edit');
                                if (in_array($serviceId, ['appointment_reminders', 'recall', 'announcements', 'gogreen', 'clinical_reminders', 'surveys'])) {
                                    $editLabel = xlt('Edit Messaging');
                                }
                            ?>
                            <button type="button" onclick="toggleProviderList('<?php echo attr($serviceId); ?>')" class="btn-edit-providers">
                                <i class="fas fa-edit"></i> <?php echo text($editLabel); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                    <!-- Allow HTML in description -->
                    <div class="service-desc"><?php echo xl($service['description']); ?></div>
                    <div class="service-footer">
                        <div class="service-footer-left">
                            <?php
                            // Build price string - avoid duplicate /mo
                            $unit = $service['unit'] ?? '';
                            if ($service['price'] === null) {
                                $priceStr = '&mdash;'; // API unreachable — price unavailable
                            } else {
                                $priceStr = '$' . number_format((float)$service['price'], 2);
                                if (empty($unit) || $unit === 'mo') {
                                    $priceStr .= '/mo';
                                } elseif (strpos($unit, '/') === 0) {
                                    // Unit already starts with slash like "/mo per provider"
                                    $priceStr .= text($unit);
                                } else {
                                    // Unit without slash like "per provider" or "+ usage"
                                    $priceStr .= '/mo ' . text($unit);
                                }
                            }
                            ?>
                            <div class="service-price"><?php echo $priceStr; ?></div>
                            <?php if ($service['trial_days'] > 0): ?>
                                <div class="service-trial"><?php echo text($service['trial_days']); ?> <?php echo xlt('day free trial'); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="service-footer-right">
                            <?php if ($isActive): ?>
                                <button class="btn-action-bottom" onclick="confirmRemoveService('<?php echo attr($serviceId); ?>')">
                                    <i class="fas fa-ban"></i> <?php echo xlt('Cancel'); ?>
                                </button>
                            <?php elseif (!empty($service['coming_soon'])): ?>
                                <span class="coming-soon-badge"><?php echo xlt('Coming Soon'); ?></span>
                            <?php else: ?>
                                <button class="btn-action-bottom btn-add" onclick="addService('<?php echo attr($serviceId); ?>')">
                                    <?php echo xlt('Subscribe'); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Expanded View for Providers & Facilities -->
                    <div class="expanded-selector-grid" id="expanded-view-<?php echo attr($serviceId); ?>" style="display: none;">
                        <!-- Breadcrumb Navigation (all expanded views) -->
                        <?php if ($serviceId !== 'calendar_ai'): ?>
                        <div class="medex-breadcrumb" style="grid-column: 1 / -1;">
                            <span class="medex-breadcrumb-item" onclick="closeExpandedView('<?php echo attr($serviceId); ?>')">
                                <i class="fas fa-th"></i> <?php echo xlt('Dashboard'); ?>
                            </span>
                            <span class="medex-breadcrumb-sep"><i class="fas fa-chevron-right"></i></span>
                            <span class="medex-breadcrumb-item" onclick="closeExpandedView('<?php echo attr($serviceId); ?>')">
                                <i class="fas fa-cogs"></i> <?php echo xlt('Services'); ?>
                            </span>
                            <span class="medex-breadcrumb-sep"><i class="fas fa-chevron-right"></i></span>
                            <span class="medex-breadcrumb-current">
                                <i class="<?php echo attr($service['icon'] ?? 'fas fa-gear'); ?>"></i> <?php echo text($service['name']); ?>
                            </span>
                            <span style="margin-left: auto;">
                                <button type="button" onclick="closeExpandedView('<?php echo attr($serviceId); ?>')" class="btn btn-sm" style="background: #dc3545; color: white; border: none; border-radius: 4px; padding: 4px 12px; font-weight: 600; font-size: 12px;">
                                    <i class="fas fa-times"></i> <?php echo xlt('Close'); ?>
                                </button>
                            </span>
                        </div>
                        <?php endif; ?>
                        <!-- DEBUG: Expanded view for serviceId = <?php echo $serviceId; ?> -->
                        <?php if ($serviceId === 'calendar_export'): ?>
                        <?php error_log('[get_subscriptions] INSIDE calendar_export block'); ?>
                        <!-- Calendar Export / CalDAV Settings - Create Filtered Feeds -->
                        <?php
                            // Build local OpenEMR calendar feed URL (module endpoint, not MedEx API endpoint)
                            $host = trim((string)($_SERVER['HTTP_HOST'] ?? ''));
                            $proto = strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
                            if ($proto === '') {
                                $https = (string)($_SERVER['HTTPS'] ?? '');
                                $proto = (!empty($https) && strtolower($https) !== 'off') ? 'https' : 'http';
                            } else {
                                $proto = trim(explode(',', $proto)[0]);
                            }
                            if ($proto !== 'https') {
                                $proto = 'https';
                            }
                            $openemrBase = ($host !== '') ? ($proto . '://' . $host . rtrim((string)($GLOBALS['webroot'] ?? ''), '/')) : '';
                            $caldavFeedUrl = $openemrBase . '/interface/modules/custom_modules/oe-module-medex/public/calendar_feed.php';
                            
                            // Get practice info for CalDAV auth
                            $practiceEmail = '';
                            $practiceId = '';
                            try {
                                $prefsData = sqlQuery("SELECT ME_username, MedEx_id FROM medex_prefs LIMIT 1");
                                $practiceEmail = $prefsData['ME_username'] ?? '';
                                $practiceId = $prefsData['MedEx_id'] ?? '';
                            } catch (\Exception $e) {
                                // Ignore
                            }
                            
                            // Get existing calendar feeds from LOCAL database (source of truth)
                            // MedEx API is just a mirror - local table is authoritative
                            $existingFeeds = [];
                            $currentOpenEmrUserId = (int)($_SESSION['authUserID'] ?? 0);
                            try {
                                // Match "My Calendar Feeds" scope: show feeds owned by current OpenEMR user.
                                if ($currentOpenEmrUserId > 0) {
                                    $feedStmt = sqlStatement(
                                        "SELECT id, token, name, providers, facilities, provider_names, facility_names,
                                                openemr_user_id, openemr_username, created_at
                                         FROM medex_calendar_feeds
                                         WHERE openemr_user_id = ?
                                         ORDER BY created_at DESC",
                                        [$currentOpenEmrUserId]
                                    );
                                } else {
                                    $feedStmt = sqlStatement(
                                        "SELECT id, token, name, providers, facilities, provider_names, facility_names,
                                                openemr_user_id, openemr_username, created_at
                                         FROM medex_calendar_feeds
                                         WHERE 1 = 0"
                                    );
                                }
                                
                                while ($feed = sqlFetchArray($feedStmt)) {
                                    // Decode JSON fields
                                    $feed['provider_names'] = json_decode($feed['provider_names'] ?: '[]', true);
                                    $feed['facility_names'] = json_decode($feed['facility_names'] ?: '[]', true);
                                    
                                    // Get access stats
                                    $token = $feed['token'] ?? '';
                                    if ($token) {
                                        $statsResult = sqlQuery(
                                            "SELECT 
                                                COUNT(*) as total_accesses,
                                                SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful_accesses,
                                                MAX(accessed_at) as last_access,
                                                MIN(accessed_at) as first_access
                                             FROM medex_calendar_feed_access_log 
                                             WHERE feed_token = ?",
                                            [$token]
                                        );
                                        $feed['access_stats'] = $statsResult;
                                        
                                        // Get recent access (last 5)
                                        $recentAccess = sqlStatement(
                                            "SELECT ip_address, user_agent, accessed_at, success, openemr_username
                                             FROM medex_calendar_feed_access_log 
                                             WHERE feed_token = ? AND success = 1
                                             ORDER BY accessed_at DESC LIMIT 5",
                                            [$token]
                                        );
                                        $feed['recent_access'] = [];
                                        while ($row = sqlFetchArray($recentAccess)) {
                                            $feed['recent_access'][] = $row;
                                        }
                                    }
                                    
                                    $existingFeeds[] = $feed;
                                }
                            } catch (\Exception $e) {
                                error_log('[MedEx Admin] Failed to fetch local feeds: ' . $e->getMessage());
                            }
                        ?>
                        <!-- DEBUG: existingFeeds count = <?php echo count($existingFeeds); ?> (from local DB) -->
                        
                        <!-- Existing Calendar Feeds - Compact Table View -->
                        <div class="expanded-selector-section" style="grid-column: 1 / -1;">
                            <h4><i class="fas fa-rss"></i> <?php echo xlt('Calendar Feeds'); ?></h4>
                            <?php if (empty($existingFeeds)): ?>
                            <div style="padding: 15px; text-align: center; color: #666; background: #f8f9fa; border-radius: 4px;">
                                <p style="margin: 0;"><?php echo xlt('No calendar feeds. Create feeds from the provider calendar page.'); ?></p>
                            </div>
                            <?php else: ?>
                            <?php 
                            // Parse user agent to friendly name
                            if (!function_exists('parseUserAgent')) {
                                function parseUserAgent($ua) {
                                    if (strpos($ua, 'dataaccessd') !== false) return 'Apple';
                                    if (strpos($ua, 'CalendarStore') !== false) return 'Apple';
                                    if (strpos($ua, 'Google-Calendar') !== false) return 'Google';
                                    if (strpos($ua, 'Outlook') !== false) return 'Outlook';
                                    if (strpos($ua, 'curl') !== false) return 'curl';
                                    if (strpos($ua, 'Thunderbird') !== false) return 'Thunderbird';
                                    return 'Other';
                                }
                            }
                            ?>
                            <!-- Search and Controls -->
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <input type="text" id="feeds-search" placeholder="<?php echo xla('Search feeds...'); ?>" 
                                       style="padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; width: 200px; font-size: 12px;"
                                       onkeyup="filterFeedsTable()">
                                <div style="font-size: 11px; color: #666;">
                                    <span id="feeds-count"><?php echo count($existingFeeds); ?></span> <?php echo xlt('feeds'); ?>
                                    | <?php echo xlt('Click headers to sort'); ?>
                                </div>
                            </div>
                            <div style="max-height: 400px; overflow-y: auto;">
                            <table id="feeds-table" style="width: 100%; border-collapse: collapse; font-size: 12px;">
                                <thead style="position: sticky; top: 0; background: #e9ecef;">
                                    <tr style="text-align: left;">
                                        <th onclick="sortFeedsTable(0)" style="padding: 8px; border-bottom: 2px solid #dee2e6; cursor: pointer; user-select: none;">
                                            <?php echo xlt('Feed Name'); ?> <i class="fas fa-sort" style="color: #999;"></i>
                                        </th>
                                        <th onclick="sortFeedsTable(1)" style="padding: 8px; border-bottom: 2px solid #dee2e6; cursor: pointer; user-select: none;">
                                            <?php echo xlt('Created'); ?> <i class="fas fa-sort" style="color: #999;"></i>
                                        </th>
                                        <th style="padding: 8px; border-bottom: 2px solid #dee2e6;">
                                            <?php echo xlt('Created By'); ?>
                                        </th>
                                        <th onclick="sortFeedsTable(2)" style="padding: 8px; border-bottom: 2px solid #dee2e6; cursor: pointer; user-select: none;">
                                            <?php echo xlt('Syncs'); ?> <i class="fas fa-sort" style="color: #999;"></i>
                                        </th>
                                        <th onclick="sortFeedsTable(3)" style="padding: 8px; border-bottom: 2px solid #dee2e6; cursor: pointer; user-select: none;">
                                            <?php echo xlt('Last Sync'); ?> <i class="fas fa-sort" style="color: #999;"></i>
                                        </th>
                                        <th onclick="sortFeedsTable(4)" style="padding: 8px; border-bottom: 2px solid #dee2e6; cursor: pointer; user-select: none;">
                                            <?php echo xlt('Client'); ?> <i class="fas fa-sort" style="color: #999;"></i>
                                        </th>
                                        <th style="padding: 8px; border-bottom: 2px solid #dee2e6; text-align: center;"><?php echo xlt('Actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($existingFeeds as $feed): ?>
                                <?php 
                                    $stats = $feed['access_stats'] ?? [];
                                    $successAccess = (int)($stats['successful_accesses'] ?? 0);
                                    $lastAccess = $stats['last_access'] ?? null;
                                    $createdAt = $feed['created_at'] ?? null;
                                    $lastClient = '';
                                    $lastIp = '';
                                    if (!empty($feed['recent_access'][0])) {
                                        $lastClient = parseUserAgent($feed['recent_access'][0]['user_agent']);
                                        $lastIp = $feed['recent_access'][0]['ip_address'];
                                    }
                                ?>
                                <tr style="border-bottom: 1px solid #dee2e6;" data-feed-id="<?php echo attr($feed['id']); ?>" 
                                    data-created="<?php echo $createdAt ? strtotime($createdAt) : 0; ?>"
                                    data-syncs="<?php echo $successAccess; ?>"
                                    data-lastsync="<?php echo $lastAccess ? strtotime($lastAccess) : 0; ?>">
                                    <td style="padding: 8px;">
                                        <strong><?php echo text($feed['name']); ?></strong>
                                        <?php if (!empty($feed['openemr_username'])): ?>
                                        <i class="fas fa-lock" style="color: #28a745; margin-left: 4px;" title="<?php echo xla('Protected'); ?>"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 8px; color: #666;"><?php echo $createdAt ? text(date("M j 'y", strtotime($createdAt))) : '-'; ?></td>
                                    <td style="padding: 8px; color: #666;"><?php echo text($feed['openemr_username'] ?? '-'); ?></td>
                                    <td style="padding: 8px;"><?php echo $successAccess; ?></td>
                                    <td style="padding: 8px; color: #666;"><?php echo $lastAccess ? text(date('M j, g:ia', strtotime($lastAccess))) : xlt('Never'); ?></td>
                                    <td style="padding: 8px;" title="<?php echo attr($lastIp); ?>"><?php echo text($lastClient ?: '-'); ?></td>
                                    <td style="padding: 8px; text-align: center; white-space: nowrap;">
                                        <button onclick="copyToClipboard('<?php echo attr($caldavFeedUrl . '?feed=' . $feed['token']); ?>')" class="btn btn-sm btn-outline" title="<?php echo xla('Copy URL'); ?>" style="padding: 2px 6px;">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <button onclick="deleteCalendarFeed('<?php echo attr($feed['id']); ?>')" class="btn btn-sm btn-danger" title="<?php echo xla('Delete'); ?>" style="padding: 2px 6px;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                            </div>
                            <script>
                            // Feeds table sorting
                            var feedsSortCol = -1;
                            var feedsSortAsc = true;
                            function sortFeedsTable(col) {
                                var table = document.getElementById('feeds-table');
                                var tbody = table.tBodies[0];
                                var rows = Array.from(tbody.rows);
                                
                                if (feedsSortCol === col) {
                                    feedsSortAsc = !feedsSortAsc;
                                } else {
                                    feedsSortCol = col;
                                    feedsSortAsc = true;
                                }
                                
                                rows.sort(function(a, b) {
                                    var valA, valB;
                                    if (col === 1) { // Created - use data attr
                                        valA = parseInt(a.dataset.created) || 0;
                                        valB = parseInt(b.dataset.created) || 0;
                                    } else if (col === 2) { // Syncs - numeric
                                        valA = parseInt(a.dataset.syncs) || 0;
                                        valB = parseInt(b.dataset.syncs) || 0;
                                    } else if (col === 3) { // Last Sync - use data attr
                                        valA = parseInt(a.dataset.lastsync) || 0;
                                        valB = parseInt(b.dataset.lastsync) || 0;
                                    } else { // Text columns
                                        valA = a.cells[col].textContent.toLowerCase();
                                        valB = b.cells[col].textContent.toLowerCase();
                                    }
                                    
                                    if (valA < valB) return feedsSortAsc ? -1 : 1;
                                    if (valA > valB) return feedsSortAsc ? 1 : -1;
                                    return 0;
                                });
                                
                                rows.forEach(function(row) { tbody.appendChild(row); });
                                
                                // Update sort icons
                                table.querySelectorAll('th i.fa-sort, th i.fa-sort-up, th i.fa-sort-down').forEach(function(icon, idx) {
                                    if (idx === col) {
                                        icon.className = 'fa fa-sort-' + (feedsSortAsc ? 'up' : 'down');
                                        icon.style.color = '#0f4b8f';
                                    } else {
                                        icon.className = 'fa fa-sort';
                                        icon.style.color = '#999';
                                    }
                                });
                            }
                            
                            // Feeds table filtering
                            function filterFeedsTable() {
                                var input = document.getElementById('feeds-search');
                                var filter = input.value.toLowerCase();
                                var table = document.getElementById('feeds-table');
                                var rows = table.tBodies[0].rows;
                                var visibleCount = 0;
                                
                                for (var i = 0; i < rows.length; i++) {
                                    var text = rows[i].textContent.toLowerCase();
                                    if (text.indexOf(filter) > -1) {
                                        rows[i].style.display = '';
                                        visibleCount++;
                                    } else {
                                        rows[i].style.display = 'none';
                                    }
                                }
                                
                                document.getElementById('feeds-count').textContent = visibleCount;
                            }
                            </script>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Brief Instructions -->
                        <div class="expanded-selector-section" style="grid-column: 1 / -1;">
                            <div style="display: flex; gap: 30px; font-size: 12px; color: #666;">
                                <div><i class="fab fa-apple"></i> <strong>Apple:</strong> Calendar → File → New Calendar Subscription → Paste URL</div>
                                <div><i class="fab fa-google"></i> <strong>Google:</strong> Other calendars (+) → From URL (no auth support)</div>
                                <div><i class="fas fa-shield-alt" style="color: #28a745;"></i> All access logged for HIPAA compliance</div>
                            </div>
                        </div>
                        
                        <?php elseif ($serviceId === 'secure_chat'): ?>
                        <!-- Secure Chat Audit Trail with Pagination and Sorting -->
                        <div class="expanded-selector-section" style="grid-column: 1 / -1;">
                            <h4><i class="fas fa-comments"></i> <?php echo xlt('Secure Chat Audit Trail'); ?></h4>
                            <p style="color: #666; margin-bottom: 15px;">
                                <?php echo xlt('Review HIPAA-compliant patient conversations. All chats are stored as encounters in OpenEMR.'); ?>
                            </p>
                            
                            <!-- Pagination Controls -->
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <label><?php echo xlt('Show'); ?></label>
                                    <select id="chatAuditPerPage" class="form-control form-control-sm" style="width: auto;" onchange="loadChatAuditData(1)">
                                        <option value="10">10</option>
                                        <option value="25" selected>25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                    <label><?php echo xlt('entries'); ?></label>
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <input type="text" id="chatAuditSearch" class="form-control form-control-sm" 
                                           placeholder="<?php echo xla('Search patient...'); ?>" 
                                           style="width: 200px;" onkeyup="debounceSearch()">
                                    <button class="btn btn-sm btn-outline-primary" onclick="loadChatAuditData(1)">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div id="secure-chat-audit" style="max-height: 400px; overflow-y: auto;">
                                <table style="width: 100%; border-collapse: collapse;" id="chatAuditTable">
                                    <thead>
                                        <tr style="background: #f5f5f5; text-align: left;">
                                            <th style="padding: 10px; border-bottom: 2px solid #ddd; cursor: pointer;" onclick="sortChatAudit('patient')" data-sort="patient">
                                                <?php echo xlt('Patient'); ?> <i class="fas fa-sort" id="sortIcon-patient"></i>
                                            </th>
                                            <th style="padding: 10px; border-bottom: 2px solid #ddd; cursor: pointer;" onclick="sortChatAudit('date')" data-sort="date">
                                                <?php echo xlt('Date/Time'); ?> <i class="fas fa-sort" id="sortIcon-date"></i>
                                            </th>
                                            <th style="padding: 10px; border-bottom: 2px solid #ddd; cursor: pointer;" onclick="sortChatAudit('action')" data-sort="action">
                                                <?php echo xlt('Action'); ?> <i class="fas fa-sort" id="sortIcon-action"></i>
                                            </th>
                                            <th style="padding: 10px; border-bottom: 2px solid #ddd;"><?php echo xlt('Method'); ?></th>
                                            <th style="padding: 10px; border-bottom: 2px solid #ddd; cursor: pointer;" onclick="sortChatAudit('user')" data-sort="user">
                                                <?php echo xlt('User'); ?> <i class="fas fa-sort" id="sortIcon-user"></i>
                                            </th>
                                            <th style="padding: 10px; border-bottom: 2px solid #ddd;"><?php echo xlt('Actions'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody id="chatAuditTableBody">
                                        <tr>
                                            <td colspan="6" style="padding: 30px; text-align: center; color: #999;">
                                                <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i><br>
                                                <?php echo xlt('Loading...'); ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination Navigation -->
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                                <div id="chatAuditPageInfo" style="color: #666; font-size: 13px;"></div>
                                <div style="display: flex; gap: 5px;">
                                    <button class="btn btn-sm btn-outline-secondary" id="chatAuditFirstBtn" onclick="loadChatAuditData(1)" disabled>
                                        <i class="fas fa-angle-double-left"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" id="chatAuditPrevBtn" onclick="loadChatAuditData(currentChatPage - 1)" disabled>
                                        <i class="fas fa-angle-left"></i>
                                    </button>
                                    <span id="chatAuditPageNumbers" style="display: flex; gap: 5px;"></span>
                                    <button class="btn btn-sm btn-outline-secondary" id="chatAuditNextBtn" onclick="loadChatAuditData(currentChatPage + 1)" disabled>
                                        <i class="fas fa-angle-right"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" id="chatAuditLastBtn" onclick="loadChatAuditData(totalChatPages)" disabled>
                                        <i class="fas fa-angle-double-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="expanded-selector-section" style="grid-column: 1 / -1;">
                            <h4><i class="fas fa-bolt"></i> <?php echo xlt('Quick Actions'); ?></h4>
                            <div style="display: flex; gap: 15px;">
                                <a href="<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/public/secure_chat.php"
                                   class="btn btn-primary" target="_blank">
                                    <i class="fas fa-plus"></i> <?php echo xlt('New Secure Chat'); ?>
                                </a>
                                <button class="btn btn-outline-secondary" onclick="exportChatAudit()">
                                    <i class="fas fa-download"></i> <?php echo xlt('Export Audit Log'); ?>
                                </button>
                            </div>
                        </div>
                        
                        <div class="expanded-selector-section" style="grid-column: 1 / -1;">
                            <h4><i class="fas fa-info-circle"></i> <?php echo xlt('About Secure Chat'); ?></h4>
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; font-size: 13px;">
                                <div style="padding: 12px; background: #e3f2fd; border-radius: 6px;">
                                    <strong><i class="fas fa-shield-alt"></i> <?php echo xlt('HIPAA Compliant'); ?></strong><br>
                                    <span style="color: #666;"><?php echo xlt('End-to-end encrypted messaging'); ?></span>
                                </div>
                                <div style="padding: 12px; background: #e8f5e9; border-radius: 6px;">
                                    <strong><i class="fas fa-file-medical"></i> <?php echo xlt('Stored as Encounters'); ?></strong><br>
                                    <span style="color: #666;"><?php echo xlt('Chats linked to patient records'); ?></span>
                                </div>
                                <div style="padding: 12px; background: #fff3e0; border-radius: 6px;">
                                    <strong><i class="fas fa-bell"></i> <?php echo xlt('Real-time Notifications'); ?></strong><br>
                                    <span style="color: #666;"><?php echo xlt('Instant alerts for new messages'); ?></span>
                                </div>
                            </div>
                        </div>

                        <?php elseif ($serviceId === 'calendar_full'): ?>
                        <!-- DEBUG: calendar_full block matched! -->
                        <?php error_log('[get_subscriptions] INSIDE calendar_full block for serviceId: ' . $serviceId); ?>
                        <!-- Full Calendar View Configuration -->
                        <div class="expanded-selector-section" style="grid-column: 1 / -1;">
                            <h4><i class="fas fa-calendar-alt"></i> <?php echo xlt('Full Calendar View Access Control'); ?></h4>
                            <p style="color: #666; margin-bottom: 15px;">
                                <?php echo xlt('Select which providers can access the enhanced Full Calendar view. Individual users can further customize their preferences from their personal settings.'); ?>
                            </p>
                        </div>

                        <!-- Providers Section -->
                        <div class="expanded-selector-section">
                            <h4><i class="fas fa-user-md"></i> <?php echo xlt('Select Providers'); ?></h4>
                            <div style="display: flex; gap: 8px; margin-bottom: 15px;">
                                <button type="button" onclick="selectAllProviders('<?php echo attr($serviceId); ?>')" class="btn btn-sm btn-outline">
                                    <?php echo xlt('All'); ?>
                                </button>
                                <button type="button" onclick="selectNoProviders('<?php echo attr($serviceId); ?>')" class="btn btn-sm btn-outline">
                                    <?php echo xlt('None'); ?>
                                </button>
                            </div>
                            <div id="expanded-provider-list-<?php echo attr($serviceId); ?>">
                                <?php
                                // $selectedProviders loaded from medex_prefs at top of file — do NOT reset here
                                foreach ($providerList as $provider):
                                    $isChecked = in_array($provider['id'], $selectedProviders) || in_array((string)$provider['id'], $selectedProviders);
                                ?>
                                <div class="expanded-provider-item">
                                    <input type="checkbox"
                                           name="provider_<?php echo attr($serviceId); ?>"
                                           value="<?php echo attr($provider['id']); ?>"
                                           id="exp_prov_<?php echo attr($serviceId); ?>_<?php echo attr($provider['id']); ?>"
                                           <?php echo $isChecked ? 'checked' : ''; ?>>
                                    <label for="exp_prov_<?php echo attr($serviceId); ?>_<?php echo attr($provider['id']); ?>">
                                        <?php echo text($provider['lname'] . ', ' . $provider['fname']); ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Facilities Section -->
                        <div class="expanded-selector-section">
                            <h4><i class="fas fa-hospital"></i> <?php echo xlt('Select Facilities'); ?></h4>
                            <div style="display: flex; gap: 8px; margin-bottom: 15px;">
                                <button type="button" onclick="selectAllFacilities('<?php echo attr($serviceId); ?>')" class="btn btn-sm btn-outline">
                                    <?php echo xlt('All'); ?>
                                </button>
                                <button type="button" onclick="selectNoFacilities('<?php echo attr($serviceId); ?>')" class="btn btn-sm btn-outline">
                                    <?php echo xlt('None'); ?>
                                </button>
                            </div>
                            <div id="expanded-facility-list-<?php echo attr($serviceId); ?>">
                                <?php
                                // $selectedFacilities loaded from medex_prefs at top of file — do NOT reset here
                                foreach ($facilityList as $facility):
                                    $isChecked = in_array($facility['id'], $selectedFacilities) || in_array((string)$facility['id'], $selectedFacilities);
                                ?>
                                <div class="expanded-facility-item">
                                    <input type="checkbox"
                                           name="facility_<?php echo attr($serviceId); ?>"
                                           value="<?php echo attr($facility['id']); ?>"
                                           id="exp_fac_<?php echo attr($serviceId); ?>_<?php echo attr($facility['id']); ?>"
                                           <?php echo $isChecked ? 'checked' : ''; ?>>
                                    <label for="exp_fac_<?php echo attr($serviceId); ?>_<?php echo attr($facility['id']); ?>">
                                        <?php echo text($facility['name']); ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Save Button for calendar_full -->
                        <button type="button" onclick="saveProvidersAndFacilities('<?php echo attr($serviceId); ?>')" class="btn-save-providers" style="grid-column: 1 / -1;">
                            <i class="fas fa-save"></i> <?php echo xlt('Save Selection'); ?>
                        </button>

                        <?php elseif ($serviceId === 'calendar_ai'): ?>
                        <?php
                            $aiSchedulerUrl = '';
                            try {
                                $sessionToken = (string)($loginData['token'] ?? '');
                                $practiceId = (string)($loginData['practice_id'] ?? ($loginData['practice']['P_PID'] ?? ''));
                                if ($practiceId === '') {
                                    $pref = sqlQuery("SELECT MedEx_id FROM medex_prefs WHERE MedEx_id IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1");
                                    $practiceId = (string)($pref['MedEx_id'] ?? '');
                                }
                                if ($sessionToken !== '' && $practiceId !== '') {
                                    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                                    $host = trim((string)($_SERVER['HTTP_HOST'] ?? ''));
                                    $webRoot = rtrim((string)($GLOBALS['webroot'] ?? ''), '/');
                                    $openEmrBaseUrl = ($host !== '') ? ($scheme . '://' . $host . $webRoot) : '';
                                    $siteId = (string)($_SESSION['site_id'] ?? 'default');
                                    $ssoPayload = [
                                        'practice_id' => $practiceId,
                                        'session_token' => $sessionToken,
                                        'timestamp' => time(),
                                        'nonce' => bin2hex(random_bytes(16)),
                                        'source' => 'openemr_dashboard',
                                        'openemr_base_url' => $openEmrBaseUrl,
                                        'site' => $siteId
                                    ];
                                    $ssoToken = base64_encode(json_encode($ssoPayload));
                                    $aiSchedulerUrl = \OpenEMR\Modules\MedEx\MedExConfig::publicBaseUrl()
                                        . '/index.php?route=calendar/dashboard'
                                        . '&embed=1'
                                        . '&site=' . urlencode($siteId)
                                        . '&sso_token=' . urlencode($ssoToken);
                                }
                            } catch (\Throwable $e) {
                                error_log('[MedEx Admin] Failed to build calendar AI URL: ' . $e->getMessage());
                            }
                        ?>
                        <div class="medex-breadcrumb" style="grid-column: 1 / -1;">
                            <span class="medex-breadcrumb-item" onclick="closeExpandedView('calendar_ai')">
                                <i class="fas fa-th"></i> <?php echo xlt('Dashboard'); ?>
                            </span>
                            <span class="medex-breadcrumb-sep"><i class="fas fa-chevron-right"></i></span>
                            <span class="medex-breadcrumb-item" onclick="closeExpandedView('calendar_ai')">
                                <i class="fas fa-cogs"></i> <?php echo xlt('Services'); ?>
                            </span>
                            <span class="medex-breadcrumb-sep"><i class="fas fa-chevron-right"></i></span>
                            <span class="medex-breadcrumb-current">
                                <i class="fas fa-calendar-alt"></i> <?php echo xlt('Calendar'); ?>
                            </span>
                            <span style="margin-left: auto;">
                                <button type="button" onclick="closeExpandedView('calendar_ai')" class="btn btn-sm" style="background: #dc3545; color: white; border: none; border-radius: 4px; padding: 4px 12px; font-weight: 600; font-size: 12px;">
                                    <i class="fas fa-times"></i> <?php echo xlt('Close'); ?>
                                </button>
                            </span>
                        </div>
                        <div class="expanded-selector-section" style="grid-column: 1 / -1;">
                            <h4><i class="fas fa-calendar-alt"></i> <?php echo xlt('Calendar'); ?></h4>
                            <p style="margin-bottom: 16px;">
                                <?php echo xlt('Open Calendar Services to manage appointment categories, build schedules, configure patient rescheduling, and manage cancellation list rules.'); ?>
                            </p>
                            <?php if ($aiSchedulerUrl !== ''): ?>
                                <a href="<?php echo attr($aiSchedulerUrl); ?>" target="_blank" rel="noopener" onclick="if(typeof top!=='undefined'&&typeof top.restoreSession==='function')top.restoreSession();" class="btn btn-primary" style="background: #0f4b8f; border-color: #0f4b8f;">
                                    <i class="fas fa-external-link-alt"></i> <?php echo xlt('Open Calendar Services'); ?>
                                </a>
                            <?php else: ?>
                                <div class="alert alert-warning" style="margin-bottom: 0;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <?php echo xlt('Unable to build secure scheduler link. Refresh the dashboard and try again.'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php elseif ($serviceId === 'pdf_management'): ?>
                        <!-- PDF Management - Opens externally, this is just a fallback -->
                        <div class="expanded-selector-section" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                            <i class="fas fa-file-pdf" style="font-size: 48px; color: #0f4b8f; margin-bottom: 15px;"></i>
                            <h4><?php echo xlt('PDF Template Manager'); ?></h4>
                            <p style="color: #666; margin-bottom: 20px;">
                                <?php echo xlt('Create and manage custom PDF forms for your practice.'); ?>
                            </p>
                            <a href="pdf/index.php" onclick="if(typeof top!=='undefined'&&typeof top.restoreSession==='function')top.restoreSession();" class="btn btn-primary" style="padding: 12px 24px; background: #0f4b8f; color: white; border-radius: 6px; text-decoration: none;">
                                <i class="fas fa-file-pdf"></i> <?php echo xlt('Open PDF Manager'); ?>
                            </a>
                        </div>
                        
                        <?php elseif ($serviceId === 'appointment_reminders'): ?>
                        <?php
                            // Fetch current Dial 0 status from MedEx API
                            $dial0Enabled = false;
                            try {
                                $dial0Url = $api->getBaseUrl() . '/api/dial0.php';
                                $sessionToken = $api->getSessionToken();
                                
                                $http = \OpenEMR\Common\Http\oeHttp::setOptions(['timeout' => 10, 'verify' => false]);
                                $response = $http->get($dial0Url . '?token=' . urlencode($sessionToken));
                                
                                if ($response->getStatusCode() === 200) {
                                    $dial0Data = json_decode($response->getBody(), true);
                                    $dial0Enabled = $dial0Data['dial0_enabled'] ?? false;
                                }
                            } catch (\Exception $e) {
                                error_log('[MedEx] Failed to fetch Dial 0 status: ' . $e->getMessage());
                            }
                        ?>
                        <!-- Dial 0 Settings (MedEx Messaging only) -->
                        <div class="expanded-selector-section" style="grid-column: 1 / -1; margin-bottom: 15px; background: <?php echo $dial0Enabled ? '#e8f5e9' : '#fff3e0'; ?>; border: 1px solid <?php echo $dial0Enabled ? '#4caf50' : '#ff9800'; ?>; padding: 12px; border-radius: 6px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <h4 style="margin: 0 0 4px 0; font-size: 14px;">
                                        <i class="fas fa-phone" style="color: <?php echo $dial0Enabled ? '#4caf50' : '#ff9800'; ?>;"></i> 
                                        <?php echo xlt('Dial 0 - Live Transfer'); ?>
                                    </h4>
                                    <p style="font-size: 12px; color: #666; margin: 0;">
                                        <?php echo xlt('Patients can press 0 during calls or dial back the SMS number to reach your office.'); ?>
                                    </p>
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span id="dial0-status" style="font-size: 12px; font-weight: 600; color: <?php echo $dial0Enabled ? '#4caf50' : '#999'; ?>;">
                                        <?php echo $dial0Enabled ? xlt('Enabled') : xlt('Disabled'); ?>
                                    </span>
                                    <label class="toggle-switch" style="position: relative; display: inline-block; width: 50px; height: 26px;">
                                        <input type="checkbox" id="dial0-toggle" <?php echo $dial0Enabled ? 'checked' : ''; ?> 
                                               style="opacity: 0; width: 0; height: 0;"
                                               onchange="toggleDial0(this.checked)">
                                        <span class="toggle-slider" style="
                                            position: absolute;
                                            cursor: pointer;
                                            top: 0; left: 0; right: 0; bottom: 0;
                                            background-color: <?php echo $dial0Enabled ? '#4caf50' : '#ccc'; ?>;
                                            transition: 0.3s;
                                            border-radius: 26px;
                                        "></span>
                                        <span class="toggle-knob" style="
                                            position: absolute;
                                            content: '';
                                            height: 20px; width: 20px;
                                            left: <?php echo $dial0Enabled ? '27px' : '3px'; ?>;
                                            bottom: 3px;
                                            background-color: white;
                                            transition: 0.3s;
                                            border-radius: 50%;
                                            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                                        "></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <script>
                        function toggleDial0(enable) {
                            var toggle = document.getElementById('dial0-toggle');
                            var status = document.getElementById('dial0-status');
                            var container = toggle.closest('.expanded-selector-section');
                            var slider = toggle.parentElement.querySelector('.toggle-slider');
                            var knob = toggle.parentElement.querySelector('.toggle-knob');
                            
                            // Disable toggle while processing
                            toggle.disabled = true;
                            status.textContent = '<?php echo xla("Updating..."); ?>';
                            
                            if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
                            fetch('<?php echo attr($api->getBaseUrl()); ?>/api/dial0.php?token=<?php echo attr(urlencode($api->getSessionToken())); ?>', {
                                method: 'POST',
                                headers: {'Content-Type': 'application/json'},
                                body: JSON.stringify({enable: enable})
                            })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success) {
                                    var isEnabled = data.dial0_enabled;
                                    status.textContent = isEnabled ? '<?php echo xla("Enabled"); ?>' : '<?php echo xla("Disabled"); ?>';
                                    status.style.color = isEnabled ? '#4caf50' : '#999';
                                    container.style.background = isEnabled ? '#e8f5e9' : '#fff3e0';
                                    container.style.borderColor = isEnabled ? '#4caf50' : '#ff9800';
                                    container.querySelector('h4 i').style.color = isEnabled ? '#4caf50' : '#ff9800';
                                    slider.style.backgroundColor = isEnabled ? '#4caf50' : '#ccc';
                                    knob.style.left = isEnabled ? '27px' : '3px';
                                    if (window.showToast) window.showToast('Dial 0 ' + (isEnabled ? 'enabled' : 'disabled'), 'success');
                                } else {
                                    // Revert toggle
                                    toggle.checked = !enable;
                                    status.textContent = !enable ? '<?php echo xla("Enabled"); ?>' : '<?php echo xla("Disabled"); ?>';
                                    if (window.showToast) window.showToast('Error: ' + (data.error || 'Unknown'), 'error');
                                }
                            })
                            .catch(err => {
                                toggle.checked = !enable;
                                status.textContent = !enable ? '<?php echo xla("Enabled"); ?>' : '<?php echo xla("Disabled"); ?>';
                                if (window.showToast) window.showToast('Error updating Dial 0', 'error');
                            })
                            .finally(() => {
                                toggle.disabled = false;
                            });
                        }
                        </script>
                        <!-- Providers Section (for messaging services) -->
                        <div class="expanded-selector-section">
                            <h4><i class="fas fa-user-md"></i> <?php echo xlt('Select Providers'); ?></h4>
                            <div style="display: flex; gap: 8px; margin-bottom: 15px;">
                                <button type="button" onclick="selectAllProviders('<?php echo attr($serviceId); ?>')" class="btn btn-sm btn-outline">
                                    <?php echo xlt('All'); ?>
                                </button>
                                <button type="button" onclick="selectNoProviders('<?php echo attr($serviceId); ?>')" class="btn btn-sm btn-outline">
                                    <?php echo xlt('None'); ?>
                                </button>
                            </div>
                            <div id="expanded-provider-list-<?php echo attr($serviceId); ?>">
                                <?php
                                // Get providers from medex_prefs (stored locally, not from API)
                                foreach ($providerList as $provider):
                                    $isChecked = in_array($provider['id'], $selectedProviders) || in_array((string)$provider['id'], $selectedProviders);
                                ?>
                                <div class="expanded-provider-item">
                                    <input type="checkbox"
                                           name="provider_<?php echo attr($serviceId); ?>"
                                           value="<?php echo attr($provider['id']); ?>"
                                           id="exp_prov_<?php echo attr($serviceId); ?>_<?php echo attr($provider['id']); ?>"
                                           <?php echo $isChecked ? 'checked' : ''; ?>>
                                    <label for="exp_prov_<?php echo attr($serviceId); ?>_<?php echo attr($provider['id']); ?>">
                                        <?php echo text($provider['lname'] . ', ' . $provider['fname']); ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Facilities Section -->
                        <div class="expanded-selector-section">
                            <h4><i class="fas fa-hospital"></i> <?php echo xlt('Select Facilities'); ?></h4>
                            <div style="display: flex; gap: 8px; margin-bottom: 15px;">
                                <button type="button" onclick="selectAllFacilities('<?php echo attr($serviceId); ?>')" class="btn btn-sm btn-outline">
                                    <?php echo xlt('All'); ?>
                                </button>
                                <button type="button" onclick="selectNoFacilities('<?php echo attr($serviceId); ?>')" class="btn btn-sm btn-outline">
                                    <?php echo xlt('None'); ?>
                                </button>
                            </div>
                            <div id="expanded-facility-list-<?php echo attr($serviceId); ?>">
                                <?php
                                // Get facilities from medex_prefs (stored locally, not from API)
                                foreach ($facilityList as $facility):
                                    $isChecked = in_array($facility['id'], $selectedFacilities) || in_array((string)$facility['id'], $selectedFacilities);
                                ?>
                                <div class="expanded-facility-item">
                                    <input type="checkbox"
                                           name="facility_<?php echo attr($serviceId); ?>"
                                           value="<?php echo attr($facility['id']); ?>"
                                           id="exp_fac_<?php echo attr($serviceId); ?>_<?php echo attr($facility['id']); ?>"
                                           <?php echo $isChecked ? 'checked' : ''; ?>>
                                    <label for="exp_fac_<?php echo attr($serviceId); ?>_<?php echo attr($facility['id']); ?>">
                                        <?php echo text($facility['name']); ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Reminder Campaigns Section -->
                        <div class="expanded-selector-section">
                            <h4 style="display:flex;justify-content:space-between;align-items:center;">
                                <span><i class="fas fa-bell"></i> <?php echo xlt('Reminders'); ?></span>
                                <button type="button" class="btn btn-sm btn-outline" onclick="openCampaignsModal('reminder')" style="font-size:11px;padding:3px 10px;">
                                    <i class="fas fa-cog"></i> <?php echo xlt('Manage'); ?>
                                </button>
                            </h4>
                            <div id="reminder-campaigns-<?php echo attr($serviceId); ?>">
                                <?php
                                $reminderCampaigns = array_filter($campaigns, function($c) {
                                    return in_array($c['type'] ?? '', ['reminder']) && ($c['active'] ?? false);
                                });
                                if (empty($reminderCampaigns)): ?>
                                    <div style="padding: 8px 0; color: #999; font-style: italic; font-size:13px;">
                                        <?php echo xlt('None active — click Manage to create one'); ?>
                                    </div>
                                <?php else:
                                    foreach ($reminderCampaigns as $campaign): ?>
                                    <div class="expanded-campaign-item">
                                        <i class="fas fa-check-circle" style="color: #28a745;"></i>
                                        <span><?php echo text($campaign['name'] ?? 'Unnamed Campaign'); ?></span>
                                    </div>
                                    <?php endforeach;
                                endif; ?>
                            </div>
                        </div>

                        <!-- Recall Campaigns Section -->
                        <div class="expanded-selector-section">
                            <h4 style="display:flex;justify-content:space-between;align-items:center;">
                                <span><i class="fas fa-calendar-plus"></i> <?php echo xlt('Recalls'); ?></span>
                                <button type="button" class="btn btn-sm btn-outline" onclick="openCampaignsModal('recall')" style="font-size:11px;padding:3px 10px;">
                                    <i class="fas fa-cog"></i> <?php echo xlt('Manage'); ?>
                                </button>
                            </h4>
                            <div id="recall-campaigns-<?php echo attr($serviceId); ?>">
                                <?php
                                $recallCampaigns = array_filter($campaigns, function($c) {
                                    return in_array($c['type'] ?? '', ['recall']) && ($c['active'] ?? false);
                                });
                                if (empty($recallCampaigns)): ?>
                                    <div style="padding: 8px 0; color: #999; font-style: italic; font-size:13px;">
                                        <?php echo xlt('None active — click Manage to create one'); ?>
                                    </div>
                                <?php else:
                                    foreach ($recallCampaigns as $campaign): ?>
                                    <div class="expanded-campaign-item">
                                        <i class="fas fa-check-circle" style="color: #28a745;"></i>
                                        <span><?php echo text($campaign['name'] ?? 'Unnamed Campaign'); ?></span>
                                    </div>
                                    <?php endforeach;
                                endif; ?>
                            </div>
                        </div>

                        <!-- GoGreen Campaigns Section -->
                        <div class="expanded-selector-section">
                            <h4 style="display:flex;justify-content:space-between;align-items:center;">
                                <span><i class="fas fa-leaf"></i> <?php echo xlt('GoGreen'); ?></span>
                                <button type="button" class="btn btn-sm btn-outline" onclick="openCampaignsModal('gogreen')" style="font-size:11px;padding:3px 10px;">
                                    <i class="fas fa-cog"></i> <?php echo xlt('Manage'); ?>
                                </button>
                            </h4>
                            <div id="gogreen-campaigns-<?php echo attr($serviceId); ?>">
                                <?php
                                $gogreenCampaigns = array_filter($campaigns, function($c) {
                                    return in_array($c['type'] ?? '', ['gogreen']) && ($c['active'] ?? false);
                                });
                                if (empty($gogreenCampaigns)): ?>
                                    <div style="padding: 8px 0; color: #999; font-style: italic; font-size:13px;">
                                        <?php echo xlt('None active — click Manage to create one'); ?>
                                    </div>
                                <?php else:
                                    foreach ($gogreenCampaigns as $campaign): ?>
                                    <div class="expanded-campaign-item">
                                        <i class="fas fa-check-circle" style="color: #28a745;"></i>
                                        <span><?php echo text($campaign['name'] ?? 'Unnamed Campaign'); ?></span>
                                    </div>
                                    <?php endforeach;
                                endif; ?>
                            </div>
                        </div>

                        <!-- Announcements Section -->
                        <div class="expanded-selector-section">
                            <h4 style="display:flex;justify-content:space-between;align-items:center;">
                                <span><i class="fas fa-bullhorn"></i> <?php echo xlt('Announcements'); ?></span>
                                <button type="button" class="btn btn-sm btn-outline" onclick="openCampaignsModal('announce')" style="font-size:11px;padding:3px 10px;">
                                    <i class="fas fa-cog"></i> <?php echo xlt('Manage'); ?>
                                </button>
                            </h4>
                            <div id="announcements-<?php echo attr($serviceId); ?>">
                                <?php
                                // API returns type='announce' (from typeMap in campaigns.php)
                                $announcements = array_filter($campaigns, function($c) {
                                    return in_array($c['type'] ?? '', ['announce', 'announcement']) && ($c['active'] ?? false);
                                });
                                if (empty($announcements)): ?>
                                    <div style="padding: 8px 0; color: #999; font-style: italic; font-size:13px;">
                                        <?php echo xlt('None active — click Manage to create one'); ?>
                                    </div>
                                <?php else:
                                    foreach ($announcements as $campaign): ?>
                                    <div class="expanded-campaign-item">
                                        <i class="fas fa-check-circle" style="color: #28a745;"></i>
                                        <span><?php echo text($campaign['name'] ?? 'Unnamed Campaign'); ?></span>
                                    </div>
                                    <?php endforeach;
                                endif; ?>
                            </div>
                        </div>

                        <!-- Clinical Reminders Section -->
                        <div class="expanded-selector-section">
                            <h4 style="display:flex;justify-content:space-between;align-items:center;">
                                <span><i class="fas fa-stethoscope"></i> <?php echo xlt('Clinical Reminders'); ?></span>
                                <button type="button" class="btn btn-sm btn-outline" onclick="openCampaignsModal('clinical')" style="font-size:11px;padding:3px 10px;">
                                    <i class="fas fa-cog"></i> <?php echo xlt('Manage'); ?>
                                </button>
                            </h4>
                            <div id="clinical-reminders-<?php echo attr($serviceId); ?>">
                                <?php
                                // API returns type='clinical' (from typeMap in campaigns.php)
                                $clinicalReminders = array_filter($campaigns, function($c) {
                                    return in_array($c['type'] ?? '', ['clinical', 'clinical_reminder']) && ($c['active'] ?? false);
                                });
                                if (empty($clinicalReminders)): ?>
                                    <div style="padding: 8px 0; color: #999; font-style: italic; font-size:13px;">
                                        <?php echo xlt('None active — click Manage to create one'); ?>
                                    </div>
                                <?php else:
                                    foreach ($clinicalReminders as $campaign): ?>
                                    <div class="expanded-campaign-item">
                                        <i class="fas fa-check-circle" style="color: #28a745;"></i>
                                        <span><?php echo text($campaign['name'] ?? 'Unnamed Campaign'); ?></span>
                                    </div>
                                    <?php endforeach;
                                endif; ?>
                            </div>
                        </div>

                        <!-- Surveys Section -->
                        <div class="expanded-selector-section">
                            <h4 style="display:flex;justify-content:space-between;align-items:center;">
                                <span><i class="fas fa-clipboard-list"></i> <?php echo xlt('Surveys'); ?></span>
                                <button type="button" class="btn btn-sm btn-outline" onclick="openCampaignsModal('survey')" style="font-size:11px;padding:3px 10px;">
                                    <i class="fas fa-gear"></i> <?php echo xlt('Manage'); ?>
                                </button>
                            </h4>
                            <div id="surveys-<?php echo attr($serviceId); ?>">
                                <?php
                                $surveys = array_filter($campaigns, function($c) {
                                    return ($c['type'] ?? '') === 'survey' && ($c['active'] ?? false);
                                });
                                if (empty($surveys)): ?>
                                    <div style="padding: 8px 0; color: #999; font-style: italic; font-size:13px;">
                                        <?php echo xlt('None active — click Manage to create one'); ?>
                                    </div>
                                <?php else:
                                    foreach ($surveys as $campaign): ?>
                                    <div class="expanded-campaign-item">
                                        <i class="fas fa-check-circle" style="color: #28a745;"></i>
                                        <span><?php echo text($campaign['name'] ?? 'Unnamed Campaign'); ?></span>
                                    </div>
                                    <?php endforeach;
                                endif; ?>
                            </div>
                        </div>

                        <button type="button" onclick="saveProvidersAndFacilities('<?php echo attr($serviceId); ?>')" class="btn-save-providers" style="grid-column: 1 / -1;">
                            <i class="fas fa-save"></i> <?php echo xlt('Save Selection'); ?>
                        </button>
                        <?php endif; ?> <!-- End service-specific expanded views -->
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    <!-- Right Panel: Cart & Payment -->
    <div class="panel" id="cart-panel">
            <h3><?php echo xlt('Subscription Summary'); ?></h3>

            <div id="cart-items">
                <?php if (empty($currentSubscriptions)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-shopping-cart"></i>
                        <div><?php echo xlt('No services selected yet.'); ?></div>
                    </div>
                <?php else: ?>
                    <?php foreach ($currentSubscriptions as $serviceKey => $sub):
                        // Determine service ID from either key or array value
                        $cartServiceId = null;
                        $cartStatus = null;

                        if (isset($serviceDefinitions[$serviceKey]) && is_array($sub)) {
                            // Structure: ['appointment_reminders' => ['status' => 'active']]
                            $cartServiceId = $serviceKey;
                            $cartStatus = $sub['status'] ?? null;
                        } elseif (is_array($sub) && !empty($sub['service_id']) && isset($serviceDefinitions[$sub['service_id']])) {
                            // Structure: [['service_id' => 'appointment_reminders', 'status' => 'trial']]
                            $cartServiceId = $sub['service_id'];
                            $cartStatus = $sub['status'] ?? null;
                        }

                        if ($cartServiceId && isset($serviceDefinitions[$cartServiceId])):
                            $service = $serviceDefinitions[$cartServiceId];
                    ?>
                    <div class="cart-item" data-service="<?php echo attr($cartServiceId); ?>">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0;">
                            <div>
                                <strong><?php echo text($service['name']); ?></strong>
                                <?php if ($cartStatus === 'trial'): ?>
                                    <span class="badge trial" style="margin-left: 8px;"><?php echo xlt('Trial'); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($sub['provider_count']) && $sub['provider_count'] > 1): ?>
                                    <div style="font-size: 11px; color: #666; margin-top: 2px;">
                                        <?php echo text($sub['provider_count']); ?> <?php echo xlt('providers'); ?> × <?php echo $service['price'] !== null ? '$' . number_format((float)$service['price'], 2) : '&mdash;'; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="text-align: right;">
                                <?php
                                $displayCost = (!empty($sub['monthly_cost']) && $sub['monthly_cost'] > 0) ? $sub['monthly_cost'] : $service['price'];
                                echo $displayCost !== null ? '<strong>$' . number_format((float)$displayCost, 2) . '</strong>' : '<strong>&mdash;</strong>';
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php
                        endif;
                    endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pending Changes Section -->
            <div id="pending-changes-section" style="display: none; margin-top: 15px;">
                <!-- Pending changes will be inserted here by JavaScript -->
            </div>

            <div style="margin-top: 15px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <strong style="font-size: 18px;"><?php echo xlt('Total'); ?></strong>
                    <strong style="font-size: 24px; color: #0f4b8f;" id="cart-total">$<?php echo number_format($currentTotal, 2); ?></strong>
                </div>
                <div style="color: #666; font-size: 13px; margin-bottom: 15px;">
                    <?php echo xlt('per month'); ?>
                </div>
            </div>

            <div id="payment-section" style="display: none;">
                <div id="medex-payment-errors" style="display:none; margin-bottom: 12px; color: #b42318; font-weight: 600;"></div>

                <div id="medex-card-fields-wrap" style="border: 1px solid #d0d7de; border-radius: 6px; padding: 12px;">
                    <div style="margin-bottom: 10px;">
                        <label for="medex-cardholder-name" style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 4px;"><?php echo xlt('Cardholder Name'); ?></label>
                        <input type="text" id="medex-cardholder-name" autocomplete="cc-name" autocapitalize="words" style="width: 100%; box-sizing: border-box; height: 36px; border: 1px solid #c6cbd1; border-radius: 4px; padding: 6px 10px;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 4px;"><?php echo xlt('Card Number'); ?></label>
                        <div id="medex-card-number" style="height: 36px; border: 1px solid #c6cbd1; border-radius: 4px; padding: 8px 10px; background: #fff;"></div>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <div style="flex:1;">
                            <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 4px;"><?php echo xlt('Expiration'); ?></label>
                            <div id="medex-card-expiration" style="height: 36px; border: 1px solid #c6cbd1; border-radius: 4px; padding: 8px 10px; background: #fff;"></div>
                        </div>
                        <div style="flex:1;">
                            <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 4px;"><?php echo xlt('CVV'); ?></label>
                            <div id="medex-card-cvv" style="height: 36px; border: 1px solid #c6cbd1; border-radius: 4px; padding: 8px 10px; background: #fff;"></div>
                        </div>
                    </div>
                    <div style="margin-top: 10px;">
                        <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 4px;"><?php echo xlt('Postal Code'); ?></label>
                        <div id="medex-card-postal" style="height: 36px; border: 1px solid #c6cbd1; border-radius: 4px; padding: 8px 10px; background: #fff;"></div>
                    </div>
                    <div style="margin-top:8px; color:#4b5563; font-size:12px;"><?php echo xlt('Card number may autofill. Expiration, CVV, and postal code may require manual entry in secure fields.'); ?></div>
                </div>

                <div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 4px;">
                    <div class="form-check" style="margin-bottom: 10px;">
                        <input class="form-check-input" type="checkbox" id="agree-baa" style="cursor: pointer;">
                        <label class="form-check-label" for="agree-baa" style="cursor: pointer; user-select: none;">
                            <?php echo xlt('I agree to the'); ?>
                            <a href="https://medexbank.com/baa" target="_blank" style="color: #007bff; text-decoration: underline;">
                                <?php echo xlt('Business Associate Agreement (BAA)'); ?>
                            </a>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="agree-terms" style="cursor: pointer;">
                        <label class="form-check-label" for="agree-terms" style="cursor: pointer; user-select: none;">
                            <?php echo xlt('I agree to the'); ?>
                            <a href="https://medexbank.com/terms" target="_blank" style="color: #007bff; text-decoration: underline;">
                                <?php echo xlt('Terms and Conditions'); ?>
                            </a>
                        </label>
                    </div>
                </div>

                <button type="button" class="btn btn-success" id="payment-submit-btn" style="width: 100%; margin-top: 15px;" onclick="processPayment()" disabled>
                    <i class="fas fa-check"></i> <?php echo xlt('Complete Subscription Changes'); ?>
                </button>
            </div>

            <button class="btn btn-primary" id="review-changes-btn" style="width: 100%; display: none;" onclick="processSubscriptionChanges()">
                <i class="fas fa-check"></i> <?php echo xlt('Process Changes'); ?>
            </button>
        </div>
</div>

</div><!-- Close subscription-container -->

<script>
// Store Braintree token
window.braintreeToken = <?php echo json_encode($braintreeToken ?? null); ?>;
window.serviceDefinitions = <?php echo json_encode($serviceDefinitions ?? []); ?>;
window.currentSubscriptions = <?php echo json_encode($currentSubscriptions ?? []); ?>;
window.providerList = <?php echo json_encode($providerList ?? []); ?>;

// Copy text to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        if (window.showToast) {
            window.showToast('Copied to clipboard!', 'success');
        } else {
            alert('Copied to clipboard!');
        }
    }, function(err) {
        console.error('Could not copy text: ', err);
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        if (window.showToast) {
            window.showToast('Copied to clipboard!', 'success');
        } else {
            alert('Copied to clipboard!');
        }
    });
}

// Calendar Feed Management Functions
function selectAllNewFeedProviders() {
    document.querySelectorAll('input[name="new_feed_provider"]').forEach(cb => cb.checked = true);
}

function selectNoNewFeedProviders() {
    document.querySelectorAll('input[name="new_feed_provider"]').forEach(cb => cb.checked = false);
}

function selectAllNewFeedFacilities() {
    document.querySelectorAll('input[name="new_feed_facility"]').forEach(cb => cb.checked = true);
}

function selectNoNewFeedFacilities() {
    document.querySelectorAll('input[name="new_feed_facility"]').forEach(cb => cb.checked = false);
}

function createCalendarFeed() {
    const feedName = document.getElementById('new-feed-name').value.trim();
    if (!feedName) {
        if (window.showToast) {
            window.showToast('Please enter a name for this calendar feed', 'error');
        } else {
            alert('Please enter a name for this calendar feed');
        }
        return;
    }
    
    const feedPassword = document.getElementById('new-feed-password').value;
    if (!feedPassword || feedPassword.length < 8) {
        if (window.showToast) {
            window.showToast('Please enter a password with at least 8 characters', 'error');
        } else {
            alert('Please enter a password with at least 8 characters');
        }
        return;
    }
    
    const selectedProviders = Array.from(document.querySelectorAll('input[name="new_feed_provider"]:checked')).map(cb => cb.value);
    const selectedFacilities = Array.from(document.querySelectorAll('input[name="new_feed_facility"]:checked')).map(cb => cb.value);
    
    if (selectedProviders.length === 0 && selectedFacilities.length === 0) {
        if (window.showToast) {
            window.showToast('Please select at least one provider or facility', 'error');
        } else {
            alert('Please select at least one provider or facility');
        }
        return;
    }
    
    // Restore session before AJAX call (OpenEMR session management)
    if (typeof top.restoreSession === 'function') {
        top.restoreSession();
    }
    
    // Create feed via API
    const formData = new FormData();
    formData.append('csrf_token_form', csrfToken);
    formData.append('action', 'create_calendar_feed');
    formData.append('name', feedName);
    formData.append('feed_password', feedPassword);
    formData.append('providers', selectedProviders.join(','));
    formData.append('facilities', selectedFacilities.join(','));
    
    if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
    fetch('../admin/api/calendar_feeds.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.showToast) {
                window.showToast('Calendar feed created successfully!', 'success');
            }
            // Reload to show the new feed
            location.reload();
        } else {
            if (window.showToast) {
                window.showToast('Error: ' + (data.error || 'Unknown error'), 'error');
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.showToast) {
            window.showToast('Error creating calendar feed', 'error');
        }
    });
}

function deleteCalendarFeed(feedId) {
    if (!confirm('Are you sure you want to delete this calendar feed? Any calendar apps using this URL will stop receiving updates.')) {
        return;
    }
    
    // Restore session before AJAX call (OpenEMR session management)
    if (typeof top.restoreSession === 'function') {
        top.restoreSession();
    }
    
    const formData = new FormData();
    formData.append('csrf_token_form', csrfToken);
    formData.append('action', 'delete_calendar_feed');
    formData.append('feed_id', feedId);
    
    if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
    fetch('../admin/api/calendar_feeds.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.showToast) {
                window.showToast('Calendar feed deleted', 'success');
            }
            // Remove table row from DOM (calendar export admin table view)
            const feedRow = document.querySelector(`#feeds-table tr[data-feed-id="${feedId}"]`);
            if (feedRow) {
                feedRow.remove();
            }
            const countEl = document.getElementById('feeds-count');
            if (countEl) {
                const current = parseInt(countEl.textContent || '0', 10) || 0;
                countEl.textContent = String(Math.max(0, current - 1));
            }
            const remainingRows = document.querySelectorAll('#feeds-table tbody tr');
            if (remainingRows.length === 0) {
                location.reload();
            }
        } else {
            if (window.showToast) {
                window.showToast('Error: ' + (data.error || 'Unknown error'), 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.showToast) {
            window.showToast('Error deleting calendar feed', 'error');
        }
    });
}

// Toggle provider list visibility - now expands card to full page
function toggleProviderList(serviceId) {
    // PDF Management: Open PDF Manager directly instead of expanded view
    if (serviceId === 'pdf_management') {
        if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
        window.location.href = 'pdf/index.php';
        return;
    }
    
    const card = document.querySelector(`.service-card[data-service="${serviceId}"]`);
    const expandedView = document.getElementById(`expanded-view-${serviceId}`);

    if (!card || !expandedView) return;

    // Keep one expanded editor open at a time.
    document.querySelectorAll('.service-card.expanded').forEach(function(openCard) {
        if (openCard !== card) {
            openCard.classList.remove('expanded');
            const sid = openCard.getAttribute('data-service');
            const view = sid ? document.getElementById(`expanded-view-${sid}`) : null;
            if (view) view.style.display = 'none';
        }
    });

    // Toggle expanded state
    card.classList.toggle('expanded');

    if (card.classList.contains('expanded')) {
        expandedView.style.display = 'grid';
        document.body.style.overflow = 'hidden'; // Prevent scrolling behind
        const serviceName = window.serviceDefinitions?.[serviceId]?.name || serviceId;
        if (typeof window.medexSetContext === 'function') {
            if (serviceId === 'appointment_reminders') {
                window.medexSetContext(['Dashboard', 'Services', serviceName, 'Edit Messaging']);
            } else {
                window.medexSetContext(['Dashboard', 'Services', serviceName, 'Edit']);
            }
        }
    } else {
        expandedView.style.display = 'none';
        document.body.style.overflow = '';
        if (typeof window.medexSetContext === 'function') {
            window.medexSetContext(['Dashboard', 'Services']);
        }
    }
}

// Close expanded view
window.closeExpandedView = function closeExpandedView(serviceId) {
    const card = document.querySelector(`.service-card[data-service="${serviceId}"]`);
    const expandedView = document.getElementById(`expanded-view-${serviceId}`);

    if (card) card.classList.remove('expanded');
    if (expandedView) expandedView.style.display = 'none';
    document.body.style.overflow = '';
    if (typeof window.medexSetContext === 'function') {
        window.medexSetContext(['Dashboard', 'Services']);
    }
};

window.medexOpenServiceView = function medexOpenServiceView(serviceId, serviceName) {
    const card = document.querySelector(`.service-card[data-service="${serviceId}"]`);
    if (!card) return false;
    const alreadyOpen = card.classList.contains('expanded');
    if (!alreadyOpen) {
        toggleProviderList(serviceId);
    } else if (typeof window.medexSetContext === 'function') {
        const svcName = serviceName || window.serviceDefinitions?.[serviceId]?.name || serviceId;
        window.medexSetContext(['Dashboard', 'Services', svcName, (serviceId === 'appointment_reminders' ? 'Edit Messaging' : 'Edit')]);
    }
    try { card.scrollIntoView({ behavior: 'smooth', block: 'start' }); } catch (e) {}
    return true;
};

// Select all providers for a service
function selectAllProviders(serviceId) {
    const checkboxes = document.querySelectorAll(`input[name="provider_${serviceId}"]`);
    checkboxes.forEach(cb => cb.checked = true);
    handleProviderChange(serviceId);
}

// Deselect all providers for a service
function selectNoProviders(serviceId) {
    const checkboxes = document.querySelectorAll(`input[name="provider_${serviceId}"]`);
    checkboxes.forEach(cb => cb.checked = false);
    handleProviderChange(serviceId);
}

// Sort providers alphabetically
function sortProviders(serviceId, order) {
    const listContainer = document.getElementById(`provider-list-${serviceId}`);
    if (!listContainer) return;

    const items = Array.from(listContainer.querySelectorAll('.provider-item'));

    // Toggle sort order
    const currentOrder = listContainer.dataset.sortOrder || 'az';
    const newOrder = currentOrder === 'az' ? 'za' : 'az';
    listContainer.dataset.sortOrder = newOrder;

    // Sort items
    items.sort((a, b) => {
        const labelA = a.querySelector('label').textContent.trim();
        const labelB = b.querySelector('label').textContent.trim();
        return newOrder === 'az'
            ? labelA.localeCompare(labelB)
            : labelB.localeCompare(labelA);
    });

    // Re-append sorted items
    items.forEach(item => listContainer.appendChild(item));
}

// Handle provider selection changes
function handleProviderChange(serviceId) {
    const checkboxes = document.querySelectorAll(`input[name="provider_${serviceId}"]:checked`);
    const selectedProviders = Array.from(checkboxes).map(cb => cb.value);
    console.log(`Provider change for ${serviceId}:`, selectedProviders);
}

// Select all facilities for a service
function selectAllFacilities(serviceId) {
    const checkboxes = document.querySelectorAll(`input[name="facility_${serviceId}"]`);
    checkboxes.forEach(cb => cb.checked = true);
}

// Deselect all facilities for a service
function selectNoFacilities(serviceId) {
    const checkboxes = document.querySelectorAll(`input[name="facility_${serviceId}"]`);
    checkboxes.forEach(cb => cb.checked = false);
}

// Save providers and facilities selection
function saveProvidersAndFacilities(serviceId) {
    console.log('[saveProvidersAndFacilities] Saving for service:', serviceId);
    const providerCheckboxes = document.querySelectorAll(`input[name="provider_${serviceId}"]:checked`);
    const facilityCheckboxes = document.querySelectorAll(`input[name="facility_${serviceId}"]:checked`);

    const selectedProviders = Array.from(providerCheckboxes).map(cb => cb.value);
    const selectedFacilities = Array.from(facilityCheckboxes).map(cb => cb.value);

    console.log('[saveProvidersAndFacilities] Providers:', selectedProviders);
    console.log('[saveProvidersAndFacilities] Facilities:', selectedFacilities);

    // Save via AJAX
    const formData = new FormData();
    formData.append('csrf_token_form', <?php echo json_encode($csrfToken); ?>);
    formData.append('service_id', serviceId);
    formData.append('providers', selectedProviders.join('|'));
    formData.append('facilities', selectedFacilities.join('|'));

    console.log('[saveProvidersAndFacilities] Sending to: ../admin/save_preferences.php');

    if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
    fetch('../admin/save_preferences.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('[saveProvidersAndFacilities] Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('[saveProvidersAndFacilities] Response data:', data);
        if (data.success) {
            if (window.showToast) {
                window.showToast('Providers and facilities saved successfully', 'success');
            }
            closeExpandedView(serviceId);
            // Reset the tab cache so the next open reflects the saved selection.
            // The tab was rendered once at load time; without this the old
            // server-rendered HTML (with stale checkboxes) persists forever.
            const tabDiv = document.getElementById('tab-subscriptions');
            if (tabDiv) tabDiv.dataset.loaded = 'false';
            if (typeof loadTabContent === 'function') loadTabContent('subscriptions');
        } else {
            if (window.showToast) {
                window.showToast('Error: ' + (data.error || 'Unknown error'), 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.showToast) {
            window.showToast('Error saving selection', 'error');
        }
    });
}

// Add service
function addService(serviceId) {
    console.log('[addService] Adding service:', serviceId);

    const svcDef = window.serviceDefinitions?.[serviceId];
    const isProviderBased = svcDef?.scope === 'provider' || svcDef?.provider_based === true;
    const hasKnownFreePrice = svcDef && svcDef.price !== null && parseFloat(svcDef.price) <= 0;

    // One-click activation for free, practice-scoped services.
    // No cart review/payment gate is needed when total charge is $0.00.
    if (!isProviderBased && hasKnownFreePrice) {
        activateFreeServiceNow(serviceId);
        return;
    }

    // For per-provider services, show the provider picker so the user can
    // choose exactly which (and how many) providers to subscribe for.
    if (isProviderBased && window.providerList && window.providerList.length > 0) {
        showProviderPickerModal(serviceId);
        return;
    }

    // Practice-scoped (flat) or no provider list: add directly with quantity = 1
    _doAddService(serviceId, 1, []);
}

function activateFreeServiceNow(serviceId) {
    const card = document.querySelector(`.service-card[data-service="${serviceId}"]`);
    if (card) {
        card.classList.add('pending-add');
        card.style.pointerEvents = 'none';
        card.style.opacity = '0.78';
    }
    showToast('Activating free service...', 'info');

    const requestData = {
        csrf_token: <?php echo json_encode($csrfToken); ?>,
        add: [{ serviceId: serviceId, quantity: 1, providerIds: [] }],
        remove: [],
        use_existing_payment: false,
        dev_bypass: false,
        providers: {}
    };

    if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') {
        top.restoreSession();
    }

    fetch('<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/admin/process_subscription.php?site=<?php echo urlencode($_SESSION['site_id'] ?? 'default'); ?>', {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(function(response) {
        if (!response.ok) {
            return response.text().then(function(txt) {
                throw new Error('HTTP ' + response.status + ': ' + (txt || 'activation failed'));
            });
        }
        return response.json();
    })
    .then(function(data) {
        if (!data || !data.success) {
            throw new Error((data && data.error) ? data.error : 'Failed to activate service');
        }
        showToast('Service activated', 'success');
        window.location.reload();
    })
    .catch(function(error) {
        if (card) {
            card.style.pointerEvents = '';
            card.style.opacity = '';
            card.classList.remove('pending-add');
        }
        showToast('Activation failed: ' + error.message, 'error');
    });
}

// Show a lightweight inline modal for selecting providers
function showProviderPickerModal(serviceId) {
    const svcDef = window.serviceDefinitions?.[serviceId] || {};
    const svcName = svcDef.name || serviceId;
    const basePrice = svcDef.price || 0;

    // Build checkbox rows
    const providerRows = (window.providerList || []).map(p => {
        const label = (p.fname || '') + ' ' + (p.lname || '');
        return `<label style="display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid #f0f0f0;cursor:pointer;">
            <input type="checkbox" class="prov-picker-cb" value="${p.id}" checked style="width:16px;height:16px;">
            <span>${label.trim()}</span>
        </label>`;
    }).join('');

    const modalHtml = `
    <div id="prov-picker-overlay" style="
        position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.45);
        z-index:9999;display:flex;align-items:center;justify-content:center;">
      <div style="
        background:#fff;border-radius:10px;padding:24px 28px;width:380px;max-width:95vw;
        box-shadow:0 8px 32px rgba(0,0,0,0.22);font-family:inherit;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
          <h3 style="margin:0;font-size:16px;color:#333;">Select Providers</h3>
          <button onclick="document.getElementById('prov-picker-overlay').remove()" style="
            background:none;border:none;font-size:20px;cursor:pointer;color:#666;">&times;</button>
        </div>
        <p style="margin:0 0 12px 0;font-size:13px;color:#555;">
          Choose which providers to enroll in <strong>${svcName}</strong>.
          Billing is <strong>$${basePrice.toFixed(2)}/mo per provider</strong>.
        </p>
        <div style="margin-bottom:4px;display:flex;gap:12px;">
          <a href="#" onclick="document.querySelectorAll('.prov-picker-cb').forEach(c=>c.checked=true);_updatePickerTotal(${basePrice});return false;" style="font-size:12px;color:#0f4b8f;">All</a>
          <a href="#" onclick="document.querySelectorAll('.prov-picker-cb').forEach(c=>c.checked=false);_updatePickerTotal(${basePrice});return false;" style="font-size:12px;color:#0f4b8f;">None</a>
        </div>
        <div style="max-height:200px;overflow-y:auto;margin-bottom:14px;padding:0 4px;">
          ${providerRows}
        </div>
        <div id="picker-total-line" style="font-size:14px;font-weight:600;color:#333;margin-bottom:16px;">
          Total: <span id="picker-total-amt">$${(basePrice * window.providerList.length).toFixed(2)}</span>/mo
          (<span id="picker-total-count">${window.providerList.length}</span> provider${window.providerList.length !== 1 ? 's' : ''})
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
          <button onclick="document.getElementById('prov-picker-overlay').remove()" style="
            padding:8px 18px;border:1px solid #ccc;border-radius:6px;background:#fff;cursor:pointer;font-size:13px;">
            Cancel
          </button>
          <button onclick="_confirmProviderPick('${serviceId}',${basePrice})" style="
            padding:8px 18px;border:none;border-radius:6px;background:#28a745;color:#fff;
            cursor:pointer;font-size:13px;font-weight:600;">
            <i class='fa fa-check'></i> Add to Cart
          </button>
        </div>
      </div>
    </div>`;

    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Wire up checkboxes to live-update the total line
    document.querySelectorAll('.prov-picker-cb').forEach(cb => {
        cb.addEventListener('change', () => _updatePickerTotal(basePrice));
    });
}

function _updatePickerTotal(basePrice) {
    const checked = document.querySelectorAll('.prov-picker-cb:checked');
    const n = checked.length;
    const el = document.getElementById('picker-total-amt');
    const elN = document.getElementById('picker-total-count');
    if (el) el.textContent = '$' + (basePrice * n).toFixed(2);
    if (elN) elN.textContent = n + ' provider' + (n !== 1 ? 's' : '');
}

function _confirmProviderPick(serviceId, basePrice) {
    const checked = Array.from(document.querySelectorAll('.prov-picker-cb:checked'));
    if (checked.length === 0) {
        alert('Please select at least one provider.');
        return;
    }
    const providerIds = checked.map(cb => parseInt(cb.value));
    document.getElementById('prov-picker-overlay').remove();
    _doAddService(serviceId, providerIds.length, providerIds);
}

// Core: add a service with quantity + provider list to pendingChanges
function _doAddService(serviceId, quantity, providerIds) {
    if (!window.pendingChanges) {
        window.pendingChanges = { add: [], remove: [] };
    }

    // If it was queued for removal, undo that instead
    const removeIndex = window.pendingChanges.remove.indexOf(serviceId);
    if (removeIndex > -1) {
        window.pendingChanges.remove.splice(removeIndex, 1);
    } else {
        // Only add once per service (replace if already present)
        const existingIdx = window.pendingChanges.add.findIndex(
            item => (typeof item === 'string' ? item : item.serviceId) === serviceId
        );
        const entry = { serviceId, quantity: quantity || 1, providerIds: providerIds || [] };
        if (existingIdx > -1) {
            window.pendingChanges.add[existingIdx] = entry;
        } else {
            window.pendingChanges.add.push(entry);
        }
    }

    console.log('[_doAddService] pendingChanges:', window.pendingChanges);

    updateCart();
    showCartPanel();
    showReviewButton();

    const card = document.querySelector(`.service-card[data-service="${serviceId}"]`);
    if (card) {
        card.classList.add('pending-add');
        console.log('[_doAddService] Added pending-add class to card');
    }
}

// Show cart panel and switch to 2-column layout
function showCartPanel() {
    const container = document.getElementById('subscription-container');
    console.log('[showCartPanel] container:', container);
    if (container) {
        container.classList.remove('subscription-layout-full');
        container.classList.add('subscription-layout-two-col');
        console.log('[showCartPanel] Applied subscription-layout-two-col');
    }
}

// Show review changes button
function showReviewButton() {
    const btn = document.getElementById('review-changes-btn');
    console.log('[showReviewButton] btn:', btn, 'pendingChanges:', window.pendingChanges);
    if (btn && window.pendingChanges && (window.pendingChanges.add.length > 0 || window.pendingChanges.remove.length > 0)) {
        btn.style.display = 'block';
        console.log('[showReviewButton] Button displayed');
    }
}

// Update cart display
function updateCart() {
    const pendingSection = document.getElementById('pending-changes-section');
    const reviewBtn = document.getElementById('review-changes-btn');

    if (!pendingSection || !window.pendingChanges) return;

    const totalChanges = window.pendingChanges.add.length + window.pendingChanges.remove.length;

    // Build HTML for pending changes
    let html = '';

    if (totalChanges === 0) {
        pendingSection.style.display = 'none';
    } else {
        pendingSection.style.display = 'block';
        html = '<h4 style="margin-top: 0; margin-bottom: 15px; color: #0f4b8f;"><i class="fas fa-exchange"></i> Pending Changes</h4>';

        // Show services being added
        if (window.pendingChanges.add.length > 0) {
            html += '<div style="margin-bottom: 10px;"><strong style="color: #28a745; font-size: 13px;"><i class="fas fa-plus-circle"></i> Adding:</strong></div>';
            window.pendingChanges.add.forEach(item => {
                const serviceId = typeof item === 'string' ? item : item.serviceId;
                const qty       = typeof item === 'string' ? 1 : (item.quantity || 1);
                const serviceName = getServiceName(serviceId);
                const basePrice = window.serviceDefinitions[serviceId]?.price || 0;
                const lineTotal = basePrice * qty;
                const qtyLabel  = qty > 1 ? ` <small style="color:#666;">(${qty} providers × $${basePrice.toFixed(2)})</small>` : '';
                const removeLink = `<a href="#" onclick="removeFromCart('${serviceId}');return false;" style="margin-left:8px;color:#dc3545;font-size:11px;">✕</a>`;
                html += `
                    <div style="padding: 10px; background: #f0f9f4; border-left: 3px solid #28a745; margin-bottom: 8px; border-radius: 4px; font-size: 14px; display: flex; justify-content: space-between; align-items: center;">
                        <span><i class="fas fa-plus"></i> ${serviceName}${qtyLabel}${removeLink}</span>
                        <span style="color: #28a745; font-weight: bold;">+$${lineTotal.toFixed(2)}</span>
                    </div>
                `;
            });
        }

        // Show services being removed
        if (window.pendingChanges.remove.length > 0) {
            html += '<div style="margin-bottom: 10px; margin-top: 15px;"><strong style="color: #dc3545; font-size: 13px;"><i class="fas fa-minus-circle"></i> Removing:</strong></div>';
            window.pendingChanges.remove.forEach(serviceId => {
                const serviceName = getServiceName(serviceId);
                const price = window.serviceDefinitions[serviceId]?.price || 0;
                html += `
                    <div style="padding: 10px; background: #fff5f5; border-left: 3px solid #dc3545; margin-bottom: 8px; border-radius: 4px; font-size: 14px; display: flex; justify-content: space-between; align-items: center;">
                        <span><i class="fas fa-times"></i> ${serviceName}</span>
                        <span style="color: #dc3545; font-weight: bold;">-$${price.toFixed(2)}</span>
                    </div>
                `;
            });
        }
    }

    pendingSection.innerHTML = html;

    // Calculate new total based on pending changes
    calculateNewTotal();

    // Update the review button text
    if (reviewBtn) {
        if (totalChanges > 0) {
            reviewBtn.innerHTML = `<i class="fas fa-check"></i> Process ${totalChanges} Change${totalChanges > 1 ? 's' : ''}`;
        }
    }
}

// Remove a pending addition from the cart
function removeFromCart(serviceId) {
    if (!window.pendingChanges) return;
    const idx = window.pendingChanges.add.findIndex(
        item => (typeof item === 'string' ? item : item.serviceId) === serviceId
    );
    if (idx > -1) window.pendingChanges.add.splice(idx, 1);
    const card = document.querySelector(`.service-card[data-service="${serviceId}"]`);
    if (card) card.classList.remove('pending-add');
    updateCart();
    if (window.pendingChanges.add.length === 0 && window.pendingChanges.remove.length === 0) {
        const container = document.getElementById('subscription-container');
        if (container) {
            container.classList.remove('subscription-layout-two-col');
            container.classList.add('subscription-layout-full');
        }
        const btn = document.getElementById('review-changes-btn');
        if (btn) btn.style.display = 'none';
    }
}

// Calculate new total based on current subscriptions and pending changes
function calculateNewTotal() {
    const cartTotalElement = document.getElementById('cart-total');
    if (!cartTotalElement) return;

    let newTotal = 0;

    // Start with current subscriptions
    if (window.currentSubscriptions) {
        Object.keys(window.currentSubscriptions).forEach(serviceKey => {
            const sub = window.currentSubscriptions[serviceKey];

            // Skip if this service is being removed
            if (window.pendingChanges && window.pendingChanges.remove.includes(serviceKey)) return;

            let price = 0;
            if (window.serviceDefinitions[serviceKey]) {
                if (typeof sub === 'object' && sub.status === 'active' && sub.active === true) {
                    if (sub.monthly_cost) {
                        price = parseFloat(sub.monthly_cost);
                    } else {
                        const basePrice = window.serviceDefinitions[serviceKey].price || 0;
                        const providerCount = sub.provider_count || 1;
                        price = basePrice * providerCount;
                    }
                }
            }
            if (price > 0) newTotal += price;
        });
    }

    // Add services being added (use quantity × base price)
    if (window.pendingChanges && window.pendingChanges.add.length > 0) {
        window.pendingChanges.add.forEach(item => {
            const serviceId = typeof item === 'string' ? item : item.serviceId;
            const qty       = typeof item === 'string' ? 1 : (item.quantity || 1);
            if (window.serviceDefinitions[serviceId]) {
                const price = (window.serviceDefinitions[serviceId].price || 0) * qty;
                newTotal += parseFloat(price);
            }
        });
    }

    // Round to fix floating point precision issues
    newTotal = Math.round(newTotal * 100) / 100;
    cartTotalElement.textContent = '$' + newTotal.toFixed(2);
}

// Show inline confirmation banner
function showConfirmBanner(serviceId, message, isWarning = false) {
    const card = document.querySelector(`[data-service="${serviceId}"]`);
    if (!card) return;

    // Remove any existing banners
    const existingBanner = card.querySelector('.confirm-banner');
    if (existingBanner) existingBanner.remove();

    // Create banner
    const banner = document.createElement('div');
    banner.className = 'confirm-banner';
    banner.style.cssText = `
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: ${isWarning ? 'rgba(220, 53, 69, 0.95)' : 'rgba(102, 126, 234, 0.95)'};
        color: white;
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 10;
        border-radius: 10px;
    `;

    banner.innerHTML = `
        <div style="text-align: center; margin-bottom: 20px; font-size: 14px; line-height: 1.6;">
            ${message}
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="removeService('${serviceId}'); this.closest('.confirm-banner').remove();"
                    class="btn btn-light" style="padding: 8px 20px;">
                <i class="fas fa-check"></i> Confirm
            </button>
            <button onclick="this.closest('.confirm-banner').remove();"
                    class="btn btn-outline-light" style="padding: 8px 20px; background: rgba(255,255,255,0.2);">
                <i class="fas fa-times"></i> Cancel
            </button>
        </div>
    `;

    card.style.position = 'relative';
    card.appendChild(banner);
}

// Confirm removal with inline banner
function confirmRemoveService(serviceId) {
    const serviceName = getServiceName(serviceId);

    // Special warning for appointment_reminders
    if (serviceId === 'appointment_reminders') {
        const message = `<strong style="font-size: 16px;">⚠️ WARNING</strong><br><br>
            Cancelling ${serviceName} will stop ALL messaging services:<br><br>
            • Appointment Reminders<br>
            • Recall Board<br>
            • Announcements<br>
            • Clinical Reminders<br>
            • Dial-0 Patient Callback<br>
            • Surveys<br><br>
            <em>Your billing will be prorated for the unused portion of this month.</em>`;

        showConfirmBanner(serviceId, message, true);
    } else {
        const message = `Cancel ${serviceName}?<br><br>
            <em>Your billing will be prorated for the unused portion of this month.</em>`;

        showConfirmBanner(serviceId, message, false);
    }
}

// Remove service
function removeService(serviceId) {
    console.log('Removing service:', serviceId);

    // Initialize pending changes
    if (!window.pendingChanges) {
        window.pendingChanges = { add: [], remove: [] };
    }

    // Remove from add list if it was there
    const addIndex = window.pendingChanges.add.indexOf(serviceId);
    if (addIndex > -1) {
        window.pendingChanges.add.splice(addIndex, 1);
    } else {
        // Add to remove list
        if (!window.pendingChanges.remove.includes(serviceId)) {
            window.pendingChanges.remove.push(serviceId);
        }
    }

    // Update cart and show panel
    updateCart();
    showCartPanel();
    showReviewButton();
}

// Get service display name
function getServiceName(serviceId) {
    const fromApi = window.serviceDefinitions?.[serviceId]?.name;
    if (fromApi && String(fromApi).trim() !== '') {
        return fromApi;
    }

    const names = {
        'appointment_reminders': 'Automated Reminders',
        'secure_chat': 'Secure Patient Chat',
        'calendar_export': 'Calendar Export',
        'calendar_full': 'Full Calendar View',
        'calendar_ai': 'Calendar',
        'pdf_management': 'PDF Form Management',
        'vfax': 'vFax',
        'whatsapp': 'WhatsApp Integration',
        'dedicated_number': 'Dedicated 10-DLC Number'
    };
    return names[serviceId] || serviceId;
}

// Show toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        background: ${type === 'error' ? '#dc3545' : type === 'success' ? '#28a745' : '#0f4b8f'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 10000;
        font-size: 14px;
        max-width: 400px;
        animation: slideIn 0.3s ease;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Process subscription changes - send to backend
function processSubscriptionChanges() {
    if (!window.pendingChanges || (window.pendingChanges.add.length === 0 && window.pendingChanges.remove.length === 0)) {
        showToast('No changes to process', 'info');
        return;
    }

    const btn = document.getElementById('review-changes-btn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    }

    // For removals only, no payment needed
    const hasAdditions = window.pendingChanges.add.length > 0;
    const hasRemovals = window.pendingChanges.remove.length > 0;

    // Calculate total cost of additions to determine if payment is needed
    let estimatedTotal = 0;
    if (hasAdditions) {
        window.pendingChanges.add.forEach(item => {
            const sId = typeof item === 'string' ? item : item.serviceId;
            const qty = typeof item === 'string' ? 1 : (item.quantity || 1);
            const svc = window.serviceDefinitions && window.serviceDefinitions[sId];
            if (svc) {
                const price = parseFloat(svc.price || 0);
                const isProviderBased = svc.provider_based || svc.scope === 'provider';
                estimatedTotal += isProviderBased ? (price * qty) : price;
            }
        });
    }

    // Check if we have a Braintree token (indicates payment method on file)
    const hasPaymentOnFile = window.braintreeToken !== null && window.braintreeToken !== undefined;

    // Auto-enable on localhost so test cards aren't needed during dev.
    // Production (non-localhost) always requires real payment.
    const devBypass = <?php echo (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false || strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false) ? 'true' : 'false'; ?>;

    // If adding services, total > 0, and no payment on file, show Braintree payment form
    // Skip payment form for $0.00 totals (demo/free subscriptions)
    if (hasAdditions && estimatedTotal > 0 && !hasPaymentOnFile && !devBypass) {
        window._medexPendingTotal = Number(estimatedTotal || 0);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Process Changes';
        }
        // Fetch a fresh Braintree client token, then show the payment section
        if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
        fetch('<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/admin/get_braintree_token.php?site=<?php echo urlencode($_SESSION['site_id'] ?? 'default'); ?>', {credentials: 'include'})
            .then(r => r.json())
            .then(data => {
                if (!data.success || !data.clientToken) {
                    showToast('Unable to initialise payment form: ' + (data.error || 'no token'), 'error');
                    return;
                }
                window._medexBraintreeToken = data.clientToken;
                const paymentSection = document.getElementById('payment-section');
                if (paymentSection) paymentSection.style.display = 'block';
                window._initMedexPaymentComponents();
            })
            .catch(err => showToast('Payment initialisation error: ' + err.message, 'error'));
        return;
    }

    // Build request data - only use existing payment if token exists
    const requestData = {
        csrf_token: <?php echo json_encode($csrfToken); ?>,
        add: window.pendingChanges.add,
        remove: window.pendingChanges.remove,
        use_existing_payment: hasAdditions && hasPaymentOnFile,
        dev_bypass: devBypass,
        providers: {}
    };

    // Collect provider selections from the items themselves (populated at picker confirm time)
    if (hasAdditions) {
        window.pendingChanges.add.forEach(item => {
            const sId  = typeof item === 'string' ? item : item.serviceId;
            const pIds = typeof item === 'string' ? [] : (item.providerIds || []);
            if (pIds.length > 0) {
                requestData.providers[sId] = pIds;
            }
        });
    }

    // Send to process_subscription.php endpoint (use webroot path)
    if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
    fetch('<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/admin/process_subscription.php?site=<?php echo urlencode($_SESSION['site_id'] ?? 'default'); ?>', {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        if (!response.ok) {
            console.error('HTTP error:', response.status, response.statusText);
            return response.text().then(text => {
                console.error('Response body:', text);
                throw new Error(`HTTP ${response.status}: ${text.substring(0, 200)}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Success response:', data);
        if (data.success) {
            // Reload page to show updated subscriptions
            window.location.reload();
        } else {
            showToast('Error: ' + (data.error || 'Failed to update subscriptions'), 'error');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check"></i> Process Changes';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error processing changes: ' + error.message, 'error');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Confirm Changes';
        }
    });
}

// ========== Braintree Hosted Fields helpers ==========
window._medexPayment = window._medexPayment || {
    token: null,
    clientInstance: null,
    hostedFieldsInstance: null,
    ready: false
};

window._medexShowPaymentError = function(msg) {
    const el = document.getElementById('medex-payment-errors');
    if (!el) { return; }
    el.textContent = msg || '';
    el.style.display = msg ? 'block' : 'none';
};

window._medexInvalidHostedFields = function(instance) {
    if (!instance || typeof instance.getState !== 'function') {
        return [];
    }
    const state = instance.getState();
    const labels = {
        number: 'card number',
        expirationDate: 'expiration',
        cvv: 'CVV',
        postalCode: 'postal code'
    };
    return Object.keys(labels)
        .filter(key => !(state.fields && state.fields[key] && state.fields[key].isValid))
        .map(key => labels[key]);
};

window._medexUpdateSubmitState = function() {
    const baa = document.getElementById('agree-baa');
    const terms = document.getElementById('agree-terms');
    const submitBtn = document.getElementById('payment-submit-btn');
    const enabled = !!(baa && baa.checked && terms && terms.checked);
    if (submitBtn) { submitBtn.disabled = !enabled; }
};

window._medexLoadScript = function(src) {
    return new Promise(function(resolve, reject) {
        const existing = Array.from(document.scripts).find(s => s.src === src);
        if (existing) {
            if (existing.dataset.loaded === '1') {
                resolve();
                return;
            }
            existing.addEventListener('load', () => resolve(), { once: true });
            existing.addEventListener('error', () => reject(new Error('Failed to load ' + src)), { once: true });
            return;
        }
        const s = document.createElement('script');
        s.src = src;
        s.async = true;
        s.onload = function() { s.dataset.loaded = '1'; resolve(); };
        s.onerror = function() { reject(new Error('Failed to load ' + src)); };
        document.head.appendChild(s);
    });
};

window._initMedexPaymentComponents = function() {
    const payment = window._medexPayment;
    if (!window._medexBraintreeToken) { return; }
    if (payment.ready && payment.token === window._medexBraintreeToken) {
        window._medexUpdateSubmitState();
        return;
    }
    payment.ready = false;
    payment.token = window._medexBraintreeToken;
    window._medexShowPaymentError('');

    Promise.all([
        window._medexLoadScript('https://js.braintreegateway.com/web/3.97.2/js/client.min.js'),
        window._medexLoadScript('https://js.braintreegateway.com/web/3.97.2/js/hosted-fields.min.js')
    ]).then(function() {
        braintree.client.create({ authorization: payment.token }, function(clientErr, clientInstance) {
            if (clientErr) {
                window._medexShowPaymentError('Payment setup failed: ' + clientErr.message);
                return;
            }
            payment.clientInstance = clientInstance;
            braintree.hostedFields.create({
                client: clientInstance,
                styles: {
                    input: { 'font-size': '14px', color: '#1f2937' },
                    ':focus': { color: '#111827' }
                },
                fields: {
                    number: { selector: '#medex-card-number', placeholder: '4111 1111 1111 1111' },
                    expirationDate: { selector: '#medex-card-expiration', placeholder: 'MM/YY' },
                    cvv: { selector: '#medex-card-cvv', placeholder: '123' },
                    postalCode: { selector: '#medex-card-postal', placeholder: 'ZIP / Postal' }
                }
            }, function(hfErr, hostedFieldsInstance) {
                if (hfErr) {
                    window._medexShowPaymentError('Card form setup failed: ' + hfErr.message);
                    return;
                }
                payment.hostedFieldsInstance = hostedFieldsInstance;
                payment.ready = true;
                window._medexUpdateSubmitState();
            });
        });
    }).catch(function(err) {
        window._medexShowPaymentError('Payment libraries failed to load: ' + err.message);
    });

    const baa = document.getElementById('agree-baa');
    const terms = document.getElementById('agree-terms');
    if (baa && !baa.dataset.medexBound) {
        baa.dataset.medexBound = '1';
        baa.addEventListener('change', window._medexUpdateSubmitState);
    }
    if (terms && !terms.dataset.medexBound) {
        terms.dataset.medexBound = '1';
        terms.addEventListener('change', window._medexUpdateSubmitState);
    }
    window._medexUpdateSubmitState();
};

window.processPayment = function processPayment() {
    const payment = window._medexPayment;
    if (!payment || !payment.ready) {
        showToast('Payment form not ready', 'error');
        return;
    }
    const submitBtn = document.getElementById('payment-submit-btn');
    if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...'; }
    window._medexShowPaymentError('');

    const submitWithNonce = function(nonce) {
        const requestData = {
            csrf_token: <?php echo json_encode($csrfToken); ?>,
            add: window.pendingChanges.add,
            remove: window.pendingChanges.remove,
            payment_nonce: nonce,
            use_existing_payment: false,
            dev_bypass: false,
            providers: {}
        };
        // Collect provider selections
        if (window.pendingChanges && window.pendingChanges.add) {
            window.pendingChanges.add.forEach(item => {
                const sId  = typeof item === 'string' ? item : item.serviceId;
                const pIds = typeof item === 'string' ? [] : (item.providerIds || []);
                if (pIds.length > 0) { requestData.providers[sId] = pIds; }
            });
        }
        if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
        fetch('<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/admin/process_subscription.php?site=<?php echo urlencode($_SESSION['site_id'] ?? 'default'); ?>', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(requestData)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                showToast('Error: ' + (data.error || 'Failed to process subscription'), 'error');
                if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fas fa-check"></i> Complete Subscription Changes'; }
            }
        })
        .catch(err => {
            showToast('Error: ' + err.message, 'error');
            if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fas fa-check"></i> Complete Subscription Changes'; }
        });
    };

    const cardholderName = (document.getElementById('medex-cardholder-name')?.value || '').trim();
    const invalidFields = window._medexInvalidHostedFields(payment.hostedFieldsInstance);
    if (invalidFields.length > 0) {
        const msg = 'Please verify: ' + invalidFields.join(', ') + '.';
        window._medexShowPaymentError(msg);
        showToast('Payment error: ' + msg, 'error');
        if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fas fa-check"></i> Complete Subscription Changes'; }
        return;
    }
    payment.hostedFieldsInstance.tokenize({
        cardholderName: cardholderName || undefined
    }, function(err, payload) {
        if (err) {
            let msg = err.message || 'Unable to process card details.';
            const detailFields = window._medexInvalidHostedFields(payment.hostedFieldsInstance);
            if (detailFields.length > 0) {
                msg += ' Please verify: ' + detailFields.join(', ') + '.';
            }
            window._medexShowPaymentError(msg);
            showToast('Payment error: ' + msg, 'error');
            if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fas fa-check"></i> Complete Subscription Changes'; }
            return;
        }
        submitWithNonce(payload.nonce);
    });
}

// ========== Secure Chat Audit Trail Functions ==========
let currentChatPage = 1;
let totalChatPages = 1;
let totalChatRecords = 0;
let chatSortColumn = 'date';
let chatSortDirection = 'desc';
let searchDebounceTimer = null;

function debounceSearch() {
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => loadChatAuditData(1), 300);
}

function loadChatAuditData(page) {
    if (typeof top.restoreSession === 'function') {
        top.restoreSession();
    }
    
    currentChatPage = page;
    const perPage = document.getElementById('chatAuditPerPage').value;
    const searchTerm = document.getElementById('chatAuditSearch').value;
    
    const tableBody = document.getElementById('chatAuditTableBody');
    tableBody.innerHTML = `
        <tr>
            <td colspan="6" style="padding: 30px; text-align: center; color: #999;">
                <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i><br>
                Loading...
            </td>
        </tr>
    `;
    
    const formData = new FormData();
    formData.append('csrf_token_form', csrfToken);
    formData.append('action', 'get_secure_chat_audit');
    formData.append('page', page);
    formData.append('per_page', perPage);
    formData.append('sort_column', chatSortColumn);
    formData.append('sort_direction', chatSortDirection);
    formData.append('search', searchTerm);
    
    if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
    fetch('secure_chat_audit.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderChatAuditData(data.records, data.total, data.page, data.per_page);
        } else {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" style="padding: 30px; text-align: center; color: #999;">
                        <i class="fas fa-exclamation-circle" style="font-size: 24px; color: #dc3545;"></i><br>
                        ${data.error || 'Error loading data'}
                    </td>
                </tr>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading chat audit:', error);
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" style="padding: 30px; text-align: center; color: #999;">
                    <i class="fas fa-inbox" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
                    No secure chat activity recorded yet.<br>
                    <small>Patient chats will appear here when the service is active.</small>
                </td>
            </tr>
        `;
        // Reset pagination to empty state
        document.getElementById('chatAuditPageInfo').textContent = '';
        document.getElementById('chatAuditPageNumbers').innerHTML = '';
        updateChatPaginationButtons(0);
    });
}

function renderChatAuditData(records, total, page, perPage) {
    const tableBody = document.getElementById('chatAuditTableBody');
    totalChatRecords = total;
    totalChatPages = Math.ceil(total / perPage) || 1;
    currentChatPage = page;
    
    if (records.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" style="padding: 30px; text-align: center; color: #999;">
                    <i class="fas fa-inbox" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
                    No secure chat activity recorded yet.<br>
                    <small>Patient chats will appear here when the service is active.</small>
                </td>
            </tr>
        `;
    } else {
        let html = '';
        records.forEach(record => {
            const methodIcon = getMethodIcon(record.method);
            const actionBadge = getActionBadge(record.action);
            html += `
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px;">
                        <strong>${escapeHtml(record.patient_name)}</strong><br>
                        <small style="color: #666;">PID: ${record.pid}</small>
                    </td>
                    <td style="padding: 10px;">${formatDateTime(record.created_at)}</td>
                    <td style="padding: 10px;">${actionBadge}</td>
                    <td style="padding: 10px;">${methodIcon} ${record.method || '-'}</td>
                    <td style="padding: 10px;">${escapeHtml(record.user_name || 'System')}</td>
                    <td style="padding: 10px;">
                        <button class="btn btn-sm btn-outline-primary" onclick="viewChatDetail(${record.id})" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="openPatientChart(${record.pid})" title="Open Patient Chart">
                            <i class="fas fa-user"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        tableBody.innerHTML = html;
    }
    
    // Update pagination info
    const start = (page - 1) * perPage + 1;
    const end = Math.min(page * perPage, total);
    document.getElementById('chatAuditPageInfo').textContent = total > 0 
        ? `Showing ${start} to ${end} of ${total} entries` 
        : 'No entries';
    
    // Update page numbers
    renderChatPageNumbers();
    
    // Update button states
    updateChatPaginationButtons(total);
}

function renderChatPageNumbers() {
    const container = document.getElementById('chatAuditPageNumbers');
    let html = '';
    
    const maxButtons = 5;
    let startPage = Math.max(1, currentChatPage - Math.floor(maxButtons / 2));
    let endPage = Math.min(totalChatPages, startPage + maxButtons - 1);
    
    if (endPage - startPage + 1 < maxButtons) {
        startPage = Math.max(1, endPage - maxButtons + 1);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const isActive = i === currentChatPage;
        html += `<button class="btn btn-sm ${isActive ? 'btn-primary' : 'btn-outline-secondary'}" 
                         onclick="loadChatAuditData(${i})">${i}</button>`;
    }
    
    container.innerHTML = html;
}

function updateChatPaginationButtons(total) {
    document.getElementById('chatAuditFirstBtn').disabled = currentChatPage <= 1;
    document.getElementById('chatAuditPrevBtn').disabled = currentChatPage <= 1;
    document.getElementById('chatAuditNextBtn').disabled = currentChatPage >= totalChatPages || total === 0;
    document.getElementById('chatAuditLastBtn').disabled = currentChatPage >= totalChatPages || total === 0;
}

function sortChatAudit(column) {
    // Reset all sort icons
    document.querySelectorAll('[id^="sortIcon-"]').forEach(icon => {
        icon.className = 'fa fa-sort';
    });
    
    // Toggle direction if same column, otherwise default to desc
    if (chatSortColumn === column) {
        chatSortDirection = chatSortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        chatSortColumn = column;
        chatSortDirection = 'desc';
    }
    
    // Update icon for active column
    const icon = document.getElementById(`sortIcon-${column}`);
    if (icon) {
        icon.className = `fa fa-sort-${chatSortDirection === 'asc' ? 'up' : 'down'}`;
    }
    
    loadChatAuditData(1);
}

function getMethodIcon(method) {
    switch (method?.toLowerCase()) {
        case 'sms': return '<i class="fas fa-sms" style="color: #28a745;"></i>';
        case 'email': return '<i class="fas fa-envelope" style="color: #007bff;"></i>';
        case 'copy': return '<i class="fas fa-copy" style="color: #6c757d;"></i>';
        default: return '<i class="fas fa-question-circle" style="color: #aaa;"></i>';
    }
}

function getActionBadge(action) {
    switch (action?.toLowerCase()) {
        case 'link_sent':
            return '<span style="background: #e3f2fd; color: #1976d2; padding: 2px 8px; border-radius: 4px; font-size: 12px;">Link Sent</span>';
        case 'link_opened':
            return '<span style="background: #e8f5e9; color: #388e3c; padding: 2px 8px; border-radius: 4px; font-size: 12px;">Link Opened</span>';
        case 'message_sent':
            return '<span style="background: #fff3e0; color: #f57c00; padding: 2px 8px; border-radius: 4px; font-size: 12px;">Message Sent</span>';
        case 'message_received':
            return '<span style="background: #f3e5f5; color: #7b1fa2; padding: 2px 8px; border-radius: 4px; font-size: 12px;">Message Received</span>';
        case 'session_ended':
            return '<span style="background: #ffebee; color: #c62828; padding: 2px 8px; border-radius: 4px; font-size: 12px;">Session Ended</span>';
        default:
            return '<span style="background: #f5f5f5; color: #666; padding: 2px 8px; border-radius: 4px; font-size: 12px;">' + escapeHtml(action || 'Unknown') + '</span>';
    }
}

function formatDateTime(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function viewChatDetail(recordId) {
    // Open modal or new window with chat details
    alert('View chat detail #' + recordId + ' - Feature coming soon');
}

function openSecureChatInFrame() {
    // Open secure chat in OpenEMR's iframe system
    if (typeof top.RTop !== 'undefined') {
        top.RTop.location = '<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/public/secure_chat.php';
    } else {
        window.open('<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/public/secure_chat.php', '_blank');
    }
}

function openPatientChart(pid) {
    // Open patient chart in OpenEMR
    if (typeof top.RTop !== 'undefined') {
        top.RTop.location = '<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics.php?set_pid=' + pid;
    } else {
        window.open('<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics.php?set_pid=' + pid, '_blank');
    }
}

function exportChatAudit() {
    if (typeof top.restoreSession === 'function') {
        top.restoreSession();
    }
    
    const searchTerm = document.getElementById('chatAuditSearch').value;
    const params = new URLSearchParams({
        action: 'export',
        sort_column: chatSortColumn,
        sort_direction: chatSortDirection,
        search: searchTerm,
        csrf_token_form: csrfToken
    });
    
    window.open('secure_chat_audit.php?' + params.toString(), '_blank');
}

// Initialize chat audit when secure_chat section is expanded
document.addEventListener('DOMContentLoaded', function() {
    // Load chat audit data when secure_chat section is visible
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                const el = mutation.target;
                if (el.id === 'expanded-view-secure_chat' && el.style.display !== 'none') {
                    loadChatAuditData(1);
                }
            }
        });
    });
    
    const expandedView = document.getElementById('expanded-view-secure_chat');
    if (expandedView) {
        observer.observe(expandedView, { attributes: true });
        // Also check if it's already visible
        if (expandedView.style.display !== 'none' && expandedView.offsetParent !== null) {
            loadChatAuditData(1);
        }
    }
});
</script>

<!-- ============================================================
     Campaigns Manager Modal
     Opens public/campaigns.php in an SSO-authenticated iframe.
     Triggered by openCampaignsModal(type) from each campaign section.
     ============================================================ -->
<div id="campaigns-modal-overlay" style="
    display:none;
    position:fixed;
    top:0;left:0;right:0;bottom:0;
    background:rgba(0,0,0,0.55);
    z-index:99999;
    align-items:center;
    justify-content:center;
" onclick="handleCampaignsOverlayClick(event)">
    <div style="
        background:#fff;
        border-radius:8px;
        box-shadow:0 8px 40px rgba(0,0,0,0.35);
        width:92vw;
        max-width:1100px;
        height:88vh;
        display:flex;
        flex-direction:column;
        overflow:hidden;
        position:relative;
    " onclick="event.stopPropagation()">
        <iframe
            id="campaigns-modal-iframe"
            src=""
            style="flex:1;border:none;width:100%;display:block;"
            allow="camera; microphone"
            sandbox="allow-same-origin allow-scripts allow-forms allow-popups allow-popups-to-escape-sandbox"
        ></iframe>
    </div>
</div>

<script>
<?php
// Generate SSO token for campaigns access on medexbank.com
$api = new \OpenEMR\Modules\MedEx\MedExAPI();
$ssoToken = '';
try {
    // Force a fresh login so token + practice_id are present and current.
    $loginData = $api->login(true);

    $sessionToken = (string)($loginData['token'] ?? '');
    $practiceId = (string)(
        $loginData['practice_id']
        ?? ($loginData['practice']['P_PID'] ?? '')
    );

    // Fallbacks for legacy payloads
    if ($practiceId === '') {
        try {
            $cfg = $api->getConfig();
            $practiceId = (string)($cfg['practice_id'] ?? '');
        } catch (\Throwable $cfgEx) {
            // ignore; fallback below
        }
    }
    if ($practiceId === '') {
        $pref = sqlQuery("SELECT MedEx_id FROM medex_prefs WHERE MedEx_id IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1");
        $practiceId = (string)($pref['MedEx_id'] ?? '');
    }

    if ($sessionToken !== '' && $practiceId !== '') {
        // Create SSO token with practice_id and session token
        $ssoPayload = [
            'practice_id' => $practiceId,
            'session_token' => $sessionToken,
            'timestamp' => time(),
            'nonce' => bin2hex(random_bytes(16)),
            'site' => (string)($_SESSION['site_id'] ?? 'default')
        ];
        $ssoToken = base64_encode(json_encode($ssoPayload));
    } else {
        error_log('[MedEx] Campaign SSO token not generated: missing token or practice_id');
    }
} catch (\Exception $e) {
    error_log('[MedEx] Failed to generate SSO token: ' . $e->getMessage());
}
$_medexSiteParam = urlencode($_SESSION['site_id'] ?? 'default');
?>
const _medexCampaignsBaseUrl = <?php echo json_encode($ssoToken !== '' ? ('https://api.hipaabank.net/cart/upload/campaigns_sso.php?site=' . $_medexSiteParam . '&sso_token=' . urlencode($ssoToken) . '&type=') : ''); ?>;
const _medexApiToken = <?php echo json_encode((string)($loginData['token'] ?? '')); ?>;
const _medexCampaignsRouteBase = <?php echo json_encode('https://api.hipaabank.net/cart/upload/index.php?route=information/campaigns'); ?>;
const _medexSsoToken = <?php echo json_encode((string)$ssoToken); ?>;

window.openCampaignsModal = function openCampaignsModal(type) {
    const overlay = document.getElementById('campaigns-modal-overlay');
    const iframe  = document.getElementById('campaigns-modal-iframe');
    if (!overlay || !iframe) return;
    let src = '';

    // Reminders/Recall should open native campaigns manager directly with API token
    // so user lands on campaign list/edit UI (not generic login).
    if (_medexSsoToken && (type === 'reminder' || type === 'recall')) {
        const g = (type === 'recall') ? 'rec' : 'rem';
        src = _medexCampaignsRouteBase + '&sso_token=' + encodeURIComponent(_medexSsoToken) + '&g=' + encodeURIComponent(g) + '&embed=1';
    } else if (_medexApiToken && (type === 'reminder' || type === 'recall')) {
        // Legacy fallback path
        const g = (type === 'recall') ? 'rec' : 'rem';
        src = _medexCampaignsRouteBase + '&token=' + encodeURIComponent(_medexApiToken) + '&g=' + encodeURIComponent(g) + '&embed=1';
    } else if (_medexCampaignsBaseUrl) {
        src = _medexCampaignsBaseUrl + encodeURIComponent(type);
    }

    if (!src) {
        if (window.showToast) {
            window.showToast('Campaign manager unavailable: missing MedEx SSO session. Please refresh and try again.', 'error');
        }
        return;
    }
    if (typeof window.medexSetContext === 'function') {
        const label = (type === 'recall') ? 'Recalls' : (type === 'reminder' ? 'Reminders' : 'Campaigns');
        window.medexSetContext(['Dashboard', 'Services', 'Messaging', 'Edit Messaging', label]);
    }
    if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
    iframe.src = src;
    overlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
};

window.closeCampaignsModal = function closeCampaignsModal() {
    const overlay = document.getElementById('campaigns-modal-overlay');
    const iframe  = document.getElementById('campaigns-modal-iframe');
    if (overlay) overlay.style.display = 'none';
    if (iframe)  iframe.src = '';
    document.body.style.overflow = '';
};

window.handleCampaignsOverlayClick = function handleCampaignsOverlayClick(e) {
    if (e.target === document.getElementById('campaigns-modal-overlay')) {
        closeCampaignsModal();
    }
};

// Listen for close signal from the iframe (campaigns.php calls closeCampaigns() which posts a message)
window.addEventListener('message', function(e) {
    console.log('[get_subscriptions] Received postMessage:', e.data);
    if (e.data && e.data.action === 'closeCampaignsModal') {
        console.log('[get_subscriptions] Closing campaigns modal');
        closeCampaignsModal();
    }
    if (e.data && e.data.action === 'campaignUpdated') {
        console.log('[get_subscriptions] Campaign updated, closing modal');
        closeCampaignsModal();
    }
});

// ESC key closes the modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const overlay = document.getElementById('campaigns-modal-overlay');
        if (overlay && overlay.style.display !== 'none') closeCampaignsModal();
    }
});
</script>

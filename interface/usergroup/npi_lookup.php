<?php

/**
 * NPI Lookup Backend Proxy
 *
 * Proxies requests to NPPES API to avoid CORS issues
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2025 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use GuzzleHttp\Client as Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

// Check authorization
if (!AclMain::aclCheckCore('admin', 'practice')) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Verify CSRF token for security
if (
    !CsrfUtils::verifyCsrfToken($_GET['csrf_token'] ?? '')
) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

// Set response header
header('Content-Type: application/json');

// Build NPPES API query parameters
$queryParams = [];

// Supported search parameters
$allowedParams = [
    'number',           // NPI number
    'enumeration_type', // NPI-1 (Individual) or NPI-2 (Organization)
    'taxonomy_description',
    'first_name',
    'last_name',
    'organization_name',
    'address_purpose',  // LOCATION, MAILING, PRIMARY, SECONDARY
    'city',
    'state',
    'postal_code',
    'country_code',
    'limit',
    'skip',
    'version'
];

// Filter and sanitize parameters
foreach ($allowedParams as $param) {
    if (isset($_GET[$param]) && $_GET[$param] !== '') {
        $queryParams[$param] = trim((string) $_GET[$param]);
    }
}

// Set defaults
if (!isset($queryParams['version'])) {
    $queryParams['version'] = '2.1';
}
if (!isset($queryParams['limit'])) {
    $queryParams['limit'] = '20'; // Default to 20 results
}

// Enforce maximum limit of 200 (NPPES API maximum)
if (isset($queryParams['limit']) && $queryParams['limit'] > 200) {
    $queryParams['limit'] = '200';
}

// API base URL (Guzzle will handle query parameters)
$apiUrl = 'https://npiregistry.cms.hhs.gov/api/';

// Log the request (optional - for debugging)
if (!empty($GLOBALS['debug_mode'])) {
    error_log("NPI Lookup Request: " . $apiUrl . '?' . http_build_query($queryParams));
}

try {
    $client = new Client([
        'timeout' => 30,
        'connect_timeout' => 10,
        'verify' => true,
        'http_errors' => true,
    ]);

    $response = $client->request('GET', $apiUrl, [
        'headers' => [
            'Accept' => 'application/json',
            'User-Agent' => 'OpenEMR/NPI-Lookup',
        ],
        'query' => $queryParams, // Guzzle handles query string building
    ]);

    $statusCode = $response->getStatusCode();
    $body = $response->getBody()->getContents();

    // Validate JSON response
    $data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("NPI Lookup JSON Error: " . json_last_error_msg());
        http_response_code(500);
        echo json_encode([
            'error' => 'Invalid response from NPPES Registry',
            'message' => $GLOBALS['debug_mode'] ? json_last_error_msg() : 'Invalid response format'
        ]);
        exit;
    }
} catch (ConnectException $e) {
    // Connection/network errors
    error_log("NPI Lookup Connection Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to connect to NPPES Registry',
        'message' => $GLOBALS['debug_mode'] ? $e->getMessage() : 'Connection error'
    ]);
    exit;
} catch (RequestException $e) {
    // HTTP errors (4xx, 5xx)
    $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 500;
    $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : '';

    error_log("NPI Lookup HTTP Error: " . $statusCode . " - " . $e->getMessage());

    http_response_code($statusCode);
    echo json_encode([
        'error' => 'NPPES Registry returned error',
        'http_code' => $statusCode,
        'message' => $GLOBALS['debug_mode'] ? $e->getMessage() : 'Registry error'
    ]);
    exit;
} catch (\Exception $e) {
    // Other errors
    error_log("NPI Lookup Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Unexpected error occurred',
        'message' => $GLOBALS['debug_mode'] ? $e->getMessage() : 'Server error'
    ]);
    exit;
}

// Return the response
echo json_encode($data);

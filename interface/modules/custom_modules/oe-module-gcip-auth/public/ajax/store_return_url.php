<?php

/**
 * GCIP Return URL Storage AJAX Handler
 * 
 * <!-- AI-Generated Content Start -->
 * This AJAX endpoint handles storing the return URL in the user's session
 * so they can be redirected back to their original location after
 * successful GCIP authentication.
 * <!-- AI-Generated Content End -->
 *
 * @package   OpenEMR GCIP Authentication Module
 * @link      http://www.open-emr.org
 * @author    OpenEMR Development Team
 * @copyright Copyright (c) 2024 OpenEMR Development Team
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Set JSON response headers - AI-Generated
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Only allow POST requests - AI-Generated
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Start session - AI-Generated
session_start();

// Get JSON input - AI-Generated
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['return_url'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// Validate return URL - AI-Generated
$returnUrl = $input['return_url'];

// Basic URL validation - AI-Generated
if (!filter_var($returnUrl, FILTER_VALIDATE_URL) && !str_starts_with($returnUrl, '/')) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid URL format']);
    exit;
}

// Security check: ensure URL is from same domain - AI-Generated
$parsedUrl = parse_url($returnUrl);
if (isset($parsedUrl['host'])) {
    $currentHost = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
    if ($parsedUrl['host'] !== $currentHost) {
        http_response_code(400);
        echo json_encode(['error' => 'External URLs not allowed']);
        exit;
    }
}

// Store return URL in session - AI-Generated
$_SESSION['gcip_return_url'] = $returnUrl;

// Return success response - AI-Generated
echo json_encode(['success' => true]);
exit;
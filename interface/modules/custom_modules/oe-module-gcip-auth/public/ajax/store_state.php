<?php

/**
 * GCIP State Storage AJAX Handler
 * 
 * <!-- AI-Generated Content Start -->
 * This AJAX endpoint handles storing the OAuth2 state parameter in the
 * user's session for CSRF protection during the authentication flow.
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

if (!$input || !isset($input['state'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// Validate state parameter - AI-Generated
$state = $input['state'];
if (!preg_match('/^[a-f0-9]{64}$/', $state)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid state format']);
    exit;
}

// Store state in session - AI-Generated
$_SESSION['gcip_oauth_state'] = $state;

// Return success response - AI-Generated
echo json_encode(['success' => true]);
exit;
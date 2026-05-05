# MedEx SSO Token Implementation Specification

## Overview

The OpenEMR MedEx module now generates SSO tokens for seamless authentication when embedding SaaS pages. This document describes how the MedExBank SaaS platform should validate and process these tokens.

## Token Generation (OpenEMR Side)

### Method: `MedExAPI::generateSSOToken($ttl = 3600)`

Generates a time-limited, HMAC-SHA256 signed token containing:

```php
$payload = [
    'practice_id' => $this->practiceId,     // MedEx Practice ID
    'issued_at' => time(),                   // Unix timestamp
    'expires_at' => time() + $ttl,          // Unix timestamp (default: 1 hour)
    'nonce' => bin2hex(random_bytes(16))    // 32-character random hex string
];

$signature = hash_hmac('sha256', json_encode($payload), $apiKey);

$token = base64_encode(json_encode([
    'payload' => $payload,
    'signature' => $signature
]));
```

### URL Format

SSO tokens are passed to the SaaS via query parameter:

```
https://medexbank.com/cart/upload/index.php?route=account/account&embed=1&sso_token={token}&practice_id={practice_id}
```

**Query Parameters:**
- `route` - Target route (e.g., `account/account`, `account/subscription`)
- `embed` - Set to `1` to indicate iframe embedding (optional: hide header/footer)
- `sso_token` - Base64-encoded JSON token
- `practice_id` - Practice ID for validation

## Token Validation (SaaS Side)

### Step 1: Decode Token

```php
// Decode base64
$decoded = base64_decode($_GET['sso_token']);
if (!$decoded) {
    throw new Exception('Invalid token format');
}

// Parse JSON
$tokenData = json_decode($decoded, true);
if (!$tokenData || !isset($tokenData['payload'], $tokenData['signature'])) {
    throw new Exception('Invalid token structure');
}

$payload = $tokenData['payload'];
$signature = $tokenData['signature'];
```

### Step 2: Validate Payload Structure

```php
$required = ['practice_id', 'issued_at', 'expires_at', 'nonce'];
foreach ($required as $field) {
    if (!isset($payload[$field])) {
        throw new Exception("Missing required field: {$field}");
    }
}
```

### Step 3: Verify Practice Exists

```php
// Query database for practice with this ID
$practice = getPracticeByMedExId($payload['practice_id']);
if (!$practice) {
    throw new Exception('Practice not found');
}

// Get the practice's API key
$apiKey = $practice['api_key'];
```

### Step 4: Verify Signature

```php
// Recreate signature using practice's API key
$expectedSignature = hash_hmac('sha256', json_encode($payload), $apiKey);

// Constant-time comparison to prevent timing attacks
if (!hash_equals($expectedSignature, $signature)) {
    throw new Exception('Invalid signature');
}
```

### Step 5: Check Expiration

```php
if (time() > $payload['expires_at']) {
    throw new Exception('Token expired');
}

// Optional: Prevent clock skew abuse
if ($payload['issued_at'] > time() + 300) {
    throw new Exception('Token issued in future');
}
```

### Step 6: Prevent Replay Attacks (Optional)

Store used nonces in cache/database with TTL:

```php
// Check if nonce was already used
if (nonceExists($payload['nonce'])) {
    throw new Exception('Token already used');
}

// Store nonce with expiration
storeNonce($payload['nonce'], $payload['expires_at']);
```

### Step 7: Auto-Login User

```php
// Create session for the practice
$_SESSION['practice_id'] = $practice['id'];
$_SESSION['practice_name'] = $practice['name'];
$_SESSION['logged_in'] = true;
$_SESSION['sso_login'] = true;  // Flag to indicate SSO login

// Log the SSO login
logActivity('SSO Login', $practice['id'], $_SERVER['REMOTE_ADDR']);

// Redirect to requested route
header("Location: index.php?route={$_GET['route']}&embed={$_GET['embed']}");
exit;
```

## Complete Validation Example

```php
<?php
/**
 * SSO Token Validator for MedExBank SaaS
 * Place in: cart/upload/sso_login.php
 */

function validateSSOToken(string $token, string $practiceId): array
{
    // Step 1: Decode
    $decoded = base64_decode($token);
    if (!$decoded) {
        throw new Exception('Invalid token format');
    }

    $tokenData = json_decode($decoded, true);
    if (!$tokenData || !isset($tokenData['payload'], $tokenData['signature'])) {
        throw new Exception('Invalid token structure');
    }

    $payload = $tokenData['payload'];
    $signature = $tokenData['signature'];

    // Step 2: Validate structure
    $required = ['practice_id', 'issued_at', 'expires_at', 'nonce'];
    foreach ($required as $field) {
        if (!isset($payload[$field])) {
            throw new Exception("Missing field: {$field}");
        }
    }

    // Verify practice_id matches
    if ($payload['practice_id'] !== $practiceId) {
        throw new Exception('Practice ID mismatch');
    }

    // Step 3: Get practice and API key from database
    $query = $db->query("SELECT * FROM practices WHERE medex_practice_id = '" . $db->escape($practiceId) . "'");
    $practice = $query->row;

    if (!$practice) {
        throw new Exception('Practice not found');
    }

    $apiKey = $practice['api_key'];

    // Step 4: Verify signature
    $expectedSignature = hash_hmac('sha256', json_encode($payload), $apiKey);
    if (!hash_equals($expectedSignature, $signature)) {
        throw new Exception('Invalid signature');
    }

    // Step 5: Check expiration
    if (time() > $payload['expires_at']) {
        throw new Exception('Token expired');
    }

    if ($payload['issued_at'] > time() + 300) {
        throw new Exception('Token issued in future');
    }

    // Step 6: Check nonce (optional but recommended)
    $cacheKey = "sso_nonce:{$payload['nonce']}";
    if ($cache->get($cacheKey)) {
        throw new Exception('Token already used');
    }
    $cache->set($cacheKey, true, $payload['expires_at'] - time());

    return $practice;
}

// Main SSO handler
try {
    if (!isset($_GET['sso_token']) || !isset($_GET['practice_id'])) {
        throw new Exception('Missing SSO parameters');
    }

    $practice = validateSSOToken($_GET['sso_token'], $_GET['practice_id']);

    // Create session
    $_SESSION['practice_id'] = $practice['id'];
    $_SESSION['practice_name'] = $practice['name'];
    $_SESSION['logged_in'] = true;
    $_SESSION['sso_login'] = true;

    // Log activity
    $log->write("SSO Login: Practice #{$practice['id']} from " . $_SERVER['REMOTE_ADDR']);

    // Redirect to requested route
    $route = $_GET['route'] ?? 'account/account';
    $embed = $_GET['embed'] ?? '';

    header("Location: index.php?route={$route}&embed={$embed}");
    exit;

} catch (Exception $e) {
    // Log error
    $log->write("SSO Login Failed: " . $e->getMessage());

    // Redirect to login page with error
    header("Location: index.php?route=account/login&error=" . urlencode($e->getMessage()));
    exit;
}
```

## Security Considerations

### 1. **HTTPS Required**
All SSO URLs must use HTTPS to prevent token interception.

### 2. **API Key Protection**
API keys must be:
- Stored securely (hashed or encrypted at rest)
- Never logged or exposed in error messages
- Rotatable when compromised

### 3. **Token Expiration**
- Default TTL: 1 hour (3600 seconds)
- Tokens should NOT be reusable after expiration
- Consider shorter TTL (5-15 minutes) for sensitive operations

### 4. **Replay Attack Prevention**
- Implement nonce tracking with Redis/Memcached
- Store nonces until token expiration
- Use database if cache unavailable (with cleanup job)

### 5. **Rate Limiting**
Limit SSO login attempts per practice:
- 10 attempts per minute per practice
- 100 attempts per hour per practice
- Block on excessive failures

### 6. **Audit Logging**
Log all SSO attempts with:
- Timestamp
- Practice ID
- Source IP
- Success/failure
- Route accessed

## Testing the Implementation

### Test Token Generation

From OpenEMR module console:

```php
require_once 'src/MedExAPI.php';
$api = new \OpenEMR\Modules\MedEx\MedExAPI();
$token = $api->generateSSOToken(60); // 60 second TTL for testing
$url = $api->getSaaSUrl('dashboard');
echo "Test URL: " . $url . "\n";
```

### Test Cases

1. **Valid Token**: Should auto-login and redirect
2. **Expired Token**: Should reject with "Token expired"
3. **Invalid Signature**: Should reject with "Invalid signature"
4. **Reused Token**: Should reject with "Token already used"
5. **Missing Fields**: Should reject with "Missing field: X"
6. **Wrong Practice ID**: Should reject with "Practice ID mismatch"

## Integration Checklist

- [ ] Add SSO validation endpoint (e.g., `sso_login.php`)
- [ ] Update router to intercept `sso_token` parameter
- [ ] Add nonce cache/database table
- [ ] Implement token validation function
- [ ] Add SSO login logging
- [ ] Test with OpenEMR module
- [ ] Add rate limiting
- [ ] Enable HTTPS enforcement
- [ ] Update documentation

## Support

**Questions?** Contact the OpenEMR MedEx module team:
- GitHub Issues: https://github.com/openemr/openemr/issues
- Email: dev@medexbank.com

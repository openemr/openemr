# Migration Guide: From curl_* to GuzzleHttp

This guide helps you migrate from raw `curl_*` functions to GuzzleHttp, the modern HTTP client library used by OpenEMR.

## Why Migrate?

1. **Better Error Handling**: Guzzle throws exceptions for HTTP errors, making error handling more robust
2. **Testability**: Guzzle can be easily mocked in unit tests
3. **PSR-7 Compliance**: Uses standard HTTP message interfaces
4. **Rich Features**: Built-in middleware, retries, authentication, and more
5. **Consistency**: Unified HTTP client usage across OpenEMR codebase

## Common Migration Patterns

### Simple GET Request

**Before (curl):**
```php
$ch = curl_init('https://api.example.com/data');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    error_log("HTTP Error: $httpCode");
    return null;
}

$data = json_decode($response, true);
```

**After (Guzzle):**
```php
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

$client = new Client();

try {
    $response = $client->request('GET', 'https://api.example.com/data');
    $data = json_decode($response->getBody()->getContents(), true);
} catch (GuzzleException $e) {
    error_log("HTTP Error: " . $e->getMessage());
    return null;
}
```

### POST Request with JSON Body

**Before (curl):**
```php
$data = ['key' => 'value'];
$jsonData = json_encode($data);

$ch = curl_init('https://api.example.com/endpoint');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
```

**After (Guzzle):**
```php
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

$data = ['key' => 'value'];
$client = new Client();

try {
    $response = $client->request('POST', 'https://api.example.com/endpoint', [
        'json' => $data  // Automatically sets Content-Type and encodes JSON
    ]);
    
    $result = json_decode($response->getBody()->getContents(), true);
} catch (GuzzleException $e) {
    error_log("HTTP Error: " . $e->getMessage());
    return null;
}
```

### Request with Custom Headers

**Before (curl):**
```php
$ch = curl_init('https://api.example.com/data');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
    'User-Agent: OpenEMR/7.0'
]);

$response = curl_exec($ch);
curl_close($ch);
```

**After (Guzzle):**
```php
use GuzzleHttp\Client;

$client = new Client();
$response = $client->request('GET', 'https://api.example.com/data', [
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json',
        'User-Agent' => 'OpenEMR/7.0'
    ]
]);
```

### File Upload

**Before (curl):**
```php
$filePath = '/path/to/file.pdf';
$ch = curl_init('https://api.example.com/upload');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'file' => new CURLFile($filePath, 'application/pdf', 'document.pdf')
]);

$response = curl_exec($ch);
curl_close($ch);
```

**After (Guzzle):**
```php
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;

$client = new Client();
$response = $client->request('POST', 'https://api.example.com/upload', [
    'multipart' => [
        [
            'name'     => 'file',
            'contents' => Utils::tryFopen('/path/to/file.pdf', 'r'),
            'filename' => 'document.pdf'
        ]
    ]
]);
```

### Using OpenEMR's oeHttp Wrapper

OpenEMR provides a convenient wrapper around Guzzle:

```php
use OpenEMR\Common\Http\oeHttp;

try {
    $response = oeHttp::get('https://api.example.com/data', [
        'headers' => [
            'Authorization' => 'Bearer ' . $token
        ]
    ]);
    
    $data = json_decode($response->getBody()->getContents(), true);
} catch (\Exception $e) {
    error_log("HTTP request failed: " . $e->getMessage());
}
```

## Handling Errors

Guzzle throws different exceptions for different error scenarios:

```php
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;  // 4xx errors
use GuzzleHttp\Exception\ServerException;  // 5xx errors
use GuzzleHttp\Exception\ConnectException; // Connection failures
use GuzzleHttp\Exception\RequestException; // General request errors

$client = new Client();

try {
    $response = $client->request('GET', 'https://api.example.com/data');
} catch (ClientException $e) {
    // 400-level errors (client errors)
    $statusCode = $e->getResponse()->getStatusCode();
    error_log("Client error: $statusCode - " . $e->getMessage());
} catch (ServerException $e) {
    // 500-level errors (server errors)
    $statusCode = $e->getResponse()->getStatusCode();
    error_log("Server error: $statusCode - " . $e->getMessage());
} catch (ConnectException $e) {
    // Connection failures (timeout, DNS, etc.)
    error_log("Connection failed: " . $e->getMessage());
} catch (RequestException $e) {
    // Other request-related errors
    error_log("Request failed: " . $e->getMessage());
}
```

## Setting Timeouts

**Before (curl):**
```php
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
```

**After (Guzzle):**
```php
$client = new Client();
$response = $client->request('GET', 'https://api.example.com/data', [
    'timeout' => 30,        // Total timeout
    'connect_timeout' => 10 // Connection timeout
]);
```

## SSL/TLS Options

**Before (curl):**
```php
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_CAINFO, '/path/to/cacert.pem');
```

**After (Guzzle):**
```php
$client = new Client();
$response = $client->request('GET', 'https://api.example.com/data', [
    'verify' => '/path/to/cacert.pem',  // Path to CA bundle, or false to disable
    // Or use boolean: 'verify' => true/false
]);
```

## Additional Resources

- [Guzzle Documentation](https://docs.guzzlephp.org/)
- [PSR-7: HTTP Message Interface](https://www.php-fig.org/psr/psr-7/)
- OpenEMR's oeHttp: `src/Common/Http/oeHttp.php`
- OpenEMR's oeOAuth: `src/Common/Http/oeOAuth.php`

## Getting Help

If you need help migrating a complex curl usage pattern, please:
1. Check existing Guzzle usage in the OpenEMR codebase for examples
2. Look at `src/Common/Http/oeHttp.php` for the convenience wrapper
3. Ask in the OpenEMR development chat
4. Consult the Guzzle documentation

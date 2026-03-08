# PHPStan Custom Rules for OpenEMR

This directory contains custom PHPStan rules to enforce modern coding patterns in OpenEMR.

## Rules

### ForbiddenGlobalsAccessRule

**Purpose:** Prevents direct `$GLOBALS` array access in favor of `OEGlobalsBag::getInstance()`.

**What it catches:**
- `$GLOBALS['key']` - Direct array access (single or double quotes)
- `$GLOBALS["key"]` - Direct array access with double quotes
- `$value = $GLOBALS['setting']` - Variable assignment from $GLOBALS
- `function($GLOBALS['param'])` - Passing $GLOBALS values as parameters

**What it doesn't catch (intentionally):**
- `global $GLOBALS;` - Global declarations (rare edge case, can be addressed separately if needed)
- References to `$GLOBALS` in comments or strings

**Rationale:**
- **Testability** - `OEGlobalsBag` can be mocked in unit tests
- **Type safety** - Proper return types instead of mixed
- **Dependency injection** - Can be injected into constructors
- **Consistency** - Centralized access pattern for globals
- **Gradual deprecation** - Path to eventually removing `$GLOBALS` superglobal dependency

**Before (❌ Forbidden):**
```php
$value = $GLOBALS['some_setting'];
```

**After (✅ Recommended):**
```php
use OpenEMR\Core\OEGlobalsBag;

$globals = OEGlobalsBag::getInstance();
$value = $globals->get('some_setting');

// Note: For encrypted values, you still need CryptoGen:
use OpenEMR\Common\Crypto\CryptoGen;

$cryptoGen = new CryptoGen();
$apiKey = $cryptoGen->decryptStandard($globals->get('gateway_api_key'));
```

### ForbiddenFunctionsRule

**Purpose:** Prevents use of legacy functions:
- Legacy `sql.inc.php` functions in the `src/` directory
- `call_user_func()` and `call_user_func_array()` functions (use modern PHP syntax instead)
- `error_log()` function (use `SystemLogger` instead)

**Rationale for SQL functions:** Contributors should use `QueryUtils` or `DatabaseQueryTrait` instead for modern database patterns.

**Rationale for call_user_func:**
- Modern PHP supports **uniform variable syntax** for dynamic function calls
- The **argument unpacking operator** (`...`) provides cleaner syntax
- Variadic functions with `...$args` are more readable than array-based arguments
- Better static analysis and IDE support with modern syntax

**Before (❌ Forbidden):**
```php
// Legacy dynamic function calls
$result = call_user_func('myFunction', $arg1, $arg2);
$result = call_user_func_array('myFunction', [$arg1, $arg2]);
$result = call_user_func([$object, 'method'], $arg1);
$result = call_user_func_array([$object, 'method'], $args);
```

**After (✅ Recommended):**
```php
// Modern PHP 7+ syntax
$result = myFunction($arg1, $arg2);

// Dynamic function name
$functionName = 'myFunction';
$result = $functionName($arg1, $arg2);

// With argument unpacking
$args = [$arg1, $arg2];
$result = $functionName(...$args);

// Object method calls
$result = $object->method($arg1);
// or with callable syntax
$callable = [$object, 'method'];
$result = $callable($arg1, $arg2);
// or with argument unpacking
$result = $callable(...$args);
```

**Rationale for error_log:**
- **Structured logging** - `SystemLogger` supports PSR-3 log levels and context arrays
- **Centralized configuration** - Log destinations and formats can be configured globally
- **Testability** - `SystemLogger` can be mocked in unit tests
- **Consistency** - Uniform logging pattern across the codebase

**Before (❌ Forbidden):**
```php
error_log("Something went wrong: " . $error);
error_log("User {$userId} logged in");
```

**After (✅ Recommended):**
```php
use OpenEMR\Common\Logging\SystemLogger;

$logger = new SystemLogger();
$logger->error("Something went wrong", ['error' => $error]);
$logger->info("User logged in", ['userId' => $userId]);
```

### ForbiddenClassesRule

**Purpose:** Prevents use of `laminas-db` classes outside of the `zend_modules` directory.

**Rationale:** Laminas-DB is deprecated and scheduled for removal.

### NoCoversAnnotationRule

**Purpose:** Prevents use of `@covers` annotations in test method docblocks.

**Rationale:** The `@covers` annotation in PHPUnit tests causes any code that is used transitively or ancillary to the annotated code to be excluded from coverage reports. This results in incomplete coverage information and makes it harder to understand which code paths are actually being exercised by our test suite.

**Before (❌ Forbidden):**
```php
/**
 * @covers \OpenEMR\Services\SomeService
 */
public function testSomeMethod(): void
{
    // test code
}
```

**After (✅ Recommended):**
```php
public function testSomeMethod(): void
{
    // test code - coverage is tracked automatically for all exercised code
}
```

### NoCoversAnnotationOnClassRule

**Purpose:** Prevents use of `@covers` annotations in test class docblocks.

**Rationale:** Same as `NoCoversAnnotationRule` - class-level `@covers` annotations also exclude transitively used code from coverage reports.

**Before (❌ Forbidden):**
```php
/**
 * @covers \OpenEMR\Services\SomeService
 */
class SomeServiceTest extends TestCase
{
    // tests
}
```

**After (✅ Recommended):**
```php
class SomeServiceTest extends TestCase
{
    // tests - coverage is tracked automatically for all exercised code
}
```

### Disallowed empty() (via phpstan-strict-rules)

**Purpose:** Prevents use of the `empty()` language construct.

**What it catches:**
- `if (empty($var))` - Empty check on any variable
- `empty($array['key'])` - Empty check on array access
- `!empty($value)` - Negated empty checks

**Rationale:**
- **Surprising behavior** - `empty("0")` returns `true` (string "0" is considered empty)
- **Silent failures** - `empty()` on undefined variables returns `true` without warning
- **Type confusion** - `empty(0)`, `empty(0.0)`, `empty([])`, `empty(null)`, `empty(false)`, and `empty("")` all return `true`

**Before (❌ Forbidden):**
```php
if (empty($value)) {
    // What are we actually checking for?
}
```

**After (✅ Recommended):**
```php
// Be explicit about what you're checking
if ($value === null) {              // Check for null
if ($value === '') {                // Check for empty string
if ($value === null || $value === '') {  // Check for null or empty string
if (count($array) === 0) {          // Check for empty array
if (!$array) {                      // Boolean check on array (empty array is falsy)

// For checking if a variable or key exists
if (isset($var)) {                  // Check if variable is set and not null
if (isset($array['key'])) {         // Check if array key exists and is not null
if (array_key_exists('key', $array)) {  // Check if array key exists (even if null)
```

**Configuration:** This rule is provided by `phpstan/phpstan-strict-rules` with only `disallowedEmpty` enabled:

```yaml
parameters:
  strictRules:
    allRules: false
    disallowedEmpty: true
```

### ForbiddenCurlFunctionsRule

**Purpose:** Prevents use of raw `curl_*` functions throughout the codebase.

**What it catches:**
- `curl_init()` - Initialize a cURL session
- `curl_setopt()` - Set an option for a cURL transfer
- `curl_exec()` - Execute a cURL session
- `curl_close()` - Close a cURL session
- Any other `curl_*` function calls

**Rationale:**
- **Testability** - GuzzleHttp can be easily mocked in unit tests
- **PSR-7 Compliance** - Standard HTTP message interfaces
- **Error Handling** - Better exception handling and error messages
- **Maintainability** - Consistent HTTP client usage across the codebase
- **Features** - Built-in middleware, authentication, retries, and more

**Before (❌ Forbidden):**
```php
$ch = curl_init('https://api.example.com/data');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer token']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    // handle error
}

$data = json_decode($response, true);
```

**After (✅ Recommended):**
```php
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

try {
    $client = new Client();
    $response = $client->request('GET', 'https://api.example.com/data', [
        'headers' => [
            'Authorization' => 'Bearer token'
        ]
    ]);

    $data = json_decode($response->getBody()->getContents(), true);
} catch (GuzzleException $e) {
    // handle error with proper exception
    (new SystemLogger())->error('API request failed', ['exception' => $e]);
}
```

**Or using OpenEMR's oeHttp wrapper:**
```php
use OpenEMR\Common\Http\oeHttp;

$response = oeHttp::get('https://api.example.com/data', [
    'headers' => [
        'Authorization' => 'Bearer token'
    ]
]);

$data = json_decode($response->getBody()->getContents(), true);
```

## Baseline

Existing violations are recorded in `.phpstan/baseline/` as individual PHP files, organized by error type. The `loader.php` file includes all baseline files. New code should follow the patterns documented above.

To regenerate the baseline after fixing violations:

```bash
composer phpstan-baseline
```

## Running PHPStan

```bash
composer phpstan
```

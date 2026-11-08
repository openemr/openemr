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
- `error_log()` function (use `Psr\Log\LoggerInterface` instead, through OpenEMR\BC\ServiceContainer::getLogger() if needed)

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
- **Structured logging** - Use PSR-3 log levels and context arrays
- **Centralized configuration** - Log destinations and formats can be configured globally
- **Testability** - `LoggerInterface` can be mocked in unit tests
- **Consistency** - Uniform logging pattern across the codebase

**Before (❌ Forbidden):**
```php
error_log("Something went wrong: " . $error);
error_log("User {$userId} logged in");
```

**After (✅ Recommended):**
```php
use OpenEMR\BC\ServiceContainer();

$logger = ServiceContainer::getLogger();
$logger->error("Something went wrong", ['error' => $error]);
$logger->info("User logged in", ['userId' => $userId]);
```

### ForbiddenClassesRule

**Purpose:** Prevents use of `laminas-db` classes outside of the `zend_modules` directory.

**Rationale:** Laminas-DB is deprecated and scheduled for removal.

### ForbiddenCoversRule

**Purpose:** Prevents use of `@covers` docblock annotations and `#[CoversClass]`/`#[CoversFunction]` PHP attributes in test files.

**Rationale:** These annotations restrict PHPUnit's coverage attribution to only the listed symbols, which causes transitively used code to be excluded from coverage reports. This results in test file lines showing 0% in codecov patch coverage reports on test-only PRs.

**Before (❌ Forbidden):**
```php
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @covers \OpenEMR\Services\SomeService
 */
#[CoversClass(SomeService::class)]
class SomeServiceTest extends TestCase
{
    /**
     * @covers \OpenEMR\Services\SomeService::someMethod
     */
    public function testSomeMethod(): void
    {
        // test code
    }
}
```

**After (✅ Recommended):**
```php
class SomeServiceTest extends TestCase
{
    public function testSomeMethod(): void
    {
        // test code - coverage is tracked automatically for all exercised code
    }
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
    $this->logger->error('API request failed', ['exception' => $e]);
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

To wipe and rebuild the baseline from scratch (ignoring whatever is currently on disk):

```bash
composer phpstan-baseline-reset
```

This deletes every file under `.phpstan/baseline/`, writes a minimal empty `loader.php`, and then runs `composer phpstan-baseline` to rebuild the per-identifier files from the current codebase. Use this instead of `composer phpstan-baseline` when you want a clean regeneration — for example, when the baseline has drifted, or when the existing files are in a state PHPStan can't even load (leftover merge conflict markers, a truncated file, etc.).

### Fatal-category caps

Certain baseline files hold errors for code that cannot run at load or call time. `.phpstan/fatal-baseline-caps.php` records the current count per file and `tests/Tests/Isolated/PHPStan/FatalBaselineCapsIsolatedTest.php` asserts the actual count equals the cap. Two modes:

- **Whole-file (`all`)** — every baseline entry counts. Used for identifiers where each entry is a symbol that simply doesn't exist:
  - `class.notFound`, `method.notFound`, `staticMethod.notFound`, `trait.notFound`, `interface.notFound`
  - `function.notFound`
  - `classConstant.notFound`, `constant.notFound`
  - `include.fileNotFound`, `includeOnce.fileNotFound`, `requireOnce.fileNotFound`, `require.fileNotFound`
  - `return.missing`, `variable.undefined`

- **Confident non-object (`confidentNonObject`)** — only entries whose reported type narrows to a definitely-non-object (e.g. `on bool`, `on null`, `on array`) count. PHPStan also fires `*.nonObject` on `mixed` and class-union types like `SomeClass|null`; those aren't certain crashes and are excluded. Covered identifiers:
  - `method.nonObject`, `staticMethod.nonObject`
  - `property.nonObject`, `classConstant.nonObject`
  - `clone.nonObject`

If you regenerate the baseline and a count changes:

- **Count went down** — lower the cap in `fatal-baseline-caps.php` to match.
- **Count went up** — fix the underlying code instead of raising the cap. Each entry resolves to one of four fixes: delete dead code, wrap optional code in `class_exists()` / `function_exists()` / `defined()`, add a PHPStan stub, or install the missing dependency.

See openemr/openemr#11792 for the plan to drive every cap to zero.

## Running PHPStan

```bash
composer phpstan
```

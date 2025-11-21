# Migration Guide: From $GLOBALS to OEGlobalsBag

This guide helps developers migrate from direct `$GLOBALS` access to using `OEGlobalsBag`.

## Why Migrate?

- **Testability**: OEGlobalsBag can be mocked in unit tests
- **Type Safety**: Returns proper types instead of `mixed`
- **Dependency Injection**: Can be injected into constructors
- **Automatic Decryption**: Handles encrypted values transparently
- **Future-Ready**: Prepares for integration with external secrets providers

## Basic Usage

### Before (❌ Old Pattern)
```php
$siteName = $GLOBALS['sitename'];
$timeout = $GLOBALS['timeout'];
$webRoot = $GLOBALS['webroot'];
```

### After (✅ New Pattern)
```php
use OpenEMR\Core\OEGlobalsBag;

$globals = OEGlobalsBag::getInstance();
$siteName = $globals->get('sitename');
$timeout = $globals->get('timeout');
$webRoot = $globals->get('webroot');
```

## Encrypted Values

### Before (❌ Old Pattern)
```php
use OpenEMR\Common\Crypto\CryptoGen;

$cryptoGen = new CryptoGen();
$apiKey = $cryptoGen->decryptStandard($GLOBALS['gateway_api_key']);
$password = $cryptoGen->decryptStandard($GLOBALS['database_password']);
```

### After (✅ New Pattern)
```php
use OpenEMR\Core\OEGlobalsBag;

$globals = OEGlobalsBag::getInstance();
// OEGlobalsBag handles decryption automatically
$apiKey = $globals->get('gateway_api_key');
$password = $globals->get('database_password');
```

## Setting Values

### Before (❌ Old Pattern)
```php
$GLOBALS['some_setting'] = 'new value';
$GLOBALS['another_setting'] = 42;
```

### After (✅ New Pattern)
```php
use OpenEMR\Core\OEGlobalsBag;

$globals = OEGlobalsBag::getInstance();
$globals->set('some_setting', 'new value');
$globals->set('another_setting', 42);
```

## Dependency Injection

For classes, prefer dependency injection over singleton:

### Good (✅ Dependency Injection)
```php
namespace OpenEMR\Services;

use OpenEMR\Core\OEGlobalsBag;

class MyService
{
    private OEGlobalsBag $globals;
    
    public function __construct(OEGlobalsBag $globals)
    {
        $this->globals = $globals;
    }
    
    public function doSomething(): void
    {
        $setting = $this->globals->get('some_setting');
        // use $setting
    }
}

// Usage
$globals = OEGlobalsBag::getInstance();
$service = new MyService($globals);
```

### Also Good (✅ Singleton Access)
```php
namespace OpenEMR\Services;

use OpenEMR\Core\OEGlobalsBag;

class MyService
{
    public function doSomething(): void
    {
        $globals = OEGlobalsBag::getInstance();
        $setting = $globals->get('some_setting');
        // use $setting
    }
}
```

## Checking if a Key Exists

### Before (❌ Old Pattern)
```php
if (isset($GLOBALS['some_key'])) {
    $value = $GLOBALS['some_key'];
}
```

### After (✅ New Pattern)
```php
use OpenEMR\Core\OEGlobalsBag;

$globals = OEGlobalsBag::getInstance();
if ($globals->has('some_key')) {
    $value = $globals->get('some_key');
}
```

## Iterating Over Globals

### Before (❌ Old Pattern)
```php
foreach ($GLOBALS as $key => $value) {
    // process each global
}
```

### After (✅ New Pattern)
```php
use OpenEMR\Core\OEGlobalsBag;

$globals = OEGlobalsBag::getInstance();
foreach ($globals as $key => $value) {
    // process each global
}
```

## Common Patterns

### Pattern 1: Configuration Values
```php
// Before
$timezone = $GLOBALS['gbl_time_zone'] ?? 'UTC';

// After
use OpenEMR\Core\OEGlobalsBag;
$globals = OEGlobalsBag::getInstance();
$timezone = $globals->get('gbl_time_zone', 'UTC');  // with default
```

### Pattern 2: Database Configuration
```php
// Before
$dbHost = $GLOBALS['host'];
$dbName = $GLOBALS['dbase'];

// After
use OpenEMR\Core\OEGlobalsBag;
$globals = OEGlobalsBag::getInstance();
$dbHost = $globals->get('host');
$dbName = $globals->get('dbase');
```

### Pattern 3: Feature Flags
```php
// Before
$enableFeature = $GLOBALS['enable_some_feature'];

// After
use OpenEMR\Core\OEGlobalsBag;
$globals = OEGlobalsBag::getInstance();
$enableFeature = $globals->get('enable_some_feature');
```

## Testing with OEGlobalsBag

OEGlobalsBag makes testing easier:

```php
use PHPUnit\Framework\TestCase;
use OpenEMR\Core\OEGlobalsBag;

class MyServiceTest extends TestCase
{
    public function testSomething(): void
    {
        // Create a test instance with known values
        $testGlobals = new OEGlobalsBag([
            'some_setting' => 'test value',
            'timeout' => 60
        ]);
        
        $service = new MyService($testGlobals);
        // Test your service
    }
}
```

## Gradual Migration

You don't need to migrate everything at once:

1. **New code**: Always use `OEGlobalsBag`
2. **Modified code**: When touching a file, migrate the `$GLOBALS` access in that section
3. **Existing code**: Can remain unchanged (it's in the baseline)

## PHPStan Enforcement

The new `ForbiddenGlobalsAccessRule` will catch direct `$GLOBALS` access in your code:

```
------ ERROR -------------------------------------------------------
 Direct access to $GLOBALS is forbidden. Use OEGlobalsBag::getInstance()->get() instead.
 💡 For encrypted values, OEGlobalsBag handles decryption automatically. See src/Core/OEGlobalsBag.php
```

## Questions?

See `src/Core/OEGlobalsBag.php` for the full implementation and available methods.

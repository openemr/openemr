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

**Purpose:** Prevents use of legacy `sql.inc.php` functions in the `src/` directory.

**Rationale:** Contributors should use `QueryUtils` or `DatabaseQueryTrait` instead for modern database patterns.

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

## Baseline

Existing violations of these rules are recorded in `phpstan-database-baseline.neon` so they won't cause errors. However, new code should follow these patterns.

## Running PHPStan

```bash
vendor/bin/phpstan --memory-limit=8G analyze
```

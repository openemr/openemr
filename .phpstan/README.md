# PHPStan Custom Rules for OpenEMR

This directory contains custom PHPStan rules to enforce modern coding patterns in OpenEMR.

## Rules

### ForbiddenGlobalsAccessRule

**Purpose:** Prevents direct `$GLOBALS` access in favor of `OEGlobalsBag::getInstance()`.

**Rationale:**
- **Testability** - `OEGlobalsBag` can be mocked in unit tests
- **Type safety** - Proper return types instead of mixed
- **Dependency injection** - Can be injected into constructors
- **Secrets management** - Handles encryption/decryption internally and enables future integration with external secrets providers (Vault, AWS Secrets Manager, etc.)
- **Gradual deprecation** - Path to eventually removing `$GLOBALS` superglobal dependency

**Before (❌ Forbidden):**
```php
$value = $GLOBALS['some_setting'];

// Or for encrypted values:
$cryptoGen = new CryptoGen();
$apiKey = $cryptoGen->decryptStandard($GLOBALS['gateway_api_key']);
```

**After (✅ Recommended):**
```php
use OpenEMR\Core\OEGlobalsBag;

$globals = OEGlobalsBag::getInstance();
$value = $globals->get('some_setting');
$apiKey = $globals->get('gateway_api_key');  // handles decryption internally
```

### ForbiddenFunctionsRule

**Purpose:** Prevents use of legacy `sql.inc.php` functions in the `src/` directory.

**Rationale:** Contributors should use `QueryUtils` or `DatabaseQueryTrait` instead for modern database patterns.

### ForbiddenClassesRule

**Purpose:** Prevents use of `laminas-db` classes outside of the `zend_modules` directory.

**Rationale:** Laminas-DB is deprecated and scheduled for removal.

## Baseline

Existing violations of these rules are recorded in `phpstan-database-baseline.neon` so they won't cause errors. However, new code should follow these patterns.

## Running PHPStan

```bash
vendor/bin/phpstan --memory-limit=8G analyze
```

## Adding to Baseline

If you need to add more violations to the baseline (e.g., for legacy code), you can regenerate it:

```bash
vendor/bin/phpstan --memory-limit=8G analyze --generate-baseline=.phpstan/phpstan-database-baseline.neon
```

Note: Only add existing violations to the baseline. New code should not violate these rules.

# Isolated Testing Suite

This directory contains an isolated testing setup that allows you to run PHPUnit tests without database or service dependencies.

## Problem

The default OpenEMR test bootstrap loads `interface/globals.php`, which:
- Connects to the database through `library/sql.inc.php`
- Loads configuration from `sqlconf.php`
- Initializes various OpenEMR services and dependencies

This prevents running simple unit tests without a full OpenEMR environment.

## Solution

The isolated testing suite provides:
- `tests/bootstrap-isolated.php` - Minimal bootstrap with only Composer autoloader
- `phpunit-isolated.xml` - PHPUnit configuration for isolated tests
- `tests/Tests/Isolated/` - Directory for dependency-free tests

## Usage

### Run Isolated Tests Only
```bash
vendor/bin/phpunit -c phpunit-isolated.xml
```

### Run Specific Test Suite
```bash
vendor/bin/phpunit -c phpunit-isolated.xml --testsuite isolated
vendor/bin/phpunit -c phpunit-isolated.xml --testsuite unit-isolated
```

### Run Single Test File
```bash
vendor/bin/phpunit -c phpunit-isolated.xml tests/Tests/Isolated/ExampleIsolatedTest.php
```

## Writing Isolated Tests

1. Place tests in `tests/Tests/Isolated/` directory, mirroring the `src/` structure
   - For `src/Validators/` classes → `tests/Tests/Isolated/Validators/`
   - For `src/Services/` classes → `tests/Tests/Isolated/Services/`
   - For `src/Common/` classes → `tests/Tests/Isolated/Common/`
2. Use appropriate namespace: `OpenEMR\Tests\Isolated\{Module}`
3. Extend `PHPUnit\Framework\TestCase`
4. Avoid using OpenEMR classes that require database connections
5. Use only pure PHP functions and Composer-loaded dependencies

### Example Test Structure
```
tests/Tests/Isolated/
├── Validators/
│   ├── AllergyIntoleranceValidatorTest.php
│   └── ConditionValidatorTest.php
├── Services/
│   └── SomeServiceTest.php
└── Common/
    └── UtilsTest.php
```

### Example Test
```php
<?php
namespace OpenEMR\Tests\Isolated\Validators;

use OpenEMR\Validators\SomeValidator;
use PHPUnit\Framework\TestCase;

class SomeValidatorTest extends TestCase
{
    public function testValidation(): void
    {
        $validator = new SomeValidatorStub();
        $result = $validator->validate($data, 'context');
        $this->assertTrue($result->isValid());
    }
}

// Stub class to override database methods
class SomeValidatorStub extends SomeValidator
{
    public static function validateId($field, $table, $lookupId, $isUuid = false)
    {
        return true; // Mock database validation
    }
}
```

## What's Loaded

The isolated bootstrap only loads:
- Composer autoloader (`vendor/autoload.php`)
- Minimal global variables required by some classes
- UTF-8 character encoding settings

## What's NOT Loaded

- Database connections
- OpenEMR session management
- Configuration from `sqlconf.php`
- Service containers and dependency injection
- Authentication systems
- Module system

## Benefits

- **Fast**: No database connection overhead
- **Reliable**: No external service dependencies
- **Portable**: Runs in any environment with PHP and Composer
- **Isolated**: Tests don't affect each other through shared database state

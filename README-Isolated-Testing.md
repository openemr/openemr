# Isolated Testing Suite

OpenEMR includes an isolated testing setup that allows you to run PHPUnit tests without database or service dependencies.

## Problem

The default OpenEMR test bootstrap loads `interface/globals.php`, which:
- Connects to the database through `library/sql.inc.php`
- Loads configuration from `sqlconf.php`
- Initializes various OpenEMR services and dependencies

This prevents running simple unit tests without a full OpenEMR environment.

## Solution

New code written to fully follow SOLID design principles, and in particular (D)ependency Inversion, can be tested in complete isolation using mocks and stubs.

The isolated test suite bypasses the main test suite's bootstrap process, and provides only the Composer autoloader.

Key files:
- `phpunit-isolated.xml` — PHPUnit configuration for isolated tests
- `tests/Tests/Isolated/` — Directory for dependency-free tests

## What's NOT Loaded

- Database connections
- OpenEMR session management
- Configuration from `sqlconf.php`
- Service containers and OEGlobalsBag
- Authentication systems
- Module system
- Global variables set by the bootstrap (`$GLOBALS`)

## Benefits

- **Fast**: No database connection overhead
- **Reliable**: No external service dependencies
- **Portable**: Runs in any environment with PHP and Composer
- **Isolated**: Tests don't affect each other through shared database state

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
5. Only use classes that don't require the OpenEMR bootstrap
6. Use dependency injection — classes under test should accept dependencies via
   constructor parameters so tests can provide mocks or stubs

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
namespace OpenEMR\Tests\Isolated\Services;

use OpenEMR\Services\SomeService;
use OpenEMR\Repositories\SomeRepositoryInterface;
use PHPUnit\Framework\TestCase;

class SomeServiceTest extends TestCase
{
    public function testProcessReturnsTransformedData(): void
    {
        // Create mock for the repository dependency
        $repository = $this->createMock(SomeRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findById')
            ->with(123)
            ->willReturn(['id' => 123, 'name' => 'Test']);

        // Inject mocked dependencies
        $service = new SomeService($repository);

        // Test the behavior
        $result = $service->process(123);

        $this->assertEquals('Test', $result->getName());
    }
}
```

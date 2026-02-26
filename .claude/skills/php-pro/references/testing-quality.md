# OpenEMR Testing & Quality

## Running Tests

All tests run inside the Docker dev-easy container unless marked "isolated."

```bash
# From docker/development-easy/ directory:

# Run all tests
docker compose exec openemr /root/devtools clean-sweep-tests

# Individual suites
docker compose exec openemr /root/devtools unit-test
docker compose exec openemr /root/devtools api-test
docker compose exec openemr /root/devtools e2e-test
docker compose exec openemr /root/devtools services-test

# View PHP error log
docker compose exec openemr /root/devtools php-log
```

### Isolated Tests (No Docker Required)

These run on the host without a database:

```bash
composer phpunit-isolated
```

### Twig Template Tests

```bash
# Regenerate fixture files after modifying Twig templates
composer update-twig-fixtures

# Fixtures live in:
# tests/Tests/Isolated/Common/Twig/fixtures/render/
```

## Writing PHPUnit Tests for a Custom Module

```php
<?php

/**
 * Tests for Safety Sentinel module integration
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Modules\SafetySentinel;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class SafetyControllerTest extends TestCase
{
    private SafetyController $controller;

    protected function setUp(): void
    {
        $this->controller = new SafetyController();
    }

    public function testCheckSafetyReturnsResultForValidPatient(): void
    {
        // Note: this test would need the FastAPI backend running
        // or mock the curl call
        $result = $this->controller->checkSafety('patient-003', 'metformin');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('severity', $result);
    }

    public function testCheckSafetyHandlesServiceUnavailable(): void
    {
        // Point to a non-existent service
        $GLOBALS['safety_sentinel_url'] = 'http://localhost:99999';

        $result = $this->controller->checkSafety('patient-001', 'aspirin');

        $this->assertArrayHasKey('error', $result);
    }
}
```

### Test File Location

For custom modules, tests can go in:
- `tests/Tests/` (OpenEMR's main test directory) — if contributing to the core test suite
- Inside your module directory — for module-specific tests during development

## Code Quality Checks

Run on the host (requires local PHP and Node):

```bash
# All PHP quality checks at once
composer code-quality

# Individual checks
composer phpstan            # Static analysis
composer phpcs              # Code style check
composer phpcbf             # Code style auto-fix
composer rector-check       # Code modernization (dry-run)

# JavaScript/CSS
npm run lint:js             # ESLint check
npm run lint:js-fix         # ESLint auto-fix
npm run stylelint           # CSS/SCSS lint
```

## phpcs (Code Style)

OpenEMR has its own phpcs ruleset. Run it before every commit:

```bash
composer phpcs

# Auto-fix what it can
composer phpcbf
```

Common issues it catches:
- Wrong indentation (must be 4 spaces)
- Missing/wrong file headers
- Line length violations
- Whitespace issues

## PHPStan (Static Analysis)

```bash
composer phpstan
```

OpenEMR runs PHPStan but not at the strictest level. Don't add `phpstan.neon` overrides that conflict with the project's configuration.

## Pre-Commit Hooks

OpenEMR provides pre-commit hooks via `.pre-commit-config.yaml`:

```bash
# Install pre-commit hooks
pip install pre-commit
pre-commit install
```

## Testing Strategy for Safety Sentinel Module

For the OpenEMR module specifically, test at these levels:

| Level | What to Test | How |
|-------|-------------|-----|
| Module loading | `openemr.bootstrap.php` doesn't error | Docker devtools |
| Event subscription | Menu items appear, JS injected | Manual or e2e test |
| API proxy | PHP controller calls FastAPI correctly | PHPUnit with mocked curl |
| ACL | Unauthorized users are blocked | PHPUnit |
| Safety agent (Python) | Drug interactions, allergy checks | Python eval runner (separate) |

The PHP module is thin glue — most testing effort should remain on the Python agent side using your existing eval framework.

## Commit Conventions

```
feat(module): add safety sentinel custom module bootstrap
fix(module): correct ACL check for prescription access
test(module): add integration test for safety check proxy
docs(module): add module installation instructions
```
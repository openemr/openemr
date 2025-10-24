# Coverage Collection for E2E and API Tests

This directory contains scripts for capturing code coverage during end-to-end (E2E) and API tests.

## How It Works

### 1. Prepend/Shutdown Bookends (`auto_prepend.php`)

A PHP auto-prepend script runs before every HTTP request during E2E and API tests:

- **Prepend**: Starts Xdebug coverage collection when `ENABLE_COVERAGE=true`
- **Shutdown**: Captures raw coverage data at request end and saves to:
  - `/tmp/openemr-coverage/e2e/coverage.e2e.*.raw.php` for E2E tests
  - `/tmp/openemr-coverage/api/coverage.api.*.raw.php` for API tests

Each file contains a raw Xdebug coverage array from a single HTTP request. The test type is automatically detected based on the request URI (`/apis/*` = API, otherwise = E2E).

### 2. Setup (`setup_e2e_bookends` in `ciLibrary.source`)

Configures PHP to auto-prepend the coverage script:
- Adds `auto_prepend_file` INI directive pointing to `ci/auto_prepend.php`
- Restarts web server to apply configuration
- Verifies prepend/shutdown handlers execute via marker files

### 3. Conversion (`convert-coverage`)

After tests complete, this generic CLI tool:
- Loads all raw Xdebug arrays from the specified input directory
- Merges them into a single `CodeCoverage` object
- Outputs `.cov` file (PHPUnit format) and Clover XML report

Examples:
```bash
./ci/convert-coverage /tmp/coverage-e2e-raw/e2e coverage/coverage.e2e.cov --clover=coverage.e2e.clover.xml
./ci/convert-coverage /tmp/coverage-api-raw/api coverage/coverage.api.cov --clover=coverage.api.clover.xml
```

### 4. Merging (`merge_coverage` in `ciLibrary.source`)

The existing `phpcov merge` command combines all `.cov` files (unit, api, e2e, etc.) into final coverage reports.

## Environment Variables

- `OPENEMR_ENABLE_CI_PHP=1` - Enables CI scripts (set in docker-compose)
- `ENABLE_COVERAGE=true` - Activates coverage collection

## Files

- `auto_prepend.php` - Auto-prepend script with coverage hooks (supports both E2E and API)
- `convert-coverage` - Generic Symfony Console CLI tool to merge raw coverage files
- `ciLibrary.source` - Contains `setup_e2e_bookends()` and `merge_coverage()`

## GitHub Actions Workflow

In `.github/workflows/test.yml`, the coverage process happens in these steps:

### For API Tests:
1. **Api testing** - Runs API tests via `build_test api`
2. **Copy API coverage files from container** - Extracts raw coverage files from `/tmp/openemr-coverage/api`
3. **Convert API coverage to .cov format** - Runs `./ci/convert-coverage` to merge raw files into `coverage/coverage.api.cov`
4. **Upload api test coverage to Codecov** - Uploads the clover XML report

### For E2E Tests:
1. **E2e setup** - Calls `setup_e2e_bookends()` to configure auto-prepend
2. **E2e testing** - Runs E2E tests via `build_test e2e`
3. **Copy E2E coverage files from container** - Extracts raw coverage files from `/tmp/openemr-coverage/e2e`
4. **Convert E2E coverage to .cov format** - Runs `./ci/convert-coverage` to merge raw files into `coverage/coverage.e2e.cov`
5. **Upload e2e test coverage to Codecov** - Uploads the clover XML report

### Final Step:
- **Combine coverage** - Calls `merge_coverage()` which runs `phpcov merge coverage/` to combine all `.cov` files into final reports

## Workflow Diagram

```mermaid
flowchart TD
    A[Test Starts - E2E or API] --> B[HTTP Request]
    B --> C[auto_prepend.php detects test type]
    C --> D{Request URI?}
    D -->|/apis/*| E[API Test - save to /tmp/openemr-coverage/api/]
    D -->|Other| F[E2E Test - save to /tmp/openemr-coverage/e2e/]
    E --> G[PHP executes application code]
    F --> G
    G --> H[Shutdown handler saves raw coverage]
    H --> I{More requests?}
    I -->|Yes| B
    I -->|No| J[Copy files from container to host]
    J --> K[convert-coverage merges raw files]
    K --> L[Output: coverage/coverage.TYPE.cov]
    L --> M[phpcov merge combines all .cov files]
    M --> N[Final: coverage.clover.xml + htmlcov/]
```

## Why This Approach?

- **Unified infrastructure**: Same prepend/convert scripts for E2E and API tests
- **Minimal overhead**: Prepend/shutdown only capture raw data
- **No test changes**: Works transparently with existing tests
- **Automatic detection**: Test type determined by request URI
- **Standard format**: Outputs PHPUnit-compatible `.cov` files
- **Merge-friendly**: Integrates with existing coverage workflow

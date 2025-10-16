# Edit Globals E2E Tests

This document describes the E2E tests for the `interface/super/edit_globals.php` page.

## Test File

- **File**: `tests/Tests/E2e/KkEditGlobalsTest.php`
- **XPath Constants**: `tests/Tests/E2e/Xpaths/XpathsConstantsEditGlobals.php`

## What These Tests Cover

### 1. Page Load Test (`testEditGlobalsPageLoads`)
- Verifies the globals configuration page loads correctly
- Checks that the form, save button, and search field are present
- **Dependencies**: Requires successful login (`testLoginAuthorized`)

### 2. Tab Navigation Test (`testTabNavigation`)
- Tests clicking through different configuration tabs (Locale, Appearance, Security, Connectors)
- Verifies each tab becomes active when clicked
- **Purpose**: Ensures the tab UI works correctly

### 3. Search Functionality Test (`testSearchFunctionality`)
- Tests the search feature for finding specific settings
- Verifies search highlights appear on matching settings
- Searches for "Language" and validates results
- **Purpose**: Ensures search functionality for easier setting location

### 4. Text Field Save Test (`testCanSaveTextGlobalSetting`)
- Tests saving a text-type global setting (`site_id`)
- Verifies the value is correctly saved to the database
- Restores the original value after test
- **Purpose**: Validates text input persistence

### 5. Checkbox Save Test (`testCanSaveCheckboxGlobalSetting`)
- Tests toggling and saving a checkbox setting (`disable_utf8_flag`)
- Verifies checkbox state is saved correctly
- Restores original value after test
- **Purpose**: Validates checkbox input persistence

### 6. Select Dropdown Save Test (`testCanSaveSelectGlobalSetting`)
- Tests changing and saving a dropdown selection (`language_default`)
- Verifies selected option is saved to database
- Restores original value after test
- **Purpose**: Validates select dropdown persistence

### 7. Multiple Settings Transaction Test (`testMultipleGlobalsCanBeSavedInOneTransaction`)
- Tests saving multiple settings at once (`site_id` and `phone_country_code`)
- Verifies all changes are committed in a single transaction
- Restores original values after test
- **Purpose**: Validates the transaction handling in lines 235-300 of edit_globals.php

## Running the Tests

### Option 1: Run All E2E Tests
```bash
vendor/bin/phpunit --testsuite e2e
```

### Option 2: Run Only Edit Globals Tests
```bash
vendor/bin/phpunit tests/Tests/E2e/KkEditGlobalsTest.php
```

### Option 3: Run Specific Test Method
```bash
vendor/bin/phpunit tests/Tests/E2e/KkEditGlobalsTest.php --filter testEditGlobalsPageLoads
```

### Option 4: Run with Selenium Grid (Recommended for CI)
```bash
# Start the selenium service
cd ci/apache_84_118  # or your preferred environment
docker-compose --profile selenium up -d

# Run tests inside the container
docker-compose exec openemr bash
SELENIUM_USE_GRID=true SELENIUM_HOST=selenium vendor/bin/phpunit tests/Tests/E2e/KkEditGlobalsTest.php
```

### Option 5: Run in GitHub Actions
The tests will automatically run when you push to GitHub if E2E tests are configured in your CI workflow.

## Environment Variables

These tests respect the following environment variables (configured in `BaseTrait.php`):

- `SELENIUM_USE_GRID`: Set to `"true"` to use Selenium Grid instead of local ChromeDriver
- `SELENIUM_HOST`: Hostname of Selenium Grid (default: `"selenium"`)
- `SELENIUM_BASE_URL`: Base URL of OpenEMR instance (default: `"http://openemr"`)
- `SELENIUM_FORCE_HEADLESS`: Set to `"true"` to force headless mode (disables VNC viewing)

## Test Data Cleanup

All tests follow these principles:
1. **Read original values** before making changes
2. **Make test changes** and verify they work
3. **Restore original values** after test completes
4. **Always quit the browser client** in finally/catch blocks

This ensures tests don't leave the database in a modified state.

## Debugging Tests

### View Test Execution in Browser
When using Selenium Grid without headless mode, you can connect to the VNC server to watch tests execute:

```bash
# VNC is available on port 5900 (if exposed in docker-compose)
# Use a VNC client to connect to localhost:5900
# Password is typically "secret"
```

### Enable Verbose Output
```bash
vendor/bin/phpunit tests/Tests/E2e/KkEditGlobalsTest.php -v
```

### Check Test Logs
If tests fail, check:
1. Browser console errors (visible in VNC viewer)
2. PHPUnit error output
3. OpenEMR logs in `sites/default/documents/logs_and_misc/`

## What Database Interactions Are Tested

These tests validate the critical database operations in `edit_globals.php`:

1. **Reading globals** (line 466-471): Tests verify values are read correctly
2. **Saving single globals** (lines 288-289): Tests verify INSERT/DELETE for single values
3. **Transaction handling** (lines 235-236, 299-300): Tests verify multiple changes commit atomically
4. **Different field types**:
   - Text fields (line 256)
   - Checkboxes (line 527)
   - Select dropdowns (line 502)
   - Multi-value fields would need additional tests

## Files Modified by Tests

These tests temporarily modify values in the `globals` table but always restore them:

- `site_id`
- `phone_country_code`
- `disable_utf8_flag`
- `language_default`

## Next Steps for Refactoring

Once these tests pass consistently:

1. You can safely refactor the database interaction code in `edit_globals.php`
2. Extract database operations into service classes (e.g., `GlobalsManager`, `GlobalsRepository`)
3. Re-run these tests after each refactoring step
4. If tests still pass, your refactoring preserved functionality
5. Add more tests for edge cases (encryption, multi-value fields, side effects)

## Continuous Integration

To add these tests to your GitHub Actions workflow, ensure your workflow file includes:

```yaml
- name: Run E2E Tests
  run: |
    docker-compose --profile selenium up -d
    docker-compose exec -T openemr bash -c "SELENIUM_USE_GRID=true vendor/bin/phpunit --testsuite e2e"
```

## Known Limitations

1. Tests don't currently cover encrypted field types (`encrypted`, `encrypted_hash`)
2. Tests don't cover multi-select fields (`m_` prefix fields)
3. Tests don't verify side effects like:
   - Background service updates (`checkBackgroundServices()`)
   - CouchDB initialization (`checkCreateCDB()`)
   - Audit logging (lines 307-316)

These could be added as additional test methods if needed.

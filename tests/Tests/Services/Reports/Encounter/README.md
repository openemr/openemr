# Encounter Report Tests

This directory contains comprehensive test coverage for the Encounter Report classes in OpenEMR.

## Test Files

### 1. EncounterReportFormatterTest.php
**Type:** Unit Test (Isolated)  
**Coverage:** `OpenEMR\Reports\Encounter\EncounterReportFormatter`

Tests the formatting and transformation of encounter report data:
- `formatEncounters()` - Array transformation
- `formatEncounterRow()` - Individual row formatting
- `formatSummary()` - Summary data aggregation

**Tests:** 8 tests covering:
- Array transformations
- Empty data handling
- Date formatting
- Optional field handling
- Summary calculations

### 2. EncounterReportFormHandlerTest.php
**Type:** Unit Test (Isolated)  
**Coverage:** `OpenEMR\Reports\Encounter\EncounterReportFormHandler`

Tests form data processing and validation:
- `processForm()` - Form data normalization
- Date validation with multiple formats
- Input sanitization
- Filter validation

**Tests:** 12 tests covering:
- Valid input processing
- Optional field handling
- Date format validation (YYYY-MM-DD, MM/DD/YYYY, DD/MM/YYYY)
- Invalid data filtering
- SQL injection prevention
- Numeric validation for IDs
- Default behaviors

### 3. EncounterReportDataTest.php
**Type:** Integration Test  
**Coverage:** `OpenEMR\Reports\Encounter\EncounterReportData`

Tests database queries and data retrieval:
- `getEncounters()` - Main encounter query with filters
- `getEncounterCount()` - Count encounters
- `getEncounterSummary()` - Provider summaries
- `formatDate()` - Date formatting utility

**Tests:** 21 tests covering:
- Data structure validation
- Date range filtering
- Facility and provider filtering
- Signed-only encounter filtering
- Combined filter scenarios
- Invalid input handling
- Count operations
- Summary aggregations
- Date formatting

## Running the Tests

### Unit Tests (No Database Required)
```bash
# Run all isolated unit tests
vendor/bin/phpunit --configuration phpunit-isolated.xml --testsuite reports-unit --colors=always

# Run specific test file
vendor/bin/phpunit --configuration phpunit-isolated.xml tests/Tests/Services/Reports/Encounter/EncounterReportFormatterTest.php
```

### Integration Tests (Requires Database)
```bash
# Run all integration tests (requires database connection)
vendor/bin/phpunit tests/Tests/Services/Reports/Encounter/EncounterReportDataTest.php

# Run only integration tests in the integration group
vendor/bin/phpunit --group integration
```

### All Tests Together
```bash
# First run isolated tests, then integration tests
vendor/bin/phpunit --configuration phpunit-isolated.xml --testsuite reports-unit && \
vendor/bin/phpunit tests/Tests/Services/Reports/Encounter/EncounterReportDataTest.php
```

## Test Statistics

- **Total Tests:** 41
- **Unit Tests (Isolated):** 20
- **Integration Tests:** 21
- **Total Assertions:** 116+ (in unit tests alone)

## Configuration

### phpunit-isolated.xml
The isolated test configuration has been updated to include the Encounter Report unit tests in the `reports-unit` test suite.

### Database Requirements
Integration tests require:
- Running MySQL/MariaDB instance
- OpenEMR database initialized
- Proper site configuration (default site: 'default')
- Test data in the database (uses existing data)

## Code Coverage

All tests use PHP 8 attributes:
- `#[Test]` - Mark test methods
- `#[CoversClass]` - Specify covered class
- `#[Group('integration')]` - Group integration tests

## Standards Compliance

Tests follow OpenEMR coding standards:
- PSR-12 formatting
- PHP 8+ features (attributes, typed properties)
- Deidentified test data (no PHI)
- Proper namespacing: `OpenEMR\Tests\Services\Reports\Encounter`

## Future Enhancements

Optional improvements that could be added:
1. **Fixture Manager**: Create reusable fixtures for seeding test data
2. **Transaction Wrappers**: Implement rollback after each test
3. **Data Providers**: Use PHPUnit data providers for parameterized tests
4. **Mock Database**: Mock database responses for faster unit tests
5. **Coverage Reports**: Generate coverage reports with `--coverage-html`

## Notes

- Unit tests use the `tests/bootstrap-isolated.php` which doesn't require database
- Integration tests use `tests/bootstrap.php` which loads full OpenEMR environment
- All tests respect OpenEMR's no-PHI policy - only synthetic data is used
- Tests are independent and can run in any order

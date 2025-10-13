# OpenEMR Portal Messaging Frontend Tests

This test suite provides comprehensive frontend testing for the OpenEMR Portal Messaging functionality. The tests are specifically designed to detect issues that might arise from dependency updates in the Node.js ecosystem.

## Overview

The portal messaging system consists of two main components:

1. **Messages System** (`portal/messaging/messages.php`) - AngularJS-based secure messaging interface
2. **Secure Chat** (`portal/messaging/secure_chat.php`) - Real-time chat functionality with CKEditor

## Test Structure

```
tests/frontend/
├── portal-messaging.test.js           # Core messaging functionality tests
├── portal-messaging-integration.test.js # DOM and integration tests
├── portal-messaging-dependencies.test.js # Dependency-specific compatibility tests
├── setup.js                          # Test environment configuration
└── __mocks__/                        # Mock implementations
    ├── jquery.js                     # jQuery mock
    ├── angular.js                    # AngularJS mock
    └── bootstrap.js                  # Bootstrap mock
```

## Dependencies Tested

### Core Frontend Libraries
- **AngularJS 1.8.3** - Main application framework for messaging interface
- **jQuery 3.7.1** - DOM manipulation and AJAX requests
- **Bootstrap 4.6.2** - UI components (modals, dropdowns, responsive layout)

### Rich Text Editors
- **Summernote 0.9.1** - WYSIWYG editor for message composition
- **CKEditor 5.x** - Advanced editor for secure chat

### Utilities and Plugins
- **DOMPurify 3.2.6** - HTML sanitization for security
- **Moment.js 2.30.1** - Date formatting and manipulation
- **Chart.js 4.5.0** - Data visualization (if used in messaging analytics)
- **Validate.js 0.13.1** - Form validation

## Running Tests

### Basic Test Commands

```bash
# Run all frontend tests
npm run test:frontend

# Run tests in watch mode (for development)
npm run test:watch

# Run tests with coverage report
npm run test:coverage

# Run tests for CI/CD (non-interactive)
npm run test:ci

# Check for dependency vulnerabilities and outdated packages
npm run test:dependency-check
```

### Individual Test Files

```bash
# Run specific test files
npx jest tests/frontend/portal-messaging.test.js
npx jest tests/frontend/portal-messaging-integration.test.js
npx jest tests/frontend/portal-messaging-dependencies.test.js
```

## Test Categories

### 1. Core Functionality Tests (`portal-messaging.test.js`)

These tests verify the basic messaging functionality:

- **Angular Module Initialization** - Ensures AngularJS modules load correctly
- **Message Operations** - Create, read, update, delete operations
- **Pagination and Search** - Message list handling
- **Form Validation** - Input validation and error handling
- **AJAX Communication** - HTTP requests to backend endpoints
- **Security Features** - CSRF token handling and input sanitization

### 2. Integration Tests (`portal-messaging-integration.test.js`)

These tests verify component integration:

- **DOM Structure** - HTML element presence and structure
- **CSS Framework Integration** - Bootstrap classes and responsive design
- **Event Handling** - User interaction events
- **Form Submission** - Complete form workflows
- **Modal Interactions** - Bootstrap modal functionality
- **Accessibility** - ARIA attributes and semantic HTML

### 3. Dependency Compatibility Tests (`portal-messaging-dependencies.test.js`)

These tests focus on library compatibility:

- **Version Compatibility** - Ensures libraries work with specified versions
- **API Changes** - Detects breaking changes in library APIs
- **Cross-library Integration** - Tests interaction between different libraries
- **Performance** - Memory usage and cleanup
- **Error Handling** - Graceful degradation when features are unavailable

## Key Test Scenarios

### Dependency Update Detection

The tests are designed to catch common issues when dependencies are updated:

1. **Breaking API Changes**
   ```javascript
   // Tests verify that jQuery methods still work as expected
   expect(mockjQuery('#element').modal('show')).not.toThrow();
   ```

2. **Event System Changes**
   ```javascript
   // Tests ensure event handlers continue to work
   element.on('hidden.bs.modal', function() { /* handler */ });
   ```

3. **CSS Class Changes**
   ```javascript
   // Tests verify Bootstrap classes are still applied correctly
   expect(element.classList.contains('table-responsive')).toBe(true);
   ```

4. **Angular Compatibility**
   ```javascript
   // Tests ensure AngularJS dependency injection still works
   app.controller('TestCtrl', ['$scope', '$http', controllerFunction]);
   ```

### Security Testing

- **XSS Prevention** - Tests DOMPurify sanitization
- **CSRF Protection** - Verifies token handling
- **Input Validation** - Tests form input sanitization

### Performance Testing

- **Large Dataset Handling** - Tests with 1000+ messages
- **Memory Management** - Event listener cleanup
- **Pagination Efficiency** - Large list rendering

## Configuration

### Jest Configuration (`jest.config.frontend.js`)

The test configuration includes:

- **Test Environment**: jsdom for browser simulation
- **Setup Files**: Automatic mock loading
- **Coverage Thresholds**: 50% minimum coverage
- **Module Mapping**: Automatic library mocking

### Mock System

The mock system provides realistic implementations of:

- **jQuery**: Full jQuery API with chaining support
- **AngularJS**: Module system, dependency injection, and utilities
- **Bootstrap**: Component lifecycle and event system
- **Browser APIs**: Audio, Notification, localStorage, etc.

## Continuous Integration

### GitHub Actions / CI/CD Integration

```yaml
# Example CI configuration
- name: Run Frontend Tests
  run: npm run test:ci

- name: Check Dependencies
  run: npm run test:dependency-check

- name: Upload Coverage
  uses: codecov/codecov-action@v3
  with:
    file: ./coverage/frontend/lcov.info
```

### Test Reports

Tests generate multiple report formats:

- **Console Output** - Real-time test results
- **HTML Coverage** - Visual coverage reports
- **JUnit XML** - CI/CD integration
- **LCOV** - Coverage tracking tools

## Troubleshooting

### Common Issues

1. **JSDOM Environment Issues**
   ```bash
   # Ensure jsdom is properly installed
   npm install --save-dev jsdom jsdom-global
   ```

2. **Mock Import Errors**
   ```bash
   # Verify mock files are in the correct location
   ls tests/frontend/__mocks__/
   ```

3. **Dependency Version Conflicts**
   ```bash
   # Check for version mismatches
   npm ls --depth=0
   ```

### Debug Mode

Run tests with additional debugging:

```bash
# Enable verbose output
npx jest --verbose --config jest.config.frontend.js

# Run specific test with debugging
npx jest --testNamePattern="should handle modal interactions" --verbose
```

## Contributing

When adding new tests:

1. **Follow Naming Conventions** - Use descriptive test names
2. **Mock External Dependencies** - Don't rely on actual library implementations
3. **Test Edge Cases** - Include error conditions and boundary cases
4. **Update Documentation** - Add new test categories to this README

### Test Template

```javascript
describe('New Feature Tests', () => {
    beforeEach(() => {
        // Setup mocks and environment
    });

    test('should handle expected behavior', () => {
        // Test implementation
        expect(result).toBe(expected);
    });

    test('should handle error conditions', () => {
        // Error case testing
        expect(() => errorFunction()).toThrow();
    });
});
```

## Maintenance

### Regular Tasks

1. **Update Dependency Versions** - Keep mocks in sync with actual library versions
2. **Review Coverage Reports** - Ensure adequate test coverage
3. **Monitor CI/CD Results** - Address any failing tests promptly
4. **Security Audits** - Regular dependency vulnerability checks

### Version Updates

When updating dependencies:

1. Update the version numbers in mocks to match
2. Run the full test suite to identify breaking changes
3. Update test expectations if API changes are detected
4. Add new tests for any new functionality

This test suite ensures that the OpenEMR Portal Messaging system remains stable and functional as dependencies evolve, providing early warning of potential issues before they affect users.
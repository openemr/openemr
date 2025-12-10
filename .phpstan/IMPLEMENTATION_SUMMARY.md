# Implementation Summary: PHPStan Rule for curl_* Functions

## Overview

This implementation creates a custom PHPStan rule that prevents introduction of raw `curl_*` functions in the OpenEMR codebase, supporting the migration to GuzzleHttp client.

## What Was Implemented

### 1. Custom PHPStan Rule
**File**: `.phpstan/ForbiddenCurlFunctionsRule.php`

A custom PHPStan rule that:
- Detects all `curl_*` function calls using pattern matching (`/^curl_/i`)
- Provides helpful error messages directing developers to use GuzzleHttp
- Includes actionable tips in the error output
- Uses PHPStan's `Rule` interface for seamless integration

### 2. Rule Registration
**File**: `.phpstan/extension.neon`

The rule is registered in the PHPStan extension configuration:
```yaml
- class: OpenEMR\PHPStan\Rules\ForbiddenCurlFunctionsRule
  tags:
  - phpstan.rules.rule
```

This ensures it runs automatically during PHPStan analysis.

### 3. Documentation
**File**: `.phpstan/README.md`

Updated the main README to document:
- Purpose and rationale of the rule
- What function calls it catches
- Before/after code examples
- Migration recommendations

### 4. Test File
**File**: `.phpstan/test-curl-forbidden.php`

A test file containing intentional curl_* function calls to validate the rule works correctly. This file should always fail PHPStan analysis with specific error messages.

### 5. Test Instructions
**File**: `.phpstan/TEST-INSTRUCTIONS.md`

Detailed instructions for:
- How to run PHPStan on the test file
- Expected output with exact error messages
- CI/CD integration information

### 6. Migration Guide
**File**: `.phpstan/MIGRATION_GUIDE_CURL.md`

A comprehensive guide showing:
- Why migrate from curl to GuzzleHttp
- Common migration patterns with side-by-side examples
- Error handling best practices
- Timeout and SSL/TLS configuration
- File upload examples
- Using OpenEMR's HttpClient wrapper

## How It Works

1. **Developer creates a PR** with code changes
2. **GitHub Actions runs PHPStan** automatically via `.github/workflows/phpstan.yml`
3. **PHPStan analyzes the code** including all custom rules
4. **ForbiddenCurlFunctionsRule checks** for any `curl_*` function calls
5. **If found, PHPStan fails** with error message:
   ```
   Raw curl_* function curl_init() is forbidden. Use GuzzleHttp\Client or 
   OpenEMR\Common\Http\HttpClient instead.
   💡 Migrate to GuzzleHttp for better testability and PSR-7 compliance
   ```
6. **Developer sees the error** in PR checks and must update code to use GuzzleHttp

## Testing the Rule

### Manual Testing
```bash
# Install dependencies
composer install

# Run PHPStan on test file (should fail with 8 errors)
vendor/bin/phpstan analyze .phpstan/test-curl-forbidden.php --level=1

# Run PHPStan on entire codebase
vendor/bin/phpstan --memory-limit=8G analyze
```

### Expected Behavior
- The test file should generate exactly 8 errors (one for each curl_* function call)
- Each error should identify the specific curl_* function and suggest using GuzzleHttp
- The rule should not flag non-curl functions

### CI Integration
The rule is automatically run by GitHub Actions on every PR via the phpstan workflow.

## Benefits

### For the Project
1. **Prevents regression**: No new curl code can be introduced
2. **Enforces consistency**: All HTTP requests use the same client
3. **Improves quality**: GuzzleHttp provides better error handling and testing
4. **Supports migration**: Clear path from legacy curl to modern HTTP client

### For Developers
1. **Immediate feedback**: Errors shown during PR review
2. **Clear guidance**: Error messages explain what to do
3. **Documentation**: Comprehensive migration guide with examples
4. **Testability**: GuzzleHttp can be mocked in unit tests

## Future Enhancements

Potential improvements to consider:
1. **Baseline for existing code**: Create a baseline file excluding existing curl usage
2. **Auto-fix suggestions**: Integrate with tools that can suggest automatic fixes
3. **Custom exceptions**: Allow specific files to be excluded if absolutely necessary
4. **Migration tracking**: Count remaining curl usages and track migration progress

## Related Files

- Custom Rule: `.phpstan/ForbiddenCurlFunctionsRule.php`
- Rule Config: `.phpstan/extension.neon`
- Documentation: `.phpstan/README.md`
- Migration Guide: `.phpstan/MIGRATION_GUIDE_CURL.md`
- Test File: `.phpstan/test-curl-forbidden.php`
- Test Docs: `.phpstan/TEST-INSTRUCTIONS.md`
- CI Workflow: `.github/workflows/phpstan.yml`

## Similar Rules

OpenEMR has several similar custom PHPStan rules:
- `ForbiddenFunctionsRule`: Prevents legacy SQL functions
- `ForbiddenClassesRule`: Prevents laminas-db usage
- `ForbiddenGlobalsAccessRule`: Prevents direct $GLOBALS access
- `NoCoversAnnotationRule`: Prevents @covers annotations

All follow the same pattern and work together to enforce modern coding practices.

## Support

For questions or issues:
1. Check the migration guide: `.phpstan/MIGRATION_GUIDE_CURL.md`
2. Review existing GuzzleHttp usage in `src/Common/Http/`
3. Ask in OpenEMR development chat
4. Consult [Guzzle documentation](https://docs.guzzlephp.org/)

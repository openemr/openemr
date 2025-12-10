# Test PHPStan Rule for curl_* Functions

This directory contains a test file to validate that the ForbiddenCurlFunctionsRule is working correctly.

## Running the Test

Once composer dependencies are installed, you can run PHPStan on the test file:

```bash
# From the repository root
vendor/bin/phpstan analyze .phpstan/test-curl-forbidden.php --level=1 --error-format=table
```

## Expected Output

The test should produce errors similar to:

```
------ -----------------------------------------------------------------------------------------
Line   .phpstan/test-curl-forbidden.php
------ -----------------------------------------------------------------------------------------
22     Raw curl_* function curl_init() is forbidden. Use GuzzleHttp\Client or 
       OpenEMR\Common\Http\oeHttp instead.
       💡 Migrate to GuzzleHttp for better testability and PSR-7 compliance
23     Raw curl_* function curl_setopt() is forbidden. Use GuzzleHttp\Client or 
       OpenEMR\Common\Http\oeHttp instead.
       💡 Migrate to GuzzleHttp for better testability and PSR-7 compliance
24     Raw curl_* function curl_exec() is forbidden. Use GuzzleHttp\Client or 
       OpenEMR\Common\Http\oeHttp instead.
       💡 Migrate to GuzzleHttp for better testability and PSR-7 compliance
25     Raw curl_* function curl_getinfo() is forbidden. Use GuzzleHttp\Client or 
       OpenEMR\Common\Http\oeHttp instead.
       💡 Migrate to GuzzleHttp for better testability and PSR-7 compliance
26     Raw curl_* function curl_errno() is forbidden. Use GuzzleHttp\Client or 
       OpenEMR\Common\Http\oeHttp instead.
       💡 Migrate to GuzzleHttp for better testability and PSR-7 compliance
27     Raw curl_* function curl_error() is forbidden. Use GuzzleHttp\Client or 
       OpenEMR\Common\Http\oeHttp instead.
       💡 Migrate to GuzzleHttp for better testability and PSR-7 compliance
28     Raw curl_* function curl_close() is forbidden. Use GuzzleHttp\Client or 
       OpenEMR\Common\Http\oeHttp instead.
       💡 Migrate to GuzzleHttp for better testability and PSR-7 compliance
31     Raw curl_* function curl_multi_init() is forbidden. Use GuzzleHttp\Client or 
       OpenEMR\Common\Http\oeHttp instead.
       💡 Migrate to GuzzleHttp for better testability and PSR-7 compliance
------ -----------------------------------------------------------------------------------------
```

## Continuous Integration

The PHPStan workflow in `.github/workflows/phpstan.yml` will automatically run this rule on all PRs and flag any new code that attempts to use raw curl_* functions.

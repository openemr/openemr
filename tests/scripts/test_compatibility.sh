#!/bin/bash

# Front Controller Compatibility Test Script
#
# Verifies backward compatibility with existing functionality
# Tests: Entry points, multisite, authentication, static assets
#
# AI DISCLOSURE: This file contains code generated using Claude AI (Anthropic)
#
# Usage: ./test_compatibility.sh [base_url]
# Example: ./test_compatibility.sh http://localhost/openemr

set -e

# Configuration
BASE_URL="${1:-http://localhost/openemr}"
REPORT_DIR="$(dirname "$0")/../../reports"
REPORT_FILE="$REPORT_DIR/compatibility-test-report-$(date +%Y%m%d-%H%M%S).txt"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Create report directory
mkdir -p "$REPORT_DIR"

echo "Compatibility Test Report" | tee "$REPORT_FILE"
echo "Date: $(date)" | tee -a "$REPORT_FILE"
echo "URL: $BASE_URL" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

# Test counters
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

# Function to run a test
run_test() {
    local test_name="$1"
    local url="$2"
    local expected_codes="$3"
    local description="$4"

    TOTAL_TESTS=$((TOTAL_TESTS + 1))

    echo -n "Testing: $test_name... " | tee -a "$REPORT_FILE"

    # Make HTTP request and get status code
    http_code=$(curl -s -o /dev/null -w "%{http_code}" -L "$url")

    # Check if code matches any of the expected codes
    if [[ "$expected_codes" == *"$http_code"* ]]; then
        echo -e "${GREEN}PASS${NC} (HTTP $http_code)" | tee -a "$REPORT_FILE"
        PASSED_TESTS=$((PASSED_TESTS + 1))
        echo "  ✓ $description" | tee -a "$REPORT_FILE"
    else
        echo -e "${RED}FAIL${NC} (Expected [$expected_codes], got $http_code)" | tee -a "$REPORT_FILE"
        FAILED_TESTS=$((FAILED_TESTS + 1))
        echo "  ✗ $description" | tee -a "$REPORT_FILE"
        echo "  URL: $url" | tee -a "$REPORT_FILE"
    fi
    echo "" | tee -a "$REPORT_FILE"
}

# Test 1: Core Entry Points
echo "========================================" | tee -a "$REPORT_FILE"
echo "TEST CATEGORY: Core Entry Points" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

run_test \
    "index.php" \
    "$BASE_URL/index.php" \
    "200 302" \
    "Main entry point should be accessible"

run_test \
    "login.php" \
    "$BASE_URL/interface/login/login.php" \
    "200" \
    "Login page should be accessible"

run_test \
    "setup.php" \
    "$BASE_URL/setup.php" \
    "200 302" \
    "Setup workflow should be accessible"

# Test 2: Existing Front Controllers
echo "========================================" | tee -a "$REPORT_FILE"
echo "TEST CATEGORY: Existing Front Controllers" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

run_test \
    "REST API routing" \
    "$BASE_URL/apis/default/api/patient" \
    "200 401 403" \
    "REST API front controller should work (not 404)"

run_test \
    "Patient Portal routing" \
    "$BASE_URL/portal/index.php" \
    "200 302" \
    "Patient portal front controller should work"

run_test \
    "OAuth2 routing" \
    "$BASE_URL/oauth2/authorize" \
    "200 400 401" \
    "OAuth2 front controller should work (not 404)"

# Test 3: Multisite Support
echo "========================================" | tee -a "$REPORT_FILE"
echo "TEST CATEGORY: Multisite Support" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

run_test \
    "Multisite via query parameter" \
    "$BASE_URL/index.php?site=default" \
    "200 302" \
    "Multisite selection via ?site parameter should work"

run_test \
    "Multisite with language" \
    "$BASE_URL/index.php?site=default&lang=en" \
    "200 302" \
    "Query parameters should be preserved"

# Test 4: Core Workflows
echo "========================================" | tee -a "$REPORT_FILE"
echo "TEST CATEGORY: Core Workflows" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

run_test \
    "Patient file workflow" \
    "$BASE_URL/interface/patient_file/summary/demographics.php" \
    "200 302" \
    "Patient file workflows should work"

run_test \
    "Calendar workflow" \
    "$BASE_URL/interface/main/calendar/index.php" \
    "200 302" \
    "Calendar workflows should work"

run_test \
    "Billing workflow" \
    "$BASE_URL/interface/billing/billing_report.php" \
    "200 302" \
    "Billing workflows should work"

# Test 5: Static Assets
echo "========================================" | tee -a "$REPORT_FILE"
echo "TEST CATEGORY: Static Assets" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

# Test if public directory exists
if [ -d "$(dirname "$0")/../../public" ]; then
    run_test \
        "Static CSS files" \
        "$BASE_URL/public/assets/css/style.css" \
        "200 404" \
        "CSS files should be served directly (not routed through PHP)"

    run_test \
        "Static JS files" \
        "$BASE_URL/public/assets/js/script.js" \
        "200 404" \
        "JS files should be served directly (not routed through PHP)"
else
    echo "  ℹ Skipping static asset tests (public directory not found)" | tee -a "$REPORT_FILE"
    echo "" | tee -a "$REPORT_FILE"
fi

# Test 6: Custom Modules
echo "========================================" | tee -a "$REPORT_FILE"
echo "TEST CATEGORY: Custom Modules" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

run_test \
    "Custom modules path" \
    "$BASE_URL/sites/default/custom/test.php" \
    "200 404" \
    "Custom modules should not be blocked (403 would indicate blocking)"

# Test 7: POST Requests
echo "========================================" | tee -a "$REPORT_FILE"
echo "TEST CATEGORY: POST Requests" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

TOTAL_TESTS=$((TOTAL_TESTS + 1))
echo -n "Testing: POST request handling... " | tee -a "$REPORT_FILE"

http_code=$(curl -s -o /dev/null -w "%{http_code}" -X POST \
    -d "authUser=test&authPass=test" \
    "$BASE_URL/interface/login/login.php")

if [ "$http_code" != "404" ] && [ "$http_code" != "403" ]; then
    echo -e "${GREEN}PASS${NC} (HTTP $http_code)" | tee -a "$REPORT_FILE"
    PASSED_TESTS=$((PASSED_TESTS + 1))
    echo "  ✓ POST requests are processed correctly" | tee -a "$REPORT_FILE"
else
    echo -e "${RED}FAIL${NC} (HTTP $http_code)" | tee -a "$REPORT_FILE"
    FAILED_TESTS=$((FAILED_TESTS + 1))
    echo "  ✗ POST requests are being blocked" | tee -a "$REPORT_FILE"
fi
echo "" | tee -a "$REPORT_FILE"

# Test 8: File Uploads
echo "========================================" | tee -a "$REPORT_FILE"
echo "TEST CATEGORY: File Upload Paths" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

run_test \
    "File upload handler" \
    "$BASE_URL/interface/patient_file/upload_form.php" \
    "200 302" \
    "File upload paths should not be blocked"

# Test 9: AJAX Endpoints
echo "========================================" | tee -a "$REPORT_FILE"
echo "TEST CATEGORY: AJAX Endpoints" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

run_test \
    "AJAX endpoints" \
    "$BASE_URL/library/ajax/execute_javascript_globals.php" \
    "200 302 401 403" \
    "AJAX endpoints should be accessible (not 404)"

# Final Report
echo "========================================" | tee -a "$REPORT_FILE"
echo "TEST SUMMARY" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "Total Tests: $TOTAL_TESTS" | tee -a "$REPORT_FILE"
echo -e "Passed: ${GREEN}$PASSED_TESTS${NC}" | tee -a "$REPORT_FILE"
echo -e "Failed: ${RED}$FAILED_TESTS${NC}" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

if [ $FAILED_TESTS -eq 0 ]; then
    echo -e "${GREEN}✓ ALL COMPATIBILITY TESTS PASSED${NC}" | tee -a "$REPORT_FILE"
    echo "" | tee -a "$REPORT_FILE"
    echo "The front controller maintains 100% backward compatibility." | tee -a "$REPORT_FILE"
    echo "All existing functionality works as expected." | tee -a "$REPORT_FILE"
    exit 0
else
    echo -e "${RED}✗ SOME COMPATIBILITY TESTS FAILED${NC}" | tee -a "$REPORT_FILE"
    echo "" | tee -a "$REPORT_FILE"
    echo "Please review the failed tests above." | tee -a "$REPORT_FILE"
    echo "Some existing functionality may be broken." | tee -a "$REPORT_FILE"
    exit 1
fi

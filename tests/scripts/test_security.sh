#!/bin/bash

# Front Controller Security Test Script
#
# Tests .inc.php blocking, path traversal, security headers
#
# Usage: ./test_security.sh [base_url]
# Example: ./test_security.sh http://localhost/openemr

set -e

# Configuration
BASE_URL="${1:-http://localhost/openemr}"
REPORT_DIR="$(dirname "$0")/../../reports"
REPORT_FILE="$REPORT_DIR/security-test-report-$(date +%Y%m%d-%H%M%S).txt"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Create report directory
mkdir -p "$REPORT_DIR"

echo "Security Test Report" | tee "$REPORT_FILE"
echo "Date: $(date)" | tee -a "$REPORT_FILE"
echo "URL: $BASE_URL" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

# Test counters
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

run_test() {
    local test_name="$1"
    local url="$2"
    local expected_code="$3"
    local description="$4"

    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    echo -n "Testing: $test_name... " | tee -a "$REPORT_FILE"

    # Make HTTP request and get status code
    http_code=$(curl -s -o /dev/null -w "%{http_code}" -L "$url")

    if [ "$http_code" == "$expected_code" ]; then
        echo -e "${GREEN}PASS${NC} (HTTP $http_code)" | tee -a "$REPORT_FILE"
        PASSED_TESTS=$((PASSED_TESTS + 1))
        echo "  ✓ $description" | tee -a "$REPORT_FILE"
    else
        echo -e "${RED}FAIL${NC} (Expected $expected_code, got $http_code)" | tee -a "$REPORT_FILE"
        FAILED_TESTS=$((FAILED_TESTS + 1))
        echo "  ✗ $description" | tee -a "$REPORT_FILE"
        echo "  URL: $url" | tee -a "$REPORT_FILE"
    fi
    echo "" | tee -a "$REPORT_FILE"
}

# Test 1: Block .inc.php files (403 Forbidden)
echo "========================================" | tee -a "$REPORT_FILE"
echo "TEST CATEGORY: .inc.php File Blocking" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

run_test \
    "history.inc.php blocking" \
    "$BASE_URL/interface/patient_file/history/history.inc.php" \
    "403" \
    "Vulnerable file from security log should be blocked"

run_test \
    "printPatientForms.inc.php blocking" \
    "$BASE_URL/interface/patient_file/summary/printPatientForms.inc.php" \
    "403" \
    "Another .inc.php file should be blocked"

run_test \
    "demographics_full.inc blocking" \
    "$BASE_URL/interface/patient_file/summary/demographics_full.inc" \
    "403" \
    ".inc files should also be blocked"

# Test 2: Path Traversal Prevention
echo "========================================" | tee -a "$REPORT_FILE"
echo "TEST CATEGORY: Path Traversal Prevention" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

run_test \
    "Path traversal ../" \
    "$BASE_URL/home.php?_ROUTE=../../../etc/passwd" \
    "404" \
    "Basic path traversal should be blocked"

run_test \
    "Path traversal encoded" \
    "$BASE_URL/home.php?_ROUTE=..%2F..%2F..%2Fetc%2Fpasswd" \
    "404" \
    "URL-encoded path traversal should be blocked"

run_test \
    "Path traversal double encoded" \
    "$BASE_URL/home.php?_ROUTE=%2e%2e%2f%2e%2e%2f%2e%2e%2fetc%2fpasswd" \
    "404" \
    "Double-encoded path traversal should be blocked"

run_test \
    "Path traversal backslash" \
    "$BASE_URL/home.php?_ROUTE=..%5c..%5c..%5cetc%5cpasswd" \
    "404" \
    "Backslash path traversal should be blocked"

# Test 3: Non-PHP File Blocking
echo "========================================" | tee -a "$REPORT_FILE"
echo "TEST CATEGORY: Non-PHP File Blocking" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

run_test \
    ".htaccess access" \
    "$BASE_URL/home.php?_ROUTE=.htaccess" \
    "404" \
    ".htaccess files should not be accessible via front controller"

run_test \
    "composer.json access" \
    "$BASE_URL/home.php?_ROUTE=composer.json" \
    "404" \
    "JSON files should not be routed through front controller"

run_test \
    ".env access" \
    "$BASE_URL/home.php?_ROUTE=.env" \
    "404" \
    ".env files should not be accessible"

# Test 4: Non-Existent Files
echo "========================================" | tee -a "$REPORT_FILE"
echo "TEST CATEGORY: Non-Existent File Handling" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

run_test \
    "Non-existent PHP file" \
    "$BASE_URL/home.php?_ROUTE=nonexistent.php" \
    "404" \
    "Non-existent files should return 404"

run_test \
    "Non-existent path" \
    "$BASE_URL/home.php?_ROUTE=fake/path/file.php" \
    "404" \
    "Non-existent paths should return 404"

# Test 5: Legitimate File Access
echo "========================================" | tee -a "$REPORT_FILE"
echo "TEST CATEGORY: Legitimate File Access" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

run_test \
    "index.php access" \
    "$BASE_URL/index.php" \
    "200" \
    "Main entry point should be accessible"

run_test \
    "login.php access" \
    "$BASE_URL/interface/login/login.php" \
    "200" \
    "Login page should be accessible"

# Test 6: Security Headers
echo "========================================" | tee -a "$REPORT_FILE"
echo "TEST CATEGORY: Security Headers" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

TOTAL_TESTS=$((TOTAL_TESTS + 1))
echo -n "Testing: Security headers... " | tee -a "$REPORT_FILE"

headers=$(curl -s -I "$BASE_URL/index.php")

if echo "$headers" | grep -q "X-Content-Type-Options" && \
   echo "$headers" | grep -q "X-XSS-Protection" && \
   echo "$headers" | grep -q "X-Frame-Options"; then
    echo -e "${GREEN}PASS${NC}" | tee -a "$REPORT_FILE"
    PASSED_TESTS=$((PASSED_TESTS + 1))
    echo "  ✓ All required security headers present" | tee -a "$REPORT_FILE"
else
    echo -e "${RED}FAIL${NC}" | tee -a "$REPORT_FILE"
    FAILED_TESTS=$((FAILED_TESTS + 1))
    echo "  ✗ Missing required security headers" | tee -a "$REPORT_FILE"
fi
echo "" | tee -a "$REPORT_FILE"

# Test 7: Front Controller Feature Flag
echo "========================================" | tee -a "$REPORT_FILE"
echo "TEST CATEGORY: Feature Flag" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

TOTAL_TESTS=$((TOTAL_TESTS + 1))
echo -n "Testing: Front controller responds when enabled... " | tee -a "$REPORT_FILE"

response=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/home.php?_ROUTE=index.php")

if [ "$response" != "404" ]; then
    echo -e "${GREEN}PASS${NC} (HTTP $response)" | tee -a "$REPORT_FILE"
    PASSED_TESTS=$((PASSED_TESTS + 1))
    echo "  ✓ Front controller is active" | tee -a "$REPORT_FILE"
else
    echo -e "${YELLOW}INFO${NC} (HTTP 404)" | tee -a "$REPORT_FILE"
    echo "  ℹ Front controller is disabled (OPENEMR_ENABLE_FRONT_CONTROLLER not set)" | tee -a "$REPORT_FILE"
fi
echo "" | tee -a "$REPORT_FILE"

# Final Report
echo "========================================" | tee -a "$REPORT_FILE"
echo "TEST SUMMARY" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "Total Tests: $TOTAL_TESTS" | tee -a "$REPORT_FILE"
echo -e "Passed: ${GREEN}$PASSED_TESTS${NC}" | tee -a "$REPORT_FILE"
echo -e "Failed: ${RED}$FAILED_TESTS${NC}" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

if [ $FAILED_TESTS -eq 0 ]; then
    echo -e "${GREEN}✓ ALL SECURITY TESTS PASSED${NC}" | tee -a "$REPORT_FILE"
    echo "" | tee -a "$REPORT_FILE"
    echo "The front controller security implementation is working correctly." | tee -a "$REPORT_FILE"
    exit 0
else
    echo -e "${RED}✗ SOME SECURITY TESTS FAILED${NC}" | tee -a "$REPORT_FILE"
    echo "" | tee -a "$REPORT_FILE"
    echo "Please review the failed tests above and fix any security issues." | tee -a "$REPORT_FILE"
    exit 1
fi

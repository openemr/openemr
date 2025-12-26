#!/bin/bash

# OpenEMR Vietnamese PT Integration Test Script
# Tests login functionality and Vietnamese PT module integration

BASE_URL="http://localhost:8300"
USERNAME="admin"
PASSWORD="pass"
COOKIE_FILE="/tmp/openemr-cookies.txt"
SCREENSHOT_DIR="test-screenshots"
TEST_RESULTS_FILE="$SCREENSHOT_DIR/test-results.txt"

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Create screenshot directory
mkdir -p "$SCREENSHOT_DIR"

# Initialize results
TESTS_PASSED=0
TESTS_FAILED=0
TESTS_TOTAL=0

# Function to log test result
log_test() {
    local test_name="$1"
    local status="$2"
    local message="$3"

    TESTS_TOTAL=$((TESTS_TOTAL + 1))

    if [ "$status" == "PASS" ]; then
        TESTS_PASSED=$((TESTS_PASSED + 1))
        echo -e "${GREEN}✓ PASS${NC}: $test_name - $message"
    else
        TESTS_FAILED=$((TESTS_FAILED + 1))
        echo -e "${RED}✗ FAIL${NC}: $test_name - $message"
    fi

    echo "$status: $test_name - $message" >> "$TEST_RESULTS_FILE"
}

# Clean up old cookie file
rm -f "$COOKIE_FILE"

echo ""
echo "=== Starting OpenEMR Vietnamese PT Integration Tests ==="
echo ""
echo "Base URL: $BASE_URL"
echo "Screenshot Directory: $SCREENSHOT_DIR"
echo ""

# TEST 1: Login Functionality
echo "--- Test 1: Login Functionality ---"
echo ""

# Step 1.1: Check if login page loads
echo "Testing login page accessibility..."
LOGIN_RESPONSE=$(curl -s -w "\n%{http_code}" "$BASE_URL/interface/login/login.php?site=default" -c "$COOKIE_FILE")
HTTP_CODE=$(echo "$LOGIN_RESPONSE" | tail -1)
LOGIN_PAGE=$(echo "$LOGIN_RESPONSE" | head -n -1)

if [ "$HTTP_CODE" == "200" ]; then
    log_test "1.1 Login Page Loads" "PASS" "HTTP 200 received"
    echo "$LOGIN_PAGE" > "$SCREENSHOT_DIR/01-login-page.html"
else
    log_test "1.1 Login Page Loads" "FAIL" "HTTP $HTTP_CODE received"
fi

# Step 1.2: Verify login form elements
echo "Checking for login form elements..."
if echo "$LOGIN_PAGE" | grep -q "authUser" && echo "$LOGIN_PAGE" | grep -q "authPass"; then
    log_test "1.2 Login Form Elements" "PASS" "Username and password fields found"
else
    log_test "1.2 Login Form Elements" "FAIL" "Login form fields not found"
fi

# Step 1.3-1.5: Perform login
echo "Attempting login..."
LOGIN_POST_RESPONSE=$(curl -s -w "\n%{http_code}" \
    -X POST \
    "$BASE_URL/interface/main/main_screen.php?auth=login&site=default" \
    -b "$COOKIE_FILE" \
    -c "$COOKIE_FILE" \
    -d "authUser=$USERNAME" \
    -d "authPass=$PASSWORD" \
    -d "languageChoice=1" \
    -L)

LOGIN_POST_CODE=$(echo "$LOGIN_POST_RESPONSE" | tail -1)
LOGIN_RESULT=$(echo "$LOGIN_POST_RESPONSE" | head -n -1)

if [ "$LOGIN_POST_CODE" == "200" ]; then
    log_test "1.3-1.5 Login Request" "PASS" "Login POST returned HTTP 200"
    echo "$LOGIN_RESULT" > "$SCREENSHOT_DIR/02-after-login.html"
else
    log_test "1.3-1.5 Login Request" "FAIL" "Login POST returned HTTP $LOGIN_POST_CODE"
fi

# Step 1.6: Verify successful login
echo "Verifying login success..."
MAIN_PAGE_RESPONSE=$(curl -s -w "\n%{http_code}" \
    "$BASE_URL/interface/main/main_screen.php" \
    -b "$COOKIE_FILE")

MAIN_PAGE_CODE=$(echo "$MAIN_PAGE_RESPONSE" | tail -1)
MAIN_PAGE=$(echo "$MAIN_PAGE_RESPONSE" | head -n -1)

if [ "$MAIN_PAGE_CODE" == "200" ] && ! echo "$MAIN_PAGE" | grep -q "login"; then
    log_test "1.6 Verify Login Success" "PASS" "Successfully accessed main screen"
    echo "$MAIN_PAGE" > "$SCREENSHOT_DIR/03-dashboard.html"
else
    log_test "1.6 Verify Login Success" "FAIL" "Could not verify successful login"
fi

# TEST 2: Check Database for Vietnamese PT Tables
echo ""
echo "--- Test 2: Database Verification ---"
echo ""

# Check if we can access the database through the container
DB_TABLES=$(docker exec openemr mysql -u openemr -popenemr -D openemr -e "SHOW TABLES LIKE 'pt_%_bilingual'" 2>/dev/null || echo "")

if [ -n "$DB_TABLES" ]; then
    TABLE_COUNT=$(echo "$DB_TABLES" | grep -c "pt_" || echo "0")
    if [ "$TABLE_COUNT" -gt "0" ]; then
        log_test "2.1 Vietnamese PT Tables" "PASS" "Found $TABLE_COUNT Vietnamese PT tables in database"
        echo "$DB_TABLES" > "$SCREENSHOT_DIR/db-tables.txt"
    else
        log_test "2.1 Vietnamese PT Tables" "FAIL" "No Vietnamese PT tables found"
    fi
else
    log_test "2.1 Vietnamese PT Tables" "FAIL" "Could not access database"
fi

# Check for medical terms table
MEDICAL_TERMS=$(docker exec openemr mysql -u openemr -popenemr -D openemr -e "SELECT COUNT(*) as count FROM vietnamese_medical_terms" 2>/dev/null || echo "0")
TERM_COUNT=$(echo "$MEDICAL_TERMS" | tail -1)

if [ "$TERM_COUNT" -gt "0" ]; then
    log_test "2.2 Medical Terms Data" "PASS" "Found $TERM_COUNT medical terms in database"
else
    log_test "2.2 Medical Terms Data" "FAIL" "No medical terms data found"
fi

# TEST 3: Check for Vietnamese PT Files
echo ""
echo "--- Test 3: File System Verification ---"
echo ""

# Check for Vietnamese PT service files
cd /home/dang/dev/openemr
SERVICE_FILES=$(find src/Services/VietnamesePT -name "*.php" 2>/dev/null | wc -l)

if [ "$SERVICE_FILES" -gt "0" ]; then
    log_test "3.1 Vietnamese PT Services" "PASS" "Found $SERVICE_FILES service files"
else
    log_test "3.1 Vietnamese PT Services" "FAIL" "No service files found"
fi

# Check for REST controllers
CONTROLLER_FILES=$(find src/RestControllers/VietnamesePT -name "*.php" 2>/dev/null | wc -l)

if [ "$CONTROLLER_FILES" -gt "0" ]; then
    log_test "3.2 Vietnamese PT Controllers" "PASS" "Found $CONTROLLER_FILES controller files"
else
    log_test "3.2 Vietnamese PT Controllers" "FAIL" "No controller files found"
fi

# Check for form files
FORM_DIRS=$(find interface/forms -name "vietnamese_pt_*" -type d 2>/dev/null | wc -l)

if [ "$FORM_DIRS" -gt "0" ]; then
    log_test "3.3 Vietnamese PT Forms" "PASS" "Found $FORM_DIRS form directories"
else
    log_test "3.3 Vietnamese PT Forms" "FAIL" "No form directories found"
fi

# Check for widget file
if [ -f "library/custom/vietnamese_pt_widget.php" ]; then
    log_test "3.4 Vietnamese PT Widget" "PASS" "Widget file exists"
else
    log_test "3.4 Vietnamese PT Widget" "FAIL" "Widget file not found"
fi

# TEST 4: Check API Routes
echo ""
echo "--- Test 4: API Route Verification ---"
echo ""

# Check if routes file includes Vietnamese PT routes
if grep -q "vietnamese-pt" apis/routes/_rest_routes_standard.inc.php 2>/dev/null; then
    ROUTE_COUNT=$(grep -c "vietnamese-pt" apis/routes/_rest_routes_standard.inc.php)
    log_test "4.1 API Routes Registered" "PASS" "Found $ROUTE_COUNT Vietnamese PT routes"
else
    log_test "4.1 API Routes Registered" "FAIL" "No Vietnamese PT routes found in routes file"
fi

# Try to access API endpoint (without auth for now, just check if it exists)
API_RESPONSE=$(curl -s -w "\n%{http_code}" "$BASE_URL/apis/default/vietnamese-pt/medical-terms" 2>/dev/null || echo "")
API_CODE=$(echo "$API_RESPONSE" | tail -1)

if [ "$API_CODE" == "401" ] || [ "$API_CODE" == "200" ]; then
    log_test "4.2 API Endpoint Accessible" "PASS" "Endpoint exists (HTTP $API_CODE)"
else
    log_test "4.2 API Endpoint Accessible" "FAIL" "Endpoint returned HTTP $API_CODE or not accessible"
fi

# TEST 5: Check for Widget Integration
echo ""
echo "--- Test 5: Widget Integration Verification ---"
echo ""

# Check if demographics.php includes the widget
if grep -q "vietnamese_pt_widget" interface/patient_file/summary/demographics.php 2>/dev/null; then
    log_test "5.1 Widget Integration" "PASS" "Widget is integrated in demographics.php"
else
    log_test "5.1 Widget Integration" "FAIL" "Widget not found in demographics.php"
fi

# TEST 6: Check Patient Summary Page (if we can access it)
echo ""
echo "--- Test 6: Patient Summary Access ---"
echo ""

# Try to access patient summary (needs patient ID)
PATIENT_LIST_RESPONSE=$(curl -s -w "\n%{http_code}" \
    "$BASE_URL/interface/main/finder/patient_select.php" \
    -b "$COOKIE_FILE")

PATIENT_LIST_CODE=$(echo "$PATIENT_LIST_RESPONSE" | tail -1)
PATIENT_LIST=$(echo "$PATIENT_LIST_RESPONSE" | head -n -1)

if [ "$PATIENT_LIST_CODE" == "200" ]; then
    log_test "6.1 Patient Finder Access" "PASS" "Can access patient finder"
    echo "$PATIENT_LIST" > "$SCREENSHOT_DIR/patient-list.html"

    # Try to extract a patient ID
    PATIENT_ID=$(echo "$PATIENT_LIST" | grep -oP 'pid=\K\d+' | head -1)

    if [ -n "$PATIENT_ID" ]; then
        # Access patient summary
        SUMMARY_RESPONSE=$(curl -s -w "\n%{http_code}" \
            "$BASE_URL/interface/patient_file/summary/demographics.php?set_pid=$PATIENT_ID" \
            -b "$COOKIE_FILE")

        SUMMARY_CODE=$(echo "$SUMMARY_RESPONSE" | tail -1)
        SUMMARY_PAGE=$(echo "$SUMMARY_RESPONSE" | head -n -1)

        if [ "$SUMMARY_CODE" == "200" ]; then
            log_test "6.2 Patient Summary Access" "PASS" "Accessed patient summary (PID: $PATIENT_ID)"
            echo "$SUMMARY_PAGE" > "$SCREENSHOT_DIR/patient-summary.html"

            # Check if Vietnamese PT widget is present
            if echo "$SUMMARY_PAGE" | grep -q "Vietnamese"; then
                log_test "6.3 Vietnamese PT Widget Visible" "PASS" "Widget content found in patient summary"
            else
                log_test "6.3 Vietnamese PT Widget Visible" "FAIL" "Widget content not visible in patient summary"
            fi
        else
            log_test "6.2 Patient Summary Access" "FAIL" "Could not access patient summary"
        fi
    else
        log_test "6.2 Patient Summary Access" "FAIL" "No patient ID found"
    fi
else
    log_test "6.1 Patient Finder Access" "FAIL" "Could not access patient finder"
fi

# Generate Summary
echo ""
echo "=== TEST SUMMARY ==="
echo ""
echo "Total Tests: $TESTS_TOTAL"
echo -e "Passed: ${GREEN}$TESTS_PASSED${NC}"
echo -e "Failed: ${RED}$TESTS_FAILED${NC}"

SUCCESS_RATE=$(awk "BEGIN {printf \"%.2f\", ($TESTS_PASSED / $TESTS_TOTAL) * 100}")
echo "Success Rate: $SUCCESS_RATE%"

echo ""
echo "=== Vietnamese PT Integration Assessment ==="
echo ""

if [ "$TESTS_FAILED" -eq 0 ]; then
    echo -e "${GREEN}ASSESSMENT: Vietnamese PT integration appears to be fully working.${NC}"
elif [ "$TESTS_PASSED" -gt "$((TESTS_TOTAL / 2))" ]; then
    echo -e "${YELLOW}ASSESSMENT: Vietnamese PT integration is partially working.${NC}"
    echo "Some features are integrated but may need additional configuration."
else
    echo -e "${RED}ASSESSMENT: Vietnamese PT integration may have issues.${NC}"
    echo "Several integration points were not found or not working."
fi

echo ""
echo "Test results saved to: $TEST_RESULTS_FILE"
echo "HTML snapshots saved to: $SCREENSHOT_DIR/"
echo ""
echo "=== Tests Complete ==="
echo ""

# Save summary
echo "" >> "$TEST_RESULTS_FILE"
echo "=== SUMMARY ===" >> "$TEST_RESULTS_FILE"
echo "Total: $TESTS_TOTAL" >> "$TEST_RESULTS_FILE"
echo "Passed: $TESTS_PASSED" >> "$TEST_RESULTS_FILE"
echo "Failed: $TESTS_FAILED" >> "$TEST_RESULTS_FILE"
echo "Success Rate: $SUCCESS_RATE%" >> "$TEST_RESULTS_FILE"

exit $TESTS_FAILED

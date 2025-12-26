#!/bin/bash

###############################################################################
# Vietnamese PT Module - Test Runner Script
# AI-GENERATED CODE
###############################################################################

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../../.." && pwd)"

cd "$PROJECT_ROOT"

echo "============================================"
echo "Vietnamese PT Module - Test Suite Runner"
echo "============================================"
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Function to print section headers
print_header() {
    echo ""
    echo -e "${YELLOW}========================================${NC}"
    echo -e "${YELLOW}$1${NC}"
    echo -e "${YELLOW}========================================${NC}"
    echo ""
}

# Function to run a test suite
run_suite() {
    local suite_name=$1
    local description=$2

    print_header "$description"

    if ./vendor/bin/phpunit --testsuite "$suite_name" --testdox; then
        echo -e "${GREEN}✓ $description PASSED${NC}"
        return 0
    else
        echo -e "${RED}✗ $description FAILED${NC}"
        return 1
    fi
}

# Parse command line arguments
TEST_TYPE=${1:-all}

case $TEST_TYPE in
    e2e)
        print_header "Running E2E Tests Only"
        run_suite "vietnamese-e2e" "Vietnamese PT E2E Browser Tests"
        ;;

    api)
        print_header "Running API Tests Only"
        run_suite "vietnamese-api" "Vietnamese PT API Integration Tests"
        ;;

    performance)
        print_header "Running Performance Tests Only"
        run_suite "vietnamese-performance" "Vietnamese PT Performance Tests"
        ;;

    unit)
        print_header "Running Existing Unit Tests Only"
        run_suite "vietnamese" "Vietnamese PT Unit Tests"
        ;;

    all)
        print_header "Running All Vietnamese PT Tests"

        FAILED=0

        echo "1/4: Unit Tests..."
        run_suite "vietnamese" "Vietnamese PT Unit Tests" || FAILED=$((FAILED+1))

        echo ""
        echo "2/4: API Integration Tests..."
        run_suite "vietnamese-api" "Vietnamese PT API Tests" || FAILED=$((FAILED+1))

        echo ""
        echo "3/4: E2E Browser Tests..."
        run_suite "vietnamese-e2e" "Vietnamese PT E2E Tests" || FAILED=$((FAILED+1))

        echo ""
        echo "4/4: Performance Tests..."
        run_suite "vietnamese-performance" "Vietnamese PT Performance Tests" || FAILED=$((FAILED+1))

        echo ""
        print_header "Test Summary"

        if [ $FAILED -eq 0 ]; then
            echo -e "${GREEN}✓ All test suites passed!${NC}"
            exit 0
        else
            echo -e "${RED}✗ $FAILED test suite(s) failed${NC}"
            exit 1
        fi
        ;;

    coverage)
        print_header "Running Tests with Coverage Report"

        if command -v php &> /dev/null; then
            if php -m | grep -q xdebug; then
                echo "Xdebug detected, generating coverage report..."
                ./vendor/bin/phpunit --testsuite vietnamese-pt \
                    --coverage-html "$PROJECT_ROOT/coverage-vietnamese-pt" \
                    --coverage-text

                echo ""
                echo -e "${GREEN}Coverage report generated in: coverage-vietnamese-pt/index.html${NC}"
            else
                echo -e "${YELLOW}Warning: Xdebug not installed. Coverage report requires Xdebug.${NC}"
                echo "Running tests without coverage..."
                run_suite "vietnamese-pt" "All Vietnamese PT Tests"
            fi
        fi
        ;;

    quick)
        print_header "Quick Test Run (API + Unit only)"

        run_suite "vietnamese" "Vietnamese PT Unit Tests"
        echo ""
        run_suite "vietnamese-api" "Vietnamese PT API Tests"
        ;;

    *)
        echo "Usage: $0 {all|e2e|api|performance|unit|coverage|quick}"
        echo ""
        echo "Test suites:"
        echo "  all         - Run all Vietnamese PT tests (default)"
        echo "  e2e         - Run E2E browser tests only"
        echo "  api         - Run API integration tests only"
        echo "  performance - Run performance/load tests only"
        echo "  unit        - Run existing unit tests only"
        echo "  coverage    - Run all tests with code coverage report"
        echo "  quick       - Quick test run (unit + api only)"
        echo ""
        exit 1
        ;;
esac

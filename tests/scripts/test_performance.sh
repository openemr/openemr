#!/bin/bash

# Front Controller Performance Test Script
#
# Measures performance impact: direct vs. routed access
# Metrics: Response time, throughput, resource usage
#
# AI DISCLOSURE: This file contains code generated using Claude AI (Anthropic)
#
# Usage: ./test_performance.sh [base_url] [iterations]
# Example: ./test_performance.sh http://localhost/openemr 100

set -e

# Configuration
BASE_URL="${1:-http://localhost/openemr}"
ITERATIONS="${2:-100}"
REPORT_DIR="$(dirname "$0")/../../reports"
REPORT_FILE="$REPORT_DIR/performance-test-report-$(date +%Y%m%d-%H%M%S).txt"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Create report directory
mkdir -p "$REPORT_DIR"

echo "Performance Test Report" | tee "$REPORT_FILE"
echo "Date: $(date)" | tee -a "$REPORT_FILE"
echo "URL: $BASE_URL" | tee -a "$REPORT_FILE"
echo "Iterations: $ITERATIONS" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

# Check if Apache Bench (ab) is available
if ! command -v ab &> /dev/null; then
    echo -e "${YELLOW}WARNING: Apache Bench (ab) not found. Installing via brew...${NC}" | tee -a "$REPORT_FILE"
    if command -v brew &> /dev/null; then
        brew install httpd
    else
        echo -e "${RED}ERROR: Apache Bench (ab) not available. Please install it manually.${NC}" | tee -a "$REPORT_FILE"
        echo "  macOS: brew install httpd" | tee -a "$REPORT_FILE"
        echo "  Linux: sudo apt-get install apache2-utils" | tee -a "$REPORT_FILE"
        exit 1
    fi
fi

# Function to run performance test
run_performance_test() {
    local test_name="$1"
    local url="$2"
    local description="$3"

    echo "----------------------------------------" | tee -a "$REPORT_FILE"
    echo -e "${BLUE}Test: $test_name${NC}" | tee -a "$REPORT_FILE"
    echo "URL: $url" | tee -a "$REPORT_FILE"
    echo "Description: $description" | tee -a "$REPORT_FILE"
    echo "" | tee -a "$REPORT_FILE"

    # Run Apache Bench test
    ab_output=$(ab -n "$ITERATIONS" -c 10 -q "$url" 2>&1 || true)

    # Extract key metrics
    requests_per_sec=$(echo "$ab_output" | grep "Requests per second" | awk '{print $4}')
    time_per_request=$(echo "$ab_output" | grep "Time per request.*mean\)" | head -1 | awk '{print $4}')
    failed_requests=$(echo "$ab_output" | grep "Failed requests" | awk '{print $3}')

    # Extract percentiles
    p50=$(echo "$ab_output" | grep "50%" | awk '{print $2}')
    p95=$(echo "$ab_output" | grep "95%" | awk '{print $2}')
    p99=$(echo "$ab_output" | grep "99%" | awk '{print $2}')

    # Display results
    echo "Results:" | tee -a "$REPORT_FILE"
    echo "  Requests/sec: $requests_per_sec" | tee -a "$REPORT_FILE"
    echo "  Time/request: ${time_per_request}ms (mean)" | tee -a "$REPORT_FILE"
    echo "  Failed requests: $failed_requests" | tee -a "$REPORT_FILE"
    echo "  50th percentile: ${p50}ms" | tee -a "$REPORT_FILE"
    echo "  95th percentile: ${p95}ms" | tee -a "$REPORT_FILE"
    echo "  99th percentile: ${p99}ms" | tee -a "$REPORT_FILE"
    echo "" | tee -a "$REPORT_FILE"

    # Return metrics as array (for comparison)
    echo "$requests_per_sec|$time_per_request|$p95"
}

# Test 1: Direct File Access (Baseline)
echo "========================================" | tee -a "$REPORT_FILE"
echo "BASELINE: Direct File Access" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

baseline=$(run_performance_test \
    "Direct index.php access" \
    "$BASE_URL/index.php" \
    "Direct file access without front controller")

baseline_rps=$(echo "$baseline" | cut -d'|' -f1)
baseline_time=$(echo "$baseline" | cut -d'|' -f2)
baseline_p95=$(echo "$baseline" | cut -d'|' -f3)

# Test 2: Front Controller Routing
echo "========================================" | tee -a "$REPORT_FILE"
echo "FRONT CONTROLLER: Routed Access" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

fc_result=$(run_performance_test \
    "Front controller routing" \
    "$BASE_URL/home.php?_ROUTE=index.php" \
    "Access through front controller")

fc_rps=$(echo "$fc_result" | cut -d'|' -f1)
fc_time=$(echo "$fc_result" | cut -d'|' -f2)
fc_p95=$(echo "$fc_result" | cut -d'|' -f3)

# Test 3: Login Page (Anonymous Access)
echo "========================================" | tee -a "$REPORT_FILE"
echo "TEST: Login Page Performance" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

login_result=$(run_performance_test \
    "Login page access" \
    "$BASE_URL/interface/login/login.php" \
    "Anonymous page with ignoreAuth pattern")

login_rps=$(echo "$login_result" | cut -d'|' -f1)
login_time=$(echo "$login_result" | cut -d'|' -f2)

# Test 4: Static Assets
echo "========================================" | tee -a "$REPORT_FILE"
echo "TEST: Static Asset Performance" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

# Check if public directory exists
if [ -d "$(dirname "$0")/../../public/assets" ]; then
    static_result=$(run_performance_test \
        "Static CSS file" \
        "$BASE_URL/public/assets/css/style.css" \
        "Static assets should bypass front controller")

    static_rps=$(echo "$static_result" | cut -d'|' -f1)
else
    echo "  ℹ Skipping static asset tests (public/assets directory not found)" | tee -a "$REPORT_FILE"
    echo "" | tee -a "$REPORT_FILE"
fi

# Test 5: REST API
echo "========================================" | tee -a "$REPORT_FILE"
echo "TEST: REST API Performance" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

api_result=$(run_performance_test \
    "REST API endpoint" \
    "$BASE_URL/apis/default/api/patient" \
    "Existing API front controller (should not change)")

api_rps=$(echo "$api_result" | cut -d'|' -f1)

# Performance Analysis
echo "========================================" | tee -a "$REPORT_FILE"
echo "PERFORMANCE ANALYSIS" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

# Calculate overhead
if [ -n "$baseline_time" ] && [ -n "$fc_time" ]; then
    overhead=$(awk "BEGIN {printf \"%.2f\", ($fc_time - $baseline_time) / $baseline_time * 100}")

    echo "Front Controller Overhead:" | tee -a "$REPORT_FILE"
    echo "  Baseline (direct): ${baseline_time}ms" | tee -a "$REPORT_FILE"
    echo "  Front controller: ${fc_time}ms" | tee -a "$REPORT_FILE"
    echo "  Overhead: ${overhead}%" | tee -a "$REPORT_FILE"
    echo "" | tee -a "$REPORT_FILE"

    # Performance verdict
    if (( $(echo "$overhead < 5" | bc -l) )); then
        echo -e "${GREEN}✓ EXCELLENT: Overhead < 5%${NC}" | tee -a "$REPORT_FILE"
        echo "  Front controller has negligible performance impact" | tee -a "$REPORT_FILE"
    elif (( $(echo "$overhead < 10" | bc -l) )); then
        echo -e "${GREEN}✓ GOOD: Overhead < 10%${NC}" | tee -a "$REPORT_FILE"
        echo "  Front controller has acceptable performance impact" | tee -a "$REPORT_FILE"
    elif (( $(echo "$overhead < 20" | bc -l) )); then
        echo -e "${YELLOW}⚠ ACCEPTABLE: Overhead < 20%${NC}" | tee -a "$REPORT_FILE"
        echo "  Front controller adds some overhead, consider optimization" | tee -a "$REPORT_FILE"
    else
        echo -e "${RED}✗ HIGH OVERHEAD: > 20%${NC}" | tee -a "$REPORT_FILE"
        echo "  Front controller adds significant overhead, optimization needed" | tee -a "$REPORT_FILE"
    fi
fi

echo "" | tee -a "$REPORT_FILE"

# Throughput comparison
echo "Throughput Comparison:" | tee -a "$REPORT_FILE"
echo "  Baseline: ${baseline_rps} req/sec" | tee -a "$REPORT_FILE"
echo "  Front controller: ${fc_rps} req/sec" | tee -a "$REPORT_FILE"
echo "  Login page: ${login_rps} req/sec" | tee -a "$REPORT_FILE"
if [ -n "$static_rps" ]; then
    echo "  Static assets: ${static_rps} req/sec" | tee -a "$REPORT_FILE"
fi
echo "  REST API: ${api_rps} req/sec" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

# Latency Analysis
echo "Latency Analysis (95th percentile):" | tee -a "$REPORT_FILE"
echo "  Baseline: ${baseline_p95}ms" | tee -a "$REPORT_FILE"
echo "  Front controller: ${fc_p95}ms" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

# Resource Usage (if available)
if command -v ps &> /dev/null; then
    echo "Current Resource Usage:" | tee -a "$REPORT_FILE"
    php_procs=$(ps aux | grep php | grep -v grep | wc -l)
    echo "  Active PHP processes: $php_procs" | tee -a "$REPORT_FILE"

    if command -v top &> /dev/null; then
        cpu_usage=$(top -l 1 | grep "CPU usage" | awk '{print $3}' || echo "N/A")
        echo "  CPU usage: $cpu_usage" | tee -a "$REPORT_FILE"
    fi
    echo "" | tee -a "$REPORT_FILE"
fi

# Recommendations
echo "========================================" | tee -a "$REPORT_FILE"
echo "RECOMMENDATIONS" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "" | tee -a "$REPORT_FILE"

if [ -n "$overhead" ]; then
    if (( $(echo "$overhead < 10" | bc -l) )); then
        echo "✓ Performance is acceptable for production use" | tee -a "$REPORT_FILE"
        echo "✓ Front controller adds minimal overhead" | tee -a "$REPORT_FILE"
        echo "✓ Security benefits outweigh performance impact" | tee -a "$REPORT_FILE"
    else
        echo "⚠ Consider performance optimizations:" | tee -a "$REPORT_FILE"
        echo "  - Enable OPcache for PHP" | tee -a "$REPORT_FILE"
        echo "  - Use FastCGI process manager (PHP-FPM)" | tee -a "$REPORT_FILE"
        echo "  - Enable static asset caching" | tee -a "$REPORT_FILE"
        echo "  - Consider CDN for static assets" | tee -a "$REPORT_FILE"
    fi
fi

echo "" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"
echo "Report saved to: $REPORT_FILE" | tee -a "$REPORT_FILE"
echo "========================================" | tee -a "$REPORT_FILE"

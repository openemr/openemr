#!/bin/bash

# Front Controller Performance Test Script
#
# Measures performance impact: direct vs. routed access
# Metrics: Response time, throughput, resource usage
#
# AI DISCLOSURE: This file contains code generated using Claude AI (Anthropic)
#
# Copyright (c) 2025 OpenCoreEMR, Inc.
# License: GPLv3
#
# Usage: ./test_performance.sh [base_url] [iterations]
# Example: ./test_performance.sh http://localhost/openemr 100

set -e
# Ensure errexit is inherited in command substitutions (addresses SC2311)
shopt -s inherit_errexit 2>/dev/null || true

# Configuration
BASE_URL="${1:-http://localhost/openemr}"
ITERATIONS="${2:-100}"
REPORT_DIR="$(dirname "${0}")/../../reports"
REPORT_FILE="${REPORT_DIR}/performance-test-report-$(date +%Y%m%d-%H%M%S).txt"

# Colors for output using tput (with fallback for non-interactive environments)
if command -v tput >/dev/null 2>&1 && [[ -n "${TERM}" ]]; then
    RED=$(tput setaf 1)
    GREEN=$(tput setaf 2)
    BLUE=$(tput setaf 4)
    NC=$(tput sgr0) # No Color
else
    RED=""
    GREEN=""
    BLUE=""
    NC=""
fi

# Create report directory
mkdir -p "${REPORT_DIR}"

{
    echo "Performance Test Report"
    echo "Date: $(date)"
    echo "URL: ${BASE_URL}"
    echo "Iterations: ${ITERATIONS}"
    echo
} | tee "${REPORT_FILE}"

# Check if Apache Bench (ab) is available
if ! command -v ab &> /dev/null; then
    {
        printf "%sERROR: Apache Bench (ab) not available. Please install it manually.%s\n" "${RED}" "${NC}"
        echo "  macOS: brew install httpd"
        echo "  Linux: sudo apt-get install apache2-utils"
    } | tee -a "${REPORT_FILE}"
    exit 1
fi

# Function to evaluate overhead performance
# $1: overhead percentage
evaluate_overhead() {
    local overhead="${1}"

    bc_result=$(bc -l <<< "${overhead} < 5")
    if (( bc_result )); then
        {
            printf "%s✓ EXCELLENT: Overhead < 5%%%s\n" "${GREEN}" "${NC}"
            echo "  Front controller has negligible performance impact"
        } | tee -a "${REPORT_FILE}"
    else
        bc_result=$(bc -l <<< "${overhead} < 10")
        if (( bc_result )); then
            {
                printf "%s✓ GOOD: Overhead < 10%%%s\n" "${GREEN}" "${NC}"
                echo "  Front controller has acceptable performance impact"
            } | tee -a "${REPORT_FILE}"
        else
            bc_result=$(bc -l <<< "${overhead} < 20")
            if (( bc_result )); then
                {
                    printf "⚠ ACCEPTABLE: Overhead < 20%%\n"
                    echo "  Front controller adds some overhead, consider optimization"
                } | tee -a "${REPORT_FILE}"
            else
                {
                    printf "%s✗ HIGH OVERHEAD: > 20%%%s\n" "${RED}" "${NC}"
                    echo "  Front controller adds significant overhead, optimization needed"
                } | tee -a "${REPORT_FILE}"
            fi
        fi
    fi
}

# Function to run performance test
run_performance_test() {
    local test_name="${1}"
    local url="${2}"
    local description="${3}"

    {
        echo "----------------------------------------"
        printf "%sTest: %s%s\n" "${BLUE}" "${test_name}" "${NC}"
        echo "URL: ${url}"
        echo "Description: ${description}"
        echo
    } | tee -a "${REPORT_FILE}"

    # Run Apache Bench test
    if ! ab_output=$(ab -n "${ITERATIONS}" -c 10 -q "${url}" 2>&1); then
        {
            printf "%sERROR: Apache Bench failed for %s%s\n" "${RED}" "${url}" "${NC}"
            echo "Output: ${ab_output}"
        } | tee -a "${REPORT_FILE}"
        return 1
    fi

    # Extract key metrics using awk with safe defaults
    requests_per_sec=$(awk '/Requests per second/ {print $4}' <<< "${ab_output}" || echo "0")
    time_per_request=$(awk '/Time per request.*mean\)/ {print $4; exit}' <<< "${ab_output}" || echo "0")
    failed_requests=$(awk '/Failed requests/ {print $3}' <<< "${ab_output}" || echo "0")

    # Extract percentiles using awk with safe defaults
    p50=$(awk '/50%/ {print $2}' <<< "${ab_output}" || echo "0")
    p95=$(awk '/95%/ {print $2}' <<< "${ab_output}" || echo "0")
    p99=$(awk '/99%/ {print $2}' <<< "${ab_output}" || echo "0")

    # Display results
    {
        echo "Results:"
        echo "  Requests/sec: ${requests_per_sec}"
        echo "  Time/request: ${time_per_request}ms (mean)"
        echo "  Failed requests: ${failed_requests}"
        echo "  50th percentile: ${p50}ms"
        echo "  95th percentile: ${p95}ms"
        echo "  99th percentile: ${p99}ms"
        echo
    } | tee -a "${REPORT_FILE}"

    # Return metrics as array (for comparison)
    echo "${requests_per_sec}|${time_per_request}|${p95}"
}

# Test 1: Direct File Access (Baseline)
{
    echo "========================================"
    echo "BASELINE: Direct File Access"
    echo "========================================"
    echo
} | tee -a "${REPORT_FILE}"

baseline=$(run_performance_test \
    "Direct index.php access" \
    "${BASE_URL}/index.php" \
    "Direct file access without front controller")

baseline_rps=$(cut -d'|' -f1 <<< "${baseline}")
baseline_time=$(cut -d'|' -f2 <<< "${baseline}")
baseline_p95=$(cut -d'|' -f3 <<< "${baseline}")

# Test 2: Front Controller Routing
{
    echo "========================================"
    echo "FRONT CONTROLLER: Routed Access"
    echo "========================================"
    echo
} | tee -a "${REPORT_FILE}"

fc_result=$(run_performance_test \
    "Front controller routing" \
    "${BASE_URL}/home.php?_ROUTE=index.php" \
    "Access through front controller")

fc_rps=$(cut -d'|' -f1 <<< "${fc_result}")
fc_time=$(cut -d'|' -f2 <<< "${fc_result}")
fc_p95=$(cut -d'|' -f3 <<< "${fc_result}")

# Test 3: Login Page (Anonymous Access)
{
    echo "========================================"
    echo "TEST: Login Page Performance"
    echo "========================================"
    echo
} | tee -a "${REPORT_FILE}"

login_result=$(run_performance_test \
    "Login page access" \
    "${BASE_URL}/interface/login/login.php" \
    "Anonymous page with ignoreAuth pattern")

login_rps=$(cut -d'|' -f1 <<< "${login_result}")
_login_time=$(cut -d'|' -f2 <<< "${login_result}")

# Test 4: Static Assets
{
    echo "========================================"
    echo "TEST: Static Asset Performance"
    echo "========================================"
    echo
} | tee -a "${REPORT_FILE}"

# Check if public directory exists
if [[ -d "$(dirname "${0}")/../../public/assets" ]]; then
    static_result=$(run_performance_test \
        "Static CSS file" \
        "${BASE_URL}/public/assets/css/style.css" \
        "Static assets should bypass front controller")

    static_rps=$(cut -d'|' -f1 <<< "${static_result}")
else
    {
        echo "  ℹ Skipping static asset tests (public/assets directory not found)"
        echo
    } | tee -a "${REPORT_FILE}"
fi

# Test 5: REST API
{
    echo "========================================"
    echo "TEST: REST API Performance"
    echo "========================================"
    echo
} | tee -a "${REPORT_FILE}"

api_result=$(run_performance_test \
    "REST API endpoint" \
    "${BASE_URL}/apis/default/api/patient" \
    "Existing API front controller (should not change)")

api_rps=$(cut -d'|' -f1 <<< "${api_result}")

# Performance Analysis
{
    echo "========================================"
    echo "PERFORMANCE ANALYSIS"
    echo "========================================"
    echo
} | tee -a "${REPORT_FILE}"

# Calculate overhead
if [[ -n "${baseline_time}" && -n "${fc_time}" && "${baseline_time}" != "0" ]]; then
    # Validate numeric values
    if ! [[ "${baseline_time}" =~ ^[0-9]+\.?[0-9]*$ ]] || ! [[ "${fc_time}" =~ ^[0-9]+\.?[0-9]*$ ]]; then
        {
            printf "WARNING: Invalid numeric values, skipping overhead calculation\n"
        } | tee -a "${REPORT_FILE}"
    else
        overhead=$(awk "BEGIN {printf \"%.2f\", (${fc_time} - ${baseline_time}) / ${baseline_time} * 100}")

        {
            echo "Front Controller Overhead:"
            echo "  Baseline (direct): ${baseline_time}ms"
            echo "  Front controller: ${fc_time}ms"
            echo "  Overhead: ${overhead}%"
            echo
        } | tee -a "${REPORT_FILE}"

        # Performance verdict using bc for portable comparison
        evaluate_overhead "${overhead}"
    fi
fi

echo | tee -a "${REPORT_FILE}"

# Throughput comparison
{
    echo "Throughput Comparison:"
    echo "  Baseline: ${baseline_rps} req/sec"
    echo "  Front controller: ${fc_rps} req/sec"
    echo "  Login page: ${login_rps} req/sec"
    if [[ -n "${static_rps}" ]]; then
        echo "  Static assets: ${static_rps} req/sec"
    fi
    echo "  REST API: ${api_rps} req/sec"
    echo
} | tee -a "${REPORT_FILE}"

# Latency Analysis
{
    echo "Latency Analysis (95th percentile):"
    echo "  Baseline: ${baseline_p95}ms"
    echo "  Front controller: ${fc_p95}ms"
    echo
} | tee -a "${REPORT_FILE}"

# Resource Usage (if available)
if command -v pgrep &> /dev/null; then
    # Use pgrep instead of ps|grep to avoid SC2009
    php_procs=$(pgrep -c php 2>/dev/null || echo "0")
    {
        echo "Current Resource Usage:"
        echo "  Active PHP processes: ${php_procs}"
    } | tee -a "${REPORT_FILE}"

    # Portable CPU usage detection
    if command -v top &> /dev/null; then
        if [[ "$(uname)" == "Darwin" ]]; then
            # macOS
            cpu_usage=$(top -l 1 | grep "CPU usage" | awk '{print $3}' 2>/dev/null || echo "N/A")
        else
            # Linux
            cpu_usage=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' 2>/dev/null || echo "N/A")
        fi
        echo "  CPU usage: ${cpu_usage}" | tee -a "${REPORT_FILE}"
    fi
    echo | tee -a "${REPORT_FILE}"
fi

# Recommendations
{
    echo "========================================"
    echo "RECOMMENDATIONS"
    echo "========================================"
    echo
} | tee -a "${REPORT_FILE}"

if [[ -n "${overhead}" ]] && [[ "${overhead}" =~ ^[0-9]+\.?[0-9]*$ ]]; then
    bc_result=$(bc -l <<< "${overhead} < 10")
    if (( bc_result )); then
        {
            echo "✓ Performance is acceptable for production use"
            echo "✓ Front controller adds minimal overhead"
            echo "✓ Security benefits outweigh performance impact"
        } | tee -a "${REPORT_FILE}"
    else
        {
            echo "⚠ Consider performance optimizations:"
            echo "  - Enable OPcache for PHP"
            echo "  - Use FastCGI process manager (PHP-FPM)"
            echo "  - Enable static asset caching"
            echo "  - Consider CDN for static assets"
        } | tee -a "${REPORT_FILE}"
    fi
else
    {
        echo "ℹ Unable to calculate overhead - ensure valid baseline measurements"
    } | tee -a "${REPORT_FILE}"
fi

{
    echo
    echo "========================================"
    echo "Report saved to: ${REPORT_FILE}"
    echo "========================================"
} | tee -a "${REPORT_FILE}"

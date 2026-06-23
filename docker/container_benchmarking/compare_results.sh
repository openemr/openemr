#!/usr/bin/env bash
# ============================================================================
# Benchmark Results Comparison Tool
# ============================================================================
# This script compares benchmark results and generates a summary report
# Usage: ./compare_results.sh [result_file]
# If no file is specified, compares Image A vs Image B from the most recent
# result. Any additional arguments are currently ignored -- the implementation
# only processes the first file.
# ============================================================================

set -euo pipefail

RESULTS_DIR="${RESULTS_DIR:-./results}"

# Colors for output (using tput for better portability, with ANSI fallback)
if [[ -t 1 ]] && command -v tput >/dev/null 2>&1; then
    RED=$(tput setaf 1)
    GREEN=$(tput setaf 2)
    YELLOW=$(tput bold; tput setaf 3)
    BLUE=$(tput setaf 4)
    CYAN=$(tput setaf 6)
    NC=$(tput sgr0) # No Color
else
    # Fallback to ANSI codes
    RED=$'\033[0;31m'
    GREEN=$'\033[0;32m'
    YELLOW=$'\033[1;33m'
    BLUE=$'\033[0;34m'
    CYAN=$'\033[0;36m'
    NC=$'\033[0m' # No Color
fi

log_info() {
    printf "%b\n" "${BLUE}ℹ${NC} $*"
}

log_success() {
    printf "%b\n" "${GREEN}✓${NC} $*"
}

log_warning() {
    printf "%b\n" "${YELLOW}⚠${NC} $*"
}

log_error() {
    printf "%b\n" "${RED}✗${NC} $*"
}

log_section() {
    printf "\n"
    printf "============================================================================\n"
    printf "%s\n" "$*"
    printf "============================================================================\n"
}

# Extract metric from result file
extract_metric() {
    local file=$1
    local metric=$2
    grep "^${metric}=" "${file}" | cut -d'=' -f2 | sed 's/ms$//' | sed 's/s$//' || echo ""
}

# Compare two values and return percentage difference
calculate_diff() {
    local val_a=$1
    local val_b=$2
    local metric_type=$3  # "lower_better" or "higher_better"

    if [[ -z "${val_a}" ]] || [[ -z "${val_b}" ]] || [[ "${val_a}" = "0" ]] || [[ "${val_b}" = "0" ]]; then
        echo "N/A"
        return
    fi

    local diff
    if [[ "${metric_type}" = "lower_better" ]]; then
        # For metrics where lower is better (startup time, response time)
        diff=$(python3 -c "print(round(((${val_b} - ${val_a}) / ${val_a}) * 100, 2))" 2>/dev/null || echo "0")
    else
        # For metrics where higher is better (requests per second)
        diff=$(python3 -c "print(round(((${val_a} - ${val_b}) / ${val_b}) * 100, 2))" 2>/dev/null || echo "0")
    fi

    echo "${diff}"
}

# Format comparison result
format_comparison() {
    local val_a=$1
    local val_b=$2
    local diff=$3
    local metric_type=$4
    local unit=$5

    if [[ "${diff}" = "N/A" ]]; then
        printf "%b\n" "${YELLOW}N/A${NC}"
        return
    fi

    local is_positive
    is_positive=$(python3 -c "print(1 if ${diff} > 0 else 0)" 2>/dev/null || echo "0")
    local is_negative
    is_negative=$(python3 -c "print(1 if ${diff} < 0 else 0)" 2>/dev/null || echo "0")

    if [[ "${is_positive}" = "1" ]]; then
        printf "%b\n" "${GREEN}+${diff}%${NC} (${val_a}${unit} vs ${val_b}${unit})"
    elif [[ "${is_negative}" = "1" ]]; then
        printf "%b\n" "${RED}${diff}%${NC} (${val_a}${unit} vs ${val_b}${unit})"
    else
        printf "%b\n" "${CYAN}0%${NC} (${val_a}${unit} vs ${val_b}${unit})"
    fi
}

# Compare Image A vs Image B from a result file
compare_file() {
    local file=$1

    if [[ ! -f "${file}" ]]; then
        log_error "Result file not found: ${file}"
        return 1
    fi

    log_section "Comparing Benchmark Results"

    echo "Benchmark File: $(basename "${file}")"
    echo ""

    # Extract metrics - Image A vs Image B from the same file
    local startup_a startup_b
    startup_a=$(extract_metric "${file}" "Image_A_startup_time")
    startup_b=$(extract_metric "${file}" "Image_B_startup_time")

    local rps_a rps_b
    rps_a=$(extract_metric "${file}" "Image_A_requests_per_second")
    rps_b=$(extract_metric "${file}" "Image_B_requests_per_second")

    local time_a time_b
    time_a=$(extract_metric "${file}" "Image_A_time_per_request_ms")
    time_b=$(extract_metric "${file}" "Image_B_time_per_request_ms")

    local cpu_a cpu_b
    cpu_a=$(extract_metric "${file}" "Image_A_avg_cpu_percent")
    cpu_b=$(extract_metric "${file}" "Image_B_avg_cpu_percent")

    local mem_a mem_b
    mem_a=$(extract_metric "${file}" "Image_A_avg_memory_mb")
    mem_b=$(extract_metric "${file}" "Image_B_avg_memory_mb")

    local peak_mem_a peak_mem_b
    peak_mem_a=$(extract_metric "${file}" "Image_A_peak_memory_mb")
    peak_mem_b=$(extract_metric "${file}" "Image_B_peak_memory_mb")

    # Calculate differences (Image A vs Image B)
    local startup_diff rps_diff time_diff cpu_diff mem_diff peak_mem_diff

    startup_diff=$(calculate_diff "${startup_a}" "${startup_b}" "lower_better")
    rps_diff=$(calculate_diff "${rps_a}" "${rps_b}" "higher_better")
    time_diff=$(calculate_diff "${time_a}" "${time_b}" "lower_better")
    cpu_diff=$(calculate_diff "${cpu_a}" "${cpu_b}" "lower_better")
    mem_diff=$(calculate_diff "${mem_a}" "${mem_b}" "lower_better")
    peak_mem_diff=$(calculate_diff "${peak_mem_a}" "${peak_mem_b}" "lower_better")

    # Display comparison
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "METRIC                    | IMAGE A          | IMAGE B          | DIFFERENCE"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

    printf "Startup Time              | %-15s | %-15s | " "${startup_a}s" "${startup_b}s"
    format_comparison "${startup_a}" "${startup_b}" "${startup_diff}" "lower_better" "s"

    printf "Requests/Second           | %-15s | %-15s | " "${rps_a}" "${rps_b}"
    format_comparison "${rps_a}" "${rps_b}" "${rps_diff}" "higher_better" ""

    printf "Avg Response Time         | %-15s | %-15s | " "${time_a}ms" "${time_b}ms"
    format_comparison "${time_a}" "${time_b}" "${time_diff}" "lower_better" "ms"

    printf "Avg CPU Usage             | %-15s | %-15s | " "${cpu_a}%" "${cpu_b}%"
    format_comparison "${cpu_a}" "${cpu_b}" "${cpu_diff}" "lower_better" "%"

    printf "Avg Memory Usage          | %-15s | %-15s | " "${mem_a}MB" "${mem_b}MB"
    format_comparison "${mem_a}" "${mem_b}" "${mem_diff}" "lower_better" "MB"

    printf "Peak Memory Usage         | %-15s | %-15s | " "${peak_mem_a}MB" "${peak_mem_b}MB"
    format_comparison "${peak_mem_a}" "${peak_mem_b}" "${peak_mem_diff}" "lower_better" "MB"

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""

    # Summary
    log_section "Summary"

    local better_count=0
    local worse_count=0

    # Count wins (Image A better)
    local startup_better startup_worse
    startup_better=$(python3 -c "print(1 if '${startup_diff}' != 'N/A' and float('${startup_diff}') < 0 else 0)" 2>/dev/null || echo "0")
    startup_worse=$(python3 -c "print(1 if '${startup_diff}' != 'N/A' and float('${startup_diff}') > 0 else 0)" 2>/dev/null || echo "0")
    better_count=$((better_count + startup_better))
    worse_count=$((worse_count + startup_worse))

    local rps_better rps_worse
    rps_better=$(python3 -c "print(1 if '${rps_diff}' != 'N/A' and float('${rps_diff}') > 0 else 0)" 2>/dev/null || echo "0")
    rps_worse=$(python3 -c "print(1 if '${rps_diff}' != 'N/A' and float('${rps_diff}') < 0 else 0)" 2>/dev/null || echo "0")
    better_count=$((better_count + rps_better))
    worse_count=$((worse_count + rps_worse))

    local time_better time_worse
    time_better=$(python3 -c "print(1 if '${time_diff}' != 'N/A' and float('${time_diff}') < 0 else 0)" 2>/dev/null || echo "0")
    time_worse=$(python3 -c "print(1 if '${time_diff}' != 'N/A' and float('${time_diff}') > 0 else 0)" 2>/dev/null || echo "0")
    better_count=$((better_count + time_better))
    worse_count=$((worse_count + time_worse))

    local cpu_better cpu_worse
    cpu_better=$(python3 -c "print(1 if '${cpu_diff}' != 'N/A' and float('${cpu_diff}') < 0 else 0)" 2>/dev/null || echo "0")
    cpu_worse=$(python3 -c "print(1 if '${cpu_diff}' != 'N/A' and float('${cpu_diff}') > 0 else 0)" 2>/dev/null || echo "0")
    better_count=$((better_count + cpu_better))
    worse_count=$((worse_count + cpu_worse))

    local mem_better mem_worse
    mem_better=$(python3 -c "print(1 if '${mem_diff}' != 'N/A' and float('${mem_diff}') < 0 else 0)" 2>/dev/null || echo "0")
    mem_worse=$(python3 -c "print(1 if '${mem_diff}' != 'N/A' and float('${mem_diff}') > 0 else 0)" 2>/dev/null || echo "0")
    better_count=$((better_count + mem_better))
    worse_count=$((worse_count + mem_worse))

    printf "Image A (Local Build) Performance:\n"
    printf "  %b\n" "${GREEN}Better: ${better_count} metrics${NC}"
    printf "  %b\n" "${RED}Worse: ${worse_count} metrics${NC}"
    printf "\n"

    if [[ "${better_count}" -gt "${worse_count}" ]]; then
        log_success "Image A performs better overall"
    elif [[ "${worse_count}" -gt "${better_count}" ]]; then
        log_warning "Image B performs better overall"
    else
        log_info "Images perform similarly"
    fi
}

# Main function
main() {
    local files=("$@")

    if [[ ${#files[@]} -eq 0 ]]; then
        # Find most recent result file
        log_info "No file specified, finding most recent result..."
        local recent_file
        recent_file=$(find "${RESULTS_DIR}" -name "benchmark_*.txt" -type f | sort -r | head -1)

        if [[ -z "${recent_file}" ]] || [[ ! -f "${recent_file}" ]]; then
            log_error "No benchmark results found"
            log_info "Available files:"
            find "${RESULTS_DIR}" -name "benchmark_*.txt" -type f | sort -r | head -5
            exit 1
        fi

        files=("${recent_file}")
        log_info "Comparing Image A vs Image B from: $(basename "${recent_file}")"
        echo ""
    fi

    if [[ ${#files[@]} -eq 0 ]]; then
        log_error "No file specified"
        exit 1
    fi

    # Compare Image A vs Image B from the first file
    compare_file "${files[0]}"
}

main "$@"

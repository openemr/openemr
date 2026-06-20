#!/usr/bin/env bash
# ============================================================================
# Benchmark Results Summary Tool
# ============================================================================
# Provides a summary of all benchmark results with statistics
# Usage: ./summary.sh
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
    # shellcheck disable=SC2034 # RED and YELLOW are defined for consistency but may not be used
    RED='\033[0;31m'
    GREEN='\033[0;32m'
    # shellcheck disable=SC2034  # YELLOW is reserved for future use
    YELLOW='\033[1;33m'
    BLUE='\033[0;34m'
    CYAN='\033[0;36m'
    NC='\033[0m' # No Color
fi

log_info() {
    printf "${BLUE}ℹ${NC} %s\n" "$*"
}

log_success() {
    printf "${GREEN}✓${NC} %s\n" "$*"
}

log_section() {
    echo ""
    echo "============================================================================"
    echo "$*"
    echo "============================================================================"
}

# Extract metric from result file
extract_metric() {
    local file=$1
    local metric=$2
    if [[ ! -f "${file}" ]] || [[ -z "${metric}" ]]; then
        echo ""
        return 0
    fi
    grep "^${metric}=" "${file}" 2>/dev/null | cut -d'=' -f2 | sed 's/ms$//' | sed 's/s$//' || echo ""
}

# Calculate statistics
calculate_stats() {
    local values=("$@")
    local count=${#values[@]}

    if (( count == 0 )); then
        echo "0,0,0,0"
        return
    fi

    # Filter out empty values and convert to numbers
    local valid_values=()
    for val in "${values[@]}"; do
        if [[ -n "${val}" && "${val}" != "0" && "${val}" != "N/A" ]]; then
            valid_values+=("${val}")
        fi
    done

    if (( ${#valid_values[@]} == 0 )); then
        echo "0,0,0,0"
        return
    fi

    # Calculate min, max, avg using Python
    local result
    result=$(python3 -c "
import sys
try:
    values = [float(v) for v in sys.argv[1:] if v and v != '0' and v != 'N/A' and float(v) > 0]
    if not values:
        print('0,0,0,0')
    else:
        print(f'{min(values)},{max(values)},{sum(values)/len(values):.2f},{len(values)}')
except:
    print('0,0,0,0')
" "${valid_values[@]}" 2>/dev/null || echo "0,0,0,0")

    echo "${result}"
}

# Main function
main() {
    log_section "Benchmark Results Summary"

    # Find all result files
    local files=()
    while IFS= read -r file; do
        files+=("${file}")
    done < <(find "${RESULTS_DIR}" -name "benchmark_*.txt" -type f | sort) || true

    if (( ${#files[@]} == 0 )); then
        echo "No benchmark results found in ${RESULTS_DIR}"
        exit 1
    fi

    echo "Found ${#files[@]} benchmark result(s)"
    echo ""

    # Collect metrics
    local startup_a_values=() startup_b_values=()
    local rps_a_values=() rps_b_values=()
    local time_a_values=() time_b_values=()
    local cpu_a_values=() cpu_b_values=()
    local mem_a_values=() mem_b_values=()

    for file in "${files[@]}"; do
        startup_a_values+=("$(extract_metric "${file}" "Image_A_startup_time")")
        startup_b_values+=("$(extract_metric "${file}" "Image_B_startup_time")")
        rps_a_values+=("$(extract_metric "${file}" "Image_A_requests_per_second")")
        rps_b_values+=("$(extract_metric "${file}" "Image_B_requests_per_second")")
        time_a_values+=("$(extract_metric "${file}" "Image_A_time_per_request_ms")")
        time_b_values+=("$(extract_metric "${file}" "Image_B_time_per_request_ms")")
        cpu_a_values+=("$(extract_metric "${file}" "Image_A_avg_cpu_percent")")
        cpu_b_values+=("$(extract_metric "${file}" "Image_B_avg_cpu_percent")")
        mem_a_values+=("$(extract_metric "${file}" "Image_A_avg_memory_mb")")
        mem_b_values+=("$(extract_metric "${file}" "Image_B_avg_memory_mb")")
    done

    # Calculate statistics
    local startup_a_stats startup_b_stats
    startup_a_stats=$(calculate_stats "${startup_a_values[@]}")
    startup_b_stats=$(calculate_stats "${startup_b_values[@]}")

    local rps_a_stats rps_b_stats
    rps_a_stats=$(calculate_stats "${rps_a_values[@]}")
    rps_b_stats=$(calculate_stats "${rps_b_values[@]}")

    local time_a_stats time_b_stats
    time_a_stats=$(calculate_stats "${time_a_values[@]}")
    time_b_stats=$(calculate_stats "${time_b_values[@]}")

    local cpu_a_stats cpu_b_stats
    cpu_a_stats=$(calculate_stats "${cpu_a_values[@]}")
    cpu_b_stats=$(calculate_stats "${cpu_b_values[@]}")

    local mem_a_stats mem_b_stats
    mem_a_stats=$(calculate_stats "${mem_a_values[@]}")
    mem_b_stats=$(calculate_stats "${mem_b_values[@]}")

    # Display summary
    log_section "Statistics Summary"

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "METRIC                    | IMAGE A (Local)              | IMAGE B (Docker Hub)"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

    # Startup Time
    local a_min a_max a_avg a_count
    IFS=',' read -r a_min a_max a_avg a_count <<< "${startup_a_stats}"
    local b_min b_max b_avg b_count
    IFS=',' read -r b_min b_max b_avg b_count <<< "${startup_b_stats}"
    printf "Startup Time (s)          | Min: %-6.2f Avg: %-6.2f Max: %-6.2f | Min: %-6.2f Avg: %-6.2f Max: %-6.2f\n" \
        "${a_min}" "${a_avg}" "${a_max}" "${b_min}" "${b_avg}" "${b_max}"

    # Requests per Second
    IFS=',' read -r a_min a_max a_avg a_count <<< "${rps_a_stats}"
    IFS=',' read -r b_min b_max b_avg b_count <<< "${rps_b_stats}"
    printf "Requests/Second           | Min: %-6.2f Avg: %-6.2f Max: %-6.2f | Min: %-6.2f Avg: %-6.2f Max: %-6.2f\n" \
        "${a_min}" "${a_avg}" "${a_max}" "${b_min}" "${b_avg}" "${b_max}"

    # Response Time
    IFS=',' read -r a_min a_max a_avg a_count <<< "${time_a_stats}"
    IFS=',' read -r b_min b_max b_avg b_count <<< "${time_b_stats}"
    printf "Avg Response Time (ms)    | Min: %-6.2f Avg: %-6.2f Max: %-6.2f | Min: %-6.2f Avg: %-6.2f Max: %-6.2f\n" \
        "${a_min}" "${a_avg}" "${a_max}" "${b_min}" "${b_avg}" "${b_max}"

    # CPU Usage
    IFS=',' read -r a_min a_max a_avg a_count <<< "${cpu_a_stats}"
    IFS=',' read -r b_min b_max b_avg b_count <<< "${cpu_b_stats}"
    printf "Avg CPU Usage (%%)         | Min: %-6.2f Avg: %-6.2f Max: %-6.2f | Min: %-6.2f Avg: %-6.2f Max: %-6.2f\n" \
        "${a_min}" "${a_avg}" "${a_max}" "${b_min}" "${b_avg}" "${b_max}"

    # Memory Usage
    # shellcheck disable=SC2034  # a_count and b_count are read but not used (reserved for future use)
    IFS=',' read -r a_min a_max a_avg a_count <<< "${mem_a_stats}"
    # shellcheck disable=SC2034  # b_count is read but not used (reserved for future use)
    IFS=',' read -r b_min b_max b_avg b_count <<< "${mem_b_stats}"
    printf "Avg Memory Usage (MB)     | Min: %-6.2f Avg: %-6.2f Max: %-6.2f | Min: %-6.2f Avg: %-6.2f Max: %-6.2f\n" \
        "${a_min}" "${a_avg}" "${a_max}" "${b_min}" "${b_avg}" "${b_max}"

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""

    log_section "Recent Results"

    # Show last 5 results
    local recent_files=()
    while IFS= read -r file; do
        recent_files+=("${file}")
    done < <(find "${RESULTS_DIR}" -name "benchmark_*.txt" -type f | sort -r | head -5) || true

    for file in "${recent_files[@]}"; do
        local timestamp filename
        filename="${file##*/}"
        filename="${filename#benchmark_}"
        timestamp="${filename%.txt}"
        local startup_a startup_b
        startup_a=$(extract_metric "${file}" "Image_A_startup_time")
        startup_b=$(extract_metric "${file}" "Image_B_startup_time")
        local rps_a rps_b
        rps_a=$(extract_metric "${file}" "Image_A_requests_per_second")
        rps_b=$(extract_metric "${file}" "Image_B_requests_per_second")

        printf "${CYAN}%s${NC}:\n" "${timestamp}"
        echo "  Startup: A=${startup_a}s, B=${startup_b}s | RPS: A=${rps_a}, B=${rps_b}"
    done

    echo ""
    log_success "Summary complete!"
    log_info "Use './compare_results.sh' to compare specific results"
    log_info "Use './export_to_csv.sh' to export all results to CSV"
}

main "$@"

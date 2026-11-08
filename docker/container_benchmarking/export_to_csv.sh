#!/usr/bin/env bash
# ============================================================================
# Benchmark Results CSV Export Tool
# ============================================================================
# Exports benchmark results to CSV format for analysis in spreadsheet tools
# Usage: ./export_to_csv.sh [result_file] ...
# If no files specified, exports all results
# ============================================================================

set -euo pipefail

RESULTS_DIR="${RESULTS_DIR:-./results}"
OUTPUT_CSV="${OUTPUT_CSV:-${RESULTS_DIR}/benchmark_results.csv}"

# Extract metric from result file
extract_metric() {
    local file=$1
    local metric=$2
    grep "^${metric}=" "${file}" | cut -d'=' -f2 | sed 's/ms$//' | sed 's/s$//' || echo ""
}

# Extract timestamp from filename
extract_timestamp() {
    local filename="${1}"
    filename="${filename##*/}"
    filename="${filename#benchmark_}"
    echo "${filename%.txt}"
}

# Export single file to CSV line
export_file() {
    local file=$1
    local timestamp
    timestamp=$(extract_timestamp "${file}")

    # Extract all metrics
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

    local failed_a failed_b
    failed_a=$(extract_metric "${file}" "Image_A_failed_requests")
    failed_b=$(extract_metric "${file}" "Image_B_failed_requests")

    # Output CSV line
    echo "${timestamp},${startup_a},${startup_b},${rps_a},${rps_b},${time_a},${time_b},${cpu_a},${cpu_b},${mem_a},${mem_b},${peak_mem_a},${peak_mem_b},${failed_a},${failed_b}"
}

# Main function
main() {
    local files=("$@")

    if [[ ${#files[@]} -eq 0 ]]; then
        # Export all result files - use while read loop for portability
        while IFS= read -r file; do
            files+=("${file}")
        done < <(find "${RESULTS_DIR}" -name "benchmark_*.txt" -type f 2>/dev/null | sort || true)
    fi

    if [[ ${#files[@]} -eq 0 ]]; then
        echo "No result files found in ${RESULTS_DIR}"
        exit 1
    fi

    if (( ${#files[@]} == 0 )); then
        # Export all result files
        shopt -s globstar
        shopt -s nullglob
        files=( "${RESULTS_DIR}"/**/benchmark_*.txt )
    fi

    if (( ${#files[@]} == 0 )); then
        echo "No result files found in ${RESULTS_DIR}"
        exit 1
    fi

    # Write CSV header
    echo "timestamp,image_a_startup_s,image_b_startup_s,image_a_rps,image_b_rps,image_a_time_ms,image_b_time_ms,image_a_cpu_pct,image_b_cpu_pct,image_a_mem_mb,image_b_mem_mb,image_a_peak_mem_mb,image_b_peak_mem_mb,image_a_failed,image_b_failed" > "${OUTPUT_CSV}"

    # Export each file
    for file in "${files[@]}"; do
        if [[ -f "${file}" ]]; then
            export_file "${file}" >> "${OUTPUT_CSV}"
        fi
    done

    echo "Exported ${#files[@]} benchmark results to: ${OUTPUT_CSV}"
}

main "$@"

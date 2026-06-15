#!/usr/bin/env bash
# ============================================================================
# Container Benchmarking Script
# ============================================================================
# This script runs comparative benchmarks between two OpenEMR container images:
#   - Image A: Local build from repository (default: ./docker/openemr/8.1.0/)
#   - Image B: Public Docker Hub image (default: openemr/openemr:8.1.0)
#
# Benchmarks measured:
#   1. Startup time (time to healthy status)
#   2. Performance under load (response times, throughput)
#   3. Resource utilization (CPU, memory) during load test
#
# Usage: ./benchmark.sh [options]
# ============================================================================

set -euo pipefail

# ============================================================================
# CONFIGURATION
# ============================================================================
# Modify these variables to test different images

# Image A: Local build context (relative to this script's directory)
IMAGE_A_CONTEXT="${IMAGE_A_CONTEXT:-../../docker/openemr/8.1.0}"

# Image B: Docker Hub image name and tag
IMAGE_B_IMAGE="${IMAGE_B_IMAGE:-openemr/openemr:8.1.0}"

# Port mappings (must be different for both containers)
IMAGE_A_PORT="${IMAGE_A_PORT:-8080}"
IMAGE_B_PORT="${IMAGE_B_PORT:-8081}"

# Load test configuration
LOAD_TEST_CONCURRENT="${LOAD_TEST_CONCURRENT:-10}"      # Number of concurrent requests
LOAD_TEST_REQUESTS="${LOAD_TEST_REQUESTS:-1000}"        # Total number of requests
LOAD_TEST_DURATION="${LOAD_TEST_DURATION:-60}"          # Duration in seconds for resource monitoring

# Benchmark project name (for Docker Compose)
PROJECT_NAME="${PROJECT_NAME:-container-benchmark}"

# Results directory
RESULTS_DIR="${RESULTS_DIR:-./results}"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
RESULT_FILE="${RESULTS_DIR}/benchmark_${TIMESTAMP}.txt"

# Colors for output (using tput for better portability, with ANSI fallback)
if [[ -t 1 ]] && command -v tput >/dev/null 2>&1; then
    RED=$(tput setaf 1)
    GREEN=$(tput setaf 2)
    YELLOW=$(tput bold; tput setaf 3)
    BLUE=$(tput setaf 4)
    NC=$(tput sgr0) # No Color
else
    # Fallback to ANSI codes
    RED='\033[0;31m'
    GREEN='\033[0;32m'
    YELLOW='\033[1;33m'
    BLUE='\033[0;34m'
    NC='\033[0m' # No Color
fi

# ============================================================================
# HELPER FUNCTIONS
# ============================================================================

log_info() {
    printf "${BLUE}ℹ${NC} %s\n" "$*"
}

log_success() {
    printf "${GREEN}✓${NC} %s\n" "$*"
}

log_warning() {
    printf "${YELLOW}⚠${NC} %s\n" "$*"
}

log_error() {
    printf "${RED}✗${NC} %s\n" "$*"
}

log_section() {
    echo ""
    echo "============================================================================"
    echo "$*"
    echo "============================================================================"
}

# Cleanup function
cleanup() {
    log_info "Cleaning up..."
    docker compose -p "${PROJECT_NAME}" down --remove-orphans --volumes >/dev/null 2>&1 || true
}

# Trap to ensure cleanup on exit
trap cleanup EXIT

# ============================================================================
# STARTUP TIME BENCHMARK
# ============================================================================

benchmark_startup_time() {
    local container_name=$1
    local port=$2
    local label=$3
    local provided_start_time="${4:-}"  # Optional: start time passed from caller

    log_info "Measuring startup time for ${label}..."

    # Get the container's actual start time from Docker
    # Measure from when docker compose registered the container as started until it becomes healthy
    local start_time
    if [[ -n "${provided_start_time}" ]]; then
        # Use provided start time (from docker inspect right after compose up)
        start_time="${provided_start_time}"
    else
        # Fallback: get start time from docker inspect
        start_time=$(docker inspect "${container_name}" --format '{{.State.StartedAt}}' 2>/dev/null || echo "")
    fi

    if [[ -z "${start_time}" ]]; then
        log_error "Could not determine start time for ${container_name}"
        echo "${label}_startup_time=ERROR" >> "${RESULT_FILE}"
        return 1
    fi

    # Convert ISO 8601 timestamp to Unix timestamp
    # Docker timestamps are in UTC (indicated by Z suffix)
    # Strip nanoseconds before parsing (Python's fromisoformat doesn't handle them)
    local unix_start_time
    local clean_start_time="${start_time%.*}"
    unix_start_time=$(python3 -c "
import sys
from datetime import datetime, timezone
try:
    # Docker timestamps end with Z indicating UTC
    if '${clean_start_time}'.endswith('Z'):
        # Parse as UTC
        dt_str = '${clean_start_time}'.replace('Z', '+00:00')
        dt = datetime.fromisoformat(dt_str)
        print(int(dt.timestamp()))
    elif '+' in '${clean_start_time}' or ('${clean_start_time}'.count('-') > 2 and '${clean_start_time}'.rfind('-') > 10):
        # Has timezone offset
        dt = datetime.fromisoformat('${clean_start_time}')
        print(int(dt.timestamp()))
    else:
        # No timezone, assume UTC
        dt = datetime.strptime('${clean_start_time}', '%Y-%m-%dT%H:%M:%S')
        dt = dt.replace(tzinfo=timezone.utc)
        print(int(dt.timestamp()))
except Exception as e:
    try:
        # Final fallback: parse as UTC naive and convert
        dt = datetime.strptime('${clean_start_time}', '%Y-%m-%dT%H:%M:%S')
        dt = dt.replace(tzinfo=timezone.utc)
        print(int(dt.timestamp()))
    except:
        sys.exit(1)
" 2>/dev/null)

    # Fallback to date command if Python fails
    if [[ -z "${unix_start_time}" ]] || [[ "${unix_start_time}" = "0" ]]; then
        if [[ "$(uname || true)" == "Darwin" ]]; then
            # macOS: Parse ISO 8601 format (remove nanoseconds and Z)
            local clean_date="${start_time%.*}"
            clean_date="${clean_date//Z/}"
            unix_start_time=$(date -j -f "%Y-%m-%dT%H:%M:%S" "${clean_date}" +%s 2>/dev/null || echo "")
        else
            # Linux: date command handles ISO 8601 natively
            unix_start_time=$(date -d "${start_time}" +%s 2>/dev/null || echo "")
        fi
    fi

    # Final fallback: use current time (shouldn't happen, but prevents division by zero)
    if [[ -z "${unix_start_time}" ]] || [[ "${unix_start_time}" = "0" ]]; then
        log_warning "Could not parse container start time for ${container_name}, using current time as fallback"
        unix_start_time=$(date +%s)
    fi

    start_time=${unix_start_time}

    # Wait for container to be healthy with high-frequency polling for precision
    # Poll every 0.5 seconds to capture the exact moment each container becomes healthy
    local max_wait=600  # 10 minutes max
    local waited=0
    local poll_interval=0.5  # Poll every 0.5 seconds for better precision
    local last_status=""

    while [[ "${waited}" -lt "${max_wait}" ]]; do
        local health_status
        health_status=$(docker inspect "${container_name}" --format '{{.State.Health.Status}}' 2>/dev/null || echo "starting")

        # Check if status changed to healthy
        if [[ "${health_status}" = "healthy" ]]; then
            # Get the exact timestamp from Docker's health check log
            # This is more accurate than polling because it shows when the health check actually passed
            local end_time
            local first_success_time
            # Get the End timestamp of the first successful health check (ExitCode 0)
            first_success_time=$(docker inspect "${container_name}" --format '{{range .State.Health.Log}}{{if eq .ExitCode 0}}{{.End}}{{break}}{{end}}{{end}}' 2>/dev/null || echo "")

            if [[ -n "${first_success_time}" ]] && [[ "${first_success_time}" != "<no value>" ]] && [[ "${first_success_time}" != "" ]]; then
                # Convert Docker's health check timestamp (ISO 8601 with nanoseconds) to Unix timestamp
                local clean_time="${first_success_time%.*}"
                end_time=$(python3 -c "
from datetime import datetime, timezone
try:
    if '${clean_time}'.endswith('Z'):
        dt_str = '${clean_time}'.replace('Z', '+00:00')
        dt = datetime.fromisoformat(dt_str)
        print(dt.timestamp())
    elif '+' in '${clean_time}' or ('${clean_time}'.count('-') > 2 and '${clean_time}'.rfind('-') > 10):
        dt = datetime.fromisoformat('${clean_time}')
        print(dt.timestamp())
    else:
        dt = datetime.strptime('${clean_time}', '%Y-%m-%dT%H:%M:%S')
        dt = dt.replace(tzinfo=timezone.utc)
        print(dt.timestamp())
except Exception as e:
    import time
    print(time.time())
" 2>/dev/null || python3 -c "import time; print(time.time())" 2>/dev/null || date +%s)
            else
                # Fallback: use current time with high precision
                if command -v python3 >/dev/null 2>&1; then
                    end_time=$(python3 -c "import time; print(time.time())" 2>/dev/null || date +%s)
                else
                    end_time=$(date +%s)
                fi
            fi

            # Calculate startup time with high precision (3 decimal places)
            local startup_time
            if command -v python3 >/dev/null 2>&1; then
                startup_time=$(python3 -c "print(round(${end_time} - ${start_time}, 3))" 2>/dev/null || echo "$((end_time - start_time))")
            else
                startup_time=$((end_time - start_time))
            fi

            log_success "${label} started in ${startup_time} seconds"
            echo "${label}_startup_time=${startup_time}s" >> "${RESULT_FILE}"
            return 0
        fi

        # Log status changes for debugging
        if [[ "${health_status}" != "${last_status}" ]] && [[ -n "${last_status}" ]]; then
            log_info "${label} health status changed: ${last_status} -> ${health_status}"
        fi
        last_status="${health_status}"

        sleep "${poll_interval}"
        waited=$(python3 -c "print(${waited} + ${poll_interval})" 2>/dev/null || echo "$((waited + 1))")
    done

    log_error "${label} did not become healthy within ${max_wait}s"
    echo "${label}_startup_time=FAILED" >> "${RESULT_FILE}"
    return 1
}

# ============================================================================
# PERFORMANCE BENCHMARK (Load Testing)
# ============================================================================

benchmark_performance() {
    local container_name=$1
    local port=$2
    local label=$3

    log_info "Running performance benchmark for ${label}..."

    # Wait for OpenEMR to be ready (check login page)
    log_info "Waiting for ${label} to be ready..."
    local max_wait=120
    local waited=0

    while [[ "${waited}" -lt "${max_wait}" ]]; do
        local http_code
        http_code=$(curl -sL -o /dev/null -w "%{http_code}" --max-time 5 "http://localhost:${port}/interface/login/login.php" 2>/dev/null || echo "000")

        if [[ "${http_code}" = "200" ]]; then
            break
        fi

        sleep 2
        waited=$((waited + 2))
    done

    if [[ "${waited}" -ge "${max_wait}" ]]; then
        log_error "${label} is not responding"
        echo "${label}_performance=FAILED" >> "${RESULT_FILE}"
        return 1
    fi

    # Run Apache Bench load test from load-generator container
    log_info "Running load test: ${LOAD_TEST_CONCURRENT} concurrent, ${LOAD_TEST_REQUESTS} total requests..."

    # Determine container hostname based on label
    local target_host
    if [[ "${label}" = "Image_A" ]]; then
        target_host="openemr-image-a"
    else
        target_host="openemr-image-b"
    fi

    local ab_output
    ab_output=$(docker exec benchmark-load-generator ab -n "${LOAD_TEST_REQUESTS}" -c "${LOAD_TEST_CONCURRENT}" \
        "http://${target_host}/interface/login/login.php" 2>/dev/null || echo "")

    # Extract key metrics
    local requests_per_sec
    requests_per_sec=$(echo "${ab_output}" | grep "Requests per second" | awk '{print $4}' || echo "0")
    local time_per_request
    time_per_request=$(echo "${ab_output}" | grep "Time per request.*mean" | head -1 | awk '{print $4}' || echo "0")
    local failed_requests
    failed_requests=$(echo "${ab_output}" | grep "Failed requests" | awk '{print $3}' | cut -d'(' -f1 || echo "0")

    log_success "${label} performance: ${requests_per_sec} req/s, ${time_per_request}ms avg"

    # Save metrics with grouped redirects
    {
        echo "${label}_requests_per_second=${requests_per_sec}"
        echo "${label}_time_per_request_ms=${time_per_request}"
        echo "${label}_failed_requests=${failed_requests}"
        echo ""
        echo "=== ${label} Full Apache Bench Output ==="
        echo "${ab_output}"
        echo ""
    } >> "${RESULT_FILE}"
}

# ============================================================================
# RESOURCE UTILIZATION BENCHMARK
# ============================================================================

benchmark_resources() {
    local container_name=$1
    local label=$2

    log_info "Monitoring resource utilization for ${label}..."

    # Start background monitoring
    local stats_file="${RESULTS_DIR}/${label}_stats_${TIMESTAMP}.txt"
    echo "timestamp,cpu_percent,memory_usage_mb,memory_limit_mb" > "${stats_file}"

    # Monitor for specified duration
    local end_time
    end_time=$(($(date +%s) + LOAD_TEST_DURATION))

    while true; do
        current_time=$(date +%s) || break
        [[ ${current_time} -lt ${end_time} ]] || break
        local stats
        stats=$(docker stats "${container_name}" --no-stream --format "{{.CPUPerc}},{{.MemUsage}}" 2>/dev/null) || stats="0,0 B / 0 B"

        local cpu_percent
        cpu_percent=$(echo "${stats}" | cut -d',' -f1 | tr -d '%' || echo "0")
        local mem_usage
        mem_usage=$(echo "${stats}" | cut -d',' -f2 | awk '{print $1}' | tr -d 'MiB' || echo "0")
        local mem_limit
        mem_limit=$(echo "${stats}" | cut -d',' -f2 | awk '{print $3}' | tr -d 'MiB') || mem_limit="0"

        timestamp=$(date +%s) || timestamp="0"
        echo "${timestamp},${cpu_percent},${mem_usage},${mem_limit}" >> "${stats_file}"
        sleep 5
    done

    # Calculate averages
    local avg_cpu
    avg_cpu=$(awk -F',' 'NR>1 {sum+=$2; count++} END {if(count>0) print sum/count; else print 0}' "${stats_file}")
    local avg_memory
    avg_memory=$(awk -F',' 'NR>1 {sum+=$3; count++} END {if(count>0) print sum/count; else print 0}' "${stats_file}")
    local max_memory
    max_memory=$(awk -F',' 'NR>1 {if($3>max) max=$3} END {print max+0}' "${stats_file}")

    log_success "${label} resources: ${avg_cpu}% CPU avg, ${avg_memory}MB memory avg, ${max_memory}MB memory peak"

    {
        echo "${label}_avg_cpu_percent=${avg_cpu}"
        echo "${label}_avg_memory_mb=${avg_memory}"
        echo "${label}_peak_memory_mb=${max_memory}"
    } >> "${RESULT_FILE}"
}

# ============================================================================
# MAIN EXECUTION
# ============================================================================

main() {
    log_section "Container Benchmarking Suite"

    # Pre-flight checks
    log_info "Running pre-flight checks..."

    # Check Docker is available
    if ! command -v docker >/dev/null 2>&1; then
        log_error "Docker is not installed or not in PATH"
        exit 1
    fi

    # Check docker compose is available
    if ! docker compose version >/dev/null 2>&1; then
        log_error "Docker Compose is not available"
        exit 1
    fi

    # Check disk space (warn if low)
    # Use macOS-compatible df command (df -h on macOS, df -BG on Linux)
    local available_space
    if [[ "$(uname || true)" == "Darwin" ]]; then
        # macOS: df -h outputs in human-readable format
        available_space=$(df -h . | awk 'NR==2 {print $4}' | sed 's/Gi//;s/G//' || echo "0")
    else
        # Linux: df -BG outputs in GB
        available_space=$(df -BG . | awk 'NR==2 {print $4}' | sed 's/G//' || echo "0")
    fi
    # Convert to numeric value for comparison (handle "12Gi" -> "12")
    available_space=$(echo "${available_space}" | grep -oE '[0-9]+' | head -1 || echo "0")
    if [[ "${available_space}" -lt 5 ]] 2>/dev/null; then
        log_warning "Low disk space detected: ${available_space}GB available"
        log_warning "MySQL may fail to start. Consider freeing disk space."
    else
        log_info "Disk space check: ${available_space}GB available"
    fi

    # Check Docker disk usage
    local docker_space
    docker_space=$(docker system df --format "{{.Reclaimable}}" 2>/dev/null | head -1 || echo "0")
    if [[ -n "${docker_space}" ]] && [[ "${docker_space}" != "0B" ]]; then
        log_info "Docker reclaimable space: ${docker_space}"
        log_info "Run 'docker system prune' to free space if needed"
    fi

    # Create results directory
    mkdir -p "${RESULTS_DIR}"

    # Initialize result file
    {
        date_output=$(date) || date_output="Unknown"
        echo "Container Benchmark Results - ${date_output}"
        echo "Image A Context: ${IMAGE_A_CONTEXT}"
        echo "Image B Image: ${IMAGE_B_IMAGE}"
        echo "Load Test: ${LOAD_TEST_CONCURRENT} concurrent, ${LOAD_TEST_REQUESTS} requests"
        echo ""
    } > "${RESULT_FILE}"

    log_info "Configuration:"
    echo "  Image A: ${IMAGE_A_CONTEXT}"
    echo "  Image B: ${IMAGE_B_IMAGE}"
    echo "  Ports: ${IMAGE_A_PORT} (A), ${IMAGE_B_PORT} (B)"
    echo "  Results: ${RESULT_FILE}"
    echo ""

    # Export variables for docker-compose
    export IMAGE_A_CONTEXT IMAGE_B_IMAGE IMAGE_A_PORT IMAGE_B_PORT

    # Build and start containers
    log_section "Building and Starting Containers"
    log_info "Building Image A from ${IMAGE_A_CONTEXT}..."
    if ! docker compose -p "${PROJECT_NAME}" build openemr-image-a; then
        log_error "Failed to build Image A"
        exit 1
    fi

    # Helper function to wait for a MySQL service to be healthy
    wait_for_mysql_service() {
        local service_name=$1
        local display_name=$2
        log_info "Waiting for ${display_name} to be ready..."
        local mysql_wait=0
        local mysql_max_wait=120

        while [[ ${mysql_wait} -lt ${mysql_max_wait} ]]; do
            local mysql_status
            mysql_status=$(docker compose -p "${PROJECT_NAME}" ps "${service_name}" --format json 2>/dev/null | grep -o '"State":"[^"]*"' | head -1 | cut -d'"' -f4 || echo "")

            if [[ "${mysql_status}" = "running" ]]; then
                # Check MySQL health status
                local container_name
                container_name=$(docker compose -p "${PROJECT_NAME}" ps "${service_name}" -q | head -1)
                if [[ -n "${container_name}" ]]; then
                    local mysql_health
                    mysql_health=$(docker inspect "${container_name}" --format '{{.State.Health.Status}}' 2>/dev/null || echo "")
                    if [[ "${mysql_health}" = "healthy" ]]; then
                        log_success "${display_name} is ready and healthy"
                        return 0
                    elif [[ "${mysql_health}" = "unhealthy" ]]; then
                        # Only fail if we've been waiting for a while (past start_period of 60s)
                        # During initialization, MySQL may temporarily report unhealthy
                        if [[ ${mysql_wait} -gt 70 ]]; then
                            log_error "${display_name} is running but unhealthy after ${mysql_wait}s"
                            log_info "${display_name} logs:"
                            docker compose -p "${PROJECT_NAME}" logs "${service_name}" 2>&1 | tail -20 || true
                            return 1
                        fi
                        # Otherwise, continue waiting (MySQL is still initializing)
                    fi
                    # If health status is "starting" or empty, continue waiting
                    # (healthcheck uses mariadb client and should work reliably)
                fi
                # Continue waiting
            elif [[ "${mysql_status}" = "exited" ]] || [[ "${mysql_status}" = "dead" ]]; then
                log_error "${display_name} container exited unexpectedly"
                log_info "${display_name} status:"
                docker compose -p "${PROJECT_NAME}" ps "${service_name}" || true
                log_info "${display_name} logs (last 50 lines):"
                docker compose -p "${PROJECT_NAME}" logs "${service_name}" 2>&1 | tail -50 || true

                # Check for common error patterns
                local mysql_logs
                mysql_logs=$(docker compose -p "${PROJECT_NAME}" logs "${service_name}" 2>&1 || echo "")
                if echo "${mysql_logs}" | grep -qi "No space left on device"; then
                    log_error "${display_name} failed due to insufficient disk space"
                    log_info "Disk space:"
                    df -h . | head -2 || true
                    log_info "Docker disk usage:"
                    docker system df || true
                    log_info "Try running: docker system prune -a --volumes"
                fi

                return 1
            fi

            sleep 2
            mysql_wait=$((mysql_wait + 2))
            if [[ $((mysql_wait % 20)) -eq 0 ]]; then
                log_info "Still waiting for ${display_name}... (${mysql_wait}s)"
            fi
        done

        log_error "${display_name} did not become ready within ${mysql_max_wait}s"
        log_info "${display_name} status:"
        docker compose -p "${PROJECT_NAME}" ps "${service_name}" || true
        log_info "${display_name} logs (last 50 lines):"
        docker compose -p "${PROJECT_NAME}" logs "${service_name}" 2>&1 | tail -50 || true
        return 1
    }

    log_info "Starting MySQL services..."
    if ! docker compose -p "${PROJECT_NAME}" up -d mysql-a mysql-b; then
        log_error "Failed to start MySQL containers"
        log_info "MySQL logs:"
        docker compose -p "${PROJECT_NAME}" logs mysql-a mysql-b 2>&1 | tail -20 || true
        exit 1
    fi

    # Wait for both MySQL instances to be healthy
    # shellcheck disable=SC2310  # set -e behavior in conditionals is intentional
    if ! wait_for_mysql_service "mysql-a" "MySQL-A"; then
        exit 1
    fi
    # shellcheck disable=SC2310  # set -e behavior in conditionals is intentional
    if ! wait_for_mysql_service "mysql-b" "MySQL-B"; then
        exit 1
    fi

    # Start load generator first (it doesn't affect timing)
    log_info "Starting load generator..."
    docker compose -p "${PROJECT_NAME}" up -d load-generator >/dev/null 2>&1 || true

    # =========================================================================
    # STARTUP TIME BENCHMARK - Using Docker's internal timestamps for accuracy
    # =========================================================================
    # We measure startup time using Docker's authoritative timestamps:
    #   - State.StartedAt: When the container process actually started
    #   - State.Health.Log[0].End: When the first successful health check completed
    # This gives us the true time from container start to healthy status.
    # =========================================================================
    log_section "Benchmark 1: Startup Time"

    local max_wait=600
    local waited=0

    # -------------------------------------------------------------------------
    # Measure Image A startup time
    # -------------------------------------------------------------------------
    log_info "Starting Image_A and measuring startup time..."

    # Start the container
    docker compose -p "${PROJECT_NAME}" up -d openemr-image-a >/dev/null 2>&1

    # Wait for container to become healthy
    waited=0
    while [[ ${waited} -lt ${max_wait} ]]; do
        local health_a
        health_a=$(docker inspect benchmark-image-a --format '{{.State.Health.Status}}' 2>/dev/null || echo "starting")
        if [[ "${health_a}" == "healthy" ]]; then
            break
        fi
        sleep 1
        waited=$((waited + 1))
    done

    if [[ ${waited} -ge ${max_wait} ]]; then
        log_error "Image_A did not become healthy within ${max_wait}s"
        echo "Image_A_startup_time=FAILED" >> "${RESULT_FILE}"
    else
        # Calculate startup time from Docker's internal timestamps
        local startup_a
        startup_a=$(python3 << 'PYTHON_SCRIPT'
import subprocess
from datetime import datetime, timezone

def parse_docker_timestamp(ts):
    """Parse Docker timestamp in various formats:
    - StartedAt: "2025-11-26T18:58:23.923023048Z"
    - Health End: "2025-11-26 18:58:36.679263929 +0000 UTC"
    """
    ts = ts.strip()

    # Format 2: "2025-11-26 18:58:36.679263929 +0000 UTC" (health log)
    if ' UTC' in ts:
        # Remove " UTC" suffix
        ts = ts.replace(' UTC', '')
        # Parse: "2025-11-26 18:58:36.679263929 +0000"
        # Split into datetime part and timezone
        parts = ts.rsplit(' ', 1)  # Split on last space
        dt_part = parts[0]  # "2025-11-26 18:58:36.679263929"
        tz_part = parts[1] if len(parts) > 1 else "+0000"  # "+0000"

        # Truncate nanoseconds to microseconds
        if '.' in dt_part:
            base, frac = dt_part.split('.')
            frac = frac[:6].ljust(6, '0')
            dt_part = f"{base}.{frac}"

        # Parse datetime
        dt = datetime.strptime(dt_part, "%Y-%m-%d %H:%M:%S.%f")
        dt = dt.replace(tzinfo=timezone.utc)
        return dt

    # Format 1: "2025-11-26T18:58:23.923023048Z" (StartedAt)
    if ts.endswith('Z'):
        ts = ts[:-1]  # Remove Z
        # Truncate nanoseconds to microseconds
        if '.' in ts:
            base, frac = ts.split('.')
            frac = frac[:6].ljust(6, '0')
            ts = f"{base}.{frac}"
        dt = datetime.strptime(ts, "%Y-%m-%dT%H:%M:%S.%f")
        dt = dt.replace(tzinfo=timezone.utc)
        return dt

    # Fallback: try fromisoformat
    return datetime.fromisoformat(ts.replace('Z', '+00:00'))

try:
    # Get container start time
    result = subprocess.run(
        ['docker', 'inspect', 'benchmark-image-a', '--format', '{{.State.StartedAt}}'],
        capture_output=True, text=True
    )
    start_time = parse_docker_timestamp(result.stdout.strip())

    # Get first successful health check time
    result = subprocess.run(
        ['docker', 'inspect', 'benchmark-image-a', '--format',
         '{{range .State.Health.Log}}{{if eq .ExitCode 0}}{{.End}}|{{end}}{{end}}'],
        capture_output=True, text=True
    )
    health_times = result.stdout.strip()
    first_health = health_times.split('|')[0] if '|' in health_times else health_times
    end_time = parse_docker_timestamp(first_health)

    diff = (end_time - start_time).total_seconds()
    print(f"{diff:.1f}")
except Exception as e:
    print(f"ERROR:{e}")
PYTHON_SCRIPT
)

        if [[ "${startup_a}" == ERROR* ]]; then
            log_error "Failed to calculate Image_A startup time: ${startup_a}"
            echo "Image_A_startup_time=ERROR" >> "${RESULT_FILE}"
        else
            log_success "Image_A startup time: ${startup_a} seconds"
            echo "Image_A_startup_time=${startup_a}s" >> "${RESULT_FILE}"
        fi
    fi

    # -------------------------------------------------------------------------
    # Measure Image B startup time
    # -------------------------------------------------------------------------
    log_info "Starting Image_B and measuring startup time..."

    # Start the container
    docker compose -p "${PROJECT_NAME}" up -d openemr-image-b >/dev/null 2>&1

    # Wait for container to become healthy
    waited=0
    while [[ ${waited} -lt ${max_wait} ]]; do
        local health_b
        health_b=$(docker inspect benchmark-image-b --format '{{.State.Health.Status}}' 2>/dev/null || echo "starting")
        if [[ "${health_b}" == "healthy" ]]; then
            break
        fi
        sleep 1
        waited=$((waited + 1))
    done

    if [[ ${waited} -ge ${max_wait} ]]; then
        log_error "Image_B did not become healthy within ${max_wait}s"
        echo "Image_B_startup_time=FAILED" >> "${RESULT_FILE}"
    else
        # Calculate startup time from Docker's internal timestamps
        local startup_b
        startup_b=$(python3 << 'PYTHON_SCRIPT'
import subprocess
from datetime import datetime, timezone

def parse_docker_timestamp(ts):
    """Parse Docker timestamp in various formats:
    - StartedAt: "2025-11-26T18:58:23.923023048Z"
    - Health End: "2025-11-26 18:58:36.679263929 +0000 UTC"
    """
    ts = ts.strip()

    # Format 2: "2025-11-26 18:58:36.679263929 +0000 UTC" (health log)
    if ' UTC' in ts:
        ts = ts.replace(' UTC', '')
        parts = ts.rsplit(' ', 1)
        dt_part = parts[0]

        if '.' in dt_part:
            base, frac = dt_part.split('.')
            frac = frac[:6].ljust(6, '0')
            dt_part = f"{base}.{frac}"

        dt = datetime.strptime(dt_part, "%Y-%m-%d %H:%M:%S.%f")
        dt = dt.replace(tzinfo=timezone.utc)
        return dt

    # Format 1: "2025-11-26T18:58:23.923023048Z" (StartedAt)
    if ts.endswith('Z'):
        ts = ts[:-1]
        if '.' in ts:
            base, frac = ts.split('.')
            frac = frac[:6].ljust(6, '0')
            ts = f"{base}.{frac}"
        dt = datetime.strptime(ts, "%Y-%m-%dT%H:%M:%S.%f")
        dt = dt.replace(tzinfo=timezone.utc)
        return dt

    return datetime.fromisoformat(ts.replace('Z', '+00:00'))

try:
    result = subprocess.run(
        ['docker', 'inspect', 'benchmark-image-b', '--format', '{{.State.StartedAt}}'],
        capture_output=True, text=True
    )
    start_time = parse_docker_timestamp(result.stdout.strip())

    result = subprocess.run(
        ['docker', 'inspect', 'benchmark-image-b', '--format',
         '{{range .State.Health.Log}}{{if eq .ExitCode 0}}{{.End}}|{{end}}{{end}}'],
        capture_output=True, text=True
    )
    health_times = result.stdout.strip()
    first_health = health_times.split('|')[0] if '|' in health_times else health_times
    end_time = parse_docker_timestamp(first_health)

    diff = (end_time - start_time).total_seconds()
    print(f"{diff:.1f}")
except Exception as e:
    print(f"ERROR:{e}")
PYTHON_SCRIPT
)

        if [[ "${startup_b}" == ERROR* ]]; then
            log_error "Failed to calculate Image_B startup time: ${startup_b}"
            echo "Image_B_startup_time=ERROR" >> "${RESULT_FILE}"
        else
            log_success "Image_B startup time: ${startup_b} seconds"
            echo "Image_B_startup_time=${startup_b}s" >> "${RESULT_FILE}"
        fi
    fi

    # Calculate and display speedup
    if [[ -n "${startup_a}" && -n "${startup_b}" && "${startup_a}" != ERROR* && "${startup_b}" != ERROR* ]]; then
        local speedup
        speedup=$(python3 -c "print(f'{float(${startup_b}) / float(${startup_a}):.1f}')" 2>/dev/null || echo "N/A")
        if [[ "${speedup}" != "N/A" ]]; then
            log_success "Startup speedup: Image_A is ${speedup}x faster than Image_B"
            echo "startup_speedup=${speedup}x" >> "${RESULT_FILE}"
        fi
    fi

    # Brief pause for containers to stabilize
    log_info "Waiting for containers to stabilize..."
    sleep 10

    # Benchmark performance
    log_section "Benchmark 2: Performance Under Load"
    benchmark_performance "benchmark-image-a" "${IMAGE_A_PORT}" "Image_A" &
    local perf_a=$!
    benchmark_performance "benchmark-image-b" "${IMAGE_B_PORT}" "Image_B" &
    local perf_b=$!
    wait "${perf_a}"
    wait "${perf_b}"

    # Benchmark resource utilization
    log_section "Benchmark 3: Resource Utilization"
    log_info "Monitoring resources for ${LOAD_TEST_DURATION} seconds..."
    benchmark_resources "benchmark-image-a" "Image_A" &
    local res_a=$!
    benchmark_resources "benchmark-image-b" "Image_B" &
    local res_b=$!
    wait "${res_a}"
    wait "${res_b}"

    # Print summary
    log_section "Benchmark Summary"
    cat "${RESULT_FILE}"
    echo ""
    log_success "Benchmark complete! Results saved to: ${RESULT_FILE}"
    log_info "Resource stats saved to: ${RESULTS_DIR}/"
}

# Run main function
main "$@"

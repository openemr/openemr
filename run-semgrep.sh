#!/bin/bash
#
# Run Semgrep on OpenEMR PHP code using Docker
#
# Usage:
#   ./run-semgrep.sh [options]
#
# Options:
#   --output-format <format>   Output format: text, json, sarif (default: text)
#   --output-file <file>       Write results to file instead of stdout
#   --config <config>          Semgrep config/ruleset (default: uses registry + local rules)
#   --severity <level>         Filter by severity: INFO, WARNING, ERROR (can repeat)
#   --exclude <pattern>        Additional patterns to exclude
#   --help                     Show this help message
#

set -e

# Default values
OUTPUT_FORMAT="text"
OUTPUT_FILE=""
CONFIG=""  # Empty means use default (registry + local)
SEVERITY=""
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Arrays for building arguments
declare -a EXTRA_EXCLUDES=()

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --output-format)
            OUTPUT_FORMAT="$2"
            shift 2
            ;;
        --output-file)
            OUTPUT_FILE="$2"
            shift 2
            ;;
        --config)
            CONFIG="$2"
            shift 2
            ;;
        --severity)
            SEVERITY="$2"
            shift 2
            ;;
        --exclude)
            EXTRA_EXCLUDES+=("--exclude=$2")
            shift 2
            ;;
        --help)
            head -20 "$0" | tail -15 || true
            exit 0
            ;;
        *)
            echo "Unknown option: $1"
            exit 1
            ;;
    esac
done

# Standard excludes for OpenEMR (matches CI workflow)
declare -a EXCLUDES=(
    "--exclude=vendor"
    "--exclude=node_modules"
    "--exclude=tests"
    "--exclude=ccdaservice/node_modules"
)

# Build output arguments
declare -a OUTPUT_ARGS=()
if [[ -n "${OUTPUT_FILE}" ]]; then
    OUTPUT_ARGS+=("--output=${OUTPUT_FILE}")
fi

# Build format argument (text is default, only specify for json/sarif)
declare -a FORMAT_ARG=()
if [[ "${OUTPUT_FORMAT}" = "json" ]]; then
    FORMAT_ARG+=("--json")
elif [[ "${OUTPUT_FORMAT}" = "sarif" ]]; then
    FORMAT_ARG+=("--sarif")
fi

# Build severity argument (can specify multiple: INFO, WARNING, ERROR)
declare -a SEVERITY_ARG=()
if [[ -n "${SEVERITY}" ]]; then
    for sev in ${SEVERITY}; do
        SEVERITY_ARG+=("--severity=${sev}")
    done
fi

# Build config args
declare -a CONFIG_ARGS=()
if [[ -n "${CONFIG}" ]]; then
    # User-specified config
    for cfg in ${CONFIG}; do
        if [[ -f "${cfg}" ]]; then
            cfg="/src/$(basename "${cfg}")"
        fi
        CONFIG_ARGS+=("--config=${cfg}")
    done
    echo "Config: ${CONFIG}"
else
    # Default: registry rulesets + local OpenEMR-specific rules
    CONFIG_ARGS+=(
        "--config=p/php"
        "--config=p/security-audit"
        "--config=/src/semgrep.yaml"
    )
    echo "Config: p/php p/security-audit semgrep.yaml"
fi

echo "Running Semgrep on OpenEMR PHP code..."
if [[ -n "${SEVERITY}" ]]; then
    echo "Severity filter: ${SEVERITY}"
fi
echo "Output format: ${OUTPUT_FORMAT}"
echo ""

# Run Semgrep in Docker using official semgrep/semgrep image
# Semgrep Docker image expects /src as mount point
docker run --rm \
    -v "${SCRIPT_DIR}:/src" \
    -w /src \
    semgrep/semgrep:latest \
    semgrep \
    "${CONFIG_ARGS[@]}" \
    "${SEVERITY_ARG[@]}" \
    "${FORMAT_ARG[@]}" \
    --no-git-ignore \
    "${EXCLUDES[@]}" \
    "${EXTRA_EXCLUDES[@]}" \
    "${OUTPUT_ARGS[@]}" \
    .

echo ""
echo "Semgrep scan complete."

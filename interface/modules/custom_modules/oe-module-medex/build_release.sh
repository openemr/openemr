#!/bin/bash
#
# Build MedEx customer bootstrap ZIP from manifest.
#
# The ZIP filename is bootstrap-specific, but the archive still expands to the
# OpenEMR module directory name `oe-module-medex/`.
#
# Usage:
#   cd oe-module-medex && bash build_release.sh
#

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
BUILD_DIR="${SCRIPT_DIR}/build"
STAGE_ROOT="${BUILD_DIR}/stage"
MODULE_DIR_NAME="oe-module-medex"
PACKAGE_NAME="oe-module-medex-bootstrap"
MANIFEST_FILE="${SCRIPT_DIR}/CUSTOMER_BOOTSTRAP_MANIFEST.md"
TIMESTAMP="$(date +%Y%m%d)"
ZIP_PATH="${BUILD_DIR}/${PACKAGE_NAME}-${TIMESTAMP}.zip"
STAGE_MODULE_DIR="${STAGE_ROOT}/${MODULE_DIR_NAME}"

extract_manifest_paths() {
    local start_heading="$1"
    local end_heading="$2"

    awk -v start="$start_heading" -v end="$end_heading" '
        $0 == start { in_section=1; next }
        $0 == end { in_section=0 }
        in_section && $0 ~ /^- `[^`]+`$/ {
            line = $0
            sub(/^- `/, "", line)
            sub(/`$/, "", line)
            print line
        }
    ' "$MANIFEST_FILE"
}

copy_manifest_file() {
    local relative_path="$1"
    local src="${SCRIPT_DIR}/${relative_path}"
    local dest="${STAGE_MODULE_DIR}/${relative_path}"

    if [ ! -e "$src" ]; then
        echo "[ERROR] Manifest file is missing from source tree: ${relative_path}" >&2
        exit 1
    fi

    mkdir -p "$(dirname "$dest")"
    cp -p "$src" "$dest"
}

validate_stage_against_denylist() {
    local violations=0
    local deny_entry

    while IFS= read -r deny_entry; do
        [ -n "$deny_entry" ] || continue

        case "$deny_entry" in
            */)
                if find "$STAGE_MODULE_DIR" -path "${STAGE_MODULE_DIR}/${deny_entry%/}" -prune | grep -q .; then
                    echo "[ERROR] Deny-listed directory present in stage: ${deny_entry}" >&2
                    violations=1
                fi
                ;;
            *'*'*)
                if find "$STAGE_MODULE_DIR" -name "$deny_entry" | grep -q .; then
                    echo "[ERROR] Deny-listed pattern present in stage: ${deny_entry}" >&2
                    violations=1
                fi
                ;;
            *)
                if [ -e "${STAGE_MODULE_DIR}/${deny_entry}" ]; then
                    echo "[ERROR] Deny-listed path present in stage: ${deny_entry}" >&2
                    violations=1
                fi
                ;;
        esac
    done < <(extract_manifest_paths "## Never Ship In Customer Bootstrap ZIP" "## Immediate Packaging Rule")

    if [ "$violations" -ne 0 ]; then
        echo "[ERROR] Stage validation failed against bootstrap deny-list." >&2
        exit 1
    fi
}

print_summary() {
    echo "=== Build Complete ==="
    echo
    echo "Created: ${ZIP_PATH}"
    echo "  Size: $(du -h "${ZIP_PATH}" | cut -f1)"
    echo "  Files: $(unzip -l "${ZIP_PATH}" | tail -1 | awk '{print $2}')"
    echo
    echo "Archive root directory: ${MODULE_DIR_NAME}/"
    echo "Manifest source: ${MANIFEST_FILE}"
}

if [ ! -f "$MANIFEST_FILE" ]; then
    echo "[ERROR] Bootstrap manifest not found: ${MANIFEST_FILE}" >&2
    exit 1
fi

rm -rf "$BUILD_DIR"
mkdir -p "$STAGE_MODULE_DIR"

echo "=== Building MedEx customer bootstrap ZIP ==="
echo "Source:   ${SCRIPT_DIR}"
echo "Manifest: ${MANIFEST_FILE}"
echo "Build:    ${BUILD_DIR}"
echo

copied_count=0
while IFS= read -r relative_path; do
    [ -n "$relative_path" ] || continue
    copy_manifest_file "$relative_path"
    copied_count=$((copied_count + 1))
done < <(extract_manifest_paths "## Bootstrap Package Contents" "## Component Packages")

if [ "$copied_count" -eq 0 ]; then
    echo "[ERROR] No bootstrap files were parsed from the manifest." >&2
    exit 1
fi

validate_stage_against_denylist

(cd "$STAGE_ROOT" && zip -rq "$ZIP_PATH" "$MODULE_DIR_NAME")

print_summary

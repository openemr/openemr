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
COMPONENT_BUILD_DIR="${BUILD_DIR}/components"

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

extract_component_entries() {
    awk '
        $0 == "## Component Packages" { in_components=1; next }
        $0 == "## SaaS Wrapper Files" { in_components=0 }
        in_components && $0 ~ /^### `component-[^`]+`$/ {
            component = $0
            sub(/^### `/, "", component)
            sub(/`$/, "", component)
            next
        }
        in_components && component != "" && $0 ~ /^- `[^`]+`$/ {
            path = $0
            sub(/^- `/, "", path)
            sub(/`$/, "", path)
            print component "\t" path
        }
    ' "$MANIFEST_FILE"
}

validate_stage_against_denylist() {
    local stage_module_dir="$1"
    local violations=0
    local deny_entry

    while IFS= read -r deny_entry; do
        [ -n "$deny_entry" ] || continue

        case "$deny_entry" in
            */)
                if find "$stage_module_dir" -path "${stage_module_dir}/${deny_entry%/}" -prune | grep -q .; then
                    echo "[ERROR] Deny-listed directory present in stage: ${deny_entry}" >&2
                    violations=1
                fi
                ;;
            *'*'*)
                if find "$stage_module_dir" -name "$deny_entry" | grep -q .; then
                    echo "[ERROR] Deny-listed pattern present in stage: ${deny_entry}" >&2
                    violations=1
                fi
                ;;
            *)
                if [ -e "${stage_module_dir}/${deny_entry}" ]; then
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

build_zip_from_stage() {
    local stage_dir="$1"
    local zip_path="$2"

    validate_stage_against_denylist "${stage_dir}/${MODULE_DIR_NAME}"
    (cd "$stage_dir" && zip -rq "$zip_path" "$MODULE_DIR_NAME")
}

print_zip_summary() {
    local label="$1"
    local zip_path="$2"

    echo "${label}: ${zip_path}"
    echo "  Size: $(du -h "${zip_path}" | cut -f1)"
    echo "  Files: $(unzip -l "${zip_path}" | tail -1 | awk '{print $2}')"
}

if [ ! -f "$MANIFEST_FILE" ]; then
    echo "[ERROR] Bootstrap manifest not found: ${MANIFEST_FILE}" >&2
    exit 1
fi

rm -rf "$BUILD_DIR"
mkdir -p "$STAGE_MODULE_DIR"
mkdir -p "$COMPONENT_BUILD_DIR"

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

build_zip_from_stage "$STAGE_ROOT" "$ZIP_PATH"

component_names_file="${BUILD_DIR}/component-names.txt"
extract_component_entries | awk -F '\t' '{print $1}' | awk '!seen[$0]++' > "$component_names_file"

while IFS= read -r component_name; do
    [ -n "$component_name" ] || continue
    component_stage_root="${COMPONENT_BUILD_DIR}/${component_name}/stage"
    component_stage_module_dir="${component_stage_root}/${MODULE_DIR_NAME}"
    mkdir -p "$component_stage_module_dir"

    while IFS=$'\t' read -r entry_component_name relative_path; do
        [ "$entry_component_name" = "$component_name" ] || continue
        STAGE_MODULE_DIR="$component_stage_module_dir" copy_manifest_file "$relative_path"
    done < <(extract_component_entries)
done < "$component_names_file"

echo "=== Build Complete ==="
echo
print_zip_summary "Bootstrap ZIP" "$ZIP_PATH"
echo

if [ -s "$component_names_file" ]; then
    echo "Component ZIPs:"
    while IFS= read -r component_name; do
        [ -n "$component_name" ] || continue
        component_zip="${COMPONENT_BUILD_DIR}/${component_name}-${TIMESTAMP}.zip"
        build_zip_from_stage "${COMPONENT_BUILD_DIR}/${component_name}/stage" "$component_zip"
        print_zip_summary "  ${component_name}" "$component_zip"
    done < "$component_names_file"
    echo
fi

echo "Archive root directory: ${MODULE_DIR_NAME}/"
echo "Manifest source: ${MANIFEST_FILE}"

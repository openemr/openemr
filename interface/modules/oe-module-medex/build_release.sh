#!/bin/bash
#
# Build MedEx Module release ZIPs
#
# Produces two ZIPs:
#   1. oe-module-medex-v8.zip  — for OpenEMR >=8.0 (post-PR, MedEx removed from core)
#   2. oe-module-medex-v7.zip  — for OpenEMR 7.x (pre-PR, library/MedEx still in core)
#
# Both contain the same module code. The module auto-detects which environment
# it's on (hasLegacyMedEx) and adapts install/enable/disable/unregister behavior.
# The v7 ZIP additionally includes a README_V7_INSTALL.md with legacy-specific notes.
#
# Usage: cd oe-module-medex && bash build_release.sh
#

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
BUILD_DIR="${SCRIPT_DIR}/build"
MODULE_NAME="oe-module-medex"
TIMESTAMP=$(date +%Y%m%d)

# Clean previous build
rm -rf "${BUILD_DIR}"
mkdir -p "${BUILD_DIR}/stage"

echo "=== Building MedEx Module release ZIPs ==="
echo "Source: ${SCRIPT_DIR}"
echo "Build:  ${BUILD_DIR}"
echo ""

# ---------------------------------------------------------------
# Collect files for the release (exclude dev/doc artifacts)
# ---------------------------------------------------------------
STAGE="${BUILD_DIR}/stage/${MODULE_NAME}"
mkdir -p "${STAGE}"

# Core PHP/JS/CSS/JSON/SQL files
rsync -a --prune-empty-dirs \
    --include='*/' \
    --include='*.php' \
    --include='*.js' \
    --include='*.css' \
    --include='*.json' \
    --include='*.sql' \
    --include='*.html' \
    --include='*.lock' \
    --exclude='*' \
    "${SCRIPT_DIR}/" "${STAGE}/"

# Include essential docs only
for doc in README.md INSTALL.md LICENSE.md CHANGELOG.md HELP.md QUICK_START.md; do
    [ -f "${SCRIPT_DIR}/${doc}" ] && cp "${SCRIPT_DIR}/${doc}" "${STAGE}/"
done

# Remove dev/test artifacts that shouldn't ship
rm -rf "${STAGE}/build"
rm -rf "${STAGE}/tests"
rm -f "${STAGE}/test_integration.sh"
rm -f "${STAGE}/modernize_api.php"
rm -f "${STAGE}/debug_api.php"  2>/dev/null || true
rm -f "${STAGE}/admin/debug_api.php"
rm -f "${STAGE}/src/API/API_Original_Backup.php"
rm -rf "${STAGE}/backup_recall_board_assets"
rm -f "${STAGE}/.DS_Store"
find "${STAGE}" -name '.DS_Store' -delete 2>/dev/null || true
find "${STAGE}" -name '*.bak*' -delete 2>/dev/null || true

# ---------------------------------------------------------------
# v8 ZIP (post-PR / clean core)
# ---------------------------------------------------------------
echo "--- Building v8 ZIP (OpenEMR >=8.0, post-PR) ---"

V8_ZIP="${BUILD_DIR}/${MODULE_NAME}-v8-${TIMESTAMP}.zip"
(cd "${BUILD_DIR}/stage" && zip -r "${V8_ZIP}" "${MODULE_NAME}" -x '*/.DS_Store' '*/.*')
echo "Created: ${V8_ZIP}"
echo "  Size: $(du -h "${V8_ZIP}" | cut -f1)"
echo ""

# ---------------------------------------------------------------
# v7 ZIP (pre-PR / legacy core with library/MedEx)
# Add v7-specific install docs
# ---------------------------------------------------------------
echo "--- Building v7 ZIP (OpenEMR 7.x, pre-PR) ---"

cat > "${STAGE}/README_V7_INSTALL.md" << 'V7EOF'
# MedEx Module — OpenEMR 7.x Installation Guide

## Prerequisites
- OpenEMR 7.0.x with existing `library/MedEx/` directory
- Admin access to Module Manager (Administration → Modules)

## Installation Steps

1. **Extract** this ZIP to:
   ```
   <openemr>/interface/modules/custom_modules/oe-module-medex/
   ```

2. **Register** the module in Module Manager:
   - Go to Administration → Modules → Manage Modules → Unregistered
   - Find "MedEx Communication Manager" and click Register

3. **Install** the module:
   - Click Install on the module row
   - This will **automatically deactivate** the legacy `library/MedEx/MedEx_background.php` background service
   - The previous background service state is saved and will be restored if you uninstall

4. **Enable** the module:
   - Click Enable
   - The module now manages all MedEx communication features

## What Happens on Install (v7 / pre-PR)

- The legacy `MedEx` background service in `background_services` is set to `active=0`
- Its previous state is saved in `medex_module_state` so it can be restored
- The module's own API handles all MedEx server communication
- The legacy `library/MedEx/API.php` is NOT modified or deleted

## Uninstall / Disable

- **Disable**: Restores the legacy background service to its previous state
- **Unregister**: Restores background service, clears credentials, drops `medex_module_state`
- `library/MedEx/` files are never touched — they remain intact throughout

## Cron Jobs

If you have a cron job running `library/ajax/execute_background_services.php`
for MedEx, it will be harmless while the module is active (the background service
row is set to `active=0`). You do not need to modify your crontab.
V7EOF

V7_ZIP="${BUILD_DIR}/${MODULE_NAME}-v7-${TIMESTAMP}.zip"
(cd "${BUILD_DIR}/stage" && zip -r "${V7_ZIP}" "${MODULE_NAME}" -x '*/.DS_Store' '*/.*')
echo "Created: ${V7_ZIP}"
echo "  Size: $(du -h "${V7_ZIP}" | cut -f1)"
echo ""

# ---------------------------------------------------------------
# Summary
# ---------------------------------------------------------------
echo "=== Build Complete ==="
echo ""
echo "v8 (OpenEMR >=8.0, post-PR):  ${V8_ZIP}"
echo "v7 (OpenEMR 7.x, pre-PR):    ${V7_ZIP}"
echo ""
echo "File counts:"
echo "  v8: $(unzip -l "${V8_ZIP}" | tail -1 | awk '{print $2}') files"
echo "  v7: $(unzip -l "${V7_ZIP}" | tail -1 | awk '{print $2}') files"
echo ""
echo "To test in k8s:"
echo "  kubectl cp ${V8_ZIP} openemr/<pod>:/tmp/"
echo "  kubectl exec -it <pod> -- unzip -o /tmp/$(basename ${V8_ZIP}) -d /var/www/localhost/htdocs/openemr/interface/modules/custom_modules/"

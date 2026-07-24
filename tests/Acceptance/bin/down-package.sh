#!/usr/bin/env bash
#
# Tear down the acceptance-package openemr stack booted by boot-package.sh.
#
# Removes containers, network, AND the mariadb named volume. Also removes
# the scratch dir at /tmp/openemr-acceptance-<version>/ (specified as
# argument, or all matching dirs if no arg).
#
# Usage:
#   tests/Acceptance/bin/down-package.sh [version]
#     version — optional; if given, removes only /tmp/openemr-acceptance-<version>/
#               if omitted, removes ALL /tmp/openemr-acceptance-*/ scratch dirs

set -euo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"
REPO_ROOT="$(cd -- "${SCRIPT_DIR}/../../.." &>/dev/null && pwd)"

cd "${REPO_ROOT}"

echo "==> Tearing down openemr acceptance-package stack"

# TARBALL_DIR must be defined (even to a dummy value) or compose warns
# and fails to parse the file — the compose file references it in the
# openemr service's volumes: block. Actual value doesn't matter at
# teardown time; the containers already exist with their mount baked in.
export TARBALL_DIR="/dev/null"

docker compose \
    -f .github/docker/acceptance-package-compose.yml \
    -p openemr-acceptance-package \
    down --volumes --remove-orphans || true

if [[ $# -eq 1 ]]; then
    SCRATCH="/tmp/openemr-acceptance-$1"
    echo "==> Removing scratch dir ${SCRATCH}"
    rm -rf "${SCRATCH}" "/tmp/openemr-$1.tar.gz"
else
    echo "==> Removing all /tmp/openemr-acceptance-*/ scratch dirs"
    rm -rf /tmp/openemr-acceptance-*/
    rm -f /tmp/openemr-*.tar.gz
fi

echo "==> Teardown complete."

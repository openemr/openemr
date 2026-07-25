#!/usr/bin/env bash
#
# Tear down the acceptance-testing openemr Docker stack booted by
# tests/Acceptance/bin/boot-docker.sh.
#
# Removes containers, network, AND the named volumes (databasevolume,
# sitevolume, logvolume01) — a fresh boot afterwards will re-run the
# auto-install rather than picking up the prior installation.
#
# Usage:
#   tests/Acceptance/bin/down-docker.sh

set -euo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"
REPO_ROOT="$(cd -- "${SCRIPT_DIR}/../../.." &>/dev/null && pwd)"

cd "${REPO_ROOT}"

echo "==> Tearing down openemr acceptance stack (removing volumes)"

docker compose \
    -f docker/production/docker-compose.yml \
    -f .github/docker/acceptance-docker-compose.yml \
    -p openemr-acceptance \
    down --volumes --remove-orphans

echo "==> Teardown complete."

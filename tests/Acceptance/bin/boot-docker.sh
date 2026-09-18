#!/usr/bin/env bash
#
# Boot an openemr Docker image for acceptance testing on a laptop.
#
# Composes docker/production/docker-compose.yml with the acceptance
# override at .github/docker/acceptance-docker-compose.yml — same
# production stack shape end users deploy, with the image ref driven
# by $1 (defaults to `latest`) and ports rebound to 8580/8543 so
# nothing collides with a dev stack running alongside.
#
# Usage:
#   tests/Acceptance/bin/boot-docker.sh [tag]
#     tag  — Docker Hub tag to boot (default: latest)
#            Examples: latest, next, dev, 8.2.0, 8.1.0-2026-06-01
#
# After a successful boot, the artifact is reachable at
#   http://localhost:8580   (matches ACCEPTANCE_ARTIFACT_URL's default)
#
# Run the acceptance suite against it:
#   composer acceptance
#   # or:
#   vendor/bin/phpunit -c tests/Acceptance/phpunit.acceptance.xml
#
# Tear down (preserving no state):
#   tests/Acceptance/bin/down-docker.sh

set -euo pipefail

TAG="${1:-latest}"

# Locate repo root regardless of where this script is invoked from
SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"
REPO_ROOT="$(cd -- "${SCRIPT_DIR}/../../.." &>/dev/null && pwd)"

cd "${REPO_ROOT}"

echo "==> Booting openemr/openemr:${TAG} for acceptance testing"

OPENEMR_TAG="${TAG}" docker compose \
    -f docker/production/docker-compose.yml \
    -f .github/docker/acceptance-docker-compose.yml \
    -p openemr-acceptance \
    up --detach --wait --wait-timeout 900

echo ""
echo "==> Boot complete."
echo "    Artifact URL:  http://localhost:8580"
echo "    HTTPS URL:     https://localhost:8543 (self-signed cert)"
echo ""
echo "    Run tests:     composer acceptance"
echo "    Teardown:      tests/Acceptance/bin/down-docker.sh"

#!/usr/bin/env bash
#
# Boot the acceptance-package stack against an openemr release tarball.
#
# Downloads the tarball from the GitHub release page, extracts it into
# a scratch directory, boots the compose stack (flex image + mariadb)
# with the extracted tree bind-mounted, then runs install-helper.php
# via `docker compose exec` to complete the install via OpenEMR's
# Installer class.
#
# Usage:
#   tests/Acceptance/bin/boot-package.sh <version>
#     version — release tag suffix, must match ^[0-9]+\.[0-9]+\.[0-9]+$
#               (e.g. 8.2.0). Anything else is rejected before any path
#               is constructed — VERSION is caller-controlled and flows
#               into `rm -rf` / `curl -o` paths.
#               Fetches https://github.com/openemr/openemr/releases/download/v<X_Y_Z>/openemr-<version>.tar.gz
#
# After a successful boot:
#   Artifact URL:  http://localhost:8680
#   HTTPS URL:     https://localhost:8643  (self-signed cert)
#   Scratch dir:   /tmp/openemr-acceptance-<version>/  (bind-mount source)
#
# Run tests:      ACCEPTANCE_ARTIFACT_URL=http://localhost:8680 composer acceptance
# Tear down:      tests/Acceptance/bin/down-package.sh

set -euo pipefail

if [[ $# -ne 1 ]]; then
    echo "Usage: $0 <version>" >&2
    echo "  e.g.: $0 8.2.0" >&2
    exit 2
fi

VERSION="$1"

# Validate VERSION before it flows into any path. VERSION is
# caller-controlled; a value like `foo/../../../workspace` could make
# TARBALL_DIR escape /tmp and the later `rm -rf` would delete arbitrary
# runner files. Only accept semver-shape strings.
if [[ ! "${VERSION}" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "::error::VERSION '${VERSION}' does not match expected format X.Y.Z" >&2
    exit 2
fi

# openemr's tag scheme uses underscores: 8.2.0 → v8_2_0. Bash
# parameter-substitution avoids the SC2250 SC lint that `$(echo ... | tr)`
# would trigger.
TAG_NAME="v${VERSION//./_}"
TARBALL_URL="https://github.com/openemr/openemr/releases/download/${TAG_NAME}/openemr-${VERSION}.tar.gz"

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"
REPO_ROOT="$(cd -- "${SCRIPT_DIR}/../../.." &>/dev/null && pwd)"

# Persist TARBALL_DIR + HELPER_PATH for every compose invocation in this
# script (compose reparses the file each time and would otherwise warn
# about the unset variable + create broken bind mounts).
export TARBALL_DIR="/tmp/openemr-acceptance-${VERSION}"
export HELPER_PATH="${SCRIPT_DIR}/install-helper.php"
export COMPOSE_FILE="${REPO_ROOT}/.github/docker/acceptance-package-compose.yml"
export COMPOSE_PROJECT_NAME="openemr-acceptance-package"

# Random tarball path via mktemp — a predictable path like
# /tmp/openemr-${VERSION}.tar.gz would let a local attacker
# pre-create a symlink to redirect the curl download.
TARBALL_PATH="$(mktemp -t "openemr-acceptance-tarball.XXXXXX.tar.gz")"
trap 'rm -f "${TARBALL_PATH}"' EXIT

cd "${REPO_ROOT}"

echo "==> Preparing scratch dir at ${TARBALL_DIR}"
rm -rf "${TARBALL_DIR}"
mkdir -p "${TARBALL_DIR}"

echo "==> Downloading ${TARBALL_URL}"
curl -fsSL "${TARBALL_URL}" -o "${TARBALL_PATH}"

echo "==> Extracting into ${TARBALL_DIR}"
# --strip-components=1 pulls contents up so ${TARBALL_DIR} matches the
# openemr web-root layout the flex image's bind mount expects.
tar -pxzf "${TARBALL_PATH}" -C "${TARBALL_DIR}" --strip-components=1

echo "==> Booting compose stack (mysql + openemr, EMPTY=yes)"
docker compose up --detach --no-recreate

echo "==> Waiting for MariaDB to become healthy (compose --wait can't be used"
echo "    here because the openemr healthcheck depends on install running first)"
for attempt in $(seq 1 30); do
    HEALTH="$(docker compose ps mysql --format '{{.Health}}' 2>/dev/null || echo unknown)"
    if [[ "${HEALTH}" == "healthy" ]]; then
        echo "    mysql healthy"
        break
    fi
    if [[ "${attempt}" -eq 30 ]]; then
        echo "::error::mysql did not become healthy within 150s (last status: ${HEALTH})" >&2
        exit 1
    fi
    sleep 5
done

echo "==> Running install-helper.php via docker compose exec (as apache user)"
# install-helper.php is mounted READ-ONLY at /opt/openemr-acceptance-helper.php
# by the compose file (OUTSIDE the openemr webroot, so Apache never
# serves it). RootCliGuard rejects UID 0 → wrap in `su -s /bin/sh apache`.
docker compose exec -T openemr \
    su -s /bin/sh apache -c \
    'php /opt/openemr-acceptance-helper.php'

echo "==> Waiting for openemr container to become healthy"
for attempt in $(seq 1 60); do
    HEALTH="$(docker compose ps openemr --format '{{.Health}}' 2>/dev/null || echo unknown)"
    if [[ "${HEALTH}" == "healthy" ]]; then
        echo "    openemr healthy"
        break
    fi
    if [[ "${attempt}" -eq 60 ]]; then
        echo "::error::openemr did not become healthy within 300s (last status: ${HEALTH})" >&2
        exit 1
    fi
    sleep 5
done

echo ""
echo "==> Boot complete."
echo "    Artifact URL:  http://localhost:8680"
echo "    HTTPS URL:     https://localhost:8643 (self-signed cert)"
echo ""
echo "    Run tests:     ACCEPTANCE_ARTIFACT_URL=http://localhost:8680 composer acceptance"
echo "    Teardown:      tests/Acceptance/bin/down-package.sh"

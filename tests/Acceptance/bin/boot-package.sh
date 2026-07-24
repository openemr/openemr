#!/usr/bin/env bash
#
# Boot the acceptance-package stack against an openemr release tarball.
#
# Downloads the tarball from the GitHub release page, extracts it into
# a scratch directory, boots the compose stack (flex image + mariadb)
# with the extracted tree bind-mounted, then runs the flex image's
# built-in `/root/devtools dev-install` to complete the install via
# OpenEMR's Installer class. Same code path openemr-cmd `dev-install`
# uses for local dev.
#
# Usage:
#   tests/Acceptance/bin/boot-package.sh <version>
#     version — release tag suffix (e.g. 8.2.0, 8.1.0)
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
# openemr's tag scheme uses underscores: 8.2.0 → v8_2_0
TAG_NAME="v$(echo "$VERSION" | tr '.' '_')"
TARBALL_URL="https://github.com/openemr/openemr/releases/download/${TAG_NAME}/openemr-${VERSION}.tar.gz"

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"
REPO_ROOT="$(cd -- "${SCRIPT_DIR}/../../.." &>/dev/null && pwd)"

# Persist TARBALL_DIR for every compose invocation in this script (compose
# reparses the file each time and would otherwise warn about the unset
# variable + create broken bind mounts).
export TARBALL_DIR="/tmp/openemr-acceptance-${VERSION}"
export COMPOSE_FILE="${REPO_ROOT}/.github/docker/acceptance-package-compose.yml"
export COMPOSE_PROJECT_NAME="openemr-acceptance-package"

cd "${REPO_ROOT}"

echo "==> Preparing scratch dir at ${TARBALL_DIR}"
rm -rf "${TARBALL_DIR}"
mkdir -p "${TARBALL_DIR}"

echo "==> Downloading ${TARBALL_URL}"
curl -fsSL "${TARBALL_URL}" -o "/tmp/openemr-${VERSION}.tar.gz"

echo "==> Extracting into ${TARBALL_DIR}"
# --strip-components=1 pulls contents up so ${TARBALL_DIR} matches the
# openemr web-root layout the flex image's bind mount expects.
tar -pxzf "/tmp/openemr-${VERSION}.tar.gz" -C "${TARBALL_DIR}" --strip-components=1

echo "==> Copying install-helper.php into the tarball tree"
# The tarball ships without docker/release/auto_configure.php (docker/
# is export-ignored). Flex image's own /root/devtools dev-install
# depends on /root/auto_configure.php which openemr.sh removes at boot
# when EMPTY=yes for security (installer must not remain accessible).
# So we bring our own leaner script that instantiates OpenEMR's
# Installer class directly with the tarball's vendor/ autoload.
cp "${SCRIPT_DIR}/install-helper.php" "${TARBALL_DIR}/install-helper.php"

echo "==> Booting compose stack (mysql + openemr, EMPTY=yes)"
docker compose up --detach --no-recreate

echo "==> Waiting for MariaDB to become healthy (compose --wait can't be used"
echo "    here because the openemr healthcheck depends on install running first)"
for attempt in $(seq 1 30); do
    HEALTH=$(docker compose ps mysql --format '{{.Health}}' 2>/dev/null || echo unknown)
    if [[ "$HEALTH" == "healthy" ]]; then
        echo "    mysql healthy"
        break
    fi
    if [[ $attempt -eq 30 ]]; then
        echo "::error::mysql did not become healthy within 150s (last status: $HEALTH)" >&2
        exit 1
    fi
    sleep 5
done

echo "==> Running install-helper.php inside openemr container (as apache user)"
# OpenEMR's RootCliGuard rejects installer runs under UID 0 — installer
# would create root-owned files the web server can't read. Wrap in
# `su -s /bin/sh apache` to run as the apache user (same pattern as
# openemr-cmd's Symfony-console invocations).
docker compose exec -T openemr \
    su -s /bin/sh apache -c \
    'php /var/www/localhost/htdocs/openemr/install-helper.php'

echo "==> Waiting for openemr container to become healthy"
for attempt in $(seq 1 60); do
    HEALTH=$(docker compose ps openemr --format '{{.Health}}' 2>/dev/null || echo unknown)
    if [[ "$HEALTH" == "healthy" ]]; then
        echo "    openemr healthy"
        break
    fi
    if [[ $attempt -eq 60 ]]; then
        echo "::error::openemr did not become healthy within 300s (last status: $HEALTH)" >&2
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

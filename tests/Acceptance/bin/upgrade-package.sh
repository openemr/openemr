#!/usr/bin/env bash
#
# Upgrade an already-installed openemr tarball to a newer version.
#
# Prerequisite: tests/Acceptance/bin/boot-package.sh <from-version> was
# run first (so <from> is installed with data). This script:
#
#   1. Downloads the <to> tarball from GitHub releases
#   2. Extracts it into a scratch dir
#   3. Copies the <from> install's sites/ dir into the <to> extraction
#      (preserves DB config + any installed modules — matches the wiki
#      "Move openemr/ to openemr_bk/, extract new, copy sites/" flow)
#   4. Swaps the compose bind mount from <from> to <to> (stops openemr
#      container, restarts with new TARBALL_DIR — mysql/volumes preserved)
#   5. Runs sql_upgrade.php in CLI mode via `docker compose exec`,
#      passing --from=<from> so the upgrade script knows which schema
#      migrations to apply
#
# Usage:
#   tests/Acceptance/bin/upgrade-package.sh <from-version> <to-version>
#     from-version — the version already installed via boot-package.sh
#     to-version   — the target version (must have a v<X_Y_Z> release tag)
#     Both must match ^[0-9]+\.[0-9]+\.[0-9]+$; from must be strictly
#     less than to (this is an UPgrade — same-version is a no-op that
#     would delete the installed source, and downgrade isn't supported).
#
# After successful upgrade:
#   Same URL:      http://localhost:8680  (openemr now serving <to>)
#   Scratch dir:   /tmp/openemr-acceptance-<to>/  (bind-mount source)

set -euo pipefail

if [[ $# -ne 2 ]]; then
    echo "Usage: $0 <from-version> <to-version>" >&2
    echo "  e.g.: $0 8.2.0 8.3.0" >&2
    exit 2
fi

FROM_VERSION="$1"
TO_VERSION="$2"

# Validate both versions before either flows into a path — same
# reasoning as boot-package.sh. Also reject same-version (no-op that
# would delete installed source) and downgrade (unsupported).
for v in "${FROM_VERSION}" "${TO_VERSION}"; do
    if [[ ! "${v}" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        echo "::error::version '${v}' does not match expected format X.Y.Z" >&2
        exit 2
    fi
done
# Use `sort -V` for semantic version comparison.
# `printf '%s\n' from to | sort -V | head -1` returns the smaller version;
# for an upgrade, that must be FROM (and FROM != TO).
if [[ "${FROM_VERSION}" == "${TO_VERSION}" ]]; then
    echo "::error::FROM_VERSION and TO_VERSION are identical (${FROM_VERSION})" >&2
    exit 2
fi
SMALLER="$(printf '%s\n%s\n' "${FROM_VERSION}" "${TO_VERSION}" | sort -V | head -1)"
if [[ "${SMALLER}" != "${FROM_VERSION}" ]]; then
    echo "::error::FROM_VERSION (${FROM_VERSION}) is not less than TO_VERSION (${TO_VERSION}); downgrade not supported" >&2
    exit 2
fi

FROM_TARBALL_DIR="/tmp/openemr-acceptance-${FROM_VERSION}"
TO_TARBALL_DIR="/tmp/openemr-acceptance-${TO_VERSION}"

TO_TAG_NAME="v${TO_VERSION//./_}"
TO_TARBALL_URL="https://github.com/openemr/openemr/releases/download/${TO_TAG_NAME}/openemr-${TO_VERSION}.tar.gz"

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"
REPO_ROOT="$(cd -- "${SCRIPT_DIR}/../../.." &>/dev/null && pwd)"

export HELPER_PATH="${SCRIPT_DIR}/install-helper.php"
export COMPOSE_FILE="${REPO_ROOT}/.github/docker/acceptance-package-compose.yml"
export COMPOSE_PROJECT_NAME="openemr-acceptance-package"

# mktemp for the download path (same rationale as boot-package.sh).
TARBALL_PATH="$(mktemp -t "openemr-acceptance-upgrade-tarball.XXXXXX.tar.gz")"
trap 'rm -f "${TARBALL_PATH}"' EXIT

cd "${REPO_ROOT}"

if [[ ! -d "${FROM_TARBALL_DIR}/sites" ]]; then
    echo "::error::${FROM_TARBALL_DIR}/sites not found — did boot-package.sh ${FROM_VERSION} run first?" >&2
    exit 1
fi

echo "==> Preparing scratch dir at ${TO_TARBALL_DIR}"
rm -rf "${TO_TARBALL_DIR}"
mkdir -p "${TO_TARBALL_DIR}"

echo "==> Downloading ${TO_TARBALL_URL}"
curl -fsSL "${TO_TARBALL_URL}" -o "${TARBALL_PATH}"

echo "==> Extracting into ${TO_TARBALL_DIR}"
tar -pxzf "${TARBALL_PATH}" -C "${TO_TARBALL_DIR}" --strip-components=1

echo "==> Overlaying ${FROM_VERSION}'s sites/ dir onto ${TO_VERSION} extraction"
# Matches the wiki "Copy the old OpenEMR sites/ to the new OpenEMR at
# openemr/sites/" step — preserves site config + document paths + any
# per-site customization the from-install accumulated.
rm -rf "${TO_TARBALL_DIR}/sites"
cp -a "${FROM_TARBALL_DIR}/sites" "${TO_TARBALL_DIR}/sites"

echo "==> Stopping openemr container (mysql + volumes preserved)"
export TARBALL_DIR="${FROM_TARBALL_DIR}"
docker compose stop openemr

echo "==> Re-creating openemr container with ${TO_VERSION} bind mount"
export TARBALL_DIR="${TO_TARBALL_DIR}"
docker compose up --detach --force-recreate --no-deps openemr

echo "==> Running sql_upgrade.php CLI mode (--from=${FROM_VERSION})"
# The wiki upgrade flow points users at sql_upgrade.php in the browser
# with a "select prior version" dropdown. CLI mode does the same thing
# with --from=<version> passed on the argv, bypassing the HTTP form.
# Runs as apache (RootCliGuard rejects UID 0). --from value already
# validated at script entry — safe to interpolate into the su command.
docker compose exec -T openemr \
    su -s /bin/sh apache -c \
    "php /var/www/localhost/htdocs/openemr/sql_upgrade.php --from=${FROM_VERSION}"

echo "==> Waiting for openemr container to become healthy post-upgrade"
for attempt in $(seq 1 60); do
    HEALTH="$(docker compose ps openemr --format '{{.Health}}' 2>/dev/null || echo unknown)"
    if [[ "${HEALTH}" == "healthy" ]]; then
        echo "    openemr healthy"
        break
    fi
    if [[ "${attempt}" -eq 60 ]]; then
        echo "::error::openemr did not become healthy post-upgrade within 300s (last status: ${HEALTH})" >&2
        exit 1
    fi
    sleep 5
done

echo ""
echo "==> Upgrade complete: ${FROM_VERSION} → ${TO_VERSION}"
echo "    Artifact URL:  http://localhost:8680  (now serving ${TO_VERSION})"
echo ""
echo "    Run tests:     ACCEPTANCE_ARTIFACT_URL=http://localhost:8680 composer acceptance -- --group=post-upgrade"
echo "    Teardown:      tests/Acceptance/bin/down-package.sh"

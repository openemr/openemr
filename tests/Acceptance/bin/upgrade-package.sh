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
#   tests/Acceptance/bin/upgrade-package.sh [--skip-sql-upgrade] \
#     [--to-local-tarball=<path> | --to-local-zip=<path>] <from-version> <to-version>
#     --skip-sql-upgrade (optional)
#         Skip step 5. Leaves the artifact serving to-version's
#         filesystem with from-version's DB — the classic "user just
#         extracted new tarball, hasn't run the upgrade script yet"
#         state, ready for UpgradeWizardUiTest (Phase 4c-2) to drive
#         sql_upgrade.php via Panther. Also skips the post-upgrade
#         healthcheck wait (the compose healthcheck asserts the
#         login-page redirect target, which the pre-upgrade state
#         still meets; but the DB migrations haven't run, so any
#         downstream test that touches the DB would fail).
#     --to-local-tarball=<path> (optional)
#         Extract the given local .tar.gz as the to-version artifact
#         instead of curl-ing the GitHub release page. Phase 3.5
#         PR-tarball validation: acceptance-package workflow builds
#         the tarball via PackageAssembler from the PR checkout,
#         uploads it as a workflow artifact, and passes the
#         downloaded path here. from-version is always downloaded
#         from GitHub (whole point of the upgrade test is starting
#         from a shipped release).
#     --to-local-zip=<path> (optional)
#         Extract the given local .zip as the to-version artifact.
#         Same semantics as --to-local-tarball but for the ZIP
#         artifact — Phase 3.6 slice 2 upgrade[zip] +
#         wizard-upgrade[zip] matrix cells use this. Mutually
#         exclusive with --to-local-tarball.
#     from-version — the version already installed via boot-package.sh
#     to-version   — the target version (must have a v<X_Y_Z> release tag,
#                    unless --to-local-tarball or --to-local-zip is set)
#     Both must match ^[0-9]+\.[0-9]+\.[0-9]+$; from must be strictly
#     less than to (this is an UPgrade — same-version is a no-op that
#     would delete the installed source, and downgrade isn't supported).
#
# After successful upgrade (default mode):
#   Same URL:      http://localhost:8680  (openemr now serving <to>)
#   Scratch dir:   /tmp/openemr-acceptance-<to>/  (bind-mount source)

set -euo pipefail

skip_sql_upgrade="false"
to_local_tarball=""
to_local_zip=""
while [[ $# -gt 0 && "$1" == --* ]]; do
    case "$1" in
        --skip-sql-upgrade)
            skip_sql_upgrade="true"
            shift
            ;;
        --to-local-tarball=*)
            to_local_tarball="${1#--to-local-tarball=}"
            shift
            ;;
        --to-local-zip=*)
            to_local_zip="${1#--to-local-zip=}"
            shift
            ;;
        *)
            echo "::error::unknown flag: $1" >&2
            exit 2
            ;;
    esac
done

if [[ -n "${to_local_tarball}" && -n "${to_local_zip}" ]]; then
    echo "::error::--to-local-tarball and --to-local-zip are mutually exclusive" >&2
    exit 2
fi

if [[ $# -ne 2 ]]; then
    echo "Usage: $0 [--skip-sql-upgrade] [--to-local-tarball=<path> | --to-local-zip=<path>] <from-version> <to-version>" >&2
    echo "  e.g.: $0 8.2.0 8.3.0" >&2
    echo "        $0 --skip-sql-upgrade 8.2.0 8.3.0" >&2
    echo "        $0 --to-local-tarball=/tmp/openemr-8.3.0.tar.gz 8.2.0 8.3.0" >&2
    echo "        $0 --to-local-zip=/tmp/openemr-8.3.0.zip 8.2.0 8.3.0" >&2
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

# shellcheck source=tests/Acceptance/bin/lib/extract-zip.sh
source "${SCRIPT_DIR}/lib/extract-zip.sh"

export HELPER_PATH="${SCRIPT_DIR}/install-helper.php"
export COMPOSE_FILE="${REPO_ROOT}/.github/docker/acceptance-package-compose.yml"
export COMPOSE_PROJECT_NAME="openemr-acceptance-package"

if [[ -n "${to_local_tarball}" ]]; then
    # Local-tarball mode: caller-supplied file, no network fetch.
    if [[ ! -f "${to_local_tarball}" ]]; then
        echo "::error::--to-local-tarball path does not exist or is not a regular file: ${to_local_tarball}" >&2
        exit 2
    fi
    # Absolute-ize before the `cd "${REPO_ROOT}"` below — same
    # rationale as boot-package.sh's --local-tarball normalization.
    ARTIFACT_PATH="$(realpath "${to_local_tarball}")"
    ARTIFACT_FORMAT="tar"
    # No trap cleanup — the caller owns this file.
elif [[ -n "${to_local_zip}" ]]; then
    # Local-zip mode — same shape as local-tarball; the extractor
    # further below switches on ARTIFACT_FORMAT.
    if [[ ! -f "${to_local_zip}" ]]; then
        echo "::error::--to-local-zip path does not exist or is not a regular file: ${to_local_zip}" >&2
        exit 2
    fi
    ARTIFACT_PATH="$(realpath "${to_local_zip}")"
    ARTIFACT_FORMAT="zip"
    # No trap cleanup — the caller owns this file.
else
    # mktemp for the download path (same rationale as boot-package.sh).
    ARTIFACT_PATH="$(mktemp -t "openemr-acceptance-upgrade-tarball.XXXXXX.tar.gz")"
    ARTIFACT_FORMAT="tar"
    trap 'rm -f "${ARTIFACT_PATH}"' EXIT
fi

cd "${REPO_ROOT}"

if [[ ! -d "${FROM_TARBALL_DIR}/sites" ]]; then
    echo "::error::${FROM_TARBALL_DIR}/sites not found — did boot-package.sh ${FROM_VERSION} run first?" >&2
    exit 1
fi

# Make from-version files readable to the host user before the sites/
# overlay below. The flex-image apache runs at its own UID (typically
# 100 in Alpine), and any files that apache created during install-
# helper + downstream test activity — most notably the OAuth key pair
# under sites/default/documents/certificates/, generated during OAuth
# setup with 0400 perms — land on the host bind mount owned by that
# UID and unreadable to the host runner user. Without this chown, the
# `cp -a` below fails with `Permission denied` on those keys.
#
# `chown -R $(id -u):$(id -g)` inside the still-running from-container
# rewrites ownership to the host runner user via the shared bind
# mount, giving cp read access. Runs before the TO scratch-dir setup
# so the from-container is still up when this executes. Ownership is
# discarded seconds later when the to-version container recreates the
# bind mount (compose stop + up --force-recreate), so there's no
# lingering side effect on the downstream to_version boot.
export TARBALL_DIR="${FROM_TARBALL_DIR}"
# Split `id -u` / `id -g` into their own vars — shellcheck SC2312
# flags inline `$(...)` in a compound command because a failing
# substitution's non-zero exit is masked by the outer chown.
RUNNER_UID="$(id -u)"
RUNNER_GID="$(id -g)"
docker compose exec -T openemr chown -R "${RUNNER_UID}:${RUNNER_GID}" /var/www/localhost/htdocs/openemr/sites

echo "==> Preparing scratch dir at ${TO_TARBALL_DIR}"
rm -rf "${TO_TARBALL_DIR}"
mkdir -p "${TO_TARBALL_DIR}"

if [[ -n "${to_local_tarball}" ]]; then
    echo "==> Using local to-version tarball ${ARTIFACT_PATH} (--to-local-tarball set — skipping GitHub download)"
elif [[ -n "${to_local_zip}" ]]; then
    echo "==> Using local to-version zip ${ARTIFACT_PATH} (--to-local-zip set — skipping GitHub download)"
else
    echo "==> Downloading ${TO_TARBALL_URL}"
    curl -fsSL "${TO_TARBALL_URL}" -o "${ARTIFACT_PATH}"
fi

echo "==> Extracting into ${TO_TARBALL_DIR} (format=${ARTIFACT_FORMAT})"
case "${ARTIFACT_FORMAT}" in
    tar)
        tar -pxzf "${ARTIFACT_PATH}" -C "${TO_TARBALL_DIR}" --strip-components=1
        ;;
    zip)
        # Same unzip + single-top-level-dir enforcement as
        # boot-package.sh's zip branch — both call the shared helper.
        # Download and zip branches are mutually exclusive (this branch
        # only runs when --to-local-zip is set) so the helper's bare
        # EXIT trap doesn't clobber the download branch's
        # ARTIFACT_PATH cleanup.
        extract_zip_flattening_single_top_level_dir \
            "${ARTIFACT_PATH}" \
            "${TO_TARBALL_DIR}" \
            "openemr-acceptance-upgrade-zip.XXXXXX" \
            "--to-local-zip / zip extraction"
        ;;
    *)
        echo "::error::unexpected ARTIFACT_FORMAT '${ARTIFACT_FORMAT}' (expected 'tar' or 'zip')" >&2
        exit 1
        ;;
esac

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

if [[ "${skip_sql_upgrade}" == "true" ]]; then
    echo "==> Skipping sql_upgrade.php CLI (--skip-sql-upgrade set)"
    echo "==> Waiting for apache to serve /sql_upgrade.php (proxy for 'container up')"
    # No healthcheck wait: the compose openemr healthcheck asserts the
    # post-install login-page redirect target, which the pre-upgrade
    # state still meets (sqlconf.php $config=1 from the from-version
    # install), but the DB schema hasn't migrated. Instead poll
    # sql_upgrade.php directly — that's what the wizard test will hit.
    # Absolute 300s deadline via $SECONDS (each attempt is up to 10s
    # of curl + 5s of sleep = 15s worst case).
    deadline=$((SECONDS + 300))
    STATUS="not-yet-attempted"
    while (( SECONDS < deadline )); do
        STATUS="$(curl -sk --max-time 10 -o /dev/null -w '%{http_code}' "http://localhost:8680/sql_upgrade.php" || echo "curl-failed")"
        if [[ "${STATUS}" == "200" ]]; then
            echo "    apache serving /sql_upgrade.php (HTTP 200)"
            break
        fi
        sleep 5
    done
    if [[ "${STATUS}" != "200" ]]; then
        echo "::error::apache never served /sql_upgrade.php with HTTP 200 within 300s (last status: ${STATUS})" >&2
        exit 1
    fi

    echo ""
    echo "==> Upgrade prep complete (skip-sql-upgrade mode): ${TO_VERSION} filesystem via TARBALL_DIR, ${FROM_VERSION} sites/ + DB preserved, migrations NOT run"
    echo "    Artifact URL:  http://localhost:8680  (serving ${TO_VERSION} source, pre-migration ${FROM_VERSION} DB)"
    echo ""
    echo "    Run tests:     ACCEPTANCE_ARTIFACT_URL=http://localhost:8680 composer acceptance -- --group=wizard-upgrade"
    echo "    Teardown:      tests/Acceptance/bin/down-package.sh"
    exit 0
fi

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

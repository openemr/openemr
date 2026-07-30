#!/usr/bin/env bash
#
# Boot the acceptance-package stack against an openemr release tarball.
#
# Downloads the tarball from the GitHub release page, extracts it into
# a scratch directory, boots the compose stack (flex image + mariadb)
# with the extracted tree bind-mounted, then (by default) runs
# install-helper.php via `docker compose exec` to complete the install
# via OpenEMR's Installer class.
#
# Usage:
#   tests/Acceptance/bin/boot-package.sh [--skip-install-helper] \
#     [--local-tarball=<path> | --local-zip=<path>] <version>
#     --skip-install-helper (optional)
#         Skip the install-helper.php step. Leaves the artifact serving
#         setup.php on http://localhost:8680/, ready for the
#         InstallWizardUiTest (Phase 4c) to drive the wizard end-to-end
#         via Panther. Also skips the "wait for openemr healthy" gate
#         because that healthcheck asserts the post-install login-page
#         redirect target, which only appears AFTER a completed install;
#         instead, waits for /setup.php to return 200 (proves apache is
#         serving on the mapped port).
#     --local-tarball=<path> (optional)
#         Extract the given local .tar.gz instead of curl-ing the
#         GitHub release page. Enables Phase 3.5 PR-tarball validation:
#         the acceptance-package workflow builds the tarball via
#         PackageAssembler from the PR checkout, uploads it as a
#         workflow artifact, and downstream matrix jobs pass the
#         downloaded path here. Skips the network fetch entirely; the
#         `<version>` arg is still required (used for the scratch dir
#         name only in this mode).
#     --local-zip=<path> (optional)
#         Extract the given local .zip instead. Same semantics as
#         --local-tarball but for the ZIP artifact PackageAssembler
#         also produces alongside the tarball. Enables Phase 3.6 ZIP
#         acceptance coverage — the matrix's `format: zip` cells use
#         this to validate the shipped ZIP boots + installs correctly.
#         Mutually exclusive with --local-tarball.
#     version — release tag suffix, must match ^[0-9]+\.[0-9]+\.[0-9]+$
#               (e.g. 8.2.0). Anything else is rejected before any path
#               is constructed — VERSION is caller-controlled and flows
#               into `rm -rf` / `curl -o` paths.
#               Without --local-tarball / --local-zip: fetches
#               https://github.com/openemr/openemr/releases/download/v<X_Y_Z>/openemr-<version>.tar.gz
#
# After a successful boot (default mode):
#   Artifact URL:  http://localhost:8680
#   HTTPS URL:     https://localhost:8643  (self-signed cert)
#   Scratch dir:   /tmp/openemr-acceptance-<version>/  (bind-mount source)
#
# Run tests:      ACCEPTANCE_ARTIFACT_URL=http://localhost:8680 composer acceptance
# Tear down:      tests/Acceptance/bin/down-package.sh

set -euo pipefail

skip_install_helper="false"
local_tarball=""
local_zip=""
while [[ $# -gt 0 && "$1" == --* ]]; do
    case "$1" in
        --skip-install-helper)
            skip_install_helper="true"
            shift
            ;;
        --local-tarball=*)
            local_tarball="${1#--local-tarball=}"
            shift
            ;;
        --local-zip=*)
            local_zip="${1#--local-zip=}"
            shift
            ;;
        *)
            echo "::error::unknown flag: $1" >&2
            exit 2
            ;;
    esac
done

if [[ -n "${local_tarball}" && -n "${local_zip}" ]]; then
    echo "::error::--local-tarball and --local-zip are mutually exclusive" >&2
    exit 2
fi

if [[ $# -ne 1 ]]; then
    echo "Usage: $0 [--skip-install-helper] [--local-tarball=<path> | --local-zip=<path>] <version>" >&2
    echo "  e.g.: $0 8.2.0" >&2
    echo "        $0 --skip-install-helper 8.2.0" >&2
    echo "        $0 --local-tarball=/tmp/openemr-8.2.0.tar.gz 8.2.0" >&2
    echo "        $0 --local-zip=/tmp/openemr-8.2.0.zip 8.2.0" >&2
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

# shellcheck source=tests/Acceptance/bin/lib/extract-zip.sh
source "${SCRIPT_DIR}/lib/extract-zip.sh"

# Persist TARBALL_DIR + HELPER_PATH for every compose invocation in this
# script (compose reparses the file each time and would otherwise warn
# about the unset variable + create broken bind mounts).
export TARBALL_DIR="/tmp/openemr-acceptance-${VERSION}"
export HELPER_PATH="${SCRIPT_DIR}/install-helper.php"
export COMPOSE_FILE="${REPO_ROOT}/.github/docker/acceptance-package-compose.yml"
export COMPOSE_PROJECT_NAME="openemr-acceptance-package"

if [[ -n "${local_tarball}" ]]; then
    # Local-tarball mode: use the file the caller pointed us at, no
    # network fetch. Path is expected to be a caller-controlled
    # workflow-runner path (e.g. actions/download-artifact target); no
    # mktemp / symlink defenses apply because there's no attacker-
    # writable predictable path in play.
    if [[ ! -f "${local_tarball}" ]]; then
        echo "::error::--local-tarball path does not exist or is not a regular file: ${local_tarball}" >&2
        exit 2
    fi
    # Absolute-ize before the `cd "${REPO_ROOT}"` below — a caller who
    # passes a relative path (e.g. `--local-tarball=openemr-8.2.0.tar.gz`
    # from their own cwd) would otherwise see `tar` look for it under
    # REPO_ROOT and either extract the wrong file or fail. realpath
    # handles both leading-`./` and no-leading-`./` shapes uniformly.
    ARTIFACT_PATH="$(realpath "${local_tarball}")"
    ARTIFACT_FORMAT="tar"
    # No trap cleanup — the caller owns this file.
elif [[ -n "${local_zip}" ]]; then
    # Local-zip mode — same shape as local-tarball; the only difference
    # is the extractor further below (unzip vs tar -xzf). Phase 3.6
    # validation of the shipped ZIP artifact.
    if [[ ! -f "${local_zip}" ]]; then
        echo "::error::--local-zip path does not exist or is not a regular file: ${local_zip}" >&2
        exit 2
    fi
    ARTIFACT_PATH="$(realpath "${local_zip}")"
    ARTIFACT_FORMAT="zip"
    # No trap cleanup — the caller owns this file.
else
    # Random tarball path via mktemp — a predictable path like
    # /tmp/openemr-${VERSION}.tar.gz would let a local attacker
    # pre-create a symlink to redirect the curl download.
    ARTIFACT_PATH="$(mktemp -t "openemr-acceptance-tarball.XXXXXX.tar.gz")"
    ARTIFACT_FORMAT="tar"
    trap 'rm -f "${ARTIFACT_PATH}"' EXIT
fi

cd "${REPO_ROOT}"

echo "==> Preparing scratch dir at ${TARBALL_DIR}"
rm -rf "${TARBALL_DIR}"
mkdir -p "${TARBALL_DIR}"

if [[ -n "${local_tarball}" ]]; then
    echo "==> Using local tarball ${ARTIFACT_PATH} (--local-tarball set — skipping GitHub download)"
elif [[ -n "${local_zip}" ]]; then
    echo "==> Using local zip ${ARTIFACT_PATH} (--local-zip set — skipping GitHub download)"
else
    echo "==> Downloading ${TARBALL_URL}"
    curl -fsSL "${TARBALL_URL}" -o "${ARTIFACT_PATH}"
fi

echo "==> Extracting into ${TARBALL_DIR} (format=${ARTIFACT_FORMAT})"
case "${ARTIFACT_FORMAT}" in
    tar)
        # --strip-components=1 pulls contents up so ${TARBALL_DIR} matches
        # the openemr web-root layout the flex image's bind mount expects.
        tar -pxzf "${ARTIFACT_PATH}" -C "${TARBALL_DIR}" --strip-components=1
        ;;
    zip)
        # Delegate to the shared helper — same extract-into-scratch,
        # enforce-single-top-level-dir, mv-into-place dance that
        # upgrade-package.sh's zip branch also runs. See
        # lib/extract-zip.sh for the full rationale (unzip PATH guard,
        # single-top-level enforcement, trap-composition notes).
        extract_zip_flattening_single_top_level_dir \
            "${ARTIFACT_PATH}" \
            "${TARBALL_DIR}" \
            "openemr-acceptance-zip.XXXXXX" \
            "--local-zip / zip extraction"
        ;;
    *)
        # ARTIFACT_FORMAT is derived from the flag-parsing block above
        # (--local-tarball → tar, --local-zip → zip, download → tar) so
        # any other value is a programming error rather than user input.
        # Fail loud rather than silently continue with an empty web root.
        echo "::error::unexpected ARTIFACT_FORMAT '${ARTIFACT_FORMAT}' (expected 'tar' or 'zip')" >&2
        exit 1
        ;;
esac

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

if [[ "${skip_install_helper}" == "true" ]]; then
    echo "==> Skipping install-helper.php (--skip-install-helper set)"
    echo "==> Waiting for apache to serve /setup.php (proxy for 'container up')"
    # Can't wait on the compose openemr healthcheck here because that
    # check asserts the post-install login-page redirect target — a
    # freshly-extracted artifact serves /setup.php instead, and would
    # never pass the healthcheck. Poll setup.php directly instead.
    #
    # Require HTTP 200 explicitly (not curl -f which passes 3xx
    # redirects). Absolute 300s deadline via $SECONDS (each attempt is
    # up to 10s of curl + 5s of sleep = 15s worst case; a naive "60
    # attempts × sleep 5" loop would allow 900s wall-clock, not 300s
    # the diagnostic message advertises).
    deadline=$((SECONDS + 300))
    STATUS="not-yet-attempted"
    while (( SECONDS < deadline )); do
        STATUS="$(curl -sk --max-time 10 -o /dev/null -w '%{http_code}' "http://localhost:8680/setup.php" || echo "curl-failed")"
        if [[ "${STATUS}" == "200" ]]; then
            echo "    apache serving /setup.php (HTTP 200)"
            break
        fi
        sleep 5
    done
    if [[ "${STATUS}" != "200" ]]; then
        echo "::error::apache never served /setup.php with HTTP 200 within 300s (last status: ${STATUS})" >&2
        exit 1
    fi

    echo ""
    echo "==> Boot complete (skip-install-helper mode)."
    echo "    Artifact URL:  http://localhost:8680  (serving setup.php)"
    echo "    HTTPS URL:     https://localhost:8643 (self-signed cert)"
    echo ""
    echo "    Run tests:     ACCEPTANCE_ARTIFACT_URL=http://localhost:8680 composer acceptance -- --group=wizard-install"
    echo "    Teardown:      tests/Acceptance/bin/down-package.sh"
    exit 0
fi

echo "==> Running install-helper.php via docker compose exec (as apache user)"
# install-helper.php is mounted READ-ONLY at /opt/openemr-acceptance-helper.php
# by the compose file (OUTSIDE the openemr webroot, so Apache never
# serves it). RootCliGuard rejects UID 0 → wrap in `su -s /bin/sh apache`.
# OPENEMR_ENABLE_ACCEPTANCE_HELPER is the helper's opt-in env guard
# (same pattern InstallerAuto.php uses). su clears the environment, so
# the var is set inside the su-launched shell as a `VAR=x prog` prefix.
docker compose exec -T openemr \
    su -s /bin/sh apache -c \
    'OPENEMR_ENABLE_ACCEPTANCE_HELPER=1 php /opt/openemr-acceptance-helper.php'

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

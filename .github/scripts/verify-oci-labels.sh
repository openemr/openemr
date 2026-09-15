#!/usr/bin/env bash
#
# Verify OCI labels on a pushed OpenEMR docker image.
#
# Pulls REF_TAG from Docker Hub, then asserts that the image's
# org.opencontainers.image.{revision,version,created} labels match the
# expected values, and that the four static labels
# org.opencontainers.image.{title,source,url,licenses} are present and
# non-empty. Prints a success summary on green, ::error:: annotations on
# any mismatch, and exits non-zero on any failure.
#
# Called from .github/workflows/docker-build-release.yml in three places:
#   1. the build job's "gated path" step (Phase 7c-docker candidate tag)
#   2. the build job's "non-gated path" step (first tag of expanded list)
#   3. the publish job's post-imagetools-create step (first tag of final
#      expanded list, as defense-in-depth against imagetools pointing at
#      the wrong manifest)
#
# All three previously carried near-identical inline blocks. Extracted
# here so a fix to the verify logic (new label, tweaked comparison,
# richer error hints) lands once instead of three times.
#
# Inputs (env, all required)
#   REF_TAG               full image ref to pull + inspect
#                         (e.g. "openemr/openemr:8.1.0" or
#                          "openemr/openemr:release-candidate-12345-1")
#   EXPECTED_REVISION     value the org.opencontainers.image.revision
#                         label must equal (the OPENEMR_VERSION build
#                         arg -- git ref baked into the image)
#   EXPECTED_VERSION      value the org.opencontainers.image.version
#                         label must equal (the IMAGE_VERSION build arg
#                         -- human-facing version parsed from
#                         version.php at the resolved ref)
#   EXPECTED_BUILD_DATE   value the org.opencontainers.image.created
#                         label must equal (the BUILD_DATE build arg --
#                         YYYY-MM-DD of the build)
#
# Exit codes
#   0   all label checks passed
#   1   one or more label checks failed, OR REF_TAG couldn't be pulled

set -euo pipefail

: "${REF_TAG:?REF_TAG env var is required}"
: "${EXPECTED_REVISION:?EXPECTED_REVISION env var is required}"
: "${EXPECTED_VERSION:?EXPECTED_VERSION env var is required}"
: "${EXPECTED_BUILD_DATE:?EXPECTED_BUILD_DATE env var is required}"

docker pull "${REF_TAG}"

inspect_label() {
    # docker's Go-template `index` returns the literal string "<no value>"
    # when a label key is missing on newer Docker versions, instead of an
    # empty string. Normalize it back to empty so callers (both the
    # value-comparison and the presence-checks below) can rely on -z to
    # mean "label absent."
    local key="$1"
    local value
    value=$(docker inspect --format "{{ index .Config.Labels \"org.opencontainers.image.${key}\" }}" "${REF_TAG}")
    if [[ "${value}" == "<no value>" ]]; then
        echo ""
    else
        echo "${value}"
    fi
}

FAIL=0

# Value checks: the three labels that vary per build and have a
# computable expected value.
ACTUAL_REVISION=$(inspect_label revision)
if [[ "${ACTUAL_REVISION}" != "${EXPECTED_REVISION}" ]]; then
    echo "::error::revision label '${ACTUAL_REVISION}' doesn't match expected '${EXPECTED_REVISION}'"
    echo "  This means docker/release/Dockerfile either dropped the OPENEMR_VERSION ARG or hardcoded the LABEL value."
    FAIL=1
fi

ACTUAL_VERSION=$(inspect_label version)
if [[ "${ACTUAL_VERSION}" != "${EXPECTED_VERSION}" ]]; then
    echo "::error::version label '${ACTUAL_VERSION}' doesn't match expected '${EXPECTED_VERSION}' (parsed from openemr@${EXPECTED_REVISION}/version.php)"
    echo "  This means docker/release/Dockerfile either dropped the IMAGE_VERSION ARG or hardcoded the LABEL value."
    FAIL=1
fi

ACTUAL_CREATED=$(inspect_label created)
if [[ "${ACTUAL_CREATED}" != "${EXPECTED_BUILD_DATE}" ]]; then
    echo "::error::created label '${ACTUAL_CREATED}' doesn't match expected '${EXPECTED_BUILD_DATE}'"
    echo "  This means docker/release/Dockerfile either dropped the BUILD_DATE ARG or hardcoded the LABEL value."
    FAIL=1
fi

# Presence checks: the four static labels that don't vary per build.
# Catches the "someone truncated the Dockerfile and the whole LABEL
# block disappeared" failure mode that the value checks above wouldn't
# see if those three ARGs were also gone.
for STATIC_LABEL in title source url licenses; do
    VALUE=$(inspect_label "${STATIC_LABEL}")
    if [[ -z "${VALUE}" ]]; then
        echo "::error::Image is missing org.opencontainers.image.${STATIC_LABEL} label entirely"
        echo "  Check that docker/release/Dockerfile's LABEL block at the final stage is intact."
        FAIL=1
    else
        echo "✓ ${STATIC_LABEL}=${VALUE}"
    fi
done

[[ "${FAIL}" -eq 0 ]] || exit 1
echo "✓ revision=${ACTUAL_REVISION} version=${ACTUAL_VERSION} created=${ACTUAL_CREATED}"

#!/usr/bin/env bash
#
# Mock `docker` CLI for BATS tests of verify-oci-labels.sh.
#
# The mock replaces the real `docker` binary on PATH so the script under
# test never touches Docker Hub. Two subcommands are supported:
#
#   docker pull <ref>
#     If the fixture file $MOCK_DOCKER_LABELS_FILE exists, exits 0.
#     Otherwise exits 1 (simulates a pull failure -- no manifest available).
#     Prints nothing (the real docker CLI is chatty but the script doesn't
#     grep its output, so silence keeps test output clean).
#
#   docker inspect --format <template> <ref>
#     Reads label values from $MOCK_DOCKER_LABELS_FILE, one label per
#     line in "<key>=<value>" format. Only Go-template `index` on
#     .Config.Labels is supported (the shape the script uses):
#
#         {{ index .Config.Labels "org.opencontainers.image.<KEY>" }}
#
#     If the key is present in the fixture, echoes its value.
#     If the key is absent, echoes "<no value>" (matches the real
#     docker CLI's behavior for missing labels).
#
# Fixture file format ($MOCK_DOCKER_LABELS_FILE):
#   One label per line, "<KEY>=<VALUE>". KEY is the bit after
#   "org.opencontainers.image." (e.g. "revision", "version", "title").
#   Blank lines and #-prefixed lines are ignored. Setting a value to
#   the literal string ABSENT is equivalent to omitting the line
#   (the mock returns "<no value>").
#
#   Example:
#     revision=master
#     version=8.2.0
#     created=2026-01-15
#     title=OpenEMR
#     source=https://github.com/openemr/openemr
#     url=https://www.open-emr.org
#     licenses=GPL-3.0-or-later
#
# Controlled via env in the calling shell:
#   MOCK_DOCKER_LABELS_FILE   path to the fixture (required for inspect
#                             + required-for-success for pull)

set -euo pipefail

subcommand="${1:-}"
shift || true

case "${subcommand}" in
    pull)
        # docker pull <ref>
        if [[ -f "${MOCK_DOCKER_LABELS_FILE:-}" ]]; then
            exit 0
        fi
        echo "Error response from daemon: manifest not found (mock)" >&2
        exit 1
        ;;
    inspect)
        # docker inspect --format <template> <ref>
        # Real invocation:
        #   docker inspect --format '{{ index .Config.Labels "org.opencontainers.image.KEY" }}' <ref>
        format=""
        while [[ $# -gt 0 ]]; do
            case "$1" in
                --format)
                    format="$2"
                    shift 2
                    ;;
                --format=*)
                    format="${1#--format=}"
                    shift
                    ;;
                *)
                    # positional (the image ref) -- ignore, we don't validate it
                    shift
                    ;;
            esac
        done

        # Extract KEY from the Go template. Look for the last quoted string.
        # The template is always shaped as:
        #   {{ index .Config.Labels "org.opencontainers.image.<KEY>" }}
        key=""
        if [[ "${format}" =~ \"org\.opencontainers\.image\.([^\"]+)\" ]]; then
            key="${BASH_REMATCH[1]}"
        fi

        if [[ -z "${key}" ]]; then
            echo "docker-mock: unsupported format template: ${format}" >&2
            exit 2
        fi

        # Look up the key in the fixture file.
        if [[ ! -f "${MOCK_DOCKER_LABELS_FILE:-}" ]]; then
            echo "docker-mock: MOCK_DOCKER_LABELS_FILE not set or missing" >&2
            exit 2
        fi

        value=""
        found=0
        while IFS= read -r line || [[ -n "${line}" ]]; do
            # Skip comments + blanks.
            [[ -z "${line}" || "${line}" =~ ^[[:space:]]*# ]] && continue
            if [[ "${line}" == "${key}="* ]]; then
                value="${line#*=}"
                found=1
                break
            fi
        done < "${MOCK_DOCKER_LABELS_FILE}"

        if [[ "${found}" -eq 0 || "${value}" == "ABSENT" ]]; then
            # Real docker prints the literal string "<no value>" for
            # missing labels on newer Docker versions. The script under
            # test normalizes this back to empty in its inspect_label
            # helper; the mock's job is just to emit the raw string.
            echo "<no value>"
        else
            echo "${value}"
        fi
        ;;
    *)
        echo "docker-mock: unsupported subcommand: ${subcommand}" >&2
        exit 2
        ;;
esac

#!/usr/bin/env bash
#
# Mock `gh` for BATS tests of create-release-tag.sh.
#
# The script under test makes two gh calls:
#   1. gh release view <tag> --repo openemr/openemr    (idempotency
#                                                       check)
#   2. gh release create <tag> --repo openemr/openemr ...
#                                                      (only if 1
#                                                       returned
#                                                       non-zero)
#
# The mock routes on `gh release <subverb>`. Each invocation appends
# one line to $MOCK_CALL_LOG so tests can assert on the call sequence.
#
# Env-var contract:
#
#   MOCK_GH_RELEASE_VIEW_EXIT     exit code for `gh release view`. Real
#                                 gh returns 0 when the release exists
#                                 and 1 when it doesn't. Default 1
#                                 (release-doesn't-exist, so the script
#                                 creates it).
#   MOCK_GH_RELEASE_CREATE_EXIT   exit code for `gh release create`.
#                                 Default 0. Set non-zero to exercise
#                                 the "release create failed" branch.
#
# Other gh subcommands aren't used by the script under test; the mock
# fails loudly on unexpected verbs so a regression in the script that
# starts calling `gh api ...` (or anything else) shows up immediately.

set -euo pipefail

{
    printf 'gh'
    for a in "$@"; do
        printf ' %s' "${a}"
    done
    printf '\n'
} >> "${MOCK_CALL_LOG:-/dev/null}"

verb="${1:-}"
subverb="${2:-}"

if [[ "${verb}" != "release" ]]; then
    echo "gh-mock: unsupported verb: ${verb} (only 'release' is mocked)" >&2
    exit 2
fi

case "${subverb}" in
    view)
        exit "${MOCK_GH_RELEASE_VIEW_EXIT:-1}"
        ;;
    create)
        exit "${MOCK_GH_RELEASE_CREATE_EXIT:-0}"
        ;;
    *)
        echo "gh-mock: unsupported release subverb: ${subverb}" >&2
        exit 2
        ;;
esac

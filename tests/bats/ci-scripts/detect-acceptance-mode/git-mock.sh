#!/usr/bin/env bash
#
# Mock `git` CLI for BATS tests of detect-acceptance-mode.sh.
#
# The script under test invokes exactly one git subcommand:
#
#   git diff --name-only <BASE>..<HEAD>
#
# The mock emits the newline-separated file list from
# $MOCK_GIT_DIFF_OUTPUT (may be empty for the "no relevant change"
# case). Ignores <BASE>..<HEAD> — the test controls the file set
# directly, not the range.
#
# Any other git subcommand exits 0 silently. The script never calls
# git for anything else today; if it starts to, this mock will need
# a matching arm rather than the current silent no-op.
#
# Controlled via env in the calling shell:
#   MOCK_GIT_DIFF_OUTPUT   newline-separated file paths to emit for
#                          `git diff --name-only`. Unset or empty
#                          means no files (mimics a diff that
#                          touched nothing on the release surface).

set -euo pipefail

subcommand="${1:-}"
shift || true

case "${subcommand}" in
    diff)
        # Only the `--name-only <range>` shape is supported (the only
        # form the script uses). Ignore all args and emit the fixture.
        if [[ -n "${MOCK_GIT_DIFF_OUTPUT:-}" ]]; then
            printf '%s\n' "${MOCK_GIT_DIFF_OUTPUT}"
        fi
        ;;
    *)
        # No-op for any other subcommand; the script only uses `diff`.
        exit 0
        ;;
esac

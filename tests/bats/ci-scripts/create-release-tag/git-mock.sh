#!/usr/bin/env bash
#
# Mock `git` for BATS tests of create-release-tag.sh.
#
# The script under test makes the following git calls:
#   1. git config user.name ...                 (identity setup)
#   2. git config user.email ...                (identity setup)
#   3. git ls-remote --tags --exit-code origin refs/tags/<tag>
#                                               (remote tag check)
#   4. git tag -a -m "Release <tag>" <tag> <branch>
#                                               (create tag, only if 3
#                                               returned 2)
#   5. git push origin <tag>                    (push tag, only if 3
#                                               returned 2)
#
# The mock routes on subcommand. Each invocation appends one line to
# $MOCK_CALL_LOG so tests can assert which subcommands ran (and infer
# from that whether the script correctly skipped a step under an
# idempotent-recovery scenario).
#
# Env-var contract (defaults chosen for the happy-path scenario):
#
#   MOCK_LS_REMOTE_EXIT       exit code for the ls-remote call. Real
#                             git returns 0 when the tag exists on
#                             origin, 2 when it doesn't; anything else
#                             is a lookup failure. Default 2.
#   MOCK_LS_REMOTE_STDOUT     text emitted on stdout for ls-remote
#                             (mirrors the "<sha>\trefs/tags/<tag>"
#                             lines real git prints). Default empty.
#   MOCK_GIT_PUSH_EXIT        exit code for git push. Default 0.
#
# `git config` and `git tag` are always logged but always succeed --
# the script's own error handling covers real failures on those calls
# via `set -e`, and the tests don't need to exercise `git config`
# failure modes (that's a git-internals concern, not the script's).

set -euo pipefail

# Log the whole argv on one line so tests can substring-match. Prefix
# with `git ` so a diagnostic dump reads naturally.
{
    printf 'git'
    for a in "$@"; do
        printf ' %s' "${a}"
    done
    printf '\n'
} >> "${MOCK_CALL_LOG:-/dev/null}"

# Skip real git's `-c KEY=VALUE` prefix args (e.g.,
# `-c http.https://github.com/.extraheader=Authorization: Basic ...`)
# before locating the subcommand — matches real git's own parsing.
# Needed for the APP_TOKEN inline-auth path (create-release-tag.sh
# uses `git -c http.extraheader=... ls-remote` and `git -c ... push`
# when APP_TOKEN is set).
while [[ "${1:-}" == "-c" ]]; do
    shift  # drop -c
    shift  # drop KEY=VALUE
done

subcommand="${1:-}"
shift || true

case "${subcommand}" in
    config)
        # Always succeed.
        exit 0
        ;;
    ls-remote)
        # Emit the fixture stdout (empty by default). Real git writes
        # the "<sha>\trefs/tags/<tag>" lines here when the tag exists;
        # the script under test redirects stdout to /dev/null and
        # keys off the exit code, so the content isn't load-bearing,
        # but the stdout stream is populated for symmetry.
        printf '%s' "${MOCK_LS_REMOTE_STDOUT:-}"
        exit "${MOCK_LS_REMOTE_EXIT:-2}"
        ;;
    tag)
        # Always succeed. `git tag -a -m "..." <tag> <branch>` doesn't
        # write to stdout in the success case.
        exit 0
        ;;
    push)
        exit "${MOCK_GIT_PUSH_EXIT:-0}"
        ;;
    *)
        echo "git-mock: unsupported subcommand: ${subcommand}" >&2
        exit 2
        ;;
esac

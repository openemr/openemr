#!/usr/bin/env bash
#
# Decide whether the docker acceptance workflow's upgrade scenario cell
# should skip its downstream test-running steps for the current
# from-tag/to-tag pairing. Extracted from acceptance-docker.yml's
# inline logic so BATS tests can pin the decision behavior without
# needing a live docker daemon or matrix run.
#
# The docker upgrade cell is only meaningful when both from_tag and
# to_tag are shipped/rel-branch builds AND the target has the
# docker-upgrade infrastructure (fsupgrade-N + docker-version bump)
# wired for the source. When it's not, post-upgrade DB stays at from's
# version while code advances to to's version, and every downstream
# assertion (version-check, business, api-enabled) becomes unreliable.
# Even the boot's login-page healthcheck can fail because login
# requires sql_upgrade to have run.
#
# Two skip criteria (either fires -> skip):
#
#   1. from_tag's OCI revision == master -- from IS master's build,
#      no higher shipped version exists to upgrade TO. Same-or-lower
#      target is either a no-op or an unsupported downgrade.
#
#   2. to_tag's OCI revision == master AND master carries the `next`
#      docker tag in release-targets.yml (between-cycles state, no
#      rel branch in active dev cycle). Master's docker-upgrade
#      infrastructure hasn't been scaffolded to walk from currently-
#      shipped versions to master's current version.php in this
#      window. When `next` is on a rel-XXX branch instead (that
#      branch's active dev cycle), the release-cut / patch-prep
#      mutators cross-propagate the fsupgrade + docker-version bumps
#      to BOTH the rel branch AND master -- master's dev docker DOES
#      have upgrade infra in that window.
#
# Inputs (env, all required except RELEASE_TARGETS_PATH):
#
#   FROM_REV               OCI revision label of the from-tag image
#                          (e.g., 'master', 'v8_4_1', 'rel-840').
#                          Empty string treated as not-master
#                          (unrecognizable revision doesn't trigger
#                          skip -- lets the cell run so an actual
#                          failure surfaces).
#   TO_REV                 OCI revision label of the to-tag image.
#                          Same shape as FROM_REV.
#   FROM_TAG               Docker tag name for from side (e.g.,
#                          'latest', '8.4.1'). Used in skip-reason
#                          output only; no logic depends on it.
#   TO_TAG                 Docker tag name for to side (e.g., 'next',
#                          'pr-built'). Used in skip-reason output.
#   RELEASE_TARGETS_PATH   (optional) Path to release-targets.yml.
#                          Defaults to '.github/release-targets.yml'
#                          relative to cwd.
#
# Outputs (written to $GITHUB_OUTPUT, or stdout if GITHUB_OUTPUT unset):
#
#   skip=true|false
#   skip_reason=<multi-line string>   (only set when skip=true; uses
#                                      REASON_EOF heredoc pattern for
#                                      newline-safe multi-line values
#                                      per GH Actions convention)
#
# Exit codes:
#
#   0  Decision emitted (regardless of skip=true or skip=false).
#   1  Called with a bad shape (missing required env, unreadable
#      release-targets.yml when needed).
#
# Unit-tested by tests/bats/ci-scripts/detect-upgrade-cell-skip/.

set -euo pipefail

# Required-env guard. Not using assert-inputs-nonempty.sh here because
# empty FROM_REV / TO_REV are LEGITIMATE inputs (unrecognizable image
# = don't skip = "let the cell run"), whereas empty FROM_TAG / TO_TAG
# indicate the caller forgot to plumb them and skip_reason would be
# malformed. Explicit check for FROM_TAG / TO_TAG only.
if [[ -z "${FROM_TAG:-}" ]] || [[ -z "${TO_TAG:-}" ]]; then
    echo "::error::detect-upgrade-cell-skip.sh: FROM_TAG and TO_TAG env must both be non-empty" >&2
    exit 1
fi
# FROM_REV / TO_REV must be SET (even if empty). Detect unset vs empty
# via the -v test rather than -z (which treats unset as empty).
if [[ ! -v FROM_REV ]] || [[ ! -v TO_REV ]]; then
    echo "::error::detect-upgrade-cell-skip.sh: FROM_REV and TO_REV env must both be set (empty string OK for unrecognizable revision)" >&2
    exit 1
fi

RELEASE_TARGETS_PATH="${RELEASE_TARGETS_PATH:-.github/release-targets.yml}"

# Where to emit outputs. $GITHUB_OUTPUT is the GH Actions convention;
# fall back to stdout for local BATS invocations so tests can capture
# the emitted key=value pairs from `run` output.
emit() {
    if [[ -n "${GITHUB_OUTPUT:-}" ]]; then
        printf '%s\n' "$1" >> "${GITHUB_OUTPUT}"
    else
        printf '%s\n' "$1"
    fi
}

# Multi-line output writer using GH Actions' REASON_EOF heredoc
# pattern. Necessary because skip_reason contains newlines (readable
# multi-paragraph explanation of the skip cause).
emit_multiline() {
    local name="$1"
    local value="$2"
    if [[ -n "${GITHUB_OUTPUT:-}" ]]; then
        {
            printf '%s<<REASON_EOF\n' "${name}"
            printf '%s\n' "${value}"
            printf 'REASON_EOF\n'
        } >> "${GITHUB_OUTPUT}"
    else
        # Local BATS output shape: single key=value line with newlines
        # escaped as \n, so `run` output can be substring-matched.
        # Tests that need to inspect the exact value do so via the
        # substring-match on the readable content, not the escaping.
        printf '%s=' "${name}"
        # POSIX sed newline escape -- replace \n with literal \n.
        printf '%s' "${value}" | sed -z 's/\n/\\n/g'
        printf '\n'
    fi
}

echo "==> from_tag (${FROM_TAG}) revision: ${FROM_REV}"
echo "==> to_tag   (${TO_TAG}) revision: ${TO_REV}"

# Criterion 1: from is master's build -- no higher shipped version
# exists to upgrade TO. Any to_tag would be same-or-lower version.
if [[ "${FROM_REV}" == "master" ]]; then
    emit "skip=true"
    emit_multiline "skip_reason" "from_tag (${FROM_TAG}) has OCI revision=master -- from IS master's build, no higher version exists to upgrade TO. Cell would exercise a same-or-lower version transition which the docker upgrade mechanism doesn't support."
    exit 0
fi

# Criterion 2 gate: only applies when to is master's build.
if [[ "${TO_REV}" != "master" ]]; then
    emit "skip=false"
    echo "==> to_tag is a shipped or rel-branch build (revision=${TO_REV}) -- auto-upgrade infra present, cell will run"
    exit 0
fi

# Criterion 2 body: to_tag revision=master. Check whether master row
# in release-targets.yml carries the `next` docker tag. When master
# has next, master is between-cycles (no rel branch in active dev
# cycle) and lacks the upgrade infra for arbitrary source versions.
if [[ ! -r "${RELEASE_TARGETS_PATH}" ]]; then
    echo "::error::detect-upgrade-cell-skip.sh: cannot read RELEASE_TARGETS_PATH='${RELEASE_TARGETS_PATH}' -- needed to check master's docker_tags" >&2
    exit 1
fi

# awk finds `- branch: master`, then emits the docker_tags line that
# follows (the row's other fields don't matter). grep matches `next`
# as a word (surrounded by start/end of string, comma, or space) so
# we don't false-match on `next-something` or `nothing-next`.
if awk '/^- branch: master$/{f=1} f && /^  docker_tags:/{print; exit}' "${RELEASE_TARGETS_PATH}" | grep -qE '(^|,| )next(,| |$)'; then
    emit "skip=true"
    emit_multiline "skip_reason" "to_tag (${TO_TAG}) has OCI revision=master AND master carries the \`next\` docker tag in release-targets.yml -- indicates between-cycles state (no rel branch in active dev cycle). Master's docker-upgrade infrastructure (fsupgrade-N + docker-version bump) hasn't been scaffolded to walk from currently-shipped versions to master's current version.php in this window. Post-upgrade DB will not advance -> code-vs-DB mismatch -> every downstream assertion unreliable."
else
    emit "skip=false"
    echo "==> to_tag revision=master but master does NOT carry \`next\` tag -- indicates rel-XXX branch is in active dev cycle. Release-cut / patch-prep mutators cross-propagated upgrade infra to master; cell will run."
fi

#!/usr/bin/env bash
#
# Resolve the acceptance-package.yml matrix inputs (build_locally +
# to_version) from the workflow's trigger event.
#
# Extracted from .github/workflows/acceptance-package.yml's `detect-mode`
# job (Phase 10e-4). The workflow's step still owns the `env:` block that
# maps github.* + inputs.* into the env vars this script reads; the
# script owns the branching + output-emission logic. Every echo /
# ::error:: line is preserved verbatim from the pre-extraction inline
# block — operators and downstream steps grep on these strings.
#
# Divergence from acceptance-docker.yml's detect-mode job (~44 lines,
# still inline as of Phase 10e-4): the docker variant has no workflow_call
# gate (candidate images are pushed to Docker Hub before the caller
# invokes it), no release-prep detection, no emit_to_version helper
# (no to_version output at all), and diffs `docker/release/**` instead
# of `tools/release/` + build.xml + .gitattributes. Different callers,
# different concerns — extracting both into one shared script would
# require plumbing every divergent knob through env, which loses more
# than it saves. Could be a separate slice later if the two variants
# converge.
#
# Inputs (env, all set by acceptance-package.yml's `env:` block on the
# calling step; every one is always defined by GitHub Actions even when
# the underlying context value renders as an empty string, so no
# `${VAR:?}` guards)
#   EVENT_NAME               github.event_name
#   DISPATCH_BUILD_LOCALLY   inputs.build_locally (workflow_dispatch)
#   DISPATCH_TO_VERSION      inputs.to_version    (workflow_dispatch /
#                            workflow_call)
#   CALLER_TARBALL_ARTIFACT  inputs.caller_tarball_artifact
#                            (workflow_call gate)
#   CALLER_ZIP_ARTIFACT      inputs.caller_zip_artifact
#                            (workflow_call gate)
#   PR_BASE_SHA              github.event.pull_request.base.sha
#   PR_HEAD_SHA              github.event.pull_request.head.sha
#   PR_HEAD_REF              github.event.pull_request.head.ref
#   PR_TITLE                 github.event.pull_request.title
#   PUSH_BEFORE_SHA          github.event.before
#   PUSH_HEAD_SHA            github.sha
#   PUSH_REF                 github.ref
#   GITHUB_OUTPUT            standard GHA runner-provided path
#
# Outputs (written to $GITHUB_OUTPUT)
#   build_locally=<true|false>
#   to_version=<X.Y.Z>
#
# Exit codes
#   0   Decision emitted successfully.
#   1   Invalid input (workflow_call gate half-set, missing to_version
#       on workflow_call, or resolved to_version not in X.Y.Z shape).

# shellcheck disable=SC2154
# All env vars listed in the header are set by the caller workflow's
# `env:` block on the calling step — GitHub Actions always defines each
# expression (may render as empty). Shellcheck's "referenced but not
# assigned" check can't see the workflow-side origin.

set -euo pipefail

# emit_to_version <build_locally> [<preferred_version>] —
# resolves the to_version from dispatch input first, then the
# optional preferred value (e.g. the version parsed from a
# release-prep PR title), then falls back based on build_locally
# state. Validates the shape either way so a crafted
# workflow_dispatch input can't inject shell metacharacters
# into downstream `run:` steps that splice this value.
emit_to_version() {
    local build_locally="$1"
    local preferred="${2:-}"
    local candidate
    if [[ -n "${DISPATCH_TO_VERSION}" ]]; then
        candidate="${DISPATCH_TO_VERSION}"
    elif [[ -n "${preferred}" ]]; then
        candidate="${preferred}"
    elif [[ "${build_locally}" == "true" ]]; then
        candidate="99.99.99"
    else
        candidate="8.2.0"
    fi
    if [[ ! "${candidate}" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        echo "::error::resolved to_version '${candidate}' does not match required X.Y.Z"
        exit 1
    fi
    echo "to_version=${candidate}" >> "${GITHUB_OUTPUT}"
    echo "==> resolved to_version=${candidate}"
}

# Phase 7c workflow_call gate — caller (build-release.yml)
# already has the tarball (+ Phase 3.6 ZIP) built and uploaded
# as an artifact. Skip the diff-detection dance entirely; treat
# as build_locally=true with the caller-supplied version. The
# build-tarball job self-skips (see its `if:` condition below)
# and the bootflags step downloads the caller's artifact name
# instead of Phase 3.5's `openemr-pr-built-tarball` /
# `openemr-pr-built-zip` defaults.
#
# Detect on CALLER_TARBALL_ARTIFACT / CALLER_ZIP_ARTIFACT being
# non-empty, NOT on $EVENT_NAME == 'workflow_call' — GitHub
# Actions reports the original event that started the CALLING
# workflow in a called workflow's github.event_name (so
# workflow_dispatch of build-release.yml surfaces as
# workflow_dispatch here, not workflow_call).
#
# Enforce both-or-neither on the caller_*_artifact pair. If the
# caller passes only one (say tarball), the other-format matrix
# cells (zip) would either fail their download step or silently
# skip — masking a real integration mistake at the caller side.
# build-release.yml correctly passes both today (single bundle
# contains both formats), so requiring symmetric inputs costs
# nothing at the intended caller and catches misconfiguration
# in any future caller cleanly.
if [[ -n "${CALLER_TARBALL_ARTIFACT}" || -n "${CALLER_ZIP_ARTIFACT}" ]]; then
    if [[ -z "${CALLER_TARBALL_ARTIFACT}" || -z "${CALLER_ZIP_ARTIFACT}" ]]; then
        echo "::error::workflow_call requires BOTH caller_tarball_artifact AND caller_zip_artifact to be set (or neither). Got tarball='${CALLER_TARBALL_ARTIFACT:-<empty>}', zip='${CALLER_ZIP_ARTIFACT:-<empty>}'."
        exit 1
    fi
    if [[ -z "${DISPATCH_TO_VERSION}" ]]; then
        echo "::error::workflow_call with caller_tarball_artifact/caller_zip_artifact requires to_version to be set explicitly"
        exit 1
    fi
    echo "==> workflow_call gate mode (tarball=${CALLER_TARBALL_ARTIFACT}, zip=${CALLER_ZIP_ARTIFACT}): forcing build_locally=true"
    echo "build_locally=true" >> "${GITHUB_OUTPUT}"
    emit_to_version "true"
    exit 0
fi

# release-prep detection — the release-prep.yml conductor opens
# a long-lived PR from release-prep/<rel-branch> INTO its
# rel-branch (base=rel-820, head=release-prep/rel-820, title
# like `chore(release): prep 8.2.1`). When this workflow fires
# on such a branch, force build_locally=true regardless of
# what files the PR happens to touch — the whole point of the
# release-prep PR is validating the tarball this release would
# ship.
#
# NOTE on how the workflow actually gets triggered on
# release-prep PRs: the existing paths filter above doesn't
# match a typical release-prep PR (which only touches
# `version.php` + `docker/production/docker-compose.yml`), and
# adding either of those to the paths filter would over-fire
# on unrelated version bumps and docker-compose edits. The
# intended trigger is release-prep.yml explicitly dispatching
# this workflow (via `gh workflow run acceptance-package.yml
# --ref release-prep/<branch> -f build_locally=true`) after
# it opens or updates the release-prep PR — that couples the
# trigger to the release-prep concern itself rather than to
# incidental file paths. That wiring is a follow-up PR; until
# it lands, this block still catches manual workflow_dispatch
# against a release-prep branch and any PR that happens to
# touch a paths-list file (e.g. a tools/release change piggy-
# backing on the release-prep PR).
#
# Detected on both pull_request (head_ref matches) AND push
# (branch matches) so the check fires however the release-prep
# branch is currently being exercised.
HEAD_REF=""
case "${EVENT_NAME}" in
    pull_request) HEAD_REF="${PR_HEAD_REF}" ;;
    push)         HEAD_REF="${PUSH_REF#refs/heads/}" ;;
    *)            HEAD_REF="" ;;
esac
if [[ "${HEAD_REF}" == release-prep/* ]]; then
    echo "==> release-prep branch detected (${HEAD_REF}): forcing build_locally=true"
    echo "build_locally=true" >> "${GITHUB_OUTPUT}"
    # Parse the release version out of the PR title
    # (`chore(release): prep 8.2.1${suffix}`) so downstream logs
    # and artifact filenames reflect the actual release being
    # validated rather than the 99.99.99 synthetic default.
    # to_version only affects log messages + artifact filename +
    # upgrade-package.sh's from/to comparison guards; sql_upgrade.php
    # reads --from=FROM_VERSION and derives the target from the
    # DB `version` table populated by the from-install, NOT from
    # the to side. So the real version is a cosmetic upgrade
    # over 99.99.99, not a correctness requirement.
    preferred=""
    if [[ -n "${PR_TITLE}" && "${PR_TITLE}" =~ prep[[:space:]]+([0-9]+\.[0-9]+\.[0-9]+) ]]; then
        preferred="${BASH_REMATCH[1]}"
        echo "==> parsed release version from PR title: ${preferred}"
    fi
    emit_to_version "true" "${preferred}"
    exit 0
fi

# Manual dispatch always wins — honor the input.
if [[ "${EVENT_NAME}" == "workflow_dispatch" ]]; then
    echo "build_locally=${DISPATCH_BUILD_LOCALLY}" >> "${GITHUB_OUTPUT}"
    echo "==> dispatch mode: build_locally=${DISPATCH_BUILD_LOCALLY}"
    emit_to_version "${DISPATCH_BUILD_LOCALLY}"
    exit 0
fi
# Resolve the diff range for push/pull_request.
case "${EVENT_NAME}" in
    pull_request) BASE="${PR_BASE_SHA}"; HEAD="${PR_HEAD_SHA}" ;;
    push)         BASE="${PUSH_BEFORE_SHA}"; HEAD="${PUSH_HEAD_SHA}" ;;
    *)
        echo "==> ${EVENT_NAME} event: defaulting build_locally=false"
        echo "build_locally=false" >> "${GITHUB_OUTPUT}"
        emit_to_version "false"
        exit 0
        ;;
esac
# Branch creation on push emits before=000...; can't diff.
if [[ -z "${BASE}" || "${BASE}" == "0000000000000000000000000000000000000000" ]]; then
    echo "==> branch-creation event (no base ref): defaulting build_locally=false"
    echo "build_locally=false" >> "${GITHUB_OUTPUT}"
    emit_to_version "false"
    exit 0
fi
# Verify BASE is reachable in the local clone. If the branch was
# force-pushed or regenerated (bot-authored sync PRs like master->rel-*,
# rebase-and-push, etc.) the push event's before-SHA can point at an
# orphaned commit no longer in any ref. actions/checkout fetch-depth: 0
# clones all refs but not orphaned commits (those live only in the
# server-side reflog). Try to fetch the specific SHA — GitHub retains
# force-pushed commits for ~90 days and supports fetch-by-SHA — and if
# it still can't be resolved, treat as branch-creation-style and default
# to build_locally=false. Preserves fail-loud on genuinely broken git
# state (see git diff error handling below) while gracefully handling
# the force-push-orphaned-base case that was blocking legitimate reruns.
if ! git cat-file -e "${BASE}^{commit}" 2>/dev/null; then
    echo "==> BASE ${BASE} not present locally; attempting server-side fetch-by-SHA"
    # Capture stderr so a failed fetch (auth, transient network, unknown
    # SHA) surfaces in the fall-back log below rather than being silently
    # masked. Not retried or fatal — we still want to unblock legitimate
    # force-pushed reruns — but operators reading the log can distinguish
    # "reflog GC" from "auth failure" without spelunking through the run.
    fetch_stderr=$(git fetch --depth=1 --no-tags origin "${BASE}" 2>&1 >/dev/null) || true
    if ! git cat-file -e "${BASE}^{commit}" 2>/dev/null; then
        echo "==> BASE ${BASE} still not reachable after fetch attempt: defaulting build_locally=false"
        echo "==> (may indicate: force-push + reflog GC, auth failure, or transient network — see fetch output below)"
        if [[ -n "${fetch_stderr}" ]]; then
            awk '{ print "==>   fetch: " $0 }' <<< "${fetch_stderr}"
        fi
        echo "build_locally=false" >> "${GITHUB_OUTPUT}"
        emit_to_version "false"
        exit 0
    fi
fi
# Trigger the local-build path when the PR touches anything
# PackageAssembler consumes at build time: the assembler code
# itself, the phing buildfile that owns the prune target list,
# or the .gitattributes export-ignore rules that gate what
# ships. tools/release/** is the primary surface.
#
# Capture git diff separately from the grep so a git-side failure
# (unresolvable range after a force-push + reflog GC, etc.) exits
# loudly. Under set -euo pipefail the pipeline's exit status is the
# rightmost non-zero; without this split, git diff's failure would
# be masked by grep's exit-1-for-no-match and silently reported as
# "no relevant change" -> build_locally=false, wrong-artifact
# validation with no ::error:: line.
changed_files=$(git diff --name-only "${BASE}".."${HEAD}") || {
    echo "::error::git diff failed for range ${BASE}..${HEAD}"
    exit 1
}
if grep -qE '^(tools/release/|build\.xml$|\.gitattributes$)' <<< "${changed_files}"; then
    echo "==> tarball-build surface changed between ${BASE} and ${HEAD}: build_locally=true"
    echo "build_locally=true" >> "${GITHUB_OUTPUT}"
    emit_to_version "true"
else
    echo "==> tarball-build surface unchanged between ${BASE} and ${HEAD}: build_locally=false"
    echo "build_locally=false" >> "${GITHUB_OUTPUT}"
    emit_to_version "false"
fi

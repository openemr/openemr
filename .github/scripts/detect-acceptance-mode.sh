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
#   DISPATCH_FROM_VERSION    inputs.from_version  (workflow_dispatch /
#                            workflow_call) — empty triggers
#                            derive-from-checkout via sql/*.sql files
#                            cross-referenced against the shipped-
#                            versions manifest (see fetch_shipped_versions)
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
#   from_version=<X.Y.Z>
#   expected_version=<X.Y.Z>   version the running artifact will
#                              self-report post-install/post-upgrade.
#                              Equals to_version on the shipped-tarball
#                              path (label = actual). On the build_locally
#                              path the to_version label is cosmetic
#                              (99.99.99 default; PackageAssembler does
#                              not bake --release-version into
#                              version.php), so the label cannot equal
#                              the value sql_upgrade.php writes to the
#                              DB. expected_version reads the checkout's
#                              version.php in that case so the
#                              acceptance version-display / version-api
#                              groups (ACCEPTANCE_EXPECTED_VERSION) can
#                              compare against something that actually
#                              matches. See openemr/openemr#13753.
#
# Exit codes
#   0   Decision emitted successfully.
#   1   Invalid input (workflow_call gate half-set, missing to_version
#       on workflow_call, resolved to_version/from_version not in
#       X.Y.Z shape, no sql/*-to-*_upgrade.sql files found for
#       from_version derivation, releases manifest fetch/parse failed,
#       or no derivation candidate matched the shipped-versions manifest).

# shellcheck disable=SC2154
# All env vars listed in the header are set by the caller workflow's
# `env:` block on the calling step — GitHub Actions always defines each
# expression (may render as empty). Shellcheck's "referenced but not
# assigned" check can't see the workflow-side origin.

set -euo pipefail

# Last to_version resolved by emit_to_version. emit_expected_version
# reads this on the non-build_locally path so the "expected" value
# tracks the same label the acceptance job's TO_VERSION env sees.
LAST_RESOLVED_TO_VERSION=""

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
    LAST_RESOLVED_TO_VERSION="${candidate}"
    echo "to_version=${candidate}" >> "${GITHUB_OUTPUT}"
    echo "==> resolved to_version=${candidate}"
}

# read_tree_version — extracts the shipped self-reported version from
# the checkout's `version.php` (repo root). Emits X.Y.Z on stdout;
# exits 1 with an ::error:: line if the file is missing or any of the
# three components can't be parsed.
#
# PackageAssembler ships version.php as-is from the checked-out ref
# (documented in tools/release/bin/package-assemble.php), so on the
# build_locally acceptance path this is what the DB `version` table
# will hold post-install / post-upgrade. Used only by
# emit_expected_version — see its docstring for the full rationale.
#
# Uses awk (not PHP): the detect-mode job runs on ubuntu-24.04 without
# an apt install of php, and the parse is trivial.
read_tree_version() {
    local file="version.php"
    if [[ ! -r "${file}" ]]; then
        # `|| true` on the pwd capture to satisfy SC2312 — pwd can't
        # fail meaningfully here, but the linter can't prove that.
        local cwd
        cwd=$(pwd || true)
        echo "::error::read_tree_version: cannot read ${file} from ${cwd}" >&2
        return 1
    fi
    local major minor patch
    # Each line looks like: $v_major = '8';
    # Match: $v_(major|minor|patch)  =  '<digits>' ;
    major=$(awk -F"'" '/^\$v_major *=/ { print $2; exit }' "${file}")
    minor=$(awk -F"'" '/^\$v_minor *=/ { print $2; exit }' "${file}")
    patch=$(awk -F"'" '/^\$v_patch *=/ { print $2; exit }' "${file}")
    if [[ -z "${major}" || -z "${minor}" || -z "${patch}" ]]; then
        echo "::error::read_tree_version: failed to parse \$v_major/\$v_minor/\$v_patch from ${file} (got major='${major}' minor='${minor}' patch='${patch}')" >&2
        return 1
    fi
    printf '%s.%s.%s' "${major}" "${minor}" "${patch}"
}

# emit_expected_version <build_locally> —
# resolves the version the running artifact will actually self-report
# post-install/post-upgrade. On the non-build_locally path this equals
# the just-emitted to_version (label = actual, since a shipped tarball
# has --release-version baked in). On build_locally=true the label is
# cosmetic (see the emit_to_version 99.99.99 default) — the shipped
# self-reported version is whatever version.php in the checkout says,
# which is what sql_upgrade.php writes into the DB `version` table.
# Two mutually-exclusive assumptions used to collide here:
#   - PackageAssembler docs `--release-version` as "naming label only,
#     not baked in" (staging dir + tarball filename)
#   - #13635 added assertions asserting the label equals the DB value
# Which meant build_locally acceptance runs could never pass the
# post-install/post-upgrade version-display / version-api groups.
# See openemr/openemr#13753 for the failure trace + rationale.
#
# Must be called AFTER emit_to_version so LAST_RESOLVED_TO_VERSION
# is populated for the else branch. Shape-validates the result for
# the same defense-in-depth reason as emit_to_version.
emit_expected_version() {
    local build_locally="$1"
    local candidate
    if [[ "${build_locally}" == "true" ]]; then
        # Same SC2310 pattern used at emit_from_version's
        # derive_from_version call — read_tree_version's failure modes
        # are explicit `return 1` after emitting an ::error:: line, so
        # the disabled `set -e` inside the function doesn't mask
        # anything the `if !` guard doesn't already catch.
        # shellcheck disable=SC2310
        if ! candidate=$(read_tree_version); then
            exit 1
        fi
    else
        candidate="${LAST_RESOLVED_TO_VERSION}"
    fi
    if [[ ! "${candidate}" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        echo "::error::resolved expected_version '${candidate}' does not match required X.Y.Z" >&2
        exit 1
    fi
    echo "expected_version=${candidate}" >> "${GITHUB_OUTPUT}"
    echo "==> resolved expected_version=${candidate}"
}

# SHIPPED_VERSIONS_MANIFEST_URL — canonical location of the openemr
# release manifest. Written by three dispatch events from
# openemr/openemr (openemr-rel-cut → DRAFT, openemr-tag → flip to
# FINAL). Read here as the authoritative "which versions have
# actually shipped tarballs" signal. See
# tools/release/src/BranchVersionResolver.php in this repo for the
# other CI consumer.
#
# `:=` default so tests can override with an unreachable URL for
# fetch-failure coverage. Not readonly for the same reason.
: "${SHIPPED_VERSIONS_MANIFEST_URL:=https://raw.githubusercontent.com/openemr/website-openemr/master/data/releases.json}"

# fetch_shipped_versions — pulls the openemr/website-openemr
# releases manifest and returns the FINAL-status version keys,
# newline-separated. Filtered to FINAL because DRAFT entries mean
# "release-prep dispatched but not yet shipped" — the tarball
# isn't downloadable from GitHub Releases until the openemr-tag
# dispatch flips status to FINAL.
#
# MOCK_SHIPPED_VERSIONS env override — when set, bypasses the real
# fetch and emits its content verbatim. BATS uses this to keep the
# tests offline (mirrors the MOCK_GIT_DIFF_OUTPUT pattern). The
# sentinel value `__FAIL__` simulates a fetch failure (mirrors the
# MOCK_GIT_DIFF_EXIT pattern) — tests use it to cover the
# error-surface branch.
fetch_shipped_versions() {
    if [[ "${MOCK_SHIPPED_VERSIONS:-}" == "__FAIL__" ]]; then
        echo "::error::fetch_shipped_versions: (mocked) simulated fetch failure" >&2
        return 1
    fi
    if [[ -n "${MOCK_SHIPPED_VERSIONS:-}" ]]; then
        printf '%s' "${MOCK_SHIPPED_VERSIONS}"
        return 0
    fi
    local manifest
    if ! manifest=$(curl -fsSL --retry 3 --retry-delay 2 --max-time 30 "${SHIPPED_VERSIONS_MANIFEST_URL}" 2>&1); then
        echo "::error::fetch_shipped_versions: curl failed for ${SHIPPED_VERSIONS_MANIFEST_URL}: ${manifest}" >&2
        return 1
    fi
    local final_versions
    if ! final_versions=$(jq -re 'to_entries | .[] | select(.value.status == "FINAL") | .key' <<< "${manifest}"); then
        echo "::error::fetch_shipped_versions: failed to parse releases.json as JSON (fetched ${#manifest} bytes from ${SHIPPED_VERSIONS_MANIFEST_URL})" >&2
        return 1
    fi
    printf '%s' "${final_versions}"
}

# derive_from_version — picks the latest "from-version" from the
# checkout's `sql/*-to-*_upgrade.sql` filenames, intersected with
# the shipped-versions manifest. Each filename encodes an upgrade
# hop (e.g. `sql/8_1_1-to-8_2_0_upgrade.sql` = "from 8.1.1 to
# 8.2.0"), and the MAX from-version that ALSO appears in the
# manifest is the newest upgrade-from we can be sure has a
# downloadable tarball on GitHub Releases.
#
# Two-signal rationale — the sql/ enumeration alone guarantees the
# result is in the current branch's `sql_upgrade.php` wizard
# dropdown (satisfies the acceptance test's dropdown-membership
# assertion). The manifest filter guarantees the result was
# actually released (satisfies boot-package.sh's tarball download
# from GitHub Releases). Neither signal alone is sufficient:
#
#   - sql-only would return a version scaffolded by patch-prep
#     before it ships (e.g., `8_3_1-to-8_4_0_upgrade.sql` added
#     to master mid-8.3.1-prep would break the download).
#   - manifest-only would return a released version that isn't in
#     the current branch's dropdown (e.g., a rel-830 test with
#     from=8.3.0 would fail dropdown-membership since 8.3.0 is
#     that line's own version).
#
# Historically `FROM_VERSION` defaulted to a hardcoded `8.2.0`.
# That worked on master (currently 8.4.0-dev, has 8.2.0 in its
# upgrade dropdown) and rel-830 (8.3.0 line, has 8.2.0 in
# dropdown), but broke on rel-820 (8.2.0 line — 8.2.0 is not in
# rel-820's own dropdown because you don't upgrade from your own
# version). Deriving from the checkout's own `sql/` files
# sidesteps that trap entirely and works on any branch without
# config maintenance. See openemr/openemr#13573 for the full
# symptom trace.
derive_from_version() {
    local candidates
    # Guard the `find` on directory existence first — under `set -e`
    # a bare `find sql` when sql/ doesn't exist returns 1 and would
    # tear down the whole script before our empty-result check
    # below could emit a useful error message. Same shape as the
    # `[[ ! -f ]]` guards elsewhere in this script.
    if [[ ! -d sql ]]; then
        echo "::error::derive_from_version: no sql/*-to-*_upgrade.sql files found in checkout" >&2
        exit 1
    fi
    # Enumerate `sql/*-to-*_upgrade.sql` (filenames like
    # `sql/8_1_0-to-8_1_1_upgrade.sql`) via `find` rather than `ls`
    # (shellcheck SC2012; also more robust to non-alphanumeric
    # names should the convention ever drift). Strip the `sql/`
    # prefix and the `-to-<version>_upgrade.sql` suffix to get the
    # from-version in underscore-shape (`8_1_1`), swap underscores
    # to dots.
    # Post-strip grep filters out any candidate that isn't in strict
    # X.Y.Z shape — defense against a hypothetical drift in the
    # sql/*-to-*_upgrade.sql filename convention (e.g., a stray
    # `sql/8_1-to-8_2_0_upgrade.sql` two-segment left-side would
    # otherwise slip through to the manifest intersect and silently
    # exclude itself with a confusing "no candidate matched" error).
    # Explicit shape enforcement here surfaces the drift cleanly at
    # the enumeration step.
    #
    # `|| true` on the pipeline because grep exits 1 when nothing
    # matches (all candidates malformed). The empty-check below
    # handles that case with a specific error message.
    candidates=$(find sql -maxdepth 1 -name '*-to-*_upgrade.sql' -type f 2>/dev/null \
        | sed -E 's|^sql/||; s|-to-[0-9_]+_upgrade\.sql$||' \
        | tr '_' '.' \
        | grep -E '^[0-9]+\.[0-9]+\.[0-9]+$' \
        | sort -uV || true)
    if [[ -z "${candidates}" ]]; then
        echo "::error::derive_from_version: no well-formed sql/*-to-*_upgrade.sql files found in checkout (expected X_Y_Z-to-A_B_C shape)" >&2
        exit 1
    fi
    # Fetch the shipped-versions manifest and intersect. See
    # fetch_shipped_versions for rationale + failure modes. Same
    # SC2310 pattern used at emit_from_version's derive_from_version
    # call — fetch_shipped_versions' failure modes are explicit
    # `return 1` after emitting an ::error:: line, so the disabled
    # `set -e` inside the function doesn't mask anything.
    local shipped
    # shellcheck disable=SC2310
    if ! shipped=$(fetch_shipped_versions); then
        exit 1
    fi
    # grep -Fxf reads pattern file (shipped set), matches whole lines
    # literally against candidates — clean set-intersection primitive.
    # `|| true` because grep exits 1 on no-match, which we handle
    # explicitly below with an actionable error.
    local matched
    matched=$(grep -Fxf <(printf '%s\n' "${shipped}") <<< "${candidates}" || true)
    if [[ -z "${matched}" ]]; then
        # `|| true` on the format-collapse `tr` calls to satisfy
        # SC2312 — tr can't fail on this input, but the linter can't
        # prove that.
        local candidates_line shipped_line
        candidates_line=$(tr '\n' ' ' <<< "${candidates}" || true)
        shipped_line=$(tr '\n' ' ' <<< "${shipped}" || true)
        echo "::error::derive_from_version: no sql-derived from-version candidate matches the shipped-versions manifest. Candidates from sql/: ${candidates_line}. Shipped per manifest: ${shipped_line}. See ${SHIPPED_VERSIONS_MANIFEST_URL}." >&2
        exit 1
    fi
    # Candidates are already X.Y.Z-shaped (see grep filter above);
    # no post-max shape check needed.
    sort -V <<< "${matched}" | tail -1
}

# emit_from_version — honors DISPATCH_FROM_VERSION when set (operator
# override via workflow_dispatch, or explicit caller pass on
# workflow_call), else auto-derives via derive_from_version. Shape-
# validates either way (same defense-in-depth as emit_to_version).
emit_from_version() {
    local candidate
    if [[ -n "${DISPATCH_FROM_VERSION}" ]]; then
        candidate="${DISPATCH_FROM_VERSION}"
    else
        # Explicit exit-code check on the command substitution.
        # `set -e` in the outer scope doesn't reliably propagate
        # a `$()`-in-assignment nonzero exit in bash, and
        # derive_from_version's `::error::` lines go to stderr
        # (not captured by `$()`), so we can't rely on catching
        # the failure via candidate's content either. Explicit `if !`
        # is the durable pattern here. SC2310 warns that `if !` on a
        # function call disables `set -e` inside the function — that
        # applies in general but not here: derive_from_version's
        # failure modes are all explicit `exit 1` (dir-missing,
        # empty-result, shape-check) that propagate regardless of
        # `set -e`, and the empty-result check catches any silent
        # pipeline failure that the disabled `set -e` would have
        # otherwise masked.
        # shellcheck disable=SC2310
        if ! candidate=$(derive_from_version); then
            exit 1
        fi
    fi
    if [[ ! "${candidate}" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        echo "::error::resolved from_version '${candidate}' does not match required X.Y.Z" >&2
        exit 1
    fi
    echo "from_version=${candidate}" >> "${GITHUB_OUTPUT}"
    echo "==> resolved from_version=${candidate}"
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
    emit_expected_version "true"
    emit_from_version
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
    emit_expected_version "true"
    emit_from_version
    exit 0
fi

# Manual dispatch always wins — honor the input.
if [[ "${EVENT_NAME}" == "workflow_dispatch" ]]; then
    echo "build_locally=${DISPATCH_BUILD_LOCALLY}" >> "${GITHUB_OUTPUT}"
    echo "==> dispatch mode: build_locally=${DISPATCH_BUILD_LOCALLY}"
    emit_to_version "${DISPATCH_BUILD_LOCALLY}"
    emit_expected_version "${DISPATCH_BUILD_LOCALLY}"
    emit_from_version
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
        emit_expected_version "false"
        emit_from_version
        exit 0
        ;;
esac
# Branch creation on push emits before=000...; can't diff.
if [[ -z "${BASE}" || "${BASE}" == "0000000000000000000000000000000000000000" ]]; then
    echo "==> branch-creation event (no base ref): defaulting build_locally=false"
    echo "build_locally=false" >> "${GITHUB_OUTPUT}"
    emit_to_version "false"
    emit_expected_version "false"
    emit_from_version
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
        emit_expected_version "false"
        emit_from_version
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
    emit_expected_version "true"
    emit_from_version
else
    echo "==> tarball-build surface unchanged between ${BASE} and ${HEAD}: build_locally=false"
    echo "build_locally=false" >> "${GITHUB_OUTPUT}"
    emit_to_version "false"
    emit_expected_version "false"
    emit_from_version
fi

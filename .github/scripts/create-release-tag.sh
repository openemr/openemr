#!/usr/bin/env bash
#
# Create the annotated git tag and GitHub release for a tarball
# release build, idempotently.
#
# Extracted from reusable-publish-release.yml's "Create annotated tag
# and GitHub release" step. The step runs in both the primary release
# path (build-release.yml) and the Phase 9 recovery path
# (acceptance-only.yml), so both call sites re-fire this script under
# real-world "already-partially-done" states:
#
#   - tag doesn't exist + release doesn't exist -> first-run happy path
#   - tag exists + release doesn't exist        -> partial: prior run
#                                                  pushed the tag but
#                                                  gh release create
#                                                  failed
#   - tag exists + release exists               -> full recovery re-run,
#                                                  no-op
#
# The idempotency logic (git ls-remote 0/2/other case, gh release view
# check) is release-critical: if it regresses, a recovery re-run would
# either double-tag (git-side regression) or clobber the release
# (gh-side regression). Pulling the block out lets BATS assert the
# case-handling stays honest.
#
# Inputs (env, all required unless noted)
#   RELEASE_TAG          Annotated git tag to create (e.g. v8_2_0).
#   RELEASE_VERSION      Human-readable version used in the release
#                        title (e.g. 8.2.0). Rendered as
#                        "OpenEMR $RELEASE_VERSION".
#   VERSION_BRANCH       Branch to tag from (e.g. rel-820) — the tag
#                        points at that branch's tip in the checkout
#                        this script runs against, NOT the ref the
#                        workflow was dispatched against.
#   RELEASE_NOTES_FILE   Path to a file whose contents become the
#                        release body (e.g. tools/release/release-output/
#                        changelog.md). Passed to `gh release create
#                        --notes-file`.
#   GH_TOKEN             gh CLI auth token. Read directly by gh from
#                        env; not consumed here.
#   APP_TOKEN (optional) Installation token for the release App. When
#                        set, used inline via `git -c http.extraheader`
#                        for git ls-remote and git push — one-shot,
#                        never written to .git/config. Prevents the
#                        token from leaking to intermediate steps (npm,
#                        composer, etc.) that a compromised dep could
#                        exfiltrate. When unset, git operations use
#                        whatever credentials the calling checkout
#                        persisted (backward compat with legacy
#                        callers).
#
# Exit codes
#   0    Success — tag + release both exist in the expected state
#        (either created here or pre-existed from a prior run).
#   1    git ls-remote returned an unexpected exit code (auth,
#        transport, or a shape we haven't seen). Also: any downstream
#        git/gh failure surfaces via `set -e`.
#
# Non-preservation note: the original inline block does not verify
# that a pre-existing tag points at the expected commit. Preserving
# that behavior verbatim so this extraction is refactor-in-place; a
# future PR can add SHA verification if the operational history shows
# it's needed.

set -euo pipefail

: "${RELEASE_TAG:?RELEASE_TAG env var is required}"
: "${RELEASE_VERSION:?RELEASE_VERSION env var is required}"
: "${VERSION_BRANCH:?VERSION_BRANCH env var is required}"
: "${RELEASE_NOTES_FILE:?RELEASE_NOTES_FILE env var is required}"

# Preflight the notes file BEFORE any tag mutation. If it's missing,
# unreadable, or a directory, the failure would otherwise surface
# later at `gh release create --notes-file` — AFTER `git push` has
# already published the tag, leaving the release in a partial state
# (tag exists, no release, manual recovery needed).
if [[ ! -f "${RELEASE_NOTES_FILE}" || ! -r "${RELEASE_NOTES_FILE}" ]]; then
    echo "::error::RELEASE_NOTES_FILE is missing or unreadable: ${RELEASE_NOTES_FILE}" >&2
    exit 1
fi

tag="${RELEASE_TAG}"

git config user.name 'github-actions[bot]'
git config user.email '41898282+github-actions[bot]@users.noreply.github.com'

# If APP_TOKEN is set, build a `-c http.extraheader` arg to inject
# Basic auth into git ls-remote + git push without writing anything
# to .git/config. Leaves no residual credentials for any subsequent
# step (or a compromised dep in the same job) to read. When unset,
# git commands run bare (backward compat with callers that persist
# credentials via actions/checkout).
git_auth_arg=()
if [[ -n "${APP_TOKEN:-}" ]]; then
    basic_auth_b64=$(printf 'x-access-token:%s' "${APP_TOKEN}" | base64 -w0)
    git_auth_arg=(-c "http.https://github.com/.extraheader=Authorization: Basic ${basic_auth_b64}")
fi

# Check the remote, not the local checkout: the checkout step uses
# fetch-depth: 1 and does not fetch tags, so a local rev-parse always
# misses an already-published tag. That made `git push origin "$tag"`
# fail with "tag already exists in the remote" when re-firing after
# the openemr/openemr conductor had already tagged via its finalize
# job.
#
# `git ls-remote --exit-code` returns 0 when the tag exists and 2 when
# it does not. Any other status is a real lookup failure (auth,
# transport) and must abort rather than fall through to a tag push
# that would mask the underlying error.
tag_lookup_status=0
# Capture stderr into a variable so the wildcard-failure branch below
# can surface git's own diagnostic (auth error, transport failure,
# etc.) alongside the exit code — speeds up incident triage on the
# release-critical path. `2>&1 > /dev/null` reorders redirects: stderr
# to captured stdout, then the original stdout to /dev/null.
tag_lookup_stderr=$(git "${git_auth_arg[@]}" ls-remote --tags --exit-code origin "refs/tags/${tag}" 2>&1 > /dev/null) || tag_lookup_status=$?
case "${tag_lookup_status}" in
    0)
        echo "Tag ${tag} already exists on origin, skipping tag creation"
        ;;
    2)
        git tag -a -m "Release ${tag}" "${tag}" "${VERSION_BRANCH}"
        git "${git_auth_arg[@]}" push origin "${tag}"
        ;;
    *)
        echo "::error::Failed to query origin for tag ${tag} (git ls-remote exit ${tag_lookup_status}): ${tag_lookup_stderr}" >&2
        exit "${tag_lookup_status}"
        ;;
esac

if ! gh release view "${tag}" --repo openemr/openemr > /dev/null 2>&1; then
    echo "Creating release ${tag}"
    gh release create "${tag}" \
        --repo openemr/openemr \
        --title "OpenEMR ${RELEASE_VERSION}" \
        --notes-file "${RELEASE_NOTES_FILE}" \
        --verify-tag
fi

# BATS tests for .github/scripts/create-release-tag.sh
#
# Runs the script against mock `git` + `gh` binaries (see helpers.bash
# + git-mock.sh + gh-mock.sh). Covers the recovery-path idempotency
# matrix the extraction was motivated by:
#
#   - tag doesn't exist + release doesn't exist  (happy path -> create
#                                                 both)
#   - tag exists + release exists                (full recovery re-run
#                                                 -> no-op)
#   - tag exists + release doesn't exist         (partial: tag pushed
#                                                 but gh release create
#                                                 failed last run ->
#                                                 create release only)
#   - tag doesn't exist + release exists         (should never happen
#                                                 in practice, but the
#                                                 script tolerates it
#                                                 -- creates tag, then
#                                                 gh release view sees
#                                                 the pre-existing
#                                                 release and skips
#                                                 create)
#   - ls-remote unexpected exit                  (auth, transport, or
#                                                 shape we haven't seen
#                                                 -> exit 1 with error)
#   - gh release create fails                    (surface via set -e)
#   - git push fails                             (surface via set -e)
#
# What's NOT covered
#   - The exact `git tag -a -m ...` flag ordering. The mock accepts
#     any args on `git tag ...`; the test asserts on the presence of
#     the subcommand + the tag name.
#   - The `gh release create` flag list beyond `--verify-tag`. Same
#     reason -- if a future tweak changes flag ordering, that's not a
#     behavior regression this suite should flag.

load 'helpers'

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

# --- happy path ---

@test "tag absent + release absent -> creates both, exit 0" {
    # Defaults from setup_test_dir already configure this. Just run.
    run bash "${CREATE_RELEASE_TAG_SCRIPT}"
    [[ ${status} -eq 0 ]]
    local calls
    calls="$(mock_calls)"
    # Must have called ls-remote to check origin.
    [[ "${calls}" == *"git ls-remote --tags --exit-code origin refs/tags/v8_2_0"* ]]
    # Must have created the tag against the version branch.
    [[ "${calls}" == *"git tag -a -m Release v8_2_0 v8_2_0 rel-820"* ]]
    # Must have pushed.
    [[ "${calls}" == *"git push origin v8_2_0"* ]]
    # Must have checked release existence, then created it.
    [[ "${calls}" == *"gh release view v8_2_0 --repo openemr/openemr"* ]]
    [[ "${calls}" == *"gh release create v8_2_0 --repo openemr/openemr"* ]]
    [[ "${output}" == *"Creating release v8_2_0"* ]]
    [[ "${output}" != *"::error::"* ]]
}

# --- idempotent recovery matrix ---

@test "tag exists + release exists -> full no-op, exit 0" {
    export MOCK_LS_REMOTE_EXIT="0"
    # Mirror what real git prints when the tag exists on origin.
    export MOCK_LS_REMOTE_STDOUT="abc123\trefs/tags/v8_2_0"
    export MOCK_GH_RELEASE_VIEW_EXIT="0"
    run bash "${CREATE_RELEASE_TAG_SCRIPT}"
    [[ ${status} -eq 0 ]]
    local calls
    calls="$(mock_calls)"
    # Skipped tag creation (log message).
    [[ "${output}" == *"Tag v8_2_0 already exists on origin, skipping tag creation"* ]]
    # No git tag or git push call.
    [[ "${calls}" != *"git tag "* ]]
    [[ "${calls}" != *"git push "* ]]
    # gh release view ran; gh release create did NOT.
    [[ "${calls}" == *"gh release view v8_2_0"* ]]
    [[ "${calls}" != *"gh release create"* ]]
    # No "Creating release" log line.
    [[ "${output}" != *"Creating release"* ]]
    [[ "${output}" != *"::error::"* ]]
}

@test "tag exists + release absent -> creates release only, skips tag" {
    export MOCK_LS_REMOTE_EXIT="0"
    export MOCK_LS_REMOTE_STDOUT="abc123\trefs/tags/v8_2_0"
    export MOCK_GH_RELEASE_VIEW_EXIT="1"
    run bash "${CREATE_RELEASE_TAG_SCRIPT}"
    [[ ${status} -eq 0 ]]
    local calls
    calls="$(mock_calls)"
    [[ "${output}" == *"Tag v8_2_0 already exists on origin, skipping tag creation"* ]]
    [[ "${calls}" != *"git tag "* ]]
    [[ "${calls}" != *"git push "* ]]
    [[ "${calls}" == *"gh release create v8_2_0 --repo openemr/openemr"* ]]
    [[ "${output}" == *"Creating release v8_2_0"* ]]
    [[ "${output}" != *"::error::"* ]]
}

@test "tag absent + release exists -> creates tag, skips release" {
    # Not a scenario the operational history has produced, but the
    # script's two-decisions-are-independent structure tolerates it.
    # Locking the behavior in with a test prevents a future refactor
    # that couples the two decisions from silently regressing.
    export MOCK_LS_REMOTE_EXIT="2"
    export MOCK_GH_RELEASE_VIEW_EXIT="0"
    run bash "${CREATE_RELEASE_TAG_SCRIPT}"
    [[ ${status} -eq 0 ]]
    local calls
    calls="$(mock_calls)"
    [[ "${calls}" == *"git tag -a -m Release v8_2_0 v8_2_0 rel-820"* ]]
    [[ "${calls}" == *"git push origin v8_2_0"* ]]
    [[ "${calls}" == *"gh release view v8_2_0"* ]]
    [[ "${calls}" != *"gh release create"* ]]
    [[ "${output}" != *"Creating release"* ]]
    [[ "${output}" != *"::error::"* ]]
}

# --- ls-remote failure ---

@test "ls-remote returns unexpected exit (e.g. 128) -> exit 128 with clear error" {
    # curl-style network / auth failure surfaces as an exit code that's
    # neither 0 nor 2. Real git uses 128 for most auth/transport
    # failures. The script re-exits with the same code so the caller
    # sees the real failure code, not a synthesized 1.
    export MOCK_LS_REMOTE_EXIT="128"
    run bash "${CREATE_RELEASE_TAG_SCRIPT}"
    [[ ${status} -eq 128 ]]
    [[ "${output}" == *"::error::Failed to query origin for tag v8_2_0 (git ls-remote exit 128)"* ]]
    local calls
    calls="$(mock_calls)"
    # Must NOT have proceeded to tag / push / release.
    [[ "${calls}" != *"git tag "* ]]
    [[ "${calls}" != *"git push "* ]]
    [[ "${calls}" != *"gh release"* ]]
}

# --- downstream failures surface via set -e ---

@test "gh release create fails -> script exits non-zero" {
    export MOCK_GH_RELEASE_CREATE_EXIT="1"
    run bash "${CREATE_RELEASE_TAG_SCRIPT}"
    [[ ${status} -ne 0 ]]
    local calls
    calls="$(mock_calls)"
    # Must have attempted the create (that's what failed).
    [[ "${calls}" == *"gh release create v8_2_0"* ]]
    # Must have progressed through the happy-path tag creation first.
    [[ "${calls}" == *"git tag -a -m Release v8_2_0 v8_2_0 rel-820"* ]]
    [[ "${calls}" == *"git push origin v8_2_0"* ]]
}

@test "git push fails -> script exits non-zero and skips gh calls" {
    export MOCK_GIT_PUSH_EXIT="1"
    run bash "${CREATE_RELEASE_TAG_SCRIPT}"
    [[ ${status} -ne 0 ]]
    local calls
    calls="$(mock_calls)"
    [[ "${calls}" == *"git push origin v8_2_0"* ]]
    # set -e should have stopped execution before any gh call.
    [[ "${calls}" != *"gh release"* ]]
}

# --- env-var contract ---

@test "missing RELEASE_TAG -> exit non-zero with 'required' message" {
    unset RELEASE_TAG
    run bash "${CREATE_RELEASE_TAG_SCRIPT}"
    [[ ${status} -ne 0 ]]
    [[ "${output}" == *"RELEASE_TAG"*"required"* ]]
}

@test "RELEASE_NOTES_FILE points at missing path -> exit 1 BEFORE any tag mutation" {
    # Regression guard: without the preflight -f check, a missing
    # notes file would only surface at `gh release create --notes-file`
    # AFTER `git push` published the tag, leaving the release in a
    # partial state (tag on origin, no release, manual recovery
    # needed). Preflight moves the failure to before any mutation.
    export RELEASE_NOTES_FILE="${CWD}/does-not-exist.md"
    run bash "${CREATE_RELEASE_TAG_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::RELEASE_NOTES_FILE is missing or unreadable"* ]]
    # Must NOT have invoked git tag / git push / any gh call.
    [[ "${output}" != *"git tag"* ]]
    [[ "${output}" != *"gh release"* ]]
}

@test "RELEASE_NOTES_FILE points at a directory -> exit 1 BEFORE any tag mutation" {
    # -f rejects directories too. Same partial-state defense as the
    # missing-file case.
    mkdir -p "${CWD}/notes-dir"
    export RELEASE_NOTES_FILE="${CWD}/notes-dir"
    run bash "${CREATE_RELEASE_TAG_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::RELEASE_NOTES_FILE is missing or unreadable"* ]]
}

@test "APP_TOKEN set -> injected inline via git -c http.extraheader, never persisted" {
    # Security: APP_TOKEN authenticates ls-remote + push via one-shot
    # -c http.extraheader=..., NOT via `git remote set-url` /
    # persist-credentials which would leave the token in .git/config
    # readable by any subsequent step (composer install, npm ci, task
    # summary, etc.) or a compromised dep. Verifies the token appears
    # in the git command invocation itself (mock records the full
    # argv) but the script does no `git config` / `git remote
    # set-url` that would persist it.
    export APP_TOKEN='ghs_testFAKEtoken12345'
    run bash "${CREATE_RELEASE_TAG_SCRIPT}"
    [[ ${status} -eq 0 ]]
    local calls
    calls="$(mock_calls)"
    # Base64 of "x-access-token:ghs_testFAKEtoken12345" (no newline).
    local expected_b64
    expected_b64="$(printf 'x-access-token:%s' "${APP_TOKEN}" | base64 -w0)"
    # Both ls-remote and push must carry the extraheader arg.
    [[ "${calls}" == *"git -c http.https://github.com/.extraheader=Authorization: Basic ${expected_b64} ls-remote"* ]]
    [[ "${calls}" == *"git -c http.https://github.com/.extraheader=Authorization: Basic ${expected_b64} push origin"* ]]
    # NO `git remote set-url` that would persist token into .git/config.
    [[ "${calls}" != *"git remote set-url"* ]]
}

@test "APP_TOKEN unset -> git commands run bare (backward compat)" {
    # Legacy callers configure origin credentials via actions/checkout's
    # persist-credentials=true; the script should not require APP_TOKEN.
    unset APP_TOKEN
    run bash "${CREATE_RELEASE_TAG_SCRIPT}"
    [[ ${status} -eq 0 ]]
    local calls
    calls="$(mock_calls)"
    # git operations run without the extraheader injection.
    [[ "${calls}" == *"git ls-remote --tags --exit-code origin"* ]]
    [[ "${calls}" == *"git push origin v8_2_0"* ]]
    [[ "${calls}" != *"http.https://github.com/.extraheader"* ]]
}

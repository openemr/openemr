# BATS tests for .github/scripts/build-release-body.sh
#
# Covers:
#   - short body (< 124K) -> passes through unchanged
#   - long body (> 124K) -> truncated + anchor pointer appended
#   - anchor slug variations: `[8.2.0] - 2026-07-08` -> `820---2026-07-08`
#     and several other version/date shapes
#   - truncation boundary: content exactly 124K -> no truncation
#   - truncation boundary: content 124K+1 -> truncated
#   - missing CHANGELOG_URL when truncation triggers -> clear error
#   - stdin fallback when SECTION_FILE is unset
#
# The 124K limit (not 125K) matches the script's LIMIT constant: a
# 1000-byte safety margin under GitHub's hard 125,000-char Release-
# body cap. Tests reference 124K throughout so they lock the limit
# in — any change to LIMIT breaks these tests, forcing a follow-up
# review of why the margin is moving.

load 'helpers'

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

# --- pass-through vs truncation ---

@test "short body (< 124K) passes through unchanged, no pointer" {
    cat > section.md <<'EOF'
## [8.2.0] - 2026-07-08

Short body content.
EOF
    SECTION_FILE=section.md VERSION=8.2.0 DATE=2026-07-08 \
        CHANGELOG_URL=https://github.com/openemr/openemr/blob/rel-820/CHANGELOG.md \
        REL_BRANCH=rel-820 \
        run bash "${BUILD_RELEASE_BODY_SCRIPT}"
    [[ ${status} -eq 0 ]]
    # Body content on stdout matches the input verbatim (stderr carries
    # the "Section fits" diagnostic).
    [[ "${output}" == *"## [8.2.0] - 2026-07-08"* ]]
    [[ "${output}" == *"Short body content."* ]]
    # No pointer body substituted
    [[ "${output}" != *":warning:"* ]]
    [[ "${output}" != *"exceeds GitHub"* ]]
}

@test "long body (> 124K) triggers truncation with anchor pointer" {
    make_body_of_size section.md 200000
    SECTION_FILE=section.md VERSION=8.2.0 DATE=2026-07-08 \
        CHANGELOG_URL=https://github.com/openemr/openemr/blob/rel-820/CHANGELOG.md \
        REL_BRANCH=rel-820 \
        run bash "${BUILD_RELEASE_BODY_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *":warning:"* ]]
    [[ "${output}" == *"exceeds GitHub's 125,000-character Release body limit"* ]]
    [[ "${output}" == *"820---2026-07-08"* ]]
    [[ "${output}" == *"https://github.com/openemr/openemr/blob/rel-820/CHANGELOG.md#820---2026-07-08"* ]]
    [[ "${output}" == *"on the live \`rel-820\` branch"* ]]
    # Full section content is NOT in the truncated body (that's the
    # whole point — attachment carries it separately).
    [[ "${output}" != *"aaaaaaaaaaaaaaaaaaaa"* ]]
}

# --- anchor slug variations ---

@test "anchor slug: 8.2.0 + 2026-07-08 -> 820---2026-07-08" {
    make_body_of_size section.md 200000
    SECTION_FILE=section.md VERSION=8.2.0 DATE=2026-07-08 \
        CHANGELOG_URL=https://example.com/CHANGELOG.md \
        REL_BRANCH=rel-820 \
        run bash "${BUILD_RELEASE_BODY_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"https://example.com/CHANGELOG.md#820---2026-07-08"* ]]
}

@test "anchor slug: 8.1.5 + 2026-05-15 -> 815---2026-05-15" {
    make_body_of_size section.md 200000
    SECTION_FILE=section.md VERSION=8.1.5 DATE=2026-05-15 \
        CHANGELOG_URL=https://example.com/CHANGELOG.md \
        REL_BRANCH=rel-810 \
        run bash "${BUILD_RELEASE_BODY_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"https://example.com/CHANGELOG.md#815---2026-05-15"* ]]
}

@test "anchor slug: 10.0.0 (two-digit major) + 2027-01-01 -> 1000---2027-01-01" {
    # Multi-digit segments still just have their dots stripped — no
    # segment-width assumption. Locks that in.
    make_body_of_size section.md 200000
    SECTION_FILE=section.md VERSION=10.0.0 DATE=2027-01-01 \
        CHANGELOG_URL=https://example.com/CHANGELOG.md \
        REL_BRANCH=rel-1000 \
        run bash "${BUILD_RELEASE_BODY_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"https://example.com/CHANGELOG.md#1000---2027-01-01"* ]]
}

@test "anchor slug: 8.2.10 (two-digit patch) + 2026-12-31 -> 8210---2026-12-31" {
    make_body_of_size section.md 200000
    SECTION_FILE=section.md VERSION=8.2.10 DATE=2026-12-31 \
        CHANGELOG_URL=https://example.com/CHANGELOG.md \
        REL_BRANCH=rel-820 \
        run bash "${BUILD_RELEASE_BODY_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"https://example.com/CHANGELOG.md#8210---2026-12-31"* ]]
}

# --- truncation boundary ---

@test "body exactly at 124K limit -> no truncation" {
    make_body_of_size section.md 124000
    # Redirect stdout to a file so we can byte-count it independent of
    # stderr diagnostics that BATS's `run` merges into `output`.
    SECTION_FILE=section.md VERSION=8.2.0 DATE=2026-07-08 \
        CHANGELOG_URL=https://example.com/CHANGELOG.md \
        REL_BRANCH=rel-820 \
        bash "${BUILD_RELEASE_BODY_SCRIPT}" > out.md 2> err.log
    # No pointer body in stdout
    ! grep -q ':warning:' out.md
    ! grep -q 'exceeds GitHub' out.md
    # Stdout is byte-identical to input (pure pass-through, no metadata
    # added).
    out_size=$(wc -c < out.md)
    [[ "${out_size}" -eq 124000 ]]
}

@test "body at 124K+1 bytes -> truncated" {
    make_body_of_size section.md 124001
    SECTION_FILE=section.md VERSION=8.2.0 DATE=2026-07-08 \
        CHANGELOG_URL=https://example.com/CHANGELOG.md \
        REL_BRANCH=rel-820 \
        run bash "${BUILD_RELEASE_BODY_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *":warning:"* ]]
    [[ "${output}" == *"820---2026-07-08"* ]]
}

# --- missing CHANGELOG_URL when truncation triggers ---

@test "truncation triggers but CHANGELOG_URL unset -> exit 1 with clear error" {
    make_body_of_size section.md 200000
    SECTION_FILE=section.md VERSION=8.2.0 DATE=2026-07-08 \
        REL_BRANCH=rel-820 \
        run bash "${BUILD_RELEASE_BODY_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::"* ]]
    [[ "${output}" == *"CHANGELOG_URL is not set"* ]]
}

@test "short body without CHANGELOG_URL is fine (no truncation, url not consulted)" {
    # If the section fits in the body, CHANGELOG_URL is irrelevant —
    # the script must not require it in the short-body path. Locks
    # that in so a future refactor doesn't accidentally tighten it.
    cat > section.md <<'EOF'
## [8.2.0] - 2026-07-08

Small.
EOF
    SECTION_FILE=section.md VERSION=8.2.0 DATE=2026-07-08 REL_BRANCH=rel-820 \
        run bash "${BUILD_RELEASE_BODY_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"Small."* ]]
}

# --- stdin fallback ---

@test "section content via stdin (SECTION_FILE unset) also works" {
    # Composition mode: extract-changelog-section.sh | build-release-body.sh.
    # No SECTION_FILE — script must read stdin.
    run bash -c '
        printf "%s\n" "## [8.2.0] - 2026-07-08" "" "Small stdin body." | \
        VERSION=8.2.0 DATE=2026-07-08 REL_BRANCH=rel-820 \
        bash '"'${BUILD_RELEASE_BODY_SCRIPT}'"'
    '
    [[ ${status} -eq 0 ]]
    [[ "${output}" == *"## [8.2.0] - 2026-07-08"* ]]
    [[ "${output}" == *"Small stdin body."* ]]
    [[ "${output}" != *":warning:"* ]]
}

@test "SECTION_FILE set to nonexistent path -> exit 1" {
    SECTION_FILE=/nonexistent/section.md VERSION=8.2.0 DATE=2026-07-08 \
        CHANGELOG_URL=https://example.com/CHANGELOG.md \
        REL_BRANCH=rel-820 \
        run bash "${BUILD_RELEASE_BODY_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::SECTION_FILE '/nonexistent/section.md' does not exist"* ]]
}

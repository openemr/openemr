# BATS tests for .github/scripts/pick-smoketest-target.sh
#
# Pure file-parse: no network, no external commands beyond awk.
# Covers:
#   - happy path: single latest-marked row
#   - happy path: latest marker at start / end / middle of docker_tags
#     comma-list
#   - happy path: latest on a non-first row (skips earlier rows)
#   - error: no row contains latest
#   - error: file missing
#   - error: version_ref shape invalid
#   - error: latest row missing openemr_version_ref
#   - resilience: comment lines don't false-match; master row (no
#     openemr_version_ref) doesn't get picked

load 'helpers'

setup() {
    setup_test_dir
}

teardown() {
    teardown_test_dir
}

@test "happy path: single row with docker_tags 8.4.0,latest -> prints branch + version_ref" {
    write_fixture <<'EOF'
- branch: rel-840
  docker_tags: 8.4.0,latest
  openemr_version_ref: v8_4_0

EOF
    run bash "${PICK_SMOKETEST_TARGET_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == "rel-840 v8_4_0" ]]
}

@test "happy path: latest first in docker_tags list -> matches" {
    write_fixture <<'EOF'
- branch: rel-840
  docker_tags: latest,8.4.0
  openemr_version_ref: v8_4_0

EOF
    run bash "${PICK_SMOKETEST_TARGET_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == "rel-840 v8_4_0" ]]
}

@test "happy path: latest only tag -> matches" {
    write_fixture <<'EOF'
- branch: rel-840
  docker_tags: latest
  openemr_version_ref: v8_4_0

EOF
    run bash "${PICK_SMOKETEST_TARGET_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == "rel-840 v8_4_0" ]]
}

@test "happy path: latest on later row after unmarked rows -> picks the latest row" {
    write_fixture <<'EOF'
- branch: rel-830
  docker_tags: 8.3.0
  openemr_version_ref: v8_3_0

- branch: rel-840
  docker_tags: 8.4.0,latest
  openemr_version_ref: v8_4_0

- branch: rel-800
  docker_tags: 8.0.0
  openemr_version_ref: v8_0_0

EOF
    run bash "${PICK_SMOKETEST_TARGET_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == "rel-840 v8_4_0" ]]
}

@test "happy path: file matches real release-targets.yml shape (with master block first)" {
    write_fixture <<'EOF'
- branch: master
  # 8.5.0 is the version master is currently developing.
  docker_tags: dev
  openemr_version_ref: master

- branch: rel-840
  docker_tags: 8.4.0,latest
  openemr_version_ref: v8_4_0
  gate_with_acceptance: true

- branch: rel-830
  docker_tags: 8.3.0
  openemr_version_ref: v8_3_0
  gate_with_acceptance: true

EOF
    run bash "${PICK_SMOKETEST_TARGET_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == "rel-840 v8_4_0" ]]
}

@test "happy path: file without trailing blank line still parses" {
    # Use printf instead of heredoc so we control the final newline.
    printf -- '- branch: rel-840\n  docker_tags: 8.4.0,latest\n  openemr_version_ref: v8_4_0\n' \
        > "${CWD}/release-targets.yml"
    export RELEASE_TARGETS_FILE="${CWD}/release-targets.yml"
    run bash "${PICK_SMOKETEST_TARGET_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == "rel-840 v8_4_0" ]]
}

@test "error: no row contains latest -> exit 1 with :error:" {
    write_fixture <<'EOF'
- branch: rel-840
  docker_tags: 8.4.0
  openemr_version_ref: v8_4_0

- branch: rel-830
  docker_tags: 8.3.0
  openemr_version_ref: v8_3_0

EOF
    run bash "${PICK_SMOKETEST_TARGET_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::"* ]]
    [[ "${output}" == *"No row with docker_tags containing 'latest'"* ]]
}

@test "error: file missing -> exit 1 with :error:" {
    export RELEASE_TARGETS_FILE="${CWD}/does-not-exist.yml"
    run bash "${PICK_SMOKETEST_TARGET_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::release-targets file not found"* ]]
}

@test "error: version_ref not v<M>_<m>_<p> shape -> exit 1" {
    write_fixture <<'EOF'
- branch: rel-840
  docker_tags: 8.4.0,latest
  openemr_version_ref: v8.4.0

EOF
    run bash "${PICK_SMOKETEST_TARGET_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::"* ]]
    [[ "${output}" == *"does not match expected v<major>_<minor>_<patch> shape"* ]]
}

@test "error: version_ref is a branch name (like master), not a tag -> exit 1" {
    # The master block itself has openemr_version_ref: master. If someone
    # accidentally marks the master row as latest, we want to catch it
    # (a git ref "master" is not a shipped-version tag).
    write_fixture <<'EOF'
- branch: master
  docker_tags: dev,latest
  openemr_version_ref: master

EOF
    run bash "${PICK_SMOKETEST_TARGET_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::"* ]]
    [[ "${output}" == *"does not match expected v<major>_<minor>_<patch> shape"* ]]
}

@test "error: latest row missing openemr_version_ref field entirely -> exit 1" {
    write_fixture <<'EOF'
- branch: rel-840
  docker_tags: 8.4.0,latest

EOF
    run bash "${PICK_SMOKETEST_TARGET_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::"* ]]
}

@test "no false-positive: 'latest' as substring of another tag (e.g. 'latest-dev') does NOT match" {
    # Regex uses (^|,)latest($|,) -- word boundary via commas, so
    # "latest-dev" or "not-latest" are not treated as the latest marker.
    write_fixture <<'EOF'
- branch: rel-840
  docker_tags: 8.4.0,latest-dev
  openemr_version_ref: v8_4_0

EOF
    run bash "${PICK_SMOKETEST_TARGET_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::"* ]]
    [[ "${output}" == *"No row with docker_tags containing 'latest'"* ]]
}

@test "no false-positive: comment line mentioning 'latest' does NOT match a row" {
    # docker_tags line is the only source of truth. Comments and other
    # text can freely mention "latest" without picking that row.
    write_fixture <<'EOF'
- branch: rel-840
  # 8.4.0 is the latest shipped version -- see below.
  docker_tags: 8.4.0
  openemr_version_ref: v8_4_0

EOF
    run bash "${PICK_SMOKETEST_TARGET_SCRIPT}"
    [[ ${status} -eq 1 ]]
    [[ "${output}" == *"::error::"* ]]
    [[ "${output}" == *"No row with docker_tags containing 'latest'"* ]]
}

@test "picker uses the FIRST latest row encountered (defensive: normally only one row is latest)" {
    write_fixture <<'EOF'
- branch: rel-840
  docker_tags: 8.4.0,latest
  openemr_version_ref: v8_4_0

- branch: rel-830
  docker_tags: 8.3.0,latest
  openemr_version_ref: v8_3_0

EOF
    run bash "${PICK_SMOKETEST_TARGET_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == "rel-840 v8_4_0" ]]
}

@test "row boundary: adjacent list entries with no blank between rows" {
    # YAML allows list entries to be adjacent (no blank between).
    # Parser must use "- branch:" as the row boundary, not blank
    # lines, or the unmarked row's fields would leak into the
    # latest-marked row and misidentify it.
    printf -- '- branch: rel-840\n  docker_tags: 8.4.0,latest\n  openemr_version_ref: v8_4_0\n- branch: rel-830\n  docker_tags: 8.3.0\n  openemr_version_ref: v8_3_0\n' \
        > "${CWD}/release-targets.yml"
    export RELEASE_TARGETS_FILE="${CWD}/release-targets.yml"
    run bash "${PICK_SMOKETEST_TARGET_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == "rel-840 v8_4_0" ]]
}

@test "row boundary: adjacent entries, latest is on the second row" {
    # Same as above, but latest is on the second row -- test that
    # finalizing the first row on "- branch:" doesn't confuse the
    # second row's state.
    printf -- '- branch: rel-830\n  docker_tags: 8.3.0\n  openemr_version_ref: v8_3_0\n- branch: rel-840\n  docker_tags: 8.4.0,latest\n  openemr_version_ref: v8_4_0\n' \
        > "${CWD}/release-targets.yml"
    export RELEASE_TARGETS_FILE="${CWD}/release-targets.yml"
    run bash "${PICK_SMOKETEST_TARGET_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == "rel-840 v8_4_0" ]]
}

@test "row boundary: blank line INSIDE an entry (between docker_tags and openemr_version_ref) does not break parsing" {
    # YAML permits blank lines inside a list entry. Parser must
    # ignore blanks (not treat them as row boundaries), otherwise
    # it would emit the row with an empty version_ref.
    write_fixture <<'EOF'
- branch: rel-840
  docker_tags: 8.4.0,latest

  openemr_version_ref: v8_4_0

EOF
    run bash "${PICK_SMOKETEST_TARGET_SCRIPT}"
    [[ ${status} -eq 0 ]]
    [[ "${output}" == "rel-840 v8_4_0" ]]
}

# BATS tests for .github/scripts/composer-annotations.sh
#
# Covers:
#   - require-checker: one annotation per unknown symbol, with the guessed
#     dependency when the tool gives one; summary table; non-JSON output
#   - validate: one annotation per "- " finding, lock-file findings on
#     composer.lock; fallback annotation; summary
#   - normalize: one annotation; diff in the summary; output that is not a
#     diff reported as a failed run
#   - validate: Composer's real section order (lock errors, then general
#     warnings) puts each finding on the right file
#   - workflow-command escaping of % and CR
#   - annotations only under GITHUB_ACTIONS=true, summary only with
#     GITHUB_STEP_SUMMARY set
#   - usage errors

load 'helpers'

setup() {
    setup_scratch
}

teardown() {
    teardown_scratch
}

write_require_checker_report() {
    cat > "${SCRATCH}/crc.json" <<'JSON'
{
  "_meta": {"composer-require-checker": {"version": "4.17.0"}, "date": "2026-11-18T16:32:35+00:00"},
  "unknown-symbols": {
    "gmp_add": ["ext-gmp"],
    "Nonexistent\\Vendor\\Thing": []
  }
}
JSON
}

write_validate_output() {
    cat > "${SCRATCH}/validate.txt" <<'TEXT'
./composer.json is valid, but with a few warnings
See https://getcomposer.org/doc/04-schema.md for details on the schema
# General warnings
- require.foo/bar : unbound version constraints (*) should be avoided
# Lock file warnings
- Required package "foo/bar" is not present in the lock file.
TEXT
}

@test "require-checker: annotates each unknown symbol on composer.json" {
    write_require_checker_report
    GITHUB_ACTIONS=true run bash "${COMPOSER_ANNOTATIONS_SCRIPT}" require-checker "${SCRATCH}/crc.json"
    [[ ${status} -eq 0 ]]
    [[ ${output} == *'::error file=composer.json,title=Unknown symbol::gmp_add is used but no package or extension in composer.json provides it. Guessed dependency: ext-gmp.'* ]]
    [[ ${output} == *'::error file=composer.json,title=Unknown symbol::Nonexistent\Vendor\Thing is used but no package or extension in composer.json provides it.'* ]]
    [[ ${output} != *'Thing is used but no package or extension in composer.json provides it. Guessed'* ]]
}

@test "require-checker: plain report without annotations outside Actions" {
    write_require_checker_report
    run bash "${COMPOSER_ANNOTATIONS_SCRIPT}" require-checker "${SCRATCH}/crc.json"
    [[ ${status} -eq 0 ]]
    [[ ${output} != *'::error'* ]]
    [[ ${output} == *'  gmp_add (guessed: ext-gmp)'* ]]
    [[ ${output} == *'  Nonexistent\Vendor\Thing'* ]]
}

@test "require-checker: writes a summary table" {
    write_require_checker_report
    GITHUB_STEP_SUMMARY="${SCRATCH}/summary.md" run bash "${COMPOSER_ANNOTATIONS_SCRIPT}" require-checker "${SCRATCH}/crc.json"
    [[ ${status} -eq 0 ]]
    summary=$(< "${SCRATCH}/summary.md")
    [[ ${summary} == *'## Composer Require Checker: unknown symbols'* ]]
    [[ ${summary} == *'| `gmp_add` | ext-gmp |'* ]]
    [[ ${summary} == *'| `Nonexistent\Vendor\Thing` | none |'* ]]
}

@test "require-checker: output that is not the JSON report is shown as is" {
    printf 'PHP Fatal error: Allowed memory size exhausted\n' > "${SCRATCH}/crc.json"
    GITHUB_ACTIONS=true run bash "${COMPOSER_ANNOTATIONS_SCRIPT}" require-checker "${SCRATCH}/crc.json"
    [[ ${status} -eq 0 ]]
    [[ ${output} == *'PHP Fatal error: Allowed memory size exhausted'* ]]
    [[ ${output} == *'::error file=composer.json,title=Composer Require Checker::Composer Require Checker did not produce a report'* ]]
}

@test "require-checker: a report with no unknown symbols is treated as a tool failure" {
    printf '{"unknown-symbols": {}}\n' > "${SCRATCH}/crc.json"
    GITHUB_ACTIONS=true run bash "${COMPOSER_ANNOTATIONS_SCRIPT}" require-checker "${SCRATCH}/crc.json"
    [[ ${status} -eq 0 ]]
    [[ ${output} == *'title=Composer Require Checker::Composer Require Checker did not produce a report'* ]]
    [[ ${output} != *'Unknown symbol'* ]]
}

@test "validate: annotates each finding, lock-file findings on composer.lock" {
    write_validate_output
    GITHUB_ACTIONS=true run bash "${COMPOSER_ANNOTATIONS_SCRIPT}" validate "${SCRATCH}/validate.txt"
    [[ ${status} -eq 0 ]]
    [[ ${output} == *'::error file=composer.json,title=composer validate::require.foo/bar : unbound version constraints (*) should be avoided'* ]]
    [[ ${output} == *'::error file=composer.lock,title=composer validate::Required package "foo/bar" is not present in the lock file.'* ]]
    [[ ${output} == *'# General warnings'* ]]
}

@test "validate: output with no findings still gets one annotation" {
    printf './composer.json does not contain valid JSON\n' > "${SCRATCH}/validate.txt"
    GITHUB_ACTIONS=true run bash "${COMPOSER_ANNOTATIONS_SCRIPT}" validate "${SCRATCH}/validate.txt"
    [[ ${status} -eq 0 ]]
    [[ ${output} == *'::error file=composer.json,title=composer validate::composer.json failed strict validation; see the job log.'* ]]
}

@test "validate: summary holds the output in a fenced block" {
    write_validate_output
    GITHUB_STEP_SUMMARY="${SCRATCH}/summary.md" run bash "${COMPOSER_ANNOTATIONS_SCRIPT}" validate "${SCRATCH}/validate.txt"
    [[ ${status} -eq 0 ]]
    summary=$(< "${SCRATCH}/summary.md")
    [[ ${summary} == *'## composer validate --strict failed'* ]]
    [[ ${summary} == *'```text'$'\n''./composer.json is valid, but with a few warnings'* ]]
}

@test "normalize: one annotation and the diff in the summary" {
    printf -- '--- original\n+++ normalized\n-  "b": 1,\n+  "a": 2,\n' > "${SCRATCH}/normalize.txt"
    GITHUB_ACTIONS=true GITHUB_STEP_SUMMARY="${SCRATCH}/summary.md" run bash "${COMPOSER_ANNOTATIONS_SCRIPT}" normalize "${SCRATCH}/normalize.txt"
    [[ ${status} -eq 0 ]]
    [[ ${output} == *'::error file=composer.json,title=composer normalize::composer.json is not normalized.'* ]]
    summary=$(< "${SCRATCH}/summary.md")
    [[ ${summary} == *'```diff'$'\n''--- original'* ]]
}

@test "normalize: output that is not a diff is reported as a failed run" {
    printf 'Command "normalize" is not defined.\n' > "${SCRATCH}/normalize.txt"
    GITHUB_ACTIONS=true GITHUB_STEP_SUMMARY="${SCRATCH}/summary.md" run bash "${COMPOSER_ANNOTATIONS_SCRIPT}" normalize "${SCRATCH}/normalize.txt"
    [[ ${status} -eq 0 ]]
    [[ ${output} == *'::error file=composer.json,title=composer normalize::composer normalize failed without a diff; see the job log.'* ]]
    [[ ${output} != *'is not normalized'* ]]
    summary=$(< "${SCRATCH}/summary.md")
    [[ ${summary} == *'## composer normalize failed'* ]]
    [[ ${summary} == *'Command "normalize" is not defined.'* ]]
}

@test "validate: lock errors then general warnings each land on their own file" {
    cat > "${SCRATCH}/validate.txt" <<'TEXT'
./composer.json is valid but your composer.lock has some errors
# Lock file errors
- The lock file is not up to date with the latest changes in composer.json.
# General warnings
- require.foo/bar : unbound version constraints (*) should be avoided
TEXT
    GITHUB_ACTIONS=true run bash "${COMPOSER_ANNOTATIONS_SCRIPT}" validate "${SCRATCH}/validate.txt"
    [[ ${status} -eq 0 ]]
    [[ ${output} == *'::error file=composer.lock,title=composer validate::The lock file is not up to date'* ]]
    [[ ${output} == *'::error file=composer.json,title=composer validate::require.foo/bar : unbound'* ]]
}

@test "summary fence outgrows backticks in the content" {
    printf -- '--- original\n+++ normalized\n+  "a": "``` b"\n' > "${SCRATCH}/normalize.txt"
    GITHUB_STEP_SUMMARY="${SCRATCH}/summary.md" run bash "${COMPOSER_ANNOTATIONS_SCRIPT}" normalize "${SCRATCH}/normalize.txt"
    [[ ${status} -eq 0 ]]
    summary=$(< "${SCRATCH}/summary.md")
    [[ ${summary} == *'````diff'$'\n''--- original'* ]]
    [[ ${summary} == *'+  "a": "``` b"'$'\n''````'* ]]
}

@test "annotation messages escape percent signs" {
    printf -- '- 100%% of the time\n' > "${SCRATCH}/validate.txt"
    GITHUB_ACTIONS=true run bash "${COMPOSER_ANNOTATIONS_SCRIPT}" validate "${SCRATCH}/validate.txt"
    [[ ${status} -eq 0 ]]
    [[ ${output} == *'::error file=composer.json,title=composer validate::100%25 of the time'* ]]
}

@test "annotation messages escape a carriage return" {
    printf -- '- finding from a CRLF line\r\n' > "${SCRATCH}/validate.txt"
    GITHUB_ACTIONS=true run bash "${COMPOSER_ANNOTATIONS_SCRIPT}" validate "${SCRATCH}/validate.txt"
    [[ ${status} -eq 0 ]]
    [[ ${output} == *'::error file=composer.json,title=composer validate::finding from a CRLF line%0D'* ]]
}

@test "usage errors exit 2" {
    run bash "${COMPOSER_ANNOTATIONS_SCRIPT}" validate
    [[ ${status} -eq 2 ]]
    run bash "${COMPOSER_ANNOTATIONS_SCRIPT}" unknown "${SCRATCH}/missing"
    [[ ${status} -eq 2 ]]
    touch "${SCRATCH}/present"
    run bash "${COMPOSER_ANNOTATIONS_SCRIPT}" unknown "${SCRATCH}/present"
    [[ ${status} -eq 2 ]]
}

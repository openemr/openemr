# BATS tests for .github/scripts/legacy-new-files.sh
#
# Runs the script against a synthetic git repo (see helpers.bash). Covers:
#   - new PHP / .inc files under interface/ (incl. interface/modules/),
#     portal/ and library/ -> fail, listing each file
#   - new .tpl anywhere -> fail
#   - new files in src/, new .twig files, non-PHP files in legacy
#     directories -> pass
#   - moves out of legacy directories, between legacy paths, and renames
#     of existing templates -> pass
#   - moves into a legacy directory from elsewhere -> fail
#   - the failure message names where code goes instead
#   - annotations only under GITHUB_ACTIONS=true
#   - usage error

load 'helpers'

setup() {
    setup_test_repo
}

teardown() {
    teardown_test_repo
}

@test "no forbidden additions -> pass" {
    write_file src/Feature/NewService.php 'new service'
    write_file templates/feature/new.html.twig 'new twig'
    write_file interface/forms/feature/new.js 'new js'
    commit_all
    run bash "${LEGACY_NEW_FILES_SCRIPT}" base HEAD
    [[ ${status} -eq 0 ]]
    [[ ${output} == *'No new PHP files in legacy directories'* ]]
}

@test "new PHP and .inc files in each legacy directory -> fail listing each" {
    write_file interface/new_page.php 'a'
    write_file interface/modules/custom_modules/oe-module-new/start.php 'b'
    write_file portal/new.php 'c'
    write_file library/new.inc.php 'd'
    write_file library/old_style.inc 'e'
    commit_all
    run bash "${LEGACY_NEW_FILES_SCRIPT}" base HEAD
    [[ ${status} -eq 1 ]]
    [[ ${output} == *'  interface/new_page.php'* ]]
    [[ ${output} == *'  interface/modules/custom_modules/oe-module-new/start.php'* ]]
    [[ ${output} == *'  portal/new.php'* ]]
    [[ ${output} == *'  library/new.inc.php'* ]]
    [[ ${output} == *'  library/old_style.inc'* ]]
}

@test "new Smarty template anywhere -> fail" {
    write_file templates/feature/new.tpl 'a'
    write_file src/Templates/other.tpl 'b'
    commit_all
    run bash "${LEGACY_NEW_FILES_SCRIPT}" base HEAD
    [[ ${status} -eq 1 ]]
    [[ ${output} == *'  templates/feature/new.tpl'* ]]
    [[ ${output} == *'  src/Templates/other.tpl'* ]]
}

@test "failure message says where code goes instead" {
    write_file interface/new_page.php 'a'
    commit_all
    run bash "${LEGACY_NEW_FILES_SCRIPT}" base HEAD
    [[ ${status} -eq 1 ]]
    [[ ${output} == *'class under src/'* ]]
    [[ ${output} == *'Twig template'* ]]
    [[ ${output} == *'install it with Composer'* ]]
    [[ ${output} != *'::error'* ]]
}

@test "GITHUB_ACTIONS=true -> one error annotation per file" {
    write_file interface/new_page.php 'a'
    write_file portal/new.php 'b'
    commit_all
    GITHUB_ACTIONS=true run bash "${LEGACY_NEW_FILES_SCRIPT}" base HEAD
    [[ ${status} -eq 1 ]]
    [[ ${output} == *'::error file=interface/new_page.php::'* ]]
    [[ ${output} == *'::error file=portal/new.php::'* ]]
}

@test "moving a legacy file into src/ -> pass" {
    git mv library/existing.inc.php src/ExistingHelper.php
    commit_all
    run bash "${LEGACY_NEW_FILES_SCRIPT}" base HEAD
    [[ ${status} -eq 0 ]]
}

@test "moving a file between legacy paths -> pass" {
    mkdir -p interface/moved templates/moved
    git mv interface/existing/page.php interface/moved/page.php
    git mv templates/existing.tpl templates/moved/existing.tpl
    commit_all
    run bash "${LEGACY_NEW_FILES_SCRIPT}" base HEAD
    [[ ${status} -eq 0 ]]
}

@test "moving a file into a legacy directory from src/ -> fail" {
    git mv src/Existing.php library/existing_class.php
    commit_all
    run bash "${LEGACY_NEW_FILES_SCRIPT}" base HEAD
    [[ ${status} -eq 1 ]]
    [[ ${output} == *'  library/existing_class.php'* ]]
}

@test "modifying an existing legacy file -> pass" {
    printf 'more\n' >> interface/existing/page.php
    commit_all
    run bash "${LEGACY_NEW_FILES_SCRIPT}" base HEAD
    [[ ${status} -eq 0 ]]
}

@test "wrong argument count -> usage error" {
    run bash "${LEGACY_NEW_FILES_SCRIPT}" base
    [[ ${status} -eq 2 ]]
    [[ ${output} == *'Usage:'* ]]
}

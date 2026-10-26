# Helpers for BATS tests of .github/scripts/legacy-new-files.sh.
#
# Loaded by legacy-new-files.bats via `load 'helpers'`. Builds a synthetic
# git repo whose `base` branch holds a few existing files, so each test commits
# its change on top and runs the script against `base HEAD`.

__HELPERS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck disable=SC2034
LEGACY_NEW_FILES_SCRIPT="$(cd "${__HELPERS_DIR}/../../../.." && pwd)/.github/scripts/legacy-new-files.sh"

setup_test_repo() {
    # `mktemp -d` (no `-t <template>`) -- BusyBox's mktemp in the
    # bats/bats:1.13.0 Alpine image rejects the `-t` form.
    REPO=$(mktemp -d)
    cd "${REPO}" || exit 1
    git init -q -b master
    git config user.email 'test@example.com'
    git config user.name 'Test'
    write_file library/existing.inc.php 'existing library helper'
    write_file interface/existing/page.php 'existing interface page'
    write_file templates/existing.tpl 'existing smarty template'
    write_file src/Existing.php 'existing class'
    git add -A
    git commit -q -m 'seed'
    git branch base
    # Plain output regardless of caller environment; the annotation form
    # is asserted explicitly where it matters.
    unset GITHUB_ACTIONS
}

teardown_test_repo() {
    cd / || exit 1
    rm -rf "${REPO}"
}

# Write enough distinct content that rename detection pairs moves correctly.
write_file() {
    local path=$1 marker=$2
    mkdir -p "$(dirname "${path}")"
    printf '<?php\n// %s\n%s\n' "${marker}" "$(seq 1 40)" > "${path}"
}

commit_all() {
    git add -A
    git commit -q -m change
}

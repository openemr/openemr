#!/usr/bin/env bash
#
# Fail when a change adds PHP to a legacy directory or adds a Smarty template.
#
# interface/, portal/ and library/ (including interface/modules/) are frozen:
# new logic goes in src/ classes, markup in Twig templates, and new modules in
# their own repositories installed with Composer. See openemr/openemr#14764.
#
# Usage: legacy-new-files.sh <base> <head>
#
# Compares the two commits with rename detection. A file moved into a
# forbidden location from an allowed one counts as added. A file moved out of
# one (for example from library/ into src/), or between forbidden locations,
# does not.
#
# Exit codes
#   0  nothing forbidden was added
#   1  at least one forbidden file was added; the files and what to do
#      instead are printed on stdout
#   2  usage error

set -euo pipefail

# Paths the policy forbids adding: PHP in a legacy directory, or a Smarty
# template anywhere.
readonly FORBIDDEN_RE='^(interface|portal|library)/.*\.(php|inc)$|\.tpl$'

usage() {
    printf 'Usage: %s <base> <head>\n' "${0##*/}"
}

report() {
    local path
    if [[ ${GITHUB_ACTIONS:-} = true ]]; then
        for path in "$@"; do
            printf '::error file=%s::New file in a frozen legacy location; see the job log for where it belongs.\n' "${path}"
        done
    fi
    printf 'This pull request adds files that the legacy-directory policy forbids:\n\n'
    printf '  %s\n' "$@"
    cat <<'EOF'

interface/, portal/ and library/ (including interface/modules/) are frozen,
and Smarty is not used for new work. Instead:

  - Put logic in a class under src/ and call it from the existing legacy file.
  - Put markup in a Twig template. Twig is the only template engine for new work.
  - Put a new module in its own repository and install it with Composer,
    not under interface/modules/.

Moving an existing file out of these directories (for example into src/) does
not trip this check. Rationale: https://github.com/openemr/openemr/issues/14764
EOF
}

main() {
    if (( $# != 2 )); then
        usage >&2
        exit 2
    fi
    local base=$1 head=$2
    local status from to
    local -a added=()

    # -z keeps unusual file names intact; it needs a file because command
    # substitution drops NUL bytes. diff_file stays global so the EXIT trap
    # can still see it after main returns.
    diff_file=$(mktemp)
    trap 'rm -f "${diff_file}"' EXIT
    git diff --no-ext-diff --name-status -z -M --diff-filter=AR "${base}" "${head}" > "${diff_file}"

    while IFS= read -r -d '' status; do
        IFS= read -r -d '' from
        to=${from}
        if [[ ${status} = R* ]]; then
            IFS= read -r -d '' to
            if [[ ${from} =~ ${FORBIDDEN_RE} ]]; then
                continue
            fi
        fi
        if [[ ${to} =~ ${FORBIDDEN_RE} ]]; then
            added+=("${to}")
        fi
    done < "${diff_file}"

    if (( ${#added[@]} == 0 )); then
        echo 'No new PHP files in legacy directories and no new Smarty templates.'
        return 0
    fi
    report "${added[@]}"
    exit 1
}

main "$@"

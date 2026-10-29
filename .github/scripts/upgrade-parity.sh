#!/usr/bin/env bash
#
# Does upgrading a database give the same result as a fresh install?
#
# Loads the previous release's sql/database.sql into one database and this
# checkout's into another, runs this checkout's upgrade file over the first
# with OpenEMR's SQLUpgradeService (upgrade-parity-run.php), and compares the
# two: every table, column and index, and every row database.sql seeds.
# Auto-increment ids are left out, and timestamps written while loading
# (NOW() and CURRENT_TIMESTAMP defaults) are treated as equal.
#
# A database.sql change with no matching upgrade SQL shows up here whatever
# statement it is, which a keyword check on the diff can't promise.
#
# Invoked by .github/workflows/upgrade-parity.yml, from the repo root, once
# MySQL is running and sites/default/sqlconf.php names parity_fresh.
#
# Environment:
#   PARITY_MYSQL     client command
#                    (default: mysql --host=127.0.0.1 --user=root --password=root)
#   PARITY_FROM_TAG  release tag to upgrade from
#                    (default: derived from the sql/*-to-*_upgrade.sql names;
#                    between a release branch's cut and its tag, that tag
#                    doesn't exist yet, so the release branch is used: the
#                    one master's .github/release-targets.yml names for the
#                    version)
#
# Exit codes:
#   0  identical
#   1  differences, listed on stdout and in $GITHUB_STEP_SUMMARY
#   2  the comparison could not run

set -Eeuo pipefail
trap 'echo "::error::upgrade-parity: failed at line ${LINENO}" >&2; exit 2' ERR

summary=${GITHUB_STEP_SUMMARY:-/dev/null}
work=$(mktemp -d)

q() {
    # shellcheck disable=SC2086  # PARITY_MYSQL is a command with arguments
    ${PARITY_MYSQL:-mysql --host=127.0.0.1 --user=root --password=root} --batch --skip-column-names "$@"
}

# Which release, and which upgrade file. The highest from-version among the
# upgrade files is the one this checkout's newest upgrade file starts at.
# shellcheck source=./.github/scripts/lib/derive-from-version.sh
source .github/scripts/lib/derive-from-version.sh
from=$(derive_from_version_sql_candidates . | tail -n 1)
upgrade=$(find sql -maxdepth 1 -name "${from//./_}-to-*_upgrade.sql" | sort | tail -n 1)
tag=${PARITY_FROM_TAG:-v${from//./_}}
if [[ -z "${upgrade}" ]]; then
    echo "::error::upgrade-parity: no sql/${from//./_}-to-*_upgrade.sql" >&2
    exit 2
fi

# The release branch master's release-targets.yml names for a version: the row
# whose docker_tags list the version or whose openemr_version_ref is its tag.
# One branch serves a release line (8.4.0 and 8.4.1 are both rel-840), so the
# name can't be built from the version's digits. Inline "# ..." comments and
# quotes are ignored, as in lib/release-targets-queries.sh.
release_branch_for() {
    awk -v version="$2" -v tag="$3" -v squote="'" '
        function value(line) {
            line = substr(line, index(line, ":") + 1)
            sub(/[[:space:]]+#.*$/, "", line)
            gsub(/["[:space:]]/, "", line)
            gsub(squote, "", line)
            return line
        }
        /^-[[:space:]]*branch:/ { branch = value($0); next }
        branch != "" && /^[[:space:]]+docker_tags:/ {
            n = split(value($0), tags, ",")
            for (i = 1; i <= n; i++) if (tags[i] == version) { print branch; exit }
        }
        branch != "" && /^[[:space:]]+openemr_version_ref:/ && value($0) == tag { print branch; exit }
    ' "$1"
}

# A shallow fetch, stopping the run with git's own reason if it fails. On
# success its stderr is dropped: a depth-1 fetch can warn about the parent
# commits it deliberately leaves out.
fetch_or_stop() {
    local message=$1
    shift
    if ! git fetch --quiet --no-tags --depth=1 origin "$@" 2> "${work}/fetch.err"; then
        reason=$(tail -n 1 "${work}/fetch.err" || true)
        echo "::error::upgrade-parity: ${message}: ${reason}" >&2
        exit 2
    fi
}

# Once a release ships, its tag is the base. Between the release branch's cut
# and its tag, master's newest upgrade file already starts at the new version
# while the tag doesn't exist yet, so the release branch stands in for it.
# Only a tag origin doesn't have falls back to the branch: a failed request is
# an error, since a branch can carry changes newer than the shipped tag.
if git rev-parse -q --verify "refs/tags/${tag}" >/dev/null; then
    tag_on_origin=yes
elif ! remote_tag=$(git ls-remote --tags --refs origin "refs/tags/${tag}" 2> "${work}/ls-remote.err"); then
    reason=$(tail -n 1 "${work}/ls-remote.err" || true)
    echo "::error::upgrade-parity: couldn't ask origin for the ${tag} tag: ${reason}" >&2
    exit 2
elif [[ -n "${remote_tag}" ]]; then
    tag_on_origin=yes
    fetch_or_stop "origin has the ${tag} tag, but fetching it failed" "refs/tags/${tag}:refs/tags/${tag}"
else
    tag_on_origin=no
fi

if [[ ${tag_on_origin} == yes ]]; then
    base=${tag}
    base_name=${tag}
elif [[ -z "${PARITY_FROM_TAG:-}" ]]; then
    # release-targets.yml is read from master, which is its source of truth:
    # this workflow also runs on pull requests into rel-* branches, whose copy
    # is the snapshot taken at their cut (see the file's own header).
    fetch_or_stop "no ${tag} tag yet, and fetching master for its release-targets.yml failed" \
        "refs/heads/master:refs/remotes/origin/master"
    git show "origin/master:.github/release-targets.yml" > "${work}/release-targets.yml"
    branch=$(release_branch_for "${work}/release-targets.yml" "${from}" "${tag}")
    if [[ -n "${branch}" ]]; then
        source_note="the release-targets.yml row for ${from}"
    else
        IFS=. read -r major minor _ <<< "${from}"
        branch=rel-${major}${minor}0
        source_note="no release-targets.yml row names ${from}, so the line's usual name"
    fi
    fetch_or_stop "no ${tag} tag yet, and the release branch ${branch} (${source_note}) couldn't be fetched" \
        "refs/heads/${branch}:refs/remotes/origin/${branch}"
    base=origin/${branch}
    base_name="${branch} (no ${tag} tag yet; ${source_note})"
    echo "::notice::upgrade-parity: no ${tag} tag yet, so comparing against the release branch ${branch} (${source_note})"
else
    echo "::error::upgrade-parity: no tag ${tag} on origin (PARITY_FROM_TAG)" >&2
    exit 2
fi
echo "fresh: this checkout's sql/database.sql"
echo "upgraded: ${base_name}'s sql/database.sql + ${upgrade}"

for db in parity_fresh parity_upgraded; do
    q -e "DROP DATABASE IF EXISTS ${db}; CREATE DATABASE ${db} CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
done
q parity_fresh < sql/database.sql
git show "${base}:sql/database.sql" | q parity_upgraded
php .github/scripts/upgrade-parity-run.php "${upgrade}"

# Timestamps from today or yesterday (UTC) were written by the load itself.
today=$(date -u +%F)
yesterday=$(date -u -d yesterday +%F)
loaded="(${today}|${yesterday}) [0-9]{2}:[0-9]{2}:[0-9]{2}"

schema() {
    q -e "SELECT 'table', table_name, engine, table_collation
            FROM information_schema.tables WHERE table_schema = '$1' ORDER BY 2;
          SELECT 'column', table_name, column_name, column_type, is_nullable,
                 IFNULL(column_default, '<null>'), extra, IFNULL(collation_name, '')
            FROM information_schema.columns WHERE table_schema = '$1' ORDER BY 2, 3;
          SELECT 'index', table_name, index_name, non_unique, seq_in_index, column_name, IFNULL(sub_part, '')
            FROM information_schema.statistics WHERE table_schema = '$1' ORDER BY 2, 3, 5;"
}

# Rows of every table both databases have, over the columns both have (the
# schema comparison reports the rest), auto-increment ids left out. facility.id
# stays in: the seed fixes it at 3, and the installer's first user refers to it.
# The client reads /dev/null so it can't consume the table list the loop is reading.
rows() {
    local table cols table_list
    # Captured first, so a failed query stops the script instead of reading as no tables.
    table_list=$(q -e "SELECT f.table_name FROM information_schema.tables f
                         JOIN information_schema.tables u ON u.table_schema = 'parity_upgraded' AND u.table_name = f.table_name
                        WHERE f.table_schema = 'parity_fresh' ORDER BY 1")
    while read -r table; do
        cols=$(q -e "SET SESSION group_concat_max_len = 1000000;
                     SELECT GROUP_CONCAT(CONCAT('\`', f.column_name, '\`') ORDER BY f.ordinal_position)
                       FROM information_schema.columns f
                       JOIN information_schema.columns u
                         ON u.table_schema = 'parity_upgraded' AND u.table_name = f.table_name AND u.column_name = f.column_name
                      WHERE f.table_schema = 'parity_fresh' AND f.table_name = '${table}'
                        AND (f.extra NOT LIKE '%auto_increment%'
                             OR (f.table_name = 'facility' AND f.column_name = 'id'))" < /dev/null)
        [[ -n "${cols}" && "${cols}" != NULL ]] || continue
        q -e "SELECT ${cols} FROM \`$1\`.\`${table}\`" < /dev/null | sed -E "s/${loaded}/<load-time>/g" | LC_ALL=C sort | sed "s/^/${table}\t/"
    done <<< "${table_list}"
}

for db in parity_fresh parity_upgraded; do
    schema "${db}" > "${work}/${db}.schema"
    rows "${db}" > "${work}/${db}.rows"
done

# A probe must be able to come back empty for the wrong reason; refuse that.
columns=$(grep -c '^column' "${work}/parity_fresh.schema" || true)
seeded=$(wc -l < "${work}/parity_fresh.rows")
if [[ ${columns} -lt 1000 || ${seeded} -lt 1000 ]]; then
    echo "::error::upgrade-parity: the fresh database looks empty; not comparing" >&2
    exit 2
fi

status=0
for part in schema rows; do
    if ! diff -u --label "fresh install" --label "upgraded from ${base_name}" \
        "${work}/parity_fresh.${part}" "${work}/parity_upgraded.${part}" > "${work}/${part}.diff"; then
        status=1
    fi
done

if [[ ${status} -eq 0 ]]; then
    echo "identical: ${columns} columns, ${seeded} seeded rows"
    {
        echo '## ✅ Upgrade matches a fresh install'
        echo
        echo "\`${base_name}\` + \`${upgrade}\` gives the same schema and seeded rows (${columns} columns, ${seeded} rows) as this checkout's \`sql/database.sql\`."
    } >> "${summary}"
    exit 0
fi

{
    echo '## ⚠️ Upgrade does not match a fresh install'
    echo
    echo "Upgrading \`${base_name}\` with \`${upgrade}\` gives a different database from this checkout's \`sql/database.sql\`."
    # shellcheck disable=SC2016  # the backticks are Markdown, not command substitution
    echo 'Usually a change to `sql/database.sql` needs the same change in the upgrade file, or the reverse.'
    # shellcheck disable=SC2016  # the backticks are Markdown, not command substitution
    echo 'Lines starting `-` exist only in a fresh install; `+` only after upgrading.'
    for part in schema rows; do
        if [[ -s "${work}/${part}.diff" ]]; then
            echo
            echo "### ${part}"
            echo
            echo '```diff'
            tail -n +3 "${work}/${part}.diff" | grep -E '^[-+]' | head -n 200 || true
            echo '```'
        fi
    done
} | tee -a "${summary}"
exit 1

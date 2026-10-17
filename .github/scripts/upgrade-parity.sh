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
#                    (default: derived from the sql/*-to-*_upgrade.sql names)
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
if ! git rev-parse -q --verify "refs/tags/${tag}" >/dev/null; then
    git fetch --quiet --no-tags --depth=1 origin "refs/tags/${tag}:refs/tags/${tag}"
fi
echo "fresh: this checkout's sql/database.sql"
echo "upgraded: ${tag}'s sql/database.sql + ${upgrade}"

for db in parity_fresh parity_upgraded; do
    q -e "DROP DATABASE IF EXISTS ${db}; CREATE DATABASE ${db} CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
done
q parity_fresh < sql/database.sql
git show "${tag}:sql/database.sql" | q parity_upgraded
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
# schema comparison reports the rest), auto-increment ids left out. The client
# reads /dev/null so it can't consume the table list the loop is reading.
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
                        AND f.extra NOT LIKE '%auto_increment%'" < /dev/null)
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
    if ! diff -u --label "fresh install" --label "upgraded from ${tag}" \
        "${work}/parity_fresh.${part}" "${work}/parity_upgraded.${part}" > "${work}/${part}.diff"; then
        status=1
    fi
done

if [[ ${status} -eq 0 ]]; then
    echo "identical: ${columns} columns, ${seeded} seeded rows"
    {
        echo '## ✅ Upgrade matches a fresh install'
        echo
        echo "\`${tag}\` + \`${upgrade}\` gives the same schema and seeded rows (${columns} columns, ${seeded} rows) as this checkout's \`sql/database.sql\`."
    } >> "${summary}"
    exit 0
fi

{
    echo '## ⚠️ Upgrade does not match a fresh install'
    echo
    echo "Upgrading \`${tag}\` with \`${upgrade}\` gives a different database from this checkout's \`sql/database.sql\`."
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

#!/usr/bin/env bash
#
# Fail if any `CREATE TABLE` in the core openemr schema SQL files lacks
# an explicit `PRIMARY KEY`. Catches regressions where a new table is
# added without one.
#
# Why every table needs a PRIMARY KEY in this codebase:
#
#   * Doctrine ORM mapping (every entity requires an #[ORM\Id] field).
#     openemr is actively migrating toward Doctrine — see AGENTS.md:
#     "Database: MySQL via Doctrine DBAL 4.x (ADODB surface API for
#     legacy code)". DBAL is in place today, ORM requires PKs on
#     every mapped entity.
#   * InnoDB row clustering. Without an explicit PK, InnoDB generates
#     a hidden 6-byte row ID and clusters by that; secondary-index
#     lookups do an extra pointer-chase. Explicit PK removes the
#     indirection — measurable speedup on hot lookup paths.
#   * Standard-compliance posture. HIPAA / NIST SP 800-53 database
#     hardening guides list "every table must have a unique row
#     identifier" as a basic control.
#   * Galera / synchronous-replication compatibility (opt-in path
#     for openemr-devops users). Galera requires PRIMARY KEY on
#     every replicated table — Galera's cert pass uses it for
#     row identification during commit.
#
# Files scanned (both the fresh-install schema and the vendored
# language-tables SQL that OVERRIDES sql/database.sql's lang_*
# definitions during install):
#
#   * sql/database.sql
#   * contrib/util/language_translations/currentLanguage_utf8.sql
#
# Invocation:
#
#   tests/schema/assert-primary-keys.sh                 # scan default files
#   tests/schema/assert-primary-keys.sh path/to/one.sql # scan specified files instead
#
# Exit codes:
#
#   0 — every CREATE TABLE has a PRIMARY KEY
#   1 — one or more tables lack a PRIMARY KEY (listed on stderr)

set -euo pipefail

if [[ "$#" -gt 0 ]]; then
    files=("$@")
else
    # Resolve the repo root from this script's location so the hook can be
    # invoked from any working directory.
    script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
    repo_root=$(cd -- "${script_dir}/../.." && pwd)
    files=(
        "${repo_root}/sql/database.sql"
        "${repo_root}/contrib/util/language_translations/currentLanguage_utf8.sql"
    )
fi

# Scan each file and collect `<file>: <table>` entries for every CREATE
# TABLE that reaches its `) ENGINE=` close without a `PRIMARY KEY` line.
# IGNORECASE handles the mixed `auto_increment` / `AUTO_INCREMENT` / etc.
# shapes present in the codebase.
missing=""
for f in "${files[@]}"; do
    if [[ ! -f "${f}" ]]; then
        # A caller-specified file that does not exist (or a default schema
        # file that was renamed) is a gate failure, not a skip — otherwise
        # a typo silently drops coverage.
        echo "ERROR: ${f} not found" >&2
        exit 1
    fi
    # shellcheck disable=SC2016  # $0 is awk's, not the shell's
    found=$(awk -v file="${f}" '
        BEGIN { IGNORECASE=1 }
        function flush_open_block() {
            # Report the still-open CREATE TABLE block if one is tracked
            # and we have not seen a PRIMARY KEY inside it yet. Used
            # when a NEW CREATE TABLE begins before we saw a close, or
            # at EOF if the final block is unterminated.
            if (in_table && !has_pk) printf "  %s: %s\n", file, tbl
        }
        /^[[:space:]]*CREATE[[:space:]]+TABLE/ {
            flush_open_block()   # catches a prior unterminated block
            # $3 holds the table identifier whether the line is bare or
            # indented, because awk resplits on whitespace.
            tbl = $3
            has_pk = 0
            in_table = 1
            next
        }
        in_table && /PRIMARY KEY/ { has_pk = 1 }
        in_table && /^[[:space:]]*\) *ENGINE/ {
            if (!has_pk) printf "  %s: %s\n", file, tbl
            in_table = 0
        }
        END { flush_open_block() }   # catches an unterminated final block
    ' "${f}")
    missing="${missing}${found}"
done

if [[ -n "${missing}" ]]; then
    {
        echo "ERROR: these CREATE TABLE statements lack an explicit PRIMARY KEY:"
        echo
        printf '%s' "${missing}"
        echo
        echo "Every openemr-core table must declare a PRIMARY KEY — see this"
        echo "script's header comment for the reasons (Doctrine ORM, InnoDB"
        echo "clustering, standard-compliance, Galera-compat)."
        echo
        echo "Fix: add PRIMARY KEY to the table in the CREATE TABLE definition"
        echo "AND add an idempotent migration to the current sql_upgrade file"
        echo "(sql/8_5_0-to-8_6_0_upgrade.sql on master as of 2026-11-12)."
    } >&2
    exit 1
fi

exit 0

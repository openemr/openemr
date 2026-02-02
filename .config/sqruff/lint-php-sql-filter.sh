#!/bin/bash
# Filter PHP files that contain SQL and lint them
# Usage: lint-php-sql-filter.sh [file1.php file2.php ...]
#
# If no files provided, scans all PHP files in the repo.
# Otherwise, filters provided files to only those containing SQL calls.

set -e

SQL_PATTERN='(sqlStatement|sqlQuery|sqlInsert|QueryUtils::|->Execute|->GetOne|->GetAll|->GetRow)'

if (( $# == 0 )); then
    # No files provided - scan all PHP files with SQL
    # shellcheck disable=SC2312
    git grep -l -zE "${SQL_PATTERN}" -- '*.php' '*.inc' | xargs -0 php .config/sqruff/lint-php-sql.php || true
else
    # Filter provided files to only those with SQL
    files_with_sql=()
    for file in "$@"; do
        if grep -qE "${SQL_PATTERN}" "${file}" 2>/dev/null; then
            files_with_sql+=("${file}")
        fi
    done

    if (( ${#files_with_sql[@]} > 0 )); then
        php .config/sqruff/lint-php-sql.php "${files_with_sql[@]}"
    fi
fi

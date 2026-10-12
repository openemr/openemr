#!/usr/bin/env bash
#
# Print the Codecov flag for each CI config, one "<docker_dir> <flag>" line
# per config. Reads the JSON that ci/parse_docker_dir.sh prints on stdin.
#
# Each CI config needs its own flag. Codecov carries coverage forward per
# flag, so configs sharing a name replace each other's coverage instead of
# adding to it. Fails if a config name has a variant suffix with no flag
# suffix below, or if two configs get the same flag; run over every config
# (as the collect jobs in test-all.yml and test-scheduled.yml do) to catch
# the second.
#
# Codecov limits flag names to 45 characters, so the variant suffixes are
# short; "Check Codecov flag lengths" in test.yml enforces the limit.

set -euo pipefail

rows=$(jq -r '.[].output | [.docker_dir, .php, .webserver, .database, .db] | @tsv')
if [[ -z "${rows}" ]]; then
    echo '::error::No CI configs to compute Codecov flags for' >&2
    exit 1
fi

status=0
declare -A owner=()
while IFS=$'\t' read -r docker_dir php webserver database db; do
    # Database major.minor only, so point-release bumps keep the same flag.
    [[ "${db}" =~ ^([0-9]+\.[0-9]+) ]] && db="${BASH_REMATCH[1]}"
    flag="php${php}-${webserver}-${database}${db:+-${db}}"
    case "${docker_dir}" in
        *_upgrade) flag="${flag}-upg" ;;
        *_redis_sentinel_mtls) flag="${flag}-rsm" ;;
        *_redis_sentinel_tls) flag="${flag}-rst" ;;
        *_redis_sentinel) flag="${flag}-rs" ;;
        *)
            if [[ ! "${docker_dir}" =~ ^[a-z]+(_[0-9]+)+$ ]]; then
                echo "::error::No Codecov flag suffix for config '${docker_dir}'" >&2
                status=1
                continue
            fi
            ;;
    esac
    if [[ -n "${owner[${flag}]:-}" ]]; then
        echo "::error::Configs '${owner[${flag}]}' and '${docker_dir}' both get Codecov flag '${flag}'" >&2
        status=1
        continue
    fi
    owner[${flag}]="${docker_dir}"
    echo "${docker_dir} ${flag}"
done <<<"${rows}"
exit "${status}"

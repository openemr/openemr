#!/bin/sh

set -eu

repo_root=$(CDPATH='' cd -- "$(dirname -- "$0")/../.." && pwd)
minimum_upload_bytes=$((100 * 1024 * 1024))
checked=0
maximum_post_bytes=0

# Keep these version ranges deliberately narrow. They cover current flex
# (8.3-8.5), current dev-FPM images (8.2-8.6) with their paired plain +
# redis-flavored php.ini files under php-ini/{,redis/}, and the stock
# development-easy-redis config. Historical dev-FPM 5.x-8.1 and production
# binary/release configs are intentionally excluded, as are unrelated generic
# prebuild data/php.ini files.
flex_versions="8.3 8.4 8.5"
dev_fpm_versions="8-2 8-3 8-4 8-5 8-6"

ini_bytes() {
    awk -v setting="$1" '
        /^[[:space:]]*[;#]/ { next }
        $0 ~ "^[[:space:]]*" setting "[[:space:]]*=" {
            value = $0
            sub(/^[^=]*=[[:space:]]*/, "", value)
            sub(/[[:space:]]*[;#].*$/, "", value)
            sub(/[[:space:]]*$/, "", value)
            if (value !~ /^[0-9]+[KkMmGg]?$/) exit 1
            suffix = toupper(substr(value, length(value), 1))
            number = substr(value, 1, length(value) - 1) + 0
            if (suffix == "G") number *= 1024 * 1024 * 1024
            else if (suffix == "M") number *= 1024 * 1024
            else if (suffix == "K") number *= 1024
            else number = value + 0
            result = number
            found++
        }
        END {
            if (found != 1) exit 1
            printf "%.0f\n", result
        }
    ' "$2"
}

check_php_config() {
    relative_path=$1
    config="${repo_root}/${relative_path}"
    if [ ! -f "${config}" ]; then
        echo "Missing development PHP config: ${relative_path}" >&2
        exit 1
    fi

    # shellcheck disable=SC2310 # Parse errors are handled explicitly here.
    if ! upload_bytes=$(ini_bytes upload_max_filesize "${config}"); then
        echo "Expected exactly one active upload_max_filesize in ${relative_path}" >&2
        exit 1
    fi
    # shellcheck disable=SC2310 # Parse errors are handled explicitly here.
    if ! post_bytes=$(ini_bytes post_max_size "${config}"); then
        echo "Expected exactly one active post_max_size in ${relative_path}" >&2
        exit 1
    fi

    if [ "${upload_bytes}" -lt "${minimum_upload_bytes}" ]; then
        echo "${relative_path}: upload_max_filesize must be at least 100M" >&2
        exit 1
    fi
    if [ "${post_bytes}" -le "${upload_bytes}" ]; then
        echo "${relative_path}: post_max_size must exceed upload_max_filesize for multipart overhead" >&2
        exit 1
    fi
    if [ "${post_bytes}" -gt "${maximum_post_bytes}" ]; then
        maximum_post_bytes=${post_bytes}
    fi
    checked=$((checked + 1))
}

check_php_config docker/development-easy-redis/php.ini

for version in ${flex_versions}; do
    check_php_config "docker/flex/configs/php${version}/php.ini"
done

for version in ${dev_fpm_versions}; do
    normal="docker/library/dockers/dev-php-fpm-${version}/php-ini/php.ini"
    redis="docker/library/dockers/dev-php-fpm-${version}/php-ini/redis/php.ini"
    if [ ! -f "${repo_root}/${normal}" ] || [ ! -f "${repo_root}/${redis}" ]; then
        echo "Missing normal/redis development PHP config pair for ${version}" >&2
        exit 1
    fi
    check_php_config "${normal}"
    check_php_config "${redis}"
done

nginx_relative_path=docker/library/dockers/dev-nginx/nginx.conf
nginx_config="${repo_root}/${nginx_relative_path}"
# shellcheck disable=SC2310 # Parse errors are handled explicitly here.
if ! nginx_limit_bytes=$(awk '
    /^[[:space:]]*#/ { next }
    /^[[:space:]]*client_max_body_size[[:space:]]+/ {
        value = $2
        sub(/;.*/, "", value)
        if (value !~ /^[0-9]+[KkMmGg]?$/) exit 1
        suffix = toupper(substr(value, length(value), 1))
        number = substr(value, 1, length(value) - 1) + 0
        if (suffix == "G") number *= 1024 * 1024 * 1024
        else if (suffix == "M") number *= 1024 * 1024
        else if (suffix == "K") number *= 1024
        else number = value + 0
        result = number
        found++
    }
    END {
        if (found != 1) exit 1
        printf "%.0f\n", result
    }
' "${nginx_config}"); then
    echo "Expected exactly one active client_max_body_size in ${nginx_relative_path}" >&2
    exit 1
fi
if [ "${nginx_limit_bytes}" -lt "${maximum_post_bytes}" ]; then
    echo "${nginx_relative_path}: client_max_body_size must be at least the largest development PHP post_max_size" >&2
    exit 1
fi

echo "Validated ${checked} development PHP upload configurations and the development nginx body limit."

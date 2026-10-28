#!/usr/bin/env bash
#
# Mock `gh` for BATS tests of admin-approval.sh.
#
# The script makes two kinds of call:
#   gh api --paginate --slurp repos/<repo>/pulls/<n>/reviews
#   gh api repos/<repo>/collaborators/<login>/permission
#
# Env-var contract:
#
#   MOCK_REVIEWS       JSON printed for the reviews call, already in
#                      --slurp shape (an array of pages). Default [[]].
#   MOCK_REVIEWS_EXIT  exit code for the reviews call. Default 0.
#   MOCK_PERMISSIONS   space-separated login=permission pairs. A login
#                      not listed gets a 404 (exit 1), as real gh does
#                      for a user who isn't a collaborator.
#
# Anything else fails loudly so a script change that adds calls shows up.

set -euo pipefail

if [[ ${1:-} != api ]]; then
    echo "gh-mock: unsupported verb: ${1:-}" >&2
    exit 2
fi
shift

path=''
for arg in "$@"; do
    case ${arg} in
        --*) ;;
        *) path=${arg} ;;
    esac
done

case ${path} in
    repos/*/pulls/*/reviews)
        printf '%s\n' "${MOCK_REVIEWS:-[[]]}"
        exit "${MOCK_REVIEWS_EXIT:-0}"
        ;;
    repos/*/collaborators/*/permission)
        login=${path#repos/*/collaborators/}
        login=${login%/permission}
        for pair in ${MOCK_PERMISSIONS:-}; do
            if [[ ${pair%%=*} = "${login}" ]]; then
                printf '{"permission":"%s"}\n' "${pair#*=}"
                exit 0
            fi
        done
        echo 'gh: Not Found (HTTP 404)' >&2
        exit 1
        ;;
    *)
        echo "gh-mock: unsupported path: ${path}" >&2
        exit 2
        ;;
esac

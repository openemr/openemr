#!/usr/bin/env bash
#
# Report whether a repository admin approved a pull request at its head commit.
#
# Policy gates (the legacy-directory checks of openemr/openemr#14764) pass
# when a repository admin has approved the pull request, so a bugfix that
# can't follow the policy can still land. Only each reviewer's latest
# approve, request-changes or dismissed review counts, and an approval of an
# earlier commit does not: a push after the approval needs a new one.
#
# Inputs (env)
#   REPO       owner/name of the repository
#   PR_NUMBER  pull request number
#   HEAD_SHA   the pull request's current head commit
#   GH_TOKEN   read by gh; needs pull-requests: read
#
# Output: the approving admin's login on stdout.
#
# Exit codes
#   0  an admin approved HEAD_SHA
#   1  no admin approval of HEAD_SHA, or the reviews could not be read

set -euo pipefail

warn() {
    if [[ ${GITHUB_ACTIONS:-} = true ]]; then
        printf '::warning::%s\n' "$1"
    else
        printf 'warning: %s\n' "$1" >&2
    fi
}

main() {
    local repo=${REPO:?REPO must be set (owner/name)}
    local pr_number=${PR_NUMBER:?PR_NUMBER must be set}
    local head_sha=${HEAD_SHA:?HEAD_SHA must be set}
    local approvers login permission

    # Keep each reviewer's latest deciding review, then the ones approving
    # the head commit.
    if ! approvers=$(gh api --paginate --slurp "repos/${repo}/pulls/${pr_number}/reviews" \
        | jq -r --arg sha "${head_sha}" '
            reduce (.[][] | select(.state != "COMMENTED" and .state != "PENDING")) as $r
                ({}; .[$r.user.login] = $r)
            | .[]
            | select(.state == "APPROVED" and .commit_id == $sha)
            | .user.login'); then
        warn "Could not read the reviews of pull request #${pr_number}."
        exit 1
    fi
    while IFS= read -r login; do
        if [[ -z ${login} ]]; then
            continue
        fi
        if ! permission=$(gh api "repos/${repo}/collaborators/${login}/permission" | jq -r '.permission'); then
            warn "Could not read the repository permission of ${login}."
            continue
        fi
        if [[ ${permission} = admin ]]; then
            printf '%s\n' "${login}"
            return 0
        fi
    done <<< "${approvers}"
    exit 1
}

main "$@"

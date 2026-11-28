#!/usr/bin/env bash
#
# Wait for a GitHub Actions run to finish, and exit with its result.
#
# `gh run watch --exit-status` exits non-zero both when the watched
# run fails and when one of its own API calls fails (an HTTP 500 from
# the jobs endpoint, for example). On its own it can't tell the
# caller which one happened, so a GitHub API error reads as "the run
# failed" and a still-running run gets treated as finished.
#
# This wrapper re-checks the run's status after every non-zero exit
# from `gh run watch`. If the run is still going, the watch failed
# rather than the run, so it waits and watches again. Only a run that
# has completed decides the exit code.
#
# Usage:
#   wait-for-run.sh <run-id>
#
# Inputs (env):
#   REPO                 Fully-qualified repo. Required.
#   GH_TOKEN             gh auth token. Required by gh.
#   WAIT_MAX_ATTEMPTS    Watch attempts before giving up. Default 5.
#   WAIT_RETRY_SECONDS   Pause between attempts. Default 30.
#
# Exit codes:
#   0  run completed with conclusion 'success'
#   1  run completed with any other conclusion, or still wasn't
#      complete after WAIT_MAX_ATTEMPTS watch attempts
#   2  usage error
#
# Unit-tested by tests/bats/ci-scripts/wait-for-run/.

set -euo pipefail

run_id="${1:-}"
if [[ -z "${run_id}" ]]; then
    echo "::error::wait-for-run.sh: run id argument required" >&2
    exit 2
fi
if [[ -z "${REPO:-}" ]]; then
    echo "::error::wait-for-run.sh: REPO env var required" >&2
    exit 2
fi

max_attempts="${WAIT_MAX_ATTEMPTS:-5}"
retry_seconds="${WAIT_RETRY_SECONDS:-30}"

for ((attempt = 1; attempt <= max_attempts; attempt++)); do
    if gh run watch "${run_id}" --exit-status --repo "${REPO}"; then
        exit 0
    fi

    # The watch exited non-zero. Ask for the run's state directly to
    # tell a failed run apart from a failed watch.
    if state=$(gh run view "${run_id}" --repo "${REPO}" \
        --json status,conclusion --jq '"\(.status) \(.conclusion)"'); then
        status="${state%% *}"
        conclusion="${state#* }"
        if [[ "${status}" == "completed" ]]; then
            if [[ "${conclusion}" == "success" ]]; then
                exit 0
            fi
            echo "::error::Run ${run_id} completed with conclusion '${conclusion}'." >&2
            exit 1
        fi
        echo "::warning::gh run watch exited while run ${run_id} is still '${status}' (attempt ${attempt}/${max_attempts}); watching again." >&2
    else
        echo "::warning::Couldn't read the status of run ${run_id} after gh run watch exited (attempt ${attempt}/${max_attempts}); watching again." >&2
    fi

    if ((attempt < max_attempts)); then
        sleep "${retry_seconds}"
    fi
done

echo "::error::Gave up waiting for run ${run_id} after ${max_attempts} watch attempts; it never reported as completed." >&2
exit 1

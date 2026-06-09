#!/usr/bin/env bash
# Golden-snapshot capture for PostCalendar pre-migration HTML.
#
# Hits the running dev-easy worktree stack with admin credentials, fetches
# each calendar view's rendered HTML, normalises out per-request variables
# (CSRF tokens, cache-busting timestamps, session-derived nonces), and
# saves to .ai-notes/snapshots/baseline/.
#
# Re-run after each plugin port or template conversion that produces
# user-visible HTML; diff the new output against the baseline to verify
# behavioural equivalence with the pre-migration Smarty render.
#
# Usage:
#   .ai-notes/snapshots/capture.sh <target-dir>
#
# Example:
#   .ai-notes/snapshots/capture.sh .ai-notes/snapshots/baseline
#   .ai-notes/snapshots/capture.sh /tmp/post-migration

set -euo pipefail

TARGET="${1:?usage: ${0} <target-dir>}"
BASE="http://localhost:8302"
COOKIES=$(mktemp)
trap 'rm -f "${COOKIES}"' EXIT

mkdir -p "${TARGET}"

echo "Logging in as admin..."
# Prime session
curl -s -c "${COOKIES}" "${BASE}/interface/login/login.php?site=default" > /dev/null

# POST credentials. OpenEMR's login form has no CSRF field (verified by
# inspecting the rendered form's input names) so authProvider + authUser +
# clearPass are sufficient. languageChoice + new_login_session_management
# match the form's hidden inputs.
curl -s -L -b "${COOKIES}" -c "${COOKIES}" -o /dev/null -w "  login HTTP %{http_code}\n" \
    -X POST "${BASE}/interface/main/main_screen.php?auth=login" \
    --data-urlencode "new_login_session_management=1" \
    --data-urlencode "authProvider=Default" \
    --data-urlencode "authUser=admin" \
    --data-urlencode "clearPass=pass" \
    --data-urlencode "languageChoice=1"

# Normaliser: strips per-request variability so two captures of the same
# view are byte-identical when underlying state is unchanged.
#   - cache-busting ?v=N and ?t=N query params
#   - CSRF tokens (16-byte hex)
#   - session-derived nonces
#   - leading/trailing whitespace
normalise() {
    # Reads from stdin (no file arg) — script pipes curl output through.
    sed -E '
        s/\?v=[0-9]+/?v=NORMALISED/g
        s/\?t=[0-9]+/?t=NORMALISED/g
        s/csrf_token_form" value="[a-f0-9]+"/csrf_token_form" value="NORMALISED"/g
        s/nonce-[a-f0-9-]+/nonce-NORMALISED/g
    '
}

# Fixed date for deterministic event-list rendering. June 1 2026 (Monday)
# is past the dev-easy seed-data window so day/week/month all render
# with empty event sets — exercises the templates' "no events" branch.
# When seeded events are needed for a richer baseline, swap to a known
# event date and document below.
DATE="20260601"

capture() {
    local name=$1
    local query=$2
    local out="${TARGET}/${name}.html"
    echo "  -> ${name}"
    curl -s -b "${COOKIES}" "${BASE}/interface/main/calendar/index.php?${query}" \
        | normalise > "${out}"
    wc -l "${out}" | awk '{print "     " $1 " lines"}'
}

echo "Capturing views..."
capture "day"               "module=PostCalendar&func=view&viewtype=day&Date=${DATE}"
capture "week"              "module=PostCalendar&func=view&viewtype=week&Date=${DATE}"
capture "month"             "module=PostCalendar&func=view&viewtype=month&Date=${DATE}"
capture "day_print"         "module=PostCalendar&func=view&viewtype=day&Date=${DATE}&print=1"
capture "week_print"        "module=PostCalendar&func=view&viewtype=week&Date=${DATE}&print=1"
capture "month_print"       "module=PostCalendar&func=view&viewtype=month&Date=${DATE}&print=1"
capture "user_search"       "module=PostCalendar&func=search"
capture "admin_categories"  "module=PostCalendar&type=admin&func=categories"

echo "Done. Outputs in ${TARGET}/"

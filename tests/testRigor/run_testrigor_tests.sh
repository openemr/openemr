#!/bin/bash

set -ou pipefail

failTest=false

TEST_SUITE_ID="$1"
AUTH_TOKEN="$2"
WORKSPACE_DIR="${3:-$(pwd)}"
BRANCH_NAME="$4"
COMMIT_NAME="${5::7}"
JOB_ID="$6"
LOCALHOST_URL="http://localhost:80"
TEST_CASES_PATH="$WORKSPACE_DIR/tests/testRigor/testcases/**/*.txt"
RULES_PATH="$WORKSPACE_DIR/tests/testRigor/rules/**/*.txt"
H_AUTH_TOKEN="auth-token: $AUTH_TOKEN"
echo "Running testRigor tests..."
response=$(npx testrigor-cli test-suite run "$TEST_SUITE_ID" --token "$AUTH_TOKEN" --localhost --url "$LOCALHOST_URL" --test-cases-path "$TEST_CASES_PATH" --rules-path "$RULES_PATH" --branch "$BRANCH_NAME-$JOB_ID" --commit "$COMMIT_NAME-$JOB_ID") || failTest=true

RUN_ID=$(echo "$response" | sed -n "s|.*/test-suites/$TEST_SUITE_ID/runs/\([^ \"]*\).*|\1|p")
if [ -z "$RUN_ID" ]; then
    echo "Error: Could not extract RUN_ID from the response."
fi
STATUS_URL="https://api2.testrigor.com/api/v1/apps/$TEST_SUITE_ID/runs/$RUN_ID/testcases"
RES=$(curl -s -X GET \
  -H "$H_AUTH_TOKEN" \
  "$STATUS_URL")

echo "In Progress: https://app.testrigor.com/test-suites/$TEST_SUITE_ID/runs/$RUN_ID"
echo "$RES" | jq -r '
    "TOTAL: \(.data.totalElements)",
    "STATUS: \(if (.data.content | map(select(.status == "Failed")) | length > 0) then "Failed" 
              elif (.data.content | map(select(.status == "Cancelled")) | length > 0) then "Cancelled" 
              else "Passed" end)",
    "TEST CASES:",
    (.data.content[] | "\(.name) \(if .status == "Passed" then "✓" elif .status == "Cancelled" then "-" else "✖" end)")
' | awk -v total="$(echo "$RES" | jq '.data.totalElements')" '
BEGIN {
    print "\033[1mTEST RESULTS\033[0m"
    print "========================================"
    passed = 0
    failed = 0
    cancelled = 0
}
NR==1 { 
    printf "\033[1m%-30s %s\033[0m\n", $0, sprintf("(%d total)", total)
    next 
}
NR==2 { 
    if ($2 == "Failed") {
        status_color="\033[31m"
    } else if ($2 == "Cancelled") {
        status_color="\033[33m"
    } else {
        status_color="\033[32m"
    }
    printf "\033[1m%-30s %s%s\033[0m\n", $1, status_color, $2
    next
}
NR==3 { print; next }
{
    if ($2 == "✓") {
        passed++
        gsub(/✓/, "\033[32m✓\033[0m")
    } else if ($2 == "✖") {
        failed++
        gsub(/✖/, "\033[31m✖\033[0m")
    } else if ($2 == "-") {
        cancelled++
        gsub(/-/, "\033[33m-\033[0m")
    }
    printf "  %-40s %s\n", $1, $2
}
END { 
    print "========================================"
    printf "\033[32m%d passing\033[0m  \033[31m%d failing\033[0m  \033[33m%d cancelled\033[0m\n", passed, failed, cancelled
}'
if $failTest; then
    exit 1
fi
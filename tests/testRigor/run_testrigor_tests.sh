#!/bin/bash

BRANCH_NAME="$(git rev-parse --abbrev-ref HEAD)"
COMMIT_NAME="$(git rev-parse --verify HEAD)"

# Define default values according to test suite
OPENEMR_TEST_SUITE_ID="bpCriLgskAyPkTD9f"
OPENEMR_AUTH_TOKEN="45c2e22b-da8f-4047-a10b-270a36569154"
LOCALHOST_URL="http://0.0.0.0:$OPENEMR_PORT"

# Paths for the test cases and rules files
TEST_CASES_PATH="tests/testRigor/testcases/**/*.txt"
RULES_PATH="tests/testRigor/rules/**/*.txt"

# Command to run the tests using the testRigor CLI
testrigor test-suite run "$OPENEMR_TEST_SUITE_ID" --token "$OPENEMR_AUTH_TOKEN" --localhost --url "$LOCALHOST_URL" --test-cases-path "$TEST_CASES_PATH" --rules-path "$RULES_PATH" --branch "$BRANCH_NAME" --commit "$COMMIT_NAME" --verbose
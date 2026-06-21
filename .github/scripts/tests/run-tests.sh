#!/usr/bin/env bash
#
# Test runner for the shell-script tests under .github/scripts/tests/.
#
# Discovers every test-*.sh file in this directory, sources it, and runs
# every test_* function defined in it. Each test runs in a subshell so a
# failure (or set -e abort) in one test doesn't stop subsequent ones.
#
# Exit code: 0 if all tests pass, non-zero if any failed.
#
# No external dependencies (no BATS, no shellspec). Just bash + git + yq.

set -uo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")"

TOTAL=0
PASSED=0
FAILED=0
FAILED_NAMES=()

for test_file in test-*.sh; do
  echo "=== $test_file ==="
  # shellcheck disable=SC1090
  source "$test_file"

  # Collect test_* functions defined in (or after sourcing) this file. We
  # can't easily separate by source file, so we run them all when we hit
  # the first file. Use a deduplication marker so the same test isn't run
  # twice if multiple test files are sourced in sequence.
  for fn in $(compgen -A function | grep '^test_' | sort); do
    # Skip if already run (when multiple test files share helpers).
    if [[ " ${RAN[*]:-} " == *" $fn "* ]]; then
      continue
    fi
    RAN+=("$fn")
    TOTAL=$((TOTAL + 1))
    printf '  %-60s ' "$fn"
    if (set -e; "$fn") >/tmp/test-output.$$ 2>&1; then
      echo "PASS"
      PASSED=$((PASSED + 1))
    else
      echo "FAIL"
      FAILED=$((FAILED + 1))
      FAILED_NAMES+=("$fn")
      sed 's/^/      /' /tmp/test-output.$$
    fi
    rm -f /tmp/test-output.$$
  done
done

echo
echo "Total: $TOTAL  Passed: $PASSED  Failed: $FAILED"
if [ "$FAILED" -gt 0 ]; then
  echo "Failed tests:"
  for name in "${FAILED_NAMES[@]}"; do
    echo "  - $name"
  done
  exit 1
fi
echo "All tests passed."

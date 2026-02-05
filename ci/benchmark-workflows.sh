#!/usr/bin/env bash
#
# Benchmark GitHub Actions workflow runtimes between two branches
#
# Usage: ci/benchmark-workflows.sh [branch] [base]
#   branch: Branch to compare (default: current branch)
#   base:   Base branch to compare against (default: master)
#
# Requires: gh CLI authenticated with repo access

set -euo pipefail

BRANCH="${1:-$(git branch --show-current)}"
BASE="${2:-master}"

# Workflows to compare - add/remove as needed
WORKFLOWS=(
  "api-docs.yml"
  "composer-require-checker.yml"
  "composer.yml"
  "conventional-commits.yml"
  "isolated-tests.yml"
  "phpstan.yml"
  "rector.yml"
  "styling.yml"
  "syntax.yml"
)

echo "Comparing workflow runtimes: $BRANCH vs $BASE"
echo ""
echo "| Workflow | $BRANCH | $BASE | Delta |"
echo "|----------|---------|-------|-------|"

for wf in "${WORKFLOWS[@]}"; do
  name="${wf%.yml}"

  # Get branch run ID (most recent successful)
  branch_run=$(gh run list --workflow="$wf" --branch="$BRANCH" --limit=1 \
    --json databaseId,conclusion \
    --jq '.[0] | select(.conclusion == "success") | .databaseId' 2>/dev/null || true)

  # Get base run ID (most recent successful)
  base_run=$(gh run list --workflow="$wf" --branch="$BASE" --limit=5 \
    --json databaseId,conclusion \
    --jq '[.[] | select(.conclusion == "success")][0].databaseId' 2>/dev/null || true)

  if [[ -n "$branch_run" && -n "$base_run" ]]; then
    # Get job durations (sum of all jobs)
    branch_time=$(gh run view "$branch_run" --json jobs \
      --jq '[.jobs[] | .completedAt as $e | .startedAt as $s | (($e | fromdateiso8601) - ($s | fromdateiso8601))] | add | round' 2>/dev/null || true)
    base_time=$(gh run view "$base_run" --json jobs \
      --jq '[.jobs[] | .completedAt as $e | .startedAt as $s | (($e | fromdateiso8601) - ($s | fromdateiso8601))] | add | round' 2>/dev/null || true)

    if [[ -n "$branch_time" && -n "$base_time" ]]; then
      delta=$((branch_time - base_time))
      sign=""
      [[ $delta -gt 0 ]] && sign="+"
      echo "| $name | ${branch_time}s | ${base_time}s | ${sign}${delta}s |"
    else
      echo "| $name | err | err | - |"
    fi
  else
    echo "| $name | - | - | - |"
  fi
done

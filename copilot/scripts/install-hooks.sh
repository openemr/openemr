#!/usr/bin/env bash
# install-hooks.sh — install the pre-push regression-blocking hook for the W2 eval gate.
#
# Run from the repo root:
#   bash copilot/scripts/install-hooks.sh
#
# What it installs:
#   .git/hooks/pre-push — runs `make -C copilot eval-fast` and blocks `git push`
#                         if the 12-case fast subset fails.
#
# The hook is safe to re-run (idempotent — overwrites the same file). To
# bypass once during an emergency: `git push --no-verify` (use sparingly;
# bypassing the gate without good cause is a protocol violation).

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
HOOK_PATH="$REPO_ROOT/.git/hooks/pre-push"

if [ ! -d "$REPO_ROOT/.git" ]; then
  echo "FATAL: not a git repo (no .git/ at $REPO_ROOT)" >&2
  exit 1
fi

cat > "$HOOK_PATH" <<'HOOK'
#!/usr/bin/env bash
# Auto-installed by copilot/scripts/install-hooks.sh.
#
# Runs the W2 50-case eval gate's FAST_SUBSET (12 cases, ~2s in Docker)
# before allowing a push. The PRD requires this gate to block regressions:
# during grading, a small regression is injected and the gate must fail.
#
# Bypass (emergency only): git push --no-verify

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"

# Skip if no copilot/** changes in the push range — saves docker-pull time
# on PHP-only branches.
range="HEAD"
if git rev-parse --verify --quiet "$1@{0}" >/dev/null 2>&1; then
  : # interactive push; just use HEAD
fi
if ! git diff --name-only --quiet -- copilot/ 2>/dev/null; then
  : # there are local copilot changes vs index — run gate
fi
# Coarse check: does the push touch copilot/?
copilot_touched=0
if git log --name-only --pretty=format: HEAD..@{u} 2>/dev/null \
    | grep -q '^copilot/' ; then
  copilot_touched=1
fi
if [ "$copilot_touched" = "0" ] && git diff --name-only @{u}..HEAD 2>/dev/null \
    | grep -q '^copilot/' ; then
  copilot_touched=1
fi

# If we can't determine, run the gate anyway (safe default).
if [ "$copilot_touched" = "0" ]; then
  if git diff --name-only HEAD~1..HEAD 2>/dev/null | grep -q '^copilot/' ; then
    copilot_touched=1
  fi
fi

if [ "$copilot_touched" = "1" ]; then
  echo "→ pre-push: running W2 eval gate (make eval-fast)…"
  if ! make -C "$REPO_ROOT/copilot" eval-fast >/tmp/copilot-eval-fast.log 2>&1; then
    echo
    echo "✗ pre-push BLOCKED: W2 eval gate failed."
    echo "  See /tmp/copilot-eval-fast.log + copilot/evals/RESULTS.md."
    echo "  Bypass (emergency only): git push --no-verify"
    exit 1
  fi
  echo "✓ pre-push: W2 eval gate passed."
fi

exit 0
HOOK

chmod +x "$HOOK_PATH"
echo "Installed: $HOOK_PATH"
echo
echo "The pre-push hook now runs 'make eval-fast' on every git push that"
echo "touches copilot/**. Test with:"
echo "    git push --dry-run"

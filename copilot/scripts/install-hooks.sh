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
# Runs the W2 50-case eval gate's FAST_SUBSET (~14 cases, <2s in Docker)
# before allowing a push. The PRD requires this gate to block regressions:
# during grading, a small regression is injected and the gate must fail.
#
# Bypass (emergency only): git push --no-verify

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
ZERO_OID="0000000000000000000000000000000000000000"

# Git's pre-push hook contract: pushed refs are passed on STDIN as
# "<local_ref> <local_oid> <remote_ref> <remote_oid>" lines, one per ref.
# Reading stdin is the only authoritative way to know what's being pushed
# — `@{u}` doesn't resolve on new branches with no upstream, and
# `HEAD~1..HEAD` misses copilot changes in earlier commits of the push
# (codex round-7 P2 fix).
copilot_touched=0
read_any=0
while read -r local_ref local_oid remote_ref remote_oid; do
  read_any=1
  # Skip ref deletions (local_oid is all zeros).
  [ "$local_oid" = "$ZERO_OID" ] && continue
  if [ "$remote_oid" = "$ZERO_OID" ]; then
    # New branch on the remote — diff against the merge-base with origin's
    # default branch if we can find one; otherwise fall back to "all
    # commits reachable from local_oid that aren't on master".
    base="$(git merge-base "$local_oid" origin/master 2>/dev/null \
            || git merge-base "$local_oid" master 2>/dev/null \
            || echo "")"
    if [ -n "$base" ]; then
      range="$base..$local_oid"
    else
      # Last resort: just check the tip.
      range="$local_oid~1..$local_oid"
    fi
  else
    range="$remote_oid..$local_oid"
  fi
  if git diff --name-only "$range" 2>/dev/null | grep -q '^copilot/' ; then
    copilot_touched=1
    break
  fi
done

# If stdin had no refs (e.g. git push run without piping), default to
# running the gate — safer than skipping.
if [ "$read_any" = "0" ]; then
  copilot_touched=1
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

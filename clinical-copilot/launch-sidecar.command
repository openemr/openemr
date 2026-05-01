#!/usr/bin/env bash
# Double-click this file in Finder to boot the Clinical Co-Pilot sidecar.
# Picks the first usable Python (>= 3.11) on the system and provisions a
# .venv in-place. Idempotent — re-running just relaunches uvicorn.
set -euo pipefail

cd "$(dirname "$0")"
ROOT="$(pwd)"
LOG="$ROOT/.launch.log"

echo "── Clinical Co-Pilot launcher ──"
echo "Working dir : $ROOT"

# Make sure common install dirs are on PATH (.command files boot with a
# minimal PATH on some macOS versions and miss Homebrew / Xcode CLT).
export PATH="/opt/homebrew/bin:/opt/homebrew/sbin:/usr/local/bin:/usr/local/sbin:/Library/Developer/CommandLineTools/usr/bin:/usr/bin:/bin:/usr/sbin:/sbin:$HOME/.local/bin:$PATH"

# 1. Pick a Python. We prefer 3.11+, but the codebase's _compat shim handles
# 3.10 too, so we fall back to whatever python3 we can find and pass
# --ignore-requires-python to pip.
PY=""
PIP_REQUIRES_FLAG=""

# Search by name on PATH …
for candidate in python3.13 python3.12 python3.11; do
  if command -v "$candidate" >/dev/null 2>&1; then
    PY="$candidate"; break
  fi
done

# … then search common absolute paths in case PATH is still too thin.
if [ -z "$PY" ]; then
  for abs in \
    /opt/homebrew/bin/python3.13 /opt/homebrew/bin/python3.12 /opt/homebrew/bin/python3.11 \
    /usr/local/bin/python3.13 /usr/local/bin/python3.12 /usr/local/bin/python3.11 \
    /Library/Frameworks/Python.framework/Versions/3.13/bin/python3 \
    /Library/Frameworks/Python.framework/Versions/3.12/bin/python3 \
    /Library/Frameworks/Python.framework/Versions/3.11/bin/python3; do
    if [ -x "$abs" ]; then PY="$abs"; break; fi
  done
fi

if [ -z "$PY" ] && command -v uv >/dev/null 2>&1; then
  echo "No system Python ≥ 3.11 found — bootstrapping via uv."
  uv python install 3.12 >/dev/null 2>&1 || true
  PY="$(uv python find 3.12 2>/dev/null || true)"
fi

# Last-ditch fallback: any python3 ≥ 3.9. macOS Sequoia ships 3.9.6 with
# Xcode CLT; the sidecar's runtime no longer uses any 3.10-only syntax,
# so we accept it and pass --ignore-requires-python to pip.
if [ -z "$PY" ]; then
  for candidate in python3 /opt/homebrew/bin/python3 /usr/local/bin/python3 \
                   /Library/Developer/CommandLineTools/usr/bin/python3 /usr/bin/python3; do
    if [ -x "$(command -v "$candidate" 2>/dev/null || echo "$candidate")" ] || command -v "$candidate" >/dev/null 2>&1; then
      ver=$("$candidate" -c 'import sys;print("%d.%d"%sys.version_info[:2])' 2>/dev/null || echo "")
      if [ -n "$ver" ] && [ "$(printf '%s\n' "3.9" "$ver" | sort -V | head -1)" = "3.9" ]; then
        echo "No Python 3.11+ found; using $candidate ($ver) with --ignore-requires-python."
        PY="$candidate"
        PIP_REQUIRES_FLAG="--ignore-requires-python"
        break
      fi
    fi
  done
fi

if [ -z "$PY" ]; then
  echo "ERROR: no Python found. PATH=$PATH"
  echo "Install with:  brew install python@3.12"
  echo "          or:  curl -LsSf https://astral.sh/uv/install.sh | sh"
  read -r -p "Press return to close…" _; exit 1
fi
echo "Using Python : $PY ($($PY -V))"

# 2. Try venv first (preferred); fall back to user-site if venv fails
# (Apple's Xcode CLT Python 3.9 sometimes returns exit 0 from `python3 -m venv`
# but leaves an incomplete .venv with no activate script — so we verify the
# venv post-hoc and treat anything missing as a failure.)
USE_USER_SITE=0
# Force a fresh .venv if either:
#   • there isn't one,
#   • there is one but it's broken (no activate script), or
#   • the cached pip inside it is wedged (Apple's ensurepip can ship a
#     newer pip that uses 3.10-only syntax on 3.9).
NEEDS_FRESH=0
if [ ! -f .venv/bin/activate ]; then
  NEEDS_FRESH=1
elif [ -x .venv/bin/python ] && ! .venv/bin/python -m pip --version >/dev/null 2>&1; then
  echo ".venv/bin/python -m pip is broken; rebuilding .venv from scratch."
  NEEDS_FRESH=1
fi

if [ "$NEEDS_FRESH" = "1" ]; then
  rm -rf .venv 2>/dev/null || true
  echo "Creating .venv …"
  "$PY" -m venv .venv 2>/tmp/copilot-venv.log || true
  if [ ! -f .venv/bin/activate ]; then
    echo "venv creation failed (activate script missing). Log:"
    cat /tmp/copilot-venv.log 2>/dev/null || true
    echo "Falling back to user-site install (--user)."
    USE_USER_SITE=1
    rm -rf .venv 2>/dev/null || true
  fi
fi

USER_FLAG=""
RUN_PY=""
if [ "$USE_USER_SITE" = "0" ] && [ -f .venv/bin/activate ]; then
  # shellcheck source=/dev/null
  . .venv/bin/activate
  RUN_PY="python"
else
  RUN_PY="$PY"
  USER_FLAG="--user"
fi

# Make sure pip is available wherever we're installing into.
if ! "$RUN_PY" -m pip --version >/dev/null 2>&1; then
  echo "pip missing; bootstrapping …"
  "$RUN_PY" -m ensurepip --upgrade 2>/dev/null || \
    curl -sS https://bootstrap.pypa.io/get-pip.py -o /tmp/get-pip.py && \
    "$RUN_PY" /tmp/get-pip.py $USER_FLAG --quiet || true
fi

# Older Pythons need older releases of pip, uvicorn, fastapi, pydantic, etc.
# constraints-py39.txt pins every package to a known-good 3.9 release.
PY_MINOR=$("$RUN_PY" -c 'import sys;print(sys.version_info[1])' 2>/dev/null || echo 0)
CONSTRAINTS=""
if [ "$PY_MINOR" -lt 10 ] && [ -f constraints-py39.txt ]; then
  CONSTRAINTS="-c constraints-py39.txt"
  echo "Using $(pwd)/constraints-py39.txt to pin packages to 3.9-safe versions."
fi

"$RUN_PY" -m pip install $USER_FLAG --quiet --upgrade $CONSTRAINTS pip $PIP_REQUIRES_FLAG 2>&1 | tail -3 || true
echo "Installing dependencies (first run takes ~30 s) …"
"$RUN_PY" -m pip install $USER_FLAG --quiet $CONSTRAINTS $PIP_REQUIRES_FLAG -e ".[openai]" 2>&1 | tail -5 || {
  echo "FATAL: dependency install failed.  Last 20 log lines:"
  "$RUN_PY" -m pip install $USER_FLAG $CONSTRAINTS $PIP_REQUIRES_FLAG -e ".[openai]" 2>&1 | tail -20
  read -r -p "Press return to close…" _; exit 1
}

# 3. Boot uvicorn. Loads .env (gitignored) for OPENAI_API_KEY etc.
export PYTHONPATH="$ROOT"
echo "Starting on http://127.0.0.1:8801/  (logs: $LOG)"
echo "Press Ctrl-C to stop."
exec "$RUN_PY" -m uvicorn sidecar.main:app --host 127.0.0.1 --port 8801 --log-level info \
  --reload --reload-dir sidecar --reload-dir ui --reload-include "*.html"

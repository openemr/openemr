"""Eval runner: invoke pytest, capture results to ``evals/results/``.

CI gate: regression below the documented thresholds blocks deploy.
"""

from __future__ import annotations

import datetime as dt
import json
import subprocess
import sys
from pathlib import Path


def main() -> int:
    here = Path(__file__).resolve().parent
    timestamp = dt.datetime.now(tz=dt.timezone.utc).strftime("%Y-%m-%dT%H-%M-%SZ")
    results_path = here / "results" / f"{timestamp}.json"
    results_path.parent.mkdir(parents=True, exist_ok=True)

    junit_path = here / "results" / f"{timestamp}.junit.xml"
    cmd = [
        sys.executable, "-m", "pytest",
        str(here),
        "-v",
        f"--junitxml={junit_path}",
    ]
    proc = subprocess.run(cmd, capture_output=False)
    payload = {
        "timestamp": timestamp,
        "exit_code": proc.returncode,
        "junit_path": str(junit_path),
    }
    results_path.write_text(json.dumps(payload, indent=2))
    print(f"\nResults summary written to {results_path}")
    return proc.returncode


if __name__ == "__main__":
    sys.exit(main())

#!/usr/bin/env python3
"""Lint: workflow jobs that call `.github/scripts/*` must have actions/checkout.

The fetch-source-artifact jobs in acceptance-only.yml and docker-acceptance-
only.yml (openemr/openemr#13972) shipped without an `actions/checkout` step
even though they called scripts under `.github/scripts/`. The workflows'
Actions runners started with an empty workspace, so the script invocations
died with `No such file or directory` at runtime -- a bug type that
actionlint and the shell-script BATS tests can't catch because it's a
workflow-shape invariant (a job that references a repo-local script must
have checked out the repo first).

This lint scans every workflow YAML in .github/workflows/ and reports any
job whose steps contain a `run:` block referencing `.github/scripts/` OR
that runs `.github/scripts/*.sh` from the workspace root, unless the same
job also contains a step using `actions/checkout@`.

Exit codes:
  0 -- all workflow jobs are clean
  1 -- one or more violations reported (via ::error:: annotations)
"""

from __future__ import annotations

import re
import sys
from pathlib import Path

import yaml

WORKFLOWS_DIR = Path('.github/workflows')

# Match any `run:` block that references a script under .github/scripts/
# (either as a bare path or after a `bash `/`sh ` prefix; simple substring
# match is sufficient -- false positives cost a redundant checkout, false
# negatives cost a runtime failure).
SCRIPT_REF = re.compile(r'\.github/scripts/')


def collect_violations(workflows_dir: Path) -> list[str]:
    violations: list[str] = []
    for wf_file in sorted(workflows_dir.glob('*.yml')):
        try:
            with wf_file.open() as f:
                wf = yaml.safe_load(f)
        except yaml.YAMLError as e:
            violations.append(f"{wf_file}: YAML parse error: {e}")
            continue
        if not isinstance(wf, dict):
            continue
        jobs = wf.get('jobs')
        if not isinstance(jobs, dict):
            continue
        for job_name, job in jobs.items():
            if not isinstance(job, dict):
                continue
            steps = job.get('steps')
            if not isinstance(steps, list):
                continue
            has_checkout = any(
                isinstance(s, dict)
                and isinstance(s.get('uses'), str)
                and s['uses'].startswith('actions/checkout@')
                for s in steps
            )
            calls_repo_script = any(
                isinstance(s, dict)
                and isinstance(s.get('run'), str)
                and SCRIPT_REF.search(s['run'])
                for s in steps
            )
            if calls_repo_script and not has_checkout:
                violations.append(
                    f"{wf_file}: job '{job_name}' calls a .github/scripts/ script "
                    f"but has no actions/checkout step in the same job "
                    f"(the workspace will be empty at script-invocation time)"
                )
    return violations


def main() -> int:
    if not WORKFLOWS_DIR.is_dir():
        print(f"::error::workflows dir not found: {WORKFLOWS_DIR}", file=sys.stderr)
        return 1
    violations = collect_violations(WORKFLOWS_DIR)
    if violations:
        for v in violations:
            print(f"::error::{v}", file=sys.stderr)
        print(
            f"\nFound {len(violations)} workflow job(s) missing actions/checkout. "
            f"Add `- uses: actions/checkout@vN` as the first step of each flagged job.",
            file=sys.stderr,
        )
        return 1
    print(
        f"OK: all workflow jobs calling .github/scripts/ have actions/checkout"
    )
    return 0


if __name__ == '__main__':
    sys.exit(main())

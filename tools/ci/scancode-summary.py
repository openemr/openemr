#!/usr/bin/env python3
"""Render a ScanCode JSON report as a GitHub Actions step summary.

Reads the ScanCode JSON report passed as the first argument and appends a
markdown summary to ``$GITHUB_STEP_SUMMARY`` (or stdout if the variable is
unset — useful for local testing).

Environment variables:
    SCAN_MODE   Label shown in the summary heading ("diff" or "full").
    PER_FILE_LIMIT
                Maximum number of per-file rows to include in the collapsible
                details block. Defaults to 500.
    SCANCODE_DISALLOWED_LICENSES
                Comma-separated list of license expressions that should fail
                the job. Matching files are also annotated via
                ``::error file=...::`` so they surface in the PR diff view.
                Unset / empty disables enforcement (informational mode).
"""
from __future__ import annotations

import json
import os
import sys
from collections import Counter


def main(argv: list[str]) -> int:
    if len(argv) != 2:
        print(f'usage: {argv[0]} <scancode-results.json>', file=sys.stderr)
        return 2

    results_path = argv[1]
    mode = os.environ.get('SCAN_MODE', 'unknown')
    limit = int(os.environ.get('PER_FILE_LIMIT', '500'))
    disallowed = frozenset(
        expr.strip()
        for expr in os.environ.get('SCANCODE_DISALLOWED_LICENSES', '').split(',')
        if expr.strip()
    )

    with open(results_path) as fh:
        data = json.load(fh)

    licenses: Counter[str] = Counter()
    per_file: list[tuple[str, list[str]]] = []
    violations: list[tuple[str, str]] = []
    for entry in data.get('files', []):
        if entry.get('type') != 'file':
            continue
        detections = entry.get('license_detections', []) or []
        if not detections:
            continue
        expressions = sorted({d.get('license_expression', 'unknown') for d in detections})
        path = entry['path']
        # Normalize scan-root prefixes so paths match the repo layout.
        for prefix in ('scancode-input/', 'scancode-shard-input/'):
            if path.startswith(prefix):
                path = path[len(prefix):]
                break
        per_file.append((path, expressions))
        for expr in expressions:
            licenses[expr] += 1
            if expr in disallowed:
                violations.append((path, expr))

    lines: list[str] = [f'## ScanCode License Findings ({mode} scan)', '']
    if not licenses:
        lines.append('No license text detected.')
    else:
        lines.append('| License Expression | File Count |')
        lines.append('|---|---|')
        lines.extend(f'| `{expr}` | {count} |' for expr, count in licenses.most_common())
        lines.append('')
        shown = per_file[:limit]
        heading = 'Per-file findings'
        if len(per_file) > limit:
            heading = f'Per-file findings (first {limit} of {len(per_file)})'
        lines.append(f'<details><summary>{heading}</summary>')
        lines.append('')
        lines.append('| File | License(s) |')
        lines.append('|---|---|')
        lines.extend(
            f'| `{path}` | {", ".join(f"`{e}`" for e in exprs)} |'
            for path, exprs in shown
        )
        lines.append('')
        lines.append('</details>')

    if violations:
        lines.append('')
        lines.append(f'**Policy violations:** {len(violations)} '
                     f'file(s) detected with disallowed licenses '
                     f'({", ".join(f"`{e}`" for e in sorted(disallowed))}).')

    output = '\n'.join(lines) + '\n'
    summary_path = os.environ.get('GITHUB_STEP_SUMMARY')
    if summary_path:
        with open(summary_path, 'a') as out:
            out.write(output)
    else:
        sys.stdout.write(output)

    # Emit GitHub Actions annotations for each violation so they surface in
    # the PR "Files changed" view, not just in the step summary.
    for path, expr in violations:
        title = f'Disallowed license: {expr}'
        message = f'{path} matches a license expression on the disallow list'
        print(f'::error file={path},title={title}::{message}')
    return 1 if violations else 0


if __name__ == '__main__':
    sys.exit(main(sys.argv))

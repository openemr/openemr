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

    with open(results_path) as fh:
        data = json.load(fh)

    licenses: Counter[str] = Counter()
    per_file: list[tuple[str, list[str]]] = []
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

    output = '\n'.join(lines) + '\n'
    summary_path = os.environ.get('GITHUB_STEP_SUMMARY')
    if summary_path:
        with open(summary_path, 'a') as out:
            out.write(output)
    else:
        sys.stdout.write(output)
    return 0


if __name__ == '__main__':
    sys.exit(main(sys.argv))

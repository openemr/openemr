#!/usr/bin/env python3
"""Convert .scancode-ignore glob patterns into git exclude pathspecs.

Prints one ``:(exclude)<pattern>`` pathspec per line, for use with
``git ls-files`` / ``git diff`` in .github/workflows/scancode.yml. Keeping the
conversion here means the diff-mode and full-mode jobs share one exclusion
list instead of drifting apart.
"""
from __future__ import annotations

import sys

DEFAULT_IGNORE_FILE = '.scancode-ignore'


def pathspecs(lines: list[str]) -> list[str]:
    specs = []
    for raw in lines:
        pattern = raw.split('#', 1)[0].strip()
        if pattern == '':
            continue
        # Strip a trailing /* so the pathspec excludes the whole subtree
        # rather than only its immediate children.
        if pattern.endswith('/*'):
            pattern = pattern[:-2]
        specs.append(f':(exclude){pattern}')
    return specs


def main(argv: list[str]) -> int:
    path = argv[1] if len(argv) > 1 else DEFAULT_IGNORE_FILE
    with open(path) as fh:
        lines = fh.readlines()
    for spec in pathspecs(lines):
        print(spec)
    return 0


if __name__ == '__main__':
    sys.exit(main(sys.argv))

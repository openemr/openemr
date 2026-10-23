#!/usr/bin/env python3
"""Run real drawing UI against the isolated site; requires Playwright and its Chromium."""
from pathlib import Path
import subprocess

from background_preferences import harness


if __name__ == '__main__':
    with harness() as (root, base):
        subprocess.run(['node', str(Path(__file__).with_suffix('.cjs')), base, str(root)], check=True)

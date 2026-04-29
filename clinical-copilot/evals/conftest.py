"""Pytest configuration shared by all eval layers."""

from __future__ import annotations

import sys
from pathlib import Path

import pytest

# Ensure the package root (``clinical-copilot/``) is importable when pytest
# is invoked from anywhere.
_ROOT = Path(__file__).resolve().parent.parent
if str(_ROOT) not in sys.path:
    sys.path.insert(0, str(_ROOT))


@pytest.fixture
def fixture_dir() -> Path:
    return _ROOT / "fixtures" / "patients"

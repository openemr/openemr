"""Tiny compatibility shims for environments older than Python 3.11.

Currently provides a ``StrEnum`` fallback. Production deploys target
Python 3.12 (per the Dockerfile); this shim only matters when developers
or CI runners have an older interpreter.
"""

from __future__ import annotations

import sys
from enum import Enum

if sys.version_info >= (3, 11):
    from enum import StrEnum  # noqa: F401  (re-export)
else:
    class StrEnum(str, Enum):  # type: ignore[no-redef]
        """Backport of :class:`enum.StrEnum`. Mixes ``str`` and ``Enum``.

        Members are strings whose ``.value`` equals the member name's value.
        """

        def __str__(self) -> str:  # noqa: D401
            return str(self.value)

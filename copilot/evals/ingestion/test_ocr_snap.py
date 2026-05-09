"""Unit tests for ``app.ingestion.ocr`` — pure logic, no Tesseract needed."""
from __future__ import annotations

import pytest

from app.ingestion.ocr import OcrItem, snap_bbox
from app.ingestion.schemas import BoundingBox


def _box(x: float, y: float, w: float, h: float) -> BoundingBox:
    return BoundingBox(x=x, y=y, w=w, h=h)


def test_snap_picks_numeric_token_and_expands_to_row():
    """Lab values: '142' appears once. We pick the value via the numeric
    branch, then expand the returned rect to cover the whole row
    (label + value + units) so the citation has clinical context and the
    red stroke sits on the row gutters instead of the digits."""
    items = [
        OcrItem(text="LDL", x=0.10, y=0.20, w=0.05, h=0.02),
        OcrItem(text="Cholesterol", x=0.16, y=0.20, w=0.10, h=0.02),
        OcrItem(text="142", x=0.42, y=0.20, w=0.04, h=0.02),
        OcrItem(text="mg/dL", x=0.47, y=0.20, w=0.05, h=0.02),
    ]
    fb = _box(x=0.08, y=0.20, w=0.55, h=0.04)  # VLM's whole-row bbox

    snapped = snap_bbox(items, raw_text="LDL Cholesterol 142 mg/dL", fallback_bbox=fb)

    assert snapped is not None
    x, y, w, h = snapped
    # Row union: x = 0.10 (LDL), x+w = 0.52 (mg/dL right edge), y = 0.20.
    assert x == pytest.approx(0.10)
    assert y == pytest.approx(0.20)
    assert (x + w) == pytest.approx(0.52)
    assert h == pytest.approx(0.02)


def test_snap_row_expansion_excludes_other_rows():
    """The row band is tight (winner.h * 0.7) — items on other rows must
    NOT be unioned even when they would extend the rect horizontally."""
    items = [
        OcrItem(text="LDL", x=0.10, y=0.20, w=0.05, h=0.02),
        OcrItem(text="142", x=0.42, y=0.20, w=0.04, h=0.02),
        # HDL row, just below — must NOT be unioned with the LDL row.
        OcrItem(text="HDL", x=0.10, y=0.24, w=0.05, h=0.02),
        OcrItem(text="55", x=0.42, y=0.24, w=0.04, h=0.02),
    ]
    fb = _box(x=0.08, y=0.20, w=0.55, h=0.02)

    snapped = snap_bbox(items, raw_text="142", fallback_bbox=fb)

    assert snapped is not None
    x, y, w, h = snapped
    # Row union covers LDL row only: x=0.10, x+w=0.46, y=0.20, h=0.02.
    assert x == pytest.approx(0.10)
    assert y == pytest.approx(0.20)
    assert (x + w) == pytest.approx(0.46)
    assert h == pytest.approx(0.02)


def test_snap_whole_string_branch_does_not_row_expand():
    """Single-word intake answers ("penicillin") use the whole-string
    branch and must stay tight to the matched item — row expansion would
    pull in unrelated reaction tokens like "rash" on the same line."""
    items = [
        OcrItem(text="penicillin", x=0.10, y=0.30, w=0.10, h=0.02),
        OcrItem(text="rash", x=0.30, y=0.30, w=0.05, h=0.02),
    ]
    fb = _box(x=0.05, y=0.30, w=0.40, h=0.04)

    snapped = snap_bbox(items, raw_text="penicillin", fallback_bbox=fb)

    assert snapped is not None
    x, y, w, h = snapped
    assert (x, y, w, h) == (0.10, 0.30, 0.10, 0.02)


def test_snap_disambiguates_repeated_numeric_token_by_row():
    """'88' appears twice (in eGFR row and in a footer). We pick the one
    closer to the VLM's vertical center."""
    items = [
        OcrItem(text="88", x=0.40, y=0.10, w=0.04, h=0.02),  # footer / wrong row
        OcrItem(text="88", x=0.42, y=0.55, w=0.04, h=0.02),  # actual eGFR row
    ]
    fb = _box(x=0.30, y=0.55, w=0.30, h=0.03)

    snapped = snap_bbox(items, raw_text="88", fallback_bbox=fb)

    assert snapped is not None
    assert snapped[1] == 0.55  # eGFR row, not footer


def test_snap_prefers_exact_token_over_substring():
    """When both an exact match and a substring match exist, the exact
    one wins regardless of distance."""
    items = [
        OcrItem(text="142mg", x=0.10, y=0.20, w=0.06, h=0.02),  # substring (exact-distance)
        OcrItem(text="142", x=0.50, y=0.40, w=0.04, h=0.02),    # exact (further)
    ]
    fb = _box(x=0.10, y=0.20, w=0.10, h=0.02)

    snapped = snap_bbox(items, raw_text="142", fallback_bbox=fb)

    assert snapped is not None
    assert (snapped[0], snapped[1]) == (0.50, 0.40)


def test_snap_returns_none_when_nothing_matches():
    """No OCR'd item contains the value → caller falls back to VLM bbox."""
    items = [OcrItem(text="LDL", x=0.10, y=0.20, w=0.05, h=0.02)]
    fb = _box(x=0.08, y=0.20, w=0.55, h=0.04)

    assert snap_bbox(items, raw_text="142", fallback_bbox=fb) is None


def test_snap_returns_none_for_empty_inputs():
    """Defensive — empty raw_text or empty items list is a no-op, never
    a crash."""
    fb = _box(x=0.0, y=0.0, w=1.0, h=1.0)
    assert snap_bbox([], raw_text="142", fallback_bbox=fb) is None
    assert snap_bbox([OcrItem(text="x", x=0, y=0, w=0.1, h=0.1)],
                     raw_text="", fallback_bbox=fb) is None


def test_snap_falls_back_to_whole_string_when_no_numeric():
    """Allergy verbatims and intake answers have no numeric token. The
    helper should match on whole-string equality."""
    items = [
        OcrItem(text="penicillin", x=0.10, y=0.30, w=0.10, h=0.02),
        OcrItem(text="rash", x=0.30, y=0.30, w=0.05, h=0.02),
    ]
    fb = _box(x=0.05, y=0.30, w=0.40, h=0.04)

    snapped = snap_bbox(items, raw_text="penicillin", fallback_bbox=fb)

    assert snapped is not None
    assert (snapped[0], snapped[1]) == (0.10, 0.30)


def test_snap_unions_multi_token_intake_answer():
    """Intake answers like ``"John Doe"`` are split across multiple OCR
    items. The helper should union their rects (filtered to the same
    row as the VLM bbox) so the red rectangle hugs both tokens
    together — not snap to one token, not stretch into another row."""
    items = [
        OcrItem(text="John", x=0.10, y=0.30, w=0.06, h=0.02),
        OcrItem(text="Doe", x=0.17, y=0.30, w=0.05, h=0.02),
        # Different row — must NOT be unioned even though it shares no
        # token with the answer.
        OcrItem(text="male", x=0.10, y=0.40, w=0.05, h=0.02),
    ]
    fb = _box(x=0.08, y=0.30, w=0.30, h=0.02)  # VLM's row-wide bbox

    snapped = snap_bbox(items, raw_text="John Doe", fallback_bbox=fb)

    assert snapped is not None
    x, y, w, h = snapped
    # Hugs both tokens — left edge at "John", right edge at end of "Doe".
    assert x == pytest.approx(0.10)
    assert y == pytest.approx(0.30)
    assert (x + w) == pytest.approx(0.22)  # 0.17 + 0.05
    assert h == pytest.approx(0.02)


def test_snap_strips_trailing_punctuation_in_multi_token():
    """pdf.js / Tesseract often emit punctuation glued to the preceding
    token (``"shellfish,"`` rather than ``"shellfish"`` + ``","``). The
    multi-token branch must strip trailing ``,.;:`` from both sides
    before comparing, so the answer "shellfish peanuts" still snaps."""
    items = [
        OcrItem(text="shellfish,", x=0.10, y=0.30, w=0.10, h=0.02),
        OcrItem(text="peanuts", x=0.21, y=0.30, w=0.07, h=0.02),
    ]
    fb = _box(x=0.08, y=0.30, w=0.30, h=0.02)

    snapped = snap_bbox(items, raw_text="shellfish peanuts", fallback_bbox=fb)

    assert snapped is not None
    x, y, w, h = snapped
    assert x == pytest.approx(0.10)
    assert (x + w) == pytest.approx(0.28)  # 0.21 + 0.07


def test_snap_returns_none_when_only_one_multi_token_matches():
    """If raw_text has 2+ tokens but only one of them is found in the
    OCR items, the multi-token branch must return None — a one-token
    "match" pretending to be the whole answer is worse than the VLM
    bbox the caller already has."""
    items = [
        OcrItem(text="John", x=0.10, y=0.30, w=0.06, h=0.02),
        # "Smith" is missing entirely.
    ]
    fb = _box(x=0.08, y=0.30, w=0.30, h=0.02)

    snapped = snap_bbox(items, raw_text="John Smith", fallback_bbox=fb)

    assert snapped is None

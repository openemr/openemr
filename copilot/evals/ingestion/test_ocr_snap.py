"""Unit tests for ``app.ingestion.ocr`` — pure logic, no Tesseract needed."""
from __future__ import annotations

from app.ingestion.ocr import OcrItem, snap_bbox
from app.ingestion.schemas import BoundingBox


def _box(x: float, y: float, w: float, h: float) -> BoundingBox:
    return BoundingBox(x=x, y=y, w=w, h=h)


def test_snap_picks_numeric_token_in_correct_row():
    """Lab values: '142' appears once. We snap the VLM's row-bbox onto the
    OCR'd value rect even when the VLM bbox covered the whole row."""
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
    assert (x, y, w, h) == (0.42, 0.20, 0.04, 0.02)


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

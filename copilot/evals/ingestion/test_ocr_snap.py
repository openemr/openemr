"""Unit tests for ``app.ingestion.ocr``.

Most tests are pure-logic and don't need Tesseract installed.
``test_pdf_page_ocr_items_returns_normalized_items_when_available``
exercises the live pypdfium2 + Tesseract path; it skips cleanly when
those aren't installed in the host env.
"""
from __future__ import annotations

import pytest

from app.ingestion.ocr import OcrItem, snap_bbox
from app.ingestion.schemas import BoundingBox


def _box(x: float, y: float, w: float, h: float) -> BoundingBox:
    return BoundingBox(x=x, y=y, w=w, h=h)


def test_snap_picks_numeric_token_in_correct_row():
    """Lab values cited by value alone (raw_text='142') tight-snap to the
    numeric token. (Multi-word raw_text like 'LDL Cholesterol 142 mg/dL'
    falls into the multi-token row-union path tested below — that
    matches the iframe behavior and gives the user a row-level highlight
    instead of a tiny rect on just the number.)"""
    items = [
        OcrItem(text="LDL", x=0.10, y=0.20, w=0.05, h=0.02),
        OcrItem(text="Cholesterol", x=0.16, y=0.20, w=0.10, h=0.02),
        OcrItem(text="142", x=0.42, y=0.20, w=0.04, h=0.02),
        OcrItem(text="mg/dL", x=0.47, y=0.20, w=0.05, h=0.02),
    ]
    fb = _box(x=0.08, y=0.20, w=0.55, h=0.04)  # VLM's whole-row bbox

    snapped = snap_bbox(items, raw_text="142", fallback_bbox=fb)

    assert snapped is not None
    assert snapped == (0.42, 0.20, 0.04, 0.02)


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


def test_snap_multi_token_med_row_union():
    """Med-name citations like 'Amlodipine 5 mg daily' must NOT snap to
    a stray '5' on a different row. Multi-token row-union picks the
    row containing ≥60% of the tokens and returns their union rect.
    This is the regression for the synthea-intake bug where the bbox
    drifted to a Vitamin D '5000 IU daily' row.
    """
    items = [
        # Target row (Amlodipine line in a meds table)
        OcrItem(text="Amlodipine", x=0.10, y=0.30, w=0.10, h=0.02),
        OcrItem(text="5mg", x=0.21, y=0.30, w=0.04, h=0.02),
        OcrItem(text="PO", x=0.26, y=0.30, w=0.03, h=0.02),
        OcrItem(text="daily", x=0.30, y=0.30, w=0.05, h=0.02),
        # Decoy row containing '5' AND 'daily' but not 'amlodipine'
        OcrItem(text="Vitamin", x=0.10, y=0.50, w=0.07, h=0.02),
        OcrItem(text="D", x=0.18, y=0.50, w=0.02, h=0.02),
        OcrItem(text="5000", x=0.21, y=0.50, w=0.04, h=0.02),
        OcrItem(text="IU", x=0.26, y=0.50, w=0.03, h=0.02),
        OcrItem(text="daily", x=0.30, y=0.50, w=0.05, h=0.02),
    ]
    fb = _box(x=0.05, y=0.30, w=0.40, h=0.04)

    snapped = snap_bbox(items, raw_text="Amlodipine 5 mg daily", fallback_bbox=fb)

    assert snapped is not None
    x, y, w, h = snapped
    assert abs(y - 0.30) < 0.001, f"snapped to wrong row (y={y})"
    assert x <= 0.10
    assert x + w >= 0.30


def test_snap_multi_token_excludes_adjacent_row():
    """Tightly-packed meds tables: PO appears on every row. Without a
    snug per-anchor row tolerance, the snap would union across rows
    and return a 2-row-tall rect. We assert the rect height stays
    within a single line-height of the anchor.
    """
    items = [
        OcrItem(text="Atorvastatin", x=0.12, y=0.107, w=0.07, h=0.020),
        OcrItem(text="20mg", x=0.25, y=0.114, w=0.04, h=0.010),
        OcrItem(text="PO", x=0.34, y=0.114, w=0.02, h=0.008),
        OcrItem(text="bedtime", x=0.38, y=0.114, w=0.05, h=0.008),
        # Aspirin row, which shares 'PO' and 'daily' tokens
        OcrItem(text="Aspirin", x=0.12, y=0.138, w=0.04, h=0.010),
        OcrItem(text="(baby)", x=0.17, y=0.138, w=0.04, h=0.010),
        OcrItem(text="81mg", x=0.25, y=0.138, w=0.04, h=0.010),
        OcrItem(text="PO", x=0.34, y=0.138, w=0.02, h=0.008),
        OcrItem(text="daily", x=0.38, y=0.138, w=0.04, h=0.010),
    ]
    fb = _box(x=0.05, y=0.10, w=0.85, h=0.10)

    snapped = snap_bbox(items, raw_text="Atorvastatin 20mg PO bedtime", fallback_bbox=fb)

    assert snapped is not None
    x, y, w, h = snapped
    # Atorvastatin row has h≈0.020; rect must stay within ~1 line-height
    # (no bleed into Aspirin row at y≈0.138).
    assert h < 0.025, f"row union bled into adjacent row (h={h})"
    assert abs(y - 0.107) < 0.001


def test_snap_returns_none_when_multi_token_target_missing():
    """No row contains ≥60% of the tokens — the helper returns None so
    the caller keeps the VLM bbox (no false-confident drift)."""
    items = [
        OcrItem(text="Lisinopril", x=0.10, y=0.30, w=0.06, h=0.02),
        OcrItem(text="10mg", x=0.18, y=0.30, w=0.04, h=0.02),
    ]
    fb = _box(x=0.05, y=0.30, w=0.40, h=0.04)

    snapped = snap_bbox(items, raw_text="Amlodipine 5 mg daily", fallback_bbox=fb)

    assert snapped is None


def test_pdf_page_ocr_items_returns_normalized_items_when_available():
    """Smoke-test the PDF-rasterization helper. Skips when pypdfium2 or
    Tesseract aren't available in the host env (CI/dev split). When
    available, every returned OcrItem must be in the [0, 1] frame."""
    try:
        import pypdfium2  # noqa: F401
    except ImportError:
        pytest.skip("pypdfium2 not installed")

    from app.ingestion.ocr import _ocr_available, pdf_page_ocr_items

    if not _ocr_available():
        pytest.skip("Tesseract binary not available")

    # Generate a one-page PDF with reportlab (dev dep) so the test
    # doesn't depend on a checked-in binary fixture.
    try:
        from reportlab.lib.pagesizes import letter
        from reportlab.pdfgen import canvas
    except ImportError:
        pytest.skip("reportlab not installed")

    import io as _io
    buf = _io.BytesIO()
    c = canvas.Canvas(buf, pagesize=letter)
    c.setFont("Helvetica", 14)
    c.drawString(72, 720, "Lisinopril 10 mg PO daily")
    c.drawString(72, 700, "Amlodipine 5 mg PO daily")
    c.showPage()
    c.save()

    items = pdf_page_ocr_items(buf.getvalue(), 0)

    assert items, "OCR should return at least one item for typed text"
    for it in items:
        assert 0.0 <= it.x <= 1.0
        assert 0.0 <= it.y <= 1.0
        assert 0.0 < it.w <= 1.0
        assert 0.0 < it.h <= 1.0
    # And we should be able to find 'Amlodipine' somewhere
    assert any("Amlodipine" in it.text or "amlodipine" in it.text.lower() for it in items)

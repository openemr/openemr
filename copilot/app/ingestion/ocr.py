"""OCR-based bbox snap for image and PDF extractions.

Claude vision (the VLM) emits approximate bboxes on rasterized photos —
documented model behavior. The same problem hits born-digital PDFs whose
text is rendered through subsetted fonts/CMaps where pdf.js's text-layer
output doesn't match the VLM's ``raw_text`` verbatim (e.g. synthea-
generated intake forms). This module addresses both:

  1. ``ocr_items(image_bytes)`` runs Tesseract once on an image and
     returns every detected text item normalized to [0, 1].
  2. ``pdf_page_ocr_items(pdf_bytes, page_index)`` rasterizes a single
     PDF page via pypdfium2 and OCRs that page with Tesseract — same
     ``OcrItem`` shape, same [0, 1] frame, but cached per-page since
     multiple citations on one page share a rasterization.
  3. ``snap_bbox(items, raw_text, fallback_bbox)`` snaps the VLM bbox.
     For multi-word ``raw_text`` it picks the y-row that contains ≥60%
     of the tokens and returns the union rect (so a med-name citation
     highlights the whole "Amlodipine 5mg PO daily" row, not just one
     numeric token in the wrong place). For single-token raw_text the
     existing tight-snap behavior (numeric or whole-string) is kept.

The caller mutates ``source_citation.bbox`` in place at extraction time
so both the SQLite store and the bbox_overlay payload carry the snapped
rect from the moment the document is recorded.
"""

from __future__ import annotations

import io
import logging
import math
import re
from dataclasses import dataclass

logger = logging.getLogger(__name__)

# Module-level singleton — None means "not attempted yet"; False means
# "tried and failed" (so we don't keep re-importing on every extraction).
_OCR_AVAILABLE: bool | None = None


@dataclass(frozen=True)
class OcrItem:
    text: str
    x: float  # normalized [0, 1] from page top-left
    y: float
    w: float
    h: float


def _ocr_available() -> bool:
    """Probe pytesseract + the underlying tesseract binary once."""
    global _OCR_AVAILABLE
    if _OCR_AVAILABLE is not None:
        return _OCR_AVAILABLE
    try:
        import pytesseract  # noqa: F401
        from PIL import Image  # noqa: F401
        # Fail fast if the C binary is missing.
        pytesseract.get_tesseract_version()
        _OCR_AVAILABLE = True
    except Exception as e:
        logger.warning("OCR snap disabled: %s", e)
        _OCR_AVAILABLE = False
    return _OCR_AVAILABLE


def ocr_items(image_bytes: bytes) -> list[OcrItem]:
    """Return all OCR-detected text items for the image, normalized [0, 1].

    Returns ``[]`` on any error — callers fall back to the VLM bbox.
    """
    if not _ocr_available():
        return []
    try:
        import pytesseract
        from PIL import Image
        img = Image.open(io.BytesIO(image_bytes))
        # Tesseract works on RGB / grayscale; convert palette/RGBA up front.
        if img.mode not in ("L", "RGB"):
            img = img.convert("RGB")
        page_w, page_h = img.size
        if page_w <= 0 or page_h <= 0:
            return []
        data = pytesseract.image_to_data(
            img, output_type=pytesseract.Output.DICT
        )
    except Exception as e:
        logger.warning("OCR failed: %s", e)
        return []

    items: list[OcrItem] = []
    for i in range(len(data.get("text", []))):
        text = (data["text"][i] or "").strip()
        if not text:
            continue
        try:
            x = float(data["left"][i]) / page_w
            y = float(data["top"][i]) / page_h
            w = float(data["width"][i]) / page_w
            h = float(data["height"][i]) / page_h
        except (TypeError, ValueError, ZeroDivisionError):
            continue
        items.append(OcrItem(text=text, x=x, y=y, w=w, h=h))
    return items


def pdf_page_ocr_items(
    pdf_bytes: bytes, page_index: int, *, scale: float = 2.0
) -> list[OcrItem]:
    """Rasterize one PDF page and OCR it. Returns ``[]`` on any error.

    ``scale=2.0`` ≈ 144 DPI (PDFium native is 72 DPI), a good Tesseract
    sweet spot — higher slows OCR without much accuracy gain on typed
    forms. Coordinates are normalized to [0, 1] over the page so the
    returned ``OcrItem``s share the same frame as the VLM's bbox.
    """
    if not _ocr_available():
        return []
    try:
        import pypdfium2 as pdfium
        import pytesseract
    except Exception as e:
        logger.warning("PDF OCR disabled: %s", e)
        return []

    pdf = None
    try:
        pdf = pdfium.PdfDocument(pdf_bytes)
        if page_index < 0 or page_index >= len(pdf):
            return []
        page = pdf[page_index]
        img = page.render(scale=scale).to_pil()
        if img.mode not in ("L", "RGB"):
            img = img.convert("RGB")
        page_w, page_h = img.size
        if page_w <= 0 or page_h <= 0:
            return []
        data = pytesseract.image_to_data(img, output_type=pytesseract.Output.DICT)
    except Exception as e:
        logger.warning("PDF page OCR failed (page %d): %s", page_index, e)
        return []
    finally:
        if pdf is not None:
            try:
                pdf.close()
            except Exception:
                pass

    items: list[OcrItem] = []
    for i in range(len(data.get("text", []))):
        text = (data["text"][i] or "").strip()
        if not text:
            continue
        try:
            x = float(data["left"][i]) / page_w
            y = float(data["top"][i]) / page_h
            w = float(data["width"][i]) / page_w
            h = float(data["height"][i]) / page_h
        except (TypeError, ValueError, ZeroDivisionError):
            continue
        items.append(OcrItem(text=text, x=x, y=y, w=w, h=h))
    return items


_NUMERIC_TOKEN = re.compile(r"-?\d+(?:\.\d+)?")
_PUNCT_LEAD = re.compile(r"^[^A-Za-z0-9]+")
_PUNCT_TRAIL = re.compile(r"[,.;:'\-)]+$")


def _strip_punct(s: str) -> str:
    """Mirror the iframe ``_stripPunct``: drop leading non-alphanumeric and
    trailing ``,.;:'-)``. Tokens like ``"(Amlodipine)"`` → ``"Amlodipine"``,
    ``"shellfish,"`` → ``"shellfish"`` line up with the form text.
    """
    return _PUNCT_TRAIL.sub("", _PUNCT_LEAD.sub("", s or ""))


def _multi_token_snap(
    items: list[OcrItem],
    tokens: list[str],
    fallback_bbox,
) -> tuple[float, float, float, float] | None:
    """Find the y-row matching ≥60% of ``tokens`` and return the union rect.

    Used for multi-word ``raw_text`` like ``"Amlodipine 5 mg daily"``
    or ``"Ankle swelling in the past 2 weeks"`` where no single OCR
    item contains the whole phrase. Mirrors the iframe's
    ``trySnapMultiToken`` so client and server behavior stay aligned.
    """
    if not tokens or len(tokens) < 2 or not items:
        return None
    fb_w = max(fallback_bbox.w, 0.05)
    fb_cy = fallback_bbox.y + fallback_bbox.h / 2
    required = max(2, math.ceil(len(tokens) * 0.6))

    tokens_lower = [t.lower() for t in tokens if t]
    if not tokens_lower:
        return None

    matches: list[tuple[OcrItem, set[int]]] = []
    for item in items:
        item_text = _strip_punct(item.text).lower()
        if not item_text:
            continue
        matched: set[int] = set()
        for i, tok in enumerate(tokens_lower):
            if tok and tok in item_text:
                matched.add(i)
        if matched:
            matches.append((item, matched))
    if not matches:
        return None

    best: tuple[tuple[int, float], tuple[float, float, float, float]] | None = None
    for anchor, _ in matches:
        anchor_cy = anchor.y + anchor.h / 2
        # Per-anchor row tolerance: half the anchor's height. Tesseract's
        # per-line rect heights track the actual line height, so this
        # admits tokens on the same baseline (intra-row variation in
        # bbox top/bottom is fractions of a glyph) while excluding the
        # next row (typically ≥1× line-height away). Hard min keeps
        # fast/sparse OCR rows from collapsing.
        row_tol = max(0.005, min(0.02, anchor.h * 0.6))
        cluster = [
            (it, idx)
            for it, idx in matches
            if abs((it.y + it.h / 2) - anchor_cy) <= row_tol
        ]
        all_idx: set[int] = set()
        for _, idx in cluster:
            all_idx.update(idx)
        if len(all_idx) < required:
            continue
        x_min = min(it.x for it, _ in cluster)
        x_max = max(it.x + it.w for it, _ in cluster)
        y_min = min(it.y for it, _ in cluster)
        y_max = max(it.y + it.h for it, _ in cluster)
        # Drift guard: union width should not balloon past 1.5× the VLM
        # hint width — keeps stray cross-row substring matches out.
        if x_max - x_min > fb_w * 1.5:
            continue
        # Score: more tokens matched wins; ties broken by closer y to fb_cy.
        score = (len(all_idx), -abs(anchor_cy - fb_cy))
        rect = (x_min, y_min, x_max - x_min, y_max - y_min)
        if best is None or score > best[0]:
            best = (score, rect)
    return best[1] if best else None


def snap_bbox(
    items: list[OcrItem],
    raw_text: str,
    fallback_bbox,  # BoundingBox; uses .x/.y/.w/.h
) -> tuple[float, float, float, float] | None:
    """Snap ``raw_text`` to the best-matching OCR item; return (x, y, w, h).

    Order is shape-aware (mirrors the iframe ``_snapBboxToText``):

      * **First token is numeric** (lab values like ``"142 mg/dL"``,
        vitals like ``"5.6 mg/dL"``): tight-snap to the numeric token,
        falling back to multi-token row union, then whole-string. Keeps
        existing tight highlight on the value.
      * **First token is non-numeric** (med names like ``"Amlodipine 5
        mg daily"``, intake answers like ``"Ankle swelling in the past
        2 weeks"``): try multi-token row-union first so the rectangle
        spans the relevant text. The numeric branch is deliberately
        skipped — for med names the embedded ``"5"`` is incidental and
        snapping to a stray ``"5"`` elsewhere on the page is worse than
        falling back to ``None``.
      * **Single non-numeric token** (``"penicillin"``): whole-string
        equality.

    Returns ``None`` if no strategy matches — caller keeps the VLM bbox.
    OCR coordinates are already normalized (see ``ocr_items`` /
    ``pdf_page_ocr_items``), so the returned rect is in the same [0, 1]
    frame as ``fallback_bbox``.
    """
    target = (raw_text or "").strip()
    if not target or not items:
        return None

    fb_cy = fallback_bbox.y + fallback_bbox.h / 2
    stripped_tokens = [_strip_punct(t) for t in target.split()]
    stripped_tokens = [t for t in stripped_tokens if t]
    first_token_is_numeric = bool(
        stripped_tokens and _NUMERIC_TOKEN.fullmatch(stripped_tokens[0])
    )

    def _try_numeric() -> tuple[float, float, float, float] | None:
        num_match = _NUMERIC_TOKEN.search(target)
        if not num_match:
            return None
        num_token = num_match.group(0)
        cands: list[tuple[OcrItem, float, bool]] = []
        for it in items:
            if num_token not in it.text:
                continue
            cy = it.y + it.h / 2
            cands.append((it, abs(cy - fb_cy), it.text == num_token))
        if not cands:
            return None
        cands.sort(key=lambda c: (not c[2], c[1]))
        it = cands[0][0]
        return (it.x, it.y, it.w, it.h)

    def _try_whole_string() -> tuple[float, float, float, float] | None:
        cands: list[tuple[OcrItem, float]] = []
        for it in items:
            if it.text == target:
                cy = it.y + it.h / 2
                cands.append((it, abs(cy - fb_cy)))
        if not cands:
            return None
        cands.sort(key=lambda c: c[1])
        it = cands[0][0]
        return (it.x, it.y, it.w, it.h)

    def _try_multi_token() -> tuple[float, float, float, float] | None:
        return _multi_token_snap(items, stripped_tokens, fallback_bbox)

    if first_token_is_numeric:
        return _try_numeric() or _try_multi_token() or _try_whole_string()
    return _try_multi_token() or _try_whole_string()

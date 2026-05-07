"""OCR-based bbox snap for image (PNG/JPG) extractions.

Claude vision (the VLM) emits approximate bboxes on rasterized photos —
documented model behavior. PDF extractions can rescue this in the iframe
via PDF.js's text layer (see ``app/web/copilot_iframe.js::_snapBboxToText``),
but images have no equivalent. This module fills that gap server-side:

  1. ``ocr_items(image_bytes)`` runs Tesseract once over the image and
     returns every detected text item normalized to [0, 1] over the image
     dimensions (matching the BoundingBox schema).
  2. ``snap_bbox(items, raw_text, fallback_bbox)`` searches for the OCR
     item that best matches ``raw_text`` (preferring the numeric token,
     disambiguated by vertical proximity to ``fallback_bbox``) and
     returns its rect. Caller falls back to ``fallback_bbox`` on None.

The caller mutates ``source_citation.bbox`` in place at extraction time
so both the SQLite store and the bbox_overlay payload carry the snapped
rect from the moment the document is recorded.
"""

from __future__ import annotations

import io
import logging
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


_NUMERIC_TOKEN = re.compile(r"-?\d+(?:\.\d+)?")


def snap_bbox(
    items: list[OcrItem],
    raw_text: str,
    fallback_bbox,  # BoundingBox; uses .x/.y/.w/.h
) -> tuple[float, float, float, float] | None:
    """Snap ``raw_text`` to the best-matching OCR item; return (x, y, w, h).

    Same disambiguation strategy as the PDF text-snap helper:

      * If ``raw_text`` contains a numeric token (lab values, vitals),
        prefer items containing that token, picking the one whose
        vertical center is closest to ``fallback_bbox``'s center.
      * Otherwise fall back to whole-string equality (intake answers,
        allergy strings).
      * Returns ``None`` if no candidate matches — caller keeps the VLM
        bbox.

    OCR coordinates are already normalized to the image (see
    ``ocr_items``), so the returned rect is in the same [0, 1] frame as
    ``fallback_bbox``.
    """
    target = (raw_text or "").strip()
    if not target or not items:
        return None

    fb_cy = fallback_bbox.y + fallback_bbox.h / 2

    candidates: list[tuple[OcrItem, float, bool]] = []
    num_match = _NUMERIC_TOKEN.search(target)
    if num_match:
        num_token = num_match.group(0)
        for it in items:
            if num_token not in it.text:
                continue
            cy = it.y + it.h / 2
            candidates.append((it, abs(cy - fb_cy), it.text == num_token))

    if not candidates:
        for it in items:
            if it.text == target:
                cy = it.y + it.h / 2
                candidates.append((it, abs(cy - fb_cy), True))

    if not candidates:
        return None

    # Prefer exact-token matches over substring; then the y-closest one.
    candidates.sort(key=lambda c: (not c[2], c[1]))
    it = candidates[0][0]
    return (it.x, it.y, it.w, it.h)

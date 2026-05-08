# app/ingestion/service.py
"""Single ingestion seam — used by the HTTP route and the agent tool.

Pipeline: hash → dedup-lookup → DocumentReference write (or skip on hit) →
VLM call → derived FHIR writes → record in dedup store.

The service is the *only* code path that calls the VLM; both `/v1/documents/attach`
and the `attach_and_extract` agent tool route through here.
"""
from __future__ import annotations

from dataclasses import dataclass
from typing import Any

from app.fhir.client import FhirClient
from app.ingestion.fhir_writer import write_extraction
from app.ingestion.ocr import ocr_items, snap_bbox
from app.ingestion.schemas import (
    BoundingBox,
    DocType,
    IntakeFormExtraction,
    LabPDFExtraction,
    MimeType,
)
from app.ingestion.vlm import VlmExtractor
from app.observability.vlm_span import vlm_span_output
from app.persistence.processed_documents import (
    ProcessedDocument,
    ProcessedDocumentStore,
    hash_bytes,
)


@dataclass
class BboxOverlayItem:
    page: int
    bbox: BoundingBox
    field_or_chunk_id: str
    record_id: str
    raw_text: str


@dataclass
class IngestionResult:
    doc_id: str
    # ``None`` when the result represents a deferred-extraction (pending) row
    # produced by ``IngestionService.attach_only``. Callers must handle the
    # pending case before serializing.
    extraction: LabPDFExtraction | IntakeFormExtraction | None
    bbox_overlay: list[BboxOverlayItem]
    was_dedup_hit: bool
    span_output: dict[str, Any] | None  # for the caller's tracer
    # W2 KR8: per-call USD cost estimate from the VLM extraction. None when
    # this was a cache hit (no fresh VLM call) or when the model_id isn't
    # in the cost table (`app.observability.cost`).
    cost_estimate_usd: float | None = None
    # True when the row carries a ``_pending`` marker — set by
    # ``attach_only``. Lets the HTTP route shape the response without
    # checking the extraction field's truthiness.
    is_pending: bool = False


def _walk_citations(payload: Any):
    """Yield (cite, parent_obj) pairs for every SourceCitation in the extraction."""
    if hasattr(payload, "model_fields"):
        for field_name in payload.model_fields:
            child = getattr(payload, field_name)
            if field_name == "source_citation" and child is not None:
                yield child, payload
                continue
            if isinstance(child, list):
                for item in child:
                    yield from _walk_citations(item)
            elif hasattr(child, "model_fields"):
                yield from _walk_citations(child)


async def _ocr_snap_extraction(extraction: Any, image_bytes: bytes) -> None:
    """Mutate ``extraction.*.source_citation.bbox`` in place using OCR.

    Runs Tesseract once on the full image (synchronous CPU work, dispatched
    to a worker thread so the event loop stays responsive), then walks every
    SourceCitation with a bbox+raw_text and snaps the bbox to the OCR-
    detected glyph rect when a confident match exists. No-ops when OCR is
    unavailable or no item matches — the VLM bbox is preserved.
    """
    import asyncio
    items = await asyncio.to_thread(ocr_items, image_bytes)
    if not items:
        return
    for cite, _parent in _walk_citations(extraction):
        if cite.bbox is None or not cite.raw_text:
            continue
        snapped = snap_bbox(items, cite.raw_text, cite.bbox)
        if snapped is None:
            continue
        x, y, w, h = snapped
        # Clamp to [0, 1] in case OCR rounding pushes us a hair outside.
        cite.bbox = BoundingBox(
            x=max(0.0, min(1.0, x)),
            y=max(0.0, min(1.0, y)),
            w=max(0.0, min(1.0, w)),
            h=max(0.0, min(1.0, h)),
        )


def _bbox_overlay(extraction: Any, doc_id: str) -> list[BboxOverlayItem]:
    items: list[BboxOverlayItem] = []
    for cite, _ in _walk_citations(extraction):
        if cite.bbox is None or cite.page is None:
            continue
        items.append(
            BboxOverlayItem(
                page=cite.page,
                bbox=cite.bbox,
                field_or_chunk_id=cite.field_or_chunk_id,
                record_id=cite.source_doc_id,
                raw_text=cite.raw_text,
            )
        )
    return items


class IngestionService:
    def __init__(
        self,
        *,
        fhir: FhirClient,
        vlm: VlmExtractor,
        store: ProcessedDocumentStore,
    ) -> None:
        self._fhir = fhir
        self._vlm = vlm
        self._store = store

    async def attach_and_extract(
        self,
        *,
        patient_fhir_id: str,
        patient_pseudonym: str,
        doc_type: DocType,
        mime_type: MimeType,
        file_bytes: bytes,
        physician_user_id: str,
    ) -> IngestionResult:
        sha = hash_bytes(file_bytes)
        prior = await self._store.lookup(
            patient_pseudonym=patient_pseudonym, hash=sha
        )
        if prior is not None:
            # Reconstruct the typed extraction from the stored JSON for overlay rebuild.
            cls = LabPDFExtraction if prior.doc_type == "lab_doc" else IntakeFormExtraction
            cached = cls.model_validate(prior.extracted_facts)
            return IngestionResult(
                doc_id=prior.canonical_doc_id,
                extraction=cached,
                bbox_overlay=_bbox_overlay(cached, prior.canonical_doc_id),
                was_dedup_hit=True,
                span_output=None,
            )

        doc = await self._fhir.create_document_reference(
            patient_fhir_id=patient_fhir_id,
            doc_type=doc_type,
            mime_type=mime_type,
            file_bytes=file_bytes,
            sha3_hex=sha,
            physician_user_id=physician_user_id,
        )
        doc_id = doc["id"]

        import time as _t
        t0 = _t.perf_counter()
        extraction, vlm_meta = await self._vlm.extract(
            file_bytes=file_bytes,
            mime_type=mime_type,
            doc_type=doc_type,
            doc_id=doc_id,
        )
        latency_ms = (_t.perf_counter() - t0) * 1000.0

        # OCR-snap for image extractions: Claude vision is approximate at
        # pixel localization on rasterized photos. Run Tesseract once and
        # rewrite each fact's bbox to the OCR-detected glyph rect.
        # PDFs have a text layer the iframe snaps to client-side and don't
        # need this pass.
        if mime_type.startswith("image/"):
            await _ocr_snap_extraction(extraction, file_bytes)

        await write_extraction(
            extraction,
            fhir=self._fhir,
            patient_fhir_id=patient_fhir_id,
            doc_id=doc_id,
            physician_user_id=physician_user_id,
        )

        await self._store.record(
            patient_pseudonym=patient_pseudonym,
            hash=sha,
            canonical_doc_id=doc_id,
            doc_type=doc_type,
            extracted_facts=extraction.model_dump(mode="json"),
            source_path="attach_route",
            file_bytes=file_bytes,
            mime_type=mime_type,
        )

        span_output = vlm_span_output(
            extraction,
            doc_id=doc_id,
            doc_type=doc_type,
            mime_type=mime_type,
            model_id=vlm_meta.model_id,
            latency_ms=latency_ms,
        )

        # W2 KR8: per-call USD cost estimate. None if model_id isn't priced.
        from app.observability.cost import estimate_anthropic_cost_usd
        cost_usd = estimate_anthropic_cost_usd(
            model_id=vlm_meta.model_id,
            input_tokens=vlm_meta.input_tokens,
            output_tokens=vlm_meta.output_tokens,
            cache_read_tokens=vlm_meta.cache_read_tokens,
            cache_write_tokens=vlm_meta.cache_creation_tokens,
        )

        return IngestionResult(
            doc_id=doc_id,
            extraction=extraction,
            bbox_overlay=_bbox_overlay(extraction, doc_id),
            was_dedup_hit=False,
            span_output=span_output,
            cost_estimate_usd=cost_usd,
        )

    async def attach_only(
        self,
        *,
        patient_fhir_id: str,
        patient_pseudonym: str,
        doc_type: DocType,
        mime_type: MimeType,
        file_bytes: bytes,
        physician_user_id: str,
    ) -> IngestionResult:
        """W2 LITE — front-desk skip-extraction path.

        Hashes the file, dedups, and stores the raw bytes plus a
        ``{"_pending": True}`` marker in ``processed_documents`` so the
        physician's pending-intake banner surfaces it. Skips VLM, OCR, and
        derived FHIR writes — those run later when the physician clicks
        the banner item and ``process_pending`` is invoked via
        ``/v1/documents/{doc_id}/process``.

        Returns an ``IngestionResult`` with ``is_pending=True`` and
        ``extraction=None``.
        """
        sha = hash_bytes(file_bytes)
        prior = await self._store.lookup(
            patient_pseudonym=patient_pseudonym, hash=sha
        )
        if prior is not None:
            # Already filed (front-desk re-uploaded the same scan, or admin
            # already extracted). Surface as deduped — no rework.
            cached_facts = prior.extracted_facts
            still_pending = (
                isinstance(cached_facts, dict) and cached_facts.get("_pending")
            )
            return IngestionResult(
                doc_id=prior.canonical_doc_id,
                extraction=None,
                bbox_overlay=[],
                was_dedup_hit=True,
                span_output=None,
                is_pending=bool(still_pending),
            )

        # Mirror the synthesized id scheme used by FhirClient.create_document_reference
        # (currently a stub — see app/fhir/client.py:116-139). Stable across
        # restarts because it derives from the file hash.
        doc_id = f"copilot-{sha[:16]}"
        await self._store.record(
            patient_pseudonym=patient_pseudonym,
            hash=sha,
            canonical_doc_id=doc_id,
            doc_type=doc_type,
            extracted_facts={"_pending": True},
            source_path="front_desk_scan",
            file_bytes=file_bytes,
            mime_type=mime_type,
        )
        return IngestionResult(
            doc_id=doc_id,
            extraction=None,
            bbox_overlay=[],
            was_dedup_hit=False,
            span_output=None,
            is_pending=True,
        )

    async def process_pending(
        self,
        *,
        row: "ProcessedDocument",
        physician_user_id: str,
    ) -> IngestionResult:
        """Run VLM + persist extraction for a row that was filed by ``attach_only``.

        Idempotent: if the row no longer carries the ``_pending`` marker
        (already processed), reconstruct the cached extraction and return
        it without re-running VLM.

        Mirrors the post-DocumentReference half of ``attach_and_extract``:
        VLM extract → OCR snap (image/* only) → derived FHIR writes →
        update ``processed_documents`` with the real ``extracted_facts``.
        """
        # Idempotent fast-path: already extracted.
        facts = row.extracted_facts
        if not (isinstance(facts, dict) and facts.get("_pending")):
            cls = (
                LabPDFExtraction if row.doc_type == "lab_doc"
                else IntakeFormExtraction
            )
            cached = cls.model_validate(facts)
            return IngestionResult(
                doc_id=row.canonical_doc_id,
                extraction=cached,
                bbox_overlay=_bbox_overlay(cached, row.canonical_doc_id),
                was_dedup_hit=True,
                span_output=None,
                is_pending=False,
            )

        if not row.file_bytes:
            raise ValueError(
                f"pending row {row.canonical_doc_id!r} has no stored bytes"
            )
        if not row.mime_type:
            raise ValueError(
                f"pending row {row.canonical_doc_id!r} has no mime_type"
            )

        # Type-narrow doc_type to a known DocType. ``attach_only`` only ever
        # stores the validated form, but row.doc_type is typed as ``str`` on
        # the way back so re-assert here.
        doc_type: DocType = row.doc_type  # type: ignore[assignment]
        mime_type: MimeType = row.mime_type  # type: ignore[assignment]

        import time as _t
        t0 = _t.perf_counter()
        extraction, vlm_meta = await self._vlm.extract(
            file_bytes=row.file_bytes,
            mime_type=mime_type,
            doc_type=doc_type,
            doc_id=row.canonical_doc_id,
        )
        latency_ms = (_t.perf_counter() - t0) * 1000.0

        if mime_type.startswith("image/"):
            await _ocr_snap_extraction(extraction, row.file_bytes)

        await write_extraction(
            extraction,
            fhir=self._fhir,
            patient_fhir_id=row.patient_pseudonym,  # MVP: pseudonym == fhir_id
            doc_id=row.canonical_doc_id,
            physician_user_id=physician_user_id,
        )

        await self._store.replace_extraction(
            patient_pseudonym=row.patient_pseudonym,
            canonical_doc_id=row.canonical_doc_id,
            extracted_facts=extraction.model_dump(mode="json"),
            doc_type=doc_type,
        )

        span_output = vlm_span_output(
            extraction,
            doc_id=row.canonical_doc_id,
            doc_type=doc_type,
            mime_type=mime_type,
            model_id=vlm_meta.model_id,
            latency_ms=latency_ms,
        )

        from app.observability.cost import estimate_anthropic_cost_usd
        cost_usd = estimate_anthropic_cost_usd(
            model_id=vlm_meta.model_id,
            input_tokens=vlm_meta.input_tokens,
            output_tokens=vlm_meta.output_tokens,
            cache_read_tokens=vlm_meta.cache_read_tokens,
            cache_write_tokens=vlm_meta.cache_creation_tokens,
        )

        return IngestionResult(
            doc_id=row.canonical_doc_id,
            extraction=extraction,
            bbox_overlay=_bbox_overlay(extraction, row.canonical_doc_id),
            was_dedup_hit=False,
            span_output=span_output,
            cost_estimate_usd=cost_usd,
            is_pending=False,
        )

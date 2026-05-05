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
from app.ingestion.schemas import (
    BoundingBox,
    DocType,
    IntakeFormExtraction,
    LabPDFExtraction,
    MimeType,
    SourceCitation,
)
from app.ingestion.vlm import VlmExtractor
from app.observability.vlm_span import vlm_span_output
from app.persistence.processed_documents import (
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
    extraction: LabPDFExtraction | IntakeFormExtraction
    bbox_overlay: list[BboxOverlayItem]
    was_dedup_hit: bool
    span_output: dict[str, Any] | None  # for the caller's tracer


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

        return IngestionResult(
            doc_id=doc_id,
            extraction=extraction,
            bbox_overlay=_bbox_overlay(extraction, doc_id),
            was_dedup_hit=False,
            span_output=span_output,
        )

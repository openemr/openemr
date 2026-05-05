# app/tools/document_tools.py
"""Agent-callable tools for working with uploaded clinical documents.

- `attach_and_extract` (PRD §1): runs ingestion mid-turn for a doc on disk.
- `get_recent_uploads`: reads the most recent processed_documents rows so the
  agent can answer questions about a doc the physician just dropped in the
  iframe (without having to call OpenEMR/FHIR for the same data — those reads
  fail when the deployed Co-Pilot can't reach OpenEMR, and the extracted
  facts live in Co-Pilot's own SQLite anyway).

Both tools produce ToolResults whose `record_ids` flow through the existing
verify() gate without rule changes.
"""
from __future__ import annotations

from pathlib import Path
from typing import Any

from app.ingestion.schemas import (
    IntakeFormExtraction,
    LabPDFExtraction,
    encode_record_id_for_vlm,
    field_id_for_lab_result,
)
from app.ingestion.service import IngestionService
from app.persistence.processed_documents import ProcessedDocumentStore
from app.phi.session import PseudonymMap
from app.tools._base import ToolResult


SCHEMA: dict[str, Any] = {
    "name": "attach_and_extract",
    "description": (
        "Ingest a previously uploaded clinical document for the active patient. "
        "Returns the structured extraction. Use this only when the user asks "
        "about a document that is sitting on disk and not yet extracted."
    ),
    "input_schema": {
        "type": "object",
        "properties": {
            "doc_type": {"type": "string", "enum": ["lab_doc", "intake_form_doc"]},
            "mime_type": {
                "type": "string",
                "enum": ["application/pdf", "image/png", "image/jpeg"],
            },
            "file_path": {"type": "string"},
        },
        "required": ["doc_type", "mime_type", "file_path"],
    },
}


async def run_attach_and_extract(
    *,
    ingestion_service: IngestionService,
    session: PseudonymMap,
    args: dict[str, Any],
) -> ToolResult:
    file_path = args["file_path"]
    inline = args.get("_inline_bytes")  # test seam — never set in production
    file_bytes = inline if inline is not None else Path(file_path).read_bytes()

    result = await ingestion_service.attach_and_extract(
        patient_fhir_id=getattr(session, "patient_fhir_id", session.patient_pseudonym()),
        patient_pseudonym=session.patient_pseudonym(),
        doc_type=args["doc_type"],
        mime_type=args["mime_type"],
        file_bytes=file_bytes,
        physician_user_id=session.physician_user_id,
    )

    record_ids: list[str] = [f"DocumentReference/{result.doc_id}"]
    if isinstance(result.extraction, LabPDFExtraction):
        for idx, lab in enumerate(result.extraction.results):
            if lab.source_citation.bbox is None or lab.source_citation.page is None:
                continue
            record_ids.append(
                encode_record_id_for_vlm(
                    doc_id=result.doc_id,
                    page=lab.source_citation.page,
                    bbox=lab.source_citation.bbox,
                    field_or_chunk_id=field_id_for_lab_result(lab, idx),
                )
            )

    return ToolResult(
        name="attach_and_extract",
        record_type="DocumentReference",
        data=[result.extraction.model_dump(mode="json")],
        record_ids=record_ids,
    )


GET_RECENT_UPLOADS_SCHEMA: dict[str, Any] = {
    "name": "get_recent_uploads",
    "description": (
        "Return the most recent clinical documents uploaded for the active "
        "patient with their structured extractions. Use this when the "
        "physician asks about a lab or intake form they just dropped in the "
        "iframe (e.g. 'what was the LDL on the lab I just uploaded?'). The "
        "extracted facts include lab values, allergies, medications, and "
        "intake demographics — all anchored to the source DocumentReference "
        "so claims you make can cite them."
    ),
    "input_schema": {
        "type": "object",
        "properties": {
            "limit": {
                "type": "integer",
                "minimum": 1,
                "maximum": 10,
                "default": 3,
            },
        },
    },
}


async def run_get_recent_uploads(
    *,
    store: ProcessedDocumentStore,
    session: PseudonymMap,
    args: dict[str, Any],
) -> ToolResult:
    limit = int(args.get("limit") or 3)
    # The HTTP attach route stores rows keyed by raw FHIR uuid (no session
    # context at upload time). The session's `patient_pseudonym()` is a
    # randomized "Patient-XXXX" label used for trace minimization, NOT the
    # storage key. Use `active_patient_id` (the real FHIR uuid this session
    # is scoped to) so the lookup matches what the route stored.
    docs = await store.list_recent_for_patient(
        patient_pseudonym=session.active_patient_id, limit=limit
    )
    payload: list[dict[str, Any]] = []
    record_ids: list[str] = []
    for d in docs:
        record_ids.append(f"DocumentReference/{d.canonical_doc_id}")
        # Re-emit per-fact record_ids so any claim citing a specific lab value
        # passes verify() the same way attach_and_extract's claims do.
        if d.doc_type == "lab_doc":
            try:
                lab_extr = LabPDFExtraction.model_validate(d.extracted_facts)
                for idx, lab in enumerate(lab_extr.results):
                    if lab.source_citation.bbox and lab.source_citation.page:
                        record_ids.append(
                            encode_record_id_for_vlm(
                                doc_id=d.canonical_doc_id,
                                page=lab.source_citation.page,
                                bbox=lab.source_citation.bbox,
                                field_or_chunk_id=field_id_for_lab_result(lab, idx),
                            )
                        )
            except Exception:  # noqa: BLE001 — never let a malformed row break the tool
                pass
        payload.append(
            {
                "doc_id": d.canonical_doc_id,
                "doc_type": d.doc_type,
                "extracted_at": d.extracted_at.isoformat(),
                "extraction": d.extracted_facts,
            }
        )
    return ToolResult(
        name="get_recent_uploads",
        record_type="DocumentReference",
        data=payload,
        record_ids=record_ids,
    )

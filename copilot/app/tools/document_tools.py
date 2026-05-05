# app/tools/document_tools.py
"""attach_and_extract — agent-callable tool that triggers ingestion mid-turn.

PRD §1 names this tool explicitly. The implementation is a thin shim over
IngestionService that produces a ToolResult whose `record_ids` cover both the
parent DocumentReference and every derived FHIR resource — that's what makes
downstream `verify()` accept any claim citing a derived resource without
custom rules.
"""
from __future__ import annotations

from pathlib import Path
from typing import Any

from app.ingestion.schemas import (
    LabPDFExtraction,
    encode_record_id_for_vlm,
    field_id_for_lab_result,
)
from app.ingestion.service import IngestionService
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

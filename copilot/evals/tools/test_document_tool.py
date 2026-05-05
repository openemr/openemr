# evals/tools/test_document_tool.py
"""attach_and_extract tool — record_ids include the doc + every derived resource."""
from __future__ import annotations

import pytest
from unittest.mock import AsyncMock, MagicMock

from app.ingestion.service import IngestionResult
from app.ingestion.schemas import (
    BoundingBox, LabPDFExtraction, LabResult, SourceCitation,
)
from datetime import date

from app.tools.document_tools import run_attach_and_extract


def _stub_result() -> IngestionResult:
    return IngestionResult(
        doc_id="doc-1",
        extraction=LabPDFExtraction(
            results=[
                LabResult(
                    test_name="LDL", analyte_key="ldl_cholesterol", loinc_code=None,
                    value=142.0, unit="mg/dL", reference_range="<100",
                    collection_date=date(2026, 4, 30), abnormal_flag="H",
                    source_citation=SourceCitation(
                        source_doc_id="DocumentReference/doc-1", page=1,
                        bbox=BoundingBox(x=0.1, y=0.2, w=0.3, h=0.04),
                        raw_text="LDL 142", confidence=0.9, source_kind="document",
                        field_or_chunk_id="results[ldl_cholesterol].value",
                    ),
                )
            ],
            document_date=date(2026, 4, 30),
        ),
        bbox_overlay=[],
        was_dedup_hit=False,
        span_output={"extracted_field_count": 1},
    )


async def test_attach_tool_emits_record_ids_for_doc_and_each_observation():
    svc = MagicMock()
    svc.attach_and_extract = AsyncMock(return_value=_stub_result())
    session = MagicMock()
    session.physician_user_id = "dr_who"
    session.patient_pseudonym = MagicMock(return_value="patient-7")
    session.patient_fhir_id = "patient-7"

    result = await run_attach_and_extract(
        ingestion_service=svc,
        session=session,
        args={
            "doc_type": "lab_doc",
            "mime_type": "application/pdf",
            "file_path": "/tmp/lipid.pdf",
            "_inline_bytes": b"%PDF-1.4 fake",  # test injection avoids file-system dependency
        },
    )
    assert result.error is None
    assert "DocumentReference/doc-1" in result.record_ids
    # The expected record_id encoding for the lab uses the analyte_key path
    assert any("results[ldl_cholesterol].value" in rid for rid in result.record_ids)

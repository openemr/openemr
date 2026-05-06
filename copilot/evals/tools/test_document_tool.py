# evals/tools/test_document_tool.py
"""attach_and_extract + get_recent_uploads tools — record_ids and per-fact
data items both populated so Layer-1 attribution AND Layer-2 cross-patient-
leakage checks pass."""
from __future__ import annotations

import os
import tempfile
from datetime import date
from unittest.mock import AsyncMock, MagicMock

import pytest

from app.ingestion.service import IngestionResult
from app.ingestion.schemas import (
    BoundingBox, LabPDFExtraction, LabResult, SourceCitation,
)
from app.persistence.processed_documents import ProcessedDocumentStore
from app.tools.document_tools import (
    run_attach_and_extract,
    run_get_recent_uploads,
)
from app.verification.rules import _record_belongs_to_active_patient


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


async def test_attach_tool_data_items_pass_cross_patient_leakage_check():
    """Every record_id the tool emits must appear as `record_id` on a
    matching data item with `subject_pseudonym` set, so
    `_record_belongs_to_active_patient` returns True for each cited id."""
    svc = MagicMock()
    svc.attach_and_extract = AsyncMock(return_value=_stub_result())
    session = MagicMock()
    session.physician_user_id = "dr_who"
    session.patient_pseudonym = MagicMock(return_value="Patient-XYZ")
    session.patient_fhir_id = "patient-7"

    result = await run_attach_and_extract(
        ingestion_service=svc,
        session=session,
        args={
            "doc_type": "lab_doc",
            "mime_type": "application/pdf",
            "file_path": "/tmp/lipid.pdf",
            "_inline_bytes": b"%PDF-1.4 fake",
        },
    )

    # Simulate what the agent loop does: pack the ToolResult into the same
    # dict shape verify() consumes.
    tool_result_dict = result.to_dict()

    # Every record_id should be findable in the data, with the right subject.
    for rid in result.record_ids:
        assert _record_belongs_to_active_patient(
            rid, [tool_result_dict], "Patient-XYZ"
        ), f"record_id {rid} not anchored to active patient in data items"

    # And the data items should carry the actual lab fields (the agent reads
    # `value`, `unit`, etc. directly from the data items).
    obs_items = [d for d in tool_result_dict["data"] if d.get("resourceType") == "Observation"]
    assert obs_items, "expected at least one Observation-shaped data item"
    assert obs_items[0]["test_name"] == "LDL"
    assert obs_items[0]["value"] == pytest.approx(142.0)
    assert obs_items[0]["unit"] == "mg/dL"


async def test_get_recent_uploads_data_items_pass_cross_patient_leakage_check():
    """Same contract as attach_and_extract — every emitted record_id must
    appear as a data-item key with subject_pseudonym set."""
    fd, db_path = tempfile.mkstemp(suffix=".db")
    os.close(fd)
    store = ProcessedDocumentStore(db_path)
    await store.init()
    try:
        # Seed one row keyed by the active_patient_id (raw FHIR uuid), as
        # the HTTP attach route does.
        extraction = LabPDFExtraction(
            results=[
                LabResult(
                    test_name="HDL", analyte_key="hdl_cholesterol", loinc_code=None,
                    value=38.0, unit="mg/dL", reference_range=">40",
                    collection_date=date(2026, 4, 30), abnormal_flag="L",
                    source_citation=SourceCitation(
                        source_doc_id="DocumentReference/copilot-abc",
                        page=1,
                        bbox=BoundingBox(x=0.1, y=0.16, w=0.42, h=0.025),
                        raw_text="HDL 38", confidence=0.9, source_kind="document",
                        field_or_chunk_id="results[hdl_cholesterol].value",
                    ),
                )
            ],
            document_date=date(2026, 4, 30),
        )
        await store.record(
            patient_pseudonym="patient-7",
            hash="abc",
            canonical_doc_id="copilot-abc",
            doc_type="lab_doc",
            extracted_facts=extraction.model_dump(mode="json"),
            source_path="attach_route",
        )

        session = MagicMock()
        session.physician_user_id = "dr_who"
        session.active_patient_id = "patient-7"
        session.patient_pseudonym = MagicMock(return_value="Patient-XYZ")

        result = await run_get_recent_uploads(
            store=store, session=session, args={}
        )
        tool_result_dict = result.to_dict()

        assert result.record_ids, "expected at least one record_id"
        for rid in result.record_ids:
            assert _record_belongs_to_active_patient(
                rid, [tool_result_dict], "Patient-XYZ"
            ), f"record_id {rid} not anchored to active patient in data items"

        obs_items = [d for d in tool_result_dict["data"] if d.get("resourceType") == "Observation"]
        assert obs_items, "expected an Observation-shaped data item from get_recent_uploads"
        assert obs_items[0]["value"] == pytest.approx(38.0)
    finally:
        os.unlink(db_path)

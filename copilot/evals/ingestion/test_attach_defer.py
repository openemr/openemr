"""W2 LITE deferred-extraction tests.

Front-desk role (``COPILOT_FRONT_DESK_USERS``) bypasses the panel gate AND
skips VLM at upload time. The doc lands in ``processed_documents`` with a
``{"_pending": True}`` marker. The physician later POSTs
``/v1/documents/{doc_id}/process`` to run extraction on demand; that path
is idempotent and panel-gated.

These tests pin the role-detection + defer behavior without booting the
real VLM / FHIR client.
"""
from __future__ import annotations

import json
from datetime import date
from unittest.mock import AsyncMock

import pytest
from fastapi.testclient import TestClient

from app import main as main_module
from app.ingestion.schemas import (
    BoundingBox,
    LabPDFExtraction,
    LabResult,
    SourceCitation,
)
from app.ingestion.service import BboxOverlayItem, IngestionResult


FRONT_DESK = "Reception Desk"
PHYSICIAN = "dr_who"
PATIENT_ID = "patient-7"


@pytest.fixture
def client(monkeypatch, tmp_path) -> TestClient:
    monkeypatch.setenv("OPENEMR_BASE_URL", "https://emr.example.com")
    monkeypatch.setenv("OPENEMR_FHIR_BASE", "https://emr.example.com/apis/default/fhir")
    monkeypatch.setenv("OPENEMR_CLIENT_ID", "x")
    monkeypatch.setenv("OPENEMR_CLIENT_SECRET", "x")
    monkeypatch.setenv("OPENEMR_VERIFY_TLS", "false")
    monkeypatch.setenv("ANTHROPIC_API_KEY", "test-key")
    monkeypatch.setenv("CONVERSATION_DB_PATH", str(tmp_path / "copilot.db"))
    monkeypatch.setenv("COPILOT_DOCS_DB_PATH", str(tmp_path / "docs.db"))
    monkeypatch.setenv(
        "PHYSICIAN_PATIENT_PANEL",
        json.dumps({PHYSICIAN: [PATIENT_ID]}),
    )
    monkeypatch.setenv("COPILOT_FRONT_DESK_USERS", FRONT_DESK)

    from app.config import get_settings
    get_settings.cache_clear()

    return TestClient(main_module.app)


def _pending_result() -> IngestionResult:
    return IngestionResult(
        doc_id="copilot-abcdef0123456789",
        extraction=None,
        bbox_overlay=[],
        was_dedup_hit=False,
        span_output=None,
        is_pending=True,
    )


def _extracted_result() -> IngestionResult:
    extraction = LabPDFExtraction(
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
    )
    return IngestionResult(
        doc_id="doc-1",
        extraction=extraction,
        bbox_overlay=[
            BboxOverlayItem(
                page=1,
                bbox=BoundingBox(x=0.1, y=0.2, w=0.3, h=0.04),
                field_or_chunk_id="results[ldl_cholesterol].value",
                record_id="DocumentReference/doc-1",
                raw_text="LDL 142",
            )
        ],
        was_dedup_hit=False,
        span_output={"extracted_field_count": 1},
        is_pending=False,
    )


def test_front_desk_upload_routes_to_attach_only(client: TestClient) -> None:
    """Front-desk username → attach_only fires, attach_and_extract does NOT."""
    with client:
        attach_only = AsyncMock(return_value=_pending_result())
        attach_and_extract = AsyncMock(return_value=_extracted_result())
        client.app.state.ingestion_service.attach_only = attach_only
        client.app.state.ingestion_service.attach_and_extract = attach_and_extract
        r = client.post(
            "/v1/documents/attach",
            data={
                "patient_id": PATIENT_ID,
                "doc_type": "intake_form_doc",
                "mime_type": "application/pdf",
                "physician_user_id": FRONT_DESK,
            },
            files={"file": ("intake.pdf", b"%PDF-1.4 fake", "application/pdf")},
        )
    assert r.status_code == 200, r.text
    body = r.json()
    assert body["is_pending"] is True
    assert body["extraction"] is None
    assert body["bbox_overlay"] == []
    attach_only.assert_awaited_once()
    attach_and_extract.assert_not_awaited()


def test_front_desk_upload_skips_panel_gate(client: TestClient) -> None:
    """Front-desk username may upload to a patient outside their panel.

    PHYSICIAN_PATIENT_PANEL doesn't list ``Reception Desk``; without the
    bypass the request would 403 at session-create-style panel-gate logic.
    """
    with client:
        attach_only = AsyncMock(return_value=_pending_result())
        client.app.state.ingestion_service.attach_only = attach_only
        r = client.post(
            "/v1/documents/attach",
            data={
                "patient_id": "patient-not-in-any-panel",
                "doc_type": "intake_form_doc",
                "mime_type": "application/pdf",
                "physician_user_id": FRONT_DESK,
            },
            files={"file": ("intake.pdf", b"%PDF-1.4 fake", "application/pdf")},
        )
    assert r.status_code == 200, r.text
    attach_only.assert_awaited_once()


def test_physician_upload_routes_to_attach_and_extract(client: TestClient) -> None:
    """Physician (not in COPILOT_FRONT_DESK_USERS) → existing extract path."""
    with client:
        attach_only = AsyncMock(return_value=_pending_result())
        attach_and_extract = AsyncMock(return_value=_extracted_result())
        client.app.state.ingestion_service.attach_only = attach_only
        client.app.state.ingestion_service.attach_and_extract = attach_and_extract
        r = client.post(
            "/v1/documents/attach",
            data={
                "patient_id": PATIENT_ID,
                "doc_type": "lab_doc",
                "mime_type": "application/pdf",
                "physician_user_id": PHYSICIAN,
            },
            files={"file": ("lipid.pdf", b"%PDF-1.4 fake", "application/pdf")},
        )
    assert r.status_code == 200, r.text
    body = r.json()
    assert body["is_pending"] is False
    assert body["extraction"] is not None
    assert len(body["bbox_overlay"]) == 1
    attach_and_extract.assert_awaited_once()
    attach_only.assert_not_awaited()


def test_process_pending_runs_vlm_and_returns_extraction(
    client: TestClient, monkeypatch
) -> None:
    """POST /v1/documents/{doc_id}/process: lazy extraction on a pending row."""
    from app.persistence.processed_documents import ProcessedDocument
    from datetime import datetime, timezone

    with client:
        # Seed a pending row in the store.
        pending_row = ProcessedDocument(
            patient_pseudonym=PATIENT_ID,
            hash="0" * 128,
            canonical_doc_id="copilot-pending-1",
            doc_type="lab_doc",
            extracted_facts={"_pending": True},
            source_path="front_desk_scan",
            extracted_at=datetime.now(timezone.utc),
            file_bytes=b"%PDF-1.4 fake",
            mime_type="application/pdf",
        )

        store = client.app.state.processed_documents
        store.lookup_by_doc_id = AsyncMock(return_value=pending_row)
        client.app.state.ingestion_service.process_pending = AsyncMock(
            return_value=_extracted_result()
        )

        r = client.post(
            f"/v1/documents/copilot-pending-1/process"
            f"?patient_id={PATIENT_ID}&physician_user_id={PHYSICIAN}"
        )
    assert r.status_code == 200, r.text
    body = r.json()
    assert body["doc_id"] == "doc-1"
    assert body["is_pending"] is False
    assert body["extraction"] is not None
    assert len(body["bbox_overlay"]) == 1


def test_process_pending_returns_404_when_doc_missing(client: TestClient) -> None:
    with client:
        client.app.state.processed_documents.lookup_by_doc_id = AsyncMock(
            return_value=None
        )
        r = client.post(
            "/v1/documents/copilot-missing/process"
            f"?patient_id={PATIENT_ID}&physician_user_id={PHYSICIAN}"
        )
    assert r.status_code == 404
    assert r.json()["detail"] == "document_not_found"


def test_process_pending_rejects_out_of_panel_physician(
    client: TestClient, monkeypatch
) -> None:
    """``/process`` honors whatever the panel gate decides.

    Stubs ``_verify_patient_in_panel`` to raise a 403 so this test
    pins the endpoint contract (forwards the panel-gate exception)
    rather than the gate's internal semantics — those are covered by
    ``evals/agent/test_panel_scope.py``.
    """
    from app import main as main_module
    from fastapi import HTTPException

    async def _deny(*args, **kwargs):
        raise HTTPException(status_code=403, detail="patient_out_of_panel")
    monkeypatch.setattr(main_module, "_verify_patient_in_panel", _deny)

    with client:
        r = client.post(
            "/v1/documents/copilot-anything/process"
            "?patient_id=some-other-patient&physician_user_id=" + PHYSICIAN
        )
    assert r.status_code == 403
    assert "out_of_panel" in r.json()["detail"]

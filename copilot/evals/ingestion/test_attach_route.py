"""End-to-end test of POST /v1/documents/attach with mocked downstream services."""
from __future__ import annotations

import json
from datetime import date
from unittest.mock import AsyncMock

import pytest
from fastapi.testclient import TestClient

from app import main as main_module
from app.ingestion.service import BboxOverlayItem, IngestionResult
from app.ingestion.schemas import (
    BoundingBox,
    LabPDFExtraction,
    LabResult,
    SourceCitation,
)


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
    # PHYSICIAN_PATIENT_PANEL must be JSON: physician_user_id → list[patient_fhir_id]
    monkeypatch.setenv(
        "PHYSICIAN_PATIENT_PANEL",
        json.dumps({"dr_who": ["patient-7"]}),
    )

    # Clear lru_cache so the monkeypatched env vars are picked up.
    from app.config import get_settings
    get_settings.cache_clear()

    return TestClient(main_module.app)


def _result_with_one_lab() -> IngestionResult:
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
    )


def test_attach_route_returns_doc_id_and_overlay(client: TestClient) -> None:
    with client:
        client.app.state.ingestion_service.attach_and_extract = AsyncMock(
            return_value=_result_with_one_lab()
        )
        r = client.post(
            "/v1/documents/attach",
            data={
                "patient_id": "patient-7",
                "doc_type": "lab_doc",
                "mime_type": "application/pdf",
                "physician_user_id": "dr_who",
            },
            files={"file": ("lipid.pdf", b"%PDF-1.4 fake", "application/pdf")},
        )
    assert r.status_code == 200, r.text
    body = r.json()
    assert body["doc_id"] == "doc-1"
    assert body["was_dedup_hit"] is False
    assert len(body["bbox_overlay"]) == 1
    assert body["bbox_overlay"][0]["field_or_chunk_id"] == "results[ldl_cholesterol].value"


def test_attach_route_falls_through_when_patient_outside_env_panel(
    client: TestClient, monkeypatch
) -> None:
    """2026-05-08 — env-panel is now advisory; when the patient isn't in
    the physician's listed UUIDs, the panel-gate falls through to the
    FHIR-derived check instead of hard-denying. This is the
    'physicians can't see the pending banner' fix.

    With the FHIR fallback (already relaxed) returning an empty
    ``Patient.generalPractitioner``, the request succeeds. The
    OpenEMR-side awk-injected demographics gate is the authoritative
    scope check.
    """
    # Stub the panel verification to a no-op so we can test the route
    # contract without standing up a full FHIR mock for the OAuth + Patient
    # fetch chain. The gate's behavior is exercised end-to-end in
    # evals/agent/test_panel_scope.py with respx.
    from app import main as main_module

    async def _noop(*args, **kwargs):
        return None
    monkeypatch.setattr(main_module, "_verify_patient_in_panel", _noop)

    with client:
        client.app.state.ingestion_service.attach_and_extract = AsyncMock(
            return_value=_result_with_one_lab()
        )
        r = client.post(
            "/v1/documents/attach",
            data={
                "patient_id": "patient-99",  # not in PHYSICIAN_PATIENT_PANEL[dr_who]
                "doc_type": "lab_doc",
                "mime_type": "application/pdf",
                "physician_user_id": "dr_who",
            },
            files={"file": ("x.pdf", b"%PDF-1.4 fake", "application/pdf")},
        )
    assert r.status_code == 200, r.text
    assert r.json()["doc_id"] == "doc-1"

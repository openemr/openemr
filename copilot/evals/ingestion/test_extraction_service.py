# evals/ingestion/test_extraction_service.py
"""Service-level orchestration: dedup → DocumentReference → VLM → derived writes."""
from __future__ import annotations

import os
import tempfile
from datetime import date
from unittest.mock import AsyncMock, MagicMock

import pytest

from app.ingestion.schemas import (
    BoundingBox,
    LabPDFExtraction,
    LabResult,
    SourceCitation,
)
from app.ingestion.service import IngestionService
from app.persistence.processed_documents import ProcessedDocumentStore


def _lipid_extraction(doc_id: str) -> LabPDFExtraction:
    return LabPDFExtraction(
        results=[
            LabResult(
                test_name="LDL", analyte_key="ldl_cholesterol", loinc_code=None,
                value=142.0, unit="mg/dL", reference_range="<100",
                collection_date=date(2026, 4, 30), abnormal_flag="H",
                source_citation=SourceCitation(
                    source_doc_id=f"DocumentReference/{doc_id}",
                    page=1,
                    bbox=BoundingBox(x=0.1, y=0.2, w=0.3, h=0.04),
                    raw_text="LDL 142", confidence=0.9, source_kind="document",
                    field_or_chunk_id="results[ldl_cholesterol].value",
                ),
            )
        ],
        document_date=date(2026, 4, 30),
    )


@pytest.fixture
async def store() -> ProcessedDocumentStore:
    fd, path = tempfile.mkstemp(suffix=".db")
    os.close(fd)
    s = ProcessedDocumentStore(path)
    await s.init()
    yield s
    os.unlink(path)


async def test_attach_and_extract_writes_doc_and_observations(store):
    fhir = MagicMock()
    fhir.create_document_reference = AsyncMock(
        return_value={"id": "doc-1", "resourceType": "DocumentReference"}
    )
    fhir.create_observation = AsyncMock(
        return_value={"id": "obs-1", "resourceType": "Observation"}
    )
    extractor = MagicMock()
    extractor.extract = AsyncMock(
        return_value=(
            _lipid_extraction("doc-1"),
            MagicMock(
                model_id="claude-opus-4-5",
                input_tokens=1234, output_tokens=567,
                cache_read_tokens=0, cache_creation_tokens=0,
            ),
        )
    )
    svc = IngestionService(fhir=fhir, vlm=extractor, store=store)

    result = await svc.attach_and_extract(
        patient_fhir_id="patient-7",
        patient_pseudonym="patient-7",
        doc_type="lab_doc",
        mime_type="application/pdf",
        file_bytes=b"%PDF-1.4 fake",
        physician_user_id="dr_who",
    )
    assert result.doc_id == "doc-1"
    assert result.was_dedup_hit is False
    assert len(result.bbox_overlay) == 1
    fhir.create_document_reference.assert_awaited_once()
    fhir.create_observation.assert_awaited_once()


async def test_attach_and_extract_dedupes_repeat_uploads(store):
    fhir = MagicMock()
    fhir.create_document_reference = AsyncMock(
        return_value={"id": "doc-1", "resourceType": "DocumentReference"}
    )
    fhir.create_observation = AsyncMock(
        return_value={"id": "obs-1", "resourceType": "Observation"}
    )
    extractor = MagicMock()
    extractor.extract = AsyncMock(
        return_value=(
            _lipid_extraction("doc-1"),
            MagicMock(
                model_id="claude-opus-4-5",
                input_tokens=1234, output_tokens=567,
                cache_read_tokens=0, cache_creation_tokens=0,
            ),
        )
    )
    svc = IngestionService(fhir=fhir, vlm=extractor, store=store)

    await svc.attach_and_extract(
        patient_fhir_id="patient-7",
        patient_pseudonym="patient-7",
        doc_type="lab_doc",
        mime_type="application/pdf",
        file_bytes=b"%PDF-1.4 fake",
        physician_user_id="dr_who",
    )
    second = await svc.attach_and_extract(
        patient_fhir_id="patient-7",
        patient_pseudonym="patient-7",
        doc_type="lab_doc",
        mime_type="application/pdf",
        file_bytes=b"%PDF-1.4 fake",
        physician_user_id="dr_who",
    )
    assert second.was_dedup_hit is True
    assert second.doc_id == "doc-1"
    # Did NOT re-call VLM or re-write
    extractor.extract.assert_awaited_once()
    fhir.create_document_reference.assert_awaited_once()

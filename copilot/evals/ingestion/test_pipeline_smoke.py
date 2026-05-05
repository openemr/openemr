# evals/ingestion/test_pipeline_smoke.py
"""End-to-end MVP smoke — fixture PDF through the service with VLM mocked."""
from __future__ import annotations

import json
import os
import tempfile
from pathlib import Path
from unittest.mock import AsyncMock, MagicMock

import pytest

from app.ingestion.schemas import LabPDFExtraction
from app.ingestion.service import IngestionService
from app.persistence.processed_documents import ProcessedDocumentStore


@pytest.fixture
async def store():
    fd, path = tempfile.mkstemp(suffix=".db")
    os.close(fd)
    s = ProcessedDocumentStore(path)
    await s.init()
    yield s
    os.unlink(path)


async def test_lipid_fixture_round_trips(store):
    file_bytes = Path("evals/fixtures/documents/lab-lipid-small.pdf").read_bytes()
    canned = json.loads(Path("evals/fixtures/vlm_responses/lipid.json").read_text())

    fhir = MagicMock()
    fhir.create_document_reference = AsyncMock(
        return_value={"id": "doc-1", "resourceType": "DocumentReference"}
    )
    fhir.create_observation = AsyncMock(
        return_value={"id": "obs-1", "resourceType": "Observation"}
    )

    # Substitute REPLACE in the canned JSON's source_doc_id with the doc_id
    # the FHIR mock will return.
    def _patch_doc_ids(payload, doc_id="doc-1"):
        if isinstance(payload, dict):
            for k, v in payload.items():
                if k == "source_doc_id" and isinstance(v, str):
                    payload[k] = v.replace("REPLACE", doc_id)
                else:
                    _patch_doc_ids(v, doc_id)
        elif isinstance(payload, list):
            for item in payload:
                _patch_doc_ids(item, doc_id)
        return payload

    canned = _patch_doc_ids(canned)
    extraction = LabPDFExtraction.model_validate(canned)
    vlm = MagicMock()
    vlm.extract = AsyncMock(
        return_value=(
            extraction,
            MagicMock(
                model_id="claude-opus-4-5",
                input_tokens=1, output_tokens=1,
                cache_read_tokens=0, cache_creation_tokens=0,
            ),
        )
    )
    svc = IngestionService(fhir=fhir, vlm=vlm, store=store)

    result = await svc.attach_and_extract(
        patient_fhir_id="patient-7",
        patient_pseudonym="patient-7",
        doc_type="lab_doc",
        mime_type="application/pdf",
        file_bytes=file_bytes,
        physician_user_id="dr_who",
    )
    assert result.doc_id == "doc-1"
    assert len(result.extraction.results) == 2
    # Both labs got Observations
    assert fhir.create_observation.await_count == 2
    # Bbox overlay populated
    assert all(item.bbox is not None for item in result.bbox_overlay)

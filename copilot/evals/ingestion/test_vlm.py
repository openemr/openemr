"""VLM adapter — image/PDF dispatch and tool_use unwrapping."""
from __future__ import annotations

import base64
import json
from unittest.mock import AsyncMock, MagicMock

import pytest

from app.ingestion.vlm import VlmExtractor


def _fake_anthropic_response(payload: dict) -> MagicMock:
    """Mimic anthropic.types.Message with a single tool_use block."""
    block = MagicMock()
    block.type = "tool_use"
    block.name = "submit_extraction"
    block.input = payload

    msg = MagicMock()
    msg.content = [block]
    msg.model = "claude-opus-4-5"
    msg.usage.input_tokens = 1234
    msg.usage.output_tokens = 567
    msg.usage.cache_creation_input_tokens = 0
    msg.usage.cache_read_input_tokens = 0
    return msg


@pytest.fixture
def lipid_payload() -> dict:
    return {
        "results": [
            {
                "test_name": "LDL Cholesterol",
                "analyte_key": "ldl_cholesterol",
                "loinc_code": None,
                "value": 142.0,
                "unit": "mg/dL",
                "reference_range": "<100",
                "collection_date": "2026-04-30",
                "abnormal_flag": "H",
                "source_citation": {
                    "source_doc_id": "DocumentReference/doc-1",
                    "page": 1,
                    "bbox": {"x": 0.1, "y": 0.2, "w": 0.3, "h": 0.04},
                    "raw_text": "LDL Cholesterol  142  mg/dL  <100  H",
                    "confidence": 0.92,
                    "source_kind": "document",
                    "field_or_chunk_id": "results[ldl_cholesterol].value",
                },
            }
        ],
        "document_date": "2026-04-30",
    }


async def test_vlm_extracts_lab_pdf(lipid_payload: dict) -> None:
    fake_client = MagicMock()
    fake_client.messages.create = AsyncMock(
        return_value=_fake_anthropic_response(lipid_payload)
    )
    extractor = VlmExtractor(client=fake_client, model_id="claude-opus-4-5")

    extraction, meta = await extractor.extract(
        file_bytes=b"%PDF-1.4 fake",
        mime_type="application/pdf",
        doc_type="lab_doc",
        doc_id="doc-1",
    )

    assert len(extraction.results) == 1
    assert extraction.results[0].analyte_key == "ldl_cholesterol"
    assert extraction.results[0].value == pytest.approx(142.0)
    assert meta.input_tokens == 1234
    assert meta.output_tokens == 567

    # Inspect the request: PDF should go via the `document` block.
    call_kwargs = fake_client.messages.create.await_args.kwargs
    user_msg = call_kwargs["messages"][0]
    blocks = user_msg["content"]
    doc_block = next(b for b in blocks if b["type"] == "document")
    assert doc_block["source"]["media_type"] == "application/pdf"
    assert doc_block["source"]["data"] == base64.b64encode(b"%PDF-1.4 fake").decode()


async def test_vlm_extracts_intake_png() -> None:
    intake_payload = {
        "demographics": {
            "age": 58,
            "gender": "F",
            "chief_concern": "fatigue",
            "source_citation": {
                "source_doc_id": "DocumentReference/doc-2",
                "page": 1,
                "bbox": {"x": 0.05, "y": 0.05, "w": 0.4, "h": 0.04},
                "raw_text": "Age: 58  Sex: F  Concern: fatigue",
                "confidence": 0.95,
                "source_kind": "document",
                "field_or_chunk_id": "demographics",
            },
        },
        "chief_concern": "fatigue",
        "current_medications": [],
        "allergies": [
            {
                "verbatim_substance": "shellfish?? maybe iodine",
                "coded_substance": None,
                "code": None,
                "code_system": None,
                "reaction": "itchy?",
                "severity": None,
                "ambiguity_note": "no code — ambiguous; surface to clinician",
                "source_citation": {
                    "source_doc_id": "DocumentReference/doc-2",
                    "page": 1,
                    "bbox": {"x": 0.1, "y": 0.5, "w": 0.5, "h": 0.04},
                    "raw_text": "shellfish?? maybe iodine — itchy?",
                    "confidence": 0.6,
                    "source_kind": "document",
                    "field_or_chunk_id": "allergies[0].substance",
                },
            }
        ],
        "family_history": [],
        "source_citation": {
            "source_doc_id": "DocumentReference/doc-2",
            "page": 1,
            "bbox": {"x": 0.0, "y": 0.0, "w": 1.0, "h": 1.0},
            "raw_text": "(intake form, page 1)",
            "confidence": 1.0,
            "source_kind": "document",
            "field_or_chunk_id": "form",
        },
    }
    fake_client = MagicMock()
    fake_client.messages.create = AsyncMock(
        return_value=_fake_anthropic_response(intake_payload)
    )
    extractor = VlmExtractor(client=fake_client, model_id="claude-opus-4-5")

    extraction, _ = await extractor.extract(
        file_bytes=b"\x89PNG\r\n\x1a\n",
        mime_type="image/png",
        doc_type="intake_form_doc",
        doc_id="doc-2",
    )
    assert len(extraction.allergies) == 1
    assert extraction.allergies[0].coded_substance is None
    assert extraction.allergies[0].ambiguity_note is not None

    # Image MIME → `image` block, not `document`.
    call_kwargs = fake_client.messages.create.await_args.kwargs
    user_msg = call_kwargs["messages"][0]
    blocks = user_msg["content"]
    img_block = next(b for b in blocks if b["type"] == "image")
    assert img_block["source"]["media_type"] == "image/png"

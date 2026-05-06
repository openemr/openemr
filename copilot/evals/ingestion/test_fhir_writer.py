"""Build derived FHIR resources from a typed extraction; smoke the write path."""
from __future__ import annotations

from datetime import date
from unittest.mock import AsyncMock, MagicMock


from app.ingestion.fhir_writer import (
    build_observation_from_lab,
    build_allergy_from_intake,
    write_extraction,
)
from app.ingestion.schemas import (
    Allergy,
    BoundingBox,
    LabPDFExtraction,
    LabResult,
    SourceCitation,
)


def _cite(field_id: str) -> SourceCitation:
    return SourceCitation(
        source_doc_id="DocumentReference/doc-1",
        page=1,
        bbox=BoundingBox(x=0.1, y=0.2, w=0.3, h=0.04),
        raw_text="x",
        confidence=0.9,
        source_kind="document",
        field_or_chunk_id=field_id,
    )


def test_build_observation_includes_derived_from_and_loinc() -> None:
    lab = LabResult(
        test_name="LDL Cholesterol",
        analyte_key="ldl_cholesterol",
        loinc_code="13457-7",
        value=142.0,
        unit="mg/dL",
        reference_range="<100",
        collection_date=date(2026, 4, 30),
        abnormal_flag="H",
        source_citation=_cite("results[ldl_cholesterol].value"),
    )
    body = build_observation_from_lab(
        lab, patient_fhir_id="patient-7", doc_id="doc-1"
    )
    assert body["resourceType"] == "Observation"
    assert body["status"] == "final"
    assert body["subject"]["reference"] == "Patient/patient-7"
    assert body["derivedFrom"][0]["reference"] == "DocumentReference/doc-1"
    assert body["valueQuantity"]["value"] == 142.0
    assert body["valueQuantity"]["unit"] == "mg/dL"
    assert any(
        c.get("system", "").startswith("http://loinc.org") for c in body["code"]["coding"]
    )
    assert body["interpretation"][0]["coding"][0]["code"] == "H"


def test_build_allergy_preserves_verbatim_when_uncoded() -> None:
    allergy = Allergy(
        verbatim_substance="shellfish?? maybe iodine",
        coded_substance=None,
        code=None,
        code_system=None,
        reaction="itchy?",
        severity=None,
        ambiguity_note="no code — ambiguous; surface to clinician",
        source_citation=_cite("allergies[0].substance"),
    )
    body = build_allergy_from_intake(
        allergy, patient_fhir_id="patient-7", doc_id="doc-1"
    )
    assert body["resourceType"] == "AllergyIntolerance"
    assert body["code"]["text"] == "shellfish?? maybe iodine"
    assert body["patient"]["reference"] == "Patient/patient-7"
    # No invented code system when ambiguous
    assert "coding" not in body["code"] or not body["code"].get("coding")
    # Ambiguity note carried into the resource so it's visible in OpenEMR
    assert "ambiguous" in body.get("note", [{}])[0].get("text", "").lower()
    assert body["extension"][0]["url"].endswith("derived-from-document")
    assert body["extension"][0]["valueReference"]["reference"] == "DocumentReference/doc-1"


async def test_write_extraction_posts_one_observation_per_lab() -> None:
    fhir = MagicMock()
    fhir.create_observation = AsyncMock(
        return_value={"id": "obs-1", "resourceType": "Observation"}
    )
    fhir.create_allergy_intolerance = AsyncMock()
    fhir.create_medication_statement = AsyncMock()

    extraction = LabPDFExtraction(
        results=[
            LabResult(
                test_name="LDL", analyte_key="ldl_cholesterol", loinc_code=None,
                value=142.0, unit="mg/dL", reference_range="<100",
                collection_date=date(2026, 4, 30), abnormal_flag="H",
                source_citation=_cite("results[ldl_cholesterol].value"),
            ),
            LabResult(
                test_name="HDL", analyte_key="hdl_cholesterol", loinc_code=None,
                value=38.0, unit="mg/dL", reference_range=">40",
                collection_date=date(2026, 4, 30), abnormal_flag="L",
                source_citation=_cite("results[hdl_cholesterol].value"),
            ),
        ],
        document_date=date(2026, 4, 30),
    )
    written = await write_extraction(
        extraction,
        fhir=fhir,
        patient_fhir_id="patient-7",
        doc_id="doc-1",
        physician_user_id="dr_who",
    )
    assert fhir.create_observation.await_count == 2
    assert fhir.create_allergy_intolerance.await_count == 0
    assert len(written.observation_ids) == 2

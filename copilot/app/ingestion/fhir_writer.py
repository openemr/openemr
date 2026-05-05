"""Build FHIR resources from typed extractions; write them via FhirClient.

Every derived resource carries a `derivedFrom` reference back to the
DocumentReference that produced it. Layer-2 verification
(`check_extracted_fact_has_source_doc`, post-MVP) will rely on this anchor.
"""
from __future__ import annotations

from dataclasses import dataclass, field
from typing import Any

from app.fhir.client import FhirClient
from app.ingestion.schemas import (
    Allergy,
    IntakeFormExtraction,
    LabPDFExtraction,
    LabResult,
    Medication,
)


@dataclass
class WrittenResources:
    document_id: str
    observation_ids: list[str] = field(default_factory=list)
    allergy_ids: list[str] = field(default_factory=list)
    medication_statement_ids: list[str] = field(default_factory=list)


_DERIVED_FROM_EXT = "https://copilot.local/fhir/StructureDefinition/derived-from-document"


def build_observation_from_lab(
    lab: LabResult, *, patient_fhir_id: str, doc_id: str
) -> dict[str, Any]:
    coding: list[dict[str, Any]] = []
    if lab.loinc_code:
        coding.append(
            {
                "system": "http://loinc.org",
                "code": lab.loinc_code,
                "display": lab.test_name,
            }
        )
    if lab.analyte_key:
        coding.append(
            {
                "system": "https://copilot.local/CodeSystem/analyte-key",
                "code": lab.analyte_key,
                "display": lab.test_name,
            }
        )
    if not coding:
        coding.append({"display": lab.test_name})

    body: dict[str, Any] = {
        "resourceType": "Observation",
        "status": "final",
        "category": [
            {
                "coding": [
                    {
                        "system": "http://terminology.hl7.org/CodeSystem/observation-category",
                        "code": "laboratory",
                    }
                ]
            }
        ],
        "code": {"coding": coding, "text": lab.test_name},
        "subject": {"reference": f"Patient/{patient_fhir_id}"},
        "derivedFrom": [{"reference": f"DocumentReference/{doc_id}"}],
    }
    if lab.collection_date:
        body["effectiveDateTime"] = lab.collection_date.isoformat()
    if lab.value is not None:
        body["valueQuantity"] = {"value": lab.value, "unit": lab.unit or ""}
    if lab.reference_range:
        body["referenceRange"] = [{"text": lab.reference_range}]
    if lab.abnormal_flag:
        body["interpretation"] = [
            {
                "coding": [
                    {
                        "system": "http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation",
                        "code": lab.abnormal_flag,
                    }
                ]
            }
        ]
    return body


def build_allergy_from_intake(
    allergy: Allergy, *, patient_fhir_id: str, doc_id: str
) -> dict[str, Any]:
    code_block: dict[str, Any] = {"text": allergy.verbatim_substance}
    if allergy.code and allergy.code_system:
        system_uri = (
            "http://snomed.info/sct"
            if allergy.code_system == "SNOMED"
            else "http://www.nlm.nih.gov/research/umls/rxnorm"
        )
        code_block["coding"] = [
            {
                "system": system_uri,
                "code": allergy.code,
                "display": allergy.coded_substance or allergy.verbatim_substance,
            }
        ]

    body: dict[str, Any] = {
        "resourceType": "AllergyIntolerance",
        "clinicalStatus": {
            "coding": [
                {
                    "system": "http://terminology.hl7.org/CodeSystem/allergyintolerance-clinical",
                    "code": "active",
                }
            ]
        },
        "patient": {"reference": f"Patient/{patient_fhir_id}"},
        "code": code_block,
        "extension": [
            {
                "url": _DERIVED_FROM_EXT,
                "valueReference": {"reference": f"DocumentReference/{doc_id}"},
            }
        ],
    }
    notes: list[str] = []
    if allergy.ambiguity_note:
        notes.append(allergy.ambiguity_note)
    if allergy.reaction:
        notes.append(f"reaction: {allergy.reaction}")
    if allergy.severity:
        notes.append(f"severity: {allergy.severity}")
    if notes:
        body["note"] = [{"text": " | ".join(notes)}]
    return body


def build_medication_statement_from_intake(
    med: Medication, *, patient_fhir_id: str, doc_id: str
) -> dict[str, Any]:
    parts = [med.name]
    if med.dose:
        parts.append(med.dose)
    if med.frequency:
        parts.append(med.frequency)
    return {
        "resourceType": "MedicationStatement",
        "status": "active",
        "subject": {"reference": f"Patient/{patient_fhir_id}"},
        "medicationCodeableConcept": {"text": " ".join(parts)},
        "extension": [
            {
                "url": _DERIVED_FROM_EXT,
                "valueReference": {"reference": f"DocumentReference/{doc_id}"},
            }
        ],
    }


async def write_extraction(
    extraction: LabPDFExtraction | IntakeFormExtraction,
    *,
    fhir: FhirClient,
    patient_fhir_id: str,
    doc_id: str,
    physician_user_id: str,
) -> WrittenResources:
    written = WrittenResources(document_id=doc_id)

    if isinstance(extraction, LabPDFExtraction):
        for lab in extraction.results:
            body = build_observation_from_lab(
                lab, patient_fhir_id=patient_fhir_id, doc_id=doc_id
            )
            r = await fhir.create_observation(
                body=body, physician_user_id=physician_user_id
            )
            written.observation_ids.append(r.get("id", ""))
        return written

    # IntakeFormExtraction
    for allergy in extraction.allergies:
        body = build_allergy_from_intake(
            allergy, patient_fhir_id=patient_fhir_id, doc_id=doc_id
        )
        r = await fhir.create_allergy_intolerance(
            body=body, physician_user_id=physician_user_id
        )
        written.allergy_ids.append(r.get("id", ""))
    for med in extraction.current_medications:
        body = build_medication_statement_from_intake(
            med, patient_fhir_id=patient_fhir_id, doc_id=doc_id
        )
        r = await fhir.create_medication_statement(
            body=body, physician_user_id=physician_user_id
        )
        written.medication_statement_ids.append(r.get("id", ""))
    return written

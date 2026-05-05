# scripts/generate_mvp_fixtures.py
"""Generate two deterministic synthetic fixtures for the MVP smoke test.

No PHI, fully reproducible (SEED=42). Written from scratch with reportlab so
the repo doesn't depend on LibreOffice or external rendering tools.
"""
from __future__ import annotations

import json
from pathlib import Path

from reportlab.lib.pagesizes import LETTER
from reportlab.pdfgen import canvas


SEED = 42  # not actually used — output is fully deterministic


def write_lab_pdf(path: Path) -> None:
    c = canvas.Canvas(str(path), pagesize=LETTER)
    c.setFont("Helvetica-Bold", 16)
    c.drawString(72, 720, "Synthetic Reference Lab — Lipid Panel")
    c.setFont("Helvetica", 10)
    c.drawString(72, 700, "Patient: SYNTHEA-MVP-001    Collected: 2026-04-30")
    c.setFont("Helvetica-Bold", 11)
    c.drawString(72, 660, "Test                       Value    Unit     Ref Range   Flag")
    c.setFont("Helvetica", 11)
    c.drawString(72, 640, "LDL Cholesterol            142      mg/dL    <100        H")
    c.drawString(72, 620, "HDL Cholesterol             38      mg/dL    >40         L")
    c.drawString(72, 600, "Total Cholesterol          228      mg/dL    <200        H")
    c.drawString(72, 580, "Triglycerides              210      mg/dL    <150        H")
    c.showPage()
    c.save()


def write_intake_pdf(path: Path) -> None:
    c = canvas.Canvas(str(path), pagesize=LETTER)
    c.setFont("Helvetica-Bold", 16)
    c.drawString(72, 720, "Synthetic Intake Form")
    c.setFont("Helvetica", 11)
    c.drawString(72, 690, "Patient ID: SYNTHEA-MVP-001")
    c.drawString(72, 670, "Age: 58    Sex: F")
    c.drawString(72, 640, "Chief Concern: fatigue, occasional chest tightness on exertion")
    c.drawString(72, 600, "Current Medications:")
    c.drawString(96, 580, "- Lisinopril 10mg daily")
    c.drawString(96, 560, "- Metformin 500mg BID")
    c.drawString(72, 520, "Allergies:")
    c.drawString(96, 500, "- shellfish?? maybe iodine — itchy?")
    c.drawString(72, 460, "Family History:")
    c.drawString(96, 440, "- Mother: type 2 diabetes")
    c.drawString(96, 420, "- Father: MI age 62")
    c.showPage()
    c.save()


_LIPID_VLM = {
    "results": [
        {
            "test_name": "LDL Cholesterol", "analyte_key": "ldl_cholesterol",
            "loinc_code": None, "value": 142.0, "unit": "mg/dL",
            "reference_range": "<100", "collection_date": "2026-04-30",
            "abnormal_flag": "H",
            "source_citation": {
                "source_doc_id": "DocumentReference/REPLACE", "page": 1,
                "bbox": {"x": 0.10, "y": 0.13, "w": 0.42, "h": 0.025},
                "raw_text": "LDL Cholesterol 142 mg/dL <100 H",
                "confidence": 0.93, "source_kind": "document",
                "field_or_chunk_id": "results[ldl_cholesterol].value",
            },
        },
        {
            "test_name": "HDL Cholesterol", "analyte_key": "hdl_cholesterol",
            "loinc_code": None, "value": 38.0, "unit": "mg/dL",
            "reference_range": ">40", "collection_date": "2026-04-30",
            "abnormal_flag": "L",
            "source_citation": {
                "source_doc_id": "DocumentReference/REPLACE", "page": 1,
                "bbox": {"x": 0.10, "y": 0.16, "w": 0.42, "h": 0.025},
                "raw_text": "HDL Cholesterol 38 mg/dL >40 L",
                "confidence": 0.93, "source_kind": "document",
                "field_or_chunk_id": "results[hdl_cholesterol].value",
            },
        },
    ],
    "document_date": "2026-04-30",
}

_INTAKE_VLM = {
    "demographics": {
        "age": 58, "gender": "F", "chief_concern": "fatigue",
        "source_citation": {
            "source_doc_id": "DocumentReference/REPLACE", "page": 1,
            "bbox": {"x": 0.10, "y": 0.10, "w": 0.30, "h": 0.025},
            "raw_text": "Age: 58 Sex: F", "confidence": 0.95,
            "source_kind": "document", "field_or_chunk_id": "demographics",
        },
    },
    "chief_concern": "fatigue, occasional chest tightness on exertion",
    "current_medications": [
        {
            "name": "Lisinopril", "dose": "10mg", "frequency": "daily",
            "source_citation": {
                "source_doc_id": "DocumentReference/REPLACE", "page": 1,
                "bbox": {"x": 0.13, "y": 0.21, "w": 0.30, "h": 0.025},
                "raw_text": "Lisinopril 10mg daily", "confidence": 0.92,
                "source_kind": "document", "field_or_chunk_id": "medications[0]",
            },
        }
    ],
    "allergies": [
        {
            "verbatim_substance": "shellfish?? maybe iodine",
            "coded_substance": None, "code": None, "code_system": None,
            "reaction": "itchy?", "severity": None,
            "ambiguity_note": "no code — ambiguous; surface to clinician",
            "source_citation": {
                "source_doc_id": "DocumentReference/REPLACE", "page": 1,
                "bbox": {"x": 0.13, "y": 0.30, "w": 0.40, "h": 0.025},
                "raw_text": "shellfish?? maybe iodine — itchy?",
                "confidence": 0.55, "source_kind": "document",
                "field_or_chunk_id": "allergies[0].substance",
            },
        }
    ],
    "family_history": [
        {
            "relation": "Mother", "condition": "type 2 diabetes",
            "source_citation": {
                "source_doc_id": "DocumentReference/REPLACE", "page": 1,
                "bbox": {"x": 0.13, "y": 0.40, "w": 0.40, "h": 0.025},
                "raw_text": "Mother: type 2 diabetes", "confidence": 0.93,
                "source_kind": "document", "field_or_chunk_id": "family_history[0]",
            },
        }
    ],
    "source_citation": {
        "source_doc_id": "DocumentReference/REPLACE", "page": 1,
        "bbox": {"x": 0.0, "y": 0.0, "w": 1.0, "h": 1.0},
        "raw_text": "(intake form, page 1)", "confidence": 1.0,
        "source_kind": "document", "field_or_chunk_id": "form",
    },
}


def main() -> None:
    docs = Path("evals/fixtures/documents")
    vlm = Path("evals/fixtures/vlm_responses")
    docs.mkdir(parents=True, exist_ok=True)
    vlm.mkdir(parents=True, exist_ok=True)
    write_lab_pdf(docs / "lab-lipid-small.pdf")
    write_intake_pdf(docs / "intake-small.pdf")
    (vlm / "lipid.json").write_text(json.dumps(_LIPID_VLM, indent=2))
    (vlm / "intake.json").write_text(json.dumps(_INTAKE_VLM, indent=2))
    print("OK: fixtures written under evals/fixtures/")


if __name__ == "__main__":
    main()

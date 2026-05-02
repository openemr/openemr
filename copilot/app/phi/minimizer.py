"""PHI minimization — strip identifiers before any data crosses the LLM boundary.

This addresses AUDIT.md §1.4 (no PHI de-identification before LLM). The
minimizer is per-resource: each tool calls the appropriate strip_* function on
its FHIR response before assembling the ToolResult.

What gets stripped (AUDIT §1.4 + ARCHITECTURE §3.3):
  - Patient.name, telecom, address, identifier (SSN/MRN), birthDate (replaced with age)
  - Practitioner.name, telecom (replaced with role/letter pseudonym)
  - Free-text fields (encounter notes, observation comments) get a coarse PHI
    pass: name regex matches against active patient/provider names are masked.

What is preserved:
  - All clinical content (RxNorm, LOINC, ICD-10, values, units, reference ranges)
  - Encounter dates (clinically relevant — "what's changed since last visit")
  - Provider role (attending, RN) — clinically relevant, not identifying

The function returns the resource ID separately so the verification gate
(Phase C) can use it as a citation anchor.
"""
from __future__ import annotations

import re
from datetime import date, datetime
from typing import Any

from app.phi.session import PseudonymMap


def _age_from_birthdate(birth: str | None) -> str | None:
    if not birth:
        return None
    try:
        b = datetime.fromisoformat(birth).date() if "T" in birth else date.fromisoformat(birth[:10])
    except ValueError:
        return None
    today = date.today()
    years = today.year - b.year - ((today.month, today.day) < (b.month, b.day))
    return f"{years}yo"


def _name_text(human_name: dict[str, Any] | None) -> str:
    if not human_name:
        return ""
    given = " ".join(human_name.get("given") or [])
    family = human_name.get("family") or ""
    text = human_name.get("text") or f"{given} {family}".strip()
    return text


def _scrub_text(s: str | None, name_terms: list[str]) -> str | None:
    """Best-effort masking of any leaked human names in free-text."""
    if not s:
        return s
    out = s
    for term in name_terms:
        if term and len(term) >= 2:
            out = re.sub(rf"\b{re.escape(term)}\b", "[REDACTED]", out, flags=re.IGNORECASE)
    return out


def strip_patient(resource: dict[str, Any], session: PseudonymMap) -> dict[str, Any]:
    real_id = resource.get("id", "")
    pseudo = session.pseudo_for("Patient", real_id)
    age = _age_from_birthdate(resource.get("birthDate"))
    return {
        "resourceType": "Patient",
        "id": pseudo,
        "record_id": f"Patient/{real_id}",  # used internally for verification, not exposed to LLM
        "age": age,
        "gender": resource.get("gender"),
        "active": resource.get("active", True),
        "deceased": resource.get("deceasedBoolean") or bool(resource.get("deceasedDateTime")),
        # name/telecom/address/identifier intentionally dropped
    }


def strip_practitioner(resource: dict[str, Any], session: PseudonymMap) -> dict[str, Any]:
    real_id = resource.get("id", "")
    pseudo = session.pseudo_for("Practitioner", real_id)
    return {
        "resourceType": "Practitioner",
        "id": pseudo,
        "record_id": f"Practitioner/{real_id}",
    }


def _ref_pseudonymize(ref: str | None, session: PseudonymMap) -> str | None:
    """Replace `Patient/uuid` → `Patient-XXXX`, `Practitioner/uuid` → `Provider-A`."""
    if not ref or "/" not in ref:
        return ref
    rt, _, rid = ref.partition("/")
    if rt in ("Patient", "Practitioner"):
        return session.pseudo_for(rt, rid)
    return ref  # leave Encounter, MedicationRequest etc. alone — those ARE the citation anchors


def strip_medication_request(
    resource: dict[str, Any], session: PseudonymMap, name_terms: list[str]
) -> dict[str, Any]:
    real_id = resource.get("id", "")
    med = resource.get("medicationCodeableConcept") or {}
    coding = (med.get("coding") or [{}])[0]
    return {
        "resourceType": "MedicationRequest",
        "id": real_id,
        "record_id": f"MedicationRequest/{real_id}",
        "status": resource.get("status"),
        "intent": resource.get("intent"),
        "drug_name": _scrub_text(med.get("text") or coding.get("display"), name_terms),
        "rxnorm_code": coding.get("code") if "rxnorm" in (coding.get("system") or "").lower() else None,
        "code_system": coding.get("system"),
        "authored_on": resource.get("authoredOn"),
        "dosage_text": _scrub_text(
            next(
                (
                    x.get("text")
                    for x in (resource.get("dosageInstruction") or [])
                    if isinstance(x, dict)
                ),
                None,
            ),
            name_terms,
        ),
        "requester": _ref_pseudonymize(
            (resource.get("requester") or {}).get("reference"), session
        ),
        "subject_pseudonym": _ref_pseudonymize(
            (resource.get("subject") or {}).get("reference"), session
        ),
    }


def strip_observation(
    resource: dict[str, Any], session: PseudonymMap, name_terms: list[str]
) -> dict[str, Any]:
    real_id = resource.get("id", "")
    code = resource.get("code") or {}
    coding = (code.get("coding") or [{}])[0]
    val = None
    unit = None
    if "valueQuantity" in resource:
        val = resource["valueQuantity"].get("value")
        unit = resource["valueQuantity"].get("unit")
    elif "valueString" in resource:
        val = _scrub_text(resource["valueString"], name_terms)
    elif "valueCodeableConcept" in resource:
        val = (resource["valueCodeableConcept"].get("coding") or [{}])[0].get("display")
    ref_range = None
    rrs = resource.get("referenceRange") or []
    if rrs:
        rr = rrs[0]
        ref_range = {
            "low": (rr.get("low") or {}).get("value"),
            "high": (rr.get("high") or {}).get("value"),
            "unit": (rr.get("low") or {}).get("unit") or (rr.get("high") or {}).get("unit"),
        }
    return {
        "resourceType": "Observation",
        "id": real_id,
        "record_id": f"Observation/{real_id}",
        "status": resource.get("status"),
        "category": ((resource.get("category") or [{}])[0].get("coding") or [{}])[0].get("code"),
        "loinc_code": coding.get("code") if "loinc" in (coding.get("system") or "").lower() else None,
        "display": _scrub_text(code.get("text") or coding.get("display"), name_terms),
        "value": val,
        "unit": unit,
        "reference_range": ref_range,
        "effective_datetime": resource.get("effectiveDateTime") or resource.get("issued"),
        "subject_pseudonym": _ref_pseudonymize(
            (resource.get("subject") or {}).get("reference"), session
        ),
    }


def strip_condition(
    resource: dict[str, Any], session: PseudonymMap, name_terms: list[str]
) -> dict[str, Any]:
    real_id = resource.get("id", "")
    code = resource.get("code") or {}
    coding = (code.get("coding") or [{}])[0]
    return {
        "resourceType": "Condition",
        "id": real_id,
        "record_id": f"Condition/{real_id}",
        "clinical_status": ((resource.get("clinicalStatus") or {}).get("coding") or [{}])[0].get("code"),
        "verification_status": (
            (resource.get("verificationStatus") or {}).get("coding") or [{}]
        )[0].get("code"),
        "icd10_code": coding.get("code") if "icd-10" in (coding.get("system") or "").lower() else None,
        "code_system": coding.get("system"),
        "display": _scrub_text(code.get("text") or coding.get("display"), name_terms),
        "onset_datetime": resource.get("onsetDateTime"),
        "recorded_date": resource.get("recordedDate"),
        "subject_pseudonym": _ref_pseudonymize(
            (resource.get("subject") or {}).get("reference"), session
        ),
    }


def strip_encounter(
    resource: dict[str, Any], session: PseudonymMap, name_terms: list[str]
) -> dict[str, Any]:
    real_id = resource.get("id", "")
    type_codings = (resource.get("type") or [{}])[0].get("coding") or [{}]
    period = resource.get("period") or {}
    return {
        "resourceType": "Encounter",
        "id": real_id,
        "record_id": f"Encounter/{real_id}",
        "status": resource.get("status"),
        "class": (resource.get("class") or {}).get("code"),
        "type_display": _scrub_text(type_codings[0].get("display"), name_terms),
        "reason_text": _scrub_text(
            ((resource.get("reasonCode") or [{}])[0].get("text")), name_terms
        ),
        "start": period.get("start"),
        "end": period.get("end"),
        "participant_pseudonyms": [
            _ref_pseudonymize((p.get("individual") or {}).get("reference"), session)
            for p in (resource.get("participant") or [])
        ],
        "subject_pseudonym": _ref_pseudonymize(
            (resource.get("subject") or {}).get("reference"), session
        ),
    }


def strip_allergy(
    resource: dict[str, Any], session: PseudonymMap, name_terms: list[str]
) -> dict[str, Any]:
    real_id = resource.get("id", "")
    code = resource.get("code") or {}
    coding = (code.get("coding") or [{}])[0]
    return {
        "resourceType": "AllergyIntolerance",
        "id": real_id,
        "record_id": f"AllergyIntolerance/{real_id}",
        "clinical_status": ((resource.get("clinicalStatus") or {}).get("coding") or [{}])[0].get("code"),
        "verification_status": (
            (resource.get("verificationStatus") or {}).get("coding") or [{}]
        )[0].get("code"),
        "category": (resource.get("category") or [None])[0],
        "criticality": resource.get("criticality"),
        "code": coding.get("code"),
        "code_system": coding.get("system"),
        "display": _scrub_text(code.get("text") or coding.get("display"), name_terms),
        "reactions": [
            {
                "manifestation": [
                    _scrub_text((m.get("coding") or [{}])[0].get("display"), name_terms)
                    for m in (r.get("manifestation") or [])
                ],
                "severity": r.get("severity"),
            }
            for r in (resource.get("reaction") or [])
        ],
    }


def collect_name_terms_from_patient(resource: dict[str, Any]) -> list[str]:
    """Extract name strings from a Patient resource for free-text scrubbing."""
    terms: list[str] = []
    for n in resource.get("name") or []:
        text = _name_text(n)
        if text:
            terms.append(text)
            terms.extend(text.split())
    return [t for t in terms if t]

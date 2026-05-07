"""Unit tests for ``app.agent.evidence.extract_evidence_records``."""
from __future__ import annotations

from app.agent.evidence import extract_evidence_records
from app.agent.schemas import Claim


def _claim(record_id: str) -> Claim:
    return Claim(text=record_id, record_id=record_id)


def test_only_cited_record_ids_are_returned():
    """Tool results may carry many records; we only ship those the LLM cited."""
    tool_results = [
        {
            "tool": "get_recent_labs",
            "data": [
                {"record_id": "Observation/o-1", "value": 142, "unit": "mg/dL"},
                {"record_id": "Observation/o-2", "value": 5.6, "unit": "%"},
            ],
        }
    ]
    claims = [_claim("Observation/o-1")]

    out = extract_evidence_records(tool_results, claims)

    assert set(out.keys()) == {"Observation/o-1"}
    assert out["Observation/o-1"].kind == "observation"
    assert out["Observation/o-1"].data["value"] == 142


def test_kind_routing_covers_every_supported_prefix():
    """Each FHIR family + Guideline + QuestionnaireResponse routes to the
    expected EvidenceKind literal."""
    cases = [
        ("DocumentReference/d-1", "document"),
        ("Observation/o-1", "observation"),
        ("MedicationRequest/m-1", "medication"),
        ("MedicationStatement/m-2", "medication"),
        ("AllergyIntolerance/a-1", "allergy"),
        ("Condition/c-1", "condition"),
        ("Encounter/e-1", "encounter"),
        ("Patient/p-1", "patient"),
        ("Guideline/uspstf-statin-2022#sec-2.1", "guideline"),
        ("QuestionnaireResponse/qr-1#linkId=q-foo", "questionnaire"),
    ]
    tool_results = [
        {"tool": "fake", "data": [{"record_id": rid} for rid, _ in cases]}
    ]
    claims = [_claim(rid) for rid, _ in cases]

    out = extract_evidence_records(tool_results, claims)

    for rid, expected_kind in cases:
        assert out[rid].kind == expected_kind, rid


def test_unknown_prefix_falls_back_to_unknown_kind():
    """Defensive: a tool that emits a never-before-seen prefix is still
    surfaced (as kind='unknown') rather than dropped silently."""
    tool_results = [{"tool": "fake", "data": [{"record_id": "Foo/bar"}]}]
    out = extract_evidence_records(tool_results, [_claim("Foo/bar")])

    assert out["Foo/bar"].kind == "unknown"


def test_data_dict_instead_of_list_is_handled():
    """Some tools (notably summary tools) emit `data` as a single dict
    rather than a list. We accept both shapes."""
    tool_results = [
        {
            "tool": "get_patient_summary",
            "data": {"record_id": "Patient/p-1", "age": 58, "gender": "F"},
        }
    ]
    out = extract_evidence_records(tool_results, [_claim("Patient/p-1")])

    assert out["Patient/p-1"].kind == "patient"
    assert out["Patient/p-1"].data["age"] == 58


def test_no_claims_returns_empty_map():
    """When the LLM cited nothing, we ship nothing — saves bytes on a wire
    that would otherwise carry the full tool_results bundle for the UI."""
    tool_results = [{"tool": "get_recent_labs", "data": [{"record_id": "Observation/o-1"}]}]

    assert extract_evidence_records(tool_results, []) == {}

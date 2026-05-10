# app/tools/document_tools.py
"""Agent-callable tools for working with uploaded clinical documents.

- `attach_and_extract` (PRD §1): runs ingestion mid-turn for a doc on disk.
- `get_recent_uploads`: reads the most recent processed_documents rows so the
  agent can answer questions about a doc the physician just dropped in the
  iframe (without having to call OpenEMR/FHIR for the same data — those reads
  fail when the deployed Co-Pilot can't reach OpenEMR, and the extracted
  facts live in Co-Pilot's own SQLite anyway).

Both tools produce ToolResults whose `data` items each carry their own
`record_id` field. This is what `app/verification/rules.py::_record_belongs_to_active_patient`
walks looking for a match — without it, Layer-2 verification rejects any
claim whose record_id was emitted in `record_ids` but not duplicated as a
key on a data item. `subject_pseudonym` is set to the session's pseudonym so
the cross-patient-leakage check passes for facts derived from this session's
upload.
"""
from __future__ import annotations

from pathlib import Path
from typing import Any

from app.ingestion.schemas import (
    IntakeFormExtraction,
    LabPDFExtraction,
    encode_record_id_for_vlm,
    field_id_for_lab_result,
)
from app.ingestion.service import IngestionService
from app.persistence.processed_documents import ProcessedDocumentStore
from app.phi.session import PseudonymMap
from app.tools._base import ToolResult


SCHEMA: dict[str, Any] = {
    "name": "attach_and_extract",
    "description": (
        "Ingest a previously uploaded clinical document for the active patient. "
        "Returns the structured extraction. Use this only when the user asks "
        "about a document that is sitting on disk and not yet extracted."
    ),
    "input_schema": {
        "type": "object",
        "properties": {
            "doc_type": {"type": "string", "enum": ["lab_doc", "intake_form_doc"]},
            "mime_type": {
                "type": "string",
                "enum": ["application/pdf", "image/png", "image/jpeg"],
            },
            "file_path": {"type": "string"},
        },
        "required": ["doc_type", "mime_type", "file_path"],
    },
}


def _flatten_extraction_to_data_items(
    extraction: LabPDFExtraction | IntakeFormExtraction,
    *,
    doc_id: str,
    doc_type: str,
    subject_pseudonym: str,
    extracted_at_iso: str | None = None,
) -> tuple[list[dict[str, Any]], list[str]]:
    """Build per-fact data items + their record_ids for verify() to walk.

    Returns (data_items, per_fact_record_ids). Both lists are kept in
    lockstep — for every record_id in the second list there is exactly one
    data item with a matching `record_id` key, plus the parent
    DocumentReference item at index 0. Caller appends the parent
    record_id (`DocumentReference/{doc_id}`) themselves.
    """
    parent_item: dict[str, Any] = {
        "record_id": f"DocumentReference/{doc_id}",
        "subject_pseudonym": subject_pseudonym,
        "resourceType": "DocumentReference",
        "doc_type": doc_type,
    }
    if extracted_at_iso:
        parent_item["extracted_at"] = extracted_at_iso

    data_items: list[dict[str, Any]] = [parent_item]
    per_fact_record_ids: list[str] = []

    if isinstance(extraction, LabPDFExtraction):
        for idx, lab in enumerate(extraction.results):
            if lab.source_citation.bbox is None or lab.source_citation.page is None:
                continue
            rid = encode_record_id_for_vlm(
                doc_id=doc_id,
                page=lab.source_citation.page,
                bbox=lab.source_citation.bbox,
                field_or_chunk_id=field_id_for_lab_result(lab, idx),
                raw_text=lab.source_citation.raw_text,
            )
            per_fact_record_ids.append(rid)
            data_items.append(
                {
                    "record_id": rid,
                    "subject_pseudonym": subject_pseudonym,
                    "resourceType": "Observation",
                    "test_name": lab.test_name,
                    "analyte_key": lab.analyte_key,
                    "loinc_code": lab.loinc_code,
                    "value": lab.value,
                    "unit": lab.unit,
                    "reference_range": lab.reference_range,
                    "abnormal_flag": lab.abnormal_flag,
                    "collection_date": (
                        lab.collection_date.isoformat()
                        if lab.collection_date else None
                    ),
                }
            )
        return data_items, per_fact_record_ids

    # IntakeFormExtraction — flatten allergies / medications / family history.
    if isinstance(extraction, IntakeFormExtraction):
        # Demographics + chief concern as a Patient-shaped item, no per-field
        # record_id encoding (no useful bbox to anchor a derived FHIR id).
        data_items.append(
            {
                "record_id": f"DocumentReference/{doc_id}#field=demographics",
                "subject_pseudonym": subject_pseudonym,
                "resourceType": "Patient",
                "id": subject_pseudonym,
                "age": extraction.demographics.age,
                "gender": extraction.demographics.gender,
                "chief_concern": extraction.chief_concern,
            }
        )
        per_fact_record_ids.append(f"DocumentReference/{doc_id}#field=demographics")

        for i, med in enumerate(extraction.current_medications):
            if med.source_citation.bbox is None or med.source_citation.page is None:
                continue
            rid = encode_record_id_for_vlm(
                doc_id=doc_id,
                page=med.source_citation.page,
                bbox=med.source_citation.bbox,
                field_or_chunk_id=f"medications[{i}]",
                raw_text=med.source_citation.raw_text,
            )
            per_fact_record_ids.append(rid)
            data_items.append(
                {
                    "record_id": rid,
                    "subject_pseudonym": subject_pseudonym,
                    "resourceType": "MedicationStatement",
                    "name": med.name,
                    "dose": med.dose,
                    "frequency": med.frequency,
                }
            )

        for i, allergy in enumerate(extraction.allergies):
            if allergy.source_citation.bbox is None or allergy.source_citation.page is None:
                continue
            rid = encode_record_id_for_vlm(
                doc_id=doc_id,
                page=allergy.source_citation.page,
                bbox=allergy.source_citation.bbox,
                field_or_chunk_id=f"allergies[{i}].substance",
                raw_text=allergy.source_citation.raw_text,
            )
            per_fact_record_ids.append(rid)
            data_items.append(
                {
                    "record_id": rid,
                    "subject_pseudonym": subject_pseudonym,
                    "resourceType": "AllergyIntolerance",
                    "verbatim_substance": allergy.verbatim_substance,
                    "coded_substance": allergy.coded_substance,
                    "reaction": allergy.reaction,
                    "severity": allergy.severity,
                    "ambiguity_note": allergy.ambiguity_note,
                }
            )

        for i, fh in enumerate(extraction.family_history):
            if fh.source_citation.bbox is None or fh.source_citation.page is None:
                continue
            rid = encode_record_id_for_vlm(
                doc_id=doc_id,
                page=fh.source_citation.page,
                bbox=fh.source_citation.bbox,
                field_or_chunk_id=f"family_history[{i}]",
                raw_text=fh.source_citation.raw_text,
            )
            per_fact_record_ids.append(rid)
            data_items.append(
                {
                    "record_id": rid,
                    "subject_pseudonym": subject_pseudonym,
                    "resourceType": "FamilyMemberHistory",
                    "relation": fh.relation,
                    "condition": fh.condition,
                }
            )

    return data_items, per_fact_record_ids


async def run_attach_and_extract(
    *,
    ingestion_service: IngestionService,
    session: PseudonymMap,
    args: dict[str, Any],
) -> ToolResult:
    file_path = args["file_path"]
    inline = args.get("_inline_bytes")  # test seam — never set in production
    file_bytes = inline if inline is not None else Path(file_path).read_bytes()

    result = await ingestion_service.attach_and_extract(
        patient_fhir_id=getattr(session, "patient_fhir_id", session.patient_pseudonym()),
        patient_pseudonym=session.patient_pseudonym(),
        doc_type=args["doc_type"],
        mime_type=args["mime_type"],
        file_bytes=file_bytes,
        physician_user_id=session.physician_user_id,
    )

    data_items, per_fact_record_ids = _flatten_extraction_to_data_items(
        result.extraction,
        doc_id=result.doc_id,
        doc_type=args["doc_type"],
        subject_pseudonym=session.patient_pseudonym(),
    )
    record_ids: list[str] = [f"DocumentReference/{result.doc_id}"] + per_fact_record_ids

    # W2 KR8: surface the per-call USD cost on the parent DocumentReference
    # data item so the intake_extractor worker (and any post-hoc analysis)
    # can attribute cost to this turn. None on dedup hits or unpriced models.
    if result.cost_estimate_usd is not None and data_items:
        data_items[0]["cost_estimate_usd"] = result.cost_estimate_usd

    return ToolResult(
        name="attach_and_extract",
        record_type="DocumentReference",
        data=data_items,
        record_ids=record_ids,
    )


GET_RECENT_UPLOADS_SCHEMA: dict[str, Any] = {
    "name": "get_recent_uploads",
    "description": (
        "Return the most recent clinical documents uploaded for the active "
        "patient with their structured extractions. Use this when the "
        "physician asks about a lab or intake form they just dropped in the "
        "iframe (e.g. 'what was the LDL on the lab I just uploaded?'). The "
        "extracted facts include lab values, allergies, medications, and "
        "intake demographics — all anchored to the source DocumentReference "
        "so claims you make can cite them. "
        "When the user asks 'what's new', 'what changed', or 'what's "
        "different since the last visit', set ``confirmed_only=true`` so "
        "only physician-accepted intakes (not pending or rejected) drive "
        "the answer; cross-reference against prior FHIR Observations / "
        "Conditions / Medications / Allergies to surface what's different."
    ),
    "input_schema": {
        "type": "object",
        "properties": {
            "limit": {
                "type": "integer",
                "minimum": 1,
                "maximum": 10,
                "default": 3,
            },
            "confirmed_only": {
                "type": "boolean",
                "description": (
                    "If true, return ONLY documents the physician has "
                    "explicitly confirmed via the pending-intake banner. "
                    "Default false (returns all extracted documents)."
                ),
                "default": False,
            },
            "since_days": {
                "type": "integer",
                "minimum": 1,
                "maximum": 365,
                "description": (
                    "Recency window in days. Only used when "
                    "``confirmed_only=true`` (filters by confirmation date)."
                ),
            },
        },
    },
}


async def run_get_recent_uploads(
    *,
    store: ProcessedDocumentStore,
    session: PseudonymMap,
    args: dict[str, Any],
) -> ToolResult:
    limit = int(args.get("limit") or 3)
    confirmed_only = bool(args.get("confirmed_only") or False)
    # The HTTP attach route stores rows keyed by raw FHIR uuid (no session
    # context at upload time). The session's `patient_pseudonym()` is a
    # randomized "Patient-XXXX" label used for trace minimization, NOT the
    # storage key. Use `active_patient_id` (the real FHIR uuid this session
    # is scoped to) so the lookup matches what the route stored.
    pending_unconfirmed: list[Any] = []
    if confirmed_only:
        from datetime import datetime, timedelta, timezone
        since_days = args.get("since_days")
        since = (
            datetime.now(timezone.utc) - timedelta(days=int(since_days))
            if since_days is not None
            else None
        )
        docs = await store.list_confirmed_recent(
            patient_pseudonym=session.active_patient_id, since=since
        )
        # Mirror the limit semantics — list_confirmed_recent returns
        # newest-first.
        docs = docs[:limit]
        # Count extracted-but-unconfirmed forms so the agent can nudge
        # the physician without leaking the form contents. Only the
        # count is exposed (see ``pending_review_notice`` below) — the
        # confirmed-only contract is preserved.
        recent_all = await store.list_recent_for_patient(
            patient_pseudonym=session.active_patient_id, limit=20
        )
        pending_unconfirmed = [
            d for d in recent_all
            if d.confirmed_at is None
            and d.rejected_at is None
            and not (
                isinstance(d.extracted_facts, dict)
                and d.extracted_facts.get("_pending")
            )
        ]
    else:
        docs = await store.list_recent_for_patient(
            patient_pseudonym=session.active_patient_id, limit=limit
        )
    data_items: list[dict[str, Any]] = []
    record_ids: list[str] = []
    subject_pseudonym = session.patient_pseudonym()

    for d in docs:
        # Reconstruct the typed extraction so we can flatten it the same way
        # attach_and_extract does. Malformed rows are skipped quietly so a
        # single bad row doesn't kill the tool.
        try:
            if d.doc_type == "lab_doc":
                extraction = LabPDFExtraction.model_validate(d.extracted_facts)
            else:
                extraction = IntakeFormExtraction.model_validate(d.extracted_facts)
        except Exception:  # noqa: BLE001
            continue

        items, per_fact_rids = _flatten_extraction_to_data_items(
            extraction,
            doc_id=d.canonical_doc_id,
            doc_type=d.doc_type,
            subject_pseudonym=subject_pseudonym,
            extracted_at_iso=d.extracted_at.isoformat(),
        )
        data_items.extend(items)
        record_ids.append(f"DocumentReference/{d.canonical_doc_id}")
        record_ids.extend(per_fact_rids)

    # Pending-review nudge (W2 Plan B — confirm-first contract). When the
    # caller asked for confirmed-only and there are extracted forms still
    # awaiting confirmation, surface the COUNT (no PHI) so the agent can
    # tell the physician "1 form awaiting your review" without leaking
    # its contents. The notice has no record_id — it MUST NOT be cited.
    if pending_unconfirmed:
        data_items.append({
            "type": "pending_review_notice",
            "pending_count": len(pending_unconfirmed),
            "hint": (
                "There are intake forms uploaded for this patient that "
                "have not yet been confirmed by the physician. Their "
                "contents are not included in this tool's results. To "
                "include them, click the form in the pending-review "
                "banner and press 'Confirm & save to chart'."
            ),
        })

    return ToolResult(
        name="get_recent_uploads",
        record_type="DocumentReference",
        data=data_items,
        record_ids=record_ids,
    )

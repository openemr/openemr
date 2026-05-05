"""Strict Pydantic schemas for Week 2 document ingestion.

The shapes here are the data contract for both branches of the
`intake_extractor` worker — the VLM path (Claude vision over PDFs and images)
and the structured-pass-through path (LForms `QuestionnaireResponse`).

Every extracted fact carries a `SourceCitation`. The citation includes a
`field_or_chunk_id` slot so VLM-extracted facts satisfy the PRD §5 minimum
citation shape `{source_type, source_id, page_or_section, field_or_chunk_id,
quote_or_value}` without leaning on the bbox to fill the role.

For the `record_id` that downstream `verify()` checks against, see the
encoding rules in `W2_ARCHITECTURE.md` §3.

This module also resolves four real-world gaps surfaced by the example
fixtures in `Desktop/Week2/example-documents/`:

- **Gap A (image MIME types).** Two of four intake forms and one of four
  labs in the example set are PNG photographs of paper forms. `DocType` is
  `lab_doc` / `intake_form_doc` (format-agnostic) and `MimeType` carries
  `application/pdf` | `image/png` | `image/jpeg`.
- **Gap B (ambiguous allergies).** `Allergy.verbatim_substance` preserves
  the exact substring (e.g. `"shellfish?? maybe iodine"`) while
  `coded_substance` + `code_system` carry the disambiguated coded form when
  the VLM can resolve it. `ambiguity_note` is the explicit "surface to
  clinician" channel.
- **Gap G (analyte_key for stable lab citations).** `LabResult.analyte_key`
  is a normalized string key produced by the small `ANALYTE_NORMALIZER`
  table below. The VLM emits raw `test_name` (which may be garbled, e.g.
  `"CO&sub2;"`); the schema also stores the normalized key, which the
  citation builder uses for `field_or_chunk_id` so the record_id is stable
  across re-extractions and resilient to OCR noise.
"""
from __future__ import annotations

from datetime import date
from typing import Literal

from pydantic import BaseModel, ConfigDict, Field, model_validator


DocType = Literal["lab_doc", "intake_form_doc"]
MimeType = Literal["application/pdf", "image/png", "image/jpeg"]


class AttachDocumentRequest(BaseModel):
    """Body of `POST /v1/documents/attach`.

    `doc_type` describes the *clinical role* (lab vs intake form), independent
    of file format. `mime_type` is the file format. The two together are
    enough to dispatch to the right VLM prompt + the right Pydantic
    extraction schema, while remaining open to future formats (e.g. TIFF
    fax scans) without renaming an enum.
    """

    model_config = ConfigDict(extra="forbid")

    doc_type: DocType
    mime_type: MimeType


class BoundingBox(BaseModel):
    model_config = ConfigDict(extra="forbid")

    x: float = Field(..., ge=0.0, le=1.0)
    y: float = Field(..., ge=0.0, le=1.0)
    w: float = Field(..., ge=0.0, le=1.0)
    h: float = Field(..., ge=0.0, le=1.0)


SourceKind = Literal["document", "questionnaire_response"]


class SourceCitation(BaseModel):
    """Per-field provenance for an extracted clinical fact.

    Maps onto PRD §5 citation shape:
      source_type        ← `source_kind`
      source_id          ← `source_doc_id`
      page_or_section    ← `page` (None for QuestionnaireResponse)
      field_or_chunk_id  ← `field_or_chunk_id` (this slot)
      quote_or_value     ← `raw_text`
    """

    model_config = ConfigDict(extra="forbid")

    source_doc_id: str = Field(
        ...,
        description=(
            "FHIR record reference: 'DocumentReference/{id}' for VLM, "
            "'QuestionnaireResponse/{id}' for structured pass-through, "
            "'Guideline/{chunk_id}' for evidence citations."
        ),
    )
    page: int | None = Field(
        default=None,
        ge=1,
        description="1-indexed page number; None for QuestionnaireResponse and Guideline.",
    )
    bbox: BoundingBox | None = Field(
        default=None,
        description=(
            "Pixel-region anchor on the document page. Required for VLM facts "
            "with confidence >= 0.5; None for structured pass-through."
        ),
    )
    raw_text: str = Field(
        ...,
        min_length=1,
        description="Exact substring lifted from the VLM output or LForms answer.",
    )
    confidence: float = Field(
        ...,
        ge=0.0,
        le=1.0,
        description=(
            "1.0 for structured pass-through (LForms answer is verbatim); "
            "[0,1] for VLM-reported confidence."
        ),
    )
    source_kind: SourceKind = Field(...)
    field_or_chunk_id: str = Field(
        ...,
        min_length=1,
        description=(
            "Stable per-field identifier. For VLM lab facts, "
            "'results[<analyte_key>].value' when the analyte normalizes "
            "(e.g. 'results[ldl_cholesterol].value'); 'results[<index>].value' "
            "when it doesn't. For QuestionnaireResponse, the LForms linkId. "
            "For Guideline citations, the chunk_id. Required by PRD §5 "
            "'minimum citation shape'."
        ),
    )

    @model_validator(mode="after")
    def _bbox_required_for_high_confidence_documents(self) -> "SourceCitation":
        if (
            self.source_kind == "document"
            and self.bbox is None
            and self.confidence >= 0.5
        ):
            raise ValueError(
                "VLM-extracted facts with confidence >= 0.5 must include a bbox; "
                "low-confidence VLM facts may set bbox=None."
            )
        return self


# ----- Gap G: analyte normalization for lab results --------------------------

# Maps lowercased, whitespace-normalized lab-report `test_name` strings to
# stable internal keys. Hand-curated for the panels we expect in the demo
# corpus (lipid, CBC, CMP, HbA1c). The dict is intentionally small and
# explicit — when the VLM extracts an analyte not in this table,
# `normalize_analyte_name` returns None and the citation falls back to a
# positional `field_or_chunk_id`.
#
# Synonyms include the garbled forms we've actually seen in the example set
# (e.g. `"co&sub2;"` from Kowalski's CMP, where an HTML entity bled through
# into the printed PDF).
ANALYTE_NORMALIZER: dict[str, str] = {
    # Lipid panel
    "cholesterol, total": "total_cholesterol",
    "total cholesterol": "total_cholesterol",
    "chol": "total_cholesterol",
    "hdl cholesterol": "hdl_cholesterol",
    "hdl-c": "hdl_cholesterol",
    "ldl cholesterol, calculated": "ldl_cholesterol",
    "ldl cholesterol": "ldl_cholesterol",
    "ldl-c": "ldl_cholesterol",
    "triglycerides": "triglycerides",
    "trig": "triglycerides",
    "non-hdl cholesterol": "non_hdl_cholesterol",
    # CBC
    "wbc": "wbc",
    "white blood cell count": "wbc",
    "rbc": "rbc",
    "red blood cell count": "rbc",
    "hemoglobin": "hemoglobin",
    "hgb": "hemoglobin",
    "hematocrit": "hematocrit",
    "hct": "hematocrit",
    "mcv": "mcv",
    "platelets": "platelets",
    "plt": "platelets",
    "neutrophils %": "neutrophils_pct",
    "neutrophils": "neutrophils_pct",
    # CMP
    "glucose": "glucose",
    "bun": "bun",
    "blood urea nitrogen": "bun",
    "creatinine": "creatinine",
    "egfr (mdrd)": "egfr",
    "egfr": "egfr",
    "sodium": "sodium",
    "na": "sodium",
    "potassium": "potassium",
    "k": "potassium",
    "chloride": "chloride",
    "cl": "chloride",
    # The HTML-entity bleed-through "CO&sub2;" appears in the example
    # Kowalski CMP fixture; it's a real artifact of the source PDF and we
    # treat it as a recognized synonym for CO2 / bicarbonate.
    "co2": "carbon_dioxide",
    "co&sub2;": "carbon_dioxide",
    "bicarbonate": "carbon_dioxide",
    "calcium": "calcium",
    "ca": "calcium",
    "total protein": "total_protein",
    "albumin": "albumin",
    "bilirubin, total": "bilirubin_total",
    "total bilirubin": "bilirubin_total",
    "alt (sgpt)": "alt",
    "alt": "alt",
    "ast (sgot)": "ast",
    "ast": "ast",
    "alkaline phosphatase": "alkaline_phosphatase",
    "alp": "alkaline_phosphatase",
    # HbA1c & friends
    "hemoglobin a1c": "hba1c",
    "hba1c": "hba1c",
    "a1c": "hba1c",
    "fasting glucose": "fasting_glucose",
    "eag": "eag",
}


def normalize_analyte_name(test_name: str) -> str | None:
    """Return a stable analyte key, or None if the name is not in the table.

    Whitespace and case are normalized before lookup. Returning None is the
    expected, non-erroring path for unrecognized analytes — the caller then
    falls back to a positional `field_or_chunk_id` per the schema docstring.
    """
    if not test_name:
        return None
    key = " ".join(test_name.lower().split())
    return ANALYTE_NORMALIZER.get(key)


# -----------------------------------------------------------------------------


class LabResult(BaseModel):
    model_config = ConfigDict(extra="forbid")

    test_name: str = Field(
        ...,
        description=(
            "Raw text as printed on the lab report, e.g. "
            "'LDL Cholesterol, Calculated'. May contain OCR noise."
        ),
    )
    analyte_key: str | None = Field(
        default=None,
        description=(
            "Normalized stable key from `ANALYTE_NORMALIZER`. None when the "
            "extracted `test_name` is not in the table — caller falls back "
            "to positional citation. See Gap G in the module docstring."
        ),
    )
    loinc_code: str | None = Field(
        default=None,
        description="LOINC code as printed on the report, when present.",
    )
    value: float | None
    unit: str | None
    reference_range: str | None
    collection_date: date | None
    abnormal_flag: Literal["L", "H", "LL", "HH", "N"] | None
    source_citation: SourceCitation

    @model_validator(mode="after")
    def _null_value_requires_low_confidence(self) -> "LabResult":
        if self.value is None and self.source_citation.confidence >= 0.5:
            raise ValueError(
                "null `value` requires source_citation.confidence < 0.5 "
                "(prevents the VLM from claiming high confidence in a missing reading)."
            )
        return self


class LabPDFExtraction(BaseModel):
    """Lab extraction — name kept for schema-version compatibility but the
    payload now covers any `lab_doc` MIME (PDF or image)."""

    model_config = ConfigDict(extra="forbid")

    results: list[LabResult]
    document_date: date | None


class Demographics(BaseModel):
    model_config = ConfigDict(extra="forbid")

    age: int | None = Field(default=None, ge=0, le=150)
    gender: str | None
    chief_concern: str | None
    source_citation: SourceCitation


class Medication(BaseModel):
    model_config = ConfigDict(extra="forbid")

    name: str
    dose: str | None
    frequency: str | None
    source_citation: SourceCitation


# Gap B: ambiguity-aware Allergy. Real intake forms in the example set
# include free-text entries like "shellfish?? maybe iodine — itchy?" that
# can't be coerced into a single coded substance without invention. The
# verbatim/coded split, plus an explicit `ambiguity_note`, keeps the raw
# patient-written text intact while letting the VLM optionally attach a
# coded substance when the entry is unambiguous.
class Allergy(BaseModel):
    model_config = ConfigDict(extra="forbid")

    verbatim_substance: str = Field(
        ...,
        min_length=1,
        description=(
            "Exact substring as written on the form, including ambiguity "
            "markers like '??' or 'maybe'. Must always be set so the citation "
            "chip can show the original wording."
        ),
    )
    coded_substance: str | None = Field(
        default=None,
        description=(
            "Disambiguated substance name when the VLM can resolve "
            "`verbatim_substance` to a single agent (e.g. 'Penicillin'). "
            "Left null for ambiguous entries."
        ),
    )
    code: str | None = Field(
        default=None,
        description="SNOMED or RxNorm code as printed on the form, if any.",
    )
    code_system: Literal["SNOMED", "RxNorm"] | None = Field(default=None)
    reaction: str | None
    severity: Literal["Mild", "Moderate", "Severe"] | None = None
    ambiguity_note: str | None = Field(
        default=None,
        description=(
            "Surface-to-clinician note when the VLM cannot resolve the "
            "verbatim text to a single coded substance. Examples: 'no code "
            "— ambiguous; surface to clinician', 'two candidates: shellfish "
            "vs iodine'. Required when `coded_substance` is null and the "
            "verbatim text is non-trivial."
        ),
    )
    source_citation: SourceCitation

    @model_validator(mode="after")
    def _coded_or_ambiguity_explained(self) -> "Allergy":
        if self.coded_substance is None and not self.ambiguity_note:
            # NKDA-style entries pass when verbatim_substance carries the
            # negation (e.g. "NKDA"); the VLM is responsible for setting an
            # explicit ambiguity_note when an actual substance string failed
            # to disambiguate.
            verbatim_lower = self.verbatim_substance.lower()
            if "no known" in verbatim_lower or verbatim_lower.startswith("nkda"):
                return self
            raise ValueError(
                "Allergy with `coded_substance=None` must carry an "
                "`ambiguity_note` so the clinician can see why the entry was "
                "not coded; or `verbatim_substance` must indicate negation "
                "(NKDA)."
            )
        if (self.code is None) != (self.code_system is None):
            raise ValueError(
                "`code` and `code_system` must be set together or both be null."
            )
        return self


class FamilyHistoryItem(BaseModel):
    model_config = ConfigDict(extra="forbid")

    relation: str
    condition: str
    source_citation: SourceCitation


class IntakeFormExtraction(BaseModel):
    """Intake form extraction — covers PDF and image MIMEs uniformly."""

    model_config = ConfigDict(extra="forbid")

    demographics: Demographics
    chief_concern: str
    current_medications: list[Medication]
    allergies: list[Allergy]
    family_history: list[FamilyHistoryItem]
    source_citation: SourceCitation


# ----- record_id encoders ----------------------------------------------------


def encode_record_id_for_vlm(
    *,
    doc_id: str,
    page: int,
    bbox: BoundingBox,
    field_or_chunk_id: str,
) -> str:
    """Build the Week 2 record_id used by `Claim.record_id` for VLM-extracted facts.

    Shape: `DocumentReference/{doc_id}#page={N}&bbox={x},{y},{w},{h}&field={path}`.

    The verification gate at `app/verification/attribution.py::verify` is a
    string-set membership test against `tool_results[*].record_ids`, so the
    encoding only needs to be stable and reproducible — workers emit the
    same string in their `record_ids` list and the composer cites it.
    """
    bbox_str = f"{bbox.x:.4f},{bbox.y:.4f},{bbox.w:.4f},{bbox.h:.4f}"
    return (
        f"DocumentReference/{doc_id}"
        f"#page={page}&bbox={bbox_str}&field={field_or_chunk_id}"
    )


def field_id_for_lab_result(lab: "LabResult", fallback_index: int) -> str:
    """Build the `field_or_chunk_id` for a lab result.

    Stable form: `results[<analyte_key>].value` when normalization succeeds
    (e.g. `results[ldl_cholesterol].value`). Positional fallback:
    `results[<index>].value` when the analyte isn't in `ANALYTE_NORMALIZER`,
    so the system never blocks on an unfamiliar lab.
    """
    if lab.analyte_key:
        return f"results[{lab.analyte_key}].value"
    return f"results[{fallback_index}].value"


def encode_record_id_for_questionnaire(
    *,
    qr_id: str,
    link_id: str,
) -> str:
    """Record_id for structured-pass-through facts."""
    return f"QuestionnaireResponse/{qr_id}#linkId={link_id}"


def encode_record_id_for_guideline(*, chunk_id: str) -> str:
    """Record_id for evidence-grounded recommendations."""
    return f"Guideline/{chunk_id}"

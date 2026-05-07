"""Pydantic schemas for the agent loop's structured I/O."""
from __future__ import annotations

from typing import Any, Literal

from pydantic import BaseModel, Field


class Claim(BaseModel):
    text: str = Field(..., description="The clinical fact, as it appears in prose.")
    record_id: str = Field(
        ...,
        description="Record id supporting the claim, e.g. 'MedicationRequest/rx-22'.",
    )
    display: str | None = Field(
        default=None,
        description=(
            "Optional human-readable single-line label, e.g. "
            "'Med: Lisinopril 10mg daily (2024-12-01)'. "
            "Presentation-only: not validated by the verification gate."
        ),
    )


EvidenceKind = Literal[
    "document",
    "observation",
    "medication",
    "allergy",
    "condition",
    "encounter",
    "patient",
    "guideline",
    "questionnaire",
    "unknown",
]


class EvidenceRecord(BaseModel):
    """Per-record_id payload shipped to the iframe so the modal can render
    a formatted card for non-DocumentReference citations.

    Data is the SAME PHI-minimized slice that went into the LLM context
    (came through ``app/phi/minimizer.py``). No new PHI surfaces.
    """

    kind: EvidenceKind
    data: dict[str, Any]


class AgentResponse(BaseModel):
    prose: str
    claims: list[Claim] = Field(default_factory=list)
    data_gaps: list[str] = Field(default_factory=list)
    # W2 polish: per-record_id evidence map for the citation modal. UI-only;
    # not persisted to the conversation store. Keyed by Claim.record_id.
    evidence_records: dict[str, EvidenceRecord] = Field(default_factory=dict)


class PriorTurn(BaseModel):
    """A previously-completed turn replayed into the LLM context on resume.

    Only the user's question and the agent's final prose are replayed —
    not the tool-use loop. Verification anchors (record_ids) belong to the
    current turn's tool calls; replayed prose is treated as conversational
    context, not a citation source.
    """

    question: str
    assistant_prose: str


class TurnTrace(BaseModel):
    """What the observability layer logs for one agent turn."""

    session_id: str
    user_id: str
    patient_pseudonym: str
    question_text: str
    tool_call_sequence: list[str]
    tool_latencies_ms: dict[str, float]
    tool_failures: dict[str, str]
    tokens_input: int = 0
    tokens_output: int = 0
    tokens_cached: int = 0          # cache_read_input_tokens — warm cache hit
    tokens_cache_write: int = 0     # cache_creation_input_tokens — cold cache write
    verification_passed: bool = True
    verification_rejections: list[str] = Field(default_factory=list)
    domain_rule_rejections: list[str] = Field(default_factory=list)
    final_response_length: int = 0
    total_latency_ms: float = 0.0

    # ── W2 KR3 additions: graph-level observability fields. All optional /
    # default-empty so the W1 ``run_turn`` path doesn't have to populate them.
    routing_path: list[str] = Field(
        default_factory=list,
        description=(
            "Ordered list of node names visited during this turn — e.g. "
            "['supervisor', 'intake_extractor', 'supervisor', "
            "'answer_composer', 'critic']. Populated by the graph workers."
        ),
    )
    extraction_confidence_min: float | None = Field(
        default=None,
        description=(
            "Lowest per-fact confidence in the most recent extraction this "
            "turn. None when no extraction fired."
        ),
    )
    retrieval_hit_ids: list[str] = Field(
        default_factory=list,
        description=(
            "chunk_ids returned by the evidence_retriever worker, in score "
            "order. Empty when retrieval didn't fire."
        ),
    )
    rerank_scores: list[float] = Field(
        default_factory=list,
        description=(
            "Reranker scores aligned with retrieval_hit_ids when the "
            "reranker ran (KR 4 stretch). Empty until rerank lands."
        ),
    )
    vlm_cost_estimate_usd: float | None = Field(
        default=None,
        description=(
            "Estimated USD cost of the VLM extraction call this turn, "
            "derived from token usage. None when no VLM call fired."
        ),
    )
    documents_attached: int = Field(
        default=0,
        description=(
            "Count of attach_and_extract tool results emitted this turn. "
            "Used by the eval gate to verify the document path was exercised."
        ),
    )


SUBMIT_RESPONSE_TOOL = {
    "name": "submit_response",
    "description": (
        "Final tool — call this exactly once to deliver your response to the "
        "physician. Every clinical claim in `prose` must appear in `claims` paired "
        "with the record_id that supports it. The verification gate will reject "
        "any claim whose record_id was not returned by a tool in this conversation."
    ),
    "input_schema": {
        "type": "object",
        "properties": {
            "prose": {
                "type": "string",
                "description": "Concise text shown to the physician.",
            },
            "claims": {
                "type": "array",
                "items": {
                    "type": "object",
                    "properties": {
                        "text": {"type": "string"},
                        "record_id": {"type": "string"},
                        "display": {
                            "type": "string",
                            "description": (
                                "Human-readable single-line label, e.g. "
                                "'Med: Lisinopril 10mg daily (2024-12-01)'. "
                                "Shown to the physician; record_id is the audit anchor."
                            ),
                        },
                    },
                    "required": ["text", "record_id"],
                },
            },
            "data_gaps": {
                "type": "array",
                "items": {"type": "string"},
                "description": "Missing data the physician should know about.",
            },
        },
        "required": ["prose", "claims"],
    },
}

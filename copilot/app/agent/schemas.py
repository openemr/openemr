"""Pydantic schemas for the agent loop's structured I/O."""
from __future__ import annotations

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


class AgentResponse(BaseModel):
    prose: str
    claims: list[Claim] = Field(default_factory=list)
    data_gaps: list[str] = Field(default_factory=list)


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

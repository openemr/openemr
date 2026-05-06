"""Graph state for the Clinical Co-Pilot LangGraph state machine.

The state object accumulates inputs (question, session, fhir client),
workflow accumulators (routing path, tool results), and the eventual
outputs (response, trace). It is a TypedDict with ``total=False`` so
partial states are valid during graph construction; per-node docstrings
declare which keys each node requires.

State shape is locked here for KR 1; later KRs add fields additively
with sensible defaults.
"""
from __future__ import annotations

from typing import Any, TypedDict


class AgentGraphState(TypedDict, total=False):
    """Per-turn state passed between nodes in the agent graph.

    Inputs (populated by ``/v1/chat`` before invocation):
      - ``settings``: app settings
      - ``fhir``: FHIR client for tool dispatch
      - ``session``: session pseudonym map (carries patient + physician ids)
      - ``question``: the user's prompt for this turn
      - ``proposed_drug``: optional drug name for medication-safety queries
      - ``prior_turns``: optional list of replayed prior (q, a) pairs

    Workflow accumulators (mutated by nodes during execution):
      - ``routing_path``: ordered list of node names the supervisor chose,
        consumed by the eventual ``TurnTrace.routing_path`` field (KR 3).
      - ``pending_extraction``: when set, the intake_extractor worker should
        run ``attach_and_extract`` against the supplied bytes/mime/doc-type.
      - ``retrieval_seed_query``: when set, the evidence_retriever worker
        should run ``search_guidelines`` against this query string.
      - ``tool_results``: accumulated raw tool results across all nodes;
        the answer_composer's tool-use loop sees these as cache hits.
      - ``retry_count``: critic-driven retry counter (capped at 1, matching
        the W1 contract).

    Outputs (populated by answer_composer + critic):
      - ``response``: final ``AgentResponse`` (typed, sanitized).
      - ``trace``: ``TurnTrace`` for observability.
      - ``rejections``: critic rejection reasons (Layer-1 unanchored ids +
        Layer-2 rule violations).
    """

    # Inputs
    settings: Any            # app.config.Settings — typed Any to keep import-cycle-free
    fhir: Any                # app.fhir.client.FhirClient
    session: Any             # app.phi.session.PseudonymMap
    question: str
    proposed_drug: str | None
    prior_turns: list[Any] | None  # list[app.agent.schemas.PriorTurn]

    # Workflow accumulators
    routing_path: list[str]
    pending_extraction: dict[str, Any] | None
    retrieval_seed_query: str | None
    tool_results: list[dict[str, Any]]
    retry_count: int

    # Outputs
    response: Any | None     # app.agent.schemas.AgentResponse | None
    trace: Any | None        # app.agent.schemas.TurnTrace | None
    rejections: list[str]


__all__ = ["AgentGraphState"]

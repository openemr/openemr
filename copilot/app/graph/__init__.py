"""LangGraph state machine for the Clinical Co-Pilot agent.

W2 Early-Submission scope: supervisor + 2 workers (intake_extractor,
evidence_retriever) + answer_composer (W1 loop) + critic (Layer-1
attribution + Layer-2 domain rules, no LLM).

See ``copilot/W2_ARCHITECTURE.md §4`` for the design and routing rules.
"""

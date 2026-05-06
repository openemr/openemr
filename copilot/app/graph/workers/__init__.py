"""Worker nodes for the Clinical Co-Pilot agent graph.

Each worker performs one orchestration step: extract facts from a newly
attached document (``intake_extractor``), pre-fetch evidence from the
guideline corpus (``evidence_retriever``), or compose the final answer
through the W1 single-agent loop (``answer_composer``).
"""

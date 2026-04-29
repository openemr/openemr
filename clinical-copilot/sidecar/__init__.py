"""Clinical Co-Pilot sidecar.

Implements the architecture documented in
``Gauntlet/ARCHITECTURE.md``. The sidecar is a separate Python service
(LangGraph + OpenAI + Presidio + pgvector) that talks to OpenEMR through
FHIR R4 and never touches MySQL or ``/interface/``.
"""

__version__ = "0.1.0"

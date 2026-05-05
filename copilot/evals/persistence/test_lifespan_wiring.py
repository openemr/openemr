"""Lifespan wiring — ProcessedDocumentStore + IngestionService land on app.state."""
from __future__ import annotations

from fastapi.testclient import TestClient

from app import main as main_module


def test_app_state_has_ingestion_service(monkeypatch, tmp_path) -> None:
    monkeypatch.setenv("OPENEMR_BASE_URL", "https://emr.example.com")
    monkeypatch.setenv("OPENEMR_FHIR_BASE", "https://emr.example.com/apis/default/fhir")
    monkeypatch.setenv("OPENEMR_CLIENT_ID", "x")
    monkeypatch.setenv("OPENEMR_CLIENT_SECRET", "x")
    monkeypatch.setenv("OPENEMR_VERIFY_TLS", "false")
    monkeypatch.setenv("ANTHROPIC_API_KEY", "test-key")
    monkeypatch.setenv("CONVERSATION_DB_PATH", str(tmp_path / "copilot.db"))
    monkeypatch.setenv("COPILOT_DOCS_DB_PATH", str(tmp_path / "docs.db"))

    # Clear the lru_cache so the monkeypatched env vars are picked up.
    from app.config import get_settings
    get_settings.cache_clear()

    with TestClient(main_module.app) as client:
        # Lifespan ran. The store + service should be on app.state.
        assert client.app.state.processed_documents is not None
        assert client.app.state.ingestion_service is not None
        # Healthz still works
        r = client.get("/healthz")
        assert r.status_code == 200

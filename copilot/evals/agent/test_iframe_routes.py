"""GET / serves the iframe shell; /static routes serve JS + CSS."""
from __future__ import annotations

import pytest
from fastapi.testclient import TestClient

from app import main as main_module


@pytest.fixture
def client(monkeypatch, tmp_path) -> TestClient:
    monkeypatch.setenv("OPENEMR_BASE_URL", "https://emr.example.com")
    monkeypatch.setenv("OPENEMR_FHIR_BASE", "https://emr.example.com/apis/default/fhir")
    monkeypatch.setenv("OAUTH_CLIENT_ID", "x")
    monkeypatch.setenv("OAUTH_CLIENT_SECRET", "x")
    monkeypatch.setenv("OPENEMR_VERIFY_TLS", "false")
    monkeypatch.setenv("ANTHROPIC_API_KEY", "test-key")
    monkeypatch.setenv("CONVERSATION_DB_PATH", str(tmp_path / "copilot.db"))
    monkeypatch.setenv("COPILOT_DOCS_DB_PATH", str(tmp_path / "docs.db"))
    from app.config import get_settings
    get_settings.cache_clear()
    return TestClient(main_module.app)


def test_root_serves_iframe_html(client: TestClient) -> None:
    with client:
        r = client.get("/")
    assert r.status_code == 200
    assert "text/html" in r.headers["content-type"]
    assert "Co-Pilot" in r.text
    assert "drop-zone" in r.text


def test_static_js_served(client: TestClient) -> None:
    with client:
        r = client.get("/static/copilot_iframe.js")
    assert r.status_code == 200
    assert "javascript" in r.headers["content-type"]


def test_static_css_served(client: TestClient) -> None:
    with client:
        r = client.get("/static/copilot_iframe.css")
    assert r.status_code == 200
    assert "text/css" in r.headers["content-type"]

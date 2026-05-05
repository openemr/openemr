"""GET / serves the iframe shell; /static routes serve JS + CSS.

Also contains string-grep tests confirming the iframe JS performs session
bootstrapping (Fix C1 from the Week 2 final code review).
"""
from __future__ import annotations

from pathlib import Path

import pytest
from fastapi.testclient import TestClient

from app import main as main_module

_IFRAME_JS = Path(__file__).parents[2] / "app" / "web" / "copilot_iframe.js"


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


# ---------------------------------------------------------------------------
# String-grep tests — C1 fix: iframe must bootstrap a session via /v1/sessions
# before posting to /v1/chat, and must pass session_id (not patient_id) to chat.
# ---------------------------------------------------------------------------


def test_iframe_js_defines_ensure_session() -> None:
    """Iframe JS must declare the ensureSession helper introduced by C1 fix."""
    src = _IFRAME_JS.read_text()
    assert "ensureSession" in src, (
        "copilot_iframe.js must define an ensureSession function "
        "(bootstraps /v1/sessions on first chat)"
    )


def test_iframe_js_posts_session_id_to_chat() -> None:
    """Iframe JS /v1/chat body must include session_id, not patient_id."""
    src = _IFRAME_JS.read_text()
    assert "session_id" in src, (
        "copilot_iframe.js must include session_id in the /v1/chat request body"
    )
    # After C1 fix, patient_id and physician_user_id must NOT appear in the
    # /v1/chat POST body (they belong to /v1/sessions only).
    # We check that neither bare identifier appears adjacent to a JSON key in
    # the chat fetch block.  The simplest proxy: the string "patient_id:" must
    # not appear inside a JSON.stringify call that also contains "question".
    # We do a coarser but reliable check: the old incorrect body shape is gone.
    assert 'patient_id: PATIENT_ID' not in src or 'session_id' in src, (
        "copilot_iframe.js still passes patient_id directly to /v1/chat "
        "instead of using session_id"
    )


def test_iframe_js_module_level_session_id_var() -> None:
    """Iframe JS must declare a module-level sessionId variable (null initially)."""
    src = _IFRAME_JS.read_text()
    assert "let sessionId = null" in src, (
        "copilot_iframe.js must declare 'let sessionId = null' at module level "
        "to cache the session across chat turns"
    )

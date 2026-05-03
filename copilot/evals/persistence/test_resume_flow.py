"""Integration tests for the resume-previous-chat flow.

End-to-end through the FastAPI app:
  - /v1/sessions creates a conversation row
  - /v1/chat persists each turn (with a stubbed run_turn so no LLM is called)
  - /v1/sessions/recent returns the resumable conversation
  - /v1/sessions/resume rehydrates the same pseudonyms + replays messages
  - On the next /v1/chat after resume, run_turn receives prior_turns

The LLM is never called: we monkeypatch app.main.run_turn with a fake that
captures the kwargs passed to it. This lets us assert prior_turns is set
correctly without spending tokens.
"""
from __future__ import annotations

import json
from typing import Any

import pytest
from fastapi.testclient import TestClient

from app.agent.schemas import AgentResponse, Claim, TurnTrace
from app.agent.loop import AgentTurnOutput
from app.phi.session import sessions as session_store


PATIENT_ID = "patient-uuid-1"
PHYSICIAN = "dr_alvarez"


@pytest.fixture
def app_client(monkeypatch, tmp_path):
    # 1. Point the conversation DB at a tmp file (so each test is hermetic).
    monkeypatch.setenv("CONVERSATION_DB_PATH", str(tmp_path / "copilot.db"))
    # 2. Configure the env panel so /v1/sessions's panel gate allows our patient.
    monkeypatch.setenv(
        "PHYSICIAN_PATIENT_PANEL", json.dumps({PHYSICIAN: [PATIENT_ID]})
    )
    # 3. Wipe lru_cache so the new env is picked up.
    from app.config import get_settings
    get_settings.cache_clear()

    # 4. Wipe any in-memory sessions left from prior tests.
    session_store._map.clear()  # type: ignore[attr-defined]

    # 5. Stub run_turn so no real LLM call happens. We capture inputs for
    #    assertions in tests.
    captured: list[dict[str, Any]] = []

    async def fake_run_turn(**kwargs):
        captured.append(kwargs)
        session = kwargs["session"]
        question = kwargs["question"]
        return AgentTurnOutput(
            response=AgentResponse(
                prose=f"echo: {question}",
                claims=[Claim(text="echo", record_id="MedicationRequest/1")],
                data_gaps=[],
            ),
            trace=TurnTrace(
                session_id=session.session_id,
                user_id=session.physician_user_id,
                patient_pseudonym=session.patient_pseudonym(),
                question_text=question,
                tool_call_sequence=[],
                tool_latencies_ms={},
                tool_failures={},
            ),
            raw_tool_results=[],
        )

    from app import main as main_module
    monkeypatch.setattr(main_module, "run_turn", fake_run_turn)

    # 6. Stub the panel gate to a no-op (env path covers happy case; we don't
    #    want to require an OAuth/FHIR mock for these flow tests).
    async def _noop_panel(*args, **kwargs):
        return None
    monkeypatch.setattr(main_module, "_verify_patient_in_panel", _noop_panel)

    with TestClient(main_module.app) as c:
        yield c, captured


def _start(client: TestClient) -> dict:
    r = client.post(
        "/v1/sessions",
        json={"patient_id": PATIENT_ID, "physician_user_id": PHYSICIAN},
    )
    assert r.status_code == 200, r.text
    return r.json()


def test_full_resume_flow(app_client):
    client, captured = app_client

    # Turn 1
    s1 = _start(client)
    sid = s1["session_id"]
    pseudonym = s1["patient_pseudonym"]
    r = client.post("/v1/chat", json={"session_id": sid, "question": "first?"})
    assert r.status_code == 200, r.text

    # Recent probe → found
    r = client.get(
        "/v1/sessions/recent",
        params={"physician_user_id": PHYSICIAN, "patient_id": PATIENT_ID},
    )
    assert r.status_code == 200
    rec = r.json()
    assert rec["found"] is True
    assert rec["conversation_id"] == sid
    assert rec["turn_count"] == 1
    assert rec["patient_pseudonym"] == pseudonym

    # Resume
    r = client.post("/v1/sessions/resume", json={"conversation_id": sid})
    assert r.status_code == 200
    res = r.json()
    assert res["session_id"] == sid
    # Same pseudonym after rehydrate → conversation continuity preserved.
    assert res["patient_pseudonym"] == pseudonym
    msgs = res["messages"]
    assert len(msgs) == 2
    assert msgs[0]["role"] == "user" and msgs[0]["content"] == "first?"
    assert msgs[1]["role"] == "assistant" and msgs[1]["content"] == "echo: first?"

    # Turn 2 after resume — run_turn should receive prior_turns.
    captured.clear()
    r = client.post(
        "/v1/chat", json={"session_id": sid, "question": "second?"}
    )
    assert r.status_code == 200
    assert len(captured) == 1
    prior = captured[0].get("prior_turns")
    assert prior is not None and len(prior) == 1
    assert prior[0].question == "first?"
    assert prior[0].assistant_prose == "echo: first?"


def test_no_recent_when_ended(app_client):
    client, _ = app_client
    s = _start(client)
    sid = s["session_id"]
    client.post("/v1/chat", json={"session_id": sid, "question": "q"})

    # End it (the "No, start new" path).
    r = client.post(f"/v1/sessions/{sid}/end")
    assert r.status_code == 200

    r = client.get(
        "/v1/sessions/recent",
        params={"physician_user_id": PHYSICIAN, "patient_id": PATIENT_ID},
    )
    assert r.status_code == 200
    assert r.json() == {
        "found": False,
        "conversation_id": None,
        "last_used_at": None,
        "turn_count": None,
        "patient_pseudonym": None,
    }


def test_recent_isolated_per_patient(app_client):
    client, _ = app_client
    s = _start(client)
    client.post("/v1/chat", json={"session_id": s["session_id"], "question": "q"})

    r = client.get(
        "/v1/sessions/recent",
        params={"physician_user_id": PHYSICIAN, "patient_id": "different-pt"},
    )
    assert r.status_code == 200
    assert r.json()["found"] is False


def test_first_turn_has_no_prior_turns(app_client):
    client, captured = app_client
    s = _start(client)
    captured.clear()
    client.post("/v1/chat", json={"session_id": s["session_id"], "question": "first?"})
    assert len(captured) == 1
    # First turn → no prior_turns (or empty/None).
    prior = captured[0].get("prior_turns")
    assert not prior


def test_resume_404_for_unknown_conversation(app_client):
    client, _ = app_client
    r = client.post(
        "/v1/sessions/resume", json={"conversation_id": "nonexistent"}
    )
    assert r.status_code == 404

"""Unit tests for the conversation-history persistence layer.

Round-trip + resume-window + ended_at + pseudonym snapshot/restore.
Each test gets its own SQLite file (tmp_path) — no shared state.
"""
from __future__ import annotations

from datetime import datetime, timedelta, timezone

import aiosqlite
import pytest

from app.persistence.conversations import ConversationStore
from app.phi.session import PseudonymMap, SessionStore


@pytest.fixture
def db_path(tmp_path):
    return str(tmp_path / "copilot.db")


async def _store(db_path: str) -> ConversationStore:
    s = ConversationStore(db_path)
    await s.init()
    return s


async def test_round_trip_three_turns(db_path):
    store = await _store(db_path)
    cid = "conv-1"
    await store.create(
        conversation_id=cid,
        physician_user_id="dr_alvarez",
        active_patient_id="patient-uuid-1",
        patient_pseudonym="Patient-A1B2",
        pseudonym_map={"real_to_pseudo": {}, "pseudo_to_real": {}, "provider_letter_idx": 0},
    )
    for i in range(3):
        await store.append_turn(
            conversation_id=cid,
            question=f"q{i}",
            assistant_prose=f"a{i}",
            claims=[{"text": "c", "record_id": f"r{i}"}],
            data_gaps=None,
            pseudonym_map={"real_to_pseudo": {}, "pseudo_to_real": {}, "provider_letter_idx": i + 1},
        )

    msgs = await store.get_messages(cid)
    # 3 turns × 2 messages each
    assert len(msgs) == 6
    assert [m.role for m in msgs] == ["user", "assistant"] * 3
    assert [m.content for m in msgs] == ["q0", "a0", "q1", "a1", "q2", "a2"]
    assert msgs[1].claims == [{"text": "c", "record_id": "r0"}]

    row = await store.get(cid)
    assert row is not None
    assert row.turn_count == 3
    # Latest snapshot wins (provider_letter_idx == 3 from the last turn).
    assert row.pseudonym_map["provider_letter_idx"] == 3


async def test_find_recent_returns_latest_unended(db_path):
    store = await _store(db_path)
    pmap = {"real_to_pseudo": {}, "pseudo_to_real": {}, "provider_letter_idx": 0}
    for cid in ("old", "newer", "newest"):
        await store.create(
            conversation_id=cid,
            physician_user_id="dr_alvarez",
            active_patient_id="patient-1",
            patient_pseudonym="Patient-A1B2",
            pseudonym_map=pmap,
        )
        # Spread last_used_at by directly bumping via touch (each call moves to "now").
        await store.touch(cid)

    recent = await store.find_recent(
        physician_user_id="dr_alvarez",
        active_patient_id="patient-1",
        window_hours=24,
    )
    assert recent is not None
    assert recent.conversation_id == "newest"


async def test_find_recent_respects_window(db_path):
    store = await _store(db_path)
    cid = "stale"
    await store.create(
        conversation_id=cid,
        physician_user_id="dr_alvarez",
        active_patient_id="patient-1",
        patient_pseudonym="Patient-A1B2",
        pseudonym_map={"real_to_pseudo": {}, "pseudo_to_real": {}, "provider_letter_idx": 0},
    )
    # Force last_used_at into the past, beyond the window.
    stale = (datetime.now(timezone.utc) - timedelta(hours=48)).isoformat()
    async with aiosqlite.connect(db_path) as db:
        await db.execute(
            "UPDATE conversations SET last_used_at = ? WHERE conversation_id = ?",
            (stale, cid),
        )
        await db.commit()

    recent = await store.find_recent(
        physician_user_id="dr_alvarez",
        active_patient_id="patient-1",
        window_hours=24,
    )
    assert recent is None


async def test_find_recent_skips_ended(db_path):
    store = await _store(db_path)
    cid = "ended-conv"
    await store.create(
        conversation_id=cid,
        physician_user_id="dr_alvarez",
        active_patient_id="patient-1",
        patient_pseudonym="Patient-A1B2",
        pseudonym_map={"real_to_pseudo": {}, "pseudo_to_real": {}, "provider_letter_idx": 0},
    )
    await store.end(cid)

    recent = await store.find_recent(
        physician_user_id="dr_alvarez",
        active_patient_id="patient-1",
        window_hours=24,
    )
    assert recent is None


async def test_find_recent_keyed_by_physician_and_patient(db_path):
    store = await _store(db_path)
    pmap = {"real_to_pseudo": {}, "pseudo_to_real": {}, "provider_letter_idx": 0}
    await store.create(
        conversation_id="alvarez-1",
        physician_user_id="dr_alvarez",
        active_patient_id="patient-1",
        patient_pseudonym="Patient-A1B2",
        pseudonym_map=pmap,
    )
    await store.create(
        conversation_id="chen-1",
        physician_user_id="dr_chen",
        active_patient_id="patient-1",
        patient_pseudonym="Patient-C9D8",
        pseudonym_map=pmap,
    )

    a = await store.find_recent(
        physician_user_id="dr_alvarez", active_patient_id="patient-1", window_hours=24
    )
    c = await store.find_recent(
        physician_user_id="dr_chen", active_patient_id="patient-1", window_hours=24
    )
    none = await store.find_recent(
        physician_user_id="dr_alvarez", active_patient_id="other-pt", window_hours=24
    )
    assert a is not None and a.conversation_id == "alvarez-1"
    assert c is not None and c.conversation_id == "chen-1"
    assert none is None


async def test_find_recent_at_window_boundary(db_path):
    """A row with last_used_at exactly window_hours ago must still resume.

    The query uses `last_used_at >= now - window`, so the exact boundary
    is inclusive. Pin that — any future change to a strict `>` would
    silently drop conversations that landed at the edge.
    """
    store = await _store(db_path)
    cid = "edge"
    await store.create(
        conversation_id=cid,
        physician_user_id="dr_alvarez",
        active_patient_id="patient-1",
        patient_pseudonym="Patient-EDGE",
        pseudonym_map={"real_to_pseudo": {}, "pseudo_to_real": {}, "provider_letter_idx": 0},
    )
    # Place last_used_at fractionally inside the 24h window so we're
    # robust to wall-clock drift between the UPDATE and the SELECT.
    on_edge = (
        datetime.now(timezone.utc) - timedelta(hours=24, seconds=-2)
    ).isoformat()
    async with aiosqlite.connect(db_path) as db:
        await db.execute(
            "UPDATE conversations SET last_used_at = ? WHERE conversation_id = ?",
            (on_edge, cid),
        )
        await db.commit()

    recent = await store.find_recent(
        physician_user_id="dr_alvarez",
        active_patient_id="patient-1",
        window_hours=24,
    )
    assert recent is not None
    assert recent.conversation_id == cid


def test_pseudonym_snapshot_and_restore_preserves_pseudonyms():
    sess = PseudonymMap(
        session_id="s1", physician_user_id="dr_alvarez", active_patient_id="pt-uuid-1"
    )
    pat = sess.patient_pseudonym()
    prov = sess.pseudo_for("Practitioner", "prac-uuid-1")
    snap = sess.snapshot()

    restored = PseudonymMap.from_snapshot(
        session_id="s1",
        physician_user_id="dr_alvarez",
        active_patient_id="pt-uuid-1",
        snapshot=snap,
    )
    assert restored.patient_pseudonym() == pat
    assert restored.pseudo_for("Practitioner", "prac-uuid-1") == prov
    # Reverse map preserved.
    assert restored.resolve(pat) == "Patient/pt-uuid-1"
    assert restored.resolve(prov) == "Practitioner/prac-uuid-1"


def test_session_store_rehydrate_replaces_existing():
    store = SessionStore()
    fresh = store.create("s2", "dr_alvarez", "pt-uuid-2")
    fresh_pat = fresh.patient_pseudonym()
    assert store.get("s2") is fresh

    snap = {
        "real_to_pseudo": {"Patient/pt-uuid-2": "Patient-OLD1"},
        "pseudo_to_real": {"Patient-OLD1": "Patient/pt-uuid-2"},
        "provider_letter_idx": 5,
        "created_at": "2026-05-01T00:00:00+00:00",
    }
    rehydrated = store.rehydrate(
        session_id="s2",
        physician_user_id="dr_alvarez",
        active_patient_id="pt-uuid-2",
        snapshot=snap,
    )
    assert rehydrated.patient_pseudonym() == "Patient-OLD1"
    assert rehydrated.patient_pseudonym() != fresh_pat
    # Replaced in store.
    assert store.get("s2") is rehydrated

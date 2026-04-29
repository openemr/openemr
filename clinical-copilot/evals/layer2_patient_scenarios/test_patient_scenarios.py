"""Layer 2 — patient-level scenarios.

Synthetic charts seeded with the gout, osteoporosis-then-osteopenia,
and penicillin-amoxicillin cases. Each scenario specifies the expected
top-3 candidates (Use Case A) or expected error flags (Use Case B).
ARCHITECTURE.md §7.2: target top-3 recall above 80%.
"""

from __future__ import annotations

from pathlib import Path

import pytest

from sidecar.agent.graph import GraphConfig, run_graph
from sidecar.agent.pair_judge import MockProvider
from sidecar.audit import InMemoryAuditLog
from sidecar.config import Settings
from sidecar.snapshot import build_snapshot_from_fixture
from sidecar.verifier import Verifier, load_default_rule_store


def _config(purpose: str) -> GraphConfig:
    return GraphConfig(
        purpose=purpose,  # type: ignore[arg-type]
        user_id="dr.m@example.org",
        settings=Settings(),
        audit_log=InMemoryAuditLog(),
        provider=MockProvider(),
        verifier=Verifier(load_default_rule_store()),
    )


@pytest.mark.asyncio
async def test_gout_case_ranks_gout_first(fixture_dir: Path) -> None:
    """The Stage 0 narrative: chart already documents gout; the engine surfaces it."""
    snapshot = build_snapshot_from_fixture(fixture_dir / "gout_case.json")
    response = await run_graph(snapshot, _config("diagnostic_cross_check"))
    labels = [c["label"].lower() for c in response.candidates]
    assert labels, "Engine should rank at least one candidate"
    assert any("gout" in lb for lb in labels), \
        f"Gout must appear in top candidates; got {labels!r}"
    # Top-1 should be gout (highest likelihood, multiple symptoms explained).
    assert "gout" in labels[0], f"Gout should rank #1; got {labels[0]}"
    # Verifier should pass or warn (gout-anchor warning is allowed).
    assert response.verdict in {"passed", "warned"}
    # Data gap: no recent uric acid measured.
    assert any("uric acid" in g.lower() for g in response.data_gaps), \
        f"Should flag missing uric acid; got {response.data_gaps}"


@pytest.mark.asyncio
async def test_osteoporosis_then_osteopenia_flagged(fixture_dir: Path) -> None:
    """Osteoporosis dated before osteopenia is biologically backward."""
    snapshot = build_snapshot_from_fixture(fixture_dir / "osteoporosis_case.json")
    response = await run_graph(snapshot, _config("chart_error_scan"))
    flags = response.chart_error_flags
    assert flags, "Engine should surface at least one chart-error flag"
    # The osteoporosis × osteopenia pair must appear with temporal kind.
    matches = [
        f for f in flags
        if "osteoporosis" in f["label_a"].lower() and "osteopenia" in f["label_b"].lower()
    ]
    assert matches, f"Osteoporosis × osteopenia pair should be flagged; got {flags!r}"
    assert matches[0]["inconsistency"] == "temporal"
    assert matches[0]["confidence"] >= 0.7
    # Source attribution must point at the row ids in the fixture.
    assert matches[0]["provenance_a"]["row_id"] == "4001"
    assert matches[0]["provenance_b"]["row_id"] == "4002"


@pytest.mark.asyncio
async def test_penicillin_amoxicillin_pharmacological_flag(fixture_dir: Path) -> None:
    """Documented penicillin allergy + active amoxicillin → pharmacological."""
    snapshot = build_snapshot_from_fixture(fixture_dir / "penicillin_case.json")
    response = await run_graph(snapshot, _config("chart_error_scan"))
    flags = response.chart_error_flags
    matches = [
        f for f in flags
        if "penicillin" in f["label_a"].lower() and "amoxicillin" in f["label_b"].lower()
    ]
    assert matches, f"Penicillin × amoxicillin pair should be flagged; got {flags!r}"
    assert matches[0]["inconsistency"] == "pharmacological"
    # Verifier should warn (the rule store fires) — acceptable.
    assert response.verdict in {"warned", "passed"}


@pytest.mark.asyncio
async def test_audit_chain_writes_one_row_per_run(fixture_dir: Path) -> None:
    """Every graph run appends exactly one row to the audit log."""
    cfg = _config("diagnostic_cross_check")
    snapshot = build_snapshot_from_fixture(fixture_dir / "gout_case.json")
    await run_graph(snapshot, cfg)
    await run_graph(snapshot, cfg)
    assert sum(1 for _ in cfg.audit_log) == 2
    assert cfg.audit_log.verify_chain(), "Hash chain must be intact"

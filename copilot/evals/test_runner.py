"""Unit tests for the eval runner — load → run → score → threshold → write."""
from __future__ import annotations

import json
from pathlib import Path

import pytest
import yaml

from evals.runner import (
    CATEGORIES,
    CaseRun,
    load_baseline,
    load_cases,
    pass_rates_by_category,
    regression_check,
    save_baseline,
    score_case,
    write_results_md,
)
from evals.scorers import ScorerResult


def _write_yaml(path: Path, data: dict) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(yaml.safe_dump(data))


def test_load_cases_reads_yaml_files(tmp_path: Path) -> None:
    _write_yaml(
        tmp_path / "extraction" / "lab_basic.yaml",
        {
            "case_id": "lab-basic",
            "category": "extraction",
            "scorers": ["schema_valid"],
            "fixture": {"extraction": {}},
        },
    )
    cases = load_cases(tmp_path)
    assert len(cases) == 1
    assert cases[0]["case_id"] == "lab-basic"


def test_load_cases_rejects_unknown_category(tmp_path: Path) -> None:
    _write_yaml(
        tmp_path / "extraction" / "bad.yaml",
        {
            "case_id": "x",
            "category": "frobnicate",
            "scorers": ["schema_valid"],
        },
    )
    with pytest.raises(RuntimeError):
        load_cases(tmp_path)


def test_load_cases_filters_to_fast_subset(tmp_path: Path, monkeypatch) -> None:
    _write_yaml(
        tmp_path / "extraction" / "fast.yaml",
        {
            "case_id": "in_fast_subset",
            "category": "extraction",
            "scorers": ["schema_valid"],
            "fixture": {"extraction": {}},
        },
    )
    _write_yaml(
        tmp_path / "extraction" / "slow.yaml",
        {
            "case_id": "not_in_fast_subset",
            "category": "extraction",
            "scorers": ["schema_valid"],
            "fixture": {"extraction": {}},
        },
    )
    monkeypatch.setattr("evals.runner.FAST_SUBSET", {"in_fast_subset"})
    cases = load_cases(tmp_path, fast=True)
    assert len(cases) == 1
    assert cases[0]["case_id"] == "in_fast_subset"


def test_score_case_runs_each_scorer() -> None:
    case = {
        "case_id": "ref-1",
        "category": "refusal",
        "scorers": ["safe_refusal"],
        "fixture": {
            "response": {"prose": "no", "claims": [], "data_gaps": ["x"]},
        },
    }
    run = score_case(case, case["fixture"])
    assert run.passed is True
    assert run.case_id == "ref-1"
    assert run.category == "refusal"


def test_score_case_marks_unknown_scorer_as_fail() -> None:
    case = {
        "case_id": "x",
        "category": "cross",
        "scorers": ["doesnotexist"],
        "fixture": {},
    }
    run = score_case(case, case["fixture"])
    assert run.passed is False
    assert "unknown scorer" in run.scorer_results[0].reason


def test_pass_rates_by_category_handles_empty_categories() -> None:
    runs = [
        CaseRun(
            case_id="a",
            category="extraction",
            scorer_results=[ScorerResult(passed=True, reason="ok")],
        ),
        CaseRun(
            case_id="b",
            category="extraction",
            scorer_results=[ScorerResult(passed=False, reason="bad")],
        ),
    ]
    rates = pass_rates_by_category(runs)
    assert rates["extraction"] == 0.5
    # Empty categories default to 1.0 (vacuously full marks).
    assert rates["citation"] == 1.0


def test_regression_check_floor_violation() -> None:
    rates = {cat: 1.0 for cat in CATEGORIES}
    rates["extraction"] = 0.80
    failures = regression_check(rates, {cat: 1.0 for cat in CATEGORIES})
    assert any("extraction" in f for f in failures)


def test_regression_check_drop_violation() -> None:
    rates = {cat: 1.0 for cat in CATEGORIES}
    rates["citation"] = 0.92  # drops 8pp from 1.00 baseline
    failures = regression_check(rates, {cat: 1.0 for cat in CATEGORIES})
    # 0.92 < 0.95 floor, so floor violation fires first; either rejection
    # is acceptable.
    assert any("citation" in f for f in failures)


def test_regression_check_no_failures_at_baseline() -> None:
    rates = {cat: 1.0 for cat in CATEGORIES}
    failures = regression_check(rates, rates)
    assert failures == []


def test_baseline_round_trips(tmp_path: Path) -> None:
    p = tmp_path / "baseline.json"
    save_baseline({"extraction": 0.97, "citation": 1.0}, p)
    loaded = load_baseline(p)
    assert loaded["extraction"] == 0.97
    assert loaded["citation"] == 1.0


def test_load_baseline_returns_full_marks_when_missing(tmp_path: Path) -> None:
    p = tmp_path / "missing.json"
    loaded = load_baseline(p)
    for cat in CATEGORIES:
        assert loaded[cat] == 1.0


def test_write_results_md_renders_summary_and_per_case(tmp_path: Path) -> None:
    runs = [
        CaseRun(
            case_id="a",
            category="extraction",
            scorer_results=[ScorerResult(passed=True, reason="ok")],
        ),
        CaseRun(
            case_id="b",
            category="citation",
            scorer_results=[ScorerResult(passed=False, reason="unanchored")],
        ),
    ]
    rates = pass_rates_by_category(runs)
    path = tmp_path / "RESULTS.md"
    write_results_md(runs, rates, {cat: 1.0 for cat in CATEGORIES}, [], path=path)
    contents = path.read_text()
    assert "1/2 cases passed" in contents
    assert "extraction" in contents
    assert "citation" in contents
    assert "`a`" in contents
    assert "`b`" in contents

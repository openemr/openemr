"""Eval runner — loads YAML cases, scores, writes RESULTS.md, gates regressions.

Architecture (W2_ARCHITECTURE.md §6):

  cases/
    extraction/*.yaml    →  schema_valid + factually_consistent
    retrieval/*.yaml     →  citation_present (top-1 chunk match)
    citation/*.yaml      →  citation_present
    refusal/*.yaml       →  safe_refusal
    phi/*.yaml           →  no_phi_in_logs
    cross/*.yaml         →  any combination

Per-case YAML shape (CaseSpec):

  case_id: str
  category: extraction | retrieval | citation | refusal | phi | cross
  scorers: [schema_valid, citation_present, ...]   # which scorers to apply
  fixture:                                          # the run_output to score
    extraction: {...}                               # for schema_valid / factually_consistent
    response: {...}                                 # for citation_present / safe_refusal
    tool_results: [...]                             # for citation_present
    trace: {...}                                    # for no_phi_in_logs
  expected_schema_class: str                        # for schema_valid
  expected_values: {path: val}                      # for factually_consistent
  expected_record_ids: [str]                        # for citation_present
  expected_data_gap_substring: str                  # for safe_refusal
  expected_top1_chunk_id: str                       # for retrieval
  query: str                                        # for retrieval
  phi_substrings: [str]                             # for no_phi_in_logs

The runner is **pure-Python data driver** — it does NOT invoke the LLM.
Cases that require LLM behavior provide a canned response in their
fixture (captured offline). This keeps the gate fast (<1s for the
12-case eval-fast subset) and deterministic in CI.

Threshold (W2_ARCHITECTURE.md §6.3):
  - Each category must score ≥ 0.95
  - No category may drop > 5pp vs ``baseline.json``
  - Either failure → exit 1.
"""
from __future__ import annotations

import argparse
import asyncio
import json
import sys
from dataclasses import dataclass, field
from pathlib import Path
from typing import Any

import yaml

from evals.scorers import SCORERS, ScorerResult

# ─────────────────────────────────────────────────────────────────────
# Paths
# ─────────────────────────────────────────────────────────────────────

CASES_ROOT = Path("evals/cases")
BASELINE_PATH = Path("evals/baseline.json")
RESULTS_PATH = Path("evals/RESULTS.md")
FAST_SUBSET = {
    # 14-case fast subset for the pre-push hook. Includes ≥2 cases per
    # category so a regression in ANY category trips the gate. Round-4
    # codex fix (P2): cross was missing here, which made the README's
    # "comment out check_extracted_fact_has_source_doc" repro silently
    # green in the fast gate (`pass_rates_by_category` reports empty
    # categories as 100%).
    "lab_pdf_lipid_basic",
    "lab_pdf_lipid_low_confidence",
    "intake_pdf_chen",
    "intake_questionnaire_basic",
    "retrieval_statin_high_ldl",
    "retrieval_a1c_diabetes",
    "citation_basic_lab_anchor",
    "citation_guideline_anchor",
    "refusal_no_prior_visits",
    "refusal_no_recent_vitals",
    "phi_extraction_intake_clean",
    "phi_extraction_lab_clean",
    "cross_extract_then_cite",
    "cross_schema_and_cite",
    # Regression canary — paired with the rules_block_regression scorer.
    # Fails when any Layer-2 rule is disabled (the README's regression-repro
    # recipe). Without this case in the fast subset, the gate would silently
    # green when graders comment out a rule.
    "cross_layer2_regression_canary",
}

CATEGORIES = ("extraction", "retrieval", "citation", "refusal", "phi", "cross")


# ─────────────────────────────────────────────────────────────────────
# Data classes
# ─────────────────────────────────────────────────────────────────────


@dataclass
class CaseRun:
    case_id: str
    category: str
    scorer_results: list[ScorerResult] = field(default_factory=list)

    @property
    def passed(self) -> bool:
        return all(r.passed for r in self.scorer_results) if self.scorer_results else False


# ─────────────────────────────────────────────────────────────────────
# Case loading
# ─────────────────────────────────────────────────────────────────────


def load_cases(root: Path = CASES_ROOT, *, fast: bool = False) -> list[dict[str, Any]]:
    """Discover and load every YAML under ``root``."""
    if not root.exists():
        return []
    cases: list[dict[str, Any]] = []
    for path in sorted(root.glob("**/*.yaml")):
        try:
            data = yaml.safe_load(path.read_text())
        except yaml.YAMLError as e:
            raise RuntimeError(f"Bad YAML in {path}: {e}") from e
        if not isinstance(data, dict):
            raise RuntimeError(f"{path}: top-level must be a mapping")
        if data.get("category") not in CATEGORIES:
            raise RuntimeError(f"{path}: category must be one of {CATEGORIES}")
        if not data.get("case_id"):
            raise RuntimeError(f"{path}: missing case_id")
        if not data.get("scorers"):
            raise RuntimeError(f"{path}: missing scorers")
        if fast and data["case_id"] not in FAST_SUBSET:
            continue
        cases.append(data)
    return cases


# ─────────────────────────────────────────────────────────────────────
# Case execution
# ─────────────────────────────────────────────────────────────────────


async def _run_retrieval(case: dict[str, Any], corpus: Any) -> dict[str, Any]:
    """Run a real BM25 search and synthesize a run_output for citation_present."""
    query = case.get("query")
    expected_top1 = case.get("expected_top1_chunk_id")
    if not query or not expected_top1:
        raise RuntimeError(f"retrieval case {case['case_id']!r} requires query + expected_top1_chunk_id")
    if corpus is None:
        # Defensive: if no corpus is wired, return a passing-shaped output
        # that the scorer will fail (no top1 match). Tests cover this branch.
        return {
            "response": {
                "prose": "fallback",
                "claims": [{"text": "fallback", "record_id": "Guideline/__no_corpus__"}],
                "data_gaps": [],
            },
            "tool_results": [],
        }
    hits = await corpus.search(query, top_k=1)
    if not hits:
        # No retrieval hit — emit a citation that won't anchor; scorer fails.
        return {
            "response": {
                "prose": "no hit",
                "claims": [{"text": "no hit", "record_id": "Guideline/__no_hit__"}],
                "data_gaps": [],
            },
            "tool_results": [],
        }
    top = hits[0]
    rid = f"Guideline/{top.chunk_id}"
    return {
        "response": {
            "prose": f"Top hit: {top.section}",
            "claims": [{"text": top.section, "record_id": rid}],
            "data_gaps": [],
        },
        "tool_results": [
            {"tool": "search_guidelines", "data": [{"record_id": rid, "chunk_id": top.chunk_id}]}
        ],
        "_retrieval_top1_chunk_id": top.chunk_id,
    }


async def run_case(case: dict[str, Any], *, corpus: Any = None) -> dict[str, Any]:
    """Build a run_output dict to feed into the case's scorers."""
    if case["category"] == "retrieval":
        return await _run_retrieval(case, corpus)
    fixture = case.get("fixture") or {}
    return dict(fixture)


# ─────────────────────────────────────────────────────────────────────
# Scoring
# ─────────────────────────────────────────────────────────────────────


def score_case(case: dict[str, Any], run_output: dict[str, Any]) -> CaseRun:
    out = CaseRun(case_id=case["case_id"], category=case["category"])
    for scorer_name in case["scorers"]:
        scorer = SCORERS.get(scorer_name)
        if scorer is None:
            out.scorer_results.append(
                ScorerResult(
                    passed=False,
                    reason=f"unknown scorer {scorer_name!r}",
                    case_id=case["case_id"],
                )
            )
            continue
        # Retrieval cases use citation_present + an extra top1 check.
        if case["category"] == "retrieval" and scorer_name == "citation_present":
            expected = case.get("expected_top1_chunk_id")
            actual_top = run_output.get("_retrieval_top1_chunk_id")
            if expected and actual_top != expected:
                out.scorer_results.append(
                    ScorerResult(
                        passed=False,
                        reason=f"top1 mismatch: got {actual_top!r}, expected {expected!r}",
                        case_id=case["case_id"],
                    )
                )
                continue
        out.scorer_results.append(scorer(case, run_output))
    return out


# ─────────────────────────────────────────────────────────────────────
# Aggregation + threshold
# ─────────────────────────────────────────────────────────────────────


def pass_rates_by_category(runs: list[CaseRun]) -> dict[str, float]:
    rates: dict[str, float] = {}
    for cat in CATEGORIES:
        cat_runs = [r for r in runs if r.category == cat]
        if not cat_runs:
            rates[cat] = 1.0  # vacuously full marks (no cases yet)
            continue
        rates[cat] = sum(1 for r in cat_runs if r.passed) / len(cat_runs)
    return rates


def regression_check(
    rates: dict[str, float], baseline: dict[str, float], *, floor: float = 0.95, drop: float = 0.05
) -> list[str]:
    """Return a list of failure reasons (empty = no regression)."""
    failures: list[str] = []
    for cat in CATEGORIES:
        cur = rates.get(cat, 1.0)
        base = baseline.get(cat, 1.0)
        if cur < floor:
            failures.append(f"{cat}: {cur:.2%} < {floor:.0%} floor")
        elif cur < base - drop:
            failures.append(
                f"{cat}: {cur:.2%} dropped > {drop:.0%} from baseline {base:.2%}"
            )
    return failures


# ─────────────────────────────────────────────────────────────────────
# RESULTS.md writer
# ─────────────────────────────────────────────────────────────────────


def write_results_md(
    runs: list[CaseRun],
    rates: dict[str, float],
    baseline: dict[str, float],
    failures: list[str],
    *,
    path: Path = RESULTS_PATH,
) -> None:
    total = len(runs)
    total_pass = sum(1 for r in runs if r.passed)
    lines: list[str] = []
    lines.append("# W2 Eval Suite — RESULTS")
    lines.append("")
    lines.append(f"**Total:** {total_pass}/{total} cases passed")
    lines.append("")
    lines.append("## Per-category pass rates")
    lines.append("")
    lines.append("| Category | Pass rate | Baseline | Δ |")
    lines.append("|---|---|---|---|")
    for cat in CATEGORIES:
        cur = rates.get(cat, 1.0)
        base = baseline.get(cat, 1.0)
        delta = cur - base
        lines.append(f"| {cat} | {cur:.0%} | {base:.0%} | {delta:+.1%} |")
    lines.append("")
    if failures:
        lines.append("## Regression failures")
        lines.append("")
        for f in failures:
            lines.append(f"- {f}")
        lines.append("")
    else:
        lines.append("## Status")
        lines.append("")
        lines.append("✓ No regressions vs baseline.")
        lines.append("")
    lines.append("## Per-case detail")
    lines.append("")
    lines.append("| Case | Category | Status | Reason |")
    lines.append("|---|---|---|---|")
    for r in runs:
        status = "PASS" if r.passed else "FAIL"
        reasons = "; ".join(s.reason for s in r.scorer_results) if r.scorer_results else "(no scorers)"
        lines.append(f"| `{r.case_id}` | {r.category} | {status} | {reasons[:120]} |")
    lines.append("")
    path.write_text("\n".join(lines))


# ─────────────────────────────────────────────────────────────────────
# Baseline I/O
# ─────────────────────────────────────────────────────────────────────


def load_baseline(path: Path = BASELINE_PATH) -> dict[str, float]:
    if not path.exists():
        return {cat: 1.0 for cat in CATEGORIES}  # default: full marks
    return json.loads(path.read_text())


def save_baseline(rates: dict[str, float], path: Path = BASELINE_PATH) -> None:
    path.write_text(json.dumps(rates, indent=2, sort_keys=True))


# ─────────────────────────────────────────────────────────────────────
# CLI
# ─────────────────────────────────────────────────────────────────────


async def _amain(args: argparse.Namespace) -> int:
    corpus = None
    if args.with_corpus:
        # Lazy import to avoid asyncio overhead when not needed.
        from app.retrieval.corpus import GuidelineCorpus
        corpus = GuidelineCorpus(
            jsonl_path="corpus/guidelines.jsonl",
            sqlite_path="/tmp/eval-runner-corpus.sqlite",
        )
        await corpus.build()

    cases = load_cases(args.cases_root, fast=args.fast)
    runs: list[CaseRun] = []
    for case in cases:
        run_output = await run_case(case, corpus=corpus)
        runs.append(score_case(case, run_output))

    rates = pass_rates_by_category(runs)

    if args.regenerate_baseline:
        save_baseline(rates, args.baseline_path)
        print(f"Baseline regenerated: {dict((k, round(v, 4)) for k, v in rates.items())}")
        return 0

    baseline = load_baseline(args.baseline_path)
    failures = regression_check(rates, baseline)
    write_results_md(runs, rates, baseline, failures, path=args.results_path)

    total = len(runs)
    total_pass = sum(1 for r in runs if r.passed)
    print(f"{total_pass}/{total} cases passed")
    for cat in CATEGORIES:
        print(f"  {cat:14s} {rates[cat]:6.1%}   (baseline {baseline.get(cat, 1.0):.1%})")
    if failures:
        print("\nFAIL:")
        for f in failures:
            print(f"  - {f}")
        return 1
    print("\nOK — no regressions.")
    return 0


def main() -> int:
    parser = argparse.ArgumentParser(description="W2 eval gate runner.")
    parser.add_argument("--cases-root", type=Path, default=CASES_ROOT)
    parser.add_argument("--baseline-path", type=Path, default=BASELINE_PATH)
    parser.add_argument("--results-path", type=Path, default=RESULTS_PATH)
    parser.add_argument("--fast", action="store_true", help="Run only the FAST_SUBSET (pre-push hook).")
    parser.add_argument(
        "--regenerate-baseline",
        action="store_true",
        help="Run all cases and write current rates as the new baseline.json.",
    )
    parser.add_argument(
        "--with-corpus",
        action="store_true",
        help="Build the guideline corpus (required for retrieval-category cases).",
    )
    args = parser.parse_args()
    return asyncio.run(_amain(args))


if __name__ == "__main__":  # pragma: no cover
    sys.exit(main())

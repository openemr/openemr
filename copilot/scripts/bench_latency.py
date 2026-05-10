#!/usr/bin/env python3
"""Latency benchmark for the Clinical Co-Pilot deploy.

Hits a running Co-Pilot deployment (defaults to the Railway production
URL) with a curated workload covering the three primary use cases:

  * UC1 — pre-visit brief ("brief me on this patient")
  * UC2 — medication question ("what meds is the patient on")
  * UC3 — applied guideline ("should I screen for X")

For each turn it posts to ``/v1/chat`` and reads the per-turn
``TurnTrace`` from the response (``tool_latencies_ms`` + ``total_latency_ms``).
The script then computes p50 / p95 / mean per tool and per use case and
prints a markdown summary suitable for pasting into ``copilot/COST.md``.

Usage::

    python scripts/bench_latency.py \\
        --base-url https://copilot-production-b532.up.railway.app \\
        --patient-id a1a5a6d3-3edd-4341-9281-017568b3c36e \\
        --physician-user-id admin \\
        --runs-per-uc 5

Output is written to stdout AND, if ``--out FILE`` is given, to a file.

Cost: ~$0.02 / turn × runs_per_uc × 3 use cases. Defaults to 5 turns
per UC = 15 turns ≈ $0.30. Use ``--dry-run`` to inspect the workload
without hitting the API.
"""
from __future__ import annotations

import argparse
import json
import statistics
import sys
import time
from dataclasses import dataclass, field
from typing import Any
from urllib import error as urllib_error
from urllib import request as urllib_request

DEFAULT_BASE_URL = "https://copilot-production-b532.up.railway.app"
DEFAULT_PATIENT_ID = "a1a5a6d3-3edd-4341-9281-017568b3c36e"
DEFAULT_PHYSICIAN_USER_ID = "admin"
DEFAULT_RUNS_PER_UC = 5
DEFAULT_TIMEOUT_SECONDS = 120

# Curated workload — one prompt per use case, run N times to capture
# variance. Each is intentionally short so the agent's tool routing
# (not the question parsing) is what we're benchmarking.
WORKLOAD: dict[str, str] = {
    "UC1_brief": "Brief me on this patient.",
    "UC2_meds": "What medications is the patient currently on?",
    "UC3_applied_guideline": (
        "Given the patient's most recent labs, should I consider "
        "screening for type 2 diabetes?"
    ),
}


@dataclass
class TurnSample:
    """One captured /v1/chat turn."""

    use_case: str
    total_latency_ms: float
    tool_latencies_ms: dict[str, float]
    tools_called: list[str]
    routing_path: list[str]
    cache_hit_tools: list[str] = field(default_factory=list)


def _post_json(url: str, body: dict[str, Any], *, timeout: float) -> dict[str, Any]:
    data = json.dumps(body).encode("utf-8")
    req = urllib_request.Request(
        url,
        data=data,
        headers={"Content-Type": "application/json"},
        method="POST",
    )
    try:
        with urllib_request.urlopen(req, timeout=timeout) as resp:
            raw = resp.read()
    except urllib_error.HTTPError as e:
        body_text = e.read().decode("utf-8", errors="replace") if e.fp else ""
        raise RuntimeError(f"HTTP {e.code} from {url}: {body_text}") from e
    return json.loads(raw.decode("utf-8"))


def start_session(
    base_url: str,
    patient_id: str,
    physician_user_id: str,
    *,
    timeout: float,
) -> str:
    body = {"patient_id": patient_id, "physician_user_id": physician_user_id}
    out = _post_json(f"{base_url}/v1/sessions", body, timeout=timeout)
    sid = out.get("session_id")
    if not sid:
        raise RuntimeError(f"missing session_id in /v1/sessions response: {out}")
    return sid


def run_turn(
    base_url: str,
    session_id: str,
    question: str,
    *,
    timeout: float,
) -> tuple[float, dict[str, Any]]:
    """POST /v1/chat. Returns (wall_clock_ms, response_payload)."""
    body = {"session_id": session_id, "question": question}
    started = time.perf_counter()
    payload = _post_json(f"{base_url}/v1/chat", body, timeout=timeout)
    wall_ms = (time.perf_counter() - started) * 1000.0
    return wall_ms, payload


def collect(
    base_url: str,
    patient_id: str,
    physician_user_id: str,
    runs_per_uc: int,
    *,
    timeout: float,
    dry_run: bool,
) -> list[TurnSample]:
    if dry_run:
        print("# Dry-run plan", file=sys.stderr)
        for uc, q in WORKLOAD.items():
            print(f"  {uc}: {q!r} × {runs_per_uc}", file=sys.stderr)
        return []

    samples: list[TurnSample] = []
    for run_index in range(runs_per_uc):
        # Fresh session per run so the warm-cache curve we capture is
        # session-internal (turn 1 cold, turn 2+ warm) — averaging across
        # runs would otherwise smear cold-cache spikes evenly.
        session_id = start_session(
            base_url, patient_id, physician_user_id, timeout=timeout
        )
        print(
            f"\n--- run {run_index + 1}/{runs_per_uc} session={session_id[:8]} ---",
            file=sys.stderr,
        )
        for uc, question in WORKLOAD.items():
            try:
                wall_ms, payload = run_turn(
                    base_url, session_id, question, timeout=timeout
                )
            except Exception as e:  # noqa: BLE001 — bench, not prod
                print(f"  {uc}: ERROR {e}", file=sys.stderr)
                continue

            trace = payload.get("trace") or {}
            tool_latencies = trace.get("tool_latencies_ms") or {}
            sample = TurnSample(
                use_case=uc,
                total_latency_ms=float(
                    trace.get("total_latency_ms") or wall_ms
                ),
                tool_latencies_ms={
                    str(k): float(v) for k, v in tool_latencies.items()
                },
                tools_called=list(trace.get("tool_call_sequence") or []),
                routing_path=list(trace.get("routing_path") or []),
            )
            samples.append(sample)
            tool_summary = ", ".join(
                f"{t}={ms:.0f}ms" for t, ms in sample.tool_latencies_ms.items()
            )
            print(
                f"  {uc}: total={sample.total_latency_ms:.0f}ms "
                f"wall={wall_ms:.0f}ms tools=[{tool_summary}]",
                file=sys.stderr,
            )
    return samples


def _percentile(values: list[float], p: float) -> float:
    if not values:
        return 0.0
    if len(values) == 1:
        return values[0]
    # statistics.quantiles uses exclusive method by default; for small
    # samples that produces NaN at extremes. Inclusive matches numpy
    # default and reads the way most engineers expect.
    quantiles = statistics.quantiles(values, n=100, method="inclusive")
    # quantiles returns list of length n-1: [P1, P2, ..., P99]
    idx = max(0, min(98, int(round(p * 100)) - 1))
    return quantiles[idx]


def summarize(samples: list[TurnSample]) -> str:
    if not samples:
        return "_(no samples collected)_\n"

    # Per-UC totals
    by_uc: dict[str, list[float]] = {}
    for s in samples:
        by_uc.setdefault(s.use_case, []).append(s.total_latency_ms)

    # Per-tool aggregated across all use cases
    per_tool: dict[str, list[float]] = {}
    tool_calls: dict[str, int] = {}
    for s in samples:
        for tool, ms in s.tool_latencies_ms.items():
            per_tool.setdefault(tool, []).append(ms)
            tool_calls[tool] = tool_calls.get(tool, 0) + 1

    out: list[str] = []
    out.append("## End-to-end latency by use case\n")
    out.append("| Use case | n | p50 (ms) | p95 (ms) | mean (ms) |")
    out.append("|---|---|---|---|---|")
    for uc, lats in sorted(by_uc.items()):
        out.append(
            f"| {uc} | {len(lats)} | "
            f"{_percentile(lats, 0.50):.0f} | "
            f"{_percentile(lats, 0.95):.0f} | "
            f"{statistics.mean(lats):.0f} |"
        )

    out.append("\n## Per-tool latency (all turns)\n")
    out.append("| Tool | calls | p50 (ms) | p95 (ms) | mean (ms) |")
    out.append("|---|---|---|---|---|")
    # Sort by mean latency desc — bottlenecks at top
    tools_by_mean = sorted(
        per_tool.items(),
        key=lambda kv: statistics.mean(kv[1]),
        reverse=True,
    )
    for tool, lats in tools_by_mean:
        out.append(
            f"| `{tool}` | {tool_calls[tool]} | "
            f"{_percentile(lats, 0.50):.0f} | "
            f"{_percentile(lats, 0.95):.0f} | "
            f"{statistics.mean(lats):.0f} |"
        )

    # Routing path observation
    paths = {tuple(s.routing_path) for s in samples if s.routing_path}
    if paths:
        out.append("\n## Routing paths observed\n")
        for path in sorted(paths):
            count = sum(1 for s in samples if tuple(s.routing_path) == path)
            out.append(f"- `{' → '.join(path)}` ({count}×)")

    return "\n".join(out) + "\n"


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--base-url", default=DEFAULT_BASE_URL)
    parser.add_argument("--patient-id", default=DEFAULT_PATIENT_ID)
    parser.add_argument("--physician-user-id", default=DEFAULT_PHYSICIAN_USER_ID)
    parser.add_argument(
        "--runs-per-uc",
        type=int,
        default=DEFAULT_RUNS_PER_UC,
        help="Number of fresh-session runs per use case (default: 5).",
    )
    parser.add_argument(
        "--timeout",
        type=float,
        default=DEFAULT_TIMEOUT_SECONDS,
        help="HTTP timeout per request, seconds.",
    )
    parser.add_argument(
        "--out",
        type=str,
        default=None,
        help="Optional file to write the markdown summary.",
    )
    parser.add_argument("--dry-run", action="store_true")
    args = parser.parse_args()

    samples = collect(
        args.base_url,
        args.patient_id,
        args.physician_user_id,
        args.runs_per_uc,
        timeout=args.timeout,
        dry_run=args.dry_run,
    )
    if args.dry_run:
        return 0

    summary = summarize(samples)
    print(summary)
    if args.out:
        with open(args.out, "w", encoding="utf-8") as f:
            f.write(summary)
        print(f"\n(also written to {args.out})", file=sys.stderr)
    return 0 if samples else 1


if __name__ == "__main__":
    raise SystemExit(main())

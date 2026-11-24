# PR Shepherding — Phase 1: Stock-take

Return to the [hub](README.md).

**Purpose.** Produce a snapshot of every open PR matching the subject filter, classified
into one of the six buckets, with change-since-last-snapshot deltas called out. The
output is a workstream-doc section update + a shepherd-log index that drives Phase 2.

**Prerequisites.**

- The maintainer has named a subject (see [hub §2](README.md#2-the-subject-arg)).
- Standing rules read: [standing-rules.md](standing-rules.md).

**Deliverables.**

- Updated "Open as of <today>" subsection in the workstream doc.
- Shepherd log entries (one short line per PR) in the running shepherd log.
- Change-since-last-snapshot delta list: new PRs, promoted drafts, regressed CI,
  resurfaced-from-dormancy, closed, newly-shipped.

---

## 1. Scan step — list every candidate PR

Pull the full open-PR list, filter by subject, sort by `updatedAt desc`.

### Pattern

```bash
gh pr list --repo openemr/openemr --state open --limit 300 \
  --json number,title,updatedAt,isDraft,author,headRefName \
  --search "sort:updated-desc"
```

`--limit 300` is a safety margin — `gh pr list` returns at most `--limit` PRs and
silently truncates (oldest matches drop first). OpenEMR's open-PR count has been in
the 90–110 range during 2026-11; bump `--limit` or paginate via `gh api`
(`/repos/openemr/openemr/pulls?state=open&per_page=100&page=N`) if the count ever
crosses the limit — a truncated candidate list means an older matching PR silently
disappears from the snapshot.

Then apply the subject filter in post-processing. For a keyword subject, a case-insensitive
title regex is enough:

```python
pat = re.compile(r'fhir|ccda|cda|us[ -]?core|inferno|smart|oauth2|...', re.I)
hits = [p for p in prs if pat.search(p['title'])]
```

For an author subject, filter by `p['author']['login']`.

For a milestone/label subject, use `gh pr list ... --search "milestone:<name>"` or
`--label <name>` directly instead of post-filter.

### Scope pass — not every title match is in scope

Scan the title list and move anything that is title-matching-but-content-wrong into an
"outside primary scope" bucket for the snapshot. Example: a PR titled
"fix(payments): a visit closed past the levels on file is the patient's" matches the
`insurance` word in a FHIR keyword filter but is a billing-UI fix with no FHIR surface —
belongs outside primary scope.

Conversely: a PR titled "refactor(encounter): move encounter.inc.php lookups into
EncounterService" has no FHIR keyword in the title but touches FHIR Encounter emission
downstream — belongs *in* a FHIR scope pass.

The subject filter is advisory, not strict. Use judgment.

---

## 2. Metadata pass — classify each PR

For every PR in scope, pull the metadata needed for bucketing. One `gh pr view ... --json`
call per PR with the full set:

```bash
gh pr view <N> --repo openemr/openemr --json \
  title,isDraft,state,reviewDecision,mergeable,updatedAt,\
  statusCheckRollup,additions,deletions,files,author,commits,reviews
```

Collect the following signals per PR:

| Signal | Source | Use |
|---|---|---|
| `isDraft` | `view.isDraft` | Draft vs non-draft |
| `reviewDecision` | `view.reviewDecision` | `CHANGES_REQUESTED` → held |
| `mergeable` | `view.mergeable` | `CONFLICTING` → held |
| CI rollup | `statusCheckRollup` | Any `FAILURE` outside `codecov/*` and the `All Checks Passed` aggregator → held, unless the FAILURE matches the flake exception (single scope-unrelated or known-flaky lane; see per-pr-review §8). `codecov/*` are advisory; the `All Checks Passed` aggregator fails occasionally when every *other* underlying lane is SUCCESS or SKIPPED — underlying lanes are the primary health signal, aggregator is a required merge check cleared by healer / manual rerun / admin merge override (see per-pr-review §8) |
| CANCELLED lanes | `statusCheckRollup` | Investigate; see §3 |
| Author activity | `commits[-N:].authoredDate` | Dormant vs active |
| Last update | `updatedAt` | Freshness, dormancy check |
| Diff size | `additions / deletions / files` | Review-effort estimate |
| Rabbit state | `reviews` + `comments` | See [per-pr-review §4](per-pr-review.md#4-walk-rabbit-history) |

**CI rollup quick-classify:**

- All `SUCCESS` or `SKIPPED` (+ at most 1 `CANCELLED` that's title-lint-superseded) → `OK`
- Any `FAILURE` outside `codecov/*` and `All Checks Passed` → `FAIL`, **except** when it matches the flake exception in the next bullet. Both are advisory *for the health classification*: `codecov/patch` + `codecov/project` don't fail the aggregator (a solo codecov FAILURE with green aggregator is OK); and a solo `All Checks Passed` aggregator FAILURE (or aggregator + codecov/* only) with every *other* underlying lane SUCCESS or SKIPPED is also OK for health — treat the same as codecov-only. The aggregator is still a required merge check; see per-pr-review §8 for resolution paths (healer / manual rerun / admin merge override).
- A single `FAILURE` lane **scope-unrelated** to the PR's changed-file surface (test file not in the PR's changed files and not transitively reached) → classify as `FAIL?` pending. A known-flaky-on-master lane qualifies only when also scope-unrelated; a scope-related failure never qualifies as a flake candidate regardless of flakiness history. Place in ⏳ awaiting shepherd review with a "flake candidate, rerun pending" note (only when the other metadata gates pass — draft, `CHANGES_REQUESTED`, mergeable still rule); Phase 2 runs the rerun-based triage per per-pr-review §8. Rerun clears → mark the flake cleared, then reclassify the full CI rollup; treat as `OK` for the CI axis only when every lane satisfies the standard OK criteria. Advance to ✅ / 🔶 only if the full decision gate also passes, otherwise use the matching bucket. Rerun still red → promote to 🔴 held.
- Many `CANCELLED` + no `FAILURE` → `CRED` (concurrency-redeemed; usually in-flight
  re-run — recheck in 5-10m)
- Lanes `IN_PROGRESS` > 24 h on a non-dormant PR with aggregator NULL → `STALE` → 🔴
  held (CI isn't going to resolve itself; something upstream is stuck). **STALE takes
  precedence over CRED** when both match (many CANCELLED + no FAILURE + an IN_PROGRESS
  lane older than 24 h) — a stuck lane doesn't resolve by rechecking.

Store these as a one-line summary per PR; feed into the bucketing step.

---

## 3. Bucketing step — place each PR

Apply the decision gate from [hub §5](README.md#5-decision-gate--is-this-pr-merge-ready).
Every PR lands in exactly one bucket.

**Important: Phase 1 metadata alone cannot promote a PR to `✅ Merge-ready`.** Phase 1
reads what is visible in the GitHub API — draft state, CI rollup, review decision,
mergeable state. That tells you a PR is a *candidate* for Phase 2 review. It does not
tell you whether the code is actually ready to merge. A PR with green metadata can
hide a defect that only shows under a commit walk. Phase 1 therefore classifies PRs
as `⏳ Awaiting shepherd review` when metadata passes but no brief exists yet; the
promotion to `✅ Merge-ready` happens only after Phase 2 writes a brief with that
verdict.

### Classification buckets

#### ⏳ Awaiting shepherd review (Phase 1 default for candidates)

Every decision-gate metadata row passes (non-draft, CI green, no open maintainer
`CHANGES_REQUESTED`, mergeable). No brief yet, OR the brief's recorded head/base-tip
no longer matches (author pushed, or base branch advanced). Feeds Phase 2.

`FAIL?` flake candidates (see §2) enter this bucket **only when the other
metadata gates pass** (non-draft, no open maintainer `CHANGES_REQUESTED`,
mergeable); a draft / `CHANGES_REQUESTED` / conflicting PR with a `FAIL?` lane
stays in its existing bucket with the flake note recorded in that bucket's log
entry. When routed to ⏳, flag as "flake candidate, rerun pending" so Phase 2
can run the rerun triage — a cleared rerun marks the flake cleared, then
reclassifies the full CI rollup; CI is `OK` only when every lane satisfies
the standard OK criteria. Once the brief is written, advance to ✅ / 🔶 only
if the full decision gate also passes, otherwise use the matching bucket.
A still-red rerun promotes to 🔴 held.

#### ✅ Merge-ready

Phase 2 brief exists with verdict `**Approve + merge.**` and the current PR head
matches the brief's recorded reviewed head. This is a *Phase 2 output*, not a Phase 1
classification.

#### 🔶 Merge-ready with known residual

Decision gate passes but one non-blocking concern is recorded. Common residuals:

- Sibling-file pattern the PR correctly scopes out (→ `📏` sweep follow-up).
- Pre-existing bug the PR surfaces but doesn't own (→ `🧹` cleanup follow-up).
- Format/convention nit rabbit raised and author legitimately scoped out.

Produces a Phase 2 brief that calls out the residual explicitly.

#### 🔴 Held

One or more rows of the decision gate fails. Private brief states the exact blocker and
who owns it:

- Author (needs to address a rabbit Major / fix CI lane / respond to maintainer CR).
- Maintainer (needs to re-review to clear `CHANGES_REQUESTED`).
- CI (flaky lane; recheck).
- Dependency (waiting on a sibling PR to land first).

Does **not** produce a deep Phase 2 brief — just a shepherd log entry naming the blocker.

#### ⏸️ Author-declared draft

`isDraft == true`. Scan the metadata only; do not deep-review. The author has explicitly
said "not ready." A one-line log entry is enough.

If a draft has a long dormancy + a specific question in the body ("RFC: should this..."),
flag it for the maintainer so they can engage if they want.

#### Outside primary scope

Caught in the subject filter but on inspection not actually in subject-scope. Listed in
the snapshot for maintainer visibility with a one-line explanation of why it's out of
scope. No further action.

---

## 4. Delta step — what changed since the last snapshot

If a prior snapshot exists in the workstream doc, call out the differences:

- **Newly shipped** since last snapshot (with merge SHA + date).
- **Newly ready** — was draft, now non-draft.
- **Newly held** — was green, CI regressed, or author's `CHANGES_REQUESTED` replied to.
- **Resurfaced from dormancy** — was dormant 30+ days, now receiving commits.
- **Newly arrived** — opened since the last snapshot.
- **Closed** without merge — note why if visible.

The delta is often the most useful thing in the snapshot for the maintainer, because it
shows what moved. Put it at the top of the new "Open as of <date>" subsection.

---

## 5. Snapshot-doc output

Write to the workstream doc (see [deliverables.md §1](deliverables.md#1-workstream-doc-snapshot)
for the exact format). Shape:

```markdown
**Open as of <YYYY-MM-DD> (<count> PRs):**

Changes since the <prior-date> snapshot:
- 5 batch-N PRs landed (#NNNN, #NNNN, ...)
- #NNNN promoted from draft → ready
- #NNNN **unblocked** — author pushed N CI-fixing commits on <date>
- #NNNN **CI regressed** to FAIL on new <lane> workflow (was green before)
- NEW: #NNNN (<author> <scope summary>)
- Resurfaced from dormancy: #NNNN (<author> <scope>, last touched <date>)

✅ Shepherd-reviewed, merge-ready:
- #NNNN — <title> — <author>. <one-sentence verdict>. Brief: `pr-NNNN-review-brief.md`.

🔶 Shepherd-reviewed, merge-ready with known residual:
- ...

🔴 Held (author/CI blockers unaddressed):
- ...

⏳ Awaiting shepherd review (non-draft, CI green, net-new or newly-promoted):
- ...

⏸️ Author-declared draft (not shippable yet):
- ...

Outside primary scope (visibility only):
- ...
```

---

## 6. Shepherd-log index

For each PR touched in the pass — whether or not it got a Phase 2 brief — append or
update a one-paragraph entry in the running shepherd log (see
[deliverables.md §2](deliverables.md#2-shepherd-log-entry)). Minimum fields:

- PR number + title + author
- Bucket emoji
- Shipped SHA + date if merged
- Scope summary (one line)
- Link to brief if one exists

The shepherd log is the audit trail across passes. It is additive, not rewritten per pass.

---

## 7. Pass handoff

A stock-take pass hands off to Phase 2 with the **⏳ awaiting-review** list — the
PRs whose metadata passes but don't yet have a brief. ✅ / 🔶 / 🔴 / ⏸️ are Phase 2
outputs or already-classified and don't need re-review. Held / draft / out-of-scope
go back to the maintainer for direction. The hub's three-phase loop restarts only
after Phase 2 drains the ⏳ queue; don't start a fresh stock-take while Phase 2 still
has open briefs.

### Chain order over updated-desc

Phase 1 sorts by `updatedAt desc` by default. When the merge-ready list contains a
visible **chain** — several PRs touching the same services, or one landing after
another in a sibling-PR series (e.g. #15030 → #15042 → #15059 → #15038 all touching
`InsuranceService` and `FhirCoverageService`) — hand off Phase 2 in chain order
instead. Each brief then references the prior brief's lane context while it's still
warm in session memory. This matters most for bigger reviews: the parent session's
fourth brief in a chain writes faster because it has the first three to lean on.

A chain is recognizable by:

- Overlapping file lists across candidate PRs on the merge-ready list.
- One PR's description referencing another ("follows #15030," "carries
  #14925's two fixes").
- A single author staging a strategy across multiple PRs with a reference-branch fork.

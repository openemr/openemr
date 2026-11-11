# PR Shepherding

**Status:** Living document — the process a maintainer or agent follows when working a batch
of open PRs on a chosen *subject* through review and merge.
**Audience:** Any maintainer or coding agent (Claude Code, Codex, Cursor, Copilot, human)
assisting with PR triage and review on OpenEMR.
**Relationship to [`AGENTS.md`](../../AGENTS.md):** `AGENTS.md` carries the line-level coding
standard and the per-change decision gate. These documents are the layer above it: *how to
move a queue of open PRs forward*, not how to write the code inside one PR. When the two
overlap, `AGENTS.md` wins on mechanics; these documents own the workflow around the queue.

This file is the **hub**: the shared mental model, the subject-arg concept, the three-phase
loop, and a routing table to the detailed sub-documents. Read it first; open a sub-document
only for the phase you are in or the rule you are consulting.

---

## How these documents are organized (and why)

This is a set of small documents, not one large one, **deliberately — to fit AI-agent
context windows.** An AI agent generally loads a referenced document *whole*. Splitting
lets an agent load only the slice that matters for the phase it is in:

- **This hub** is small and cheap, and the thing an agent should reach for first. It carries
  the mental model, the subject-arg concept, the loop overview, and the routing table.
- **Each sub-document is self-contained** and covers one coherent phase or rule-set.
- **The routing table** below is the index: match your current phase (or the question
  you're about to answer) to one row, open that one file.

For humans this is a slight cost — no single-scroll read — so to read the whole process in
order: this hub → [standing-rules](standing-rules.md) → [stocktake](stocktake.md) →
[per-pr-review](per-pr-review.md) → [deliverables](deliverables.md).

---

## 1. When this process applies

Open this document when you are:

- Working a batch of open PRs on a chosen **subject** — a domain area (`fhir`, `oauth2`,
  `ccda`, `billing`, `referrals`), an author (`anun333`, `snagaraj07`), a label, a
  milestone, or a freeform filter.
- Triaging the state of a workstream for a maintainer who needs to know what is
  merge-ready, what is held, and why.
- Preparing a release — producing the per-PR verdicts that decide what ships.

Do **not** open this document when you are writing a PR of your own (consult
[`AGENTS.md`](../../AGENTS.md) and [`docs/architecture/`](../architecture/)).

**Reviewing a single PR outside a batch** skips Phase 1: read
[standing-rules.md](standing-rules.md), then run [per-pr-review.md](per-pr-review.md) on
that PR and save the brief. The decision gate (§5) applies as written. In place of the
Phase 1 output that per-pr-review expects, write the PR's one-line scope summary yourself
and check the gate's metadata rows first (draft state, CI, mergeable, review decision);
if one fails, the PR is held and gets a note naming the blocker, not a deep review.

The workstream doc and shepherd log are optional for a one-off review. A non-blocking
residual still needs a follow-up register entry: add it to the area's workstream doc when
one exists; otherwise the brief's "Follow-ups to track" section is the record, and the
maintainer decides whether it becomes an issue.

---

## 2. The subject arg

Every shepherd pass starts with a **subject** — one argument that defines "which open PRs
are in scope." Examples:

| Invocation shape | What it means |
|---|---|
| `fhir` | FHIR / FHIR-adjacent (CCDA, QRDA, US Core, SMART, Inferno-touching) |
| `oauth2` | OAuth2 / OIDC / token / session-cookie flows |
| `anun333` | All open PRs by one author |
| `release-rel-860` | PRs milestoned to a specific release |
| `"ccda OR cda"` | Freeform keyword filter |

The subject answers "which PRs do I classify and report on." Everything else in the process
— the three-phase loop, the standing rules, the deliverables — is identical regardless of
subject.

The subject is **advisory, not strict.** A PR that mentions "FHIR" in the title but is
really a billing-UI fix belongs in an "outside primary scope" bucket; a PR titled
"refactor(encounter)" that changes FHIR Encounter emission belongs *in* a `fhir` sweep.
The agent picks the bucket the PR actually belongs in, not the one the title grep landed
it in.

---

## 3. The three-phase loop

```
    ┌───────────────────────┐
    │ Phase 1: Stock-take   │  (Scan + classify every open PR in scope.
    │                       │   Deliverable: a snapshot entry in the
    │                       │   workstream doc + the shepherd log index.)
    └───────────┬───────────┘
                │
                ▼
    ┌───────────────────────┐
    │ Phase 2: Per-PR deep  │  (Per merge-ready PR: walk commits, check
    │ review                │   rabbit history, verify claims, write a
    │                       │   private brief. Deliverable: one brief
    │                       │   file per PR in the briefs directory.)
    └───────────┬───────────┘
                │
                ▼
    ┌───────────────────────┐
    │ Phase 3: Deliverables │  (Update the workstream doc + shepherd log
    │ sync                  │   + follow-up register. Save a memory
    │                       │   entry for anything pattern-worthy.)
    └───────────────────────┘
                │
                ▼
             repeat
```

- **Phase 1 runs once per pass.** Its deliverable is a snapshot, not an action.
  Details: [stocktake.md](stocktake.md).
- **Phase 2 runs once per merge-ready PR in the pass.** Its deliverable is a private
  brief with a verdict and an evidence chain. Details:
  [per-pr-review.md](per-pr-review.md).
- **Phase 3 runs after each PR's verdict is in** and after each merge the maintainer
  confirms. Details: [deliverables.md](deliverables.md).

**Between passes**, keep the running shepherd log + follow-up register up to date as the
maintainer reports `landed <PR#>`. Update the brief + log entry with the merge SHA + a
"SHIPPED" marker.

---

## 4. Routing table — match your task to one document

| If you are about to… | Read |
|---|---|
| Scan the queue + classify every open PR | [stocktake.md](stocktake.md) |
| Deep-review one merge-ready PR | [per-pr-review.md](per-pr-review.md) |
| Review a single PR outside a batch | [standing-rules.md](standing-rules.md), then [per-pr-review.md](per-pr-review.md) |
| Review a batch of 3+ independent PRs via parallel subagents | [per-pr-review.md §13](per-pr-review.md#13-parallel-subagents-for-batch-reviews) |
| Verify two PRs touching the same files really merge cleanly (overlapping-touch) | [per-pr-review.md §6.6](per-pr-review.md#66-probe-an-unknown-mergeable-state) |
| Just merged a PR — re-probe surviving ✅ queue for landing-cascade base-advance effects | [per-pr-review.md §12](per-pr-review.md#12-handoff-to-phase-3) |
| Write a review brief, log entry, or follow-up register entry | [deliverables.md](deliverables.md) |
| Post-verdict state shift (head moved, maintainer inline comment, base advance, new rabbit finding) — maintain the brief | [deliverables.md §3.12](deliverables.md#312-state-delta-header-brief-maintenance-across-post-verdict-shifts) |
| Decide whether an action (public comment, branch push, direct code change) is allowed | [standing-rules.md](standing-rules.md) |
| Interpret a confusing CodeRabbit interaction (incremental review, "already reviewed," paused reviews, typo'd mention) | [standing-rules.md](standing-rules.md#2-coderabbit-mechanics) |
| Resolve a stale hold after 24h | [standing-rules.md](standing-rules.md#3-staleness-and-recheck-cadence) |

---

## 5. Decision gate — "is this PR merge-ready?"

Before producing a `✅ merge-ready` verdict, every row below must be true. If any row is
false, the PR goes into the matching held / residual / draft bucket instead.

- **Non-draft.** The PR is not in GitHub Draft state.
- **CI green.** All checks either SUCCESS or SKIPPED. CANCELLED is acceptable only if the
  cancellation is a title-lint-superseded-by-next-commit case; note it explicitly.
  `codecov/patch` and `codecov/project` are advisory status checks — they don't fail the
  "All Checks Passed" aggregator, so a solo codecov FAILURE with green aggregator is OK.
  The `All Checks Passed` aggregator itself occasionally shows FAILURE when every other
  underlying lane is SUCCESS or SKIPPED (a dependent check didn't register SUCCESS but
  no real lane failed). The PR's *health signal* is still green — underlying lanes are
  primary — but the aggregator is a required merge check, so merge needs it cleared.
  Three resolution paths, in preferred order: the healer workflow
  (`.github/workflows/rerun-stale-all-checks.yml`) reruns the aggregator
  automatically after a heavy-workflow rerun, and may leave it red for any
  mechanical reason (cap reached, out-of-scope trigger, in-progress sibling,
  etc. — specifics live in the workflow file); a **maintainer manually reruns**
  the aggregator job via the GitHub UI's "Re-run jobs" button when the healer
  leaves it red; an **admin uses merge override** when the aggregator is
  genuinely stuck despite clean underlying lanes. The shepherd *observes* and
  names the expected path in the brief — the shepherd doesn't trigger any of
  these itself.
- **No unresolved rabbit Actionable.** Every CodeRabbit "Actionable" finding needs its
  own finding-specific evidence of resolution: addressed in a subsequent commit
  (confirmed by rabbit's auto-ack **or** by the shepherd's diff inspection against the
  raised line), withdrawn by rabbit, or scoped out by the author with rabbit's
  acknowledgement (also acceptable: the shepherd documents the scope-out based on
  diff inspection when the author doesn't reply inline — see
  [per-pr-review §4](per-pr-review.md#4-walk-rabbit-history) step 4). Rabbit's
  "already reviewed the last commit" no-op response does **not** confirm earlier
  findings are fixed — see
  [standing-rules.md](standing-rules.md#2-coderabbit-mechanics) §2.1. A thread's
  resolved state does **not** mean nothing is open in it: rabbit resolves threads
  itself and can raise a new finding in the same reply, so this row is checked
  against the full text of every rabbit reply in every thread, resolved ones included,
  with each finding tracked on its own (a later no-op reply does not close an earlier
  one) — see
  [standing-rules §2.7](standing-rules.md#27-a-resolved-thread-can-carry-a-new-finding).
- **No unresolved maintainer `CHANGES_REQUESTED`.** A formal review-state request stays
  open until the requesting maintainer re-reviews. A separate maintainer approving does
  not clear it.
- **Mergeable state.** GitHub's `mergeable` is `MERGEABLE`. `UNKNOWN` does not count as
  mergeable (GitHub hasn't finished computing it, or a conflict exists that doesn't show
  as markers). Either poll until the state refreshes, or run the local merge-compatibility
  verify ([per-pr-review §6.6](per-pr-review.md#66-probe-an-unknown-mergeable-state)).
- **Baseline integrity.** PHPStan baseline: zero new entries. Count reductions are fine
  (they indicate source-level fixes). Verify against the **actual merge base**, not your
  local `upstream/master` (which may have drifted).
- **No private-submodule dependency is dangling.** If the PR changes CCDA/QRDA/FHIR
  emission, note whether Inferno's private-submodule fixture will need admin updating
  post-merge. See [standing-rules.md](standing-rules.md#6-inferno-ccda-fixture-in-private-submodule).
- **Reviewed head recorded.** The brief records the exact head SHA it was written
  against. Before merging on a shepherd verdict, confirm the current PR head still
  matches that SHA — if the author pushed after the brief was written, the verdict
  applies to different code and a fresh review is needed.

Scope is not a gate row. A PR the subject filter caught but that is not really in scope
is listed under "Outside primary scope" at stock-take
([stocktake.md §3](stocktake.md#classification-buckets)) whatever its merge state, and
gets no verdict in this pass.

If a PR *passes every row* but surfaces a non-blocking concern (coverage gap, sibling-file
sweep, pre-existing bug the PR correctly scopes out), place it in **`🔶 merge-ready with
known residual`** and log the residual in the follow-up register.

---

## 6. Classification buckets (summary)

Full definitions live in [stocktake.md](stocktake.md#classification-buckets). Short form:

- **⏳ Awaiting shepherd review.** Phase 1 metadata passes; no Phase 2 brief yet.
- **✅ Merge-ready.** Phase 2 brief written with approve verdict; decision gate passes.
- **🔶 Merge-ready with known residual.** As ✅ but one non-blocking concern is recorded.
- **🔴 Held.** One or more decision-gate rows fail. Brief names the blocker and owner.
- **⏸️ Draft.** Author-declared draft. Scan metadata only; no deep review.
- **Outside primary scope.** Caught in the subject filter but not actually in scope.

---

## 7. Deliverables (summary)

Full formats live in [deliverables.md](deliverables.md). Every pass produces at least:

- **Workstream doc update** — snapshot of shipped-since / open-as-of, by bucket.
- **Per-PR review brief** (one file per merge-ready PR) — in the briefs directory.
- **Shepherd log entry** (one entry per PR touched in the pass) — in the running log.
- **Follow-up register entries** (per non-blocking concern) — in the workstream doc's
  register section.

Pattern-worthy observations (recurring gotchas, rule corrections, new conventions) go to
**memory** so the next shepherd pass starts with the context already loaded. See
[standing-rules.md](standing-rules.md#8-memory-vs-session).

---

## 8. Anti-patterns to avoid

- **Classify-without-evidence.** "Looks green, merge-ready" without walking the rabbit
  history or verifying the fix. Evidence chains are the point of the brief.
- **Block a PR for a sibling-file pattern it didn't introduce.** A cross-cutting concern
  across N files is a sweep follow-up, not a block on the first PR that touches one of
  them. See [standing-rules.md](standing-rules.md#4-sibling-pattern-coverage-sweep-not-block).
- **Trust rabbit's claim without checking.** Rabbit occasionally cites a raw-github SHA
  that doesn't match any PR commit; verify the finding against current code before
  propagating it into the brief. See
  [standing-rules.md](standing-rules.md#2-coderabbit-mechanics).
- **Carry a stale hold.** A 🔴 hold captured at the morning snapshot can be addressed by
  afternoon on an active contributor's PR. Re-check 24h later. See
  [standing-rules.md](standing-rules.md#3-staleness-and-recheck-cadence).
- **Act outside the standing rules.** No public PR comments without per-post approval; no
  direct pushes to contributor branches; no rebases of contributor PRs without explicit
  per-PR approval. See [standing-rules.md](standing-rules.md#1-hard-rules).

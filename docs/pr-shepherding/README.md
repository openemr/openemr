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
[`AGENTS.md`](../../AGENTS.md) and [`docs/architecture/`](../architecture/)) or when you
are reviewing a single PR unrelated to a batch (just read the PR).

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
| Write a review brief, log entry, or follow-up register entry | [deliverables.md](deliverables.md) |
| Decide whether an action (public comment, branch push, direct code change) is allowed | [standing-rules.md](standing-rules.md) |
| Interpret a confusing CodeRabbit interaction (incremental review, "already reviewed," paused reviews, typo'd mention) | [standing-rules.md](standing-rules.md#coderabbit-mechanics) |
| Resolve a stale hold after 24h | [standing-rules.md](standing-rules.md#staleness-and-recheck-cadence) |

---

## 5. Decision gate — "is this PR merge-ready?"

Before producing a `✅ merge-ready` verdict, every row below must be true. If any row is
false, the PR goes into the matching held / residual / draft bucket instead.

- **Non-draft.** The PR is not in GitHub Draft state.
- **CI green.** All checks either SUCCESS or SKIPPED. CANCELLED is acceptable only if the
  cancellation is a title-lint-superseded-by-next-commit case; note it explicitly.
- **No unresolved rabbit Actionable.** Every CodeRabbit "Actionable" finding is either
  addressed in a subsequent commit, withdrawn by rabbit, or scoped out by the author with
  rabbit's acknowledgement. "Already-reviewed, no new findings" counts as resolved; see
  [standing-rules.md](standing-rules.md#coderabbit-mechanics).
- **No unresolved maintainer `CHANGES_REQUESTED`.** A formal review-state request stays
  open until the requesting maintainer re-reviews. A separate maintainer approving does
  not clear it.
- **Mergeable state.** GitHub's `mergeable` is `MERGEABLE` (or `UNKNOWN` with no visible
  conflict markers).
- **Scope confirmed in scope.** The PR falls in the subject-filter bucket, not "outside
  primary scope."
- **Baseline integrity.** PHPStan baseline: zero new entries. Count reductions are fine
  (they indicate source-level fixes). Verify against the **actual merge base**, not your
  local `upstream/master` (which may have drifted).
- **No private-submodule dependency is dangling.** If the PR changes CCDA/QRDA/FHIR
  emission, note whether Inferno's private-submodule fixture will need admin updating
  post-merge. See [standing-rules.md](standing-rules.md#inferno-ccda-fixture-in-private-submodule).

If a PR *passes every row* but surfaces a non-blocking concern (coverage gap, sibling-file
sweep, pre-existing bug the PR correctly scopes out), place it in **`🔶 merge-ready with
known residual`** and log the residual in the follow-up register.

---

## 6. Classification buckets (summary)

Full definitions live in [stocktake.md](stocktake.md#classification-buckets). Short form:

- **✅ Merge-ready.** Decision gate passes; approve + merge.
- **🔶 Merge-ready with known residual.** Decision gate passes but a non-blocking concern is
  recorded in the follow-up register.
- **🔴 Held.** One or more rows of the decision gate fail. Private brief notes the exact
  blocker and who owns it (author, maintainer, CI, dependency).
- **⏸️ Draft.** Author-declared draft. Scan metadata only; no deep review.
- **Outside primary scope.** Caught in the subject filter but on inspection not actually
  in scope. Listed for maintainer visibility, not for shepherd action.

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
[standing-rules.md](standing-rules.md#memory-vs-session).

---

## 8. Anti-patterns to avoid

- **Classify-without-evidence.** "Looks green, merge-ready" without walking the rabbit
  history or verifying the fix. Evidence chains are the point of the brief.
- **Block a PR for a sibling-file pattern it didn't introduce.** A cross-cutting concern
  across N files is a sweep follow-up, not a block on the first PR that touches one of
  them. See [standing-rules.md](standing-rules.md#sibling-pattern-coverage-sweep-not-block).
- **Trust rabbit's claim without checking.** Rabbit occasionally cites a raw-github SHA
  that doesn't match any PR commit; verify the finding against current code before
  propagating it into the brief. See
  [standing-rules.md](standing-rules.md#coderabbit-mechanics).
- **Carry a stale hold.** A 🔴 hold captured at the morning snapshot can be addressed by
  afternoon on an active contributor's PR. Re-check 24h later. See
  [standing-rules.md](standing-rules.md#staleness-and-recheck-cadence).
- **Act outside the standing rules.** No public PR comments without per-post approval; no
  direct pushes to contributor branches; no rebases of contributor PRs without explicit
  per-PR approval. See [standing-rules.md](standing-rules.md#hard-rules).

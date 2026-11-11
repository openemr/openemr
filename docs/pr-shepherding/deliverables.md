# PR Shepherding — Deliverables & Templates

Return to the [hub](README.md).

Every shepherd pass produces four kinds of durable output: a workstream-doc snapshot, a
shepherd-log index, one brief per deep-reviewed PR, and follow-up register entries. This
file is the format reference for each.

---

## 1. Workstream doc snapshot

### 1.1 Where it lives

The workstream doc is whatever audit / coverage / tracking doc already exists for the
subject area. If none exists, create one at `docs/<subject>-workstream.md` — a flat
markdown file with sections for shipped, open, and follow-up register.

Precedent: the OAuth2/FHIR/API test-coverage audit uses
`docs/oauth2-fhir-api-test-coverage.md`, with a dedicated `FHIR workstream status`
subsection updated per shepherd pass.

### 1.2 Section shape

```markdown
#### <Subject> workstream status (<YYYY-MM-DD>)

<Optional infrastructure note — e.g. "Inferno CI red since <date>; tracked as issue #N.">

**Shipped since <reference-date> (<count> <subject> PRs merged):**

- #NNNN — <commit-type>(<scope>): <title> — <author> (<merge-date> at `<merge-sha-short>`).
  <One-sentence summary>. Brief: `pr-NNNN-review-brief.md`. **🚢 SHIPPED.**
- ...

**Open as of <YYYY-MM-DD> (<count> PRs):**

Changes since the <prior-date> snapshot:
- <delta bullets — see stocktake.md §4>

✅ Shepherd-reviewed, merge-ready:
- #NNNN — <title> — <author>. <one-sentence verdict>. Brief: `pr-NNNN-review-brief.md`.

🔶 Shepherd-reviewed, merge-ready with known residual:
- #NNNN — ... <reason for residual>. Brief: ...

🔴 Held (author/CI blockers unaddressed):
- #NNNN — ... <blocker + owner>.

⏳ Awaiting shepherd review (non-draft, CI green, net-new or newly-promoted):
- #NNNN — ... <scope summary>.

⏸️ Author-declared draft (not shippable yet):
- #NNNN — ... <scope summary>.

Outside primary scope (visibility only):
- #NNNN — ... <why out of scope>.
```

**Point at a brief by file name only** (`pr-NNNN-review-brief.md`), never by absolute
path. The workstream doc is committed and public; the brief is private and its directory
differs per machine ([§5](#5-filesystem-locations)), so a full path helps no other reader
and publishes a local layout. The shepherd log is not committed and can carry the full path.

### 1.3 Updating the doc as PRs land

When the maintainer confirms a merge (`landed <N>`):

1. Pull the merge SHA with `gh pr view <N> --json mergeCommit`.
2. **Add** an entry to the `**Shipped since ...**` list — do not delete or edit the PR's
   entry in the dated `**Open as of ...**` snapshot. The snapshot is a point-in-time
   record (header says "N PRs" based on the bullets at that date); rewriting it after
   a merge decorrelates the count from the bullets and destroys history useful for
   release-note narrative and dormancy audits.
3. On the `**Shipped since ...**` entry, include `(<merge-date> at \`<sha-short>\`)` and
   `**🚢 SHIPPED.**`, and preserve the brief path pointer.
4. Commit with `docs(<area>): add #NNNN to shipped list in <subject> workstream`.

**Dated snapshots have a half-life.** Add a fresh "Open as of" on each shepherd pass;
let the prior snapshot age unchanged. See
[standing-rules §3.3](standing-rules.md#3-staleness-and-recheck-cadence).

### 1.4 Follow-up register

Within the workstream doc, a dedicated section captures the running tally of
non-blocking concerns surfaced by shepherd reviews:

```markdown
**Follow-up issues surfaced during the shepherding pass (running tally, started <date>):**

Latent bugs / cleanup / coverage gaps surfaced BY reviewing someone else's PR — things
we'd file as issues or small follow-up PRs ourselves rather than block the current PR on.
Keep entries short.

Legend: 🪲 test-infrastructure bug · 🧹 cleanup / pattern alignment · 📏 coverage gap

- <emoji> **<Short name>** (surfaced via #NNNN review <date>). <one-paragraph summary>.
  <optional fix pointer>.
```

The register is additive. Don't delete entries on their own; either:

- Promote to an issue + remove the register entry with a note pointing at the issue.
- Mark as closed with the resolution SHA, keep the entry for audit.

---

## 2. Shepherd log entry

### 2.1 Where it lives

A single running file at `<git-parent>/<subject>-pr-shepherd-log.md` (default location;
see [§5 Filesystem locations](#5-filesystem-locations)).

### 2.2 Format

```markdown
# <Subject> PR shepherd log

Starting <date>. Goal: review + verify tests on each open <subject> PR; land the
merge-ready ones; raise concerns on the rest via private brief.

Standing rules:
- No public PR comments without maintainer's per-post approval
- No direct pushes to contributor branches
- Private review briefs to scratch files; maintainer decides engagement

## Legend

Same emojis and meanings as standing-rules §9 (`docs/pr-shepherding/standing-rules.md` in the repo):

- ⏳ **Awaiting shepherd review** — metadata passes; no brief yet, or the brief's reviewed head or base tip no longer matches
- ✅ **Merge-ready** — brief with approve verdict; decision gate passes
- 🔶 **Merge-ready with known residual** — as ✅; the brief captures one non-blocking concern
- 🔴 **Held** — a decision-gate row fails; the entry names the blocker and its owner
- ⏸️ **Author-declared draft** — scan only; no deep review
- 🚢 **Shipped** — appended once the maintainer confirms the merge

A PR that is outside primary scope, or that the maintainer deferred, gets no emoji: say
so in its verdict line.

## Review outcomes

### #NNNN — <title> — <author> <emoji> [🚢 MERGED <date> at `<sha>`]

**Scope:** <size>, <N commits/files>. <one-sentence description>.

**Deep review brief:** `/path/to/pr-NNNN-review-brief.md`.

**Key findings:**
- <bullet per substantive check>
- <...>

**Verdict:** <one-line>.
```

### 2.3 Updating on merge

Append ` 🚢 MERGED <date> at \`<sha-short>\`` to the entry's header. Keep the body as
historical record — don't rewrite the verdict.

---

## 3. Review brief template

### 3.1 Where it lives

`<briefs-dir>/pr-<N>-review-brief.md` (default `<git-parent>/`; see
[§5 Filesystem locations](#5-filesystem-locations)).

### 3.2 Header

```markdown
# PR #<N> deep review brief — `<title>`

**Author:** <login> · <N> commits, +<adds>/-<dels> across <N> files · base: `<base-branch>` (merge base `<short-sha>`)
**Reviewed head:** `<head-sha>` — **re-verify before merging** via `gh pr view <N> --json headRefOid --jq .headRefOid`; a push after this brief invalidates the verdict until a delta review covers the new commits.
**Reviewed base-tip:** `<base-tip-sha>` — if the base branch advances before merge, re-run §6.6 against the new tip.
**CI:** <summary — "all green" or "N SUCCESS, M SKIPPED (<reasons>)">.
**Prior maintainer action:** <e.g. "kojiromike filed CHANGES_REQUESTED 2026-11-05; addressed by commits X + Y">.
**Previous status:** <prior bucket if applicable — e.g. "🔴 held at 2026-11-04 end-of-day; promoted to ✅ on ...">.
```

The `Reviewed head` field binds the verdict to the specific commit SHA the brief was
written against. Standard shepherd-log entries also carry this SHA so the maintainer
can confirm "the verdict applies to this exact head" before merging. See
[per-pr-review §12](per-pr-review.md#12-handoff-to-phase-3) for the pre-merge
re-verification checklist.

### 3.3 Commit walk

For multi-commit PRs:

```markdown
## Commits walked

| Commit | Change | Correctness | Test coverage |
|---|---|---|---|
| `abc123def4` | Primary fix for X | Correct per <reason> | Locked by `TestClass::testMethod` |
| `def456abc0` | Response to rabbit Major Y | Correct — the X guard prevents the overwrite | New test case `testMethodZ` |
| ... | | | |
```

### 3.4 TL;DR verdict

```markdown
## TL;DR verdict

**Approve + merge.** <One paragraph. Why the verdict. What the PR actually does. What's
not in scope but surfaces as follow-up.>
```

Verdict phrasings:

- `**Approve + merge.**` — ✅ merge-ready.
- `**Approve + merge with residual.**` — 🔶 merge-ready-with-residual.
- `**Hold.**` — 🔴 held; brief body states the blocker and owner.

### 3.5 Primary fix shape

```markdown
## Primary bug + fix

**The bug:** <description with code reference>.

**The fix:** <the actual change, shape over content — don't duplicate the PR diff, explain
*why* it's the right shape for the bug>.
```

For feature PRs, replace with `## Feature scope`:

```markdown
## Feature scope

**What it does:** <description>.

**Why this shape:** <architectural rationale — e.g. "new rule-based service hoists the
precedence logic out of N callers">.

**What it doesn't do:** <explicit non-goals>.
```

### 3.6 Test coverage

```markdown
## Test coverage

| File | Tier | Lines | Purpose |
|---|---|---|---|
| `tests/.../XxxTest.php` | Isolated | 93 | Unit test for the pure-PHP branch |
| `tests/.../YyyApiTest.php` | API | 354 | HTTP contract regression gates |

- All data providers carry `@codeCoverageIgnore` per CLAUDE.md. [confirmed / not confirmed]
- Coverage gap: <any noted>.
```

### 3.7 Rabbit coverage assessment

One subsection per finding:

```markdown
## Rabbit coverage assessment

**Finding 1 (<severity>, line <N>):** "<rabbit's quoted concern>."
- Raised: <date> in commit `<sha>`.
- Addressed: commit `<sha>` — <specific change>.
- Confirmed: rabbit <date>: "<quoted acknowledgement>."

**Finding 2 (<severity>, line <N>):** "..."
- Raised: ...
- Scoped out: <author's rebuttal>.
- Rabbit accepted: "..."
- Follow-up register: <link to register entry>.
```

If zero findings, say so explicitly: `**No rabbit findings.** <N> review passes, all clean.`

### 3.8 Baseline cleanup

```markdown
## Baseline (+<a> / -<b>, net <signed>)

Against merge base `<short-sha>`:
- `<baseline-file>`: <summary of changes>

**Zero new baseline entries.** All changes are count reductions or full-entry deletions,
indicating source-level fixes.
```

### 3.9 CI state

```markdown
## CI state

- <N> SUCCESS, <N> SKIPPED (<reasons>).
- <Any non-green explained — e.g. "1 CANCELLED on title-lint-superseded; next commit's
  lint passed">.
- `mergeable=<value>`.
- Codecov patch <%> (<optional note>).
```

### 3.10 Follow-ups to track

```markdown
## Follow-ups to track

- <emoji> **<Short name>** — <description with enough detail to recreate the finding>.
- ...
```

### 3.11 Shepherd recommendation

```markdown
## Shepherd recommendation

**<Approve + merge | Hold on X | ...>.** <One-sentence why, suitable for the maintainer's
decision at a glance>.
```

### 3.12 STATE DELTA header (brief maintenance across post-verdict shifts)

When post-verdict state shifts — whether the verdict survives (maintainer inline
comment resolved by re-read, base advance that re-clears the merge-compatibility
([per-pr-review §6.6](per-pr-review.md#66-probe-an-unknown-mergeable-state)) and
baseline-integrity ([per-pr-review §7](per-pr-review.md#7-check-baseline-integrity))
checks, post-resume rabbit finding scoped out) or the verdict changes (✅→🔴 from a
maintainer-spotted gap, 🔴→✅ after a block is cleared) — prepend a dated delta
header to the brief rather than rewriting the body. Preserves the
verified-at-head-X evidence trail while making the current state scannable.

```markdown
## STATE DELTA — <YYYY-MM-DD>

**Current verdict:** <bucket emoji + new verdict if changed, or "unchanged ✅/🔶/🔴" if preserved>.
**Observed head:** `<new-head-sha>` (set when head moved; mark "re-verified" once
checks run against it).
**Observed base-tip:** `<new-base-tip-sha>` (same convention).

<One-paragraph note: what changed, what the brief body no longer reflects, which
sections need re-verification before the verdict applies again. End with the
re-verification criteria for the delta.>

---

<original brief body below, unchanged>
```

Record the observed head + base-tip SHAs whenever either moved, even on an
"unchanged verdict" delta — without them, a reader can't tell which revisions
are under re-verification. Promote to "re-verified" once the checks finish
against the observed SHAs; those SHAs then supersede the §3.2 header.

**Delta authority.** The delta is authoritative for any fact it records as changed —
re-verifying one finding doesn't make an unrelated stale statement in the brief body
accurate again. When successive deltas accumulate (verdict changes twice), the newest
delta supersedes earlier deltas for the specific facts it updates; earlier deltas
remain authoritative for unrelated facts they recorded. The brief body returns to
fully authoritative only when it's updated to reflect the changed facts while
preserving the original evidence (e.g. "Updated 2026-11-09: X finding now addressed,
see commit Y; original pre-fix evidence in the delta above").

Pattern observed across multiple shepherd passes (4+ uses in one pass alone): used when
a maintainer inline comment flips ✅→🔴, when a base advance introduces a baseline
violation the brief predates, when a post-unpause rabbit review adds a Minor, or when
re-reading under a mid-pass standing-rule change surfaces a reclassification.

---

## 4. Follow-up register entry (format)

Added directly to the workstream doc's register section (see §1.4). Format per entry:

```markdown
- <emoji> **<Short imperative name>** (surfaced via #NNNN review <date>). <One-paragraph
  description: what the issue is, why it's out of scope for the current PR, where the fix
  would go, what tier of test would lock it>.
```

Keep entries short — 2–4 sentences. If an entry needs more depth, promote to an issue
and replace the register entry with a one-line pointer.

---

## 5. Filesystem locations

Defaults for a shepherd pass. Override in the workstream doc's header if a different
scheme is in use.

| Artifact | Default path |
|---|---|
| Workstream doc | `docs/<subject>-workstream.md` or the existing audit doc for the area |
| Shepherd log | `<git-parent>/<subject>-pr-shepherd-log.md` |
| Review briefs | `<git-parent>/pr-<N>-review-brief.md` |
| Scratch artifacts (batch reports, parallel-agent outputs) | `<git-parent>/<subject>-scratch/` |

`<git-parent>` is the parent directory of the openemr repo clone (e.g. `/home/brady2/git/`),
not the repo itself. This keeps shepherd artifacts out of the repo tree.

For a maintainer using Claude Code specifically, memory lives at
`~/.claude/projects/<slug>/memory/` and is pattern-memory only (see
[standing-rules §8](standing-rules.md#8-memory-vs-session)). For other agents, use
whatever persistent-memory mechanism that agent provides.

---

## 6. When to commit the workstream doc update

- After each merge-ready verdict (so the maintainer can read the doc + brief together).
- After each `landed <N>` from the maintainer (move entry to shipped).
- After adding a follow-up register entry.

These commits go on a branch and reach `master` through a PR like any other change;
never commit the workstream doc on `master` directly
([standing-rules §1.5](standing-rules.md#15-no-destructive-git-actions-on-mastermain)).
Several of them can ride in one PR.

Each commit should be one of these three kinds of change, not a mix. One-sentence commit
message in the `docs(<area>): <action>` shape:

- `docs(oauth2): move #15041 from merge-ready to shipped in FHIR workstream`
- `docs(oauth2): FHIR shepherd pass batch N — #NNNN #NNNN #NNNN reviewed`
- `docs(oauth2): add follow-up register entry for X surfaced by #NNNN review`

---

## 7. When the pass ends

A shepherd pass ends when the merge-ready queue is drained — every ✅ / 🔶 either merged or
explicitly deferred by the maintainer. At that point:

- The workstream doc has an accurate "Open as of" snapshot and a fresh set of shipped
  entries.
- Every merge-ready PR has a brief on disk.
- The shepherd log has one entry per PR touched in the pass, with the merge SHA on the
  landed ones.
- The follow-up register has any surfaced concerns.

Then the next stock-take can start cleanly.

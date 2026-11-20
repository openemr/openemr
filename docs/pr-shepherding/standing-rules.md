# PR Shepherding — Standing Rules

Return to the [hub](README.md).

Rules the agent and the maintainer have standing agreement on. These apply regardless of
subject, phase, or PR. When one of these and a specific instruction appear to conflict,
the rule wins unless the instruction is explicit that it overrides.

---

## 1. Hard rules

The agent **must** check with the maintainer before doing any of the following, even if
it looks obviously correct:

### 1.1 No public PR comments without per-post approval

- The agent does not post comments on any GitHub PR, issue, or advisory without
  per-post approval from the maintainer.
- This includes `gh pr comment`, `gh issue comment`, `gh api` writes against comment
  endpoints, and anything that triggers an email to the author or watchers.
- Draft comments go into the private review brief; the maintainer decides whether and
  when to post, and the maintainer does the posting themselves.
- Reason: the agent's drafts are often correct on substance but off on tone,
  diplomatic framing, or project-political context; a public mistake is irreversible.

### 1.2 No direct pushes to contributor PR branches

- The agent does not push commits to a contributor's fork, even when the PR has
  `maintainer_can_modify: true` enabled.
- Fine-grained PATs scoped to `openemr/openemr` return 403 on cross-fork pushes
  regardless of the per-PR flag; attempting it leaves a confusing partial state.
- When a contributor PR needs a maintainer-side change (e.g. a baseline rebase after
  PHPStan drift), the agent prepares the change locally in a worktree, exports patches
  via `git format-patch`, and hands them off with an explicit push command for the
  maintainer to run from their own terminal.

### 1.3 No other modifications of another PR without per-PR approval

Beyond pushes (§1.2) and rebases (§1.4), the agent does not modify another contributor's
PR at all without explicit per-PR approval. That includes `gh pr edit` (title, body,
base, reviewers, assignees, labels, milestones), `gh pr close` / `gh pr reopen`,
toggling draft ↔ ready, merging, and any `gh api PATCH/PUT/DELETE` against the pull or
its comments. The agent prepares a proposed change in a brief and waits for an
explicit "go ahead and <action> #NNNN."

### 1.4 No rebase of a contributor PR without per-PR approval

- A rebase of someone else's PR is a hard-to-undo action that rewrites their branch
  history and triggers CI + notification noise.
- Even with the hand-off pattern from §1.2, the agent waits for an explicit per-PR
  "go ahead and rebase #NNNN" from the maintainer before preparing the rebase.
- The agent never rebases a PR that is currently receiving commits from its author.

### 1.5 No destructive git actions on master/main

- The agent does not force-push, reset, or amend commits on `master`, `main`, or any
  shared release branch (`rel-NNN`).
- Agents do not create or push new commits directly to `master`/`main` either (this is
  also in `AGENTS.md` → "Working in a git worktree"). Feature work goes on a feature
  branch via `openemr-cmd worktree add <branch> -b`.

### 1.6 No fetch with `--update-head-ok` in the primary repo

- Covered in `AGENTS.md`. Mentioned here so a shepherd pass that needs to refresh
  `upstream/master` uses `git fetch upstream` (or `git pull`), not
  `git fetch --update-head-ok`.

### 1.7 Memory discretion

- The agent never saves a memory entry that would be surprising to the maintainer if
  read back later (private opinions about contributors, internal strategy, anything the
  maintainer wouldn't put in a project doc).
- Memory is for pattern-worthy process observations, not per-PR state.

---

## 2. CodeRabbit mechanics

CodeRabbit's review system has non-obvious behaviors that confuse first-time shepherds.

### 2.1 Incremental review — "Already reviewed the last commit"

- CodeRabbit reviews each new commit **once**. A second `@coderabbitai review` on an
  already-reviewed commit returns:

  > "Already reviewed the last commit. Use `@coderabbitai full review` to rerun a review
  > of the entire changeset. Note: CodeRabbit is an incremental review system and does
  > not re-review already reviewed commits."

- This is a no-op, **not** a dangling request. A thread that ends with this reply from
  rabbit is complete.
- To force a fresh review of an already-reviewed changeset, use
  `@coderabbitai full review`. Or push a trivial new commit, which triggers an auto-review
  of that commit.

### 2.2 Paused reviews and resume

- Reviews can be auto-paused (e.g. after heavy rapid-fire comment activity on a PR).
  `@coderabbitai pause` and `@coderabbitai resume` toggle the auto-review loop.
- `@coderabbitai resume` responds `"Reviews resumed. No new commits to review."` when
  the current head has already been reviewed. That is not a failure; it is just
  resume without a side-effect because there is nothing new to do.
- `@coderabbitai review` and `@coderabbitai resume` are different commands; a single
  mention attempt may be partially interpreted as both. Don't be surprised to see two
  rabbit replies for one maintainer mention.

### 2.3 Mis-spelled mentions are silently dropped

- Rabbit's mention parser requires the exact literal `@coderabbitai`. Typos
  (`@coderabbi`, `@coderabbitai-bot`, `@coderabbot`) are silently dropped — rabbit never
  parses them, no reply posts.
- A thread that ends with an apparent "maintainer asked rabbit for something and rabbit
  never responded" is almost always a typo in the mention; check the exact spelling
  before assuming rabbit is broken.

### 2.4 Post-unpause reviews reliably find edge cases

- After a `@coderabbitai resume` on a PR that has been paused for a while, rabbit's
  next review commonly finds a Minor or Major concern the initial review missed. This
  has been observed consistently enough to treat as a pattern: if rabbit returned "clean"
  on the first pass and then gets paused + resumed before merge, expect the post-unpause
  review to surface at least one actionable concern.

### 2.5 "Outside diff" comments

- Rabbit occasionally identifies a concern in code outside the PR's diff (a caller of a
  changed function, a sibling file with the same pattern). These post as "outside diff
  range comments" in a single top-level review comment and don't appear as inline
  threads. Check the review body, not just the inline threads, when reading rabbit's
  output.

### 2.6 Rabbit's cited raw-github SHAs are not always current

- Rabbit sometimes links to `raw.githubusercontent.com/.../<sha>/<path>` to support a
  finding. That SHA may be the state rabbit reviewed, not current master or the PR's
  current head. If a rabbit finding contradicts what the file looks like in your local
  worktree, verify against current code before propagating the finding into the brief.
- Example: PR #13598's Nov-2 rabbit finding on `EncounterFixtureManager` uuid-registry
  cleanup cited a SHA that didn't match any PR commit; the current code already does
  the cleanup correctly.

### 2.7 A resolved thread can carry a new finding

- Rabbit resolves threads itself. When the author replies "fixed in X" on an inline
  finding, rabbit's follow-up reply typically confirms the fix and ends with "Review
  thread resolved" — and that same reply can add a **new** finding ("Separate security
  blocker: …"). The thread then shows as resolved in the GitHub UI and in the
  `reviewThreads.isResolved` GraphQL field, with the new finding inside it.
- So resolution state is not a signal, in either direction. Before a verdict, read the
  full text of **every** rabbit reply in **every** thread, resolved ones included, and
  track each finding on its own row of the raised → addressed → confirmed walk. The last
  reply is not enough: a later reply can be a no-op ("already reviewed") or about a
  different concern, and then an earlier finding in the same thread is still open with
  nothing after it that says so. The new finding is an inline reply, not a review body,
  so walking review bodies for "outside diff" comments (§2.5) doesn't surface it either.
- One call returns every thread with its resolution state and full comment text (`body`,
  not `bodyText`: `bodyText` drops Markdown link targets, so a reply that links its fix as
  `[fix](commit URL)` loses the URL that verifies it). Both connections are paginated, and
  the truncation is silent, so an open finding can be missed: when `reviewThreads`
  reports `hasNextPage`, repeat the call with `after:"<endCursor>"` on `reviewThreads`; when
  a thread's `comments` reports `hasNextPage`, page that one thread by its `id` with the
  second query, because an `after:` cursor on the `comments` connection of the first query
  would apply to every thread it returns and skip other threads' comments.

  ```bash
  gh api graphql -f query='{ repository(owner:"openemr", name:"openemr") {
    pullRequest(number:<N>) { reviewThreads(first:100) {
      pageInfo { hasNextPage endCursor }
      nodes { id isResolved path line
        comments(first:100) {
          pageInfo { hasNextPage endCursor }
          nodes { author { login } createdAt body } } } } } } }'
  ```

  ```bash
  gh api graphql -f query='{ node(id:"<thread id>") { ... on PullRequestReviewThread {
    comments(first:100, after:"<endCursor>") {
      pageInfo { hasNextPage endCursor }
      nodes { author { login } createdAt body } } } } }'
  ```

- Precedent: #15100 at 2026-11-08. Rabbit's 23:01 UTC reply on the `RCFaxClient.php:272`
  thread confirmed the email fix in `754ed06da9`, added "**Separate security blocker:** if
  both `isDocuments` and `isContent` are set, the code skips path validation and the
  authorized document reader…" (a real Major, fixed in `15148379f7`), and resolved the
  thread. A gate check against resolution state read "no unresolved Actionable" with that
  finding open.

### 2.8 Rate-limit → no coverage, not clean coverage

- When rabbit never ran on a PR (OSS rate-limit hit before initial review), the
  `CodeRabbit` check conclusion is `NEUTRAL` and no Actionables are ever posted. The
  decision-gate's "no unresolved rabbit Actionable" row passes vacuously — the shepherd
  has no signal from rabbit, not a clean signal.
- Getting `@coderabbitai full review` posted is a **Phase 1 action**, independent of
  whether the PR ever reaches Phase 2. The mention is a public PR comment, so §1.1
  applies: the agent flags the PR and asks the maintainer to post it (or to approve that
  one post); it does not post it on its own. The request is a prerequisite for the gate,
  not a Phase 2 brief-writing step — if the PR is held in Phase 1 for any other reason, the
  trigger still has to fire so rabbit coverage is available when the other block
  clears. Flag `NEUTRAL` in the stock-take snapshot and the shepherd log.

### 2.9 Rebase invalidates rabbit's commit-SHA citations

- A force-push rebase on an in-review PR replaces the branch's commit history. Rabbit's
  walkthrough banner still shows the pre-rebase `Last reviewed: X`, but X is no longer
  in the current linear history and the commit-SHA citations in its earlier findings
  point at commits that don't resolve. Detect by comparing the walkthrough banner's
  `Last reviewed` SHA to `git log pr-<N>` — if absent, the PR was rebased.
- The mechanic is already handled in practice: §4 step 3 verified-by-diff on the
  current head doesn't require the cited SHA to resolve. But naming it explicitly keeps
  a new shepherd from blocking on an unresolvable citation.

### 2.10 Rabbit's concurrence is weak evidence

- Rabbit will often withdraw a finding within a minute of the author declining it
  ("I agree… I withdraw the suggestion") without re-reading the code. On the same
  #15100 session that drove §2.7, rabbit withdrew an outside-diff Minor the author
  had pushed back on, with no independent verification.
- Record "I agree / I withdraw" as the **author's** scope-out, verified by the
  shepherd against the code, not as rabbit having re-checked anything. The scope-out
  path in [per-pr-review.md §4 step 4](per-pr-review.md#4-walk-rabbit-history) applies:
  shepherd confirms by diff inspection, then splits by what the inspection shows —
  verified out-of-scope → `🔶` residual + follow-up; verified false positive → record
  and move on, no residual. Rabbit's agreement is not the final word in either case.

---

## 3. Staleness and recheck cadence

### 3.1 Hold triage can go stale within a day

- A `🔴 held` verdict captured at a morning snapshot can be addressed by afternoon on an
  active contributor's PR.
- Re-check held PRs at least once per 24h before reporting them as "still blocked" in a
  subsequent pass.
- Precedents from the 2026-11 workstream: #15012 moved 🔴 → ✅ after 11 proactive
  afternoon commits; #14968 moved 🔴 → ✅ after CI fix; #14924 moved 🔴 → ✅ after a
  sibling PR #14939 shipped and closed the pre-existing-bug objection.

### 3.2 Author-dormant vs author-dormant-and-blocked

- "Author dormant" is a status observation, not a verdict. A PR can be dormant and
  merge-ready, or dormant and blocked. State both explicitly.
- When flagging a dormant PR as blocked on author action, state the exact action needed
  (address rabbit Major #N, fix CI lane X, resolve maintainer `CHANGES_REQUESTED`)
  so the maintainer knows whether to nudge or close.

### 3.3 Workstream-doc snapshots have a half-life

- The "Open as of <date>" bucket in the workstream doc is only true for that date. Add a
  fresh bucket on each shepherd pass rather than editing the old one in place; the
  history is useful for release-note narrative and for spotting dormant work.

---

## 4. Sibling-pattern: coverage sweep, not block

- When a PR correctly fixes one instance of a pattern that exists across N sibling files,
  the siblings are a **follow-up sweep PR**, not a block on the current one.
- Blocking the first PR raises the bar higher than the siblings that already shipped and
  creates inconsistent review standards.
- Log the sweep as a follow-up register entry with the emoji `📏 coverage gap` and move
  on.
- Precedent: #15034's rabbit Nitpick ("cross-patient exclusion test for FHIR
  GET-by-patient") applied to the shipped #15000 and the long-shipped
  `ConditionFhirApiTest` just as well — making #15034 do the sweep solo would have been
  a stricter standard than the siblings in the tree.

---

## 5. Baseline integrity

- PHPStan baseline: **zero new entries** on a merge-ready PR. Count reductions are fine
  and expected on a PR that fixes source-level issues the baseline was masking.
- Verify against the **actual merge base**, not your local `upstream/master` snapshot.
  Drift between the two is normal and misleading. Command:
  `git diff $(git merge-base pr-NNNN upstream/master)..pr-NNNN -- .phpstan/baseline/`.
- The `github-actions[bot]` "PHPStan Baseline Diff" comment on the PR is authoritative;
  cross-check your local diff against it.
- The baseline-stability CI check will reject any difference after regeneration, so
  hand-added docblocks to baseline files are a known rabbit-nit false alarm — explain to
  the maintainer when it comes up.

---

## 6. Inferno CCDA fixture in private submodule

- The G9 Certification test `CCDADocRefGenerationTest::testGenerateCCDADocRef` at
  `tests/Tests/Certification/HIT1/G9_Certification/CCDADocRefGenerationTest.php:73`
  compares live-generated CCDA against
  `ci/inferno/inferno-files/files/data/ccda/CCDA_Newman_Alice_expected.xml`.
- That fixture lives in the **private** `openemr/inferno-files` submodule. The test skips
  when the fixture is absent, so forks + first-time-contributor CI never see it; only
  `openemr/openemr` master-CI's Inferno job does.
- When a PR changes CCDA/QRDA/FHIR emission (template IDs, LOINC/ActCode shifts, attribute
  strips, namespace-sweep rules), flag that Inferno CI on master may go red post-merge
  until an admin updates the private fixture.
- Diagnosis footprint: a single failure on `CCDADocRefGenerationTest`, 11 tests / 341
  assertions, expected-vs-actual DOM diff showing exactly the emission change the PR
  made.
- Fix path (two steps, both required):
  1. An admin with write access to `openemr/inferno-files` updates the matching XML
     lines and pushes to `openemr/inferno-files` master.
  2. An openemr-side PR bumps the `ci/inferno/inferno-files` **gitlink** in
     `openemr/openemr` to the new submodule SHA. Without this bump, CI still checks
     out the old pointer — the Inferno workflow uses `actions/checkout@v7` with
     `submodules: recursive`, which resolves to the recorded gitlink, and
     `ci/inferno/run.sh` does `git submodule update` without `--remote`. **Pushing
     to the private submodule alone leaves CI on the old fixture.**
- Precedent: PR #14953 merged 2026-11-04, Inferno master-red through 2026-11-08
  because only step 1 was followed; step 2 (gitlink bump) was the missing action.
  Tracked as public openemr/openemr issue #15083.

---

## 7. Verify against the merge base, not `upstream/master`

- Local `upstream/master` can be hours or days behind the actual branch state. Commands
  that compare "PR vs master" should compute the merge base explicitly:

  ```
  git fetch upstream pull/NNNN/head:pr-NNNN
  git fetch upstream master
  base=$(git merge-base pr-NNNN upstream/master)
  git diff $base..pr-NNNN -- <path>
  ```

- A stat line that reads `+510 / -65` against your local `upstream/master` and `+2 / -27`
  against the merge base is normal — the former includes ambient master drift the PR did
  not cause.

---

## 8. Memory vs session

- **Memory** (persistent across sessions): pattern-worthy observations — recurring
  gotchas, workflow conventions, rule corrections the maintainer explicitly calls out.
  Save these so the next shepherd pass starts informed. Memory location is agent-specific
  (Claude Code: `~/.claude/projects/<slug>/memory/`); the point is "persistent, not
  per-conversation."
- **Session state** (per-conversation): current pass's PRs, in-flight briefs, running
  shepherd log. These live in the workstream doc + scratch briefs + shepherd log; they
  are not memory-worthy.
- A memory entry that names a specific function / file / flag is a claim that it existed
  *when the memory was written*. Before recommending from memory, verify the current
  state in the repo — rename / removal / relocation is common.
- When the maintainer corrects a shepherd verdict ("you missed that X was addressed"),
  save the *rule* behind the correction to memory, not the specific PR state. Example:
  "when a maintainer leaves a formal `CHANGES_REQUESTED`, track for subsequent resolution
  commits before reporting the concern as unaddressed" — that rule survives; the specific
  PR state doesn't.

---

## 9. Classification emoji legend

These emojis are used in the workstream doc and shepherd log. Keep them consistent so
grep-across-docs works.

### PR buckets

- `⏳` — Awaiting shepherd review. Phase 1 metadata passes; no Phase 2 brief yet.
- `✅` — Merge-ready. Phase 2 brief with approve verdict; decision gate passes.
- `🔶` — Merge-ready with known residual. As ✅ but one non-blocking concern is recorded.
- `🔴` — Held. One or more decision-gate rows fails.
- `⏸️` — Author-declared draft. Scan-only; no deep review.
- `🚢` — Shipped. Appended to a bucketed entry once the maintainer confirms merge. Keep the
  bucket emoji so history is scannable.

### Follow-up register

- `🪲` — Test-infrastructure bug. Fixture, test harness, or test-only infrastructure defect
  surfaced by shepherding but not caused by the PR.
- `🧹` — Cleanup or pattern alignment. Pre-existing code smell, redundant pattern,
  consistency gap.
- `📏` — Coverage gap. Missing test for a behavior the PR touches or a sibling it doesn't.

**Security-adjacent findings use `🧹`, not a separate emoji.** A sibling-file sweep that
happens to involve a known-risky pattern (IDOR template, injection surface, DoS vector)
is still a hygiene follow-up at the register level, with specific details kept in the
private brief only. See [per-pr-review §13](per-pr-review.md#13-parallel-subagents-for-batch-reviews)
for the pattern.

**The private brief is not durable tracking by itself.** A scratch file under a
maintainer's home directory is a session artifact, not a ledger. When a shepherd pass
surfaces a concern that meets vulnerability severity thresholds (exploitable, not
pre-existing-and-documented, affects more than the current PR), the handoff isn't
just "write it in the brief and log the register entry" — the maintainer also opens
a private GitHub Security Advisory under `.github/SECURITY.md`'s flow, or routes it
through whatever private-advisory-triage workflow the project maintains, so the
security team has the finding on a tracked queue regardless of which shepherd
session surfaced it. The brief + register are the shepherd's record of *where the
finding came from*; the GHSA draft (or equivalent) is the record of *what happens
to it next*.

---

## 10. CLAUDE.md house conventions (pointers)

These are enforced by CI or by human review on OpenEMR, but they matter during shepherding
because they tell you whether a PR's shape is actually ready to merge:

- `declare(strict_types=1)` on new PHP files. (`AGENTS.md` → "Strict Typing")
- Native type declarations on every property, parameter, and return. (`AGENTS.md`)
- Narrow with `is_string()` / `instanceof` — don't cast. (`AGENTS.md` → "Static Analysis")
- `@codeCoverageIgnore` with the standard wording on every data provider. (`AGENTS.md` →
  "Data providers: mark as `@codeCoverageIgnore`")
- No new baseline entries. (`AGENTS.md` → "Avoid baselines")
- `OEGlobalsBag` instead of `$GLOBALS`. (`AGENTS.md` → "Database and Global Settings")
- PSR-20 `ClockInterface` injected instead of `new \DateTimeImmutable()`. (`AGENTS.md` →
  "Dependency Injection")
- Catch `\Throwable`, not `\Exception`. (`AGENTS.md` → "Error Handling and Logging")

Flag a `🔶 residual` if a PR does good work but misses one of these in a way that would
land a baseline entry, a `mixed` cast, or an un-ignored data provider. The maintainer
decides whether to merge-with-residual or block.

# PR Shepherding — Phase 2: Per-PR Deep Review

Return to the [hub](README.md).

**Purpose.** For each merge-ready (or merge-ready-with-residual) PR from Phase 1, produce
a private review brief that walks the evidence and ends in a defensible verdict.

**Prerequisites.**

- Phase 1 output in hand: the merge-ready list + each PR's one-line scope summary.
- Standing rules read: [standing-rules.md](standing-rules.md).
- Brief template in hand: [deliverables.md §3](deliverables.md#3-review-brief-template).

**Deliverable.**

- One review brief per PR, in the briefs directory (default
  `<git-parent>/pr-<N>-review-brief.md`; see
  [deliverables.md §5](deliverables.md#5-filesystem-locations)).

---

## 1. Fetch the PR locally

Before reading code, pull the PR head as a local branch so diffs are cheap:

```bash
git fetch upstream pull/<N>/head:pr-<N>
```

(`origin` on the primary repo's remotes is the maintainer's fork, not upstream. Use the
remote that points at `openemr/openemr`.)

Then compute the merge base against **the PR's base branch**, not always master —
a PR targeting `rel-810` has a merge base against `rel-810`, not against today's
master. Look up the base branch first:

```bash
base_branch=$(gh pr view <N> --repo openemr/openemr --json baseRefName --jq .baseRefName)
git fetch upstream "$base_branch"
base=$(git merge-base pr-<N> "upstream/$base_branch")
```

Most PRs target `master`, so `$base_branch` will typically be `master`. For a
release-branch PR, computing the merge base against master instead would include
changes outside the PR in the baseline comparison — the wrong signal entirely.

Keep `$base` handy; every "PR vs base branch" comparison should use it, not raw
`upstream/master`. See
[standing-rules §7](standing-rules.md#7-verify-against-the-merge-base-not-upstreammaster).

---

## 2. Pull full metadata

One `gh pr view` call with the fields needed for the brief:

```bash
gh pr view <N> --repo openemr/openemr --json \
  title,body,author,commits,files,additions,deletions,\
  reviews,comments,mergeable,statusCheckRollup,isDraft,reviewDecision
```

Save to a scratch file for repeated reads; it has everything you need except inline
review-comment bodies (those come from a second call).

For inline review comments (CodeRabbit's Actionable + Nitpick posts, author replies,
rabbit's follow-up acknowledgements):

```bash
gh api repos/openemr/openemr/pulls/<N>/comments --paginate
```

For issue-level comments (the maintainer directives, author announcements,
`@coderabbitai review` tags, CI bots):

```bash
gh api repos/openemr/openemr/issues/<N>/comments
```

(PR review comments and issue comments are different GitHub resources; both matter.)

---

## 3. Walk each commit

For a multi-commit PR, each commit is a step in the author's reasoning. Walk them in
order and classify:

- **Feature / fix commit** — the change the title names. One or more.
- **Test commit** — new or updated tests covering a specific invariant.
- **Baseline commit** — PHPStan baseline count-reductions from the fix.
- **Merge-master commit** — periodic sync; verify no conflict leftover.
- **Response-to-rabbit commit** — a commit introduced to address a specific rabbit
  finding. These are usually small and named `fix(...): skip the X for :missing` style.

For each real change commit, verify:

- The change is correct for the stated bug / feature.
- The test coverage is appropriate for the tier (isolated / DB / api / E2E).
- The commit message matches the shape of the change (conventional commits).

Record the commit walk as a table in the brief; one row per commit with correctness +
coverage notes. See [deliverables.md §3.3](deliverables.md#33-commit-walk).

---

## 4. Walk rabbit history

Rabbit state lives in **three** API endpoints, not two — pull all three:

| Endpoint | What it carries |
|---|---|
| `issues/<N>/comments` | Walkthrough summary, pre-merge-checks table, top-level review body, maintainer / author chat replies |
| `pulls/<N>/reviews` | Review-body-level posts (sometimes carry the `Actionable comments posted: N` header) |
| `pulls/<N>/comments` | Inline file-level findings — the Actionables and Nitpicks live here as resolvable threads |

For every rabbit Actionable or Major on the PR:

1. **Raised.** What rabbit said, with line reference and severity.
2. **Addressed.** The specific commit that addresses it. If rabbit auto-marked it
   ("✅ Addressed in commit X"), note the SHA. Rabbit's auto-ack is intermittent in
   practice — fires on some findings and not others on the same PR, so don't require
   it. Read the commit diff against the raised line and confirm the specific concern
   is addressed.
3. **Confirmed.** Either rabbit's follow-up acknowledgement ("Review thread resolved" /
   "I withdraw the request" / "This addresses the finding") **or** verified-by-diff
   inspection against the raised line. A finding without rabbit's ack but with a commit
   that demonstrably addresses the raised concern counts as confirmed; record the SHA
   and the specific lines.
4. **Scoped-out.** The author legitimately pushed back ("that's pre-existing", "that's
   out of scope") and rabbit accepted. Record as a `🔶 residual` with follow-up register
   entry. For an author who doesn't reply inline, Scoped-out can be reached via rabbit's
   own withdrawal or the shepherd's verdict based on the diff — don't block waiting for
   an author reply that may not come.

Treat rabbit's assertions as evidence, not ground truth. See
[standing-rules §2](standing-rules.md#2-coderabbit-mechanics) for non-obvious
mechanics — incremental-review no-ops, paused/resumed sessions, mis-spelled mentions,
and the "outside diff" one-top-comment vs inline-thread distinction.

**Rabbit false-positive check:** when a rabbit finding cites a raw-github SHA that
doesn't match any PR commit, verify the claim against current code before propagating
it into the brief. Precedent: #13598's Nov-2 uuid-registry finding cited a stale SHA;
current code already handled the cleanup.

**Walk the pre-merge-checks table separately from Actionable/Nitpick counts.** Rabbit's
walkthrough comment (not the review body) carries a pre-merge-checks table — Title,
Description, Linked Issues, Out of Scope Changes, and Docstring Coverage, each rated
Passed / Warning / Failed. A PR with zero Actionable findings can still carry a
non-blocking warning worth recording. Walk the table, note any warning or failure in
the brief's rabbit-coverage section. Example: a Docstring Coverage warning at 50 %
(threshold 80 %) is not merge-blocking — rabbit's own gating treats it as info —
but it belongs in the brief so a later reader isn't surprised.

---

## 5. Walk maintainer review history

Separate from rabbit, pull every formal review on the PR:

- `APPROVED` — approval. A separate approval does **not** clear a prior
  `CHANGES_REQUESTED` from a different reviewer.
- `CHANGES_REQUESTED` — hard block. Stays open until the requesting maintainer
  re-reviews.
- `COMMENTED` — informational, not blocking.
- `DISMISSED` — the author or admin dismissed a stale review.

If the PR state is `CHANGES_REQUESTED`, identify the requesting maintainer and the
specific concern. If the concern has been addressed in a subsequent commit, flag in the
brief that the PR is substantively ready but the `CHANGES_REQUESTED` state hasn't been
formally cleared — the blocker is a re-review, not a code change.

Precedent: #14730 at 2026-11-07 — kojiromike filed `CHANGES_REQUESTED`, author fixed
it, anun333 confirmed closure, rabbit raised a new Major that was legitimately scoped
out — state still `CHANGES_REQUESTED` because kojiromike hadn't re-reviewed.

**Reference-branch strategy signal.** When a PR author has posted a staged strategy on
their own fork (reference branches showing later phases of the work), capture it in the
brief: which branches exist, what each represents, what's merged in the current PR vs
waiting. This becomes the follow-up register entry for the chain, and lets later
shepherd passes expect the sequel PRs. Example: #15038 referenced `insurance-reference`,
`-boundary`, `-full`, `-module` branches on `anun333/openemr`; the register tracks the
~12 sequel PRs that chain is expected to produce.

---

## 6. Verify claims (don't trust)

When the PR body, a rabbit comment, or an author reply makes a specific factual claim,
verify it. Common checkables:

### 6.1 "Method X does Y"

```bash
grep -n "function X" <path/to/file>
```

Read the method. Confirm it does Y, not just that it exists.

### 6.2 "Fixture Z exists"

```bash
ls <path/to/fixture>
```

Or for a DB fixture, grep the fixture-manager file for the helper method.

### 6.3 "Baseline change against master is +N / -M"

Use the actual merge base, not local master:

```bash
git diff $base..pr-<N> --stat -- .phpstan/baseline/
```

### 6.4 "Tests fail on master / pass on this PR"

Check at least one test method's assertions line up with the fix, or run it locally if
the DB-backed suite is set up.

### 6.5 "US Core validator confirms X"

The author may have run the HL7 FHIR validator against the Coverage/Patient/etc
output. You usually can't rerun that, but you can:

- Spot-check the emitted FHIR shape against the US Core profile's MustSupport list.
- Verify the US Core constraint ID (e.g. `us-core-15`) is referenced in a recognizable
  way.
- Note "author-reported, not independently verified" if you can't rerun.

### 6.6 Probe an UNKNOWN mergeable state

GitHub computes `mergeable` lazily. `UNKNOWN` is the default state on a cold PR,
not an actual conflict signal. Two paths:

- **Poll.** Call `gh pr view <N> --json mergeable` every 15 s until it flips to
  `MERGEABLE` or `CONFLICTING`. GitHub typically computes within a minute of the
  first external viewer.
- **Local 3-way merge.** For PRs touching the same files as a recently-landed PR
  ("overlapping-touch"), the poll alone isn't enough — a merge can succeed but silently
  drop one side's intent. Run the throwaway-clone merge below and grep the conflict-prone
  sections for the expected post-merge markers from both PRs. Decides whether a rebase
  is actually needed.

```bash
# in a throwaway clone (not the primary repo):
base_branch=$(gh pr view <N> --repo openemr/openemr --json baseRefName --jq .baseRefName)
git clone <primary> /tmp/merge-test-<N>
cd /tmp/merge-test-<N>
git fetch <upstream> "$base_branch":upstream-base
git fetch <upstream> pull/<N>/head:pr-<N>
git checkout upstream-base -q
git -c user.email=t@t -c user.name=t merge pr-<N> --no-ff --no-commit
```

The merge either (a) succeeds with both PRs' semantics preserved — approve without a
rebase, (b) succeeds but silently drops one side's intent — request a rebase, or
(c) fails with conflicts — request a rebase.

---

## 7. Check baseline integrity

Against the merge base (not local master):

```bash
git diff $base..pr-<N> --stat -- .phpstan/baseline/
```

Rules:

- **Zero new entries.** Each change must be either a count reduction or a full entry
  deletion.
- **Count reductions should match source-level fixes.** A count going 2→1 should
  correspond to one fewer occurrence of the error pattern in the file; verify.
- **Cap file changes.** `.phpstan/fatal-baseline-caps.php` cap must exactly match the
  entry count after the change; the `FatalBaselineCapsIsolatedTest` enforces this.

The `github-actions[bot]` "PHPStan Baseline Diff" comment on the PR is authoritative —
cross-check your local diff against it.

---

## 8. Check CI breakdown

Not every green rollup is cleanly green. Call out explicitly:

- **CANCELLED lanes.** Usually a title-lint-job superseded by the next commit's title
  lint. Verify the next job passed.
- **SKIPPED lanes.** Inferno is often SKIPPED on forks (expected); other skips may be
  conditional on label.
- **FAILURE lanes** — the PR is held; name the lane in the brief. Exception: a solo
  `codecov/patch` or `codecov/project` FAILURE with a green "All Checks Passed"
  aggregator is OK; those are advisory status checks.

A common masking pattern: "All Checks Passed" job shows FAILURE because it depends on a
preceding CANCELLED or FAILURE. The underlying lane is what matters; the aggregator is
derivative.

---

## 9. Sibling-pattern check

Before writing the verdict, ask: does the pattern this PR fixes exist in sibling files
it doesn't touch?

- If **yes and the siblings already fixed it** (e.g. the PR aligns to a precedent): say
  so in the brief, note the precedent.
- If **yes and the siblings still have the bug** (e.g. the PR fixes one instance of a
  cross-cutting bug): log a `📏` sweep follow-up in the register, don't block the PR.
  See [standing-rules §4](standing-rules.md#4-sibling-pattern-coverage-sweep-not-block).
- If **no**: fine; move on.

---

## 10. Write the brief

Use the template at [deliverables.md §3](deliverables.md#3-review-brief-template).
Required sections:

1. **Metadata header.** Author, commits, diff, CI, prior maintainer action, rabbit
   summary.
2. **TL;DR verdict.** One paragraph. Start with `**Approve + merge.**` / `**Approve +
   merge with residual.**` / `**Hold.**`. Then the one-sentence justification.
3. **Primary fix or feature shape.** What the PR actually does, in enough depth that
   the maintainer can decide without re-reading the PR.
4. **Test coverage assessment.** Tier table, data-provider count, `@codeCoverageIgnore`
   check.
5. **Rabbit coverage assessment.** One subsection per finding: raised → addressed →
   confirmed.
6. **Maintainer review assessment.** If any formal review is open (`CHANGES_REQUESTED`),
   state who / when / whether it's been substantively addressed.
7. **Baseline cleanup.** Net adds/removes against merge base; call out zero adds.
8. **CI state.** Summary + explain any non-SUCCESS.
9. **Follow-ups to track.** Register entries this review surfaces.
10. **Shepherd recommendation.** The one line the maintainer acts on.

The brief is written for the maintainer, not for the public. Terse, direct, evidence-led.

---

## 11. When to downgrade a verdict

Some patterns that should move a verdict from `✅ Merge-ready` to `🔶 residual` or
`🔴 Held`:

- Open rabbit Actionable that isn't withdrawn or addressed.
- Open maintainer `CHANGES_REQUESTED` with no evidence of substantive resolution.
- CI FAILURE in any lane that isn't title-lint-superseded or advisory codecov/*.
- Baseline entries added (not removed).
- A convention violation flagged by `AGENTS.md` that would land `mixed`, cast, or
  un-ignored data provider.
- A test that asserts the *symptom* rather than the *cause*.
- A test that pins a bug (regression test that confirms the current behavior is wrong).
- Author's rebuttal to a rabbit finding that doesn't actually address the raised concern
  (common pattern: author defends the fix generally without responding to the specific
  edge case rabbit raised).

When downgrading, the brief says exactly which row of the decision gate fails and what
would unblock it.

---

## 12. Handoff to Phase 3

Phase 2 ends when the brief is written and saved. The brief's verdict goes into the
workstream doc's bucketed snapshot. Phase 3 then updates the running doc + log + register
to match. See [deliverables.md](deliverables.md).

**The brief binds the verdict to the reviewed head SHA.** The brief header records the
exact head commit the review was written against. If the author pushes after the brief
is written, the recorded head no longer matches the PR's current head — the verdict
applies to different code, and a fresh review (or at minimum a delta review against
the new commits) is needed before the maintainer merges.

The brief records both the reviewed **head SHA** and the reviewed **base-branch tip**
(the base branch itself can advance without the head moving — e.g. master receives
unrelated commits between the brief and the merge — and the merge-compatibility checks
need to re-run against the new base tip).

Before merging on a shepherd verdict, the maintainer (or the agent, when confirming
`landed <N>` reports) verifies:

1. Head unchanged: `gh pr view <N> --json headRefOid --jq .headRefOid` equals the
   brief's recorded head SHA. (Don't derive from `--json commits` — that API caps at
   100 commits; on a 100+ commit PR the last returned SHA can match while new ones
   exist.)
2. Base-tip unchanged: the current tip of the PR's base branch equals the brief's
   recorded base-tip. If it moved, re-run the merge-compatibility checks (§6.6)
   against the new tip before merging.
3. CI is still green.
4. No new rabbit Actionable has been posted since the brief.
5. No new maintainer `CHANGES_REQUESTED` has been filed.

If any of 1-5 moved, refresh the brief before merging.

**Do not post the brief publicly.** The brief lives in the private briefs directory. The
maintainer decides whether anything in it should land on the PR, and the maintainer does
the posting. See [standing-rules §1.1](standing-rules.md#11-no-public-pr-comments-without-per-post-approval).

**The brief on disk is authoritative, not the subagent return summary.** When Phase 2
is delegated to a subagent (see §13), the agent returns a short summary as a courtesy
preview. Before propagating anything into the shepherd log, workstream doc, or
follow-up register, re-read the brief on disk — the summary is lossy by design and can
drop nuance that matters at the dashboard level (a Docstring Coverage warning the
summary paraphrased away as "no findings," a scoped-out concern the summary omitted,
etc.).

---

## 13. Parallel subagents for batch reviews

When Phase 1 hands off an awaiting-review list of several PRs that are mutually
independent across **every pair** (not just adjacent — a transitive dependency where
A and C depend on each other via a function the intermediate B doesn't touch can still
break integration if A and C are reviewed in parallel), dispatch one agent per PR in
parallel. Each agent runs Phase 2 against its PR and writes the brief to disk;
the parent session integrates the results into Phase 3 deliverables.

**When this works well:** 4–5 independent PRs on the list. One agent handles one PR.
Keep the parent session concurrently handling the biggest or most-complex PR directly
— the parent has conversation context the subagent can't easily get (recent review
briefs, running follow-up register, maintainer-preference nuance).

**When to skip:** fewer than 3 independent PRs on the list (overhead not worth it); or
a PR pair with a dependency (one agent reviewing both is cheaper than coordinating two);
or a security-sensitive PR where the parent's standing-rules context is load-bearing.

### Prompting shape

Each agent needs enough context to apply the process without the parent's conversation
history. Minimum prompt fields:

- **PR number** and one-line scope description.
- **Process-doc paths** in this subtree ([per-pr-review.md](per-pr-review.md),
  [standing-rules.md](standing-rules.md), [deliverables.md](deliverables.md) §3 for
  the brief template). Agent is expected to read them before starting.
- **Recent-master context.** Short list of recently-landed related PRs with SHAs and
  one-line summaries — anything the agent would need to interpret "merges cleanly against
  current master" or "this is part of chain X." The agent doesn't have the running
  shepherd log in context.
- **Brief output path.** Standard convention: `<git-parent>/pr-<N>-review-brief.md`.
- **Standing rules re-emphasis.** The agent must not post anything publicly. Brief on
  disk only.
- **Return-format expectations.** A `<250-word` summary with verdict, 3–5 key findings,
  follow-ups surfaced. Explicitly note that the summary is a preview — the brief is
  authoritative.

### Security-adjacent findings in subagent briefs

If a subagent surfaces a security-adjacent concern (IDOR, injection, DoS pattern) in a
private brief, the parent session handles it neutrally in the public-facing artifacts:

- **Private brief:** keep the specific concern details (vuln class, surface, attacker
  model). This is the record for maintainer action.
- **Shepherd log:** neutral framing — "open-aisle concerns per private brief" rather
  than naming the vuln class. The shepherd log lives in scratch but is where a maintainer
  summary would be sourced from.
- **Follow-up register:** use the `🧹` (hygiene/cleanup) emoji from the standard legend,
  not `📏` or an invented emoji. Frame the sweep as hygiene or pattern alignment, not
  as a vuln-class remediation.

This matches the "no security language in bundle-lane titles, PR bodies, or code
comments" rule — the rule survives even when the finding is pre-existing and the
affected code isn't under a draft advisory.

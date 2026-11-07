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

Then compute the merge base for later baseline-integrity checks:

```bash
git fetch upstream master
base=$(git merge-base pr-<N> upstream/master)
```

Keep `$base` handy; every "PR vs master" comparison should use it, not raw
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

CodeRabbit posts in two places: PR reviews (the top-level review-body comments) and
inline pull-request-review-comments. Both matter.

For every rabbit Actionable or Major on the PR:

1. **Raised.** What rabbit said, with line reference and severity.
2. **Addressed.** The specific commit that addresses it. If rabbit auto-marked it
   ("✅ Addressed in commit X"), note the SHA. If not, read the commit diff and
   confirm manually.
3. **Confirmed.** Rabbit's follow-up acknowledgement ("Review thread resolved" /
   "I withdraw the request" / "This addresses the finding"). An un-acknowledged
   finding is still open even if the author claims it's addressed.
4. **Scoped-out.** The author legitimately pushed back ("that's pre-existing", "that's
   out of scope") and rabbit accepted the scoping decision. Record as a `🔶 residual`
   with follow-up register entry, not as an open finding.

Treat rabbit's assertions as evidence, not ground truth. See
[standing-rules §2](standing-rules.md#2-coderabbit-mechanics) for non-obvious
mechanics — incremental-review no-ops, paused/resumed sessions, mis-spelled mentions,
and the "outside diff" one-top-comment vs inline-thread distinction.

**Rabbit false-positive check:** when a rabbit finding cites a raw-github SHA that
doesn't match any PR commit, verify the claim against current code before propagating
it into the brief. Precedent: #13598's Nov-2 uuid-registry finding cited a stale SHA;
current code already handled the cleanup.

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
- **FAILURE lanes** — the PR is held; name the lane in the brief.

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
- CI FAILURE in any lane that isn't title-lint-superseded.
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

**Do not post the brief publicly.** The brief lives in the private briefs directory. The
maintainer decides whether anything in it should land on the PR, and the maintainer does
the posting. See [standing-rules §1.1](standing-rules.md#11-no-public-pr-comments-without-per-post-approval).

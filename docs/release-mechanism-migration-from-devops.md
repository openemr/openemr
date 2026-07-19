# Release-mechanism migration from openemr-devops

Architectural rationale, phased plan, and living tracker for migrating the
OpenEMR release-mechanism (release-prep tooling, ship orchestration,
release-package builds, announcement drafting, dispatch contracts) out of
`openemr/openemr-devops` and into this repository.

Companion to [`docker-migration-from-devops.md`](docker-migration-from-devops.md),
which moved the production-docker pipeline out of openemr-devops 2026-06-20.
That migration completed first because the docker surface had cleaner seams
and a clearer "current state" — the release-mechanism's seams (3-PR ordered
merge, cross-repo `repository_dispatch`, vendored contracts) are more
intricate and benefit from being scoped against the post-docker-migration
state, not the pre-docker-migration one.

Also runs in parallel with [`artifact-acceptance-testing-plan.md`](artifact-acceptance-testing-plan.md),
which adds a black-box acceptance test surface for the shipped Docker image
and release tarball (openemr/openemr#12811). No structural ordering
dependency — release-mechanism migration changes *who owns* the pipeline;
acceptance-testing changes *what tests exist against the artifact*. Once both
land, acceptance runs become the natural required-check surface for
release-prep PRs.

## Contents

- [Goal](#goal)
- [Why now](#why-now)
- [Proposed model](#proposed-model)
- [Validated foundation](#validated-foundation)
- [What moves where (concrete)](#what-moves-where-concrete)
- [What dies as part of the migration](#what-dies-as-part-of-the-migration)
- [What stays in `openemr-devops`](#what-stays-in-openemr-devops)
- [Phased plan](#phased-plan)
- [Behavior contract](#behavior-contract)
- [Branch-cut process under the final model](#branch-cut-process-under-the-final-model)
- [Decisions to lock before phase 2](#decisions-to-lock-before-phase-2)
- [Risks and wrinkles to plan for](#risks-and-wrinkles-to-plan-for)
- [Rollback](#rollback)
- [Deferred / known debt](#deferred--known-debt)
- [Feedback wanted](#feedback-wanted)

## Goal

Consolidate the OpenEMR release-mechanism into a single-repo flow rooted in
this repo (`openemr/openemr`), with:

- **The conductor** (release-prep PR + tag-creation) already here.
- **The consumers** (package build, ship orchestration, announcement drafting)
  moved from `openemr-devops`.
- **The canonical cross-repo contracts** (`dispatch.schema.json`, `TagVerifier`)
  relocated to `openemr/openemr` as the new owner, with the remaining external
  consumers (`website-openemr`, `demo_farm_openemr`) vendoring from here.
- **The rotation slice** (`release-rotation.yml`, `SlotRotator`, `versions.yml`,
  the registry linter) deleted outright — its docker-pin job moved to this
  repo's [`.github/release-targets.yml`](../.github/release-targets.yml) during
  the docker pipeline migration, leaving zero live targets in openemr-devops.
  (`release-targets.yml` is master-authoritative: every consumer either fires
  only from master or reads master's copy via `git show master:...`, so rel
  branches carry frozen snapshots that no consumer ever reads — documented in
  the file's header as of openemr/openemr#12586.)

Preserving:

- The **maintainer's ship-day UX** — one `gh workflow run ship-release.yml`
  invocation, same inputs, same artifacts. Only the `--repo` flag changes from
  `openemr-devops` to `openemr/openemr`.
- The **3-PR ordered merge contract** (infra → conductor → docs), or its
  collapsed-to-2 successor if we eliminate the Infra PR slot (decision deferred
  to phase 3 design).
- All **published artifacts** — annotated tags, GitHub Release objects,
  distribution packages with checksums + changelog, Docker Hub tag set + readme,
  per-channel announcement drafts.
- **Cross-repo `repository_dispatch`** to `website-openemr` and
  `demo_farm_openemr` (still external consumers).

## Why now

The docker-pipeline migration (`openemr-devops#790`, completed 2026-06-20) was
the load-bearing prerequisite. Pre-docker-migration, the release-rotation
slice of `openemr-devops` was actively rotating production docker pins on
every release — non-trivial work. Post-migration, those pins live in
[`.github/release-targets.yml`](../.github/release-targets.yml) here, which
has its own simpler update mechanism (edit the file when reshuffling
`latest`/`next`/`dev`; no rotation workflow needed).

The release-mechanism is now the *only* reason `openemr-devops` still receives
`repository_dispatch` events from this repo. Eliminating that seam:

- Removes the cross-repo dispatch round-trip from every release-prep push
- Dissolves the contract-vendoring chore (canonical → vendored copies with
  drift-check workflows)
- Co-locates the tooling with the source it operates on
- Shortens the maintainer iteration loop (one repo, one PR, one CI surface)

## Current execution order (revised 2026-06-23; renumbered 2026-06-30)

The original plan below treated this as one big linear migration ("Phased
plan" section). Post-8.1.1-prep, the plan re-orders into 7 workstreams
ordered by **what's next to happen in production**, with the actual
devops→core migration last (when all the upstream learnings are baked in).
Workstreams 2, 3, and 6 below are *not* migration work — they're net-new
optimization on top of release tooling that already lives in
`openemr/openemr`.

**Renumbering note (2026-06-30):** the actual devops→core migration was
previously slot 6. Workstream 6 is now patch-prep automation (PR #12697
in-flight); the migration moved to slot 7. References to "workstream 6"
elsewhere in this doc that pre-date 2026-06-30 mean the migration —
treated as workstream 7 going forward, and updated in place where
encountered.

| # | Workstream | Where the code lives | Est. | Rationale |
|---|---|---|---|---|
| 1 | **Remove unused stuff in devops** — **SHIPPED 2026-06-28** | openemr-devops + openemr/openemr | ~1 day | Required prerequisite for 8.1.1 ship (workstream 4). Trio of PRs landed 2026-06-28: openemr-devops#835 (collapse ship-release to 2-PR shape + delete dead rotation slice), openemr/openemr#12631 (docs collapsed to 2-PR shape), openemr/openemr#12619 (wire vendored-contract drift check via devops reusable workflow). Originally framed as "safe drop-in cleanup" — re-classified 2026-06-24 after tracing the ship flow: the Infra PR slot (openemr-devops PR #760, `release-rotation/auto`) was frozen with pre-docker-migration content that would either block ship-release.yml's preflight or silently undo the docker migration on merge. Workstream 1 collapsed ship-release to 2-PR shape, eliminating the Infra slot entirely. Maps to the original Phase 1 PR 1a + 1b + 1c detailed below. |
| 2 | **rel-820 cut readiness** — **SHIPPED 2026-07-01; first production exercise 2026-07-02 after three attempts** | openemr/openemr (already) | ~2-3 days | Next thing actually scheduled to happen after the 8.1.1 ship. Address G4 (master-scope mutator wiring for `8.X.0-dev → 8.X+1.0-dev` advance) + G5 (auto-trigger when `rel-NNN0` cut). Plus cross-branch-cut automation script. **Not migration work** — the conductor + mutators all already live in `openemr/openemr` core; this workstream extends them. Shipped in openemr/openemr#12696 (merge `6513b487b6`); rabbit-fix follow-up shipped in openemr/openemr#12709 (merge `8a01001a27`) — 3 Major items missed at initial merge (App-token permission scoping + `persist-credentials: false` on 3 checkouts + `endsWith` gate tightening). **First production exercise 2026-07-02** took three attempts and three rounds of mutator/workflow fixes to land clean: round 1 = #12722 (`--skip-globals` on branch-cut + patch-prep CLI invocations) + #12724 (drop `paths-ignore: docs/**` from release-prep); round 2 = #12731 (five fixes — fsupgrade-N.sh full-copy scaffold, `PostReleaseTargetsMutator` early-return when no live row, drop `GlobalsIncMutator` from release-prep rel-side list, `DockerComposeProductionMutator` tag-only pin, drop `MutatorContext::imageDigest`); round 3 = #12735 (`SqlUpgradeSkeletonMutator` double-newline fix + `DockerUpgradeScaffoldMutator` sql-scan `priorOpenemrVersion` derivation + `priorOpenemrVersion < target` ordering invariant + PR template audit). Fourth attempt landed clean via #12743 (rel-side) + #12744 (master-side) with release-prep #12742; downstream Docker Hub tags, dockerhub README, and demo_farm reconciliation (#168) all landed cleanly. |
| 3 | **Per-release-on-rel-branch optimization** — **PHASE A SHIPPED 2026-07-01; refined during 2026-07-02 first exercise** | openemr/openemr (already) | ~3-4 days | Apply 8.1.1 learnings. P1-P4 (docker upgrade machinery, version.php bump, release-targets row bump) are 100% mechanical; build a release-cycle-bot script that takes `X.Y.Z` + rel branch and generates the prep PRs. Folds in G7 (rel-810 surgical conductor sync), G8 (regression tests on master + rel-810). Pays dividends every patch release going forward. **Not migration work** — release-prep tooling already in core. Phase A (release-finalize partner PR + `PostReleaseTargetsMutator`) shipped in openemr/openemr#12662 (merge `abb1e2d940`). 8.1.x is being skipped — first production exercise of the paired release-prep + release-finalize conductor was the rel-820 cut on 2026-07-02, which surfaced Phase-A-touching bugs fixed in #12731 (`PostReleaseTargetsMutator` early-return when target rel has no live row; `DockerComposeProductionMutator` tag-only pin — no `--image-digest`; drop `GlobalsIncMutator` from release-prep rel-side list) and #12724 (release-prep filter no longer ignores docs-only master tips). Phase B (bulk P1-P4 release-cycle-bot) still outstanding. |
| 4 | **Ensure 8.1.1 ship works** | openemr/openemr (validation) | hours | When PR #12377 lands and the post-tag flow fires for the first time end-to-end. Most consumer dispatches fire to devops's still-live workflows; this is the validation of the half-migrated state. **Blocked by workstream 1** — see analysis below. Lessons feed back into workstream 7's design. |
| 5 | **Automate demo farm derivation (G6)** | demo_farm_openemr | ~3-4 days | Cross-repo work — build automation in `demo_farm_openemr` that derives `ip_map_branch.txt` + `demoLibrary.source` from `openemr/openemr`'s `release-targets.yml` + per-rel-branch Dockerfile ARGs. Orthogonal to release-mechanism migration; can run in parallel with any other workstream. |
| 6 | **Patch-prep automation** — **SHIPPED 2026-07-01; workflow-invocation + mutator refinements during 2026-07-02 branch-cut exercise** | openemr/openemr (already) | ~2-3 days | Sibling to workstream 2 but for patch-dev-cycle start instead of minor-branch cut: when a maintainer bumps `$v_patch` in `version.php` on a `rel-*` branch, fire a workflow that opens 2 coordinated ready-for-review PRs (rel-side + master-side) carrying the mechanical bootstrap work — docker upgrade scaffolding on both, new SQL upgrade skeleton on both, master bridge-file rename, master release-targets row insert + placeholder drop. Reuses the workstream 2 mutator framework (`DockerUpgradeScaffoldMutator`, `SqlUpgradeSkeletonMutator`) plus 2 new mutators + `MutatorContext::$fromVersion` extension. Shipped in openemr/openemr#12697 (merge `c1af6370a0`) directly after workstream 2's #12696 merged. **Not migration work** — patch-prep tooling lives entirely in `openemr/openemr` core. 8.1.x is being skipped; first production exercise fires on rel-820's `8.2.0 → 8.2.1-dev` transition. Shared mutators picked up branch-cut-exercise fixes on 2026-07-02: #12722 added `--skip-globals` to patch-prep's CLI invocation, #12731 changed `DockerUpgradeScaffoldMutator` to full-copy `fsupgrade-N.sh` from the prior file (no more stub), and #12735 fixed `SqlUpgradeSkeletonMutator` double-newline output + wired the sql-scan `priorOpenemrVersion` derivation (with `MutatorContext::$fromVersion` override — the same field patch-prep introduced). |
| 7 | **Migrate release stuff from devops to core** | openemr-devops → openemr/openemr | ~5+ days | The actual migration. All workstream 1-6 learnings baked in. Reusable-workflow-pattern decision revisited at workstream 7 entry (downgraded from blocking — see "Pre-Phase-1 architectural decision" section). Detailed phases in "Phased plan" section below cover the workstream-7 internals. (Previously numbered workstream 6 before the 2026-06-30 renumber for patch-prep.) |

**Sequencing notes:**

- **Workstream 1 is a hard prerequisite for workstream 4** (8.1.1 ship).
  Discovered 2026-06-24 while tracing the ship flow end-to-end:
  - `ship-release.yml`'s preflight (via `PullRequestTarget::forRelease()`
    in `openemr-devops/tools/release/src/`) expects 3 PRs ready to merge:
    Infra (`openemr-devops:release-rotation/auto`), Conductor
    (`openemr:release-prep/rel-810`), Docs (`website-openemr:release-docs/8.1.1`).
  - The current Infra PR #760 was last updated 2026-06-10 (10 days *before*
    the docker migration completed). Its diff edits paths now deleted on
    devops master: `docker/openemr/8.0.0/Dockerfile`, `docker/openemr/current`,
    `dependabot.yml` references to `docker/openemr/*`. All return 404 on
    devops master today.
  - `release-rotation.yml` runs successfully on every dispatch but skips
    `Force-push rotation branch` + `Open or update rotation PR` because
    its diff is empty (the rotated state is already on the branch from
    2026-06-10). PR #760 stays frozen indefinitely.
  - Shipping 8.1.1 through current `ship-release.yml` would either: (a)
    block preflight on PR #760's unmergeable state, or (b) merge PR #760
    and silently resurrect the deleted docker/openemr/* tree.
  - Workstream 1's PR 1a updates `PullRequestTarget::forRelease()` to
    return 2 targets (Conductor + Docs only). After workstream 1,
    `ship-release.yml` looks for only those two PRs; PR #760 becomes a
    dangling ignored OPEN PR (cosmetic; close manually anytime).
- **Workstreams 2, 3, 5 can run in parallel with each other** (different
  repos / different files / no critical-path overlap)
- **Workstream 4 (8.1.1 ship) unblocks the moment workstream 1 lands**
  — happens whenever 8.1.1 is ready to go after that
- **Workstream 6 (patch-prep automation) shares its mutator framework with
  workstream 2** — PR #12697 is based on PR #12696's branch. Lands after
  workstream 2 lands and rebases.
- **Workstream 7 (the actual migration) starts after 1-6 are well in hand**

**Updated execution timeline:**

1. Land workstream 1 (PR 1a coordinated with PR 1b — see Phase 1 detail
   below). Close PR #760 as cosmetic cleanup after workstream 1 merges.
2. 8.1.1 ships (workstream 4) — `gh workflow run ship-release.yml --repo openemr/openemr-devops -f version=8.1.1 -f rel_branch=rel-810`
3. Workstreams 2, 3, 5, 6 proceed in parallel after 8.1.1 ships (or
   alongside, if appetite for concurrent work). Workstream 6 rebases
   onto landed workstream 2.
4. Workstream 7 (actual migration) begins when 2/3/5/6 are stable

This re-order has the load-bearing property that **most workstreams are
not migration work** — they're net-new optimizations on tooling already in
`openemr/openemr` core. The migration shrinks to "move the post-tag
devops workflows over" rather than "everything moves at once."

### Workstream 2 detail — branch-cut automation (rel-820 readiness)

**STATUS: SHIPPED 2026-07-01; first production exercise 2026-07-02.**
Merged as openemr/openemr#12696
(merge commit `6513b487b6`) — the entire workstream as a single PR:
new `openemr:branch-cut` command, `.github/workflows/branch-cut-automation.yml`,
and 4 new mutators (`DockerUpgradeScaffoldMutator`,
`DockerfileOpenemrVersionMutator`, `TranslationFileCopyFromPriorRelMutator`,
`BranchCutReleaseTargetsMutator`) plus tests. Reality matched plan
closely; the mutator audit held up. Rabbit-fix follow-up shipped in
openemr/openemr#12709 (merge `8a01001a27`) — 3 Major items missed at
the initial merge (App-token permission scoping, `persist-credentials: false` on 3 checkouts,
`endsWith` gate tightening). **First real end-to-end exercise fired at
the rel-820 cut on 2026-07-02** — took three attempts + three rounds
of fixes to land clean: #12722 + #12724 (round 1: missing
`--skip-globals`; release-prep `paths-ignore: docs/**` filter), #12731
(round 2: five mutator surface fixes — fsupgrade full-copy, empty-rel
early-return, drop rel-side `GlobalsIncMutator`, tag-only compose pin,
drop `imageDigest`), #12735 (round 3: skeleton newline, sql-scan
`priorOpenemrVersion` + ordering invariant, PR body template audit).
Fourth attempt clean; rel-820 landed via #12743 + #12744; downstream
Docker Hub / dockerhub README / demo_farm#168 all landed on schedule.

Prior state: cutting a new `rel-NNN0` branch was a manual git operation
that required coordinated by-hand updates on the new branch + on
master. Tracked as gaps G4 (master-scope mutator wiring) and G5
(no auto-trigger on cut) in the gaps doc.

**Design:** New workflow `.github/workflows/branch-cut-automation.yml`
on master with `on: create` trigger. Fires exactly once when a new
`rel-NNN0` branch is created (filter by
`github.event.ref_type == 'branch'` + regex match on the ref name).

Inside the job:

- Derive target version from the branch name
  (`branch-to-version.php` already does this — extends to handle the
  freshly-cut case where no tag exists yet, returning the base version)
- Open **two coordinated PRs** (same shape as the release-time
  partner PR pattern below):
  - **rel-NNN0-side**: small PR with the `docker/release/Dockerfile`
    ARG edit (`OPENEMR_VERSION=master → rel-NNN0`). CI overrides via
    `--build-arg`, so it's cosmetic for local builds — but worth
    keeping consistent with branch identity.
  - **master-side**: version.php advance via `VersionPhpMasterMutator`
    (existing), add the rel-NNN0 row to `release-targets.yml` via a
    new `AddReleaseTargetsRowMutator`, rotate `next` from master to
    rel-NNN0, add the SQL skeleton + bridge-file-rename dance via a
    new `SqlSkeletonAdvanceMutator`.

**Interaction with the conductor:** `release-prep.yml` ALSO fires on
the cut push (it has `on: push:` matching `rel-[0-9]*0`). On a
freshly-cut rel-NNN0, the conductor will open a premature
`release-prep/rel-NNN0` draft PR suggesting 8.X.0 release content.
**Accept this** — the draft PR stays mostly inert through the dev
cycle, re-rendered on each push, eventually marked Ready + merged
when 8.X.0 actually ships. Suppressing the conductor's first run
adds non-trivial logic with edge cases; the visual nuisance is small.

**Refinement (2026-06-30) — audit reduces new code burden + concrete plan:**
Audit of existing mutators (in `src/Common/Command/ReleasePrep/Mutator/`)
revealed that 5 of them already do exactly what branch-cut needs on
master-side and rel-side: `VersionPhpMasterMutator` (already exists),
`OpenApiVersionMutator` (already exists), `SwaggerRegenMutator` (already
exists), `SqlUpgradeSkeletonMutator` (already exists, scaffold the new
SQL upgrade stub), `GlobalsIncMutator` (already exists, flips the
`allow_debug_language` global). Workstream 2 implementation only needs
**4 new mutators**: `DockerUpgradeScaffoldMutator` (both sides — 3
docker-version bumps + new `fsupgrade-N.sh` stub + Dockerfile manifest
update per PRs #12608/#12609), `DockerfileOpenemrVersionMutator`
(rel-side, flip `ARG OPENEMR_VERSION=master → rel-NNN0`),
`TranslationFileCopyFromPriorRelMutator` (rel-side, copy
`contrib/util/language_translations/currentLanguage_utf8.sql` from prior
rel branch), `BranchCutReleaseTargetsMutator` (master-side, sibling to
G11's PostReleaseTargetsMutator: adds the new rel row with `next` tag,
drops `next` from master row + bumps minor, removes any `unreleased:
true` rows — uniformly handles both normal-cut and skip-line-cut paths).

**Slot dynamics at cut:** rel-NNN0 takes the `next` slot from master
(rel-820 row gets `docker_tags: 8.2.0,next`; master row drops `next` +
bumps from `8.2.0,dev,next` → `8.3.0,dev`). Master only has `next` in
the interim when no other rel branch holds it (covered by both the
"normal cut" path after G11's PostReleaseTargetsMutator runs at the
prior release's ship, AND the "skip-line cut" path where the maintainer
flagged the prior rel branch's rows as `unreleased: true`).

**Command + workflow shape:** new sibling command `openemr:branch-cut`
(NOT a `--scope=branch-cut` extension to `openemr:release-prep`; Phase A
in G11 established `--scope=master` as release-time only). Takes
`--target-version`, `--rel-branch`, `--prev-rel-branch`, internal
`--side=rel|master` for mutator-list selection. New workflow
`.github/workflows/branch-cut-automation.yml` on master, `on: create:`
filtered to `refs/heads/rel-[0-9]*0` (plus `workflow_dispatch:` escape
hatch). Same dual-checkout / peter-evans pattern as Phase A.

**Implementation goal: ONE PR for the entire workstream 2** (new
command + 4 new mutators + new workflow + tests). Easier review for
OpenEMR admins — the pieces only make sense together. Full file-level
inventory + mutator audit in gaps doc G5 "Refinement (2026-06-30)"
subsection.

**Conditional sequencing — resolved 2026-07-01 (revised):** 8.1.x is
being skipped entirely — no 8.1.1 ship. All three workstreams
(2 / 3 Phase A / 6) landed to master on 2026-07-01, and the rel-820
cut becomes the first end-to-end exercise of the full paired flow.
Pre-cut posture landed in openemr/openemr#12712: rel-810 rows both
marked `unreleased: true` (dropped by `BranchCutReleaseTargetsMutator`
at cut time), `next` moved to master interim (rel-820 acquires it via
the mutator's row insert). First production exercise of **branch-cut
automation** = the rel-820 create event itself. First production
exercise of **release-prep + release-finalize together** = 8.2.0
shipping from rel-820. First production exercise of **patch-prep
automation** = rel-820's `8.2.0 → 8.2.1-dev` transition, whenever
that happens. Workstream 3 Phase B (cherry-pick to rel-810) is
therefore unnecessary — rel-820 inherits all three automations
in-place since it's cut from current master.

### Workstream 3 detail — release-time partner PR + release-cycle-bot

Currently: per-release work on a rel branch (P1-P4 in the canonical
sequence) is fully manual edits to docker upgrade machinery,
version.php, and release-targets.yml. Plus a post-release manual PR
to master for the release-targets.yml slot shuffle + ref pin (P6).

**Two sub-components:**

#### Release-cycle-bot (P1-P4 automation)

Single CLI: `openemr-release-cycle <X.Y.Z> <rel-branch>` generates
the four prep PRs needed before the conductor opens its release-prep
PR:

- P1: docker upgrade machinery on the rel branch (3 docker-version
  bumps + new fsupgrade-N.sh + Dockerfile two-block manifest)
- P2: same on master (cross-branch sync requirement)
- P3: version.php bump on the rel branch (X.Y.(Z-1) → X.Y.Z-dev)
- P4: release-targets.yml row update on master (docker_tags +
  openemr_version_ref)

Each is 100% mechanical given the version + rel branch. Compresses
hours of manual work to a single command. Folds in G7 (rel-810
surgical conductor sync) and G8 (regression tests).

#### Release-time partner PR (post-release automation)

Extend the conductor to open a SECOND PR — `release-finalize/<rel-branch>`
— on **master**, paired with the existing `release-prep/<rel-branch>`
PR on the rel branch. The second PR carries the post-release
release-targets.yml mutations:

- Pin the rel branch's `openemr_version_ref` to the new tag (e.g.,
  `rel-810 → v8_1_1`)
- Slot shuffle across rows (next → latest promotion + previous-latest
  drops + next moves master-ward or to a newer rel branch)
- Drop the unreleased placeholder row (per the multi-row mechanism
  added in openemr/openemr#12656)

**Lifecycle:**

1. Conductor fires on push to the rel branch → opens BOTH PRs as
   drafts
2. Maintainer marks both Ready
3. ship-release.yml merges conductor PR → tag fires → openemr-tag
   dispatch consumer either auto-merges the master partner PR, or
   marks it Ready for manual merge

Full design in gaps doc G11.

**Phase A — STATUS: SHIPPED 2026-07-01** as openemr/openemr#12662
(merge commit `abb1e2d940`). Delivered as planned: `PostReleaseTargetsMutator`
+ `ReleasePrepCommand` extension (`--rel-branch` option, master mutator
list) + conductor workflow extension (second checkout/run/PR cycle
against master). Reality matched plan — release-finalize partner PR
on master is now paired with the existing release-prep PR on the rel
branch. For the 8.1.1 ship, Phase B (cherry-pick the conductor + PHP
changes to rel-810) still needs to land ahead of the tag. Auto-merge
of the master partner PR on `openemr-tag` remains a stretch goal
deferred to follow-up — for 8.1.1, maintainer will mark Ready + merge
manually after the tag fires.

**The "partner PR" pattern generalizes** to branch-cut (workstream
2). Same shape: one workflow opens two coordinated PRs targeting
different branches; both drafts → mark Ready → merge. Phase A's
shared infrastructure (extended MutatorContext with `relBranch`,
dual-checkout workflow structure, peter-evans pattern) is designed
so workstream 2's branch-cut mutators inherit the framework.
Workstream 2's instance: cut detection fires → opens post-cut PRs
on the new rel branch (e.g., copy translation file from prior rel,
turn off dummy-translation global) + opens post-cut PR on master
(bump version.php to next-dev via `VersionPhpMasterMutator`, which
stays as a dormant class until workstream 2 wires it).

### Workstream 5 note — demo farm release mechanism is changing

**STATUS: SHIPPED 2026-06-28.** All G6 work landed across three repos:
demo_farm_openemr (#135 scaffold, #138 write+PR, #141 atomic flip retiring
`bump-tag.yml` + `tools/release/`, #142 printf-dash fix, #143 first real
reconciliation PR; plus #136 dependabot, #139 shellcheck ratchet, #140 first
ratchet issue), openemr-devops (#846 canonical schema event), openemr/openemr
(#12657 vendored schema + workflow firing on push to release-targets.yml).
Bonus phpstan plumbing: #12658 + #12659. The bot is operational end-to-end:
daily 07:00 UTC cron + immediate `repository_dispatch types=release-targets-changed`
from openemr master + manual `workflow_dispatch`. Opens/updates a single stable
PR `auto-derive/reconciliation` on diff; closes it on no-diff. See gaps doc G6
for the full shipping notes (vendor sync dance, phpstan flake root cause,
bash printf `-` trap, PR-rebase pickup of master workflow fixes).

The `demo_farm_openemr` release mechanism (today: `bump-tag.yml`
matches production rows by `MAJOR.MINOR` and updates them on
`openemr-tag`; cluster-to-flex-image mapping is hand-maintained) is
itself going to change as part of workstream 5 / gap G6.

The auto-generation vision (per maintainer 2026-06-23, design
refined 2026-06-27): derive the entire `ip_map_branch.txt` +
`demoLibrary.source` from upstream openemr/openemr state, with a
single hand-curated "Miscellaneous" section reserved for non-standard
demos. **Reconciliation, not from-scratch render** — cluster
identity is sticky across runs because each cluster name maps to a
subdomain (e.g., `eight` → `eight.openemr.io`) referenced from
external surfaces (wiki, social, mail). The bot preserves
cluster→subdomain stability by reading current state + applying a
reconciliation diff. Each section's derivation rule (full matrix in
gaps doc G6):

- **Production** (cluster `five` + aliases): from the rel branch
  holding `latest` in release-targets.yml; flex from that branch's
  Dockerfile ARGs
- **Up-for-grabs** (cluster `four` + aliases): branch preserved
  (community claims); flex always from **master's** Dockerfile
- **Master demos** (clusters `one`/`two`/`seven`/...): sticky from
  prior state; new ones from parked when a PHP version is added
- **Release demos** (clusters `three`/`six`/`eight`/...): sticky
  from prior state; new ones from parked when a rel branch appears
- **Parked**: dynamic bench (overflow + retired-from-active)
- **Miscellaneous**: hand-curated, bot never touches

Bot trigger composition: daily cron (load-bearing self-healing) +
eager `repository_dispatch` consumers (openemr-tag /
openemr-rel-cut / openemr-rel-update) + manual workflow_dispatch.
Implementation: bash + yq + curl + awk; demo_farm self-contained.
Three-PR scaffolding plan (dry-run → live PR → eager dispatch +
unreleased skip).

Implication for workstreams 2 + 3 above: the release-time partner
PR + branch-cut automation should be **demo-farm-aware** — when the
new demo_farm bot exists, it consumes the same release-targets.yml
state that the partner PR mutates. Coordination boundary:
`release-targets.yml` is the contract between master-side automation
and the demo_farm bot. As long as the partner PR + branch-cut
automation correctly produce `release-targets.yml` state, the
demo_farm bot rederives correctly. **No tight coupling at the
workflow level** — they're decoupled via the data file.

Full design (algorithm, per-input source map, section ownership
matrix, edge cases, 3-PR scaffolding plan) in gaps doc G6.

### Workstream 6 — patch-prep automation

**STATUS: SHIPPED 2026-07-01.** Merged as openemr/openemr#12697
(merge commit `c1af6370a0`), directly following workstream 2's #12696
merge earlier the same day. Delivered as scoped: new `openemr:patch-prep`
command + `.github/workflows/patch-prep-automation.yml` + 2 new mutators
(`MasterSqlPatchBridgeMutator`, `PatchPrepReleaseTargetsMutator`) +
strictly-additive `MutatorContext::$fromVersion` extension. Reality
matched plan; branch-cut callers unaffected by the context extension
(back-compat by construction, as designed). First production exercise
fires at the patch after 8.1.1 (rel-810's `8.1.1 → 8.1.2-dev`
transition), pending the Phase B cherry-pick to rel-810 discussed in
workstream 2's "Conditional sequencing" note above.

**What it automates.** The per-patch-cycle bootstrap work that mirrors
workstream 2's per-cut bootstrap. When a maintainer bumps `$v_patch` in
`version.php` on a `rel-*` branch — e.g., rel-810 going `8.1.0` →
`8.1.1-dev` to open the next dev cycle — the workflow opens **two
coordinated ready-for-review PRs** (not drafts; patch-prep PRs should
land fast, paving the way for the new dev cycle):

- **Rel-side** (`patch-prep/<rel-branch>` → base `<rel-branch>`): docker
  upgrade scaffolding (3 docker-version files bumped + new
  `fsupgrade-(N+1).sh` stub + Dockerfile manifest update in BOTH the
  `COPY upgrade/...` block AND the `RUN chmod 500 ...` block) + new SQL
  upgrade skeleton `sql/X_Y_(P-1)-to-X_Y_P_upgrade.sql`.
- **Master-side** (`patch-prep/<rel-branch>-master` → base `master`):
  same docker upgrade scaffolding (cross-branch sync) + same new SQL
  upgrade skeleton (mirrored from rel) + master bridge-file rename
  (`sql/X_Y_(P-1)-to-X_(Y+1)_0_upgrade.sql` → `sql/X_Y_P-to-X_(Y+1)_0_upgrade.sql`,
  contents preserved) + `.github/release-targets.yml` row insert (new
  dev row for the next patch) + drop any `unreleased: true` placeholder
  rows for that rel branch.

**Mutator reuse from workstreams 2 + 3.** No code duplication —
patch-prep is a new conductor command (`openemr:patch-prep`) over the
existing mutator framework:

- `DockerUpgradeScaffoldMutator` — reused as-is from workstream 2. Same
  scaffold operation both sides, same cross-branch sync requirement.
- `SqlUpgradeSkeletonMutator` — reused with extension. Patch-prep
  introduces `MutatorContext::$fromVersion` (optional, `MAJOR.MINOR.PATCH`)
  because rel-side `version.php` has already been bumped at workflow
  trigger time — the post-bump `$v_patch` is the *target*, not the
  *from*. The mutator falls through to its existing version.php-derived
  behavior when `fromVersion` is null, so workstream 2's branch-cut
  callers don't need updates. **Back-compat by construction.**
- 2 new mutators introduced by this workstream:
  - `MasterSqlPatchBridgeMutator` — master-side. Renames the long-lived
    bridge file as above; preserves the file body verbatim (it
    accumulates in-flight dev-cycle SQL). Reports BOTH old + new paths
    in `MutatorResult::changedFiles` since rename = delete + create.
  - `PatchPrepReleaseTargetsMutator` — master-side. Inserts the new dev
    row for the target rel branch (`docker_tags: <target>,next`) and
    drops `unreleased: true` rows scoped to that same rel branch only
    (unlike `BranchCutReleaseTargetsMutator` which drops uniformly).
    Same line-based surgical-edit approach as the existing release-targets
    mutators to preserve comments; Symfony YAML parser used as a
    structural sanity check on the result.

**Trigger.** `on: push` to `branches: rel-*` with `paths: version.php`.
A resolver step then:

1. Skips cleanly on branch-creation events (`before` SHA all zeros) —
   workstream 2 owns that lifecycle event.
2. Fetches before-state + after-state `version.php` via the GitHub API
   (`github.event.before` vs `github.event.after`) and parses
   `$v_major`, `$v_minor`, `$v_patch`, `$v_tag`.
3. **Gate 1**: `$v_patch` must have strictly increased. Cosmetic edits
   to other parts of `version.php` skip cleanly.
4. **Gate 2**: after-state `$v_tag` must be `-dev`. Release-prep's
   mid-flight `-dev` strip during a ship flow also bumps `$v_patch` in
   some scenarios; that's not our event.
5. **Gate 3**: same major + same minor before/after (catches edge
   cases).

If all gates pass, derives `target_version = MAJOR.MINOR.after_patch`
and `prev_version = MAJOR.MINOR.before_patch`, then runs the conductor
both sides via dual-checkout + peter-evans pattern (same as workstream
2 and workstream 3 Phase A).

`workflow_dispatch` escape hatch with explicit `rel-branch`,
`target-version`, `prev-version` inputs for manual recovery from a
workflow miss.

**PR shape.** Ready-for-review (not draft). Mirrors workstream 2's
branch-cut PRs in this respect — bootstrap work should be reviewed and
merged fast. Branch names: `patch-prep/<rel-branch>` (rel-side) and
`patch-prep/<rel-branch>-master` (master-side).

**Lifecycle relationship to workstreams 2 + 3.**

| Lifecycle event | Workstream | Trigger |
|---|---|---|
| Minor branch cut (`rel-NNN0` created) | 2 | `on: create:` for `rel-NNN0` refs |
| Patch dev cycle start ($v_patch bump on rel branch) | **6** | `on: push:` `rel-*` w/ `paths: version.php` + resolver gate |
| Per-release ship (rel branch merges to tag) | 3 | conductor `on: push:` `rel-*` (existing) |

Together, workstreams 2 + 3 + 6 cover the full release-lifecycle
automation surface. Workstream 2 fires once per minor at cut time;
workstream 6 fires once per patch at dev-cycle start (between ships);
workstream 3's conductor + release-finalize fires once per ship.

**Implementation goal: ONE PR (#12697)** for the entire workstream 6
(new command + 2 new mutators + `MutatorContext` extension + new
workflow + tests). Same single-PR rationale as workstream 2 — the
pieces only make sense together.

Full design (gates, sample diffs both sides, mutator order, edge cases)
in gaps doc G12.

The remaining sections of this doc detail workstream 7's internals (the
actual migration; formerly numbered workstream 6 before the 2026-06-30
renumber for patch-prep) with the original Phase-1-through-6 structure.
Workstreams 1-6 details + tracking live in separate planning artifacts
(gaps doc for G4/G5/G6/G7/G8/G11/G12, plus per-workstream sub-issues
when they enter active development).

## Proposed model

```
                       openemr/openemr (single source of truth)
                       │
       Maintainer ── triggers ──┐
                                │
                                ▼
   release-prep.yml ── push → release-prep/<rel> PR ── merge → annotated tag
                                                                    │
                                                                    ├─ self-dispatch openemr-tag (internal)
                                                                    │   │
                                                                    │   ├─→ build-release-on-tag.yml → distribution pkgs + GitHub Release
                                                                    │   └─→ release-announcements.yml → per-channel draft artifacts
                                                                    │
                                                                    └─ cross-repo repository_dispatch (external consumers)
                                                                        │
                                                                        ├─→ openemr/website-openemr (docs PR)
                                                                        └─→ openemr/demo_farm_openemr (direct push)

   ship-release.yml ── workflow_dispatch ── merges {conductor, docs} PRs in order
                                                       │
                                                       └─ posts release/ship-approved status
```

Two key changes from today:

1. **`build-release-on-tag.yml` + `release-announcements.yml` consume the
   `openemr-tag` event from inside the same repo** (via `repository_dispatch`
   self-dispatch, or `workflow_run` after release-prep's `finalize` job).
   No cross-repo hop.
2. **`ship-release.yml` lives here** and coordinates 2 PRs (conductor + docs)
   rather than 3 (infra + conductor + docs). The Infra PR is eliminated in
   Phase 1 because `release-rotation/auto`'s reason for existing went away
   with the docker migration. Locked decision; not a deferred question.

External consumers (`website-openemr`, `demo_farm_openemr`) continue to
receive `repository_dispatch` cross-repo. Their vendored copies of the
contract follow `openemr/openemr` as the new canonical source.

`build-patch.yml` is **deliberately not part of the automated flow above**.
It's a manually-invoked emergent-patch escape hatch for zero-day-style
hotfixes -- the maintainer runs it, the workflow produces a diff zip + tag
+ GitHub Release, and the maintainer manually distributes the artifact.
It moves to `openemr/openemr` alongside the other tooling but stays
intentionally inert (no `openemr-tag` dispatch, no downstream consumers
wired). Standard openemr-consumer upgrade flow goes through full minor
releases (8.1.0 → 8.1.1 → 8.1.2 from rel-810) via the conductor + tag +
build-release-on-tag chain, not the patch path.

## Pre-Phase-1 architectural decision: per-branch copies vs reusable workflows

**Open question that shapes every phase below.** As of 2026-06-23 the
release-mechanism migration is structurally similar to the docker-pipeline
migration: workflows + supporting PHP tooling live per-branch in
`openemr/openemr`, and rel branches carry their own copies that can drift
from master. The docker pipeline manages drift via the byte-identical
canary system (FILES_ALL + auto-sync + classification). The
release-mechanism doc originally adopted the same "per-branch copy,
divergence tolerated" pattern, with byte-identity explicitly NOT
enforced for release tooling (per the gaps doc's decisions-made section).

**The first production-use of the conductor (8.1.1 prep, 2026-06-23)
exposed why that decision deserves consideration.** Bug G7 — rel-810's
`BranchVersionResolver` had drifted from master's tag-walking version
without being noticed for weeks. The fix backported master's class to
rel-810 only (openemr/openemr#12611). rel-800 and rel-704 still carry
the old version but are out of scope (per maintainer 2026-06-23 —
those branches will rotate out without future releases). So the
practical drift surface is just rel-810.

**Two options for migrated workflows + tooling going forward:**

1. **Per-branch copies (status quo extended).** Every workflow being
   migrated (`build-release-on-tag`, `ship-release`,
   `release-announcements`) lands on every rel branch via copy. Drift
   managed via either (a) ad-hoc "remember to backport," (b) the
   byte-identical canary's FILES_ALL set expanded to cover the new
   files, or (c) tolerated divergence with manual reconciliation when
   bugs emerge. Today's pattern is (c); G7 is the cost of that choice.
2. **Reusable workflows (caller + impl split).** Master owns the *real*
   workflow implementations (`*-impl.yml`); rel branches carry only
   thin caller stubs (`~10 LOC`) invoking master's impl via
   `uses: openemr/openemr/.github/workflows/...-impl.yml@master`.
   Supporting PHP classes live on master only; the impl workflow
   checks out master's `tools/release/` tree explicitly, regardless of
   which branch fired the caller. Drift impossible by construction.

**Architectural tradeoffs:**

| Aspect | Per-branch copies | Reusable workflows |
|---|---|---|
| Drift | Possible (G7-class bugs) | Eliminated by construction |
| Master bug fixes reach rel branches | After explicit backport PR per branch | Immediately on master merge |
| PR queue per rel branch | Sync PRs accumulate (if canary used) | Minimal (only thin caller stubs change) |
| Ad-hoc inspection (`cat` on a rel-branch workflow) | Shows real logic | Shows a stub pointing at master |
| Per-branch override flexibility | Native (edit the copy) | Requires editing caller's `uses:` ref |
| Setup cost during migration | Lower (copy existing pattern) | Higher (caller/impl decomposition per workflow) |
| Surface that needs byte-identity enforcement | Whole workflow + tooling files | Only the thin caller stubs (~3-5 files per branch) |

**Decision locked 2026-07-07: option 1 with byte-identical enforcement
(docker-pipeline pattern extended to release machinery).** Formal tracking
lives in gaps doc as G15; full reasoning + file-set sizing there.

Summary of the lock in weight order:

1. **Production-validated this session.** Track A openemr/openemr#12778
   landed on master → sync-byte-identical fired → produced
   #12787/#12788/#12789 auto-sync PRs against rel-820/800/704 within 13
   minutes. Zero manual per-branch work. Real evidence, not theory.
2. **Lower migration risk.** Reusable workflows would be a structural
   refactor per workflow — adds complexity to the migration itself. Byte-
   identical is "add files to FILES_ALL" — trivial.
3. **Same team mental model.** Byte-identical already understood from
   docker; reusable workflows introduce caller/impl decomposition + cross-
   branch action refs that nobody in this codebase has used.
4. **Doesn't preclude reusable workflows later.** They're an optimization
   on top of byte-identical; can land as a follow-up refactor without
   redoing migrated work. Zero lock-in.
5. **Sync PRs stay review-gated** (auto-merge intentionally off in
   sync-byte-identical.yml). Drift prevention without giving up substantive
   review.
6. **Preserves per-branch customization escape hatch.** If a specific
   rel-* ever needs legitimately divergent build behavior, remove the file
   from FILES_ALL (or move it to the opt-out comment block). Reusable-
   workflow equivalent is much more restrictive.

**File-set sizing:** Only Phase 2 (build-release + build-release-on-tag +
build-patch) fires from rel-* branches, so only Phase 2 adds to FILES_ALL.
Phases 3-6 are master-only. Additions ~20-25 files (3 workflows +
`tools/release/**` glob covering the PHP tree). Post-migration FILES_ALL
totals ~30 files (current docker set is 8).

**Recommended FILES_ALL shape at Phase 2 kickoff:**

```yaml
files:
  - .github/workflows/build-release.yml
  - .github/workflows/build-release-on-tag.yml
  - .github/workflows/build-patch.yml
  - tools/release/**            # whole tree byte-identical
```

Enumerate the workflows individually (only 3 fire from rel-*); use a
glob for the PHP tree so per-file curation isn't needed as classes
churn during Phase 2/3/4 development. Cost: some ship-release /
announcement PHP classes end up as dead code on rel-* branches (their
workflows never fire from there). ~10-15 KB dead weight per rel-* —
trivial. Verify sync-byte-identical.yml handles glob expansion cleanly
at Phase 2 kickoff.

**G10 (future consideration in gaps doc):** the docker pipeline's own
canary could be retired if reusable workflows were ever adopted for
release-mechanism. That path narrowed by locking byte-identical here —
G10 stays open as a separate future consideration if reusable-workflow
appetite ever materializes.

## Validated foundation

Empirical findings the plan rests on (audit:
[`/tmp/release-mechanism-audit.md`](file:///tmp/release-mechanism-audit.md)
generated 2026-06-20 by sweep against `openemr-devops` at `eb1008c0`,
post-docker-migration phase 5):

- **Rotation slice has zero live targets in devops.** All 13 entries in
  `versions.yml :: files:` reference paths that no longer exist
  (`docker/openemr/**`, `utilities/container_benchmarking/**`, two deleted
  workflows). The dependabot.yml entry rotates docker-dir directives that were
  pruned. The next `openemr-rel-cut`/`-update`/`-tag` dispatch crashes
  `SlotRotator::rotate()` at `SlotRotator.php:74` on the first missing file.
  **This is a live regression.**
- **The conductor (`release-prep.yml`) is already in this repo as
  net-new tooling** built here in preparation for this migration — not
  migrated from devops (devops never had it). It mutates `version.php`,
  `library/globals.inc.php`, `docker/production/docker-compose.yml`,
  `src/RestControllers/OpenApi/OpenApiDefinitions.php`, `swagger/openemr-api.yaml`,
  and every `docker-version` file via a path-agnostic sweep — all targets are
  in this repo and unaffected by the docker migration. The release-prep
  half of the destination state is in place; the migration's remaining
  job is to move the post-tag pieces (`build-release-on-tag`,
  `ship-release`, `release-announcements`) over and retire devops's copies.
- **First production-use of the conductor (8.1.1 release prep,
  2026-06-23) surfaced an off-by-one bug** in rel-810's
  `BranchVersionResolver::branchToVersion()` — the old static form
  decomposed the branch name without walking tags, returning `8.1.0`
  for `rel-810` regardless of the existing `v8_1_0` tag. Fixed in
  openemr/openemr#12611 by backporting master's tag-walking
  instance method. The bug had been latent on rel-810 since 8.1.0
  shipped (every conductor fire dispatched the wrong VERSION to
  consumers, but consumers absorbed the no-op until rel-810 HEAD's
  content diverged from 8.1.0 content). This is **gap G7** in the
  gaps doc (per-branch tooling drift) — read the deferred-debt
  section below for how it shapes the migration.
- **Contracts are already vendored into this repo.**
  `tools/release/contracts/dispatch.schema.json`,
  `tools/release/src/TagVerifier.php`, `tools/release/src/TagVerificationResult.php`
  exist here as vendored copies pointing back at devops as canonical owner.
  Migration just inverts the canonical/vendored relationship.
- **The vendored-contract drift check IS NOW wired into openemr-core's CI**
  as of #12619 (2026-06-28). Devops's `check-vendored-contracts.yml` is a
  reusable workflow; openemr-core's `.github/workflows/check-vendored-contracts.yml`
  is the thin caller. Verified live by openemr#12657 (added
  `release-targets-changed` event) — the drift check correctly failed until
  the canonical was updated via openemr-devops#846, then passed after rebase.
  Originally tracked as a pre-existing bug; closed by workstream 1.
- **`tools/release/`'s architecture pattern is documented** in
  `openemr-devops/tools/release/README.md`: PHP 8.5 + PHPStan level 10 + strict
  rules, thin workflow + thick PHP, `gh` CLI as network layer, interface +
  concrete + in-memory-fake for every API surface, one value object per result.
  The migration preserves the pattern.
- **`website-openemr` doesn't actually vendor the contracts** despite being
  mentioned in the schema docstring as a consumer (verified by clone). Only
  `openemr/openemr` is a real consumer; that simplifies the
  canonical-relocation seam.
- **The byte-identical canary + auto-sync system is production-validated**
  end-to-end (2026-06-21/22). FILES_ALL has 8 entries — the original 5
  (`docker-build-release.yml`, `docker-test-core.yml`, `docker-test-release.yml`,
  `test-actions-core/action.yml`, `.github/docker/compose.yml`) plus the
  canary's own trio (workflow, config, extracted+BATS-tested script). The
  auto-sync workflow (`sync-byte-identical.yml`) has demonstrably handled all
  five classification cases — identical / add / update / delete-as-rename /
  demote-skip — under real load (actions/checkout v6→v7 bump, compose.yml
  relocation). The docker pipeline is steady-state; no remaining docker-side
  infrastructure work blocks this release-mechanism migration.

## What moves where (concrete)

| Devops path | Destination in this repo | Notes |
|---|---|---|
| `.github/workflows/build-release.yml` | `.github/workflows/build-release.yml` | Already targets this repo's artifacts |
| `.github/workflows/build-release-on-tag.yml` | `.github/workflows/build-release-on-tag.yml` | `repository_dispatch` consumer → self-dispatch or `workflow_run` post-finalize |
| `.github/workflows/build-patch.yml` | `.github/workflows/build-patch.yml` | Already targets this repo. Parallel manual-only path; intentionally not wired to the automated release flow (no `openemr-tag` dispatch, no downstream consumers). Migrated as an emergent-patch escape hatch for zero-day-style hotfixes -- maintainer manually invokes, manually distributes the produced diff zip. |
| `.github/workflows/ship-release.yml` | `.github/workflows/ship-release.yml` | Coordinates this-repo PRs + cross-repo docs PR |
| `.github/workflows/release-announcements.yml` | `.github/workflows/release-announcements.yml` | `repository_dispatch` consumer → self-dispatch or `workflow_run` |
| `.github/workflows/release-tools-php.yml` | `.github/workflows/release-tools-php.yml` | CI for the moved PHP tooling |
| `tools/release/src/PackageAssembler.php` | `tools/release/src/PackageAssembler.php` | Distribution package builder |
| `tools/release/src/ChangelogGenerator.php` | `tools/release/src/ChangelogGenerator.php` | Same |
| `tools/release/src/PreflightChecker.php` | `tools/release/src/PreflightChecker.php` | Milestone + GHSA preflight |
| `tools/release/src/CompatibilityDeriver.php` + `CompatibilityNotesRenderer.php` | Same paths | Min-supported-versions derivation |
| `tools/release/src/AnnouncementRenderer.php` + `AnnouncementStepSummaryRenderer.php` | Same paths | Twig-driven announcement drafting |
| `tools/release/src/ShipReleaseOrchestrator.php` + `ShipReleaseOptions.php` + `ShipReleaseResult.php` + `ShipReleaseStepResult.php` + `ShipReleaseStepStatus.php` + `ShipReleaseRenderer.php` + `ShipReleaseSummaryRenderer.php` | Same paths | 3-PR (or 2-PR) merge orchestration |
| `tools/release/src/PullRequestApi.php` + `GhPullRequestApi.php` + `PullRequestTarget.php` + `PullRequestSnapshot.php` + `PullRequestReadiness.php` + `PullRequestState.php` + `RoleLabel.php` | Same paths | gh-PR abstraction layer |
| `tools/release/src/AppPermissionApi.php` + `AppPermissionProbe.php` + `AppPermissionResult.php` + `GhAppPermissionApi.php` | Same paths | Permission probe (per-repo, also stays in devops) |
| `tools/release/src/GitHubApi.php` + `GhTokenSource.php` + `Clock.php` + `SystemClock.php` | Same paths | Shared infrastructure |
| `tools/release/src/TagDispatchPayload.php` + supporting fakes | Same paths | Dispatch payload parsing |
| `tools/release/contracts/dispatch.schema.json` | Already here (becomes canonical) | Vendoring inverts |
| `tools/release/src/TagVerifier.php` + `TagVerificationResult.php` | Already here (becomes canonical) | Vendoring inverts |
| `tools/release/src/VendoredFileChecker.php` + `VendoredDriftIssue.php` | `tools/release/src/` | Moves; flips which paths are canonical-vs-vendored |
| `tools/release/bin/*.php` | `tools/release/bin/` | All ~20 CLI scripts move |
| `tools/release/templates/full-checklist.md` + `patch-checklist.md` + announcement Twig templates (`forum.md.twig`, `chat.md.twig`, `x.md.twig`, `facebook.md.twig`, `linkedin.md.twig`, `mail.html.twig`, `mail.eml.twig`, `mail.subject.txt.twig`, `step-summary.md.twig`) + summary templates | Same paths | All ~12 templates move |
| `tools/release/tests/**` + `tests/Fakes/**` + `tests/fixtures/{dispatch,vendored}/**` | Same paths | Test suite + in-memory fakes + fixture dirs |
| `tools/release/Taskfile.yml` (release:* tasks) | Merge with this repo's Taskfile if present, else create | Glue between workflows and PHP CLIs |
| `tools/release/composer.json` + `composer.lock` + `phpstan.neon` + `phpcs.xml` + `rector.php` | Same paths | Module config; PHP 8.5 + level 10 + strict |

## What dies as part of the migration

| Surface | Reason |
|---|---|
| `.github/workflows/release-rotation.yml` | No live rotation targets post-docker-migration |
| `tools/release/src/SlotRotator.php` (~265 LOC) | Same |
| `tools/release/src/SlotAssignmentParser.php` (~100 LOC) | Same |
| `tools/release/src/SlotRotationResult.php` | Same |
| `tools/release/src/RotationPrPublisher.php` (~120 LOC) | Same |
| `tools/release/bin/rotate.php` + `open-rotation-pr.php` | Same |
| `tools/release/tests/SlotRotatorTest.php` + `SlotAssignmentParserTest.php` (+ related fixtures) | Same |
| `tools/release/versions.yml` | The registry it indexes is gone |
| `tools/release/src/VersionsRegistryLinter.php` + `LintIssue.php` (~200 LOC) | Was never wired into devops CI (audit Q2 finding); pattern targets empty match-space post-docker-migration |
| `tools/release/bin/lint-versions.php` | Same |
| `tools/release/tests/VersionsRegistryLinterTest.php` | Same |
| `.github/actions/test-actions-core/action.yml` | Orphan in devops; was lift-and-shifted to this repo in phase 1b of docker migration; the devops copy has zero references in the repo (verified by grep) |
| `tools/release/Taskfile.yml` entries for `release:rotate` and `release:lint-versions` | Tasks for the deleted bin scripts |
| `.github/workflows/check-vendored-contracts.yml` + `tests/fixtures/vendored/**` + `.github/workflows/vendored-contracts-self-test.yml` | Reusable workflow moves to this repo when canonical does (or gets reshaped if no external consumer needs vendoring) |

**Net deletion: ~700+ LOC of PHP + workflow + registry from devops as a direct
consequence of the docker migration's completion.**

## What stays in `openemr-devops`

After Phase 6 (final devops cleanup), what's left in devops's release-related
surface:

- `.github/workflows/release-permissions-check.yml` — per-repo by design,
  each repo carries its own copy and probes the App's installed permissions
  against itself. Scope narrows post-migration (no cross-repo dispatch probe
  back to itself), but the workflow's purpose remains.

Everything else release-mechanism-related moves out. Devops's other content
(kubernetes, packages, raspberrypi, utilities/openemr-*, docker/obsolete,
mariadb-backup-manager, portainer) is unaffected.

## Phased plan (workstream 7 internals)

**Note:** This six-phase breakdown covers workstream 7's internals — the
actual devops→core migration. (Workstream 7 was previously numbered
workstream 6 before the 2026-06-30 renumber for patch-prep.) Workstreams
1-6 from the "Current execution order" section above happen first (most
of them in parallel) and are not detailed here. Phase 1 below is
essentially identical to workstream 1 (pre-migration cleanup); the rest
are workstream 7's interior phasing.

Six phases. Phase 1 is pre-migration cleanup (small PRs that don't change
behavior contracts). Phases 2-6 do the actual move with each phase preserving
the 3-PR (or post-collapse) ship-release contract throughout.

| Phase | What | Estimate |
|---|---|---|
| **1. Pre-migration cleanup + 2-PR collapse** | ✅ SHIPPED 2026-06-28. Three PRs landed: openemr-devops#835 (rotation deletion + 2-PR collapse), openemr/openemr#12631 (cross-repo docs collapsed), openemr/openemr#12619 (drift-check wiring). See "Phase 1 detail" section below. | ~1 day |
| **2. Move build-release + build-patch + supporting tooling** | Move `build-release.yml` + `build-release-on-tag.yml` + `build-patch.yml` + `PackageAssembler` + `ChangelogGenerator` + `PreflightChecker` + `CompatibilityDeriver`/`Renderer` + supporting tests/fakes to this repo. Self-dispatch the `openemr-tag` consumer side. Devops copies stay live until phase 6 (parallel-run validation window). | ~1.5 days |
| **3. Move ship-release** | Move `ship-release.yml` + `ShipReleaseOrchestrator` + `PullRequest*` classes + fakes + tests. Already in 2-PR shape (locked in Phase 1) so the port is mechanical — no contract change in this phase. Devops copy stays live until phase 6. | ~1 day |
| **4. Move release-announcements + permissions probe (core-side scope)** | Move `release-announcements.yml` + `AnnouncementRenderer` + templates. Add the openemr-core-specific permission probes to core's `release-permissions-check.yml`. Devops's permissions-check.yml narrows scope. | ~0.5 day |
| **5. Invert canonical/vendored** | Make this repo canonical for `dispatch.schema.json` + `TagVerifier` + `TagVerificationResult`. Move `VendoredFileChecker` + `check-vendored-contracts.yml` here. Update devops's vendored-copies-of-the-vendored-checker references. If website-openemr or demo_farm_openemr add vendoring later, they pull from here. | ~0.5 day |
| **6. Devops cleanup** | Delete the now-duplicate workflows + `tools/release/` (minus `release-permissions-check.yml`) from devops. Update devops README. Close issue #664 family. | ~0.5 day |

Total active engineering: **~5 days** assuming parallel-run validation windows
between each phase are quick.

### Phase 1 detail (SHIPPED 2026-06-28)

**All three PRs landed:** PR 1a = openemr-devops#835 (rotation deletion +
2-PR collapse), PR 1b = openemr/openemr#12631 (docs collapsed), PR 1c =
openemr/openemr#12619 (vendored-contract drift check wired). The drift
check was exercised in the wild within hours by openemr/openemr#12657 (G6
work added a new dispatch event), confirming the trio works end-to-end:
canonical updated in devops, vendored copy in openemr/openemr re-synced,
CI passed. Phase 1 closed.

Three PRs. **PR 1a (devops) and PR 1b (core) must land coordinated** — both
together or neither — because PR 1a deletes the rotation slice + collapses
ship-release to 2-PR shape, and PR 1b updates the cross-repo release docs to
match. Landing one without the other leaves docs and code disagreeing for a
window.

PR 1c (core, drift-check wiring) is independent of 1a/1b. Can land any time.

#### PR 1a (devops): Delete rotation slice + collapse ship-release to 2-PR shape (atomic)

Scope: in one PR on devops master, simultaneously (a) delete the dead
rotation surface that has zero live targets post-docker-migration, and
(b) update `ShipReleaseOrchestrator` + `PullRequestTarget::forRelease()` to
expect 2 PRs (Conductor + Docs) instead of 3 (Infra + Conductor + Docs).
Atomic so ship-release.yml never lands in a state where it's looking for an
Infra PR that doesn't exist.

The 2-PR collapse is permitted by the docker migration's completion: the
Infra PR's only job was rotating docker pins + CI matrices that no longer
live in devops. With nothing left for it to rotate, an always-empty Infra
PR adds zero value to the release flow.

Files deleted:

- `tools/release/versions.yml` (registry pointing at deleted paths)
- `tools/release/src/SlotRotator.php` (~265 LOC)
- `tools/release/src/SlotAssignmentParser.php` (~100 LOC)
- `tools/release/src/SlotRotationResult.php`
- `tools/release/src/RotationPrPublisher.php` (~120 LOC)
- `tools/release/src/VersionsRegistryLinter.php` (~95 LOC) + `LintIssue.php`
- `tools/release/bin/rotate.php` + `lint-versions.php` + `open-rotation-pr.php`
- `tools/release/tests/SlotRotatorTest.php` + `SlotAssignmentParserTest.php`
  + `VersionsRegistryLinterTest.php` + related fixtures
- `.github/workflows/release-rotation.yml` (workflow itself)
- `docs/release-automation-plan.md` (devops slice plan — pre-implementation
  design doc for the rotation slice; once the rotation code is gone, the
  design doc is misleading scaffolding pointing at nothing)

Files modified:

- `tools/release/Taskfile.yml` — drop the deleted task entries
  (`release:rotate`, `release:lint-versions`, anything else that called the
  removed bin scripts)
- `tools/release/src/PullRequestTarget.php` — `forRelease()` returns 2
  targets instead of 3 (`Conductor:1, Docs:2`); update `mergeOrder` constants
- `tools/release/src/RoleLabel.php` — drop the `Infra` enum case
- `tools/release/src/ShipReleaseOrchestrator.php` — adjust any hardcoded
  3-target assumptions; verify the docs-first detection still works with
  Conductor-first instead of Infra-first
- `tools/release/src/PullRequestTarget.php` tests, plus
  `ShipReleaseOrchestratorTest.php`, plus any fakes that pre-seed 3 PRs —
  drop Infra from fixtures + assertions
- `.github/workflows/ship-release.yml` — no functional change expected
  (workflow just calls `task release:ship`), but verify

Behavior contract preserved post-PR:

- `gh workflow run ship-release.yml --repo openemr/openemr-devops -f version=X.Y.Z -f rel_branch=rel-XY0` — same command, but the orchestrator now merges 2 PRs (Conductor → Docs) instead of 3. Maintainer-visible difference: one less PR to scan in the step summary.
- All artifacts unchanged (tag, Release, Docker Hub publishes, announcements)
- `repository_dispatch` events unchanged (the conductor still emits them; the rotation workflow just doesn't consume them anymore — devops still receives but no-ops)
- Devops still receives the dispatches today (no Phase 1 change to the conductor's `DEFAULT_TARGET_REPOS`); pruning devops from the conductor's targets happens in Phase 5 when canonical/vendored inverts

Total: ~750 LOC delete + ~50 LOC modify + tests rebalanced. PR title:
`refactor(release): collapse to 2-PR shape + delete dead rotation slice`.

Must land coordinated with PR 1b (core docs update) so docs and code don't
disagree mid-flight. Land 1b first (docs match the future code), then 1a
(code catches up to the new docs). If 1a needs revision after review, 1b
can stay landed — the doc just briefly over-promises until 1a follows.

#### PR 1b (core): Update RELEASE_PROCESS.md + release-automation-plan.md for 2-PR shape

Scope: on openemr/openemr master, update the release-process docs to reflect
the post-PR-1a 2-PR shape.

Files modified:

- `docs/RELEASE_PROCESS.md`:
  - Mermaid `flowchart TB` in the "Cross-repo flow" section — remove the
    `od["openemr/openemr-devops"]` subgraph's `infraPR` node + the
    `ship-->|1. merges| infraPR` edge; renumber the conductor edge from "2"
    to "1" and docs edge from "3" to "2"; remove the `prepPR -. openemr-rel-cut
    .-> infraPR` and `prepPR -. openemr-rel-update .-> infraPR` and
    `tag -. openemr-tag .-> infraPR` dispatch edges
  - "Repositories involved" table — strip "Rotates the `current` / `next` /
    `dev` slot in CI matrices, package versions, and Docker pins" from the
    `openemr-devops` row (already partly hedged in #12551; this finishes the
    job)
  - "Cross-repo events" table — drop devops from each event's "Emitter →
    target" column. New targets: `openemr-rel-cut` → website-openemr;
    `openemr-rel-update` → website-openemr; `openemr-tag` → website-openemr
    + demo_farm_openemr.
  - "What each PR contains" section — delete the "Infra PR —
    `release-rotation/auto` in `openemr/openemr-devops`" subsection entirely
  - "Release runbook" Phase 2 step 5 (currently the infra workflow opening
    `release-rotation/auto`) — delete
  - "Release runbook" Phase 4 intro — change "infra → conductor → docs" to
    "conductor → docs" everywhere; reword the explanation of why the
    conductor merge matters now that infra is gone
  - "Partial merges and recovery" section — collapse the 7 partial-merge
    states to the 3 still-meaningful ones (conductor-only, docs-only,
    conductor + docs out-of-band-tag); remove Infra+* rows
  - "Naming and tag conventions" table — drop the "Devops rotation PR
    branch" row
- `docs/release-automation-plan.md` (the conductor slice plan, sibling to
  the now-deleted devops slice):
  - "Role in the flow" ASCII diagram — same 3-PR → 2-PR collapse
  - "Out of scope here" section — already says "Test-matrix / package pin
    rotations — `openemr-devops` PR" (line 145); change to reflect that
    rotation no longer exists post-docker-migration
  - Update the cross-link in the doc body that points at devops's
    release-automation-plan.md (the doc PR 1a is deleting)

Total: ~80-100 LOC modified across 2 doc files. PR title:
`docs(release): collapse cross-repo release-process docs to 2-PR shape`.

Must land coordinated with PR 1a. Recommended order: 1b first (docs match
the imminent code), then 1a.

#### PR 1c (core): Wire the vendored-contract drift check

Scope: the canonical contracts (`dispatch.schema.json` + `TagVerifier.php` +
`TagVerificationResult.php`) are vendored into this repo under
`tools/release/contracts/` and `tools/release/src/` but no workflow here calls
`openemr/openemr-devops/.github/workflows/check-vendored-contracts.yml` to
verify drift. The devops-side reusable workflow exists for exactly this
purpose; this repo just needs to call it.

Files added in this repo:

- `.github/workflows/check-vendored-contracts.yml` — minimal caller that
  invokes the devops reusable workflow on PRs touching the vendored paths,
  similar shape to devops's `vendored-contracts-self-test.yml` but pointed
  at the canonical source.

Total: ~30 LOC, 1 new workflow file. PR title:
`ci(release): wire vendored-contract drift check`.

Independent of PR 1a/1b — can land any time.

Caveat: this PR is short-lived. Phase 5 inverts canonical/vendored, at which
point this caller workflow either disappears (no consumers vendor from
devops anymore) or flips direction. Worth landing now anyway because (1) it
defuses a real silent-drift bug today, and (2) it gives Phase 5 a clear
"before/after" to swap.

### Changelog surface early-migration slice (G22 fix + workstream 7 pilot)

Runs in the pre-Phase-2 window. Motivated by G22 in
[`release-mechanism-gaps.md`](release-mechanism-gaps.md) — the CHANGELOG.md
+ GitHub Release body currently ship without the dependabot docker filter
that website-openemr's `ReleaseNotesGenerator` applies (multiple docker
bumps per day flood every release). Fix requires either patching devops's
`ChangelogGenerator` in place and moving it later, or pulling the whole
changelog surface (`ChangelogGenerator`, `CompatibilityDeriver`,
`CompatibilityNotesRenderer` + bin scripts) forward into openemr now as
an early Phase 2 increment. The second option avoids touching devops
twice and doubles as a workstream-7 pattern pilot: proves that the
existing byte-identical machinery (which today enforces docker files)
carries over cleanly to release-mechanism files, before the umbrella
migration commits to that pattern for the full workflow set.

**Sequenced as six PRs** (dependencies noted):

**PR 1 — Rename `.github/docker-byte-identical.yml` → `.github/byte-identical.yml`
(+ sibling workflow rename).** *SHIPPED 2026-07-12 as openemr/openemr#12896.
Auto-sync required three follow-up cleanup PRs (openemr/openemr#12901/#12902/#12903)
for the manifest-rename gap in the sweep logic, plus openemr/openemr#12905 for
rel-820's stale references in master-only files.* The manifest and its enforcement pair are no
longer docker-only. `git mv` the manifest + `git mv .github/workflows/docker-validate-byte-identical.yml
→ validate-byte-identical.yml`. Update references in `sync-byte-identical.yml`,
`.github/scripts/validate-byte-identical.sh`, and both docs. Self-track: the
renamed manifest + renamed workflow paths update the FILES_ALL list inside
the manifest itself. Sync workflow auto-propagates the rename to rel-820 +
rel-800 + rel-704 in-place. BATS regression coverage at
`tests/bats/ci-scripts/validate-byte-identical/` already exists for the
diff logic; add pinned-name updates if any. Validation shape confirmed
during the 2026-06-21 externalization work (docker-migration doc's
"Post-migration refinement" note).

**PR 2 — Add glob support to the validator + sync scripts.** *SHIPPED 2026-07-12
as openemr/openemr#12904 (six-commit sequence covering the initial implementation,
CodeRabbit round-1 + shellcheck cleanup with shared lib extracted into
.github/scripts/lib/glob-expand.sh, byte-identical manifest addition, sync BATS
GITHUB_ACTIONS unset for CI compat, POSIX-negated-class [!...] regex conversion
per CodeRabbit round-2, and shellcheck source directive switched to /dev/null to
avoid rel-branch break). Required openemr/openemr#12906 hotfix because
sync-byte-identical.yml's RUNNER_TEMP extraction needed to also copy lib/
alongside the sync script.* Extend
`validate-byte-identical.sh` and `sync-byte-identical.yml`'s apply step to
expand glob patterns from the manifest (`git ls-files -- <pattern>` handles
`**` natively). BATS regression tests: glob patterns matching multiple
files, empty glob results, glob matching a mix of existing + newly-added
files, glob delete-propagation. Purely a capability addition — no manifest
content change. Motivation: at the 58-75 file scale that release-mechanism
enforcement needs, enumerating individual paths becomes a "forgot to add
the new file" failure mode. Globs close that failure mode by contract.

**PR 3 — Add release-mechanism globs to `byte-identical.yml`.** *SHIPPED 2026-07-12
as openemr/openemr#12910. 12 new entries added, manifest went from 9 to 21
entries. Immediate follow-up openemr/openemr#12915 pivoted those 12 entries
from bare-string form to object form with per-entry `exclude-branches:` —
rel-800 and rel-704 lack the composer autoload topology needed for the
release-mechanism PHP files, so adding 60 files there triggered phpstan /
isolated-tests / composer-require-checker failures on the auto-sync PRs.
Two workflow yq bugs surfaced while validating the new shape: openemr/openemr#12916
(object entries dumped as raw YAML splat in `add-paths`) and openemr/openemr#12920
(paths extracted but not filtered by target branch). Both were the same class
of bug — the workflow's inline yq expression drifting from the sync script's
`read_manifest_entries` + `filter_by_branch` in `lib/glob-expand.sh` —
which motivated the pre-PR-4 refactor below.* Covers the
release-mechanism surface that already lives in openemr core today —
this PR does not move any new content, it only starts enforcing
uniformity on what's here. Roughly twelve entries:

Code + tests (globs):

- `tools/release/**`
- `src/Common/Command/ReleasePrep/**`
- `src/Common/Command/ReleasePrepCommand.php`
- `src/Common/Command/CreateReleaseChangelogCommand.php`
- `tests/Tests/Isolated/Release/**`

Explicitly NOT included: `bin/console`. It's the general-purpose
Symfony CLI runner (not release-mechanism-owned) and evolves for
reasons unrelated to release-mechanism work. If a release-mechanism
change on master needs a bootstrap change too, backport it targeted
rather than forcing every unrelated bin/console tweak to sync.

Workflows + composite actions (files that fire from rel branches —
matches the manifest's existing "canary travels with the file set it
polices" design principle):

- `.github/workflows/release-prep.yml`
- `.github/workflows/branch-cut-automation.yml`
- `.github/workflows/patch-prep-automation.yml`
- `.github/workflows/release-permissions-check.yml`
- `.github/actions/setup-php-composer/action.yml`

PR body templates (read at rel-branch runtime by peter-evans invocations
in `release-prep.yml`; must exist on rel branches for the create-pull-
request step to find them):

- `.github/PULL_REQUEST_TEMPLATE/release-prep.md`
- `.github/PULL_REQUEST_TEMPLATE/release-finalize.md`

Explicitly NOT in this set (master-only-firing, no need to travel with
rel branches):

- `.github/workflows/notify-release-targets-changed.yml`
- `.github/workflows/docker-release-orchestrator.yml`
- `.github/workflows/docker-validate-release-targets.yml`
- `.github/workflows/sync-byte-identical.yml`
- `.github/scripts/sync-byte-identical.sh`
- `.github/release-targets.yml` (master-authoritative by explicit design;
  see file's own header comment)

Sync workflow auto-opens sync PRs against rel-820 (small — surface
already exists), rel-800 + rel-704 (large — ~60 file additions each).
The rel-800/rel-704 additions are dead code (no workflow on those
branches to invoke them); harmless until those branches rotate out.
Byte-identical enforcement now covers the release-mechanism surface,
matching the protection currently applied to docker files.

**Pre-PR-4 refactor — Extract byte-identical manifest parsing into a shared
script + BATS coverage.** *SHIPPED 2026-07-12 as openemr/openemr#12924.*
Not a numbered PR in the original slice plan, added mid-slice after
openemr/openemr#12916 and openemr/openemr#12920 (two consecutive workflow
yq bugs) made it clear the three-way parsing duplication — sync script,
validate script, and inline yq in `sync-byte-identical.yml`'s Compute step
— was the underlying failure mode. New `.github/scripts/list-manifest-paths.sh`
wraps `read_manifest_entries` + `filter_by_branch` (already in
`lib/glob-expand.sh` from PR 2) as a callable driver; the workflow's Compute
step now invokes it instead of parsing YAML inline. 10 new BATS cases at
`tests/bats/ci-scripts/list-manifest-paths/` give the workflow-side
manifest-parsing path its first regression coverage. Fires only on master
(not in the byte-identical set — RUNNER_TEMP materialization uses master's
copy at each workflow run). Auto-sync PRs from PR 3's fallout landed the
same day as openemr/openemr#12921 / openemr/openemr#12922 / openemr/openemr#12923.

**PR 4 — Move `ChangelogGenerator` + `CompatibilityDeriver` +
`CompatibilityNotesRenderer` from devops → openemr; port website's filter;
fix compare-link; wire mutator.** *SHIPPED 2026-07-13 as
openemr/openemr#12925 (paired with openemr/openemr-devops#857 which retired
`changelog-pr.php` — landed same day to avoid the "release cut in the gap"
window where devops has stopped opening the two post-tag PRs but the mutator
isn't in place yet).* Four PHP classes came across from
`openemr-devops/tools/release/src/` into `openemr/tools/release/src/`
(GitHubApi, ChangelogGenerator, CompatibilityDeriver,
CompatibilityNotesRenderer) plus the two bin wrappers
(`changelog.php`, `derive-compatibility.php`). `changelog-pr.php`
deleted from devops rather than moved — its "open two post-tag PRs
that prepend the CHANGELOG.md entry" behavior is subsumed by
`ChangelogMutator`. The four private static filter methods (`isNoise`,
`isDockerBump`, `isNoOpVersionBump`, `scopeOf`) plus four constants
(`RELEASE_BOT`, `DEPENDABOT`, `DEPENDABOT_DOCKER_GROUPS`,
`MACHINERY_SCOPES`) ported from website-openemr's `ReleaseNotesGenerator`
into openemr's `ChangelogGenerator` as a pre-`categorize()` filter step,
after extending `GitHubApi::prsForCommits()` to also return the PR
author login (needed to identify dependabot/release-bot PRs). Compare-
link fix: new `compareLinkOverride` param on `generate()` renders
`vPREV...vNEW` (aspirational immutable-tag URL) while the git-range
API call still enumerates commits from a ref that exists (rel branch
tip at release-prep time, target tag post-tag on finalize). Ancillary
CR round-1/2/3 fixes: `preg_replace_callback()` for backref-safe
section replacement, `Psr\Clock\ClockInterface` injection for
rerun-idempotent dates, `escapeMarkdown()` + `sanitizeGitHubUrl()` to
prevent Markdown injection via contributor-controlled PR titles / area
labels / advisory summaries, `mkdir`/`file_put_contents` return-value
checks in the bin script. `ChangelogMutator` landed at
`tools/release/src/Mutator/ChangelogMutator.php` under namespace
`OpenEMR\Release\Mutator` (autoload-dev alongside its dep chain,
preserving the original dev/prod split for `OpenEMR\Release\`).
`ReleasePrepCommand`'s default mutator lists wire it in on BOTH scopes
via a `class_exists()`-guarded FQCN reference (whitelisted in
`.composer-require-checker.json` so require-checker allows the
documented cross-boundary reference). Wired at both
`buildDefaultRelMutators` (release-prep PR, rel-branch scope) and
`buildDefaultMasterMutators` (release-finalize partner PR, master
scope, post-tag — the persisted CHANGELOG entry becomes the canonical
immutable-tag diff shape). 20 isolated `ChangelogGeneratorTest` cases
+ 8 `ChangelogMutatorTest` cases. Live smoke test against real
`v8_2_0...rel-820` gh api walk exercised prev-tag resolution + range-
head fallback + aspirational URL rendering + prepend behavior end-to-
end. Auto-sync PR #12926 propagated the 10 new files to rel-820;
rel-800 + rel-704 correctly no-op'd (the object-form
`exclude-branches: [rel-800, rel-704]` filter from PR 3's #12915 does
its job for both `tools/release/**` and `src/Common/Command/
ReleasePrep/**`). Follow-up openemr/openemr#12927 added the
`ChangelogMutator` whitelist entry to rel-820's
`.composer-require-checker.json` — that file isn't in the byte-
identical set, so the sync PR didn't carry it, and require-checker
was failing on rel-820 until the manual port. Follow-ups deferred:
compatibility injection into the mutator flow (a future
CompatibilityMutator will thread CompatibilityDeriver +
CompatibilityNotesRenderer through, keeping the CHANGELOG.md
Minimum-supported-versions section that 8.2.0's entry has today);
`CompatibilityNotesRenderer::inject()` non-idempotence for a second
inject call (pre-existing devops behavior, no consumer today, will be
fixed alongside the CompatibilityMutator work); fixture regression
that regenerates 8.2.0's real CHANGELOG entry from frozen inputs
(would need ~100 real PR objects captured to fixtures, deferred for
scope).

**Sequencing note (added 2026-07-13):** PR 8 lands BEFORE PR 5.
Original ordering had PR 5 first (as a "close out devops-side
duplication first, add compat later" plan), but that creates a real
compat-gap window: 8.2.0's CHANGELOG.md has a "Minimum supported
versions" section that devops's current `derive-compatibility.php`
step injects; ChangelogMutator (PR 4) writes only the ChangelogGenerator
body, no compat. Post-PR-5 the section-extract would pull an entry
missing that block. Landing PR 8 (CompatibilityMutator) first closes
the gap: by the time PR 5 swaps devops to extract-only, openemr's
CHANGELOG.md already has compat baked in, extracts are complete
end-to-end.

**PR 5 — Rewire devops's `build-release.yml` to section-extract from
openemr's tagged tree.** *SHIPPED 2026-07-13 as
openemr/openemr-devops#858.* Deleted `ChangelogGenerator.php` +
`CompatibilityDeriver.php` + `CompatibilityNotesRenderer.php` + the
two consumer bin scripts (`changelog.php`, `derive-compatibility.php`)
from devops. `changelog-pr.php` was already gone (PR 4's coordinated
devops PR openemr/openemr-devops#857). GitHubApi.php stays in devops
-- `preflight.php` + `PreflightChecker.php` still consume it;
consolidation is a future PR when PreflightChecker also migrates.
New `tools/release/bin/extract-changelog-section.php` (60-line
Symfony Console CLI, `--release-version` chosen to avoid Symfony's
built-in `--version` / `--verbose` clashes) reads openemr's
CHANGELOG.md, locates `## [MAJOR.MINOR.PATCH]`, writes everything
up to (but not including) the next `## ` heading or EOF to
`release-output/changelog.md`. Content is pre-computed in openemr's
CHANGELOG.md by ChangelogMutator + CompatibilityMutator (PRs 4 + 8),
so the extract carries both the noise-filtered PR list and the
Minimum-supported-versions block in a single pass. Taskfile: dropped
`changelog:` + `compatibility:` tasks, added `changelog:extract`.
`build-release.yml`: replaced the two steps (`task release:changelog`
walking commits + `task release:compatibility` injecting the
ci-matrix section) with one `task release:changelog:extract` step.
No `GH_TOKEN` / no `gh api` calls needed here anymore -- everything
reads from the openemr checkout already sitting at `$OPENEMR_DIR`.
Cleaned up dead `base_ref` end-to-end: removed from
`build-release.yml`'s workflow_dispatch + workflow_call input
declarations, removed the "Derive base_ref from previous release"
step from `build-release-on-tag.yml`'s derive job (previously
gh-api-called to find the previous release tag). Ancillary CR
round-1 fix: routed `inputs.version` through an env var in the
Extract CHANGELOG section step so a shell-metachar in the input
can't break out of the task-argument quoting (rest of the workflow
uses the direct-template-expansion pattern; hardening the rest is
a separate follow-up PR). Byte-comparison parity: extracted 8.2.0
section from live rel-820 CHANGELOG.md → 792 lines / 83.6KB,
matching the "~794 total lines" measure from openemr's gaps-doc
analysis of 8.2.0's GitHub Release body. Since 8.2.0's CHANGELOG.md
was written by pre-PR-4 devops's own ChangelogGenerator, extract IS
byte-identical to what today's changelog.php would produce for
8.2.0; parity check trivially passes. 8.2.1+ will have the noise
filter applied (mutator writes filtered content) -- the intended
semantic improvement.

**PR 6 — Retire website-openemr's release-notes generation surface.**
*SHIPPED 2026-07-14 as openemr/website-openemr#190.* Deleted
`tools/release-docs/bin/gen-release-notes.php` +
`src/ReleaseNotesGenerator.php` + `tests/ReleaseNotesGeneratorTest.php`
+ the `tests/fixtures/release-notes/` fixture directory (only used
by the retired test). Taskfile: dropped `gen-release-notes:` task.
`.github/workflows/release-docs.yml`: replaced the "Generate release
notes" step with a "Remove stale per-release release-notes page"
scrub, matching the pattern already in place for the retired
per-release install/upgrade pages. Scrub touches
`$PUBLISH/content/release-notes/$VERSION.md` for the current
dispatch's version only — 8.2.0's live page at
openemr.org/release-notes/8.2.0/ stays grandfathered (VERSION=8.2.0
will never dispatch again). AcknowledgementsGenerator +
acknowledgements page untouched — separate output with a separate
template, still consumed by the release-cycle acknowledgements
surface. Follow-up not in this PR: replace 8.2.0's static live page
with a redirect to https://github.com/openemr/openemr/releases/tag/
v8_2_0 for uniform user experience with 8.2.1+ (which won't have a
per-version page). Tests: 67 pass; phpstan + phpcs clean.

**PR 7 — Retire `src/Common/Command/CreateReleaseChangelogCommand.php`.**
*SHIPPED 2026-07-14 as openemr/openemr#12964.* Deleted the 374-line
Guzzle-based milestone-driven changelog helper (Stephen Nielson's,
predates the migration; fully redundant with ChangelogGenerator +
ChangelogMutator). Also removed the `.github/byte-identical.yml`
entry for the file and pruned 66 stale phpstan-baseline entries
across 17 baseline files (all referencing the deleted class). Two
fatal-baseline-caps followed the count reductions
(`method.notFound.php` 138→137, `variable.undefined.php` 509→508)
in the same commit -- caps only go down per the fatal-baseline-caps
contract. No live callers grepped anywhere in the tree; no tests
referenced the class. 19 files changed / 706 deletions net. Sync
workflow will propagate the file deletion + manifest update to
rel-820 (excluded from rel-800 + rel-704 per the object-form
`exclude-branches:` on the release-mechanism paths).

**PR 8 — Add `CompatibilityMutator`; fix `CompatibilityNotesRenderer::inject()`
idempotence.** *SHIPPED 2026-07-13 as openemr/openemr#12928 (paired with
openemr/openemr#12933 for the rel-820 config-file ports —
`.composer-require-checker.json` whitelist entry + `.codespell-ignore-words.txt`
`deriver` entry — neither file is in the byte-identical set so the
auto-sync PR openemr/openemr#12932 didn't carry them).* New
`tools/release/src/Mutator/CompatibilityMutator.php` at namespace
`OpenEMR\Release\Mutator` (autoload-dev alongside its dep chain)
materializes rel branch's `ci/*/docker-compose.yml` via `git ls-tree` +
`git show` per file (not `git archive` — `.gitattributes` marks `ci/`
as `export-ignore`, correctly, but that means archive silently produces
an empty tarball). Master scope handling: same `git ls-tree` code path
resolves the rel branch as either the bare name (rel scope, refs/heads/
present) or `origin/<relBranch>` (master scope, refs/remotes/origin/
populated by workflow's `fetch-depth: 0`). Wired into
`ReleasePrepCommand`'s default mutator lists AFTER `ChangelogMutator`
on both scopes — ordering matters, injects into the `## [X.Y.Z]`
heading the changelog mutator writes. Refactored
`appendOptionalReleaseMutators()` to loop over an FQCN array (was one
hardcoded entry). Fixed `CompatibilityNotesRenderer::inject()`
non-idempotence: the original CR-round-2 fix on PR 4 flagged blindly
splicing after the first `## ` heading duplicates the section on
rerun; PR 8's first pass fixed that but CR-round-1 on 12928 caught a
worse variant — the strip regex was unscoped, so on a multi-release
CHANGELOG (older release with its own compat block below the current
heading), the strip deleted the older release's block. Landed fix
scopes the strip to the FIRST `## ` section's contents only, older
sections preserved. Also fixed a latent bug in ChangelogMutator
noticed while planning PR 8: release-prep.yml's rel-scope invocation
didn't pass `--rel-branch`, so `$context->relBranch` was null on
rel scope and ChangelogMutator's `resolveRangeHead()` would throw on
the first real 8.2.1 release-prep run. Fix: pass `--rel-branch` on
rel scope too (workflow already computes the value).
`CompatibilityMutatorTest` covers 8 scenarios (injection after
version heading, matrixUrl uses rel-branch, rerun idempotence,
master-scope reads rel branch's ci/ after intentional master-side
drift, multi-heading-preserves-older-block, origin-remote-tracking-ref
fallback, throw-when-neither-ref-resolves, MutatorResult shape).
`CompatibilityNotesRendererTest` covers 5 scenarios (render shape,
inject after version heading, prepend when no heading, idempotence,
stale-section replacement). Live smoke test against real rel-820
produced the exact `- **PHP** 8.2+ / - **MariaDB** 10.6+ / - **MySQL**
5.7+` block that 8.2.0's committed entry has.

**PR 9 — Fixture-based regression regenerating 8.2.0's real CHANGELOG
entry from frozen inputs.** *SHIPPED 2026-07-15 as openemr/openemr#12969
(paired with openemr/openemr#12995 for the rel-820 sync — auto-sync PR
openemr/openemr#12992 failed whitespace on new fixture subdirs because
`.pre-commit-config.yaml` isn't byte-identical-tracked; #12995
cherry-picked the master commit as a workaround).* Captured live gh
API state for `v8_1_0...v8_2_0` via new
`tools/release/bin/capture-changelog-fixture.php` one-shot maintenance
tool: 653 SHAs, 647 unique PRs, published advisories. Persisted as
JSON fixtures under `tests/Tests/Isolated/Release/fixtures/8_2_0/`
with a twin-scenario subdir layout: `release-time/` (empty advisories
— simulates release-prep merge time before GHSAs are published) and
`post-ghsa/` (advisories filtered to only those matching this
release's SHA/PR range OR `vulnerabilities[].patched_versions ==
targetVersion` — simulates the post-GHSA-publish amendment dispatch).
Test replays each scenario through `ChangelogGenerator` +
`FakeGitHubApi` + `FrozenClock` set to 2026-07-08, asserts byte-
identical rendered output. **Also introduced a matcher change**:
`ChangelogGenerator::advisoryMatchesRange()` now matches on
`vulnerabilities[].patched_versions == $targetVersion` as the primary
signal — openemr's GHSAs don't populate References with commit/PR
URLs so the pre-existing reference-based matcher never actually
fired in production. Also added `state === 'published'` post-filter
in `GitHubApi::publishedAdvisories()` as a belt-and-suspenders guard
against draft-advisory leakage. New unit tests: 2 for the
patched_versions match path, 1 mutator idempotence test covering the
amendment workflow (release-time → post-ghsa → post-ghsa →
release-time round-trip, wholesale-replace semantics preserved).
Fixture confirms the real 8.2.0 CHANGELOG shape byte-for-byte.

**PR 10 — Update `docs/RELEASE_PROCESS.md` for the new mutator-driven
changelog flow.** *SHIPPED 2026-07-14 as openemr/openemr#12976 (rel-820
sync via openemr/openemr#12977 — manual since docs/ isn't on the
byte-identical manifest). Round 1 CR follow-ups landed with the initial
merge.* Rewrote intro + Repositories-involved table + Cross-repo mermaid
+ Conductor PR + Docs PR + Runbook Phase 2/3/4 + Phase 5 (build-release
step 10) + Recovery sections. Deleted the "Changelog PRs" subsection
entirely (retired with changelog-pr.php in PR 4). Runbook merge-order
collapsed from 4 PRs to 3 (Conductor / Finalize-on-master / Docs; no
separate changelog PRs). Runbook step 6 rewritten as "edit CHANGELOG.md
on release-prep PR" (was "edit release-notes draft on website-openemr
PR") with an explicit "mutator wholesale-replaces on every rel-branch
push — hand-edits only survive if made after all release-cycle commits
land" caveat. Runbook step 10 (build-release-on-tag) rewritten to
describe the section-extract flow (`extract-changelog-section.php`)
instead of the removed `base_ref` lookup. Slice plans list gained a
"Changelog surface migration" pointer (initially added, then removed
before merge because the migration doc PR #12598 was still draft — will
be re-added when #12598 lands to avoid a broken doc-link).

**Post-slice follow-ups (all landed 2026-07-15):**

The 10-PR slice above closed the *migration* of changelog generation
from openemr-devops + website-openemr into openemr core, but surfaced
a real timing gap during the changelog-related design discussion: the
mutator-driven flow only fires at release-prep / release-finalize
time (pre-tag or immediately post-tag). GHSAs for a release are
typically published *after* the release ships — days or weeks later
— so the shipped CHANGELOG.md and the tagged Release body miss
Security sections that document those late-published advisories.
This wasn't a regression from the migration; devops's old
`changelog-pr.php` had the same window since it also fired near
release time. But the migration made it a *fixable* gap because
everything was now consolidated in openemr core. Follow-ups:

- **openemr/openemr#12993** — RELEASE_PROCESS.md documents the "when
  publishing a GHSA for a fix that landed in a specific release, set
  Patched versions to the exact release string" convention. Strict-
  string-equal is what the matcher checks (see PR 9's matcher
  change); no ranges, no comma-separated lists.
- **openemr/openemr#12996** — `.github/workflows/release-amendment.yml`.
  Manual `workflow_dispatch`-only workflow the release manager runs
  post-GHSA-publish. Reuses `openemr:release-prep --scope=rel|master`
  on a post-tag checkout: all mutators except ChangelogMutator +
  CompatibilityMutator are idempotent no-op against shipped state,
  so the diff is scoped to CHANGELOG.md's target section (typically
  the newly-populated `### Security Fixes` block). Opens
  `release-amendment/<version>-<rel_branch>` +
  `release-amendment/<version>-master` PRs via peter-evans and re-
  extracts + `gh release edit`s the GitHub Release body + syncs the
  `changelog.md` attachment in the same run. Preserves original
  release date via extract-and-restore. Validates annotated tag
  exists before amending. Docs updates in the same PR describe the
  new workflow + a "hand-edits do not survive rerun" caveat.
- **openemr/openemr#12998** — rel-820 doc sync for the above two.
- **openemr/openemr#12999** — GH_TOKEN fix. Dry-run of the amendment
  workflow on 8.2.0/rel-820 failed at the mutator step because
  ChangelogMutator shells out to `gh api` and needs GH_TOKEN. My
  workflow set `persist-credentials: false` on the checkouts
  (hardening) so there was no ambient token in `.git/config` for
  `gh` to fall back on. Fix: explicit `GH_TOKEN: ${{ steps.app-token.outputs.token }}`
  in the mutator step env blocks. release-prep.yml has the same
  latent issue but happens to work today because it uses default
  `persist-credentials: true`; noted as a separate hardening
  follow-up.
- **openemr/openemr#13000** — `.github/workflows/release-mechanism-smoketest.yml`.
  CI-gated smoke test that runs `openemr:release-prep --scope=rel`
  end-to-end against rel-820 on every PR touching a release-mechanism
  workflow YAML or PHP file. Deliberately uses the strictest
  workflow shape (`persist-credentials: false` + explicit
  `GH_TOKEN`) so any regression in either release-prep.yml or
  release-amendment.yml's auth/env plumbing fails CI before merge.
  The `openemr:release-prep` unit + fixture tests don't exercise the
  actual `gh api` shellout (they inject FakeGitHubApi), so this is
  the only place that catches env-plumbing bugs like #12999.
- **openemr/openemr#13004** *(SHIPPED)* — extract-order fix. First
  real dispatch of the amendment workflow on 8.2.0/rel-820 surfaced
  a bug: `peter-evans/create-pull-request@v8` resets the calling
  checkout's working tree back to base-branch HEAD after committing
  to the amendment branch. The Extract step (which reads
  `rel-checkout/CHANGELOG.md` and pipes to `gh release edit
  --notes-file`) was running AFTER peter-evans, so it saw the
  pre-amendment content. Release body + attachment got overwritten
  with byte-identical pre-amendment content — no user-visible
  damage, but the amendment didn't actually take. Fix: reorder
  Extract to run BEFORE peter-evans; extracted content lives in
  `$RUNNER_TEMP/section.md` which survives peter-evans's reset.
  Behavior gotcha saved as memory
  (`feedback_peter_evans_resets_working_tree.md`) for future
  workflows that follow the same shape.
- **openemr/openemr#13005** *(SHIPPED, rel-820 sync
  openemr/openemr#13006)* — noise-filter relaxation. Review of the
  first real amendment dispatch (see PRs #13002/#13003 below)
  showed the ported website filter was too aggressive for the
  CHANGELOG surface: it dropped rel-branch backport PRs (like
  openemr/openemr#12827, #12832 — sql-upgrade fixes backported to
  rel-820 for 8.2.0) and human-authored `fix(release):` /
  `fix(release-prep):` PRs (~10 in 8.2.0's range). Backports are
  the PRs that actually shipped in a release; release-scoped human
  PRs are internal-tooling changes but CHANGELOG readers (devs +
  release engineers) benefit from seeing them. Dropped both filter
  rules. The docker auto-bump noise the release-scope rule was
  meant to catch is already handled specifically by
  `isDockerBump()` + `isNoOpVersionBump()` under the DEPENDABOT
  author branch — those stay. Website's release-notes surface
  still uses the stricter filter (different audience). Also
  tightened the chore-release-cut regex to require a version
  number after "release" (`\s+v?\d`) so titles like
  `chore(docs): release notes update` don't false-match — CR
  round 1 catch on a pre-existing over-match.
- **openemr/openemr#13007** *(SHIPPED)* — rel-checkout submodule
  leak fix. Third dispatch (first fully-green end-to-end run)
  surfaced yet another bug: master-side peter-evans's `git add -A`
  at the workflow root treated `rel-checkout/` (a sub-directory
  with its own `.git/` from the rel-820 checkout) as a git
  submodule and added a spurious `Subproject commit <sha>` entry
  to the master amendment PR. Fix: `rm -rf rel-checkout` between
  the rel-side peter-evans call and the master-scope block. By
  that point Extract has saved to `$RUNNER_TEMP` and rel-side
  peter-evans has captured its diff, so nothing downstream reads
  from rel-checkout.
- **openemr/openemr#13038** *(SHIPPED 2026-07-17)* — Release body
  125K truncation. Fourth real dispatch (post-#13015 fixture-based
  changelog regen, which added the missing v8_0_0 → v8_2_0 PRs
  the skipped-v8_1_0 boundary had suppressed) surfaced the last
  unrecovered failure mode: the extracted section grew to ~130KB
  and `gh release edit --notes-file` rejected it with `body is too
  long (maximum is 125000 characters)`. All upstream steps
  succeeded, but the Release body itself never updated. Hybrid
  strategy: body ≤124KB carries the full section unchanged
  (historical behaviour); body >124KB is substituted with a short
  link-only pointer referencing the tagged `CHANGELOG.md` on the
  source + the `changelog.md` Release attachment. Attachment
  always carries the full section regardless of size (attachments
  have no such limit) — no content is ever lost. Summary step
  surfaces which path was taken; dry-runs surface "would truncate"
  up front. RELEASE_PROCESS.md updated in same PR with a new
  "Release body truncation at 125K" paragraph, workflow-topology
  cross-reference + surface count updated to 4 (adding the
  attachment as its own surface), and the stale "auto-dispatch
  workflow is a tracked follow-up" line in the GHSA convention
  section replaced with a pointer to the shipped
  `release-amendment.yml`.
- **openemr/openemr#13002 + #13003** *(SHIPPED)* — real amendment
  PRs (8.2.0-rel-820 + 8.2.0-master). CHANGELOG.md diffs land the
  8.2.0 Security section (`GHSA-vv5j-6gjw-ffx9`) + the full PR
  list (including the ~12 previously-filtered PRs from #13005's
  relaxation). Compare link corrected to `v8_1_0...v8_2_0`, date
  preserved as `2026-07-08`. Body of both PRs is `chore(release):
  amend 8.2.0 CHANGELOG (post-GHSA-publish)`. Force-updated three
  times across the fix iterations — final content is identical to
  the fourth dispatch's mutator output.

**End-to-end validation history for the amendment workflow:**

- Dry-run #1 (openemr/openemr actions run 29392343714, 2026-07-15
  05:46Z): **failed** at "Run release-prep mutators (rel scope)"
  — missing GH_TOKEN. Prompted openemr/openemr#12999.
- Dry-run #2 (run 29396553059, 07:11Z, post-#12999): **succeeded**.
  All 28 steps green, PR-open steps correctly SKIPPED via
  `if: ${{ !inputs.dry_run }}`, mutator diff previewed in step
  summary. `changed=true` on both scopes (GHSA-vv5j-6gjw-ffx9
  matched via patched_versions == "8.2.0"). ~14 minutes total.
- Real dispatch #1 (run 29397657588, 07:31Z, post-#12999): all
  steps green; amendment PRs #13002 + #13003 opened with correct
  content; but Release body edit put byte-identical pre-amendment
  content because of the peter-evans reset. Prompted #13004.
- Real dispatch #2 (run 29404476642, 09:26Z, post-#13004 +
  #13005 + #13006): Release body + attachment correctly updated
  with amended content (Security Fixes section, tag-to-tag
  compare link, preserved date). Amendment PRs force-updated with
  the fuller PR list (+ backports + release-scoped PRs). BUT
  #13003 accumulated a spurious `rel-checkout` submodule entry
  (peter-evans's `git add -A` at workflow root treated the
  rel-820 checkout subdirectory as a submodule). Prompted #13007.
- Real dispatch #3 (run 29422237030, 14:08Z, post-#13007):
  **fully green end-to-end**. Both #13002 + #13003 now show only
  `CHANGELOG.md` in their files list (+6/-265 each, identical
  diff shape). Release body + attachment stayed byte-identical
  (already amended in dispatch #2; content didn't change so no
  visible update). #13002 + #13003 merged shortly after.
- Real dispatch #4 (2026-07-16, post-#13015 fixture-based
  changelog regen + #13025 codespell config): first amendment
  dispatch after the ChangelogMutator now delegates prev-tag
  derivation to BranchVersionResolver — the resulting section
  went from ~68KB (v8_1_0 → v8_2_0 range) to ~130KB (v8_0_0 →
  v8_2_0 range, since v8_1_0 is intentionally missing from the
  manifest). All upstream steps green (amendment PRs updated,
  attachment synced), but the final `gh release edit --notes-file`
  failed with `body is too long (maximum is 125000 characters)`.
  Prompted #13038 (hybrid truncation).
- Real dispatch #5 (post-#13038): validates the truncation path
  end-to-end. **PENDING as of 2026-07-17** — dispatched after
  #13038 merges to master.

Both amendment PRs landed 2026-07-15 — closes the loop end-to-end.
All four surfaces (rel-branch CHANGELOG, master CHANGELOG,
GitHub Release body, `changelog.md` attachment) now carry the
amended 8.2.0 Security section + full PR list. The Release body
now uses the truncation pointer (section is >124KB post-#13015)
and points at the `changelog.md` attachment for the full content.

**Testing / validation strategy per PR:**

- PR 1: canary self-enforces; watch sync PRs open cleanly on all three
  rel branches. Manual worktree_dispatch of the renamed validator
  workflow as a smoke test.
- PR 2: BATS coverage exercises new glob semantics. No functional impact
  on the current 8-file manifest until PR 3 uses globs.
- PR 3: canary + sync workflows auto-verify; observe rel-800/rel-704 sync
  PRs (large additions) merge cleanly.
- PR 4: `workflow_dispatch` dry-run against `rel-test` before the next
  real release — mutator produces a plausible `CHANGELOG.md` diff without
  cutting a real tag. Fixture-based regression pins the ported filter.
- PR 5: byte-compare extracted section against `ChangelogGenerator` output
  for 8.2.0 as parity check pre-merge.
- PR 7: repo-wide grep for `openemr:create-release-changelog` +
  `CreateReleaseChangelogCommand` before deletion — no live callers
  should remain post-PR 6.
- PR 8: smoke-test the mutator against rel-820's `ci/` directory (real
  CI matrix), verify the rendered Minimum-supported-versions block
  matches the block already in 8.2.0's CHANGELOG entry. Also cover the
  rerun-idempotence fix: apply twice, assert no second copy of the
  section appears.
- PR 10: cross-check every retired path referenced in
  `RELEASE_PROCESS.md` is actually gone from the tree (no
  `changelog-pr.php`, no `task release:changelog`, no
  `task release:compatibility`, no `openemr:create-release-changelog`).
  No CI signal to lean on for docs correctness — reviewer eyeballs the
  rewritten sections against the actual mutator + workflow code.
- PR 9: fixture regression pins byte-identical output; regenerate the
  fixture (via a small `bin/capture-8_2_0-inputs.php` one-shot) any time
  the noise filter, section ordering, or area labels shift.
- Full-cycle: the next real release (probably 8.2.1) is the acceptance
  test for the whole consolidation.

**What Phase 2 inherits after this slice lands:** the ChangelogGenerator
+ CompatibilityDeriver + CompatibilityNotesRenderer moves are done;
Phase 2's "supporting PHP classes move with their tests" clause shrinks
to `PackageAssembler` + `PreflightChecker`. The `build-release.yml` move
proceeds as originally sketched but from a smaller starting point in
devops.

### Announcements early-migration slice (post-changelog-surface pilot) — SHIPPED 2026-07-18/19

**STATUS: SHIPPED 2026-07-18 through 2026-07-19.** All 5 PRs landed:

- `openemr/website-openemr#192` — renderer classes + templates + tests
- `openemr/website-openemr#193` — bin CLI scripts + PR-metadata input adaptation
- `openemr/website-openemr#194` — `release-announcements.yml` workflow
- `openemr/website-openemr#198` — sync `forum.md.twig` update from `openemr-devops#853` (post-PR-1 template drift)
- `openemr/openemr-devops#861` — retire devops's copies
- `openemr/openemr#13055` — `RELEASE_PROCESS.md` phase 4 + runbook step 16 update to reference the new home + trigger

Manual-dispatch smoke test on 2026-07-19 confirmed all 8 rendered files byte-identical between website's new workflow and devops's July 8 production run (before `#853`), and post-#198 sync confirmed the updated `forum.md.twig` renders identically. Parallel-run window closed with `#861`; website is now the sole canonical announcement drafter.

Follow-ups filed on `openemr/website-openemr` for the future automated-posting phase:

- `openemr/website-openemr#195` — bot-login gate defense-in-depth (blocks human-branch false positives once posting is automated)
- `openemr/website-openemr#196` — CI smoke test workflow for the task chain end-to-end
- `openemr/website-openemr#197` — mandatory `--dry-run` + golden fixtures on every future channel poster (design constraint for the post-phase)

The original plan (below) is preserved as-designed for archival reference.

---

Runs after the changelog-surface slice lands, in the same pre-Phase-2
window. Motivated by Brady's timing observation
(2026-07-15): announcements should fire *after* the website docs PR
merges, not at `openemr-tag` dispatch time — the download page going
live is the semantic "now it's really out there" moment, and putting
the drafter in `website-openemr` makes that trigger natural (no need
to cross-repo dispatch backwards from website → openemr just to fire
the announcement drafter). The eventual goal is automated posting to
each channel; parking the drafter in `website-openemr` now sets up
the natural home for the post-drafter automation later.

**Motivation vs "just wait for Phase 3 / build-release move":**

The announcements workflow (`release-announcements.yml` in devops)
consumes `openemr-tag` today and renders per-channel drafts (forum,
chat, X, Facebook, LinkedIn, mail) into a workflow artifact for a
maintainer to copy/paste + `oe-sender.js`-mail. Moving it during the
umbrella `openemr-devops → openemr/openemr` migration (Phase 2+
proper) would put it in `openemr/openemr` — but that's the wrong
home per the semantic argument above (fires at tag time, not
docs-shipped time). Pulling it forward as its own slice lets us
land the trigger change (`openemr-tag` → `pull_request: closed` on
release-docs branches) with the location change (devops → website)
in one atomic move, rather than shipping it to openemr/openemr in
Phase 2 and then re-locating it later.

**What moves:**

| From `openemr-devops` | To `openemr/website-openemr` | Notes |
| --- | --- | --- |
| `.github/workflows/release-announcements.yml` | `.github/workflows/release-announcements.yml` | Trigger changes -- see below |
| `tools/release/bin/render-announcements.php` | `tools/release-docs/bin/render-announcements.php` (or nearest existing dir) | Same shape as existing `tools/release-docs/bin/` scripts |
| `tools/release/bin/derive-announcement-inputs.php` | ditto | Adapts to read PR metadata instead of `openemr-tag` dispatch payload -- version + tag + branch come from `release-docs/<version>` head-ref parsing |
| `tools/release/bin/render-announcement-step-summary.php` | ditto | |
| `tools/release/src/AnnouncementRenderer.php` | `src/` | ~275-line PHP class; no cross-references to migrate |
| `tools/release/src/AnnouncementStepSummaryRenderer.php` | `src/` | |
| `tools/release/templates/announcements/` | `templates/announcements/` | Per-channel Twig templates |
| `tools/release/tests/AnnouncementRendererTest.php` | `tests/` | |
| `tools/release/tests/AnnouncementStepSummaryRendererTest.php` | `tests/` | |
| `tools/release/tests/DeriveAnnouncementInputsCliTest.php` | `tests/` | Update fixtures if payload shape changes for the PR-metadata input path |

Devops's copies retire in the last PR of the slice, once the website-
side workflow has fired on at least one real release without
regression.

**Trigger change (the biggest design decision):**

Current: `on: repository_dispatch: types: [openemr-tag]` (dispatched
from `openemr/openemr` at annotated-tag creation time).

New: `on: pull_request: types: [closed]`, filtered to
`base_ref == 'master'` + `head_ref` matches `release-docs/*` +
`merged == true`. The `release-docs/<version>` head-ref pattern
carries the version natively (no parsing of dispatch payload). The
tag / branch inputs derive from that:
- `version` = tail of head-ref after `release-docs/`
- `tag` = `v<major>_<minor>_<patch>` (canonical mapping)
- `branch` = `rel-<major><minor>0` (canonical from `version`)

Fallback: retain `workflow_dispatch` with the current three inputs
(version, tag, branch) so a maintainer can re-render drafts
manually if the PR-closed path missed or the drafts artifact was
lost. Same shape as devops's current workflow_dispatch input list.

Advantages of PR-closed trigger:
- Fires exactly when the download page is live (docs PR merged =
  website re-deployed by `deploy-pages.yml` = new download page
  served)
- Natural website-side event; no cross-repo dispatch loop
- Idempotent by construction: PR-closed fires once per merge, so
  the drafts artifact is produced once per shipped release

**PR sequence (~5 PRs):**

Modeled on the changelog-surface slice's copy → parallel-run →
validate → retire pattern:

- **PR 1** (openemr/website-openemr) — Copy `AnnouncementRenderer` +
  `AnnouncementStepSummaryRenderer` PHP classes + their tests +
  the `templates/announcements/` Twig templates into
  `website-openemr` alongside the existing docs-generator tooling.
  No workflow, no consumer -- just the source of truth for the
  renderer. Test suite runs as pure-PHP isolated (matches the
  existing pattern in `website-openemr/tests/`). Passes on its
  own; devops still owns the live drafts.
- **PR 2** (openemr/website-openemr) — Copy the bin scripts
  (`render-announcements.php`,
  `derive-announcement-inputs.php`,
  `render-announcement-step-summary.php`) with the PR-metadata
  input adaptation. Update `DeriveAnnouncementInputsCliTest`
  fixtures. Bin scripts are runnable but no workflow invokes
  them yet.
- **PR 3** (openemr/website-openemr) — Add
  `.github/workflows/release-announcements.yml` with the new
  trigger (`pull_request: closed` on `release-docs/*` merged into
  master). `workflow_dispatch` retained for manual reruns. First
  live-fire happens on the next real release-docs PR merge (see
  Testing below). Devops's workflow still fires on
  `openemr-tag` -- parallel-run window opens here.
- **PR 4** (openemr/openemr-devops) — Retire devops's
  `release-announcements.yml` + the moved PHP + templates + tests.
  Wired to land only after the website-side workflow has fired
  cleanly on at least one real release (see Testing below).
- **PR 5** (openemr/openemr) — Update `RELEASE_PROCESS.md`'s
  Phase 4 announcement description + the "Runbook step 16"
  (Announce the release) section to reference the new location +
  trigger. Cross-link the drafts artifact URL pattern from the
  new location. Depends on PR 4 for accuracy.

Estimated 3-4 days total, most of which is PR 3's live-fire
validation window waiting for a real docs PR merge.

**Testing strategy per PR:**

- **PR 1:** existing tests port cleanly (pure-PHP isolated), run
  in website-openemr's PHPUnit config. `gen-ehi smoke` covers the
  broader docs-gen path. If any of the copied tests depend on
  fixtures from devops (`tests/fixtures/`), those move too.
- **PR 2:** CLI tests validate the new input adaptation (PR-metadata
  → announcement inputs). Fixture files updated to match the new
  input shape. Manual smoke: run each bin script against known-
  good inputs and byte-compare the rendered channel drafts against
  devops's output for the same version.
- **PR 3:** `workflow_dispatch` from the Actions tab against a
  test release (or the next real release-docs PR). Verify the
  workflow artifact contains the same per-channel files devops's
  currently produces. Diff the two artifacts to confirm byte-
  identical (or explicitly-noted divergence). Live-fire on the
  next real docs PR merge is the acceptance test.
- **PR 4:** the devops workflow retirement -- CI can't validate
  the retirement itself (nothing broken by removing an unused
  workflow), so the gate is the maintainer signal from PR 3's
  live-fire.
- **PR 5:** doc-only, review by eyeball against the shipped
  workflow shape.

**Blast radius (per PR):**

- **PR 1 + 2:** additive on website-openemr; no consumers.
  Risk = zero (nothing calls this code yet).
- **PR 3:** parallel-run window. Both devops's + website's
  workflows fire on the same release event. Risk =
  double-drafts-produced (harmless; maintainer picks one). No
  posting until manual copy/paste.
- **PR 4:** retires devops. Risk = misconfigured
  website-side workflow means no drafts get produced for the next
  release. Mitigated by PR 3's parallel-run window catching that
  before this PR lands.
- **PR 5:** doc-only.

**What Phase 2 (build-release move) inherits after this slice
lands:** devops's `tools/release/` shrinks by
`AnnouncementRenderer` + `AnnouncementStepSummaryRenderer` +
templates + tests. Phase 2's "supporting PHP classes move with
their tests" clause is unchanged (announcement classes never
belonged to that list); the announcements retirement is a
separate concern that clears devops-side surface area for the
umbrella migration.

**Deferred to a later slice (not this one):** automated *posting*
to each channel. The current output is per-channel *drafts* in a
workflow artifact -- posting is still manual copy/paste + a mailing-
list sender script (`oe-sender.js` in `openemr-registration`).
Automating the posting requires per-channel credentials + rate-
limit handling + retry semantics; substantial scope. This slice
only moves the drafter and changes its trigger; posting stays
manual. Placing the drafter in `website-openemr` is the natural
home for the future auto-post workflow to live alongside.

### Phase 2+ outlines (sketches; settle details when each phase starts)

**Phase 2** — `build-release.yml` + `build-release-on-tag.yml` + `build-patch.yml`
move to this repo. Each workflow already operates against this repo's git
state and Docker Hub; the move is mostly relocation + adjusting the
`repository_dispatch` consumer side (the conductor's `openemr-tag` event no
longer needs to cross repo boundaries — can self-dispatch, use
`workflow_run` after `release-prep.yml`'s `finalize` job, or fire on
`push: tags: ['v*']` from the tag `release-prep.yml` creates via
`create-tag.php`; the last is semantically clean and symmetric with how
`docker-build-release.yml` already fires in this repo today — trigger
choice locked at phase kickoff per Decision 2). Devops keeps its
copies live during parallel-run validation; after one clean release through
the new path, devops's copies get deleted in Phase 6. Supporting PHP classes
(PackageAssembler, ChangelogGenerator, PreflightChecker, CompatibilityDeriver,
CompatibilityNotesRenderer) move with their tests.

The natural verification that Phase 2's moved build path produces a
working artifact comes from the sibling
[`artifact-acceptance-testing-plan.md`](artifact-acceptance-testing-plan.md)
(openemr/openemr#12811) — its `acceptance-docker.yml` /
`acceptance-package.yml` workflows exercise the shipped Docker image and
release tarball end-to-end. If acceptance-testing lands before or
alongside Phase 2, it's the ideal validation surface for the
parallel-run window; if not, Phase 2 relies on the manual pre-release
testing pattern used today.

If the "Changelog surface early-migration slice" (above) lands before
Phase 2, `ChangelogGenerator`, `CompatibilityDeriver`, and
`CompatibilityNotesRenderer` are already in openemr and out of devops.
Phase 2's PHP-classes move shrinks to `PackageAssembler` +
`PreflightChecker`; `build-release.yml` moves from a smaller starting
point.

**Phase 3** — `ship-release.yml` + `ShipReleaseOrchestrator` move. Mechanical
relocation since the 2-PR shape was already locked in Phase 1. The port moves
the workflow file + the orchestrator class + all `PullRequest*` classes +
fakes + tests verbatim. Maintainer-visible change: `--repo openemr/openemr-devops`
becomes `--repo openemr/openemr`.

**Phase 4** — `release-announcements.yml` + `AnnouncementRenderer` move with
templates. The Twig template structure (one per channel + a summary) ports
verbatim. The dispatch consumer side switches from cross-repo
`repository_dispatch` to internal trigger.

**Phase 5** — Invert canonical/vendored for the three contract files. Audit
the vendored-files mechanism in this repo (after PR 1c lands a drift check)
to confirm any consumer changes; update devops's references to point at this
repo as canonical. Migrate `VendoredFileChecker` + `check-vendored-contracts.yml`.

**Phase 6** — Delete the migrated workflows + supporting tooling from devops.
Confirm parallel-run validation succeeded for at least one full release cycle
in Phases 2-4 before deletion. Update devops's README. Close
[openemr/openemr-devops#664](https://github.com/openemr/openemr-devops/issues/664)
(the umbrella tracking issue) and its open sub-issues #706/#711/#761 if their
implementation lands as part of the migration.

## Behavior contract

What the maintainer experiences today, that must be preserved (unless
explicitly redesigned and documented):

| Touchpoint | Today | Post-migration |
|---|---|---|
| Cut a new rel-line | `git push` of new `rel-NNN0` from master in this repo | Identical |
| Iterate on a rel branch | Subsequent `git push` to the rel branch | Identical |
| Edit release notes draft | PR-side edit on `release-docs/<version>` in website-openemr | Identical |
| Ship | `gh workflow run ship-release.yml --repo openemr/openemr-devops -f version=X.Y.Z -f rel_branch=rel-XY0` | Same command, `--repo` changes to `openemr/openemr` |
| Number of PRs to land | 3 (Infra in devops + Conductor in core + Docs in website-openemr) | **2** (Conductor + Docs) — locked in Phase 1 |
| Manual docs-PR merge | `gh pr merge` on website-openemr after conductor merge | Identical |
| Patch release | `gh workflow run build-patch.yml --repo openemr/openemr-devops` | Same command, `--repo` changes |
| Investigate permissions | Per-repo `gh workflow run release-permissions-check.yml` | Identical (each repo keeps its own probe; devops's narrows scope) |

Artifacts produced — all preserved identically:

- Annotated tag on `openemr/openemr`
- GitHub Release with distribution packages + checksums + changelog
- Docker Hub publish (already migrated; orchestrator runs daily + on-tag)
- Docker Hub readme push (already migrated)
- `website-openemr` install/upgrade/release-notes pages
- `demo_farm_openemr` tag-row updates
- Per-channel announcement draft artifacts

Ordering guarantees + recovery semantics from
[`RELEASE_PROCESS.md`](RELEASE_PROCESS.md) preserved (post-collapse):

- Strict merge order conductor → docs
- No-partial-merge preflight
- Docs-first refusal (now equivalent to docs-before-conductor)
- Idempotent re-run for partial-merge recovery
- Per-PR readiness re-check before merge

`RELEASE_PROCESS.md`'s Mermaid diagram + partial-merge state table + runbook
step numbering all document the 3-PR flow today; PR 1b updates them in lock
step with PR 1a's code changes.

`repository_dispatch` events — emitted set unchanged from the conductor's
perspective; the *destinations* prune:

| Event | Today's targets | Post-migration targets |
|---|---|---|
| `openemr-rel-cut` | devops + website-openemr | website-openemr (devops drops out) |
| `openemr-rel-update` | devops + website-openemr | website-openemr |
| `openemr-tag` | devops + website-openemr + demo_farm_openemr | website-openemr + demo_farm_openemr |
| Internal consumers (moved workflows) | N/A | self-dispatch or `workflow_run` after `release-prep.yml :: finalize` |

Schema patterns from `dispatch.schema.json` unchanged. `TagVerifier` semantics
unchanged.

## Branch-cut process under the final model

Cutting a new `rel-NNN0` branch post-migration:

1. **Cut the branch** — `git push origin master:rel-NNN0` (or via GitHub UI).
   The new branch inherits the full byte-identical set verbatim (8 files
   currently in FILES_ALL — `docker-build-release.yml`, `docker-test-core.yml`,
   `docker-test-release.yml`, `test-actions-core/action.yml`,
   `.github/docker/compose.yml`, `docker-validate-byte-identical.yml`,
   `docker-byte-identical.yml`, `validate-byte-identical.sh`) plus
   `docker/release/` and everything else in master's tree at cut time.
2. **Adjust the new branch's `docker/release/Dockerfile`** —
   change `ARG OPENEMR_VERSION=master` to `ARG OPENEMR_VERSION=rel-NNN0`
   so hand-built `docker build` runs against the new branch produce a
   sensibly-tagged image. *(CI always overrides this via `--build-arg`
   from `release-targets.yml`, so the literal value only matters for
   local builds; but it should reflect the branch's identity.)*
3. **Add the row** to master's `.github/release-targets.yml` with
   `docker_tags` and `openemr_version_ref` for the new branch. This
   starts the orchestrator dispatching daily docker builds. *(Same as
   today.)*
4. **First `release-prep.yml` fire** — conductor opens `release-prep/rel-NNN0`
   PR on first push, dispatches `openemr-rel-cut` event. Internally this
   triggers the moved consumers (announcements draft, etc.) via self-dispatch.
   External consumers (website-openemr) receive the dispatch and open their
   docs PR.

No manual byte-identical sync step needed anywhere: the auto-sync workflow
(`sync-byte-identical.yml`) keeps the new branch converged with master's
FILES_ALL set on each subsequent master push that touches a managed file,
opening a long-lived `sync-byte-identical/rel-NNN0` PR when drift is detected.
The canary validates each sync PR against master before it merges.

No new maintainer steps relative to today's process. The release-mechanism
migration removes one dispatch destination (devops) without adding any
steps to branch-cut.

## Decisions to lock before phase 2

These don't block Phase 1. They need to be settled before Phase 2's PHP-side
moves start, since they shape the destination structure:

1. **Composer integration** — `openemr/openemr-devops/tools/release/composer.json`
   is its own composer project (own `vendor/`, own PHP version pin). Does the
   moved version (a) stay as its own composer project inside this repo
   (under `tools/release/`), or (b) merge into this repo's root `composer.json`
   as PSR-4 entries + `require-dev`?

   **Locked 2026-07-07: (a) — nested `tools/release/composer.json`.** Not a
   preference call: root-merge is a hard block. The release toolchain
   requires PHP `^8.5` and `symfony/yaml ^8.0`; this repo's root
   `composer.json` requires PHP `^8.2` and `symfony/yaml ^7.3`. Merging
   forces root's PHP minimum to 8.5, breaking every openemr install on
   PHP 8.2/8.3/8.4 (which the CI matrix tests explicitly). Nested keeps
   release-toolchain deps isolated at the cost of one extra
   `composer install --working-dir=tools/release` step in the build
   workflow (~30–60s in CI). No end-user impact — release toolchain deps
   never enter an openemr install's `vendor/`.
2. **Trigger shape for internal event consumers** — for the `openemr-tag`
   consumers that move into this repo (build-release-on-tag,
   release-announcements), three options:
   1. Self-dispatch `repository_dispatch` events to its own repo (lossy
      parity with cross-repo pattern, requires extra PAT)
   2. `workflow_run` triggers tied to `release-prep.yml :: finalize` (no
      extra PAT, but couples the workflows structurally rather than via
      event contract)
   3. `push: tags: ['v*']` — fire on the tag-creation push that
      release-prep.yml already produces via `create-tag.php`. Semantically
      correct (the artifact under build corresponds to a specific tag; if
      the tag exists, we build for it). Symmetric with how
      `docker-build-release.yml` already fires in this repo today. Also
      naturally covers any manual `v*` tag push (emergency release), which
      the other two options don't. Devops uses option 1 today because it's
      cross-repo — that constraint disappears in-repo. **Emerging leaning:
      option 3** unless a same-run causality argument tips it to option 2.
      Lock in phase 2 kickoff.
3. **Token scope for the moved workflows** — today's `release-prep.yml` token
   is scoped to `openemr,openemr-devops,website-openemr,demo_farm_openemr`.
   Post-migration, the scope shrinks to `openemr,website-openemr,
   demo_farm_openemr` (devops drops out). Confirm the App's installation +
   org-variable + org-secret continue to work for the narrowed scope without
   intervention.
4. **`release-permissions-check.yml` reshape** — devops's probe today
   includes a cross-repo dispatch probe back to itself (since devops
   receives dispatches today). Post-migration, devops no longer receives
   dispatches — the probe simplifies. This-repo's probe expands slightly to
   cover the consumers that move here. Mechanical.
5. **Release-prep PR merge is irreducibly required — do not "simplify" by
   creating the tag directly.** Idea floated (verbally) that post-migration
   the release-prep PR could be skipped and `create-tag.php` could just
   fire on some other trigger (workflow_dispatch, cron, a version.php
   push, etc.). It cannot. The release-prep PR does three jobs:
   1. Approval gate — replaceable with any signal.
   2. Audit trail — replaceable with tag messages / commit trailers.
   3. **Delivery mechanism for the mechanical mutations** — irreducible.
      The mutators (`version.php` strip `-dev`, `docker/production/docker
      -compose.yml` image-tag pin, `src/RestControllers/OpenApi/OpenApi
      Definitions.php` version bump, `swagger/openemr-api.yaml` regen)
      land on the rel branch *via the merge commit*. Without the merge,
      the branch tip still says `X.Y.Z-dev` in `version.php` and still
      pins `openemr/openemr:latest` in the compose. Tagging that state
      would put a public `v_X_Y_Z` tag on a tree that reports itself
      as `X.Y.Z-dev`.

   Any "just create the tag directly" proposal has to first answer
   *where do the mechanical edits go*. The only structurally-sound
   answers are (a) a PR merge (which is what release-prep.yml already
   does — reinvented) or (b) an unreviewed direct push to the rel
   branch by the conductor (which loses the approval gate and skips
   the byte-identical/actionlint/etc. CI that runs on the PR). Option
   (b) is a strictly worse version of the PR flow. **Locked from the
   start** — captured here so the "skip the PR" idea doesn't get
   re-litigated later when the migration surfaces new ambitions to
   trim steps.

(The 3-PR-vs-2-PR question was previously listed here; it's locked to 2 PRs,
landing as part of Phase 1.)

(The per-branch-copy-vs-reusable-workflow question is now blocking
**Phase 1** — see the "Pre-Phase-1 architectural decision" section above.
It changes the destination structure of every workflow migrated in
Phase 2+, so needs to be settled before Phase 2 starts and ideally
before the Phase 1 PRs land so they reflect the chosen direction in
their doc updates.)

## Risks and wrinkles to plan for

- **Parallel-run window between Phases 2/3/4 and Phase 6** is the highest-risk
  state — two copies of the workflow exist, both wired to the same triggers.
  Mitigation: gate each phase's devops-side delete on at least one observed
  green release through the new path. Or use feature-flag-style `if:` guards
  in workflows to disable the old copy as soon as the new copy is verified
  on a single release.
  - **Empirical observation lowering this risk (2026-06-23):** The
    conductor's self-heal-on-push pattern (discovered during 8.1.1 prep
    recovery for #12611) means most misfires auto-correct on the next push
    to the rel branch. Recovery flow: fix the bug on a feature branch,
    merge to rel branch, the merge push itself fires the conductor again
    with the corrected logic, peter-evans updates the existing release-prep
    PR in place, consumer dispatches re-fire with corrected payload.
    Mostly applies to the conductor + release-prep PR pattern; less clear
    whether build-release-on-tag / ship-release have equivalent
    self-correcting properties (one-shot artifacts like `gh release upload`
    are harder to redo). Worth empirically validating per-workflow during
    its parallel-run window — successful self-heal lowers the "N green
    releases before Phase 6 delete" bar; brittle artifact-creation
    workflows keep it strict.
- **G7-class bugs (per-branch tooling drift) won't surface until first
  production use** of each migrated workflow on rel-810. The first
  production-use of the conductor (8.1.1 prep) exposed BranchVersionResolver
  drift on rel-810 (fixed via #12611). Build-release / ship-release /
  announcements have not yet been production-fired from rel-810 either;
  they may carry their own latent drift if Phase 2 migrates them as
  per-branch copies. Workstream 3 (per-release-on-rel-branch optimization)
  does a comprehensive rel-810 conductor sync from master which proactively
  catches these. The reusable-workflow alternative (eliminate drift by
  construction) is the deferred option — see "Pre-Phase-1 architectural
  decision" section. (rel-800/rel-704 not in scope — they'll rotate out
  without future releases, so latent drift bugs there will never fire.)
- **`ShipReleaseOrchestrator`'s tests** are the densest seam — 19 test
  classes + 4 in-memory fakes, asserting strict ordering, docs-first
  detection, partial-merge recovery, etc. Phase 3 has to carry the test
  suite along faithfully or risk silent regressions in release-day
  reliability. PHPStan level 10 + strict-rules is non-negotiable per
  `tools/release/README.md`.
- **Canonical/vendored inversion** (Phase 5) requires website-openemr +
  demo_farm_openemr (any other consumers) to update their vendored paths,
  if they have any. Audit confirmed only openemr-core vendors today; verify
  this remains true at the time Phase 5 lands.
- **The `openemr-tag` self-dispatch pattern** requires re-evaluating the
  dispatch payload contract. Cross-repo dispatch goes through GitHub's
  events API; internal self-dispatch may have subtly different ordering
  semantics or latency. Validate empirically in Phase 2 before committing
  the design.
- **Token rotation during migration** — if the org-level
  `RELEASE_APP_CLIENT_ID`/`RELEASE_APP_PRIVATE_KEY` get rotated mid-migration,
  both the old (devops) and new (core) workflow copies need to pick up the
  rotated values. Org-level provisioning makes this transparent, but the
  permissions-check workflow in each repo must be run after rotation to
  surface any per-repo permission drift.
- **CI-of-the-release-tooling** — `release-tools-php.yml` runs the
  release-tooling PHP CI on every PR touching `tools/release/**`. Moving the
  workflow means the CI signal flips from "passes on devops" to "passes on
  this repo." Confirm this repo's PHP toolchain (8.5 + PHPStan level 10 +
  strict rules + PSR12 + composer-require-checker) is set up identically
  before moving Phase 2's classes.

## Rollback

Per-phase rollback is straightforward because the parallel-run window means
devops always has the working copies during Phases 2-5. A failed phase
reverts its destination-side commits in this repo + leaves devops's copies
as the live path. No data loss; releases continue through devops's path
until the destination is fixed.

Phase 1 rollback is also clean — restoring deleted files from git history
brings back the dead-code surface, no harm done (it was dead before, it's
dead after).

Phase 6 (final devops cleanup) is the only point of no easy return — once
devops's copies are deleted, releases must go through this repo's path.
Gate it on N green releases through the new path (N = 2 minimum; ideally a
full minor + a patch).

## Deferred / known debt

- **Open release-mechanism gaps documented in [`RELEASE_PROCESS.md`](RELEASE_PROCESS.md#automation-gaps)**:
  the 5 open sub-issues (openemr-devops#711, #761, website-openemr#132, #133,
  demo_farm_openemr#110) plus the umbrella openemr-devops#706. These are
  orthogonal to the migration but worth re-prioritizing once everything lands
  in one repo.
- **Branch-protection-bypass detection for the conductor PR** — today
  `ship-release.yml` relies on branch protection to require the
  `release/ship-approved` commit status. Admin-overrides bypass this. A guard
  workflow could catch out-of-band merges and dispatch a recovery event.
  Not blocked by the migration; easier to land once everything's in one repo.
- **`peter-evans/create-pull-request` install requirement** vs the custom
  `tools/release/bin/open-rotation-pr.php` (audit finding 4) — unified PR-
  management approach worth considering when Phase 3 lands ship-release.
- **`tools/release/README.md`** itself moves with Phase 2 (it documents
  conventions for the whole module). Update it during the move to reflect
  the new home + remove stale references.
- **Wiki/Confluence release docs** — both [QA and Release Process](https://www.open-emr.org/wiki/index.php/QA_and_Release_Process)
  and [Steps for an official release](https://www.open-emr.org/wiki/index.php/Steps_for_an_official_release)
  reference `openemr-devops` paths. After Phase 6 they should be rewritten as
  pointers to this repo's [`RELEASE_PROCESS.md`](RELEASE_PROCESS.md).
- **No workflow invocation path for `--scope=master`** — the
  `openemr:release-prep` console command supports `--scope=master` (post-cut
  version bump on master via `VersionPhpMasterMutator`), but `release-prep.yml`
  only ever calls `--scope=rel`. No `scope` input on `workflow_dispatch`
  either. Today the master-scope path is purely manual (run the console
  command on a host checkout). Small fix: add `scope` input to
  `release-prep.yml`'s `workflow_dispatch`, propagate through the `php`
  invocation. Could land during the migration or as immediate post-migration
  cleanup.
- **No automatic trigger when `rel-NNN0` is cut** — cutting a new release
  branch is a manual git operation (`git push origin master:rel-NNN0`);
  nothing fires automatically to bump master's version (e.g., advance from
  8.2.0-dev to 8.3.0-dev when rel-820 is cut). More ambitious than the
  previous item -- needs design around target version, who tags, whether
  the bump runs unattended. Natural follow-up to the `--scope=master`
  exposure above.

(More open gaps tracked in [`release-mechanism-gaps.md`](release-mechanism-gaps.md)
-- working notes that aren't migration-blocking but warrant follow-up
post-migration or during the upcoming manual 8.1.1 release work.)

**Post-2026-06-23 gaps (surfaced during 8.1.1 release prep — see gaps doc
G7-G10 for full detail):**

- **G7 — Conductor tooling drift on rel-810** (scope narrowed 2026-06-23 —
  rel-800/rel-704 out of scope since they'll rotate out without future
  releases). rel-810 carries per-branch copies of `tools/release/src/` +
  `.github/workflows/release-*.yml` that silently diverged from master;
  surgical fix #12611 caught the BranchVersionResolver instance, but other
  conductor pieces may have similar latent drift. **Sits in workstream 3
  (per-release-on-rel-branch optimization)** — natural place to do a
  comprehensive rel-810 conductor sync from master as part of optimizing
  the per-release cycle. Was originally tied to a Phase-1-blocking
  reusable-workflow POC; downgraded (see "Pre-Phase-1 architectural
  decision" above) because bounded scope (just rel-810) no longer justifies
  pre-migration restructuring.
- **G8 — No automated regression test for conductor resolvers.** The G7 bug
  shipped silently because `BranchVersionResolver` has no isolated PHPUnit
  coverage exercising `branchToVersion('rel-810')` against realistic tag
  fixtures. Adding tests on master is a small high-value PR; rel-810
  inherits them via the workstream 3 conductor sync. (rel-800/rel-704 don't
  need them — they won't get future releases.)
- **G9 — `release-docs/<version>` PRs on website-openemr don't supersede
  across version changes.** Each conductor re-resolution opens a new PR
  at a new head branch; the old PR sits orphaned. Affects Phase 4 (move
  `release-announcements`) — natural time to also restructure the
  website-openemr docs PR naming to per-rel-branch (`release-docs/rel-810`),
  matching openemr/openemr's `release-prep/rel-810` pattern. Cross-repo
  change so requires coordination with website-openemr maintainers.
- **G10 — Reusable workflows as a replacement for the byte-identical canary
  on the docker pipeline.** Captured as a future post-migration consistency
  pass. If Phase 2+ ever adopts reusable workflows for release-mechanism
  (deferred decision; see "Pre-Phase-1 architectural decision" above), the
  docker pipeline would become the lone holdout using canary; aligning the
  two on the same pattern would be a natural follow-up.

**Post-2026-07-05 gaps closed during 8.2.0 dispatch shake-out (see gaps
doc G12-G13 for full detail):**

- **G12 — `derive-prev-release` returned skipped-version tags.**
  `BranchVersionResolver::previousRelease()` walked annotated
  `v<M>_<m>_<p>` tags and returned the highest below target, but v8_1_0
  was a cut-then-skipped tag (in-repo annotated, but absent from
  `website-openemr/data/releases.json`). Effect: 8.2.0's dispatch
  payload got `prev_release=8.1.0`, so release-notes+acknowledgements
  generated against the wrong ~30-day window. Fixed by consulting the
  website manifest at derive time (openemr/openemr#12769/#12770, with
  follow-up #12771/#12772 for a lightweight-tag secondary bug). The
  `unreleased: true` flag in `release-targets.yml` is transient and NOT
  the durable signal — the website manifest is. Landed same session.
  Validates the "conductor self-heals on next push" pattern for tool-
  behavior fixes, not just misfires.
- **G13 — `release-docs/<version>` branches accumulated divergent
  history.** Byproduct of validating G12's fix. Even after the derive
  correctly returned 8.0.0, PR openemr/website-openemr#164 stayed
  CONFLICTING because the docs branch had been committing on top of the
  previous branch state each dispatch (7 commits since branch cut vs
  master's 9 commits touching the same file). Rebuilt
  `workflow:prepare-publish` in the docs pipeline to unconditionally
  create the branch fresh on master's HEAD each dispatch → single-commit
  branches, clean merge base with master, PR merges cleanly.
  openemr/website-openemr#174/#175/#176.

## Feedback wanted

Specifically on:

- ~~Composer integration shape (Phase 2 decision 1)~~ — **locked 2026-07-07,
  nested `tools/release/composer.json`**. Was open until an investigation
  of the actual dep constraints (PHP `^8.5` + `symfony/yaml ^8.0` in the
  release toolchain vs root's `^8.2` + `^7.3`) made root-merge a hard
  block, not a preference call. See Phase 2 decision 1 for details.
- Self-dispatch vs `workflow_run` for internal event consumers (Phase 2
  decision 2)
- Parallel-run-window risk tolerance — gate each phase's devops-side delete
  on (a) one observed green release, (b) two releases, or (c) a full
  minor + patch cycle?
- Anything else load-bearing the audit + sweep missed

## Optimization opportunities (post-migration, not in scope)

This whole automation system was "work in progress, close to good real-world
use" pre-migration. Once the migration completes, several optimizations
become natural follow-ups (separate from migration scope; tracked here so
they don't fall off):

- Ship-release's "desired end state" per `RELEASE_PROCESS.md` § Partial
  merges — make it tag/Release-aware rather than PR-state-only, which
  retires the manual out-of-band-tag recovery and makes "re-run ship-release"
  the answer for every non-docs-first stuck state.
- Auto-merge the website-openemr docs PR on `openemr-tag` (openemr-devops#761).
- Docs DRAFT→FINAL tag-exists guard (website-openemr#132).
- Docs-first reconciliation workflow (website-openemr#133).
- Automated post-release announcement fan-out (openemr-devops#711).
- Demo-farm production-demo-row seeding on new minor lines
  (demo_farm_openemr#110).
- Unified PR-management approach — the rotation slice used a custom
  `bin/open-rotation-pr.php`; the conductor uses `peter-evans/create-pull-request`.
  With rotation deleted, this divergence goes away naturally.
- Wire the vendored-contract drift check into the post-migration canonical
  location (Phase 5 handles the move; the wiring stays).

## Status

**Drafted 2026-06-20** post-completion of the docker-pipeline migration. Not
yet tracked as a GitHub issue; this doc is the working planning surface.
Phase 1 ready to execute on user signoff.

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
exposed why that decision needs revisiting.** Bug G7 — rel-810's
`BranchVersionResolver` had drifted from master's tag-walking version
without being noticed for weeks. The fix backported master's class to
rel-810 only (openemr/openemr#12611), but rel-800 and rel-704 still
carry the old version; a future use of the conductor on those branches
will hit the same bug.

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

**Recommendation:** Adopt **reusable workflows** for every workflow
migrated in Phase 2+. Captured as a Phase-1-blocking decision because
it changes the destination structure of every phase. The migration's
Phase 1 (rotation deletion + 2-PR collapse) is unaffected; Phases 2-6
get restructured around caller+impl as the default architecture.

**POC scope before locking the decision:** convert today's already-in-core
`release-prep.yml` to caller+impl split, deploy thin caller to rel-810
(currently active rel branch), verify end-to-end behavior on one push
cycle. ~1-2 days of work. If POC succeeds, structurally fixes G7 for the
conductor and informs every subsequent phase.

**Follow-up consideration (out of scope for this migration):** the docker
pipeline today uses byte-identical canary; if reusable workflows are
adopted for release-mechanism, a future consistency pass could migrate
the docker pipeline to the same pattern, retiring the canary system
entirely. Tracked as G10 in the gaps doc. Not urgent — canary works.

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
- **The vendored-contract drift check is NOT currently wired into openemr-core's
  CI.** Devops's `check-vendored-contracts.yml` is reusable but no openemr-core
  workflow calls it. Drift could silently land today. **Pre-existing bug,
  must fix before the migration changes canonical seams.**
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

## Phased plan

Six phases. Phase 1 is pre-migration cleanup (small PRs that don't change
behavior contracts). Phases 2-6 do the actual move with each phase preserving
the 3-PR (or post-collapse) ship-release contract throughout.

| Phase | What | Estimate |
|---|---|---|
| **1. Pre-migration cleanup + 2-PR collapse** | 🟡 Drafted. Three PRs (1 devops + 2 core). PR 1a+1b are coordinated (rotation deletion + 2-PR collapse on devops, paired with cross-repo docs update on core). PR 1c is an independent drive-by (drift-check wiring). Locks the 2-PR shape so Phase 3's ship-release move becomes mechanical. | ~1 day |
| **2. Move build-release + build-patch + supporting tooling** | Move `build-release.yml` + `build-release-on-tag.yml` + `build-patch.yml` + `PackageAssembler` + `ChangelogGenerator` + `PreflightChecker` + `CompatibilityDeriver`/`Renderer` + supporting tests/fakes to this repo. Self-dispatch the `openemr-tag` consumer side. Devops copies stay live until phase 6 (parallel-run validation window). | ~1.5 days |
| **3. Move ship-release** | Move `ship-release.yml` + `ShipReleaseOrchestrator` + `PullRequest*` classes + fakes + tests. Already in 2-PR shape (locked in Phase 1) so the port is mechanical — no contract change in this phase. Devops copy stays live until phase 6. | ~1 day |
| **4. Move release-announcements + permissions probe (core-side scope)** | Move `release-announcements.yml` + `AnnouncementRenderer` + templates. Add the openemr-core-specific permission probes to core's `release-permissions-check.yml`. Devops's permissions-check.yml narrows scope. | ~0.5 day |
| **5. Invert canonical/vendored** | Make this repo canonical for `dispatch.schema.json` + `TagVerifier` + `TagVerificationResult`. Move `VendoredFileChecker` + `check-vendored-contracts.yml` here. Update devops's vendored-copies-of-the-vendored-checker references. If website-openemr or demo_farm_openemr add vendoring later, they pull from here. | ~0.5 day |
| **6. Devops cleanup** | Delete the now-duplicate workflows + `tools/release/` (minus `release-permissions-check.yml`) from devops. Update devops README. Close issue #664 family. | ~0.5 day |

Total active engineering: **~5 days** assuming parallel-run validation windows
between each phase are quick.

### Phase 1 detail (drafted, ready to execute)

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

### Phase 2+ outlines (sketches; settle details when each phase starts)

**Phase 2** — `build-release.yml` + `build-release-on-tag.yml` + `build-patch.yml`
move to this repo. Each workflow already operates against this repo's git
state and Docker Hub; the move is mostly relocation + adjusting the
`repository_dispatch` consumer side (the conductor's `openemr-tag` event no
longer needs to cross repo boundaries — can self-dispatch or use
`workflow_run` after `release-prep.yml`'s `finalize` job). Devops keeps its
copies live during parallel-run validation; after one clean release through
the new path, devops's copies get deleted in Phase 6. Supporting PHP classes
(PackageAssembler, ChangelogGenerator, PreflightChecker, CompatibilityDeriver,
CompatibilityNotesRenderer) move with their tests.

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
   as PSR-4 entries + `require-dev`? Today's pattern in devops is (a);
   simplest port preserves (a); but (b) reduces operational moving parts.
2. **Self-dispatch vs `workflow_run` for internal event consumers** — for
   the `openemr-tag` consumers that move into this repo (build-release-on-tag,
   release-announcements), should the conductor self-dispatch
   `repository_dispatch` events to its own repo (lossy parity with cross-repo
   pattern, requires extra PAT), or use `workflow_run` triggers tied to
   `release-prep.yml :: finalize` (no extra PAT, but couples the workflows
   structurally rather than via event contract)?
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
  production use** of each migrated workflow on a rel branch. The
  release-mechanism migration's first production-use was 8.1.1 prep —
  exposed BranchVersionResolver drift on rel-810 (fixed via #12611).
  Build-release / ship-release / announcements have not yet been
  production-fired from rel-810 either; they may carry their own latent
  drift on rel branches if Phase 2 migrates them as per-branch copies.
  Adopting reusable workflows (per the Pre-Phase-1 architectural
  decision section) eliminates this risk structurally for the migration's
  migrated surfaces.
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

- **G7 — Conductor tooling drift on pre-820 rel branches.** rel-810/800/704
  carry per-branch copies of `tools/release/src/` + `.github/workflows/release-*.yml`
  that silently diverge from master. Surgical fix #12611 applied to rel-810
  only; rel-800 + rel-704 still carry stale tooling. **Affects the migration's
  Pre-Phase-1 architectural decision** (per-branch copies vs reusable
  workflows) — the migration is the natural time to either backport the full
  conductor toolchain to all pre-820 rel branches, or restructure to reusable
  workflows so drift becomes impossible by construction. Recommend tying the
  G7 cleanup to the reusable-workflow POC: a successful POC obsoletes the
  need to backport stale code to rel-800/rel-704 (those branches just get
  thin caller stubs that always invoke master's current impl).
- **G8 — No automated regression test for conductor resolvers.** The G7 bug
  shipped silently because `BranchVersionResolver` has no isolated PHPUnit
  coverage exercising `branchToVersion('rel-810')` against realistic tag
  fixtures. Adding tests on master is a small high-value PR; combining with
  the reusable-workflow pattern means rel branches automatically inherit
  the tests' protection via the impl-on-master architecture.
- **G9 — `release-docs/<version>` PRs on website-openemr don't supersede
  across version changes.** Each conductor re-resolution opens a new PR
  at a new head branch; the old PR sits orphaned. Affects Phase 4 (move
  `release-announcements`) — natural time to also restructure the
  website-openemr docs PR naming to per-rel-branch (`release-docs/rel-810`),
  matching openemr/openemr's `release-prep/rel-810` pattern. Cross-repo
  change so requires coordination with website-openemr maintainers.
- **G10 — Reusable workflows as a replacement for the byte-identical canary
  on the docker pipeline.** Captured as a future post-migration consistency
  pass. If Phase 2+ adopts reusable workflows for release-mechanism, the
  docker pipeline becomes the lone holdout using canary; aligning the two
  on the same pattern is a natural follow-up.

## Feedback wanted

Specifically on:

- Composer integration shape (Phase 2 decision 1) — keep `tools/release/` as
  its own composer project or merge into root `composer.json`?
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

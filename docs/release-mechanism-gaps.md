# OpenEMR Release Mechanism: Gaps & Open Questions

Working notes — gaps surfaced during release-automation exploration that
aren't blockers for the current migration but warrant follow-up. Captures
things to investigate or address AFTER the release-mechanism migration
completes, plus discoveries during the upcoming manual 8.1.1 work.

**Last updated:** 2026-07-07

Migration-related gaps also appear in the planning doc's `## Deferred /
known debt` section:
`/home/brady2/git/openemr-wt-release-mechanism-migration-doc/docs/release-mechanism-migration-from-devops.md`

**Artifact-testing gap tracked separately.** "How do we verify the shipped
Docker image + release tarball actually install and upgrade cleanly for
end users?" is not a gap in release-mechanism scope — it's being
addressed by the sibling
[`artifact-acceptance-testing-plan.md`](artifact-acceptance-testing-plan.md)
(openemr/openemr#12811, draft). Anyone reading this file looking for
that gap: it's not here on purpose. It sits at a distinct layer —
release-mechanism owns the *pipeline that produces* artifacts;
acceptance-testing owns the *verification that they work*.

## Quick context

- **Current branch under release work:** `rel-810` (8.1.0 was cut as
  artifact only — was buggy, removed; next planned release is **8.1.1**).
- **Conductor (`release-prep.yml`)** lives in `openemr/openemr` already.
- **Consumers (build / ship)** still in `openemr-devops`;
  release-mechanism migration in flight. **Announcements consumer**
  migrated 2026-07-18/19 to `openemr/website-openemr` — fires on
  docs-PR-merge (`pull_request:closed` on `release-docs/*`) instead
  of `openemr-tag`; see `openemr/website-openemr#194` for the workflow
  + `openemr/openemr-devops#861` for the devops-side retirement.
- **Patch flow (`build-patch.yml`)** intentionally separate from main
  automation — emergent-patch escape hatch only.

## Gaps identified

### G1 — `GlobalsIncMutator` skipped via `--skip-globals` in production

- **What:** `release-prep.yml` invokes
  `php bin/console openemr:release-prep --skip-globals --target-version=$V --scope=rel`.
  The `--skip-globals` flag disables the `GlobalsIncMutator`
  (`library/globals.inc.php` mutation).
- **Why I care:** Prior manual releases edited `library/globals.inc.php`
  by hand. The mutator exists but is intentionally disabled in production
  runs — why? Mutator behavior may not match current release-model intent,
  or it may have been disabled during conductor development and never
  re-enabled.
- **TODO:** Read `src/Common/Command/ReleasePrep/Mutator/GlobalsIncMutator.php`
  to understand what it would do; check git log for when `--skip-globals`
  was added and why.
- **Status:** Not investigated.

### G2 — Post-release version bump (rel-810 → next "-dev")  *(effectively covered 2026-07-20 — workstreams 2 + 6)*

- **What:** After v8_1_1 is tagged, rel-810's `version.php` needs to
  advance to something like "8.1.2-dev" so subsequent pushes correctly
  enter the 8.1.2 cycle (per the conductor's expected starting state).
  Not visible in the obvious mutator set at the time this gap was
  filed.
- **Why I care:** If this isn't automated, it's a manual post-release
  step that has to happen between cycles. Otherwise rel-810 keeps trying
  to re-release 8.1.1.
- **Effective status (verified 2026-07-20):** covered in code, though
  never closed here.
  - Master-side bump handled by `VersionPhpMasterMutator` (workstream
    2, invoked via branch-cut).
  - Rel-side patch-cycle bump handled by
    `PatchPrepReleaseTargetsMutator` (workstream 6 / openemr/openemr#12697,
    `patch-prep-automation.yml`).
  - Not fully traced across a complete post-tag → next-push cycle (the
    original TODO). Coverage is much better than the original "not
    investigated" framing, but a formal cycle trace hasn't been done.
- **TODO (optional, low-priority):** trace `version.php` state across a
  complete cycle to confirm the mutator pair leaves no manual seam
  between the tag write and the next dev push on the same rel branch.

### G3 — `SqlUpgradeSkeletonMutator` behavior  *(investigated 2026-06-22 — master-only + unwired)*

- **What it does:** Master-only mutator. Scaffolds
  `sql/<from>-to-<to>_upgrade.sql` where `<from>` is read from the
  current master `version.php` (`$v_major.$v_minor.$v_patch`) and
  `<to>` is the conductor's target version. Body is the comment-meta-
  language header from the most recent existing `sql/*-to-*_upgrade.sql`,
  no SQL statements. Idempotent (no-op if the file already exists or
  if from == to). Reference:
  `src/Common/Command/ReleasePrep/Mutator/SqlUpgradeSkeletonMutator.php`,
  class docstring line 3: *"Master-only: scaffold the next
  sql/X_Y_Z-to-A_B_C_upgrade.sql file by copying the comment-meta-
  language header from the most recent upgrade file."*
- **Master-only means scope=master only.** The mutator is in the
  conductor's `--scope=master` mutator set, not `--scope=rel`. So:
  - **Releases from rel-* branches do NOT auto-drop a new SQL
    skeleton.** Conductor runs with `--scope=rel` (per `release-prep.yml`)
    and SqlUpgradeSkeletonMutator simply isn't in that set.
  - **Master-side conductor runs would drop it**, but those runs have
    no workflow path today (see G4). The mutator is currently
    pure-code-with-no-trigger.
- **Practical consequence:** the SQL skeleton work is entirely manual
  today. For rel-branch releases, maintainer creates the next-cycle
  blank file by hand (see "Per-release-on-rel-branch" section below for
  the manual procedure, including the cross-branch master sync + the
  file-rename dance on master).
- **When G4 lands**, master-side conductor runs (workflow_dispatch
  with `scope=master`) would auto-drop the master-side blank, leaving
  only the rel-branch-side blank + master-side rename dance as manual
  steps.
- **Consumed 2026-07-01 by workstreams 2 + 6.** Workstream 2's
  branch-cut automation (openemr/openemr#12696) now runs
  `SqlUpgradeSkeletonMutator` as part of `openemr:branch-cut --side=master`
  when a `rel-NNN0` is cut — auto-dropping the master-side blank at
  cut time as anticipated. Workstream 6's patch-prep automation
  (openemr/openemr#12697) then handles the master-side rename dance +
  the rel-branch-side blank on subsequent patch dev-cycle starts,
  reusing `SqlUpgradeSkeletonMutator` via the new
  `MutatorContext::$fromVersion` extension. See G12 for the full
  patch-cycle bootstrap flow.

### G4 — No workflow invocation path for `--scope=master`  *(branch-cut prerequisite)*

**STATUS: SHIPPED 2026-07-01** as part of workstream 2's
openemr/openemr#12696 (merge commit `6513b487b6`). Reality diverged
mildly from the original G4 framing: rather than wiring
`VersionPhpMasterMutator` under a new invocation path of the existing
`openemr:release-prep --scope=master`, workstream 2 introduced a
sibling command `openemr:branch-cut` with its own mutator lists (see
G5's shipped note for the full command shape). `--scope=master` on
`openemr:release-prep` was formalized as release-time only in
workstream 3 Phase A; `VersionPhpMasterMutator` now runs from
`openemr:branch-cut --side=master` inside the branch-cut workflow.
Net effect for the master-side advance is the same as the original
design intent.

- **What:** The `openemr:release-prep` console command supports
  `--scope=master` for "post-cut version bump on master" via
  `VersionPhpMasterMutator`. But `release-prep.yml` only ever calls
  `--scope=rel`. No `scope` input on `workflow_dispatch` either.
- **Why I care:** When `rel-820` is cut from master in the future,
  master needs `version.php` advanced (e.g., 8.2.0-dev → 8.3.0-dev).
  Today this is purely manual or requires running the console command
  directly on a host checkout.
- **Design (folded into G5's branch-cut automation):** Don't add a
  `scope` input to `release-prep.yml`. Instead, a separate
  `branch-cut-automation.yml` workflow (G5) invokes the master-scope
  mutator as one of its steps when it fires on `on: create`. Keeps
  `release-prep.yml`'s scope narrow (rel-branch-only) and consolidates
  cut-time work into one place. The console command's `--scope=master`
  surface stays as-is for ad-hoc + manual fallback.
- **Status:** Implementation lives in workstream 2 (rel-820 cut
  readiness) of the migration plan. See G5 for the full automation
  design.

### G5 — No automatic trigger when `rel-NNN0` is cut  *(workstream 2 design)*

**STATUS: SHIPPED 2026-07-01** as workstream 2 / openemr/openemr#12696
(merge commit `6513b487b6`). Delivered the full design as scoped:
new `openemr:branch-cut` command + `.github/workflows/branch-cut-automation.yml`
(triggering on `create` for `rel-[0-9]*0` refs + `workflow_dispatch`
escape hatch) + 4 new mutators (`DockerUpgradeScaffoldMutator`,
`DockerfileOpenemrVersionMutator`, `TranslationFileCopyFromPriorRelMutator`,
`BranchCutReleaseTargetsMutator`) + tests, all in a single PR as
planned. Reality matched design; the mutator audit's "5 already exist,
4 new needed" split held up. Rabbit-fix follow-up shipped in
openemr/openemr#12709 (merge `8a01001a27`) — 3 Major items missed at
the initial merge (App-token permission scoping + `persist-credentials: false` on 3 checkouts in
the new workflow + `endsWith` gate tightening). First real end-to-end
production exercise is deferred to the rel-820 cut.

- **What:** Cutting a new `rel-NNN0` branch is a manual git operation
  (`git push origin master:rel-NNN0`). Nothing fires automatically on
  this event to:
  - Advance master's `version.php` from `8.X.0-dev` → `8.X+1.0-dev`
  - Add the new rel-NNN0 row to `.github/release-targets.yml`
  - Rotate the `next` Docker tag from master to rel-NNN0
  - Add the new SQL upgrade skeleton + adjust the bridge file chain
  - Edit rel-NNN0's `docker/release/Dockerfile`
    (`ARG OPENEMR_VERSION=master` → `ARG OPENEMR_VERSION=rel-NNN0`)
- **Design (2026-06-27):** New workflow
  `.github/workflows/branch-cut-automation.yml` on master with:

  ```yaml
  on:
    create:
      # No path filter — `create` events fire per-ref-creation,
      # not per-file-change.
  ```

  Inside the job:
  - Check `github.event.ref_type == 'branch'` and `github.event.ref`
    matches `rel-[0-9]+0` (otherwise return early — only fires for
    actual rel-branch creation, not tags or other branches).
  - Derive target version from the branch name
    (`branch-to-version.php` already does this — extends to handle
    the freshly-cut case where no tag exists yet, returning the base
    version).
  - Open **two coordinated PRs** (same shape as the release-time
    partner PR pattern from G11):
    - **rel-NNN0-side**: small PR with the
      `docker/release/Dockerfile` ARG edit. CI overrides this via
      `--build-arg`, so it's cosmetic for local builds, but worth
      keeping consistent with branch identity.
    - **master-side**: version.php advance via
      `VersionPhpMasterMutator` (existing — see G4), add the rel-NNN0
      row to release-targets.yml via a new
      `AddReleaseTargetsRowMutator`, rotate `next` from master to
      rel-NNN0, add the SQL skeleton + bridge-file-rename dance via
      a new `SqlSkeletonAdvanceMutator`.

- **Interaction with `release-prep.yml` (conductor):** The conductor
  ALSO fires on the cut push (it has `on: push:` matching
  `rel-[0-9]*0`). On a freshly-cut rel-NNN0:
  - `branch-to-version.php('rel-NNN0')` returns the base version
    (e.g., `8.2.0` for rel-820, since no `v8_2_0` tag yet)
  - Mutators run, suggest 8.2.0 release-prep content
  - Opens `release-prep/rel-NNN0` draft PR

  This is **premature** — 8.2.0 isn't shipping at cut time. Two ways
  to handle:
  - **(a) Accept** the draft PR existing from day one; mostly inert;
    re-rendered on each push during the dev cycle. **Recommended**
    — small visual nuisance, low engineering cost.
  - **(b) Conductor suppresses** its first run after a cut event.
    Possible via a "cut-marker" file the cut workflow writes that
    the conductor checks. Adds non-trivial logic with edge cases.

- **Status:** Workstream 2 (rel-820 cut readiness). Tightly coupled
  with G4 (master-scope mutator wiring) — same workflow handles both.
  Implementation depends on whether the project has an upcoming rel
  cut event to use as the proving ground.

- **Refinement (2026-06-30) — concrete file inventory + mutator audit:**

  Concretely, at the cut of `rel-NNN0` (e.g., rel-820), the workflow
  opens **two coordinated PRs** with the following contents.

  **Both sides — docker upgrade scaffolding** (per PRs #12608/#12609 +
  `feedback_docker_upgrade_actions_mandatory.md` memory):
  1. Bump 3 docker-version files (currently `11` → `12` at rel-820 cut):
     `docker-version`, `docker/release/upgrade/docker-version`,
     `sites/default/docker-version`.
  2. Create `docker/release/upgrade/fsupgrade-(N+1).sh` as a stub; per-
     release work fills in the actual upgrade body before each ship.
  3. Update `docker/release/Dockerfile` to add `fsupgrade-(N+1).sh` to
     BOTH the `COPY upgrade/...` block AND the `RUN chmod 500 ...` block.

  **Rel-NNN0 side specific** (the new branch):
  4. Replace `contrib/util/language_translations/currentLanguage_utf8.sql`
     with the file contents from the most recent prior rel branch (for
     rel-820 cut, from rel-810). The file is ~250k lines; the bot fetches
     prior rel's blob via git, doesn't synthesize.
  5. In `library/globals.inc.php`: flip `allow_debug_language` default
     from `'1'` (dev) to `'0'` (production). The comment in-file
     spells it out: *"default = true during development and false for
     production releases."*
  6. In `docker/release/Dockerfile`: change `ARG OPENEMR_VERSION=master`
     to `ARG OPENEMR_VERSION=rel-NNN0`. Single-line surgical edit.

  **Master side specific** (next-dev advance):
  7. `version.php`: bump `$v_minor` (8.M.0-dev → 8.(M+1).0-dev), reset
     `$v_patch = '0'`, keep `$v_tag = '-dev'`.
  8. `src/RestControllers/OpenApi/OpenApiDefinitions.php`: bump the
     `#[OA\Info(version: '8.M.0')]` attribute to `8.(M+1).0`.
  9. `swagger/openemr-api.yaml`: regenerated from #8 via the existing
     `openemr:create-api-documentation` command.
  10. Create `sql/8_M_0-to-8_(M+1)_0_upgrade.sql` as a stub containing
      the "Comment Meta Language Constructs" big header (~150 lines of
      `--` comments documenting `#IfNotTable`, `#IfColumn`, etc.; no
      body).
  11. `.github/release-targets.yml`: add a new row for rel-NNN0
      (`docker_tags: 8.M.0,next`, `openemr_version_ref: rel-NNN0`),
      bump the master row's docker_tags (drop `next` if present + bump
      minor, e.g., `8.2.0,dev,next` → `8.3.0,dev`), drop any rows
      marked `unreleased: true` (covers the skip-line scenario; see
      below).

  **Mutator audit (2026-06-30) — most of this already exists:**

  | Mutator | Reusable as-is for cut? |
  | --- | --- |
  | `VersionPhpMasterMutator` | ✅ Already exists. Item #7 above. |
  | `OpenApiVersionMutator` | ✅ Already exists. Item #8. |
  | `SwaggerRegenMutator` | ✅ Already exists. Item #9. Runs AFTER OpenApiVersionMutator. |
  | `SqlUpgradeSkeletonMutator` | ✅ Already exists. Item #10. Reads "from" from version.php; "to" from target. |
  | `GlobalsIncMutator` | ✅ Already exists. Item #5. |
  | `DockerUpgradeScaffoldMutator` | **NEW** — items #1, #2, #3 (could be 1 mutator or split into 3). |
  | `DockerfileOpenemrVersionMutator` | **NEW** — item #6 (rel-side only). |
  | `TranslationFileCopyFromPriorRelMutator` | **NEW** — item #4 (rel-side only). Needs git fetch of prior rel's blob. |
  | `BranchCutReleaseTargetsMutator` | **NEW** — item #11. Sibling to G11's `PostReleaseTargetsMutator`; same line-based surgical-edit approach to preserve comments. |

  **Command + workflow shape:**

  - **New command** `openemr:branch-cut` (sibling to `openemr:release-prep`,
    not an extension of `--scope=master` — Phase A in G11 established
    `--scope=master` as release-time only). Takes `--target-version`,
    `--rel-branch`, `--prev-rel-branch`, internal `--side=rel|master`
    to pick mutator list. Each side has its own list:
    - `relSideMutators`: `[DockerUpgradeScaffoldMutator,
      DockerfileOpenemrVersionMutator, TranslationFileCopyFromPriorRelMutator,
      GlobalsIncMutator]`
    - `masterSideMutators`: `[DockerUpgradeScaffoldMutator,
      VersionPhpMasterMutator, OpenApiVersionMutator, SwaggerRegenMutator,
      SqlUpgradeSkeletonMutator, BranchCutReleaseTargetsMutator]`
  - **New workflow** `.github/workflows/branch-cut-automation.yml` on
    master. Triggers: `on: create:` filtered to `refs/heads/rel-[0-9]*0`
    via `github.event.ref_type == 'branch'` + regex match in a job
    `if:`. Plus `workflow_dispatch:` with `rel-branch` input for manual
    override/recovery. Mirrors Phase A's dual-checkout pattern: one
    job, two PRs opened via peter-evans against different branches.

  **Skip-line cut scenario (e.g., 8.1.1 skipped; rel-820 cut while
  rel-810 still exists but won't ship):** Maintainer pre-flags BOTH
  rel-810 rows with `unreleased: true` BEFORE the cut. Once both rel-810
  rows are unreleased, the `next` slot has no published owner, so master
  acquires `next` interim (master's docker_tags becomes `8.2.0,dev,next`).
  When the branch-cut workflow fires:
  - `BranchCutReleaseTargetsMutator` adds the rel-820 row with `8.2.0,next`
    (rel-820 takes the `next` slot from master).
  - Same mutator drops master's `next` (back to `8.3.0,dev`) and bumps
    minor.
  - Same mutator removes the `unreleased: true` rows uniformly (covers
    skip-line cleanup AND normal cut cases — no-op if none present).

  The "normal cut" path (rel-810 shipped 8.1.1, slot already shuffled
  by G11's PostReleaseTargetsMutator) produces an end-state identical to
  the skip-line path: master `8.3.0,dev`, new rel `8.M.0,next`. The
  mutator handles both cases uniformly without conditionals.

  **Goal for the implementation PR: ONE PR for the entire workstream 2**
  (new command + 4 new mutators + new workflow + tests). Keeps review
  surface manageable for OpenEMR admin review. Trade-off vs. splitting:
  a single coordinated PR is easier to reason about end-to-end (the
  pieces only make sense together) and reviewers don't have to mentally
  stitch together a half-shipped feature across multiple commits.

  **Conditional sequencing — resolved 2026-07-01 (revised):** 8.1.x is
  being skipped entirely — no 8.1.1 ship. Pre-cut posture landed in
  openemr/openemr#12712 (both rel-810 rows marked `unreleased: true`,
  `next` moved to master interim). rel-820 cut becomes the first
  end-to-end exercise of branch-cut automation AND (subsequently) of
  the paired release-prep + release-finalize conductor (at 8.2.0 ship
  from rel-820). Workstream 3 Phase B (cherry-pick to rel-810) is
  unnecessary — rel-820 inherits all three automations in-place.

<a id="g5-refinement-2026-07-02"></a>
- **Refinement (2026-07-02) — first production exercise:**

  The rel-820 cut on 2026-07-02 was the first end-to-end production
  exercise of branch-cut alongside the release-prep + release-finalize
  pair. It took **three attempts and three rounds of fixes** before
  landing clean on attempt four. The bugs were meaningful — worth
  cataloging so future exercises benefit.

  **Round 1** (fix PRs #12722 + cherry-pick #12723; #12724):
  - `--skip-globals` missing from both `branch-cut-automation.yml` and
    `patch-prep-automation.yml` CLI invocations of the Symfony console
    command — caused `mysqli_query` bootstrap error on every run.
  - `release-prep.yml` had `paths-ignore: docs/**` which filtered
    the conductor out at cut time whenever master's tip was a
    docs-only PR (as it was on 2026-07-01 for #12721). Removed —
    the conductor now fires on every push, and peter-evans's
    `pull-request-operation=none` signal suppresses downstream
    consumer dispatch when no diff.

  **Round 2** (fix PR #12731 — 5 mutator surface fixes):
  - `DockerUpgradeScaffoldMutator`: `fsupgrade-(N+1).sh` is now a
    **full copy** of the prior file with 5 header lines substituted
    (docker versions + priorOpenemrVersion), not a bare stub. Preserves
    the previous release's upgrade body byte-for-byte for per-release
    refinement.
  - `PostReleaseTargetsMutator`: early-return / no-op when the target
    rel branch has no live row in release-targets.yml (avoids
    incorrectly trying to slot-shuffle rows that don't exist yet at
    premature-draft time).
  - `ReleasePrepCommand`: dropped `GlobalsIncMutator` from the
    release-prep rel-side list. Branch-cut owns that flip; running it
    again at release-prep time is redundant and hides "did branch-cut
    merge?" behind mutator idempotency.
  - `DockerComposeProductionMutator`: swap the tag only. No
    `--image-digest`, no `@sha256:...` output. At release-prep time
    the release image doesn't yet exist in Docker Hub (tag→build fires
    only after release-prep merges), so there is no valid digest to
    pin — chicken-and-egg. Any existing `@sha256:...` suffix on the
    source line is dropped.
  - `MutatorContext::$imageDigest` removed entirely (was unused after
    the digest handling was dropped).

  **Round 3** (fix PR #12735 — 2 mutator fixes + PR template audit):
  - `SqlUpgradeSkeletonMutator`: no more double trailing newline in
    the generated skeleton output.
  - `DockerUpgradeScaffoldMutator`: `priorOpenemrVersion` derivation
    now scans `sql/*_upgrade.sql` for the highest LEFT-side version
    at branch-cut time, with `MutatorContext::$fromVersion` override
    for patch-prep (where version.php has already been bumped past
    the anchor). A `priorOpenemrVersion < target` invariant defends
    the derivation — mutator ordering bugs (e.g.,
    `SqlUpgradeSkeletonMutator` running before this one and inflating
    the sql-scan result to the target version) now throw loudly
    rather than silently producing wrong scaffolding.
  - PR body templates audited for stale content: 4 of the 6
    templates (branch-cut-rel, branch-cut-master, patch-prep-rel,
    patch-prep-master) updated to remove "stub" language, describe
    the 5-line substitution shape, note `priorOpenemrVersion`
    derivation, and expand merge-order guidance. release-prep +
    release-finalize templates had no drift.
  - Coderabbit follow-up on #12735 hoisted validation before
    destructive writes for atomicity.

  **Attempt 4 (clean):** All three PRs opened correctly —
  branch-cut rel-side #12743, master-side #12744, release-prep
  #12742. Merging rel-side #12743 re-fired the conductor and
  auto-updated release-prep #12742. Merging master-side #12744 wired
  master's release-targets.yml row for rel-820, kicking off:
  docker-release-orchestrator, notify-release-targets-changed,
  demo_farm auto-derive, and dockerhub-readme-push. All fired cleanly.
  - Docker Hub: `openemr/openemr:8.2.0` + `next` + `8.2.0-2026-07-02`
    with OCI `revision: rel-820`; `8.3.0` + `dev` + `8.3.0-2026-07-02`
    with `revision: master`.
  - Dockerhub README refreshed.
  - `demo_farm_openemr#168` auto-derive reconciliation PR opened cleanly.
  - release-finalize PR pending — will materialize on next natural
    push to rel-820 (the "release-finalize doesn't auto-refresh on
    master pushes" gap called out in the Lifecycle section of
    `release-automation-plan.md` is real but not blocking; dev
    cycle pushes to the rel branch happen naturally).

  **Takeaway:** Mutator ordering matters and is now defended by the
  `priorOpenemrVersion < target` invariant. The premature-draft
  design worked as documented — accepted tradeoff, zero external
  noise. Three rounds of fixes is not ideal but each round caught
  something real that would have bit at some future exercise.

### G6 — demo_farm_openemr's production-demo + flex-image mappings are manually maintained

**STATUS: SHIPPED 2026-06-28.** Bot operational end-to-end. Demo_farm PRs: #135 (scaffold + dry-run), #138 (write + auto-PR mode), #141 (atomic flip retiring `bump-tag.yml` + `tools/release/` PHP toolchain), #142 (printf-dash bash bug fix), #143 (first real bot-produced reconciliation PR — `rel-704/800/810` col-3 tag-pin transitions). Sibling infra: #136 (dependabot github-actions weekly), #139 (shellcheck workflow with ratchet `.shellcheckrc`), #140 (issue tracking first ratchet: SC2115 rm-rf guard, 5 sites in demo_build.sh). Cross-repo dispatch event: openemr-devops#846 (canonical `release-targets-changed` event in dispatch.schema.json), openemr/openemr#12657 (vendored schema + `EVENT_RELEASE_TARGETS_CHANGED` + DispatchDataBuilder case + `.github/workflows/notify-release-targets-changed.yml` firing on push to release-targets.yml). Bonus phpstan CI cleanup: openemr#12658 (COMPOSER_AUTH band-aid for the wkhtmltopdf 429 flake) + openemr#12659 (real fix: dropped vestigial `Remove Rector` step — empirically verified phpstan output is byte-identical with rector installed vs removed).

**Bot behavior now:** daily 07:00 UTC + `repository_dispatch types=release-targets-changed` (immediate on upstream push) + manual `workflow_dispatch` (mode=reconcile|dry-run). On diff: force-push stable branch `auto-derive/reconciliation` + open/update PR. On no-diff: close any open reconciliation PR + delete remote branch. Stable-branch design = at-most-one bot PR open at any time; bot updates the same PR across days rather than spamming new ones.

**G6 shipping notes (lessons worth keeping):**

1. **Vendor sync dance is required when the canonical dispatch schema changes.** The dispatch.schema.json canonical lives in openemr-devops; openemr/openemr (and previously demo_farm via tools/release/) vendor it. Adding a new event required a devops PR (#846) BEFORE the openemr-side PR (#12657) could clear the check-vendored-contract drift check that #12619 wired into openemr CI. The check-vendored test fixtures under `tests/fixtures/vendored/good/` + `good-overrides/` also need re-sync — they're byte-identical copies of the canonical and break if you forget.

2. **The phpstan flake had a real root cause.** `composer remove` triggers full dep resolution which re-probes every `repositories.type=vcs` URL in composer.json. The wkhtmltopdf-openemr vcs entry is from 2021, isn't required by any package, but Composer probes it every time. GitHub's secondary rate limit on `/repos/.../commits/<sha>` fires on the burst. Other openemr workflows do `composer install` (reads lock, no probes) — phpstan was uniquely affected. Real fix: drop the Remove Rector step entirely (modern rector + phpstan don't interfere anymore, empirically verified byte-identical output).

3. **bash printf with format starting with `-` aborts with "invalid option".** Caught in the wild by #142 when the scheduled bot run first hit the diff-path code: `printf '- bullet\n'` is parsed as printf trying to take a `- ` option. Fix: `printf '%s\n' '- bullet'`. Worth grepping for in any new shell-heredoc workflow.

4. **For PR triggers, GitHub Actions uses the workflow file from the PR's HEAD branch, not master.** So workflow fixes that land in master don't help open PRs until they rebase. Forced this dance for #12657: rebase to pick up #12658 → still failed (same flake) → land #12659 → rebase again → passed.

- **What:** Two pieces of state in `openemr/demo_farm_openemr` need
  to track openemr/openemr's release state but currently don't have
  any cross-repo sync:
  - **`ip_map_branch.txt` production rows** (`branch_tag=tag`,
    cluster `five`/`five_a`/`five_b`) — pinned at a specific release
    tag. `bump-tag.yml` advances these for patch releases on the
    same minor line (e.g., v8_0_0_3 → v8_0_0_4), but cross-minor
    seeding (e.g., the eventual v8_0_0_3 → v8_1_1 cutover) is a
    manual #111-style PR.
  - **`docker/scripts/demoLibrary.source` `startDemoWrapper`
    case statement** — maps each cluster to a flex base image
    (e.g., `flex-3.22-php-8.4`). Should match the corresponding
    rel branch's `docker/release/Dockerfile` ARGs (ALPINE_VERSION
    + PHP_VERSION) in openemr/openemr, so demo runtime matches
    production runtime. Currently has no cross-repo sync; manual
    maintenance only.
- **Concrete drift example (2026-06-23):** Cluster `five` carried
  `flex-3.23-php-8.5` (rel-810's Dockerfile combo) for the entire
  pre-#111 v8_0_0_3 era + the post-#133 v8_0_0_3 era, when
  rel-800's Dockerfile is actually 3.22 + 8.4. demo.openemr.io ran
  v8_0_0_3 source on a newer runtime than the released image.
  Closed by demo_farm_openemr#134.
- **Why this matters:** demo consumers see the "production demo"
  image as representative of what they'd deploy themselves
  (`docker pull openemr/openemr:8.0.0.3`). Runtime drift between
  the demo and the actual release undermines the demo's
  representativeness. Same concern for rel-810 / rel-704 dev demos
  if their flex-image mapping drifts from their rel branch's
  Dockerfile combo.
- **The auto-generation vision** (per maintainer, 2026-06-23,
  refined 2026-06-27): the recent demo_farm reorg to consolidate
  around flex docker bases (vs the prior per-version base images)
  was deliberate preparation for this. **Goal: auto-derive the
  entire `ip_map_branch.txt` + `demoLibrary.source`
  `startDemoWrapper` case statement from upstream openemr/openemr
  state via a daily reconciliation script** — with a single hand-
  curated "Miscellaneous" section reserved for non-standard demos
  that don't fit any derivable pattern.

  **Reconciliation, not from-scratch render.** Cluster identity is
  sticky across runs because each cluster name maps to a subdomain
  (e.g., `eight` → `eight.openemr.io`) that's referenced from
  external surfaces (wiki, social, mail). Reassigning a cluster's
  meaning breaks those external references. The bot reads the
  CURRENT `ip_map_branch.txt` as state, applies a reconciliation
  diff against the desired state computed from upstream, and writes
  the new file preserving cluster→subdomain stability.

  **Per-input source map:**

  | Input | Read from | Per-branch or master-only |
  |---|---|---|
  | `release-targets.yml` (list of rel branches, latest holder, `unreleased` flags) | openemr/openemr **master** | master-only (file is master-authoritative) |
  | `docker/release/Dockerfile` (ARGs for ALPINE + PHP) | openemr/openemr each rel branch + master | **per-branch** (each branch's Alpine/PHP can drift independently) |
  | Integration-tests workflow (supported PHP matrix) | openemr/openemr **master** | master-only |
  | Current `ip_map_branch.txt` + `demoLibrary.source` | demo_farm_openemr **master** | current-state read for reconciliation |

  **Section ownership matrix:**

  | Section | Cluster identity | Branch/tag | Flex image |
  |---|---|---|---|
  | **Production** (five + aliases) | fixed | derived from latest holder's `openemr_version_ref` | from latest holder's Dockerfile ARGs |
  | **Up-for-grabs** (four + aliases) | fixed | **preserved** (community claims as overrides) | from **master's** Dockerfile, always (independent of any claim) |
  | **Master demos** | sticky from prior state; new from parked | derived (master) | master's Alpine + the cluster's assigned PHP |
  | **Release demos** | sticky from prior state; new from parked | derived (rel branch name) | from the assigned rel branch's Dockerfile ARGs |
  | **Parked** | dynamic (overflow + retired-from-active) | preserved from when active | preserved |
  | **Miscellaneous** | hand-curated | preserved | preserved (bot never touches) |

  **Cluster count derivation:** counts derive from the current
  per-cluster state (preserves the production=3, up-for-grabs=3,
  rest=2 pattern naturally). Default 2 for newly-assigned clusters
  (which only happens in dynamic categories — master demos +
  release demos).

  **Reconciliation algorithm (high-level):**

  1. Read inputs (upstream openemr/openemr + current demo_farm state)
  2. Validate upstream: **exactly one** non-unreleased row carries
     `latest` — else **FAIL LOUD** (this should never happen; if it
     does, surface immediately rather than fall back)
  3. Parse current `ip_map_branch.txt` → cluster→{section, branch,
     branch_tag, count, ...} map
  4. Compute desired cluster assignments per section:
     - Production / Up-for-grabs: fixed cluster identities; branch
       per the section's derivation rule
     - Master demos: for each supported PHP, find sticky cluster or
       take from parked; PHPs retired upstream → matching cluster
       moves to parked
     - Release demos: for each non-latest non-unreleased rel branch,
       find sticky cluster or take from parked; branches dropped
       from release-targets.yml → matching cluster moves to parked
     - Parked: bench (overflow)
     - Misc: preserved verbatim
  5. For each cluster, derive row + flex image per section rule
  6. Diff vs current; PR if diff

  **Triggers:**

  - **Scheduled (daily cron)** at `0 7 * * *` UTC (1h after
    openemr's docker-release-orchestrator at 06:00). The
    load-bearing trigger — catches everything (manual Dockerfile
    edits, CI matrix changes, anything not announced via dispatch).
    Self-healing.
  - **`repository_dispatch`** on `openemr-tag`,
    `openemr-rel-cut`, `openemr-rel-update`: eager updates so the
    demo doesn't lag a full day after a release. Optional
    optimization — daily cron alone is functionally sufficient.
  - **`workflow_dispatch`**: manual recovery + dry-run testing
    (optional `dry_run` flag).

  Net behavior: any upstream change that affects what demo_farm
  should publish gets caught + PR'd automatically (eagerly via
  dispatch when possible, falling back to daily cron). Maintainer
  reviews + merges the PR (review-gated, not auto-merge — same
  posture as `bump-tag.yml`'s current behavior). Hand-edits to the
  misc section stay safe (bot never touches it).

  **Edge cases to handle explicitly:**

  - **Parked bench empty when a new cluster is needed** (rel-820
    cuts, no parked cluster available) → **fail loud**, asking
    maintainer to add a parked cluster first. Don't invent cluster
    names — that would break the cluster→subdomain stability
    contract.
  - **`unreleased: true` rows** in release-targets.yml → skipped
    when deriving release demos (this is the demo_farm-side
    consumer the openemr/openemr#12656 PR description tracks).
  - **No `latest` holder** in release-targets.yml → fail loud (per
    Validate step). Should never happen.
  - **Dockerfile ARG parsing fails** (regex doesn't match, ARG
    removed) → fail with clear error pointing at the source file +
    line.
  - **CI matrix workflow not found** at the expected path → fail
    with clear error; don't silently default to a wrong PHP list.
  - **Misc section markers missing** in current ip_map_branch.txt
    → assume empty misc; surface a warning so the maintainer can
    fix the section markers.

  **Implementation language:** Bash + `yq` + `curl` + `awk`. Data
  manipulation isn't complex enough to justify Python/PHP/Node
  infrastructure; demo_farm's existing tooling is bash; keeps
  contributor barrier low + repo self-contained.

  **Coordination boundary with workstreams 2 + 3:** the new bot
  consumes `release-targets.yml` state; the conductor's release-
  time partner PR (G11) + branch-cut automation (G5) produce it.
  No tight workflow-level coupling — they're decoupled via the
  data file. When workstream 3's partner PR mutates
  `release-targets.yml`, the demo_farm bot picks up the change on
  its next run (daily cron or eager dispatch).

  **Three-PR scaffolding plan:**

  1. **PR #1 on demo_farm:** scaffold the bot + sticky-
     reconciliation logic in dry-run mode. Outputs a diff artifact
     on PRs/schedule. No live PR yet — just renders + compares.
     Verifies the algorithm end-to-end before going live.
  2. **PR #2 on demo_farm:** open PR on diff. Force-push pattern to
     a `release-auto-update` branch + peter-evans-style PR
     open/update. Concurrency lock to prevent races. First real
     auto-PR.
  3. **PR #3 on demo_farm: atomic flip — wire dispatch consumers
     + retire prior automation.** Single PR coordinates:

     **Add:**
     - Eager `repository_dispatch` consumers on the new bot's
       workflow (openemr-tag / openemr-rel-cut / openemr-rel-update)
     - `unreleased: true` skip on release-targets.yml rows (the
       demo_farm-side consumer of the openemr/openemr#12656 marker)

     **Delete:**
     - `.github/workflows/bump-tag.yml` (109 lines) — functionally
       subsumed by the new bot's Production-section reconciliation,
       which is richer (also handles release demos, master demos,
       parked, flex images)
     - `tools/release/src/IpMapBumper.php` — the PHP class
     - `tools/release/bin/bump-ip-map.php` — the CLI entry
     - `tools/release/tests/IpMapBumperTest.php` — the tests
     - `tools/release/Taskfile.yml`, `phpunit.xml.dist`, `phpcs.xml`,
       `phpstan.neon`, `rector.php`, `composer.json`,
       `composer.lock`, `.gitignore` — the entire PHP toolchain
       scaffolding (only used by IpMapBumper today; the new bot is
       bash so doesn't need it). Future bash tools live in their
       own dir (e.g., `tools/auto-derive/`); future PHP tools (if
       ever) re-scaffold their own project.

     **Net:** ~200 LOC + ~10 config files deleted; ~50 LOC added
     to the new bot's workflow for the dispatch wiring.

     **Why atomic** (single PR vs separate "wire then retire"):
     - Retiring `bump-tag.yml` BEFORE PR #3 → gap window where
       openemr-tag dispatches don't update demo_farm (production
       demo lags real release)
     - Retiring AFTER PR #3 → both workflows fire on every
       openemr-tag dispatch → race condition on `ip_map_branch.txt`
       writes
     - Atomic → one dispatch consumer running at all times, clean
       cutover
- **Procedural rule until automation lands** (the manual checklist
  to follow when changing a production-demo row in
  `ip_map_branch.txt`):
  1. Identify the target tag (e.g., `v8_0_0_3`) and the rel
     branch it lives on (e.g., rel-800).
  2. Read that rel branch's `docker/release/Dockerfile` to find
     `ARG ALPINE_VERSION=` and `ARG PHP_VERSION=`.
  3. Update the cluster's flex-image in
     `demoLibrary.source::startDemoWrapper` to match
     (`openemr/openemr:flex-<alpine>-php-<php>`).
  4. Land the ip_map_branch.txt change + the demoLibrary.source
     change together (or in coordinated PRs back-to-back).
- **Status:** Not migration-blocking. Captured as a candidate
  cross-repo automation for the broader release-mechanism work.
  Could land as a phase in (or after) the release-mechanism
  migration, since it depends on the same dispatch + scheduled-
  workflow patterns.

### G7 — Conductor tooling drift on pre-820 rel branches  *(discovered live 2026-06-23)*

- **What:** The release-prep conductor's PHP tooling +
  workflows under `src/Release/`, `tools/release/`, and
  `.github/workflows/release-prep.yml` (+ `ship-release.yml`,
  related validators) live on EACH branch independently and run
  from whatever's checked into the branch where the workflow
  fires. Drift between master and rel-810/800/704 silently
  produces subtle wrong behavior — including stale logic in
  contexts where master has bug fixes that were never
  backported.
- **Concrete bug exposed 2026-06-23** (openemr/openemr#12611):
  rel-810's `BranchVersionResolver::branchToVersion()` was
  the OLD static form that decomposed the branch name without
  walking tags, returning `8.1.0` for `rel-810` regardless of
  the existing `v8_1_0` tag. Master's version (instance-based,
  walks annotated `v<MAJOR>_<MINOR>_<PATCH>` tags, returns
  next patch) was never backported. Surface: every rel-810
  push since 8.1.0 shipped has dispatched the conductor with
  `VERSION=8.1.0`. No-op until rel-810 HEAD's content diverged
  from 8.1.0 content (which happened with P3 of the 8.1.1
  prep), then mutators rewrote the release-prep branch with
  wrong content. Recovery: surgical backport in #12611 (just
  `BranchVersionResolver` + `branch-to-version.php`).
- **Why this matters (for rel-810 only):** The surgical fix
  unblocks 8.1.1 but doesn't address the structural drift.
  Other parts of the conductor (mutators, dispatcher logic,
  validator workflows) may have similar latent bugs on rel-810
  that haven't fired yet. Each future per-release cycle on
  rel-810 (8.1.2, 8.1.3, ...) carries discovery risk.
- **Why this is only on pre-820 rel branches:** rel-820 and
  later are cut from current master, so they inherit current
  conductor at cut time. The drift problem is bounded to
  rel-810, rel-800, rel-704 — branches that pre-date the
  conductor's rapid iteration on master.
- **Scope decision (2026-06-23, per maintainer):** rel-800 and
  rel-704 will **not** get backports. Both branches will rotate
  out (no future releases planned on either), so latent drift
  bugs there will never fire. G7 work scopes to rel-810 only.
  If a need ever surfaces to ship from rel-800 or rel-704
  before they're retired, that triggers a per-branch surgical
  backport at that time, not preemptive sync now.
- **The fix (rel-810 only):** Sync the following from master to
  rel-810 as a single coherent PR:
  - `tools/release/src/` — all conductor PHP classes
  - `tools/release/bin/` — all CLI wrappers
  - `.github/workflows/release-prep.yml` — conductor workflow
  - `.github/workflows/ship-release.yml` — finalize job (if
    present on rel branches)
  - Any conductor-related validator workflows
    (`docker-validate-release-targets.yml`, etc. — audit
    which ones rel branches need)
  - `composer.json` / `composer.lock` — if conductor adds new
    deps (compare against current rel-810 state)

  Audit each piece for rel-applicability: some master-scope
  mutators (e.g., `SqlUpgradeSkeletonMutator`, `--scope=master`
  paths) are master-only by design — they can ship to rel
  branches harmlessly (just never fire) or be excluded from
  the sync. Cleaner to ship as-is for parity, accept harmless
  unused code.
- **Risk:** Bigger PR surface = bigger chance of "finds yet
  another rel-vs-master drift bug during review". Recommend
  doing this when there's no active release-prep in flight on
  rel-810 (to avoid coordinating in-flight mutations with the
  sync PR).
- **Status:** Captured for follow-up. Surgical fix #12611
  unblocks 8.1.1. Full rel-810 sync recommended after 8.1.1
  ships and before the 8.1.2 cycle.

### G8 — No automated regression test for conductor resolvers  *(complements G7)*

- **What:** `BranchVersionResolver`, `derivePrevious`,
  `DispatchDataBuilder`, and the other PHP classes under
  `tools/release/src/` have no isolated PHPUnit coverage that
  exercises them against realistic input fixtures. A bug like
  the static-`branchToVersion()` off-by-one (#12611) ships
  silently because nothing catches "returns wrong version
  for `rel-810`" before the conductor actually fires in
  production.
- **Why this matters:** Conductor logic is high-blast-radius
  per-execution (every misfire dispatches to 3 consumer repos
  + force-pushes a release-prep PR), so the cost of a bug
  reaching production is much higher than per-feature code.
  The class boundaries are clean (DI via constructor, no DB,
  no I/O beyond `Symfony\Process` against a git dir), so
  testing them is low-friction — set up a temp git repo with
  realistic tags, instantiate the resolver, assert.
- **Concrete test surface (BranchVersionResolver as canonical
  example):**
  - `branchToVersion('rel-810')` against tags `[v8_0_0_3,
    v8_1_0]` → `8.1.1`
  - `branchToVersion('rel-810')` against tags `[]` (no
    releases yet) → `8.1.0`
  - `branchToVersion('rel-800')` against tags `[v8_0_0,
    v8_0_0_1, v8_0_0_2, v8_0_0_3]` → `8.0.4`
  - `branchToVersion('rel-810')` ignoring lightweight tags
    `v8_1_0-test.abc` (must be filtered by annotated-only
    check)
  - `branchToVersion('master')` → throws InvalidArgumentException
  - `previousRelease('8.1.1')` against tags `[v8_0_0_3,
    v8_1_0]` → `8.1.0`
- **Bonus benefit when combined with G7:** running the same
  test suite on rel-810's CI catches future drift
  *automatically* — if rel-810's `BranchVersionResolver`
  diverges from master's expected behavior, the test fails
  on the next push, surfacing the drift before it causes
  production damage. (Per the G7 scope decision, rel-800 +
  rel-704 are out of scope — they won't get the tests
  either since they won't get future releases.)
- **Status:** Follow-up. Adding the tests on master is the
  natural first step; the G7 rel-810 sync would carry them
  along as part of the broader tooling backport.

### G9 — `release-docs/<version>` PRs on website-openemr don't supersede across version changes

- **What:** The release-docs workflow on website-openemr
  uses `release-docs/<version>` as the head branch name (e.g.,
  `release-docs/8.1.0`, `release-docs/8.1.1`). When the
  conductor's resolved `VERSION` changes (e.g., from a buggy
  `8.1.0` to a corrected `8.1.1`), the workflow opens a
  *new* PR at the new branch and leaves the prior PR + branch
  orphaned. peter-evans only updates in place when the head
  branch name matches.
- **Concrete instance (2026-06-23):** During 8.1.1 release
  prep recovery, the BranchVersionResolver bug (G7, #12611)
  caused two cycles of dispatch:
  - First cycle: VERSION=8.1.0 → updated existing
    `release-docs/8.1.0` PR #142 (originally created for the
    real 8.1.0 prep weeks earlier)
  - Second cycle (after #12611): VERSION=8.1.1 → opened new
    `release-docs/8.1.1` PR #160
  - PR #142 left orphaned; needed manual close as cleanup
- **Why this matters:** Pattern repeats on every rel branch's
  per-release cycle. PR #142's stale 8.1.0 entry will recur
  as N stale PRs over time (one per version bump cycle).
  Each requires a maintainer to know "this is superseded,
  close it" — repeatable toil, easy to forget.
- **Two possible fixes:**
  - **A. Auto-close on supersede:** when the workflow opens
    a new `release-docs/<version>` PR, check for prior open
    PRs with the `release-docs/<other-version>` head pattern
    targeting the same `rel-*` branch (or the same conductor
    dispatch source), close them with a "superseded by
    #<new-PR>" comment.
  - **B. Per-rel-branch head naming:** match the
    `release-prep/rel-810` pattern used by openemr/openemr's
    conductor. Use `release-docs/rel-810` as the head branch,
    so the same PR updates in place across version cycles
    (peter-evans flow). PR title encodes the version
    (rewrites on each run via the `title:` input). One PR
    per rel branch, never orphaned.
- **Recommendation:** Option B is structurally cleaner — it
  removes the orphaning by design rather than papering over
  it. It also makes the website-openemr side match the
  openemr/openemr release-prep PR pattern, reducing per-system
  cognitive load for maintainers.
- **Status:** Follow-up. Discovered 2026-06-23 during 8.1.1
  prep recovery; manual close of PR #142 is the one-time
  cleanup until the underlying pattern changes.

### G10 — Reusable workflows as a replacement for the byte-identical canary system on the docker pipeline  *(future consideration)*

- **What:** The docker pipeline today keeps workflow files,
  config, and the composite action byte-identical across
  master + rel-810/800/704 via the canary trio
  (`validate-byte-identical.yml` + `sync-byte-identical.yml`
  + `validate-byte-identical.sh`) and the FILES_ALL config
  (8 entries). Auto-sync opens `sync-byte-identical/rel-*`
  PRs to mirror master's changes; canary validates each PR
  before merge.
- **Alternative pattern:** GitHub reusable workflows would
  let master own the *real* implementation files
  (`docker-build-release-impl.yml`,
  `docker-test-core-impl.yml`,
  `docker-test-release-impl.yml`); rel branches would carry
  only thin caller stubs (`~10 LOC`) invoking master's impl
  via `uses: openemr/openemr/.github/workflows/...-impl.yml@master`.
  Composite actions + config files (`test-actions-core`,
  `.github/docker/compose.yml`) are already cross-branch
  referenceable today; impl-on-master would consume master's
  copies directly without canary syncing.
- **What gets deleted under reusable-workflow model:**
  - `sync-byte-identical.yml` workflow
  - `validate-byte-identical.yml` workflow
  - `validate-byte-identical.sh` extracted script
  - `validate-byte-identical-config.yml` (FILES_ALL config)
  - The 5-classification-case state machine
    (identical/add/update/delete-as-rename/demote-skip)
  - The entire `sync-byte-identical/rel-*` PR stream
  - ~hundreds of LOC + canary trio infrastructure
- **What stays:**
  - Per-branch files that legitimately differ
    (`docker/release/Dockerfile` with branch-specific ARGs)
  - A *micro*-canary covering the thin caller stubs
    themselves (~3-5 files) — they still need byte-identity
    across branches, but the surface shrinks dramatically
- **Tradeoffs:**
  - ✅ Drift eliminated by construction (same benefit as
    proposed for release-mechanism — see migration doc)
  - ✅ Master bug fixes apply to rel branches immediately,
    no per-branch sync PR review cycle
  - ✅ Per-rel-branch PR queue shrinks significantly
  - ❌ **Replacing-what-isn't-broken.** Canary system is
    production-validated end-to-end with all 5 cases handled
    correctly under real load.
  - ❌ Slightly worse for ad-hoc inspection (`cat` on a
    rel-branch workflow shows a stub, not the real logic)
  - ❌ Lose the per-branch "override" flexibility if it's
    ever needed (today's `demote-skip` classification case
    would require editing the caller's `uses:` ref)
  - ❌ Decomposition cost: caller/impl split per workflow,
    caller stubs to each rel branch, canary careful retirement
- **Why canary was chosen originally:** the workflows
  already existed and ran on rel branches via copy-paste
  before canary was built. Adding canary was a low-disruption
  "freeze the current state" answer; restructuring to
  caller+impl would have been a parallel rewrite. Once
  canary worked, no pressure to revisit.
- **Status:** Not urgent. Defensible engineering decision
  either way given canary works. Natural time to revisit:
  when a "this would have been easier with single-source-of-
  truth" moment hits the docker side (something like #12611
  but for docker). Or as part of a broader "all
  cross-branch-shared workflows use reusable-workflow pattern"
  consistency pass after the release-mechanism migration
  adopts the pattern (see migration doc's pre-Phase-1 decision
  on reusable workflows).

### G11 — Post-release release-targets.yml updates are manual  *(workstream 3 design)*

**STATUS: PHASE A SHIPPED 2026-07-01** as openemr/openemr#12662
(merge commit `abb1e2d940`). Delivered as scoped: new
`PostReleaseTargetsMutator`, `MutatorContext::$relBranch` extension,
`ReleasePrepCommand --rel-branch` option + master mutator list,
conductor workflow extension opening the paired
`release-finalize/<rel-branch>` PR against master. Reality matched
plan. Phase B (cherry-pick to rel-810 for 8.1.1 ship) is the next
milestone; auto-merge of the master partner PR on `openemr-tag`
remains deferred. For the 8.1.1 ship, maintainer will mark Ready +
merge manually after the tag fires.

- **What:** After a rel-branch ships (e.g., 8.1.1 ships from
  rel-810), `.github/release-targets.yml` on master needs three
  coordinated edits that no automation produces today:
  - **Pin the rel branch's `openemr_version_ref` to the new tag**
    (e.g., `rel-810 → v8_1_1`). Stops daily builds from tracking
    the rel branch tip; locks them to the immutable tag content.
  - **Slot shuffle across rows** (per the slot-promotion model
    documented in this doc's "Docker Hub tag model" section):
    - The newly-shipped rel branch promotes its `next` tag →
      `latest` (e.g., `rel-810: 8.1.1,next → 8.1.1,latest`).
    - The previous `latest` holder drops it (e.g.,
      `rel-800: 8.0.0,8.0.0.3,latest → 8.0.0,8.0.0.3`).
    - `next` moves to the next upcoming-stable owner — to a
      newly-cut rel branch if one exists, else back to master
      (e.g., `master: 8.2.0,dev → 8.2.0,dev,next`).
  - **Drop the unreleased placeholder row** for the same rel
    branch (per the multi-row mechanism added in
    openemr/openemr#12656). If the multi-row was set up for
    the in-dev publish-prior-stable case, the prior-version row
    becomes redundant once the new version ships and gets removed.

  Today: P6 in the canonical 8.1.1 release sequence is purely
  manual — operator opens the PR by hand after the tag is created.

- **Design (2026-06-27):** Extend the conductor (or add a sibling
  workflow firing on the same triggers) to open a SECOND PR —
  `release-finalize/<rel-branch>` — on **master**, paired with
  the existing `release-prep/<rel-branch>` PR on the rel branch.
  The second PR carries the post-release release-targets.yml
  mutations + dropping the unreleased placeholder.

  **Lifecycle:**
  1. Conductor fires on push to rel-810 → opens BOTH
     `release-prep/rel-810` (on rel-810) AND
     `release-finalize/rel-810` (on master) as drafts.
  2. Maintainer marks both Ready.
  3. ship-release.yml merges conductor PR → tag fires →
     openemr-tag dispatch consumer either auto-merges the master
     partner PR, or marks it Ready for manual merge.
  4. Optional: master partner PR has a guard refusing to merge if
     the tag doesn't exist yet (mirrors the docs-first refusal
     pattern in ship-release.yml).

- **What gets implemented:**
  - New mutator: `PostReleaseTargetsMutator` (computes the slot
    shuffle, ref pin, and unreleased-row drop in one pass).
  - Conductor extension: opens the second PR via peter-evans
    against master.
  - openemr-tag consumer extension: after the tag fires, marks
    `release-finalize/<rel-branch>` Ready and/or auto-merges it.

- **Status:** Workstream 3 (per-release-on-rel-branch
  optimization). Tightly coupled with the broader release-cycle-bot
  for P1-P4 automation.

- **Phase A refinement (2026-06-28; shipped 2026-07-01 as PR #12662):**
  Sliced the work into master-side and rel-branch-side cherry-pick.
  Phase A scope (what actually shipped):
  - **New mutator** `PostReleaseTargetsMutator` at
    `src/Common/Command/ReleasePrep/Mutator/`. Implements existing
    `MutatorInterface` (`name()`, `apply(MutatorContext): MutatorResult`).
    Operates on `.github/release-targets.yml`. Performs the three
    coordinated edits (pin / slot shuffle / drop placeholder)
    idempotently — re-running on already-mutated input yields no
    diff.
  - **`MutatorContext` extension**: add `?string $relBranch` property.
    Existing mutators ignore it; PostReleaseTargetsMutator needs it
    to know which branch's row to mutate. The extension is also
    forward-useful for workstream 2's branch-cut mutators.
  - **ReleasePrepCommand extension**: add `--rel-branch` CLI option
    plumbed through to context. Register `PostReleaseTargetsMutator`
    as the sole entry in the `masterMutators` list. Document
    `--scope=master` as **release-time only**; the existing
    `VersionPhpMasterMutator` stays as a class but is **not in any
    wired list** — workstream 2 (G4) will introduce a separate
    branch-cut workflow + invocation path for it.
  - **Conductor extension** (`.github/workflows/release-prep.yml`):
    after the existing "Create or update release-prep PR" step,
    add a second cycle (checkout master / run mutators with
    `--scope=master` / peter-evans to open
    `release-finalize/<rel-branch>` PR against master as a draft).
  - **Tests**: `PostReleaseTargetsMutatorTest` with fixture
    release-targets.yml inputs + expected outputs. Covers the
    three transformations + the single-rel-branch idempotency + the
    multi-row unreleased-placeholder drop case.
  - **YAML comment preservation**: line-based surgical edits (regex
    or string replace), not parse-and-dump via Symfony YAML — the
    release-targets.yml file has substantial human-authored comments
    that explain each field, and losing them would degrade
    maintainer experience. The mutations are small + structurally
    predictable (3 specific transformations), so surgical edits are
    tractable.

- **Phase B scope (post-Phase-A merge):** Cherry-pick the conductor
  extension to rel-810 so the 8.1.1 release benefits. Per
  `feedback_rel_branch_workflow.md`: worktree from
  `--base https://github.com/openemr/openemr.git#rel-810`, commit
  with `--no-verify`. Needs both the workflow YAML cherry-pick AND
  the PHP changes (PostReleaseTargetsMutator + MutatorContext +
  command extension) because rel-810's conductor invokes its own
  `src/Common/Command/...` tree, not master's.

- **The "partner PR" pattern generalizes**. The dual-PR
  (rel-branch + master, both managed by one workflow, both drafts
  → mark Ready → merge) pattern appears in TWO contexts:
  - **Release-time** (workstream 3 / G11 / this work): conductor
    fires on rel-* push → opens release-prep PR on rel-* + opens
    release-finalize PR on master.
  - **Branch-cut** (workstream 2 / G4 + G5): cut detection fires →
    opens post-cut PRs on the new rel branch (e.g., copy translation
    file from prior rel, turn off dummy-translation global, other
    post-cut hygiene) + opens post-cut PR on master (bump version.php
    to next-dev via `VersionPhpMasterMutator`).

  The shared infrastructure (extended MutatorContext, dual-checkout
  workflow structure, peter-evans pattern) built in Phase A is
  designed to be reusable by workstream 2 without rework.

### G12 — Patch-cycle bootstrap on rel-* requires manual SQL skeleton + docker scaffolding + master file-rename  *(workstream 6 design)*

**STATUS: SHIPPED 2026-07-01** as workstream 6 / openemr/openemr#12697
(merge commit `c1af6370a0`), landed directly after workstream 2's
#12696 the same day. Delivered as scoped in a single PR: new
`openemr:patch-prep` command + `.github/workflows/patch-prep-automation.yml`
(triggering on push to `rel-*` with `paths: version.php` + resolver
gate) + 2 new mutators (`MasterSqlPatchBridgeMutator`,
`PatchPrepReleaseTargetsMutator`) + strictly-additive
`MutatorContext::$fromVersion` extension + tests. Reality matched
plan; branch-cut callers unaffected by the context extension as
designed (back-compat by construction). First production exercise
fires at the patch after 8.1.1 (rel-810's `8.1.1 → 8.1.2-dev`
transition) — contingent on the workflow YAML + supporting PHP being
cherry-picked to rel-810 alongside the workstream 3 Phase B set, else
fall back to a one-time manual patch-prep PR pair for that transition.

- **What:** When a maintainer bumps `$v_patch` in `version.php` on a
  `rel-*` branch to start a new patch dev cycle (e.g., rel-810 going
  from `8.1.0` to `8.1.1-dev` after 8.1.0 was the last shipped
  version on the branch — or 8.1.1 to 8.1.2-dev after 8.1.1 ships),
  multiple coordinated mechanical edits across rel-* + master are
  needed today that are all manual:
  - **Both sides — docker upgrade scaffolding** (per the `docker
    upgrade actions mandatory per release` rule, PRs
    #12608/#12609): bump 3 docker-version files (`docker-version`,
    `docker/release/upgrade/docker-version`,
    `sites/default/docker-version`); create
    `docker/release/upgrade/fsupgrade-(N+1).sh` stub; extend
    `docker/release/Dockerfile` to add the new fsupgrade script
    name to BOTH the `COPY upgrade/...` block AND the `RUN chmod
    500 ...` block.
  - **Rel-* side** — create a blank SQL upgrade skeleton at
    `sql/X_Y_(P-1)-to-X_Y_P_upgrade.sql` (long Comment Meta
    Language Constructs header; empty body — body fills in
    per-patch as the dev cycle progresses).
  - **Master side** — create the same blank SQL skeleton (mirror of
    the rel-side file — cross-branch propagation) AND rename
    master's existing long-lived bridge file from
    `sql/X_Y_(P-1)-to-X_(Y+1)_0_upgrade.sql` to
    `sql/X_Y_P-to-X_(Y+1)_0_upgrade.sql`, preserving the bridge
    file contents byte-for-byte (the bridge accumulates in-flight
    dev-cycle SQL across patches; only the "from" anchor in the
    filename advances).
  - **Master side** — `.github/release-targets.yml` updates: add
    the new dev row for the target rel branch (`docker_tags:
    <target-version>,next`), drop any prior placeholder rows for
    that same rel branch (rows flagged `unreleased: true` per the
    multi-row mechanism added in openemr/openemr#12656).

  Today: P8 in the canonical 8.1.1 release sequence captures this
  manual work for the cut-time-only scope; the broader patch-cycle
  bootstrap case (any rel branch, any patch, between ships) was
  unstructured until this gap was identified.

- **Design (2026-06-30):** New workflow
  `.github/workflows/patch-prep-automation.yml` triggered on push
  to `branches: rel-*` matching `paths: version.php`, with a
  resolver step that gates on:
  1. Skip cleanly on branch-creation events (`before` SHA all zeros)
     — workstream 2 (branch-cut) owns that lifecycle event.
  2. Fetch before-state + after-state `version.php` via GitHub API
     (`github.event.before` vs `github.event.after`); parse
     `$v_major`, `$v_minor`, `$v_patch`, `$v_tag` via regex.
  3. **Gate**: `$v_patch` must have strictly increased.
  4. **Gate**: after-state `$v_tag` must be `-dev` (excludes
     release-prep's mid-flight `-dev` strip events that also bump
     `$v_patch` in some flows).
  5. **Gate**: same major + same minor before/after.

  Two coordinated **ready-for-review** PRs (not drafts, mirroring
  workstream 2's branch-cut PRs — patch-prep PRs should land fast):
  - **Rel-side**: `patch-prep/<rel-branch>` → base `<rel-branch>`,
    carrying docker upgrade scaffolding + new SQL upgrade skeleton.
  - **Master-side**: `patch-prep/<rel-branch>-master` → base
    `master`, carrying same docker upgrade scaffolding + same new
    SQL skeleton (mirror) + master bridge-file rename + release-
    targets row insert + placeholder drop.

  **Mutator reuse + extension:**
  - `DockerUpgradeScaffoldMutator` (NEW in workstream 2 / PR
    #12696) — reused as-is, both sides.
  - `SqlUpgradeSkeletonMutator` (existing) — reused with extension:
    new `MutatorContext::$fromVersion` field (optional,
    `MAJOR.MINOR.PATCH`) introduced because rel-side `version.php`
    has already been bumped at workflow trigger time; the post-bump
    `$v_patch` is the *target*, not the *from*. Mutator falls
    through to existing version.php-derived behavior when
    `fromVersion` is null — branch-cut callers unaffected.
    Back-compat by construction.
  - 2 new mutators (master-side only):
    - `MasterSqlPatchBridgeMutator` — performs the bridge file
      rename; preserves body byte-for-byte. Reports both old and
      new paths in `MutatorResult::changedFiles` (rename = delete +
      create).
    - `PatchPrepReleaseTargetsMutator` — inserts new dev row +
      drops placeholders scoped to the target rel branch only
      (unlike `BranchCutReleaseTargetsMutator` which drops
      uniformly). Line-based surgical edits to preserve comments;
      Symfony YAML used as a structural sanity check on the result.

  **Command + workflow shape:** new sibling command
  `openemr:patch-prep` (NOT a `--scope=<x>` extension to
  `openemr:release-prep` — matches the precedent set by workstream
  2's `openemr:branch-cut`). Takes `--target-version`,
  `--rel-branch`, `--prev-version`, internal `--side=rel|master`
  for mutator-list selection. `workflow_dispatch` escape hatch
  takes the same inputs explicitly for manual recovery.

- **Lifecycle relationship to workstreams 2 + 3 (lifecycle
  coverage):**

  | Lifecycle event | Workstream | Trigger |
  |---|---|---|
  | Minor branch cut (`rel-NNN0` created) | 2 | `on: create:` for `rel-NNN0` refs |
  | Patch dev cycle start (`$v_patch` bump on rel branch) | **6** | `on: push:` `rel-*` w/ `paths: version.php` + resolver gate |
  | Per-release ship (rel branch merges to tag) | 3 | conductor `on: push:` `rel-*` (existing) |

  Together, workstreams 2 + 3 + 6 cover the full release-lifecycle
  automation surface. This closes G12 in tandem with G4 (workstream
  2) and G11 (workstream 3 Phase A).

- **Status:** Workstream 6 SHIPPED 2026-07-01 as PR #12697 (merge
  commit `c1af6370a0`). Implementation delivered as one PR (new
  command + 2 new mutators + `MutatorContext::$fromVersion` extension
  + new workflow + tests). The MutatorContext extension is strictly
  additive — existing branch-cut and release-prep callers unaffected.

- **Conditional sequencing — resolved 2026-07-01 (revised):** 8.1.x is
  being skipped entirely — no 8.1.1 ship. rel-820 is the first rel
  branch that ships anything after the automation landed. First
  fully-automated patch-prep firing is rel-820's
  `8.2.0 → 8.2.1-dev` transition. No cherry-pick to rel-810 needed
  since rel-810 will never ship anything.

### G13 — `derive-prev-release` returned skipped-version tags  *(discovered + closed 2026-07-05)*

**STATUS: SHIPPED 2026-07-05** as openemr/openemr#12769 (master) +
#12770 (rel-820 backport, identical patch-id), with follow-up #12771
(master) + #12772 (rel-820 backport) fixing a secondary bug the initial
version missed. All four merges landed same session; full chain verified
end-to-end via a manual `workflow_dispatch` on `release-prep.yml`
producing `prev_release: 8.0.0` in the dispatch payload for target 8.2.0.

- **What:** `tools/release/bin/derive-prev-release.php` (called from
  `release-prep.yml`'s Dispatch step to populate the
  `openemr-rel-cut` / `openemr-rel-update` payload's `prev_release`
  field) walked annotated `v<M>_<m>_<p>` tags and returned the highest
  below the target. That worked as long as every tag corresponded to a
  shipped release. But **8.1.0 was cut (`v8_1_0` exists as an annotated
  tag with GitHub Release assets) and then skipped** — never publicly
  released, permanently absent from
  `website-openemr/data/releases.json`. So 8.2.0's dispatch payload got
  `prev_release=8.1.0`, and its acknowledgements + release-notes
  generated against a ~30-day window (v8_1_0..HEAD) instead of the
  correct ~5-month window (v8_0_0..HEAD). Users going 8.0.0 → 8.2.0
  would have seen a truncated changelog.

- **Fix:** `BranchVersionResolver` consults
  `website-openemr/data/releases.json` at derive time and treats any
  tag whose version isn't in the FINAL entries as skipped. Fetched
  fresh over HTTPS (`raw.githubusercontent.com`) with explicit
  timeout/max_duration. Any fetch/parse failure falls back to the
  pre-manifest annotated-tag walk (safety net so a raw-content hiccup
  can't block dispatch). Landed with constructor-injected
  `HttpClientInterface` (nullable, defaults to null → skip
  manifest lookup) so existing single-arg constructor calls in tests
  stay valid; production consumer creates `HttpClient::create()` and
  passes it in.

- **Secondary bug (openemr/openemr#12771/#12772):** the initial fix
  still walked annotated tags only, then filtered via the manifest.
  Historic SourceForge-era tags like `v8_0_0` are LIGHTWEIGHT, not
  annotated — dropped silently by the walk. So for target 8.2.0 the
  walk saw only `v8_1_0` (annotated + skipped), filtered it out via
  the manifest, fell through to `null`, and `previousRelease()`
  synthesised prev-minor as fallback → returned "8.1.0" anyway
  (target's minor is 2, prev-minor is 1, so `8.1.0`). Follow-up
  changed the flow: when manifest is available, iterate shipped-version
  list directly instead of git-tag walk. Tag walk stays as fallback
  when manifest fetch fails.

- **Not used as signal:** `unreleased: true` in
  `release-targets.yml`. That flag is transient — clears once the
  next release supersedes the placeholder row — so any code keyed
  on it would silently break in a later release cycle. The website
  manifest is durable: skipped versions are permanently absent from
  it.

- **Coordination:** `tools/release/` is not in the byte-identical set,
  so no auto-sync. Fix landed on master + rel-820 in parallel (patch-id
  parity verified: `55fa587bf66e...` on both initial fixes,
  `4aa74098c...` on both follow-ups, `c232842e11...` on both squash
  merges). rel-800, rel-704, rel-810 don't need the fix — rel-810
  isn't shipping; rel-800/rel-704 next patches don't cross the
  skipped-8.1.0 boundary.

- **Verification (2026-07-05):** After all four PRs landed and a
  `workflow_dispatch` on `release-prep.yml` fired against rel-820, PR
  openemr/website-openemr#164 regenerated with `prev_release: 8.0.0`
  in the dispatch payload — 930 PRs in the release-notes window
  (v8_0_0..HEAD, matching 1508 total commits in that range less #173's
  bot-filter drops).

### G14 — `release-docs/<version>` branches accumulated divergent history  *(discovered + closed 2026-07-05)*

**STATUS: SHIPPED 2026-07-05** as three cascading PRs on
openemr/website-openemr — #174 (initial reset-manifest-from-master, later
subsumed), #175 (rebase-per-dispatch structural fix), and #176 (restore
branch-ref fetch so `--force-with-lease` has a baseline).

- **What:** Byproduct of validating G13's fix. Even after
  `derive-prev-release` correctly returned 8.0.0, PR
  openemr/website-openemr#164 stayed CONFLICTING because the docs
  branch had been committing on top of the previous branch state on
  each dispatch. Two forms of drift compounded:
  - **File-content drift** — master's edits to unrelated
    `data/releases.json` entries (e.g. #167's 8.0.0 checksums URL
    fix) never propagated to the docs branch because
    `update-manifest.php` reads the branch's stale snapshot and only
    mutates the dispatched version's entry.
  - **History drift** — each dispatch's commit and each master merge
    to `data/releases.json` edited overlapping lines, and 3-way merge
    with master flagged spurious content-agrees-history-diverges
    conflicts even when the file content on both sides was
    identical.

- **Fix:** Rebuilt `workflow:prepare-publish` to unconditionally
  `checkout -B $BRANCH origin/HEAD` — every dispatch produces a
  single-commit branch rooted at master's current tip. Merge base
  with master is always master's tip → 3-way merge is trivial. Loses
  per-dispatch commit history on the docs branches, which nobody
  consumes (each commit was a full-file rewrite anyway).

- **Intermediate PRs the fix went through:** #174 added a
  `git checkout origin/HEAD -- data/releases.json` reset step that
  fixed content drift but not history drift (branches still
  committed on top of previous state). #175 restructured to
  `checkout -B $BRANCH origin/HEAD` — but dropping the remote branch
  fetch broke `commit-push`'s `--force-with-lease` baseline lookup,
  fell to plain push, and hit `[rejected] (fetch first)` because the
  remote branch existed. #176 restored the fetch alongside the
  master-based checkout and tightened error handling (only "couldn't
  find remote ref" is silently ignored; real fetch failures now
  surface in CI logs).

- **Verification (2026-07-05):** After all three PRs landed and a
  fresh dispatch fired, PR #164 became `mergeable: MERGEABLE` with
  `ahead: 1, behind: 0` — clean single-commit branch on top of
  current master. `data/releases.json` diff is a single-hunk add
  (the 8.2.0 DRAFT entry). No 8.0.0 formatting or content drift.

### G15 — Drift-management strategy for migrated release-machinery on rel-* branches  *(workstream 7 entry decision)*

- **What:** The workstream 7 migration brings `build-release.yml` +
  `build-release-on-tag.yml` + `build-patch.yml` + `ship-release.yml` +
  `release-announcements.yml` + their supporting PHP classes (`PackageAssembler`,
  `ChangelogGenerator`, `PreflightChecker`, `CompatibilityDeriver`,
  `ShipReleaseOrchestrator`, `AnnouncementRenderer`, `PullRequest*`, etc.)
  into `openemr/openemr`. Some of these workflows need to exist on each
  rel-* branch to fire from that branch's context (build-release-on-tag
  in particular fires on `push: tags: ['v*']` which is inherently
  branch-scoped). This creates a drift surface: rel-820's copy of the
  release-machinery can silently diverge from master's, and a bug fixed
  on master doesn't reach the branch until backport.

- **Empirical precedent:** G7 (`BranchVersionResolver` drift on rel-810,
  2026-06-23) is the reference incident — rel-810 carried a stale copy
  of a class that had been fixed on master weeks earlier; the drift
  wasn't discovered until 8.1.1 prep tried to use it. Cost: hours of
  troubleshooting during a live release-prep exercise + a surgical
  backport PR (openemr/openemr#12611). Currently only rel-810 is in
  scope (rel-800 and rel-704 rotate out without future releases per
  maintainer 2026-06-23), but every future rel-* branch inherits the
  same drift risk unless a mechanism prevents it.

- **Comparison with the docker pipeline:** The docker-pipeline migration
  (completed 2026-06-20) explicitly instituted a byte-identical
  enforcement mechanism because docker workflows fire from each rel-*
  branch's tree and drift there was previously a live headache. That
  mechanism has three moving parts, all working in production
  (validated during Track A #12778 landing on 2026-07-06):
  - **Source of truth:** `.github/docker-byte-identical.yml` FILES_ALL
    list
  - **Canary:** `docker-validate-byte-identical.yml` fails PRs when
    any FILES_ALL file drifts across branches
  - **Auto-sync:** `sync-byte-identical.yml` opens sync PRs on master
    push to backfill each rel-* branch

- **Three options for release-machinery drift management:**
  1. **Per-branch copies, ad-hoc backport** (status quo pre-G7).
     Every workflow + supporting class lands on every rel branch;
     bugs get backported when discovered. Cost: G7-class drift bugs
     surface at release time.
  2. **Per-branch copies + byte-identical canary + auto-sync**
     (docker-pipeline pattern extended). Add the migrated files to
     FILES_ALL; the canary catches drift; auto-sync backfills.
     Adds ~5-10 files per phase to the byte-identical surface. Same
     mechanism that already works in production for docker.
  3. **Reusable workflows** (caller/impl split). Master owns the
     real workflow implementations; rel branches carry thin caller
     stubs invoking master's impl via
     `uses: openemr/openemr/.github/workflows/*-impl.yml@master`.
     Supporting PHP classes live on master only; the impl workflow
     checks out master's `tools/release/` tree explicitly regardless
     of which branch fired the caller. Drift impossible by
     construction. Structural refactor per workflow; higher upfront
     cost.

- **Migration doc coverage:** The tradeoff table + revisit criteria
  live in the migration doc's `## Pre-Phase-1 architectural decision:
  per-branch copies vs reusable workflows` section (currently deferred
  to workstream 7 entry). This gap formalizes the same question as a
  tracked item so it doesn't get lost in the migration doc's phasing
  discussion.

- **Status:** **Locked 2026-07-07 — Option 2 (byte-identical canary +
  auto-sync).** Reasons in weight order:
  1. Production-validated this session. Track A openemr/openemr#12778
     landed on master → sync-byte-identical fired → produced
     #12787/#12788/#12789 auto-sync PRs against rel-820/800/704 within
     13 minutes. Zero manual per-branch work. Real evidence, not theory.
  2. Lower migration risk. Option 3 (reusable workflows) is a
     structural refactor per workflow — adds complexity to the
     migration itself. Option 2 is "add files to FILES_ALL" — trivial.
  3. Same team mental model. Byte-identical is already understood
     from the docker pipeline; extending it is a small delta. Option 3
     introduces caller/impl decomposition + `uses: @master` cross-branch
     action refs — a new pattern nobody in this codebase has used.
  4. Doesn't preclude option 3 later. Migration doc explicitly notes
     option 3 is an optimization on top of option 2 that can land later
     without redoing migrated work. Zero lock-in.
  5. Sync PRs are review-gated by default (auto-merge intentionally
     off — from sync-byte-identical.yml's header). Drift-prevention
     without giving up substantive review.
  6. Preserves customization escape hatch. If a specific rel-* ever
     needs legitimately divergent build behavior, remove the file from
     FILES_ALL (or move it to the opt-out comment block) — the sync
     PR reviewer sees the drift and decides case-by-case. Option 3
     is much more restrictive (would require forking the impl or
     pinning caller to a SHA, both brittle).

- **File-set sizing:** Only Phase 2 (build-release + build-release-on-tag
  + build-patch) fires from rel-* branches, so only Phase 2 adds to
  FILES_ALL. Phases 3-6 are master-only (ship-release orchestrator,
  announcements, contract flip, deletions) — no drift surface.

  | Phase | Workflows | PHP src | Bin scripts | Adds to FILES_ALL |
  |---|---|---|---|---|
  | Phase 2 | 3 | ~10-12 | ~6-8 | **~20-25 files** |
  | Phase 3 | 0 | 0 | 0 | 0 (master-only) |
  | Phase 4 | 0 | 0 | 0 | 0 (master-only) |
  | Phase 5 | 0 | 0 | 0 | 0 (master-only) |
  | Phase 6 | 0 | 0 | 0 | 0 (deletions) |

  Current docker FILES_ALL is 8 files. Post-migration total: ~30 files.

- **Recommended shape: enumerate workflows individually, use a glob
  for the PHP tree.** Since `tools/release/` is one composer project,
  splitting per-class between "on rel-*" and "master-only" is
  awkward — cleaner to include the whole tree via a glob pattern:
  ```yaml
  files:
    - .github/workflows/build-release.yml
    - .github/workflows/build-release-on-tag.yml
    - .github/workflows/build-patch.yml
    - tools/release/**            # whole tree byte-identical
  ```
  Cost: some ship-release / announcement PHP classes end up as dead
  code on rel-* branches (their workflows never fire from rel-*, so
  the code sits unused). ~10-15 KB dead weight per rel-* branch —
  trivial. Benefit: no per-file curation as classes get added or
  removed during Phase 2/3/4 development. Sync-byte-identical.yml's
  script needs to be verified against glob expansion at phase kickoff
  (should work per its FILES_ALL iteration pattern, but worth explicit
  test).

- **Related gaps:** G7 (empirical driver), G10 (future consideration
  of retiring the docker-pipeline canary in favor of reusable workflows
  — that path was closed by locking option 2 here; G10 stays open as
  a separate future consideration if reusable-workflows appetite ever
  materializes).

### G16 — Conductor overshoots to next patch version post-tag  *(discovered live 2026-07-08, SHIPPED 2026-07-10)*

**STATUS: SHIPPED 2026-07-10** as openemr/openemr#12868 (master)
+ #12872 (rel-820 backport, patch-id parity verified via
`c25529e29e5459dac9e3be5b5a4d4f39bd921e7b`). Also required
#12871 as a preceding rel-820 doc catch-up to make #12872 a
clean cherry-pick.

Fix approach: state gate in `release-prep.yml`'s Resolve step
that parses version.php via a shell `parse_version_php` helper
(mirroring `patch-prep-automation.yml`'s existing pattern) and
requires `$v_tag == '-dev'` before proceeding. When absent, the
step sets `should-run=false` and 9 subsequent steps in the
`prep` job skip cleanly via a `steps.resolve.outputs.should-run
!= 'false'` `if:` condition. Target version derived directly
from version.php's own `MAJOR.MINOR.PATCH` — the maintainer's
explicit choice when bumping the branch into dev — rather than
`highest_patch + 1` from the tag walk. Includes belt-and-braces
error paths for parser drift + branch/version.php major-minor
consistency (both from CodeRabbit review). No changes to
`BranchVersionResolver::branchToVersion()` itself; the tag walk
is still used from the finalize job where its semantics are
correct.


- **What:** After a release tag is created, the release-prep conductor
  auto-advances rotation as if the next patch cycle has already
  started. On the 8.2.0 ship it produced four artifacts pointing at a
  nonexistent `v8_2_1`:
  - **openemr/openemr#12767** (finalize-on-master PR): rel-820 row's
    `openemr_version_ref` force-pushed from `rel-820` to `v8_2_1`.
    The `docker-validate-release-targets.yml` guard blocked the merge
    with `openemr_version_ref 'v8_2_1' does not resolve` (HTTP 404 on
    raw.githubusercontent.com fetch).
  - **openemr/openemr#12843** (release-prep 8.2.1 PR): new bot PR
    bumping `version.php` + `docker/production/docker-compose.yml` +
    `swagger/openemr-api.yaml` + OpenApiDefinitions to 8.2.1.
  - **openemr/website-openemr#179** (release-docs 8.2.1 DRAFT PR):
    generated acknowledgements + release-notes for a nonexistent
    8.2.1 against a `prev_release=8.2.0` window.
  - Docker Hub `latest` fanout consequence: the guard-blocked #12767
    stopped the corrected `release-targets.yml` from reaching
    master, which delayed the `latest` tag promotion to 8.2.0 until
    the ref was corrected.

- **Policy:** rel-`NNN0` stays pinned at the just-released tag
  (`v_X_Y_Z`) until an explicit patch cycle is started (via a
  `$v_patch` bump on the rel branch — workstream 6's patch-prep
  trigger surface). 8.2.1 is only a valid target when someone
  decides to make it one — not on 8.2.0's ship.

- **Immediate workarounds applied (2026-07-08):**
  - #12767: force-pushed a corrective one-line edit changing
    `openemr_version_ref: v8_2_1` back to `v8_2_0`; title corrected
    from "finalize 8.2.1" to "finalize 8.2.0"; merged.
  - #12843: closed as "no 8.2.1 patch cycle planned; conductor
    overshoot after 8.2.0 tag creation".
  - website #179: closed as stale draft.

- **Root cause hypothesis:** whichever post-tag hook in the
  conductor computes the next `openemr_version_ref` treats the
  just-shipped tag as if the branch has already entered
  `(patch+1)-dev`. Should either (a) point at the just-released tag
  (`v_X_Y_Z`) and hold until a patch cycle explicitly starts, OR
  (b) key the rotation on a `version.php` bump event on the rel
  branch (mirroring workstream 6's patch-prep automation trigger
  surface), not on the tag creation.

- **Fix scope:** single conductor code fix eliminates all four
  downstream symptoms.

### G17 — Redundant `push: tags: ['v*']` trigger on `docker-build-release.yml`  *(discovered 2026-07-08, SHIPPED 2026-07-10)*

**STATUS: SHIPPED 2026-07-10** as openemr/openemr#12862, bundled
with G18. Removes the `push: tags: ['v*']` trigger and drops the
now-unreachable push branches in the two resolve steps
(`resolve_docker_tags` and `resolve_openemr_version_ref`).
Byte-identical-enforced file — master merge auto-syncs to rel-820
+ rel-810 + rel-800 + rel-704 via sync-byte-identical.yml.
Companion doc update to RELEASE_PROCESS.md step 12 landed in the
same PR.


- **What:** `docker-build-release.yml` currently triggers on
  `push: tags: ['v*']` in addition to `workflow_dispatch`. The
  orchestrator already fires per-branch fanout via
  `push: paths: [.github/release-targets.yml]` on master, dispatched
  to `docker-build-release` as `workflow_dispatch`. The tag-push
  trigger is a second path to the same image content and causes
  double-builds every release.

- **8.2.0 concrete observation:** Tag-push run 28973845615
  (event=push, branch=v8_2_0) built + pushed
  `openemr/openemr:8.2.0`. After #12767 merged, orchestrator fanout
  run 28976847649 (event=workflow_dispatch, branch=rel-820) built +
  pushed the same `8.2.0` + `latest` from `release-targets.yml`
  state. Content was equivalent — no correctness issue, just
  wasted CI + two orphan image digests on Docker Hub.

- **Fix:** Remove `push: tags: ['v*']` from
  `docker-build-release.yml`. Rely on the orchestrator (release-
  targets.yml push + daily 06:00 UTC cron + manual dispatch) as
  the sole trigger surface. Loses the "immediate build on tag push"
  reflex, gains a consistent-state guarantee: every docker build
  reflects the current committed `release-targets.yml` and cannot
  race a mid-flight release-targets update.

- **Related:** G18 (concurrency group) would prevent the wasteful
  overlap without removing the tag trigger, but G17 is the cleaner
  fix — one trigger surface instead of two.

### G18 — No `concurrency:` group on `docker-build-release.yml`  *(discovered 2026-07-08, SHIPPED 2026-07-10)*

**STATUS: SHIPPED 2026-07-10** as openemr/openemr#12862, bundled
with G17. Adds `concurrency: docker-build-release-${{ github.ref
}}-${{ inputs.docker_tags }}` with `cancel-in-progress: false`.
Key includes `inputs.docker_tags` per CodeRabbit review — same
branch with different `docker_tags` (multi-row-per-branch case:
a rel-* branch with both a stable-tag row and an `unreleased:
true` next-version row) fans out in parallel because they push
different registry tag pointers with no shared race surface.
Same-branch/same-tags dispatches serialise as intended.


- **What:** `docker-build-release.yml` has no `concurrency:` block,
  so multiple simultaneous invocations against the same branch/tag
  can race. Observed a benign version of this during 8.2.0 ship
  (the same double-build called out in G17 — tag-push +
  orchestrator-fanout running concurrently, both building the same
  v8_2_0 source, pushing the same `8.2.0` tag pointer, last-write-
  wins with identical content = no functional impact).

- **Real race we haven't hit yet:** two orchestrator-fanout runs
  for the same branch (e.g., release-targets.yml push + daily cron
  tick landing close together) would push the same tag
  simultaneously → registry sees both pushes → tag pointer race +
  wasted CI + potential digest-swap flapping until the last push
  wins.

- **Fix:** Add
  `concurrency: docker-build-release-${{ github.event.inputs.branch
  || github.ref }}` with `cancel-in-progress: false` (regenerations
  are cheap; cancelling could lose state mid-push). Serializes
  per-branch builds without cross-branch interference.

### G19 — `raw.githubusercontent.com` rate-limit + misleading error on version.php pre-fetch  *(discovered live 2026-07-08, SHIPPED 2026-07-11)*

**STATUS: SHIPPED 2026-07-11** as openemr/openemr#12883.
Applied uniformly across three version.php fetches in two
release-flow workflows:

- `.github/workflows/docker-build-release.yml` — pre-build
  IMAGE_VERSION derivation (1 call).
- `.github/workflows/docker-validate-release-targets.yml` —
  "Verify every openemr_version_ref resolves" step (1 call) +
  "Cross-check docker_tag version" step's non-master fetch (1
  call). Master row still uses the local PR checkout (chicken-
  and-egg preserved).

Two coupled changes at each site:

1. Anonymous `curl` against `raw.githubusercontent.com` → `gh api`
   at `/repos/openemr/openemr/contents/version.php?ref=$REF`.
   Content fetches use `Accept: application/vnd.github.raw` to
   get the file body directly; status-only probes use the default
   representation and check the return code. Each step gets
   `GH_TOKEN: ${{ secrets.GITHUB_TOKEN }}` in `env:`. Authenticated
   5000/hr per-token budget replaces anonymous 60/hr shared-IP.
2. Error path distinguishes HTTP 404 (real bad ref → "does not
   resolve in openemr/openemr; check release-targets.yml") from
   any other failure (429, 5xx, network — surfaces the underlying
   `gh api` error verbatim instead of the previous misleading
   "ref likely doesn't exist" message).

CodeRabbit review on the initial commit caught that
`docker-validate-release-targets.yml` had the same vulnerability;
the follow-up commit extended the fix to that file too. The
`docker-build-release.yml` fix auto-syncs to rel-820 + rel-810 +
rel-800 + rel-704 via `sync-byte-identical.yml` (byte-identical-
enforced file). `docker-validate-release-targets.yml` is master-
only (fires only on `pull_request: branches: [master]`) — no
cross-branch propagation needed.


- **What:** `docker-build-release.yml` prefetches
  `https://raw.githubusercontent.com/openemr/openemr/${REF}/version.
  php` via unauthenticated `curl` to sanity-check the
  `openemr_version_ref` before building. On the 8.2.0 orchestrator
  fanout, the rel-820 build hit HTTP 429 (rate limit) and reported
  `##[error]Couldn't fetch version.php at openemr@v8_2_0. The ref
  likely doesn't exist; check release-targets.yml.` — a misleading
  message: the tag existed; the fetch was throttled after earlier
  validate-loop fetches + all 4 fanout branches hitting the same
  URL simultaneously.

- **Fix (two parts):**
  1. **Retry-with-backoff on 429:** wrap the curl with 3-5 retries
     spaced by `Retry-After` (default 30-60s) before failing. Or
     switch to `gh api` — has authenticated rate limits, much higher
     ceiling, and better error semantics.
  2. **Distinguish 404 vs 429 in the error message.** Emit a clear
     `HTTP 429 rate limit — retry after N seconds` for 429s so the
     operator doesn't chase a phantom "bad ref" during a real
     transient throttle.

- **Recovery cost during 8.2.0 ship:** ~20 min manual rerun latency
  after diagnosing the 429 was the cause. Would have been zero with
  either fix in place.

### G20 — `openemr-tag` event schema drops `prev_release` end-to-end  *(discovered 2026-07-08, refined 2026-07-09, SHIPPED 2026-07-11)*

**STATUS: SHIPPED 2026-07-11** as a coordinated three-repo change:

- openemr/openemr-devops#855 — canonical
  `tools/release/contracts/dispatch.schema.json`: `tagData`
  block adds `prev_release` to `required` + `properties`. Also
  updates the four fixture copies (dispatch/good-tag*.json and
  the two vendored self-test schemas) to carry the field and
  bump `8.1.0 → 8.2.0` (8.1.0 was a prerelease that never
  publicly shipped and read misleadingly as if it had).
- openemr/website-openemr#189 — website envelope schema +
  `inputs-to-envelope.jq`'s openemr-tag branch (was the sole
  event that omitted `prev_release`) + three fixtures updated
  to carry the field and use 8.2.0.
- openemr/openemr#12887 — vendored copy of the canonical
  schema, `release-prep.yml`'s openemr-tag emitter now derives
  `prev_release` via `derive-prev-release.php` and passes
  `--prev-release`, `DispatchDataBuilder::EVENT_TAG` match arm
  reads the CLI option into the emitted data block, tests and
  fixtures updated to match.

Together the three PRs close the schema-strips-prev_release
chain so the next FINAL cut carries the field end-to-end. The
downstream `release-docs.yml` generators (acknowledgements +
release-notes), gated on `if: prev_release != ''`, will fire
on FINAL cuts instead of silently skipping.

The 8.2.0 acknowledgements page recovery was already handled
out-of-band via openemr/website-openemr#181's manual
openemr-rel-update dispatch. This shipping is prospective.


- **What:** website-openemr's `release-docs.yml` gates two steps on
  `if: prev_release != ''` — "Generate acknowledgements" (writes
  `content/acknowledgements/$VERSION.md`) and "Generate release
  notes" (writes `content/release-notes/$VERSION.md`). On the 8.2.0
  shipping path, the `openemr-tag` dispatch fired without
  `prev_release` and both steps SKIPPED. The `openemr-rel-cut` /
  `openemr-rel-update` dispatches from earlier in the same cycle
  DID carry `prev_release` and generated the pages, but those were
  on the pre-final DRAFT content — and in the case of the 8.2.1
  overshoot (G16), on wrong-version content that got closed as
  stale. Net result: no `content/acknowledgements/8.2.0.md` or
  `content/release-notes/8.2.0.md` exists on the FINAL release
  branch.

- **Recovery attempt (2026-07-09):** manually fired
  `workflow_dispatch` on `release-docs.yml` with explicit
  `event=openemr-tag`, `prev_release=8.0.0`, and other v8_2_0
  payload fields. Run 28990379608 completed successfully — but
  both "Generate acknowledgements" and "Generate release notes"
  STILL skipped in the output. Digging in confirmed the root cause
  is DEEPER than "openemr/openemr's tag dispatch omits
  `prev_release`" — the openemr-tag event actively STRIPS
  `prev_release` at the schema/envelope layer regardless of source
  (repository_dispatch OR workflow_dispatch).

- **Three-place fix (all in openemr/website-openemr):**
  1. `tools/release-docs/contracts/dispatch.schema.json` — the
     openemr-tag event schema (lines 91-111) sets
     `additionalProperties: false` and lists only `["tag", "branch",
     "version"]` in `required` + `properties`. `prev_release` needs
     to be added to both. Compare to the rel-cut / rel-update event
     schemas (lines 49-69, 70-90) which already require it.
  2. `tools/release-docs/contracts/inputs-to-envelope.jq` (line
     20-23) — the openemr-tag branch of the if/else drops
     `prev_release` even when the workflow_dispatch input carries
     it:
     ```jq
     if   .event == "openemr-tag"
     then {tag: .tag, branch: .branch, version: .version}
     else {branch: .branch, version: .version, prev_release: .prev_release}
     end
     ```
     Needs `prev_release: .prev_release` added to the openemr-tag
     branch.
  3. openemr/openemr side — release-prep's tag-dispatch emitter
     needs to include `prev_release` in the `data` payload of the
     openemr-tag repository_dispatch. Same value as the earlier
     cut/update dispatches (via `derive-prev-release.php`, fixed
     via G13 on 2026-07-05).

  All three parts are required. Any one alone leaves the pipe
  broken: (1) without (2) doesn't affect envelope building; (2)
  without (1) fails schema validation; (3) without (1)+(2) still
  loses the value at the boundary.

- **Design refinement (2026-07-09):** The three-place fix restores
  BOTH release-notes and acknowledgements generation on the tag
  path — but only acknowledgements need to LAND on master after
  the fix. Per the "GitHub Release body is source of truth for
  release notes" design (see G22), the website's per-version
  release-notes markdown becomes a maintainer-preview artifact
  visible in the DRAFT PR during rel-cut/rel-update dispatches but
  is not merged to master on the FINAL cut. release-docs.yml will
  need a companion change: on `openemr-tag` event, either skip
  "Generate release notes" or delete `content/release-notes/
  $VERSION.md` from $STAGING before `workflow:commit-push`. See
  G22 for the full surface trim + upstream filter alignment.

- **Related:** G21 (no layout links to these pages) means even a
  fixed generation path doesn't surface acknowledgements on the
  public site until layouts are wired.

### G21 — No layout links to per-version acknowledgements or GitHub Release notes  *(discovered 2026-07-08, refined 2026-07-09, acknowledgements half CLOSED 2026-07-09, release-notes half CLOSED as won't-do 2026-07-09)*

**STATUS: acknowledgements half SHIPPED 2026-07-09** via
openemr/website-openemr#182 (`layouts/section/downloads.html` +
`layouts/section/releases.html`, both use
`site.GetPage "/acknowledgements/<version>"` for graceful
degradation).

**STATUS: release-notes / changelog half CLOSED as won't-do 2026-07-09.**
Decision (during #184 review): no dedicated changelog surface on
the public site. GitHub Release body + downloadable `changelog.md`
release asset are the source of truth for release notes; the
existing "Release notes (GitHub)" link on both layouts already
takes users there. Website's release-docs DRAFT PR keeps
generating a per-version release-notes markdown for maintainer
preview during development, but the file is not surfaced on the
public master. G22 (align GitHub Release body + codebase
`CHANGELOG.md` filter algorithms to drop bot commits) remains
the follow-up so the two surfaces stay high-quality without
depending on the website's `gen-release-notes.php` for filtering.


- **What:** website-openemr's release-docs workflow generates per-
  version acknowledgements markdown at
  `content/acknowledgements/$VERSION.md`. When present, Hugo renders
  it at `/acknowledgements/$VERSION/` per the section-page
  convention. But no layout template on the site links to it.
  `grep` of `layouts/section/{downloads,releases}.html` +
  `layouts/shortcodes/release-status.html` for "acknowledg" or
  "release-notes" returns nothing.

- **Impact:** Even when the generation path fires (which — per G20
  — it currently doesn't for FINAL cuts), the content is
  inaccessible to users except by typing the URL directly.

- **Fix (per 2026-07-09 design refinement, see G22):** Add
  per-version link entries to `layouts/section/releases.html`
  and/or `layouts/section/downloads.html`. Two link targets per
  released version:
  - **Release notes:** point at the GitHub Release URL, computed
    from the version key —
    `https://github.com/openemr/openemr/releases/tag/v${major}_${minor}_${patch}`.
    (The website does NOT surface its own per-version release-
    notes markdown on master — that's a DRAFT-PR-only artifact.
    See G22.)
  - **Acknowledgements:** point at Hugo's rendered page for the
    per-version markdown, via
    `.Site.GetPage "/acknowledgements/$version"` — omit the link
    when the page doesn't exist (graceful degradation for pre-
    mechanism releases without generated pages).

- **Related:** G20 needs to land first (or in parallel) for the
  acknowledgements pages to exist for 8.2.0+. G22 defines the
  "GitHub Release body = source of truth for release notes"
  design that shapes the release-notes link target choice.

### G22 — GitHub Release body + repo CHANGELOG.md need the website's dependabot filter algorithm  *(discovered 2026-07-09, fix in flight 2026-07-12, PRs 4+5+8 landed 2026-07-13, PRs 6+7+10 landed 2026-07-14, PR 9 landed 2026-07-15 → **CLOSED**)*

**Status: CLOSED — 10 of 10 PRs SHIPPED** plus 6 post-slice follow-ups shipping the release-amendment workflow (see below). Ten-PR "Changelog surface early-migration slice" (see `docs/release-mechanism-migration-from-devops.md` for the plan). Slice consolidated the changelog content across all three surfaces (website release-notes / codebase CHANGELOG.md / GitHub Release body) into a single generator that lives in openemr/openemr, ported the website filter along the way, and retired the website generator + the two post-tag changelog PRs. As of 2026-07-15:

- **PR 1** openemr/openemr#12896 — rename `docker-byte-identical.yml` → `byte-identical.yml` (SHIPPED)
- **PR 2** openemr/openemr#12904 — glob-pattern support in validator + sync scripts (SHIPPED)
- **PR 3** openemr/openemr#12910 — add release-mechanism surface to byte-identical.yml (SHIPPED)
- **Pre-PR-4 refactor** openemr/openemr#12924 — extract manifest parsing into shared `list-manifest-paths.sh` + BATS coverage (SHIPPED). Not in the original slice plan; added after two consecutive workflow yq bugs traced back to three-way parsing duplication.
- **PR 4** openemr/openemr#12925 + openemr/openemr-devops#857 — move ChangelogGenerator + friends to openemr, wire ChangelogMutator, retire devops's `changelog-pr.php` (SHIPPED same-day coordinated to avoid the "release cut in the gap" window where devops has stopped opening the two post-tag PRs but the mutator isn't in place yet)
- **PR 8** openemr/openemr#12928 + openemr/openemr#12933 (rel-820 config-file ports for `.composer-require-checker.json` + `.codespell-ignore-words.txt`) + auto-sync openemr/openemr#12932 — CompatibilityMutator wired after ChangelogMutator on both scopes; `CompatibilityNotesRenderer::inject()` idempotence fixed (with a CR-round-1 catch scoping the strip to the first `## ` section so older releases' compat blocks are preserved); latent ChangelogMutator relBranch bug from PR 4 fixed (workflow now passes `--rel-branch` on rel scope). Resequenced 2026-07-13 to land BEFORE PR 5 (compat-gap window: PR 5's section-extract would have pulled entries missing the compat block for 8.2.1+ without the mutator in place). (SHIPPED)
- **PR 5** openemr/openemr-devops#858 — rewire devops `build-release.yml` to section-extract from openemr's tagged CHANGELOG.md; deleted ChangelogGenerator + CompatibilityDeriver + CompatibilityNotesRenderer + their bin wrappers from devops; new `extract-changelog-section.php` reads the pre-computed section (both filtered PR list + Minimum-supported-versions block, both baked into openemr's CHANGELOG.md by PR 4 + PR 8 mutators); also cleaned up dead `base_ref` end-to-end. (SHIPPED)
- **PR 6** openemr/website-openemr#190 — retired website's `gen-release-notes.php` + `ReleaseNotesGenerator.php` + test + Taskfile task; workflow "Generate release notes" step replaced with scrub-stale-page step (matches the retired install/upgrade-pages pattern); 8.2.0's live page at openemr.org/release-notes/8.2.0/ grandfathered (scrub only touches the current dispatch's version). AcknowledgementsGenerator untouched. (SHIPPED)
- **PR 7** openemr/openemr#12964 — retired `src/Common/Command/CreateReleaseChangelogCommand.php` (Stephen Nielson's 374-line Guzzle-based milestone helper); also removed the byte-identical manifest entry + pruned 66 stale phpstan-baseline entries across 17 files + lowered two fatal-baseline-caps (`method.notFound` 138→137, `variable.undefined` 509→508) in the same commit. 706 deletions net. Unblocks PR 10. (SHIPPED)
- **PR 9** openemr/openemr#12969 (rel-820 sync openemr/openemr#12995 as workaround for whitespace-check failure on auto-sync openemr/openemr#12992) — fixture-based regression regenerating 8.2.0's real CHANGELOG entry from frozen inputs. Captured live gh API state via new `tools/release/bin/capture-changelog-fixture.php` one-shot (653 SHAs, 647 unique PRs). Twin-scenario fixture layout: `release-time/` (empty advisories) + `post-ghsa/` (matching advisories only). Also introduced a matcher change: `ChangelogGenerator::advisoryMatchesRange()` now matches on `vulnerabilities[].patched_versions == targetVersion` as primary signal (openemr GHSAs don't populate References with commit/PR URLs, so pre-existing reference-based matcher never fired in production). Added defensive `state === 'published'` post-filter. 3 new unit tests including a mutator idempotence test covering release-time → post-ghsa → post-ghsa → release-time round-trip. (SHIPPED)
- **PR 10** openemr/openemr#12976 (rel-820 sync openemr/openemr#12977 — docs/ not on byte-identical manifest, so manual) — RELEASE_PROCESS.md rewrite for mutator-driven flow. Deleted "Changelog PRs" subsection entirely, collapsed merge-order from 4 → 3 PRs, rewrote runbook step 6 as "edit CHANGELOG.md on release-prep PR" with hand-edit caveat, rewrote step 10 (build-release-on-tag) for section-extract flow, docs-first recovery updated. (SHIPPED)

**Post-slice follow-ups (all landed or in-flight 2026-07-15):** the slice closed the *migration* but surfaced a timing gap: GHSAs typically get published *after* the release ships, so the shipped CHANGELOG + Release body miss late-published Security sections. Not a regression from the migration (devops's `changelog-pr.php` had the same window) but the migration made it a *fixable* gap. Follow-ups:

- **openemr/openemr#12993** — docs the "when publishing a GHSA, set Patched versions to the exact release string" convention. Strict-string-equal is what PR 9's matcher checks. (SHIPPED)
- **openemr/openemr#12996** — new `.github/workflows/release-amendment.yml`. Manual `workflow_dispatch` the release manager runs post-GHSA-publish. Reuses `openemr:release-prep --scope=rel|master` on post-tag checkouts: all mutators except ChangelogMutator + CompatibilityMutator are idempotent no-op against shipped state, so the diff is scoped to CHANGELOG.md's target section. Opens `release-amendment/<version>-<rel_branch>` + `release-amendment/<version>-master` PRs via peter-evans + re-extracts + `gh release edit`s the GitHub Release body + syncs `changelog.md` attachment in one run. Preserves original release date via extract-and-restore. Validates annotated tag exists before amending. (SHIPPED)
- **openemr/openemr#12998** — rel-820 doc sync for #12993/#12996. (SHIPPED)
- **openemr/openemr#12999** — GH_TOKEN fix on amendment workflow's mutator steps. First real dry-run failed because `ChangelogMutator` shells out to `gh api` and needs `GH_TOKEN`; my workflow set `persist-credentials: false` for hardening so `gh` had no ambient token to fall back on. Fix: explicit `GH_TOKEN: ${{ steps.app-token.outputs.token }}` in mutator step env blocks. release-prep.yml has the same latent issue but happens to work today because it uses default `persist-credentials: true`; noted as separate hardening follow-up. (SHIPPED)
- **openemr/openemr#13000** — new `.github/workflows/release-mechanism-smoketest.yml`. CI-gated smoke test that runs `openemr:release-prep --scope=rel` end-to-end against rel-820 on every PR touching a release-mechanism workflow YAML or PHP file. Deliberately uses the strictest workflow shape (`persist-credentials: false` + explicit `GH_TOKEN`) so any regression in either release-prep.yml or release-amendment.yml's auth/env plumbing fails CI before merge. The `ChangelogMutatorTest` + `ChangelogGeneratorFixtureTest` unit tests don't exercise the actual `gh api` shellout (they inject FakeGitHubApi), so the smoke test is the only CI gate that catches env-plumbing bugs like #12999. (SHIPPED)
- **openemr/openemr#13004** — extract-order fix. First real dispatch surfaced a `peter-evans/create-pull-request@v8` gotcha: it resets the calling checkout's working tree back to base-branch HEAD after committing to the amendment branch. The Extract step (which pipes to `gh release edit --notes-file`) was running AFTER peter-evans, so it saw the pre-amendment content and Release body + attachment got overwritten with byte-identical pre-amendment content. Fix: reorder Extract to run BEFORE peter-evans; extracted content lives in `$RUNNER_TEMP/section.md` which survives peter-evans's reset. Saved as memory (`feedback_peter_evans_resets_working_tree.md`) for future workflows. (SHIPPED)
- **openemr/openemr#13005** + rel-820 sync **openemr/openemr#13006** — noise-filter relaxation. Review of the amendment diff showed the ported website filter was too aggressive for the CHANGELOG surface: dropped rel-branch backports (like openemr/openemr#12827 + #12832) and human-authored `fix(release):` PRs (~10 in the 8.2.0 range). Both filter rules removed. Docker auto-bump noise (the actual thing the release-scope rule was aimed at) is already handled by `isDockerBump()` + `isNoOpVersionBump()` under the DEPENDABOT branch — those stay. Also tightened chore-release-cut regex per CR round 1: requires `\s+v?\d` after "release" so `chore(docs): release notes update` no longer false-matches. Website release-notes page still uses stricter filter (different audience). (SHIPPED)
- **openemr/openemr#13007** — rel-checkout submodule leak fix. Second real dispatch (post-#13004 + #13005 + #13006) landed the amended Release body correctly, but master-side amendment PR #13003 accumulated a spurious `rel-checkout: Subproject commit <sha>` entry — master's peter-evans's `git add -A` at the workflow root treated the rel-820 checkout subdirectory (which has its own `.git/`) as a git submodule. Fix: `rm -rf rel-checkout` between the rel-side peter-evans call and the master-scope block. (SHIPPED)
- **openemr/openemr#13002 + #13003** — real amendment PRs (8.2.0-rel-820 + 8.2.0-master). Landed with the amended CHANGELOG diff (Security Fixes section with `GHSA-vv5j-6gjw-ffx9` + full PR list including the ~12 previously-filtered PRs from #13005, compare link updated to `v8_1_0...v8_2_0`, date preserved as 2026-07-08). Force-updated three times across the fix iterations before landing clean. (SHIPPED)

**End-to-end validation history for the amendment workflow:**

- Dry-run #1 (openemr/openemr actions run 29392343714, 2026-07-15 05:46Z): **failed** at mutator step, missing GH_TOKEN — prompted #12999.
- Dry-run #2 (run 29396553059, 07:11Z, post-#12999): **succeeded** end-to-end. All 28 steps green, dry-run paths correctly skipped, mutator diff previewed in step summary, `changed=true` on both scopes (GHSA-vv5j-6gjw-ffx9 matched).
- Real dispatch #1 (run 29397657588, 07:31Z, post-#12999 but pre-#13004): all steps green, #13002 + #13003 opened with correct commit content, but Release body edit put byte-identical pre-amendment content because of the peter-evans reset. Prompted #13004.
- Real dispatch #2 (run 29404476642, 09:26Z, post-#13004 + #13005 + #13006): Release body + `changelog.md` attachment correctly updated with amended content; #13002/#13003 force-updated with the fuller PR list. BUT #13003 accumulated a spurious `rel-checkout` submodule entry. Prompted #13007.
- Real dispatch #3 (run 29422237030, 14:08Z, post-#13007): **fully green end-to-end**. #13002 + #13003 show only `CHANGELOG.md` (+6/-265 each). Release body + attachment byte-identical (already amended in dispatch #2). #13002 + #13003 merged shortly after — all four surfaces (rel-branch CHANGELOG, master CHANGELOG, GitHub Release body, `changelog.md` attachment) now carry the amended 8.2.0 Security section + full PR list.

Ancillary follow-ups landed alongside: openemr/openemr#12901/#12902/#12903 (rel-branch orphan cleanup after PR 1's rename), openemr/openemr#12905 (rel-820 stale-refs cleanup), openemr/openemr#12906 (hotfix for sync workflow's RUNNER_TEMP extraction after PR 2 added a script dependency), openemr/openemr#12907/#12908/#12909 (sync PRs propagating PR 2's shared lib to rel branches), openemr/openemr#12915 (pivot PR 3's 12 release-mechanism entries from bare-string form to object form with per-entry `exclude-branches:` — rel-800/rel-704 couldn't carry the 60 release-mechanism PHP files without autoload topology, phpstan + isolated-tests + composer-require-checker were failing on the sync PRs), openemr/openemr#12916 + openemr/openemr#12920 (two consecutive workflow yq bugs surfaced during the object-form validation — object entries dumped as raw YAML splat, then paths extracted but not filtered by branch), openemr/openemr#12921/#12922/#12923 (sync PRs propagating PR 3's release-mechanism surface + object-form manifest to rel branches), openemr/openemr#12926 (auto-sync PR propagating PR 4's mutator + generator + tests to rel-820), openemr/openemr#12927 (manual rel-820 port of the ChangelogMutator whitelist entry to `.composer-require-checker.json` — that file isn't in the byte-identical set, so the sync PR didn't carry the entry and require-checker was failing on rel-820 until the manual port). Also openemr/openemr-devops#856 (bump kubernetes deployment image pin from 8.1.0 → 8.2.0 — unrelated to this slice but done in the same window).

The two follow-ups deferred from PR 4 are now first-class in the slice plan as PR 8 (CompatibilityMutator + `CompatibilityNotesRenderer::inject()` idempotence fix) and PR 9 (fixture regression against 8.2.0's real inputs) — see the PR list above.

The design + fix-scope discussion below reflects the plan the slice implements.

- **What:** website-openemr's `gen-release-notes.php` implements
  a curated dependabot filter (#173, landed 2026-07-05): keeps
  composer/npm dependabot bumps per #136 intent, drops docker-
  image bumps identified by openemr/openemr's dependabot.yml
  groups + path patterns (specifically the `openemr-images` group
  bumps like `bump openemr/openemr from flex-3.17 to flex-3.17 in
  /docker/development-insane in the openemr-images group across
  1 directory`). The result is a clean, human-readable release-
  notes markdown file.

  Two other surfaces publish "release notes" for the same version
  but do NOT apply this filter:
  - **GitHub Release body** on the `v_M_m_p` tag object (visible
    at `https://github.com/openemr/openemr/releases/tag/v_M_m_p`).
    Auto-generated at tag creation.
  - **`CHANGELOG.md`** in the openemr/openemr codebase (added per
    release via the changelog-add PRs — for 8.2.0, that was
    #12844 on rel-820 + #12845 on master). Same underlying
    changelog is uploaded as the `changelog.md` release asset on
    the GitHub Release.

  Concrete measure for 8.2.0's GitHub Release body: **794 total
  lines, ~224 (~28%) match `dependabot`/`[bot]`/`bump` patterns.**
  Long consecutive runs of the `openemr-images group across 1
  directory` bumps — precisely what #173 filters — are present
  verbatim.

- **Design (2026-07-09):** GitHub Release body is the source of
  truth for release notes on the FINAL cut. The repo
  `CHANGELOG.md` should carry the same filtered content (single
  algorithm across both). The website's per-version release-notes
  page is redundant with those two and should not land on master
  after the FINAL tag dispatch. It remains valuable as a maintainer
  preview during rel-cut/rel-update DRAFT PR cycles.

- **Fix scope (three interlocking pieces):**
  1. **Extract the #173 filter into a shared algorithm** — port
     the logic out of website-openemr's `gen-release-notes.php`
     into a form consumable by openemr/openemr's release tooling.
     Either an inline copy (byte-identical risk on drift) or a
     packaged tool that both consumers call.
  2. **Apply the filter to `CHANGELOG.md` generation** on
     openemr/openemr. Determine where the current CHANGELOG.md
     entries get produced (release-prep conductor or a
     conventional-commits tool wrapper) and inject the filter
     there. Rebuild-safe — regenerate a filtered CHANGELOG.md at
     release time from the same PR-list source.
  3. **Apply the filter to GitHub Release body generation** on
     openemr/openemr. If body is currently produced by
     release-please, configure its skip labels/paths. If custom,
     add the filter to that path. The `changelog.md` release
     asset should also be regenerated with the filter (same
     source content).

  Companion workflow change on website-openemr side (also
  described under G20's design refinement):
  - `release-docs.yml`: on `openemr-tag` event, skip "Generate
    release notes" or delete
    `$STAGING/content/release-notes/$VERSION.md` before
    `workflow:commit-push`. Preserves the release-notes preview
    in DRAFT PRs (rel-cut/rel-update dispatches) without landing
    the file on master.

- **Sequencing:** G22 needs to land BEFORE dropping website
  release-notes visibility. Until then, the website's per-version
  release-notes markdown remains the highest-quality surface
  (unfiltered GitHub Release body is not an equivalent
  replacement).

- **Related:** G20 (fix `prev_release` chain so acknowledgements
  land on the FINAL cut) is orthogonal to G22 but the design
  refinement in G20 depends on this gap's outcome. G21 (layout
  links) references G22 for the "link to GitHub Release for
  notes" target.

### G23 — `workflow:upsert-pr` misses the "merged PR + regenerated branch" case  *(discovered live 2026-07-09, SHIPPED 2026-07-11)*

**STATUS: SHIPPED 2026-07-11** as openemr/website-openemr#187.
`workflow:upsert-pr` now uses `gh pr list --head "$BRANCH" --state
open --json number` and captures the resulting PR number
explicitly. When no open PR exists it `gh pr create --draft`
regardless of whether merged/closed PRs are associated with the
branch; when one does, it `gh pr edit`s by number so no branch-
name ambiguity remains. Closes the silent-absorb failure mode
that hit the 2026-07-09 8.2.0 recovery dispatch (which required
manual `gh pr create` for #181 to salvage).


- **What:** `tools/release-docs/Taskfile.yml`'s `workflow:upsert-pr`
  task guards create-vs-edit on the presence of ANY PR for the
  branch, without filtering by state:
  ```bash
  if gh pr view "$BRANCH" --repo "$GITHUB_REPOSITORY" --json number >/dev/null 2>&1; then
      gh pr edit "$BRANCH" ...          # editing existing
  else
      gh pr create --draft ...          # opening new
  fi
  ```
  `gh pr view <branch>` returns the most recent PR for that head
  branch regardless of state. When a MERGED PR exists for
  `release-docs/<version>` and a fresh dispatch regenerates the
  branch, the task hits the edit branch and runs `gh pr edit` on
  the merged PR (a silent no-op for the actual intent — a merged
  PR can't be reopened or receive new commits). The force-pushed
  branch content sits there with no live PR pointing at it.

- **Concrete surface (2026-07-09):** Recovery dispatch for 8.2.0
  (`openemr-rel-update` event with `prev_release=8.0.0`) ran
  successfully — both `gen-acknowledgements` and
  `gen-release-notes` produced files, `workflow:commit-push`
  force-pushed `release-docs/8.2.0` to a fresh sha
  (`d31a801d4fe2`). `workflow:upsert-pr` step marked SUCCESS but
  `gh pr view release-docs/8.2.0` matched merged PR #164 →
  ran `gh pr edit #164` → workflow log shows
  `https://github.com/openemr/website-openemr/pull/164` in the
  final line. No new PR opened. Recovery manually completed by
  opening a fresh PR (#181) from the CLI.

- **Fix:** filter by state=open in the view guard:
  ```bash
  if gh pr view "$BRANCH" --repo "$GITHUB_REPOSITORY" \
       --json number,state --jq 'select(.state == "OPEN")' \
       >/dev/null 2>&1 && [ -n "$(gh pr view "$BRANCH" \
       --repo "$GITHUB_REPOSITORY" --json state --jq '.state')" ]; then
  ```
  Or simpler: use `gh pr list --head "$BRANCH" --state open --json number`
  and branch on whether the result array is empty.

- **When it triggers:** Any time a `release-docs/<version>`
  branch gets regenerated AFTER its PR has merged. Two known
  paths:
  - **Recovery dispatches** (this incident): fixing content bugs
    after the FINAL cut merged.
  - **Post-merge conductor re-fires** that update release-docs
    for a version that already shipped (edge cases in future
    workflow paths).

- **Related:** G20's recovery path via `openemr-rel-update` is
  the concrete trigger that surfaced G23; but G23 is independent
  and generic to the upsert-pr contract. Even after G20 lands
  (schema + jq + emitter fix), G23 remains until upsert-pr is
  hardened.

### G24 — Demo page version references are manually maintained  *(discovered 2026-07-09, SHIPPED 2026-07-10)*

**STATUS: SHIPPED 2026-07-10** as openemr/website-openemr#185.
Implements the render-time Hugo shortcode option (preferred
design captured below): new
`layouts/shortcodes/current-stable-version.html` reads
`data/releases.json`, filters for `status: FINAL`, sorts by
version descending, and returns the highest as a plain string.
Replaces three hardcoded `8.2.0` references in
`content/demo/_index.md` (H1 heading + two table rows) with
`{{< current-stable-version >}}`. Prerelease-only versions
(status != FINAL) are ignored so an aborted cut like v8_1_0
doesn't briefly become the "current stable" between its cut
and the next real release. Empty fallback on no-FINAL entries
(data-corruption case) rather than a hardcoded default -- makes
the corruption visible fast rather than silently masking it.


- **What:** website-openemr's `content/demo/_index.md` hardcodes
  the current-stable OpenEMR version in three places (as of the
  8.2.0 cycle):
  - H1 heading — `# Fully Working OpenEMR 8.2.0 Demo`
  - Main demo table row — `| OpenEMR 8.2.0 Main Demo | ... |`
  - Portal demo table row — `| OpenEMR 8.2.0 Portal Demo | ... |`

  The URLs, credentials, and alternate/portal demo entries are
  version-agnostic and don't change per release. Only the three
  version strings need updating each release cycle.

- **Manual bump for 8.2.0 (2026-07-09):** landed via
  openemr/website-openemr#183 as a straight-through
  `s/8.0.0/8.2.0/g` on the three references. Was 8.0.0 before
  because the 8.1.0 prerelease cycle was not a public release
  (G13 backstory), so the previous shipped version was 8.0.0.

- **Automation design (two options):**
  1. **Data-driven at render time.** Replace the hardcoded
     version strings with a Hugo shortcode that reads
     `.Site.Data.releases`, picks the highest-version FINAL
     entry, and renders the version into the heading/table
     rows. Future bumps happen automatically the moment a new
     FINAL entry lands in `data/releases.json` (via a
     release-docs PR merge). Single source of truth, no
     workflow logic needed.
  2. **Auto-updated at release-docs dispatch time.** Have
     `release-docs.yml` detect when a new FINAL version has
     shipped and rewrite the demo page's version strings as
     part of the existing per-version content generation.
     Requires the workflow to touch content/demo/_index.md
     during a dispatch it currently doesn't touch, plus a
     "did anything change?" check to avoid empty commits.

  **Preference: option 1.** Cleaner separation (workflow keeps
  its per-version scope; layout keeps rendering responsibilities;
  data drives). Also handles the "prerelease was cut but never
  shipped" case gracefully — `data/releases.json` already knows
  what's actually FINAL (per G13's manifest-aware behavior), so
  the demo page would auto-skip prereleases without any
  additional logic.

- **Related:** G6 (demo_farm_openemr auto-derivation goal —
  same theme: derive downstream state from upstream shipped
  versions instead of hand-editing) applies the same principle
  to demo_farm_openemr's `ip_map_branch.txt` +
  `demoLibrary.source`. G24 is the website-side analogue.

### G25 — Copilot appears as a contributor in generated acknowledgements  *(discovered 2026-07-09, SHIPPED 2026-07-10)*

**STATUS: SHIPPED 2026-07-10** as openemr/website-openemr#186.
Extended `AcknowledgementsGenerator`'s filter beyond the `[bot]`
suffix rule (from #172) to also drop authors whose name is an
exact-match member of a hand-curated `NON_HUMAN_NAMES` blocklist
in the class. Initial blocklist entry: `Copilot`. Filter method
renamed `filterBots()` → `filterAutomatedAuthors()` to reflect
the broader scope. Tests updated + two new cases added (positive:
Copilot dropped, humans preserved; negative: only exact full-name
match triggers the block — `Copilot Enthusiast` and `copilot`
both preserved). Prospective fix; the already-landed
`content/acknowledgements/8.2.0.md` (via #181's recovery
dispatch) is unaffected by this change and needs a separate
touch-up (hand-edit or re-dispatch) if the 8.2.0 page is to
lose the Copilot entry too.


- **What:** website-openemr's `gen-acknowledgements.php` picks up
  `Copilot` as a contributor name in the acknowledgements page.
  Concrete for 8.2.0's just-landed acknowledgements page
  (`content/acknowledgements/8.2.0.md`, via #181):
  ```
  - Copilot (16 commits)
  ```
  Copilot is not a person — it's the GitHub Copilot / VSCode
  Copilot commit author string that some contributors leave in
  their commit author field instead of their own name. Crediting
  it in the release acknowledgements is wrong on two axes: not a
  real contributor, and duplicates the actual human author who
  authored the same commits under a different name in other
  commits.

- **Related to but distinct from:** website-openemr#172 (which
  landed earlier — filters `[bot]` suffix from acknowledgements
  authors). That filter catches obvious bot accounts
  (`github-actions[bot]`, `dependabot[bot]`, `openemr-release-
  bot[bot]`, etc.) via the `[bot]` marker in author name. Copilot
  doesn't have a `[bot]` marker on GitHub — its commits appear
  under a bare `Copilot` name string — so the existing filter
  misses it.

- **Fix:** Add `Copilot` to the acknowledgements author blocklist
  in `gen-acknowledgements.php` (alongside the `[bot]` filter
  from #172). Consider making the blocklist a hand-curated list
  rather than pattern-only so future non-`[bot]` non-humans
  (LLM assistants, other IDE tools) can be added by name as
  they appear.

- **Recovery for 8.2.0:** The acknowledgements page landed via
  #181 already has the Copilot entry. Two options:
  1. Land the fix on gen-acknowledgements.php + re-dispatch
     `openemr-rel-update` for 8.2.0 → new PR (working around
     G23's upsert-pr-vs-merged issue via manual PR create) →
     merge.
  2. Hand-edit `content/acknowledgements/8.2.0.md` on master to
     drop the Copilot line (small one-line PR).
  Option 2 is expedient; option 1 also validates the filter fix
  end-to-end for future releases.

### G26 — `arduino/setup-task@v2` rate-limit failure on release announcements  *(discovered 2026-07-08, SHIPPED 2026-07-10)*

**STATUS: SHIPPED 2026-07-10** as openemr/openemr-devops#854
(6 workflows: build-patch, build-release, build-release-on-tag,
release-announcements, release-permissions-check, ship-release —
all bumped to v3 and wired with `repo-token: ${{ secrets.GITHUB_
TOKEN }}`) plus openemr/website-openemr#188 (release-docs.yml +
release-docs-ci.yml — bumped to v3; `repo-token` was already
wired on the website side so only the version bump was needed
there). Dependabot's #165 proposing the same website-openemr
version bump was superseded by #188 and auto-closed on merge.
Authenticated API calls now use the per-token 5000/hr budget
instead of the shared-IP anonymous 60/hr quota that failed the
v8_2_0 tag-push cascade. Also clears the Node.js 20 deprecation
warning that appeared on every v2 usage.


- **What:** openemr/openemr-devops's `Release Announcements
  (drafts)` workflow (fires on `openemr-tag` repository_dispatch,
  runs on tag-push cascades) uses
  `uses: arduino/setup-task@v2` to install the `task` CLI. The
  v2 action makes UNAUTHENTICATED GitHub API calls to download
  the Task binary release. Shared-IP runner pools can exhaust
  the anonymous 60/hr rate limit — resulting in job failure.

- **Concrete failure (2026-07-08):** Run 28973848333 fired from
  the v8_2_0 tag creation at 20:35Z. Failed at the `Install
  Task` step with:
  ```
  ##[error]API rate limit exceeded for 52.159.229.55. (But
  here's the good news: Authenticated requests get a higher
  rate limit...)
  ```
  Draft announcements for 8.2.0 were not rendered. Recoverable
  by workflow rerun (rate limits reset hourly), but the release
  cadence shouldn't depend on getting lucky with runner IP
  quota.

- **Fix:** Bump the action to `arduino/setup-task@v3` and pass
  a token so its API calls are authenticated (5000/hr per-token
  budget). v3 accepts a `repo-token` input for this purpose;
  the default `secrets.GITHUB_TOKEN` is sufficient.
  ```yaml
  - uses: arduino/setup-task@v3
    with:
      version: 3.x
      repo-token: ${{ secrets.GITHUB_TOKEN }}
  ```
  v3 is also Node.js 24 native — clears the "Node 20
  deprecation, forced to Node 24" warning that also appears in
  the failed run's log.

- **Related consumer:** website-openemr has the analogous
  `arduino/setup-task@v2` dependency; dependabot opened #165
  there (bump to v3) as of 2026-07-04. Same fix pattern applies
  — merge that + wire `repo-token`, and audit other repos
  (demo_farm_openemr, website-openemr-files) for the same
  action usage.

- **Trigger surface:** any release path that fires the release
  announcements workflow. Tag pushes, manual dispatches, and
  cascades from other workflows.

### G27 — Release announcement templates need content/style review  *(discovered 2026-07-09)*

- **What:** After G26's rerun of the Release Announcements
  (drafts) workflow succeeded on 2026-07-09, the rendered
  drafts revealed content/style issues across all channel
  templates. Templates live at
  `openemr-devops/tools/release/templates/announcements/`:
  - `chat.md.twig` (community/Slack/Discord)
  - `facebook.txt.twig`
  - `forum.md.twig`
  - `linkedin.txt.twig`
  - `mail.html.twig` + `mail.subject.txt.twig`
  - `x.txt.twig` (Twitter/X)
  - `step-summary.md.twig` (GitHub Actions job summary)

- **Fix:** Per-template review pass. Concrete gaps to enumerate
  as maintainer works through each template — capture the
  actual per-template diffs here once identified so this gap
  can be broken up into per-channel PRs.

- **Related:** G26 (rate-limit fix) is a workflow-plumbing gap;
  G27 is a content gap. Both surface on the same
  release-announcements pipeline.

### G28 — Master-side finalize PR requires a post-tag auto-update before merge; ordering isn't documented or enforced  *(discovered 2026-07-08, captured 2026-07-10, SHIPPED 2026-07-11)*

**Status: SHIPPED on master via openemr/openemr#12889 and on rel-820 via openemr/openemr#12891** (byte-identical patch-id parity after force-pushing a clean re-cherry-pick once #12892 landed G20 on rel-820 — the one-line divergence around G20's `--prev-release` wiring went away).

- **What:** The full "ship a release" sequence has two conductor
  PRs whose ordering is non-obvious:
  1. **Release-prep PR on the rel branch** (e.g., #12742
     — `chore(release): prep 8.2.0` targeting rel-820).
     Merging this triggers the annotated tag creation.
  2. **Finalize-on-master PR** (e.g., #12767 —
     `chore(release): finalize 8.2.0 on master` targeting
     master). Auto-opened by the conductor **during** the
     release-prep phase with preview content (release-targets.yml
     rotation as it will look post-ship). This PR must be
     **auto-updated one more time** by the conductor after the
     rel-branch PR merges + tag is created, so that its content
     reflects the actual just-shipped state (docker_tags slot
     shuffle, openemr_version_ref pinned to the just-created
     tag, etc.). **Only after that post-tag auto-update is the
     finalize PR safe to merge.**

- **Concrete 8.2.0 timeline:**
  - 20:33Z — #12742 (release-prep 8.2.0) merged into rel-820
  - 20:35Z — `v8_2_0` tag created by post-merge conductor
  - 20:37Z — #12767 (finalize on master) force-pushed by
    openemr-release-bot with the post-tag update
    (independently affected by G16, but that's the update
    event in question)
  - Only after 20:37 is #12767's content correct-shape to
    merge to master

- **Ordering hazard:** Nothing in the workflow surface
  currently signals "wait for the post-tag auto-update before
  merging this finalize PR." A maintainer looking at #12767
  during the release-prep window (before tag creation) sees a
  green PR with preview content and could merge it — landing
  the pre-shipping preview on master instead of the actual
  shipped state.

- **Fix options:**
  1. **UI signal on the finalize PR body:** conductor renders
     the PR body to include a state indicator — e.g.,
     `Status: WAITING_FOR_TAG` before rel-branch PR merges;
     `Status: READY_TO_MERGE` after post-tag auto-update.
     Simple, human-readable, doesn't block anything but makes
     the ordering explicit.
  2. **Merge gate via GitHub required check:** conductor
     writes a status check on the finalize PR that stays
     "pending" until the post-tag auto-update fires, then
     transitions to "success." Branch-protection rule requires
     this check → PR literally can't merge early. Stronger but
     needs branch-protection change.
  3. **Draft state:** open the finalize PR in `draft: true`
     during the release-prep phase, and flip to
     `ready-for-review` in the post-tag auto-update event.
     Matches existing conductor patterns (release-docs uses
     draft state as a maintainer-review gate) and requires
     no branch-protection change.

  **Preference: option 3.** Reuses an existing UX signal
  maintainers already understand ("draft = not ready");
  aligns with the release-docs draft-gate pattern; needs no
  branch-protection touch. Also serves as a natural indicator
  for G16-related recovery scenarios: if the post-tag update
  overshoots (as it did for 8.2.0), the draft state gives
  time to correct before the maintainer flips-and-merges.

- **Actual fix (2026-07-08, PR #12889):** Deeper investigation
  revealed the ordering hazard was compounded by a regression
  the G16 fix introduced: the Layer 2 gate (`$v_tag == '-dev'`)
  now correctly skips the prep job on the release-prep PR merge
  push, but the prep job re-firing on that push was what
  previously refreshed the release-finalize PR with post-tag
  content. Under the G16 fix that refresh never happens, so
  the release-finalize PR is permanently stuck with preview
  state and merging it would land the pre-shipping rel-branch
  tip ref on master instead of the just-created annotated tag.
  Fix: fold the master-scope mutator + peter-evans PR update
  work into the `finalize` job (which fires on the release-prep
  PR merge and creates the tag), so post-tag content lands on
  the partner PR by the time a maintainer sees it. Adds
  `gh pr ready` (peter-evans's `draft: false` doesn't unset
  existing draft state) + `gh pr comment` for the "ready to
  merge to master" maintainer signal. Combines option 3
  (draft-state UX) with fixing the underlying refresh
  regression.

- **Related:**
  - G16 (conductor overshoots to next patch version) — the
    post-tag auto-update this gap describes is exactly where
    G16's wrong-content surfaces.
  - G23 (`workflow:upsert-pr` doesn't distinguish open vs
    merged PRs) — same theme: silent
    conductor-vs-maintainer coordination issues in the
    release-prep / release-docs PR lifecycle.

## Timing picture: who does what, when

Consolidates "what the conductor handles automatically" vs "what's
manual" vs "when it happens in the release cycle."

| Work | Who | When |
|---|---|---|
| `version.php` strip `-dev` → `X.Y.Z` | Conductor (rel-scope) | **Release time** (in release-prep PR) |
| OpenAPI version bump | Conductor (rel-scope) | Release time |
| `swagger/openemr-api.yaml` regen | Conductor (rel-scope) | Release time |
| `docker/production/docker-compose.yml` image pin | Conductor (rel-scope) | Release time |
| **Docker upgrade machinery on rel branch** (3 docker-version flags + new `fsupgrade-N.sh` + Dockerfile two-block manifest) | **Manual** | **Release time** (must be in the image baked at tag creation; in or alongside the release-prep PR before it merges) |
| **Docker upgrade machinery on master** (same files, byte-equal copy of the new `fsupgrade-N.sh`) | **Manual cross-branch sync** | **Release time** (master's image needs the same fsupgrade chain entry for forward-upgrade compatibility from rel-branch image → master image) |
| **Next-cycle SQL skeleton on rel branch** (`sql/<just-released>-to-<next>_upgrade.sql`, blank with long header) | **Manual** (G3: `SqlUpgradeSkeletonMutator` is master-only + unwired today) | **Cut time** (start of next cycle, i.e., after this release ships, to prep for the next one) |
| **Next-cycle SQL skeleton on master + file-rename dance** | **Manual** | **Cut time** |
| **`version.php` advance to next-dev on rel branch** (e.g., `8.1.1` → `8.1.2-dev`) | **Manual** today (G2: no auto-bump mechanism) | **Cut time** (post-release, set the intent state for the next cycle) |
| **`version.php` advance on master + bridge SQL + OpenAPI bump + release-targets master-row bump** (when cutting a NEW rel branch from master) | **Manual** today (G4: no `--scope=master` workflow path) | **At new rel-branch cut time** (master-side, distinct from rel-branch per-release cut) |

## Docker Hub tag model (`dev`, `next`, `latest` are versions)

Per Brady (2026-06-22): the named tags `dev`, `next`, `latest` are
treated as **full versions** on the Docker Hub presence, not just
floating pointers — same first-class status as numbered tags like
`8.1.0`, `8.0.0.3`. So a `release-targets.yml` row's `docker_tags`
field typically pairs a version-numbered tag with its named-version
alias from the same release stream.

The current shape across rows reflects this:

| branch | docker_tags | meaning |
|---|---|---|
| master | `8.2.0,dev` | future version 8.2.0 == version-name `dev` (master's planned next) |
| rel-810 | `8.1.0,next` | currently-released 8.1.0 == version-name `next` (the next-stable line) |
| rel-800 | `8.0.0,8.0.0.3,latest` | currently-released 8.0.0 (+ specific patch 8.0.0.3) == version-name `latest` |
| rel-704 | `7.0.4` | older release, no version-name alias |

So pre-release docker_tags entries are NOT placeholders — they're the
"this is the version we're heading toward" declaration. The
version-numbered tag in a pre-release row publishes to Docker Hub
under that number AS IF the version were already shipped (just from
the in-progress branch content rather than a stable tag).

### Slot-promotion model

The three named version-slots represent a stable → upcoming → in-dev
hierarchy. At-most-one row holds each slot at any time:

| Slot | Semantic | Typical owner |
|---|---|---|
| `latest` | current production GA — what `docker pull openemr/openemr` gives consumers | the most recently released rel branch (e.g., rel-800 currently) |
| `next` | upcoming stable — the version preparing to ship next | the rel branch preparing the next release (e.g., rel-810 currently), OR master when master is preparing the next minor without a dedicated rel branch yet (e.g., master after rel-820 cut) |
| `dev` | active development — newest, master | always master |

When a rel branch ships its release, the slots shuffle:

- The branch that just shipped: `next` → `latest` in its row
  (promoted from upcoming to current-stable).
- The branch that previously held `latest`: drops `latest`
  (demoted — still publishes its version-numbered tags, just no
  longer the "current GA" alias).
- The `next` slot: moves to wherever the *next* upcoming stable
  lives. If a new rel branch has been cut for the next minor, it
  takes `next`. If no new rel branch exists yet, master acquires
  `next` alongside `dev` (master is then doubly tagged — `dev` for
  "actively developing" + `next` for "what's coming next").

Example shuffle when 8.1.1 ships from rel-810 (current planned state,
no rel-820 cut yet):

| branch | docker_tags pre | docker_tags post |
|---|---|---|
| master | `8.2.0,dev` | `8.2.0,dev,next` (acquired `next`) |
| rel-810 | `8.1.1,next` | `8.1.1,latest` (`next` → `latest`) |
| rel-800 | `8.0.0,8.0.0.3,latest` | `8.0.0,8.0.0.3` (lost `latest`) |
| rel-704 | `7.0.4` | `7.0.4` (out of rotation, unchanged) |

Future shuffle when rel-820 is cut from master and master moves to
8.3.0-dev (hypothetical):

| branch | docker_tags before cut | docker_tags after cut |
|---|---|---|
| master | `8.2.0,dev,next` | `8.3.0,dev` (lost `next` to rel-820) |
| rel-820 (new) | (didn't exist) | `8.2.0,next` (`next` acquired) |
| rel-810 | `8.1.1,latest` | `8.1.1,latest` (unchanged — still current GA) |

The slot moves are part of the manual PR work, NOT conductor-handled.

## `openemr_version_ref` pattern: branch tip vs tag pin

For a given row, `openemr_version_ref` flips between two shapes
across the release cycle:

- **Branch tip (`rel-810`, `master`):** daily orchestrator builds use
  the branch's HEAD content. Image content moves as commits land.
  Used for "currently developing this version" state.
- **Tag pin (`v8_1_0`, `v8_0_0_3`):** daily orchestrator builds use
  the immutable tag's content. Image content is locked. Used for
  "this version has been released, no more changes to this stream
  until next release."

A rel branch's row cycles through these states across a release:

1. **Stable** (just after vX.Y.Z shipped): `openemr_version_ref: vX_Y_Z`,
   `docker_tags: X.Y.Z,<name>`. Image locked to released content.
2. **Pre-release prep** (working toward X.Y.Z+1): `openemr_version_ref: rel-XYZ`,
   `docker_tags: X.Y.(Z+1),<name>` (both updated together — docker_tag
   gets the FUTURE version number AND ref switches to branch tip).
   Image moves as commits land on rel branch. Both tags
   `X.Y.(Z+1)` and `<name>` publish in-progress content.
3. **Released X.Y.Z+1**: `openemr_version_ref: vX_Y_(Z+1)`,
   `docker_tags: X.Y.(Z+1),<name>` (just the ref flips back to a tag;
   docker_tags stays). Image locked to the new released content.

Master is always at stage 2 (`openemr_version_ref: master`), since
master never gets stable-released as itself — its successor versions
get released from rel branches.

Two distinct "cut time" contexts conflated by the word "cut":

- **Per-release cut time on the rel branch** — start of the next
  release cycle on an existing rel branch (e.g., starting 8.1.2 work
  after 8.1.1 ships). SQL skeleton + version.php-to-next-dev work
  happens here.
- **New rel branch cut time on master** — actually cutting a new rel
  branch (e.g., cutting rel-820 from master). Master-side big bump
  (`version.php`, OpenAPI, release-targets master row, bridge SQL,
  etc. — see master-side branch-cut checklist above) happens here.

## Canonical 8.1.1 release sequence (apply pattern to subsequent releases)

The full PR sequence for releasing 8.1.1 from rel-810, end to end.
Most steps are manual today (see G2-G5 for which automations would
collapse this in the future). Numbered by ordering constraint.

### Pre-release (before pushing version.php bump on rel-810)

**P1. Docker upgrade machinery on rel-810** (PR against rel-810).
Increment all 3 docker-version files to 11 (`/docker-version`,
`/sites/default/docker-version`,
`/docker/release/upgrade/docker-version`). Add new
`docker/release/upgrade/fsupgrade-11.sh` (copy fsupgrade-10.sh +
~5 substitutions: `priorOpenemrVersion="8.1.0"`, header comment
"Upgrade number 11", "from prior version 8.1.0", two echo statements).
Add `upgrade/fsupgrade-11.sh \` to docker/release/Dockerfile lines
280-291 COPY block AND `/root/fsupgrade-11.sh` to lines 294-304
chmod block.

**P2. Docker upgrade machinery on master** (PR against master).
Same set of changes as P1 — `fsupgrade-11.sh` is byte-equal to
rel-810's copy; 3 docker-version files bump to 11; Dockerfile two
blocks updated. Cross-branch sync requirement (each branch's image
carries its own copy of the fsupgrade chain).

### Release trigger

**P3. Version bump on rel-810** (PR against rel-810). Bump
version.php from `8.1.0` (no `-dev`) → `8.1.1-dev` ($v_patch '0' →
'1', add `-dev` to $v_tag). This represents "rel-810 is now actively
preparing 8.1.1." When P3 merges, the push to rel-810 triggers
`release-prep.yml`:

- Conductor's `branch-to-version.php` resolves target = 8.1.1
  (looks at existing tags, sees v8_1_0, returns next patch)
- Conductor mutators (rel-scope) apply: VersionPhpMutator strips
  `-dev` → 8.1.1; OpenApiVersionMutator bumps to 8.1.1;
  SwaggerRegenMutator regenerates; DockerComposeProductionMutator
  pins; (SqlUpgradeSkeletonMutator is master-only — doesn't fire)
- peter-evans opens DRAFT `release-prep/rel-810` PR with the
  conductor mutations
- Dispatches `openemr-rel-cut` event (first run, or
  `openemr-rel-update` if not first)

**P4. release-targets master row → pre-release stage 2** (PR against
master). Bump rel-810 row: `docker_tags: 8.1.0,next → 8.1.1,next`
AND `openemr_version_ref: v8_1_0 → rel-810` together (validator
requires alignment). Daily orchestrator builds now publish rel-810
HEAD content to `openemr/openemr:8.1.1` and `openemr/openemr:next`.
Can land any time after rel-810's version.php is at 8.1.1-dev so
the validator's docker_tag↔version.php check passes (it reads
rel-810 HEAD's version.php for `openemr_version_ref: rel-810`).

### Ship

**P5. Merge the release-prep PR** (the one peter-evans opened in
P3). Two paths:

- **Recommended path: via `ship-release.yml`.** From any host with
  gh: `gh workflow run ship-release.yml --repo openemr/openemr-devops -f version=8.1.1 -f rel_branch=rel-810`.
  The orchestrator's preflight evaluates every unmerged target's
  readiness, then merges in strict order (today: Infra → Conductor
  → Docs; post-workstream-1: Conductor → Docs).
- **Direct path:** simply merging the conductor PR
  (`openemr:release-prep/rel-810`) in the GitHub UI fires the
  conductor's `finalize` job, which mints the tag and dispatches
  `openemr-tag` independently of ship-release.yml. Loses the
  multi-PR-merge-ordering safety net but works if ship-release.yml
  is itself blocked.

⚠️ **Pre-ship blocker discovered 2026-06-24:** the current Infra PR
slot (openemr-devops PR #760, `release-rotation/auto`) is frozen
with pre-docker-migration content from 2026-06-10. Its diff edits
paths now deleted on devops master:
`docker/openemr/8.0.0/Dockerfile`, `docker/openemr/current`,
`dependabot.yml` references to `docker/openemr/*` (all 404 on master
today). `release-rotation.yml` runs successfully on every dispatch
but skips force-push + PR-update because the diff against the
already-rotated branch is empty — PR #760 stays frozen indefinitely.
Shipping via `ship-release.yml` today would either (a) block on PR
#760's unmergeable state or (b) silently undo the docker migration
on merge. **Workstream 1 (migration doc, ~1 day) collapses
ship-release to a 2-PR shape that eliminates the Infra slot
entirely**, unblocking this ship path. After workstream 1, PR #760
becomes a dangling OPEN PR — close manually as cosmetic cleanup.
The "Direct path" above sidesteps the blocker but doesn't help if
the docs PR needs to merge first per the merge-order rules.

On merge: finalize job creates the annotated `v8_1_1` tag from
the merge commit, dispatches `openemr-tag` to openemr-devops +
website-openemr + demo_farm_openemr. `build-release-on-tag.yml`
(in openemr-devops) consumes the dispatch, runs build-release.yml:
preflight → version-bump safety net → changelog → compatibility →
package assemble (tarball + zip) → checksum → push tag (defensively
— conductor already did) → `gh release create --verify-tag` →
`gh release upload` (tarball + zip + checksums + changelog) →
changelog-pr (PRs to rel-810 + master with the rendered CHANGELOG).
`release-announcements.yml` (in openemr-devops) renders forum /
chat / X / Facebook / LinkedIn / mail drafts into a workflow
artifact.

### Post-release

**P6. release-targets master row → stage 3 + slot shuffle** (PR
against master, single coordinated edit across multiple rows).
Two distinct changes in one PR:

a. *rel-810 row ref pin*: `openemr_version_ref: rel-810 → v8_1_1`.
   Daily images now lock to v8_1_1's immutable content instead of
   moving with rel-810 HEAD.

b. *Slot shuffle* (`next` → `latest` promotion, see "Slot-promotion
   model" section above):

   | row | docker_tags before | docker_tags after |
   |---|---|---|
   | master | `8.2.0,dev` | `8.2.0,dev,next` |
   | rel-810 | `8.1.1,next` | `8.1.1,latest` |
   | rel-800 | `8.0.0,8.0.0.3,latest` | `8.0.0,8.0.0.3` |
   | rel-704 | `7.0.4` | `7.0.4` (no change) |

   `next` moves from rel-810 to master (because no rel-820 has been
   cut to take it). `latest` moves from rel-800 to rel-810 (rel-810
   is now the current GA). rel-800 keeps its version-numbered tags
   but drops `latest`.

**P7. Advance rel-810 to next-dev** (PR against rel-810). Bump
version.php from `8.1.1` → `8.1.2-dev` (G2 — no auto-bump
mechanism). Sets intent state for the next 8.1.x cycle.

**P8. Cut-time SQL skeleton for next cycle** (PR(s) — could be
combined with P7 on rel-810 side). Add blank
`sql/8_1_1-to-8_1_2_upgrade.sql` on rel-810 (long Comment Meta
Language Constructs header copied from prior file; body empty).
Add the same blank on master (cross-branch sync). On master, if a
next-minor bridge file exists (it doesn't currently — master is at
8.2.0-dev with `sql/8_1_1-to-8_2_0_upgrade.sql`), the file-rename
dance applies: `sql/8_1_1-to-8_2_0_upgrade.sql` →
`sql/8_1_2-to-8_2_0_upgrade.sql`. **Currently doesn't apply for the
8.1.1 → 8.1.2 transition** because master's existing bridge is
`8_1_1-to-8_2_0` and the new blank `8_1_1-to-8_1_2` would slot in
front of it — but that requires renaming `8_1_1-to-8_2_0` →
`8_1_2-to-8_2_0`. So actually it DOES apply here.
**TODO: verify the rename direction with maintainer when this step runs.**

**Note (2026-06-30):** P8's framing above is scoped to the canonical
8.1.1 release sequence (the SQL skeleton work happens here as part of
the same post-ship cleanup chain). The broader patch-cycle bootstrap
case — including this same SQL skeleton work on **both** rel + master
plus docker upgrade scaffolding plus the master release-targets row
insert — is now handled separately by **G12 / workstream 6
(patch-prep automation, PR #12697)**. Once workstream 6 ships, P8 is
absorbed into the patch-prep workflow's master-side PR for every
patch transition (8.1.1 → 8.1.2-dev triggers it, etc.). P8 stays in
the canonical sequence here for the manual baseline; consult G12 for
the automated end-state.

### Implied (ride-along on other PRs)

- The `release-prep/rel-810` PR opened in P3 contains the conductor's
  mutations: version.php strip-dev, OpenAPI bump to 8.1.1, swagger
  regen, docker/production/docker-compose.yml pin to 8.1.1.
- CHANGELOG.md PRs (auto-opened in P5 by build-release-on-tag) land
  on both rel-810 and master after merge — maintainer reviews +
  merges these separately.
- website-openemr opens its own `release-docs/<version>` PR on the
  rel-cut/rel-update dispatch (in P3); ship-release.yml (manual)
  coordinates merging the 3-PR contract once the rel-810 prep PR is
  ready to merge.

### Ordering constraints summary

- P1, P2 can be parallel (different branches).
- P4 must follow P3 (so rel-810's version.php is at 8.1.1-dev for
  validator alignment).
- P5 must follow both P3 and P4 (release-prep PR can't merge until
  the docker-upgrade-machinery + version.php bump are all in place).
- **P5 also blocked on workstream 1 of the release-mechanism migration
  doc** (delete rotation slice + collapse ship-release to 2-PR shape)
  — see the ⚠️ pre-ship blocker note above. Workstream 1 must land
  before P5's `ship-release.yml` path will work cleanly.
- P6, P7, P8 all post-P5.
- P8's rename dance on master only matters if a next-minor bridge
  file is present on master at that time (currently is).

## Things to verify during 8.1.1 manual prep

(Grows as the manual work uncovers gaps.)

- [ ] Inspect rel-810's current `version.php`: what's `$v_tag`?
- [ ] Inspect rel-810's `library/globals.inc.php`: does it need manual
      edits before 8.1.1 push?
- [ ] Confirm `sql/8_1_0-to-8_1_1_upgrade.sql` exists on rel-810 with
      8.1.1's actual SQL upgrade content (NOT a blank skeleton — this
      file is what runs when consumers upgrade TO 8.1.1).
      `SqlUpgradeSkeletonMutator` won't create or modify it (master-only
      + unwired per G3, and it scaffolds the NEXT-cycle blank, not the
      current-cycle populated file). Manual responsibility either way.
- [ ] Test-mode end-to-end first:
      `gh workflow run release-prep.yml --ref rel-810 -f target-version=8.1.1 -f branch=rel-810 -f test=true`
      and inspect what artifacts come out, what the test-tag looks like,
      what the test PR contains.
- [ ] Verify docker auto-upgrade refs (the `DockerComposeProductionMutator`
      target — `docker/production/docker-compose.yml`) look right after a
      test-mode run.
- [ ] After successful test run: do the same dispatch without `test=true`
      for the real 8.1.1 release, OR push to rel-810 and let the push
      trigger fire it.

## Manual procedure: master-side actions when cutting a new rel branch

**Status:** fully manual today. Surfaces gaps G4 (no `--scope=master`
workflow path) + G5 (no auto-trigger on branch creation). Capture here
so the steps are documented for when the next rel branch is cut from
master.

**Trigger event:** cutting a new `rel-NNN0` branch from master
(e.g., `git push origin master:rel-820`). The new rel branch inherits
master's tree verbatim; master's own version state then needs to
advance to represent "the next planned cut."

**Steps on master, in order** (open one PR with all of them):

1. **`version.php`** — bump `$v_minor` by 1, reset `$v_patch` to `'0'`,
   keep `$v_tag = '-dev'`. Example: `8.1.1-dev` → `8.2.0-dev`.
   - `$v_major` only bumps for major-version transitions (rare; e.g.,
     7.0.4 → 8.0.0); not in scope here.
   - `$v_realpatch` stays at `'0'` (only used by the patch-flow path).

2. **`src/RestControllers/OpenApi/OpenApiDefinitions.php`** — update
   the `version: 'X.Y.Z'` value in the `#[OA\Info(...)]` attribute
   (line 17 as of 2026-06-22) to match. Format probably matches
   `<MAJOR>.<MINOR>.<PATCH>` without the `-dev` suffix (Swagger/OpenAPI
   conventions don't typically carry `-dev`). Example: bump to `'8.2.0'`.

3. **Regenerate `swagger/openemr-api.yaml`** from
   `OpenApiDefinitions.php`. The conductor's `SwaggerRegenMutator`
   normally does this, but on master-side manual cut it has to be
   triggered by hand. (TODO: find the build command — likely a `composer`
   script or `php bin/...` invocation. Inspect `composer.json` scripts
   or look at how `SwaggerRegenMutator` does it internally.)

4. **Bridge SQL upgrade script** at `/sql/` — create a blank skeleton
   for the transition between the last released minor and the new dev
   minor. Naming convention (per existing files like
   `sql/8_0_0-to-8_1_0_upgrade.sql`): `<from>_to_<to>_upgrade.sql`.
   Example: when bumping master to 8.2.0-dev after rel-810 has shipped
   8.1.x, the new file is `sql/8_1_1-to-8_2_0_upgrade.sql` (or
   whichever 8.1.x was the last released).
   - **Header content:** copy the long Comment Meta Language Constructs
     header (`#IfNotTable`, `#IfTable`, `#IfColumn`, etc.) verbatim
     from the most recent prior upgrade file (e.g.,
     `sql/8_1_0-to-8_1_1_upgrade.sql`). The header is the same across
     every upgrade file — copy-paste-friendly.
   - The blank file is just header + (no SQL statements yet); SQL
     content gets added as features land on master targeting that
     next minor.
   - **See the per-release-on-rel-branch section below** for what
     happens to this file when subsequent rel-branch releases later
     insert intermediate minors (the "weird" cross-branch dance that
     can require renaming this master file).

5. **`.github/release-targets.yml` master row** — bump `docker_tags`
   to the new dev minor. Current row (as of 2026-06-22):
   ```yaml
   - branch: master
     docker_tags: 8.1.1,dev
     openemr_version_ref: master
   ```
   Becomes (after master moves to 8.2.0-dev):
   ```yaml
   - branch: master
     docker_tags: 8.2.0,dev
     openemr_version_ref: master
   ```
   `openemr_version_ref: master` stays — it always tracks master HEAD.
   `docker_tags`'s first value mirrors `$v_major.$v_minor.$v_patch`
   from `version.php`; `dev` floating tag stays.
   - The `docker-validate-release-targets.yml` PR check enforces this
     alignment, so a mismatch fails the PR — good safety net.

6. **Add the new rel branch's row** to `.github/release-targets.yml`
   (separate from the master bump but typically in the same PR).
   See planning doc's "Branch-cut process under the final model" for
   the rel-side configuration.

7. **Docker upgrade machinery** (3 files + a new script + Dockerfile
   edit) — only required if the new minor introduces docker-level
   upgrade actions (new sites/* directories, permission fixes,
   removed dependencies, etc.). Skip the bump if the new minor needs
   no docker-level changes from the prior baseline:
   - **Increment 3 docker-version flag files**, all currently at the
     same integer (e.g., `10` as of 2026-06-22):
     - `/docker-version` (repo root; codebase-side flag #1)
     - `/sites/default/docker-version` (codebase-side flag #2)
     - `/docker/release/upgrade/docker-version` (docker-side flag,
       becomes `/root/docker-version` inside container)
   - **Add a new `docker/release/upgrade/fsupgrade-<N>.sh` script**
     with the next sequential number `<N> = previous + 1`. Almost
     always a copy of the most recent prior fsupgrade with ~5
     substitutions of `<N>` and the prior-version reference (see the
     per-release-on-rel-branch section below for the substitution
     details). Body changes only when the new minor adds extra
     filesystem actions (new directories, file moves, etc.).
   - **Add the new script to `docker/release/Dockerfile`'s manifest
     in TWO PLACES** (easy to update one and forget the other --
     known footgun):
     - **COPY block (lines 280-291 currently):** add a new line
       `upgrade/fsupgrade-<N>.sh \` to the `COPY upgrade/docker-version + upgrade/fsupgrade-*.sh /root/` step
     - **chmod block (lines 294-304 currently):** add a new line
       `/root/fsupgrade-<N>.sh` to the `RUN chmod 500 /root/fsupgrade-*.sh`
       step (in-container path, no trailing backslash on the last line)
     - The `docker-version` file is in the COPY block but NOT the
       chmod block (it's a data file, not executable). Its content
       change auto-propagates via the existing COPY line; no
       Dockerfile edit needed for the bump itself.
   - Detection logic (already in place, no edit needed):
     `docker/binary/openemr.sh:495-506` compares
     `/root/docker-version` vs codebase's `${OE_ROOT}/docker-version`
     vs `${OE_ROOT}/sites/default/docker-version`; if root > sites and
     root == code, runs the bridge fsupgrade scripts.

**Validation before merging the master-bump PR:**

- `docker-validate-release-targets.yml` runs on PR; verify it passes
  (alignment check between version.php, release-targets.yml, and the
  Dockerfile).
- Spot-check swagger/openemr-api.yaml diff matches what you'd expect
  from the version bump (just version field, not other content).
- Spot-check the SQL skeleton file is non-empty and follows the prior
  bridge file's structure.

## Manual procedure: per-release-on-rel-branch (docker upgrade portion)

**Status:** the version.php / SQL / swagger / release-targets-bump
work is handled by the conductor automatically when releasing from a
rel branch (per the walkthrough in this gaps doc's "Quick context"
section + the conductor's mutators). The **docker upgrade machinery**
(3 flag files + a new fsupgrade script + a Dockerfile manifest line),
however, is NOT touched by the conductor. The same machinery that
master initializes at branch-cut time also needs to be **incremented
on each release from a rel branch IF the release introduces
docker-level upgrade actions.**

**Note on the first release after a cut** (e.g., 8.1.1 from rel-810
where 8.1.0 was artifact-only): the cut-time prep already established
the docker upgrade flags at their initial state, so the FIRST release
from the branch doesn't strictly need this work — unless the new
release itself needs new docker-level upgrade actions over the
established baseline. Subsequent releases from the same rel branch
need it whenever upgrade actions change.

**Per-release-on-rel-branch docker upgrade steps** (in the release PR
on the rel branch, alongside any conductor-generated mutations).
**ALWAYS REQUIRED on every new docker version release** — this is
the auto-upgrade feature consumers depend on:

- Increment all 3 docker-version files together (`/docker-version`,
  `/sites/default/docker-version`, `/docker/release/upgrade/docker-version`).
- Add new `docker/release/upgrade/fsupgrade-<N>.sh` with the
  incremented number. **The script is essentially a copy of the
  most recent prior fsupgrade with ~5 substitutions:**
  - `# Upgrade number <N>` (header comment)
  - `# From prior version <X.Y.Z>` (header comment — the version
    being upgraded FROM, i.e., the most recently released prior
    version)
  - `priorOpenemrVersion="<X.Y.Z>"` (variable; sets
    `$form_old_version` for `sql_upgrade.php`'s chain walker)
  - `echo "Start: Upgrade to docker-version <N>"`
  - `echo "Completed: Upgrade to docker-version <N>"`

  Body is otherwise identical: ensure sites/* directories, clear
  smarty cache, fix permissions, run `sql_upgrade.php` per site
  with `$form_old_version` set from `priorOpenemrVersion`. The SQL
  upgrade chain in `sql_upgrade.php` walks forward from
  `$form_old_version`, so consumers upgrading across multiple
  docker-version steps (e.g., from docker-version 9 → 11) still
  get the full SQL upgrade chain applied sequentially.
- Add the new script's path to `docker/release/Dockerfile` in
  **both** the COPY block and the chmod block (see master-side
  step 7 above for the exact line locations + the easy-to-forget
  footgun).

**SQL upgrade script work** (parallel to docker upgrade machinery
but distinct — also always required per release, also needs the
cross-branch dance):

- **On the rel branch (e.g. rel-810):** add a new blank
  `sql/<JUST-RELEASED>-to-<NEXT-MINOR-OR-PATCH>_upgrade.sql` to
  prepare for the next release-prep cycle. Header is the long Comment
  Meta Language Constructs block copied verbatim from the most recent
  prior file; body starts empty (gets populated as fixes/migrations
  land between releases).

- **On master: the "weird" file-rename dance.** Master typically
  carries a "next-minor" bridge SQL file representing master's
  planned next release (e.g., if master is at 8.2.0-dev, it has
  something like `sql/8_1_1-to-8_2_0_upgrade.sql` as its placeholder
  for the eventual 8.2.0 release). When a rel branch releases an
  *intermediate* minor that didn't exist before, master needs to
  weave the new minor INTO its existing chain:

  1. Create a blank `sql/<JUST-RELEASED-PRIOR>-to-<JUST-RELEASED>_upgrade.sql`
     on master (same blank rel branch just created — needs to land
     on master too for chain integrity).
  2. **Rename master's existing next-minor bridge file** to reflect
     the new "from" version. E.g., if master had
     `sql/8_1_1-to-8_2_0_upgrade.sql` and rel-810 just released 8.1.2,
     master's existing file gets renamed to
     `sql/8_1_2-to-8_2_0_upgrade.sql`. The new blank file
     `sql/8_1_1-to-8_1_2_upgrade.sql` then sits "in front of" master's
     renamed file in the chain (chain walks 8.1.1 → 8.1.2 → 8.2.0
     via two files instead of one).

  **Note:** if no master next-minor bridge file exists yet (master
  hasn't been advanced past the previous minor — current state as of
  2026-06-22 with master at 8.1.1-dev), the rename half is skipped;
  just add the blank `sql/8_1_1-to-8_1_2_upgrade.sql` on master.

**Cross-branch propagation requirement** (load-bearing):

If a rel-branch release adds a new `fsupgrade-<N>.sh` script (or
bumps the docker-version files), the **same change must also land on
master** (and ideally any other live rel branch). Reason: each
branch's docker image carries its own copy of the fsupgrade chain
+ docker-version. Users upgrading from an older image to a newer
image rely on the receiving image to have every fsupgrade script
between the two docker-version values. If rel-810 ships
fsupgrade-11.sh but master's image still tops out at
fsupgrade-10.sh, a user upgrading from rel-810's 8.1.1 image to
master's image misses the 10→11 upgrade and the upgrade detection
short-circuits.

So the actual work surface for a docker-action-needing rel-branch
release is:

- On the rel branch (e.g. rel-810): 3 docker-version files bumped,
  new fsupgrade-N.sh added, Dockerfile two-block update.
- On master: same 3 docker-version files bumped, same fsupgrade-N.sh
  added (byte-identical), same Dockerfile two-block update.

These files are intentionally **not** in the byte-identical FILES_ALL
canary set (since version.php, OpenApiDefinitions, etc. legitimately
differ between master and rel branches by design). So the canary
doesn't catch a drift here. Cross-branch propagation is a manual
discipline — easy to forget.

**Open process question — when in the release cycle to do this work?**

The work is always required per docker version (see above), so the
question isn't "if" but "when." Three reasonable timings:

- **Right after each rel-branch release ships**: bump to N+1 + add
  empty fsupgrade-(N+1).sh on both branches immediately. Pro: state
  stays "ready to receive next release's upgrade actions" continuously;
  any commit that lands a docker-level change has a clear target file
  to populate. Con: bumps the docker-version flag before a next
  release is concretely planned.
- **At next release-prep time**: bump just before shipping the next
  release. Pro: timing aligns with other release-prep work (single
  PR coordinates everything). Con: easier to forget the cross-branch
  master sync under release-time pressure; any docker-level changes
  merged between prior release and this prep have no target file
  until the bump happens.
- **At new-rel-branch-cut time** (only relevant when cutting from
  master, e.g., rel-820 from master): the master-side cut already
  bumps everything; this is the natural moment for master's docker
  upgrade machinery to advance for the next minor's planning window.

In practice the three timings combine: the rel-branch's per-release
work happens at the release's own cadence; master's bumps need to
shadow rel-branch bumps for cross-branch consistency; new branch cuts
add their own bump moment on master.

Not resolving the precise rhythm now — left as an open question for
when the release-mechanism migration completes and the team has
experience with a few real release cycles. Whatever cadence gets
settled on should be documented in `RELEASE_PROCESS.md`.

**For the upcoming 8.1.1 release from rel-810** (where 8.1.0 was
artifact-only): the docker-version is currently `10` on rel-810 and
master (both already in sync), matching the most recent
fsupgrade-10.sh on both branches. 8.1.1 will need the +1 bump and
new fsupgrade-11.sh on **both** rel-810 AND master per the steps
above (since docker upgrade actions are always done per docker
version release — this is how consumers get the auto-upgrade
behavior).

**Snapshot of currently-stale state (2026-06-22, pre-rel-820-cut):**

| File | Current value | Drift from expected |
|---|---|---|
| `version.php` | 8.1.1-dev | OK if 8.1.1 is the next planned master release; **but** rel-810 will also ship 8.1.1 — semantically conflicts. Probably should have been bumped to 8.2.0-dev when rel-810 was cut (missed manual step). |
| `OpenApiDefinitions.php` | 8.0.1 | Stale, two minor versions behind. Worth fixing during the rel-820 cut (or earlier). |
| `release-targets.yml` master row `docker_tags` | 8.1.1,dev | Matches version.php; would conflict with rel-810 if rel-810 also publishes 8.1.1. |

When the rel-820 cut work happens, address all three at once via this
checklist + the existing master-bump pattern.

## Decisions made (don't re-litigate)

- **Patch mechanism (`build-patch.yml`)** stays as emergent-patch escape
  hatch (zero-day hotfixes), not part of main release flow. Migrates with
  the rest of the release tooling but stays inert (no `openemr-tag`
  dispatch, no downstream wiring).
- **Byte-identity for release tooling** NOT enforced at this time. Drift
  between branches' release tooling is tolerated. Each rel branch's
  releases are produced by whatever release tooling the branch carries
  (frozen at cut time + manual backports). Revisit only if drift becomes
  a real headache.
- **Multi-row-per-branch IS supported** in `release-targets.yml`
  (decision reversed 2026-06-27 — see update log). Original framing
  was "one active version per rel branch" — older patches get
  superseded rather than maintained in parallel. Realized that
  doesn't work for the in-dev patch release case: when a rel branch
  enters dev mode for a new patch (e.g., rel-810 → 8.1.2-dev), the
  daily orchestrator still needs to publish the prior stable image
  (8.1.1) until the new dev actually ships. One row per branch
  can't express this — the row only targets one version at a time.
  Reversal landed via openemr/openemr#12656: validator's
  branch-uniqueness check (was check 3) removed; duplicate-
  docker_tag check (now check 3) retained because two rows pushing
  the same image:tag would race on Docker Hub. New optional
  `unreleased: true` row marker lets consumers skip placeholder
  rows while the validator still applies per-row checks for
  ready-to-flip-live confidence.

## Update log

- **2026-06-22**: Initial gaps doc created during release-mechanism
  orientation work. Captured G1-G5 plus the patch + byte-identity
  decisions. Pre-migration, pre-8.1.1.
- **2026-06-22**: Added master-side branch-cut manual procedure +
  snapshot of currently-stale state on master (version.php at
  8.1.1-dev, OpenApiDefinitions at 8.0.1, release-targets master row
  out of sync with rel-810's planned 8.1.1 release).
- **2026-06-22**: Added docker upgrade machinery (3 docker-version
  flags + fsupgrade script + Dockerfile manifest) to both the
  master-side cut checklist (step 7) and a new "Per-release-on-rel-
  branch" checklist for the recurring case. Identified the 3 flag
  file paths + detection logic in `docker/binary/openemr.sh`.
- **2026-06-22**: Corrected Dockerfile reference to note **two**
  blocks (COPY + chmod) that both enumerate fsupgrade scripts; both
  need updating per new script (easy-to-forget footgun). Added the
  cross-branch propagation requirement (rel-branch fsupgrade
  additions must also land on master, since each branch's image
  carries its own copy). Captured the open process question about
  WHEN to do this work (right-after-release vs at-next-prep, both
  with trade-offs).
- **2026-06-22**: Per Brady, clarified that docker upgrade actions
  are MANDATORY per docker version release (not conditional) — this
  IS the consumer auto-upgrade feature. Removed the "if no actions
  needed → skip" branch from the per-release procedure.
- **2026-06-22**: Per Brady, noted that new `fsupgrade-<N>.sh`
  scripts are essentially copies of the prior one with ~5
  substitutions of `<N>` and the prior-version reference. Body
  changes only when the new release adds extra filesystem actions.
  Documented the exact substitution list (header comment ×2,
  priorOpenemrVersion variable, two echo statements).
- **2026-06-22**: Captured the SQL upgrade script pattern: the long
  Comment Meta Language Constructs header is copied verbatim from
  the most recent prior file; new blank file added on the rel branch
  AND propagated to master per release. On master, the "weird thing"
  is the file-rename dance: if master has a next-minor bridge file
  (e.g., `8_1_1-to-8_2_0_upgrade.sql`) and a rel branch ships an
  intermediate minor (8.1.2), master adds the new blank
  `8_1_1-to-8_1_2_upgrade.sql` AND renames its existing bridge to
  `8_1_2-to-8_2_0_upgrade.sql` (the chain now goes via two files).
  Skipped when master has no next-minor bridge yet (current state
  as of 2026-06-22).
- **2026-06-22**: G3 investigated. `SqlUpgradeSkeletonMutator` is
  master-only (not in the `--scope=rel` mutator set) AND currently
  unwired (no workflow invokes `--scope=master`, per G4). So the SQL
  skeleton work is entirely manual today — corrected an earlier doc
  assumption that the rel-side conductor would handle it.
- **2026-06-22**: Added consolidated "Timing picture" section
  mapping each work item to (conductor vs manual) × (release time vs
  cut time). Distinguishes the two "cut time" contexts: per-release
  cut on an existing rel branch (SQL skeleton + next-dev bump) vs
  new-rel-branch cut on master (the big master-side checklist).
- **2026-06-22**: Per Brady, captured the "dev / next / latest are
  versions" Docker Hub model — those names are full versions on the
  Docker Hub presence, not just floating pointers. Documented the
  `openemr_version_ref` branch-tip-vs-tag-pin pattern that cycles
  per release. Added canonical 8.1.1 release sequence (P1-P8)
  capturing the full PR flow end-to-end including the two-stage
  release-targets master row updates (pre-release stage 2:
  docker_tags bump + ref to branch tip; post-release stage 3: ref
  to immutable tag).
- **2026-06-22**: Per Brady, extended the Docker Hub model with the
  slot-promotion pattern: `latest` / `next` / `dev` are at-most-one
  slots that shuffle on every release. When a rel branch ships:
  the branch promotes `next` → `latest` in its docker_tags; the
  previous `latest` holder drops it; `next` moves to the next
  upcoming-stable owner (a new rel branch if one exists, else
  master alongside `dev`). Refined P6 to capture the full
  multi-row shuffle (not just the ref pin switch).
- **2026-06-23**: Added G6 — demo_farm_openemr's `ip_map_branch.txt`
  production rows + `demoLibrary.source` flex-image mappings are
  manually maintained, no cross-repo sync from openemr/openemr's
  `release-targets.yml` + per-branch Dockerfile combos. Surfaced
  via concrete drift on cluster `five` (carried `flex-3.23-php-8.5`
  while pointing at v8_0_0_3, which is built on Alpine 3.22 + PHP
  8.4 per rel-800's Dockerfile). Closed by demo_farm_openemr#134.
  Captured the auto-generation vision (the recent flex-docker
  reorg was preparation for it) + manual procedural rule until
  automation lands.
- **2026-06-23**: Per maintainer, expanded G6's auto-generation
  vision to the full file. Goal is to derive the entire
  `ip_map_branch.txt` + `demoLibrary.source` from upstream
  openemr/openemr state, with a single hand-curated
  "Miscellaneous" section reserved for non-standard demos. Each
  existing section's derivation rule documented: production
  from the `latest`-tag-holder rel branch; up-for-grabs defaults
  to master; release demos from all rel branches in
  release-targets.yml; master demos one-per-supported-PHP-version
  from CI matrix; parked is a managed bench. Single bot script
  with multiple triggers (`openemr-tag`,
  `openemr-rel-cut`/`-update`, scheduled, manual dispatch). Bot
  never touches the misc section.
- **2026-06-23**: Decision locked — no multi-row-per-branch in
  `release-targets.yml`. Considered briefly (would support parallel
  version-line publishing from a single rel branch); deliberately
  not pursuing to keep the model simple. Recorded in decisions-made.
- **2026-06-27**: Reversed the no-multi-row decision via
  openemr/openemr#12656. The in-dev patch release case requires it:
  when a rel branch enters dev mode for a new patch (e.g., rel-810
  hits 8.1.2-dev after 8.1.1 ships), daily builds still need to
  publish the prior stable image (8.1.1) until the new dev ships.
  PR drops the validator's branch-uniqueness check, adds an
  optional `unreleased: true` row marker for placeholders (consumer-
  side skip), wires the orchestrator's skip filter, and adds an
  example unreleased row (second rel-810 entry with
  docker_tags: 8.1.0 + openemr_version_ref: v8_1_0 +
  unreleased: true) to wire up the mechanism without immediately
  publishing 8.1.0 daily images.
- **2026-06-30**: Added G12 — Patch-cycle bootstrap on rel-* requires
  manual SQL skeleton + docker scaffolding + master file-rename.
  Captures the broader scope of the cut-time-only P8 work in the
  canonical 8.1.1 sequence: any rel branch's `$v_patch` bump (between
  ships) needs coordinated mechanical edits across rel + master.
  Workstream 6 (PR #12697 in-flight, built on PR #12696's branch)
  introduces `.github/workflows/patch-prep-automation.yml` triggered
  on `push` to `rel-*` matching `paths: version.php` with resolver
  gating on actual `$v_patch` increment + `$v_tag == '-dev'`. Two
  coordinated ready-for-review PRs (mirroring workstream 2's
  branch-cut + workstream 3's release-finalize pattern). Mutator
  reuse from `DockerUpgradeScaffoldMutator` +
  `SqlUpgradeSkeletonMutator` (extended via new
  `MutatorContext::$fromVersion` field — back-compat) plus 2 new
  mutators (`MasterSqlPatchBridgeMutator` +
  `PatchPrepReleaseTargetsMutator`). Together with workstreams 2
  (cut) + 3 (ship), this closes the full release-lifecycle
  automation surface. Cross-referenced from P8 in the canonical
  sequence.
- **2026-07-05**: G13 discovered + closed same session during 8.2.0
  dispatch shake-out. `derive-prev-release` for target 8.2.0 was
  returning 8.1.0 — a tag that exists (annotated v8_1_0) but a
  release that was SKIPPED (not in
  website-openemr/data/releases.json). Root cause: resolver walked
  tags without consulting the shipped-versions manifest. Landed
  manifest-aware behavior in openemr/openemr#12769 (master) + #12770
  (rel-820 backport), with follow-up #12771/#12772 fixing a secondary
  bug (annotated-tag filter dropping historic lightweight tags like
  v8_0_0 → walk-then-filter produced null → synthesized prev-minor
  fallback returned 8.1.0 anyway). Also clarified permanently that
  `release-targets.yml`'s `unreleased: true` marker is TRANSIENT
  (clears once next release supersedes the placeholder) — any code
  needing a durable "was-this-released" signal should consult
  data/releases.json instead.
- **2026-07-05**: G14 discovered + closed same session. Byproduct of
  validating G13's fix. Even with `prev_release=8.0.0` correctly
  computed, openemr/website-openemr#164 stayed CONFLICTING because
  the docs branch had been committing on top of the previous branch
  state each dispatch and history diverged from master indefinitely.
  Three cascading PRs on openemr/website-openemr: #174 (initial
  reset-manifest-from-master, later subsumed), #175 (rebase-per-
  dispatch structural fix), and #176 (restore branch-ref fetch so
  `--force-with-lease` baseline works). Post-merge dispatch produces
  a single-commit docs branch rooted at master's current HEAD; PR #164
  becomes MERGEABLE with a clean single-hunk diff (adds 8.2.0 DRAFT
  entry only).
- **2026-07-05**: End-to-end verification of the release-prep
  `workflow_dispatch` path against rel-820. Full chain fired
  cleanly: release-prep on rel-820 → derive-prev-release with merged
  fixes returns `8.0.0` → dispatches openemr-rel-update to all three
  consumers → website-openemr's release-docs.yml regenerates PR #164
  against the correct 0.0.0 → 8.2.0 window (930 PRs from
  v8_0_0..HEAD = 1508 total commits less #173's bot-filter drops).
  The `workflow_dispatch` inputs to watch: `--ref rel-820` +
  `--field force-dispatch=true` are both required; without either
  the workflow rejects or short-circuits. Confirmed the pattern is
  safe (no release-side effects — `finalize` job is gated on
  pull_request.merged, unreachable from workflow_dispatch).
- **2026-07-05**: openemr/website-openemr release-docs consumer had
  a parallel workstream landed same day: 8.0.0-download-page cleanup
  (#167), /releases/ Checksums column + backfill (#168), dispatch-
  managed URL derivation for /downloads/ (#169), version-agnostic
  install/upgrade wiki defaults (#170), drop per-release install/
  upgrade page generation (#171), filter [bot] acknowledgements
  (#172), release-notes bot filter hybrid (#173 — keeps composer/npm
  dependabot bumps per #136 intent, drops docker-image bumps
  identified by openemr/openemr's dependabot.yml groups + path
  patterns). Recorded here for cross-reference; ancillary to the
  release-mechanism migration but relevant for the "what does the
  docs PR contain now" surface area.
- **2026-07-08 to 2026-07-09**: 8.2.0 shipped end-to-end. First real
  production use of the migrated release mechanism for a public
  release (rel-820 → v8_2_0 tag → docker fanout → website updates
  → docker hub README push). Ship was blocked or degraded by six
  new gaps discovered live during the run:
  - **G16** (conductor overshoots to next patch): #12767 mis-titled
    "finalize 8.2.1", release-prep #12843 opened for 8.2.1, website
    #179 opened for 8.2.1 release-docs. All three closed or
    corrected manually. Root cause is one conductor bug; single fix
    eliminates all downstream symptoms.
  - **G17** (redundant `push: tags` trigger on
    docker-build-release): caused a benign double-build of
    `openemr/openemr:8.2.0` (tag-push + orchestrator-fanout).
    Content identical; no correctness impact, but two paths to the
    same output complicate future debugging.
  - **G18** (no `concurrency:` group on docker-build-release):
    surfaced by G17's double-build but a latent race for any two
    concurrent invocations against the same branch.
  - **G19** (raw.githubusercontent.com 429 + misleading error):
    rel-820 fanout failed with `##[error]The ref likely doesn't
    exist`. Actual cause was HTTP 429 rate limit from the fanout's
    four parallel version.php fetches (plus prior validate-loop
    fetches). Manual rerun ~20 min later succeeded, unblocking
    `latest` promotion to 8.2.0.
  - **G20** (openemr-tag missing prev_release): 8.2.0's FINAL
    release-docs regeneration skipped acknowledgements + release-
    notes generation because prev_release was empty on the
    openemr-tag dispatch payload. Recovered manually via a
    `workflow_dispatch` on release-docs.yml supplying
    prev_release=8.0.0.
  - **G21** (no layout links to acknowledgements/release-notes):
    even after G20 is fixed, no /releases/ or /downloads/ layout
    links to these per-version pages. Users can't discover them
    via normal navigation.
  Also affirmed as by-design (not gaps): manual maintainer DRAFT-
  flip on release-docs PRs (intentional review gate); release-docs
  PRs going stale across subsequent release cycles (a one-off from
  the 8.1.0 prerelease test — pattern is to close superseded /
  aborted release-docs PRs).
  End state (2026-07-09): 8.2.0 is fully shipped and consistent
  across openemr/openemr (v8_2_0 tag + release assets +
  CHANGELOG.md landed on rel-820 via #12844 and master via #12845),
  Docker Hub (`openemr/openemr:8.2.0` + `latest`; `next` promoted
  to master's 8.3.0-dev; Docker Hub README pushed reflecting new
  slot state), website-openemr (`data/releases.json` has 8.2.0
  FINAL entry via #164), and demo_farm_openemr (via #169). The
  changelog is a repo-only doc; the website does not surface
  `CHANGELOG.md` (repo diff of relevant Hugo layouts contains no
  changelog references).
- **2026-07-09**: G20/G21 refined + G22 added after design
  discussion on release-notes surface trim. Two triggers:
  - Manual `workflow_dispatch` on `release-docs.yml` supplying
    `event=openemr-tag` + `prev_release=8.0.0` STILL skipped
    acknowledgements + release-notes generation. Root cause is
    deeper than "openemr-tag emitter omits prev_release" — the
    website-openemr side actively STRIPS `prev_release` at the
    envelope layer: `dispatch.schema.json` doesn't list it in the
    openemr-tag event's properties, and `inputs-to-envelope.jq`
    doesn't pass it through for that event. Three-place fix
    required (schema + jq + openemr-side emitter).
  - GitHub Release body for `v8_2_0` measured at 794 lines with
    ~224 (~28%) dependabot/`[bot]`/`bump` noise, including many
    consecutive `openemr-images group across 1 directory` bumps
    that #173 was designed to filter out — but the filter only
    applies to the website's `gen-release-notes.php`, not to the
    GitHub Release body or the repo's `CHANGELOG.md`.
  Design (locked with maintainer 2026-07-09): GitHub Release
  body is the source of truth for release notes on the FINAL
  cut; repo `CHANGELOG.md` should carry the same filtered
  content; the website's per-version release-notes page is
  redundant and should not land on master post-tag. Website's
  release-notes preview stays useful during rel-cut/rel-update
  DRAFT PR cycles for maintainer review. Acknowledgements
  remains distinct on the website (contributor call-outs not
  duplicated on GitHub Release).

  Gap re-shape:
  - **G20 narrowed:** three-place fix (schema + jq + emitter)
    still needed to unblock the tag-path generation flow, but
    only acknowledgements need to LAND on master after the fix.
    release-notes generation on openemr-tag event should be
    skipped (or its output deleted before commit-push) so the
    website's master doesn't accumulate a redundant surface.
  - **G21 narrowed:** downloads/releases layout links to
    `github.com/openemr/openemr/releases/tag/v_M_m_p` for notes
    + `/acknowledgements/<version>/` on website for
    contributors.
  - **G22 (new):** port #173's dependabot filter algorithm to
    apply to both openemr/openemr's `CHANGELOG.md` generation
    and the GitHub Release body generation. Sequencing: G22
    lands BEFORE dropping website release-notes visibility;
    until then the website surface is the higher-quality
    version.

  Immediate 8.2.0 workaround for missing content/acknowledgements/
  8.2.0.md and content/release-notes/8.2.0.md: fire
  `openemr-rel-update` workflow_dispatch instead of
  `openemr-tag` — that event schema HAS `prev_release`, both
  generators run, resulting draft PR gets merged. Merges the
  release-notes too (temporarily accepted — will be removed
  when G22 + G20's companion workflow change land).
- **2026-07-09**: G23 discovered during the openemr-rel-update
  recovery dispatch. The recovery run completed successfully
  and both generators produced content, but `workflow:upsert-pr`
  reported success while actually NOT opening a new draft PR.
  Root cause: `gh pr view "$BRANCH"` matched merged PR #164 (the
  original 8.2.0 release-docs PR) → task ran `gh pr edit #164` on
  it → the merged PR silently ignored the intended new-PR-create.
  Force-pushed branch content sat unattached until manual PR
  create (#181). Independent of G20's schema chain — surfaces on
  ANY post-merge regen of a release-docs branch. Fix is state=open
  filter on the pr view guard.
- **2026-07-09**: G24 added. `content/demo/_index.md` on
  website-openemr hardcodes the current-stable version in three
  places (H1 heading + main demo table row + portal demo table
  row). Manual bump 8.0.0 → 8.2.0 landed via #183. Skipped 8.1.0
  because that was a prerelease-only cut, not public. Automation
  design captured: prefer render-time Hugo shortcode reading
  `.Site.Data.releases` over dispatch-time workflow edit.
- **2026-07-09**: G21 acknowledgements half SHIPPED via
  openemr/website-openemr#182.
- **2026-07-09**: G21 release-notes / changelog half CLOSED as
  won't-do (during #184 review). No dedicated changelog surface
  on the public site — the existing "Release notes (GitHub)"
  link on the downloads/releases layouts is sufficient. Website's
  release-docs DRAFT PR keeps generating per-version release-
  notes markdown for maintainer preview during development, not
  surfaced on public master. G22 remains the follow-up to make
  the two upstream surfaces (GitHub Release body + repo
  `CHANGELOG.md`) high-quality on their own.
- **2026-07-09**: G25 added. `Copilot` appears as a contributor
  in 8.2.0's generated acknowledgements page
  (`- Copilot (16 commits)`) because the existing #172 `[bot]`
  filter doesn't catch it — Copilot's commit author string is a
  bare `Copilot` with no `[bot]` marker. Fix: extend the
  acknowledgements author blocklist to a hand-curated list
  covering non-`[bot]` non-humans (Copilot + future LLM
  assistants + IDE tools).
- **2026-07-09**: G26 added. openemr-devops's Release
  Announcements (drafts) workflow failed on the v8_2_0 tag-push
  cascade because `arduino/setup-task@v2` made unauthenticated
  GitHub API calls that hit the shared-IP anonymous rate limit.
  Failure recovered via workflow rerun (rate limits reset
  hourly). Permanent fix: bump to v3 + wire `repo-token:
  ${{ secrets.GITHUB_TOKEN }}` so API calls are authenticated.
  Same action bump pending on website-openemr (#165) and
  needs audit on demo_farm_openemr + website-openemr-files.
- **2026-07-09**: G27 added. After G26's rerun succeeded, the
  rendered announcement drafts across all 6 channel templates
  (chat / facebook / forum / linkedin / mail / x) plus
  step-summary revealed content/style issues that need a
  per-template review pass. Placeholder gap; specific per-
  template diffs to be captured as the maintainer works
  through each channel.
- **2026-07-10**: G28 added. Captured a two-conductor-PR
  ordering hazard first surfaced during 8.2.0 shipping: the
  master-side finalize PR is auto-opened during the
  release-prep phase with preview content, then must be
  auto-updated one more time by the conductor after the
  rel-branch release-prep PR merges + tag is created. Only
  after that post-tag update reflects the actual shipped
  state is the finalize PR safe to merge. Nothing in the
  current UI or workflow surface signals "wait for the
  post-tag update" — a maintainer could merge the pre-
  shipping preview onto master. Preferred fix: open the
  finalize PR in `draft: true` and flip to
  ready-for-review in the post-tag auto-update event
  (matches release-docs draft-gate pattern; no branch-
  protection changes needed). Also functions as a natural
  guard rail for G16 overshoot cases.
- **2026-07-10**: G17 + G18 opened as openemr/openemr#12862
  (single-trigger + concurrency block on
  docker-build-release.yml). Byte-identical-enforced file,
  so master merge auto-syncs to rel-820, rel-810, rel-800,
  rel-704 via sync-byte-identical.yml.
- **2026-07-11**: G20 SHIPPED as a coordinated three-repo
  change: openemr/openemr-devops#855 (canonical schema),
  openemr/website-openemr#189 (envelope schema + jq + fixtures),
  openemr/openemr#12887 (vendored schema + emitter + tests +
  fixtures). All three add `prev_release` to the `openemr-tag`
  event's `tagData` block; the openemr side's `release-prep.yml`
  now derives it via `derive-prev-release.php` and passes it to
  `dispatch.php`. Fixtures across all three repos also updated
  from `8.1.0 / v8_1_0 / rel-810` to `8.2.0 / v8_2_0 / rel-820`
  since 8.1.0 was cut as a prerelease and skipped -- never
  publicly released, so referencing it as the canonical example
  read as if a shipped 8.1.0 existed. Recovery for 8.2.0's own
  acknowledgements + release-notes was handled out-of-band via
  openemr/website-openemr#181; this ships the fix prospectively
  from the next release forward.
- **2026-07-11**: G19 SHIPPED via openemr/openemr#12883. Switched
  three anonymous `raw.githubusercontent.com` version.php fetches
  (one in `docker-build-release.yml`, two in `docker-validate-
  release-targets.yml`) to authenticated `gh api` calls, and
  distinguished HTTP 404 from any other failure in the error
  path. CodeRabbit review caught the second file needing the same
  treatment and the follow-up commit landed together with the
  primary. `docker-build-release.yml` auto-syncs to the four rel
  branches via `sync-byte-identical.yml`.
- **2026-07-11**: G23 SHIPPED via openemr/website-openemr#187.
  `workflow:upsert-pr` now filters `gh pr list --head "$BRANCH"
  --state open` and captures the PR number explicitly, so a
  post-merge branch regeneration (e.g., the 2026-07-09 8.2.0
  acknowledgements-recovery dispatch that was silently absorbed
  by the merged #164 → salvaged manually as #181) now opens a
  fresh draft PR instead of no-op'ing `gh pr edit` on the
  merged one.
- **2026-07-10**: G26 SHIPPED via openemr/openemr-devops#854
  (6 workflows) + openemr/website-openemr#188 (2 workflows;
  supersedes dependabot #165 which was auto-closed on merge).
  arduino/setup-task bumped v2 → v3 everywhere the release
  path uses it, and `repo-token: ${{ secrets.GITHUB_TOKEN }}`
  wired on all openemr-devops usages (website usages already
  had it). Anonymous GitHub API rate limit exposure that
  failed release-announcements on the v8_2_0 tag-push cascade
  is now closed on the token-authenticated 5000/hr budget.
  Node 20 deprecation warning on the action's runtime also
  cleared as v3 is Node 24 native.
- **2026-07-10**: G25 SHIPPED via openemr/website-openemr#186.
  Extended the acknowledgements author-drop rule beyond
  `[bot]`-suffix to a hand-curated exact-match blocklist for
  non-`[bot]` non-humans. Initial blocklist: `Copilot`. Filter
  method renamed `filterBots()` → `filterAutomatedAuthors()`.
  Prospective fix; already-landed 8.2.0 acknowledgements page
  keeps the Copilot line until a separate touch-up or
  re-dispatch runs.
- **2026-07-10**: G24 SHIPPED via openemr/website-openemr#185.
  Render-time Hugo shortcode approach chosen: new
  `layouts/shortcodes/current-stable-version.html` reads
  `data/releases.json`, filters for FINAL, sorts descending,
  returns highest as a plain string; `content/demo/_index.md`'s
  three hardcoded `8.2.0` references replaced with the
  shortcode. Prerelease-only versions ignored (status != FINAL),
  empty fallback on no-FINAL rather than a hardcoded default.
  Manual bump-on-ship (3 hand-edits per cycle, previously via
  #183) retired.
- **2026-07-10**: G16 SHIPPED via openemr/openemr#12868 (master)
  + #12872 (rel-820 backport, patch-id parity verified). Doc
  catch-up landed as a prerequisite via #12871 so #12872 could
  cherry-pick cleanly. CodeRabbit review caught (a) branch/
  version.php major-minor consistency check as defense-in-depth,
  and — round 2 — (b) the value-vs-assignment ambiguity in my
  first-pass `-z "${TAG}"` guard: empty TAG can mean either
  "assignment absent from version.php" (parser drift, should
  error) OR "assignment present with empty value" (the normal
  shipped state, should skip cleanly). Fixed by replacing the
  value-emptiness check with a separate `grep -qP` presence
  probe for the assignment line. Bug was one grep away from
  breaking G16 in reverse -- would have caused `exit 1` on any
  push to rel-820 in its current shipped state.
- **2026-07-10**: G17 + G18 SHIPPED via openemr/openemr#12862.
  CodeRabbit review during PR life caught that the concurrency
  key should include `inputs.docker_tags` in addition to
  `github.ref` — otherwise multi-row-per-branch entries (an
  rel-* branch with both a stable-tag row and an `unreleased:
  true` next-version row in release-targets.yml) would queue
  behind each other despite pushing different registry tag
  pointers. Follow-up commit corrected the key and expanded
  the inline rationale. Companion doc update to
  RELEASE_PROCESS.md step 12 (dropping the obsolete "each rel
  branch's docker-build-release.yml also fires on its own
  `push: tags: ['v*']`" clause) bundled into the same PR.
  sync-byte-identical.yml will now open four propagation PRs
  (rel-820, rel-810, rel-800, rel-704); merging each carries
  the trigger+concurrency change onto the corresponding rel
  branch.
- **2026-07-10**: `docs/RELEASE_PROCESS.md` "What each PR
  contains" + Phase 4 title/intro updated via
  openemr/openemr#12863 to document the four-step bot-created
  PR sequence — conductor → finalize-on-master → changelog PRs
  → docs. Minimal-scope patch; the ship-release description
  is intentionally left unchanged (the current ship-release
  workflow behavior for the finalize + changelog PRs is not
  yet documented, and will be settled when the corresponding
  gaps are addressed). Two layouts touched:
  `layouts/section/downloads.html` adds an `Acknowledgements`
  `<li>` on the current stable release's "What's in this release"
  list; `layouts/section/releases.html` adds an inline
  `notes | acknowledgements` link in each row's Release notes
  cell. Both gate on `site.GetPage "/acknowledgements/<version>"`
  so pre-mechanism releases without a generated acknowledgements
  page stay quiet — no broken links or empty rows. 8.2.0 is the
  first version to surface an acknowledgements page via this
  path (content landed via #181's recovery dispatch). G21's
  release-notes half stays open pending G22 (upstream filter
  alignment).
- **2026-07-11**: G28 SHIPPED on master via openemr/openemr#12889
  and (after force-push re-do post-#12892) SHIPPED on rel-820 via
  openemr/openemr#12891 with byte-identical patch-id parity
  (`73914f4d001c2edbb51b774cd8bd13974baa8bbd` on both). Also
  carries a RELEASE_PROCESS.md two-phase-lifecycle doc update
  (Finalize-on-master PR section, one-shot-vs-continuous
  responsibility table, Phase 4 step 2) so the draft-preview
  vs post-tag-ready-for-review distinction is documented
  where maintainers will look. Investigation-narrative for
  how the G16 gate compounded the ordering hazard is in G28's
  "Actual fix" section above; not repeated here.
- **2026-07-11 -> 2026-07-12**: G20 rel-820 drift caught + fixed
  in the middle of G28's rel-820 backport work. G20 (#12887)
  had landed on master ~24h earlier but no rel-820 backport had
  been opened, and it would have silently repeated the 8.2.0
  acknowledgements-skip on the next 8.2.x ship. Fix: opened
  #12892 with byte-identical patch-id
  (`1bb7b0be1ee60d7c249a11d62322c41c45b84d3e`), landed 2026-07-12.
  Follow-up gap noted: release-mechanism files (`release-prep.yml`,
  `tools/release/**`, contract schemas, dispatch fixtures) aren't
  under byte-identical drift enforcement yet -- that surface will
  join `.github/docker-byte-identical.yml`'s manifest as part of
  the release-mechanism migration from devops. In the interim,
  keep manual cross-branch propagation the discipline.
- **2026-07-12**: 8.1.0 fixture-drift cleanup SHIPPED on both
  master (openemr/openemr#12893) and rel-820 (openemr/openemr#12894),
  byte-identical patch-id parity `02fa2f1c2926e1074531e9d5d727a611ee00823f`.
  #12887's fixture-update pass only touched the openemr-tag
  goldens + good-tag*.json; #12893 sweeps the remaining
  rel-cut / rel-update / docs-binaries goldens + their
  good-/bad-* fixture JSONs + parallel DispatchDataBuilderTest
  cases from 8.1.0/rel-810 to 8.2.0/rel-820/v8_2_0. Not
  touching BranchVersionResolverTest -- its 8.1.0 refs exercise
  the load-bearing skipped-8.1.0 manifest-filter behaviour.
  With this landed, master and rel-820 have zero drift in the
  release-mechanism file surface again.
- **2026-07-12**: G22 "Changelog surface early-migration slice"
  PRs 1-3 SHIPPED, plus a pre-PR-4 refactor. The slice
  consolidates the changelog content across all three surfaces
  (website release-notes / codebase CHANGELOG.md / GitHub Release
  body) into one generator in openemr/openemr, ports the website
  filter forward, and retires the redundant surfaces. Preparatory
  infrastructure done in three PRs: openemr/openemr#12896 (rename
  docker-byte-identical.yml -> byte-identical.yml), #12904 (glob-
  pattern support in the validator + sync scripts, extracted
  shared lib), #12910 (12 new entries in byte-identical.yml
  adding the release-mechanism surface -- release-prep.yml,
  tools/release/**, mutator classes, tests, PR body templates,
  setup-php-composer composite). PR 3 required in-flight follow-
  up #12915 to pivot the 12 entries from bare-string to object
  form with per-entry `exclude-branches:` (rel-800 and rel-704
  lack the composer autoload topology to carry 60 release-
  mechanism PHP files -- phpstan + isolated-tests + composer-
  require-checker failed on the auto-sync PRs). Object-form
  validation surfaced two consecutive workflow yq bugs, #12916
  (object entries dumped as raw YAML splat in `add-paths`) and
  #12920 (paths extracted but not filtered by target branch) --
  same root cause: the workflow's inline yq expression duplicated
  the sync script's `read_manifest_entries` + `filter_by_branch`
  logic in `lib/glob-expand.sh` and drifted from it. Motivated
  the pre-PR-4 refactor #12924, which extracts manifest parsing
  into `.github/scripts/list-manifest-paths.sh` (a shared driver
  over the existing lib functions) with 10 new BATS cases at
  `tests/bats/ci-scripts/list-manifest-paths/` -- first regression
  coverage on the workflow-side manifest-parsing path. Sync PRs
  #12921/#12922/#12923 landed the release-mechanism surface +
  object-form manifest across rel-820/rel-800/rel-704. PRs 4-6
  still pending: actual move of ChangelogGenerator + friends from
  devops -> openemr, devops build-release.yml rewire to section-
  extract from the moved CHANGELOG.md, retire of website's
  gen-release-notes.php. Other ancillary landed: #12901/#12902/#12903
  (rel-branch orphan cleanup after PR 1's rename hit a sweep-logic
  gap in sync), #12905 (rel-820 stale-refs cleanup for master-only
  files with references to the pre-rename manifest name), #12906
  (sync workflow RUNNER_TEMP hotfix after PR 2 added a script
  dependency), #12907/#12908/#12909 (auto-sync propagation of PR 2's
  shared lib to rel branches). Two significant new discoveries
  from this window captured as follow-up: sync script's rename-
  sweep gap on manifest-file-rename cases (rare; documented inline
  in the sync script for the next occurrence); master's byte-
  identical machinery + sync + validator NOT currently covering
  the sync-byte-identical.* + BATS test files (per-file rationale
  in the manifest's "Intentionally NOT in this list" block).
- **2026-07-13**: G22 PR 4 SHIPPED as coordinated pair
  openemr/openemr#12925 + openemr/openemr-devops#857 (landed same
  day to avoid the "release cut in the gap" window). Openemr side:
  moved GitHubApi + ChangelogGenerator + CompatibilityDeriver +
  CompatibilityNotesRenderer from devops into openemr's
  tools/release/src/, plus the two bin wrappers. Ported the four
  private static filter methods (isNoise / isDockerBump /
  isNoOpVersionBump / scopeOf) from website-openemr's
  ReleaseNotesGenerator into openemr's ChangelogGenerator, applied
  as a filterNoise() pre-step in generate(). Fixed compare-link:
  new compareLinkOverride parameter renders vPREV...vNEW
  (aspirational immutable-tag URL) while the git-range API call
  still enumerates from a ref that exists (rel branch at release-
  prep, target tag post-tag). Wired new ChangelogMutator at
  tools/release/src/Mutator/ChangelogMutator.php (autoload-dev
  alongside its dep chain, preserving Mike's OpenEMR\Release\
  dev/prod split) into ReleasePrepCommand's default lists via
  class_exists()-guarded FQCN reference (whitelisted in
  .composer-require-checker.json). 20 isolated
  ChangelogGeneratorTest cases + 8 ChangelogMutatorTest cases.
  Three rounds of CR fixes: preg_replace_callback for backref-safe
  section replacement, Psr\Clock\ClockInterface injection for
  rerun-idempotent dates, escapeMarkdown() +
  sanitizeGitHubUrl() to prevent Markdown injection via
  contributor-controlled PR titles / area labels / advisory
  summaries. Devops side: retired changelog-pr.php + its Taskfile
  entry + its build-release.yml step (the two post-tag PRs it
  opened are supplanted by ChangelogMutator, which writes CHANGELOG.md
  on both the release-prep PR and the release-finalize partner PR).
  Auto-sync PR #12926 propagated the 10 new files to rel-820;
  rel-800 + rel-704 correctly no-op'd (object-form
  exclude-branches filter from #12915). Follow-up #12927 added the
  ChangelogMutator whitelist entry to rel-820's
  .composer-require-checker.json manually — that file isn't in
  the byte-identical set, so the sync PR didn't carry it and
  require-checker was failing on rel-820. New PR 7 added to the
  slice plan: retire src/Common/Command/CreateReleaseChangelogCommand.php
  (Stephen Nielson's older milestone-driven Guzzle-based helper,
  374 lines) after PR 6 so we've fully cut over. Live gh-api smoke
  test against real v8_2_0...rel-820 range exercised the full
  mutator end-to-end (prev-tag resolution, range-head fallback,
  aspirational URL, prepend behavior).
- **2026-07-13** (later): G22 PR 8 SHIPPED as
  openemr/openemr#12928 (master) + openemr/openemr#12933
  (coordinated rel-820 config-file ports for
  .composer-require-checker.json whitelist +
  .codespell-ignore-words.txt "deriver" entry -- neither file is
  in the byte-identical set, so #12928's auto-sync PR
  openemr/openemr#12932 needed #12933 landed first to pass CI).
  Resequenced 2026-07-13 to land BEFORE PR 5 (compat-gap window:
  PR 5's section-extract would have pulled CHANGELOG entries
  missing the Minimum-supported-versions block for 8.2.1+ if the
  mutator wasn't in place). Content: CompatibilityMutator at
  tools/release/src/Mutator/CompatibilityMutator.php (autoload-dev
  alongside CompatibilityDeriver + Renderer deps), materializes
  rel branch's ci/ compose files via git ls-tree + git show per
  file (not git archive -- .gitattributes marks ci/ as
  export-ignore, correctly, but that means archive silently
  produces an empty tarball). Wired AFTER ChangelogMutator on
  both scopes via the same class_exists()-guarded FQCN pattern
  (refactored appendOptionalReleaseMutators() to loop over an
  FQCN array). Fixed CompatibilityNotesRenderer::inject()
  non-idempotence (CR round-2 finding from PR 4), then CR round-1
  on #12928 caught a worse variant: my initial strip regex was
  unscoped, so on a multi-release CHANGELOG the strip deleted the
  OLDER release's compat block. Landed fix scopes the strip to
  the FIRST ## section only. Also fixed a latent bug in
  ChangelogMutator noticed while planning PR 8: release-prep.yml's
  rel-scope invocation didn't pass --rel-branch, so
  $context->relBranch was null on rel scope and
  ChangelogMutator::resolveRangeHead() would throw on the first
  real 8.2.1 release-prep run. Workflow fix passes --rel-branch
  on rel scope too. Live smoke test against real rel-820's ci/
  produced exactly the shape 8.2.0's committed entry has (PHP
  8.2+ / MariaDB 10.6+ / MySQL 5.7+ + tested-CI-matrix link).
  With PR 8 landed the compat-gap window is closed and PR 5
  (devops section-extract) is unblocked.
- **2026-07-13** (even later): G22 PR 5 SHIPPED as
  openemr/openemr-devops#858. Deleted ChangelogGenerator +
  CompatibilityDeriver + CompatibilityNotesRenderer +
  changelog.php + derive-compatibility.php from devops; GitHubApi
  stays (preflight.php + PreflightChecker still consume it, will
  migrate in a future PR). New
  tools/release/bin/extract-changelog-section.php is a 60-line
  Symfony Console CLI that reads openemr's CHANGELOG.md and
  writes one version's `## [X.Y.Z]` section to
  release-output/changelog.md. Since ChangelogMutator +
  CompatibilityMutator (PR 4 + PR 8) both bake into that section
  (filtered PR list + Minimum-supported-versions), the extract
  carries everything in one pass. Taskfile: dropped `changelog:`
  + `compatibility:`, added `changelog:extract`.
  build-release.yml: replaced the two steps (task
  release:changelog + task release:compatibility) with one
  extract step; no more GH_TOKEN or gh api calls in the changelog
  path (everything reads from the local openemr checkout).
  Cleaned up dead `base_ref` end-to-end: removed from
  build-release.yml's input declarations + build-release-on-tag.
  yml's "Derive base_ref from previous release" step. Byte-
  comparison parity: extracted 8.2.0 section = 792 lines / 83.6KB,
  matches the ~794 lines measure of 8.2.0's live GitHub Release
  body -- since 8.2.0's CHANGELOG.md was written by pre-PR-4
  devops's own ChangelogGenerator, extract IS byte-identical to
  what devops's changelog.php would produce today for 8.2.0.
  8.2.1+ will have the noise filter applied via mutator, which
  is the intended semantic improvement of the whole slice.
  CR round-1 fix (shell-metachar injection risk on
  VERSION='inputs.version') routed the value through an env var.
  New PR 10 added to the slice plan: update openemr's
  docs/RELEASE_PROCESS.md to describe the new mutator-driven
  flow and drop references to the retired paths. Belongs after
  PR 7 so the doc doesn't describe paths that still technically
  exist somewhere in the tree.
- **2026-07-14**: G22 PR 6 SHIPPED as
  openemr/website-openemr#190. Retired website's release-notes
  generator (gen-release-notes.php + ReleaseNotesGenerator.php +
  tests + fixtures + Taskfile task). Workflow "Generate release
  notes" step replaced with a "Remove stale per-release
  release-notes page" scrub matching the pattern already in place
  for the retired install/upgrade pages -- targets
  $PUBLISH/content/release-notes/$VERSION.md for the current
  dispatch's version only, so 8.2.0's live page at
  openemr.org/release-notes/8.2.0/ stays grandfathered.
  AcknowledgementsGenerator + acknowledgements page untouched.
  Follow-up not in the PR: replace 8.2.0's static page with a
  redirect to https://github.com/openemr/openemr/releases/tag/v8_2_0
  for uniform user experience with 8.2.1+ (no per-version page).
  Slice now 7 of 10 SHIPPED; remaining: PR 7 (retire
  CreateReleaseChangelogCommand), PR 9 (fixture regression), PR 10
  (RELEASE_PROCESS.md rewrite, depends on PR 7).
- **2026-07-14** (later): G22 PR 7 SHIPPED as
  openemr/openemr#12964. Deleted CreateReleaseChangelogCommand
  (Stephen Nielson's 374-line Guzzle-based milestone helper;
  predates the migration, fully redundant with the
  ChangelogGenerator + ChangelogMutator flow). Also removed the
  file's `.github/byte-identical.yml` entry, pruned 66 stale
  phpstan-baseline entries across 17 baseline files (via awk
  block-filter), and lowered two fatal-baseline-caps
  (method.notFound 138->137, variable.undefined 509->508) in the
  same commit -- caps only go down per the caps contract. No live
  callers of `openemr:create-release-changelog` grepped anywhere
  in the tree; no tests referenced the class. 19 files changed /
  706 deletions net. Sync workflow propagates the file deletion +
  manifest update to rel-820 (excluded from rel-800 + rel-704 per
  the object-form filter). Slice now 8 of 10 SHIPPED; remaining:
  PR 9 (fixture regression) and PR 10 (RELEASE_PROCESS.md rewrite,
  now unblocked).

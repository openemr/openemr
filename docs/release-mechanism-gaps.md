# OpenEMR Release Mechanism: Gaps & Open Questions

Working notes — gaps surfaced during release-automation exploration that
aren't blockers for the current migration but warrant follow-up. Captures
things to investigate or address AFTER the release-mechanism migration
completes, plus discoveries during the upcoming manual 8.1.1 work.

**Last updated:** 2026-06-22

Migration-related gaps also appear in the planning doc's `## Deferred /
known debt` section:
`/home/brady2/git/openemr-wt-release-mechanism-migration-doc/docs/release-mechanism-migration-from-devops.md`

## Quick context

- **Current branch under release work:** `rel-810` (8.1.0 was cut as
  artifact only — was buggy, removed; next planned release is **8.1.1**).
- **Conductor (`release-prep.yml`)** lives in `openemr/openemr` already.
- **Consumers (build / announcements / ship)** still in `openemr-devops`;
  release-mechanism migration in flight.
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

### G2 — Post-release version bump (rel-810 → next "-dev") not visible

- **What:** After v8_1_1 is tagged, rel-810's `version.php` needs to
  advance to something like "8.1.2-dev" so subsequent pushes correctly
  enter the 8.1.2 cycle (per the conductor's expected starting state).
  Not visible in the obvious mutator set.
- **Why I care:** If this isn't automated, it's a manual post-release
  step that has to happen between cycles. Otherwise rel-810 keeps trying
  to re-release 8.1.1.
- **Possible answers (unverified):**
  - A separate post-finalize step in `release-prep.yml` (didn't see one)
  - Part of `build-release-on-tag.yml`'s post-publish cleanup
  - Genuinely manual (maintainer commits a `version.php → 8.1.2-dev`
    change after each release)
- **TODO:** Trace version.php state across a complete release cycle
  (push → conductor → tag → build → next push). Read the conductor's
  `VersionPhpMutator` end-state behavior.
- **Status:** Not investigated.

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

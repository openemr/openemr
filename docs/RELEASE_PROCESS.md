# OpenEMR Release Process

This document is the **complete release runbook** for tagged OpenEMR releases — every step from pre-release QA through post-release announcements, including the parts that are automated, the parts that aren't yet, and the parts that are irreducibly manual.

The automation core spans three repositories and is driven by `repository_dispatch` events emitted by this repo as the conductor. Two of them open reviewable PRs — the docs PR in `website-openemr` (acknowledgements + release-status shortcode + b10 SchemaSpy on tag) and the auto-derive reconciliation PR in `demo_farm_openemr` (`ip_map_branch.txt` + `demoLibrary.source` regenerated from upstream state); the conductor PR here (code/version bumps plus the generated `CHANGELOG.md` entry) rounds out the three PRs a release cuts. `website-openemr` also renders per-channel announcement drafts (forum/chat/social/mail) into a workflow artifact when the docs PR merges to master — the download page going live is the semantic "release is really out there" moment. The build side (distribution packages + checksums + GitHub Release object) runs intra-repo: this repo's [`build-release-on-tag.yml`](../.github/workflows/build-release-on-tag.yml) consumes its own `openemr-tag` dispatch (workstream 7 Phase 2 landed 2026-07-21; the build previously ran in `openemr-devops`). One step is still done by hand today (social/forum/email announcement posting after drafts are rendered); see [Automation gaps](#automation-gaps).

For background on why the flow is shaped this way, see [openemr/openemr-devops#664](https://github.com/openemr/openemr-devops/issues/664). For the per-slice plan documents, see the [Slice plans](#slice-plans) section below. For the end-to-end ordered checklist a release manager actually walks through, jump to [Release runbook](#release-runbook).

## Repositories involved

| Repository | Role |
| --- | --- |
| [`openemr/openemr`](https://github.com/openemr/openemr) | **Conductor + build consumer.** Owns the release-prep PR. Merging it is the "we're shipping" decision; the merge commit gets the annotated release tag. On tag, the intra-repo `build-release-on-tag.yml` consumes `openemr-tag` and produces distribution packages + checksums + the GitHub Release object. Also emits `repository_dispatch` cross-repo to `website-openemr` and `demo_farm_openemr` (docs PR + demo-farm auto-derive reconciliation). |
| [`openemr/website-openemr`](https://github.com/openemr/website-openemr) | **Docs + announcements consumer.** Subscribes to `rel-*` and tag events. Generates per-version Hugo content: acknowledgements page and the release-status shortcode that renders DRAFT until the tag event flips it to FINAL. Install/upgrade pages default to version-agnostic wiki targets (see the Docs PR section). Release notes are no longer generated per-release — the canonical changelog now lives in `openemr/openemr`'s `CHANGELOG.md` (written by the conductor's `ChangelogMutator`) and is extracted for the GitHub Release body at build time. On `openemr-tag` the same workflow also regenerates the EHI / ONC (b)(10) SchemaSpy schema documentation and publishes it under `/documentation/<version>/b10/` (tracked by git-lfs) in the same docs PR. Also fires `release-announcements.yml` on `release-docs/<version>` PR merge (docs-PR-merged = download page live = "release is really out there" moment); renders per-channel announcement drafts (forum, chat, social, mail) into a workflow artifact for maintainer copy-paste + mailing-list send. |
| [`openemr/openemr-devops`](https://github.com/openemr/openemr-devops) | **Dispatch contract source (until Phase 5).** Owns the canonical `dispatch.schema.json` and `TagVerifier` (workstream 7 Phase 5 inverts these to this repo as canonical). Historically also the build consumer; the build machinery moved to this repo in workstream 7 Phase 2 (2026-07-21). Announcement rendering moved to `openemr/website-openemr` in Phase 4 (2026-07-19, `openemr-devops#861`). The CI matrix / package pin rotation slice was retired in the release-mechanism cleanup (post-docker-migration: Docker pins live in **this repo**'s [`.github/release-targets.yml`](../.github/release-targets.yml); CI matrix rotation went away with the slot system). Devops copies of build-release / build-release-on-tag / build-patch stay live but dormant until Phase 6 formally deletes them. |
| [`openemr/demo_farm_openemr`](https://github.com/openemr/demo_farm_openemr) | **Demo-farm consumer.** Subscribes to `release-targets-changed`. The `derive-ip-map` auto-derive bot regenerates `ip_map_branch.txt` + `docker/scripts/demoLibrary.source` from upstream `openemr/openemr` state (`.github/release-targets.yml` + per-rel-branch Dockerfile ARGs + `ci/apache_*` listing + flex Dockerfile). On diff it force-pushes the stable `auto-derive/reconciliation` branch and opens (or updates) a reviewable PR titled `[auto-derive] reconcile demo_farm against upstream openemr master`; on no-diff it closes any open reconciliation PR and deletes the branch. Runs on the `release-targets-changed` dispatch (immediate), a daily 07:00 UTC cron (self-healing fallback), and `workflow_dispatch` (manual). A maintainer merges the PR and manually updates wiki pages if applicable; the demo-farm host's nightly reset then picks up the new pins. |

## Cross-repo flow

```mermaid
flowchart TB
    subgraph manual["Maintainer actions"]
        direction LR
        cut["Cut rel-NNN0 branch<br/>(e.g. rel-810)"]
        edit["Edit CHANGELOG.md<br/>on release-prep PR"]
        sign["Sign off ONC cert page<br/>(major only)"]
        trigger["Trigger ship-release.yml<br/>(pick version + rel-branch)"]
    end

    subgraph oe["openemr/openemr (conductor)"]
        prepPR(["release-prep/&lt;rel&gt; PR<br/>reviewable"])
        tag[["annotated tag<br/>vX_Y_Z"]]
        rel[("GitHub Release object<br/>dist packages + checksums + changelog")]
    end

    subgraph wo["openemr/website-openemr"]
        docsPR(["release-docs/&lt;version&gt; PR<br/>reviewable"])
    end

    subgraph od["openemr/openemr"]
        ship{{"ship-release.yml<br/>merges 3 PRs in order<br/>(semi-auto | full-auto | dry-run)"}}
    end

    subgraph df["openemr/demo_farm_openemr"]
        derivePR(["auto-derive/reconciliation PR<br/>reviewable"])
    end

    cut -->|push rel-*| prepPR
    prepPR -->|every push| prepPR
    edit -.-> prepPR
    sign -.-> docsPR
    trigger -->|workflow_dispatch| ship
    ship -->|1. merges| prepPR
    ship -->|2. merges| docsPR

    prepPR ==>|merge creates| tag
    tag -. "build-release-on-tag.yml" .-> rel

    prepPR -. openemr-rel-cut .-> docsPR
    prepPR -. openemr-rel-update .-> docsPR
    tag -. openemr-tag .-> docsPR
    oe -. release-targets-changed<br/>(master push touching<br/>.github/release-targets.yml) .-> derivePR

    classDef manualStep fill:#fff4cc,stroke:#b58900
    classDef autoArtifact fill:#e8f0ff,stroke:#3b6fb8
    classDef autoTag fill:#d4f1d4,stroke:#2a7f2a
    classDef autoWorkflow fill:#f0e8ff,stroke:#7a3bb8
    class cut,edit,sign,trigger manualStep
    class prepPR,docsPR,derivePR autoArtifact
    class tag autoTag
    class ship autoWorkflow
```

**Legend.** Yellow nodes are maintainer actions. Blue nodes are reviewable PRs that workflows open and force-update on every dispatch (the conductor PR, the docs PR, and the demo_farm auto-derive reconciliation PR). The green node is the annotated tag the conductor creates on merge. Purple hexagons are workflows: `ship-release.yml` in `openemr-devops`, which an operator triggers via `workflow_dispatch` to merge the two release-cycle PRs in order (conductor → docs) with mergeability gates. The demo_farm reconciliation PR is opened by the `derive-ip-map` bot in `demo_farm_openemr`, which re-derives `ip_map_branch.txt` + `demoLibrary.source` from upstream `openemr/openemr` state and writes the result via a reviewable PR; it runs on the `release-targets-changed` dispatch (fired by this repo on pushes to master that touch `.github/release-targets.yml`), a daily 07:00 UTC cron (self-healing fallback), and `workflow_dispatch`. The `openemr-tag` event also flows intra-repo to this repo's `build-release-on-tag.yml` (which produces the GitHub Release object; workstream 7 Phase 2 migrated the build from `openemr-devops` to here on 2026-07-21). Separately, when the docs PR merges to master, `release-announcements.yml` in `website-openemr` renders per-channel announcement drafts (fires on `pull_request:closed`, not the `openemr-tag` dispatch — the download-page-live moment is what triggers announcements, not tag creation). Both build-release and release-announcements are omitted from the diagram to keep the cross-repo PR flow legible. Solid arrows are git/PR actions and workflow triggers; dotted arrows are `repository_dispatch` events labeled with the event name.

## Cross-repo events

`openemr/openemr` emits `repository_dispatch` on every push to `rel-*`, on tag creation, and on master pushes that change `.github/release-targets.yml`, targeting itself (`openemr-tag` build consumer, intra-repo since workstream 7 Phase 2 landed 2026-07-21), `openemr/website-openemr`, and `openemr/demo_farm_openemr`. Consumers subscribe via the matching `repository_dispatch` workflow trigger; `demo_farm_openemr` acts on `release-targets-changed` (its auto-derive bot is the canonical writer for the demo-farm config) and is no longer a consumer of `openemr-tag`.

| Event | Emitter → target | When | `data` payload |
| --- | --- | --- | --- |
| `openemr-rel-cut` | `openemr/openemr` → website-openemr | First push to a new `rel-*` branch | `{ branch, version, prev_release }` |
| `openemr-rel-update` | `openemr/openemr` → website-openemr | Subsequent push to an existing `rel-*` branch | `{ branch, version, prev_release }` |
| `openemr-tag` | `openemr/openemr` → openemr (intra-repo build consumer), website-openemr | Annotated tag created on `rel-*` HEAD | `{ tag, branch, version }` |
| `release-targets-changed` | `openemr/openemr` → demo_farm_openemr | Push to `master` touching `.github/release-targets.yml` (emitted by [`.github/workflows/notify-release-targets-changed.yml`](../.github/workflows/notify-release-targets-changed.yml)) | `{}` (no event-specific fields; the common envelope's `sha`, `actor`, and `dispatched_at` fully identify the change) |

Common envelope on every event: `{ event, repo, sha, actor, dispatched_at, data }`.

**`prev_release` derivation.** The `prev_release` field in `openemr-rel-cut` / `openemr-rel-update` payloads is computed by [`tools/release/bin/derive-prev-release.php`](../tools/release/bin/derive-prev-release.php) at dispatch time. It calls `BranchVersionResolver::previousRelease($targetVersion)`, which fetches the canonical shipped-versions manifest from `openemr/website-openemr` (`https://raw.githubusercontent.com/openemr/website-openemr/master/data/releases.json`) and returns the highest FINAL version below the target. A tag whose version isn't in the manifest is treated as skipped (e.g., `v8_1_0` was cut and then skipped; the resolver correctly walks past it to `8.0.0` for target 8.2.0). Any manifest-fetch failure falls back to the pre-manifest annotated-tag walk as a safety net. `release-targets.yml`'s `unreleased: true` marker is NOT used as the signal — it's transient and clears once the next release supersedes the placeholder row.

**Schema location.** The canonical JSON Schema lives in `openemr-devops` at [`tools/release/contracts/dispatch.schema.json`](https://github.com/openemr/openemr-devops/blob/master/tools/release/contracts/dispatch.schema.json) and is vendored into each consumer (drift-checked in CI). The vendored copy in this repo is at [`tools/release/contracts/dispatch.schema.json`](../tools/release/contracts/dispatch.schema.json).

**Tag verifier.** The shared `TagVerifier` lives at [`tools/release/src/TagVerifier.php`](../tools/release/src/TagVerifier.php), vendored from `openemr-devops`'s [`tools/release/src/TagVerifier.php`](https://github.com/openemr/openemr-devops/blob/master/tools/release/src/TagVerifier.php). It confirms the tag is annotated (not a lightweight ref) and that the tag message contains a `MAJOR.MINOR.PATCH` version, an ISO date (`YYYY-MM-DD`), and the 40-hex merge-commit SHA — the fields the openemr-devops#664 spec requires CI to enforce.

## What each PR contains

### Conductor PR — `release-prep/<rel-branch>` in `openemr/openemr`

Long-lived draft PR against the `rel-*` branch, force-updated by [`.github/workflows/release-prep.yml`](../.github/workflows/release-prep.yml) on pushes to a production release branch (matching `rel-[0-9]*0`) when the branch is in an active dev cycle — determined by parsing the branch's `version.php` and requiring `$v_tag == '-dev'`. Pushes to a rel branch that already shipped (post-tag, `$v_tag` empty) fire the workflow but exit cleanly without opening or updating a prep PR, so a `$v_database` bump from a database-migration PR or any other post-ship commit doesn't produce a spurious "prep <next-patch>" PR. Test branches like `rel-test` go through `workflow_dispatch` instead. The mechanical edits applied by `bin/console openemr:release-prep` are documented in [`docs/release-automation-plan.md`](release-automation-plan.md) (the conductor slice plan).

In short, the conductor rewrites: `version.php`, `docker/production/docker-compose.yml` (image tag pin), `src/RestControllers/OpenApi/OpenApiDefinitions.php`, `swagger/openemr-api.yaml` (regenerated from the CLI), and `CHANGELOG.md` — via [`ChangelogMutator`](../tools/release/src/Mutator/ChangelogMutator.php) (writes the filtered PR list for the release window using `ChangelogGenerator`, refreshed on every push) and [`CompatibilityMutator`](../tools/release/src/Mutator/CompatibilityMutator.php) (injects the "Minimum supported versions" block using `CompatibilityDeriver` + `CompatibilityNotesRenderer`, kept idempotent by scoping the strip-and-inject to the first `## [X.Y.Z]` section). Both mutators run on rel-scope release-prep and on master-scope release-finalize so the `CHANGELOG.md` diff appears in the same PR as the version bumps. (The `library/globals.inc.php` debug toggle is applied by the sibling branch-cut conductor at cut time; release-prep does not re-apply it. The `docker-version` triple and `sql/*_upgrade.sql` skeleton are scaffolded at branch-cut / patch-prep time, not at ship time.)

**GHSA → CHANGELOG matching (the "Patched versions" convention).** `ChangelogGenerator` renders a `### Security Fixes` block for each published security advisory whose `vulnerabilities[].patched_versions` field exactly equals the release version string (e.g. `"8.2.0"`). This is the primary match path — openemr's GHSA publish workflow leaves the free-form References field empty, so the fallback SHA/PR reference-based match rarely fires in practice. **When publishing a GHSA for a fix that lands in a specific release, set Patched versions to the exact release string** — no ranges, no comma-separated lists (the matcher is strict-exact). GHSAs published *before* the conductor PR merges land in the shipped `CHANGELOG.md` and the tagged GitHub Release body automatically. GHSAs published *after* the release ships need a follow-up amendment via [`.github/workflows/release-amendment.yml`](../.github/workflows/release-amendment.yml) — see the Release-amendment PRs section below for the full flow (single `workflow_dispatch` invocation opens the sibling rel + master CHANGELOG PRs and syncs the Release body + `changelog.md` attachment; idempotent so re-dispatching is safe).

### Docs PR — `release-docs/<version>` in `openemr/website-openemr`

Per-release PR that gets rewritten as a single-commit branch on master's HEAD each dispatch (openemr/website-openemr#175). Generated content: acknowledgements (from `git shortlog vPREV..HEAD`, `[bot]`-authored entries filtered per openemr/website-openemr#172) and the release-status shortcode that renders `DRAFT — based on rel-* @ <sha>` until the `openemr-tag` event flips it to FINAL. Install/upgrade pages are NO LONGER generated per release — the site's `/downloads/` template defaults to the wiki's version-agnostic `OpenEMR_<Linux|Windows>_<Installation|Upgrade>` pages instead (openemr/website-openemr#170/#171). Release notes are NO LONGER generated on the docs side either — the canonical changelog lives in `openemr/openemr`'s `CHANGELOG.md` (written by the conductor's `ChangelogMutator`; see the Conductor PR section above). The GitHub Release body is a section-extract of that same `CHANGELOG.md` at build time (see runbook step 10).

On the `openemr-tag` event, the same workflow also regenerates the EHI / ONC (b)(10) SchemaSpy schema documentation from the tagged release's schema and commits it under `static/documentation/<version>/b10/` (tracked by git-lfs) in this same docs PR. It is served at `/documentation/<version>/b10/`. The table set in scope is read from `Documentation/EHI_Export/b10-tables.yml` in the tagged openemr checkout, so it always matches that release's schema.

### Finalize-on-master PR — `release-finalize/<rel-branch>` in `openemr/openemr`

Auto-opened alongside the conductor PR during the release-prep phase. Carries the master-side updates to `.github/release-targets.yml`: pins the rel-branch row's `openemr_version_ref` to the new tag, slot-shuffles `latest` / `next` / `dev` across rows, and drops any `unreleased: true` placeholder row.

Two-phase lifecycle:

1. **Preview phase (during the `-dev` cycle).** Opened as a **draft** by `release-prep.yml`'s `prep` job on each push to the rel branch while `$v_tag == '-dev'`. Content reflects the *planned* post-ship rotation (`openemr_version_ref` points at the rel-branch tip as a preview since the annotated tag doesn't exist yet). Maintainers can preview and sanity-check the rotation shape here, but the draft state signals "not yet safe to merge."
2. **Post-tag phase (after conductor PR merge).** The `finalize` job re-runs the master-scope mutators after creating the annotated tag, force-updates the PR body + commit with the actual just-shipped state (`openemr_version_ref` pinned to the real `v_X_Y_Z` tag), then explicitly flips the PR from draft to ready-for-review and posts a maintainer-facing signal comment ("Post-tag update applied ... ready to merge to master"). Only after this flip is the PR safe to merge.

### Release-amendment PRs — `release-amendment/<version>-<rel-branch>` and `release-amendment/<version>-master` in `openemr/openemr`

Opened manually via [`.github/workflows/release-amendment.yml`](../.github/workflows/release-amendment.yml) (`workflow_dispatch`) *after* a release has shipped, to pick up security advisories the maintainer published in the days/weeks after the tag. Re-runs the release-prep mutators against the shipped state on both rel-branch and master; every mutator except `ChangelogMutator` + `CompatibilityMutator` is a no-op on a post-tag checkout, so the diff is scoped to `CHANGELOG.md`'s target `## [X.Y.Z]` section — typically the newly-populated `### Security Fixes` block. The workflow also re-extracts the amended section from `CHANGELOG.md` and updates the GitHub Release body (`gh release edit --notes-file`) plus the sibling `changelog.md` Release attachment (`gh release upload --clobber`) in the same run, so all four surfaces (rel-branch CHANGELOG, master CHANGELOG, Release body, Release attachment) converge without waiting for the CHANGELOG PRs to merge. Idempotent by design (mutators + peter-evans no-op detection + strict-string-equal Release body edit): re-dispatching without new GHSAs produces empty PRs and a no-op Release edit.

> **Hand-edits do not survive a rerun.** `ChangelogMutator` wholesale-replaces the target `## [X.Y.Z]` section on every run, so any prose edits made to the amendment PR (or committed directly to the amendment branch) will be wiped the next time the workflow is dispatched. This is intentional — the mutator's output is the source of truth, and idempotence is a hard requirement for safely re-running the workflow as additional GHSAs are published. If tone/prose edits are needed, either (a) make them once *and don't re-dispatch*, or (b) apply the edits to the generator's input filters / formatter and rerun from clean.

**Interactions with `build-release.yml`.** `build-release.yml` uses `gh release view` before `gh release create`, so a re-fire on `openemr-tag` after an amendment run will NOT overwrite the amended Release body (the create is skipped). Its `gh release upload --clobber changelog.md` step DOES run unconditionally, though, and would re-upload the pre-amendment `changelog.md` attachment. To keep the attachment in step with the body, the amendment workflow uploads the amended section as `changelog.md` too — the two surfaces stay aligned at amendment time. A subsequent build-release re-fire is the one window where the attachment could drift from the body; re-dispatch the amendment workflow to reconcile. (`build-release.yml` migrated from `openemr-devops` to this repo in workstream 7 Phase 2 on 2026-07-21.)

**Original release date is preserved.** `ChangelogGenerator` uses `SystemClock` and rewrites the `## [X.Y.Z] - YYYY-MM-DD` heading date on every mutator run. That's correct at release-prep time (today IS the release day), but wrong for an amendment weeks later. The workflow captures the shipped release date from the existing CHANGELOG heading before the mutator runs and restores it after, so the heading date stays pinned to when the release actually shipped.

**Release body truncation at 125K.** GitHub caps the Release body at 125,000 characters — larger payloads are rejected by the API (`body is too long (maximum is 125000 characters)`). Big versions (wide PR footprints or many advisories) can exceed that; the extracted 8.2.0 amended section is ~130 KB, for example. The workflow handles this with a hybrid strategy: if the extracted section fits (≤124 KB, one KB margin under the hard cap), the body carries the full section unchanged. If it doesn't, the body is substituted with a short link-only pointer that references the version-specific section on the rel branch's live `CHANGELOG.md` (e.g., `blob/rel-820/CHANGELOG.md#820---2026-07-08` for 8.2.0) plus the sibling `changelog.md` Release attachment. The URL points at the **rel branch** — not the tag — so it stays linkable when future patch entries (e.g., 8.2.1) land above the 8.2.0 section on the same file; the version-anchor (GitHub's markdown-heading slug of `## [X.Y.Z] - YYYY-MM-DD`, which strips dots and joins the version to the ISO date with `---`) keeps the link scrolling to the correct entry. **The `changelog.md` attachment always carries the full amended section regardless of size** (attachments have no such limit), so no content is ever lost — only the body-vs-attachment split changes. The Summary step surfaces which path was taken so operators can see it in the run output; dry-runs also surface the "would truncate" state up front.

## Workflow topology

Five `openemr/openemr` workflows handle the release automation: two are lifecycle-event one-shots that open ready-for-review scaffolding PRs, one is the continuous tracking + dispatch workflow that produces the drafts described above, one is a manual-dispatch post-release amendment, and one is a CI-gate smoke test that guards the whole family against auth/env plumbing regressions.

### Lifecycle-event workflows (siblings)

Both fire on a single event, open **two coordinated ready-for-review PRs** (rel-side + master-side) meant to be merged quickly after review, and don't run again for that same event. They handle the mechanical scaffolding a maintainer would otherwise do by hand.

- **[`branch-cut-automation.yml`](../.github/workflows/branch-cut-automation.yml)** — fires on the `create` event when a new `rel-NNN0` branch is pushed. Rel-side PR carries the branch-cut mutations (`library/globals.inc.php` debug toggle, `docker-version` triple seed, `docker/release/Dockerfile` bump, `docker/release/upgrade/fsupgrade-<N>.sh` seed, etc.); master-side PR advances master's `-dev` version to the next minor line and inserts the new release-targets.yml row with the slot shuffle.
- **[`patch-prep-automation.yml`](../.github/workflows/patch-prep-automation.yml)** — fires when a `$v_patch` bump into `-dev` lands on a `rel-*` branch (e.g., `8.1.0` → `8.1.1-dev` on `rel-810`). Path-filtered to `version.php` with a before/after diff check so unrelated pushes never trigger it. Rel-side PR seeds the patch-cycle boilerplate (`sql/*_upgrade.sql` skeleton for the incoming patch's migrations, docker `fsupgrade-<N>.sh`, docker-version triple bump); master-side PR handles the SQL bridge file-rename dance (e.g., `8_1_1-to-8_2_0_upgrade.sql` → `8_1_2-to-8_2_0_upgrade.sql` plus a new `8_1_1-to-8_1_2_upgrade.sql`) so master's upgrade chain stays consistent when a rel branch ships an intermediate minor.

### Continuous tracking workflow

- **[`release-prep.yml`](../.github/workflows/release-prep.yml)** — fires on **every push** to a `rel-[0-9]*0` branch (also triggered on the `create`-event push and on the `patch-prep`-triggering push, so it runs alongside its siblings on both cut events). Maintains the two **draft** PRs described in "What each PR contains" above (`release-prep/<rel-branch>` + `release-finalize/<rel-branch>`), including a fresh `CHANGELOG.md` regen on every push via `ChangelogMutator` + `CompatibilityMutator`. Also emits the `openemr-rel-cut` / `openemr-rel-update` `repository_dispatch` events to `openemr/website-openemr` so the docs draft PR stays in sync. Gates internally on `version.php`'s `$v_tag == '-dev'` — pushes to a rel branch that already shipped (post-tag, `$v_tag` empty) fire the workflow but exit cleanly without touching the draft PRs, so a `$v_database` bump from a database-migration PR or any other post-ship commit doesn't produce a spurious "prep <next-patch>" PR.

### Manual-dispatch amendment workflow

- **[`release-amendment.yml`](../.github/workflows/release-amendment.yml)** — fires on **`workflow_dispatch` only**, invoked by the release manager after publishing security advisories in the post-ship window. Reuses the release-prep mutator entry point (`openemr:release-prep --scope=rel|master`) on a post-tag checkout: every mutator except `ChangelogMutator` + `CompatibilityMutator` is idempotent no-op against shipped state, so the diff is scoped to `CHANGELOG.md`'s target section (typically the newly-populated `### Security Fixes` block, matching the just-published GHSAs' `patched_versions == "X.Y.Z"`). Opens `release-amendment/<version>-<rel_branch>` + `release-amendment/<version>-master` PRs and re-extracts + `gh release edit`s the GitHub Release body + `gh release upload --clobber`s the `changelog.md` attachment in the same run, so all four surfaces (rel-branch CHANGELOG, master CHANGELOG, Release body, Release attachment) converge without waiting for the CHANGELOG PRs to merge. Body is truncated to a link-only pointer when the extracted section exceeds GitHub's 125K Release body limit; the attachment always carries the full section (see the Release-amendment PRs section above for the full description). Validates that the annotated tag exists before amending — refuses to amend an unshipped release.

### CI smoke test

- **[`release-mechanism-smoketest.yml`](../.github/workflows/release-mechanism-smoketest.yml)** — path-gated `pull_request` + `push`-to-master workflow that runs `openemr:release-prep --scope=rel` end-to-end against `rel-820` on every change to a release-mechanism workflow YAML or PHP file. Deliberately checks out with `persist-credentials: false` and requires `GH_TOKEN` in the mutator step's env block — mirrors the strictest workflow shape (`release-amendment.yml`). Any auth/env-plumbing regression (e.g., dropping `GH_TOKEN`, flipping `persist-credentials` without keeping the token) fails the smoke test in CI before landing. Complements the isolated `ChangelogMutatorTest` + `ChangelogGeneratorFixtureTest` unit tests, which exercise the mutator LOGIC via injected fakes and never actually shell out to `gh api`; the smoke test is the only place that catches env-plumbing bugs like [openemr/openemr#12999](https://github.com/openemr/openemr/pull/12999). No side effects — smoke test discards its mutations.

### Post-hardening manual step

Whenever hardening `release-prep.yml`, `release-amendment.yml`, or any other release-mechanism workflow that runs `openemr:release-prep`: **verify the smoke test job (`release-mechanism-smoketest`) passes on the PR before merge.** The smoke test covers most regressions automatically. For behaviour changes that go beyond auth/env plumbing (e.g., logic changes to `finalize` job, tag-cut path, etc.), also do a manual `test: true` dispatch of `release-prep.yml` against `rel-test` — that opens a `release-prep-test/<branch>` PR whose merge produces a throwaway `-test.<sha>` tag, exercising the whole conductor path without cutting a real release.

### One-shot vs continuous — where each responsibility lives

| Event | `branch-cut` | `patch-prep` | `release-prep` |
| --- | --- | --- | --- |
| New `rel-NNN0` branch created | fires; opens 2 ready-for-review PRs | — | fires (on the same push); opens 2 drafts + emits `openemr-rel-cut` |
| Existing `rel-*` receives a `$v_patch` bump into `-dev` | — | fires; opens 2 ready-for-review PRs | fires (on the same push); opens/updates 2 drafts + emits `openemr-rel-update` |
| Any subsequent commit while `$v_tag == '-dev'` | — | — | fires; updates 2 drafts + emits `openemr-rel-update` |
| Any commit after the branch shipped (post-tag, `$v_tag` empty) | — | — | fires but exits with `should-run=false`; no draft PR changes, no dispatch |
| Release-prep PR merged (annotated tag created) | — | — | `finalize` job; creates tag + refreshes release-finalize PR with post-tag content (including `CHANGELOG.md` regen so both rel-branch and master land the same entry) + flips it draft → ready + emits `openemr-tag` |

The two sibling one-shots handle **discrete lifecycle events** (branch creation, patch-cycle start); `release-prep.yml` handles the **continuous state** (draft PRs following the branch tip during an active dev cycle) plus the tag-time emission on merge. Clean separation of concerns — no overlap on which PRs each workflow owns, and the parallel firing on cut events is by design so the ready-for-review scaffolding and the draft tracking both appear together.

## Orientation: finding the current release state

This document describes the *process*. The *current* state lives in Git and the GitHub API — discover it before acting; do not assume a release is or isn't in flight.

- **Which release is in flight?** Active release branches and the open conductor PR:

  ```
  git ls-remote --heads https://github.com/openemr/openemr 'rel-*'
  gh pr list --repo openemr/openemr --state open --json number,headRefName \
    --jq '.[] | select(.headRefName | startswith("release-prep/"))'
  ```

- **The two sibling PRs** (given a version `X.Y.Z` and branch `rel-<MAJOR><MINOR>0`):

  ```
  gh pr list --repo openemr/openemr        --state open --json number,headRefName --jq '.[]|select(.headRefName|startswith("release-prep/"))'   # conductor
  gh pr list --repo openemr/website-openemr --head "release-docs/X.Y.Z" --state open                                                          # docs
  ```

- **Is the tag cut? Did the Release object land?**

  ```
  git ls-remote --tags https://github.com/openemr/openemr 'vX_Y_Z'
  gh release view vX_Y_Z --repo openemr/openemr   # 404 here after a tag = the historical v8.1.0 failure (step 10/11)
  ```

- **Shipping.** `ship-release.yml` inputs are `version` (e.g. `8.2.0`), `rel_branch` (e.g. `rel-820`), `mode` (choice: `semi-auto` default | `full-auto` | `dry-run`), `dry_run` (bool; legacy alias — wins over `mode` when true). Validate before merging: `gh workflow run ship-release.yml --repo openemr/openemr -f version=X.Y.Z -f rel_branch=rel-XY0 -f mode=dry-run`. Semi-auto merges Conductor and leaves Docs+Finalize for manual review; full-auto merges all three after waiting for the GitHub Release object to exist (proxy for build-release-on-tag completing). See `.github/workflows/ship-release.yml` header for the full mode matrix.

- **When to escalate to a human / org owner.** An automated agent cannot: merge the conductor PR (cuts a public tag — a go/no-go decision), or set/rotate the release-App credentials (`vars.RELEASE_APP_CLIENT_ID` and `secrets.RELEASE_APP_PRIVATE_KEY`). If a consumer's auth fails, run that repo's `release-permissions-check.yml`; if it reports a missing credential or permission, stop and escalate — only an org owner can fix it.

## Release runbook

The complete ordered checklist for cutting a release. Each step is marked **[Automated]**, **[Manual]** (will be automated later — see [Automation gaps](#automation-gaps)), or **[Manual — judgment]** (irreducibly manual; requires human input).

### Phase 1 — Pre-release QA

1. **[Manual — judgment]** Confirm pre-release QA is complete. The QA process (test plan, regression coverage, sign-off) lives on the [QA and Release Process wiki page](https://www.open-emr.org/wiki/index.php/QA_and_Release_Process). QA runs in parallel with the release-prep PR being continuously regenerated; the sign-off is what authorizes the conductor PR merge in step 9 — not the branch cut or any prep-PR update. The maintainers authorized to merge the conductor PR are the QA team, so the merge button is itself the QA gate; no separate sign-off mechanism is required.

### Phase 2 — Branch cut and PR generation

2. **[Manual — judgment]** Cut the release branch: `rel-<MAJOR><MINOR>0` (e.g. `rel-810`) from `master`. This is the only step that creates new state from nothing. The demo-farm side requires no manual seeding for new minor lines — the `derive-ip-map` bot in `demo_farm_openemr` derives `ip_map_branch.txt` and `demoLibrary.source` entirely from upstream `openemr/openemr` state (chiefly `.github/release-targets.yml` plus per-rel-branch Dockerfile ARGs), so a new minor flows through naturally once it's represented in `release-targets.yml` (see step 15).
3. **[Automated]** Conductor workflow (`release-prep.yml` in `openemr/openemr`) opens or updates the `release-prep/<rel-branch>` draft PR with all mechanical version bumps plus a regenerated `CHANGELOG.md` entry (via `ChangelogMutator` + `CompatibilityMutator`; see the Conductor PR section). Re-fires on every relevant push.
4. **[Automated]** Docs workflow (in `website-openemr`) opens or updates the `release-docs/<version>` draft PR with acknowledgements, the release-status shortcode (rendering `DRAFT — based on rel-* @ <sha>`), and Hugo aliases.
5. **[Automated]** Continuous cycle: as further commits land on the rel-branch during the dev cycle, `release-prep.yml` re-fires on every push and both draft PRs regenerate (including a fresh `CHANGELOG.md` block). No maintainer action in this window — iterate until QA sign-off and the release-cycle commits have settled.

### Phase 3 — Manual editorial work (in the open PRs)

6. **[Manual — judgment]** In the `openemr/openemr` `release-prep/<rel-branch>` PR, edit the auto-generated `CHANGELOG.md` entry for tone and what's noteworthy — this is the canonical release notes source and is what the GitHub Release body extracts at build time (step 10). The mutator wholesale-replaces the target-version section on every rel-branch push, so hand-edits only survive if made *after* all release-cycle commits have landed. In practice: hold edits until the release-prep churn has settled, then commit them directly to the PR branch as the last change before triggering ship-release. Ship-release does not re-run release-prep, so a commit made straight to the PR branch is safe once no further rel-branch pushes are expected.
7. **[Manual — judgment]** *(Major releases only)* In the `website-openemr` PR, sign off on the ONC Ambulatory EHR Certification Requirements page.
8. **[Manual — judgment]** *(Major releases only)* Write the marketing piece for the website.

### Phase 4 — Ship: merge the bot-created PRs

The conductor merge creates the annotated tag (which flips the docs PR's banner from DRAFT to FINAL and fires the `openemr-tag` cascade for the Release object). Announcement drafts fire later — on the docs PR merge itself (runbook step 16), not on `openemr-tag`. The conductor PR creates the tag first; the remaining bot-created PRs land after the tag exists, in the following order:

Listed here in the enforced merge order (Conductor → Finalize → Docs). Docs is last because merging it will trigger the future auto-announce pipeline; by then packages + dockers should be as-ready-as-possible. Finalize before Docs so its `release-targets.yml` update starts the docker cascade earlier (dockers publish independently on cron regardless, so this ordering is nice-to-have not strictly required).

1. **Conductor PR** (`openemr/openemr` `release-prep/<rel-branch>`) — merges to rel-branch, creates the annotated tag. Includes the finalized `CHANGELOG.md` entry for the rel-branch side. *Merged by ship-release (step 9) in every mode except dry-run.*
2. **Finalize-on-master PR** (`openemr/openemr` `release-finalize/<rel-branch>`) — auto-updated by the `finalize` job post-tag, flipped from draft to ready-for-review with a signal comment; lands the master-side `release-targets.yml` rotation and the matching master-side `CHANGELOG.md` entry (regenerated post-tag against `vNEW` so master and rel-branch land the identical block). Don't merge while it's still in draft state — the draft flag is the "post-tag update hasn't happened yet" indicator. *Merged by ship-release in full-auto mode after waiting for the GitHub Release object to exist (blocks packaging failures from cascading to dockers); in semi-auto mode, marked SKIPPED_BY_MODE and left for the maintainer to merge manually after review.*
3. **Docs PR** (`openemr/website-openemr` `release-docs/<version>`) — ships the now-FINAL pages on the website. *Merged by ship-release in full-auto mode last (packages already verified to exist per step 2's wait, so download links resolve when the future auto-announce fires); in semi-auto mode, marked SKIPPED_BY_MODE and left for manual merge.*

The demo-farm reconciliation runs on its own track — see step 15.

> **Docs PR auto-flip (full-auto mode):** the `release-docs/<version>` PR on `website-openemr` is opened as a GitHub **draft** by its generator workflow and stays draft while it accumulates pre-tag content on `openemr-rel-cut` / `openemr-rel-update` events. On the post-tag `openemr-tag` dispatch (fired by the conductor merge in step 9 below), the docs workflow automatically flips the PR out of draft, drops the `(DRAFT)` title suffix, and posts a signal comment — full-auto ship-release's post-conductor wait then clears immediately and the merge proceeds. Semi-auto mode also benefits: the docs PR arrives at the maintainer's queue already Ready-for-review by the time the operator is ready to merge it manually.

9. **[Automated]** Run the **ship-release workflow** in `openemr/openemr` (`workflow_dispatch` on `.github/workflows/ship-release.yml`, or `task ship-release` locally for a dry-run). One operator action: pick the version + rel-branch + mode and trigger.

   **Modes (`mode` input, default `semi-auto`):**

   - **`semi-auto`** (default): merges Conductor PR only. Docs + Finalize PRs marked SKIPPED_BY_MODE (still success — exit 0), left for maintainer to review + merge manually. Use for the first 1-2 releases after wiring up the automation so surprising mutator/EHI/finalize output can be caught in review before committing to full-auto.
   - **`full-auto`**: merges all three (Conductor → Finalize → Docs). Conductor first, then waits for the GitHub Release object to exist (proxy for build-release-on-tag completing package assembly + upload — **blocks both downstream merges if packaging failed so dockers + announcements don't fire on top of a broken release; operator debugs + reruns**), then merges Finalize (which triggers the docker cascade via its `release-targets.yml` update on master), then Docs last (which will trigger the future auto-announce pipeline). Waits for each downstream PR's post-tag HEAD SHA update + readiness (asymmetric approval gate: Conductor requires APPROVED, Docs + Finalize don't since they're bot-authored). True "one command go" — the docs PR's post-tag auto-flip (website-openemr `release-docs.yml`) removes the previously-manual mark-Ready step.
   - **`dry-run`**: preflight + dress-rehearsal build. Probes every PR's readiness and prints a report (merges nothing); on preflight success, the workflow layers a `dry-run-build` job that invokes the reusable `build-release.yml` with `dry_run=true`, pinning its checkout to the Conductor PR head (`release-prep/<rel_branch>`) so the packaged tree carries the pending version bump + `## [X.Y.Z]` CHANGELOG entry that `extract-changelog-section.php` requires. Produces the actual tarball, zip, changelog, and checksums as workflow-run artifacts — no git tag is created, no GitHub Release is published, no downstream dispatches fire. Download the artifacts from the run page to eyeball what would ship before firing a real ship-release. `dry_run: true` still supported as a legacy alias and always wins over `mode` when set.

   The workflow locates the 3 sibling PRs by branch convention, posts a `release/ship-approved` commit status on each PR head before merging it, and enforces merge order Conductor → Finalize → Docs with mergeability gates between steps. Already-merged PRs are detected and skipped (so the same trigger handles the replayable PR-merge recovery cases — see [Partial merges and recovery](#partial-merges-and-recovery); docs-first and out-of-band-tag states still need manual handling). In full-auto mode, the docs PR's post-tag auto-flip (see the prerequisite note above) clears the Ready gate without operator input.

   **Manual fallback** (only if the workflow is unavailable): merge in order — conductor PR (creates the annotated tag), then docs PR (flips DRAFT → FINAL). Direct merges should be blocked by branch protection requiring the `release/ship-approved` status the workflow posts; admin-override the protection only if the workflow itself is broken.

### Phase 5 — Post-merge artifact and download verification

10. **[Automated]** Create the **GitHub Release object** on `openemr/openemr` for the new tag and attach the build artifacts. This is the canonical (and only) distribution target — SourceForge is no longer supported, and the website's `/downloads/` and `/releases/` pages link directly to assets on the Release object (see step 14). A tag alone is not enough: without a Release object the website's "Download" button 404s.

    The Release object must include:
    - **Distribution packages** (`openemr-<version>.tar.gz`, `openemr-<version>.zip`) — full, ready-to-run installs with production Composer dependencies and compiled front-end assets baked in and dev/test cruft pruned (per openemr/openemr's `.gitattributes export-ignore` and `build.xml`). These are *not* GitHub's auto-generated "Source code" archives; they are built and uploaded by the workflow below.
    - **Checksums** (`.md5`, `.sha256`, `.sha512`) for each distribution package.
    - **`changelog.md`** — the generated release notes.

    This runs automatically: [`build-release-on-tag.yml`](../.github/workflows/build-release-on-tag.yml) in this repo consumes the conductor's `openemr-tag` dispatch (intra-repo since workstream 7 Phase 2 landed 2026-07-21; previously ran in `openemr-devops`), derives the build inputs from the payload, and calls the reusable [`build-release.yml`](../.github/workflows/build-release.yml) with `dry_run=false`. That workflow extracts the target version's section from the just-tagged `CHANGELOG.md` via [`extract-changelog-section.php`](../tools/release/bin/extract-changelog-section.php) (the section content is already pre-computed by the conductor's `ChangelogMutator` + `CompatibilityMutator` — see the Conductor PR section — so build-release just slices the block out and hands it to `gh release create`); builds the packages with `task package:assemble` (`git archive HEAD` → `composer install --no-dev` → `npm ci && npm run build` → prune via `build.xml` phing targets); then its "Create annotated tag and GitHub release" step is no-op-safe when the tag already exists and proceeds to `gh release create --verify-tag --notes-file changelog.md`, generates the checksum sidecars, and uploads the packages + checksums + changelog with `gh release upload --clobber`. `build-release.yml` remains available as a manual `workflow_dispatch` fallback (`dry_run=false`, the conductor-created tag in `release_tag`). Closing this gap was tracked in [openemr/openemr-devops#756](https://github.com/openemr/openemr-devops/issues/756).
11. **[Manual — judgment]** Verify the Release object on the [GitHub releases page](https://github.com/openemr/openemr/releases): distribution packages downloadable, all three checksum files present, changelog rendered.
12. **[Automated]** Docker images for the new release build via the workflows in **this repo** (`.github/workflows/docker-release-orchestrator.yml` fans out per-branch `docker-build-release.yml` dispatches). Triggered by the daily orchestrator cron or by a master push touching `.github/release-targets.yml` (which reflects the just-shipped state after the finalize-on-master PR merges). See [`docs/docker-migration-from-devops.md`](docker-migration-from-devops.md) for why this lives here rather than in `openemr-devops`.
13. **[Automated]** The DockerHub readme (per-version description on [hub.docker.com/r/openemr/openemr](https://hub.docker.com/r/openemr/openemr)) is updated by `.github/workflows/docker-push-dockerhub-readme.yml` in **this repo**, which fires via `workflow_run` after each orchestrator run. Source template at [`docker/dockerhub/overview.md`](../docker/dockerhub/overview.md).
14. **[Automated]** The Downloads landing page and the historical release table on `website-openemr` (`/downloads/` and `/releases/`) re-render from `data/releases.json`, which the docs PR workflow updates on every dispatch. The `/downloads/` page's "Download" buttons link to the Release-object assets from step 10 — if step 10 is skipped, those buttons 404. The legacy [OpenEMR Downloads wiki page](https://www.open-emr.org/wiki/index.php/OpenEMR_Downloads) is no longer the source of truth and should be edited by hand to a one-line pointer to https://www.open-emr.org/downloads/.

Standalone patch releases (`v<MAJOR>_<MINOR>_<PATCH>_<N>` tags with a `<M>-<m>-<p>-Patch-<N>.zip` asset) are not part of the automated cadence: the [OpenEMR Patches wiki page](https://www.open-emr.org/wiki/index.php/OpenEMR_Patches) and its download list go away once automated tagged releases ship security and bug fixes on a regular interval. The wiki page should be edited by hand to point readers at the most recent regular release on the [Downloads](https://www.open-emr.org/downloads/) page.

### Phase 6 — Demo and promotion

15. **[Automated]** Point the demo farm (live demo servers at open-emr.org) at the new upstream state. The `derive-ip-map` workflow in [`openemr/demo_farm_openemr`](https://github.com/openemr/demo_farm_openemr/blob/master/.github/workflows/derive-ip-map.yml) is the canonical writer for `ip_map_branch.txt` + `docker/scripts/demoLibrary.source`. It regenerates both files from upstream `openemr/openemr` state — `.github/release-targets.yml`, each rel branch's Dockerfile ARGs (PHP/MySQL/Apache pins), the `ci/apache_*` snippet listing, and the flex Dockerfile — and reconciles them onto the `auto-derive/reconciliation` stable branch. On diff it force-pushes the branch and opens (or updates) a reviewable PR titled `[auto-derive] reconcile demo_farm against upstream openemr master`; on no-diff it closes any open reconciliation PR and deletes the remote branch. A maintainer reviews and merges the PR and manually updates the wiki page(s) if needed; the demo-farm host's nightly reset then picks up the new pins.

    The bot runs on three triggers: (a) `repository_dispatch types: [release-targets-changed]`, fired immediately by this repo's [`notify-release-targets-changed.yml`](../.github/workflows/notify-release-targets-changed.yml) on master pushes that touch `.github/release-targets.yml`; (b) a daily 07:00 UTC cron as a self-healing fallback; (c) `workflow_dispatch` for manual reruns. Because release-targets.yml is the contract, new minor lines flow through naturally as soon as their entry lands on master — no manual seeding required.
16. **[Manual]** Announce the release:
    - Forums
    - Chat
    - Twitter / X
    - Facebook
    - LinkedIn (group + company page)
    - Registered-users mailing list

    Per-channel drafts are auto-generated by [`release-announcements.yml`](https://github.com/openemr/website-openemr/blob/master/.github/workflows/release-announcements.yml) in `website-openemr`. It fires on the docs PR's `pull_request: closed` event (specifically: `release-docs/<version>` head_ref merged into master) — the same moment the download page goes live, so announcements are drafted exactly when the release is "really out there." A `workflow_dispatch` fallback with `version`/`tag`/`branch`/`forum_url` inputs handles manual re-renders when the pull_request path missed or the drafts artifact was lost. The maintainer copy/pastes the rendered drafts onto the short-copy channels and runs [`oe-sender.js`](https://github.com/openemr/openemr-registration/blob/master/oe-sender.js) against the rendered `mail.html` + `mail.subject.txt` for the mailing list. Posting is still manual; the drafting half is automated.

## Automation gaps

This section tracks every post-automation gap, not just the manual runbook steps: currently-manual steps (marked **[Manual]** in the runbook above), missing guards, and recovery tooling. None of them are irreducibly manual; they're tracked for follow-on automation. Closed rows stay in the table with a strikethrough + "Closed <date> by <PR>" annotation — the historical framing (why the gap existed, what constraint drove it) explains what changed and is useful when a similar future gap comes up. The "Step" column references the runbook step a gap relates to, or "Recovery" for gaps in the partial-merge recovery path:

| Step | What | Tracking |
| --- | --- | --- |
| 9 | ~~Auto-mark the `website-openemr` docs PR Ready **before `ship-release.yml` is dispatched**~~ **Closed 2026-07-22 by [openemr/website-openemr#207](https://github.com/openemr/website-openemr/pull/207)** — the docs workflow now auto-flips the PR out of draft on the post-tag `openemr-tag` dispatch (which is emitted after the conductor merge; full-auto ship-release's post-conductor wait blocks on `isDraft==false` and clears as soon as the flip lands). Note that the original design constraint noted in the pre-close text — "pre-ship signal earlier than `openemr-tag` is needed to unblock preflight" — no longer applies; the wait was moved from preflight to a post-conductor readiness check in Phase 3b (#13098), so an on-`openemr-tag` flip is exactly the right unblock. | [openemr/website-openemr#207](https://github.com/openemr/website-openemr/pull/207) |
| 10 | Verify the `openemr-tag` exists in `openemr/openemr` before the docs workflow flips pages DRAFT→FINAL — a guard so docs can never ship FINAL for a version that was never tagged | [openemr/website-openemr#132](https://github.com/openemr/website-openemr/issues/132) |
| 16 | Automated post-release announcement fan-out (forums, chat, social, mailing list) — drafting is automated in `openemr/website-openemr`; only the actual posting to each channel is still manual. Design constraint for the future automated posting phase (mandatory `--dry-run` + golden fixtures on every poster to keep test/dev runs safe) tracked in [openemr/website-openemr#197](https://github.com/openemr/website-openemr/issues/197); bot-login gate defense-in-depth in [openemr/website-openemr#195](https://github.com/openemr/website-openemr/issues/195). Umbrella issue: [openemr/openemr-devops#711](https://github.com/openemr/openemr-devops/issues/711). | [openemr/website-openemr#197](https://github.com/openemr/website-openemr/issues/197) |
| Recovery | Docs-first reconciliation workflow — re-render orphaned FINAL pages against the real tag after a docs-first partial merge (see [Docs-first recovery](#docs-first-recovery-manual-today)) | [openemr/website-openemr#133](https://github.com/openemr/website-openemr/issues/133) |

Recently closed: the Release-object creation for **runbook step 10** (automated GitHub Release object creation + checksum/changelog upload on `openemr-tag`) shipped via [openemr/openemr-devops#757](https://github.com/openemr/openemr-devops/pull/757), closing [#756](https://github.com/openemr/openemr-devops/issues/756). The v8.1.0 release surfaced the gap — tag landed, no Release object did; `build-release-on-tag.yml` now creates the Release object automatically when the conductor emits `openemr-tag`. The remaining open gap in the table's step-10 row, #132, is a different concern: the **docs DRAFT→FINAL tag-exists guard**, not the Release-object creation. Also closed: the demo-farm new-minor-line seeding gap ([openemr/demo_farm_openemr#110](https://github.com/openemr/demo_farm_openemr/issues/110)) was retired by the auto-derive bot rewrite (2026-06-28) — the bot derives `ip_map_branch.txt` from `release-targets.yml` instead of advancing pre-seeded rows, so new minor lines no longer require any manual seeding step. Also: the **announcement-drafting migration** from `openemr-devops` to `openemr/website-openemr` shipped 2026-07-18 through 2026-07-19 (`website-openemr#192/#193/#194/#198` + `openemr-devops#861`) — announcements now fire on the docs PR merge (download-page-live moment) instead of `openemr-tag`. The **drafting** side of step 16 is fully automated; only the actual per-channel **posting** remains manual (that's the gap the row above tracks).

Umbrella issue tracking the full gap closure: [openemr/openemr-devops#706](https://github.com/openemr/openemr-devops/issues/706). Its open sub-issues are the four rows above: [openemr-devops#711](https://github.com/openemr/openemr-devops/issues/711) (posting-phase umbrella; concrete next steps live at [website-openemr#195](https://github.com/openemr/website-openemr/issues/195) + [website-openemr#197](https://github.com/openemr/website-openemr/issues/197)), [openemr-devops#761](https://github.com/openemr/openemr-devops/issues/761), [website-openemr#132](https://github.com/openemr/website-openemr/issues/132), and [website-openemr#133](https://github.com/openemr/website-openemr/issues/133).

## Partial merges and recovery

The two PRs are coupled only by `repository_dispatch`. Branch protection should block direct merges and require the [ship-release workflow](https://github.com/openemr/openemr-devops/blob/master/.github/workflows/ship-release.yml) as the only merge path (via the `release/ship-approved` commit status the workflow posts), but admin-overrides and misconfigurations happen — this section documents the recovery path when they do.

**Re-running `ship-release.yml` is the normal recovery mechanism for partial *PR merges*, not a special bootstrap path.** Its idempotency is scoped to PR-merge state: it snapshots both sibling PRs, skips any already merged, and merges the rest in order (conductor → docs) after a readiness check. So re-triggering is safe when one of the PRs merged (by admin-override or a prior interrupted run) and the other is still open and ready. Treat "re-run ship-release and let it reconcile" as the default response to a stuck *PR-merge* state.

What it does **not** do today is inspect the annotated tag or the GitHub Release object — it never reads `refs/tags` or the Release API. The tag is created as a side effect of merging the conductor PR (`release-prep.yml` runs `create-tag.php` on merge), and the Release object follows from the `openemr-tag` dispatch. This matters for one state re-running ship-release cannot fix: a tag that already exists with the conductor PR still open (see the out-of-band-tag row below). The partial-merge table enumerates the states re-running recovers from and the two it does not (docs-first and out-of-band-tag).

**Desired end state:** ship-release should be fully idempotent across the whole release, not just PR-merge state. It should read the tag and Release object up front, treat an already-existing correct tag as a completed step (rather than letting the conductor merge blindly re-attempt `create-tag.php` and fail), and fire the `openemr-tag` dispatch itself if it never ran. Reaching that retires the manual out-of-band-tag recovery below and makes "re-run ship-release and let it reconcile" the answer for *every* non-docs-first state. This is not yet built; until it is, the manual recovery applies.

### Partial-merge states

| Merged | Effect |
| --- | --- |
| Conductor only | Annotated tag exists; the Release object follows automatically once `build-release-on-tag.yml` in `openemr-devops` consumes the `openemr-tag` dispatch and finishes the build (step 10). No Release object is now a transient state, not a resting one. Website still advertises the prior version. GitHub's auto-generated source archives exist as soon as the tag does; the full distribution packages (and the website's "Download" buttons that point at them) appear when that build completes. |
| Docs only | Cannot reach FINAL — the DRAFT/FINAL banner is driven by the `openemr-tag` event, which the conductor never emitted. Merging publishes pages permanently stamped DRAFT for a version that was never tagged. **Worst case.** See "Docs-first recovery" below. |
| None merged, but tag already exists | The annotated tag exists, yet both sibling PRs are still open — the tag was cut out-of-band rather than by a conductor-PR merge. Two sub-cases depending on whether the `openemr-tag` dispatch already fired: (a) **dispatch fired**: the docs PR may already be FINAL and `build-release-on-tag.yml` may already have run, so downstream state is partial-but-non-trivial; (b) **dispatch did not fire**: ship-release fails preflight on the docs PR's still-DRAFT state before any conductor merge attempt. In either sub-case, **re-running ship-release does not fix this**: ship-release inspects only PR state, so once docs is marked Ready it tries to merge the still-open conductor PR, whose merge runs `create-tag.php` — and that fails (HTTP 422 on `POST /git/refs`) because the tag already exists, aborting the conductor step. Recovery is manual today (see [Out-of-band tag recovery](#out-of-band-tag-recovery-manual)); the [desired end state](#partial-merges-and-recovery) is for ship-release to recognize the existing tag/Release and reconcile this automatically. This state has happened in practice: v8.1.0's Release object published while its conductor and docs PRs remained open — `release-docs.yml` carries dedicated retroactive-8.1.0 fallback logic because that tag's SHA predates `b10-tables.yml`, which is what let the tag land ahead of its PRs. (As always, discover the actual current state from Git and the GitHub API before acting; the v8.1.0 case is an example, not a standing assertion about today.) |

### Recovery

For partial *PR-merge* states (one sibling PR merged, the other still open and ready) **except docs-first and out-of-band-tag**: re-trigger the ship-release workflow. Its idempotency is scoped to PR-merge state (see the section intro) — it re-reads the PRs, detects the already-merged one, skips it, and merges the other in dependency order with the same preconditions check (mergeable + green + required approvals). The website may serve stale content until the workflow completes, but no manual intervention is needed.

Re-running ship-release does **not** cover the case where the tag already exists with the conductor PR still open — it inspects only PR state, so it would attempt the conductor merge and fail when `create-tag.php` hits the existing tag. See [Out-of-band tag recovery](#out-of-band-tag-recovery-manual) for that case.

### Docs-first recovery (manual, today)

This is the worst case and recovery is currently manual. The ship-release workflow detects docs-first up front and **refuses to do anything** — it will not even merge the conductor PR, because doing so would create a tag for a version whose docs have already shipped FINAL with no tag link. A docs-side reconciliation workflow to automate the recovery is tracked in [openemr/website-openemr#133](https://github.com/openemr/website-openemr/issues/133); until it ships, follow the manual steps below.

The docs PR has already shipped FINAL pages for a version that has no tag yet. After the operator manually merges the conductor (creating the tag), the existing FINAL-flip mechanism doesn't help — it fires on docs-PR updates, but the docs PR is already merged and closed. The published pages are orphaned: they reference a version that now exists, but with stale DRAFT-era SHAs and no tag link.

Manual steps:

1. Manually merge the conductor PR (the ship-release workflow refuses to act once docs-first is detected; admin-override the branch protection or merge directly via the GitHub UI). This creates the tag.
2. In `openemr/website-openemr`, open a follow-up PR that re-renders the affected acknowledgements page and release-status shortcode against the now-real tag (install/upgrade default to version-agnostic wiki targets and don't need a re-render; the changelog lives in `openemr/openemr`'s `CHANGELOG.md` and does not participate in this recovery). Easiest path is to manually re-run the docs-PR generator script with the new tag SHA, commit the regenerated output, and merge.
3. Verify the live website pages now show the FINAL banner with the correct tag link, not DRAFT.
4. If anyone scraped or linked the DRAFT-stamped pages between merge and reconciliation, the URLs are stable — they now serve correct FINAL content.

Folding this reconciliation into a workflow is tracked in [openemr/website-openemr#133](https://github.com/openemr/website-openemr/issues/133). Scope it once docs-first has happened in practice (or the user-facing impact justifies preemptive automation).

### Out-of-band tag recovery (manual)

This is the "tag exists, all PRs still open" row above — the tag was created outside the conductor-merge path. (This has happened in practice: it was v8.1.0's state. As always, discover the real current state before acting rather than assuming this is today's.) Recovery is manual because **re-running ship-release does not work here**: ship-release inspects only PR state, sees the conductor PR open, and tries to merge it; the conductor's post-merge tag step then fails with HTTP 422 because `refs/tags/<tag>` already exists. The squash-merge lands but the tag/dispatch step errors, leaving a half-finished conductor merge.

The reason the conductor PR can't simply be merged: `release-prep.yml`'s `finalize` job runs `create-tag.php` on the merge of **any** PR whose head ref starts with `release-prep/` (or `release-prep-test/`) — there is no in-PR switch to skip it. So merging the existing conductor PR as-is always re-attempts the tag and fails on the existing ref.

First discover the real state from Git and the GitHub API — which PRs are open, what SHA the existing tag points at, whether the `openemr-tag` dispatch already fired (i.e. whether the Release object and downstream consumers are up to date). Then reconcile by hand:

1. **Conductor version bumps:** the conductor PR's two effects are the version-bump edits (still needed) and the tag (already done). Land the version-bump content via a branch **not** named `release-prep/*`, so the `finalize` tag job doesn't trigger: open a new PR from a plain branch (e.g. `release-reconcile/<version>`) carrying the same edits against the rel-branch, merge it, and close the original `release-prep/*` PR unmerged. Confirm the existing tag already points at the intended commit.
2. **Dispatch:** if the `openemr-tag` dispatch never fired for the existing tag (Release object or downstream consumers not up to date), fire it manually so `build-release-on-tag.yml` and the consumer repos pick it up.
3. **Docs:** once the `release-prep/*` PR is closed, merge/flip docs to FINAL against the now-confirmed tag (re-run ship-release if the conductor PR is closed and the docs PR is the only remaining one).
4. Verify the website advertises the new version and docs show FINAL with the correct tag link.

This whole manual path exists only because ship-release is PR-state-only today. The [desired end state](#partial-merges-and-recovery) — ship-release reading the tag/Release object and treating an already-correct tag as done — would let the operator just re-run the workflow here instead of hand-reconciling. Until that lands, the steps above are the path.

## Naming and tag conventions

| Thing | Pattern | Example |
| --- | --- | --- |
| Release branch | `rel-<MAJOR><MINOR>0` | `rel-810` |
| Release tag | `v<MAJOR>_<MINOR>_<PATCH>` | `v8_1_0` |
| Hugo version param | `<MAJOR>.<MINOR>.<PATCH>` | `8.1.0` |
| Conductor PR branch | `release-prep/<rel-branch>` | `release-prep/rel-810` |
| Docs PR branch | `release-docs/<version>` | `release-docs/8.1.0` |

**Tags are always annotated** (Git object type `tag`, not `commit`). Lightweight tags lack author/date/message metadata and break `git describe`, downstream tooling, and consumers that introspect tag objects. The `TagVerifier` enforces this in CI on all three repos.

The tag message follows this template:

```
OpenEMR <version> released <YYYY-MM-DD>

Conductor PR: <url>
Merge commit: <sha>

Created by openemr-release-bot via automation
```

Tags are unsigned; the trailer line records that automation produced them. Revisit if maintainers later want signed tags.

## Bot identity and credentials

A GitHub App (the "release app") performs all automated git/PR actions. Every workflow mints a short-lived installation token via `actions/create-github-app-token`, authenticating with the App's client ID and private key — `client-id: ${{ vars.RELEASE_APP_CLIENT_ID }}` and `private-key: ${{ secrets.RELEASE_APP_PRIVATE_KEY }}`. `RELEASE_APP_CLIENT_ID` is an **organization variable** (the App's public client ID, not a secret) and `RELEASE_APP_PRIVATE_KEY` is an **organization secret**. Because they're set once at the org level, they're available to the release workflows in all four repos automatically — there's no need to duplicate them repo-by-repo. The App is installed on each repo.

Each repo carries a `release-permissions-check.yml` workflow (manual `workflow_dispatch`) that mints an App token and probes every permission the workflow needs — branch create/delete, PR open/close, tag create/delete, cross-repo `repository_dispatch`. Run it after installing the App or rotating the credentials; it fails loudly with the missing permission name.

This repo's check is at [`.github/workflows/release-permissions-check.yml`](../.github/workflows/release-permissions-check.yml).

## Slice plans

Each repo's slice has its own plan document with the per-slice mechanical detail (mutators, registry shape, consumer wiring, hypotheses, testing strategy):

- **Conductor:** [`docs/release-automation-plan.md`](release-automation-plan.md) in this repo.
- **Docs:** the website-openemr slice was implemented in [openemr/website-openemr#82](https://github.com/openemr/website-openemr/pull/82); see the PR description for its design.

(The former `openemr-devops` rotation-slice plan was retired when the
docker-pipeline migration removed all of its live targets.)

## Checklist templates

The conductor PR description embeds a maintainer-facing checklist of irreducibly-manual steps. The full and patch-release templates live in `openemr-devops`:

- [`tools/release/templates/full-checklist.md`](https://github.com/openemr/openemr-devops/blob/master/tools/release/templates/full-checklist.md)
- [`tools/release/templates/patch-checklist.md`](https://github.com/openemr/openemr-devops/blob/master/tools/release/templates/patch-checklist.md)

These templates are scoped to the devops slice; the conductor PR template (in this repo) collects the cross-repo checklist the release manager actually walks through.

## Wiki

The wiki pages [QA and Release Process](https://www.open-emr.org/wiki/index.php/QA_and_Release_Process) and [Steps for an official release](https://www.open-emr.org/wiki/index.php/Steps_for_an_official_release) historically described the manual flow that this automation replaces. Once the automated flow has cut its first release, those pages should be rewritten as short pointers to this document plus the manual checklist — keeping the URLs contributors already know without leaving stale step-by-step instructions in two places.

# OpenEMR release process

Maintainer-facing reference for cutting an OpenEMR release. The release flow
spans three repositories and a binaries target, coordinated by cross-repo
[`repository_dispatch`](https://docs.github.com/en/actions/reference/events-that-trigger-workflows#repository_dispatch)
events. This document is the single end-to-end view; per-slice plans and
implementation detail live in the linked plan docs.

| Repo                                                                                     | Role                                                                |
| ---------------------------------------------------------------------------------------- | ------------------------------------------------------------------- |
| [`openemr/openemr`](https://github.com/openemr/openemr)                                  | Conductor. Owns the release-prep PR, the annotated tag, and the dispatches. |
| [`openemr/website-openemr`](https://github.com/openemr/website-openemr)                  | Docs. Owns the release-notes, install/upgrade, acknowledgements, ONC pages. |
| [`openemr/openemr-devops`](https://github.com/openemr/openemr-devops)                    | Infra. Owns the test-matrix rotation and Docker/Kubernetes pins.    |
| [`openemr/website-openemr-files`](https://github.com/openemr/website-openemr-files)      | Binaries target. Receives generated API docs and download artifacts.  |

## Cross-repo flow

```mermaid
flowchart TD
    M([Maintainer])

    subgraph OE [openemr/openemr — conductor]
        OERel[/rel-NNN0 branch/]
        OEPrep[release-prep PR<br/>chore release: prep X.Y.Z]
        OETag[/annotated tag vX_Y_Z/]
    end

    subgraph WO [openemr/website-openemr — docs]
        WOPrep[release-docs PR<br/>release-docs: OpenEMR X.Y.Z DRAFT]
    end

    subgraph DO [openemr/openemr-devops — infra]
        DOPrep[release-rotation PR]
    end

    subgraph WOF [openemr/website-openemr-files]
        WOFBin[/API docs + binaries/]
    end

    M -- "1. cut rel-NNN0 from master" --> OERel
    OERel -- push --> OEPrep
    OEPrep -- openemr-rel-cut / openemr-rel-update --> WOPrep
    OEPrep -- openemr-rel-cut / openemr-rel-update --> DOPrep

    M -- "2. edit notes + ONC signoff" --> WOPrep
    M -- "3. confirm green" --> DOPrep
    M -- "4. merge release-prep" --> OEPrep
    OEPrep --> OETag

    OETag -- openemr-tag --> WOPrep
    OETag -- openemr-tag --> DOPrep
    WOPrep -- openemr-docs-binaries --> WOFBin

    M -- "5. merge docs + infra PRs" --> WOPrep
    M -. .-> DOPrep
```

Maintainer actions are the five numbered steps. Everything else is automation.

## Dispatch events

The canonical payload schema lives in
[`openemr-devops/tools/release/contracts/dispatch.schema.json`](https://github.com/openemr/openemr-devops/blob/master/tools/release/contracts/dispatch.schema.json);
each consumer vendors a copy and CI fails on drift.

| Event                   | Emitter                                              | Consumers                                | When fired                                                  |
| ----------------------- | ---------------------------------------------------- | ---------------------------------------- | ----------------------------------------------------------- |
| `openemr-rel-cut`       | `openemr/openemr` (Release Prep Conductor)           | `website-openemr`, `openemr-devops`      | First push to a new `rel-NNN0` branch (release-prep PR opens). |
| `openemr-rel-update`    | `openemr/openemr` (Release Prep Conductor)           | `website-openemr`, `openemr-devops`      | Subsequent pushes to the same `rel-NNN0` branch.            |
| `openemr-tag`           | `openemr/openemr` (Release Prep Conductor finalize)  | `website-openemr`, `openemr-devops`      | After the release-prep PR merges and the annotated tag is pushed. |
| `openemr-docs-binaries` | `website-openemr` (release-docs)                     | `website-openemr-files`                  | After the docs PR merges and generated artifacts are ready. |

## Maintainer steps

The numbered points correspond to the steps on the diagram. Everything not
numbered is mechanical; if a maintainer ever has to fix it by hand, it's a bug
in the automation.

### 1. Cut the `rel-NNN0` branch

From `openemr/openemr` `master`:

```bash
git fetch upstream
git checkout -b rel-810 upstream/master
git push upstream rel-810
```

Branch naming pattern is `rel-<MAJOR><MINOR>0` (e.g. `rel-810` for 8.1.0). The
conductor's push trigger rejects off-pattern branches; test runs use
`workflow_dispatch` with `test=true` instead.

That push fires the Release Prep Conductor
([`release-prep.yml`](../.github/workflows/release-prep.yml)), which:

- Runs the mechanical edits (`version.php`, OpenAPI version, docker pins,
  `docker-version` bump, etc. — see
  [`docs/release-automation-plan.md`](release-automation-plan.md)).
- Opens or force-updates a draft PR `release-prep/rel-NNN0` titled
  `chore(release): prep X.Y.Z`.
- Dispatches `openemr-rel-cut` (first push) or `openemr-rel-update`
  (subsequent pushes) to `website-openemr` and `openemr-devops`.

The release-prep branch is force-pushed on every conductor run; **do not
commit directly to it**. Mechanical errors get fixed at the source (PR
against the base `rel-*` branch or against `master`) — the next conductor run
re-renders the prep PR.

### 2. Edit the release-notes draft and sign off the ONC cert page

In the docs PR opened in `openemr/website-openemr` by the `openemr-rel-cut`
dispatch (title `release-docs: OpenEMR X.Y.Z (DRAFT)`):

- Edit `content/release-notes/X.Y.Z.md` — the draft is auto-generated from
  conventional-commits between `vPREV..HEAD`; pare it down to the
  release-relevant headlines.
- Sign off `content/onc-certification/X.Y.Z.md` (majors and any release that
  affects certified functionality).

Acknowledgements (`content/acknowledgements/X.Y.Z.md`) and the install /
upgrade pages are fully auto-generated and don't need maintainer review for
typical releases.

### 3. Confirm the `openemr-devops` rotation PR is green

The rotation PR (long-lived, title `chore(release): rotate test matrix to
X.Y.Z`) opens automatically against `openemr-devops/master` on the same
dispatch. CI must be green before merge; if it isn't, the failure is in the
matrix rotation itself, not the release content.

See [`openemr-devops/docs/release-automation-plan.md`](https://github.com/openemr/openemr-devops/blob/master/docs/release-automation-plan.md)
for what the rotation actually changes.

### 4. Merge the release-prep PR

Merging `release-prep/rel-NNN0` in `openemr/openemr` is the "we're shipping"
decision. On merge the conductor's `finalize` job runs:

- Creates an **annotated** tag `v<MAJOR>_<MINOR>_<PATCH>` on the merge commit
  (lightweight refs are explicitly avoided — they lack author/date/message
  metadata and break `git describe` and downstream tooling).
- Dispatches `openemr-tag` to `website-openemr` and `openemr-devops`.

The `openemr-tag` dispatch flips the docs entry in
`website-openemr/data/releases.json` from `DRAFT` to `FINAL` and stamps
`released_at`.

### 5. Merge the docs and infra PRs

After the tag fires:

- Merge the `website-openemr` docs PR. Its post-merge workflow dispatches
  `openemr-docs-binaries` to `website-openemr-files` to publish generated
  API docs and binaries.
- Merge the `openemr-devops` rotation PR. The rotation only takes effect
  once merged.

The order does not matter; the consumers are independent.

## Things the automation does NOT do

These items appear on the historical wiki release checklist but are owned
elsewhere or no longer apply:

- **Forum / chat / social media announcements** — manual. Templates live in
  [`openemr-devops/tools/release/templates/announcements/`](https://github.com/openemr/openemr-devops/blob/master/tools/release/templates/announcements/).
- **Wiki "Release History" page update** — manual until the wiki is migrated
  to `website-openemr`.
- **Demo farm update** — owned by the `demo_farm_openemr` repo.
- **SourceForge upload** — preserved for historical mirrors; the canonical
  download path is now the GitHub release attached to the annotated tag.

The maintainer-facing checklist the conductor renders into the release-prep
PR body lives at
[`openemr-devops/tools/release/templates/full-checklist.md`](https://github.com/openemr/openemr-devops/blob/master/tools/release/templates/full-checklist.md).

## Slice-level plan documents

For implementation detail, see the per-slice plans (forward-looking; they
become historical once each slice ships):

- [`docs/release-automation-plan.md`](release-automation-plan.md) — conductor slice in this repo.
- [`openemr-devops/docs/release-automation-plan.md`](https://github.com/openemr/openemr-devops/blob/master/docs/release-automation-plan.md) — infra rotation slice.
- [`openemr/openemr-devops#664`](https://github.com/openemr/openemr-devops/issues/664) — design issue covering the whole cross-repo flow.

## Test mode

The conductor accepts a `test=true` `workflow_dispatch` input. Test runs:

- Open the prep PR under `release-prep-test/<branch>` so the post-merge
  finalize job creates a `v{M}_{m}_{p}-test.{shortSha}` tag instead of a real
  release tag.
- Are safe to run end-to-end against `rel-test` without cutting a real
  release.

This is how the cross-repo plumbing gets rehearsed without burning a real
version number.

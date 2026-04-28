# Release automation — `openemr/openemr` slice (the conductor)

Tracks: openemr/openemr-devops#664 (refines #662, overlaps with #638)

This repo owns the **release-prep PR** — the conductor in the three-PR release
flow. Merging this PR is the "we're shipping" decision; the merge commit gets
the annotated release tag, which then drives the downstream PRs in
`website-openemr` and `openemr-devops`.

## Role in the flow

```
openemr/openemr release-prep PR  ── merge → tag v8_1_0   ← this repo
            │                              │
            └── (push to rel-*) ───────────┼──→ website-openemr docs PR
                                           │
                                           └──→ openemr-devops infra PR
```

## Pattern

Borrowed from release-please: one long-lived PR per `rel-*` branch,
auto-updated on every push to that branch. Force-pushed (history is generated,
not authored). Merging it is the release decision.

## What the conductor PR contains

Mechanical changes from the wiki's [pre-tag checklist](https://www.open-emr.org/wiki/index.php/QA_and_Release_Process#Documentation):

- **`version.php`** — strip `-dev` suffix from `$v_tag`.
- **`library/globals.inc.php`** — `allow_debug_language` default to `0`.
- **`docker/production/docker-compose.yml`** — pin image version (master uses `latest`).
- **`_rest_routes.inc.php`** — bump version constant.
- **`swagger/openemr-api.yaml`** — regenerated via existing CLI:
  `php bin/console openemr:create-api-documentation`.
- **`docker-version`** files — increment in repo root, `sites/default/`,
  and `contrib/util/installScripts/` / `docker/.../root/` as applicable.
- **`contrib/util/installScripts/InstallerAuto.php`** — version refs.
- **New `fsupgrade-N.sh` scaffold** + Dockerfile edits in the upgrade dir.
- **New `sql/X_Y_Z-to-X_Y_Z+1_upgrade.sql` skeleton** on master (not on rel-*).
- **Refreshed `acknowledge_license_cert.html`** — regenerated from current
  third-party license inventory.

## Tag handling

On merge of the release-prep PR, the workflow creates an **annotated** tag on
the merge commit (`git tag -a` or the GitHub API with a tag object — never a
lightweight ref). Lightweight tags lack author/date/message metadata and break
`git describe`, downstream tooling, and consumers that introspect tag objects.

## Dispatch events emitted

The workflow emits `repository_dispatch` to consumer repos:

| Event                  | When                           | Payload                                |
| ---------------------- | ------------------------------ | -------------------------------------- |
| `openemr-rel-cut`      | first push to a new `rel-*`    | `{ branch, sha }`                      |
| `openemr-rel-update`   | subsequent push to `rel-*`     | `{ branch, sha }`                      |
| `openemr-tag`          | annotated tag created          | `{ tag, branch, sha }`                 |

Targets: `openemr/website-openemr`, `openemr/openemr-devops`.

## Components to build

In dependency order:

1. **`bin/console openemr:release-prep` command.**
   - Idempotent: applies all the mechanical edits listed above given a target
     version. Re-runnable on every push.
   - Reuses `openemr-dev:create-release-change-log` conventions for grouping.
   - Invoked by the conductor workflow on every push to `rel-*`.

2. **Workflow `.github/workflows/release-prep.yml`.**
   - Trigger: `push` on `rel-*`.
   - Steps: checkout → run `release-prep` console command → if diff,
     force-push to `release-prep/<rel-branch>` and open/update a draft PR
     against `<rel-branch>`.
   - On merge: create annotated tag, then `repository_dispatch` to consumers.

3. **App or PAT credential** with `contents:write`, `pull-requests:write`,
   and the cross-repo dispatch permission.

4. **PR template / banner.** The release-prep PR description includes a
   checklist of irreducibly-manual steps for the release manager:
   - [ ] Edit the auto-generated release-notes draft (in `website-openemr` PR)
   - [ ] Sign off on ONC certification page (in `website-openemr` PR)
   - [ ] Confirm `openemr-devops` infra PR is green
   - [ ] Merge this PR (triggers the tag and downstream finalization)

## Out of scope here

- Docs publishing — `website-openemr` PR.
- Test-matrix / package pin rotations — `openemr-devops` PR.
- Wiki content migration — handled in `website-openemr`.

## Open questions

- Should the release-prep workflow run on `master` too, to keep the
  `sql/*-upgrade.sql` skeleton fresh? Or leave that as a one-shot at branch-cut
  time?
- Acknowledgements list — generate from `git shortlog vX..HEAD` here, or
  defer to the docs PR? Currently leaning: generate the raw list here, render
  in the docs PR.

## Status

Draft plan. Lives alongside the existing `openemr-dev:` CLI conventions; the
release-prep command will share helpers with the existing change-log generator.

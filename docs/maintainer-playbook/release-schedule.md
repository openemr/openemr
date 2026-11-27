# OpenEMR Release Schedule

**Status:** Living document — captures the project's release-cadence target so
maintainers know *when* to kick off a release pass. The *how* lives in
[`docs/RELEASE_PROCESS.md`](../RELEASE_PROCESS.md).
**Hub:** [`README.md`](README.md)

---

## Target cadence

**Monthly minor release.** Each monthly release cuts a **new**
`rel-<MAJOR><MINOR>0` branch from master (e.g., `rel-860` for 8.6.0 after
`rel-850`'s 8.5.0; `rel-8100` for 8.10.0 after `rel-890`). The minor digit
increments; the `0` suffix is the initial patch position.

**Patch releases** happen on the existing `rel-<MAJOR><MINOR>0` branch
(e.g., 8.5.1 on `rel-850`) and are unscheduled — cut them when a specific
fix needs to ship ahead of the next monthly minor.

**Major releases** (next major number, e.g. 9.0.0) are rare and not on a
calendar cadence; they have their own planning window when needed.

## When to kick off

Open the monthly minor pass when ~4 weeks have passed since the last minor
cut. The new branch is `rel-<MAJOR><MINOR+1>0` relative to the current
active rel branch. Patch cycles kick off ad-hoc on the active rel branch
when a fix needs to ship before the next monthly minor.

## How to execute

See [`docs/RELEASE_PROCESS.md`](../RELEASE_PROCESS.md) for the complete
runbook — branch-cut, patch-prep, ship, amendment, and the full workflow
inventory. That document is the operational source of truth; this page just
anchors the *when*.

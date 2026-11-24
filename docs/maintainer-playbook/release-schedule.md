# OpenEMR Release Schedule

**Status:** Living document — captures the project's release-cadence target so
maintainers know *when* to kick off a release pass. The *how* lives in
[`docs/RELEASE_PROCESS.md`](../RELEASE_PROCESS.md).
**Hub:** [`README.md`](README.md)

---

## Target cadence

**Monthly minor release** on the active `rel-<NNN>` line. **Patch releases**
between minors are unscheduled — cut them when a specific fix needs to ship
ahead of the next monthly minor.

A fresh `rel-<NNN+1>0` major-release branch cut happens on its own calendar
beat (the November beat — see
[php-version-support.md](php-version-support.md) for the rationale that
anchors major cuts alongside new PHP/Alpine adoption).

## When to kick off

Open the monthly minor pass when ~4 weeks have passed since the last tagged
minor on the active `rel-<NNN>` line. Patch cycles kick off ad-hoc when a
fix needs to ship before the next monthly minor.

## How to execute

See [`docs/RELEASE_PROCESS.md`](../RELEASE_PROCESS.md) for the complete
runbook — branch-cut, patch-prep, ship, amendment, and the full workflow
inventory. That document is the operational source of truth; this page just
anchors the *when*.

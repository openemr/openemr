# OpenEMR Release Schedule

**Status:** Living document — captures the project's release-cadence target so
maintainers know *when* to kick off a release pass. The *how* lives in
[`docs/RELEASE_PROCESS.md`](../RELEASE_PROCESS.md).
**Hub:** [`README.md`](README.md)

---

## Target cadence

**Monthly.** Aim to tag a new release roughly once per calendar month on the
active `rel-<NNN>` line (patch bumps between majors/minors). The yearly
major/minor cut on a fresh `rel-<NNN+1>0` branch happens on its own calendar
beat (the November-aligned beat — see
[php-version-support.md](php-version-support.md) for the rationale that
anchors the yearly cut alongside new PHP/Alpine adoption).

Monthly cadence absorbs backported fixes, security advisories (GHSAs), and
small feature work merged to the active release branch between major cuts.
Per-release validation burden stays bounded when the queue between releases
is modest; long gaps compound it.

## When to kick off

Open the monthly release pass when **both** are true:
- ~4 weeks have passed since the last tagged release on the active
  `rel-<NNN>` line.
- There is a non-trivial queue of commits on `rel-<NNN>` past the last tag
  (bugfixes, GHSAs, backports) worth shipping.

If the active rel branch has been quiet, defer a cycle. If a GHSA needs to
ship urgently, kick off regardless of the 4-week mark.

## How to execute

See [`docs/RELEASE_PROCESS.md`](../RELEASE_PROCESS.md) for the complete
runbook — branch-cut, patch-prep, ship, amendment, and the full workflow
inventory. That document is the operational source of truth; this page just
anchors the *when*.

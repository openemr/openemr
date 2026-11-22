# OpenEMR Maintainer Playbook

**Status:** Living document — index of recurring maintainer-side tasks on
OpenEMR that would otherwise rely on institutional memory, so they don't fall
off the radar. Includes both calendar-triggered items (PHP drop, Alpine
adoption) and event-triggered items (triggered by a new upstream release, a
CVE, a dependency EOL, etc.).
**Audience:** Maintainers (and coding agents assisting maintainers) on routine
upkeep. Not for contributors writing feature or bugfix PRs.
**Relationship to [`AGENTS.md`](../../AGENTS.md):** `AGENTS.md` carries the
line-level coding standard and the per-change decision gate. These documents
are the layer above it: *when and why to kick off a maintenance pass*, not how
to write the code inside one. When the two overlap, `AGENTS.md` wins on
mechanics; these documents own the recurring-task triggers.

Open a sub-document only for the item you're actioning. New entries belong
here when the trigger is "a maintainer periodically does X" (calendar or
event-driven) and the alternative is an unwritten tribal rule; one-off plans
belong in the parent `docs/`.

---

## Index

| Item | Cadence | Sub-document |
|---|---|---|
| Drop oldest-supported PHP version | ~1 year before upstream PHP EOL | [php-version-support.md](php-version-support.md) |
| Add newest PHP version (RC → GA) | On upstream RC publish; promote on GA | [php-version-support.md](php-version-support.md) |
| Add newest Alpine version | On Alpine release (~every 6 months, May + November) | [alpine-version-support.md](alpine-version-support.md) |

---

## Reference PRs

Each sub-document points at concrete reference PRs — the authoritative example
of how an item has been done. When executing an item, read the most recent
reference PR end-to-end before starting; it is the surface map.

| Area | Reference PR |
|---|---|
| PHP drop (→ 8.3 floor) | [#13280](https://github.com/openemr/openemr/pull/13280) |
| PHP drop (→ 8.4 floor) | [#15455](https://github.com/openemr/openemr/pull/15455) |
| PHP late-cleanup drop from weekly builds | [#13764](https://github.com/openemr/openemr/pull/13764) |
| PHP GA adoption (8.6 in dev-php-fpm sidecars) | [#15454](https://github.com/openemr/openemr/pull/15454) |
| Alpine add (3.24 build + test workflows) | [#13656](https://github.com/openemr/openemr/pull/13656), [#13657](https://github.com/openemr/openemr/pull/13657) |
| Alpine promote to default flex | [#13658](https://github.com/openemr/openemr/pull/13658) |

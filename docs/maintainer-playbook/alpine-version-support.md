# Alpine Version Support

**Status:** Living document — covers adopting new Alpine Linux versions as
upstream releases them.
**Hub:** [`README.md`](README.md)

---

## When to add a new Alpine version

**Cadence:** Alpine Linux ships a new minor (3.XX) roughly every 6 months, in
May and November.

Upstream publishes the schedule at <https://alpinelinux.org/releases/>. Check
it when deciding whether an adoption pass is due.

Each new Alpine minor reaches an EOL after ~2 years of support. Keep the
most-recent Alpine as the default flex image; keep the previous one or two
Alpines built in parallel for release-branch maintenance.

## Alpine ↔ PHP calibration

PHP's release cadence is yearly (November GA), Alpine's is twice yearly (May +
November). Consequently:

- **Every other Alpine release coincides with a new PHP minor.** A November
  Alpine (e.g. 3.25) typically ships packages for the November-released PHP
  (e.g. PHP 8.6). A May Alpine (e.g. 3.26) usually inherits the previous
  November's PHP set with no new minor.
- When adopting a November Alpine, expect to add PHP support for the
  freshly-GA'd PHP at the same time — the new Alpine is the vehicle that lets
  the project's `docker/flex` and `docker/release` images build against that
  new PHP. See [php-version-support.md](php-version-support.md) for the PHP add
  surface; the two items are complementary but still land as separate PRs.

---

## Reference PRs

Read these end-to-end before executing. They're the surface map.

### Alpine add (3-stage sequence) — [#13656](https://github.com/openemr/openemr/pull/13656), [#13657](https://github.com/openemr/openemr/pull/13657), [#13658](https://github.com/openemr/openemr/pull/13658) (Alpine 3.24 adoption)

The 3.24 adoption split cleanly into three dependent PRs. Mirror that shape:

1. **Build workflow** — [#13656](https://github.com/openemr/openemr/pull/13656)
   adds `.github/workflows/docker-build-3<XX>.yml` for the new Alpine, calling
   `docker-build-flex-core.yml` with the Alpine version and its supported PHP
   list. `is_default_flex` stays **false** here so the new workflow builds
   images but doesn't claim the floating `flex` tag yet.

2. **Test workflow** — [#13657](https://github.com/openemr/openemr/pull/13657)
   adds `.github/workflows/docker-test-flex-3<XX>.yml`, which calls
   `docker-test-flex-core.yml` against the new Alpine's images (build-workflow
   outputs). Must land after #13656 so the test workflow has real images to
   pull.

3. **Promote to default** — [#13658](https://github.com/openemr/openemr/pull/13658)
   flips the new Alpine's `docker-build-3<XX>.yml`'s `is_default_flex` **true**
   and the previous default's `is_default_flex` **false**. Also bumps the
   `openemr/openemr:flex-3.<XX>-php-<Y>@sha256:*` pins across ~26 CI and dev
   compose files to the new Alpine's digests (dependabot keeps them fresh
   going forward). Updates `CONTRIBUTING.md` PHP-swap examples and
   `docker/README.md` flex tag enumeration.

Land stage 3 only once the new Alpine's images have had time to shake out in
scheduled nightly builds against stage 2's workflow — a week or two of green
runs before the default flip.

## Keeping older Alpines alive

The "Not touched" principle from PHP drops applies symmetrically here: don't
delete `docker-build-3<old>.yml` / `docker-test-flex-3<old>.yml` just because
a newer Alpine is now default. The release branches (`rel-<NNN>`) build against
whichever Alpine was current at their release, and those workflows are the
authoritative source for their flex images. Alpine retirement is a separate,
later decision tied to Alpine's own EOL schedule, not tied to adopting the
next version.

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

The 3.24 adoption split cleanly into three PRs. Mirror that shape:

1. **Build workflow** — [#13656](https://github.com/openemr/openemr/pull/13656)
   adds `.github/workflows/docker-build-3<XX>.yml` for the new Alpine, calling
   `docker-build-flex-core.yml` with the Alpine version and its supported PHP
   list. Runs nightly on a cron schedule, publishing images to Docker Hub.
   `is_default_flex` stays **false** here so the new workflow builds images
   but doesn't claim the floating `flex` tag yet.

2. **Test workflow** — [#13657](https://github.com/openemr/openemr/pull/13657)
   adds `.github/workflows/docker-test-flex-3<XX>.yml`, which calls
   `docker-test-core.yml`. That reusable workflow does a *local* build:
   `docker compose build --build-arg ALPINE_VERSION=3.<XX>
   --build-arg PHP_VERSION=<Y>` against `docker/flex`, then runs tests against
   the locally-built image. It does not pull from Docker Hub, so it isn't
   functionally dependent on stage 1 — the ordering is convention. Triggers
   are `push` to master + `pull_request`, both path-filtered to `docker/flex`
   and workflow files; no schedule.

3. **Promote to default** — [#13658](https://github.com/openemr/openemr/pull/13658)
   flips the new Alpine's `docker-build-3<XX>.yml`'s `is_default_flex` **true**
   and the previous default's `is_default_flex` **false**. Bumps `ARG
   ALPINE_VERSION` in `docker/release/Dockerfile` to the new version (release
   branches keep the Alpine they were cut with; this is a master-only flip).
   Also bumps the `openemr/openemr:flex-3.<XX>-php-<Y>@sha256:*` pins across
   ~26 CI and dev compose files to the new Alpine's digests (dependabot keeps
   them fresh going forward). Updates `CONTRIBUTING.md` PHP-swap examples and
   `docker/README.md` flex tag enumeration.

Land stage 3 only once the new Alpine's images have had time to shake out — a
week or two of green nightly runs from stage 1 (which publishes to Docker Hub
on cron), plus any stage-2 test runs that fired on PRs touching `docker/flex`
during the window. The gate is "no publish-path or build-path regression has
surfaced across repeated nightly cycles and whatever contributor-driven test
runs happened in the window," not "a scheduled stage-2 test run went green"
(stage 2 has no cron).

## Keeping older Alpines alive

The "Not touched" principle from PHP drops applies symmetrically here: don't
delete `docker-build-3<old>.yml` / `docker-test-flex-3<old>.yml` just because
a newer Alpine is now default. The release branches (`rel-<NNN>`) build against
whichever Alpine was current at their release, and those workflows are the
authoritative source for their flex images. Alpine retirement is a separate,
later decision tied to Alpine's own EOL schedule, not tied to adopting the
next version.

### Caveat: Alpine `community` package shelf-life

Alpine distinguishes two package repositories: `main` is supported for the
full ~2-year branch lifetime; `community` is supported only until the *next*
stable release (~6 months). **PHP lives in `community`.** The moment Alpine
3.<N+1> ships, Alpine 3.<N>'s `community/php*` packages stop getting upstream
security fixes — well before the 3.<N> branch itself EOLs.

Impact: retaining an older Alpine line for release-branch maintenance (e.g.
`rel-830` on Alpine 3.23 after 3.24 ships) means that release branch's PHP
packages are already past upstream security support. Factor that into
release-branch maintenance decisions — the `rel-<NNN>` branch should move
off an Alpine line sooner than the branch EOL suggests, or accept running on
unpatched PHP for the gap.

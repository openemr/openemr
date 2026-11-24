# PHP Version Support

**Status:** Living document — covers both ends of the supported-PHP range:
dropping the oldest version when it approaches EOL, and adopting a new version
as upstream cuts RCs and ships GA.
**Hub:** [`README.md`](README.md)

---

## When to drop the oldest-supported PHP version

**Target:** The November beat that falls slightly more than one year before
upstream PHP's end-of-security-support ("EOL") date for that minor — i.e. the
November roughly 3 years after that version's GA.

Upstream PHP's release cycle is ~2 years active support + ~2 years security-only
support, for ~4 years total per minor. PHP minors GA in November; PHP EOLs
land in December four years later. Dropping on the November beat ~3 years
after a minor's GA lands the drop:
- ~13 months before upstream EOL on that minor — still within upstream's
  security-support window, with a runway for modern syntax adoption
- ~1 year after upstream's active-support phase ended for that minor
- **Same calendar beat as two companion items** — the fresh November PHP
  GA (handled by [this doc's "When to add a new PHP version"](#when-to-add-a-new-php-version))
  and any new November Alpine release (handled by
  [alpine-version-support.md](alpine-version-support.md))

Aligning on November means the drop, the new-PHP adoption, and the
Alpine adoption share a review window and a mental-model reset per year,
instead of trickling across the calendar as independent surprises. Keep the
PRs separate (see the reference PRs section for the surfaces), but kick off
the pass as one calendar event.

The upstream schedule is published at <https://www.php.net/supported-versions.php>.
Check it when deciding whether a drop is due; don't rely on memory.

## When to add a new PHP version

The two touch-points below cover the `docker-library/php`-based sidecars used
by the dev stack and CI matrices. A **third** path — the Alpine-packaged PHP
used by `docker/flex` and `docker/release` — advances separately whenever a
new Alpine version ships with that PHP minor packaged. See
[alpine-version-support.md](alpine-version-support.md) ("Alpine ↔ PHP
calibration" and the 3-stage adoption sequence) — that work lands alongside
an Alpine bump, not as a standalone PHP-add.

Two sidecar-track touch-points per PHP release:

1. **Upstream RC images published** — the `php:X.Y-rc-fpm` tag goes live
   (sometime between upstream's RC1 and GA). Two moves happen together at
   this point because upstream `php-src` master simultaneously advances to
   the *next* minor:
   - Flip the project's dev-track sidecar for the new RC version from
     `pre-build-dev-XY` to `X.Y-rc-fpm` so CI starts catching compatibility
     regressions against the imminent GA.
   - Advance the from-source master-tracking image
     (`docker/library/dockers/dev-php-fpm/Dockerfile` and
     `.github/workflows/build-dev-php-fpm-docker.yml`) from the old
     master-tracked version to the new one (`X.(Y+1)`). Upstream has just
     branched `PHP-X.Y` for release engineering, leaving `master` building
     the next minor; our from-source slot follows.

2. **Upstream GA** — the `php:X.Y-fpm` tag replaces `-rc-fpm`. Flip the
   sidecar FROM, move the version from the "pre-release" bucket to the
   "supported" bucket in `.github/workflows/isolated-tests.yml`, and add it
   to any matrix workflows that iterate PHP versions.

RC and GA do **not** share a calendar beat. The RC image typically ships in
September (upstream cuts RC1 ~2 months before November GA — e.g. PHP 8.5 RC1
landed September 2025), so start the RC stage as soon as `php:X.Y-rc-fpm`
appears on Docker Hub; waiting until November loses weeks of pre-GA CI
testing. The GA stage then shares the November beat with the oldest-version
drop review (see "When to drop" above). Both touch-points are independent
commits/PRs either way — keep them separate.

After the sidecar FROM flip in either stage, the next weekly rebuild of
`openemr/dev-php-fpm:X.Y` publishes the refreshed image to Docker Hub.
Dependabot's `docker-compose` jobs (`.github/dependabot.yml`) then bump the
`openemr/dev-php-fpm:X.Y@sha256:*` digest pins across `ci/nginx_8<Y>/` and
`docker/development-insane/` on the next scheduled scan — the project's own
images are excluded from the 3-day cooldown so CI picks up each rebuild
promptly. No manual digest updates needed.

---

## Reference PRs

Read these end-to-end before executing. They're the surface map.

### PHP drop — [#15455](https://github.com/openemr/openemr/pull/15455) (PHP 8.3 → floor 8.4)

Also see [#13280](https://github.com/openemr/openemr/pull/13280) (PHP 8.2 → floor 8.3) for the pattern's origin and
[#13764](https://github.com/openemr/openemr/pull/13764) for the follow-up "drop from weekly builds" cleanup.

Surface the drop touches:
- `composer.json` — `require.php` bump, `platform.php` bump (see gotcha below),
  `ext-*` platform entries
- `composer.lock` — regenerated against the new `platform.php`
- `src/Common/Compatibility/Checker.php` — `$minimumPhpVersion`
- CI workflow matrices — remove the dropped version from `api-docs`, `composer`,
  `composer-require-checker`, `database-version`, `integration-tests`,
  `isolated-tests`, `license-headers`, `phpstan-types`, `syntax`,
  `test-frontcontroller`, `windows-tests` (single-version floors bump up; multi-
  version matrices drop the entry)
- `.github/workflows/test-all.yml` — flip the PR-time `jq select(.php == ...)`
  filter and the enable_coverage pin to the new floor's `apache_<N><M>_123` dir
- `.github/workflows/test.yml` — update the `workflow_dispatch` `docker_dir`
  choice list (add new names, remove dropped ones)
- `ci/` dirs — delete the old-floor's plain `apache_<old>_123/` + `nginx_<old>/`
  (new-floor equivalents already exist); rename the old-floor's special variants
  `apache_<old>_123_redis_sentinel_mtls/` and `apache_<old>_123_upgrade/` to the
  new-floor name, and bump their `flex-3.XX-php-<new>` image refs inside
- `.github/dependabot.yml` — update the `docker-compose` directories list to
  match ci/ surgery
- Any PHPStan deprecations the new target surfaces — fix at source (project
  policy: no new baseline entries)
- Docs — `AGENTS.md` "PHP X+ required", `CONTRIBUTING.md` references

Left intact on drop PRs:
- `.github/workflows/weekly-build-php-fpm-dockers.yml` keeps building the
  dropped version's `openemr/dev-php-fpm:<old>` image for release-branch flex
  configs and community downstream
- `docker-build-3<XX>.yml` / `docker-test-flex-3<XX>.yml` keep the full
  PHP matrix per Alpine line for maintenance releases
- Release workflows (`build-patch`, `build-release`, `ship-release`,
  `release-amendment`, `branch-cut-automation`, `docker-build-release`,
  `release-mechanism-smoketest`) — release-branch-facing, not master-facing

### PHP GA adoption — [#15454](https://github.com/openemr/openemr/pull/15454) (PHP 8.6 RC → GA)

Surface the GA flip touches:
- `docker/library/dockers/dev-php-fpm-<X>-<Y>/Dockerfile` — `FROM php:X.Y-rc-fpm`
  → `FROM php:X.Y-fpm`, drop the RC-rationale header, refresh "rc-fpm" wording
  in surrounding comments (if the lowest-supported PHP changes too, see the
  separate `platform.php` gotcha below)
- `.github/workflows/isolated-tests.yml` — move the version from
  `prerelease=(...)` to `supported=(...)`
- Stale "PHP X.Y" examples elsewhere — grep for the pre-GA version string and
  refresh references (ship-release workflow examples, docker-push-dockerhub-readme
  comments, dev-php-fpm nightly-master Dockerfile comments)

---

## Gotchas

### `composer.json` `platform.php` must satisfy `roave/security-advisories`

`roave/security-advisories` declares conflicts with insecure PHP versions as
part of its dev-latest advisory list. When a new PHP CVE lands, roave adds a
range ban (e.g. `>=8.4,<8.4.28` for php2shell on PHP 8.4). If `platform.php` is
set to the raw minor (`"8.4"` = 8.4.0), it falls inside the banned range and
`composer update` / `composer update --lock` cannot resolve.

**Policy:** Pin `platform.php` to the current minimum *safe* patch (e.g.
`"8.4.28"`, not `"8.4"`). `ext-*` entries stay unpinned minor. When roave adds
a future patch-range ban, `composer update` surfaces a resolution failure —
bump `platform.php` to the new safe patch as follow-up.

**Never use** `composer update --ignore-platform-req=php` to bypass this check.
The flag defeats roave's purpose and masks a real signal. Established on
#15455.

**What `platform.php` does and doesn't guarantee.** `platform.php` is
Composer's *resolution* simulation — it tells Composer "assume PHP is this
version when picking packages." It does **not** validate the deployed runtime.
A deployment running PHP 8.4.0 (below a pinned safe `"8.4.28"`) will not
trigger any Composer check; roave's conflict only fires during `composer
update` on the maintainer's machine, not on the deployed server. The actual
runtime gate is `src/Common/Compatibility/Checker.php`'s `$minimumPhpVersion`,
which the project keeps at the *publicly-stated* floor (e.g. `"8.4.0"`),
deliberately looser than the Composer platform pin. In practice, downstream
users running the openemr Docker images get the latest `php:X.Y-fpm` patch
automatically (docker-library/php tracks upstream), so the gap only matters
for hand-installed deployments on an obsolete patch. If stricter enforcement
is needed per-deployment, run `composer check-platform-reqs` in the
deployment pipeline against the actual runtime.

### Deprecations surfaced at the new PHP target

Bumping `platform.php` changes PHPStan's target, and that surfaces new
deprecation warnings (PHP 8.4 added property hooks, deprecated `E_STRICT`, etc.).
Fix at source — the project policy forbids new baseline entries for
deprecations that have a clean source fix. See the "PHPStan deprecations
surfaced by the platform bump" section of the drop reference PRs for the shape.

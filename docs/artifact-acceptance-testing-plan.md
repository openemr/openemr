# Artifact acceptance testing

Architectural rationale, phase-by-phase design record, and running debt
list for OpenEMR's black-box acceptance test surface that validates the
shipped production artifacts (Docker image + release tarball) end-to-end,
without requiring test infrastructure inside the artifacts themselves.

Companion to [`release-mechanism-migration-from-devops.md`](release-mechanism-migration-from-devops.md)
(release-mechanism consolidation, completed 2026-07-23) and
[`docker-migration-from-devops.md`](docker-migration-from-devops.md)
(docker-pipeline consolidation, completed 2026-06-20). Sits alongside those
as one of the three major structural changes to how OpenEMR ships.

**Doc scope.** This is the design record — the *why* behind the current
acceptance surface. Every Phase N section documents one shipped chunk of
the surface (the PR reference, the design decision, and the wrinkles that
surfaced during build). Read top-to-bottom to understand how the surface
grew; use as a jump-off point when touching acceptance code to find the
phase that owns the affected area.

## Contents

- [Goal](#goal)
- [Motivation](#motivation)
- [Current state and the problem](#current-state-and-the-problem)
- [Proposed model](#proposed-model)
- [Test surfaces after the migration](#test-surfaces-after-the-migration)
- [What lives where (concrete)](#what-lives-where-concrete)
- [What stays unchanged](#what-stays-unchanged)
- [Phased plan](#phased-plan)
- [Test-coverage philosophy](#test-coverage-philosophy)
- [Risks and wrinkles](#risks-and-wrinkles)
- [Deferred / known debt](#deferred--known-debt)

## Goal

Add a black-box test surface that validates the two production artifacts
OpenEMR ships to end users:

- **Docker image** (`openemr/openemr:X.Y.Z` on Docker Hub, built by
  [`.github/workflows/docker-build-release.yml`](../.github/workflows/docker-build-release.yml)
  from [`docker/release/Dockerfile`](../docker/release/Dockerfile))
- **Release tarball / zip** (`openemr-X.Y.Z.tar.gz` on the GitHub release
  page, built by the release toolchain's `PackageAssembler`)

Tests are **external to the artifact**. They boot it (docker run / extract
tarball into a generic PHP+Apache+MySQL stack), then exercise it via its
public interfaces:

- HTTP (install wizard, admin login, page rendering)
- REST + FHIR API endpoints
- Database (verify persisted state)
- Panther / Selenium (browser flows)

Two flows validated per artifact:

1. **Fresh install**: pull/extract artifact → boot → install wizard runs →
   admin login works → smoke pages render → API responds
2. **Upgrade from prior version**: install prior artifact → seed data →
   swap to new artifact → auto-upgrade runs (`fsupgrade-<N>.sh` +
   `sql_upgrade.php`) → seeded data still intact → app functional

Preserving:

- **Existing source-side tests** (unit, isolated, services, api, e2e in
  `tests/Tests/`) run against source in the dev stack, unchanged.
- **Existing dev-stack coverage** collection stays as-is.
- **CI runtime budget** — acceptance tests are heavier than unit tests but
  bounded (~20-30 min per artifact per run, run selectively).

## Motivation

Two forces converge here:

**1. Content divergence between artifacts.** The release tarball uses
`git archive` and honors `export-ignore` in `.gitattributes`, so it strips
`tests/`, `.github/`, `ci/`, `docker/`, `tools/`, `build.xml`,
`.pre-commit-config.yaml`, and the large `Documentation/EHI_Export/`
schemaspy tree. The Docker image uses `git clone` and ships everything.
Two artifacts that are semantically the same product carry materially
different file sets. SBOM, provenance, and audit reviews see divergence.

**2. Testing-in-image ties us to that divergence.** Today's
`docker-test-core.yml` → `test-actions-core` invokes PHPUnit *inside* the
built container against `tests/` and `phpunit.xml`. If we align the Docker
image to the tarball (strip export-ignored paths), PHPUnit inside the
container has nothing to run. Openemr/openemr#12790 demonstrated this
exact failure mode — the coverage-instrumented (`kcov`) path failed with
PHPUnit's `--help` output because it couldn't find its config or tests.

The wrong response is "put test machinery back in the artifact." The
right response is "test the artifact from the outside." That's what this
plan does.

## Current state and the problem

Today's test surfaces:

| Surface | Runs where | Runs against | Scope |
|---|---|---|---|
| `tests/Tests/Isolated/**` | dev-stack container | Source tree | Pure PHP logic, no DB |
| `tests/Tests/Api/**`, `E2e/**`, `Services/**`, `Unit/**` | dev-stack container | Source tree | Full DB, browser, HTTP |
| `docker-test-release.yml` → `docker-test-core.yml` → `test-actions-core` | GitHub runner (prod profile) | Built Docker image | Boot + install + web-response smoke |
| `docker-test-core.yml` (kcov profile, `production_coverage_openemr_version: release`) | GitHub runner | Built Docker image with `tests/` inside | PHPUnit test suite inside container, kcov coverage collected |
| `test-all.yml` (matrix of ci/apache_*_*) | GitHub runner | ci/*/docker-compose.yml test rigs (not the shipped Docker image) | PHP × webserver × DB matrix |
| `test-all.yml` `_upgrade` variants | GitHub runner | ci/*_upgrade/docker-compose.yml (flex/dev image + seeded 5.0.0 SQL) | SQL-schema upgrade from ancient version |

Gaps:

- **No test validates the release tarball at all.** Nothing extracts
  `openemr-X.Y.Z.tar.gz`, boots it, and confirms it installs cleanly.
- **No docker upgrade-path test** — no CI job boots a prior Docker Hub image,
  swaps to the new one, and confirms the auto-upgrade path works. (An
  in-flight PR at openemr/openemr#12791 sketched the workflow scaffolding
  but was parked in favor of this plan's more complete design.)
- **Coverage instrumentation ties the Docker image to test infrastructure.**
  The `kcov` profile in `docker-test-core.yml` fires PHPUnit inside the
  built image, so `tests/` and `phpunit.xml` have to be present in the
  clone. This blocks the docker/tarball content parity that
  openemr/openemr#12790 attempted.

Effect at ship time: we ship two artifacts on faith that they'll install
and upgrade correctly for end users, because no CI test exercises those
flows against those artifacts. Manual pre-release testing has been the
backstop. That's the load-bearing gap this plan closes.

## Proposed model

Two-layer testing:

**Source-side** (unchanged from today):
- White-box tests against the source tree
- Runs in the dev stack (`docker/development-easy` + siblings)
- Owns coverage measurement of source code
- Fast iteration for contributors

**Artifact-side** (this plan):
- Black-box tests against the shipped artifact
- Runs on GitHub Actions runners against pulled/extracted artifacts
- Owns confidence in "does the artifact install and upgrade for end users"
- Slower, run selectively (release-prep PRs + scheduled + release-tag builds)

Each artifact has one acceptance workflow:

- `.github/workflows/acceptance-docker.yml` — boots
  `openemr/openemr:${TAG}` from Docker Hub (or from a local build for
  PR validation of Dockerfile changes)
- `.github/workflows/acceptance-package.yml` — extracts
  `openemr-${VERSION}.tar.gz` (or `.zip`) into a generic PHP+Apache+MySQL
  stack

Both workflows drive a shared test harness in `tests/Acceptance/` (or
`tests/Artifact/` — [naming to be decided](#decisions-to-lock-before-phase-2))
that runs from the workflow runner (not from inside the artifact),
executing PHPUnit against the artifact's exposed URLs, DB, and browser.

Once acceptance tests exist and prove their value:

- Retire or restructure `docker-test-core.yml`'s `kcov` profile
- Land the Dockerfile `git clone` → `git archive` switch that
  openemr/openemr#12790 explored — docker image content parity with the
  tarball becomes achievable
- Consider making acceptance tests required checks on release-prep PRs
  (replacing today's implicit reliance on docker-test-release for that
  coverage)

## Test surfaces after the migration

| Surface | Runs where | Runs against | Scope |
|---|---|---|---|
| `tests/Tests/**` (unchanged) | dev-stack container | Source tree | Unit + isolated + services + api + e2e against source |
| `tests/Acceptance/**` (new) | GitHub runner (harness process) | Booted shipped artifact (Docker or tarball) | Install wizard, upgrade path, data integrity, API smoke, E2E critical path — via the artifact's public interfaces only |
| `test-all.yml` (unchanged) | GitHub runner | ci/*/docker-compose.yml rigs | Configuration matrix testing (PHP × webserver × DB) |
| `docker-test-release.yml` (evolves) | GitHub runner | Built Docker image | Basic smoke — either stays as a fast pre-acceptance gate, or absorbed into acceptance-docker.yml, depending on Phase 3 choice |

Deleted:

- `docker-test-core.yml` `kcov` profile (or restructured to mount tests
  from host, [decision deferred](#decisions-to-lock-before-phase-2))

## What lives where (concrete)

### `tests/Acceptance/`

New PSR-4 namespace, likely `OpenEMR\Tests\Acceptance\`. Directory
sketch:

```
tests/Acceptance/
├── phpunit.acceptance.xml           # dedicated PHPUnit config, distinct from
│                                    # phpunit.xml (which is export-ignored)
├── bootstrap.php                    # loads .env with artifact endpoint URL,
│                                    # DB creds, admin creds
├── Support/
│   ├── ArtifactClient.php           # HTTP client wrapping the artifact endpoint
│   ├── ArtifactDatabase.php         # DB reader (raw PDO), not through OpenEMR abstractions
│   ├── ArtifactBrowser.php          # Panther client pointed at artifact endpoint
│   ├── DataSeed/                    # seed helpers (create patient, encounter, user)
│   │   ├── PatientSeeder.php
│   │   └── EncounterSeeder.php
│   └── Assertions/                  # domain-specific assertions
│       └── InstallWizardAssertions.php
├── InstallTest.php                  # fresh install → admin login → smoke pages
├── UpgradeIntegrityTest.php         # seed → upgrade → verify data + app functional
├── ApiSmokeTest.php                 # subset of API endpoints, black-box
├── FhirSmokeTest.php                # subset of FHIR endpoints
└── E2eCriticalPathTest.php          # login → new patient → schedule → encounter
```

Composer wiring: `autoload-dev` PSR-4 entry mapping
`OpenEMR\Tests\Acceptance\` to `tests/Acceptance/`. Existing
`OpenEMR\Tests\` mapping to `tests/Tests/` unchanged.

### Workflows

`.github/workflows/acceptance-docker.yml`:

```yaml
inputs:
  from_tag        # default: latest
  to_tag          # default: next
  # Optionally: build_locally: bool -- for PR validation of Dockerfile
  #             changes, build from the PR's docker/release/Dockerfile
  #             instead of pulling from Docker Hub

# Runs three scenarios (matrix jobs, parallel):
#
#   1. Fresh install of from_tag -- validates the currently-shipped
#      installer path. Baseline sanity for the pattern.
#
#   2. Fresh install of to_tag -- validates the target-version's
#      installer code (setup.php / wizard) directly. This is a
#      DIFFERENT code path than the upgrade path -- new users
#      installing the target version don't run fsupgrade-<N>.sh
#      or sql_upgrade.php, they run the full installer.
#
#   3. Upgrade from_tag -> to_tag -- validates the auto-upgrade
#      path (fsupgrade-<N>.sh + sql_upgrade.php) that existing
#      installations traverse.
#
# All three exercise the same InstallTest / UpgradeIntegrityTest
# classes with different artifact endpoints + test groups.

# Matrix scenario shapes:

# --- scenario: fresh-install (from_tag or to_tag) ---
steps:
  - checkout
  - install composer deps for acceptance harness on the runner
  - boot docker/production/docker-compose.yml with the scenario's tag
  - run acceptance harness --group=fresh-install (against the tag)

# --- scenario: upgrade (from_tag -> to_tag) ---
steps:
  - checkout
  - install composer deps for acceptance harness on the runner
  - boot docker/production/docker-compose.yml with FROM_TAG
  - run acceptance harness --group=fresh-install (against from_tag)
  - seed reference data via harness
  - docker compose down (preserve volumes)
  - swap image to to_tag
  - docker compose up (auto-upgrade runs)
  - run acceptance harness --group=post-upgrade (verify seeded data + functionality)
```

`.github/workflows/acceptance-package.yml`:

```yaml
inputs:
  from_version    # e.g. 8.0.0.3
  to_version      # e.g. 8.2.0
  from_source     # 'github-release' or 'local-artifact' -- how to obtain from
  to_source       # same, for to

steps:
  - checkout
  - install composer deps for acceptance harness on the runner
  - download or build from-tarball, extract to /tmp/openemr-from
  - boot generic stack (mariadb + php-apache) with mount pointing at /tmp/openemr-from
  - run acceptance harness --group=fresh-install
  - seed reference data
  - stop app container (keep DB + sites/ volume)
  - download or build to-tarball, extract to /tmp/openemr-to
  - swap mount to /tmp/openemr-to
  - start app container, trigger sql_upgrade.php + any post-upgrade CLI needed
  - run acceptance harness --group=post-upgrade
```

Both workflows fire on:

- Schedule (daily, catches Docker Hub / GitHub release drift)
- `workflow_dispatch` (manual, any version pair)
- On release-prep PRs / merges (to give pre-tag confidence)
- On changes to the acceptance harness itself, its workflow, or the
  compose files it uses

### Compose files

Shared harness compose files under `.github/docker/`:

- `.github/docker/acceptance-docker-compose.yml` — an override on top
  of `docker/production/docker-compose.yml` that:
  - Replaces the sha-pinned `image:` with an env-driven ref
  - Relaxes the healthcheck for compatibility with older openemr images
    that predate `/meta/health/readyz`
  - Removes `restart: always` (workflow manages lifecycle)

- `.github/docker/acceptance-package-compose.yml` — generic
  PHP+Apache+MySQL stack that mounts an extracted openemr tarball into
  `/var/www/localhost/htdocs/openemr`:
  - MariaDB service (matching production-supported version)
  - php:${VER}-apache image with required extensions installed
  - Volume mounts driven by env vars

## What stays unchanged

- `tests/Tests/` and its full existing structure
- `docker/development-easy/` dev stack + all its openemr-cmd tooling
- `test-all.yml` and its ci/apache_*_* matrix — these test the PHP
  application in a variety of runtimes, complementary to but distinct
  from acceptance
- All existing source-level PHPUnit invocations (`openemr-cmd ut`,
  `openemr-cmd at`, etc.)
- Contributor development flow — writing new PHPUnit tests still goes in
  `tests/Tests/`, running still uses `openemr-cmd`
- Existing docker builds and their tags

## Phased plan

**Rollout order**: `1 → 2 → 2.5 → 3 → 4 → 3.5 → 5 → 6 → 7`. Phases
execute roughly in numeric order with two intentional out-of-numeric
insertions:

- Phase 6 was added 2026-07-24 as a scoped-out follow-up to Phase 5
  — Phase 5 alone doesn't unlock full-fidelity PR-image validation
  (see [Phase 5](#phase-5--retire-the-tests-in-image-dependency)
  and [Phase 6](#phase-6--full-fidelity-pr-image-testing-source-mode-indirection)
  for the misread-and-correction thread that generated Phase 6).
- Phase 3.5 was formalized 2026-07-26 as the tarball counterpart to
  Phase 2.5's docker `build_locally` mode — originally called out
  as a "possible follow-up" in Phase 3's shipped-scope notes but
  not planned as a discrete phase until then. Rollout slot is
  after Phase 4 because Phase 4 was already in flight when 3.5
  got formalized; nothing in 3.5's design depends on Phase 4, but
  reordering an in-flight phase would be pointless churn.

### Phase 1 — Planning + one representative test  *(SHIPPED 2026-07-24)*

**STATUS: SHIPPED 2026-07-24** as
[openemr/openemr#13149](https://github.com/openemr/openemr/pull/13149).
Delivered:
- Planning doc landed for community discussion (this doc, draft PR #12811)
- `tests/Acceptance/InstallTest.php` + `Support/ArtifactBrowser.php`
  + `phpunit.acceptance.xml` + `bootstrap.php`
- `.github/docker/acceptance-docker-compose.yml` compose override
- `tests/Acceptance/bin/boot-docker.sh` + `down-docker.sh` laptop helpers
- Symfony BrowserKit (`HttpBrowser`) — no Selenium needed for the
  login flow (form POST, no JS). Panther+Selenium deferred to Phase 4.

Exit criterion met: `InstallTest` runs from a developer laptop against
`openemr/openemr:latest`, verifies install completes + admin login works
(302 → `interface/main/tabs/main.php?token_main=<hex>` + 200 on follow).

### Phase 2 — Docker acceptance workflow + upgrade coverage  *(SHIPPED 2026-07-24)*

**STATUS: SHIPPED 2026-07-24** as
[openemr/openemr#13159](https://github.com/openemr/openemr/pull/13159).
Delivered:
- `.github/workflows/acceptance-docker.yml` — 3-scenario parallel
  matrix (fail-fast disabled): `fresh-install-from` (default
  `latest`), `fresh-install-to` (default `next`), `upgrade`
  (`from_tag` → `to_tag` with volume-preserving swap).
- `tests/Acceptance/UpgradeIntegrityTest.php` — post-upgrade admin
  login validation (session storage survived, users table intact,
  `token_main` machinery functional).
- `tests/Acceptance/Support/ResponseHeaders.php` — shared BrowserKit
  header-narrowing helper (extracted from InstallTest, reused in
  UpgradeIntegrityTest).
- Byte-identical enforcement in `.github/byte-identical.yml` covering
  the three acceptance surface entries. rel-820 excluded (predates
  Phase 1's `symfony/mime` require-dev addition; enforcement starts
  effectively at rel-830+).
- Triggers: workflow_dispatch (any tag pair), daily 09:00 UTC schedule
  (Docker Hub drift detection), push/PR on acceptance surface files.

Exit criterion met: workflow succeeds on `latest` → `next` upgrade
(first CI run passed all 3 matrix scenarios: 2m19s, 2m34s, 4m1s).

### Phase 2.5 — Build-from-codebase for PR validation *(SHIPPED)*

**STATUS: SHIPPED as #13163** (merge commit `00721eb0b8`).

Adds `build_locally: bool` workflow_dispatch input + auto-fire on
any PR/push touching `docker/release/**` (detected via `git diff`).
When enabled, a `build-image` job runs `docker build docker/release/`
against the PR's Dockerfile and hands the resulting `openemr/openemr:pr-built`
image to the acceptance matrix via a workflow artifact
(`docker save | zstd | upload-artifact` → `download-artifact | zstd -d
| docker load`, no registry needed).

Purpose is to answer "will this PR ship a broken image?" *pre-merge*
rather than catching it only in `docker-test-release` (which builds
but doesn't run the auto-upgrade path or exercise the installer against
real assertions).

Two invocation shapes fire from a single workflow run:

- **Fresh install of PR-built** (`fresh-install-to` scenario) —
  boots the built image, runs InstallTest.
- **`latest` → PR-built upgrade** (`upgrade` scenario) — boots real
  Docker Hub `latest`, then swaps to the PR-built image, runs
  UpgradeIntegrityTest. Higher-value: validates existing-user
  upgrade path.

**Known limitation (addressed by Phase 6, NOT Phase 5):** the
current Dockerfile does `git clone
https://github.com/openemr/openemr.git --branch ${OPENEMR_VERSION}`
at build time (defaults to `master`), so the PR-built image reflects
PR's Dockerfile applied to *master* source — NOT the PR's source.
Full-fidelity artifact testing (PR Dockerfile + PR source) needs
source-mode indirection in the Dockerfile — that's Phase 6, a
scoped-out follow-up. Phase 5 does NOT close this gap; it only
delivers content parity (see Phase 5's section).

Exit criterion: workflow succeeds with `build_locally: true` on a
scratch PR that intentionally touches the Dockerfile (e.g. adds a
no-op comment), demonstrating pre-merge PR-image validation works
against the current (hybrid) shape.

### Phase 3 — Package acceptance workflow — **SHIPPED 2026-07-25 (#13165)**

- Design the generic-stack compose file for tarball testing
  (`.github/docker/acceptance-package-compose.yml` — mariadb +
  php-apache with tarball mount)
- Draft `acceptance-package.yml`
- Adapt InstallTest + UpgradeIntegrityTest to work against a tarball-
  mounted stack (mostly by making the artifact endpoint configurable)

Naturally full-fidelity for the tarball path: the tarball artifact
IS `git archive HEAD` from the checkout, so pointing the workflow
at the PR's checkout produces the exact tarball that would ship.
(This is the property Phase 6 aims to replicate for the docker path.)

Exit criterion: workflow succeeds against an 8.0.0 → 8.2.0 tarball
upgrade path. Roughly 1 week.

**Shipped scope notes:**
- Fresh-install scenario runs on every push/PR/schedule against
  `TO_VERSION` (default 8.2.0).
- Upgrade scenario gated on `workflow_dispatch` until 8.3.0 releases —
  no earlier version has a shipped `.tar.gz` asset (v8_1_0 was cut but
  never shipped; patch releases only shipped `.zip`).
- Install choreography uses a standalone `install-helper.php` invoked
  via `docker compose exec` (not `/root/devtools dev-install` — that
  path depends on `/root/auto_configure.php` which the flex image's
  `openemr.sh` removes at boot with `EMPTY=yes`).
- Three guard layers on the helper: outside-webroot mount +
  `PHP_SAPI !== 'cli'` + `OPENEMR_ENABLE_ACCEPTANCE_HELPER=1` env
  opt-in (matches `contrib/util/installScripts/InstallerAuto.php`).
- `.github/docker/` added to dependabot; mariadb + flex images
  sha-pinned.

### Phase 3.5 — Build-from-codebase for package/tarball PR validation — SHIPPED 2026-07-26 (#13204)

**Fulfills the long-standing "test the release-tarball artifact
(both fresh install and upgrade paths) during the release process"
goal for the tarball side.** Phase 7a's tarball prong (release-prep
PR triggers) is now covered — a release-prep PR fires this workflow
with `build_locally=true`, PackageAssembler builds the tarball from
the PR checkout, and all four matrix scenarios (`fresh-install`,
`wizard-install`, `upgrade`, `wizard-upgrade`) exercise that tarball
end-to-end. The one remaining piece for full release-lifecycle
coverage is gating the actual publish on acceptance (see "Follow-up
release-time gate" below).

Analogous to Phase 2.5 for docker, but far simpler because
`PackageAssembler` already builds a tarball from local source (no
"checkout ref vs source ref" decoupling problem like the docker
Dockerfile has — that's why Phase 6 needs source-mode indirection
while 3.5 does not).

Added `build_locally: bool` workflow_dispatch input + auto-fire on
any PR/push touching `tools/release/**`, `build.xml`, or
`.gitattributes` (the surface `PackageAssembler` consumes at build
time). Also added a `detect-mode` job that resolves the flag
identically to acceptance-docker.yml (dispatch input wins;
otherwise diff the PR/push base against HEAD). When enabled, a
`build-tarball` job runs `PackageAssembler` against the PR
checkout, uploads `openemr-<to_version>.tar.gz` as a workflow
artifact (~200 MB via `actions/upload-artifact`, retention 1 day,
node-version 24 to match `build-release.yml`'s canonical shape).

The existing acceptance matrix legs then download+extract that
artifact instead of curling from the GitHub releases page. Two
new script flags carry the local-tarball path through the
existing scripts without breaking the from-GitHub path:

- `tests/Acceptance/bin/boot-package.sh --local-tarball=<path>`
  skips the curl step and extracts the given file. Used by the
  `fresh-install` + `wizard-install` scenarios to boot the
  PR-built tarball as to_version.
- `tests/Acceptance/bin/upgrade-package.sh --to-local-tarball=<path>`
  skips the to-version curl step (from_version always downloads
  from GitHub — the whole point of the upgrade test is starting
  from a shipped release). Used by `upgrade` + `wizard-upgrade`
  scenarios.

Both flags `realpath`-normalize the input before the `cd $REPO_ROOT`
so a caller-supplied relative path resolves correctly at extraction.

Also unblocks CI's `upgrade` + `wizard-upgrade` scenarios: they
were workflow_dispatch-only because from_version needed a shipped
earlier tarball as its input. With PR-built acting as to_version
and the derived shipped predecessor (see #13573, described below)
as from_version, those scenarios now fire whenever `build_locally=true`
via `github.event_name == 'workflow_dispatch' || needs.detect-mode.outputs.build_locally == 'true'`.
The synthetic `99.99.99` label default satisfies `upgrade-package.sh`'s
equality/downgrade guards without needing a real version bump; it's a
naming-only label (drives artifact filename + scratch-dir name),
because `sql_upgrade.php` reads `--from=FROM_VERSION` and derives
the target from the DB `version` table populated by the from-install.

**Release-prep PR handling.** `detect-mode` also detects
`release-prep/*` head branches (pull_request `head_ref` or push
`ref` stripped) and force-enables `build_locally=true` regardless of
what files the PR touches — the whole point of `release-prep.yml`'s
conductor PR is validating the tarball the release would ship. On
match, the block parses `X.Y.Z` out of the `chore(release): prep
X.Y.Z` PR title so downstream logs and the artifact filename reflect
the actual release version rather than the 99.99.99 synthetic
default. Falls back to 99.99.99 on push events (no PR title context)
or when the title regex misses.

The trigger surface intentionally does NOT include `version.php` —
that file gets bumped in many non-release contexts (dev-cycle bumps,
backport version ticks, etc.) and adding it to paths would over-fire.
The intended long-term wire-up is `release-prep.yml` explicitly
dispatching this workflow after opening/updating the release-prep PR
(`gh workflow run acceptance-package.yml --ref release-prep/<branch>
-f build_locally=true`), coupling the trigger to the release-prep
concern itself. That dispatch depends on Phase 3.5 being present on
the target rel-branch (rel-830+, since Brady chose not to backport
to rel-820).

**Additional bug fixed:** `upgrade-package.sh`'s `cp -a ${FROM}/sites
${TO}/sites` had a latent perm-denied on OAuth key files
(`sites/default/documents/certificates/oaprivate.key` / `oapublic.key`)
created by the flex-image apache uid, unreadable to the host runner
user. Was previously masked by the "identical from/to versions" bug
that fired first. Fix runs `docker compose exec openemr chown -R ...`
inside the still-live from-container to rewrite ownership via the
shared bind mount before the cp; bounded to `sites/` since ownership
is discarded seconds later when compose recreates the container with
the to-version bind mount.

Notable implementation detail from the local validation walk: the
`--release-version` arg to `PackageAssembler` is a naming label
only (drives staging-dir + artifact filename), not baked into the
packaged codebase — the shipped self-reported version comes from
`version.php` in the source tree, which the assembler ships as-is
from the checked-out ref. Using the workflow's `to_version` value
keeps the artifact filename aligned with what `boot-package.sh`'s
`<version>` arg + scratch-dir naming expects downstream.

**`TO_VERSION` (label) vs `EXPECTED_VERSION` (actual) split**
(openemr/openemr#13753): downstream of the label-is-cosmetic
principle above, the version-display / version-api acceptance
groups compare the running artifact's self-reported version
against `ACCEPTANCE_EXPECTED_VERSION`. That env var is populated
from `detect-mode.outputs.expected_version` (not `to_version`)
precisely because on `build_locally=true` the two diverge — label
stays `99.99.99` (cosmetic; drives filenames) while
`expected_version` reads the checkout's `version.php` (what
`sql_upgrade.php` actually writes to the DB `version` table). On
the shipped-tarball path the two are equal (label = actual) because
the release process bumps `version.php` in the checkout tree to the
release version BEFORE packaging — so the tarball ships with
`version.php` already equal to its download-URL label. The equality
comes from the release-prep flow, NOT from `PackageAssembler` baking
`--release-version` into anything (see the paragraph above; the
assembler ships `version.php` as-is from the checked-out ref). On
build_locally there is no release-prep pass, so `version.php` stays
at whatever the checkout has (e.g. `8.4.0`) while the label defaults
to the synthetic `99.99.99` — hence the divergence. Coupling the
assertion to `TO_VERSION` — the pattern that #13635 originally
shipped, and #13753 later corrected — made the acceptance groups
un-passable on `build_locally=true`. Do not re-couple.

**Follow-up fix (openemr/openemr#13786)**: #13761 covered the six
workflow-level `ACCEPTANCE_EXPECTED_VERSION` sites (the PHPUnit
version-display / version-api groups) but missed the two identical
shell-level DB assertions in `boot-package.sh` and `upgrade-package.sh`,
which compared `DB_VERSION` against their positional `VERSION` /
`TO_VERSION` args (i.e. the cosmetic label). Same divergence, same
symptom (`post-install DB version '8.4.0' does not match expected
'99.99.99'`), same failure mode as before the label-vs-actual split
was recognized. Follow-up makes both shell guards read
`ACCEPTANCE_EXPECTED_VERSION` env if set (falling back to the
positional arg for dev-time standalone runs), and promotes
`ACCEPTANCE_EXPECTED_VERSION` from per-step assignments to a job-level
env so both the PHPUnit consumers and the shell scripts see the same
value automatically. Discovered by a `workflow_dispatch -f
build_locally=true` smoke test against master post-#13761 merge —
that PR's own CI hadn't exercised the build_locally path because
#13761 didn't touch `tools/release/**`.

**Second follow-up (openemr/openemr#13790)** discovered by re-running
the same smoke test after #13786 landed: the About page displays
`SoftwareVersion::__toString()` which returns
`"{base}[{tag}][.{realpatch}]"`, so on master (with `$v_tag='-dev'`)
it renders `8.4.0-dev` while the DB `version` table only holds `8.4.0`
(schema has no v_tag column). `ACCEPTANCE_EXPECTED_VERSION` intentionally
stays at X.Y.Z shape (uniform with the DB + `/api/version` assertions,
both of which return X.Y.Z), and `VersionDisplayAcceptanceTest` was
updated to compare against the X.Y.Z prefix of the About page's
displayed value — dropping the mid-cycle suffix. On shipped tarballs
release-prep sets `$v_tag=''` + `$v_realpatch=0`, so the strip is a
no-op there; the divergence only shows up on `build_locally=true`
against a dev-tagged checkout.

**Third follow-up (openemr/openemr#13791)** cleaned up a related
ergonomic bug in the same code path: the workflow's `to_version`
`workflow_dispatch` input had been `required: true` with
`default: '8.2.0'`. On a manual dispatch with `-f build_locally=true`
but no `-f to_version=`, the input default (`8.2.0`) still overrode
`emit_to_version`'s build_locally branch (which would otherwise
resolve to `99.99.99`, matching the auto-fire path). The stale
default also drove downgrade failures when the derived `from_version`
was >= `8.2.0` (e.g., `8.3.0` on master → 4 of 8 acceptance jobs
tripped `upgrade-package.sh`'s ordering guard for reasons unrelated
to what the operator was actually testing). The `workflow_call`
variant of the same input already had the correct shape
(`required: false`, `default: ''`); the two triggers had diverged
and `workflow_dispatch` never got updated. Matched `workflow_call`'s
shape — `emit_to_version`'s `[[ -n "${DISPATCH_TO_VERSION}" ]]`
guard already handled empty, so no shell script or downstream
changes needed. Explicit `-f to_version=X.Y.Z` still wins over the
empty default. Answer to "why isn't `to_version` required?" for
future readers.

Exit criterion (met): end-to-end `build_locally=true` demo on a
real runner produced 6/6 green — `detect-mode`, `build-tarball`
(PackageAssembler produced tarball from PR HEAD), `fresh-install`,
`wizard-install`, `upgrade` (8.2.0 → PR-built 99.99.99), `wizard-upgrade`.
See PR #13204's test plan for the run link.

**Follow-up release-time gate (not yet scoped as a phase):**
Phase 3.5 validates the PR-built tarball, which is produced by the
same `PackageAssembler` invocation that release-time will use — so
in practice a passing Phase 3.5 gives very high confidence in the
actual release-time tarball. Closing the last gap (validating the
literal published artifact before it lands on the GitHub releases
page) requires:

- Adding `workflow_call` trigger to `acceptance-package.yml` so it
  can be invoked as a reusable workflow with a "here's the tarball,
  don't fetch from GitHub" input (uses the same `--local-tarball`
  plumbing already in place).
- Modifying `build-release.yml` to a build → validate → publish
  sequence: the existing build job's output tarball flows into an
  acceptance-package call, and the "upload to GitHub release" step
  gains `needs: [acceptance] && if: needs.acceptance.result ==
  'success'`. If acceptance fails, the tarball never publishes.

Small follow-up scope. Could ship alongside Phase 7b's
repository_dispatch listener for `openemr-tag` events (fires
acceptance against a just-shipped floating-tag artifact within
minutes of publish) — the two together give both pre-publish
gating and post-publish latency-catching.

### Phase 3.6 — ZIP acceptance coverage *(SHIPPED 2026-07-29: slice 1 via #13261 + slice 2 via #13262)*

**Slice 1 (SHIPPED #13261):** ZIP coverage for `fresh-install` +
`wizard-install` scenarios only — the ones that use
`boot-package.sh` end-to-end. Delivered via `boot-package.sh
--local-zip` + acceptance-package.yml matrix.include with
per-scenario format expansion + workflow_call
`caller_zip_artifact` input + build-release.yml passing both
tar and zip artifacts to acceptance-gate. Default matrix
grows from 2 cells to 4 (fresh-install × [tar, zip] +
wizard-install × [tar, zip]); expanded matrix (workflow_dispatch
or build_locally) grows from 4 cells to 6 (adds upgrade[tar] +
wizard-upgrade[tar], both tar-only).

**Slice 2 (SHIPPED #13262) — upgrade-side ZIP.**
`upgrade-package.sh` gained `--to-local-zip`
mirroring boot-package.sh's slice-1 pattern (mktemp -d + unzip
+ nullglob single-top-level enforcement + mv). Matrix grew to
8 cells on the expanded path (all 4 scenarios × [tar, zip]);
default matrix unchanged at 4 (install-side only). Boot-flags
step's `to_upgrade_local_flag` switches on `matrix.format`
between `--to-local-tarball` and `--to-local-zip`.

FROM-version side stays tar-only — always downloaded from
GitHub Release as .tar.gz. The whole point of an upgrade test
is starting from real shipped release bytes; only the TO side
varies between local-built PR artifact and shipped tag.

**Trigger-time observations (all captured 2026-07-29).**

Testing surface for the tarball/zip artifact split by trigger:

  * **push / schedule** (default `build_locally=false`) — tests
    already-released packages. Downloads `openemr-<from>.tar.gz`
    + `.zip` from the GitHub Release page. No PackageAssembler
    run.
  * **workflow_dispatch** — operator toggles `build_locally`.
    Off → tests shipped release; on → PackageAssembler builds
    from PR checkout, then tests.
  * **push/PR touching `tools/release/**`** — Phase 3.5 auto-
    detect flips `build_locally=true` → build then test.
  * **release-prep.yml dispatch** — always `build_locally=true`,
    PackageAssembler builds from the release-prep PR checkout.
  * **workflow_call from build-release.yml** (release-time
    gate) — caller passes `caller_tarball_artifact` +
    `caller_zip_artifact` → tests the exact bundle
    build-release.yml just produced. Bytes tested = bytes
    published, atomically.

Both tar and zip validated across ALL of these — full parity
for the format dimension.

**Default-matrix upgrade coverage.** Today's default matrix (push
/ schedule / non-`tools/release/**` PR) is install-only. Upgrade
cells only exist when `from != to`. With post-#13573 auto-
derivation, that condition holds automatically on any branch whose
newest `sql/*-to-*_upgrade.sql` maxes out below the workflow's
`to_version` default (which stays static — the target under test
doesn't depend on the checkout's shape). On the build_locally path
the pair is derived-predecessor → 99.99.99 (PR-built synthetic
label); on workflow_call it's derived-predecessor → the version
being shipped. Moving upgrade scenarios into the plain scheduled
default matrix — so daily runs exercise the shipped `latest-1 →
latest` path continuously and would catch a base-image regression
or release-page availability issue without needing operator
dispatch — would additionally require a mechanism that keeps
`to_version` aligned with the latest shipped release, since the
current static default drifts stale as new versions ship. Small
follow-up scoping question, not a full phase.

**`from_version` auto-derivation from checkout (openemr/openemr#13573,
SHIPPED post-8.3.0).** Historically `FROM_VERSION` defaulted to a
hardcoded `8.2.0` in the workflow. That works on master (currently
8.4.0-dev — 8.2.0 is a valid predecessor in master's `sql_upgrade.php`
wizard dropdown) and on rel-830 (8.3.x line — 8.2.0 is that line's
last cross-line predecessor). But it breaks on rel-820: 8.2.0 is
rel-820's own line, not an upgrade-from, so rel-820's wizard dropdown
stops at 8.1.1 and the acceptance test's dropdown-membership assertion
fails when tested with FROM=8.2.0. Auto-firing acceptance runs on
rel-820 sync PRs hit this consistently. Fix: derive `from_version` at detect-mode time via:

1. **Enumerate all from-version candidates** from the checkout's
   `sql/*-to-*_upgrade.sql` filenames (the value before `-to-` on
   each file). Same convention `SqlUpgradeSkeletonMutator` +
   `DockerUpgradeScaffoldMutator` use for prior-version derivation
   during release-prep (see `src/Common/Command/ReleasePrep/Mutator/`)
   — the sql-filename pattern is the project-wide
   "shipped-predecessors" source; every entry is guaranteed to
   appear in the branch's `sql_upgrade.php` wizard dropdown.
2. **Intersect** that candidate set with the website-openemr
   `data/releases.json` manifest (the authoritative "which
   versions have actually shipped tarballs on GitHub Releases"
   source of truth, filtered to `status: FINAL`).
3. **Take the MAX** of the intersection — the newest from-version
   that BOTH satisfies dropdown-membership AND has a downloadable
   tarball.

Neither the sql set nor the manifest set alone is sufficient.
sql-only would return a version scaffolded by patch-prep before
it ships (e.g., `8_3_1-to-8_4_0_upgrade.sql` on master mid-8.3.1-
prep would return 8.3.1, and boot-package.sh would 404 trying to
download `openemr-8.3.1.tar.gz`). Manifest-only would return a
released version that isn't in the current branch's dropdown
(e.g., a rel-830 test with from=8.3.0 fails dropdown-membership).

Operator can still override via explicit `from_version` input on
dispatch or `workflow_call` — the override skips both derivation
steps and only shape-validates the value. See
`.github/scripts/detect-acceptance-mode.sh` + its bats tests for
the shape. Fetch is retried 3× with a 30s hard timeout; a
persistent fetch failure exits the run loudly rather than
silently falling back to sql-only derivation, since the whole
point of the filter is to guarantee shipped-status.

**Original scoping (as-scoped 2026-07-29, preserved for
context):**

Phase 3 through Phase 7c-tarball built the full "test the shipped
tarball, at PR time + release-time gate" story. But the shipped
package artifact list is TWO files: `openemr-<version>.tar.gz`
AND `openemr-<version>.zip`. Only the tarball flows through
acceptance today; the ZIP ships tested only in the "same
PackageAssembler produced both" sense (byte-similar content
guarantee, not black-box behavior guarantee). Deferred-known-debt
entry in the doc from day one; promoted here now that the tarball
path is stable and the "every artifact tested" goal is in reach.

ZIP-only bugs are rare (both files are produced by the same
PackageAssembler pass over the same source tree) but not
theoretical:

  * Line-ending translation (`\r\n` vs `\n`) — ZIP toolchains
    sometimes normalize; PackageAssembler doesn't. A regression
    that switched to a normalizing zip tool would produce a
    subtly-broken ZIP.
  * Executable-bit preservation — ZIP encodes exec-bit via
    extra-field metadata (unix external attributes); some
    extractors on some platforms drop it silently. Real user
    impact: `.sh` scripts in the extracted tree can't run.
  * Empty-directory handling — ZIP treats zero-file dirs as
    optional entries; PackageAssembler may skip them. Some
    OpenEMR install paths expect specific empty dirs to exist.
  * Path-separator handling — ZIP uses `/` (like tar), but
    extractors on Windows can transform to `\`. Content-wise
    fine, but if any OpenEMR runtime code does path-string
    comparison, could surface only on ZIP-extracted installs.

Design mirrors the tarball path:

  * **`boot-package.sh`** grows a `--local-zip <path>` flag
    alongside the existing `--local-tarball`. Uses `unzip`
    instead of `tar -xzf`; otherwise identical extract-into-
    scratch-dir → point compose at it flow.
  * **`build-tarball` job** (name becomes slightly misleading —
    could rename to `build-package` for consistency with
    build-release.yml's terminology) uploads both artifacts.
    PackageAssembler already produces both; just add the ZIP to
    the upload-artifact step.
  * **`acceptance-package.yml` matrix** gains a `format: [tar, zip]`
    dimension. Cartesian with the existing scenario dimension:
    fresh-install / wizard-install / upgrade / wizard-upgrade
    × tar/zip = 8 cells (vs current 4). Runtime doubles but
    parallelizes — wall clock similar.
  * **`acceptance-package.yml`'s workflow_call trigger** gets
    a `caller_zip_artifact` input alongside `caller_tarball_artifact`.
    When Phase 7c-tarball's build-release.yml calls it (release-
    time gate), both artifacts flow through.
  * **`build-release.yml`'s build-package job** already produces
    both files via `task package:assemble`; just add the ZIP to
    its upload-artifact list. The publish job's `gh release
    upload` already publishes both (byte-identical publish
    unchanged) — Phase 7b's sha256 re-verify already covers both
    since it uses the shipped-asset list.

Coverage after landing:

  * Release-prep PR: auto-fired acceptance-package now tests
    both formats (via the same release-prep.yml dispatch that
    Phase 3.5 already wired).
  * Release-time gate: Phase 7c-tarball's build-release.yml
    workflow_call passes both artifacts; publish only proceeds
    if acceptance passes on both formats.
  * PR touching tools/release/**: same doubled coverage via the
    Phase 3.5 auto-detect mechanism.

Not covered (out of scope for 3.6): ZIP extraction on Windows
itself. The acceptance runners are Ubuntu; Windows-native
extraction quirks stay outside the automated harness (would need
a windows-2022 runner + PowerShell Expand-Archive test — real
work + real value but separate phase).

Exit criterion: a synthetic ZIP-only regression (e.g., temporarily
mutate PackageAssembler to strip exec bits on ZIP output) is
caught by the fresh-install (zip) scenario in release-prep PR CI.
~2 days of implementation.

### Phase 4 — Broaden test coverage

Sliced for reviewability. Rollout order: 4a → 4a-2 → 4b → 4c → 4a-3.
(4a-3 was originally sequenced third but shifted to last because
its API-enable prerequisite is best handled via Panther admin-panel
automation — which needs 4b's Panther plumbing to exist first — and
because the authenticated-flow assertions dovetail naturally with
4c's wizard-UI work rather than standing alone.)

**4a — SHIPPED 2026-07-25 (#13193)**. `FhirSmokeTest` (unauth
`/apis/default/fhir/metadata` + `/apis/default/fhir/.well-known/smart-configuration`
per FHIR + SMART spec) tagged for both `fresh-install` and
`post-upgrade` groups. `Support/LoginFlow` extracted from the
duplicated admin-login flow in InstallTest + UpgradeIntegrityTest.

**4a-2 — SHIPPED 2026-07-26 (#13194)**. `OAuth2SmokeTest` asserting
the API-disabled 404 gate on `/oauth2/default/*` — production docker
ships with rest_api/rest_fhir_api/rest_portal_api all `0` by default,
and `OAuth2AuthorizationListener` returns 404 with `"API is disabled"`
for all `/oauth2/*` paths in that state. Two tests: OIDC discovery
gate + DCR gate, both asserting 404 + `Content-Type: application/json`
+ `message` containing "API is disabled". Complements 4a's
FhirSmokeTest (which proves FHIR discovery BYPASSES the auth gate
via `SkipAuthorizationStrategy`) by proving OAuth2 does NOT bypass
it. Original plan for this slice was successful DCR + authenticated
`/api/version` — cut back after local repro on `openemr/openemr:latest`
showed OAuth2 endpoints are 404-gated on any default install.

**4b — SHIPPED 2026-07-26 (#13196)**. `Support/BrowserSession`
Panther factory (local ChromeDriver + Selenium-grid backends,
env-var selected via `SELENIUM_USE_GRID`) + `E2eCriticalPathTest`
asserting admin login → Knockout main menu render. Chrome install
in both acceptance workflows via `nanasess/setup-chromedriver@v2`
matched against the runner's pre-installed google-chrome — chosen
over `browser-actions/setup-chrome@v1` after that action's
Chrome/ChromeDriver release-cadence drift shipped a mismatched pair
mid-PR. Original scope of "admin login → patient add → encounter
start" cut to just login+menu-render for the first Panther-in-
acceptance PR — more critical-path steps land as follow-ups once
plumbing is proven in CI. Foundation that 4c and 4a-3 both build on.

**4c — sliced into 4c-1 and 4c-2.** Wizard-UI tests, tarball-only —
docker skips wizards entirely via env-var auto-install. Depends on
4b's Panther plumbing.

  * **4c-1 — SHIPPED 2026-07-26 (#13198)**. `InstallWizardUiTest`
    walks setup.php state machine 0 → 1 → 2 → 3 via Panther, then
    verifies GET / redirects to the login page as the definitive
    "install actually took effect" signal. `boot-package.sh` grew
    a `--skip-install-helper` flag so the artifact serves setup.php
    on a fresh boot; `acceptance-package.yml` matrix grew a
    `wizard-install` scenario firing on push/PR/schedule. States
    4-7 (informational pages: PHP config, web server config, theme
    select, final credentials display) not walked — the login-page
    redirect is a stronger signal than walking decorative pages.
    Documented two Panther form-fill quirks inline: (1) submitForm
    silently drops POSTs when the value array contains a field
    the DOM lacks, and (2) button-click sometimes doesn't fire
    when the button label has nested icon markup — switched to
    findElement+sendKeys and form-element `.submit()` respectively.

  * **4c-2 — SHIPPED 2026-07-26 (#13199)**. `UpgradeWizardUiTest`
    walks sql_upgrade.php's version-selector form via Panther:
    load /sql_upgrade.php → select from-version → submit → wait
    for "Database and Access Control upgrade finished." marker →
    verify GET / redirects to login. `upgrade-package.sh` grew a
    `--skip-sql-upgrade` flag (extracts to-tarball + overlays
    from-version's sites/ + swaps bind mount, but skips step 5's
    CLI sql_upgrade). `acceptance-package.yml` matrix grew a
    `wizard-upgrade` scenario, gated on workflow_dispatch alongside
    the plain upgrade scenario (same "no earlier tarball" reason).
    Panther interaction quirks documented: form-element `.submit()`
    left DOM stale on sql_upgrade.php (switched to button.click());
    option.click() didn't fire the select's change handler in
    headless mode (switched to executeScript with dispatchEvent).

**4a-3 — SHIPPED 2026-07-26 (both slices, #13201 + #13203).**

  * **4a-3 (1/2) — SHIPPED 2026-07-26 (#13201)**. Panther admin-
    panel bootstrap for API-enable + `site_addr_oath` (via new
    `tests/Acceptance/bin/api-enable.php`, guarded by
    `OPENEMR_ENABLE_API_BOOTSTRAP=1` opt-in + CLI-only guard) plus
    `OAuth2ApiEnabledTest` with the successful-flow OIDC discovery
    + DCR assertions that were cut back from 4a-2. Workflow wired
    in both acceptance-docker and acceptance-package to run the
    bootstrap after the fresh-install group, then the api-enabled
    group. Bootstrap resolves target fields by label-text walk
    since form_N indices shift when globals.inc.php gets added-to.

  * **4a-3 (2/2) — SHIPPED 2026-07-26 (#13203).** Full
    authenticated Bearer-token `GET /apis/default/api/facility`
    with a real access token minted via DCR + auth-code flow (login
    form scrape + consent form scrape + code exchange), via new
    `Support/OAuth2/AuthCodeFlow` helper and `ApiSmokeTest`.

    Two mid-implementation pivots from the original description
    above:

      * **/api/version → /api/facility.** `/api/version` turned
        out to be on the `SkipAuthorizationStrategy` skip-list
        (see `src/RestControllers/Subscriber/AuthorizationListener.php`)
        — a Bearer request against it never actually exercises
        token validation. `/api/facility` requires `api:oemr` +
        `user/facility.*` SMART scope + `admin/users` ACL +
        Bearer strategy pass, so the endpoint exercises every
        layer in the dispatch pipeline. Fresh install always has
        at least one facility (the one the install wizard
        creates), so no seeding needed.

      * **Inline admin client-approval step added.** DCR-registered
        clients requesting any `user/` or `system/` scope land in
        `is_enabled=0` per `ScopeRepository::hasScopesThatRequireManualApproval`;
        `/token` returns `invalid_client` until an admin approves
        the client via `/interface/smart/admin-client.php`. The
        helper drives that approval through a second HttpBrowser
        session (admin auth via `LoginFlow`, then GET
        `?action=edit/<id>/enable&csrf_token=<t>`) — cheaper than
        replicating openemr's manual-approval rules on the harness
        side, and unconditional so the flow keeps working if the
        rules change. Not covered by the source-side
        `AuthorizationLogoutFullFlowTest` (openemr/openemr#13175)
        that this ports from, since that test seeds `is_enabled=1`
        directly in the DB.

Two prerequisites, both handled inside this slice:

  1. **API-enable via Panther admin-panel automation.** Post-install,
     drive a Panther session into the admin panel (Administration →
     Globals → Connectors) and flip `rest_api`, `rest_fhir_api`,
     `rest_portal_api` to enabled. Then re-hit /oauth2/* — should
     now return 200 with the actual OIDC discovery / DCR responses.
     This is what a real admin would do to turn the API on. Depends
     on 4b's Panther plumbing being in place.

     Alternative considered: install-time env-var override (add
     OE_ENABLE_API=1 handling to install-helper.php +
     docker/production/openemr.sh, flip globals via post-install
     SQL). Faster to land but changes the artifact-under-test's
     default configuration for the test run and doesn't exercise
     the real user-facing enable path. Rejected in favor of the
     Panther admin-panel approach for fidelity.

  2. **`site_addr_oath` install-time configuration.** OpenEMR's
     token endpoint stamps the `iss` claim from
     `globals.site_addr_oath`. The artifact detects its own base
     URL from `HTTP_HOST` at install time, but the acceptance
     runner hits it at a DIFFERENT URL (docker container's internal
     alias vs mapped host port) — so minted tokens' `iss` doesn't
     match the acceptance URL. Fix: env-var override for
     `site_addr_oath` in `install-helper.php` +
     `docker/production/openemr.sh` (or drive the same value
     through Panther admin-panel automation alongside the API-enable
     flip — one-time setup, then all authenticated tests work).

Once both prerequisites are addressed inside this slice, the
authenticated `/api/version` GET works end-to-end, and the
successful-flow OIDC/DCR assertions that were held back from 4a-2
can land as sibling tests in the same PR.

**Phase 4 continuation candidates (identified 2026-07-28, scoped
2026-07-29 — parked until Phase 7d + 3.6 finish).** With 4a → 4c
→ 4a-3 all shipped, two adjacent test surfaces stand out as
natural extensions of the Acceptance suite. Both are scoped in
detail below; slicing waits until Phase 7d (arm64 restoration +
PR-time coverage) and Phase 3.6 (ZIP acceptance) finish, since
those close open gaps on already-shipped surfaces and 4d/4e are
net-new coverage broadening.

**4d — absorb `docker/container_benchmarking/test_suite.sh` into
Acceptance.** 1700-line bash suite exercising release/binary/flex
containers end-to-end. Driven by
`docker-test-container-functionality.yml` on push/PR against
`docker/{container_benchmarking,release,binary,flex}/**` paths
(no daily schedule). Consolidation + delete-duplication play; the
Swarm/K8s/Redis-sessions paths are the interesting keepers.

Per-function slicing map (scoped 2026-07-29):

  | Function                | Class         | Effort | Notes |
  |-------------------------|---------------|--------|-------|
  | fresh_installation      | DUPLICATE     | 0      | Covered by `InstallTest`. Delete bash version after parity check. |
  | manual_setup            | PORTABLE      | Small  | Net-new. `MANUAL_SETUP=yes` boot; assert `auto_configure.php` present + OpenEMR NOT configured. |
  | ssl_configuration       | PORTABLE      | Medium | Net-new. HTTPS on :443 with self-signed cert; Panther health-check via HTTPS. |
  | redis_sessions          | PORTABLE      | Small  | Net-new. `REDIS_SERVER` env; assert 99-redis-sessions.ini + marker file. |
  | xdebug_configuration    | PORTABLE      | Small  | Net-new. `XDEBUG_ON=1`; `php -m` assert + opcache-disabled assert. Binary variant skipped upstream. |
  | document_upload         | PORTABLE      | Small  | Net-new. Filesystem-permission touch-test on /sites/default/documents. |
  | swarm_mode              | BASH-NATIVE   | Large  | Multi-container leader/follower coordination; docker-completed marker; shared sites volume. Not expressible as PHPUnit. |
  | kubernetes_mode         | BASH-NATIVE   | Large  | K8S admin/worker roles; shared sites volume + service_completed_successfully. Orchestration-topology test. |
  | docker_upgrade          | BASH-NATIVE   | Large  | Version-mismatch detection + fsupgrade-N.sh + marker update. State-machine behavior, not browser-driven. |

Recommended shape:

  * **Slice 1 (~3-4 days):** delete `fresh_installation` bash test;
    port `manual_setup`, `redis_sessions`, `xdebug_configuration`,
    `document_upload`, `ssl_configuration` as
    `tests/Acceptance/{ManualSetup,RedisSessions,XDebug,DocumentUpload,Ssl}Test.php`.
    ~510 lines total. High-confidence, low-friction; validates the
    absorption pattern.

  * **Slice 2 (~larger — separate design phase):** extract
    `swarm_mode` + `kubernetes_mode` + `docker_upgrade` into a
    dedicated bash-based Acceptance harness at
    `tests/Acceptance/docker-orchestration-suite.sh`. Different
    concern (deployment topology, not application behavior) so
    keeping bash + running as a distinct CI group makes sense.
    Timing: whenever the current bash suite starts drifting or
    needs a real reason to consolidate.

  * **Delete post-slice-1:** the docker-test-container-functionality.yml
    workflow's fresh_installation coverage. Keep the workflow
    itself for the three bash-native tests until slice 2 lands.

**Phase 4d BLOCKED as-scoped 2026-08-01 — variant coverage gap.**
Slicing map originally sliced by *function*, but `docker-test-
container-functionality.yml` runs `test_suite.sh` against release
+ binary + flex (3 separate jobs). The PHPUnit Acceptance harness
(`boot-package.sh`, `boot-docker.sh`) only targets the release
image. **Every port would lose flex + binary coverage** for the
ported function. Even the "pure duplicate" delete (`fresh_
installation`) loses binary + flex fresh-install coverage since
`InstallTest` (PHPUnit) only exercises release.

Options considered:

  1. **Extend Acceptance harness with flex + binary variants**
     first, then port. Adds ~1 week harness work before touching
     ports. Also opens design questions: does boot-package.sh even
     make sense for flex (dev-oriented bind-mount)? Does the
     Panther-based flow work against flex's stack?
  2. **Delete only `fresh_installation` bash** (pure duplicate for
     release) + keep bash for flex/binary + release-variant-of-
     the-others. Partial-hybrid — achieves audit item #3
     (eliminate duplication) for release only; nothing else.
  3. **Port release variants + keep bash for flex/binary** —
     hybrid. Would need docker-test-container-functionality.yml to
     skip release, run only flex + binary.
  4. **Accept flex + binary coverage loss** — regression.

Decision (2026-08-01, per user): don't extend Acceptance scope
AND don't lose tests. Every option violates one constraint or the
other. **Phase 4d is skipped indefinitely** — revisit if either
constraint relaxes (e.g., if flex/binary variants gain natural
Acceptance-harness coverage via a separate effort, or if flex/
binary phase out entirely). The audit item #3 duplication cost is
low (fresh_installation bash + PHPUnit InstallTest both continue
to run) — not worth breaking a constraint over.

**4e — reuse `tests/Tests/E2e/*` against shipped artifacts.**
Dev-checkout suite has 17 Selenium test classes. `E2eCriticalPathTest`
(#13196) established the "take a dev-checkout E2E flow and run
against a shipped artifact via Panther" pattern for admin login +
main-menu-render.

Per-class slicing map (scoped 2026-07-29):

  | Class                                 | Effort | Notes |
  |---------------------------------------|--------|-------|
  | AaLoginTest                           | Small  | **SHIPPED 2026-08-02 as #13336 (partial port — 2/5 scenarios).** Login-page-happy-path duplicated E2eCriticalPathTest; admin.php-disabled scenarios impossible against release image (openemr.sh deletes admin.php post-configure). Ported: testLoginUnauthorized + testurlWithoutTokenShouldRedirectToLoginPage. Support/ extract SHIPPED 2026-08-02 as #13344. Post-upgrade dual-tag SHIPPED 2026-08-02 as #13345. |
  | GgUserMenuLinksTest                   | Small  | **SHIPPED 2026-08-02 as #13338 (full port — 5/5 scenarios).** All menuLinkProvider scenarios (Settings, Change Password, MFA Management, About OpenEMR, Logout). Absorbed release-image-specific gotcha: shipped image shows a Product Registration modal on first login that intercepts user-icon click; dismissed via jQuery `.modal('hide')` after waiting for Bootstrap's show-transition. Support/ extract SHIPPED 2026-08-02 as #13344. Post-upgrade dual-tag SHIPPED 2026-08-02 as #13345. |
  | FrontPaymentCssContrastTest           | Small  | **SHIPPED 2026-08-02 as #13340 (full port — 1/1 scenario).** `testReceiptCssHasExplicitTextColor` — CSS-inspection assertion on `front_payment.php?receipt=1`, real signal for openemr#10842 (light/solar-theme text visibility). No login needed, no modal-dismiss needed. Support/ extract SHIPPED 2026-08-02 as #13344. Post-upgrade dual-tag SHIPPED 2026-08-02 as #13345. |
  | KkEncounterFormNavbarUrlTest          | Small  | **SHIPPED 2026-08-02 as #13341 (full port — 1/1 scenario).** First 4e port requiring UI-driven seeding (patient + encounter). Menu XPaths calibrated live via Panther probe against booted stack — release-image uses `Patient` label (not `Patient/Client`) and `<div class="menuLabel">` (not `<a>`); dev-checkout blind-copy would silently break. Modal-dismiss included defensively. Support/ extract SHIPPED 2026-08-02 as #13344. **Post-upgrade dual-tag DEFERRED**: WebDriver `alertIsPresent()` timeout surfaced on rel-820 sync PR #13342's upgrade scenario pre-flight (line 412 inside `addEncounterViaUi`); dual-tag ships bundled with the flake root-cause fix. |
  | BbCreateStaffTest                     | Medium | UI-driven user creation. **SHIPPED 2026-08-04 as #13383.** Third 4f Medium port. New `addStaffUserViaUi()` helper on UiSeedingTrait (Admin → Users → Add User modal → fill `rumple`/`stiltskin`/`fname`/`lname`/`adminPass` via JS value-set → Save → wait modal-close → wait users-table row). Historically the flakiest test on core E2E; acceptance port uses JS value-set (fires input/change, NOT keyup) to side-step the `checkPasswordStrength` onkeyup handler + adds the source-side defensive gates (wait for submitform function defined, field-value verification loop). Per-instance random `foobar<suffix>` username (8-byte suffix per rabbit round-1 nit). Locally smoke-tested end-to-end against dev stack before push. Dual-tagged fresh-install + post-upgrade. |
  | CcCreatePatientTest                   | **SKIP** | **PERMANENTLY SKIPPED per user 2026-08-04.** Would be an attribution-only port with no new functional coverage: `addPatientViaUi` (Cc's underlying flow) is already exercised by KkEncounterFormNavbarUrlAcceptanceTest + DdOpenPatientAcceptanceTest as their setup on every acceptance CI run (8+ executions per PR cycle across all scenarios × arches × prongs). A patient-create regression would already surface via Kk/Dd failures. Standalone Cc port would add maintenance surface for a "clearer failure attribution" benefit that isn't a real pain point today. |
  | DdOpenPatientTest                     | Medium | Requires seeded patient. **SHIPPED 2026-08-02 as #13354.** First 4f Medium port. New `openPatientViaUi(fname, lname)` helper on UiSeedingTrait (search-by-lastname via `frm_search_globals` → click finder result → land on dashboard). Dual-tagged fresh-install + post-upgrade from the start (per-instance seed identity from #13351 makes both phases safe). |
  | EeCreateEncounterTest                 | **SKIP** | **PERMANENTLY SKIPPED per user 2026-08-04.** Same rationale as Cc — attribution-only, no new coverage: `addEncounterViaUi` (Ee's underlying flow) is already exercised by KkEncounterFormNavbarUrlAcceptanceTest on every acceptance CI run (6 executions per PR cycle). An encounter-create regression would surface via Kk failure. |
  | FfOpenEncounterTest                   | Medium | Requires seeded encounter. **SHIPPED 2026-08-04 as #13382.** Second 4f Medium port. New `openEncounterViaUi()` helper on UiSeedingTrait (Past Encounters dropdown → first Office Visit entry → enc → forms iframe chain → navbar wait). Test seeds patient + encounter, navigates back via `openPatientViaUi`, re-opens via `openEncounterViaUi` — distinct user journey from Kk (which lands in the navbar as a side effect of creation). Dual-tagged fresh-install + post-upgrade. |
  | SvcCodeFinancialReportTest            | **SKIP** | **PERMANENTLY SKIPPED per user 2026-08-04.** Source-side test seeds its fixtures via direct SQL through `QueryUtils` (inserts into `facility`, `form_encounter`, `billing`, `ar_activity`, `codes` tables + `DELETE`-based tearDown). That's the exact anti-pattern the acceptance suite explicitly avoids ("no SQL, no direct DB writes, no fixture files" — UiSeedingTrait docblock). Porting would require either (a) building UI-driven seeding for custom codes + Fee Sheet billing entry + payment posting — ~4 new complex UI helpers for one test, (b) breaking black-box discipline for this test alone (sets a precedent that undermines the suite's uniformity), or (c) porting only page-loads-empty scenarios that skip the actual signal (does the report compute totals correctly). Financial-report SQL correctness stays covered source-side; acceptance is about "does the shipped artifact boot + serve the primary user journeys," which this test isn't. |
  | HhMainMenuLinksTest                   | Large  | 58× menu links; feature-module dependent; skips on old Node. |
  | IiPatientContextMainMenuLinksTest     | Large  | 40+ patient-scoped menu variants; needs seeded patient+encounter. |
  | JjEncounterContextMainMenuLinksTest   | Large  | 15+ encounter-scoped variants; complex dependencies. |
  | EmailSendTest                         | N/A    | DEV-ONLY. Requires Mailpit; shipped artifacts don't have email. |
  | EmailTestServiceTest                  | N/A    | DEV-ONLY. Mailpit + email_queue table dependencies. |
  | FaxSmsEmailTest                       | N/A    | DEV-ONLY. Module not in CI DB; auto-skipped. |
  | NotificationCronEmailTest             | N/A    | DEV-ONLY. Requires faxsms + cron pipeline. |

Recommended shape:

  * **Slice 1 (validation, ~1-2 weeks):** port 3 easy tests —
    `AaLoginTest` → `E2eLoginTest`, `FrontPaymentCssContrastTest`
    → `E2eFrontPaymentTest`, `GgUserMenuLinksTest`
    → `E2eUserMenuTest`. No seeding required. Validates the
    porting mechanic + Selenium infrastructure on shipped
    artifacts. ~200 LOC.

  * **Slice 2 (data-seeding, ~3-4 weeks):** add
    `CcCreatePatientTest` (introduces fixture-seeding pattern) +
    `SvcCodeFinancialReportTest` (introduces billing/codes
    seeding). Validates the seeding strategy for shipped-artifact
    context.

  * **Deferred / possibly-never:** the 3 Hh/Ii/Jj menu-links tests
    (113+ menu variants combined; huge maintenance surface).
    Defer until core slices prove ROI. May not be worth porting
    at all — the daily-gate + release-time gate on core flows
    already catches most regressions.

  * **Excluded:** the 4 email/fax/notification DEV-ONLY tests.
    Don't fit shipped-artifact context.

Refactoring approach: **slice-dependent — duplicate for
slice 1, reconsider shared-trait for slice 2.**

For **slice 1 (3 easy tests: Login/UserMenu/FrontPaymentCss)**:
duplicate straight into `tests/Acceptance/E2e*.php`. Total
surface is small, no seeding involved, easy to keep in sync
manually. UI-automation-heavy code (Selenium XPath sequences,
form interactions) tends to couple in awkward ways when you
try to share it up front — better to see 2-3 concrete
duplications before designing a shared abstraction. The two
suites also have slightly different contexts (dev-stack
pre-seeded data vs fresh-install seed) that would need to be
threaded through any shared-flow abstraction.

For **slice 2 (CRUD flows with fixture seeding —
CcCreatePatientTest, SvcCodeFinancialReportTest, etc.)**:
reconsider the shared-trait approach. The seeding logic
(patient creation, encounter creation, billing/codes/ar_activity
fixture setup) IS genuinely reusable across dev-stack + shipped-
artifact context — the values and DOM sequences are the same.
The trait pattern already exists in the dev-checkout E2E suite
(PatientAddTrait, EncounterAddTrait, UserAddTrait) so extension
into shared-across-suites traits is a natural evolution rather
than a new abstraction. Shape: extract flow logic (form-fill
sequence, assertion targets) into
`tests/Acceptance/Support/E2eFlows/*` trait imported by both
concrete tests; thin per-suite wiring around the shared trait.
Higher upfront design cost than pure duplication, but eliminates
drift risk long-term where it matters most.

For slice 3 (Hh/Ii/Jj menu-links tests, 113+ variants): likely
defer indefinitely; if ever ported, the sheer surface area makes
shared-trait mandatory to keep maintenance sane.

`Support/LoginFlow` from 4a already handles the login side; the
slice-2 shared-trait pattern extends that same approach to
patient / encounter / seeding flows.

Runtime concern: 17 tests × 3 acceptance scenarios × 2 archs =
102 test runs, each 30-60s. Not tenable as always-on. Land as
a distinct matrix group (`--group=e2e-acceptance`) that runs
either only on manual dispatch, or as a separate scheduled tick
(distinct from the 09:00 UTC acceptance schedule). Not gating
merge to start.

Both 4d and 4e are independent of Phase 7d, Phase 3.6, and each
other. Ordering: after 7d + 3.6 finish, pick whichever slice-1
lands cleanest given whoever picks it up.

Original Phase 4 scope preserved below for reference:

- Add ApiSmokeTest, FhirSmokeTest, E2eCriticalPathTest (last one
  needs Panther+Selenium — first introduction of a headless
  browser to acceptance)
- Add **wizard-UI coverage for the tarball path**:
  `InstallWizardUiTest` (browser-driven walkthrough of setup.php's
  multi-step state machine — form validation, step transitions,
  UI-rendered errors) and `UpgradeWizardUiTest` (browser interaction
  with sql_upgrade.php's version-selector form + "Upgrade Database"
  button). Phase 3's CLI install/upgrade paths bypass these
  completely — `Installer::quick_install()` and `sql_upgrade.php`'s
  CLI mode share the underlying logic but skip the wizard's own
  state machine, form fields, and HTML rendering. Real tarball
  users see the wizards; a setup.php state-machine bug would slip
  past CLI-only tests. Docker artifact skips these tests entirely
  (auto-install via env vars means Docker users never see the
  wizard). Uses the same Panther+Selenium plumbing that
  `E2eCriticalPathTest` introduces; runs only against the tarball
  acceptance workflow.
- Extract common seeders + assertions into `tests/Acceptance/Support/`
  (`DataSeed/PatientSeeder.php`, `Assertions/InstallWizardAssertions.php`,
  etc.)
- Consider making acceptance runs required checks on release-prep PRs

Exit criterion: ~30 min total acceptance runtime per artifact,
meaningful coverage of API + FHIR + one critical E2E flow, plus
tarball-path wizard walkthroughs (install + upgrade) covered.
Roughly 2 weeks.

### Phase 5 — Retire the tests-in-image dependency

- Land the Dockerfile `git clone` → `git clone + git archive HEAD
  | tar -x` change (equivalent to parked openemr/openemr#12790).
  The clone stays — source is still fetched from GitHub via
  `${OPENEMR_VERSION}` — but the cloned content is piped through
  `git archive HEAD` to honor `.gitattributes` `export-ignore`
  rules. Result: image content matches tarball content (strips
  `tests/`, `.github/`, `ci/`, `docker/`, `tools/`, most of
  `Documentation/EHI_Export/`, etc.).
- Restructure or retire `docker-test-core.yml`'s `kcov` profile
  (currently runs PHPUnit inside the built container against
  `tests/`; can't survive the strip. **Required co-change** — it
  will fail otherwise, not optional).
- Docker image content aligns with tarball content (SBOM /
  provenance parity across the two artifacts).

**What Phase 5 does NOT do:** it does not enable full-fidelity
PR-image validation. `${OPENEMR_VERSION}` still drives the source
fetch from GitHub — Phase 2.5's PR-built images still contain
master source, not PR source. That's Phase 6's scope; the two
changes are orthogonal.

**Rel-branch invocation model (important context for both Phase 5
and Phase 6):** the `ARG OPENEMR_VERSION=...` default in the
Dockerfile is fallback-only. The correct invocation path always
goes through `docker-build-release.yml` (orchestrated from master's
`docker-release-orchestrator.yml`), which reads
`.github/release-targets.yml` as source of truth and passes
`--build-arg OPENEMR_VERSION=<value>` — overriding the Dockerfile
default per-branch. `DockerfileOpenemrVersionMutator` (which sets
the ARG default to `rel-820` on branch cut) is cosmetic
self-documentation for anyone running `docker build docker/release/`
directly — an "if you invoke this the wrong way" safety net, not
the production path. **Direct `docker build` on a rel branch is
out-of-scope** — if it produces a hybrid or breaks, that's
acceptable because nobody does that in production.

**Risks (validated + acceptable):**
1. Runtime dependencies on now-stripped export-ignored files — Phase
   2's acceptance suite + `docker-test-release` catch install + login
   regressions; hidden runtime paths (admin panel referencing dev
   files) can't be proven absent without exercising them.
2. kcov profile break — MUST be handled in same PR (not "optional
   restructure or retire" — it will fail otherwise).
3. Local `docker build` behavior shift — users who ran the release
   Dockerfile locally previously got `tests/` inside the image;
   per the "rel-branch invocation model" note above, direct
   `docker build` isn't the correct invocation anyway, so any
   behavior shift there is bounded to already-out-of-scope usage.

Exit criterion: `docker-test-release.yml` passes with the stripped
image; kcov either moved to source-side (dev stack) or dropped.
Roughly 1 week.

### Phase 6 — Full-fidelity PR-image testing (source-mode indirection)

Closes the gap Phase 2.5 leaves open: today Phase 2.5's
`build_locally=true` runs produce a PR-Dockerfile-plus-master-source
hybrid, because the Dockerfile hardcodes `git clone
https://github.com/openemr/openemr.git --branch "${OPENEMR_VERSION}"`.
Phase 6 lets the docker build consume the PR's local checkout as
source (matching what Phase 3.5 gives the tarball path via
`PackageAssembler` — the tarball toolchain already builds from local
source with no source-vs-checkout decoupling to unwind).

**Non-trivial constraint** — the release pipeline currently
depends on the "checkout ref vs source ref are different"
decoupling: `docker-release-orchestrator.yml` dispatches
`docker-build-release.yml --ref rel-820 -f openemr_version_ref=v8_2_0`,
so the workflow file + Dockerfile come from `rel-820` but the
source that goes INTO the image comes from `v8_2_0` (via
`git clone --branch v8_2_0`). Any Phase 6 design MUST preserve
that separation — a naive "just use local checkout" breaks
release builds because the runner is checked out on rel-820,
which drifts past v8_2_0 as more commits land.

**Feasibility survey** (2026-07-24, before design phase — pick one at
implementation time, no fundamental blockers on any of these):

- **Option A — Source-mode build-arg with conditional stage selection**
  ```dockerfile
  ARG OPENEMR_SOURCE_MODE=github   # or "context"
  ARG OPENEMR_VERSION=master
  FROM base AS openemr-source-github
  RUN git clone https://github.com/openemr/openemr.git --branch "${OPENEMR_VERSION}" ...
  FROM base AS openemr-source-context
  COPY openemr-src/ /openemr/
  FROM openemr-source-${OPENEMR_SOURCE_MODE} AS openemr-source
  ```
  Well-known BuildKit pattern (ARG-templated stage FROM). Release
  builds keep `github` default and existing workflow, so no ship-
  pipeline coordination change. Phase 2.5's build-image job passes
  `context` + prepares an `openemr-src/` from the PR checkout
  (probably via `git archive HEAD | tar -x` locally).

- **Option B — Named build contexts (BuildKit `--build-context`)**
  ```bash
  docker build \
    --build-context openemr-source=/tmp/prepared-src \
    --file docker/release/Dockerfile \
    docker/release
  ```
  Dockerfile references the named context via
  `COPY --from=openemr-source ...`. Workflow prepares
  `/tmp/prepared-src` from either `git clone` (release path) or PR
  checkout (Phase 2.5 build-image path). Semantically cleaner than
  Option A (separation between docker/release/ build context vs
  openemr source context) but requires the ship pipeline to prepare
  the source context too — coordination change with existing
  release-build workflow.

- **Option C — Workflow pushes PR ref to openemr/openemr as temp branch**
  ```bash
  git push origin HEAD:refs/heads/tmp-pr-<PR#>-<sha>
  # docker build with OPENEMR_VERSION=tmp-pr-<PR#>-<sha>
  git push origin :refs/heads/tmp-pr-<PR#>-<sha>   # cleanup
  ```
  Zero Dockerfile change; zero release-pipeline coordination change.
  BUT works only for internal branches on `openemr/openemr` — fork
  PRs (most contributor PRs) don't have push access to the parent
  repo. Would need to scope auto-fire to internal-only.

**Lean toward Option A** at design time: single Dockerfile change,
release pipeline unchanged, both modes coexist. Option B is arguably
cleaner semantically but requires more workflow coordination. Option
C is scope-limited to internal PRs and creates temp-branch cleanup
choreography. Coin flip between A and B; final pick when Phase 6
starts.

**Not-blockers** for any option:
- Docker Buildx / BuildKit already used in `docker-build-release.yml`
  (Options A + B rely on it)
- No external service or new permission model (Options A + B)
- Existing release-build flow stays intact — new modes are opt-in
  per invocation (Options A + B)

**Also needs**: `DockerfileOpenemrVersionMutator` (which bumps
`ARG OPENEMR_VERSION=master` → `ARG OPENEMR_VERSION=rel-820` on
branch cut) keeps working as-is. Its purpose was already cosmetic
self-documentation — the correct invocation path
(`docker-build-release.yml` → `--build-arg OPENEMR_VERSION=<value
from release-targets.yml>`) always overrides the Dockerfile
default. Post-Phase-6 nothing changes about that — the mutator
stays because the safety-net cosmetic value is real, and any new
source-mode ARG introduced by Phase 6 (Option A/B) follows the
same "release orchestrator always overrides" pattern.

**Rel-branch invocation model reminder** (same nuance called out
in Phase 5): whatever Phase 6 picks, direct `docker build
docker/release/` on a rel branch is out-of-scope. The correct
invocation is always through `docker-build-release.yml` which
reads `release-targets.yml` as source of truth and passes
overrides via `--build-arg`. Phase 6's new source-mode ARG (if
Options A/B) follows the same pattern: default in Dockerfile is
fallback for direct invocations, real value comes from
`--build-arg` in `docker-build-release.yml` (or Phase 2.5's
build-image job for the PR-image path).

Exit criterion: a source-only PR (no Dockerfile change) can be
manually workflow-dispatched with `build_locally: true` and the
resulting image contains the PR's source (spot-check via
`docker exec ... cat interface/<file-touched-in-PR>`). Roughly 1
week including design + integration.

### Phase 7 — Release-lifecycle trigger integration

Phases 1-6 build the acceptance harness + wire the "always-on"
triggers (workflow_dispatch + daily schedule + push/PR on the
acceptance surface + PR-Dockerfile auto-fire). That trigger set
already catches most issues during ongoing development — no
release-specific integration is *required* for the harness to be
useful.

Phase 7 layers on the two release-lifecycle-specific trigger
points that give the end goal ("validate the artifact that will
actually ship, right when it's about to ship or has just shipped"):

**7a. Release-prep PR triggers.** The release-prep conductor
opens 2 PRs per release event (release-prep PR on `rel-*` +
finalize PR on master). Wire acceptance to fire on those PRs'
push events, validating the artifacts the release will ship:

- Docker path: **SHIPPED as Phase 7a-docker 2026-07-27
  (openemr/openemr#13210).** `release-prep.yml` dispatches
  acceptance-docker.yml alongside acceptance-package.yml after
  every peter-evans force-push. No changes to acceptance-docker
  itself were needed — `DockerfileOpenemrVersionMutator` already
  bakes `OPENEMR_VERSION=<rel-branch>` into the rel-branch's
  Dockerfile at branch-cut time, so `docker build docker/release`
  on `release-prep/rel-830` naturally clones rel-830 source during
  the image build; the existing Phase 2.5 `build-image` job does
  the rest. Byte-identical sync tweaked in the same landing (add
  rel-820 to `build-release.yml`'s exclude-branches — Phase 7c's
  reusable-workflow reference to acceptance-package.yml was making
  actionlint fail on the rel-820 sync PR since acceptance-package.yml
  is already excluded from rel-820).
- Tarball path: **SHIPPED as Phase 3.5 (#13204).** `detect-mode`
  detects `release-prep/*` head branches and force-enables
  `build_locally=true` regardless of what files the PR touches;
  PackageAssembler builds the tarball from the PR checkout; all
  four matrix scenarios (`fresh-install`, `wizard-install`,
  `upgrade`, `wizard-upgrade`) exercise the PR-built tarball.
  Actual trigger delivery (how the workflow starts firing on
  release-prep PRs) is a small follow-up — the intended pattern
  is `release-prep.yml` explicitly dispatching acceptance-package
  via `gh workflow run` after opening/updating the PR (path-based
  triggers were rejected as over-firing on non-release version
  bumps). Depends on Phase 3.5 being on the target rel-branch, so
  effective from rel-830+.

The Phase 4 sub-item "Consider making acceptance runs required
checks on release-prep PRs" folds into this: once 7a fires on
release-prep PRs and proves stable, promoting to required check
is a small branch-protection change.

**7c. Release-time pre-publish gate — SHIPPED 2026-07-27 (openemr/openemr#13207).**
Closes the last gap between "PR-built tarball validated" (Phase 3.5)
and "actual shipped tarball validated": now the literal same bytes
that reach the GitHub releases page have already passed the full
acceptance matrix.

Shipped shape:

- `acceptance-package.yml` gained a `workflow_call` trigger with
  `caller_tarball_artifact` + `to_version` inputs. When called with
  those set, `detect-mode` fast-paths to `build_locally=true`,
  `build-tarball` self-skips (caller already built it), and the
  new download step grabs the caller-supplied artifact into the
  same `/tmp/pr-built-tarball/openemr-<version>.tar.gz` path
  Phase 3.5 uses — downstream `boot-package.sh --local-tarball`
  plumbing unchanged.
- `build-release.yml` split from one job into three:
  `build-package` (existing build steps + upload-artifact
  `openemr-release-candidate-<version>`) → `acceptance-gate`
  (calls acceptance-package via reusable workflow) → `publish`
  (needs both, re-generates app token, re-checks out, re-downloads
  artifact, runs the existing tag + `gh release create` + upload
  + summary steps). Publish only fires if acceptance-gate passes.
- Bonus follow-up in the same PR: wired `release-prep.yml` to
  `gh workflow run acceptance-package.yml --ref release-prep/<branch>
  -f build_locally=true` after peter-evans force-pushes the
  release-prep PR, closing the Phase 3.5 "every release-prep push
  runs acceptance" coverage gap the plan doc originally called out
  as pending. Fire-and-forget from release-prep.yml; RELEASE_APP
  token because GITHUB_TOKEN can't trigger workflow runs (platform
  loop-guard); fallback swallow on dispatch failure so pre-Phase-3.5
  rel-branches (rel-820) keep working without the gate.

Failure recovery is 4 scenarios (documented in PR body +
RELEASE_PROCESS.md runbook step 10): transient infra flake
(re-run failed jobs), bad package build (re-run all jobs — fresh
PackageAssembler pass), acceptance-harness false positive (fix on
master, backport to rel-branch, re-run), real codebase regression
that snuck past Phase 3.5 (rare — version bump recovery, poisoned
tag stays cosmetic). Publication is NOT strictly atomic
(`gh release create` + `gh release upload` are separate steps),
but re-run is idempotent via `gh release view` skip +
`gh release upload --clobber`.

Docker equivalent (7c-docker) is analogous once acceptance-docker
grows a `workflow_call` trigger + docker-build-release.yml gains
build → validate → publish sequencing. Not yet shipped; can ship
independently.

**7b. Release-event triggers.** Fire acceptance against the
just-shipped artifact within minutes of publish, catching
"artifact published broken, users hit it before daily schedule
fires":

- Add `repository_dispatch` listener for `openemr-tag` events
  to both `acceptance-docker.yml` and `acceptance-package.yml`
- Dispatch payload includes the version number → workflows
  pull that Docker Hub tag / GitHub release tarball and run
  the full matrix against it
- Difference from daily schedule: latency (minutes vs 24h),
  targeting (specific just-shipped version vs floating `latest`),
  guaranteed fire (schedule can miss if runner capacity is
  short)

**Why defer to last:** the harness's existing trigger set
(daily schedule + push/PR on acceptance surface + PR-Dockerfile
auto-fire) already catches most issues during ongoing
development. Release-lifecycle integration is a *latency* +
*targeting* improvement, not a coverage improvement — worth
adding once the underlying harness is proven stable, not before.
Also naturally builds on Phase 6 (full-fidelity PR-image
validation) if that changes how the release-prep PR image is
constructed.

Exit criterion: release-prep PRs auto-fire the acceptance
matrix against the PR's artifacts (**7a tarball: DONE via
Phase 3.5 + release-prep.yml dispatch wire-up (openemr/openemr#13207),
effective rel-830+**; **7a docker: DONE via
openemr/openemr#13210**); the release pipeline gates
GitHub-releases publish on acceptance against the literal
shipped tarball (**7c tarball: DONE via openemr/openemr#13207**;
**7c docker-latest-gate: SCOPED + DEFERRED until 8.3.0 ships**
— narrow scope covers only the daily orchestrator's `latest`-
tagged build; in-place docker-build-release.yml refactor becomes
trivial once acceptance-docker.yml is present on the `latest`-
owning rel-branch, which happens when 8.3.0 ships and rotates
latest ownership from rel-820 to rel-830); publish verifies
byte-integrity of the uploaded release assets (**7b: DONE via
openemr/openemr#13217** as a scoped-down sha256 re-verify inside
build-release.yml's publish job — the original repository_dispatch
full-matrix re-run was rejected as redundant given 7c-tarball
validated the same exact bytes). **7c-docker-latest-gate DONE
2026-07-28 via #13239 + follow-ups (#13244 + #13245 + #13246
byte-identical exclusion + rel-800/rel-704 reverts, #13254 arm64
composite action, #13258 orchestrator gate-flag plumbing);
validated end-to-end 2026-07-29 with both amd64 + arm64
matrix cells green on rel-820's daily `latest` build.** The
"deferred until 8.3.0 ships" position was reversed by
backporting the acceptance surface to rel-820 first; see the
2026-07-28 update-log entry for the sequence.
**Phase 7d-2 (PR-time build_locally arm64 coverage) DONE
2026-07-29 via #13259** — arm64 now covered on both the daily
latest-gate (Phase 7d-1) AND the release-prep PR path
(Phase 7d-2). **All of Phase 7 is complete.**


**Total remaining calendar (from 2026-07-25 baseline, Phases 1+2+2.5
+3 all in flight or landed):** ~5-6 weeks focused work through Phase
7. No hard deadline. rel-830 (~2 weeks out) gets the Phase 1+2+2.5+3
baseline; Phases 4+5+6+7 land into a rel-830-shipped codebase.

### Phase 7d — arm64 acceptance restoration + PR-time coverage

**Sequencing note.** Phase 7c-docker-latest-gate (#13239) wired
`test_arm64: true` into acceptance-docker.yml's workflow_call from
docker-build-release.yml — but the first end-to-end run on 2026-07-28
revealed that `nanasess/setup-chromedriver@v2` doesn't work on
ubuntu-24.04-arm runners: Google Chrome ships amd64-only debs from
Google's apt repo, so the action's fallback `apt install
google-chrome-stable` fails with unmet dependencies. All 3 arm64
scenarios failed at the ChromeDriver setup step in 29s each. Full
arm64 restoration is this phase.

Phase 7d has two slices:

**7d-1 — multi-arch ChromeDriver setup composite action —
SHIPPED + VALIDATED 2026-07-29 (#13254).** Chose Option B
(composite action) from the original scoping, but pivoted the
arm64 install source when research confirmed Chrome for Testing
publishes NO `linux-arm64` platform variant (only linux64,
mac-arm64, mac-x64, win32, win64 as of 2026-07;
GoogleChromeLabs/chrome-for-testing#1 open, crbug/374811603
unshipped). Ubuntu noble's own chromium is a snap-transitional
stub not usable in CI; browser-actions/setup-chrome@v2.1.2
still marks all Linux ARM64 columns unsupported. Landed on
**xtradeb PPA** (ppa:xtradeb/apps) which ships native arm64
debs for chromium + chromium-driver as a byte-matched-pair
version, eliminating the drift risk that killed
browser-actions historically.

Shipped shape:

  * New composite action `.github/actions/setup-chromedriver-
    multiarch/action.yml` — amd64 delegates unchanged to
    nanasess/setup-chromedriver@v2; arm64 does
    `add-apt-repository -y ppa:xtradeb/apps` +
    `apt-get install -y chromium chromium-driver` +
    symlink chromium to `/usr/local/bin/google-chrome` and
    `/chrome` so Panther autodetect works unchanged.
  * acceptance-docker.yml swaps its `uses: nanasess/setup-
    chromedriver@v2` line for `uses: ./.github/actions/setup-
    chromedriver-multiarch`.
  * byte-identical.yml carries the composite action path with
    the same `exclude-branches: [rel-800, rel-704]` as
    acceptance-docker.yml — callee only needs to exist where
    the caller does.

Validated 2026-07-29 via manual orchestrator dispatch
(`gh workflow run docker-release-orchestrator.yml --repo
openemr/openemr --ref master`): rel-820's gated flow ran all
6 acceptance matrix cells (3 scenarios × amd64/arm64) green,
publish (imagetools alias) succeeded, cleanup-candidate
deleted the temporary tag. `docker manifest inspect
openemr/openemr:latest` confirmed multi-arch manifest with
amd64 digest c8d3b10f... and arm64 digest 1c5423c1... —
identical to what acceptance tested against. First true
end-to-end validation of Phase 7c-docker + arm64 coverage.

Also landed in flight: **#13258 — orchestrator gate-flag
plumbing fix.** The reverted (pre-Phase-7c) docker-build-
release.yml on rel-800/rel-704 doesn't declare
`gate_with_acceptance`, so the orchestrator's unconditional
`-f gate_with_acceptance=false` failed dispatch on those
branches with HTTP 422 "Unexpected inputs provided". Fix:
only pass the flag when GATE=true (rows with `latest` in
their docker_tags). Elides the input for non-gated rows,
letting the receiving workflow's default handle both shapes.

acceptance-package.yml still uses nanasess directly (amd64-
only). It doesn't yet have a test_arm64 input, so composite
adoption there is a follow-up when acceptance-package grows
arm64 coverage (probably alongside Phase 3.6 or a dedicated
small PR).

**7d-2 — PR-time build_locally arm64 coverage — SHIPPED
2026-07-29 (#13259).** Extended the test_arm64 matrix pattern
into acceptance-docker.yml's build_locally path (Phase 2.5's
`build-image` job) so PR-time validation catches arm64 Dockerfile
regressions at release-prep review time rather than post-merge
on the daily gate.

Shipped shape:

  * `build-image` job matrixed on
    `runs_on: [ubuntu-24.04, ubuntu-24.04-arm]` gated on the
    existing `test_arm64` input. Each arch builds natively
    (no QEMU emulation) on its own runner and uploads its
    arch-specific artifact under a distinct name
    (`openemr-pr-built-image-amd64` / `openemr-pr-built-image-arm64`).
  * The `acceptance` job's "Download PR-built image" step
    resolves runner arch via `uname -m` and picks the matching
    artifact — amd64 runners load the amd64 tarball, arm64
    runners load the arm64 tarball.
  * `release-prep.yml`'s `gh workflow run acceptance-docker.yml`
    dispatch grew `-f test_arm64=true` alongside the existing
    `-f build_locally=true`. Swallow-on-failure guard preserved
    so pre-Phase-7d-2 rel-branches (still on the amd64-only
    build-image shape) degrade to amd64-only rather than
    failing hard.
  * Every other trigger (PR touching docker/release/**,
    schedule, workflow_dispatch without test_arm64) stays
    amd64-only — default behavior unchanged.

Trade-offs (as-shipped):

  * Runtime: PR-time gets a second parallel build-image job;
    wall clock similar (parallel), runner-minutes ~2x for the
    build phase. Both arm64 + amd64 GHA runners are free for
    public repos, so no billing.
  * Coverage: every release-prep PR catches arm64 regressions
    before the merge lands on a rel-branch. Also picks up
    docker/release/** PRs that opt in via workflow_dispatch.
  * Complexity: acceptance-docker.yml gained a matrix dimension
    on build-image + a per-arch artifact name; release-prep.yml
    gained one flag on its dispatch. No new registry, no OCI
    extraction, no cleanup logic. Composite action from Phase
    7d-1 (#13254) handles the arm64 chromedriver install on
    both PR-time and gated paths.

Rejected alternative (revisit if it ever matters): single multi-
arch build+push to a temporary Docker Hub or GHCR tag (mirroring
7c-docker's candidate-tag pattern) with per-arch pull on each
acceptance runner. Adds registry churn per PR run + cleanup +
potential visibility of half-baked images. The two-build-image-
jobs approach keeps everything workflow-artifact-scoped, matching
Phase 2.5's existing transport.

**Phase 7d is complete** — 7d-1 + 7d-2 both shipped. arm64 now
covered end-to-end: daily latest-gate validates the shipped
`latest` tag on both platforms; release-prep PRs validate the
PR-built image on both platforms. Only next-up validation:
observing the first release-prep cycle that fires with test_arm64
propagated (rel-820+ once sync #13260 or the equivalent for
#13259 lands).

### Phase 8 — Test reliability hardening *(partially delivered ad-hoc via later phase-flake-fix work; formal Phase 8 milestone not taken up)*

By Phases 7 + 7d, acceptance gates on the release-prep PR AND on
the daily `latest` push AND on the release-time publish. Every
gate that fires increases the surface area where a flaky test can
delay a release. This phase reduces the flake rate + adds explicit
handling for the flakes that do slip through.

Scope (unordered — implementation order tbd):

  * **Flake rate baseline.** Instrument acceptance jobs to emit a
    per-test-per-run status to a small persistent store (Codecov's
    test-analytics feature, a GitHub Issues-based bucket, or
    similar). Establish a flake baseline before making changes;
    then track whether interventions actually reduce it.
  * **Root-cause common flake modes.** Panther/Selenium race
    conditions (element-not-yet-visible vs polling), Docker Hub
    rate-limit 429s during pulls, GHA runner cold-start variability
    on the compose stack boot, Chrome/ChromeDriver version drift.
    Each has a targeted fix; enumerate + prioritize.
  * **Automatic retry policy.** GHA-native retry (rerun-failed-jobs
    on transient signals) vs test-level retry (PHPUnit @retryFor
    annotations or a small wrapping harness). Trade-off:
    hiding a real regression behind auto-retry is a worse outcome
    than a caught flake; retries should target known-transient
    failure modes only, with visible retry-count in the check
    output.
  * **Quarantine mechanism.** A test file/method can be marked
    `@group flaky-quarantined` — runs, but its result doesn't
    fail the gate. Quarantined tests appear in a distinct check
    summary with owner + rationale. Time-boxed (auto-un-quarantine
    after N days) so quarantine can't become a graveyard.
  * **Better error surfacing.** When a gate fails, the operator
    should see immediately whether it was (a) code regression,
    (b) known-flake retry-exhausted, or (c) infrastructure issue
    (Docker Hub, Chrome install, etc.). Structured failure
    classification in the workflow summary.

Rejected alternative: "just accept some flakiness and let operators
re-run." That's the current baseline. Phase 8 exists because
release-time flakes have higher pain (visible pause in ship, ops
scramble, sometimes users watching Docker Hub for `latest` bump)
than dev-time flakes. Worth investing to reduce.

Exit criterion: acceptance gate flake rate below X% (baseline TBD;
target ~1% or lower per gate invocation) sustained across a
2-week window. Rough timing: ~2-3 weeks of iterative work.

### Phase 9 — Skip-build re-run for release recovery *(SHIPPED 2026-07-29 as #13272)*

Even with Phase 8's flake reduction, release-time flakes will
happen occasionally. Today's recovery path is "Re-run failed jobs"
which re-runs everything from build-package onward — for the
tarball path that's ~15 min of PackageAssembler + composer + node
work before acceptance even starts; for the docker path it's
~30-60 min of multi-arch buildx before acceptance starts. Long
enough that operators feel the pain and start second-guessing
whether to just push-through.

Phase 9 adds a dedicated re-run entry point: **acceptance-only
against already-built artifacts**. When a release-time acceptance
gate flakes on a known-good build, the operator dispatches this
entry point instead of "Re-run failed jobs", skips the entire
build phase, and gets a fresh acceptance verdict in ~5-10 minutes.

Concrete shape:

  * **Tarball path.** `build-release.yml`'s `build-package` job
    already uploads the release-candidate tarball as a workflow-
    run artifact with 7-day retention. Add a new
    `acceptance-only` workflow_dispatch entry point that takes a
    workflow run ID as input, downloads that run's
    `openemr-release-candidate-<version>` artifact, and calls
    acceptance-package.yml against it via the existing
    `caller_tarball_artifact` workflow_call surface. If
    acceptance passes, the operator manually re-fires the publish
    job of the original workflow run (or a follow-up PR wires
    "acceptance-only → publish" for full automation).
  * **Docker path.** Requires a small tweak to Phase 7c-docker's
    cleanup-candidate job: today it always runs on the gated
    path regardless of acceptance outcome (`if: always() &&
    inputs.gate_with_acceptance`). Change to only cleanup on
    (acceptance success AND publish success) OR explicit operator
    override. Result: on acceptance flake, the candidate tag
    stays on Docker Hub; the acceptance-only dispatch re-fires
    acceptance-docker.yml with `to_tag=<preserved-candidate-suffix>`
    against the still-existing candidate. Publish then proceeds
    normally against the same digest.
  * **Guardrail.** The acceptance-only entry point should refuse
    to run against an artifact older than N hours (7 days is too
    long — real regression risk if the operator has been sitting
    on a broken artifact). Suggested: 24-48h ceiling.

Trade-offs:

  * Complexity: two changes (build-release.yml + docker-build-
    release.yml) + one new small workflow OR extension of
    existing workflow_call surface. Well-bounded.
  * Docker-Hub tag lifetime: candidate tags on the gated docker
    path stay longer (until publish succeeds), which briefly
    increases visibility of temporary tags. Naming (release-
    candidate-<run-id>-<attempt>) already signals internal-use.
  * Operator UX: a dedicated re-run entry point vs remembering
    to click "Re-run failed jobs" on the right workflow run.
    Small quality-of-life win, real when release-time pressure
    is on.

Exit criterion: an operator observing a release-time acceptance
flake can dispatch acceptance-only and get pass/fail in under 10
minutes without re-doing the build phase; on green, publish
proceeds normally. ~3-4 days of implementation.

### Phase 10 — Release-mechanism infrastructure unification + test coverage *(SHIPPED 2026-08-01; slices 10a/b/c/d/f + 10e-1..10e-6 all landed; 10g optional-deferred)*

By Phases 1-9, the release-mechanism has 20+ shipped slices touching
~10 workflows and 4 shell scripts. Concrete duplication + test-
coverage gaps have accumulated. Consolidate before they become
drift-bug sources.

**Sliced by concern; slices are independent — pick in any order:**

* **10a — Extract OCI-label verify script — SHIPPED 2026-07-29 as
  #13274.** Was 3 near-identical ~35-line blocks in
  `docker-build-release.yml` (gated candidate verify, non-gated
  pushed verify, publish job verify); now single script at
  `.github/scripts/verify-oci-labels.sh` + 3 thin ~4-line callers.
  Env contract: REF_TAG + EXPECTED_REVISION + EXPECTED_VERSION +
  EXPECTED_BUILD_DATE. New BATS suite at
  `tests/bats/ci-scripts/verify-oci-labels/` (16 tests via
  docker-mock.sh, wired into test-byte-identical-scripts.yml).
  Script added to `.github/byte-identical.yml` with same
  `exclude-branches: [rel-800, rel-704]` as docker-build-release.yml
  itself. Two agent judgment calls landed as-is: (a) verbose
  Dockerfile-context hints now emit on all 3 sites (previously
  only non-gated) — net-positive diagnostic info, not a
  regression; (b) publish job gained an actions/checkout since
  the extracted script needs a workspace on disk;
  persist-credentials: false added in a follow-up commit
  (belt-and-suspenders — publish uses its own app-token for gh
  commands, doesn't need persisted GITHUB_TOKEN in .git/config).

* **10b — Extract app-token composite action — SHIPPED 2026-07-30 as
  #13287.** Wound up much larger than the plan-doc estimate: whole-
  tree sweep found **14 callsites across 13 workflows** (not the ~3
  originally scoped). Composite lives at
  `.github/actions/generate-app-token/action.yml` (renamed from
  the original `generate-release-app-token` after enumeration
  showed non-release apps also using it). Three apps ride the same
  composite: RELEASE_APP (11 callers — build/publish/prep/patch/
  amendment/branch-cut/ship/sync-byte-identical/notify/permissions-
  check/reusable-publish), AUTO_MERGE_APP (dependabot-auto-merge),
  RESERVED_WORD_BOT (refresh-reserved-word-supplement). Composite
  is app-agnostic (client-id + private-key inputs forwarded to
  `actions/create-github-app-token@v3`). Two design points landed
  as-is: (a) 8 workflows minted the token BEFORE any checkout, so
  those got a lean `sparse-checkout: .github/actions/generate-app-
  token` prepended (persist-credentials: false); subsequent full
  checkouts were assumed to overwrite transparently — that assumption
  turned out to be wrong (see the follow-up fixes below). (b)
  `.github/actions/generate-app-token/**` added to byte-identical.yml
  with same rel-800/rel-704 exclusion as the 7 caller workflows
  already in that block.

  **Follow-up (SHIPPED 2026-08-12/13, discovered during rel-830
  cut).** The "subsequent full checkouts overwrite transparently"
  assumption was invalid: `git sparse-checkout disable` clears the
  config but files never written on the first sparse pass do not
  reappear when the second full checkout is byte-identical at those
  paths. Series of fixes:
    * `#13480` — expanded sparse to include `setup-php-composer` for
      branch-cut/patch-prep; added `GH_TOKEN` to release-prep mutator
      steps (`gh api` inside `ChangelogMutator` needs it).
    * `#13509` — same sparse expansion applied to the remaining 5
      workflows in the composite-consumer set (notify-release-
      targets-changed, build-patch, build-release, ship-release,
      reusable-publish-release).
    * `#13517` — dropped the sparse-first pattern entirely in those
      5 workflows in favor of full-checkout-first (the pattern
      release-prep.yml has always used and never had this bug).
      The sparse pattern still couldn't materialize `composer.json`
      for setup-php-composer's install step; full-checkout-first
      sidesteps the leak fully. `create-release-tag.sh` gained an
      APP_TOKEN inline-auth path via `git -c http.extraheader=...`
      so the git-push credential is never persisted to `.git/config`
      during intermediate build steps. Dependabot's github-actions ecosystem scans composite
  action.yml files (per config `directory: /`), so future
  create-github-app-token version bumps get a single PR against
  the composite instead of 14 workflow PRs.

* **10c — Extract release publish flow + automate Phase 9 recovery —
  SHIPPED 2026-07-30 as #13279.** Both prongs landed:
    * Tarball: `build-release.yml`'s inline publish job replaced with
      `uses: ./.github/workflows/reusable-publish-release.yml`;
      `acceptance-only.yml` gained a `publish` job calling the same
      reusable — Phase 9 fast re-run now auto-publishes on green,
      no manual step.
    * Docker: `docker-build-release.yml`'s publish + cleanup-
      candidate jobs replaced with a single call to
      `reusable-docker-publish.yml`; new `docker-acceptance-only.yml`
      gives operators the same fast-recovery ergonomics for docker
      (aliases candidate → final tags via imagetools, cleans up
      candidate) with no manual `imagetools create` / candidate
      delete.
    * Also extracted `expand-docker-tags.sh` (env contract:
      INPUT_TAGS + BUILD_DATE) so tag-expansion logic isn't
      duplicated between docker-build-release.yml and
      docker-acceptance-only.yml. New BATS suite at
      `tests/bats/ci-scripts/expand-docker-tags/` (16 tests).
    * Guardrails on docker-acceptance-only.yml: master-ref only;
      source-run must be docker-build-release.yml workflow; source
      run <48h old; supplied `candidate_tag` must match the source
      run's expected `release-candidate-${run_id}-${run_attempt}`
      format (rabbit round-2 major fix — prevents alias-publishing
      an unrelated candidate under final tags). Docker Hub curls
      bounded (`--connect-timeout 10 --max-time 30`).
    * Verify-step in docker-acceptance-only.yml degenerates to
      "labels preserved through alias" smoke check — accepted as
      correct for recovery context (build-time verify in
      docker-build-release.yml already caught Dockerfile-ARG
      regressions before the candidate reached Docker Hub).
      Recovery-context comment documents the trade-off inline.

* **10d — Extract common ZIP-extract helper for boot/upgrade scripts
  — SHIPPED 2026-07-30 as #13286.** Helper at
  `tests/Acceptance/bin/lib/extract-zip.sh` exposes
  `extract_zip_flattening_single_top_level_dir <zip> <dest>
  <mktemp_template> <error_context>`; both callers now delegate.
  Behavior-preserving — same unzip PATH guard, same nullglob
  single-top-level enforcement, same EXIT-trap cleanup ordering,
  same `::error::` message shapes. Callers pass their own template
  prefix + context noun so diagnostics stay flag-accurate
  ("--local-zip" vs "--to-local-zip"). New 16-test BATS suite at
  `tests/bats/ci-scripts/extract-zip/`, wired into
  test-byte-identical-scripts.yml. Rabbit iterated 3 rounds; ended
  up applying: dotglob addition to catch dot-prefixed top-level
  entries (rabbit round-1, edge case for a zip containing
  `.metadata` alongside the wrapper dir); shopt-state
  preservation via `shopt -p ... eval` for sourced-helper hygiene
  plus skip-mv-when-empty for the empty-top-level-dir case (rabbit
  round-2, real edge case); `mv ... || exit 1` for portable
  failure propagation independent of caller set -e (rabbit
  round-3). Rejected: rabbit round-2 EXIT-trap-clobber concern
  (docstring already addressed — local-zip branch has no caller
  trap) and unzip-exit-status concern (`set -euo pipefail` in
  callers already handles). No new byte-identical entry needed —
  helper lives under `tests/Acceptance/**` glob so auto-syncs
  to rel-810/rel-820 alongside callers.

* **10e — BATS coverage audit + fill gaps.** Multi-slice; slice 1
  (portable-mktemp) IN FLIGHT as #13292; audit DONE 2026-07-30;
  slices 2-6 planned as follow-up work.

  * **10e-1 — Portable-mktemp helpers cleanup — SHIPPED 2026-07-30
    as #13292.** Switched the 5 remaining BATS suites' `setup_test_dir`
    from `mktemp -d -t <template>` (GNU-only) to the portable
    `mktemp -d` form already used by extract-zip's helpers. Unblocks
    the `bats/bats:1.13.0` Alpine image for local iteration (CI on
    ubuntu-24.04 was unaffected; issue was local-only). Not
    sufficient for full Alpine runnability — some suites still need
    yq or git that Alpine doesn't ship; called out as separate
    follow-up if local Alpine iteration becomes routine (post-fix
    state: 3 of 6 suites fully clean in Alpine, 3 have deeper dep
    gaps — `sync-byte-identical` needs git, `validate-byte-identical`
    test 16 + `list-manifest-paths` test 10 need yq).

  * **10e-audit — Release-mechanism BATS coverage audit — DONE
    2026-07-30.** Systematic enumeration of everything currently
    running as part of release orchestration + byte-identical
    propagation. Six BATS suites cover 5 scripts + 1 lib
    (sync-byte-identical, validate-byte-identical, verify-oci-labels,
    expand-docker-tags, list-manifest-paths, extract-zip); glob-expand
    lib is indirectly covered via three of those. Untested surface
    (grouped by risk):

    - **High** (publishes artifacts / tags / blesses builds):
      `reusable-publish-release.yml` tag+release + sha256 verify
      (silent regression → wrong tag or skipped verify); `reusable-
      docker-publish.yml` cleanup-candidate JWT+DELETE (has
      `continue-on-error: true` — silent stale-tag accumulation on
      Docker Hub with zero visibility); `acceptance-only.yml` +
      `docker-acceptance-only.yml` source-run validation (48h +
      workflow_path + candidate_tag binding — the last is the
      malicious-alias defense, regression could ship mismatched
      candidate as final); `acceptance-package.yml` detect-mode
      (~140 lines of shell branching — silently changes what gets
      acceptance-tested and therefore what ships).
    - **Medium** (release-adjacent orchestration): `release-
      amendment.yml` changelog-section extract + 125K truncation +
      anchor-slug; `release-prep.yml` parse-version-php + G16
      dev-gate + branch/version cross-check; `patch-prep-
      automation.yml` / `branch-cut-automation.yml` version-parse
      shape (candidate for shared `lib/parse-version-php.sh`);
      `sync-byte-identical.yml` enumerate-rel-branches yq expression.
    - **Low** (already loud on failure): `release-permissions-check.yml`
      (fails loudly, real-API mocking would defeat the point);
      `setup-chromedriver-multiarch` + `generate-app-token` +
      `setup-php-composer` composites (thin wrappers, surface
      immediately); `notify-release-targets-changed.yml` dispatch;
      `ship-release.yml` delegates to PHP `task ship`.

  * **10e-2 — Slice: `acceptance-only-guardrails` — SHIPPED
    2026-07-31 as #13294.** Agent split into two scripts by concern
    (offline metadata parse vs network I/O): `validate-source-run.sh`
    handles the 48h age check + workflow_path check + candidate_tag
    binding, `verify-dockerhub-tag-exists.sh` handles the remote HEAD
    probe. Both scripts under `.github/scripts/`, both master-only
    (callers are master-only recovery workflows). New BATS at
    `tests/bats/ci-scripts/validate-source-run/` (22 tests) +
    `tests/bats/ci-scripts/verify-dockerhub-tag-exists/` (9 tests).
    Rabbit iterated 3 rounds; ended up applying: caller-drift BATS
    case (round 2, defense-in-depth branch was implicit-only);
    caller-side `jq -er` → non-strict + `|| echo` capture-with-default
    (round 3, so malformed RUN_JSON flows to the script's guards
    instead of aborting with raw jq error under set -e). Alpine
    `setup_file()` shim apk-installs jq + coreutils (BusyBox mktemp
    rejects ISO-8601 T…Z; coreutils gets GNU date).

  * **10e-3 — Slice: `docker-publish-cleanup` JWT+DELETE — SHIPPED
    2026-07-31 as #13293.** Extracted cleanup-candidate step into
    `.github/scripts/dockerhub-delete-tag.sh`. New BATS at
    `tests/bats/ci-scripts/dockerhub-delete-tag/` (8 tests, mocked
    curl). Byte-identical caveat surfaced during extraction:
    `reusable-docker-publish.yml` IS byte-identical'd to rel-810/820+
    (not master-only as the audit assumed), so the extracted script
    was added to `.github/byte-identical.yml` with the same
    `[rel-800, rel-704]` exclusion; without that, rel-branch runs
    would `run: .github/scripts/dockerhub-delete-tag.sh` and hit a
    missing file. Rabbit iterated 3 rounds; ended up applying:
    build-body-with-jq (round 1, JSON-escape safety for
    quote/backslash in creds); token via `--rawfile` from mktemp'd
    tempfile (round 3, keeps raw token out of jq's argv); response-
    body echoes prefix each line with 2 spaces + strip CR (round 3,
    prevents GHA workflow-command injection via response containing
    `::error::` or `%0A`). Skipped rabbit's mock-body-assertion
    suggestion (round 3) — muddles mock concerns; jq escaping is
    already trusted. Also surfaced a silent-failure improvement in
    the process: original inline `curl -sf` on login masked 401/403
    as empty JWT → warn + exit 0; extracted script surfaces HTTP
    errors honestly (workflow's `continue-on-error: true` still
    tolerates it operationally).

  * **10e-4 — Slice: `acceptance-package-detect-mode` — SHIPPED
    2026-07-31 as #13306.** Extracted the ~165-line detect-mode
    inline block from `acceptance-package.yml` into
    `.github/scripts/detect-acceptance-mode.sh` (`emit_to_version`
    kept as internal function; every echo/error preserved verbatim).
    18 BATS tests cover all 7 emit paths + validator + override
    precedence. Byte-identical propagation applied (script added to
    byte-identical.yml with same rel-800/rel-704 exclusion as the
    caller workflow). Left `acceptance-docker.yml`'s simpler
    detect-mode variant inline (different caller, different diff
    surface `docker/release/**` vs `tools/release/`, no workflow_call
    gate / no release-prep detection / no emit_to_version — could
    be a separate slice later). Rabbit found one legit bug during
    review that applied to BOTH the extracted script AND the docker
    variant: `git diff --name-only | grep -qE` under set -euo
    pipefail silently masks git-diff failures as grep's exit-1-for-
    no-match (rightmost non-zero wins under pipefail), reporting
    build_locally=false with no ::error:: line when the range is
    unresolvable (force-push + reflog GC scenario). Fix landed
    atomically in the same PR — captured git diff into a variable +
    checked exit before grepping, applied to both the extracted
    script AND acceptance-docker.yml inline. New BATS regression
    test simulates git diff exit 128 + asserts loud failure.

  * **10e-5 — Slice: `reusable-publish-tag-create` — SHIPPED
    2026-08-01 as #13310.** Extracted `reusable-publish-release.yml`'s
    "Create annotated tag and GitHub release" step (43 inline lines)
    into `.github/scripts/create-release-tag.sh` (101 lines) with 10
    BATS tests covering the 4-cell tag-exists × release-exists
    idempotency matrix plus ls-remote unexpected-exit, gh/git push
    failure, missing-env, missing-notes-file, and directory-target
    cases. Byte-identical propagation applied (script added to
    byte-identical.yml with same rel-800/rel-704 exclusion as caller).
    One env-contract refinement landed: script takes explicit
    RELEASE_NOTES_FILE instead of computing `$GITHUB_WORKSPACE/...`
    internally (caller renders full path via `${{ github.workspace }}`);
    same final path resolution, cleaner boundary. Rabbit iterated 2
    rounds: round 1 caught a real bug (git ls-remote stderr was
    discarded on the wildcard-failure branch, so incident triage
    only saw the exit code number — applied); round 2 flagged missing
    RELEASE_NOTES_FILE preflight (missing/unreadable/directory would
    fail AFTER `git push`, leaving release in partial state — applied
    with 2 regression BATS tests). Round 2 also flagged distinguishing
    "release not found" from "release-view lookup failure" — skipped
    with reasoning (gh release create errors cleanly on duplicate-tag,
    so mis-classified transport flakes result in spurious create-
    attempts that fail safely, not double-releases; fix would need
    fragile CLI-string parsing or full gh-api path-swap).

  * **10e-6 — Slice: `release-amendment-changelog-extract` — SHIPPED
    2026-08-01 as #13311.** Extracted `release-amendment.yml`'s awk
    section-extractor + 125K truncation + anchor-slug into two
    scripts by concern: `extract-changelog-section.sh` (pure awk
    extractor) + `build-release-body.sh` (truncation + anchor pointer
    assembly). 19 BATS tests (7 extractor + 12 body-assembler). Both
    scripts stay master-only (release-amendment.yml is a manual-
    dispatch orchestrator, not byte-identical'd). Anchor-slug NOT a
    general GitHub-slug implementation — exploits the specific
    `[X.Y.Z] - YYYY-MM-DD` heading shape (strip dots, join with
    `---`); documented as a fragility surface in the script header
    so future heading-format drift trips fast. Two rabbit rounds:
    round 1 = docs/naming (make_body_of_size docstring; empty-section
    test rename to match its own success assertion — both applied).
    A CI-only failure surfaced on the exact-equality assertion `[[
    "${output}" == "..." ]]` — reproducible only on GHA runners, not
    on local bats/bats:1.13.0 docker OR fresh-from-source bats-core
    v1.13.0 on ubuntu:24.04; some GHA-runner-specific output-capture
    quirk. Switched to substring assertions matching other tests'
    style (captures actual intent without depending on byte-equality)
    and merged.

  Byte-identical propagation for 10e-2 through 10e-6: none of the
  new BATS suites need propagation (tests are master-only). Target
  scripts (`validate-source-run.sh`, `dockerhub-delete-tag.sh`,
  `detect-acceptance-mode.sh`, `create-release-tag.sh`, `extract-
  changelog-section.sh`, `build-release-body.sh`) are consumed only
  from master-only workflows (`acceptance-only.yml`, `docker-
  acceptance-only.yml`, `reusable-*.yml`, `acceptance-package.yml`,
  `release-amendment.yml`) — safe to keep on master. Only exception
  worth noting: `acceptance-package.yml` IS byte-identical'd to
  rel-810/rel-820, so extracting detect-mode into a helper means the
  helper (`detect-acceptance-mode.sh`) also needs the same byte-
  identical entry with same exclusions.

  **Explicitly not-worth-testing** (audit's non-recommendations,
  preserved to prevent future noise-BATS slices): permissions-check
  probes (need real API); orchestration workflows (`release-prep`'s
  peter-evans loop, `docker-release-orchestrator`, `ship-release`'s
  merge sequencing, `branch-cut-automation`'s dual-scope checkout)
  observable only end-to-end; Taskfile-delegating steps (their PHP
  has its own test surface); `sync-byte-identical.yml` PR-body
  heredoc + add-paths compute (string interpolation, visible in
  the PR body immediately); `notify-release-targets-changed.yml`
  dispatch (single call); `release-amendment.yml` restore-original-
  date step (already has a `grep -q` self-verify); `docker-build-
  release.yml` IMAGE_VERSION extraction (parses `version.php` at a
  specific ref, hard to meaningfully mock, fails immediately at
  build time).

* **10f — Doc audit — SHIPPED 2026-07-30 as #13285.** Smaller than
  scoped: Phase 9 (#13272) and Phase 10c (#13279) each shipped their
  own RELEASE_PROCESS.md updates alongside the workflow work, so the
  "manual re-publish" / "hand-run imagetools" language was already
  gone. 10f's load-bearing edits were plan-doc cross-references from
  the runbook (Steps 10 + 12) and one redundant sentence removal.
  Doc is not in `.github/byte-identical.yml` so no rel-branch
  exclusions needed. Known artifact: cross-references to
  `docs/artifact-acceptance-testing-plan.md` will 404 on GitHub's
  web view until this doc's long-lived PR lands; in-repo relative
  links work for anyone with a clone. Accepted per Brady 2026-07-30.

* **10g — Pin candidate image by digest through acceptance +
  publish** *(OPTIONAL — benefit unclear; deferred from #13279 rabbit
  round-3)*. Today's docker publish path re-resolves the candidate
  tag at publish time rather than pinning to the immutable digest
  returned by build. Rabbit flagged this as a data-integrity gap: a
  retagged image with matching OCI labels could pass acceptance-gate
  and then be alias-published (attacker with `openemr/openemr`
  Docker Hub write access; window is ~minutes on normal path, up to
  48h on Phase 9 recovery). Fix: `docker buildx imagetools inspect
  ${CANDIDATE_TAG} --format '{{json .Manifest}}' | jq -r .digest`
  right after build; thread digest through as job output; publish
  and recovery both reference `openemr/openemr@sha256:${DIGEST}`
  instead of tag. Two callsites to update
  (`reusable-docker-publish.yml` + `docker-acceptance-only.yml`).
  Cost: moderate — untested for our multi-arch aliasing pattern
  (`imagetools create` semantics with digest refs need to be
  verified).

  **Why unclear if worth doing at all:** threat model requires
  already-compromised Docker Hub write access on the openemr org.
  An attacker at that level can push a NEW digest under any tag
  anyway — including a "pinned" digest tag they retag over. So
  digest-pinning defends against a self-signed-org-only tag-swap
  window (minutes to 48h) but does not defend against the
  compromised-credentials scenario that would let the tag-swap
  happen in the first place. If the org-level Docker Hub creds are
  intact, the current re-resolve path is already safe. If they're
  compromised, digest-pinning doesn't stop the attacker. Net: the
  bug rabbit flagged is real but the fix's marginal defense is
  minimal, and the fix itself carries real cost (multi-arch
  imagetools semantics with digest refs are untested for us). Skip
  unless (a) the cost drops dramatically, (b) the threat model
  changes (e.g. multi-org publishing), or (c) an incident shows the
  window matters.

**Sequencing suggestion:** 10a first (smallest, closes a deferred
rabbit finding, no behavior change) — SHIPPED. 10c second (biggest
ergonomic win — eliminates manual recovery steps) — SHIPPED. 10b +
10d + 10f shipped together in parallel — SHIPPED 2026-07-30. 10e
now sub-sliced: 10e-1 SHIPPED 2026-07-30 as #13292; 10e-audit DONE
2026-07-30; 10e-2 + 10e-3 SHIPPED 2026-07-31 as #13294 + #13293;
10e-4 SHIPPED 2026-07-31 as #13306; 10e-5 + 10e-6 SHIPPED 2026-08-01
as #13310 + #13311. **Phase 10 fully wraps 2026-08-01** (10a/b/c/d/f
+ 10e-1 through 10e-6 all shipped). Only 10g remains, marked
OPTIONAL — do not do unless the cost/benefit shifts. Next attack:
Phase 11 (native arm64 for release-time docker builds, rel-810+).

**Not in Phase 10 scope:** anything that changes acceptance
semantics or gate topology. This is refactoring-in-place. If a
consolidation exposes a bug (e.g. drift between the 3 OCI verify
copies), fix in a paired PR with clear before/after tests.

### Phase 11 — Native arm64 for release-time docker builds (rel-810+) *(SHIPPED 2026-08-01 as #13330)*

**Goal:** Move release-time docker builds from QEMU-emulated arm64 on a
single amd64 runner to native arm64 builds on arm64 runners, mirroring
the Phase 7d pattern already used at PR-validation time.

**Motivation** (three benefits, all real):

1. **Reliability.** QEMU intermittently SIGILLs (exit 132) during
   arm64 build steps like `apk add build-base` / composer / npm.
   rel-800 hit this 2026-07-30 daily orchestrator (recovered
   2026-07-31); the same class of failure surfaced repeatedly during
   Phase 7d PR-validation work before we moved to native. Native
   builds don't hit that class of failure at all.
2. **Speed.** Native arm64 is ~5-10x faster than emulated. rel-704 +
   rel-800 builds currently take ~1h (per activity-log 2026-07-27
   note); native would land in 10-15 min.
3. **Correctness parity.** Phase 7d already validates on native arm64
   at PR time. Release-time still QEMU-builds means we ship a
   differently-produced arm64 image than what was validated. Not a
   correctness bug today (same Dockerfile → same content), but a
   drift surface — any QEMU-specific behavior (missing instructions,
   syscall differences) that PR-validation caught would ship uncaught.

**Scope decision — rel-810+ only:**

- rel-704 + rel-800 have a monolithic Dockerfile that predates split-
  arch-friendly structure. Restructuring them is a separate project
  (Dockerfile split, ARG plumbing, cross-arch testing) worth doing
  only if daily-orchestrator flakes become a maintenance burden.
- Those branches are maintenance-only anyway; daily-retry catches
  QEMU flakes eventually and the images are less-frequently pulled.
- rel-810+ have the modern multi-stage Dockerfile that already
  accommodates the split-build pattern Phase 7d uses.

**Design shape:**

- Split `docker-build-release.yml`'s build job into a matrix (amd64
  runner + arm64 runner), each producing a per-arch pushed image
  under an intermediate arch-specific tag.
- Add a manifest-merge job that runs `docker buildx imagetools
  create` to unify the per-arch pushed images into one multi-arch
  final tag. Same imagetools-alias pattern Phase 10c uses for
  candidate → final promotion.
- Preserve all existing gates: OCI-label verify (Phase 10a extracted
  script), acceptance-gate (Phase 7c-docker-latest-gate), publish
  (Phase 10c reusable), cleanup-candidate (Phase 10c reusable + 10e-3
  extracted script).
- Byte-identical: `docker-build-release.yml` IS byte-identical'd to
  rel-810/820+ with rel-800/rel-704 exclusion. The restructure
  propagates automatically to rel-810 + rel-820 (any future rel-830+
  inherits too). Existing exclusion protects rel-800/rel-704 from
  the split-arch structure they can't consume.

**Costs:**

- GHA arm64 hosted-runners are 2x amd64 minutes. Amortized over 4
  daily orchestrator runs (master + rel-820 + one more rel-branch =
  3 native-arm64-eligible per day), roughly triples the arm-related
  minutes vs current single-runner QEMU pass. Real dollar impact is
  small because arm64 build takes 10-15 min native vs 30-45 min
  QEMU, so total minutes are comparable.
- More YAML surface: matrix build + manifest-merge job. Phase 7d
  already carries this pattern for PR-validation, so the shape is
  proven.

**Blast radius on transition:**

- `docker buildx imagetools create` merge pattern proven in Phase 7d
  (PR-time) and Phase 10c (acceptance-only recovery).
- Rollback: revert the workflow structure; each arch image still
  exists on Docker Hub under intermediate tags for the transition
  window.

**Not blocking rel-830:** the ~2-week cadence to rel-830 cut is not
gated by this — QEMU still works, just slower + occasionally flaky.
Phase 11 is quality-of-life + reliability improvement, not
correctness blocker.

**Sequencing:** Phase 11 SHIPPED 2026-08-01 as #13330 — three-job
split (prep + build-arch matrix + merge-manifest). Rabbit found +
we fixed one gap: verify-OCI-labels moved from merge-manifest into
build-arch matrix so both arches get natively verified (docker
pull's arch-selection on merged manifest would otherwise skip
arm64). See activity-log 2026-08-01 for full detail.

**Not in Phase 11 (initial) scope — flex builds** *(`.github/
workflows/docker-build-flex-core.yml`; same `linux/amd64,linux/
arm64` single-runner QEMU pattern as release, moved to openemr core
in the 2026-06-20 docker-migration)*:

Flex would benefit from the same native-arm64 treatment in principle
— same QEMU class of risk, same imagetools-merge pattern applies
directly. Deferred from initial scope because the cost/benefit shape
is proportionally weaker:

- **QEMU pain is much smaller per-build.** Flex images are alpine +
  PHP base only (no composer/npm install inside like the release
  image), so builds finish in ~1-2 min under QEMU vs release's
  30-45 min. SIGILL surface scales with build weight; flex has
  historically been near-zero flake rate.
- **Matrix scale amplifies runner cost.** 3 alpine versions × 4 PHP
  versions ≈ 12 variants × 2 archs = ~24 native jobs per flex-
  refresh vs release-time's 4 rel-branches × 2 archs = 8 jobs.
  GHA arm64 minutes are 2x amd64, so aggregate spend jumps.
- **No PR-validation parity concern.** Release-time has Phase 7d
  PR-validation on native arm64, so QEMU-vs-native drift is a real
  surface. Flex has no equivalent per-arch PR gate — PR tests
  (`docker-test-flex-{322,323,edge}.yml`) pull the built image and
  run test suites against it, agnostic to how it was built.

**Easy to extend later** — same imagetools-merge pattern transfers
directly to `docker-build-flex-core.yml` once the release-time
version is proven and any operational quirks (arm64 runner
availability, retry patterns, cache reuse) are ironed out. Track as
a follow-up Phase 11b if flex QEMU flakes actually surface or the
"parity" argument grows teeth (e.g. if a future flex change
introduces build-step complexity that makes QEMU pain scale up).

### Phase 12 — Extend acceptance-gate to all rel-820+ branches, not just `latest` *(SHIPPED 2026-08-01 as #13332)*

**Goal:** Replace the current "row-contains-latest tag → gate=true"
auto-detection in `docker-release-orchestrator.yml` with an explicit
per-row `gate_with_acceptance: true` flag in `release-targets.yml`.
Enable for master + rel-820 (and any future rel-830+); leave rel-800
+ rel-704 unflagged (they don't have the acceptance-docker.yml
surface anyway per byte-identical exclusion).

**Motivation:** Pre-Phase-11 the CI-budget cost of running the full
6-scenario acceptance-docker matrix was ~1h under QEMU emulation, so
scoping to `latest` only (the widest end-user pull path) was the
right cost/benefit call. Post-Phase-11 the same matrix runs natively
in ~15-25 min. The "expensive CI" argument that drove latest-only is
now much weaker; consistent quality-bar-for-everything-shipped-that-
can-be-tested wins on ergonomics.

**Scope:**
- **Enable gate** on: master (ships `8.3.0` / `dev` / `next`),
  rel-820 (ships `latest` / `8.2.0`), any future rel-830+ (docs
  update to include the flag by default on cut).
- **Skip gate** on: rel-800 + rel-704 — legacy branches, no
  acceptance-docker.yml surface (byte-identical excluded), phasing
  out anyway.

**Design shape:**
1. Add `gate_with_acceptance: true` to master + rel-820 rows in
   `.github/release-targets.yml` (~1 line each).
2. Change `docker-release-orchestrator.yml`'s GATE-detection block
   from "if any tag in row.docker_tags == 'latest', set GATE=true"
   to "if row.gate_with_acceptance == true, set GATE=true". ~5-line
   swap.
3. Update the comment above that block to reflect the new criterion
   (post-Phase-11 native builds make the wider gate affordable).
4. Update release-cut docs (rel-branch cut playbook) to note that
   new rel branches should get `gate_with_acceptance: true` at cut
   time.

**Cost:** adds ~15-25 min to master's daily orchestrator wall-clock
(the master build now waits on acceptance-gate before publish/
cleanup, though publish/cleanup are skipped for non-gated tags —
gating master shifts it to the gated path where publish IS reused
via imagetools create). Real GHA runner minutes: 6 acceptance
scenarios × ~5 min each on native runners, ×2 arches. Small
relative to Phase 11's savings on the same runs.

**Benefit:** `8.3.0` / `dev` / `next` daily rebuilds get the same
acceptance validation as `latest`. Regressions in master's daily
build get caught before shipping to preview-tag users (dev, next)
or numbered-pin users (8.3.0). Consistent "was this validated
before shipping?" bar across everything Phase-11-eligible.

**Consideration re: master's preview tags:** `dev` / `next` are
by-design unstable — users on those tags have opted in to
instability. But "unstable" doesn't mean "we don't check it boots":
acceptance is fresh-install + upgrade smoke, not deep stability
testing. A gate here catches complete-boot-failure regressions
without over-committing to preview-tag stability guarantees.

**Not in Phase 12 scope:**
- Extending gate to flex builds — flex has no equivalent
  acceptance-docker.yml surface. Track as Phase 12b if flex gains
  one.
- Retiring `latest`-based auto-detect entirely — keep as fallback
  for a transition window? Or hard-switch to explicit flag? Choose
  at implementation time; hard-switch is cleaner if the migration
  is atomic (release-targets.yml + orchestrator change land in the
  same PR).

**Sequencing:** implement as a single small PR — release-targets.yml
edit + orchestrator GATE-detect swap + docs update. Low risk;
release-targets.yml is master-only, orchestrator is master-only.
No byte-identical concern.

### Phase 13 — Source-side E2e back-ports *(SHIPPED 2026-08-05)*

Three patterns surfaced while building the acceptance surface that
also reduce false-positive rate on the pre-existing source-side E2e
dev-suite. All three landed as source-side PRs.

1. **CDP `window.alert` muzzle** (#13398). Overrides `window.alert`
   / `.confirm` / `.prompt` via `Page.addScriptToEvaluateOnNewDocument`
   installed once per WebDriver session in `BaseTrait::base()`. Kills
   the clinical-reminders alert race (`library/clinical_rules.php`
   fires `<img onload="alert(...)">` on newly-created patient
   dashboards; used to race source-side `PatientAddTrait`'s
   `wait(10)->until(alertIsPresent())` and cause false-positive
   failures). Installed globally after a grep proved no source-side
   test asserts on alerts — all `alertIsPresent` / `switchTo()->alert()`
   usages are defensive accept, not assertive.

2. **Yank dead `unhandledPromptBehavior=accept`** (#13398, paired
   with #1). Two-line removal from `BaseTrait::createChromeClient`.
   Added defensively 2025-07-05 in #8555, never observed catching
   anything. Acceptance-side yanked its copies (#13358 + #13364)
   after tracing history + proving the CDP muzzle is what works.

3. **Bb row-oracle recovery pattern** (#13397). Replaced source-
   side `UserAddTrait`'s 3-retry-whole-test loop with a single-shot
   `isUserExist` DB check + hard-fail on missing-user. Kept the
   diagnostic-capture machinery (`gatherModalDiagnostics`,
   `captureForceRefreshDiagnostics`, `dumpForceRefreshFailure`)
   intact since source-side has DB access + selenium-videos artifact
   upload that acceptance-side lacks. Also added the paired
   positive-path breadcrumb alongside the recovery-path one so
   grepping logs confirms the mechanism runs end-to-end regardless
   of whether the flake fires.

Ripple in #13398: `PatientAddTrait`'s `wait(10)->until(alertIsPresent())`
dropped (muzzle moots it). `sleep(5)` before confirm-click was
briefly dropped then restored after a CI-only failure surfaced —
it turns out to be guarding a separate click-wiring race unrelated
to the alert flake. Sleep now carries an inline comment naming the
real race + pointing at acceptance-side's direct-form-submit bypass
(from #13372) as the proper follow-up fix if we ever want to drop it.

### Phase 14 — Skip-acceptance escape hatch on the recovery rerun workflows *(SHIPPED 2026-08-05 as #13394)*

Operator escape valve for the two rerun workflows
(`acceptance-only.yml` tarball, `docker-acceptance-only.yml` docker)
when acceptance keeps flaking on a known-good artifact due to a
confirmed test-side issue. Motivated by the Kk clinical-reminders
saga's four-iteration debug window, during which operators re-fired
the rerun workflow multiple times knowing the artifact was fine —
no bypass existed short of the pre-Phase-10c manual publish dance.

**Inputs (matched pair, both workflows):**

- `skip_acceptance` (bool, default `false`) — escape-hatch toggle.
  When `true`, `acceptance-gate` is `if:`-gated OFF and publish
  jumps straight to firing.
- `skip_acceptance_reason` (string, default empty) — required
  non-empty (whitespace-only rejected) when `skip_acceptance=true`.
  First job's first step fails loudly if skip=true + reason empty.

**Behavior:**

| skip_acceptance | reason | Result |
| --- | --- | --- |
| `false` | (ignored) | Normal flow — acceptance matrix runs, publish on green |
| `true` | non-empty | Bypass acceptance, jump to publish. Reason lands in run-name + `::warning::` annotation + `GITHUB_STEP_SUMMARY` markdown block |
| `true` | empty/whitespace | Fail-loud in the first job's first step; never publishes |

**Audit trail on bypass:**

- Bypass reason appears in the workflow `run-name` so it's visible
  from the Actions UI list view without clicking in.
- `::warning::` annotation on the run summary panel.
- Markdown block in `GITHUB_STEP_SUMMARY` with the reason + guidance
  that "if bypasses are repeating on the same failure class, the
  underlying flake needs a fix, not a bypass."

**Publish `needs`/`if` interaction.** `acceptance-gate` is
`if:`-gated, so it either runs to success/failure OR is
skipped-by-`if`. GH Actions auto-skips downstream jobs when a needed
job was skipped-by-`if` unless the downstream uses
`always()`/`!failure()` to opt out. Publish uses:

```yaml
if: always() && !cancelled()
    && fetch-source-*.result == 'success'
    && acceptance-gate.result != 'failure'
    && acceptance-gate.result != 'cancelled'
```

Proceeds on acceptance success (normal path) OR skipped (bypass
path); blocks on acceptance failure OR any workflow-level cancel.

**When to use vs when NOT to use:**

- **Use when:** acceptance has failed N times in a row on the SAME
  artifact, with failures inspection has confirmed are test-side
  flakes. Operator has read the failure logs and understands
  what's flaking.
- **Do NOT use** on a NEW artifact never validated before — first-
  time failures might be real regressions; bypass hides the signal.
- **Do NOT use** repeatedly on the same flake class — that's a
  signal to fix the flake, not bypass. The audit trail is designed
  to make repeat-bypass visible.

**Documented in the runbook.** `docs/RELEASE_PROCESS.md` has
paragraphs under both prong recovery sections describing dispatch
pattern, audit trail, and when-to-use guidance.

**Parked follow-ups** (not shipped, may land as Phase 14b if
demand ever appears): release-description trailer noting the
bypass on the published release object; automated bypass-count
budgeting.

### Production validation — 8.3.0 ship 2026-08-18

The 8.3.0 ship on 2026-08-18 was the first end-to-end automated
release through the ship-release semi-auto path and the first time
the Phase 7c pre-publish acceptance gates (both `build-release-on-tag`
tarball gate and `docker-release-orchestrator` per-branch docker
gates) fired against a real production release rather than a
release-prep-PR dry run.

**Tarball gate (build-release-on-tag Phase 7c):** all 8 matrix cells
passed against the shipped `openemr-8.3.0.tar.gz` + `openemr-8.3.0.zip`
before the publish job fired — fresh-install + wizard-install +
upgrade + wizard-upgrade × tar + zip. GitHub Release object only
came into existence after acceptance-gate returned green (per Phase
7c design: publish requires acceptance).

**Docker gates (Phase 7c + Phase 12 extension):** 3 modern
per-branch acceptance gates fired (master, rel-820, rel-830), each
covering fresh-install-from + fresh-install-to + upgrade on both
amd64 + arm64 runners (6 cells per branch, 18 gated cells total).
rel-820 and rel-830 passed all 6 cells cleanly (12/12) and published:
rel-820 promoted to `openemr/openemr:latest`; rel-830 published
`openemr/openemr:8.3.0`. Master's gate FAILED on the arm64
`Fresh install of from_tag` cell (5/6 on that branch); per Phase 7c
design the master publish job was skipped, so `openemr/openemr:next`
/ `dev` did not refresh on this ship-day dispatch (they refresh on
the next scheduled orchestrator run or `docker-acceptance-only.yml`
recovery dispatch). rel-800 + rel-704 legacy single-job builds
completed successfully; those branches are `gate_with_acceptance`
unset per Phase 12 exclusion (they use the pre-Phase-7c single-step
build+push shape, no acceptance gate).

The single arm64 from_tag failure was on the master line only — not
blocking 8.3.0 since master's `next` image is separate from rel-830's
shipped image. Documented here as a data point on gate surface area:
1 fail out of 18 gated cells (~5.6%), all-master-side, all-arm64-side.

## Test-coverage philosophy

Guidelines for where a new test belongs, once both surfaces exist:

- **Unit / isolated / services** — pure logic that doesn't depend on the
  runtime environment: source-side (`tests/Tests/Unit/**` etc.).
- **API / FHIR / E2E** — needs a booted app, but the app under test is
  the source tree in dev stack: source-side (`tests/Tests/Api/**`,
  `E2e/**`).
- **API / FHIR / E2E — validating the shipped artifact behaves the same**:
  acceptance-side (`tests/Acceptance/**`). Smaller subset — pick the
  critical paths, not the full matrix.
- **Install wizard behavior** — acceptance-side only. Source-side dev
  stack skips setup wizard via `MANUAL_SETUP=yes`; only the shipped
  artifact runs the real installer flow.
- **Upgrade behavior** (`fsupgrade-<N>.sh`, `sql_upgrade.php`) —
  acceptance-side only. Nothing else exercises the auto-upgrade path.
- **Configuration matrix** (PHP × webserver × DB) — stays with
  `test-all.yml`. Not duplicated in acceptance.

Not-a-goal: **don't try to make acceptance a superset of source-side
tests.** Acceptance is a filter: "does the artifact do the important
things end users depend on." Not: "does every source-side test also pass
in acceptance."


## Risks and wrinkles

Real gotchas the acceptance surface has to keep an eye on. Some of
these were live concerns during the design phase and have since been
addressed (e.g. Panther/Selenium setup — solved in Phase 4b via the
`Support/BrowserSession` factory + `nanasess/setup-chromedriver@v2`
install); the rest remain worth periodic re-visit.

- **Docker Hub image availability.** Acceptance runs against
  `openemr/openemr:latest` and `:next`. If either tag goes stale
  (rotator down, orchestrator broken, Docker Hub outage), acceptance
  runs fail spuriously. Mitigation: allow `workflow_dispatch` overrides
  to specific version-tagged variants. Detection: acceptance fails
  with a clear "couldn't pull image" error, not a mysterious install
  failure.
- **Install wizard flakiness.** OpenEMR's install wizard has historically
  been the most fragile part of the boot flow. Acceptance tests hit
  it every run. Watch for regressions here — the acceptance surface is
  the primary automated coverage of the setup wizard path.
- **Post-upgrade data verification.** Some columns and tables change
  schema across major versions. Seeders must write via API / UI (not
  raw SQL) so data flows through whatever schema migrations happen;
  verifiers similarly read via API / UI. Raw SQL reads of seeded rows
  may not match post-upgrade if a migration renamed a column. Phase 4g
  persistence-flow tests deliberately use fixed-identity UI-seeded
  fixtures precisely so both phases exercise the same read path.
- **Compose file drift.** `docker/production/docker-compose.yml`
  evolves over time (new services, env vars). The acceptance override
  needs to track these; a `docker-compose config` sanity step in the
  workflow catches basic breakage.
- **CI runtime cost.** Two image pulls (~1GB each) + two boots +
  test-suite invocation is ~15-25 min per acceptance run. Multiplying
  across the trigger surface (schedule + PRs + dispatch) adds nontrivial
  CI minutes. Not a blocker but a real budget line — batch smart to
  amortize.

## Deferred / known debt

- **Master branch acceptance coverage.** Current wiring covers
  release-time gates (release-prep PRs, docker-build-release,
  acceptance-only recovery) but does not run against master
  automatically outside those paths. Adding master coverage requires
  deciding what "the target version" means on master (currently
  `dev`, which represents a much larger upgrade jump than most
  real-world upgrades). Defer until rel-\* cadence proves the pattern
  is stable enough to widen.
- **Multi-hop upgrade coverage** (e.g. 7.0.4 → 8.2.0). Real user
  scenario but expensive to test comprehensively — quadratic in
  version count. Current default is single-hop (latest → next); add
  multi-hop case-by-case for reported upgrade paths.
- **Kcov replacement strategy.** This surface retired the artifact-
  side kcov path in Phase 5 but didn't specify what replaces it for
  code-coverage measurement. Options: source-side kcov in the dev
  stack (matches what tests do today, easier to instrument), or drop
  artifact coverage entirely and rely on acceptance pass/fail as the
  artifact quality signal. Not currently pressing — codecov/patch on
  source-side PRs covers the practical need.
- **Pre-upgrade seeding stage for post-upgrade persistence tests.**
  Phase 4g persistence-flow tests rely on the upgrade-scenario matrix
  cell's existing behavior (runs fresh-install group against FROM_TAG
  before firing the upgrade), which happens to let a dual-tagged
  test seed on the from side and assert on the to side without any
  extra scaffold. If we ever want persistence tests where the
  from-side and to-side are semantically distinct methods (rather
  than one idempotent method that runs on both), a dedicated
  pre-upgrade seeding stage would need to land — deferred until
  demand appears.

## Post-8.4.0 acceptance-surface refactor plan

**Status:** Deferred until after the next release cut. Captured here so
the work is pickable-up-cold without relying on session context. Trigger
that surfaced this: adding version-display coverage in
openemr/openemr#13635 (three signals: DB via shell, About page via
Panther, `/apis/default/api/version` via Panther) required workarounds
whose shape teaches the wrong pattern for future contributors.

### Motivating problem

Group tags on acceptance tests do three unrelated jobs at once:
1. **Scenario timeline** — "when in the boot→install→upgrade sequence
   this test is meant to run" (e.g., `post-upgrade`).
2. **Runtime state** — "what the app must be configured as before this
   test can run" (e.g., `api-enabled`).
3. **Workflow isolation** — "which of the two workflows can invoke this
   test" (implicit; #13635 had to introduce `version-display` /
   `version-api` groups purely because `acceptance-docker.yml` cannot
   resolve floating tags to `X.Y.Z` at runtime and so cannot set
   `ACCEPTANCE_EXPECTED_VERSION`).

The overloading hides real coverage gaps. Two concrete instances that
surfaced during #13635's design:
- `api-enabled` group tests (`ApiSmokeTest`, `OAuth2ApiEnabledTest`)
  historically ran only in the fresh-install scenario, never
  post-upgrade — because the workflow only invokes `--group=api-enabled`
  after fresh-install. #13635 partially closed this by adding
  post-upgrade `api-enable.php` + `--group=api-enabled` steps to the
  upgrade scenario.
- `version-display` / `version-api` had to be their own groups (rather
  than tagging the existing `post-install` / `post-upgrade` /
  `api-enabled` groups) because `acceptance-docker.yml` would otherwise
  pick them up and fail without `ACCEPTANCE_EXPECTED_VERSION`.

### Current-state snapshot

**Invocation contexts (`acceptance-package.yml`):**

| # | Trigger | Artifact source | Version signal | Matrix |
|---|---------|-----------------|----------------|--------|
| 1 | schedule (daily) | GitHub Releases tarball | `TO_VERSION` default | Default (install-only) |
| 2 | push (paths filter) | GitHub Releases tarball | Same | Default |
| 3 | pull_request (paths filter) | GitHub Releases tarball | Same | Default |
| 4 | push/PR touching `tools/release/**` | PR-built via PackageAssembler | Synthetic `99.99.99` | Expanded |
| 5 | `release-prep/*` branch | PR-built | Parsed from PR title | Expanded |
| 6 | workflow_dispatch | GitHub OR PR-built | Operator input | Expanded |
| 7 | workflow_call (build-release.yml Phase 7c) | Caller artifact | Caller `to_version` | Expanded |
| 8 | workflow_call (acceptance-only.yml Phase 9) | Same replayed | Same | Expanded |

Plus per-branch `FROM_VERSION` derivation from `sql/*-to-*_upgrade.sql`
× shipped-versions manifest (#13630).

**Invocation contexts (`acceptance-docker.yml`):**

| # | Trigger | Artifact source | Version signal | Matrix |
|---|---------|-----------------|----------------|--------|
| 1 | schedule (daily) | Docker Hub `:latest` + `:next` | Floating tag — no `X.Y.Z` resolution | Default |
| 2 | push/PR (paths filter) | Docker Hub tags | Same | Default |
| 3 | workflow_dispatch | Docker Hub OR PR-built image | Operator tag input | Expanded |
| 4 | workflow_call (docker-build-release.yml) | PR-built image | Caller `to_tag` | Expanded |
| 5 | workflow_call (docker-acceptance-only.yml) | Same | Same | Expanded |

**Group → tests (as of 2026-08-20):**

| Group | Tests |
|-------|-------|
| `fresh-install` | Aa, Appointment, Bb, Dd, Document, E2e, Ff, Fhir, FrontPayment, Gg, Install, Kk, OAuth2Smoke |
| `post-upgrade` | Same 13 minus `InstallTest`, plus `UpgradeIntegrity` |
| `wizard-install` | `InstallWizardUiTest` |
| `wizard-upgrade` | `UpgradeWizardUiTest` |
| `api-enabled` | `ApiSmokeTest`, `OAuth2ApiEnabledTest` |
| `version-display` (workaround, #13635) | `VersionDisplayAcceptanceTest` |
| `version-api` (workaround, #13635) | `VersionApiAcceptanceTest` |

**Group invocation per scenario (package workflow):**

- `fresh-install` scenario → `--group=fresh-install` → `api-enable.php` → `--group=api-enabled` → (post-#13635) `--group=version-display` + `--group=version-api`
- `wizard-install` scenario → `--group=wizard-install` → (post-#13635) `--group=version-display`
- `upgrade` scenario → `--group=fresh-install` (against from) → upgrade → `--group=post-upgrade` → (post-#13635) `--group=version-display` + `api-enable.php` + `--group=api-enabled` + `--group=version-api`
- `wizard-upgrade` scenario → `--group=wizard-upgrade` → (post-#13635) `--group=version-display`

### Friction points captured

1. Same test, different context, no way to know from the test which workflow/scenario invoked it.
2. Expected-version signal shape differs per context (env `TO_VERSION` vs matrix `image_tag` vs synthetic `99.99.99` vs PR-title parse).
3. `api-enabled` post-upgrade coverage gap (partially closed in #13635 for package workflow; docker workflow still has the gap).
4. Group tags overloaded across three concerns (scenario timeline, runtime state, workflow isolation).
5. Workflow YAML duplication — both workflows walk the same boot→group→api-enable→group shape independently, with drift risk (e.g., #13635 added post-upgrade api-enable to package only).
6. No mapping-doc reference table for "what runs where" — contributors have to grep both workflows to understand a test's blast radius.
7. Docker workflow's default matrix excludes wizard-* — a wizard-flow regression on `:latest` wouldn't fire outside dispatch.

### Refactor items (proposed, in priority order)

**Item 1: `AcceptanceContext` support class** *(highest leverage, smallest surface)*
Central resolver for "what am I running against?" — expected version, base URL, feature-flag state, scenario name — read from a common `ACCEPTANCE_*` env contract. Tests call `AcceptanceContext::expectedVersion()` instead of `getenv('ACCEPTANCE_EXPECTED_VERSION')`. Fail-fast diagnostic if the context isn't ready (rather than tests failing at their business assertions).

Migration path: introduce class, migrate `VersionDisplayAcceptanceTest` + `VersionApiAcceptanceTest` as the first consumers (they're already env-aware), then adopt in future tests. Existing tests can migrate opportunistically.

**Item 2: Split scenario-timeline from runtime-state group tags**
Rename group tags into two dimensions:
- Scenario timeline: `post-install`, `post-upgrade`, `wizard-completed`
- Runtime state: `api-enabled`, `demo-data-seeded`

Tests declare BOTH tags. Workflow steps advance state, then invoke tests via `--group=<scenario> --group=<state>` (PHPUnit's `--group` is OR — need a small custom filter for AND semantics, OR name-combined groups like `api-enabled-post-upgrade` if AND filter turns out to be too invasive).

Once this lands, `version-display` and `version-api` collapse into `#[Group('post-install')] #[Group('post-upgrade')]` (plus `#[Group('api-enabled')]` for the api variant) — the isolation workaround becomes unnecessary.

**Item 3: Shared boot-orchestration composite action**
Both workflows repeat the boot→group→api-enable→group→teardown shape. Extract to a composite action at `.github/actions/run-acceptance-scenario/` (or a shared shell library at `tests/Acceptance/bin/lib/`). Workflows declare "here's the artifact, here's the expected version, run scenario X." Cuts YAML duplication; makes drift impossible (e.g., item #3 friction, and the `api-enabled` post-upgrade gap in docker workflow).

**Item 4: Docker workflow tag→version resolution**
So `ACCEPTANCE_EXPECTED_VERSION` can be set in docker context too and the `version-display` / `version-api` isolation isn't needed. Options: query Docker Hub API for the tag→digest→config manifest, OR pull the image and read `version.php`, OR require the caller to pass the version explicitly (already the case for workflow_call gates). Simplest is the last one — scheduled/floating-tag runs can skip version-check by not setting the env.

**Item 5 (hygiene): "Invocation contexts" reference section in this doc**
Table of ~6 contexts × what each provides. Not covered elsewhere. Cheap; prevents future confusion. (The table above is a starting point.)

**Item 6 (hygiene): Directory structure by concern as suite grows**
`tests/Acceptance/Version/`, `Upgrade/`, `OAuth/`, `Ui/`. Currently all flat under `tests/Acceptance/`. PHPUnit `<directory>` config handles it naturally.

### Sequencing recommendation

Item 1 first (small standalone refactor PR, highest leverage). Item 2 second (mechanical rename + workflow-invocation update). Item 3 third (biggest structural change; benefits from semantics being settled first). Items 4-6 opportunistic.

### Interim workaround (as of #13635)

`version-display` and `version-api` are dedicated groups purely because `acceptance-docker.yml` can't currently set `ACCEPTANCE_EXPECTED_VERSION`. The workaround is contained (2 test files + additive workflow YAML in package workflow only) and migrates cleanly to standard groups (`post-install`, `post-upgrade`, `api-enabled`) during item #2. No touch to existing tests, so no unwinding needed — just a rename pass.

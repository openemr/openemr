# Artifact acceptance testing plan

Architectural rationale, phased plan, and living tracker for adding a
black-box acceptance test surface that validates OpenEMR's shipped
production artifacts (Docker image + release tarball) end-to-end, without
requiring test infrastructure inside the artifacts themselves.

Companion to [`release-mechanism-migration-from-devops.md`](release-mechanism-migration-from-devops.md)
(release-mechanism consolidation) and [`docker-migration-from-devops.md`](docker-migration-from-devops.md)
(docker-pipeline consolidation, completed 2026-06-20). Sits alongside those
as the next major structural change to how OpenEMR ships. Independent of
both — no ordering dependency, but naturally follows the release-mechanism
migration since acceptance tests become the strongest candidate for
required checks on release-prep PRs.

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
- [Decisions to lock before phase 2](#decisions-to-lock-before-phase-2)
- [Risks and wrinkles](#risks-and-wrinkles)
- [Deferred / known debt](#deferred--known-debt)
- [Feedback wanted](#feedback-wanted)

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

### Phase 1 — Planning + one representative test (this doc + follow-up PR)

- Land this planning doc for community discussion
- Prototype `tests/Acceptance/InstallTest.php` + minimal harness
- Prove the harness pattern works against master's current Docker image
  (no workflow yet, just local runs)

Exit criterion: an `InstallTest` that runs from a developer laptop
against `openemr/openemr:latest` and verifies install completes + admin
login works. Roughly 3-5 days of work.

### Phase 2 — Docker acceptance workflow + upgrade coverage

- Draft `acceptance-docker.yml` (evolving from the parked
  openemr/openemr#12791 scaffolding)
- Wire the InstallTest + a new UpgradeIntegrityTest into the workflow
- Fire on `workflow_dispatch` + daily schedule initially

Exit criterion: workflow succeeds on `latest` → `next` upgrade path, run
manually 3 consecutive days without flake. Roughly 1 week.

### Phase 3 — Package acceptance workflow

- Design the generic-stack compose file for tarball testing
- Draft `acceptance-package.yml`
- Adapt InstallTest + UpgradeIntegrityTest to work against a tarball-
  mounted stack (mostly by making the artifact endpoint configurable)

Exit criterion: workflow succeeds against an 8.0.0 → 8.2.0 tarball
upgrade path. Roughly 1 week.

### Phase 4 — Broaden test coverage

- Add ApiSmokeTest, FhirSmokeTest, E2eCriticalPathTest
- Extract common seeders + assertions into `tests/Acceptance/Support/`
- Consider making acceptance runs required checks on release-prep PRs

Exit criterion: ~30 min total acceptance runtime per artifact,
meaningful coverage of API + FHIR + one critical E2E flow. Roughly 2
weeks.

### Phase 5 — Retire the tests-in-image dependency

- Land the Dockerfile `git clone` → `git archive` switch (equivalent to
  the parked openemr/openemr#12790)
- Restructure or retire `docker-test-core.yml`'s `kcov` profile
- Docker image content aligns with tarball content
- SBOM / provenance parity across the two artifacts

Exit criterion: `docker-test-release.yml` passes with the stripped
image; kcov either moved to source-side (dev stack) or dropped.
Roughly 1 week.

**Total elapsed calendar: 5-6 weeks focused work, longer with regular
release cadence. No hard deadline. Not blocking any specific release.**

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

## Decisions to lock before phase 2

- **Directory name.** `tests/Acceptance/` vs `tests/Artifact/`.
  "Acceptance" is the industry-standard name; "Artifact" is more literal
  about what's being tested. Slight lean toward Acceptance.
- **Namespace.** `OpenEMR\Tests\Acceptance\` vs `OpenEMR\Acceptance\`.
  Slight lean toward former (mirrors existing `OpenEMR\Tests\`).
- **PHPUnit config file.** Separate `phpunit.acceptance.xml` (my
  default) vs one config with a testsuite for acceptance. Separate is
  cleaner but two files to maintain.
- **Composer setup.** Add PHPUnit + Panther as `require-dev` at repo
  root (already there for source-side)? Or nested
  `tests/Acceptance/composer.json` for isolation? Root is simpler and
  the packages overlap heavily.
- **Runner PHP version.** Which PHP version runs the acceptance harness
  on the runner? Should probably match the highest supported (8.5)
  since the harness is meant to talk to any artifact.
- **Artifact source strategy for docker.** Always pull from Docker Hub
  (real end-user experience) vs support "build from current branch's
  Dockerfile" for PR validation? Support both — env-driven.
- **Artifact source strategy for package.** Same — pull from GitHub
  release page vs build locally in the workflow. Support both.
- **Post-upgrade verification depth.** Just verify seeded data readable,
  or also run a subset of API smoke tests, or also drive a Panther E2E
  flow? Suggest: layer these — data readable is required, API smoke is
  strong-preferred, Panther is nice-to-have.

## Risks and wrinkles

- **Docker Hub image availability.** Acceptance runs against
  `openemr/openemr:latest` and `:next`. If either tag goes stale
  (rotator down, orchestrator broken, Docker Hub outage), acceptance
  runs fail spuriously. Mitigation: allow `workflow_dispatch` overrides
  to specific version-tagged variants. Detection: the acceptance run
  fails with a clear "couldn't pull image" error, not a mysterious
  install failure.
- **Install wizard flakiness.** OpenEMR's install wizard has historically
  been the most fragile part of the boot flow. Acceptance tests hit it
  every run. Some initial flake likely — build in retries + generous
  timeouts.
- **Panther / Selenium in acceptance runner.** Panther expects a browser
  driver. Two options: run Selenium as a sidecar container in the same
  compose, or install ChromeDriver on the runner. Sidecar container is
  cleaner (matches how existing E2E tests do it).
- **Post-upgrade data verification** — some columns and tables change
  schema across major versions. The seeder needs to write data via the
  API / UI (not raw SQL), so seeded data flows through whatever schema
  migrations happen. And the verifier needs to read via API / UI
  similarly. Raw SQL reads of seeded rows may not match post-upgrade if
  a migration renamed a column.
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

- **Master branch acceptance coverage.** Initial rollout wires
  acceptance to rel-* PRs only (matching the pattern of
  openemr/openemr#12791). Adding master coverage requires deciding
  what "the target version" means on master (currently `dev`, which
  represents a much larger upgrade jump than most real-world upgrades).
  Defer until rel-* case proves stable.
- **Multi-hop upgrade coverage** (e.g. 7.0.4 → 8.2.0). Real user
  scenario but expensive to test comprehensively — quadratic in
  version count. Start with single-hop (latest → next), add multi-hop
  case-by-case for reported upgrade paths.
- **Package acceptance for zip artifact.** Initial rollout tests the
  tarball only. Zip is byte-similar; add later when the tarball path is
  solid.
- **Kcov replacement strategy.** This plan retires the artifact-side
  kcov path but doesn't specify what replaces it for code coverage
  measurement. Options: source-side kcov in the dev stack (matches
  what tests do today, easier to instrument), or drop artifact coverage
  entirely and rely on acceptance pass/fail as the artifact quality
  signal. Decide during Phase 5.
- **Migration testing infrastructure to openemr-devops parity.** The
  release-mechanism migration [`release-mechanism-migration-from-devops.md`](release-mechanism-migration-from-devops.md)
  runs on a similar phasing. Once both are complete, acceptance runs
  become the natural quality gate on release-prep PRs — but only after
  release-mechanism migration lands the changes that make PRs the
  authoritative release-gate surface.

## Feedback wanted

- Directory naming (`tests/Acceptance/` vs `tests/Artifact/`)
- Phasing order — is Phase 3 (package acceptance) really the right
  followup to Phase 2 (docker acceptance)? Or should Phase 2 broaden
  the docker test coverage first, and Phase 3 add package?
- Test-runtime budget target — is ~30 min per artifact per run
  acceptable? Any hard ceiling?
- Whether to make acceptance runs required checks on release-prep PRs
  from Phase 4, or leave voluntary indefinitely
- Naming conventions for the `--group` phpunit annotations
  (`fresh-install` / `post-upgrade` / `api-smoke` / etc.)
- Which prior version(s) to include in the default upgrade-path matrix
  (just `latest` → `next`, or also `7.0.4` → `next` for a two-major-
  jump case?)

## Update log

- **2026-07-07** — Doc drafted after the openemr/openemr#12790 (docker
  git-archive) + openemr/openemr#12791 (docker upgrade smoke test)
  exploration surfaced that testing-in-image is the tail wagging the
  dog. Both PRs closed pending this plan; branches kept alive as
  reference material for phases 2 + 5.

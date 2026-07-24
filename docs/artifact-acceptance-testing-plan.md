# Artifact acceptance testing plan

Architectural rationale, phased plan, and living tracker for adding a
black-box acceptance test surface that validates OpenEMR's shipped
production artifacts (Docker image + release tarball) end-to-end, without
requiring test infrastructure inside the artifacts themselves.

Companion to [`release-mechanism-migration-from-devops.md`](release-mechanism-migration-from-devops.md)
(release-mechanism consolidation, completed 2026-07-23) and
[`docker-migration-from-devops.md`](docker-migration-from-devops.md)
(docker-pipeline consolidation, completed 2026-06-20). Sits alongside those
as the next major structural change to how OpenEMR ships. Independent of
both — no ordering dependency, but naturally follows the release-mechanism
migration since acceptance tests become the strongest candidate for
required checks on release-prep PRs; now that release-mechanism migration
has landed, this plan is the next candidate to pick up.

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

**Rollout order**: `1 → 2 → 2.5 → 3 → 4 → 5 → 6`. Phases execute in
numeric order. Phase 6 was added 2026-07-24 as a scoped-out follow-up
to Phase 5 — Phase 5 alone doesn't unlock full-fidelity PR-image
validation (see [Phase 5](#phase-5--retire-the-tests-in-image-dependency)
and [Phase 6](#phase-6--full-fidelity-pr-image-testing-source-mode-indirection)
for the misread-and-correction thread that generated Phase 6).

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

### Phase 2.5 — Build-from-codebase for PR validation *(extension of Phase 2, IN FLIGHT)*

**STATUS: IN FLIGHT** as
[openemr/openemr#13163](https://github.com/openemr/openemr/pull/13163).

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

### Phase 3 — Package acceptance workflow

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

### Phase 4 — Broaden test coverage

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
source (matching what Phase 3 gets for free with tarballs).

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

**Total remaining calendar (from 2026-07-24 baseline):** ~5-6
weeks focused work through Phase 6. No hard deadline. rel-830 (~2
weeks out) gets the Phase 1+2+2.5 baseline once 2.5 lands;
Phases 3+4+5+6 land into a rel-830-shipped codebase.

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
- **Drift management across rel-* branches.** ~~Open~~ **Locked
  2026-07-07: byte-identical enforcement (docker-pipeline pattern).**
  Same drift concern applies here as for the release-mechanism
  migration (tracked in the release gaps doc as G15) — the acceptance
  workflows + compose files + `tests/Acceptance/**` tree need to exist
  on every rel-* branch to fire from those branches' PR + push
  contexts, which is a drift surface. Same decision holds for the same
  six reasons captured in G15 (production-validated pattern; lower
  risk; same team mental model; doesn't preclude reusable-workflows
  later; sync PRs stay review-gated; preserves per-branch customization
  escape hatch). Add to `.github/docker-byte-identical.yml`'s
  `files:` list at Phase 2 (docker acceptance workflow) and Phase 3
  (package acceptance workflow):

  ```yaml
  files:
    - .github/workflows/acceptance-docker.yml       # add in Phase 2
    - .github/workflows/acceptance-package.yml      # add in Phase 3
    - .github/docker/acceptance-docker-compose.yml  # add in Phase 2
    - .github/docker/acceptance-package-compose.yml # add in Phase 3
    - tests/Acceptance/**                           # add in Phase 2, grows through Phase 4
  ```

  Estimated additions: 2 workflow files + 2 compose files + whole
  `tests/Acceptance/**` tree glob. Post-acceptance-plan additions
  probably ~5-10 files enumerated + a glob (much smaller than the
  release-mechanism migration's ~20-25 file addition since acceptance
  has fewer moving parts).

  **rel-820 exception (2026-07-24):** Phase 1 (openemr/openemr#13149,
  landed 2026-07-24) added `symfony/mime` to `require-dev` for
  BrowserKit's POST-body path. rel-820 was cut before this dependency
  was added, so byte-identically syncing `tests/Acceptance/**` onto
  rel-820 would produce a branch where the acceptance suite can't
  actually run. Deliberately SKIP rel-820 from the byte-identical
  sync for these acceptance paths — enforcement kicks in from
  **rel-830 onward** (not yet cut; will be cut from a master that
  already has `symfony/mime` in composer.json). Two ways this can
  be implemented in Phase 2 depending on how the byte-identical
  tooling handles per-branch exclusions:
    1. Per-file `exclude:` list in `docker-byte-identical.yml` that
       names rel-820 for the acceptance-specific entries only, OR
    2. A blanket "acceptance byte-identical is rel-830+" gate in
       the sync workflow itself.

  Not a permanent exception — once rel-820 is EOL (post-8.2.1 or
  whenever the next-next release ships), the exclusion can be
  removed.

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
  **completed 2026-07-23** (Phase 6 wholesale delete of the devops
  release-mechanism surface, openemr/openemr-devops#863). PRs are now
  the authoritative release-gate surface — so this plan can proceed
  with acceptance runs as the target quality gate on release-prep PRs
  without waiting on further release-mechanism scaffolding.

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

- **2026-07-23** — Added Phase 2.5 (build-from-codebase for PR
  validation). Was already hinted as a workflow input option in the
  "Workflows" section but not scheduled; splitting out as its own
  slice makes the sequencing explicit: land Phase 2 against Docker
  Hub tags first, then layer PR-image validation on top once the
  harness pattern is proven. Two invocation shapes captured
  (fresh install of PR-built; latest -> PR-built upgrade), each
  answering a distinct pre-merge question. Phase 1 InstallTest
  design should treat artifact endpoint as opaque (Docker Hub tag
  vs local build) so Phase 2.5 is workflow-only wiring, not a test
  rewrite.

- **2026-07-23** — Split Phase 2 workflow into 3 matrix
  scenarios (fresh-install of from_tag, fresh-install of to_tag,
  upgrade from_tag -> to_tag). Prior sketch only exercised
  fresh-install of from_tag before the upgrade cycle; the
  target-version installer code path (setup.php on next) went
  unvalidated. Splitting scenarios also isolates failure modes
  and enables parallel matrix jobs.

- **2026-07-24** — Captured rel-820 byte-identical exception:
  Phase 1 (#13149) added `symfony/mime` require-dev for BrowserKit
  POST-body support; rel-820 was cut before this dep landed, so
  syncing `tests/Acceptance/**` onto rel-820 would produce a
  branch where the suite cannot run. Byte-identical enforcement
  for the acceptance paths starts at rel-830 (not yet cut; will
  be cut from master that already has the dep).

- **2026-07-24** — Phase 1 SHIPPED as #13149. Phase 2 SHIPPED as
  #13159. Phase 2.5 IN FLIGHT as #13163. Reordered plan: Phase 5
  (Dockerfile git-clone -> git-archive) promoted from last to
  execute right after Phase 2.5, before Phase 3. Rationale: current
  Phase 2.5 validates PR-Dockerfile + master-source (hybrid). End
  goal is testing the actual PR-derived artifact. Phase 5 removes
  the Dockerfile git-clone so the build consumes local checkout,
  which makes Phase 2.5 automatically full-fidelity with zero
  workflow changes. Also delivers docker/tarball content parity
  for SBOM/provenance. Reordered sections in-place; Phase numbers
  remain stable as labels.

- **2026-07-24 (revision)** — Retracted the earlier same-day Phase 5
  promotion. Careful re-read of parked openemr/openemr#12790 showed
  that Phase 5 (git clone -> git clone + git archive HEAD | tar -x)
  does NOT switch to local-checkout source — it keeps cloning from
  GitHub via OPENEMR_VERSION and just filters the cloned content
  through export-ignore. So Phase 5 delivers content parity + image
  size reduction (real value), but does NOT unlock full-fidelity
  PR-image validation as I had claimed.

  Additionally, a naive "build from local checkout" approach would
  BREAK release builds: the pipeline currently dispatches
  docker-build-release.yml on rel-820 with openemr_version_ref=v8_2_0,
  and the Dockerfile clones v8_2_0 (a frozen tag) as source — that
  decoupling of "workflow checkout ref" vs "source-baked-into-image
  ref" would be lost if the docker build just used whatever was
  checked out on the runner.

  Corrections:
  * Reverted plan doc back to original phase order (`1 -> 2 -> 2.5
    -> 3 -> 4 -> 5`).
  * Fixed Phase 2.5's "Known limitation" text — points to Phase 6,
    not Phase 5.
  * Rewrote Phase 5 section to accurately scope: content parity +
    kcov restructure only. Explicit "what Phase 5 does NOT do"
    callout for the misread avoidance.
  * Added Phase 6 — full-fidelity PR-image testing via source-mode
    indirection. Includes feasibility survey (Options A/B/C:
    source-mode build-arg, named build contexts, temp-branch push)
    with tradeoffs and preferred-option lean. Preserves the ship-
    pipeline decoupling constraint that Phase 5 confusion surfaced.

  Total remaining calendar updated: ~5-6 weeks through Phase 6.

- **2026-07-24** — Added wizard-UI coverage for tarball path to
  Phase 4 (InstallWizardUiTest + UpgradeWizardUiTest). Phase 3 as
  currently scoped uses CLI install/upgrade paths
  (Installer::quick_install() + sql_upgrade.php CLI mode) — same
  underlying logic as the wizard but bypasses the setup.php state
  machine, form fields, and HTML rendering that real tarball users
  see. Wizard UI tests fill that coverage gap. Reuses the
  Panther+Selenium plumbing that E2eCriticalPathTest introduces.
  Docker artifact skips these tests (auto-install via env vars
  means Docker users never see the wizard). Runs only against the
  tarball acceptance workflow.

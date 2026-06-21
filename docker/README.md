# `docker/` — directory orientation

This directory contains everything needed to build, test, publish, and consume
OpenEMR Docker images. For consumer-facing guidance (which tag to pull, how to
start a production stack, dev environment overview) see
[`../DOCKER_README.md`](../DOCKER_README.md). The notes below are oriented at
maintainers and contributors who need to navigate the source.

## Image source

These three subdirectories each contain a `Dockerfile` that gets published to
[Docker Hub](https://hub.docker.com/r/openemr/openemr/):

| Path | Image kind | Branch scope | Publishes |
|---|---|---|---|
| [`release/`](release/) | Production | Per-branch: master + every `rel-*` | Versioned tags like `8.0.0.3`, `7.0.4`; floating aliases `latest` / `next` / `dev` (which alias maps to which branch is set in [`.github/release-targets.yml`](../.github/release-targets.yml)) |
| [`flex/`](flex/) | Flex (development) | Master only | `flex`, `flex-3.22`, `flex-3.23`, `flex-edge`, and `flex-<alpine>-php-<php>` variants |
| [`binary/`](binary/) | Binary release | Master only | Specific binary builds for offline / appliance use |

The release Dockerfile is per-branch because each rel branch pins its own
Alpine + PHP version and its own `ARG OPENEMR_VERSION` default. The flex
Dockerfile is master-only because flex fetches OpenEMR source at runtime
rather than baking it in — there's nothing branch-specific to vary.

## Consumer compose files

| Path | Purpose |
|---|---|
| [`production/`](production/) | Example `docker-compose.yml` for running OpenEMR in production. Works on amd64 + arm64 (Raspberry Pi). |
| [`development-easy/`](development-easy/) | Primary dev environment (used by `openemr-cmd worktree` and the contributor workflow in [`../CONTRIBUTING.md`](../CONTRIBUTING.md)). |
| [`development-easy-light/`](development-easy-light/) | Slimmer variant of easy-dev. |
| [`development-easy-redis/`](development-easy-redis/) | Easy-dev plus Redis session store. |
| [`development-insane/`](development-insane/) | Full-stack dev environment with extra services. See its [README](development-insane/README.md). |

Image references in the development-* composes are pinned by SHA and bumped
automatically by Dependabot under the `openemr-images` group in
[`../.github/dependabot.yml`](../.github/dependabot.yml).

## Pipeline + tooling

| Path | Purpose |
|---|---|
| [`dockerhub/`](dockerhub/) | Docker Hub repo overview rendering. `overview.md` is the template; `render.sh` is the bash + yq + jq + sed renderer (reads [`../.github/release-targets.yml`](../.github/release-targets.yml) + scans `docker-build-*.yml` flex callers); `tests/` carries a Tier 1 sanity check and a Tier 2 golden-file test. Published by `.github/workflows/docker-push-dockerhub-readme.yml`. |
| [`container_benchmarking/`](container_benchmarking/) | Container functionality + performance test harness. Driven by `.github/workflows/docker-test-container-functionality.yml`. |
| [`library/`](library/) | Shared assets pulled into multiple images: SQL/LDAP/CouchDB SSL cert fixtures, dev-only PHP-FPM base Dockerfiles, API scope listings. |
| [`compose.yml`](compose.yml) | **CI test-harness compose** used by `.github/actions/test-actions-core`. NOT for end-user `docker compose up` workflows — those live in `production/` and `development-*/`. |
| [`COVERAGE.md`](COVERAGE.md) | Kcov entrypoint-script coverage docs. |

## Publish flow

Daily 06:00 UTC cron on `.github/workflows/docker-release-orchestrator.yml`
(or manual `workflow_dispatch`) reads `.github/release-targets.yml` and fans
out one `docker-build-release.yml` dispatch per row to the corresponding rel
branch. Each dispatch builds + pushes that branch's tags. Once the
orchestrator completes, `docker-push-dockerhub-readme.yml` fires via
`workflow_run` and republishes the Docker Hub overview rendered from
`docker/dockerhub/overview.md`.

The three flex publish workflows (`docker-build-{322,323,edge}.yml`) run on
their own daily 02:00 UTC cron, independent of the orchestrator.

## Validation guards

| Workflow | What it catches |
|---|---|
| `docker-validate-byte-identical.yml` | Files listed in [`.github/docker-byte-identical.yml`](../.github/docker-byte-identical.yml) must stay byte-identical across master + every rel branch. Fires on every PR to master or `rel-*` + daily 07:00 UTC cron. |
| `sync-byte-identical.yml` | Auto-propagates byte-identical file changes from master to every rel branch (opens or updates a long-lived sync PR per branch). Fires on master push + daily 09:00 UTC backstop. Pairs with the canary above. |
| `docker-validate-release-targets.yml` | Schema validation on `release-targets.yml`, git-ref resolution checks, and `docker_tags` ↔ `version.php` alignment on master. |
| `docker-test-{bats,container-functionality,core,release}.yml` + `docker-test-flex-{322,323,edge}.yml` | Build the image locally and exercise it (BATS, container functionality, OpenEMR install). Catches build-time and runtime regressions before publish. |
| `docker-lint-hadolint.yml` + `docker-compose-lint.yml` | Lint Dockerfiles and compose files. |

## History

This whole pipeline was migrated from
[`openemr/openemr-devops`](https://github.com/openemr/openemr-devops) (where it
previously lived under `docker/openemr/`) into this repository in June 2026.
The architecture, rationale, and migration mechanics are written up in
[`../docs/docker-migration-from-devops.md`](../docs/docker-migration-from-devops.md).

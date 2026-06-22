# OpenEMR Docker Documentation

## Overview
The OpenEMR community loves Docker. We eat and breathe Docker. The OpenEMR dockers and detailed documentation
can be found on [dockerhub](https://hub.docker.com/r/openemr/openemr/). There are two main categories of
dockers for OpenEMR, Production Dockers and Development Dockers. Production dockers are meant for production
use with tags such as `8.0.0.3` or `7.0.4`, whereby the `latest` tag identifies the most recent **stable
production release** (not the absolute newest build — `next` and `dev` carry in-development versions).
Development dockers are mainly meant for development and/or testing and include the `flex`
series which are highly flexible development dockers that are used to create the standard OpenEMR development
environments. There is also a less-flexible development docker rebuilt daily, tagged `dev` (head of master)
and `next` (head of the upcoming-release rel branch).

## Production Dockers
Production dockers are meant for production use with tags such as `8.0.0.3` or `7.0.4`, whereby the `latest`
tag identifies the most recent **stable production release** (not the absolute newest build — `next` and
`dev` carry in-development versions). All production tags are published to
[dockerhub](https://hub.docker.com/r/openemr/openemr/). Several example docker-compose.yml scripts are
discussed below.

### Production example
An example docker-compose.yml script can be found at
[docker/production/docker-compose.yml](docker/production/docker-compose.yml). After modifying the
script for your purposes, it can then be started with `docker compose up`, which will then take about 5-10
minutes to complete.

### Production example for Raspberry Pi
The production example above ([docker/production/docker-compose.yml](docker/production/docker-compose.yml)) will also work on Raspberry Pi (arm64).

## Development Dockers
Development dockers are meant for development and testing and can be found on
[dockerhub](https://hub.docker.com/r/openemr/openemr/).

### `Flex` Series Development Dockers and Development Environments
The `flex` series development dockers are highly flexible development dockers that are used to create the
standard OpenEMR development environments, and can be found on
[dockerhub](https://hub.docker.com/r/openemr/openemr/). It is strongly recommended to not use these dockers
for production purposes unless you know what you are doing. There are 2 OpenEMR development environments,
which are based on these development dockers. The main development environment is the Easy Development Docker
environment, which is documented at [CONTRIBUTING.md](CONTRIBUTING.md#code-contributions-local-development);
note this environment can also be run on Raspberry Pi (arm64). The other development environment, which is much more
complex, is the Insane Development Docker environment, which is documented at
[docker/development-insane/README.md](docker/development-insane/README.md#insane-development-docker-environment).

### Nightly build Development Docker
There is also a less-flexible development docker rebuilt daily, tagged `dev` (head of master) and `next`
(head of the upcoming-release rel branch), mainly used for testing. Available on
[dockerhub](https://hub.docker.com/r/openemr/openemr/).

## Source repository and pipeline

This repository owns the production Docker image pipeline end-to-end as of June 2026. Previously it lived in
[openemr/openemr-devops](https://github.com/openemr/openemr-devops); see
[docs/docker-migration-from-devops.md](docs/docker-migration-from-devops.md) for the migration history and
architectural rationale.

For maintainers and contributors, the per-directory orientation guide is [docker/README.md](docker/README.md).
At a glance:

- **Source of truth for what gets published**: [`.github/release-targets.yml`](.github/release-targets.yml).
  One row per `rel-*` branch; each row declares which Docker tags it publishes and which OpenEMR ref to
  bake in. Adding a row publishes a new rel branch's tags; editing a row's `docker_tags` reshuffles the
  floating `latest`/`next`/`dev` aliases.
- **Dockerfiles**: `docker/release/Dockerfile` (production, per-branch), `docker/flex/Dockerfile` (flex
  dev image, master-only), `docker/binary/Dockerfile` (binary release variant, master-only).
- **Publish workflows** (all under `.github/workflows/`):
  - `docker-release-orchestrator.yml` — daily 06:00 UTC cron; reads `release-targets.yml` and fans out
    one `docker-build-release.yml` dispatch per row.
  - `docker-build-release.yml` — byte-identical across master + every `rel-*`; builds + pushes per branch.
  - `docker-build-{322,323,edge}.yml` + `docker-build-flex-core.yml` — flex publish (daily 02:00 UTC).
  - `docker-push-dockerhub-readme.yml` — pushes the rendered Docker Hub overview after each orchestrator
    run, sourced from `docker/dockerhub/overview.md`.
  - `docker-validate-byte-identical.yml` + `docker-validate-release-targets.yml` — drift canaries.
- **Docker Hub readme rendering**: [`docker/dockerhub/`](docker/dockerhub/) — `overview.md` template,
  `render.sh` bash + yq renderer, and a two-tier test suite (`tests/sanity.sh` + `tests/golden-test.sh`).

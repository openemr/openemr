Bumps `ARG ALPINE_VERSION` in `docker/binary/Dockerfile` and `docker/release/Dockerfile` to the newest Alpine 3.x minor line, plus the default documented in the binary image's README.

Opened by `.github/workflows/updatecli-docker-pins.yml` from `.github/updatecli/alpine-base.yaml`. Nothing here merges itself — review the diff and merge when the bump looks right.

#### Why a bot and not Dependabot

Dependabot's docker ecosystem parses literal `FROM alpine:3.24` lines. Every OpenEMR Dockerfile writes `FROM alpine:${ALPINE_VERSION}`, so the entries that covered these files produced no PRs at all (see `docs/docker-migration-from-devops.md`). With nothing watching them, the two pins drifted apart on their own — binary on 3.22, release on 3.24.

#### Scope

- Only `docker/binary` and `docker/release` are targeted. Their `ARG` default *is* the version that ships: neither build passes an `ALPINE_VERSION` build-arg.
- `docker/flex` is deliberately excluded. Every published flex image overrides `ALPINE_VERSION` from the `docker-build-3XX.yml` matrix, so its default is never the shipped value, and supporting a new Alpine line there means adding a workflow file rather than moving a version string.
- The pin tracks a minor line (`3.24`), not a patch (`3.24.1`), so rebuilds pick up patch-level security fixes without a code change.
- Constrained to Alpine 3.x. A major jump is a deliberate migration, not something a scheduled bot should propose.

#### What still needs a human

A minor Alpine bump can move package versions and default toolchains under the image. The Docker test suites on this PR are the evidence that it did not.

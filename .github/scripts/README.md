# `.github/scripts/`

Standalone scripts invoked by GitHub Actions workflows. Extracted from
inline workflow `run:` blocks when the logic is complex enough to benefit
from testing or reuse.

## What's here

| Path | Invoked by | Tested by |
|---|---|---|
| [`sync-byte-identical.sh`](sync-byte-identical.sh) | [`.github/workflows/sync-byte-identical.yml`](../workflows/sync-byte-identical.yml) | [`tests/bats/ci-scripts/sync-byte-identical/`](../../tests/bats/ci-scripts/sync-byte-identical/) (BATS, synthetic git repo) |
| [`validate-byte-identical.sh`](validate-byte-identical.sh) | [`.github/workflows/docker-validate-byte-identical.yml`](../workflows/docker-validate-byte-identical.yml) | [`tests/bats/ci-scripts/validate-byte-identical/`](../../tests/bats/ci-scripts/validate-byte-identical/) (BATS, curl mocked via PATH shim) |

## Running the tests locally

```sh
# From the repo root:
bats tests/bats/ci-scripts/sync-byte-identical/
bats tests/bats/ci-scripts/validate-byte-identical/
```

Requires [BATS](https://github.com/bats-core/bats-core) (v1.13.0 per
repo standard), `bash`, `git`, and
[Mike Farah's `yq`](https://github.com/mikefarah/yq) on PATH.

CI installs BATS via [`.github/workflows/test-byte-identical-scripts.yml`](../workflows/test-byte-identical-scripts.yml)
using the same pattern as the existing
[`docker-test-bats.yml`](../workflows/docker-test-bats.yml).

## Conventions for new scripts

When extracting another workflow's bash into a script here:

1. Put the script at `.github/scripts/<name>.sh`. Make it executable
   (`chmod +x`).
2. Use `set -euo pipefail` at the top.
3. Read inputs from positional args or env vars; don't bake in any
   workflow-specific paths.
4. Emit `::error::` / `::warning::` annotations only when
   `$GITHUB_ACTIONS == 'true'`; fall back to plain stderr otherwise so
   the BATS output stays readable.
5. Write structured outputs to files in `$OUTPUT_DIR` (default a fresh
   temp dir) rather than just stdout, so tests can assert on them
   precisely.
6. Add tests at `tests/bats/ci-scripts/<name>/<name>.bats` with a
   sibling `helpers.bash`. Match the pattern of the existing
   [`sync-byte-identical/`](../../tests/bats/ci-scripts/sync-byte-identical/)
   suite.
7. Add a CI workflow that installs BATS and runs the suite on PR
   changes (model after
   [`test-byte-identical-scripts.yml`](../workflows/test-byte-identical-scripts.yml)).

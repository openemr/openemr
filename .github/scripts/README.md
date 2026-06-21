# `.github/scripts/`

Standalone scripts invoked by GitHub Actions workflows. Extracted from
inline workflow `run:` blocks when the logic is complex enough to benefit
from testing or reuse.

## What's here

| Path | Invoked by | Tested by |
|---|---|---|
| [`sync-byte-identical.sh`](sync-byte-identical.sh) | [`.github/workflows/sync-byte-identical.yml`](../workflows/sync-byte-identical.yml) | [`tests/test-sync-byte-identical.sh`](tests/test-sync-byte-identical.sh) |

## Running the tests locally

```sh
# From the repo root:
bash .github/scripts/tests/run-tests.sh
```

Requires `bash`, `git`, and [Mike Farah's `yq`](https://github.com/mikefarah/yq)
on PATH. No other dependencies (no BATS, no shellspec).

The test runner discovers every `test-*.sh` file under
[`tests/`](tests/) and runs every `test_*` function in each. Tests run in
subshells so a failure doesn't cascade.

## Conventions for new scripts

When extracting another workflow's bash into a script here:

1. Put the script at `.github/scripts/<name>.sh`. Make it executable
   (`chmod +x`).
2. Use `set -euo pipefail` at the top.
3. Read inputs from positional args or env vars; don't bake in any
   workflow-specific paths.
4. Emit `::error::` / `::warning::` annotations only when
   `$GITHUB_ACTIONS == 'true'`; fall back to plain stderr otherwise so
   the test suite output stays readable.
5. Write structured outputs to files in `$OUTPUT_DIR` (default a fresh
   temp dir) rather than just stdout, so tests can assert on them
   precisely.
6. Add tests at `.github/scripts/tests/test-<name>.sh` using the
   patterns in [`tests/helpers.sh`](tests/helpers.sh).
7. Add a CI workflow that runs the tests on PR changes (model after
   [`test-byte-identical-scripts.yml`](../workflows/test-byte-identical-scripts.yml)).

## Why bash + custom runner instead of BATS

The repo already has a BATS suite for `openemr-cmd` testing, but that
harness expects a running Docker stack to exec commands against — wrong
context for "test bash logic against synthetic git state." Building a
new BATS harness for fixture-driven git tests would have added a layer
of indirection without buying any testing capability that `set -e` +
shell asserts don't already give. See [`tests/helpers.sh`](tests/helpers.sh)
for the assertion helpers.

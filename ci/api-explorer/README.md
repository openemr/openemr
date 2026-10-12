# ci/api-explorer

End-to-end smoke that exercises OpenEMR's OAuth2 + FHIR surface from a real
third-party client (`sjpadgett/oe-module-api-explorer`). Runs on every PR; its
job is to catch regressions in the OAuth2 code path that in-tree unit and
isolated tests cannot see — real DCR, real JWT assertion, real FHIR Patient
read with a system scope.

## What the smoke does

1. Boots `docker/development-easy` with `docker compose up --wait`.
2. Clones `sjpadgett/oe-module-api-explorer` at a **pinned commit SHA** into
   `devtools/oe-module-api-explorer/` (the explorer loads
   `../../interface/globals.php` and must sit two levels below the OpenEMR
   root; it is **not** an `interface/modules/custom_modules/` module).
3. Writes a CI-only `config.local.php` pointing the explorer at a single site
   named `docker` at `https://openemr`.
4. Seeds demo data via `openemr-cmd drid` so the FHIR Patient endpoint has
   something to return.
5. Seeds the globals the explorer requires: `site_addr_oath`, `rest_api`,
   `rest_fhir_api`, `rest_system_scopes_api`. (Must run **after** `drid` —
   drid rewrites `site_addr_oath`.)
6. Runs the Guzzle driver, which:
   - Fetches `oeApiExplorer.php` once to initialize the PHP session.
   - Fetches `client_register.php?regen=1`, which calls OpenEMR's DCR
     endpoint to register four clients (confidential auth-code, public PKCE,
     JWT client-credentials, SMART EHR launch) and enables them in the DB.
   - Fetches `oeApiExplorer.php` with the request form's query shape to run a
     JWT client-credentials grant + `GET /fhir/Patient` round-trip inside the
     explorer's own PHP (no browser — the JWT grant is server-to-server).
   - Asserts the response HTML carries a FHIR `Bundle` with `total >= 1`.

The smoke runs in ~30s on top of the stack boot. It exercises paths unit
tests cannot reach (RFC 7591 DCR with inline JWKS, JWT assertion generation
and validation, system-scope FHIR reads) from a client the server has no
awareness of until registration time, which is as close to "real external
app" as you can get in CI.

## What the smoke does **not** do

- No authorization-code / consent-UI click-through. That path is covered
  in-tree by `tests/Acceptance/Support/OAuth2/AuthCodeFlow.php` already and
  the browser-driven version would need Selenium + 5+ minute runtime.
- No SMART-launch coverage. The sibling smoke (`oe-smart-questionnaire`,
  acceptance-side) owns that path.
- No conformance surface. If the explorer added bulk `$export` or FHIR write
  coverage to this smoke, it would still be a smoke, not Inferno.

## Updating the pin

`EXPLORER_SHA` lives at the top of `.github/workflows/api-explorer-test.yml`.
The drift-check workflow files a tracking issue weekly when the pin lags
upstream `main`; opening a bump PR is a normal reviewed change — never
auto-merged.

If upstream makes a breaking change the smoke catches, the right fix is
usually a conversation with the module maintainer, not a hasty pin bump.

## Reproducing locally

```bash
openemr-cmd worktree add my-smoke -b --start --env easy-light
EXPLORER_SHA=<40-char-hex> ci/api-explorer/run.sh
```

The `easy-light` env (no Selenium / CouchDB / Mailpit) is enough for this
smoke; the full `easy` env works too.

## Files

| File | Purpose |
|------|---------|
| `run.sh` | Entry point: clone the explorer, seed demo data + globals, invoke the driver. |
| `driver.php` | Guzzle round-trip against the explorer's three request paths. |
| `seed-globals.sql` | REPLACE INTO globals rows the explorer requires. Keys backed by `src/Services/Globals/GlobalConnectorsEnum.php`. |

## Credits

[`sjpadgett/oe-module-api-explorer`](https://github.com/sjpadgett/oe-module-api-explorer)
is maintained by Jerry Padgett. OpenEMR CI depending on it is deliberately a
cross-repo arrangement — the pin + drift-check are the mitigation for the
usual "someone else's merge breaks our CI" failure mode.

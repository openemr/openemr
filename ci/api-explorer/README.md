# ci/api-explorer

End-to-end smoke that exercises OpenEMR's OAuth2 + FHIR surface from a real
third-party client (`sjpadgett/oe-module-api-explorer`). Runs on every PR; its
job is to catch regressions in the OAuth2 code path that in-tree unit and
isolated tests cannot see — real RFC 7591 DCR, real token endpoint for every
non-SMART grant, real FHIR Patient read from a bearer-authenticated client.

## What the smoke does

1. Boots `docker/development-easy` with `docker compose up --wait`.
2. Clones `sjpadgett/oe-module-api-explorer` at the current `main` HEAD into
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
6. Runs **`driver.php`** (Guzzle, no browser): initializes the explorer's
   session, triggers `client_register.php` to register all four clients
   (confidential auth-code, public PKCE, JWT client-credentials, SMART EHR
   launch) via DCR, then runs the JWT client-credentials grant +
   `GET /fhir/Patient` round-trip server-to-server inside the explorer's PHP.
   Asserts a FHIR `Bundle` with `total >= 1`.
7. Runs **`driver-browser.php`** (Panther/Selenium, browser): drives the
   explorer's confidential authorization-code flow (login + consent click-
   through + token exchange + FHIR fetch) and then the public PKCE flow.
   Confidential: asserts a FHIR `Bundle` with `total >= 1`. Public PKCE: the
   explorer registers its public client with `patient/*` scopes only, so the
   admin login's user-role token hits a 401 on bulk FHIR Patient search by
   design — the smoke accepts that as "OAuth round-trip succeeded, FHIR
   access was scope-limited" and still passes. Any OAuth-protocol regression
   (DCR, authorize, PKCE verifier, token exchange) rises as a hard failure.

Total runtime ~2-3 min on top of the stack boot. The smoke exercises paths
unit tests cannot reach (RFC 7591 DCR, JWT assertion generation + validation,
system-scope FHIR reads, authorize + consent UI, PKCE verifier at the token
endpoint) from a client the server has no awareness of until registration
time, which is as close to "real external app" as you can get in CI.

## What the smoke does **not** do

- No SMART-launch coverage. The sibling smoke (`oe-smart-questionnaire`,
  acceptance-side) owns that path.
- No conformance surface. If the explorer added bulk `$export` or FHIR write
  coverage to this smoke, it would still be a smoke, not Inferno.

## Upstream tracking

The smoke clones `main` HEAD on every run — no SHA pin, no drift-check
infrastructure. Trade-off: upstream merges can break OpenEMR CI without
warning, but the module and OpenEMR are maintained by overlapping
contributors so divergence is typically caught and discussed in-channel
before it lands. If a breaking upstream merge does break the smoke, the
fix is a conversation with the module maintainer plus (if needed) a
workaround in `run.sh` / `driver*.php` — not a scramble.

## Reproducing locally

```bash
openemr-cmd worktree add my-smoke -b --start --env easy
ci/api-explorer/run.sh
```

Use the full `easy` env to match what CI boots (`docker/development-easy`
is the `easy` stack); keeping local and CI aligned means a local pass
reproduces the same shape CI runs. The browser driver needs Selenium, which
`easy` composes in; `easy-light` drops it and the browser driver will fail
to connect.

On a failed browser flow, `driver-browser.php` writes a screenshot and page
source to `tmp/driver-browser-<client>.{png,html}`. The CI workflow uploads
those as the `api-explorer-driver-browser-postmortem` artifact.

## Files

| File | Purpose |
|------|---------|
| `run.sh` | Entry point: clone the explorer, seed demo data + globals, invoke both drivers. |
| `driver.php` | Guzzle round-trip: DCR + JWT client-credentials FHIR Patient read. |
| `driver-browser.php` | Panther/Selenium round-trip: confidential auth-code + public PKCE flows. |
| `seed-globals.sql` | REPLACE INTO globals rows the explorer requires. Keys backed by `src/Services/Globals/GlobalConnectorsEnum.php`. |

## Credits

[`sjpadgett/oe-module-api-explorer`](https://github.com/sjpadgett/oe-module-api-explorer)
is maintained by Jerry Padgett. OpenEMR CI depending on it is deliberately a
cross-repo arrangement — see "Upstream tracking" above for how the smoke
handles divergence.

# ci/api-explorer

End-to-end smoke that exercises OpenEMR's OAuth2 + FHIR surface from a real
third-party client (`openemr/oe-module-api-explorer`). Runs on every PR; its
job is to catch regressions in the OAuth2 code path that in-tree unit and
isolated tests cannot see — real RFC 7591 DCR, real token endpoint for the
`client_credentials`, `authorization_code` (confidential and public-with-PKCE),
and `password` grants, real FHIR Patient read from a bearer-authenticated
client, a real FHIR write round-trip (`CareTeam` POST + PUT + GET) with the
password-grant bearer, and a real portal-API read (`GET /portal/patient`)
with a patient-role bearer against a seeded portal patient fixture.
Refresh-token and SMART launch are not covered here; refresh-token has no
dedicated flow in this smoke, and SMART launch is covered by the sibling
`oe-smart-questionnaire` smoke.

## What the smoke does

1. Boots `docker/development-easy` with `docker compose up --wait`.
2. Clones `openemr/oe-module-api-explorer` at the current `main` HEAD into
   `devtools/oe-module-api-explorer/` (the explorer loads
   `../../interface/globals.php` and must sit two levels below the OpenEMR
   root; it is **not** an `interface/modules/custom_modules/` module).
3. Writes a CI-only `config.local.php` pointing the explorer at a single site
   named `docker` at `https://openemr`.
4. Seeds demo data via `openemr-cmd drid` so the FHIR Patient endpoint has
   something to return.
5. Seeds the globals the explorer requires: `site_addr_oath`, `rest_api`,
   `rest_fhir_api`, `rest_system_scopes_api`, `rest_portal_api`,
   `oauth_password_grant`. (Must run **after** `drid` — drid rewrites
   `site_addr_oath`.)
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
8. Runs **`driver-password.php`** (Guzzle, no browser): registers a one-shot
   confidential client via DCR, flips its stored `grant_types` to `password`
   + `is_enabled=1` (DCR drops the password grant because it is admin-only;
   see `ClientGrantTypePolicy::ADMIN_ONLY`), posts admin credentials to
   `/oauth2/default/token` with `grant_type=password` + `user_role=users`,
   and reads `/apis/default/fhir/Patient` with the resulting bearer. Asserts
   a FHIR `Bundle` with `total >= 1`. The explorer's four shipped clients
   (confidential auth-code, public PKCE, JWT client-credentials, SMART EHR
   launch) do not include a password-grant client, so this driver drives
   the token endpoint directly rather than through the explorer's UI; the
   explorer's own Scope Lab panel reaches the same contract via per-session
   CSRF + a profile-shaped payload, which is more scope-matrix plumbing
   than the password grant itself. The in-tree `PasswordGrantPatientRole`
   api test uses the same DCR + DB-flip + token pattern.
9. Runs **`driver-fhir-writes.php`** (Guzzle, no browser): reuses the
   same DCR + DB-flip + password-grant lane as `driver-password.php`, but
   requests `user/CareTeam.write` in the DCR scope and drives a full
   write round-trip against `/apis/default/fhir/CareTeam` — POST a
   minimal fixture (subject = a `Patient` uuid discovered via
   `GET /fhir/Patient?_count=1`, participant = the admin user's uuid as
   `Practitioner`), PUT the created resource with a mutated name,
   GET it back and assert the mutation persisted. Closes a §8.2 external-
   realism gap: all four other grant lanes exercise read-only FHIR, and
   Inferno's ONC G10 US Core 3.1.1 suite is read-only too, so a scope-
   filtering or write-path bearer-validation regression that only shows
   up on writes was previously only catchable by the in-tree api-tier
   tests, not from a real external client.
10. Runs **`driver-portal.php`** (Guzzle, no browser, but with OE
    bootstrap for fixture setup): installs a portal-enabled patient
    fixture via `PortalPatientFixtureManager::installPortalPatient`
    (demo-data patients from `drid` do NOT have portal credentials),
    DCRs a confidential client with `openid api:port
    patient/patient.read patient/encounter.read offline_access`, flips
    `grant_types=password` + `is_enabled=1` the same way the other
    password-grant lanes do, posts the fixture credentials with
    `user_role=patient`, and reads `/apis/default/portal/patient` with
    the resulting bearer. Asserts a 200 + non-empty JSON array whose
    body identifies the fixture by email. Closes the §8.2 Portal API
    external-realism gap: the other four lanes all target
    `/apis/default/fhir/*` (user-role tokens, FHIR surface); nothing
    in CI exercises `/apis/default/portal/*` (patient-role tokens,
    portal surface) from a real third-party client. Note: this driver
    diverges from the three siblings by requiring OE bootstrap
    (`$ignoreAuth=true; require interface/globals.php`) because
    `PortalPatientFixtureManager` uses `AuthHash::passwordHash` +
    `QueryUtils` that are awkward to replicate as raw SQL. The driver
    cleans up its fixture rows + its own `oauth_clients` row in a
    finally block.

Total runtime ~4 min on top of the stack boot. The smoke exercises paths
unit tests cannot reach (RFC 7591 DCR, JWT assertion generation + validation,
system-scope FHIR reads, authorize + consent UI, PKCE verifier at the token
endpoint, password-grant token issuance against user_role=users, FHIR
write-path POST / PUT / GET with persistence verification, portal-API read
with a patient-role bearer) from a client the server has no awareness of
until registration time, which is as close to "real external app" as you
can get in CI.

## What the smoke does **not** do

- No SMART-launch coverage. The sibling smoke (`oe-smart-questionnaire`,
  acceptance-side) owns that path.
- No conformance surface. The FHIR write lane in `driver-fhir-writes.php`
  is a round-trip, not a conformance sweep — it exercises one resource
  (CareTeam) at POST + PUT + GET to catch write-path OAuth regressions
  from an external client, not every write route.

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
| `run.sh` | Entry point: clone the explorer, seed demo data + globals, invoke all five drivers. |
| `driver.php` | Guzzle round-trip: DCR + JWT client-credentials FHIR Patient read. |
| `driver-browser.php` | Panther/Selenium round-trip: confidential auth-code + public PKCE flows. |
| `driver-password.php` | Guzzle round-trip: DCR + DB-flip + password-grant token + FHIR Patient read. |
| `driver-fhir-writes.php` | Guzzle round-trip: DCR + DB-flip + password-grant token + FHIR CareTeam POST + PUT + GET persistence read-back. |
| `driver-portal.php` | Guzzle round-trip + OE bootstrap: portal patient fixture + DCR + DB-flip + password-grant (user_role=patient) + `/apis/default/portal/patient` read. |
| `seed-globals.sql` | REPLACE INTO globals rows the smoke requires. Keys backed by `src/Services/Globals/GlobalConnectorsEnum.php`. |

## Credits

[`openemr/oe-module-api-explorer`](https://github.com/openemr/oe-module-api-explorer)
is maintained by Jerry Padgett. OpenEMR CI depending on it is deliberately a
cross-repo arrangement — see "Upstream tracking" above for how the smoke
handles divergence.

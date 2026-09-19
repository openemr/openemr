# OpenID Connect development stack

Dedicated easy-dev environment plus a **local Keycloak** used to exercise staff
SSO. Ports are offset from `docker/development-easy/` (8300/9300) so this stack
can run at the same time.

This is not the clinicflow Keycloak realm. The issuer is
`http://127.0.0.1:8480/realms/openemr` so the authorization redirect is
cross-site from OpenEMR on `http://localhost:8400`.

## Start

```bash
cd docker/development-oidc
HOST_UID="$(id -u)" HOST_GID="$(id -g)" docker compose up --detach --wait
```

| Service | URL |
| --- | --- |
| OpenEMR | http://localhost:8400/ (HTTPS is also on 9400; this stack uses HTTP for SSO cookies) |
| Keycloak account / login | http://127.0.0.1:8480/realms/openemr/account (loopback only) |
| Keycloak admin console | http://127.0.0.1:8480/admin/ (`admin` / `admin`) |
| phpMyAdmin | http://127.0.0.1:8410/ |

OpenEMR login is `admin` / `pass`. The same username and password exist in
Keycloak so SSO matches the seeded OpenEMR administrator.

The OpenEMR container reaches Keycloak at `http://127.0.0.1:8480` through a
loopback proxy (`keycloak-loopback`) so the issuer URL matches the browser.

### Why 8400 and 8480 are pinned

`keycloak/openemr-realm.json` registers `http://localhost:8400/...` as the
client's redirect URI and web origin, and Keycloak's realm import does not
substitute environment variables — a templated port is imported literally and
rejected as an invalid redirect URI. So `site_addr_oath` and the issuer are
fixed at 8400/8480 rather than derived from `WT_HTTP_PORT` / `WT_KEYCLOAK_PORT`.
Those variables still offset the ports published to the host, which keeps two
worktrees from colliding; the SSO flow does not use them. Running this stack on
a non-default `WT_HTTP_PORT` therefore leaves SSO reachable from the acceptance
tests but not from a host browser — change the realm's redirect URIs too if you
need that.

Chrome runs inside the `selenium` container, where `localhost` is Selenium
itself, so two more loopbacks (`selenium-app-loopback`, `selenium-idp-loopback`)
publish OpenEMR on `localhost:8400` and Keycloak on `127.0.0.1:8480` inside the
browser's network namespace. Without them the authorization redirect and the
callback both resolve to the browser's own container and the SSO acceptance test
cannot complete the round trip.

## Groups, roles, and creating users

OpenEMR keeps access control. Keycloak is only identity.

1. Create the person in **Administration → Users** with the same username as
   Keycloak `preferred_username` (the docker realm ships `admin`, `clinician`,
   and `physician`).
2. Assign the OpenEMR **ACL group** (Clinicians, Physicians, Front Office, …)
   and the auth **group** (usually Default) in OpenEMR, the same way as for a
   local password user.
3. Sign in with Keycloak. OpenEMR links the IdP subject to that user.

Optional: enable **OIDC Create Missing Users** in Globals. New users land in
the configured OpenEMR ACL and group (defaults: Clinicians + Default).
Administrators and Emergency Login cannot be assigned that way.

There is no Keycloak-group-to-ACL mapping. Change permissions in OpenEMR.

Local username/password remains available at
`https://localhost:9400/interface/login/login.php?local=1`.

Composer GitHub tokens are optional environment variables (`GITHUB_COMPOSER_TOKEN`,
`GITHUB_COMPOSER_TOKEN_ENCODED`, and `GITHUB_COMPOSER_TOKEN_ENCODED_ALTERNATE`).
Supply your own token only when needed for dependency download rate limits.

## Browser regression check

`tests/Acceptance/OidcKeycloakAcceptanceTest.php` exercises the real authorization
redirect and login, then checks that the authenticated session uses `SameSite=Strict`.
The login handshake temporarily uses `SameSite=Lax` so a top-level GET callback
from an identity provider on a different site can carry the state/nonce/PKCE session.
Successful login rotates the session ID; failed login restores Strict when the
local login page displays the error.

Run against a disposable installation with SSO auto-redirect enabled and an
existing local account matching the Keycloak account. Set `OIDC_KEYCLOAK_TESTS=1`,
`ACCEPTANCE_ARTIFACT_URL` and the usual Selenium settings. The browser and OpenEMR
must both reach the exact configured issuer URL. `OIDC_TEST_USERNAME` and
`OIDC_TEST_PASSWORD` default to the development realm's `admin` / `pass`.

```bash
OIDC_KEYCLOAK_TESTS=1 vendor/bin/phpunit \
  -c tests/Acceptance/phpunit.acceptance.xml \
  --filter OidcKeycloakAcceptanceTest
```

The account for this test must not have local MFA enrolled. Test local TOTP/U2F
separately with enrolled test accounts; they must remain blocked from other
OpenEMR pages until the local challenge succeeds.

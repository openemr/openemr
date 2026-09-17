# OpenID Connect development stack

Dedicated easy-dev environment plus a **local Keycloak** used to exercise staff
SSO. Ports are offset from `docker/development-easy/` (8300/9300) so this stack
can run at the same time.

This is not the clinicflow Keycloak realm. The issuer is
`http://localhost:8480/realms/openemr`.

## Start

```bash
cd docker/development-oidc
HOST_UID="$(id -u)" HOST_GID="$(id -g)" docker compose up --detach --wait
```

| Service | URL |
| --- | --- |
| OpenEMR | http://localhost:8400/ (HTTPS is also on 9400; this stack uses HTTP for SSO cookies) |
| Keycloak account / login | http://localhost:8480/realms/openemr/account |
| Keycloak admin console | http://localhost:8480/admin/ (`admin` / `admin`) |
| phpMyAdmin | http://localhost:8410/ |

OpenEMR login is `admin` / `pass`. The same username and password exist in
Keycloak so SSO matches the seeded OpenEMR administrator.

The OpenEMR container reaches Keycloak at `http://localhost:8480` through a
loopback proxy (`keycloak-loopback`) so the issuer URL matches the browser.

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

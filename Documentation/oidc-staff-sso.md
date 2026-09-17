# OpenID Connect staff login

Staff can sign in through any OpenID Connect provider that supports the
authorization-code flow and PKCE (Keycloak, Authentik, Zitadel, and similar).
This is separate from Google Sign-In and LDAP.

OpenEMR remains the source of groups and permissions. The identity provider
proves who the person is. phpGACL groups (Clinicians, Physicians, Front Office,
Accounting, Administrators, Emergency Login) and the `groups` table (often
`Default`) stay in OpenEMR.

## Enable

1. Register a confidential or public client at the provider.
2. Set the redirect URI to `{site_addr_oath}{webroot}/interface/login/oidc_callback.php`.
3. In **Administration → Globals → Security**, set:

   | Setting | Typical value |
   | --- | --- |
   | Enable OpenID Connect Staff Login | yes |
   | OIDC Issuer URL | `https://keycloak.example.com/realms/openemr` |
   | OIDC Client ID | the client id |
   | OIDC Client Secret | for confidential clients; leave empty for PKCE-only public clients |
   | OIDC Username Claim | `preferred_username` |
   | OIDC Default Login | yes to send the login page straight to the provider |

Append `?local=1` to the login URL to use the OpenEMR username and password form.

## Creating users (no IdP role mapping)

The easiest path is the same as any other OpenEMR user:

1. Create the user in **Administration → Users**.
2. Use the same username the provider puts in `preferred_username` (or the
   claim configured as **OIDC Username Claim**).
3. Assign ACL and the auth group in OpenEMR.

On first SSO, OpenEMR matches that username (or a unique email if **OIDC Match
Users by Email** is on) and stores the provider `iss` + `sub` in
`oidc_external_identity`. Later logins use that binding. Existing ACL is
unchanged.

Optional **OIDC Create Missing Users** inserts a local user into:

- **OIDC New User Access Control Group** (phpGACL title, default Clinicians)
- **OIDC New User Auth Group** (`groups.name`, default Default)

Administrators and Emergency Login cannot be granted through that setting.
Promote a user in **Administration → ACL** after they exist.

Provider groups or realm roles are not imported.

## Provider notes

- **Keycloak:** client protocol OpenID Connect, standard flow, PKCE S256,
  redirect URI as above. A disposable local realm is in
  `docker/development-oidc/`.
- **Authentik:** create an OAuth2/OpenID provider and application; issuer is
  the OpenID well-known prefix without `/.well-known/openid-configuration`.
- **Zitadel:** use the instance or project issuer URL and an OIDC web
  application.

The issuer, authorization, token, JWKS, and end-session URLs must share the
issuer host. HTTP issuers are off unless **OIDC Allow HTTP Issuer
(Development)** is enabled.

A development Keycloak stack (separate ports from easy-dev) is documented in
`docker/development-oidc/README.md`. The sample issuer is
`http://localhost:8480/realms/openemr`.

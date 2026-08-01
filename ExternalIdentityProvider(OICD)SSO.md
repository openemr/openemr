# External Identity Provider (OIDC) SSO

This document explains how to configure and use the OpenEMR External Identity
Provider module for OpenID Connect (OIDC) single sign-on.

It is written for:

- OpenEMR administrators
- technical implementers
- support staff who need to configure Keycloak or another OIDC provider

## Purpose

The module allows OpenEMR to delegate authentication to an external identity
provider such as Keycloak.

In the working flow:

1. the user clicks the external login button on the OpenEMR login page
2. the user authenticates with the identity provider
3. OpenEMR receives the OIDC callback
4. OpenEMR logs the user into a local OpenEMR account

The local OpenEMR account can be:

- a manually bound existing user
- an automatically matched existing user
- an automatically provisioned shadow user

## Supported behavior

The current implementation supports:

- OIDC discovery
- authorization code flow with PKCE
- login through Keycloak and similar OIDC providers
- manual subject-to-user binding
- optional auto-bind of an existing local OpenEMR user
- optional auto-provisioning of a local shadow user
- admin access from:
  - `Admin -> External Identity Provider`

## Important concepts

### External identity

The stable identity key is the OIDC `sub` claim.

This is the value OpenEMR uses for the long-term external binding.

### Local OpenEMR user

OpenEMR still requires a local user account for authorization and session
creation.

External authentication does not remove the need for a local OpenEMR user.

### Shadow user

A shadow user is a local OpenEMR user created automatically after successful
OIDC authentication.

This is useful when you want users to log in through Keycloak without manually
creating and binding every OpenEMR user in advance.

## Prerequisites

Before configuration, make sure you have:

- a working OpenEMR instance
- administrator access to OpenEMR
- the External Identity Provider module installed and enabled
- an OIDC provider, such as Keycloak
- the issuer URL
- the client ID
- the client secret
- a redirect URI that the user browser can reach

## Where to configure it

After the module is enabled, open:

- `Admin -> External Identity Provider`

In a local development environment, the page is typically reachable at:

- `http://localhost:8300/interface/modules/custom_modules/oe-module-external-idp/moduleConfigShell.php`

## Fields on the OpenEMR configuration page

### Provider configuration

- Provider display name
  - the label shown to users on the login page

- Issuer URL
  - the exact OIDC issuer URL

- Callback URL
  - read-only
  - this must be copied into the identity provider client configuration

- Client ID
  - the OIDC client ID

- Client secret
  - the OIDC client secret
  - after save, this field is blank by design
  - blank after save does not mean the secret was lost

- Scopes
  - default:
    - `openid profile email`

- Enable sign-in with this provider
  - enables the login option on the OpenEMR login screen

### Shadow-user provisioning

- Provisioning mode
  - `Manual binding only`
  - `Auto-bind existing local user`
  - `Auto-provision shadow user`
  - `Auto-bind or auto-provision`

- Match claim
  - claim used to find an existing OpenEMR user
  - common value:
    - `preferred_username`
  - another possible value:
    - `email`

- Username claim
  - claim used when generating a local shadow username

- Email claim
  - usually:
    - `email`

- First name claim
  - usually:
    - `given_name`

- Last name claim
  - usually:
    - `family_name`

- Local group name
  - OpenEMR local group membership

- ACL group
  - OpenEMR access control group

- Username prefix
  - prefix used for shadow users
  - common value:
    - `oidc_`

- Default facility
  - optional

- Default authorized flag
  - whether provisioned users are marked authorized

- Provisioned users are active by default

- Sync name/email claims on each login

## Provisioning modes

### 1. Manual binding only

Use this when:

- you want maximum control
- each external subject should be explicitly mapped by an administrator

Behavior:

- no automatic matching
- no automatic user creation

### 2. Auto-bind existing local user

Use this when:

- OpenEMR users already exist
- the identity provider claim exactly matches local usernames or emails

Behavior:

- if no subject binding exists, OpenEMR searches for one active local user
- if exactly one match is found, it creates the binding automatically

### 3. Auto-provision shadow user

Use this when:

- you want OpenEMR to create local users on first successful external login

Behavior:

- if no binding exists, OpenEMR creates a local user
- OpenEMR stores the subject binding
- later logins reuse that same local user

### 4. Auto-bind or auto-provision

Use this when:

- you want OpenEMR to reuse existing local users when possible
- but create a shadow user if no match is found

## Keycloak configuration

This section uses a concrete example.

Example:

- OpenEMR URL:
  - `http://localhost:8300`
- Keycloak realm:
  - `ai_gateway`
- Keycloak client ID:
  - `openemr-client`
- OpenEMR callback URL:
  - `http://localhost:8300/interface/modules/custom_modules/oe-module-external-idp/callback.php`

### Keycloak client settings

In Keycloak, configure the client like this:

- Client type:
  - OpenID Connect

- Client ID:
  - `openemr-client`

- Client authentication:
  - `On`

- Standard flow:
  - `On`

- Direct access grants:
  - usually `Off`

- Root URL:
  - `http://localhost:8300`

- Valid redirect URIs:
  - `http://localhost:8300/interface/modules/custom_modules/oe-module-external-idp/callback.php`

- Web origins:
  - `http://localhost:8300`

- Admin URL:
  - `http://localhost:8300`

Then go to:

- `Credentials`

Copy the client secret and paste it into OpenEMR.

## Mapping Keycloak values into OpenEMR

For a Keycloak realm such as:

- base URL:
  - `http://localhost:8002`
- realm ID:
  - `ai_gateway`

the OpenEMR values are:

| OpenEMR field | Value |
| --- | --- |
| Provider display name | `Keycloak SSO` |
| Issuer URL | `http://host.docker.internal:8002/realms/ai_gateway` or the reachable issuer used by the OpenEMR container |
| Client ID | `openemr-client` |
| Client secret | the client secret from Keycloak Credentials tab |
| Scopes | `openid profile email` |

Important distinction:

- browser-facing redirect URI uses the OpenEMR URL the browser sees
  - example:
    - `http://localhost:8300/.../callback.php`

- server-to-server discovery and token endpoint access must use a host reachable
  from the OpenEMR container
  - in Docker-based local testing this may be:
    - `http://host.docker.internal:8002/...`

## Recommended initial configuration

For a first working rollout, use:

- Provisioning mode:
  - `Auto-provision shadow user`

- Match claim:
  - `preferred_username`

- Username claim:
  - `preferred_username`

- Email claim:
  - `email`

- First name claim:
  - `given_name`

- Last name claim:
  - `family_name`

- Username prefix:
  - `oidc_`

- Local group name:
  - use an existing OpenEMR group name

- ACL group:
  - choose a valid ACL group from the dropdown

- Default authorized flag:
  - choose based on your operational policy

## Step-by-step setup in OpenEMR

### 1. Enable the module

Enable:

- `External Identity Provider (OIDC) SSO`

### 2. Open the configuration page

Go to:

- `Admin -> External Identity Provider`

### 3. Enter provider values

Complete:

- Provider display name
- Issuer URL
- Client ID
- Client secret
- Scopes

### 4. Configure provisioning mode

Choose the desired provisioning mode.

If you choose either:

- `Auto-provision shadow user`
- `Auto-bind or auto-provision`

then you must also complete:

- Local group name
- ACL group

### 5. Test discovery

Click:

- `Test discovery`

Expected result:

- success message on the same page

### 6. Save the configuration

Click:

- `Validate discovery and save`

Expected result:

- success message on the same page

### 7. Confirm the provider is enabled

Make sure:

- `Enable sign-in with this provider` is checked

### 8. Test the login

1. open the OpenEMR login page
2. click the external login button
3. authenticate in Keycloak
4. confirm that OpenEMR logs you in

## How to test shadow-user provisioning

Recommended test order:

### Test 1: existing user logs in again

1. log out
2. log back in with the same Keycloak user
3. confirm the same OpenEMR user is reused

Expected result:

- no duplicate user is created
- the same binding is reused

### Test 2: new Keycloak user

1. create a new normal Keycloak user in the configured realm
2. log in through the OpenEMR external login button
3. confirm OpenEMR creates a new local shadow user

Expected result:

- a new local OpenEMR user appears
- the binding appears in `Current bindings`

## How to verify the result

After a successful login, check:

- Provider status
  - `Last success`

- Current bindings
  - verify the `sub` is mapped to the correct local user

- OpenEMR user list
  - verify the user exists if auto-provisioning was used

## Troubleshooting

### 1. The login button does not appear

Check:

- the module is enabled
- the provider is enabled
- the configuration saved successfully

### 2. Discovery fails

Common causes:

- incorrect issuer URL
- the OpenEMR container cannot reach the identity provider
- invalid discovery metadata
- TLS or local network issues

### 3. Login returns to the OpenEMR login page

Common causes:

- token exchange failure
- no local binding and provisioning disabled
- local user validation failure

Check:

- `Last failure` on the configuration page
- OpenEMR container logs

### 4. Keycloak says `Client not found`

The OpenEMR `Client ID` does not match the actual Keycloak client ID.

### 5. Keycloak says `Invalid parameter: redirect_uri`

The redirect URI in the Keycloak client does not exactly match the OpenEMR
callback URL.

### 6. Keycloak says `Invalid client or Invalid client credentials`

Check:

- client secret is correct
- client authentication is enabled
- OpenEMR configuration was saved successfully

### 7. Provisioning mode appears to revert to manual

If save fails, the page now preserves submitted values.

Most common reason for this symptom:

- `Auto-provision shadow user` or `Auto-bind or auto-provision` was selected
- but `Local group name` or `ACL group` was left blank

### 8. The client secret field is blank after save

This is expected.

The screen intentionally does not redisplay the saved secret.

## OpenEMR log collection

In Docker-based development, collect recent OpenEMR logs with:

```bash
docker logs development-easy-openemr-1 2>&1 | tail -n 120
```

This is the first place to look when:

- discovery fails
- callback fails
- login returns to the login page

## Operational recommendations

- keep one local OpenEMR admin account available
- test with one pilot user first
- use exact claim matching only
- document the chosen provisioning mode
- document the callback URL together with the Keycloak client settings
- treat the client secret as sensitive

## Limitations and current design notes

- OpenEMR authorization is still local
- external login does not automatically make a user an administrator
- shadow users depend on local group and ACL assignment
- the current implementation stores a generated hidden local password hash for
  shadow users to satisfy the existing OpenEMR session/login flow

## Summary

For most implementations, the working recipe is:

1. configure the Keycloak client correctly
2. copy issuer URL, client ID, and client secret into OpenEMR
3. save and enable the provider
4. choose a provisioning mode
5. test login with one Keycloak user
6. verify the local OpenEMR binding or shadow-user creation

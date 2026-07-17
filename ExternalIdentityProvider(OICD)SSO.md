# External Identity Provider (OIDC) SSO

This document explains how to install, configure, and test the OpenEMR
External Identity Provider module for single sign-on using OpenID Connect
(OIDC).

The module lets OpenEMR act as an OIDC client. In practical terms, your users
sign in with an external identity provider such as Keycloak, and OpenEMR uses
that identity to complete the login.

## What this module does

- Adds an external login button to the OpenEMR login screen when enabled.
- Supports OIDC discovery from an HTTPS issuer URL.
- Uses the authorization code flow with PKCE.
- Lets an administrator bind an external OIDC user ID to an OpenEMR user.
- Stores provider status and login history in the module configuration screen.

## What this module does not do

- It does not replace your existing local OpenEMR users.
- It does not automatically create OpenEMR users from the identity provider.
- It does not perform single logout with the external provider.
- It does not make every user an administrator.

## Prerequisites

Before you configure the module, make sure you have:

- An installed and running OpenEMR instance.
- Access to the Module Manager as an administrator.
- An OIDC-compatible identity provider, such as Keycloak.
- The exact issuer URL for the provider.
- A client ID and client secret from the identity provider.
- An HTTPS endpoint that OpenEMR and the identity provider can both reach.

## How to find the module in OpenEMR

The module appears in OpenEMR as:

`External Identity Provider (OIDC) SSO`

In the Module Manager, enable it first. After that, open the configuration
screen from the module row.

After the module is enabled, a shortcut is also added under the OpenEMR
`Admin` menu:

- `Admin -> External Identity Provider`

That menu item opens the same configuration page.

If you are using the development Docker compose setup, the configuration page
can also be opened directly at:

`http://localhost:8300/interface/modules/custom_modules/oe-module-external-idp/moduleConfig.php`

If your environment serves OpenEMR over HTTPS, use the HTTPS version instead:

`https://localhost:9300/interface/modules/custom_modules/oe-module-external-idp/moduleConfig.php`

## Required OpenEMR permissions

The configuration page is restricted to users with administrator access
(`admin/users`). If you open the page and see a 403 or `Not authorized`, log
in as an admin user.

## Configuration steps

### 1. Open the module configuration screen

After enabling the module, open the configuration page from Module Manager or
use the direct URL above.

### 2. Enter the provider details

Fill in the following fields:

- Provider display name
  - The text shown on the OpenEMR login button.
  - Example: `Keycloak SSO`

- Issuer URL
  - The exact OIDC issuer URL.
  - Example:
    `https://keycloak.example.com/realms/clinic`

- Client ID
  - The client ID created in the identity provider.

- Client secret
  - The secret created with the OIDC client.
  - If you are updating settings and want to keep the existing secret, leave
    this blank.

- Scopes
  - Keep the default unless your identity provider requires more or less.
  - The default is:
    `openid profile email`

### 3. Test discovery

Use the `Test discovery` button first.

This checks whether OpenEMR can reach the issuer URL and fetch the OIDC
metadata.

The result is shown as an inline message on the same page. The browser should
not open a new blank tab or lose the configuration screen.

If discovery fails, verify:

- the issuer URL is exact,
- the URL uses HTTPS,
- the server is reachable from the OpenEMR container,
- the identity provider is healthy,
- TLS certificates are valid or trusted.

### 4. Save the configuration

Click `Validate discovery and save`.

When saved successfully:

- the provider configuration is stored,
- discovery metadata is cached,
- the provider may be enabled if the checkbox is selected.

### 5. Enable sign-in

Make sure `Enable sign-in with this provider` is checked.

If the provider is saved but disabled, users will not see the external login
button.

### 6. Configure the redirect URI in your identity provider

Copy the callback URL shown on the configuration page into the OIDC client
settings in your identity provider.

This URL is the redirect target after the user authenticates at the provider.

### 7. Bind external users to OpenEMR users

After the provider is configured, use the identity binding section to map an
external subject to a local OpenEMR user.

This is the step that makes the external login resolve to the correct OpenEMR
account.

Recommended practice:

- keep one local administrator login available,
- bind and test one account before rolling out to all users,
- document which external subject belongs to which OpenEMR account.

## Testing the login flow

After configuration, test the full path:

1. Log out of OpenEMR.
2. Return to the login screen.
3. Confirm the external login button appears.
4. Click the external login button.
5. Complete authentication in the identity provider.
6. Confirm OpenEMR returns you to the application as the expected user.

If the login succeeds, the module records the last successful login and the
user it was mapped to.

## Using Keycloak as the IDP

The recommended pattern is one Keycloak realm per environment or trust domain,
and one confidential client for OpenEMR inside that realm.

Concrete example:

- Keycloak base URL: `https://keycloak.example.com`
- Realm ID: `clinic`
- Issuer URL: `https://keycloak.example.com/realms/clinic`
- OpenEMR client ID: `openemr`
- OpenEMR client secret: the secret generated for that `openemr` client

In this example:

- the realm identifies the Keycloak tenant;
- the client ID identifies the OpenEMR application within that tenant;
- the client secret authenticates the OpenEMR client during token exchange.

### 1. Create the realm

Create or select the realm that will issue identities for OpenEMR.

If the Keycloak URL is:

`https://keycloak.example.com/realms/clinic`

then the realm ID is:

`clinic`

OpenEMR uses the full issuer URL, not just the realm name.

Enter this exact value into OpenEMR:

`https://keycloak.example.com/realms/clinic`

### 2. Create the OpenEMR client

Create a new Keycloak client for OpenEMR and configure it as an OIDC
application.

Recommended baseline settings:

- Client type: `OpenID Connect`
- Client authentication: `On` or `confidential`
- Standard flow: `On`
- Direct access grants: `Off` unless you have a documented reason to enable it
- Service accounts: `Off`
- Root URL: optional
- Home URL: optional

The client name can be anything stable and readable. A common choice is:

`openemr`

That name becomes the Keycloak client ID and is the value you enter into
OpenEMR.

### 3. Configure the redirect URI

OpenEMR shows the callback URL on the provider configuration page. Copy that
value into Keycloak.

For the current module, the callback URL is typically:

`http://localhost:8300/interface/modules/custom_modules/oe-module-external-idp/callback.php`

If you are serving OpenEMR over HTTPS, use the HTTPS variant of the same
path.

In Keycloak, configure:

- Valid redirect URIs: the exact callback URL, or a controlled pattern if your
  environment requires it
- Web origins: the OpenEMR origin if Keycloak requires CORS/origin
  restrictions

Example origin for local development:

`http://localhost:8300`

### 4. Retrieve and map the client credentials

Keycloak generates a client secret only for confidential clients.

Record the following values from the Keycloak client page:

| Keycloak value | Meaning | OpenEMR field |
| --- | --- | --- |
| Realm ID: `clinic` | The Keycloak tenant name | Not entered directly; it is part of the issuer URL |
| Issuer URL: `https://keycloak.example.com/realms/clinic` | OIDC discovery and token issuer | `Issuer URL` |
| Client ID: `openemr` | The application identifier inside the realm | `Client ID` |
| Client secret: generated secret | Secret used for token exchange | `Client secret` |

Important:

- Do not enter the realm ID into the `Client ID` field.
- Do not enter the client ID into the `Issuer URL` field.
- Do not enter the client secret anywhere except the `Client secret` field.

### 5. Set scopes

The module expects standard OIDC scopes and requires `openid`.

The default OpenEMR value is:

`openid profile email`

That is the correct starting point for Keycloak in most deployments.

If you remove `openid`, discovery and login will fail.

### 6. Save the values in OpenEMR

On the External Identity Provider page, populate these fields:

| OpenEMR field | Example value |
| --- | --- |
| Provider display name | `Keycloak SSO` |
| Issuer URL | `https://keycloak.example.com/realms/clinic` |
| Client ID | `openemr` |
| Client secret | the generated Keycloak secret |
| Scopes | `openid profile email` |

Then click:

1. `Test discovery`
2. `Validate discovery and save`
3. Enable the provider

If validation succeeds, OpenEMR keeps you on the same configuration page and
shows a success banner. If validation fails, the error message is shown in the
same banner area so you can correct the values without leaving the page.

### 7. Validate the integration

After the save succeeds:

1. Confirm the provider is enabled.
2. Log out of OpenEMR.
3. Return to the login page.
4. Verify the external login button is visible.
5. Authenticate through Keycloak.
6. Bind the returned external subject to the appropriate OpenEMR user.

For production rollout, test one pilot user before enabling the flow for a
larger group.

### 6. Update OpenEMR with the Keycloak values

On the OpenEMR External Identity Provider page, fill in the fields like this:

| OpenEMR field | Keycloak value |
| --- | --- |
| Provider display name | Any friendly label, such as `Keycloak SSO` |
| Issuer URL | Realm issuer URL, for example `https://keycloak.example.com/realms/clinic` |
| Client ID | Keycloak client ID, for example `openemr` |
| Client secret | Keycloak client secret generated for that client |
| Scopes | `openid profile email` |
| Enable sign-in with this provider | Checked after validation |

Then click:

1. `Test discovery`
2. `Validate discovery and save`

### 7. Test login

After saving:

1. Enable the provider if it is not already enabled.
2. Log out of OpenEMR.
3. Return to the login page.
4. Use the external login button.
5. Sign in through Keycloak.

If the login succeeds, bind the Keycloak user subject to the correct OpenEMR
user from the identity binding section.

## Troubleshooting

### I do not see the module

Make sure the module is installed and enabled in Module Manager.

The module name should be:

`External Identity Provider (OIDC) SSO`

If it is present but not shown, refresh the page or re-open Module Manager.

### The module page says Not authorized

You are not logged in with enough privileges.

Use an OpenEMR admin account with super-user rights.

### Discovery fails

Common causes:

- wrong issuer URL,
- HTTP instead of HTTPS,
- TLS certificate problems,
- network access blocked between OpenEMR and the provider,
- provider does not expose standard OIDC discovery metadata.

### Login button does not appear

Check that:

- the provider is enabled,
- the configuration was saved successfully,
- the provider record has valid discovery data,
- you are logging out and returning to the normal OpenEMR login page.

### Login returns to OpenEMR but the wrong user is used

Check the identity binding:

- confirm the external subject is mapped to the correct OpenEMR user,
- confirm the local user is active,
- update the binding if the identity provider subject changed.

## Safe rollback

If you need to temporarily disable external sign-in:

1. Open the module configuration page.
2. Uncheck `Enable sign-in with this provider`.
3. Save the configuration.

This keeps the provider settings in place but prevents new external logins.

## Best practices

- Keep one local OpenEMR administrator account available.
- Test with a single pilot user before broad rollout.
- Use HTTPS only for the issuer URL.
- Treat the client secret as sensitive information.
- Document your provider URL, client ID, and redirect URI together.

## Support note

If you are sharing this document with end users, you can shorten it to the
three actions they actually need:

1. Open the module configuration page.
2. Enter the issuer URL, client ID, and client secret.
3. Test discovery, save, enable the provider, and then bind users.

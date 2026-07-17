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

The configuration page is restricted to users with administrator/super-user
access. If you open the page and see a 403 or `Not authorized`, log in as an
admin user.

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

## Keycloak example

If you are using Keycloak, the issuer URL is usually the realm URL.

Example:

`https://keycloak.example.com/realms/clinic`

In Keycloak, make sure the client:

- is configured for OpenID Connect,
- allows the authorization code flow,
- uses the exact redirect URI shown in OpenEMR,
- has the correct client secret,
- includes the required scopes.

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

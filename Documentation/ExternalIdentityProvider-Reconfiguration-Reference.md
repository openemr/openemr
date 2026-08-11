# External Identity Provider Reconfiguration Reference

This document is a rebuild checklist for `Admin -> External Identity Provider`.

It is intended for the case where you remove the existing module data and need
to configure the provider again from scratch.

## Scope

This reference combines:

1. verified module field names and fallback defaults from the module code
2. the current configuration you copied from the running admin page
3. repo-local OIDC example values already used in this checkout

## Screens and module files

- Admin page:
  `Admin -> External Identity Provider`
- Module config page:
  [interface/modules/custom_modules/oe-module-external-idp/moduleConfig.php](/home/banchanattu/projects/openemr/interface/modules/custom_modules/oe-module-external-idp/moduleConfig.php:1)
- Provider table schema:
  [interface/modules/custom_modules/oe-module-external-idp/table.sql](/home/banchanattu/projects/openemr/interface/modules/custom_modules/oe-module-external-idp/table.sql:1)
- Existing OIDC setup guide:
  [ExternalIdentityProvider(OICD)SSO.md](/home/banchanattu/projects/openemr/ExternalIdentityProvider(OICD)SSO.md:1)
- Local OIDC test values:
  [http/auth.http](/home/banchanattu/projects/openemr/http/auth.http:1)

## Current configuration

These values came from the current `Admin -> External Identity Provider` page
you copied from the running system.

| Field | Reference value | Source |
| --- | --- | --- |
| Provider display name | `Keycloak SSO` | copied from current UI |
| Issuer URL | `http://host.docker.internal:8002/realms/ai_gateway` | copied from current UI |
| Callback URL | `https://localhost:9300/interface/modules/custom_modules/oe-module-external-idp/callback.php` | updated to match the current browser-facing OpenEMR URL |
| Client ID | `openemr-client` | copied from current UI |
| Accepted bearer audiences | `ai_gateway_client` | copied from current UI |
| Scopes | `openid profile email` | copied from current UI |
| Provisioning mode | `Auto-provision shadow user` | copied from current UI |
| Match claim | `preferred_username` | copied from current UI |
| Username claim | `preferred_username` | copied from current UI |
| Email claim | `email` | copied from current UI |
| First name claim | `given_name` | copied from current UI |
| Last name claim | `family_name` | copied from current UI |
| Local group name | `Default` | copied from current UI |
| ACL group | `Front Office` | copied from current UI |
| Username prefix | `oidc_` | copied from current UI |
| Default facility | `None` | copied from current UI |
| Default authorized flag | `No` | copied from current UI |
| Provisioned users are active by default | `Yes` | copied from current UI |
| Sync name/email claims on each login | `Yes` | copied from current UI |
| Enable internal scope exchange for external bearer tokens | `No` | copied from current UI |
| Enable sign-in with this provider | `Yes` | copied from current UI |

## Current provider status

These values are operational status only. They do not need to be re-entered,
but they are useful as a before-reset reference.

| Status field | Current value |
| --- | --- |
| Enabled | `Yes` |
| Discovery fetched | `2026-07-23 04:31:19` |
| Last login start | `2026-07-24 21:45:40` |
| Last success | `2026-07-24 21:46:10 — user #6 (oidc_aaa — aaa aaa)` |
| Last failure | `None` |

## Client secret

The admin UI intentionally does not show the saved client secret after save.

For reconfiguration, use one of these:

- preferred: get the active client secret from Keycloak `Credentials`
- repo-local test secret reference:
  [http/auth.http](/home/banchanattu/projects/openemr/http/auth.http:37)

If you rotate the client secret in Keycloak, this document must be updated.

## Module defaults

These are the exact module defaults from the table schema and save logic.

| Field | Module default |
| --- | --- |
| Provider display name | `External Identity Provider` |
| Scopes | `openid profile email` |
| Provisioning mode | `manual` |
| Match claim | `preferred_username` |
| Username claim | `preferred_username` |
| Email claim | `email` |
| First name claim | `given_name` |
| Last name claim | `family_name` |
| Local group name | empty |
| ACL group | empty |
| Username prefix | `oidc_` |
| Default facility | `None` / `0` |
| Default authorized flag | `No` / `0` |
| Provisioned users are active by default | `Yes` / `1` |
| Sync name/email claims on each login | `Yes` / `1` |
| Enable internal scope exchange for external bearer tokens | `No` / `0` |
| Enable sign-in with this provider | `No` / `0` |

## Exact rebuild values for this environment

Use this set to restore the configuration you currently have.

### Provider configuration

| OpenEMR field | Value to enter | Notes |
| --- | --- | --- |
| Provider display name | `Keycloak SSO` | exact current value |
| Issuer URL | `http://host.docker.internal:8002/realms/ai_gateway` | exact current value |
| Callback URL | read-only | copy exactly from UI |
| Client ID | `openemr-client` | exact current value |
| Client secret | confirm in Keycloak | UI will not show it later |
| Accepted bearer audiences | `ai_gateway_client` | exact current value |
| Scopes | `openid profile email` | required to include `openid` |
| Enable sign-in with this provider | `checked` | required for login button |

### Provisioning

| OpenEMR field | Recommended value | Reason |
| --- | --- | --- |
| Provisioning mode | `Auto-provision shadow user` | exact current value |
| Match claim | `preferred_username` | exact current value |
| Username claim | `preferred_username` | exact current value |
| Email claim | `email` | exact current value |
| First name claim | `given_name` | exact current value |
| Last name claim | `family_name` | exact current value |
| Local group name | `Default` | exact current value |
| ACL group | `Front Office` | exact current value |
| Username prefix | `oidc_` | exact current value |
| Default facility | `None` | exact current value |
| Default authorized flag | `No` | exact current value |
| Provisioned users are active by default | `checked` | exact current value |
| Sync name/email claims on each login | `checked` | exact current value |
| Enable internal scope exchange for external bearer tokens | `unchecked` | exact current value |

## Step-by-step rebuild procedure

1. Enable the module `External Identity Provider (OIDC) SSO` if it is not already enabled.
2. Open `Admin -> External Identity Provider`.
3. Enter the provider values from the `Provider configuration` table above.
4. Enter the provisioning values from the `Provisioning` table above.
5. Click `Test discovery`.
6. Confirm discovery succeeds.
7. Click `Validate discovery and save`.
8. Confirm `Enable sign-in with this provider` remains checked.
9. Open the login page and verify the external login button appears.
10. Perform one login test through Keycloak.

## Current bindings

These are the current subject-to-user bindings shown on the page. If you remove
the existing data, these bindings will need to be recreated unless you rely on
auto-provisioning to create new local users again.

| Provider | Issuer | External subject | Local user | Created | Updated |
| --- | --- | --- | --- | --- | --- |
| `Keycloak SSO` | `http://host.docker.internal:8002/realms/ai_gateway` | `3d4a93a8-552f-4097-b0f1-5747a47d58d9` | `oidc_aaa` / `aaa aaa` | `2026-07-19 03:46:09` | `2026-07-19 03:46:09` |
| `Keycloak SSO` | `http://host.docker.internal:8002/realms/ai_gateway` | `8f6c8139-757f-43d7-bec2-29fe4867b2bb` | `oidc_bbb` / `bbb bbb` | `2026-07-18 21:20:23` | `2026-07-18 21:20:23` |

## Values still not recoverable from the page copy

The page copy is enough for almost the full rebuild. The main missing value is:

- the actual current client secret

Capture that from Keycloak before deleting anything if you do not already have
it stored elsewhere.

## Data stored by the module

Provider settings are stored in:

- `module_external_idp_provider`

Subject to local user bindings are stored in:

- `module_external_idp_identity`

Schema reference:

- [interface/modules/custom_modules/oe-module-external-idp/table.sql](/home/banchanattu/projects/openemr/interface/modules/custom_modules/oe-module-external-idp/table.sql:1)

## Notes

- The module requires `openid` to be present in the scopes list.
- The callback URL is generated by OpenEMR and should be copied exactly.
- The issuer URL must be reachable by the OpenEMR runtime, not just by your browser.
- If you use `auto_provision` or `auto_bind_or_provision`, `Local group name`
  and `ACL group` are required.

## Post-reset verification checklist

After rebuilding the provider configuration, verify these items in order.

1. Open `Admin -> External Identity Provider` and confirm every field matches the `Exact rebuild values for this environment` section.
2. Confirm `Enable sign-in with this provider` is checked.
3. Click `Test discovery` and confirm it succeeds.
4. Click `Validate discovery and save` and confirm the save succeeds.
5. Confirm `Provider status -> Enabled` shows `Yes`.
6. Confirm `Provider status -> Discovery fetched` updates to the current save/test time.
7. Open the OpenEMR login page and verify the external login button appears.
8. Perform one Keycloak login with a known working user.
9. Confirm OpenEMR redirects successfully into the application after login.
10. Return to the provider admin page and confirm `Last login start` and `Last success` update.
11. Confirm the logged-in local user is the expected user account.
12. If the user should reuse an existing binding, confirm no duplicate local user was created.
13. If the user should auto-provision, confirm the created local username follows the `oidc_` prefix rule.
14. Review `Current bindings` and confirm the expected subject-to-user rows exist.
15. If your workflows use direct external bearer tokens for API access, run one API test and confirm the configured bearer audience `ai_gateway_client` is accepted.

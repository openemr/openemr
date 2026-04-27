# GCIP Auth Module for OpenEMR

Enterprise SSO/OIDC authentication via Google Cloud Identity Platform (GCIP)
and Firebase Authentication.

## Overview

This module replaces OpenEMR's standard login form with a Firebase
Authentication UI, enabling single sign-on through any identity provider
configured in your GCIP project (Google, SAML, OIDC, email/password, etc.).

**Architecture:**

- **Core OIDC layer** (`src/Common/Auth/Oidc/`) lives in OpenEMR core and
  provides provider-agnostic JWT validation, JWKS fetching, discovery, and
  identity mapping. It is not GCIP-specific.
- **This module** provides the GCIP-specific integration: Firebase JS SDK
  login form, GCIP claim mapping, and admin configuration UI.

**Scope:** Staff/clinician users only. Patient Portal is out of scope.

## Prerequisites

1. A Google Cloud project with Identity Platform enabled
2. A Firebase Authentication configuration (API key, auth domain)
3. At least one identity provider configured in the Firebase Console
4. OpenEMR running on HTTPS (required for secure cookie handling)

## Installation

### 1. Enable the module

In OpenEMR, navigate to **Modules > Manage Modules**. Find
**GCIP Auth Module** in the list of custom modules and click **Install**,
then **Enable**.

This creates the `module_gcip_config` table and registers the module's event
listeners.

### 2. Configure OpenEMR core OIDC settings

Navigate to **Administration > Config > Security** (the OIDC settings are in
the Security section of globals):

| Setting | Value | Notes |
|---------|-------|-------|
| **Enable OIDC Authentication** | Yes | Master switch |
| **OIDC Clock Skew (seconds)** | 30 | Default is fine for most setups |
| **OIDC Session Re-validation (minutes)** | 5 | How often to re-check token validity |
| **Disable Local Password Login** | No | Keep local login available during setup; disable later once OIDC is confirmed working |
| **OIDC Cache Backend** | Filesystem | Use Database or Redis for multi-instance deployments |

### 3. Configure the GCIP module

Navigate to **Modules > GCIP Auth** (or access the admin page directly at
the module's admin URL). Fill in the following fields:

| Field | Where to find it | Example |
|-------|-------------------|---------|
| **Firebase Project ID** | Firebase Console > Project Settings > General | `my-emr-project` |
| **Firebase API Key** | Firebase Console > Project Settings > General > Web API Key | `AIzaSyB...` |
| **Firebase Auth Domain** | Firebase Console > Authentication > Settings > Authorized domains | `my-emr-project.firebaseapp.com` |
| **OIDC Issuer** | For GCIP: `https://securetoken.google.com/{project-id}` | `https://securetoken.google.com/my-emr-project` |
| **Client ID (Audience)** | Same as Firebase Project ID for GCIP | `my-emr-project` |
| **Allowed Tenant ID** | Firebase Console > Authentication > Tenants (if using multi-tenancy) | `tenant-abc123` or leave empty for single-tenant/no-tenant setups |

### 4. Link users to external identities

Before a user can log in via OIDC, their OpenEMR account must be linked to
their external identity. See [Migration Guide](docs/migration.md) for
details.

### 5. Test the login

1. Open OpenEMR in a browser
2. The login page should show the Firebase Authentication UI instead of the
   standard username/password form
3. Authenticate with a linked identity provider
4. You should be redirected to the OpenEMR dashboard

If login fails, see [Troubleshooting](docs/troubleshooting.md).

### 6. Disable local login (optional)

Once OIDC login is confirmed working for all users, you can disable local
password login:

**Administration > Config > Security > Disable Local Password Login > Yes**

This forces all users through the OIDC flow. Keep at least one admin account
with a known password as a recovery mechanism (or document how to re-enable
local login via the database).

## Firebase Console Configuration Walkthrough

### Step 1: Create a Firebase project

1. Go to the [Firebase Console](https://console.firebase.google.com/)
2. Click **Add project** (or select an existing project)
3. Follow the wizard (you can disable Google Analytics if not needed)

### Step 2: Enable Identity Platform

1. In the Firebase Console, go to **Authentication**
2. Click **Get started** if not already enabled
3. If prompted to upgrade to Identity Platform, do so (required for SAML
   and multi-tenancy)

### Step 3: Configure identity providers

Go to **Authentication > Sign-in method** and enable the providers you need:

- **Google** — simplest to set up, good for organizations using Google
  Workspace
- **SAML** — for enterprise IdPs (Okta, Azure AD, etc.)
- **OIDC** — for other OIDC-compatible providers
- **Email/Password** — for development/testing (not recommended for
  production SSO)

### Step 4: Note your configuration values

Go to **Project Settings > General** and note:
- **Project ID** (e.g., `my-emr-project`)
- **Web API Key** (e.g., `AIzaSyB...`)

Go to **Authentication > Settings > Authorized domains** and note your
auth domain (e.g., `my-emr-project.firebaseapp.com`).

### Step 5: Configure authorized domains

In **Authentication > Settings > Authorized domains**, add your OpenEMR
server's domain (e.g., `emr.example.com`). This is required for the Firebase
JS SDK to work on your login page.

### Step 6: Multi-tenancy (optional)

If you need to serve multiple organizations from a single GCIP project:

1. Go to **Authentication > Tenants**
2. Create a tenant for each organization
3. Configure providers per tenant
4. Enter the tenant ID for this deployment in the OpenEMR GCIP module config

The admin UI currently accepts a single tenant ID per OpenEMR deployment
(this is what the Firebase JS SDK binds the sign-in form to). The storage
layer keeps a list-capable shape so a future release can add multi-tenant
UX (tenant picker) without a schema change.

## Configuration Reference

### Core OIDC Globals (Administration > Config > Security)

| Global | Type | Default | Description |
|--------|------|---------|-------------|
| `oidc_enabled` | bool | false | Master switch for OIDC authentication |
| `oidc_clock_skew_seconds` | int | 30 | Clock skew tolerance for JWT validation |
| `oidc_session_revalidation_minutes` | int | 5 | Token re-validation interval |
| `oidc_local_login_disabled` | bool | false | Disable local password login |
| `oidc_cache_backend` | string | filesystem | Cache for JWKS/discovery: filesystem, database, or redis |

### Module Settings (module_gcip_config table)

| Key | Description |
|-----|-------------|
| `gcip_firebase_project_id` | Firebase/GCP project identifier |
| `gcip_firebase_api_key` | Web API key for Firebase JS SDK |
| `gcip_firebase_auth_domain` | Firebase auth domain |
| `gcip_issuer` | Expected `iss` claim in ID tokens |
| `gcip_client_id` | Expected `aud` claim (usually same as project ID) |
| `gcip_allowed_tenant_ids` | Allowed tenant ID for this deployment (empty = no tenant filtering). The storage shape is a comma-separated list for forward compatibility with a future multi-tenant UX; today the admin UI sets a single value. |

## How It Works

1. User navigates to OpenEMR login page
2. Module intercepts the login page render (via `TemplatePageEvent`) and
   replaces it with the Firebase Authentication UI
3. User authenticates with their identity provider through Firebase
4. Firebase JS SDK returns an ID token (signed JWT)
5. JavaScript POSTs the token to OpenEMR's standard login endpoint
6. `GcipAuthHandler` validates the token (signature, issuer, audience,
   expiry, revocation) via the core `OidcTokenValidator`
7. The handler looks up the local user via `ExternalIdentityRepository`
   (matching `iss` + `sub` to a local user ID)
8. If found and active, a PHP session is created and the user is redirected
   to the dashboard

## Security

- JWT signatures are verified against the provider's JWKS (fetched and cached
  from the `.well-known/openid-configuration` endpoint)
- `alg:none` and symmetric algorithms (HS256) are rejected
- Issuer (`iss`) and audience (`aud`) claims are verified against configuration
- Token expiry (`exp`) is checked with configurable clock skew tolerance
- Revoked tokens (`jti`) are checked against a local revocation list
- IP-based rate limiting protects the OIDC login endpoint against brute-force
  attacks (uses existing OpenEMR `ip_tracking` infrastructure)
- All authentication events (success and failure) are recorded in the audit log

## Further Reading

- [Firebase Account Setup](docs/firebase_account_setup.md) — creating and
  configuring a Firebase/GCIP project from scratch
- [Migration Guide](docs/migration.md) — transitioning from local-only to
  OIDC-enabled authentication
- [Troubleshooting](docs/troubleshooting.md) — common errors and debugging

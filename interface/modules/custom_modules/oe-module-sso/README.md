# OpenEMR SSO Module (oe-module-sso)

Multi-provider Single Sign-On authentication module for OpenEMR.

## Overview

This module enables OpenEMR to authenticate users via external identity providers using the OpenID Connect (OIDC) protocol. It supports multiple identity providers out of the box:

- **Microsoft Entra ID** (formerly Azure AD) - For Microsoft 365 / Azure environments
- **Google Workspace** - For Google-based organizations
- **Generic OIDC** - For any OIDC-compliant identity provider (Okta, Auth0, Keycloak, etc.)

## Features

- PKCE (Proof Key for Code Exchange) for secure authorization
- JWT validation with JWKS key fetching and caching
- Automatic user linking (match IdP users to OpenEMR users by email)
- Optional auto-provisioning of new users
- Group-to-ACL mapping support (for providers that include group claims)
- Single logout support
- Comprehensive audit logging
- Admin UI for provider configuration

## Requirements

- OpenEMR 7.0.0 or later
- PHP 8.2 or later
- PR #10213 merged (SSO session support in main_screen.php)

## Installation

1. Copy the `oe-module-sso` folder to `interface/modules/custom_modules/`

2. In OpenEMR, navigate to **Admin > Modules > Manage Modules**

3. Find "oe-module-sso" in the list and click **Install**

4. Click **Enable** to activate the module

5. Navigate to **Admin > Modules > oe-module-sso** to configure providers

## Configuration

### Microsoft Entra ID

#### Option 1: Azure Portal

1. In the [Azure Portal](https://portal.azure.com), go to **Azure Active Directory > App registrations**

2. Create a new registration:
   - Name: OpenEMR SSO
   - Supported account types: Choose based on your needs
   - Redirect URI: `https://your-openemr-url/interface/modules/custom_modules/oe-module-sso/public/callback.php`

3. Note the **Application (client) ID** and **Directory (tenant) ID**

4. Create a client secret under **Certificates & secrets**

5. Under **API permissions**, ensure these are granted:
   - `openid`
   - `email`
   - `profile`

6. In OpenEMR module config, enter:
   - Client ID
   - Client Secret
   - Tenant ID

#### Option 2: Azure CLI

```bash
# Set variables
APP_NAME="OpenEMR SSO"
REDIRECT_URI="https://your-openemr-url/interface/modules/custom_modules/oe-module-sso/public/callback.php"

# Create the app registration
az ad app create \
  --display-name "$APP_NAME" \
  --web-redirect-uris "$REDIRECT_URI" \
  --sign-in-audience AzureADMyOrg

# Get the App ID
APP_ID=$(az ad app list --display-name "$APP_NAME" --query "[0].appId" -o tsv)

# Create a client secret (valid for 2 years)
az ad app credential reset --id $APP_ID --years 2

# Get Tenant ID
TENANT_ID=$(az account show --query tenantId -o tsv)

echo "Client ID: $APP_ID"
echo "Tenant ID: $TENANT_ID"
echo "Client Secret: (shown above)"
```

#### Storing Secrets in Azure Key Vault (Recommended)

```bash
# Store secrets in Key Vault
az keyvault secret set --vault-name your-keyvault --name openemr-sso-client-id --value "$APP_ID"
az keyvault secret set --vault-name your-keyvault --name openemr-sso-tenant-id --value "$TENANT_ID"
az keyvault secret set --vault-name your-keyvault --name openemr-sso-client-secret --value "your-secret"

# Retrieve later
az keyvault secret show --vault-name your-keyvault --name openemr-sso-client-secret --query value -o tsv
```

### Google Workspace

1. Go to the [Google Cloud Console](https://console.cloud.google.com)

2. Create a new project or select an existing one

3. Enable the **Google+ API** (for OpenID Connect)

4. Go to **APIs & Services > Credentials**

5. Create an **OAuth 2.0 Client ID**:
   - Application type: Web application
   - Authorized redirect URI: `https://your-openemr-url/interface/modules/custom_modules/oe-module-sso/public/callback.php`

6. In OpenEMR module config, enter:
   - Client ID
   - Client Secret
   - (Optional) Hosted Domain to restrict to your organization

### Generic OIDC

For other identity providers (Okta, Keycloak, Auth0, etc.):

1. Register OpenEMR as an OAuth/OIDC client in your IdP

2. Configure the redirect URI: `https://your-openemr-url/interface/modules/custom_modules/oe-module-sso/public/callback.php`

3. In OpenEMR module config, enter:
   - Client ID
   - Client Secret
   - Discovery URL (e.g., `https://your-idp.com/.well-known/openid-configuration`)
   - Display Name (optional - shown on login button, defaults to "SSO")
   - Icon URL (optional - custom icon for the login button)

#### Keycloak Example

1. Create a new client in your Keycloak realm
2. Set Access Type to "confidential"
3. Add the redirect URI
4. Discovery URL format: `https://keycloak.example.com/realms/your-realm/.well-known/openid-configuration`

## User Matching

When a user authenticates via SSO, the module attempts to match them to an existing OpenEMR user:

1. First, checks for existing SSO link (previous login)
2. Then, matches by email address
3. Then, matches by username (email prefix before @)
4. If no match and auto-provision is enabled, creates a new user

## Security Considerations

- All OAuth flows use PKCE for additional security
- State parameter prevents CSRF attacks
- Nonce in ID token prevents replay attacks
- Client secrets are encrypted at rest using OpenEMR's CryptoGen
- All ID tokens are validated against the IdP's JWKS
- Issuer and audience claims are verified

## Callback URLs

Configure these URLs in your identity provider:

| Setting | URL |
|---------|-----|
| Redirect URI | `https://your-openemr/interface/modules/custom_modules/oe-module-sso/public/callback.php` |
| Logout URI | `https://your-openemr/interface/modules/custom_modules/oe-module-sso/public/logout.php` |

## Troubleshooting

### "No matching OpenEMR user found"

The email from the IdP doesn't match any active OpenEMR user. Either:
- Create an OpenEMR user with that email address, or
- Enable auto-provisioning in the provider config

### "Token has expired"

The authentication took too long. Try the login again.

### "Invalid state parameter"

The authentication session expired or was tampered with. Start the login process again.

### Checking Logs

SSO events are logged to the `sso_audit_log` table. You can view them via SQL:

```sql
SELECT * FROM sso_audit_log ORDER BY created_at DESC LIMIT 50;
```

## Database Tables

| Table | Purpose |
|-------|---------|
| `sso_providers` | Provider configurations |
| `sso_user_links` | Links between IdP users and OpenEMR users |
| `sso_group_mappings` | IdP group to OpenEMR ACL mappings |
| `sso_auth_states` | Temporary storage for PKCE/state during auth flow |
| `sso_audit_log` | Audit trail of SSO events |

## Development

### Running Tests

```bash
# From the module's test directory
cd interface/modules/custom_modules/oe-module-sso/tests
../../../../../vendor/bin/phpunit

# Or from the OpenEMR root
vendor/bin/phpunit interface/modules/custom_modules/oe-module-sso/tests/Tests/Unit/
```

### Code Structure

```
oe-module-sso/
├── moduleConfig.php       # Admin configuration UI
├── public/
│   ├── authorize.php      # Initiates OIDC flow
│   ├── callback.php       # Handles IdP callback
│   └── logout.php         # Handles single logout
├── src/
│   ├── Bootstrap.php      # Module initialization & event subscriptions
│   ├── Providers/
│   │   ├── ProviderInterface.php
│   │   ├── AbstractOidcProvider.php
│   │   ├── EntraProvider.php
│   │   ├── GoogleProvider.php
│   │   └── GenericOidcProvider.php
│   └── Services/
│       ├── ProviderRegistry.php   # Provider management
│       ├── SessionBridge.php      # OpenEMR session integration
│       ├── TokenService.php       # JWT/JWKS handling
│       └── UserLinkService.php    # User matching & provisioning
├── sql/
│   └── install.sql        # Database schema
└── tests/
    ├── bootstrap.php
    ├── phpunit.xml
    └── Tests/Unit/        # PHPUnit test cases
```

### Adding a New Provider

1. Create a new class extending `AbstractOidcProvider`
2. Implement the `getDiscoveryUrl()` method
3. Override other methods as needed for provider-specific behavior
4. Register the provider in `ProviderRegistry::$providerClasses`

## License

GNU General Public License 3.0

## Authors

- A CTO, LLC

## Contributing

Contributions are welcome! Please see the main OpenEMR [CONTRIBUTING.md](https://github.com/openemr/openemr/blob/master/CONTRIBUTING.md) for guidelines.

# OpenEMR GCIP Authentication Module

<!-- AI-Generated Content Start -->
This module provides Google Cloud Identity Platform (GCIP) authentication integration for OpenEMR, allowing users to authenticate using Google Workspace accounts with OpenID Connect.

## Features

- Google Cloud Identity Platform (GCIP) authentication
- OpenID Connect integration with Google Workspace
- Secure credential storage with encryption
- Audit logging for authentication events
- Administrative configuration interface
- User settings for GCIP authentication preferences

## Installation

1. Navigate to OpenEMR's module management interface: **Modules -> Manage Modules**
2. Click the **Install** button for the GCIP Authentication module
3. After installation, click the **Config** gear icon to configure the module
4. Complete the GCIP configuration settings and click **Validate and Save**
5. Click **Enable** to activate the module

## Configuration

The module requires the following GCIP configuration parameters:

### Primary GCIP Settings (Required)
- **GCIP Project ID**: Your Google Cloud project ID
- **Client ID**: OAuth 2.0 client ID from Google Cloud Console
- **Client Secret**: OAuth 2.0 client secret from Google Cloud Console
- **Tenant ID** (Optional): For multi-tenant configurations

### User Settings
- Users can configure their GCIP authentication preferences in their user settings
- GCIP authentication can be enabled/disabled per user

## Security Features

- Encrypted storage of GCIP credentials and tokens
- Comprehensive audit logging for all authentication events
- Integration with OpenEMR's existing security framework
- Support for secure token refresh and validation

## Requirements

- OpenEMR 7.0+
- Google Cloud Identity Platform configured project
- Valid Google Workspace domain (for organization authentication)
- SSL/TLS enabled for secure authentication flows

## Documentation

For detailed setup instructions and troubleshooting, see the module's configuration interface and OpenEMR documentation.
<!-- AI-Generated Content End -->

## License

GNU General Public License 3

## Support

For support, please refer to OpenEMR community forums and documentation.
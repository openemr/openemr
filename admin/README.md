# OpenEMR Multi-Site Administration (Secure)

This directory contains the new secure multi-site administration interface for OpenEMR.

## Overview

The multi-site administration has been moved from the insecure root `admin.php` to this secure `/admin` directory with proper authentication.

## Key Features

- **Secure Authentication**: Requires login with admin credentials from the default site database
- **Session Management**: 30-minute session timeout with automatic refresh
- **ACL Integration**: Only users in the 'Administrators' group can access
- **Modern UI**: Twig-based templates with responsive design
- **CSRF Protection**: All forms protected against CSRF attacks

## File Structure

```
/admin/
  ├── login.php          # Login front controller
  ├── index.php          # Authenticated dashboard
  └── README.md          # This file

/templates/admin/
  ├── base.html.twig     # Base template for admin pages
  ├── login.html.twig    # Login page template
  └── dashboard.html.twig # Admin dashboard template

/src/Admin/
  ├── AdminAuthService.php            # Authentication service
  ├── Contracts/                      # Service interfaces
  │   ├── DatabaseConnectorInterface.php
  │   ├── SiteConfigLoaderInterface.php
  │   ├── SiteDiscoveryInterface.php
  │   └── SiteVersionReaderInterface.php
  ├── Services/                       # SOLID service implementations
  │   ├── ConnectionPoolManager.php   # Database connection pooling
  │   ├── SiteConfigLoader.php        # Configuration file loading
  │   ├── SiteDiscoveryService.php    # Filesystem site discovery
  │   ├── SiteInfoService.php         # Orchestrator service
  │   └── SiteVersionReader.php       # Database version queries
  ├── ValueObjects/                   # Immutable DTOs
  │   ├── ConnectionConfig.php        # Connection pool configuration
  │   ├── DatabaseCredentials.php     # Database credentials
  │   ├── Site.php                    # Complete site information
  │   └── SiteVersion.php             # Version information
  └── Exceptions/                     # Module-specific exceptions
      ├── SiteAdminException.php      # Base exception
      ├── DatabaseConnectionException.php
      ├── DatabaseQueryException.php
      ├── InvalidSiteNameException.php
      └── SiteConfigException.php
```

## Usage

### Access the Admin Area

1. Navigate to `http://yourserver/admin/` or `http://yourserver/admin.php`
2. You will be redirected to the login page
3. Enter admin credentials from the default site
4. Upon successful authentication, you'll see the multi-site dashboard

### Authentication Requirements

- Valid username and password from the default site database
- User must belong to the 'Administrators' ACL group
- Active user account (not disabled)

### Session Management

- Sessions automatically expire after 30 minutes of inactivity
- Each page access refreshes the session timeout
- Manual logout available via logout button

## Migration from Old Admin Page

The old root `admin.php` now redirects to `/admin/login.php`. A backup of the original file is saved as `admin.php.bak`.

### Backward Compatibility

- Existing bookmarks to `/admin.php` will automatically redirect to the new login page
- All URLs for site login and portal access remain unchanged

## Security Features

### Authentication
- Password verification using OpenEMR's AuthUtils
- Secure password clearing from memory using sodium_memzero
- Session-based authentication state

### Authorization
- ACL group checking (Administrators only)
- Active user validation
- Site-specific access control

### Session Security
- Automatic timeout after 30 minutes
- Session refresh on each page access
- Proper session destruction on logout

### CSRF Protection
- All forms include CSRF tokens
- Token verification on form submission
- Uses OpenEMR's CsrfUtils class

### UI Security
- X-Frame-Options: DENY header
- Content-Security-Policy: frame-ancestors 'none'
- All user input properly escaped in templates

## AdminAuthService API

### authenticate(string $username, string $password): array
Authenticates user credentials against the default site database.

**Returns:**
```php
[
    'success' => bool,
    'message' => string,
    'user_id' => int,    // only on success
    'username' => string // only on success
]
```

### initializeSession(int $userId, string $username): void
Initializes an authenticated admin session.

### isAuthenticated(): bool
Checks if the current session is authenticated.

### checkSessionTimeout(int $timeoutMinutes = 30): bool
Validates session hasn't timed out and refreshes timeout.

### logout(): void
Destroys the admin session.

## Testing

For a properly configured OpenEMR instance with database:

1. Access `http://localhost/admin/login.php`
2. Login with an admin user (e.g., 'admin' / 'pass')
3. Verify dashboard displays all configured sites
4. Test logout functionality
5. Verify session timeout after 30 minutes

## Development Notes

- This codebase (PoppyBilling) is a local development instance without database connectivity
- For full testing, use `/var/www/html/openemr703` which has proper database configuration
- The authentication requires a configured default site with user data

## Troubleshooting

### "Invalid username or password"
- Verify user exists in default site database
- Check user is active
- Confirm password is correct

### "User does not have administrative privileges"
- User must be in the 'Administrators' ACL group
- Check `gacl_groups_aro_map` table

### Session Timeout Issues
- Default timeout is 30 minutes
- Can be adjusted in AdminAuthService::checkSessionTimeout()

### Database Connection Errors
- Ensure default site has valid sqlconf.php
- Check database credentials
- Verify database server is running

## Future Enhancements

Potential improvements for consideration:

- Two-factor authentication (2FA) support
- Audit logging for admin actions
- IP whitelist for additional security
- Remember me functionality
- Password reset via email
- Admin user management interface
- Site creation wizard integration

## Architecture

### SOLID Principles Refactoring

The site administration system follows SOLID principles with clear separation of concerns:

**Value Objects (Immutable DTOs)**
- `Site` - Complete site information with factory methods
- `SiteVersion` - Version data with upgrade comparison logic
- `DatabaseCredentials` - Validated database configuration
- `ConnectionConfig` - Connection pool settings

**Services (Single Responsibility)**
- `SiteDiscoveryService` - Scans filesystem for valid sites
- `SiteConfigLoader` - Loads and validates sqlconf.php files
- `ConnectionPoolManager` - Manages DB connections with retry logic and exponential backoff
- `SiteVersionReader` - Queries database for version and site name
- `SiteInfoService` - Orchestrates all services to build complete Site objects

**Interfaces**
All services implement interfaces for testability and dependency injection:
- `SiteDiscoveryInterface`
- `SiteConfigLoaderInterface`
- `DatabaseConnectorInterface`
- `SiteVersionReaderInterface`

**Exception Hierarchy**
Module-specific exceptions extending `SiteAdminException`:
- `InvalidSiteNameException` - Site name validation failures
- `SiteConfigException` - Configuration loading errors
- `DatabaseConnectionException` - Connection failures with retry context
- `DatabaseQueryException` - Query execution errors

### Dependency Injection Example

```php
// Initialize services
$discovery = new SiteDiscoveryService($sitesBaseDir);
$configLoader = new SiteConfigLoader();
$connectionManager = new ConnectionPoolManager(new ConnectionConfig());
$versionReader = new SiteVersionReader($versionFilePath);
$siteInfoService = new SiteInfoService(
    $discovery,
    $configLoader,
    $connectionManager,
    $versionReader
);

// Use with exception handling
try {
    $sites = $siteInfoService->getAllSitesInfo();
    foreach ($sites as $site) {
        echo $site->getSiteName() . ': ' . $site->getVersion();
    }
} catch (SiteAdminException $e) {
    error_log('Error: ' . $e->getMessage());
} finally {
    $connectionManager->closeAllConnections();
}
```

## Credits

Based on OpenEMR's existing authentication patterns and multi-site administration system.
Refactored to SOLID principles with dependency injection and comprehensive exception handling.

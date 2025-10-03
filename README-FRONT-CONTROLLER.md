# Global Front Controller for OpenEMR

## Overview

Optional security enhancement providing centralized routing, path security, and `.inc.php` file blocking while maintaining 100% backward compatibility.

### Problem Solved

Security logs show `.inc.php` files accessed directly, causing errors and exposing vulnerabilities:
```
interface/patient_file/history/history.inc.php
Fatal error: Call to undefined function xl()
```

### Solution

Front controller (`home.php`) provides:
- ✅ Blocks `.inc.php` files (403)
- ✅ Denies forbidden paths (404)
- ✅ Prevents path traversal (404)
- ✅ Restricts admin paths
- ✅ Preserves multisite selection
- ✅ Extension hooks for custom logic
- ✅ Zero changes to existing files

## Architecture

```
User Request
    ↓
Web Server (Apache/Nginx)
    ├── Deny forbidden paths (403)
    ├── Block .inc.php files (403)
    ├── Pass through existing front controllers (/apis/, /portal/, /oauth2/)
    ├── Pass through static assets (CSS, JS, images, fonts)
    └── Route .php files → home.php
        ↓
home.php Front Controller Entry Point
    ├── CLI Detection (exit if CLI)
    ├── Load .env variables
    ├── Feature Flag Check (exit if disabled)
    ├── Early Extension Hook (custom/front_controller_early.php)
    ├── Multisite Selection (domain or ?site parameter)
    └── Delegate to Router
        ↓
Router (src/OpenCoreEMR/FrontController/Router.php)
    ├── Load Route Configuration (RouteConfig)
    ├── Extract Route from ?_ROUTE parameter
    ├── Trailing Slash Redirect (301)
    ├── Security Validation Layer
    │   ├── Deny forbidden paths (404)
    │   ├── Admin path detection (sets $_SERVER['REQUIRE_ADMIN'])
    │   ├── Block .inc.php files (403)
    │   ├── Prevent path traversal (404)
    │   └── Validate file type (404)
    ├── Late Extension Hook (custom/front_controller_late.php)
    └── Include Target File
        ↓
Target PHP File (handles auth/sessions/logic unchanged)
```

### Extensible Configuration System

**Route Configuration** (`src/OpenCoreEMR/FrontController/RouteConfig.php`):
- Centralized routing rules (forbidden paths, admin paths, bypass patterns)
- Extensible via Event System for custom rules
- Module integration support
- Easy to modify and test

**Event System Integration**:
```php
// Modules can add custom routing rules
$this->dispatcher->addListener('route.config.before', function($event) {
    $config = $event->getConfig();
    $config->addForbiddenPath('/custom/sensitive/*');
    $config->addAdminPath('/custom/admin.php');
});
```

## Security Features

### 1. Path Restrictions

**Always Denied (404)**:
- `/portal/patient/fwk/libs/*` - Framework internals
- `/sites/*/documents/*` - Patient documents

**Admin-Only Paths** (flag set for target file validation):
- `/admin.php`, `/setup.php`, `/rector.php`
- `/phpstan_panther_alias.php`, `/acl_setup.php`, `/acl_upgrade.php`
- `/sl_convert.php`, `/sql_upgrade.php`, `/gacl/setup.php`
- `/ippf_upgrade.php`, `/sql_patch.php`

### 2. .inc.php File Blocking

**Multi-layer defense**:
1. Web Server (.htaccess/nginx) - Returns 403
2. PHP Level (security-check.php) - Auto-prepended via .user.ini
3. Front Controller (home.php) - Redundant validation

### 3. Path Traversal Prevention

Blocks all variants:
- `../../../etc/passwd`
- `....//....//etc/passwd`
- `..%2F..%2F..%2Fetc%2Fpasswd` (URL-encoded)
- Uses `realpath()` + base directory validation

### 4. Static Assets

**Pass-through (no routing)**:
- CSS, JS, images (jpg, png, gif, svg, ico, webp, bmp)
- Fonts (woff, woff2, ttf, eot, otf)
- Documents (pdf, txt, xml, json, yaml, htm, html)
- Swagger documentation

### 5. Preserved Front Controllers

Routes bypass global controller:
- `/apis/*` → Existing API dispatcher
- `/portal/*` → Patient portal
- `/oauth2/*` → OAuth2 handler
- `/gacl/admin/*` → GACL admin
- `/interface/esign/*` → E-signature
- `/interface/main/calendar/*` → Calendar
- `/interface/modules/*` → Custom modules

## Extension Hooks

Optional custom logic via `/custom/` directory:

### Early Hook (`custom/front_controller_early.php`)
Runs after feature flag check, before routing.

**Use cases**:
- Request logging and telemetry
- Custom authentication layer
- Rate limiting
- Request preprocessing

**Example - Request Logging**:
```php
<?php
// Log all requests
error_log("Front Controller: " . ($_GET['_ROUTE'] ?? 'index'));

// Custom telemetry
if (function_exists('send_telemetry')) {
    send_telemetry($_SERVER['REQUEST_URI']);
}
```

**Example - Rate Limiting**:
```php
<?php
// Rate limiting (100 requests per minute per IP)
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$key = "rate_limit_$ip";
$limit = 100;
$window = 60;

if (!isset($_SESSION[$key])) {
    $_SESSION[$key] = ['count' => 0, 'start' => time()];
}

$data = $_SESSION[$key];
if (time() - $data['start'] > $window) {
    $_SESSION[$key] = ['count' => 1, 'start' => time()];
} elseif ($data['count'] >= $limit) {
    http_response_code(429);
    header('Retry-After: ' . ($window - (time() - $data['start'])));
    exit('Rate limit exceeded');
} else {
    $_SESSION[$key]['count']++;
}
```

### Late Hook (`custom/front_controller_late.php`)
Runs after validation, before executing target file.

**Use cases**:
- Error handling setup
- Performance monitoring
- Security logging
- Custom headers

**Example**:
```php
<?php
// Add custom security header
header('X-Custom-Security: enabled');

// Monitor performance
$GLOBALS['fc_start_time'] = microtime(true);

register_shutdown_function(function() {
    $duration = microtime(true) - $GLOBALS['fc_start_time'];
    error_log("Request duration: {$duration}s");
});
```

**Note**: Hook files are optional. If not present, front controller works normally.

## Quick Start

### 1. Enable Front Controller

Edit `.env` file (or create if missing):
```bash
OPENEMR_ENABLE_FRONT_CONTROLLER=1
```

### 2. Configure Web Server

**Apache** (using `.htaccess` - already configured):
```bash
sudo systemctl restart apache2
```

**Nginx** (include `nginx-front-controller.conf`):
```nginx
# In your nginx server block
include /path/to/openemr/nginx-front-controller.conf;
```
```bash
sudo nginx -s reload
```

### 3. Verify Security

Test that `.inc.php` files are blocked:
```bash
curl -I https://your-domain/interface/patient_file/history/history.inc.php
# Expected: HTTP/1.1 403 Forbidden
```

Test path traversal protection:
```bash
curl -I "https://your-domain/home.php?_ROUTE=../../../etc/passwd"
# Expected: HTTP/1.1 404 Not Found
```

## Testing

### Automated Test Suites

**CI-Integrated PHPUnit Tests**:
```bash
# Run all front controller tests
vendor/bin/phpunit --testsuite frontcontroller

# Or run individually
vendor/bin/phpunit tests/Tests/FrontController/SecurityTest.php
vendor/bin/phpunit tests/Tests/FrontController/CompatibilityTest.php
```

**Shell Script Tests** (for deployment validation):
```bash
# Security validation
./tests/scripts/test_security.sh https://your-domain

# Backward compatibility check
./tests/scripts/test_compatibility.sh https://your-domain

# Performance benchmarking (100 iterations)
./tests/scripts/test_performance.sh https://your-domain 100
```

**CLI Support**:
```bash
# Set test URL via environment variable
export OPENEMR_TEST_URL=https://your-domain

# Run tests
vendor/bin/phpunit --testsuite frontcontroller
```

### Manual Testing Checklist

- [ ] Login page accessible
- [ ] Dashboard loads after login
- [ ] Patient workflows functional
- [ ] REST API accessible
- [ ] Patient portal accessible
- [ ] `.inc.php` files blocked (403)
- [ ] Static assets load (CSS, JS, images)
- [ ] Multisite selection works

## Rollback

### Instant Disable (<1 minute)

```bash
./rollback-front-controller.sh
```

Or manually edit `.env`:
```bash
OPENEMR_ENABLE_FRONT_CONTROLLER=0
```

### Full Removal

```bash
./rollback-front-controller.sh --full
```

This removes all front controller files and creates backups.

## Files Implemented

**Core** (8 files):
- `home.php` - Main front controller entry point
- `src/OpenCoreEMR/FrontController/Router.php` - Routing engine with security validation
- `src/OpenCoreEMR/FrontController/RouteConfig.php` - Extensible routing configuration
- `security-check.php` - PHP-level .inc.php blocking (auto-prepended)
- `.htaccess` - Apache routing and security rules
- `nginx-front-controller.conf` - Nginx configuration
- `.env.example` - Feature flag configuration template
- `custom/.gitkeep` - Extension hooks directory

**Tests** (6 files):
- `tests/Tests/FrontController/SecurityTest.php` - PHPUnit security suite (CI integrated)
- `tests/Tests/FrontController/CompatibilityTest.php` - PHPUnit compatibility suite (CI integrated)
- `tests/scripts/test_security.sh` - Shell security tests
- `tests/scripts/test_compatibility.sh` - Shell compatibility tests
- `tests/scripts/test_performance.sh` - Performance benchmarks

**Documentation**:
- `README-FRONT-CONTROLLER.md` - This file

## Performance Impact

Expected overhead: **<5%**

- Static assets bypass front controller (no overhead)
- Existing API front controllers preserved (no change)
- CLI scripts bypass front controller (no overhead)
- Single `require` operation per request (minimal)

## Compatibility

- **OpenEMR**: All versions
- **PHP**: 8.0+ (already required by OpenEMR)
- **Web Server**: Apache 2.4+ with mod_rewrite OR Nginx 1.10+
- **Development Environments**: Herd, XAMPP, Docker, native installations
- **Multisite**: Fully supported (domain or ?site parameter)
- **Custom Modules**: Compatible (in `sites/*/custom/`)

## Configuration Details

### Environment Variables

**.env file**:
```bash
# Enable/disable front controller (0 or 1)
OPENEMR_ENABLE_FRONT_CONTROLLER=1

# Debug logging (debug or empty)
OPENEMR_FC_LOG_LEVEL=debug
```

### Web Server Configuration

**Apache (.htaccess)**:
- Deny forbidden paths (403)
- Block .inc.php files (403)
- Pass through existing controllers and static assets
- Route .php files to home.php

**Nginx (nginx-front-controller.conf)**:
- Include in server block
- Set fastcgi_param for feature flag
- Configure PHP-FPM socket path

### PHP Configuration (.user.ini)

Auto-prepend security check for Herd/Nginx environments:
```ini
auto_prepend_file = "/path/to/openemr/security-check.php"
```

## Troubleshooting

### .htaccess Feature Flag Not Working

**Problem**: `.env` file changes not taking effect in Apache environments.

**Cause**: Apache doesn't natively support `.env` files. The `.htaccess` file sets a default value.

**Solution Options**:

1. **Edit .htaccess directly** (recommended for Apache):
```apache
# Change this line in .htaccess:
SetEnvIf Request_URI .* OPENEMR_ENABLE_FRONT_CONTROLLER=1
```

2. **Use Apache virtual host config** (system-wide):
```apache
<VirtualHost *:80>
    SetEnv OPENEMR_ENABLE_FRONT_CONTROLLER 1
</VirtualHost>
```

3. **Use PHP's putenv()** (for custom setups):
```php
// In globals.php or custom config
putenv('OPENEMR_ENABLE_FRONT_CONTROLLER=1');
```

**Note**: For Nginx/Herd environments, `.env` file works correctly via `fastcgi_param`.

### Performance Issues

**Problem**: Slow page loads after enabling front controller.

**Diagnosis**:
```bash
# Check overhead percentage
./tests/scripts/test_performance.sh https://your-domain 100
```

**Solutions**:
- Enable OPcache in PHP
- Use PHP-FPM with FastCGI
- Verify static assets bypass front controller
- Check for custom hooks causing delays

### CLI Scripts Not Working

**Problem**: CLI scripts fail with routing errors.

**Cause**: CLI detection not working properly.

**Solution**: Router automatically detects CLI via `php_sapi_name()`. If issues persist:
```php
// Add to script before including files:
if (PHP_SAPI === 'cli') {
    putenv('OPENEMR_ENABLE_FRONT_CONTROLLER=0');
}
```

## FAQ

**Q: Do I need to modify existing code?**
A: No. Zero code changes required to existing PHP files.

**Q: What if I don't enable it?**
A: OpenEMR works exactly as before. Front controller is optional.

**Q: Does it affect performance?**
A: Minimal (<5%). Security benefits outweigh the cost.

**Q: How do I disable it?**
A: Set `OPENEMR_ENABLE_FRONT_CONTROLLER=0` in `.env` file (or `.htaccess` for Apache).

**Q: Does it work with Docker?**
A: Yes. Set in `docker-compose.yml`:
```yaml
environment:
  OPENEMR_ENABLE_FRONT_CONTROLLER: 1
```

**Q: What paths are denied?**
A: `/portal/patient/fwk/libs/*` and `/sites/*/documents/*` return 404.

**Q: How are admin paths handled?**
A: Router sets `$_SERVER['REQUIRE_ADMIN']` flag. Target file validates.

**Q: Can I add custom logic?**
A: Yes. Create `/custom/front_controller_early.php` or `/custom/front_controller_late.php`.

**Q: Does it work with Herd/Nginx?**
A: Yes. Uses `.user.ini` auto-prepend for PHP-level security.

**Q: What about trailing slashes?**
A: Automatically redirects `/path/` to `/path` (301 redirect).

**Q: How do I extend routing rules?**
A: Use Event System in RouteConfig.php to add custom paths dynamically.


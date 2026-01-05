# WARP.md - OpenEMR FaxSMS Module

This file provides guidance to WARP (warp.dev) when working with the OpenEMR FaxSMS Module.

## Module Overview

**oe-module-faxsms** is a custom OpenEMR module that provides integrated fax and SMS messaging capabilities. The module supports multiple vendor services:

- **SMS Providers**: Twilio SMS, RingCentral SMS
- **Fax Providers**: etherFAX, RingCentral Fax, SignalWire Fax
- **Email**: Email reminder/notification system

**Version**: 5.0.0
**License**: GPL-3.0
**Authors**: Jerry Padgett, Stephen Nielson

## Architecture

### Design Pattern

This module uses an **abstract dispatch pattern** to arbitrate and route API calls to different vendor services. The architecture supports:

- Multiple vendors for the same service type (SMS/Fax)
- Per-call vendor selection
- Easy extensibility for new vendors

### Directory Structure

```
oe-module-faxsms/
├── src/                          # PSR-4 autoloaded classes (OpenEMR\Modules\FaxSMS\*)
│   ├── Controller/               # Service controllers and dispatch logic
│   │   ├── AppDispatch.php       # Abstract base class for all service clients
│   │   ├── TwilioSMSClient.php   # Twilio SMS implementation
│   │   ├── RCFaxClient.php       # RingCentral Fax implementation
│   │   ├── EtherFaxActions.php   # etherFAX implementation
│   │   ├── SignalWireClient.php  # SignalWire Fax implementation
│   │   ├── EmailClient.php       # Email notification client
│   │   ├── NotificationTaskManager.php  # Appointment reminder task manager
│   │   └── AppDispatch.php       # Service dispatcher
│   ├── EtherFax/                 # etherFAX SDK components
│   │   ├── EtherFaxClient.php    # Main client
│   │   ├── FaxAccount.php
│   │   ├── FaxReceive.php
│   │   ├── FaxResult.php
│   │   ├── FaxState.php
│   │   └── FaxStatus.php
│   ├── Events/                   # Event listeners
│   │   └── NotificationEventListener.php
│   └── BootstrapService.php      # Module initialization and globals management
├── library/                      # Legacy utility scripts
│   ├── rc_sms_notification.php   # SMS notification dispatcher
│   ├── setup_services.php        # Service configuration UI
│   ├── utility.php               # Utility functions
│   ├── run_notifications.php     # Notification runner
│   └── api_onetime.php           # One-time API setup
├── sql/                          # SQL migration scripts
├── vendor/                       # Composer dependencies (RingCentral SDK)
├── openemr.bootstrap.php         # Module bootstrap - event listeners and menu
├── ModuleManagerListener.php     # Laminas module lifecycle hooks
├── BootstrapService.php          # Service initialization
├── messageUI.php                 # Main UI for viewing/sending messages
├── contact.php                   # Contact selection dialog
├── setup.php                     # Credential setup UI
├── setup_rc.php                  # RingCentral specific setup
├── setup_email.php               # Email setup
├── moduleConfig.php              # Module configuration iframe
├── table.sql                     # Database schema
├── composer.json                 # Module dependencies
└── version.php                   # Version information
```

## Core Components

### 1. AppDispatch Abstract Class

**Location**: `src/Controller/AppDispatch.php`

**Purpose**: Base class for all service client implementations. Handles:
- Request/response management
- Authentication routing
- Action dispatching
- Credential management (encrypted)
- Session management

**Abstract Methods** (must be implemented by child classes):
```php
abstract function authenticate(): string|int|bool;
abstract function sendFax(): string|bool;
abstract function sendSMS(): mixed;
abstract function sendEmail(): mixed;
abstract function fetchReminderCount(): string|bool;
```

**Key Features**:
- Encrypts/decrypts credentials using OpenEMR's CryptoGen
- Routes actions based on URL parameters (`_ACTION_COMMAND`, `type`)
- Manages service type in session (`$_SESSION['oefax_current_module_type']`)

### 2. Service Implementations

Each vendor has its own controller class extending `AppDispatch`:

- **TwilioSMSClient.php** - Twilio SMS API integration
- **RCFaxClient.php** - RingCentral Fax/SMS API integration
- **EtherFaxActions.php** - etherFAX API integration
- **SignalWireClient.php** - SignalWire Fax API integration
- **EmailClient.php** - Email notification system

### 3. Event System Integration

**Bootstrap File**: `openemr.bootstrap.php`

The module integrates with OpenEMR's Symfony event dispatcher to:

1. **Add Menu Items** - Dynamically adds SMS/Fax menu items based on enabled services
2. **Patient Report Buttons** - Injects "Send Fax" button in patient reports
3. **Document Actions** - Adds "Send Fax" link to patient documents
4. **SMS Buttons** - Injects "Send SMS" button where configured
5. **Notification Listeners** - Subscribes to appointment reminder events

**Event Listeners**:
```php
// Menu
MenuEvent::MENU_UPDATE

// Patient Reports
PatientReportEvent::ACTIONS_RENDER_POST
PatientReportEvent::JAVASCRIPT_READY_POST

// Documents
PatientDocumentEvent::ACTIONS_RENDER_FAX_ANCHOR
PatientDocumentEvent::JAVASCRIPT_READY_FAX_DIALOG

// SMS
SendSmsEvent::ACTIONS_RENDER_SMS_POST
SendSmsEvent::JAVASCRIPT_READY_SMS_POST
```

### 4. Module Lifecycle (Laminas Integration)

**File**: `ModuleManagerListener.php`

Handles module lifecycle events through Laminas Module Manager:

- **install()** - Initial installation
- **enable()** - Restores persisted globals when enabled
- **disable()** - Persists current globals and disables services
- **unregister()** - Cleanup on unregistration

## Database Schema

### Tables

#### `module_faxsms_credentials`
Stores encrypted vendor API credentials per user.

```sql
CREATE TABLE module_faxsms_credentials (
  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  auth_user INT(11) UNSIGNED DEFAULT 0,  -- 0 for global, user ID for per-user
  vendor VARCHAR(63),                     -- 'twilio', 'etherfax', 'ringcentral', '_persisted'
  credentials MEDIUMBLOB NOT NULL,        -- Encrypted JSON credentials
  updated DATETIME DEFAULT CURRENT_TIMESTAMP,
  setup_persist TINYTEXT,
  UNIQUE KEY (auth_user, vendor)
);
```

**Special Vendor**: `_persisted` - Used to backup setup globals when module is disabled

#### `oe_faxsms_queue`
Fax/SMS message queue and history.

```sql
CREATE TABLE oe_faxsms_queue (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  account TINYTEXT,                       -- Vendor account identifier
  uid INT(11),                            -- OpenEMR user ID
  job_id TEXT,                            -- Vendor job/message GUID
  date DATETIME DEFAULT CURRENT_TIMESTAMP,
  receive_date DATETIME,
  deleted INT(1) DEFAULT 0,
  calling_number TINYTEXT,                -- From number
  called_number TINYTEXT,                 -- To number
  mime TINYTEXT,                          -- Content type
  details_json LONGTEXT,                  -- JSON details from vendor
  KEY (uid, receive_date)
);
```

### Global Variables

The module uses custom globals stored in the `globals` table:

- `oefax_enable_fax` - Fax vendor selection ('0'=disabled, '1'=RingCentral, '3'=etherFAX, '6'=SignalWire)
- `oefax_enable_sms` - SMS vendor selection ('0'=disabled, '1'=RingCentral, '2'=Twilio)
- `oesms_send` - Enable SMS send buttons in UI
- `oerestrict_users` - Restrict to individual user accounts
- `oe_enable_email` - Enable email reminders

## Configuration

### Service Setup Flow

1. **Module Registration**: Modules → Manage Modules → Register → Install → Enable
2. **Module Config**: Click config icon → Select vendors (SMS/Fax/Email)
3. **Credential Setup**: Click "Setup SMS/Fax Account" button
4. **Enter Credentials**: Input vendor API credentials (encrypted on save)
5. **Re-login**: Log out and back in to activate

### Per-User vs. Global Credentials

- **Global** (`auth_user = 0`): Credentials shared across all users
- **Per-User** (`auth_user = user_id`): Each user maintains their own vendor accounts

Set via "Individual User Accounts" checkbox in module config.

## Security

### Credential Encryption

**Critical**: All vendor credentials are encrypted using `OpenEMR\Common\Crypto\CryptoGen` before storage.

```php
$crypto = new CryptoGen();
$encrypted = $crypto->encryptStandard(json_encode($credentials));
```

**Never store API keys, passwords, or tokens in plain text.**

### ACL Requirements

Minimum ACL for module features:
- **SMS/Fax Management**: `['patients', 'docs']`
- **Setup Services**: `['admin', 'docs']`
- **Notifications**: `['admin', 'demo']`

Verify ACL in controllers:
```php
$clientApp->verifyAcl(); // Checks ACL before allowing access
```

## Vendor Integration

### Adding a New Vendor

To add a new SMS or Fax vendor:

1. **Create Controller Class** in `src/Controller/` extending `AppDispatch`:
```php
namespace OpenEMR\Modules\FaxSMS\Controller;

class NewVendorClient extends AppDispatch
{
    public function authenticate(): bool { /* ... */ }
    public function sendFax(): string|bool { /* ... */ }
    public function sendSMS(): mixed { /* ... */ }
    public function sendEmail(): mixed { /* ... */ }
    public function fetchReminderCount(): string|bool { /* ... */ }
}
```

2. **Update Service Type Constants** in `AppDispatch.php` or config

3. **Add Setup UI** in `setup.php` or create new setup file

4. **Update Bootstrap Menu** in `openemr.bootstrap.php` to include new vendor

5. **Register in Dispatcher** - Ensure `AppDispatch::getApiService()` can instantiate your class

### Current Vendor API Versions

- **Twilio**: Uses Twilio REST API (native implementation)
- **RingCentral**: Uses `ringcentral/ringcentral-php` 3.0.3 (via Composer)
- **etherFAX**: Custom SDK in `src/EtherFax/`
- **SignalWire**: Uses `signalwire-community/signalwire` 3.2.0 (via Composer)

## UI Components

### Main Message UI

**File**: `messageUI.php`

Features:
- Tabbed interface (Received / Sent / Queue)
- Date range filtering
- View/download fax documents
- Send new fax/SMS
- Document upload (Dropzone.js)
- TIFF and PDF preview support

**JavaScript Libraries**:
- Dropzone.js - File uploads
- jsPDF - PDF generation
- jsTIFF - TIFF rendering
- datetime-picker - Date selection

### Contact Dialog

**File**: `contact.php`

Modal dialog for selecting recipient and sending messages:
- Patient search
- Provider search
- Direct number entry
- Document attachment
- Preview before send

## Background Services

### Appointment Reminders

**File**: `library/rc_sms_notification.php`

Runs appointment reminder notifications via:
- **Manual**: Click menu item "Send Email Reminders" / "Test Email Reminders"
- **Scheduled**: Can be triggered via OpenEMR background services or cron

**Configuration**:
- Hours before appointment to notify (set in Twilio setup)
- Message template with replaceable tags: `***NAME***`, `***PROVIDER***`, `***DATE***`, `***STARTTIME***`, `***ENDTIME***`, `***ORG***`

### Background Service Integration

To integrate with OpenEMR's background services:

```sql
INSERT INTO background_services (name, title, active, running, next_run,
    execute_interval, function, require_once, sort_order)
VALUES ('FaxSMS_Notifications', 'FaxSMS Appointment Notifications', 1, 0, NOW(),
    3600, 'send_faxsms_notifications',
    '/interface/modules/custom_modules/oe-module-faxsms/library/run_notifications.php',
    100);
```

## Common Issues & Troubleshooting

### Issue: Module doesn't appear in menu

**Cause**: Global variables not set or module not enabled

**Solution**:
1. Check module is installed and enabled in Modules Manager
2. Verify globals exist: `SELECT * FROM globals WHERE gl_name LIKE 'oefax%'`
3. Log out and back in after configuration changes

### Issue: Credentials not saving

**Cause**: Encryption failure or database permissions

**Solution**:
1. Check `sites/<site>/documents` directory is writable (CryptoGen needs keys)
2. Verify `module_faxsms_credentials` table exists
3. Check PHP error logs for CryptoGen errors

### Issue: "Not Authorised" errors

**Cause**: User lacks required ACL permissions

**Solution**:
1. Verify user has `['patients', 'docs']` ACL at minimum
2. For setup: User needs `['admin', 'docs']` ACL
3. Check ACL in: Administration → ACL Administrator

### Issue: Background notifications not running

**Cause**: Background services not configured or credentials invalid

**Solution**:
1. Test manually first: Menu → Services → Notifications → Test Email Reminders
2. Verify credentials in setup are valid
3. Check `background_services` table for service entry
4. Review logs in `sites/<site>/documents/logs_and_misc/`

## Development Guidelines

### Code Standards

- **PSR-12**: All new code must follow PSR-12 Extended Coding Style
- **Namespacing**: Use `OpenEMR\Modules\FaxSMS\*` namespace for src/ classes
- **Encryption**: Always encrypt sensitive credentials before storage
- **ACL**: Always verify ACL before allowing actions
- **Legacy Functions**: Avoid deprecated OpenEMR database functions

### Database Queries

Use modern QueryUtils instead of legacy functions:

```php
use OpenEMR\Common\Database\QueryUtils;

// Instead of sqlQuery()
$row = QueryUtils::querySingleRow($sql, $binds);

// Instead of sqlStatement() + loop
$rows = QueryUtils::fetchRecords($sql, $binds);
```

### Testing Credentials

Use **demo/sandbox modes** when testing:
- Twilio: Offers test credentials and phone numbers
- etherFAX: Has demo mode (toggle in setup)
- RingCentral: Provides sandbox environment
- SignalWire: Offers sandbox space for testing

**Never test with production credentials during development.**

### Session Management

The module uses `$sessionAllowWrite = true` in entry points to ensure session writes:

```php
$sessionAllowWrite = true;
require_once(__DIR__ . "/../../../globals.php");
```

This is required for CLI scripts and background services.

## Key Dependencies

### Composer Packages

```json
{
  "require": {
    "ringcentral/ringcentral-php": "3.0.3"
  }
}
```

**Important**: Each module has its own `composer.json`. Do **NOT** run `composer dump-autoload` in the OpenEMR root directory.

### Module Dependencies

- OpenEMR 7.0.3+
- PHP 8.1+ (uses modern PHP features: enums, first-class callables)
- Laminas Framework (for module management)
- Symfony EventDispatcher

## File Upload & Document Integration

### Document Categories

Module auto-creates "FAX" category in `categories` table on installation:

```sql
INSERT INTO categories (name, value, parent, aco_spec)
VALUES ('FAX', '', 1, 'patients|docs');
```

Received faxes are stored as patient documents under this category.

### File Storage

- **Fax Documents**: Stored in `sites/<site>/documents/<patient>/`
- **Temporary Uploads**: Uses OpenEMR's temp directory system
- **MIME Types**: Supports TIFF, PDF, JPEG, PNG

## API Endpoints

The module uses internal routing via `_ACTION_COMMAND` parameter:

**Format**: `?type=<service>&_ACTION_COMMAND=<action>`

**Examples**:
- `?type=sms&_ACTION_COMMAND=sendSMS` - Send SMS
- `?type=fax&_ACTION_COMMAND=sendFax` - Send fax
- `?type=fax&_ACTION_COMMAND=retrieveFax` - Get fax from queue
- `?type=sms&_ACTION_COMMAND=fetchReminderCount` - Get reminder count

Routes are handled by `AppDispatch::dispatchActions()` and mapped to controller methods.

## JavaScript Integration

### Global Variables

Set in page header for JavaScript access:

```javascript
let pid = <patient_id>;
let portalUrl = "<portal_url>";
let currentService = "<service_type>";  // '1'=RC, '2'=Twilio, '3'=etherFAX, '6'=SignalWire
let serviceType = "<type>";              // 'sms', 'fax', 'email'
```

### Dialog System

Uses OpenEMR's `dlgopen()` for modal dialogs:

```javascript
dlgopen(url, dialogName, modalSize, height, allowResize, title, options);
```

**Modal Sizes**: `'modal-sm'`, `'modal-md'`, `'modal-lg'`, `'modal-xl'`

## Useful Commands

### View Module Status
```bash
mysql -u local_openemr -p 5qy3xkMjP4A2US1u7Qv -e "
  SELECT mod_name, mod_directory, enabled, mod_ui_active
  FROM modules
  WHERE mod_directory = 'oe-module-faxsms';"
```

### Check Globals
```bash
mysql -u local_openemr -p 5qy3xkMjP4A2US1u7Qv -e "
  SELECT gl_name, gl_value
  FROM globals
  WHERE gl_name LIKE 'oefax%' OR gl_name LIKE 'oesms%' OR gl_name = 'oe_enable_email';"
```

### View Credentials (Encrypted)
```bash
mysql -u local_openemr -p 5qy3xkMjP4A2US1u7Qv -e "
  SELECT id, auth_user, vendor, updated
  FROM module_faxsms_credentials;"
```

### Check Message Queue
```bash
mysql -u local_openemr -p 5qy3xkMjP4A2US1u7Qv -e "
  SELECT id, account, job_id, date, calling_number, called_number
  FROM oe_faxsms_queue
  ORDER BY date DESC LIMIT 20;"
```

## References

- **OpenEMR Module System**: `/Documentation/MODULES.md` (in OpenEMR root)
- **Event System**: `/src/Events/` (in OpenEMR root)
- **Twilio API**: https://www.twilio.com/docs/sms
- **RingCentral API**: https://developers.ringcentral.com/
- **etherFAX API**: https://www.etherfax.net/developers/
- **SignalWire API**: https://developer.signalwire.com/fax/

## Best Practices

1. **Always encrypt credentials** using CryptoGen before database storage
2. **Verify ACL** at the start of every controller action
3. **Use service type routing** to support multiple vendors seamlessly
4. **Test in demo/sandbox mode** before using production API keys
5. **Log errors** to OpenEMR's logging system for debugging
6. **Respect session management** - use `$sessionAllowWrite = true` where needed
7. **Follow OpenEMR coding standards** - PSR-12, QueryUtils, modern PHP
8. **Document vendor-specific quirks** in controller comments
9. **Handle API failures gracefully** with user-friendly error messages
10. **Keep vendor SDKs updated** but test thoroughly after updates

## SignalWire Fax Integration

### Overview

SignalWire provides a Twilio-compatible REST API for faxing, making it an excellent alternative or addition to existing fax providers. The integration uses the `signalwire-community/signalwire` PHP SDK.

### Setup Requirements

**Credentials Needed**:
1. **Space URL**: Your SignalWire space (e.g., `example.signalwire.com`)
2. **Project ID**: UUID from SignalWire dashboard
3. **API Token**: Generated API token from project settings
4. **Fax Number**: A fax-enabled phone number in E.164 format

### Configuration Steps

1. **Sign up for SignalWire**:
   - Visit https://signalwire.com
   - Create an account and project
   - Note your Space URL from the dashboard

2. **Generate API Token**:
   - Navigate to project settings
   - Create a new API token with Fax permissions
   - Save the token securely

3. **Provision Fax Number**:
   - Purchase or port a fax-capable phone number
   - Ensure the number is configured for fax

4. **Configure in OpenEMR**:
   - Navigate to: **Modules → Manage Modules → FaxSMS Module → Config**
   - Select "SignalWire Fax" from Fax vendor dropdown
   - Click "Setup Fax Account"
   - Enter your credentials:
     - Space URL (without https://)
     - Project ID
     - API Token
     - Fax Number (+1XXXXXXXXXX)
   - Check "Production Mode" if using production credentials
   - Save configuration

5. **Configure Webhook** (for receiving faxes):
   - In SignalWire dashboard, select your fax number
   - Configure "Fax Settings":
     - When a fax is received: Webhook
     - URL: `https://your-openemr.com/interface/modules/custom_modules/oe-module-faxsms/library/webhook_receiver.php?type=fax&vendor=signalwire`
     - Method: POST
     - Content-Type: application/x-www-form-urlencoded

### Features

**Sending Faxes**:
- Send faxes from patient documents
- Send faxes from reports
- Support for PDF and TIFF documents
- Real-time status updates via webhooks
- Automatic retry on transient failures

**Receiving Faxes**:
- Automatic webhook processing
- Media download and storage
- Integration with patient documents (when configured)
- Queue management and tracking

### Webhook Security

The webhook endpoint uses:
- `$ignoreAuth = true` for external access
- Vendor verification via URL parameters
- Encrypted credential storage
- Error logging for debugging

### Troubleshooting SignalWire

**Issue: Faxes not sending**
- Verify credentials are correct
- Check API token has Fax permissions
- Ensure fax number is in E.164 format
- Review error logs: `sites/<site>/documents/logs_and_misc/`

**Issue: Webhooks not working**
- Verify webhook URL is publicly accessible
- Check webhook logs in error_log
- Ensure URL parameters are correct (`?type=fax&vendor=signalwire`)
- Test webhook with SignalWire's webhook testing tool

**Issue: Media download fails**
- Verify temp directory is writable
- Check credentials are properly encrypted
- Review curl errors in error_log
- Ensure outbound HTTPS connections are allowed

### API Rate Limits

SignalWire applies standard rate limits:
- Check current limits in SignalWire documentation
- Monitor usage in SignalWire dashboard
- Implement exponential backoff for retries
- Cache fax status to minimize API calls

### Cost Considerations

- Faxes are billed per page
- Inbound and outbound rates may differ
- Check current pricing at https://signalwire.com/pricing
- Monitor usage to avoid unexpected costs

## Next Steps

When working on this module:

1. **Review the specific issue** - What's not working? Error messages?
2. **Check relevant controller** - Which vendor service is affected?
3. **Verify configuration** - Are credentials set and valid?
4. **Check logs** - OpenEMR logs and vendor API logs
5. **Test in isolation** - Use vendor's test tools before debugging module code

---

**Last Updated**: December 2025
**For**: OpenEMR 7.0.3 / oe-module-faxsms v5.0.0

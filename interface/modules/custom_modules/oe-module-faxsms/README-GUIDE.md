# Fax • SMS • Email • Voice Module for OpenEMR (`oe-module-faxsms`)

> Unified communications for OpenEMR — enabling Fax, SMS, Email, and optional Voice (RingCentral) features. Compatible with OpenEMR **7.0.4+**.

---

## 📘 Overview
This module extends OpenEMR’s communication capabilities, providing clinicians and staff with a single interface to send and receive **Faxes**, **SMS**, **Emails**, and make **Voice** calls. It supports multiple vendors, allows **per-user service permissions**, and integrates tightly with OpenEMR’s patient and document management systems.

---

## ⚙️ Supported Vendors and Services

| Service | Supported Vendors | Notes |
|----------|------------------|-------|
| **Fax** | RingCentral Fax (ID 1), etherFAX (ID 3) | Use account credentials or API key. |
| **SMS** | RingCentral SMS (ID 1), Twilio (ID 2), Clickatell (ID 5) | Requires valid API SID and Secret. |
| **Email** | Built-in SMTP (ID 4) | Standard mail client configuration. |
| **Voice** | RingCentral Voice (ID 6) | Supports web phone / click-to-call widgets. |

> ⚖️ **HIPAA Tip:** Avoid transmitting PHI in SMS messages unless your vendor signs a BAA. Use generic appointment or callback notifications instead.

---

## 🧩 Installation

### 1️⃣ Enable the Module
1. Go to **Administration → Modules → Manage Modules**.
2. Click **Unregistered**, find **Fax SMS Module**, and **Register → Install → Enable** it.
3. The module will appear under the **Modules** menu.

### 2️⃣ Install Dependencies
Run the following in your OpenEMR root directory:
```bash
composer install && composer dump-autoload -o
```

### 3️⃣ Configure Vendor Accounts
Open **Modules → Fax • SMS • Email → Setup** to enable and configure services.

#### Global Setup Options
- Enable/disable Fax, SMS, Email, and Voice individually.
- Choose vendors from dropdowns (RingCentral, Twilio, etherFAX, etc.).
- Optionally enable **Individual User Accounts** (for user-specific credentials).
- Use **Dialog Mode** for pop-up configuration panels.

#### Account Setup
After enabling, use the buttons:
- **Setup Fax** — Enter API key, username/password, or JWT.
- **Setup SMS** — Enter vendor credentials (SID/Secret for Twilio, JWT or OAuth for RingCentral).
- **Setup Email** — Configure SMTP host, port, username, password.
- **Setup Voice** — Configure RingCentral JWT or OAuth credentials.

---

## 🔐 User Service Permissions (Fine-Grained Access)

Administrators can now control each user’s access to Fax, SMS, Email, and Voice features independently.

**Path:** `Modules → Fax • SMS • Email → Setup → Users/Permissions`

| Permission | Grants Access To | When Disabled |
|-------------|------------------|----------------|
| **Fax** | Send, receive, and file faxes | Fax tab hidden and API calls blocked |
| **SMS** | Send SMS messages and reminders | SMS tab hidden and send disabled |
| **Email** | Compose and send emails | Email tab hidden |
| **Voice** | Use Voice widget for calling | Voice tab hidden |

**Special Controls:**
- **Use Primary** — Use shared global account credentials.
- **Primary User** — Designates master account for all services.
- **Bulk Toggles** — Enable or disable all services per user or per column.

**Permission Hierarchy:**
1. Global service must be enabled.
2. User must have permission for the service.
3. Appropriate credentials (user-specific or primary) are applied.

> Changes apply immediately—no restart required.

---

## 📞 RingCentral App Scope Requirements

**Minimum Application Scopes (under Security → Application Scopes):**
- Call Control
- Edit Message
- Edit Presence
- Internal Messages
- Read Accounts
- Read Call Log
- Read Call Recording (for fax/voice records)
- Read Contacts
- Read Messages
- Read Presence
- RingOut
- SMS
- VoIP Calling
- WebSocketSubscription
- Edit Extensions

These scopes are required for full operation of RingCentral Fax, SMS, and Voice.

---

## ⚙️ Background Services (Notifications)

The module includes built-in background jobs for automated **SMS** and **Email reminders**.

### Configuration
Available under: `Modules → Fax • SMS • Email → Background Services`

| Action | Description |
|---------|-------------|
| **Create and Run** | Creates a service job but leaves it disabled. |
| **Enable / Disable** | Start or stop the background service. |
| **Delete** | Permanently remove the job. |

**Service Parameters:**
- Execution interval (hours; default 24)
- Status display includes last run, next run, and status.

**Notes:**
- First notifications run within 2 minutes of enablement.
- Each service has its own schedule.
- Services require Phase 1 (Enable Accounts) to be complete.

---

## 🧰 Cron Job Example
To automate appointment reminders:

```bash
30 8 * * * www-data /usr/bin/php \
  /var/www/openemr/interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php \
  site=default user=admin type=sms testrun=0 >/dev/null 2>&1
```

**Parameters:**
- `site` – OpenEMR site ID (e.g., default)
- `user` – Username for execution
- `type` – `sms` or `email`
- `testrun` – `1` for dry run, `0` to send

---

## 🔒 Security and Compliance
- All forms use **CSRF tokens**.
- **ACL** and **User Service Permissions** enforce multi-layered access control.
- **Individual User Accounts** isolate credentials and usage.
- Usage is tracked per username.
- **GPL-3 License** applies.

---

## 🧾 Troubleshooting
| Issue | Likely Cause | Resolution |
|--------|---------------|-------------|
| Service missing from menu | Not enabled in Setup | Enable under “Enable Accounts” |
| User blocked | Missing permission | Grant in User Permissions tab |
| Background job not running | Disabled or misconfigured | Enable service and verify interval |
| Fax/SMS fails | Invalid credentials | Re-enter API info or JWT |
| Email send failure | SMTP misconfiguration | Verify host, port, TLS, credentials |
| Voice widget error | Missing scope or JWT expired | Renew token or refresh permissions |

---

## 🧠 Best Practices
**Initial Setup**
1. Enable only necessary services.
2. Configure vendor credentials before user permissions.
3. Test with one user.
4. Schedule background jobs during low-usage periods.

**User Management**
- Use **Primary Account** for shared credentials.
- Review permissions quarterly.
- Use **Individual Accounts** for auditing.

**Background Jobs**
- Adjust execution frequency for practice size.
- Monitor logs for errors.
- Back up configuration files.

---

## 🧾 License
This module is distributed under the **GNU General Public License v3.0 (GPL-3)**.

---

## 📚 References
- **Repository Path:**
  `interface/modules/custom_modules/oe-module-faxsms`
- **OpenEMR Forum Topic:**
  [Newly Improved Fax, SMS and Email Module – Community Discussion](https://community.open-emr.org/t/newly-improved-fax-sms-and-email-module/23266)
- **Vendor Docs:**
  [RingCentral Developer Portal](https://developer.ringcentral.com)
  [etherFAX](https://www.etherfax.net)
  [Twilio Docs](https://www.twilio.com/docs)

---

## 🧩 Version / Change Log

### 7.0.4 — *November 28, 2025*
- Added **User Service Permissions** (per-user Fax/SMS/Email/Voice control)
- Integrated **Voice (RingCentral)** web phone widget
- Improved **Background Services** (SMS & Email reminders)
- Enhanced **Vendor Setup** flow (Dialog & Panel modes)
- Added full **RingCentral Scopes** documentation
- Security hardening (CSRF tokens, ACL enforcement)
- Documentation consolidated into one authoritative README

### 7.0.3 and Earlier
- Initial release supporting Twilio SMS and etherFAX
- Added abstract dispatch layer for vendor API integration
- Introduced per-call arbitration for fax/SMS services

---

**Maintained by:** OpenEMR Community
**Lead Developer:** Jerry Padgett
**Version:** 7.0.4
**License:** GPL-3
**Module Path:** `interface/modules/custom_modules/oe-module-faxsms`

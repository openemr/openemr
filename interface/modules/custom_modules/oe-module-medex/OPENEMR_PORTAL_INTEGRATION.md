# MedEx Secure Chat - OpenEMR Portal Integration

## Overview

MedEx secure chat now integrates with OpenEMR's portal messaging system, providing two options:

1. **MedEx Standalone UI** (default) - Original chat interface
2. **OpenEMR Portal Redirect** - Uses OpenEMR's built-in portal messaging

## Features

### ✅ Dual-Write Messaging
- Messages sent in MedEx chat automatically sync to OpenEMR's `onsite_mail` table
- Patient can view messages in either MedEx UI or OpenEMR portal
- Provider can respond through OpenEMR portal messaging interface

### ✅ UI Toggle
- Add `&use_portal=1` to secure chat link to redirect to OpenEMR portal
- Example: `http://localhost/chat/secure/{token}` → MedEx UI
- Example: `http://localhost/chat/secure/{token}?use_portal=1` → OpenEMR Portal

### ✅ Automatic Session Mapping
- Bearer token automatically creates OpenEMR portal session
- Last-name verification preserved (for patient tokens)
- Provider tokens bypass verification in both UIs

## How It Works

### Token Registration Flow
1. OpenEMR generates secure chat token via MedEx module
2. Token registered on MedEx with OpenEMR URL + API key
3. Link sent to patient via SMS/email

### Message Send Flow (MedEx UI)
1. Patient sends message in MedEx chat interface
2. Message saved to MedEx `hipaa_outgoing` table (local)
3. Message POSTed to OpenEMR's `receive_chat_message.php` endpoint
4. OpenEMR writes to `onsite_mail` table via `addPortalMailboxMail()`
5. Message appears in both systems

### Portal Redirect Flow (use_portal=1)
1. Patient clicks secure chat link with `use_portal=1` parameter
2. MedEx detects parameter and redirects to OpenEMR `portal_redirect.php`
3. OpenEMR validates token from `medex_secure_chat_tokens` table
4. Patient verifies last name (if not provider token)
5. OpenEMR creates portal session with `pid` and `patient_portal_onsite_two`
6. Redirect to OpenEMR portal messaging UI (`portal/messaging/messages.php`)

## Database Schema

### MedEx (HIPAA database)
```sql
-- hipaa_secure_chat_tokens (enhanced)
ALTER TABLE hipaa_secure_chat_tokens 
ADD COLUMN openemr_url VARCHAR(255) -- OpenEMR base URL
ADD COLUMN openemr_api_key VARCHAR(64); -- Shared secret for API calls
```

### OpenEMR (openemr database)
```sql
-- medex_chat_sync (new table)
CREATE TABLE medex_chat_sync (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medex_msg_uid BIGINT NOT NULL, -- MedEx msg_uid
    openemr_mail_id BIGINT NOT NULL, -- OpenEMR onsite_mail.id
    practice_id INT NOT NULL,
    pid VARCHAR(50) NOT NULL,
    sync_date DATETIME NOT NULL,
    UNIQUE KEY uk_medex_msg (medex_msg_uid)
);
```

## Security

- **API Key Authentication**: Each practice has unique API key stored in `medex_prefs.medex_api_key`
- **Token Validation**: Tokens verified against `medex_secure_chat_tokens` before portal session creation
- **Last-Name Verification**: Patient tokens require last-name verification (provider tokens bypass)
- **Session Isolation**: Portal sessions properly scoped to patient PID

## Configuration

### Enable Portal Sync (Automatic)
Integration is enabled automatically when:
- Token registered from OpenEMR MedEx module
- OpenEMR URL and API key included in registration

### Generate Links

**MedEx UI** (default):
```php
$token = bin2hex(random_bytes(32));
$chatUrl = 'http://localhost/chat/secure/' . $token;
```

**OpenEMR Portal UI**:
```php
$token = bin2hex(random_bytes(32));
$chatUrl = 'http://localhost/chat/secure/' . $token . '?use_portal=1';
```

## Testing

### Test Dual-Write
1. Send secure chat link (without `use_portal` parameter)
2. Patient sends message in MedEx UI
3. Check OpenEMR: `SELECT * FROM onsite_mail ORDER BY id DESC LIMIT 5`
4. Verify message appears in `onsite_mail.body`

### Test Portal Redirect
1. Send secure chat link with `?use_portal=1` parameter
2. Patient clicks link
3. Should redirect to OpenEMR portal after last-name verification
4. Check session: `SELECT * FROM sessions WHERE session_data LIKE '%patient_portal_onsite_two%'`

### Test API Endpoint
```bash
# Test receive_chat_message.php (requires valid API key)
curl -X POST http://localhost:8300/interface/modules/custom_modules/oe-module-medex/public/receive_chat_message.php \
  -H "Content-Type: application/json" \
  -d '{
    "token": "abc123...",
    "practice_id": 1,
    "pid": "123",
    "message": "Test message",
    "from": "PATIENT",
    "msg_uid": 999,
    "api_key": "your-api-key-here"
  }'
```

## Troubleshooting

### Messages Not Syncing to OpenEMR
- Check `openemr_url` and `openemr_api_key` in `hipaa_secure_chat_tokens`
- Verify API key matches `medex_prefs.medex_api_key` in OpenEMR
- Check MedEx logs: `docker logs medex-localhost-80-app-1 | grep RedisChat`

### Portal Redirect Not Working
- Verify `openemr_url` is set in token record
- Check OpenEMR logs: `docker logs openemr-8-0-1-dev-openemr-1 | grep portal_redirect`
- Ensure `medex_secure_chat_tokens` table exists in OpenEMR

### Last-Name Verification Fails
- Check patient data: `SELECT fname, lname FROM patient_data WHERE pid = ?`
- Verify case-insensitive comparison working
- Check session storage: Patient verification stored in `$_SESSION['medex_portal_verified_{token}']`

## Files Modified/Created

### OpenEMR
- `interface/modules/custom_modules/oe-module-medex/public/receive_chat_message.php` (new)
- `interface/modules/custom_modules/oe-module-medex/public/portal_redirect.php` (new)
- `interface/modules/custom_modules/oe-module-medex/src/MedExAPI.php` (modified)
- `sql/medex_chat_sync.sql` (new)

### MedEx
- `system/library/medex/RedisChat.php` (modified - both medex-core and web_full)
- `catalog/controller/information/chat_patient.php` (modified - both medex-core and web_full)
- `catalog/controller/api/secure_chat.php` (modified)
- `sql/add_openemr_sync_fields.sql` (new)

## Future Enhancements

- [ ] Make `use_portal` a practice-level default setting
- [ ] Add admin UI toggle in MedEx module settings
- [ ] Bidirectional sync: OpenEMR portal messages → MedEx
- [ ] Read receipts across both systems
- [ ] Notification preferences (push to MedEx, portal, or both)

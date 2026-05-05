# MedEx-OpenEMR Portal Integration - Quick Start Guide

## ✅ Integration Complete

All tasks completed successfully:
1. ✅ Inventory OpenEMR portal messaging tables and APIs
2. ✅ Design dual-write layer for MedEx messages
3. ✅ Add UI toggle for MedEx vs portal redirect
4. ✅ Map bearer tokens to portal sessions

## How to Use

### Option 1: MedEx UI with Portal Sync (Recommended)
Send secure chat links as normal. Messages automatically sync to OpenEMR portal:

```
http://localhost/chat/secure/{token}
```

**Features:**
- Patient uses familiar MedEx chat interface
- Messages saved to both MedEx and OpenEMR databases
- Provider can respond in either MedEx or OpenEMR portal
- Real-time Redis pub/sub for instant delivery

### Option 2: Direct to OpenEMR Portal
Add `?use_portal=1` to redirect patient to OpenEMR portal messaging:

```
http://localhost/chat/secure/{token}?use_portal=1
```

**Features:**
- Patient redirected to OpenEMR portal after verification
- Uses OpenEMR's built-in messaging UI
- Bearer token automatically creates portal session
- Message history preserved from MedEx

## Test the Integration

### 1. Generate a New Secure Chat Token
From OpenEMR:
- Navigate to patient chart
- Use MedEx module to send secure chat
- Integration is automatic (no configuration needed)

### 2. Test Dual-Write (Option 1)
```bash
# Send the link without ?use_portal parameter
# Patient uses MedEx UI to send message
# Then check OpenEMR database:

docker exec openemr-8-0-1-dev-openemr-1 mysql -uopenemr -popenemr openemr \
  -e "SELECT id, owner, sender_name, LEFT(body, 50) as message 
      FROM onsite_mail 
      ORDER BY id DESC LIMIT 5"
```

Expected: Message appears in `onsite_mail` table

### 3. Test Portal Redirect (Option 2)
```bash
# Send the link WITH ?use_portal=1 parameter
# Patient clicks link
# Should redirect to: http://localhost:8300/portal/messaging/messages.php
```

Expected: Patient sees OpenEMR portal messaging interface after verification

## Architecture

```
┌─────────────┐                        ┌─────────────┐
│   Patient   │                        │   Provider  │
└──────┬──────┘                        └──────┬──────┘
       │                                      │
       │ Click secure link                   │
       ▼                                      ▼
┌─────────────────────────────────────────────────┐
│              MedEx Chat Controller              │
│  (catalog/controller/information/chat_patient)  │
└──────┬──────────────────────┬─────────────────┘
       │                      │
       │ use_portal=0         │ use_portal=1
       │ (default)            │
       ▼                      ▼
┌──────────────┐      ┌────────────────────┐
│   MedEx UI   │      │  Portal Redirect   │
│ (Redis chat) │      │  (portal_redirect) │
└──────┬───────┘      └─────────┬──────────┘
       │                        │
       │ Send message           │ Create session
       ▼                        ▼
┌──────────────┐      ┌────────────────────┐
│ RedisChat    │      │ OpenEMR Portal UI  │
│ + syncToEMR  │      │    (onsite_mail)   │
└──────┬───────┘      └────────────────────┘
       │
       │ POST to OpenEMR
       ▼
┌─────────────────────┐
│ receive_chat_msg.php│
│ (OpenEMR endpoint)  │
└──────┬──────────────┘
       │
       │ Write to DB
       ▼
┌─────────────────────┐
│   onsite_mail       │
│   (portal messages) │
└─────────────────────┘
```

## Configuration (Auto-Generated)

The following are automatically configured when the first secure chat token is created:

- **OpenEMR URL**: Detected from `$GLOBALS['webroot']` or HTTP headers
- **API Key**: Auto-generated 64-char hex string, stored in `medex_prefs.medex_api_key`
- **Token Settings**: `openemr_url` and `openemr_api_key` stored per-token in MedEx database

No manual configuration needed!

## Monitoring

### Check Message Sync
```bash
# See synced messages
docker exec openemr-8-0-1-dev-openemr-1 mysql -uopenemr -popenemr openemr \
  -e "SELECT * FROM medex_chat_sync ORDER BY sync_date DESC LIMIT 10"
```

### Check Logs
```bash
# MedEx logs (dual-write)
docker logs medex-localhost-80-app-1 2>&1 | grep -i "RedisChat\|OpenEMR sync"

# OpenEMR logs (receive endpoint)
docker logs openemr-8-0-1-dev-openemr-1 2>&1 | grep -i "MedEx Chat Receiver"
```

### Verify Integration Status
```bash
# Run test suite
bash /Users/ray/projects/openemr/interface/modules/custom_modules/oe-module-medex/test_integration.sh
```

## Troubleshooting

### Messages not syncing to OpenEMR
1. Check MedEx logs for HTTP errors
2. Verify `openemr_url` and `openemr_api_key` in token record
3. Test endpoint directly:
```bash
curl -X POST http://localhost:8300/interface/modules/custom_modules/oe-module-medex/public/receive_chat_message.php \
  -H "Content-Type: application/json" \
  -d '{"token":"...","practice_id":1,"pid":"123","message":"test","from":"PATIENT","msg_uid":1,"api_key":"..."}'
```

### Portal redirect not working
1. Verify `use_portal=1` in URL
2. Check `openemr_url` is set in token record
3. Ensure `medex_secure_chat_tokens` table exists in OpenEMR
4. Check session creation in portal_redirect.php logs

### Last-name verification fails
1. Verify patient has `lname` in `patient_data` table
2. Check case-insensitive comparison
3. Provider tokens should bypass verification (`is_provider=1`)

## Files Reference

### OpenEMR Files
- `interface/modules/custom_modules/oe-module-medex/public/receive_chat_message.php` - Message receiver endpoint
- `interface/modules/custom_modules/oe-module-medex/public/portal_redirect.php` - Bearer token → portal session mapper
- `interface/modules/custom_modules/oe-module-medex/src/MedExAPI.php` - Token registration with integration settings
- `sql/medex_chat_sync.sql` - Sync tracking table schema

### MedEx Files
- `system/library/medex/RedisChat.php` - Extended with `syncToOpenEMR()` method
- `catalog/controller/information/chat_patient.php` - Added `use_portal` redirect logic
- `catalog/controller/api/secure_chat.php` - Accepts `openemr_url` and `openemr_api_key` parameters
- `sql/add_openemr_sync_fields.sql` - Schema updates for integration

## Support

See full documentation: [OPENEMR_PORTAL_INTEGRATION.md](OPENEMR_PORTAL_INTEGRATION.md)

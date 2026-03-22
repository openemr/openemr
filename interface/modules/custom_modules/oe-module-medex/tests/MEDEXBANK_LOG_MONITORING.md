# MedExBank Container Log Monitoring Guide

## Overview

This guide explains how to monitor the MedExBank Docker container logs while testing the modernized MedEx module to verify API communication and message processing.

---

## Prerequisites

### 1. Docker Compose Setup

Ensure your `docker-compose.yml` includes the MedExBank service:

```yaml
services:
  openemr:
    # ... openemr config ...

  medexbank:
    image: medexbank/api:latest
    container_name: medexbank
    ports:
      - "8301:80"
    environment:
      - API_KEY=your_test_api_key
      - API_SECRET=your_test_secret
    networks:
      - openemr-net
```

### 2. Start Containers

```bash
cd docker/development-easy
docker compose up --detach --wait
```

### 3. Verify MedExBank is Running

```bash
docker compose ps medexbank
```

Expected output:
```
NAME        IMAGE                    STATUS         PORTS
medexbank   medexbank/api:latest     Up 2 minutes   0.0.0.0:8301->80/tcp
```

---

## Log Monitoring Commands

### Basic Log Viewing

**Follow all logs (real-time):**
```bash
docker compose logs -f medexbank
```

**View last 100 lines:**
```bash
docker compose logs --tail=100 medexbank
```

**View logs since timestamp:**
```bash
docker compose logs --since 2026-01-23T10:00:00 medexbank
```

**View logs for specific time range:**
```bash
docker compose logs --since 2h medexbank
```

---

## What to Look For in Logs

### 1. Container Startup

**Expected on startup:**
```
[2026-01-23 10:00:00] MedExBank API Server Starting
[2026-01-23 10:00:00] Environment: development
[2026-01-23 10:00:00] API Version: 2.0.1
[2026-01-23 10:00:00] Listening on port 80
[2026-01-23 10:00:00] Database connection: OK
[2026-01-23 10:00:00] Redis connection: OK
[2026-01-23 10:00:00] Ready to accept requests
```

**⚠️ If you see errors:**
- Database connection failed → Check DB credentials
- Redis connection failed → Check Redis service
- Port already in use → Check port conflicts

---

### 2. Authentication Requests

When OpenEMR connects to MedExBank, watch for auth logs.

**Expected flow:**

```
[2026-01-23 10:01:00] POST /api/auth/token
[2026-01-23 10:01:00]   IP: 172.18.0.2 (openemr container)
[2026-01-23 10:01:00]   User-Agent: OpenEMR/7.0.0 MedEx/2.0
[2026-01-23 10:01:00]   API Key: abc123... (first 6 chars)
[2026-01-23 10:01:00]   Status: 200 OK
[2026-01-23 10:01:00]   Token issued: eyJ0eXAiOiJKV1QiLCJ... (expires in 1h)
```

**✅ Success indicators:**
- Status: 200 OK
- Token issued
- Valid expiration time

**❌ Failure indicators:**
```
[2026-01-23 10:01:00] POST /api/auth/token
[2026-01-23 10:01:00]   Status: 401 Unauthorized
[2026-01-23 10:01:00]   Error: Invalid API credentials
```

**Fix:** Check API key/secret in OpenEMR → Administration → Globals → Connectors

---

### 3. Message Submission (EventsService)

When EventsService generates and sends messages, watch for API calls.

**Expected log flow:**

```
[2026-01-23 10:05:00] POST /api/messages/send
[2026-01-23 10:05:00]   Auth: Bearer eyJ0eXAiOiJKV1Qi... [VALID]
[2026-01-23 10:05:00]   Campaign: REMINDER
[2026-01-23 10:05:00]   Messages in batch: 4
[2026-01-23 10:05:00]   Processing message 1/4:
[2026-01-23 10:05:00]     Type: SMS
[2026-01-23 10:05:00]     To: +1-555-0001
[2026-01-23 10:05:00]     Message: "Reminder: You have an appointment tomorrow..."
[2026-01-23 10:05:00]     Status: QUEUED
[2026-01-23 10:05:00]     Message ID: msg_abc123
[2026-01-23 10:05:00]   Processing message 2/4:
[2026-01-23 10:05:00]     Type: EMAIL
[2026-01-23 10:05:00]     To: jane.email@example.com
[2026-01-23 10:05:00]     Subject: "Appointment Reminder"
[2026-01-23 10:05:00]     Status: QUEUED
[2026-01-23 10:05:00]     Message ID: msg_def456
[2026-01-23 10:05:00]   Processing message 3/4:
[2026-01-23 10:05:00]     Type: AVM (Voice)
[2026-01-23 10:05:00]     To: +1-555-0003
[2026-01-23 10:05:00]     Status: QUEUED
[2026-01-23 10:05:00]     Message ID: msg_ghi789
[2026-01-23 10:05:00]   Processing message 4/4: SKIPPED (patient blocked)
[2026-01-23 10:05:00]   Batch complete: 3 queued, 0 failed, 1 skipped
[2026-01-23 10:05:00]   Response: 200 OK
[2026-01-23 10:05:00]   Total processing time: 247ms
```

**✅ Success indicators:**
- Status: QUEUED (not SENT yet, just queued)
- Message IDs generated
- Response: 200 OK
- Processing time reasonable (<1s per message)

**❌ Failure indicators:**
```
[2026-01-23 10:05:00]   Processing message 1/4:
[2026-01-23 10:05:00]     Type: SMS
[2026-01-23 10:05:00]     To: +1-555-0001
[2026-01-23 10:05:00]     Status: FAILED
[2026-01-23 10:05:00]     Error: Invalid phone number format
```

---

### 4. Message Delivery Status

After messages are queued, watch for delivery status updates.

**Expected flow:**

```
[2026-01-23 10:05:30] Background Worker: Processing message queue
[2026-01-23 10:05:30]   Message ID: msg_abc123 (SMS)
[2026-01-23 10:05:30]   Provider: Twilio
[2026-01-23 10:05:30]   Sending to: +1-555-0001
[2026-01-23 10:05:31]   Twilio Response: SID=SM1234567890abcdef
[2026-01-23 10:05:31]   Status: SENT
[2026-01-23 10:05:31]   Delivery time: 1.2s

[2026-01-23 10:05:32]   Message ID: msg_def456 (EMAIL)
[2026-01-23 10:05:32]   Provider: SendGrid
[2026-01-23 10:05:32]   Sending to: jane.email@example.com
[2026-01-23 10:05:33]   SendGrid Response: message_id=abc123
[2026-01-23 10:05:33]   Status: SENT
[2026-01-23 10:05:33]   Delivery time: 0.8s

[2026-01-23 10:05:34]   Message ID: msg_ghi789 (AVM)
[2026-01-23 10:05:34]   Provider: VoiceAPI
[2026-01-23 10:05:34]   Calling: +1-555-0003
[2026-01-23 10:05:40]   Call connected
[2026-01-23 10:05:45]   Message played successfully
[2026-01-23 10:05:45]   Status: DELIVERED
[2026-01-23 10:05:45]   Call duration: 11s
```

**✅ Success indicators:**
- Status: SENT → DELIVERED
- Provider responses received
- No errors

**❌ Failure indicators:**
```
[2026-01-23 10:05:30]   Message ID: msg_abc123 (SMS)
[2026-01-23 10:05:30]   Provider: Twilio
[2026-01-23 10:05:30]   Error: Phone number is not opted in for SMS
[2026-01-23 10:05:30]   Status: FAILED
[2026-01-23 10:05:30]   Will retry in 1 hour
```

---

### 5. Callback Processing (CallbackService)

When providers send delivery receipts, MedExBank forwards them to OpenEMR.

**Expected flow:**

```
[2026-01-23 10:10:00] Webhook received: POST /api/callbacks/twilio
[2026-01-23 10:10:00]   Message SID: SM1234567890abcdef
[2026-01-23 10:10:00]   Status: delivered
[2026-01-23 10:10:00]   Delivered at: 2026-01-23 10:09:55
[2026-01-23 10:10:00]   Looking up OpenEMR message ID...
[2026-01-23 10:10:00]   Found: msg_abc123
[2026-01-23 10:10:00]   Forwarding to OpenEMR: http://openemr/interface/modules/custom_modules/oe-module-medex/src/callback.php
[2026-01-23 10:10:01]   OpenEMR Response: 200 OK
[2026-01-23 10:10:01]   Callback processed successfully
```

**✅ Success indicators:**
- Webhook received
- Message ID matched
- OpenEMR callback successful (200 OK)
- CallbackService->update() processed correctly

**❌ Failure indicators:**
```
[2026-01-23 10:10:00]   Forwarding to OpenEMR: http://openemr/...
[2026-01-23 10:10:01]   OpenEMR Response: 500 Internal Server Error
[2026-01-23 10:10:01]   Error: Failed to update message status
[2026-01-23 10:10:01]   Will retry in 5 minutes
```

---

### 6. Campaign Event Generation (EventsService)

When calculating events for campaigns.

**Expected flow:**

```
[2026-01-23 10:15:00] POST /api/campaigns/calculate
[2026-01-23 10:15:00]   Auth: Bearer eyJ0eXAi... [VALID]
[2026-01-23 10:15:00]   Campaign UID: camp_abc123
[2026-01-23 10:15:00]   Campaign Type: REMINDER
[2026-01-23 10:15:00]   Date Range: 2026-01-24 to 2026-01-31
[2026-01-23 10:15:00]   Request payload:
[2026-01-23 10:15:00]     {
[2026-01-23 10:15:00]       "token": "abc123...",
[2026-01-23 10:15:00]       "events": [
[2026-01-23 10:15:00]         {
[2026-01-23 10:15:00]           "C_UID": "camp_abc123",
[2026-01-23 10:15:00]           "M_group": "REMINDER",
[2026-01-23 10:15:00]           "E_timing": "24",
[2026-01-23 10:15:00]           "enable_SMS": "1",
[2026-01-23 10:15:00]           "enable_AVM": "1",
[2026-01-23 10:15:00]           "enable_EMAIL": "1"
[2026-01-23 10:15:00]         }
[2026-01-23 10:15:00]       ]
[2026-01-23 10:15:00]     }
[2026-01-23 10:15:01]   Events calculated: 12 messages for 4 patients
[2026-01-23 10:15:01]   Response: 200 OK
```

**✅ Success indicators:**
- Campaign parameters received correctly
- Event count matches expectations
- M_group recognized (REMINDER, RECALL, etc.)
- Response: 200 OK

---

## Monitoring Multiple Terminals

### Recommended Terminal Setup

**Terminal 1: OpenEMR PHP Errors**
```bash
docker compose exec openemr tail -f /var/log/apache2/error.log
```

**Terminal 2: MedExBank Logs**
```bash
docker compose logs -f medexbank
```

**Terminal 3: MySQL Queries**
```bash
docker compose exec openemr tail -f /var/lib/mysql/queries.log | grep medex
```

**Terminal 4: OpenEMR Access Log**
```bash
docker compose exec openemr tail -f /var/log/apache2/access.log | grep medex
```

---

## Filtering MedExBank Logs

### Filter by Log Level

**Errors only:**
```bash
docker compose logs -f medexbank | grep -i "error\|fail"
```

**Warnings and errors:**
```bash
docker compose logs -f medexbank | grep -i "error\|fail\|warn"
```

**Info and above:**
```bash
docker compose logs -f medexbank | grep -v "debug"
```

### Filter by Component

**Authentication only:**
```bash
docker compose logs -f medexbank | grep -i "auth\|token"
```

**Message processing:**
```bash
docker compose logs -f medexbank | grep -i "message\|sms\|email\|avm"
```

**Campaign events:**
```bash
docker compose logs -f medexbank | grep -i "campaign\|event\|calculate"
```

**Callbacks:**
```bash
docker compose logs -f medexbank | grep -i "callback\|webhook\|delivery"
```

### Filter by Status Code

**Successful requests (200-299):**
```bash
docker compose logs -f medexbank | grep "Status: 2[0-9][0-9]"
```

**Client errors (400-499):**
```bash
docker compose logs -f medexbank | grep "Status: 4[0-9][0-9]"
```

**Server errors (500-599):**
```bash
docker compose logs -f medexbank | grep "Status: 5[0-9][0-9]"
```

---

## Correlating OpenEMR and MedExBank Logs

### Match Request/Response

When OpenEMR calls MedExBank, correlate logs using timestamps and request IDs.

**OpenEMR log (Terminal 1):**
```
[2026-01-23 10:05:00] EventsService->generate() called
[2026-01-23 10:05:00] Sending 4 messages to MedExBank API
[2026-01-23 10:05:00] Request ID: req_xyz789
```

**MedExBank log (Terminal 2):**
```
[2026-01-23 10:05:00] POST /api/messages/send
[2026-01-23 10:05:00] Request ID: req_xyz789
[2026-01-23 10:05:00] Processing...
```

**✅ Match found:** Same request ID and timestamp

### Track Message Flow

**Step 1: OpenEMR generates message (EventsService)**
```
Terminal 1 (OpenEMR):
[10:05:00] EventsService->processReminders()
[10:05:00] Generated message for patient 123
[10:05:00] Modalities: SMS=true, Email=true
```

**Step 2: Message sent to MedExBank**
```
Terminal 2 (MedExBank):
[10:05:00] POST /api/messages/send
[10:05:00] Message ID: msg_abc123
[10:05:00] Type: SMS, To: +1-555-0001
[10:05:00] Status: QUEUED
```

**Step 3: MedExBank processes queue**
```
Terminal 2 (MedExBank):
[10:05:30] Background worker processing msg_abc123
[10:05:31] Sent via Twilio: SID=SM123...
[10:05:31] Status: SENT
```

**Step 4: Delivery receipt (CallbackService)**
```
Terminal 2 (MedExBank):
[10:10:00] Webhook from Twilio: delivered
[10:10:00] Forwarding to OpenEMR callback
```

**Step 5: OpenEMR updates status**
```
Terminal 1 (OpenEMR):
[10:10:01] CallbackService->update() called
[10:10:01] Message msg_abc123: QUEUED → DELIVERED
[10:10:01] Updated medex_outgoing table
```

---

## Common Log Patterns

### Successful Campaign Event Generation

```
OpenEMR:
[10:00:00] EventsService->generate() START
[10:00:00] Campaign: REMINDER, Date range: 2026-01-24 to 2026-01-31
[10:00:00] Found 15 appointments
[10:00:00] Processing patient 1: Modalities SMS=Y, AVM=Y, EMAIL=Y
[10:00:00] Processing patient 2: Modalities SMS=N, AVM=N, EMAIL=Y
...
[10:00:05] Generated 38 messages total
[10:00:05] EventsService->generate() END (5.2s)

MedExBank:
[10:00:05] POST /api/messages/send
[10:00:05] Batch: 38 messages
[10:00:06] All queued successfully
[10:00:06] Response: 200 OK
```

### Failed Authentication

```
OpenEMR:
[10:00:00] HttpClient->post() to https://api.medexbank.com/api/auth/token
[10:00:01] Response: 401 Unauthorized

MedExBank:
[10:00:00] POST /api/auth/token
[10:00:00] API Key: invalid_key_123
[10:00:00] Lookup failed: No matching credentials
[10:00:00] Response: 401 Unauthorized
[10:00:00] Error: Invalid API credentials
```

**Fix:** Update credentials in OpenEMR Globals

### Message Delivery Failure

```
MedExBank:
[10:05:30] Processing message msg_abc123 (SMS)
[10:05:30] Provider: Twilio
[10:05:31] Twilio Error: 21211 - Invalid phone number
[10:05:31] Status: FAILED
[10:05:31] Will retry in 1 hour

OpenEMR (after callback):
[10:05:32] CallbackService->update()
[10:05:32] Message msg_abc123: QUEUED → FAILED
[10:05:32] Reason: Invalid phone number
```

---

## Performance Monitoring

### Response Times

**Watch for slow requests:**
```bash
docker compose logs medexbank | grep "processing time:" | awk '{print $NF}' | sort -n
```

**Expected:**
- Authentication: <100ms
- Message submission (single): <200ms
- Message submission (batch of 10): <1s
- Campaign calculation: <5s for 100 patients

**⚠️ If slower:**
- Check database performance
- Check network latency
- Check provider API response times

### Request Rates

**Count requests per minute:**
```bash
docker compose logs --since 1h medexbank | grep "POST\|GET" | wc -l
```

**Monitor for rate limiting:**
```bash
docker compose logs -f medexbank | grep -i "rate limit\|too many requests"
```

---

## Debugging Tips

### Enable Debug Logging

**In MedExBank container:**
```bash
docker compose exec medexbank /bin/sh
echo "LOG_LEVEL=debug" >> /etc/medexbank/config
supervisorctl restart medexbank-api
```

**Expected output:**
```
[10:00:00] [DEBUG] Request headers: {...}
[10:00:00] [DEBUG] Request body: {...}
[10:00:00] [DEBUG] SQL query: SELECT * FROM ...
[10:00:00] [DEBUG] Query time: 23ms
[10:00:00] [DEBUG] Response prepared: {...}
```

### Save Logs to File

**Capture logs for analysis:**
```bash
docker compose logs medexbank > medexbank_logs_$(date +%Y%m%d_%H%M%S).log
```

### Search Historical Logs

```bash
grep -i "error" medexbank_logs_*.log
grep "msg_abc123" medexbank_logs_*.log
```

---

## Success Criteria

### ✅ Healthy MedExBank Logs Show:

1. Container starts without errors
2. Database and Redis connections succeed
3. Authentication requests return 200 OK with tokens
4. Message submissions return 200 OK with message IDs
5. Messages queue successfully
6. Background workers process queue
7. Delivery receipts received and forwarded
8. CallbackService updates processed
9. Response times reasonable (<1s for most operations)
10. No 500 errors

### ❌ Problem Indicators:

- 401 Unauthorized (auth issues)
- 500 Internal Server Error (server crash)
- Timeout errors (network/performance issues)
- "Connection refused" (container not running)
- SQL errors (database issues)
- Provider API errors (Twilio/SendGrid issues)

---

## Next Steps

After verifying logs are healthy:

1. Run through all test scenarios in USER_TESTING_GUIDE.md
2. Monitor both OpenEMR and MedExBank logs simultaneously
3. Verify message flow from generation → queue → send → deliver → callback
4. Document any errors or unexpected behavior
5. Performance test with large message volumes

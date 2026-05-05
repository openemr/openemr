# MedEx Subscription & Licensing Model

## Business Model

MedEx Calendar and AI features are **premium SaaS add-ons** requiring active subscription.

### Free (Core MedEx)
- ✅ Recall board
- ✅ Patient communication (SMS/AVM/Email)
- ✅ Basic appointment reminders
- ✅ MedExBank registration

### Premium (Subscription Required)
- 💰 **Modern Calendar** - FullCalendar UI with drag-drop
- 💰 **Schedule Templates** - One-click template application
- 💰 **AI Features** - No-show prediction, smart suggestions, revenue insights
- 💰 **Advanced Analytics** - Practice optimization dashboards

## Licensing Gates

### Three-Gate System

```
┌─────────────────────────────────────┐
│  GATE 1: MedEx Module Enabled?      │
│  ├─ Check: globals.medex_enable = 1 │
│  └─ If NO → OpenEMR calendar        │
└─────────────────────────────────────┘
              ↓ YES
┌─────────────────────────────────────┐
│  GATE 2: Active Subscription?       │
│  ├─ Call: MedExBank API             │
│  ├─ Check: Payment current?         │
│  └─ If NO → OpenEMR calendar        │
└─────────────────────────────────────┘
              ↓ YES
┌─────────────────────────────────────┐
│  GATE 3: Calendar Feature in Plan?  │
│  ├─ Check: 'calendar' in features[] │
│  └─ If NO → OpenEMR calendar        │
└─────────────────────────────────────┘
              ↓ YES
┌─────────────────────────────────────┐
│  ✅ SHOW MEDEX CALENDAR              │
└─────────────────────────────────────┘
```

## Subscription Pricing (Per-Service / Per-Provider)

MedEx uses **à la carte per-service pricing**, NOT bundled tiers.

| Service | Service Key | Price | Billing Scope |
|---------|------------|-------|---------------|
| Appointment Reminders | `appointment_reminders` | $9.95/mo | Per provider |
| Secure Patient Chat | `secure_chat` | $9.95/mo | Per practice |
| Calendar Export | `calendar_export` | $4.95/mo | Per practice |
| Full Calendar View | `calendar_full` | $4.95/mo | Per practice |
| AI Scheduling Assistant | `calendar_ai` | $14.95/mo | Per provider |
| PDF Form Management | `pdf_management` | $9.95/mo | Per practice |
| vFax | `vfax` | $9.95/mo | Per practice *(coming soon)* |
| Dedicated 10-DLC Number | `dedicated_number` | $14.95/mo | Per practice *(coming soon)* |

**Pricing source:** OpenCart `oc_product` table on `api.hipaabank.net` (served by `api/oemr/pricing`).
Cached in `medex_prefs.status` JSON for 7 days. To update prices, change `oc_product.price`
for the relevant `product_id` and bust the client pricing cache.

**OpenCart product_ids for the key services:**
- 54: appointment_reminders — 69: calendar_export — 70: calendar_ai
- 75: secure_chat — 76: pdf_management

## MedExBank API - Subscription Check

### Endpoint

**POST** `https://api.medexbank.com/api/v2/subscription/status`

**Headers:**
```
Authorization: Bearer {api_key}
Content-Type: application/json
```

**Request:**
```json
{
  "practice_id": "12345",
  "check_date": "2026-01-29"
}
```

**Response (Active):**
```json
{
  "active": true,
  "plan": "professional",
  "expires": "2026-02-28",
  "features": [
    "recall_board",
    "sms",
    "email",
    "avm",
    "calendar",
    "templates",
    "ai_basic"
  ],
  "payment_status": "current",
  "last_payment": "2026-01-15",
  "next_billing": "2026-02-15",
  "billing_amount": 99.00,
  "grace_period_ends": null
}
```

**Response (Expired):**
```json
{
  "active": false,
  "reason": "subscription_expired",
  "expired_date": "2026-01-15",
  "features": [],
  "payment_status": "overdue",
  "amount_due": 99.00,
  "grace_period_ends": "2026-01-22",
  "renew_url": "https://medexbank.com/billing/renew"
}
```

**Response (Payment Failed):**
```json
{
  "active": false,
  "reason": "payment_failed",
  "expires": "2026-02-28",
  "features": [],
  "payment_status": "payment_failed",
  "last_attempt": "2026-01-15",
  "retry_date": "2026-01-18",
  "update_payment_url": "https://medexbank.com/billing/payment-method"
}
```

## Fallback Behavior

### Scenario 1: MedEx Disabled
```
User clicks "Calendar" →
  Check: medex_enable = 0
  Result: OpenEMR calendar loads (no redirect)
```

### Scenario 2: Subscription Expired
```
User clicks "Calendar" →
  Check: medex_enable = 1 ✓
  Check: subscription active? ✗ (expired)
  Result: OpenEMR calendar loads
  Optional: Show banner "Renew MedEx to access modern calendar"
```

### Scenario 3: Payment Failed
```
User clicks "Calendar" →
  Check: medex_enable = 1 ✓
  Check: subscription active? ✗ (payment failed)
  Result: OpenEMR calendar loads
  Optional: Show banner "Update payment method"
```

### Scenario 4: Feature Not in Plan
```
User clicks "Calendar" →
  Check: medex_enable = 1 ✓
  Check: subscription active? ✓
  Check: 'calendar' in features? ✗
  Result: OpenEMR calendar loads
  Optional: Show banner "Upgrade to Professional for Modern Calendar"
```

### Scenario 5: Everything Active
```
User clicks "Calendar" →
  Check: medex_enable = 1 ✓
  Check: subscription active? ✓
  Check: 'calendar' in features? ✓
  Result: MedEx calendar loads!
```

## Grace Period

**7-day grace period** after payment failure:
- Days 1-7: Full access continues
- Day 8: Features disabled, OpenEMR calendar shows
- Automatic retry: 3 attempts over 7 days

## Caching Strategy

### Why Cache?
- Network failures shouldn't break calendar
- Reduce API calls (cost)
- Better performance

### Cache Rules
- **Duration:** 24 hours
- **Storage:** `medex_prefs.status` field (JSON)
- **Update:** On successful API call
- **Fallback:** If API unreachable, use cache
- **Expiry:** After 24 hours, treat as expired

### Cache Example
```json
{
  "active": true,
  "plan": "professional",
  "features": ["calendar", "templates"],
  "cached_at": "2026-01-29 10:00:00"
}
```

## Admin Panel Integration

### Show Subscription Status

In MedEx settings page:

```php
$licenseService = new LicenseService();
$subscription = $licenseService->checkSubscription();

if ($subscription['active']) {
    echo "✅ Active - {$subscription['plan']} plan";
    echo "Expires: {$subscription['expires']}";
} else {
    echo "❌ Inactive - {$subscription['reason']}";
    echo "<a href='{$subscription['renew_url']}'>Renew Now</a>";
}
```

### Feature Toggle UI

```
┌─────────────────────────────────────────────────┐
│  MedEx Settings                                 │
├─────────────────────────────────────────────────┤
│  Subscription: ✅ Professional Plan             │
│  Status: Active until Feb 28, 2026             │
│  [Manage Subscription] [Update Payment]        │
├─────────────────────────────────────────────────┤
│  Features:                                      │
│  ☑ Modern Calendar (included)                  │
│  ☑ Schedule Templates (included)               │
│  ☐ AI Suite (upgrade to Enterprise)            │
├─────────────────────────────────────────────────┤
│  Calendar Settings:                             │
│  ☑ Enable MedEx Calendar                       │
│  ☐ Fall back to OpenEMR on errors              │
└─────────────────────────────────────────────────┘
```

## Testing Scenarios

### Test 1: Disable MedEx
```bash
UPDATE globals SET gl_value = '0' WHERE gl_name = 'medex_enable';
# Expected: OpenEMR calendar shows
```

### Test 2: Simulate Expired Subscription
```bash
# MedExBank API returns: "active": false, "reason": "subscription_expired"
# Expected: OpenEMR calendar shows
```

### Test 3: Invalid API Key
```bash
UPDATE medex_prefs SET ME_api_key = 'invalid_key';
# Expected: OpenEMR calendar shows (graceful fallback)
```

### Test 4: MedExBank API Down
```bash
# Disconnect from internet
# Expected: Use cached status, if cache expired → OpenEMR calendar
```

## Revenue Protection

### Why This Works:
1. ✅ **No bypass** - License check in code (not just UI)
2. ✅ **Graceful degradation** - Never breaks user workflow
3. ✅ **Server-side validation** - Can't be circumvented client-side
4. ✅ **Cached for reliability** - Works offline briefly
5. ✅ **Clear upgrade path** - Easy to convert free users

### What Stays Free:
- OpenEMR calendar (always available)
- Basic MedEx recall functionality
- Free trial (30 days)

### What Requires Payment:
- Modern calendar UI
- Templates
- AI features
- Advanced analytics

---

**Perfect SaaS Model:**
- Free tier gets them hooked
- Premium features clearly valuable
- Automatic fallback = no angry customers
- Easy upgrade path = conversion
- Server-side enforcement = no piracy

**If they stop paying, they just lose the premium features. Calendar still works (OpenEMR's).**

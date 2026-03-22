# Menu Links Added - MedEx Portal Integration

## ✅ Menu Items Created

### 1. **MedEx → Portal Messages** (Main Menu)
**Location:** Top menu under "MedEx" section  
**Link:** `interface/modules/custom_modules/oe-module-medex/public/portal_messages.php`  
**Access:** Users with "patients/portal" ACL permission  

**Features:**
- View all messages synced from MedEx to OpenEMR portal
- Sync statistics (total messages, last sync time)
- Recent messages table with preview
- Quick links to send secure chat and view portal
- Integration status dashboard

**How to Access:**
1. Log in to OpenEMR
2. Click "MedEx" in top menu
3. Select "Portal Messages"

---

### 2. **MedEx → Secure Chat** (Existing, Enhanced)
**Location:** Top menu under "MedEx" section  
**Link:** `interface/modules/custom_modules/oe-module-medex/public/secure_chat.php`  

**Features:**
- Search for patient
- Generate secure chat token
- Send link via SMS or Email
- Now includes option to send with `?use_portal=1` for portal redirect

**How to Access:**
1. Log in to OpenEMR
2. Click "MedEx" in top menu
3. Select "Secure Chat"

---

### 3. **Quick Chat Link** (Patient Chart Widget)
**Location:** Can be embedded in patient demographics  
**Link:** `interface/modules/custom_modules/oe-module-medex/public/quick_chat.php?pid={pid}`  

**Features:**
- Quick access from patient chart
- Pre-fills patient info
- Redirects to secure chat form
- JSON API for AJAX integration

**How to Use:**
- Direct link: Add button to patient demographics
- AJAX: `GET /quick_chat.php?pid=123` returns patient info + send URL
- POST: Redirects to secure chat with patient pre-selected

---

## Implementation Details

### Menu Registration
**File:** [openemr.bootstrap.php](../openemr.bootstrap.php#L105-L127)

```php
// Portal Messages viewer (shows synced messages)
$portalMessagesItem = new \stdClass();
$portalMessagesItem->requirement = 0;
$portalMessagesItem->target = 'med';
$portalMessagesItem->menu_id = 'medex_portal_messages';
$portalMessagesItem->label = xlt("Portal Messages");
$portalMessagesItem->url = $buildUrl('/interface/modules/custom_modules/oe-module-medex/public/portal_messages.php');
$portalMessagesItem->acl_req = ["patients", "portal"];
$medexTopMenu->children[] = $portalMessagesItem;
```

### Menu Structure
```
OpenEMR Main Menu
└── MedEx
    ├── MedEx Dashboard
    ├── SMS Bot (if enabled)
    ├── Secure Chat          ← Send secure chat links
    ├── Portal Messages       ← NEW: View synced messages
    ├── PDF Filler (if enabled)
    ├── Telehealth (if enabled)
    └── Calendar Feeds
```

---

## Usage Examples

### Example 1: Send Secure Chat (MedEx UI)
1. Navigate to **MedEx → Secure Chat**
2. Search for patient by name
3. Select delivery method (SMS/Email)
4. Click "Send Link"
5. Patient receives: `http://localhost/chat/secure/{token}`
6. Messages auto-sync to portal

### Example 2: Send Portal Redirect Link
1. Navigate to **MedEx → Secure Chat**
2. Search for patient
3. **Add `&use_portal=1` to the link manually** (or configure as default)
4. Send modified link: `http://localhost/chat/secure/{token}?use_portal=1`
5. Patient redirects to OpenEMR portal after verification

### Example 3: View Synced Messages
1. Navigate to **MedEx → Portal Messages**
2. See sync statistics
3. Browse recent synced messages
4. Click "View" to open specific message in portal
5. Monitor integration health

---

## Future Enhancements

### Planned Menu Additions
- [ ] **Patient Chart Button**: "Send Secure Chat" button in demographics sidebar
- [ ] **Message Center Integration**: Link from OpenEMR Messages to MedEx chat
- [ ] **Admin Settings**: MedEx module settings page for portal integration config
- [ ] **Reports Menu**: "Secure Chat Activity" report under Reports → MedEx

### Configurable Options
- [ ] Make `use_portal=1` a practice-level default toggle
- [ ] Add admin UI to enable/disable portal sync per practice
- [ ] Configure auto-redirect vs manual choice per user

---

## Testing Menu Links

### Test Portal Messages Page
```bash
# Must be logged in to OpenEMR first
# Navigate to: http://localhost:8300/interface/modules/custom_modules/oe-module-medex/public/portal_messages.php
```

### Test From Menu
1. Log in to OpenEMR at http://localhost:8300
2. Look for "MedEx" in top menu
3. Click → should see dropdown with:
   - Secure Chat
   - **Portal Messages** ← NEW

### Test Quick Chat API
```bash
# Get patient chat info (requires session)
curl http://localhost:8300/interface/modules/custom_modules/oe-module-medex/public/quick_chat.php?pid=1 \
  -H "Cookie: OpenEMR=your-session-cookie"

# Response:
{
  "success": true,
  "patient": {
    "pid": "1",
    "name": "John Doe",
    "phone_cell": "555-1234",
    "email": "john@example.com",
    "portal_enabled": true
  },
  "send_url": "/interface/modules/custom_modules/oe-module-medex/public/secure_chat.php?pid=1"
}
```

---

## Troubleshooting

### Menu Item Not Showing
1. **Check subscription**: Portal Messages only shows if `secure_chat` subscription enabled
2. **Clear cache**: `rm -rf sites/default/caches/*`
3. **Check ACL**: User needs "patients/portal" permission
4. **Reload page**: Hard refresh (Cmd+Shift+R or Ctrl+Shift+R)

### "Access Denied" Error
- Verify user has correct ACL permissions
- Check: Administration → ACL → Edit user → Enable "Portal" permission

### Page Loads Blank
- Check PHP error log: `docker logs openemr-8-0-1-dev-openemr-1`
- Verify `medex_chat_sync` table exists
- Ensure globals.php loads correctly

---

## Screenshots

*(Add screenshots after testing in browser)*

1. MedEx menu dropdown showing "Portal Messages"
2. Portal Messages dashboard
3. Synced messages table
4. Quick chat from patient chart

---

## Related Files

- [portal_messages.php](portal_messages.php) - Main dashboard page
- [quick_chat.php](quick_chat.php) - Patient chart widget
- [openemr.bootstrap.php](../openemr.bootstrap.php) - Menu registration
- [OPENEMR_PORTAL_INTEGRATION.md](../OPENEMR_PORTAL_INTEGRATION.md) - Technical docs
- [QUICK_START.md](../QUICK_START.md) - User guide

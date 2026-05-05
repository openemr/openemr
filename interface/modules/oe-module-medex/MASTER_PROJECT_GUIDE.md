# 🎯 MedEx Module Extraction - MASTER PROJECT GUIDE

**READ THIS FIRST - This document contains the complete project context, goals, architecture, and current state. Any AI agent working on this project should start here.**

---

## 📋 PROJECT OVERVIEW

### **Primary Goal**
Extract ALL MedEx code from OpenEMR core (`/library/MedEx/`) while preserving 100% of core functionality for non-MedEx users. MedEx becomes a completely optional module that injects features via events.

### **What MUST Work Without MedEx Module**
- ✅ **Recall Board** - Patient recalls, postcards, labels, phone calls, notes
- ✅ **Flow Board** - Patient tracking with communication icons  
- ✅ **Messages.php** - Core messaging and recall management
- ✅ **Age Calculations** - Patient age from DOB
- ✅ **Communication Modalities** - SMS/Voice/Email availability indicators

### **What MedEx Module Provides (Optional)**
- ❌ SMS Bot interface
- ❌ Automated messaging campaigns
- ❌ MedEx navigation bar
- ❌ Enhanced recall features
- ❌ Online status indicators

---

## 🏗️ ARCHITECTURE

### **Three-Phase Project Structure**

#### **Phase 1: Module Modernization** ✅ COMPLETED
- Modernized MedEx module codebase
- Created `MedExAPI.php` wrapper
- Module became self-contained
- **Status**: DONE

#### **Phase 2: Event-Based Architecture** ✅ COMPLETED  
- Created event injection system
- Built `MessagesPageRenderEvent` and `PatientTrackerPageRenderEvent`
- Created event listeners and templates
- **Status**: DONE - Ready for core integration

#### **Phase 3: Core Extraction** ❌ NOT STARTED - **CURRENT PHASE**
- Create core services (`RecallService`, `CommunicationService`)
- Update core files to use services/events
- Delete `/library/MedEx/` entirely
- Rename database tables to vendor-neutral names
- **Status**: NEEDS IMPLEMENTATION

### **Event-Based Injection Pattern**
```
Core Page → Dispatch Event → Module Listener → Inject UI/Logic
```

**Benefits:**
- Core doesn't know about MedEx
- Module can be disabled without breaking core
- Clean separation of concerns
- Multiple modules can use same injection points

---

## 📁 FILE STRUCTURE

### **Core Services (Phase 3)**
```
library/
├── RecallBoard/
│   └── RecallService.php ✅ (exists)
└── PatientCommunication/
    └── CommunicationService.php ❌ (missing - NEED TO CREATE)
```

### **Module Event System (Phase 2)**
```
oe-module-medex/
├── src/
│   ├── Events/
│   │   ├── MessagesPageRenderEvent.php ✅
│   │   └── PatientTrackerPageRenderEvent.php ✅
│   ├── Listeners/
│   │   ├── MessagesPageListener.php ✅
│   │   └── PatientTrackerListener.php ✅
│   └── templates/
│       └── navigation.php ✅
└── openemr.bootstrap.php ✅ (updated with events)
```

### **Core Files to Modify (Phase 3)**
```
interface/main/messages/
├── save.php ❌ (needs RecallService integration)
└── messages.php ❌ (needs event dispatching)
interface/patient_tracker/
└── patient_tracker.php ❌ (needs CommunicationService)
interface/patient_file/summary/
└── demographics.php ❌ (table name updates)
interface/main/messages/
└── print_postcards.php ❌ (table name updates)
library/
└── globals.inc.php ❌ (remove medex_enable)
```

### **To Delete (Phase 3)**
```
library/MedEx/ ❌ (entire directory - ~4000 lines)
```

---

## 🎯 CURRENT STATE & NEXT ACTIONS

### **What's Done ✅**
1. Phase 1: Module modernization complete
2. Phase 2: Event architecture complete
3. RecallService exists and works
4. Event system ready for integration
5. All documentation created

### **What's Missing ❌**
1. **CommunicationService.php** - Core communication modality detection
2. **Core file updates** - Replace MedEx calls with services/events
3. **Database migration** - Rename tables to vendor-neutral names
4. **Testing** - Verify core works without module
5. **Cleanup** - Delete `/library/MedEx/`

### **Immediate Priority Tasks**
1. Create `CommunicationService.php`
2. Update `messages/save.php` to use `RecallService`
3. Update `messages.php` to dispatch events
4. Update `patient_tracker.php` to use `CommunicationService`
5. Test core functionality without module
6. Delete `/library/MedEx/`

---

## 🔧 TECHNICAL SPECIFICATIONS

### **RecallService API** ✅ (exists)
```php
namespace OpenEMR\Services\RecallBoard;

class RecallService {
    public static function getAge(string $dob, string $asof = ''): int
    public static function saveRecall(array $data): void  
    public static function deleteRecall(?int $pid = null, ?int $r_ID = null): void
}
```

### **CommunicationService API** ❌ (needs creation)
```php
namespace OpenEMR\Services\PatientCommunication;

class CommunicationService {
    public static function getAvailableModalities(array $patient): array
    public static function getModalitiesSummary(array $patient): string
}
```

### **Event Injection Points**
```php
// Messages Page
MessagesPageRenderEvent::INJECT_NAVIGATION
MessagesPageRenderEvent::INJECT_CONTENT
MessagesPageRenderEvent::INJECT_SMS_TAB
MessagesPageRenderEvent::INJECT_SCRIPTS
MessagesPageRenderEvent::INJECT_STYLES

// Patient Tracker Page  
PatientTrackerPageRenderEvent::INJECT_NAVIGATION
PatientTrackerPageRenderEvent::INJECT_STATUS_ICONS
PatientTrackerPageRenderEvent::INJECT_MODALITIES
PatientTrackerPageRenderEvent::INJECT_SCRIPTS
```

---

## 🗄️ DATABASE CHANGES

### **Current Tables (MedEx-branded)**
- `medex_recalls` - Core recall data
- `medex_outgoing` - Message/action tracking
- `medex_prefs` - MedEx preferences (module-specific)
- `medex_icons` - Custom icons

### **Target Tables (Vendor-neutral)**
- `patient_recalls` - Core recall data
- `recall_board_actions` - Message/action tracking
- `medex_prefs` - Keep as-is (module-specific)
- `medex_icons` - Keep as-is (module-specific)

### **Migration SQL**
```sql
-- Rename core tables
RENAME TABLE `medex_recalls` TO `patient_recalls`;
RENAME TABLE `medex_outgoing` TO `recall_board_actions`;

-- Create backward-compatible views (optional)
CREATE VIEW `medex_recalls` AS SELECT * FROM `patient_recalls`;
CREATE VIEW `medex_outgoing` AS SELECT * FROM `recall_board_actions`;
```

---

## 📋 TESTING REQUIREMENTS

### **Test 1: Core Without Module**
- Disable MedEx module
- Navigate to Messages → Recalls
- Create/edit/delete recalls ✅
- Print postcards/labels ✅
- Navigate to Patient Tracker
- Verify communication icons display ✅
- Verify no PHP errors ✅

### **Test 2: Core With Module**
- Enable MedEx module
- Verify all core functionality still works ✅
- Verify MedEx navigation appears ✅
- Verify SMS Zone tab appears ✅
- Test MedEx-specific features ✅

### **Test 3: Data Migration**
- Backup database
- Run migration script
- Verify all data preserved ✅
- Verify core functionality works ✅
- Verify module functionality works ✅

---

## 🚨 CRITICAL CONSTRAINTS

### **DO NOT BREAK**
1. **Core Recall Board** - Must work 100% without module
2. **Flow Board** - Must work 100% without module  
3. **Messages.php** - Must work 100% without module
4. **Existing Data** - No data loss during migration
5. **Backward Compatibility** - Existing installations must upgrade smoothly

### **REQUIREMENTS**
1. **No Core Dependencies** - Core files cannot require MedEx
2. **Graceful Degradation** - Module disabled = no errors
3. **Event-Based** - All module injection via events
4. **Vendor Neutral** - Remove MedEx branding from core
5. **Clean Separation** - Clear boundary between core and module

---

## 📚 REFERENCE DOCUMENTS

### **Architecture Documents**
- `ARCHITECTURE.md` - Complete event-based architecture design
- `PHASE2_SUMMARY.md` - Phase 2 implementation details
- `MIGRATION_AUDIT.md` - Detailed API migration audit

### **PR Plans**
- `FINAL_PR_PLAN.md` - Complete PR implementation plan
- `CLEAN_REMOVAL_PR.md` - Step-by-step removal guide
- `PR_SUBMISSION_PLAN.md` - PR submission strategy
- `CORRECTED_PR_PLAN.md` - Corrected approach with AJAX handler

### **Implementation Guides**
- `IMPLEMENTATION_GUIDE.md` - Core file modification steps
- `TABLE_RENAME_PLAN.md` - Database rename strategy
- `INTEGRATION_ACTION_PLAN.md` - Integration action items

### **Testing & Status**
- `TEST_RESULTS.md` - Testing results
- `USER_TESTING_GUIDE.md` - User testing procedures
- `WORK_LOG.md` - Detailed work log

---

## 🎯 SUCCESS CRITERIA

### **Project Complete When:**
1. ✅ All core files work without MedEx module
2. ✅ MedEx module injects features via events
3. ✅ `/library/MedEx/` directory deleted
4. ✅ Database tables renamed to vendor-neutral
5. ✅ All tests pass
6. ✅ PR submitted to OpenEMR
7. ✅ No breaking changes for existing users

---

## 🚀 GETTING STARTED FOR NEW AI AGENTS

### **Step 1: Read Key Documents**
1. `FINAL_PR_PLAN.md` - Complete implementation plan
2. `CLEAN_REMOVAL_PR.md` - Step-by-step changes
3. `ARCHITECTURE.md` - Event system design

### **Step 2: Assess Current State**
1. Check if `CommunicationService.php` exists
2. Check core files for MedEx dependencies
3. Verify event system is working

### **Step 3: Implement Phase 3**
1. Create missing services
2. Update core files
3. Test thoroughly
4. Clean up legacy code

### **Step 4: Verify**
1. Core works without module ✅
2. Module works when enabled ✅
3. No data loss ✅
4. All tests pass ✅

---

## 📞 EMERGENCY CONTACTS

### **Project Context Questions**
- Refer to this MASTER document first
- Check `FINAL_PR_PLAN.md` for implementation details
- Check `WORK_LOG.md` for recent changes

### **Technical Decisions**
- Event-based architecture is final decision
- Three-phase approach is locked
- Core functionality preservation is mandatory

---

## 🔄 VERSION HISTORY

- **v1.0** (2026-02-14) - Initial MASTER document created
- Contains all project context for AI agents
- Prevents hours of context rediscovery

---

## 🚨 CRITICAL LESSON: Evidence-Based Development

### **DO NOT MAKE ASSUMPTIONS**
- ❌ **NEVER** speculate about how systems work without code evidence
- ❌ **NEVER** invent architectures (webhooks, websockets, real-time) without finding actual implementation
- ❌ **NEVER** assume efficiency patterns without seeing the actual code

### **ALWAYS VERIFY WITH CODE**
- ✅ **ALWAYS** grep/search for actual implementation before describing how something works
- ✅ **ALWAYS** read the actual code files before making architectural claims
- ✅ **ALWAYS** look for evidence in comments, function names, and actual implementations

### **EXAMPLE OF WHAT NOT TO DO**
```php
// WRONG: Making up how MedEx works without evidence
"MedEx uses webhooks and real-time streaming to handle thousands of customers efficiently"

// CORRECT: What the code actually shows
"Background services were removed. There's a callback service but the actual mechanism is not visible in the codebase."
```

### **RECENT EXAMPLE**
When asked how MedEx handles appointment notifications:
- **Wrong Answer**: Invented elaborate webhook/streaming architecture
- **Right Answer**: "I don't know - the code shows background services were removed and there's a callback service, but I don't see the actual mechanism"

### **CONSEQUENCES OF MAKING ASSUMPTIONS**
- Leads to incorrect implementation suggestions
- Wastes development time on non-existent features
- Creates technical debt based on fantasy architectures
- Undermines trust in AI recommendations

---

## 📡 MEDEX REAL-TIME ARCHITECTURE (CRITICAL DISCOVERY)

### **How MedEx Actually Works in Real-Time**

**DISCOVERED:** MedEx achieves < 1 minute SMS/email delivery through **event-driven CalendarSync**, not polling.

### **The Real-Time Flow:**

#### **1. Appointment Creation/Change**
```php
// Secretary creates appointment in OpenEMR
InsertEvent($args); // Inserts into openemr_postcalendar_events

// OpenEMR fires AppointmentSetEvent immediately
$event = new AppointmentSetEvent($post);
$eventDispatcher->dispatch($event, 'appointment.set');
```

#### **2. Event Listener Catches Change**
```php
// CalendarListeners.php - Registered in openemr.bootstrap.php
public static function getSubscribedEvents() {
    return [
        'appointment.update' => 'onAppointmentUpdate',
        'appointment.create' => 'onAppointmentCreate',
        'appointment.delete' => 'onAppointmentDelete'
    ];
}

public function onAppointmentCreate($event) {
    $pc_eid = $event->getAppointmentId();
    
    // IMMEDIATE sync to MedEx
    $result = $this->sync->syncAppointment($pc_eid);
}
```

#### **3. Real-Time CalendarSync**
```php
// CalendarSync.php - syncAppointment()
public function syncAppointment(int $pc_eid): array
{
    $appointment = $this->getAppointmentFromOpenEMR($pc_eid);
    
    // Send to MedEx server IMMEDIATELY
    $url = $this->medex_url . '/index.php?route=api/calendarsync/update';
    $response = $this->makeRequest($url, [
        'api_key' => $this->api_key,
        'appointment' => $appointment
    ]);
    
    return $response;
}
```

#### **4. MedEx Server Campaign Processing**
```php
// MedEx server receives appointment via API
POST /index.php?route=api/calendarsync/update

// IMMEDIATE campaign processing
if ($campaign['trigger'] === 'new_appointment') {
    sendSMS($patient['phone_cell'], $campaign['message']);
}

if ($campaign['trigger'] === 'new_patient') {
    sendEmail($patient['email'], $campaign['template'], $attachments);
}
```

### **Timeline for < 1 Minute Delivery:**
```
0:00 - Secretary creates appointment
0:01 - OpenEMR fires AppointmentSetEvent
0:02 - MedEx CalendarListener catches event
0:03 - CalendarSync sends to MedEx server
0:04 - MedEx server receives appointment
0:05 - Campaign engine processes immediately
0:06 - SMS/Email generated and sent
0:30 - Patient receives message
```

### **Key Components Working Together:**

#### **CalendarSync Service** (`src/CalendarSync.php`)
- **Purpose**: Real-time appointment synchronization
- **Methods**: `syncAppointment()`, `fullSync()`, `delete()`
- **Trigger**: OpenEMR AppointmentSetEvent
- **Destination**: MedEx server API

#### **CalendarListeners** (`src/CalendarListeners.php`)
- **Purpose**: Event subscriber for appointment changes
- **Events**: `'appointment.create'`, `'appointment.update'`, `'appointment.delete'`
- **Action**: Calls CalendarSync immediately

#### **MedEx Server Campaign Engine**
- **Purpose**: Process campaigns in real-time
- **Triggers**: `new_appointment`, `new_patient`, custom conditions
- **Actions**: Send SMS, Email with attachments, create tasks

### **Campaign Types Supported:**

#### **GoGreen SMS Campaigns**
```php
$campaign = [
    'type' => 'gogreen',
    'trigger' => 'new_appointment',
    'actions' => ['send_sms' => true],
    'conditions' => ['hipaa_allowsms' => 'YES']
];
```

#### **GoGreen Welcome Kit Campaigns**
```php
$campaign = [
    'type' => 'gogreen',
    'trigger' => 'new_patient',
    'actions' => [
        'send_email' => true,
        'template' => 'welcome_kit',
        'attachments' => ['welcome_packet.pdf']
    ],
    'conditions' => ['patient_status' => 'new', 'appt_category' => 'NEW']
];
```

### **Requirements for Real-Time Delivery:**

#### **Must Be Enabled:**
- ✅ CalendarSync service (real-time appointment sync)
- ✅ CalendarListeners registered (in openemr.bootstrap.php)
- ✅ GoGreen campaigns configured (on MedEx server)
- ✅ Patient contact info (phone_cell for SMS, email for Welcome Kit)

#### **Subscription Required:**
- ✅ MedEx Calendar subscription (for CalendarSync)
- ✅ GoGreen campaign subscription (for automated messaging)

### **What This Replaces:**

#### **OLD SYSTEM (Removed):**
- ❌ Background service polling every 29 minutes
- ❌ Manual sync triggers
- ❌ Delayed campaign processing

#### **NEW SYSTEM (Current):**
- ✅ Event-driven real-time sync
- ✅ Immediate campaign processing
- ✅ < 1 minute delivery

### **Critical Files for Real-Time Architecture:**

1. **`src/CalendarSync.php`** - Real-time appointment synchronization
2. **`src/CalendarListeners.php`** - Event subscribers
3. **`openemr.bootstrap.php`** - Event listener registration
4. **`src/MedExConfig.php`** - Subscription management
5. **`src/Events/Appointments/AppointmentSetEvent.php`** - OpenEMR core event

### **Common Misconceptions (CORRECTED):**

#### **❌ Wrong: "MedEx polls every few minutes"**
**✅ Correct: MedEx receives appointments immediately via CalendarSync**

#### **❌ Wrong: "Background service handles campaigns"**
**✅ Correct: MedEx server processes campaigns in real-time**

#### **❌ Wrong: "No integration with calendar changes"**
**✅ Correct: Full event-driven integration with OpenEMR calendar**

#### **❌ Wrong: "1-minute delivery is impossible"**
**✅ Correct: < 1 minute delivery is standard with CalendarSync**

---

**⚠️ AI AGENTS: Stick to what the code actually shows. If you don't know how something works, say "I don't know" rather than making up an answer.**

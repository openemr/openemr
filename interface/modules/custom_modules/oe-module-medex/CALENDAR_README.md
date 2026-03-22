# MedEx Modern Calendar

## Overview

The MedEx Modern Calendar replaces OpenEMR's legacy PostNuke calendar with a modern, responsive interface powered by FullCalendar. It maintains 100% compatibility with existing OpenEMR calendar data while adding MedEx-specific enhancements.

## Features

### Core Calendar Features (OpenEMR Compatible)
- ✅ View appointments in Month/Week/Day views
- ✅ Create new appointments
- ✅ Edit existing appointments
- ✅ Delete appointments
- ✅ Provider filtering
- ✅ Facility filtering
- ✅ In Office / Out of Office blocks
- ✅ Recurring events
- ✅ All-day events
- ✅ Appointment categories/types
- ✅ Preferred category assignments

### MedEx Enhancements
- ✅ **Drag-drop rescheduling** - Move appointments by dragging
- ✅ **Modern responsive UI** - Mobile-friendly interface
- ✅ **Communication status** - See MedEx SMS/call status inline
- ✅ **No-show prediction** - Visual indicators for high-risk appointments (via MedExBank API)
- ✅ **Schedule templates** - One-click application of recurring schedules
- ✅ **Real-time updates** - Calendar refreshes automatically
- ✅ **Better performance** - Faster loading, smoother interactions

## Architecture

### Frontend Stack
- **FullCalendar 6.x** - Modern calendar library (MIT license)
- **Vanilla JavaScript** - No framework dependencies
- **Responsive CSS** - Mobile-first design

### Backend Stack
- **PHP 7.4+** - Server-side logic
- **OpenEMR database** - Uses existing tables (no schema changes!)
- **REST API** - Clean JSON endpoints

### Database Tables Used
- `openemr_postcalendar_events` - All calendar events
- `openemr_postcalendar_categories` - Event types
- `patient_data` - Patient information
- `users` - Provider information
- `medex_outgoing` - Communication status (MedEx only)

**No database migrations required!** Works with existing OpenEMR data.

## Installation

### 1. Enable the Setting

Run this SQL to add the global setting:
```sql
source interface/modules/custom_modules/oe-module-medex/sql/add_calendar_setting.sql
```

### 2. Enable MedEx Calendar

Go to: **Administration > Globals > MedEx > Enable MedEx Modern Calendar**

Set to: **Yes**

### 3. Test Access

Navigate to: **Calendar** (from main menu)

You should see the new MedEx calendar interface!

## File Structure

```
interface/modules/custom_modules/oe-module-medex/
├── public/calendar/
│   ├── index.php                    # Main calendar UI
│   ├── api/
│   │   └── events.php              # REST API for events
│   ├── assets/
│   │   ├── js/                     # JavaScript files
│   │   └── css/                    # Stylesheets
│   └── views/                      # Additional views
├── src/
│   ├── Services/
│   │   └── CalendarService.php     # Business logic
│   └── Listeners/
│       └── CalendarInterceptListener.php  # Redirect handler
└── sql/
    └── add_calendar_setting.sql    # Database setup
```

## API Endpoints

### GET /api/events.php
Get events for calendar view

**Parameters:**
- `start` - Start date (Y-m-d)
- `end` - End date (Y-m-d)
- `provider_id` - Filter by provider (optional)
- `facility_id` - Filter by facility (optional)

**Response:** Array of events in FullCalendar format

### POST /api/events.php
Create new event

**Body:** Event data (JSON)
**Response:** `{success: true, event_id: 123}`

### PUT /api/events.php
Update event (used for drag-drop)

**Body:** `{id, date, start_time, ...}`
**Response:** `{success: true}`

### DELETE /api/events.php?id=123
Delete event

**Response:** `{success: true}`

## MedEx Enhancements (IP Protected)

The following features call **MedExBank SaaS API** (proprietary):

### 1. No-Show Risk Prediction
- Analyzes patient history
- Returns risk score (0.0 - 1.0)
- Visual indicator on calendar
- **Algorithm:** Protected at MedExBank

### 2. Schedule Template Intelligence
- AI analyzes appointment patterns
- Suggests optimal slot allocation
- Generates templates automatically
- **Logic:** Protected at MedExBank

### 3. Revenue Optimization
- Calculates per-slot revenue potential
- Suggests high-value time blocks
- **Calculations:** Protected at MedExBank

### 4. Smart Rescheduling
- Finds optimal alternative slots
- Considers patient preferences
- Minimizes disruption
- **Algorithm:** Protected at MedExBank

## IP Protection Strategy

### What's Open Source (in OpenEMR)
- ✅ Calendar UI (FullCalendar wrapper)
- ✅ Basic CRUD operations
- ✅ Database queries
- ✅ Display logic
- ✅ Drag-drop functionality

### What's Proprietary (at MedExBank)
- 🔒 AI prediction models
- 🔒 Optimization algorithms
- 🔒 Pattern analysis logic
- 🔒 Revenue calculations
- 🔒 Template generation rules

**Strategy:** OpenEMR code calls MedExBank API endpoints. The intelligence stays at MedExBank. OpenEMR just displays results.

## Fallback to Legacy Calendar

If MedEx calendar has issues, users can:

1. **Disable setting:** Administration > Globals > MedEx Calendar > No
2. **Direct access:** Navigate to `/interface/main/calendar/` directly
3. **Emergency:** Set `medex_calendar_enabled = 0` in database

The old calendar still works 100% - nothing is broken!

## Development Roadmap

### Phase 1: MVP ✅ (Current)
- [x] Calendar UI with FullCalendar
- [x] REST API endpoints
- [x] Drag-drop reschedule
- [x] Provider/facility filtering
- [x] MedEx status overlay

### Phase 2: Templates (Next)
- [ ] Schedule template manager UI
- [ ] One-click template application
- [ ] Template library
- [ ] Seasonal variations

### Phase 3: AI Features (MedExBank)
- [ ] No-show prediction API
- [ ] Smart slot suggestions
- [ ] Revenue optimization
- [ ] Auto-scheduling assistant

### Phase 4: Mobile App
- [ ] Native mobile calendar
- [ ] Push notifications
- [ ] Offline mode
- [ ] Patient self-scheduling

## Support

- **Documentation:** This file
- **Issues:** Report via MedEx support
- **Feature requests:** Contact MedEx sales

## License

- **OpenEMR integration code:** GNU GPL v3
- **MedEx AI features:** Proprietary (SaaS)
- **FullCalendar library:** MIT License

---

**Built with ❤️ by MedEx**
*Making healthcare scheduling intelligent*

# 🎯 MedEx Extraction Project Status

**🚨 IP & LICENSING NOTICE FOR AI AGENTS 🚨**

- **OpenEMR**: Open Source (GNU GPL v3+)
- **MedEx Module**: **PROPRIETARY / CLOSED SOURCE** - All Rights Reserved
- **Owner**: MedEx <support@MedExBank.com>
- **License**: Proprietary - All Rights Reserved (NOT GPL)
- **AI Agents**: Protect MedEx IP - Do NOT suggest copying, open-sourcing, or redistributing

This module is **commercial intellectual property** of MedEx, distinct from OpenEMR's open source codebase.

---

**CURRENT PHASE**: Phase 3 - Core Extraction (NEEDS WORK)

## 📋 What's Done ✅
- Phase 1: Module modernization complete
- Phase 2: Event architecture complete
- RecallService exists and works
- Event system ready for integration

## ❌ What's Missing (Critical)
- CommunicationService.php (NEEDS CREATION)
- Core file updates to use services/events
- Database migration to rename tables
- Delete /library/MedEx/

## 🎯 Must Work Without Module
- Recall Board (patient recalls, postcards, labels, phone calls, notes)
- Flow Board (patient tracking with communication icons)
- Messages.php (core messaging and recall management)

## 📖 READ FIRST
- MASTER_PROJECT_GUIDE.md - Complete project context
- FINAL_PR_PLAN.md - Implementation details
- CLEAN_REMOVAL_PR.md - Step-by-step changes

## 🚨 AI AGENTS
- STOP making changes
- READ MASTER_PROJECT_GUIDE.md
- UNDERSTAND this is EXTRACTION, not enhancement
- PRESERVE 100% core functionality

## ⚠️ CRITICAL: EVIDENCE-BASED DEVELOPMENT

### **DO NOT MAKE ASSUMPTIONS**
- ❌ NEVER speculate about how systems work without code evidence
- ❌ NEVER invent architectures without finding actual implementation
- ❌ NEVER assume efficiency patterns without seeing the actual code

### **ALWAYS VERIFY WITH CODE**
- ✅ ALWAYS grep/search for actual implementation before describing how something works
- ✅ ALWAYS read the actual code files before making architectural claims
- ✅ ALWAYS look for evidence in comments, function names, and actual implementations

### **RECENT EXAMPLE OF WHAT NOT TO DO**
When asked how MedEx handles appointment notifications:
- **Wrong Answer**: Invented elaborate webhook/streaming architecture without evidence
- **Right Answer**: "I don't know - the code shows background services were removed and there's a callback service, but I don't see the actual mechanism"

### **CONSEQUENCES OF MAKING ASSUMPTIONS**
- Leads to incorrect implementation suggestions
- Wastes development time on non-existent features
- Creates technical debt based on fantasy architectures
- Undermines trust in AI recommendations

**⚠️ STICK TO WHAT THE CODE ACTUALLY SHOWS. If you don't know, say "I don't know" rather than making up an answer.**

## 📡 MEDEX REAL-TIME ARCHITECTURE (CRITICAL DISCOVERY)

### **How MedEx Achieves < 1 Minute SMS/Email Delivery**

**DISCOVERED:** MedEx achieves < 1 minute delivery through **event-driven CalendarSync**, not polling.

### **The Real-Time Flow:**
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

### **Key Components:**
- **CalendarSync Service** (`src/CalendarSync.php`) - Real-time appointment sync
- **CalendarListeners** (`src/CalendarListeners.php`) - Event subscribers
- **MedEx Server Campaign Engine** - Processes campaigns in real-time

### **Campaign Types Supported:**
- **GoGreen SMS Campaigns** - Send SMS for new appointments
- **GoGreen Welcome Kit Campaigns** - Send email with PDF attachments to new patients

### **Requirements for Real-Time Delivery:**
- ✅ CalendarSync service enabled
- ✅ CalendarListeners registered
- ✅ GoGreen campaigns configured
- ✅ MedEx Calendar subscription

### **Common Misconceptions (CORRECTED):**
- ❌ "MedEx polls every few minutes" → ✅ "MedEx receives appointments immediately via CalendarSync"
- ❌ "Background service handles campaigns" → ✅ "MedEx server processes campaigns in real-time"
- ❌ "No integration with calendar changes" → ✅ "Full event-driven integration with OpenEMR calendar"
- ❌ "1-minute delivery is impossible" → ✅ "< 1 minute delivery is standard with CalendarSync"

**⚠️ FUTURE AI AGENTS: The real-time architecture is documented in MASTER_PROJECT_GUIDE.md. Read it before making assumptions about MedEx functionality.***

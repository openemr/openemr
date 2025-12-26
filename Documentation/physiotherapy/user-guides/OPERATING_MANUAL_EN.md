# Vietnamese Physiotherapy Module - Operating Manual (English)

**Complete User Guide for Healthcare Providers**
*OpenEMR Vietnamese Physiotherapy Module v1.0*
*November 2025*

---

## Table of Contents

1. [Quick Start Guide](#1-quick-start-guide)
2. [Module Overview](#2-module-overview)
3. [System Requirements](#3-system-requirements)
4. [Getting Access](#4-getting-access)
5. [Patient Summary Widget](#5-patient-summary-widget)
6. [PT Assessment Form](#6-pt-assessment-form)
7. [Exercise Prescription Form](#7-exercise-prescription-form)
8. [Treatment Plan Form](#8-treatment-plan-form)
9. [Outcome Measures Form](#9-outcome-measures-form)
10. [Bilingual Features](#10-bilingual-features)
11. [Best Practices and Workflows](#11-best-practices-and-workflows)
12. [Troubleshooting](#12-troubleshooting)
13. [Quick Reference](#13-quick-reference)
14. [Appendices](#14-appendices)

---

## 1. Quick Start Guide

### What is the Vietnamese Physiotherapy Module?

The Vietnamese Physiotherapy (PT) Module is a comprehensive, bilingual clinical system integrated into OpenEMR. It provides specialized tools for physiotherapy documentation and patient management with full Vietnamese language support.

**Key Components:**
- 4 clinical forms for assessment, exercise prescription, treatment planning, and outcome tracking
- Bilingual interface (English/Vietnamese) with parallel documentation
- Patient summary widget for quick access to PT data
- Built-in medical terminology translation
- Support for Vietnamese healthcare practices and terminology

**Who Uses This?**
- Physiotherapists and Physical Therapists
- Rehabilitation specialists
- Healthcare providers treating Vietnamese-speaking patients
- Clinics and hospitals using OpenEMR

### Prerequisites

Before using the PT module, ensure you have:

- [ ] Active OpenEMR user account with appropriate permissions
- [ ] Physiotherapy module access enabled by system administrator
- [ ] Basic familiarity with OpenEMR navigation
- [ ] Understanding of patient encounter workflow
- [ ] Vietnamese keyboard input capability (optional, but recommended)

### First Steps (30 seconds)

1. **Log into OpenEMR** with your credentials
2. **Open a patient chart** (Calendar → Select appointment → Click patient, or use Search)
3. **Create/Open an encounter** (Patient Dashboard → New Encounter)
4. **Look for "Vietnamese PT" forms** in the Clinical menu under "Add Form"

That's it! You're ready to start documenting physiotherapy care.

---

## 2. Module Overview

### What Does This Module Do?

This module extends OpenEMR with specialized physiotherapy documentation capabilities:

| Feature | Purpose |
|---------|---------|
| **PT Assessment Form** | Document patient evaluations, chief complaints, pain, and functional goals |
| **Exercise Prescription** | Create and manage home exercise programs with bilingual instructions |
| **Treatment Plan** | Define treatment strategy, timeline, and status tracking |
| **Outcome Measures** | Track objective progress (ROM, strength, pain, function, balance) |
| **PT Widget** | Quick-access view of patient's PT history on summary page |

### How It Integrates with OpenEMR

The Vietnamese PT module integrates seamlessly with OpenEMR:

- **Patient Encounters:** Forms are added like any other encounter form
- **Patient Summary:** Widget displays PT data on patient dashboard
- **Medical Records:** All documentation stored in OpenEMR database
- **Reporting:** Data accessible through OpenEMR reports system
- **User Permissions:** Uses OpenEMR's role-based access control

### Bilingual Support

All forms support parallel documentation in both languages:

- **Vietnamese (Tiếng Việt):** For Vietnamese-speaking patients and documentation
- **English:** For medical records, team communication, research
- **Language Preference:** Each form lets you choose Vietnamese-only, English-only, or both

---

## 3. System Requirements

### Browser Requirements

- **Modern browser:** Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **JavaScript enabled:** Required for form functionality and pain indicators
- **Cookies enabled:** Required for OpenEMR session management
- **Screen resolution:** 1024x768 minimum (1280x1024+ recommended)

### Character Encoding

- **UTF-8 encoding:** Required for Vietnamese characters to display correctly
- **Browser encoding:** Verify page encoding is set to Unicode (UTF-8)
- **Vietnamese keyboard:** Recommended for efficient data entry

### Internet Connection

- **Stable connection:** Ensure reliable internet while working
- **Minimum bandwidth:** Standard broadband (1 Mbps+)
- **Connection timeout:** Forms may time out after 15-20 minutes of inactivity

### System Administrator Configuration

Your system administrator should have:

- [ ] Vietnamese PT module installed and activated
- [ ] Database tables created with UTF-8mb4_vietnamese_ci collation
- [ ] User permissions configured for physiotherapy access
- [ ] Forms registered in OpenEMR system
- [ ] Patient summary widget enabled

---

## 4. Getting Access

### User Permissions Required

Contact your system administrator to request:

1. **Physiotherapy Form Access:** Permission to view, create, and edit PT forms
2. **Encounter Management:** Permission to create and modify encounters
3. **Patient Data Access:** Access to patient records and summaries
4. **Documentation Rights:** Permission to complete clinical documentation

### Accessing the Module

#### From Patient Encounter

**Method 1: Add New Form**
1. Open patient chart
2. Select or create an encounter
3. Click "Clinical" tab in encounter menu
4. Click "Add Form" dropdown
5. Select desired Vietnamese PT form:
   - Vietnamese PT Assessment
   - Vietnamese PT Exercise Prescription
   - Vietnamese PT Treatment Plan
   - Vietnamese PT Outcome Measures

**Method 2: Quick Add from Widget**
1. Open patient summary page
2. Scroll to "Vietnamese Physiotherapy" section
3. Click green "+ New" button next to desired section
4. Form opens in current encounter

#### From Patient Summary

**View PT Widget:**
1. Navigate to patient chart
2. Click "Summary" or "Patient Dashboard"
3. Scroll down to "Vietnamese Physiotherapy" section
4. View recent data and quick add buttons

---

## 5. Patient Summary Widget

### Widget Overview

The Patient Summary Widget provides at-a-glance access to a patient's physiotherapy history and current status.

### Accessing the Widget

1. **Open patient chart** (search patient or select from calendar)
2. **Click "Summary"** tab or "Patient Dashboard"
3. **Scroll down** to green "Vietnamese Physiotherapy" section
4. Widget displays three key sections with quick action buttons

### Widget Sections

#### Section A: Recent Assessments

**What It Shows:**
- Last 3 PT assessments
- Assessment date
- Chief complaint (abbreviated)
- Pain level (0-10 with color coding)
- Status (Draft/Completed/Reviewed)

**Color Coding:**
- Green badge = Low pain (0-3)
- Yellow badge = Moderate pain (4-6)
- Red badge = High pain (7-10)

**Quick Actions:**
- Click "+ New" button → Create new assessment
- Click assessment row → View/edit details

#### Section B: Active Exercise Prescriptions

**What It Shows:**
- Up to 5 active exercises
- Exercise name (Vietnamese or English)
- Sets, reps, frequency (e.g., "3 sets × 10 reps - 5x/week")
- Intensity level (Low/Moderate/High)

**Exercise Status:**
- Only shows exercises with active dates
- End date not yet reached
- Marked as "Active"

**Quick Actions:**
- Click "+ New" button → Add new exercise
- Click exercise row → View/edit details

#### Section C: Active Treatment Plans

**What It Shows:**
- All active treatment plans
- Plan name
- Start date
- Estimated duration (weeks)
- Status (Active/Completed/On Hold)

**Quick Actions:**
- Click "+ New" button → Create new plan
- Click plan row → View/edit details

### Using Quick Add Buttons

**To Create New PT Data Using Widget:**

1. Click green "+ New" button next to desired section
2. Form opens in current encounter (or creates new encounter if needed)
3. Complete the form as described in sections 6-9
4. Click "Save [Form Name]" button
5. Return to widget and refresh (F5) to see updated data

**Benefits:**
- Skip navigating through menus
- One-click access directly to form
- Faster documentation during patient visits

### When Widget Shows "No Data"

If a section displays "No assessments recorded" or similar:

**This is normal when:**
- Patient has never had PT assessment
- All previous exercises have ended
- Treatment plans have been completed and closed

**To populate the widget:**
1. Create first PT form using "+ New" button
2. Complete and save the form
3. Widget updates automatically to show new data

### Refreshing Widget Data

The widget automatically updates when you:
- Create new PT forms
- Save changes to existing forms
- Update exercise status
- Change treatment plan status

If recent changes don't appear:
- Refresh the page (F5 or Ctrl+R)
- Navigate away and back to patient summary

---

## 6. PT Assessment Form

### Purpose

The PT Assessment Form is the primary documentation tool for physiotherapy patient evaluations. It captures:
- Patient's chief complaint and symptom description
- Pain level and pain characteristics
- Functional limitations and goals
- Proposed treatment approach
- Clinical status

### When to Use

- Initial patient evaluation (must complete)
- Progress re-assessments (every 2-4 weeks)
- Discharge evaluation (final visit)
- Significant status changes

### Accessing the Form

**Option 1: From Encounter Menu**
1. Open patient encounter
2. Click "Clinical" tab
3. Click "Add Form" → "Vietnamese PT Assessment"

**Option 2: From PT Widget**
1. Open patient summary
2. Scroll to "Vietnamese Physiotherapy"
3. Click "+ New" in "Recent Assessments" section

### Completing the Assessment Form

#### Step 1: Select Language Preference

At the top of the form, choose your documentation language:

- **Vietnamese (Tiếng Việt):** Only Vietnamese fields display (yellow background)
- **English:** Only English fields display (blue background)
- **Both (Recommended):** Both Vietnamese and English fields display side-by-side

**Recommendation:**
Select "Both" for comprehensive bilingual documentation that serves both Vietnamese-speaking patients and the medical record.

#### Step 2: Document Chief Complaint

**Field Type:** Large text area with language-specific sections

**Vietnamese Field (Yellow Background):**
```
Example: Đau lưng dưới mãn tính từ 6 tháng,
tăng khi ngồi lâu hoặc nâng vật nặng
```

**English Field (Blue Background):**
```
Example: Chronic low back pain for 6 months,
worse with prolonged sitting or lifting heavy objects
```

**Best Practices:**
- Be specific about symptoms
- Include duration and triggers
- Describe functional impact
- Use professional medical terminology
- Complete both languages when language preference is "Both"

#### Step 3: Pain Assessment

**Pain Level (0-10 Scale):**
1. Enter numeric value (0 = no pain, 10 = worst pain)
2. Visual indicator updates automatically:
   - 0-3: Green badge (mild pain)
   - 4-6: Yellow badge (moderate pain)
   - 7-10: Red badge (severe pain)

**Pain Location:**

Vietnamese Example:
```
Lưng dưới bên phải, có thể lan xuống mông
```

English Example:
```
Lower right back, may radiate to buttock
```

**Pain Description:**

Vietnamese Example:
```
Đau nhói, tăng khi ngồi lâu, giảm khi nằm
```

English Example:
```
Sharp pain, worse with prolonged sitting, better with rest
```

**Common Pain Descriptors:**
- đau nhói = sharp/stabbing pain
- đau âm ỉ = dull/aching pain
- đau rát = burning pain
- đau buốt = throbbing pain
- đau lan tỏa = radiating pain

#### Step 4: Functional Goals

Document measurable, patient-centered goals:

**Vietnamese Example:**
```
- Có thể đi bộ 30 phút không đau
- Ngủ suốt đêm không bị đau đánh thức
- Ngồi làm việc 2 giờ liên tục
- Trở lại tập gym hoặc thể thao nhẹ
```

**English Example:**
```
- Walk 30 minutes without pain
- Sleep through the night
- Sit for 2 hours of work
- Return to light gym or sports activities
```

**Goal-Setting Tips:**
- Make goals specific and measurable
- Include timeframe when possible
- Focus on functional activities (not just pain reduction)
- Ask patient input for realistic goals
- Include at least 2-3 goals

#### Step 5: Treatment Plan Summary

Outline the proposed treatment approach:

**Vietnamese Example:**
```
- Vật lý trị liệu 3 lần/tuần trong 8 tuần
- Bài tập kéo giãn và tăng cường
- Giáo dục tư thế ngồi đúng
- Modalities như TENS nếu cần
- Tái đánh giá sau 2 tuần
```

**English Example:**
```
- Physical therapy 3x/week for 8 weeks
- Stretching and strengthening exercises
- Posture and body mechanics education
- Modalities such as TENS if needed
- Re-evaluation in 2 weeks
```

**Treatment Plan Should Include:**
- Treatment frequency and duration
- Types of interventions (manual therapy, exercises, modalities)
- Education topics
- Re-evaluation timeline
- Precautions or contraindications

#### Step 6: Set Assessment Status

Select the appropriate status:

| Status | Meaning | When to Use |
|--------|---------|------------|
| Draft | Form incomplete, needs more work | Interrupted assessment, incomplete documentation |
| Completed | Assessment is final and complete | Ready for clinical use, assessment done |
| Reviewed | Assessment has been reviewed/approved | Peer review completed, supervisor signed off |

Default: "Completed"

#### Step 7: Save the Assessment

**To Save:**
1. Verify all required information is complete
2. Click blue "Save Assessment" button at bottom of form
3. System saves data and returns to encounter page
4. Assessment appears in encounter forms list

**To Cancel Without Saving:**
1. Click gray "Cancel" button
2. Returns to previous page without saving changes
3. Unsaved work will be lost

### Viewing or Editing Existing Assessments

**To View/Edit Assessment:**
1. Navigate to patient encounter
2. Find assessment in encounter forms list
3. Click assessment title
4. Form opens with existing data pre-filled
5. Make changes as needed
6. Click "Save Assessment" to update

**To View in PT Widget:**
1. Open patient summary page
2. Scroll to "Recent Assessments" section
3. Click assessment row to view details

---

## 7. Exercise Prescription Form

### Purpose

The Exercise Prescription Form allows you to create detailed, bilingual exercise programs with specific parameters (sets, reps, frequency, intensity) and instructions.

### When to Use

- Initial exercise prescription (at start of treatment)
- Adding new exercises to program
- Modifying existing exercises (sets, reps, intensity)
- Progressing exercise difficulty
- Patient education and home program handouts

### Accessing the Form

**Option 1: From Encounter Menu**
1. Open patient encounter
2. Click "Clinical" tab
3. Click "Add Form" → "Vietnamese PT Exercise Prescription"

**Option 2: From PT Widget**
1. Open patient summary
2. Scroll to "Vietnamese Physiotherapy"
3. Click "+ New" in "Active Exercise Prescriptions" section

### Completing the Exercise Prescription

#### Step 1: Enter Exercise Name (Bilingual)

Provide clear exercise names in both languages:

**Vietnamese Example:**
```
Động tác mèo-bò (Cat-Cow)
```

**English Example:**
```
Cat-Cow Stretch
```

**Best Practices:**
- Use professional exercise terminology
- Include English name in parentheses for common exercises
- Be consistent with naming across prescriptions
- Avoid slang or colloquial terms

#### Step 2: Describe Exercise Technique

Provide clear, step-by-step instructions for performing the exercise:

**Vietnamese Example:**
```
Quỳ bốn chân, tay thẳng dưới vai, đầu gối dưới hông.
Hít vào sâu: vùng lưng xuống, ngẩng đầu lên (tư thế bò).
Thở ra: gù lưng lên, cúi đầu xuống (tư thế mèo).
Chuyển động chậm, mượt mà, không gập gật.
Lặp lại 10 lần, 2 lần mỗi ngày.
```

**English Example:**
```
Start on hands and knees, hands under shoulders, knees under hips.
Inhale: Drop belly down, look up (cow pose).
Exhale: Round spine, tuck chin (cat pose).
Move slowly and smoothly, avoid jerky movements.
Repeat 10 times, twice daily.
```

**Effective Descriptions Should:**
- Include starting position
- Describe movement clearly
- Note breathing cues
- Include modifications if applicable
- Be readable at patient education level

#### Step 3: Set Exercise Parameters

Configure the exercise prescription details:

| Parameter | Field Name | Type | Range | Default | Example |
|-----------|-----------|------|-------|---------|---------|
| Sets | Sets (Hiệp) | Number | 1-10 | 3 | 3 |
| Repetitions | Reps (Số lần) | Number | 1-50 | 10 | 10 |
| Duration | Duration (Thời gian) | Minutes | Optional | - | 5 |
| Frequency | Frequency Per Week | Days | 1-7 | 5 | 5 |
| Intensity | Intensity Level | Dropdown | Low/Moderate/High | Moderate | Moderate |

**Setting Frequency:**
- Enter number of days per week to perform exercise
- Example: 5 = perform Monday through Friday
- Example: 7 = perform every day
- Example: 2 = perform twice weekly

**Setting Intensity Levels:**
- **Low:** Initial rehab, gentle exercises, acute pain phase
- **Moderate:** Standard therapeutic exercises, most common (default)
- **High:** Advanced patients, strengthening phase, pain-free movements

#### Step 4: Set Date Range

Configure when the exercise should be performed:

**Start Date:**
- Default: Today's date
- Click calendar icon to select different date
- This is when patient should begin the exercise

**End Date (Optional):**
- Leave blank for ongoing exercises
- Set specific date if exercise is temporary
- Exercise becomes "inactive" after end date
- Example: Set 4-week end date for progressive loading

#### Step 5: Add Exercise Instructions

Provide additional guidance and safety notes:

**Vietnamese Example:**
```
- Thực hiện vào buổi sáng khi thức dậy
- Có thể thực hiện trên giường hoặc sàn
- Dừng lại nếu cảm thấy đau tăng
- Kết hợp với hơi thở sâu
- Tăng số lần nếu cảm thấy quá dễ
```

**English Example:**
```
- Perform in morning when waking up
- Can be done on bed or floor
- Stop if pain increases significantly
- Combine with deep breathing
- Increase repetitions if exercise feels too easy
```

**Effective Instructions Include:**
- Best time to perform
- Where to perform (location)
- When to stop or modify
- How to progress when ready
- Environmental considerations

#### Step 6: Specify Equipment Needed

List any equipment required for the exercise:

**Examples:**
- Yoga mat
- Resistance band
- Dumbbells or weights (specify weight)
- Chair for support
- Pillow or cushion
- Towel roll

**Format:**
```
Thảm yoga, ghế tựa, gối
```
or
```
Yoga mat, chair for support, pillow
```

#### Step 7: Document Safety Precautions

Describe any warnings or contraindications:

**Vietnamese Example:**
```
- Không làm nếu có đau cấp tính
- Tránh động tác gật gù đầu quá mạnh
- Giữ cột sống trung tính, không quá võng
- Ngừng nếu có tê hoặc chân yếu
```

**English Example:**
```
- Do not perform if acute pain present
- Avoid excessive neck bending
- Keep spine neutral, don't over-arch
- Stop if numbness or weakness occurs
```

**Precautions Should Cover:**
- Pain-related contraindications
- Movement restrictions
- Neurological symptoms to watch for
- When to contact therapist
- Specific positions to avoid

#### Step 8: Save the Exercise Prescription

1. Verify all required fields completed
2. Click blue "Save Exercise Prescription" button
3. System saves data and returns to encounter
4. Exercise appears in active list if dates are current

**For Multiple Exercises:**
1. Create each exercise separately
2. Use same start date for exercises in same program
3. Use same or related end dates for program cohesion
4. Vary intensity for progressive challenge

### Managing Exercise Programs

#### Creating a Complete Home Exercise Program

Example: Lower back pain, 4-week program

**Exercise 1: Pelvic Tilts**
- Sets: 3 | Reps: 15 | Frequency: 7x/week | Intensity: Low
- Duration: Weeks 1-4
- Purpose: Core activation, pain relief

**Exercise 2: Cat-Cow**
- Sets: 2 | Reps: 10 | Frequency: 7x/week | Intensity: Low
- Duration: Weeks 1-4
- Purpose: Spinal mobility, flexibility

**Exercise 3: Dead Bug**
- Sets: 3 | Reps: 10 | Frequency: 5x/week | Intensity: Moderate
- Duration: Weeks 2-4
- Purpose: Core strengthening, stability

**Exercise 4: Bridge Hold**
- Sets: 3 | Reps: 8-10 | Frequency: 5x/week | Intensity: Moderate
- Duration: Weeks 3-4
- Purpose: Glute strengthening, power

### Progression Guidelines

**When to Progress Exercise:**
- Patient performing exercise without difficulty
- No pain with current level
- 2+ weeks at current intensity
- Patient reports exercise feels "too easy"

**How to Progress:**
1. Increase number of sets (2 → 3)
2. Increase number of reps (10 → 15)
3. Increase frequency (5x/week → 7x/week)
4. Increase intensity level (Low → Moderate or Moderate → High)
5. Add duration for timed exercises
6. Add resistance (weights, bands)

**To Update Exercise:**
1. Open existing exercise prescription
2. Modify relevant parameters
3. Save updated prescription
4. Note progression in assessment or notes

---

## 8. Treatment Plan Form

### Purpose

The Treatment Plan Form provides structured documentation for overall treatment strategy, including:
- Patient's diagnosis or primary condition
- Treatment timeline and duration
- Treatment goals and status
- Periodic monitoring and updates

### When to Use

- Initial treatment planning (required for new patients)
- At start of each treatment phase
- Status changes (Active → Completed, etc.)
- Multi-phase treatments

### Accessing the Form

**Option 1: From Encounter Menu**
1. Open patient encounter
2. Click "Clinical" tab
3. Click "Add Form" → "Vietnamese PT Treatment Plan"

**Option 2: From PT Widget**
1. Open patient summary
2. Scroll to "Vietnamese Physiotherapy"
3. Click "+ New" in "Active Treatment Plans" section

### Completing the Treatment Plan

#### Step 1: Name the Treatment Plan

Provide a descriptive name for the overall treatment:

**Examples:**
```
Lower Back Pain Rehabilitation - Phase 1
Phục hồi chức năng đau lưng - Giai đoạn 1
```

```
Post-Surgical Knee Rehab - Week 1-6
Phục hồi sau phẫu thuật đầu gối - Tuần 1-6
```

```
Stroke Recovery - Motor Control Training
Phục hồi chức năng sau đột quỵ - Huấn luyện kiểm soát vận động
```

**Best Practices:**
- Include primary condition and phase (if multi-phase)
- Be descriptive but concise
- Include timeframe if known
- Use consistent naming conventions

#### Step 2: Document Diagnosis

Enter the patient's primary diagnosis in both languages:

**Vietnamese Field Example:**
```
Thoát vị đĩa đệm L4-L5 với chèn ép rễ thần kinh S1
```

**English Field Example:**
```
L4-L5 disc herniation with S1 nerve root compression
```

**Common Vietnamese PT Diagnoses:**

| English | Vietnamese |
|---------|-----------|
| Low back pain (nonspecific) | Đau lưng dưới không xác định nguyên nhân |
| Cervical strain | Căng cơ cổ tay |
| Knee osteoarthritis | Viêm khớp gối |
| Rotator cuff tear | Rách dây quay |
| Carpal tunnel syndrome | Hội chứng ống cổ tay |
| Anterior cruciate ligament (ACL) tear | Rách dây chằng chéo trước |
| Bell's palsy | Liệt nửa mặt |
| Stroke (CVA) | Đột quỵ |

**Diagnosis Documentation Tips:**
- Use specific medical terminology
- Include laterality (left/right) if applicable
- Include severity if known
- Distinguish between diagnosis and symptoms

#### Step 3: Set Treatment Timeline

Configure when treatment will occur and expected duration:

**Start Date:**
- Default: Today's date
- Click calendar icon to select different date
- Should match when treatment actually begins

**Estimated Duration (Weeks):**
- Enter number of weeks for treatment
- Range: 1-52 weeks

**Common Treatment Durations:**

| Condition Category | Duration |
|--------------------|----------|
| Acute pain episode | 2-4 weeks |
| Subacute condition | 4-8 weeks |
| Chronic condition | 8-12 weeks |
| Post-surgical rehab (simple) | 6-8 weeks |
| Post-surgical rehab (complex) | 12-24 weeks |
| Neurological recovery | 12+ weeks |

**Setting Accurate Durations:**
- Base on typical condition timeline
- Account for patient compliance
- Allow for progress plateaus
- Plan for re-evaluation intervals
- Document any deviations during treatment

#### Step 4: Set Treatment Plan Status

Select the appropriate status:

| Status | Meaning | When to Use | Action |
|--------|---------|------------|--------|
| **Active** | Treatment plan is current | New plans, ongoing treatment | Default selection |
| **On Hold** | Treatment temporarily paused | Patient travel, medical hold, other reasons | Temporary pause, plan to resume |
| **Completed** | Patient finished plan | Goals achieved, patient discharged | Permanent closure of plan |

**Status Management:**
- Create plan with "Active" status when treatment begins
- Change to "On Hold" if patient pauses treatment
- Change to "Completed" when patient finishes treatment
- Can reactivate "On Hold" plans if treatment resumes

#### Step 5: Link to Goals (Reference)

While treatment goals are documented elsewhere, reference them:

**Where Goals Are Documented:**
- PT Assessment → Functional Goals section
- Outcome Measures → Target values
- Treatment notes → Session-by-session progress

**Reference in Plan:**
Include in plan name or notes:
```
Goals: Achieve pain level <3/10, walk 30 min without pain,
return to work light duty
```

#### Step 6: Save the Treatment Plan

1. Verify all required information complete
2. Click blue "Save Treatment Plan" button
3. System saves data and returns to encounter page
4. Plan appears in:
   - Encounter forms list
   - PT widget "Active Treatment Plans" section
   - Patient reports and summaries

### Updating Treatment Plan Status

As treatment progresses, update the status:

**Typical Status Changes:**

```
Week 1-2: Active (initial phase)
    ↓
Week 4: Active (continue, progress is good)
    ↓
Week 6: Completed (goals achieved)
```

```
Week 1: Active (started)
    ↓
Week 3: On Hold (patient medical issue)
    ↓
Week 5: Active (resuming treatment)
    ↓
Week 10: Completed (finished)
```

**To Update Status:**
1. Open existing treatment plan (click title in encounter)
2. Change status field to new value
3. Add note explaining status change
4. Click "Save Treatment Plan"

### Multi-Phase Treatment Plans

For complex cases requiring multiple phases:

**Create Separate Plans for Each Phase:**

**Plan 1: Acute Pain Management (Weeks 1-2)**
- Status: Completed (after 2 weeks)
- Estimated Duration: 2 weeks
- Focus: Pain relief, inflammation reduction

**Plan 2: Mobility & Strengthening (Weeks 3-6)**
- Status: Active (current)
- Estimated Duration: 4 weeks
- Focus: ROM improvement, basic strengthening

**Plan 3: Functional Restoration (Weeks 7-10)**
- Status: Active (next phase)
- Estimated Duration: 4 weeks
- Focus: Advanced strengthening, return to activities

**Benefits of Phase-Based Planning:**
- Clear milestones and transitions
- Easier status tracking
- Reflects realistic progression
- Allows plan modification based on progress
- Better documentation for insurance/reports

---

## 9. Outcome Measures Form

### Purpose

The Outcome Measures Form tracks objective, measurable patient progress across multiple domains:
- **ROM (Range of Motion):** Joint mobility
- **Strength:** Muscle force and endurance
- **Pain:** Pain level reduction
- **Function:** Ability to perform daily activities
- **Balance:** Postural stability and equilibrium

### When to Use

- Initial baseline measurement (at first assessment)
- Progress monitoring (weekly or biweekly)
- Pre/post treatment modalities
- Discharge evaluation
- Outcome documentation for reports

### Accessing the Form

**Option 1: From Encounter Menu**
1. Open patient encounter
2. Click "Clinical" tab
3. Click "Add Form" → "Vietnamese PT Outcome Measures"

**Option 2: From PT Widget**
1. Open patient summary
2. Scroll to "Vietnamese Physiotherapy"
3. Click "+ New" (no specific section, use form menu)

### Completing the Outcome Measure

#### Step 1: Select Measure Type

Choose which type of outcome you're measuring:

| Type | Definition | Examples |
|------|-----------|----------|
| **ROM** | Range of motion at a joint | Knee flexion, ankle dorsiflexion, shoulder abduction |
| **Strength** | Force-generating capacity | Grip strength, leg press, muscle testing |
| **Pain** | Pain intensity or severity | Pain level 0-10, Visual Analog Scale |
| **Function** | Ability to perform activities | Walking distance, stair climbing, ADL independence |
| **Balance** | Postural stability | Single-leg stance, tandem walk, Berg Balance Scale |

**Selecting Type:** Use dropdown menu to select measure type

#### Step 2: Set Measurement Date

**Default:** Today's date

**To Change Date:**
1. Click calendar icon next to Measurement Date
2. Select desired date
3. Use current date for new measurements
4. Use original date for baseline historical data

**When to Record:**
- Initial baseline: Use first assessment date
- Progress checks: Use current date
- Retrospective data: Use actual measurement date

#### Step 3: Enter Measurement Values

Document three key values for trend analysis:

| Value | Required | Purpose | Example |
|-------|----------|---------|---------|
| **Baseline** | Optional (recommended) | Initial measurement at treatment start | 30 |
| **Current** | Required | Today's measurement | 65 |
| **Target** | Optional (recommended) | Goal or expected outcome | 120 |

**Entering Values:**
1. Click "Baseline Value" field (optional)
2. Enter initial measurement number
3. Click "Current Value" field (required)
4. Enter today's measurement number
5. Click "Target Value" field (optional)
6. Enter goal number

**Important Notes:**
- All values must be numeric
- Decimal values allowed (e.g., 45.5)
- Use same units consistently
- Leave fields blank if not applicable

#### Step 4: Specify Unit of Measurement

Enter the unit for your specific measurement:

**ROM Measurements:**
- degrees (most common)
- inches
- centimeters (cm)

**Strength Measurements:**
- kg (kilograms)
- lbs (pounds)
- MMT 0-5 (Manual Muscle Test scale)
- repetitions (functional strength)

**Pain Measurements:**
- 0-10 scale (Numeric Pain Rating Scale)
- 0-100 mm (Visual Analog Scale)
- 1-5 scale (alternative)

**Functional Measurements:**
- LEFS score (Lower Extremity Functional Scale, 0-80)
- DASH score (Disability Arm, Shoulder, Hand, 0-100)
- Oswestry % (Low back disability, 0-100%)
- seconds (timed tests, 6-minute walk, Timed Up and Go)
- meters (walking distance)
- SFMS (Stair climbing, Floor sit, Measured seconds)

**Balance Measurements:**
- Berg score (Berg Balance Scale, 0-56 points)
- seconds (single-leg stance time)
- SEBT cm (Star Excursion Balance Test)
- TUG seconds (Timed Up and Go)

**Format Examples:**
- "degrees"
- "0-10 scale"
- "MMT 0-5"
- "LEFS score"
- "Berg score"
- "seconds"

#### Step 5: Add Measurement Notes

Document relevant context for the measurement:

**Examples:**

*ROM Measurement:*
```
Knee flexion measured with goniometer in supine position.
Patient able to flex knee further. No pain at end range.
Right leg only measured today.
```

*Strength Measurement:*
```
Used 3kg dumbbell, decreased from 5kg due to pain flare-up.
Able to complete 10 reps without substitution patterns.
No tremor noted.
```

*Pain Measurement:*
```
Pain decreased significantly since last week.
Mainly present with prolonged standing only.
Morning stiffness improving.
```

*Functional Measurement:*
```
First measurement post-surgery, Week 6.
Patient reports easier stairs, still difficulty with squatting.
Walking distance improved from 100m to 200m.
```

*Balance Measurement:*
```
Single leg stance, right leg, eyes open.
Much improved stability, no loss of balance.
Less reliance on arm support.
```

**Effective Notes Include:**
- Measurement technique used
- Position or conditions
- Notable observations
- Patient report
- Comparison to previous values
- Changes since last measurement

#### Step 6: Save the Outcome Measure

1. Verify all required information complete
2. Click blue "Save Outcome Measure" button
3. Data is saved to patient's record
4. Can be viewed in:
   - Patient encounter forms list
   - Outcome reports
   - Progress tracking charts

### Complete Outcome Measure Examples

#### Example 1: ROM - Knee Flexion

| Field | Value |
|-------|-------|
| Measure Type | ROM |
| Measurement Date | 2025-11-20 |
| Baseline Value | 30 |
| Current Value | 65 |
| Target Value | 120 |
| Unit | degrees |
| Notes | Post-ACL reconstruction, 6 weeks post-op. Patient demonstrating good progress. Zero effusion. |

#### Example 2: Strength - Quadriceps

| Field | Value |
|-------|-------|
| Measure Type | Strength |
| Measurement Date | 2025-11-20 |
| Baseline Value | 3 |
| Current Value | 4 |
| Target Value | 5 |
| Unit | MMT 0-5 |
| Notes | Improved from 3/5 to 4/5. Patient can now resist moderate force. No pain with testing. |

#### Example 3: Pain - Lower Back

| Field | Value |
|-------|-------|
| Measure Type | Pain |
| Measurement Date | 2025-11-20 |
| Baseline Value | 8 |
| Current Value | 3 |
| Target Value | 0 |
| Unit | 0-10 scale |
| Notes | Significant improvement. Pain mainly morning stiffness and after 2+ hours sitting. Activity tolerance greatly improved. |

#### Example 4: Function - Lower Extremity

| Field | Value |
|-------|-------|
| Measure Type | Function |
| Measurement Date | 2025-11-20 |
| Baseline Value | 35 |
| Current Value | 52 |
| Target Value | 70 |
| Unit | LEFS score |
| Notes | Patient reports easier stairs. Still difficulty with squatting and kneeling. Pain-free ambulation distance increased from 50m to 200m. |

#### Example 5: Balance - Single Leg Stance

| Field | Value |
|-------|-------|
| Measure Type | Balance |
| Measurement Date | 2025-11-20 |
| Baseline Value | 5 |
| Current Value | 18 |
| Target Value | 30 |
| Unit | seconds |
| Notes | Right leg, eyes open. Much improved stability. No upper extremity support needed. Ready to progress to single-leg stance eyes closed. |

### Tracking Progress Over Time

**Effective Progress Tracking:**

1. **Establish Baseline:** Record initial values at first assessment
2. **Regular Measurements:** Repeat same measures consistently (e.g., weekly)
3. **Consistent Units:** Always use same unit for each measure type
4. **Document Changes:** Note why values improve or worsen
5. **Visual Review:** Use reports or spreadsheets to see trends
6. **Adjust Treatment:** Modify treatment based on progress or plateaus

**Progress Calculation:**
```
Progress % = (Current - Baseline) / (Target - Baseline) × 100%

Example: (50 - 30) / (90 - 30) × 100% = 33% progress toward goal
```

**Data Analysis Tips:**
- Plot measurements on graph to visualize trends
- Look for consistent improvement or plateaus
- Compare rate of progress to benchmarks
- Adjust treatment if no progress after 2-3 weeks

---

## 10. Bilingual Features

### Language Support Overview

All Vietnamese PT forms support parallel bilingual documentation, allowing you to:
- Document in Vietnamese for Vietnamese-speaking patients
- Document in English for medical records and team communication
- Use both languages simultaneously for comprehensive documentation

### Language Preference Options

Each form (Assessment, Exercise, Plan, Outcome) allows you to select:

| Option | Display | Use When |
|--------|---------|----------|
| Vietnamese Only (Tiếng Việt) | Only Vietnamese fields appear (yellow background) | Patient speaks only Vietnamese, quick documentation |
| English Only | Only English fields appear (blue background) | International records, English-only teams |
| Both (Recommended) | Both Vietnamese and English fields side-by-side | Complete medical record, bilingual clinic |

### Field Color Coding

**Visual Cues for Language:**

| Color | Language | Purpose |
|-------|----------|---------|
| Yellow background | Vietnamese (Tiếng Việt) | Patient communication, Vietnamese documentation |
| Blue background | English | Medical records, team communication |
| White/gray background | Neutral | Dates, numbers, status fields (no language needed) |

### Medical Terminology Translation

#### Pre-loaded Medical Terms

The system includes 50+ Vietnamese-English medical term pairs:

**Common Physiotherapy Terms:**

| English | Vietnamese | Context |
|---------|-----------|---------|
| pain | đau | Symptom |
| physical therapy | vật lý trị liệu | Treatment type |
| assessment | đánh giá | Documentation |
| exercise | bài tập | Intervention |
| rehabilitation | phục hồi chức năng | Treatment goal |
| treatment | điều trị | Clinical action |
| acute | cấp tính | Time course |
| chronic | mãn tính | Time course |
| inflammation | viêm | Pathology |
| swelling | sưng | Symptom |
| stiffness | cứng | Symptom |
| weakness | yếu | Symptom |
| range of motion | biên độ chuyển động | Assessment |
| strength | sức mạnh | Assessment |
| balance | thăng bằng | Assessment |
| flexibility | độ mềm dẻo | Assessment |

**Anatomical Terms:**

| English | Vietnamese |
|---------|-----------|
| spine | cột sống |
| back | lưng |
| neck | cổ |
| shoulder | vai |
| elbow | khuỷu tay |
| wrist | cổ tay |
| hip | hông |
| knee | đầu gối |
| ankle | cổ chân |
| muscle | cơ |
| joint | khớp |

**Condition Terms:**

| English | Vietnamese |
|---------|-----------|
| fracture | gãy xương |
| sprain | bong gân |
| strain | căng cơ |
| arthritis | viêm khớp |
| carpal tunnel syndrome | hội chứng ống cổ tay |
| ACL tear | rách dây chằng chéo trước |
| Bell's palsy | liệt nửa mặt |
| stroke | đột quỵ |
| hernia (disc) | thoát vị (đĩa đệm) |

### Vietnamese Medical Phrases

#### Pain Descriptions

| Vietnamese | English | Use |
|-----------|---------|-----|
| đau nhói | sharp/stabbing pain | Acute, localized pain |
| đau âm ỉ | dull/aching pain | Chronic, throbbing |
| đau rát | burning pain | Neuropathic symptoms |
| đau buốt | throbbing pain | Vascular or pulsing |
| đau lan tỏa | radiating pain | Nerve root involvement |

#### Movement Terms

| Vietnamese | English | Type |
|-----------|---------|------|
| gấp | flexion | Movement direction |
| duỗi | extension | Movement direction |
| xoay | rotation | Movement direction |
| nghiêng | side bending | Movement direction |
| nâng | elevation | Movement direction |
| hạ | depression | Movement direction |

#### Frequency Terms

| Vietnamese | English |
|-----------|---------|
| hàng ngày | daily |
| mỗi tuần | weekly |
| 2 lần/ngày | twice daily |
| 3 lần/tuần | 3 times per week |
| mỗi giờ | hourly |
| vài lần mỗi ngày | several times daily |

#### Severity Terms

| Vietnamese | English |
|-----------|---------|
| nhẹ | mild/light |
| trung bình | moderate |
| nặng | severe |
| rất nặng | very severe |

### Using Bilingual Fields Effectively

#### Best Practices

**1. Complete Both Fields When Possible**
- Serves bilingual documentation needs
- Useful for medical records review
- Facilitates team communication
- Ensures complete patient handouts

**2. Priority Language Based on Context**
- **Patient communication:** Prioritize Vietnamese
- **Medical records:** Complete both
- **Team communication:** May prioritize English
- **Research/reporting:** May be English-focused

**3. Use Consistent Terminology**
- Reference the term lists above
- Maintain consistency across all forms
- Use medical terms, not colloquial expressions
- Check spelling in both languages

**4. Translation Tips**
- Start with Vietnamese if patient is Vietnamese-speaking
- Use the terminology reference tables
- Consult Vietnamese medical resources if uncertain
- Avoid direct word-for-word translation; use medical equivalents

#### Workflow Example: Bilingual Documentation

**Scenario:** Vietnamese-speaking patient with knee pain

**Step 1: Document Chief Complaint in Vietnamese First**
```
Triệu chứng: Đau đầu gối bên trong khi đi cầu thang,
đau âm ỉ khi ngồi lâu hoặc đứng lâu
```

**Step 2: Translate to Professional English**
```
Chief Complaint: Medial knee pain with stair climbing,
dull ache with prolonged sitting or standing
```

**Step 3: Verify Medical Terms Match**
- đau = pain ✓
- đầu gối = knee ✓
- bên trong = medial ✓
- đi cầu thang = stair climbing ✓
- đau âm ỉ = dull ache ✓

**Result:** Comprehensive bilingual documentation serving both patient needs and medical record requirements

### Handling Special Characters

Vietnamese text includes special characters that must be entered correctly:

#### Vietnamese Vowel Marks (Diacritics)

| Character | Keyboard Method | Examples |
|-----------|-----------------|----------|
| â | a + a (Telex) | Vô | tay, hông |
| ă | a + w (Telex) | ăn, cắn |
| ê | e + e (Telex) | ên, khen |
| ô | o + o (Telex) | ôn, tô |
| ơ | o + w (Telex) | ơm, sơn |
| ư | u + w (Telex) | ừ, mười |
| đ | d + d (Telex) | đau, đầu |

#### Tone Marks (Pitch Accents)

| Tone | Mark | Keyboard | Example |
|------|------|----------|---------|
| Sắc (Rising) | ´ | s (Telex) | Sắc: đáu |
| Huyền (Falling) | ` | f (Telex) | Huyền: dầu |
| Hỏi (Question) | ̉ | r (Telex) | Hỏi: dẩu |
| Ngã (Rising Broken) | ˜ | x (Telex) | Ngã: dãu |
| Nặng (Heavy) | . | j (Telex) | Nặng: dạu |

#### Using Vietnamese Input

**Desktop Vietnamese Keyboard Setup:**

*Windows 10/11:*
1. Settings → Time & Language → Language
2. Add Vietnamese language
3. Install
4. Use Windows + Space to toggle between English and Vietnamese

*macOS:*
1. System Preferences → Keyboard → Input Sources
2. Click "+" → Add "Vietnamese"
3. Use Control + Space or Command + Space to toggle

**Online Vietnamese Keyboard (No Installation):**
- Visit: https://www.branah.com/vietnamese
- Type using Telex method
- Copy and paste into OpenEMR forms

### Character Encoding Troubleshooting

**If Vietnamese characters don't display or save correctly:**

1. **Check Browser Encoding:**
   - Chrome: View → Developer Tools → Character Encoding → UTF-8
   - Firefox: View → Text Encoding → Unicode

2. **Copy-Paste from Text Editor:**
   - Type Vietnamese text in UTF-8 text editor first
   - Copy and paste into OpenEMR form
   - Ensures proper encoding

3. **Report to Administrator:**
   - Database may need UTF-8mb4 collation
   - Contact system administrator
   - Provide examples of text that won't save

---

## 11. Best Practices and Workflows

### Documentation Standards

#### Completeness

**DO:**
- Fill out both Vietnamese and English fields (if "Both" selected)
- Document pain levels numerically (0-10 scale)
- Include specific functional limitations
- Set measurable, time-bound goals
- Record baseline outcome measures
- Complete all required fields

**DON'T:**
- Leave one language blank if you select "Both"
- Use vague terms like "better" or "worse"
- Skip baseline measurements
- Set unrealistic goals without patient input
- Leave forms in "Draft" status indefinitely

#### Consistency

**DO:**
- Use standard medical terminology (reference term lists)
- Maintain consistent abbreviations
- Use same units for repeated measurements
- Document at regular intervals (e.g., weekly)
- Spell terms consistently across languages

**DON'T:**
- Mix colloquial and medical terms
- Switch units between measurements
- Skip scheduled re-assessments
- Use inconsistent naming conventions
- Abbreviate Vietnamese terms inconsistently

#### Timeliness

**DO:**
- Document during or immediately after patient visit
- Complete assessments on initial visit
- Update outcome measures at regular intervals
- Mark completed plans as "Completed" promptly
- Document exercise progression when made

**DON'T:**
- Delay documentation until end of day/week
- Backdate entries without indication
- Leave forms in "Draft" status for multiple visits
- Documentation weeks after treatment

### Language Selection Guidelines

#### When to Use Vietnamese Only

- Patient speaks only Vietnamese
- Documentation for patient handouts or education
- Direct patient communication notes
- Forms patient will sign or review
- Clinic operates primarily in Vietnamese

#### When to Use English Only

- International medical record sharing
- Research data collection
- Communication with English-only colleagues
- Insurance/billing documentation (if required)
- Clinic operates primarily in English

#### When to Use Both (Recommended)

- **Primary clinical documentation** (assessments, treatment plans)
- **Teaching hospitals** with multilingual teams
- **Medical-legal documentation**
- **Comprehensive patient records**
- **Quality assurance reviews**
- **Patient handouts** distributed to diverse populations
- **Insurance and billing** requiring dual language support

### Clinical Workflows

#### Workflow 1: New Patient PT Assessment

**Scenario:** New patient with chronic low back pain
**Time Required:** 45-60 minutes
**Forms to Complete:** Assessment, Baseline Outcome Measures, Treatment Plan, Initial Exercises

**Detailed Steps:**

**1. Create Encounter (2 minutes)**
- Open patient chart
- Click "New Encounter" or select existing encounter
- Set encounter date and provider
- Select appropriate encounter type

**2. PT Assessment (25-30 minutes)**
- Click "Add Form" → "Vietnamese PT Assessment"
- Select language preference: "Both" (recommended)
- Complete Chief Complaint (Vietnamese and English)
- Complete Pain Assessment (0-10 scale, location, quality)
- Complete Functional Goals section (2-3 goals minimum)
- Complete Treatment Plan Summary (proposed interventions)
- Set status to "Completed"
- Save Assessment

**3. Baseline Outcome Measures (5-10 minutes)**
- Create multiple outcome measures (typically 3-5):
  - Pain (baseline, current, target, unit)
  - Function (baseline, current, target, unit)
  - ROM (baseline, current, target, unit)
  - Balance (if applicable)
  - Strength (if applicable)
- Include detailed notes for each
- Save each measure

**4. Treatment Plan (5 minutes)**
- Click "Add Form" → "Vietnamese PT Treatment Plan"
- Enter Plan Name (e.g., "Lower Back Pain Rehabilitation")
- Enter Diagnosis (Vietnamese and English)
- Set Start Date (today)
- Set Estimated Duration (8-12 weeks typical)
- Set Status: "Active"
- Save Plan

**5. Initial Exercise Prescriptions (5-10 minutes)**
- Create 2-3 initial exercises
- Use low to moderate intensity
- Set 7-day frequency for initial phase
- Include clear bilingual instructions
- Set same start date
- Set end date 2-4 weeks out for initial program
- Save each exercise

**6. Patient Education (5-10 minutes)**
- Review treatment plan timeline with patient
- Explain exercise program
- Provide printed handouts
- Schedule follow-up appointment
- Answer patient questions

**Documentation Complete:** Patient has comprehensive initial assessment documented and home exercise program prescribed.

---

#### Workflow 2: Progress Re-Assessment (2-4 Week Visit)

**Scenario:** Patient returning for 2-week progress check
**Time Required:** 35-45 minutes
**Forms to Update:** Outcome Measures, Exercise Prescriptions, Treatment Plan (optional)

**Detailed Steps:**

**1. Review Previous Documentation (5 minutes)**
- Open patient chart
- Check PT widget for recent activity
- Review initial assessment findings
- Review baseline outcome measures
- Note current exercise prescriptions

**2. Conduct Treatment Session (20-25 minutes)**
- Provide hands-on treatment
- Supervise home exercise performance
- Assess compliance with program
- Note patient feedback and response

**3. Update Outcome Measures (5-10 minutes)**
- Create new outcome measure forms with current values
- Include comparison to baseline
- Document changes in notes
- Examples:
  - Pain: Down from 7 to 4
  - Function: Improved in LEFS score
  - ROM: Increased knee flexion
  - Notes: "Patient reports better sleep, still stiff in morning"

**4. Progress Exercise Program (5 minutes)**
- Increase intensity on existing exercises (sets, reps, frequency)
- Or add new exercise for progression
- Modify or discontinue exercises not tolerated
- Update duration if needed

**5. Brief Progress Note (3 minutes)**
- Create brief PT Assessment update, OR
- Document in encounter notes
- Note patient's response to treatment
- Describe functional improvements

**6. Update Treatment Plan (Optional)**
- If status should change, update plan
- Keep "Active" if progressing appropriately
- Change to "On Hold" if patient needs pause
- Save changes

**Documentation Complete:** Progress documented, exercise program advanced, re-assessment completed.

---

#### Workflow 3: Discharge Planning (Final Visit)

**Scenario:** Patient completing 8-week treatment program
**Time Required:** 45-50 minutes
**Forms to Complete:** Final Assessment, Final Outcome Measures, Home Maintenance Program, Treatment Plan Status Update

**Detailed Steps:**

**1. Final Treatment Session (20-25 minutes)**
- Complete final hands-on treatment
- Review all exercises for correct form
- Discuss long-term maintenance strategies
- Assess readiness for discharge

**2. Final Outcome Measures (10 minutes)**
- Record final measurements for all baseline measures
- Document achievement (or non-achievement) of goals
- Include comparison to baseline and target
- Notes: "Goal achieved. Minimal pain with prolonged activity only."
- Examples:
  - Pain: Baseline 8, Current 2 (Goal 0, Near goal)
  - Function: Baseline 35, Current 68 (Goal 65, Exceeded)
  - ROM: Baseline 30, Current 65 (Goal 60, Exceeded)

**3. Discharge Assessment (10 minutes)**
- Create final PT Assessment
- Select language: "Both"
- Chief Complaint: "Discharge assessment - condition improved"
- Document goals achieved
- Summary of treatment provided
- Functional gains achieved
- Status: "Completed"
- Save Assessment

**4. Complete Treatment Plan (2 minutes)**
- Open Treatment Plan
- Change Status: "Active" → "Completed"
- Add discharge notes if desired
- Save changes

**5. Maintenance Exercise Program (5 minutes)**
- Create final exercise prescriptions for home maintenance
- Keep intensity Moderate to High
- Set longer duration (12+ weeks)
- Mark as Active
- Print handouts for patient

**6. Discharge Instructions (5-8 minutes)**
- Provide written home program with clear instructions
- Discuss return-to-activity guidelines
- Explain red flags (when to seek care)
- Schedule follow-up if needed (PRN basis)
- Provide discharge summary document to patient

**Documentation Complete:** Comprehensive discharge documentation, functional outcomes documented, maintenance program provided, patient discharged with clear home instructions.

---

#### Workflow 4: Quick Exercise Progression (Routine Visit)

**Scenario:** Regular treatment visit, progressing exercises only
**Time Required:** 5-10 minutes documentation

**Quick Steps:**
1. Open patient encounter
2. Navigate to existing exercise (click from encounter list)
3. Modify parameters:
   - Increase sets/reps
   - Increase frequency
   - Upgrade intensity level
   - Extend end date
4. Click Save

**When to Use:** Routine visits between formal re-assessments, exercise modifications based on patient tolerance, progressive loading.

---

#### Workflow 5: Multi-Patient Morning Review

**Scenario:** Therapist preparing for day of scheduled patients
**Time Required:** 2-3 minutes per patient

**Steps:**
1. Open daily schedule or calendar
2. For each scheduled patient:
   - Open patient chart
   - Navigate to Summary page
   - Review PT widget:
     - Check recent assessment findings
     - Review active exercises
     - Note treatment plan status
   - Note any needed documentation:
     - Re-assessment due (2+ weeks)?
     - Outcome measures update due (weekly)?
     - Exercise progression needed (plateauing)?
     - Treatment plan status update needed?
3. Prepare treatment approach mentally

**Benefit:** Efficient morning prep ensures you don't miss required documentation during busy treatment sessions.

---

### Quality Assurance

#### Self-Review Checklist

Before closing patient encounter, verify:

- [ ] All required form fields completed
- [ ] Pain levels documented numerically (0-10)
- [ ] Both language fields filled (if "Both" selected)
- [ ] Outcome measures include baseline values
- [ ] Exercise prescriptions marked "Active"
- [ ] Treatment plan status is current
- [ ] No spelling/grammar errors in key fields
- [ ] Forms saved successfully (not left as drafts)
- [ ] Dates are realistic and logical
- [ ] Clinical information is accurate

#### Common Documentation Errors

**Error 1: Missing Baseline Values**
- Problem: Outcome measures without baseline
- Impact: Cannot calculate progress
- Prevention: Always record initial values at first assessment

**Error 2: Inconsistent Units**
- Problem: Pain measured as 0-10 one week, 0-100 next
- Impact: Difficult to track trends
- Prevention: Use same unit throughout treatment

**Error 3: Vague Functional Goals**
- Problem: "Improve function" without specifics
- Impact: Hard to measure progress
- Prevention: Make goals specific ("Walk 30 min without pain")

**Error 4: Outdated Exercise Status**
- Problem: Exercise end date has passed, still shows as "active"
- Impact: Confuses patient and treatment planning
- Prevention: Regularly review active exercises, update dates

**Error 5: Forgotten Status Updates**
- Problem: Treatment plan left "Active" long after discharge
- Impact: Inaccurate tracking and reporting
- Prevention: Update status when patient completes or stops treatment

**Error 6: Single-Language Documentation**
- Problem: Only English or only Vietnamese filled in
- Impact: Incomplete medical record, reduced usability
- Prevention: Complete both languages when "Both" is selected

---

## 12. Troubleshooting

### Common Issues and Solutions

#### Issue 1: Cannot Find Vietnamese PT Forms

**Symptoms:**
- PT forms not visible in "Add Form" menu
- Only standard OpenEMR forms available
- Search doesn't find Vietnamese PT forms

**Solution A: Enable PT Forms in Administration**

1. Log into OpenEMR as administrator
2. Navigate: Administration → Forms Management
3. Search for "Vietnamese PT"
4. Verify all 4 forms are enabled:
   - Vietnamese PT Assessment
   - Vietnamese PT Exercise Prescription
   - Vietnamese PT Treatment Plan
   - Vietnamese PT Outcome Measures
5. If not enabled, click "Enable" button for each
6. Refresh browser (F5)
7. Try accessing forms again

**Solution B: Check User Permissions**

1. Ask system administrator to verify your user account permissions
2. You need:
   - "Encounters" permission
   - "Notes" permission
   - "Forms" access
   - Specific "physiotherapy" or "PT" module access
3. Administrator may need to grant permissions in Admin → Users

**Solution C: Verify Module Installation**

1. Contact system administrator
2. Ask them to verify Vietnamese PT module is installed
3. Check if database tables are present:
   - pt_assessments_bilingual
   - pt_exercise_prescriptions_bilingual
   - pt_treatment_plans_bilingual
   - pt_outcome_measures_bilingual
4. May require database migration if tables missing

**If Still Not Working:**
- Contact system administrator with error details
- Provide screenshot of current situation
- Note exact where you're looking for forms

---

#### Issue 2: PT Widget Not Showing on Patient Summary

**Symptoms:**
- Patient summary page displays normally
- No "Vietnamese Physiotherapy" section visible
- Cannot access PT widget

**Possible Cause A: No PT Data Exists Yet**

- Normal for completely new patients
- Widget hides until first PT form is created
- Solution: Create first PT Assessment form
- After saving, return to summary and refresh
- Widget should appear with your new data

**Possible Cause B: Widget Not Activated**

1. Contact system administrator
2. Ask them to verify widget is enabled
3. Widget code location: `library/custom/vietnamese_pt_widget.php`
4. May need to be added to patient summary template

**Possible Cause C: Browser Cache Issue**

1. Clear browser cache (Ctrl+Shift+Delete)
2. Hard refresh page (Ctrl+F5 or Cmd+Shift+R)
3. Navigate away from patient, then back
4. Try different browser if problem persists

**Solution: Verify Widget Activation**

1. Navigate to patient summary
2. Check page source code (right-click → View Page Source)
3. Search for "vietnamese_pt" or "Physiotherapy"
4. If found: Widget is on page but may be hidden
5. If not found: Widget not included in template

---

#### Issue 3: Vietnamese Characters Not Displaying or Saving

**Symptoms:**
- Type Vietnamese text in form
- After save, text appears as question marks or garbled
- Special characters (ă, â, ê, ô, ơ, ư, đ) don't save
- Vietnamese text shows "??????" or similar

**Solution A: Check Browser Encoding**

1. Verify page encoding is UTF-8:
   - **Chrome:** View → Developer Tools → Settings → Encoding → UTF-8
   - **Firefox:** View → Text Encoding → Unicode (UTF-8)
   - **Edge:** Settings → Encoding → UTF-8
2. Refresh the page (F5)
3. Try entering Vietnamese text again
4. Text should display correctly now

**Solution B: Use Copy-Paste Method**

1. Type Vietnamese text in separate UTF-8 text editor (NotePad++, VS Code, etc.)
2. Copy the text
3. Paste into OpenEMR form field
4. Ensures proper encoding transfer
5. Save the form

**Solution C: Verify Database Configuration**

1. Contact system administrator
2. Verify database has UTF-8mb4 charset:
   ```sql
   SHOW CREATE DATABASE openemr;
   ```
   Should show: `CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci`
3. Verify table collation is correct:
   ```sql
   SHOW CREATE TABLE pt_assessments_bilingual;
   ```
   Should show: `COLLATE utf8mb4_vietnamese_ci`
4. If incorrect, administrator needs to fix database configuration

**Solution D: Test with Simple Text First**

1. Try entering just English text first → Save successfully?
2. Then try Vietnamese text → Does it save?
3. If English works but Vietnamese doesn't: Encoding issue
4. If both fail: Broader database or permission issue

**If Still Failing:**
- Provide example text that won't save
- Take screenshot showing the issue
- Report to system administrator with browser and OS details

---

#### Issue 4: Pain Level Indicator Not Updating

**Symptoms:**
- Enter pain level (0-10)
- Visual color indicator doesn't change
- Shows wrong number or stays at "0"
- Indicator appears, but doesn't update

**Solution A: Enable JavaScript**

1. Check browser JavaScript is enabled:
   - Chrome: Settings → Privacy → JavaScript → Enabled
   - Firefox: Type "about:config" in address bar → Search "javascript.enabled" → Ensure "true"
   - Edge: Settings → Advanced → JavaScript → On
2. Refresh page (F5)
3. Try entering pain level again

**Solution B: Reload the Form**

1. Cancel out of form (don't save changes)
2. Navigate back to form
3. Re-enter data
4. Indicator should update now

**Solution C: Browser Compatibility**

1. Try in different browser:
   - If Chrome fails, try Firefox
   - If Edge fails, try Chrome
2. Clear browser cache (Ctrl+Shift+Delete)
3. Hard refresh (Ctrl+F5)

**Solution D: Indicator Is Visual Only**

- Important: Pain indicator is visual feedback only
- Doesn't affect saved data
- Pain value saves correctly even if indicator doesn't update
- You can continue working despite visual issue

**Workaround:**
- Manually remember color scheme:
  - 0-3: Green (low)
  - 4-6: Yellow (moderate)
  - 7-10: Red (high)
- Form will save pain value correctly regardless

---

#### Issue 5: Exercise Prescription Shows as Inactive

**Symptoms:**
- Created exercise prescription
- Does not appear in "Active Exercise Prescriptions" section
- Appears in encounter list but marked inactive

**Possible Cause A: Date Range Issue**

1. Open exercise prescription (click title)
2. Check Start Date:
   - Is it ≤ today's date?
   - If future date: Exercise not yet active
3. Check End Date:
   - Is it > today's date or blank?
   - If past date: Exercise is expired
4. Update dates if needed:
   - Set Start Date to today or earlier
   - Set End Date to future date or leave blank
5. Save changes
6. Exercise should now show as active

**Solution B: Check Active Status Field**

1. Open exercise prescription
2. Look for "Active", "Is Active", or status field
3. Verify it's checked/set to "Yes" or "Active"
4. If unchecked, check the box
5. Save changes
6. Exercise should show as active now

**Solution C: Create New Prescription**

If existing exercise won't activate:
1. Create new exercise prescription with same details
2. Use current start date
3. Leave end date blank (for ongoing exercises)
4. Ensure status is set to "Active"
5. Save new prescription
6. Can deactivate old one by setting past end date

**Troubleshooting Tips:**
- Widget only shows exercises with dates in current range
- Exercises with end dates in past don't show
- Verify dates are logical (start before end)
- Check both start and end dates, not just one

---

#### Issue 6: Cannot Save Form - CSRF Error

**Symptoms:**
- Fill out entire form
- Click "Save [Form Name]" button
- Error message appears: "CSRF token validation failed"
- Form does not save

**What Is This?**
- Security feature protecting against unauthorized form submissions
- Triggered by: Session timeout, security policy, browser issue

**Solution A: Copy Your Data (Prevent Retyping)**

**Before closing error:**
1. Select all text in form (Ctrl+A)
2. Copy to clipboard (Ctrl+C)
3. Open text editor (Notepad, Word, etc.)
4. Paste data (Ctrl+V)
5. Save text file
6. Now close error safely

**Solution B: Refresh Session and Retry**

1. Click "Back" button in browser
2. Navigate to OpenEMR home/dashboard page
3. Verify you're still logged in
4. Navigate back to patient encounter
5. Open a NEW form (don't use back button)
6. Paste saved data from text file
7. Save immediately
8. Should succeed this time

**Solution C: Avoid Session Timeout**

- Complete forms within 15-20 minutes
- Don't leave forms open during breaks or lunch
- For complex forms: Save as "Draft" first
- Reopen, complete, change to "Completed", save again

**Solution D: Try Different Browser**

1. If persistent issue with Chrome, try Firefox
2. Clear browser cookies and cache
3. Log out completely, close browser
4. Open new browser window
5. Log back in
6. Try form again

**Prevention Strategies:**
- Don't multitask or interrupt form entry
- Close other browser tabs
- Use stable internet connection
- Don't leave forms open overnight
- Save complex forms in stages (Draft → Completed)

---

#### Issue 7: Outcome Measures Not Calculating Progress

**Symptoms:**
- Enter baseline, current, target values
- Expect to see progress percentage calculated
- No calculation shown or error appears

**Important Note:**
Current module focuses on data collection. Automatic progress calculations not yet implemented.

**Solution A: Calculate Progress Manually**

Use this formula:
```
Progress % = (Current - Baseline) / (Target - Baseline) × 100%
```

**Example:**
- Baseline ROM: 30 degrees
- Current ROM: 65 degrees
- Target ROM: 90 degrees
- Progress = (65 - 30) / (90 - 30) × 100% = 58% of goal achieved

**Solution B: Use Reports if Available**

1. Navigate to: Reports → Physiotherapy (if available)
2. Generate outcome measures report
3. Report may include calculations and trends
4. Use for patient communication and billing

**Solution C: Track in External Spreadsheet**

1. Export outcome measures data from OpenEMR
2. Create spreadsheet (Excel, Google Sheets, etc.)
3. Add calculated progress column
4. Create charts and graphs
5. Reference during patient visits
6. Use for evidence-based treatment decisions

**Solution D: Document in Notes Field**

1. Open outcome measure form
2. In Notes section, type progress calculation
3. Example: "Progress: 58% toward goal (58 of 90 degrees)"
4. Save form with documented calculation
5. Visible in patient notes for reference

**Workaround for Providers:**
- Many providers calculate progress manually anyway
- Electronic calculation is nice feature but not essential
- Focus on collecting data consistently
- Progress becomes obvious visually across multiple measurements

---

### Getting Additional Help

#### When to Contact System Administrator

Contact your system administrator for:
- Technical setup issues (forms not showing, permissions)
- Database or encoding problems
- Installation or configuration questions
- User account or login issues
- System crashes or error messages

**Provide These Details:**
- Screenshots of issue
- Exact error messages (copy-paste if possible)
- Steps to reproduce (what you did when error occurred)
- Which patient/form affected
- When issue started
- Which browser/computer you're using

#### When to Contact PT Department Lead or Supervisor

Contact your clinical supervisor for:
- Documentation standards questions
- Form completion guidance
- Treatment planning advice
- Clinical workflow questions
- Discharge planning questions

#### Internal Resources

1. **This Manual:** Reference sections 1-11
2. **Form Help:** Hover over field labels for hints
3. **Getting Started Guide:** See `Documentation/physiotherapy/user-guides/GETTING_STARTED.md`
4. **OpenEMR Docs:** https://www.open-emr.org/wiki/
5. **PT Module Docs:** See `Documentation/physiotherapy/`

#### External Resources

1. **OpenEMR Forums:** https://community.open-emr.org/
2. **Module Developer:** Contact information in system admin resources
3. **OpenEMR Wiki:** https://www.open-emr.org/wiki/ (core system help)

#### Before Requesting Help

Complete this checklist:
- [ ] Read relevant section of this manual
- [ ] Try solutions listed in troubleshooting
- [ ] Collect screenshots of issue
- [ ] Note exact error messages
- [ ] Document steps to reproduce
- [ ] Check if issue affects all patients or just one
- [ ] Try in different browser
- [ ] Verify internet connection is stable

This preparation helps support staff help you faster.

---

## 13. Quick Reference

### Keyboard and Navigation Shortcuts

| Action | Shortcut | Notes |
|--------|----------|-------|
| Save form | Click button | Ctrl+S may not work in web forms |
| Cancel/Close | Esc key | May not work in all areas |
| Refresh page | F5 or Ctrl+R | Use when data doesn't update |
| Hard refresh | Ctrl+F5 (Cmd+Shift+R Mac) | Clears cache, full reload |
| Print page | Ctrl+P | Works on most pages |
| Find text | Ctrl+F | Search page content |

### Form Location Quick Reference

| Form | Path | Time to Complete |
|------|------|-----------------|
| PT Assessment | Encounter → Clinical → Add Form → Vietnamese PT Assessment | 20-25 min |
| Exercise Prescription | Encounter → Clinical → Add Form → Vietnamese PT Exercise Prescription | 10-15 min |
| Treatment Plan | Encounter → Clinical → Add Form → Vietnamese PT Treatment Plan | 5-10 min |
| Outcome Measures | Encounter → Clinical → Add Form → Vietnamese PT Outcome Measures | 5 min per measure |
| PT Widget | Patient Summary → Scroll to Vietnamese Physiotherapy section | View only |

### Field Color Coding Reference

| Color | Meaning | Language | Content Type |
|-------|---------|----------|--------------|
| Yellow background | Vietnamese language field | Vietnamese (Tiếng Việt) | Patient communication |
| Blue background | English language field | English | Medical documentation |
| White/gray background | Neutral field | N/A | Dates, numbers, selections |
| Green badge | Low pain level | N/A | Pain 0-3 |
| Yellow badge | Moderate pain level | N/A | Pain 4-6 |
| Red badge | High pain level | N/A | Pain 7-10 |

### Status Values Quick Reference

| Form | Status Options | Default | Meaning |
|------|----------------|---------|---------|
| Assessment | Draft, Completed, Reviewed | Completed | Work status of assessment |
| Treatment Plan | Active, Completed, On Hold | Active | Treatment status |
| Exercise | Active (shown in widget) | Based on dates | Shown only if start ≤ today and end > today |

### Outcome Measure Units Quick Reference

| Measure Type | Common Units | Examples |
|--------------|--------------|----------|
| ROM | degrees, cm, inches | "90 degrees", "30 cm" |
| Strength | kg, lbs, MMT 0-5, reps | "5kg", "4/5", "15 reps" |
| Pain | 0-10 scale, 0-100 mm | "5", "50 mm" |
| Function | LEFS, DASH, Oswestry %, seconds, meters | "60 LEFS", "45 Oswestry %", "12 seconds" |
| Balance | Berg score, seconds, cm | "45 Berg", "25 seconds" |

### Exercise Prescription Defaults

| Field | Default | Range | Tips |
|-------|---------|-------|------|
| Sets | 3 | 1-10 | Start with 2-3, increase gradually |
| Reps | 10 | 1-50 | Adjust based on exercise type |
| Frequency | 5x/week | 1-7 days | Initial rehab: daily; maintenance: 3-5x |
| Intensity | Moderate | Low/Moderate/High | Progress: Low → Moderate → High |
| Start Date | Today | Any date | Should be realistic (today or tomorrow) |
| End Date | Blank (ongoing) | Any date | Leave blank for ongoing, set for progression |

### Treatment Plan Typical Durations

| Condition Type | Duration | Frequency | Sessions |
|---|---|---|---|
| Acute pain | 2-4 weeks | 2-3x/week | 6-12 |
| Subacute condition | 4-8 weeks | 2-3x/week | 12-24 |
| Chronic pain | 8-12 weeks | 2-3x/week | 16-36 |
| Post-surgical (simple) | 6-8 weeks | 2-3x/week | 12-24 |
| Post-surgical (complex) | 12-24 weeks | 3-5x/week | 36-72 |
| Neurological condition | 12+ weeks | 3-5x/week | 36-72+ |

### Common Vietnamese Medical Terms (Most Frequently Used)

| English | Vietnamese | English | Vietnamese |
|---------|-----------|---------|-----------|
| pain | đau | acute | cấp tính |
| physical therapy | vật lý trị liệu | chronic | mãn tính |
| exercise | bài tập | inflammation | viêm |
| assessment | đánh giá | weakness | yếu |
| treatment | điều trị | stiffness | cứng |
| rehabilitation | phục hồi chức năng | numbness | tê |
| strength | sức mạnh | tingling | ngứa ran |
| flexibility | độ mềm dẻo | swelling | sưng |
| range of motion | biên độ chuyển động | balance | thăng bằng |
| spine | cột sống | back | lưng |
| knee | đầu gối | shoulder | vai |
| ankle | cổ chân | neck | cổ |

### Documentation Frequency Guidelines

| Item | Initial Visit | During Treatment | Discharge |
|------|---------------|------------------|-----------|
| PT Assessment | Required | Every 2-4 weeks | Required |
| Outcome Measures | Baseline | Weekly or biweekly | Final measurement |
| Treatment Plan | Required | Review every 2-4 weeks | Mark "Completed" |
| Exercise Prescription | 2-3 exercises | Update weekly/biweekly | Maintenance program |
| Session Notes | Detailed | Brief progress notes | Summary |

### Common PT Abbreviations and Terms

| Abbreviation | Meaning (English) | Meaning (Vietnamese) |
|---|---|---|
| PT | Physical Therapy | Vật lý trị liệu |
| ROM | Range of Motion | Biên độ chuyển động |
| ADL | Activities of Daily Living | Hoạt động sinh hoạt hàng ngày |
| MMT | Manual Muscle Test | Kiểm tra sức cơ thủ công |
| VAS | Visual Analog Scale | Thang điểm đau tương tự trực quan |
| NPRS | Numeric Pain Rating Scale | Thang điểm đau số |
| LEFS | Lower Extremity Functional Scale | Thang điểm chức năng chi dưới |
| DASH | Disability Arm Shoulder Hand | Khuyết tật tay vai bàn tay |
| ACL | Anterior Cruciate Ligament | Dây chằng chéo trước |
| Rehab | Rehabilitation | Phục hồi chức năng |

---

## 14. Appendices

### Appendix A: Vietnamese Keyboard Setup

For efficient Vietnamese text entry, set up a Vietnamese keyboard:

#### Windows 10/11 Setup

1. Click Start menu
2. Type "Language settings" → Open "Language & region settings"
3. Scroll to "Languages" section
4. Click "Add a language"
5. Search for "Vietnamese"
6. Click "Vietnamese" and then "Install"
7. Switch between keyboards: Press Windows + Space
8. Select English or Vietnamese input method

#### macOS Setup

1. Click Apple menu → System Preferences
2. Go to Keyboard → Input Sources
3. Click "+" button
4. Search for "Vietnamese"
5. Select "Vietnamese" or "Vietnamese (Telex)"
6. Click "Add"
7. Switch keyboards: Press Control + Space or Command + Space
8. Select Vietnamese from input menu

#### Online Vietnamese Keyboard (No Installation)

- Visit: https://www.branah.com/vietnamese
- Type using Telex method (see below)
- Copy and paste Vietnamese text into OpenEMR forms
- Works on any computer without installation

#### Telex Typing Method (Most Common)

Vietnamese Telex typing uses ASCII characters to create Vietnamese characters:

**Vowel Marks:**
- a + a = â
- a + w = ă
- e + e = ê
- o + o = ô
- o + w = ơ
- u + w = ư
- d + d = đ

**Tone Marks (applied after vowel mark or with final consonant):**
- + s = Sắc (rising tone)
- + f = Huyền (falling tone)
- + r = Hỏi (rising question tone)
- + x = Ngã (broken rising tone)
- + j = Nặng (heavy/low tone)

**Example:**
- d + a + u = dau (pain, no tone)
- d + a + u + s = daus → display as "dáu" (wrong tone, but system corrects)
- Better: da + u + s = dás → "dás" (correct)

**Common Examples:**
- Dau → Đau (pain)
- Bie → Biệt (distinguish)
- Tay → Tay (hand, no tone needed)
- Tay + s = tays → tá​y (tone mark)

**Note:** Telex takes practice. Use online keyboard or Vietnamese input method if unsure.

---

### Appendix B: Sample Documentation Templates

#### Template 1: Initial Assessment - Lower Back Pain

**Chief Complaint (Vietnamese):**
```
Đau lưng dưới từ [6 tháng], tăng khi [ngồi lâu hoặc nâng vật nặng],
giảm khi [nằm hoặc đứng nhẹ nhàng]. Không có tiền sử chấn thương cụ thể.
Hiện tại đau [7/10] vào buổi sáng, giảm xuống [5/10] sau khi vận động.
```

**Chief Complaint (English):**
```
Low back pain for [6 months], worse with [prolonged sitting or lifting],
better with [lying down or light activity]. No history of specific trauma.
Currently [7/10] pain in morning, improves to [5/10] with movement.
```

**Pain Assessment:**
- Pain Level: 7
- Location: Lower back, right side
- Quality: Aching, dull pain; sharp pain with certain movements
- Aggravating factors: Sitting > 30 minutes, lifting, bending forward
- Relieving factors: Lying down, heat, gentle stretching

**Functional Goals:**
```
Vietnamese:
1. Có thể ngồi làm việc 2 giờ liên tục không cần đứy lên
2. Ngủ suốt đêm không bị đau đánh thức
3. Đi bộ 30 phút không đau hoặc khó chịu
4. Trở lại hoạt động thể thao nhẹ (như quần vợt)

English:
1. Sit for work 2 hours without needing to stand due to pain
2. Sleep through the night without pain interruption
3. Walk 30 minutes without pain or discomfort
4. Return to light sports activities like tennis
```

**Treatment Plan Summary:**
```
Vietnamese:
Kế hoạch vật lý trị liệu 8 tuần bao gồm:
- 3 lần/tuần, 45 phút/lần
- Trị liệu bằng tay: Massage, động viên khớp
- Điều trị tia: Ultrasound, TENS nếu cần
- Bài tập: Kéo giãn, tăng cường cơ, kiểm soát tư thế
- Giáo dục: Tư thế ngồi đúng, kỹ thuật nâng đúng
- Tái đánh giá: Sau 2 tuần

English:
8-week physical therapy plan including:
- 3x/week, 45 minutes per session
- Manual therapy: Massage, joint mobilization
- Modalities: Ultrasound, TENS as needed
- Exercises: Stretching, strengthening, posture control
- Education: Proper sitting posture, correct lifting technique
- Re-evaluation: After 2 weeks
```

---

#### Template 2: Exercise Program - Knee Rehabilitation

**Exercise 1: Quadriceps Sets (Siết cơ tứ đầu đùi)**

Sets: 3 | Reps: 15 | Frequency: 7x/week | Intensity: Low

**Description (Vietnamese):**
```
Nằm ngửa, chân thẳng. Đặt gối nhỏ dưới lưng dâu gối chỉnh thoát lực.
Siết cơ đùi trước, đẩy lưng đầu gối xuống khỏi gối nhỏ.
Giữ 5 giây, sau đó thả lỏng.
Thực hiện liên tục 15 lần, 3 hiệp, 7 ngày/tuần.
```

**Description (English):**
```
Lie on back, leg straight. Place small pillow under back of knee.
Tighten front thigh muscle, push back of knee down into pillow.
Hold 5 seconds, then relax.
Perform 15 repetitions, 3 sets, 7 days per week.
```

**Precautions (Vietnamese):**
```
- Không làm nếu đau cấp tính
- Giữ đầu gối thẳng hoàn toàn, không gập
- Ngừng nếu sưng tăng hoặc đau tăng
```

**Precautions (English):**
```
- Do not perform if acute pain present
- Keep knee completely straight, no bending
- Stop if swelling or pain increases
```

---

#### Template 3: Discharge Assessment Summary

**Chief Complaint (Vietnamese):**
```
Đánh giá xuất viện - Bệnh nhân đã hoàn thành chương trình vật lý trị liệu
8 tuần cho đau lưng dưới. Các mục tiêu hầu hết đã đạt được.
Bệnh nhân báo cáo tình trạng chức năng đáng kể cải thiện và
sẵn sàng quay trở lại hoạt động bình thường.
```

**Chief Complaint (English):**
```
Discharge assessment - Patient has completed 8-week physical therapy program
for low back pain. Most goals achieved. Patient reports significant functional
improvement and is ready to return to normal activities with home program.
```

**Functional Gains (Vietnamese):**
```
- Đau giảm từ 7/10 xuống 2/10 (mục tiêu đạt được)
- Có thể ngồi 2 giờ không đau (mục tiêu đạt được)
- Ngủ suốt đêm không đau (mục tiêu đạt được)
- Đi bộ 45 phút (vượt qua mục tiêu 30 phút)
- Quay trở lại tập gym nhẹ (mục tiêu vượt)
- Biên độ gấp cột sống tăng từ 30° lên 65° (tốt)
```

**Functional Gains (English):**
```
- Pain reduced from 7/10 to 2/10 (goal achieved)
- Can sit 2 hours without pain (goal achieved)
- Sleep through night without pain (goal achieved)
- Walk 45 minutes (exceeded 30-minute goal)
- Return to light gym work (goal exceeded)
- Lumbar flexion ROM increased from 30° to 65° (good)
```

**Home Maintenance Program (Vietnamese):**
```
Bệnh nhân được hướng dẫn chương trình duy trì tại nhà bao gồm:
- 3 bài tập, 5 lần/tuần, 20-30 phút/lần
- Bài tập kéo giãn hàng ngày
- Tư thế ngồi đúng trong công việc
- Ngừng hoạt động nếu đau tái phát
- Tái khám nếu đau tái phát hoặc tăng
```

**Home Maintenance Program (English):**
```
Patient instructed on home maintenance program including:
- 3 exercises, 5x/week, 20-30 minutes per session
- Daily stretching
- Proper sitting posture at work
- Activity modification if pain recurs
- Return if pain returns or worsens
```

---

### Appendix C: Complete Glossary

#### English to Vietnamese - Common PT Terms

| English | Vietnamese | Context/Use |
|---------|-----------|------------|
| Activities of Daily Living | Hoạt động sinh hoạt hàng ngày | Functional assessment |
| Anterior Cruciate Ligament | Dây chằng chéo trước | ACL injury |
| Assessment | Đánh giá | Documentation type |
| Balance | Thăng bằng | Assessment domain |
| Baseline | Giá trị nền/ban đầu | Initial measurement |
| Bell's Palsy | Liệt nửa mặt | Neurological condition |
| Carpal Tunnel Syndrome | Hội chứng ống cổ tay | Wrist condition |
| Chronic | Mãn tính | Duration descriptor |
| Compliance | Tuân thủ | Adherence to program |
| Diagnosis | Chẩn đoán | Medical assessment |
| Disability | Khuyết tật/Khó khăn chức năng | Functional limitation |
| Discharge | Xuất viện | End of treatment |
| Exercise | Bài tập | Intervention type |
| Flexibility | Độ mềm dẻo | ROM/mobility assessment |
| Fracture | Gãy xương | Bone injury |
| Frequency | Tần suất | How often |
| Function | Chức năng | Functional ability |
| Goal | Mục tiêu | Treatment objective |
| Heat Therapy | Trị liệu bằng nhiệt | Modality |
| Manual Therapy | Trị liệu bằng tay | Treatment technique |
| Numbness | Tê/Tê dại | Sensory symptom |
| Outcome | Kết quả | Treatment result |
| Pain | Đau | Primary symptom |
| Physical Therapy | Vật lý trị liệu | Treatment type |
| Posture | Tư thế | Body position |
| Prognosis | Tiên lượng | Expected outcome |
| Range of Motion | Biên độ chuyển động | ROM assessment |
| Rehabilitation | Phục hồi chức năng | Treatment goal |
| Repetition | Số lần lặp lại | Reps in exercise |
| Set | Hiệp/Lần | Group of reps |
| Strength | Sức mạnh | Force-generating ability |
| Stretching | Kéo giãn/Giãn cơ | Exercise type |
| Stroke (CVA) | Đột quỵ | Neurological condition |
| Swelling | Sưng | Physical symptom |
| Tendon | Gân | Anatomical structure |
| Tingling | Ngứa ran/Nứa ran | Neurological symptom |
| Treatment | Điều trị | Clinical intervention |
| Treatment Plan | Kế hoạch điều trị | Documentation type |
| Weakness | Yếu | Muscular symptom |

#### Vietnamese to English - Common PT Terms

| Vietnamese | English | Context/Use |
|-----------|---------|------------|
| Bài tập | Exercise | Intervention type |
| Bảo hiểm y tế | Health Insurance (BHYT) | Insurance type |
| Biên độ chuyển động | Range of Motion (ROM) | Assessment |
| Căng cơ | Muscle strain | Muscle injury |
| Chẩn đoán | Diagnosis | Assessment result |
| Cối | Joint | Anatomical structure |
| Cơ | Muscle | Anatomical structure |
| Cổ | Neck | Body region |
| Cổ tay | Wrist | Body region |
| Cứng | Stiffness | Symptom |
| Đau | Pain | Primary symptom |
| Đau âm ỉ | Dull ache/Aching pain | Pain quality |
| Đau buốt | Throbbing pain | Pain quality |
| Đau nhói | Sharp pain/Stabbing pain | Pain quality |
| Đau rát | Burning pain | Pain quality |
| Đau lan tỏa | Radiating pain | Pain pattern |
| Đánh giá | Assessment | Documentation type |
| Đầu gối | Knee | Body region |
| Điều trị | Treatment | Clinical intervention |
| Độ mềm dẻo | Flexibility | Assessment domain |
| Dây chằng | Ligament | Anatomical structure |
| Dây chằng chéo trước | Anterior Cruciate Ligament (ACL) | Knee structure |
| Dây quay | Rotator cuff | Shoulder structure |
| Duỗi | Extension | Movement type |
| Gấp | Flexion | Movement type |
| Gãy xương | Fracture | Bone injury |
| Gân | Tendon | Anatomical structure |
| Giãn cơ | Stretching | Exercise type |
| Giáo dục tư thế | Posture education | Education topic |
| Hàng ngày | Daily | Frequency |
| Hông | Hip | Body region |
| Hội chứng | Syndrome | Condition type |
| Hoạt động sinh hoạt | Activities of Daily Living (ADL) | Functional assessment |
| Huyết áp | Blood pressure | Vital sign |
| Khó khăn chức năng | Functional disability | Functional limitation |
| Khuyết tật | Disability | Functional limitation |
| Kiểm tra sức cơ | Manual Muscle Test (MMT) | Strength assessment |
| Khuỷu tay | Elbow | Body region |
| Liệt nửa mặt | Bell's Palsy | Neurological condition |
| Liệu pháp vật lý | Physical therapy | Treatment type |
| Lưng | Back | Body region |
| Mục tiêu | Goal | Treatment objective |
| Mềm dẻo | Flexibility | Movement quality |
| Mỗi tuần | Weekly | Frequency |
| Nâng | Elevation | Movement type |
| Ngứa ran | Tingling | Neurological symptom |
| Ngủ | Sleep | Functional activity |
| Nhẹ | Mild/Light | Severity descriptor |
| Nặng | Severe | Severity descriptor |
| Nằm | Lying/Supine | Position |
| Phục hồi chức năng | Rehabilitation | Treatment goal |
| Rách | Tear | Tissue injury |
| Sức mạnh | Strength | Force-generating ability |
| Sưng | Swelling | Physical symptom |
| Tây | Arm | Body region |
| Tê | Numbness | Sensory symptom |
| Tế bào | Cell | Biological structure |
| Tần suất | Frequency | How often |
| Thăng bằng | Balance | Assessment domain |
| Tiêm | Injection | Medical procedure |
| Tiên lượng | Prognosis | Expected outcome |
| Thoát vị | Hernia/Prolapse | Tissue displacement |
| Tiếng Việt | Vietnamese | Language |
| Tín hiệu | Signal/Symptom | Clinical indication |
| Trị liệu | Treatment | Clinical intervention |
| Trị liệu bằng tay | Manual therapy | Treatment technique |
| Trị liệu bằng nhiệt | Heat therapy | Modality |
| Tuân thủ | Compliance/Adherence | Program adherence |
| Tư thế | Posture | Body position |
| Tương tư | Similar/Analogue | Comparison |
| Vai | Shoulder | Body region |
| Vật lý trị liệu | Physical therapy | Treatment type |
| Viêm | Inflammation | Pathological process |
| Viêm khớp | Arthritis | Joint disease |
| Xu hướng | Trend | Data pattern |
| Xoay | Rotation | Movement type |
| Yếu | Weakness | Muscular symptom |
| Đột quỵ | Stroke (CVA) | Neurological condition |
| Đĩa đệm | Intervertebral disc | Spinal structure |
| Đốt sống | Vertebra | Spinal structure |

---

## Document Information

**Document Title:** Vietnamese Physiotherapy Module - Operating Manual (English)
**Version:** 1.0
**Release Date:** November 20, 2025
**Last Updated:** November 20, 2025
**Author(s):** OpenEMR Vietnamese Physiotherapy Module Team
**Intended Audience:** Physiotherapists, Physical Therapists, Rehabilitation Specialists, Healthcare Providers
**Software Version:** OpenEMR 7.0.0+ with Vietnamese Physiotherapy Module
**Language:** English
**Complementary Documents:** Vietnamese version (OPERATING_MANUAL_VI.md)

**Revision History:**

| Version | Date | Changes | Author |
|---------|------|---------|--------|
| 1.0 | Nov 20, 2025 | Initial comprehensive operating manual | PT Module Team |

**Document Status:** FINAL - Ready for distribution to users

**Feedback and Updates:**

We welcome feedback, corrections, and suggestions for improvement:
- Submit to: System Administrator or Module Developer
- Include: Your name, date, specific section, and suggested change
- Type: Bug reports, feature suggestions, documentation improvements

---

**This manual is a living document and will be updated as the module evolves. Check the version date to ensure you have the most current version.**

---

**END OF OPERATING MANUAL (ENGLISH)**

# Vietnamese Physiotherapy Module - Operating Manual

**Complete User Guide for Healthcare Providers**
*OpenEMR Vietnamese PT Module v1.0*

---

## Table of Contents

1. [Quick Start Guide](#1-quick-start-guide)
2. [Accessing PT Features](#2-accessing-pt-features)
3. [PT Assessment Form](#3-pt-assessment-form)
4. [Exercise Prescription](#4-exercise-prescription)
5. [Treatment Plan](#5-treatment-plan)
6. [Outcome Measures](#6-outcome-measures)
7. [Patient Summary Widget](#7-patient-summary-widget)
8. [Medical Terminology Translation](#8-medical-terminology-translation)
9. [Best Practices](#9-best-practices)
10. [Common Workflows](#10-common-workflows)
11. [Troubleshooting](#11-troubleshooting)
12. [Quick Reference](#12-quick-reference)

---

## 1. Quick Start Guide

### What is the Vietnamese PT Module?

The Vietnamese Physiotherapy (PT) module is a comprehensive bilingual system integrated into OpenEMR that provides specialized tools for physiotherapy practice with full Vietnamese language support. It includes:

- **4 Clinical Forms**: Assessment, Exercise Prescription, Treatment Plan, Outcome Measures
- **Bilingual Interface**: Complete Vietnamese/English parallel documentation
- **Patient Widget**: Quick access from patient summary page
- **Medical Translation**: Built-in Vietnamese medical terminology database

### Prerequisites

Before using the PT module, ensure:
- [ ] You have OpenEMR access with appropriate permissions
- [ ] Your user account has physiotherapy privileges
- [ ] You are familiar with basic OpenEMR navigation
- [ ] You understand how to access patient encounters

### First-Time Setup

1. **Log into OpenEMR** with your credentials
2. **Navigate to a patient** record (Calendar → Click appointment → Open patient chart)
3. **Open or create an encounter** (Patient Dashboard → Encounter → New Encounter)
4. **Look for PT forms** in the Forms menu or Patient Summary widget

---

## 2. Accessing PT Features

### From Patient Encounter

The primary way to access PT forms is through a patient encounter:

1. **Open Patient Chart**
   - Navigate to: Calendar → Select appointment → Open patient
   - Or: Search patient → Open chart

2. **Create/Open Encounter**
   - Click "Encounter" tab or "New Encounter"
   - Select encounter date and provider

3. **Add PT Form**
   - Click "Add Form" or "Forms" dropdown
   - Look for Vietnamese PT forms:
     - Vietnamese PT Assessment
     - Vietnamese PT Exercise Prescription
     - Vietnamese PT Treatment Plan
     - Vietnamese PT Outcome Measures

[Screenshot: Encounter page with Forms dropdown menu showing PT forms]

### From Patient Summary Widget

Quick access buttons available on the patient summary page:

1. **Locate PT Widget**
   - Open patient summary page
   - Scroll to "Vietnamese Physiotherapy" section
   - Widget displays recent assessments, active exercises, and treatment plans

2. **Use Quick Add Buttons**
   - Click green "+ New" buttons next to each section
   - Forms open directly in the current encounter

[Screenshot: Patient summary page showing PT widget with recent data and "Add New" buttons]

---

## 3. PT Assessment Form

The PT Assessment form is the primary tool for documenting patient evaluations, including chief complaints, pain assessment, functional goals, and treatment plans.

### Creating a New Assessment

#### Step 1: Open the Form

1. Navigate to patient encounter
2. Click "Add Form" → "Vietnamese PT Assessment"
3. Form opens in new/update mode

#### Step 2: Select Language Preference

At the top of the form:
- **Vietnamese**: Form displays Vietnamese fields only
- **English**: Form displays English fields only
- **Both** (Recommended): Displays both Vietnamese and English fields side-by-side

[Screenshot: Language preference dropdown showing three options]

> **Tip:** Select "Both" for complete documentation that serves both Vietnamese-speaking patients and English-speaking colleagues.

#### Step 3: Document Chief Complaint (Triệu chứng chính)

Fill in the patient's primary complaint:

**Vietnamese Field (Yellow background):**
```
Example: Đau lưng mãn tính từ 6 tháng, tăng khi ngồi lâu
```

**English Field (Blue background):**
```
Example: Chronic back pain for 6 months, worsens with prolonged sitting
```

> **Note:** Color-coded fields make it easy to distinguish:
> - Yellow background = Vietnamese field
> - Blue background = English field

#### Step 4: Pain Assessment (Đánh giá đau)

Document the patient's pain level and characteristics:

1. **Pain Level (0-10 Scale)**
   - Enter a number from 0 (no pain) to 10 (worst pain)
   - Visual indicator updates automatically:
     - Green (0-3): Low/Mild pain
     - Yellow (4-6): Moderate pain
     - Red (7-10): High/Severe pain

2. **Pain Location**
   - **Vietnamese**: `Ví dụ: Lưng dưới, bên phải`
   - **English**: `Example: Lower back, right side`

3. **Pain Description**
   - **Vietnamese**: `Ví dụ: Đau nhói, tăng khi ngồi lâu`
   - **English**: `Example: Sharp pain, increases with prolonged sitting`

[Screenshot: Pain assessment section showing pain level slider and color-coded indicator]

#### Step 5: Functional Goals (Mục tiêu chức năng)

Document measurable, patient-centered goals:

**Vietnamese Example:**
```
- Có thể đi bộ 30 phút không đau
- Ngủ suốt đêm không bị đau đánh thức
- Trở lại làm việc toàn thời gian
```

**English Example:**
```
- Able to walk 30 minutes without pain
- Sleep through the night without pain
- Return to full-time work
```

#### Step 6: Treatment Plan (Kế hoạch điều trị)

Outline the proposed treatment approach:

**Vietnamese Example:**
```
- Liệu pháp vật lý 3 lần/tuần trong 4 tuần
- Bài tập kéo giãn hàng ngày
- Giáo dục tư thế ngồi đúng
- Tái đánh giá sau 2 tuần
```

**English Example:**
```
- Physical therapy 3x/week for 4 weeks
- Daily stretching exercises
- Posture education
- Re-evaluation in 2 weeks
```

#### Step 7: Set Status

Select the appropriate status:
- **Draft**: Incomplete assessment, saved for later completion
- **Completed** (Default): Assessment is complete
- **Reviewed**: Assessment has been reviewed by supervisor/senior therapist

#### Step 8: Save the Assessment

1. Click **"Save Assessment"** button (blue, bottom of form)
2. Form saves and returns to encounter page
3. Assessment appears in patient's encounter list

**To Cancel Without Saving:**
- Click **"Cancel"** button (gray, bottom of form)
- Returns to previous page without saving changes

[Screenshot: Bottom of form showing Save Assessment and Cancel buttons]

### Viewing/Editing Existing Assessments

1. Navigate to patient encounter
2. Find the assessment in the encounter forms list
3. Click the assessment title to open
4. Form opens in "Update" mode with existing data pre-filled
5. Make changes as needed
6. Click "Save Assessment" to update

---

## 4. Exercise Prescription

The Exercise Prescription form allows you to create detailed, bilingual exercise programs with specific sets, reps, frequency, and instructions.

### Creating a New Exercise Prescription

#### Step 1: Open the Form

1. Navigate to patient encounter
2. Click "Add Form" → "Vietnamese PT Exercise Prescription"
3. Or use Quick Add button from PT widget

#### Step 2: Enter Exercise Name (Tên bài tập)

Provide bilingual exercise names:

**Vietnamese Field:**
```
Ví dụ: Động tác mèo-bò (Cat-Cow)
```

**English Field:**
```
Example: Cat-Cow Stretch
```

> **Best Practice:** Include the English name in parentheses in the Vietnamese field for exercises with common English names.

#### Step 3: Enter Description (Mô tả)

Describe how to perform the exercise:

**Vietnamese Example:**
```
Quỳ bốn chân, tay thẳng dưới vai, đầu gối dưới hông.
Hít vào: võng lưng xuống, ngẩng đầu lên (tư thế bò).
Thở ra: gù lưng lên, cúi đầu xuống (tư thế mèo).
Chuyển động chậm và mượt mà.
```

**English Example:**
```
Start on hands and knees, hands under shoulders, knees under hips.
Inhale: Drop belly, lift head (cow pose).
Exhale: Round spine, drop head (cat pose).
Move slowly and smoothly.
```

#### Step 4: Set Prescription Details (Chi tiết kê đơn)

Configure the exercise parameters:

1. **Sets (Số hiệp)**: Number of sets
   - Enter value: 1-10
   - Default: 3 sets
   - Example: `3`

2. **Reps (Số lần)**: Repetitions per set
   - Enter value: 1-50
   - Default: 10 reps
   - Example: `10`

3. **Duration (Thời gian)**: Time per set in minutes
   - Optional field
   - Use for timed exercises (e.g., plank holds)
   - Example: `5` (for 5-minute hold)

4. **Frequency Per Week (Tần suất mỗi tuần)**
   - Enter days per week: 1-7
   - Default: 5 days/week
   - Example: `5` (Monday-Friday)

5. **Intensity Level (Mức độ)**
   - **Low (Nhẹ)**: For initial rehab, gentle exercises
   - **Moderate (Trung bình)**: Standard therapeutic exercises (Default)
   - **High (Cao)**: For advanced patients, strengthening phase

6. **Start Date**: When to begin the exercise
   - Default: Today's date
   - Click calendar icon to select date

7. **End Date** (Optional): When to stop or re-evaluate
   - Leave blank for ongoing exercises
   - Set specific date for temporary exercises

[Screenshot: Prescription details section showing sets, reps, frequency, intensity fields]

#### Step 5: Add Instructions (Hướng dẫn)

Provide additional guidance for the patient:

**Vietnamese Example:**
```
- Thực hiện bài tập vào buổi sáng và buổi tối
- Dừng lại nếu cảm thấy đau tăng
- Có thể thực hiện trên giường nếu sàn khó khăn
- Kết hợp với hơi thở sâu
```

**English Example:**
```
- Perform morning and evening
- Stop if pain increases
- Can be done on bed if floor is difficult
- Combine with deep breathing
```

#### Step 6: Specify Equipment (Thiết bị cần thiết)

List any equipment needed:

**Examples:**
```
Yoga mat, resistance band, chair for support
Thảm yoga, dây kháng lực, ghế để tựa
```

#### Step 7: Add Precautions (Lưu ý)

Document safety considerations:

**Vietnamese Example:**
```
- Không làm nếu đau cấp tính
- Tránh động tác gật gù đầu quá mạnh
- Giữ cột sống trung tính, không quá võng
```

**English Example:**
```
- Avoid if acute pain present
- Don't force excessive neck movement
- Keep spine neutral, don't over-arch
```

#### Step 8: Save the Prescription

1. Click **"Save Exercise Prescription"** button
2. Prescription is saved to patient's record
3. Marked as "Active" and appears in PT widget

[Screenshot: Complete exercise prescription form]

### Managing Multiple Exercises

To create a comprehensive exercise program:

1. **Create each exercise separately** using the form
2. **Use consistent start/end dates** for exercises that are part of the same program
3. **Vary intensity levels** for progressive loading
4. **Document progression** by creating new prescriptions with updated parameters

### Example: Complete Home Exercise Program

**Exercise 1**: Cat-Cow Stretch
- Sets: 2 | Reps: 10 | Frequency: 7x/week | Intensity: Low

**Exercise 2**: Pelvic Tilts
- Sets: 3 | Reps: 15 | Frequency: 5x/week | Intensity: Moderate

**Exercise 3**: Dead Bug
- Sets: 3 | Reps: 10 | Frequency: 5x/week | Intensity: Moderate

**Exercise 4**: Bird Dog
- Sets: 3 | Reps: 8/side | Frequency: 3x/week | Intensity: High

---

## 5. Treatment Plan

The Treatment Plan form provides structured documentation for overall treatment strategy, including diagnosis, timeline, goals, and status tracking.

### Creating a New Treatment Plan

#### Step 1: Open the Form

1. Navigate to patient encounter
2. Click "Add Form" → "Vietnamese PT Treatment Plan"
3. Or use Quick Add button from PT widget

#### Step 2: Enter Plan Name

Provide a descriptive name for the treatment plan:

**Examples:**
```
Lower Back Pain Rehabilitation Program
Chương trình phục hồi chức năng đau lưng
```

```
Post-Surgical Knee Rehab - Phase 1
Phục hồi chức năng sau phẫu thuật đầu gối - Giai đoạn 1
```

> **Tip:** Include the phase or timeframe in the name for multi-phase treatments.

#### Step 3: Document Diagnosis (Chẩn đoán)

Enter the primary diagnosis in both languages:

**Vietnamese Field:**
```
Thoát vị đĩa đệm L4-L5 với chèn ép rễ thần kinh
```

**English Field:**
```
L4-L5 disc herniation with nerve root compression
```

**Common Vietnamese PT Diagnoses:**
- Đau lưng mãn tính (Chronic low back pain)
- Viêm khớp gối (Knee arthritis)
- Hội chứng ống cổ tay (Carpal tunnel syndrome)
- Rách dây chằng chéo trước (ACL tear)
- Đau vai mãn tính (Chronic shoulder pain)
- Liệt dây thần kinh mặt (Bell's palsy)
- Đột quỵ (Stroke/CVA)
- Chấn thương cột sống cổ (Cervical spine injury)

[Screenshot: Diagnosis section with bilingual fields]

#### Step 4: Set Timeline

Configure the treatment timeline:

1. **Start Date**: When treatment begins
   - Default: Today's date
   - Use calendar picker to select

2. **Estimated Duration (Weeks)**: Expected treatment length
   - Enter number of weeks: 1-52
   - Default: 8 weeks
   - Common durations:
     - Acute conditions: 2-4 weeks
     - Subacute conditions: 4-8 weeks
     - Chronic conditions: 8-12 weeks
     - Post-surgical rehab: 12-24 weeks

3. **Status**: Current plan status
   - **Active** (Default): Treatment plan is currently being implemented
   - **Completed**: Patient has finished the treatment plan
   - **On Hold**: Treatment temporarily paused (e.g., patient travel, medical hold)

[Screenshot: Timeline section showing date pickers and duration field]

#### Step 5: Document Goals (Optional - via linked forms)

Treatment goals are typically documented in:
- **PT Assessment**: Functional goals section
- **Outcome Measures**: Target values for measurable outcomes

> **Workflow Tip:** Create the Assessment form first to document goals, then create the Treatment Plan referencing those goals.

#### Step 6: Save the Treatment Plan

1. Click **"Save Treatment Plan"** button
2. Plan is saved and appears in:
   - Patient encounter forms list
   - PT widget "Active Treatment Plans" section
   - PT reports and summaries

### Managing Treatment Plan Status

As treatment progresses, update the status:

1. **Open existing treatment plan** (click title in encounter)
2. **Change status field**:
   - `Active` → `Completed` when patient achieves goals
   - `Active` → `On Hold` when treatment is paused
   - `On Hold` → `Active` when resuming treatment
3. **Save changes**

### Multi-Phase Treatment Example

For complex cases, create separate plans for each phase:

**Phase 1**: Acute Pain Management (Weeks 1-2)
- Status: Completed
- Estimated Duration: 2 weeks

**Phase 2**: Mobility & Strengthening (Weeks 3-6)
- Status: Active
- Estimated Duration: 4 weeks

**Phase 3**: Functional Restoration (Weeks 7-10)
- Status: Active
- Estimated Duration: 4 weeks

---

## 6. Outcome Measures

The Outcome Measures form tracks objective, measurable patient progress across multiple domains: Range of Motion (ROM), Strength, Pain, Function, and Balance.

### Creating a New Outcome Measure

#### Step 1: Open the Form

1. Navigate to patient encounter
2. Click "Add Form" → "Vietnamese PT Outcome Measures"
3. Or use Quick Add button from PT widget

#### Step 2: Select Measure Type

Choose the type of outcome you're measuring:

- **ROM (Range of Motion)**: Joint mobility measurements
- **Strength**: Muscle strength assessments
- **Pain**: Pain level tracking
- **Function**: Functional status/disability indices
- **Balance**: Balance and stability measures

[Screenshot: Measure type dropdown with 5 options]

#### Step 3: Set Measurement Date

- Default: Today's date
- Use calendar picker to select specific date
- For baseline measures: Use initial assessment date
- For progress measures: Use current date

#### Step 4: Enter Values

Document three key values:

1. **Baseline Value** (Optional but recommended)
   - Initial measurement at start of treatment
   - Use for calculating progress percentage
   - Example: `30` (degrees ROM) or `3` (pain level)

2. **Current Value** (Required)
   - Today's measurement
   - Must be numeric
   - Decimals allowed (e.g., `45.5`)
   - Example: `50` (degrees ROM) or `2` (pain level)

3. **Target Value** (Optional)
   - Goal or expected outcome
   - Used for progress tracking
   - Example: `90` (degrees ROM) or `0` (pain level)

[Screenshot: Values section showing baseline, current, target input fields]

#### Step 5: Specify Unit of Measurement

Enter the unit for your measurement:

**Common Units by Type:**

**ROM Measurements:**
- `degrees` (most common)
- `inches` or `cm` (for linear measurements)

**Strength Measurements:**
- `kg` or `lbs` (weight lifted)
- `MMT 0-5` (Manual Muscle Test scale)
- `repetitions` (functional strength)

**Pain Measurements:**
- `0-10 scale` (VAS/NRS)
- `0-100 mm` (Visual Analog Scale)

**Functional Measurements:**
- `LEFS score` (Lower Extremity Functional Scale: 0-80)
- `DASH score` (Disability of Arm, Shoulder, Hand: 0-100)
- `Oswestry %` (Low back disability: 0-100%)
- `seconds` (timed tests like TUG)
- `meters` (walk tests)

**Balance Measurements:**
- `Berg score` (Berg Balance Scale: 0-56)
- `seconds` (single leg stance time)
- `SEBT cm` (Star Excursion Balance Test)

[Screenshot: Unit field with example entries]

#### Step 6: Add Notes

Document any relevant context:

**Examples:**
```
Patient able to flex knee further today. No pain at end range.
Đầu gối gấp được nhiều hơn hôm nay. Không đau ở cuối biên độ.
```

```
Used 3kg weight, decreased from 5kg due to pain flare-up.
Sử dụng tạ 3kg, giảm từ 5kg do đau tăng.
```

```
First measurement post-surgery. Baseline established.
Đo lần đầu sau phẫu thuật. Thiết lập giá trị nền.
```

#### Step 7: Save the Measure

1. Click **"Save Outcome Measure"** button
2. Measure is saved to patient's record
3. Can be viewed in reports and progress charts

### Complete Examples by Measure Type

#### Example 1: ROM - Knee Flexion

- **Measure Type**: ROM
- **Measurement Date**: 2025-11-20
- **Baseline Value**: 30
- **Current Value**: 65
- **Target Value**: 120
- **Unit**: degrees
- **Notes**: Post-ACL reconstruction, week 6. Good progress.

#### Example 2: Strength - Quadriceps

- **Measure Type**: Strength
- **Measurement Date**: 2025-11-20
- **Baseline Value**: 3
- **Current Value**: 4
- **Target Value**: 5
- **Unit**: MMT 0-5
- **Notes**: Improved from 3/5 to 4/5. Patient can now resist moderate force.

#### Example 3: Pain - Lower Back

- **Measure Type**: Pain
- **Measurement Date**: 2025-11-20
- **Baseline Value**: 8
- **Current Value**: 3
- **Target Value**: 0
- **Unit**: 0-10 scale
- **Notes**: Significant improvement. Pain only with prolonged standing.

#### Example 4: Function - Lower Extremity

- **Measure Type**: Function
- **Measurement Date**: 2025-11-20
- **Baseline Value**: 35
- **Current Value**: 52
- **Target Value**: 70
- **Unit**: LEFS score
- **Notes**: Patient reports easier stairs, still difficulty with squatting.

#### Example 5: Balance - Single Leg Stance

- **Measure Type**: Balance
- **Measurement Date**: 2025-11-20
- **Baseline Value**: 5
- **Current Value**: 18
- **Target Value**: 30
- **Unit**: seconds
- **Notes**: Right leg, eyes open. Much improved stability.

### Tracking Progress Over Time

To effectively monitor patient progress:

1. **Establish Baseline**: Record initial values at first assessment
2. **Regular Measurements**: Repeat same measures every 1-2 weeks
3. **Consistent Units**: Always use the same unit for each measure type
4. **Document Changes**: Note why values improve/worsen in Notes field
5. **Visual Review**: Use reports/charts to visualize progress trends

---

## 7. Patient Summary Widget

The Patient Summary Widget provides at-a-glance access to all PT data directly from the patient summary page.

### Widget Location

The widget appears on the patient summary page:
1. Open patient chart
2. Click "Summary" or "Patient Dashboard"
3. Scroll to find "Vietnamese Physiotherapy" section (green header)

[Screenshot: Patient summary page with PT widget visible]

### Widget Components

The widget displays three key sections:

#### Section 1: Recent Assessments

**What It Shows:**
- Last 3 PT assessments
- Assessment date
- Chief complaint (first 50 characters)
- Pain level with color-coded badge:
  - Green badge (0-3): Low pain
  - Yellow badge (4-6): Moderate pain
  - Red badge (7-10): High pain
- Status (Draft, Completed, Reviewed)

**Quick Actions:**
- Click "+ New" button to create new assessment
- Click assessment row to view details

[Screenshot: Recent Assessments section with table of 3 assessments]

#### Section 2: Active Exercise Prescriptions

**What It Shows:**
- Up to 5 active exercises
- Exercise name (Vietnamese or English)
- Sets, reps, frequency (e.g., "3 sets × 10 reps - 5x/week")
- Intensity level badge (Low/Moderate/High)

**Quick Actions:**
- Click "+ New" button to create new exercise prescription
- Review current home exercise program at a glance

[Screenshot: Active Exercise Prescriptions list with 3 exercises]

#### Section 3: Active Treatment Plans

**What It Shows:**
- All active treatment plans
- Plan name
- Start date
- Estimated duration (in weeks)
- Status badge (Active/Completed/On Hold)

**Quick Actions:**
- Click "+ New" button to create new treatment plan
- Monitor active treatment timelines

[Screenshot: Active Treatment Plans section with 2 plans]

### Using Quick Add Buttons

Each section has a "+ New" button for rapid data entry:

1. **Click the green "+ New" button** next to the section you want to add to
2. **Form opens in current encounter** (or creates new encounter if needed)
3. **Fill out the form** as described in sections 3-6
4. **Save the form**
5. **Widget updates automatically** to show new data

> **Benefit:** Skip navigating through menus - one click goes directly to the form.

### Widget Updates

The widget automatically refreshes when:
- New PT forms are saved
- Existing forms are updated
- Exercise prescriptions are activated/deactivated
- Treatment plan status changes

> **Note:** If you don't see recent changes, refresh the browser page (F5 or Ctrl+R).

### When Widget Shows "No Data"

If sections show messages like "No assessments recorded":

**Meaning:** Patient has no data for that section yet.

**Action:**
1. Click the "+ New" button to create first entry
2. Fill out and save the form
3. Widget will display the new data

---

## 8. Medical Terminology Translation

The Vietnamese PT module includes a built-in medical terminology database with 52+ pre-loaded Vietnamese medical terms.

### How Translation Works

The system uses database functions to translate medical terms:

**English to Vietnamese:**
```sql
get_vietnamese_term('pain') → 'đau'
```

**Vietnamese to English:**
```sql
get_english_term('đau') → 'pain'
```

### Pre-loaded Medical Terms

The database includes commonly used physiotherapy terms:

| English | Vietnamese |
|---------|-----------|
| pain | đau |
| physical therapy | vật lý trị liệu |
| assessment | đánh giá |
| treatment | điều trị |
| exercise | bài tập |
| rehabilitation | phục hồi chức năng |
| strength | sức mạnh |
| flexibility | độ mềm dẻo |
| range of motion | biên độ chuyển động |
| balance | thăng bằng |
| posture | tư thế |
| joint | khớp |
| muscle | cơ |
| spine | cột sống |
| shoulder | vai |
| knee | đầu gối |
| back | lưng |
| neck | cổ |
| acute | cấp tính |
| chronic | mãn tính |
| inflammation | viêm |
| swelling | sưng |
| stiffness | cứng |
| weakness | yếu |
| numbness | tê |
| tingling | ngứa ran |
| fracture | gãy xương |
| sprain | bong gân |
| strain | căng cơ |
| arthritis | viêm khớp |

### Using Bilingual Fields Effectively

#### Best Practices:

1. **Complete Both Fields When Possible**
   - Serves bilingual documentation needs
   - Useful for medical records review
   - Facilitates communication across teams

2. **Priority Language Based on Context**
   - **Patient communication**: Prioritize Vietnamese
   - **Medical records**: Complete both
   - **Team communication**: May prioritize English

3. **Use Consistent Terminology**
   - Reference pre-loaded terms above
   - Maintain consistency across forms
   - Use medical terms, not colloquial expressions

4. **Translation Tips**
   - Start with Vietnamese if patient is Vietnamese-speaking
   - Use the term reference table above for accuracy
   - When unsure, consult Vietnamese medical terminology resources

#### Example Workflow:

**Scenario:** Vietnamese-speaking patient with knee pain

**Step 1:** Document in Vietnamese first (patient's language)
```
Triệu chứng chính: Đau đầu gối bên trong khi đi cầu thang
```

**Step 2:** Translate to English for medical record
```
Chief Complaint: Medial knee pain when climbing stairs
```

**Step 3:** Verify medical terms match
- đau = pain ✓
- đầu gối = knee ✓
- cầu thang = stairs ✓

### Common Vietnamese Medical Phrases

**Pain Descriptions:**
- Đau nhói (Sharp/stabbing pain)
- Đau âm ỉ (Dull/aching pain)
- Đau rát (Burning pain)
- Đau buốt (Throbbing pain)
- Đau lan tỏa (Radiating pain)

**Movement Terms:**
- Gấp (Flexion)
- Duỗi (Extension)
- Xoay (Rotation)
- Nghiêng (Side bending)
- Nâng (Elevation)
- Hạ (Depression)

**Frequency Terms:**
- Hàng ngày (Daily)
- Mỗi tuần (Weekly)
- 2 lần/ngày (Twice daily)
- 3 lần/tuần (3 times per week)
- Mỗi giờ (Hourly)

**Severity Terms:**
- Nhẹ (Mild/Light)
- Trung bình (Moderate)
- Nặng (Severe)
- Rất nặng (Very severe)

---

## 9. Best Practices

### Documentation Standards

#### 1. Completeness

**DO:**
- Fill out both Vietnamese and English fields
- Document pain levels numerically (0-10 scale)
- Include specific functional limitations
- Set measurable, time-bound goals
- Record baseline outcome measures

**DON'T:**
- Leave one language blank if you select "Both"
- Use vague terms like "better" or "worse"
- Skip baseline measurements
- Set unrealistic goals without patient input

#### 2. Consistency

**DO:**
- Use standard medical terminology
- Reference the pre-loaded term list
- Maintain consistent abbreviations
- Use same units for repeated measurements
- Document at regular intervals (e.g., weekly)

**DON'T:**
- Mix colloquial and medical terms
- Switch units between measurements
- Skip scheduled re-assessments
- Use inconsistent naming conventions

#### 3. Timeliness

**DO:**
- Document during or immediately after patient visit
- Complete assessments on initial visit
- Update outcome measures at regular intervals
- Mark completed plans as "Completed" promptly

**DON'T:**
- Delay documentation until end of day/week
- Backdate entries
- Leave forms in "Draft" status indefinitely

### Language Selection Guidelines

#### When to Use Vietnamese Only:

- Patient speaks only Vietnamese
- Documentation for patient handouts
- Direct patient communication notes
- Forms that patient will sign/review

#### When to Use English Only:

- International medical record sharing
- Research data collection
- Communication with English-only colleagues
- Billing/insurance documentation (if required)

#### When to Use Both (Recommended):

- **Primary documentation** (assessments, plans)
- **Teaching hospitals** with multilingual teams
- **Medical-legal documentation**
- **Comprehensive patient records**
- **Quality assurance reviews**

### Clinical Workflows

#### New Patient Workflow:

1. **Session 1** (Initial Evaluation - 45-60 min)
   - Create PT Assessment (complete all sections)
   - Record baseline Outcome Measures (ROM, Pain, Function)
   - Create Treatment Plan (8-12 week timeframe)
   - Prescribe 2-3 initial Exercises (low-moderate intensity)
   - Patient education and home program instructions

2. **Session 2-3** (Early Treatment - 30-45 min)
   - Brief progress note (may use encounter notes)
   - Update Exercise Prescriptions (modify based on tolerance)
   - No formal outcome measures yet

3. **Session 4-6** (Mid-Treatment - 30-45 min)
   - Progress Assessment (2-week mark)
   - Record Outcome Measures (compare to baseline)
   - Update Treatment Plan if needed
   - Progress Exercises (increase intensity/complexity)

4. **Session 7-12** (Late Treatment - 30-45 min)
   - Continue regular treatment
   - Weekly outcome measure tracking
   - Gradual exercise progression

5. **Final Session** (Discharge - 45 min)
   - Create final PT Assessment
   - Record final Outcome Measures
   - Mark Treatment Plan as "Completed"
   - Provide final Exercise Prescription (home maintenance)
   - Discharge summary and patient education

#### Follow-up Visit Workflow (Returning Patient):

1. **Review previous records** (5 min)
   - Check PT widget for recent activity
   - Review last assessment and outcome measures
   - Check current exercise prescriptions

2. **Conduct treatment** (25-30 min)
   - Provide manual therapy, modalities, supervised exercises
   - Document in encounter notes

3. **Update documentation** (5-10 min)
   - If 2+ weeks since last formal assessment: Create new Assessment
   - If significant change: Update Outcome Measures
   - If exercise changes needed: Update Exercise Prescriptions
   - If plan changes needed: Update Treatment Plan status

### Quality Assurance Tips

#### Self-Review Checklist:

Before closing patient encounter, verify:
- [ ] All required fields completed
- [ ] Pain levels documented numerically
- [ ] Both language fields filled (if "Both" selected)
- [ ] Outcome measures have baseline values
- [ ] Exercise prescriptions are marked "Active"
- [ ] Treatment plan status is current
- [ ] No spelling/grammar errors in key fields
- [ ] Forms saved successfully (not left as drafts)

#### Common Errors to Avoid:

1. **Missing baseline values** - Always record initial measurements
2. **Inconsistent units** - Use same unit across all measurements for a patient
3. **Vague functional goals** - Make goals specific and measurable
4. **Outdated exercise status** - Deactivate exercises that are no longer prescribed
5. **Forgotten status updates** - Update treatment plan status when patient completes/stops
6. **Single-language documentation** - Complete both languages when feasible

---

## 10. Common Workflows

### Workflow 1: Initial Patient Assessment (New Patient)

**Scenario:** New patient with chronic low back pain

**Time Required:** 45-60 minutes

**Steps:**

1. **Create Encounter** (2 min)
   - Open patient chart
   - Click "New Encounter"
   - Set encounter date and provider

2. **PT Assessment** (25-30 min)
   - Click "Add Form" → "Vietnamese PT Assessment"
   - Select language: "Both"
   - Document:
     - Chief Complaint (Vi): `Đau lưng mãn tính 6 tháng`
     - Chief Complaint (En): `Chronic low back pain 6 months`
     - Pain Level: `7`
     - Pain Location: `Lưng dưới, lan xuống mông bên trái` / `Lower back, radiating to left buttock`
     - Functional Goals (Vi): `Ngủ suốt đêm không đau, đi bộ 30 phút`
     - Functional Goals (En): `Sleep through night, walk 30 minutes`
     - Treatment Plan: Document proposed approach
   - Save Assessment

3. **Baseline Outcome Measures** (5-10 min)
   - Create multiple outcome measure forms:

   **Measure 1 - Pain:**
   - Type: Pain
   - Baseline: 7, Current: 7, Target: 2
   - Unit: `0-10 scale`

   **Measure 2 - Function:**
   - Type: Function
   - Baseline: 35, Current: 35, Target: 65
   - Unit: `Oswestry %`

   **Measure 3 - ROM:**
   - Type: ROM
   - Baseline: 30, Current: 30, Target: 60
   - Unit: `degrees`
   - Notes: `Lumbar flexion`

4. **Treatment Plan** (5 min)
   - Click "Add Form" → "Vietnamese PT Treatment Plan"
   - Plan Name: `Chronic Low Back Pain Rehabilitation`
   - Diagnosis (Vi): `Đau lưng dưới mãn tính`
   - Diagnosis (En): `Chronic low back pain`
   - Start Date: Today
   - Duration: `8` weeks
   - Status: `Active`
   - Save Plan

5. **Initial Exercise Prescription** (5-10 min)
   - Create 2-3 exercises:

   **Exercise 1: Pelvic Tilts**
   - Name (Vi): `Nghiêng khung chậu`
   - Name (En): `Pelvic Tilts`
   - Sets: 3, Reps: 10, Frequency: 7x/week
   - Intensity: Low
   - Instructions: Include lying position, breathing cues

   **Exercise 2: Cat-Cow**
   - Name (Vi): `Động tác mèo-bò`
   - Name (En): `Cat-Cow Stretch`
   - Sets: 2, Reps: 10, Frequency: 7x/week
   - Intensity: Low

   **Exercise 3: Dead Bug**
   - Name (Vi): `Động tác con bọ chết`
   - Name (En): `Dead Bug`
   - Sets: 2, Reps: 8, Frequency: 5x/week
   - Intensity: Moderate

6. **Patient Education** (5-10 min)
   - Print or review exercise handouts
   - Explain treatment plan timeline
   - Schedule next appointment
   - Provide home exercise log sheet

**Total Forms Created:** 1 Assessment, 3 Outcome Measures, 1 Treatment Plan, 3 Exercise Prescriptions

---

### Workflow 2: Progress Re-Assessment (Week 2-4)

**Scenario:** Patient returning for 2-week progress check

**Time Required:** 35-45 minutes

**Steps:**

1. **Review Previous Documentation** (5 min)
   - Open patient chart
   - Check PT widget for recent activity
   - Review initial assessment and baseline measures
   - Review current exercise prescriptions

2. **Conduct Treatment Session** (20-25 min)
   - Provide hands-on treatment
   - Supervise exercises
   - Assess patient's home program compliance

3. **Update Outcome Measures** (5-10 min)
   - Create new outcome measure forms with current values:

   **Pain Update:**
   - Type: Pain
   - Baseline: 7, Current: 4, Target: 2
   - Unit: `0-10 scale`
   - Notes: `Improved, pain mainly AM only now`

   **Function Update:**
   - Type: Function
   - Baseline: 35, Current: 48, Target: 65
   - Unit: `Oswestry %`
   - Notes: `Better with sitting, still difficulty with lifting`

4. **Progress Exercise Program** (5 min)
   - Open existing exercises, increase intensity:

   **Update Dead Bug:**
   - Sets: 3 (was 2), Reps: 10 (was 8), Frequency: 5x/week
   - Intensity: Moderate

   **Add New Exercise: Bridge**
   - Name (Vi): `Nâng mông`
   - Name (En): `Glute Bridge`
   - Sets: 3, Reps: 12, Frequency: 5x/week
   - Intensity: Moderate

5. **Update Treatment Plan (if needed)** (2 min)
   - Open Treatment Plan
   - Keep Status: `Active` (if progressing as expected)
   - Or change to `On Hold` if patient needs to pause
   - Save changes

6. **Brief Progress Note** (3 min)
   - May use encounter soap note OR
   - Create brief PT Assessment update
   - Document patient's response to treatment

---

### Workflow 3: Discharge Planning (Final Visit)

**Scenario:** Patient completing 8-week treatment program

**Time Required:** 45-50 minutes

**Steps:**

1. **Final Treatment Session** (20-25 min)
   - Complete final hands-on treatment
   - Review all exercises, ensure proper form
   - Discuss long-term maintenance

2. **Final Outcome Measures** (10 min)
   - Record final measurements:

   **Pain Final:**
   - Type: Pain
   - Baseline: 7, Current: 2, Target: 2
   - Unit: `0-10 scale`
   - Notes: `Goal achieved. Minimal pain with prolonged activity only`

   **Function Final:**
   - Type: Function
   - Baseline: 35, Current: 68, Target: 65
   - Unit: `Oswestry %`
   - Notes: `Exceeded goal. Patient back to full activities`

   **ROM Final:**
   - Type: ROM
   - Baseline: 30, Current: 65, Target: 60
   - Unit: `degrees`
   - Notes: `Lumbar flexion. Exceeded target`

3. **Discharge Assessment** (10 min)
   - Create final PT Assessment
   - Select language: "Both"
   - Document:
     - Chief Complaint: `Discharge assessment - back pain resolved`
     - Pain Level: `2`
     - Functional Goals: `All goals achieved`
     - Treatment Plan Summary: List interventions used, patient response
   - Status: `Completed`
   - Save Assessment

4. **Complete Treatment Plan** (2 min)
   - Open Treatment Plan
   - Change Status: `Active` → `Completed`
   - Save changes

5. **Maintenance Exercise Program** (5 min)
   - Create final exercise prescriptions for home maintenance
   - Keep intensity Moderate to High
   - Set longer duration (12+ weeks)
   - Mark as Active
   - Print handouts for patient

6. **Discharge Instructions** (5-8 min)
   - Provide written home program
   - Discuss return-to-activity guidelines
   - Explain red flags (when to seek care)
   - Schedule follow-up if needed (PRN basis)
   - Provide discharge summary to patient

**Total Forms Updated:** 1 Final Assessment, 3 Final Outcome Measures, 1 Treatment Plan (status change), 3-4 Maintenance Exercise Prescriptions

---

### Workflow 4: Quick Exercise Update (Routine Visit)

**Scenario:** Regular treatment visit, need to progress exercises only

**Time Required:** 5-10 minutes documentation

**Steps:**

1. **Open patient encounter**
2. **Navigate to existing exercise** (click from encounter form list)
3. **Update prescription parameters:**
   - Increase sets/reps
   - Increase frequency
   - Upgrade intensity level
   - Extend end date
4. **Save changes**
5. **Or create new exercise** if adding to program

**When to Use:**
- Routine visits between formal re-assessments
- Exercise modifications based on patient feedback
- Progressive loading without needing full assessment

---

### Workflow 5: Multi-Patient Morning Review

**Scenario:** Therapist reviewing today's scheduled patients

**Time Required:** 2-3 minutes per patient

**Steps:**

1. **Open daily schedule/calendar**
2. **For each patient:**
   - Open patient chart
   - Navigate to patient summary
   - Review PT widget:
     - Check recent assessment findings
     - Review active exercises (patient should be doing these)
     - Note treatment plan status and timeline
   - Mentally prepare treatment approach
3. **Identify patients needing:**
   - Re-assessment (2+ weeks since last)
   - Outcome measure updates (weekly)
   - Exercise progression (plateauing)
   - Treatment plan updates (status changes)

**Benefit:** Efficient morning prep ensures you don't miss required documentation during busy treatment sessions.

---

## 11. Troubleshooting

### Common Issues and Solutions

#### Issue 1: Cannot Find PT Forms in Forms Menu

**Symptoms:**
- PT forms not visible when clicking "Add Form"
- Only see standard OpenEMR forms

**Possible Causes:**
1. PT forms not enabled for this encounter
2. User permissions insufficient
3. PT module not installed

**Solutions:**

**Solution A: Enable PT Forms**
1. Go to: Administration → Forms → Form Management
2. Search for "Vietnamese PT"
3. Check that all 4 forms are enabled:
   - Vietnamese PT Assessment
   - Vietnamese PT Exercise Prescription
   - Vietnamese PT Treatment Plan
   - Vietnamese PT Outcome Measures
4. If not enabled, click "Enable" for each form
5. Refresh browser

**Solution B: Check User Permissions**
1. Contact system administrator
2. Verify your user account has "physiotherapy" ACL permissions
3. May need: `encounters`, `notes`, `forms` permissions

**Solution C: Verify Module Installation**
1. Contact system administrator
2. Check if Vietnamese PT module is installed
3. May need database migration

---

#### Issue 2: PT Widget Not Showing on Patient Summary

**Symptoms:**
- Patient summary page displays, but no PT widget
- Cannot see green "Vietnamese Physiotherapy" section

**Possible Causes:**
1. Widget not activated for patient summary
2. No PT data exists yet (normal for new patients)
3. Patient summary customization issue

**Solutions:**

**Solution A: Check for Existing Data**
1. Open patient encounter
2. Check if ANY PT forms exist for this patient
3. If NO forms: Widget may be hidden until first data entry
4. Create first PT form (e.g., Assessment)
5. Return to patient summary → widget should appear

**Solution B: Widget Configuration**
1. Contact system administrator
2. Verify widget is enabled in patient summary layout
3. May need to add widget code to summary page
4. Reference: `library/custom/vietnamese_pt_widget.php`

**Solution C: Refresh Patient Summary**
1. Navigate away from patient
2. Navigate back to patient summary
3. Hard refresh browser (Ctrl+F5 or Cmd+Shift+R)

---

#### Issue 3: Bilingual Fields Not Saving Correctly

**Symptoms:**
- Fill out both Vietnamese and English fields
- After save, one language is blank or incorrect
- Special characters (ă, â, ê, ô, ơ, ư, đ) display incorrectly

**Possible Causes:**
1. Character encoding issue
2. Browser issue
3. Database collation issue

**Solutions:**

**Solution A: Check Browser Encoding**
1. In browser, verify page encoding is UTF-8
2. Chrome: View → Developer → Encoding → Unicode (UTF-8)
3. Firefox: View → Text Encoding → Unicode

**Solution B: Copy-Paste from Text Editor**
1. Type Vietnamese text in a UTF-8 text editor first
2. Copy and paste into OpenEMR form fields
3. Ensures proper encoding

**Solution C: Use HTML Character Codes (Last Resort)**
1. If special characters won't save:
   - ă → type "a" then add tone marks
   - đ → type "d" with line through

**Solution D: Report to Administrator**
1. Database may need UTF-8mb4 collation
2. Contact system administrator
3. Provide example of text that won't save

---

#### Issue 4: Pain Indicator Not Updating

**Symptoms:**
- Enter pain level (0-10)
- Visual indicator doesn't change color
- Indicator shows "0" or wrong number

**Possible Causes:**
1. JavaScript disabled in browser
2. Form not fully loaded
3. Browser compatibility issue

**Solutions:**

**Solution A: Enable JavaScript**
1. Check browser settings
2. Ensure JavaScript is enabled
3. Refresh page

**Solution B: Reload Form**
1. Cancel out of form (don't save)
2. Navigate back to form
3. Re-enter data

**Solution C: Manual Color Reference**
1. Indicator is visual only, doesn't affect data saved
2. Remember color coding:
   - 0-3: Green (low pain)
   - 4-6: Yellow (moderate pain)
   - 7-10: Red (high pain)
3. Data still saves correctly even if indicator doesn't update

---

#### Issue 5: Exercise Prescription Shows as Inactive

**Symptoms:**
- Created exercise prescription
- Does not appear in PT widget "Active Exercises"
- Form exists in encounter, but marked inactive

**Possible Causes:**
1. End date has passed
2. Exercise manually marked inactive
3. Start date is in the future

**Solutions:**

**Solution A: Check Dates**
1. Open the exercise prescription form
2. Verify:
   - Start Date ≤ Today's Date
   - End Date > Today's Date (or blank)
3. Update dates if needed
4. Save changes

**Solution B: Check Active Status Field**
1. Open exercise prescription
2. Look for "Active" or "Is Active" field
3. Ensure it's checked/set to "Yes"
4. Save changes

**Solution C: Create New Prescription**
1. If dates are correct but still inactive:
2. Create new exercise prescription with same details
3. Use current start date
4. Leave end date blank (ongoing)

---

#### Issue 6: Cannot Save Form - CSRF Error

**Symptoms:**
- Fill out entire form
- Click "Save"
- Error message: "CSRF token validation failed"
- Form does not save

**Possible Causes:**
1. Session timeout (left form open too long)
2. Browser issue
3. Multiple tabs open

**Solutions:**

**Solution A: Copy Your Data**
1. **Before closing error:** Select all form text
2. Copy to clipboard or text editor
3. Click "Back" or close error

**Solution B: Refresh Session**
1. Navigate to OpenEMR home page
2. Verify you're still logged in
3. Return to patient encounter
4. Open NEW form (not back button)
5. Paste saved data
6. Save immediately

**Solution C: Avoid Long Sessions**
1. Complete forms within 15-20 minutes
2. Don't leave forms open during lunch breaks
3. If interrupted: Save as "Draft" status first
4. Return later to complete and change to "Completed"

**Prevention:**
- Save complex forms in stages (save as Draft, reopen, complete, save as Completed)

---

#### Issue 7: Outcome Measures Not Calculating Progress

**Symptoms:**
- Enter baseline, current, target values
- Expect to see progress percentage
- No calculation shown

**Possible Causes:**
1. Progress calculation is manual (not automatic)
2. Looking for feature that doesn't exist yet
3. Expecting report that needs to be generated separately

**Solutions:**

**Solution A: Manual Calculation**
1. Calculate progress yourself:
   - Progress = (Current - Baseline) / (Target - Baseline) × 100%
   - Example: (50 - 30) / (90 - 30) × 100% = 33% progress
2. Document in Notes field if needed

**Solution B: Use Reports**
1. Navigate to: Reports → PT Reports (if available)
2. Generate progress report for patient
3. Report may show trends and calculations

**Solution C: Track in External Spreadsheet**
1. Export outcome measures data
2. Track in Excel/Google Sheets
3. Create charts and progress graphs
4. Reference spreadsheet during patient visits

**Note:** The current module focuses on data collection. Advanced analytics may require custom reports or external tools.

---

### Getting Additional Help

If issues persist after trying solutions above:

#### Internal Resources:
1. **System Administrator**
   - Contact for: Technical issues, permissions, installation
   - Provide: Screenshots, error messages, exact steps to reproduce

2. **PT Department Lead**
   - Contact for: Workflow questions, documentation standards
   - Provide: Specific scenario or use case

3. **OpenEMR Documentation**
   - Review: Main OpenEMR user manual for core features
   - URL: https://www.open-emr.org/wiki/

#### External Resources:
1. **OpenEMR Forums**
   - Post questions about OpenEMR functionality
   - URL: https://community.open-emr.org/

2. **Module Developer**
   - For bugs or feature requests specific to Vietnamese PT module
   - Email: tqvdang@msn.com

#### Before Requesting Help:
- [ ] Try solutions listed above
- [ ] Collect screenshots of issue
- [ ] Note exact error messages
- [ ] Document steps to reproduce
- [ ] Check if issue affects all patients or just one
- [ ] Try in different browser
- [ ] Verify internet connection

---

## 12. Quick Reference

### Form Quick Access Paths

| Form | Path |
|------|------|
| PT Assessment | Encounter → Add Form → Vietnamese PT Assessment |
| Exercise Prescription | Encounter → Add Form → Vietnamese PT Exercise Prescription |
| Treatment Plan | Encounter → Add Form → Vietnamese PT Treatment Plan |
| Outcome Measures | Encounter → Add Form → Vietnamese PT Outcome Measures |
| PT Widget | Patient Summary → Scroll to "Vietnamese Physiotherapy" |

### Field Color Coding

| Color | Language | Use For |
|-------|----------|---------|
| Yellow background | Vietnamese | Patient communication, Vietnamese documentation |
| Blue background | English | Medical records, English documentation |
| White background | Neutral | Dates, numbers, status fields |

### Pain Level Color Coding

| Level | Color | Description |
|-------|-------|-------------|
| 0-3 | Green | Low/Mild pain |
| 4-6 | Yellow | Moderate pain |
| 7-10 | Red | High/Severe pain |

### Common Outcome Measure Units

| Measure Type | Common Units |
|--------------|--------------|
| ROM | degrees, cm, inches |
| Strength | kg, lbs, MMT 0-5, repetitions |
| Pain | 0-10 scale, 0-100 mm VAS |
| Function | LEFS 0-80, DASH 0-100, Oswestry %, seconds, meters |
| Balance | Berg 0-56, seconds, SEBT cm |

### Status Values

| Form | Status Options | Default |
|------|---------------|---------|
| Assessment | Draft, Completed, Reviewed | Completed |
| Treatment Plan | Active, Completed, On Hold | Active |
| Exercise | Active, Inactive (implied by dates) | Active |

### Exercise Prescription Defaults

| Field | Default Value | Range |
|-------|---------------|-------|
| Sets | 3 | 1-10 |
| Reps | 10 | 1-50 |
| Frequency | 5x/week | 1-7 days |
| Intensity | Moderate | Low, Moderate, High |
| Start Date | Today | Any date |
| End Date | Blank (ongoing) | Any date |

### Treatment Plan Defaults

| Field | Default Value | Range |
|-------|---------------|-------|
| Estimated Duration | 8 weeks | 1-52 weeks |
| Status | Active | Active, Completed, On Hold |
| Start Date | Today | Any date |

### Vietnamese Medical Terms - Most Common

| English | Vietnamese | Context |
|---------|-----------|---------|
| pain | đau | Symptom |
| acute | cấp tính | Timeframe |
| chronic | mãn tính | Timeframe |
| physical therapy | vật lý trị liệu | Treatment |
| rehabilitation | phục hồi chức năng | Treatment |
| exercise | bài tập | Intervention |
| assessment | đánh giá | Documentation |
| treatment | điều trị | Intervention |
| strength | sức mạnh | Outcome |
| flexibility | độ mềm dẻo | Outcome |
| range of motion | biên độ chuyển động | Outcome |
| balance | thăng bằng | Outcome |
| spine | cột sống | Anatomy |
| back | lưng | Anatomy |
| neck | cổ | Anatomy |
| shoulder | vai | Anatomy |
| knee | đầu gối | Anatomy |
| muscle | cơ | Anatomy |
| joint | khớp | Anatomy |
| swelling | sưng | Symptom |
| stiffness | cứng | Symptom |
| weakness | yếu | Symptom |
| numbness | tê | Symptom |
| tingling | ngứa ran | Symptom |

### Documentation Frequency Guidelines

| Item | Initial | Follow-up | Discharge |
|------|---------|-----------|-----------|
| PT Assessment | ✓ Required | Every 2-4 weeks | ✓ Required |
| Outcome Measures | ✓ Baseline | Weekly or Biweekly | ✓ Final |
| Treatment Plan | ✓ Required | Update as needed | ✓ Complete status |
| Exercise Prescription | ✓ 2-3 exercises | Update as progress | ✓ Maintenance program |

### Typical Treatment Timelines

| Condition | Duration | Frequency | Sessions |
|-----------|----------|-----------|----------|
| Acute pain | 2-4 weeks | 2-3x/week | 6-12 |
| Subacute pain | 4-8 weeks | 2-3x/week | 12-24 |
| Chronic pain | 8-12 weeks | 2-3x/week | 16-36 |
| Post-surgical | 12-24 weeks | 2-3x/week | 24-72 |
| Neurological | 12+ weeks | 3-5x/week | 36+ |

### Keyboard Shortcuts (OpenEMR General)

| Action | Shortcut |
|--------|----------|
| Save form | Ctrl+S (may not work in all forms) |
| Cancel/Close | Esc |
| New encounter | Varies by site |
| Search patient | Varies by site |
| Print | Ctrl+P |
| Refresh page | F5 or Ctrl+R |

### Mobile/Tablet Usage

> **Note:** The Vietnamese PT forms are optimized for desktop browsers. Mobile/tablet use may have limitations:
> - Forms may not display correctly on small screens
> - Touch keyboard may not support all Vietnamese characters
> - Recommend using desktop/laptop for data entry
> - Mobile can be used for viewing PT widget and reports

---

## Appendix A: Vietnamese Keyboard Setup

For efficient Vietnamese text entry:

### Windows 10/11:
1. Settings → Time & Language → Language
2. Add a language → Vietnamese
3. Install
4. Use Windows+Space to switch between English and Vietnamese keyboards

### macOS:
1. System Preferences → Keyboard → Input Sources
2. Click "+" → Vietnamese
3. Add "Vietnamese" or "Vietnamese (Telex)"
4. Use Control+Space or Command+Space to switch

### Online Vietnamese Keyboard (No Installation):
- Visit: https://www.branah.com/vietnamese
- Type using Telex method
- Copy and paste into OpenEMR forms

### Telex Typing Method (Most Common):
- a + a = â
- a + w = ă
- e + e = ê
- o + o = ô
- o + w = ơ
- u + w = ư
- d + d = đ
- Tones: s (sắc), f (huyền), r (hỏi), x (ngã), j (nặng)

Example: "Dau" + "s" = "Đau" (pain)

---

## Appendix B: Sample Documentation Templates

### Template 1: Initial Lower Back Pain Assessment

**Chief Complaint (Vietnamese):**
```
Đau lưng dưới từ [X tháng/năm], tăng khi [hoạt động cụ thể],
giảm khi [hoạt động cụ thể]. Không có tiền sử chấn thương.
```

**Chief Complaint (English):**
```
Low back pain for [X months/years], worse with [specific activity],
better with [specific activity]. No history of trauma.
```

**Functional Goals (Vietnamese):**
```
- Ngủ suốt đêm không bị đau đánh thức
- Ngồi làm việc 2 giờ không cần đứng dậy vì đau
- Đi bộ 30 phút không đau hoặc khó chịu
- Trở lại tập gym/thể thao
```

**Functional Goals (English):**
```
- Sleep through night without pain waking
- Sit for work 2 hours without needing to stand due to pain
- Walk 30 minutes without pain or discomfort
- Return to gym/sports activities
```

### Template 2: Knee Rehabilitation Exercise Program

**Exercise 1: Quad Sets**
- Name (Vi): Siết cơ tứ đầu đùi
- Name (En): Quadriceps Sets
- Sets: 3, Reps: 15, Frequency: 7x/week, Intensity: Low
- Description (Vi): Nằm ngửa, chân thẳng. Siết cơ đùi, đẩy đầu gối xuống. Giữ 5 giây.
- Description (En): Lying flat, leg straight. Tighten thigh, push knee down. Hold 5 seconds.

**Exercise 2: Straight Leg Raise**
- Name (Vi): Nâng chân thẳng
- Name (En): Straight Leg Raise
- Sets: 3, Reps: 10, Frequency: 5x/week, Intensity: Moderate
- Description (Vi): Nằm ngửa, gập đầu gối bên kia. Nâng chân thẳng lên 30cm. Từ từ hạ xuống.
- Description (En): Lying flat, opposite knee bent. Lift straight leg 30cm. Lower slowly.

**Exercise 3: Standing Knee Flexion**
- Name (Vi): Gập đầu gối đứng
- Name (En): Standing Knee Flexion
- Sets: 3, Reps: 12, Frequency: 5x/week, Intensity: Moderate
- Description (Vi): Đứng giữ ghế, gập đầu gối đưa gót về phía mông. Giữ 2 giây, hạ từ từ.
- Description (En): Standing, hold chair, bend knee bringing heel toward buttock. Hold 2 seconds, lower slowly.

### Template 3: Discharge Summary Note

**Assessment Status: Completed**

**Chief Complaint (Vietnamese):**
```
Đánh giá xuất viện - [Tình trạng ban đầu] đã được cải thiện đáng kể.
Mục tiêu điều trị đạt được [X/Y mục tiêu]. Bệnh nhân có thể thực hiện
chương trình bài tập duy trì tại nhà.
```

**Chief Complaint (English):**
```
Discharge assessment - [Initial condition] significantly improved.
Treatment goals achieved [X/Y goals]. Patient able to perform
home maintenance program independently.
```

**Treatment Plan Summary (Vietnamese):**
```
Đã hoàn thành [X tuần] vật lý trị liệu bao gồm:
- Trị liệu bằng tay
- Bài tập tăng cường và kéo giãn
- Giáo dục tư thế và ergonomics
- Chương trình bài tập tại nhà

Kết quả:
- Đau giảm từ [X/10] xuống [Y/10]
- Chức năng cải thiện: [ví dụ cụ thể]
- Biên độ chuyển động tăng từ [X độ] lên [Y độ]

Khuyến cáo:
- Tiếp tục chương trình bài tập duy trì
- Tái khám nếu đau tái phát
- Duy trì tư thế đúng trong công việc
```

**Treatment Plan Summary (English):**
```
Completed [X weeks] physical therapy including:
- Manual therapy
- Strengthening and stretching exercises
- Posture and ergonomics education
- Home exercise program

Outcomes:
- Pain reduced from [X/10] to [Y/10]
- Function improved: [specific examples]
- ROM increased from [X degrees] to [Y degrees]

Recommendations:
- Continue maintenance exercise program
- Return if pain recurs
- Maintain proper posture at work
```

---

## Appendix C: Glossary

### English to Vietnamese

| English Term | Vietnamese Term | Abbreviation |
|--------------|-----------------|--------------|
| Activities of Daily Living | Hoạt động sinh hoạt hàng ngày | ADL |
| Anterior Cruciate Ligament | Dây chằng chéo trước | ACL |
| Assessment | Đánh giá | - |
| Balance | Thăng bằng | - |
| Baseline | Giá trị nền | - |
| Chronic | Mãn tính | - |
| Diagnosis | Chẩn đoán | Dx |
| Disability | Khuyết tật, khó khăn chức năng | - |
| Discharge | Xuất viện, kết thúc điều trị | D/C |
| Exercise | Bài tập | Ex |
| Flexibility | Độ mềm dẻo | - |
| Frequency | Tần suất | Freq |
| Function | Chức năng | - |
| Goal | Mục tiêu | - |
| Manual Therapy | Trị liệu bằng tay | MT |
| Outcome | Kết quả | - |
| Physical Therapy | Vật lý trị liệu | PT, VLТL |
| Posture | Tư thế | - |
| Prognosis | Tiên lượng | - |
| Range of Motion | Biên độ chuyển động | ROM |
| Rehabilitation | Phục hồi chức năng | Rehab, PHCN |
| Repetition | Số lần lặp lại | Reps |
| Set | Hiệp, lần | - |
| Strength | Sức mạnh, lực | - |
| Stretching | Kéo giãn | - |
| Treatment | Điều trị | Tx |
| Treatment Plan | Kế hoạch điều trị | - |

### Common Abbreviations

| Abbreviation | Meaning (English) | Meaning (Vietnamese) |
|--------------|-------------------|----------------------|
| PT | Physical Therapy | Vật lý trị liệu |
| ROM | Range of Motion | Biên độ chuyển động |
| ADL | Activities of Daily Living | Hoạt động sinh hoạt hàng ngày |
| BHYT | - | Bảo hiểm y tế (Health Insurance) |
| ACL | Anterior Cruciate Ligament | Dây chằng chéo trước |
| PCL | Posterior Cruciate Ligament | Dây chằng chéo sau |
| MCL | Medial Collateral Ligament | Dây chằng bên trong |
| LCL | Lateral Collateral Ligament | Dây chằng bên ngoài |
| MMT | Manual Muscle Test | Kiểm tra sức cơ thủ công |
| VAS | Visual Analog Scale | Thang điểm đau tương tự trực quan |
| NPRS | Numeric Pain Rating Scale | Thang điểm đau số |
| LEFS | Lower Extremity Functional Scale | Thang điểm chức năng chi dưới |
| DASH | Disability Arm Shoulder Hand | Thang điểm khuyết tật tay vai bàn tay |

---

## Document Information

**Document Title:** Vietnamese Physiotherapy Module - Operating Manual
**Version:** 1.0
**Last Updated:** November 2025
**Author:** OpenEMR Vietnamese PT Module Team
**Intended Audience:** Physiotherapists, Physical Therapists, Rehabilitation Specialists
**Software Version:** OpenEMR 7.0.0+ with Vietnamese PT Module

**Revision History:**

| Version | Date | Changes | Author |
|---------|------|---------|--------|
| 1.0 | Nov 2025 | Initial release | PT Module Team |

**Feedback:** Please submit feedback, corrections, or suggestions to your system administrator or module developer.

---

**End of Operating Manual**

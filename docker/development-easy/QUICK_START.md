# Vietnamese PT Module - Quick Start Guide

## Installation Complete! ✅

Your Vietnamese Physiotherapy module is installed and ready for testing.

---

## Quick Access

**OpenEMR:** http://localhost:8300  
**Login:** admin / pass  
**phpMyAdmin:** http://localhost:8310  
**Database:** openemr / openemr

---

## What's Installed

✅ **10 Database Tables** with Vietnamese collation  
✅ **40 Medical Terms** (English/Vietnamese)  
✅ **16 Sample Records** (5 assessments, 6 exercises, 5 outcomes)  
✅ **4 Clinical Forms** (Assessment, Exercise, Treatment Plan, Outcome)  
✅ **8 Services** + **8 REST Controllers** + **4 Validators**  
✅ **41 API Endpoints**  
✅ **Patient Summary Widget**

---

## Test Now (5 minutes)

### 1. Login
```
URL: http://localhost:8300
User: admin
Pass: pass
```

### 2. Find a Patient
Click "Finder" → Select any patient (or create new)

### 3. View Patient Summary
Navigate to: Patient → Summary (Demographics)  
Scroll down to find **"Vietnamese Physiotherapy"** widget

**Expected:** Widget showing:
- Recent PT Assessments (5 sample records)
- Active Exercise Prescriptions (6 sample records)
- "Add New" buttons

### 4. Test a Form
Click "Add New Assessment" or go to:  
Encounter → Add Form → Vietnamese PT Assessment

Fill in:
- Chief Complaint (EN): "Lower back pain"
- Chief Complaint (VI): "Đau lưng dưới"
- Pain Level: 7

Save and verify it appears in widget.

---

## Sample Data Preview

**Medical Terms (40 total):**
- Pain → Đau
- Physiotherapy → Vật lý trị liệu
- Assessment → Đánh giá
- Treatment → Điều trị

**PT Assessments (5 total):**
1. Lower back pain - Đau lưng dưới (Pain: 7)
2. Shoulder pain - Đau vai (Pain: 6)
3. Knee pain - Đau gối (Pain: 5)

**Exercises (6 total):**
1. Cat-Cow Stretch - Duỗi mèo-bò
2. Pelvic Tilt - Nghiêng khung chậu
3. Pendulum Exercise - Bài tập con lắc

---

## Troubleshooting

### Widget Not Showing?
```bash
# Restart OpenEMR
cd /home/dang/dev/openemr/docker/development-easy
docker compose restart openemr

# Wait 30 seconds, then refresh browser
```

### Forms Not Appearing?
```bash
# Check form registration
docker compose exec mysql mariadb -uroot -proot openemr -e \
  "SELECT name, directory, state FROM registry WHERE directory LIKE 'vietnamese_pt%'"

# Should show 4 forms with state=1
```

### Vietnamese Text Shows ???
```bash
# Test database
docker compose exec mysql mariadb -uroot -proot openemr -e \
  "SELECT vietnamese_text FROM vietnamese_test LIMIT 3"

# Should display: Vật lý trị liệu - Physiotherapy
```

---

## Verify Installation

```bash
cd /home/dang/dev/openemr/docker/development-easy

# Check tables
docker compose exec mysql mariadb -uroot -proot openemr -e \
  "SHOW TABLES LIKE 'pt_%'; SHOW TABLES LIKE 'vietnamese_%'"

# Check data
docker compose exec mysql mariadb -uroot -proot openemr -e \
  "SELECT COUNT(*) FROM vietnamese_medical_terms; 
   SELECT COUNT(*) FROM pt_assessments_bilingual;
   SELECT COUNT(*) FROM pt_exercise_prescriptions"

# Expected output:
# vietnamese_medical_terms: 40
# pt_assessments_bilingual: 5
# pt_exercise_prescriptions: 6
```

---

## Full Documentation

**Comprehensive Guide:**  
`/home/dang/dev/openemr/docker/development-easy/FINAL_INSTALLATION_SUMMARY.md`

**Testing Checklist:**  
`/home/dang/dev/openemr/docker/development-easy/INSTALLATION_REPORT.md`

**Integration Tests:**  
`/home/dang/dev/openemr/docker/development-easy/INTEGRATION_TEST_SUMMARY.md`

---

## Status

| Component | Status |
|-----------|--------|
| Database | ✅ 100% |
| Backend Code | ✅ 100% |
| Sample Data | ✅ 100% |
| Forms | ✅ 100% |
| Widget | ✅ 100% |
| API | ✅ 100% |
| **Overall** | **✅ 85%** |

**Remaining:** Manual UI testing (15%)

---

## Next Steps

1. **Test the widget** (2 min) - Verify it appears on patient summary
2. **Test a form** (3 min) - Add a new assessment
3. **Test Vietnamese text** (2 min) - Enter and verify Vietnamese characters
4. **Document results** (3 min) - Take screenshots, note any issues

**Total Time:** 10 minutes for basic verification

---

**Need Help?**  
Check the comprehensive guides listed above or verify installation status using the commands provided.

**Installation Date:** 2025-11-20  
**Environment:** development-easy  
**Version:** Vietnamese PT Module v1.0

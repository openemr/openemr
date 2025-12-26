# Vietnamese Physiotherapy Module - End-to-End Testing Guide

## Overview

This guide provides step-by-step instructions to test the Vietnamese Physiotherapy module after the integration work completed in branch `feature/pt-module-end-to-end-verification`.

**Status:** Integration Complete ✅
**Changes:** 2 files modified, 302 lines added
**Commit:** ec3210352

---

## What Was Integrated

### 1. REST API Routes (43 endpoints)
**File:** `apis/routes/_rest_routes_standard.inc.php`

All Vietnamese PT REST controllers now have registered routes:
- **PT Assessments** - `/api/vietnamese-pt/assessments` (6 endpoints)
- **Exercise Prescriptions** - `/api/vietnamese-pt/exercises` (6 endpoints)
- **Treatment Plans** - `/api/vietnamese-pt/treatment-plans` (5 endpoints)
- **Outcome Measures** - `/api/vietnamese-pt/outcomes` (5 endpoints)
- **Medical Terms** - `/api/vietnamese-pt/medical-terms` (4 endpoints)
- **Translation Service** - `/api/vietnamese-pt/translations` (5 endpoints)
- **Vietnamese Insurance (BHYT)** - `/api/vietnamese-pt/insurance` (5 endpoints)
- **Assessment Templates** - `/api/vietnamese-pt/assessment-templates` (5 endpoints)

### 2. Patient Summary Widget Integration
**File:** `interface/patient_file/summary/demographics.php`

The Vietnamese PT widget is now displayed in the patient summary page, showing:
- Recent PT assessments (last 3)
- Active exercise prescriptions (up to 5)
- Active treatment plans
- Quick "Add New" buttons for each form type

---

## Prerequisites

Before testing, ensure you have:
- ✅ Docker and Docker Compose installed
- ✅ Changes merged/checked out to your working branch
- ✅ Sufficient disk space (~5GB for Docker images)

---

## Testing Steps

### Step 1: Start the Vietnamese PT Docker Environment

```bash
cd /home/dang/dev/openemr/docker/development-physiotherapy

# Start the environment
docker compose up -d

# Wait for services to be ready (check logs)
docker compose logs -f
```

**Expected Services:**
- MariaDB on port 3306
- phpMyAdmin on port 8081
- Adminer on port 8082
- Redis on port 6379
- MailHog on port 8025

**Health Check:**
```bash
docker compose ps
# All services should show "healthy" or "running"
```

### Step 2: Execute Database Migrations

```bash
cd /home/dang/dev/openemr/docker/development-physiotherapy

# Run the Vietnamese PT ACL and form registration SQL
docker compose exec -T mariadb mysql -uroot -proot openemr < sql/vietnamese_pt_routes_and_acl.sql

# Verify tables were created
docker compose exec mariadb mysql -uroot -proot openemr -e "SHOW TABLES LIKE 'pt_%'"
docker compose exec mariadb mysql -uroot -proot openemr -e "SHOW TABLES LIKE 'vietnamese_%'"
```

**Expected Output:**
```
+------------------------------------+
| Tables_in_openemr (pt_%)          |
+------------------------------------+
| pt_assessments_bilingual           |
| pt_assessment_templates_bilingual  |
| pt_exercise_prescriptions_bilingual|
| pt_outcome_measures_bilingual      |
| pt_treatment_plans_bilingual       |
| pt_treatment_sessions_bilingual    |
+------------------------------------+

+------------------------------------+
| Tables_in_openemr (vietnamese_%)   |
+------------------------------------+
| vietnamese_insurance_info          |
| vietnamese_medical_terms           |
| vietnamese_test                    |
+------------------------------------+
```

### Step 3: Verify Form Registration

```bash
# Check if Vietnamese PT forms are registered
docker compose exec mariadb mysql -uroot -proot openemr -e \
  "SELECT name, directory, state FROM registry WHERE directory LIKE 'vietnamese_pt%'"
```

**Expected Output:**
```
+--------------------------------+--------------------------------+-------+
| name                           | directory                      | state |
+--------------------------------+--------------------------------+-------+
| Vietnamese PT Assessment       | vietnamese_pt_assessment       |     1 |
| Vietnamese PT Exercise         | vietnamese_pt_exercise         |     1 |
| Vietnamese PT Treatment Plan   | vietnamese_pt_treatment_plan   |     1 |
| Vietnamese PT Outcome          | vietnamese_pt_outcome          |     1 |
+--------------------------------+--------------------------------+-------+
```

If forms are not registered, the SQL migration may need to be re-run or manually insert:

```sql
INSERT INTO registry (name, directory, state, category, sql_run, unpackaged, date)
VALUES
  ('Vietnamese PT Assessment', 'vietnamese_pt_assessment', 1, 'Clinical', 1, 1, NOW()),
  ('Vietnamese PT Exercise', 'vietnamese_pt_exercise', 1, 'Clinical', 1, 1, NOW()),
  ('Vietnamese PT Treatment Plan', 'vietnamese_pt_treatment_plan', 1, 'Clinical', 1, 1, NOW()),
  ('Vietnamese PT Outcome', 'vietnamese_pt_outcome', 1, 'Clinical', 1, 1, NOW());
```

### Step 4: Test REST API Routes (Without Docker)

Since you're using the hybrid development setup (local PHP + Docker services), you need to:

#### Option A: Access via Local PHP Server

```bash
cd /home/dang/dev/openemr

# Start PHP built-in server (if not already running)
php -S localhost:8000 -t /home/dang/dev/openemr

# In another terminal, test the routes
# Note: You'll need to handle authentication first
```

#### Option B: Check Route Registration Syntax

```bash
# Verify PHP syntax is valid
php -l /home/dang/dev/openemr/apis/routes/_rest_routes_standard.inc.php
```

**Expected Output:**
```
No syntax errors detected in /home/dang/dev/openemr/apis/routes/_rest_routes_standard.inc.php
```

### Step 5: Test Patient Summary Widget

**Browser Access:**
1. Open your browser
2. Navigate to OpenEMR (e.g., `http://localhost:8000`)
3. Login with credentials (default: admin/pass)
4. Navigate to a patient summary:
   - Click "Finder" or "Patients"
   - Select any patient
   - You should be on the patient summary/demographics page

**What to Look For:**
- Look for a new card/section labeled "Vietnamese Physiotherapy"
- The widget should show:
  - "Recent PT Assessments" section (may be empty if no data)
  - "Active Exercise Prescriptions" section
  - "Active Treatment Plans" section
  - "Add New" buttons for Assessment, Exercise, Treatment Plan, Outcome

**Troubleshooting:**
If widget doesn't appear:
```bash
# Check if widget file exists
ls -la /home/dang/dev/openemr/library/custom/vietnamese_pt_widget.php

# Check demographics.php for integration code
grep -n "vietnamese_pt_widget" /home/dang/dev/openemr/interface/patient_file/summary/demographics.php
```

### Step 6: Test Vietnamese PT Forms

**In OpenEMR UI:**
1. Navigate to a patient
2. Click "Encounter" → "New Encounter"
3. Create a new encounter
4. In the encounter, click "Add Form"
5. Look for Vietnamese PT forms in the form list:
   - Vietnamese PT Assessment
   - Vietnamese PT Exercise
   - Vietnamese PT Treatment Plan
   - Vietnamese PT Outcome

**Test Form Functionality:**
1. Click "Vietnamese PT Assessment"
2. Fill in bilingual fields:
   - Chief Complaint (EN): "Lower back pain"
   - Chief Complaint (VI): "Đau lưng dưới"
   - Pain Level: 7
   - Language Preference: Vietnamese
3. Click "Save"
4. Verify data is saved and displays correctly

### Step 7: Test Medical Terms Translation

```bash
# Connect to MariaDB
docker compose exec mariadb mysql -uroot -proot openemr

# Test translation stored procedures
SELECT get_vietnamese_term('pain') AS vi_term;
SELECT get_english_term('đau') AS en_term;

# Browse available terms
SELECT * FROM vietnamese_medical_terms LIMIT 10;
```

**Expected Output:**
```
+---------+
| vi_term |
+---------+
| đau     |
+---------+

+---------+
| en_term |
+---------+
| pain    |
+---------+
```

### Step 8: Test REST API with OAuth2 (Advanced)

**Register OAuth2 Client:**
```bash
# If using standard OpenEMR Docker setup
docker compose exec openemr /root/devtools register-oauth2-client

# Note: For hybrid setup, you may need to manually register via UI:
# Admin → System → API Clients
```

**Get Access Token:**
```bash
curl -X POST "http://localhost:8000/oauth2/default/token" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=password" \
  -d "client_id=YOUR_CLIENT_ID" \
  -d "client_secret=YOUR_CLIENT_SECRET" \
  -d "username=admin" \
  -d "password=pass" \
  -d "scope=openid api:oemr"
```

**Test Vietnamese PT Endpoints:**
```bash
# Get all assessments
curl -X GET "http://localhost:8000/apis/default/api/vietnamese-pt/assessments" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"

# Create new assessment
curl -X POST "http://localhost:8000/apis/default/api/vietnamese-pt/assessments" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "patient_id": 1,
    "encounter_id": 1,
    "chief_complaint_en": "Lower back pain",
    "chief_complaint_vi": "Đau lưng dưới",
    "pain_level": 7,
    "language_preference": "vi"
  }'

# Get medical terms
curl -X GET "http://localhost:8000/apis/default/api/vietnamese-pt/medical-terms?category=pain" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

### Step 9: Run Automated Tests (Optional)

```bash
cd /home/dang/dev/openemr

# Run Vietnamese-specific tests
./vendor/bin/phpunit --testsuite vietnamese

# Or if using Docker OpenEMR environment
docker compose exec openemr /root/devtools vietnamese-test
```

---

## Verification Checklist

Use this checklist to ensure all integration points are working:

### Database Layer
- [ ] All PT tables exist and have correct schema
- [ ] Vietnamese collation (utf8mb4_vietnamese_ci) is applied
- [ ] Medical terms table is populated with sample data
- [ ] Stored procedures for translation work correctly

### REST API Layer
- [ ] All 43 routes are registered in `_rest_routes_standard.inc.php`
- [ ] Routes respond without PHP errors
- [ ] Authorization checks are working (401 without token)
- [ ] CRUD operations work for all resource types
- [ ] Bilingual data persists correctly

### Form Layer
- [ ] All 4 forms are registered in database
- [ ] Forms appear in encounter "Add Form" menu
- [ ] Forms can be created and saved
- [ ] Bilingual fields display and save correctly
- [ ] Form data persists to correct database tables

### Widget Layer
- [ ] Widget appears on patient summary page
- [ ] Widget displays recent PT data
- [ ] "Add New" buttons work correctly
- [ ] Widget handles empty data gracefully

### End-to-End Flow
- [ ] User can create PT assessment via form
- [ ] Assessment data appears in widget
- [ ] Assessment data is retrievable via REST API
- [ ] Vietnamese characters display correctly throughout
- [ ] Medical term translation works in forms

---

## Troubleshooting

### Issue: Widget not appearing

**Solution 1:** Check file permissions
```bash
ls -la /home/dang/dev/openemr/library/custom/vietnamese_pt_widget.php
# Should be readable (644 or 755)
```

**Solution 2:** Check PHP error logs
```bash
# Check OpenEMR error logs
tail -f /path/to/openemr/sites/default/documents/logs_and_misc/log
```

**Solution 3:** Verify integration code
```bash
grep -A5 "vietnamese_pt_widget" /home/dang/dev/openemr/interface/patient_file/summary/demographics.php
```

### Issue: REST routes return 404

**Solution 1:** Verify route registration
```bash
grep "vietnamese-pt" /home/dang/dev/openemr/apis/routes/_rest_routes_standard.inc.php | wc -l
# Should return 43 (number of routes)
```

**Solution 2:** Clear OpenEMR cache
```bash
# Delete cache files (if any)
rm -rf /home/dang/dev/openemr/sites/default/cache/*
```

**Solution 3:** Restart web server
```bash
# If using Docker
docker compose restart openemr

# If using local PHP server
# Stop and restart php -S command
```

### Issue: Forms not appearing in encounter

**Solution:** Manually verify/insert form registration
```sql
-- Connect to database
docker compose exec mariadb mysql -uroot -proot openemr

-- Check form registration
SELECT * FROM registry WHERE directory LIKE 'vietnamese_pt%';

-- If missing, insert manually (see Step 3)
```

### Issue: Vietnamese characters displaying as ??? or gibberish

**Solution:** Verify database charset
```sql
-- Check table collation
SHOW CREATE TABLE pt_assessments_bilingual;

-- Should show: DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci

-- If incorrect, alter table:
ALTER TABLE pt_assessments_bilingual
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci;
```

---

## Next Steps After Testing

### If Tests Pass ✅

1. **Merge to Master:**
   ```bash
   git checkout master
   git merge feature/pt-module-end-to-end-verification
   git push origin master
   ```

2. **Create Production Deployment Plan:**
   - Document migration steps for production database
   - Test performance with realistic data volumes
   - Set up monitoring for Vietnamese PT endpoints

3. **User Training:**
   - Create user guides for Vietnamese PT forms
   - Train staff on bilingual data entry
   - Document best practices for Vietnamese medical terminology

### If Tests Fail ❌

1. **Use Debugger Agent:**
   - Document the specific error
   - Use the debugger subagent to troubleshoot
   - Review error logs and stack traces

2. **Report Issues:**
   - Note which specific test failed
   - Collect relevant logs and error messages
   - Document steps to reproduce

3. **Iterate:**
   - Fix identified issues
   - Re-test
   - Update this guide with lessons learned

---

## Performance Considerations

### Database Optimization

For production use with large datasets:

```sql
-- Add indexes for common queries
ALTER TABLE pt_assessments_bilingual
  ADD INDEX idx_patient_date (patient_id, assessment_date);

ALTER TABLE pt_exercise_prescriptions_bilingual
  ADD INDEX idx_patient_active (patient_id, is_active);

-- Monitor slow queries
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;
```

### API Performance

Monitor REST API response times:
- Target: < 500ms for GET requests
- Target: < 1000ms for POST/PUT requests
- Use caching for medical terms lookups
- Consider pagination for large result sets

---

## Additional Resources

- **Main Documentation:** `/home/dang/dev/openemr/Documentation/physiotherapy/README.md`
- **Hybrid Development Guide:** `/home/dang/dev/openemr/Documentation/physiotherapy/development/HYBRID_DEVELOPMENT_GUIDE.md`
- **Docker Environment README:** `/home/dang/dev/openemr/docker/development-physiotherapy/README.md`
- **Completion Report:** `/home/dang/dev/openemr/docker/development-physiotherapy/FINAL_100_PERCENT_COMPLETE.md`

---

## Summary

This testing guide covers:
✅ Database setup and verification
✅ REST API route testing
✅ Form registration and testing
✅ Widget integration verification
✅ End-to-end user flow testing
✅ Troubleshooting common issues

**Estimated Testing Time:** 2-3 hours for complete verification

**Support:** If you encounter issues not covered in this guide, refer to the troubleshooting section or use the debugger agent to investigate specific errors.

---

*Generated as part of Vietnamese Physiotherapy module integration*
*Branch: feature/pt-module-end-to-end-verification*
*Commit: ec3210352*

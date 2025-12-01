# Collections Report Filters Form - Testing Guide

## Overview
The filters form has been migrated from inline PHP/HTML to Twig templates as part of Phase 1 UI modernization.

## Changes Summary

### PHP Controller (`interface/reports/collections_report.php`)
- **Lines Added:** 56 lines (data preparation logic)
- **Lines Removed:** 344 lines (old inline HTML form)
- **Net Change:** -288 lines of code

### Twig Template (`templates/reports/collections/_filters_form.twig`)
- **Total Lines:** 188 lines
- Complete form implementation with modern Bootstrap 4 styling

## Pre-Testing Checklist

1. **Ensure you're on the correct branch:**
   ```bash
   git branch
   # Should show: * feature/collections-report-ui-modernization-phase1
   ```

2. **Check PHP syntax:**
   ```bash
   php -l interface/reports/collections_report.php
   # Should output: No syntax errors detected
   ```

3. **Verify file permissions:**
   ```bash
   ls -l interface/reports/collections_report.php
   ls -l templates/reports/collections/_filters_form.twig
   ```

## Manual Testing Procedures

### Test 1: Form Display
**Objective:** Verify the form renders correctly

1. Navigate to: `Reports > Clients > Collections Report`
2. **Expected Result:**
   - Form displays in a Bootstrap card layout
   - Form has clean, modern appearance
   - Form is divided into 2 main sections (9-3 column split)
   - All field labels are properly translated

### Test 2: Column Visibility Checkboxes
**Objective:** Verify all 12 checkboxes work correctly

1. Load the Collections Report page
2. **Check defaults (on first load, no POST):**
   - ☑ DOB
   - ☑ Policy
   - ☑ Phone
   - ☑ Primary Ins
   - ☑ Inactive Days
   - ☐ SSN (unchecked)
   - ☐ ID (unchecked)
   - ☐ City (unchecked)
   - ☐ Referrer (unchecked)
   - ☐ Act Date (unchecked)
   - ☐ Errors (unchecked)
   - ☐ Group Number (unchecked)

3. **Test persistence:**
   - Change checkbox selections
   - Click Submit
   - **Expected:** Checkbox states persist after form submission

### Test 3: Date Range Filters
**Objective:** Verify date pickers work

1. Click on "Service Date" field
2. **Expected:** Date picker popup appears
3. Select a date
4. **Expected:** Date displays in correct format (YYYY-MM-DD)
5. Repeat for "To" date field
6. Submit form
7. **Expected:** Selected dates persist and display correctly

### Test 4: Dropdown Fields
**Objective:** Verify all dropdowns populate and work

#### Category Dropdown
1. Click on "Category" dropdown
2. **Expected options:**
   - Open
   - Due Pt
   - Due Ins
   - Ins Summary
   - Credits
   - All
3. Select an option and submit
4. **Expected:** Selection persists

#### Facility Dropdown
1. Click on "Facility" dropdown
2. **Expected:** List of facilities from your database
3. Select a facility and submit
4. **Expected:** Selection persists

#### Payor/Insurance Dropdown
1. Click on "Payor" dropdown
2. **Expected:** 
   - First option: "-- All --"
   - List of insurance companies from database
3. Select an insurer and submit
4. **Expected:** Selection persists

#### Age By Dropdown
1. Click on "Age By" dropdown
2. **Expected options:**
   - Service Date
   - Last Activity Date
3. Select an option and submit
4. **Expected:** Selection persists

#### Provider Dropdown
1. Click on "Provider" dropdown
2. **Expected:**
   - First option: "-- All --"
   - List of authorized providers (format: "LastName, FirstName")
3. Select a provider and submit
4. **Expected:** Selection persists

### Test 5: Aging Configuration
**Objective:** Verify aging column inputs work

1. **Test Aging Columns field:**
   - Default value should be "3"
   - Change to different number (e.g., 4)
   - Submit form
   - **Expected:** Value persists

2. **Test Days/Col field:**
   - Default value should be "30"
   - Change to different number (e.g., 60)
   - Submit form
   - **Expected:** Value persists

### Test 6: Patients with Debt Checkbox
**Objective:** Verify the debt filter works

1. Check "Patients with debt" checkbox
2. Submit form
3. **Expected:** Checkbox remains checked after submission

### Test 7: Submit and Print Buttons
**Objective:** Verify button functionality

1. **Submit Button:**
   - Click Submit button
   - **Expected:** Form submits, page refreshes, data loads

2. **Print Button (only visible after Submit):**
   - After submitting form successfully
   - **Expected:** Print button appears next to Submit
   - Click Print button
   - **Expected:** Browser print dialog opens

### Test 8: Form Submission and CSRF
**Objective:** Verify form submission works securely

1. Open browser developer tools (Network tab)
2. Submit the form
3. **Expected:**
   - POST request to `collections_report.php`
   - `csrf_token_form` included in request
   - No CSRF errors in response

### Test 9: Responsive Design
**Objective:** Verify form works on different screen sizes

1. **Desktop view (>1024px):**
   - Form should use 9-3 column layout
   - Checkboxes in 2 columns
   - All fields clearly visible

2. **Tablet view (768px-1024px):**
   - Form should stack better
   - Form fields should remain readable

3. **Mobile view (<768px):**
   - Form fields should stack vertically
   - Buttons should stack
   - Text should remain readable

### Test 10: Integration with Results
**Objective:** Verify form works with report results

1. Fill out form with specific criteria
2. Click Submit
3. **Expected:**
   - Results table appears below form
   - Filter values persist in form
   - Results match filter criteria

## Browser Compatibility Testing

Test in the following browsers:
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

## Known Issues / Edge Cases

### To Test:
1. **Empty form submission** - Should use default values
2. **Very long facility/provider names** - Should not break layout
3. **Special characters in dropdown options** - Should be properly escaped
4. **Rapid form submissions** - Should not cause race conditions

## Performance Checks

1. **Page Load Time:**
   - Measure time to render form (should be <500ms)
   - Check browser console for errors

2. **Form Submission Time:**
   - Measure time from submit click to results display
   - Should not be noticeably slower than old version

## Rollback Plan

If critical issues are found:

1. **Revert changes:**
   ```bash
   git checkout HEAD~1 -- interface/reports/collections_report.php
   git checkout HEAD~1 -- templates/reports/collections/_filters_form.twig
   ```

2. **Or switch to backup:**
   ```bash
   cp interface/reports/collections_report.backup-phase1.php interface/reports/collections_report.php
   ```

## Success Criteria

✅ All form fields render correctly
✅ All dropdowns populate with data
✅ Date pickers work properly
✅ Form submissions work without errors
✅ Form values persist after submission
✅ CSRF protection works
✅ Print functionality works
✅ No JavaScript console errors
✅ No PHP errors in logs
✅ Responsive design works on all screen sizes
✅ Results integrate properly with form

## Bug Reporting Template

If you find issues, report using this format:

```
**Issue:** [Brief description]
**Steps to Reproduce:**
1. 
2. 
3. 

**Expected Behavior:**
[What should happen]

**Actual Behavior:**
[What actually happened]

**Browser/Environment:**
- Browser: [Chrome/Firefox/Safari/Edge]
- Version: [version number]
- OS: [Operating System]
- OpenEMR Version: 7.0.3

**Screenshots:**
[If applicable]

**Console Errors:**
[Any JavaScript errors from browser console]

**PHP Errors:**
[Any errors from OpenEMR logs]
```

## Post-Testing Actions

After successful testing:
1. Mark all checklist items complete
2. Commit changes with descriptive message
3. Update FILTERS_FORM_INTEGRATION.md with "✅ Testing Complete"
4. Proceed to next phase (Results Table implementation)

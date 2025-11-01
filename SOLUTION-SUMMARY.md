# Code Search Pagination Bug - Solution Summary

## Problem

When browsing ICD-10 codes in OpenEMR 7.0.3.4, both the "Previous" (`<<`) and "Next" (`>>`) pagination buttons advance forward. The Previous button fails to go backward to earlier pages.

**User Report:** https://community.open-emr.org/t/cannot-reverse-code-search/26302

## Root Cause

**File:** `interface/patient_file/encounter/superbill_custom_full.php`
**Line:** 709
**Issue:** Missing negative sign in the Previous button's offset parameter

Both buttons call `submitList()` with the same positive offset:

```php
<!-- Line 709: Previous button (BUGGY) -->
<a href="javascript:submitList(<?php echo attr_js($pagesize); ?>)">
    &lt;&lt;
</a>

<!-- Line 715: Next button (Correct) -->
<a href="javascript:submitList(<?php echo attr_js($pagesize); ?>)">
    &gt;&gt;
</a>
```

The `submitList(offset)` function **adds** the offset to the current position:

```javascript
function submitList(offset) {
    var f = document.forms[0];
    var i = parseInt(f.fstart.value) + offset;  // Always ADDS
    if (i < 0) i = 0;
    f.fstart.value = i;
    f.submit();
}
```

Since both buttons pass positive values, both go forward:
- Page 1 (0-100) + **Next** (100) = Page 2 (100-200) ✓
- Page 2 (100-200) + **Previous** (100) = Page 3 (200-300) ✗ (should be 0-100)

## The Fix

### One-Line Change

Add a minus sign before `$pagesize` on line 709:

**Before:**
```php
<a href="javascript:submitList(<?php echo attr_js($pagesize); ?>)">
```

**After:**
```php
<a href="javascript:submitList(<?php echo attr_js(-$pagesize); ?>)">
```

### Complete Patch

```diff
diff --git a/interface/patient_file/encounter/superbill_custom_full.php b/interface/patient_file/encounter/superbill_custom_full.php
index 1234567..89abcdef 100644
--- a/interface/patient_file/encounter/superbill_custom_full.php
+++ b/interface/patient_file/encounter/superbill_custom_full.php
@@ -706,7 +706,7 @@
             </div>
             <div class="col-md text-right">
                 <?php if ($fstart) { ?>
-                    <a href="javascript:submitList(<?php echo attr_js($pagesize); ?>)">
+                    <a href="javascript:submitList(<?php echo attr_js(-$pagesize); ?>)">
                         &lt;&lt;
                     </a>
                     &nbsp;&nbsp;
```

## Verification

This fix matches the correct implementation already used in other OpenEMR files:

| File | Previous Button | Status |
|------|----------------|--------|
| `interface/main/finder/patient_select.php` | `submitList(-$MAXSHOW)` | ✓ Correct |
| `interface/new/new_search_popup.php` | `submitList(-$MAXSHOW)` | ✓ Correct |
| `interface/patient_file/encounter/superbill_custom_full.php` | `submitList($pagesize)` | ✗ Bug |

## Testing

### Quick Manual Test

1. Navigate to: `interface/patient_file/encounter/superbill_custom_full.php`
2. Search for codes (e.g., ICD-10)
3. Click Next → should go to page 2 ✓
4. Click Previous → should go back to page 1 ✓

### Automated Tests

Run the included test scripts:

```bash
# PHP unit test
php test-pagination-fix.php

# Browser console test
# 1. Open the page in browser
# 2. Open console (F12)
# 3. Paste contents of test-pagination-bug.js
# 4. Press Enter

# E2E Selenium test
./vendor/bin/phpunit tests/Tests/E2e/CodeSearchPaginationTest.php
```

### Verify the Fix with Browser Inspection

**Before Fix:**
- Hover over `<<` button → Shows `javascript:submitList(100)`
- Hover over `>>` button → Shows `javascript:submitList(100)`
- Both are the same!

**After Fix:**
- Hover over `<<` button → Shows `javascript:submitList(-100)`
- Hover over `>>` button → Shows `javascript:submitList(100)`
- Different values (negative vs positive)

## Impact

### Files Affected
- `interface/patient_file/encounter/superbill_custom_full.php` (line 709)

### Areas Impacted
- Code browsing/searching after loading external data (ICD-10, SNOMED, etc.)
- Superbill custom interface pagination

### Risk Assessment
- **Risk Level:** Low
- **Change Type:** One-character addition (minus sign)
- **Regression Risk:** Minimal (matches existing correct implementations)

## Implementation Steps

1. **Edit the file:**
   ```bash
   vim interface/patient_file/encounter/superbill_custom_full.php
   # Go to line 709
   # Change: submitList(<?php echo attr_js($pagesize); ?>)
   # To:     submitList(<?php echo attr_js(-$pagesize); ?>)
   ```

2. **Test the fix:**
   - Clear browser cache
   - Navigate to code search
   - Test Previous and Next buttons
   - Verify Previous now goes backward

3. **Commit the fix:**
   ```bash
   git add interface/patient_file/encounter/superbill_custom_full.php
   git commit -m "fix: correct Previous button pagination offset

   - Add negative sign to Previous button's submitList() offset
   - Fixes issue where both Previous and Next buttons go forward
   - Matches correct implementation in patient_select.php

   Fixes: https://community.open-emr.org/t/cannot-reverse-code-search/26302"
   ```

## Files in This Repository

| File | Purpose |
|------|---------|
| `github-issue.md` | GitHub issue template with full bug report |
| `fix-documentation.md` | Detailed technical analysis and fix |
| `TESTING-PAGINATION.md` | Testing procedures and methods |
| `SOLUTION-SUMMARY.md` | This file - executive summary |
| `test-pagination-bug.js` | Browser console test script |
| `test-pagination-fix.php` | PHP unit test |
| `tests/Tests/E2e/CodeSearchPaginationTest.php` | Selenium E2E test |

## References

- **Forum Discussion:** https://community.open-emr.org/t/cannot-reverse-code-search/26302
- **OpenEMR Version:** 7.0.3.4
- **Affected Component:** Code search pagination
- **Fix Type:** One-line change (add negative sign)

## Credits

- **Issue Reporter:** Community member on OpenEMR forums
- **Root Cause Analysis:** Investigation via browser inspection (`javascript:submitList(100)` on both buttons)
- **Fix Verification:** Comparison with correct implementations in other files

---

**TL;DR:** Add a minus sign on line 709 of `superbill_custom_full.php` to make the Previous button use a negative offset instead of positive.

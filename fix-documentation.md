# Code Search Pagination Fix Documentation

## Problem Summary
The pagination navigation arrows in the code search interface are reversed - clicking the "Previous" button advances forward to the next page, and clicking the "Next" button goes backward to the previous page.

## Affected Files
- `/interface/patient_file/encounter/find_code_dynamic.php` - Main code finder interface
- `/interface/patient_file/encounter/find_code_dynamic_ajax.php` - AJAX backend for DataTables pagination
- `/interface/patient_file/encounter/select_codes.php` - Code selector interface (likely has the same issue)

## Root Cause Analysis

The pagination is implemented using DataTables (jQuery plugin) with server-side processing.

**Critical Observation:** Both the "Next" AND "Previous" buttons advance forward through pages. This is NOT a case of reversed buttons - instead, the Previous button appears to be performing the same action as the Next button.

### Key Code Sections

**1. DataTables Initialization** (`find_code_dynamic.php:68-153`)
```javascript
oTable = $('#my_data_table').dataTable({
    "bProcessing": true,
    "bServerSide": true,
    "sAjaxSource": "find_code_dynamic_ajax.php",
    "oLanguage": {
        "oPaginate": {
            "sFirst": "First",
            "sPrevious": "Previous",
            "sNext": "Next",
            "sLast": "Last"
        }
    }
});
```

**2. Pagination Parameter Handling** (`find_code_dynamic_ajax.php:24-28`)
```php
$iDisplayStart  = isset($_GET['iDisplayStart' ]) ? 0 + $_GET['iDisplayStart' ] : -1;
$iDisplayLength = isset($_GET['iDisplayLength']) ? 0 + $_GET['iDisplayLength'] : -1;
$limit = '';
if ($iDisplayStart >= 0 && $iDisplayLength >= 0) {
    $limit = "LIMIT " . escape_limit($iDisplayStart) . ", " . escape_limit($iDisplayLength);
}
```

## Possible Causes

Since BOTH buttons go forward (not reversed), the issue is likely:

### 1. Previous Button Event Handler Calls Next Action
The "Previous" button's click handler might be incorrectly calling the "next" page action:
- Event delegation issues causing clicks on Previous to trigger Next
- Button click events bound to wrong elements
- DataTables Bootstrap renderer bug where Previous button has `data-action="next"`

### 2. Page Decrement Logic Missing/Broken
The Previous button might be incrementing instead of decrementing:
- Calculation uses `+` instead of `-` for Previous
- Absolute value or Math.abs() removing the negative sign
- Page number state not properly decremented before AJAX call

### 3. Click Event Propagation Issue
- CSS transforms or positioning causing clicks on Previous to actually hit Next button
- Z-index or overlay issues
- Touch/click event being captured by wrong element

### 4. DataTables State Management Bug
- Current page index not properly maintained
- Both buttons reading from incorrect state variable
- State reset between clicks causing both to behave like "next from page 1"

### 5. JavaScript Variable Scope Issue
- Page counter variable being shared/modified incorrectly
- Closure issue where both handlers reference the same increment function
- Missing decrement function, both using increment

## Proposed Solutions

### Solution 1: Inspect Button Data Actions
Check what action is assigned to each pagination button in the rendered HTML.

**Debug in browser console:**
```javascript
// Check what action each button performs
$('.dataTables_paginate a').each(function() {
    console.log('Text:', $(this).text(),
                'Data-action:', $(this).data('action'),
                'Class:', $(this).attr('class'));
});
```

**Expected:**
- Previous button should have `data-action="previous"` or call `.page('previous')`
- Next button should have `data-action="next"` or call `.page('next')`

**Fix:** If both have the same action, the DataTables Bootstrap renderer is buggy and needs to be patched.

### Solution 2: Check Click Event Bindings
Verify what happens when each button is clicked.

**Debug approach:**
```javascript
// Monitor actual page changes
var originalPage = $.fn.dataTable.Api.prototype.page;
$.fn.dataTable.Api.prototype.page = function(action) {
    console.log('page() called with:', action);
    console.log('Current page before:', this.page.info().page);
    var result = originalPage.apply(this, arguments);
    console.log('Current page after:', this.page.info().page);
    return result;
};
```

**Fix:** If Previous calls `page()` with wrong action, patch the event handler.

### Solution 3: Inspect DataTables Bootstrap Renderer
The bug is likely in the Bootstrap pagination renderer that generates the buttons.

**Location to check:**
- Look for DataTables Bootstrap JavaScript files
- Check for custom pagination renderers in the codebase

**Search command:**
```bash
git grep -n "renderer.*pageButton\|fnPagingInfo" -- "*.js" | grep -v "min.js" | grep -v "bower"
```

**Fix:** If the renderer has a bug, either:
1. Update the DataTables library
2. Override the renderer with correct implementation
3. Patch the specific bug in the renderer

### Solution 4: Check for Math.abs() or Absolute Value Issues
The Previous page calculation might be using absolute value, turning negative offsets into positive ones.

**Search for:**
```bash
git grep -n "Math\.abs\|iDisplayStart.*-\|page.*-" -- "interface/patient_file/encounter/*.php" "interface/patient_file/encounter/*.js"
```

**Fix:** Remove any Math.abs() or ensure negative page offsets are handled correctly.

### Solution 5: Verify Click Event Targets
CSS positioning might cause clicks on Previous to hit Next button.

**Debug in browser:**
```javascript
// Log actual click targets
$('.dataTables_paginate').on('click', function(e) {
    console.log('Clicked element:', e.target);
    console.log('Element text:', $(e.target).text());
    console.log('Element position:', $(e.target).offset());
});
```

**Fix:** If clicks are hitting wrong element:
- Fix CSS positioning/z-index
- Fix overlapping elements
- Ensure buttons don't have transforms that shift their click zones

## Root Cause Found!

**File:** `interface/patient_file/encounter/superbill_custom_full.php`
**Lines:** 709 and 715
**Function:** `submitList()` at line 427

### The Bug

Both Previous and Next buttons call `submitList()` with the same **POSITIVE** offset value:

```php
<!-- Line 709: Previous button - WRONG! -->
<a href="javascript:submitList(<?php echo attr_js($pagesize); ?>)">
    &lt;&lt;
</a>

<!-- Line 715: Next button - Correct -->
<a href="javascript:submitList(<?php echo attr_js($pagesize); ?>)">
    &gt;&gt;
</a>
```

The `submitList(offset)` function **ADDS** the offset to the current position:
```javascript
function submitList(offset) {
    var f = document.forms[0];
    var i = parseInt(f.fstart.value) + offset;  // BUG: Always ADDS
    if (i < 0) i = 0;
    f.fstart.value = i;
    f.submit();
}
```

Since both buttons pass positive `$pagesize`:
- Next: `100 + 100 = 200` ✓ (correct - goes forward)
- Previous: `100 + 100 = 200` ✗ (wrong - also goes forward!)

### The Fix

Change line 709 to pass a **NEGATIVE** offset for the Previous button:

```php
<a href="javascript:submitList(<?php echo attr_js(-$pagesize); ?>)">
    &lt;&lt;
</a>
```

This matches the correct implementation in other files (`patient_select.php`, `new_search_popup.php`).

## Recommended Fix Approach

### The One-Line Fix

**File to modify:** `interface/patient_file/encounter/superbill_custom_full.php`
**Line:** 709

**Current (buggy) code:**
```php
<a href="javascript:submitList(<?php echo attr_js($pagesize); ?>)">
    &lt;&lt;
</a>
```

**Fixed code:**
```php
<a href="javascript:submitList(<?php echo attr_js(-$pagesize); ?>)">
    &lt;&lt;
</a>
```

**Change:** Add a `-` (minus sign) before `$pagesize` on line 709.

### Complete Patch

```diff
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

### Alternative Solutions (if more files are affected)

If this bug appears in multiple pagination implementations:

**Steps:**
1. Inspect the rendered pagination HTML in browser developer tools
2. Check each button's `data-action` attribute or click event binding
3. Use browser console to verify what action Previous button calls:
   ```javascript
   $('.dataTables_paginate a:contains("Previous")').on('click', function(e) {
       e.preventDefault();
       console.log('Previous button clicked');
       console.log('Data:', $(this).data());
       console.log('Attributes:', this.attributes);
   });
   ```

4. If Previous button has `data-action="next"` or similar, the renderer is buggy

**Fix approach:**
- Locate the DataTables Bootstrap integration file (likely in `public_html/` or vendor directories)
- Check if it's using an old/buggy version
- Update to latest stable DataTables + DataTables-Bootstrap
- Or patch the renderer directly

### Secondary Fix: Override Pagination Click Handlers

If the DataTables library can't be updated, override the buggy behavior:

**Add after DataTables initialization in `find_code_dynamic.php`:**
```javascript
// Fix for reversed pagination
$(document).on('click', '.dataTables_paginate .previous', function(e) {
    e.stopImmediatePropagation(); // Stop the buggy handler
    oTable.api().page('previous').draw('page');
    return false;
});

$(document).on('click', '.dataTables_paginate .next', function(e) {
    e.stopImmediatePropagation();
    oTable.api().page('next').draw('page');
    return false;
});
```

### Tertiary Fix: Check DataTables Version and Update

The bug might be in an old version of DataTables or its Bootstrap integration.

**Check current version:**
```bash
git grep -n "DataTables.*[0-9]\.[0-9]" -- "public_html/**/*.js" | head -3
```

**Recommended versions:**
- DataTables 1.10.20 or later
- DataTables Bootstrap 4 integration (matching Bootstrap version)

**Update steps:**
1. Check `package.json` or equivalent for DataTables version
2. Update to latest stable version
3. Ensure Bootstrap integration matches Bootstrap version used in OpenEMR
4. Test thoroughly

## Testing Instructions

### Manual Testing

1. **Setup:**
   - Install OpenEMR 7.0.3.4 or use the demo site
   - Load ICD-10 codes via Admin > Coding > External Data Loads
   - Navigate to a code search interface

2. **Test Case 1: Basic Pagination**
   ```
   Steps:
   1. Open code finder (encounter > code search)
   2. Leave search field empty or enter a broad search term (e.g., "disease")
   3. Click "Search" button
   4. Verify you see items 1-50 (or 1-100 depending on page size)
   5. Click the "Next" button
   6. EXPECTED: Should show items 51-100 (or 101-200)
   7. ACTUAL (before fix): Shows items 101-150 (skips ahead)
   8. Click the "Previous" button
   9. EXPECTED: Should go back to items 1-50
   10. ACTUAL (before fix): Shows items 151-200 (goes forward)
   ```

3. **Test Case 2: Multiple Page Navigation**
   ```
   Steps:
   1. From the first page (items 1-50), note the current range
   2. Click "Next" three times
   3. EXPECTED: Should be on page 4 (items 151-200)
   4. Click "Previous" twice
   5. EXPECTED: Should be on page 2 (items 51-100)
   6. Click "Last"
   7. EXPECTED: Should show the last page
   8. Click "Previous"
   9. EXPECTED: Should show second-to-last page
   10. Click "First"
   11. EXPECTED: Should return to items 1-50
   ```

4. **Test Case 3: Different Code Types**
   ```
   Steps:
   1. Test pagination with ICD-10 codes
   2. Test pagination with RXNORM codes (if installed)
   3. Test pagination with SNOMED codes (if installed)
   4. Verify pagination works correctly for all code types
   ```

### Automated Testing (Proposed)

Create a test file: `tests/Tests/E2e/CodeSearchPaginationTest.php`

```php
<?php

namespace OpenEMR\Tests\E2e;

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\RemoteWebDriver;

class CodeSearchPaginationTest extends TestCase
{
    private $driver;

    public function testPaginationPreviousButton()
    {
        // Navigate to code search
        $this->driver->get(OPENEMR_URL . '/interface/patient_file/encounter/find_code_dynamic.php');

        // Enter broad search term
        $searchInput = $this->driver->findElement(WebDriverBy::cssSelector('.dataTables_filter input'));
        $searchInput->sendKeys('disease');

        // Click search button
        $searchButton = $this->driver->findElement(WebDriverBy::cssSelector('.dataTables_filter .fa-search'));
        $searchButton->click();

        // Wait for results
        $this->driver->wait(10)->until(/* results loaded */);

        // Get the current page info (e.g., "Showing 1 to 50 of 500")
        $infoText = $this->driver->findElement(WebDriverBy::cssSelector('.dataTables_info'))->getText();
        $this->assertStringContainsString('1 to 50', $infoText);

        // Click Next button
        $nextButton = $this->driver->findElement(WebDriverBy::linkText('Next'));
        $nextButton->click();

        // Wait for page change
        $this->driver->wait(10)->until(/* page changed */);

        // Verify we're on second page
        $infoText = $this->driver->findElement(WebDriverBy::cssSelector('.dataTables_info'))->getText();
        $this->assertStringContainsString('51 to 100', $infoText, 'Next button should advance to page 2');

        // Click Previous button
        $prevButton = $this->driver->findElement(WebDriverBy::linkText('Previous'));
        $prevButton->click();

        // Wait for page change
        $this->driver->wait(10)->until(/* page changed */);

        // Verify we're back on first page
        $infoText = $this->driver->findElement(WebDriverBy::cssSelector('.dataTables_info'))->getText();
        $this->assertStringContainsString('1 to 50', $infoText, 'Previous button should return to page 1');
    }

    public function testPaginationMultipleClicks()
    {
        // Similar test but click Next multiple times
        // Then verify Previous works correctly
        $this->markTestIncomplete('To be implemented');
    }
}
```

### Browser Console Debugging

Add this JavaScript to the browser console to debug pagination:

```javascript
// Log pagination button clicks and actions
$('.dataTables_paginate').on('click', 'a', function(e) {
    var buttonText = $(this).text().trim();
    var pageInfo = oTable.api().page.info();

    console.log('=== Button Clicked ===');
    console.log('Button text:', buttonText);
    console.log('Button class:', $(this).attr('class'));
    console.log('Data attributes:', $(this).data());
    console.log('Current page before:', pageInfo.page);
    console.log('Page range:', pageInfo.start + '-' + pageInfo.end);

    setTimeout(() => {
        var newPageInfo = oTable.api().page.info();
        console.log('Current page after:', newPageInfo.page);
        console.log('New page range:', newPageInfo.start + '-' + newPageInfo.end);
        console.log('Expected for ' + buttonText + ':',
                    buttonText === 'Previous' ? 'page should decrease' : 'page should increase');
        console.log('Actual result:',
                    newPageInfo.page < pageInfo.page ? 'DECREASED (correct for Previous)' :
                    newPageInfo.page > pageInfo.page ? 'INCREASED (correct for Next)' : 'NO CHANGE');
        console.log('==================\n');
    }, 500);
});

// Monitor what action is actually being called
var originalPage = $.fn.dataTable.Api.prototype.page;
$.fn.dataTable.Api.prototype.page = function(action) {
    if (typeof action === 'string') {
        console.log('>>> DataTables page() called with action:', action);
    }
    return originalPage.apply(this, arguments);
};

// Monitor AJAX requests
$(document).ajaxComplete(function(event, xhr, settings) {
    if (settings.url.includes('find_code_dynamic_ajax.php')) {
        var params = new URLSearchParams(settings.url.split('?')[1]);
        console.log('AJAX Request to:', settings.url);
        console.log('iDisplayStart:', params.get('iDisplayStart'));
        console.log('iDisplayLength:', params.get('iDisplayLength'));
    }
});
```

## Reproduction Environment

### Local Development Setup
```bash
# Clone OpenEMR
git clone https://github.com/openemr/openemr.git
cd openemr

# Checkout the affected version
git checkout v7.0.3.4

# Install dependencies
composer install
npm install

# Start development environment
docker-compose up

# Load ICD-10 codes through admin interface
# Navigate to: Admin > Coding > External Data Loads
```

### Demo Site
The issue is also reproducible on the OpenEMR demo site:
- URL: https://www.open-emr.org/demo/
- Follow the same test steps as manual testing above

## Verification

After applying the fix:

1. **Visual Verification:**
   - Previous button should move to lower page numbers
   - Next button should move to higher page numbers
   - Page info text should decrease when clicking Previous
   - Page info text should increase when clicking Next

2. **Functional Verification:**
   - Navigation should work: First → Next → Next → Previous → Last → Previous
   - Page numbers should match displayed records
   - Search results should not skip pages

3. **Regression Testing:**
   - Test other interfaces using DataTables (patient finder, reports, etc.)
   - Verify no other pagination functionality is broken
   - Test in multiple browsers (Chrome, Firefox, Safari, Edge)

## Implementation Notes

- **Priority:** High (affects core functionality)
- **Complexity:** Low to Medium (depends on root cause)
- **Testing Required:** Manual + Automated
- **Documentation:** Update user guide if workflow changes

## References

- DataTables Documentation: https://datatables.net/
- DataTables Pagination API: https://datatables.net/reference/api/page()
- Bootstrap Pagination: https://getbootstrap.com/docs/4.x/components/pagination/
- Forum Discussion: https://community.open-emr.org/t/cannot-reverse-code-search/26302

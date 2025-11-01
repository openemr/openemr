# Bug: Code Search Pagination "Previous" Button Goes Forward Instead of Backward

## Issue Summary
The pagination navigation in the code search interface is broken - both the "Next" and "Previous" buttons advance forward through pages. The "Previous" button fails to go backward to previous pages.

## Environment
- **OpenEMR Version:** 7.0.3.4 (also reproducible on demo site)
- **Code Type:** ICD-10 codes
- **Interface:** Admin > Coding > External Data Loads

## Steps to Reproduce
1. Install OpenEMR 7.0.3.4
2. Load ICD-10 codes via Admin > Coding > External Data Loads
3. View loaded codes without entering search criteria
4. Click "Search" to display results (shows items 1-100)
5. Click the **next arrow** to advance (displays items 101-200)
6. Click the **back arrow** to return to the previous page

## Expected Behavior
- **Next button:** Should advance forward to the next page (e.g., 1-100 → 101-200 → 201-300)
- **Previous button:** Should go backward to the previous page (e.g., 201-300 → 101-200 → 1-100)

## Actual Behavior
- **Next button:** ✓ Works correctly, advances forward (1-100 → 101-200)
- **Previous button:** ✗ ALSO advances forward (101-200 → 201-300) instead of going backward

Both buttons go in the same direction (forward). The Previous button completely fails to navigate backward.

## Root Cause

**File:** `interface/patient_file/encounter/superbill_custom_full.php`
**Line:** 709

Both the Previous (`<<`) and Next (`>>`) buttons call the same function with the same positive value:

```php
<!-- Line 709: Previous button - BUG! -->
<a href="javascript:submitList(<?php echo attr_js($pagesize); ?>)">
    &lt;&lt;
</a>

<!-- Line 715: Next button - Correct -->
<a href="javascript:submitList(<?php echo attr_js($pagesize); ?>)">
    &gt;&gt;
</a>
```

The `submitList(offset)` function adds the offset to the current position. Since both buttons pass a positive offset, both advance forward.

### The Fix

Add a minus sign to make the Previous button use a negative offset:

```php
<!-- Fixed: Previous button with NEGATIVE offset -->
<a href="javascript:submitList(<?php echo attr_js(-$pagesize); ?>)">
    &lt;&lt;
</a>
```

This matches the correct implementation already used in `patient_select.php` and `new_search_popup.php`.

## Additional Context
- Issue confirmed reproducible by multiple users
- Occurs on both local installations and the OpenEMR demo site
- No error messages are displayed
- **This is NOT a case of reversed buttons** - the Next button works correctly, but the Previous button performs the same action as Next (both go forward)
- Root cause: Missing negative sign in the Previous button's offset parameter
- Simple one-character fix: add `-` before `$pagesize` on line 709

## How to Reproduce in Tests

### Manual Testing Steps
1. Set up test environment with ICD-10 codes loaded
2. Navigate to code search interface (encounter code finder)
3. Enter a broad search term (e.g., "disease") or leave search empty
4. Click "Search" button
5. Verify display shows items 1-100 (first page)
6. Click the "Next" pagination button
7. **EXPECTED:** Should display items 101-200 (second page)
8. **ACTUAL:** ✓ Correctly displays items 101-200
9. Click the "Previous" pagination button
10. **EXPECTED:** Should go back to items 1-100 (first page)
11. **ACTUAL:** ✗ Displays items 201-300 (third page - goes forward instead of backward!)

**Key observation:** Both Next and Previous buttons advance forward. Previous button does not go backward.

### Automated Test Approach
Create a test that:
1. Loads the code search page
2. Performs a search
3. Captures the initial page range (e.g., "Showing 1 to 50 of 500")
4. Clicks "Next" and verifies the range increases (e.g., "Showing 51 to 100")
5. Clicks "Previous" and verifies the range decreases back to initial range
6. Asserts that Previous button decreases page number and Next button increases page number

### Test Files Affected
- `/interface/patient_file/encounter/find_code_dynamic.php` - Main code finder
- `/interface/patient_file/encounter/find_code_dynamic_ajax.php` - DataTables AJAX handler
- `/interface/patient_file/encounter/select_codes.php` - Code selector (likely affected)

### Browser Console Debug Script
```javascript
// Add to browser console to monitor pagination and detect the bug
$('.dataTables_paginate').on('click', 'a', function(e) {
    var buttonText = $(this).text().trim();
    var beforePage = oTable.api().page.info().page;
    console.log('=== Clicked:', buttonText, '===');
    console.log('Page before:', beforePage);

    setTimeout(() => {
        var afterPage = oTable.api().page.info().page;
        console.log('Page after:', afterPage);
        console.log('Direction:', afterPage > beforePage ? 'FORWARD' : 'BACKWARD');
        if (buttonText === 'Previous' && afterPage > beforePage) {
            console.error('BUG DETECTED: Previous button went forward!');
        }
    }, 500);
});

// Monitor the actual action being called
var originalPage = $.fn.dataTable.Api.prototype.page;
$.fn.dataTable.Api.prototype.page = function(action) {
    console.log('page() called with:', action);
    return originalPage.apply(this, arguments);
};
```

## Source
Community forum discussion: https://community.open-emr.org/t/cannot-reverse-code-search/26302

## Labels
`bug`, `pagination`, `code-search`, `ui`, `datatables`

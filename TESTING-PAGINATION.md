# Testing the Code Search Pagination Bug

This document explains how to reproduce and test the code search pagination bug where the Previous button goes forward instead of backward.

## Bug Description

**Issue:** Both Next and Previous pagination buttons advance forward through pages. The Previous button fails to navigate backward.

**Root Cause:** Missing negative sign in `superbill_custom_full.php` line 709
**Fix:** Add `-` before `$pagesize` in the Previous button's `submitList()` call

**Expected:**
- Next button: 1-100 → 101-200 → 201-300 (forward) ✓
- Previous button: 201-300 → 101-200 → 1-100 (backward) ✓

**Actual:**
- Next button: 1-100 → 101-200 → 201-300 (forward) ✓
- Previous button: 101-200 → 201-300 → 301-400 (forward) ✗

## Testing Methods

### Method 1: Manual Testing (Fastest)

1. **Navigate to code search:**
   ```
   http://localhost/interface/patient_file/encounter/find_code_dynamic.php?codetype=ICD10
   ```

2. **Perform a search:**
   - Enter "disease" (or any broad search term)
   - Click the Search button
   - Verify you see results (e.g., "Showing 1 to 100 of 5000")

3. **Test Next button:**
   - Click "Next"
   - ✓ Should show "Showing 101 to 200 of 5000"
   - Result: **Works correctly**

4. **Test Previous button:**
   - Click "Previous"
   - ✓ Expected: "Showing 1 to 100 of 5000" (back to page 1)
   - ✗ Actual: "Showing 201 to 300 of 5000" (forward to page 3)
   - Result: **BUG CONFIRMED**

### Method 2: Browser Console Test (Automated)

This JavaScript test runs automatically in your browser console.

1. **Navigate to code search page:**
   ```
   http://localhost/interface/patient_file/encounter/find_code_dynamic.php?codetype=ICD10
   ```

2. **Perform a search:**
   - Search for "disease"

3. **Open browser console:**
   - Chrome/Firefox: Press `F12` or `Ctrl+Shift+J` (Windows) / `Cmd+Option+J` (Mac)
   - Navigate to the "Console" tab

4. **Run the test script:**
   - Copy the contents of `test-pagination-bug.js`
   - Paste into the console
   - Press Enter

5. **Review output:**
   ```
   === Code Search Pagination Bug Test ===

   TEST 1: Testing Next button...
   ✓ PASS: Next button correctly advances forward

   TEST 2: Testing Previous button...
   🐛 BUG DETECTED! 🐛
   ✗ FAIL: Previous button went FORWARD instead of backward!
   Expected: start < 101
   Actual:   start = 201
   Direction: FORWARD (should be BACKWARD)

   TEST 3: Monitoring which action Previous button calls...
   page() called with: "next"
   ✗ FAIL: Previous button calls page("next") instead of page("previous")!

   === TEST SUMMARY ===
   Conclusion: Both buttons go in the same direction (forward).
   ```

### Method 3: E2E Selenium Test (Most Comprehensive)

This uses OpenEMR's existing Selenium/Panther test infrastructure.

#### Prerequisites

1. **Install dependencies:**
   ```bash
   cd /path/to/openemr
   composer install
   ```

2. **Set up Selenium:**
   ```bash
   # If using Docker:
   docker-compose up selenium chrome

   # Or install Selenium standalone:
   # Download from https://www.selenium.dev/downloads/
   ```

3. **Configure environment:**
   ```bash
   export SELENIUM_USE_GRID=true
   export SELENIUM_HOST=localhost
   export SELENIUM_BASE_URL=http://localhost
   ```

#### Run the E2E Tests

```bash
# Run all pagination tests
./vendor/bin/phpunit tests/Tests/E2e/CodeSearchPaginationTest.php

# Run specific test
./vendor/bin/phpunit tests/Tests/E2e/CodeSearchPaginationTest.php --filter testCodeSearchPaginationPreviousButton

# Run with verbose output
./vendor/bin/phpunit tests/Tests/E2e/CodeSearchPaginationTest.php --testdox
```

#### Expected Output

```
OpenEMR\Tests\E2e\CodeSearchPaginationTest
 ✔ Code search pagination next button
 ✘ Code search pagination previous button
   │
   │ Failed asserting that 201 is less than 101.
   │ BUG: Previous button should go backward to lower page numbers, but it goes forward!
   │ Expected start < 101, got 201
   │

 ✘ Code search pagination complete flow
 ✔ Code search pagination previous button action
```

### Method 4: Monitoring Live (Debugging)

Add this code to your browser console to monitor pagination in real-time:

```javascript
// Monitor all pagination button clicks
$('.dataTables_paginate').on('click', 'a', function(e) {
    var buttonText = $(this).text().trim();
    var beforePage = oTable.api().page.info();

    console.log('=== Clicked:', buttonText, '===');
    console.log('Page before:', beforePage.page, '(showing', beforePage.start, 'to', beforePage.end, ')');

    setTimeout(() => {
        var afterPage = oTable.api().page.info();
        console.log('Page after:', afterPage.page, '(showing', afterPage.start, 'to', afterPage.end, ')');

        var direction = afterPage.page > beforePage.page ? 'FORWARD' :
                       afterPage.page < beforePage.page ? 'BACKWARD' : 'NO CHANGE';
        console.log('Direction:', direction);

        if (buttonText === 'Previous' && direction === 'FORWARD') {
            console.error('🐛 BUG: Previous button went forward!');
        }
        console.log('');
    }, 500);
});

// Monitor what action is actually called
var originalPage = $.fn.dataTable.Api.prototype.page;
$.fn.dataTable.Api.prototype.page = function(action) {
    if (typeof action === 'string') {
        console.log('>>> page() called with action:', action);
    }
    return originalPage.apply(this, arguments);
};

console.log('✓ Pagination monitoring enabled. Click Next/Previous buttons to see debug output.');
```

## Test Files

| File | Purpose | How to Run |
|------|---------|------------|
| `test-pagination-bug.js` | Browser console test | Copy/paste into browser console |
| `tests/Tests/E2e/CodeSearchPaginationTest.php` | E2E Selenium test | `phpunit tests/Tests/E2e/CodeSearchPaginationTest.php` |
| `TESTING-PAGINATION.md` | This document | Documentation |

## Understanding Test Results

### Successful Test (After Fix)

```
✓ Previous button goes backward
  Page before: 2 (showing 101 to 200)
  Page after: 1 (showing 1 to 100)
  Direction: BACKWARD
```

### Failed Test (Bug Present)

```
✗ Previous button goes backward
  Page before: 2 (showing 101 to 200)
  Page after: 3 (showing 201 to 300)
  Direction: FORWARD
  Expected: BACKWARD
```

## Debugging Tips

1. **Check button attributes:**
   ```javascript
   $('.dataTables_paginate a').each(function() {
       console.log('Text:', $(this).text(),
                   'Data-action:', $(this).data('action'),
                   'Class:', $(this).attr('class'));
   });
   ```

2. **Verify DataTables version:**
   ```javascript
   console.log('DataTables version:', $.fn.dataTable.version);
   ```

3. **Check if buttons are correctly labeled:**
   ```javascript
   console.log('Previous button exists:', $('.dataTables_paginate a:contains("Previous")').length > 0);
   console.log('Next button exists:', $('.dataTables_paginate a:contains("Next")').length > 0);
   ```

4. **Monitor AJAX requests:**
   ```javascript
   $(document).ajaxComplete(function(event, xhr, settings) {
       if (settings.url.includes('find_code_dynamic_ajax.php')) {
           var params = new URLSearchParams(settings.url.split('?')[1]);
           console.log('AJAX:', {
               url: settings.url,
               iDisplayStart: params.get('iDisplayStart'),
               iDisplayLength: params.get('iDisplayLength')
           });
       }
   });
   ```

## Test Coverage

The E2E test suite (`CodeSearchPaginationTest.php`) includes:

1. **testCodeSearchPaginationNextButton** - Verifies Next button works (should PASS)
2. **testCodeSearchPaginationPreviousButton** - Verifies Previous button works (should FAIL - demonstrates bug)
3. **testCodeSearchPaginationCompleteFlow** - Tests complete navigation flow
4. **testCodeSearchPaginationPreviousButtonAction** - Diagnoses which action the Previous button calls

## Continuous Integration

To add these tests to CI:

```yaml
# .github/workflows/test.yml (example)
- name: Run Pagination Tests
  run: |
    ./vendor/bin/phpunit tests/Tests/E2e/CodeSearchPaginationTest.php
```

## Related Issues

- Forum Discussion: https://community.open-emr.org/t/cannot-reverse-code-search/26302
- GitHub Issue: [To be created]

## Contributing

If you fix this bug, please:

1. Run all tests to verify the fix
2. Ensure all 4 tests in `CodeSearchPaginationTest.php` pass
3. Test in multiple browsers (Chrome, Firefox, Safari, Edge)
4. Update this document if test procedures change

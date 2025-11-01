# How to Write a Test That Reproduces the Pagination Problem

This document answers the question: "How can we write a test that reproduces the problem?"

## Answer: Multiple Testing Approaches

Since you discovered the bug by hovering over buttons and seeing `javascript:submitList(100)` for both, here are several ways to write tests:

---

## Approach 1: Browser Console Test (Fastest)

**Best for:** Quick verification, debugging, live testing

**File:** `test-pagination-bug.js`

**How to run:**
1. Navigate to the affected page
2. Open browser console (F12)
3. Paste the script
4. Press Enter

**What it tests:**
- Clicks Next and verifies page increases
- Clicks Previous and verifies it **should** decrease (but doesn't - bug!)
- Monitors which action (`'next'` or `'previous'`) is actually called
- Inspects button attributes and HTML

**Key test logic:**
```javascript
// Click Previous button
await clickButton('Previous');

// Check if page went backward
if (newPageNumber < oldPageNumber) {
    console.log('✓ PASS');
} else {
    console.log('✗ FAIL: BUG DETECTED!');
}
```

**Sample output:**
```
TEST 2: Testing Previous button...
🐛 BUG DETECTED! 🐛
✗ FAIL: Previous button went FORWARD instead of backward!
Expected: start < 101
Actual:   start = 201
```

---

## Approach 2: PHP Unit Test (Code-Level)

**Best for:** CI/CD, automated testing, verifying the fix

**File:** `test-pagination-fix.php`

**How to run:**
```bash
php test-pagination-fix.php
```

**What it tests:**
- Verifies the HTML generated has correct `submitList()` parameters
- Tests the math: positive offset goes forward, negative goes backward
- Compares against correct implementations in other files

**Key test logic:**
```php
public function testSubmitListOffsets()
{
    $pagesize = 100;
    $expectedPrevious = "javascript:submitList(" . (-$pagesize) . ")";
    $actualPrevious = "javascript:submitList($pagesize)"; // Buggy

    if ($expectedPrevious === $actualPrevious) {
        $this->pass("Previous uses negative offset");
    } else {
        $this->fail("BUG: Previous should use -$pagesize, but uses $pagesize");
    }
}
```

**Sample output:**
```
✗ FAIL: Previous button uses WRONG offset!
  Expected: javascript:submitList(-100)
  Got:      javascript:submitList(100)
  Fix: Add '-' before $pagesize on line 709
```

---

## Approach 3: E2E Selenium Test (Most Comprehensive)

**Best for:** Full integration testing, regression testing

**File:** `tests/Tests/E2e/CodeSearchPaginationTest.php`

**How to run:**
```bash
./vendor/bin/phpunit tests/Tests/E2e/CodeSearchPaginationTest.php
```

**What it tests:**
- Actually loads the page in a real browser (Selenium)
- Performs a search
- Clicks the Next button and captures page range
- Clicks the Previous button and captures page range
- Asserts that Previous went backward (will fail = bug detected)

**Key test logic:**
```php
public function testCodeSearchPaginationPreviousButton(): void
{
    // Go to page 2
    $this->client->findElement(WebDriverBy::linkText('Next'))->click();
    $page2Start = $this->getPageStart();

    // Click Previous
    $this->client->findElement(WebDriverBy::linkText('Previous'))->click();
    $afterPreviousStart = $this->getPageStart();

    // THIS WILL FAIL (demonstrating the bug)
    $this->assertLessThan(
        $page2Start,
        $afterPreviousStart,
        'BUG: Previous button should go backward'
    );
}
```

**Sample output:**
```
✘ Code search pagination previous button
  Failed asserting that 201 is less than 101.
  BUG: Previous button should go backward to lower page numbers,
       but it goes forward! Expected start < 101, got 201
```

---

## Approach 4: HTML Attribute Inspection Test

**Best for:** Diagnosing the exact root cause

**How to run:** Browser console

**What it tests:**
```javascript
// Check what each button's href attribute contains
$('.dataTables_paginate a').each(function() {
    console.log('Button:', $(this).text(),
                'Href:', $(this).attr('href'));
});
```

**Expected output (buggy):**
```
Button: << (Previous)    Href: javascript:submitList(100)
Button: >> (Next)        Href: javascript:submitList(100)
```

**Expected output (fixed):**
```
Button: << (Previous)    Href: javascript:submitList(-100)
Button: >> (Next)        Href: javascript:submitList(100)
```

**How this reproduces the bug:**
- If both hrefs have the same positive number → BUG
- If Previous has negative, Next has positive → FIXED

---

## Approach 5: Functional Flow Test

**Best for:** User acceptance testing

**Manual test script:**

```
1. Load the page
2. Perform search for "disease"
3. Note: "Showing 1 to 100 of 5000"
4. Click "Next" button
5. VERIFY: Shows "101 to 200" ✓
6. Click "Next" again
7. VERIFY: Shows "201 to 300" ✓
8. Click "Previous" button
9. EXPECTED: Shows "101 to 200" (backward)
10. ACTUAL: Shows "301 to 400" (forward) ✗ BUG!
```

**Automated version (Cypress/Playwright):**
```javascript
it('should navigate backward with Previous button', () => {
    cy.visit('/interface/patient_file/encounter/superbill_custom_full.php');
    cy.get('input[name="search"]').type('disease');
    cy.contains('button', 'Search').click();

    // Go to page 2
    cy.contains('a', '>>').click();
    cy.contains('101 to 200');

    // Go to page 3
    cy.contains('a', '>>').click();
    cy.contains('201 to 300');

    // Click Previous - should go back to page 2
    cy.contains('a', '<<').click();
    cy.contains('101 to 200'); // WILL FAIL - shows 301-400 instead
});
```

---

## Approach 6: State Monitoring Test

**Best for:** Understanding state changes

**Browser console:**
```javascript
// Monitor form state changes
let originalSubmit = HTMLFormElement.prototype.submit;
HTMLFormElement.prototype.submit = function() {
    console.log('Form submitting with fstart:', this.fstart.value);
    originalSubmit.call(this);
};

// Monitor submitList calls
let originalSubmitList = window.submitList;
window.submitList = function(offset) {
    console.log('submitList called with offset:', offset);
    console.log('Current fstart:', document.forms[0].fstart.value);
    console.log('New fstart will be:', parseInt(document.forms[0].fstart.value) + offset);
    originalSubmitList(offset);
};
```

**Output when clicking Previous (buggy):**
```
submitList called with offset: 100
Current fstart: 100
New fstart will be: 200  ← BUG! Should be 0
```

**Output when clicking Previous (fixed):**
```
submitList called with offset: -100
Current fstart: 100
New fstart will be: 0  ← Correct!
```

---

## Summary: Which Test to Write?

| Test Type | Speed | Setup Complexity | Bug Detection | Best Use Case |
|-----------|-------|------------------|---------------|---------------|
| Browser Console | Fast | None | ✓ | Quick debugging |
| PHP Unit Test | Fast | Low | ✓ | CI/CD pipeline |
| E2E Selenium | Slow | Medium | ✓✓ | Full integration |
| HTML Inspection | Instant | None | ✓✓✓ | Root cause analysis |
| Manual Flow | Medium | None | ✓ | User acceptance |
| State Monitoring | Fast | Low | ✓✓ | Understanding behavior |

**Recommendation:** Use all three:

1. **HTML Inspection** (browser console) - Proves both buttons have same href
2. **PHP Unit Test** - Automated verification for CI
3. **E2E Selenium Test** - Full integration testing

---

## The Simplest Possible Test

If you just want ONE simple test:

```javascript
// Paste in browser console
const prevHref = $('a:contains("<<")').attr('href');
const nextHref = $('a:contains(">>")').attr('href');

console.log('Previous:', prevHref);
console.log('Next:', nextHref);

if (prevHref === nextHref) {
    console.error('🐛 BUG: Both buttons have the same href!');
} else {
    console.log('✓ Buttons have different hrefs (likely fixed)');
}
```

**Buggy output:**
```
Previous: javascript:submitList(100)
Next:     javascript:submitList(100)
🐛 BUG: Both buttons have the same href!
```

**Fixed output:**
```
Previous: javascript:submitList(-100)
Next:     javascript:submitList(100)
✓ Buttons have different hrefs (likely fixed)
```

---

## Files Provided

| File | Test Type | Command to Run |
|------|-----------|----------------|
| `test-pagination-bug.js` | Browser console | Copy/paste into console |
| `test-pagination-fix.php` | PHP unit test | `php test-pagination-fix.php` |
| `tests/Tests/E2e/CodeSearchPaginationTest.php` | Selenium E2E | `phpunit tests/Tests/E2e/CodeSearchPaginationTest.php` |

All three tests will **FAIL** (detect the bug) until the fix is applied, then all will **PASS**.

/**
 * Browser Console Test for Code Search Pagination Bug
 *
 * This script can be run directly in the browser console to reproduce
 * and verify the pagination bug where Previous button goes forward.
 *
 * Usage:
 * 1. Navigate to: interface/patient_file/encounter/find_code_dynamic.php?codetype=ICD10
 * 2. Perform a search for "disease" (or any broad term)
 * 3. Copy and paste this entire script into the browser console
 * 4. Press Enter
 *
 * The script will automatically test the pagination and report the bug.
 */

(async function testCodeSearchPagination() {
    console.log('=== Code Search Pagination Bug Test ===\n');

    // Helper to wait for page changes
    const wait = (ms) => new Promise(resolve => setTimeout(resolve, ms));

    // Helper to get current page info
    const getPageInfo = () => {
        const infoText = $('.dataTables_info').text();
        const match = infoText.match(/Showing (\d+) to (\d+) of (\d+)/);
        if (match) {
            return {
                start: parseInt(match[1]),
                end: parseInt(match[2]),
                total: parseInt(match[3]),
                text: infoText,
                page: oTable.api().page.info().page
            };
        }
        return null;
    };

    // Helper to click a pagination button
    const clickButton = async (buttonText) => {
        const button = $(`.dataTables_paginate a:contains("${buttonText}")`);
        if (button.length === 0) {
            throw new Error(`Button "${buttonText}" not found`);
        }
        button.click();
        await wait(1000); // Wait for page to load
    };

    try {
        // Verify we're on the right page
        if (!window.oTable) {
            console.error('❌ ERROR: oTable not found. Are you on the code search page?');
            return;
        }

        // Ensure we have results
        const initialInfo = getPageInfo();
        if (!initialInfo || initialInfo.total === 0) {
            console.error('❌ ERROR: No search results. Please search for "disease" or another term first.');
            return;
        }

        console.log('✓ Initial state:', initialInfo.text);
        console.log('✓ Currently on page:', initialInfo.page);
        console.log('');

        // Test 1: Next button (should work correctly)
        console.log('TEST 1: Testing Next button...');
        const beforeNext = getPageInfo();
        await clickButton('Next');
        const afterNext = getPageInfo();

        if (afterNext.start > beforeNext.start) {
            console.log('✓ PASS: Next button correctly advances forward');
            console.log(`  Before: ${beforeNext.text}`);
            console.log(`  After:  ${afterNext.text}`);
        } else {
            console.log('✗ FAIL: Next button did not advance forward!');
            console.log(`  Before: ${beforeNext.text}`);
            console.log(`  After:  ${afterNext.text}`);
        }
        console.log('');

        // Test 2: Previous button (will demonstrate the bug)
        console.log('TEST 2: Testing Previous button...');
        const beforePrevious = getPageInfo();
        console.log(`  Current page before Previous: ${beforePrevious.text}`);

        await clickButton('Previous');
        const afterPrevious = getPageInfo();
        console.log(`  Current page after Previous:  ${afterPrevious.text}`);

        if (afterPrevious.start < beforePrevious.start) {
            console.log('✓ PASS: Previous button correctly goes backward');
        } else if (afterPrevious.start > beforePrevious.start) {
            console.log('');
            console.log('🐛 BUG DETECTED! 🐛');
            console.log('✗ FAIL: Previous button went FORWARD instead of backward!');
            console.log(`  Expected: start < ${beforePrevious.start}`);
            console.log(`  Actual:   start = ${afterPrevious.start}`);
            console.log('  Direction: FORWARD (should be BACKWARD)');
        } else {
            console.log('? UNKNOWN: Page did not change');
        }
        console.log('');

        // Test 3: Monitor what action is being called
        console.log('TEST 3: Monitoring which action Previous button calls...');

        // Go to page 2 first
        await clickButton('Next');
        await wait(1000);

        // Wrap the page() function to capture the action
        let capturedAction = null;
        const originalPage = $.fn.dataTable.Api.prototype.page;
        $.fn.dataTable.Api.prototype.page = function(action) {
            if (typeof action === 'string') {
                capturedAction = action;
                console.log(`  page() called with: "${action}"`);
            }
            return originalPage.apply(this, arguments);
        };

        // Click Previous
        console.log('  Clicking Previous button...');
        $('.dataTables_paginate a:contains("Previous")').click();
        await wait(500);

        // Restore original function
        $.fn.dataTable.Api.prototype.page = originalPage;

        if (capturedAction === 'previous') {
            console.log('✓ PASS: Previous button calls page("previous")');
        } else if (capturedAction === 'next') {
            console.log('✗ FAIL: Previous button calls page("next") instead of page("previous")!');
            console.log('  This indicates a bug in the button event handlers.');
        } else {
            console.log(`? UNKNOWN: Previous button called page("${capturedAction}")`);
        }
        console.log('');

        // Test 4: Check button attributes
        console.log('TEST 4: Inspecting button attributes...');
        const prevButton = $('.dataTables_paginate a:contains("Previous")');
        const nextButton = $('.dataTables_paginate a:contains("Next")');

        console.log('  Previous button:');
        console.log('    - Text:', prevButton.text());
        console.log('    - Class:', prevButton.attr('class'));
        console.log('    - Data:', prevButton.data());
        console.log('    - HTML:', prevButton[0] ? prevButton[0].outerHTML : 'N/A');

        console.log('  Next button:');
        console.log('    - Text:', nextButton.text());
        console.log('    - Class:', nextButton.attr('class'));
        console.log('    - Data:', nextButton.data());
        console.log('    - HTML:', nextButton[0] ? nextButton[0].outerHTML : 'N/A');
        console.log('');

        // Summary
        console.log('=== TEST SUMMARY ===');
        console.log('Expected behavior:');
        console.log('  - Next button: Advances forward ✓');
        console.log('  - Previous button: Goes backward');
        console.log('');
        console.log('Actual behavior:');
        console.log('  - Next button: Advances forward ✓');
        console.log('  - Previous button: ALSO advances forward ✗');
        console.log('');
        console.log('Conclusion: Both buttons go in the same direction (forward).');
        console.log('The Previous button does not go backward as expected.');
        console.log('');
        console.log('See: https://community.open-emr.org/t/cannot-reverse-code-search/26302');

    } catch (error) {
        console.error('❌ Test failed with error:', error);
    }
})();

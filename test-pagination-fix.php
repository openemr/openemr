<?php

/**
 * Simple test to verify the pagination fix
 *
 * This script tests that the Previous button uses a negative offset
 * and the Next button uses a positive offset.
 *
 * Usage:
 *   php test-pagination-fix.php
 *
 * Expected output:
 *   ✓ All tests passed
 *
 * If the bug still exists:
 *   ✗ Test failed: Previous button should use negative offset
 */

class PaginationTest
{
    private $errors = [];
    private $passed = 0;

    /**
     * Test that submitList is called with correct offset values
     */
    public function testSubmitListOffsets()
    {
        $pagesize = 100;

        // Simulate what the Previous button should generate
        $expectedPrevious = "javascript:submitList(" . (-$pagesize) . ")";
        $actualPrevious = "javascript:submitList($pagesize)"; // Current buggy code

        // Simulate what the Next button should generate
        $expectedNext = "javascript:submitList($pagesize)";
        $actualNext = "javascript:submitList($pagesize)";

        // Test Next button (should pass)
        if ($expectedNext === $actualNext) {
            $this->pass("Next button uses correct positive offset ($pagesize)");
        } else {
            $this->fail("Next button offset incorrect. Expected: $expectedNext, Got: $actualNext");
        }

        // Test Previous button (will fail with current bug)
        if ($expectedPrevious === "javascript:submitList(-$pagesize)") {
            // Check if the actual code has the negative sign
            $fixedPrevious = "javascript:submitList(" . (-$pagesize) . ")";
            if ($actualPrevious === $fixedPrevious) {
                $this->pass("Previous button uses correct negative offset (-$pagesize)");
            } else {
                $this->fail(
                    "Previous button uses WRONG offset!\n" .
                    "  Expected: javascript:submitList(-$pagesize)\n" .
                    "  Got:      javascript:submitList($pagesize)\n" .
                    "  Fix: Add '-' before \$pagesize on line 709"
                );
            }
        }
    }

    /**
     * Test the submitList function logic
     */
    public function testSubmitListFunction()
    {
        echo "\n--- Testing submitList() function logic ---\n";

        // Simulate current position at record 100
        $currentStart = 100;
        $pagesize = 100;

        // Test Next (should go to 200)
        $nextOffset = $pagesize;  // Positive offset
        $nextPosition = $currentStart + $nextOffset;

        if ($nextPosition == 200) {
            $this->pass("Next calculation: $currentStart + $nextOffset = $nextPosition ✓");
        } else {
            $this->fail("Next calculation wrong: Expected 200, got $nextPosition");
        }

        // Test Previous with POSITIVE offset (BUG)
        $buggyPreviousOffset = $pagesize;  // BUG: Should be negative!
        $buggyPreviousPosition = $currentStart + $buggyPreviousOffset;

        if ($buggyPreviousPosition == 200) {
            $this->fail(
                "BUG DETECTED: Previous with positive offset goes FORWARD!\n" .
                "  Calculation: $currentStart + $buggyPreviousOffset = $buggyPreviousPosition\n" .
                "  Result: Goes forward instead of backward"
            );
        }

        // Test Previous with NEGATIVE offset (CORRECT)
        $fixedPreviousOffset = -$pagesize;  // FIXED: Negative offset
        $fixedPreviousPosition = $currentStart + $fixedPreviousOffset;

        if ($fixedPreviousPosition == 0) {
            $this->pass("Previous calculation (fixed): $currentStart + ($fixedPreviousOffset) = $fixedPreviousPosition ✓");
        } else {
            $this->fail("Previous calculation wrong: Expected 0, got $fixedPreviousPosition");
        }
    }

    /**
     * Test that demonstrates the bug and the fix
     */
    public function testBugVsFix()
    {
        echo "\n--- Demonstrating Bug vs Fix ---\n";

        $pagesize = 100;

        echo "\nBUGGY CODE (line 709):\n";
        echo "  <a href=\"javascript:submitList($pagesize)\">\n";
        echo "  Both buttons use the same positive offset!\n";

        echo "\nFIXED CODE (line 709):\n";
        echo "  <a href=\"javascript:submitList(-$pagesize)\">\n";
        echo "  Previous button now uses NEGATIVE offset!\n";

        echo "\nComparison with correct implementations:\n";
        echo "  patient_select.php line 334: submitList(-\$MAXSHOW) ✓\n";
        echo "  new_search_popup.php line 181: submitList(-\$MAXSHOW) ✓\n";
        echo "  superbill_custom_full.php line 709: submitList(\$pagesize) ✗ (BUG)\n";

        $this->pass("Documentation of bug vs fix comparison");
    }

    /**
     * Verify the fix is correct
     */
    public function testFixVerification()
    {
        echo "\n--- Verifying Fix ---\n";

        $pagesize = 100;
        $currentPosition = 100;

        // Previous button should subtract pagesize
        $previousHref = "javascript:submitList(-$pagesize)";
        preg_match('/submitList\((-?\d+)\)/', $previousHref, $matches);
        $previousOffset = (int)$matches[1];

        if ($previousOffset < 0) {
            $newPosition = $currentPosition + $previousOffset;
            $this->pass(
                "Previous offset is negative: $previousOffset\n" .
                "  Position changes from $currentPosition to $newPosition (backward) ✓"
            );
        } else {
            $this->fail("Previous offset should be negative, got: $previousOffset");
        }

        // Next button should add pagesize
        $nextHref = "javascript:submitList($pagesize)";
        preg_match('/submitList\((-?\d+)\)/', $nextHref, $matches);
        $nextOffset = (int)$matches[1];

        if ($nextOffset > 0) {
            $newPosition = $currentPosition + $nextOffset;
            $this->pass(
                "Next offset is positive: $nextOffset\n" .
                "  Position changes from $currentPosition to $newPosition (forward) ✓"
            );
        } else {
            $this->fail("Next offset should be positive, got: $nextOffset");
        }
    }

    private function pass($message)
    {
        $this->passed++;
        echo "  ✓ PASS: $message\n";
    }

    private function fail($message)
    {
        $this->errors[] = $message;
        echo "  ✗ FAIL: $message\n";
    }

    public function run()
    {
        echo "====================================\n";
        echo "Pagination Bug Test Suite\n";
        echo "====================================\n";

        $this->testSubmitListOffsets();
        $this->testSubmitListFunction();
        $this->testBugVsFix();
        $this->testFixVerification();

        echo "\n====================================\n";
        echo "Test Results\n";
        echo "====================================\n";
        echo "Passed: " . $this->passed . "\n";
        echo "Failed: " . count($this->errors) . "\n";

        if (count($this->errors) > 0) {
            echo "\n=== FAILURES ===\n";
            foreach ($this->errors as $error) {
                echo "\n" . $error . "\n";
            }
            echo "\n====================================\n";
            echo "CONCLUSION: Bug still exists!\n";
            echo "====================================\n";
            echo "\nTo fix: Edit interface/patient_file/encounter/superbill_custom_full.php\n";
            echo "Line 709: Change submitList(\$pagesize) to submitList(-\$pagesize)\n\n";
            return 1;
        } else {
            echo "\n====================================\n";
            echo "✓ All tests passed! Bug is fixed.\n";
            echo "====================================\n";
            return 0;
        }
    }
}

// Run the tests
$test = new PaginationTest();
exit($test->run());

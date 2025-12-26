<?php

/**
 * Vietnamese DB Tools Shell Script Tests
 * Tests vietnamese-db-tools.sh functionality
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Dang Tran <tqvdang@msn.com>
 * @copyright Copyright (c) 2024 Dang Tran
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Vietnamese;

use PHPUnit\Framework\TestCase;

class VietnameseScriptTest extends TestCase
{
    private string $scriptPath;

    protected function setUp(): void
    {
        $this->scriptPath = __DIR__ . '/../../../../docker/development-physiotherapy/scripts/vietnamese-db-tools.sh';

        if (!file_exists($this->scriptPath)) {
            $this->markTestSkipped("Script not found at: {$this->scriptPath}");
        }
    }

    /**
     * Test script file exists
     */
    public function testScriptExists(): void
    {
        $this->assertFileExists($this->scriptPath);
    }

    /**
     * Test script is executable
     */
    public function testScriptIsExecutable(): void
    {
        $this->assertTrue(is_executable($this->scriptPath), "Script should be executable");
    }

    /**
     * Test script has bash shebang
     */
    public function testScriptHasBashShebang(): void
    {
        $firstLine = fgets(fopen($this->scriptPath, 'r'));
        $this->assertStringStartsWith('#!/', $firstLine, "Script should have shebang");
        $this->assertStringContainsString('bash', $firstLine, "Script should use bash");
    }

    /**
     * Test script has expected functions
     */
    public function testScriptHasExpectedFunctions(): void
    {
        $content = file_get_contents($this->scriptPath);

        // Check for key function definitions
        $expectedFunctions = [
            'test-vietnamese',
            'search',
            'check-schema',
            'analyze',
            'validate'
        ];

        foreach ($expectedFunctions as $function) {
            $this->assertStringContainsString(
                $function,
                $content,
                "Script should contain '$function' functionality"
            );
        }
    }

    /**
     * Test script help command runs without error
     */
    public function testScriptHelpCommand(): void
    {
        $output = [];
        $returnCode = 0;

        exec("{$this->scriptPath} help 2>&1", $output, $returnCode);

        // Help should return 0 or show usage
        $this->assertLessThanOrEqual(1, $returnCode, "Help command should not fail");

        $outputString = implode("\n", $output);
        $this->assertNotEmpty($outputString, "Help should produce output");
    }

    /**
     * Test script validates commands
     */
    public function testScriptValidatesCommands(): void
    {
        $output = [];
        $returnCode = 0;

        // Run with invalid command
        exec("{$this->scriptPath} invalid-command 2>&1", $output, $returnCode);

        // Should fail with non-zero return code or show usage
        $this->assertNotEquals(0, $returnCode, "Invalid command should fail");
    }

    /**
     * Test script has proper Vietnamese encoding support
     */
    public function testScriptSupportsVietnameseEncoding(): void
    {
        $content = file_get_contents($this->scriptPath);

        // Check for UTF-8 handling
        $this->assertTrue(
            mb_check_encoding($content, 'UTF-8'),
            "Script should be UTF-8 encoded"
        );

        // Check for Vietnamese characters or UTF-8 related commands
        $hasEncodingSupport = (
            strpos($content, 'utf8') !== false ||
            strpos($content, 'UTF-8') !== false ||
            strpos($content, 'LANG=') !== false ||
            strpos($content, 'LC_ALL=') !== false
        );

        $this->assertTrue($hasEncodingSupport, "Script should handle UTF-8/Vietnamese encoding");
    }

    /**
     * Test script has database connection handling
     */
    public function testScriptHasDatabaseConnection(): void
    {
        $content = file_get_contents($this->scriptPath);

        // Should have MySQL/MariaDB connection
        $this->assertTrue(
            strpos($content, 'mysql') !== false ||
            strpos($content, 'mariadb') !== false,
            "Script should have database connection code"
        );
    }

    /**
     * Test script has error handling
     */
    public function testScriptHasErrorHandling(): void
    {
        $content = file_get_contents($this->scriptPath);

        // Check for basic error handling patterns
        $hasErrorHandling = (
            strpos($content, 'set -e') !== false ||
            strpos($content, 'exit 1') !== false ||
            strpos($content, 'error') !== false ||
            strpos($content, '||') !== false
        );

        $this->assertTrue($hasErrorHandling, "Script should have error handling");
    }

    /**
     * Test script syntax is valid
     */
    public function testScriptSyntaxIsValid(): void
    {
        $output = [];
        $returnCode = 0;

        // Use bash -n to check syntax without executing
        exec("bash -n {$this->scriptPath} 2>&1", $output, $returnCode);

        $this->assertEquals(
            0,
            $returnCode,
            "Script should have valid bash syntax. Errors: " . implode("\n", $output)
        );
    }

    /**
     * Test script has proper documentation
     */
    public function testScriptHasDocumentation(): void
    {
        $content = file_get_contents($this->scriptPath);

        // Should have comments explaining usage
        $hasComments = (
            strpos($content, '#') !== false &&
            (strpos($content, 'Usage') !== false ||
             strpos($content, 'Description') !== false ||
             strpos($content, 'Author') !== false)
        );

        $this->assertTrue($hasComments, "Script should have documentation comments");
    }

    /**
     * Test script has collation checks
     */
    public function testScriptHasCollationChecks(): void
    {
        $content = file_get_contents($this->scriptPath);

        $this->assertStringContainsString(
            'vietnamese',
            strtolower($content),
            "Script should reference Vietnamese collation"
        );
    }
}
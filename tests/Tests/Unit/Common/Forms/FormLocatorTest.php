<?php

namespace OpenEMR\Tests\Unit\Common\Forms;

use PHPUnit\Framework\TestCase;
use OpenEMR\Common\Forms\FormLocator;

/**
 * FormLocator Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    GitHub Copilot <copilot@github.com>
 * @copyright Copyright (c) 2025 OpenEMR <info@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FormLocatorTest extends TestCase
{
    private FormLocator $formLocator;
    private string $fileRoot;

    protected function setUp(): void
    {
        global $GLOBALS;
        // Store original fileroot
        $this->fileRoot = $GLOBALS['fileroot'] ?? '';
        // Set fileroot to the OpenEMR root directory for tests
        $GLOBALS['fileroot'] = dirname(__FILE__, 6);

        $this->formLocator = new FormLocator();
    }

    protected function tearDown(): void
    {
        global $GLOBALS;
        // Restore original fileroot
        $GLOBALS['fileroot'] = $this->fileRoot;
    }

    /**
     * Test that LBF forms are correctly located to /interface/forms/LBF/
     */
    public function testFindFileLBFForm(): void
    {
        global $GLOBALS;
        $expectedPath = $GLOBALS['fileroot'] . "/interface/forms/LBF/view.php";

        $result = $this->formLocator->findFile('LBFmyform', 'view.php', 'load_form.php');

        $this->assertEquals($expectedPath, $result);
    }

    /**
     * Test that LBT forms are correctly located to /interface/forms/LBF/
     * This is the main bug fix - LBT forms should use the same directory as LBF forms
     */
    public function testFindFileLBTForm(): void
    {
        global $GLOBALS;
        $expectedPath = $GLOBALS['fileroot'] . "/interface/forms/LBF/view.php";

        $result = $this->formLocator->findFile('LBTref', 'view.php', 'load_form.php');

        $this->assertEquals($expectedPath, $result);
    }

    /**
     * Test that LBTbill form (billing transaction) is correctly located
     */
    public function testFindFileLBTbillForm(): void
    {
        global $GLOBALS;
        $expectedPath = $GLOBALS['fileroot'] . "/interface/forms/LBF/view.php";

        $result = $this->formLocator->findFile('LBTbill', 'view.php', 'load_form.php');

        $this->assertEquals($expectedPath, $result);
    }

    /**
     * Test that LBTptreq form (patient request) is correctly located
     */
    public function testFindFileLBTptreqForm(): void
    {
        global $GLOBALS;
        $expectedPath = $GLOBALS['fileroot'] . "/interface/forms/LBF/view.php";

        $result = $this->formLocator->findFile('LBTptreq', 'view.php', 'load_form.php');

        $this->assertEquals($expectedPath, $result);
    }

    /**
     * Test that LBTphreq form (physician request) is correctly located
     */
    public function testFindFileLBTphreqForm(): void
    {
        global $GLOBALS;
        $expectedPath = $GLOBALS['fileroot'] . "/interface/forms/LBF/view.php";

        $result = $this->formLocator->findFile('LBTphreq', 'view.php', 'load_form.php');

        $this->assertEquals($expectedPath, $result);
    }

    /**
     * Test that LBTlegal form is correctly located
     */
    public function testFindFileLBTlegalForm(): void
    {
        global $GLOBALS;
        $expectedPath = $GLOBALS['fileroot'] . "/interface/forms/LBF/view.php";

        $result = $this->formLocator->findFile('LBTlegal', 'view.php', 'load_form.php');

        $this->assertEquals($expectedPath, $result);
    }

    /**
     * Test that regular forms (non-LBF/LBT) are located in their own directories
     */
    public function testFindFileRegularForm(): void
    {
        global $GLOBALS;
        $expectedPath = $GLOBALS['fileroot'] . "/interface/forms/vitals/view.php";

        $result = $this->formLocator->findFile('vitals', 'view.php', 'load_form.php');

        $this->assertEquals($expectedPath, $result);
    }

    /**
     * Test that new.php file for LBF forms is correctly located
     */
    public function testFindFileLBFNewForm(): void
    {
        global $GLOBALS;
        $expectedPath = $GLOBALS['fileroot'] . "/interface/forms/LBF/new.php";

        $result = $this->formLocator->findFile('LBFmyform', 'new.php', 'load_form.php');

        $this->assertEquals($expectedPath, $result);
    }

    /**
     * Test that new.php file for LBT forms is correctly located
     */
    public function testFindFileLBTNewForm(): void
    {
        global $GLOBALS;
        $expectedPath = $GLOBALS['fileroot'] . "/interface/forms/LBF/new.php";

        $result = $this->formLocator->findFile('LBTref', 'new.php', 'load_form.php');

        $this->assertEquals($expectedPath, $result);
    }
}

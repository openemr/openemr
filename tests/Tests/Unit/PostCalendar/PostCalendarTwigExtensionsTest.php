<?php

/**
 * PostCalendarTwigExtensionsTest
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2024 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\PostCalendar;

use OpenEMR\PostCalendar\PostCalendarTwigExtensions;
use PHPUnit\Framework\TestCase;

class PostCalendarTwigExtensionsTest extends TestCase
{
    private PostCalendarTwigExtensions $extensions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extensions = new PostCalendarTwigExtensions();
    }

    /**
     * Test that all required Twig functions are registered
     */
    public function testGetFunctionsReturnsAllRequiredFunctions(): void
    {
        $functions = $this->extensions->getFunctions();
        
        $functionNames = array_map(function ($func) {
            return $func->getName();
        }, $functions);

        $expectedFunctions = [
            'monthSelector',
            'create_event_time_anchor',
            'renderProviderTimeSlots',
            'is_weekend_day',
            'PrintDatePicker',
            'generateDOWCalendar',
            'PrintEvents',
            'getProviderInfo',
            'datetimepickerJsConfig',
            'getCalendarImagePath',
            'generatePrintURL'
        ];

        foreach ($expectedFunctions as $expectedFunction) {
            $this->assertContains(
                $expectedFunction,
                $functionNames,
                "Function '$expectedFunction' should be registered"
            );
        }
    }

    /**
     * Test generateDOWCalendar with a specific date
     */
    public function testGenerateDOWCalendarReturnsProperStructure(): void
    {
        $dateString = '20260609'; // June 9, 2026
        $DOWlist = [0, 1, 2, 3, 4, 5, 6]; // Sunday - Saturday

        $result = $this->extensions->generateDOWCalendar($dateString, $DOWlist);

        // Should return an array of weeks
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // Each week should be an array
        foreach ($result as $week) {
            $this->assertIsArray($week);
            
            // Each day in the week should have required properties
            foreach ($week as $day) {
                $this->assertIsArray($day);
                $this->assertArrayHasKey('class', $day);
                $this->assertArrayHasKey('id', $day);
                $this->assertArrayHasKey('title', $day);
                $this->assertArrayHasKey('text', $day);
            }
        }
    }

    /**
     * Test generateDOWCalendar identifies current date correctly
     */
    public function testGenerateDOWCalendarMarksCurrentDate(): void
    {
        $dateString = '20260609'; // June 9, 2026
        $DOWlist = [0, 1, 2, 3, 4, 5, 6];

        $result = $this->extensions->generateDOWCalendar($dateString, $DOWlist);

        $foundCurrentDate = false;
        foreach ($result as $week) {
            foreach ($week as $day) {
                if ($day['id'] === $dateString) {
                    $this->assertStringContainsString('currentDate', $day['class']);
                    $foundCurrentDate = true;
                }
            }
        }

        $this->assertTrue($foundCurrentDate, 'Current date should be marked in the calendar');
    }

    /**
     * Test generatePrintURL generates correct URL structure
     */
    public function testGeneratePrintURLCreatesValidURL(): void
    {
        $templateView = 'default';
        $viewtype = 'month';
        $date = '20260609';
        $username = 'testuser';
        $category = '1';
        $topic = 'testtopic';

        $url = $this->extensions->generatePrintURL(
            $templateView,
            $viewtype,
            $date,
            $username,
            $category,
            $topic
        );

        // Should contain required parameters
        $this->assertStringContainsString('module=PostCalendar', $url);
        $this->assertStringContainsString('func=view', $url);
        $this->assertStringContainsString('tplview=default', $url);
        $this->assertStringContainsString('viewtype=month', $url);
        $this->assertStringContainsString('Date=20260609', $url);
        $this->assertStringContainsString('print=1', $url);
        $this->assertStringContainsString('pc_username=testuser', $url);
        $this->assertStringContainsString('pc_category=1', $url);
        $this->assertStringContainsString('pc_topic=testtopic', $url);
    }

    /**
     * Test generatePrintURL with empty optional parameters
     */
    public function testGeneratePrintURLWithEmptyParameters(): void
    {
        $url = $this->extensions->generatePrintURL('default', 'day', '20260609');

        $this->assertStringContainsString('module=PostCalendar', $url);
        $this->assertStringContainsString('func=view', $url);
        $this->assertStringContainsString('viewtype=day', $url);
        $this->assertStringContainsString('print=1', $url);
    }

    /**
     * Test getCalendarImagePath returns correct path
     */
    public function testGetCalendarImagePathReturnsValidPath(): void
    {
        global $webroot;
        $webroot = '/openemr'; // Set a test webroot

        $path = $this->extensions->getCalendarImagePath();

        $this->assertStringContainsString('/interface/main/calendar/modules/PostCalendar/pntemplates/default/images', $path);
        $this->assertStringStartsWith('/openemr', $path);
    }

    /**
     * Test create_event_time_anchor creates proper HTML
     */
    public function testCreateEventTimeAnchorReturnsValidHTML(): void
    {
        $displayString = '9:00am';
        
        $result = $this->extensions->create_event_time_anchor($displayString);

        // Should contain an anchor tag
        $this->assertStringContainsString('<a', $result);
        $this->assertStringContainsString('class=\'event_time\'', $result);
        $this->assertStringContainsString('onclick=\'event_time_click(this)\'', $result);
        $this->assertStringContainsString('9:00am', $result);
    }

    /**
     * Test generateDOWCalendar with Monday start of week
     */
    public function testGenerateDOWCalendarWithMondayStart(): void
    {
        $dateString = '20260609'; // June 9, 2026 (Tuesday)
        $DOWlist = [1, 2, 3, 4, 5, 6, 0]; // Monday - Sunday

        $result = $this->extensions->generateDOWCalendar($dateString, $DOWlist);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // First day of first week should be a Monday (day of week = 1)
        $firstDay = $result[0][0];
        $firstDayDate = \DateTime::createFromFormat('Ymd', $firstDay['id']);
        $this->assertEquals('1', $firstDayDate->format('N')); // N returns 1 for Monday
    }

    /**
     * Test generateDOWCalendar marks weekend days
     */
    public function testGenerateDOWCalendarMarksWeekendDays(): void
    {
        $dateString = '20260609';
        $DOWlist = [0, 1, 2, 3, 4, 5, 6];

        $result = $this->extensions->generateDOWCalendar($dateString, $DOWlist);

        $foundWeekend = false;
        foreach ($result as $week) {
            foreach ($week as $day) {
                $dayDate = \DateTime::createFromFormat('Ymd', $day['id']);
                $dayOfWeek = $dayDate->format('w');
                
                // Check if weekend days (0 = Sunday, 6 = Saturday) have weekend class
                if ($dayOfWeek == '0' || $dayOfWeek == '6') {
                    $this->assertStringContainsString('tdWeekend-small', $day['class']);
                    $foundWeekend = true;
                }
            }
        }

        $this->assertTrue($foundWeekend, 'Should mark weekend days with proper class');
    }

    /**
     * Test generateDOWCalendar marks other month days
     */
    public function testGenerateDOWCalendarMarksOtherMonthDays(): void
    {
        $dateString = '20260609'; // June 2026
        $DOWlist = [0, 1, 2, 3, 4, 5, 6];

        $result = $this->extensions->generateDOWCalendar($dateString, $DOWlist);

        $foundOtherMonth = false;
        foreach ($result as $week) {
            foreach ($week as $day) {
                $dayDate = \DateTime::createFromFormat('Ymd', $day['id']);
                
                // Check if days from other months are marked
                if ($dayDate->format('m') != '06') {
                    $this->assertStringContainsString('tdOtherMonthDay-small', $day['class']);
                    $foundOtherMonth = true;
                }
            }
        }

        $this->assertTrue($foundOtherMonth, 'Should mark days from other months');
    }
}

<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PostCalendar;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('postcalendar')]
final class CalendarIndexFacilityCookieContractTest extends TestCase
{
    public function testAllowedFacilityIdsAreSafelyExtractedFromMixedData(): void
    {
        $source = file_get_contents(__DIR__ . '/../../../../interface/main/calendar/index.php');
        self::assertIsString($source);

        self::assertStringNotContainsString('array_column($facilities', $source);
        self::assertMatchesRegularExpression(
            '/\/\*\* @var list<int\|string> \$allowedFacilityIds \*\/\s*'
            . '\$allowedFacilityIds = \[\];\s*'
            . 'if \(is_array\(\$facilities\)\) \{\s*'
            . 'foreach \(\$facilities as \$facility\) \{\s*'
            . 'if \(!is_array\(\$facility\)\).*?'
            . '\$facilityId = \$facility\[\'id\'\] \?\? null;\s*'
            . 'if \(is_int\(\$facilityId\) \|\| is_string\(\$facilityId\)\) \{\s*'
            . '\$allowedFacilityIds\[\] = \$facilityId;/s',
            $source
        );
    }

    public function testResolvedFacilityCookieIsWrittenAfterSessionWithApplicationRootPath(): void
    {
        $source = file_get_contents(__DIR__ . '/../../../../interface/main/calendar/index.php');
        self::assertIsString($source);

        $sessionWritePosition = strpos($source, 'SessionUtil::setSession($sessionSetArray);');
        $cookieWritePosition = strpos($source, 'setcookie("pc_facility"');

        self::assertNotFalse($sessionWritePosition);
        self::assertNotFalse($cookieWritePosition);
        self::assertGreaterThan($sessionWritePosition, $cookieWritePosition);

        $cookieContract = substr($source, $cookieWritePosition, 350);
        self::assertMatchesRegularExpression(
            '/setcookie\(\s*["\']pc_facility["\']\s*,\s*\(string\)\s*\$sessionSetArray\[["\']pc_facility["\']\]/',
            $cookieContract
        );
        self::assertStringContainsString("'path' => OEGlobalsBag::getInstance()->getWebRoot()", $cookieContract);

        $guard = substr($source, max(0, $cookieWritePosition - 220), 220);
        self::assertStringContainsString("\$sessionSetArray['pc_facility'] !== null", $guard);
        self::assertStringNotContainsString("\$sessionSetArray['pc_facility'] > 0", $guard);
    }
}

<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Tabs;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Tabs\PatientBarInfo;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class PatientBarInfoTest extends TestCase
{
    private bool $translationWas = false;

    protected function setUp(): void
    {
        // xl() would otherwise query the translation tables.
        $this->translationWas = OEGlobalsBag::getInstance()->getBoolean('disable_translation');
        OEGlobalsBag::getInstance()->set('disable_translation', true);
    }

    protected function tearDown(): void
    {
        OEGlobalsBag::getInstance()->set('disable_translation', $this->translationWas);
    }

    /**
     * @return array<string, array{PatientBarInfo, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function partsProvider(): array
    {
        return [
            'adult' => [
                new PatientBarInfo(43, 'Male', '06/09/1993', '33'),
                ['PID 43', 'Male', 'DOB 06/09/1993', '33 years'],
            ],
            'baby, age in months' => [
                new PatientBarInfo(7, 'Female', '05/01/2026', '6 month'),
                ['PID 7', 'Female', 'DOB 05/01/2026', '6 month'],
            ],
            'age in years, months and days' => [
                new PatientBarInfo(8, 'Female', '01/02/2024', '2y 10m 21d'),
                ['PID 8', 'Female', 'DOB 01/02/2024', '2y 10m 21d'],
            ],
            'deceased' => [
                new PatientBarInfo(9, 'Male', '03/04/1940', '81', deceased: true),
                ['PID 9', 'Male', 'DOB 03/04/1940', 'Age at death 81 years'],
            ],
            'no sex recorded' => [
                new PatientBarInfo(10, '', '06/09/1993', '33'),
                ['PID 10', 'DOB 06/09/1993', '33 years'],
            ],
            'no date of birth or age' => [
                new PatientBarInfo(11, 'Male', '', ''),
                ['PID 11', 'Male'],
            ],
            'nothing else recorded' => [
                new PatientBarInfo(12, '', '', ''),
                ['PID 12'],
            ],
        ];
    }

    /**
     * @param list<string> $expected
     */
    #[DataProvider('partsProvider')]
    public function testParts(PatientBarInfo $info, array $expected): void
    {
        $this->assertSame($expected, $info->parts());
    }
}

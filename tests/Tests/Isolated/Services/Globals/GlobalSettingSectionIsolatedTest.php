<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services\Globals;

use OpenEMR\Services\Globals\GlobalSettingSection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('setting')]
#[CoversClass(GlobalSettingSection::class)]
class GlobalSettingSectionIsolatedTest extends TestCase
{
    #[Test]
    public function allSectionsTest(): void
    {
        $this->assertCount(24, GlobalSettingSection::ALL_SECTIONS);
    }

    #[Test]
    public function userSpecificSectionsTest(): void
    {
        $this->assertCount(10, GlobalSettingSection::USER_SPECIFIC_SECTIONS);

        $unexpectedUserSpecificValues = array_diff(
            GlobalSettingSection::USER_SPECIFIC_SECTIONS,
            array_values(array_intersect(GlobalSettingSection::ALL_SECTIONS, GlobalSettingSection::USER_SPECIFIC_SECTIONS)),
        );

        $this->assertCount(
            0,
            $unexpectedUserSpecificValues,
            sprintf(
                'Sections %s was found at GlobalSettingSection::USER_SPECIFIC_SECTIONS, but absent at GlobalSettingSection::ALL_SECTIONS',
                implode(', ', $unexpectedUserSpecificValues)
            )
        );
    }
}

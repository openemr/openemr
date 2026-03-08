<?php

/**
 * Isolated PlaceOfServiceEnum Test
 *
 * Tests PlaceOfServiceEnum methods for place of service codes.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Common\Enum;

use OpenEMR\Common\Enum\PlaceOfServiceEnum;
use PHPUnit\Framework\TestCase;

class PlaceOfServiceEnumTest extends TestCase
{
    public function testFromCodeReturnsEnumForValidCode(): void
    {
        $result = PlaceOfServiceEnum::fromCode('11');
        $this->assertSame(PlaceOfServiceEnum::OFFICE, $result);
    }

    public function testFromCodeReturnsNullForInvalidCode(): void
    {
        $result = PlaceOfServiceEnum::fromCode('invalid');
        $this->assertNull($result);
    }

    public function testFromCodeReturnsNullForEmptyCode(): void
    {
        $result = PlaceOfServiceEnum::fromCode('');
        $this->assertNull($result);
    }

    public function testGetCodeReturnsValue(): void
    {
        $this->assertSame('11', PlaceOfServiceEnum::OFFICE->getCode());
        $this->assertSame('12', PlaceOfServiceEnum::HOME->getCode());
        $this->assertSame('21', PlaceOfServiceEnum::INPATIENT_HOSPITAL->getCode());
    }

    public function testGetNameReturnsUntranslatedName(): void
    {
        $this->assertSame('Office', PlaceOfServiceEnum::OFFICE->getName());
        $this->assertSame('Home', PlaceOfServiceEnum::HOME->getName());
        $this->assertSame('Telehealth', PlaceOfServiceEnum::TELEHEALTH->getName());
    }

    public function testGetNameForAllCases(): void
    {
        foreach (PlaceOfServiceEnum::cases() as $case) {
            $name = $case->getName();
            $this->assertNotEmpty($name);
        }
    }

    public function testGetDescriptionReturnsDescription(): void
    {
        $officeDesc = PlaceOfServiceEnum::OFFICE->getDescription();
        $this->assertStringContainsString('health professional', $officeDesc);

        $homeDesc = PlaceOfServiceEnum::HOME->getDescription();
        $this->assertStringContainsString('private residence', $homeDesc);
    }

    public function testGetDescriptionForUnassignedReturnsNA(): void
    {
        $this->assertSame('N/A', PlaceOfServiceEnum::UNASSIGNED_10->getDescription());
        $this->assertSame('N/A', PlaceOfServiceEnum::UNASSIGNED_27->getDescription());
    }

    public function testGetDescriptionForAllCases(): void
    {
        foreach (PlaceOfServiceEnum::cases() as $case) {
            $desc = $case->getDescription();
            $this->assertNotEmpty($desc);
        }
    }

    public function testCommonPlaceOfServiceCodes(): void
    {
        // Test common codes used in medical billing
        $this->assertSame('01', PlaceOfServiceEnum::PHARMACY->getCode());
        $this->assertSame('02', PlaceOfServiceEnum::TELEHEALTH->getCode());
        $this->assertSame('11', PlaceOfServiceEnum::OFFICE->getCode());
        $this->assertSame('12', PlaceOfServiceEnum::HOME->getCode());
        $this->assertSame('20', PlaceOfServiceEnum::URGENT_CARE->getCode());
        $this->assertSame('21', PlaceOfServiceEnum::INPATIENT_HOSPITAL->getCode());
        $this->assertSame('22', PlaceOfServiceEnum::OUTPATIENT_HOSPITAL->getCode());
        $this->assertSame('23', PlaceOfServiceEnum::EMERGENCY_ROOM->getCode());
        $this->assertSame('31', PlaceOfServiceEnum::SKILLED_NURSING->getCode());
        $this->assertSame('99', PlaceOfServiceEnum::OTHER->getCode());
    }

    public function testFromCodeCoversFullRange(): void
    {
        // Verify that when fromCode returns an enum, its code matches
        $validCodes = 0;
        for ($i = 1; $i <= 99; $i++) {
            $code = str_pad((string)$i, 2, '0', STR_PAD_LEFT);
            $result = PlaceOfServiceEnum::fromCode($code);

            // If we got an enum, its code should match
            if ($result !== null) {
                $this->assertSame($code, $result->getCode());
                $validCodes++;
            }
        }

        // Verify we found at least some valid codes
        $this->assertGreaterThan(0, $validCodes);
    }

    public function testEnumValueMatchesCode(): void
    {
        foreach (PlaceOfServiceEnum::cases() as $case) {
            $this->assertSame($case->value, $case->getCode());
        }
    }

    public function testSpecificFacilityNames(): void
    {
        $this->assertSame('Ambulatory Surgical Center', PlaceOfServiceEnum::AMBULATORY_SURGICAL->getName());
        $this->assertSame('Federally Qualified Health Center', PlaceOfServiceEnum::FEDERALLY_QUALIFIED_HEALTH->getName());
        $this->assertSame('Community Mental Health Center', PlaceOfServiceEnum::COMMUNITY_MENTAL_HEALTH->getName());
    }

    public function testIndianHealthServiceFacilities(): void
    {
        $this->assertSame('05', PlaceOfServiceEnum::IHS_FREESTANDING->getCode());
        $this->assertSame('06', PlaceOfServiceEnum::IHS_PROVIDER_BASED->getCode());
        $this->assertSame('07', PlaceOfServiceEnum::TRIBAL_638_FREESTANDING->getCode());
        $this->assertSame('08', PlaceOfServiceEnum::TRIBAL_638_PROVIDER_BASED->getCode());

        $this->assertStringContainsString('Indian Health Service', PlaceOfServiceEnum::IHS_FREESTANDING->getName());
        $this->assertStringContainsString('Tribal 638', PlaceOfServiceEnum::TRIBAL_638_FREESTANDING->getName());
    }

    public function testAmbulanceCodes(): void
    {
        $this->assertSame('41', PlaceOfServiceEnum::AMBULANCE_LAND->getCode());
        $this->assertSame('42', PlaceOfServiceEnum::AMBULANCE_AIR_WATER->getCode());

        $this->assertStringContainsString('Land', PlaceOfServiceEnum::AMBULANCE_LAND->getName());
        $this->assertStringContainsString('Air or Water', PlaceOfServiceEnum::AMBULANCE_AIR_WATER->getName());
    }
}

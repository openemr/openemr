<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Common;

use PHPUnit_Framework_TestCase as TestCase;

class VersionTest extends TestCase
{
    public static function versionProvider()
    {
        $array = array();

        for ($i = 1; $i <= 40; $i++) {
            $array[] = array($i, 4 * $i + 17);
        }

        return $array;
    }

    public static function decodeInformationProvider()
    {
        return array(
            array(7, 0x07c94),
            array(12, 0x0c762),
            array(17, 0x1145d),
            array(22, 0x168c9),
            array(27, 0x1b08e),
            array(32, 0x209d5),
        );
    }

    /**
     * @dataProvider versionProvider
     * @param        integer $versionNumber
     * @param        integer $dimension
     */
    public function testVersionForNumber($versionNumber, $dimension)
    {
        $version = Version::getVersionForNumber($versionNumber);

        $this->assertNotNull($version);
        $this->assertEquals($versionNumber, $version->getVersionNumber());
        $this->assertNotNull($version->getAlignmentPatternCenters());

        if ($versionNumber > 1) {
            $this->assertTrue(count($version->getAlignmentPatternCenters()) > 0);
        }

        $this->assertEquals($dimension, $version->getDimensionForVersion());
        $this->assertNotNull($version->getEcBlocksForLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::H)));
        $this->assertNotNull($version->getEcBlocksForLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::L)));
        $this->assertNotNull($version->getEcBlocksForLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::M)));
        $this->assertNotNull($version->getEcBlocksForLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::Q)));
        $this->assertNotNull($version->buildFunctionPattern());
    }

    /**
     * @dataProvider versionProvider
     * @param        integer $versionNumber
     * @param        integer $dimension
     */
    public function testGetProvisionalVersionForDimension($versionNumber, $dimension)
    {
        $this->assertEquals(
            $versionNumber,
            Version::getProvisionalVersionForDimension($dimension)->getVersionNumber()
        );
    }

    /**
     * @dataProvider decodeInformationProvider
     * @param        integer $expectedVersion
     * @param        integer $mask
     */
    public function testDecodeVersionInformation($expectedVersion, $mask)
    {
        $version = Version::decodeVersionInformation($mask);
        $this->assertNotNull($version);
        $this->assertEquals($expectedVersion, $version->getVersionNumber());
    }
}
<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Common;

/**
 * Enum representing various modes in which data can be encoded to bits.
 */
class Mode extends AbstractEnum
{
    /**#@+
     * Mode constants.
     */
    const TERMINATOR           = 0x0;
    const NUMERIC              = 0x1;
    const ALPHANUMERIC         = 0x2;
    const STRUCTURED_APPEND    = 0x3;
    const BYTE                 = 0x4;
    const ECI                  = 0x7;
    const KANJI                = 0x8;
    const FNC1_FIRST_POSITION  = 0x5;
    const FNC1_SECOND_POSITION = 0x9;
    const HANZI                = 0xd;
    /**#@-*/

    /**
     * Character count bits for each version.
     *
     * @var array
     */
    protected static $characterCountBitsForVersions = array(
        self::TERMINATOR           => array(0, 0, 0),
        self::NUMERIC              => array(10, 12, 14),
        self::ALPHANUMERIC         => array(9, 11, 13),
        self::STRUCTURED_APPEND    => array(0, 0, 0),
        self::BYTE                 => array(8, 16, 16),
        self::ECI                  => array(0, 0, 0),
        self::KANJI                => array(8, 10, 12),
        self::FNC1_FIRST_POSITION  => array(0, 0, 0),
        self::FNC1_SECOND_POSITION => array(0, 0, 0),
        self::HANZI                => array(8, 10, 12),
    );

    /**
     * Gets the number of bits used in a specific QR code version.
     *
     * @param  Version $version
     * @return integer
     */
    public function getCharacterCountBits(Version $version)
    {
        $number = $version->getVersionNumber();

        if ($number <= 9) {
            $offset = 0;
        } elseif ($number <= 26) {
            $offset = 1;
        } else {
            $offset = 2;
        }

        return self::$characterCountBitsForVersions[$this->value][$offset];
    }
}

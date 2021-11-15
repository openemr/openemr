<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Encoder;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Common\Mode;
use BaconQrCode\Common\Version;

/**
 * QR code.
 */
class QrCode
{
    /**
     * Number of possible mask patterns.
     */
    const NUM_MASK_PATTERNS = 8;

    /**
     * Mode of the QR code.
     *
     * @var Mode
     */
    protected $mode;

    /**
     * EC level of the QR code.
     *
     * @var ErrorCorrectionLevel
     */
    protected $errorCorrectionLevel;

    /**
     * Version of the QR code.
     *
     * @var Version
     */
    protected $version;

    /**
     * Mask pattern of the QR code.
     *
     * @var integer
     */
    protected $maskPattern = -1;

    /**
     * Matrix of the QR code.
     *
     * @var ByteMatrix
     */
    protected $matrix;

    /**
     * Gets the mode.
     *
     * @return Mode
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Sets the mode.
     *
     * @param  Mode $mode
     * @return void
     */
    public function setMode(Mode $mode)
    {
        $this->mode = $mode;
    }

    /**
     * Gets the EC level.
     *
     * @return ErrorCorrectionLevel
     */
    public function getErrorCorrectionLevel()
    {
        return $this->errorCorrectionLevel;
    }

    /**
     * Sets the EC level.
     *
     * @param  ErrorCorrectionLevel $errorCorrectionLevel
     * @return void
     */
    public function setErrorCorrectionLevel(ErrorCorrectionLevel $errorCorrectionLevel)
    {
        $this->errorCorrectionLevel = $errorCorrectionLevel;
    }

    /**
     * Gets the version.
     *
     * @return Version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Sets the version.
     *
     * @param  Version $version
     * @return void
     */
    public function setVersion(Version $version)
    {
        $this->version = $version;
    }

    /**
     * Gets the mask pattern.
     *
     * @return integer
     */
    public function getMaskPattern()
    {
        return $this->maskPattern;
    }

    /**
     * Sets the mask pattern.
     *
     * @param  integer $maskPattern
     * @return void
     */
    public function setMaskPattern($maskPattern)
    {
        $this->maskPattern = $maskPattern;
    }

    /**
     * Gets the matrix.
     *
     * @return ByteMatrix
     */
    public function getMatrix()
    {
        return $this->matrix;
    }

    /**
     * Sets the matrix.
     *
     * @param  ByteMatrix $matrix
     * @return void
     */
    public function setMatrix(ByteMatrix $matrix)
    {
        $this->matrix = $matrix;
    }

    /**
     * Validates whether a mask pattern is valid.
     *
     * @param  integer $maskPattern
     * @return boolean
     */
    public static function isValidMaskPattern($maskPattern)
    {
        return $maskPattern > 0 && $maskPattern < self::NUM_MASK_PATTERNS;
    }

    /**
     * Returns a string representation of the QR code.
     *
     * @return string
     */
    public function __toString()
    {
        $result = "<<\n"
                . " mode: " . $this->mode . "\n"
                . " ecLevel: " . $this->errorCorrectionLevel . "\n"
                . " version: " . $this->version . "\n"
                . " maskPattern: " . $this->maskPattern . "\n";

        if ($this->matrix === null) {
            $result .= " matrix: null\n";
        } else {
            $result .= " matrix:\n";
            $result .= $this->matrix;
        }

        $result .= ">>\n";

        return $result;
    }
}
